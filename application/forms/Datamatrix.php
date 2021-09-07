<?php
/**
 * 
 * @author claudiu  
 * Aug 31, 2017 
 *
 */
class Application_Form_Datamatrix extends Pms_Form
{
	
	private $triggerformid = 0; //use 0 if you want not to trigger
	
	private $triggerformname = "frmDatamatrix";  //define the name if you want to piggyback some triggers

	
	public function process_step( $post = array() ) {
		
		
		$result = false;
		
		switch($post['step']) {
			
			case "1":{
				//user input xml, makeit to array
				$result = $this->xml2array($post);
				
				break;
			}
			
			case "2":{
				// xml is ok, user selected from the array of medis what he wants to import
				$result = $this->save_new_medis($post);
				break;
			}
			default:
				//why you cumm in hia?
				$result = array("success"=> false, "message" => "you are in the wrong place");
			
		}
		
		return $result;
	}
	
	
	//this is step 1 in datamatrix import
	private function xml2array ( $post = array() )
	{
		
// 		@todo
// 		$dmXmlPerPage[$i]  = $barcodeService->convertUtf8Latin1( $dmXmlPerPage[$i] , $latin1ConvStrict );
		
		$xml_string = $post['datamatrix_xml'];

		$decid = (isset( $post['decid'])) ? $post['decid'] : Pms_Uuid::decrypt($post['id']); // patient_master id
		
		$result = false;
	    // Maria:: Migration ISPC to CISPC 08.08.2020					
		//$version = "2.3";//should be 2.4.1
		$version = "2.6";//should be 2.4.1//ISPC-2551 Ancuta 06.04.2020
		
		$datamatrix_lang = $this->translate('datamatrix_lang');
			
		$DeKbv_Bmp2 = new Pms_DeKbv_Bmp2(array(
				'tcpdf_service'   => null ,
				'barcode_service' => null ,
				'version'         => $version ,
				'generic'         => null
		) );
		

		$DeKbv_Bmp2->importDataMatrixXml($xml_string);

		$xpath = new DOMXPath($DeKbv_Bmp2->getDataMatrixDOM());
		$MP_nodeList = $xpath->query('/MP');
		if ( ! $MP_nodeList->length ) {
			
			return $result;
			throw new Exception( __METHOD__ . ' : <MP> root node missing.', 1 );
		}
		$MP_node = $MP_nodeList->item(0);
		
		$dataArray = $DeKbv_Bmp2->getArrayFromNode( $MP_node , array( 'skip' => array( 'MP.S' ) ) );
		
		$MPP = $dataArray['P'];
		
		$MPPb = '';
		if ( isset( $MPP['b'] ) ) {
			$dt = new Datetime( $MPP['b'] );
			$MPPb = " (". $dt->format('d.m.Y') .")";
		}
		
		$MPPg = isset( $MPP['g'] ) ? $MPP['g'] : "";//first name
		$MPPf = isset( $MPP['f'] ) ? $MPP['f'] : "";//last name
		
		$xml_patient_info = array(
				'dob'=>$MPPb,
				'first_name'=>$MPPg,
				'last_name'=>$MPPf,
		);
		
		
		// fetch PZN-based data (all in one query) ...
		$pznList = array();
		foreach ( $MP_node->childNodes as $sectionNode ) {
			if ( $sectionNode->nodeType !== XML_ELEMENT_NODE || 'S' !== $sectionNode->nodeName ) {
				continue;
			}
			foreach ( $sectionNode->childNodes as $mrx ) { // => <M>, <X> or <R> node
				if ( 'M' == $mrx->nodeName ) {
					$pzn = trim( $mrx->getAttribute('p') );
					if ( ! empty( $pzn ) && ! in_array( $pzn, $pznList ) ) {
						array_push( $pznList, $pzn );
					}
				}
			}
		}
		/*
		 * //ISPC-2346 - comented by Ancuta 04.07.2019
		$pznResult = $DeKbv_Bmp2->getPznData( $pznList );
		if ( 'error' == $pznResult['status'] ) {
		    $errorMsg .= '| ' .  $pznResult['error'];
		}
		$pznData = $pznResult['products'];
		*/
		
		//ISPC-2346 - changed by Ancuta 04.07.2019 - so data for each pzn is searched - not all at once, as if one of the pzn is no longer available - error is sent back
		// and the valid ones were not sent 
		$pznResult=array();
		foreach($pznList as $pk=>$pzn_item){
    		$pznResult[$pzn_item] = $DeKbv_Bmp2->getPznData( array($pzn_item) );
    		if ( 'error' == $pznResult[$pzn_item]['status'] ) {
    			$errorMsg .= '| ' .  $pznResult[$pzn_item]['error'];
    			$pznResult[$pzn_item]['products'][$pzn_item]['error'] = $pznResult[$pzn_item]['error']; 
    		}  
    		
        	//$pznData[$pzn_item] = $pznResult[$pzn_item]['products'][$pzn_item];
        	//ANCUTA 06.04.2020 edited
        	if(!empty($pznResult[$pzn_item]['products'][$pzn_item])){
        	    $pznData[$pzn_item] = $pznResult[$pzn_item]['products'][$pzn_item];
        	} else{
        	    $pzn_noleading_zero = ltrim( rtrim( $pzn_item ) , ' 0' ); // " 00246 " => "246"
        	    if(!empty($pznResult[$pzn_item]['products'][$pzn_noleading_zero])){
        	        $pznData[$pzn_noleading_zero] = $pznResult[$pzn_item]['products'][$pzn_noleading_zero];
        	    }
        	}
        	//-- 
		}

		$codeToSTitle = $DeKbv_Bmp2->keyFileToArray( 'KBV_BMP2_ZWISCHENUEBERSCHRIFT' );
		$codeToForm   = $DeKbv_Bmp2->keyFileToArray( 'KBV_BMP2_DARREICHUNGSFORM' );
		$codeToUnit   = $DeKbv_Bmp2->keyFileToArray( 'KBV_BMP2_DOSIEREINHEIT' );
		
		$my_medication_groups = PatientDrugPlan::$KBV_BMP2_ZWISCHENUEBERSCHRIFT_ASSOC;
		
		$all_medis = array(); // array to hold all medications from xml, ungrouped
		
		foreach ( $MP_node->childNodes as $sectionNode ) {
			if ( $sectionNode->nodeType !== XML_ELEMENT_NODE || 'S' !== $sectionNode->nodeName ) {
				continue;
			}
			$i++;
			$rowsPerSection[$i] = 0;
			
			$group_owner   = ''; //isbedarf, etc..
			
			$STitleCode = trim( $sectionNode->getAttribute('c') );
			$STitleText = trim( $sectionNode->getAttribute('t') );
			// $countKids  = $sectionNode->childNodes->length;
			
			if ( strlen( $STitleCode )  &&  '' === $STitleText ) {
				if ( isset( $codeToSTitle[$STitleCode] ) ) {
					$STitle = $codeToSTitle[$STitleCode];
					
				}
				else {
					$STitle = $STitleCode;
				}
				
				if ( isset( $my_medication_groups[$STitleCode] ) ) {
					$group_owner =  $my_medication_groups[$STitleCode] ;
				}
			}
			
						
			foreach ( $sectionNode->childNodes as $mrx ) { // => <M>, <X> or <R> node
				if ( 'M' != $mrx->nodeName || $mrx->nodeType !== XML_ELEMENT_NODE) {
					continue;
				}
				
				// <M ... >(<W ... >)</M>
				
				// reset output variables ...
				$substances    = '';
				$tradeName     = '';
				$concentration = '';
				$dosageForm    = '';
				$tiFreeText    = '';
				$tiMorning     = '';
				$tiLunch       = '';
				$tiEvening     = '';
				$tiNight       = '';
				$ingestUnit    = '';
				$information   = '';
				$reason        = '';
				$pzn_dbf_id    = '';
				
				
				$subCount = 0; // "substances counter"
				
				$pzn = ltrim( rtrim( $mrx->getAttribute('p') ) , ' 0' ); // " 00246 " => "246"
				
				$substanceArr = array();
				$concentArr = array();
				
				if ( strlen( $pzn ) && isset( $pznData[$pzn] )) {
					$data = $pznData[$pzn];
					$pzn_dbf_id   = $data['id'];
					$tradeName    = trim( $data['name'] );
					$substanceArr = $data['substances'];
					$concentArr   = $data['concentrations'];
					$subCount     = count( $substanceArr );
					$itemError = "";
					if(isset($data['error'])){
    					$itemError     = $data['error'];
					}
				}
				
				$str = trim( $mrx->getAttribute('a') );
				if ( strlen( $str ) ) {
					$tradeName = trim( $DeKbv_Bmp2->attribToClearText( $str ) );
				}
				
				if ( strlen( $tradeName ) > 50 ) {
					$tradeName = mb_substr ( $tradeName, 0, 48, 'UTF-8' ) . '...';
				}
				
				$substList = $mrx->getElementsByTagName('W');
				if ( $substList->length ) {
					$substanceArr = array();
					$concentArr = array();
					foreach ( $mrx->childNodes as $w ) {
						if ( 'W' === $w->nodeName ) {
							$subCount++;
							$str = trim( $w->getAttribute('w') );
							$str = $DeKbv_Bmp2->attribToClearText( $str );
							$str = str_replace( ' ', $nbsp, $str );
							array_push( $substanceArr, $str );
							$str = trim( $w->getAttribute('s') );
							$str = $DeKbv_Bmp2->attribToClearText( $str );
							$str = str_replace( ' ', $nbsp, $str );
							array_push( $concentArr, $str );
						}
					}
					unset($w);
				}
				
				foreach ( $substanceArr as &$str ) {
					if ( strlen( $str ) > 80 ) {
						$str = mb_substr ( $str, 0, 78, 'UTF-8' ) . '...';
					}
				}
				unset($str);
				foreach ( $concentArr as &$str ) {
					if ( strlen( $str ) > 11 ) {
						$str = mb_substr ( $str, 0, 11, 'UTF-8' );
					}
				}
				unset($str);
				
				if( count($substanceArr) > 3 ) {
					$substances    = 'Kombi-Präp.';
					$concentration = '';
				}
				else {
					$substances    = implode( "\n", $substanceArr );
					$concentration = implode( "\n", $concentArr );
				}
				
				// ATTENTION WHEN CHANGING LINE CONSUMPTION: do also adapt the
				// "foreach" after comment "note down the rows per section ..."!
				
				
				
			
				$str = trim( $mrx->getAttribute('fd') );
				if ( strlen($str) ) {
					$dosageForm = $DeKbv_Bmp2->attribToClearText( $str );
				}
				else {
					$str = trim( $mrx->getAttribute('f') );
					if ( strlen($str) ) {
						$dosageForm = isset( $codeToForm[$str] ) ? $codeToForm[$str] : '('.$str.')';
					}
					else {
						// ToDo - this should be found in the PZN data ...
						$dosageForm = '';
					}
				}
			
				if ( strlen( $dosageForm ) > 7 ) {
					$dosageForm = mb_substr ( $dosageForm, 0, 7, 'UTF-8' );
				}
			
				// "ti" - time of ingestion ...
				$str = trim( $mrx->getAttribute('t') );
				if ( strlen($str) ) {
					$tiFreeText = $DeKbv_Bmp2->attribToClearText( $str );
				}
				else {
					$tiFreeText = null;
					$str = trim( $mrx->getAttribute('m') );
					$tiMorning  = strlen($str) ? $str : '0';
					$str = trim( $mrx->getAttribute('d') );
					$tiLunch    = strlen($str) ? $str : '0';
					$str = trim( $mrx->getAttribute('v') );
					$tiEvening  = strlen($str) ? $str : '0';
					$str = trim( $mrx->getAttribute('h') );
					$tiNight    = strlen($str) ? $str : '0';
				}
			
				$str = trim( $mrx->getAttribute('dud') );
				if ( strlen($str) ) {
					$ingestUnit = $DeKbv_Bmp2->attribToClearText( $str );
				}
				else {
					$str = trim( $mrx->getAttribute('du') );
					if ( strlen($str) ) {
						$ingestUnit = isset( $codeToUnit[$str] ) ? $codeToUnit[$str] : $str;
					}
					else {
						$ingestUnit = '';
					}
				}
			
				$str = trim( $mrx->getAttribute('i') );
				if ( strlen($str) ) {
					$information = $DeKbv_Bmp2->attribToClearText( $str );
				}
				else {
					$information = '';
				}
			
				$str = trim( $mrx->getAttribute('r') );
				if ( strlen($str) ) {
					$reason = $DeKbv_Bmp2->attribToClearText( $str );
				}
				else {
					$reason = '';
				}
			
					
				$one_medi = array();
				
				$one_medi ['group_owner'] = $group_owner ; // what category this medi fits in
				$one_medi ['pzn'] = $pzn ;
				$one_medi ['dbf_id'] = $pzn_dbf_id ;
				$one_medi ['substances'] = $substances ;
				$one_medi ['tradeName'] = $tradeName;
				$one_medi ['concentration'] = $concentration;
				$one_medi ['dosageForm'] = $dosageForm;
				if ( $tiFreeText ) {
					$one_medi ['tiFreeText'] = $tiFreeText;
				}
				else {
					$one_medi ['tiFreeText'] = null;
					$one_medi ['tiMorning'] = $tiMorning;
					$one_medi ['tiLunch'] = $tiLunch;
					$one_medi ['tiEvening'] = $tiEvening;
					$one_medi ['tiNight'] = $tiNight;
				}
			
				$one_medi ['ingestUnit'] = $ingestUnit;
				$one_medi ['information'] = $information;
				$one_medi ['reason'] = $reason;
				
				$one_medi ['pzn_error'] = $itemError;
			
				array_push ($all_medis, $one_medi);
				
				
			}
		}
		
		//if we are to save also as json_encode
		/*
		$dataArray = $DeKbv_Bmp2->getArrayFromNode( 
				$DeKbv_Bmp2->getDataMatrixDOM(),
				array( 'multiple' => array('MP.S','MP.S.M','MP.S.X','MP.S.R') )
		);
		*/

		if ( ! empty($all_medis) ) {
			
			//our selected patienr
			
			
			
			$patientmaster = new PatientMaster();
			$patientinfo = $patientmaster->getMasterData($decid, 0);
			
			
			if ( stripos( $patientinfo['nice_name'] , $xml_patient_info ['first_name']) === false 
					|| stripos( $patientinfo['nice_name'] , $xml_patient_info ['last_name']) === false ) 
			{
				$medis_grid = "<div><span class='error'>". $this->translate("Patient names do not match") . " ( {$patientinfo['nice_name']} {$patientinfo['birthd']})";
				$medis_grid .= "<br/>XML Patient: ". $xml_patient_info ['last_name'] . ", " . $xml_patient_info ['first_name'] ." " . $xml_patient_info ['dob'] ;
				$medis_grid .= "</span>" ;
				$medis_grid .= "</div>\n";
			} else {
				$medis_grid = "<div>XML Patient: ". $xml_patient_info ['last_name'] . ", " . $xml_patient_info ['first_name'] ." " . $xml_patient_info ['dob'] ;
				$medis_grid .= "</div>\n";
			}
			
			
			$medication_groups = array();
			foreach($my_medication_groups as $row) {
				$medication_groups[$row] = $this->translate($row . " medication title");
			}
			
			$grid = new Pms_Grid($all_medis, 1, count( $all_medis ), "list_datamatrix_medications.html");
			$grid->patient_info = $xml_patient_info;
			$grid->medication_groups = $medication_groups;
				
			$medis_grid .= $grid->renderGrid();
			
			
			
			
			$result = array(
					"success"=> true,
					"status" => "",
					"message" => $datamatrix_lang["xml2array processed ok"],
					"medication_html_grid" => $medis_grid,
					"medication_json" => json_encode($dataArray['MP']),
			
			);
		} else {
			$result = array(
					"success"=> false,
					"status" => "",
					"message" => $datamatrix_lang["medication array is empty"],
						
			);
		}
		
		
		return $result;
	}


	
	// this is step 2 in datamatrix import
	// !! be aware i import what user is posting... i do not re-parse the xml... 
	// so you have the ability to use textinputs and allow the user to tweak the list if he wants 
	private function save_new_medis ( $post = array() ) {
		
		$data = $post['dataObj'];
		
		$ipid = $post['ipid'];
		
		$start_date = date("Y-m-d H:i:s", time());
		
		$datamatrix_lang = $this->translate('datamatrix_lang');
		
		if ( isset($data)  && ! empty ($data['dm_selected']) ) {
			
			//added like this maybe you want to change to $post[logininfo] if you make some cronjob import
			$clientid = ! empty($post['clientid']) ? $post['clientid'] : $this->logininfo->clientid; 
			$usertype = ! empty($post['usertype']) ? $post['usertype'] : $this->logininfo->usertype; 
			$userid = ! empty($post['userid']) ? $post['userid'] : $this->logininfo->userid; 
			
			$usersDetails = User::getUsersNiceName(array($userid));
			$user_name = $usersDetails[$userid]['nice_name'];
			
			//Medication acknowledge
			$acknowledge_module = false; //by default all can add/edit if they don't have this module activated
			
			if ( Modules::checkModulePrivileges("111", $clientid)) {
				
				$acknowledge_module = true;
				
				$approval_users = MedicationApprovalUsers::get_medication_approval_users($clientid, true);
				
				$change_users = MedicationChangeUsers::get_medication_change_users($clientid, true);
				
				if ( ! in_array($userid, $change_users) && ! in_array($userid, $approval_users) && $usertype != 'SA') {
					//this userid cannot add/modify the medis .. return now
					$allow_change = false;
					
					$result = array(
							"success"=> false,
							"status" => "acknowledge !allow_change",
							"message" => $this->translate('youdonothavepermission'),
								
					);
						
					return $result;
				
				} elseif ( in_array($userid, $approval_users)) {
					//this user has full edit/approve rights, so you can ignore the $acknowledge module
					$acknowledge_module = false;
					
				} else {
					//this userid is a SA or a $change_users
				}	
			}
		
			$all_medication = array();
			$all_patient_course = array();
			$master_isschmerzpumpe_cocktail_id = 0; // this will keep the pumpe_id, because we create only one for all medis
			$master_isschmerzpumpe_cocktail_id_alt = 0;
			
			//ISPC-2833 Ancuta 26.02.2021
			$master_ispumpe_pumpe_id = 0; // this will keep the pumpe_id, because we create only one for all medis
			$master_ispumpe_pumpe_id_alt = 0;
			//-- 
			
			foreach ($data['dm_selected'] as $selected) {
				
				//reset
				$medication =  array();
				
				$medication_master_id =
				$PatientDrugPlan_id = 
				$PatientDrugPlanAlt_id = 
				$PatientDrugPlanExtra_id = 
				$PatientDrugPlanExtraAlt_id = 0;
				
				$comments = '';
				
				//ISPC-2833 Ancuta 26.02.2021 - added ispumpe and pumpe_id
				//this are the groups we now have
				$actual = 
				$isbedarfs =
				$iscrisis = 
				$isivmed =
				$isschmerzpumpe =
				$cocktailid =
				$ispumpe =
				$pumpe_id =
				$treatment_care =
				$isnutrition =
				$scheduled =
				$has_interval= 0;
				
				//set the group
				$group = $data['dm_medication'][$selected]['group'];
				${$group} = 1;
				
				//create one-time NEW cocktail pump
				if ( $group == 'isschmerzpumpe' && $master_isschmerzpumpe_cocktail_id == 0 ) {
                   
                    $pdpc_obj = new PatientDrugPlanCocktails();
                    $pdpc_obj->userid = $userid;
                    $pdpc_obj->clientid = $clientid;
                    $pdpc_obj->ipid = $ipid;
                    $pdpc_obj->description = "Datamatrix XML Daten";
                    $pdpc_obj->bolus = null;
                    $pdpc_obj->max_bolus = null;
                    $pdpc_obj->flussrate = null;
                    $pdpc_obj->sperrzeit = null;
                    $pdpc_obj->pumpe_type = "pca";
                    $pdpc_obj->pumpe_medication_type = null;
                    $pdpc_obj->carrier_solution = null;
                    $pdpc_obj->save();
                    $master_isschmerzpumpe_cocktail_id = $pdpc_obj->id;
                    
                    
                    if ( $acknowledge_module ) {
                    	// insert in cocktail alt
                    	$pdpac_obj = new PatientDrugPlanAltCocktails();
                    	$pdpac_obj->ipid = $ipid;
                    	$pdpac_obj->userid = $userid;
                    	$pdpac_obj->clientid = $clientid;
                    	$pdpac_obj->drugplan_cocktailid = $master_isschmerzpumpe_cocktail_id;
                    	$pdpac_obj->description = "Datamatrix XML Daten";
                    	$pdpac_obj->bolus = null;
                    	$pdpac_obj->max_bolus = null;
                    	$pdpac_obj->flussrate = null;
                    	$pdpac_obj->sperrzeit = null;
                    	$pdpac_obj->pumpe_type = "pca";
                    	$pdpac_obj->pumpe_medication_type = null;
                    	$pdpac_obj->carrier_solution = null;
                    	$pdpac_obj->status = "new";
                    	$pdpac_obj->save();
                    	
                    	$master_isschmerzpumpe_cocktail_id_alt = $pdpac_obj->id;
                    }
                    
                    
				}
				
				//ISPC-2833 Ancuta 26.02.2021
				if ( $group == 'ispumpe' && $master_ispumpe_pumpe_id == 0 ) {
                   
                    $pdpc_obj = new PatientDrugPlanPumpe();
                    $pdpc_obj->userid = $userid;
                    $pdpc_obj->clientid = $clientid;
                    $pdpc_obj->ipid = $ipid;
                    $pdpc_obj->save();
                    $master_ispumpe_pumpe_id = $pdpc_obj->id;
                    
                    if ( $acknowledge_module ) {
                    	// insert in cocktail alt
                    	$pdpac_obj = new PatientDrugPlanPumpeAlt();
                    	$pdpac_obj->ipid = $ipid;
                    	$pdpac_obj->userid = $userid;
                    	$pdpac_obj->clientid = $clientid;
                    	$pdpac_obj->drugplan_pumpe_id = $master_ispumpe_pumpe_id;
                    	$pdpac_obj->status = "new";
                    	$pdpac_obj->save();
                    	
                    	$master_ispumpe_pumpe_id_alt = $pdpac_obj->id;
                    }
				}
				//--
				
				//@todo populate this array with more infos from the xml
				$medication = array(
						
						'name' => $data['dm_medication'][$selected]['tradeName'],
						'pzn' => $data['dm_medication'][$selected]['pzn'],
						'source' => 'datamatrix',
						'dbf_id' => $data['dm_medication'][$selected]['dbfid'],
						'description' => "",
						'package_size' => "",
						'amount_unit' => "",
						'price' => "",
						'manufacturer' => "",
						'package_amount' => "",
						'comment' => "",
						'clientid' => $clientid,
				
				);
				
				//depending on the group type, medication is inserted in 3 different tables
				if ( $group == 'treatment_care' ) {
					
					$m_obj = new MedicationTreatmentCare();
					
				}
				elseif ( $group == 'isnutrition' ) {

					$m_obj = new Nutrition();
					
				}
				else {
					//default save to Medication table
					$m_obj = new Medication();
				}
				
				$medication_master_id = $m_obj->set_new_record( $medication );
				
				array_push($all_medication, $medication);
				
				//leave this if here... fn set_new_record is a proxy
				if ( $medication_master_id > 0 ) {
				
					//if dossage is not array, we have a text-input
					$dosage = '';
					if ( ! is_array( $data['dm_medication'][$selected]['dosage'])) {
						$dosage = $data['dm_medication'][$selected]['dosage'];
					}
										
					$pdp_obj = new PatientDrugPlan();
					$pdp_obj->ipid = $ipid;
					$pdp_obj->dosage = $dosage;
					$pdp_obj->medication_master_id = $medication_master_id;
					$pdp_obj->isbedarfs = $isbedarfs;
					$pdp_obj->iscrisis = $iscrisis;
					$pdp_obj->isivmed = $isivmed;
					$pdp_obj->isschmerzpumpe = $isschmerzpumpe;
					if ($isschmerzpumpe && $master_isschmerzpumpe_cocktail_id) {
						$pdp_obj->cocktailid = $master_isschmerzpumpe_cocktail_id;
					} else {
						$pdp_obj->cocktailid = 0;
					}
					
					
					//ISPC-2833 Ancuta 26.02.2021
					$pdp_obj->ispumpe = $ispumpe;
					if ($ispumpe && $master_ispumpe_pumpe_id) {
					    $pdp_obj->pumpe_id = $master_ispumpe_pumpe_id;
					} else {
						$pdp_obj->pumpe_id = 0;
					}
					// -- 
					
					
					$pdp_obj->treatment_care = $treatment_care;
					$pdp_obj->isnutrition = $isnutrition;
					$pdp_obj->scheduled = $scheduled;
					$pdp_obj->has_interval = $has_interval;
					$pdp_obj->comments = $comments;
					$pdp_obj->medication_change = date('Y-m-d 00:00:00');
					$pdp_obj->save();
					$PatientDrugPlan_id = $pdp_obj->id;
					
					if ( $acknowledge_module ) {
						$pdpa_obj = new PatientDrugPlanAlt();
						$pdpa_obj->ipid = $ipid;
						$pdpa_obj->drugplan_id = $PatientDrugPlan_id;
						$pdpa_obj->dosage = $dosage;
						$pdpa_obj->medication_master_id = $medication_master_id;
						$pdpa_obj->isbedarfs = $isbedarfs;
						$pdpa_obj->iscrisis = $iscrisis;
						$pdpa_obj->isivmed = $isivmed;
						$pdpa_obj->isschmerzpumpe = $isschmerzpumpe;
						if ($isschmerzpumpe && $master_isschmerzpumpe_cocktail_id) {
						    $pdpa_obj->cocktailid = $master_isschmerzpumpe_cocktail_id;
						} else {
						    $pdpa_obj->cocktailid = 0;
						}
						//ISPC-2833 Ancuta 26.02.2021
						$pdpa_obj->ispumpe = $ispumpe;
						if ($ispumpe && $master_ispumpe_pumpe_id) {
						    $pdpa_obj->pumpe_id = $master_ispumpe_pumpe_id;
						} else {
						    $pdpa_obj->pumpe_id = 0;
						}
						// --
						$pdpa_obj->treatment_care = $treatment_care;
						$pdpa_obj->isnutrition = $isnutrition;
						$pdpa_obj->verordnetvon = $post['verordnetvon'][$key];
						$pdpa_obj->comments = $comments;
						$pdpa_obj->medication_change = date('Y-m-d 00:00:00');
						$pdpa_obj->status = "new";
						$pdpa_obj->save();
						$PatientDrugPlanAlt_id = $pdpa_obj->id;
					}
					
					
					
					//now the extra fields
					$pdpe_obj = new PatientDrugPlanExtra();
					$pdpe_obj->ipid = $ipid;
					$pdpe_obj->drugplan_id = $PatientDrugPlan_id;
					$pdpe_obj->drug =  $data['dm_medication'][$selected]['substances'];
// 					$pdpe_obj->unit =  $data['dm_medication'][$selected]['substances'];
// 					$pdpe_obj->type =  $data['dm_medication'][$selected]['substances'];
// 					$pdpe_obj->indication =  $data['dm_medication'][$selected]['substances'];
// 					$pdpe_obj->importance =  $data['dm_medication'][$selected]['substances'];
// 					$pdpe_obj->dosage_form =  $data['dm_medication'][$selected]['substances'];
// 					$pdpe_obj->concentration =  $data['dm_medication'][$selected]['substances'];
					$pdpe_obj->save();
					$PatientDrugPlanExtra_id = $pdpe_obj->id;
					
					if ( $acknowledge_module ) {
						$pdpea_obj = new PatientDrugPlanExtraAlt();
						$pdpea_obj->ipid = $ipid;
						$pdpea_obj->drugplan_id_alt = $PatientDrugPlanAlt_id;
						$pdpea_obj->drugplan_id = $PatientDrugPlan_id;
						$pdpea_obj->drug = $data['dm_medication'][$selected]['substances'];
// 						$pdpea_obj->unit = $post['unit'][$key];
// 						$pdpea_obj->type = $post['type'][$key];
// 						$pdpea_obj->indication = $post['indication'][$key];
// 						$pdpea_obj->importance = $post['importance'][$key];
// 						$pdpea_obj->dosage_form = $post['dosage_form'][$key];
// 						$pdpea_obj->concentration= $post['concentration'][$key];
						$pdpea_obj->save();
						$PatientDrugPlanExtraAlt_id = $pdpea_obj->id;
					}
					
					//dosages as multiple hours
					if ( is_array( $data['dm_medication'][$selected]['dosage'])) {
						
						$dosages_interval_array =  array();
						$dosages_interval_alt_array =  array();
						
						foreach ( $data['dm_medication'][$selected]['dosage'] as $k_time=>$v_dosage)
						{
							$k_time = str_replace("_", ":", $k_time); // i've replaced in js the hh:mm with hh_mm 
							
							if ( trim($v_dosage) != '' ) {
								array_push($dosages_interval_array, array(
										"ipid" => $ipid,
										"drugplan_id" => $PatientDrugPlan_id,
										"dosage" => $v_dosage,
										"dosage_time_interval" => $k_time.":00",
								));
								
								if ( $acknowledge_module ) {
									array_push($dosages_interval_alt_array, array(
											"ipid" => $ipid,
											"drugplan_id_alt" => $PatientDrugPlanAlt_id,
											"drugplan_id" => $PatientDrugPlan_id,
											"dosage" => $v_dosage,
											"dosage_time_interval" => $k_time.":00",
									));
								}
								
							}
						}
						
						if ( ! empty($dosages_interval_array)) {
							$collection = new Doctrine_Collection('PatientDrugPlanDosage');
							$collection->fromArray($dosages_interval_array);
							$collection->save();
							
						}
						
						if ( ! empty($dosages_interval_alt_array)) {
							$collection = new Doctrine_Collection('PatientDrugPlanDosageAlt');
							$collection->fromArray($dosages_interval_alt_array);
							$collection->save();
								
						}
					}
					
					/**
					 * patient course
					 * THIS could have been a Event in Triggerliste if you would have started from the begining with it 
					 * there is allready one checked...  
					 * insert = ispc_website/trigger/edittrigger?id=34&frmid=12&event=2
					 * update = ispc_website/trigger/edittrigger?id=34&frmid=12&event=1
					 */
					if ( $acknowledge_module ) {
						
						$prefix = 'OHNE FREIGABE: ' .$this->translate($group . " medication title"). ": ";
						$tabname = 'patient_drugplan_alt_new_med';
						
						// SEND MESSAGE
						$text  = "";
						$text .= "Patient %patient_name% \n ";
						$text .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. \n ";
						$text .= "neue ". $this->translate($group . " medication title") . ": ".  $medication['name']." \n";
						$mess = Messages::medication_acknowledge_messages($ipid, $text);
						
						// CREATE TODO
						$text_todo  = "";
						$text_todo .= "Patient %patient_name% <br/>";
						$text_todo .= "Benutzer ".$user_name." änderte eine Medikation. Bitte bestätigen Sie dies. <br/>";
						$text_todo .= "neue ". $this->translate($group . " medication title") . ": ".  $medication['name']." <br/>";
						$todos = Messages::medication_acknowledge_todo($ipid, $text_todo, $PatientDrugPlan_id, $PatientDrugPlanAlt_id);
						
						
					} else {
						
						$prefix = $this->translate($group . " medication title"). ": ";
						$tabname = 'patient_drugplan';
						
					}
					
					$shortcut =  PatientDrugPlan::getGroupCourseType( $group );
					$course_title  = $prefix;
					$course_title .= $medication['name'];
					$course_title .= !empty($medication['comment']) ? " | ". $medication['comment'] : "";	
							
					$pc_array = array(
							"ipid" => $ipid,
							"course_date" => $start_date,
							"course_type" => $shortcut,
							"tabname" => $tabname,
							"recordid" => $PatientDrugPlan_id,
							"course_title" => $course_title,
							"user_id" => $userid,
					);					
					
					$pc_obj = new PatientCourse();
// 					$pc_obj->triggerformid = 0; // skip triggers
					$pc_obj->set_new_record($pc_array);				
					
					
					array_push($all_patient_course, $pc_array);
					
				}
			}


			if ( ! empty($all_medication)) {
				
// 				//now save to patient course
// 				if ( ! empty($all_patient_course)) {
// 					$collection = new Doctrine_Collection('PatientCourse');
// 					$collection->fromArray($all_patient_course);
// 					$collection->save();
// 				}
				
				
				//save the xml
				$pbi_obj = new PatientDatamatrixImport();
				$pbi_obj->set_new_record(array(
						'ipid' => $ipid,
						'tabname' => 'medication_new',
						'datamatrix_xml' => $post['datamatrix_xml'],
						'datamatrix_array' => null //maybe will change later
				));
				
				$result = array(
						"success"=> true,
						"status" => "",
						"message" => $datamatrix_lang["all went ok, please wait for the medication page"],
							
				);
				
			} else {
				$result = array(
						"success"=> false,
						"status" => "",
						"message" => $datamatrix_lang["failed to import the selected medis"],
							
				);
			}
				
		}  else {
			$result = array(
					"success"=> false,
					"status" => "",
					"message" => $datamatrix_lang["no medication was selected"],
						
			);
		}

		return $result;
		
	}

	
	
}

