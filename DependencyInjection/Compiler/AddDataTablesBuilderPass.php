<?php

namespace Level42\Bundle\DataTablesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged data_tables.builder services to level42_data_tables.builder.manager service
 *
 * @author Dj level42
 */
class AddDataTablesBuilderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('level42_data_tables.manager')) {
            return;
        }

        $definition = $container->getDefinition('level42_data_tables.manager');

        foreach ($container->findTaggedServiceIds('data_tables.factory_builder') as $id => $factories) {
            foreach ($factories as $factory) {
                $name     = isset($factory['builder']) ? $factory['builder'] : null;
                $priority = isset($factory['priority']) ? $factory['priority'] : 0;
                
                if (!isset($factory['resultClass']))
                {
                    throw new Exception("No result Class defined");
                }
                $resultClass = $factory['resultClass'];
               

                if ($priority === "false") {
                    $priority = null;
                }

                $definition->addMethodCall('add', array(new Reference($id), $priority, $name,$resultClass));
            }
        }
    }
}
