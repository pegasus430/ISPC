<?php
/**
 * 
 * @author  Feb 4, 2020  ancuta
 * ISPC-2432
 * 12.02.2020
 */
abstract class BasePpPayloadDelivery extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('pp_payload_delivery');
			
		}

		
	}
	