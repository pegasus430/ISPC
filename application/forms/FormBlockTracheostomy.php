<?php
// require_once ("Pms/Form.php");
/**
 * @update Jan 25, 2018: @author claudiu, checked/modified for ISPC-2071
 * tracheostomy = Trachealkanüle = FormBlockTracheostomy
 *
 * changed: bypass Trigger() on PC
 */
class Application_Form_FormBlockTracheostomy extends Pms_Form
{

    public function clear_block_data($ipid = 0, $contact_form_id = '')
    {
        if (! empty($contact_form_id)) {
            $Q = Doctrine_Query::create()->update('FormBlockTracheostomy')
                ->set('isdelete', '1')
                ->where("contact_form_id = ?", $contact_form_id)
                ->andWhere('ipid = ?', $ipid);
            $result = $Q->execute();
            
            return true;
        } else {
            return false;
        }
    }

    public function InsertData($post, $allowed_blocks)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $save_2_PC = false; //if we have insert or update on PatientCourse
        $change_date = '';
        $course_str = '';
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));
        
        $vt_block = new FormBlockTracheostomy();
        
        // format datapicker 'dd.mm.yy' to mysql date
        if ( ! empty($post['tracheostomy']['last_change']) 
            && ($datetime = DateTime::createFromFormat("d.m.Y", $post['tracheostomy']['last_change']))) {
            
            $post['tracheostomy']['last_change'] = $datetime->format("Y-m-d");
        }
        
        
        if ( ! empty($post['old_contact_form_id'])) {
        
            $change_date = $post['contact_form_change_date'];
        
            $vt_old_data = $vt_block->getPatientFormBlockTracheostomy($post['ipid'], $post['old_contact_form_id'], true);
            
        
            if ( ! in_array('tracheostomy', $allowed_blocks)) {
                // override post data if no permissions on block
                // PatientCourse will NOT be inserted
                if ( ! empty($vt_old_data[0])) {
                    $post['tracheostomy']['size'] = $vt_old_data[0]['size'];
                    $post['tracheostomy']['designation'] = $vt_old_data[0]['designation'];
                    $post['tracheostomy']['company'] = $vt_old_data[0]['company'];
                    $post['tracheostomy']['speaking_cannula'] = $vt_old_data[0]['speaking_cannula'];
                    $post['tracheostomy']['cuff_pressure'] = $vt_old_data[0]['cuff_pressure'];
                    $post['tracheostomy']['change_interval'] = $vt_old_data[0]['change_interval'];
                    $post['tracheostomy']['last_change'] = $vt_old_data[0]['last_change'];
                    $post['tracheostomy']['change_by'] = $vt_old_data[0]['change_by'];
                }
            }
            else {
                //we have permissions and cf is being edited
                //write changes in PatientCourse is something was changed
                if ( ! empty($vt_old_data[0])) {
        
                    if ($post['tracheostomy']['size'] != $vt_old_data[0]['size']
                        || $post['tracheostomy']['designation'] != $vt_old_data[0]['designation']
                        || $post['tracheostomy']['company'] != $vt_old_data[0]['company']
                        || $post['tracheostomy']['speaking_cannula'] != $vt_old_data[0]['speaking_cannula']
                        || $post['tracheostomy']['cuff_pressure'] != $vt_old_data[0]['cuff_pressure']
                        || $post['tracheostomy']['change_interval'] != $vt_old_data[0]['change_interval']
                        || $post['tracheostomy']['last_change'] != $vt_old_data[0]['last_change']
                        || $post['tracheostomy']['change_by'] != $vt_old_data[0]['change_by'])
                    {
                        //something changed
                        $save_2_PC = true;
                    }
                    
                }
                else {
                    //nothing was edited last time, or this block was added after the form was created
                    $save_2_PC = true;
                    $change_date = '';
                     
                }
            }
        } else {
            //new cf, save
            /**
             * ISPC-1787
             * When the user opens a new cntact form, the values are
             * prefilled with last filled values and CAN get changed.
             * it gets added to verlauf ONLY if something is changed.
             */
            $last_old_data = $vt_block->getPatientFormBlockTracheostomy($post['ipid'], false, true, false, 1);
            
            if (empty($last_old_data[0])) {
                //this is the first time you save this block for this patient
                $save_2_PC = true;
            }
            elseif ($post['tracheostomy']['size'] != $last_old_data[0]['size']
                || $post['tracheostomy']['designation'] != $last_old_data[0]['designation']
                || $post['tracheostomy']['company'] != $last_old_data[0]['company']
                || $post['tracheostomy']['speaking_cannula'] != $last_old_data[0]['speaking_cannula']
                || $post['tracheostomy']['cuff_pressure'] != $last_old_data[0]['cuff_pressure']
                || $post['tracheostomy']['change_interval'] != $last_old_data[0]['change_interval']
//                 || $post['tracheostomy']['last_change'] != $last_old_data[0]['last_change']
                || $post['tracheostomy']['change_by'] != $last_old_data[0]['change_by'])
            {
                //something changed from the last time you edited this block in another cf
                $save_2_PC = true;
            }
           
            
        }
        
        
        //set the old block values as isdelete
        $clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);
        
        
        $vt_block->ipid = $post['ipid'];
        $vt_block->contact_form_id = $post['contact_form_id'];
        $vt_block->size = $post['tracheostomy']['size'];
        $vt_block->designation = $post['tracheostomy']['designation'];
        $vt_block->company = $post['tracheostomy']['company'];
        $vt_block->speaking_cannula = $post['tracheostomy']['speaking_cannula'];
        $vt_block->cuff_pressure = $post['tracheostomy']['cuff_pressure'];
        $vt_block->change_interval = $post['tracheostomy']['change_interval'];
        $vt_block->last_change = $post['tracheostomy']['last_change'];
        $vt_block->change_by = $post['tracheostomy']['change_by'];
        
        
        
        if ($save_2_PC && in_array('tracheostomy', $allowed_blocks)) {
        
            $course_title_line = array();
            
            if ($post['tracheostomy']['size'] != '') {
                $course_title_line[] = $this->translate('lang_block_tracheostomy_size') . ": " . $post['tracheostomy']['size'];
            }
            
            if ($post['tracheostomy']['designation'] != '') {
                $course_title_line[] = $this->translate('lang_block_tracheostomy_designation') . ": " . $post['tracheostomy']['designation'];
            }
            
            if ($post['tracheostomy']['company'] != '') {
                $course_title_line[] = $this->translate('lang_block_tracheostomy_company') . ": " . $post['tracheostomy']['company'];
            }
            
            if ($post['tracheostomy']['speaking_cannula'] != '0') {
                if ($post['tracheostomy']['speaking_cannula'] == 1) {
                    $course_title_line[] = $this->translate('lang_block_tracheostomy_speaking_cannula') . ": " . $this->translate('option_yes');
                } elseif ($post['tracheostomy']['speaking_cannula'] == 2) {
                    $course_title_line[] = $this->translate('lang_block_tracheostomy_speaking_cannula') . ": " . $this->translate('option_no');
                }
            }
            
            if ($post['tracheostomy']['cuff_pressure'] != '') {
                $course_title_line[] = $this->translate('lang_block_tracheostomy_cuff_pressure') . ": " . $post['tracheostomy']['cuff_pressure'];
            }
            
            if ($post['tracheostomy']['change_interval'] != '') {
                $course_title_line[] = $this->translate('lang_block_tracheostomy_change_interval') . ": " . $post['tracheostomy']['change_interval'];
            }
            
            
            if ($post['tracheostomy']['change_by'] != '') {
                $course_title_line[] = $this->translate('lang_block_tracheostomy_change_by') . ": " . $post['tracheostomy']['change_by'];
            }
            
            if ( ! empty($course_title_line) && $post['tracheostomy']['last_change'] != '') {
                $course_title_line[] = $this->translate('lang_block_tracheostomy_last_change') . ": " . date('d.m.Y ', strtotime($post['tracheostomy']['last_change']));
            }
            
            if (empty($course_title_line)) {
                //you unchecked all the options
                //must remove from PC this option
                //manualy remove and set $save_2_PC false
                $save_2_PC =  false;
                
                if ( ! empty($post['old_contact_form_id'])) {
                    $pc_entity = new PatientCourse();
                    $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockTracheostomy');
                }
                
            } elseif ($pc_listener = $vt_block->getListener()->get('PostInsertWriteToPatientCourse')) {
                
                    $course_str =  $this->translate('lang_block_tracheostomy_title') . ": " . implode("; ", $course_title_line);
                    $change_date = "";//removed from pc; ISPC-2071
                    
                    $pc_listener->setOption('disabled', false);
                    $pc_listener->setOption('course_title', $course_str . $change_date);
                    $pc_listener->setOption('done_date', $done_date);
                    $pc_listener->setOption('user_id', $userid);
                
            }
        
        }
        
        $vt_block->save();
    }
}

?>