<?php

	abstract class BaseDgpKern extends Doctrine_Record {

// 	    public static $getBegleitungAssoc = array(
// 	        'Voluntary service' => 'Ehrenamtlicher Dienst',
//             'Hospice stationary' => 'Hospiz stationär',
//             'Palliative care' => 'Palliativpflege',
//             'Home' => 'Heim',
//             'family doctor' => 'Hausarzt',
//             'Palliativarzt' => 'Palliativarzt',
//             'Palliative Care Team' => 'Palliative Care Team',
//             'Outpatient care' => 'Ambulante Pflege',
//             'palliative consultation AHPB' => ' Pallivberatung AHPB',
//             'KH palliative ward' => 'KH Palliativstation',
//             'Hospital other ward' => 'Krankenhaus andere Station',
//             'MVZ' => 'MVZ',
//             'KH Palliativdienst' => 'KH Palliativdienst',
// 	    );
// 	    public static function getBegleitung( $name_number = null){
// 	        if (is_numeric($name_number)) {
// 	            return self::$getBegleitungAssoc [$name_number];
// 	        }
// 	    }
	    
		function setTableDefinition()
		{
			$this->setTableName('patient_dgp_kern');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wohnsituation', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('wohnsituations', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('ecog', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('begleitung', 'text', NULL, array('type' => 'text', 'length' => NULL));
			// ISPC-1994 04.09.2017 Ancuta
			$this->hasColumn('pverfuegung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('vollmacht', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('betreuungsurkunde', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('acp', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('vorlage', 'string', 255, array('type' => 'string', 'length' => 255));
			
			
			/*
			 * @cla ISPC-2198,  09.06.2018
			 * do NOT use this column !
			 * 
			 * this datum_der_erfassung1 wanted to be BA_datum and BL_datum from XSD v2.0 of KERN ...
			 * this was removed/missing after that version
			 * 
			 * use instead Timestamp listener's create/update date for the record if you want to know when it was moddified
			 * @deprecated
			 */
			$this->hasColumn('datum_der_erfassung1', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			
			$this->hasColumn('schmerzen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('ubelkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('erbrechen', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('luftnot', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('verstopfung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('swache', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('appetitmangel', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('mudigkeit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('dekubitus', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('hilfebedarf', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('depresiv', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('angst', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anspannung', 'integer', 1, array('type' => 'integer', 'length' => 1));
            //ISPC-2105: 08.06.2018 
			$this->hasColumn('unruhe', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//--
			$this->hasColumn('desorientier', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('versorgung', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('umfelds', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sonstige_probleme', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('kontaktes', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('who', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('steroide', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('chemotherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('strahlentherapie', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('aufwand_mit', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('problem_besonders', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('problem_ausreichend', 'text', NULL, array('type' => 'text', 'length' => NULL));
			//ISPC-1994: 31.08.2017
			$this->hasColumn('bedarf', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('massnahmen', 'text', NULL, array('type' => 'text', 'length' => NULL));
			//$this->hasColumn('medication_references', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('analgetika', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('who2', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('nicht_opioide', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('co_analgetika', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anxiolytika', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('laxantien', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sedativa', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('neuroleptika', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anti_eleptika', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('antiemetika', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('antibiotika', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('magenschutz', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//ISPC-2496 Ancuta 02.12.2019
			$this->hasColumn('secretioninhibiting_sub', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('benzodiazepines', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('antidepressants', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('antipsychotics', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anti_infectives', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('anticoagulants', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('other_meds', 'integer', 1, array('type' => 'integer', 'length' => 1));
			//-- 
 
			
			/*
			 * @cla ISPC-2198
			 * do NOT use this column !
			 * use patient_readmission_ID
			 * @deprecated
			 */
			$this->hasColumn('entlasung_date', 'string', 255, array('type' => 'string', 'length' => 255));
			
			$this->hasColumn('therapieende', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('sterbeort', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('sterbeort_dgp', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('zufriedenheit_mit', 'integer', 1, array('type' => 'integer', 'length' => 1));
			
			$this->hasColumn('form_type', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('unknown','adm', 'dis')));
			
			
			/*
			 * @cla ISPC-2198
			 * this column replaces the need for `entlasung_date`
			 * and fixes the issue of what wappens if you edit/delete the FALLS
			 */
			$this->hasColumn('patient_readmission_ID', 'integer', 11, array(
			    'type' => 'integer',
			    'length' => 11,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comments' => 'primary key from patient_readmission'
			));
			
			/*
			 * @cla ISPC-2198
			 * this links 2 rows that are the admission/discharge = 2 parts of the same single form
			 */
			$this->hasColumn('twin_ID', 'integer', 11, array(
			    'type' => 'integer',
			    'length' => 11,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			    'comments' => 'adm+dis linked in this table'
			));
			
		}

		function setUp()
		{
			$this->actAs(new Timestamp());

			/*
			 * @cla ISPC-2198
			 */
			$this->hasOne('PatientReadmission', array(
			    'local' => 'patient_readmission_ID',
			    'foreign' => 'id'
			));
			
			
			/*
			 * @cla ISPC-2198
			 * one form_type = 'adm' will have a twin form_type = 'dis', when you discharge the patient
			 * one 'dis' allways has a twin 'adm' .. or you failed
			 * 
			 * this `hack` would not be needed ,
			 * IF you would have clearely linked a patient admission to a discharge, via a key (that is not a key_date1 > key_date2).. 
			 */
			$this->hasOne('DgpKern as TwinDgpKern', array(
			    'local' => 'id',
			    'foreign' => 'twin_ID'
			));
			
			
		}

	}

?>