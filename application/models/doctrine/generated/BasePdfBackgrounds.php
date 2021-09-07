<?php

	abstract class BasePdfBackgrounds extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('pdf_backgrounds');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('filename', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('pdf_type', 'varchar', 8, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('date_added', 'integer', 8, array('type' => 'integer', 'length' => 32));
		}

	}

?>