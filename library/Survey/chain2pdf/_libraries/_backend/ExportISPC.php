<?php

class ExportISPC extends Export
{

    function chain_generate_mpdf($patientchain, $patient_details)
    {
        $data = $this->chain_surveys_questions_get($patientchain);
        if ($remove_skipped) {
            $this->chain_surveys_questions_remove_skipped($data);
        }
        $surveys = $data['surveys'];
        $answers = $data['answers'];

        $pat_sur_query = $this->db->query('
				SELECT * FROM ' . TABLE_PREFIX . '_patient2chain WHERE  id = ' . $patientchain . '');
        $patient_chain_details = $this->db->get_results(null, ARRAY_A);
        
        

        if ($patient_chain_details[0]['end'] != 0) {
            $survey_complete_date = date('d.m.Y', strtotime($patient_chain_details[0]['end']));
        } else {
            $survey_complete_date = '';
        }

        $patientname = '<div class="export_pat_details"><div class="pat_det"><b>Name:</b> '.$patient_details['last_name'].' '.$patient_details['first_name'].'  </div><div class="date">' . $survey_complete_date . '</div></div>';

        $survey_take = new painPoolSurveyTake();
        
        /*
         * No scores for now,  mepatient ISPC-2432
         */
        /*$the_scores = $survey_take->patient_scores_get_all($patientdetails[0]['patient_id'], $project_id);
        $chain_scores_array = $the_scores['chain_score'][$patientchain];*/

        if ($_REQUEST['dbg']) {
            var_dump_pre($patientchain);
            var_dump_pre($data);
        }

        if ($fb_korff_score) {
            switch ($fb_korff_score) {

                case '1':
                    $fb_korff_text = 'geringe Schmerzintensität und geringe Beeinträchtigung (< 3 Disability-Punkte)';
                    break;

                case '2':
                    $fb_korff_text = 'hohe Schmerzintensität und geringe Beeinträchtigung (< 3 Disability-Punkte)';
                    break;

                case '3':
                    $fb_korff_text = 'hohe schmerzbedingte Beeinträchtigung, mäßig limitierend (3-4 Disability-Punkte)';
                    break;

                default:
                    $fb_korff_text = 'hohe schmerzbedingte Beeinträchtigung, stark limitierend (5-6 Disability-Punkte)';
                    break;
            }
        } else {
            $fb_korff_text = '';
        }

        $i = 1;
        if ($chain_scores_array) {

            $korff_score_id = $GLOBALS['all_scores_map']['korff'];

            $sbls_score_id = $GLOBALS['all_scores_map']['slbs'];

            $sbla_score_id = $GLOBALS['all_scores_map']['slba'];

            $depression_id = $GLOBALS['all_scores_map']['depression'];

            $angst_id = $GLOBALS['all_scores_map']['angst'];

            $stress_id = $GLOBALS['all_scores_map']['stress'];

            $qlip_score_id = $GLOBALS['all_scores_map']['qlip'];

            $pp42fw7_score_id = $GLOBALS['all_scores_map']['fw7'];

            $nrs_score_id = $GLOBALS['all_scores_map']['nrs'];

            $nrsm_score_id = $GLOBALS['all_scores_map']['nrsm'];

            $mpss_score_id = $GLOBALS['all_scores_map']['mpss'];

            $korff_nachfrage_id = 96;
            $verlaufsbogen_id = 80;
            $schmerzfragen_id = 93;
            $sbls_id = 77;

            $dass_id = 55; // (DASS-copied)

            foreach ($chain_scores_array as $key => $value) {
                if ($value !== false) {
                    foreach ($value as $ky => $survey_details) {

                        if (is_int($i / 2)) {
                            $alt_bg = ' ';
                        } else {
                            $alt_bg = 'class="alt_backg"';
                        }

                        $chain_survey_score[$ky]['the_score'] = $survey_details['score'];
                        $chain_survey_score[$ky]['the_details'] = $survey_details['details']['score'];

                        if ($survey_details['details']['range_end'] != '0') {
                            $survey_details_range = $survey_details['details']['range_start'] . ' - ' . $survey_details['details']['range_end'];
                        } else {
                            $survey_details_range = '';
                        }

                        

                        $score_text = $survey_take->get_score_text($survey_details['details']['id'], $survey_details['score'], $survey_details['extra']);


                        // PAINPOOL-444 display line instead of value for category score
                        if ($chain_scores_array[510]) {
                            if ($survey_details['details']['survey'] == 510) {
                                $survey_details['details']['score'] = '----';
                                $survey_details['score'] = '----';
                                $survey_details_range = '----';
                            }
                        }

                        $chain_scores_str .= '<tr class="score_row">
												<td class = "row" style="border-left:0;border-top: 1px solid  #e1e1e1" ' . $alt_bg . '>' . $survey_details['details']['score'] . '</td>
												<td ' . $alt_bg . '>' . $survey_details['score'] . '</td>';
                        if ($chain_scores_array[150]) {
                            if ($survey_details['details']['survey'] == 150) {
                                $chain_scores_str .= '<td ' . $alt_bg . '>';
                                if ($survey_details['value_extra']) {
                                    $chain_scores_str .= $survey_details['value_extra'];
                                } else {
                                    $chain_scores_str .= '--';
                                }
                                $chain_scores_str .= '</td>';
                            } else {
                                $chain_scores_str .= '<td ' . $alt_bg . '> -- </td>';
                            }
                        }
                        if ($chain_scores_array[154]) {
                            if ($survey_details['details']['survey'] == 154) {
                                $chain_scores_str .= '<td ' . $alt_bg . '>';
                                if ($survey_details['value_extra']) {
                                    $chain_scores_str .= $survey_details['value_extra'];
                                } else {
                                    $chain_scores_str .= '--';
                                }
                                $chain_scores_str .= '</td>';
                            } else {
                                $chain_scores_str .= '<td ' . $alt_bg . '> -- </td>';
                            }
                        }
                        $chain_scores_str .= '  <td ' . $alt_bg . '>' . $score_text . '</td>
												<td ' . $alt_bg . '>' . $survey_details_range . '</td>
											</tr>';

                        $i ++;
                    }
                }
            }
        }

        $surveytake = new painPoolSurveyTake();

        if ($surveys) {
            $html .= $patientname; // show patient name
            if (! $dummy) {
                foreach ($surveys as $chain_key => $chain_arr) {

                    foreach ($chain_arr as $survey => $survey_arrs) {
                        foreach ($survey_arrs as $q_key => $questions) {
                            $html .= $surveytake->question_generate_html_dompdf($questions, $answers[$patientchain][$survey], $data['took_id']);
                            $html .= '<br />';
                        }
                    }
                }
            }
            if ($chain_scores_str) { // show scores
                $html .= '<br /><br /><a name="scores"></a><table width="100%" class="export_chain_scores" style="page-break-inside: avoid;" >
							<tr>
								<th width="20%" style="border-left:0;"><b>Score</b></th>
								<th><b>Wert</b></th>';
                if ($chain_scores_array[150]) {
                    $html .= '<th><b>' . $chain_scores_array[150][151]['value_extra_text'] . '</b></th>';
                }
                if ($chain_scores_array[154]) {
                    $html .= '<th><b>' . $chain_scores_array[154][150]['value_extra_text'] . '</b></th>';
                }
                $html .= '
								<th><b>Beurteilung</b></th>
								<th><b>Range</b></th>
							</tr>';
                $html .= $chain_scores_str;
                $html .= '</table>';
            }

            return $html;
        } else {
            return false;
        }
    }

    function chain_surveys_questions_get($patientchain = false)
    {
        if ($patientchain) {
            // $query = $this->db->query('SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'survey2chain sc
            // LEFT JOIN ' . SURVEY_TABLE_PREFIX . 'surveys s ON sc.survey = s.id
            // WHERE sc.chain ="' . $chain . '"
            // ORDER BY `order` ASC');
            
            /*
             * get all the surveys
             */
           /* $query = $this->db->query('SELECT pc.id as took_id, s.id as survey_id, pc.*, sc.*, s.* FROM ' . TABLE_PREFIX . '_patient2chain pc, 
                    ' . SURVEY_TABLE_PREFIX . 'survey2masterchain sc, 
                    ' . SURVEY_TABLE_PREFIX . 'master_chains s 
					WHERE sc.chain = s.master_chain AND pc.id = ' . $patientchain . ' AND s.id = pc.survey_id 
					ORDER BY `order` ASC');
					*/
            /*
             * get only the master chain
             */
            $query = $this->db->query('SELECT pc.id as took_id, s.id as survey_id, pc.*, s.* FROM ' . TABLE_PREFIX . '_patient2chain pc,
                    ' . SURVEY_TABLE_PREFIX . 'master_chains s
					WHERE  pc.id = ' . $patientchain . ' AND s.id = pc.survey_id
					');
            
            if ($_REQUEST['dbg']) {
                $this->db->debug();
            }

            if ($query) {
                $surveys = $this->db->get_results(null, ARRAY_A);
                if ($surveys) {
                    $survey_take = new painPoolSurveyTake();

                    foreach ($surveys as $i_survey) {
                        if ($i_survey['master_chain'] > 0) {
                            $chain_links = $survey_take->master_chain_surveys($i_survey['survey_id']);
                            if (is_array($chain_links)) {
                                foreach ($chain_links as $chain_link) {
                                    $new_survey = $i_survey;

                                    $new_survey['survey_id'] = $chain_link['survey_id'];

                                    $surveyz[] = $new_survey;
                                }
                            }
                        } else {
                            $surveyz[] = $i_survey;
                        }
                    }

                    foreach ($surveyz as $k_survey => $v_survey) {

                        $questions[$v_survey['survey_id']] = $survey_take->survey_get_questions($v_survey['survey_id'], false);

                        $result['answers'][$patientchain][$v_survey['survey_id']] = $survey_take->survey_results_get($v_survey['took_id']);
                        $result['took_id'] = $v_survey['took_id'];

                        if ($questions[$v_survey['survey_id']]) {
                            foreach ($questions[$v_survey['survey_id']] as $question) {
                                $q_data[$question['qid']] = $survey_take->question_get_details($question['qid']);

                                if ($q_data[$question['qid']]['question_details']) {
                                    $result['surveys'][$patientchain][$v_survey['survey_id']][$question['qid']] = $q_data[$question['qid']];
                                }
                            }
                        }
                    }
                    if ($_REQUEST['dbg']) {
                        // var_dump_pre ( $result ['answers'] );
                    }
                    return $result;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    
    function generate_mpdfs_chain($chain_id, $patient_details, $outputfolder = null) {
        
        
        $filename = 'results_'.$chain_id.'.pdf';
        
        $html_survey_footer = $this->chain_generate_mpdf_footer($chain_id, $patient_details);
        
        $htmlpdf = $this->chain_generate_mpdf($chain_id, $patient_details);
        
        $htmlpdf = $this->html_prepare_for_pdf($htmlpdf);
        
        $htmlpdfall ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            
            <html xmlns="http://www.w3.org/1999/xhtml"><head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <link rel="stylesheet" href="'.TEMPLATE_ABSPATH.'/styles/pdf/survey-print-mpdf.css" /><title></title></head><body>
                '.$htmlpdf.'
			</body></html>';
        
        $mpdf = new Mpdf\Mpdf([
            'tempDir' => TMP_ABSPATH . '/mpdf',
            //'defaultPagebreakType' => 'slice',
            //'defaultCssFile' => TEMPLATE_ABSPATH.'/styles/pdf/print.css'
        ]);
       
        
        
        
        $mpdf->DefHTMLFooterByName('survey_custom_footer',$html_survey_footer);
        $mpdf->WriteHTML($htmlpdfall, \Mpdf\HTMLParserMode::DEFAULT_MODE);
        if(!empty($outputfolder)) {
            $diskpath = $outputfolder.DIRECTORY_SEPARATOR.$filename;
            $mpdf->Output($diskpath, 'F');
        } else {
            $mpdf->Output($filename, 'D');
            $diskpath = null;
        }
        
        unset($dompdf);
        unset($mpdf);
        unset($htmlpdf);
        unset($htmlpdfall);
        
        
        return ($diskpath ? $diskpath : $filename);
        
    }
    
    function chain_generate_mpdf_footer($chain_took_id, $patient_details) {
        
        
        
        $htmlfooter = '
        <table width="100%">
            <tr>
                <td width="50%">
                    <span>'
            .$patient_details['first_name'].' '.$patient_details['last_name'].(!empty($patient_details['dob'])? ', '.date('d.m.Y', strtotime($patient_details['dob'])) : '').'
                    </span>
                </td>
                <td width="50%" style="text-align: right;">
                    '.$patient_details['survey_name'].'
                </td>
            </tr>
        </table>';

            return $htmlfooter;
    }
    
}


?>