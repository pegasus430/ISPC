<?php
class SurveyTake {
	var $db;
	//PAINPOOL-383 New question body-m, body-f answers
	var $new_body_areas_list = arraY(
	    
	    1 => array('item' => 'Kopf, rechte Seite', 'description' => '', 'ids' => array('fhl')),
	    
	    2 => array('item' => 'Kopf, linke Seite', 'description' => '', 'ids' => array('fhr')),
	    
	    3 => array('item' => 'Kopf, mittig (Stirn)', 'description' => '', 'ids' => array('fhm')),
	    
	    4 => array('item' => 'Hinterkopf, linke Seite', 'description' => '', 'ids' => array('bhl')),
	    
	    5 => array('item' => 'Hinterkopf, rechte Seite', 'description' => '', 'ids' => array('bhr')),
	    
	    6 => array('item' => 'Hinterkopf, mittig', 'description' => '', 'ids' => array('bhm')),
	    
	    7 => array('item' => 'Brust', 'description' => '', 'ids' => array('fc')),
	    
	    8 => array('item' => 'Bauch', 'description-male' => 'Dazu zählen auch Magen und unterer Bauch',
	        'description-female' =>'Dazu zählen auch Magen und Unterleib', 'ids' => array('fa')),
	    
	    9 => array('item' => 'Rücken', 'description' => 'Dazu zählt auch der Nacken', 'ids' => array('bb')),
	    
	    10 => array('item' => 'Arme, vorne', 'description' => 'Dazu zählen auch Hand und Schulter', 'ids' => array('fra','fla')),
	    
	    11 => array('item' => 'Arme, hinten', 'description' => 'Dazu zählen auch Hand und Schulter', 'ids' => array('bra','bla')),
	    
	    12 => array('item' => 'Beine, vorne', 'description' => 'Dazu zählen auch Hüfte und Fuß', 'ids' => array('frl','fll')),
	    
	    13 => array('item' => 'Beine, hinten', 'description' => 'Dazu zählen auch Hüfte und Fuß', 'ids' => array('bll','brl'))
	    
	    );
	
	//PAINPOOL-464 new new body question items
	
	var $new_2019_body_areas_list = arraY(
	    
	    1 => array('item' => 'Kopf vorne, rechts', 'description' => '', 'ids' => array('fhr')),
	    
	    2 => array('item' => 'Kopf vorne, links', 'description' => '', 'ids' => array('fhl')),
	    
	    3 => array('item' => 'Kopf vorne, Stirn', 'description' => '', 'ids' => array('fhm')),
	    
	    4 => array('item' => 'Gesicht', 'description' => '', 'ids' => array('fhf')),
	    
	    5 => array('item' => 'Hinterkopf', 'description' => '', 'ids' => array('bh')),
	    
	    6 => array('item' => 'Brust', 'description' => '', 'ids' => array('fc')),
	    
	    7 => array('item' => 'Bauch', 'description-' => '', 'ids' => array('fa')),
	    
	    8 => array('item' => 'Rücken', 'description' => '', 'ids' => array('bb')),
	    
	    9 => array('item' => 'Arm, rechts', 'description' => '', 'ids' => array('fra', 'bra')),
	    
	    10 => array('item' => 'Arm, links', 'description' => '', 'ids' => array('fla', 'bla')),
	    
	    11 => array('item' => 'Bein, rechts', 'description' => '', 'ids' => array('frl','brl')),
	    
	    12 => array('item' => 'Bein, links', 'description' => '', 'ids' => array('fll','bll')),
	    
	    );
	
	
	
	
	
	function __construct() {
		$this->db = $GLOBALS ['db'];
	}

	function survey_last_page_get($survey_id) {
		return $this->db->get_row ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'pages WHERE survey = "' . $survey_id . '" ORDER BY page DESC LIMIT 1', ARRAY_A, 0 );
	}

	function survey_get_questions($survey_id, $conditions = '') {
		$questions = $this->db->get_results ( '
			SELECT *, q.question as qtext, qt.type as qtype, q.id as qid, q.order as qorder,p.page as thepage, p.id as thepageid, p.page as page_no FROM
			' . SURVEY_TABLE_PREFIX . 'question_types qt,
			' . SURVEY_TABLE_PREFIX . 'surveys s,
			' . SURVEY_TABLE_PREFIX . 'questions q
			LEFT JOIN ' . SURVEY_TABLE_PREFIX . 'questions2pages q2p on q2p.question=q.id
			LEFT JOIN ' . SURVEY_TABLE_PREFIX . 'pages p on p.id=q2p.page
			WHERE
			s.id = "' . $survey_id . '" AND
			s.id = q.survey AND
			qt.type = q.type ' . (! empty ( $conditions ) ? $conditions : '') . '
			ORDER BY p.page, q.order asc', ARRAY_A );
		return $questions;
	}

	function survey_results_get($survey_took) {
		$sql = 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'results t WHERE t.survey_took ="' . $survey_took . '" AND answered = "1"';
		$results = $this->db->get_results ( $sql, ARRAY_A );
		if ($_REQUEST ['dbg']) {
			$this->db->debug ();
		}
		if (is_array ( $results )) {
			foreach ( $results as $result ) {
				// [$result['survey']]
				if ($result ['freetext'] == '1') {
					$resultz [$result ['question']] [$result ['row'] . '_' . $result ['column']] ['free_answer'] = $result ['answer'];
				}
				$resultz [$result ['question']] [$result ['row'] . '_' . $result ['column']] ['answer'] = $result ['answer'];
				$resultz [$result ['question']] [$result ['row'] . '_' . $result ['column']] ['row'] = $result ['row'];
				$resultz [$result ['question']] [$result ['row'] . '_' . $result ['column']] ['column'] = $result ['column'];
				$resultz [$result ['question']] [$result ['row'] . '_' . $result ['column']] ['freetext'] = $result ['freetext'];
				$resultz [$result ['question']] [$result ['row'] . '_' . $result ['column']] ['cell_key'] = $result ['row'] . '_' . $result ['column'];
				$resultz [$result ['question']] [$result ['row'] . '_' . $result ['column']] ['answer_id'] = $result ['id'];
			}
		}

		return $resultz;
	}

	function question_get_details($id) {
		$question = $this->db->get_row ( 'SELECT *,q.type as qtype,q.question as qtext, q.id as qid
										  FROM ' . SURVEY_TABLE_PREFIX . 'questions q,
										  ' . SURVEY_TABLE_PREFIX . 'question_types qt
										  WHERE qt.type = q.type AND q.id="' . intval ( $id ) . '"', ARRAY_A, 0 );
		// $this->db->debug();
		$question_details ['question_details'] = $question;

		$question_details ['cf'] = $this->question_cf_get ( $id );

		switch ($question ['qtype']) {

			case 'html' :
			case 'html-ne' :
				$question_details ['answer'] ['html'] = $this->db->get_row ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'answers_html WHERE question="' . $question ['qid'] . '"', ARRAY_A, 0 );
				break;

			case 'matrix_mradio' :
			case 'matrix_mtext' :
			case 'matrix_scheck' :
			case 'matrix_mcheck' :
			case 'matrix_sradio' :

				$rows = $this->db->get_results ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'answers_matrix_rows WHERE question="' . $question ['qid'] . '" order by `order`', ARRAY_A );
				foreach ( $rows as $row ) {
					$question_details ['answer'] ['rh'] ['rh_' . $row ['order']] = $row;
				}

				$question_details ['answer'] ['rows_no'] = sizeof ( $rows );


				//done TODO-377
                switch ($question['custom']) {
                    case 'order_text_signed':
                        $orderby = 'cast(`text` as signed)';
                        break;
                    default:
                        $orderby = '`order`';
                        break;
                }

				/*if($question['qid'] == '2321' || $question['qid'] == '6235') {
					$orderby = 'cast(`text` as signed)';
				} else {
					$orderby = '`order`';
				}*/

				$cols = $this->db->get_results ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'answers_matrix_columns WHERE question="' . $question ['qid'] . '" order by '.$orderby.'', ARRAY_A );
				foreach ( $cols as $col ) {
					$question_details ['answer'] ['ch'] ['ch_' . $col ['order']] = $col;
				}

				$question_details ['answer'] ['cols_no'] = sizeof ( $cols );

				$cells = $this->db->get_results ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'answers_matrix_values WHERE question="' . $question ['qid'] . '" order by `row`,`column`', ARRAY_A );

				foreach ( $cells as $cell ) {
					$question_details ['answer'] ['cells'] [$cell ['row'] . '_' . $cell ['column']] = $cell;
				}

				break;

			case 'complex' :

				$rows = $this->db->get_results ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'answers_complex_rows WHERE question="' . $question ['qid'] . '" order by `order`', ARRAY_A );
				foreach ( $rows as $row ) {
					$question_details ['answer'] ['rh'] ['rh_' . $row ['order']] = $row;
				}

				$question_details ['answer'] ['rows_no'] = sizeof ( $rows );


				$cols = $this->db->get_results ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'answers_complex_columns WHERE question="' . $question ['qid'] . '" order by `order`', ARRAY_A );
				foreach ( $cols as $col ) {
					$question_details ['answer'] ['ch'] ['ch_' . $col ['order']] = $col;
				}

				$question_details ['answer'] ['cols_no'] = sizeof ( $cols );

				$cells = $this->db->get_results ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'answers_complex_values WHERE question="' . $question ['qid'] . '" order by `row`,`column`', ARRAY_A );

				foreach ( $cells as $cell ) {
					$question_details ['answer'] ['cells'] [$cell ['row'] . '_' . $cell ['column']] = $cell;
				}

				break;

			case 'comment' :
				$question_details ['answer'] ['comment'] = $this->db->get_row ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'answers_comment WHERE question="' . $question ['qid'] . '"', ARRAY_A, 0 );
				break;

			case 'slider' :
				$question_details ['answer'] ['slider'] = $this->db->get_row ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'answers_slider WHERE question="' . $question ['qid'] . '"', ARRAY_A, 0 );
				break;

			case 'free_text' :
				$question_details ['answer'] ['text'] = $this->db->get_row ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'answers_text WHERE question="' . $question ['qid'] . '"', ARRAY_A, 0 );
				break;
			
			case 'faces':
			    $question_details ['answer'] ['faces'] = $this->db->get_row ( 'SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'answers_faces WHERE question="' . $question ['qid'] . '"', ARRAY_A, 0 );
			    break;
				
			default :
				break;
		}

		return $question_details;
	}
	function question_cf_get($id) {
		$conditions = $this->db->get_results ( 'SELECT cf.id as cfid, q.question_id AS question_id, cf.operator, q.id as qid FROM ' . SURVEY_TABLE_PREFIX . 'question_filling cf,' . SURVEY_TABLE_PREFIX . 'questions q WHERE cf.depends = q.id AND cf.question = "' . $id . '"', ARRAY_A, 'cfid' );
		if ($conditions) {
			return $conditions;
		} else {
			return false;
		}
	}

	function question_generate_html_dompdf($data, $answers, $took_id = null) {

	    /*
	     * 
	     * no tokens for ISPC , mepatient ISPC-2432
	     */
	    /*$patient = $this->survey_patient_get ( $took_id );
	    $patient_id = $patient ['patient'];
	   
	    
	    $p = new Patient ();
	    $patient_details = $p->get_patient($patient_id);*/

	    $question_details = $data ['question_details'];

	    $question_details ['qtext'] = $this->patient_replace_tokens($question_details ['qtext'],$patient_details);


	    $cf = $data ['cf'];
	    $question_id = $question_details ['qid'];
	    $qans = $answers [$question_id];

	    if ($question_details ['required'] == 1) {
	        $req_html = '<span class="qreq" id="qreq_' . $question_details ['question_id'] . '">*</span>';
	    } else {
	        $req_html = '';
	    }

	    if ($question_details ['qtype'] == 'human-big') {
	        $html .= '<table class="qtable qtable_' . $question_details ['qtype'] . '" id="qc_' . $question_details ['question_id'] . '">';
	        $html .= '<tr><th class="qtitleth" ><h3 class="qtitle qtitle_' . $question_details ['qtype'] . '" id="qtitle_' . $question_details ['question_id'] . '">' . $question_details ['qtext'] . ' ' . $req_html . '</h3></th></tr>';
	    } elseif ($question_details ['qtype'] !== 'html' &&
					$question_details ['qtype'] !== 'html-ne' &&
					$question_details ['qtype'] !== 'free_textarea' &&
					$question_details ['qtype'] !== 'free_text') {
	        $html .= '<table class="qtable qtable_' . $question_details ['qtype'] . '" id="qc_' . $question_details ['question_id'] . '">';
	        $html .= '<tr><th class="qtitleth" ><h3 class="qtitle qtitle_' . $question_details ['qtype'] . '" id="qtitle_' . $question_details ['question_id'] . '">' . $question_details ['qtext'] . ' ' . $req_html . '</h3>
					 <br /><span class="qsubtext qsubtext_' . $question_details ['qtype'] . '" id="qsubtext_' . $question_details ['question_id'] . '">' . $question_details ['subtext'] . '</span></th></tr>';
	    } elseif ($question_details ['qtype'] == 'free_textarea' ||
				  $question_details ['qtype'] == 'free_text') {
			$html .= '<div class="qtable qtable_' . $question_details ['qtype'] . '" id="qc_' . $question_details ['question_id'] . '">';
			$html .= '<div><div class="qtitleth" ><h3 class="qtitle qtitle_' . $question_details ['qtype'] . '" id="qtitle_' . $question_details ['question_id'] . '">' . $question_details ['qtext'] . ' ' . $req_html . '</h3>
					 <br /><span class="qsubtext qsubtext_' . $question_details ['qtype'] . '" id="qsubtext_' . $question_details ['question_id'] . '">' . $question_details ['subtext'] . '</span></div></div>';
	    } else {
	        $html .= '<table class="qtable-nb" id="qc_' . $question_details ['question_id'] . '">';
	    }

	    switch ($question_details ['qtype']) {

	    	case 'complex' :


	    		if(sizeof($data ['answer'] ['rh']) == '1' && $data ['answer'] ['rh']['rh_1']['indefinite'] == 1) {
	    			$indefinite = true;
	    		} else {
	    			$indefinite = false;
	    		}

	    		$html .= '<tr><td>';


	    		$real_rows_no = 1;

	    		if($indefinite === true) {
	    			if(!empty($qans)) {
	    				foreach($qans as $qans_cell) {
	    					$real_rows[$qans_cell['row']] = $qans_cell['row'];
	    				}
	    				if(!empty($real_rows)) {
	    					$real_rows_no = sizeof($real_rows);
	    				}
	    			}
	    			$html .= '<script type="text/javascript">
								real_rows_no['.$question_details ['qid'].'] = "'.$real_rows_no.'";
							</script>';
	    		}

	    		$html .= '<table id="table_' . $question_details ['qid'] . '" class="matrix-answer complex_' . $question_details ['question_id'] . ' pp-hidden-but-measurable pp-full-table" cellspacing="0" autosize="1">';

	    		$html .= '<tr><th class="firstcolth"></th>';
	    		foreach ( $data ['answer'] ['ch'] as $col_head ) {
	    			$th_col_class = ' th_col_' . $question_details ['question_id'] . '_' . $col_head ['order'];
	    			$html .= '<th class="col ' . $th_col_class . '">' . $col_head ['text'] . '</th>';
	    		}
	    		$html .= '</tr>';


	    		$i = 1;

	    		foreach ( $data ['answer'] ['rh'] as $row_head ) {
	    			$row_no = $row_head['order'];
	    			$html .= $this->question_complex_generate_row_export($data, $row_head, $row_no, $answers, $indefinite, $took_id);

	    			$i ++;
	    		}

	    		if($indefinite == true) {

	    			for($j = $i; $j <= $real_rows_no; $j++) {
	    				$html .= $this->question_complex_generate_row_export($data, $row_head, $j, $answers, $indefinite, $took_id);
	    			}

	    			$html .= '<tr id="indefinite_tr_' . $question_details ['qid'] . '"><td colspan="'.(sizeof($data ['answer'] ['ch']) + 1).'" class="complex_indefinite_add_c">';
	    			$html .= '<a href="#" class="complex_indefinite_add" data-qid="' . $question_details ['qid'] . '">&nbsp;</a>';
	    			$html .= '</td></tr>';
	    		}
	    		$html .= '</table></td></tr>';

	    		break;


	        case 'matrix_mradio' :
	        case 'matrix_scheck' :
	        case 'matrix_mtext' :
	            $html .= '<tr><td><table style="width: 100%;" class="matrix-answer ma_' . $question_details ['question_id'] . '" cellspacing="0">';
	            if (is_array ( $cf ) && ($question_details ['qtype'] == 'matrix_mradio' || $question_details ['qtype'] == 'matrix_sradio')) {

	                foreach ( $cf as $cfs ) {
	                    $cf_str .= $cfs ['qid'] . '|' . $cfs ['operator'] . ';';
	                }
	            }

	            $html .= '<tr><th class="firstcolth"></th>';
	            foreach ( $data ['answer'] ['ch'] as $col_head ) {
	                $th_col_class = ' th_col_' . $question_details ['question_id'] . '_' . $col_head ['order'];
	                $html .= '<th class="col ' . $th_col_class . '">' . $col_head ['text'] . '</th>';
	            }
	            $html .= '</tr>';

	            $i = 1;

	            foreach ( $data ['answer'] ['rh'] as $row_head ) {

	                // if (is_int ( $i / 2 )) {
	                // $class .= ' alt';
	                // } else {
	                // $class .= '';
	                // }

	                $tr_class = ' tr_' . $question_details ['question_id'] . '_' . $row_head ['order'];
	                $th_row_class = ' th_row_' . $question_details ['question_id'] . '_' . $row_head ['order'];

	                $html .= '<tr class="' . $tr_class . '"><th class="row ' . $th_row_class . '">' . $row_head ['text'] . '</th>';
	                foreach ( $data ['answer'] ['ch'] as $col_head ) {
	                    $class = 'qt_' . $question_details ['qtype'] . ' qid_' . $question_details ['qid']; // reset class to question type and id
	                    $rh_key = $row_head ['order'] . '_' . $col_head ['order'];
	                    $cell_text = $data ['answer'] ['cells'] [$rh_key] ['text'];

	                    if (! empty ( $qans [$rh_key] ['answer'] )) {
	                        $cell_value = $qans [$rh_key] ['answer'];
	                    } else {
	                        $cell_value = $data ['answer'] ['cells'] [$rh_key] ['value'];
	                    }

	                    $class .= ' cell_' . $rh_key;
	                    $td_cell_class = 'td_' . $question_details ['question_id'] . '_' . $rh_key;

	                    if ($question_details ['qtype'] == 'matrix_mradio' && $question_details ['sudoku'] == '1') { // sudoku madness
	                        $class .= ' sudoku_' . $question_details ['qid'] . '_' . $col_head ['order'];
	                    }

	                    if ($question_details ['qtype'] == 'matrix_mradio') {
	                        $class .= ' rr_' . $question_details ['qid'] . '_' . $row_head ['order'] . ' rc_' . $question_details ['qid'] . '_' . $col_head ['order'];
	                    }

	                    if ($question_details ['required'] == '1') { // questions is required
	                        $class .= ' req_' . $question_id;
	                    }

	                    // if ($cell_value == $default || @in_array ( $cell_value, $default )) {
	                    if (! empty ( $qans ) && is_array ( $qans ) && @array_key_exists ( $rh_key, $qans )) {
	                        $checked = 'checked="checked"';
	                    } else {
	                        $checked = '';
	                    }

	                    switch ($question_details ['qtype']) {
	                        case 'matrix_mradio' :
	                            $html .= '<td class="' . $td_cell_class . '">
											<input class="styled ' . $class . '" ' . $checked . ' id="ans_' . $question_details ['qid'] . '_' . $rh_key . '" type="radio" name="answer[' . $question_details ['qid'] . '][' . $rh_key . ']" value="' . $cell_value . '" /></td>';
	                            break;
	                        case 'matrix_scheck' :
	                            $html .= '<td class="' . $td_cell_class . '"><input class="styled ' . $class . '" ' . $checked . ' id="ans_' . $question_details ['qid'] . '_' . $rh_key . '" type="checkbox" name="answer[' . $question_details ['qid'] . '][' . $rh_key . ']" value="' . $cell_value . '" /></td>';
	                            break;
	                        case 'matrix_mtext' :
	                            //$html .= '<td class="' . $td_cell_class . '"><input class="' . $class . '" type="text" id="ans_' . $question_details ['qid'] . '_' . $rh_key . '"  name="answer[' . $question_details ['qid'] . '][' . $row_head ['order'] . '_' . $col_head ['order'] . ']" size="3" value="' . $qans [$rh_key] ['answer'] . '" /></td>';
	                            $html .= '<td class="' . $td_cell_class . '">' . wordwrap($qans [$rh_key] ['answer'], 30, "\n",true) . '</td>';
	                            break;
	                    }
	                }
	                $html .= '</tr>';
	                $i ++;
	            }

	            $html .= '</table></td></tr>';

	            break;

	        case 'matrix_mcheck' :
	        case 'matrix_sradio' :

	            $html .= '<tr><td><table class="matrix-answer ma_' . $question_details ['question_id'] . '" cellspacing="0">';
	            if (is_array ( $cf ) && ($question_details ['qtype'] == 'matrix_mradio' || $question_details ['qtype'] == 'matrix_sradio')) {

	                foreach ( $cf as $cfs ) {
	                    $cf_str .= $cfs ['qid'] . '|' . $cfs ['operator'] . ';';
	                }

	            }
	            // $html .= '<tr><th></th>';
	            // $html .= '<th class="col">'.$data['answer']['ch']['ch_1']['text'].'</th>';
	            // $html .= '</tr>';
	            // var_dump_pre($default);
	            $i = 1;
	            foreach ( $data ['answer'] ['rh'] as $row_head ) {
	                $class = 'qt_' . $question_details ['qtype'] . ' qid_' . $question_details ['qid']; // reset class to question type and id
	                // if (is_int ( $i / 2 )) {
	                // $class = 'class="alt"';
	                    // } else {
	                    // $class = '';
	                        // }

	                        // $html .= '<tr '.$class.'><th class="row">'.$row_head['text'].'</th>';

	                        $rh_key = $row_head ['order'] . '_1';
	                        $cell_value = $data ['answer'] ['cells'] [$rh_key] ['value'];
	                        $cell_text = $data ['answer'] ['cells'] [$rh_key] ['text'];
	                        $class .= ' cell_' . $rh_key;

	                        if ($row_head ['nonecompatible'] == '1') { // mark as nonecompatible option
	                            $class .= ' nonecompatible';
	                        }

	                        if ($question_details ['required'] == '1') { // questions is required
	                            $class .= ' req_' . $question_id;
	                        }

	                        if ($question_details ['qtype'] == 'matrix_sradio') {
	                            // $class .= ' rr_' . $question_details ['qid'] . '_' . $row_head ['order'] . ' rc_' . $question_details ['qid'] . '_' . $col_head ['order'];
	                            $class .= ' rr_' . $question_details ['qid']; // sradio it's on 1 column
	                        }

	                        // if ($cell_value == $default || @in_array ( $cell_value, $default )) {
	                        if (! empty ( $qans ) && @array_key_exists ( $rh_key, $qans )) {
	                            $checked = 'checked="checked"';
	                        } else {
	                            $checked = '';
	                        }

	                        $tr_class = ' tr_' . $question_details ['question_id'] . '_' . $row_head ['order'];
	                        $td_cell_class = 'td_' . $question_details ['question_id'] . '_' . $rh_key;
	                        $th_row_class = ' th_row_' . $question_details ['question_id'] . '_' . $row_head ['order'];

	                        $html .= '<tr class="' . $tr_class . '">';
	                        switch ($question_details ['qtype']) {
	                            case 'matrix_sradio' :
	                                $html .= '<td class="' . $td_cell_class . ' special_radChkbtn" >
								      	<input class="styled ' . $class . '" ' . $checked . ' id="ans_' . $question_details ['qid'] . '_' . $rh_key . '" type="radio" name="answer[' . $question_details ['qid'] . '][' . $rh_key . ']" value="' . $cell_value . '"  />
								      </td>';
	                                $html .= '<th class="cell ' . $th_row_class . ' special_radChkbtn">
									  	<label class="label_radio" for="ans_' . $question_details ['qid'] . '_' . $rh_key . '">' . $data ['answer'] ['cells'] [$row_head ['order'] . '_1'] ['text'] . '</label>';
	                                if ($row_head ['freetext'] == 1) {
	                                    $html .= ' <i>' . wordwrap($qans [$rh_key] ['free_answer'], 75, "\n", true) . '</i></th>';//'<input type="text" name="free_answer[' . $question_details ['qid'] . '][' . $rh_key . ']" value="' . $qans [$rh_key] ['free_answer'] . '" /></th>';
	                                }
	                                break;
	                            case 'matrix_mcheck' :
	                                $html .= '<td class="' . $td_cell_class . ' special_radChkbtn">
										<input class="styled ' . $class . '" ' . $checked . ' id="ans_' . $question_details ['qid'] . '_' . $rh_key . '" type="checkbox" name="answer[' . $question_details ['qid'] . '][' . $rh_key . ']" value="' . $cell_value . '" />
									  </td>';
	                                $html .= '<th class="cell ' . $th_row_class . ' special_radChkbtn">
									  <label for="ans_' . $question_details ['qid'] . '_' . $rh_key . '">' . $data ['answer'] ['cells'] [$row_head ['order'] . '_1'] ['text'] . '</label>';
	                                if ($row_head ['freetext'] == 1) {
	                                    $html .= ' <i>' . wordwrap($qans [$rh_key] ['free_answer'], 75, "\n", true) . '</i></th>';//'<input type="text" name="free_answer[' . $question_details ['qid'] . '][' . $rh_key . ']" value="' . $qans [$rh_key] ['free_answer'] . '" /></th>';
	                                }
	                                break;
	                            default :
	                                break;
	                        }
	                        // $html .= '<th class="cell '.$th_row_class.'"><label for="ans_' . $question_details ['qid'] . '_' . $rh_key .'">' . $data ['answer'] ['cells'] [$row_head ['order'] . '_1'] ['text'] . '</label></th>';
	                        // $html .= '<td class="cell">'.$data['answer']['cells'][$row_head['order'].'_1']['value'].'</td>';
	                        $html .= '</tr>';
	                        $i ++;
	            }

	            $html .= '</table></td></tr>';

	            break;

	        case 'html' :
	        case 'html-ne' :
	            $html .= '<tr><td align="left">' . str_replace ( 'https://www.painpool.de/app', DOMAIN, html_entity_decode ( $data ['answer'] ['html'] ['text'] ) ) . '</td></tr>';
	            break;

	        case 'free_text' :
	            $html .= '<div><div style="text-align: left; page-break-inside: auto">';
	            $rh_key = '0_0'; // add rh key for ALL answers
	            if ($data ['answer'] ['text'] ['validation']) {
	                $class = 'validate_' . $data ['answer'] ['text'] ['validation'];
	                if ($data ['answer'] ['text'] ['validation'] == 'number') {
	                    $class .= ' range_' . $data ['answer'] ['text'] ['range_start'] . '_' . $data ['answer'] ['text'] ['range_end'];
	                }
	            } else {
	                $class = 'free_text_input';
	            }

	            if ($question_details ['required'] == '1') { // questions is required
	                $class .= ' req_' . $question_id;
	            }

	            /*switch ($data ['answer'] ['text'] ['validation']) {

	                case 'date' :
	                    $html .= '<input class="' . $class . '" name="answer[' . $question_details ['qid'] . ']" type="text" value="' . $qans [$rh_key] ['answer'] . '" size="10"/>';
	                    break;

	                case 'number' :
	                    $html .= '<input class="' . $class . '" name="answer[' . $question_details ['qid'] . ']" type="number" value="' . trim ( $qans [$rh_key] ['answer'] ) . '" size="3" class="numeric-key" />';
	                    break;

	                default :
	                    $html .= '<input class="' . $class . '" name="answer[' . $question_details ['qid'] . ']" type="text" value="' . $qans [$rh_key] ['answer'] . '" size="12" />';
	                    break;
	            }
	            $html .= '</td></tr>';
	            $html .= trim ( $qans [$rh_key] ['answer'] ).'</td></tr>';
	            $html .= '</td></tr>';*/
				$html .= trim ( nl2br(wordwrap($qans [$rh_key] ['answer'], 75, "\n", true)) ).'</div></div>';
	            break;

	        case 'free_textarea' :
	            $html .= '<div><div style="text-align: left; page-break-inside: auto">';
	            $rh_key = '0_0'; // add rh key for ALL answers

	            if ($question_details ['required'] == '1') { // questions is required
	                $class .= ' req_' . $question_id;
	            }

	           /* switch ($data ['answer'] ['text'] ['validation']) {

	                case 'date' :
	                    $html .= '<textarea class="' . $class . '" name="answer[' . $question_details ['qid'] . ']">' . $qans [$rh_key] ['answer'] . '</textarea>';
	                    break;

	                case 'number' :
	                    $html .= '<textarea class="' . $class . '" name="answer[' . $question_details ['qid'] . ']">' . $qans [$rh_key] ['answer'] . '</textarea>';
	                    break;

	                default :
	                    $html .= '<textarea class="' . $class . '" name="answer[' . $question_details ['qid'] . ']">' . $qans [$rh_key] ['answer'] . '</textarea>';
	                    break;
	            }
	            $html .= '</td></tr>';

	            $html .= trim ( $qans [$rh_key] ['answer'] ).'</td></tr>';

	            $html .= '</td></tr>';*/
				$html .= trim ( nl2br(wordwrap($qans [$rh_key] ['answer'], 75, "\n", true)) ).'</div></div>';
	            break;

	        case 'human' :
	        case 'human-big' :
	            $rh_key = '0_0'; // add rh key for ALL answers
	            if (! empty ( $qans [$rh_key] ['answer'] )) {
	                $export = new Export ();

	                $tmp_file = $export->temporary_image_create ( $qans [$rh_key] ['answer'], 'base64', $question_details ['qtype'] );
	                $tmp_file = basename ( $tmp_file );

	                $html .= '<tr><td style="text-align: center">';
	                // $html .= '<img style="background: url('.TEMPLATE_WWWPATH.'/images/human.jpg)" src="'.$qans[$rh_key]['answer'].'"/>';

	                // $html .= '<img style="background: url('.TEMPLATE_WWWPATH.'/images/human.jpg)" src="'.DOMAIN.'/admin/output_img.php?answer='.$qans[$rh_key]['answer_id'].'"/>';
	                $html .= '<img src="' . ABSPATH . '/_tmp/_images/' . $tmp_file . '" alt=""/>';
	                $html .= '</td></tr>';
	            }
	            break;

	        case 'barmer_treatment' :

	            $patient = $this->survey_patient_get ( $took_id );
	            $patient_id = $patient ['patient'];
	            $p = new Patient ();

	            $barmer_events = $p->planner_day_get ( $patient_id, $patient ['interview'] );

	            if ($_REQUEST ['dbg']) {
	                echo '****************************BARMER************************************************';
	                var_dump_pre ( $barmer_events );
	                var_dump_pre ( $patient );
	                echo '****************************END BARMER******************************************';
	            }

	            if ($barmer_events ['events'] && $patient ['project'] == BARMER_ID) {
	                $confirm_day = date ( 'd.m.Y', $barmer_events ['day'] );
	                $html = str_replace ( '%day%', $confirm_day, $html );
	                $html .= '<tr><td><table class="matrix-answer ma_' . $question_details ['question_id'] . '" cellspacing="0">';
	                $html .= '<input type="hidden" name="barmer_confirm_day[' . $question_details ['qid'] . ']" value="' . $confirm_day . '" />';

	                foreach ( $barmer_events ['events'] as $block_id => $bevent ) {
	                    if (! empty ( $bevent ['confirmed'] )) {
	                        $checked = 'checked="checked"';
	                    } else {
	                        $checked = '';
	                    }
	                    $html .= '<tr><td class="' . $td_cell_class . ' special_radChkbtn">
										<input class="styled ' . $class . '" ' . $checked . ' id="ans_' . $question_details ['qid'] . '_' . $block_id . '" type="checkbox" name="barmer_confirm[' . $question_details ['qid'] . '][' . $block_id . ']" value="1" />
									  </td>';
	                    $html .= '<th class="cell ' . $th_row_class . ' special_radChkbtn">
									  <label for="ans_' . $question_details ['qid'] . '_' . $block_id . '">' . $bevent ['name'] . '</label></th></tr>';
	                }
	                $html .= '</table></td></tr>';
	            } else {
	                $html = '';
	            }

	            break;

	        case 'body-m':
	        case 'body-f':
	        case 'body-m-new':
	        case 'body-f-new':

	            $html .= '<tr><td style="text-align: center"><table class="human_new_table" style="width: 100%">';
	            $html .= '<tr><td>';
	            $html .= '<table>';

	            if($question_details ['qtype'] == 'body-m' && !empty($item['description-male'])){
	                $item['description'] = $item['description-male'];
	            }

	            if($question_details ['qtype'] == 'body-f' && !empty($item['description-female'])){
	                $item['description'] = $item['description-female'];
	            }

	            if(stripos($question_details ['qtype'], 'new') !== false) {
	                $body_parts = $this->new_2019_body_areas_list;
	            } else {
	                $body_parts = $this->new_body_areas_list;
	            }

	            $i = 1;
	            foreach($body_parts as $item_key => $item) {

	                $class = 'qt_' . $question_details ['qtype'] . ' qid_' . $question_details ['qid']; //reset class to question type and id


	                $rh_key = $item_key. '_1';
	                $cell_value = $item_key;
	                $cell_text = $item_key;
	                $class .= ' body_items cell_'.$rh_key;

	                if($question_details['required'] == '1') { //questions is required
	                    $class .= ' req_'.$question_id;
	                }

	                if(is_array($qans) && @array_key_exists($rh_key, $qans)) {
	                    $checked = 'checked="checked"';
	                } else {
	                    $checked = '';
	                }

	                if(!empty($item['description'])) {
	                    $html_moreinfo = '<a href="#" class="tooltipx" style="float: left" data-tooltip="'.$item['description'].'">&nbsp;<img src="'.TEMPLATE_WWWPATH.'/images/tooltip.png" alt="" /></a>';
	                } else {
	                    $html_moreinfo = '';
	                }

	                $tr_class = ' tr_'.$question_details ['question_id'].'_'.$item['id'];
	                $td_cell_class = 'td_'.$question_details ['question_id'].'_'.$rh_key;
	                $th_row_class = ' th_row_'.$question_details ['question_id'].'_'.$item['id'];

	                $html .= '<tr class="' . $tr_class . '">';
	                $html .= '<td class="'.$td_cell_class.' special_radChkbtn">
								<input class="styled ' . $class . '" ' . $checked . ' data-ids=\''.json_encode($item['ids']).'\' id="ans_' . $question_details ['qid'] . '_' . $rh_key .'" type="checkbox" name="answer[' . $question_details ['qid'] . ']['.$rh_key.']" value="' . $item_key. '" />
							</td>';
	                $html .= '<th class="cell '.$th_row_class.' special_radChkbtn">
								  <label for="ans_' . $question_details ['qid'] . '_' . $rh_key .'">' . $item['item'] . '</label>'.$html_moreinfo.'</th>
								  		';



	                $html .= '</tr>';
	                $i ++;

	            }

	            $html .= '</table>';
	            $html .= '</td><td id="humansvgc">';
	            //$html .= '<img src="'.TEMPLATE_WWWPATH.'/images/m.svg" alt="" style="width: 80%"/>';
	            //$html .= file_get_contents(TEMPLATE_ABSPATH.'/images/'.$question_details ['qtype'].'.svg');
	            //	$html .= '<object id="humansvg" data="'.TEMPLATE_WWWPATH.'/images/'.$question_details ['qtype'].'.svg" type="image/svg+xml"></object>';
	            $html .= '</td></tr>';
	            $html .= '</table></td></tr>';
	            $html .= '<script src="'.SURVEYTAKE_JS_PATH.'/viewport-units-buggyfill.js" type="text/javascript"></script>';
	            $html .= '<script src="'.SURVEYTAKE_JS_PATH.'/viewport-units-buggyfill.hacks.js" type="text/javascript"></script>';
	            $html .= '<script>
				        // We activate contentHack, since iOS cannot handle calc with vw units.
				        // We do not use the behaviorHack property because IE9 is ok with vw units in calc statements
				        window.viewportUnitsBuggyfill.init({
				            refreshDebounceWait: 50,
				            // Content hack is needed since iOS cannot parse calc with viewport units natively.
				            hacks: window.viewportUnitsBuggyfillHacks
				          });
				      </script>';
	            break;

	        
	        case 'faces':
	            $faces_details = $data['answer']['faces'];
	            $html .= '<tr><td><table class="faces-answer fa_' . str_replace(' ','_',$question_details ['question_id']) . '" cellspacing="0" style="width:100%">';
	            
	            $nooffaces = $faces_details['nooffaces'];
	            
	            $html .= '<tr style="100%">';
	            for ($i=1; $i <= $nooffaces; $i++) {
	                switch ($i) {
	                    case 1 :
	                        $html .= '<td>'.$faces_details['label_left'].'</td>';
	                        break;
	                    case $nooffaces:
	                        $html .= '<td>'.$faces_details['label_right'].'</td>';
	                        break;
	                    default:
	                        $html .= '<td></td>';
	                        break;
	                }
	            }
	            $html .= '</tr><tr>';
	            
	            $image_filename ='';
	            $faces_indexes = [
	                3 => [1,2,6],
	                5 => [1,2,3,5,6],
	                6 => [1,2,3,4,5,6],
	            ];
	            for ($i=0; $i < $nooffaces; $i++) {
	                if ($faces_details['direction'] != 'desc') {
	                    $index = $nooffaces - $i - 1;
	                } else {
	                    $index = $i;
	                }
	                switch ($nooffaces) {
	                    case 3:
	                        if ($qans['1_1']['answer'] == $faces_indexes[$nooffaces][$index]) {
	                            $image_filename='set_of_3-'.$faces_indexes[$nooffaces][$index].'.svg';
	                        } else {
	                            $image_filename='inactive-set_of_3-'.$faces_indexes[$nooffaces][$index].'.svg';
	                        }
	                        break;
	                    case 5:
	                    case 6:
	                        if ($qans['1_1']['answer'] == $faces_indexes[$nooffaces][$index]) {
	                            $image_filename='set_of_6-'.$faces_indexes[$nooffaces][$index].'.svg';
	                        } else {
	                            $image_filename='inactive-smile-'.$faces_indexes[$nooffaces][$index].'.svg';
	                        }
	                        break;
	                }
	                $html .= '<td class="'.($qans['1_1']['answer'] == $faces_indexes[$nooffaces][$index] ? 'selected_face' :'').'"><img width="50" src="'.TEMPLATE_ABSPATH.'/images/smiles/'.$image_filename.'"></td>';
	            }
	            
	            $html .= '</tr>';
	            
	            $html .= '</table></td></tr>';
	            break;
	            
	            
	            
	        default :
	            break;
	    }

	    if ($question_details ['qtype'] == 'free_textarea' ||
			$question_details ['qtype'] == 'free_text') {
			$html .= '</div>';
		} else {
	    $html .= '</table>';
		}

	    return $html;
	}

	static function patient_replace_tokens ($html, $tokens = array ())
	{
	    if (sizeof ($tokens) > 0)
	    {
	        foreach ($tokens as $token => $data)
	        {
	            $html = str_ireplace ('$' . $token . '$', $data, $html);
	        }
	    }

	    return $html;
	}
}

?>
