<?php
/**
 *
 * @author Ancuta
 * Aprilie 12 2019
 * // Maria:: Migration ISPC to CISPC 08.08.2020 
 */
use Dompdf\Dompdf;
use Dompdf\Options;

class RubinController extends Pms_Controller_Action {

    public function init()
    {
		    
// 		    $export = new DoctrineExport();
// 		    $export->out(array('patient_contactphone'));
		    
			/* Initialize action controller here */

			if( strlen($_GET['id']) > '0')	{
				$this->dec_id = Pms_Uuid::decrypt($_GET['id']);
				$this->enc_id = $_GET['id'];
			}
			elseif(strlen($_REQUEST['id'])>'0')
			{
				$this->dec_id = Pms_Uuid::decrypt($_REQUEST['id']);
				$this->enc_id = $_REQUEST['id'];
			}
			else
			{
				$actionName = $this->getRequest()->getActionName();
				
				if( ! in_array($actionName, $this->allow_with_no_patient_id)) {
					//redir to overview if patient encripted is is empty
	// 				$this->_redirect(APP_BASE . "overview/overview");
	// 				exit;
				}
			}
			
			//ISPC-791 secrecy tracker
			$user_access = PatientPermissions::document_user_acces();

			//Check patient permissions on controller and action
			$patient_privileges = PatientPermissions::checkPermissionOnRun();
			if(!$patient_privileges)
			{
				$this->_redirect(APP_BASE . 'error/previlege');
			}
			
			

			$this
			->setActionsWithPatientinfoAndTabmenus([
			    /*
			     * actions that have the patient header
			     */
	 
				'rubin',
				'rubiniadl',
				'rubinforms',
			    
				'mna',
				'iadl',
				'mmst',
				'tug',
				'whoqol',
				'demtect',
			    
				'gds',
				'npi',
				'bdi',
				'cmai',
				'nosger',
				'demstepcare',
				'dsv',
			    'badl',   //ISPC-2455
			    'cmscale',   //ISPC-2456
			    'carerelated',   //ISPC-2492 Lore 02.12.2019
			    'carepatient',   //ISPC-2493 Lore 03.12.2019
			    'dscdsv',       //ISPC-2509 Lore 06.01.2020
			])
			->setActionsWithJsFile([
			    /*
			     * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
			     */
				'rubin', //ISPC -2353 - create a form RUBIN - Mini Nutritional Assessment
				'rubiniadl', 
				'rubinforms',
			    
				'mna',
				'iadl',
				'mmst',
				'tug',
				'whoqol',
				'demtect',
				'gds',
				'npi',
				'bdi',
				'cmai',
				'nosger',
				'demstepcare',
				'dsv',
			    'badl',    //ISPC-2455
			    'cmscale',   //ISPC-2456
			    'carerelated',   //ISPC-2492 Lore 02.12.2019
			    'carepatient',   //ISPC-2493 Lore 03.12.2019
			    'dscdsv',       //ISPC-2509 Lore 06.01.2020
			])
			->setActionsWithLayoutNew([
			    /*
			     * actions that will use layout_new.phtml
			     * Actions With Patientinfo And Tabmenus also use layout_new.phtml 
			     */
			    'rubinq'
			])
			;
				
			
		}    

		//clean filename and extension
		private function clean_filename( $filename =  "ispc_download.pdf")
		{
		    //sanitize filename
		    $filename = Pms_CommonData::filter_filename($filename, true);
		     
		    if ( ($ext = strtolower( substr($filename, strlen($filename) - 4, 4))) != '.pdf') {
		        $filename .= '.pdf';
		    }
		     
		    return $filename;
		}
		
		public function dompdf_ToFTP($dompdf_output ='', $pdfname = 'ispc_document.pdf'  )
		{
		    $legacy_path    = "uploads";
		    $is_zipped      = NULL;
		    $foster_file    = false;
		    $clientid       = NULL;
		    $filepass       = NULL;
		
		    $pdfname = $this->clean_filename($pdfname);
		
		    $temp_folder_pdf = Pms_CommonData::uniqfolder_v2( PDF_PATH , 'dompdf_');
		
		    $file_path = $temp_folder_pdf ."/". $pdfname ;
		
		    @file_put_contents($file_path, $dompdf_output);
		
		    $result =  Pms_CommonData::ftp_put_queue( $file_path , $legacy_path , $is_zipped, $foster_file, $clientid, $filepass);
		
		     
		    $file_path_for_db = false;
		
		    if ($result !== false) {
		
		        $pathinfo = pathinfo($file_path);
		        $fulldir = $pathinfo['dirname'];
		        $dir = pathinfo($fulldir , PATHINFO_BASENAME);
		
		        $file_path_for_db = $dir . "/" . $pdfname;
		
		    }
		
		    return $file_path_for_db ;
		     
		}
 
		
		
		
		
		
		
		
	public function iadlAction()
	{
		$ipid = $this->ipid;
		$clientid = $this->logininfo->clientid;
		$userid = $this->logininfo->userid;
		
		$form_ident = 'iadl';
		$this->view->form_ident = $form_ident;

		$form_name = strtoupper('RUBIN-'.$form_ident);
		$form_tabname = strtolower('RUBIN_'.$form_ident);
		
		$form = new Application_Form_PatientRubinForms(array(
				'_patientMasterData'    => $this->_patientMasterData,
				'_block_name'           => $form_name,
				'_page_name'           => $form_name,
		));
		  
		$gender = $this->_patientMasterData['sex'];
		
		 
		if($_REQUEST['form_id'])
		{
			$form_id = $_REQUEST['form_id'];
		}
	    else
	    {
	        /*
    		 * 12.07.2019 - Ancuta
    		// Check if patient has a saved form
    	    $form_details = array();
    	    $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
    	    if(!empty($form_details)){
    	        $form_id = $form_details['id'];
    	    }
    	    */
	        // allow multiple forms per patient
	        $form_id = null;	
	    }
	
		$saved_values = array();
		$saved_values = $this->_rubin_forms_GatherDetails($form_id, $form_ident);

		
		$form->create_form($saved_values, $form_ident);
	
		$request = $this->getRequest();

		
		if ( ! $request->isPost()) {
	
			//TODO move to populate
			//$form->populate($options);
	
	
		} elseif ($request->isPost()) {
	
		    
			$post = $request->getPost();
			
			$form->populate($post);
			
			$post_form = $post[$form_ident];
			
	
			if ( $form->isValid($post)) // no validation is implemented
			{
				
				if($_POST[$form_ident]['formular']['button_action'] == "save")
				{
					//$post_form['userid'] = $this->logininfo->userid;
					/* ------------------- SAVE FORM ----------------------------------- */
				    
				    $patient_rubin  = $form->save_form_rubin($this->ipid, $post_form);
						
				    // get gender of patient
				    
				    $form_total  = $post_form['form_content']['form_total'];
				    $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
				    $form_date_dmY   = date('d.m.Y',strtotime($form_date));
				    
				    $score_text = "";
				    if($gender == "1"){
				        $score_text = 'Männliche Patienten Gesamtpunktzahl: '.$form_total.' / 5';
				    } elseif($gender == "2"){
				        $score_text = 'Weibliche Patienten Gesamtpunktzahl: '.$form_total.' / 8';
				    } else{
				        $score_text = 'Männliche/Weibliche Patienten Gesamtpunktzahl: '.$form_total.' / 8';
				    }
                     
				    
					if($patient_rubin->id)
					{
					    $course_values[$form_ident]['course_type'] = "K";
					    $course_values[$form_ident]['course_tabname'] = "patient_rubin_".$form_ident;
					    $course_values[$form_ident]['course_done_name'] = "patient_rubin_".$form_ident;
						
// 					    $coursetitle =   $this->translate('patient_rubin_'.$form_ident.'  was saved')."\n";
					    $coursetitle =   "RUBIN-Instrumentelle Aktivitäten\n";
					    $coursetitle .= "Datum der Befragung: ".$form_date_dmY." \n";
						$coursetitle .=  $score_text;
						
						$custcourse = new PatientCourse();
						$custcourse->ipid = $ipid;
						$custcourse->course_date = date("Y-m-d H:i:s", time());
						$custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
						$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
						$custcourse->user_id = $userid;
						$custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
						$custcourse->recordid = $patient_rubin->id;
						$custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
						$custcourse->done_id = $patient_rubin->id;
						$custcourse->done_date = $form_date;
						$custcourse->save();
					}    
					
						
					//$form->removeDisplayGroup('form_actions');
					$form->removeDecorator('Form');
					$form->removeSubForm('tabs_navi');
					$form->removeSubForm('form_actions');
					$today_date = date('d.m.Y');
					$nice_name_epid = $this->_patientMasterData['nice_name_epid'];
						
						
					$html_form  = $form->__toString();
					$html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
					/*$html_print = sprintf($bsHead, APP_BASE)
					. $html_form
					. $bsFoot
					;*/
// 					echo $html_print;exit;
						
					$this->view->form_ident = $form_ident;
					$this->view->form_pdf = $html_form; //this is the body of the pdf
						
					$html_print = $this->view->render("templates/rubinforms_pdf.phtml");
// 					echo $html_print; exit;
					$options = new Options();
					$options->set('isRemoteEnabled', false);
					$dompdf = new Dompdf($options);
						
					$dompdf->loadHtml($html_print);
					// (Optional) Setup the paper size and orientation
					$dompdf->setPaper('A4', 'portrait');
						
					$dompdf->set_option("enable_php",true);
					$dompdf->set_option('defaultFont', 'times');
					$dompdf->set_option("fontHeightRatio",0.90);
						
					// Render the HTML as PDF
					$dompdf->render();
						
					$canvas = $dompdf->get_canvas();
					
					$footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
					$footer_font_size = 8;
					
					$footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
					$text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
					$footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
					
					
					$canvas->page_text(
					    ($canvas->get_width() - $text_width)/2,
					    $canvas->get_height()-30,
					    $footer_text,
					    $footer_font_family,
					    $footer_font_size,
					    array(0,0,0));
					
					
						
					
					$output = $dompdf->output();
						
					// Output the generated PDF to Browser
// 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
// 					                       $dompdf->stream();
// 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
					
					$patient_file_title = 'patient_rubin_'.$form_ident.'_file';
					
					$result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
						
					if ($result !== false) {
	
						$encrypted = Pms_CommonData::aesEncryptMultiple(array(
								'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
								'file_name' => $result,
								'file_type' => 'PDF',
						));
							
							
						$entity = new PatientFileUpload ();
						//bypass triggers, we will use our own
						$entity->triggerformid = null;
						$entity->triggerformname = null;
						$entity->title = $encrypted['title'];
						$entity->ipid = $this->ipid;
						$entity->file_name = $encrypted['file_name'];
						$entity->file_type = $encrypted['file_type'];
						//$entity->recordid = $patient_rubin->id;
						$entity->tabname = 'patient_rubin_'.$form_ident;
						$entity->system_generated = "0"; 
						$entity->save();
	
						$recordid = $entity->id;
	
						
						$comment =   $this->translate('patient_rubin_'.$form_ident.' PDF was created');
						$comment =   str_replace('%date', $form_date_dmY, $comment);
						$cust = new PatientCourse();
						$cust->ipid = $this->ipid;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt($comment);
						$cust->recordid = $recordid;
						$cust->tabname = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_saved');
						$cust->user_id = $userid;
						$cust->done_name = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_generated');;
						$cust->done_date = $form_date;
						$cust->save();
					}
						
						
					// empty the post by using a redirect
					//$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ), array (
					// 						$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ) . "&selected_month=" . $query_date, array (
					//"exit" => true
// 					) );
					$this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);

// 					$this->_redirect(APP_BASE . "rubin/rubin".$form_ident."?id=" . $_REQUEST['id']);
				}
					
	
			} else {
	
	
				$form->populate($post);
	
			}
	
		}
	
		//@todo : move messages in layout
		$this->view->SuccessMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('SuccessMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
				);
		$this->view->ErrorMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('ErrorMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
				);
	
		$this->_helper->flashMessenger->clearMessages('ErrorMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
		$this->_helper->flashMessenger->clearMessages('SuccessMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
		$this->view->form = $form;
		$this->view->enc_id = $this->enc_id;
	}
		
		
		
	public function mmstAction()
	{
		$ipid = $this->ipid;
		$clientid = $this->logininfo->clientid;
		$userid = $this->logininfo->userid;
		
		$form_ident = 'mmst';
		$this->view->form_ident = $form_ident;

		$form_name = strtoupper('RUBIN-'.$form_ident);
		$form_tabname = strtolower('RUBIN_'.$form_ident);
		
		$form = new Application_Form_PatientRubinForms(array(
				'_patientMasterData'    => $this->_patientMasterData,
				'_block_name'           => $form_name,
				'_page_name'           => $form_name,
		));
		  
		
		if($_REQUEST['form_id'])
		{
			$form_id = $_REQUEST['form_id'];
		}
	    else
	    {
	        /*
    		 * 12.07.2019 - Ancuta
    		// Check if patient has a saved form
    	    $form_details = array();
    	    $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
    	    if(!empty($form_details)){
    	        $form_id = $form_details['id'];
    	    }
    	    */
	        // allow multiple forms per patient
	        $form_id = null;	        
	        
	    }
	    
	    $saved_values = array();
		$saved_values = $this->_rubin_forms_GatherDetails($form_id, $form_ident);
	
		
		$form->create_form($saved_values, $form_ident);
	
		$request = $this->getRequest();

		
		if ( ! $request->isPost()) {
	
			//TODO move to populate
			//$form->populate($options);
	
	
		} elseif ($request->isPost()) {
	
		    
			$post = $request->getPost();

			$form->populate($post);
			
			$post_form = $post[$form_ident];
			
	
			if ( $form->isValid($post)) // no validation is implemented
			{
				if($_POST[$form_ident]['formular']['button_action'] == "save")
				{
					/* ------------------- SAVE FORM ----------------------------------- */
				    $patient_rubin  = $form->save_form_rubin($this->ipid, $post_form);
						
				    
				    $form_total  = $post_form['form_content']['form_total'];
				    $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
				    $form_date_dmY   = date('d.m.Y',strtotime($form_date));

				    $score_text = "";
				    
				    if ($form_total <= 9 )
				    {
    				    $score_text = "Schwere Demenz";
				    } 
				    elseif ($form_total >=10 && $form_total <= 19)
				    {
    				    $score_text = "Mittelschwere Demenz";
				    }
				    elseif ($form_total >= 20 && $form_total <= 26)
				    {
    				    $score_text = "Leichte Demenz";
				    }
				    elseif ($form_total >=27 && $form_total <= 30)
				    {
    				    $score_text = "Keine Demenz";
				    }
				    
					if($patient_rubin->id)
					{
					    $course_values[$form_ident]['course_type'] = "K";
					    $course_values[$form_ident]['course_tabname'] = "patient_rubin_".$form_ident;
					    $course_values[$form_ident]['course_done_name'] = "patient_rubin_".$form_ident;
						
// 					    $coursetitle =   $this->translate('patient_rubin_'.$form_ident.'  was saved')."\n";
					    $coursetitle =   "RUBIN - Mini-Mental-Status-Test\n";
					    $coursetitle .= "Datum der Befragung: ".$form_date_dmY;
					    $coursetitle .= "\n Interpretation des Testergebnisses: ".$form_total." (".$score_text.")";
						
						$custcourse = new PatientCourse();
						$custcourse->ipid = $ipid;
						$custcourse->course_date = date("Y-m-d H:i:s", time());
						$custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
						$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
						$custcourse->user_id = $userid;
						$custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
						$custcourse->recordid = $patient_rubin->id;
						$custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
						$custcourse->done_date = $form_date ;
						$custcourse->done_id = $patient_rubin->id;
						$custcourse->save();
					}    
					
						
					//$form->removeDisplayGroup('form_actions');
					$form->removeDecorator('Form');
					$form->removeSubForm('tabs_navi');
					$form->removeSubForm('form_actions');
					$today_date = date('d.m.Y');
					$nice_name_epid = $this->_patientMasterData['nice_name_epid'];
						
						
					$html_form  = $form->__toString();
// 					dd($html_form);
					$html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
					/*$html_print = sprintf($bsHead, APP_BASE)
					. $html_form
					. $bsFoot
					;*/
 					//echo $html_print;exit;
						
					$this->view->form_ident = $form_ident;
					$this->view->form_pdf = $html_form; //this is the body of the pdf
						
					$html_print = $this->view->render("templates/rubinforms_pdf.phtml");
 					//echo $html_print; exit;
					$options = new Options();
					$options->set('isRemoteEnabled', false);
					$dompdf = new Dompdf($options);
						
					$dompdf->loadHtml($html_print);
					// (Optional) Setup the paper size and orientation
					$dompdf->setPaper('A4', 'portrait');
						
					$dompdf->set_option("enable_php",true);
					$dompdf->set_option('defaultFont', 'times');
					$dompdf->set_option("fontHeightRatio",0.90);
						
					// Render the HTML as PDF
					$dompdf->render();
						
					$canvas = $dompdf->get_canvas();
					
					$footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
					$footer_font_size = 8;
					
					$footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
					$text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
					$footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
					
					
					$canvas->page_text(
					    ($canvas->get_width() - $text_width)/2,
					    $canvas->get_height()-30,
					    $footer_text,
					    $footer_font_family,
					    $footer_font_size,
					    array(0,0,0));
					
					
						
					
					$output = $dompdf->output();
						
					// Output the generated PDF to Browser
// 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
// 					                       $dompdf->stream();
// 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
					
					$patient_file_title = 'patient_rubin_'.$form_ident.'_file';
					
					$result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
						
					if ($result !== false) {
	
						$encrypted = Pms_CommonData::aesEncryptMultiple(array(
								'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
								'file_name' => $result,
								'file_type' => 'PDF',
						));
							
							
						$entity = new PatientFileUpload ();
						//bypass triggers, we will use our own
						$entity->triggerformid = null;
						$entity->triggerformname = null;
						$entity->title = $encrypted['title'];
						$entity->ipid = $this->ipid;
						$entity->file_name = $encrypted['file_name'];
						$entity->file_type = $encrypted['file_type'];
						//$entity->recordid = $patient_rubin->id;
						$entity->tabname = 'patient_rubin_'.$form_ident;
						$entity->system_generated = "0"; 
						$entity->save();
	
						$recordid = $entity->id;
	
						$pdf_date = date('d.m.Y');
						$comment =   $this->translate('patient_rubin_'.$form_ident.' PDF was created');
						$comment =   str_replace('%date', $form_date_dmY, $comment);
						$cust = new PatientCourse();
						$cust->ipid = $this->ipid;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt($comment);
						$cust->recordid = $recordid;
						$cust->tabname = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_saved');
						$cust->user_id = $userid;
						$cust->done_name = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_generated');;
						$cust->done_date = $form_date;
						$cust->save();
							
					}
						
						
					// empty the post by using a redirect
					//$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ), array (
					// 						$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ) . "&selected_month=" . $query_date, array (
					//"exit" => true
// 					) );
// 					$this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);

					//$this->_redirect(APP_BASE . "rubin/".$form_ident."?id=" . $_REQUEST['id']);
					$this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
				}
					
	
			} else {
	
	
				$form->populate($post);
	
			}
	
		}
	
		//@todo : move messages in layout
		$this->view->SuccessMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('SuccessMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
				);
		$this->view->ErrorMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('ErrorMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
				);
	
		$this->_helper->flashMessenger->clearMessages('ErrorMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
		$this->_helper->flashMessenger->clearMessages('SuccessMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
		$this->view->form = $form;
		$this->view->enc_id = $this->enc_id;
	}
		
	public function tugAction()
	{
		$ipid = $this->ipid;
		$clientid = $this->logininfo->clientid;
		$userid = $this->logininfo->userid;
		
		$form_ident = 'tug';
		$this->view->form_ident = $form_ident;

		$form_name = strtoupper('RUBIN-'.$form_ident);
		$form_tabname = strtolower('RUBIN_'.$form_ident);
		
		$form = new Application_Form_PatientRubinForms(array(
				'_patientMasterData'    => $this->_patientMasterData,
				'_block_name'           => $form_name,
				'_page_name'           => $form_name,
		));
		  


		if($_REQUEST['form_id'])
		{
		    $form_id = $_REQUEST['form_id'];
		}
		else
		{
	        /*
    		 * 12.07.2019 - Ancuta
    		// Check if patient has a saved form
    	    $form_details = array();
    	    $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
    	    if(!empty($form_details)){
    	        $form_id = $form_details['id'];
    	    }
    	    */
	        // allow multiple forms per patient
	        $form_id = null;	    
		}
		 
		$saved_values = array();
		$saved_values = $this->_rubin_forms_GatherDetails($form_id, $form_ident);
		
		
		$form->create_form($saved_values, $form_ident);
	
		$request = $this->getRequest();

		
		if ( ! $request->isPost()) {
	
			//TODO move to populate
			//$form->populate($options);
	
	
		} elseif ($request->isPost()) {
	
		    
			$post = $request->getPost();
 
			$form->populate($post);
			
			$post_form = $post[$form_ident];
 
			
				
	
			if ( $form->isValid($post)) // no validation is implemented
			{
				
				if($_POST[$form_ident]['formular']['button_action'] == "save")
				{
					/* ------------------- SAVE FORM ----------------------------------- */
				    
				    $patient_rubin  = $form->save_form_rubin($this->ipid, $post_form);
						
				    $form_total  = $post_form['form_content']['form_total'];
				    $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
				    $form_date_dmY   = date('d.m.Y',strtotime($form_date));
				    
				    $score_text ="";
					if($patient_rubin->id)
					{
					    $course_values[$form_ident]['course_type'] = "K";
					    $course_values[$form_ident]['course_tabname'] = "patient_rubin_".$form_ident;
					    $course_values[$form_ident]['course_done_name'] = "patient_rubin_".$form_ident;
						
// 					    $coursetitle =   $this->translate('patient_rubin_'.$form_ident.'  was saved')."\n";
					    $coursetitle  = "RUBIN - Timed Up &amp;  Go\n";
					    $coursetitle .= "Datum der Befragung: ".$form_date_dmY;
// 					    $coursetitle .= "Interpretation des Testergebnisses: ".$form_total."(".$score_text.")";
						
						$custcourse = new PatientCourse();
						$custcourse->ipid = $ipid;
						$custcourse->course_date = date("Y-m-d H:i:s", time());
						$custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
						$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
						$custcourse->user_id = $userid;
						$custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
						$custcourse->recordid = $patient_rubin->id;
						$custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
						$custcourse->done_date = $form_date ;
						$custcourse->done_id = $patient_rubin->id;
						$custcourse->save();
					} 
						
					//$form->removeDisplayGroup('form_actions');
					$form->removeDecorator('Form');
					$form->removeSubForm('tabs_navi');
					$form->removeSubForm('form_actions');
					$today_date = date('d.m.Y');
					$nice_name_epid = $this->_patientMasterData['nice_name_epid'];
						
						
					$html_form  = $form->__toString();
					$html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
					/*$html_print = sprintf($bsHead, APP_BASE)
					. $html_form
					. $bsFoot
					;*/
// 					echo $html_print;exit;
						
					$this->view->form_ident = $form_ident;
					$this->view->form_pdf = $html_form; //this is the body of the pdf
						
					$html_print = $this->view->render("templates/rubinforms_pdf.phtml");
// 					echo $html_print; exit;
					$options = new Options();
					$options->set('isRemoteEnabled', false);
					$dompdf = new Dompdf($options);
						
					$dompdf->loadHtml($html_print);
					// (Optional) Setup the paper size and orientation
					$dompdf->setPaper('A4', 'portrait');
						
					$dompdf->set_option("enable_php",true);
					$dompdf->set_option('defaultFont', 'times');
					$dompdf->set_option("fontHeightRatio",0.90);
						
					// Render the HTML as PDF
					$dompdf->render();
						
					$canvas = $dompdf->get_canvas();
					
					$footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
					$footer_font_size = 8;
					
					$footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
					$text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
					$footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
					
					
					$canvas->page_text(
					    ($canvas->get_width() - $text_width)/2,
					    $canvas->get_height()-30,
					    $footer_text,
					    $footer_font_family,
					    $footer_font_size,
					    array(0,0,0));
					
					
						
					
					$output = $dompdf->output();
						
					// Output the generated PDF to Browser
// 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
// 					                       $dompdf->stream();
// 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
					
					$patient_file_title = 'patient_rubin_'.$form_ident.'_file';
					
					$result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
						
					if ($result !== false) {
	
						$encrypted = Pms_CommonData::aesEncryptMultiple(array(
								'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
								'file_name' => $result,
								'file_type' => 'PDF',
						));
							
							
						$entity = new PatientFileUpload ();
						//bypass triggers, we will use our own
						$entity->triggerformid = null;
						$entity->triggerformname = null;
						$entity->title = $encrypted['title'];
						$entity->ipid = $this->ipid;
						$entity->file_name = $encrypted['file_name'];
						$entity->file_type = $encrypted['file_type'];
						//$entity->recordid = $patient_rubin->id;
						$entity->tabname = 'patient_rubin_'.$form_ident;
						$entity->system_generated = "0"; 
						$entity->save();
	
						$recordid = $entity->id;
	
						
						$comment =   $this->translate('patient_rubin_'.$form_ident.' PDF was created');
						$comment =   str_replace('%date', $form_date_dmY, $comment);
						$cust = new PatientCourse();
						$cust->ipid = $this->ipid;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt($comment);
						$cust->recordid = $recordid;
						$cust->tabname = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_saved');
						$cust->user_id = $userid;
						$cust->done_name = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_generated');;
						$cust->done_date = $form_date;						
						$cust->save();
							
							
							
					}
						
						
					// empty the post by using a redirect
					//$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ), array (
					// 						$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ) . "&selected_month=" . $query_date, array (
					//"exit" => true
// 					) );
					$this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);

// 					$this->_redirect(APP_BASE . "rubin/rubin".$form_ident."?id=" . $_REQUEST['id']);
				}
					
	
			} else {
	
	
				$form->populate($post);
	
			}
	
		}
	
		//@todo : move messages in layout
		$this->view->SuccessMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('SuccessMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
				);
		$this->view->ErrorMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('ErrorMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
				);
	
		$this->_helper->flashMessenger->clearMessages('ErrorMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
		$this->_helper->flashMessenger->clearMessages('SuccessMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
		$this->view->form = $form;
		$this->view->enc_id = $this->enc_id;
	}
		
		
	public function whoqolAction()
	{
		$ipid = $this->ipid;
		$clientid = $this->logininfo->clientid;
		$userid = $this->logininfo->userid;
		
		$form_ident = 'whoqol';
		$this->view->form_ident = $form_ident;

		$form_name = strtoupper('RUBIN-'.$form_ident);
		$form_tabname = strtolower('RUBIN_'.$form_ident);
		
		$form = new Application_Form_PatientRubinForms(array(
				'_patientMasterData'    => $this->_patientMasterData,
				'_block_name'           => $form_name,
				'_page_name'           => $form_name,
		));
		  
		
		if($_REQUEST['form_id'])
		{
			$form_id = $_REQUEST['form_id'];
		}
	    else
	    {
    		/*
    		 * 12.07.2019 - Ancuta 
    		// Check if patient has a saved form
    	    $form_details = array();
    	    $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
    	    if(!empty($form_details)){
    	        $form_id = $form_details['id'];
    	    }
    	     */
	        // allow multiple forms per patient
    	    $form_id = null;
	    }
	    
	    $saved_values = array();
		$saved_values = $this->_rubin_forms_GatherDetails($form_id, $form_ident);
	
	
		$form->create_form($saved_values, $form_ident);
	
		$request = $this->getRequest();

		
		if ( ! $request->isPost()) {
	
			//TODO move to populate
			//$form->populate($options);
	
	
		} elseif ($request->isPost()) {
	
		    
			$post = $request->getPost();

			$form->populate($post);
			
			$post_form = $post[$form_ident];
			
	
			if ( $form->isValid($post)) // no validation is implemented
			{
				
				if($_POST[$form_ident]['formular']['button_action'] == "save")
				{
					/* ------------------- SAVE FORM ----------------------------------- */
				    
				    foreach($post_form['form_content'] as $q_ident=>$q_opts){
			             $quest[$q_ident] = $q_opts['opt_1'];
				    }
				    for($r=1;$r<28;$r++){
				        if(!isset($quest['q_'.$r])){
				            $quest['q_'.$r] = 0 ; 
				        }
				    }
				    
				    foreach($quest as $q_nr=>$q_value){
				        ${$q_nr}=$q_value;
				    }
				    
				   $score['Physische Gesundheit'] = round( (4 * ((6-$q_3)+(6-$q_4)+$q_10+$q_15+$q_16+$q_17+$q_18)/7) ,'2' ) ;
				   $score['Psychologische Gesundheit'] = round( (4*($q_5+$q_6+$q_7+$q_11+$q_19+(6-$q_26))/6),'2');
				   $score['Soziale Beziehungen'] = round( (4 *($q_20+$q_21+$q_22)/3) ,'2' ) ;
				   $score['Umwelt'] = round( (4*($q_8+$q_9+$q_12+$q_13+$q_14+$q_23+$q_24+$q_25)/8),'2' ) ;
				    
				    $patient_rubin  = $form->save_form_rubin($this->ipid, $post_form);
				    
				    $form_total  = $post_form['form_content']['form_total'];
				    $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
				    $form_date_dmY   = !empty($post_form[$form_ident]['form_date']) ? date("d.m.Y",strtotime($post_form[$form_ident]['form_date'])) : date("d.m.Y");
				    $score_text = "";
	 
				    
				    if($patient_rubin->id)
				    {
				        $course_values[$form_ident]['course_type'] = "K";
				        $course_values[$form_ident]['course_tabname'] = "patient_rubin_".$form_ident;
				        $course_values[$form_ident]['course_done_name'] = "patient_rubin_".$form_ident;
				    
// 				        $coursetitle =   $this->translate('patient_rubin_'.$form_ident.'  was saved')."\n";
   				        $coursetitle =  "RUBIN- WHOQOL-BREF";
				        $coursetitle .= "\n Datum der Befragung: ".$form_date_dmY;
				        $coursetitle .= "\n Physische Gesundheit: ".$score['Physische Gesundheit']."\n";
				        $coursetitle .= "Psychologische Gesundheit: ".$score['Psychologische Gesundheit']."\n";
				        $coursetitle .= "Soziale Beziehungen: ".$score['Soziale Beziehungen']."\n";
				        $coursetitle .= "Umwelt: ".$score['Umwelt'];
				    
				        $custcourse = new PatientCourse();
				        $custcourse->ipid = $ipid;
				        $custcourse->course_date = date("Y-m-d H:i:s", time());
				        $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
				        $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
				        $custcourse->user_id = $userid;
				        $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
				        $custcourse->recordid = $patient_rubin->id;
				        $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
				        $custcourse->done_date = $form_date ;
				        $custcourse->done_id = $patient_rubin->id;
				        $custcourse->save();
				    }
						
					//$form->removeDisplayGroup('form_actions');
					$form->removeDecorator('Form');
					$form->removeSubForm('tabs_navi');
					$form->removeSubForm('form_actions');
					if($post_form['before_anamnesis_total'] >=12)
					{
						$form->removeSubForm('anamnesis');
						$form->removeSubForm('anamnesis_total');
					}
					$today_date = date('d.m.Y');
					$nice_name_epid = $this->_patientMasterData['nice_name_epid'];
						
						
					$html_form  = $form->__toString();
					$html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
					/*$html_print = sprintf($bsHead, APP_BASE)
					. $html_form
					. $bsFoot
					;*/
// 					echo $html_print;exit;
						
					$this->view->form_ident = $form_ident;
					$this->view->form_pdf = $html_form; //this is the body of the pdf
						
					$html_print = $this->view->render("templates/rubinforms_pdf.phtml");
// 					echo $html_print; exit;
					$options = new Options();
					$options->set('isRemoteEnabled', false);
					$dompdf = new Dompdf($options);
						
					$dompdf->loadHtml($html_print);
					// (Optional) Setup the paper size and orientation
					$dompdf->setPaper('A4', 'portrait');
						
					$dompdf->set_option("enable_php",true);
					$dompdf->set_option('defaultFont', 'times');
					$dompdf->set_option("fontHeightRatio",0.90);
						
					// Render the HTML as PDF
					$dompdf->render();
						
					$canvas = $dompdf->get_canvas();
					
					$footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
					$footer_font_size = 8;
					
					$footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
					$text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
					$footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
					
					
					$canvas->page_text(
					    ($canvas->get_width() - $text_width)/2,
					    $canvas->get_height()-30,
					    $footer_text,
					    $footer_font_family,
					    $footer_font_size,
					    array(0,0,0));
					
					
						
					
					$output = $dompdf->output();
						
					// Output the generated PDF to Browser
// 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
// 					                       $dompdf->stream();
// 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
					
					$patient_file_title = 'patient_rubin_'.$form_ident.'_file';
					
					$result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
						
					if ($result !== false) {
	
						$encrypted = Pms_CommonData::aesEncryptMultiple(array(
								'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
								'file_name' => $result,
								'file_type' => 'PDF',
						));
							
							
						$entity = new PatientFileUpload ();
						//bypass triggers, we will use our own
						$entity->triggerformid = null;
						$entity->triggerformname = null;
						$entity->title = $encrypted['title'];
						$entity->ipid = $this->ipid;
						$entity->file_name = $encrypted['file_name'];
						$entity->file_type = $encrypted['file_type'];
						//$entity->recordid = $patient_rubin->id;
						$entity->tabname = 'patient_rubin_'.$form_ident;
						$entity->system_generated = "0"; 
						$entity->save();
	
						$recordid = $entity->id;
						
						$comment =   $this->translate('patient_rubin_'.$form_ident.' PDF was created');
						$comment =   str_replace('%date', $form_date_dmY, $comment);
						$cust = new PatientCourse();
						$cust->ipid = $this->ipid;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt($comment);
						$cust->recordid = $recordid;
						$cust->tabname = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_saved');
						$cust->user_id = $userid;
						$cust->done_name = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_generated');;
						$cust->done_date = $form_date;
						$cust->save();
					}
						
					$this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
				}
					
	
			} else {
	
	
				$form->populate($post);
	
			}
	
		}
	
		//@todo : move messages in layout
		$this->view->SuccessMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('SuccessMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
				);
		$this->view->ErrorMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('ErrorMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
				);
	
		$this->_helper->flashMessenger->clearMessages('ErrorMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
		$this->_helper->flashMessenger->clearMessages('SuccessMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
		$this->view->form = $form;
		$this->view->enc_id = $this->enc_id;
	}
		
	public function demtectAction()
	{
		$ipid = $this->ipid;
		$clientid = $this->logininfo->clientid;
		$userid = $this->logininfo->userid;
		
		$form_ident = 'demtect';
		$this->view->form_ident = $form_ident;

		$form_name = strtoupper('RUBIN-'.$form_ident);
		$form_tabname = strtolower('RUBIN_'.$form_ident);
		
		$form = new Application_Form_PatientRubinForms(array(
				'_patientMasterData'    => $this->_patientMasterData,
				'_block_name'           => $form_name,
				'_page_name'           => $form_name,
		));
		  
		$patientmaster = new PatientMaster();
		// NOT OK  - CHANGE to TOD date
		$age = $patientmaster->GetAge(date("Y-m-d", strtotime($this->_patientMasterData['birthd'])),date('Y-m-d'),true );
		
		$this->view->patient_age = $age;
		
		if($_REQUEST['form_id'])
		{
			$form_id = $_REQUEST['form_id'];
		}
	    else
	    {
	        /*
    		 * 12.07.2019 - Ancuta
    		// Check if patient has a saved form
    	    $form_details = array();
    	    $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
    	    if(!empty($form_details)){
    	        $form_id = $form_details['id'];
    	    }
    	    */
	        // allow multiple forms per patient
	        $form_id = null;	
	    }
	    
	    $saved_values = array();
		$saved_values = $this->_rubin_forms_GatherDetails($form_id, $form_ident);
	
		
	
		$form->create_form($saved_values, $form_ident);
	
		$request = $this->getRequest();

		
		if ( ! $request->isPost()) {
	
			//TODO move to populate
			//$form->populate($options);
	
	
		} elseif ($request->isPost()) {
	
		    
			$post = $request->getPost();

			$form->populate($post);
			
			$post_form = $post[$form_ident];
			
				
	
			if ( $form->isValid($post)) // no validation is implemented
			{
				
				if($_POST[$form_ident]['formular']['button_action'] == "save")
				{
					//$post_form['userid'] = $this->logininfo->userid;
					/* ------------------- SAVE FORM ----------------------------------- */
				    
				    $patient_rubin  = $form->save_form_rubin($this->ipid, $post_form);
						
				    $form_total  = $post_form['form_content']['form_total'];
				    $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
				    $form_date_dmY   = date('d.m.Y',strtotime($form_date));
				    // calculate score 
				    
				    
				    $post_questions = $post_form['form_content']['demtect'];
				    
				    foreach($post_questions as $q_ident => $q_options){
				        if($q_ident == "q_4"){
				            
    				        foreach($q_options as $optd_ident=>$opt_values){
    				           foreach($opt_values as $opt_ident=>$opt_value){
    				            if($opt_value > 0 ){
        				            $total[$q_ident][] = $opt_value;         
        				            ${$q_ident} += $opt_value;         
    				            }
    				        }
    				        }
				        } else{
				            
    				        foreach($q_options as $opt_ident=>$opt_value){
    				            if($opt_value > 0 ){
        				            $total[$q_ident][] = $opt_value;
        				            ${$q_ident} += $opt_value;
    				            }
    				        }
				        }
                    }
                    
                    
                    $qt['Q1'] = $q_1+$q_1_a;
                    $qt['Q2'] = $q_2;
                    $qt['Q3'] = $q_3;
                    //$qt['Q4'] = $q_4;
                    $qt['Q4'] = max($total['q_4']);
                    $qt['Q5'] = $q_5;
                    
                    // Q1)
					if ($age < 60){
					    
                        if($qt['Q1'] <= 7){
                            $final['q1'] = 0;
    					}
                        elseif($qt['Q1'] >=8 && $qt['Q1'] <=10){
                            $final['q1'] = 1;
    					}
                        elseif($qt['Q1'] >=11 && $qt['Q1'] <=12){
                            $final['q1'] = 2;
    					}
                        elseif($qt['Q1'] >=13){
                            $final['q1'] = 3;
    					}
    					
                    }  else {
                        
                        if($qt['Q1'] <= 6){
                            $final['q1'] = 0;
    					}
                        elseif($qt['Q1'] >= 7 && $qt['Q1'] <= 8){
                            $final['q1'] = 1;
    					}
                        elseif($qt['Q1'] >= 9 && $qt['Q1'] <= 10){
                            $final['q1'] = 2;
    					}
                        elseif($qt['Q1'] >= 11){
                            $final['q1'] = 3;
    					}
                    } 
					
                    // Q2
                    if($qt['Q2'] == 0){
                        $final['q2'] = 0;
                    }
                    else if($qt['Q2'] == 1 ||$qt['Q2'] == 2)
                    {
                        $final['q2'] = 1;
                    }
                    else if($qt['Q2'] == 3 )
                    {
                        $final['q2'] = 2;
                    }
                    else if($qt['Q2'] == 4 )
                    {
                        $final['q2'] = 3;
                    }
                    
                    // Q3
                    if ($age < 60){
                        	
                        if( $qt['Q3'] >= 0 && $qt['Q3'] <= 12 ){
                            $final['q3'] = 0;
                        }
                        elseif($qt['Q3'] >=13 && $qt['Q3'] <=15){
                            $final['q3'] = 1;
                        }
                        elseif($qt['Q3'] >=16 && $qt['Q3'] <=19){
                            $final['q3'] = 2;
                        }
                        elseif($qt['Q3'] >=20){
                            $final['q3'] = 4;
                        }
                        
                    } else {
                    
                         if( $qt['Q3'] >= 0 && $qt['Q3'] <= 5 ){
                            $final['q3'] = 0;
                        }
                        elseif($qt['Q3'] >=6 && $qt['Q3'] <=9){
                            $final['q3'] = 1;
                        }
                        elseif($qt['Q3'] >=10 && $qt['Q3'] <= 15){
                            $final['q3'] = 2;
                        }
                        elseif($qt['Q3'] >=16){
                            $final['q3'] = 4;
                        }
                    }
                    
                    // Q4
                    if ($age < 60){
                        	
                        if( $qt['Q4']  == 0  ){
                            $final['q4'] = 0;
                        }
                        elseif($qt['Q4'] == 2 || $qt['Q4'] == 3){
                            $final['q4'] = 1;
                        }
                        elseif($qt['Q4'] == 4){
                            $final['q4'] = 2;
                        }
                        elseif($qt['Q4'] >= 5 ){
                            $final['q4'] = 3;
                        }
                        
                    } else {

                        if( $qt['Q4']  == 0  ){
                            $final['q4'] = 0;
                        }
                        elseif($qt['Q4'] == 2){
                            $final['q4'] = 1;
                        }
                        elseif($qt['Q4'] == 3){
                            $final['q4'] = 2;
                        }
                        elseif($qt['Q4'] >= 4 ){
                            $final['q4'] = 3;
                        }
                    }

                    
                    // Q5
                    if ($age < 60){
                        	
                        if( $qt['Q5']  == 0  ){
                            $final['q5'] = 0;
                        }
                        elseif($qt['Q5'] >= 1 && $qt['Q5'] <= 3){
                            $final['q5'] = 1;
                        }
                        elseif($qt['Q5'] >=4 && $qt['Q5'] <= 5){
                            $final['q5'] = 2;
                        }
                        elseif($qt['Q5'] >= 6 ){
                            $final['q5'] = 5;
                        }
                        
                    } else {

                        if( $qt['Q5']  == 0  ){
                            $final['q5'] = 0;
                        }
                        elseif($qt['Q5'] >=1 && $qt['Q5'] <= 2){
                            $final['q5'] = 1;
                        }
                        elseif($qt['Q5'] >=3 && $qt['Q5'] <= 4){
                            $final['q5'] = 2;
                        }
                        elseif($qt['Q5'] >= 5 ){
                            $final['q5'] = 5;
                        }
                    }
                    
                    	
                    $form_total = $final['q1'] + $final['q2'] +$final['q3']+$final['q4'] + $final['q5']; 
                    
                     
                    
// 				    dd($total_final,$q_1,$total,$post_questions);
				    
// 				    $age
// 				    $q1_total 
				    
				    
				    $score_text = "";
				    
				    if ($form_total <= 8 )
				    {
    				    $score_text = "Demenzverdacht | Weitere diagnostische Abklärung, Therapie einleiten ";
				    } 
				    elseif ($form_total >=9 && $form_total <= 12)
				    {
    				    $score_text = "Leichte Kognitive Beeinträchtigung | Nach 6 Monaten erneut testen – Verlauf beobachten";
				    }
				    elseif ($form_total >= 13 && $form_total <= 18)
				    {
    				    $score_text = "Altersgemäße kognitive Leistung | Nach 12 Monaten bzw. bei Auftreten von Problemen erneut testen";
				    }
				    else 
				    {
    				    $score_text = "Keine Demenz";
				    }
				    
					if($patient_rubin->id)
					{
					    $course_values[$form_ident]['course_type'] = "K";
					    $course_values[$form_ident]['course_tabname'] = "patient_rubin_".$form_ident;
					    $course_values[$form_ident]['course_done_name'] = "patient_rubin_".$form_ident;
						
// 					    $coursetitle =   $this->translate('patient_rubin_'.$form_ident.'  was saved')."\n";
					    $coursetitle =   "DemTect \n";
					    $coursetitle .= "Datum der Befragung: ".$form_date_dmY." \n";
					    $coursetitle .=  $form_total." (".$score_text.")";
						
						$custcourse = new PatientCourse();
						$custcourse->ipid = $ipid;
						$custcourse->course_date = date("Y-m-d H:i:s", time());
						$custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
						$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
						$custcourse->user_id = $userid;
						$custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
						$custcourse->recordid = $patient_rubin->id;
						$custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
						$custcourse->done_id = $patient_rubin->id;
						$custcourse->done_date = $form_date ;
						$custcourse->save();
					}   
						
					//$form->removeDisplayGroup('form_actions');
					$form->removeDecorator('Form');
					$form->removeSubForm('tabs_navi');
					$form->removeSubForm('form_actions');
					$today_date = date('d.m.Y');
					$nice_name_epid = $this->_patientMasterData['nice_name_epid'];
						
						
					$html_form  = $form->__toString();
					$html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
					/*$html_print = sprintf($bsHead, APP_BASE)
					. $html_form
					. $bsFoot
					;*/
// 					echo $html_print;exit;
						
					$this->view->form_ident = $form_ident;
					$this->view->form_pdf = $html_form; //this is the body of the pdf
						
					$html_print = $this->view->render("templates/rubinforms_pdf.phtml");
// 					echo $html_print; exit;
					$options = new Options();
					$options->set('isRemoteEnabled', false);
					$dompdf = new Dompdf($options);
						
					$dompdf->loadHtml($html_print);
					// (Optional) Setup the paper size and orientation
					$dompdf->setPaper('A4', 'portrait');
						
					$dompdf->set_option("enable_php",true);
					$dompdf->set_option('defaultFont', 'times');
					$dompdf->set_option("fontHeightRatio",0.90);
						
					// Render the HTML as PDF
					$dompdf->render();
						
					$canvas = $dompdf->get_canvas();
					
					$footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
					$footer_font_size = 8;
					
					$footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
					$text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
					$footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
					
					
					$canvas->page_text(
					    ($canvas->get_width() - $text_width)/2,
					    $canvas->get_height()-30,
					    $footer_text,
					    $footer_font_family,
					    $footer_font_size,
					    array(0,0,0));
					
					
						
					
					$output = $dompdf->output();
						
					// Output the generated PDF to Browser
// 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
// 					                       $dompdf->stream();
// 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
					
					$patient_file_title = 'patient_rubin_'.$form_ident.'_file';
					
					$result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
						
					if ($result !== false) {
	
						$encrypted = Pms_CommonData::aesEncryptMultiple(array(
								'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
								'file_name' => $result,
								'file_type' => 'PDF',
						));
							
							
						$entity = new PatientFileUpload ();
						//bypass triggers, we will use our own
						$entity->triggerformid = null;
						$entity->triggerformname = null;
						$entity->title = $encrypted['title'];
						$entity->ipid = $this->ipid;
						$entity->file_name = $encrypted['file_name'];
						$entity->file_type = $encrypted['file_type'];
						//$entity->recordid = $patient_rubin->id;
						$entity->tabname = 'patient_rubin_'.$form_ident;
						$entity->system_generated = "0"; 
						$entity->save();
	
						$recordid = $entity->id;
	
						
						$comment =   $this->translate('patient_rubin_'.$form_ident.' PDF was created');
						$comment =   str_replace('%date', $form_date_dmY, $comment);
						$cust = new PatientCourse();
						$cust->ipid = $this->ipid;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt($comment);
						$cust->recordid = $recordid;
						$cust->tabname = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_saved');
						$cust->user_id = $userid;
						$cust->done_name = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_generated');;
						$cust->done_date = $form_date;						
						$cust->save();
							
							
							
					}
						
						
					// empty the post by using a redirect
					//$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ), array (
					// 						$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ) . "&selected_month=" . $query_date, array (
					//"exit" => true
// 					) );
					$this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);

// 					$this->_redirect(APP_BASE . "rubin/rubin".$form_ident."?id=" . $_REQUEST['id']);
				}
					
	
			} else {
	
	
				$form->populate($post);
	
			}
	
		}
	
		//@todo : move messages in layout
		$this->view->SuccessMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('SuccessMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
				);
		$this->view->ErrorMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('ErrorMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
				);
	
		$this->_helper->flashMessenger->clearMessages('ErrorMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
		$this->_helper->flashMessenger->clearMessages('SuccessMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
		$this->view->form = $form;
		$this->view->enc_id = $this->enc_id;
	}
		
		
	public function rubinformsAction()
	{
		$ipid = $this->ipid;
		$clientid = $this->logininfo->clientid;
		$userid = $this->logininfo->userid;
		
		$form_ident = 'mmst';
		$this->view->form_ident = $form_ident;

		$form_name = strtoupper('RUBIN-'.$form_ident);
		$form_tabname = strtolower('RUBIN_'.$form_ident);
		
		$form = new Application_Form_PatientRubinForms(array(
				'_patientMasterData'    => $this->_patientMasterData,
				'_block_name'           => $form_name,
		));
		  
		
		if($_REQUEST['msid'])
		{
			$form_id = $_REQUEST['msid'];
		}
	
		$saved_values = $this->_rubiniadl_GatherDetails($form_id);
	
		$form->create_form($saved_values, $form_ident);
	
		$request = $this->getRequest();

		
		if ( ! $request->isPost()) {
	
			//TODO move to populate
			//$form->populate($options);
	
	
		} elseif ($request->isPost()) {
	
		    
			$post = $request->getPost();
// 			print_r($post); exit;
			$form->populate($post);
			
			$post_form = $post[$form_ident];
			
// 			unset($post_form['formular']);
			
// 			foreach($post_form as $kpost=>$valpost)
// 			{
// 					if (strpos($kpost, 'total') !== false) {
// 						foreach($valpost as $ktotalhead=>$totalhead)
// 						{
// 							foreach($totalhead as $ktotalpost=>$totalpost)
// 							{
// 								$post_form[$ktotalpost] = $totalpost;
// 							}
// 						}
// 					}
						
					
// 			}
			
				
	
			if ( $form->isValid($post)) // no validation is implemented
			{
				
				if($_POST[$form_ident]['formular']['button_action'] == "save")
				{
					//$post_form['userid'] = $this->logininfo->userid;
					/* ------------------- SAVE FORM ----------------------------------- */
				    
					/*   $patient_rubin  = $form->save_form_rubin($this->ipid, $post_form);
						
					if($patient_rubin->id)
					{
						if($post_form['total'] >= 17 && $post_form['total'] <= 23.5)
						{
							$coursetitle = PatientRubin::PATIENT_COURSE_TITLE."\nAuswertung des Gesamt-Index : " . $post_form['total'] . " => Risikobereich für Unterernährung";
						}
						else
						{
							$coursetitle = PatientRubin::PATIENT_COURSE_TITLE."\nAuswertung des Gesamt-Index : " . $post_form['total'] . " => schlechter Ernährungszustand";
						}
							
						$custcourse = new PatientCourse();
						$custcourse->ipid = $ipid;
						$custcourse->course_date = date("Y-m-d H:i:s", time());
						$custcourse->course_type = Pms_CommonData::aesEncrypt(PatientRubin::PATIENT_COURSE_TYPE);
						$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
						$custcourse->user_id = $userid;
						$custcourse->tabname = Pms_CommonData::aesEncrypt(PatientRubin::PATIENT_COURSE_TABNAME);
						$custcourse->recordid = $patient_rubin->id;
						$custcourse->done_name = PatientRubin::PATIENT_COURSE_TABNAME;
						$custcourse->done_id = $patient_rubin->id;
						$custcourse->save();
							
					}   */
						
					//$form->removeDisplayGroup('form_actions');
					$form->removeDecorator('Form');
					$form->removeSubForm('tabs_navi');
					$form->removeSubForm('form_actions');
					if($post_form['before_anamnesis_total'] >=12)
					{
						$form->removeSubForm('anamnesis');
						$form->removeSubForm('anamnesis_total');
					}
					$today_date = date('d.m.Y');
					$nice_name_epid = $this->_patientMasterData['nice_name_epid'];
						
						
					$html_form  = $form->__toString();
					$html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
					/*$html_print = sprintf($bsHead, APP_BASE)
					. $html_form
					. $bsFoot
					;*/
// 					echo $html_print;exit;
						
					$this->view->form_ident = $form_ident;
					$this->view->form_pdf = $html_form; //this is the body of the pdf
						
					$html_print = $this->view->render("templates/rubin_pdf.phtml");
// 					echo $html_print; exit;
					$options = new Options();
					$options->set('isRemoteEnabled', false);
					$dompdf = new Dompdf($options);
						
					$dompdf->loadHtml($html_print);
					// (Optional) Setup the paper size and orientation
					$dompdf->setPaper('A4', 'portrait');
						
					$dompdf->set_option("enable_php",true);
					$dompdf->set_option('defaultFont', 'times');
					$dompdf->set_option("fontHeightRatio",0.90);
						
					// Render the HTML as PDF
					$dompdf->render();
						
					$canvas = $dompdf->get_canvas();
					
					$footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
					$footer_font_size = 8;
					
					$footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
					$text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
					$footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
					
					
					$canvas->page_text(
					    ($canvas->get_width() - $text_width)/2,
					    $canvas->get_height()-30,
					    $footer_text,
					    $footer_font_family,
					    $footer_font_size,
					    array(0,0,0));
					
					
						
					
					$output = $dompdf->output();
						
					// Output the generated PDF to Browser
// 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
// 					                       $dompdf->stream();
// 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
					
					$patient_file_title = 'patient_rubin_'.$form_ident.'_file';
					
					$result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
						
					if ($result !== false) {
	
						$encrypted = Pms_CommonData::aesEncryptMultiple(array(
								'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
								'file_name' => $result,
								'file_type' => 'PDF',
						));
							
							
						$entity = new PatientFileUpload ();
						//bypass triggers, we will use our own
						$entity->triggerformid = null;
						$entity->triggerformname = null;
						$entity->title = $encrypted['title'];
						$entity->ipid = $this->ipid;
						$entity->file_name = $encrypted['file_name'];
						$entity->file_type = $encrypted['file_type'];
						//$entity->recordid = $patient_rubin->id;
						$entity->tabname = 'patient_rubin_'.$form_ident;
						$entity->system_generated = "0"; 
						$entity->save();
	
						$recordid = $entity->id;
	
						
						$comment =   $this->translate('patient_rubin_'.$form_ident.' PDF was created');
						$comment =   str_replace('%date', $form_date_dmY, $comment);
						$cust = new PatientCourse();
						$cust->ipid = $this->ipid;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt($comment);
						$cust->recordid = $recordid;
						$cust->tabname = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_saved');
						$cust->user_id = $userid;
						$cust->save();
							
							
							
					}
						
						
					// empty the post by using a redirect
					//$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ), array (
					// 						$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ) . "&selected_month=" . $query_date, array (
					//"exit" => true
// 					) );
					$this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);

// 					$this->_redirect(APP_BASE . "rubin/rubin".$form_ident."?id=" . $_REQUEST['id']);
				}
					
	
			} else {
	
	
				$form->populate($post);
	
			}
	
		}
	
		//@todo : move messages in layout
		$this->view->SuccessMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('SuccessMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
				);
		$this->view->ErrorMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('ErrorMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
				);
	
		$this->_helper->flashMessenger->clearMessages('ErrorMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
		$this->_helper->flashMessenger->clearMessages('SuccessMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
		$this->view->form = $form;
		$this->view->enc_id = $this->enc_id;
	}

	
	private function _rubin_forms_GatherDetails( $form_id = null, $form_type = null )
	{
	    //one formular / patient
	    $ipid = $this->ipid;
	    $entity  = new PatientRubinForms();
	    $saved_formular_final = array();
	
	    
	    if($form_id)
	    {
	        $form_details = array();
	        $form_details = PatientRubinFormsTable::find_patient_form_By_Form_Id($ipid,$form_type,$form_id);
	        
	        if(!empty($form_details)){
	            
        	    $saved_formular_final['formular']['form_type'] = $form_type;
        	    $saved_formular_final['formular']['form_total'] = $form_details['form_total'];
        	    $saved_formular_final['formular']['form_date'] = !empty($form_details['form_date']) ? date("d.m.Y",strtotime($form_details['form_date'])) : date("d.m.Y",strtotime($form_details['create_date'])) ;
	            
	            
	            foreach($form_details as $fk=>$fd){
	                if(is_array($fd)){
	                    
    	                foreach($fd as $qk=>$qo){
                            $saved_formular_final['formular'][$qo['question_id'] ][$qo['question_option']]['checked'] = $qo['option_checked'];
                            $saved_formular_final['formular'][$qo['question_id'] ][$qo['question_option']]['value'] = $qo['option_value'];
                            $saved_formular_final['formular'][$qo['question_id'] ][$qo['question_option']]['extra_value'] = $qo['extra_value'];
    	                }
	                }
	            }
                $saved_formular_final['form_id'] = $form_id;
	        }
	        
	    }
	    else
	    {
 
	    }
	
	        return $saved_formular_final;
	    }
	private function _rubiniadl_GatherDetails( $form_id = null)
	{
	    //var_dump($this->ipid); exit;
	    //one formular / patient
	    $ipid = $this->ipid;
	    $entity  = new PatientRubin();
	    $saved_formular_final = array();
	
	    
	    
	    if($form_id)
	    {
	        $saved_formular = $entity->getTable()->findOneBy('id', $form_id, Doctrine_Core::HYDRATE_RECORD);
	        //print_r($saved_formular);
	        foreach($saved_formular as $kcol=>$vcol)
	        {
	
	            $saved_formular_final[$kcol]['colprop'] = $entity->getTable()->getColumnDefinition($kcol);
	            $saved_formular_final[$kcol]['value'] = $vcol;
	        }
	        $saved_formular_final['form_id'] = $form_id;
	    }
	    else
	    {
	        // get questions for rubin form - 

	        
	        
	        
	        
	        //$saved_formular= $entity->findOrCreateOneByIdAndIpid($form_id , $this->ipid);
	
	        //if(!$saved_formular)
	        //{
	        $saved_formular= $entity->getTable()->getFieldNames();
	
	        foreach($saved_formular as $kcol=>$vcol)
	        {
	            $saved_formular_final[$vcol]['colprop'] = $entity->getTable()->getColumnDefinition($vcol);
	            if($vcol == "ipid"){
    	            $saved_formular_final[$vcol]['value'] = $ipid;
	            } else{
	               $saved_formular_final[$vcol]['value'] = null;
	            }
	        }
	        }
	
	
	        //print_r($saved_formular_final); exit;
	
	        return $saved_formular_final;
	    }
     
	public function mnaAction()
	{
		$ipid = $this->ipid;
		$clientid = $this->logininfo->clientid;
		$userid = $this->logininfo->userid;
		
		$form_ident = "mna";
		$this->view->form_ident = $form_ident;

		$form_name = strtoupper('RUBIN-'.$form_ident);
		$form_tabname = strtolower('RUBIN_'.$form_ident);
		
		
		
		$form = new Application_Form_PatientRubin(array(
				'_patientMasterData'    => $this->_patientMasterData,
				'_block_name'           => $form_name,
				'_page_name'           => $form_name
		));
		
		if($_REQUEST['msid'])
		{
			$form_id = $_REQUEST['msid'];
		}
	
		//last saved values
		$saved_values = $this->_rubin_GatherDetails($form_id);
		//print_r($saved_values); exit;
	
		$form->create_form($saved_values, 'mna');
	
		$request = $this->getRequest();
	
		//$saved_values = array();
	
		if ( ! $request->isPost()) {
	
			//TODO move to populate
			//$form->populate($options);
	
	
		} elseif ($request->isPost()) {
	
			$post = $request->getPost();
			//print_r($post); exit;
			$form->populate($post);
			$post_form = $post['mna'];
			unset($post_form['formular']);
			
			foreach($post_form as $kpost=>$valpost)
			{
				if (strpos($kpost, 'total') !== false) {
					foreach($valpost as $ktotalhead=>$totalhead)
					{
						foreach($totalhead as $ktotalpost=>$totalpost)
						{
							$post_form[$ktotalpost] = $totalpost;
						}
					}
				}
						
			}
			
				
	
			if ( $form->isValid($post)) // no validation is implemented
			{
				
				
				if($_POST['mna']['formular']['button_action'] == "save")
				{
					//$post_form['userid'] = $this->logininfo->userid;
					$patient_rubin  = $form->save_form_rubin($this->ipid, $post_form);
						
					if($patient_rubin->id)
					{
						if($post_form['total'] >= 17 && $post_form['total'] <= 23.5)
						{
							$coursetitle = PatientRubin::PATIENT_COURSE_TITLE."\nAuswertung des Gesamt-Index : " . $post_form['total'] . " => Risikobereich für Unterernährung";
						}
						else
						{
							$coursetitle = PatientRubin::PATIENT_COURSE_TITLE."\nAuswertung des Gesamt-Index : " . $post_form['total'] . " => schlechter Ernährungszustand";
						}
							
						$custcourse = new PatientCourse();
						$custcourse->ipid = $ipid;
						$custcourse->course_date = date("Y-m-d H:i:s", time());
						$custcourse->course_type = Pms_CommonData::aesEncrypt(PatientRubin::PATIENT_COURSE_TYPE);
						$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
						$custcourse->user_id = $userid;
						$custcourse->tabname = Pms_CommonData::aesEncrypt(PatientRubin::PATIENT_COURSE_TABNAME);
						$custcourse->recordid = $patient_rubin->id;
						$custcourse->done_name = PatientRubin::PATIENT_COURSE_TABNAME;
						$custcourse->done_id = $patient_rubin->id;
						$custcourse->save();
							
					}
						
					//$form->removeDisplayGroup('form_actions');
					$form->removeDecorator('Form');
					$form->removeSubForm('tabs_navi');
					$form->removeSubForm('form_actions');
					if($post_form['before_anamnesis_total'] >=12)
					{
						$form->removeSubForm('anamnesis');
						$form->removeSubForm('anamnesis_total');
					}
					$today_date = date('d.m.Y');
					$nice_name_epid = $this->_patientMasterData['nice_name_epid'];
						
						
					/*$bsHead = <<<EOT
<html>
    <head>
        <link href="%s/css/page-css/besdsurvey.css" rel="stylesheet" type="text/css" />
        <style>
            @page { margin: 20px 20px 60px 60px; }
        </style>
    </head>
    <body >
			
			
EOT;
						
					$bsFoot = <<<EOT
			
    </body>
</html>
EOT;*/
					$html_form  = $form->__toString();
						
						
					$html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
						
					/*$html_print = sprintf($bsHead, APP_BASE)
					. $html_form
					. $bsFoot
					;*/
					//echo $html_print;exit;
						
					$this->view->form_ident = $form_ident;
					$this->view->form_pdf = $html_form; //this is the body of the pdf
						
					$html_print = $this->view->render("templates/rubin_pdf.phtml");
					//echo $html_print; exit;
					$options = new Options();
					$options->set('isRemoteEnabled', false);
					$dompdf = new Dompdf($options);
						
					$dompdf->loadHtml($html_print);
					// (Optional) Setup the paper size and orientation
					$dompdf->setPaper('A4', 'portrait');
						
					$dompdf->set_option("enable_php",true);
					$dompdf->set_option('defaultFont', 'times');
					$dompdf->set_option("fontHeightRatio",0.90);
						
					// Render the HTML as PDF
					$dompdf->render();
						
					$canvas = $dompdf->get_canvas();
						
					$footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
					$footer_font_size = 8;
						
					$footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
					$text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
					$footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
						
						
					$canvas->page_text(
					    ($canvas->get_width() - $text_width)/2,
					    $canvas->get_height()-30,
					    $footer_text,
					    $footer_font_family,
					    $footer_font_size,
					    array(0,0,0));
					
					$output = $dompdf->output();
						
					// Output the generated PDF to Browser
					//$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
					//                        $dompdf->stream();
						
					$patient_file_title = 'patient_rubin_'.$form_ident.'_file';
					
					$result = $this->dompdf_ToFTP($output, $this->translate(PatientRubin::PATIENT_FILE_TITLE));
						
					if ($result !== false) {
	
						$encrypted = Pms_CommonData::aesEncryptMultiple(array(
								'title' => $this->translate(PatientRubin::PATIENT_FILE_TITLE),
								'file_name' => $result,
								'file_type' => 'PDF',
						));
							
							
						$entity = new PatientFileUpload ();
						//bypass triggers, we will use our own
						$entity->triggerformid = null;
						$entity->triggerformname = null;
						$entity->title = $encrypted['title'];
						$entity->ipid = $this->ipid;
						$entity->file_name = $encrypted['file_name'];
						$entity->file_type = $encrypted['file_type'];
						$entity->recordid = $patient_rubin->id;
						$entity->tabname = PatientRubin::PATIENT_FILE_TABNAME;
						$entity->system_generated = "0"; //TODO this should be 0?
						$entity->save();
						$recordid = $entity->id;
	
						
						$comment = PatientRubin::PATIENT_COURSE_TITLE_PDF;
						$cust = new PatientCourse();
						$cust->ipid = $this->ipid;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt($comment);
						$cust->recordid = $recordid;
						$cust->tabname = Pms_CommonData::aesEncrypt(PatientRubin::PATIENT_COURSE_TABNAME_SAVE);
						$cust->user_id = $userid;
						$cust->save();
							
							
							
					}
						
						
					// empty the post by using a redirect
					//$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ), array (
					// 						$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ) . "&selected_month=" . $query_date, array (
					//"exit" => true
					//) );
					$this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
				}
					
	
			} else {
	
	
				$form->populate($post);
	
			}
	
		}
	
		//@todo : move messages in layout
		$this->view->SuccessMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('SuccessMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
				);
		$this->view->ErrorMessages = array_merge(
				$this->_helper->flashMessenger->getMessages('ErrorMessages'),
				$this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
				);
	
		$this->_helper->flashMessenger->clearMessages('ErrorMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
		$this->_helper->flashMessenger->clearMessages('SuccessMessages');
		$this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
		$this->view->form = $form;
		$this->view->enc_id = $this->enc_id;
	
			
	
	}
	
	private function _rubin_GatherDetails( $form_id = null)
	{
		//var_dump($this->ipid); exit;
		//one formular / patient
		$entity  = new PatientRubin();
		$saved_formular_final = array();
	
		if($form_id)
		{
			$saved_formular = $entity->getTable()->findOneBy('id', $form_id, Doctrine_Core::HYDRATE_RECORD);
			//print_r($saved_formular);
			foreach($saved_formular as $kcol=>$vcol)
			{
	
				$saved_formular_final[$kcol]['colprop'] = $entity->getTable()->getColumnDefinition($kcol);
				$saved_formular_final[$kcol]['value'] = $vcol;
			}
			$saved_formular_final['form_id'] = $form_id;
		}
		else
		{
			//$saved_formular= $entity->findOrCreateOneByIdAndIpid($form_id , $this->ipid);
	
			//if(!$saved_formular)
			//{
			$saved_formular= $entity->getTable()->getFieldNames();
				
			foreach($saved_formular as $kcol=>$vcol)
			{
				$saved_formular_final[$vcol]['colprop'] = $entity->getTable()->getColumnDefinition($vcol);
				$saved_formular_final[$vcol]['value'] = null;
			}
		}
	
		//print_r($saved_formular_final); exit;
	
		return $saved_formular_final;
	}
	
	
	public function rubinqAction(){
	    
	    $request = $this->getRequest();
	    if ($request->isPost()) {
	    
	        $post = $request->getPost();
	        
	        $new_q = new PatientRubinQuestions();
	        $new_q->form_id = $post['form_id'];
	        $new_q->question_id = $post['question_id'];
	        $new_q->question_text = $post['question_text'];
	        $new_q->question_type = $post['question_type'];
	        $new_q->save();
	        
	        $qid = $new_q->id;
	        if($qid){
	            foreach($post['option'] as $k=> $opt){

	                
	                if(!empty($opt['name']) && !empty($opt['name']))
	                {
            	        $new_qo = new PatientRubinQuestionsOptions();
            	        $new_qo->form_id = $post['form_id'];
            	        $new_qo->question_id = $post['question_id'];
            	        $new_qo->option_label = $opt['name'];
            	        $new_qo->option_value = $opt['value'];
            	        $new_qo->save();
	                }
	            }
	            
	        }
	        
// 	        $this->_redirect(APP_BASE . "rubin/rubinq?msg=qAdded");
	        
	    } 
	    
	}
	
	


	public function gdsAction()
	{
	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	
	    $form_ident = 'gds';
	    $this->view->form_ident = $form_ident;
	
	    $form_name = strtoupper('DemStepCare-'.$form_ident);
	    $form_tabname = strtolower('DemStepCare_'.$form_ident);
	
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	
	
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
		    {
	        /*
    		 * 12.07.2019 - Ancuta
    		// Check if patient has a saved form
    	    $form_details = array();
    	    $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
    	    if(!empty($form_details)){
    	        $form_id = $form_details['id'];
    	    }
    	    */
	        
	        // allow multiple forms per patient
	        $form_id = null;
	    }
	    $saved_values = array();
	    $saved_values = $this->_dsp_forms_GatherDetails($form_id, $form_ident);
	
	
	    $form->create_form($saved_values, $form_ident);
	
	    $request = $this->getRequest();
	
	
	    if ( ! $request->isPost()) {
	
	        //TODO move to populate
	        //$form->populate($options);
	
	
	    } elseif ($request->isPost()) {
	
	
	        $post = $request->getPost();
	
	        $form->populate($post);
	        	
	        $post_form = $post[$form_ident];
	        	
	
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */

	                /*
                    0-5 unauffällig
                    6-10 Verdacht auf leicht bis mäßige depressive Symptomatik
                    11-15 Verdacht auf schwere depressive Symptomatik
	                 */
	                $patient_rubin  = $form->save_form_DemStepCare($this->ipid, $post_form);
	
	                $form_total  = $post_form['form_content']['form_total'];
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));
	                
	                $score_text = "";
	
	                if ($form_total <= 5 )
	                {
	                    $score_text = "unauffällig";
	                }
	                elseif ($form_total >=6 && $form_total <= 10)
	                {
	                    $score_text = "Verdacht auf leicht bis mäßige depressive Symptomatik";
	                }
	                elseif ($form_total >=11 && $form_total <= 15)
	                {
	                    $score_text = "Verdacht auf schwere depressive Symptomatik";
	                }
	
	                if($patient_rubin->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_demstepcare_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_demstepcare_".$form_ident;
	
	                    $coursetitle =   "GDS-15 (DemStepCare)\n";
	                    $coursetitle .= "Datum der Befragung: ".$form_date_dmY;
	                    $coursetitle .= "\n Interpretation des Testergebnisses: ".$form_total." (".$score_text.")";
	                    
	                    
	
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_rubin->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->done_id = $patient_rubin->id;
	                    $custcourse->save();
	                }
	                	
	
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	
	
	                $html_form  = $form->__toString();
	                // 					dd($html_form);
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
	                /*$html_print = sprintf($bsHead, APP_BASE)
	                 . $html_form
	                 . $bsFoot
	                ;*/
	                // 					echo $html_print;exit;
	
	                $this->view->form_ident = $form_ident;
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
	                // 					echo $html_print; exit;
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	
	                // Render the HTML as PDF
	                $dompdf->render();
	
	                $canvas = $dompdf->get_canvas();
	                	
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	                	
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	                $footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	                	
	                	
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-30,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	                	
	                	
	
	                	
	                $output = $dompdf->output();
	
	                // Output the generated PDF to Browser
	                // 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
	                // 					                       $dompdf->stream();
	                // 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
	                	
	                $patient_file_title = 'patient_demstepcare_'.$form_ident.'_file';
	                	
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	
	                if ($result !== false) {
	
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	                    	
	                    	
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_rubin->id;
	                    $entity->tabname = 'patient_demstepcare_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	
	                    $recordid = $entity->id;
	
	                    $pdf_date = date('d.m.Y');
	                    $comment =   $this->translate('patient_demstepcare_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_demstepcare_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	                    	
	                }
	
	                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	            }
	            	
	
	        } else {
	
	
	            $form->populate($post);
	
	        }
	
	    }
	
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	    );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	    );
	
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	}
	
	/**
	 * @Ancuta
	 * 11.07.2019
	 * ISPC-2404
	 */
	public function npiAction()
	{
	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	
	    $form_ident = 'npi';
	    $this->view->form_ident = $form_ident;
	
	    $form_name = strtoupper('DemStepCare-'.$form_ident);
	    $form_tabname = strtolower('DemStepCare_'.$form_ident);
	
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	
	
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
		    {
	        /*
    		 * 12.07.2019 - Ancuta
    		// Check if patient has a saved form
    	    $form_details = array();
    	    $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
    	    if(!empty($form_details)){
    	        $form_id = $form_details['id'];
    	    }
    	    */
	        
	        // allow multiple forms per patient
	        $form_id = null;
	    }
	     
	    $saved_values = array();
	    $saved_values = $this->_dsp_forms_GatherDetails($form_id, $form_ident);
	
	
	    $form->create_form($saved_values, $form_ident);
	
	    $request = $this->getRequest();
	
	
	    if ( ! $request->isPost()) {
	
	        //TODO move to populate
	        //$form->populate($options);
	
	
	    } elseif ($request->isPost()) {
	
	
	        $post = $request->getPost();
	
	        $form->populate($post);
	        	
	        $post_form = $post[$form_ident];
	        	
	
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	            
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */
	
	                $patient_DemStepCare  = $form->save_form_DemStepCare($this->ipid, $post_form);
	
	                $form_total  = $post_form['form_content']['form_total'];
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));
	
	                if($patient_DemStepCare->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_demstepcare_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_demstepcare_".$form_ident;
	
	                    $coursetitle =   "DemStepCare - NPI \n";
	                    $coursetitle .= "Datum der Befragung: ".$form_date_dmY." \n";
	                    $coursetitle .= "Interpretation des Testergebnisses: ".$form_total;
	
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_DemStepCare->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->done_id = $patient_DemStepCare->id;
	                    $custcourse->save();
	                }
	                	
	
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	
	
	                $html_form  = $form->__toString();
	                // 					dd($html_form);
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
	                /*$html_print = sprintf($bsHead, APP_BASE)
	                 . $html_form
	                 . $bsFoot
	                ;*/
	                // 					echo $html_print;exit;
	
	                $this->view->form_ident = $form_ident;
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
// 	               					echo $html_print; exit;
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	
	                // Render the HTML as PDF
	                $dompdf->render();
	
	                $canvas = $dompdf->get_canvas();
	                	
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	                	
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	                $footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	                	
	                	
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-30,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	                	
	                	
	
	                	
	                $output = $dompdf->output();
	
	                // Output the generated PDF to Browser
	                // 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
	                // 					                       $dompdf->stream();
	                // 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
	                	
	                $patient_file_title = 'patient_demstepcare_'.$form_ident.'_file';
	                	
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	
	                if ($result !== false) {
	
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	                    	
	                    	
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_DemStepCare->id;
	                    $entity->tabname = 'patient_demstepcare_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	
	                    $recordid = $entity->id;
	
	                    $pdf_date = date('d.m.Y');
	                    $comment =   $this->translate('patient_demstepcare_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_demstepcare_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	                    	
	                }
	
	                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	            }
	            	
	
	        } else {
	
	
	            $form->populate($post);
	
	        }
	
	    }
	
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	    );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	    );
	
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	}
	
	
	/**
	 * @auth Ancuta
	 * 11.07.2019
	 * ISPC-2402
	 */
	public function bdiAction()
	{
	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	
	    $form_ident = 'bdi';
	    $this->view->form_ident = $form_ident;
	
	    $form_name = strtoupper('DemStepCare-'.$form_ident);
	    $form_tabname = strtolower('DemStepCare_'.$form_ident);
	
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	
	
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
	    {
	        /*
    		 * 12.07.2019 - Ancuta
    		// Check if patient has a saved form
    	    $form_details = array();
    	    $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
    	    if(!empty($form_details)){
    	        $form_id = $form_details['id'];
    	    }
    	    */
	        
	        // allow multiple forms per patient
	        $form_id = null;
	    }
	     
	    $saved_values = array();
	    $saved_values = $this->_dsp_forms_GatherDetails($form_id, $form_ident);
	
	
	    $form->create_form($saved_values, $form_ident);
	
	    $request = $this->getRequest();
	
	
	    if ( ! $request->isPost()) {
	
	        //TODO move to populate
	        //$form->populate($options);
	
	
	    } elseif ($request->isPost()) {
	
	
	        $post = $request->getPost();
	
	        $form->populate($post);
	        	
	        $post_form = $post[$form_ident];
	        	
	
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */
	
	                /*
                    0-8   Keine Depression
                    9-13  Minimale depressive Symptomatik
                    14-19 Leichte depressive Symptomatik
                    20-28 Mittelschwere depressive Symptomatik
                    29-63 Schwere depressive Symptomatik
	                */
	                
	                
	                $patient_rubin  = $form->save_form_DemStepCare($this->ipid, $post_form);
	
	                $form_total  = $post_form['form_content']['form_total'];
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));
	                
	                $score_text = "";
	
	                if ($form_total <= 8 )
	                {
	                    $score_text = "Keine Depression";
	                }
	                elseif ($form_total >=9 && $form_total <= 13)
	                {
	                    $score_text = "Minimale depressive Symptomatik";
	                }
	                elseif ($form_total >= 14 && $form_total <= 19)
	                {
	                    $score_text = "Leichte depressive Symptomatik";
	                }
	                elseif ($form_total >=20 && $form_total <= 28)
	                {
	                    $score_text = "Mittelschwere depressive Symptomatik";
	                }
	                elseif ($form_total >=29 && $form_total <= 63)
	                {
	                    $score_text = "Schwere depressive Symptomatik";
	                }
	
	                if($patient_rubin->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_demstepcare_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_demstepcare_".$form_ident;
	                    
	                    $coursetitle =   "BDI-II (DemStepCare)\n";
	                    $coursetitle .= "Datum der Befragung: ".$form_date_dmY;
	                    $coursetitle .= "\n Interpretation des Testergebnisses: ".$form_total." (".$score_text.")";
	                    
	                    
	                    
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_rubin->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->done_id = $patient_rubin->id;
	                    $custcourse->save();
	                }
	                	
	
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	
	
	                $html_form  = $form->__toString();
	                // 					dd($html_form);
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
	                /*$html_print = sprintf($bsHead, APP_BASE)
	                 . $html_form
	                 . $bsFoot
	                ;*/
	                // 					echo $html_print;exit;
	
	                $this->view->form_ident = $form_ident;
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
	                // 					echo $html_print; exit;
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	
	                // Render the HTML as PDF
	                $dompdf->render();
	
	                $canvas = $dompdf->get_canvas();
	                	
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	                	
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	                $footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	                	
	                	
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-30,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	                	
	                	
	
	                	
	                $output = $dompdf->output();
	
	                // Output the generated PDF to Browser
	                // 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
	                // 					                       $dompdf->stream();
	                // 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
	                	
	                $patient_file_title = 'patient_demstepcare_'.$form_ident.'_file';
	                	
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	
	                if ($result !== false) {
	
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	                    	
	                    	
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_rubin->id;
	                    $entity->tabname = 'patient_demstepcare_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	
	                    $recordid = $entity->id;
	
	                    $pdf_date = date('d.m.Y');
	                    $comment =   $this->translate('patient_demstepcare_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_demstepcare_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	                    	
	                }
	
	                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	            }
	            	
	
	        } else {
	
	
	            $form->populate($post);
	
	        }
	
	    }
	
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	    );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	    );
	
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	}
	
	

	private function _dsp_forms_GatherDetails( $form_id = null, $form_type = null )
	{
	    //one formular / patient
	    $ipid = $this->ipid;
	    $entity  = new PatientRubinForms();
	    $saved_formular_final = array();
	
	     
	    if($form_id)
	    {
	        $form_details = array();
	        $form_details = PatientRubinFormsTable::find_patient_form_By_Form_Id($ipid,$form_type,$form_id);
	         
	        if(!empty($form_details)){
	             
	            $saved_formular_final['formular']['form_type'] = $form_type;
	            $saved_formular_final['formular']['form_total'] = $form_details['form_total'];
	            
	            if($form_type=='nosger'){
    	            $saved_formular_final['formular']['score_memory'] = $form_details['score_memory'];
    	            $saved_formular_final['formular']['score_iadl'] = $form_details['score_iadl'];
    	            $saved_formular_final['formular']['score_adl'] = $form_details['score_adl'];
    	            $saved_formular_final['formular']['score_mood'] = $form_details['score_mood'];
    	            $saved_formular_final['formular']['score_social'] = $form_details['score_social'];
    	            $saved_formular_final['formular']['score_disturbing'] = $form_details['score_disturbing'];
	            }
	            
	            $saved_formular_final['formular']['form_date'] = !empty($form_details['form_date']) ? date("d.m.Y",strtotime($form_details['form_date'])) : date("d.m.Y",strtotime($form_details['create_date'])) ;
	             
	             
	            foreach($form_details as $fk=>$fd){
	                if(is_array($fd)){
	                     
	                    foreach($fd as $qk=>$qo){
	                        $saved_formular_final['formular'][$qo['question_id'] ][$qo['question_option']]['checked'] = $qo['option_checked'];
	                        $saved_formular_final['formular'][$qo['question_id'] ][$qo['question_option']]['value'] = $qo['option_value'];
	                        $saved_formular_final['formular'][$qo['question_id'] ][$qo['question_option']]['extra_value'] = $qo['extra_value'];
	                    }
	                }
	            }
	            $saved_formular_final['form_id'] = $form_id;
	        }
	         
	    }
	    else
	    {
	        if($form_type=='dsv'){
	            $p_data = $this->_patientMasterData;

	            $sex = $p_data['sex'];
	            if($sex == "1"){
    	            $saved_formular_final['formular']['q_1']['opt_1']['value'] = "1";
       	            $saved_formular_final['formular']['q_1']['opt_1']['checked'] = "yes";
       	            	         
    	            $saved_formular_final['formular']['q_8']['opt_1']['value'] = "1";
       	            $saved_formular_final['formular']['q_8']['opt_1']['checked'] = "yes";
       	            	         
       	                   
	            } 
	            else if($sex == "2"){
    	            $saved_formular_final['formular']['q_1']['opt_1']['value'] = "2";	                
       	            $saved_formular_final['formular']['q_1']['opt_1']['checked'] = "yes";
       	            
    	            $saved_formular_final['formular']['q_8']['opt_1']['value'] = "2";	                
       	            $saved_formular_final['formular']['q_8']['opt_1']['checked'] = "yes";
	            }
	            
	            // Birthdate 
	            $birthd = $p_data['birthd'];
	            $saved_formular_final['formular']['q_9']['opt_1']['value'] = $birthd;
	            
	            /* STAGE*/
	            $pms = new PatientMaintainanceStage();
	            $pat_pmsinfo = $pms->getLastpatientMaintainanceStage($ipid);
	             
	            $stage = "";
	            if($pat_pmsinfo)
	            {
	                $stage= $pat_pmsinfo[0]['stage'];
	            }
	            
	            if(!empty($stage)){
	               $saved_formular_final['formular']['q_12']['opt_1']['value'] = $stage;
    	           $saved_formular_final['formular']['q_12']['opt_1']['checked'] = "yes";
	            }
	            
	            
	        }
	    }
	
	    return $saved_formular_final;
	}
	
	
	
	/**
	 * @author Ancuta 
	 * 04.09.2019
	 * @param string $form_id
	 * @return Ambigous <multitype:string , string>
	 */
	private function _demstepcare_GatherDetails( $form_id = null)
	{
	    //one formular / patient
	    $ipid = $this->ipid;
	    $entity  = new PatientDemstepcare();
	    $saved_formular_final = array();
	
	     
	    if($form_id)
	    {
	        $form_details = array();
	        $form_details = PatientDemstepcareTable::find_patient_form_By_Form_Id($ipid,$form_id);
	         
	        if(!empty($form_details)){

	            //get the files  
	            $files = PatientFileUpload::get_demstepcare_files( array($ipid), $form_id );

	            $saved_formular_final['formular']['form_date'] = !empty($form_details['form_date']) ? date("d.m.Y",strtotime($form_details['form_date'])) : date("d.m.Y",strtotime($form_details['create_date'])) ;

	            $saved_formular_final['formular']['dementia_diagnosis'] = !empty($form_details['dementia_diagnosis']) ?  $form_details['dementia_diagnosis'] : '' ;
	            $saved_formular_final['formular']['cerebral_imaging'] = !empty($form_details['cerebral_imaging']) ?  $form_details['cerebral_imaging'] : '' ;
	            $saved_formular_final['formular']['laboratory'] = !empty($form_details['laboratory']) ?  $form_details['laboratory'] : '' ;
	             
	            $saved_formular_final['formular']['files'] = !empty($files[$ipid]) ?  $files[$ipid] : '' ;
 
	            $saved_formular_final['form_id'] = $form_id;
	            
	        }
	         
	    }
	    else
	    {
	
	    }
	
	    return $saved_formular_final;
	}
	
	
	
	
	public function formlistAction(){

	    $forms_array = array(         
	          0 => 'mna',
              1 => 'iadl',
              2 => 'mmst',
              3 => 'tug',
              4 => 'demtect',
              5 => 'whoqol',
              6 => 'npi',
              7 => 'gds',
              8 => 'bdi',
	          9 => 'carerelated',      //ISPC-2492 Lore 02.12.2019
	         10 => 'carepatient'       //ISPC-2493 Lore 03.12.2019
	    );  
	    
	    $this->view->forms=$forms_array;
	    
	}
	public function formquestionlistAction(){

	    if(!empty($_REQUEST['form_ident'])){
    	    $form_ident = $_REQUEST['form_ident'];
	    } else {
    	    
    	    $this->_redirect(APP_BASE . "rubin/formlist");
    	    exit;
	    }
	    $this->view->form_ident = $form_ident;
	    
	    $form_question = array();
	    $form_question = PatientRubinQuestionsTable::find_form_questions($form_ident);
// 	    dd($form_question);
	    $this->view->form_questions = $form_question;
	    
	    
// 	    if ($request->isPost()) {
// 	        $post = $request->getPost();
// // 	        dd($post);
// 	    }

	    
	    if( $this->getRequest()->isPost())
	    {
	        foreach($_POST['order'] as $q_db_id=>$q_order){
	            
	            $dg = Doctrine::getTable('PatientRubinQuestions')->find($q_db_id);
	            if($dg){
	               $dg->question_order = $q_order;
    	           $dg->save();
	            }
	        }
	    }
	}
	
	
	
	public function saveemptyformAction(){

	    $decid = Pms_Uuid::decrypt ( $_GET ['id'] );
	    $ipid = Pms_CommonData::getIpid ( $decid );
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;

	    $result['success'] = 0;
	    $result['message'] = "";
	    
	    $allow_save = 0 ;
	    $form_id = null;
	    $form_total = trim($_REQUEST['form_total']);
	    $form_post_date = $_REQUEST['form_date'];
	    
	    //TODO-2621  Ancuta - 30.10.2019 - changed to acomodate multiple forms 
	    $score_upper_limit = 0 ;
	    switch ($_REQUEST['form_ident']){
	        case 'bdi':
                $score_upper_limit = 63 ;
	            break;
	        case 'gds':
                $score_upper_limit = 15 ;
	            break;
	        case 'mmst':
        	    $score_upper_limit = 30 ;
	            break;
	    }
	    // --
	    
	    if(strlen($form_total) == 0 || empty($form_post_date) ){
	        $result['message'] = $this->translate('Please fill both fields - date and score');
	    } elseif( strtotime($form_post_date) > strtotime(date("d.m.Y"))   ){
	        $result['message'] = $this->translate('Date in future it is not allowed');
	    }
	    elseif( !is_numeric($form_total)  ){
	        $result['message'] = $this->translate('Please check score - it should be NUMERC only');
	    }
	    elseif( $form_total < 0 || $form_total > $score_upper_limit   ){
	        //$result['message'] = $this->translate('Please check score - it should be from 0 to 63');

	        //TODO-2621  Ancuta - 30.10.2019 - changed to accomodate multiple options
	        $message="";
	        $message = $this->translate('Please check score - it should be from 0 to X');
	        $result['message']  =   str_replace('%score_upper_limit', $score_upper_limit, $message);
	        //-- 
	    }
	    else
	    {
	        $allow_save = 1;
	    }
	   
	    
	    if($allow_save == 1 ){
	    
	        $form_date   = !empty($_REQUEST['form_date']) ? date("Y-m-d H:i:s",strtotime($_REQUEST['form_date'])) : date("Y-m-d H:i:s");
    	    $from_data['form_id'] = "";
    	    $from_data['form_type'] = $_REQUEST['form_ident'];
    	    $from_data['form_total'] = $_REQUEST['form_total'];
    	    $from_data['form_date'] = $_REQUEST['form_date'];
    	    $from_data['custom'] = '1';
    	    
    	    // save in forms
    	    $entity = PatientRubinFormsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
    	    
    	    // save in form answer
    	    $form_id = $entity->id;
    	    
	    } else {
	        
	        $result['success'] = 0;
	        
	        echo json_encode($result);
	        exit;
	    }
	  
	    
	    if($form_id)
	    {
    	    $form_total  = $from_data['form_total'];
    	    $form_date_dmY   = date('d.m.Y',strtotime($form_date));
    	    $form_ident = $from_data['form_type'];
    	    switch ($form_ident){
    	        
    	        case 'bdi':
            	    $score_text = "";
            	    
            	    if ($form_total <= 8 )
            	    {
            	        $score_text = "Keine Depression";
            	    }
            	    elseif ($form_total >=9 && $form_total <= 13)
            	    {
            	        $score_text = "Minimale depressive Symptomatik";
            	    }
            	    elseif ($form_total >= 14 && $form_total <= 19)
            	    {
            	        $score_text = "Leichte depressive Symptomatik";
            	    }
            	    elseif ($form_total >=20 && $form_total <= 28)
            	    {
            	        $score_text = "Mittelschwere depressive Symptomatik";
            	    }
            	    elseif ($form_total >=29 && $form_total <= 63)
            	    {
            	        $score_text = "Schwere depressive Symptomatik";
            	    }
        	    break;
        	    
        	    
        	    case 'gds':
            	    $score_text = "";
            	    
            	    if ($form_total <= 5 )
            	    {
            	        $score_text = "unauffällig";
            	    }
            	    elseif ($form_total >=6 && $form_total <= 10)
            	    {
            	        $score_text = "Verdacht auf leicht bis mäßige depressive Symptomatik";
            	    }
            	    elseif ($form_total >=11 && $form_total <= 15)
            	    {
            	        $score_text = "Verdacht auf schwere depressive Symptomatik";
            	    }
        	    break;
        	    
       	        //TODO-2621 Ancuta 30.10.2019 - added empty form to mmst 
        	    case 'mmst':
                    $score_text = "";
        	    
                    if ($form_total <= 9 )
				    {
    				    $score_text = "Schwere Demenz";
				    } 
				    elseif ($form_total >=10 && $form_total <= 19)
				    {
    				    $score_text = "Mittelschwere Demenz";
				    }
				    elseif ($form_total >= 20 && $form_total <= 26)
				    {
    				    $score_text = "Leichte Demenz";
				    }
				    elseif ($form_total >=27 && $form_total <= 30)
				    {
    				    $score_text = "Keine Demenz";
				    }
        	    break;
        	    //--
        	    
        	    
        	    default:
        	        
        	        break;
    	    }
    	    
	        $course_values[$form_ident]['course_type'] = "K";
	        $course_values[$form_ident]['course_tabname'] = "patient_demstepcare_".$form_ident;
	        $course_values[$form_ident]['course_done_name'] = "patient_demstepcare_".$form_ident;
	         
	        $form_ident_course_title = $this->translate($form_ident.'_course_title');
	        
	        $coursetitle =   $form_ident_course_title."\n";
	        $coursetitle .= "Datum der Befragung: ".$form_date_dmY;
	        $coursetitle .= "\n Interpretation des Testergebnisses: ".$form_total." (".$score_text.")";
	         
	         
	         
	        $custcourse = new PatientCourse();
	        $custcourse->ipid = $ipid;
	        $custcourse->course_date = date("Y-m-d H:i:s", time());
	        $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	        $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	        $custcourse->user_id = $userid;
	        $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	        $custcourse->recordid = $form_id;
	        $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	        $custcourse->done_id = $form_id;
	        $custcourse->save();
	        
	        $result['success'] = 1;
	        
	        $result['message'] = $this->translate('Form saved !');
	    } else {
	        // something went wrong
	        $result['message'] = $this->translate('Form not saved, please check data!');
	    }
	    
	    
	    echo json_encode($result);
	    exit;
	}
	
	
	/**
	 * @Ancuta
	 * 11.07.2019
	 * ISPC-2404
	 */
	public function cmaiAction()
	{
	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	
	    $form_ident = 'cmai';
	    $this->view->form_ident = $form_ident;
	
	    $form_name = strtoupper('DemStepCare-'.$form_ident);
	    $form_tabname = strtolower('DemStepCare_'.$form_ident);
	
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	
	
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
	    {
	        /*
	         * 12.07.2019 - Ancuta
	         // Check if patient has a saved form
	    	    $form_details = array();
	    	    $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
	    	    if(!empty($form_details)){
	    	    $form_id = $form_details['id'];
	    	    }
	    	    */
	         
	        // allow multiple forms per patient
	        $form_id = null;
	    }
	
	    $saved_values = array();
	    $saved_values = $this->_dsp_forms_GatherDetails($form_id, $form_ident);
	
	
	    $form->create_form($saved_values, $form_ident);
	
	    $request = $this->getRequest();
	
	
	    if ( ! $request->isPost()) {
	
	        //TODO move to populate
	        //$form->populate($options);
	
	
	    } elseif ($request->isPost()) {
	
	
	        $post = $request->getPost();
	        $form->populate($post);
	
	        $post_form = $post[$form_ident];
	
	
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	             
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */
	
	                $patient_DemStepCare  = $form->save_form_DemStepCare($this->ipid, $post_form);
	
	                $form_total  = $post_form['form_content']['form_total'];
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));

	                if($patient_DemStepCare->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_demstepcare_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_demstepcare_".$form_ident;
	
	                    if( !empty($post_form['form_id'])){
	                       $coursetitle =   "DemStepCare - CMAI-D wurde editiert.\n";
	                    } else{
	                       $coursetitle =   "DemStepCare - CMAI-D \n";
	                    }
	                    $coursetitle .= "Datum der Befragung: ".$form_date_dmY." \n";
	                    $coursetitle .= "Gesamtpunktzahl: ".$form_total;
	
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_DemStepCare->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->done_id = $patient_DemStepCare->id;
	                    $custcourse->save();
	                }
	
	
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	
	
	                $html_form  = $form->__toString();
// 	                					dd($html_form);
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
	                /*$html_print = sprintf($bsHead, APP_BASE)
	                 . $html_form
	                 . $bsFoot
	                ;*/
// 	                					echo $html_print;exit;
	
	                $this->view->form_ident = $form_ident; 
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
// 	                	               					echo $html_print; exit;
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	
	                // Render the HTML as PDF
	                $dompdf->render();
	
	                $canvas = $dompdf->get_canvas();
	
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	                $footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	
	
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-30,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	
	
	
	
	                $output = $dompdf->output();
	
	                // Output the generated PDF to Browser
	                // 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
	                // 					                       $dompdf->stream();
	                // 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
	
	                $patient_file_title = 'patient_demstepcare_'.$form_ident.'_file';
	
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	
	                if ($result !== false) {
	
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	
	
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_DemStepCare->id;
	                    $entity->tabname = 'patient_demstepcare_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	
	                    $recordid = $entity->id;
	
	                    $pdf_date = date('d.m.Y');
	                    $comment =   $this->translate('patient_demstepcare_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_demstepcare_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	
	                }
	
	                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	            }
	
	
	        } else {
	
	
	            $form->populate($post);
	
	        }
	
	    }
	
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	    );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	    );
	
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	}
		
	
	
	/**
	 * @author Ancuta
	 * 06.09.2019
	 * ISPC-2423
	 * 
	 */
	public function dsvAction()
	{
	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	
	    $form_ident = 'dsv';
	    $this->view->form_ident = $form_ident;
	
	    $form_name = strtoupper('DemStepCare-'.$form_ident);
	    $form_tabname = strtolower('DemStepCare_'.$form_ident);
	
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	
	
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
	    {
	        /*
	         * 12.07.2019 - Ancuta
    	    */
	         // Check if patient has a saved form
	    	    $form_details = array();
	    	    $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
	    	    if(!empty($form_details)){
	    	    $form_id = $form_details['id'];
	    	    }
	         
	        // allow multiple forms per patient
// 	        $form_id = null;
	    }
	    $saved_values = array();
	    $saved_values = $this->_dsp_forms_GatherDetails($form_id, $form_ident);
	
	
	    $form->create_form($saved_values, $form_ident);
	
	    $request = $this->getRequest();
	
	
	    if ( ! $request->isPost()) {
	
	        //TODO move to populate
	        //$form->populate($options);
	
	
	    } elseif ($request->isPost()) {
	
	
	        $post = $request->getPost();
	        $form->populate($post);
	
	        $post_form = $post[$form_ident];
	
	
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	             
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */
	
	                $patient_DemStepCare  = $form->save_form_DemStepCare($this->ipid, $post_form);
	
	                $form_total  = $post_form['form_content']['form_total'];
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));

	                if($patient_DemStepCare->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_demstepcare_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_demstepcare_".$form_ident;
	
	                    if( !empty($post_form['form_id'])){
	                       $coursetitle =   "DemStepCare - DSV wurde editiert.\n";
	                    } else{
	                       $coursetitle =   "DemStepCare - DSV \n";
	                    }
	                    $coursetitle .= "Datum der Befragung: ".$form_date_dmY." \n";
	                    
	                    if($post_form[$form_ident]['score_info'] == "green"){
    	                    $coursetitle .= "Stabile Versorgung";
	                    } elseif($post_form[$form_ident]['score_info'] == "red"){
    	                    $coursetitle .= "Erhöhtes Versorgungsrisiko oder Versorgungskrise";
	                        
	                    }
	
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_DemStepCare->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->done_id = $patient_DemStepCare->id;
	                    $custcourse->save();
	                }
	
	
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	
	
	                $html_form  = $form->__toString();
// 	                					dd($html_form);
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
	                /*$html_print = sprintf($bsHead, APP_BASE)
	                 . $html_form
	                 . $bsFoot
	                ;*/
// 					echo $html_print;exit;
	
	                $this->view->form_ident = $form_ident; 
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
//                     echo $html_print; exit;
                    
                    
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	
	                // Render the HTML as PDF
	                $dompdf->render();
	
	                $canvas = $dompdf->get_canvas();
	
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	                $footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	
	
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-30,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	
	
	
	
	                $output = $dompdf->output();
	
	                // Output the generated PDF to Browser
	                // 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
	                // 					                       $dompdf->stream();
	                // 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
	
	                $patient_file_title = 'patient_demstepcare_'.$form_ident.'_file';
	
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	
	                if ($result !== false) {
	
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	
	
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_DemStepCare->id;
	                    $entity->tabname = 'patient_demstepcare_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	
	                    $recordid = $entity->id;
	
	                    $pdf_date = date('d.m.Y');
	                    $comment =   $this->translate('patient_demstepcare_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_demstepcare_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	
	                }
	
	                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	            }
	
	
	        } else {
	
	
	            $form->populate($post);
	
	        }
	
	    }
	
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	    );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	    );
	
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	}
		
	
/*
 * @Lore 06.01.2020
 * ISPC-2509 
 */
	public function dscdsvAction()
	{
	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	    
	    $form_ident = 'dscdsv';
	    $this->view->form_ident = $form_ident;
	    
	    $form_name = strtoupper('DemStepCare-'.$form_ident);
	    $form_tabname = strtolower('DemStepCare_'.$form_ident);
	    
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	    
	    
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
	    {
	        /*
	         * 12.07.2019 - Ancuta
	         */
	        // Check if patient has a saved form
	        $form_details = array();
	        $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
	        if(!empty($form_details)){
	            $form_id = $form_details['id'];
	        }
	        
	        // allow multiple forms per patient
	        // 	        $form_id = null;
	    }
	    $saved_values = array();
 	    $saved_values = $this->_dsp_forms_GatherDetails($form_id, $form_ident);
 	    
	    $form->create_form($saved_values, $form_ident);
	    
	    $request = $this->getRequest();
	    
	    
	    if ( ! $request->isPost()) {
	        
	        //TODO move to populate
	        //$form->populate($options);
	        
	        
	    } elseif ($request->isPost()) {
	        
	        
	        $post = $request->getPost();
	        $form->populate($post);
	        
	        $post_form = $post[$form_ident];
	        
	       
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	            
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */
	                
	                $patient_DemStepCare  = $form->save_form_DemStepCare($this->ipid, $post_form);
	                
	                $form_total  = $post_form['form_content']['form_total'];
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));
	                
	                if($patient_DemStepCare->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_demstepcare_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_demstepcare_".$form_ident;
	                    
	                    if( !empty($post_form['form_id'])){
	                        $coursetitle =   "DemStepCare - Assessment zur Versorgungssituation wurde editiert.\n";
	                    } else{
	                        $coursetitle =   "DemStepCare - Assessment zur Versorgungssituation \n";
	                    }
	                    $coursetitle .= "Datum der Befragung: ".$form_date_dmY." \n";
	                    
	                    
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_DemStepCare->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->done_id = $patient_DemStepCare->id;
	                    $custcourse->save();
	                }
	                
	                
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	                
	                
	                $html_form  = $form->__toString();
//	                	                					dd($html_form);
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	                
	                /*$html_print = sprintf($bsHead, APP_BASE)
	                 . $html_form
	                 . $bsFoot
	                 ;*/
	              //  					echo $html_print;exit;
	                
	                $this->view->form_ident = $form_ident;
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	                
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
	                  //                   echo $html_print; exit;
	                
	                
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	                
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	                
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	                
	                // Render the HTML as PDF
	                $dompdf->render();
	                
	                $canvas = $dompdf->get_canvas();
	                
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	                
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	                //$footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	                $footer_text_pdf = "DemStepCare Assessment zur Versorgungssituation";
	                $footer_text = "{$footer_text_pdf} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	                
	                
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-30,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	                
	                
	                
	                
	                $output = $dompdf->output();
	                
	                // Output the generated PDF to Browser
	                // 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
	                // 					                       $dompdf->stream();
	                // 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
	                
	                $patient_file_title = 'patient_demstepcare_'.$form_ident.'_file';
	                
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	                
	                if ($result !== false) {
	                    
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	                    
	                    
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_DemStepCare->id;
	                    $entity->tabname = 'patient_demstepcare_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	                    
	                    $recordid = $entity->id;
	                    
	                    $pdf_date = date('d.m.Y');
	                    $comment =   $this->translate('patient_demstepcare_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_demstepcare_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	                    
	                }
	                
	                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	            }
	            
	            
	        } else {
	            
	            
	            $form->populate($post);
	            
	        }
	        
	    }
	    
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	        );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	        );
	    
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	    
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	    
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	}
	
	
	/**
	 * @Ancuta
	 * 11.07.2019
	 * ISPC-2404
	 */
	public function nosgerAction()
	{
	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	
	    $form_ident = 'nosger';
	    $this->view->form_ident = $form_ident;
	
	    $form_name = strtoupper('DemStepCare-'.$form_ident);
	    $form_tabname = strtolower('DemStepCare_'.$form_ident);
	
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	
	
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
	    {
	        /*
	         * 12.07.2019 - Ancuta
	         // Check if patient has a saved form
	    	    $form_details = array();
	    	    $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
	    	    if(!empty($form_details)){
	    	    $form_id = $form_details['id'];
	    	    }
	    	    */
	         
	        // allow multiple forms per patient
	        $form_id = null;
	    }
	
	    $saved_values = array();
	    $saved_values = $this->_dsp_forms_GatherDetails($form_id, $form_ident);
	
	
	    $form->create_form($saved_values, $form_ident);
	
	    $request = $this->getRequest();
	
	
	    if ( ! $request->isPost()) {
	
	        //TODO move to populate
	        //$form->populate($options);
	
	
	    } elseif ($request->isPost()) {
	
	
	        $post = $request->getPost();
	
	        $form->populate($post);
	
	        $post_form = $post[$form_ident];
	
	
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */
	
	                $patient_DemStepCare  = $form->save_form_DemStepCare($this->ipid, $post_form);
	
	                $form_total  = $post_form['form_content']['form_total'];
	                $nosger_scores = $post_form['form_content']['nosger_scores'];
	                
	                $nosger_score_labels = $form->nosger_scores(true,false);
	                
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));
	
	                if($patient_DemStepCare->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_demstepcare_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_demstepcare_".$form_ident;
	
	                    
	                    if( !empty($post_form['form_id'])){
	                        $coursetitle =   "DemStepCare - NOSGER II wurde editiert.\n";
	                    } else{
	                        $coursetitle =   "DemStepCare - NOSGER II \n";
	                    }
	                    $coursetitle .= "Datum der Befragung: ".$form_date_dmY;
	                    foreach($nosger_score_labels as $score_ident=>$score_label){
	                        if(!isset($nosger_scores[$score_ident])){
	                            $nosger_scores[$score_ident] = 0 ;
	                        } 
	                        
    	                    $coursetitle .= "\n".$score_label.": ".$nosger_scores[$score_ident];
	                    }
	                    
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_DemStepCare->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->done_id = $patient_DemStepCare->id;
	                    $custcourse->save();
	                }
	
	
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	
	
	                $html_form  = $form->__toString();
	                // 					dd($html_form);
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	
	                /*$html_print = sprintf($bsHead, APP_BASE)
	                 . $html_form
	                 . $bsFoot
	                ;*/
	                // 					echo $html_print;exit;
	
	                $this->view->form_ident = $form_ident;
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
	                // 	               					echo $html_print; exit;
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	
	                // Render the HTML as PDF
	                $dompdf->render();
	
	                $canvas = $dompdf->get_canvas();
	
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	                $footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	
	
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-30,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	
	
	
	
	                $output = $dompdf->output();
	
	                // Output the generated PDF to Browser
	                // 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
	                // 					                       $dompdf->stream();
	                // 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
	
	                $patient_file_title = 'patient_demstepcare_'.$form_ident.'_file';
	
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	
	                if ($result !== false) {
	
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	
	
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_DemStepCare->id;
	                    $entity->tabname = 'patient_demstepcare_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	
	                    $recordid = $entity->id;
	
	                    $pdf_date = date('d.m.Y');
	                    $comment =   $this->translate('patient_demstepcare_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_demstepcare_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	
	                }
	
	                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	            }
	
	
	        } else {
	
	
	            $form->populate($post);
	
	        }
	
	    }
	
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	    );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	    );
	
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	}
		
	public function  demstepcareAction(){

	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	    
	    $form_ident = 'demstepcare';
	    $this->view->form_ident = $form_ident;
	    
	    $form_name = strtoupper($form_ident);
	    $form_tabname = strtolower('DemStepCare_'.$form_ident);
	    
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	    
	    
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
	    {
	        
	         //12.07.2019 - Ancuta
	         // Check if patient has a saved form
	         $form_details = array();
	         $form_details = PatientDemstepcareTable::find_form_By_patient($ipid);
	         if(!empty($form_details)){
	           $form_id = $form_details['id'];
	         }
	    }
	   
	    if($_REQUEST['action'] == 'filedelete')
		{
	    	if($_REQUEST['doc_id'] > 0)
	    	{
	    		$previleges = new Pms_Acl_Assertion();
	    		$returnadd = $previleges->checkPrevilege('patientfileupload', $userid, 'candelete');
	    	
	    		if(!$returnadd)
	    		{
	    			$this->_redirect(APP_BASE . "error/previlege");
	    		}
	    	
	    		$upload_form = new Application_Form_PatientFileUpload();
	    		$upload_form->deleteFile($_REQUEST['doc_id']);
	    		
	    		$this->_redirect("rubin/demstepcare?id=" . $this->enc_id);
	    	}
	    }
	    
// 	    dd($form_id);
	    $saved_values = array();
	    $saved_values = $this->_demstepcare_GatherDetails($form_id);
	    
	    
	    $extra_forms = array();
	    //  Get MMST values
	    $saved_values['extra_forms']['mmst'] = PatientRubinFormsTable::find_form_By_patient($ipid,'mmst');
	    //  Get GDS-15  values
	    $saved_values['extra_forms']['gds'] = PatientRubinFormsTable::find_form_By_patient($ipid,'gds');
	    //  Get BDI  values
	    $saved_values['extra_forms']['bdi'] = PatientRubinFormsTable::find_form_By_patient($ipid,'bdi');
 
	    
	    $form->create_form($saved_values, $form_ident);
	    
	    $request = $this->getRequest();
	    
	    
	    if ( ! $request->isPost()) {
	    
	        //TODO move to populate
	        //$form->populate($options);
	    
	    
	    } elseif ($request->isPost()) {
	    
	    
	        $post = $request->getPost();
	        $form->populate($post);
	        
	        $post_form = $post[$form_ident];
	        $post_form['qquuid'] = $post['qquuid'];
	        $post_form['qqfile'] = $post['qqfile'];
	    
	    
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */
	    
 
	                $patient_rubin  = $form->save_form_DemStepCare($this->ipid, $post_form);
	    
	                $form_total  = $post_form['form_content']['form_total'];
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));
	                 
 
	    
	                if($patient_rubin->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_demstepcare_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_demstepcare_".$form_ident;
	    
	                    
	                    if( !empty($post_form['form_id'])){
	                        $coursetitle =   "DemStepCare Status wurde editiert.\n";
	                    } else{
	                        $coursetitle =   "DemStepCare Status gespeichert\n";
	                    }
	                    $coursetitle .= "Datum: ".$form_date_dmY;
	    
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_rubin->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->done_id = $patient_rubin->id;
	                    $custcourse->save();
	                }
	    
	                if(!empty($this->getRequest()->getPost('qquuid'))){
    	                $qquuid = $this->getRequest()->getPost('qquuid');
    	                $qquuid_title = $this->getRequest()->getPost('qquuid_title');
    	    
    	                $decid = $this->dec_id;
    	                $action_name = "upload_dms_patient_files{$decid}";
    	                 
    	                 
    	                if (is_array($qquuid) && ! empty($qquuid) && ($last_uploaded_files = $this->get_last_uploaded_file($action_name, $qquuid, $clientid))) {
    	                
    	                    $upload_form = new Application_Form_PatientFileUpload();
    	                    foreach ($qquuid as $k=>$qquuidID) {
    	                         
    	                        if (($last_uploaded_file = $last_uploaded_files[$qquuidID]) && $last_uploaded_file['isZipped'] == 1) {
    	                             
    	                            $file_name = pathinfo($last_uploaded_file['filepath'], PATHINFO_FILENAME) . "/" . $last_uploaded_file['fileInfo']['name'];
    	                            $file_type = strtoupper(pathinfo($last_uploaded_file['filename'], PATHINFO_EXTENSION));
 
	                                $post = [
	                                    'ipid'      => $ipid,
	                                    'clientid'  => $clientid,
	                                    'title'     => ! empty($qquuid_title[$k]) ? $qquuid_title[$k] : $last_uploaded_file['filename'] ,
	                                    'filetype'  => $file_type,
	                                    'file_name' => $file_name,
	                                    'zipname'   => $last_uploaded_file['filepath'], //filepath
	                                    'active_version' => '0',
	                                    
	                                    'tabname'   => "patient_demstepcare_page",
	                                    'recordid'  => $patient_rubin->id,
	                                ];
    	                             
    	                            $rec = $upload_form->insertData($post);
    	                             
    	                            $this->delete_last_uploaded_file($action_name, $qquuidID, $clientid);
    	                        }
    	                         
    	                    }
    	                }
    	                
                        //remove session stuff
                        $_SESSION['filename'] = '';
                        $_SESSION['filetype'] = '';
                        $_SESSION['filetitle'] = '';
                        unset($_SESSION['filename']);
                        unset($_SESSION['filetype']);
                        unset($_SESSION['filetitle']);
	                
                    }
	             /*    
	                
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	    
	    
	                $html_form  = $form->__toString();
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	    
	                $this->view->form_ident = $form_ident;
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	    
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	    
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	    
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	    
	                // Render the HTML as PDF
	                $dompdf->render();
	    
	                $canvas = $dompdf->get_canvas();
	    
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	    
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	                $footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	    
	    
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-30,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	    
	    
	    
	    
	                $output = $dompdf->output();
	    
	                $patient_file_title = 'patient_demstepcare_'.$form_ident.'_file';
	    
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	    
	                if ($result !== false) {
	    
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	    
	    
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_rubin->id;
	                    $entity->tabname = 'patient_demstepcare_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	    
	                    $recordid = $entity->id;
	    
	                    $pdf_date = date('d.m.Y');
	                    $comment =   $this->translate('patient_demstepcare_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_demstepcare_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	    
	                } */
	    
	                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	            }
	    
	    
	        } else {
	    
	    
	            $form->populate($post);
	    
	        }
	    
	    }
	    
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	    );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	    );
	    
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	    
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	    
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	    
	}

	/**
	 * @Lore
	 * 12.09.2019
	 * ISPC-2455
	 */
	public function badlAction()
	{
	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	    
	    $form_ident = 'badl';
	    $this->view->form_ident = $form_ident;
	    
	    $form_name = strtoupper('DemStepCare-'.$form_ident);
	    $form_tabname = strtolower('DemStepCare_'.$form_ident);
	    
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	    
	    
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
	    {
	        /*
	         * 12.07.2019 - Ancuta
	         // Check if patient has a saved form
	         $form_details = array();
	         $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
	         if(!empty($form_details)){
	         $form_id = $form_details['id'];
	         }
	         */
	        
	        // allow multiple forms per patient
	        $form_id = null;
	    }
	    
	    $saved_values = array();
	    $saved_values = $this->_dsp_forms_GatherDetails($form_id, $form_ident);
	    
	    
	    $form->create_form($saved_values, $form_ident);
	    
	    $request = $this->getRequest();
	    
	    
	    if ( ! $request->isPost()) {
	        
	        //TODO move to populate
	        //$form->populate($options);
	        
	        
	    } elseif ($request->isPost()) {
	        
	        
	        $post = $request->getPost();
	        $form->populate($post);
	        
	        $post_form = $post[$form_ident];
	        
	        
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	            
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */
	                
	                $patient_DemStepCare  = $form->save_form_DemStepCare($this->ipid, $post_form);
	                
	                $form_total  = $post_form['form_content']['form_total'];
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));
	                
	                if($patient_DemStepCare->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_demstepcare_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_demstepcare_".$form_ident;
	                    
	                    if( !empty($post_form['form_id'])){
	                        $coursetitle = $this->translate('demstepcare_'.$form_ident.'_course_title_form_edited');
	                    } else{
	                        $coursetitle =  $this->translate('demstepcare_'.$form_ident.'_course_title_form_created');
	                    }
	                    $coursetitle .= "\n";
	                    $coursetitle .= "Datum der Befragung: ".$form_date_dmY." \n";
	                    $coursetitle .= "Gesamtpunktzahl: ".$form_total;
	                    
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_DemStepCare->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->done_id = $patient_DemStepCare->id;
	                    $custcourse->save();
	                }
	                
	                
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	                
	                
	                $html_form  = $form->__toString();
	                // 	                					dd($html_form);
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	                
	                /*$html_print = sprintf($bsHead, APP_BASE)
	                 . $html_form
	                 . $bsFoot
	                 ;*/
	                // 	                					echo $html_print;exit;
	                
	                $this->view->form_ident = $form_ident;
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	                
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
	                // 	                	               					echo $html_print; exit;
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	                
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	                
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	                
	                // Render the HTML as PDF
	                $dompdf->render();
	                
	                $canvas = $dompdf->get_canvas();
	                
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	                
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	                $footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	                
	                
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-30,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	                
	                
	                
	                
	                $output = $dompdf->output();
	                
	                // Output the generated PDF to Browser
	                // 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
	                // 					                       $dompdf->stream();
	                // 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
	                
	                $patient_file_title = 'patient_demstepcare_'.$form_ident.'_file';
	                
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	                
	                if ($result !== false) {
	                    
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	                    
	                    
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_DemStepCare->id;
	                    $entity->tabname = 'patient_demstepcare_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	                    
	                    $recordid = $entity->id;
	                    
	                    $pdf_date = date('d.m.Y');
	                    $comment =   $this->translate('patient_demstepcare_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_demstepcare_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	                    
	                }
	                
	                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	            }
	            
	            
	        } else {
	            
	            
	            $form->populate($post);
	            
	        }
	        
	    }
	    
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	        );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	        );
	    
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	    
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	    
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	}
	
	/*
	 * @auth Lore 16.09.2019
	 * ISPC-2456
	 */
	public function cmscaleAction()
	{
	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	    
	    $form_ident = 'cmscale';
	    $this->view->form_ident = $form_ident;
	    
	    $form_name = strtoupper('DemStepCare-'.$form_ident);
	    $form_tabname = strtolower('DemStepCare_'.$form_ident);
	    
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	    
	    
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
	    {
	        /*
	         * 12.07.2019 - Ancuta
	         // Check if patient has a saved form
	         $form_details = array();
	         $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
	         if(!empty($form_details)){
	         $form_id = $form_details['id'];
	         }
	         */
	        
	        // allow multiple forms per patient
	        $form_id = null;
	    }
	    
	    $saved_values = array();
	    $saved_values = $this->_dsp_forms_GatherDetails($form_id, $form_ident);
	    
	    
	    $form->create_form($saved_values, $form_ident);
	    
	    $request = $this->getRequest();
	    
	    
	    if ( ! $request->isPost()) {
	        
	        //TODO move to populate
	        //$form->populate($options);
	        
	        
	    } elseif ($request->isPost()) {
	        
	        
	        $post = $request->getPost();
	        $form->populate($post);
	        
	        $post_form = $post[$form_ident];
	        
	        
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	            
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */
	                
	                $patient_DemStepCare  = $form->save_form_DemStepCare($this->ipid, $post_form);
	                
	                $form_total  = $post_form['form_content']['form_total'];
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));
	                
	                if($patient_DemStepCare->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_demstepcare_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_demstepcare_".$form_ident;
	                    
	                    if( !empty($post_form['form_id'])){
	                        $coursetitle = $this->translate('demstepcare_'.$form_ident.'_course_title_form_edited');
	                    } else{
	                        $coursetitle =  $this->translate('demstepcare_'.$form_ident.'_course_title_form_created');
	                    }
	                    $coursetitle .= "\n";
	                    $coursetitle .= "Datum der Befragung: ".$form_date_dmY." \n";
	                    $coursetitle .= "Summe: ".$form_total;
	                    
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_DemStepCare->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->done_id = $patient_DemStepCare->id;
	                    $custcourse->save();
	                }
	                
	                
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	                
	                
	                $html_form  = $form->__toString();
	                // 	                					dd($html_form);
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	                
	                /*$html_print = sprintf($bsHead, APP_BASE)
	                 . $html_form
	                 . $bsFoot
	                 ;*/
	                // 	                					echo $html_print;exit;
	                
	                $this->view->form_ident = $form_ident;
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	                
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
	                // 	                	               					echo $html_print; exit;
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	                
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	                
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	                
	                // Render the HTML as PDF
	                $dompdf->render();
	                
	                $canvas = $dompdf->get_canvas();
	                
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	                
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	                $footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	                
	                
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-30,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	                
	                
	                
	                
	                $output = $dompdf->output();
	                
	                // Output the generated PDF to Browser
	                // 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
	                // 					                       $dompdf->stream();
	                // 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
	                
	                $patient_file_title = 'patient_demstepcare_'.$form_ident.'_file';
	                
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	                
	                if ($result !== false) {
	                    
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	                    
	                    
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_DemStepCare->id;
	                    $entity->tabname = 'patient_demstepcare_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	                    
	                    $recordid = $entity->id;
	                    
	                    $pdf_date = date('d.m.Y');
	                    $comment =   $this->translate('patient_demstepcare_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_demstepcare_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	                    
	                }
	                
	                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	            }
	            
	            
	        } else {
	            
	            
	            $form->populate($post);
	            
	        }
	        
	    }
	    
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	        );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	        );
	    
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	    
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	    
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	}
	
	/*
	 * @auth Lore 02.12.2019
	 * ISPC-2492
	 */
	public function carerelatedAction()
	{
	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	    
	    $form_ident = 'carerelated';
	    $this->view->form_ident = $form_ident;
	    
	    $form_name = strtoupper('RUBIN-'.$form_ident);
	    $form_tabname = strtolower('RUBIN_'.$form_ident);
	    
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	    
	    $patientmaster = new PatientMaster();
	    // NOT OK  - CHANGE to TOD date
	    $age = $patientmaster->GetAge(date("Y-m-d", strtotime($this->_patientMasterData['birthd'])),date('Y-m-d'),true );
	    
	    $this->view->patient_age = $age;
	    
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
	    {
	        /*
	         * 12.07.2019 - Ancuta
	         // Check if patient has a saved form
	         $form_details = array();
	         $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
	         if(!empty($form_details)){
	         $form_id = $form_details['id'];
	         }
	         */
	        // allow multiple forms per patient
	        $form_id = null;
	    }
	    
	    $saved_values = array();
	    $saved_values = $this->_rubin_forms_GatherDetails($form_id, $form_ident);
	    
	    
	    
	    $form->create_form($saved_values, $form_ident);
	    
	    $request = $this->getRequest();
	    
	    
	    if ( ! $request->isPost()) {
	        
	        //TODO move to populate
	        //$form->populate($options);
	        
	        
	    } elseif ($request->isPost()) {
	        
	        $post = $request->getPost();
	        
	        $form->populate($post);
	        
	        $post_form = $post[$form_ident];
	        	        
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	            
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */
	                $patient_rubin  = $form->save_form_rubin($this->ipid, $post_form);
	                
	                $form_total  = $post_form['form_content']['form_total'];
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));
	                // calculate score
	                
	                $post_questions = $post_form['form_content'];
	                
	                $score_text = 0 ;
	                foreach($post_questions as $q_ident => $q_options){
	                    foreach($q_options as $values){
	                        $score_text += $values;
	                    }
	                }
	                	                
	                if($patient_rubin->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_rubin_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_rubin_".$form_ident;
	                    
	                    $coursetitle =  "Blick auf die Versorgung (Nahestehende) \n"  ;
	                    $coursetitle .= "Datum der Befragung: ".$form_date_dmY." \n";
	                   // $coursetitle .=  $form_total." (".$score_text.")";
	                    
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_rubin->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_id = $patient_rubin->id;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->save();
	                }
	                
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	                
	                
	                $html_form  = $form->__toString();
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	                
	                /*$html_print = sprintf($bsHead, APP_BASE)
	                 . $html_form
	                 . $bsFoot
	                 ;*/
	                // 					echo $html_print;exit;
	                
	                $this->view->form_ident = $form_ident;
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	                
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
	                // 					echo $html_print; exit;
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	                
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	                
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	                
	                // Render the HTML as PDF
	                $dompdf->render();
	                
	                $canvas = $dompdf->get_canvas();
	                
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	                
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	              //  $footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	                $carerela = $this->translate('carerelated_footer');
	                $footer_text = "{$carerela} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	                
	                
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-20,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	                
	                
	                
	                
	                $output = $dompdf->output();
	                
	                // Output the generated PDF to Browser
	                // 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
	                // 					                       $dompdf->stream();
	                // 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
	                
	                $patient_file_title = 'patient_rubin_'.$form_ident.'_file';
	                
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	                
	                if ($result !== false) {
	                    
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	                    
	                    
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_rubin->id;
	                    $entity->tabname = 'patient_rubin_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	                    
	                    $recordid = $entity->id;
	                    
	                    
	                    $comment =   $this->translate('patient_rubin_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_name = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_generated');;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	                    
	                    
	                    
	                }
	                
	                
	                // empty the post by using a redirect
	                //$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ), array (
	                // 						$this->redirect ( APP_BASE . $this->getRequest ()->getControllerName () . "/" . $this->getRequest ()->getActionName () . "?id=" . Pms_Uuid::encrypt ( $this->_patientMasterData ['id'] ) . "&selected_month=" . $query_date, array (
	                    //"exit" => true
	                    // 					) );
	                    $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	                    
	                    // 					$this->_redirect(APP_BASE . "rubin/rubin".$form_ident."?id=" . $_REQUEST['id']);
	            }
	            
	            
	        } else {
	            
	            
	            $form->populate($post);
	            
	        }
	        
	    }
	    
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	        );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	        );
	    
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	    
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	    
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	}

	/**
	 * ISPC-2493 Lore 03.12.2019
	 */
	public function carepatientAction()
	{
	    $ipid = $this->ipid;
	    $clientid = $this->logininfo->clientid;
	    $userid = $this->logininfo->userid;
	    
	    $form_ident = 'carepatient';
	    $this->view->form_ident = $form_ident;
	    
	    $form_name = strtoupper('RUBIN-'.$form_ident);
	    $form_tabname = strtolower('RUBIN_'.$form_ident);
	    
	    $form = new Application_Form_PatientRubinForms(array(
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $form_name,
	        '_page_name'           => $form_name,
	    ));
	    
	    $patientmaster = new PatientMaster();
	    // NOT OK  - CHANGE to TOD date
	    $age = $patientmaster->GetAge(date("Y-m-d", strtotime($this->_patientMasterData['birthd'])),date('Y-m-d'),true );
	    
	    $this->view->patient_age = $age;
	    
	    if($_REQUEST['form_id'])
	    {
	        $form_id = $_REQUEST['form_id'];
	    }
	    else
	    {
	        /*
	         * 12.07.2019 - Ancuta
	         // Check if patient has a saved form
	         $form_details = array();
	         $form_details = PatientRubinFormsTable::find_form_By_patient($ipid,$form_ident);
	         if(!empty($form_details)){
	         $form_id = $form_details['id'];
	         }
	         */
	        // allow multiple forms per patient
	        $form_id = null;
	    }
	    
	    $saved_values = array();
	    $saved_values = $this->_rubin_forms_GatherDetails($form_id, $form_ident);
	    
	    
	    
	    $form->create_form($saved_values, $form_ident);
	    
	    $request = $this->getRequest();
	    
	    
	    if ( ! $request->isPost()) {
	        
	        //TODO move to populate
	        //$form->populate($options);
	        
	        
	    } elseif ($request->isPost()) {
	        
	        $post = $request->getPost();
	        
	        $form->populate($post);
	        
	        $post_form = $post[$form_ident];
	        
	        if ( $form->isValid($post)) // no validation is implemented
	        {
	            
	            if($_POST[$form_ident]['formular']['button_action'] == "save")
	            {
	                /* ------------------- SAVE FORM ----------------------------------- */
	                $patient_rubin  = $form->save_form_rubin($this->ipid, $post_form);
	                
	                $form_total  = $post_form['form_content']['form_total'];
	                $form_date   = !empty($post_form[$form_ident]['form_date']) ? date("Y-m-d H:i:s",strtotime($post_form[$form_ident]['form_date'])) : date("Y-m-d H:i:s");
	                $form_date_dmY   = date('d.m.Y',strtotime($form_date));
	                // calculate score
	                
	                $post_questions = $post_form['form_content'];
	                
	                $score_text = 0 ;
	                foreach($post_questions as $q_ident => $q_options){
	                    foreach($q_options as $values){
	                        $score_text += $values;
	                    }
	                }
	                
	                if($patient_rubin->id)
	                {
	                    $course_values[$form_ident]['course_type'] = "K";
	                    $course_values[$form_ident]['course_tabname'] = "patient_rubin_".$form_ident;
	                    $course_values[$form_ident]['course_done_name'] = "patient_rubin_".$form_ident;
	                    
	                    $coursetitle =  "Blick auf die Versorgung (Patientin/Patient) \n"  ;
	                    $coursetitle .= "Datum der Befragung: ".$form_date_dmY." \n";
	                    //$coursetitle .=  $form_total." (".$score_text.")";
	                    
	                    $custcourse = new PatientCourse();
	                    $custcourse->ipid = $ipid;
	                    $custcourse->course_date = date("Y-m-d H:i:s", time());
	                    $custcourse->course_type = Pms_CommonData::aesEncrypt($course_values[$form_ident]['course_type']);
	                    $custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes($coursetitle));
	                    $custcourse->user_id = $userid;
	                    $custcourse->tabname = Pms_CommonData::aesEncrypt( $course_values[$form_ident]['course_tabname']);
	                    $custcourse->recordid = $patient_rubin->id;
	                    $custcourse->done_name = $course_values[$form_ident]['course_done_name'] ;
	                    $custcourse->done_id = $patient_rubin->id;
	                    $custcourse->done_date = $form_date ;
	                    $custcourse->save();
	                }
	                
	                //$form->removeDisplayGroup('form_actions');
	                $form->removeDecorator('Form');
	                $form->removeSubForm('tabs_navi');
	                $form->removeSubForm('form_actions');
	                $today_date = date('d.m.Y');
	                $nice_name_epid = $this->_patientMasterData['nice_name_epid'];
	                
	                
	                $html_form  = $form->__toString();
	                $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);
	                
	                
	                $this->view->form_ident = $form_ident;
	                $this->view->form_pdf = $html_form; //this is the body of the pdf
	                
	                $html_print = $this->view->render("templates/rubinforms_pdf.phtml");
	                // 					echo $html_print; exit;
	                $options = new Options();
	                $options->set('isRemoteEnabled', false);
	                $dompdf = new Dompdf($options);
	                
	                $dompdf->loadHtml($html_print);
	                // (Optional) Setup the paper size and orientation
	                $dompdf->setPaper('A4', 'portrait');
	                
	                $dompdf->set_option("enable_php",true);
	                $dompdf->set_option('defaultFont', 'times');
	                $dompdf->set_option("fontHeightRatio",0.90);
	                
	                // Render the HTML as PDF
	                $dompdf->render();
	                
	                $canvas = $dompdf->get_canvas();
	                
	                $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
	                $footer_font_size = 8;
	                
	                $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
	                $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
	               // $footer_text = "{$form_name} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	                $carepati = $this->translate('carepatient_footer');
	                $footer_text = "{$carepati} | Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text
	                
	                
	                $canvas->page_text(
	                    ($canvas->get_width() - $text_width)/2,
	                    $canvas->get_height()-30,
	                    $footer_text,
	                    $footer_font_family,
	                    $footer_font_size,
	                    array(0,0,0));
	                
	                
	                
	                
	                $output = $dompdf->output();
	                
	                // Output the generated PDF to Browser
	                // 					$dompdf->stream($this->translate(PatientBesd::PATIENT_FILE_TITLE), array('Attachment' => true));
	                // 					                       $dompdf->stream();
	                // 					$dompdf->stream($this->translate(PatientRubin::PATIENT_FILE_TITLE), array('Attachment' => true));
	                
	                $patient_file_title = 'patient_rubin_'.$form_ident.'_file';
	                
	                $result = $this->dompdf_ToFTP($output, $this->translate($patient_file_title));
	                
	                if ($result !== false) {
	                    
	                    $encrypted = Pms_CommonData::aesEncryptMultiple(array(
	                        'title' => $this->translate($patient_file_title).' ['.$form_date_dmY.']',
	                        'file_name' => $result,
	                        'file_type' => 'PDF',
	                    ));
	                    
	                    
	                    $entity = new PatientFileUpload ();
	                    //bypass triggers, we will use our own
	                    $entity->triggerformid = null;
	                    $entity->triggerformname = null;
	                    $entity->title = $encrypted['title'];
	                    $entity->ipid = $this->ipid;
	                    $entity->file_name = $encrypted['file_name'];
	                    $entity->file_type = $encrypted['file_type'];
	                    //$entity->recordid = $patient_rubin->id;
	                    $entity->tabname = 'patient_rubin_'.$form_ident;
	                    $entity->system_generated = "0";
	                    $entity->save();
	                    
	                    $recordid = $entity->id;
	                    
	                    
	                    $comment =   $this->translate('patient_rubin_'.$form_ident.' PDF was created');
	                    $comment =   str_replace('%date', $form_date_dmY, $comment);
	                    $cust = new PatientCourse();
	                    $cust->ipid = $this->ipid;
	                    $cust->course_date = date("Y-m-d H:i:s", time());
	                    $cust->course_type = Pms_CommonData::aesEncrypt("K");
	                    $cust->course_title = Pms_CommonData::aesEncrypt($comment);
	                    $cust->recordid = $recordid;
	                    $cust->tabname = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_saved');
	                    $cust->user_id = $userid;
	                    $cust->done_name = Pms_CommonData::aesEncrypt('patient_rubin_'.$form_ident.'_generated');;
	                    $cust->done_date = $form_date;
	                    $cust->save();
	                    
	                    
	                    
	                }
	                
	              
	                $this->_redirect(APP_BASE . "patientcourse/patientcourse?id=" . $_REQUEST['id']);
	                
	                // 					$this->_redirect(APP_BASE . "rubin/rubin".$form_ident."?id=" . $_REQUEST['id']);
	            }
	            
	            
	        } else {
	            
	            
	            $form->populate($post);
	            
	        }
	        
	    }
	    
	    //@todo : move messages in layout
	    $this->view->SuccessMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('SuccessMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
	        );
	    $this->view->ErrorMessages = array_merge(
	        $this->_helper->flashMessenger->getMessages('ErrorMessages'),
	        $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
	        );
	    
	    $this->_helper->flashMessenger->clearMessages('ErrorMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
	    
	    $this->_helper->flashMessenger->clearMessages('SuccessMessages');
	    $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
	    
	    $this->view->form = $form;
	    $this->view->enc_id = $this->enc_id;
	}
	
}
		
?>