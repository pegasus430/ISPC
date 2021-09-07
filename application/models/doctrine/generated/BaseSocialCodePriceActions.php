<?php

	abstract class BaseSocialCodePriceActions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('social_code_price_actions');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('list', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('actionid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('aorder', 'integer', 11, array('type' => 'integer', 'length' => 11));
		}

		function setUp()
		{
			$this->hasOne('SocialCodeActions', array(
				'local' => 'actionid',
				'foreign' => 'id'
			));

			$this->hasOne('SocialCodePriceList', array(
				'local' => 'list',
				'foreign' => 'id'
			));
		}

	}

?>