<?php

namespace Squirrel\Http;

/**
 * HTTP response object representation.
 *
 * @package Squirrel\Http
 * @author Valérian Galliat
 */
class Response
{
    /**
     * @var string[integer] List of status texts indexed by status codes.
     */
    public static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    );

    /**
     * @var integer
     */
    protected $status;

    /**
     * @var string[string]
     */
    protected $headers;

    /**
     * @var string
     */
    protected $body;

    /**
     * Instanciates a new response with optional status, headers and body.
     *
     * @param integer $status Optional HTTP status.
     * @param string[string] $headers Optional response headers.
     * @param string $body Optional body.
     */
    public function __construct($status = 200, array $headers = array(), $body = '')
    {
        $this->setStatus($status);
        $this->setHeaders($headers);
        $this->setBody($body);
    }

    /**
     * Returns response body.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getBody();
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @throws \InvalidArgumentException If given status is not a valid HTTP status.
     * @param integer $status
     * @return Response
     */
    public function setStatus($status)
    {
        if (!isset(self::$statusTexts[$status])) {
            throw new \InvalidArgumentException(sprintf('Given status %s is not a valid HTTP status', $status));
        }

        $this->status = $status;
        return $this;
    }

    /**
     * @return string[string] headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string[string] $headers
     * @return Response
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param string $name HTTP header name.
     * @param string $default Optional fallback value.
     * @return string|null
     */
    public function getHeader($name, $default = null)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    /**
     * @param string $name HTTP header name.
     * @param string $value HTTP header valud.
     * @return Response
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string body
     * @return Response
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Sends response headers to PHP.
     *
     * @return Response
     */
    public function sendHeaders()
    {
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        header(sprintf('%s %s %s', $protocol, $this->status, self::$statusTexts[$this->status]));

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        
        return $this;
    }

    /**
     * Sends response body to PHP.
     *
     * @return Response
     */
    public function sendBody()
    {
        echo $this->body;
        return $this;
    }

    /**
     * Outputs the whole response.
     *
     * @return Response
     */
    public function send()
    {
        return $this->sendHeaders()->sendBody();
    }
}
