<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('PatientCurrentProblems', 'SYSDAT');

/**
 * BasePatientCurrentProblems
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $ipid
 * @property enum $icon
 * @property text $problem
 * @property integer $isdelete
 * @property integer $create_user
 * @property timestamp $create_date
 * @property integer $change_user
 * @property timestamp $change_date
 * 
 * @package    ISPC
 * @subpackage Application (2018-09-14)
 * @author     carmen <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BasePatientCurrentProblems extends Pms_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('patient_current_problems');
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
        $this->hasColumn('icon', 'enum', 3, array(
             'type' => 'enum',
             'length' => 3,
             'fixed' => false,
             'unsigned' => false,
			 'values' =>
        		array(
        			0 => 'measure',
        			1 => 'current_situation',
        			2 => 'sapv_appl',
        		    3 => 'ventilation',           //TODO-3707 Lore 06.01.2021
        	 ),
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('problem', 'text', null, array(
        	'type' => 'text',
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
        
        $this->index('idx_isdeleted', array(
        		'fields' =>
        		array(
        				0 => 'isdelete',
        		),
        ));
        
        $this->index('idx_ipid', array(
        		'fields' =>
        		array('ipid'),
        ));
        
        
    }

    public function setUp()
    {
        parent::setUp();
        
        $this->actAs(new Timestamp());
        
        $this->actAs(new Softdelete());
        
    }
}