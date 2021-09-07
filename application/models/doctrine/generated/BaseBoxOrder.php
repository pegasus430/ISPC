<?php

	abstract class BaseBoxOrder extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('box_order');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			
			
			$this->hasColumn('boxcol', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			/*
			 * @cla
			 * [101, 102]; Patient Stammdaten
			 * [201, 202]; Patient Versorger
			 * [301, 302, 303]; Roster users
			 * [304, 305, 305]; Roster pseudogroups
			 * 
			 */
			
			$this->hasColumn('boxid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('boxorder', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}
	