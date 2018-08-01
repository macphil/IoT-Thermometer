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
                $start = $request->getQueryParam('start'); 
                if($start != null)
                {
                    $start = str_replace(".png", "", $start);
                    $parsedInterval = ParseAmountAndUnitFromTimeInterval($start);

                    header("HTTP/1.0 200 Ok");
                    header("Content-Type: image/png");
                    ThermometerRRD::GetGraph($parsedInterval['timeInterval'], $parsedInterval['title']);
                    break;                          
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

    function ParseAmountAndUnitFromTimeInterval($timeInterval)
    {
        $returnValue = array('timeInterval' => '1h', 'amount' => '1', 'unit' => 'h', 'title' => 'letzte Stunde');

        $pattern = "/([1-9]{1,}[0-9]*)([A-Za-z]{1,})/";
        if(preg_match($pattern, $timeInterval, $splitted) == 1)
        {
            $amount = $splitted[1];
            $unit =  $splitted[2];   
            switch ($splitted[2]) {
                case 's':
                case 'second':
                case 'seconds':
                    $title =  $amount==1?"letzte Sekunde" : sprintf("letzte %d Sekunden", $amount);
                    break;

                case 'h':
                case 'hour':
                case 'hours':
                    $title =  $amount==1?"letzte Stunde" : sprintf("letzte %d Stunden", $amount);
                    break;

                case 'm':
                case 'minute':
                case 'minutes':
                    $title =  $amount==1?"letzte Minute" : sprintf("letzte %d Minuten", $amount);
                    $unit = "minute";
                    break;

                case 'd':
                case 'day':
                case 'days':
                    $title =  $amount==1?"letzter Tag" : sprintf("letzte %d Tage", $amount);
                    break;

                case 'w':
                case 'week':
                case 'weekss':
                    $title =  $amount==1?"letzte Woche" : sprintf("letzte %d Wochen", $amount);
                    break;

                case 'M':
                case 'month':
                case 'months':
                    $title =  $amount==1?"letzter Monat" : sprintf("letzte %d Monate", $amount);
                    $unit = "month";
                    break;                    

                case 'y':
                case 'year':
                case 'years':
                    $title =  $amount==1?"letztes Jahr" : sprintf("letzte %d Jahre", $amount);
                    break;                    
                default:
                    $title = $splitted[0];
                    break;
            }

            $returnValue =  array('timeInterval' => $splitted[0], 'amount' => $amount, 'unit' => $unit, 'title' => $title);
        }
        return $returnValue;
    }

    function DebugLog($debug)
    {
        $logdate = new DateTime();
        $logEntry = $logdate->format(DateTime::ATOM) . ": $debug " . PHP_EOL;
        file_put_contents("/var/www/html/rrd2/debug.log", $logEntry, FILE_APPEND);
    }
?>