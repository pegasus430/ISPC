<?php

	abstract class BaseDgpSapv extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_dgp_sapv');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('identifiknr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sapv', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('verordnung_datum', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('art_der_erordnung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('verordnung_durch', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ubernahme_aus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('arztlich', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('arztlich_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pflegerisch', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ambulanter_hospizdienst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('weitere_professionen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('weitere_professionen_more', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('regel_km', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('anzahl_der_teambes', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('krankenhause', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('end_date_sapv ', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sapvteam ', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('versorgungsstufe ', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('datum_der_erfassung2', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('grund_einweisung', 'string', 128, array('type' => 'string', 'length' => 128));
			$this->hasColumn('pcteam', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('therapieende', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>