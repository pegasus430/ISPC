<?php

Doctrine_Manager::getInstance()->bindComponent('VoluntaryworkersCoKoordinator', 'SYSDAT');
/**
 * 
 * @author claudiu
 *
 */
abstract class BaseVoluntaryworkersCoKoordinator extends Doctrine_Record {

    public function setTableDefinition()
    {
        $this->setTableName('voluntaryworkers_co_koordinator');
        
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
                
        $this->hasColumn('vw_id', 'integer', 11, array(
        		'type' => 'integer',
        		'length' => 11,
        		'fixed' => false,
        		'unsigned' => true,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        		'comment' => "id of the row we want to modify" 
        ));
        
        $this->hasColumn('vw_id_koordinator', 'integer', 11, array(
        		'type' => 'integer',
        		'length' => 11,
        		'fixed' => false,
        		'unsigned' => true,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        		'comment' => "id of the koordinator" 
        ));
        
      
        
        $this->index('id', array(
        		'fields' => array('id'),
        		'primary' => true
        ));

        $this->index('clientid', array(
        		'fields' => array('clientid')
        ));
        
        $this->index('vw_id+isdelete', array(
        		'fields' => array(
        				'vw_id',
        				'isdelete',
        		)
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