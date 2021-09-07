<?php

	abstract class BaseSapAnfrage extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('bavaria_sapanfrage');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('datum_der_anfrage', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ehrenamtliche', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('beziehung_zum_patient', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('grunde_fur_die_anfrage', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('kommentar_spez', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('relevante_diagnosen', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('relevante_nebendiagnosen', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('vermittelt_von', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hausarzt_praxis', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hausarzt_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hausarzt_mobil', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('palliativarzt_mobil', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('palliativkraft_mobil', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('pflegedienst_tel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('hospizhelfer_tel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('klinikum_tel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('krankenkasse_tel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('besonderheiten', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patient_einverstanden_mit_anfrage', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('aktueller_aufenthalt_des_patienten', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('empfehlung', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('procedere', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('anfragende_person', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>