<?php
/**
 * 
 * @author claudiu
 *
 */

// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('MemberAutoNumber', 'SYSDAT');

abstract class BaseMemberAutoNumber extends Doctrine_Record
{
	function setTableDefinition()
	{
		$this->setTableName('member_auto_number');
		
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
		
		$this->hasColumn('start_number', 'VARCHAR', 255, array(
				'type' => 'VARCHAR',
				'length' => 255,
				'fixed' => false,
				'default' => '0',
				'notnull' => true,
		));		
		
		//add index
		$this->index('id', array(
				'fields' => array('id'),
				'primary' => true
		));
		
		$this->index('clientid_isdelete', array(
				'fields' => array('clientid', 'isdelete')
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