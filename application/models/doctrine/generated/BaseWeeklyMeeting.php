<?php
/**
 * 
 * Maria:: Migration CISPC to ISPC 22.07.2020
 *
 */
Doctrine_Manager::getInstance()->bindComponent('weekly_meeting', 'MDAT');
abstract class BaseWeeklyMeeting extends Doctrine_Record
{
    function setTableDefinition()
    {
        $this->setTableName('weekly_meeting');
        $this->hasColumn('id', 'integer', 11, array('type' => 'integer','length' => 11, 'primary' => true, 'autoincrement' => true));

        $this->hasColumn('date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));

        $this->hasColumn('ipid', 'string', 255, array('type' => 'string','length' => 255));
        $this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));

        $this->hasColumn('week', 'integer', 11, array('type' => 'integer','length' => 11));

        $this->hasColumn('course', 'text', NULL, array('type' => 'text','length' => NULL));
        $this->hasColumn('main_problems', 'text', NULL, array('type' => 'text','length' => NULL));

        $this->hasColumn('medic_goal', 'text', NULL, array('type' => 'text','length' => NULL));
        $this->hasColumn('medic_plan', 'text', NULL, array('type' => 'text','length' => NULL));

        $this->hasColumn('care_goal', 'text', NULL, array('type' => 'text','length' => NULL));
        $this->hasColumn('care_plan', 'text', NULL, array('type' => 'text','length' => NULL));

        $this->hasColumn('psy_goal', 'text', NULL, array('type' => 'text','length' => NULL));
        $this->hasColumn('psy_plan', 'text', NULL, array('type' => 'text','length' => NULL));

        $this->hasColumn('social_goal', 'text', NULL, array('type' => 'text','length' => NULL));
        $this->hasColumn('social_plan', 'text', NULL, array('type' => 'text','length' => NULL));

        $this->hasColumn('spiritual_goal', 'text', NULL, array('type' => 'text','length' => NULL));
        $this->hasColumn('spiritual_plan', 'text', NULL, array('type' => 'text','length' => NULL));

        $this->hasColumn('physio_goal', 'text', NULL, array('type' => 'text','length' => NULL));
        $this->hasColumn('physio_plan', 'text', NULL, array('type' => 'text','length' => NULL));

        $this->hasColumn('breath_goal', 'text', NULL, array('type' => 'text','length' => NULL));
        $this->hasColumn('breath_plan', 'text', NULL, array('type' => 'text','length' => NULL));

        $this->hasColumn('finished', 'integer', 1, array('type' => 'integer','length' => 1));
        $this->hasColumn('mins', 'string', 255, array('type' => 'string','length' => 255));

        $this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer','length' => 1));

        $this->hasColumn('create_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('create_user', 'int', 11, array('type' => 'bigint','length' => 20));
        $this->hasColumn('change_date', 'datetime', NULL, array('type' => 'datetime','length' => NULL));
        $this->hasColumn('change_user', 'int', 11, array('type' => 'bigint','length' => 20));

        $this->hasColumn('caseid', 'integer', 11, array('type' => 'integer','length' => 11));
        $this->hasColumn('casedate', 'string', 255, array('type' => 'string','length' => 255));
    }

    function setUp()
    {
        $this->actAs(new Timestamp());

    }
}

?>
