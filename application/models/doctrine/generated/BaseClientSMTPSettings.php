<?php

Doctrine_Manager::getInstance()->bindComponent('ClientSMTPSettings', 'MDAT');
/**
 * 
 * @author claudiu
 *
 */
abstract class BaseClientSMTPSettings extends Doctrine_Record {

    public function setTableDefinition()
    {
        $this->setTableName('client_smtp_settings');
        
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
        
        $this->hasColumn('sender_name', 'varbinary', 255, array(
        		'type' => 'varbinary',
        		'length' => 255,
        		'comment' => ""
        ));
        $this->hasColumn('sender_email', 'varbinary', 255, array(
        		'type' => 'varbinary',
        		'length' => 255,
        		'comment' => ""
        ));
        
        $this->hasColumn('smtp_server', 'varchar', 255, array(
        		'type' => 'varchar',
        		'length' => 255,
        		'comment' => ""
        ));
        $this->hasColumn('smtp_port', 'integer', 6, array(
        		'type' => 'integer',
        		'length' => 6,
        		'comment' => ""
        ));
        $this->hasColumn('smtp_username', 'varbinary', 255, array(
        		'type' => 'varbinary',
        		'length' => 255,
        		'comment' => ""
        ));
        $this->hasColumn('smtp_password', 'varbinary', 255, array(
        		'type' => 'varbinary',
        		'length' => 255,
        		'comment' => ""
        ));
        
        $this->hasColumn('ssl_require', 'enum', null, array(
        	'type' => 'enum',	
            'notnull' => true,
       		'values' => array('NO', 'YES'),
        	'default' => 'NO',
        	'comment'=>''
        ));
        $this->hasColumn('ssl_port', 'integer', 6, array(
        		'type' => 'integer',
        		'length' => 6,
        		'fixed' => false,
        		'unsigned' => true,
        		'notnull' => false,
        		'autoincrement' => false,
        ));
        
        $this->hasColumn('tls_require', 'enum', null, array(
        		'type' => 'enum',
        		'notnull' => true,
        		'values' => array('NO', 'YES'),
        		'default' => 'NO',
        		'comment'=>''
        ));
        $this->hasColumn('tls_port', 'integer', 6, array(
        		'type' => 'integer',
        		'length' => 6,
        		'fixed' => false,
        		'unsigned' => true,
        		'notnull' => false,
        		'autoincrement' => false,
        ));
        

        
        
        $this->index('id', array(
        		'fields' => array('id'),
        		'primary' => true
        ));

        
        $this->index('clientid+isdelete', array(
        		'fields' => array(
        				'clientid',
        				'isdelete'
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