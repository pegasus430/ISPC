<?php
/**
 * 
 * @author    Ancuta
 * changes made - for ISPC-2452
 */
abstract class BaseSapExcelTemplate extends Doctrine_Record {
	function setTableDefinition() {
		$this->setTableName ( 'sap_excel_template' );
		
		$this->hasColumn ( 'id', 'integer', NULL, array (
				'type' => 'integer',
				'length' => NULL,
				'primary' => true,
				'autoincrement' => true 
		) );
		$this->hasColumn('export_type', 'enum', 7, array(
		    'type' => 'enum',
		    'length' => 7,
		    'fixed' => false,
		    'unsigned' => false,
		    'values' =>
		    array(
		        0 => 'sap_txt',
		        1 => 'sap_ii_txt',
		    ),
		    'primary' => false,
		    'notnull' => true,
		    'autoincrement' => false,
		));
		
		$this->hasColumn ( 'mappe', 'string', 255, array (
		    'type' => 'string',
		    'length' => 255
		) );
		$this->hasColumn ( 'xls_line', 'integer', 10, array (
				'type' => 'integer',
				'length' => 10 
		) );
		
		$this->hasColumn ( 'line', 'integer', 10, array (
				'type' => 'integer',
				'length' => 10 
		) );
 
		
		$this->hasColumn ( 'nr', 'integer', 10, array (
				'type' => 'integer',
				'length' => 10 
		) );
		
		$this->hasColumn ( 'field', 'string', 255, array (
				'type' => 'string',
				'length' => 255 
		) );
		
		$this->hasColumn ( 'description', 'text', NULL, array (
				'type' => 'string',
				'length' => NULL,
				'fixed' => false,
				'unsigned' => false,
				'primary' => false,
				'notnull' => false,
				'autoincrement' => false 
		) );

		
		$this->hasColumn ( 'type', 'string', 255, array (
				'type' => 'string',
				'length' => 255 
		) );
		
		$this->hasColumn ( 'value_length', 'string', 255, array (
				'type' => 'string',
				'length' => 255 
		) );
		
		$this->hasColumn ( 'import_date', 'timestamp', null, array (
				'type' => 'timestamp',
				'fixed' => false,
				'unsigned' => false,
				'primary' => false,
				'notnull' => false,
				'autoincrement' => false 
		) );

		$this->hasColumn ( 'explanation', 'text', NULL, array (
				'type' => 'string',
				'length' => NULL,
				'fixed' => false,
				'unsigned' => false,
				'primary' => false,
				'notnull' => false,
				'autoincrement' => false
		) );
 
	}
	function setUp() {
	}
}

?>