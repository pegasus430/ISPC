<?php

	class TimestampTemplate extends Doctrine_Template {

		public function setTableDefinition()
		{
			$this->hasColumn('create_date', 'datetime');
			$this->hasColumn('change_date', 'datetime');
			$this->hasColumn('create_user', 'bigint');
			$this->hasColumn('change_user', 'bigint');

			$this->addListener(new TimestampListener());
		}

	}

?>