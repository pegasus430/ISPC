<?php

	abstract class BaseEmergencyPlan extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('emergency_plan');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('gesetzliche', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('besonderheiten', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vollmatch', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('mogliche1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mogliche2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mogliche3', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mogliche4', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mogliche5', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mogliche6', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('mogliche7', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vompatient1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vompatient2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vompatient3', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vompatient4', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vompatient5', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vompatient6', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('vompatient7', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>