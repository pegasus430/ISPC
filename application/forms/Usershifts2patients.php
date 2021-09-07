<?php
/**
 *
 * @author Ancuta
 *
 * 20.09.2018
 *
 */
require_once ("Pms/Form.php");

class Application_Form_Usershifts2patients extends Pms_Form
{

    public function save_form_data($clientid, $data = array())
    {
        if (empty($clientid)) {
            return;
        }
        
        $post = $data;
        $post_epids = array();
        foreach ($post['form_data'] as $epid => $shifts_data_array) {
            $post_epids[] = $epid;
        }
        
        if (empty($post_epids)) {
            return;
        }
        
        $epid_data = Doctrine_Query::create()->select('epid,ipid,clientid')
            ->from('EpidIpidMapping INDEXBY epid')
            ->where("clientid =? ", $clientid)
            ->andWhereIn('epid', $post_epids)
            ->fetchArray();
        
        

        //delete now, insert after
        
        $this->clear_roster_by_cells($clientid, $post, $epid_data);
        
        foreach ($post['form_data'] as $epid => $shifts_data) {
            foreach ($shifts_data as $shift => $shift_days) {
                foreach ($shift_days as $strtotime_day => $user_id) {
                    
                    $post_arr[] = array(
                        'clientid' => $epid_data[$epid]['clientid'],
                        'ipid' => $epid_data[$epid]['ipid'],
                        'shift_type' => $shift,
                        'userid' => $user_id,
                        'shift_date' => date('Y-m-d', $strtotime_day)
                    );
                }
            }
        }
        
        if (! empty($post_arr)) {
            $collection = new Doctrine_Collection('Usershifts2patients');
            $collection->fromArray($post_arr);
            $collection->save();
            
            return $post_arr;
        } else {
            return false;
        }
    }
    
    
    
    public function clear_roster_by_cells ( $clientid, $post , $epid_data )
    {
        
        if(!empty($clientid)) {
            	
            $logininfo = new Zend_Session_Namespace('Login_Info');
    
            //deleted days
    
            $deleted_cells = explode(',',$post['form_data']['deleted_cells']);
            $deleted_cells = array_unique($deleted_cells);
           
            
            
            
            if(!empty($deleted_cells) && is_array($deleted_cells)) {
                foreach($deleted_cells as $cell) {
                    if(!empty($cell)) {
                        $cell_exp = explode('_', $cell);
                        $delete_epids[] = $cell_exp[1];
                    }
                }
            }
            
            $row_del_epid_data = array();
            $row_del_epid_data = $epid_data;
            if(!empty($delete_epids)){
                $delete_epid_data = Doctrine_Query::create()->select('epid,ipid,clientid')
                ->from('EpidIpidMapping INDEXBY epid')
                ->where("clientid =? ", $clientid)
                ->andWhereIn('epid', $delete_epids)
                ->fetchArray();
                $row_del_epid_data =array_merge($row_del_epid_data,$delete_epid_data);
            }
            
            
            if(!empty($deleted_cells) && is_array($deleted_cells)) {
                foreach($deleted_cells as $cell) {
                    if(!empty($cell)) {
                        $cell_exp = explode('_', $cell);
                        $cell_date = date('Y-m-d', $cell_exp[0]);
                        
                        if($cell_date && !empty($cell_exp[1]) && (!empty($cell_exp[2]) || $cell_exp[2] == '0')) {
                            $del_q = Doctrine_Query::create()
                            ->update('Usershifts2patients')
                            ->set('isdelete', "1")
                            ->set('change_date', 'NOW()')
                            ->set('change_user', $logininfo->userid)
                            ->where("clientid = ?", $clientid)
                            ->andWhere('shift_date = ?', $cell_date)
                            ->andWhere('ipid = ?', $row_del_epid_data[$cell_exp[1]]['ipid'])
                            ->andWhere('shift_type = ?', $cell_exp[2] )
                            ->andWhere('isdelete = "0"');
                            $del_q->execute();
                        }
                    }
                }
            }
            
            //delete days that have shifts too, just to be safe
            foreach ($post['form_data'] as $patient_epid => $patient2shifts)
            {
                foreach ($patient2shifts as $shift_type => $shift_data)
                {
                    foreach ($shift_data  as $shift_strtotime => $userid)
                    {
                        $date = date('Y-m-d', $shift_strtotime);
                        if (!empty($userid) || $userid == '0' && !empty($date))
                        {
                            $del_q = Doctrine_Query::create()
                            ->update('Usershifts2patients')
                            ->set('isdelete', "1")
                            ->set('change_date', 'NOW()')
                            ->set('change_user', $logininfo->userid)
                            ->where("clientid = ?",$clientid )
                            ->andWhere('shift_date = ?', $date )
                            ->andWhere('ipid = ?', $row_del_epid_data[$patient_epid]['ipid'])
                            ->andWhere('userid = ?', $userid)
                            ->andWhere('shift_type = ?', $shift_type )
                            ->andWhere('isdelete = "0"');
                            $del_q->execute();
                        }
                    }
                }
            }
        }
    
    }
    
    
}

?>