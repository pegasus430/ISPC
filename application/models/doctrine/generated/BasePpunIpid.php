<?php

	abstract class BasePpunIpid extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('ppun_ipid');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('prefix', 'string', 255, array('type' => 'string', 'length' => 255));
//			ppun = Private Patient Unique Number
			$this->hasColumn('ppun', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('clientid', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>