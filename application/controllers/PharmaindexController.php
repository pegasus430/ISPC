<?php

	/**
	 * Class PharmaindexController
	 *
	 * This Controller is aproxy for function calls to external MMI Service Platform
	 */
	class PharmaindexController extends Zend_Controller_Action {

		public function init()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
		}

		public function getinsuranceAction()
		{
			$return = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['name']));
			echo $return;
			exit;
		}

		public function getdocumentsAction()
		{
			$return = $this->callServicePlatform('getDocuments', array('productid' => $_REQUEST['pid'], 'documenttypecode' => "SPC", 'contentfilter' => '0'));
			echo $return;
			exit;
		}

		public function getdocumentsbaseAction()
		{
			$return = $this->callServicePlatform('getDocuments', array('productid' => $_REQUEST['pid'], 'documenttypecode' => "BI", 'contentfilter' => '0'));
			echo $return;
			exit;
		}

		public function getmoleculeAction()
		{
			$searchText = $_REQUEST['searchtext'];
			$searchText = explode(' ', $searchText);

			for($i = 0; $i < sizeof($searchText); $i++)
			{
				$searchParams['name'] = $searchText[$i];
				$return = $this->callServicePlatform('getMolecules', $searchParams);
				$moleculeId = json_decode($return);

				$search[] = $moleculeId->MOLECULE[0]->ID;
			}

			$searchParams = '';
			$return = '';
			$sizeOfSearch = sizeof($search);

			if($sizeOfSearch > 0)
			{
				$iknrGroupId = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ik_no']));
				$iknrGroupId = json_decode($iknrGroupId);
				$iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;

				$searchParams['moleculeid_andlist'] = $search;

				$searchParams['moleculetype'] = 'ACTIVE';
				$searchParams['maxresult'] = 100;
				$searchParams['insurancegroupid'] = $iknrGroupId;

				$return = $this->callServicePlatform('getProducts', $searchParams);
			}

			echo $return;
			exit;
		}

		public function getproductsAction()
		{
			//$return = file_get_contents('/home/claudiu/Desktop/mmi_1.txt');
			//echo $return;
			//exit;
		
			$iknrGroupId = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ik_no']));
			$iknrGroupId = json_decode($iknrGroupId);
			$iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;
			
			$searchtext = trim(urldecode(trim($_REQUEST['searchtext'])));
			
			$searchParams = array();
			$searchParams['name'] = '[' . $searchtext . ']';
			
			if (isset($_REQUEST['searchType'])) {
				
				switch($_REQUEST['searchType']) {
					case "1":{
						$searchParams['name'] = '[' . $searchtext . ']';
						$searchParams['pzn_orlist'] = null;
					}break;
						
					case "pzn":{
						
						$searchtext =  ltrim( rtrim( (string) $searchtext ) , ' 0' );
						
						$searchParams['name'] = null;
						$searchParams['pzn_orlist'] = array($searchtext);//null;
						
					}break;	

					
				}
			} 

// 			$searchParams['name'] = '[' . $_REQUEST['searchtext'] . ']';

			$searchParams['moleculename'] = null;
			$searchParams['companyname'] = null;
			$searchParams['moleculetype'] = null;
			$searchParams['fulltextsearch'] = (int)$_REQUEST['sm'];
 			$searchParams['disabledobjects'] = array();
			$searchParams['maxresult'] = 100;
// 			$searchParams['pzn_orlist'] = null;
			$searchParams['insurancegroupid'] = $iknrGroupId;

			$searchParams['assortment'] = null;
			$searchParams['tolerance'] = 0;
			$searchParams['sortorder'] = 'NONE';

			$return = $this->callServicePlatform('getProducts', $searchParams);
			
			echo $return;
			exit;
		}

		//used in patient medications livesearch
		public function getproductsmedilsAction()
		{
//ISPC-2554
// // 		    $mmi_dosage_form_response = $this->callServicePlatform('getCatalogEntries',array('catalogshortname'=>'PHARMFORMIFA'));
// 		    $mmi_dosage_form_response = $this->callServicePlatform('getCatalogEntries',array('catalogshortname'=>'MOLECULEUNIT'));
// 		    $mmi_dosage_form_array = json_decode($mmi_dosage_form_response);
// 		    echo "<pre/>";
		    
// 		    print_R("catalog name: Darreichungsform IFA \n ");
// 		    print_R($mmi_dosage_form_array);
// 		    exit;
// --

			$iknrGroupId = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ik_no']));
			$iknrGroupId = json_decode($iknrGroupId);
			$iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;

			$searchtext = trim(urldecode(trim($_REQUEST['searchtext']))); // cause this is a GET

			$searchParams['name'] = '[' . urldecode($_REQUEST['searchtext']) . ']';
			
			$searchParams['moleculename'] = null;
			$searchParams['companyname'] = null;
			$searchParams['moleculetype'] = null;
			$searchParams['fulltextsearch'] = $_REQUEST['sm'];
			$searchParams['disabledobjects'] = array();
			$searchParams['maxresult'] = 100;
			
			$searchParams['pzn_orlist'] = null;
			if ( is_numeric($searchtext) && strlen($searchtext) >= 7  && strlen($searchtext) <= 8 ) {
				
				$searchtext =  ltrim( rtrim( (string) $searchtext ) , ' 0' ); //remove leading 0
				
				$searchParams['name'] = null;
				$searchParams['pzn_orlist'] = array($searchtext);
			}
			$searchParams['insurancegroupid'] = $iknrGroupId;

			$searchParams['assortment'] = null;
			$searchParams['tolerance'] = 0;
			$searchParams['sortorder'] = 'NONE';

			$return = $this->callServicePlatform('getProducts', $searchParams);

			if(strlen($_REQUEST['source']) && ($_REQUEST['source'] == "receipt" || $_REQUEST['source'] == "medication" )){
    			$source_page = $_REQUEST['source'];
			} else{
    			$source_page = "medication"; // set default medication 
			}
			
			if($return)
			{
				$drop_array = json_decode($return);
				
				$this->view->result_json = json_encode($return);
				
                $this->view->result = $drop_array;
				$i = "1";
				foreach($drop_array->PRODUCT as $key => $val)
				{
					$wirkstoffe = ''; // active substance = drug
					$atc_details = ''; //ISPC-2554 pct.3
					
                    if($source_page == "receipt"){
                        
    				    foreach($val->PACKAGE_LIST as $f=>$pval)				    
                        {
        					$droparray[$i]['id'] = $i;
        					$droparray[$i]['name'] = $pval->NAME;
        					$droparray[$i]['comment'] = "";
        					$droparray[$i]['row'] = $_REQUEST['row'];
        					$droparray[$i]['wirkstoffe'] = $wirkstoffe;
        					
        					$droparray[$i]['PZN'] = $pval->PZN;
        					$droparray[$i]['DBF_ID'] = $pval->ID;
        					$droparray[$i]['TYPE'] = 'mmi_receipt_dropdown';
        					
                            $droparray[$i]['isBTM'] = in_array("BTM", $val->ICONCODE_LIST) ? 1 : 0;
        					$i++;
                        }
                        
                    } else {
                    	
                    	$wirkstoffe_array = array();
                    	
                    	foreach($val->ITEM_LIST[0]->COMPOSITIONELEMENTS_LIST as $ak => $av)	{
                    		if($av->MOLECULETYPECODE == "A") {
                    			$unit = $av->MOLECULEUNITCODE;
                    			$name = $av->MOLECULENAME;
                    			$mass = $av->MASSFROM;
                    			//ISPC-2554 Carmen 06.08.2020
                    			/* if(!empty($name) || (!empty($mass) && !empty($unit))) {
                    				$extra = '';
                    				if(!empty($mass) && !empty($unit)) {
                    					$extra = "(" . $mass . " " . strtolower($unit) . ")";
                    				}
                    				$wirkstoffe_array[] = $name . $extra;

                    			} */
                    			
                    			$wirkstoffe_array[] = $name;
                    			//--
                    		}
                    	}
                    	
                    	$wirkstoffe = implode(", ", $wirkstoffe_array);
                    	//ISPC-2554 pct.3 Carmen 26.03.2020
                    	$atc_array = array();
                    	
                    	foreach($val->ITEM_LIST[0]->ATCCODE_LIST as $ak => $av)	{
                    		$atc_array['atc_code'] = $av->CODE;
                    		$atc_array['atc_description'] = $av->NAME;
                    		$atc_array['atc_groupe_code'] = $av->UPPERCODE;
                    		$atc_array['atc_groupe_description'] = $av->{PARENT}->NAME;
                    	}
                    	if(!empty($atc_array))
                    	{
                    		$atc_details = json_encode($atc_array);
                    	}
                    	//--
    					$droparray[$i]['id'] = $i;
    					$droparray[$i]['name'] = $val->NAME;
    					$droparray[$i]['wirkstoffe'] = $wirkstoffe;
    					$droparray[$i]['atc'] = $atc_details; //ISPC-2554
    					$droparray[$i]['dosageformid'] = $val->ITEM_LIST[0]->PHARMFORMCODE; //ISPC-2554
    					$droparray[$i]['unit'] = $val->ITEM_LIST[0]->BASEMOLECULEUNITCODE; //ISPC-2554
    					$droparray[$i]['takinghint'] = $val->ITEM_LIST[0]->TAKINGHINT; //ISPC-2554 Carmen 16.06.2020
						//ISPC-2554 Ancuta 
						/*
    					$droparray[$i]['unit'] = $val->ITEM_LIST[0]->BASEMOLECULEUNITCODE; //ISPC-2554
    					
    					if($val->ITEM_LIST[0]->PHARMFORMCODE){
    					    $mmi_dosage_form_response = $this->callServicePlatform('getCatalogEntries',array('catalogshortname'=>'PHARMFORM','code'=>$val->ITEM_LIST[0]->PHARMFORMCODE));
    					    $mmi_dosage_form_array = json_decode($mmi_dosage_form_response);
    					    $droparray[$i]['dosage_form_name'] = $mmi_dosage_form_array->CATALOGENTRY[0]->NAME;
    					    $droparray[$i]['dosage_form_id'] = $mmi_dosage_form_array->CATALOGENTRY[0]->CODE;
    					    $droparray[$i]['dosage_form_catalogid'] = $mmi_dosage_form_array->CATALOGENTRY[0]->CATALOGID;
    					}
    					*/
    					//$droparray[$i]['dosageform'] = $val->ITEM_LIST[0]->BASEMOLECULEUNITCODE; //ISPC-2554 ???? FROm WHERE? 
						// --
    					$droparray[$i]['comment'] = "";
    					$droparray[$i]['row'] = $_REQUEST['row'];
    					
    					//@todo: need info on how to modify the packages
    					//!!! for now i use the first PZN ... same in php livesearch on inpout !!!!
    					$droparray[$i]['PZN'] = $val->PACKAGE_LIST[0]->PZN;//ISPC-2329 Ancuta 03.04.2020 Add pzn- first in PACKAGE_LIST
//     					$droparray[$i]['PZN'] = 0;
                        $droparray[$i]['isBTM'] = in_array("BTM", $val->ICONCODE_LIST) ? 1 : 0;//ISPC-2912,Elena,25.05.2021
    					$droparray[$i]['DBF_ID'] = $val->PACKAGE_LIST[0]->ID;
    					$droparray[$i]['TYPE'] = 'mmi_notreceipt_dropdown';
    					
    					$i++;
                        
                    }

				}
			
				//ISPC-2572 Carmen 22.06.2020
				usort($droparray, array(new Pms_Sorter("name"), "_strnatcasecmp"));
				//--
				$this->view->droparray = $droparray;				
				
				
			}

			if($_REQUEST['client'] && strlen($_REQUEST['client'])>0 && is_numeric($_REQUEST['client']) ) {
			    // insert personal data
			    $clientid = $_REQUEST['client'];
			    $modules =  new Modules();
			    $clientModules = $modules->get_client_modules($clientid);
			    
			    $querystr = "
    			     select m.id,m.name,m.pzn,m.comment, m.pkgsz from
            			(select distinct(name),min(id)as id,pzn,  package_size as pkgsz, comment as comment
            			from medication_master
            			where clientid = '" . $clientid . "'
            			and extra=0
            			and isdelete=0
            			group by name)as m
            			inner join medication_master b on m.id=b.id
            			where(trim(lower(m.name)) like trim(lower(:search_string)))
            			and isdelete=0
            			and clientid = '" . $clientid . "'
            			and extra=0";

			    $manager = Doctrine_Manager::getInstance();
			    $manager->setCurrentConnection('SYSDAT');
			    $conn = $manager->getCurrentConnection();
			
			    $query = $conn->prepare($querystr);
			
			    $search_string = addslashes(urldecode(trim($_REQUEST['searchtext']) . "%"));
			    $query->bindValue(':search_string', $search_string);
			
			    $dropexec = $query->execute();
			    $personal_drop_array = $query->fetchAll();
			
			    foreach($personal_drop_array as $key => $val)
			    {
			        $personal_droparray[$key+100000]['id'] = $val['id'];
			        $personal_droparray[$key+100000]['name'] = html_entity_decode($val['name'], ENT_QUOTES, "UTF-8");
			        $personal_droparray[$key+100000]['comment'] = html_entity_decode($val['comment'], ENT_QUOTES, "UTF-8");
			        $personal_droparray[$key+100000]['wirkstoffe'] = '';
			        //this is the increment to know which line to fill in admission medis form
			        $personal_droparray[$key+100000]['row'] = $_REQUEST['row'];
			        
			        $personal_droparray[$key+100000]['PZN'] = $val['pzn'];
			        $personal_droparray[$key+100000]['DBF_ID'] = $val['id'];
			        //$droparray[$i]['TYPE'] = 'personal';
			        $personal_droparray[$key+100000]['TYPE'] = 'personal';
			    }
			
			    //ISPC - 2439
			    if($clientModules['192'])
			    {
			    	$this->view->firstarray = $personal_droparray;
			    	$this->view->secondarray = $droparray;
			    	$this->view->firsttitle = $this->view->translate('PERSONAL RESULTS');
			    	$this->view->secondtitle = $this->view->translate('MMI RESULTS');
			    }
			    else
			    {
			    	$this->view->firstarray = $droparray;
			    	$this->view->secondarray = $personal_droparray;
			    	$this->view->firsttitle = $this->view->translate('MMI RESULTS');
			    	$this->view->secondtitle = $this->view->translate('PERSONAL RESULTS');
			    }
			    //ISPC - 2439
			   // $this->view->personal_droparray = $personal_droparray;
			    $show_personal = "1";
			} else{
			    $show_personal = "0";
			}
			
			$this->view->show_personal = $show_personal;

			//init is stopping the zend renderer for all functions... so we use this
			echo $this->view->render('pharmaindex/getproductsmedils.html');
			exit;
		}
		
		//used in patient medications livesearch
		public function getproductsmedils_150825Action()
		{
			$iknrGroupId = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ik_no']));
			$iknrGroupId = json_decode($iknrGroupId);
			$iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;

			$searchParams['name'] = '' . $_REQUEST['searchtext'] . '';

			$searchParams['moleculename'] = null;
			$searchParams['companyname'] = null;
			$searchParams['moleculetype'] = null;
			$searchParams['fulltextsearch'] = $_REQUEST['sm'];
			$searchParams['disabledobjects'] = array();
			$searchParams['maxresult'] = 100;
			$searchParams['pzn_orlist'] = null;
			$searchParams['insurancegroupid'] = $iknrGroupId;

			$searchParams['assortment'] = null;
			$searchParams['tolerance'] = 0;
			$searchParams['sortorder'] = 'NONE';

			$return = $this->callServicePlatform('getProducts', $searchParams);

			if($return)
			{
				$drop_array = json_decode($return);

				$i = "1";
				foreach($drop_array->PRODUCT as $key => $val)
				{
					$droparray[$i]['id'] = $i;
					$droparray[$i]['name'] = $val->NAME;
					$droparray[$i]['comment'] = "";
					//this is the increment to know which line to fill in admission medis form
					$droparray[$i]['row'] = $_REQUEST['row'];
					$i++;
				}
				

				$this->view->droparray = $droparray;
			}

			//init is stopping the zend renderer for all functions... so we use this
			echo $this->view->render('pharmaindex/getproductsmedils.html');
			exit;
		}

		public function getpricecomparisonAction()
		{
			//$return = file_get_contents('/home/claudiu/Desktop/mmi_getpricecomparison.txt');
			//echo $return;
			//exit;
			//GET INSURANCE COMPANY GROUP ID
			$insid = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ikno']));
			$insid = json_decode($insid);
			$insid = $insid->INSURANCECOMPANYGROUP[0]->ID;

			// Initial price comparison
			$return_price_comp = $this->callServicePlatform('getPackages', array('pricecomparisonpzn' => $_REQUEST['pid'], 'maxresult' => 100, 'insurancegroupid' => $insid));
			$price_comparsion = json_decode($return_price_comp);

			// Get packages for the original product from select
		    $return_new = $this->callServicePlatform('getPackages',  array('productid_orlist' => array($_REQUEST['org']), 'insurancegroupid' => $insid));
		    $original_product_package =json_decode($return_new);
		    
		    $pkg_size_data_array =  array();
		    if($_REQUEST['pkg_size'] && $_REQUEST['pkg_size'] != "0"){
		        $pkg_size_data_array = explode("_",$_REQUEST['pkg_size']); 
		    }

		    
            if(!empty($pkg_size_data_array)){
    		    foreach($original_product_package->PPACKAGE as $ckp=>$prd){
                    if($pkg_size_data_array[0] == "amount" && $prd->SIZE_AMOUNT != $pkg_size_data_array[1]){
                        unset($original_product_package->PPACKAGE[$ckp]);
                    } elseif($pkg_size_data_array[0] == "salescode" && $prd->SALESSTATUSCODE.$prd->SIZE_NORMSIZECODE != $pkg_size_data_array[1]){
                            unset($original_product_package->PPACKAGE[$ckp]);
                    }
    		    }
    		    
    		    $original_product_package->PPACKAGE = array_values($original_product_package->PPACKAGE);
            }
 
		    
            // Merge Result from price_comparison with initial packages from selcted product
		    if(!empty($price_comparsion->PPACKAGE)){
		        $price_comparsion->PPACKAGE = array_merge($price_comparsion->PPACKAGE,$original_product_package->PPACKAGE);
		    } else {
		        $price_comparsion->PPACKAGE = $original_product_package->PPACKAGE;
		    }

		    $return = json_encode($price_comparsion);
			
			echo $return;
			exit;
		}

		public function getamrAction()
		{
			//$return = file_get_contents('/home/claudiu/Desktop/mmi_getamr.txt');
			//echo $return;
			//exit;
			
			$return = $this->callServicePlatform('getAMR', array('productid' => $_REQUEST['pid'], 'regulationtypecode_orlist' => array(1, 3, 4)));
			echo $return;
			exit;
		}

		public function getpackagesAction()
		{
			$return = $this->callServicePlatform('getPackages', array('productid_orlist' => array($_REQUEST['pid']), 'insurancegroupid' => $_REQUEST['ikno']));
			echo $return;
			exit;
		}

		public function getcatalogsAction()
		{
			$return = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => '109028580'));
			#echo $return;
			$return = json_decode($return);
			echo $return;
			exit;
		}

		public function getatcAction()
		{
			$iknrGroupId = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ik_no']));
			$iknrGroupId = json_decode($iknrGroupId);
			$iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;

			$search[] = strtoupper($_REQUEST['searchtext']);
			$searchParams['atccode_orlist'] = $search;

			$searchParams['moleculename'] = null;
			$searchParams['moleculetype'] = null;
			$searchParams['maxresult'] = 100;
			$searchParams['sortorder'] = 'KBV';
			$searchParams['insurancegroupid'] = $iknrGroupId;

			$return = $this->callServicePlatform('getProducts', $searchParams);

			echo $return;
			exit;
		}

		public function getcompanyAction()
		{
			$searchParams['companyid_orlist'][] = $_POST['companyId'];
			$return = $this->callServicePlatform('getCompanies', $searchParams);
			echo $return;
			exit;
		}

		public function getproductsbycompanyAction()
		{
			$searchParams['companyname'] = '%' . $_REQUEST['searchtext'] . '%';
			$searchParams['maxresult'] = 100;
			$return = $this->callServicePlatform('getProducts', $searchParams);
			echo $return;
			exit;
		}

		public function getsuggestionsAction()
		{
			$searchParams['searchname'] = '' . $_REQUEST['searchtext'] . '';
			$searchParams['resulttype'] = 'PRODUCT';
			$searchParams['toplistsize'] = '10';

			$return = $this->callServicePlatform('getSuggestions', $searchParams);

			echo $return;
			exit;
		}

		/*
		 * @param $function: (getCompanies, getProducts, ..)
		 */

		private function callServicePlatform($function, $params = 0)
		{
			$url = "http://" . Zend_Registry::get('mmilicserver') . "/rest/pharmindexv2";

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$licensekey = Zend_Registry::get('mmilicserial');
			$licensename = Zend_Registry::get('mmilicname');

			$curl = curl_init();
			$url = $url . '/' . $function . '/' . $licensekey . '/' . $licensename . '/';
				
			if($params != 0)
			{
				$params = urlencode(json_encode($params));
				$params = str_replace('+', '%20', $params);
			}
			else
			{
				$params = "{}";
			}

			$url = sprintf("%s%s", $url, $params);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);  //bugfix prevent ispc hanging on broken mmi-server
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);         //bugfix prevent ispc hanging on broken mmi-server

			if($_REQUEST['urlx'])
			{
				print_r(urldecode($url));
			}
			$result = curl_exec($curl);
			curl_close($curl);

			return $result;
			exit;
		}
		
		public function getdosageformmmiAction()
		{
			$this->_helper->layout->setLayout('layout');
			$this->_helper->viewRenderer->setNoRender();
			
			$return = $this->callServicePlatform('getCatalogEntries',array('catalogshortname'=>'PHARMFORM'));
		
			/*$return = '{"CATALOGENTRY":[{"NAME":"Tbl.","CODE":"001","CATALOGID":"104"},
			{"NAME":"Kaps.","CODE":"002","CATALOGID":"104"},
			{"NAME":"Augencreme","CODE":"003","CATALOGID":"104"},
			{"NAME":"Augenöl","CODE":"004","CATALOGID":"104"},
			{"NAME":" Augentropfen, Lösung","CODE":"005","CATALOGID":"104"},
			{"NAME":"Badeöl","CODE":"007","CATALOGID":"104"},
			{"NAME":"Brausetbl.","CODE":"008","CATALOGID":"104"},
			{"NAME":"Creme","CODE":"010","CATALOGID":"104"},
			{"NAME":"Drg.","CODE":"013","CATALOGID":"104"},
			{"NAME":"Filmtbl.","CODE":"014","CATALOGID":"104"},
			{"NAME":"Lacktbl.","CODE":"015","CATALOGID":"104"},
			{"NAME":"Flasche","CODE":"016","CATALOGID":"104"},
			{"NAME":" Flüssigkeit zum Einnehmen","CODE":"017","CATALOGID":"104"},
			{"NAME":"Gel","CODE":"018","CATALOGID":"104"},
			{"NAME":"Gran. zum Einnehmen","CODE":"020","CATALOGID":"104"},
			{"NAME":"Inf.-Amp.","CODE":"021","CATALOGID":"104"}]}';*/
			
			$dosageformarr = (array)json_decode($return);
			
			foreach($dosageformarr as $kdos => $vdos)
			{
				foreach($vdos as $kitem => $vitem)
				{
					$droparray[$kitem]['dosageform_code'] = $vitem->CODE;
					$droparray[$kitem]['dosageform_name'] = $vitem->NAME;
					$droparray[$kitem]['dosageform_catalogid'] = $vitem->CATALOGID;;
				}				
			}
			
			$entries = Doctrine_Query::create()
			->select('count("id")')
			->from('MedicationDosageformMmi')
			->fetchArray();
						
			if($entries)
			{
				$deleteall = MedicationDosageformMmiTable::getInstance()->createQuery('atc')->delete()->execute();
			}
			
			if(!empty($droparray))
			{
				$atccollection = new Doctrine_Collection('MedicationDosageformMmi');
				$atccollection->fromArray($droparray);
				$atccollection->save();
			}			
			exit;
		}
		
		public function gettypemmmiAction()
		{
			$this->_helper->layout->setLayout('layout');
			$this->_helper->viewRenderer->setNoRender();
				
			$return = $this->callServicePlatform('getCatalogEntry',array('catalogId'=>'123', 'ITEMROACODE' => '18'));
		
			/*$return = '{"CATALOGENTRY":[{"NAME":"Tbl.","CODE":"001","CATALOGID":"104"},
			 {"NAME":"Kaps.","CODE":"002","CATALOGID":"104"},
			 {"NAME":"Augencreme","CODE":"003","CATALOGID":"104"},
			 {"NAME":"Augenöl","CODE":"004","CATALOGID":"104"},
			 {"NAME":" Augentropfen, Lösung","CODE":"005","CATALOGID":"104"},
			 {"NAME":"Badeöl","CODE":"007","CATALOGID":"104"},
			 {"NAME":"Brausetbl.","CODE":"008","CATALOGID":"104"},
			 {"NAME":"Creme","CODE":"010","CATALOGID":"104"},
			 {"NAME":"Drg.","CODE":"013","CATALOGID":"104"},
			 {"NAME":"Filmtbl.","CODE":"014","CATALOGID":"104"},
			 {"NAME":"Lacktbl.","CODE":"015","CATALOGID":"104"},
			 {"NAME":"Flasche","CODE":"016","CATALOGID":"104"},
			 {"NAME":" Flüssigkeit zum Einnehmen","CODE":"017","CATALOGID":"104"},
			 {"NAME":"Gel","CODE":"018","CATALOGID":"104"},
			 {"NAME":"Gran. zum Einnehmen","CODE":"020","CATALOGID":"104"},
			 {"NAME":"Inf.-Amp.","CODE":"021","CATALOGID":"104"}]}';*/
			echo $return;	
			/* $typearr = (array)json_decode($return);
				
			foreach($dosageformarr as $kdos => $vdos)
			{
				foreach($vdos as $kitem => $vitem)
				{
					$droparray[$kitem]['dosageform_code'] = $vitem->CODE;
					$droparray[$kitem]['dosageform_name'] = $vitem->NAME;
					$droparray[$kitem]['dosageform_catalogid'] = $vitem->CATALOGID;;
				}
			}
				
			$entries = Doctrine_Query::create()
			->select('count("id")')
			->from('MedicationDosageformMmi')
			->fetchArray();
		
			if($entries)
			{
				$deleteall = MedicationDosageformMmiTable::getInstance()->createQuery('atc')->delete()->execute();
			}
				
			if(!empty($droparray))
			{
				$atccollection = new Doctrine_Collection('MedicationDosageformMmi');
				$atccollection->fromArray($droparray);
				$atccollection->save();
			} */
			exit;
		}

		/**
		 * ISPC-2711 Ancuta 12.03.2021
		 */
		public function getpatientmedicsAction()
		{
		    $this->_helper->viewRenderer->setNoRender();
		    $this->_helper->layout->setLayout('layout_ajax');
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    //$clientid = $logininfo->clientid;
		    //$userid = $logininfo->userid;
		    
		    $decid = Pms_Uuid::decrypt($_REQUEST['id']);
		    //$ipid = Pms_CommonData::getIpid($decid);
		    
		    //ISPC-2711 Lore 01.04.2021
		    $modules = new Modules();
		    $mmi_module = $modules->checkModulePrivileges("87", $clientid);
		    if(!$mmi_module){
		        return;
		    }
		        
		    $pdp = new PatientDrugPlan();
		    
		    $arr = $pdp->getPatientDrugPlan($decid, true);
 
		    if(empty($arr))
		    {
		        $arr = array();
		    } else {
		        foreach($arr as $k=>$med){
		            if(!empty(!empty($med['MedicationMaster']))){
		                
    		            if(!empty($med['MedicationMaster']['pzn']) && $med['MedicationMaster']['pzn'] != "00000000"){
    		                $searchtext = $med['MedicationMaster']['pzn'];
    		                $arr[$k]['mmi_product_pzn']  = $med['MedicationMaster']['pzn'];
    		                $searchType = 'pzn';
    		            } else{
    		                $searchtext = $med['MedicationMaster']['name'];
    		                $searchType = '1';
    		            }
		            }
		            
		            $iknrGroupId = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => ""));
		            $iknrGroupId = json_decode($iknrGroupId);
		            $iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;
		            
		            $searchtext = trim(urldecode(trim($searchtext)));
		            
		            $searchParams = array();
		            $searchParams['name'] = '[' . $searchtext . ']';
		            
		            if (isset($searchType)) {
		                
		                switch($searchType) {
		                    case "1":{
		                        $searchParams['name'] = '[' . $searchtext . ']';
		                        $searchParams['pzn_orlist'] = null;
		                    }break;
		                    
		                    case "pzn":{
		                        
		                        $searchtext =  ltrim( rtrim( (string) $searchtext ) , ' 0' );
		                        
		                        $searchParams['name'] = null;
		                        $searchParams['pzn_orlist'] = array($searchtext);//null;
		                        
		                    }break;
		                    
		                    
		                }
		            }
		            
		            $searchParams['moleculename'] = null;
		            $searchParams['companyname'] = null;
		            $searchParams['moleculetype'] = null;
// 		            $searchParams['fulltextsearch'] = (int)$_REQUEST['sm'];
		            $searchParams['disabledobjects'] = array();
// 		            $searchParams['maxresult'] = 100;
		            $searchParams['insurancegroupid'] = $iknrGroupId;
		            
		            $searchParams['assortment'] = null;
		            $searchParams['tolerance'] = 0;
		            $searchParams['sortorder'] = 'NONE';
		            
		            $mmi_result = array();
		            $mmi_result_json_encoded = "";
		            $mmi_result_json_encoded= $this->callServicePlatform('getProducts', $searchParams);
		            
		            $mmi_med_product_id = $med['MedicationMaster']['dbf_id'];
                    
		            if($mmi_result_json_encoded){
        		        $mmi_result_array = json_decode($mmi_result_json_encoded);
		                if(count($mmi_result_array->PRODUCT) == "1"){
		                    
        		            foreach($mmi_result_array->PRODUCT as $key => $val)
        		            {
        		                $mmi_med_product_id = $val->ITEM_LIST[0]->PRODUCTID;
        		                foreach($val->PACKAGE_LIST as $pk => $pkg){
        		                    if($pkg->SIZE_NORMSIZECODE){
        		                        $arr[$k]['mmi_packages'][$pk]['pkg_code'] = $pkg->SALESSTATUSCODE.''.$pkg->SIZE_NORMSIZECODE;
        		                    } else{
        		                        $arr[$k]['mmi_packages'][$pk]['pkg_code'] = $pkg->SIZE_AMOUNT;
        		                    }
    		                        $arr[$k]['mmi_packages'][$pk]['pkg_name'] = $pkg->NAME;
    		                        $arr[$k]['mmi_packages'][$pk]['pkg_pzn'] = $pkg->PZN;
    		                        $arr[$k]['mmi_packages'][$pk]['pkg_receipt'] = $pkg->NAME.' '.$pkg->PZN;
        		                }
        		                $arr[$k]['mmi_product_pzn'] = $val->PACKAGE_LIST[0]->PZN;
        		            }
        		            
        		            
        		            $arr[$k]['mmi_product_id'] =$mmi_med_product_id; // IF this is empty we should not show buttons ! 
        		            $arr[$k]['mmi_results'] =$mmi_result_array;
		                }
		            }
 
		        }
		        
		    }
		    
		    echo json_encode($arr);
		    exit;
		}
		
		
	}
	