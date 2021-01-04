## Erstellen

`pi@raspberrypi:/var/www/html/rrd2 $ rrdtool create temp_rh.rrd --step 60 DS:temp:GAUGE:120:U:U DS:rh:GAUGE:120:10:110 RRA:AVERAGE:0.5:5:576`



## rrdinfo

```bash
pi@raspberrypi:/var/www/html/rrd2 $ rrdtool info temp_rh.rrd
filename = "temp_rh.rrd"
rrd_version = "0003"
step = 60
last_update = 1530699877
header_size = 868
ds[temp].index = 0
ds[temp].type = "GAUGE"
ds[temp].minimal_heartbeat = 120
ds[temp].min = NaN
ds[temp].max = NaN
ds[temp].last_ds = "U"
ds[temp].value = NaN
ds[temp].unknown_sec = 37
ds[rh].index = 1
ds[rh].type = "GAUGE"
ds[rh].minimal_heartbeat = 120
ds[rh].min = 1,0000000000e+01
ds[rh].max = 1,1000000000e+02
ds[rh].last_ds = "U"
ds[rh].value = NaN
ds[rh].unknown_sec = 37
rra[0].cf = "AVERAGE"
rra[0].rows = 576
rra[0].cur_row = 187
rra[0].pdp_per_row = 5
rra[0].xff = 5,0000000000e-01
rra[0].cdp_prep[0].value = NaN
rra[0].cdp_prep[0].unknown_datapoints = 4
rra[0].cdp_prep[1].value = NaN
rra[0].cdp_prep[1].unknown_datapoints = 4
```





http://web-tech.ga-usa.com/2011/08/using-rrds-with-many-data-sources-rrdtool/index.html



https://oss.oetiker.ch/rrdtool/tut/rrd-beginners.en.html