<?php

/**
 * Handler for request and mapped into, controller, metohd and requests.
 *
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @link	http://panadaframework.com/
 *
 * @license	http://www.opensource.org/licenses/bsd-license.php
 *
 * @since	version 0.1
 */
namespace Resources;

final class Uri
{
    private $pathUri = array();
    public $baseUri;
    public $defaultController;
    public $requestScheme;
    public $frontController;
    public static $staticDefaultController = 'Home';

    /**
     * Class constructor.
     *
     * Difine the SAPI mode, cli or web/http
     */
    public function __construct()
    {
        if (PHP_SAPI == 'cli') {
            $this->pathUri = array_slice($_SERVER['argv'], 1);

            return;
        }

        $this->requestScheme = ($this->isHttps()) ? 'https://':'http://';

        $scriptPath = explode('/', $_SERVER['SCRIPT_FILENAME']);
        $this->frontController = end($scriptPath);

        $docRoot = str_replace('/'.$this->frontController, '', $_SERVER['DOCUMENT_ROOT']);
        $scriptFile =  str_replace('/'.$this->frontController, '', $_SERVER['SCRIPT_FILENAME']);
        $requestURI = str_replace('/'.$this->frontController, '', $_SERVER['REQUEST_URI']);
        $this->basePath = str_replace($docRoot, '', $scriptFile);

        $this->baseUri = $this->requestScheme.$_SERVER['HTTP_HOST'].$this->basePath.'/';
        $requestURI = str_replace($this->basePath, '', $requestURI);
        $this->pathInfo = trim(strtok($requestURI, '?'), '/');
        $this->pathUri = explode('/', $this->pathInfo);
        $this->defaultController = self::$staticDefaultController;
    }

    /**
     * Does this site use https?
     *
     * @return bool
     */
    public function isHttps()
    {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS'])) {
                return true;
            }
            if ('1' == $_SERVER['HTTPS']) {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }

        return false;
    }

    /**
     * Clean the 'standard' model query.
     *
     * @param string
     *
     * @return string
     */
    public function removeQuery($path)
    {
        $pathAr = explode('?', $path);
        if (count($pathAr) > 0) {
            $path = $pathAr[0];
        }

        return $path;
    }

    /**
     * Break the string given from extractUriString() into class, method and request.
     *
     * @param    int
     *
     * @return string
     */
    public function path($segment = false)
    {
        if ($segment !== false) {
            return (isset($this->pathUri[$segment]) && $this->pathUri[$segment] != $this->frontController) ? $this->pathUri[$segment] : false;
        }

        return $this->pathUri;
    }

    /**
     * Get class name from the url.
     *
     * @return string
     */
    public function getClass()
    {
        if ($uriString = $this->path(0)) {
            if ($this->stripUriString($uriString)) {
                return $uriString;
            }

            throw new RunException('Invalid controller name: '.htmlentities($uriString));
        }

        return $this->defaultController;
    }

    /**
     * Get method name from the url.
     *
     * @return string
     */
    public function getMethod($default = 'index')
    {
        $uriString = $this->path(1);

        if (isset($uriString) && !empty($uriString)) {
            if ($this->stripUriString($uriString)) {
                return $uriString;
            }

            return '';
        }

        return $default;
    }

    /**
     * Get "GET" request from the url.
     *
     * @param    int
     *
     * @return array
     */
    public function getRequests($segment = 2)
    {
        $uriString = $this->path($segment);

        if (isset($uriString)) {
            return array_slice($this->path(), $segment);
        }

        return false;
    }

    /**
     * Cleaner for class and method name.
     *
     * @param string
     *
     * @return bool
     */
    public function stripUriString($uri)
    {
        return (!preg_match('/[^a-zA-Z0-9_.-]/', $uri)) ? true : false;
    }

    /**
     * Setter for default controller.
     *
     * @param string $defaultController
     */
    public function setDefaultController($defaultController)
    {
        $this->defaultController = self::$staticDefaultController = $defaultController;
    }

    /**
     * Getter for default controller.
     */
    public function getDefaultController()
    {
        return $this->defaultController;
    }

    /**
     * Getter for baseUri.
     *
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }
}
