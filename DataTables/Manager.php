<?php
namespace Level42\Bundle\DataTablesBundle\DataTables;


use Level42\Bundle\DataTablesBundle\DataTables\Builder\BuilderInterface;

/**
* @author dajay
*
*/
final class Manager
{

    /**
     * @var array
     */
    protected $builderFactories = array();
    
    /**
     * @var array
    */
    protected $namedBuilderFactories = array();    
    
    /**
     * @var array
     */
    protected $resultClassesBuilderFactories = array();    
    
    /**
     * Adds a dataTable builder.
     *
     * Builders match either explicitly via $name or by iteration over all
     * builders with a $priority. If you pass a $priority = null then the
     * added builder will not be part of the iteration chain and can only
     * be invoked explicitly.
     *
     * @param object     $builderFactory     A factoryBuilder instance
     * @param integer    $priority           The priority (between -10 and 10).
     * @param string     $name               Name of the builder.
     * @param string     $resultClass        Name of the result class.
     */
    public function add($builderFactory, $priority = 0, $name = null, $resultClass)
    {
        if ($priority !== null) {
            if (!isset($this->builderFactories[$priority])) {
                $this->builderFactories[$priority] = array();
            }
    
            $this->builderFactories[$priority][] = $builderFactory;
            $this->resultClassesBuilderFactories[$resultClass] = $builderFactory;
        }
    
        if (null !== $name) {
            $this->namedBuilderFactories[$name] = $builderFactory;
        }
    }
    
    /**
     * Returns all registered datatables builders.
     *
     * @return array An array of datatables builders
     */
    public function all()
    {
        krsort($this->builderFactories);
    
        $builders = array();
        foreach ($this->builderFactories as $all) {
            $builders = array_merge($builders, $all);
        }
    
        return new \ArrayObject($builders);
    }    
    
    /**
     * Returns all registered datatables builder factories with resultClass key.
     * @return ArrayObject
     */
    public function getResultClassesBuilderFactories()
    {
        return new \ArrayObject($this->resultClassesBuilderFactories);
    }

    /**
     * Has ResultClass key in builderFactories array
     * 
     * @param boolean
     */
    public function hasResultClassBuilderFactory($className)
    {
        return (isset($this->resultClassesBuilderFactories[$className]));
    }    
    
    /**
     * Get a builder by class name result
     * 
     * @param string $className
     * @throws Exception
     * @return BuilderInterface
     */
    public function getResultClassBuilderFactory($className)
    {
        if (!isset($this->resultClassesBuilderFactories[$className]))
        {
            throw new Exception("No builder associated with the class $className");
        }
        
        return $this->resultClassesBuilderFactories[$className];
    }

}
