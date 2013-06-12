<?php
namespace Level42\Bundle\DataTablesBundle\Tests\DataTables\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

use Level42\Bundle\DataTablesBundle\DataTables\Request\Params;

/**
 * @author dajay
 *
 */
class ParamsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $request = new ParameterBag();
        $request->set("iDisplayStart", 1);
        $request->set("iDisplayLength", 10);
        $request->set("iColumns", 2);
        $request->set("sSearch", "test search");
        $request->set("bRegex", true);
        $request->set("bSearchable_0", true);
        $request->set("bSearchable_1", false);
        $request->set("sSearch_0", "test1");
        $request->set("sSearch_1", "test2");
        $request->set("bRegex_0", false);
        $request->set("bRegex_1", true);
        $request->set("bRegex_2", true); // not exists
        $request->set("bSortable_0", true);
        $request->set("bSortable_1", true);
        $request->set("iSortingCols", 2);
        $request->set("iSortCol_0", 1);
        $request->set("iSortCol_1", 0);
        $request->set("sSortDir_0", "asc");
        $request->set("sSortDir_1", "desc");
        $request->set("mDataProp_0", "field1");
        $request->set("mDataProp_1", "field2");
        $request->set("sEcho", "delay");
        //request->set("", $value);
        $this->params = new Params($request);
    }
    
    public function testGetStart()
    {
         $this->assertEquals(1, $this->params->getStart());
    }
    
    public function testGetLimit()
    {
        $this->assertEquals(10, $this->params->getLimit());
    }  

    public function testGetGlobalSearch()
    {
        $this->assertEquals("test search", $this->params->getGlobalSearch());
    }    

    public function testNbDisplayedColumns()
    {
         $this->assertEquals(2, $this->params->getNbDisplayedColumns());
    }    
    
    public function testIsRegexEnable()
    {
        $this->assertTrue($this->params->isRegexEnable());
    }    
    
    public function testGetIsSearchables()
    {
        $searchables = $this->params->getIsSearchables();
        $this->assertEquals(2, count($searchables));
        $this->assertTrue(reset($searchables));
        $this->assertFalse(end($searchables));
    }
    
    public function testGetSearches()
    {
        $searches = $this->params->getSearches();
        $this->assertEquals(2, count($searches));
        $this->assertEquals("test1",reset($searches));
        $this->assertEquals("test2",end($searches));
    }    
    
    public function testGetIsRegexs()
    {
        $list = $this->params->getIsRegexs();
        $this->assertEquals(2, count($list));
        $this->assertFalse(reset($list));
        $this->assertTrue(end($list));
    }    
    
    public function testGetIsSortables()
    {
        $list = $this->params->getIsSortables();
        $this->assertEquals(2, count($list));
        $this->assertTrue(reset($list));
        $this->assertTrue(end($list));
    }    

    public function testGetNbSortingCols()
    {
        $this->assertEquals(2, $this->params->getNbSortingCols());
    }    

    public function testGetIsSortCols()
    {
        $list = $this->params->getIsSortCols();
        $this->assertEquals(2, count($list));
        $this->assertEquals("field2",reset($list));
        $this->assertEquals(1,key($list));

        $this->assertEquals("field1",end($list));
        $this->assertEquals(0,key($list));
    }    
    
    public function testGetSortDirs()
    {
        $list = $this->params->getSortDirs();
        $this->assertEquals(2, count($list));
        $this->assertEquals("asc",reset($list));
        $this->assertEquals(1,key($list));
        $this->assertEquals("desc",end($list));
        $this->assertEquals(0,key($list));
    }    
    
    public function testGetSortNamedFieldDirs()
    {
        $list = $this->params->getSortNamedFieldDirs();
        $this->assertEquals(2, count($list));
        $this->assertEquals("asc",reset($list));
        $this->assertEquals("field2",key($list));
        $this->assertEquals("desc",end($list));
        $this->assertEquals("field1",key($list));
    }
    
    
    public function testGetFieldNames()
    {
        $list = $this->params->getFieldNames();
        $this->assertEquals(2, count($list));
        $this->assertEquals("field1",reset($list));
        $this->assertEquals("field2",end($list));
    }    
    
    public function testGetEcho()
    {
        $this->assertEquals("delay",$this->params->getEcho());
    }    
}