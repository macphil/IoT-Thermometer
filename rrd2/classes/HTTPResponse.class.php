<?php
class HTTPResponse
{
    const ContentType = "Content-Type: application/json";
    const AccessControl = "Access-Control-Allow-Origin: *";
    const HTTP200 = '200 Ok';
    const HTTP400 = '400 Bad Request';
    const HTTP405 = '405 Method Not Allowed';
    const HTTP500 = '500 Internal Server Error';

    protected function SendHeaders($HTTPStatuscode)
    {
        header("HTTP/1.0 " . $HTTPStatuscode);
        header(ContentType);
        header(AccessControl);
    }

    public static function Ok200($message)
    {
        self::SendHeaders(self::HTTP200);
        echo json_encode($message);
        die;
    }   

    public static function Error400($message)
    {
        self::SendHeaders(self::HTTP400);
        echo json_encode($message);
        die;
    }

    public static function Error405($allowed)
    {
        self::SendHeaders(self::HTTP405);
        header("Allow: $allowed");
        die;
    }    

    public static function Error500($message)
    {
        self::SendHeaders(self::HTTP500);
        echo json_encode($message);
        die;
    }

}
?>
