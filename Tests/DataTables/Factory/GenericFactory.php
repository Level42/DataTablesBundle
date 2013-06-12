<?php
namespace Level42\Bundle\DataTablesBundle\DataTables\Factory;

use Level42\Bundle\DataTablesBundle\DataTables\Builder\BuilderInterface;

class GenericFactory
{
    /**
     * Class String of builder
     * @var string
     */
    private $builderClass;
    
    function __construct($builderClass)
    {
        $this->builderClass = $builderClass;
    }
    
    /**
     * 
     * @return BuilderInterface
     */
    function createBuilder()
    {
        return new $this->builderClass();
    }
    
}
