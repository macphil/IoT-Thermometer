<?php
class Request
{
    protected $method;
    protected $httpAccept;
    protected $uri;
    protected $currentDir;
    protected $requestTarget;
    protected $queryParams;
    protected $content;
    protected $json_last_error;


    public function __construct()
    {

    }

    public function isJson()
    {
        $this->getContent();
        return !is_null($this->content) && $this->content && $this->json_last_error == JSON_ERROR_NONE;
    }

    public function getMethod()
    {
        if ($this->method === null)
        {
            $this->method = $_SERVER['REQUEST_METHOD'];
        }
        return $this->method;
    }

    public function getContent()
    {
        if($this->content === null)
        {
            $this->content = json_decode(file_get_contents('php://input'), true);
            $this->json_last_error = json_last_error();
        }
        return $this->content;
    }

    public function getQueryParams()
    {
        if($this->queryParams === null)
        {
            $this->queryParams = ($_GET);
        }
        return $this->queryParams;
    }

    public function getQueryParam($key)
    {
        if($this->queryParams === null)
        {
            $this->queryParams = ($_GET);
        }
        return @$this->queryParams[$key];
    }    

    public function getUri()
    {
        if($this->uri === null)
        {
            $this->uri = $_SERVER['REQUEST_URI'];
        }
        return $this->uri;
    }

    public function getEndpoint()
    {
        if($this->currentDir === null)
        {
            $this->currentDir = dirname($_SERVER['SCRIPT_NAME']);
        }

        $endpoint = $this->getUri();
        $endpoint = str_replace($this->currentDir, "", $endpoint);
        $endpoint = str_replace($this->getQueryParams(),"", $endpoint);
        $endpoint = str_replace("?", "", $endpoint);
        $this->endpoint = trim($endpoint, "/");
        return $this->endpoint;
    }

    public function isEndpoint($endpoint)
    {
        return $this->getEndpoint() === trim($endpoint,'/');
    }

    public function isGet()
    {
        return $this->isMethod('GET');
    }
    public function isPost()
    {
        return $this->isMethod('POST');
    }
    public function isPut()
    {
        return $this->isMethod('PUT');
    }
    public function isDelete()
    {
        return $this->isMethod('DELETE');
    }

    public function __debugInfo()
    {
        return [
            'method' => $this->getMethod(),
            'uri' => $this->getUri(),
            'endpoint' => $this->getEndpoint(),
            'isJson' => $this->isJson(),
            'query' => $this->getQueryParams(),
            'content' => $this->getContent(),
        ];
    }

    // returns true if $needle is a substring of $haystack
    private function contains($needle, $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }

    private function isMethod($method)
    {
        return $this->getMethod() === $method;
    }
}
?>
