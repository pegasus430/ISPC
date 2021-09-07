<?php
//ISPC-2657, elena, 25.08.2020 (ELSA: Reaktionen)
// Maria:: Migration CISPC to ISPC 02.09.2020

class BaseSaeReactions extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('sae_reactions');
        $this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
        $this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));

        $this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
        $this->hasColumn('first_sae_date', 'date'); // Date of first diagnosis
        $this->hasColumn('first_sae_date_knowledge', 'string', 64, array('type' => 'string', 'length' => 64)); // Knowledge of date of first diagnosis (full date, month/year only, year only)
        $this->hasColumn('study', 'string', 255, array('type' => 'string', 'length' => 255)); // Studie
        $this->hasColumn('reaction_text', 'string', 255, array('type' => 'string', 'length' => 255)); // Reaction
        $this->hasColumn('cause', 'string', 255, array('type' => 'string', 'length' => 255)); // Ursache
        $this->hasColumn('place', 'string', 255, array('type' => 'string', 'length' => 255)); // Ort
        $this->hasColumn('consequence', 'string', 255, array('type' => 'string', 'length' => 255)); // Folgen

        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1, 'default' => 0));


    }


    function setUp()
    {
        $this->actAs(new Createtimestamp());

    }


}