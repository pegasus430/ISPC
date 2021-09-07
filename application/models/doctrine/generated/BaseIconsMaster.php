<?php

	Doctrine_Manager::getInstance()->bindComponent('IconsMaster', 'SYSDAT');

	abstract class BaseIconsMaster extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('icons_master');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('color', 'string', 10, array('type' => 'string', 'length' => 10));
			$this->hasColumn('image', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('function', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn(
					'icon_settings',
					'text',
					null,
					array(
							'type' => 'text',
							'length' => null,
							'comment'=> 'ISPC-1896 - add extra settings for the system icon',
							'default' => null
					)
			);
			
		}

	}

?>