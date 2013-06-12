<?php

namespace Level42\Bundle\DataTablesBundle\DataTables\Request\ParamConverter;

use Doctrine\Common\Inflector\Inflector;

use Symfony\Component\HttpKernel\Exception\HttpException;

use Level42\Bundle\DataTablesBundle\DataTables\Request\Params;

use Level42\Bundle\DataTablesBundle\DataTables\Builder\BuilderInterface;

use Level42\Bundle\DataTablesBundle\DataTables\Manager;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;



/**
 * DoctrineParamConverter.
 *
 * @author David J level42
 */
class DataTablesParamConverter implements ParamConverterInterface
{
    /**
     * @var Manager
     */
    protected $dTManager;

    public function __construct(Manager $dTManager = null)
    {
        $this->dTManager = $dTManager;
    }

    /**
     * @{inheritdoc}
     * 
     * @throws \LogicException       When unable to guess how to get a Doctrine instance from the request information
     * @throws NotFoundHttpException When object not found
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $name    = $configuration->getName();
        
        $options = $configuration->getOptions();
        
        if ($request->attributes->has("entity_class"))
        {
            $options["entity_class"] = Inflector::camelize(Inflector::singularize($request->attributes->get("entity_class")));
            $configuration->setOptions($options);
        }

        if (!isset($options["entity_class"]))
        {
            throw new HttpException("404","No entity_class option given");
        }
        
        $factories = $this->dTManager->getResultClassesBuilderFactories();
        
        $factory = $factories[$configuration->getClass()];
        /* @var $builder BuilderInterface  */
        $builder = $factory->createBuilder();    
        
        $builder->setOptions($this->getOptions($configuration));
        
        if ($request->isMethod("GET"))
        {
            $requestParams =   new Params($request->query);  
        }
        else
        {
            $requestParams =   new Params($request->request);
        }
        
        $builder->setRequestParams($requestParams);
        
        $builder->init();
        
        $request->attributes->set($name,$builder->getResult() );

        return true;
    }



    /**
     * @{inheritdoc}
     */
    public function supports(ConfigurationInterface $configuration)
    {
        if (!$configuration instanceof ParamConverter) 
        {
            return false;
        }
        
        $classToConvert = $configuration->getClass();
        
        return $this->dTManager->hasResultClassBuilderFactory($classToConvert);
    }

    /**
     * return options converter
     * 
     * @param ConfigurationInterface $configuration
     * @return array
     */
    protected function getOptions(ConfigurationInterface $configuration)
    {
        return array_replace(array(
                'entity_class' => null,
                'repository_method' => null,
        ), $configuration->getOptions());
    }    
  
}
