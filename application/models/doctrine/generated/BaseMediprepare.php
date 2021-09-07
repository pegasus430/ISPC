<?php

/**
 * Class BaseMediprepare
 *
 * ISPC-2804,Elena,18.02.2021
 */
class BaseMediprepare extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('mediprepare');
        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));

        $this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('prepare_for_date', 'date'); //prepared for this date
        $this->hasColumn('criteria', 'text'); //prepared for this date
        $this->hasColumn('notice', 'string', 255, array('type' => 'string', 'length' => 255)); // Notice

        $this->hasColumn('isprepared', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));
        $this->hasColumn('preparedby', 'integer', 11, array('type' => 'integer', 'length' => 11));
        $this->hasColumn('isgiven', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));
        $this->hasColumn('givenby', 'integer', 11, array('type' => 'integer', 'length' => 11));

        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));

    }


    function setUp()
    {
        parent::setUp();

        $this->actAs(new Timestamp());
    }

}