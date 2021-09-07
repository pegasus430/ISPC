<?php 

/** 
 * PatientRass
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ISPC
 * @subpackage Application (2020-05-25)
 * @author     Ancuta <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class PatientRass extends BasePatientRass
{            
    /**
     * translations are grouped into an array
     * @var unknown
     */
    const LANGUAGE_ARRAY    = 'patientrass_lang';
            
    /**
     * define the FORMID and FORMNAME, if you want to piggyback some triggers
     * @var unknown
     */
    const TRIGGER_FORMID    = null;
    const TRIGGER_FORMNAME  = 'frm_patientrass';
            
    /**
     * insert into patient_files will use this
     */
    const PATIENT_FILE_TABNAME  = 'PatientRass';
    const PATIENT_FILE_TITLE    = 'PatientRass PDF'; //this will be translated
            
    /**
     * insert into patient_course will use this
     */
    const PATIENT_COURSE_TITLE      = 'PatientRass PDF was created';
    const PATIENT_COURSE_TABNAME    = 'patientrass';
    const PATIENT_COURSE_TYPE       = 'F'; // add letter

    // ISPC-2564 Andrei 26.05.2020
    
    public static function getPatientRassRadios()
    {
        return array(
            '+4' => 'Streitlustig',
            '+3' => 'Sehr agitiert',
            '+2' => 'Agitiert',
            '+1' => 'Unruhig',
            '0' => 'Aufmerksam und ruhig',
            '-1' => 'Schläfrig',
            '-2' => 'Leichte Sedierung',
            '-3' => 'Mäßige Sedierung',
            '-4' => 'Tiefe Sedierung',
            '-5' => 'Nicht erweckbar'
        );
        
    }
    
    public static function getPatientRassRadiosExplanations()
    {
    	return array(
    			'+4' => 'Offenkundig aggressives und gewalttätiges Verhalten, unmittelbare Gefahr für das Personal',
    			'+3' => 'Zieht oder entfernt Schläuche oder Katheter, aggressiv',
    			'+2' => 'Häufige ungezielte Bewegung, atmet gegen das Beatmungsgerät',
    			'+1' => 'Ängstlich aber Bewegungen nicht aggressiv oder lebhaft',
    			'0' => '',
    			'-1' => 'Nicht ganz aufmerksam, aber erwacht (Augen öffnen/Blickkontakt) anhaltend bei Ansprache (> 10 Sekunden)',
    			'-2' => 'Erwacht kurz mit Blickkontakt bei Ansprache (< 10 Sekunden)',
    			'-3' => 'Bewegung oder Augenöffnung bei Ansprache (aber ohne Blickkontakt)',
    			'-4' => 'Keine Reaktion auf Ansprache, aber Bewegung oder Augenöffnung durch körperlichen Reiz',
    			'-5' => 'Keine Reaktion auf Ansprache oder körperlichen Reiz'
    	);
    
    }

}