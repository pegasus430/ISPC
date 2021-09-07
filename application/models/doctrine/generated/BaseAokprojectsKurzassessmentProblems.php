<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('AokprojectsKurzassessmentProblems', 'MDAT');

/**
 * Class BaseAokprojectsKurzassessmentProblems
 * ISPC-2625, AOK Kurzassessment, 04.07.2020, elena
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class BaseAokprojectsKurzassessmentProblems extends Pms_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('aokprojects_kurzassessment_problems');

        $this->hasColumn('id', 'integer', 4, array(
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => true,
            'autoincrement' => true,
        ));
        $this->hasColumn('assessment_id', 'integer', 4, array(
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false,
        ));
        $this->hasColumn('assessment_name', 'enum', 15, array(
            'type' => 'enum',
            'length' => 15,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'MamboAssessment',
                    1 => 'WlAssessment',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('parent_table', 'string', 512, array(
            'type' => 'string',
            'length' => 512,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('parent_table_id', 'integer', 4, array(
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('fn_name', 'string', 255, array(
            'type' => 'string',
            'length' => 255,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false,
        ));
        $this->hasColumn('feedback', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Feedback',
        ));
        $this->hasColumn('feedback_val', 'array', null, array(
            'type' => 'array',
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('todo', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'TODO',
        ));
        $this->hasColumn('todo_val', 'array', null, array(
            'type' => 'array',
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('benefit_plan', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Versorgungsplan',
        ));
        $this->hasColumn('heart_monitoring', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Aufnahme in das Monitoring-Programm Herzinsuffizienz',
        ));
        $this->hasColumn('referral_to', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Überweisung zum Neurologen, - Psychologen, - Psychiater',
        ));
        $this->hasColumn('further_assessment', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Weiterführendes Assessment',
        ));
        $this->hasColumn('training_nutrition', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Ernährungsschulung',
        ));
        $this->hasColumn('training_adherence', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Schulung Adh�renz',
        ));
        $this->hasColumn('training_device', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Schulung device',
        ));
        $this->hasColumn('training_prevention', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Schulung zur Sturzprophylaxe',
        ));
        $this->hasColumn('training_incontinence', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Schulung Inkontinenz',
        ));
        $this->hasColumn('organization_careaids', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Organisation Pflege-/Hilfsmittel',
        ));
        $this->hasColumn('inclusion_COPD', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Aufnahme in das Monitoring-Programm COPD',
        ));
        $this->hasColumn('inclusion_measures', 'enum', 3, array(
            'type' => 'enum',
            'length' => 3,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'no',
                    1 => 'yes',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Einbindung Unterstützungsmaßnahmen',
        ));
        $this->hasColumn('isdelete', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'default' => '0',
            'notnull' => true,
            'autoincrement' => false,
        ));
        $this->hasColumn('create_user', 'integer', 4, array(
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false,
        ));
        $this->hasColumn('create_date', 'timestamp', null, array(
            'type' => 'timestamp',
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false,
        ));
        $this->hasColumn('change_user', 'integer', 4, array(
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('change_date', 'timestamp', null, array(
            'type' => 'timestamp',
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));


        $this->index('block_name', array(
            'fields' =>
                array(
                    0 => 'fn_name',
                ),
        ));
        $this->index('parent_table', array(
            'fields' =>
                array(
                    0 => 'parent_table',
                ),
        ));
        $this->index('isdelete', array(
            'fields' =>
                array(
                    0 => 'isdelete',
                ),
        ));
        $this->index('assessment_id', array(
            'fields' =>
                array(
                    0 => 'assessment_id',
                ),
        ));
    }


    public function setUp()
    {
        parent::setUp();

        $this->hasOne('AokprojectsKurzassessmentProblemStatus', array(
            'local' => 'id',
            'foreign' => 'assessment_problems_id'
        ));

        $this->hasMany('AokprojectsKurzassessmentProblemCourse', array(
            'local' => 'id',
            'foreign' => 'assessment_problems_id'
        ));


        /*
         *  auto-added by builder
         */
        $this->actAs(new Softdelete());

        /*
         *  auto-added by builder
         */
        $this->actAs(new Timestamp());
    }

}