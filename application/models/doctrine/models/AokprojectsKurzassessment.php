<?php

/**
 * Class AokprojectsKurzassessment
 * ISPC-2625, AOK Kurzassessment, 04.07.2020, elena
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */ 
class AokprojectsKurzassessment extends BaseAokprojectsKurzassessment
{

    /**
     * translations are grouped into an array
     * @var unknown
     */
    const LANGUAGE_ARRAY    = 'mamboassessment_lang';

    /**
     * define the FORMID and FORMNAME, if you want to piggyback some triggers
     * @var unknown
     */
    const TRIGGER_FORMID    = null;
    const TRIGGER_FORMNAME  = 'frm_mamboassessment';

    /**
     * insert into patient_files will use this
     */
    const PATIENT_FILE_TABNAME  = 'AokprojectsKurzassessment';
    const PATIENT_FILE_TITLE    = 'AOK Kurzassessment'; //this will be translated

    /**
     * insert into patient_course will use this
     */
    const PATIENT_COURSE_TITLE      = 'Kurzassessment was created';
    const PATIENT_COURSE_TABNAME    = 'kurzassessment';
    const PATIENT_COURSE_TYPE       = ''; // add letter

    /**
     * one ring to rule them all
     * @var unknown
     */
    const PATIENT_COURSE_DONE_NAME  = 'kurzassessment';

    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }



}