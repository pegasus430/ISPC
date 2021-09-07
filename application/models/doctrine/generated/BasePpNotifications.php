<?php
/**
 * 
 * @author  Feb 12, 2020  ancuta
 * ISPC-2432
 * 12.02.2020
 */
abstract class BasePpNotifications extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('pp_notifications');
			
		}

		
	}
	