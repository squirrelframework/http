<?php

namespace Squirrel\Http;

/**
 * HTTP request object representation.
 *
 * @package Squirrel\Http
 * @author Valérian Galliat
 */
class Request
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $scheme;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $pass;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var integer
     */
    protected $port;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $scriptPath;

    /**
     * @var mixed[string]
     */
    protected $search;

    /**
     * @var string[string]
     */
    protected $headers;

    /**
     * @var mixed[string]
     */
    protected $post;

    /**
     * @var array[string]
     */
    protected $files;

    /**
     * @var string
     */
    protected $payload;

    /**
     * @var string[string]
     */
    protected $params;

    /**
     * Initializes all class properties with default values
     * or context values if given.
     *
     * Some context values can override URL data.
     *
     * @throws \InvalidArgumentException
     * @param string url
     * @param mixed[string] context
     */
    public function __construct($url, array $context = array())
    {
        $this->setDefaultMethod();
        $this->setDefaultScheme();
        $this->setDefaultHost();
        $this->setDefaultPort();
        $this->setUrl($url);

        if (isset($context['method'])) {
            $this->setMethod($context['method']);
        }
        
        if (isset($context['scheme'])) {
            $this->setScheme($context['scheme']);
        }
        
        if (isset($context['user'])) {
            $this->setUser($context['user']);
        }
        
        if (isset($context['pass'])) {
            $this->setPass($context['pass']);
        }

        if (isset($context['host'])) {
            $this->setHost($context['host']);
        }
        
        if (isset($context['port'])) {
            $this->setPort($context['port']);
        }
        
        if (isset($context['path'])) {
            $this->setPath($context['path']);
        }
        
        if (isset($context['basePath'])) {
            $this->setBasePath($context['basePath']);
        }

        if (isset($context['scriptPath'])) {
            $this->setScriptPath($context['scriptPath']);
        }

        if (isset($context['search'])) {
            $this->setSearchVars($context['search']);
        }

        isset($context['headers']) ? $this->setHeaders($context['headers']) : $this->clearHeaders();
        isset($context['post']) ? $this->setPostVars($context['post']) : $this->clearPostVars();
        isset($context['files']) ? $this->setFiles($context['files']) : $this->clearFiles();
        isset($context['payload']) ? $this->setPayload($context['payload']) : $this->clearPayload();
        isset($context['params']) ? $this->setParams($context['params']) : $this->clearParams();
    }

    /**
     * Creates a request object from PHP global variables.
     *
     * @return Request
     */
    public static function createFromGlobals()
    {
        $headers = array();

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) !== 'HTTP_') {
                continue;
            }

            $httpName = substr($name, 5);
            $httpName = str_replace('_', ' ', $httpName);
            $httpName = strtolower($httpName);
            $httpName = ucwords($httpName);
            $httpName = str_replace(' ', '-', $httpName);

            $headers[$httpName] = $value;
        }

        return new static($_SERVER['REQUEST_URI'], array(
            'method' => $_SERVER['REQUEST_METHOD'],
            'scheme' => isset($_SERVER['HTTPS']) ? 'https' : 'http',
            'user' => isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null,
            'pass' => isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null,
            'host' => $_SERVER['HTTP_HOST'],
            'port' => $_SERVER['SERVER_PORT'],
            'basePath' => $_SERVER['SCRIPT_NAME'],
            'search' => $_GET,
            'headers' => $headers,
            'post' => $_POST,
            'files' => $_FILES,
            'payload' => file_get_contents('php://input')
        ));
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $url = $this->scheme . '://' . $this->host;

        if (isset($this->port)) {
            $url .= ':' . $this->port;
        }

        $url .= $this->path;

        if (empty($this->search)) {
            $url .= '?' . http_build_query($this->search);
        }
    }

    /**
     * @param string $url
     * @return Request
     */
    public function setUrl($url)
    {
        $parts = parse_url($url);

        if (isset($parts['host'])) {
            $this->setHost($parts['host']);
        }

        if (isset($parts['port'])) {
            $this->setPort($parts['port']);
        }

        if (isset($parts['path'])) {
            $this->setPath($parts['path']);
        }

        if (isset($parts['scheme'])) {
            $this->setScheme($parts['scheme']);
        }

        if (isset($parts['user'])) {
            $this->setUser($parts['user']);
        }

        if (isset($parts['pass'])) {
            $this->setPass($parts['pass']);
        }

        if (isset($parts['fragment'])) {
            $this->setFragment($parts['fragment']);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return Request
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * @return Request
     */
    public function setDefaultMethod()
    {
        $this->method = 'GET';
        return $this;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @throws \InvalidArgumentException If the scheme is not supported.
     * @param string $scheme
     * @return Request
     */
    public function setScheme($scheme)
    {
        if ($scheme !== 'http' && $scheme !== 'https') {
            throw new \InvalidArgumentException(sprintf('Unsupported scheme "%s".', $scheme));
        }

        $this->scheme = $scheme;

        if ($this->port === 80) {
            $this->setDefaultPort();
        }

        return $this;
    }

    /**
     * @return Request
     */
    public function setDefaultScheme()
    {
        $this->scheme = 'http';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     * @return Request
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Request
     */
    public function removeUser()
    {
        $this->user = null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param string $pass
     * @return Request
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
        return $this;
    }

    /**
     * @return Request
     */
    public function removePass()
    {
        $this->pass = null;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return Request
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return Request
     */
    public function setDefaultHost()
    {
        $this->host = 'localhost';
        return $this;
    }

    /**
     * @return integer|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param integer $port
     * @return Request
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return Request
     */
    public function setDefaultPort()
    {
        $this->port = $this->getScheme() === 'https' ? 443 : 80;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return Request
     */
    public function setPath($path)
    {
        $this->path = $this->fixPath($path);
        $this->computePaths();
        return $this;
    }

    /**
     * @return Request
     */
    public function removePath()
    {
        $this->path = '/';
        $this->computePaths();
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasBasePath()
    {
        return isset($this->basePath);
    }

    /**
     * @return string|null
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param string $basePath
     * @return Request
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $this->fixPath($basePath);
        $this->computePaths();
        $this->computePath();
        return $this;
    }

    /**
     * @return Request
     */
    public function removeBasePath()
    {
        $this->basePath = null;
        $this->computePaths();
        $this->computePath();
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasScriptPath()
    {
        return isset($this->scriptPath);
    }

    /**
     * @return string|null
     */
    public function getScriptPath()
    {
        return $this->scriptPath;
    }

    /**
     * @param string $scriptPath
     * @return Request
     */
    public function setScriptPath($scriptPath)
    {
        $this->scriptPath = $this->fixPath($scriptPath);
        $this->computePaths();
        $this->computePath();
        return $this;
    }

    /**
     * @return Request
     */
    public function removeScriptPath()
    {
        $this->scriptPath = null;
        $this->computePaths();
        $this->computePath();
        return $this;
    }

    /**
     * @return mixed[string]
     */
    public function getSearchVars()
    {
        return $this->search;
    }

    /**
     * @param mixed[string] $search
     * @return Request
     */
    public function setSearchVars(array $search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return Request
     */
    public function clearSearchVars()
    {
        $this->search = array();
        return $this;
    }

    /**
     * @param string $name
     * @param string|array|null $default
     * @return string|array|null
     */
    public function getSearch($name, $default = null)
    {
        return isset($this->search[$name]) ? $this->search[$name] : $default;
    }

    /**
     * @param string $name
     * @param string|array $value
     * @return Request
     */
    public function setSearch($name, $value)
    {
        $this->search[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return Request
     */
    public function removeSearch($name)
    {
        unset($this->search[$name]);
        return $this;
    }

    /**
     * @return string[string]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string[string] $headers
     * @return Request
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return Request
     */
    public function clearHeaders()
    {
        $this->headers = array();
        return $this;
    }

    /**
     * @param string $name
     * @param string|null $default
     * @return string|null
     */
    public function getHeader($name, $default = null)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : $default;
    }

    /**
     * @param string $name
     * @param string $value
     * @return Request
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return Request
     */
    public function removeHeader($name)
    {
        unset($this->headers[$name]);
        return $this;
    }

    /**
     * @return mixed[string]
     */
    public function getPostVars()
    {
        return $this->post;
    }

    /**
     * @param mixed[string] $post
     * @return Request
     */
    public function setPostVars(array $post)
    {
        $this->post = $post;
        return $this;
    }

    /**
     * @return Request
     */
    public function clearPostVars()
    {
        $this->post = array();
        return $this;
    }

    /**
     * @param string $name
     * @param string|array|null $default
     * @return string|array|null
     */
    public function getPost($name, $default = null)
    {
        return isset($this->post[$name]) ? $this->post[$name] : $default;
    }

    /**
     * @param string $name
     * @param string|array $value
     * @return Request
     */
    public function setPost($name, $value)
    {
        $this->post[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return Request
     */
    public function removePost($name)
    {
        unset($this->post[$name]);
        return $this;
    }

    /**
     * @return array[string] files
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param array[string] $files
     * @return Request
     */
    public function setFiles(array $files)
    {
        $this->files = $files;
        return $this;
    }

    /**
     * @return Request
     */
    public function clearFiles()
    {
        $this->files = array();
        return $this;
    }

    /**
     * @param string $name
     * @param array|null $default
     * @return array|null
     */
    public function getFile($name, array $default = null)
    {
        return isset($this->files[$name]) ? $this->files[$name] : $default;
    }

    /**
     * @param string $name
     * @param array $value
     * @return Request
     */
    public function setFile($name, array $value)
    {
        $this->files[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return Request
     */
    public function removeFile($name)
    {
        unset($this->files[$name]);
        return $this;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param string $payload
     * @return Request
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @return Request
     */
    public function clearPayload()
    {
        $this->payload = '';
        return $this;
    }

    /**
     * @return string[string]
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string[string] $params
     * @return Request
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return Request
     */
    public function clearParams()
    {
        $this->params = array();
        return $this;
    }

    /**
     * @param string $name
     * @param string|null $default
     * @return string|null
     */
    public function getParam($name, $default = null)
    {
        return isset($this->params[$name]) ? $this->params[$name] : $default;
    }

    /**
     * @param string $name
     * @param string $value
     * @return Request
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return Request
     */
    public function removeParam($name)
    {
        unset($this->params[$name]);
        return $this;
    }

    /**
     * Computes path with base path and script path.
     */
    protected function computePath()
    {
        $this->fixPaths();
        $this->path = $this->basePath . ($this->scriptPath === '/' ? '' : $this->scriptPath);
    }

    /**
     * Computes base path and script path using path.
     */
    protected function computePaths()
    {
        if (!$this->hasBasePath() && !$this->hasScriptPath()) {
            return;
        }

        $this->fixPaths();

        $entry = basename($this->basePath);
        $baseFolder = dirname($this->basePath);

        if (substr($this->path, 0, strlen($baseFolder)) !== $baseFolder) {
            $this->basePath = null;
            $this->scriptPath = null;
            return;
        }

        if (substr($this->path, strlen($baseFolder) + 1, strlen($entry)) === $entry) {
            $this->scriptPath = substr($this->path, strlen($this->basePath));
        } else {
            $this->basePath = $baseFolder;
            $this->scriptPath = substr($this->path, strlen($baseFolder));
        }

        if ($this->scriptPath === false) {
            $this->scriptPath = '/';
        }
    }

    /**
     * Fixes given path of trailing slashes.
     *
     * @param string $path
     * @return string
     */
    protected function fixPath($path)
    {
        $path = rtrim($path, '/');

        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }

        return $path;
    }

    /**
     * Fixes base path and script path values.
     */
    protected function fixPaths()
    {        
        if ($this->hasBasePath() && $this->basePath === '/') {
            $this->basePath = '';
        }

        if ($this->hasBasePath() && !$this->hasScriptPath()) {
            $this->scriptPath = '/';
        } elseif ($this->hasScriptPath() && !$this->hasBasePath()) {
            $this->basePath = '';
        }
    }
}
