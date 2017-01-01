<?php

if (!file_exists('vendor/autoload.php')) die ('Run `composer install` before using');

include 'vendor/autoload.php';

use DigitalOceanV2\Adapter\BuzzAdapter;
use DigitalOceanV2\DigitalOceanV2;

main();

function main() {
    global $force_update, $ip_check, $ini_array;
    $force_update = false;

    define("CONFIG_FILE", "config.ini");
    if (!file_exists(CONFIG_FILE)) die("No config file");

    $ini_array = parse_ini_file(CONFIG_FILE, true);
    if (!$ini_array) die('No config content');

    if (isset($ini_array['config']['time_zone']))
        date_default_timezone_set($ini_array['config']['time_zone']);

    if (!isset($ini_array['config']['ip_api'])) die ('No IP API');
    if (!isset($ini_array['config']['token'])) die ('No token');
    if (isset($ini_array['config']['force_update']) && $ini_array['config']['force_update'])
        $force_update = true;

    $ip_check = file_get_contents($ini_array['config']['ip_api']);
    if (!isset($ip_check) || !$ip_check) die("Cannot fetch current IP");

    do_update();
}

function do_update() {
    global $ini_array;

    $domain_list = $ini_array['domain'];

    foreach ($domain_list as $domain => $records) {
        if (is_array($records)) {
            foreach ($records as $key => $recordName) {
                update_ip_for_domain($domain, $recordName);
            }
        } else {
            update_ip_for_domain($domain, $records);
        }
    }
}

function update_ip_for_domain($domain, $a_record) {
    global $ini_array, $force_update, $ip_check;

    if (!isset($ip_check) || !$ip_check) die("Cannot fetch current IP");

    echo "--- Updating for $a_record.$domain\r\n";

    $last_ip = get_last_ip($domain, $a_record);

    if (!is_null($last_ip) && $ip_check === $last_ip && !$force_update) {
        echo date('Y-m-d H:i:s')." - IP not change, no update\r\n";
        return false;
    }

    // create an adapter with your access token which can be
    // generated at https://cloud.digitalocean.com/settings/applications
    $adapter = new BuzzAdapter($ini_array['config']['token']);

    // create a digital ocean object with the previous adapter
    $digitalocean = new DigitalOceanV2($adapter);
    $domainRecord = $digitalocean->domainRecord();
    $domainRecords = $domainRecord->getAll($domain);
    $id = get($domainRecords, $a_record, 'id') ."\r\n";

    if (empty(trim($id)) || !$id) {
        $id = set($domainRecord, $domain, [
            "type" => "A",
            "name" => $a_record,
            "data" => $ip_check,
            "priority" => null,
            "port" => null,
            "weight" => null
        ]);

        if (!$id) {
            echo "Cannot create record $a_record for domain $domain";
            return false;
        }
    }

    echo date('Y-m-d H:i:s').' - New IP: '.$ip_check."\r\n";

    $domainRecord->update($domain, $id, $a_record, $ip_check, null, null, null);

    set_last_ip($domain, $a_record, $ip_check);

    echo "--- Done updating for $a_record.$domain\r\n";
}

///
function get($domainRecords, $name = "", $index) {
    $result = null;
    foreach ($domainRecords as $key => $rec) {
        foreach ($rec as $_k => $_v) {
            if ("name" === $_k && $name === $_v && $rec->type == "A") {
                $result = $rec->$index;
            }
        }
    }

    return $result;
}

function set($domainRecord, $domainName, $data) {
    echo sprintf("Creating record %s %s for %s\r\n", $data['type'], $data['name'], $domainName);
    $created = $domainRecord->create($domainName, $data['type'], $data['name'], $data['data']);

    if ($created) {
        echo sprintf("Done creating record %s %s for %s\r\n", $data['type'], $data['name'], $domainName);
        return $created->id;
    }

    echo "Created failed\r\n";
    return null;
}

function get_last_ip($domainName, $recordName) {
    if (file_exists(sprintf("%s_%s.log", $domainName, $recordName)))
        return file_get_contents(sprintf("%s_%s.log", $domainName, $recordName));
    else
        return null;
}

function set_last_ip($domainName, $recordName, $ip) {
    file_put_contents(sprintf("%s_%s.log", $domainName, $recordName), $ip);
}
