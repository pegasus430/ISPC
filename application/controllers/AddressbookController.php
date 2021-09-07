<?php

	class AddressbookController extends Zend_Controller_Action {

		public function init()
		{

		}

		public function indexAction()
		{

		}

		public function addressbookAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$ipid = false;
			$letter = false;
			$keyword = false;
			$fdoc_ids = false;
			$pfle_ids = false;
			$spec_ids = false;
			$apo_ids = false;
			$type = false;


			if($_REQUEST['slet'])
			{
				$letter = $_REQUEST['slet'];
			}

			if($_REQUEST['ipid'])
			{
				$ipid = $_REQUEST['ipid'];
			}

			if($_REQUEST['keyword'])
			{
				$keyword = $_REQUEST['keyword'];
			}

			if(empty($_REQUEST['type']))
			{
				$type = 'All';
			}
			else
			{
				$type = $_REQUEST['type'];
			}

			$favorites = AddrbookFavorites::getFavorites($logininfo->userid);
			if($_REQUEST['fav'] == '1')
			{
				if(is_array($favorites['H']) && sizeof($favorites['H']) > 0)
				{
					$fdoc_ids = $favorites['H'];
				}
				else
				{
					$fdoc_ids = array('999999999999999999'); //hack to force script to grab no results
				}

				if(is_array($favorites['P']) && sizeof($favorites['P']) > 0)
				{
					$pfle_ids = $favorites['P'];
				}
				else
				{
					$pfle_ids = array('999999999999999999'); //hack to force script to grab no results
				}

				if(is_array($favorites['F']) && sizeof($favorites['F']) > 0)
				{
					$spec_ids = $favorites['F'];
				}
				else
				{
					$spec_ids = array('999999999999999999'); //hack to force script to grab no results
				}

				if(is_array($favorites['A']) && sizeof($favorites['A']) > 0)
				{
					$apo_ids = $favorites['A'];
				}
				else
				{
					$apo_ids = array('999999999999999999'); //hack to force script to grab no results
				}

				if(is_array($favorites['I']) && sizeof($favorites['I']) > 0)
				{
					$his_ids = $favorites['I'];
				}
				else
				{
					$his_ids = array('999999999999999999'); //hack to force script to grab no results
				}


				if(is_array($favorites['SH']) && sizeof($favorites['SH']) > 0)
				{
					$sh_ids = $favorites['SH'];
				}
				else
				{
					$sh_ids = array('999999999999999999'); //hack to force script to grab no results
				}

				if(is_array($favorites['AU']) && sizeof($favorites['AU']) > 0)
				{
					$loc_ids = $favorites['AU'];
				}
				else
				{
					$loc_ids = array('999999999999999999'); //hack to force script to grab no results
				}
				
				if(is_array($favorites['SR']) && sizeof($favorites['SR']) > 0)
				{
					$sr_ids = $favorites['SR'];
				}
				else
				{
					$sr_ids = array('999999999999999999'); //hack to force script to grab no results
				}
				
				if(is_array($favorites['PH']) && sizeof($favorites['PH']) > 0)
				{
					$ph_ids = $favorites['PH'];
				}
				else
				{
					$ph_ids = array('999999999999999999'); //hack to force script to grab no results
				}
				
				if(is_array($favorites['HC']) && sizeof($favorites['HC']) > 0)
				{
					$hc_ids = $favorites['HC'];
				}
				else
				{
					$hc_ids = array('999999999999999999'); //hack to force script to grab no results
				}

				if(is_array($favorites['B']) && sizeof($favorites['B']) > 0)
				{
					$b_ids = $favorites['B'];
				}
				else
				{
					$b_ids = array('999999999999999999'); //hack to force script to grab no results
				}
			}

          

			//specialists(Facharzt) (F)
			if($type == 'All' || $type == 'F')
			{
				$specialists = Specialists::get_specialists_addressbook($ipid, $letter, $keyword, $spec_ids);

				$m_specialists_types = new SpecialistsTypes();
				$specialists_types  =$m_specialists_types->get_specialists_types($logininfo->clientid);
				
				if(!empty($specialists_types)){
					foreach($specialists_types as $k=>$tp){
						$s_type[$tp['id']] = $tp['name'];
					}
				}
				
				
				foreach($specialists as $specialist)
				{
					if(empty($specialist['practice']))
					{
						$specialist_name = trim($specialist['first_name']) . ' ' . trim($specialist['last_name']);
					}
					else
					{
						$specialist_name = trim($specialist['practice']);
					}
					$master_contacts[$i]['name'] = $specialist_name;
					$master_contacts[$i]['type'] = 'F';
					$master_contacts[$i]['id'] = $specialist['id'];
					$master_contacts[$i]['details'] = $specialist;
					$master_contacts[$i]['details']['medical_speciality'] = $s_type[$specialist['medical_speciality']];
					$master_contacts[$i]['user'] = $logininfo->userid;
					if(in_array($specialist['id'], $favorites['F']))
					{
						$master_contacts[$i]['isfavorite'] = 1;
					}
					else
					{
						$master_contacts[$i]['isfavorite'] = 0;
					}
					$sort_names[$i] = trim($specialist['last_name']) . ' ' . trim($specialist['title']) . ' ' . trim($specialist['first_name']);
// 					$sort_names[$i] = $specialist_name;
					$letters[] = strtoupper(substr($specialist_name, 0, 1));
					$i++;
				}
			}
			
			if ($type == 'All' || $type == 'H')
			{
				$family_doctors = FamilyDoctor::getFamilyDoctors($ipid, $letter, $keyword, $fdoc_ids);
				$i = 0;
				foreach ($family_doctors as $family_doctor)
				{
					$master_contacts[$i]['name'] = trim($family_doctor['last_name']) . ' ' . trim($family_doctor['title']) . ', ' . trim($family_doctor['first_name']);
					$master_contacts[$i]['type'] = 'H';
					$master_contacts[$i]['id'] = $family_doctor['id'];
					$master_contacts[$i]['details'] = $family_doctor;
					$master_contacts[$i]['user'] = $logininfo->userid;
					if (in_array($family_doctor['id'], $favorites['H']))
					{
						$master_contacts[$i]['isfavorite'] = 1;
					}
					else
					{
						$master_contacts[$i]['isfavorite'] = 0;
					}
					$sort_names[$i] = trim($family_doctor['last_name']) . ' ' . trim($family_doctor['title']) . ' ' . trim($family_doctor['first_name']);
					$letters[] = strtoupper(substr($family_doctor['first_name'], 0, 1));
					$letters[] = strtoupper(substr($family_doctor['last_name'], 0, 1));
					$i++;
				}
			}

			if($type == 'All' || $type == 'P')
			{
				$nurses = Pflegedienstes::getPflegedienstes($ipid, $letter, $keyword, $pfle_ids);

				foreach($nurses as $nurse)
				{

					if(empty($nurse['nursing']))
					{
						$nurse_name = trim($nurse['first_name']) . ' ' . trim($nurse['last_name']);
					}
					else
					{
						$nurse_name = trim($nurse['nursing']);
					}
					$master_contacts[$i]['name'] = $nurse_name;
					$master_contacts[$i]['type'] = 'P';
					$master_contacts[$i]['id'] = $nurse['id'];
					$master_contacts[$i]['details'] = $nurse;
					$master_contacts[$i]['user'] = $logininfo->userid;
					if(in_array($nurse['id'], $favorites['P']))
					{
						$master_contacts[$i]['isfavorite'] = 1;
					}
					else
					{
						$master_contacts[$i]['isfavorite'] = 0;
					}
					$sort_names[$i] = $nurse_name;
					$letters[] = strtoupper(substr($nurse_name, 0, 1));
					$i++;
				}
			}
			if($type == 'All' || $type == 'A')
			{
				$pharmacies = Pharmacy::getPharmacys($ipid, $letter, $keyword, $apo_ids);

				foreach($pharmacies as $pharmacy)
				{

					if(empty($pharmacy['pharmacy']))
					{
						$pharmacy_name = trim($pharmacy['first_name']) . ' ' . trim($pharmacy['last_name']);
					}
					else
					{
						$pharmacy_name = trim($pharmacy['pharmacy']);
					}
					$master_contacts[$i]['name'] = $pharmacy_name;
					$master_contacts[$i]['type'] = 'A';
					$master_contacts[$i]['id'] = $pharmacy['id'];
					$master_contacts[$i]['details'] = $pharmacy;
					$master_contacts[$i]['user'] = $logininfo->userid;
					if(in_array($pharmacy['id'], $favorites['A']))
					{
						$master_contacts[$i]['isfavorite'] = 1;
					}
					else
					{
						$master_contacts[$i]['isfavorite'] = 0;
					}
					$sort_names[$i] = $pharmacy_name;
					$letters[] = strtoupper(substr($pharmacy_name, 0, 1));
					$i++;
				}
			}

			if($type == 'All' || $type == 'I')
			{
				$health_ins = HealthInsurance::getHealthInsuraces($ipid, $letter, $keyword, $his_ids);
				foreach($health_ins as $healthins)
				{

					$master_contacts[$i]['name'] = trim($healthins['name']);
					$master_contacts[$i]['type'] = 'I';
					$master_contacts[$i]['id'] = $healthins['id'];
					$master_contacts[$i]['details'] = $healthins;
					$master_contacts[$i]['user'] = $logininfo->userid;
					if(in_array($healthins['id'], $favorites['I']))
					{
						$master_contacts[$i]['isfavorite'] = 1;
					}
					else
					{
						$master_contacts[$i]['isfavorite'] = 0;
					}
					$sort_names[$i] = $healthins['name'];
					$letters[] = strtoupper(substr($healthins['name'], 0, 1));
					$i++;
				}
			}

			if($type == 'All' || $type == 'SH')
			{

				//get supplies
				$suppliers = Supplies::getSuppliess($ipid, $letter, $keyword, $sh_ids);

				foreach($suppliers as $k_supplier => $v_supplier)
				{

					$master_contacts[$i]['name'] = trim($v_supplier['supplier']);
					$master_contacts[$i]['type'] = 'SH';
					$master_contacts[$i]['id'] = $v_supplier['id'];
					$master_contacts[$i]['details'] = $v_supplier;
					$master_contacts[$i]['user'] = $logininfo->userid;

					if(in_array($v_supplier['id'], $favorites['SH']))
					{
						$master_contacts[$i]['isfavorite'] = 1;
					}
					else
					{
						$master_contacts[$i]['isfavorite'] = 0;
					}
					$sort_names[$i] = $v_supplier['supplier'];
					$letters[] = strtoupper(substr($v_supplier['name'], 0, 1));
					$i++;
				}
			}

			if($type == 'All' || $type == 'AU')
			{

				//get locations
				$locations = Locations::getAllLocations($ipid, $letter, $keyword, $loc_ids, true);

				foreach($locations as $k_location => $v_location)
				{
					$master_contacts[$i]['name'] = trim($v_location['location']);
					$master_contacts[$i]['type'] = 'AU';
					$master_contacts[$i]['id'] = $v_location['id'];
					$master_contacts[$i]['details'] = $v_location;
					$master_contacts[$i]['user'] = $logininfo->userid;

					if(in_array($v_location['id'], $favorites['AU']))
					{
						$master_contacts[$i]['isfavorite'] = 1;
					}
					else
					{
						$master_contacts[$i]['isfavorite'] = 0;
					}
					$sort_names[$i] = $v_location['location'];
					$letters[] = strtoupper(substr($v_location['name'], 0, 1));
					$i++;
				}
			}
			
			if($type == 'All' || $type == 'SR')
			{
			
				//get suppliers
				$suppliers_vers = Suppliers::getSupplierss($ipid, $letter, $keyword, $sr_ids);
			
				foreach($suppliers_vers as $k_supplier_vers => $v_supplier_vers)
				{
					if(empty($v_supplier_vers['supplier']))
					{
						$suppl_name = trim($v_supplier_vers['first_name']) . ' ' . trim($v_supplier_vers['last_name']);
					}
					else
					{
						$suppl_name = trim($v_supplier_vers['supplier']);
					}
			
					$master_contacts[$i]['name'] = trim($v_supplier_vers['supplier']);
					$master_contacts[$i]['type'] = 'SR';
					$master_contacts[$i]['id'] = $v_supplier_vers['id'];
					$master_contacts[$i]['details'] = $v_supplier_vers;
					$master_contacts[$i]['user'] = $logininfo->userid;
			
					if(in_array($v_supplier_vers['id'], $favorites['SR']))
					{
						$master_contacts[$i]['isfavorite'] = 1;
					}
					else
					{
						$master_contacts[$i]['isfavorite'] = 0;
					}
					$sort_names[$i] = $v_supplier_vers['supplier'];
					$letters[] = strtoupper(substr($suppl_name, 0, 1));
					$i++;
				}
			}
			if($type == 'All' || $type == 'PH')
			{
				$physiotherapeuten = Physiotherapists::getPhysiotherapists($ipid, $letter, $keyword, $ph_ids);

				foreach($physiotherapeuten as $physio)
				{
			
					if(empty($physio['physiotherapist']))
					{
						$physio_name = trim($physio['first_name']) . ' ' . trim($physio['last_name']);
					}
					else
					{
						$physio_name = trim($physio['physiotherapist']);
					}
					$master_contacts[$i]['name'] = $physio_name;
					$master_contacts[$i]['type'] = 'PH';
					$master_contacts[$i]['id'] = $physio['id'];
					$master_contacts[$i]['details'] = $physio;
					$master_contacts[$i]['user'] = $logininfo->userid;
					if(in_array($physio['id'], $favorites['PH']))
					{
						$master_contacts[$i]['isfavorite'] = 1;
					}
					else
					{
						$master_contacts[$i]['isfavorite'] = 0;
					}
					$sort_names[$i] = $physio_name;
					$letters[] = strtoupper(substr($physio_name, 0, 1));
					$i++;
				}
			}
			if($type == 'All' || $type == 'HC')
			{
				$homecare = Homecare::getHomecare_suffix($ipid, $letter, $keyword, $hc_ids);
					
				foreach($homecare as $home)
				{
					
					if(empty($home['homecare']))
					{
						$home_name = trim($home['first_name']) . ' ' . trim($home['last_name']);
					}
					else
					{
						$home_name = trim($home['homecare']);
					}
					$master_contacts[$i]['name'] = $home_name;
					$master_contacts[$i]['type'] = 'HC';
					$master_contacts[$i]['id'] = $home['id'];
					$master_contacts[$i]['details'] = $home;
					$master_contacts[$i]['user'] = $logininfo->userid;
					if(in_array($home['id'], $favorites['HC']))
					{
						$master_contacts[$i]['isfavorite'] = 1;
					}
					else
					{
						$master_contacts[$i]['isfavorite'] = 0;
					}
					$sort_names[$i] = $home_name;
					$letters[] = strtoupper(substr($home_name, 0, 1));
					$i++;
				}
			}
			if($type == 'All' || $type == 'B')
			{
			    $servicesf = Servicesfuneral::getServicesfuneral($ipid, $letter, $keyword, $b_ids);
				$i = 0;
				foreach ($servicesf as $sf)
				{
					if(empty($sf['services_funeral_name']))
					{
						$sf_name = trim($sf['cp_fname']) . ' ' . trim($sf['cp_lname']);
					}
					else
					{
						$sf_name = trim($sf['services_funeral_name']);
					}
					$master_contacts[$i]['name'] = $sf_name;
					$master_contacts[$i]['type'] = 'B';
					$master_contacts[$i]['id'] = $sf['id'];
					$master_contacts[$i]['details'] = $sf;
					$master_contacts[$i]['user'] = $logininfo->userid;
					if (in_array($sf['id'], $favorites['B']))
					{
						$master_contacts[$i]['isfavorite'] = 1;
					}
					else
					{
						$master_contacts[$i]['isfavorite'] = 0;
					}
					$sort_names[$i] = $sf_name;
					$letters[] = strtoupper(substr( $sf_name, 0, 1));
					//$letters[] = strtoupper(substr($sf['last_name'], 0, 1));
					$i++;
				}
			}
			array_multisort($sort_names, SORT_ASC, SORT_STRING, $master_contacts);

			$letters = array_unique($letters);

			asort($letters, SORT_STRING);

			$this->view->letters = $letters;
			if($_REQUEST['source'] == 'brief')
			{
				$grid = new Pms_Grid($master_contacts, 1, count($master_contacts), "addressbooklistletters.html");
			}
			else
			{
				$grid = new Pms_Grid($master_contacts, 1, count($master_contacts), "addressbooklist.html");
			}

            //ISPC-2708,elena,07.01.2021
			$this->view->showMessageForEmpty = false;
			if(empty($master_contacts) && $_REQUEST['source'] == 'brief' ){
                $this->view->showMessageForEmpty = true;
                //$this->view->addressgrid = '<tr><td colspan="2">keine Einträge vorhanden</td></tr>';
            }
			$this->view->addressgrid = $grid->renderGrid();

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['patientlist'] = $this->view->render('addressbook/fetchlist.html');

			echo json_encode($response);
			exit;
		}

		public function addaddrfavoriteAction()
		{

			$allowed_types = array("H", "F", "P", "A", "I", "SH", "AU", "SR" , "PH" ,"HC","B");
			$uid = trim($_REQUEST['usr_id']);
			$fid = trim($_REQUEST['fav_id']);
			$type = trim(strtoupper($_REQUEST['type']));
			if(in_array($type, $allowed_types) && is_numeric($uid) && is_numeric($fid))
			{
				$addrbook = new AddrbookFavorites();
				$addrbook->user_id = $uid;
				$addrbook->fav_id = $fid;
				$addrbook->type = $type;
				$addrbook->save();
				print_r($addrbook->id);
			}

			exit;
		}

		public function deladdrfavoriteAction()
		{
			$uid = trim($_REQUEST['usr_id']);
			$fid = trim($_REQUEST['fav_id']);
			$type = trim(strtoupper($_REQUEST['type']));
			$allowed_types = array("H", "F", "P", "A", "I", "SH", "AU", "SR" , "PH" ,"HC","B");

			if(in_array($type, $allowed_types) && is_numeric($uid) && is_numeric($fid))
			{
				$q = Doctrine_Query::create()
					->delete('AddrbookFavorites a')
					->where("a.user_id = ?",  $uid)
					->andWhere("a.fav_id = ?",  $fid)
					->andWhere("a.type = ?",  $type);
				$q->execute();
			}

			exit;
		}

	}
