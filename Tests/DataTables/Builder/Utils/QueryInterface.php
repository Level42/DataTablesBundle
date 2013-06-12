<?php
namespace Level42\Bundle\DataTablesBundle\Tests\DataTables\Builder\Utils;


Interface QueryInterface
{

    public function setHydrationMode($hydrationMode);

    public function execute();

    public function getSingleScalarResult();
    
}
