# Grafik-Ausgabe

## Variante 1: Erstellen per Crontab

++ einfache Erstellung

-- wird zu oft erstellt (IO-Last :-))

## Variante 2: Erstellen "on demand"

-- Etwas Aufwendiger:



### Mod-Rewrite

```bash
macphil@ubuntu-2gb-nbg1-dc3-1:~/backupspace/vps/private/108/home/rootpfad/_unused/quota$ more .htaccess
RewriteEngine On

# This will rewrite web12.png => rootpfad.php?id=12
RewriteRule ^web([0-9]{1,2})\.png$ createQuotaPNG.php?id=$1

Options -Indexes
```



### Ausgabe an Browser

Genutzt in `createQuotaPNG.php`:

```php
include("pChart/pData.class");
include("pChart/pChart.class");
$Chart->Stroke("rootpfad.png");
```

http://wiki.pchart.net/doc.faq.script.output.html

> Calling the Stroke() method in your script will automatically send the ‘*Content-type: image/png*‘ header to the user web browser and the raw picture in the HTTP GET data field. 

Entsprechendes bei RRDGRAPH

> *filename* can be '`-`' to send the image to `stdout`. In this case, no other output is generated.

https://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html:
