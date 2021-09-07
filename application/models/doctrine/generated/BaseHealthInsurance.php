<?php

	abstract class BaseHealthInsurance extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('health_insurance');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
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
			$this->hasColumn('name2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('insurance_provider', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('street2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zip', 'string', 125, array('type' => 'string', 'length' => 125));
			$this->hasColumn('city', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('phone', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('phone2', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('phonefax', 'varchar', 255, array('type' => 'varchar', 'length' => 255));

			$this->hasColumn('post_office_box ', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('post_office_box_location ', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('email', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('zip_mailbox', 'varchar', 255, array('type' => 'varchar', 'length' => 255));

			$this->hasColumn('kvnumber', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('iknumber', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('debtor_number', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			
			$this->hasColumn('comments', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('valid_from', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('valid_till', 'date', NULL, array('type' => 'date', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('extra', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('onlyclients', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('price_sheet', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('price_sheet_group', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('he_price_list_type', 'varchar', 20, array('type' => 'varchar', 'length' => 20));
			
// 			$this->hasColumn('import_hi', 'string', 255, array('type' => 'string', 'length' => 255));

            // ISPC-2461 Ancuta 03.10.2019
			$this->hasColumn('demstepcare_billing', 'enum', 4, array(
			    'type' => 'enum',
			    'length' => 4,
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
			));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			
			
			$this->hasMany('HealthInsurance2Subdivisions', array(
					'local' => 'id',
					'foreign' => 'company_id'
			));
			
			
			//ISPC-2612 Ancuta 29.06.2020
			// DO NOT MOVE - Leave this at the end ( after Softdelete and Timestamp)
			$this->addListener(new ListConnectionListner(array(
			    
			)), "ListConnectionListner");
			//
		}

		
		
		
		//  aici trebuie facuta legatura dintre tabele -   ca in eroare iti spune ca nu gaseste alias -
		// faci legatura intre tabele , apoi se pune h.tabel -  
		
		
	}

?>