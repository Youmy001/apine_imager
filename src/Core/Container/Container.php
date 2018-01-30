<?php
/**
 * Created by PhpStorm.
 * User: youmy
 * Date: 15/01/18
 * Time: 12:32 AM
 */
declare(strict_types=1);

namespace Apine\Core\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    private $entries = array();
    
    private $instantiatedEntries = array();
    
    /**
     * Adds an entry to the container with an identifier
     *
     * Doing so will override any entry with the same identifier
     *
     * @param mixed $id
     * @param mixed $value
     */
    public function register ($id, $value)
    {
        unset($this->instantiatedEntries[$id]);
        unset($this->entries[$id]);
        
        $this->entries[$id] = $value;
    }
    
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new ContainerNotFoundException(sprintf('No entry was found for the identifier "$e"', $id));
        }
        
        try {
            /*return $this->services[$id];*/
            if (is_callable($this->entries[$id])) {
                if (!isset($this->instantiatedEntries[$id])) {
                    $entry = $this->entries[$id];
                    $this->instantiatedEntries[$id] = $entry();
                }
                
                return $this->instantiatedEntries[$id];
            } else {
                return $this->entries[$id];
            }
            
        } catch (\Exception $e) {
            throw new ContainerException(
                sprintf('Error while trying to retrieve the entry "%s"', $id),
                null,
                $e
            );
        }
    }
    
    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id) : bool
    {
        return (isset($this->entries[$id]));
    }
}