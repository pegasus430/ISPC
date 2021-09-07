<?php

	abstract class BaseOverviewCookie extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('overview_cookie');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('cookie', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('page_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('useroption', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			/**
			 * @cla on 04.07.2018 ... colum useroption description ...
			 * useroption = 1 <=> Alle Boxen geschlossen == All boxes closed
			 * useroption = 2 <=> Nur Boxen mit Inhalt öffnen == Only open boxes with content
			 * useroption = 3 <=> persönliche Einstellungen == personal settings.. and you parse `cookie`
			 */
			
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>