<?php

	abstract class BasePdfForms extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('pdf_forms');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('formid', 'integer', 8, array('type' => 'varchar', 'length' => 8));
			$this->hasColumn('pdfname', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('isdelete', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('name', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('version', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('dimension', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('dimensionwidth', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('dimensionheight', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('noofpages', 'integer', 8, array('type' => 'varchar', 'length' => 8));
			$this->hasColumn('header', 'text', NULL, array('type' => 'text'));
			$this->hasColumn('footer', 'text', NULL, array('type' => 'text'));
			$this->hasColumn('headerheight', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('footerheight', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
		}

		function setUp()
		{

			$this->actAs(new Timestamp());
		}

	}

?>