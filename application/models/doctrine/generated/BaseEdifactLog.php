<?php
/**
 * Class BaseEdifactLog
 * Nico :: DemSTepCare - Special EDIFACT-Billing ISPC-2598
 */

abstract class BaseEdifactLog extends Doctrine_Record
{

    function setTableDefinition()
    {
        $this->setTableName('edifact_log');
        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('trustcenter', 'string', 255, array('type' => 'string', 'length' => 255));

        $this->hasColumn('clientid', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('send_ik', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('rec_ik', 'string', 255, array('type' => 'string', 'length' => 255));

        $this->hasColumn('billing_id', 'integer', 8, array('type' => 'integer', 'length' => 8));

        $this->hasColumn('fileno_ik', 'integer', 8, array('type' => 'integer', 'length' => 8));
        $this->hasColumn('fileno_trust', 'integer', 8, array('type' => 'integer', 'length' => 8));
        $this->hasColumn('msg', 'text', NULL, array('type' => 'text', 'length' => NULL));
        $this->hasColumn('file', 'text', NULL, array('type' => 'text', 'length' => NULL));

    }

    function setUp()
    {
        $this->actAs(new Timestamp());
    }

}

