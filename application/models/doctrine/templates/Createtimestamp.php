<?php

	class Createtimestamp extends Doctrine_Template {

		public function setTableDefinition()
		{
			$this->hasColumn('create_date', 'datetime');
			$this->hasColumn('create_user', 'bigint');

			$this->addListener(new CreatetimestampListener());
		}

	}

?>