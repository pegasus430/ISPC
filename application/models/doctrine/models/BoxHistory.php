<?php

	Doctrine_Manager::getInstance()->bindComponent('BoxHistory', 'MDAT');

	class BoxHistory extends BaseBoxHistory {

		public function getHistoryId($hid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('BoxHistory')
				->where("id='" . $hid . "'");
			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}

		public function getHistoryByPatient($ipid = '' , $formid = null)
		{
		    if (empty($ipid)) {
		        return; //fail-safe
		    }
		    
			$drop = Doctrine_Query::create()
				->select("*")
				->from('BoxHistory')
				->where('ipid = ?', $ipid);
			
			if ( ! empty($formid)) {
			    $drop->andWhere("formid = ? ", $formid);
			}
			
			return $drop->fetchArray();

			
		}

		
		
		public function CreateOne(array $data = array(), $hydrationMode = null)
		{
			$entity = $this->getTable()->create(array());
			$entity->fromArray($data); //update
			$entity->save(); //at least one field must be dirty in order to persist
		
			return $entity;
		}
		
		
		/**
		 * @see PatientController::patientboxhistoryAction()
		 * 
		 * @param string $ipid
		 * @param string $formid
		 */
		public function fetch_patient_box_history ($ipid = '' , $formid = null)
		{

		    if (empty($ipid)) {
		        return; //fail-safe
		    }
		    
		    
		    //messedup boxes.. tryiong to fix
		    
		    
		    $history = $this->getHistoryByPatient($ipid, $formid);
		    
		    $ContactPersonMaster_array = array(); // this should/will be populated only on demand, check grow6new
		    	
		    if(count($history) > 0) {
		        $out = $history;
		    } else {
		        
		        $out = "No log data";
		        return;
		    }
		    
		    $ds = new ExtraForms();
		    $dsarr = $ds->getExtraFormsDetails();
		    
		    //$this->grid->extraforms = $dsarr;
		    
		    $stam = new Stammdatenerweitert();
		    $familienstands = $stam->getFamilienstandfun();
		    $stastszugehorigkeits = $stam->getStastszugehorigkeitfun();
		    $vigilanzs = $stam->getVigilanzfun();
		    $orientierungs = $stam->getOrientierungfun();
		    $ernahrungs = $stam->getErnahrungfun();
		    $ausscheidungs = $stam->getAusscheidungfun();
		    $kunstliches = $stam->getKunstlichefun();
		    $radiobuttonoptions = $stam->getRadioOptionsfun();
		    $grow_map = array("grow1" => "Patient lebt", "grow4" => "Versorgung", "grow5" => "Mobilität",
		        
		        "grow6" => "Patientenverfügung",
		        "grow6new" => "ACP",// this was introduced with ISPC-2056
		        
		        "grow171120" => "Orientierung II",// this was introduced with ISPC-2070
		        "grow54" => "Orientierung II",// this was introduced with ISPC-2070
		        
		        "grow171121" => "Mobilität II",// this was introduced with ISPC-2070
		        
		        "grow55" => "Mobilität II",// this was introduced with ISPC-2070
		        	
		        "grow7" => "Pflegestufe", "grow8" => "Religionszugehörigkeit", "grow16" => "Familienstand", "grow17" => "Staatszugehörigkeit",
		        "grow18" => "Vigilanz", "grow19" => "Orientierung", "grow20" => "Ernährung", "grow21" => "Ausscheidung", "grow22" => "Künstliche Ausgänge"
		        , "grow24" => "Hilfsmittel", "grow25" => "Wunsch des Patienten", "grow26" => "Apotheke",
		        "grow35" => "Patient History", "grow36" => "Vollversorgung History", "grow37" => "Vorausschauende Therapieplanung", "grow38" => "", "grow39" => "", "grow40" => "", "grow41" => ""
		        , "grow42" => "", "grow43" => "Tourenplanung", "grow44" => "", "grow45" => "", "grow46" => "", "grow47" => "",
		        "grow48" => "", "grow49" => "", "grow50" => "", "grow51" => "", "grow52" => "Keimbesiedelung", "grow53" => "",
		        "grow56" => "Erwerbssituation", "grow11" => "SAPV Verordnung", "grow70" => "Ipos Patient Settings",
		    	"grow100" => "Künstliche Zugänge-Ausgänge", //TODO-3433 Carmen 21.09.2020
		    	
		    );
		    
		    
		    ;
		    
		    $userdata = User::getUsersNiceName(array_column($history, 'create_user'));
		    
		    
		    foreach($history as $k => $v)
		    {
		        $history[$k]['fname'] = $userdata[$v['create_user']]['first_name'];
		        $history[$k]['lname'] = $userdata[$v['create_user']]['last_name'];
		        
		        $history[$k]['fieldname'] = ucfirst($v['fieldname']);
		        $history[$k]['create_date'] = date("d.m.Y H:i", strtotime($v['create_date']));
		        $history[$k]['fieldname'] = $grow_map[$v['formid']];
		    
		        foreach($dsarr as $val)
		        {
		            $formid = explode("grow", $v['formid']);
		            if($formid[1] == $val['id'])
		            {
		                $history[$k]['boxname'] = $val['formname'];
		            }
		    
		            if($v['formid'] == "grow8")
		            {
		                $rl = new PatientReligions();
		                $religions = $rl->getReligionsNames();
		    
		                $history[$k]['fieldvalue'] = $religions[$v['fieldvalue']];
		                $history[$k]['fieldname'] = $grow_map[$v['formid']];
		            }
		    
		            $wunsch = array("1" => "Zu Hause bleiben können", "2" => "kein Krankenhaus", "3" => "Autonomie", "4" => "Leidenslinderung", "5" => "Symptomlinderung",
		                "6" => "mehr Kraft", "7" => "wieder aufstehen können", "8" => "noch eine Reise machen",
		                "9" => "In Ruhe gelassen werden", "10" => "Keine Angabe", "11" => "Frage nach aktiver Sterbehilfe",
		                "12" => "Lebensbeendigung", "13" => "Expliziter Wunsch");
		        }
		    
		        
		        
		        switch ($v['formid']) {
		            
		            
		            case "grow24" :
		                $hilfsmittel = array('1' => "O2", '2' => "Toilettensitz", '3' => "Pflegebett", '4' => "Rollstuhl", "5" => "Rollator", "6" => "Nachtstuhl", "7" => "Wechseldruckmatratze");
		                $values = explode(",", $v['fieldvalue']);
		                
		                if($v['fieldname'] == "pumps")
		                {
		                    if($v['fieldvalue'] == 0)
		                    {
		                        $history[$k]['fieldvalue'] = "Pumpe entfernt";
		                    }
		                    else
		                    {
		                        $history[$k]['fieldvalue'] = "Pumpe hinzugefügt";
		                    }
		                }
		                else
		                {
		                    $x = explode("-", $v['fieldvalue']);
		                    if($x[1] == 0)
		                    {
		                        $history[$k]['fieldvalue'] = $hilfsmittel[$x[0]] . " entfernt";
		                    }
		                    else
		                    {
		                        $history[$k]['fieldvalue'] = $hilfsmittel[$x[0]] . " hinzugefügt";
		                    }
		                }
		                
		                $f_array = '';
		                break;
		                
		                
		            case "grow25" :
		                $wunsch = array("1" => "Zu Hause bleiben können", "2" => "kein Krankenhaus", "3" => "Autonomie", "4" => "Leidenslinderung", "5" => "Symptomlinderung",
		                    "6" => "mehr Kraft", "7" => "wieder aufstehen können", "8" => "noch eine Reise machen",
		                    "9" => "In Ruhe gelassen werden", "10" => "Keine Angabe", "11" => "Frage nach aktiver Sterbehilfe",
		                    "12" => "Lebensbeendigung", "13" => "Expliziter Wunsch");
		                
		                $x = explode("-", $v['fieldvalue']);
		                if($x[1] == 0)
		                {
		                    $history[$k]['fieldvalue'] = $wunsch[$x[0]] . " entfernt";
		                }
		                else
		                {
		                    $history[$k]['fieldvalue'] = $wunsch[$x[0]] . " hinzugefügt";
		                }
		                
		                $f_array = '';
		                break;

		                
		            case "grow18" :
		                $history[$k]['fieldvalue'] = $vigilanzs[$v['fieldvalue']];
		                break;
		                
		                
		            case "grow19" :
		                if($v['fieldname'] != "orientierung")
		                {
		                    $array_flds = array("horprobleme" => "Hörprobleme", "kognitiv" => "kognitiv", "sprachlich" => "sprachlich");
		                    if($v['fieldvalue'] == 0)
		                    {
		                        $history[$k]['fieldvalue'] = $array_flds[$v['fieldname']] . " entfernt";
		                    }
		                    else
		                    {
		                        $history[$k]['fieldvalue'] = $array_flds[$v['fieldname']] . " hinzugefügt";
		                    }
		                }
		                else
		                {
		                    $x = explode("-", $v['fieldvalue']);
		                    if($x[1] == 0)
		                    {
		                        $history[$k]['fieldvalue'] = $orientierungs[$x[0]] . " entfernt";
		                    }
		                    else
		                    {
		                        $history[$k]['fieldvalue'] = $orientierungs[$x[0]] . " hinzugefügt";
		                    }
		                }
		                
		                $f_array = '';
		                break;
		                
		                
		            case "grow22" :
		                if($v['fieldname'] == "kunstlichemore"){
		                    $history[$k]['fieldvalue'] = $v['fieldvalue'];
		                } else {
		                    $x = explode("-", $v['fieldvalue']);
		                    if($x[1] == 0)
		                    {
		                        $history[$k]['fieldvalue'] = $kunstliches[$x[0]] . " entfernt";
		                    }
		                    else
		                    {
		                        $history[$k]['fieldvalue'] = $kunstliches[$x[0]] . " hinzugefügt";
		                    }
		                }
		                break;
		                
		                
		            case "grow7" :

		                if($v['fieldname'] != "stage")
		                {
		                    if($v['fieldvalue'] == 0)
		                    {
		                        $history[$k]['fieldvalue'] = " entfernt";
		                    }
		                    else
		                    {
		                        $history[$k]['fieldvalue'] = " hinzugefügt";
		                    }
		                }
		                else
		                {
		                    $history[$k]['fieldvalue'] = "Stage: " . $v['fieldvalue'];
		                }
		                 
		                if (date("Y", strtotime($v['create_date'])) > 2016 ){
		                    $history[$k]['fieldname'] = "Pflegegrade";
		                }
		                break;

		                
	                case "grow6new" :
		            case "grow6" :

		                $_field_json	= json_decode($v['fieldname'], true);
		                
		                if ($_field_json === null
		                    && json_last_error() !== JSON_ERROR_NONE) 
		                {
		                    //old box
	                        $fld_array_grow6 = array("living_will" => "Vorhanden", "living_will_deposited" => "wo hinterlegt", "living_will_from" => "von wann");
	                        
	                        if($v['fieldname'] == "living_will_from")
	                        {
	                            $history[$k]['fieldvalue'] = $fld_array_grow6[$v['fieldname']] . ": " . date("d.m.Y", strtotime($v['fieldvalue']));
	                        }
	                        else
	                        {
	                        
	                            if($v['fieldvalue'] == 0)
	                            {
	                                $history[$k]['fieldvalue'] = $fld_array_grow6[$v['fieldname']] . " entfernt";
	                            }
	                            else
	                            {
	                                $history[$k]['fieldvalue'] = $fld_array_grow6[$v['fieldname']] . " hinzugefügt";
	                            }
	                        }
		                    
		                } else {
		                    //new box
		                    // this new was introduced with ISPC-2056
		                    $_acp_box_lang	= $this->translate('acp_box_lang');
		                    $_field_json	= json_decode($v['fieldname'], true);
		                    $_division_tab	= $_field_json['division_tab'];
		                    $_fieldname		= $_field_json['fieldname'];
		                    
		                     
		                    $history[$k]['fieldname'] = 'ACP '. $_acp_box_lang[$_division_tab];
		                     
		                    switch ($_fieldname) {
		                    
		                        case "active" :
		                            $history[$k]['fieldvalue'] = $_acp_box_lang[$_fieldname] .": " ;
		                            $history[$k]['fieldvalue'] .= ($v['fieldvalue'] == 'no') ? " entfernt" :" hinzugefügt";
		                            break;
		                             
		                        case "contactperson_master_id":
		                             
		                            if ( empty ($ContactPersonMaster_array)) {
		                                $cpm_obj = new ContactPersonMaster();
		                                $cpm_data = $cpm_obj->getPatientContact($ipid, false);
		                                foreach ($cpm_data as $row){
		                                    $ContactPersonMaster_array[$row['id']] = $row;
		                                }
		                                ContactPersonMaster::beautifyName($ContactPersonMaster_array);
		                            }
		                             
		                            $history[$k]['fieldvalue'] = $_acp_box_lang[$_fieldname] . ": ";
		                            $history[$k]['fieldvalue'] .= $ContactPersonMaster_array[$v['fieldvalue']] ['nice_name'];
		                            break;
		                             
		                        case "comments" :
		                            $history[$k]['fieldvalue'] = $_acp_box_lang[$_fieldname] . ": ";
		                            $history[$k]['fieldvalue'] .= $v['fieldvalue'];
		                            break;
		                             
		                    }
		                }
		                
		                
		                
		                break;
		                
		                
		                
		                
		            case "grow171120" :
		            case "grow54" :
		                $PatientOrientation_lang = array_merge(PatientOrientation::getDefaultOrientation(), PatientOrientation::getDefaultCommunicationRestricted() );
		                $history[$k]['fieldvalue'] = isset($PatientOrientation_lang[$v['fieldname']]) ? $PatientOrientation_lang[$v['fieldname']] : $v['fieldname'];
		                $history[$k]['fieldvalue'] .= ($v['fieldvalue'] == '0') ? " entfernt" :" hinzugefügt";
		                break;
		                
		                
		                
		            case "grow171121" :
		            case "grow55" :
		                if ( ! isset($PatientMobility_lang)) {
		                    $pobj = new PatientMobility2();
		                    $PatientMobility_lang = $pobj->getEnumValuesDefaults();
		                }
		                
		                $history[$k]['fieldvalue'] = isset($PatientMobility_lang[$v['fieldname']]) ? $PatientMobility_lang[$v['fieldname']] : $v['fieldname'];
		                $history[$k]['fieldvalue'] .= ($v['fieldvalue'] == '0') ? " entfernt" :" hinzugefügt";
		                break;
		                
		                
		                
		            case "grow5" :
		                $fld_array = array("wechseldruckmatraze" => "Wechseldruckmatratze",
		                    "nachtstuhl" => "Nachtstuhl",
		                    "goable" => "gehfähig",
		                    "wheelchair" => "Rollstuhl",
		                    "walker" => "Rollator",
		                    "bed" => "Bett");
		                
		                if($v['fieldname'] != "wheelchairmore" && $v['fieldname'] != "walkermore" && $v['fieldname'] != "bedmore" && $v['fieldname'] != "wechseldruckmatrazemore" && $v['fieldname'] != "goablemore" && $v['fieldname'] != "nachtstuhlmore")
		                {
		                    if($v['fieldvalue'] == 0)
		                    {
		                        $history[$k]['fieldvalue'] = $fld_array[$v['fieldname']] . " entfernt";
		                    }
		                    else
		                    {
		                        $history[$k]['fieldvalue'] = $fld_array[$v['fieldname']] . " hinzugefügt";
		                    }
		                }
		                else
		                {
		                    $history[$k]['fieldvalue'] = ucfirst($v['fieldname']) . ": " . $v['fieldvalue'];
		                }
		                break;
		                
		                
		                
		            case "grow43" :
		                $history[$k]['fieldvalue'] = ucfirst($this->translate($v['fieldname'])) . ": " . $v['fieldvalue'];
		                break;
		                
		                
		                
		            case "grow4" :
		                $valTranslate = PatientSupply::getCbValuesArray();
		                
		                if($v['fieldvalue'] == 0)
		                {
		                    $history[$k]['fieldvalue'] = $valTranslate[$v['fieldname']] . " entfernt";
		                }
		                else
		                {
		                    $history[$k]['fieldvalue'] = $valTranslate[$v['fieldname']] . " hinzugefügt";
		                }
		                break;
		                
		                
		            case "grow21" :
		                if($v['fieldname'] != "dk")
		                {
		                    $x = explode("-", $v['fieldvalue']);
		                    if($x[1] == 0)
		                    {
		                        $history[$k]['fieldvalue'] = $ausscheidungs[$x[0]] . " entfernt";
		                    }
		                    else
		                    {
		                        $history[$k]['fieldvalue'] = $ausscheidungs[$x[0]] . " hinzugefügt";
		                    }
		                }
		                else
		                {
		                    if($v['fieldvalue'] == 0)
		                    {
		                        $history[$k]['fieldvalue'] = "DK entfernt";
		                    }
		                    else
		                    {
		                        $history[$k]['fieldvalue'] = "DK hinzugefügt";
		                    }
		                }
		                break;
		                
		                
		            case "grow17" :
		                if($v['fieldname'] == "stastszugehorigkeit")
		                {
		                    $history[$k]['fieldvalue'] =$this->translate('stastszugehorigkeit'). ": ".$stastszugehorigkeits[$v['fieldvalue']];
		                }
		                elseif($v['fieldname'] == "2ndstastszugehorigkeit")
		                {
		                    $history[$k]['fieldvalue'] =$this->translate('2ndstastszugehorigkeit'). ": ".$stastszugehorigkeits[$v['fieldvalue']];
		                }
		                elseif($v['fieldname'] == "dolmetscher")
		                {
		                    $history[$k]['fieldvalue'] = $this->translate('dolmetscher'). ": ". $v['fieldvalue'];
		                }
		                else
		                {
		                    $history[$k]['fieldvalue'] = $v['fieldvalue'];
		                }
		                break;
		                
		                
		            case "grow20" :
		                if($v['fieldname'] == "ernahrung")
		                {
		                    $x = explode("-", $v['fieldvalue']);
		                    if($x[1] == 0)
		                    {
		                        $history[$k]['fieldvalue'] = $ernahrungs[$x[0]] . " entfernt";
		                    }
		                    else
		                    {
		                        $history[$k]['fieldvalue'] = $ernahrungs[$x[0]] . " hinzugefügt";
		                    }
		                    $f_array = '';
		                }
		                else
		                {
		                    if($v['fieldname'] != "pegmore" && $v['fieldname'] != "portmore")
		                    {
		                        if($v['fieldvalue'] == 0)
		                        {
		                            $arr_flds = array("peg" => "PEG", "port" => "Port", "zvk" => "ZVK", "magensonde" => "Magensonde");
		                            $history[$k]['fieldvalue'] = $arr_flds[$v['fieldname']] . " entfernt";
		                        }
		                        else
		                        {
		                            $history[$k]['fieldvalue'] = $arr_flds[$v['fieldname']] . " hinzugefügt";
		                        }
		                    }
		                    else
		                    {
		                        if($v['fieldname'] == "pegmore")
		                        {
		                            $history[$k]['fieldvalue'] = "PEG Ablauf: " . $v['fieldvalue'];
		                        }
		                        elseif($v['fieldname'] == "portmore")
		                        {
		                            $history[$k]['fieldvalue'] = "PORT Ablauf: " . $v['fieldvalue'];
		                        }
		                    }
		                }
		                break;
		                
		                
		                
		            case "grow16" :
		                $history[$k]['fieldvalue'] = $familienstands[$v['fieldvalue']];
		                $history[$k]['fieldname'] = $grow_map[$v['formid']];
		                break;
		                
		                
		                
		            case "grow1" :
		                $arr_flds = PatientLives::getCbValuesArray();
		                
		                
		                if($v['fieldvalue'] == 0)
		                {
		                    $history[$k]['fieldvalue'] = $arr_flds[$v['fieldname']] . " entfernt";
		                }
		                else
		                {
		                    $history[$k]['fieldvalue'] = $arr_flds[$v['fieldname']] . " hinzugefügt";
		                }
		                break;
		                
		                
		                
		            case "grow52" :
		                if($v['fieldname'] == "germination_cbox" && $v['fieldvalue'] == 0)
		                {
		                    $history[$k]['fieldvalue'] = "Keimbesiedelung entfernt";
		                }
		                elseif($v['fieldname'] == "germination_cbox" && $v['fieldvalue'] == 1)
		                {
		                    $history[$k]['fieldvalue'] = "Keimbesiedelung hinzugefügt";
		                }
		                elseif($v['fieldname'] == "iso_cbox" && $v['fieldvalue'] == 0)
		                {
		                    $history[$k]['fieldvalue'] = "Isolationspflichtig entfernt";
		                }
		                elseif($v['fieldname'] == "iso_cbox" && $v['fieldvalue'] == 1)
		                {
		                    $history[$k]['fieldvalue'] = "Isolationspflichtig hinzugefügt";
		                }
		           
		                break;
		                
		                
		                
		            case "grow37" :
		                $arr_flds = PatientTherapieplanung::getCbValuesArray();
		                
		                if ($v['fieldname'] == "freetext") {
		                    $history[$k]['fieldvalue'] = $v['fieldvalue'];
		                    
		                } else {
    		                if ($v['fieldvalue'] == 0) {
    		                    $history[$k]['fieldvalue'] = $arr_flds[$v['fieldname']] . " entfernt";
    		                } else  {
    		                    $history[$k]['fieldvalue'] = $arr_flds[$v['fieldname']] . " hinzugefügt";
    		                }
		                }
		                break;
	                
		                
	                case "grow56" :
	                    
	                    switch ($v['fieldname']) {
	                        
	                        case "status":
	                            
	                            $arr_flds = PatientEmploymentSituation::getStatusValuesArray();
	                            
	                            $x = explode("-", $v['fieldvalue']);
	                            
	                            if($x[1] == 0) {
	                                $history[$k]['fieldvalue'] = $arr_flds[$x[0]] . " entfernt";
	                            } else {
	                                $history[$k]['fieldvalue'] = $arr_flds[$x[0]] . " hinzugefügt";
	                            }
	                            break;
	                        
	                        case "supplementary_services":
	                            $history[$k]['fieldvalue'] = $this->translate('supplementary services'). ": ". $v['fieldvalue'];
	                            break;
	                            
	                        case "comments":
	                            $history[$k]['fieldvalue'] = $this->translate('comments'). ": ". $v['fieldvalue'];
	                            break;
	                            
                            case "since_when":
                                $history[$k]['fieldvalue'] = $this->translate('since when'). ": " . (! empty($v['fieldvalue']) ? date("d.m.Y", strtotime($v['fieldvalue'])) : 'entfernt');
                                break;
	                    }
	                
	                    
	                    
	                    break;
	                
	                case "grow11" :
	                	
	                	$history[$k]['fieldvalue'] = $v['fieldvalue'];
	                	$history[$k]['fieldname'] = $v['fieldname'];
	                	
	                	break;
	                
	                //TODO-3433 Carmen 21.09.2020 
                	case "grow100" :
                	
                		$history[$k]['fieldvalue'] = $v['fieldvalue'];
                		$history[$k]['fieldname'] = $v['fieldname'];
                	
                		break;
                	//--
		                    
		            case "xxxxxxxxxx" :
		                break;
		                
		                
		            
		        }
		        
		        
		    
		    }
		    
		    return $history;
		}
		
	}

?>