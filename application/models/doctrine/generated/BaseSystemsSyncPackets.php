<?php
abstract class BaseSystemsSyncPackets extends Doctrine_Record
{

    function setTableDefinition ()
    {
        $this->setTableName('systems_sync_packets');
        $this->hasColumn('id', 'int', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
        $this->hasColumn('actionname', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('payload', 'string', NULL, array('type' => 'string', 'length' => NULL));
        $this->hasColumn('outgoing', 'int', 1, array('type' => 'integer', 'length' => 1));
        $this->hasColumn('done', 'int', 1, array('type' => 'integer', 'length' => 1));
    }

    function setUp ()
    {
        $this->actAs(new TimeStamp());
    }

}
?>