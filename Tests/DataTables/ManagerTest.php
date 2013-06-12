<?php
namespace Level42\Bundle\DataTablesBundle\Tests\DataTables;


use Level42\Bundle\DataTablesBundle\DataTables\Manager;

/**
* @author dajay
*
*/
final class ManagerTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->manager = new Manager();
    }

    public function testPriorities()
    {
        $this->assertEquals(array(), $this->manager->all());

        $high = $this->createFactoryBuilderMock();
        $low = $this->createFactoryBuilderMock();

        $this->manager->add($low,0,null,"LowClass");
        $this->manager->add($high, 10,null,"HighClass");

        $this->assertEquals(array(
            $high,
            $low,
        ), $this->manager->all());
    }

    public function testHasResultClassBuilderFactory()
    {
        $builderFactory = $this->createFactoryBuilderMock();
    
        $this->manager->add($builderFactory,0,null,"MyClassResult");

        $this->assertFalse($this->manager->hasResultClassBuilderFactory("NotMyClassResult"));
        $this->assertTrue($this->manager->hasResultClassBuilderFactory("MyClassResult"));
        
    }    
    
    public function testGetResultClassBuilderFactory()
    {
        $factory = $this->createFactoryBuilderMock();
        $this->manager->add($factory, 10,null,"MyClassResult");
        
        $this->assertSame($factory, $this->manager->getResultClassBuilderFactory("MyClassResult"));
    }

    public function testGetResultClassesBuilderFactories()
    {
        $factory1 = $this->createFactoryBuilderMock();
        $factory2 = $this->createFactoryBuilderMock();
        $this->manager->add($factory1, 10,null,"MyClassResult1");
        $this->manager->add($factory1, 10,null,"MyClassResult2");
        
        $this->assertEquals(2,count($this->manager->getResultClassesBuilderFactories()));
    }
        
    
    protected function createFactoryBuilderMock()
    {
        return $this->getMock('Level42\Bundle\DataTablesBundle\DataTables\Factory\FactoryInterface');
    }
}