<?php


abstract class BaseMedicationReceipt extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('medication_receipt');
		$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint','length' => NULL, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint','length' => NULL));
		//ISPC-2612 Ancuta 25.06.2020-28.06.2020
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
		$this->hasColumn('name', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('pzn', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('description', 'text',NULL, array('type' => 'text','length' => NULL));
		$this->hasColumn('package_size', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('amount_unit', 'float',NULL, array('type' => 'string','float' => NULL));
		$this->hasColumn('price', 'float',NULL, array('type' => 'string','float' => NULL));
		$this->hasColumn('manufacturer', 'string', 255, array('type' => 'string','length' => 255));
		$this->hasColumn('package_amount', 'float',NULL, array('type' => 'string','float' => NULL));
		$this->hasColumn('extra', 'integer', 1, array('type' => 'integer','length' => 1));
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));
				
	}
	
	function setUp()
	{
		$this->hasOne('Client', array(
	            'local' => 'clientid',
	            'foreign' => 'id'
	        ));
		$this->actAs(new Timestamp());	
		
		
		
		//ISPC-2612 Ancuta 29.06.2020
		// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
		$this->addListener(new ListConnectionListner(array(
		    
		)), "ListConnectionListner");
		//
	}	
}

?>