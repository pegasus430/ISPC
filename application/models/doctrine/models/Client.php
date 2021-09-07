<?php

	Doctrine_Manager::getInstance()->bindComponent('Client', 'MDAT');

	class Client extends BaseClient {

		public function getClientData()
		{
			$clist = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name,'" . Zend_Registry::get('salt') . "') as client_name,
					AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1,
					AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2,
					AES_DECRYPT(postcode,'" . Zend_Registry::get('salt') . "') as postcode,
					AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city,
					AES_DECRYPT(firstname,'" . Zend_Registry::get('salt') . "') as firstname,
					AES_DECRYPT(lastname,'" . Zend_Registry::get('salt') . "') as lastname,
					AES_DECRYPT(emailid,'" . Zend_Registry::get('salt') . "') as emailid,
					AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone,
					AES_DECRYPT(fax,'" . Zend_Registry::get('salt') . "') as fax,
					AES_DECRYPT(institutskennzeichen,'" . Zend_Registry::get('salt') . "') as institutskennzeichen,
					AES_DECRYPT(betriebsstattennummer,'" . Zend_Registry::get('salt') . "') as betriebsstattennummer")
				->from('Client')
				->where('isdelete=0');
			$clistexec = $clist->execute();
			if($clistexec)
			{
				$clientlist = $clistexec->toArray();
				return $clientlist;
			}
		}

		/**
		 * this returns an array[], even thus we are searching one client by primarykey
		 * 
		 * @param number $cid
		 * @return Ambigous <multitype:, Doctrine_Collection>
		 * ISPC-2272 (07.11.2018) - company_number,cost_center
		 * ISPC-2452 RP interface II - Ancuta 24.09.2019, rlp_hi_account_number,rlp_pv_account_number,rlp_terms_of_payment
		 * ISPC-2171 RP interface - Ancuta 17.11.2018, hospiz_hi_cont,hospiz_pv_cont,hospiz_const_center
		 * ISPC-2171 RP export interface -ISPC-2171 Ancuta 08.01.2020, rlp_document_header_txt
		 */
		public static function getClientDataByid($cid = 0)
		{
		    if (empty($cid)) {
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $cid = $logininfo->clientid;
		    }
		    
		    
			$clist = Doctrine_Query::create()
				->select("*,AES_DECRYPT(client_name, :key) as client_name,
					AES_DECRYPT(street1, :key) as street1,
					AES_DECRYPT(fileupoadpass, :key) as fileupoadpass,
					AES_DECRYPT(street2, :key) as street2,
					AES_DECRYPT(postcode, :key) as postcode,
					AES_DECRYPT(city, :key) as city,
					AES_DECRYPT(firstname, :key) as firstname,
					AES_DECRYPT(lastname, :key) as lastname,
					AES_DECRYPT(emailid, :key) as emailid,
					AES_DECRYPT(phone, :key) as phone,
					AES_DECRYPT(fax, :key) as fax,
					AES_DECRYPT(comment, :key) as comment,
					AES_DECRYPT(institutskennzeichen, :key) as institutskennzeichen,
					AES_DECRYPT(betriebsstattennummer, :key) as betriebsstattennummer,
					AES_DECRYPT(lbg_sapv_provider, :key) as lbg_sapv_provider,
					AES_DECRYPT(lbg_postcode, :key) as lbg_postcode,
					AES_DECRYPT(lbg_city, :key) as lbg_city,
					AES_DECRYPT(lbg_street, :key) as lbg_street,
					AES_DECRYPT(lbg_institutskennzeichen, :key) as lbg_institutskennzeichen,
					AES_DECRYPT(dgp_user, :key) as dgp_user,
					AES_DECRYPT(sepa_iban, :key) as sepa_iban,
					AES_DECRYPT(sepa_bic, :key) as sepa_bic,
					AES_DECRYPT(sepa_ci, :key) as sepa_ci,
					AES_DECRYPT(company_number, :key) as company_number,
					AES_DECRYPT(cost_center, :key) as cost_center,
					AES_DECRYPT(rlp_past_revenue, :key) as rlp_past_revenue,
					AES_DECRYPT(rlp_hi_account_number, :key) as rlp_hi_account_number,
					AES_DECRYPT(rlp_pv_account_number, :key) as rlp_pv_account_number,
					AES_DECRYPT(rlp_terms_of_payment, :key) as rlp_terms_of_payment,
					AES_DECRYPT(rlp_document_header_txt, :key) as rlp_document_header_txt,
					AES_DECRYPT(hospiz_hi_cont, :key) as hospiz_hi_cont,
					AES_DECRYPT(hospiz_pv_cont, :key) as hospiz_pv_cont,
					AES_DECRYPT(hospiz_const_center, :key) as hospiz_const_center,
					AES_DECRYPT(dgp_pass, :key) as dgp_pass")
				->from('Client')
				->where('id = :cid ' )
				->andWhere('isdelete = :isdelete')
				->fetchArray(array(
				    "key" =>  Zend_Registry::get('salt'),
				    "cid" =>  $cid,
				    "isdelete" => 0
				));
				
			if ($clist) {
				return $clist;
			}
		}

		
		public static function getImageRadios()
		{
			$Tr = new Zend_View_Helper_Translate();
			$smallimge = $Tr->translate('smallimge');
			$bigimage = $Tr->translate('bigimage');
			$doctorimage = $Tr->translate('doctorimage');

			$verordnetarray = array('1' => $smallimge, '2' => $bigimage, '3' => $doctorimage);
			return $verordnetarray;
		}

		public static function clientOnlyHealthInsurance($clientid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clist = Doctrine_Query::create()
				->select("id, health_insurance_client")
				->from('Client')
				->where('isdelete=0')
				->andwhere('id=?', $clientid);
			$clientlist = $clist->fetchArray();

			foreach($clientlist as $ky => $client_details)
			{
				$client_health = $client_details['health_insurance_client'];
			}

			return $client_health;
		}

		public static function get_all_clients_ids()
		{
			$clients_q = Doctrine_Query::create()
				->select('id')
				->from('Client')
				->where('isdelete = 0');
			$clients_res = $clients_q->fetchArray();

			if($clients_res)
			{
				foreach($clients_res as $k_res => $v_res)
				{
					$clients_arr[] = $v_res['id'];
				}

				return $clients_arr;
			}
			else
			{
				return false;
			}
		}

		public static function get_all_clients()
		{

			$sql = "*,AES_DECRYPT(client_name, :key) as client_name,
				AES_DECRYPT(street1, :key) as street1,
				AES_DECRYPT(fileupoadpass, :key) as fileupoadpass,
				AES_DECRYPT(street2, :key) as street2,
				AES_DECRYPT(postcode, :key) as postcode,
				AES_DECRYPT(city, :key) as city,
				AES_DECRYPT(firstname, :key) as firstname,
				AES_DECRYPT(lastname, :key) as lastname,
				AES_DECRYPT(emailid, :key) as emailid,
				AES_DECRYPT(phone, :key) as phone,
				AES_DECRYPT(fax, :key) as fax,
				AES_DECRYPT(institutskennzeichen, :key) as institutskennzeichen,
				AES_DECRYPT(betriebsstattennummer, :key) as betriebsstattennummer,
				AES_DECRYPT(lbg_sapv_provider, :key) as lbg_sapv_provider,
				AES_DECRYPT(lbg_postcode, :key) as lbg_postcode,
				AES_DECRYPT(lbg_city, :key) as lbg_city,
				AES_DECRYPT(lbg_street, :key) as lbg_street,
				AES_DECRYPT(dgp_user, :key) as dgp_user,
				AES_DECRYPT(dgp_pass, :key) as dgp_pass,
				AES_DECRYPT(comment, :key) as comment";

			$clients_res = Doctrine_Query::create()
				->select($sql)
				->from('Client indexBy id')
				->where('isdelete = 0')
				->orderBy("AES_DECRYPT(client_name, :key)  ASC")
				->fetchArray(array("key" =>  Zend_Registry::get('salt')));

			
			return $clients_res ? $clients_res : false;
		}
		
		
		/**
		 * ! Attention this overrides !
		 * 
		 * @claudiu 2017.12.07
		 * used first time in wlassessment
		 * 
		 * @param number $id =  clientid
		 * @param Doctrine_Core $hydrationMode HYDRATION CONSTANT
		 * @return mixed
		 * ISPC-2272 (07.11.2018) - company_number,cost_center
		 * ISPC-2327 (23.01.2019) - working_schedule
		 */
		public function findOneById( $id = 0, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY)
		{
		    if (empty($id)) {
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $id = $logininfo->clientid;
		    }
		    
		    return $this->getTable()->createQuery('c')
		    ->select("*,AES_DECRYPT(client_name, :key) as client_name,
					,AES_DECRYPT(street1, :key) as street1
					,AES_DECRYPT(street2, :key) as street2
					,AES_DECRYPT(postcode, :key) as postcode
					,AES_DECRYPT(city, :key) as city
					,AES_DECRYPT(firstname, :key) as firstname
					,AES_DECRYPT(lastname, :key) as lastname
					,AES_DECRYPT(emailid, :key) as emailid
					,AES_DECRYPT(phone, :key) as phone
					,AES_DECRYPT(institutskennzeichen, :key) as institutskennzeichen
					,AES_DECRYPT(betriebsstattennummer, :key) as betriebsstattennummer
					,AES_DECRYPT(fax, :key) as fax
					,AES_DECRYPT(lbg_sapv_provider, :key) as lbg_sapv_provider
					,AES_DECRYPT(lbg_postcode, :key) as lbg_postcode
					,AES_DECRYPT(lbg_city, :key) as lbg_city
					,AES_DECRYPT(lbg_street, :key) as lbg_street
					,AES_DECRYPT(lbg_institutskennzeichen, :key) as lbg_institutskennzeichen
					,AES_DECRYPT(fileupoadpass, :key) as fileupoadpass
					,AES_DECRYPT(comment, :key) as comment
					,AES_DECRYPT(dgp_user, :key) as dgp_user
					,AES_DECRYPT(dgp_pass, :key) as dgp_pass
					,AES_DECRYPT(sepa_iban, :key) as sepa_iban
					,AES_DECRYPT(sepa_bic, :key) as sepa_bic
					,AES_DECRYPT(sepa_ci, :key) as sepa_ci
					,AES_DECRYPT(company_number, :key) as company_number
					,AES_DECRYPT(cost_center, :key) as cost_center
					,AES_DECRYPT(working_schedule, :key) as working_schedule"
		        )
			->where("id = :clientid")
		    ->fetchOne(
		        array(
		            'key' => Zend_Registry::get('salt'),
		            'clientid' => $id,
		        ),
		        $hydrationMode
		    );
		    
		}
		
		/**
		 * this is findById
		 * 
		 * @param number $id = clientid
		 * @param unknown $hydrationMode
		 * @return Doctrine_Collection
		 * ISPC-2272 (07.11.2018) - company_number,cost_center
		 */
		public function fetchById( $id = 0, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY)
		{
		    if (empty($id)) {
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $id = $logininfo->clientid;
		    }
		    
		    if ( ! is_array($id)) {
		        $id = array($id);
		    }

		    $key = Zend_Registry::get('salt');
		    
		    return $this->getTable()->createQuery('c indexBy id')
		    ->select("*,AES_DECRYPT(client_name, '{$key}') as client_name,
					,AES_DECRYPT(street1, '{$key}') as street1
					,AES_DECRYPT(street2, '{$key}') as street2
					,AES_DECRYPT(postcode, '{$key}') as postcode
					,AES_DECRYPT(city, '{$key}') as city
					,AES_DECRYPT(firstname, '{$key}') as firstname
					,AES_DECRYPT(lastname, '{$key}') as lastname
					,AES_DECRYPT(emailid, '{$key}') as emailid
					,AES_DECRYPT(phone, '{$key}') as phone
					,AES_DECRYPT(institutskennzeichen, '{$key}') as institutskennzeichen
					,AES_DECRYPT(betriebsstattennummer, '{$key}') as betriebsstattennummer
					,AES_DECRYPT(fax, '{$key}') as fax
					,AES_DECRYPT(lbg_sapv_provider, '{$key}') as lbg_sapv_provider
					,AES_DECRYPT(lbg_postcode, '{$key}') as lbg_postcode
					,AES_DECRYPT(lbg_city, '{$key}') as lbg_city
					,AES_DECRYPT(lbg_street, '{$key}') as lbg_street
					,AES_DECRYPT(lbg_institutskennzeichen, '{$key}') as lbg_institutskennzeichen
					,AES_DECRYPT(fileupoadpass, '{$key}') as fileupoadpass
					,AES_DECRYPT(comment, '{$key}') as comment
					,AES_DECRYPT(dgp_user, '{$key}') as dgp_user
					,AES_DECRYPT(dgp_pass, '{$key}') as dgp_pass
					,AES_DECRYPT(sepa_iban, '{$key}') as sepa_iban
					,AES_DECRYPT(sepa_bic, '{$key}') as sepa_bic
					,AES_DECRYPT(sepa_ci, '{$key}') as sepa_ci
					,AES_DECRYPT(company_number, '{$key}') as company_number
					,AES_DECRYPT(cost_center, '{$key}') as cost_center"
		    )
		    ->whereIn("id", $id)
		    ->orderBy("AES_DECRYPT(client_name, '{$key}') ASC")
		    ->execute(null, $hydrationMode);
		
		}

    /**
     * Client-Configuration for ISPC clinic
     * This configuration can only be manipulated by super-admins.
     * For this configuration there are no extra configuration sites for the client-admins available.
     * The configuration site for super-admin: client/clinicconfig
     * All configuration-items stored under _clinicconfic' in table ClientConfig
	 * Maria:: Migration CISPC to ISPC 22.07.2020
     */
    public static function getClientconfig($clientid, $item)
    {
        if ($clientid == 0) {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
        }

        //read the client-configuration
        $config = ClientConfig::getConfig($clientid, 'clinicconfic');

        if(!$config){
            // if there is no configuration, use the default
            $config = self::get_clinic_default_config();
        }

        return $config[$item];
    }

    /**
     *  Get Inital-Values for the Clinic-Configuration
     *  This will used, if no client-config was made.
     * @param $clientid
     * @return array
     */
    public static function get_clinic_default_config(){

        $lmu = array(

            'patientquicknavbar_lists' => array(
                'station' => 'Station',
                'konsil' => 'Konsildienst',
                'ambulant' => 'Ambulanz',
                'sapv' => 'SAPV',
                'standby' => 'Warteliste',
            ),
            'convert' => 'utf8',
            'orgacodes' => array(
                'konsil' => 'Konsildienst',
                'station' => 'Station',
                'ambulant' => 'Ambulanz',
                'sapv' => 'SAPV',
            ),
            'filetags_cantransmit' => array(
                'Patientenverfügung' => 'PA_SCAN_PV',
                'Vorsorgevollmacht' => 'PA_SCAN_VV',
                'Betreuungsverfügung' => 'PA_SCAN_BETRV',
                'Gerichtliche Betreuung' => 'PA_SCAN_BETR',
                'Entlassbrief' => 'PA_A_ENTLM',
                'Rezept' => 'PA_REZEPT',
                //PA_SCAN_DIV    Sonstiges

            ),
            'useruploadfiletypes_for_filetags' => array(
                'pdf', 'jpg', 'bmp', 'png'

            ),
            'ipos_needed_when_status_changed' => 1,
            'team_teammeeting_addallusers' => true,
            'newmedics' => 1,
            'medizeiten' => array('8:00', '12:01', '16:00', '20:00', 'z.N.'),
            'pdfimgpathreplace' => array(
                " src='/clinic/" => " src='../public",
                ' src="/clinic/' => ' src="../public',
                " src='../../images" => " src='../public/images",
                ' src="../../images' => ' src="../public/images'
            ),
            'qi9_from_acp' => 1
        );
        $lmu['lmutm_profsmap'] = array(
            'medic' => 'Medizin',
            'care' => 'Pflege',
            'social' => 'Soziale Arbeit',
            'psy' => 'Psycho&shy;logisch',
            'spiritual' => 'Spirituell / Seelsorge',
            'breath' => 'Atem&shy;therapie / Entspannungs&shy;verfahren',
            'physio' => 'Physio&shy;therapie',
        );
        $lmu['hl7leistungen_profsmap'] = array(
            'Arzt' => 'ILPAARZT',
            'Pflege' => 'ILPAPFL',
            'Sozialarbeit' => 'ILPASOZIAL',
            'Atemtherapie' => 'ILPAATEM',
            'Psychologie' => 'ILPAPSYCH',
            'Krankengymnastik' => 'ILPAPHYS',
            'Apotheke' => 'KEINE',
            'Seelsorge' => 'ILPASEEL'
        );
        $lmu['new_versorger'] = true;
        $lmu['cf_timedoc_logger'] = false;
        $lmu['boxes_psychosocial_status'] =
                array(
                    'left' => array(
                        'Stammdatenerweitert_familienstand' => 'familienstand',
                        'Stammdatenerweitert_stastszugehorigkeit' => 'staatszugehoerigkeit',
                        'PatientReligions' => 'patient_religionszugehorigkeit',
                        'PatientAcp' => 'acp',
                        'PatientMaintainanceStage' => 'patient_maintenance_stage',
                        'PatientTherapieplanung' => 'patient_therapieplanung',
                    	'PatientTherapy' => 'patient_therapy', //ISPC-2774 Carmen 16.12.2020
                    	'PatientAids' => 'patient_aids', //ISPC-2381 Carmen 14.01.2021
                    ),
                    'right' => array(
                        'Stammdatenerweitert_wunsch' => 'wunsch',
                        'PatientMobility2' => 'patient_mobility2',
                        'PatientLives' => 'patient_lives',
                        'Stammdatenerweitert_ausscheidung' => 'ausscheidung',
                       // 'Stammdatenerweitert_kunstliche' => 'kuenstliche_ausgaenge',
                        'Stammdatenerweitert_ernahrung' => 'ernahrung',
                        'PatientGermination' => 'keimbesiedelung',
                    	'ContactPersonMaster' => 'contactperson',//ISPC-2772 Carmen 15.12.2020
                    ),
                );
        $lmu['boxesOpened_psychosocial_status'] ='ALL_CLOSED'; //'ALL_CLOSED' 'ALL_OPENED' 'ONLY_WITH_CONTENT'
        $lmu['config_clinic_report'] =  array(
            'legaltext' => true
        );
        $lmu['block_medication_clinic'] = array(
                    'actual' => array('nice_name' => 'Medikation'),
                    'isbedarfs' => array('nice_name' => 'Bedarfs Medikation'),
                    'isivmed' => array('nice_name' => 'I.v. / s.c. Medikation'),
                    'isschmerzpumpe' => array('nice_name' => 'Pumpe'),
                );
        $lmu['block_clinic_measure'] = array(
            'Bitte Auswählen' => array('name'=>'Bitte Auswählen'),
            'Sonographie' => array('name'=>'Sonographie',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('Abdomen', 'Niere', 'Blase', 'Pleura', 'Weichteile', 'Sonstiges')),
                )
            ),
            'Punktion' => array('name'=>'Punktion',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('diagnostisch', 'therapeutisch', 'sonstige')),
                    array('name'=>'Ort', 'entries'=>array('Pleura', 'Aszites', 'Blase')),
                )
            ),
            'Transfusion' => array('name'=>'Transfusion',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('Erythrozytenkonzentrat', 'Thrombozytenkonzentrat', 'Gerinnungsfaktoren')),
                ),
                'subtextfield'=>array('Anzahl')
            ),
            'Aufklärung für Externe Prozeduren/Intervention/Diagnostik' => array('name'=>'Aufklärung für Externe Prozeduren/Intervention/Diagnostik'),
            'Externe Prozeduren/Intervention' => array('name'=>'Externe Prozeduren/Intervention',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('Port-Anlage', 'PEG-Anlage (Ernährung)', 'PEG-Anlage (Ablauf)', 'Pleuradrainage', 'Aszitesdrainage', 'Operation/Intervention (s.u.)')),
                )
            ),
            'Externe Diagnostik/Intervention' => array('name'=>'Externe Diagnostik/Intervention',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('Besondere Bildgebung (Sonographie/Röntgen/CT/MRT/PET', 'Endoskopie (Gastroskopie, ERCP, Coloskopie)', 'Kardiale Diagnostik/Intervention', 'Lungenfunktionstestung')),
                )
            ),
            'Leichenschau' => array('name'=>'Leichenschau'),
            'Konsiliarische Vorstellung' =>array('name'=>'Konsiliarische Vorstellung',
                'subitems'=>array(
                    array('name'=>'Art', 'entries'=>array('Psychiatrie', 'Dermatologie', 'HNO', 'Onkologie', 'Strahlentherapie')),
                )
            ),
        );
        $lmu['extra_patient_course_clinic'] = array(
                    'FormBlockJobBackgroundClinic' => true,
                    'FormBlockTalkContent' => true,
                    'FormBlockPalliativAssessment' => true,
                    'FormBlockSOAP' => true,
                    'FormBlockShift' => true,
                    'FormBlockClinicMeasure' => true,
                );
        //override formids on devserver
        if (APPLICATION_ENV == 'development') {
            $lmu['basisassessment'] = 34;
            $lmu['pflegedoku'] = 45;
            $lmu['basisassessment'] = 34;
            $lmu['convert'] = 'latin1';
            $lmu['pdfimgpathreplace'] = array();
            //$lmu['pdf_async']=1;
            $lmu['newmedics'] = 1;
            $lmu['team_teammeeting_shortcut'] = 'WB';
            $lmu['team_teammeeting_addallusers'] = true;
            $lmu['testpatienticon'] = 37;
            $lmu['locationquickedit'] = 1;
            $lmu['leadingusers'] = 1;
            $lmu['standby_icons'] = true;
            $lmu['woundicon'] = 70;
            $lmu['new_stammdaten'] = false;
            $lmu['new_versorger'] = true;
            $lmu['anzahl_timeslots'] = 5;//medication
            $lmu['belegungsplanfreetext'] = true;
            $lmu['cf_timedoc_logger'] = true;
            $lmu['pflegeprozess_minutes'] = true;
        }
            return $lmu;

    }

    /**
     *  Get Inital-Values for the Care-Process-Clinic (IM-4)
     *  This will used, if no client-config was made.
     * @param $clientid
     * @return array
     */
    public static function get_clinic_careprocess_config()
    {
        $sections = array();
        $sections['Körperpflege'] = array(
            array(
                'col_thema' => array(
                    'Patient benötigt vollständige Übernahme/Unterstützung bei der Körperpflege weil',
                ),
                'col_probleme' => array(
                    'Bewusstseinsstörung',
                    'Spastik',
                    'Parese',
                    'Plegie',
                    'Immobilität',
                    'Abwehr',
                    'reduzierte AZ',
                    'fehlende Eigeninitiative',
                    'fehlende Kraft',
                    'Ablauf der Körperpflege ist nicht bekannt'
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Patient fühlt sich wohl',
                    'Patient ist gepflegt',
                    'Erlernt die Reihenfolge der Körperpflege',
                    'Führt die Körperpflege unter verbaler Anleitung und Anreichung von Material selbständig durch',
                    'Kann sich mit Hilfsmittel und verlängerter Waschzeit über 20 Minuten selbstständig waschen',
                    'Kennt spastikreduzierende, kräftesparende Bewegungsmuster und setzt diese ein',
                    'Selbstständigkeit bei der Körperpflege wird dem Krankheitsbild entsprechend kontinuierlich erhalten'
                ),
                'col_massnahmen' => array(
                    'basalstimulierende Ganzkörperwäsche bei Hemiplegie (KpBasH)',
                    'Körperganzwaschung im Bett (KpW3)',
                    'Unterstützung desor. Patienten',
                    'Sonstiges',
                    'Anleitung beim An- und Auskleiden',
                    'Hilfe beim An- und Auskleiden',
                    'Duschen',
                    'Körperteilwaschung (KpW2) (Waschbecken/Bett)',
                    'Rasur',
                    'Haarwäsche',
                    '_FREETEXT',
                )
            ),
            array(
                'col_thema' => array(
                    'Patient kann sich nur teilweise waschen weil',
                ),
                'col_probleme' => array(
                    'Spastik',
                    'Parese',
                    'Plegie',
                    'Immobilität',
                    'Bettruhe',
                    'Abwehr',
                    'reduzierte AZ',
                    'fehlende Eigeninitiative',
                    'fehlende Kraft',
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Patient fühlt sich wohl',
                    'Patient ist gepflegt',
                    'Erlernt die Reihenfolge der Körperpflege',
                    'Führt die Körperpflege unter verbaler Anleitung und Anreichung von Material selbständig durch',
                    'Kann sich mit Hilfsmittel und verlängerter Waschzeit über 20 Minuten selbstständig waschen',
                    'Kennt spastikreduzierende, kräftesparende Bewegungsmuster und setzt diese ein',
                    'Selbstständigkeit bei der Körperpflege wird dem Krankheitsbild entsprechend kontinuierlich erhalten'
                ),
                'col_massnahmen' => array(
                    'basalstimulierende Ganzkörperwäsche bei Hemiplegie (KpBasH)',
                    'Körperganzwaschung im Bett (KpW3)',
                    'Unterstützung desor. Patienten',
                    'Sonstiges',
                    'Anleitung beim An- und Auskleiden',
                    'Hilfe beim An- und Auskleiden',
                    'Duschen',
                    'Körperteilwaschung (KpW2) (Waschbecken/Bett)',
                    'Rasur',
                    'Haarwäsche',
                    '_FREETEXT',
                )
            ),
            array(
                'col_thema' => array(
                    'Mundhöhle / Zunge ist',
                ),
                'col_probleme' => array(
                    'nicht intakt',
                    'belegt',
                    'trocken',
                    'Gefahr von Soor- + Parotitis'
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Mundhöle / Zunge ist intakt, belagfrei, feucht'
                ),
                'col_massnahmen' => array(
                    'Anleitung zur KpM1',
                    'Anleitung zur ApoM1',
                    'Anleitung zur KpM2',
                    'Anleitung zur ApoM2 bei bewußtseinseingetrübten Patienten',
                    'Prothesenpflege',
                    'Mundpflegeöl',
                    '_FREETEXT',
                )
            ),
            array(
                'col_thema' => array(
                    'Trockene / gefährdete Haut mit',
                ),
                'col_probleme' => array(
                    'Intertrigo',
                    'Hämatome',
                    'Mykosen'
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Haut ist intakt und geschmeidig'
                ),
                'col_massnahmen' => array(
                    'Hautpflege mit Aromapflege Nr. ###',
                    '_FREETEXT'
                )
            ),
        );
        $sections['Ernährung'] = array(
            array(
                'col_thema' => array(
                    'Patient benötigt vollständige Übernahme/Unterstützung bei der Nahrungsaufnahme weil',
                ),
                'col_probleme' => array(
                    'Spastik',
                    'Parese',
                    'Plegie',
                    'Immobilität',
                    'Abwehr',
                    'reduzierte AZ',
                    'fehlende Eigeninitiative',
                    'fehlende Kraft',
                    'Ablauf der Nahrungsaufnahme ist nicht bekannt',
                    'Schluck- / Kaustörungen'
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Patient isst allein',
                ),
                'col_massnahmen' => array(
                    'zum Essen lagern/mobilisieren',
                    'Getränke anbieten / anreichen',
                    'Mahlzeit anreichen',
                    'ApoErn',
                    'Medikamenteneinnahme überwachen',
                    'Schluckkontrolle/Mundraumkontrolle',
                    'Einfuhrplan anlegen',
                    'Ernährungsprotokoll anlegen',
                    'Mahlzeiten zubereiten',
                    'Kontrolle bei desorientierten Patienten',
                    'Nahrungsergänzungskost',
                    'Patient informieren/anleiten',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Patient kann Nahrung / Getränke nicht alleine zubereiten weil',
                ),
                'col_probleme' => array(
                    'Spastik',
                    'Parese',
                    'Plegie',
                    'Immobilität',
                    'Abwehr',
                    'reduzierte AZ',
                    'fehlende Eigeninitiative',
                    'fehlende Kraft',
                    'Bettruhe'
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Patient isst allein',
                ),
                'col_massnahmen' => array(
                    'zum Essen lagern/mobilisieren',
                    'Getränke anbieten / anreichen',
                    'Mahlzeit anreichen',
                    'ApoErn',
                    'Medikamenteneinnahme überwachen',
                    'Schluckkontrolle/Mundraumkontrolle',
                    'Einfuhrplan anlegen',
                    'Ernährungsprotokoll anlegen',
                    'Mahlzeiten zubereiten',
                    'Kontrolle bei desorientierten Patienten',
                    'Nahrungsergänzungskost',
                    'Patient informieren/anleiten',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Patient hat',
                ),
                'col_probleme' => array(
                    'PEG-Ernährungssonde',
                    'Magensonde',
                    'Mykosen'
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Patient verträgt die Nahrung',
                    'Patient ist ausreichend ernährt',
                    'Vermeiden von Aspiration',
                ),
                'col_massnahmen' => array(
                    'Sondenlagekontrolle bei liegender MS',
                    'Sondenkostplan anlegen (nur auf dem Sondenkostplan dokumentieren',
                    'Aspirationsprophylaxe',
                    '_FREETEXT',
                )
            ),
        );

        $sections['Ausscheidung'] = array(
            array(
                'col_thema' => array(
                    'Patient benötigt vollständige Übernahme bei der Ausscheidung weil',
                ),
                'col_probleme' => array(
                    'Bewusstseinsstörung',
                    'Spastik',
                    'Parese',
                    'Plegie',
                    'Immobilität',
                    'Abwehr',
                    'reduzierte AZ',
                    'fehlende Eigeninitiative',
                    'fehlende Kraft',
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Haut ist intakt',
                    'Zystitisvermeidung',
                    'Patient bleibt mit Unterstützung urinkontinent',
                    'Patient bleibt mit Unterstützung stuhlkontinent',
                    'Patient ist selbstständig',
                ),
                'col_massnahmen' => array(
                    'Versorgung von Stuhlinkontinenz',
                    'Versorgung von Harninkontinenz',
                    'Hautschutz mit: ###',
                    'Blasentraining b. lieg. SPFK',
                    'zur Toilette begleiten',
                    'Steckbecken',
                    'Toilettenstuhl',
                    'Urinflasche',
                    'Katheterpflege',
                    'Katheterbeutel leeren',
                    'Intimpflege',
                    'Sonstiges',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Patient braucht Unterstützung bei der Blasen-/Darmentleerung weil',
                ),
                'col_probleme' => array(
                    'Harninkontinenz',
                    'Bettruhe',
                    'Stuhlinkontinenz',
                    'Zystitisgefahr',
                    'DK',
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Haut ist intakt',
                    'Zystitisvermeidung',
                    'Patient bleibt mit Unterstützung urinkontinent',
                    'Patient bleibt mit Unterstützung stuhlkontinent',
                    'Patient ist selbstständig',
                ),
                'col_massnahmen' => array(
                    'Versorgung von Stuhlinkontinenz',
                    'Versorgung von Harninkontinenz',
                    'Hautschutz mit: ###',
                    'Blasentraining b. lieg. SPFK',
                    'zur Toilette begleiten',
                    'Steckbecken',
                    'Toilettenstuhl',
                    'Urinflasche',
                    'Katheterpflege',
                    'Katheterbeutel leeren',
                    'Intimpflege',
                    'Sonstiges',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Patient hat',
                ),
                'col_probleme' => array(
                    'Enterales Stoma',
                    'Urostoma',
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Patient fühlt sich sauber und sicher',
                    'Haut um Stoma ist intakt',
                ),
                'col_massnahmen' => array(
                    'Stomabeutel leeren',
                    'Plattenwechsel (alle 2-3 Tage)',
                    'Stomabeutel wechseln (täglich)',
                    'Stomatherapeut informiert',
                    '_FREETEXT',
                )
            ),
            array(
                'col_thema' => array(
                    'Häufiges Erbrechen',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Patient fühlt sich gepflegt'
                ),
                'col_massnahmen' => array(
                    'Pflegerische Versorgung bei Erbrechen',
                    '_FREETEXT',
                )
            ),
        );

        $sections['Bewegung'] = array(
            array(
                'col_thema' => array(
                    'Patient benötigt vollständige Unterstützung bei der Bewegung weil',
                ),
                'col_probleme' => array(
                    'Bewusstseinsstörung',
                    'Spastik',
                    'Parese',
                    'Plegie',
                    'Immobilität',
                    'Abwehr',
                    'reduzierte AZ',
                    'fehlende Eigeninitiative',
                    'fehlende Kraft',
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Beweglichkeit wird dem Krankheitsbild entsprechend kontinuierlich erhalten',
                    'Reduzierung des Sturzrisikos',
                    'Verringerung der Sturzhäufigkeit'
                ),
                'col_massnahmen' => array(
                    'Bewegungsplan',
                    'Anleiten zum selbstständigen Bewegen',
                    'Anleiten zur Mobilisation mit UAG I',
                    'Anleiten zur Mobilisation mit Rollator',
                    'Anleiten zur Mobilisation mit Gehwagen',
                    'Anleiten zur Mobilisation mit Stuhl/Rollstuhl',
                    'Anleitung für Motorschiene',
                    'Anleitung zur Sturzprophylaxe',
                    'Anleitung zum Anlegen des Stützkorsetts',
                    '_FREETEXT',
                ),
            ),

            array(
                'col_thema' => array(
                    'Patient ist in der Bewegung eingeschränkt weil',
                ),
                'col_probleme' => array(
                    'Spastik',
                    'Parese',
                    'Plegie',
                    'Immobilität',
                    'Abwehr',
                    'reduzierte AZ',
                    'fehlende Eigeninitiative',
                    'fehlende Kraft',
                    'Bettruhe',
                    'Sturzgefahr'
                ),
                'col_ressourcen' => array(
                    'isst selbständig',
                    'kann Schmerzen artikulieren',
                    'kann Gesicht und Arme selbst waschen',
                    'bemerkt Veränderungen und kann diese mitteilen',
                ),
                'col_ziele' => array(
                    'Beweglichkeit wird dem Krankheitsbild entsprechend kontinuierlich erhalten',
                    'Reduzierung des Sturzrisikos',
                    'Verringerung der Sturzhäufigkeit'
                ),
                'col_massnahmen' => array(
                    'Bewegungsplan',
                    'Anleiten zum selbstständigen Bewegen',
                    'Anleiten zur Mobilisation mit UAG I',
                    'Anleiten zur Mobilisation mit Rollator',
                    'Anleiten zur Mobilisation mit Gehwagen',
                    'Anleiten zur Mobilisation mit Stuhl/Rollstuhl',
                    'Anleitung für Motorschiene',
                    'Anleitung zur Sturzprophylaxe',
                    'Anleitung zum Anlegen des Stützkorsetts',
                    '_FREETEXT',
                ),
            ),

            array(
                'col_thema' => array(
                    'Thrombosegefahr',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'Venöser Rückstrom ist Gewährleistet',
                ),
                'col_massnahmen' => array(
                    'ThPr-Standard',
                    'Kompressionsstrümpfe / MTS anziehen',
                    'Anleitung',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Dekubitusgefahr',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'Haut ist intakt',
                ),
                'col_massnahmen' => array(
                    'DekuPr-Standard',
                    'Hautinspektion',
                    'Anleitung',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Kontrakturgefahr',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'Patient hat keine Kontrakturen',
                    'Beweglichkeit bleibt erhalten',
                ),
                'col_massnahmen' => array(
                    'Durchbewegung aller Gelenke',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Pneumoniegefahr',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'Lunge ist gut belüftet',
                ),
                'col_massnahmen' => array(
                    'Oberkörperhochlagerung',
                    'Absaugen',
                    'Anleitung',
                    'Triflow',
                    'Atemübungen',
                    'PneuPr-Standard',
                    '_FREETEXT',
                ),
            ),
        );

        $sections['Spezielle Pflegeprobleme, Ziele und Maßnahmen'] = array(
            array(
                'col_thema' => array(
                    'Wundbehandlung',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'Komplikation wird rechtzeitig erkannt'
                ),
                'col_massnahmen' => array(
                    'Wunddokumentation angelegt',
                    'Verbandskontrolle ZVK',
                    'Verbandskontrolle Port',
                    'Verbandskontrolle per. Venenverweilkanüle',
                    'Verbandskontrolle PEG',
                    'Verbandskontrolle SPFK',
                    'Verbandskontrolle Wunde',
                    'Verbandskontrolle Sonstige',
                    'Verbandswechsel ZVK',
                    'Verbandswechsel Port',
                    'Verbandswechsel per. Venenverweilkanüle',
                    'Verbandswechsel PEG',
                    'Verbandswechsel SPFK',
                    'Verbandswechsel Wunde',
                    'Verbandswechsel Sonstige',
                    'Ableitungssystem: Sog kontrollieren',
                    'Ableitungssystem: Sekret kontrollieren',
                    'Ableitungssystem: Sekret bilanzieren',
                    'Ableitungssystem: Eisbehandlung',
                    'Ableitungssystem: Assistenz bei Verbänden, Behandlungen, Untersuchungen',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Hautbehandlung',
                ),
                'col_probleme' => array(
                    'Aseptische Wunde, Wo? ###',
                    'Septische Wunde, Wo? ###',
                ),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'Komplikation wird rechtzeitig erkannt'
                ),
                'col_massnahmen' => array(
                    'Wunddokumentation angelegt',
                    'Verbandskontrolle ZVK',
                    'Verbandskontrolle Port',
                    'Verbandskontrolle per. Venenverweilkanüle',
                    'Verbandskontrolle PEG',
                    'Verbandskontrolle SPFK',
                    'Verbandskontrolle Wunde',
                    'Verbandskontrolle Sonstige',
                    'Verbandswechsel ZVK',
                    'Verbandswechsel Port',
                    'Verbandswechsel per. Venenverweilkanüle',
                    'Verbandswechsel PEG',
                    'Verbandswechsel SPFK',
                    'Verbandswechsel Wunde',
                    'Verbandswechsel Sonstige',
                    'Ableitungssystem: Sog kontrollieren',
                    'Ableitungssystem: Sekret kontrollieren',
                    'Ableitungssystem: Sekret bilanzieren',
                    'Ableitungssystem: Eisbehandlung',
                    'Ableitungssystem: Assistenz bei Verbänden, Behandlungen, Untersuchungen',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Patient hat',
                ),
                'col_probleme' => array(
                    'ZVK',
                    'Port',
                    'per. Venenverweilkanüle',
                    'PEG',
                    'SPFK',
                    'Easyflow',
                    'Drainage',
                    'Redon',
                    'Vakuumversieglung'
                ),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'Komplikation wird rechtzeitig erkannt'
                ),
                'col_massnahmen' => array(
                    'Wunddokumentation angelegt',
                    'Verbandskontrolle ZVK',
                    'Verbandskontrolle Port',
                    'Verbandskontrolle per. Venenverweilkanüle',
                    'Verbandskontrolle PEG',
                    'Verbandskontrolle SPFK',
                    'Verbandskontrolle Wunde',
                    'Verbandskontrolle Sonstige',
                    'Verbandswechsel ZVK',
                    'Verbandswechsel Port',
                    'Verbandswechsel per. Venenverweilkanüle',
                    'Verbandswechsel PEG',
                    'Verbandswechsel SPFK',
                    'Verbandswechsel Wunde',
                    'Verbandswechsel Sonstige',
                    'Ableitungssystem: Sog kontrollieren',
                    'Ableitungssystem: Sekret kontrollieren',
                    'Ableitungssystem: Sekret bilanzieren',
                    'Ableitungssystem: Eisbehandlung',
                    'Ableitungssystem: Assistenz bei Verbänden, Behandlungen, Untersuchungen',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Liegendes Tracheostoma',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'Selbstständige Versorgung der Trachealkanüle',
                    'Tracheostoma funktionsfähig',
                ),
                'col_massnahmen' => array(
                    'Tracheostoma versorgen',
                    'Cuffdruckmessung',
                    'Kanüle wechseln',
                    'Absaugen',
                    'Anleitung',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Inhalation',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'Patient inhaliert selbstständig',
                    'atmet tief und ruhig',
                    'hustet ab',
                ),
                'col_massnahmen' => array(
                    'Patient anleiten',
                    'Hilfestellung bei Inhalation',
                    'Hilfestellung bei Dosieraerosol',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Chemotherapie',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'Mundschleimhaut intakt',
                    'Hautdefekte werden rechtzeitig erkannt',
                    'Optimierung des Hautbildes',
                ),
                'col_massnahmen' => array(
                    'Patient informieren, Anleiten',
                    'OnkpM-Standard',
                    'Hautpflege/Aromapflege Nr. ###',
                    'Bestrh Standard',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Strahlentherapie',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'Mundschleimhaut intakt',
                    'Hautdefekte werden rechtzeitig erkannt',
                    'Optimierung des Hautbildes',
                ),
                'col_massnahmen' => array(
                    'Patient informieren, Anleiten',
                    'OnkpM-Standard',
                    'Hautpflege/Aromapflege Nr. ###',
                    'Bestrh Standard',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Patient befindet sich im Sterbeprozess',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(),
                'col_ziele' => array(
                    'würdevolle Sterbebegleitung',
                ),
                'col_massnahmen' => array(
                    'würdevolle Sterbebegleitung Patient',
                    'würdevolle Sterbebegleitung Angehörige',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Allgemeine Themen',
                ),
                'col_probleme' => array(),
                'col_ressourcen' => array(),
                'col_ziele' => array(),
                'col_massnahmen' => array(
                    'Nächtliche Patientenüberwachung',
                    'Prä Op-Versorgung',
                    'Übergabe am Krankenbett',
                    '_FREETEXT',
                ),
            ),
            array(
                'col_thema' => array(
                    'Individuelle Probleme',
                ),
                'col_probleme' => array(
                    'Problem 1 ###',
                    'Problem 2 ###',
                ),
                'col_ressourcen' => array(),
                'col_ziele' => array(),
                'col_massnahmen' => array(
                    '_FREETEXT',
                ),
            ),
        );

        return $sections;
    }

    /**
     *  Get Inital-Values for the Treatment-Plan-Clinic (IM-26)
     *  and Weekly Team-Meeting (IM-1)
     *  This will used, if no client-config was made.
     * @return array
     */
    public static function get_clinic_plan_goal_config()
    {
        $sections = array();
        $sections['Palliative Sedierung'] = array(
                'category' => 'medic',
                'plan' => array("Opioide")
            );
        $sections['Atemnot verringern'] = array(
            'category' => 'medic',
            'plan' => array("Medikamentöse Einstellung","O2")
        );
        $sections['Schmerzfreiheit'] = array(
            'category' => 'medic',
            'plan' => array("Opioide","Entspannung","Massage","TENS")
        );
        $sections['Wund-Management optimieren'] = array(
            'category' => 'care',
            'plan' => array("Verbandswechsel")
        );
        $sections['Bewegungstheapie'] = array(
            'category' => 'breath',
            'plan' => array("Physiopaket 1","Physiopaket 2","Kinesio-Tping")
        );
        $sections['Vorsorge planen'] = array(
            'category' => 'social',
            'plan' => array("Patientenverfügung besprechen","Betreuungsverfügung")
        );

        return $sections;
    }

    /**
     * ISPC-2459 Ancuta 05.08.2020
     * @param unknown $client
     */
    public static function  get_movement_number($client,$ipid,$date,$patient_movement_numbers = array()){
  
        if(empty($client) || empty($ipid) || empty($date) ){
            return;
        }
        
        $client_info_arr  = Client::getClientDataByid($client);
        if(empty($client_info_arr)){
            return;
        }
        $client_movement_number = 0 ;
        $client_movement_number = $client_info_arr['0']['movement_start_number'];
        
        // check if we have a movement number generated for curent day 
        $patient_nrs = PatientMovementnumberTable::_find_by_patient($ipid);
        $new_movment_nr = 0 ;
        $existing_pateint_nrs  = array();
        if(isset($patient_movement_numbers[$date])){
            
            $new_movment_nr = $patient_movement_numbers[$date];
            return $new_movment_nr;
            
            
        } else {
            // generate and save! 
            $existing_pateint_nrs = array_values($patient_movement_numbers);
                        
            
            // first - we check if the number is already generated for the day
            $patient_generated_nrs = array();
            foreach($patient_nrs as $k => $gen_nrs){
                $patient_generated_nrs[] =$gen_nrs['movement_number'];
                if($gen_nrs['activation_date'] == $date){
                    $new_movment_nr = $gen_nrs['movement_number'];
                }
            }
            
            if( ! empty($new_movment_nr)){
                return $new_movment_nr;
            }
            //daca nu are generat - verificam daca are deja existent- si luam de la cel mai mare 
            // verificam daca cel al clientului este cel mai mare si incremetam si generam 
            //daca al clientului e mai mic de ce exista, incrementam pe ultimul
            
            if(!empty($patient_generated_nrs)){
                asort($patient_generated_nrs);
                $last_existing = end($patient_generated_nrs);
                
                if($last_existing > $client_movement_number){
                    $new_movment_nr = $last_existing +  1;
                } else {
                    $new_movment_nr = $client_movement_number +  1;
                }
                
            } else {
                $new_movment_nr = $client_movement_number +  1;
            }
            // insert new 
            $ins = new PatientMovementnumber();
            $ins->ipid = $ipid;
            $ins->movement_number = $new_movment_nr;
            $ins->activation_date = $date;
            $ins->save();
            
            
            return $new_movment_nr;
        }
        
    }

    
	}

?>