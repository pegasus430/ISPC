<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanAtc', 'MDAT');

/**
 * PatientDrugPlanAtc
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $ipid
 * @property integer $drugplan_id
 * @property integer $medication_master_id
 * @property string $atc_code
 * @property string $atc_groupe_code
 * @property string $atc_description
 * @property string $atc_groupe_description
 * @property isdelete $isdelete
 * @property integer $create_user
 * @property timestamp $create_date
 * @property integer $change_user
 * @property timestamp $change_date
 * 
 * @package    ISPC-2554 pct.3
 * @subpackage Application (2020-03-26)
 * @author     Carmen <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BasePatientDrugPlanAtc extends Pms_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('patient_drugplan_atc');
        $this->option('type', 'INNODB');

        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('ipid', 'string', 255, array(
             'type' => 'string',
             'length' => 255,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('drugplan_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'comment' => 'id from patient_drugplan',
             ));
        $this->hasColumn('medication_master_id', 'integer', 4, array(
        		'type' => 'integer',
        		'length' => 4,
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => false,
        		'autoincrement' => false,
        		'comment' => 'id from patient_drugplan',
        ));
        $this->hasColumn('atc_code', 'string', 255, array(
        		'type' => 'string',
        		'length' => 255,
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        ));
        $this->hasColumn('atc_groupe_code', 'string', 255, array(
        		'type' => 'string',
        		'length' => 255,
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        ));
        $this->hasColumn('atc_description', 'string', 255, array(
        		'type' => 'string',
        		'length' => 255,
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        ));
        $this->hasColumn('atc_groupe_description', 'string', 255, array(
        		'type' => 'string',
        		'length' => 255,
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        ));
        $this->index('idx_ipid', array(
        		'fields' =>
        		array(
        				0 => 'ipid',
        		),
        ));
        $this->index('idx_isdeleted', array(
        		'fields' =>
        		array(
        				0 => 'isdelete',
        		),
        ));
        $this->index('idx_drugplan_id', array(
        		'fields' =>
        		array(
        				0 => 'drugplan_id',
        		),
        ));
        $this->index('idx_medication_master_id', array(
        		'fields' =>
        		array(
        				0 => 'medication_master_id',
        		),
        ));
    }    
            

    public function setUp()
    {
        parent::setUp();
        
        $this->actAs(new Timestamp());
        
        $this->actAs(new Softdelete());
        
        $this->hasOne('PatientDrugPlan', array(
        		'local' => 'drugplan_id',
        		'foreign' => 'id'
        ));
        
        $this->hasOne('Medication', array(
        		'local' => 'medication_master_id',
        		'foreign' => 'id'
        ));
    }
}