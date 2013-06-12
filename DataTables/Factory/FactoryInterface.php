<?php
namespace Level42\Bundle\DataTablesBundle\DataTables\Factory;

use Level42\Bundle\DataTablesBundle\DataTables\Builder\BuilderInterface;

interface FactoryInterface
{
    /**
     * 
     * @return BuilderInterface
     */
    function createBuilder();    
}
