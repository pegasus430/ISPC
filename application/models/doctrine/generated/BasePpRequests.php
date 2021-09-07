<?php
/**
 * 
 * @author  Jan 30, 2020  Ancuta
 * ISPC-2432
 *
 */
	abstract class BasePpRequests extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('pp_requests');
			
		}

		
	}
	