<?php

namespace Level42\Bundle\DataTablesBundle\Tests\DataTables\Request\ParamConverter;

use Level42\Bundle\DataTablesBundle\DataTables\Manager;

use Level42\Bundle\DataTablesBundle\DataTables\Request\ParamConverter\DataTablesParamConverter;
use Level42\Bundle\DataTablesBundle\DataTables;

use Symfony\Component\HttpFoundation\Request;


class DataTablesParamConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * 
     * @var Level42\Bundle\DataTablesBundle\DataTables\Manager
     */
    private $manager;
    
    /**
     * @var DataTablesParamConverter
     */
    private $converter;

    public function setUp()
    {
        $this->manager = new Manager();
        $factory = $this->getMock("Level42\Bundle\DataTablesBundle\DataTables\Factory\FactoryInterface");
        
        $this->manager->add($factory,0,"factory", "stdClass");
        
        $this->converter = new DataTablesParamConverter($this->manager);
    }

    public function testSupports()
    {
        $config = $this
        ->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')
        ->setMethods(array('getClass'))
        ->disableOriginalConstructor()
        ->getMock();        

        $config->expects($this->any())
               ->method('getClass')
               ->will($this->returnValue("stdClass"));

        $this->assertTrue($this->converter->supports($config));
    }
    


}
