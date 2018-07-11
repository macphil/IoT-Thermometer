<?php
class ThermometerRRD
{
    const RRDFILE = "db/temp_rh.rrd";
    
    public static function Update($temperature,  $humidity, $timesstamp = 0)
    {
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


    public static function CreateGraph($start)
    {
        putenv("TZ=" . date_default_timezone_get());
        $filename = "img/$start.png";
        $now = new DateTime();
        $command  = "rrdtool graph $filename";
        $command .= " --start -$start";
        $command .= " --title 'Temperatur ($start)'";
        $command .= " --vertical-label 'Grad Celsius'";
        $command .= sprintf(" --watermark 'created at %s '", $now->format(DateTime::ATOM));
        $command .= " --font WATERMARK:8 ";
        $command .= " --font LEGEND:8:Mono";
        $command .= " --imgformat PNG";
        $command .= " DEF:a0=" . ThermometerRRD::RRDFILE . ":temp:AVERAGE";
        $command .= " VDEF:a0cur=a0,LAST";
        $command .= " LINE1:a0#0000FF:temp";
        
        exec($command, $output, $returnVar);
        if($returnVar == 0)
        {
            $response = array('status' => 'ok', 'filename' => $filename);
        }
        else
        {
            $response = array('status' => 'error', 'returnVar' => $returnVar, 'command' => $command, 'output' => $output);
        }
        
        return $response;
    }


    public static function CreateRRD()
    {
        if(!is_writable(ThermometerRRD::RRDFILE))
        {
            $response = array('status' => 'error', 'msg' => ThermometerRRD::RRDFILE . " is not writable!");
        }

        $command  = "rrdtool create " .  ThermometerRRD::RRDFILE;
        $command .= " --step 60 ";
        $command .= "DS:temp:GAUGE:240:U:U ";
        $command .= "DS:rh:GAUGE:240:10:110 ";
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

        
        //exec($command, $output, $returnVar);
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