<?php

abstract class BaseSapvVerordnung extends Pms_Doctrine_Record 
{

	function setTableDefinition()
	{
		$this->setTableName('patient_sapvverordnung');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('sapv_order', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('verordnet_von', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('verordnet_von_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('family_doctor', 'specialists', 'locations')));
		$this->hasColumn('extra_set', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('primary_set', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('secondary_set', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('bra_options', 'text', NULL, array('type' => 'string', 'length' => NULL));
		$this->hasColumn('case_number', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('verordnungam', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('verordnungbis', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('regulation_start', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('regulation_end', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('verorddisabledate', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('approved_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('approved_number', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('verordnet', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('fromdate', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('tilldate', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('status', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('after_opposition', 'integer', 1, array('type' => 'integer', 'length' => 1));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
	}

	public function setUp()
	{
	    parent::setUp();
	    
		$this->actAs(new Timestamp());
		
		
		//ISPC-2614 Ancuta 16-17.07.2020
		$this->addListener(new IntenseConnectionListener(array(
		    
		)), "IntenseConnectionListener");
		//
		
	}
	
	
	

	/**
	 * @cla on 19.10.2018
	 *
	 * (non-PHPdoc)
	 * @see Doctrine_Record::preSave()
	 */
	public function preSave($event)
	{
	    parent::preSave($event);
	
	    $invoker = $event->getInvoker();
	    

	    $dateStart = null;
	    if ( ! empty($this->regulation_start)) {
	        $dateStart = new Zend_Date($this->regulation_start);
	        $this->regulation_start = $dateStart->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
// 	        $this->regulation_start = null;
	    }
	    
	    
	    $dateEnd = null;
	    if ( ! empty($this->regulation_end)) {
	        $dateEnd = new Zend_Date($this->regulation_end);
	        
	        if ($dateStart && $dateEnd->compareDate($dateStart) == -1) {
	            //end is after start... nice
	            $this->regulation_end = $dateStart->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	            $dateEnd = $dateStart;
	        } else {
    	        $this->regulation_end = $dateEnd->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	        }
	        
	    } else {
// 	        $this->regulation_end = null;
	    }
	     
	    
	    
	    $dateAm = null;
	    if ( ! empty($this->verordnungam)) {
	        $dateAm = new Zend_Date($this->verordnungam);
	        
	        if ($dateStart && $dateAm->compareDate($dateStart) == -1) {
	            //this is a condition, we must have Genehmigungszeitraum <= Verordnungszeitraum
	            $this->verordnungam = $dateStart->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	        } else {
	            $this->verordnungam = $dateAm->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	        }
	    } else {
// 	        $this->verordnungam = null;
	    }
	     
	    if ( ! empty($this->verordnungbis)) {
	        $dateBis = new Zend_Date($this->verordnungbis);

	        if ($dateAm && $dateBis->compareDate($dateAm) == -1) {
	            //end is after start... nice
	            $this->verordnungbis = $dateStart->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	            $dateBis = $dateAm;
	        } elseif ($dateEnd && $dateBis->compareDate($dateEnd) == 1) {
	            //this is a condition, we must have Genehmigungszeitraum <= Verordnungszeitraum
	            $this->verordnungbis = $dateEnd->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	        }
	        else {
	            $this->verordnungbis = $dateBis->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	        }
	    } else {
// 	        $this->verordnungbis = null;
	    }
	    
	    
	     
	    if ( ! empty($this->approved_date)) {
	        $date = new Zend_Date($this->approved_date);
	        $this->approved_date = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
// 	        $this->approved_date = null;
	    }
	     
	    if ( ! empty($this->verorddisabledate)) {
	        $date = new Zend_Date($this->verorddisabledate);
	        $this->verorddisabledate = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
// 	        $this->verorddisabledate = null;
	    }
	    
	    
	}
}

?>