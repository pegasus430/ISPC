<?php
/**
 * 
 * Maria:: Migration CISPC to ISPC 22.07.2020
 *
 */
abstract class BasePatientCaseStatusLog extends Doctrine_Record {

    function setTableDefinition()
    {
        $this->setTableName('patient_case_status_log');
        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
        $this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('case_id', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('log_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('log_type', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('log_data', 'string', NULL, array('type' => 'string','length' => NULL));
        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
    }

    function setUp(){

        $this->actAs(new Timestamp());
        $this->hasOne('PatientCaseStatus', array(
            'local' => 'case_id',
            'foreign' => 'id'
        ));

    }

}

?>
