<?php

	abstract class BasePdfFields extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('pdf_fields');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('fieldid', 'integer', 8, array('type' => 'varchar', 'length' => 8));
			$this->hasColumn('fieldelementid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('pdfid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('pageno', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('type', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('label', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('linkedtable', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('linkedfield', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('options', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('columns', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('description', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('content', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('posx', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('posy', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('dimwidth', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('dimheight', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('labelwidth', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('labelhide', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('labelfont', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('labelfontsize', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('linethickness', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('linelength', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('linecolor', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('ishide', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{

			$this->actAs(new Timestamp());
		}

	}

?>