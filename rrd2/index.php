<?php
    date_default_timezone_set('Europe/Berlin');
    define('RRDFILE', 'db/temp_rh.rrd');

    define('API_KEY', '1DFKTYNKSTGD38UX');

    include 'classes/request.class.php';
    include 'classes/HTTPResponse.class.php';

    Handle(new request());

    function Handle($request)
    {
        /*
        $logdate = new DateTime();
        $debug = print_r($request->__debugInfo(), true);
        $logEntry = $logdate->format(DateTime::ATOM) . ": $debug " . PHP_EOL;
        file_put_contents("/var/www/html/rrd2/debug.log", $logEntry, FILE_APPEND);
        */
        switch ($request->getMethod()) 
        { 
            // ==
            // == RRDLastUpdate
            // ==
            case 'GET':
                GetLastUpdate();
                break;
            // ==
            // == RRDUpdate
            // ==
            case 'POST':
                if($request->getQueryParam("api_key") == constant('API_KEY'))
                {
                    $temperature = $request->getQueryParam('temperature');
                    $humidity = $request->getQueryParam('humidity');
                    $timesstamp = $request->getQueryParam('timestamp');
                    if($temperature == null || $humidity == null)
                    {
                        HTTPResponse::Error400(array('the request is not valid!' => $request->getQueryParams()));
                    }                                     
                }
                if($request->isJson())
                {
                    $temperature = $request->getContent()['temperature'];
                    $humidity = $request->getContent()['humidity'];
                    $timesstamp = $request->getContent()['timestamp'];
                    if($temperature == null || $humidity == null)
                    {
                        HTTPResponse::Error400("the Request is not an valid json.");
                    }
                }

                if(!IsValidLogValue($temperature, $humidity))
                {
                    HTTPResponse::Error400(array('the given values are not valid!' => $request->getContent()));
                }
                $response = RRDUpdate(floatval($temperature), floatval($humidity), intval($timesstamp)); 
                if($response['status'] == "ok")
                {
                    GetLastUpdate();
                }
                HTTPResponse::Error500($response);
                break;
            // ==
            // == RRDGraph
            // ==
            case 'PUT':
                if($request->isJson())
                {
                    $start = $request->getContent()['start'];
                    if($start == null)
                    {
                        $start = "1h";
                    }
                }            
                $response = RRDGraph($start);
                if($response['status'] == "ok")
                {
                    $url = sprintf("http://%s%s/%s",
                        $_SERVER['SERVER_ADDR'],
                        dirname($_SERVER['REQUEST_URI']),
                        $response['filename']);
                    
                    HTTPResponse::Ok200(array("status" => "ok", "url" => $url));
                }
                HTTPResponse::Error500($response);       
                break;
            // ==
            // == RRDCreate
            // ==
            case 'PATCH':
                if($request->isJson())
                {
                    $start = $request->getContent()['override'];
                    if($start == "true")
                    {
                        $response = RRDCreate();
                        if($response['status'] == "ok")
                        {
                            HTTPResponse::Ok200($response);
                        }
                        HTTPResponse::Error500($response);  
                    }
                }   
                HTTPResponse::Error400("to confirm override, {'override':'true'} must be send!");  
                break; 
            case 'DELETE':
                HTTPResponse::Error418($request->__debugInfo());
                break;               
            default:
                HTTPResponse::Error405("GET, POST, PUT, PATCH");
                break;
        }
    }

    function GetLastUpdate()
    {
        $response = RRDLastUpdate();
        if($response['status'] == "ok")
        {
            HTTPResponse::Ok200($response['lastupdate']);
        }
        HTTPResponse::Error500($response);    
    }


    function IsValidLogValue($temperature, $humidity)
    {
        if (!is_numeric($temperature) || !is_numeric($humidity)) {
            return false;
        }

        if ($humidity <= 5) {
            return false;
        }

        return true;
    }

    function RRDUpdate($temperature,  $humidity, $timesstamp = 0)
    {
        if($timesstamp == 0)
        {
            $timesstamp = time();
        }
        $command = "rrdtool update " . constant('RRDFILE') ." $timesstamp:$temperature:$humidity";   
        $exec = exec($command, $output, $returnVar);
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

    function RRDLastUpdate()
    {
        $command = "rrdtool lastupdate " . constant('RRDFILE');
        
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

    function RRDCreate()
    {
        // dir must be writable!
        if(!is_writable(constant('RRDFILE')))
        {
            $response = array('status' => 'error', 'msg' => constant('RRDFILE') . " is not writable!");
        }

        $command  = "rrdtool create " .  constant('RRDFILE');
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

        
        $exec = exec($command, $output, $returnVar);
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

    function RRDGraph($start)
    {
        putenv("TZ=" . date_default_timezone_get());
        $filename = "img/$start.png";
        $now = new DateTime();
        $command  = "rrdtool graph $filename";
        $command .= " --start -$start";
        $command .= " --title 'Temperatur ($start)'";
        $command .= " --vertical-label 'Grad Celsius'";
        $command .= sprintf(" --watermark 'last update: %s '", $now->format(DateTime::ATOM));
        $command .= " --font WATERMARK:8 ";
        $command .= " --font LEGEND:8:Mono";
        $command .= " --imgformat PNG";
        $command .= " DEF:a0=" . constant('RRDFILE') . ":temp:AVERAGE";
        //$command .= " DEF:a0=db/cputemp.rrd:temp:AVERAGE";
        $command .= " VDEF:a0cur=a0,LAST";
        $command .= " LINE1:a0#0000FF:temp";

        
        $exec = exec($command, $output, $returnVar);
        if($returnVar == 0)
        {
            $response = array('status' => 'ok', 'filename' => $filename);
        }
        else
        {
            $response = array('status' => 'error', 'returnVar' => $returnVar, 'command' => $command, 'output' => $output);
        }
        
        //$response = array('status' => 'ok');
        return $response;
    }


?>