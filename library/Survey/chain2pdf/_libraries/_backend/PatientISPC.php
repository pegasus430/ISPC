<?php

class Patient{
    
    var $contact_person_required = array (
        'first_name' => array (
            'text' => 'first name',
            'noempty' => true
        ) ,
        'last_name' => array (
            'text' => 'last name',
            'noempty' => true
        ) ,
        
        'type' => array (
            'text' => 'type',
            'noempty' => true
        ) ,
        
        'city' => array (
            'text' => 'city',
            'noempty' => true
        ) ,
        
        
        
    );
    
    function __construct()
    {
        $this->db = $GLOBALS['db'];
    }

    function get_patient($patient_id = false, $field = 'id')
    {
        
        $query = 'SELECT *, '.sql_aes_decrypt().', DATE_FORMAT('.sql_aes_decrypt('dob', true).', "%d.%m.%Y") AS `dob_formatted`,
		DATE_FORMAT(NOW(), "%Y") - DATE_FORMAT('.sql_aes_decrypt('dob', true).', "%Y") - (DATE_FORMAT(NOW(), "00-%m-%d") < DATE_FORMAT('.sql_aes_decrypt('dob', true).', "00-%m-%d")) AS patient_age
		FROM ' . TABLE_PREFIX . '_patient `p` WHERE deleted = 0 AND `'.$field.'` = "' . string_clean($patient_id, 'db') . '" ORDER BY id ASC';
        
        //echo $query; exit;
        
        //
        
        if ($this->db->query($query))
        {
            $patient_results = $this->db->get_results(null, ARRAY_A);
            
            if ($patient_results)
            {
                return $patient_results['0'];
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    function patient_surveys_completed ($patient, $project = 0, $include_practice = false, $date_filter = null, $dir = 'ASC') {
        if(!empty($date_filter)) {
            $sql_date = ' AND (ps.end BETWEEN "'.strtotime($date_filter['start']).'" AND "'.strtotime($date_filter['end']).'" )';
        } else {
            $sql_date = '';
        }
        $interviews = $this->db->get_results ('SELECT ps.id, IF( `set_id` IS NOT NULL AND `set_id` > 0 , CONCAT( pc.interview, "/", pc.set_step ) ,
                                            pc.interview ) AS `interview`,
                                            pc.interview as `real_interview`,
                                            sc.survey, ps.end, ps.patientchain,
                                            pc.project, pc.chain, ss.master_chain, ss.name as survey_name,
                                            pc.set_id, pc.set_step, st.name as step_name,
                                            pse.dob_check, pse.accepted, pse.id AS pse_id, 
                                            pse.service AS pse_service, survey_result_scores.misc_details,
                                            pc.dob_required
                                            FROM
                                            ' . SURVEY_TABLE_PREFIX . 'survey2chain sc,
                                            ' . SURVEY_TABLE_PREFIX . 'master_chains ss,
                                            `' . TABLE_PREFIX . '_patient2chain` pc
                                            LEFT JOIN ' . SURVEY_TABLE_PREFIX . 'sets st ON (st.id = pc.set_id AND pc.set_id > 0)
                                            INNER JOIN ' . TABLE_PREFIX . '_patient_surveys ps ON (pc.id = ps.patientchain)
                                            LEFT JOIN ' . TABLE_PREFIX . '_patient_email_surveys pse ON ps.id = pse.patientsurvey
                                            LEFT JOIN survey_result_scores ON (ps.id = survey_result_scores.survey_took)
                                            WHERE
                                            sc.chain = pc.chain
                                            AND sc.survey = ss.id
                                            AND ss.type = "patient"
                                            AND ps.end > 0
                                            '.$sql_date.'
                                            '.($project === 'all' ? '' : ' AND pc.project = "'.$project.'"').
            ' AND pc.patient = "' . $patient . '" ORDER BY `real_interview` '.$dir, ARRAY_A);
        //$this->db->debug();exit;

        if(!empty($interviews)) {
            $surveytake = new painPoolSurveyTake();
            $pat = new Patient();
            $patient_details = $pat->get_patient($patient);

            foreach($interviews as $pointz){

                $pointz['is_email'] = false;

                //check if e-mail survey and if answer is correct
                if(!empty($pointz['pse_id'])) {
                    if(date('Y-m-d', strtotime($patient_details['dob'])) != $pointz['dob_check'] && $pointz['dob_required'] == '1' && $pointz['accepted'] != '1') {
                        $pointz['wrong_answer'] = true;
                    } else {
                        $pointz['wrong_answer'] = false;
                    }

                    $pointz['is_email'] = true;
                    $pointz['email_service'] = $pointz['pse_service'];
                }
                //PAINPOOL-271 - importing surveys device information
                if ($pointz['misc_details'] == 'DUMMY'){
                    $pointz['is_import'] = true;
                }
                //get master chain links
                if($pointz['master_chain'] > 0) {
                    $chain_links = $surveytake->master_chain_surveys($pointz['survey']);
                    if(is_array($chain_links)) {
                        foreach($chain_links as $link){
                            $newpoint = $pointz;
                            $newpoint['master_chain'] = 0;
                            $newpoint['survey'] = $link['survey_id'];

                            if($pointz['set_id'] > 0) {
                                $newpoint['interview'] = $newpoint['real_interview'];
                                $newpoint['survey_name'] = $newpoint['step_name'];
                            }

                            $points[] = $newpoint;
                        }
                    }
                } else {
                    $points[] = $pointz;
                }
            }

            //var_dump_pre($points,1);

            return $points;
        } else {
            return false;
        }
    }
}

?>
