<?php
/**
 * Internal URL Writer
 * This script contains an helper to write internal URL
 *
 * @license MIT
 * @copyright 2015 Tommy Teasdale
 */

declare(strict_types=1);

namespace Apine\Core\Utility;

use Apine\Core\Http\Factories\UriFactory;
use Apine\Core\Http\Request;
use const Apine\Core\PROTOCOL_HTTP;
use const Apine\Core\PROTOCOL_HTTPS;
use const Apine\Core\PROTOCOL_DEFAULT;

/**
 * Internal URL Writer
 * Write URL from server's information
 *
 * @author Tommy Teasdale <tteasdaleroads@gmail.com>
 * @package Apine\MVC
 */
final class URLHelper
{
    /**
     * Instance of the URL Writer
     * Singleton Implementation
     *
     * @var URLHelper
     */
    private static $instance;
    
    /**
     * Server Domain Name
     *
     * @var string
     */
    private $authority;
    
    /**
     * Main Server's Domain Name
     *
     * @var string
     */
    private $mainAuthority;
    
    /**
     * Path of the current session
     *
     * @var string
     */
    private $path;
    
    /**
     * @var \Psr\Http\Message\UriInterface
     */
    private $uri;
    
    /**
     * Construct the URL Writer helper
     * Extract string from server configuration
     */
    private function __construct()
    {
        $uri = (new UriFactory())->createUriFromArray($_SERVER);
    
        $hostArray = explode('.', $uri->getAuthority());
    
        if (count($hostArray) >= 3) {
            $start = strlen($hostArray[0]) + 1;
            $this->mainAuthority = substr($uri->getAuthority(), $start);
        } else {
            $this->mainAuthority = $uri->getAuthority();
        }
        
        $this->authority = $uri->getAuthority();
        $this->path = $uri->getPath();
        $this->uri = $uri;
    }
    
    /**
     * Singleton design pattern implementation
     *
     * @static
     * @return URLHelper
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        
        return self::$instance;
    }
    
    /**
     * Select protocol to use
     *
     * @param integer $param
     *
     * @return string
     */
    private static function protocol($param)
    {
        switch ($param) {
            case PROTOCOL_HTTP:
                $protocol = 'http://';
                break;
            case PROTOCOL_HTTPS:
                $protocol = 'https://';
                break;
            case PROTOCOL_DEFAULT:
            default:
                $protocol = self::getInstance()->uri->getScheme() . '://';
                break;
        }
        
        return $protocol;
    }
    
    /**
     * Append a path to the current absolute path
     *
     * @param string  $base
     *            Base url
     * @param string  $path
     *        String to append
     * @param integer $protocol
     *        Protocol to append to the path
     *
     * @return string
     */
    private static function writeUrl($base, $path, $protocol)
    {
        /*if (isset(Request::get()['language'])) {
            if (Request::get()['language'] == Translator::language()->code || Request::get()['language'] == Translator::language()->code_short) {
                $language = Request::get()['language'];
            } else {
                $language = Translator::language()->code_short;
            }
            
            return self::protocol($protocol) . $base . '/' . $language . '/' . $path;
        } else {*/
            return self::protocol($protocol) . $base . '/' . $path;
        //}
    }
    
    public static function resource($path)
    {
        return self::protocol(PROTOCOL_DEFAULT) . self::getInstance()->authority . '/' . $path;
    }
    
    /**
     * Retrieve the http path to a ressource relative to site's root
     *
     * @param string  $path
     *        String to append
     * @param integer $protocol
     *        Protocol to append to the path
     *
     * @return string
     */
    public static function path($path, $protocol = PROTOCOL_DEFAULT)
    {
        return self::writeUrl(self::getInstance()->authority, $path, $protocol);
    }
    
    /**
     * Retrieve the http path to a resource relative to site's main
     * domains's root
     *
     * @param string  $path
     *        String to append
     * @param integer $protocol
     *        Protocol to append to the path
     *
     * @return string
     */
    public static function mainPath($path, $protocol = PROTOCOL_DEFAULT)
    {
        return self::writeUrl(self::getInstance()->mainAuthority, $path, $protocol);
    }
    
    /**
     * Retrieve the http path to a resource relative to current
     * resource
     *
     * @param string  $path
     *        String to append
     * @param integer $protocol
     *        Protocol to append to the path
     *
     * @return string
     */
    public static function relativePath($path, $protocol = PROTOCOL_DEFAULT)
    {
        return self::writeUrl(self::getInstance()->authority, self::getInstance()->path . '/' . $path,
            $protocol);
    }
    
    /**
     * Get current current http path
     *
     * @param integer $protocol
     *
     * @return string
     */
    public static function getCurrentPath($protocol = PROTOCOL_DEFAULT)
    {
        return self::writeUrl(self::getInstance()->authority, self::getInstance()->path, $protocol);
    }
}
