<?php

namespace Level42\Bundle\DataTablesBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Level42\Bundle\DataTablesBundle\DependencyInjection\Compiler\AddDataTablesBuilderPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Level42DataTablesBundle extends Bundle
{
   
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    
        $container->addCompilerPass(new AddDataTablesBuilderPass());
    }
    
}
