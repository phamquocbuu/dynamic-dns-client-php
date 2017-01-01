# dynamic-dns-client-php
A client for Dynamic DNS system. Written in PHP.

## How to setup your own dynamic DNS system

### Requirements

1. Digital Ocean droplet
2. A domain
3. A computer/VPS

### Setup

1. Add your domain to Digital Ocean droplet.
2. Remove any existing record in your domain. Add 3 NS records as below:

 - ns1.digitalocean.com.
 - ns2.digitalocean.com.
 - ns3.digitalocean.com.

3. Clone this repo to your working directory

 `git clone https://github.com/cosmospham/dynamic-dns-client-php.git`

4. Install dependency

 ````
 cd dynamic-dns-client-php
 composer install
 ```

5. Run the script

 `php index.php`

You can install the cronjob for doing this job each 10 minute or 1 hour...
