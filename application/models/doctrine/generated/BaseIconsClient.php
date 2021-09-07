<?php

	Doctrine_Manager::getInstance()->bindComponent('IconsClient', 'SYSDAT');

	abstract class BaseIconsClient extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('icons_client');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('icon_id', 'integer', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('client_id', 'integer', 11, array('type' => 'bigint', 'length' => 11));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('color', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('image', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('patient', 'icons_vw', 'icons_member')));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
				
			$this->hasColumn(
					'icon_settings',
					'text',
					null,
					array(
							'type' => 'text', 
							'length' => null, 
							'comment'=> 'ISPC-1896 - add extra settings for any system icon ',
							'default' => null
					)
			);				
			
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			$this->actAs(new IconDefaultPermissions());
		}

	}

?>