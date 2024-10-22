<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('PatientVices', 'IDAT');

/**
 * BasePatientVices
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $ipid
 * @property enum $smoke
 * @property enum $alcohol
 * @property enum $alcohol_frequency
 * @property integer $isdelete
 * @property integer $create_user
 * @property timestamp $create_date
 * @property integer $change_user
 * @property timestamp $change_date
 * 
 * @package    ISPC
 * @subpackage Application (2018-12-10)
 * @author     claudiu <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BasePatientVices extends Pms_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('patient_vices');

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
        $this->hasColumn('smoke', 'enum', 3, array(
             'type' => 'enum',
             'length' => 3,
             'fixed' => false,
             'unsigned' => false,
             'values' => 
             array(
              0 => 'no',
              1 => 'yes',
             ),
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'comment' => 'Rauchen Sie?',
             ));
        $this->hasColumn('alcohol', 'enum', 3, array(
             'type' => 'enum',
             'length' => 3,
             'fixed' => false,
             'unsigned' => false,
             'values' => 
             array(
              0 => 'no',
              1 => 'yes',
             ),
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'comment' => 'Trinken Sie regelm��ig Alkohol?',
             ));
        $this->hasColumn('alcohol_frequency', 'enum', 21, array(
             'type' => 'enum',
             'length' => 21,
             'fixed' => false,
             'unsigned' => false,
             'values' => 
             array(
              0 => 'every day',
              1 => '1 to 3 times a week',
              2 => 'less than once a week',
             ),
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('isdelete', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
             'notnull' => true,
             'autoincrement' => false,
             'comment' => '1=deleted',
             ));
        $this->hasColumn('create_user', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('create_date', 'timestamp', null, array(
             'type' => 'timestamp',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('change_user', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('change_date', 'timestamp', null, array(
             'type' => 'timestamp',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));


        $this->index('ipid', array(
             'fields' => 
             array(
              0 => 'ipid',
             ),
             ));
        $this->index('isdelete', array(
             'fields' => 
             array(
              0 => 'isdelete',
             ),
             ));
    }    
            

    public function setUp()
    {
        parent::setUp();
        
    }
}