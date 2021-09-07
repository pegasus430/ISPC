<?php

require_once("Pms/Form.php");

class Application_Form_UserSettings extends Pms_Form
{
// 	public function InsertData($post, $userid)
// 	{
// 		$ust = new UserSettings();
		
// 		$ust->userid = $userid;
// 		$ust->calendar_visit_color = $post['calendar_visit_color'];
// 		$ust->calendar_visit_text_color = $post['calendar_visit_text_color'];
// 		$ust->save();

// 	}

    /*
     * @author claudiu on 01.02.2018 changed the fn into findOrCreateOneBy (
     * this fn acts both as insert or update
	 * entity UserSettings was created with the idea of User hasOne UserSettings
     */ 
	public function UpdateData($post = array())
	{
	    //ISPC-2138
	    //we save in db the icons user DOSEN'T want displayed in the patient
	    
	    //on new USER, this marker sent: hidden_patient_icons=0
	    // !! Attention !! if you set $post['hidden_patient_icons'] = 0 then this field is NOT updated
	    if ($post['hidden_patient_icons'] === 0) {
	        
	        unset($post['hidden_patient_icons']);
	        
	    } else {
    	    $group_allowed_icons = GroupIconsDefaultPermissions::getDetailedInfo($post['userid']);
    	    
    	    if ( ! empty($group_allowed_icons)) {
        	    $system_icons = array_keys($group_allowed_icons['system']);
        	    $custom_icons = array_keys($group_allowed_icons['custom']);
        	    
        	    
        	    if ( ! empty($system_icons)) {
        	        if(empty($post['hidden_patient_icons']['system'])) {
        	            $post['hidden_patient_icons']['system'] = array();
        	        }
        	        $system_icons_unchecked = array_diff($system_icons, $post['hidden_patient_icons']['system']);
        	        $post['hidden_patient_icons']['system'] = $system_icons_unchecked;
        	    }
        	    
        	    if ( ! empty($custom_icons)) {
        	        if(empty($post['hidden_patient_icons']['custom'])) {
        	            $post['hidden_patient_icons']['custom'] = array();
        	        }
        	        $custom_icons_unchecked = array_diff($custom_icons, $post['hidden_patient_icons']['custom']);
                    $post['hidden_patient_icons']['custom'] = $custom_icons_unchecked;
        	    }
    	    } else {
    	        //error
    	        unset($post['hidden_patient_icons']);
    	    }
	    }
	    
	    
	    if ( ! isset($post['patient_contactphone'])) {
	        $post['patient_contactphone'] = '';
	    }
	    
	    if ( ! isset($post['topmenu'])) {
	        $post['topmenu'] = Doctrine_Core::NULL_NATURAL;
	    }
	    
	    
	    $field = ! empty($post['id']) ? "id" : (! empty($post['userid']) ? 'userid' : null); 
	    $value = ! empty($post['id']) ? $post['id'] : (! empty($post['userid']) ? $post['userid'] : null); 

	    $entity = new UserSettings();
	    return $entity->findOrCreateOneBy($field, $value, $post);
	    
	
	}
	
}

?>