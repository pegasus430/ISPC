<?php

Doctrine_Manager::getInstance()->bindComponent('MedicationClientStockSeal', 'SYSDAT');
/**
 * 
 * @author claudiu
 *
 */
abstract class BaseMedicationClientStockSeal extends Doctrine_Record {

    public function setTableDefinition()
    {
        $this->setTableName('medication_client_stock_seal');
        
        $this->hasColumn('id', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'fixed' => false,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             ));
       
        
        $this->hasColumn('clientid', 'integer', 11, array(
        	'type' => 'integer',
        	'length' => 11,
        	'fixed' => false,
        	'unsigned' => false,
        	'primary' => false,
        	'default' => '0',
        	'notnull' => true,
        	'autoincrement' => false,
        ));
        
        $this->hasColumn('seal_date', 'timestamp', null, array(
        	'type' => 'timestamp',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
           
        ));
        
        
        $this->hasColumn('isdelete', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
             'notnull' => true,
             'autoincrement' => false,
             ));
        
        $this->hasColumn('create_date', 'timestamp', null, array(
             'type' => 'timestamp',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('create_user', 'integer', 8, array(
             'type' => 'integer',
             'length' => 8,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('change_date', 'timestamp', null, array(
             'type' => 'timestamp',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('change_user', 'integer', 8, array(
             'type' => 'integer',
             'length' => 8,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        
        
        $this->index('id', array(
        		'fields' => array('id'),
        		'primary' => true
        ));

        $this->index('clientid', array(
        		'fields' => array('clientid')
        ));
        	
        $this->index('seal_date', array(
        		'fields' => array('seal_date')
        ));
        
    }

    public function setUp()
    {
        parent::setUp();
        
        $this->actAs(new Timestamp());
        
        
        $this->actAs(new Softdelete());
        
        
    }
}


?>