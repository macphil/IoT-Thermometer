<?php
    define('RRDFILE', 'db/temp_rh.rrd');

    include 'classes/request.class.php';
    include 'classes/HTTPResponse.class.php';

    Handle(new request());

    function Handle($request)
    {
        switch ($request->getMethod()) 
        {
            case 'GET':
                $response = RRDLastUpdate();
                if($response['status'] == "ok")
                {
                    HTTPResponse::Ok200($response['lastupdate']);
                }
                HTTPResponse::Error500($response);       
                break;

            case 'POST':
                if(!$request->isJson())
                {
                    HTTPResponse::Error400("the Request is not an valid json.");
                }
                $temperature = $request->getContent()['temperature'];
                $humidity = $request->getContent()['humidity'];

                if(!IsValidLogValue($temperature, $humidity))
                {
                    HTTPResponse::Error400(array('the given values are not valid!' => $request->getContent()));
                }
                $response = RRDUpdate(floatval($temperature), floatval($humidity)); 
                if($response['status'] == "ok")
                {
                    HTTPResponse::Ok200('ok');
                }
                HTTPResponse::Error500($response);
                break;

            case 'PATCH':
                // break;
                // rrdgraph
            case 'PUT':
                // break;
                // rrdtool create
            default:
                HTTPResponse::Error405("GET, POST");
                break;
        }
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
            $response = array('status' => 'error', 'returnVar' => $returnVar, 'output' => $output);
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
            $response = array('status' => 'error', 'returnVar' => $returnVar, 'output' => $output);
        }
        
        return $response;
    }


?>