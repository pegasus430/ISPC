<?php

	abstract class BaseSocialCodeBonuses extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('social_code_bonuses');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('bonusname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('bonusshortcut', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->hasOne('SocialCodePriceBonuses', array(
				'local' => 'id',
				'foreign' => 'groupid'
			));
		}

	}

?>