<?php

	abstract class BaseMedication extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('medication_master');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
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
			
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
// 			$this->hasColumn('pzn', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('pzn', 'integer', 8, array(
					'type' => 'integer',
					//         		'columnDefinition'=>"INT(8) UNSIGNED ZEROFILL",
					'zerofill' => true,
					'length' => 8,
					'fixed' => true,
					'notnull' => true,
			));

			$this->hasColumn('source', 'enum', null, array(
					'type' => 'enum',
					'notnull' => true,
					'default' => 'custom',
					'values' => array(
							 0 =>'',
							 1 =>'personal',
							 2 =>'mmi_receipt_dropdown',
							 3 =>'mmi_notreceipt_dropdown',
							 4 =>'mmi_dialog_product',
							 5 =>'mmi_dialog_price',
							 6 =>'custom',
							 7 =>'datamatrix',// added via xml parse from the medication page
							 8 =>'offline', // added from the cake version for the offline app
					    
			
					), // in the future there could be also vitabook
					'comment'=>'personal=from the personal list, mmi_= taken from mmi, custom=written or changed by hand'
			));
			
			$this->hasColumn('dbf_id', 'integer', 11, array(
					'type' => 'integer',
					'length' => 11,
					'fixed' => false,
					'unsigned' => true,
					'primary' => false,
					'notnull' => true,
					'autoincrement' => false,
					'comment' => "id of this medication, in mmi or personaldrugs table"
			));
						

			// ISPC-2912,Elena,25.05.2021  
            $this->hasColumn('is_btm', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));
			//--
			
			
			$this->hasColumn('description', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('package_size', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('amount_unit', 'float', NULL, array('type' => 'string', 'float' => NULL));
			$this->hasColumn('price', 'float', NULL, array('type' => 'string', 'float' => NULL));
			$this->hasColumn('extra', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('manufacturer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('package_amount', 'float', NULL, array('type' => 'string', 'float' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
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