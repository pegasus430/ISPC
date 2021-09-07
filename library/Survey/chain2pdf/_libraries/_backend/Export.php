<?php
require_once (LIB_PATH . '/_system/random_compat-2.0.18/lib/random.php'); //load compatibility lib for mPDF php 5 random_bytes and random_int()
require_once (LIB_PATH . '/_system/mpdf-composer/autoload.php'); //load mpdf


class Export {
	function __construct() {
		$this->db = $GLOBALS ['db'];
	}
	function temporary_files_delete($folder, $age = '86400') {
		if ($handle = opendir ( $folder )) {
			while ( false !== ($entry = readdir ( $handle )) ) {
				$filename = $folder . '/' . $entry;
				$mtime = @filemtime ( $filename );
				if (is_file ( $filename ) && $mtime && (time () - $mtime > $age)) {
					@unlink ( $filename );
				}
			}
			closedir ( $handle );
		}
	}
	function temporary_image_create($data, $type = 'svg', $qtype = 'human') {
		$tmp_file = uniqid ( 'img' . rand ( 1000, 9999 ) );
		$tmp_file_path = TMP_ABSPATH . '/_images/' . $tmp_file . '.png';
		$tmp_folder = TMP_ABSPATH . '/_images';

		$this->temporary_files_delete ( $tmp_folder, '7200' ); // delete all files older than 2 hours

		switch ($type) {
			case 'svg' :
				if (get_magic_quotes_gpc ()) {
					$data = stripslashes ( $data );
				}

				$data = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . $data;

				$tmp_file_path = TMP_ABSPATH . '/_images/' . $tmp_file . '.jpg';

				$handle = fopen ( $tmp_file_path, 'w+' );
				fclose ( $handle );

				$im = new Imagick ();
				$im->readImageBlob ( $data );
				$im->setImageFormat ( "jpeg" );

				$im->writeImage ( $tmp_file_path );

				$im->clear ();
				$im->destroy ();

				break;

			case 'base64' :
				$data = substr ( $data, stripos ( $data, '64,' ) + 3 );
				$data = base64_decode ( $data );

				//test image first, PHP bug https://bugs.php.net/bug.php?id=73986

				//$tmp_file_test = tempnam(sys_get_temp_dir(), 'base64_');
				$tmp_file_test = tempnam(TMP_ABSPATH . '/_images/', 'base64_');

				$handle = fopen($tmp_file_test, 'w');
				fwrite($handle, $data);
				fclose($handle);

				$output = `php -r "imagecreatefrompng('$tmp_file_test');" 2>&1`;

				if (!empty($output)){
					//force some data to be written that can be silently discarded by PHP, yes, it's lame
					$data = 'a';
				}
				@unlink($tmp_file_test);

				// transparent answer image
				$im = @imagecreatefromstring ( $data );

				$rgb = imagecolorat ( $im, 1, 1 );
				$colors = imagecolorsforindex ( $im, $rgb );
				// var_dump($colors);
				// exit;

				if ($colors ['alpha'] > 0 && $colors ['red'] == 0) { // stupid hack CHANGE THIS!!!!!
					imagecolortransparent ( $im, imagecolorallocatealpha ( $im, 0, 0, 0, 127 ) );
				} elseif ($colors ['red'] == 255) {
					imagecolortransparent ( $im, imagecolorallocatealpha ( $im, 255, 255, 255, 127 ) );
				}

				// $black = imagecolorallocate($im, 0, 0, 0);
				// imagecolortransparent($im, $black);
				// imagealphablending($im, false);
				// imagesavealpha($im, true);

				// header('Content-type: image/png');
				// imagepng($im);
				// exit;

				// human body background
				if ($qtype == 'human-big') {
					$bg = @imagecreatefromjpeg ( TEMPLATE_ABSPATH . '/images/human_big.jpg' );
				} else {
					$bg = @imagecreatefromjpeg ( TEMPLATE_ABSPATH . '/images/human.jpg' );
				}

				// imagealphablending($bg, false);
				// imagesavealpha($bg, true);

				if ($qtype == 'human-big') {
					imagecopymerge ( $bg, $im, 0, 0, 0, 0, 850, 600, 100 );
				} else {
					imagecopymerge ( $bg, $im, 0, 0, 0, 0, 550, 388, 100 );
				}

				// header('Content-type: image/png');
				// imagepng($bg);
				//
				// exit;
				//
				// $out = imagecreatetruecolor(550, 388);
				//
				// imagecolortransparent($out, imagecolorallocatealpha($out, 0, 0, 0, 127));
				// imagealphablending($out, false);
				// imagesavealpha($out, false);
				//
				// imagecopyresampled($out, $bg, 0, 0, 0, 0, 550, 388, 550, 388);
				//
				// imagecopyresampled($out, $im, 0, 0, 0, 0, 550, 388, 550, 388);

				// header('Content-type: image/png');
				// imagepng($out);
				// exit;

				imagepng ( $bg, $tmp_file_path );

				// imagedestroy($im);
				imagedestroy ( $bg );

				break;

			default :
				break;
		}

		if (is_readable ( $tmp_file_path )) {
			return $tmp_file_path;
		} else {
			return false;
		}
	}

	function chain_generate_mpdf_footer($chain_took_id) {

		$this->db->query ( '
				SELECT  `p`.*, '.sql_aes_decrypt().', `pc`.*, `p`.`id` as `patient_id`, `s`.`name` as `survey_name`
				FROM ' . TABLE_PREFIX . '_patient `p`, 
				' . TABLE_PREFIX . '_patient2chain `pc`,
				' . SURVEY_TABLE_PREFIX . 'survey2chain `sc`,
				' . SURVEY_TABLE_PREFIX . 'master_chains `s`
				WHERE `p`.`id` = `pc`.`patient`
				AND `pc`.`chain` = `sc`.`chain`
				AND `sc`.`survey` = `s`.`id` 
				AND `pc`.`id` = "' . $chain_took_id . '"' );

		$took_details = $this->db->get_results ( null, ARRAY_A )[0];

		if (empty($took_details)) {
			return false;
		}

		$htmlfooter = '
        <table width="100%">
            <tr>
                <td width="50%">
                    <span>'
			.$took_details['first_name'].' '.$took_details['last_name'].(!empty($took_details['dob'])? ', '.date('d.m.Y', strtotime($took_details['dob'])) : '').'
                    </span>
                </td>
                <td width="50%" style="text-align: right;">
                    '.$took_details['survey_name'].'
                </td>
            </tr>
        </table>';

		return $htmlfooter;
	}

	function chain_generate_mpdf($patientchain, $form = false, $dummy = false, $remove_skipped = true) {
	    $data = $this->chain_surveys_questions_get ( $patientchain );
	    if ($remove_skipped) {
			$this->chain_surveys_questions_remove_skipped($data);
		}
	    $surveys = $data ['surveys'];
	    $answers = $data ['answers'];

	    $pat_sur_query = $this->db->query ( '
				SELECT * FROM ' . TABLE_PREFIX . '_patient_surveys WHERE  patientchain = ' . $patientchain . '' );
	    $patient_chain_details = $this->db->get_results ( null, ARRAY_A );

	    if ($patient_chain_details [0] ['end'] != 0) {
	        $survey_complete_date = date ( 'd.m.Y', $patient_chain_details [0] ['end'] );
	    } else {
	        $survey_complete_date = '';
	    }

	    $pat_query = $this->db->query ( '
				SELECT  p.*, '.sql_aes_decrypt().', pc.*, p.id as patient_id FROM ' . TABLE_PREFIX . '_patient p, ' . TABLE_PREFIX . '_patient2chain pc
				WHERE  p.id = pc.patient AND pc.id = ' . $patientchain . '' );
	    $patientdetails = $this->db->get_results ( null, ARRAY_A );

	    $project_id = $patientdetails [0] ['project'];

//	    if ($_SESSION ['user'] ['level'] == '3') {
	        $patientname = '<div class="export_pat_details"><div class="pat_det"><b>Name:</b> ' . $patientdetails [0] ['last_name'] . ' ' . $patientdetails [0] ['first_name'] . '  </div><div class="date">' . $survey_complete_date . '</div></div>';
//	    } else {
//	        $patientname = '<div class="export_pat_details"><div class="pat_det"><b>PPID:</b> ' . $patientdetails [0] ['ppid'] . ' </div><div class="date">' . $survey_complete_date . '</div></div>';
//	    }

	    $survey_take = new painPoolSurveyTake ();
	    $the_scores = $survey_take->patient_scores_get_all ( $patientdetails [0] ['patient_id'], $project_id );
	    $chain_scores_array = $the_scores ['chain_score'] [$patientchain];

	    if ($_REQUEST ['dbgz'] == '3') {
	        var_dump_pre ( $patientchain );
	        var_dump_pre ( $the_scores );
	    }

	    if ($fb_korff_score) {
	        switch ($fb_korff_score) {

	            case '1' :
	                $fb_korff_text = 'geringe Schmerzintensität und geringe Beeinträchtigung (< 3 Disability-Punkte)';
	                break;

	            case '2' :
	                $fb_korff_text = 'hohe Schmerzintensität und geringe Beeinträchtigung (< 3 Disability-Punkte)';
	                break;

	            case '3' :
	                $fb_korff_text = 'hohe schmerzbedingte Beeinträchtigung, mäßig limitierend (3-4 Disability-Punkte)';
	                break;

	            default :
	                $fb_korff_text = 'hohe schmerzbedingte Beeinträchtigung, stark limitierend (5-6 Disability-Punkte)';
	                break;
	        }
	    } else {
	        $fb_korff_text = '';
	    }

	    $i = 1;
	    if ($chain_scores_array) {

	        /*$korff_score_id = array (
	         13,
	         63,
	         81
	         );
	         $sbls_score_id = array (
	         25,
	         75
	         );
	         $sbla_score_id = array (
	         26,
	         76
	         );

	         $korff_nachfrage_id = 96;
	         $verlaufsbogen_id = 80;
	         $schmerzfragen_id = 93;
	         $sbls_id = 77;

	         $dass_id = 55; // (DASS-copied)
	         $depression_id = array (
	         21,
	         53
	         );
	         $angst_id = array (
	         22,
	         54
	         );
	         $stress_id = array (
	         23,
	         55
	         );
	         $qlip_score_id = array (
	         29,
	         50,
	         85
	         );
	         $pp42fw7_score_id = array (
	         24,
	         56
	         );

	         $nrs_score_id = array (
	         69,82,39,41,47
	         );

	         $nrsm_score_id = array (
	         69,82,39,41,47
	         );

	         $mpss_score_id = array (
	         12,34
	         );

	         */

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






	        foreach ( $chain_scores_array as $key => $value ) {
	            if ($value !== false) {
	                foreach ( $value as $ky => $survey_details ) {

	                    if (is_int ( $i / 2 )) {
	                        $alt_bg = ' ';
	                    } else {
	                        $alt_bg = 'class="alt_backg"';
	                    }

	                    $chain_survey_score [$ky] ['the_score'] = $survey_details ['score'];
	                    $chain_survey_score [$ky] ['the_details'] = $survey_details ['details'] ['score'];

	                    if ($survey_details ['details'] ['range_end'] != '0') {
	                        $survey_details_range = $survey_details ['details'] ['range_start'] . ' - ' . $survey_details ['details'] ['range_end'];
	                    } else {
	                        $survey_details_range = '';
	                    }

//	                    if (in_array ( $survey_details ['details'] ['id'], $korff_score_id )) {
//	                        switch ($survey_details ['score']) {
//	                            case '1' :
//	                                $korf_score_text = 'geringe Schmerzintensität und geringe Beeinträchtigung (< 3 Disability-Punkte)';
//	                                break;
//
//	                            case '2' :
//	                                $korf_score_text = 'hohe Schmerzintensität und geringe Beeinträchtigung (< 3 Disability-Punkte)';
//	                                break;
//
//	                            case '3' :
//	                                $korf_score_text = 'hohe schmerzbedingte Beeinträchtigung, mäßig limitierend (3-4 Disability-Punkte)';
//	                                break;
//
//	                            default :
//	                                $korf_score_text = 'hohe schmerzbedingte Beeinträchtigung, stark limitierend (5-6 Disability-Punkte)';
//	                                break;
//	                        }
//	                        $score_text = $korf_score_text;
//	                    } else if (in_array ( $survey_details ['details'] ['id'], $sbla_score_id )) {
//	                        $score_text = 'Als Grenzwert für ein erhöhtes affektives Schmerzerleben kann ein Summenwert von 8 angesetzt werden. (größer = schlechter)';
//	                    } else if (in_array ( $survey_details ['details'] ['id'], $sbls_score_id )) {
//	                        $score_text = 'Die "Sensorischen Items" werden einzeln bewertet, da keine klinisch relevanten Werte kalkuliert werden können.';
//	                    } else if (in_array ( $survey_details ['details'] ['id'], $depression_id )) {
//	                        $score_text = 'Werte größer/gleich 10 deuten auf eine Depression hin.';
//	                    } else if (in_array ( $survey_details ['details'] ['id'], $angst_id )) {
//	                        $score_text = 'Werte größer/gleich 6 deuten auf eine Angst -störung hin.';
//	                    } else if (in_array ( $survey_details ['details'] ['id'], $stress_id )) {
//	                        $score_text = 'Werte größer/gleich 10 deuten auf Stress hin.';
//	                    } else if (in_array ( $survey_details ['details'] ['id'], $pp42fw7_score_id )) {
//	                        $score_text = 'Ein Wert von 10 Punkten und darunter ist für Schmerzpatienten ein niedriger und daher auffälliger Wert des allgemeinen Wohlbefindens.';
//	                    } else if (in_array ( $survey_details ['details'] ['id'], $mpss_score_id )) {
//	                        $score_text = 'Dieser Patient befindet sich nach dem Mainzer Stadienmodell der Schmerzchronifizierung (MPSS) nach Gerbershagen im Stadium '.$survey_details ['score'].'.';
//	                    } else if (in_array ( $survey_details ['details'] ['id'], $nrs_score_id ) || in_array ( $survey_details ['details'] ['id'], $nrsm_score_id )) {
//	                        $score_text = 'Die Numerische Rating Skala (NRS) gibt den Schmerz von 0 - 10 an, wobei 0 nicht vorhanden und 10 der stärkste vorstellbare Schmerz ist.';
//	                    } else if (in_array ( $survey_details ['details'] ['id'], $qlip_score_id )) {
//
//	                        if ($survey_details ['score'] >= 0 && $survey_details ['score'] <= 10) {
//	                            $score_text = 'maximale Beeinträchtigung der Lebensqualität';
//	                        } elseif ($survey_details ['score'] >= 11 && $survey_details ['score'] <= 20) {
//	                            $score_text = 'Beeinträchtigung der Lebensqualität';
//	                        } elseif ($survey_details ['score'] >= 21 && $survey_details ['score'] <= 29) {
//	                            $score_text = 'geringe Beeinträchtigung der Lebensqualität';
//	                        } elseif ($survey_details ['score'] >= 30 && $survey_details ['score'] <= 43) {
//	                            $score_text = 'keine Beeinträchtigung der Lebensqualität';
//	                        }
//
//	                        $score_text .= '<br />Der erreichbare Summenscore variiert <br /> von „0“ (= maximale Beeinträchtigung der Lebensqualität) <br /> bis „43“ (= keine Beeinträchtigung der Lebensqualität).';
//
//	                    } else if(!empty($survey_details ['extra'])) {
//	                        $score_text = $survey_details ['extra'];
//	                    } else {
//	                        $score_text = '--';
//	                    }

						$score_text = $survey_take->get_score_text($survey_details ['details'] ['id'], $survey_details ['score'], $survey_details ['extra']);

	                    // if($survey_details['extra'] != ''){
	                    // $chain_scores_str .= '<tr><td '.$alt_bg.'>'.$survey_details['details']['score'].'</td><td '.$alt_bg.'> '.$survey_details['extra'].'</td><td '.$alt_bg.'>'.$survey_details_range.'</td></tr>';
	                    // }

						//PAINPOOL-444 display line instead of value for category score
						if($chain_scores_array[510]) {
							if ($survey_details['details']['survey'] == 510) {
								$survey_details ['details'] ['score'] = '----';
								$survey_details ['score'] = '----';
								$survey_details_range = '----';
							}
						}

	                    $chain_scores_str .= '<tr class="score_row">
												<td class = "row" style="border-left:0;border-top: 1px solid  #e1e1e1" ' . $alt_bg . '>' . $survey_details ['details'] ['score'] . '</td>
												<td ' . $alt_bg . '>' . $survey_details ['score'] . '</td>';
	                    if($chain_scores_array[150]){
	                        if($survey_details['details']['survey'] == 150) {
	                            $chain_scores_str .= '<td ' . $alt_bg . '>';
	                            if($survey_details ['value_extra']){
	                                $chain_scores_str .= $survey_details ['value_extra'];
	                            }
	                            else {
	                                $chain_scores_str .= '--';
	                            }
	                            $chain_scores_str .= '</td>';
	                        }
	                        else {
	                            $chain_scores_str .= '<td ' . $alt_bg . '> -- </td>';
	                        }
	                    }
	                    if($chain_scores_array[154]){
	                        if($survey_details['details']['survey'] == 154) {
	                            $chain_scores_str .= '<td ' . $alt_bg . '>';
	                            if($survey_details ['value_extra']){
	                                $chain_scores_str .= $survey_details ['value_extra'];
	                            }
	                            else {
	                                $chain_scores_str .= '--';
	                            }
	                            $chain_scores_str .= '</td>';
	                        }
	                        else {
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

	    $surveytake = new painPoolSurveyTake ();

	    if ($surveys) {
	        $html .= $patientname; // show patient name
	        if(!$dummy) {
	            foreach ( $surveys as $chain_key => $chain_arr ) {

	                foreach ( $chain_arr as $survey => $survey_arrs ) {
	                    foreach ( $survey_arrs as $q_key => $questions ) {
	                        $html .= $surveytake->question_generate_html_dompdf ( $questions, $answers [$patientchain] [$survey], $data ['took_id'] );
	                        //$html .= '<br />';
	                    }
	                }
	            }
	        }
	        if ($chain_scores_str) { // show scores
	            $html .= '<br /><br /><a name="scores"></a><table width="100%" class="export_chain_scores" style="page-break-inside: avoid;" >
							<tr>
								<th width="20%" style="border-left:0;"><b>Score</b></th>
								<th><b>Wert</b></th>';
	            if($chain_scores_array[150]){$html .= '<th><b>'.$chain_scores_array[150][151]['value_extra_text'].'</b></th>';}
	            if($chain_scores_array[154]){$html .= '<th><b>'.$chain_scores_array[154][150]['value_extra_text'].'</b></th>';}
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

	function chain_surveys_questions_get($patientchain = false) {
		if ($patientchain) {
			// $query = $this->db->query('SELECT * FROM ' . SURVEY_TABLE_PREFIX . 'survey2chain sc
			// LEFT JOIN ' . SURVEY_TABLE_PREFIX . 'surveys s ON sc.survey = s.id
			// WHERE sc.chain ="' . $chain . '"
			// ORDER BY `order` ASC');
			$query = $this->db->query ( 'SELECT ps.id as took_id, s.id as survey_id, pc.*, ps.*, sc.*, s.* FROM ' . TABLE_PREFIX . '_patient2chain pc, ' . SURVEY_TABLE_PREFIX . 'survey2chain sc, ' . SURVEY_TABLE_PREFIX . 'master_chains s, ' . TABLE_PREFIX . '_patient_surveys ps
					WHERE sc.survey = s.id AND pc.id = ' . $patientchain . ' AND sc.chain = pc.chain AND ps.patientchain = pc.id
					ORDER BY `order` ASC' );
			if ($_REQUEST ['dbg']) {
				$this->db->debug ();
			}

			if ($query) {
				$surveys = $this->db->get_results ( null, ARRAY_A );
				if ($surveys) {
					$survey_take = new painPoolSurveyTake ();

					foreach ( $surveys as $i_survey ) {
						if ($i_survey ['master_chain'] > 0) {
							$chain_links = $survey_take->master_chain_surveys ( $i_survey ['survey_id'] );
							if (is_array ( $chain_links )) {
								foreach ( $chain_links as $chain_link ) {
									$new_survey = $i_survey;

									$new_survey ['survey_id'] = $chain_link ['survey_id'];

									$surveyz [] = $new_survey;
								}
							}
						} else {
							$surveyz [] = $i_survey;
						}
					}

					foreach ( $surveyz as $k_survey => $v_survey ) {

						$questions [$v_survey ['survey_id']] = $survey_take->survey_get_questions ( $v_survey ['survey_id'], false );

						$result ['answers'] [$patientchain] [$v_survey ['survey_id']] = $survey_take->survey_results_get ( $v_survey ['took_id'] );
						$result ['took_id'] = $v_survey ['took_id'];

						if ($questions [$v_survey ['survey_id']]) {
							foreach ( $questions [$v_survey ['survey_id']] as $question ) {
								$q_data [$question ['qid']] = $survey_take->question_get_details ( $question ['qid'] );

								if ($q_data [$question ['qid']] ['question_details']) {
									$result ['surveys'] [$patientchain] [$v_survey ['survey_id']] [$question ['qid']] = $q_data [$question ['qid']];
								}
							}
						}
					}
					if ($_REQUEST ['dbg']) {
						//var_dump_pre ( $result ['answers'] );
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

	function generate_mpdfs_chain($chain_id, $practice_id, $is_dummy = false) {

		//administrative
		$folder = id_encode($practice_id);
		$filename = $chain_id;
		$filename_with_skipped = $chain_id.'_with_skipped';
		if (!file_exists(SURVEY_RESULTS_EXPORT_PATH.DIRECTORY_SEPARATOR.$folder)) {
			mkdir(SURVEY_RESULTS_EXPORT_PATH.DIRECTORY_SEPARATOR.$folder, 0777, true);
		}
		$folder = SURVEY_RESULTS_EXPORT_PATH.DIRECTORY_SEPARATOR.$folder;

		$html_survey_footer = $this->chain_generate_mpdf_footer($chain_id);


		//GENERATE WITHOUT SKIPPED QUESTIONS
		//$htmlpdf = $export->chain_generate_pdf($_REQUEST['chid'], $is_dummy); // html2pdf method
		$htmlpdf = $this->chain_generate_mpdf($chain_id, $form = true, $is_dummy,true);
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
		//$mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
		//$mpdf->WriteHTML($htmlpdf, \Mpdf\HTMLParserMode::HTML_BODY);
		//$mpdf->shrink_tables_to_fit = 0;
		$mpdf->DefHTMLFooterByName('survey_custom_footer',$html_survey_footer);
		$mpdf->WriteHTML($htmlpdfall, \Mpdf\HTMLParserMode::DEFAULT_MODE);
		$mpdf->Output($filename.'.pdf', 'D');

		//file_put_contents($folder.DIRECTORY_SEPARATOR.$filename, $dompdf->output());
		//$dompdf->stream($file);
		unset($dompdf);
		unset($mpdf);
		unset($htmlpdf);
		unset($htmlpdfall);


//		//GENERATE WITH SKIPPED QUESTIONS
//		//$htmlpdf = $export->chain_generate_pdf($_REQUEST['chid'], $is_dummy); // html2pdf method
//		$htmlpdf = $this->chain_generate_mpdf($chain_id, $form = true, $is_dummy,false);
//		$htmlpdf = $this->html_prepare_for_pdf($htmlpdf);
//
//		$htmlpdfall ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
//
//<html xmlns="http://www.w3.org/1999/xhtml"><head>
//	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
//    <link rel="stylesheet" href="'.TEMPLATE_ABSPATH.'/styles/pdf/survey-print-mpdf.css" /><title></title></head><body>
//			'.$htmlpdf.'
//	</body></html>';
//
//		$mpdf = new Mpdf\Mpdf([
//			'tempDir' => TMP_ABSPATH . '/mpdf',
//			//'defaultPagebreakType' => 'slice',
//			//'defaultCssFile' => TEMPLATE_ABSPATH.'/styles/pdf/print.css'
//		]);
//		//$mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
//		//$mpdf->WriteHTML($htmlpdf, \Mpdf\HTMLParserMode::HTML_BODY);
//		$mpdf->DefHTMLFooterByName('survey_custom_footer',$html_survey_footer);
//		$mpdf->WriteHTML($htmlpdfall, \Mpdf\HTMLParserMode::DEFAULT_MODE);
//		$mpdf->Output($folder.DIRECTORY_SEPARATOR.$filename_with_skipped, 'F');
//
//		//file_put_contents($folder.DIRECTORY_SEPARATOR.$filename_with_skipped, $dompdf->output());
//
//		$this->db->query('UPDATE `'.TABLE_PREFIX.'_patient_surveys` ps SET `exported` = "yes" WHERE `ps`.`patientchain` = '.$chain_id.'; ');

	}

	function html_prepare_for_pdf($html) { // replaces form elements for PDF generating

		// define functions used for callbacks
		if (!function_exists('radio_check')) {
			function radio_check($match)
			{
				if (stripos($match [0], 'checked="checked"') !== false) {
					return '<img src="' . TEMPLATE_ABSPATH . '/images/rad_btn_checked.jpg" alt="" />';
				} else {
					return '<img src="' . TEMPLATE_ABSPATH . '/images/rad_btn_unchecked.jpg" alt="" />';
				}
			}
		}
		if (!function_exists('checkbox_check')) {
			function checkbox_check($match)
			{
				if (stripos($match [0], 'checked="checked"') !== false) {
					return '<img src="' . TEMPLATE_ABSPATH . '/images/check_btn_checked.jpg" alt="" />';
				} else {
					return '<img src="' . TEMPLATE_ABSPATH . '/images/check_btn_unchecked.jpg" alt="" />';
				}
			}
		}

		// match checkboxes
		$checkbox_pat = "/<input.*type=[\"']?checkbox[\"']?.*>/iU";

		// match radios
		$radio_pat = "/<input.*type=[\"']?radio[\"']?.*>/iU";

		// match all css classes
		//$class_pat = "/class=\".*\"/iU"; // not needed for dompdf

		// replace
		$html = preg_replace_callback ( $radio_pat, 'radio_check', $html );
		$html = preg_replace_callback ( $checkbox_pat, 'checkbox_check', $html );
		if ($_REQUEST['show_template_images'] == 1) {
		    $html = str_replace ( ABSPATH, DOMAIN, $html );
		} else {
		    $html = str_replace ( DOMAIN, ABSPATH, $html );
		}

		// pt page breaks inside avoid wrap tables in divs
		//$html = str_replace ( '<table ', '<div class="pagebreak-avoid"><table ', $html );
		//$html = str_replace ( '</table>', '</table></div>', $html );

		return $html;
	}

	function chain_surveys_questions_remove_skipped ( &$data )
	{
		foreach ($data['surveys'] as $chain_key => $chain_arr) {
			foreach ($chain_arr as $survey => $survey_arrs) {
				foreach ($survey_arrs as $question => $details) {
					if (! $this->chain_surveys_question_has_answer($question, $data['answers']) && in_array($details['question_details']['type'], ['html', 'html-ne']) === false) {
						unset( $data ['surveys'] [$chain_key] [$survey] [$question]);
					}
				}
			}
		}

		/*
			foreach ($skipped_questions as $skipped_question => $value) {
				if ($surveys) {
					foreach ( $surveys as $chain_key => $chain_arr ) {
						foreach ($chain_arr as $survey => $survey_arrs) {
							if (isset($survey_arrs[$skipped_question])) {
								unset ($surveys[$chain_key][$survey][$skipped_question]);
							}
						}
					}
				}
			}
		*/

	}

	function chain_surveys_question_has_answer ( $questionID, $answers )
	{
		foreach ($answers as $chain_key => $chain_arr) {
			foreach ($chain_arr as $survey => $answer_list) {
				if (isset($answer_list[$questionID])) {
					return true;
				}
			}
		}
		return false;
	}

}



?>
