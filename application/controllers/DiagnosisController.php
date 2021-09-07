<?php

	class DiagnosisController extends Pms_Controller_Action {

		public $act;

		public function init()
		{ // Maria:: Migration ISPC to CISPC 08.08.2020
			/* Initialize action controller here */
			/* Initialize action controller here */
			$this->setActionsWithJsFile([
					"listclientdiagnosis", //ISPC - 2412
			]);
		}

		public function adddiagnosistypeAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Diagnosis', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$client_data = Pms_CommonData::getClientData($logininfo->clientid);
			$this->view->clientid = $clientid;
			$this->view->client_name = $client_data[0]['client_name'];
			
			$this->view->act = "diagnosis/adddiagnosistype";
			$this->_helper->layout->setLayout('layout');

			if($this->getRequest()->isPost())
			{

				$diagnosis_form = new Application_Form_Diagnosis();

				if($diagnosis_form->validateDiagnosis_Type($_POST))
				{
					$diagnosis_form->InsertDiagnosistypeData($_POST);
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$diagnosis_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}

		public function adddiagnosisAction()
		{
		    $this->view->genders = array("" => "Select Gender", "0" => "Divers", "1" => "Male", "2" => "Female");//ISPC-2442 @Lore   30.09.2019
			$this->view->terminals = array("" => "Select Terminal", "0" => "Terminal Key Number", "1" => "NonTerminal Key Number");

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Diagnosis', $logininfo->userid, 'canadd');


			$this->_helper->layout->setLayout('layout_popup');


			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			$this->view->act = "diagnosis/adddiagnosistype";

			if($this->getRequest()->isPost())
			{
				$diagnosis_form = new Application_Form_Diagnosis();

				if($this->diagnosis = $diagnosis_form->validate($_POST))
				{
					$diagnosis_form->InsertData($_POST);

					$fn = $_POST['main_group'];
					$this->view->closefrm = "setchild('$fn')";
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				}
				else
				{
					$diagnosis_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}
		}

		public function editdiagnosistypeAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Diagnosis', $logininfo->userid, 'canedit');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$this->view->act = "diagnosis/editdiagnosistype?id=" . $_GET['id'];
			$this->_helper->viewRenderer('adddiagnosistype');

			if($this->getRequest()->isPost())
			{

				$diagnosis_form = new Application_Form_Diagnosis();

				if($diagnosis_form->validateDiagnosis_Type($_POST))
				{
					$diagnosis_form->UpdateDiagnosistypeData($_POST);
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'diagnosis/diagnosistypelist?flg=suc');
				}
				else
				{
					$diagnosis_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}


			$diagnosis = Doctrine::getTable('DiagnosisType')->find($_GET['id']);

			if($diagnosis)
			{
				$this->retainValues($diagnosis->toArray());
			}
		}

		public function diagnosistypelistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Diagnosis', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate("recordupdatedsuccessfully");
			}
		}

		public function getjsondataAction()
		{
			$dtype = Doctrine::getTable('DiagnosisType')->findAll();

			echo json_encode($dtype->toArray());
			exit;
		}

		public function fetchlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Diagnosis', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("pk" => "id", "cl" => "clientid", "desc" => "description", "abb" => "abbrevation", "chg" => "change_date", "crd" => "create_date");

			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];

			if($logininfo->clientid > 0)
			{
				$where = ' and clientid=' . $logininfo->clientid;
			}

			$dtype = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisType')
				->where('isdelete=0 ' . $where)
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$dtypeexec = $dtype->execute();
			$dtypearray = $dtypeexec->toArray();
			$limit = 50;
			$dtype->select('*');
			$dtype->limit($limit);
			$dtype->offset($_GET['pgno'] * $limit);

			$dtypelimitexec = $dtype->execute();
			$dtypelimit = $dtypelimitexec->toArray();


			$client_data = Pms_CommonData::getClientData($logininfo->clientid);

			$grid = new Pms_Grid($dtypelimit, 1, $dtypearray[0]['count'], "listdiagnosistype.html");
			$grid->client_data = $client_data;

			$this->view->diagnosistypegrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("diagnosisnavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['diagnosistypelist'] = $this->view->render('diagnosis/fetchlist.html');

			echo json_encode($response);
			exit;
		}

		public function deletediagnosistypeAction()
		{
			$this->_helper->viewRenderer('diagnosistypelist');

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Diagnosis', $logininfo->userid, 'candelete');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			else
			{

				$thrash = Doctrine::getTable('DiagnosisType')->find($_GET['id']);
				$thrash->isdelete = 1;
				$thrash->save();
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
			}
		}

		public function fetchdropdownAction()
		{
			$this->_helper->viewRenderer('patientmasteradd');

			if(strlen($_REQUEST['ltr']) > 0)
			{

				if($_REQUEST['tb'] == "dig")
				{
					if($_REQUEST['srch'] == 'icdnumber')
					{
						$srchoption = "trim(lower(icd_primary)) like trim(lower('" . ($_REQUEST['ltr']) . "%'))";
						$callback = "icddiagnodropdiv";
						$order = 'icd_primary';
					}
					else
					{
						$search_str = htmlentities($_REQUEST['ltr'], ENT_QUOTES, "utf-8"); //stupid indians saved data as html entities
						$srchoption = "trim(lower(description)) like trim(lower('" . ($search_str) . "%'))";
						$callback = "diagnodropdiv";
						$order = 'description';
					}
				}


				$drugs = Doctrine_Query::create()
					->select('*')
					->from('Diagnosis')
					->where(" " . $srchoption . " and isdelete=0 and valid_till = '0000-00-00 00:00:00'")
					->limit('150');
				$sql = $drugs->getSqlQuery();
				$drop_array = $drugs->fetchArray();

				foreach($drop_array as $key => $val)
				{
					$drop_array[$key]['id'] = $val['id'];
					$drop_array[$key]['icd_primary'] = html_entity_decode($val['icd_primary'], ENT_QUOTES, "utf-8");
					$drop_array[$key]['description'] = html_entity_decode($val['description'], ENT_QUOTES, "utf-8");
				}
				$droparray = $drop_array;
			}
			else
			{
				$droparray = array();
			}

			if($_REQUEST['cfun'] == 3)
			{
				$callback = "diagnodropdivs";
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = $callback;
			$response['query'] = $sql;
			$response['callBackParameters'] = array();
			$response['callBackParameters']['didiv'] = $_REQUEST['did'];
			$response['callBackParameters']['diagnosisarray'] = $droparray;
			$response['callBackParameters']['tabname'] = $_REQUEST['tb'];

			echo json_encode($response);
			exit;
		}

		public function importdiagnosisAction()
		{

			if($this->getRequest()->isPost())
			{
				ini_set("upload_max_filesize", "10M");
				if(strlen($_POST['catalogue']) < 1)
				{
					$this->view->error_catalogue = $this->view->translate('providecataloguename');
					$error = 1;
				}
				if(strlen($_POST['icd_year']) < 1)
				{
					$this->view->error_year = $this->view->translate('provideyear');
					$error = 1;
				}
				if(strlen($_FILES['filename']['name']) < 1)
				{
					$this->view->error_filename = $this->view->translate('uploadcsvfile');
					$error = 1;
				}
				$filename = $_FILES['filename']['tmp_name'];

				if(strlen($_POST['delimiter']) > 0)
				{
					$delimiter = $_POST['delimiter'];
				}
				else
				{
					$delimiter = ";";
				}

				if($error == 0)
				{
					$handle = fopen($filename, "r");

					while(($data = fgetcsv($handle, NULL, $delimiter)) !== FALSE)
					{
						if($data[2] == 1)
						{
							$import = new Diagnosis();
							$import->catalogue = $_POST['catalogue'];
							$import->icd_year = $_POST['icd_year'];
							$import->detail_code = $data[1];
							$import->icd_primary = $data[3];
							$import->icd_star = $data[4];
							$import->icd_cross = $data[5];
							$import->description = $data[6];
							$import->save();
						}
					}
					$this->view->error_message = $this->view->translate('importdone');
					fclose($handle);
				}
			}
		}

		public function importdiagnosislistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Diagnosis', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			if($_GET['flg'] == 'suc')
			{
				$this->view->error_message = $this->view->translate('recordupdatedsucessfully');
			}
		}

		public function fetchimportlistAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Diagnosis', $logininfo->userid, 'canview');

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			$columnarray = array("pk" => "id", "dc" => "detail_code", "cat" => "catalogue", "des" => "description", "yr" => "icd_year", "prm" => "icd_primary", "crs" => "icd_cross", "str" => "icd_star");
			$orderarray = array("ASC" => "DESC", "DESC" => "ASC");

			$this->view->order = $orderarray[$_GET['ord']];
			$this->view->{$_GET['clm'] . "order"} = $orderarray[$_GET['ord']];
			$this->view->{"style" . $_GET['pgno']} = "active";

			$symptom = Doctrine_Query::create()
				->select('count(*)')
				->from('Diagnosis')
				->where('isdelete = ?', 0)
				->orderBy($columnarray[$_GET['clm']] . " " . $_GET['ord']);
			$symptomexec = $symptom->execute();
			$symptomarray = $symptomexec->toArray();

			$limit = 50;
			$symptom->select('*');
			$symptom->limit($limit);
			$symptom->offset($_GET['pgno'] * $limit);

			$symptomlimitexec = $symptom->execute();
			$symptomlimit = $symptomlimitexec->toArray();

			$grid = new Pms_Grid($symptomlimit, 1, $symptomarray[0]['count'], "listimportdiagnosis.html");
			$this->view->symptomgrid = $grid->renderGrid();
			$this->view->navigation = $grid->dotnavigation("icddiagnosisnavigation.html", 5, $_GET['pgno'], $limit);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['importdiagnosislist'] = $this->view->render('diagnosis/fetchimportlist.html');

			echo json_encode($response);
			exit;
		}

		public function deleteimporticdAction()
		{
			$this->_helper->viewRenderer('importdiagnosislist');

			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($this->getRequest()->isPost())
			{

				if(count($_POST['icd_id']) < 1)
				{
					$this->view->error_message = $this->view->translate('selectatleastone');
					$error = 1;
				}

				if($error == 0)
				{

					foreach($_POST['icd_id'] as $key => $val)
					{

						$thrash = Doctrine::getTable('Diagnosis')->find($val);
						$thrash->isdelete = 1;
						$thrash->save();
					}
					$this->view->error_message = $this->view->translate('recorddeletedsuccessfully');
				}
			}
		}

		public function icddiagnosiseditAction()
		{
			if($this->getRequest()->isPost())
			{

				$icd_form = new Application_Form_Diagnosis();

				if($icd_form->validateicd($_POST))
				{
					$icd_form->UpdateData($_POST);
					$this->_redirect(APP_BASE . 'diagnosis/importdiagnosislist?flg=suc');
					$this->view->error_message = $this->view->translate('recordupdatedsucessfully');
				}
				else
				{
					$icd_form->assignErrorMessages();
					$this->retainValues($_POST);
				}
			}

			$diagnosis = Doctrine::getTable('Diagnosis')->find($_GET['id']);

			if($diagnosis)
			{
				$this->retainValues($diagnosis->toArray());
			}
		}

		public function removerecordAction()
		{
			$this->_helper->viewRenderer->setNoRender();

			if($_GET['rid'] > 0)
			{
				$diagnosis = Doctrine::getTable('PatientDiagnosis')->find($_GET['rid']);
				$diagnosis->delete();
			}
			exit;
		}

		private function retainValues($values)
		{
			foreach($values as $key => $val)
			{
				$this->view->$key = $val;
			}
		}

		public function fetchicdmetaAction()
		{

			$diagno = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisIcd')
				->where("id= ? ", $_GET['mid']);

			$diagnoexe = $diagno->execute();
			if($diagnoexe)
			{
				$diagarr = $diagnoexe->toArray();
				$icdnumber = $diagarr[0]['icd_primary'];
			}

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBack'] = "callMeta";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['icdnumber'] = $icdnumber;

			echo json_encode($response);
			exit;
		}
		
		//get view list diagnosis client - ISPC-2412 Carmen 21.11.2019
		public function listclientdiagnosisAction(){
			 
			if($_REQUEST['action'])
			{
				if($_REQUEST['action'] == 'delete' && $_REQUEST['id'])
				{
					$diagr = DiagnosisTable::getInstance()->find($_REQUEST['id'], Doctrine_Core::HYDRATE_RECORD);
					 
					$diagr->isdelete = '1';
					$diagr->save();
					
					$this->_redirect(APP_BASE . "diagnosis/listclientdiagnosis");
				}
			}

			//populate the datatables
			if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
		
				$this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				
				$sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
				$sort_col_dir = $sort_col_dir == 'asc' ? 'ASC' : 'DESC';
		
				$sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
				$sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
		
				$limit = $this->getRequest()->getPost('iDisplayLength');
				$offset = $this->getRequest()->getPost('iDisplayStart');
		
				$search_value = $this->getRequest()->getPost('sSearch');
		
				$columns_array = array(
						"0" => "icd_primary",
						"1" => "description",
				);
				$columns_search_array = $columns_array;
		
				$order_by = '';
		
				$tobj = new Diagnosis(); //obj used as table
		
		
				if ( ! empty($sort_col_name) && $tobj->getTable()->hasColumn($sort_col_name)) {
					//$order_by = $sort_col_name . ' ' . $sort_col_dir;
		
					$chars[ 'Ä' ] = 'Ae';
					$chars[ 'ä' ] = 'ae';
					$chars[ 'Ö' ] = 'Oe';
					$chars[ 'ö' ] = 'oe';
					$chars[ 'Ü' ] = 'Ue';
					$chars[ 'ü' ] = 'ue';
					$chars[ 'ß' ] = 'ss';
		
					$colch =addslashes(htmlspecialchars($sort_col_name));
			   
					foreach($chars as $kch=>$vch)
					{
						$colch = 'REPLACE('.$colch.', "'.$kch.'", "'.$vch.'")';
					}
		
					$order_by ='LOWER('.$colch.') '.$sort_col_dir;
				}
		
				$tcol = $tobj->getTable()->createQuery('q');
				$tcol->select('*');
				$tcol->where('clientid = ?' , $this->logininfo->clientid);
				$tcol->andWhere("isdelete = 0");
		
				$full_count  = $tcol->count();
				$tcol_full_arr = $tcol->fetchArray();
				
				$diagids = array();
				foreach($tcol_full_arr as $kfd=>$vfd)
				{
					$diagids[] = $vfd['id'];
				}
				
				$pdiagnoses = PatientDiagnosisTable::findPatientDiagnoByDiagnoid($diagids);
				$total_passigned_cdiagno = array_unique(array_column($pdiagnoses, 'icd_id'));
				
				/* ------------- Search options ------------------------- */
				if (isset($search_value) && strlen(trim($search_value)) > 0)
				{
					$comma = '';
					$filter_string_all = '';
					 
					foreach($columns_search_array as $ks=>$vs)
					{
						$filter_string_all .= $comma.$vs;
						$comma = ',';
					}
		
					$regexp = trim($search_value);
					Pms_CommonData::value_patternation($regexp);
					 
					$searchstring = mb_strtolower(trim($search_value), 'UTF-8');
					$searchstring_input = trim($search_value);
					if(strpos($searchstring, 'ae') !== false || strpos($searchstring, 'oe') !== false || strpos($searchstring, 'ue') !== false)
					{
						if(strpos($searchstring, 'ss') !== false)
						{
							$ss_flag = 1;
						}
						else
						{
							$ss_flag = 0;
						}
						$regexp = Pms_CommonData::complete_patternation($searchstring_input, $regexp, $ss_flag);
					}
					 
					$filter_search_value_arr[] = 'CONVERT( CONCAT_WS(\' \','.$filter_string_all.' ) USING utf8 ) REGEXP ?';
					$regexp_arr[] = $regexp;
					 
					//var_dump($regexp_arr);
					$tcol->andWhere($filter_search_value_arr[0] , $regexp_arr);
					//$search_value = strtolower($search_value);
					//$fdoc1->andWhere("(lower(name) like ?)", array("%" . trim($search_value) . "%"));
					$filter_count  = $tcol->count();
				}
				else
				{
					$filter_count = $full_count;
				}
		
				if ( ! empty($order_by)) {
					$tcol->orderBy($order_by);
				}
		
				if ( ! empty($limit)) {
					$tcol->limit((int)$limit);
				}
		
				if ( ! empty($offset)) {
					$tcol->offset((int)$offset);
				}
		
				$tcol_arr = $tcol->fetchArray();
		
				$resulted_data = array();
				foreach($tcol_arr as $row)
				{
					/* if(in_array($row['id'], $total_passigned_cdiagno))
					{
						$data = array(
							'icd_primary' => 	sprintf('%s','<span>!</span>'.$row['icd_primary']),
							'description' => $row['description'],
							'actions' => '<a href="'.APP_BASE .'diagnosis/addclientdiagnosis?id='.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="1" rel="'.$row['id'].'" id="delete_'.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>'
						);
					}
					else
					{ */
						$data = array(
							'icd_primary' => 	$row['icd_primary'],
							'description' => $row['description'],
							'actions' => '<a href="'.APP_BASE .'diagnosis/addclientdiagnosis?id='.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="0" rel="'.$row['id'].'" id="delete_'.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>'
						);
					//}
					array_push($resulted_data, $data);
				}
		
				$result = array(
						'draw' => $this->getRequest()->getPost('sEcho'),
						'recordsTotal' => $full_count,
						'recordsFiltered' => $filter_count,
						'data' => $resulted_data
				);
		
				$this->_helper->json->sendJson($result);
				exit; //for readability
			}
			 
			 
		}
		public function addclientdiagnosisAction()
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
		
			if($_REQUEST['id'])
			{
				$id = $_REQUEST['id'];
			}
			 
			$saved_values = $this->_clientdiagnosis_GatherDetails($id);
			
			$form = new Application_Form_ClientDiagnosis(array(
					'_block_name'           => 'CLIENTDIAGNOSIS',
			));
		
			 
			$form->create_form_clientdiagnosis($saved_values);
		
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
		
			if($this->getRequest()->isPost())
			{
				foreach($_POST['cdiag_table'] as $kr=>$vr)
				{
					foreach($vr as $ki=>$vi)
					{
						$post[$ki] = $vi;
					}
				}
				$post['clientid'] = $_POST['clientid'];
				$post['id'] = $_POST['id'];
				$ordm  = $form->save_form_clientdiagnosis($post);
		
				if($_POST['id'])
				{
					$this->_redirect(APP_BASE . "diagnosis/listclientdiagnosis");
				}
		
			}
		}
		
		private function _clientdiagnosis_GatherDetails( $id = null)
		{
			$saved_formular_final = array();
			if ( !empty($id))
			{
				$saved_formular = DiagnosisTable::getInstance()->findOneById($id, Doctrine_Core::HYDRATE_RECORD);
			}
			
			if(!$saved_formular)
			{
				$cols= DiagnosisTable::getInstance()->getFieldNames();
				
				foreach($cols as $kr=>$vr)
				{
					if($vr == 'icd_primary' || $vr == 'description')
					{
						$saved_formular[$vr] = null;
					}
				}
			}
			//print_r($saved_formular);exit;
			foreach($saved_formular as $kcol=>$vcol)
			{
				if($kcol == 'icd_primary' || $kcol == 'description'  || $kcol == 'id' || $kcol == 'clientid')
				{
					$saved_formular_final[$kcol]['colprop'] = DiagnosisTable::getInstance()->getDefinitionOf($kcol);
			
					$saved_formular_final[$kcol]['value'] = $vcol;
				}
		
			}
			 
			//print_r($saved_formular_final); exit;
			return $saved_formular_final;
		}

	}

?>