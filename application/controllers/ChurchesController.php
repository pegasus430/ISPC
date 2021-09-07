<?php
class ChurchesController extends Zend_Controller_Action
{
	
	public $act;
	
	public function init ()
	{
		/* Initialize action controller here */
			setlocale(LC_ALL, 'de_DE.utf-8');
		
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$this->clientid = $logininfo->clientid;
			$this->userid = $logininfo->userid;
			$this->usertype = $logininfo->usertype;
			$this->filepass = $logininfo->filepass;
			if(!$logininfo->clientid)
			{
				//redir to select client error
				$this->_redirect(APP_BASE . "error/noclient");
				exit;
			}
	}
	
	public function addchurchAction ()
	{
		$this->init();

		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}

		$this->view->clickaction = "";
		$this->view->closefrm = '""';
		
		if ($_GET['popup'] == "popup")
		{
			$this->_helper->layout->setLayout('layout_popup');
			$this->view->clickaction = "setchild()";
		}
		
		/*--------------- Client  details ---------------------------------------*/
		$clientdata = Pms_CommonData::getClientData($this->clientid);
		$form['client_name'] = $clientdata[0]['client_name'];
		
		$this->view->form_data = $form;
		
		if ($this->getRequest()->isPost())
		{
			$church_form = new Application_Form_Churches();
		
			if ($church_form->validate($_POST['form']))
			{
				$church_form->insertdata($_POST['form']);
				$curr_id = $church_form->id;
				$this->view->closefrm = "setchild('$fn')"; // for closing iframe
				
				//$this->_redirect(APP_BASE . "churches/churcheslist?mes=".urlencode($this->view->translate("recordinsertedsuccessfully")));
				$this->view->error_message = $this->view->translate("recordinsertedsuccessfully");
				//exit;
			}
			else
			{
				$church_form->assignErrorMessages();
				$this->retainValues($_POST['form']);
				
			}
		}
		}
		
		private function retainValues ( $values = array() )
		{
			
		
			foreach ($values as $key=>$val)
			{				
				$this->view->form_data[$key] = $val;
			}
		}	
		
	
	
	public function editchurchAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
	
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		/*--------------- Client  details ---------------------------------------*/
		$clientdata = Pms_CommonData::getClientData($this->clientid);
		
		
		$this->_helper->viewRenderer('addchurch');
		
		//get existing data 
		if ($_GET['id'] > 0)
		{
			$chs_id = $_GET['id'];
			$chsarray = Churches::getChurch($chs_id);		
					
			if ($chsarray)
			{	
				$form = $chsarray[0];
				$form['client_name'] = $clientdata[0]['client_name'];
				$form['id'] = $chs_id;
				$this->view->form_data = $form;
				$this->retainValues($chsarray);
			}
			
		}
		
		    if($this->getRequest()->isPost())
		    {
		        if ($_GET['id'] > 0)
		        {
		            $chs_id= $_GET['id'];
		        }
		
		        $church_form = new Application_Form_Churches();
		
		        if($church_form->validate($_POST['form']))
		        {
		            $_POST['form']['id'] = $chs_id;		           
		            
		            $church_form->updatedata($_POST['form']);
		            
		            $this->_redirect(APP_BASE . "churches/churcheslist?flg=succ&mes=".urlencode($this->view->translate("recordupdatedsucessfully")));
		            exit;
		        }
		        else
		        {
		            $church_form->assignErrorMessages();
		            $this->retainValues($_POST);
		        }
		    }

		  
		}
	
	public function churcheslistAction () {
		$clientid = $this->clientid;
	
		//populate the datatables
		if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
		    
			if(!$_REQUEST['length'])
			{
			    $_REQUEST['length'] = "25";
			}
			
			$limit = $_REQUEST['length'];
			$offset = $_REQUEST['start'];
			$search_value = $_REQUEST['search']['value'];
			
			$columns_array = array(
					"0" => "name",
					"1" => "contact_firstname",
					"2" => "contact_lastname",
					"3" => "street",
					"4" => "zip",
					"5" => "city",
					"6" => "phone",
					"7" => "email"
			);
			$columns_search_array = $columns_array;
			
			if(isset($_REQUEST['order'][0]['column']))
			{
				$order_column = $_REQUEST['order'][0]['column'];
				$order_dir = $_REQUEST['order'][0]['dir'];
			}
			else
			{
				array_push($columns_array, "id");
				$nrcol = array_search ('id', $columns_array);
				$order_column = $nrcol;
				$order_dir = "ASC";
			}
			
			$order_by_str ='CONVERT(CONVERT('.addslashes(htmlspecialchars($columns_array[$order_column])).' USING BINARY) USING utf8) '.$order_dir;
			
			// ########################################
			// #####  Query for count ###############
			$chs = Doctrine_Query::create();
			$chs->select('count(*)');
			$chs->from('Churches');
			$chs->where("isdelete = 0  ");
			$chs->andWhere("clientid = ?", $clientid);
			$chs->andWhere("indrop = 0");
			
			$chsarray = $chs->fetchArray();
			$full_count = $chsarray[0]['count'];
			
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
				$chs->andWhere($filter_search_value_arr[0] , $regexp_arr);
				//$search_value = strtolower($search_value); 
				//$chs->andWhere("(lower(contact_firstname) like ? or lower(contact_lastname) like ? or lower(name) like ? or  lower(zip) like ? or  lower(city) like ? or  lower(phone) like ? or lower(email) like ?)",
				//		array("%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%"));	
			}
				
			$chsarray = $chs->fetchArray();		 
			$filter_count  = $chsarray[0]['count'];
			
			// ########################################
			// #####  Query for details ###############
			$sql = '*,';
			$chs->select($sql);
			
			$chs->orderBy($order_by_str);
			$chs->limit($limit);
			$chs->offset($offset);
			$chslimit = Pms_CommonData::array_stripslashes($chs->fetchArray());		
			
			$report_ids = array();
			foreach ($chslimit as $key => $report)
			{
				$chslimit_arr[$report['id']] = $report;
				$report_ids[] = $report['id'];
			}
			$row_id = 0;
			$link = "";
			$resulted_data = array();
			
			foreach($chslimit_arr as $report_id =>$mdata)
			{
				$link = '%s ';
				 
				$resulted_data[$row_id]['name'] = sprintf($link,$mdata['name']);
				$resulted_data[$row_id]['contact_firstname'] = sprintf($link,$mdata['contact_firstname']);
				$resulted_data[$row_id]['contact_lastname'] = sprintf($link,$mdata['contact_lastname']);
				$resulted_data[$row_id]['street'] = sprintf($link,$mdata['street']);
				$resulted_data[$row_id]['zip'] = sprintf($link,$mdata['zip']);
				$resulted_data[$row_id]['city'] = sprintf($link,$mdata['city']);
				$resulted_data[$row_id]['phone'] = sprintf($link,$mdata['phone']);
				$resulted_data[$row_id]['email'] = sprintf($link,$mdata['email']);
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'churches/editchurch?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
				$row_id++;
			}
			
			$response['draw'] = (int)$_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
			
			$this->_helper->json->sendJson($response);
		} 
	}
	
	public function deletechurchAction ()
	{		
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		 if ($_GET['id'] > 0)
		        {
		            $chs_id= $_GET['id'];
		        }
		
		        $church_form = new Application_Form_Churches();
		        $church_form->deletedata($chs_id);
		
		
		
		        $this->_redirect(APP_BASE . "churches/churcheslist?flg=succ&mes=".urlencode($this->view->translate("recorddeletedsucessfully")));
		        exit;
		    
	}
	
}

?>