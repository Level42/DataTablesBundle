<?php
namespace Level42\Bundle\DataTablesBundle\DataTables\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @author dajay
 *
 */
class Params
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     *
     * @api
     */
    private $request;

    /**
     *
     * @param array $request
     */
    public function __construct(ParameterBag $request)
    {
        $this->request = $request;
    }

    /**
     * get Display start point in the current data set.
     *
     * @return int
     */
    public function getStart()
    {
        return (int) $this->request->get("iDisplayStart",0);
    }

    /**
     * get Number of records that the table can display in the current draw.
     * It is expected that the number of records returned will be equal to this number,
     * unless the server has fewer records to return.
     *
     * @return int
     */
    public function getLimit()
    {
        return (int) $this->request->get("iDisplayLength",10);
    }

    /**
     * get Number of columns 
     *
     * @return int
     */
    public function getNbColumns()
    {
        return (int) $this->request->get("iColumns",0);
    }    
    
    
    /**
     * get Number of columns being displayed (useful for getting individual column search info)
     *
     * @return int
     */
    public function getNbDisplayedColumns()
    {
        $nb = 0;
        for ($i = 0; $i < $this->request->get("iColumns"); $i++) 
        {
            if ($this->request->get("mDataProp_" . $i) != "")
            {
                $nb++;
            }
        }
        return $nb;
    }

    /**
     * Get Global search field
     * @return string
     */
    public function getGlobalSearch()
    {
        return (string) $this->request->get("sSearch","");
    }

    /**
     * Get True if the global filter should be treated as a regular expression for advanced filtering, false if not.
     * @return boolean
     */
    public function isRegexEnable()
    {
        return (boolean) $this->request->get("bRegex",false);
    }

    /**
     * return an array of multivalue datatable param
     * @param string $paramName
     * @return array
     */
    protected function getNamedArray($paramName)
    {
        $array = array();
        for ($i = 0; $i < $this->request->get("iColumns"); $i++) 
        {
            if ($this->request->get("mDataProp_" . $i) != "")
            {
                $array[$this->request->get("mDataProp_" . $i, $i)] = $this->request->get($paramName . "_" . $i);
            }
        }
        return $array;
    }

    /**
     * Get named array of Indicator for if a column is flagged as searchable or not on the client-side
     * @return array
     */
    public function getIsSearchables()
    {
        return $this->getNamedArray("bSearchable",false);
    }

    /**
     * Get named array of Individual column filter
     * @return array
     */
    public function getSearches()
    {
        return $this->getNamedArray("sSearch");
    }

    /**
     * Get named array of True if the individual column filter should be treated as a regular expression for advanced filtering, false if not
     * @return array
     */
    public function getIsRegexs()
    {
        return $this->getNamedArray("bRegex");
    }

    /**
     * Get named array of Indicator for if a column is flagged as sortable or not on the client-side
     * @return array
     */
    public function getIsSortables()
    {
        return $this->getNamedArray("bSortable");
    }

    /**
     * Get Number of columns to sort on
     * @return int
     */
    public function getNbSortingCols()
    {
        return (int) $this->request->get("iSortingCols",0);
    }

    /**
     * Get named array of Column being sorted on 
     * @return array
     */
    public function getIsSortCols()
    {
        $array = array();
        for ($i = 0; $i < $this->getNbSortingCols(); $i++)
        {
            $idCol = $this->request->get("iSortCol_$i");
            $array[$idCol] = $this->request->get("mDataProp_$idCol");
        }        

        return $array;
    }

    /**
     * Get named array of Direction to be sorted - "desc" or "asc".
     * @return array
     */
    public function getSortDirs()
    {
        $array = array();
        for ($i = 0; $i < $this->getNbSortingCols(); $i++)
        {
            $array[$this->request->get("iSortCol_$i")] = $this->request->get("sSortDir_$i");
        }        
        return $array;
    }

    /**
     * Get key named array of Direction to be sorted - "desc" or "asc".
     * @return array
     */
    public function getSortNamedFieldDirs()
    {
        $array = array();
        for ($i = 0; $i < $this->getNbSortingCols(); $i++)
        {
            $idCol = $this->request->get("iSortCol_$i");
            $array[$this->request->get("mDataProp_$idCol")] = $this->request->get("sSortDir_$i");
        }
        return $array;
    }    
    
    /**
     * Get array of The value specified by mDataProp for each column. 
     * This can be useful for ensuring that the processing of data is independent from the order of the columns.
     * @return array
     */
    public function getFieldNames()
    {
        $array = array();
        for ($i = 0; $i < $this->request->get("iColumns"); $i++) 
        {
            if ($this->request->get("mDataProp_" . $i) != "")
            {
                $array[$i] = $this->request->get("mDataProp_" . $i, $i);
            }
        }
        return $array;
    }

    /**
     * Get Information for DataTables to use for rendering.
     * @return string
     */
    public function getEcho()
    {
        return $this->request->get("sEcho","0");
    }
	
    
    /**
     * Get separator for field range
     * @return string
     */
    public function getRangeSeparator()
    {
    	return $this->request->get("sRangeSeparator",false);
    }
    
}
