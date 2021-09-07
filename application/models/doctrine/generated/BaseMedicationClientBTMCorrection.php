<?php

Doctrine_Manager::getInstance()->bindComponent('MedicationClientBTMCorrection', 'SYSDAT');
/**
 * 
 * @author claudiu
 *
 */
abstract class BaseMedicationClientBTMCorrection extends Doctrine_Record {

    public function setTableDefinition()
    {
        $this->setTableName('medication_client_btm_correction');
        
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
        
                
        /**
         * tresor = MedicationClientStock
         * user = MedicationClientHistory
         * patient = MedicationPatientHistory - this is on another db
         */
        $this->hasColumn('correction_table', 'enum', null, array(
        	'type' => 'enum',	
            'notnull' => true,
       		'values' => array('MedicationClientStock', 'MedicationClientHistory', 'MedicationPatientHistory'),
        	'comment'=>'correction is for this table'
        ));
        
        $this->hasColumn('correction_id', 'integer', 11, array(
        		'type' => 'integer',
        		'length' => 11,
        		'fixed' => false,
        		'unsigned' => true,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        		'comment' => "id of the row we want to modify" // this is NOT unique, use can multiple-correct the same action, and even correct the correction
        ));
        
        $this->hasColumn('correction_new_id', 'integer', 11, array(
        		'type' => 'integer',
        		'length' => 11,
        		'fixed' => false,
        		'unsigned' => true,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        		'comment' => "id of the new resulted row" //correction_table+this should be unique if you dont mess around with the autoincrement
        ));
        
        $this->hasColumn('amount', 'integer', 11, array(
        		'type' => 'integer',
        		'length' => 11,
        		'fixed' => false,
        		'unsigned' => true,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        		'comment' => "only positive numbers here"
        ));
        
        $this->hasColumn('amount_corrected', 'integer', 11, array(
        		'type' => 'integer',
        		'length' => 11,
        		'fixed' => false,
        		'unsigned' => true,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        		'comment' => "amount we inserted in the original table"
        ));
        $this->hasColumn('amount_original', 'integer', 11, array(
        		'type' => 'integer',
        		'length' => 11,
        		'fixed' => false,
        		'unsigned' => true,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        		'comment' => ""
        ));
        
        $this->hasColumn('comment', 'text', null, array(
        		'type' => 'text',
        		'length' => null,
        		'comment' => ""
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

        $this->index('correction_table', array(
        		'fields' => array('correction_table')
        ));
        $this->index('correction_id', array(
        		'fields' => array('correction_id')
        ));
        	
        $this->index('clientid', array(
        		'fields' => array('clientid')
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