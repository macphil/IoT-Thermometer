<?php
    date_default_timezone_set('Europe/Berlin');
    define('RRDFILE', 'db/temp_rh.rrd');

    define('API_KEY', '1DFKTYNKSTGD38UX');

    include 'classes/request.class.php';
    include 'classes/HTTPResponse.class.php';
    include 'classes/ThermometerRRD.class.php';

    Handle(new request());

    function Handle($request)
    {
        switch ($request->getMethod()) 
        { 
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
                $response = ThermometerRRD::Update(floatval($temperature), floatval($humidity), intval($timesstamp)); 
                if($response['status'] != "ok")
                {
                    HTTPResponse::Error500($response);
                }                
                // no break to return last update
            // ==
            // == RRDLastUpdate / RRDGraph
            // ==
            case 'GET':
                $img = $request->getQueryParam('img'); 
                if($img != null)
                {
                    $start = str_replace(".png", "", $img);
                    //DebugLog($start);
                    header("HTTP/1.0 200 Ok");
                    header("Content-Type: image/png");
                    ThermometerRRD::GetGraph($start);              
                }
                            
                $response = ThermometerRRD::GetLastUpdate();                
                if($response['status'] == "ok")
                {
                    HTTPResponse::Ok200($response['lastupdate']);
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
                        $response = ThermometerRRD::CreateRRD();
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
                HTTPResponse::Error405("GET, POST, PATCH");
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

    function DebugLog($debug)
    {
        $logdate = new DateTime();
        $logEntry = $logdate->format(DateTime::ATOM) . ": $debug " . PHP_EOL;
        file_put_contents("/var/www/html/rrd2/debug.log", $logEntry, FILE_APPEND);
    }
?>