<?php

	abstract class BasePriceRpDta extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('price_rp_dta');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('shortcut', 'string', 20, array('type' => 'string', 'length' => 20));
			$this->hasColumn('location_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('2', '3', '5')));// 2= hospiz,3 (or 4) Pflegeheim or Altenheim,5 = zuhause
			$this->hasColumn('sapv_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('be', 'beko', 'tv','vv')));
			$this->hasColumn('dta_id', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dta_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('dta_price', 'decimal', 10, array('scale' => 2));
		}

	}

?>