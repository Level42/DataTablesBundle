<?php
namespace Level42\Bundle\DataTablesBundle\DataTables\Factory;

use Doctrine\Common\Persistence\ManagerRegistry;
use Level42\Bundle\DataTablesBundle\DataTables\Result\Doctrine as ResultDoctrine;
use Level42\Bundle\DataTablesBundle\DataTables\Builder\BuilderInterface;

class Doctrine implements FactoryInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;
    
    /**
     * Class String of builder
     * @var string
     */
    protected $builderClass;
    
    public function __construct(ManagerRegistry $registry = null,$builderClass)
    {
        $this->registry = $registry;
        $this->builderClass = $builderClass;
    }
    
    /**
     *
     * @return BuilderInterface
     */
    function createBuilder()
    {
        /* @var $builder Level42\Bundle\DataTablesBundle\DataTables\Builder\Doctrine */
        $builder = new $this->builderClass($this->registry);
        return $builder;
    }

}
