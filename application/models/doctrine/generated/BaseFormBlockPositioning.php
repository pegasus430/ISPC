<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('FormBlockPositioning', 'MDAT');

/**
 * BaseFormBlockPositioning
 * #ISPC-2512PatientCharts
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $ipid
 * @property integer $contact_form_id
 * @property enum $source
 * @property timestamp $positioning_date
 * @property enum $positioning_type
 //ISPC-2662+ISPC-2661 pct.13 Carmen 04.09.2020
 * @property enum $positioning_additional_info_old
 * @property object $positioning_additional_info
 * @property timestamp $positioning_start_date
 * @property timestamp $positioning_end_date
 * @property integer $isenduncertain
//--
 * @property integer $isdelete
 * @property integer $create_user
 * @property timestamp $create_date
 * @property integer $change_user
 * @property timestamp $change_date
 * 
 * @package    ISPC
 * @subpackage Application (2020-04-10) ISPC-2522
 * @author     carmen <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseFormBlockPositioning extends Pms_Doctrine_Record
{
	/**
	 * default when inserting into patient_course
	 */
	const PATIENT_COURSE_TYPE       = 'K';
	
    public function setTableDefinition()
    {
        $this->setTableName('form_block_positioning');
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
        $this->hasColumn('contact_form_id', 'integer', 8, array(
        		'type' => 'integer',
        		'length' => 8,
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => false,
        		'autoincrement' => false,
        ));
        $this->hasColumn('source', 'enum', 3, array(
        		'type' => 'enum',
        		'length' => 3,
        		'fixed' => false,
        		'unsigned' => false,
        		'values' =>
        		array(
        				0 => 'cf',
        				1 => 'charts',
        		),
        		'primary' => false,
        		'default' => null,
        		'notnull' => true,
        		'autoincrement' => false,
        ));
        //ISPC-2661 pct.13 Carmen 04.09.2020
        /* $this->hasColumn('positioning_date', 'timestamp', null, array(
        		'type' => 'timestamp',
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => false,
        		'autoincrement' => false,
        )); */
        //--
        //ISPC-2662 Carmen 28.08.2020
        $this->hasColumn('positioning_type', 'enum', 8, array(
             'type' => 'enum',
             'length' => 8,
             'fixed' => false,
             'unsigned' => false,
             /* 'values' => 
             array(
             	1 => 'rechts',
             	2 => 'links',
             	3 => 'Bauchlage',
             	4 => 'Rückenlage',
             	5 => 'Bettkante',
             	6 => 'Rollstuhl',
             	7 => 'Mobilisationsstuhl',
             	8 => 'Stand',
             ), */
             'primary' => false,
             'default' => null,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('positioning_additional_info_old', 'enum', 4, array(
        		'type' => 'enum',
        		'length' => 4,
        		'fixed' => false,
        		'unsigned' => false,
        		'values' =>
        		array(
        			1 => '0',
        			2 => '30',
        			3 => '90',
        			4 => '180',
        		),
        		'primary' => false,
        		'default' => null,
        		'notnull' => true,
        		'autoincrement' => false,
        ));
        
        $this->hasColumn('positioning_additional_info', 'object', null, array(
        		'type' => 'object',
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => false,
        		'autoincrement' => false,
        ));
        //--
        //ISPC-2661 pct.13 Carmen 04.09.2020
        $this->hasColumn('form_start_date', 'timestamp', null, array(
        		'type' => 'timestamp',
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => false,
        		'autoincrement' => false,
        ));
        $this->hasColumn('form_end_date', 'timestamp', null, array(
        		'type' => 'timestamp',
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => false,
        		'autoincrement' => false,
        ));
        $this->hasColumn('isenduncertain', 'integer', 4, array(
        		'type' => 'integer',
        		'length' => 4,
        		'fixed' => false,
        		'unsigned' => false,
        		'primary' => false,
        		'notnull' => false,
        		'autoincrement' => false,
        ));        
        //--
        $this->index('idx_isdeleted', array(
             'fields' => 
             array(
              0 => 'isdelete',
             ),
		));
    }

    public function setUp()
    {
        parent::setUp();
        
        $this->actAs(new Timestamp());
        
        $this->actAs(new Softdelete());
        
        /*
         * disabled by default, because it was created JUST for inserts from the Kontaktformular
         */
        $this->addListener(new PostInsertWriteToPatientCourseListener(array(
            "disabled"      => true,
            "course_title"  => static::PATIENT_COURSE_TITLE,
            "tabname"       => static::PATIENT_COURSE_TABNAME,
            "course_type"   => static::PATIENT_COURSE_TYPE,
            //"done_name"     => static::PATIENT_COURSE_DONE_NAME,
        )), 'PostInsertWriteToPatientCourse');
        
        //ISPC-2662 Carmen 28.08.2020
        $this->setColumnOption('positioning_type', 'values', [
        		1 => 'Lagerung nicht möglich',
        		2 => 'Pat positioniert sich eigenständig',
        		3 => 'rechts',
             	4 => 'links',
             	5 => 'Bauchlage',
             	6 => 'Rückenlage',
             	7 => 'Bettkante',
             	8 => 'Sitzend im Stuhl',
             	9 => 'Stand',
        		10 => 'Histatuch',
        		11 => 'Hängematte',
        		12 => 'Patient lagert sich selbst' //ISPC-2661 pct.7 Carmen 11.09.2020
        		
        ]);
        
        $this->setColumnOption('positioning_additional_info', 'values', [
        		'storage' => array(
        			1 => '0',
        			2 => '30',
        			3 => '90',
        			4 => '135',
        			5 => '180'
        		),
        		'storage_support' => array(
        			1 => 'Keilkissen',
        			2 => 'Kretakissen',
        			3 => 'Sonstiges'
        		)
        ]);
       //-- 
    }
}