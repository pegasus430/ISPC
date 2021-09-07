<?php
// require_once ("Pms/Form.php");
/**
 *
 * @update Jan 29, 2018: @author claudiu, checked for ISPC-2071
 * there are blocks that are saved in the cf table
 * 
 * changeing: bypass Trigger() on PC
 * fixing: adding this block to a saved cf would not save to PC the first time
 * fixing: this would insert PC each time you saved
 *
 */
class Application_Form_ContactForms extends Pms_Form
{

    public function clear_form_data($ipid = '', $contact_form_id = 0)
    {
        if (! empty($contact_form_id)) {
            $Q = Doctrine_Query::create()->update('ContactForms')
                ->set('isdelete', '1')
                ->where("id = ?", $contact_form_id)
                ->andWhere('ipid  = ?', $ipid);
            $result = $Q->execute();
            
            return true;
        } else {
            return false;
        }
    }

    public function update_child_forms($ipid,$child_id, $parent)
    {
        if (empty($ipid) || empty($child_id) || $child_id == 0){
            return;
        }
        $update_last_child = Doctrine_Query::create()->update('ContactForms')
            ->set('parent', "'" . $child_id . "'")
            ->where('id="' . $child_id . '"')
            ->andWhere('ipid = ?',$ipid)
            ->andWhere('isdelete = "1"');
        $update_child_exec = $update_last_child->execute();
        
        $update_all_childs = Doctrine_Query::create()->update('ContactForms')
            ->set('parent', "'" . $parent . "'")
            ->where('parent = "' . $child_id . '"')
            ->andWhere('ipid = ?',$ipid)
            ->andWhere('isdelete = "1"');
        $update_all_childs_res = $update_all_childs->execute();
    }

    public function InsertData($post, $allowed_blocks, $patient_details = false)
    {
        // $logininfo = new Zend_Session_Namespace('Login_Info');
        $logininfo = $this->logininfo;
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $cform = new ContactForms();
        
        // $decid = Pms_Uuid::decrypt($_GET['id']);
        // $ipid = Pms_CommonData::getIpid($decid);
        $ipid = $post['ipid'];
        
        $now = time();
        $clear_form_entryes = $this->clear_form_data($ipid, $post['old_contact_form_id']);
        
        $modules = new Modules();
        $sh_comment_module = $modules->checkModulePrivileges("83", $clientid);
        $xb_shortcut_contactform = $modules->checkModulePrivileges("113", $clientid);
        $xp_careinstructions_module = $modules->checkModulePrivileges("116", $clientid);
        $xi_internalcomment_module = $modules->checkModulePrivileges("118", $clientid);
        $modulepriv_le = $modules->checkModulePrivileges("128", $clientid);
        
        if ($sh_comment_module || $xb_shortcut_contactform) {
            $short_comment = "XB";
        } else {
            $short_comment = "K";
        }
        
        $first_contact_form = array();
        $old_contact_form = false;
//         if (strlen($post['old_contact_form_id']) > 0 && $post['old_contact_form_id'] != "0") {
        if ( ! empty($post['old_contact_form_id'])) {
            // get old contact form
            $old_contact_form = $cform->get_contact_form($post['old_contact_form_id'], true);
//             dd($old_contact_form);
            if ($old_contact_form) {
                // comment block
                if (! in_array('com', $allowed_blocks)) {
                    $post['comment_block'] = $old_contact_form['comment'];
                }
                
                // internal comment block
                if (! in_array('internalcomment', $allowed_blocks)) {
                    $post['internal_comment'] = $old_contact_form['internal_comment'];
                }
                
                if (! in_array('drivetime', $allowed_blocks)) {
                    $post['fahrtzeit'] = $old_contact_form['fahrtzeit'];
                    $post['fahrtstreke_km'] = $old_contact_form['fahrtstreke_km'];
                    $post['expert_accompanied'] = $old_contact_form['expert_accompanied'];//ispc-2291
                }
                
                if (! in_array('com_ph', $allowed_blocks)) {
                    $post['comment_apotheke'] = $old_contact_form['comment_apotheke'];
                }
                
                if (! in_array('anam', $allowed_blocks)) {
                    $post['case_history'] = $old_contact_form['case_history'];
                }
                
                if (! in_array('visitplan', $allowed_blocks)) {
                    $post['quality'] = $old_contact_form['quality'];
                }
                
                if (! in_array('ecog', $allowed_blocks)) {
                    $post['ecog'] = $old_contact_form['ecog'];
                    $post['karnofsky'] = $old_contact_form['karnofsky'];
                }
                
                if (! in_array('careinstructions', $allowed_blocks)) {
                    $post['care_instructions'] = $old_contact_form['care_instructions'];
                }
                
                if (! in_array('sgbxi', $allowed_blocks)) {
                    $post['sgbxi_quality'] = $old_contact_form['sgbxi_quality'];
                }
                
                if (! in_array('befund_txt', $allowed_blocks)) {
                    $post['befund_txt'] = $old_contact_form['befund_txt'];
                }
                
                if (! in_array('therapy', $allowed_blocks)) {
                    $post['therapy_txt'] = $old_contact_form['therapy_txt'];
                }
                
                if (! in_array('free_visit', $allowed_blocks)) {
                    $post['free_visit'] = $old_contact_form['free_visit'];
                }
                
                if (! in_array('bavaria_options', $allowed_blocks)) {
                    $sp = Doctrine_Query::create()->select('*')
                        ->from('Sapsymptom')
                        ->where("ipid='" . $ipid . "'")
                        ->andwhere('visit_id = ' . $post['old_contact_form_id'])
                        ->andwhere('visit_type = "contactform"')
                        ->andWhere('isdelete = 0')
                        ->orderBy('create_date ASC');
                    $sparr = $sp->fetchArray();
                    
                    if (is_array($sparr) && sizeof($sparr) > 0) {
                        foreach ($sparr as $sap) {
                            $sapvalarr = split(",", $sap['sapvalues']);
                            $sapv_id = $sap['id'];
                        }
                    }
                    $post['symptom'] = $sapvalarr;
                }
            }
        }
        
        // validate visit date
        if (empty($post['date']) || ! Pms_Validation::isdate($post['date'])) {
            $post['date'] = date('d.m.Y');
        }
        if (empty($post['begin_date_h']) || strlen($post['begin_date_h']) == 0) {
            $post['begin_date_h'] = date('H', strtotime('-5 minutes'));
        }
        
        if (empty($post['begin_date_m']) || strlen($post['begin_date_m']) == 0) {
            $post['begin_date_m'] = date('i', strtotime('-5 minutes'));
        }
        
        if (empty($post['end_date_h']) || strlen($post['end_date_h']) == 0) {
            $post['end_date_h'] = date('H', strtotime('+10 minutes'));
        }
        
        if (empty($post['end_date_m']) || strlen($post['end_date_m']) == 0) {
            $post['end_date_m'] = date('i', strtotime('+10 minutes'));
        }
        
        $stmb = new ContactForms();
        $stmb->ipid = $ipid;
        /* -----------------VISIT START DATE AND END DATE ------- */
        $form_date = explode(".", $post['date']);
        $stmb->start_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
        $cf_start_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
        
        if ($post['over_midnight'] == "1") {
            $next_day = date('Y-m-d', strtotime($post['date'] . "+1 day"));
            $stmb->end_date = date('Y-m-d H:i:s', strtotime($next_day . ' ' . $post['end_date_h'] . ':' . $post['end_date_m'] . ':00'));
            $cf_end_date = date('Y-m-d H:i:s', strtotime($next_day . ' ' . $post['end_date_h'] . ':' . $post['end_date_m'] . ':00'));
        } else {
            $stmb->end_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['end_date_h'] . ':' . $post['end_date_m'] . ':00'));
            $cf_end_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['end_date_h'] . ':' . $post['end_date_m'] . ':00'));
        }
        $stmb->begin_date_h = $post['begin_date_h'];
        $stmb->begin_date_m = $post['begin_date_m'];
        $stmb->end_date_h = $post['end_date_h'];
        $stmb->end_date_m = $post['end_date_m'];
        
        // CHANGE :: ISPC:2019
        if ($post['over_midnight'] == "1" && $post['billable_date'] == "start") {
            $stmb->billable_date = $cf_start_date;
            $cf_billable_date = $cf_start_date;
        } elseif ($post['over_midnight'] == "1" && $post['billable_date'] == "end") {
            $stmb->billable_date = $cf_end_date;
            $cf_billable_date = $cf_end_date;
        } else {
            $stmb->billable_date = $cf_start_date;
            $cf_billable_date = $cf_start_date;
        }
        $stmb->date = $form_date[2] . "-" . $form_date[1] . "-" . $form_date[0] . ' ' . date("H") . ':' . date("i") . ":00";
        /* -------------------------------------------------------- */
        $stmb->form_type = $post['form_type'];
        $stmb->fahrtzeit = $post['fahrtzeit'];
        $stmb->fahrtstreke_km = $post['fahrtstreke_km'];
        if (isset($post['expert_accompanied'])) {//ispc-2291
            $stmb->expert_accompanied = $post['expert_accompanied'];
        }
        $stmb->comment = htmlspecialchars($post['comment_block']);
        $stmb->comment_apotheke = htmlspecialchars($post['comment_apotheke']);
        $stmb->case_history = htmlspecialchars($post['case_history']);
        $stmb->quality = $post['quality'];
        
        if (strlen($post['karnofsky']) > 0) {
            
            if ($post['karnofsky'] == "100" || $post['karnofsky'] == "90") {
                $post['ecog'] = "0";
            } elseif ($post['karnofsky'] == "80" || $post['karnofsky'] == "70") {
                $post['ecog'] = "1";
            } else 
                if ($post['karnofsky'] == "60" || $post['karnofsky'] == "50") {
                    $post['ecog'] = "2";
                } else 
                    if ($post['karnofsky'] == "40" || $post['karnofsky'] == "30") {
                        $post['ecog'] = "3";
                    } else 
                        if ($post['karnofsky'] == "20" || $post['karnofsky'] == "10") {
                            $post['ecog'] = "4";
                        } else 
                            if ($post['karnofsky'] == "0") {
                                $post['ecog'] = "5";
                            }
            
            $stmb->ecog = $post['ecog'];
            $stmb->karnofsky = $post['karnofsky'];
        } else {
            $stmb->karnofsky = NULL;
        }
        
        $stmb->care_instructions = htmlspecialchars($post['care_instructions']);
        $stmb->befund_txt = htmlspecialchars($post['befund_txt']);
        $stmb->internal_comment = htmlspecialchars($post['internal_comment']);
        $stmb->sgbxi_quality = (strlen($post['sgbxi_quality']) > '0' ? $post['sgbxi_quality'] : '0');
        $stmb->free_visit = (strlen($post['free_visit']) > '0' ? $post['free_visit'] : '0');
        $stmb->therapy_txt = htmlspecialchars($post['therapy_txt']);
        $stmb->over_midnight = $post['over_midnight'];
        
        if (! empty($post['block_invoice_condition'])) {
            $stmb->invoice_condition = $post['block_invoice_condition'];
        }
        
        $stmb->save();
        
        $result = $stmb->id;
        
        if ($result) {
            //$this->update_child_forms($post['old_contact_form_id'], $result);
            $this->update_child_forms($ipid, $post['old_contact_form_id'], $result);
        }
        
        if ($old_contact_form) {
            $create_user = $old_contact_form['create_user'];
            $change_date = date('Y-m-d H:i:s', time());
            $change_user = $userid;
            
            $update_cf_user = Doctrine_Query::create()->update('ContactForms')
                ->set('create_user', "'" . $create_user . "'")
                ->set('change_user', "'" . $change_user . "'")
                ->set('change_date', "'" . $change_date . "'")
                ->where('id = "' . $result . '"');
            $update_cf_user->execute();
        }
        // get first contact form used to write date in verlauf
        $first_contact_form = $this->get_first_contactform($result);
        
        if ($first_contact_form) {
            $done_date = date('Y-m-d H:i:s', strtotime($first_contact_form['start_date']));
        } else {
            
            $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', $now)));
        }
        
        if (strlen($_REQUEST['cid']) > 0) {
            // edit comment
            $comment = 'Besuch vom ' . date('d.m.Y H:i', strtotime($done_date)) . ' wurde editiert';
        } else {
            // new comment
            $comment = 'Kontaktformular  hinzugefügt';
        }
        
        // $change_date = ' ['.date("d.m.Y H:i", time()).']';
        $change_date = $post['contact_form_change_date'];
        
//         $cust = new PatientCourse();
//         $cust->ipid = $ipid;
//         $cust->course_date = date("Y-m-d H:i:s", time());
//         $cust->course_type = Pms_CommonData::aesEncrypt("F");
//         $cust->course_title = Pms_CommonData::aesEncrypt($comment);
//         $cust->tabname = Pms_CommonData::aesEncrypt("contact_form");
//         $cust->recordid = $result;
//         $cust->user_id = $userid;
//         $cust->done_date = $done_date;
//         $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
//         $cust->done_id = $result;
//         $cust->save();
        
        $course_date = date("Y-m-d H:i:s", $now);
        
        $pc_records = array(); // patient_course records to insert
        $pc_records[] =  array(
            'ipid'          => $ipid,
            'course_date'   => $course_date,
            'user_id'       => $userid,
            'done_date'     => $done_date,
            'done_name'     => "contact_form",
            'done_id'       => $result,
            
            'course_type'   => "F",
            'course_title'  => $comment,
            'tabname'       => "contact_form",
            'recordid'      => $result,
        );
        
        
        $F_entry_id = $cust->id;
        
//         if (strlen($_REQUEST['cid']) > 0) {
        if ( ! empty($post['old_contact_form_id'])) {
            $update_old_link = Doctrine_Query::create()->update('PatientCourse')
                ->set('tabname', "'" . Pms_CommonData::aesEncrypt("contact_form_no_link") . "'")
                ->where('ipid = ?', $ipid)
                ->andWhere('tabname="' . Pms_CommonData::aesEncrypt("contact_form") . '"')
                ->andWhere('recordid  =  ?', $post['old_contact_form_id'])
                ->andWhere('wrong = "0"'); // disable changing the tabname if verlauf entry is allready deleted
            $update_old_link->execute();
        }
        
//         if (strlen($_REQUEST['cid']) == 0) {
        if (empty($post['old_contact_form_id'])) {
            
            $course_title = $post['begin_date_h'] . ":" . $post['begin_date_m'] . ' - ' . $post['end_date_h'] . ':' . $post['end_date_m'] . '  ' . $post['date'];
            
            $pc_records[] =  array(
                'ipid'          => $ipid,
                'course_date'   => $course_date,
                'user_id'       => $userid,
                'done_date'     => $done_date,
                'done_name'     => "contact_form",
                'done_id'       => $result,
            
                'course_title'  => $course_title,
                'course_type'   => "K",
                'tabname'       => "contact_form_first_date",
            );
            
        }
        
        $old_date = date('d.m.Y', strtotime($old_contact_form['date']));
        /*
         * if(!empty($post['date']) && strlen($_REQUEST['cid']) > 0 && $post['date'] != $old_date)
         * {
         * //edited contact form date verlauf entry
         * $cust = new PatientCourse();
         * $cust->ipid = $ipid;
         * $cust->course_date = date("Y-m-d H:i:s", time());
         * $cust->course_type = Pms_CommonData::aesEncrypt("K");
         * $cust->course_title = Pms_CommonData::aesEncrypt('Datum: ' . $old_date . ' --> ' . $post['date'].$change_date);
         * $cust->user_id = $userid;
         * $cust->done_date = $done_date;
         * $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
         * $cust->done_id = $result;
         * $cust->save();
         * }
         *
         * if(!empty($post['date']) && strlen($_REQUEST['cid']) > 0 && ($post['begin_date_h'] != $old_contact_form['begin_date_h'] || $post['begin_date_m'] != $old_contact_form['begin_date_m']))
         * {
         * $old_start_hm = $old_contact_form['begin_date_h'] . ':' . $old_contact_form['begin_date_m'];
         * $start_hm = $post['begin_date_h'] . ':' . $post['begin_date_m'];
         *
         * //edited contact form start hour:min verlauf entry
         * $cust = new PatientCourse();
         * $cust->ipid = $ipid;
         * $cust->course_date = date("Y-m-d H:i:s", time());
         * $cust->course_type = Pms_CommonData::aesEncrypt("K");
         * $cust->course_title = Pms_CommonData::aesEncrypt('Beginn: ' . $old_start_hm . ' --> ' . $start_hm.$change_date);
         * $cust->user_id = $userid;
         * $cust->done_date = $done_date;
         * $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
         * $cust->done_id = $result;
         * $cust->save();
         * }
         *
         * if(!empty($post['date']) && strlen($_REQUEST['cid']) > 0 && ($post['end_date_h'] != $old_contact_form['end_date_h'] || $post['end_date_m'] != $old_contact_form['end_date_m']))
         * {
         * $old_end_hm = $old_contact_form['end_date_h'] . ':' . $old_contact_form['end_date_m'];
         * $end_hm = $post['end_date_h'] . ':' . $post['end_date_m'];
         *
         * //edited contact form end hour:min verlauf entry
         * $cust = new PatientCourse();
         * $cust->ipid = $ipid;
         * $cust->course_date = date("Y-m-d H:i:s", time());
         * $cust->course_type = Pms_CommonData::aesEncrypt("K");
         * $cust->course_title = Pms_CommonData::aesEncrypt('Ende: ' . $old_end_hm . ' --> ' . $end_hm.$change_date);
         * $cust->user_id = $userid;
         * $cust->done_date = $done_date;
         * $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
         * $cust->done_id = $result;
         * $cust->save();
         * }
         */
        
        // ISPC-1839 extra
        if ((! empty($post['date']) && strlen($_REQUEST['cid']) > 0 && $post['date'] != $old_date) 
            || (! empty($post['date']) 
                && strlen($_REQUEST['cid']) > 0 
                && ($post['begin_date_h'] != $old_contact_form['begin_date_h'] || $post['begin_date_m'] != $old_contact_form['begin_date_m'])) 
            || (! empty($post['date']) 
                && strlen($_REQUEST['cid']) > 0 
                && ($post['end_date_h'] != $old_contact_form['end_date_h'] || $post['end_date_m'] != $old_contact_form['end_date_m']))) 
        {
            
            $course_title_arr = array();
            $course_title = ''; 
            
            if ((! empty($post['date']) && strlen($_REQUEST['cid']) > 0 && $post['date'] != $old_date)
                || (! empty($post['date']) && strlen($_REQUEST['cid']) > 0 && ($post['begin_date_h'] != $old_contact_form['begin_date_h'] || $post['begin_date_m'] != $old_contact_form['begin_date_m']))
                || (! empty($post['date']) && strlen($_REQUEST['cid']) > 0 && ($post['end_date_h'] != $old_contact_form['end_date_h'] || $post['end_date_m'] != $old_contact_form['end_date_m']))
                ) {
//                 $course_title_arr[] = 'Datum: ' . $old_date . ' --> ' . $post['date'] . $change_date;
                $course_title_arr[] = 'Datum: ' . $post['date'];
                $course_title_arr[] = 'Beginn: ' . $post['begin_date_h'] . ':' . $post['begin_date_m'];
                $course_title_arr[] = 'Ende: ' . $post['end_date_h'] . ':' . $post['end_date_m'];
                $course_title = "{$post['begin_date_h']}:{$post['begin_date_m']} - {$post['end_date_h']}:{$post['end_date_m']} {$post['date']}";
            }
            
//             if (! empty($post['date']) && strlen($_REQUEST['cid']) > 0 && ($post['begin_date_h'] != $old_contact_form['begin_date_h'] || $post['begin_date_m'] != $old_contact_form['begin_date_m'])) {
//                 $old_start_hm = $old_contact_form['begin_date_h'] . ':' . $old_contact_form['begin_date_m'];
//                 $start_hm = $post['begin_date_h'] . ':' . $post['begin_date_m'];
                
//                 $course_title_arr[] = 'Beginn: ' . $old_start_hm . ' --> ' . $start_hm . $change_date;
//             }
            
//             if (! empty($post['date']) && strlen($_REQUEST['cid']) > 0 && ($post['end_date_h'] != $old_contact_form['end_date_h'] || $post['end_date_m'] != $old_contact_form['end_date_m'])) {
//                 $old_end_hm = $old_contact_form['end_date_h'] . ':' . $old_contact_form['end_date_m'];
//                 $end_hm = $post['end_date_h'] . ':' . $post['end_date_m'];
                
//                 $course_title_arr[] = 'Ende: ' . $old_end_hm . ' --> ' . $end_hm . $change_date;
//             }
            
//             $course_title_arr[] = $change_date;
//                 $course_title = implode("\n", $course_title_arr);
            if ( ! empty($course_title)) {
                
                $pc_records[] =  array(
                    'ipid'          => $ipid,
                    'course_date'   => $course_date,
                    'user_id'       => $userid,
                    'done_date'     => $done_date,
                    'done_name'     => "contact_form",
                    'done_id'       => $result,
                
                    'course_title'  => $course_title,
                    'course_type'   => "K",
                    'tabname'       => "contact_form_change_date",
                );
            
            }
            
            $changed_date_id = $cust->id; // what was this used for ?
            
            //removed this ISPC-1839 p.2 , because of ISPC-2071, @author claudiu on 29.01.2018
            
//             // ISPC-1839 p.2 add a line "was moved to date hh:mm"
//             if (! empty($post['date']) && strlen($_REQUEST['cid']) > 0 && $post['date'] != $old_date) {
//                 $old_done_date = date('Y-m-d H:i:s', strtotime($old_contact_form['date'] . ' ' . $old_contact_form['begin_date_h'] . ':' . $old_contact_form['begin_date_m'] . ':00'));
                
//                 $cust = new PatientCourse();
//                 $cust->triggerformid = 0;
//                 $cust->ipid = $ipid;
//                 $cust->course_date = $old_contact_form['create_date']; // NOT OK
//                 $cust->course_type = Pms_CommonData::aesEncrypt("K");
                
//                 $cust->tabname = Pms_CommonData::aesEncrypt("contact_form_moved_to_date");
//                 $cust->course_title = Pms_CommonData::aesEncrypt($this->translate('contactform was moved to date') . " " . $post['date']);
//                 $cust->recorddata = '<a onclick="location.hash=\'wrc_' . $F_entry_id . '\'; return false;">' . $change_date . '</a>';
                
//                 $cust->user_id = $userid;
//                 $cust->done_date = $old_contact_form['start_date'];
//                 $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
//                 $cust->done_id = $old_contact_form['id'];
//                 $cust->save();
//             }
        }
         
        
        if ($post['fahrtzeit'] == '--') {
//             $post['fahrtzeit'] = '0';
            $post['fahrtzeit'] = null;//ISPC-2071
        }
        
        
        //Fahrtzeit
        //perform PatientCourse logic for this block
        if (in_array('drivetime', $allowed_blocks)) {
            $post['fahrtzeit'] = Pms_CommonData::mb_trim($post['fahrtzeit']);
            if ( ! empty($post['fahrtzeit'])) {
                if (empty($post['old_contact_form_id']) //this cf was just created, it's the first
                    || (is_array($old_contact_form) && $old_contact_form['fahrtzeit'] != $post['fahrtzeit'])) //this is a edit=modify of cf = old_contact_form_id and value of field 
                {
                    
                    $course_title = "Fahrtzeit: " . $post['fahrtzeit'];
                    
                    $pc_records[] =  array(
                        'ipid'          => $ipid,
                        'course_date'   => $course_date,
                        'user_id'       => $userid,
                        'done_date'     => $done_date,
                        'done_name'     => "contact_form",
                        'done_id'       => $result,
                    
                        'course_title'  => $course_title,
                        'course_type'   => "K",
                        'tabname'       => "fahrtzeit_block",
                    );
                    
                }
                
            } elseif ( ! empty($post['old_contact_form_id']) 
                && is_array($old_contact_form) 
                && $old_contact_form['fahrtzeit'] != $post['fahrtzeit']) 
            {
                //must erase old values, user emptied this field now
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'fahrtzeit_block');       
            }
        }
        
        //Fahrtstrecke
        //perform PatientCourse logic for this block
        if (in_array('drivetime', $allowed_blocks)) {
            $post['fahrtstreke_km'] = Pms_CommonData::mb_trim($post['fahrtstreke_km']);
            if (( ! empty($post['fahrtstreke_km']) && $post['fahrtstreke_km'] != '0.00')
                || ( ! empty($post['expert_accompanied'])) //ispc-2291
                ) 
            {
                if (empty($post['old_contact_form_id']) //this cf was just created, it's the first
                    || (is_array($old_contact_form) && $old_contact_form['fahrtstreke_km'] != $post['fahrtstreke_km'])
                    || (is_array($old_contact_form) && $old_contact_form['expert_accompanied'] != $post['expert_accompanied'])
                    
                    ) //this is a edit=modify of cf = old_contact_form_id and value of field
                {
                    
                    $course_title = "Fahrtstrecke: " . $post['fahrtstreke_km'] ;
                    if (isset($post['expert_accompanied'])) {//ispc-2291
                        $course_title .= PHP_EOL ."Begleitung durch eine Ligetis Fachkraft: " . ($post['expert_accompanied'] == 'yes' ? 'Ja' : 'Nein');
                    }
                    
                    $pc_records[] =  array(
                        'ipid'          => $ipid,
                        'course_date'   => $course_date,
                        'user_id'       => $userid,
                        'done_date'     => $done_date,
                        'done_name'     => "contact_form",
                        'done_id'       => $result,
                    
                        'course_title'  => $course_title,
                        'course_type'   => "K",
                        'tabname'       => "fahrtstreke_km_block",
                    );
                }
            
            } elseif ( ! empty($post['old_contact_form_id']) 
                && is_array($old_contact_form) 
                && $old_contact_form['fahrtstreke_km'] != $post['fahrtstreke_km']) 
            {
                //must erase old values, user emptied this field now
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'fahrtstreke_km_block');
            }
        }
        
        
        
        //Kommentare
        //perform PatientCourse logic for this block
        if (in_array('com', $allowed_blocks)) {
            $post['comment_block'] = Pms_CommonData::mb_trim($post['comment_block']);
            if ( ! empty($post['comment_block'])) {
                if (empty($post['old_contact_form_id']) //this cf was just created, it's the first
                    || (is_array($old_contact_form) && $old_contact_form['comment'] != $post['comment_block'])) //this is a edit=modify of cf = old_contact_form_id and value of field
                {
        
                    $course_title = "Sonstiges / Kommentar: " . htmlspecialchars(addslashes($post['comment_block'])) ;
        
                    $pc_records[] =  array(
                        'ipid'          => $ipid,
                        'course_date'   => $course_date,
                        'user_id'       => $userid,
                        'done_date'     => $done_date,
                        'done_name'     => "contact_form",
                        'done_id'       => $result,
        
                        'course_title'  => $course_title,
                        'course_type'   => $short_comment,
                        'tabname'       => "comment_block",
                    );
                }
        
            } elseif ( ! empty($post['old_contact_form_id'])
                && is_array($old_contact_form)
                && $old_contact_form['comment'] != $post['comment_block'])
            {
                //must erase old values, user emptied this field now
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'comment_block');
            }
        }
        
        
        //Besuch war
        //perform PatientCourse logic for this block
        if (in_array('visitplan', $allowed_blocks)) {
            if ($xb_shortcut_contactform) {
                $quality1 = array(
                    "1" => "geplant",
                    "2" => "ungeplant",
                    "3" => "akut",
                    "4" => "SAPV-Notdienst"
                );
        
                $post['quality'] = Pms_CommonData::mb_trim($post['quality']);
                if ( ! empty($post['quality'])) {
                    if (empty($post['old_contact_form_id']) //this cf was just created, it's the first
                        || (is_array($old_contact_form) && $old_contact_form['quality'] != $post['quality'])) //this is a edit=modify of cf = old_contact_form_id and value of field
                    {
                        $course_title = "Besuch war: " . $quality1[$post['quality']];
        
                        $pc_records[] =  array(
                            'ipid'          => $ipid,
                            'course_date'   => $course_date,
                            'user_id'       => $userid,
                            'done_date'     => $done_date,
                            'done_name'     => "contact_form",
                            'done_id'       => $result,
        
                            'course_title'  => $course_title,
                            'course_type'   => $short_comment,
                            'tabname'       => "quality_block",
                        );
                    }
        
                } elseif ( ! empty($post['old_contact_form_id'])
                    && is_array($old_contact_form)
                    && $old_contact_form['quality'] != $post['quality'])
                {
                    //must erase old values, user emptied this field now
                    PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'quality_block');
                }
        
            }
        }
        
        
        //Interner Kommentar
        //perform PatientCourse logic for this block
        if (in_array('internalcomment', $allowed_blocks)) {
            if ($xi_internalcomment_module) {
                $special_internal_comment_shortcut = "XI";
            } else {
                $special_internal_comment_shortcut = "XK";
            }
        
            $post['internal_comment'] = Pms_CommonData::mb_trim($post['internal_comment']);
            if ( ! empty($post['internal_comment'])) {
                if (empty($post['old_contact_form_id']) //this cf was just created, it's the first
                    || (is_array($old_contact_form) && $old_contact_form['internal_comment'] != $post['internal_comment'])) //this is a edit=modify of cf = old_contact_form_id and value of field
                {
                    $course_title = htmlspecialchars(addslashes($post['internal_comment']));
        
                    $pc_records[] =  array(
                        'ipid'          => $ipid,
                        'course_date'   => $course_date,
                        'user_id'       => $userid,
                        'done_date'     => $done_date,
                        'done_name'     => "contact_form",
                        'done_id'       => $result,
        
                        'course_title'  => $course_title,
                        'course_type'   => $special_internal_comment_shortcut,
                        'tabname'       => "internal_comment_block",
                    );
                }
        
            } elseif ( ! empty($post['old_contact_form_id'])
                && is_array($old_contact_form)
                && $old_contact_form['internal_comment'] != $post['internal_comment'])
            {
                //must erase old values, user emptied this field now
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'internal_comment_block');
            }
        }
        
        
        
        //Kommentare Apotheke
        //perform PatientCourse logic for this block
        if (in_array('com_ph', $allowed_blocks)) {
            $post['comment_apotheke'] = Pms_CommonData::mb_trim($post['comment_apotheke']);
            if ( ! empty($post['comment_apotheke'])) {
                if (empty($post['old_contact_form_id']) //this cf was just created, it's the first
                    || (is_array($old_contact_form) && $old_contact_form['comment_apotheke'] != $post['comment_apotheke'])) //this is a edit=modify of cf = old_contact_form_id and value of field
                {
        
                    $course_title = "Kommentar Medikation / Pumpe / Apotheke: " . htmlspecialchars(addslashes($post['comment_apotheke']));
        
                    $pc_records[] =  array(
                        'ipid'          => $ipid,
                        'course_date'   => $course_date,
                        'user_id'       => $userid,
                        'done_date'     => $done_date,
                        'done_name'     => "contact_form",
                        'done_id'       => $result,
        
                        'course_title'  => $course_title,
                        'course_type'   => "Q",
                        'tabname'       => "comment_apotheke_block",
                    );
                }
        
            } elseif ( ! empty($post['old_contact_form_id'])
                && is_array($old_contact_form)
                && $old_contact_form['comment_apotheke'] != $post['comment_apotheke'])
            {
                //must erase old values, user emptied this field now
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'comment_apotheke_block');
            }
        }
        
         
        //Anamnese
        //perform PatientCourse logic for this block
        if (in_array('anam', $allowed_blocks)) {
            $post['case_history'] = Pms_CommonData::mb_trim($post['case_history']);
            if ( ! empty($post['case_history'])) {
                if (empty($post['old_contact_form_id']) //this cf was just created, it's the first
                    || (is_array($old_contact_form) && $old_contact_form['case_history'] != $post['case_history'])) //this is a edit=modify of cf = old_contact_form_id and value of field
                {
        
                    $course_title = "Anamnese: " . htmlspecialchars(addslashes($post['case_history']));
        
                    $pc_records[] =  array(
                        'ipid'          => $ipid,
                        'course_date'   => $course_date,
                        'user_id'       => $userid,
                        'done_date'     => $done_date,
                        'done_name'     => "contact_form",
                        'done_id'       => $result,
        
                        'course_title'  => $course_title,
                        'course_type'   => "A",
                        'tabname'       => "case_history_block",
                    );
                }
        
            } elseif ( ! empty($post['old_contact_form_id'])
                && is_array($old_contact_form)
                && $old_contact_form['case_history'] != $post['case_history'])
            {
                //must erase old values, user emptied this field now
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'case_history_block');
            }
        }
        
        
        //Pflege-Anweisung
        //perform PatientCourse logic for this block
        if (in_array('careinstructions', $allowed_blocks)) {
            if ($xp_careinstructions_module) {
                $careinsrtuction_shortcut = "XP";
            } else {
                $careinsrtuction_shortcut = "K";
            }
        
            $post['care_instructions'] = Pms_CommonData::mb_trim($post['care_instructions']);
            if ( ! empty($post['care_instructions'])) {
                if (empty($post['old_contact_form_id']) //this cf was just created, it's the first
                    || (is_array($old_contact_form) && $old_contact_form['care_instructions'] != $post['care_instructions'])) //this is a edit=modify of cf = old_contact_form_id and value of field
                {
        
                    $course_title = "Pflege-Anweisung: " . htmlspecialchars(addslashes($post['care_instructions']));
        
                    $pc_records[] =  array(
                        'ipid'          => $ipid,
                        'course_date'   => $course_date,
                        'user_id'       => $userid,
                        'done_date'     => $done_date,
                        'done_name'     => "contact_form",
                        'done_id'       => $result,
        
                        'course_title'  => $course_title,
                        'course_type'   => $careinsrtuction_shortcut,
                        'tabname'       => "care_instructions_block",
                    );
                }
        
            } elseif ( ! empty($post['old_contact_form_id'])
                && is_array($old_contact_form)
                && $old_contact_form['care_instructions'] != $post['care_instructions'])
            {
                //must erase old values, user emptied this field now
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'care_instructions_block');
            }
        }
        
        
        //Befund
        //perform PatientCourse logic for this block
        if (in_array('befund_txt', $allowed_blocks)) {
            $post['befund_txt'] = Pms_CommonData::mb_trim($post['befund_txt']);
            if ( ! empty($post['befund_txt'])) {
                if (empty($post['old_contact_form_id']) //this cf was just created, it's the first
                    || (is_array($old_contact_form) && $old_contact_form['befund_txt'] != $post['befund_txt'])) //this is a edit=modify of cf = old_contact_form_id and value of field
                {
        
                    $course_title = htmlspecialchars(addslashes($post['befund_txt']));
        
                    $pc_records[] =  array(
                        'ipid'          => $ipid,
                        'course_date'   => $course_date,
                        'user_id'       => $userid,
                        'done_date'     => $done_date,
                        'done_name'     => "contact_form",
                        'done_id'       => $result,
        
                        'course_title'  => $course_title,
                        'course_type'   => "B",
                        'tabname'       => "befund_txt_block",
                    );
                }
        
            } elseif ( ! empty($post['old_contact_form_id'])
                && is_array($old_contact_form)
                && $old_contact_form['befund_txt'] != $post['befund_txt'])
            {
                //must erase old values, user emptied this field now
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'befund_txt_block');
            }
        }
        
        
        
        //Therapie
        //perform PatientCourse logic for this block
        if (in_array('therapy', $allowed_blocks)) {
            $post['therapy_txt'] = Pms_CommonData::mb_trim($post['therapy_txt']);
            if ( ! empty($post['therapy_txt'])) {
                if (empty($post['old_contact_form_id']) //this cf was just created, it's the first
                    || (is_array($old_contact_form) && $old_contact_form['therapy_txt'] != $post['therapy_txt'])) //this is a edit=modify of cf = old_contact_form_id and value of field
                {
        
                    $course_title = htmlspecialchars(addslashes($post['therapy_txt']));
        
                    $pc_records[] =  array(
                        'ipid'          => $ipid,
                        'course_date'   => $course_date,
                        'user_id'       => $userid,
                        'done_date'     => $done_date,
                        'done_name'     => "contact_form",
                        'done_id'       => $result,
        
                        'course_title'  => $course_title,
                        'course_type'   => "T",
                        'tabname'       => "therapy_txt_block",
                    );
                }
        
            } elseif ( ! empty($post['old_contact_form_id'])
                && is_array($old_contact_form)
                && $old_contact_form['therapy_txt'] != $post['therapy_txt'])
            {
                //must erase old values, user emptied this field now
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'therapy_txt_block');
            }
        }
        
        
        //Karnofsky
        //perform PatientCourse logic for this block
        if (in_array('ecog', $allowed_blocks)) {
            $post['karnofsky'] = Pms_CommonData::mb_trim($post['karnofsky']);
            if ( ! empty($post['karnofsky'])) {
                if (empty($post['old_contact_form_id']) //this cf was just created, it's the first
                    || (is_array($old_contact_form) && $old_contact_form['karnofsky'] != $post['karnofsky'])) //this is a edit=modify of cf = old_contact_form_id and value of field
                {
        
                    $karnofsky_values = Pms_CommonData::get_karnofsky();
                    foreach ($karnofsky_values as $k => $data) {
                        $karnofsky_values_array[$data['value']] = $data['label'];
                    }
        
//                     $course_title = "Karnofsky: " . htmlspecialchars(addslashes($karnofsky_values_array[$old_contact_form['karnofsky']])) . " --> " . htmlspecialchars(addslashes($karnofsky_values_array[$post['karnofsky']]));
                    /*
                     * please if you copy-paste this block... do NOT use this htmlspecialchars(addslashes(..
                     * data must be saved in the db as user intended ! 
                     * you later format for display if you like... 
                     * but you MUST save what user sent ! NOT what you like
                     */
                    $course_title = "Karnofsky: " . htmlspecialchars(addslashes($karnofsky_values_array[$post['karnofsky']]));
        
                    $pc_records[] =  array(
                        'ipid'          => $ipid,
                        'course_date'   => $course_date,
                        'user_id'       => $userid,
                        'done_date'     => $done_date,
                        'done_name'     => "contact_form",
                        'done_id'       => $result,
        
                        'course_title'  => $course_title,
                        'course_type'   => "K",
                        'tabname'       => "karnofsky_block",
                    );
                }
        
            }
            elseif ( ! empty($post['old_contact_form_id'])
                && is_array($old_contact_form)
                && $old_contact_form['karnofsky'] != $post['karnofsky'])
            {
                //must erase old values, user emptied this field now
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'karnofsky_block');
            }
        }
        
        
        
        // get saved sympot for old form
        $saved_symp = array();
        if (strlen($post['old_contact_form_id'] > 0)) {
            $cf_symptpms = new ContactFormsSymp();
            $saved_symp = $cf_symptpms->getContactFormsSymp($post['old_contact_form_id'], $ipid);
        }
        
        if (! $post['old_contact_form_id'] || empty($saved_symp)) {
            foreach ($post['symp'] as $setid => $sympt_values) {
                $selected_sets = $sympt_values['details_select'];
            }
            $symp_zapv_details = new SymptomatologyZapvDetails();
            $zapv_details = $symp_zapv_details->getSymptpomatologyZapvDetailsData($selected_sets);
            
            foreach ($post['symp'] as $setid => $sympt_values) {
                if (is_array($sympt_values['input_value']) && sizeof($sympt_values['input_value']) > 0) {
                    $a_post = $sympt_values;
                    $a_post['ipid'] = $ipid;
                    $save_symp = 0;
                    foreach ($a_post['input_value'] as $val) {
                        if (strlen($val) > '0') {
                            $save_symp = 1;
                        }
                    }
                    
                    if ($save_symp == 1) {
                        $patient_form = new Application_Form_PatientSymptomatology();
                        $a_post['iskvno'] = 1;
                        $a_post['kvnoid'] = 'c' . $result; // "c" is for contact form
                        $a_post['edit_entry_date'] = $done_date;
                        $patient_form->InsertData($a_post);
                    }
                    
                    $current_values = $sympt_values['current_value'];
                    $comments = $sympt_values['comment'];
                    $details_array = array();
                    $existing_values = array();
                    $tocourse['details'] = array();
                    foreach ($sympt_values['input_value'] as $symp_id => $val) {
                        if (strlen($val) > 0) {
                            $sympvals = new ContactFormsSymp();
                            $sympvals->contact_form_id = $result;
                            $sympvals->ipid = $ipid;
                            $sympvals->symp_id = $symp_id;
                            // $sympvals->details = $sympt_values['details'][$symp_id];
                            $sympvals->last_value = ($current_values[$symp_id] == '' ? NULL : $current_values[$symp_id]);
                            $sympvals->current_value = ($val == '' ? NULL : $val);
                            $sympvals->comment = htmlspecialchars($comments[$symp_id]);
                            $sympvals->save();
                            if ($sympvals->id > 0) {
                                $entry_id = $sympvals->id;
                                
                                $tocourse['details'][$symp_id] = array();
                                foreach ($sympt_values['details_multiple_select'][$symp_id] as $k => $select_value) {
                                    if ($select_value != 0 && ! in_array($select_value, $existing_values)) {
                                        $existing_values[] = $select_value;
                                        
                                        $details_array[] = array(
                                            "contact_form_id" => $result,
                                            "entry_id" => $entry_id,
                                            "detail_id" => $select_value
                                        );
                                        
                                        if ($sympt_values['complex'] == '1') {
                                            
                                            $tocourse['details'][$symp_id][$select_value] = $zapv_details[$sympt_values['details_select'][$symp_id]][$select_value]['details_description']; // here should be added the name selected from dropdown. !!!!!!!!!!!!!!!!!!!!!!!!!
                                        }
                                    }
                                }
                            }
                            
                            $tocourse['input_value'] = $val;
                            $tocourse['second_value'] = $sympt_values['comment'][$symp_id];
                            $tocourse['symptid'] = $symp_id;
                            $tocourse['setid'] = $setid;
                            $tocourse['alias'] = $sympt_values['alias'];
                            $tocourse['iskvno'] = '0';
                            $coursecomment[$setid][] = $tocourse;
                        }
                    }
                    
                    if (! empty($details_array)) {
                        $collection = new Doctrine_Collection('ContactFormsSympDetails');
                        $collection->fromArray($details_array);
                        $collection->save();
                    }
                }
                
                // if(!empty($coursecomment[$setid]) && strlen($_REQUEST['cid']) >= 0)
                if (! empty($coursecomment[$setid]) && strlen($_REQUEST['cid']) >= 0) {
                    $cust = new PatientCourse();
                    $cust->ipid = $ipid;
                    $cust->course_date = date("Y-m-d H:i:s", time());
                    $cust->course_type = Pms_CommonData::aesEncrypt("S");
                    $cust->course_title = Pms_CommonData::aesEncrypt(serialize($coursecomment[$setid]));
                    $cust->isserialized = 1;
                    $cust->user_id = $userid;
                    $cust->done_date = $done_date;
                    $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                    $cust->done_id = $result;
                    
                    /*
                     * ISPC-2071
                     * + Symptome = symp block insert first time
                     * + Symptome ZAPV I = symp_zapv block  insert first time
                     * + Symptome ZAPV II = symp_zapv_complex block  insert first time
                     * 
                     * ispc-2071 added this tabname
                     */
                    $cust->tabname = Pms_CommonData::aesEncrypt("PatientSymptomatology");
                    
                    $cust->save();
                }
            }
        } else {
            // edit
            // ??????
            // ???
        }
        
        
        
        
        
        
        //encrypt PatientCourse records and persist'em
        $encrypted = Pms_CommonData::aesEncryptMultiple($pc_records);
        foreach ($pc_records as $row_key => $row) {
            $pc_records[$row_key]['done_name'] = isset($encrypted[$row_key]['done_name']) ? $encrypted[$row_key]['done_name'] : null;
            $pc_records[$row_key]['course_type'] = isset($encrypted[$row_key]['course_type']) ? $encrypted[$row_key]['course_type'] : null;
            $pc_records[$row_key]['course_title'] = isset($encrypted[$row_key]['course_title']) ? $encrypted[$row_key]['course_title'] : null;
            $pc_records[$row_key]['tabname'] = isset($encrypted[$row_key]['tabname']) ? $encrypted[$row_key]['tabname'] : null;  
        }

        if ( ! empty($pc_records)) {
          
            $collection = new Doctrine_Collection('PatientCourse');
            $collection->fromArray($pc_records);
            $collection->save(); 
            $collection = $collection->getPrimaryKeys();
//             dd($pc_recorddata);
            
        }
        
        
        
        
        // ecog edit verlauf entry
        /*
         * if(!empty($post['ecog']) && strlen($_REQUEST['cid']) > 0 && $post['ecog'] != $old_contact_form['ecog'])
         * {
         * $ecog_values_array = array(
         * '0' => 'Auswahl',
         * '1' => 'normale Aktivität',
         * '2' => 'Gehfähig, leichte Arbeiten möglich',
         * '3' => 'nicht arbeitsfähig, kann > 50% der Wachzeit aufstehen',
         * '4' => 'begrenzte Selbstversorgung, > 50% Wachzeit bettlägrig',
         * '5' => 'Pflegebedürftig, permanent bettlägrig',
         * );
         *
         * $cust = new PatientCourse();
         * $cust->ipid = $ipid;
         * $cust->course_date = date("Y-m-d H:i:s", time());
         * $cust->course_type = Pms_CommonData::aesEncrypt("K");
         * $cust->course_title = Pms_CommonData::aesEncrypt("ECOG: " . htmlspecialchars(addslashes($ecog_values_array[$old_contact_form['ecog']])) . " --> " . htmlspecialchars(addslashes($ecog_values_array[$post['ecog']])));
         * $cust->user_id = $userid;
         * $cust->done_date = $done_date;
         * $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
         * $cust->done_id = $result;
         * $cust->save();
         * }
         */
        
         
        
        
        
        
        
        
        //Qualitätssicherungsbesuch - add ToDos
        //this block has no PatientCourse, it will just insert into ToDos
        if (in_array('sgbxi', $allowed_blocks)) {
            if ($post['sgbxi_quality'] == '1' && $patient_details) {
                // @todo add a check to see if users allready received todos today from same patient
                // get koordinator usergroups users
                $usr = new User();
                $usergroup = new Usergroup();
                
                $pqarr = $usr->getUserByClientid($clientid);
                $comma = ",";
                $userval = "'0'";
                
                foreach ($pqarr as $key => $val) {
                    $userval .= $comma . "'" . $val['id'] . "'";
                    $comma = ",";
                }
                
                $groupid = $usergroup->getMastergroupGroups($clientid, array(
                    '6'
                ));
                $users = $usr->getuserbyidsandGroupId($userval, $groupid, 0);
                
                $todo_text = 'Rechnung Qualitätssicherungsbesuch ' . $patient_details['first_name'] . ' ' . $patient_details['last_name'] . ' erstellen.';
                $records = array();
                foreach ($users as $k_usr => $v_usr) {
                    if ($k_usr > '0') {
                        $records[] = array(
                            'client_id' => $clientid,
                            'user_id' => $k_usr,
                            'group_id' => '0',
                            'ipid' => $ipid,
                            'todo' => $todo_text,
                            'triggered_by' => 'system',
                            'isdelete' => '0',
                            'iscompleted' => '0',
                            'create_date' => date("Y-m-d H:i:s", time()),
                            'until_date' => date("Y-m-d H:i:s", time())
                        );
                    }
                }
                
                if ( ! empty($records)) {
                    $collection = new Doctrine_Collection('ToDos');
                    $collection->fromArray($records);
                    $collection->save();
                    
                    $pc_recorddata = $collection->getPrimaryKeys();
    //                 dd($pc_recorddata, $collection->toArray());
                }
            }
        }
        
        
        // ISPC-1619
        $module_bielefeld_email = $modules->checkModulePrivileges("123", $logininfo->clientid);
        
        if ($module_bielefeld_email) {
            $mess = Messages::notdienst_action_messages($ipid, $userid);
        }
        
        // ISPC-1703 Kontaktformular for BAVARIA
        
        /* ------------------------ SAPVFB3------------------------------------ */
        $allow_e_entries = true;
        // $verlauf_e_previleges = new Modules();
        
        if ($modules->checkModulePrivileges("73", $clientid)) // DEACTIVATE the E Verlauf entries from Besuchsformular Bayern
        {
            $allow_e_entries = false;
        }
        
        
//         $loguser = Doctrine::getTable('User')->find($logininfo->userid);
//         if ($loguser) {
//             $loguserarray = $loguser->toArray();
//             $unamecd = $loguserarray['last_name'] . ", " . $loguserarray['first_name'];
//         }
        $unamecd = $logininfo->loguname;
        
        $start_ts = strtotime($cf_start_date);
        $end_ts = strtotime($cf_end_date);
        
        $cf_duration = round(($end_ts - $start_ts) / 60);
        
        if (strlen($post['old_contact_form_id']) > 0) {
            $change_date = date('Y-m-d H:i:s', time());
            $change_user = $userid;
            
            $qa = Doctrine_Query::create()->update('Sapsymptom')
                ->set('isdelete', "1")
                ->set('change_user', "'" . $change_user . "'")
                ->set('change_date', "'" . $change_date . "'")
                ->where('visit_type = "contactform"')
                ->andWhere('visit_id = "' . $post['old_contact_form_id'] . '"')
                ->andWhere('ipid LIKE "' . $ipid . '"');
            $qa->execute();
        }
        
        if (! empty($post['fahrtstreke_km'])) {
            $gesamt_fahrstrecke_in_km = trim(str_replace(" km", "", $post['fahrtstreke_km'])) * 2;
        } elseif (! empty($post['fahrtstreke_km1'])) {
            $gesamt_fahrstrecke_in_km = trim(str_replace(" km", "", $post['fahrtstreke_km1'])) * 2;
        } else {
            $gesamt_fahrstrecke_in_km = '';
        }
        
        $total_duration = 0;
        $driving_time = 0;
        $documentation_time = 0;
        
        if ( ! empty($post['fahrtzeit']) && strlen($post['fahrtzeit']) > 0 && $post['fahrtzeit'] != '--') {
            $davon_fahrtzeit = $post['fahrtzeit'] * 2;
            $driving_time = $post['fahrtzeit'] * 2;
        } elseif ( ! empty($post['fahrtzeit1']) && strlen($post['fahrtzeit1']) > 0 && $post['fahrtzeit1'] != '--') {
            $davon_fahrtzeit = $post['fahrtzeit1'] * 2;
            $driving_time = $post['fahrtzeit1'] * 2;
        } else {
            $davon_fahrtzeit = '';
            $driving_time = '0';
        }
        
        if (! empty($post['fahrt_doc1'])) {
            $documentation_time = $post['fahrt_doc1'];
        } else {
            $documentation_time = '0';
        }
        
        $total_duration = $cf_duration + $driving_time + $documentation_time;
        
        if (is_array($post['symptom'])) {
            if (count($post['symptom']) > 0) {
                $sp = new Sapsymptom();
                $sp->ipid = $ipid;
                $sp->sapvalues = join(",", $post['symptom']);
                $sp->gesamt_zeit_in_minuten = $total_duration;
                $sp->gesamt_fahrstrecke_in_km = $gesamt_fahrstrecke_in_km;
                $sp->davon_fahrtzeit = $davon_fahrtzeit;
                
                // this is important
                $sp->visit_id = $result;
                $sp->visit_type = "contactform";
                $sp->save();
                
                $sapv_sym_id = $sp->id;
                
                $kuns = Doctrine_Core::getTable('Sapsymptom')->find($sapv_sym_id);
                $kunsarr = $kuns->toArray();
                if (count($kunsarr) > 0) {
                    $kuns_up = Doctrine::getTable('Sapsymptom')->find($sapv_sym_id);
                    $kuns_up->create_date = $cf_billable_date;
                    $kuns_up->save();
                }
                
                
                if ($allow_e_entries) {  

                    $defaultGroupCheckboxes = PatientHospizvizits::defaultGroupCheckboxes();
                    $defaultCheckboxes = PatientHospizvizits::defaultCheckboxes();
                    $defaultGroups = PatientHospizvizits::defaultGroups();

                    $pc_title_lines = [];
                    
                    foreach ($post['symptom'] as $symptomvalID) {
                        
                        
                        $cbGroup = array_filter($defaultGroupCheckboxes, function($mgc) use ($symptomvalID) {
                            return in_array($symptomvalID, $mgc);
                        });
                        
                        reset($cbGroup);
                        
                        if (isset($defaultGroups[key($cbGroup)]) && isset($defaultCheckboxes[$symptomvalID])) {
                            $pc_title_lines[] =  $defaultGroups[key($cbGroup)] . " : " . $defaultCheckboxes[$symptomvalID];
                        }
                        
                    }
                    
                   
                    if ( ! empty($post['symptom']) && ! empty($pc_title_lines) && trim($pc_title_lines) != ":") {
                        
                        $cust = new PatientCourse();
                        $cust->ipid = $ipid;
                        $cust->course_date = date("Y-m-d H:i:s", time());
                        $cust->course_type = Pms_CommonData::aesEncrypt("E");
                        $cust->course_title = Pms_CommonData::aesEncrypt(implode("\n", $pc_title_lines));
                        $cust->user_id = $userid;
                        $cust->done_date = $done_date;
                        $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                        $cust->done_id = $result;
                        
                        $cust->tabname = Pms_CommonData::aesEncrypt("comments_l_block");
                        
                        $cust->save();
                    
                    }
                    /*
                    
//                     $bavaria_options_tabname_encrypted = Pms_CommonData::aesEncrypt("bavaria_options_comments_l_block");
                    for ($i = 0; $i < count($post['comments_l']); $i ++) {
                        $cust = new PatientCourse();
                        $cust->ipid = $ipid;
                        $cust->course_date = date("Y-m-d H:i:s", time());
                        $cust->course_type = $course_type_E_encrypted;//Pms_CommonData::aesEncrypt("E");
                        $cust->course_title = Pms_CommonData::aesEncrypt(addslashes($post['comments_l'][$i]) . '');
                        $cust->user_id = $userid;
                        $cust->done_date = $done_date;
                        $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                        $cust->done_id = $result;

                        $cust->tabname = $bavaria_options_tabname_encrypted;
                        
                        $cust->save();
                        
//                         ddecho($cust->toArray());
                    }
                    */
               
                
                    /*
                    
                    if (strlen($post['old_contact_form_id']) > 0 && is_array($post['checkbox'])) {
    //                     $bavaria_options_tabname_encrypted = Pms_CommonData::aesEncrypt("bavaria_options_comments_LE_block");
                        foreach ($post['checkbox'] as $key => $val) {
                            if (is_array($post['upcomments'][$key])) {
                                foreach ($post['upcomments'][$key] as $keyid => $valk) {
                                    $cust = new PatientCourse();
                                    $cust->ipid = $ipid;
                                    $cust->course_date = date("Y-m-d H:i:s", time());
                                    $cust->course_type = $course_type_E_encrypted;//Pms_CommonData::aesEncrypt("E");
                                    $cust->course_title = Pms_CommonData::aesEncrypt("Ein bestehender LE Eintrag vom " . date("d.m.Y H:i:s", time()) . " wurde von " . $unamecd . " editiert " . $valk);
                                    $cust->user_id = $userid;
                                    
                                    $cust->tabname = $bavaria_options_tabname_encrypted;
                                    
                                    $cust->save();
                                    
    //                                 ddecho($cust->toArray());
                                }
                            }
                        }
                    }
                    
                    */
                    
                }
                 
            } else {
                //this is a failsafe and should not be reached... 
                //ispc-2071
                if (strlen($post['old_contact_form_id']) > 0) {
                    PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'comments_l_block');
                }
            }
            
            
            
        } elseif (strlen($post['fahrtstreke_km']) > 0 || strlen($post['fahrtzeit']) > 0 || strlen($post['fahrtstreke_km1']) > 0 || strlen($post['fahrtzeit1']) > 0) {
            $sp = new Sapsymptom();
            $sp->ipid = $ipid;
            
            $sp->gesamt_zeit_in_minuten = $total_duration;
            $sp->gesamt_fahrstrecke_in_km = $gesamt_fahrstrecke_in_km;
            $sp->davon_fahrtzeit = $davon_fahrtzeit;
            
            $sp->visit_id = $result;
            $sp->visit_type = "contactform";
            $sp->save();
            $sapv_sym_id = $sp->id;
            
            $kuns = Doctrine_Core::getTable('Sapsymptom')->find($sapv_sym_id);
            $kunsarr = $kuns->toArray();
            if (count($kunsarr) > 0) {
                $kuns_up = Doctrine::getTable('Sapsymptom')->find($sapv_sym_id);
                $kuns_up->create_date = $cf_billable_date;
                $kuns_up->save();
            }
            
            //ispc-2071
            if (strlen($post['old_contact_form_id']) > 0) {
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'comments_l_block');
            }
        } else {
            
            //ispc-2071
            if (strlen($post['old_contact_form_id']) > 0) {
                PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'comments_l_block');
            }
        }
        /* -------------------------------------------------------------------------------------------- */
        /* -------------------------------- END SAPV FB3------------------------------------------ */
        /* -------------------------------------------------------------------------------------------- */
        
        /* -------------------------------- BDT ACTIONS LE------------------------------------------ */
        if ($modulepriv_le) {
            // client actions
            $actions_data = XbdtActions::client_xbdt_actions($clientid);
            foreach ($actions_data as $k => $ac) {
                $client_action_details[$ac['id']] = $ac;
            }
            
            // get all client national holidays
            $nholiday = new NationalHolidays();
            $national_holidays_arr = $nholiday->getNationalHoliday($clientid, $current_period['start'], true);
            
            foreach ($national_holidays_arr as $k_natholiday => $v_natholiday) {
                $national_holidays[] = date('Y-m-d', strtotime($v_natholiday['NationalHolidays']['date']));
            }
            asort($national_holidays);
            $national_holidays = array_values($national_holidays);
            
            // get current price list
            $internal_invoices_pricelist = new InternalInvoicePriceList();
            $period['start'] = $cf_start_date;
            $period['end'] = $cf_end_date;
            
            $period_pricelist_products = $internal_invoices_pricelist->get_period_pricelist($period['start'], $period['end']);
            
            // get current group details
            $current_group_data = Usergroup::get_current_group_master($userid, $clientid);
            if ($current_group_data) {
                $usergroup = $current_group_data[$userid]['user_group'];
            }
            
            $condition = array();
            foreach ($period_pricelist_products['ap'] as $kp => $vp) {
                
                if ($vp['holiday'] == '1') {
                    $check_holiday = true;
                } else {
                    $check_holiday = false;
                }
                
                $cf_date = date("Y-m-d", strtotime($cf_start_date));
                
                if ($post['form_type'] == $vp['contactform_type'] && in_array($cf_date, $period_pricelist_products['lists_days'][$vp['list']]) && (($check_holiday && (in_array(date('Y-m-d', strtotime($cf_start_date)), $national_holidays) || date('w', strtotime($cf_start_date)) == '0' || date('w', strtotime($cf_start_date)) == '6')) || (! $check_holiday && ! in_array(date('Y-m-d', strtotime($cf_start_date)), $national_holidays) && date('w', strtotime($cf_start_date)) != '0' && date('w', strtotime($cf_start_date)) != '6'))) {
                    
                    // minute duration
                    
                    $le_start_ts = strtotime($cf_start_date);
                    $le_end_ts = strtotime($cf_end_date);
                    
                    $le_cf_duration = round(($le_end_ts - $le_start_ts) / 60);
                    
                    // check range duration
                    if ($vp['range_type'] == 'min') {
                        if ($le_cf_duration >= $vp['range_start'] && $le_cf_duration <= $vp['range_end']) {
                            $condition[$vp['id']]['range'] = '1';
                        }
                    }
                    
                    // check range distance
                    if ($vp['range_type'] == 'km') {
                        $clean_km_string = str_replace(' km', '', trim(rtrim($post['fahrtstreke_km'])));
                        
                        if ($clean_km_string >= $vp['km_range_start'] && $clean_km_string <= $vp['km_range_end']) {
                            $condition[$vp['id']]['range'] = '1';
                        }
                    }
                    
                    // check which time we use for reference
                    $vp_ts['time_start'] = strtotime('1970-01-01 ' . $vp['time_start'] . ':00');
                    $vp_ts['time_end'] = strtotime('1970-01-01 ' . $vp['time_end'] . ':00');
                    $constant_midnight = strtotime('1970-01-01 00:00:00');
                    if ($vp['calculation_trigger'] == 'time_start') {
                        // use contact form start_date
                        $start_cf = strtotime(date('1970-01-01 H:i:s', strtotime($cf_start_date)));
                    } else 
                        if ($vp['calculation_trigger'] == 'time_end') {
                            // use contact form end_date
                            $start_cf = strtotime(date('1970-01-01 H:i:s', strtotime($cf_end_date)));
                        }
                    
                    // hours condition
                    if ($vp_ts['time_start'] < $vp_ts['time_end']) // 08-20 normal interval
{
                        if (($start_cf >= $vp_ts['time_start'] && $start_cf < $vp_ts['time_end']) && $condition[$vp['id']]['range'] == '1') {
                            $condition[$vp['id']]['time_interval'] = '1';
                        }
                    } else 
                        if ($vp_ts['time_start'] > $vp_ts['time_end'] || ($start_cf >= $constant_midnight && $start_cf < $vp_ts['time_end'])) 
                        // 20-08 interval (overnight)
                        {
                            if ((($start_cf >= $vp_ts['time_end'] && $start_cf >= $vp_ts['time_start']) || ($start_cf < $vp_ts['time_end'])) && $condition[$vp['id']]['range'] == '1') {
                                $condition[$vp['id']]['time_interval'] = '1';
                            }
                        }
                    
                    if ($usergroup == $vp['usergroup']) {
                        $condition[$vp['id']]['usergroup'] = '1';
                    } else {
                        $condition[$vp['id']]['usergroup'] = '0';
                    }
                    
                    if ($condition[$vp['id']]['usergroup'] == "1" && $condition[$vp['id']]['range'] == '1' && $condition[$vp['id']]['time_interval'] == '1') {
                        // TODO-898 if you EDIT a contact form where actions are triggered from then after saving the edited form the actions are added TWICE to the list
                        $old_pc_arr = array();
                        $xbdtact_old_arr = array();
                        $old_contact_forms_ids = array();
                        
                        if ($old_contact_form !== false) {
                            // get all LE
                            $pc = new PatientCourse();
                            
                            $old_pc = $pc->getCourseDetailsByIpidAndShortcut($ipid, "LE");
                            
                            if (is_array($old_pc)) {
                                
                                foreach ($old_pc as $k => $v) {
                                    $old_pc_arr[$v['recordid']] = $v;
                                }
                                
                                $old_xbdtaction_id = array_column($old_pc, 'recordid');
                                $xbdtact = new PatientXbdtActions();
                                $xbdtact_old = $xbdtact->get_actions_by_ipid_id($ipid, $old_xbdtaction_id);
                                
                                foreach ($xbdtact_old as $k => $v) {
                                    $xbdtact_old_arr[$v['id']] = $v;
                                }
                            }
                            
                            // get the child contact_forms
                            $old_contact_forms_ids = $cform->get_child_forms($result, true);
                        }
                        
                        // get all actions of product and insert them
                        foreach ($vp['actions'] as $k => $action_details) {
                            // TODO-898
                            $xbdt_action_exists = false;
                            // compare course_LE done_id
                            foreach ($old_pc_arr as $k => $v) {
                                if ($v['done_id'] > 0 && in_array($v['done_id'], $old_contact_forms_ids) && isset($xbdtact_old_arr[$v['recordid']]) && $xbdtact_old_arr[$v['recordid']]['action'] == $action_details['action_id']) {
                                    $xbdt_action_exists = true;
                                    break;
                                }
                            }
                            // compare by using recordid and done_date ... pre-existent values before this change
                            if (! $xbdt_action_exists)
                                foreach ($xbdtact_old_arr as $k => $v) {
                                    
                                    $timestamp_pc = strtotime($old_pc_arr[$v['id']]['done_date']);
                                    $timestamp_xbdt = strtotime($v['action_date']);
                                    
                                    if (isset($old_pc_arr[$v['id']]) && $timestamp_pc == $timestamp_xbdt && $v['action'] == $action_details['action_id']) {
                                        $xbdt_action_exists = true;
                                        break;
                                    }
                                }
                            
                            if ($xbdt_action_exists) {
                                continue;
                            }
                            // insert in patient
                            $xbdtact_insert = new PatientXbdtActions();
                            $xbdtact_insert->clientid = $clientid;
                            $xbdtact_insert->userid = $userid;
                            $xbdtact_insert->team = "0";
                            $xbdtact_insert->ipid = $ipid;
                            $xbdtact_insert->action = $action_details['action_id'];
                            $xbdtact_insert->action_date = $done_date;
                            ;
                            $xbdtact_insert->save();
                            $xbdtaction_id = $xbdtact_insert->id;
                            
                            // insert in patient course
                            $course_comment = "";
                            $course_comment = $action_details['action_id'] . ' |____| ' . $client_action_details[$action_details['action_id']]['action_id'] . ' |____| ' . $client_action_details[$action_details['action_id']]['name'] . ' |____| ' . $userid . ' |____| ' . date('d.m.Y H:i', strtotime($done_date));
                            $cust = new PatientCourse();
                            $cust->ipid = $ipid;
                            $cust->course_date = date("Y-m-d H:i:s", time());
                            $cust->course_type = Pms_CommonData::aesEncrypt("LE");
                            $cust->course_title = Pms_CommonData::aesEncrypt(addslashes($course_comment));
                            $cust->user_id = $userid;
                            if ($modulepriv_le && $xbdtaction_id) {
                                $cust->recordid = $xbdtaction_id;
                            }
                            $cust->done_date = $done_date;
                            $cust->done_id = $result; // holds the contact_form id
                            $cust->done_name = "le_verlauf";
                            $cust->save();
                            $insid = $cust->id;
                            
                            // update pateint course
                            if ($xbdtaction_id && $modulepriv_le) {
                                $update_xbdtaction = Doctrine::getTable('PatientXbdtActions')->find($xbdtaction_id);
                                $update_xbdtaction->course_id = $insid;
                                $update_xbdtaction->save();
                            }
                        }
                    }
                }
            }
        }
        /* -------------------------------------------------------------------------------------------- */
        /* -------------------------------- BDT ACTIONS LE FB3------------------------------------------ */
        /* -------------------------------------------------------------------------------------------- */
        
        if ($stmb->id > 0) {
            return $stmb->id;
        } else {
            return false;
        }
    }

    public function UpdateContactFormsSympt($post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $contact_form_id = $post['contact_form_id'];
        $old_contact_form_id = $post['old_contact_form_id'];
        $now = time();
        
        $cl = new Client();
        $clarr = Pms_CommonData::getClientData($clientid);
        $sympt_view_select = $clarr[0]['symptomatology_scale']; // n-> Numbers Scale(0-10); a-> Attributes scale (none/weak/averge/strong)
        
        if ($sympt_view_select == 'a') {
            $none = array(
                0
            );
            $weak = array(
                1,
                2,
                3,
                4
            );
            $average = array(
                5,
                6,
                7
            );
            $strong = array(
                8,
                9,
                10
            );
            $symptom_mapping = array(
                "0" => 'kein',
                "1" => 'leicht',
                "2" => 'leicht',
                "3" => 'leicht',
                "4" => 'leicht',
                "5" => 'mittel',
                "6" => 'mittel',
                "7" => 'mittel',
                "8" => 'schwer',
                "9" => 'schwer',
                "10" => 'schwer'
            );
        } else {
            $symptom_mapping = array(
                "0" => '0',
                "1" => '1',
                "2" => '2',
                "3" => '3',
                "4" => '4',
                "5" => '5',
                "6" => '6',
                "7" => '7',
                "8" => '8',
                "9" => '9',
                "10" => '10'
            );
        }
        
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', $now)));
        
        $saved_symp = array();
        if (strlen($old_contact_form_id > 0)) {
            $cf_symptpms = new ContactFormsSymp();
            $saved_symp = $cf_symptpms->getContactFormsSymp($post['old_contact_form_id'], $ipid);
            
            $symp_arr_q = Doctrine_Query::create()->select("*")
                ->from('Symptomatology')
                ->where('kvnoid = "c' . $old_contact_form_id . '"')
                ->andWhere('ipid=?', $ipid);
            $old_symp_arr = $symp_arr_q->fetchArray();
            
            foreach ($old_symp_arr as $k => $val) {
                // $saved_symps[$val['setid']][$val['symptomid']] = $val['input_value'];
                $saved_symps[$val['symptomid']] = $val['input_value'];
            }
        }
        
        foreach ($post['symp'] as $setid => $sympt_values) {
            if (is_array($sympt_values['input_value']) && sizeof($sympt_values['input_value']) > 0) {
                $a_post = $sympt_values;
                $a_post['ipid'] = $ipid;
                $save_symp = 0;
                foreach ($sympt_values['input_value'] as $val) {
                    if (strlen($val) > 0) {
                        $save_symp = 1;
                    }
                }
                
                if ($save_symp == 1) {
                    
                    // delete all patient symptomatology for current form
                    $upd_sym = Doctrine_Query::create()->delete('Symptomatology')
                        ->where('kvnoid = "c' . $old_contact_form_id . '"')
                        ->andWhere('ipid LIKE "' . $ipid . '"');
                    $upd_sym->execute();
                    
                    $upd_contactsym = Doctrine_Query::create()->delete('ContactFormsSymp')
                        ->where('contact_form_id = "' . $old_contact_form_id . '"')
                        ->andWhere('ipid LIKE "' . $ipid . '"');
                    $upd_contactsym->execute();
                    
                    $patient_form = new Application_Form_PatientSymptomatology();
                    $a_post['iskvno'] = 1;
                    $a_post['kvnoid'] = 'c' . $contact_form_id; // "c" is for contact form
                    $a_post['edit_entry_date'] = $done_date;
                    $patient_form->InsertData($a_post);
                    
                    $current_values = $sympt_values['current_value'];
                    $comments = $sympt_values['comment'];
                    
                    $details_array = array();
                    $existing_values = array();
                    // $tocourse ['details'] = array();
                    $tocourse = array();
                    
                    $coursecomment = [];
                    
                    $change_date = ' [' . date("d.m.Y H:i", time()) . ']';
                    foreach ($sympt_values['input_value'] as $symp_id => $val) {
                        if (strlen($val) > 0) {
                            $sympvals = new ContactFormsSymp();
                            $sympvals->contact_form_id = $contact_form_id;
                            $sympvals->ipid = $ipid;
                            $sympvals->symp_id = $symp_id;
                            $sympvals->last_value = ($current_values[$symp_id] == '' ? NULL : $current_values[$symp_id]);
                            $sympvals->current_value = ($val == '' ? NULL : $val);
                            $sympvals->comment = htmlspecialchars($comments[$symp_id]);
                            $sympvals->save();
                            
                            //ispc-2071, allways save last post
                            //if ($saved_symps[$symp_id] != $val) {
                                
                                // $tocourse['input_value'] = $symptom_mapping[$current_values [$symp_id]] .' --> '.$symptom_mapping[$val];
                                $tocourse['input_value'] = $val;
                                $tocourse['symptid'] = $symp_id;
                                $tocourse['iskvno'] = '0';
                                $tocourse['setid'] = "1";
                                //ispc-2071
//                                 $tocourse['second_value'] = $symptom_mapping[$saved_symps[$symp_id]] . ' --> NEW' . $symptom_mapping[$val] . $change_date . " \n " . htmlspecialchars($comments[$symp_id]);
                                $tocourse['second_value'] = htmlspecialchars($comments[$symp_id]);
                                
                                $coursecomment[] = $tocourse;
                            
                            //}
                            
                            
                            // else{
                            // $tocourse['input_value'] = $val;
                            // $tocourse['symptid'] = $symp_id;
                            // $tocourse['iskvno'] = '0';
                            // $tocourse['setid'] = "1";
                            // $tocourse['second_value'] = htmlspecialchars($comments[$symp_id]);
                            // $coursecomment[] = $tocourse;
                            // }
                            if ($sympvals->id > 0) {
                                $entry_id = $sympvals->id;
                                foreach ($sympt_values['details_multiple_select'][$symp_id] as $k => $select_value) {
                                    if ($select_value != 0 && ! in_array($select_value, $existing_values)) {
                                        $existing_values[] = $select_value;
                                        
                                        $details_array[] = array(
                                            "contact_form_id" => $contact_form_id,
                                            "entry_id" => $entry_id,
                                            "detail_id" => $select_value
                                        );
                                    }
                                }
                            }
                        } else {
                            
                            //ispc-2071
                            /*
                            if ($saved_symps[$symp_id] != "") {
                                
                                // $tocourse['input_value'] = $symptom_mapping[$current_values [$symp_id]] .' --> '." ";
                                $tocourse['input_value'] = $saved_symps[$symp_id] . ' --> ' . " ";
                                $tocourse['symptid'] = $symp_id;
                                $tocourse['iskvno'] = '0';
                                $tocourse['setid'] = "1";
                                $tocourse['second_value'] = $symptom_mapping[$saved_symps[$symp_id]] . ' --> ' . " " . $change_date . "\n" . htmlspecialchars($comments[$symp_id]);
                                $coursecomment[] = $tocourse;
                            }
                            */
                        }
                    }
                    
                    if (! empty($details_array)) {
                        $collection = new Doctrine_Collection('ContactFormsSympDetails');
                        $collection->fromArray($details_array);
                        $collection->save();
                    }
                } else {
                    
                    //remove pc from the older forms, you just cleared the entire block
                    //ispc-2071
                    PatientCourse::setIsRemovedByIpidAndContactFormAndTabname($ipid, $post['old_contact_form_id'], 'PatientSymptomatology');       
                    
                    
                }
            }
        }
        // insert changes to symptomatics
        
        if (! empty($coursecomment)) {
            $cust = new PatientCourse();
            $cust->ipid = $ipid;
            $cust->course_date = date("Y-m-d H:i:s", time());
            $cust->course_type = Pms_CommonData::aesEncrypt("S");
            $cust->course_title = Pms_CommonData::aesEncrypt(serialize($coursecomment));
            $cust->isserialized = 1;
            $cust->user_id = $userid;
            
            /*
             * ISPC-2071
             * explaining how this `beauty` works
             * + Symptome = symp block update
             * + Symptome ZAPV I = symp_zapv block update
             * + Symptome ZAPV II = symp_zapv_complex block update
             *
             * ispc-2071 added this tabname
             */
            $cust->tabname = Pms_CommonData::aesEncrypt("PatientSymptomatology"); 
            
            $cust->done_date = $done_date;
            $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
//             $cust->done_id = $result; // $result is undefined... yafp .. i only removeit for the NOTICE..  
            $cust->save();
        }
        
        $qa = Doctrine_Query::create()->update('PatientCourse')
            ->set('done_date', "'" . $done_date . "'")
            ->where('done_name = AES_ENCRYPT("contact_form", "' . Zend_Registry::get('salt') . '")')
            ->andWhere('done_id = "' . $contact_form_id . '"')
            ->andWhere('ipid LIKE "' . $ipid . '"');
        $qa->execute();
    }

    public function UpdateContactFormsSympt_old($post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $contact_form_id = $post['contact_form_id'];
        $old_contact_form_id = $post['old_contact_form_id'];
        
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
        
        if (is_array($post['input_value']) && sizeof($post['input_value']) > 0) {
            $a_post = $post;
            $a_post['ipid'] = $ipid;
            $save_symp = 0;
            foreach ($a_post['input_value'] as $val) {
                if (strlen($val) > 0) {
                    $save_symp = 1;
                }
            }
            if ($save_symp == 1) {
                // delete all patient symptomatology for current form
                $upd_sym = Doctrine_Query::create()->delete('Symptomatology')
                    ->where('kvnoid = "c' . $old_contact_form_id . '"')
                    ->andWhere('ipid LIKE "' . $ipid . '"');
                $upd_sym->execute();
                
                $upd_contactsym = Doctrine_Query::create()->delete('ContactFormsSymp')
                    ->where('contact_form_id = "' . $old_contact_form_id . '"')
                    ->andWhere('ipid LIKE "' . $ipid . '"');
                $upd_contactsym->execute();
                
                $patient_form = new Application_Form_PatientSymptomatology();
                $a_post['iskvno'] = 1;
                $a_post['kvnoid'] = 'c' . $contact_form_id; // "c" is for contact form
                $a_post['edit_entry_date'] = $done_date;
                $patient_form->InsertData($a_post);
                
                $current_values = $post['current_value'];
                $comments = $post['comment'];
                foreach ($post['input_value'] as $symp_id => $val) {
                    if (strlen($val) > 0) {
                        $sympvals = new ContactFormsSymp();
                        $sympvals->contact_form_id = $contact_form_id;
                        $sympvals->ipid = $ipid;
                        $sympvals->symp_id = $symp_id;
                        $sympvals->last_value = ($current_values[$symp_id] == '' ? NULL : $current_values[$symp_id]);
                        $sympvals->current_value = ($val == '' ? NULL : $val);
                        $sympvals->comment = htmlspecialchars($comments[$symp_id]);
                        $sympvals->save();
                    }
                }
            }
        }
        
        $qa = Doctrine_Query::create()->update('PatientCourse')
            ->set('done_date', "'" . $done_date . "'")
            ->where('done_name = AES_ENCRYPT("contact_form", "' . Zend_Registry::get('salt') . '")')
            ->andWhere('done_id = "' . $contact_form_id . '"')
            ->andWhere('ipid LIKE "' . $ipid . '"');
        $qa->execute();
    }

    private function get_first_contactform($parent_id)
    {
        // get first added cf using parent id
        $select = Doctrine_Query::create()->select('*')
            ->from('ContactForms')
            ->where('parent = "' . $parent_id . '"')
            ->andWhere('isdelete = "1"')
            ->orderBy('id ASC')
            ->limit(1);
        $select_res = $select->fetchArray();
        
        if ($select_res) {
            return $select_res[0];
        } else {
            return false;
        }
    }
}

?>