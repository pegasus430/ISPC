<?php

	abstract class BaseSocialCodeActions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('social_code_actions');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('internal_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('action_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('action_invoice_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('description', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('pos_nr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('max_per_day', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('default_duration', 'integer', 10, array('type' => 'integer', 'length' => 10));
			$this->hasColumn('price', 'decimal', 10, array('scale' => 2));
			$this->hasColumn('groupid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('custom', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('parent', 'integer', 10, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('issapv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('form_condition', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('extra', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('available', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('parent_list', 'integer', 10, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('night_bonus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nh_sunday_bonus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('multi_resistance_bonus', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->hasOne('SocialCodeGroups', array(
				'local' => 'groupid',
				'foreign' => 'id'
			));
			$this->actAs(new Timestamp());
		}

	}

?>