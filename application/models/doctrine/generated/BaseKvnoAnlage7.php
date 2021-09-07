<?php

	abstract class BaseKvnoAnlage7 extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('kvno_anlage7');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wohnsituation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wohnsituations', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ecog', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitung', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('vorlage', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('datum_der_erfassung1', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));

			$this->hasColumn('schmerzen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ubelkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('erbrechen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('luftnot', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('verstopfung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('swache', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('appetitmangel', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('mudigkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('dekubitus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hilfebedarf', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('depresiv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anspannung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('desorientier', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('versorgung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('umfelds', 'integer', 1, array('type' => 'integer', 'length' => 1));

			$this->hasColumn('kontaktes', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('who', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('steroide', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chemotherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('strahlentherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('aufwand_mit', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('problem_besonders', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('problem_ausreichend', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('entlasung_date', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('therapieende', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sterbeort', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sterbeort_dgp', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('zufriedenheit_mit', 'integer', 1, array('type' => 'integer', 'length' => 1));
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
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>