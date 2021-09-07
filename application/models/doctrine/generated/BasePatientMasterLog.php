<?php

/*
 * @al3x on 16.12.2019
 *
 */
abstract class BasePatientMasterLog extends Pms_Doctrine_Record
{

    function setTableDefinition()
    {
        $this->setTableName('patient_master_log');
        $this->hasColumn('id', 'bigint', NULL, array(
            'type' => 'bigint',
            'length' => NULL
        ));
        $this->hasColumn('referred_by', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11
        ));
        $this->hasColumn('recording_date', 'datetime', NULL, array(
            'type' => 'datetime',
            'length' => NULL
        ));
        $this->hasColumn('ipid', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('first_name', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('middle_name', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('last_name', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('title', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('salutation', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('street1', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('street2', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('zip', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('city', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('phone', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('mobile', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('email', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('kontactnumber', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('kontactnumbertype', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('birthd', 'integer', 10, array(
            'type' => 'integer',
            'length' => 10
        ));
        $this->hasColumn('birth_name', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('birth_city', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('sex', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('denomination_id', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11
        ));
        $this->hasColumn('familydoc_id', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11
        ));
        $this->hasColumn('familydoc_id_qpa', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11
        ));
        $this->hasColumn('pflegedienste', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11
        ));
        $this->hasColumn('pflege_comment', 'text', NULL, array(
            'type' => 'text',
            'length' => NULL
        ));
        $this->hasColumn('admission_date', 'datetime', NULL, array(
            'type' => 'datetime',
            'length' => NULL
        ));
        $this->hasColumn('fdoc_caresalone', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('nation', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('isdelete', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('isdischarged', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('isstandby', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('ishospiz', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('ishospizverein', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('isarchived', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('isstandbydelete', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('last_update', 'datetime', NULL, array(
            'type' => 'datetime',
            'length' => NULL
        ));
        $this->hasColumn('last_update_user', 'bigint', NULL, array(
            'type' => 'bigint',
            'length' => NULL
        ));
        $this->hasColumn('living_will', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('living_will_from', 'datetime', NULL, array(
            'type' => 'integer',
            'length' => NULL
        ));
        $this->hasColumn('living_will_deposited', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('comment', 'text', NULL, array(
            'type' => 'text',
            'length' => NULL
        ));
        $this->hasColumn('vollversorgung', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('vollversorgung_date', 'datetime', NULL, array(
            'type' => 'datetime',
            'length' => NULL
        ));
        $this->hasColumn('traffic_status', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('isadminvisible', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('wlanlage7completed', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1
        ));
        $this->hasColumn('orderadmission', 'integer', 11, array(
            'type' => 'integer',
            'length' => 11
        ));
        $this->hasColumn('import_pat', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('height', 'string', 255, array(
            'type' => 'string',
            'length' => 255
        ));
        $this->hasColumn('is_contact', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1,
            'default' => 0,
            'comments' => 'ist die Kontakt-Telefonnummer'
        ));
    }

    function setUp()
    {
        // nothing special, just logging
    }
}

?>