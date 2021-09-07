<?php

 //Maria:: Migration CISPC to ISPC 20.08.2020
class BaseInterventions extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('interventions');
        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));

        $this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('typ', 'string', 64, array('type' => 'string', 'length' => 64)); // medical/ nonmedical
        $this->hasColumn('opscode', 'string', 255, array('type' => 'string', 'length' => 255)); // OPScode
        $this->hasColumn('preparation', 'string', 255, array('type' => 'string', 'length' => 255)); // Präparat
        $this->hasColumn('active_ingredient', 'string', 255, array('type' => 'string', 'length' => 255)); // Wirkstoff
        $this->hasColumn('proceed_group', 'string', 255, array('type' => 'string', 'length' => 255)); // Verfahrengruppe
        $this->hasColumn('proceed_group_freetext', 'string', 255, array('type' => 'string', 'length' => 255)); // Verfahrengruppe freetext //ISPC-2630, elena, 29.09.2020

        $this->hasColumn('action_place', 'string', 255, array('type' => 'string', 'length' => 255)); // Ort der Durchführung
        $this->hasColumn('intervention_position', 'string', 255, array('type' => 'string', 'length' => 255)); // Ort der Intervention
        $this->hasColumn('main_symptom', 'string', 255, array('type' => 'string', 'length' => 255)); // Leitsymptom$
        $this->hasColumn('main_symptom_freetext', 'text'); // Leitsymptom_text
        $this->hasColumn('aim_reason', 'string', 255, array('type' => 'string', 'length' => 255)); // Grund der Durchführung / Ziel
        $this->hasColumn('frequency', 'string', 255, array('type' => 'string', 'length' => 255)); // Häufigkeit der Gabe
        $this->hasColumn('frequency_text', 'text'); // Häufigkeit / Text
        $this->hasColumn('dosageform', 'string', 255, array('type' => 'string', 'length' => 255)); // Darreichungsform

        $this->hasColumn('medication_type', 'string', 255, array('type' => 'string', 'length' => 255)); // Applikationsweg

        $this->hasColumn('dosis_absolute', 'float'); // Einzeldosis absolute

        $this->hasColumn('dosis_absolute_unit', 'string', 255, array('type' => 'string', 'length' => 255)); // Einzeldosis absolut, unit

        $this->hasColumn('count_day', 'string', 255, array('type' => 'string', 'length' => 255)); // Einzeldosis absolut, unit

        $this->hasColumn('duration_hours', 'float'); // Dauer in Stunden

        $this->hasColumn('interval_hours', 'float'); // Interval in Stunden

        $this->hasColumn('intervention', 'text'); // Häufigkeit / Text

        $this->hasColumn('first', 'datetime',NULL, array('type' => 'datetime','length' => NULL)); // Erste Gabe / erste Durchführung
        $this->hasColumn('last', 'datetime',NULL, array('type' => 'datetime','length' => NULL)); // Letzte Gabe / letzte Durchführung
        $this->hasColumn('is_ongoing', 'smallint', 6, array('type' => 'smallint', 'length' => 6)); //ist fortlaufend
        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));


    }


    function setUp()
    {
        $this->actAs(new Timestamp());

    }



}