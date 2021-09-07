<?php
/**
 * 
 * @author carmen  
 * Aug 21, 2019 
 *
 */
class SurveyController extends Pms_Controller_Action {
	
	public function init()
	{	
		array_push($this->actions_with_js_file, "settings");
		array_push($this->actions_with_patientinfo_and_tabmenus, "settings"); //??
	}
	
	public function indexAction()
	{
		$clientid = $this->logininfo->clientid;
		//$clientid = 21; // ??
		
		$ipos_form = new Application_Form_ClientSurveySettings();
// 		$result = $ipos_form->create_emails( $clientid);
		
// 		dd($result);
		$email_log = SurveyEmailsLogTable::findAllByClientid($clientid);
		dd($email_log);
		echo $this->view->tabulate($email_log , array("class"=>"datatable"));
	}
	
	public function settingsAction()
	{
		$request = $this->getRequest();
		
		$form = new Application_Form_ClientSurveySettings();
		
		$get_nice_name_multiselect = $this->_get_nice_name_multiselect();
		$form->create_settings_form(array(
				'nice_name_multiselect' =>	$get_nice_name_multiselect	
		));
		
		if ($request->isPost()) {
	
			if ( $form->isValid($request->getPost())) 
			{
				$post = $request->getPost();
				
				$post['clientid'] = $this->logininfo->clientid;
				
				$form->save($post);
				
				$this->_helper->flashMessenger->addMessage( $this->translate('message_info_suc'),  'SuccessMessages');							
				return $this->_helper->redirector('settings');
			}
		} else {
			//get old saved valus
			$res = SurveyEmailSettingsTable::findByClientid($this->logininfo->clientid);
			
			if( ! empty($res[0])) {
				$form->populate($res[0]);
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
		
		//get the tokens list
		$tokens_obj = new Pms_Tokens('Survey');
		$email_tokens = $tokens_obj->getTokens4Viewer();
				
		$this->view->email_tokens = $email_tokens['prefixed_array_viewer'];
		
	}	
	
	private function _get_nice_name_multiselect ()
	{
			
		$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
			
		$todousersarr = array(
				"0" => $this->view->translate('select'),
				$selectbox_separator_string['all'] => $this->view->translate('all')
		);
			
		$usergroup = new Usergroup();
		$todogroups = $usergroup->getClientGroups($this->logininfo->clientid);
		$grouparraytodo = array();
		foreach ($todogroups as $group)
		{
			$grouparraytodo[$selectbox_separator_string['group'] .  $group['id']] = trim($group['groupname']);
		}
	
		if (isset( $this->{'_patientMasterData'}['User'])){
			$userarray = $this->{'_patientMasterData'}['User'];
		} else {
			$users = new User();
			$userarray = $users->getUserByClientid($this->logininfo->clientid);
		}
		User::beautifyName($userarray);
			
		$userarraytodo = array();
		foreach ($userarray as $user)
		{
			$userarraytodo[$selectbox_separator_string['user'] . $user['id']] = $user['nice_name'];
		}
	
		asort($userarraytodo);
		asort($grouparraytodo);
			
		$todousersarr[$this->view->translate('group_name')] = $grouparraytodo;
		$todousersarr[$this->view->translate('users')] = $userarraytodo;
	
		$user_pseudo =  new UserPseudoGroup();
		$user_ps =  $user_pseudo->get_pseudogroups_for_todo($this->logininfo->clientid);
		$pseudogrouparraytodo = array();
		if ( ! empty ($user_ps)) {
	
			//pseudogroup must have users in order to display
			$user_ps_ids =  array_column($user_ps, 'id');
			$user_pseudo_users = new PseudoGroupUsers();
			$users_in_pseudogroups = $user_pseudo_users->get_users_by_groups($user_ps_ids);
	
			foreach($user_ps as $row) {
				if ( ! empty($users_in_pseudogroups[$row['id']]))
					$pseudogrouparraytodo[$selectbox_separator_string['pseudogroup'] . $row['id']] = $row['servicesname'];
			}
				
			$todousersarr[$this->view->translate('liste_user_pseudo_group')] = $pseudogrouparraytodo;
		}
	
		return $todousersarr;
	}
	

}