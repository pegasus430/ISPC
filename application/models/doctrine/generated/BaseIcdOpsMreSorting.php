<?php

Doctrine_Manager::getInstance()->bindComponent('IcdOpsMreSorting', 'SYSDAT');

/**
 * ISPC-2654 Lore 06.10.2020
 * @author Loredana
 *
 */
    abstract class BaseIcdOpsMreSorting extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('icd_ops_mre_sorting');
			
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('main_sort_col', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('secondary_sort_col', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sort_order', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));			
		}

		function setUp()
		{
		    parent::setUp();
		    		    
		    /*
		     *  auto-added by builder
		     */
		    $this->actAs(new Softdelete());
		    
		    /*
		     *  auto-added by builder
		     */
		    $this->actAs(new Timestamp());
		}

	}

?>