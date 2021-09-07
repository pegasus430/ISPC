<?php
/**
 * Class BaseEdifactCerts
 * Nico :: DemSTepCare - Special EDIFACT-Billing ISPC-2598
 */

abstract class BaseEdifactCerts extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->setTableName('edifact_certs');
        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('trustcenter', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('type', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('ik', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('cert', 'text', NULL, array('type' => 'text', 'length' => NULL));
        $this->hasColumn('valid', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
    }

    function setUp()
    {
        $this->actAs(new Timestamp());
    }

}

