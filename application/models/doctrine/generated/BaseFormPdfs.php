<?php

	abstract class BaseFormPdfs extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('formpdfs');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('formid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('pdfid', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{

			$this->hasOne('FbForms', array(
				'local' => 'formid',
				'foreign' => 'id'
			));

			$this->hasOne('PdfForms', array(
				'local' => 'pdfid',
				'foreign' => 'id'
			));
		}

	}

?>