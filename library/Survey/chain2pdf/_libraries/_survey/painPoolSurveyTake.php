<?php
//extend survey to integrate with painPool
class painPoolSurveyTake extends SurveyTake {

	public $db;

	function __construct() {
		$this->db = $GLOBALS ['db'];
	}


	function chain_get_details($chain_id, $field = 'pc.id', $simple = false) { //$field defaults to patient2chain table id

		$chain_query = 'SELECT sc.survey as survey_id, c.id as chain_id, c.*, pc.*,sc.* FROM
						' . SURVEY_TABLE_PREFIX . 'chains c,
						' . TABLE_PREFIX . '_patient2chain pc,
						' . SURVEY_TABLE_PREFIX . 'survey2chain sc
						 WHERE pc.chain = c.id AND sc.chain = pc.chain AND '.$field.' = "'.$chain_id.'"';
		//echo $chain_query; exit;
		$surveys = $this->db->get_results ( $chain_query, ARRAY_A );
		//$this->db->debug();

		if(!empty($surveys)) {
			foreach ( $surveys as $survey ) {
				$chain ['details']['chain_id'] = $survey['chain_id'];
				$chain ['details']['patient'] = $survey['patient'];
				$chain ['details']['interview'] = $survey['interview'];
				$chain ['details']['project'] = $survey['project'];
				$chain ['details']['set_id'] = $survey['set_id'];
				$chain ['details']['set_step'] = $survey['set_step'];
				$chain ['details']['survey_master_id'] = $survey['survey_id'];
				$chain ['details']['dummy'] = $survey['dummy'];
				$chain ['details']['dob_required'] = $survey['dob_required'];
				$chain ['details']['abort_by'] = $survey['abort_by'];
				$chain ['details']['abort_date'] = $survey['abort_date'];
				$chain ['details']['abortable'] = $survey['abortable'];
				$chain ['details']['abort_methods'] = $survey['abort_methods'];


				if($simple === false) { //don't get all surveys if not needed
					$chain ['surveys'] [$survey ['survey_id']] = $survey;
					$chain ['surveys'] [$survey ['survey_id']] ['details'] = $this->mchain_get_details( $survey ['survey_id'] );
					$chain ['surveys'] [$survey ['survey_id']] ['last_page'] = $this->survey_last_page_get ( $survey ['survey_id'] );
				}
			}
		} else {
			$chain = false;
		}

		return $chain;
	}

	function master_chain_surveys($chain_id) {
		$surveys = $this->db->get_results('SELECT sc.id as scid, ss.id as survey_id, sc.*, ss.* FROM
											`'.SURVEY_TABLE_PREFIX.'master_chains` ssc,
											`'.SURVEY_TABLE_PREFIX.'survey2masterchain` sc,
											`'.SURVEY_TABLE_PREFIX.'surveys` ss
											WHERE sc.chain = ssc.master_chain
											AND sc.survey = ss.id
											AND ssc.id = "'.$chain_id.'"
											ORDER BY `sc`.`order` ASC', ARRAY_A);
		return $surveys;
	}

	function survey_patient_get($took_id) {
		$sql = 'SELECT * FROM '.TABLE_PREFIX.'_patient_surveys ps, '.TABLE_PREFIX.'_patient2chain pc WHERE ps.patientchain = pc.id AND ps.id = "'.$took_id.'"';
		$patient = $this->db->get_row($sql, ARRAY_A, 0);
		return $patient;
	}

	function survey_scores_get($took_id, $survey_id) {

		if(is_array($survey_id) && sizeof($survey_id) > 0) {
			$sql_survey = ' AND `survey` IN ('.implode(',',$survey_id).')';
		} else {
			$sql_survey = ' AND `survey` = "'.$survey_id.'"';
		}

		$result_scores = $this->db->get_results('SELECT * FROM '.SURVEY_TABLE_PREFIX.'result_scores WHERE survey_took = "'.$took_id.'" '.$sql_survey, ARRAY_A, 'score');
		if($survey_id == 126) {
			//$this->db->debug(); exit;
		}
		$survey_scores = $this->survey_get_scores ( $survey_id, false, true );

		if(is_array($survey_scores)){
			foreach($survey_scores as $survey_score){
				$scorez[$survey_score['id']]['score'] = (float) $result_scores[$survey_score['id']]['value'];
				if($survey_id == 150 || $survey_id == 154){
				    $scorez[$survey_score['id']]['value_extra_text'] = $result_scores[$survey_score['id']]['value_extra_text'];
				    $scorez[$survey_score['id']]['value_extra'] = (float) $result_scores[$survey_score['id']]['value_extra'];
				}
				$scorez[$survey_score['id']]['eq'] = $result_scores[$survey_score['id']]['eq'];
				$scorez[$survey_score['id']]['extra'] = $result_scores[$survey_score['id']]['chart_text'];
				$scorez[$survey_score['id']]['details'] = $survey_score;
			}
			return $scorez;
		} else {
			return false;
		}
	}

	function survey_get_scores($survey_id, $noquestions = false, $no_hidden = false, $tie_them_all = false){

		if($tie_them_all === true) {
			foreach ( $GLOBALS ['all_scores_map'] as $score_name => $score_ids ) {
				$the_sql .= 'WHEN `ss`.`id` IN (' . implode ( ',', $score_ids ) . ') THEN "' . $score_name . '"' . "\n";
			}

			$the_select = '
						CASE
							' . $the_sql . '
						ELSE ss.id
						END
						AS `score_id`, ss.id AS `score_db_id`,ss.* ';
		} else {
			$the_select = 'ss.*';
		}

		if(is_array($survey_id) && sizeof($survey_id) > 0) {
			$sql_survey = ' AND `ss`.`survey` IN ('.implode(',',$survey_id).')';
		} else {
			$sql_survey = ' AND `ss`.`survey` = "'.$survey_id.'"';
		}

		if($no_hidden === true) {
			$sql_h = ' AND `ss`.`hidden` != "1"';
		} else {
			$sql_h = '';
		}
		$scores = $this->db->get_results('SELECT '.$the_select.' FROM '.SURVEY_TABLE_PREFIX.'survey_scores ss WHERE 1 '.$sql_survey.' '.$sql_h.' order by id', ARRAY_A);
		if(!empty($scores) && is_array($scores)) {
			foreach($scores as $score){
				$scorez[$score['id']] = $score;
			}
		}

		return $scorez;
	}

	function mchain_get_details($id, $field = 's.id') {
		$survey = $this->db->get_row ( 'SELECT s.* FROM ' . SURVEY_TABLE_PREFIX . 'master_chains s  WHERE ' . $field . '="' . intval ( $id ) . '"', ARRAY_A, 0 );
		//		$this->db->debug();
		if (! empty ( $survey )) {
			$survey_details ['survey_details'] = $survey;
			//$survey_details ['pages'] = $this->survey_get_pages ( $survey ['id'] );
		} else {
			$survey_details = false;
		}
		return $survey_details;
	}

	function patient_scores_get_all($patient, $project = 0, $include_practice = false, $date_filter = null) {
		$pat = new Patient ();
		$completed_surveys = $pat->patient_surveys_completed ( $patient, $project, $include_practice, $date_filter);
		//var_dump_pre($completed_surveys,1);
		if ($completed_surveys !== false) {
			foreach ( $completed_surveys as $survey ) {
				//$scorez['scores'] ['T'.$survey ['interview']][$survey ['survey']] = $this->survey_calculate_scores ( $survey ['id'], $survey ['survey'] );
				$scorez['scores'] ['T'.$survey ['interview']][$survey ['survey']] = $this->survey_scores_get ( $survey ['id'], $survey ['survey'] );
				$scorez['dates'] ['T'.$survey ['interview']] = $survey['end'];
				// get scores for chain -> added by Ancuta
				$scorez['chain_score'] [$survey ['patientchain']][$survey ['survey']] = $scorez['scores'] ['T'.$survey ['interview']][$survey ['survey']];
				//$scorez['chain_score'] [$survey ['patientchain']][$survey ['survey']] = $this->survey_scores_get ( $survey ['id'], $survey ['survey'] );


				$scorez['all_info'] [$survey ['patientchain']]['surveys'][$survey ['survey']] = $scorez['chain_score'] [$survey ['patientchain']][$survey ['survey']];
				$scorez['all_info'] [$survey ['patientchain']]['details'] = $survey;

			}
			//var_dump_pre($scorez, true);
			return $scorez;

		} else {
			return false;
		}
	}

	function get_score_text($score_id, $score_value, $score_extra)
	{
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

		if (in_array ( $score_id, $korff_score_id )) {
			switch ($score_value) {
				case '1' :
					$korf_score_text = 'geringe Schmerzintensität und geringe Beeinträchtigung (< 3 Disability-Punkte)';
					break;

				case '2' :
					$korf_score_text = 'hohe Schmerzintensität und geringe Beeinträchtigung (< 3 Disability-Punkte)';
					break;

				case '3' :
					$korf_score_text = 'hohe schmerzbedingte Beeinträchtigung, mäßig limitierend (3-4 Disability-Punkte)';
					break;

				default :
					$korf_score_text = 'hohe schmerzbedingte Beeinträchtigung, stark limitierend (5-6 Disability-Punkte)';
					break;
			}
			$score_text = $korf_score_text;
		} else if (in_array ( $score_id, $sbla_score_id )) {
			$score_text = 'Als Grenzwert für ein erhöhtes affektives Schmerzerleben kann ein Summenwert von 8 angesetzt werden. (größer = schlechter)';
		} else if (in_array ( $score_id, $sbls_score_id )) {
			$score_text = 'Die "Sensorischen Items" werden einzeln bewertet, da keine klinisch relevanten Werte kalkuliert werden können.';
		} else if (in_array ( $score_id, $depression_id )) {
			$score_text = 'Werte größer/gleich 10 deuten auf eine Depression hin.';
		} else if (in_array ( $score_id, $angst_id )) {
			$score_text = 'Werte größer/gleich 6 deuten auf eine Angst-Störung hin.';
		} else if (in_array ( $score_id, $stress_id )) {
			$score_text = 'Werte größer/gleich 10 deuten auf Stress hin.';
		} else if (in_array ( $score_id, $pp42fw7_score_id )) {
			$score_text = 'Ein Wert von 10 Punkten und darunter ist für Schmerzpatienten ein niedriger und daher auffälliger Wert des allgemeinen Wohlbefindens.';
		} else if (in_array ( $score_id, $mpss_score_id )) {
			$score_text = 'Dieser Patient befindet sich nach dem Mainzer Stadienmodell der Schmerzchronifizierung (MPSS) nach Gerbershagen im Stadium '.$score_value.'.';
		} else if (in_array ( $score_id, $nrs_score_id ) || in_array ( $score_id, $nrsm_score_id )) {
			$score_text = 'Die Numerische Rating Skala (NRS) gibt den Schmerz von 0 - 10 an, wobei 0 nicht vorhanden und 10 der stärkste vorstellbare Schmerz ist.';
		} else if (in_array ( $score_id, $qlip_score_id )) {

			if ($score_value >= 0 && $score_value <= 10) {
				$score_text = 'maximale Beeinträchtigung der Lebensqualität';
			} elseif ($score_value >= 11 && $score_value <= 20) {
				$score_text = 'Beeinträchtigung der Lebensqualität';
			} elseif ($score_value >= 21 && $score_value <= 29) {
				$score_text = 'geringe Beeinträchtigung der Lebensqualität';
			} elseif ($score_value >= 30 && $score_value <= 43) {
				$score_text = 'keine Beeinträchtigung der Lebensqualität';
			}

			$score_text .= '<br />Der erreichbare Summenscore variiert <br /> von „0“ (= maximale Beeinträchtigung der Lebensqualität) <br /> bis „43“ (= keine Beeinträchtigung der Lebensqualität).';

		} else if(!empty($score_extra)) {
			$score_text = $score_extra;
		} else {
			$score_text = '--';
		}
		return $score_text;
	}
}
?>
