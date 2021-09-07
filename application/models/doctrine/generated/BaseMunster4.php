<?php

	Doctrine_Manager::getInstance()->bindComponent('Munster4', 'MDAT');

	abstract class BaseMunster4 extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('munster4');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mittellung_von_krankheiten', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('mittellung_sonstiger_schaden_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('hauptleistung_krankenhaus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('krankenhaus_behandlungsdaten_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('hauptleistung_datum', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('ambulante_op_behandlung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ambulante_op_behandlung_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('ambulante_behandlung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ambulante_behandlung_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('begrundung_des_ausnahmefalls', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begrundung_des_ausnahmefalls_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('dauerhafte_mobilitat', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('dauerhafte_mobilitat_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('voraussichtliche_behandlungsfrequenz_woche', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('voraussichtliche_behandlungsfrequenz_monate', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('voraussichtliche_behandlungsdauer_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('zeitraum_serienverordnung_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('beforderungsmittel', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beforderungsmittel_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('begrundung_beforderungsmittels_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('medizinisch_technische', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('medizinisch_technische_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('wohnung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('arztpraxis', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('krankenhaus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('andere_beford', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('beforderungswege_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('hinfahrt', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wartezeit_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('gemeinschaftsfahrt_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('medizinisch_fachliche', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('medizinisch_fachliche_folgende_txt', 'string', NULL, array('type' => 'string', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}
