<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Projects', 'SYSDAT');

/**
 * BaseProjects
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $project_ID
 * @property integer $client_id
 * @property string $name
 * @property string $description
 * @property date $prepare_from
 * @property date $prepare_till
 * @property date $open_from
 * @property date $open_till
 * @property integer $isdelete
 * @property timestamp $create_date
 * @property integer $create_user
 * @property timestamp $change_date
 * @property integer $change_user
 * 
 * @package    ISPC
 * @subpackage Application (2018-05-11)
 * @author     claudiu <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseProjects extends Pms_Doctrine_Record
{
    
    const CLIENT_FILES_TABNAME = 'upload-add_project_files'; //TODO put this in js.. cause now is hardcoded
    
    
    public function setTableDefinition()
    {
        $this->setTableName('projects');
        $this->hasColumn('project_ID', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             'default' => NULL,
             ));
        $this->hasColumn('client_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('name', 'string', 512, array(
             'type' => 'string',
             'length' => 512,
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('prepare_from', 'date', null, array(
             'type' => 'date',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('prepare_till', 'date', null, array(
             'type' => 'date',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('open_from', 'date', null, array(
             'type' => 'date',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('open_till', 'date', null, array(
             'type' => 'date',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
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
        $this->hasColumn('create_date', 'timestamp', null, array(
             'type' => 'timestamp',
             'fixed' => false,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
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
        $this->hasColumn('change_date', 'timestamp', null, array(
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
        */
        
//         $this->index('PRIMARY', array(
// 				'fields' => array('project_ID'),
// 				'primary' => true
// 		));
		
		$this->index('idx_client_id', array(
				'fields' => array('client_id')
		));
		
		$this->index('idx_isdeleted', array(
				'fields' => array('isdelete')
		));
    }

    public function setUp()
    {
        parent::setUp();
        
        $this->actAs(new Timestamp());
        
        $this->actAs(new Softdelete());
        
        $this->hasMany('ProjectComments', array(
            'local' => 'project_ID',
            'foreign' => 'project_ID',
            'owningSide' => true,
            //'cascade' => array('delete')
        ));
        $this->hasMany('ProjectParticipants', array(
            'local' => 'project_ID',
            'foreign' => 'project_ID',
            'owningSide' => true,
            //'cascade' => array('delete')
        ));
        $this->hasMany('ClientFileUpload as ProjectFiles', array(
            'local' => 'project_ID',
            'foreign' => 'recordid'
//             'foreign' => "recordid AND `tabname`='" . self::CLIENT_FILES_TABNAME . "'" 
             //how do I define in the model , association mapping with custom conditions and alias? or this should be done in _get? 
        ));
        
        $this->hasMany('ProjectOutsideParticipants', array(
            'local' => 'project_ID',
            'foreign' => 'project_ID',
            'owningSide' => true,
            //'cascade' => array('delete')
        ));
        
        
    }
}