<?php

	abstract class BaseAddrbookFavorites extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('addrbook_favorites');
			$this->hasColumn('id', 'bigint', 20, array('type' => 'bigint', 'length' => 20, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('user_id', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('fav_id', 'bigint', 20, array('type' => 'bigint', 'length' => 20));
			$this->hasColumn('type', 'string', 2, array('type' => 'string', 'length' => 2, 'default' => 0));
		}

		function setUp()
		{
			
		}

	}

?>
