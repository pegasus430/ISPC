<?php
/**
 * in this file add your List-> menuxxx action for with you don't allready have a separate controller
 * #ISPC-2512PatientCharts 
 */

class ClientlistsController extends Pms_Controller_Action {
	
	public function init()
	{
// 		$_options = array('packagesPrefix'        =>  'Package',
// 				'packagesPath'          =>  '',
// 				'packagesFolderName'    =>  'packages',
// 				'suffix'                =>  '.php',
// 				'generateBaseClasses'   =>  true,
// 				'generateTableClasses'  =>  true,
// 				'generateAccessors'     =>  true,
// 				'baseClassPrefix'       =>  'Base',
// 				'baseClassesDirectory'  =>  'generated',
// 				'baseClassName'         =>  'Doctrine_Record',
// 				'pearStyle'             => true,
// 				'classPrefix'           => '',
// 				'classPrefixFiles'      => false,
// 				'phpDocPackage'		=> 'ISPC',
// 				'phpDocSubpackage'	=> 'Application ('. date("Y-m-d").')',
// 				'phpDocName'		=> 'ancuta',
// 				'phpDocEmail'		=> 'office@originalware.com',
// 		);
		
		
		
// 		Doctrine_Core::generateModelsFromDb(
// // 				'/home/www/ispc20172/application/models2',
// 				'/home/www/ispc20172/public/uploads',
// // 				array('MDAT', 'SYSDAT', 'IDAT'),
// 				array('SYSDAT'),
// 				$_options
// 		);
// 		die_ancuta();

		$this
		->setActionsWithJsFile([
				/*
				 * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
		*/
				'artificialentriesexitslist',
		        'organicentriesexitslist',         //ISPC-2520 Lore 08.04.2020
		        'icdopsmresettingslist',           //ISPC-2654 Lore 06.10.2020
		        'clientproblemslist',           //ISPC-2654 Lore 06.10.2020
		]);
		
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
	
	public function nutritionformularlistoldAction()
	{
		//if canedit = 0 - don't allow any additions or changes
		$has_edit_permissions = Links::checkLinkActionsPermission();
		$this->view->has_edit_permissions = $has_edit_permissions; 
		
		if ( ! $this->getRequest()->isXmlHttpRequest()) {

			if ( !empty($_GET['delete']) && !empty($_GET['id']) && $_GET['delete'] == "1" ) 
			{
				//mark one as deleted
				if(!$has_edit_permissions)
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}				
				
				$nfl = new NutritionFormularList();
				$nfl->delete_by_id_and_clientid( $_GET['id'], $this->clientid);
			} 
			else if(isset($_POST['edit_form'])) {
				//save or update 
				if(!$has_edit_permissions)
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$this->nutritionformularlisteditAction();
				
			}
			
			
			
			return;
			
		}
		
		
		//fetch simple page
		
		$this->_helper->layout->setLayout('layout_ajax');
		$this->_helper->viewRenderer->setNoRender();
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$clientid = $this->clientid;
	

		$limit = empty($_REQUEST['length']) ? 50 : (int)$_REQUEST['length'];
		$offset = (int)$_REQUEST['start'];
		
		$search_value = $_REQUEST['search']['value'];
		 
		if(!empty($_REQUEST['order'][0]['column'])){
			$order_column = $_REQUEST['order'][0]['column'];
		} else{
			$order_column = "1";
		}
		$order_dir = (strtoupper($_REQUEST['order'][0]['dir']) == "ASC") ? "ASC" : "DESC";
		 
		$columns_array = array(
				"1" => "field_value"
		);
		 
		$order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
		 
		// ########################################
		// #####  Query for count ###############
		$fdoc1 = Doctrine_Query::create();
		$fdoc1->select('count(*) as count');
		$fdoc1->from('NutritionFormularList');
		$fdoc1->Where("clientid = ?" , $clientid);
		$fdoc1->andWhere("isdelete = 0");
		$fdoc1->andWhere("field_name = 'application'");
		$fdoc1->limit(1);
		
		/* ------------- Search options ------------------------- */
		if (isset($search_value) && strlen($search_value) > 0)
		{
			$fdoc1->andWhere("field_value LIKE ?" , array('%'.trim($search_value).'%'));
		}
		$fdocarray = $fdoc1->fetchOne(null, DOCTRINE_CORE::HYDRATE_ARRAY );
		$full_count  = $fdocarray['count'];
		
		// ########################################
		// #####  Query for details ###############
		$fdoclimit_arr = array();
		if ($full_count > 0) {
			$sql = 'id, field_name, field_value';
			$fdoc1->select($sql);
			$fdoc1->orderBy($order_by_str);
			$fdoc1->limit($limit);
			$fdoc1->offset($offset);

			$fdoclimit_arr = $fdoc1->fetchArray();		 
		}
	
		$row_id = 0;
		$link = "";
		$resulted_data = array();
		
		foreach($fdoclimit_arr as $k => $row){
			
			$link = '%s ';
			 
			$resulted_data[$row_id]['field_value'] = sprintf($link, $row['field_value']);

			if ($this->view->has_edit_permissions) {
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'clientlists/nutritionformularlistedit?id='.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$row['id'].'" id="delete_'.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
			} else {
				$resulted_data[$row_id]['actions'] = "&nbsp;";
			}
			
			$row_id++;
		}
		 
		$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		$response['recordsTotal'] = $full_count;
		$response['recordsFiltered'] = $full_count; // ??
		$response['data'] = $resulted_data;
	
		header("Content-type: application/json; charset=UTF-8");
	
	
		echo json_encode($response);
		exit;
	}
	
	
	public function nutritionformularlisteditAction()
	{		
		//save some data
		if( $this->getRequest()->isPost() ) {
				
			$nfl = new NutritionFormularList();
			
			if(! empty($_POST['id']) && (int)$_POST['id'] > 0) {
				//update old value
				$nfl->set_old_row($this->clientid , $_POST['field_value'] , (int)$_POST['id']);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE . 'clientlists/nutritionformularlist?flg=succ&mes='.urlencode($this->view->error_message) );
			} else {
				//insert new value
				$nfl->set_new_row($this->clientid , $_POST['field_value']);
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
			}
			
			return;	
		}
		
		//else fetch data for the viewer
		
		$id = ( ! empty($_GET['id'])) ? (int)$_GET['id'] : 0;
		
		$this->view->id = $id;
		$this->view->field_value = '';
		
		if ( $id ) {
			$nfl = new NutritionFormularList();
			$details = $nfl->get_by_id_and_clientid($id, $this->clientid);
			
			$this->view->field_value = $details['field_value'];
		}
		
	}
	
	
	public function registertextslistAction()
	{
		//if canedit = 0 - don't allow any additions or changes
		$has_edit_permissions = Links::checkLinkActionsPermission();
		$this->view->has_edit_permissions = $has_edit_permissions; 
		
		if ( ! $this->getRequest()->isXmlHttpRequest()) {

			if ( !empty($_GET['delete']) && !empty($_GET['id']) && $_GET['delete'] == "1" ) 
			{
				//mark one as deleted
				if(!$has_edit_permissions)
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}				
				
				$nfl = new RegisterTextsList();
				$nfl->delete_row( $_GET['id'], $this->clientid);
			}
			 
			else if(isset($_POST['edit_form'])) {
				//save or update 
				if(!$has_edit_permissions)
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$this->registertextslisteditAction();
				
			}
			
			
			
			return;
			
		}
		
		
		//fetch simple page
		
		$this->_helper->layout->setLayout('layout_ajax');
		$this->_helper->viewRenderer->setNoRender();
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$clientid = $this->clientid;
		// Get register_textareas
		$register_textareas= Pms_CommonData::getRegisterTextareas();
		
		$limit = empty($_REQUEST['length']) ? 50 : (int)$_REQUEST['length'];
		$offset = (int)$_REQUEST['start'];
		
		$search_value = $_REQUEST['search']['value'];
		 
		if(!empty($_REQUEST['order'][0]['column'])){
			$order_column = $_REQUEST['order'][0]['column'];
		} else{
			$order_column = "1";
		}
		$order_dir = (strtoupper($_REQUEST['order'][0]['dir']) == "ASC") ? "ASC" : "DESC";
		 
		$columns_array = array(
				"1" => "field_value"
		);
		 
		$order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
		 
		// ########################################
		// #####  Query for count ###############
		$fdoc1 = Doctrine_Query::create();
		$fdoc1->select('count(*) as count');
		$fdoc1->from('RegisterTextsList');
		$fdoc1->Where("clientid = ?" , $clientid);
		$fdoc1->limit(1);
		
		/* ------------- Search options ------------------------- */
		if (isset($search_value) && strlen($search_value) > 0)
		{
			$fdoc1->andWhere("field_value LIKE ? OR field_name LIKE ?" , array('%'.trim($search_value).'%','%'.trim($search_value).'%'));
		}
		$fdocarray = $fdoc1->fetchOne(null, DOCTRINE_CORE::HYDRATE_ARRAY );
		$full_count  = $fdocarray['count'];
		
		// ########################################
		// #####  Query for details ###############
		$fdoclimit_arr = array();
		if ($full_count > 0) {
			$sql = 'id, field_name, field_value';
			$fdoc1->select($sql);
			$fdoc1->orderBy($order_by_str);
			$fdoc1->limit($limit);
			$fdoc1->offset($offset);

			$fdoclimit_arr = $fdoc1->fetchArray();		 
		}
	
		$row_id = 0;
		$link = "";
		$resulted_data = array();
		
		foreach($fdoclimit_arr as $k => $row){
			
			$link = '%s ';
			 
			$resulted_data[$row_id]['field_name'] = sprintf($link, $register_textareas[$row['field_name']]);
			$resulted_data[$row_id]['field_value'] = sprintf($link, $row['field_value']);

			if ($this->view->has_edit_permissions) {
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'clientlists/registertextslistedit?id='.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$row['id'].'" id="delete_'.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
			} else {
				$resulted_data[$row_id]['actions'] = "&nbsp;";
			}
			
			$row_id++;
		}
		 
		$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		$response['recordsTotal'] = $full_count;
		$response['recordsFiltered'] = $full_count; // ??
		$response['data'] = $resulted_data;
	
		header("Content-type: application/json; charset=UTF-8");
	
	
		echo json_encode($response);
		exit;
	}
	
	
	public function registertextslisteditAction()
	{		
		//save some data
		if( $this->getRequest()->isPost() ) {
				
			$rtl = new RegisterTextsList();
			
			if(! empty($_POST['id']) && (int)$_POST['id'] > 0) {
				//update old value
				$edit_data = array();
				$edit_data['record_id'] = $_POST['id'];
				$edit_data['clientid'] = $this->clientid;
				$edit_data['field_value'] = $_POST['field_value'];
				$edit_data['field_name'] = $_POST['field_name'];
				$rtl->set_old_record($edit_data);
			} else {
				//insert new value
				$save_data = array();
				$save_data['clientid'] = $this->clientid;
				$save_data['field_value'] = $_POST['field_value'];
				$save_data['field_name'] = $_POST['field_name'];
				$rtl->set_new_record($save_data);
			}
			
			$this->redirect(APP_BASE . 'clientlists/registertextslist' );
			return;	
		}
		
		//else fetch data for the viewer
		// Get register_textareas
		$register_textareas= Pms_CommonData::getRegisterTextareas();
		//print_r($register_textareas); exit;
		$this->view->register_textareas = $register_textareas;
		$id = ( ! empty($_GET['id'])) ? (int)$_GET['id'] : 0;
		
		$this->view->id = $id;
		$this->view->field_value = '';
		
		if ( $id ) {
			$rtl = new RegisterTextsList();
			$details = $rtl->get_by_id_and_clientid($id, $this->clientid);
			
			$this->view->field_value = $details['field_value'];
			$this->view->field_name = $details['field_name'];
		}
	}
	
	
	/**
	 * Create a general list
	 *   where multiple "forms" can be saved here  
	 */
	
	
	public function formstextslistoldAction()
	{
		//if canedit = 0 - don't allow any additions or changes
		$has_edit_permissions = Links::checkLinkActionsPermission();
		$this->view->has_edit_permissions = $has_edit_permissions; 
		
		if ( ! $this->getRequest()->isXmlHttpRequest()) {

			if ( !empty($_GET['delete']) && !empty($_GET['id']) && $_GET['delete'] == "1" ) 
			{
				//mark one as deleted
				if(!$has_edit_permissions)
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}				
				
				$nfl = new FormsTextsList();
				$nfl->delete_row( $_GET['id'], $this->clientid);
			}
			 
			else if(isset($_POST['edit_form'])) {
				//save or update 
				if(!$has_edit_permissions)
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				
				$this->formstextslisteditAction();
			}
			return;
		}
		
		
		
		$formtexts_lang = $this->view->translate("formstextslist_lang");
		// Get available forms and textareas
		$forms_textareas= Pms_CommonData::getFormsTextareas();
		
		foreach($forms_textareas as $form_name => $form_fields_arr){
			$links_perm[] =  $form_name;
		}
		// check menu permissions
		$form_perm = TabMenus::getMenubyLink($links_perm);
		foreach($form_perm as $kpl=>$pml){
			$allowed_forms[] = $pml['TabMenus']['menu_link'];
		}
		
		// check module for register
		$multiplestamps_previleges = new Modules();
		if($multiplestamps_previleges->checkModulePrivileges("126", $this->clientid))
		{
			$allowed_forms[] = "patientnew/hospizregisterv3"; // added separatly because it has no menu corespondent
		}
		//fetch simple page
		
		$this->_helper->layout->setLayout('layout_ajax');
		$this->_helper->viewRenderer->setNoRender();
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$clientid = $this->clientid;
		
		$limit = empty($_REQUEST['length']) ? 50 : (int)$_REQUEST['length'];
		$offset = (int)$_REQUEST['start'];
		
		$search_value = $_REQUEST['search']['value'];
		 
		if(!empty($_REQUEST['order'][0]['column'])){
			$order_column = $_REQUEST['order'][0]['column'];
		} else{
			$order_column = "1";
		}
		$order_dir = (strtoupper($_REQUEST['order'][0]['dir']) == "ASC") ? "ASC" : "DESC";
		
		$columns_array = array(
				"0" => "form_name",
				"1" => "field_name",
				"2" => "field_value"
		);
		 
		$order_by_str = $columns_array[$order_column].' '.$order_dir.' ';

		// ########################################
		// #####  Query for count ###############
		$fdoc1 = Doctrine_Query::create();
		$fdoc1->select('count(*) as count');
		$fdoc1->from('FormsTextsList');
		$fdoc1->Where("clientid = ?" , $clientid);
		$fdoc1->andWhereIn("form_name" , $allowed_forms);
		$fdoc1->limit(1);
		/* ------------- Search options ------------------------- */
		if (isset($search_value) && strlen($search_value) > 0)
		{
			$fdoc1->andWhere("field_value LIKE ? OR field_name LIKE ?" , array('%'.trim($search_value).'%','%'.trim($search_value).'%'));
		}
		$fdocarray = $fdoc1->fetchOne(null, DOCTRINE_CORE::HYDRATE_ARRAY );
		$full_count  = $fdocarray['count'];
		
		// ########################################
		// #####  Query for details ###############
		$fdoclimit_arr = array();
		if ($full_count > 0) {
			$sql = 'id, field_name, field_value,form_name';
			$fdoc1->select($sql);
			$fdoc1->orderBy($order_by_str);
			$fdoc1->limit($limit);
			$fdoc1->offset($offset);

			$fdoclimit_arr = $fdoc1->fetchArray();		 
		}
	
		$row_id = 0;
		$link = "";
		$resulted_data = array();
		
		$form_name_tr = "";
		$field_name_tr = "";
		
		foreach($fdoclimit_arr as $k => $row){
			$link = '%s ';
			$form_name_tr = $formtexts_lang[$row['form_name']][$row['form_name']];
			$field_name_tr = $formtexts_lang[$row['form_name']][$row['field_name'].'_tr'];
			$resulted_data[$row_id]['form_name'] = sprintf($link, $form_name_tr );
			$resulted_data[$row_id]['field_name'] = sprintf($link, $field_name_tr);
			$resulted_data[$row_id]['field_value'] = sprintf($link, $row['field_value']);

			if ($this->view->has_edit_permissions) {
				$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'clientlists/formstextslistedit?id='.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$row['id'].'" id="delete_'.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
			} else {
				$resulted_data[$row_id]['actions'] = "&nbsp;";
			}
			
			$row_id++;
		}
		 
		$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
		$response['recordsTotal'] = $full_count;
		$response['recordsFiltered'] = $full_count; // ??
		$response['data'] = $resulted_data;
	
		header("Content-type: application/json; charset=UTF-8");
		
		echo json_encode($response);
		exit;
	}
	
	
	public function formstextslisteditAction()
	{		
		
		$formtexts_lang = $this->view->translate("formstextslist_lang");
		// Get available forms and textareas
		$forms_textareas= Pms_CommonData::getFormsTextareas();

		
		foreach($forms_textareas as $form_name => $form_fields_arr){
			$links_perm[] =  $form_name;
		}
		// check menu permissions
		$form_perm = TabMenus::getMenubyLink($links_perm);
		foreach($form_perm as $kpl=>$pml){
			$allowed_forms[] = $pml['TabMenus']['menu_link'];
		}
		
		// check module for register
		$multiplestamps_previleges = new Modules();
		if($multiplestamps_previleges->checkModulePrivileges("126", $this->clientid))
		{
			$allowed_forms[] = "patientnew/hospizregisterv3"; // added separatly because it has no menu corespondent
		}

		// ISPC-2507 Lore 31.01.2020
		// check module for Pharmacy medi check
		$pharmacy_medicheck_previleges = new Modules();
		if($pharmacy_medicheck_previleges->checkModulePrivileges("214", $this->clientid) || $pharmacy_medicheck_previleges->checkModulePrivileges("216", $this->clientid))
		{
		    $allowed_forms[] = "patientmedication/requestchanges"; // added separatly because it has no menu corespondent
		}
		//.
		
		/*
		 * ISPC-2292 - "Todo" texts are now added via MamboAssessment
		 * "Todo" is not specific to a singe form, so will not be bound to a single form/link,
		 * @cla added the 'anyform' @since 20.02.2019
		 */
		$allowed_forms[] = 'anyform';
		
		foreach($forms_textareas as $form_name => $form_fields_arr){
			if(in_array($form_name, $allowed_forms)){
				foreach($form_fields_arr as $from_field_value => $form_field_label){
					$field2form[$from_field_value] = $form_name ;
					$formtexts_arr[ $formtexts_lang[$form_name][$form_name]][$from_field_value] = $formtexts_lang[$form_name][$form_field_label];
				}
			}
		}
		
		//save some data
		if( $this->getRequest()->isPost() ) {

			$rtl = new FormsTextsList();
			
			if(! empty($_POST['id']) && (int)$_POST['id'] > 0) {
				//update old value
				$edit_data = array();
				$edit_data['record_id'] = $_POST['id'];
				$edit_data['clientid'] = $this->clientid;
				$edit_data['form_name'] = $field2form[$_POST['field_name']];
				$edit_data['field_name'] = $_POST['field_name'];
				$edit_data['field_value'] = $_POST['field_value'];
				$rtl->set_old_record($edit_data);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE . 'clientlists/formstextslist?flg=succ&mes='.urlencode($this->view->error_message));
			} else {
				//insert new value
				$save_data = array();
				$save_data['clientid'] = $this->clientid;
				$save_data['form_name'] = $field2form[$_POST['field_name']];
				$save_data['field_name'] = $_POST['field_name'];
				$save_data['field_value'] = $_POST['field_value'];
				$rtl->set_new_record($save_data);
				$this->view->error_message = $this->view->translate("recordinsertsucessfully");
				
				$this->_redirect(APP_BASE . 'clientlists/formstextslistedit?flg=succ&mes='.urlencode($this->view->error_message));
			}
			
			return;	
		}
		
		//else fetch data for the viewer

		$forms_details['forms_textareas'] = $formtexts_arr;
		
		$id = ( ! empty($_GET['id'])) ? (int)$_GET['id'] : 0;
		
		$forms_details['id'] = $id;
		$forms_details['field_value'] = '';
		
		
		if ( $id ) {
			$rtl = new FormsTextsList();
			$details = $rtl->get_by_id_and_clientid($id, $this->clientid);
			
			$forms_details['from_name'] = $details['form_name'];
			$forms_details['field_name'] = $details['field_name'];
			$forms_details['field_value'] = $details['field_value'];
		}
		
		$this->view->forms_details = $forms_details;
	}
	
	//get view list nutrition formular
	public function nutritionformularlistAction(){
		$clientid = $this->clientid;
		//if canedit = 0 - don't allow any additions or changes
		$has_edit_permissions = Links::checkLinkActionsPermission();
		//$this->view->has_edit_permissions = $has_edit_permissions;
		
		if ( ! $this->getRequest()->isXmlHttpRequest()) {
		
			if ( !empty($_GET['delete']) && !empty($_GET['id']) && $_GET['delete'] == "1" )
			{		
				$nfl = new NutritionFormularList();
				$nfl->delete_by_id_and_clientid( $_GET['id'], $this->clientid);
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE.'clientlists/nutritionformularlist?flg=suc&mes='.urlencode($this->view->error_message));
			}
		}
		//populate the datatables
		if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			if(!$_REQUEST['length']){
				$_REQUEST['length'] = "25";
			}
			$limit = (int)$_REQUEST['length'];
			$offset = (int)$_REQUEST['start'];
			$search_value = addslashes($_REQUEST['search']['value']);
	
			$columns_array = array(
					"0" => "field_value"
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
			$fdoc1 = Doctrine_Query::create();
			$fdoc1->select('count(*)');
			$fdoc1->from('NutritionFormularList');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0 ");
			$fdoc1->andWhere("field_name = 'application'");
	
			$fdocarray = $fdoc1->fetchArray();
			$full_count  = $fdocarray[0]['count'];
	
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
				$fdoc1->andWhere($filter_search_value_arr[0] , $regexp_arr);
				//$search_value = strtolower($search_value);
				//$fdoc1->andWhere("(lower(field_value) like ?)", array("%" . trim($search_value) . "%"));
			}
	
			$fdocarray = $fdoc1->fetchArray();
			$filter_count  = $fdocarray[0]['count'];
	
			// ########################################
			// #####  Query for details ###############
			$fdoc1->select('*');
			
			$fdoc1->orderBy($order_by_str);
			$fdoc1->limit($limit);
			$fdoc1->offset($offset);
	
			$fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
	
			$report_ids = array();
			$fdoclimit_arr = array();
			foreach ($fdoclimit as $key => $report)
			{
				$fdoclimit_arr[$report['id']] = $report;
				$report_ids[] = $report['id'];
			}
	
			$row_id = 0;
			$link = "";
			
			$resulted_data = array();
			foreach($fdoclimit_arr as $report_id =>$mdata)
			{
				$link = '%s';
				$resulted_data[$row_id]['field_value'] = sprintf($link,$mdata['field_value']);
	
				if($has_edit_permissions) 
				{
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'clientlists/nutritionformularlistedit?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
				}
				$row_id++;
			}
	
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}
	
	}
	
	//get view list forms texts
	public function formstextslistAction(){
		$clientid = $this->clientid;
		//if canedit = 0 - don't allow any additions or changes
		$has_edit_permissions = Links::checkLinkActionsPermission();
		//$this->view->has_edit_permissions = $has_edit_permissions;
	
		if ( ! $this->getRequest()->isXmlHttpRequest()) {
	
			if ( !empty($_GET['delete']) && !empty($_GET['id']) && $_GET['delete'] == "1" )
			{
				$nfl = new FormsTextsList();
				$nfl->delete_row( $_GET['id'], $this->clientid);
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE.'clientlists/formstextslist?flg=suc&mes='.urlencode($this->view->error_message));
			}
		}

		//populate the datatables
		if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
			$formtexts_lang = $this->view->translate("formstextslist_lang");
			// Get available forms and textareas
			$forms_textareas= Pms_CommonData::getFormsTextareas();
			
			foreach($forms_textareas as $form_name => $form_fields_arr){
				$links_perm[] =  $form_name;
			}
			// check menu permissions
			$allowed_forms = array();
			$form_perm = TabMenus::getMenubyLink($links_perm);
			foreach($form_perm as $kpl=>$pml){
				$allowed_forms[] = $pml['TabMenus']['menu_link'];
			}
			
			// check module for register
			$multiplestamps_previleges = new Modules();
			if($multiplestamps_previleges->checkModulePrivileges("126", $this->clientid))
			{
				$allowed_forms[] = "patientnew/hospizregisterv3"; // added separatly because it has no menu corespondent
			}
			
			// ISPC-2507 Lore 31.01.2020 // Maria:: Migration ISPC to CISPC 08.08.2020
			// check module for Pharmacy medi check
			$pharmacy_medicheck_previleges = new Modules();
			if($pharmacy_medicheck_previleges->checkModulePrivileges("214", $this->clientid) || $pharmacy_medicheck_previleges->checkModulePrivileges("216", $this->clientid))
			{
			    $allowed_forms[] = "patientmedication/requestchanges"; // added separatly because it has no menu corespondent
			}
			//.
			
			/*
			 * ISPC-2292 - "Todo" texts are now added via MamboAssessment
			 * "Todo" is not specific to a singe form, so will not be bound to a single form/link, 
			 * @cla added the 'anyform' @since 20.02.2019
			 */
			$allowed_forms[] = 'anyform';
			
			
			if ( empty($allowed_forms)){
			    $allowed_forms[] = "999999";
			}
			
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			if(!$_REQUEST['length']){
				$_REQUEST['length'] = "25";
			}
			$limit = (int)$_REQUEST['length'];
			$offset = (int)$_REQUEST['start'];
			$search_value = addslashes($_REQUEST['search']['value']);
	
			$columns_array = array(
					"0" => "form_name_tr",
					"1" => "field_name_tr",
					"2" => "field_value"
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
			$fdoc1 = Doctrine_Query::create();
			$fdoc1->select('count(*)');
			$fdoc1->from('FormsTextsList');
			$fdoc1->where("clientid = ?", $clientid);
			$fdoc1->andWhere("isdelete = 0 ");
			$fdoc1->andWhereIn("form_name" , $allowed_forms);
	
			$fdocarray = $fdoc1->fetchArray();
			$full_count  = $fdocarray[0]['count'];
	
			// ########################################
			// #####  Query for details ###############
			$fdoc1->select('*');
			
			if($order_column == '2')
			{
				$fdoc1->orderBy($order_by_str);
			}
			
			$fdoclimit = $fdoc1->fetchArray();
			
			foreach ($fdoclimit as $key=> $row) {
				$row['form_name_tr'] = $formtexts_lang[$row['form_name']][$row['form_name']];
				$row['field_name_tr'] = $formtexts_lang[$row['form_name']][$row['field_name'].'_tr'];
				$fdoclimit[$key] = $row;
			}
			
			if(trim($search_value) != "")
			{	
				$regexp = trim($search_value);
				Pms_CommonData::value_patternation($regexp);
				
				foreach($columns_search_array as $ks=>$vs)
				{
					$pairs[$vs] = trim(str_replace('\\', '',$regexp));
					
				}
				//var_dump($pairs);
				$fdocsearch = array();
				foreach ($fdoclimit as $skey => $sval) {
					foreach ($pairs as $pkey => $pval) {
						$pval_arr = explode('|', $pval);
					
						foreach($pval_arr as $kpval=>$vpval)
						{
							if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) { 
								$fdocsearch[$skey] = $sval;
								break;
							}
						}
						
					}
				
					}
				
				 
				$fdoclimit = $fdocsearch;
			}
			$filter_count  = count($fdoclimit);
			//var_dump($full_count);
				
			if($order_column != '2')
			{				
				$sort_col = array();
    			foreach ($fdoclimit as $key=> $row)
    			{
    				$row[$columns_array[$order_column].'_tr'] = mb_strtolower($row[$columns_array[$order_column]], 'UTF-8');
    				$fdoclimit[$key] = $row;
    				$sort_col[$key] = $row[$columns_array[$order_column].'_tr'];
    			}
    			if($order_dir == 'desc')
    			{
    				$dir = SORT_DESC;
    			}
    			else
    			{
    				$dir = SORT_ASC;
    			}
    			array_multisort($sort_col, $dir, $fdoclimit);
    			
    			$keyw = $columns_array[$order_column].'_tr';
    			array_walk($fdoclimit, function (&$v) use ($keyw) {
    				unset($v[$keyw]);
    			});
			}
			
			if($limit != "")
			{
				$fdoclimit = array_slice($fdoclimit, $offset, $limit, true);
			}
			$fdoclimit = Pms_CommonData::array_stripslashes($fdoclimit);
		
			$report_ids = array();
			$fdoclimit_arr = array();
			foreach ($fdoclimit as $key => $report)
			{
				$fdoclimit_arr[$report['id']] = $report;
				$report_ids[] = $report['id'];
			}
	
			$row_id = 0;
			$link = "";
	
			$resulted_data = array();
			foreach($fdoclimit_arr as $report_id =>$mdata)
			{
				$link = '%s';
				$resulted_data[$row_id]['form_name_tr'] = sprintf($link,$mdata['form_name_tr']);
				$resulted_data[$row_id]['field_name_tr'] = sprintf($link,$mdata['field_name_tr']);
				$resulted_data[$row_id]['field_value'] = sprintf($link,$mdata['field_value']);
	
				if($has_edit_permissions)
				{
					$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'clientlists/formstextslistedit?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
				}
				$row_id++;
			}
	
			$response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
			$response['recordsTotal'] = $full_count;
			$response['recordsFiltered'] = $filter_count; // ??
			$response['data'] = $resulted_data;
	
			$this->_helper->json->sendJson($response);
		}
	
	}
	

	/**
	 * ISPC-2508 Carmen 15.01.2020 
	 * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
	 */
	public function artificialentriesexitslistAction()
	{
		if($_REQUEST['action'])
		{
			if($_REQUEST['action'] == 'delete' && $_REQUEST['id'])
			{
				$artee = ArtificialEntriesExitsListTable::getInstance()->find($_REQUEST['id']);
				 
				$artee->delete();

				$this->_redirect(APP_BASE . "clientlists/artificialentriesexitslist");
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
	
			$category = $this->getRequest()->getPost('category');
	
			$search_value = $this->getRequest()->getPost('sSearch');
	
			$columns_array = array(
					"0" => "name",
					"1" => "type_name",
					"2" => "localization_available_name",
					"3" => "days_availability",
			);
			$columns_search_array = $columns_array;
	
			$order_by = '';
	
			//$tobj = new ArtificialEntriesExitsList(); //obj used as table
	
	
			if ( ! empty($sort_col_name) && ArtificialEntriesExitsListTable::getInstance()->hasColumn($sort_col_name)) {
				//$order_by = $sort_col_name . ' ' . $sort_col_dir;
				if($sort_col_idx == '3')
				{
					$order_by = $sort_col_name . " " .$sort_col_dir;
				}
				else if($sort_col_idx == '0')
				{
	
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
			}
	
			$tcol = ArtificialEntriesExitsListTable::getInstance()->createQuery('q');
			$tcol->select('*');
			$tcol->where('clientid = ?' , $this->logininfo->clientid);
			
			if ( ! empty($order_by)) {
				$tcol->orderBy($order_by);
			}
	
			$full_count  = $tcol->count();

			$tcol_arr = $tcol->fetchArray();
			
			$totalartids = array_column($tcol_arr, 'id');
			$totalartassigned = PatientArtificialEntriesExitsTable::getInstance()->findAllByClientArtIds($totalartids);
			$totalartidsassigned = array_column($totalartassigned, 'option_id');

			foreach ($tcol_arr as $key => $row) {
				$row['type_name'] = $this->getColumnMapping('type')[$row['type']];
				$row['localization_available_name'] = $this->getColumnMapping('localization_available')[$row['localization_available']];
				$tcol_arr[$key] = $row;
			}
			
			if($sort_col_idx == "1" || $sort_col_idx == '2')
			{
				$sort_col = array();
    			foreach ($tcol_arr as $key=> $row)
    			{
    				$row[$columns_array[$sort_col_idx].'_tr'] = mb_strtolower($row[$columns_array[$sort_col_idx]], 'UTF-8');
    				$tcol_arr[$key] = $row;
    				$sort_col[$key] = $row[$columns_array[$sort_col_idx].'_tr'];
    			}
    			if($sort_col_dir == 'DESC')
    			{
    				$dir = SORT_DESC;
    			}
    			else
    			{
    				$dir = SORT_ASC;
    			}
    			array_multisort($sort_col, $dir, $tcol_arr);
    			
    			$keyw = $columns_array[$sort_col_idx].'_tr';
    			array_walk($tcol_arr, function (&$v) use ($keyw) {
    				unset($v[$keyw]);
    			});
			}
			
			if (isset($search_value) && strlen(trim($search_value)) > 0)
			{
				$regexp = trim($search_value);
				Pms_CommonData::value_patternation($regexp);
			
				foreach($columns_search_array as $ks=>$vs)
				{
					$pairs[$vs] = trim(str_replace('\\', '',$regexp));
						
				}
				//var_dump($pairs);
				$fdocsearch = array();
				foreach ($tcol_arr as $skey => $sval) {
					foreach ($pairs as $pkey => $pval) {
						$pval_arr = explode('|', $pval);
							
						foreach($pval_arr as $kpval=>$vpval)
						{
							if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) {
								$fdocsearch[$skey] = $sval;
								break;
							}
						}
			
					}
				}
			
					
				$tcol_arr = $fdocsearch;
			}
			$filter_count  = count($tcol_arr);
			
			if($limit != "")
			{
				$tcol_arr = array_slice($tcol_arr, $offset, $limit, true);
			}
			$tcol_arr = Pms_CommonData::array_stripslashes($tcol_arr);			
			
			$resulted_data = array();
			
			foreach($tcol_arr as $row)
			{
				$data = array(
						'name' => in_array($row['id'], $totalartidsassigned) ? sprintf('%s','<span>!</span>'.$row['name']) : $row['name'],
						'type' => $row['type_name'],
						'localization_available' => $row['localization_available_name'],
						'days_availability' => $row['days_availability'],
						'actions' => in_array($row['id'], $totalartidsassigned) 
						? '<a href="'.APP_BASE .'clientlists/artificialentryexitadd?id='.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="1"  rel="'.$row['id'].'" id="delete_'.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>'
						: '<a href="'.APP_BASE .'clientlists/artificialentryexitadd?id='.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="0" rel="'.$row['id'].'" id="delete_'.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>'
				);
				
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
	
	/**
	 * ISPC-2508 Carmen 16.01.2020
	 * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
	 */
	public function artificialentryexitaddAction()
	{
		$has_edit_permissions = Links::checkLinkActionsPermission();
		if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
		{
			$this->_redirect(APP_BASE . "error/previlege");
			exit;
		}
		
		$stamold = Stammdatenerweitert::getOldArtificialEntriesExits();
	
		if($_REQUEST['id'])
		{
			$id = $_REQUEST['id'];
		}
		 
		$saved_values = $this->_cliententryexitadd_GatherDetails($id);
		
		$form = new Application_Form_ClientArtificialEntryExit(array(
				'_block_name'           => '',
				'_old_entries_exits' => $stamold,
		));
	
		 
		$form->create_form_clientartificialentryexit($saved_values);
	
	
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
			foreach($_POST['artificial_entry_exit'] as $kr=>$vr)
			{
				foreach($vr as $ki=>$vi)
				{
					$post[$ki] = $vi;
					if($ki == "Ausscheidung" || $ki == "Kunstliche" || $ki == "Ernahrung")
					{
						$post['old_name'] = array($ki => $vi);
					}
				}
			}
			$post['clientid'] = $_POST['clientid'];
			$post['id'] = $_POST['id'];

			
			$ordm  = $form->save_form_clientartificialentryexit($post);
	
			if($_POST['id'])
			{
				$this->_redirect(APP_BASE . "clientlists/artificialentriesexitslist");
			}
	
		}
	}
	/**
	 * ISPC-2508 Carmen 16.01.2020
	 * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
	 */
	private function _cliententryexitadd_GatherDetails( $id = null)
	{
		$saved_formular_final = array();
		if ( !empty($id))
		{
			$saved_formular = ArtificialEntriesExitsListTable::getInstance()->findOneBy('id', $id, Doctrine_Core::HYDRATE_RECORD);
		}
		 
		if(!$saved_formular)
		{
			$cols= ArtificialEntriesExitsListTable::getInstance()->getFieldNames();
			foreach($cols as $kr=>$vr)
			{
				$saved_formular[$vr] = null;
			}
		}
		//print_r($saved_formular);exit;
		foreach($saved_formular as $kcol=>$vcol)
		{
			if($kcol == 'create_date' || $kcol == 'create_user' ||$kcol == 'change_date' ||$kcol == 'change_user' || $kcol == 'isdelete') continue;
			$saved_formular_final[$kcol]['colprop'] = ArtificialEntriesExitsListTable::getInstance()->getDefinitionOf($kcol);
	
			$saved_formular_final[$kcol]['value'] = $vcol;
			
			if($saved_formular_final[$kcol]['colprop']['type'] == 'enum')
			{
				$saved_formular_final[$kcol]['colprop']['values'] = $this->getColumnMapping($kcol);
			}
	
		}
		 
		//print_r($saved_formular_final); exit;
		return $saved_formular_final;
	}
	
	/**
	 * ISPC-2508 Carmen 16.01.2020
	 * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
	 */
	private function getColumnMapping($fieldName, $revers = false)
	{
	
		//             $fieldName => [ value => translation]
		$overwriteMapping = [
				'localization_available' => ['no' =>  'Nein', 'yes' =>  'Ja'],
				'type' => ['entry' => 'Zugang', 'exit' => 'Ausgang']
	
		];
	
	
		$values = ArtificialEntriesExitsListTable::getInstance()->getEnumValues($fieldName);
	
			
		$values = array_combine($values, array_map($this->translator->translate, $values));
	
		if (isset($overwriteMapping[$fieldName])) {
			$values = $overwriteMapping[$fieldName] + $values;
		}
		
		return $values;
	
	}
	
/**
 * ISPC-2520 Lore 08.04.2020
 */
	public function organicentriesexitslistAction()
	{
	    
	    if($_REQUEST['action'])
	    {
	        
	        if($_REQUEST['action'] == 'delete' && $_REQUEST['id'])
	        {
	            
	            $orgee = OrganicEntriesExitsListsTable::getInstance()->findById($_REQUEST['id']);
	            
	            if($orgee)
	            {
    	            $orgee->delete();
	            }
	            
	            $this->_redirect(APP_BASE . "clientlists/organicentriesexitslist");
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
	            "0" => "name",
	            "1" => "type_name",
	        );
	        $columns_search_array = $columns_array;
	        
	        $order_by = '';
	        
	        //$tobj = new ArtificialEntriesExitsList(); //obj used as table
	        
	        
	        if ( ! empty($sort_col_name) && OrganicEntriesExitsListsTable::getInstance()->hasColumn($sort_col_name)) {
	            //$order_by = $sort_col_name . ' ' . $sort_col_dir;
	            $chars = array();
	            if($sort_col_idx == '3')
	            {
	                $order_by = $sort_col_name . " " .$sort_col_dir;
	            }
	            else if($sort_col_idx == '0')
	            {
	                
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
	        }
	        
	        $tcol = OrganicEntriesExitsListsTable::getInstance()->createQuery('q');
	        $tcol->select('*');
	        $tcol->where('clientid = 0 OR clientid = ?' , $this->logininfo->clientid);
	        
	        if ( ! empty($order_by)) {
	            $tcol->orderBy($order_by);
	        }
	        
	        $full_count  = $tcol->count();
	        
	        $tcol_arr = $tcol->fetchArray();
	     
	        
	        $row_type = Array(
	            'entry'=>"entry", 
	            'exit'=>"exit" );
	        
	        foreach ($tcol_arr as $key => $row) {
	            
	            $row['type_name'] = $row_type[$row['type']];
	            $tcol_arr[$key] = $row;
	        }
	        
	        if($sort_col_idx == "1" || $sort_col_idx == '2')
	        {
	            $sort_col = array();
	            foreach ($tcol_arr as $key=> $row)
	            {
	                $row[$columns_array[$sort_col_idx].'_tr'] = mb_strtolower($row[$columns_array[$sort_col_idx]], 'UTF-8');
	                $tcol_arr[$key] = $row;
	                $sort_col[$key] = $row[$columns_array[$sort_col_idx].'_tr'];
	            }
	            if($sort_col_dir == 'DESC')
	            {
	                $dir = SORT_DESC;
	            }
	            else
	            {
	                $dir = SORT_ASC;
	            }
	            array_multisort($sort_col, $dir, $tcol_arr);
	            
	            $keyw = $columns_array[$sort_col_idx].'_tr';
	            array_walk($tcol_arr, function (&$v) use ($keyw) {
	                unset($v[$keyw]);
	            });
	        }
	        
	        if (isset($search_value) && strlen(trim($search_value)) > 0)
	        {
	            $regexp = trim($search_value);
	            Pms_CommonData::value_patternation($regexp);
	            
	            $pairs = array();
	            foreach($columns_search_array as $ks=>$vs)
	            {
	                $pairs[$vs] = trim(str_replace('\\', '',$regexp));
	                
	            }

	            $fdocsearch = array();
	            foreach ($tcol_arr as $skey => $sval) {
	                foreach ($pairs as $pkey => $pval) {
	                    $pval_arr = explode('|', $pval);
	                    
	                    foreach($pval_arr as $kpval=>$vpval)
	                    {
	                        if (array_key_exists($pkey, $sval) && strpos(mb_strtolower($sval[$pkey], 'UTF-8'), $vpval) !== false) {
	                            $fdocsearch[$skey] = $sval;
	                            break;
	                        }
	                    }
	                    
	                }
	            }
	            
	            
	            $tcol_arr = $fdocsearch;
	        }
	        $filter_count  = count($tcol_arr);
	        
	        if($limit != "")
	        {
	            $tcol_arr = array_slice($tcol_arr, $offset, $limit, true);
	        }
	        $tcol_arr = Pms_CommonData::array_stripslashes($tcol_arr);
	        
	        $resulted_data = array();
	        
	        foreach($tcol_arr as $row)
	        {
	            $data = array(
	                'name' => $row['name'],
	                'type' => $row['type_name'],
	                'shortcut' => $row['shortcut'],
	                'actions' => !empty($row['clientid'])
	                ? '<a href="'.APP_BASE .'clientlists/organicentryexitadd?id='.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="1"  rel="'.$row['id'].'" id="delete_'.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>'
	                : ''
	            );
	                //: '<a href="'.APP_BASE .'clientlists/organicentryexitadd?id='.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="0" rel="'.$row['id'].'" id="delete_'.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>'

	            
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
	
/**
 * ISPC-2520 Lore 08.04.2020
 */
	public function organicentryexitaddAction()
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
	    
	    $saved_values = $this->_organicentryexitadd_GatherDetails($id);
	    
	    $form = new Application_Form_ClientOrganicEntryExit(array(
	        '_block_name'           => '',
	    ));
	    
	    
	    $form->create_form_clientorganicentryexit($saved_values);
	    
	    
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
	        $post = array();
	        foreach($_POST['organic_entry_exit'] as $kr=>$vr)
	        {
	            foreach($vr as $ki=>$vi)
	            {
	                $post[$ki] = $vi;

	            }
	        }
	        $post['clientid'] = $_POST['clientid'];
	        $post['id'] = $_POST['id'];
	        	        
	        $ordm  = $form->save_form_clientorganicentryexit($post);

	        $this->_redirect(APP_BASE . "clientlists/organicentriesexitslist");

	        
	    }
	}
	
    /**
     * ISPC-2520 Lore 08.04.2020
     * @param unknown $id
     * @return array|unknown|number
     */
	private function _organicentryexitadd_GatherDetails( $id = null)
	{
	    $saved_formular_final = array();
	    if ( !empty($id))
	    {
	        $saved_formular = OrganicEntriesExitsListsTable::getInstance()->findOneBy('id', $id, Doctrine_Core::HYDRATE_RECORD);
	    }
	    
	    if(!$saved_formular)
	    {
	        $cols= OrganicEntriesExitsListsTable::getInstance()->getFieldNames();
	        foreach($cols as $kr=>$vr)
	        {
	            $saved_formular[$vr] = null;
	        }
	    }
	    
	    $row_type = Array(
	        'entry'=>"entry ",
	        'exit'=>"exit " );
	    
	    foreach($saved_formular as $kcol=>$vcol)
	    {
	        if($kcol == 'category' || $kcol == 'create_date' || $kcol == 'create_user' ||$kcol == 'change_date' ||$kcol == 'change_user' || $kcol == 'isdelete') continue;
	        $saved_formular_final[$kcol]['colprop'] = OrganicEntriesExitsListsTable::getInstance()->getDefinitionOf($kcol);
	        
	        $saved_formular_final[$kcol]['value'] = $vcol;
	        
	        if($saved_formular_final[$kcol]['colprop']['type'] == 'enum')
	        {
	        $saved_formular_final[$kcol]['colprop']['values'] = $row_type;
	        }
	        
	    }
	    
	    return $saved_formular_final;
	}
	
	/**
	 * ISPC-2654 Lore 05.10.2020
	 */
	public function icdopsmresettingslistAction()
	{
	    $clientid = $this->clientid;
	    //if canedit = 0 - don't allow any additions or changes
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    
	    if($_REQUEST['action'])
	    {
	        if($_REQUEST['action'] == 'delete' && $_REQUEST['id'])
	        {
	            $where_del = strpos($_REQUEST['id'], 'sort_');
	            if($where_del !== false){
	                $del_id = substr($_REQUEST['id'], 5);
	                $iomsort = IcdOpsMreSortingTable::getInstance()->find($del_id);
	                $iomsort->delete();
	            } else {
	                $ioms = IcdOpsMreSettingsTable::getInstance()->find($_REQUEST['id']);
	                $ioms->delete();
	            }
	            
	            $this->_redirect(APP_BASE . "clientlists/icdopsmresettingslist");
	        }
	    }
	    
	    $category_default = Pms_CommonData::get_diagnosis_category_default();
	    $sorting_default = Pms_CommonData::get_sort_column_diagnosis_default();
	    
	    $sorting  = IcdOpsMreSorting::get_sorting_columns();

	    //view sorting-ul in page 
	    $ioms_sort_arr = IcdOpsMreSorting::getIcdOpsMreSorting($clientid);
	    if(empty($ioms_sort_arr)) {
	        $ioms_sort_all = $sorting_default;
	    } else {
	        $ioms_sort_all = $ioms_sort_arr;
	    }
	    
	    foreach($ioms_sort_all as $ki=>$vals){
	        $ioms_sort_all[$ki]['main_sort_col']= $this->view->translate($sorting[$vals['main_sort_col']]);
	        $ioms_sort_all[$ki]['secondary_sort_col']= $this->view->translate($sorting[$vals['secondary_sort_col']]);
	        $ioms_sort_all[$ki]['sort_order']= $vals['sort_order'] == 1 ? 'Aufsteigend' : 'Absteigend';
	       
	        if($has_edit_permissions ){
	            //$ioms_sort_all[$ki]['actions'] = '<a href="'.APP_BASE .'clientlists/icdopsmresortingadd?id='.$vals['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="sort_'.$vals['id'].'" id="delete_sort_'.$vals['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
             if(isset($vals['id'])) {
	                $ioms_sort_all[$ki]['actions'] = '<a href="'.APP_BASE .'clientlists/icdopsmresortingadd?id='.$vals['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="sort_'.$vals['id'].'" id="delete_sort_'.$vals['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
	            } else {
	                //$ioms_sort_all[$ki]['actions'] = "";
	                $ioms_sort_all[$ki]['actions'] = '<a href="'.APP_BASE .'clientlists/icdopsmresortingadd?id='.$vals['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a>';
	            }
	        }
	    }
	    $this->view->ioms_sort_arr = $ioms_sort_all;
	    //.
	    
	    //populate the datatables
	    if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
	        
	        $ioms_all = array();
	        $ioms_arr = IcdOpsMreSettings::getIcdOpsMreSettings($clientid);
	        
	        foreach($ioms_arr as $k=>$vioms){
	            $ioms_all[$vioms['category']]=$vioms;
	        }
	        
	        foreach($category_default as $k=>$vcd){
	           if(array_key_exists($vcd['category'], $ioms_all) === false) {
	               $ioms_all[$vcd['category']] = $vcd;
	           }
	         }
	        
	        
	        $row_id = 0;
	        $link = "";
	        $resulted_data = array();
	        
	        foreach($ioms_all as $key =>$mdata) {
	            
	            $link = '%s';
	            $category = IcdOpsMreSettings::getCategory();
	            
	            $resulted_data[$row_id]['category'] = sprintf($link,$category[$mdata['category']]);

	            $has_hash = strpos($mdata['color'], '#');
	            if($has_hash !== false){
	                $resulted_data[$row_id]['color'] = '<div class="icon_color_placeholder" style="background: ' . $mdata['color'] . '"></div>';
	            }else {
	                $resulted_data[$row_id]['color'] = '<div class="icon_color_placeholder" style="background: #' . $mdata['color'] . '"></div>';
	            }
	            $resulted_data[$row_id]['shortcut'] = sprintf($link,$mdata['shortcut']);
	            //$resulted_data[$row_id]['sort_order'] = $mdata['sort_order'] == 1 ? 'ASC' : 'DESC';
	            
	            if($has_edit_permissions ){
/* 	                if(isset($mdata['id'])) {
	                    $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'clientlists/icdopsmresettingsadd?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
	                } else {
	                    $resulted_data[$row_id]['actions'] = "";
	                } */
	                $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'clientlists/icdopsmresettingsadd?id='.$mdata['id'].'&categ='.$mdata['category'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
	            }
	            $row_id++;
	        }
	        
	        $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
	        $response['recordsTotal'] = $full_count;
	        $response['recordsFiltered'] = $filter_count; // ??
	        $response['data'] = $resulted_data;
	        
	        $this->_helper->json->sendJson($response);
	        	        
	    }
	    
	}
	
	/**
	 * ISPC-2654 Lore 06.10.2020
	 */
	public function icdopsmresettingsaddAction()
	{
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	    //dd($_REQUEST);
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $ioms = IcdOpsMreSettings::getIcdOpsMreSettings($clientid);
	    
	    $id = ( ! empty($_GET['id'])) ? (int)$_GET['id'] : 0;
	    
	    $saved_values = array();
	    
	    if ($id) {
	        $saved_formular = IcdOpsMreSettingsTable::getInstance()->findOneBy('id', $id, Doctrine_Core::HYDRATE_ARRAY);
	    }
	    	    
	    if(!$saved_formular) {
	        
	        $cols= IcdOpsMreSettingsTable::getInstance()->getFieldNames();
	        foreach($cols as $kr=>$vr)
	        {
	            $saved_formular[$vr] = null;
	        }
	        
	        foreach($saved_formular as $kcol=>$vcol)
	        {
	            if($kcol == 'create_date' || $kcol == 'create_user' ||$kcol == 'change_date' ||$kcol == 'change_user' || $kcol == 'isdelete') continue;
	            
	            $saved_values[$kcol] = $vcol;
	        }
	        //dd($saved_values);
	        if(!empty($_GET['categ'])){
	            $saved_values['category'] = $_GET['categ'];
	        }
	    } else {
	        $saved_values = $saved_formular;
	        
	    }

	    
	    $form = new Application_Form_IcdOpsMreSettings(array(
	        '_block_name'           => '',
	        '_old_icd_ops_mre' => $ioms,
	    ));
	    
	    $form->create_form_IcdOpsMreSettings($saved_values);
	    $this->view->form = $form;
	    
	    if($this->getRequest()->isPost())
	    {
	        $post = $_POST;
	        
	        $ioms_form = new Application_Form_IcdOpsMreSettings();
	        $ioms  = $ioms_form->save_form_icd_ops_mre($post);
	        
            $this->_redirect(APP_BASE . "clientlists/icdopsmresettingslist");

	    }
	    
	}
	
	/**
	 * ISPC-2654 Lore 06.10.2020
	 */
	public function icdopsmresortingaddAction()
	{
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
	    {
	        $this->_redirect(APP_BASE . "error/previlege");
	        exit;
	    }
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $ioms = IcdOpsMreSorting::getIcdOpsMreSorting($clientid);
	    
	    $id = ( ! empty($_GET['id'])) ? (int)$_GET['id'] : 0;
	    
	    $saved_values = array();
	    
	    if ($id) {
	        $saved_formular = IcdOpsMreSortingTable::getInstance()->findOneBy('id', $id, Doctrine_Core::HYDRATE_ARRAY);
	    }
	    
	    if(!$saved_formular) {
	        
	        $cols= IcdOpsMreSortingTable::getInstance()->getFieldNames();
	        foreach($cols as $kr=>$vr)
	        {
	            $saved_formular[$vr] = null;
	        }
	        
	        foreach($saved_formular as $kcol=>$vcol)
	        {
	            if($kcol == 'create_date' || $kcol == 'create_user' ||$kcol == 'change_date' ||$kcol == 'change_user' || $kcol == 'isdelete') continue;
	            
	            $saved_values[$kcol] = $vcol;
	        }
	        
	    } else {
	        $saved_values = $saved_formular;
	        
	    }
	    
	    
	    $form = new Application_Form_IcdOpsMreSorting(array(
	        '_block_name'           => '',
	        '_old_icd_ops_mre_sorting' => $ioms,
	    ));
	    
	    $form->create_form_IcdOpsMreSorting($saved_values);
	    $this->view->form = $form;
	    
	    
	    if($this->getRequest()->isPost())
	    {
	        $post = $_POST;
	        
	        $ioms_form = new Application_Form_IcdOpsMreSorting();
	        $ioms  = $ioms_form->save_form_icd_ops_mre_sorting($post);
	        
            $this->_redirect(APP_BASE . "clientlists/icdopsmresettingslist");

	    }
	    
	}
	
	
	
	
	
	/**
	 * ISPC-2864 Ancuta 12.04.2021
	 */
	public function clientproblemslistAction(){
	    $clientid = $this->clientid;
	    //if canedit = 0 - don't allow any additions or changes
	    $has_edit_permissions = Links::checkLinkActionsPermission();
	    //$this->view->has_edit_permissions = $has_edit_permissions;
	    
	    if ( ! $this->getRequest()->isXmlHttpRequest()) {
	        
	        if ( !empty($_GET['delete']) && !empty($_GET['id']) && $_GET['delete'] == "1" )
	        {
	            
	            if ( $update = Doctrine::getTable('ClientProblemsList')->findOneByIdAndClientid($_GET['id'], $this->clientid)) {
	                $update->isdelete = 1;
	                $update->save();
	            }
	            
	            $this->view->error_message = $this->view->translate("recorddeletedsucessfully");
	            $this->_redirect(APP_BASE.'clientlists/clientproblemslist?flg=suc&mes='.urlencode($this->view->error_message));
	        }
	    }
	    //populate the datatables
	    if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
	        $this->_helper->layout()->disableLayout();
	        $this->_helper->viewRenderer->setNoRender(true);
	        if(!$_REQUEST['length']){
	            $_REQUEST['length'] = "25";
	        }
	        $limit = (int)$_REQUEST['length'];
	        $offset = (int)$_REQUEST['start'];
	        $search_value = addslashes($_REQUEST['search']['value']);
	        
	        $columns_array = array(
	            "0" => "problem_name"
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
	        $fdoc1 = Doctrine_Query::create();
	        $fdoc1->select('count(*)');
	        $fdoc1->from('ClientProblemsList');
	        $fdoc1->where("clientid = ?", $clientid);
	        $fdoc1->andWhere("custom = 0 ");
	        $fdoc1->andWhere("isdelete = 0 ");
	        $fdocarray = $fdoc1->fetchArray();
	        $full_count  = $fdocarray[0]['count'];
	        
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
	            $fdoc1->andWhere($filter_search_value_arr[0] , $regexp_arr);
	            //$search_value = strtolower($search_value);
	            //$fdoc1->andWhere("(lower(field_value) like ?)", array("%" . trim($search_value) . "%"));
	        }
	        
	        $fdocarray = $fdoc1->fetchArray();
	        $filter_count  = $fdocarray[0]['count'];
	        
	        // ########################################
	        // #####  Query for details ###############
	        $fdoc1->select('*');
	        
	        $fdoc1->orderBy($order_by_str);
	        $fdoc1->limit($limit);
	        $fdoc1->offset($offset);
	        
	        $fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
	        
	        $report_ids = array();
	        $fdoclimit_arr = array();
	        foreach ($fdoclimit as $key => $report)
	        {
	            $fdoclimit_arr[$report['id']] = $report;
	            $report_ids[] = $report['id'];
	        }
	        
	        $row_id = 0;
	        $link = "";
	        
	        $resulted_data = array();
	        foreach($fdoclimit_arr as $report_id =>$mdata)
	        {
	            $link = '%s';
	            $resulted_data[$row_id]['problem_name'] = sprintf($link,$mdata['problem_name']);
	            
	            if($has_edit_permissions)
	            {
	                $resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'clientlists/clientproblemslistedit?id='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
	            }
	            $row_id++;
	        }
	        
	        $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
	        $response['recordsTotal'] = $full_count;
	        $response['recordsFiltered'] = $filter_count; // ??
	        $response['data'] = $resulted_data;
	        
	        $this->_helper->json->sendJson($response);
	    }
	    
	}
	
	/**
	 * ISPC-2864 Ancuta 12.04.2021
	 */
	public function clientproblemslisteditAction()
	{
            // save some data
        if ($this->getRequest()->isPost()) {

            if (empty($_POST['problem_name'])) {
                
                $this->view->error_message = $this->view->translate("plese_entername");
                $this->_redirect(APP_BASE . 'clientlists/clientproblemslistedit?flg=succ&mes=' . urlencode($this->view->error_message));
                
            } else {

                $nfl = new ClientProblemsList();

                $data_id = isset($_POST['id']) ? $_POST['id'] : null;
                $rObj = ClientProblemsListTable::getInstance()->findOrCreateOneBy(
                    ['id','clientid'], 
                    [$data_id,$this->clientid], 
                    $_POST);

                if (! empty($_POST['id']) && (int) $_POST['id'] > 0) {
                    // update old value
                    $this->view->error_message = $this->view->translate("recordupdatedsucessfully");
                } else {
                    // insert new value
                    $this->view->error_message = $this->view->translate("recordinsertsucessfully");
                }
                $this->_redirect(APP_BASE . 'clientlists/clientproblemslist?flg=succ&mes=' . urlencode($this->view->error_message));

                return;
            }
        }

        // else fetch data for the viewer

        $id = (! empty($_GET['id'])) ? (int) $_GET['id'] : 0;

        $this->view->id = $id;
        $this->view->problem_name = '';

        if ($id) {
            $nfl = new ClientProblemsList();
            $details = $nfl->get_by_id_and_clientid($id, $this->clientid);

            $this->view->problem_name = $details['problem_name'];
        }
    }
	

}


?>