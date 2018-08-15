<?php
class ThermometerRRD
{
    const RRDFILE = "db/temp_rh.rrd";
    
    public static function Update($temperature,  $humidity, $timesstamp = 0)
    {
        // --
        // see https://oss.oetiker.ch/rrdtool/doc/rrdupdate.en.html
        // --
        if($timesstamp == 0)
        {
            $timesstamp = time();
        }
        $command = "rrdtool update " . ThermometerRRD::RRDFILE ." $timesstamp:$temperature:$humidity";   
        exec($command, $output, $returnVar);
        if($returnVar == 0)
        {
            $response = array('status' => 'ok');
        }
        else
        {
            $response = array('status' => 'error', 'returnVar' => $returnVar, 'command' => $command, 'output' => $output);
        }
        
        return $response;
    }


    public static function GetLastUpdate()
    {
        // --
        // see https://oss.oetiker.ch/rrdtool/doc/rrdlastupdate.en.html
        // --
        $command = "rrdtool lastupdate " . ThermometerRRD::RRDFILE;
        
        exec($command, $output, $returnVar);

        if($returnVar == 0 && is_array($output) && count($output) == 3)
        {
            $values = explode(' ', $output[2]);            
            $lastupdate = array('timestamp' => trim($values[0],":"), 'temperature' => $values[1], 'humidity' => $values[2]);
            $response = array('status' => 'ok', 'lastupdate' => $lastupdate);
        }
        else
        {
            $response = array('status' => 'error', 'returnVar' => $returnVar, 'command' => $command, 'output' => $output);
        }
       
        return $response;
    }


    public static function GetGraph($start, $title)
    {
        // --
        // see https://oss.oetiker.ch/rrdtool/doc/rrdgraph.en.html
        // --              
        putenv("TZ=" . date_default_timezone_get());   
        
        $filename = "-"; // filename can be '-' to send the image to stdout. In this case, no other output is generated.
        $now = new DateTime();
        $gformat = "%6.1lf%s°C";

        $command  = "rrdtool graph $filename";
        $command .= " --start -$start";
        $command .= " --title 'Temperatur & rel. Luftfeuchte ($title)'";
        $command .= " --width 600 --height 300";
        $command .= " --vertical-label 'Grad Celsius'";
        $command .= " --upper-limit 20";
        $command .= " --lower-limit 0";
        $command .= " --right-axis-label 'Rel %'";
        $command .= " --right-axis 5:0";
        $command .= " --slope-mode";
        $command .= sprintf(" --watermark 'created at %s '", $now->format(DateTime::ATOM));
        $command .= " --font WATERMARK:8 ";
        $command .= " --font LEGEND:8:Mono";
        $command .= " --imgformat PNG";
        //-- humidity
        $command .= " DEF:rh1=" . ThermometerRRD::RRDFILE . ":rh:AVERAGE";       
        $command .= " CDEF:rh1area=rh1,0.2,*";
        $command .= " VDEF:rh1cur=rh1,LAST";
        $command .= " VDEF:rh1max=rh1,MAXIMUM";
        $command .= " VDEF:rh1avg=rh1,AVERAGE";
        $command .= " VDEF:rh1min=rh1,MINIMUM";         
        //-- temperature          
        $command .= " DEF:temp0=" . ThermometerRRD::RRDFILE . ":temp:AVERAGE";
        $command .= " VDEF:temp0cur=temp0,LAST";
        $command .= " VDEF:temp0max=temp0,MAXIMUM";
        $command .= " VDEF:temp0avg=temp0,AVERAGE";
        $command .= " VDEF:temp0min=temp0,MINIMUM"; 
        
        $command .= " COMMENT:\"\r\t\t    \"";
        $command .= " COMMENT:\"last     \"";
        $command .= " COMMENT:\"mimimum  \"";
        $command .= " COMMENT:\"average  \"";
        $command .= " COMMENT:\"maximum  \"";
        $command .= " COMMENT:\"\r\l\"";     
        //-- humidity first (area in background)
        $command .= " AREA:rh1area#00ff00:Luftfeuchtigkeit";   
        $command .= " COMMENT:\"\"";               
        $command .= " GPRINT:rh1cur:$gformat";
        $command .= " GPRINT:rh1min:$gformat";
        $command .= " GPRINT:rh1avg:$gformat";
        $command .= " GPRINT:rh1max:$gformat";
        $command .= " COMMENT:\"\l\"";        
        
        $command .= " LINE1:temp0#000000:Temperatur";
        $command .= " COMMENT:\"    \"";        
        $command .= " GPRINT:temp0cur:$gformat";
        $command .= " GPRINT:temp0min:$gformat";
        $command .= " GPRINT:temp0avg:$gformat";
        $command .= " GPRINT:temp0max:$gformat";
        $command .= " COMMENT:\"\l\"";
        
        
        passthru($command);
        exit();
    }


    public static function CreateRRD()
    {
        // --
        // see https://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html
        // --
        if(!is_writable(ThermometerRRD::RRDFILE))
        {
            $response = array('status' => 'error', 'msg' => ThermometerRRD::RRDFILE . " is not writable!");
        }

        $command  = "rrdtool create " .  ThermometerRRD::RRDFILE;
        $command .= " --step 60 ";
        // -- updated by: pi@raspberrypi:/var/www/html $ rrdtool tune rrd2/db/temp_rh.rrd -h temp:310
        $command .= "DS:temp:GAUGE:310:U:U ";
        $command .= "DS:rh:GAUGE:310:10:110 ";
        $command .= "RRA:AVERAGE:0.5:1:1440 ";
        $command .= "RRA:AVERAGE:0.5:30:432 ";
        $command .= "RRA:AVERAGE:0.5:120:540 ";
        $command .= "RRA:AVERAGE:0.5:1440:450 ";
        $command .= "RRA:MAX:0.5:1:1440 ";
        $command .= "RRA:MAX:0.5:30:432 ";
        $command .= "RRA:MAX:0.5:120:540 ";
        $command .= "RRA:MAX:0.5:1440:450 ";
        $command .= "RRA:MIN:0.5:1:1440 ";
        $command .= "RRA:MIN:0.5:30:432 ";
        $command .= "RRA:MIN:0.5:120:540 ";
        $command .= "RRA:MIN:0.5:1440:450 ";

        
        exec($command, $output, $returnVar);
        if($returnVar == 0)
        {
            $response = array('status' => 'ok', 'command' => $command);
        }
        else
        {
            $response = array('status' => 'error', 'returnVar' => $returnVar, 'command' => $command, 'output' => $output);
        }

        return $response;  
    }    
}
?>