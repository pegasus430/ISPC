<?php

	abstract class BaseAnlage5Part1 extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('anlage5part1');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('date_time', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('team_name', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('user_details', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('patient_name', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('hi_number_dob', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('disease_base', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('general_condition', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('living_will', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('living_will_more', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			//do=diseased organs
			$this->hasColumn('do_hearth', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('do_neurologically', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('do_psychiatrically', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('do_lungs', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('do_liver', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('do_kidney', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('do_other', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('do_other_more', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			//sp=supply
			$this->hasColumn('sp_peg_sonde', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sp_port', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sp_zvk', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sp_pumpe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sp_catheter', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//ss = social situation
			$this->hasColumn('ss_unknown', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ss_living_alone', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ss_living_partner', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ss_no_support_partner', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ss_nurse_exists', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>