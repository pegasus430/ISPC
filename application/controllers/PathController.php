<?php
class PathController extends Zend_Controller_Action
{

	public function init ()
	{
		/* Initialize action controller here */
		$patient_privileges = PatientPermissions::checkPermissionOnRun();
		if (!$patient_privileges)
		{
			$this->_redirect(APP_BASE . 'error/previlege');
		}
	}

	private function retainValues ( $values, $prefix = '' )
	{

		foreach ($values as $key => $val)
		{
			if (!is_array($val))
			{
				$this->view->$key = $val;
			}
			else
			{//retain 1 level array used in multiple hospizvbulk form
				foreach ($val as $k_val => $v_val)
				{
					if (!is_array($v_val))
					{
						$this->view->{$prefix . $key . $k_val} = $v_val;
					}
				}
			}
		}
	}
	
	public function saveorganisationchartAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$patid = $_REQUEST['id'];
		$decid = Pms_Uuid::decrypt($patid);
		$ipid = Pms_CommonData::getIpid($decid);
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->setLayout('layout_ajax');
	
	
		if($this->getRequest()->isPost() && strlen($ipid) > 0)
		{
			$path_form = new Application_Form_PatientSteps();
			$insert_path = $path_form->insert_data($_POST, $ipid, $clientid);
			echo "1";
			exit;
			
		}
	}
	
	
	public function organisationAction ()
	{
		$patid = $_REQUEST['id'];
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$decid = Pms_Uuid::decrypt($patid);
		$ipid = Pms_CommonData::getIpid($decid);
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$this->view->patid = $patid;

		$patientmaster = new PatientMaster();
		$this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
		$this->view->tabmenus = TabMenus::getMenuTabs();

		/* ######################################################### */
		$isclient = Pms_CommonData::getPatientClient($decid, $clientid);

		if (!$isclient)
		{
			$this->_redirect(APP_BASE . "overview/overview");
		}
		/* ######################################################### */
		if ($this->getRequest()->isPost())
		{
			$path_form = new Application_Form_PatientSteps();
			$insert_path = $path_form->insert_data($_POST, $ipid, $clientid);

			$this->_redirect(APP_BASE . "path/organisation?id=" . $_REQUEST['id']);
			exit;
		}

		$paths = new OrgPaths();
		$client_paths = $paths->get_paths($clientid);

		foreach ($client_paths as $k_c_path => $v_c_path)
		{
			$result = $paths->{$v_c_path['function']}($ipid);
			if ($result)
			{
				if (!$data)
				{
					$data[$ipid] = array();
				}
				$data[$ipid] = array_merge_recursive($data[$ipid], $result[$ipid][$v_c_path['function']]);
			}
		}
		
		if($data[$ipid])
		{

			$final_steps = array();
			$final_steps = $this->pathshierarchy($clientid, $data[$ipid]);
			$this->view->path_html_structured = $this->pathshtml($final_steps);
		}
		else
		{
			echo "Keine";
		}
	}

	//PATH ADD/DEL/LIST
	public function pathlistAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$hidemagic = Zend_Registry::get('hidemagic');
		$this->view->hidemagic = $hidemagic;

		$paths = new OrgPaths();
		$client_paths = $paths->get_paths($clientid);
		$this->view->paths = $client_paths;


		//delete
		if ($_REQUEST['phid'])
		{
			$paths_form = new Application_Form_Paths();
			$delete_path = $paths_form->delete_path($_REQUEST['phid'], $clientid);

			$this->_redirect(APP_BASE . "path/pathlist");
			exit;
		}
	}

	public function addpathAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$client = new Client();
		$clients_array = $client->getClientData();

		foreach ($clients_array as $k_client => $v_client)
		{
			$clients_sel_arr[$v_client['id']] = $v_client['client_name'];
		}

		$this->view->clients_sel_arr = $clients_sel_arr;
		$this->view->clients = $clients_array;

		if ($this->getRequest()->isPost())
		{
			$path_form = new Application_Form_Paths();
			$insert_path = $path_form->insert_data($_POST);

			$this->_redirect(APP_BASE . "path/pathlist");
			exit;
		}
	}

	//STEPS ADD/DEL/LIST
	public function stepslistAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$paths = new OrgPaths();
		$client_paths = $paths->get_paths($clientid);

		foreach ($client_paths as $k_c_path => $v_c_path)
		{
			$client_paths_details[$v_c_path['id']] = $v_c_path;
			$client_paths_ids[] = $v_c_path['id'];
		}

		$this->view->client_paths = $client_paths_details;

		$final_steps = array();
		$this->getmodulehierarchy($final_steps, $client_paths_ids, '0', ' ');
		$this->view->client_steps_ordered = $final_steps;

		//delete
		if ($_REQUEST['stid'])
		{
			$steps_form = new Application_Form_Steps();
			$delete_path = $steps_form->delete_step($_REQUEST['stid']);

			$this->_redirect(APP_BASE . "path/stepslist");
			exit;
		}
	}

	public function addstepAction ()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;


		if ($this->getRequest()->isPost())
		{
			$steps = new Application_Form_Steps();
			$insert_step = $steps->insert_data($_POST);
			if ($insert_step)
			{
				$this->_redirect(APP_BASE . "path/stepslist");
				exit;
			}
			else
			{
				$this->retainValues($_POST);
			}
		}

		$paths = new OrgPaths();
		$client_paths = $paths->get_paths($clientid);

		foreach ($client_paths as $k_c_path => $v_c_path)
		{
			$client_paths_selector[$v_c_path['id']] = $v_c_path['name'];
			$client_paths_ids[] = $v_c_path['id'];
		}

		$final_steps = array();
		$this->getmodulehierarchy($final_steps, $client_paths_ids, '0', ' ');

		$final_steps_arr[0] = $this->view->translate('please_select');
		foreach ($final_steps as $k_final_steps => $v_final_steps)
		{
			$final_steps_arr[$v_final_steps['id']] = $v_final_steps['space'] . $v_final_steps['name'];
		}
		$this->view->client_paths = $client_paths_selector;
		$this->view->available_steps = $final_steps_arr;
	}

	private function getmodulehierarchy ( &$final_steps, $client_paths, $parentid, $space )
	{
		$steps_q = Doctrine_Query::create()
		->select('*, CONVERT(`name` using latin1) as name, CONVERT(`shortcut` using latin1) as shortcut')
		->from('OrgSteps')
		->where('master ="' . $parentid . '"')
		->andWhereIn('path', $client_paths)
		->andWhere('isdelete = "0"')
		->orderBy("shortcut, order", 'ASC');

		$steps_res = $steps_q->fetchArray();

		foreach ($steps_res as $key => $val)
		{
			$details = array(
					'id' => $val['id'],
					'path' => $val['path'],
					'master' => $val['master'],
					'name' => $val['name'],
					'shortcut' => $val['shortcut'],
					'tabname' => $val['tabname'],
					'ismanual' => $val['ismanual'],
					'order' => $val['order'],
					'space' => $space
			);
			array_push($final_steps, $details);
			$this->getmodulehierarchy($final_steps, $client_paths, $val['id'], $space . "&nbsp;&nbsp;&nbsp;");
		}

		return;
	}


	public function pathshierarchy ( $clientid, $paths_data = false)
	{
		$paths = new OrgPaths();
		$client_paths = $paths->get_paths($clientid);

		foreach ($client_paths as $k_c_path => $v_c_path)
		{
			$steps['top_level'][$v_c_path['id']] = $v_c_path;
		}

		$steps_q = Doctrine_Query::create()
		->select('*, CONVERT(`name` using latin1) as name, CONVERT(`shortcut` using latin1) as shortcut')
		->from('OrgSteps')
		->WhereIn('path', array_keys($steps['top_level']))
		->andWhere('isdelete = "0"')
		->orderBy("path, master, shortcut, order", 'ASC');

		$steps_res = $steps_q->fetchArray();

		foreach ($steps_res as $step)
		{
			if ($step['master'] > 0)
			{ //item has ancestors
				$steps['children'][$step['master']][$step['id']] = $step;
				if ($paths_data && array_key_exists($step['shortcut'], $paths_data))
				{
					$steps['children'][$step['master']][$step['id']]['status'] = $paths_data[$step['shortcut']]['status'];
					$steps['children'][$step['master']][$step['id']]['value'] = $paths_data[$step['shortcut']]['value'];
					$steps['children'][$step['master']][$step['id']]['step_identification'] = $paths_data[$step['shortcut']]['step_identification'];
				}
			}
			else
			{

				$steps['children']['p' . $step['path']][$step['id']] = $step;

				if ($paths_data && array_key_exists($step['shortcut'], $paths_data))
				{
					$steps['children']['p' . $step['path']][$step['id']]['status'] = $paths_data[$step['shortcut']]['status'];
					$steps['children']['p' . $step['path']][$step['id']]['value'] = $paths_data[$step['shortcut']]['value'];
					$steps['children']['p' . $step['path']][$step['id']]['step_identification'] = $paths_data[$step['shortcut']]['step_identification'];
				}
			}
		}

		return $steps;
	}

	public function pathshtml($steps) {
		$down_line = '<sup class="down_line"><em>&nbsp;</em></sup>';
		foreach($steps['top_level'] as $top_level) {
			$steps_html .= '<ul  class="path_list master_path">';
			$steps_html .= '<li><span><b class="path_name">'.$top_level['name'].'</b></span>'.$down_line.'  '."\n";
			$steps_html .= '<ul class="path_list">';
			foreach($steps['children']['p'.$top_level['id']] as $top_child) {
				$steps_html .= $this->stephtml($top_child, $steps['children']);
			}
			$steps_html .= '</ul>';
			$steps_html .= '</li>';
			$steps_html .= '</ul>';
		}

		return $steps_html;
	}

	public function stephtml($step, $children) {
		
		$patid = $_REQUEST['id'];

		if(is_array($children[$step['id']]) && sizeof($children[$step['id']]) > 0) {
			$ul_start = '<ul>'."\n";
			$b_class = 'class="master_step"';
			$ul_end = '</ul>';
		} else {
			$ul_start = '';
			$b_class = '';
			$ul_end = '';
		}

		$span_class = 'class="step_'.$step['status'].'"';

		if($step['value'] == '1')
		{
			$checked = 'checked="checked"';
		}
		else
		{
			$checked = '';
		}

		if($step['ismanual'] == 1 && $step['status'] != "gray"){
			$manual = '<input type="checkbox" name="step['.$step['id'].']" value="1" '.$checked.' />';
			$manual .= '<input type="hidden" name="step_identification['.$step['id'].']" value="'.$step['step_identification'].'" '.$checked.' />';
		} else{
			$manual = '<input type="hidden" name="step_identification['.$step['id'].']" value="'.$step['step_identification'].'" '.$checked.' />';
		}
//8b75db027ddfbd6ee077abbbfea9ddff4a68ec98

		$down_line = '<sup class="down_line"><em>&nbsp;</em></sup>';

		if(strlen($step['tabname']) > 0  && $step['status'] != "gray"){
			$step_html .= '<li>'.$down_line.'<span '.$span_class.'><a '.$b_class.' href="'.$step['tabname'].'?id='.$patid .'"  >'.$manual.'<i>'.$step['shortcut'].' - '.$step['name'].'</i></a></span>'."\n".$ul_start."\n";
		} else{
			$step_html .= '<li>'.$down_line.'<span '.$span_class.'><b '.$b_class.'>'.$manual.'<input type="hidden" name=status['.$step['id'].'] value="'.$step['status'].'" /> <i>'.$step['shortcut'].' - '.$step['name'].'</i></b></span>'."\n".$ul_start."\n";
		}

		if(is_array($children[$step['id']]) && sizeof($children[$step['id']]) > 0) {
			foreach($children[$step['id']] as $child) {
				$step_html .= $this->stephtml($child, $children);
			}
		}
		$step_html .= ''.$ul_end."</li>\n";

		return $step_html;
	}

}
?>