<?php

	abstract class BasePainQuestionnaire extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('pain_questionnaire');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			//1
			$this->hasColumn('intensity', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//2
			$this->hasColumn('quality', 'string', 50, array('type' => 'string', 'length' => 50));
			//ISPC-2802,Elena,16.03.2021
            $this->hasColumn('quality_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			
			//3
			$this->hasColumn('localisation', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('point_location', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('point_location_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			//4
			$this->hasColumn('perception', 'string', 50, array('type' => 'string', 'length' => 50));
			//5 
			$this->hasColumn('expression', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('expression_other', 'text', NULL, array('type' => 'text', 'length' => NULL));
			//6			
			$this->hasColumn('relief', 'string', 50, array('type' => 'string', 'length' => 50));
			$this->hasColumn('relief_comment', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>