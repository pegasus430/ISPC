<?php

// require_once("Pms/Form.php");
/**
 *
 *
 * @update Jan 22, 2018: @author claudiu, checked for ISPC-2071
 * Körperlicher Befund =  FormBlockBefund
 * 
 * changed: bypass Trigger() on PC
 * fixed: adding this block to a saved cf would not save to PC the first time
 *
 */
class Application_Form_FormBlockBefund extends Pms_Form 
{

	public function clear_block_data($ipid = '', $contact_form_id = 0)
	{
		if( ! empty($contact_form_id))
		{

			$Q = Doctrine_Query::create()
				->update('FormBlockBefund')
				->set('isdelete', '1')
				->where("contact_form_id= ?", $contact_form_id)
				->andWhere('ipid = ?', $ipid);
			$result = $Q->execute();

			return true;
		}
		else
		{
			return false;
		}
	}

		public function InsertData($post, $allowed_blocks)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$befund_block = new FormBlockBefund();

			$change_date = '';
			$save_2_PC =  false ;// 2 save or not 2 save into PatientCourse
			
			//set the old block values as isdelete
			$clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);

			if ( ! empty($post['old_contact_form_id']))
			{
				$change_date = $post['contact_form_change_date'];
				
				$befund_old_data = $befund_block->getPatientFormBlockBefund($post['ipid'], $post['old_contact_form_id'], true);

				if( ! in_array('befund', $allowed_blocks)) {
				    // override post data if no permissions on befund block
				    // PatientCourse will NOT be inserted
				    
				    if ( ! empty($befund_old_data)) {
				        $post['kopf'] = $befund_old_data[0]['kopf'];
				        $post['kopf_text'] = $befund_old_data[0]['kopf_text'];
				        $post['thorax'] = $befund_old_data[0]['thorax'];
				        $post['thorax_text'] = $befund_old_data[0]['thorax_text'];
				        $post['abdomen'] = $befund_old_data[0]['abdomen'];
				        $post['abdomen_text'] = $befund_old_data[0]['abdomen_text'];
				        $post['extremitaten'] = $befund_old_data[0]['extremitaten'];
				        $post['extremitaten_text'] = $befund_old_data[0]['extremitaten_text'];
				        $post['haut_wunden'] = $befund_old_data[0]['haut_wunden'];
				        $post['haut_wunden_text'] = $befund_old_data[0]['haut_wunden_text'];
				        $post['neurologisch_psychiatrisch'] = $befund_old_data[0]['neurologisch_psychiatrisch'];
				        $post['neurologisch_psychiatrisch_text'] = $befund_old_data[0]['neurologisch_psychiatrisch_text'];
				    }
				    
				} 
				else {
					//we have permissions and cf is being edited
					//write changes in PatientCourse is something was changed
					
				    if ( ! empty($befund_old_data)) {
				        
						if (($befund_old_data[0]['kopf'] != (int)$post['kopf'] || ($post['kopf'] == '2' && $befund_old_data[0]['kopf_text'] != $post['kopf_text'])) 
						    || ($befund_old_data[0]['thorax'] != (int)$post['thorax'] || ($post['thorax'] == '2' && $befund_old_data[0]['thorax_text'] != $post['thorax_text']))
						    || ($befund_old_data[0]['abdomen'] != (int)$post['abdomen'] || ($post['abdomen'] == '2' && $befund_old_data[0]['abdomen_text'] != $post['abdomen_text']))
				            || ($befund_old_data[0]['extremitaten'] != (int)$post['extremitaten'] || ($post['extremitaten'] == '2' && $befund_old_data[0]['extremitaten_text'] != $post['extremitaten_text']))
			                || ($befund_old_data[0]['haut_wunden'] != (int)$post['haut_wunden'] || ($post['haut_wunden'] == '2' && $befund_old_data[0]['haut_wunden_text'] != $post['haut_wunden_text']))
		                    || ($befund_old_data[0]['neurologisch_psychiatrisch'] != (int)$post['neurologisch_psychiatrisch'] || ($post['neurologisch_psychiatrisch'] == '2' && $befund_old_data[0]['neurologisch_psychiatrisch_text'] != $post['neurologisch_psychiatrisch_text'])))
						{
						    //something was edited, we must insert into PC   
						    $save_2_PC = true;
						}
				    } 
				    else {
				        //nothing was edited last time, or this block was added after the form was created
				        $save_2_PC = true;
				        $change_date = '';
				        
				    }	
				    
// 					$befund_values_arr = array('1' => 'krankheitsentsprechend', '2' => 'Befund');
// 				    {	
// 						/*** KOPF ***/
// 						//check if radio value is changed
// 						if($befund_old_data[0]['kopf'] != $post['kopf'] || ($post['kopf'] == '2' && $befund_old_data[0]['kopf_text'] != $post['kopf_text']))
// 						{
// 							if($post['kopf'] > '0')
// 							{
// 								$course_str .= "Kopf: ";
// 							}

// 							if($befund_old_data[0]['kopf'] > '0' && $befund_old_data[0]['kopf'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['kopf']] . ' --> ';
// 							}
// 							else if($befund_old_data[0]['kopf'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['kopf']] . ' (' . $befund_old_data[0]['kopf_text'] . ') --> ';
// 							}

// 							if($post['kopf'] > '0' && $post['kopf'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['kopf']] . "\n";
// 							}
// 							else if($post['kopf'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['kopf']] . " (" . $post['kopf_text'] . ") \n";
// 							}
// 						}


// 						/******* THORAX *******/
// 						//check if radio value is changed
// 						if($befund_old_data[0]['thorax'] != $post['thorax'] || ($post['thorax'] == '2' && $befund_old_data[0]['thorax_text'] != $post['thorax_text']))
// 						{
// 							if($post['thorax'] > '0')
// 							{
// 								$course_str .= "Thorax: ";
// 							}

// 							if($befund_old_data[0]['thorax'] > '0' && $befund_old_data[0]['thorax'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['thorax']] . ' --> ';
// 							}
// 							else if($befund_old_data[0]['thorax'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['thorax']] . ' (' . $befund_old_data[0]['thorax_text'] . ') --> ';
// 							}

// 							if($post['thorax'] > '0' && $post['thorax'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['thorax']] . "\n";
// 							}
// 							else if($post['thorax'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['thorax']] . " (" . $post['thorax_text'] . ") \n";
// 							}
// 						}


// 						/******* ABDOMEN *******/
// 						//check if radio value is changed
// 						if($befund_old_data[0]['abdomen'] != $post['abdomen'] || ($post['abdomen'] == '2' && $befund_old_data[0]['abdomen_text'] != $post['abdomen_text']))
// 						{
// 							if($post['abdomen'] > '0')
// 							{
// 								$course_str .= "Abdomen: ";
// 							}

// 							if($befund_old_data[0]['abdomen'] > '0' && $befund_old_data[0]['abdomen'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['abdomen']] . ' --> ';
// 							}
// 							else if($befund_old_data[0]['abdomen'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['abdomen']] . ' (' . $befund_old_data[0]['abdomen_text'] . ') --> ';
// 							}

// 							if($post['abdomen'] > '0' && $post['abdomen'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['abdomen']] . "\n";
// 							}
// 							else if($post['abdomen'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['abdomen']] . " (" . $post['abdomen_text'] . ") \n";
// 							}
// 						}


// 						/******* EXTREMITATEN *******/
// 						//check if radio value is changed
// 						if($befund_old_data[0]['extremitaten'] != $post['extremitaten'] || ($post['extremitaten'] == '2' && $befund_old_data[0]['extremitaten_text'] != $post['extremitaten_text']))
// 						{
// 							if($post['extremitaten'] > '0')
// 							{
// 								$course_str .= "Extremitaten: ";
// 							}
							
// 							if($befund_old_data[0]['extremitaten'] > '0' && $befund_old_data[0]['extremitaten'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['extremitaten']] . ' --> ';
// 							}
// 							else if($befund_old_data[0]['extremitaten'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['extremitaten']] . ' (' . $befund_old_data[0]['extremitaten_text'] . ') --> ';
// 							}

// 							if($post['extremitaten'] > '0' && $post['extremitaten'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['extremitaten']] . "\n";
// 							}
// 							else if($post['extremitaten'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['extremitaten']] . " (" . $post['extremitaten_text'] . ") \n";
// 							}
// 						}


// 						/******* HAUT WUNDEN *******/
// 						//check if radio value is changed
// 						if($befund_old_data[0]['haut_wunden'] != $post['haut_wunden'] || ($post['haut_wunden'] == '2' && $befund_old_data[0]['haut_wunden_text'] != $post['haut_wunden_text']))
// 						{
// 							if($post['haut_wunden'] > '0')
// 							{
// 								$course_str .= "Haut/Wunden: ";
// 							}
							
// 							if($befund_old_data[0]['haut_wunden'] > '0' && $befund_old_data[0]['haut_wunden'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['haut_wunden']] . ' --> ';
// 							}
// 							else if($befund_old_data[0]['haut_wunden'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['haut_wunden']] . ' (' . $befund_old_data[0]['haut_wunden_text'] . ') --> ';
// 							}

// 							if($post['haut_wunden'] > '0' && $post['haut_wunden'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['haut_wunden']] . "\n";
// 							}
// 							else if($post['haut_wunden'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['haut_wunden']] . " (" . $post['haut_wunden_text'] . ") \n";
// 							}
// 						}


// 						/******* NEUROLOGISCH PSYCHIATRISCH *******/
// 						//check if radio value is changed
// 						if($befund_old_data[0]['neurologisch_psychiatrisch'] != $post['neurologisch_psychiatrisch'] || ($post['neurologisch_psychiatrisch'] == '2' && $befund_old_data[0]['neurologisch_psychiatrisch_text'] != $post['neurologisch_psychiatrisch_text']))
// 						{
// 							if($post['neurologisch_psychiatrisch'] > '0')
// 							{
// 								$course_str .= "neurologisch / psychiatrisch: ";
// 							}
							
// 							if($befund_old_data[0]['neurologisch_psychiatrisch'] > '0' && $befund_old_data[0]['neurologisch_psychiatrisch'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['neurologisch_psychiatrisch']] . ' --> ';
// 							}
// 							else if($befund_old_data[0]['neurologisch_psychiatrisch'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$befund_old_data[0]['neurologisch_psychiatrisch']] . ' (' . $befund_old_data[0]['neurologisch_psychiatrisch_text'] . ') --> ';
// 							}

// 							if($post['neurologisch_psychiatrisch'] > '0' && $post['neurologisch_psychiatrisch'] != '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['neurologisch_psychiatrisch']] . "\n";
// 							}
// 							else if($post['neurologisch_psychiatrisch'] == '2')
// 							{
// 								$course_str .= $befund_values_arr[$post['neurologisch_psychiatrisch']] . " (" . $post['neurologisch_psychiatrisch_text'] . ") \n";
// 							}
// 						}

// 						if(strlen($course_str)>'0')
// 						{
// 							//befund edited entry in verlauf
// 							$cust = new PatientCourse();
// 							$cust->ipid = $post['ipid'];
// 							$cust->course_date = date("Y-m-d H:i:s", time());
// 							$cust->course_type = Pms_CommonData::aesEncrypt("B");
// 							$cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str).$change_date);
// 							$cust->user_id = $userid;
// 							$cust->done_date = date('Y-m-d H:i:s', strtotime($post['date']));
// 							$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
// 							$cust->done_id = $post['contact_form_id'];
							
// 							// ISPC-2071 - added tabname, this entry must be grouped/sorted
// 							$cust->tabname = Pms_CommonData::aesEncrypt("FormBlockBefund");
							
// 							$cust->save();
// 						}
// 					}
				}
				
			} 
			else {
		        //new cf, save
		        $save_2_PC = true;
			}
			
			
			$course_str = '';
			if (in_array('befund', $allowed_blocks)) {
			    //write in patient course, with conditions
			    
			    $befund_values_arr = array('1' => 'krankheitsentsprechend', '2' => 'Befund');
			    
			    /*** KOPF ***/
			    if($post['kopf']  > '0')
			    {
		            $course_str .= "Kopf: ";
		            
			        if($post['kopf'] > '0' && $post['kopf'] != '2')
			        {
			            $course_str .= $befund_values_arr[$post['kopf']] . "\n";
			        }
			        else if($post['kopf'] == '2')
			        {
			            $course_str .= $befund_values_arr[$post['kopf']] . " (" . $post['kopf_text'] . ") \n";
			        }
			    }
			    
			    
			    /******* THORAX *******/
			    if($post['thorax']  > '0')
			    {
		            $course_str .= "Thorax: ";
			    
			        if($post['thorax'] > '0' && $post['thorax'] != '2')
			        {
			            $course_str .= $befund_values_arr[$post['thorax']] . "\n";
			        }
			        else if($post['thorax'] == '2')
			        {
			            $course_str .= $befund_values_arr[$post['thorax']] . " (" . $post['thorax_text'] . ") \n";
			        }
			    }
			    
			    
			    /******* ABDOMEN *******/
			    if($post['abdomen']  > '0')
			    {
		            $course_str .= "Abdomen: ";
			        if($post['abdomen'] > '0' && $post['abdomen'] != '2')
			        {
			            $course_str .= $befund_values_arr[$post['abdomen']] . "\n";
			        }
			        else if($post['abdomen'] == '2')
			        {
			            $course_str .= $befund_values_arr[$post['abdomen']] . " (" . $post['abdomen_text'] . ") \n";
			        }
			    }
			    
			    
			    /******* EXTREMITATEN *******/
			    if($post['extremitaten'] > '0')
			    {
		            $course_str .= "Extremitaten: ";
			    
			        if($post['extremitaten'] > '0' && $post['extremitaten'] != '2')
			        {
			            $course_str .= $befund_values_arr[$post['extremitaten']] . "\n";
			        }
			        else if($post['extremitaten'] == '2')
			        {
			            $course_str .= $befund_values_arr[$post['extremitaten']] . " (" . $post['extremitaten_text'] . ") \n";
			        }
			    }
			    
			    
			    /******* HAUT WUNDEN *******/
			    if($post['haut_wunden'] > '0')
			    {
		            $course_str .= "Haut/Wunden: ";
			    
			        if($post['haut_wunden'] > '0' && $post['haut_wunden'] != '2')
			        {
			            $course_str .= $befund_values_arr[$post['haut_wunden']] . "\n";
			        }
			        else if($post['haut_wunden'] == '2')
			        {
			            $course_str .= $befund_values_arr[$post['haut_wunden']] . " (" . $post['haut_wunden_text'] . ") \n";
			        }
			    }
			    
			    
			    /******* NEUROLOGISCH PSYCHIATRISCH *******/
			    if($post['neurologisch_psychiatrisch']  > '0')
			    {
		            $course_str .= "neurologisch / psychiatrisch: ";

			        if($post['neurologisch_psychiatrisch'] > '0' && $post['neurologisch_psychiatrisch'] != '2')
			        {
			            $course_str .= $befund_values_arr[$post['neurologisch_psychiatrisch']] . "\n";
			        }
			        else if($post['neurologisch_psychiatrisch'] == '2')
			        {
			            $course_str .= $befund_values_arr[$post['neurologisch_psychiatrisch']] . " (" . $post['neurologisch_psychiatrisch_text'] . ") \n";
			        }
			    }
			    
			    
			    
// 			    if( ! empty($course_str) && $save_2_PC) {
			        
// 			        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':'.date('s', $now)));
// 			        //befund edited entry in verlauf
// 			        $cust = new PatientCourse();
// 			        $cust->ipid = $post['ipid'];
// 			        $cust->course_date = date("Y-m-d H:i:s", time());
// 			        $cust->course_type = Pms_CommonData::aesEncrypt("B");
// 			        $cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str));
// 			        $cust->user_id = $userid;
// // 			        $cust->done_date = date('Y-m-d H:i:s', strtotime($post['date']));
// 			        $cust->done_date = $done_date;
// 			        $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
// 			        $cust->done_id = $post['contact_form_id'];
			        
// 			        // ISPC-2071 - added tabname, this entry must be grouped/sorted
// 			        $cust->tabname = Pms_CommonData::aesEncrypt("FormBlockBefund");
			        
// 			        $cust->save();
// 			    }
			}

			
			
			$cust = new FormBlockBefund();
			//static required data
			$cust->ipid = $post['ipid'];
			$cust->contact_form_id = $post['contact_form_id'];

			//dynamic changed data
			$cust->kopf = $post['kopf'];
			$cust->kopf_text = htmlspecialchars($post['kopf_text']);
			$cust->thorax = $post['thorax'];
			$cust->thorax_text = htmlspecialchars($post['thorax_text']);
			$cust->abdomen = $post['abdomen'];
			$cust->abdomen_text = htmlspecialchars($post['abdomen_text']);
			$cust->extremitaten = $post['extremitaten'];
			$cust->extremitaten_text = htmlspecialchars($post['extremitaten_text']);
			$cust->haut_wunden = $post['haut_wunden'];
			$cust->haut_wunden_text = htmlspecialchars($post['haut_wunden_text']);
			$cust->neurologisch_psychiatrisch = $post['neurologisch_psychiatrisch'];
			$cust->neurologisch_psychiatrisch_text = htmlspecialchars($post['neurologisch_psychiatrisch_text']);
			

			
			
			if ($save_2_PC 
			    && in_array('befund', $allowed_blocks)
			    && ! empty($course_str) 
			    && ($pc_listener = $cust->getListener()->get('PostInsertWriteToPatientCourse')) ) 
			{
			    $change_date = "";//removed from pc; ISPC-2071
			    $pc_listener->setOption('disabled', false);
			    $pc_listener->setOption('course_title', $course_str . $change_date);
			    $pc_listener->setOption('done_date', date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', $now))));
			    $pc_listener->setOption('user_id', $userid);
			    
			}
			
			$cust->save();
		}

}

?>