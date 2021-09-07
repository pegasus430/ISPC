<?php

	abstract class BaseEthicalForm extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('ethical_form');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('capacitytoconsent', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('expressionofwill', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('living_checked', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('living_situation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('justificationforomission', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('nolongerindexed', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('nolongerindexed_textarea', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patientexpectations', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('patientexpectations_explicitrequest', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('consentdiscussion', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('consentdiscussion_explicitrequest', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('expectationsfamily','string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('expectationsfamily_withpatient', 'date', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('expectationsfamily_withsupervisor', 'date', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('expectationsfamily_withfamily', 'date', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('expectationsfamily_withotherservices', 'date', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('consensusbetween','string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('furtherinformation', 'string', 255, array('type' => 'string', 'length' => 255));
			
		}

	function setUp()
	{
		$this->actAs(new Timestamp());
	}

	}

?>