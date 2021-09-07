<?php

/**
 * GroupDefaultPermissionsHistory
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ISPC-2302
 * @subpackage Application (2019-10-25)
 * @author     Lore <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class GroupDefaultPermissionsHistory extends BaseGroupDefaultPermissionsHistory
{            
    /**
     * translations are grouped into an array
     * @var unknown
     */
    const LANGUAGE_ARRAY    = 'groupdefaultpermissionshistory_lang';
            
    /**
     * define the FORMID and FORMNAME, if you want to piggyback some triggers
     * @var unknown
     */
    const TRIGGER_FORMID    = null;
    const TRIGGER_FORMNAME  = 'frm_groupdefaultpermissionshistory';
            
    /**
     * insert into patient_files will use this
     */
    const PATIENT_FILE_TABNAME  = 'GroupDefaultPermissionsHistory';
    const PATIENT_FILE_TITLE    = 'GroupDefaultPermissionsHistory PDF'; //this will be translated
            
    /**
     * insert into patient_course will use this
     */
    const PATIENT_COURSE_TITLE      = 'GroupDefaultPermissionsHistory PDF was created';
    const PATIENT_COURSE_TABNAME    = 'groupdefaultpermissionshistory';
    const PATIENT_COURSE_TYPE       = ''; // add letter

    
    
    

}