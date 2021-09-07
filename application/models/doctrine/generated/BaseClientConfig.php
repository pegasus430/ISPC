<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('ClientConfig', 'SYSDAT');
abstract class BaseClientConfig extends Doctrine_Record
{

    function setTableDefinition ()
    {
        $this->setTableName('client_config');
        $this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('client_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));

        $this->hasColumn('configitem', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
        $this->hasColumn('content', 'text', null, array('type' => 'text', 'length' => NULL));

    }

    function setUp ()
    {
        $this->actAs(new Timestamp());
    }

}
?>
