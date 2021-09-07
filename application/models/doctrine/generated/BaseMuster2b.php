<?php

	Doctrine_Manager::getInstance()->bindComponent('Muster2b', 'MDAT');

	abstract class BaseMuster2b extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('muster2b');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('physician_treatment', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('emergency', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('accident_accidental', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('service_related_injury', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('accessible_hospitals', 'text', NULL, array('type' => 'text', 'length' => NULL, 'nullable'=>true));
			$this->hasColumn('relevante_diagnoses', 'text', NULL, array('type' => 'text', 'length' => NULL, 'nullable'=>true));
			$this->hasColumn('investigation_results', 'text', NULL, array('type' => 'text', 'length' => NULL, 'nullable'=>true));
			$this->hasColumn('measures_to_date', 'text', NULL, array('type' => 'text', 'length' => NULL, 'nullable'=>true));
			$this->hasColumn('other_notes', 'text', NULL, array('type' => 'text', 'length' => NULL, 'nullable'=>true));
			$this->hasColumn('submitted_results', 'text', NULL, array('type' => 'text', 'length' => NULL, 'nullable'=>true));
			$this->hasColumn('iscompleted', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('completed_date', 'datetime', 255, array('type' => 'varchar', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>