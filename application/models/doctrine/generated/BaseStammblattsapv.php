<?php

	abstract class BaseStammblattsapv extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('stammblattsapv');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('familienstand', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('wohnsituation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('allergien', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('zuzahlung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('pflegestufe', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patientenverfugung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('vorsorgevollmacht', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('bevollmachtigter', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bevollmachtigter_tel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betreuung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('betreuer', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betreuer_handy', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betreuer_tel', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('betreuer_fax', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('erstkontakt_am', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('erstkontakt_durch', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ambulant', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('stationar', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('religion', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ecog', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('genogramm', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('main_diagnosis', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>