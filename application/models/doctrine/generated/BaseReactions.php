<?php
//ISPC-2657, elena, 25.08.2020 (ELSA: Reaktionen)
// Maria:: Migration CISPC to ISPC 02.09.2020

class BaseReactions extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('reactions');
        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));

        $this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('typ', 'string', 64, array('type' => 'string', 'length' => 64)); // allergie/ unverträglichkeit (allergy/intolerance)
        $this->hasColumn('icdcode', 'string', 255, array('type' => 'string', 'length' => 255)); // ICD-Code
        $this->hasColumn('reaction_against', 'string', 255, array('type' => 'string', 'length' => 255)); // Reaktion gegen
        $this->hasColumn('reaction_text', 'string', 255, array('type' => 'string', 'length' => 255)); // Reaktion freetext
        $this->hasColumn('first_diagnosis_date', 'date'); // Date of first diagnosis
        $this->hasColumn('first_diagnosis_date_knowledge', 'string', 64, array('type' => 'string', 'length' => 64)); // Knowledge of date of first diagnosis (full date, month/year only, year only)

        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));


    }


    function setUp()
    {
        $this->actAs(new Createtimestamp());

    }



}