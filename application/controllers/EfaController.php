<?php

//ISPC-2831 Dragos 15.03.2021 full controller
class EfaController extends Pms_Controller_Action
{
    public function init()
    {
        
        
        $this->logininfo = new Zend_Session_Namespace('Login_Info');
        
        $this
        ->setActionsWithPatientinfoAndTabmenus([
            /*
             * actions that have the patient header
             */
            ($this->logininfo->isEfaClient == '1' && $this->logininfo->isEfaUser == '1') ? '' : 'patientproblems',  // ISPC-2864  Ancuta 20.04.2021
            
        ])
        ->setActionsWithJsFile([
            /*
             * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
             */
            'diagnosis',
            'interventions',
            'reactions',
            'vaccinations',
            'history',
            'findings',
            'patientproblems',
            'aidsuppliers',//ISPC-2892 Ancuta 27.04.2021
            'specialcare',//ISPC-2891 Ancuta 27.04.2021
            'users2patient',//ISPC-2894 Ancuta 19.05.2021
        ])
        ->setActionsWithLayoutNew([
            /*
             * actions that will use layout_new.phtml
             * Actions With Patientinfo And Tabmenus also use layout_new.phtml
             */
            ($this->logininfo->isEfaClient == '1' && $this->logininfo->isEfaUser == '1') ? '' : 'patientproblems',  // ISPC-2864  Ancuta 20.04.2021
        ])
        ;
        
        
 
    }
    public function diagnosisAction()
    {
        $this->view->headLink()->appendStylesheet(RES_FILE_PATH . '/css/data_tables/datatables.min.css' );
        $this->view->headScript()->appendFile(RES_FILE_PATH.'/javascript/data_tables/jquery.dataTables.js');

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $groupid = $logininfo->groupid;

        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $this->view->encid = $_REQUEST['id'];
        $ipid = Pms_CommonData::getIpid($decid);

        $blockname = 'icd'; // similar to patientform/contactform

        $pform_icd = new Application_Form_PatientDiagnosis(array(
            '_block_name'           => $blockname,
            '_clientModules'        => array(),
        ));

        if (is_null($icd_options) &&  ! empty($ipid)) {

            $entity = new PatientDiagnosis();
            $saved = $entity->getAllDiagnosisClinical($ipid,$clientid); //APPLY SORITNG!!!!

            $icd_options =  ! empty($saved[$ipid]) ? $saved[$ipid] : array();

        }

        $ops_data = $pform_icd->create_diagnosis_clinical($blockname, $icd_options, $ipid,$clientid, true);
        $__formHTML = $ops_data;

        $this->view->{$blockname} = [
            "__formHTML" => $__formHTML,
        ];
    }

    public function interventionsAction() {
        $this->view->headLink()->appendStylesheet(RES_FILE_PATH . '/css/data_tables/datatables.min.css' );
        $this->view->headScript()->appendFile(RES_FILE_PATH.'/javascript/data_tables/jquery.dataTables.js');

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $groupid = $logininfo->groupid;

        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $this->view->encid = $_REQUEST['id'];
        $ipid = Pms_CommonData::getIpid($decid);
        $epid = Pms_CommonData::getEpid($ipid);

        $blockname = 'interventions';

        $options = null;
        $af_i= new Application_Form_Interventions();
        $form_data = $af_i->create_form_interventions($options, $ipid, true);
        $__formHTML = $form_data['html'];//we need html only;

        $this->view->{$blockname} = [
            "__formHTML" => $__formHTML,
        ];
    }

    public function reactionsAction() {
        $this->view->headLink()->appendStylesheet(RES_FILE_PATH . '/css/data_tables/datatables.min.css' );
        $this->view->headScript()->appendFile(RES_FILE_PATH.'/javascript/data_tables/jquery.dataTables.js');

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $groupid = $logininfo->groupid;

        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $this->view->encid = $_REQUEST['id'];
        $ipid = Pms_CommonData::getIpid($decid);
        $epid = Pms_CommonData::getEpid($ipid);

        $blockname = 'reactions';

        $af_r= new Application_Form_Reactions();
        $__formHTML = $af_r->create_form_reactions(null, $ipid, true, true);

        $this->view->{$blockname} = [
            "__formHTML" => $__formHTML,
        ];
    }

    public function vaccinationsAction() {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $groupid = $logininfo->groupid;

        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $this->view->encid = $_REQUEST['id'];
        $ipid = Pms_CommonData::getIpid($decid);
        $epid = Pms_CommonData::getEpid($ipid);

        $patientmaster = new PatientMaster();
        $patientinfo = $patientmaster->getMasterData($decid, 1);
        $patientMasterData= $patientmaster->get_patientMasterData();

        $blockname = 'block_patient_childrendiseases';

        $af_pm =  new Application_Form_PatientChildrenDiseases(array(
            '_patientMasterData'    => $patientMasterData
        ));

        //$form = $af_pm->create_form_block_patient_childrendiseases(null, 'PatientChildrenDiseases');

        //$__formHTML = $form->render();
        $columns = $af_pm->getVersorgerExtract();
        $this->view->{$blockname} = [
            "columns" => $columns,
            "patientMasterData" => $patientMasterData,
        ];
    }

    //ISPC-2832
    public function historyAction() {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $groupid = $logininfo->groupid;

        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $this->view->encid = $_REQUEST['id'];
        $ipid = Pms_CommonData::getIpid($decid);
        $epid = Pms_CommonData::getEpid($ipid);

        $patientmaster = new PatientMaster();
        $patientinfo = $patientmaster->getMasterData($decid, 1);
        $patientMasterData= $patientmaster->get_patientMasterData();

        $colors = [
            'admission_ambulant' => "#00cc66",
            'discharge_ambulant' => "#33cccc",
            'admission_clinical' => "#339966",
            'discharge_clinical' => "#0066ff",
            'diagnosis' => '#ff9933',
            'basis-assessment' => '#6600ff',
            'chart-custom-events' => '#ff3399',
            'treatment-plan' => '#cc0000',
            'calendar-events' => '#669999',
            'patient-files' => '#663300'

        ];

        $eventTypes = [
            'admission_ambulant' => [
                'name' => $this->translate('Admission (Ambulant)'),
            ],
            'discharge_ambulant' => [
                'name' => $this->translate('Discharge (Ambulant)'),
            ],
            'admission_clinical' => [
                'name' => $this->translate('Admission (Clinical)'),
            ],
            'discharge_clinical' => [
                'name' => $this->translate('Discharge (Clinical)'),
            ],
            'diagnosis' => [
                'name' => $this->translate('Diagnosis'),
            ],
            'basis-assessment' => [
                'name' => $this->translate('Basis-assessment'),
            ],
            'chart-custom-events' => [
                'name' => $this->translate('Ereignisse events'),
            ],
            'treatment-plan' => [
                'name' => $this->translate('Treatment plan forms'),
            ],
            'calendar-events' => [
                'name' => $this->translate('Calendar events'),
            ],
            'patient-files' => [
                'name' => $this->translate('Patient files'),
            ],
        ];
        $visibleEventTypes = $_REQUEST['eventTypes'];
        foreach ($eventTypes as $key => &$eventType) {
            $eventType['color'] = $colors[$key];
            //show by default
            if (isset($visibleEventTypes[$key])) {
                $eventType['visible'] = intval($visibleEventTypes[$key]);
            } else {
                //set the defaults
                $visibleEventTypes[$key] = 1;
                $eventType['visible'] = 1;
            }

        }

        $this->view->eventTypes = $eventTypes;


        if(!empty($_REQUEST['start_date'])) {
            $start_date = strtotime($_REQUEST['start_date']);
        }
        if(!empty($_REQUEST['end_date'])) {
            $end_date = strtotime($_REQUEST['end_date']);
        }

        if (empty($end_date)) {
            $end_date = strtotime('now');
        }

        if (empty($start_date)) {
            $start_date = strtotime(date('Y-m-d',$end_date).' - 3 months');
        }

        $this->view->start_date = $start_date;
        $this->view->end_date = $end_date;

        $p_share_obj = new PatientsShare();
        $p_share_info= $p_share_obj->get_connection_by_ipid($ipid);
        $allIpids [] = $ipid;
        $client_ids[$ipid] = $clientid;
        if (!empty($p_share_info)) {
            foreach ($p_share_info as $link) {
                if ($link['source'] == $ipid && $link['target'] != $ipid) {
                    $allIpids [] = $link['target'];
                    $client_ids[$link['target']] = $link['target_client'];
                } elseif ($link['target'] == $ipid && $link['source'] != $ipid) {
                    $allIpids [] = $link['source'];
                    $client_ids[$link['source']] = $link['source_client'];
                }
            }
        }
        $client_details = Client::get_all_clients();

        $patient_falls = PatientReadmission::findFallsOfIpid($allIpids, true);

        $selected_fall = 0; // this is fetched from post or get

        $last_fall_start_id = 0;

        $patient_falls_array = array();
        $this->view->patient_falls_array = array();
        $this->view->patient_falls_selectbox = array();//only to populate a formSelect


        //falls ambulant
        if (!empty($patient_falls)) {
            foreach ($patient_falls as $fall_ipid => $falls) {
                if ($visibleEventTypes['admission_ambulant'] == 1 || $visibleEventTypes['discharge_ambulant'] == 1) {
                    foreach ($falls as $fall) {

                        if(! empty($fall['admission']['date']) && $fall['admission']['date'] != '0000-00-00' && $visibleEventTypes['admission_ambulant'] == 1) {
                            $chartEvents [] = [
                                'x' => strtotime($fall['admission']['date']),
                                'color' => $colors['admission_ambulant'],
                                'event_type' => 'admission_ambulant',
                                'label' => '<b>'.date('d.m.Y H:i',strtotime($fall['admission']['date'])).'</b><br/>Aufnahme - '.$client_details[$client_ids[$fall_ipid]]['team_name'],
                            ];
                        }
                        if(! empty($fall['discharge']['date']) && $fall['discharge']['date'] != '0000-00-00' && $visibleEventTypes['discharge_ambulant'] == 1) {
                            $chartEvents [] = [
                                'x' => strtotime($fall['discharge']['date']),
                                'color' => $colors['discharge_ambulant'],
                                'event_type' => 'discharge_ambulant',
                                'label' => '<b>'.date('d.m.Y H:i',strtotime($fall['discharge']['date'])).'</b><br/>Entlassungs - '.$client_details[$client_ids[$fall_ipid]]['team_name'],
                            ];
                        }
                    }
                }
            }
        }



        //falls clinical
        if ($visibleEventTypes['admission_clinical'] == 1 || $visibleEventTypes['discharge_clinical'] == 1) {
            $patientCasesStatus = new PatientCaseStatus();
            //$patientcases = $patientCasesStatus->get_list_patient_status($ipid,$clientid);
            $patstatus = Doctrine_Query::create()
                ->select('*')
                ->from('PatientCaseStatus')
                ->whereIn('ipid', $allIpids)
                ->andWhere('isdelete=0')
                ->orderBy('admdate');
            $patientcases = $patstatus->fetchArray();

            $casetypes = $patientCasesStatus->case_types;
            $casestatus = $patientCasesStatus->case_status;

            foreach ($patientcases as $fall) {
                if ($visibleEventTypes['admission_clinical'] == 1) {
                    $chartEvents [] = [
                        'x' => strtotime($fall['admdate']),
                        'color' => $colors['admission_clinical'],
                        'event_type' => 'admission_clinical',
                        'label' => '<b>'.date('d.m.Y H:i',strtotime($fall['admdate'])).'</b><br/>Aufnahme - '.$client_details[$client_ids[$fall['ipid']]]['team_name'],
                    ];
                }
                if ($visibleEventTypes['discharge_clinical'] == 1) {
                    $chartEvents [] = [
                        'x' => strtotime($fall['disdate']),
                        'color' => $colors['discharge_clinical'],
                        'event_type' => 'discharge_clinical',
                        'label' => '<b>'.date('d.m.Y H:i',strtotime($fall['disdate'])).'</b><br/>Entlassungs  - '.$client_details[$client_ids[$fall['ipid']]]['team_name'],
                    ];
                }
            }
        }

        //diagnosis
        if ($visibleEventTypes['diagnosis'] == 1) {
            $PatientDiagnosis = new PatientDiagnosis();
            $patientDiagnosis = $PatientDiagnosis->getAllDiagnosisClinical($ipid,$clientid);

            
            //TODO-4120 Ancuta 10.05.2021
            foreach ($patientDiagnosis as $ipidDiagd=>$diagnos_items) {
                foreach ($diagnos_items as $key_d=>$ddata) {
                    if(!empty($ddata['PatientDiagnosisClinical'])){
                        $groupped_diagnosis[$ipidDiagd][date('Y-m-d',strtotime($ddata['PatientDiagnosisClinical']['start_date']))][] = $ddata['PatientDiagnosisClinical']['icd_code']. ' | ' .$ddata['PatientDiagnosisClinical']['icd_description'];
                    }
                }
            }
            
            foreach($groupped_diagnosis as $p_ipid => $diagno2dates){
                foreach($diagno2dates as $kdate => $dlabel){
                    $dig = implode("<br/>",$dlabel);
                    $chartEvents [] = [
                        'x' => strtotime($kdate), //in milliseconds for js
                        'color' => $colors['diagnosis'],
                        'event_type' => 'diagnosis',
                        'label' => '<b>'.date('d.m.Y',strtotime($kdate)).'</b><br/> Diagnosis: <br/>'. $dig,
                    ];
                }
            }
            
            
            
            /* 
            foreach ($patientDiagnosis as $ipidDiag) {
                foreach ($ipidDiag as $diagnosis) {
                    $chartEvents [] = [
                        'x' => strtotime($diagnosis['PatientDiagnosisClinical']['start_date']), //in milliseconds for js
                        'color' => $colors['diagnosis'],
                        'event_type' => 'diagnosis',
                        'label' => '<b>'.date('d.m.Y',strtotime($diagnosis['PatientDiagnosisClinical']['start_date'])).'</b><br/> Diagnosis: '. $diagnosis['PatientDiagnosisClinical']['icd_code']. ' | ' . $diagnosis['PatientDiagnosisClinical']['icd_description'],
                    ];
                }
            }
            */
            
            //-- 
        }

        //contactforms
        if ($visibleEventTypes['basis-assessment'] == 1) {
            $sql = "*,id, ipid, course_date, wrong, done_date, create_user, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name";

            $patientCourse = Doctrine_Query::create()
                ->select($sql)
                ->from('PatientCourse')
                ->where("wrong = 0")
                ->andWhere('ipid = ?',$ipid)
                ->andWhere('tabname = "'. addslashes(Pms_CommonData::aesEncrypt('contact_form_save')) .'"')
                ->orderBy('course_date ASC')->fetchArray();

            foreach ($patientCourse as $course) {
                $formIds[] = $course['done_id'];
            }

            $contactFormSelect = Doctrine_Query::create()
                ->select('*,comment as comment_block')
                ->from('ContactForms')
                ->where('ipid = ?', $ipid)
                ->andwhere('isdelete="0"')
                ->andWhere('billable_date BETWEEN "' . date('Y-m-d H:i:s',$start_date) . '" AND "' . date('Y-m-d H:i:s',$end_date) . '"')
                ->andWhereIn('id',!empty($formIds) ? $formIds : []);
            $patientContactForms = $contactFormSelect->fetchArray();
            foreach ($patientContactForms as $contactForm) {
                $contactFormsArray[$contactForm['id']] = $contactForm;
            }

            foreach ($patientCourse as $course) {
                if (!empty($contactFormsArray[$course['done_id']]['billable_date'])) {
                    $chartEvents [] = [
                        'x' => strtotime($contactFormsArray[$course['done_id']]['billable_date']),
                        'color' => $colors['basis-assessment'],
                        'event_type' => 'basis-assessment',
                        'label' => '<b>'.date('d.m.Y H:i',strtotime($contactFormsArray[$course['done_id']]['billable_date'])).'</b><br/>Kontaktformular - <a target="_blank" href="'.APP_BASE.'stats/patientfileupload?doc_id='.$course['recordid'].'&id='.$_REQUEST['id'].'">PDF</a>',
                    ];
                }
            }
        }

        //custom events from chart
        if ($visibleEventTypes['chart-custom-events'] == 1) {
            $patientEreignisse = FormBlockCustomEvent::get_patients_chart($ipid,[
                'start' => date('Y-m-d H:i:s', $start_date),
                'end' => date('Y-m-d H:i:s', $end_date)
            ]);

            foreach ($patientEreignisse as $chartPoint) {
                $chartEvents [] = [
                    'x' => strtotime($chartPoint['form_start_date']),
                    'color' => $colors['chart-custom-events'],
                    'event_type' => 'chart-custom-events',
                    'label' => '<b>'.date('d.m.Y H:i',strtotime($chartPoint['form_start_date'])).'</b><br/>'.$chartPoint['custom_event_name'],
                ];
            }
        }


        //treatment plans
        //contactforms
        if ($visibleEventTypes['treatment-plan'] == 1) {
            $sql = "*,id, ipid, course_date, wrong, done_date, create_user, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname, AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name";

            $patientCourse = Doctrine_Query::create()
                ->select($sql)
                ->from('PatientCourse')
                ->where("wrong = 0")
                ->andWhere('ipid = ?',$ipid)
                ->andWhere('tabname = "'. addslashes(Pms_CommonData::aesEncrypt('treatmentplan_pdf')) .'"')
                ->orderBy('course_date ASC')->fetchArray();

            foreach ($patientCourse as $course) {
                $chartEvents [] = [
                    'x' => strtotime($course['done_date']),
                    'color' => $colors['treatment-plan'],
                    'event_type' => 'treatment-plan',
                    'label' => '<b>'.date('d.m.Y H:i',strtotime($course['done_date'])).'</b><br/>Behandlungsplan - <a target="_blank" href="'.APP_BASE.'stats/patientfileupload?doc_id='.$course['recordid'].'&id='.$_REQUEST['id'].'">PDF</a>',
                ];
            }
        }

        //calendar-events
        if ($visibleEventTypes['calendar-events'] == 1) {
// -------------- patient calendar stuff

            //		get verlauf koordination
            $course = new PatientCourse();
            $patientCourse = $course->getCourseDataByShortcut($ipid, "V", false, true);

            foreach($patientCourse as $koordination)
            {
                $coordTitle = explode("|", $koordination['course_title']);
                $user_sh_k[$koordination['id']] = $users_data_ini[$koordination['create_user']]['initials'];

                $chartEvents [] = [
                    'x' => strtotime($coordTitle[2]),
                    'color' => $colors['calendar-events'],
                    'event_type' => 'calendar-events',
                    'label' => '<b>'.date('d.m.Y H:i',strtotime($coordTitle[2])).'</b><br/>'.$user_sh_k[$koordination['id']]." - Koordination: \n" . $coordTitle[0] . "minuten",
                ];

//            $eventsArray[] = array(
//                'id' => $koordination['id'],
////					    'title' => "Koordination: \n".$coordTitle[0]."minuten - ".$coordTitle[1],
//// 					'title' => "Koordination: \n" . $coordTitle[0] . "minuten",
//                'title' => $user_sh_k[$koordination['id']]." - Koordination: \n" . $coordTitle[0] . "minuten",// added user shortcut - 11-05-2015 :: ISPC 1343
//                'start' => date("Y-m-d H:i:s", strtotime($coordTitle[2])),
//                //'color' => "#33CC66",
//                //'textColor' => 'black',
//                'color' => $patient_course_settings['v_color'],
//                'textColor' => $patient_course_settings['v_text_color'],
//                'allDay' => false,
//                'eventType' => "9" //koord verlauf entry
//            );
            }
//		end get verlauf koordination
//		get verlauf xt
            $patient_course = $course->getCourseDataByShortcut($ipid, "XT", false, true);

            foreach($patient_course as $k_xt => $v_xt)
            {
                $coord_title = explode("|", $v_xt['course_title']);

                $user_sh_xt[$v_xt['id']] = $users_data_ini[$v_xt['create_user']]['initials'];

                $chartEvents [] = [
                    'x' => strtotime($coordTitle[2]),
                    'color' => $colors['calendar-events'],
                    'event_type' => 'calendar-events',
                    'label' => '<b>'.date('d.m.Y H:i',strtotime($coordTitle[2])).'</b><br/>'.$user_sh_xt[$v_xt['id']]." - Telefonat: \n" . $coord_title[0] . "minuten",
                ];

//            $eventsArray[] = array(
//                'id' => $v_xt['id'],
//// 					'title' => "Telefonat: \n" . $coord_title[0] . "minuten",
//                'title' => $user_sh_xt[$v_xt['id']]." - Telefonat: \n" . $coord_title[0] . "minuten", // added user shortcut - 11-05-2015 :: ISPC 1343
//                'start' => date("Y-m-d H:i:s", strtotime($coord_title[2])),
//                //'color' => "#33CC66",
//                //'textColor' => 'black',
//                'color' => $patient_course_settings['xt_color'],
//                'textColor' => $patient_course_settings['xt_text_color'],
//                'allDay' => false,
//                'eventType' => "16" //Telefon verlauf entry
//            );
            }
//		end get verlauf xt
//		get verlauf U
            $patient_course = $course->getCourseDataByShortcut($ipid, "U", false, true);

            foreach($patient_course as $k_u => $v_u)
            {

                $user_sh_v_u[$v_u['id']] = $users_data_ini[$v_u['create_user']]['initials'];


                $coord_title = explode("|", $v_u['course_title']);


                $chartEvents [] = [
                    'x' => strtotime($coord_title[3]),
                    'color' => $colors['calendar-events'],
                    'event_type' => 'calendar-events',
                    'label' => '<b>'.date('d.m.Y H:i',strtotime($coord_title[3])).'</b><br/>'.$user_sh_v_u[$v_u['id']]." - Beratung: \n" . $coord_title[1] . "minuten",
                ];

//            $eventsArray[] = array(
//                'id' => $v_u['id'],
//// 					'title' => "Beratung: \n" . $coord_title[1] . "minuten",
//                'title' => $user_sh_v_u[$v_u['id']]." - Beratung: \n" . $coord_title[1] . "minuten", // added user shortcut - 11-05-2015 :: ISPC 1343
//                'start' => date("Y-m-d H:i:s", strtotime($coord_title[3])),
//                //'color' => "#33CC66",
//                //'textColor' => 'black',
//                'color' => $patient_course_settings['u_color'],
//                'textColor' => $patient_course_settings['u_text_color'],
//                'allDay' => false,
//                'eventType' => "15" //Beratung verlauf entry
//            );
            }
//		end get verlauf U
//		get custom events
            $docCustomEv = new DoctorCustomEvents();
            $customEvents = $docCustomEv->getDoctorCustomEvents($userid, $clientid, $ipid);

//		print_r($customEvents); exit;
            foreach($customEvents as $cEvent)
            {
                $chartEvents [] = [
                    'x' => strtotime($cEvent['startDate']),
                    'color' => $colors['calendar-events'],
                    'event_type' => 'calendar-events',
                    'label' => '<b>'.date('d.m.Y H:i',strtotime($cEvent['startDate'])).'</b><br/> Calendar:'.$cEvent['eventTitle'],
                ];


//            if($cEvent['allDay'] == 1)
//            {
//                $allDay = true;
//            }
//            else
//            {
//                $allDay = false;
//            }
//
//            $eventsArray[] = array(
//                'id' => $cEvent['id'],
//                'title' => $cEvent['eventTitle'],
//                'start' => date("Y-m-d H:i:s", strtotime($cEvent['startDate'])),
//                'end' => date("Y-m-d H:i:s", strtotime($cEvent['endDate'])),
//                'color' => '#33CCFF',
//                'textColor' => 'black',
//                'allDay' => $allDay,
//                'eventType' => $cEvent['eventType'], //custom event
//
//                'comments' => $cEvent['comments'], // this are the original so user can edit
//                'comments_qtip' => nl2br($this->view->escape($cEvent['comments'])), // this are displayed as qtip
//            );
            }
//		end get custom events
            //Patient birthday
            $pt = Doctrine_Query::create()
                ->select("*")
                ->from('PatientMaster')
                ->where('ipid = ?', $ipid);
            $patient_res = $pt->fetchArray();

            if($patient_res)
            {
                foreach($patient_res as $k_pat => $v_pat)
                {
                    $patient_birthd = date('d.m.Y', strtotime($v_pat['birthd']));
                }
            }

            $pm = new PatientMaster();
            
            $c_start_date= date('Y-m-d H:i:s', $start_date);
            $c_end_date = date('Y-m-d H:i:s', $end_date);
            $calendardays_array = $pm->getDaysInBetween($c_start_date, $c_end_date);
//             $calendardays_array = $pm->getDaysInBetween($start_date, $end_date);

            $patbirthd_arr = explode(".", $patient_birthd);


            if($patient_birthd)
            {
                foreach($calendardays_array as $k => $day)
                {
                    $patient_date = date("Y-m-d", mktime(0, 0, 0, $patbirthd_arr[1], $patbirthd_arr[0], date('Y', strtotime($day))));
                    if($patient_date == $day)
                    {
                        $evnt_date = date('Y-m-d', strtotime($day));
                    }
                }

                $chartEvents [] = [
                    'x' => strtotime($evnt_date),
                    'color' => $colors['calendar-events'],
                    'event_type' => 'calendar-events',
                    'label' => '<b>'.date('d.m.Y H:i',strtotime($evnt_date)).'</b><br/> Geburtstag des Patienten',
                ];

//            $eventsArray[] = array(
//                //'id' => $v_u['id'],
//                'title' => "Geburtstag des Patienten",
//                'editable' => false,
//                'start' => $start_date,
//                'color' => "#E0B0FF",
//                'textColor' => 'black',
//                'allDay' => true,
//                'eventType' => "19", //Birthday
//            );
            }

            //End patient Birthday

            // ----------------------- end patient calendar stuff ----------------------

        }

        //patient-files
        if ($visibleEventTypes['patient-files'] == 1) {
            $force_include_tabnames = [
                'wounddocumentation_incr',
                'wounddocumentation_uploaded_img',
                'fallprotocolform_save',
                WlAssessment::PATIENT_FILE_TABNAME,
                PatientNutritionalStatus::PATIENT_FILE_TABNAME,
                PatientBesd::PATIENT_FILE_TABNAME,
                'acp_file_living_will',
                'acp_file_care_orders',
                'acp_file_healthcare_proxy',
                MamboAssessment::PATIENT_FILE_TABNAME,
                PatientTreatmentPlan::PATIENT_FILE_TABNAME,
            ];

            $patient_fl = Doctrine_Query::create()
                ->from('PatientFileUpload pfu')
                ->where('ipid = ?', $ipid)
                ->andWhere('recordid = "0" OR  (recordid != "0" AND tabname in ('. implode(', ', array_fill(0, count($force_include_tabnames), '?')) .') ) ', $force_include_tabnames)
            ;

            $patient_fl->select("*, AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
				AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
				AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type,
				AES_DECRYPT(meta_name,'" . Zend_Registry::get('salt') . "') as meta_name,
				AES_DECRYPT(comment,'" . Zend_Registry::get('salt') . "') as comment,
				IF(`system_generated` = '1', CONCAT('ISPC ',AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "')), AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "')) as title_sort");

            $patient_all_files = Pms_CommonData::array_stripslashes($patient_fl->fetchArray());
//            $files = new PatientFileUpload();
//            $patient_all_files = $files->getFileData($ipid);

            foreach ($patient_all_files as $file) {
                if($file['system_generated'] == 1){
                    $file_prefix = "ISPC ";
                } else{
                    $file_prefix = "";
                }
                $chartEvents [] = [
                    'x' => strtotime($file['create_date']),
                    'color' => $colors['patient-files'],
                    'event_type' => 'patient-files',
                    'label' => '<b>'.date('d.m.Y H:i',strtotime($file['create_date'])).'</b><br/>'.$file_prefix.(!empty($file['meta_name'])?$file['meta_name']:$file['title']).' - <a target="_blank" href="'.APP_BASE.'stats/patientfileupload?doc_id='.$file['id'].'&id='.$_REQUEST['id'].'">'.$file['file_type'].'</a>',
                ];
            }
        }

        //remove event outside filter for unfiltered queries above
        foreach ($chartEvents as $index => &$event) {
            if ($event['x'] < $start_date || $event['x'] > $end_date) {
                unset($chartEvents[$index]);
            }
        }

        //sort the timeline - leave this last to have incrementing indexes for js array
        usort($chartEvents, function ($a,$b) {
            return $a['x'] - $b['x'];
        });
//        foreach ($chartEvents as $index => &$event) {
//            unset($event['x']);
//        }
        $this->view->chartEvents = $chartEvents;

        if ($this->getRequest()->isXmlHttpRequest()) {
            echo json_encode([
                'start_date' => $this->view->start_date,
                'end_date' => $this->view->end_date,
                'chartEvents' => $this->view->chartEvents
            ]);
            exit();
        }
    }
    
    
    public function openmailAction()
    {
        $this->_helper->layout->setLayout('layout_ajax');
        $start = microtime(true);
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $this->view->userid = $logininfo->userid;
        $previleges = new Pms_Acl_Assertion();
        $return = $previleges->checkPrevilege('message', $logininfo->userid, 'canview');
        
        if(!$return)
        {
            $this->_redirect(APP_BASE . "error/previlege");
        }
        
        // get all user messages
        $mail = Doctrine_Query::create()
        ->select("id")
        ->from('Messages')
        ->where('recipient = ' . $logininfo->userid.' OR sender = ' . $logininfo->userid.'');
        $mailarray_user = $mail->fetchArray();

        
        foreach($mailarray_user as $k=>$msg_data){
            $user_messages_array[] = $msg_data['id'];
        }
        
        if(empty($user_messages_array)){
            $user_messages_array[] = "9999999999";
        }
        
        // Error
        if(!in_array($_REQUEST['msg_id'],$user_messages_array)){
            $this->_redirect(APP_BASE . "error/previlege");
        }
        
        
        $mail = Doctrine_Query::create()
        ->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content")
        ->from('Messages')
        ->where('id= ?',  $_REQUEST['msg_id']);
        $mailarray = $mail->fetchArray();
        
        
        $priority = "";
        if( ! empty($mailarray[0]['priority']) && $mailarray[0]['priority'] != "none"){
            $priority = $this->view->translate('priority_subject_label').': '.$this->view->translate('priority_'.$mailarray[0]['priority']);
            $priority .= " | ";
        }
        
        
        $this->view->title = $priority.$mailarray[0]['title'];
        $this->view->msg_date = date('d.m.Y H:i', strtotime($mailarray[0]['msg_date']));
        
        if($mailarray[0]['source'] == "6_weeks_system_message")
        {
            $content = str_replace("Anlage 4a", "Anlage 4", $mailarray[0]['content']);
            
            $content = str_replace("anlage4awl", "anlage4wl", $content);
        }
        else
        {
            $content = $mailarray[0]['content'];
        }
        
        $this->view->content = nl2br($content);
        
        $userid = $mailarray[0]['sender'];
        if($userid > 0)
        {
            $user = Doctrine::getTable('User')->find($userid);
            $userarray = $user->toArray();
            $this->view->sendername = ucfirst($userarray['last_name']) . ", " . ucfirst($userarray['first_name']);
            $this->view->reply = '1';
        }
        else
        {
            $this->view->sendername = "System Message";
            $this->view->reply = '0';
        }
        
        $other_users = explode(',', $mailarray[0]['recipients']);
        if(!empty($other_users))
        {
            $usr = new User();
            $users_data = $usr->getMultipleUserDetails($other_users);
            
            foreach($users_data as $k_usrdata => $v_usrdata)
            {
                $users_datas[] = $v_usrdata['last_name'] . ' ' . $v_usrdata['first_name'];
            }
            $this->view->other_users_data = $users_datas;
        }
        
        
                
        // set message as read
        $update = Doctrine_Query::create()
        ->update('Messages')
        ->set('read_msg', '1')
        ->where('id = ?', $_REQUEST['msg_id'])
        ->andWhere('recipient=' . $logininfo->userid);
        $update->execute();
 
        $end = microtime(true) - $start;
        
        
//         echo round($end, 0);
//         echo "----";
        
    }
    public function sendmessagesAction()
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $previleges = new Pms_Acl_Assertion();
        $return = $previleges->checkPrevilege('message', $logininfo->userid, 'canadd');
        
        if(!$return)
        {
            $this->_redirect(APP_BASE . "error/previlege");
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        if($this->getRequest()->isPost())
        {
            $message_form = new Application_Form_Messages();
            
            if($message_form->Validate($_POST))
            {
                
                $message_form->InsertData($_POST);
                $this->view->error_message = $this->view->translate('mailsentmsg');
            }
            else
            {
                $this->view->useridjs = $_POST['userid'];
                $this->retainValues($_POST);
                $message_form->assignErrorMessages();
            }
        }
        
        $messages_obj = new Messages();
        $priority_ranks = $messages_obj->priority_ranks();
        $this->view->priority_ranks = $priority_ranks;
        $this->view->reply = 0;
        
        if($logininfo->usertype != 'SA' && $clientid > 0)
        {
            // TODO-1647 @Ancuta 22.06.2018
            /* $user_pseudo =  new UserPseudoGroup();
             $user_ps =  $user_pseudo->get_userpseudo(); */
            
            $UserPseudoGroup = Doctrine_Query::create()
            ->select("gr.*")
            ->from('UserPseudoGroup gr INDEXBY gr.id')
            ->where('gr.clientid = ?', $clientid)
            ->andWhere('gr.isdelete = 0')
            
            ->addSelect("gru.*")
            ->leftJoin("gr.PseudoGroupUsers gru ON (gr.id=gru.pseudo_id AND gru.isdelete = 0)")
            
            ->fetchArray();
            
            $user_ps = $UserPseudoGroup;
            
            foreach($UserPseudoGroup as $ps_gr_id=>$ps_gr_details){
                if ( ! empty($ps_gr_details['PseudoGroupUsers'])){
                    foreach($ps_gr_details['PseudoGroupUsers'] as $ps_gr_user_k =>$ps_gr_user_details){
                        $u2p[$ps_gr_user_details['user_id']] = $ps_gr_user_details['pseudo_id'];
                    }
                } else{
                    unset($UserPseudoGroup[$ps_gr_id]);
                }
            }
            $this->view->user_pseudo  = $UserPseudoGroup;
            $this->view->userp  = $u2p;
            //-- END TODO-1647
            
            $ug = new Usergroup();
            $grouparr = $ug->getClientGroups($clientid);
            
            foreach($grouparr as $k_group => $v_group)
            {
                $client_groups[$v_group['id']] = $v_group;
            }
            
            $user = Doctrine_Query::create()
            ->select("*")
            ->from('User')
            ->where('clientid = ' . $clientid)
            ->andWhere('isactive=0 and isdelete = 0')
            ->orderBy('last_name ASC');
            $userarray = $user->fetchArray();
            
            if(count($userarray) > 0)
            {
                $available_user_groups = array();
                foreach($userarray as $k_user => $v_user)
                {
                    if($v_user['groupid'] != '0')
                    {
                        $available_user_groups[$v_user['groupid']] = $client_groups[$v_user['groupid']]['groupname'];
                    }
                }
                
                $this->view->available_groups = $available_user_groups;

                User::beautifyName($userarray);
                foreach($userarray as $k=>$udata){
                    $userarray[$k]['pseudo_id']  = $u2p[$udata['id']];
                }
                $this->view->user_array= $userarray;
                //special client users
                $client_s_user = new UserMessageClient();
                $get_client_s_users = $client_s_user->getMessageSpecialUsers($clientid);
                
                $this->view->client_special_users = $get_client_s_users;
            }
            else
            {
                $this->view->usergrid = "<div class='err'>" . $this->view->translate('nousers') . "</div>";
            }
        }
        else
        {
            $user_pseudo =  new UserPseudoGroup();
            $user_ps_arr =  $user_pseudo->get_userpseudo();
            
            foreach($user_ps_arr as $k=>$psg){
                $user_ps[$psg['id']] = $psg;
            }
            
            $user_grouppseudo =  new PseudoGroupUsers();
            $user_gr_ps =  $user_grouppseudo->get_usersgroup();
            
            foreach($user_ps as $pseudo_id => $v)
            {
                $arr_gr_pseudo[$v['id']] = $user_grouppseudo->get_users($v['id']);
                
                if(empty($arr_gr_pseudo[$v['id']]))
                {
                    unset( $user_ps[$pseudo_id]);
                }
            }
            
            
            $this->view->user_pseudo  = $user_ps;
            if($logininfo->clientid > 0)
            {
                $whereclient = ' or clientid = ' . $logininfo->clientid;
            }
            $ug = new Usergroup();
            $grouparr = $ug->getClientGroups($logininfo->clientid);
            
            foreach($grouparr as $k_group => $v_group)
            {
                $client_groups[$v_group['id']] = $v_group;
            }
            
            foreach($user_gr_ps as $k_gr =>$val_gr)
            {
                $u2p[$val_gr['user_id']]= $val_gr['pseudo_id'];
            }
            foreach ($user_ps as $k => $v)
            {
                $arr_gr_pseudo = $user_grouppseudo->get_users( $v['id']);
            }
            
            
            $user = Doctrine_Query::create()
            ->select('*')
            ->from('User')
            ->where('id = ' . $logininfo->userid . $whereclient)
            ->andWhere('isactive=0 and isdelete = 0')
            ->orderBy('last_name ASC');
            $userarray = $user->fetchArray();
            
            if(count($userarray) > 0)
            {
                $available_user_groups = array();
                foreach($userarray as $k_user => $v_user)
                {
                    if($v_user['groupid'] != '0')
                    {
                        $available_user_groups[$v_user['groupid']] = $client_groups[$v_user['groupid']]['groupname'];
                    }
                }
                //print_r($available_user_groups); exit;
                $this->view->available_groups = $available_user_groups;
                
                $this->view->userp = $u2p;
                //ISPC-2409 Ancuta 08.11.2019
                User::beautifyName($userarray);
                foreach($userarray as $k=>$udata){
                    $userarray[$k]['pseudo_id']  = $u2p[$udata['id']];
                }
                $this->view->user_array= $userarray;
 
                // --
                //special client users
                $client_s_user = new UserMessageClient();
                $get_client_s_users = $client_s_user->getMessageSpecialUsers($clientid);
                
                $this->view->client_special_users = $get_client_s_users;
            }
            else
            {
                $this->view->usergrid = "<div class='err'>" . $this->view->translate('nousers') . "</div>";
            }
        }
    }
    public function replymailAction()
    {
 
        $this->_helper->layout->setLayout('layout_ajax');
        $logininfo = new Zend_Session_Namespace('Login_Info');
        
        $previleges = new Pms_Acl_Assertion();
        $return = $previleges->checkPrevilege('message', $logininfo->userid, 'canadd');
        if(!$return)
        {
            $this->_redirect(APP_BASE . "error/previlege");
        }
        $this->_helper->viewRenderer('sendmessages');
 
        $message_id = $_REQUEST['msg_id'];
        if($this->getRequest()->isPost())
        {
            $message_form = new Application_Form_Messages();
//             dd($_POST);
            if($message_form->Validate($_POST))
            {
                
                $message_form->InsertReplyData($_POST);
                $this->view->error_message = $this->view->translate('mailsentmsg');
                $this->_helper->viewRenderer->setNoRender();
                $this->_redirect(APP_BASE . "overview/overviewefa");
            }
            else
            {
//                 $this->_helper->viewRenderer->setNoRender();
//                 $this->retainValues($_POST);
//                 $message_form->assignErrorMessages();
            }
            
            if(empty($message_id)){
                $message_id = $_POST['msg_id'];
            }
        }
        $this->view->reply = 1;
        
        $messages_obj = new Messages();
        $priority_ranks = $messages_obj->priority_ranks();
        $this->view->priority_ranks = $priority_ranks;
        
        $ug = new Usergroup();
        $grouparr = $ug->getClientGroups($logininfo->clientid);
        
        foreach($grouparr as $k_group => $v_group)
        {
            $client_groups[$v_group['id']] = $v_group;
        }
        
        $mail = Doctrine_Query::create()
        ->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,AES_DECRYPT(content,'" . Zend_Registry::get('salt') . "') as content")
        ->from('Messages')
        ->where('id= ?',  $message_id);
        $mailarray = $mail->fetchArray();

        $userid = $mailarray[0]['sender'];
        
        $sender_id = $mailarray[0]['sender'];
        //ISPC-2409 Ancuta 08.11.2019
        $this->view->sender_id =$sender_id;
        //--
        //ISPC-2808,Elena,29.01.2021
        $aAllRecipients = [];
        $aAllRecipients[] = $sender_id;
        
            
        $aAllRecipients = explode(',', $mailarray[0]['recipients']);
        $aOtherRecipients =  [];
        //don't send to me
        foreach($aAllRecipients as $rec){
            $aOtherRecipients[] = $rec;
        }
        $this->view->recipients = $aOtherRecipients;
        
 
        $user = Doctrine::getTable('User')->find($userid);
        $userarray = $user->toArray();
        $this->view->sendername = ucfirst($userarray['last_name']) . "," . ucfirst($userarray['first_name']);
        
        
        $priority = "";
 
        $this->view->id =   $mailarray[0]['id'];
        $this->view->title = "AW: " . $mailarray[0]['title'];
        $this->view->content .= "\n";
        $this->view->content .= "\n";
        $this->view->content .= "\n";
        $this->view->content .= "\n";
        $this->view->content .= "---------------------------------------\n";
        $this->view->content .= $this->view->translate('date') . ": " . date('d.m.Y H:i', strtotime($mailarray[0]['msg_date'])) . "\n";
        $this->view->content .= $this->view->translate('sender') . ": " . ucfirst($userarray['last_name']) . "," . ucfirst($userarray['first_name']) . "\n";
        $this->view->content .= $this->view->translate('title') . ": ".$priority. ucfirst($mailarray[0]['title']) . "\n";
        $this->view->content .= "---------------------------------------\n";
        $this->view->content .= $mailarray[0]['content'];
        
        $user = Doctrine_Query::create()
        ->select('*')
        ->from('User')
        ->where('id = ' . $userid . ' and isactive=0 and isdelete = 0')
        ->orWhere('clientid = ' . $logininfo->clientid . ' and isactive=0 and isdelete = 0')
        ->orWhereIn('id', $aAllRecipients) //ISPC-2808,Elena,29.01.2021
        ->orderBy('last_name asc');
        $userarray = $user->fetchArray();
        
        $user_pseudo =  new UserPseudoGroup();
        $user_ps_arr =  $user_pseudo->get_userpseudo();
        
        foreach($user_ps_arr as $k=>$psg){
            $user_ps[$psg['id']] = $psg;
        }
        
        $user_grouppseudo =  new PseudoGroupUsers();
        $user_gr_ps =  $user_grouppseudo->get_usersgroup();
        
        foreach($user_ps as $pseudo_id => $v)
        {
            $arr_gr_pseudo[$v['id']] = $user_grouppseudo->get_users($v['id']);
            
            if(empty($arr_gr_pseudo[$v['id']]))
            {
                unset( $user_ps[$pseudo_id]);
            }
        }
        
        $this->view->user_pseudo  = $user_ps;
        if($logininfo->clientid > 0)
        {
            $whereclient = ' or clientid = ' . $logininfo->clientid;
        }
        $ug = new Usergroup();
        $grouparr = $ug->getClientGroups($logininfo->clientid);
        
        foreach($grouparr as $k_group => $v_group)
        {
            $client_groups[$v_group['id']] = $v_group;
        }
        
        foreach($user_gr_ps as $k_gr =>$val_gr)
        {
            $u2p[$val_gr['user_id']]= $val_gr['pseudo_id'];
        }
        foreach ($user_ps as $k => $v)
        {
            $arr_gr_pseudo = $user_grouppseudo->get_users( $v['id']);
        }
        
        $this->view->user_pseudo  = $user_ps;
        
        $available_user_groups = array();
        foreach($userarray as $k_user => $v_user)
        {
            if($v_user['groupid'] != '0')
            {
                if(!empty($client_groups[$v_user['groupid']]['groupname']))
                {
                    $available_user_groups[$v_user['groupid']] = $client_groups[$v_user['groupid']]['groupname'];
                }
                else //special case where user has diferent client id
                {
                    $udata = $ug->getUserGroupData($v_user['groupid']);
                    $available_user_groups[$v_user['groupid']] = $udata[0]['groupname'];
                }
            }
        }
        
        User::beautifyName($userarray);
        foreach($userarray as $k=>$udata){
            $userarray[$k]['pseudo_id']  = $u2p[$udata['id']];
        }
        $this->view->user_array= $userarray;
        
        
        $this->view->available_groups = $available_user_groups;
        $this->view->idul = '3';
        $this->view->userp = $u2p;
        $grid = new Pms_Grid($userarray, 5, count($userarray), "listclientuser.html");
        $grid->sender = $sender_id;
        $this->view->usergrid = $grid->renderGrid();
    }
    
    
    public function sendmsgAction()
    {
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->_helper->viewRenderer->setNoRender();
        
        $return['error'] = '0';
        if($this->getRequest()->isPost())
        {
            $message_form = new Application_Form_Messages();
             $return['req'] = $_REQUEST;
             $return['post'] = $_POST;
          
            if($message_form->Validate($_REQUEST))
            {
                if($_POST['reply'] == '1'){
                    $message_form->InsertReplyData($_REQUEST);
                    $this->view->error_message = $this->view->translate('mailsentmsg');
                }
                else
                {
                    $message_form->InsertData($_REQUEST);
                    $this->view->error_message = $this->view->translate('mailsentmsg');
                }
                
                $return['error'] = '0';
                $return['msg'] = 'Success';
                 
            }
            else
            {
                $this->view->useridjs = $_REQUEST['userid'];
//                 $this->retainValues($_REQUEST);
//                 $msgs = $message_form->assignErrorMessages();
                
                $return['error'] = '1';
                $return['msg'] = 'Error';
             
                
            }
        }
        
        echo json_encode($return);
        exit;
        
    }
    
    /**
     *     ISPC-2864 Ancuta 13.04.2021
     */
    public function patientproblemsAction(){
        
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $groupid = $logininfo->groupid;
        
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $this->view->encid = $_REQUEST['id'];
        $ipid = Pms_CommonData::getIpid($decid);
        $epid = Pms_CommonData::getEpid($ipid);
        
        // efa_problem_extension
        $client_details_arr = Client::getClientDataByid($clientid);
        
        if(!empty($client_details_arr)){
            $client_details = $client_details_arr[0];
        }
        $this->view->efa_extension = $client_details['efa_problem_extension'];
        
        
        
        if($client_details['efa_problem_extension'] == 'sapv'){
        
            $p_share_obj = new PatientsShare();
            $p_share_info= $p_share_obj->get_connection_by_ipid($ipid);
            $allIpids [] = $ipid;
            $client_ids[$ipid] = $clientid;
            if (!empty($p_share_info)) {
                foreach ($p_share_info as $link) {
                    if ($link['source'] == $ipid && $link['target'] != $ipid) {
                        $allIpids [] = $link['target'];
                        $client_ids[$link['target']] = $link['target_client'];
                    } elseif ($link['target'] == $ipid && $link['source'] != $ipid) {
                        $allIpids [] = $link['source'];
                        $client_ids[$link['source']] = $link['source_client'];
                    }
                }
            }
            $this->view->client_ids = $client_ids;
            
            // SAPV textarea
            $pat_sapv_q = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockProblemsSapv')
            ->where('ipid=?', $ipid);
            $pat_sapv = $pat_sapv_q->fetchArray();
            
            
            $patient_sapv_description = "";
            if(!empty($pat_sapv)){
                $patient_sapv_description = $pat_sapv[0]['sapv_description'];
            }
            $this->view->patient_sapv_description = $patient_sapv_description;
            
            //CONTACT FORMS
            $contactFormSelect = Doctrine_Query::create()
            ->select('*,comment as comment_block')
            ->from('ContactForms')
            ->whereIn('ipid', $allIpids)
            ->andwhere('isdelete="0"');
            $contactFormSelect->orderBy('billable_date DESC');
            $patientContactForms = $contactFormSelect->fetchArray();
            
            $last_contact_date  = ""; 
            if(!empty($patientContactForms)){
                $last_contact_date  = date('d.m.Y H:i',strtotime($patientContactForms['0']['billable_date'])); 
            }
            $this->view->last_contact_date =$last_contact_date;
            
            
            
            //PHONE
            $course = new PatientCourse();
            $patientCourse = $course->get_multiple_patient_course($allIpids, "XT");
            
            $all_pcc = array();
            foreach ($patientCourse as $pc_ipid => $pc_data){
                foreach($pc_data as $k=>$ppc){
                    $all_pcc[] = $ppc;
                }
            }
            
            $last_phone_date = "";
            if(!empty($all_pcc)){
                usort($all_pcc, array(new Pms_Sorter('done_date'), "_date_compare"));
                $last_pc = end($all_pcc); 
                
                $last_phone_date = date('d.m.Y H:i',strtotime($last_pc['done_date']));
            }
            $this->view->last_phone_date = $last_phone_date;
            
            //CUSTOM CALENDAR  EVENTS
            $docCustomEv = Doctrine_Query::create()
            ->select("*")
            ->from('DoctorCustomEvents')
           ->Where("clientid='" . $clientid . "'")
//             ->andWhere('viewForAll = "1"')
            ->andwhere("startDate between '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d"), date("Y"))) . "' and '" . date("Y-m-d H:i:00", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y"))) . "'")
            ;
            $docCustomEv->andWhereIn('ipid', $allIpids);
            $docCustomEv->orderBy('startDate ASC');
            $docCustomEv_Array = $docCustomEv->fetchArray();
            
            $next_custom_event ="";
            if(!empty($docCustomEv_Array)){
                $next_event = $docCustomEv_Array[0];
                
                $next_custom_event = date('d.m.Y H:i',strtotime($next_event['startDate']));
            }
            $this->view->next_custom_event = $next_custom_event;
            
    }
        
        
        
        //get client problems
        $client_problems_array = array();
        $client_problems_array = Doctrine_Query::create()
        ->select('*')
        ->from('ClientProblemsList IndexBy id')
        ->where("clientid = ?", $clientid)
        ->fetchArray();
        
        
        
        // get problems per patient 
        $patient_problems = FormBlockProblemsTable::find_patient_problems(array($ipid));
 
        
        
//         dd($patient_problems);
        $problems = array();
        foreach($patient_problems as $p_problem_id=>$prob_info){
            $problems[$p_problem_id]['problem_name'] = $client_problems_array[$prob_info['problem_id']]['problem_name'];
            $problems[$p_problem_id]['problem_date'] = date('d.m.Y H:i',strtotime($prob_info['documented_date']));
            if(!empty($prob_info['FormBlockProblemsSituations'])){
                foreach($prob_info['FormBlockProblemsSituations'] as $k=>$sit){
                    $all_problems_situations[$p_problem_id][$sit['situation_type']][] = $sit;
                    if(!empty($sit['situation_description'])){
                        $all_filled_problems_situations[$p_problem_id][$sit['situation_type']][] = $sit;
                    }
                }
                
                
                foreach($prob_info['FormBlockProblemsSituations'] as $k=>$sit){
                    if($sit['latest_version'] == '1'){
                        $problems[$p_problem_id]['problem_situations']['current'][$sit['situation_type']] = $sit; // only one  can be the latest
                        if(empty($sit['situation_description'])){
                            
                            
                            usort($all_filled_problems_situations[$p_problem_id][$sit['situation_type']], array(new Pms_Sorter('situation_date'), "_date_compare"));
                            
                            $problems[$p_problem_id]['problem_situations']['current'][$sit['situation_type']] = end( $all_filled_problems_situations[$p_problem_id][$sit['situation_type']] ); 
                        }
                    } else {
                        $problems[$p_problem_id]['problem_situations']['old'][$sit['version_nr']][$sit['situation_type']] = $sit;
                    }
                }
            }
        }
//         dd($problems);
        
        $this->view->patient_problems  = $problems;

        
        
        $user =  new User();
        $users_details = array();
        $users_details= $user->getUserByClientid($this->logininfo->clientid,1,true);
        
        // get bpss data 
        $pat_bpss_q = Doctrine_Query::create()
        ->select('*')
        ->from('FormBlockProblemsBpss')
        ->where('ipid=?', $ipid);
        $pat_bpss = $pat_bpss_q->fetchArray();
        
        
        $bpss_arr = array();
        foreach($pat_bpss as $k=>$bdata){
            $bpss_arr[$bdata['bpss_type']][$bdata['id']] = $bdata;
            $bpss_arr[$bdata['bpss_type']][$bdata['id']]['bpss_description'] = nl2br($bdata['bpss_description']);
            $bpss_arr[$bdata['bpss_type']][$bdata['id']]['qtip'] = date('d.m.Y H:i',strtotime($bdata['create_date'])).', '.$users_details[$bdata['create_user']];
        }
        
        $this->view->bpss_data  = $bpss_arr;
        
    }

    
    
    public function sortpatientproblemsAction(){
        
        if(empty($_REQUEST['id'])){
            return;
        }
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        if(!empty($_REQUEST['order'])){
            
            foreach($_REQUEST['order'] as $order_key=>$problem_id){
                $order = $order_key+1;
                $update = Doctrine_Query::create()
                ->update('FormBlockProblems')
                ->set('problem_order', '?',$order)
                ->where('id = ?', $problem_id)
                ->andWhere('ipid = ? ', $ipid);
                $update->execute();
                
            }
            
        }
        echo "1";
        exit;
    }
    
    
 
    public function savesituationboxAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        if(!empty($_REQUEST['fieldname'])){
            $field_data =  explode("-",$_REQUEST['fieldname']);
            $problem_id = $field_data['1'];
            $line_id = $field_data['2'];
            $situation_type = $field_data['3'];
        }
        
        if($ipid && $problem_id && $situation_type)          
        {
            //dd($_POST['content']);
            $content = nl2br($_POST['content']);
            
            if($line_id == "new"){
            
                $fbps = new FormBlockProblemsSituations();
                $fbps->ipid = $ipid;
                $fbps->patient_problem_id = $problem_id;
                $fbps->situation_date = date('Y-m-d H:i:s');
                $fbps->situation_type = $situation_type;
                $fbps->situation_description = $content;
                $fbps->latest_version = '1';
                $fbps->save();
                
                $line_id = $fbps->id;
                
            } else{
                
                $q = Doctrine_Query::create()
                ->update('FormBlockProblemsSituations')
                ->set('situation_description',"?",$content)
                ->where("id = ?", $line_id)
                ->andWhere("ipid = ?", $ipid);
                $q->execute();
            }
        }
        //load content
        if($clientid)
        {
            
            $patstatus = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockProblemsSituations')
            ->where('ipid=?', $ipid)
            ->andWhere('id=?',$line_id)
            ;
            $box = $patstatus->fetchArray();
 
            $user_text_box = trim(strip_tags($box[0]['situation_description']));
            
            if (strlen($user_text_box) == '0')
            {
                echo $this->view->translate('empty_user_text_box');
            }
            else
            {
                echo strip_tags($box[0]['situation_description'], '<br>');
            }
        }
        else
        {
            echo ''; //error
        }
        
        exit;
    }
    
    
    public function getlatestsituationsAction(){
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $latest = array();
        $used_problem = Doctrine_Query::create()
        ->select('*')
        ->from('FormBlockProblems')
        ->where('ipid=?', $ipid)
        ->andWhere('problem_id=?',$_REQUEST['problem_id']);
        $used_problem_arr = $used_problem->fetchArray();
        if(!empty($used_problem_arr)){
            $patient_line = $used_problem_arr[0]['id'];
        }
        
        if(!empty($patient_line)){
            $psituations_q = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockProblemsSituations')
            ->where('ipid=?', $ipid)
            ->andWhere('patient_problem_id=?',$patient_line)
            ->andWhere('isdelete = 0');
            $psituations_arr = $psituations_q->fetchArray();
            
            if(!empty($psituations_arr)){
            foreach($psituations_arr as $k=>$pls){
                if(!empty($pls['situation_description'])){
                    $all_filled_situation[$pls['situation_type']][] =$pls;
                }
            }
            
                foreach($psituations_arr as $k=>$ps){
//                     $latest[$ps['situation_type']] = $ps['situation_description'];
                    if($ps['latest_version'] == 1){
                        if(!empty($ps['situation_description'])){
                            $latest[$ps['situation_type']] = strip_tags($ps['situation_description'], '<br>');;
                        } else{
                
                            // get latest 
                            usort($all_filled_situation[$ps['situation_type']], array(new Pms_Sorter('situation_date'), "_date_compare"));
                            $latest_sit = end( $all_filled_situation[$ps['situation_type']] );
                            $latest[$ps['situation_type']] = $latest_sit['situation_description']; 
                        }
                    }
                }
            }
            
        }
        
        echo json_encode($latest);
        exit();
        
    }
    
    
    
    
    
    public function eventsAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        $ipid = $this->ipid;
        
        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            $decid = Pms_Uuid::decrypt($_REQUEST['patid']);
            $ipid = Pms_CommonData::getIpId($decid);
            $clientid = $this->logininfo->clientid;
            
            switch($_REQUEST['action'])
            {
    
                case 'show_form':
                    switch($_REQUEST['form'])
                    {
                            //ISPC-2864 Ancuta 14.04.2021
                        case 'bpssadd':
                            $afb = new Application_Form_FormBlockProblemsBpss();
                            
                            
                            $values['bpss_type'] = $_REQUEST['bpss_type'];
                            if($_REQUEST['recid'])
                            {
                                $values = FormBlockProblemsBpssTable::getInstance()->find($_REQUEST['recid'], Doctrine_Core::HYDRATE_ARRAY);
                            }
                            $values['ipid'] = $ipid;
                            $values['clientid'] = $clientid;
                            $values['enc_id'] = $_REQUEST['patid'];
                            $patient_problems_form = $afb->create_form_block_patient_problems_bpss($values);
                            $this->getResponse()->setBody($patient_problems_form)->sendResponse();
                            
                            exit;
                            break;
                            //--
                            
                            
                        default:
                            exit;
                            break;
                    }
                    break;
                    
                case 'save_form':
                    switch($_REQUEST['form'])
                    { 
                            
                        case 'bpsssave':
                            
                            switch($_REQUEST['subaction'])
                            {
                                case 'delete':
                                    $entity = FormBlockProblemsBpssTable::getInstance()->find($_POST['id']);
                                    
                                    if($entity)
                                    {
                                        $entity->delete();
                                    }
                                    break;
                                    
                                default:
                                    $fas = new Application_Form_FormBlockProblemsBpss();
                                    $form = $fas->save_form_block_patient_problems_bpss($ipid, $_POST);
                                    break;
                            }
                            
                            exit;
                            break;
                            //--
                        case 'sapvsave':
                            
                            switch($_REQUEST['subaction'])
                            {
                                case 'delete':
                                    $entity = FormBlockProblemsSapvTable::getInstance()->find($_POST['id']);
                                    
                                    if($entity)
                                    {
                                        $entity->delete();
                                    }
                                    break;
                                    
                                default:
                                    $data = $_POST;
                                    $data['ipid'] = $ipid;
                                    $entity = FormBlockProblemsSapvTable::getInstance()->findOrCreateOneBy(['ipid'], [$ipid], $data);
                                    
                                    break;
                            }
                            
                            exit;
                            break;
                            //--
                            
                        default:
                            exit;
                            break;
                    }
                    break;
          
                default:
                    exit;
                    break;
                    
            }
        }
    }
        
    /**
     * ISPC-2893
     * Ancuta 20.04.2021
     */
    public function patientcalendarAction(){
        
        
    }
    
    
    /**
     * ISPC-2893
     * Ancuta 20.04.2021
     */
    public function fetchcalendareventsAction()
    {
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $groupid = $logininfo->groupid;
        
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $this->view->encid = $_REQUEST['id'];
        $ipid = Pms_CommonData::getIpid($decid);
        $epid = Pms_CommonData::getEpid($ipid);
        
        //$logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $this->logininfo->clientid;
        $userid = $this->logininfo->userid;
        
        $ph = new PatientHealthInsurance();
        $phi = $ph->getPatientHealthInsurance($ipid);
        
        $usersm = new User();
        $users_data_ini = $usersm->get_all_users_shortname();
        
        $ust = new UserSettings();
        $ustarr = $ust->getallUsersSettings();
        
        foreach($ustarr as $ustval)
        {
            if(strlen($ustval['calendar_visit_color']) > 0 && strlen($ustval['calendar_visit_text_color'])){
                $usettings[$ustval['userid']] = $ustval;
            }
        }
        
        //ISPC-2311 - custom color/client for patient course entries
        $clt = new Client();
        $cl_data = $clt->getClientDataByid($clientid);
        
        if($cl_data[0]['patient_course_settings'])
        {
            $patient_course_settings = $cl_data[0]['patient_course_settings'];
        }
        else
        {
            $patient_course_settings = [
                "v_color" 		=> 	"#33CC66",
                "v_text_color"	=>	"#000000",
                "xt_color" 		=> 	"#33CC66",
                "xt_text_color"	=>	"#000000",
                "u_color" 		=> 	"#33CC66",
                "u_text_color"	=>	"#000000",
            ];
        }
        //print_r($patient_course_settings);
        //get doctor/nurse visits
        //get form from verlauf, created to see what`s deleted
        $visits_form_course = Doctrine_Query::create()
        ->select("*, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
        ->from('PatientCourse')
        ->where('ipid = ?', $ipid)
        ->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
        ->andWhere("wrong = 0")
        ->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_doctor_form' OR AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'kvno_nurse_form' OR AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form' OR AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'visit_koordination_form'")
        ->andWhere('source_ipid = ""')
        ->orderBy('course_date ASC');
        $visits_course = $visits_form_course->fetchArray();
        
        $allowed_visits['kvno_doctor_form'][] = '999999999';
        $allowed_visits['kvno_nurse_form'][] = '999999999';
        $allowed_visits['visit_koordination_form'][] = '999999999';
        
        foreach($visits_course as $visit)
        {
            $allowed_visits[$visit['tabname']][] = $visit['recordid'];
        }
        
        $del_cf_course = Doctrine_Query::create()
        ->select("*, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
        ->from('PatientCourse')
        ->where('ipid = ?', $ipid)
        ->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
        ->andWhere("wrong = 1")
        ->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form'")
        ->andWhere('source_ipid = ""')
        ->orderBy('course_date ASC');
        $del_visits_course = $del_cf_course->fetchArray();
        
        $del_cf_ids['contact_form'][] = '999999999';
        foreach($del_visits_course as $k_del_cf => $v_del_cf)
        {
            $del_cf_ids[$v_del_cf['tabname']][] = $v_del_cf['recordid'];
        }
        
        $kvnodoc = new KvnoDoctor();
        $kvnodoctorarray = $kvnodoc->getDoctorVisits($allowed_visits['kvno_doctor_form']);
        
        $kvnonurse = new KvnoNurse();
        $kvnonursearray = $kvnonurse->getNurseVisits($allowed_visits['kvno_nurse_form']);
        
        //8 may 2013 added contactforms to calendar as normal visit
        $contactforms = new ContactForms();
        $contactforms_array = $contactforms->get_pat_calendar_contact_form($ipid, false, $_REQUEST['start'], $_REQUEST['end'], $del_cf_ids['contact_form']);
        
        //30.05.2013 added koordination visits
        $koord_visits = new VisitKoordination();
        $koord_visits_array = $koord_visits->get_koordination_visits($allowed_visits['visit_koordination_form']);
        
        //sapv events
        $verordnetarray = Pms_CommonData::getSapvCheckBox(true);
        $sapvevents = new SapvVerordnung();
        $sapvevents_array = $sapvevents->get_patient_following_sapvs($ipid);
        
        
        //DOCTOR VISITS
        foreach($kvnodoctorarray as $k_doc => $v_doc)
        {
            $visits[$k_doc]['id'] = $v_doc['id'];
            $visits[$k_doc]['start'] = $v_doc['start_date'];
            $visits[$k_doc]['end'] = $v_doc['end_date'];
            $visits[$k_doc]['create_user'] = $v_doc['create_user'];
        }
        
        foreach($kvnodoctorarray as $docvisit)
        {
            $r1start = strtotime($docvisit['start_date']);
            $r1end = strtotime($docvisit['end_date']);
            $u1 = $docvisit['create_user'];
            
            if(array_key_exists($u1, $usettings))
            {
                $calendar_visit_color = '#'.$usettings[$u1]['calendar_visit_color'];
                $calendar_visit_text_color = '#'.$usettings[$u1]['calendar_visit_text_color'];
            }
            else
            {
                $calendar_visit_color = '#36c';
                $calendar_visit_text_color = '#fff';
            }
            
            foreach($visits as $key_vizit => $value_vizit)
            {
                if($value_vizit['id'] != $docvisit['id'])
                {
                    $r2start = strtotime($value_vizit['start']);
                    $r2end = strtotime($value_vizit['end']);
                    $u2 = $value_vizit['create_user'];
                    
                    if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end) && $u1 == $u2)
                    {
                        //overlapped!
                        $intersected[] = $value_vizit['id'];
                        $intersected[] = $docvisit['id'];
                    }
                }
            }
            
            $intersected_doc_viz = array_unique($intersected);
            
            if(in_array($docvisit['id'], $intersected_doc_viz))
            {
                $is_inters = 'intersected_event';
            }
            else
            {
                $is_inters = '';
            }
            
            $user_sh_doc_v[$docvisit['id']] = $users_data_ini[$docvisit['create_user']]['initials'];
            
            $extra_event = array(
                'id' => $docvisit['id'],
                // 					'title' => "Besuch Arzt",
                'title' => $user_sh_doc_v[$docvisit['id']]." - Besuch Arzt",  // added user shortcut - 11-05-2015 :: ISPC 1343
                'start' => $docvisit['start_date'],
                'end' => $docvisit['end_date'],
                'allDay' => false,
                'createDate' => $docvisit['create_date'],
                'eventType' => '1',
                'className' => $is_inters,
                'color' => $calendar_visit_color,
                'textColor' => $calendar_visit_text_color
            );
     
            
            $eventsExtraArray[] = $extra_event;
        }
        
        //SAPVS
        foreach($sapvevents_array as $sapvevent)
        {
            $denied = "0";
            if($sapvevent['status'] == '1'){
                
                if($sapvevent['verorddisabledate'] != '0000-00-00 00:00:00')
                {
                    $sapvevent['verordnungbis'] = $sapvevent['verorddisabledate'] ;
                    $denied = "0";
                }
                else
                {
                    $denied = "1";
                }
            }
            else
            {
                $denied = "0";
                $sapvevent['verordnungbis'] = $sapvevent['verordnungbis'] ;
            }
            
            if($denied == "0"){
                
                $r1start = strtotime($sapvevent['verordnungam']);
                $r1end = strtotime($sapvevent['verordnungbis']);
                $u1 = $sapvevent['create_user'];
                
                $comma = "";
                $ver = "";
                $verordnet = explode(",", $sapvevent['verordnet']);
                for($i = 0; $i < count($verordnet); $i++)
                {
                    $ver .= $comma . strtoupper(substr($verordnetarray[$verordnet[$i]], 0, 2));
                    $comma = ", ";
                }
                
                
                $user_sh_doc_sapv[$sapvevent['id']] = $users_data_ini[$sapvevent['create_user']]['initials'];
                
                $eventsExtraArray[] = array(
                    'id' => $sapvevent['id'],
                    'title' => $user_sh_doc_sapv[$sapvevent['id']]." - Verordnung: ".date("d.m.Y", strtotime($sapvevent['verordnungam']))." - ".date("d.m.Y", strtotime($sapvevent['verordnungbis']))." (".$ver.")",  // added user shortcut - 11-05-2015 :: ISPC 1343
                    'start' => $sapvevent['verordnungam'],
                    'end' => $sapvevent['verordnungbis'],
                    'allDay' => true,
                    'createDate' => $sapvevent['create_date'],
                    'eventType' => '18'
                    //'className' => $is_inters
                );
            }
        }
        
        
        //NURSE VISITS
        foreach($kvnonursearray as $k_nurse => $v_nurse)
        {
            $visits_n[$k_nurse]['id'] = $v_nurse['id'];
            $visits_n[$k_nurse]['start'] = $v_nurse['start_date'];
            $visits_n[$k_nurse]['end'] = $v_nurse['end_date'];
            $visits_n[$k_nurse]['create_user'] = $v_nurse['create_user'];
        }
        
        foreach($kvnonursearray as $nursevisit)
        {
            $r1start = strtotime($nursevisit['start_date']);
            $r1end = strtotime($nursevisit['end_date']);
            
            $u1 = $nursevisit['create_user'];
            
            if(array_key_exists($u1, $usettings))
            {
                $calendar_visit_color = '#'.$usettings[$u1]['calendar_visit_color'];
                $calendar_visit_text_color = '#'.$usettings[$u1]['calendar_visit_text_color'];
            }
            else
            {
                $calendar_visit_color = '#36c';
                $calendar_visit_text_color = '#fff';
            }
            
            foreach($visits_n as $key_vizit_n => $value_vizit_n)
            {
                if($value_vizit_n['id'] != $nursevisit['id'])
                {
                    $r2start = strtotime($value_vizit_n['start']);
                    $r2end = strtotime($value_vizit_n['end']);
                    
                    $u2 = $value_vizit_n['create_user'];
                    
                    if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end) && $u1 == $u2)
                    {
                        //overlapped!
                        $intersected_n[] = $value_vizit_n['id'];
                        $intersected_n[] = $nursevisit['id'];
                    }
                }
            }
            
            $intersected_n_viz = array_unique($intersected_n);
            
            if(in_array($nursevisit['id'], $intersected_n_viz))
            {
                $is_inters_n = 'intersected_event';
            }
            else
            {
                $is_inters_n = '';
            }
            
            $user_sh_nurse_v[$nursevisit['id']] = $users_data_ini[$nursevisit['create_user']]['initials'];
            
            $extra_event = array(
                'id' => $nursevisit['id'],
                // 					'title' => "Besuch Pflege ",
                'title' => $user_sh_nurse_v[$nursevisit['id']]." - Besuch Pflege ",  // added user shortcut - 11-05-2015 :: ISPC 1343
                'start' => $nursevisit['start_date'],
                'end' => $nursevisit['end_date'],
                'allDay' => false,
                'createDate' => $nursevisit['create_date'],
                'eventType' => '2',
                'className' => $is_inters_n,
                'color' => $calendar_visit_color,
                'textColor' => $calendar_visit_text_color
            );
           
            $eventsExtraArray[] = $extra_event;
            
            
        }
        
        //CONTACT FORMS
        foreach($contactforms_array as $k_cf => $v_cf)
        {
            $visits_cf[$k_cf]['id'] = $v_cf['id'];
            $visits_cf[$k_cf]['start'] = $v_cf['start_date'];
            $visits_cf[$k_cf]['end'] = $v_cf['end_date'];
            $visits_cf[$k_cf]['create_user'] = $v_cf['create_user'];
        }
        
        foreach($contactforms_array as $k_contactforms => $v_contactforms)
        {
            $r1start = strtotime($v_contactforms['start_date']);
            $r1end = strtotime($v_contactforms['end_date']);
            $u1 = $v_contactforms['create_user'];
            
            foreach($visits_cf as $key_cf_vizit => $value_cf_vizit)
            {
                if($value_cf_vizit['id'] != $v_contactforms['id'])
                {
                    $r2start = strtotime($value_cf_vizit['start']);
                    $r2end = strtotime($value_cf_vizit['end']);
                    $u2 = $value_cf_vizit['create_user'];
                    
                    if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end) && $u1 == $u2)
                    {
                        //overlapped!
                        $intersected[] = $v_contactforms['id'];
                        $intersected[] = $value_cf_vizit['id'];
                    }
                }
            }
            
            $intersected_cf_viz = array_unique($intersected);
            
            if(in_array($v_contactforms['id'], $intersected_cf_viz))
            {
                $is_inters = 'intersected_event';
            }
            else
            {
                $is_inters = '';
            }
            
            if(strlen($v_contactforms['form_type_name']) == 0)
            {
                $contact_form_name = 'Kontaktformular';
            }
            else
            {
                $contact_form_name = $v_contactforms['form_type_name'];
            }
            
            if(array_key_exists($u1, $usettings))
            {
                $contact_form_color = '#'.$usettings[$u1]['calendar_visit_color'];
                $contact_form_text_color = '#'.$usettings[$u1]['calendar_visit_text_color'];
            }
            else
            {
                if(strlen($v_contactforms['form_type_color']) == 0)
                {
                    $contact_form_color = 'gold';
                }
                else
                {
                    $contact_form_color = '#'.$v_contactforms['form_type_color'];
                }
                if(strlen($v_contactforms['form_type_text_color']) == 0)
                {
                    $contact_form_text_color = "#000000";
                }
                else
                {
                    $contact_form_text_color = '#'.$v_contactforms['form_type_text_color'];
                }
            }
            
            
            $user_sh_cnt[$v_contactforms['id']] = $users_data_ini[$v_contactforms['create_user']]['initials'];
            
            $eventsExtraArray[] = array(
                'id' => $v_contactforms['id'],
                // 					'title' => $contact_form_name,
                'title' => $user_sh_cnt[$v_contactforms['id']]." - ".$contact_form_name,  // added user shortcut - 11-05-2015 :: ISPC 1343
                'start' => $v_contactforms['start_date'],
                'end' => $v_contactforms['end_date'],
                'allDay' => false,
                'resizable' => false,
                'color' => $contact_form_color,
                'textColor' => $contact_form_text_color,
                'ipid' => $v_contactforms['ipid'],
                'createDate' => $v_contactforms['create_date'],
                'eventType' => '13', //doctor vizit
                'className' => $is_inters
            );
        }
        
        //KOORD VISITS
        foreach($koord_visits_array as $k_koord_visit => $v_koord_visit)
        {
            $visits_k[$k_koord_visit]['id'] = $v_koord_visit['id'];
            $visits_k[$k_koord_visit]['start'] = $v_koord_visit['start_date'];
            $visits_k[$k_koord_visit]['end'] = $v_koord_visit['end_date'];
            $visits_k[$k_koord_visit]['create_user'] = $v_koord_visit['create_user'];
        }
        
        foreach($koord_visits_array as $k_koordvisit => $v_koordvisit)
        {
            $r1start = strtotime($v_koordvisit['start_date']);
            $r1end = strtotime($v_koordvisit['end_date']);
            
            $u1 = $v_koordvisit['create_user'];
            
            if(array_key_exists($u1, $usettings))
            {
                $calendar_visit_color = '#'.$usettings[$u1]['calendar_visit_color'];
                $calendar_visit_text_color = '#'.$usettings[$u1]['calendar_visit_text_color'];
            }
            else
            {
                $calendar_visit_color = '#36c';
                $calendar_visit_text_color = '#fff';
            }
            
            foreach($visits_k as $key_vizit_k => $value_vizit_k)
            {
                if($value_vizit_k['id'] != $v_koordvisit['id'])
                {
                    $r2start = strtotime($value_vizit_k['start']);
                    $r2end = strtotime($value_vizit_k['end']);
                    $u2 = $value_vizit_k['create_user'];
                    
                    if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end) && $u1 == $u2)
                    {
                        //overlapped!
                        $intersected[] = $value_vizit_k['id'];
                        $intersected[] = $v_koordvisit['id'];
                    }
                }
            }
            
            $intersected_doc_viz = array_unique($intersected);
            
            if(in_array($v_koordvisit['id'], $intersected_doc_viz))
            {
                $is_inters = 'intersected_event';
            }
            else
            {
                $is_inters = '';
            }
            
            $user_sh_koord_v[$v_koordvisit['id']] = $users_data_ini[$v_koordvisit['create_user']]['initials'];
            
            
            $extra_event = array(
                'id' => $v_koordvisit['id'],
                // 					'title' => "Besuch Koordination",
                'title' => $user_sh_koord_v[$v_koordvisit['id']]." - Besuch Koordination", // added user shortcut - 11-05-2015 :: ISPC 1343
                'start' => $v_koordvisit['start_date'],
                'end' => $v_koordvisit['end_date'],
                'allDay' => false,
                'createDate' => $v_koordvisit['create_date'],
                'eventType' => '17',
                'className' => $is_inters,
                'color' => $calendar_visit_color,
                'textColor' => $calendar_visit_text_color
            );
             
            $eventsExtraArray[] = $extra_event;
        }
        
        $wlprevileges = new Modules();
        $wl = $wlprevileges->checkModulePrivileges("51", $clientid);
        
        // get patient active location
        
        $patloc = Doctrine_Query::create()
        ->select('location_id,ipid')
        ->from('PatientLocation')
        ->where('isdelete= ?',0)
        ->andWhere('ipid = ?', $ipid)
        ->andWhere("valid_till='0000-00-00 00:00:00'")
        ->orderBy('id DESC');
        $patlocid = $patloc->fetchArray();
        $plid = $patlocid[0]['location_id'];
        
        //get hospiz location
        $fdoc = Doctrine_Query::create()
        ->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
        ->from('Locations')
        ->where('id = ?', $plid )
        ->andWhere('isdelete= ?',0)
        ->orderBy('location ASC');
        $locarr = $fdoc->fetchArray();
        
        
        //+6week simple
        //+6week continuous
        //only for non privat patients
        if($wl && $phi[0]['privatepatient'] != 1 && $locarr[0]['location_type'] != 2) // Alsso exclude from active hopsiz location ISPC-2062 02.10.2017 Ancuta
        {
            //WL patient 6week continuously anlage recheck
            //09-07-2013 - change in 8 weeks check
            $pm = new PatientMaster();
            $patientInfo = $pm->getTreatedDaysRealMultiple(array("0" => "" . $ipid . ""), false);
            $patientn = $pm->getMasterData($decid, 0);
            
            if(empty($patientInfo[$ipid]['admissionDates']))
            {
                if($patientInfo[$ipid]['discharge_date'] == date("d.m.Y"))
                {
                    $admissionDate = strtotime($patientInfo[$ipid]['admission_date']);
                    $dischargeDate = strtotime('+ 8 weeks'); //today + 8weeks
                }
                else
                {
                    $admissionDate = strtotime($patientInfo[$ipid]['admission_date']);
                    $dischargeDate = strtotime($patientInfo[$ipid]['discharge_date']);
                }
            }
            else
            {
                $dischargeDates = end($patientInfo[$ipid]['dischargeDates']);
                $admissionDates = end($patientInfo[$ipid]['admissionDates']);
                
                if(count($patientInfo[$ipid]['admissionDates']) > count($patientInfo[$ipid]['dischargeDates']))
                {
                    $admissionDate = strtotime($admissionDates['date']);
                    $dischargeDate = strtotime('+ 8 weeks', strtotime($admissionDates['date']));
                }
                else
                {
                    $admissionDate = strtotime($admissionDates['date']);
                    if(strtotime($dischargeDates['date']) < date('d.m.Y'))
                    {
                        $dischargeDate = strtotime('+ 8 weeks', strtotime($dischargeDates['date']));
                    }
                    else
                    {
                        $dischargeDate = strtotime($dischargeDates['date']);
                    }
                }
                
                if((sizeof($patientInfo[$ipid]['admissionDates']) - 1) == sizeof($patientInfo[$ipid]['dischargeDates']))
                {
                    
                    $dischargeDate = strtotime(' + 1 year'); //set discharge far in the future
                }
                else
                {
                    //					$dischargeDate = strtotime('+ 6 weeks', strtotime($dischargeDates['date']));
                }
            }
            
            if(strtotime(date('d.m.Y', $admissionDate)) <= strtotime(date("d.m.Y")))
            {
                while($admissionDate <= $dischargeDate)
                {
                    
                    $admissionDate = strtotime('+ 8 weeks', $admissionDate);
                    if($admissionDate <= $dischargeDate)
                    {
                        $eventsExtraArray[] = array(
                            'id' => $patientDetails['id'],
                            'title' => "Prüfung Anlage 4",
                            'start' => date("Y-m-d", $admissionDate),
                            'editable' => false,
                            'color' => "red",
                            'textColor' => 'black',
                            'eventType' => "6" //anlage4wl recheck
                        );
                    }
                }
            }
        }
        
        //4week
        
        // exclude private patient or patient with hospiz location
        
        if($wl && $phi[0]['privatepatient'] != 1 && $locarr[0]['location_type'] != 2)
        {
            $pm = new PatientMaster();
            $patientn = $pm->getMasterData($decid, 0);
            $patientInfo = $pm->getTreatedDaysRealMultiple(array("0" => "" . $ipid . ""), false);
            
            if($patientn['vollversorgung'] == '1')
            {
                $vv_date = strtotime($patientn['vollversorgung_date']);
                
                if((sizeof($patientInfo[$ipid]['admissionDates']) - 1) == sizeof($patientInfo[$ipid]['dischargeDates']))
                {
                    
                    $discharge_date = strtotime(' + 1 year'); //set discharge far in the future
                }
                else
                {
                    $discharge_date = strtotime('+5 weeks', strtotime($patientInfo[$ipid]['discharge_date']));
                }
            }
            
            while($vv_date <= $discharge_date)
            {
                $vv_date = strtotime('+ 4 weeks', $vv_date);
                
                if($vv_date <= $discharge_date)
                {
                    $eventsExtraArray[] = array(
                        'id' => $patientDetails['id'],
                        'title' => "Prüfung Anlage 4a",
                        'start' => date("Y-m-d", $vv_date),
                        'editable' => false,
                        'color' => "#999999",
                        'textColor' => 'black',
                        'eventType' => "6" //anlage4wl recheck
                    );
                }
            }
        }
        
        //		end get WL patients
        
        $reassesmentprv = new Modules();
        $reass_mod = $reassesmentprv->checkModulePrivileges("56", $clientid);
        if($reass_mod)
        {
            #################Ancuta Reassesment start
            //		get KVNO Assessment reevaluation
            $kvno = new KvnoAssessment();
            $reevaluation = $kvno->getPatientAssessment($ipid);
            
            foreach($reevaluation as $rekvnoreeval)
            {
                if($rekvnoreeval['iscompleted'] == 1)
                {
                    $eventsExtraArray[] = array(
                        'id' => $rekvnoreeval['id'],
                        'title' => 'Re-Assessment',
                        'start' => date("Y-m-d", strtotime($rekvnoreeval['reeval'])),
                        'editable' => false,
                        'color' => "#008080", //Teal
                        'textColor' => '#fff',
                        'eventType' => "13", //re-assesment
                        'url' => 'patientform/reassessment?id=' . $_GET['id'] . ''
                    );
                }
            }
            #################Ancuta Reassesment end
        }
        
        
        
        
        
        //ISPC-2311 - custom color/client for patient course entries
        //		get verlauf koordination
        $course = new PatientCourse();
        $patientCourse = $course->getCourseDataByShortcut($ipid, "V", false, true);
        
        foreach($patientCourse as $koordination)
        {
            $coordTitle = explode("|", $koordination['course_title']);
            $user_sh_k[$koordination['id']] = $users_data_ini[$koordination['create_user']]['initials'];
            
            $eventsExtraArray[] = array(
                'id' => $koordination['id'],
                'title' => $user_sh_k[$koordination['id']]." - Koordination: \n" . $coordTitle[0] . "minuten",// added user shortcut - 11-05-2015 :: ISPC 1343
                'start' => date("Y-m-d H:i:s", strtotime($coordTitle[2])),
                'color' => $patient_course_settings['v_color'],
                'textColor' => $patient_course_settings['v_text_color'],
                'allDay' => false,
                'eventType' => "9" //koord verlauf entry
            );
        }
        //		end get verlauf koordination
        
        
        
        
        
        //		get verlauf xt
        $patient_course = $course->getCourseDataByShortcut($ipid, "XT", false, true);
        foreach($patient_course as $k_xt => $v_xt)
        {
            $coord_title = explode("|", $v_xt['course_title']);
            
            $user_sh_xt[$v_xt['id']] = $users_data_ini[$v_xt['create_user']]['initials'];
            
            $eventsExtraArray[] = array(
                'id' => $v_xt['id'],
                'title' => $user_sh_xt[$v_xt['id']]." - Telefonat: \n" . $coord_title[0] . "minuten", // added user shortcut - 11-05-2015 :: ISPC 1343
                'start' => date("Y-m-d H:i:s", strtotime($coord_title[2])),
                'color' => $patient_course_settings['xt_color'],
                'textColor' => $patient_course_settings['xt_text_color'],
                'allDay' => false,
                'eventType' => "16" //Telefon verlauf entry
            );
        }
        //		end get verlauf xt
        
        
        
        //		get verlauf U
        $patient_course = $course->getCourseDataByShortcut($ipid, "U", false, true);
        foreach($patient_course as $k_u => $v_u)
        {
            $user_sh_v_u[$v_u['id']] = $users_data_ini[$v_u['create_user']]['initials'];
            $coord_title = explode("|", $v_u['course_title']);
            $eventsExtraArray[] = array(
                'id' => $v_u['id'],
                'title' => $user_sh_v_u[$v_u['id']]." - Beratung: \n" . $coord_title[1] . "minuten", // added user shortcut - 11-05-2015 :: ISPC 1343
                'start' => date("Y-m-d H:i:s", strtotime($coord_title[3])),
                'color' => $patient_course_settings['u_color'],
                'textColor' => $patient_course_settings['u_text_color'],
                'allDay' => false,
                'eventType' => "15" //Beratung verlauf entry
            );
        }
        //		end get verlauf U
        
       
        
        //get custom events
        $docCustomEv = new DoctorCustomEvents();
        $customEvents = $docCustomEv->getDoctorCustomEvents($userid, $clientid, $ipid);
        
        foreach($customEvents as $cEvent)
        {
            
            if($cEvent['allDay'] == 1)
            {
                $allDay = true;
            }
            else
            {
                $allDay = false;
            }
            
            $eventsArray[] = array(
                'id' => $cEvent['id'],
                'title' => $cEvent['eventTitle'],
                'start' => date("Y-m-d H:i:s", strtotime($cEvent['startDate'])),
                'end' => date("Y-m-d H:i:s", strtotime($cEvent['endDate'])),
                'color' => '#33CCFF',
                'textColor' => 'black',
                'allDay' => $allDay,
                'eventType' => $cEvent['eventType'], //custom event
                
                'comments' => $cEvent['comments'], // this are the original so user can edit
                'comments_qtip' => nl2br($this->view->escape($cEvent['comments'])), // this are displayed as qtip
            );
        }
        //		end get custom events
        
        
        
        
        
        
        
        
        
        //Patient birthday
        $pt = Doctrine_Query::create()
        ->select("*")
        ->from('PatientMaster')
        ->whereIn('ipid', $ipid);
        $patient_res = $pt->fetchArray();
        
        if($patient_res)
        {
            foreach($patient_res as $k_pat => $v_pat)
            {
                $patient_birthd = date('d.m.Y', strtotime($v_pat['birthd']));
            }
        }
        
        $start_calendar = date('Y-m-d', $_REQUEST['start']);
        $end_calendar = date('Y-m-d', $_REQUEST['end']);
        
        $pm = new PatientMaster();
        $calendardays_array = $pm->getDaysInBetween($start_calendar, $end_calendar);
        
        
        $start_calendar = date('d.m.Y', $_REQUEST['start']);
        $year_calendar = date('Y', strtotime($start_calendar));
        $patbirthd_arr = explode(".", $patient_birthd);
        
        
        if($patient_birthd)
        {
            foreach($calendardays_array as $k => $day)
            {
                $patient_date = date("Y-m-d", mktime(0, 0, 0, $patbirthd_arr[1], $patbirthd_arr[0], date('Y', strtotime($day))));
                if($patient_date == $day)
                {
                    $start_date = date('Y-m-d', strtotime($day));
                }
            }
            
            $eventsExtraArray[] = array(
                //'id' => $v_u['id'],
                'title' => "Geburtstag des Patienten",
                'editable' => false,
                'start' => $start_date,
                'color' => "#E0B0FF",
                'textColor' => 'black',
                'allDay' => true,
                'eventType' => "19", //Birthday
            );
        }
        //End patient Birthday
        
        
        

        
        
                
        
        
        $sql = 'e.epid, p.ipid, e.ipid,';
        // TODO-1508
        $conditions['periods'][0]['start'] = date('Y-m-d', $_REQUEST['start']);//'2009-01-01';
        $conditions['periods'][0]['end'] = date('Y-m-d');
        $conditions['client'] = $clientid;
        $conditions['include_standby'] = true;
        $conditions['ipids'] = array($ipid);
        $patient_days = Pms_CommonData::patients_days($conditions,$sql);
        
        $treatment_days = $patient_days[$ipid]['treatment_days'];
        $treatment_days_dmy = array_values($treatment_days);
        
        foreach($treatment_days_dmy as $k=>$trday){
            $treatment_days[] = date("Y-m-d",strtotime($trday));
        }
        
        //find if there is a sapv for current period START!
        $dropSapv = Doctrine_Query::create()
        ->select('*')
        ->from('SapvVerordnung')
        ->whereIn('ipid', array($ipid))
        ->andWhere('verordnungam != "0000-00-00 00:00:00"')
        ->andWhere('verordnungbis != "0000-00-00 00:00:00"')
        ->andWhere('isdelete=0')
        ->orderBy('verordnungam ASC');
        $droparray = $dropSapv->fetchArray();
        
        $all_sapv_days = array();
        $temp_sapv_days = array();
        $s=0;
        foreach($droparray as $k_sapv => $v_sapv)
        {
            $r1['start'][$v_sapv['ipid']][$s] = "";
            $r1['end'][$v_sapv['ipid']][$s] = "";
            $r2['start'][$v_sapv['ipid']][$s] = "";
            $r2['end'][$v_sapv['ipid']][$s] = "";
            
            if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') ){
                // no sapv taken here - becouse it is considered to be fully denied
            }
            else
            {
                $r1['start'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungam'])));
                $r1['end'][$v_sapv['ipid']][$s] = strtotime(date('Y-m-d', strtotime($v_sapv['verordnungbis'])));
                
                $r2['start'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[ $v_sapv['ipid'] ]['start']);
                $r2['end'][$v_sapv['ipid']][$s] = strtotime($patients_invoices_periods[$v_sapv['ipid']]['end']);
                
                
                //aditional data from sapv which was added on 16.10.2014
                if($v_sapv['approved_date'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['approved_date'])) != '1970-01-01' && $v_sapv['status'] == '2')
                {
                    $sapv_data[$v_sapv['ipid']][$s]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
                }
                
                if(strlen($v_sapv['approved_number']) > 0  && $v_sapv['status'] != 1){
                    $sapv_data[$v_sapv['ipid']][$s]['approved_number'] = $v_sapv['approved_number'];
                } else{
                    $sapv_data[$v_sapv['ipid']][$s]['approved_number'] = "99999"; // for all sapv-s where the number was not filled
                }
                //ISPC - 2371
                $verordnungend = date("Y-m-d H:i", strtotime($v_sapv['verordnungbis']));
                $sapv_period_days[$s]['verordnungend'] = $verordnungend;
                
                $verordnungstart = date("Y-m-d H:i", strtotime($v_sapv['verordnungam']));
                $sapv_period_days[$s]['verordnungstart'] = $verordnungstart;
                //ISPC - 2371
                if($v_sapv['status'] == 1 && ($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00'  || $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00') )
                {
                    $v_sapv['verordnungbis'] = date("Y-m-d H:i", strtotime($v_sapv['verorddisabledate']));
                }
                
                
                $s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
                $s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
                
                $sapv_period_days[$s]['days'][] = $pm->getDaysInBetween($s_start, $s_end);
                $s++;
                
 
            }
        }

        
        
        
        
                            //ISPC - 2371
                            $sapv_treatment_day = array();
                            foreach($sapv_period_days as $sid => $s_dates ){
                                foreach($s_dates['days'] as $pid => $s_periods ){
                                    foreach($s_periods as $k=>$sday){
                                        if(in_array($sday,$treatment_days)){
                                            $sapv_treatment_day[$sid]['days'][] = $sday;
                                        }
                                    }
                                }
                                $sapv_treatment_day[$sid]['verordnungstart'] = $s_dates['verordnungstart'];
                                $sapv_treatment_day[$sid]['verordnungend'] = $s_dates['verordnungend'];
                            }
                            
                            
                            foreach($sapv_treatment_day as $sid=>$s_dates)
                            {
                                $sapv_treatment_intervals[$sid]['interval'] = Pms_CommonData::days_to_intervals($s_dates['days']);
                                $sapv_treatment_intervals[$sid]['verordnungstart'] = $s_dates['verordnungstart'];
                                $sapv_treatment_intervals[$sid]['verordnungend'] = $s_dates['verordnungend'];
                            }
                    
                            
                            foreach ($sapv_treatment_intervals as $sid => $s_dates) {
                                foreach ($s_dates['interval'] as $day) {
                                    $extra_event = array(
                                        'title' => "SAPV ".date('d.m.Y', strtotime($s_dates['verordnungstart'])).' - '.date('d.m.Y', strtotime($s_dates['verordnungend'])),
                                        'start' => date('Y-m-d', strtotime($day['start'])),
                                        'end' => date('Y-m-d', strtotime($day['end'])),
                                        'allDay' => true,
                    
                                        'eventType' => '20',
                                        'color' => "#E0B0FF",
                                        'textColor' => 'black',
                                    );
                                    $eventsExtraArray[] = $extra_event;
                                }
                            }
      
        
                            
        echo json_encode($eventsArray);
        exit;
    }
    
    
    
    public function savepatienteventsAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        //		all events
        //		0.custom doctor event(edit)
        //		1.docvizit
        //		2.nursevizit
        //		3.todo
        //		4.dutyrooster
        //		5.assessment
        //		6.anlage4wl
        //		7.sapvfb3
        //		8.treatment??
        //		9.verlauf koordination
        //		10, 11, 12(custom events add)
        //		13. contactform
        //		15. verlauf U
        //		16. verlauf XT
        //		17. Visit Koordination
        
        $eventid = $_POST['eventId']; //existing = edit event  / empty = new event
        $eventTitle = $_POST['eventTitle']; //existing = edit event  / empty = new event
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        
        $eventType = $_POST['eventType'];
        $allDayEvent = $_POST['allDay']; //1-true, 0-false
        $cDate = $_POST['cDate'];
        $comments = $_POST['comments'];
        
        switch($eventType):
        case "1": //docvizit
            if($eventid > 0)
            {
                //					2011-09-19 22:16:00 - 2011-09-19 22:31:00
                $startDateArray = explode(" ", $startDate);
                $endDateArray = explode(" ", $endDate);
                
                $startTimeArray = explode(":", $startDateArray[1]);
                $endTimeArray = explode(":", $endDateArray[1]);
                
                $stamq = Doctrine_Core::getTable('KvnoDoctor')->findOneById($eventid);
                // get old values from db
                $old_start_date = $stamq->start_date;
                $old_end_date = $stamq->end_date;
                $old_vizit_date = $stamq->vizit_date;
                $old_kvno_begindate_h = date('H', strtotime($stamq->start_date));
                $old_kvno_begindate_m = date('i', strtotime($stamq->start_date));
                $old_kvno_enddate_h = date('H', strtotime($stamq->end_date));
                $old_kvno_enddate_m = date('i', strtotime($stamq->end_date));
                
                $stamq->kvno_begin_date_h = $startTimeArray[0];
                $stamq->kvno_begin_date_m = $startTimeArray[1];
                $stamq->kvno_end_date_h = $endTimeArray[0];
                $stamq->kvno_end_date_m = $endTimeArray[1];
                $stamq->vizit_date = $startDate;
                /* Visit START DATE and END DATE */
                $stamq->start_date = $startDate;
                $stamq->end_date = $endDate;
                /* ---------------------------- */
                $stamq->save();
                $done_date = date('Y-m-d H:i:s', strtotime($startDate));
                $cust = new PatientCourse();
                $cust->ipid = $ipid; //TO DO: after moving the calendars to navi left get this via post *DONE
                $cust->course_date = date("Y-m-d H:i:s", time());
                $cust->course_type = Pms_CommonData::aesEncrypt("K");
                $cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($done_date)) . " wurde editiert");
                $cust->recordid = $eventid;
                $cust->user_id = $userid;
                $cust->save();
                
                if($startDate != $old_start_date || $endDate != $old_end_date)
                {
                    $old_startdate = $old_kvno_begindate_h . ':' . $old_kvno_begindate_m . ' - ' . $old_kvno_enddate_h . ':' . $old_kvno_enddate_m . ' ' . date('d.m.Y', strtotime($old_start_date));
                    $new_startDate = $startTimeArray[0] . ':' . $startTimeArray[1] . ' - ' . $endTimeArray[0] . ':' . $endTimeArray[1] . ' ' . date('d.m.Y', strtotime($startDate));
                    
                    //edited contact form date verlauf entry
                    $cust = new PatientCourse();
                    $cust->ipid = $ipid;
                    ;
                    $cust->course_date = date("Y-m-d H:i:s", time());
                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
                    $cust->course_title = Pms_CommonData::aesEncrypt("Besuchszeit: " . $old_startdate . ' -> ' . $new_startDate);
                    $cust->user_id = $userid;
                    
                    $cust->done_name = Pms_CommonData::aesEncrypt("kvno_doctor_visit");
                    $cust->done_id = $eventid;
                    $cust->save();
                }
            }
            //				print_r($eventType);
            break;
        case "2"://nursevizit
            if($eventid > 0)
            {
                $startDateArray = explode(" ", $startDate);
                $endDateArray = explode(" ", $endDate);
                
                $startTimeArray = explode(":", $startDateArray[1]);
                $endTimeArray = explode(":", $endDateArray[1]);
                
                $stamq = Doctrine_Core::getTable('KvnoNurse')->findOneById($eventid);
                // get old values from db
                $old_start_date = $stamq->start_date;
                $old_end_date = $stamq->end_date;
                $old_vizit_date = $stamq->vizit_date;
                $old_kvno_begindate_h = date('H', strtotime($stamq->start_date));
                $old_kvno_begindate_m = date('i', strtotime($stamq->start_date));
                $old_kvno_enddate_h = date('H', strtotime($stamq->end_date));
                $old_kvno_enddate_m = date('i', strtotime($stamq->end_date));
                
                $stamq->kvno_begin_date_h = $startTimeArray[0];
                $stamq->kvno_begin_date_m = $startTimeArray[1];
                $stamq->kvno_end_date_h = $endTimeArray[0];
                $stamq->kvno_end_date_m = $endTimeArray[1];
                $stamq->vizit_date = $startDate;
                /* Visit START DATE and END DATE */
                $stamq->start_date = $startDate;
                $stamq->end_date = $endDate;
                /* ---------------------------- */
                $stamq->save();
                $done_date = date('Y-m-d H:i:s', strtotime($startDate));
                $cust = new PatientCourse();
                $cust->ipid = $ipid; //TO DO: after moving the calendars to navi left get this via post
                $cust->course_date = date("Y-m-d H:i:s", time());
                $cust->course_type = Pms_CommonData::aesEncrypt("K");
                $cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($done_date)) . " wurde editiert");
                
                $cust->recordid = $eventid;
                $cust->user_id = $userid;
                $cust->save();
                
                if($startDate != $old_start_date || $endDate != $old_end_date)
                {
                    $old_startdate = $old_kvno_begindate_h . ':' . $old_kvno_begindate_m . ' - ' . $old_kvno_enddate_h . ':' . $old_kvno_enddate_m . ' ' . date('d.m.Y', strtotime($old_start_date));
                    $new_startDate = $startTimeArray[0] . ':' . $startTimeArray[1] . ' - ' . $endTimeArray[0] . ':' . $endTimeArray[1] . ' ' . date('d.m.Y', strtotime($startDate));
                    
                    //edited contact form date verlauf entry
                    $cust = new PatientCourse();
                    $cust->ipid = $ipid;
                    $cust->course_date = date("Y-m-d H:i:s", time());
                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
                    $cust->course_title = Pms_CommonData::aesEncrypt("Besuchszeit: " . $old_startdate . ' -> ' . $new_startDate);
                    $cust->user_id = $userid;
                    
                    $cust->done_name = Pms_CommonData::aesEncrypt("kvno_nurse_visit");
                    $cust->done_id = $eventid;
                    $cust->save();
                }
            }
            break;
        case "5": //KVNO Reevaluation
            if($eventid > 6)
            {
                //					2011-09-19 22:16:00 - 2011-09-19 22:31:00
                $stamq = Doctrine_Core::getTable('KvnoAssessment')->findOneById($eventid);
                $stamq->reeval = $startDate;
                $stamq->save();
                
                //verlauf
            }
            break;
        case "9": // Koord Verlauf
            if($eventid > 0)
            {
                if(strlen($startDate) == "19") {
                    $startDate = $startDate;
                } else {
                    $startDate = $endDate;
                }
                
                $qpa1 = Doctrine_Query::create()
                ->select("*,
						AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
						->from('PatientCourse')
						->where("id= ?", $eventid);
						$qp1 = $qpa1->fetchArray();
						
						if($qp1)
						{
						    $rem = explode("|", $qp1[0]['course_title']);
						    if(count($rem == 3))
						    {
						        $newCourseTitle = $rem[0] . " | " . $rem[1] . " | " . date("d.m.Y H:i", strtotime($startDate));
						    }
						}
						
						$stamq = Doctrine_Core::getTable('PatientCourse')->findOneById($eventid);
						$stamq->course_title = Pms_CommonData::aesEncrypt($newCourseTitle);
						$stamq->done_date = date("Y-m-d H:i:s", strtotime($startDate));
						$stamq->save();
            }
            break;
        case "10":
            if($eventid > 0)
            {
                if ($stamq = Doctrine_Core::getTable('DoctorCustomEvents')->findOneById($eventid)) {
                    $stamq->eventTitle = $eventTitle;
                    $stamq->startDate = $startDate;
                    $stamq->endDate = $endDate;
                    $stamq->allDay = $allDayEvent;
                    $stamq->comments = $comments;
                    
                    $stamq->save();
                }
            }
            break;
        case "14":
            if(empty($eventid) || $eventid == 0)
            { //add as new
                $docEvent = new DoctorCustomEvents();
                $docEvent->userid = 0;
                $docEvent->clientid = $clientid;
                $docEvent->ipid = $ipid;
                $docEvent->eventTitle = $eventTitle;
                $docEvent->startDate = $startDate;
                $docEvent->endDate = $endDate;
                $docEvent->eventType = $eventType;
                $docEvent->allDay = $allDayEvent;
                $docEvent->comments = $comments;
                $docEvent->save();
            }
            else
            {
                if ($stamq = Doctrine_Core::getTable('DoctorCustomEvents')->findOneById($eventid)) {
                    $stamq->eventTitle = $eventTitle;
                    $stamq->startDate = $startDate;
                    $stamq->endDate = $endDate;
                    $stamq->allDay = $allDayEvent;
                    $stamq->comments = $comments;
                    $stamq->save();
                }
            }
            break;
        case "13": //contactforms
            if($eventid > 0)
            {
                //						2011-09-19 22:16:00 - 2011-09-19 22:31:00
                $startDateArray = explode(" ", $startDate);
                $endDateArray = explode(" ", $endDate);
                
                $startTimeArray = explode(":", $startDateArray[1]);
                $endTimeArray = explode(":", $endDateArray[1]);
                
                $stamq = Doctrine_Core::getTable('ContactForms')->findOneById($eventid);
                //get old values from contact form
                $old_start_date = $stamq->start_date;
                $old_end_date = $stamq->end_date;
                $old_billable_date = $stamq->billable_date;
                $old_begindate_h = $stamq->begin_date_h;
                $old_begindate_m = $stamq->begin_date_m;
                $old_enddate_h = $stamq->end_date_h;
                $old_enddate_m = $stamq->end_date_m;
                
                $stamq->begin_date_h = $startTimeArray[0];
                $stamq->begin_date_m = $startTimeArray[1];
                $stamq->end_date_h = $endTimeArray[0];
                $stamq->end_date_m = $endTimeArray[1];
                $stamq->date = $startDate;
                /* Visit START DATE and END DATE */
                $stamq->start_date = $startDate;
                $stamq->end_date = $endDate;
                // ISPC 2019
                if($old_billable_date == $old_start_date ){
                    $stamq->billable_date = $startDate;
                }elseif($old_billable_date == $old_end_date ){
                    $stamq->billable_date = $endDate;
                }
                
                /* ---------------------------- */
                $stamq->save();
                
                $update_old_link = Doctrine_Query::create()
                ->update('PatientCourse')
                ->set('tabname','?',Pms_CommonData::aesEncrypt("contact_form_no_link") )
                ->where('ipid LIKE ?',$ipid )
                ->andWhere('tabname= ?',Pms_CommonData::aesEncrypt("contact_form"))
                ->andWhere('recordid  = ?', $eventid)
                ->andWhere('source_ipid = ""');
                $update_old_link->execute();
                
                $done_date = $startDate;
                $cust = new PatientCourse();
                $cust->ipid = $ipid;
                $cust->course_date = date("Y-m-d H:i:s", time());
                $cust->course_type = Pms_CommonData::aesEncrypt("K");
                $cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($old_start_date)) . " wurde editiert");
                $cust->tabname = Pms_CommonData::aesEncrypt("contact_form");
                $cust->recordid = $eventid;
                $cust->done_date = $done_date;
                $cust->user_id = $userid;
                $cust->save();
                
                if($startDate != $old_start_date)
                {
                    //$done_date = date('Y-m-d H:i:s', strtotime($startDate . ' ' . $startTimeArray[0] . ':' . $startTimeArray[1] . ':00'));
                    $done_date = $startDate;
                    $old_startdate = date('d.m.Y', strtotime($old_start_date));
                    $new_startDate = date('d.m.Y', strtotime($startDate));
                    //edited contact form date verlauf entry
                    $cust = new PatientCourse();
                    $cust->ipid = $ipid;
                    $cust->course_date = date("Y-m-d H:i:s", time());
                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
                    $cust->course_title = Pms_CommonData::aesEncrypt('Datum: ' . $old_startdate . ' --> ' . $new_startDate);
                    $cust->user_id = $userid;
                    $cust->done_date = $done_date;
                    $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                    $cust->done_id = $eventid;
                    $cust->save();
                }
                if($startTimeArray[0] != $old_begindate_h || $startTimeArray[1] != $old_begindate_m)
                {
                    $old_start_hm = $old_begindate_h . ':' . $old_begindate_m;
                    $start_hm = $startTimeArray[0] . ':' . $startTimeArray[1];
                    
                    //$done_date = date('Y-m-d H:i:s', strtotime($startDate . ' ' . $startTimeArray[0] . ':' . $startTimeArray[1] . ':00'));
                    $done_date = $startDate;
                    //edited contact form date verlauf entry
                    $cust = new PatientCourse();
                    $cust->ipid = $ipid;
                    $cust->course_date = date("Y-m-d H:i:s", time());
                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
                    $cust->course_title = Pms_CommonData::aesEncrypt('Beginn: ' . $old_start_hm . ' --> ' . $start_hm);
                    $cust->user_id = $userid;
                    $cust->done_date = $done_date;
                    $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                    $cust->done_id = $eventid;
                    $cust->save();
                }
                if($endTimeArray[0] != $old_enddate_h || $endTimeArray[1] != $old_enddate_m)
                {
                    $old_end_hm = $old_enddate_h . ':' . $old_enddate_m;
                    $end_hm = $endTimeArray[0] . ':' . $endTimeArray[1];
                    
                    //$done_date = date('Y-m-d H:i:s', strtotime($startDate . ' ' . $startTimeArray[0] . ':' . $startTimeArray[1] . ':00'));
                    $done_date = $startDate;
                    //edited contact form date verlauf entry
                    $cust = new PatientCourse();
                    $cust->ipid = $ipid;
                    $cust->course_date = date("Y-m-d H:i:s", time());
                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
                    $cust->course_title = Pms_CommonData::aesEncrypt('Ende: ' . $old_end_hm . ' --> ' . $end_hm);
                    $cust->user_id = $userid;
                    $cust->done_date = $done_date;
                    $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                    $cust->done_id = $eventid;
                    $cust->save();
                }
            }
            //				print_r($eventType);
            break;
        case "15": // U Verlauf
            if($eventid > 0)
            {
                $qpa1 = Doctrine_Query::create()
                ->select("*,
							AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
							AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
							->from('PatientCourse')
							->where("id= ?", $eventid)
							->andWhere('source_ipid = ""');
							$qp1 = $qpa1->fetchArray();
							
							if($qp1)
							{
							    $rem = explode("|", $qp1[0]['course_title']);
							    
							    if(count($rem == 4))
							    {
							        $newCourseTitle = $rem[0] . "|" . $rem[1] . "|" . $rem[2] . "|" . date("d.m.Y H:i", strtotime($startDate));
							    }
							}
							
							$stamq = Doctrine_Core::getTable('PatientCourse')->findOneById($eventid);
							$stamq->course_title = Pms_CommonData::aesEncrypt($newCourseTitle);
							$stamq->done_date = date("Y-m-d H:i:s", strtotime($startDate));
							$stamq->save();
            }
            break;
        case "16": // XT Verlauf
            if($eventid > 0)
            {
                $qpa1 = Doctrine_Query::create()
                ->select("*,
							AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
							AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
							->from('PatientCourse')
							->where("id= ?", $eventid)
							->andWhere('source_ipid = ""');
							$qp1 = $qpa1->fetchArray();
							
							if($qp1)
							{
							    $rem = explode("|", $qp1[0]['course_title']);
							    
							    if(count($rem == 3))
							    {
							        $newCourseTitle = $rem[0] . "|" . $rem[1] . "|" . date("d.m.Y H:i", strtotime($startDate));
							    }
							}
							
							$stamq = Doctrine_Core::getTable('PatientCourse')->findOneById($eventid);
							$stamq->course_title = Pms_CommonData::aesEncrypt($newCourseTitle);
							$stamq->done_date = date("Y-m-d H:i:s", strtotime($startDate));
							$stamq->save();
            }
            break;
        case "17": //koordination visit
            if($eventid > 0)
            {
                //					2011-09-19 22:16:00 - 2011-09-19 22:31:00
                $startDateArray = explode(" ", $startDate);
                $endDateArray = explode(" ", $endDate);
                
                $startTimeArray = explode(":", $startDateArray[1]);
                $endTimeArray = explode(":", $endDateArray[1]);
                
                $stamq = Doctrine_Core::getTable('VisitKoordination')->findOneById($eventid);
                
                $stamq->visit_begin_date_h = $startTimeArray[0];
                $stamq->visit_begin_date_m = $startTimeArray[1];
                $stamq->visit_end_date_h = $endTimeArray[0];
                $stamq->visit_end_date_m = $endTimeArray[1];
                $stamq->visit_date = $startDate;
                /* Visit START DATE and END DATE */
                $stamq->start_date = $startDate;
                $stamq->end_date = $endDate;
                /* ---------------------------- */
                $stamq->save();
                
                $cust = new PatientCourse();
                $cust->ipid = $ipid; //TO DO: after moving the calendars to navi left get this via post *DONE
                $cust->course_date = date("Y-m-d H:i:s", time());
                $cust->course_type = Pms_CommonData::aesEncrypt("K");
                $cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($cDate)) . " wurde editiert");
                $cust->recordid = $eventid;
                $cust->user_id = $userid;
                $cust->save();
            }
            break;
        default:
            break;
            endswitch;
            exit;
    }
    
    public function deleventsAction()
    {
        $eventid = $_POST['eventId'];
        $eventType = $_POST['eventType'];
        $calendar = $_REQUEST['calendar'];
        switch($eventType)
        {
            case "10":
            case "11":
            case "12":
            case "13":
            case "14":
            case "15":
            case "16":
            case "17":
            case "20":
            case "21":
            case "22":
                if($eventid > 0 && is_numeric($eventid))
                {
                    if($calendar == "doc")
                    {
                        $del = Doctrine_Query::create()
                        ->delete('DoctorCustomEvents dce')
                        ->where('dce.id = ?', $eventid);
                        $rows = $del->execute();
                    }
                    else if($calendar == "team")
                    {
                        $del = Doctrine_Query::create()
                        ->delete('TeamCustomEvents tce')
                        ->where('tce.id = ?', $eventid);
                        $rows = $del->execute();
                    }
                }
                break;
        }
        
        //			echo $del->getSqlQuery();
        exit;
    }
    
    
    
    

    public function specialcareAction(){
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $groupid = $logininfo->groupid;
        
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $this->view->encid = $_REQUEST['id'];
        $ipid = Pms_CommonData::getIpid($decid);
        $epid = Pms_CommonData::getEpid($ipid);
        
        
        
        $isdicharged = PatientDischarge::isDischarged($decid);
        $this->view->isdischarged = 0;
        if($isdicharged)
        {
            $this->view->isdischarged = 1;
        }
        
        
        $p_share_obj = new PatientsShare();
        $p_share_info= $p_share_obj->get_connection_by_ipid($ipid);
        $allIpids [] = $ipid;
        $client_ids[$ipid] = $clientid;
        $all_cl_ids = array();
        $all_cl_ids[] = $clientid;
        if (!empty($p_share_info)) {
            foreach ($p_share_info as $link) {
                if ($link['source'] == $ipid && $link['target'] != $ipid) {
                    $allIpids [] = $link['target'];
                    $client_ids[$link['target']] = $link['target_client'];
                    $all_cl_ids[] = $link['target_client'];
                } elseif ($link['target'] == $ipid && $link['source'] != $ipid) {
                    $allIpids [] = $link['source'];
                    $client_ids[$link['source']] = $link['source_client'];
                    $all_cl_ids[] = $link['source_client'];
                }
            }
        }
		$patient = Doctrine_Query::create()
		->select("p.*,e.*")
		->from('PatientMaster p');
		$patient->WhereIn('p.ipid',$allIpids);
		$patient->andWhere('p.isdelete = 0 ');
		$patient->leftJoin("p.EpidIpidMapping e");
		$patient_arr = $patient->fetchArray();
		
		$ipid_info = array();
		foreach($patient_arr as $kp => $pdata){
		    $ipid_info[$pdata['ipid']]['clientid'] = $pdata['EpidIpidMapping']['clientid'];
		    $ipid_info[$pdata['ipid']]['enc_id'] = Pms_Uuid::encrypt($pdata['id']);;
		}
        

		/*##################################*/
		/* FIRST TAB: Wund-Dokumentation   */
		/*##################################*/
		
        $wund_obj =  new WoundDocumentation();
        $all_forms = $wund_obj->get_multiple_patients_wound_documentations($allIpids);
        
        
        $saved_forms = array();
        $record_ids = array();
        foreach($all_forms as $k=>$fdata){
            $saved_forms[$fdata['id']] = $fdata;
            $record_ids[] = $fdata['id'];
        }

        
        
        $patient_files = Doctrine_Query::create()
        ->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
		AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
		AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
		->from('PatientFileUpload')
		->whereIn('ipid', $allIpids)
		->andwhereIn('recordid', $record_ids);
		$patient_files->andWhere('tabname in ("wounddocumentation_incr","wounddocumentation_uploaded_img")');
		$filearray = $patient_files->fetchArray();
		
		$record_files = array();
		foreach($filearray as $pk=>$upf){
		    $record_files[$upf['recordid']][] = $upf;
		}
					
// 		dd($filearray);
		
        $users_ids  =array();
		foreach($all_forms as $kf=>$wforms){
		    if(!empty($record_files[$wforms['id']])){
		    
    		    $users_ids[] = $wforms['create_user'];
    		    $all_forms[$kf]['clientid']= $client_ids[$wforms['ipid']];
    		    
    		    foreach($record_files[$wforms['id']] as $k=>$pf){
    		          $users_ids[] = $wforms['create_user'];
    		          if($pf['ipid'] == $wforms['ipid'] && $wforms['id'] == $pf['recordid'] ){
    		              $all_forms[$kf]['files'][] = $pf;
    		        }
    		    }
    		    
    		    usort($all_forms[$kf]['files'], array(new Pms_Sorter('create_date'), "_date_compare"));
    		    $last_pdf = end($all_forms[$kf]['files']);
    		    
    		    $all_forms[$kf]['enc_id'] = $ipid_info[$wforms['ipid']]['enc_id'];
    		    $all_forms[$kf]['pdf'] = $last_pdf;
    		    $all_forms[$kf]['pdf_link'] = APP_BASE.'stats/patientclientfileupload?doc_id='.$last_pdf['id'].'&clientid='.$ipid_info[$last_pdf['ipid']]['clientid'].'&id='.$ipid_info[$last_pdf['ipid']]['enc_id'];
		    } else{
		        unset($all_forms[$kf]);
		        
		    }
		}
		
// 		echo "<pre/>";
// 		print_R($all_forms); exit;
		
		$users_data = Pms_CommonData::getUsersData($users_ids);
		$this->view->users_data = $users_data;
		
		$this->view->all_forms = $all_forms;
		
        /*##################################*/
        /* SECOND TAB: Zu- und Ableitungen  */
        /*##################################*/
        
        $patientmaster = new PatientMaster();
        
        //artificial entries/exists
        $ae_entries = Doctrine_Query::create()
        ->select("*")
        ->from('ArtificialEntriesExitsList')
        ->whereIn('clientid',$all_cl_ids)
        ->fetchArray();
        
        $art_ext_details = array();
        foreach($ae_entries as $k=>$c_ae){
            $art_ext_details[$c_ae['id']] = $c_ae;
        }
        
        $patientsart = Doctrine_Query::create()
        ->select("*")
        ->from('PatientArtificialEntriesExits')
        ->whereIn('ipid', $allIpids)
        ->andWhere('isremove  = "0"')
        ->fetchArray();
        $artificial_items = array();
        
        $l=0;
        $patient_artificial = array();
        $patient_artificial_entry[0]['name'] = "Name";
        $patient_artificial_entry[0]['date'] = "Datum";
        $patient_artificial_entry[0]['loca'] = "Lokalisation";
        $patient_artificial_entry[0]['duration'] = "Zeit";
        $patient_artificial_exit[0]['name'] = "Name";
        $patient_artificial_exit[0]['date'] = "Datum";
        $patient_artificial_exit[0]['loca'] = "Lokalisation";
        $patient_artificial_exit[0]['duration'] = "Zeit";
        $l++;
        foreach($patientsart as $k=>$p_art){
            $patientsart[$k]['master_Details'] = $art_ext_details[$p_art['option_id']];

            if($art_ext_details[$p_art['option_id']]['type'] == 'entry'){
                
                $patient_artificial_entry[$l]['option_name'] = $art_ext_details[$p_art['option_id']]['name'];
                $patient_artificial_entry[$l]['option_date'] = date('d.m.Y H:i',strtotime($p_art['option_date']));
                if($art_ext_details[$p_art['option_id']]['localization_available'] == 'yes'){
                    $patient_artificial_entry[$l]['option_localization'] = $p_art['option_localization'];
                }else{
                    $patient_artificial_entry[$l]['option_localization'] = "";
                }
                if($art_ext_details[$p_art['option_id']]['days_availability'] != 0 ){
                    // calculate days between start and toda- if bigger show !
                    $active_days = $patientmaster->getDaysInBetween(date('Y-m-d',strtotime($p_art['option_date'])), date('Y-m-d'));
                    $overflow ="";
                    if(count($active_days) > $art_ext_details[$p_art['option_id']]['days_availability'] ){
                        $overflow = "<span style='color: red;'>(!)</span>";
                    }
                    $nr_Days = count($active_days);
                    $nr_days_label= " Tage";
                    if($nr_Days == 1){
                        $nr_days_label= " Tag";
                    }
                    $patient_artificial_entry[$l]['option_duration'] = $overflow.' '.$nr_Days.$nr_days_label;
                } else{
                    $patient_artificial_entry[$l]['option_duration'] = "";
                }
                
            }else if($art_ext_details[$p_art['option_id']]['type'] == 'exit'){
                
                $patient_artificial_exit[$l]['option_name'] = $art_ext_details[$p_art['option_id']]['name'];
                $patient_artificial_exit[$l]['option_date'] = date('d.m.Y H:i',strtotime($p_art['option_date']));
                if($art_ext_details[$p_art['option_id']]['localization_available'] == 'yes'){
                    $patient_artificial_exit[$l]['option_localization'] = $p_art['option_localization'];
                } else{
                    $patient_artificial_exit[$l]['option_localization'] = "";
                }
                if($art_ext_details[$p_art['option_id']]['days_availability'] != 0 ){
                    // calculate days between start and toda- if bigger show !
                    $active_days = $patientmaster->getDaysInBetween(date('Y-m-d',strtotime($p_art['option_date'])), date('Y-m-d'));
                    $overflow ="";
                    if(count($active_days) > $art_ext_details[$p_art['option_id']]['days_availability'] ){
                        $overflow = "<span class='red'>!</span>";
                    }
                    $patient_artificial_exit[$l]['option_duration'] = $overflow.count($active_days) ;
                } else{
                    
                    $patient_artificial_exit[$l]['option_duration'] = '' ;
                }
            }
            $l++;
        }
        
        if(!empty($patient_artificial_entry)){
            $table_html = '<h3 style="color: #000">'.$this->view->translate("efa artificial entries").'</h3>'; 
            $table_html .= $this->view->tabulate($patient_artificial_entry,array("class"=>"datatable",'id'=>'aids','escaped'=>false));
        }
        
        if(!empty($patient_artificial_exit)){
            $table_exit_html = '<br/><h3 style="color: #000">'.$this->view->translate("efa artificial exits").'</h3>'; 
            $table_exit_html .= $this->view->tabulate($patient_artificial_exit,array("class"=>"datatable",'id'=>'aids','escaped'=>false));
        }
        
        $this->view->patient_artificial_in = $table_html;
        $this->view->patient_artificial_outs = $table_exit_html;
    }
    
    /**
     * ISPC-2892 Ancuta 27.04.2021
     */
    public function aidsuppliersAction(){
        
        setlocale(LC_ALL, 'de_DE.UTF8');
        $step = null;
        
        if ($this->getRequest()->isPost()) {
            $step = $this->getRequest()->getPost('step', null);
        }
        if (is_null($step)) {
            $step = $this->getRequest()->getParam('step');
        }
   
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $isdicharged = PatientDischarge::isDischarged($decid);
        $this->view->isdischarged = 0;
        if($isdicharged)
        {
            $this->view->isdischarged = 1;
        }
        
        /* ================FILES  ======================== */
        $pft = new PatientFileTags();
        $efa_nursing_files_tags = $pft->get_tabname_tagids('efa_nursing_files');
        
        $efa_nursing_files_ids = array();
        $efa_nursing_files_tags_ids = array();
        if(!empty($efa_nursing_files_tags)){
            foreach($efa_nursing_files_tags as $k=>$tg_data){
                $efa_nursing_files_tags_ids[] = $tg_data['id'];
            }
        }
        
        if(!empty($efa_nursing_files_tags_ids)){
            $res = Doctrine_Query::create()
            ->select('*')
            ->from('PatientFile2tags')
            ->whereIn('tag', $efa_nursing_files_tags_ids)
            ->andWhere('isdelete = "0"');
            $res_array = $res->fetchArray();
            
            if(!empty($res_array)){
                foreach($res_array as $kf=>$tg_files){
                    $efa_nursing_files_ids[]  = $tg_files['file'];
                }
            }
        }
        
        $patient_files = Doctrine_Query::create()
        ->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
    						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
    						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
    						->from('PatientFileUpload')
    						->where("ipid=?", $ipid )
    						->andwhere('isdeleted = 0')
    						->orderBy("create_date DESC");
    						$contact_form_files = $patient_files->fetchArray();
 
    						$files_dates = array();
    						
    						$files = array();
    						foreach($contact_form_files as $k_file => $v_file)
    						{
    						    $files[]  = $v_file['id'];
    						    
    						    $users_ids[] = $v_file['create_user'];
    						    $files_dates[] = $v_file['create_date'];
    						    
    						    if($v_file['tabname'] == 'efa_forms_files'){
    						        $form_files[] =  $v_file;
    						    }
    						    if(in_array($v_file['id'],$efa_nursing_files_ids)){
    						        $nursing_files[] =  $v_file;
    						    }
    						}
    						
    						$this->view->users_data = Pms_CommonData::getUsersData($users_ids);
    						$this->view->form_files = $form_files;
    						$this->view->nursing_files = $nursing_files;
    						
    						/*  ...  */
        
        
        switch ($step) {
            
            case "show_aids" :
                $this->_fetch_aids();
                break;
            case "nursing_files" :
                $this->_nursing_files();
                break;
                
            case "show_suppliers" :
                $this->_fetch_suppliers();
                break;
                
                
                
            case "form_files" :
                $this->_form_files();
                break;
 
            case "save_forms_files" :
                $param=  array();
                $param['tag']=  "efa_forms_files";
                $param['tabname']=  "efa_forms_files";
                $param['step']=  "form_files";
                $this->_save_form_files($param);
                break;
                
            case "save_nursing_files" :
                $param=  array();
                $param['tag']=  "Verordnung häusl. Krankenpflege";
                $param['tabname']=  "efa_nursing_files";
                $param['step']=  "nursing_files";
                $this->_save_form_files($param);
                break;
 
                
            default:
                break;
        }
        
    }
    
    /**
     * ISPC-2892 Ancuta 27.04.2021
     */
    private function _fetch_aids(){
        $this->_helper->layout->setLayout('layout_ajax');

        // get from connected patients and aids from connected clients 
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $saved_pa = PatientAidsTable::getInstance()->findByIpid($ipid, Doctrine_Core::HYDRATE_ARRAY);
        
        $used_aids = array();
        if(!empty($saved_pa)){
            $d = 0;
            $used_aids[$d][] = $this->translate('Patient aids with Ja option');
            $d++;
            foreach($saved_pa as $k=>$pa){
                if($pa['extraaid']['needed'] == "Ja"){
                    $used_aids[$d][] = $pa['aid'];
                    $d++;
                }
            }
        }
 
        if(!empty($used_aids)){
            
            $table_html = $this->view->tabulate($used_aids,array("class"=>"datatable",'id'=>'aids','escaped'=>false));
            $print_html .= $table_html;
        } else{
            $print_html = '<span>'.$this->view->translate('no patient aid data  with Ja option').'</span>';
        }
        
            echo $print_html;
        
        exit;
    }

    
    
    /**
     * ISPC-2892 Ancuta 27.04.2021
     */
    private function _nursing_files(){
        $this->_helper->layout->setLayout('layout_ajax');
        exit;
    }
    /**
     * ISPC-2892 Ancuta 27.04.2021
     */
    private function _form_files(){
        $this->_helper->layout->setLayout('layout_ajax');
        exit;
    }

    
    
    /**
     * ISPC-2892 Ancuta 27.04.2021
     */
    private function _save_form_files($param=array()){
        $this->_helper->layout->setLayout('layout_ajax');
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        
        $user2 = new User();
        $userarray = $user2->getUserDetails($userid);

        
        if($this->getRequest()->isPost())
        {
            //patientfileupload
            $action_name = "upload_patient_files{$decid}";
            
            $qquuid = $this->getRequest()->getPost('qquuid');
            $qquuid_title = $this->getRequest()->getPost('qquuid_title');
            $qquuid_tags= $this->getRequest()->getPost('qquuid_file2tag'); //ISPC-2642 Ancuta 10-11.08.2020
            
            if (is_array($qquuid) && ! empty($qquuid) && ($last_uploaded_files = $this->get_last_uploaded_file($action_name, $qquuid, $clientid))) {
                
                
                $upload_form = new Application_Form_PatientFileUpload();
                foreach ($qquuid as $k=>$qquuidID) {
                    
                    if (($last_uploaded_file = $last_uploaded_files[$qquuidID]) && $last_uploaded_file['isZipped'] == 1) {
                        
                        $file_name = pathinfo($last_uploaded_file['filepath'], PATHINFO_FILENAME) . "/" . $last_uploaded_file['fileInfo']['name'];
                        $file_type = strtoupper(pathinfo($last_uploaded_file['filename'], PATHINFO_EXTENSION));
                        if($this->getRequest()->getPost('active_version') != 0) //ISPC - 2129
                        {
                            $post = [
                                'ipid'      => $ipid,
                                'clientid'  => $clientid,
                                'title'     => ! empty($qquuid_title[$k]) ? $qquuid_title[$k] : $last_uploaded_file['filename'] ,
                                'filetype'  => $file_type,
                                'file_name' => $file_name,
                                'zipname'   => $last_uploaded_file['filepath'], //filepath
                                'pat_files_tags_rights' => $userarray[0]['patient_file_tag_rights'],
                                'tag_name'   => isset($param['tag']) && !empty($param['tag']) ? $param['tag']: (!empty($qquuid_tags[$k]) ? $qquuid_tags[$k] :  $this->getRequest()->getPost('tag_name')),
                                'active_version' => $this->getRequest()->getPost('active_version'),
                            ];
                            
                        }
                        else
                        {
                            $post = [
                                'ipid'      => $ipid,
                                'clientid'  => $clientid,
                                'title'     => ! empty($qquuid_title[$k]) ? $qquuid_title[$k] : $last_uploaded_file['filename'] ,
                                'filetype'  => $file_type,
                                'file_name' => $file_name,
                                'zipname'   => $last_uploaded_file['filepath'], //filepath
                                'pat_files_tags_rights' => $userarray[0]['patient_file_tag_rights'],
                                'tag_name'   => isset($param['tag']) && !empty($param['tag']) ? $param['tag'] : (!empty($qquuid_tags[$k]) ? $qquuid_tags[$k] :  $this->getRequest()->getPost('tag_name')),
                                'active_version' => '0',
                                
                            ];
                            
                        }
                        
                        $post['tabname'] = $param['tabname'];
                        $rec = $upload_form->insertData($post);
                        
                        $this->delete_last_uploaded_file($action_name, $qquuidID, $clientid);
                    }
                    
                }
            }
            
            //remove session stuff
            $_SESSION['filename'] = '';
            $_SESSION['filetype'] = '';
            $_SESSION['filetitle'] = '';
            unset($_SESSION['filename']);
            unset($_SESSION['filetype']);
            unset($_SESSION['filetitle']);
            //. patientfileupload

            $this->_redirect(APP_BASE . "efa/aidsuppliers?id=" . $_REQUEST['id']."&tab=".$param['step']);
            
            exit;
        }
        
    }
    
    /**
     * ISPC-2892 Ancuta 27.04.2021
     */
    private function _fetch_suppliers(){
        $this->_helper->layout->setLayout('layout_ajax');

        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $vv = new ClinicVersorger();
        $vdata = $vv->getPatientData($ipid);
        $html = $vv->renderreportextract($vdata);
        
        echo $html;
        exit;
    }
    
    /**
     * ISPC-2894 Ancuta 19.05.2021
     */
    public function users2patientAction(){
        
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $epid = Pms_CommonData::getEpidFromId($decid);
        
        
        $qparr = Doctrine_Query::create()
        ->select('*')
        ->from('PatientQpaMapping')
        ->where('epid= ?', $epid )
        ->orderBy('assign_date ASC')
        ->fetchArray();
        
        
        foreach($qparr as $k=> $user2epid){
            $users_ids[] = $user2epid['userid'];
        }
        
        if(!empty($users_ids)){
            $usr = new User();
            $users_data = $usr->getMultipleUserDetails($users_ids);
        }
        
        
        $users2patient  = array();
        $line = 0;
        foreach($qparr as $ek => $gdata){
            if($users_data[$gdata['userid']]['usertype'] != "SA") {
                $users2patient[$line]['userid'] =  $gdata['userid'];
                
                $uname = "";
                if($users_data[$gdata['userid']]['title']){
                    $uname .=$users_data[$gdata['userid']]['title'].' ';
                }
                $uname .=  $users_data[$gdata['userid']]['first_name'].', '.$users_data[$gdata['userid']]['last_name'];
                $users2patient[$line]['user_name'] =  $uname;
                $users2patient[$line]['assign_date'] =  date('d.m.Y', strtotime($gdata['assign_date']));
                $users2patient[$line]['user_data'] = $users_data[$gdata['userid']];
                $line++;
            }
        }
        $this->view->users2patient  = $users2patient;
        
    }
    
    private function retainValues($values)
    {
        foreach($values as $key => $val)
        {
            $this->view->$key = $val;
        }
    }
}
