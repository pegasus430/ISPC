<?php

	abstract class BaseUsersAssociation extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('users_association');
			$this->hasColumn('id', 'int', 10, array('type' => 'int', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('client', 'int', 10, array('type' => 'int', 'length' => 11));
			$this->hasColumn('user', 'int', 11, array('type' => 'int', 'length' => 11));
			$this->hasColumn('associate', 'int', 11, array('type' => 'int', 'length' => 11));
		}

	}

?>