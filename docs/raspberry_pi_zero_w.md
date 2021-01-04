# Raspberry PI ZERO W



[GETTING STARTED WITH THE RASPBERRY PI ZERO W WITHOUT A MONITOR](https://www.losant.com/blog/getting-started-with-the-raspberry-pi-zero-w-without-a-monitor)



- downlaos OS:

## RASPBIAN STRETCH LITE

Minimal image based on Debian Stretch

Version:**June 2018**

Release date:**2018-06-27**

Kernel version:**4.14**

Release notes:**Link**

SHA-256:**3271b244734286d99aeba8fa043b6634cad488d211583814a2018fc14fdca313**



- add `ssh`
- add `wpa_supplicant.conf`
- ssh into
- update `sudo apt-get update && sudo apt-get upgrade -y`
- `sudo apt-get install mc`
- `ssh-copy-id`
- edit `/etc/hostname` and `/etc/hosts`

## lighttpd

- `sudo apt-get install lighttpd`
  - Configuration files can be found in `/etc/lighttpd`. Please read /etc/lighttpd/conf-available/README file.
  - The DocumentRoot, which is the directory under which all your HTML files should exist, is set to `/var/www/html`.
  - Log files are placed in `/var/log/lighttpd`, and will be rotated weekly. The frequency of rotation can be easily changed by editing /etc/logrotate.d/lighttpd.
  - The default directory index is index.html, meaning that requests for a directory /foo/bar/ will give the contents of the file /var/www/foo/bar/index.html if it exists (assuming that /var/www is your DocumentRoot).
- chown `/var/www/html`

#### lighttpd.conf

```properties
# redirect images
url.redirect = ( "^/rrd-api/img/(.*).png" => "/rrd-api/index.php?start=$1" )
```



## php 7

- `sudo apt-get install php7.0 php7.0-cgi`
- `sudo lighty-enable-mod fastcgi`
- `sudo lighty-enable-mod fastcgi-php`
- `sudo apt-get install php-curl`
- `sudo apt-get install php-dom`
- `sudo service lighttpd force-reload`

## rrdtool

- `sudo apt-get install rrdtool`

- /var/www/rrdb => cputemp.sh

  - rrdtool create cputemp.rrd --step 300 DS:temp:GAUGE:600:-20:90 RRA:AVERAGE:0.5:12:24 RRA:AVERAGE:0.5:288:31
  - crontab

- `sudo apt-get install bc`


