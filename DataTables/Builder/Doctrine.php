<?php
namespace Level42\Bundle\DataTablesBundle\DataTables\Builder;





use Doctrine\ORM\Query;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\Container;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;

use Level42\Bundle\DataTablesBundle\DataTables\Result\Doctrine as ResultDoctrine;
use Level42\Bundle\DataTablesBundle\DataTables\Builder\BuilderInterface;


class Doctrine extends BuilderAbstract
{
    /**
     * Doctrine innerJoin type
     */
    const JOIN_INNER = 'inner';
    
    /**
     * Doctrine leftJoin type
     */
    const JOIN_LEFT = 'left';
    
    /**
     * A result type of array
     */
    const RESULT_ARRAY = 'Array';
    
    /**
     * A result type of JSON
     */
    const RESULT_JSON = 'Json';
    
    /**
     * A result type of a Response object
     */
    const RESULT_RESPONSE = 'Response';
    
    /**
     * @var RegistryInterface
     */
    protected $em;
    
    /**
     * @var array Holds callbacks to be used
     */
    protected $callbacks = array(
            'WhereBuilder' => array(),
    );
    
    /**
     * @var boolean Whether or not to use the Doctrine Paginator utility
    */
    protected $useDoctrinePaginator = false;
    
    /**
     * @var boolean Whether to hide the filtered count if using pre-filter callbacks
     */
    protected $hideFilteredCount = true;
    
    /**
     * @var string Whether or not to add DT_RowId to each record
     */
    protected $useDtRowId = true;
    
    /**
     * @var string Whether or not to add DT_RowClass to each record if it is set
     */
    protected $useDtRowClass = true;
    
    /**
     * @var string The class to use for DT_RowClass
     */
    protected $dtRowClass = null;
    
    /**
     * @var object The serializer used to JSON encode data
     */
    protected $serializer;
    
    /**
     * @var string The default join type to use
     */
    protected $defaultJoinType;
    
    /**
     * @var ClassMetadataInfo object The metadata for the root entity
     */
    protected $metadata;
    
    /**
     * object The Doctrine Entity Repository
     * @var ObjectRepository 
     */
    protected $repository;
        
    /**
     * @var string  Used as the query builder identifier value
     */
    protected $tableName;
        
    /**
     * @var array The parsed request variables for the DataTable
     */
    protected $parameters;
    
    /**
     * @var array Information relating to the specific columns requested
     */
    protected $associations;
    
    /**
     * @var array SQL joins used to construct the QueryBuilder query
     */
    protected $assignedJoins = array();
    
    /**
     * @var array The SQL join type to use for a column
    */
    protected $joinTypes = array();
    
    /**
     * object The QueryBuilder instance
     * @var QueryBuilder
    */
    protected $qb;
            
    
    /**
     * @var string The DataTables global search string
     */
    protected $search;
    
    /**
     * @var array The primary/unique ID for an Entity. Needed to pull partial objects
     */
    protected $identifiers = array();
    
    /**
     * @var string The primary/unique ID for the root entity
    */
    protected $rootEntityIdentifier;
    
    /**
     * @var integer The total amount of results to get from the database
     */
    protected $limit;
    
    /**
     * 
     * @var string class of the result data object 
     */
    protected $resultClass;
    
    
    public function __construct(RegistryInterface $registry = null)
    {
        $this->em = $registry;
    }

    public function init()
    {
        $this->initBuilderProperties();
        $this->initParameters();
    }
    /**
     * Fill properties of the doctrine builder
     * 
     */
    public function initBuilderProperties()
    {
        $options = $this->getOptions();
        $entityClassName = $this->getClassName($options["entity_class"]);
        $this->repository = $this->em->getRepository($entityClassName);
        $this->metadata = $this->em->getManager()->getClassMetadata($entityClassName);
        $this->tableName = Container::camelize($this->metadata->getTableName());
        $this->defaultJoinType = self::JOIN_INNER;
        $this->defaultResultType = self::RESULT_RESPONSE;
        $this->qb = $this->em->getEntityManager()->createQueryBuilder();
        $identifiers = $this->metadata->getIdentifierFieldNames();
        $this->rootEntityIdentifier = array_shift($identifiers);
    }    
    
    
    /**
     * Given an entity class name or possible alias, convert it to the full class name
     *
     * @param string The entity class name or alias
     * @return string The entity class name
     */
    protected function getClassName($className) 
    {
        if (strpos($className, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $className);
            $className = $this->em->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }
        elseif ( strpos($className, '\\') === false && !class_exists($className))
        {
           $config = $this->em->getEntityManager()->getConfiguration();
           $namespaces = $config->getEntityNamespaces();
           $classNameToTest="";
           foreach ($namespaces as $namespace)
           {
               $classNameToTest = $namespace . '\\' . $className;
               if (class_exists($classNameToTest))
               {
                   return $classNameToTest;
               }
           }
        }
        
        return $className;
    }    

    /**
     * @return array All the paramaters (columns) used for this request
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param boolean Whether or not to add DT_RowId to each record
     */
    public function useDtRowId($useDtRowId)
    {
        $this->useDtRowId = (bool) $useDtRowId;

        return $this;
    }

    /**
     * @param boolean Whether or not to add DT_RowClass to each record
     */
    public function useDtRowClass($useDtRowClass)
    {
        $this->useDtRowClass = (bool) $useDtRowClass;

        return $this;
    }

    /**
     * @param string The class to use for DT_RowClass on each record
     */
    public function setDtRowClass($dtRowClass)
    {
        $this->dtRowClass = $dtRowClass;

        return $this;
    }

    /**
     * @param boolean Whether or not to use the Doctrine Paginator utility
     */
    public function useDoctrinePaginator($useDoctrinePaginator)
    {
        $this->useDoctrinePaginator = (bool) $useDoctrinePaginator;

        return $this;
    }

    /**
     * Parse and configure parameter/association information for this DataTable request
     */
    public function initParameters()
    {
        if (is_numeric($this->requestParams->getNbDisplayedColumns())) {
            $params = array();
            $associations = array();
            $i=0;
            foreach($this->requestParams->getFieldNames() as $fieldName) {
                $fields = explode('.', $fieldName);
                $params[] = $fieldName;
                $associations[] = array('containsCollections' => false);

                if (count($fields) > 1)
                    $this->setRelatedEntityColumnInfo($associations[$i], $fields);
                else
                    $this->setSingleFieldColumnInfo($associations[$i], $fields[0]);
                $i++;
            }
            $this->parameters = $params;
            $this->associations = $associations;
        }
    }

    /**
     * Parse a dotted-notation column format from the mData, and sets association
     * information
     *
     * @param array Association information for a column (by reference)
     * @param array The column fields from dotted notation
     */
    protected function setRelatedEntityColumnInfo(array &$association, array $fields) {
        $mdataName = implode('.', $fields);
        $lastField = Container::camelize(array_pop($fields));
        $joinName = $this->tableName;
        $entityName = '';
        $columnName = '';

        // loop through the related entities, checking the associations as we go
        $metadata = $this->metadata;
        while ($field = array_shift($fields)) {
            $columnName .= empty($columnName) ? $field : ".$field";
            $entityName = lcfirst(Container::camelize($field));
            if ($metadata->hasAssociation($entityName)) {
                $joinOn = "$joinName.$entityName";
                if ($metadata->isCollectionValuedAssociation($entityName)) {
                    $association['containsCollections'] = true;
                }
                $targetClass = $metadata->getAssociationTargetClass($entityName);
                $om = $this->em->getManager();
                $metadata = $om->getClassMetadata($targetClass);
                $joinName .= '_' . $this->getJoinName(
                    $metadata,
                    Container::camelize($metadata->getTableName()),
                    $entityName
                );
                // The join required to get to the entity in question
                if (!isset($this->assignedJoins[$joinName])) {
                    $this->assignedJoins[$joinName]['joinOn'] = $joinOn;
                    $this->assignedJoins[$joinName]['mdataColumn'] = $columnName;
                    $this->identifiers[$joinName] = $metadata->getIdentifierFieldNames();
                }
            }
            else {
                throw new HttpException('404',
                    "Association  '$entityName' not found ($mdataName)"
                );
            }
        }

        // Check the last field on the last related entity of the dotted notation
        if (!$metadata->hasField(lcfirst($lastField))) {
            throw new HttpException('404',
                "Field '$lastField' on association '$entityName' not found ($mdataName)"
            );
        }
        $association['entityName'] = $entityName;
        $association['fieldName'] = $lastField;
        $association['joinName'] = $joinName;
        $association['fullName'] = $this->getFullName($association);
    }

    /**
     * Configures association information for a single field request from the main entity
     *
     * @param array  The association information as a reference
     * @param string The field name on the main entity
     */
    protected function setSingleFieldColumnInfo(array &$association, $fieldName) {
        $fieldName = Container::camelize($fieldName);

        if (!$this->metadata->hasField(lcfirst($fieldName))) {
            throw new HttpException('404',
                "Field '$fieldName' not found.)"
            );
        }

        $association['fieldName'] = $fieldName;
        $association['entityName'] = $this->tableName;
        $association['fullName'] = $this->tableName . '.' . lcfirst($fieldName);
    }

    /**
     * Based on association information and metadata, construct the join name
     *
     * @param ClassMetadata Doctrine metadata for an association
     * @param string The table name for the join
     * @param string The entity name of the table
     */
    protected function getJoinName(ClassMetadata $metadata, $tableName, $entityName)
    {
        $joinName = $tableName;

        // If it is self-referencing then we must avoid collisions
        if ($metadata->getName() == $this->metadata->getName()) {
            $joinName .= "_$entityName";   
        }

        return $joinName;
    }

    /**
     * Based on association information, construct the full name to refer to in queries
     *
     * @param array Association information for the column
     * @return string The full name to refer to this column as in QueryBuilder statements
     */
    protected function getFullName(array $associationInfo)
    {
        return $associationInfo['joinName'] . '.' . lcfirst($associationInfo['fieldName']);
    }

    /**
     * Set the default join type to use for associations. Defaults to JOIN_INNER
     *
     * @param string The join type to use, should be of either constant: JOIN_INNER, JOIN_LEFT
     */
    public function setDefaultJoinType($joinType)
    {
        if (defined('self::JOIN_' . strtoupper($joinType))) {
            $this->defaultJoinType = constant('self::JOIN_' . strtoupper($joinType));
        }

        return $this;
    }

    /**
     * Set the type of join for a specific column/parameter
     *
     * @param string The column/parameter name
     * @param string The join type to use, should be of either constant: JOIN_INNER, JOIN_LEFT
     */
    public function setJoinType($column, $joinType)
    {
        if (defined('self::JOIN_' . strtoupper($joinType))) {
            $this->joinTypes[$column] = constant('self::JOIN_' . strtoupper($joinType));
        }

        return $this;
    }

    /**
     * @param boolean Whether to hide the filtered count if using prefilter callbacks
     */
    public function hideFilteredCount($hideFilteredCount)
    {
        $this->hideFilteredCount = (bool) $hideFilteredCount;

        return $this;
    }

    /**
     * Set the scope of the result set
     *
     * @param QueryBuilder The Doctrine QueryBuilder object
     */
    public function setLimit(QueryBuilder $qb)
    {
        $start = $this->requestParams->getStart();
        if ($start != '-1') {
            $qb->setFirstResult($start)->setMaxResults($this->requestParams->getLimit());
        }
    }

    /**
     * Set any column ordering that has been requested
     *
     * @param QueryBuilder The Doctrine QueryBuilder object
     */
    public function setOrderBy(QueryBuilder $qb)
    {
        $sortables = $this->requestParams->getIsSortables();
        foreach ($this->requestParams->getSortNamedFieldDirs() as $key => $dir)
        {
            if ($sortables[$key])
            {
                $index = array_search($key,$this->parameters);
                $qb->addOrderBy($this->associations[$index]['fullName'],$dir);
            }
        }
    }

    /**
     * Configure the WHERE clause for the Doctrine QueryBuilder if any searches are specified
     *
     * @param QueryBuilder The Doctrine QueryBuilder object
     */
    public function setWhere(QueryBuilder $qb)
    {
        // Global filtering
        $globalSearch = $this->requestParams->getGlobalSearch();
        $searchables = $this->requestParams->getIsSearchables();
        if ($globalSearch != '') {
            $orExpr = $qb->expr()->orX();
            foreach($this->parameters as $i => $name) 
            {
                if (isset($searchables[$name]) && $searchables[$name] ) {
                    $qbParam = "search_global_{$this->associations[$i]['entityName']}_{$this->associations[$i]['fieldName']}";
                    $orExpr->add($qb->expr()->like(
                        $this->associations[$i]['fullName'],
                        ":$qbParam"
                    ));
                    $qb->setParameter($qbParam, "%" . $globalSearch . "%");
                }
            }
            $qb->where($orExpr);
        }
        
        // Individual column filtering
        $searches = $this->requestParams->getSearches();
        if ($searches && join("", $searches)!='')
        {
        	$rangeSeparator = $this->requestParams->getRangeSeparator();
        	$andExpr = $qb->expr()->andX();
            foreach($this->parameters as $i => $name) {
                if (isset($searchables[$name]) && $searchables[$name]  && $searches[$name] != '' && (!$rangeSeparator ||  $searches[$name]!=$rangeSeparator)) {
                    $qbParam = "search_single_{$this->associations[$i]['entityName']}_{$this->associations[$i]['fieldName']}";
                    
                    // range filtering field
                    if ($rangeSeparator && strpos( $searches[$name],$rangeSeparator)!==false)
                    {                   
						$values= split($rangeSeparator, $searches[$name]);
						if ($values[0] != "")
						{
							$andExpr->add($qb->expr()->gte(
									$this->associations[$i]['fullName'],
									":$qbParam"
							));
							$qb->setParameter($qbParam, $values[0]);							
						}
						if ($values[1] != "")
						{
							$andExpr->add($qb->expr()->lte(
									$this->associations[$i]['fullName'],
									":".$qbParam."1"
							));
							$qb->setParameter($qbParam."1", $values[1]);
						}						
                    }
                    // simple filtering field
                    else
                    {
                    	$andExpr->add($qb->expr()->like(
                    			$this->associations[$i]['fullName'],
                    			":$qbParam"
                    	));
                    	$qb->setParameter($qbParam, "%" . $searches[$name] . "%");                    	
                    }
                }
            }
            if ($andExpr->count() > 0) {
                $qb->andWhere($andExpr);
            }
        }
        
        if (!empty($this->callbacks['WhereBuilder'])) {
            foreach ($this->callbacks['WhereBuilder'] as $callback) {
                $callback($qb);
            }
        }
    }

    /**
     * Configure joins for entity associations
     *
     * @param QueryBuilder The Doctrine QueryBuilder object
     */
    public function setAssociations(QueryBuilder $qb)
    {
        foreach ($this->assignedJoins as $joinName => $joinInfo) {
            $joinType = isset($this->joinTypes[$joinInfo['mdataColumn']]) ?
                $this->joinTypes[$joinInfo['mdataColumn']] :  $this->defaultJoinType;
            call_user_func_array(array($qb, $joinType . 'Join'), array(
                $joinInfo['joinOn'],
                $joinName
            ));
        }
    }

    /**
     * Configure the specific columns to select for the query
     *
     * @param QueryBuilder The Doctrine QueryBuilder object
     */
    public function setSelect(QueryBuilder $qb)
    {
        $columns = array();
        $partials = array();

        // Make sure all related joins are added as needed columns. A column many entities deep may rely on a
        // column not specifically requested in the mData
        foreach (array_keys($this->assignedJoins) as $joinName) {
            $columns[$joinName] = array();
        }

        // Combine all columns to pull
        foreach ($this->associations as $column) {
            $parts = explode('.', $column['fullName']);
            $columns[$parts[0]][] = $parts[1];
        }

        // Partial column results on entities require that we include the identifier as part of the selection
        foreach ($this->identifiers as $joinName => $identifiers) {
            if (!in_array($identifiers[0], $columns[$joinName])) {
                array_unshift($columns[$joinName], $identifiers[0]);
            }
        }

        // Make sure to include the identifier for the main entity
        if (!in_array($this->rootEntityIdentifier, $columns[$this->tableName])) {
            array_unshift($columns[$this->tableName], $this->rootEntityIdentifier);
        }

        foreach ($columns as $columnName => $fields) {
            $partials[] = 'partial ' . $columnName . '.{' . implode(',', $fields) . '}';
        }

        $qb->select(implode(',', $partials));
        $qb->from($this->metadata->getName(), $this->tableName);
    }

    /**
     * Method to execute after constructing this object. Configures the object before
     * executing getSearchResults()
     */
    public function makeSearch() 
    {
        $this->setSelect($this->qb);
        $this->setAssociations($this->qb);
        $this->setWhere($this->qb);
        $this->setOrderBy($this->qb);
        $this->setLimit($this->qb);

        return $this;
    }

    /**
     * Check if an array is associative or not.
     *
     * @link http://stackoverflow.com/questions/173400/php-arrays-a-good-way-to-check-if-an-array-is-associative-or-numeric
     * @param array An arrray to check
     * @return bool true if associative
     */
    protected function isAssocArray(array $array) {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Execute the QueryBuilder object, parse and save the results
     * @return ResultDoctrine
     */
    public function executeSearch()
    {
        $output = new $this->resultClass();

        $query = $this->qb->getQuery()->setHydrationMode(Query::HYDRATE_ARRAY);
        $items = $this->useDoctrinePaginator ?
            new Paginator($query, $this->doesQueryContainCollections()) : $query->execute();

        foreach ($items as $item) {
            if ($this->useDtRowClass && !is_null($this->dtRowClass)) {
                $item['DT_RowClass'] = $this->dtRowClass;
            }
            if ($this->useDtRowId) {
                $item['DT_RowId'] = $item[$this->rootEntityIdentifier];
            }
            // Go through each requested column, transforming the array as needed for DataTables
            for ($i = 0 ; $i < count($this->parameters); $i++) {
                // Results are already correctly formatted if this is the case...
                if (!$this->associations[$i]['containsCollections'])
                {
                    continue;
                }

                $rowRef = &$item;
                $fields = explode('.', $this->parameters[$i]);

                // Check for collection based entities and format the array as needed
                while ($field = array_shift($fields)) 
                {
                    $rowRef = &$rowRef[$field];
                    // We ran into a collection based entity. Combine, merge, and continue on...
                    if (!empty($fields) && !$this->isAssocArray($rowRef)) {
                        $children = array();
                        while ($childItem = array_shift($rowRef)) {
                            $children = array_merge_recursive($children, $childItem);
                        }
                        $rowRef = $children;
                    }
                }
            }
            $output->addData($item);
        }

        $output->setSEcho($this->requestParams->getEcho());
        $output->setITotalDisplayRecords($this->getCountFilteredResults());
        //$output->setITotalRecords($this->getCountAllResults());

        return $output;
    }

    /**
     * @return boolean Whether any mData contains an association that is a collection
     */
    protected function doesQueryContainCollections()
    {
        foreach ($this->associations as $column) {
            if ($column['containsCollections']) {
                return true;
            }
        }
        return false;
    }

    
    public function setResultClass($resultClass)
    {
    	$this->resultClass = $resultClass;
    }
    
    public function getResult()
    {
        $this->makeSearch();
        return $this->executeSearch();
    }

    /**
     * @return int Total query results before searches/filtering
     */
    public function getCountAllResults()
    {
        $qb = $this->repository->createQueryBuilder($this->tableName)
            ->select('count(' . $this->tableName . '.' . $this->rootEntityIdentifier . ')');

        if (!empty($this->callbacks['WhereBuilder']) && $this->hideFilteredCount)  {
            foreach ($this->callbacks['WhereBuilder'] as $callback) {
                $callback($qb);
            }
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    
    /**
     * @return int Total query results after searches/filtering
     */
    public function getCountFilteredResults()
    {
        $qbClone = clone $this->qb;

        $qbClone->resetDQLParts(array("select","groupBy","having","orderBy"))
                ->select('count(distinct ' . $this->tableName . '.' . $this->rootEntityIdentifier . ')')
                ->setFirstResult(null)
                ->setMaxResults(null);
        
        return (int) $qbClone->getQuery()->getSingleScalarResult();
    }

    /**
     * @param object A callback function to be used at the end of 'setWhere'
     */
    public function addWhereBuilderCallback($callback) {
        if (!is_callable($callback)) {
            throw new \Exception("The callback argument must be callable.");
        }
        $this->callbacks['WhereBuilder'][] = $callback;

        return $this;
    }

    public function getSearch()
    {
        return  "%" . $this->search . "%";
    }

    public function getQueryBuilder()
    {
        return  $this->qb;
    }
}
