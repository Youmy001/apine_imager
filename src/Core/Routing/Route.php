<?php
/**
 * Route
 *
 * @license MIT
 * @copyright 2018 Tommy Teasdale
 */
declare(strict_types=1);

namespace Apine\Core\Routing;

use Apine\Core\Controllers\Controller;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Basic representation of a route
 *
 * @author Tommy Teasdale
 * @package Apine\Routing
 */
final class Route
{
    /**
     * @var string
     */
    public $uri;
    
    /**
     * @var string
     */
    public $method;
    
    /**
     * Name of a controller
     *
     * @var Controller
     */
    public $controller;
    
    /**
     * Name of an action method
     *
     * @var string
     */
    public $action;
    
    /**
     * Parameters defined in the route definition
     *
     * @var ParameterDefinition[]
     */
    public $parameters;
    
    /**
     * Parameters of the action method
     *
     * @var Parameter[]
     */
    public $actionParameters;
    
    /**
     * @var bool
     */
    public $isAPIRoute;
    
    /**
     * Route constructor.
     *
     * @param string $method
     * @param string $uri
     * @param string $controller
     * @param string $action
     * @param array  $parameters
     * @param boolean $isAPI
     *
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function __construct(string $method, string $uri, string $controller, string $action, array $parameters = [], bool $isAPI = false)
    {
        if (!class_exists($controller) || !is_subclass_of($controller, Controller::class) || !method_exists($controller, $action)) {
            throw new \Exception('Controller or method not found');
        }
        
        $this->uri = $uri;
        $this->method = $method;
        $this->controller = $controller;
        $this->action = $action;
        $this->isAPIRoute = $isAPI;
        
        $this->parameters = $this->parseParameters($parameters);
        $this->actionParameters = $this->resolveAction();
    }
    
    /**
     * Parse the route pattern to extract the list of named parameters
     *
     * @param array $definitions
     *
     * @return array
     */
    private function parseParameters(array $definitions)
    {
        preg_match_all('/\{(\??)(.+?)\}/', $this->uri, $matches, PREG_SET_ORDER);
        
        return array_map(function ($match) use ($definitions) {
            $parameter = new ParameterDefinition($match[2], '([^\/]+?)');
            
            if (isset($definitions[$match[2]])) {
                $parameter->pattern = $definitions[$match[2]];
            }
            
            if ($match[1] === '?') {
                $parameter->optional = true;
            }
            
            return $parameter;
        }, $matches);
    }
    
    /**
     * Resolve the list of parameters of the action through reflection
     *
     * @return Parameter[]
     * @throws \ReflectionException
     */
    private function resolveAction()
    {
        $reflection = new \ReflectionMethod($this->controller, $this->action);
        
        return array_map(function (\ReflectionParameter $parameter) {
            $default = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
            return new Parameter((string) $parameter->getType(), $parameter->getName(), $default);
        }, $reflection->getParameters());
    }
    
    /**
     * @param string $requestString
     * @param string $requestMethod
     *
     * @return boolean TRUE if the the request string and the method match the route
     */
    public function match(string $requestString, string $requestMethod)
    {
        // Validate the request method
        if (strtoupper($requestMethod) !== strtoupper($this->method)) {
            return false;
        }
        
        // Compose the regular expression from the uri and the parameter definitions
        $regex = '/^' . str_ireplace('/', '\\/', $this->uri) . '$/';
        
        array_walk($this->parameters, function (ParameterDefinition $parameter) use (&$regex) {
            if ($parameter->optional) {
                $regex = str_ireplace('\/{?' . $parameter->name . '}', '(\/?' . $parameter->pattern . ')?', $regex);
            } else {
                $regex = str_ireplace('{' . $parameter->name . '}', $parameter->pattern, $regex);
            }
        });
        
        // Compare with the string
        if (
            preg_match($regex, $requestString) === 1 &&
            strtoupper($requestMethod) === strtoupper($this->method)
        ) {
            return true;
        }
        
        // return boolean
        return false;
    }
}