<?php

	abstract class BaseContactPersonTempMaster extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('contactperson_tempmaster');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('sessionid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_first_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_middle_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_last_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_title', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_salutation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_street1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_street2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_zip', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_city', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_phone', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_mobile', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_email', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_birthd', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('cnt_sex', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('cnt_denomination_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('cnt_familydegree_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('cnt_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('cnt_nation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('cnt_hatversorgungsvollmacht', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('cnt_legalguardian', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('notify_funeral', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('quality_control', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('cnt_kontactnumber', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('cnt_custody', 'string', 255, array('type' => 'string', 'length' => 255));
		}
	}

?>