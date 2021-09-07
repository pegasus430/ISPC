<?php

abstract class BasePatientVoluntaryworkers extends Pms_Doctrine_Record 
{

	public function setTableDefinition()
	{
		$this->setTableName('patient_voluntaryworkers');
		$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('vwid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
		$this->hasColumn('vw_comment', 'string', NULL, array('type' => 'string', 'length' => 255));
		
		$this->hasColumn('start_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		$this->hasColumn('end_date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
	}

	
	public function setUp()
	{
	    parent::setUp();
	    
	    //ISPC-1958
	    $this->hasOne("Voluntaryworkers", array(
	        'local'      => 'vwid',
	        'foreign'    => 'id',
	        'owningSide' => true,
	        'cascade'    => array('delete'),
	    ));
	    
		$this->actAs(new Timestamp());
		
		$this->actAs(new Softdelete());
		
		
       // ISPC-2614 Ancuta 20.07.2020
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
	     
	    $dateStart = null;
	    
	    if ( ! empty($this->start_date)) {	        
	        $dateStart = new Zend_Date($this->start_date);
	        $this->start_date = $dateStart->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	    } else {
// 	        $invoker->start_date = null;
	    }
	     
	    if ( ! empty($this->end_date)) {
	        $dateEnd = new Zend_Date($this->end_date);
	        if ($dateStart && $dateEnd->compareDate($dateStart) == -1) {
	            //end is after start... nice
	            $this->end_date = $dateStart->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	        } else {
	            $this->end_date = $dateEnd->toString( Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
	        }
	    } else {
// 	        $invoker->end_date = null;
	    }
    
	}

}

?>