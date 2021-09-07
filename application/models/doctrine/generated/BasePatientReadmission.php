<?php

// abstract class BasePatientReadmission extends Doctrine_Record {
abstract class BasePatientReadmission extends Pms_Doctrine_Record
{

    function setTableDefinition()
    {
        $this->setTableName('patient_readmission');
        $this->hasColumn('id', 'int', 11, array(
            'type' => 'int',
            'length' => 11,
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('user_id', 'int', 11, array(
            'type' => 'int',
            'length' => 11
        ));
        $this->hasColumn('ipid', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('date', 'datetime', NULL, array(
            'type' => 'datetime',
            'length' => NULL
        ));
        $this->hasColumn('date_type', 'int', 11, array(
            'type' => 'int',
            'length' => 11
        ));
        $this->hasColumn('special_medical_assistance', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('first_contact', 'integer', 2, array(
            'type' => 'integer',
            'length' => 2
        ));
    }

    function setUp()
    {
        $this->actAs(new Timestamp());
        
        /**
         * this is a twin row
         */
        /*
        $this->hasOne('PatientReadmission as ParentPatientReadmission', array(
            'local' => 'id',
            'foreign' => 'id'
        ));
        */
  
        
        //ISPC-2614 Ancuta 16.07.2020
        $this->addListener(new IntenseConnectionAdmissionsListener(array(
        )), "IntenseConnectionAdmissionsListener");
        //
        
//         //ISPC-2614 Ancuta 16.07.2020
//         $this->addListener(new IntenseConnectionListener(array(
            
//         )), "IntenseConnectionListener");
//         //
    }
}

?>