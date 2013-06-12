<?php
namespace Level42\Bundle\DataTablesBundle\Tests\DataTables\Builder;

use Doctrine\ORM\Query;

use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Symfony\Component\HttpFoundation\ParameterBag;

use Level42\Bundle\DataTablesBundle\DataTables\Request\Params;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Doctrine\Common\Persistence\ObjectRepository;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use Level42\Bundle\DataTablesBundle\DataTables\Builder\Doctrine;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Configuration;


class DoctrineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $registry;

    /**
     * 
     * @var Configuration
     */
    private $config;    
    
    /**
     *
     * @var ObjectManager
     */
    private $om;    
    
    /**
     *
     * @var ObjectRepository
     */
    private $repo;    
    
    
    /**
     *
     * @var ClassMetadata
     */
    private $meta;
    
    /**
     *
     * @var QueryBuilder
     */
    private $qb;
    
    /**
     * 
     * @var Level42\Bundle\DataTablesBundle\DataTables\Builder\Doctrine
     */
    private $builder;
    
    public function setUp()
    {
        if (!interface_exists('Doctrine\ORM\EntityManagerInterface')) {
            $this->markTestSkipped();
        }
    }

    
    private function setBuilderProperties()
    {
        $this->config = $this->getMock('Doctrine\ORM\Configuration');
        $this->registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');

        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repo = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->meta = $this->getMock('Doctrine\ORM\Mapping\ClassMetadataInfo',array(),array('Test\Bundle\StdClass') );  

        $this->em         = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        
        $this->qb         = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                                 ->disableOriginalConstructor()
                                 ->getMock();
                
        $this->builder = new Doctrine($this->registry);
        
        $this->builder->setOptions(array('entity_class'=>'Bundle:StdClass'));
        
        $requestParam = $this->getMock('Level42\Bundle\DataTablesBundle\DataTables\Request\Params',array(),array(new ParameterBag(array()))) ;
        $this->builder->setRequestParams($requestParam);
        
        $this->registry->expects($this->once())
        ->method('getAliasNamespace')
        ->will($this->returnValue("Test\Bundle"));
        
        $this->registry->expects($this->once())
        ->method('getRepository')
        ->with('Test\Bundle\StdClass')
        ->will($this->returnValue($this->repo));
        
        $this->registry->expects($this->any())
        ->method('getManager')
        ->will($this->returnValue($this->om));        
        
        $this->om->expects($this->at(0))
        ->method('getClassMetadata')
        ->with($this->equalTo('Test\Bundle\StdClass'))
        ->will($this->returnValue($this->meta));
        
        $this->meta->expects($this->once())
        ->method('getTableName')
        ->will($this->returnValue("std_table"));

        $this->registry->expects($this->once())
        ->method('getEntityManager')
        ->will($this->returnValue($this->em));        

        $this->em->expects($this->once())
        ->method('createQueryBuilder')
        ->will($this->returnValue($this->qb));
        
        $this->meta->expects($this->once())
        ->method('getIdentifierFieldNames')
        ->will($this->returnValue(array("id")));        
        
        
        
        $this->builder->setBuilderProperties();     
    }
    
    public function  setParametersBuilder()
    {
        $this->setBuilderProperties();
    
        $requestParams = $this->builder->getRequestParams();
        $requestParams->expects($this->once())
        ->method('getNbDisplayedColumns')
        ->will($this->returnValue(2));
    
        $requestParams->expects($this->once())
        ->method('getFieldNames')
        ->will($this->returnValue(array("name","rel.code")));
    
        $this->meta->expects($this->once())
        ->method('hasField')
        ->will($this->returnValue(true));
    
        $this->meta->expects($this->once())
        ->method('hasAssociation')
        ->will($this->returnValue(true));
    
        $this->meta->expects($this->once())
        ->method('isCollectionValuedAssociation')
        ->will($this->returnValue(false));
    
        $this->meta->expects($this->once())
        ->method('getAssociationTargetClass')
        ->with($this->equalTo("rel"))
        ->will($this->returnValue('Test\Bundle\RelClass'));
    
    
        $this->meta->expects($this->once())
        ->method('getAssociationTargetClass')
        ->will($this->returnValue('Test\Bundle\RelClass'));
    
        $meta = $this->getMock('Doctrine\ORM\Mapping\ClassMetadataInfo',array(),array('Test\Bundle\RelClass') );
    
        $this->om->expects($this->at(0))
        ->method('getClassMetadata')
        ->with($this->equalTo('Test\Bundle\RelClass'))
        ->will($this->returnValue($meta));
    
        $meta->expects($this->once())
        ->method('getTableName')
        ->will($this->returnValue("relTable"));
    
        $meta->expects($this->once())
        ->method('getName')
        ->will($this->returnValue("rel"));
    
        $this->meta->expects($this->any())
        ->method('getName')
        ->will($this->returnValue("StdTable"));
    
        $meta->expects($this->once())
        ->method('getIdentifierFieldNames')
        ->will($this->returnValue(array("id")));
    
        $meta->expects($this->once())
        ->method('hasField')
        ->will($this->returnValue(true));
    
    
        $this->builder->setParameters();
    }    
    
    
    public function  testSetParametersSingle()
    {
        $this->setBuilderProperties();
        $requestParams = $this->builder->getRequestParams();
        $requestParams->expects($this->once())
        ->method('getNbDisplayedColumns')
        ->will($this->returnValue(2));
        
        $requestParams->expects($this->once())
        ->method('getFieldNames')
        ->will($this->returnValue(array("name","code")));
        
        $this->meta->expects($this->any())
        ->method('hasField')
        ->will($this->returnValue(true));
        
        $this->builder->setParameters();
        
        $associations = array();
        $associations[] =  array('containsCollections' => false,'fieldName'=>"Name",'entityName'=>"StdTable",'fullName'=>"StdTable.name");
        $associations[] =  array('containsCollections' => false,'fieldName'=>"Code",'entityName'=>"StdTable",'fullName'=>"StdTable.code");
        
        
        $this->assertAttributeEquals($associations, "associations", $this->builder);
    }
    
    public function  testSetParametersRelated()
    {
        $this->setBuilderProperties();
        $this->setParametersBuilder();
    
        $associations = array();
        $associations[] =  array('containsCollections' => false,'fieldName'=>"Name",'entityName'=>"StdTable",'fullName'=>"StdTable.name");
        $associations[] =  array('containsCollections' => false,'fieldName'=>"Code",'entityName'=>"rel",'fullName'=>"StdTable_RelTable.code",'joinName' => 'StdTable_RelTable' );
        
        $this->assertAttributeEquals($associations, "associations", $this->builder);
        
        $assignedJoins = array();
        $assignedJoins["StdTable_RelTable"]['joinOn'] = "StdTable.rel";
        $assignedJoins["StdTable_RelTable"]['mdataColumn'] = "rel";
        $this->assertAttributeEquals($assignedJoins, "assignedJoins", $this->builder);
        
        
        $this->assertAttributeEquals(array("StdTable_RelTable"=>array('id')), "identifiers", $this->builder);
    }   

    public function  testSetSelect()
    {
        $this->setBuilderProperties();
        $this->setParametersBuilder();
        
        $this->qb->expects($this->any())
        ->method('select')
        ->with('partial StdTable_RelTable.{id,code},partial StdTable.{id,name}');
        
        $this->meta->expects($this->once())
        ->method('getName')
        ->will($this->returnValue("StdTable"));
        
        $this->qb->expects($this->once())
        ->method('from')
        ->with("StdTable","StdTable");
        
        $this->builder->setSelect($this->qb);
    }    
    
    public function  testSetAssociations()
    {
        $this->setBuilderProperties();
        $this->setParametersBuilder();
        
        $this->qb->expects($this->once())
        ->method('innerJoin')
        ->with("StdTable.rel","StdTable_RelTable");
    
        $this->builder->setAssociations($this->qb);
        
        $this->qb->expects($this->once())
        ->method('leftJoin')
        ->with("StdTable.rel","StdTable_RelTable");
        
        $this->builder->setJoinType("rel", Doctrine::JOIN_LEFT);
        $this->builder->setAssociations($this->qb);
    }    
    
    public function  testSetOrderBy()
    {
        $this->setBuilderProperties();
        $this->setParametersBuilder();
        
        $rqParam = $this->builder->getRequestParams();
    
        $rqParam->expects($this->once())
        ->method('getIsSortables')
        ->will($this->returnValue(array("name"=>true,"rel.code"=>true)));
        
        $rqParam->expects($this->once())
        ->method('getSortNamedFieldDirs')
        ->will($this->returnValue(array("name"=>"ASC","rel.code"=>"DESC")));
        
        $this->qb->expects($this->at(0))
        ->method('addOrderBy')
        ->with("StdTable.name","ASC");

        $this->qb->expects($this->at(1))
        ->method('addOrderBy')
        ->with("StdTable_RelTable.code","DESC");        
        
        $this->builder->setOrderBy($this->qb);
    }    
    

    public function  testSetLimit()
    {
        $this->setBuilderProperties();
        $this->setParametersBuilder();
    
        $rqParam = $this->builder->getRequestParams();
    
        $rqParam->expects($this->once())
        ->method('getStart')
        ->will($this->returnValue(20));
    
        $rqParam->expects($this->once())
        ->method('getLimit')
        ->will($this->returnValue(10));
    
        $this->qb->expects($this->once())
        ->method('setFirstResult')
        ->with(20)
        ->will($this->returnValue($this->qb));
    
        $this->qb->expects($this->at(1))
        ->method('setMaxResults')
        ->with(10);
    
        $this->builder->setLimit($this->qb);
    }    
    
    public function testSetWhereGlobal()
    {
        $this->setBuilderProperties();
        $this->setParametersBuilder();       
            
        $rqParam = $this->builder->getRequestParams();
        
        $rqParam->expects($this->once())
        ->method('getGlobalSearch')
        ->will($this->returnValue("mySearch"));
        
        $rqParam->expects($this->once())
        ->method('getIsSearchables')
        ->will($this->returnValue(array("name"=>true,"rel.code"=>true)));        
        
        $expr = $this->getMockBuilder('Doctrine\ORM\Query\Expr')
                                 ->disableOriginalConstructor()
                                 ->getMock();
        
        $this->qb->expects($this->any())
        ->method('expr')
        ->will($this->returnValue($expr));   

        $orx         = $this->getMockBuilder('Doctrine\ORM\Query\Expr\Orx')
        ->disableOriginalConstructor()
        ->getMock();        
        
        $expr->expects($this->any())
        ->method('orX')
        ->will($this->returnValue($orx));
        
        $expr->expects($this->at(1))
        ->method('like')
        ->with("StdTable.name",":search_global_StdTable_Name");
        
        $expr->expects($this->at(2))
        ->method('like')
        ->with("StdTable_RelTable.code",":search_global_rel_Code");        
        
        $orx->expects($this->exactly(2))
        ->method('add');        
        
        $this->qb->expects($this->exactly(2))
        ->method('setParameter');
        
        $this->qb->expects($this->once())
        ->method('where')
        ->with($orx);
       
        $this->builder->setWhere($this->qb);
    }    
    
    public function testSetWhereIndividual()
    {
        $this->setBuilderProperties();
        $this->setParametersBuilder();
    
        $rqParam = $this->builder->getRequestParams();
    
        $rqParam->expects($this->once())
        ->method('getGlobalSearch')
        ->will($this->returnValue(""));
    
        $rqParam->expects($this->once())
        ->method('getIsSearchables')
        ->will($this->returnValue(array("name"=>true,"rel.code"=>true)));
        
        $rqParam->expects($this->once())
        ->method('getSearches')
        ->will($this->returnValue(array("name"=>"myName","rel.code"=>"myRelCode")));        
    
        $expr = $this->getMockBuilder('Doctrine\ORM\Query\Expr')
        ->disableOriginalConstructor()
        ->getMock();
    
        $this->qb->expects($this->any())
        ->method('expr')
        ->will($this->returnValue($expr));
    
        $andx         = $this->getMockBuilder('Doctrine\ORM\Query\Expr\Andx')
        ->disableOriginalConstructor()
        ->getMock();
    
        $expr->expects($this->any())
        ->method('andx')
        ->will($this->returnValue($andx));
    
        $expr->expects($this->at(1))
        ->method('like')
        ->with("StdTable.name",":search_single_StdTable_Name");
    
        $expr->expects($this->at(2))
        ->method('like')
        ->with("StdTable_RelTable.code",":search_single_rel_Code");
    
        $andx->expects($this->exactly(2))
        ->method('add');
    
        $this->qb->expects($this->exactly(2))
        ->method('setParameter')
        ->with($this->logicalOr($this->equalTo("search_single_StdTable_Name"), $this->equalTo("search_single_rel_Code"))
               ,$this->logicalOr($this->equalTo("%myName%"), $this->equalTo("%myRelCode%")));

        $andx->expects($this->exactly(1))
        ->method('count')
        ->will($this->returnValue(2));
        
        $this->qb->expects($this->once())
        ->method('andWhere')
        ->with($andx);
         
        $this->builder->setWhere($this->qb);
    }    
    
    public function testExecuteSearch()
    {
        $this->setBuilderProperties();
        $this->setParametersBuilder();        
        
        $rqParam = $this->builder->getRequestParams();
        
        $rqParam->expects($this->once())
        ->method('getEcho')
        ->will($this->returnValue(2));
        
        
        $query = $this->getMock('Level42\Bundle\DataTablesBundle\Tests\DataTables\Builder\Utils\QueryInterface');
        
        $this->qb->expects($this->any())
        ->method('getQuery')
        ->will($this->returnValue($query));       

        $query->expects($this->any())
        ->method('setHydrationMode')
        ->will($this->returnValue($query));
        
        $query->expects($this->any())
        ->method('execute')
        ->will($this->returnValue(array(array("id"=>"myId1","name"=>"myName1","rel"=>array("code"=>"myCode")))));
        
        // count
        
        $this->qb->expects($this->any())
        ->method('resetDQLParts')
        ->will($this->returnValue($this->qb));
        
        $this->qb->expects($this->any())
        ->method('select')
        ->will($this->returnValue($this->qb));
        
        $this->qb->expects($this->any())
        ->method('setFirstResult')
        ->will($this->returnValue($this->qb));
        
        $this->qb->expects($this->any())
        ->method('setMaxResults')
        ->will($this->returnValue($this->qb));
        
        $query->expects($this->any())
        ->method('getSingleScalarResult')
        ->will($this->returnValue(10));
        
        $output = $this->builder->executeSearch();
        
        $this->assertAttributeEquals(array(array("id"=>"myId1","name"=>"myName1","rel"=>array("code"=>"myCode"),'DT_RowId' => 'myId1' )), "aaData", $output);
        
        $this->assertAttributeEquals(10, "iTotalDisplayRecords", $output);
        $this->assertAttributeEquals(2, "sEcho", $output);
        
    }    
    
    
}
