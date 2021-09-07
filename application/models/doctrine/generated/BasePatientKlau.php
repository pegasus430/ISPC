<?php

/**
 * this is to store HL7 KLAU information
 */
abstract class BasePatientKlau extends Pms_Doctrine_Record
{

    public function setTableDefinition()
    {
        $this->setTableName('patient_klau');

        $this->hasColumn('id', 'bigint', NULL, array(
            'type' => 'bigint',
            'length' => NULL,
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('ipid', 'string', 255, array(
            'type' => 'string',
            'length' => 255,
        ));
        $this->hasColumn('date', 'datetime', NULL, array(
            'type' => 'datetime',
            'length' => NULL
        ));
        $this->hasColumn('messageraw', 'text', NULL, array(
            'type' => 'text',
            'length' => NULL
        ));
        $this->hasColumn('messagejson', 'text', NULL, array(
            'type' => 'text',
            'length' => NULL
        ));
    }

    public function setUp()
    {
        parent::setUp();

        $this->actAs(new Timestamp());
    }
}
