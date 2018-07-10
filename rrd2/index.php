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
            case 'GET':
                GetLastUpdate();
                break;

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

            case 'PUT':
                $response = RRDGraph("1h");
                if($response['status'] == "ok")
                {
                    HTTPResponse::Ok200($response);
                }
                HTTPResponse::Error500($response);       
                break;
            default:
                HTTPResponse::Error405("GET, POST");
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
        // dir must be writable!
        //$command = "rrdtool create " . constant('RRDFILE') ." --step 60 DS:temp:GAUGE:120:U:U DS:rh:GAUGE:120:10:110 RRA:AVERAGE:0.5:5:576";
        
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

    function RRDGraph($start)
    {
        putenv("TZ=" . date_default_timezone_get());
        $now = new DateTime();
        $command  = "rrdtool graph img/$start.png";
        $command .= " --start -$start";
        $command .= " --title 'Temperatur ($start)'";
        $command .= " --vertical-label 'Grad Celsius'";
        $command .= sprintf(" --watermark 'last update: %s '", $now->format(DateTime::ATOM));
        $command .= " --font WATERMARK:8 ";
        $command .= " --font LEGEND:8:Mono";
        $command .= " --imgformat PNG";
        //$command .= " DEF:a0=" . constant('RRDFILE') . ":temp:AVERAGE";
        $command .= " DEF:a0=" . constant('RRDFILE') . ":temp:AVERAGE";
        $command .= " VDEF:a0cur=a0,LAST";
        $command .= " LINE1:a0#0000FF:temp";

        
        $exec = exec($command, $output, $returnVar);
        if($returnVar == 0)
        {
            $response = array('status' => 'ok', 'command' => $command);
        }
        else
        {
            $response = array('status' => 'error', 'returnVar' => $returnVar, 'command' => $command, 'output' => $output);
        }
        
        //$response = array('status' => 'ok');
        return $response;
    }


?>