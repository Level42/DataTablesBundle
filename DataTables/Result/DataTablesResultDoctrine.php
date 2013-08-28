<?php
namespace Level42\Bundle\DataTablesBundle\DataTables\Result;

use JMS\Serializer\Annotation\SerializedName;

use Symfony\Component\HttpFoundation\ParameterBag;


class DataTablesResultDoctrine implements DataTablesResultInterface
{
    /**
     * @SerializedName("aaData")
     * @var unknown
     */
    private $aaData = array();

    /**
     * @SerializedName("sEcho")
     * @var unknown
     */
    private $sEcho;

    /**
     * @SerializedName("iTotalRecords")
     * @var unknown
     */
    private $iTotalRecords;
    
    /**
     * @SerializedName("iTotalDisplayRecords")
     * @var unknown
     */
    private $iTotalDisplayRecords;


    public function addData($data)
    {
        $this->aaData[] = $data;
    }

    /**
     * @return the unknown_type
     */
    public function getSEcho()
    {
        return $this->sEcho;
    }

    /**
     * @param unknown_type $sEcho
     */
    public function setSEcho($sEcho)
    {
        $this->sEcho = $sEcho;
    }

    /**
     * @return the unknown_type
     */
    public function getITotalRecords()
    {
        return $this->iTotalRecords;
    }

    /**
     * @param unknown_type $iTotalRecords
     */
    public function setITotalRecords($iTotalRecords)
    {
        $this->iTotalRecords = $iTotalRecords;
    }

    /**
     * @return the unknown_type
     */
    public function getITotalDisplayRecords()
    {
        return $this->iTotalDisplayRecords;
    }

    /**
     * @param unknown_type $iTotalDisplayRecords
     */
    public function setITotalDisplayRecords($iTotalDisplayRecords)
    {
        $this->iTotalDisplayRecords = $iTotalDisplayRecords;
        if (!$this->iTotalRecords)
        {
            $this->iTotalRecords = $iTotalDisplayRecords;
        }
    }


    
}
