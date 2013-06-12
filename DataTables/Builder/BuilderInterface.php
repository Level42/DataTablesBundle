<?php
namespace Level42\Bundle\DataTablesBundle\DataTables\Builder;

use Level42\Bundle\DataTablesBundle\DataTables\Request\Params;

/**
 * @author dajay
 *
 */
interface BuilderInterface
{
      
    /**
     * set  dataTables request params
     * 
     * @param Params $requestParams
     */
    public function setRequestParams(Params $requestParams);

    /**
     * get  dataTables request params
     *
     * @return Params $requestParams
     */
    public function getRequestParams();    
    
    /**
     * set  options builder
     *
     * @param array $options
     */
    public function setOptions(array $options);    
    
    
    /**
     * get builder options
     * @return array
     */
    public function getOptions();
    
    /**
     * initialize builder
     */
    public function init(); 
    
    /**
     * get  serializer
     *
     * @return Object $serializer
     */
    public function getSerializer();

    /**
     * set  serializer
     *
     * @param Object $serializer
     */
    public function setSerializer($serializer);    
    
    /**
     * get  dataTables result
     *
     * @return Object $result
    */
    public function getResult();    
    
}
