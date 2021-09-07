<?php

/**
 * ISPC-2804,Elena,18.02.2021
 *
 * Class MediprepareController
 */
class MediprepareController extends Pms_Controller_Action
{
    public function init()
    {

        /* Initialize action controller here */
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $this->clientid = $logininfo->clientid;
        $this->userid = $logininfo->userid;
        $this->usertype = $logininfo->usertype;
        $this->filepass = $logininfo->filepass;
        $this->logininfo = $logininfo;
        $this->groupid = $logininfo->groupid; //ISPC-2507 Ancuta 05.02.2020
    }


    public function preparationAction()
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->view->clientid = $clientid;

        $dosage_form_array = MedicationDosageform::client_medication_dosage_form($clientid, false);// added second param true to includ extra for custom options from sets
        foreach ($dosage_form_array as $ku => $ds_value) {
            $dosage_form[$ds_value['id']] = $ds_value['dosage_form'];
        }
        $this->view->dosage_form = $dosage_form;
        //print_r($dosage_form);
        $clinicBed = new ClinicBed();
        $patientClinicBed = new PatientClinicBed();
        //get all ClinicBeds for the Client
        $locAll = $clinicBed->getAllBeds($clientid);
        $this->view->rooms = $locAll;
        //print_r($locAll);
        //$mediprep = new Mediprepare();
        //print_r($mediprep->getSql());

        //get all active Patient-Ipids
        $patients = array();
        $ipids = PatientReadmission::getActiveIpids();
        $ipids = array_unique($ipids);
        //get further Informations for the patients (firstname, lastname)
        $patientnames = PatientMaster::getPatientNames($ipids, 2);
        $patientfullnames = PatientMaster::getPatientNames($ipids, 3);
        //print_r($patientfullnames);
        array_multisort(array_values($patientnames), SORT_ASC, SORT_STRING | SORT_FLAG_CASE, $patientnames);
        $patientnames['NOTASSIGNED'] = 'NOTASSIGNED'; //add an 'empty' patients for the view
//initialize the patientlist()
        foreach ($patientnames as $key => $value) {
            $patients[$key]['name'] = $value;
            $patients[$key]['vorname'] = $patientfullnames[$key][0];
            $patients[$key]['nachname'] = $patientfullnames[$key][1];
            $patients[$key]['epid'] = '';
            $patients[$key]['doctors'] = '';
            $patients[$key]['birth'] = '';
            $patients[$key]['status'] = '';

        }


        //get the Rooms assigned to the patient
        $patlocs = $patientClinicBed->get_patients_beds_assignment($ipids, $clientid);

        //print_r($patlocs);
        //get a Map with key = ipid, value = location_id
        $ipid_to_location_id = array();
        $location_to_ipid = array();
        $ipids_with_details = array();
        foreach ($patlocs as $patloc) {
            $ipid_to_location_id[$patloc['ipid']] = $patloc['bed_id'];
            $location_to_ipid[$patloc['bed_id']] = $patloc['ipid'];
            $ipids_with_details[] = $patloc['ipid'];
        }

        $this->view->ipid_to_loc = $ipid_to_location_id;
        $this->view->loc_to_ipid = $location_to_ipid;


        //get a get epid for creating a link to the PateintCourse
        foreach ($ipids as $ipid) {
            $patients[$ipid]['epid'] = Pms_Uuid::encrypt(Pms_CommonData::getIdfromIpid($ipid));
        }

        $this->view->patients = $patients;

    }

    public function mediplansAction()
    {
        $this->_helper->layout->setLayout('layout_ajax');

        $postdata = $this->getRequest()->getPost();
        //print_r($postdata);
        $stellliste_date = $postdata['stellliste_date'];
        $this->view->stellliste_date = $stellliste_date;
        $pdf = $postdata['pdf'];
        $to_save = $postdata['to_save'];
        $this->view->pdf = $pdf;
        $aChosen = $postdata['chosen'];
        $dosage_form = $postdata['dosage_form'];
        $patientData = [];
        $timedata = [];
        if ($postdata['timerange_filter']) {
            $timedata['till'] = $postdata['time_till'];
            $timedata['from'] = $postdata['time_from'];
        }
        $ipids = [];
        $patsInPlan = 0; //TODO-3949,Elena,15.03.2021
        foreach ($aChosen as $chosen) {
            $decid = Pms_Uuid::decrypt($chosen);
            $ipid = Pms_CommonData::getIpid($decid);
            $ipids[] = $ipid;
            $patientData[$ipid]['decid'] = $decid;
            $medicarr = PatientDrugPlan::getMedicationPlanAll($decid);
            $interval_data = $this->getPatientDosageInterval($ipid);;
            $patientData[$ipid]['dosage_interval'] = $interval_data['dosage_intervals'];
            $patientData[$ipid]['dosage_settings'] = $interval_data['dosage_settings'];
            $patientData[$ipid]['mediplan'] = $this->getMedis($ipid, $medicarr, $interval_data['dosage_settings'], $dosage_form, $timedata);
            $excludecounterall = 0;
            $items = 0;
            foreach ($patientData[$ipid]['mediplan'] as $key => $medis) {
                $excludecounter = 0;
                foreach ($medis as $med) {
                    $items++;
                    if ($med['exclude']) {
                        $excludecounterall++;
                        $excludecounter++;

                    }
                }
                if ($excludecounter == count($medis)) {
                    if (!isset($patientData[$ipid][$key])) {
                        $patientData[$ipid][$key] = [];
                    }
                    $patientData[$ipid][$key]['exclude'] = true;
                    $excludecounter = 0;

                }

            }
            if ($excludecounterall == $items) {
                $patientData[$ipid]['exclude'] = true;
            }else{//TODO-3949,Elena,15.03.2021
                $patsInPlan ++;
            }

        }

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $this->view->clientid = $clientid;
        $this->view->patsInPlan = $patsInPlan;//TODO-3949,Elena,15.03.2021
        $clinicBed = new ClinicBed();
        $patientClinicBed = new PatientClinicBed();
        //get all ClinicBeds for the Client
        $locAll = $clinicBed->getAllBeds($clientid);
        $this->view->rooms = $locAll;
        //print_r($locAll);
        $patientnames = PatientMaster::getPatientNames($ipids, 2);
        foreach ($patientData as $ipid => $data) {
            $patientData[$ipid]['patientname'] = $patientnames[$ipid];
        }

        $patlocs = $patientClinicBed->get_patients_beds_assignment($ipids, $clientid);

        //print_r($patlocs);
        //get a Map with key = ipid, value = location_id
        $ipid_to_location_id = array();
        $location_to_ipid = array();
        $ipids_with_details = array();
        $chosen_beds = [];
        foreach ($patlocs as $patloc) {
            $ipid_to_location_id[$patloc['ipid']] = $patloc['bed_id'];
            $location_to_ipid[$patloc['bed_id']] = $patloc['ipid'];
            $ipids_with_details[] = $patloc['ipid'];
            $patientData[$patloc['ipid']]['bed'] = $patloc['ipid'];
            //echo 'bedid for loc ' .$patloc['bed_id'];
            foreach ($locAll as $loc) {
                if ($loc['id'] == $patloc['bed_id']) {
                    if (!$patientData[$patloc['ipid']]['exclude']) {
                        $chosen_beds[] = $loc;
                        $patientData[$patloc['ipid']]['bed_name'] = $loc['bed_name'];
                        $patientData[$patloc['ipid']]['bed_id'] = $loc['id'];
                    }

                }
            }
        }
        $this->view->chosenBeds = $chosen_beds;
        // print_r($patientData);

        $this->view->patientsData = $patientData;
        if (intval($to_save) == 1) {
            $ret = $this->saveprepared($patientData, $postdata);
            echo json_encode($ret);
            exit;

        }

        if (intval($pdf) == 1) {
            $rend = $this->view->render('mediprepare/mediplans_print.html');//TODO-3949,Elena,15.03.2021

            $footer_text = $this->view->translate('[Page %s from %s]');
            $options = array(
                "orientation" => "L",
                "customheader" => "Medikation",
                "footer_type" => "1 of n",
                "footer_text" => $footer_text
            );
            Pms_PDFUtil::generate_pdf_to_browser($rend, 'Mediplan', $options);

        }


    }

    public function getPatientDosageInterval($ipid)
    {

        /* ================ PATIENT TIME SCHEME ======================= */
        $modules = new Modules();
        $individual_medication_time_m = $modules->checkModulePrivileges("141", $this->clientid);
        //$medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");

        $medication_blocks = array("actual");
        //$medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta

        /* IV BLOCK -  i.v. / s.c. */
        $iv_medication_block = $modules->checkModulePrivileges("53", $this->clientid);
        if (!$iv_medication_block) {
            $medication_blocks = array_diff($medication_blocks, array("isivmed"));
        }
        $this->view->medication_blocks = $medication_blocks; //TODO-3949,Elena,15.03.2021
        if ($individual_medication_time_m) {
            $individual_medication_time = 1;
        } else {
            $individual_medication_time = 0;
        }
        $this->view->individual_medication_time = $individual_medication_time;

        //get get saved data
        if ($individual_medication_time == "0") {
            $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($this->clientid, array("all"));
        } else {
            $client_time_scheme = MedicationIntervals::client_saved_medication_intervals($this->clientid, $medication_blocks);
        }

        $this->view->intervals = $client_time_scheme;

        //get time scchedule options
        $client_med_options = MedicationOptions::client_saved_medication_options($this->clientid);
        $this->view->client_medication_options = $client_med_options;

        $time_blocks = array('all');
        $NOT_timed_scheduled_medications = array();
        foreach ($client_med_options as $mtype => $mtime_opt) {
            if ($mtime_opt['time_schedule'] == "1") {
                $time_blocks[] = $mtype;
                $timed_scheduled_medications[] = $mtype;
            } else {
                $NOT_timed_scheduled_medications[] = $mtype;
            }
        }

        if ($individual_medication_time == "0") {
            $timed_scheduled_medications = array("actual", "isivmed"); // default
            $time_blocks = array("actual", "isivmed"); // default
        }

        foreach ($timed_scheduled_medications as $tk => $tmed) {
            if (in_array($tmed, $NOT_timed_scheduled_medications)) {
                unset($timed_scheduled_medications[$tk]);
            }
        }


        $this->view->timed_scheduled_medications[$ipid] = $timed_scheduled_medications;
        $patient_time_scheme = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid, $this->clientid, $time_blocks);


        if ($patient_time_scheme['patient']) {
            foreach ($patient_time_scheme['patient'] as $med_type => $dos_data) {
                if ($med_type != "new") {
                    $set = 0;
                    foreach ($dos_data as $int_id => $int_data) {
                        if (in_array($med_type, $patient_time_scheme['patient']['new'])) {

                            $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
                            $interval_array['interval'][$med_type][$int_id]['custom'] = '1';

                            $dosage_settings[$med_type][$set] = $int_data;
                            $set++;

                            $dosage_intervals[$med_type][$int_data] = $int_data;
                        } else {
                            $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
                            $interval_array['interval'][$med_type][$int_id]['custom'] = '0';
                            $interval_array['interval'][$med_type][$int_id]['interval_id'] = $int_id;

                            $dosage_settings[$med_type][$set] = $int_data;
                            $set++;

                            $dosage_intervals[$med_type][$int_data] = $int_data;
                        }
                    }
                }
            }
        } else {
            foreach ($patient_time_scheme['client'] as $med_type => $mtimes) {

                $inf = 1;
                $setc = 0;
                foreach ($mtimes as $int_id => $int_data) {

                    $interval_array['interval'][$med_type][$inf]['time'] = $int_data;
                    $interval_array['interval'][$med_type][$inf]['custom'] = '1';
                    $dosage_settings[$med_type][$setc] = $int_data;
                    $setc++;
                    $inf++;

                    $dosage_intervals[$med_type][$int_data] = $int_data;
                }
            }
        }
        return array('dosage_intervals' => $dosage_intervals, 'dosage_settings' => $dosage_settings);
    }

    public function saveprepared($patientData, $postdata)
    {
        foreach ($patientData as $ipid => $patdata) {
            if (!$patdata['exclude']) {
                $mediprep = new Mediprepare();
                $mediprep->ipid = $ipid;
                $prepare_for_date = date_create_from_format('d.m.Y', $postdata['stellliste_date']);
                $mediprep->prepare_for_date = $prepare_for_date->format('Y-m-d');
                $mediprep->preparedby = $this->userid;
                $mediprep->isprepared = true;
                $mediprep->clientid = $this->clientid;
                $criteria = [];
                $criteria['dosage_form'] = $postdata['dosage_form'];
                $criteria['timerange_filter'] = $postdata['timerange_filter'];
                $criteria['time_from'] = $postdata['time_from'];
                $criteria['time_till'] = $postdata['time_till'];
                $mediprep->criteria = json_encode($criteria);
                $mediprep->save();


            }

        }
        $ret = new stdClass();
        $ret->success = true;
        return $ret;

    }


    /**
     *
     * @param $ipid
     * @param $medicarr
     * @param $dosage_settings
     * @param $dosage_form
     * @param $timedata
     * @return array
     *
     * source&idea from PatientmedicationController
     */
    public function getMedis($ipid, $medicarr, $dosage_settings, $dosage_form, $timedata)
    {
        //copied from patientmedication, later optimate!
        $medications_array = array();// TODO-1488 Medication II 12.04.2018
        $show_new_fields = 1;
        $modules = new Modules();

        //$medication_blocks = array("actual","isbedarfs","iscrisis","isivmed","isnutrition","isschmerzpumpe","treatment_care","scheduled");

        $medication_blocks = array("actual", "isivmed");
        //$medication_blocks[] = "isintubated"; // ISPC-2176 16.04.2018 @Ancuta

        /* IV BLOCK -  i.v. / s.c. */
        $iv_medication_block = $modules->checkModulePrivileges("53", $this->clientid);
        if (!$iv_medication_block) {
            $medication_blocks = array_diff($medication_blocks, array("isivmed"));
        }
        $this->view->medication_blocks = $medication_blocks; //TODO-3949,Elena,15.03.2021
        foreach ($medicarr as $k => $medication_data) {
            if ($medication_data['isbedarfs'] == "1") {
                $medications_array['isbedarfs'][] = $medication_data;
            } elseif ($medication_data['isivmed'] == "1") {
                $medications_array['isivmed'][] = $medication_data;
            } elseif ($medication_data['isschmerzpumpe'] == "1") {
                $medications_array['isschmerzpumpe'][] = $medication_data;
                $cocktail_ids[] = $medication_data['cocktailid'];
            } elseif ($medication_data['treatment_care'] == "1") {
                $medications_array['treatment_care'][] = $medication_data;
                $treatmen_care_med_ids[] = $medication_data['medication_master_id'];
            } elseif ($medication_data['isnutrition'] == "1") {
                $medications_array['isnutrition'][] = $medication_data;
                $nutrition_med_ids[] = $medication_data['medication_master_id'];
            } elseif ($medication_data['scheduled'] == "1") {
                $medications_array['scheduled'][] = $medication_data;
            } elseif ($medication_data['iscrisis'] == "1") {
                $medications_array['iscrisis'][] = $medication_data;
            } elseif ($medication_data['isintubated'] == "1") // ISPC-2176 16.04.2018 @Ancuta
            {
                $medications_array['isintubated'][] = $medication_data;
            } else {
                $medications_array['actual'][] = $medication_data;
            }

            $med_ids[] = $medication_data['medication_master_id'];
        }

        // get medication details
        if (empty($med_ids)) {
            $med_ids[] = "99999999";
        }
        $med = new Medication();
        $master_medication_array = $med->master_medications_get($med_ids, false);


        // get schmerzpumpe details
        $cocktail_ids = array_unique($cocktail_ids);

// 		    if(count($cocktail_ids) == 0)
// 		    {
// 		        $cocktail_ids[] = '999999';
// 		    }

        $cocktailsC = new PatientDrugPlanCocktails();
        $cocktails = $cocktailsC->getDrugCocktails($cocktail_ids);

        if (count($cocktails) > 0) {
            $addnew = 0;
        } else {
            $addnew = 1;
        }
        $this->view->addnewlink = $addnew;
        // get drugplan_alt for cocktail


        $alt_cocktail_details = PatientDrugPlanAltCocktails:: get_drug_cocktails_alt($ipid, $cocktail_ids);
        $alt_cocktail_declined = PatientDrugPlanAltCocktails:: get_declined_drug_cocktails_alt($ipid, $cocktail_ids, false);
        $alt_cocktail_declined_offline = PatientDrugPlanAltCocktails:: get_declined_drug_cocktails_alt_offline($ipid, $cocktail_ids, false);

        $alt_cocktail_details_offline = $alt_cocktail_details['offline'];
        $alt_cocktail_details = $alt_cocktail_details['online'];

        //if changes are not approved - then no description


        foreach ($medications_array['isschmerzpumpe'] as $smpkey => $medicationsmp) {

            if (!in_array($medicationsmp['cocktailid'], $alt_cocktail_declined)) {

                if ($medications_array['isschmerzpumpe'][($smpkey + 1)]['cocktailid'] != $medicationsmp['cocktailid']) {
                    $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = $cocktails[$medicationsmp['cocktailid']];

                    if (!empty($alt_cocktail_details[$medicationsmp['cocktailid']])) {
                        $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = $alt_cocktail_details[$medicationsmp['cocktailid']];
                    } else {
                        $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = "";
                    }
                } else {
                    $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = "0";
                    $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = "";
                }
            } else {
                $medications_array['isschmerzpumpe'][$smpkey]['smpdescription'] = "0";
                $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt'] = "";
            }

            //offline changes
            $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt_offline'] = null;
            if (!empty($alt_cocktail_details_offline[$medicationsmp['cocktailid']])) {
                $medications_array['isschmerzpumpe'][$smpkey]['smpdescription_alt_offline'] = $alt_cocktail_details_offline[$medicationsmp['cocktailid']];
            }


        }

        // get treatment care details
        if (empty($treatmen_care_med_ids)) {
            $treatmen_care_med_ids[] = "99999999";
        }
        $medtr = new MedicationTreatmentCare();
        $medarr_tr = $medtr->getMedicationTreatmentCareById($treatmen_care_med_ids);

        foreach ($medarr_tr as $k_medarr_tr => $v_medarr_tr) {
            $medication_tr_array[$v_medarr_tr['id']] = $v_medarr_tr;
        }

        foreach ($medications_array['treatment_care'] as $tr_key => $tr_data) {
            $medications_array['treatment_care'][$tr_key]['medication'] = $medication_tr_array[$tr_data['medication_master_id']]['name'];
        }


        // get nutrition  details
        if (empty($nutrition_med_ids)) {
            $nutrition_med_ids[] = "99999999";
        }
        $mednutrition = new Nutrition();
        $medarr_nutrition = $mednutrition->getMedicationNutritionById($nutrition_med_ids);

        foreach ($medarr_nutrition as $k_medarr_nutritionr => $v_medarr_nutrition) {
            $medication_nutrition_array[$v_medarr_nutrition['id']] = $v_medarr_nutrition;
        }


        foreach ($medications_array['isnutrition'] as $nutrition_key => $tr_data) {
            $medications_array['isnutrition'][$nutrition_key]['medication'] = $medication_nutrition_array[$tr_data['medication_master_id']]['name'];
        }

        // get patient new dosage structure
        $drugplan_dosage = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid);
        //TODO-3624 Ancuta 23.11.2020
        $drugplan_dosage_concentration = PatientDrugPlanDosage::get_patient_drugplan_dosage_concentration($ipid);


        // get patient extra details
        $medication_extra = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid, $this->clientid);


        foreach ($medications_array as $medication_type => $med_array) {
            foreach ($med_array as $km => $vm) {
                // #################################################################
                // MEDICATION NAME
                // #################################################################
                $medications_array[$medication_type][$km]['medication'] = $vm['medication'];

                if ($vm['treatment_care'] != "1" && $vm['isnutrition'] != "1") {
                    if (strlen($vm['medicatioin']) > 0) {
                        $medications_array[$medication_type][$km]['medication'] = $vm['medication'];
                    } else {
                        $medications_array[$medication_type][$km]['medication'] = $master_medication_array[$vm['medication_master_id']];
                    }
                }


                if ($vm['medication_change'] != '0000-00-00 00:00:00') {
                    $medications_array[$medication_type][$km]['medication_change'] = date('d.m.Y', strtotime($vm['medication_change']));
                } elseif ($vm['medication_change'] == '0000-00-00 00:00:00' && $vm['change_date'] != '0000-00-00 00:00:00') {
                    $medications_array[$medication_type][$km]['medication_change'] = date('d.m.Y', strtotime($vm['change_date']));
                } else {
                    $medications_array[$medication_type][$km]['medication_change'] = date('d.m.Y', strtotime($vm['create_date']));
                }


                $medications_array[$medication_type][$km]['has_interval'] = $vm['has_interval'];
                if ($vm['administration_date'] != '0000-00-00 00:00:00') {
                    $medications_array[$medication_type][$km]['scheduled_date'] = strtotime($vm['administration_date'] . ' + ' . $vm['days_interval'] . ' days');
                    if ($medications_array[$medication_type][$km]['scheduled_date'] <= strtotime(date("Y-m-d 00:00:00", time()))) {
                        $medications_array[$medication_type][$km]['allow_restart'] = "1";
                    } else {
                        $medications_array[$medication_type][$km]['allow_restart'] = "0";
                    }

                } else {
// 		                $medications_array[$medication_type ][$km]['administration_date'] =  "";
                }

                $individual_medication_time_m = $modules->checkModulePrivileges("141", $this->clientid);
                if ($individual_medication_time_m) {
                    $individual_medication_time = 1;
                } else {
                    $individual_medication_time = 0;
                }
                $this->view->individual_medication_time = $individual_medication_time;


                if ($individual_medication_time == "1") {
                    //get time scchedule options
                    $client_med_options = MedicationOptions::client_saved_medication_options($this->clientid);
                    $this->view->client_medication_options = $client_med_options;

                    $time_blocks = array('all');
                    foreach ($client_med_options as $mtype => $mtime_opt) {
                        if ($mtime_opt['time_schedule'] == "1") {
                            $time_blocks[] = $mtype;
                            $timed_scheduled_medications[] = $mtype;
                        }
                    }
                } else {
                    $timed_scheduled_medications = array("actual", "isivmed"); // default
                    $time_blocks = array("actual", "isivmed"); // default
                }


                // #################################################################
                // DOSAGE
                // #################################################################
                $medications_array[$medication_type][$km]['old_dosage'] = $vm['dosage'];
// 	                if(!in_array($medication_type,array("actual","isivmed")))
                if (!in_array($medication_type, $timed_scheduled_medications)) {
                    $medications_array[$medication_type][$km]['dosage'] = $vm['dosage'];
                } else {
                    // first get new dosage
                    if (!empty($drugplan_dosage[$vm['id']])) {

                        // TODO-3585 Ancuta 10.11.2020  pct 1 - changes so dosage values are listed with  comma not dot
                        //$medications_array[$medication_type ][$km]['dosage'] = $drugplan_dosage[$vm['id']];

                        $formated_dosages = array();
                        if (!empty($drugplan_dosage[$vm['id']])) {
                            foreach ($drugplan_dosage[$vm['id']] as $dtime => $dvalue) {
                                $formated_dosages [$vm['id']][$dtime] = str_replace(".", ",", $dvalue);
                            }
                        }
                        $medications_array[$medication_type][$km]['dosage'] = $formated_dosages[$vm['id']];
                        //--


                    } else if (strlen($vm['dosage']) > 0) {
                        $old_dosage_arr[$vm['id']] = array();
                        $medications_array[$medication_type][$km]['dosage'] = array(); // TODO-1488 Medication II 12.04.2018

                        if (strpos($vm['dosage'], "-")) {
                            $old_dosage_arr[$vm['id']] = explode("-", $vm['dosage']);


                            if (count($old_dosage_arr[$vm['id']]) <= count($dosage_settings[$medication_type])) {
                                //  create array from old
                                for ($x = 0; $x < count($dosage_settings[$medication_type]); $x++) {
                                    $medications_array[$medication_type][$km]['dosage'][$dosage_settings[$medication_type][$x]] = $old_dosage_arr[$vm['id']][$x];
                                }
                            } else {
                                $medications_array[$medication_type][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!";
                                $medications_array[$medication_type][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage'];
                                for ($x = 2; $x < count($dosage_settings[$medication_type]); $x++) {
                                    $medications_array[$medication_type][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                                }
                            }
                        } else {
                            $medications_array[$medication_type][$km]['dosage'][$dosage_settings[$medication_type][0]] = "! ALTE DOSIERUNG!";
                            $medications_array[$medication_type][$km]['dosage'][$dosage_settings[$medication_type][1]] = $vm['dosage'];

                            for ($x = 2; $x < count($dosage_settings[$medication_type]); $x++) {
                                $medications_array[$medication_type][$km]['dosage'][$dosage_settings[$medication_type][$x]] = "";
                            }
                        }
                    } else {
                        $medications_array[$medication_type][$km]['dosage'] = "";
                    }
                }
                // ############################################################
                // Extra details  - drug / unit/ type / indication / importance
                // ############################################################

                $medications_array[$medication_type][$km]['drug'] = $medication_extra[$vm['id']]['drug'];
                $medications_array[$medication_type][$km]['unit'] = $medication_extra[$vm['id']]['unit'];
                $medications_array[$medication_type][$km]['type'] = $medication_extra[$vm['id']]['type'];
                $medications_array[$medication_type][$km]['indication'] = $medication_extra[$vm['id']]['indication']['name'];
                $medications_array[$medication_type][$km]['indication_color'] = $medication_extra[$vm['id']]['indication']['color'];
                $medications_array[$medication_type][$km]['importance'] = trim($medication_extra[$vm['id']]['importance']);
                $medications_array[$medication_type][$km]['dosage_form'] = $medication_extra[$vm['id']]['dosage_form'];
                $medications_array[$medication_type][$km]['dosage_form_id'] = $medication_extra[$vm['id']]['dosage_form_id'];
                $dosage = $medications_array[$medication_type][$km]['dosage'];
                if ((intval($dosage_form) == 0) || ($medication_extra[$vm['id']]['dosage_form_id'] == $dosage_form)) {
                    $medications_array[$medication_type][$km]['exclude'] = false;
                } else {
                    $medications_array[$medication_type][$km]['exclude'] = true;
                }
                if (!empty($timedata)) {
                    //echo 'timedata not empty';
                    //print_r($timedata);
                    $timeConsists = false;
                    foreach ($dosage as $givingtimeFormatted => $amount) {
                        //echo 'givingtime formatted ' . $givingtimeFormatted . 'amount ' . $amount;
                        if (!empty($amount)) {
                            $givingtimeArr = explode(':', $givingtimeFormatted);
                            //print_r($givingtimeArr);
                            $givingTime = intval($givingtimeArr[0]);
                            //echo 'giving time: ' . $givingTime . '--';
                            if (($givingTime >= $timedata['from']) && ($givingTime <= $timedata['till'])) {
                                $timeConsists = true;
                                //echo 'to true time';
                            }

                        }


                    }
                    if (!($timeConsists)) {
                        //echo 'to exclude';
                        $medications_array[$medication_type][$km]['exclude'] = true;
                    }
                }

                //print_r($dosage);
                //ISPC-2676 Ancuta 25.09.2020
                //$medication_extra[$vm['id']]['concentration'] = str_replace(',','.',$medication_extra[$vm['id']]['concentration']);//Commented by ancuta  ISPC-2684 16.10.2020
                //
                $medications_array[$medication_type][$km]['concentration'] = $medication_extra[$vm['id']]['concentration'];
                //TODO-3585 Ancuta 10.11.2020
                //$medications_array[$medication_type ][$km]['concentration_full'] =  $medication_extra[$vm['id']]['concentration'];
                $medications_array[$medication_type][$km]['concentration_full'] = str_replace('.', ",", $medication_extra[$vm['id']]['concentration']);
                //--

                // ISPC-2176, p6
                $medications_array[$medication_type][$km]['packaging'] = $medication_extra[$vm['id']]['packaging'];
                $medications_array[$medication_type][$km]['packaging_name'] = trim($medication_extra[$vm['id']]['packaging_name']);
                $medications_array[$medication_type][$km]['kcal'] = $medication_extra[$vm['id']]['kcal'];
                $medications_array[$medication_type][$km]['volume'] = $medication_extra[$vm['id']]['volume'];

                // ISPC-2247
                $medications_array[$medication_type][$km]['escalation'] = $medication_extra[$vm['id']]['escalation'];
                // --

                if ($medication_type == "isschmerzpumpe") {

                    if ($medication_extra[$vm['id']]['unit']) {
                        $medications_array[$medication_type][$km]['concentration_full'] .= " " . $medication_extra[$vm['id']]['unit'] . '/ml';
                    }

                    $medications_array[$medication_type][$km]['carriersolution_extra_text'] = "";


                } else {

                    if ($medication_extra[$vm['id']]['unit']) {
                        $medications_array[$medication_type][$km]['concentration_full'] .= " " . $medication_extra[$vm['id']]['unit'] . '/' . $medication_extra[$vm['id']]['dosage_form'];
                    }
                }


                if ($show_new_fields == "1" && strlen($medication_extra[$vm['id']]['concentration']) > 0 && $medication_extra[$vm['id']]['concentration'] != 0) {
                    if (!empty($drugplan_dosage[$vm['id']])) {
                        foreach ($drugplan_dosage[$vm['id']] as $dtime => $dvalue) {
                            $dosage_value = str_replace(",", ".", $dvalue);
                            $concentration = str_replace(",", ".", $medication_extra[$vm['id']]['concentration']);

                            $result = "";
                            $result = $dosage_value / $concentration;

                            if (!is_int($result)) {
                                $result = round($result, 4);
                                $medications_array[$medication_type][$km]['dosage_concentration'][$dtime] = rtrim(rtrim(number_format($result, 3, ",", "."), "0"), ",") . " " . $medication_extra[$vm['id']]['dosage_form'];
                            } else {
                                $medications_array[$medication_type][$km]['dosage_concentration'][$dtime] = $result . " " . $medication_extra[$vm['id']]['dosage_form'];
                            }
                        }
                    } else {
                        if (strlen($medication_extra[$vm['id']]['concentration']) > 0 && strlen($medications_array[$medication_type][$km]['dosage']) > 0) {

                            $dosage_value = str_replace(",", ".", $medications_array[$medication_type][$km]['dosage']);
                            $concentration = str_replace(",", ".", $medication_extra[$vm['id']]['concentration']);

                            $result = "";
                            $result = $dosage_value / $concentration;
                            if (!is_int($result)) {
                                $result = round($result, 4);
                                $medications_array[$medication_type][$km]['dosage_concentration'] = rtrim(rtrim(number_format($result, 3, ",", "."), "0"), ",") . " " . $medication_extra[$vm['id']]['dosage_form'];
                            } else {
                                $medications_array[$medication_type][$km]['dosage_concentration'] = $result . " " . $medication_extra[$vm['id']]['dosage_form'];
                            }
                        }
                    }
                }


                if ($medication_type == "isschmerzpumpe") {
                    $dosage_value = "";
                    $dosage_value = str_replace(",", ".", $medications_array[$medication_type][$km]['dosage']);
//    	                    $medications_array[$medication_type ][$km]['dosage_24h'] = round($dosage_value * 24, 2) ;
                    //TODO-3624 Ancuta 23.11.2020
                    if (isset($medication_extra[$vm['id']]['dosage_24h_manual']) && !empty($medication_extra[$vm['id']]['dosage_24h_manual'])) {
                        $medications_array[$medication_type][$km]['dosage_24h'] = str_replace(".", ",", $medication_extra[$vm['id']]['dosage_24h_manual']);
                    } else {
                        $medications_array[$medication_type][$km]['dosage_24h'] = $dosage_value * 24;
                    }

                    //TODO-3585  Ancuta 10.11.202
//    	                    $medications_array[$medication_type ][$km]['dosage'] = round($dosage_value, 2);
                    //$medications_array[$medication_type ][$km]['dosage'] = $dosage_value;
                    //$medications_array[$medication_type ][$km]['dosage'] = number_format($dosage_value,3,",","."); // Ancuta - Pumpe-dosage 10.12.2020
                    $medications_array[$medication_type][$km]['dosage'] = round($dosage_value, 3); // Ancuta - Pumpe-dosage 10.12.2020
                    $medications_array[$medication_type][$km]['dosage'] = str_replace(".", ",", $medications_array[$medication_type][$km]['dosage']);
                    // --
                    $medications_array[$medication_type][$km]['unit_dosage'] = isset($medication_extra[$vm['id']]['unit_dosage']) && !empty($medication_extra[$vm['id']]['unit_dosage']) ? str_replace(".", ",", $medication_extra[$vm['id']]['unit_dosage']) : str_replace(".", ",", $dosage_value);           //ISPC-2684 Lore 08.10.2020
                    $medications_array[$medication_type][$km]['unit_dosage_24h'] = isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".", ",", $medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".", ",", $medications_array[$medication_type][$km]['dosage_24h']);   //ISPC-2684 Lore 08.10.2020
                    $medications_array[$medication_type][$km]['unit_dosage_24h'] = isset($medication_extra[$vm['id']]['unit_dosage_24h']) && !empty($medication_extra[$vm['id']]['unit_dosage_24h']) ? str_replace(".", ",", $medication_extra[$vm['id']]['unit_dosage_24h']) : str_replace(".", ",", $medications_array[$medication_type][$km]['dosage_24h']);   //ISPC-2684 Lore 08.10.2020

                    $medications_array[$medication_type][$km]['dosage_mg_h_kg'] = '';
                    if ((int)$dosage_value > 0 && (int)$patient_weight > 0) {
                        $medications_array[$medication_type][$km]['dosage_mg_h_kg'] = " (" . rtrim(rtrim(number_format($dosage_value / $patient_weight, 3, ",", "."), "0"), ",") . $medications_array[$medication_type][$km]['unit'] . "/h/kg)";
                    }

                    //1848 p VI)

                    if ($show_new_fields == "1" && strlen($medication_extra[$vm['id']]['concentration']) > 0 && $medication_extra[$vm['id']]['concentration'] != 0) {
                        $dosage_24h_value = str_replace(",", ".", $medications_array[$medication_type][$km]['dosage_24h']);
                        $concentration_24h = str_replace(",", ".", $medication_extra[$vm['id']]['concentration']);

                        $result_24h = "";
                        $result_24h = $dosage_24h_value / $concentration_24h;

                        if (!is_int($result_24h)) {
//        	                        $result_24h = round($result_24h, 4);
                            $result_24h = $result_24h;//TODO-3624 Ancuta 23.11.2020
                            $medications_array[$medication_type][$km]['dosage_24h_concentration'] = number_format($result_24h, 3, ",", ".") . " " . $medication_extra[$vm['id']]['dosage_form'];
                        } else {
                            $medications_array[$medication_type][$km]['dosage_24h_concentration'] = $result_24h . " " . $medication_extra[$vm['id']]['dosage_form'];
                        }

                        //TODO-3585
                        ///$medications_array[$medication_type ][$km]['dosage_24h_concentration'] = str_replace('.', ",", $medications_array[$medication_type ][$km]['dosage_24h_concentration']);
                    }

                    //TODO-3585
                    $medications_array[$medication_type][$km]['dosage_24h'] = str_replace('.', ",", $medications_array[$medication_type][$km]['dosage_24h']);

                }


                // #################################################################
                // MEDICATION comment
                // #################################################################
                $medications_array[$medication_type][$km]['comments'] = nl2br($vm['comments']);
//    	                $medications_array[$medication_type ][$km]['comments'] = $vm['comments'];
            }
        }

        if (!empty($medications_array['isschmerzpumpe'])) {

            foreach ($medications_array['isschmerzpumpe'] as $drug_id_ke => $med_details) {
                $alt_medications_array["isschmerzpumpe"][$med_details['cocktailid']][] = $med_details;
            }

            unset($medications_array['isschmerzpumpe']);
            $medications_array['isschmerzpumpe'] = $alt_medications_array["isschmerzpumpe"];
        }
        $allow_new_fields = array("actual", "isbedarfs", "iscrisis", "isivmed", "isnutrition");

        /* 		    echo "<pre/>";
                    print_r($medications_array); exit; */
        //ISPC-2636 Lore 29.07.2020
        $cust = Doctrine_Query::create()
            ->select("client_medi_sort, user_overwrite_medi_sort_option")
            ->from('Client')
            ->where('id = ?', $this->clientid);
        $cust->getSqlQuery();
        $disarray = $cust->fetchArray();


        $client_medi_sort = $disarray[0]['client_medi_sort'];
        $user_overwrite_medi_sort_option = $disarray[0]['user_overwrite_medi_sort_option'];

        $uss = Doctrine_Query::create()
            ->select('*')
            ->from('UserTableSorting')
            ->Where('client = ?', $this->clientid)
            ->orderBy('change_date DESC')
            ->limit(1);
        $uss_arr = $uss->fetchArray();
        $last_sort_order = unserialize($uss_arr[0]['value']);
        //dd($last_sort_order[0][1]);
        //.

        /* ================ MEDICATION :: USER SORTING ======================= */
        $usort = new UserTableSorting();
// 		    $saved_data = $usort->user_saved_sorting($userid,false, false, false ,$ipid);
        $saved_data = $usort->user_saved_sorting($this->userid, false, $ipid);


        foreach ($saved_data as $k => $sord) {
            if ($sord['name'] == "order") {

                $med_type_sarr = explode("-", $sord['page']);
                $page = $med_type_sarr[0];
                $med_type = $med_type_sarr[1];
                if ($page == "patientmedication" && $med_type) {
                    $order_value = unserialize($sord['value']);
                    $saved_order[$med_type]['col'] = $order_value[0][0];
                    $saved_order[$med_type]['ord'] = $order_value[0][1];

                }
            }
        }

        //TODO-3450 Ancuta 22.09.2020 - added sorting in request - so we can use BOTH clent sorting - and the sorting in page, as  the page is refreshed when sorting is applied
        if (!empty($client_medi_sort)) {

            $request_sort = array();
            if (!empty($_REQUEST['sort_b']) && !empty($_REQUEST['sort_c']) && !empty($_REQUEST['sort_d'])) {
                $request_sort[$_REQUEST['sort_b']]['col'] = $_REQUEST['sort_c'];
                $request_sort[$_REQUEST['sort_b']]['ord'] = $_REQUEST['sort_d'];
            }

            foreach ($medication_blocks as $k => $mt) {
                if (!empty($request_sort[$mt])) {
                    $saved_order[$mt]['col'] = $request_sort[$mt]['col'];
                    $saved_order[$mt]['ord'] = $request_sort[$mt]['ord'];
                } elseif (!empty($client_medi_sort)) {
                    $saved_order[$mt]['col'] = !empty($client_medi_sort) ? $client_medi_sort : "medication";              //ISPC-2636 Lore 29.07.2020
                    $saved_order[$mt]['ord'] = "asc";
                } elseif (empty($saved_order[$mt])) {
                    $saved_order[$mt]['col'] = "medication";
                    $saved_order[$mt]['ord'] = "asc";
                }
            }

        } else {
            foreach ($medication_blocks as $k => $mt) {
                if (empty($saved_order[$mt])) {
                    $saved_order[$mt]['col'] = "medication";
                    $saved_order[$mt]['ord'] = "asc";
                }
            }
        }
        //---


        //ISPC-2636 Lore 29.07.2020
        if ($user_overwrite_medi_sort_option != '0') {
            $uomso = Doctrine_Query::create()
                ->select('*')
                ->from('UserSettingsMediSort')
                ->Where('clientid = ?', $this->clientid)
                ->orderBy('create_date DESC')
                ->limit(1);
            $uomso_arr = $uomso->fetchArray();
            //dd($uomso_arr);
            if (!empty($uomso_arr)) {
                $overwrite_saved_order = array();
                foreach ($saved_order as $block => $vals) {
                    $overwrite_saved_order[$block]['col'] = !empty($uomso_arr[0]['sort_column']) ? $uomso_arr[0]['sort_column'] : 'medication';//Ancuta 17.09.2020-- Issue if empty
                    $overwrite_saved_order[$block]['ord'] = !empty($last_sort_order[0][1]) ? $last_sort_order[0][1] : "asc";
                }
                $saved_order = $overwrite_saved_order;
            }
        }
        //.

        //dd($saved_order);
        $this->view->sort_order = $saved_order;

        // ############ APPLY SORTING ##############
        foreach ($medications_array as $type => $m_values) {
            if ($type != "isschmerzpumpe") {
                if ($saved_order[$type]['ord'] == "asc") {
                    $medications_array_sorted[$type] = $this->array_sort($m_values, $saved_order[$type]['col'], SORT_ASC);
                } else {
                    $medications_array_sorted[$type] = $this->array_sort($m_values, $saved_order[$type]['col'], SORT_DESC);
                }
            } else {
                foreach ($medications_array[$type] as $sch_id => $sh_m_values) {
                    if ($saved_order[$type]['ord'] == "asc") {
                        $medications_array_sorted[$type][$sch_id] = $this->array_sort($sh_m_values, $saved_order[$type]['col'], SORT_ASC);
                    } else {
                        $medications_array_sorted[$type][$sch_id] = $this->array_sort($sh_m_values, $saved_order[$type]['col'], SORT_DESC);
                    }
                }

            }
        }
        if (!empty($medications_array_sorted)) {
            $medications_array = array();
            $medications_array = $medications_array_sorted;
        }

        $this->view->saved_order = $saved_order;
        $this->view->js_saved_order = json_encode($saved_order);


        return $medications_array;
    }

    /**
     * @param $array
     * @param null $on
     * @param int $order
     * @return array
     *
     * @from PatientmedicationController
     */
    private function array_sort($array, $on = NULL, $order = SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();
        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            if ($on == 'birthd' || $on == 'admissiondate' || $on == 'medication_change') {

                                if ($on == 'birthdyears') {
                                    $v2 = substr($v2, 0, 10);
                                }
                                $sortable_array[$k] = strtotime($v2);
                            } elseif ($on == 'epid') {
                                $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
                            } elseif ($on == 'percentage') {
                                $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                            } else {
                                $sortable_array[$k] = ucfirst($v2);
                            }
                        }
                    }
                } else {
                    if ($on == 'birthd' || $on == 'admissiondate' || $on == 'medication_change') {
                        if ($on == 'birthdyears') {
                            $v = substr($v, 0, 10);
                        }
                        $sortable_array[$k] = strtotime($v);
                    } elseif ($on == 'epid' || $on == 'percentage') {
                        $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
                    } elseif ($on == 'percentage') {
                        $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                    } else {
                        $sortable_array[$k] = ucfirst($v);
                    }
                }
            }
            switch ($order) {
                case SORT_ASC:
                    $sortable_array = Pms_CommonData::a_sort($sortable_array);
                    break;

                case SORT_DESC:
                    $sortable_array = Pms_CommonData::ar_sort($sortable_array);

                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }


    /**
     *
     */
    public function logAction()
    {
        $days = 7;
        $this->_helper->layout->setLayout('layout_ajax');
        $logArray = Mediprepare::getPreparationsLog($days);
        $logArrayArchive = Mediprepare::getPreparationsLog($days, true);
        $allUsers = Pms_CommonData::getClientUsers($this->clientid);
        $allUsersArray = [];
        foreach ($allUsers as $key => $user) {
            $allUsersArray[$user['id']] = $user['last_name'] . ", " . $user['first_name'];
        }

        $dosage_form_array = MedicationDosageform::client_medication_dosage_form($this->clientid, false);// added second param true to includ extra for custom options from sets
        foreach ($dosage_form_array as $ku => $ds_value) {
            $dosage_form[$ds_value['id']] = $ds_value['dosage_form'];
        }

        $this->view->dosage_form = $dosage_form;

        $ipids = [];
        $this->view->users = $allUsersArray;
        //$this->view->logs = $logArray;
        $logArrayExtended = [];
        $logArrayArchiveExtended = [];

        foreach ($logArray as $k => $log) {
            $ipids[] = $log['ipid'];
            $criteria = json_decode($log['criteria'], true);

            $log['dosage_form'] = "Alle";
            if ($criteria['dosage_form'] != 0) {
                //$logArray[$k]['dosage_form'] = $dosage_form[$criteria['dosage_form']];
                $log['dosage_form'] = $dosage_form[$criteria['dosage_form']];;
            }

            $log['timerange_filter'] = $criteria['timerange_filter'];
            $log['timerange'] = "Alle";
            if (intval($criteria['timerange_filter'] == 1)) {
                $log['timerange'] = $criteria['time_from'] . ':00 - ' . $criteria['time_till'] . ':00';
            }

            $logArrayExtended[] = $log;


        }

        krsort($logArrayExtended);
        $this->view->logs = $logArrayExtended;

        //archive
        foreach ($logArrayArchive as $k => $log) {
            $ipids[] = $log['ipid'];
            $criteria = json_decode($log['criteria'], true);

            $log['dosage_form'] = "Alle";
            if ($criteria['dosage_form'] != 0) {
                //$logArray[$k]['dosage_form'] = $dosage_form[$criteria['dosage_form']];
                $log['dosage_form'] = $dosage_form[$criteria['dosage_form']];;
            }

            $log['timerange_filter'] = $criteria['timerange_filter'];
            $log['timerange'] = "Alle";
            if (intval($criteria['timerange_filter'] == 1)) {
                $log['timerange'] = $criteria['time_from'] . ':00 - ' . $criteria['time_till'] . ':00';
            }

            $logArrayArchiveExtended[] = $log;


        }
        krsort($logArrayArchiveExtended);
        $this->view->logsarchive = $logArrayArchiveExtended;

        $patArray = PatientMaster::getPatientNames($ipids);
        //print_r($patArray);
        $this->view->patArrray = $patArray;
        //exit();


    }


    public function givelistAction(){
        $this->_helper->layout->setLayout('layout_ajax');
        $clinicBed = new ClinicBed();
        $patientClinicBed = new PatientClinicBed();
        //get all ClinicBeds for the Client
        $locAll = $clinicBed->getAllBeds($this->clientid);
        $this->view->rooms = $locAll;
        //print_r($locAll);
        //$mediprep = new Mediprepare();
        //print_r($mediprep->getSql());

        //get all active Patient-Ipids
        $patients = array();
        $ipids = PatientReadmission::getActiveIpids();
        $ipids = array_unique($ipids);
        //get further Informations for the patients (firstname, lastname)
        $patientnames = PatientMaster::getPatientNames($ipids, 2);
        array_multisort(array_values($patientnames), SORT_ASC, SORT_STRING | SORT_FLAG_CASE, $patientnames);
        $patientnames['NOTASSIGNED'] = 'NOTASSIGNED'; //add an 'empty' patients for the view
//initialize the patientlist()
        foreach ($patientnames as $key => $value) {
            $patients[$key]['name'] = $value;
            $patients[$key]['epid'] = '';
            $patients[$key]['doctors'] = '';
            $patients[$key]['birth'] = '';
            $patients[$key]['status'] = '';

        }


        //get the Rooms assigned to the patient
        $patlocs = $patientClinicBed->get_patients_beds_assignment($ipids, $this->clientid);

        //print_r($patlocs);
        //get a Map with key = ipid, value = location_id
        $ipid_to_location_id = array();
        $location_to_ipid = array();
        $ipids_with_details = array();
        foreach ($patlocs as $patloc) {
            $ipid_to_location_id[$patloc['ipid']] = $patloc['bed_id'];
            $location_to_ipid[$patloc['bed_id']] = $patloc['ipid'];
            $ipids_with_details[] = $patloc['ipid'];
        }

        $this->view->ipid_to_loc = $ipid_to_location_id;
        $this->view->loc_to_ipid = $location_to_ipid;


        //get a get epid for creating a link to the PateintCourse
        foreach ($ipids as $ipid) {
            $patients[$ipid]['epid'] = Pms_Uuid::encrypt(Pms_CommonData::getIdfromIpid($ipid));
        }

        $this->view->patients = $patients;

    }


}