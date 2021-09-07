<?php

// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ClientHeaderOption', 'MDAT');

//ISPC-2593 Lore 19.05.2020
//#ISPC-2512PatientCharts
abstract class BaseClientHeaderOption extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('client_header_option');
		$this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer','length' => 11));
		$this->hasColumn('header_option', 'enum', 30, array(
		    'type' => 'enum',
		    'length' => 30,
		    'fixed' => false,
		    'unsigned' => false,
		    'values' =>
		    array(
		        0 => 'show_location',
		        1 => 'show_age_weight',
		    ),
		    'primary' => false,
		    'default' => 'show_location',
		    'notnull' => true,
		    'autoincrement' => false,
		));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
	}
	
	function setUp()
	{
	    parent::setUp();
	    
	    $this->actAs(new Timestamp());
	    
	    $this->actAs(new Softdelete());
	    
	}
	

}

?>