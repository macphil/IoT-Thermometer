<?php
class HTTPResponse
{
    const ContentType = "Content-Type: application/json";
    const AccessControl = "Access-Control-Allow-Origin: *";
    const HTTP200 = '200 Ok';
    const HTTP303 = '303 See Other';
    const HTTP400 = '400 Bad Request';
    const HTTP405 = '405 Method Not Allowed';
    const HTTP418 = '418 I\'m a teapot';
    const HTTP500 = '500 Internal Server Error';

    protected function SendHeaders($HTTPStatuscode)
    {
        header("HTTP/1.0 " . $HTTPStatuscode);
        header(self::ContentType);
        header(self::AccessControl);
    }

    public static function Ok200($message)
    {
        self::SendHeaders(self::HTTP200);
        echo json_encode($message, JSON_UNESCAPED_SLASHES);
        die;
    }    

    public static function Redirect303($location)
    {
        self::SendHeaders(self::HTTP303);
        header("Location: $location");
        die;
    }    

    public static function Error400($message)
    {
        self::SendHeaders(self::HTTP400);
        echo json_encode($message, JSON_UNESCAPED_SLASHES);
        die;
    }

    public static function Error405($allowed)
    {
        self::SendHeaders(self::HTTP405);
        header("Allow: $allowed");
        echo json_encode($message, JSON_UNESCAPED_SLASHES);
        die;
    }
    
    public static function Error418($message)
    {
        self::SendHeaders(self::HTTP418);
        $teapod = array('message' => $message, '$_SERVER' => $_SERVER, 'see' => 'https://tools.ietf.org/html/rfc2324#section-6.5.14');
        echo json_encode($teapod, JSON_UNESCAPED_SLASHES);
        die;
    }       

    public static function Error500($message)
    {
        self::SendHeaders(self::HTTP500);
        echo json_encode($message, JSON_UNESCAPED_SLASHES);
        die;
    }

}
?>
