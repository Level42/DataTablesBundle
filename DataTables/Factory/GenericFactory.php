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
    
    /**
     * Serializer
     * @var Object
     */
    protected $serializer;
    
    public function __construct($builderClass, $serializer)
    {
        $this->builderClass = $builderClass;
        $this->serializer = $serializer;
    }
    
    /**
     * 
     * @return BuilderInterface
     */
    function createBuilder()
    {
        /* @var $builder BuilderInterface */
        $builder = new $this->builderClass();
        $builder->setSerializer($this->serializer);
        return $builder;
    }
    
}
