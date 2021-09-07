<?php
/**
 * 
 * @author  Jan 30, 2020  ancuta
 * ISPC-2432
 */

abstract class BasePpDevices extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->setTableName('pp_devices');
    }
}
	