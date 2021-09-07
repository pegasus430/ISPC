<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('PatientContactphone', 'IDAT');

/**
 * BasePatientContactphone
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $ipid
 * @property enum $parent_table
 * @property integer $table_id
 * @property enum $from_locations
 * @property blob $phone
 * @property blob $mobile
 * @property blob $fax // ISPC-2550 Lore 17.02.2020
 * @property blob $last_name
 * @property blob $first_name
 * @property blob $other_name
 * @property integer $isdelete
 * @property integer $create_user
 * @property timestamp $create_date
 * @property integer $change_user
 * @property timestamp $change_date
 * 
 * @package    ISPC
 * @subpackage Application (2017-08-14)
 * @author     claudiu <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 * ISPC-2550 Loredana 17.02.2020  ADDED fax
 */
abstract class BasePatientContactphone extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('patient_contactphone');
        
        
        $this->hasColumn('id', 'integer', 8, array(
             'type' => 'integer',
             'length' => 8,
             'fixed' => false,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => false,
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
        $this->hasColumn('parent_table', 'enum', 19, array(
             'type' => 'enum',
             'length' => 19,
             'fixed' => false,
             'unsigned' => false,
             'values' => 
             array(
              0 => 'PatientMaster',
              1 => 'ContactPersonMaster',
              2 => 'Locations',
              3 => 'Pflegedienstes',
              4 => 'Physiotherapists',
              5 => 'Homecare',
              6 => 'PatientLocation',
             ),
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
        		'default' => 'PatientMaster',
             ));
        $this->hasColumn('table_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
        		'comments' => 'id from the parent_table'
             ));
        $this->hasColumn('from_locations', 'enum', 3, array(
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
        ));
        $this->hasColumn('phone', 'blob', null, array(
             'type' => 'blob',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
        		'default' => NULL,
             ));        
        $this->hasColumn('mobile', 'blob', null, array(
             'type' => 'blob',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
        		'default' => NULL,
             )); 
        //ISPC-2550 Lore 17.02.2020 
        $this->hasColumn('fax', 'blob', null, array(
            'type' => 'blob',
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'default' => NULL,
        )); 
        $this->hasColumn('last_name', 'blob', null, array(
             'type' => 'blob',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
        		'default' => NULL,
             ));
        $this->hasColumn('first_name', 'blob', null, array(
             'type' => 'blob',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
        		'default' => NULL,
             ));
        $this->hasColumn('other_name', 'blob', null, array(
             'type' => 'blob',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
        		'default' => NULL,
             ));
        $this->hasColumn('extra', 'blob', null, array(
             'type' => 'blob',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
        		'default' => NULL,
             ));
        /*
        $this->hasColumn('isdelete', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'default' => '0',
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
        $this->hasColumn('create_date', 'timestamp', null, array(
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
        */
        
        $this->index('id', array(
        		'fields' => array('id'),
        		'primary' => true
        ));
        
        $this->index('idx_ipid', array(
        		'fields' => array('ipid')
        ));
        
        $this->index('idx_parent_table', array(
        		'fields' => array(
        				'parent_table',
        				'table_id',
        )));
        	
        $this->index('idx_isdelete', array(
        		'fields' => array('isdelete')
        ));
    }

    public function setUp()
    {
        parent::setUp();
        
        $this->actAs(new Timestamp());
        
        $this->actAs(new Softdelete());    

        $this->actAs(new SoftEncrypt(array(
        		'phone',
        		'mobile',
        		'last_name',
        		'first_name',
        		'other_name',
        		'extra',
        )));  

        $this->actAs(new SoftDecrypt(array(
        		'phone',
        		'mobile',
        		'last_name',
        		'first_name',
        		'other_name',
        		'extra',
        )));
        
        $this->addListener(new HidemagicListener(array(
        		'phone',
        		'mobile',
        		'last_name',
        		'first_name',
        		'other_name',
        		'extra',
        		
        )));
        
        //preHydrate only for this table create the field phone_number
        $this->addListener(new PatientContactphoneHydrateListener(array(
        		'phone',
        		'mobile'
        )));
    }
}