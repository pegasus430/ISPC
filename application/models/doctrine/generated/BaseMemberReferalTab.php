<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('MemberReferalTab', 'SYSDAT');

abstract class BaseMemberReferalTab extends Doctrine_Record {

	public function setTableDefinition()
	{
		$this->setTableName('member_referal_tab');
		
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
		
		$this->hasColumn('memberid', 'integer', 11, array(
				'type' => 'integer',
				'length' => 11,
				'fixed' => false,
				'unsigned' => false,
				'primary' => false,
				'default' => '0',
				'notnull' => true,
				'autoincrement' => false,
		));	

		
		$this->hasColumn('referal_tab',	'enum',	null, array(
				'type' => 'enum',
				'notnull' => false,
				'values' => array('members','donors'),
				'default' => 'members',
		));
		
		//add index
		$this->index('id', array(
				'fields' => array('id'),
				'primary' => true
		));

		$this->index('clientid', array(
				'fields' => array('clientid')
		));
		 
		$this->index('memberid', array(
				'fields' => array('memberid')
		));
		
		$this->index('referal_tab', array(
				'fields' => array('referal_tab')
		));
		
		
		
	}

	
	public function setUp()
	{
        parent::setUp();
        
        $this->hasOne('Member', array(
        		'local' => 'memberid',
        		'foreign' => 'id'
        ));
        
        $this->actAs(new Timestamp());
        
        
        $this->actAs(new Softdelete());

    }

}

?>