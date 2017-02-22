# dynamic-dns-client-php
A client for Dynamic DNS system. Written in PHP.

## How to setup your own dynamic DNS system

### Requirements

1. Digital Ocean account
2. A domain
3. A computer as a server


> We use Digital Ocean as a DNS server, and use its API for updating the IP which the CNAME of your domain pointed to.


### Setup

1. Add your domain to Digital Ocean in `Networking` Section.
2. Go to your domain provider, open control panel and remove any existing record. Add 3 NS records as below:

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

5. Get the token
 Go to https://cloud.digitalocean.com/settings/applications to generate a new token for your app.

### Configuration

- Open the `config.ini`, replace your token in the token section.
- Change `time_zone` and `ip_api` as your favorite.
- If `force_update` is set to `1`, the script will ignore IP from the logging file, and update the current IP from the API to Digital Ocean.
- Every domain record is type `A`. Each domain record in one line. You must use array config type (eg. `domain.com[]`) if there are many records in one domain

### Run the script

`php index.php`

You can install the cronjob for doing this job each 10 minutes or 1 hour...

## Additional

You can point NS of a domain to Digital Ocean. Then add as much subdomain (CNAME) (called XXX) as you want, each for a PC server. Then you can use another domain/subdomain, point it to the XXX domain.

Example:

I have a domain `phamquocbuu.name.vn`, its NS records as below:

- ns1.digitalocean.com.
- ns2.digitalocean.com.
- ns3.digitalocean.com.

In Digital Ocean, I (use API) add a lot of CNAME, `workstation.phamquocbuu.name.vn`, `web.phamquocbuu.name.vn`, `blog.phamquocbuu.name.vn`,...
 
I use my main domain `buu.vn`. Therefore I create a CNAME `blog.buu.vn` pointed to `blog.phamquocbuu.name.vn`, `ws.buu.vn` pointed to `workstation.phamquocbuu.name.vn`,...
