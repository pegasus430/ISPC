<?php

	abstract class BasePharmacy extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('pharmacy');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
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
			
			$this->hasColumn('pharmacy', 'string', 255, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('salutation', 'string', 255, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('street1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zip', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('email', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kv_no', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('comments', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('valid_from', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('valid_till', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('indrop', 'integer', 1, array('type' => 'indrop', 'length' => 1));
			
			//ispc-2291
			$this->hasColumn('is_delivering', 'enum', 3, array(
			    'type' => 'enum',
			    'length' => 3,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'no',
			        1 => 'yes',
			    ),
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Apotheke liefert aus',
			));
			$this->hasColumn('order_interval', 'enum', 25, array(
			    'type' => 'enum',
			    'length' => 25,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'once',
			        1 => 'every_x_days',
			        2 => 'selected_days_of_the_week',
			    ),
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Rythmus der Belieferung',
			));
			$this->hasColumn('order_interval_options', 'object', 255, array(
			    'type' => 'object',
			    'length' => 255,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			$this->hasColumn('produces_infusion', 'enum', 3, array(
			    'type' => 'enum',
			    'length' => 3,
			    'fixed' => false,
			    'unsigned' => false,
			    'values' =>
			    array(
			        0 => 'no',
			        1 => 'yes',
			    ),
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Apotheke produziert Infusion',
			));
			$this->hasColumn('rhythm_preparation', 'string', 255, array(
			    'type' => 'string',
			    'length' => 255,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comment' => 'Rhytmus der Zubereitung',
			));
		}

		function setUp()
		{
		    parent::setUp();
		    
		    /*
		     * if you request is from patient/versorger, prevent deleting the ones with indrop=0
		     */
		    $this->addListener(new PreventIndrodDelete(array("indrop" => "indrop")));
		    
		    $this->actAs(new Softdelete());
		    
			$this->actAs(new Timestamp());
			
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
 
		}

	}

?>