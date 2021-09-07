<?php


abstract class BaseChurches extends Pms_Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('churches');
		$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('id_master', 'integer', 8, array('type' => 'integer', 'length' => 8));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
		//ISPC-2612 Ancuta 25.06.2020
		$this->hasColumn('connection_id', 'integer', 4, array(
		    'type' => 'integer',
		    'length' => 4,
		    'fixed' => false,
		    'unsigned' => false,
		    'primary' => false,
		    'notnull' => false,
		    'autoincrement' => false,
		    'comment' => 'id from connections_master',
		));
		$this->hasColumn('master_id', 'integer', 11, array(
		    'type' => 'integer',
		    'length' => 11,
		    'fixed' => false,
		    'unsigned' => false,
		    'primary' => false,
		    'notnull' => false,
		    'autoincrement' => false,
		    'comment' => 'id from of master entry from parent client',
		));
		//--
		$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('contact_firstname', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('contact_lastname', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('street', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('zip', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('phone', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('phone_cell', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('email', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('indrop', 'integer', 1, array('type' => 'indrop', 'length' => 1));
		$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
		$this->hasColumn('valid_from', 'integer', 11, array('type' => 'integer', 'length' => 11));
		$this->hasColumn('valid_till', 'integer', 11, array('type' => 'integer', 'length' => 11));		
	}

	function setUp()
	{
	    parent::setUp();
	    
	    $this->hasOne('PatientChurches', array(
	        'local' => 'id',
	        'foreign' => 'chid'
	    ));
	    
	    /*
	     * if you request is from patient/versorger, prevent deleting the ones with indrop=0
	     */
	    $this->addListener(new PreventIndrodDelete(array("indrop" => "indrop")));
	    
	    $this->actAs(new Softdelete());
	    
		
		$this->actAs(new Timestamp());
		
		
		
		//ISPC-2612 Ancuta 29.06.2020
		// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
		$this->addListener(new ListConnectionListner(array(
		    
		)), "ListConnectionListner");
		//
	}

}

?>