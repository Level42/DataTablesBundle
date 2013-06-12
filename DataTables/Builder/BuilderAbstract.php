<?php
namespace Level42\Bundle\DataTablesBundle\DataTables\Builder;
use Level42\Bundle\DataTablesBundle\DataTables\Request\Params;

abstract class BuilderAbstract implements BuilderInterface
{
    /**
     * List the http request's params 
     * 
     * @var Params
     */
    protected $requestParams;

    /**
     * builder options
     * 
     * @var array
     */
    protected $options;

    /**
     * Serializer
     * @var object the serializer
     */
    protected $serializer;

    public function setRequestParams(Params $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    public function getRequestParams()
    {
        return $this->requestParams;
    }

    public function init()
    {
        throw new Exception("to implement");
    }    
    
    public function getResult()
    {
        throw new Exception("to implement");
    }

    /**
     * @return the array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * get Serializer
     * @return the object
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * set Serializer
     * @param  $serializer
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

}
