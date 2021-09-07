<?php

	abstract class BaseLettersTextBoxes extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('letters_text_boxes');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('sapv_invoice_footer', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('sgbv_invoice_footer', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('nd_invoice_footer', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('greetings', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('erstverordnung_footer', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('folgeverordnung_footer', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{
			$this->hasOne('Client', array(
				'local' => 'clientid',
				'foreign' => 'id'
			));


			$this->actAs(new Timestamp());
		}

	}

?>