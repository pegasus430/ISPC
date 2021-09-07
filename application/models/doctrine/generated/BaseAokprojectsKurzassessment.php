<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('AokprojectsKurzassessment', 'MDAT');

/**
 * Class BaseAokprojectsKurzassessment
 * ISPC-2625, AOK Kurzassessment, 07.07.2020, elena
 * cloned from BaseMamboAssessment
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 
abstract class BaseAokprojectsKurzassessment extends Pms_Doctrine_Record
{

    public function setTableDefinition()
    {
        $this->setTableName('aokprojects_kurzassessment');

        $this->hasColumn('id', 'integer', 4, array(
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => true,
            'autoincrement' => true,
        ));
        $this->hasColumn('ipid', 'string', 255, array(
            'type' => 'string',
            'length' => 255,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
            'autoincrement' => false,
        ));
        $this->hasColumn('status', 'enum', 9, array(
            'type' => 'enum',
            'length' => 9,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'open',
                    1 => 'completed',
                ),
            'primary' => false,
            'default' => 'open',
            'notnull' => true,
            'autoincrement' => false,
        ));
        $this->hasColumn('formular_date_start', 'date', null, array(
            'type' => 'date',
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('formular_date_end', 'date', null, array(
            'type' => 'date',
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('contact_type_1', 'enum', 18, array(
            'type' => 'enum',
            'length' => 18,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'first strike',
                    1 => 'consequences',
                    2 => 'Acute use',
                    3 => 'Use wg. amendments',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Kontaktart drop1',
        ));
        $this->hasColumn('contact_type_2', 'enum', 10, array(
            'type' => 'enum',
            'length' => 10,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'Personally',
                    1 => 'by phone',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'Kontaktart drop2',
        ));
        $this->hasColumn('contact_location', 'enum', 17, array(
            'type' => 'enum',
            'length' => 17,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'practice',
                    1 => 'domesticity',
                    2 => 'hospital',
                    3 => 'counseling center',
                    4 => 'Nursing home',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('commitment', 'set', 18, array(
            'type' => 'set',
            'length' => 18,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    /*
                     * removed per request from ISPC-2292 14.02.2019 m)
                     * ! options have NOT been removed from the table
                    0 => 'information',
                    1 => 'consultation',
                    2 => 'training',
                    */
                    3 => 'Initial Assessment',
                    4 => 'Re-Assessment',
                    /*
                     5 => 'coordination',
                     */
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('services_usage', 'set', 33, array(
            'type' => 'set',
            'length' => 33,
            'fixed' => false,
            'unsigned' => false,
            'values' =>
                array(
                    0 => 'Care allowance',
                    1 => 'Semi stationary services',
                    2 => 'Benefits in kind',
                    3 => 'Inpatient services',
                    4 => 'Combined services',
                    5 => 'Care / relief services',
                    6 => 'Short term care / prevention care',
                    7 => 'Hourly preventive care',
                ),
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('formular_reason', 'string', null, array(
            'type' => 'string',
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('comment', 'string', null, array(
            'type' => 'string',
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('_touched_ids', 'object', null, array(
            'type' => 'object',
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
            'comment' => 'app, what tables we have altered',
        ));
        $this->hasColumn('isdelete', 'integer', 4, array(
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
        $this->hasColumn('create_user', 'integer', 4, array(
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => true,
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
        $this->hasColumn('change_user', 'integer', 4, array(
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));


        $this->index('ipid', array(
            'fields' =>
                array(
                    0 => 'ipid',
                ),
        ));
        $this->index('isdelete', array(
            'fields' =>
                array(
                    0 => 'isdelete',
                ),
        ));
        $this->index('status', array(
            'fields' =>
                array(
                    0 => 'status',
                ),
        ));
    }


    public function setUp()
    {
        parent::setUp();


        $this->hasMany('AssessmentProblems', array(
            'local' => 'id',
            'foreign' => 'assessment_id'
        ));

        /*
         *  auto-added by builder
         */
        $this->actAs(new Softdelete());

        /*
         *  auto-added by builder
         */
        $this->actAs(new Timestamp());


        $this->addListener(new PostInsertWriteToPatientCourseListener(array(
            "course_title"  => self::translate(static::PATIENT_COURSE_TITLE),
            "tabname"       => static::PATIENT_COURSE_TABNAME,
            "course_type"   => static::PATIENT_COURSE_TYPE,
            "done_name"     => static::PATIENT_COURSE_DONE_NAME,
        )), 'PostInsertWriteToPatientCourseListener');

    }





    /**
     * change format of the date
     *
     * (non-PHPdoc)
     * @see Doctrine_Record::preSave()
     */
    public function preSave($event)
    {
        parent::preSave($event);

//         $invoker = $event->getInvoker();

//         dd($this->toArray(), $invoker->toArray());

        $dateStart = null;

        if ( ! empty($this->formular_date_start)) {

            $dateStart = new Zend_Date($this->formular_date_start, $this->_date_format_datepicked);
            $this->formular_date_start = null;
            $this->formular_date_start = $dateStart->toString($this->_date_format_db);

        } else {

        }


        $dateEnd = null;
        if ( ! empty($this->formular_date_end)) {
            $dateEnd = new Zend_Date($this->formular_date_end, $this->_date_format_datepicked);

            if ($dateStart && $dateEnd->compareDate($dateStart) == -1) {
                //end is after start... nice
                // do $dateEnd = $dateStart;
                $this->formular_date_end = $this->formular_date_start; //$dateStart->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
            } else {
                $this->formular_date_end = null;
                $this->formular_date_end = $dateEnd->toString($this->_date_format_db);
            }

        } else {

        }


    }
    /**
     * change format of the date
     *
     * (non-PHPdoc)
     * @see Doctrine_Record::preHydrate()
     */
    public function preHydrate( Doctrine_Event $event )
    {
        if ( ! empty($event->data['formular_date_start']) ||  ! empty($event->data['formular_date_end'])  ) {

            $data = $event->data;

            if (Zend_Date::isDate($event->data['formular_date_start'], $this->_date_format_db)) {

                $date = new Zend_Date($event->data['formular_date_start']);
                $data['formular_date_start'] = $date->toString($this->_date_format_datepicked);
            }

            if (Zend_Date::isDate($event->data['formular_date_end'], $this->_date_format_db)) {

                $date = new Zend_Date($event->data['formular_date_end']);
                $data['formular_date_end'] = $date->toString($this->_date_format_datepicked);
            }

            $event->data = $data;

        }
    }



}