<?php
/**
 * @author Nico
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */

abstract class BaseHl7DocSend extends Pms_Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('hl7_doc_send');

        $this->hasColumn('id', 'integer', 4, array(
            'type' => 'integer',
            'length' => 4,
            'fixed' => false,
            'unsigned' => false,
            'primary' => true,
            'autoincrement' => true,
        ));
        $this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
        $this->hasColumn('ipid', 'varchar', 255, array('type' => 'varchar', 'length' => 255));

        $this->hasColumn('file_id', 'integer', 8, array(
            'type' => 'integer',
            'length' => 8,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('uid', 'integer', 8, array(
            'type' => 'integer',
            'length' => 8,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'autoincrement' => false,
        ));
        $this->hasColumn('cf_id', 'integer', 8, array(
            'type' => 'integer',
            'length' => 8,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'autoincrement' => false,
        ));

        $this->hasColumn('sent_date', 'timestamp', null, array(
            'type' => 'timestamp',
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'notnull' => false,
            'autoincrement' => false,
        ));


        $this->hasColumn('muted', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'default' => '0',
            'notnull' => true,
            'autoincrement' => false,
        ));

        $this->hasColumn('signed_status', 'integer', 1, array(
            'type' => 'integer',
            'length' => 1,
            'fixed' => false,
            'unsigned' => false,
            'primary' => false,
            'default' => '0',
            'notnull' => true,
            'autoincrement' => false,
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


        $this->index('isdelete', array(
            'fields' =>
                array(
                    0 => 'isdelete',
                ),
        ));

    }


    public function setUp()
    {
        parent::setUp();
        /*
         *  auto-added by builder
         */
        $this->actAs(new Softdelete());

        /*
         *  auto-added by builder
         */
        $this->actAs(new Timestamp());

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

        if ( ! empty($this->course_date) && Zend_Date::isDate($this->course_date, $this->_date_format_datetime)) {
            $date = new Zend_Date($this->course_date, $this->_date_format_datetime);
            $this->course_date = null;
            $this->course_date = $date->toString($this->_datetime_format_db);
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
        if ( ! empty($event->data['course_date'])) {

            $data = $event->data;

            if (Zend_Date::isDate($event->data['course_date'], $this->_datetime_format_db)) {

                $date = new Zend_Date($event->data['course_date']);
                $data['course_date'] = $date->toString($this->_date_format_datetime);
            }

            $event->data = $data;

        }
    }

}