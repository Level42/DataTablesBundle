<?php

namespace Level42\Bundle\DataTablesBundle\DataTables\Result;

interface DataTablesResultInterface {
	

	/**
		Add data to the result
		
		@param array data result
	 */
	public function addData($data) ;
	
	/**
	 *
	 * @return the echo number
	 */
	public function getSEcho() ;
	
	/**
	 *
	 * @param string $sEcho        	
	 */
	public function setSEcho($sEcho) ;
	/**
	 *
	 * @return the total records
	 */
	public function getITotalRecords() ;
	
	/**
	 *
	 * @param int  $iTotalRecords        	
	 */
	public function setITotalRecords($iTotalRecords) ;
	
	/**
	 *
	 * @return int the total display records
	 */
	public function getITotalDisplayRecords() ;
	
	/**
	 *
	 * @param int $iTotalDisplayRecords        	
	 */
	public function setITotalDisplayRecords($iTotalDisplayRecords) ;
	
}
