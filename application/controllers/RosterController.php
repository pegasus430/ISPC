<?php

class RosterController extends Pms_Controller_Action {

    
	public function init()
	{
		setlocale(LC_ALL, 'de_DE.UTF8');
		/* Initialize action controller here */
		$links_privileges = Links::checkLinkPermission();

		if(!$links_privileges)
		{
			$this->_redirect(APP_BASE . 'error/previlege');
		}
		
		
		$this
		->setActionsWithPatientinfoAndTabmenus([
		    /*
		     * actions that have the patient header
		     */
		])
		->setActionsWithJsFile([
		    /*
		     * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
		     */
		    'dayplanningnew',
		])
		->setActionsWithLayoutNew([
		    /*
		     * actions that will use layout_new.phtml
		     * Actions With Patientinfo And Tabmenus also use layout_new.phtml
		     */
		])
		;
		
	}

	public function rosterAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Roster', $logininfo->userid, 'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		$this->view->options = Pms_CommonData::getMonths();

		if($logininfo->usertype == 'SA')
		{
			if($logininfo->clientid > 0)
			{
				$docquery = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('clientid=' . $logininfo->clientid . ' and isdelete=0 and isactive=1');
				$groups = $docquery->fetchArray();

				$doctorarray = array("0" => "");
				$groupsarr = array();
				$this->view->titlerow = '<tr><td>&nbsp;</td>';
				foreach($groups as $key => $val)
				{
					$doc = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('isactive=0 and isdelete=0 and groupid = ' . $val['id'] . ' and usertype!="SA" and clientid=' . $logininfo->clientid)
					->orderBy('last_name ASC');
					$docarray = $doc->fetcharray();

					$grouparray['id'] = $val['id'];
					$grouparray['groupname'] = $val['groupname'];

					$this->view->titlerow .='<td align="center" style="padding-left:5px; width:180px;">' . ucfirst($val['groupname']) . '</td>';
					$groupdoctor = array("0" => "");

					foreach($docarray as $dockey => $docval)
					{
						$groupdoctor[$docval['id']] = $docval['user_title'] . " " .$docval['last_name'] . "," . $docval['first_name'];
					}

					$grouparray['users'] = $groupdoctor;

					$deldoc = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('groupid = ' . $val['id'])
					->orderBy('last_name ASC');
					$deldocarray = $deldoc->fetchArray();

					$groupdeldoctor = array("0" => "");
					foreach($deldocarray as $dockey => $docval)
					{
						$groupdeldoctor[$docval['id']] = $docval['user_title'] . " " . $docval['last_name'] . "," . $docval['first_name'];
					}

					$grouparray['delusers'] = $groupdeldoctor;
					$groupsarr[] = $grouparray;
				}
				$this->view->titlerow .='</tr>';
				$this->view->doctorarray = $groupsarr;
			}
			else
			{
				$this->view->error_message = $this->view->translate("selectclient");
			}
		}
		else
		{
			$docquery = Doctrine_Query::create()
			->select('*')
			->from('Usergroup')
			->where('clientid=' . $logininfo->clientid . ' and isdelete=0 and isactive=1');
			$groups = $docquery->fetchArray();

			$doctorarray = array("0" => "");
			$groupsarr = array();
			$this->view->titlerow = '<tr><td>&nbsp;</td>';
			foreach($groups as $key => $val)
			{
				$doc = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('isactive=0 and isdelete=0 and groupid = ' . $val['id'])
				->orderBy('last_name ASC');
				$docarray = $doc->fetchArray();

				$grouparray['id'] = $val['id'];
				$grouparray['groupname'] = $val['groupname'];

				$this->view->titlerow .='<td align="center" style="padding-left:5px; width:180px;">' . ucfirst($val['groupname']) . '</td>';
				$groupdoctor = array("0" => " ");

				foreach($docarray as $dockey => $docval)
				{
					$groupdoctor[$docval['id']] = $docval['user_title'] . " " . $docval['last_name'] . "," . $docval['first_name'];
				}

				$grouparray['users'] = $groupdoctor;

				$deldoc = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('groupid = ' . $val['id'])
				->orderBy('last_name ASC');
				$deldocarray = $deldoc->fetchArray();

				$groupdeldoctor = array("0" => "");
				foreach($deldocarray as $dockey => $docval)
				{
					$groupdeldoctor[$docval['id']] = $docval['user_title'] . " " . $docval['last_name'] . "," . $docval['first_name'];
				}

				$grouparray['delusers'] = $groupdeldoctor;
				$groupsarr[] = $grouparray;
			}
			$this->view->titlerow .='</tr>';

			$this->view->doctorarray = $groupsarr;
		}

		$docquery = Doctrine_Query::create()
		->select('*')
		->from('Usergroup')
		->where('clientid=' . $logininfo->clientid . ' and isdelete=0 and isactive=1');
		$groups = $docquery->fetchArray();

		$groupsStr = "'9999999999999999999'";
		$comma = ",";
		foreach($groups as $group)
		{
			$groupsStr .= $comma . "'" . $group['id'] . "'";
			$comma = ",";

			$groupsFinal[$group['id']] = $group;
		}
		$this->view->groups = $groupsFinal;

		$users = Doctrine_Query::create()
		->select('*')
		->from('User')
		->where('isactive=0 and isdelete=0 and groupid IN (' . $groupsStr . ')')
		->orderBy('last_name ASC');
		$groupsUsers = $users->fetchArray();

		foreach($groupsUsers as $user)
		{
			$groupsUsersFinal[$user['groupid']][0] = "";
			$groupsUsersFinal[$user['groupid']][$user['id'] . "-" . $user['groupid']] = $user['user_title'] . " " . $user['last_name'] . ',' . $user['first_name'];
		}
		$this->view->groupUsers = $groupsUsersFinal;

		if(strlen($_POST['month']) > 0)
		{
			$valarray = $_POST;
			$this->view->curmonth = $_POST['month'];
		}
		else
		{
			$valarray = array("month" => date("Y_m", time()));
			$this->view->curmonth = date("Y_m", time());
		}

		$timestamp = strtotime(str_replace("_", "-", $valarray['month'] . "_1"));

		$dutystart = date("Y-m-d", $timestamp);

		$totaldays = date("t", $timestamp);
		$endtimestamp = strtotime(str_replace("_", "-", $valarray['month'] . "_" . $totaldays));

		$dutyend = date("Y-m-d", $endtimestamp);

		$docid_shifts_q = Doctrine_Query::create()
		->select('*')
		->from('Roster')
		->where('clientid = ' . $logininfo->clientid)
		->andWhere("duty_date between '" . $dutystart . "' and '" . $dutyend . "'")
		->andWhere("shift != 0")
		->andWhere('isdelete = "0"');
		$user_sifts_array = $docid_shifts_q->fetchArray();

		foreach($user_sifts_array as $kes => $shifts_values)
		{
			$user_shifts[$shifts_values['duty_date']][] = $shifts_values['userid'];
		}

		$this->view->user_shifts = $user_shifts;

		$docid = Doctrine_Query::create()
		->select('*')
		->from('Roster')
		->where('clientid = ' . $logininfo->clientid)
		->andWhere("duty_date between '" . $dutystart . "' and '" . $dutyend . "'")
		->andWhere("shift = 0")
		->andWhere('isdelete = "0"');
		$rostarray = $docid->fetchArray();

		foreach($rostarray as $key => $val)
		{
			if(!in_array($val['userid'], $user_shifts[$val['duty_date']]))
			{
				$valarray['doctor_' . str_replace("-", "_", $val['duty_date'])][$val['user_group']][$val['id']]['uid'] = $val['userid'];
				$valarray['doctor_' . str_replace("-", "_", $val['duty_date'])][$val['user_group']][$val['id']]['fullshift'] = $val['fullShift'];
				$valarray['doctor_' . str_replace("-", "_", $val['duty_date'])][$val['user_group']][$val['id']]['shift'] = $val['shift'];
				if($val['shiftStartTime'] != "0000-00-00 00:00:00")
				{
					$stime = date("H:i", strtotime($val['shiftStartTime']));
				}
				else
				{
					$stime = "";
				}
				if($val['shiftEndTime'] != "0000-00-00 00:00:00")
				{
					$etime = date("H:i", strtotime($val['shiftEndTime']));
				}
				else
				{
					$etime = "";
				}
				$valarray['doctor_' . str_replace("-", "_", $val['duty_date'])][$val['user_group']][$val['id']]['stime'] = $stime;
				$valarray['doctor_' . str_replace("-", "_", $val['duty_date'])][$val['user_group']][$val['id']]['etime'] = $etime;
			}
		}

		$monthdays = $this->getMonthdays($valarray);

		$grid = new Pms_Grid($monthdays, 1, count($monthdays), "listroster.html");
		$this->view->rostergrid = $grid->renderGrid();

		if($_REQUEST['delid'] > 0)
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}


			$upload_form = new Application_Form_RosterFileUpload();
			$upload_form->deleteFile($_REQUEST['delid']);
		}

		if($this->getRequest()->isPost() && $_POST['fileuploads'] == "1")
		{
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			$ftype = $_SESSION['filetype'];
			if($ftype)
			{
				$filetypearr = explode("/", $ftype);
				if($filetypearr[1] == "vnd.openxmlformats-officedocument.spreadsheetml.sheet")
				{
					$filetype = "XLSX";
				}
				elseif($filetypearr[1] == "vnd.openxmlformats-officedocument.wordprocessingml.document")
				{
					$filetype = "docx";
				}
				elseif($filetypearr[1] == "X-OCTET-STREAM")
				{
					$filetype = "PDF";
				}
				else
				{
					$filetype = $filetypearr[1];
				}
			}


			$upload_form = new Application_Form_RosterFileUpload();

			$a_post = $_POST;

			$a_post['clientid'] = $logininfo->clientid;

			$a_post['filetype'] = $_SESSION['filetype'];

			if($upload_form->validate($a_post))
			{
				$upload_form->insertData($a_post);
			}
			else
			{
				$upload_form->assignErrorMessages();
				$this->retainValues($_POST);
			}

			//remove session stuff
			$_SESSION['filename'] = '';
			$_SESSION['filetype'] = '';
			$_SESSION['filetitle'] = '';
			unset($_SESSION['filename']);
			unset($_SESSION['filetype']);
			unset($_SESSION['filetitle']);
		}

		$files = new RosterFileUpload();
		$filesData = $files->getClientFiles($logininfo->clientid);

		$this->view->filesData = $filesData;
		$this->view->showInfo = $logininfo->showinfo;

		$allUsers = Pms_CommonData::getClientUsers($logininfo->clientid);
		foreach($allUsers as $keyu => $user)
		{
			$allUsersArray[$user['id']] = $user['user_title'] . " " . $user['last_name'] . ", " . $user['first_name'];
		}
		$this->view->allusers = $allUsersArray;
	}

	private function getMonthdays($post)
	{

		$split = split("_", $post['month']);
		$timestamp = mktime(0, 0, 0, $split[1], 1, $split[0]);
		$noofdays = date("t", $timestamp);
		$daysaray = array();
		for($i = 1; $i <= $noofdays; $i++)
		{
			$curtimestamp = mktime(0, 0, 0, $split[1], $i, $split[0]);

			$daysaray[] = array(
					"day" => $this->view->translate(date("D", $curtimestamp)) . "&nbsp;" . date("d.m.Y", $curtimestamp),
					"doctor" => $post['doctor_' . str_replace("-", "_", date("Y-m-d", $curtimestamp))],
					"docdd" => 'doctor_' . str_replace("-", "_", date("Y-m-d", $curtimestamp)),
					"dates" => $this->view->translate($curtimestamp),
					"current_date" => date("Y-m-d", $this->view->translate($curtimestamp))
			);
		}
		return $daysaray;
	}

	public function insertrosterAction()
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->_helper->viewRenderer('roster');

		$split = split("_", $_POST['dt']);
		$dutydate = $split['1'] . "-" . $split['2'] . "-" . $split['3'];
		$groupid = $split['4'];
		$dutyset = Doctrine_Query::create()
		->select('*')
		->from('Roster');

		if($_REQUEST['eid'] > 0 && !empty($_REQUEST['eid']))
		{
			$dutyset->where("id= ?", $_REQUEST['eid']);
		}
		else
		{
			$dutyset->where("duty_date= ?", $dutydate);
			$dutyset->andWhere("user_group= ?", $groupid );
			$dutyset->andWhere("clientid= ?", $logininfo->clientid);
			$dutyset->andWhere("userid = ?", $_POST['id']);
		}
		$dutyset->andWhere("shift = 0");
		$dutyset->andWhere("isdelete = '0'");
		$dutyexec = $dutyset->execute();

		if(count($dutyexec->toArray()) > 0)
		{
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Roster', $logininfo->userid, 'canedit');

			if(!$return)
			{
				$response['msg'] = "Success";
				$response['error'] = "";
				$response['callBack'] = "errorcallBack";
				$response['callBackParameters'] = array();
				$response['callBackParameters']['eror_msg'] = "You do not have permission";
				echo json_encode($response);
				exit;
			}
			$selectedDate = str_replace("doctor_", "", $_POST['dt']);
			$selDate = explode("_", $selectedDate);

			$startDateTime = $selDate[0] . '-' . $selDate[1] . '-' . $selDate[2] . ' ' . $_POST['stime'] . ':00';
			$endDateTime = $selDate[0] . '-' . $selDate[1] . '-' . $selDate[2] . ' ' . $_POST['etime'] . ':00';

			$dutyset = Doctrine_Query::create()
			->update('Roster')
			->set('userid', $_POST['id'])
			->set('fullShift', $_POST['chk']);
			if($_POST['id'] == '0')
			{
				$dutyset->set('isdelete', '1');
			}

			if(!empty($_POST['stime']))
			{
				$dutyset->set('shiftStartTime', "'" . $startDateTime . "'");
			}
			if(!empty($_POST['etime']))
			{
				$dutyset->set('shiftEndTime', "'" . $endDateTime . "'");
			}

			if($_REQUEST['eid'] > 0 && !empty($_REQUEST['eid']))
			{
				$dutyset->where("id= ?", $_REQUEST['eid']);
			}
			else
			{
				$dutyset->where("duty_date= ?", $dutydate);
				$dutyset->andWhere("user_group= ?", $groupid );
				$dutyset->andWhere("clientid= ?", $logininfo->clientid);
				$dutyset->andWhere("userid = ?", $_POST['id']);
			}

			$dutyexec = $dutyset->execute();

			if($_POST['id'] != "0")
			{
				$user = Doctrine::getTable('User')->find($_POST['id']);
				$userarray = $user->toArray();

				$docquery = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('isdelete= ?',0)
				->andWhere('id= ?', $userarray['groupid']);
				$doc = $docquery->execute();
				$groups = $doc->toArray();

				$groupname = $groups[0]['groupname'];
			}
			$expget = explode("_", $_POST['dt']);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['dt'] = $_POST['dt'];
			$response['callBackParameters']['date'] = $expget[1] . "_" . $expget[2] . "_" . $expget[3];
			$response['callBackParameters']['uid'] = $_POST['uid'];
			$response['callBackParameters']['chk'] = $_REQUEST['chk'];
			$response['callBackParameters']['etime'] = $_REQUEST['stime'];
			$response['callBackParameters']['stime'] = $_REQUEST['etime'];
			$response['callBackParameters']['aid'] = $_REQUEST['eid'];
			if($_POST['id'] != "0")
			{
				$response['callBackParameters']['groupname'] = $groups[0]['groupname'];
			}
			echo json_encode($response);
			exit;
		}
		else
		{

			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Roster', $logininfo->userid, 'canadd');

			if(!$return)
			{
				$response['msg'] = "Success";
				$response['error'] = "";
				$response['callBack'] = "errorcallBack";
				$response['callBackParameters'] = array();
				$response['callBackParameters']['eror_msg'] = "You do not have permission";
				echo json_encode($response);
				exit;
			}

			$roster = new Roster();
			$roster->userid = $_POST['id'];
			$roster->duty_date = $dutydate;
			$roster->user_group = $groupid;
			if($logininfo->usertype != 'SA')
			{
				$roster->clientid = $logininfo->clientid;
			}
			else
			{
				$roster->clientid = $logininfo->clientid;
			}
			$roster->save();
			$addedId = $roster->id;

			if($_POST['id'] != "0")
			{
				$user = Doctrine::getTable('User')->find($_POST['id']);
				$userarray = $user->toArray();

				$docquery = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('isdelete= ?',0)
				->andWhere('id= ?', $userarray['groupid']);
				$groups = $docquery->fetchArray();

				$groupname = $groups[0]['groupname'];
			}

			$expget = explode("_", $_POST['dt']);

			$response['msg'] = "Success";
			$response['error'] = "";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['dt'] = $_POST['dt'];
			$response['callBackParameters']['date'] = $expget[1] . "_" . $expget[2] . "_" . $expget[3];
			$response['callBackParameters']['uid'] = $_POST['uid'];
			$response['callBackParameters']['chk'] = $_REQUEST['chk'];
			$response['callBackParameters']['etime'] = $_REQUEST['stime'];
			$response['callBackParameters']['stime'] = $_REQUEST['etime'];
			$response['callBackParameters']['aid'] = $addedId;
			if($_POST['id'] != "0")
			{
				$response['callBackParameters']['groupname'] = $groups[0]['groupname'];
			}

			echo json_encode($response);
			exit;
		}
	}

	private function retainValues($values)
	{

		foreach($values as $key => $val)
		{
			$this->view->$key = $val;
		}
	}

	public function rosteruploadifyAction()
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');

		$extension = explode(".", $_FILES['qqfile']['name']);

		$_SESSION['filetype'] = $extension[count($extension) - 1];
		$_SESSION['filetitle'] = $extension[0];
		$timestamp_filename = time() . "_file";

		$path = 'roosterUploads';
		$dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
		while(!is_dir($path . '/' . $dir))
		{
			$dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
			mkdir($path . '/' . $dir);
			if($i >= 50)
			{
				exit; //failsafe
			}
			$i++;
		}

		$folderpath = $dir;
		$filename = "roosterUploads/" . $folderpath . "/" . trim($timestamp_filename);
		$_SESSION['filename'] = $folderpath . "/" . trim($timestamp_filename);
		$_SESSION['filetype'] = $extension[count($extension) - 1];
		move_uploaded_file($_FILES['qqfile']['tmp_name'], $filename);

		echo json_encode(array(success => true));
		exit;
	}

	public function rosterfileAction()
	{

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$this->_helper->layout->setLayout('layout');
		$this->_helper->viewRenderer->setNoRender();

		if($_REQUEST['doc_id'] > 0)
		{
			$patient = Doctrine_Query::create()
			->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
					AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
					AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
					->from('RosterFileUpload')
					->where('id= ?', $_REQUEST['doc_id']);
					$fl = $patient->execute();

					if($fl)
					{
						$flarr = $fl->toArray();

						$explo = explode("/", $flarr[0]['file_name']);

						$fdname = $explo[0];
						$flname = utf8_decode($explo[1]);
					}

					$path = APPLICATION_PATH . '/../public/roosterUploads/' . $fdname . '/';
					$fullPath = $path . $flname;

					if($fd = fopen($fullPath, "r"))
					{
						$fsize = filesize($fullPath);
						$path_parts = pathinfo($fullPath . "." . $flarr[0]['file_type']);

						$ext = strtolower($path_parts["extension"]);
						switch($ext)
						{
							case "pdf":
								header('Content-Description: File Transfer');
								header("Content-type: application/pdf"); // add here more headers for diff. extensions
								header('Content-Transfer-Encoding: binary');
								header('Expires: 0');
								header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
								header('Pragma: public');
								header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\""); // use 'attachment' to force a download
								break;
							default;
							header('Content-Description: File Transfer');
							header("Content-type: application/octet-stream");
							header('Content-Transfer-Encoding: binary');
							header('Expires: 0');
							header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
							header('Pragma: public');
							header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\"");
						}
						header("Content-length: $fsize");
						header("Cache-control: private"); //use this to open files directly
						echo readfile($fullPath);
					}
					fclose($fd);
					exit;
		}
	}

	public function dayplanningAction()
	{

		setlocale(LC_ALL, 'de_DE.UTF8');
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Roster', $logininfo->userid, 'canview');
		$clientid = $logininfo->clientid;
		if(!$return)
		{
			$this->_redirect(APP_BASE . "error/previlege");
		}

		if($this->getRequest()->isPost())
		{

			if($_POST['save_visit'] == '1' || $_POST['save_users_to_plan'] == '1' || $_POST['remove_visit_action'] == '1' || $_POST['edit_visit_action'] == '1')
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
			}
		}
		/* ###################### DEFAULTS - Current day - today ############################### */
		if($_REQUEST['date'])
		{
			if($_REQUEST['sel'] == 1)
			{
				$date = date('Y-m-d 00:00:00', strtotime($_REQUEST['date']));
			}
			else
			{
				$date = date('Y-m-d 00:00:00', $_REQUEST['date']);
			}
		}
		else
		{
			$date = date('Y-m-d 00:00:00', time());
		}

		$today = date('Y-m-d 00:00:00', time());


		$previous_date = date('Y-m-d 00:00:00', mktime(date('H', strtotime($date)), date('i', strtotime($date)), 0, date('m', strtotime($date)), date('d', strtotime($date)) - 1, date('Y', strtotime($date))));
		$next_date = date('Y-m-d 00:00:00', mktime(date('H', strtotime($date)), date('i', strtotime($date)), 0, date('m', strtotime($date)), date('d', strtotime($date)) + 1, date('Y', strtotime($date))));

		/* ###################### DEFAULTS - Current day - today ############################### */
		$day_planning['previous_date'] = date('d.m.Y', strtotime($previous_date));
		$day_planning['current_date'] = date('d.m.Y', strtotime($date));
		$day_planning['next_date'] = date('d.m.Y', strtotime($next_date));

		if($this->getRequest()->isPost())
		{
			if($_POST['date_action'] == 1 && !empty($_POST['date']))
			{
				$this->_redirect(APP_BASE . "roster/dayplanning?date=" . strtotime($_POST['date']));
			}
		}

		/* ###################### POST - SAVE/EDIT/DELETE visits ############################### */
		if($this->getRequest()->isPost())
		{
			$save_visit_form = new Application_Form_DailyPlanningVisits();
			if($_POST['save_visit'] && $_POST['save_visit'] == "1")
			{/* ########################### SAVE VISIT ######################################### */
				$a_post = $_POST;
				$ipid = Pms_CommonData::getIpid($_POST['patient']['id']);

				$strip_plan_date = date('Y-m-d', strtotime($_POST['user']['starts_shift']));
				$start_date = $strip_plan_date . ' ' . $_POST['patient']['start_date'] . ':00';
				$end_date = $strip_plan_date . ' ' . $_POST['patient']['end_date'] . ':00';

				$visits_details['date'] = $date;
				$visits_details['userid'] = $a_post['patient2user'];
				$visits_details['clientid'] = $clientid;
				$visits_details['ipid'] = $ipid;
				$visits_details['start_date'] = $start_date;
				$visits_details['end_date'] = $end_date;

				$save_action = $save_visit_form->save_visit($visits_details);
				$this->_redirect(APP_BASE . "roster/dayplanning?date=" . strtotime($date));
			}
			elseif($_POST['remove_visit_action'] && $_POST['remove_visit_action'] != "0")
			{/* ########################### DELETE VISIT ######################################### */
				$visits_details['visit_id'] = $_POST['remove_visit_id'];
				$save_action = $save_visit_form->delete_visit($visits_details);
				$this->_redirect(APP_BASE . "roster/dayplanning?date=" . strtotime($date));
			}
			/* ########################### EDIT VISIT ######################################### */
			elseif($_POST['edit_visit_action'] && $_POST['edit_visit_action'] != "0")
			{
				$strip_plan_date = date('Y-m-d', strtotime($date));
				$start_date = $strip_plan_date . ' ' . $_POST['edit_visit_start_date'] . ':00';
				$end_date = $strip_plan_date . ' ' . $_POST['edit_visit_end_date'] . ':00';

				$visits_details['visit_id'] = $_POST['edit_visit_id'];
				$visits_details['userid'] = $_POST['edit_visit_user_id'];
				;
				$visits_details['start_date'] = $start_date;
				$visits_details['end_date'] = $end_date;

				$save_action = $save_visit_form->edit_visit($visits_details);
				$this->_redirect(APP_BASE . "roster/dayplanning?date=" . strtotime($date));
			}
		}

		$user_planning = new DailyPlanningUsers();
		$userids2date = $user_planning->get_users_by_date($clientid, $date, false, true, $allowed_deleted = 1);
		$users_details2date = $user_planning->get_users_by_date($clientid, $date, false);

		/* ###################### Users Groups- get groups  that are allowed to shou in dienstplan ############################### */
		$docquery = Doctrine_Query::create()
		->select('*')
		->from('Usergroup')
		->where('clientid="' . $clientid . '"')
		->andWhere('isdelete="0"')
		->andWhere('isactive=1');
		$groups = $docquery->fetchArray();

		$groups_ids[] = '9999999999';
		foreach($groups as $k_gr => $v_gr)
		{
			$groups_ids[] = $v_gr['id'];
		}

		/* ###################### Users - get user details ############################### */
		$doc = Doctrine_Query::create()
		->select('*')
		->from('User')
		->where('isactive=0')
		->andWhere('isdelete=0')
		->andWhere('usertype!="SA"')
		->andWhere('clientid=' . $clientid)
		->andWhereIn('groupid', $groups_ids)
		->orderBy('last_name ASC');
		$docarray = $doc->fetchArray();

		foreach($docarray as $d_key => $d_val)
		{
			$client_users_ids[] = $d_val['id'];
			$client_users[$d_val['id']]['id'] = $d_val['id'];
			$client_users[$d_val['id']]['name'] = $d_val['last_name'] . ", " . $d_val['first_name'];
			$client_users[$d_val['id']]['phone'] = $d_val['phone'];
			$client_users[$d_val['id']]['mobile'] = $d_val['mobile'];
			$client_users[$d_val['id']]['user_title'] = $d_val['user_title'];
			$client_users[$d_val['id']]['last_name'] = $d_val['last_name'];
			$client_users[$d_val['id']]['first_name'] = $d_val['first_name'];

			if(!empty($d_val['usercolor']))
			{
				$client_users[$d_val['id']]['usercolor'] = $d_val['usercolor'];
			}
			else
			{
				$client_users[$d_val['id']]['usercolor'] = "cccddd";
			}

			if(!empty($d_val['shortname']))
			{
				$client_users[$d_val['id']]['shortname'] = $d_val['shortname'];
			}
			else
			{
				$client_users[$d_val['id']]['shortname'] = substr($d_val['last_name'], 0, 1) . substr($d_val['first_name'], 0, 1);
			}
		}

		if(empty($client_users_ids))
		{
			$client_users_ids[] = "999999";
		}

		/* ###################### users - duty roster for today ############################### */
		$docid = Doctrine_Query::create()
		->select('*')
		->from('Roster')
		->where('clientid = ' . $clientid)
		->andWhereIn('userid', $client_users_ids)
		->andWhere("DATE(duty_date) = DATE('" . $date . "') ")
		->andWhere('isdelete = "0"');
		$rostarray = $docid->fetchArray();

		foreach($rostarray as $r_key => $r_value)
		{
			$client_roster_user_ids[] = $r_value['userid'];
			$user_roster[$r_value['userid']] = $r_value;
		}

		foreach($client_users as $k_userid => $u_values)
		{
			$client_users[$u_values['id']]['roster'] = $user_roster[$u_values['id']];
			$client_users[$u_values['id']]['active_today'] = $users_details2date[$u_values['id']];
		}
			
		/* ###################### Client settings ############################### */
		$client_data = Pms_CommonData::getClientData($clientid);
		$tagesplanung_standby_patients = $client_data['0']['tagesplanung_standby_patients']; // ISPC-1170 client setting show patients standby in tagesplanung
			
		if($tagesplanung_standby_patients == '0') // only active patients
		{

			/* ###################### Active patients ############################### */

			if(strtotime($date) >= strtotime($today))
			{

				$active_patients = $this->get_now_active_patients();
			}
			else
			{
				$sql = "p.ipid,e.ipid,a.ipid";
				$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
				$sql .= ",AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
				$sql .= ",convert(AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') using latin1) as zip";
				$sql .= ",convert(AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') using latin1) as street1";
				$sql .= ",convert(AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') using latin1) as city";
				$sql .= ",convert(AES_DECRYPT(p.phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone";
				$sql .= ",convert(AES_DECRYPT(p.mobile,'" . Zend_Registry::get('salt') . "') using latin1) as mobile";
				$sql .= ",convert(AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') using latin1) as sex";

				$date_filter = array('0' => array('start' => $date, 'end' => $date));

				$active_patients_ipids = Pms_CommonData::patients_active($sql, $clientid, $date_filter, false, "convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)", "ASC"); // BRE patient list

				foreach($active_patients_ipids as $k => $p_values)
				{
					$active_patients['ipids'][] = $p_values['ipid'];
					$active_patients['details'][] = $p_values['PatientMaster'];
				}
			}

			/* ###################### users - get visits for today ############################### */
			$patient_plan_visits = new DailyPlanningVisits();
			$patient_plan_visits_array = $patient_plan_visits->get_patients_visits($clientid, $date);

			foreach($patient_plan_visits_array as $key => $pvvalues)
			{
				$client_users[$pvvalues['userid']]['planned_visits'][] = $pvvalues;

				if(in_array($pvvalues['ipid'], $active_patients['ipids']))
				{
					$patient_visits[$pvvalues['ipid']]['planned_visits'][] = $pvvalues;
					$patient_visits[$pvvalues['ipid']]['existing_visits'] = count($patient_visits[$pvvalues['ipid']]['planned_visits']);
				}
			}

			/* ###################### Patients details ############################### */
			if(empty($active_patients['ipids']))
			{
				$active_patients['ipids'][] = "999999";
			}

			$pl = new PatientVisitsSettings();
			$pat_visits_settings = $pl->getPatientVisitsSettings($active_patients['ipids']);

			foreach($pat_visits_settings as $vskey => $vsvalues)
			{
				$visits_settings[$vsvalues['ipid']] = $vsvalues;
			}
			$patient_details = array();

			foreach($active_patients['details'] as $pat_key => $pat_value)
			{
				$patient_details[$pat_value['ipid']]['id'] = $pat_value['id'];
				$patient_details[$pat_value['ipid']]['patient_name'] = $pat_value['last_name'] . ' ' . $pat_value['first_name'];
				$patient_details[$pat_value['ipid']]['last_name'] = $pat_value['last_name'];
				$patient_details[$pat_value['ipid']]['first_name'] = $pat_value['first_name'];
				$patient_details[$pat_value['ipid']]['street'] = $pat_value['street1'];
				$patient_details[$pat_value['ipid']]['zip'] = $pat_value['zip'];
				$patient_details[$pat_value['ipid']]['city'] = $pat_value['city'];
				$patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
				$patient_details[$pat_value['ipid']]['mobile'] = $pat_value['mobile'];

				// all patient visits planned for today
				$patient_details[$pat_value['ipid']]['planned_visits'] = $patient_visits[$pat_value['ipid']]['planned_visits'];

				if(!empty($visits_settings[$pat_value['ipid']]))
				{
					$patient_details[$pat_value['ipid']]['visits_per_day'] = $visits_settings[$pat_value['ipid']]['visits_per_day'];
					$patient_details[$pat_value['ipid']]['visit_duration'] = $visits_settings[$pat_value['ipid']]['visit_duration'];
				}
				else
				{
					$patient_details[$pat_value['ipid']]['visits_per_day'] = '1';
					$patient_details[$pat_value['ipid']]['visit_duration'] = "60";
				}

				// patient - remaining visits per day
				$patient_details[$pat_value['ipid']]['visits_remaining_per_day'] = $patient_details[$pat_value['ipid']]['visits_per_day'] - $patient_visits[$pat_value['ipid']]['existing_visits'];
			}

			if($_REQUEST['dbg'] == 1)
			{
				print_R("\n number of active patients \n");
				print_R(count($patient_details));
				print_R("\n active patients \n");
				print_R($patient_details);
				exit;
			}
		}
		else
		{
			/* ###################### Active + standby patients ############################### */

			if(strtotime($date) >= strtotime($today))
			{

				$active_standby_patients = $this->get_now_active_patients(true);
			}
			else
			{
				$sql = "p.ipid,e.ipid,a.ipid";
				$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
				$sql .= ",AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
				$sql .= ",convert(AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') using latin1) as zip";
				$sql .= ",convert(AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') using latin1) as street1";
				$sql .= ",convert(AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') using latin1) as city";
				$sql .= ",convert(AES_DECRYPT(p.phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone";
				$sql .= ",convert(AES_DECRYPT(p.mobile,'" . Zend_Registry::get('salt') . "') using latin1) as mobile";
				$sql .= ",convert(AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') using latin1) as sex";

				$date_filter = array('0' => array('start' => $date, 'end' => $date));

				$activstandby_patients_ipids = Pms_CommonData::patients_active($sql, $clientid, $date_filter, false, "convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)", "ASC",false,false,'0',true); // BRE patient list

				foreach($activstandby_patients_ipids as $k => $p_values)
				{
					$active_standby_patients['ipids'][] = $p_values['ipid'];
					$active_standby_patients['details'][] = $p_values['PatientMaster'];
				}
			}

			/* ###################### users - get visits for today ############################### */
			$patient_plan_visits = new DailyPlanningVisits();
			$patient_plan_visits_array = $patient_plan_visits->get_patients_visits($clientid, $date);

			foreach($patient_plan_visits_array as $key => $pvvalues)
			{
				//$client_users[$pvvalues['userid']]['planned_visits'][] = $pvvalues;

				if(in_array($pvvalues['ipid'], $active_standby_patients['ipids']))
				{
					$patient_visits[$pvvalues['ipid']]['planned_visits'][] = $pvvalues;
					$patient_visits[$pvvalues['ipid']]['existing_visits'] = count($patient_visits[$pvvalues['ipid']]['planned_visits']);
				}
			}

			/* ###################### Patients details ############################### */
			if(empty($active_patients['ipids']))
			{
				$active_standby_patients['ipids'][] = "999999";
			}

			$pl = new PatientVisitsSettings();
			$pat_visits_settings = $pl->getPatientVisitsSettings($active_standby_patients['ipids']);

			foreach($pat_visits_settings as $vskey => $vsvalues)
			{
				$visits_settings[$vsvalues['ipid']] = $vsvalues;
			}
			$patient_details = array();

			foreach($active_standby_patients['details'] as $pat_key => $pat_value)
			{
				$patient_details[$pat_value['ipid']]['id'] = $pat_value['id'];
				$patient_details[$pat_value['ipid']]['patient_name'] = $pat_value['last_name'] . ' ' . $pat_value['first_name'];
				$patient_details[$pat_value['ipid']]['last_name'] = $pat_value['last_name'];
				$patient_details[$pat_value['ipid']]['first_name'] = $pat_value['first_name'];
				$patient_details[$pat_value['ipid']]['street'] = $pat_value['street1'];
				$patient_details[$pat_value['ipid']]['zip'] = $pat_value['zip'];
				$patient_details[$pat_value['ipid']]['city'] = $pat_value['city'];
				$patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
				$patient_details[$pat_value['ipid']]['mobile'] = $pat_value['mobile'];

				// all patient visits planned for today
				$patient_details[$pat_value['ipid']]['planned_visits'] = $patient_visits[$pat_value['ipid']]['planned_visits'];

				if(!empty($visits_settings[$pat_value['ipid']]))
				{
					$patient_details[$pat_value['ipid']]['visits_per_day'] = $visits_settings[$pat_value['ipid']]['visits_per_day'];
					$patient_details[$pat_value['ipid']]['visit_duration'] = $visits_settings[$pat_value['ipid']]['visit_duration'];
				}
				else
				{
					$patient_details[$pat_value['ipid']]['visits_per_day'] = '1';
					$patient_details[$pat_value['ipid']]['visit_duration'] = "60";
				}

				// patient - remaining visits per day
				$patient_details[$pat_value['ipid']]['visits_remaining_per_day'] = $patient_details[$pat_value['ipid']]['visits_per_day'] - $patient_visits[$pat_value['ipid']]['existing_visits'];
			}

			if($_REQUEST['dbg'] == 1)
			{
				print_R("\n number of active patients \n");
				print_R(count($patient_details));
				print_R("\n active patients \n");
				print_R($patient_details);
				exit;
			}

		}
		/* ########################################################## */
		/* ##################  Check all duty users and see if they have day plan - if not- add them  ###################### */
		/* ########################################################## */
		foreach($client_roster_user_ids as $roster_user_id)
		{
			if(!in_array($roster_user_id, $userids2date))
			{
				$records_users_plan[] = array(
						"clientid" => $clientid,
						"userid" => $roster_user_id,
						"date" => $date
				);
			}
		}

		if(count($records_users_plan) > 0)
		{
			$collection = new Doctrine_Collection('DailyPlanningUsers');
			$collection->fromArray($records_users_plan);
			$collection->save();
			$this->_redirect(APP_BASE . "roster/dayplanning?date=" . strtotime($date));
		}

		$day_planning['plan_date'] = $date;
		$day_planning['users'] = $client_users;
		$day_planning['active_patients'] = $patient_details;

		foreach($day_planning['users'] as $ku => $uvs)
		{
			if(!empty($uvs['active_today']))
			{
				$user_active_today[] = $ku;
			}
		}
		$this->view->day_planning = $day_planning;


		/* ########################### ADD USERS TO PLAN & PDF ######################################### */
		if($this->getRequest()->isPost())
		{
			if($_POST['save_users_to_plan'] == "1")
			{/* ########################### ADD USERS TO PLAN ######################################### */
				if(empty($_POST['day_planning']['users']))
				{
					$_POST['day_planning']['users'][] = "999999";
				}

				foreach($user_active_today as $k => $user_ida)
				{
					if(!in_array($user_ida, $_POST['day_planning']['users']))
					{
						$clean_data_users[] = $user_ida;
					}
				}

				foreach($_POST['day_planning']['users'] as $k => $post_user)
				{
					if(!in_array($post_user, $clean_data_users) && !in_array($post_user, $user_active_today) && (in_array($post_user, $client_users_ids)))
					{
						$records_users_to_plan[] = array(
								"clientid" => $clientid,
								"userid" => $post_user,
								"date" => $date
						);
					}
				}

				if(!empty($clean_data_users))
				{
					$clean = $this->clean_today_plans($clientid, $date, $clean_data_users);
				}

				if(count($records_users_to_plan) > 0)
				{
					$collection = new Doctrine_Collection('DailyPlanningUsers');
					$collection->fromArray($records_users_to_plan);
					$collection->save();
				}
				$this->_redirect(APP_BASE . "roster/dayplanning?date=" . strtotime($date));
			}
			/* ########################### PRINT PDF ######################################### */
			elseif($_POST['pdf_print_action'] && $_POST['pdf_print_action'] == 1)
			{
				$post_data['day_planning'] = $day_planning;
				$post_data['clientid'] = $clientid;
				$this->generate_pdf($post_data, "Tagesplanung", "day_planning_pdf2.html");
			}
		}
	}

	public function dayplanningnewtestAction()
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
			
		DailyPlanningVisits :: set_autoasign_visits_cronjob($clientid , date("Y-m-d") );
		//DailyPlanningVisits :: set_autoasign_visits_cronjob($clientid , date("Y-m-d", strtotime(date("Y-m-d") . ' -7 day')) );
		return true;
	}

	//ispc-1533
	private function dayplanningnew_allweekpdf( $date = 0 , $clientid = 0 , $allweek = 0)
	{
		// 				print_r($_POST);
		// 				die();
		$Tr = new Zend_View_Helper_Translate();
		$week_word = $Tr->translate('week');
		$we_have_errors = $Tr->translate('dayplanning_noplan_errors');
			
		$time = strtotime($date);
		$week_number =  date("W", $time);

		$year = date("Y", $time);
		for($day=1; $day<=7; $day++)
		{
			$week_days[$day] =  date('Y-m-d', strtotime($year."W".$week_number.$day)) ;
		}

		if ( $allweek == "0" ){
			$week_days = array();
			$week_days[1] = $week_days[7] = date('Y-m-d', $time) ;
				
			$week_days[date('N', $time)] = date('Y-m-d', $time) ;
		
		} else {

		    
		    $client_data = Pms_CommonData::getClientData($clientid);
		    $tourenplanung_settings = $client_data[0]['tourenplanung_settings'];
		    
		    if ($tourenplanung_settings
		        && isset($tourenplanung_settings['workweek_start']) //&& isset($tourenplanung_settings['workweek_end'])
		        && (int)$tourenplanung_settings['workweek_start'] > -1 //&& (int)$tourenplanung_settings['workweek_end'] > -1
	        )
		    {
		        //we use `last weeks` ??
		        $last_week = date('l', strtotime($date)) == $tourenplanung_settings['workweek_start'] ? '' : 'last ';
		        
		        $dayO = date_create($date)->modify("{$last_week}{$tourenplanung_settings['workweek_start']}");;
		        
		        $week_days = [];
		        $week_days[1] = $dayO->format('Y-m-d');
		            
		        for ($i = 2; $i <= 7; $i++) {
		            $dayO->modify("+1 day"); //->format('Y-m-d l');
		            $week_days[$i] = $dayO->format('Y-m-d');   
		        }
		    }
		    
		}

		//get users that have visits in this selected week
		$user = false;
		$userid_type = false;
		if ( $allweek != "0" && $_POST['pdf_print_users'] != "none" && $_POST['pdf_print_users'] != "all_users"){
			//just one user
			if (substr($_POST['pdf_print_users'], 0, strlen('pseudo_')) == 'pseudo_'){
				//this is a pseudo-group
				$user = (int)(substr($_POST['pdf_print_users'], strlen('pseudo_'), strlen($_POST['pdf_print_users'])));
				$userid_type = 'pseudogrups';
			}
			else{
				//normal user
				$user = (int)$_POST['pdf_print_users'];
				$userid_type = 'user';
			}
		}

		//create array with view mode for each user
		$DailyPlanningUsers_viewmode = array();

		$DailyPlanningUsers_allweek = DailyPlanningUsers :: get_users_by_date_interval($clientid , array("start"=>$week_days[1], "end"=>$week_days[7]) , $user, $userid_type);

		if(is_array($DailyPlanningUsers_allweek) && count($DailyPlanningUsers_allweek) >0){
			foreach($DailyPlanningUsers_allweek as $k=>$v){

				$DailyPlanningUsers_ids[ $v['userid_type'] ][$v['userid']] = $v['userid'];
				$DailyPlanningUsers_viewmode [ $v['userid_type'] ][ $v['userid'] ] [ $v['date'] ] = $v['view_mode'];

			}
			$DailyPlanningPseudoGrups_ids = $DailyPlanningUsers_ids[ 'pseudogrups' ];
			$DailyPlanningUsers_ids = $DailyPlanningUsers_ids[ 'user' ];
		}
			
		//print_r($DailyPlanningUsers_allweek);die();
		$userid_Visits = array();
		//get the patients that have visits in this selected week
		if ( (is_array($DailyPlanningUsers_ids) && count($DailyPlanningUsers_ids) > 0 )
				||
				(is_array($DailyPlanningPseudoGrups_ids) && count($DailyPlanningPseudoGrups_ids) > 0 ))
		{

			//ispc-1855
			$pseudo_tours = array(); // $pseudo_tours [ pseudogroup_id] =  shift_id associated
			$client_shifts = ClientShifts :: get_client_shifts($clientid);
			foreach ($client_shifts as $ck => $shifts){
				if ( $shifts['istours'] !='0'){
					$pseudo_tours[ $shifts['istours'] ] = $shifts['id'];
				}
			}
				
			if (count($pseudo_tours) > 0) {
					
				if ( $allweek == "0" ){
					$Visits_days = array();
					$Visits_days[] = date('Y-m-d', $time) ;
				}else{
					$Visits_days = $week_days;
						
				}
				foreach($Visits_days as $vday){
					//duty roster for today
					$docid = Doctrine_Query::create()
					->select('id, duty_date, userid, user_group, shift ')
					->from('Roster')
					->where('clientid = ?' , $clientid)
					->andWhereIn("shift", array_values($pseudo_tours))
					->andWhere("DATE(duty_date) = DATE('" . $vday . "') ")
					->andWhere('isdelete = ?', 0);
					$rostarray = $docid->fetchArray();
						
					foreach ($rostarray as $rost){
						$pseudo_tours_by_day[ $rost['duty_date'] ][ $rost['shift'] ] [] = $rost['userid'];
						$DailyPlanningUsers_ids [] = $rost['userid'];
					}
						
				}
			}


			//get the info on all this userid's
			if(!empty($DailyPlanningUsers_ids)){
				$doc = Doctrine_Query::create()
				->select('id, user_title, last_name, first_name, mobile, phone, isactive, DATE(isactive_date) as isactive_date, makes_visits')
				->from('User')
				//->where('isactive=0')
				//->Where('( isactive=0 or DATE(isactive_date) >= DATE(\''.$week_days[1] . '\')  )')
				->andWhere('isdelete=0')
				->andWhere('usertype!="SA"')
				->andWhere('clientid=' . $clientid)
				->andWhereIn('id', $DailyPlanningUsers_ids);
				$docarray = $doc->fetchArray();

				if ( is_array($docarray) && count($docarray) > 0 )
				{
					foreach($docarray as $v){
						$userid_array[ $v['id'] ] = $v;

						$userid_array[ $v['id'] ]['nice_name'] = $v['user_title'] ." ". $v['last_name'] . ", " .$v['first_name'];
						$userid_array[ $v['id'] ]['userid_type'] = 'user';

						$userid_array['user'] [ $v['id'] ] = $userid_array[ $v['id'] ];

					}
					$DailyPlanningUsers_ids = array_keys($userid_array);
				}else{
						
					$DailyPlanningUsers_ids = array("0");
				}
			}

				
			//get the info on all this pseudogroups
			if(!empty($DailyPlanningPseudoGrups_ids)){
				$userpseudo = Doctrine_Query::create()
				->select('*')
				->from('UserPseudoGroup')
				->where('clientid= ? ', $clientid)
				->andWhere('isdelete= ?', 0)
				->andWhereIn("makes_visits", array('1', 'tours'))
				->andWhereIn('id', $DailyPlanningPseudoGrups_ids);
				$userpseudo = $userpseudo->fetchArray();

				if ( is_array($userpseudo) && count($userpseudo) > 0 )
				{
					foreach($userpseudo as $v){
						$pseudo_array[ $v['id'] ] = array(
								'nice_name'=>$v['servicesname'],
								'phone'=>$v['phone'],
								'mobile'=>$v['mobile'],
								'userid_type'=>$v['pseudogrups'],

						);
					}
					$DailyPlanningPseudoGrups_ids = array_keys($pseudo_array);
				}else{

					$DailyPlanningPseudoGrups_ids = array("0");
				}

			}
			$userid_array ['pseudogrups'] = $pseudo_array;
				
			//add this ids
			foreach($DailyPlanningPseudoGrups_ids as $v){
				$DailyPlanningUsers_ids[] =  $v;
			}




			$ipid = false;
			if (  $allweek != "0" &&  $_POST['pdf_print_patients'] != "all_patients"  && $_POST['pdf_print_patients'] != "none"){
				//get this ipid cause we have a single patient selected
				$decrypt_id = Pms_Uuid::decrypt($_POST['pdf_print_patients']);
				$ipid = Pms_CommonData::getIpId( (int)$decrypt_id );
			}

			//get all planed visits
			$DailyPlanningVisits_allweek = DailyPlanningVisits :: get_patients_visits_by_date_interval($clientid, array("start"=>$week_days[1], "end"=>$week_days[7]), $DailyPlanningUsers_ids, $ipid);

				
			$userid_Visits = array_unique (array_column($DailyPlanningVisits_allweek , "userid"));
			$ipds_Visits = array_unique (array_column($DailyPlanningVisits_allweek , "ipid"));
				

			//get info on all the ipids
			$patients_details = PatientMaster :: get_multiple_patients_details_customcolumns($ipds_Visits ,
					array("id") ,
					array(	"first_name",
							"last_name",
							"kontactnumber",
							"phone",
							"mobile",
							"street1",
							"zip",
							"city"
					)
					);
			
			//ISPC-2045 - multiple contact pphone by reference
			foreach ($patients_details as &$row) {
				
				if ( ! empty($row['PatientContactphone']) ) {
				
					//ISPC-2045
					$PatientContactphone = array_column($row['PatientContactphone'], 'phone_number');
					$row['kontactnumber'] = implode("; ", $PatientContactphone);
				}
			}

				
			$patients_active_location = PatientLocation::get_all_location_by_date_interval($ipds_Visits , array("start"=>$week_days[1], "end"=>$week_days[7]), $clientid );
				
			$location_cp_ipids = array();
			foreach( $patients_active_location as $kpat=>$v_pat_loc)
			{
				$patient2location[$v_pat_loc['ipid']] = $v_pat_loc['location_id'];
				$locid = substr($v_pat_loc['location_id'], 0, 4);
					
				if($locid == '8888')
				{
					$location_cp_ipids[] = $v_pat_loc['ipid'];
				}
				else
				{
					$locations_ids[] = $v_pat_loc['location_id'];
				}
			}
 
			if(count($location_cp_ipids) > 0)
			{
				$location_contact_persons = ContactPersonMaster::get_contact_persons_by_ipids($location_cp_ipids, false, false);
				
				
				//TODO-3756 Ancuta 26.01.2021
				$location_contact_persons_array = array();
				foreach ($location_contact_persons as $ipid=>$cnts){
				    $reset_nr = 1;
				    foreach($cnts as $k=>$cnt_data){
				        $location_contact_persons_array[$ipid]['8888'.$reset_nr] = $cnt_data;
				        $reset_nr++;
				    }
				}
				
				//--
			}
			$locations_master_res = $locations_master = array();
			if(count($locations_ids) > 0)
			{
				//TODO-2874 bug in PDF generation for Tourenplan: Ancuta 31.01.2020:: added client id  and isdelete condition 
				$locmaster = Doctrine_Query::create()
				->select("street, zip , city, phone1,  AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location, location_type")
				->from('Locations')
				->whereIn('id', $locations_ids)
				->andWhere('client_id = ?',$clientid)
				->andWhere('isdelete = 0 ');
				$locations_master_res = $locmaster->fetchArray();
				foreach($locations_master_res as $loc){
					$locations_master[ $loc['id'] ] = $loc;
				}
			}
			//print_r($locations_master);
				
			$location_by_day = array();
			foreach($week_days as $k=> $day){
					
				$daytime = strtotime($day);
				foreach($patients_active_location as $location){
					//TODO-2874 bug in PDF generation for Tourenplan: Ancuta 31.01.2020:: added  check after location id and ipid 
				    if($location['location_id'] != 0 && !empty( $location['ipid'] )){
				        
    					//if
    					$valid_from = strtotime( date("Y-m-d", strtotime($location['valid_from'])));
    					$valid_till = strtotime( date("Y-m-d", strtotime($location['valid_till'])));
    					if( ( $valid_from <= $daytime && $valid_till>=$daytime )
    							||
    							( $valid_from <= $daytime && $location['valid_till'] == "0000-00-00 00:00:00")){
    									
    								$location_by_day [$k] [ $location['ipid'] ] [ $location['location_id'] ] = $location;
    								$location_by_day [$day] [ $location['ipid'] ] [ $location['location_id'] ] = $location;
    
    					}
				    }
				}
			}
			//print_r($location_by_day);
				
			$pdf_post = array();
			$cnt = 0;
			$count_users_with_planned_visits = array();
			//ISPC 2369
			 if ( $allweek != "0"  && $_POST['pdf_print_patients'] != "all_patients"  && $_POST['pdf_print_patients'] != "none"){
				array_multisort(array_column($DailyPlanningVisits_allweek, 'hour'), SORT_ASC, SORT_NUMERIC, $DailyPlanningVisits_allweek);
			} 
			//ISPC 2369
			foreach($DailyPlanningVisits_allweek as $visit){

				//ispc-1855
				$pseudogroup_doctors_nicename = "";
				if($visit['userid_type'] == 'pseudogrups'
						&& isset($pseudo_tours[ $visit['userid'] ])
						&& isset($pseudo_tours_by_day[ date("Y-m-d",  strtotime($visit['date'])) ] [ $pseudo_tours[ $visit['userid'] ] ] ))
				{
					$pseudogroup_doctors = array();
					foreach ($pseudo_tours_by_day[ date("Y-m-d",  strtotime($visit['date'])) ] [ $pseudo_tours[ $visit['userid'] ] ] as $pseudo_doctor){

						if (isset($userid_array[$pseudo_doctor]) ) {
							//&& $userid_array[$pseudo_doctor]['makes_visits'] !=0 ) {
								
							//and user makes_visits == 1 or user has shift today
								
							if(!in_array($userid_array[$pseudo_doctor]['nice_name'], $pseudogroup_doctors)){
    							$pseudogroup_doctors[] = $userid_array[$pseudo_doctor]['nice_name'];
							}
						}
					}
					if(count ($pseudogroup_doctors) > 0){
						$pseudogroup_doctors_nicename = " (" . implode(" / ", $pseudogroup_doctors). ")";
					}
				}
				

				//$day_of_the_week = date("N",  strtotime($visit['date']) ); //1 to 7
				$day_of_the_week = array_search (date('Y-m-d', strtotime($visit['date'])), $week_days);
								
				
				$this_user_details = $userid_array  [ $visit['userid_type'] ] [ $visit['userid'] ];
				$this_user_details['nice_name'] .= $pseudogroup_doctors_nicename;

				if ( $_POST['pdf_print_users'] != "none" && $_POST['pdf_print_users'] != "all_users"){
					$pdf_post [$day_of_the_week]['pseudogroup_doctors_nicename'] = "<br/>".$pseudogroup_doctors_nicename;
				}else{
					$pdf_post [$day_of_the_week]['pseudogroup_doctors_nicename'] = '';
				}
				
				// TODO-2874 bug in PDF generation for Tourenplan Ancuta 31.01.2020 add location ONLY if visit HAS IPID
				if($visit['ipid']){
    				$patient_location_this_day  = $location_by_day [date('Y-m-d', strtotime($visit['date']))] [ $visit['ipid'] ];
				} else {
				    $patient_location_this_day = array();
				}
				$visit['actual_location'] = $patient_location_this_day;//$location_by_day [$day_of_the_week] [ $visit['ipid']  ];
				$visit['patients_details'] = $patients_details[ $visit['ipid'] ];
					
				if($ipid!==false){
					$pdf_post [$day_of_the_week] ["actual_location"] = $patient_location_this_day;//$location_by_day [$day_of_the_week] [ $visit['ipid']  ];
				}



				$userid_array [ $visit['userid_type'] ] [ $visit['userid'] ] ['users_viewmode'] = $DailyPlanningUsers_viewmode[ $visit['userid_type'] ][ $visit['userid'] ] [ $visit['date'] ];
					
				if ($userid_type == 'pseudogrups' && $visit['userid_type']== 'user'){

				}else{
						
					$pdf_post [$day_of_the_week]  [ $visit['userid_type'] ] [ $visit['userid'] ]['user_details']  = $this_user_details;
						
					$pdf_post [$day_of_the_week]  [ $visit['userid_type'] ] [ $visit['userid'] ]['planned_visits'] [] = $visit;

					$count_users_with_planned_visits [$day_of_the_week] [$visit['userid'] ] = 1;
				}
				$cnt++;


			}
			//  				$this->view->pdf_post = $pdf_post;
			// 					$this->view->locations_master = $locations_master;
			// 					$this->view->location_contact_persons = $location_contact_persons;
			// 					$this->view->week_days = $week_days;
				
			//$post_data['active_patients'] = $active_patients;
				
			$post_data['count_users_with_planned_visits'] = $count_users_with_planned_visits;
			$post_data['pdf_post'] = $pdf_post;
			$post_data['locations_master'] = $locations_master;
			$post_data['location_contact_persons'] = $location_contact_persons;
			$post_data['location_contact_persons_array'] = $location_contact_persons_array;//TODO-3756 Ancuta 26.01.2021
			$post_data['week_days'] = $week_days;
			//$name_of_this_day = date( "l" , strtotime($day_planning['plan_date']));
				
			if($allweek == "0"){
				//old-default print pdf button
				$template_file = "day_planning_pdf.html";
				$week_word = $week_days[1];

				$post_data['day_of_the_week'] = $day_of_the_week;
				//echo "<pre>";
				//print_r($post_data);die();
			}
			elseif ( $_POST['pdf_print_patients'] != "all_patients"  && $_POST['pdf_print_patients'] != "none"){
				//print just one patient
				$template_file = "day_planning_pdf_allweek_patient.html";
				if($ipid!==false){
						
					$post_data['patient_array'] = $patients_details[ $ipid ];
					// 							$this->view->patient_array = $patients_details[ $ipid ];
				}
			}
			elseif ( $_POST['pdf_print_users'] != "none" && $_POST['pdf_print_users'] != "all_users"){
				// print just one doctor
				$template_file = "day_planning_pdf_allweek_doctor.html";
				$post_data['userid_array'] = $userid_array [$userid_type] [ $user ];
				// 						$this->view->userid_array = $userid_array [ (int)$_POST['pdf_print_users'] ];
					
			}else{
				$template_file = "day_planning_pdf_allweek.html";
				$post_data['patient_array'] = $userid_array [ (int)$_POST['pdf_print_users'] ];
				// 						$this->view->patient_array = $userid_array [ (int)$_POST['pdf_print_users'] ];
			}
			$post_data['we_have_errors'] =  false;
				
			if (count($post_data['pdf_post']) == 0 ){
				//no plan found
				$template_file = "day_planning_pdf_allweek_doctor.html";
				$post_data['we_have_errors'] = $we_have_errors;
			}
				
		}else{
			//no plan for this doctors
			$template_file = "day_planning_pdf_allweek_doctor.html";
			$post_data['we_have_errors'] = $we_have_errors;
		}

		$this->generate_pdf($post_data, $week_word . " ". "Tagesplanung" , $template_file);
		// 				$this->renderScript('templates/'.$template_file);

		exit;

	}

	public function dayplanningnewAction()
	{
	    $boxColsUsers = [0 => 301, 1 => 302, 2 => 303];
	    $boxColsPseudogroups = [0 => 304, 1 => 305, 2 => 306];
	    
		setlocale(LC_ALL, 'de_DE.UTF8');
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$hidemagic = Zend_Registry::get('hidemagic');

		/*
		 * $has_link_permissions 
		 * 		= true 	<=>	user has full edit/view 
		 * 		= false	<=> user can only view inside the page and print pdf's with the plan
		 */
		$has_link_permissions = Links::checkLinkActionsPermission();
		$this->view->has_link_permissions = $has_link_permissions;
		
		//checkPrevilege return true
		/*
			$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('Roster', $logininfo->userid, 'canview');
		if(!$return)
		{
		$this->_redirect(APP_BASE . "error/previlege");
		}
		*/
			
		if ($this->getRequest()->isXmlHttpRequest()) {
		    
		    $this->_helper->layout->setLayout('layout_ajax');
		    $this->_helper->viewRenderer->setNoRender();
		    
		    switch ($this->getRequest()->getPost('__action')) {
		        case "updateBoxOrder" :
		            
		            $boxCols = [];
		            
		            $type = $this->getRequest()->getPost('type'); //users || pseudogroups
		            
		            if ($type =='users') {
		                $boxCols = $boxColsUsers;
		            } elseif($type =='pseudogroups') {
		                $boxCols = $boxColsPseudogroups;
		            } else {
		                //unknown type.. maybe throw error  
		                exit;
		            }
		            
		            $orders = $this->getRequest()->getPost('order');
		            
		            foreach ($orders as $col => $order) {

		                $col = $boxCols[$col];
		                
		                $this->__updateBoxOrder( ['col' => $col, "order" => $order]);
		            }
		            
		            $responsArr = array(
		                'success' => true,
		                'message' => "box sorted",
		            );
		             
		            $this->_helper->json($responsArr, true);
		            exit; //for read-ability		            
		            break;
		            
		    }
		}
		
		
		if($this->getRequest()->isGet() && ! $this->getRequest()->getParam('date'))
		{
		    $this->redirect(APP_BASE . $this->getRequest()->getControllerName() . "/" .  $this->getRequest()->getActionName() . '?date=' . strtotime('today'), array(
		        "exit" => true
		    ));
		    
		    exit; // for read-ability
		}
			
		$clientid = $logininfo->clientid;
		$patient_master = new PatientMaster();
			
			

		if($_REQUEST['date'])
		{
			if($_REQUEST['sel'] == 1)
			{
				$date = date('Y-m-d 00:00:00', strtotime($_REQUEST['date']));
			}
			else
			{
				$date = date('Y-m-d 00:00:00', $_REQUEST['date']);
			}
		}
		else
		{
			$date = date('Y-m-d 00:00:00', time());
		}
			
		//post for pdf_print_action - allow all users
		if($this->getRequest()->isPost())
		{
			//@TODO - this permision check is only performed on link access, NO fine grained policy canedit/view

			//ispc-1533
			// custom print of the week of this date
			if( !empty($_POST['pdf_print_action']) && $_POST['pdf_print_action'] == 1 ) //&& $_POST['pdf_print_period'] == "allweek"
			{
				$this->dayplanningnew_allweekpdf( $date, $clientid , $_POST['pdf_print_period']);
				return;

			}
		}

		
			//get all client shifts
			$c_shifts = new ClientShifts();
			$client_shifts = $c_shifts->get_client_shifts($clientid);
			/* ###################### DEFAULTS - Current day - today ############################### */
				

			$today = date('Y-m-d 00:00:00', time());
			$month_start = date('Y-m', strtotime($date))."-01";
				
			if(!function_exists('cal_days_in_month'))
			{
				$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($month_start)), 1, date("Y", strtotime($month_start))));
			}
			else
			{
				$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($month_start)), date("Y", strtotime($month_start)));
			}
			$month_end = date('Y-m', strtotime($month_start)).'-'.$month_days;
			$selected_date_month_days = $patient_master->getDaysInBetween($month_start, $month_end);
			$this->view->current_month_days = $selected_date_month_days;


			$previous_date = date('Y-m-d 00:00:00', mktime(date('H', strtotime($date)), date('i', strtotime($date)), 0, date('m', strtotime($date)), date('d', strtotime($date)) - 1, date('Y', strtotime($date))));
			$next_date = date('Y-m-d 00:00:00', mktime(date('H', strtotime($date)), date('i', strtotime($date)), 0, date('m', strtotime($date)), date('d', strtotime($date)) + 1, date('Y', strtotime($date))));

			/* ###################### DEFAULTS - Current day - today ############################### */
			$day_planning['previous_date'] = date('d.m.Y', strtotime($previous_date));
			$day_planning['current_date'] = date('d.m.Y', strtotime($date));
			$day_planning['next_date'] = date('d.m.Y', strtotime($next_date));

			if($this->getRequest()->isPost())
			{
				if($_POST['date_action'] == 1 && !empty($_POST['date']))
				{
					$this->_redirect(APP_BASE . "roster/dayplanningnew?date=" . strtotime($_POST['date']));
					exit;
				}
			}

			/* ###################### POST - SAVE/EDIT/DELETE visits ############################### */
			if($this->getRequest()->isPost())
			{
				if(!$has_link_permissions)
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}

				if(empty($_POST['pdf_print_action']))
				{
					$save_visit_form = new Application_Form_DailyPlanningVisits();

					/* ########################### SAVE VISIT ######################################### */
					$a_post = $_POST;
					$a_post['current_date'] = $date;
					$a_post['clientid'] = $clientid;

					$save_action = $save_visit_form->save_multiple_visits($a_post);
					//$this->_redirect(APP_BASE . "roster/dayplanningnew?date=" . strtotime($date));
					 
					FormsEditmodeTable::finishedEditing([
					    'pathname' => $this->getRequest()->getControllerName() . "/" . $this->getRequest()->getActionName(),
					    'client_id' => $this->logininfo->clientid,
					    'patient_master_id' => null,
					    'user_id' => $this->logininfo->userid,
					    'search' => '?date='. strtotime($date),
					    'is_edited' => 'yes',
					]);
				}
			}

			/* ########################### ADD USERS TO PLAN  #########################################*/
			if($this->getRequest()->isPost())
			{
				if(!$has_link_permissions)
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
					
				if($_POST['save_users_to_plan'] == "1")
				{
						
					$users_with_plan_this_day = array();
					foreach($_POST['visit_order'] as $userid => $hours){
							
						$hours_arr = $hours;
						reset($hours_arr);
						$first = current($hours_arr);
						if ($first[0]['data_usertype'] !='user' && $first[0]['data_usertype'] !='pseudogrups'){
							$first[0]['data_usertype'] ='user';
						}
							
						$users_with_plan_this_day[$first[0]['data_usertype']][$userid] = array('userid'=>$userid , 'userid_type'=>$first[0]['data_usertype']);
					}


					$DailyPlanningUsers_today = DailyPlanningUsers :: get_users_by_date_interval(
							$clientid ,
							array("start"=>date("Y-m-d 00:00:00", strtotime($date)), "end"=>date("Y-m-d 23:59:59", strtotime($date))) ,
							false);

					$clean_data_users= array();
					foreach($DailyPlanningUsers_today as $k=>$plan){
							
						if(empty($users_with_plan_this_day [ $plan['userid_type'] ] [ $plan['userid'] ])){
							//clean this id in DailyPlanningUsers
							$clean_data_users[] =  $plan['id'];
							unset($DailyPlanningUsers_today[$k]);
						}else{
							$users_with_plan_this_day [ $plan['userid_type'] ] [ $plan['userid'] ]['id'] =  $plan['id'];
							//verify if viewmode is the same
							//if $plan['view_mode'] != $_POST['master_viewmode']
						}
							
					}

					if(!empty($clean_data_users))
					{
						$dutyuset = Doctrine_Query::create()
						->update('DailyPlanningUsers')
						->set('isdelete', "1")
						->where(" clientid= ?",  $clientid )
						->andWhereIn('id', $clean_data_users)
						->execute();
					}
						
					$insert_DailyPlanningUsers = array();
					foreach( $users_with_plan_this_day as $type=>$userid_arr ){
						foreach ($userid_arr as $k=>$v){
							if (!empty($v['id'])){
								//update
								//this are old users that allready had plan this day, just update viewmode
								$update = Doctrine::getTable('DailyPlanningUsers')->find($v['id']);
								$update->view_mode = $_POST['master_viewmode'];
								$update->save();
									
							}else{
								//insert new
								$insert_DailyPlanningUsers[] = array(
										"userid_type" => $type,
										"clientid" => $clientid,
										"userid" => $v['userid'],
										"date" => $date,
										"view_mode" =>	$_POST['master_viewmode']
								);
							}
						}
					}
						

					//this are new users that now have plans
					if (!empty($insert_DailyPlanningUsers))
					{
						$collection = new Doctrine_Collection('DailyPlanningUsers');
						$collection->fromArray($insert_DailyPlanningUsers);
						$collection->save();
					}
						

					$this->_redirect(APP_BASE . "roster/dayplanningnew?date=" . strtotime($date));
				}
					
			}
				
				
				
				
				
				
				
			$user_planning = new DailyPlanningUsers();
			$userids2date = $user_planning->get_users_by_date($clientid, $date, false, true, $allowed_deleted = 1);
			$users_details2date = $user_planning->get_users_by_date($clientid, $date, false);

				

			/* ###################### Users Groups- get groups  that are allowed to shou in dienstplan ############################### */
			$docquery = Doctrine_Query::create()
			->select('id')
			->from('Usergroup')
			->where('clientid="' . $clientid . '"')
			->andWhere('isdelete="0"')
			->andWhere('isactive=1');
			$groups = $docquery->fetchArray();

// 			$groups_ids[] = '9999999999';
			$groups_ids = array();
			foreach($groups as $k_gr => $v_gr)
			{
				$groups_ids[] = $v_gr['id'];
			}
			if ( empty($groups_ids)) {
				
				return; //something went wrong
			}
			
			/* ###################### Users - get user details ############################### */
			$doc = Doctrine_Query::create()
			->select('id, first_name, last_name, phone, mobile , user_title, usercolor, shortname, isactive, isactive_date, makes_visits')
			->from('User')
			->Where('clientid=' . $clientid)
			->andWhere('isdelete=0')
			->andWhere('usertype!="SA"')
			->andWhereIn('groupid', $groups_ids)
			//ipcs-1533
			//->andWhere('( isactive=0 or (isactive=1 AND DATE(isactive_date) > DATE(\''.$date . '\') ) )')
			//->andWhere('makes_visits = ? ', 1)
			->orderBy('last_name ASC');
			$docarray = $doc->fetchArray();

			$client_users_ids = array();
			foreach($docarray as $d_key => $d_val)
			{
				$all_client_users[$d_val['id']] = $d_val;


				if ($d_val['isactive'] == 0
						|| strtotime($d_val['isactive_date']) == "0000-00-00"
						|| strtotime($d_val['isactive_date']) > strtotime($date) )
				{
						

					$client_users_ids[] = $d_val['id'];
					$client_users[$d_val['id']]['id'] = $d_val['id'];
					$client_users[$d_val['id']]['name'] = $d_val['last_name'] . ", " . $d_val['first_name'];
					$client_users[$d_val['id']]['phone'] = $d_val['phone'];
					$client_users[$d_val['id']]['mobile'] = $d_val['mobile'];
					$client_users[$d_val['id']]['user_title'] = $d_val['user_title'];
					$client_users[$d_val['id']]['last_name'] = $d_val['last_name'];
					$client_users[$d_val['id']]['first_name'] = $d_val['first_name'];
					$client_users[$d_val['id']]['makes_visits'] = $d_val['makes_visits'];

					if(!empty($d_val['usercolor']))
					{
						$client_users[$d_val['id']]['usercolor'] = $d_val['usercolor'];
					}
					else
					{
						$client_users[$d_val['id']]['usercolor'] = "cccddd";
					}

					if(!empty($d_val['shortname']))
					{
						$client_users[$d_val['id']]['shortname'] = $d_val['shortname'];
					}
					else
					{
						$client_users[$d_val['id']]['shortname'] = substr($d_val['last_name'], 0, 1) . substr($d_val['first_name'], 0, 1);
					}
				}
			}
			//this $users_with_isinactive should not be displayed, or should have no right to add any new visit
			$users_with_isinactive = array_diff (array_keys($all_client_users) , array_keys($client_users));
			$users_with_isinactive = array_combine($users_with_isinactive, $users_with_isinactive);
				
			if( empty($client_users_ids))
			{
				return; //something went wrong
			}

			/* ###################### users - duty roster for today ############################### */
			$docid = Doctrine_Query::create()
			->select('id, clientid, duty_date, userid, user_group, shift, fullShift, row, shiftStartTime, shiftEndTime')
			->from('Roster')
			->where('clientid = ?' , $clientid)
			->andWhereIn('userid', $client_users_ids)
			->andWhere("DATE(duty_date) = DATE('" . $date . "') ")
			->andWhere('isdelete = ?', 0);
			$rostarray = $docid->fetchArray();
			//print_r($rostarray);die();
				

			//remove users with (makes_visits <> 1 && without shift today && no manual visit)
			//functionality continues at a later step to remove users without manual visit
			$Roster_userid = array();
			if ( count($rostarray) > 0 ){
				$Roster_userid = array_column($rostarray, "userid");
			}
			foreach ( $client_users as $k => $v ) {
				if ( $v['makes_visits'] != 1 && !in_array($v['id'], $Roster_userid)){
					unset($client_users[$k]);
					//unset also the id
					if(($key = array_search($v['id'], $client_users_ids)) !== false) {
						unset($client_users_ids[$key]);
					}
				}
			}
				
				
			$user_shifts = array();
			foreach($rostarray as $r_key => $r_value)
			{
				$user_roster[$r_value['userid']] = $r_value;
				//create user shifts array
				$user_shifts[$r_value['userid']][$r_value['id']] = $r_value['shift'];

				if ($client_shifts [ $r_value['shift'] ] ['isholiday'] == "0"){
					$client_roster_user_ids[] = $r_value['userid'];
				}
			}

			$user_has_shift_today = array();
			foreach($client_users_ids as $k_uid => $v_uid)
			{
				if(!array_key_exists($v_uid, $user_shifts))
				{
					$users_shift_start[$v_uid]['time'] = strtotime(date('Y-m-d', time())." 08:00:00");
					//$users_shift_start[$v_uid]['start_hour'] = "08";
				}
				else
				{
					$user_has_shift_today[$v_uid] = 1;
						
					foreach ($user_shifts[$v_uid] as $id => $shift){
						$users_shift_start[$v_uid] [ $id ] ['time'] = strtotime(date('Y-m-d', time())." ".date('H:i:s', strtotime($client_shifts[ $shift ]['start'])));
						$users_shift_start[$v_uid] [ $id ] ['start_hour'] = date('G', strtotime($client_shifts[ $shift ]['start']));
						$users_shift_start[$v_uid] [ $id ] ['end_hour'] = date('G', strtotime($client_shifts[ $shift ]['end']));
						$users_shift_start[$v_uid] [ $id ] ['color'] = $client_shifts[ $shift ]['color'];
						$users_shift_start[$v_uid] [ $id ] ['isholiday'] = $client_shifts[ $shift ]['isholiday'];
						$users_shift_start[$v_uid] [ $id ] ['start'] = strtotime($client_shifts[ $shift ]['start']);
						$users_shift_start[$v_uid] [ $id ] ['end'] = strtotime($client_shifts[ $shift ]['end']);
						$users_shift_start[$v_uid] [ $id ] ['istours'] = ($client_shifts[ $shift ]['istours']);


					}
					/*
					 $users_shift_start[$v_uid]['time'] = strtotime(date('Y-m-d', time())." ".date('H:i:s', strtotime($client_shifts[$user_shifts[$v_uid]]['start'])));
					 $users_shift_start[$v_uid]['start_hour'] = date('G', strtotime($client_shifts[$user_shifts[$v_uid]]['start']));
					 $users_shift_start[$v_uid]['end_hour'] = date('G', strtotime($client_shifts[$user_shifts[$v_uid]]['end']));
					 $users_shift_start[$v_uid]['color'] = $client_shifts[$user_shifts[$v_uid]]['color'];
					 $users_shift_start[$v_uid]['isholiday'] = $client_shifts[$user_shifts[$v_uid]]['isholiday'];
					 */
				}
			}
			//print_r($user_has_shift_today);
			//print_r($users_shift_start);die();
				
			$this->view->users_json_shifts = json_encode($users_shift_start);
			$this->view->user_has_shift_today = json_encode($user_has_shift_today);
			$this->view->user_has_shift_today_roster = $client_roster_user_ids;
				

			foreach($client_users as $k_userid => $u_values)
			{
				$client_users[$u_values['id']]['roster'] = $user_roster[$u_values['id']];
				$client_users[$u_values['id']]['active_today'] = $users_details2date[$u_values['id']];
			}
				
			/* ###################### Client settings ############################### */
			$client_data = Pms_CommonData::getClientData($clientid);
			$tagesplanung_standby_patients = $client_data['0']['tagesplanung_standby_patients']; // ISPC-1170 client setting show patients standby in tagesplanung
				
			//ispc-1533
			$tagesplanung_default_visit_time = $client_data['0']['tagesplanung_default_visit_time'];
			$tagesplanung_only_user_with_shifts = $client_data['0']['tagesplanung_only_user_with_shifts']; // ISPC-1170 client setting show patients standby in tagesplanung

			$this->view->tagesplanung_only_user_with_shifts = $tagesplanung_only_user_with_shifts ;
			
			if ((int)$client_data['0']['tourenplanung_settings']['workhours_start'] > (int)$client_data['0']['tourenplanung_settings']['workhours_end']) {
			    $workhours_start = $client_data['0']['tourenplanung_settings']['workhours_start'];
			    $client_data['0']['tourenplanung_settings']['workhours_start'] = $client_data['0']['tourenplanung_settings']['workhours_end'];
			    $client_data['0']['tourenplanung_settings']['workhours_end'] = $workhours_start;
			}
			
			$this->view->tourenplanung_settings = $client_data['0']['tourenplanung_settings'] ;
			//xxxxxxxxxxxxxxxxxxxxxx
				
			if(1==1 || $tagesplanung_standby_patients == '0') // only active patients
			{

				/* ###################### users - get visits for today ############################### */
				$patient_plan_visits = new DailyPlanningVisits();
				$patient_plan_visits_array = $patient_plan_visits->get_patients_visits_v2($clientid, $date, false, false);
				if (is_array($patient_plan_visits_array) && count($patient_plan_visits_array) >0){
					$visiting_ipids = array_column( $patient_plan_visits_array , "ipid");
				}

				//remove users with (makes_visits <> 1 && without shift today && no manual visit)
				//functionality to remove users without manual/auto visit
				$users_with_planned_visits_today = array();
				foreach ( $patient_plan_visits_array as $k => $v) {
					//&& $v['is_autoassigned'] == 0
					if ( $v['userid_type'] == 'user' ) {
						$users_with_planned_visits_today [] = $v['userid'];
					}
				}
				foreach ( $client_users as $k => $v ) {
					if ( $v['makes_visits'] != 1 && !in_array($v['id'], $users_with_planned_visits_today)){
						unset($client_users[$k]);
						//unset also the id
						if(($key = array_search($v['id'], $client_users_ids)) !== false) {
							unset($client_users_ids[$key]);
						}
					}
				}

				/* ###################### Active patients ############################### */
				if(strtotime($date) >= strtotime($today))
				{
					if($tagesplanung_standby_patients == '0'){
						$active_patients = $this->get_now_active_patients(false , $visiting_ipids);
					}
					else{
						$active_patients = $this->get_now_active_patients(true , $visiting_ipids);
					}
				}
				else
				{
					$sql = "p.ipid,e.ipid,a.ipid, p.isstandby, p.isdischarged";
					$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
					$sql .= ",AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
					$sql .= ",convert(AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') using latin1) as zip";
					$sql .= ",convert(AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') using latin1) as street1";
					$sql .= ",convert(AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') using latin1) as city";
					$sql .= ",convert(AES_DECRYPT(p.phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone";
					$sql .= ",convert(AES_DECRYPT(p.mobile,'" . Zend_Registry::get('salt') . "') using latin1) as mobile";
					$sql .= ",convert(AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') using latin1) as sex";
						
					if($logininfo->usertype == 'SA')
					{
						$sql = "p.ipid,e.ipid,a.ipid, p.isstandby, p.isdischarged,";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
					}

					$date_filter = array('0' => array('start' => $date, 'end' => $date));

					if($tagesplanung_standby_patients == '0'){
						$active_patients_ipids = Pms_CommonData::patients_active($sql, $clientid, $date_filter, false, "convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)", "ASC"); // BRE patient list
					}
					else{
						$active_patients_ipids = Pms_CommonData::patients_active($sql, $clientid, $date_filter, false, "convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)", "ASC",false,false,'0',true); // BRE patient list
					}
						
					foreach($active_patients_ipids as $k => $p_values)
					{
						$active_patients['ipids'][] = $p_values['ipid'];
						$active_patients['details'][] = $p_values['PatientMaster'];
					}
				}

				if(empty($active_patients['ipids']))
				{
					$active_patients['ipids'][] = "999999";
				}

				//fetch extra ipids details //ispc-1533 2g)
				$ipids_with_not_details = array_diff( $visiting_ipids , $active_patients['ipids']);
					
				if( count ($ipids_with_not_details) > 0 ){
					$ipids_with_not_details = PatientMaster :: get_multiple_patients_details_customcolumns($ipids_with_not_details ,
							array("id, isstandby, isdischarged") ,
							array(	"first_name",
									"last_name",
									"kontactnumber",
									"phone",
									"mobile",
									"street1",
									"zip",
									"city"
							)
							);
					foreach($ipids_with_not_details as $ipid => $val ){
						$active_patients['ipids'][] = $ipid;
						$active_patients['details'][] = $val;
					}
					//die("xtra");
				}


				//visits planned today
				foreach($patient_plan_visits_array as $key => $pvvalues)
				{
					if ($pvvalues['userid_type'] == "user"){

						$client_users[$pvvalues['userid']]['planned_visits'][] = $pvvalues;

					}elseif ($pvvalues['userid_type'] == "pseudogrups"){

						$pseudo_grups[$pvvalues['userid']]['planned_visits'][] = $pvvalues;
					}

					if(in_array($pvvalues['ipid'], $active_patients['ipids']))
					{
						$patient_visits[$pvvalues['ipid']]['planned_visits'][] = $pvvalues;
						$patient_visits[$pvvalues['ipid']]['existing_visits'] = count($patient_visits[$pvvalues['ipid']]['planned_visits']);
					}
				}
				//print_R($patient_plan_visits_array);die();

				/* ###################### Patients details ############################### */
					

				$day_of_the_week = date("N",  strtotime($date)); //1 to 7
				$pat_visits_settings = PatientVisitsSettings :: getPatientVisitsSettingsV3( $active_patients['ipids'] , $day_of_the_week);
				foreach($pat_visits_settings as $k => $v){
					if ( $v['visit_duration']  > 0){
						$visits_settings[ $v['ipid'] ]['visit_duration']  =  (int)$v['visit_duration'];
					}
					$visits_settings[ $v['ipid'] ]['visits_per_day'] +=  $v['visits_per_day'];
					$visits_settings[ $v['ipid'] ]['visit_settings'] [] = $v;
						
					if( $v['visitor_type'] == "user" || $v['visitor_type'] == "pseudogrups" ){
						$visits_settings[ $v['ipid'] ][$v['visitor_type']][ $v['visitor_id'] ] = $v;
					}
				}

				//active location for all patients START
				$ipids_with_visits[0]['ipid'] = '999999999999';
				$incr = 1;
				foreach($patient_visits as $kipid => $v_p_visits)
				{
					$ipids_with_visits[$incr]['ipid'] = $kipid;
					$incr++;
				}

				foreach($active_patients['ipids']  as $k=>$v){
					$location_ipids_arr[]['ipid'] = $v;
				}
				//$patients_active_location = PatientLocation::getActiveLocations($ipids_with_visits);
				$patients_active_location = PatientLocation::get_all_location_from_day($active_patients['ipids'] , $date, $clientid );

				//get all patients locations $locations_ids
				$locations_ids[] = '999999999';
				$location_cp_ipids[] = '99999999999';
				foreach($patients_active_location as $kpat=>$v_pat_loc)
				{
					$patient2location[$v_pat_loc['ipid']] = $v_pat_loc['location_id'];
					$locid = substr($v_pat_loc['location_id'], 0, 4);
						
					if($locid == '8888')
					{
						$location_cp_ipids[] = $v_pat_loc['ipid'];
					}
					else
					{
						$locations_ids[] = $v_pat_loc['location_id'];
					}
				}

				if($location_cp_ipids)
				{
					$contact_persons = ContactPersonMaster::get_contact_persons_by_ipids($location_cp_ipids, false, false);
						
					foreach($contact_persons as $k_ipid => $v_contact_persons)
					{
						$z[$k_ipid] = 1;
						$cnt_number[$k_ipid] = 1; // display contact number
						foreach($v_contact_persons as $value)
						{
							if($value['isdelete'] == '0')
							{
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson ' . $cnt_number[$k_ipid] . '(' . $value['cnt_last_name'] . ' ' . $value['cnt_first_name'] . ')';
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['street'] = $value['cnt_street1'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['zip'] = $value['cnt_zip'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['city'] = $value['cnt_city'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['phone'] = $value['cnt_phone'];
								$cnt_number[$k_ipid]++;
							}
							else
							{
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson';
							}
							$z[$k_ipid]++;
						}
					}
				}
					
				$locmaster = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->whereIn('id', $locations_ids)
				->orderBy('id ASC');
				$locations_master_res = $locmaster->fetchArray();


				foreach($locations_master_res as $k_lmaster => $v_lmaster)
				{
					$master_locations[$v_lmaster['id']]['id'] = $v_lmaster['id'];
					$master_locations[$v_lmaster['id']]['location'] = $v_lmaster['location'];
					$master_locations[$v_lmaster['id']]['location_type'] = $v_lmaster['location_type'];
					$master_locations[$v_lmaster['id']]['street'] = $v_lmaster['street'];
					$master_locations[$v_lmaster['id']]['zip'] = $v_lmaster['zip'];
					$master_locations[$v_lmaster['id']]['city'] = $v_lmaster['city'];
					$master_locations[$v_lmaster['id']]['phone'] = $v_lmaster['phone1'];
				}

				foreach($patient2location as $k_res_pat => $v_res_pat)
				{
					if(!empty($master_locations[$v_res_pat]))
					{
						$patient_active_locations[$k_res_pat] = $master_locations[$v_res_pat];
					}
					else
					{
						$patient_active_locations[$k_res_pat] = $master_locations[$k_res_pat][$v_res_pat];
					}
				}
				//active location for all patients END
				
				//ISPC-1987 p.3
				//treatted-by doctors append short-names
				$PatientQpaMapping_users_array = PatientQpaMapping::get_assigned_userid( array("ipids" => $active_patients['ipids'], "epids"=>array()) );	
				$PatientQpaMapping_ipis_arr = $PatientQpaMapping_users_array['ipids'];
				$PatientQpaMapping_users_array = array_unique(call_user_func_array('array_merge', $PatientQpaMapping_ipis_arr));
								
				$assigned_users_arr = User::getUsersNiceName($PatientQpaMapping_users_array , $clientid);
				foreach ($assigned_users_arr  as $row) {
					if (isset($all_client_users[$row['id']])) {
						$all_client_users[$row['id']] ['initials'] = $row['initials'];
						$all_client_users[$row['id']] ['nice_name'] = $row['nice_name'];
						$all_client_users[$row['id']] ['isdelete'] = $row['isdelete'];
						$all_client_users[$row['id']] ['isactive'] = $row['isactive'];
						$all_client_users[$row['id']] ['isactive_date'] = $row['isactive_date'];
// 						isdelete
					} else {
						$all_client_users[$row['id']]  = $row;
					}
				}//treatted-by end
				
				//get the leading star and leading user
				$modules = new Modules();
				$leading_users_module = $modules->checkModulePrivileges("119", $logininfo->clientid);
				$leading_users_array = array();
				if($leading_users_module)
				{
					// class="user-star'.$leading.'
					$show_star = "1";
					$leading_users_array = PatientQpaLeading::get_assigned_userid(array("ipids" => $active_patients['ipids'], "epids"=>array()));
					$leading_users_array = $leading_users_array['ipids'];
				}
				
				//ISPC-2545 Lore 16.03.2020
				$sapv_verordnung_arry = array();
				$view_verordnung_type = $modules->checkModulePrivileges("223", $logininfo->clientid);
				if($view_verordnung_type){
				    
				    $als = array("1" => "Beratung", "2" => "Koordination", "3" => "Teilversorgung", "4" => "Vollversorgung");
				    $sapv_arr = array('1' => 'b', '2' => 'k', '3' => 't', '4' => 'v');
				    $sapv_verordnet_details = array();
				    $icons_sapv_map = array();
				    
				    //get sapv images custom or not
				    $icons_client = new IconsMaster();
				    $icons_sapv = $icons_client->get_system_icons($clientid, false, false, true);
				    
				    foreach($icons_sapv as $k_icon_sapv => $v_icon_sapv)
				    {
				        $icons_sapv_map[$v_icon_sapv['name']] = $v_icon_sapv;
				    }
				    
				    $sapv = Doctrine_Query::create()
				    ->select("*")
				    ->from('SapvVerordnung')
				    ->whereIn('ipid', $active_patients['ipids'])
				    ->andWhere('isdelete = 0')
				    ->andWhere(' DATE("' . $date . '") BETWEEN `verordnungam` AND `verordnungbis`')
				    ->orderBy('verordnungam, verordnungbis ASC');
				    $sapv_aary = $sapv->fetchArray();
				    				    
				    foreach($sapv_aary as $key => $vals){
				        
				        $verordnet_arr = explode(',',$vals['verordnet']);
				        foreach($verordnet_arr as $key_als => $val_als){
				            $sapv_verordnung_arry[$vals['ipid']]['name'][] = $als[$val_als];
				        }
				        
				        $sapv_loop_verordnet = array();
				        $sapv_loop_statuses = array();
				        if(!empty($vals['verordnet'])) {
				            
				            if($vals['status'] == '0') {
				                $sapv_status = '3';
				            } else {
				                $sapv_status = $vals['status'];
				            }
				            
				            if(count($sapv_loop_verordnet[$vals['ipid']]) == '0') {
				                $high_veordnet_loop = '0';
				            } else {
				                $high_veordnet_loop = end($sapv_loop_verordnet[$vals['ipid']]);
				            }
				            
				            $sapv_verordnet_details[$vals['ipid']] = $verordnet_arr ;
				            asort($sapv_verordnet_details[$vals['ipid']]);
				            $high_verordnet = end($sapv_verordnet_details[$vals['ipid']]);
				            
				            $sapv_loop_statuses[$vals['ipid']][] = $sapv_status;
				            
				            //get status 2 only or status 1 or 3 if 2 is not present in loop array && only highest verordnet
				            if(($sapv_status == '2' || ( ($sapv_status == 1 || $sapv_status == 3) && !in_array('2', $sapv_loop_statuses[$vals['ipid']]) )) && $high_verordnet >= $high_veordnet_loop)
				            {
				                $sapv_loop_verordnet[$vals['ipid']][] = $high_verordnet;
				                asort($sapv_loop_verordnet[$vals['ipid']]);
				                
				                $sapv_verordnung_arry[$vals['ipid']]['image'] = $icons_sapv_map['sapv_' . $sapv_arr[$high_verordnet] . '_' . $sapv_status]['image'];

				            }
				        }
				        
				    }
				}
				//.

				$patient_details = array();
				foreach($active_patients['details'] as $pat_key => $pat_value)
				{
					$patient_details[$pat_value['ipid']]['id'] = $pat_value['id'];
					$patient_details[$pat_value['ipid']]['patient_name'] = $pat_value['last_name'] . ' ' . $pat_value['first_name'];
					$patient_details[$pat_value['ipid']]['last_name'] = $pat_value['last_name'];
					$patient_details[$pat_value['ipid']]['first_name'] = $pat_value['first_name'];
					$patient_details[$pat_value['ipid']]['street'] = $pat_value['street1'];
					$patient_details[$pat_value['ipid']]['zip'] = $pat_value['zip'];
					$patient_details[$pat_value['ipid']]['city'] = $pat_value['city'];
					$patient_details[$pat_value['ipid']]['isstandby'] = $pat_value['isstandby'];
					$patient_details[$pat_value['ipid']]['isdischarged'] = $pat_value['isdischarged'];
					if(!empty($pat_value['phone']))
					{
						$patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
					}
					elseif (!empty($pat_value['mobile']) && empty($pat_value['phone']))
					{
						$patient_details[$pat_value['ipid']]['phone'] = $pat_value['mobile'];
					}
					else
					{
						$patient_details[$pat_value['ipid']]['phone'] = '';
					}
					/* $patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
					 $patient_details[$pat_value['ipid']]['mobile'] = $pat_value['mobile']; */
					if(!empty($patient_active_locations[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['actual_location'] = $patient_active_locations[$pat_value['ipid']];
					}

					// all patient visits planned for today
					$patient_details [$pat_value['ipid']] ['planned_visits'] = $patient_visits[$pat_value['ipid']]['planned_visits'];

					if(!empty($visits_settings[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['has_visit_settings'] = true; // has settings in Stammdaten>>Tourenplanung
						$patient_details[$pat_value['ipid']]['visits_per_day'] = $visits_settings[$pat_value['ipid']]['visits_per_day'];
						$patient_details[$pat_value['ipid']]['visit_duration'] = $visits_settings[$pat_value['ipid']]['visit_duration'];
						$patient_details[$pat_value['ipid']]['visit_settings'] = $visits_settings[$pat_value['ipid']];
						if ($patient_details[$pat_value['ipid']]['visit_duration'] == "0" || $patient_details[$pat_value['ipid']]['visit_duration'] == "") $patient_details[$pat_value['ipid']]['visit_duration'] = $tagesplanung_default_visit_time;

					}
					else
					{
						$patient_details[$pat_value['ipid']]['has_visit_settings'] = false;
						$patient_details[$pat_value['ipid']]['visits_per_day'] = '1';
						$patient_details[$pat_value['ipid']]['visit_duration'] = $tagesplanung_default_visit_time;
					}

					// patient - remaining visits per day
					$patient_details[$pat_value['ipid']]['visits_remaining_per_day'] = $patient_details[$pat_value['ipid']]['visits_per_day'] - $patient_visits[$pat_value['ipid']]['existing_visits'];

					$patient_details[$pat_value['ipid']]['PatientQpaMapping'] = array_unique($PatientQpaMapping_ipis_arr[$pat_value['ipid']]);
					$patient_details[$pat_value['ipid']]['PatientQpaLeading'] = array_unique($leading_users_array[$pat_value['ipid']]);
	

					//ISPC-2545 Lore 17.03.2020
					if($view_verordnung_type){
					    $patient_details[$pat_value['ipid']]['verordnung_type'] = $sapv_verordnung_arry[$pat_value['ipid']]['name'];
					    $patient_details[$pat_value['ipid']]['verordnung_type_icon'] = $sapv_verordnung_arry[$pat_value['ipid']]['image'];
					}
				}

			}

			//dd($patient_details);
			$customBoxOrder = [];
			$userBoxOrder = BoxOrder::fetchUserCol($this->logininfo->userid, array_merge($boxColsUsers, $boxColsPseudogroups));
			array_multisort( array_column($userBoxOrder, "boxorder"), SORT_ASC, $userBoxOrder );
			array_walk($userBoxOrder, function($item) use (&$customBoxOrder) {$customBoxOrder[$item['boxcol']][] = $item;});
			
			/*
			 * let's be sure a user is only showing in one of the sorted columns
			 */
			if ( ! empty($customBoxOrder[301]) ||  ! empty($customBoxOrder[302]) ||  ! empty($customBoxOrder[303])) 
			{
			    
    			$customBoxOrder[302] = array_filter($customBoxOrder[302], function ($i) use ($customBoxOrder) {
    			    $found = array_filter($customBoxOrder[301], function($j) use($i) {return $j['boxid'] == $i['boxid'];});
    			    return empty($found);    
    			});
    			$customBoxOrder[303] = array_filter($customBoxOrder[303], function ($i) use ($customBoxOrder) {
    			    $found1 = array_filter($customBoxOrder[301], function($j) use($i) {return $j['boxid'] == $i['boxid'];});
    			    $found2 = array_filter($customBoxOrder[302], function($j) use($i) {return $j['boxid'] == $i['boxid'];});
    			    return empty($found1) && empty($found2);    
    			});
    			
			    $customBoxOrder[301] = empty($customBoxOrder[301]) ? [] : $customBoxOrder[301];
			    $customBoxOrder[302] = empty($customBoxOrder[302]) ? [] : $customBoxOrder[302];
			    $customBoxOrder[303] = empty($customBoxOrder[303]) ? [] : $customBoxOrder[303];
			}
			
			/*
			 * let's be sure a pseudo-group is only showing in one of the sorted columns
			 */
			if ( ! empty($customBoxOrder[304]) ||  ! empty($customBoxOrder[305]) ||  ! empty($customBoxOrder[306])) 
			{
			    $customBoxOrder[305] = array_filter($customBoxOrder[305], function ($i) use ($customBoxOrder) {
			        $found = array_filter($customBoxOrder[304], function($j) use($i) {return $j['boxid'] == $i['boxid'];});
			        return empty($found);
			    });
			    $customBoxOrder[306] = array_filter($customBoxOrder[306], function ($i) use ($customBoxOrder) {
			        $found1 = array_filter($customBoxOrder[304], function($j) use($i) {return $j['boxid'] == $i['boxid'];});
			        $found2 = array_filter($customBoxOrder[305], function($j) use($i) {return $j['boxid'] == $i['boxid'];});
			        return empty($found1) && empty($found2);
			    });
			         
		        $customBoxOrder[304] = empty($customBoxOrder[304]) ? [] : $customBoxOrder[304];
		        $customBoxOrder[305] = empty($customBoxOrder[305]) ? [] : $customBoxOrder[305];
		        $customBoxOrder[306] = empty($customBoxOrder[306]) ? [] : $customBoxOrder[306];
			}
			
			
			$this->view->customBoxOrder = $customBoxOrder;
				
			/* ########################################################## */
			/* ##################  Check all duty users and see if they have day plan - if not- add them  ###################### */
			/* ########################################################## */
			/*
			 foreach($client_roster_user_ids as $roster_user_id)
			 {
				if(!in_array($roster_user_id, $userids2date))
				{
				$records_users_plan[] = array(
				"clientid" => $clientid,
				"userid" => $roster_user_id,
				"userid_type" => "user",
				"date" => $date
				);
				}
				}

				if(count($records_users_plan) > 0)
				{
				$collection = new Doctrine_Collection('DailyPlanningUsers');
				$collection->fromArray($records_users_plan);
				$collection->save();
				$this->_redirect(APP_BASE . "roster/dayplanningnew?date=" . strtotime($date));
				}
				*/
			/*
			 foreach($patient_details as $ipid=>$pat){
				if ($ipid != '1e1d501b10c1066b07b8b0d0b62664902153ce54'){
				//print_r($patient_details[$pat_value['ipid']]);
				unset($patient_details[$ipid]);
					
				}
				}*/

			$day_planning['plan_date'] = $date;
			$day_planning['users'] = $client_users;
			$day_planning['active_patients'] = $patient_details;
			$day_planning['all_client_users'] = $all_client_users;
			$day_planning['users_with_isinactive'] = $users_with_isinactive;
				
			//print_R($day_planning);die();
				
			/*
			 array_walk($day_planning['active_patients'], function(&$a, $b) {
				$a['id_encrypt_format'] = Pms_Uuid::encrypt($a['id']);
				});
			 */
			$cnt = 1;
			foreach ($day_planning['active_patients'] as &$val){
				$val['id_encrypt_format'] = Pms_Uuid::encrypt($val['id']);
				$val['id'] = $cnt++;
			}
				

			$this->view->day_planning = $day_planning;
				
			$this->view->pseudo_grups_visits = $pseudo_grups;
				
				
			$order = $timed = 0;
			$hidden_users = array();
			foreach($day_planning['users'] as $ku => $uvs)
			{
				//echo $uvs['active_today']['view_mode'] ."<hr>";
				if(!empty($uvs['active_today']))
				{
					$user_active_today[] = $ku;
					if ($uvs['active_today']['view_mode'] == 'order') {$order++;}
					else {$timed++;}
				}

				if ($tagesplanung_only_user_with_shifts == 1 && empty($uvs['planned_visits']) && !in_array($ku, $client_roster_user_ids)) {
					//create array with users without shifts today
					$hidden_users[$ku] = $client_users[$ku] ['user_title'] ." ".$client_users[$ku] ['last_name'].", ".$client_users[$ku] ['first_name'];
						
				}


			}
			//die();
			$master_viewmode = "timed";
			if ( $order > $timed ) $master_viewmode = "order";
			$this->view->master_viewmode = $master_viewmode;
			$this->view->hidden_users = $hidden_users;

				

			/* ########################### ADD USERS TO PLAN */
			/*
			 if(1==2 && $this->getRequest()->isPost())
			 {
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
				}

				if($_POST['save_users_to_plan'] == "1")
				{

				$users_with_plan_this_day = array();
				foreach($_POST['visit_order'] as $userid => $hours){

				$hours_arr = $hours;
				reset($hours_arr);
				$first = current($hours_arr);
				if ($first[0]['data_usertype'] !='user' && $first[0]['data_usertype'] !='pseudogrups'){
				$first[0]['data_usertype'] ='user';
				}

				$users_with_plan_this_day[$first[0]['data_usertype']][$userid] = array('userid'=>$userid , 'userid_type'=>$first[0]['data_usertype']);
				}
					
					
				$DailyPlanningUsers_today = DailyPlanningUsers :: get_users_by_date_interval(
				$clientid ,
				array("start"=>date("Y-m-d 00:00:00", strtotime($date)), "end"=>date("Y-m-d 23:59:59", strtotime($date))) ,
				false);
					
				$clean_data_users= array();
				foreach($DailyPlanningUsers_today as $k=>$plan){

				if(empty($users_with_plan_this_day [ $plan['userid_type'] ] [ $plan['userid'] ])){
				//clean this id in DailyPlanningUsers
				$clean_data_users[] =  $plan['id'];
				unset($DailyPlanningUsers_today[$k]);
				}else{
				$users_with_plan_this_day [ $plan['userid_type'] ] [ $plan['userid'] ]['id'] =  $plan['id'];
				//verify if viewmode is the same
				//if $plan['view_mode'] != $_POST['master_viewmode']
				}
					
				}
					
				if(!empty($clean_data_users))
				{
				$dutyuset = Doctrine_Query::create()
				->update('DailyPlanningUsers')
				->set('isdelete', "1")
				->where(" clientid= ?",  $clientid )
				->andWhereIn('id', $clean_data_users)
				->execute();
				}

				$insert_DailyPlanningUsers = array();
				foreach( $users_with_plan_this_day as $type=>$userid_arr ){
				foreach ($userid_arr as $k=>$v){
				if (!empty($v['id'])){
				//update
				//this are old users that allready had plan this day, just update viewmode
				$update = Doctrine::getTable('DailyPlanningUsers')->find($v['id']);
				$update->view_mode = $_POST['master_viewmode'];
				$update->save();

				}else{
				//insert new
				$insert_DailyPlanningUsers[] = array(
				"userid_type" => $type,
				"clientid" => $clientid,
				"userid" => $v['userid'],
				"date" => $date,
				"view_mode" =>	$_POST['master_viewmode']
				);
				}
				}
				}

					
				//this are new users that now have plans
				if (!empty($insert_DailyPlanningUsers))
				{
				$collection = new Doctrine_Collection('DailyPlanningUsers');
				$collection->fromArray($insert_DailyPlanningUsers);
				$collection->save();
				}

					
				$this->_redirect(APP_BASE . "roster/dayplanningnew?date=" . strtotime($date));
				}

				}*/
				
			if ( ! $this->getRequest()->isPost() )
			{
				/*
				 * ispc-1533 added ELSE
				 * condition this is NOT a POST request
				 */
					
				//get all doctors and groups - this function is an overhead, since we allready have the array will all the info
				$visiting_users = User::get_all_visiting_users_and_groups( $clientid, false, true, array('pseudogroups_with_visits'=> true) );
				foreach($visiting_users['grups'] as $kk=>$group ){
					foreach($group as $k=>$v){
						$visiting_users['alldoctors'][$k] = $v;
					}
				}
				$this->view->pat_visits_settings_visiting_users = $visiting_users;

				//ispc-1855
				//pseudogroup -> shift associates-> users with this shift
				$users_in_pseudogroup = array();
				foreach ($client_shifts as $ck => $shifts){
					if ( $shifts['istours'] !='0'){
						foreach ($rostarray as $rk => $rost ){
							if ($rost['shift'] == $shifts['id']){
							    if(!in_array($visiting_users['alldoctors'][$rost['userid']], $users_in_pseudogroup[$shifts['istours']])){
        							$users_in_pseudogroup[$shifts['istours']][] = $visiting_users['alldoctors'][$rost['userid']];
							    }
							}
						}
					}
				}
				$this->view->users_in_pseudogroup = $users_in_pseudogroup;

				//get all custom team events for this day
				$TeamCustomEvents = array();
				$getTeamCustomEvents_on_day = TeamCustomEvents :: getTeamCustomEvents_on_day($clientid, $date);
				foreach ($getTeamCustomEvents_on_day as $k=>$event){
					if ( $event['allDay']  == "1" ){
						$event['start_hour'] = "0";
						$event['end_hour'] = "23";
					}else{
						$event['start_hour'] = date('G', strtotime( $event['startDate'] ));
						$event['end_hour'] = date('G', strtotime( $event['endDate'] ));
					}
					$TeamCustomEvents[] = $event;
				}
				$this->view->getTeamCustomEvents_on_day = json_encode( $TeamCustomEvents );

				//get all personal events of any user
				$DoctorCustomEvents = array();
				$get_all_doc_events_from_date = DoctorCustomEvents :: get_all_doc_events_from_date($clientid , $date);
				foreach($get_all_doc_events_from_date as $k=>$event){
					$logininfo->userid;
					if ($event['viewForAll'] == "0" && $logininfo->userid != $event['userid']){
						//hide the event title
						$event['eventTitle'] =  $this->view->translate("Meeting in personal calendar");
					}
					if ( $event['allDay']  == "1" ){
						$event['start_hour'] = "0";
						$event['end_hour'] = "23";
					}else{
						$event['start_hour'] = date('G', strtotime( $event['startDate'] ));
						$event['end_hour'] = date('G', strtotime( $event['endDate'] ));
					}
					$DoctorCustomEvents[] = $event;
				}
				$this->view->getDoctorCustomEvents_on_day = json_encode( $DoctorCustomEvents );

			}
		}

		public function dayplanningnew2Action()
		{

			setlocale(LC_ALL, 'de_DE.UTF8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$previleges = new Pms_Acl_Assertion();
			$hidemagic = Zend_Registry::get('hidemagic');
			$return = $previleges->checkPrevilege('Roster', $logininfo->userid, 'canview');
				
			$clientid = $logininfo->clientid;
			$patient_master = new PatientMaster();

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}

				if($_POST['save_visit'] == '1' || $_POST['save_users_to_plan'] == '1' || $_POST['remove_visit_action'] == '1' || $_POST['edit_visit_action'] == '1')
				{
					$has_edit_permissions = Links::checkLinkActionsPermission();
					if(!$has_edit_permissions) // if canedit = 0 - don't allow any changes
					{
						$this->_redirect(APP_BASE . "error/previlege");
						exit;
					}
				}
			}

			//get all client shifts
			$c_shifts = new ClientShifts();
			$client_shifts = $c_shifts->get_client_shifts($clientid);
			/* ###################### DEFAULTS - Current day - today ############################### */
			if($_REQUEST['date'])
			{
				if($_REQUEST['sel'] == 1)
				{
					$date = date('Y-m-d 00:00:00', strtotime($_REQUEST['date']));
				}
				else
				{
					$date = date('Y-m-d 00:00:00', $_REQUEST['date']);
				}
			}
			else
			{
				$date = date('Y-m-d 00:00:00', time());
			}

			$today = date('Y-m-d 00:00:00', time());

			$month_start = date('Y-m', strtotime($date))."-01";

			if(!function_exists('cal_days_in_month'))
			{
				$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($month_start)), 1, date("Y", strtotime($month_start))));
			}
			else
			{
				$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($month_start)), date("Y", strtotime($month_start)));
			}

			$month_end = date('Y-m', strtotime($month_start)).'-'.$month_days;

			$selected_date_month_days = $patient_master->getDaysInBetween($month_start, $month_end);

			$this->view->current_month_days = $selected_date_month_days;
				
				
			$previous_date = date('Y-m-d 00:00:00', mktime(date('H', strtotime($date)), date('i', strtotime($date)), 0, date('m', strtotime($date)), date('d', strtotime($date)) - 1, date('Y', strtotime($date))));
			$next_date = date('Y-m-d 00:00:00', mktime(date('H', strtotime($date)), date('i', strtotime($date)), 0, date('m', strtotime($date)), date('d', strtotime($date)) + 1, date('Y', strtotime($date))));

			/* ###################### DEFAULTS - Current day - today ############################### */
			$day_planning['previous_date'] = date('d.m.Y', strtotime($previous_date));
			$day_planning['current_date'] = date('d.m.Y', strtotime($date));
			$day_planning['next_date'] = date('d.m.Y', strtotime($next_date));

			if($this->getRequest()->isPost())
			{
				if($_POST['date_action'] == 1 && !empty($_POST['date']))
				{
					$this->_redirect(APP_BASE . "roster/dayplanningnew2?date=" . strtotime($_POST['date']));
				}
			}
				
			/* ###################### POST - SAVE/EDIT/DELETE visits ############################### */
			if($this->getRequest()->isPost())
			{

				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}

				if(empty($_POST['pdf_print_action']))
				{
					$save_visit_form = new Application_Form_DailyPlanningVisits2();  //++++

					/* ########################### SAVE VISIT ######################################### */
					$a_post = $_POST;
					$a_post['current_date'] = $date;
					$a_post['clientid'] = $clientid;

					$save_action = $save_visit_form->save_multiple_visits($a_post); //++
					//				$this->_redirect(APP_BASE . "roster/dayplanningnew?date=" . strtotime($date));
				}
			}
				
			$user_planning = new DailyPlanningUsers();
			$userids2date = $user_planning->get_users_by_date($clientid, $date, false, true, $allowed_deleted = 1);
			$users_details2date = $user_planning->get_users_by_date($clientid, $date, false);

			/* ###################### Users Groups- get groups  that are allowed to shou in dienstplan ############################### */
			$docquery = Doctrine_Query::create()
			->select('*')
			->from('Usergroup')
			->where('clientid="' . $clientid . '"')
			->andWhere('isdelete="0"')
			->andWhere('isactive=1');
			$groups = $docquery->fetchArray();

			$groups_ids[] = '9999999999';
			foreach($groups as $k_gr => $v_gr)
			{
				$groups_ids[] = $v_gr['id'];
			}

			/* ###################### Users - get user details ############################### */
			$doc = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('isactive=0')
			->andWhere('isdelete=0')
			->andWhere('usertype!="SA"')
			->andWhere('clientid=' . $clientid)
			->andWhereIn('groupid', $groups_ids)
			->orderBy('last_name ASC');
			$docarray = $doc->fetchArray();

			foreach($docarray as $d_key => $d_val)
			{
				$client_users_ids[] = $d_val['id'];
				$client_users[$d_val['id']]['id'] = $d_val['id'];
				$client_users[$d_val['id']]['name'] = $d_val['last_name'] . ", " . $d_val['first_name'];
				$client_users[$d_val['id']]['phone'] = $d_val['phone'];
				$client_users[$d_val['id']]['mobile'] = $d_val['mobile'];
				$client_users[$d_val['id']]['user_title'] = $d_val['user_title'];
				$client_users[$d_val['id']]['last_name'] = $d_val['last_name'];
				$client_users[$d_val['id']]['first_name'] = $d_val['first_name'];

				if(!empty($d_val['usercolor']))
				{
					$client_users[$d_val['id']]['usercolor'] = $d_val['usercolor'];
				}
				else
				{
					$client_users[$d_val['id']]['usercolor'] = "cccddd";
				}

				if(!empty($d_val['shortname']))
				{
					$client_users[$d_val['id']]['shortname'] = $d_val['shortname'];
				}
				else
				{
					$client_users[$d_val['id']]['shortname'] = substr($d_val['last_name'], 0, 1) . substr($d_val['first_name'], 0, 1);
				}
			}

			if(empty($client_users_ids))
			{
				$client_users_ids[] = "999999";
			}
				
			/* ###################### users - duty roster for today ############################### */
			$docid = Doctrine_Query::create()
			->select('*')
			->from('Roster')
			->where('clientid = ' . $clientid)
			->andWhereIn('userid', $client_users_ids)
			->andWhere("DATE(duty_date) = DATE('" . $date . "') ")
			->andWhere('isdelete = "0"');
			$rostarray = $docid->fetchArray();

			$user_shifts = array();
			foreach($rostarray as $r_key => $r_value)
			{
				$client_roster_user_ids[] = $r_value['userid'];
				$user_roster[$r_value['userid']] = $r_value;

				//create user shifts array
				if(!array_key_exists($r_value['userid'], $user_shifts))
				{
					$user_shifts[$r_value['userid']] = $r_value['shift'];
				}
			}

			foreach($client_users_ids as $k_uid => $v_uid)
			{
				if(!array_key_exists($v_uid, $user_shifts))
				{
					$users_shift_start[$v_uid]['time'] = strtotime(date('Y-m-d', time())." 08:00:00");

				}
				else
				{
					$users_shift_start[$v_uid]['time'] = strtotime(date('Y-m-d', time())." ".date('H:i:s', strtotime($client_shifts[$user_shifts[$v_uid]]['start'])));
				}
			}
			$this->view->users_json_shifts = json_encode($users_shift_start);

			foreach($client_users as $k_userid => $u_values)
			{
				$client_users[$u_values['id']]['roster'] = $user_roster[$u_values['id']];
				$client_users[$u_values['id']]['active_today'] = $users_details2date[$u_values['id']];
			}

			/* ###################### Client settings ############################### */
			$client_data = Pms_CommonData::getClientData($clientid);
			$tagesplanung_standby_patients = $client_data['0']['tagesplanung_standby_patients']; // ISPC-1170 client setting show patients standby in tagesplanung

			if($tagesplanung_standby_patients == '0') // only active patients
			{

					
				/* ###################### Active patients ############################### */

				if(strtotime($date) >= strtotime($today))
				{

					$active_patients = $this->get_now_active_patients();
				}
				else
				{
					$sql = "p.ipid,e.ipid,a.ipid";
					$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
					$sql .= ",AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
					$sql .= ",convert(AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') using latin1) as zip";
					$sql .= ",convert(AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') using latin1) as street1";
					$sql .= ",convert(AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') using latin1) as city";
					$sql .= ",convert(AES_DECRYPT(p.phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone";
					$sql .= ",convert(AES_DECRYPT(p.mobile,'" . Zend_Registry::get('salt') . "') using latin1) as mobile";
					$sql .= ",convert(AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') using latin1) as sex";

					if($logininfo->usertype == 'SA')
					{
						$sql = "p.ipid,e.ipid,a.ipid,";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
					}

					$date_filter = array('0' => array('start' => $date, 'end' => $date));
					 
					$active_patients_ipids = Pms_CommonData::patients_active($sql, $clientid, $date_filter, false, "convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)", "ASC"); // BRE patient list

					foreach($active_patients_ipids as $k => $p_values)
					{
						$active_patients['ipids'][] = $p_values['ipid'];
						$active_patients['details'][] = $p_values['PatientMaster'];
					}
						
				}



				/* ###################### users - get visits for today ############################### */

				$patient_plan_visits = new DailyPlanningVisits2();
				$patient_plan_visits_array = $patient_plan_visits->get_patients_visits($clientid, $date);

				foreach($patient_plan_visits_array as $key => $pvvalues)
				{
					$client_users[$pvvalues['userid']]['planned_visits'][] = $pvvalues;

					if(in_array($pvvalues['ipid'], $active_patients['ipids']))
					{
						$patient_visits[$pvvalues['ipid']]['planned_visits'][] = $pvvalues;
						$patient_visits[$pvvalues['ipid']]['existing_visits'] = count($patient_visits[$pvvalues['ipid']]['planned_visits']);
					}
				}


				/* ###################### Patients details ############################### */
				if(empty($active_patients['ipids']))
				{
					$active_patients['ipids'][] = "999999";
				}
				 
				$pl = new PatientVisitsSettings();
				$pat_visits_settings = $pl->getPatientVisitsSettings($active_patients['ipids']);


					
				//active location for all patients START
				$ipids_with_visits[0]['ipid'] = '999999999999';
				$incr = 1;
				foreach($patient_visits as $kipid => $v_p_visits)
				{
					$ipids_with_visits[$incr]['ipid'] = $kipid;
					$incr++;
				}
				$patients_active_location = PatientLocation::getActiveLocations($ipids_with_visits);



				$all_st_ipids[0]['ipid'] = '999999999999';
				$incrs = 1;
				foreach($active_patients['ipids'] as   $sipid)
				{
					$all_st_ipids[$incrs]['ipid'] = $sipid;
					$incrs++;
				}
				$patients_active_location_all = PatientLocation::getActiveLocations($all_st_ipids);


				$locations_ids[] = '999999999';
				$location_cp_ipids[] = '99999999999';
				foreach($patients_active_location as $kpat=>$v_pat_loc)
				{
					$patient2location[$v_pat_loc['ipid']] = $v_pat_loc['location_id'];
					$locid = substr($v_pat_loc['location_id'], 0, 4);

					if($locid == '8888')
					{
						$location_cp_ipids[] = $v_pat_loc['ipid'];
					}
					else
					{
						$locations_ids[] = $v_pat_loc['location_id'];
					}
				}


				$locations_ids_all[] = '999999999';
				$location_cp_ipids_all[] = '99999999999';

				foreach($patients_active_location_all as $kpat=>$v_pat_loc)
				{
					$patient2location_all[$v_pat_loc['ipid']] = $v_pat_loc['location_id'];
					$locid = substr($v_pat_loc['location_id'], 0, 4);

					if($locid == '8888')
					{
						$location_cp_ipids_all[] = $v_pat_loc['ipid'];
					}
					else
					{
						$locations_ids_all[] = $v_pat_loc['location_id'];
					}
				}


				if($location_cp_ipids)
				{
					$contact_persons = ContactPersonMaster::get_contact_persons_by_ipids($location_cp_ipids, false, false);

					foreach($contact_persons as $k_ipid => $v_contact_persons)
					{
						$z[$k_ipid] = 1;
						$cnt_number[$k_ipid] = 1; // display contact number
						foreach($v_contact_persons as $value)
						{
							if($value['isdelete'] == '0')
							{
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson ' . $cnt_number[$k_ipid] . '(' . $value['cnt_last_name'] . ' ' . $value['cnt_first_name'] . ')';
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['street'] = $value['cnt_street1'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['zip'] = $value['cnt_zip'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['city'] = $value['cnt_city'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['phone'] = $value['cnt_phone'];
								$cnt_number[$k_ipid]++;
							}
							else
							{
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson';
							}
							$z[$k_ipid]++;
						}
					}
				}

				if($location_cp_ipids_all)
				{
					$contact_persons_all = ContactPersonMaster::get_contact_persons_by_ipids($location_cp_ipids_all, false, false);

					foreach($contact_persons_all as $k_ipid => $v_contact_persons)
					{
						$z[$k_ipid] = 1;
						$cnt_number[$k_ipid] = 1; // display contact number
						foreach($v_contact_persons as $value)
						{
							if($value['isdelete'] == '0')
							{
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson ' . $cnt_number[$k_ipid] . '(' . $value['cnt_last_name'] . ' ' . $value['cnt_first_name'] . ')';
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['street'] = $value['cnt_street1'];
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['zip'] = $value['cnt_zip'];
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['city'] = $value['cnt_city'];
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['phone'] = $value['cnt_phone'];
								$cnt_number[$k_ipid]++;
							}
							else
							{
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson';
							}
							$z[$k_ipid]++;
						}
					}
				}
					
				$locmaster = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				//->whereIn('id', $locations_ids)
				->where("client_id = ".$logininfo->clientid)
				->orderBy('id ASC');
				$locations_master_res = $locmaster->fetchArray();
					
				foreach($locations_master_res as $k_lmaster => $v_lmaster)
				{
					$master_locations[$v_lmaster['id']]['id'] = $v_lmaster['id'];
					$master_locations[$v_lmaster['id']]['location'] = $v_lmaster['location'];
					$master_locations[$v_lmaster['id']]['location_type'] = $v_lmaster['location_type'];
					$master_locations[$v_lmaster['id']]['street'] = $v_lmaster['street'];
					$master_locations[$v_lmaster['id']]['zip'] = $v_lmaster['zip'];
					$master_locations[$v_lmaster['id']]['city'] = $v_lmaster['city'];
					$master_locations[$v_lmaster['id']]['phone'] = $v_lmaster['phone1'];
						
					$master_locations_all[$v_lmaster['id']]['id'] = $v_lmaster['id'];
					$master_locations_all[$v_lmaster['id']]['location'] = $v_lmaster['location'];
					$master_locations_all[$v_lmaster['id']]['location_type'] = $v_lmaster['location_type'];
					$master_locations_all[$v_lmaster['id']]['street'] = $v_lmaster['street'];
					$master_locations_all[$v_lmaster['id']]['zip'] = $v_lmaster['zip'];
					$master_locations_all[$v_lmaster['id']]['city'] = $v_lmaster['city'];
					$master_locations_all[$v_lmaster['id']]['phone'] = $v_lmaster['phone1'];
						
						
				}

				foreach($patient2location as $k_res_pat => $v_res_pat)
				{
					if(!empty($master_locations[$v_res_pat]))
					{
						$patient_active_locations[$k_res_pat] = $master_locations[$v_res_pat];
					}
					else
					{
						$patient_active_locations[$k_res_pat] = $master_locations[$k_res_pat][$v_res_pat];
					}
				}
				foreach($patient2location_all as $k_res_pat => $v_res_pat)
				{
					if(!empty($master_locations_all[$v_res_pat]))
					{
						$patient_active_locations_all[$k_res_pat] = $master_locations_all[$v_res_pat];
					}
					else
					{
						$patient_active_locations_all[$k_res_pat] = $master_locations_all[$k_res_pat][$v_res_pat];
					}
				}

					
				//active location for all patients END

				foreach($pat_visits_settings as $vskey => $vsvalues)
				{
					$visits_settings[$vsvalues['ipid']] = $vsvalues;
				}
				$patient_details = array();

				foreach($active_patients['details'] as $pat_key => $pat_value)
				{
					$patient_details[$pat_value['ipid']]['id'] = $pat_value['id'];
					$patient_details[$pat_value['ipid']]['patient_name'] = $pat_value['last_name'] . ' ' . $pat_value['first_name'];
					$patient_details[$pat_value['ipid']]['last_name'] = $pat_value['last_name'];
					$patient_details[$pat_value['ipid']]['first_name'] = $pat_value['first_name'];
					$patient_details[$pat_value['ipid']]['street'] = $pat_value['street1'];
					$patient_details[$pat_value['ipid']]['zip'] = $pat_value['zip'];
					$patient_details[$pat_value['ipid']]['city'] = $pat_value['city'];
					if(!empty($pat_value['phone']))
					{
						$patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
					}
					elseif (!empty($pat_value['mobile']) && empty($pat_value['phone']))
					{
						$patient_details[$pat_value['ipid']]['phone'] = $pat_value['mobile'];
					}
					else
					{
						$patient_details[$pat_value['ipid']]['phone'] = '';
					}
					/* $patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
						$patient_details[$pat_value['ipid']]['mobile'] = $pat_value['mobile']; */
					if(!empty($patient_active_locations[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['actual_location'] = $patient_active_locations[$pat_value['ipid']];
					}
						
					if(!empty($patient_active_locations_all[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['actual_location_all'] = $patient_active_locations_all[$pat_value['ipid']];
					}
					// all patient visits planned for today
					$patient_details[$pat_value['ipid']]['planned_visits'] = $patient_visits[$pat_value['ipid']]['planned_visits'];

					if(!empty($visits_settings[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['visits_per_day'] = $visits_settings[$pat_value['ipid']]['visits_per_day'];
						$patient_details[$pat_value['ipid']]['visit_duration'] = $visits_settings[$pat_value['ipid']]['visit_duration'];
					}
					else
					{
						$patient_details[$pat_value['ipid']]['visits_per_day'] = '1';
						$patient_details[$pat_value['ipid']]['visit_duration'] = "60";
					}

					// patient - remaining visits per day
					$patient_details[$pat_value['ipid']]['visits_remaining_per_day'] = $patient_details[$pat_value['ipid']]['visits_per_day'] - $patient_visits[$pat_value['ipid']]['existing_visits'];
				}

				if($_REQUEST['dbga'] == 1)
				{
					print_R("\n number of active patients \n");
					print_R(count($patient_details));
					print_R("\n active patients \n");
					print_R($patient_details);
					exit;
				}



			}
			else
			{
				/* ###################### Active + Standby patients ############################### */

				if(strtotime($date) >= strtotime($today))
				{

					$active_standby_patients = $this->get_now_active_patients(true);
						
				}
				else
				{
					$sql = "p.ipid,e.ipid,a.ipid";
					$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
					$sql .= ",AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
					$sql .= ",convert(AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') using latin1) as zip";
					$sql .= ",convert(AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') using latin1) as street1";
					$sql .= ",convert(AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') using latin1) as city";
					$sql .= ",convert(AES_DECRYPT(p.phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone";
					$sql .= ",convert(AES_DECRYPT(p.mobile,'" . Zend_Registry::get('salt') . "') using latin1) as mobile";
					$sql .= ",convert(AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') using latin1) as sex";

					if($logininfo->usertype == 'SA')
					{
						$sql = "p.ipid,e.ipid,a.ipid,";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
					}

					$date_filter = array('0' => array('start' => $date, 'end' => $date));

					$activstandby_patients_ipids = Pms_CommonData::patients_active($sql, $clientid, $date_filter, false, "convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)", "ASC",false,false,'0',true); // BRE patient list
					foreach($activstandby_patients_ipids as $k_a => $p_values)
					{
						$active_standby_patients['ipids'][] = $p_values['ipid'];
						$active_standby_patients['details'][] = $p_values['PatientMaster'];
					}

				}



				/* ###################### users - get visits for today ############################### */
				$patient_plan_visits = new DailyPlanningVisits2();
				$patient_plan_visits_array = $patient_plan_visits->get_patients_visits($clientid, $date);

				foreach($patient_plan_visits_array as $key => $pvvalues)
				{
					$client_users[$pvvalues['userid']]['planned_visits'][] = $pvvalues;

					if(in_array($pvvalues['ipid'], $active_standby_patients['ipids']))
					{
						$patient_visits[$pvvalues['ipid']]['planned_visits'][] = $pvvalues;
						$patient_visits[$pvvalues['ipid']]['existing_visits'] = count($patient_visits[$pvvalues['ipid']]['planned_visits']);
					}
						
				}

				/* ###################### Patients details ############################### */
				if(empty($active_standby_patients['ipids']))
				{
					$active_standby_patients['ipids'][] = "999999";
				}

				$pl = new PatientVisitsSettings();
				$pat_visits_settings = $pl->getPatientVisitsSettings($active_standby_patients['ipids']);
					
				//active location for all patients START
				$ipids_with_visits[0]['ipid'] = '999999999999';
				$incr = 1;
				foreach($patient_visits as $kipid => $v_p_visits)
				{
					$ipids_with_visits[$incr]['ipid'] = $kipid;
					$incr++;
				}

				$patients_active_location = PatientLocation::getActiveLocations($ipids_with_visits);

				//3333333333333333333333333333
				// for all
				$all_st_ipids[0]['ipid'] = '999999999999';
				$incrs = 1;
				foreach($active_standby_patients['ipids'] as   $sipid)
				{
					$all_st_ipids[$incrs]['ipid'] = $sipid;
					$incrs++;
				}

				$patients_active_location_all = PatientLocation::getActiveLocations($all_st_ipids);
				//3333333333333333333333333333


				//get all patients locations$locations_ids
				$locations_ids[] = '999999999';
				$location_cp_ipids[] = '99999999999';

				foreach($patients_active_location as $kpat=>$v_pat_loc)
				{
					$patient2location[$v_pat_loc['ipid']] = $v_pat_loc['location_id'];
					$locid = substr($v_pat_loc['location_id'], 0, 4);

					if($locid == '8888')
					{
						$location_cp_ipids[] = $v_pat_loc['ipid'];
					}
					else
					{
						$locations_ids[] = $v_pat_loc['location_id'];
					}
				}


				$locations_ids_all[] = '999999999';
				$location_cp_ipids_all[] = '99999999999';

				foreach($patients_active_location_all as $kpat=>$v_pat_loc)
				{
					$patient2location_all[$v_pat_loc['ipid']] = $v_pat_loc['location_id'];
					$locid = substr($v_pat_loc['location_id'], 0, 4);

					if($locid == '8888')
					{
						$location_cp_ipids_all[] = $v_pat_loc['ipid'];
					}
					else
					{
						$locations_ids_all[] = $v_pat_loc['location_id'];
					}
				}



				if($location_cp_ipids)
				{
					$contact_persons = ContactPersonMaster::get_contact_persons_by_ipids($location_cp_ipids, false, false);

					foreach($contact_persons as $k_ipid => $v_contact_persons)
					{
						$z[$k_ipid] = 1;
						$cnt_number[$k_ipid] = 1; // display contact number
						foreach($v_contact_persons as $value)
						{
							if($value['isdelete'] == '0')
							{
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson ' . $cnt_number[$k_ipid] . '(' . $value['cnt_last_name'] . ' ' . $value['cnt_first_name'] . ')';
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['street'] = $value['cnt_street1'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['zip'] = $value['cnt_zip'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['city'] = $value['cnt_city'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['phone'] = $value['cnt_phone'];
								$cnt_number[$k_ipid]++;
							}
							else
							{
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson';
							}
							$z[$k_ipid]++;
						}
					}
				}


				if($location_cp_ipids_all)
				{
					$contact_persons_all = ContactPersonMaster::get_contact_persons_by_ipids($location_cp_ipids_all, false, false);

					foreach($contact_persons_all as $k_ipid => $v_contact_persons)
					{
						$z[$k_ipid] = 1;
						$cnt_number[$k_ipid] = 1; // display contact number
						foreach($v_contact_persons as $value)
						{
							if($value['isdelete'] == '0')
							{
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson ' . $cnt_number[$k_ipid] . '(' . $value['cnt_last_name'] . ' ' . $value['cnt_first_name'] . ')';
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['street'] = $value['cnt_street1'];
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['zip'] = $value['cnt_zip'];
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['city'] = $value['cnt_city'];
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['phone'] = $value['cnt_phone'];
								$cnt_number[$k_ipid]++;
							}
							else
							{
								$master_locations_all[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson';
							}
							$z[$k_ipid]++;
						}
					}
				}
					
				$locmaster = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				//->whereIn('id', $locations_ids)
				->where("client_id = ".$logininfo->clientid)
				->orderBy('id ASC');
				$locations_master_res = $locmaster->fetchArray();
					
				foreach($locations_master_res as $k_lmaster => $v_lmaster)
				{
					$master_locations[$v_lmaster['id']]['id'] = $v_lmaster['id'];
					$master_locations[$v_lmaster['id']]['location'] = $v_lmaster['location'];
					$master_locations[$v_lmaster['id']]['location_type'] = $v_lmaster['location_type'];
					$master_locations[$v_lmaster['id']]['street'] = $v_lmaster['street'];
					$master_locations[$v_lmaster['id']]['zip'] = $v_lmaster['zip'];
					$master_locations[$v_lmaster['id']]['city'] = $v_lmaster['city'];
					$master_locations[$v_lmaster['id']]['phone'] = $v_lmaster['phone1'];

					$master_locations_all[$v_lmaster['id']]['id'] = $v_lmaster['id'];
					$master_locations_all[$v_lmaster['id']]['location'] = $v_lmaster['location'];
					$master_locations_all[$v_lmaster['id']]['location_type'] = $v_lmaster['location_type'];
					$master_locations_all[$v_lmaster['id']]['street'] = $v_lmaster['street'];
					$master_locations_all[$v_lmaster['id']]['zip'] = $v_lmaster['zip'];
					$master_locations_all[$v_lmaster['id']]['city'] = $v_lmaster['city'];
					$master_locations_all[$v_lmaster['id']]['phone'] = $v_lmaster['phone1'];
				}

				foreach($patient2location as $k_res_pat => $v_res_pat)
				{
					if(!empty($master_locations[$v_res_pat]))
					{
						$patient_active_locations[$k_res_pat] = $master_locations[$v_res_pat];
					}
					else
					{
						$patient_active_locations[$k_res_pat] = $master_locations[$k_res_pat][$v_res_pat];
					}
				}
				foreach($patient2location_all as $k_res_pat => $v_res_pat)
				{
					if(!empty($master_locations_all[$v_res_pat]))
					{
						$patient_active_locations_all[$k_res_pat] = $master_locations_all[$v_res_pat];
					}
					else
					{
						$patient_active_locations_all[$k_res_pat] = $master_locations_all[$k_res_pat][$v_res_pat];
					}
				}
				//active location for all patients END



				foreach($pat_visits_settings as $vskey => $vsvalues)
				{
					$visits_settings[$vsvalues['ipid']] = $vsvalues;
				}
				$patient_details = array();

				foreach($active_standby_patients['details'] as $pat_key => $pat_value)
				{
					$patient_details[$pat_value['ipid']]['id'] = $pat_value['id'];
					$patient_details[$pat_value['ipid']]['patient_name'] = $pat_value['last_name'] . ' ' . $pat_value['first_name'];
					$patient_details[$pat_value['ipid']]['last_name'] = $pat_value['last_name'];
					$patient_details[$pat_value['ipid']]['first_name'] = $pat_value['first_name'];
					$patient_details[$pat_value['ipid']]['street'] = $pat_value['street1'];
					$patient_details[$pat_value['ipid']]['zip'] = $pat_value['zip'];
					$patient_details[$pat_value['ipid']]['city'] = $pat_value['city'];
					if(!empty($pat_value['phone']))
					{
						$patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
					}
					elseif (!empty($pat_value['mobile']) && empty($pat_value['phone']))
					{
						$patient_details[$pat_value['ipid']]['phone'] = $pat_value['mobile'];
					}
					else
					{
						$patient_details[$pat_value['ipid']]['phone'] = '';
					}
					/* $patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
					 $patient_details[$pat_value['ipid']]['mobile'] = $pat_value['mobile']; */
					if(!empty($patient_active_locations[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['actual_location'] = $patient_active_locations[$pat_value['ipid']];
					}

					if(!empty($patient_active_locations_all[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['actual_location_all'] = $patient_active_locations_all[$pat_value['ipid']];
					}

					// all patient visits planned for today
					$patient_details[$pat_value['ipid']]['planned_visits'] = $patient_visits[$pat_value['ipid']]['planned_visits'];

					if(!empty($visits_settings[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['visits_per_day'] = $visits_settings[$pat_value['ipid']]['visits_per_day'];
						$patient_details[$pat_value['ipid']]['visit_duration'] = $visits_settings[$pat_value['ipid']]['visit_duration'];
					}
					else
					{
						$patient_details[$pat_value['ipid']]['visits_per_day'] = '1';
						$patient_details[$pat_value['ipid']]['visit_duration'] = "60";
					}

					// patient - remaining visits per day
					$patient_details[$pat_value['ipid']]['visits_remaining_per_day'] = $patient_details[$pat_value['ipid']]['visits_per_day'] - $patient_visits[$pat_value['ipid']]['existing_visits'];
				}

				if($_REQUEST['dbg'] == 1)
				{
					print_R("\n number of active  patients \n");
					print_R($active_patients['ipids_active']);
					print_R("\n number of standby patients \n");
					print_R($standby_patients['ipids_standby']);
					print_R("\n active standby patients \n");

					print_R($patient_details);
					exit;
				}

			}
				

			/* ########################################################## */
			/* ##################  Check all duty users and see if they have day plan - if not- add them  ###################### */
			/* ########################################################## */
			foreach($client_roster_user_ids as $roster_user_id)
			{
				if(!in_array($roster_user_id, $userids2date))
				{
					$records_users_plan[] = array(
							"clientid" => $clientid,
							"userid" => $roster_user_id,
							"date" => $date
					);
				}
			}

			if(count($records_users_plan) > 0)
			{
				$collection = new Doctrine_Collection('DailyPlanningUsers');
				$collection->fromArray($records_users_plan);
				$collection->save();
				$this->_redirect(APP_BASE . "roster/dayplanningnew2?date=" . strtotime($date));
			}

			$day_planning['plan_date'] = $date;
			$day_planning['users'] = $client_users;
			$day_planning['active_patients'] = $patient_details;

			foreach($day_planning['users'] as $ku => $uvs)
			{
				if(!empty($uvs['active_today']))
				{
					$user_active_today[] = $ku;
				}
			}
			$this->view->day_planning = $day_planning;
				
				
				
			/* ########################### ADD USERS TO PLAN & PDF ######################################### */
			if($this->getRequest()->isPost())
			{

				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}

				if($_POST['save_users_to_plan'] == "1")
				{
						
					/* ########################### ADD USERS TO PLAN ######################################### */
					if(empty($_POST['day_planning']['users']))
					{
						$_POST['day_planning']['users'][] = "999999";
					}

					foreach($user_active_today as $k => $user_ida)
					{
						if(!in_array($user_ida, $_POST['day_planning']['users']))
						{
							$clean_data_users[] = $user_ida;
						}
					}

					foreach($_POST['day_planning']['users'] as $k => $post_user)
					{
						if(!in_array($post_user, $clean_data_users) && !in_array($post_user, $user_active_today) && (in_array($post_user, $client_users_ids)))
						{
							$records_users_to_plan[] = array(
									"clientid" => $clientid,
									"userid" => $post_user,
									"date" => $date
							);
						}
					}

					if(!empty($clean_data_users))
					{

						$clean = $this->clean_today_plans2($clientid, $date, $clean_data_users);
					}

					if(count($records_users_to_plan) > 0)
					{
						$collection = new Doctrine_Collection('DailyPlanningUsers');
						$collection->fromArray($records_users_to_plan);
						$collection->save();
					}
					$this->_redirect(APP_BASE . "roster/dayplanningnew2?date=" . strtotime($date));
				}
				/* ########################### PRINT PDF ######################################### */
				elseif($_POST['pdf_print_action'] && $_POST['pdf_print_action'] == 1)
				{
						
					$post_data['day_planning'] = $day_planning;
					$post_data['clientid'] = $clientid;

					foreach($day_planning['active_patients'] as $pat_id => $pat_values)
					{
						if(!empty($pat_values['actual_location_all']) && $pat_values['actual_location_all']['location_type'] == "1")
						{
							$ids_hospital[] = $pat_values['id'];
						}
					}
					$post_data['hospital'] = $ids_hospital;
					$this->generate_pdf($post_data, "Tagesplanung", "day_planning_pdf2.html");
				}
			}
		}

		//backup of dayplanningnewAction before ispc-1533
		public function dayplanningnewAction_bkp()
		{

			setlocale(LC_ALL, 'de_DE.UTF8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$previleges = new Pms_Acl_Assertion();
			$hidemagic = Zend_Registry::get('hidemagic');
			$return = $previleges->checkPrevilege('Roster', $logininfo->userid, 'canview');
			$clientid = $logininfo->clientid;
			$patient_master = new PatientMaster();

			if(!$return)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}

			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}

				if($_POST['save_visit'] == '1' || $_POST['save_users_to_plan'] == '1' || $_POST['remove_visit_action'] == '1' || $_POST['edit_visit_action'] == '1')
				{
					$has_edit_permissions = Links::checkLinkActionsPermission();
					if(!$has_edit_permissions) // if canedit = 0 - don't allow any changes
					{
						$this->_redirect(APP_BASE . "error/previlege");
						exit;
					}
				}
			}

			//get all client shifts
			$c_shifts = new ClientShifts();
			$client_shifts = $c_shifts->get_client_shifts($clientid);
			/* ###################### DEFAULTS - Current day - today ############################### */
			if($_REQUEST['date'])
			{
				if($_REQUEST['sel'] == 1)
				{
					$date = date('Y-m-d 00:00:00', strtotime($_REQUEST['date']));
				}
				else
				{
					$date = date('Y-m-d 00:00:00', $_REQUEST['date']);
				}
			}
			else
			{
				$date = date('Y-m-d 00:00:00', time());
			}

			$today = date('Y-m-d 00:00:00', time());

			$month_start = date('Y-m', strtotime($date))."-01";

			if(!function_exists('cal_days_in_month'))
			{
				$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($month_start)), 1, date("Y", strtotime($month_start))));
			}
			else
			{
				$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($month_start)), date("Y", strtotime($month_start)));
			}

			$month_end = date('Y-m', strtotime($month_start)).'-'.$month_days;

			$selected_date_month_days = $patient_master->getDaysInBetween($month_start, $month_end);

			$this->view->current_month_days = $selected_date_month_days;


			$previous_date = date('Y-m-d 00:00:00', mktime(date('H', strtotime($date)), date('i', strtotime($date)), 0, date('m', strtotime($date)), date('d', strtotime($date)) - 1, date('Y', strtotime($date))));
			$next_date = date('Y-m-d 00:00:00', mktime(date('H', strtotime($date)), date('i', strtotime($date)), 0, date('m', strtotime($date)), date('d', strtotime($date)) + 1, date('Y', strtotime($date))));

			/* ###################### DEFAULTS - Current day - today ############################### */
			$day_planning['previous_date'] = date('d.m.Y', strtotime($previous_date));
			$day_planning['current_date'] = date('d.m.Y', strtotime($date));
			$day_planning['next_date'] = date('d.m.Y', strtotime($next_date));

			if($this->getRequest()->isPost())
			{
				if($_POST['date_action'] == 1 && !empty($_POST['date']))
				{
					$this->_redirect(APP_BASE . "roster/dayplanningnew?date=" . strtotime($_POST['date']));
				}
			}

			/* ###################### POST - SAVE/EDIT/DELETE visits ############################### */
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}

				if(empty($_POST['pdf_print_action']))
				{
					$save_visit_form = new Application_Form_DailyPlanningVisits();

					/* ########################### SAVE VISIT ######################################### */
					$a_post = $_POST;
					$a_post['current_date'] = $date;
					$a_post['clientid'] = $clientid;

					$save_action = $save_visit_form->save_multiple_visits($a_post);
					//				$this->_redirect(APP_BASE . "roster/dayplanningnew?date=" . strtotime($date));
				}
			}

			$user_planning = new DailyPlanningUsers();
			$userids2date = $user_planning->get_users_by_date($clientid, $date, false, true, $allowed_deleted = 1);
			$users_details2date = $user_planning->get_users_by_date($clientid, $date, false);

			/* ###################### Users Groups- get groups  that are allowed to shou in dienstplan ############################### */
			$docquery = Doctrine_Query::create()
			->select('*')
			->from('Usergroup')
			->where('clientid="' . $clientid . '"')
			->andWhere('isdelete="0"')
			->andWhere('isactive=1');
			$groups = $docquery->fetchArray();

			$groups_ids[] = '9999999999';
			foreach($groups as $k_gr => $v_gr)
			{
				$groups_ids[] = $v_gr['id'];
			}

			/* ###################### Users - get user details ############################### */
			$doc = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('isactive=0')
			->andWhere('isdelete=0')
			->andWhere('usertype!="SA"')
			->andWhere('clientid=' . $clientid)
			->andWhereIn('groupid', $groups_ids)
			->orderBy('last_name ASC');
			$docarray = $doc->fetchArray();

			foreach($docarray as $d_key => $d_val)
			{
				$client_users_ids[] = $d_val['id'];
				$client_users[$d_val['id']]['id'] = $d_val['id'];
				$client_users[$d_val['id']]['name'] = $d_val['last_name'] . ", " . $d_val['first_name'];
				$client_users[$d_val['id']]['phone'] = $d_val['phone'];
				$client_users[$d_val['id']]['mobile'] = $d_val['mobile'];
				$client_users[$d_val['id']]['user_title'] = $d_val['user_title'];
				$client_users[$d_val['id']]['last_name'] = $d_val['last_name'];
				$client_users[$d_val['id']]['first_name'] = $d_val['first_name'];

				if(!empty($d_val['usercolor']))
				{
					$client_users[$d_val['id']]['usercolor'] = $d_val['usercolor'];
				}
				else
				{
					$client_users[$d_val['id']]['usercolor'] = "cccddd";
				}

				if(!empty($d_val['shortname']))
				{
					$client_users[$d_val['id']]['shortname'] = $d_val['shortname'];
				}
				else
				{
					$client_users[$d_val['id']]['shortname'] = substr($d_val['last_name'], 0, 1) . substr($d_val['first_name'], 0, 1);
				}
			}

			if(empty($client_users_ids))
			{
				$client_users_ids[] = "999999";
			}

			/* ###################### users - duty roster for today ############################### */
			$docid = Doctrine_Query::create()
			->select('*')
			->from('Roster')
			->where('clientid = ' . $clientid)
			->andWhereIn('userid', $client_users_ids)
			->andWhere("DATE(duty_date) = DATE('" . $date . "') ")
			->andWhere('isdelete = "0"');
			$rostarray = $docid->fetchArray();

			$user_shifts = array();
			foreach($rostarray as $r_key => $r_value)
			{
				$client_roster_user_ids[] = $r_value['userid'];
				$user_roster[$r_value['userid']] = $r_value;

				//create user shifts array
				if(!array_key_exists($r_value['userid'], $user_shifts))
				{
					$user_shifts[$r_value['userid']] = $r_value['shift'];
				}
			}

			foreach($client_users_ids as $k_uid => $v_uid)
			{
				if(!array_key_exists($v_uid, $user_shifts))
				{
					$users_shift_start[$v_uid]['time'] = strtotime(date('Y-m-d', time())." 08:00:00");

				}
				else
				{
					$users_shift_start[$v_uid]['time'] = strtotime(date('Y-m-d', time())." ".date('H:i:s', strtotime($client_shifts[$user_shifts[$v_uid]]['start'])));
				}
			}
			$this->view->users_json_shifts = json_encode($users_shift_start);

			foreach($client_users as $k_userid => $u_values)
			{
				$client_users[$u_values['id']]['roster'] = $user_roster[$u_values['id']];
				$client_users[$u_values['id']]['active_today'] = $users_details2date[$u_values['id']];
			}

			/* ###################### Client settings ############################### */
			$client_data = Pms_CommonData::getClientData($clientid);
			$tagesplanung_standby_patients = $client_data['0']['tagesplanung_standby_patients']; // ISPC-1170 client setting show patients standby in tagesplanung

			if($tagesplanung_standby_patients == '0') // only active patients
			{

					
				/* ###################### Active patients ############################### */

				if(strtotime($date) >= strtotime($today))
				{

					$active_patients = $this->get_now_active_patients();
				}
				else
				{
					$sql = "p.ipid,e.ipid,a.ipid";
					$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
					$sql .= ",AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
					$sql .= ",convert(AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') using latin1) as zip";
					$sql .= ",convert(AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') using latin1) as street1";
					$sql .= ",convert(AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') using latin1) as city";
					$sql .= ",convert(AES_DECRYPT(p.phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone";
					$sql .= ",convert(AES_DECRYPT(p.mobile,'" . Zend_Registry::get('salt') . "') using latin1) as mobile";
					$sql .= ",convert(AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') using latin1) as sex";

					if($logininfo->usertype == 'SA')
					{
						$sql = "p.ipid,e.ipid,a.ipid,";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
					}

					$date_filter = array('0' => array('start' => $date, 'end' => $date));

					$active_patients_ipids = Pms_CommonData::patients_active($sql, $clientid, $date_filter, false, "convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)", "ASC"); // BRE patient list

					foreach($active_patients_ipids as $k => $p_values)
					{
						$active_patients['ipids'][] = $p_values['ipid'];
						$active_patients['details'][] = $p_values['PatientMaster'];
					}
				}

					
				/* ###################### users - get visits for today ############################### */
				$patient_plan_visits = new DailyPlanningVisits();
				$patient_plan_visits_array = $patient_plan_visits->get_patients_visits($clientid, $date);


					
				foreach($patient_plan_visits_array as $key => $pvvalues)
				{
					$client_users[$pvvalues['userid']]['planned_visits'][] = $pvvalues;

					if(in_array($pvvalues['ipid'], $active_patients['ipids']))
					{
						$patient_visits[$pvvalues['ipid']]['planned_visits'][] = $pvvalues;
						$patient_visits[$pvvalues['ipid']]['existing_visits'] = count($patient_visits[$pvvalues['ipid']]['planned_visits']);
					}
				}

				/* ###################### Patients details ############################### */
				if(empty($active_patients['ipids']))
				{
					$active_patients['ipids'][] = "999999";
				}

				$pl = new PatientVisitsSettings();
				$pat_visits_settings = $pl->getPatientVisitsSettings($active_patients['ipids']);
					
				//active location for all patients START
				$ipids_with_visits[0]['ipid'] = '999999999999';
				$incr = 1;
				foreach($patient_visits as $kipid => $v_p_visits)
				{
					$ipids_with_visits[$incr]['ipid'] = $kipid;
					$incr++;
				}
				$patients_active_location = PatientLocation::getActiveLocations($ipids_with_visits);
					
				//get all patients locations$locations_ids
				$locations_ids[] = '999999999';
				$location_cp_ipids[] = '99999999999';
				foreach($patients_active_location as $kpat=>$v_pat_loc)
				{
					$patient2location[$v_pat_loc['ipid']] = $v_pat_loc['location_id'];
					$locid = substr($v_pat_loc['location_id'], 0, 4);

					if($locid == '8888')
					{
						$location_cp_ipids[] = $v_pat_loc['ipid'];
					}
					else
					{
						$locations_ids[] = $v_pat_loc['location_id'];
					}
				}

				if($location_cp_ipids)
				{
					$contact_persons = ContactPersonMaster::get_contact_persons_by_ipids($location_cp_ipids, false, false);

					foreach($contact_persons as $k_ipid => $v_contact_persons)
					{
						$z[$k_ipid] = 1;
						$cnt_number[$k_ipid] = 1; // display contact number
						foreach($v_contact_persons as $value)
						{
							if($value['isdelete'] == '0')
							{
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson ' . $cnt_number[$k_ipid] . '(' . $value['cnt_last_name'] . ' ' . $value['cnt_first_name'] . ')';
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['street'] = $value['cnt_street1'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['zip'] = $value['cnt_zip'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['city'] = $value['cnt_city'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['phone'] = $value['cnt_phone'];
								$cnt_number[$k_ipid]++;
							}
							else
							{
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson';
							}
							$z[$k_ipid]++;
						}
					}
				}
					
				$locmaster = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->whereIn('id', $locations_ids)
				->orderBy('id ASC');
				$locations_master_res = $locmaster->fetchArray();
					
				foreach($locations_master_res as $k_lmaster => $v_lmaster)
				{
					$master_locations[$v_lmaster['id']]['id'] = $v_lmaster['id'];
					$master_locations[$v_lmaster['id']]['location'] = $v_lmaster['location'];
					$master_locations[$v_lmaster['id']]['location_type'] = $v_lmaster['location_type'];
					$master_locations[$v_lmaster['id']]['street'] = $v_lmaster['street'];
					$master_locations[$v_lmaster['id']]['zip'] = $v_lmaster['zip'];
					$master_locations[$v_lmaster['id']]['city'] = $v_lmaster['city'];
					$master_locations[$v_lmaster['id']]['phone'] = $v_lmaster['phone1'];
				}

				foreach($patient2location as $k_res_pat => $v_res_pat)
				{
					if(!empty($master_locations[$v_res_pat]))
					{
						$patient_active_locations[$k_res_pat] = $master_locations[$v_res_pat];
					}
					else
					{
						$patient_active_locations[$k_res_pat] = $master_locations[$k_res_pat][$v_res_pat];
					}
				}
				//active location for all patients END
					
				foreach($pat_visits_settings as $vskey => $vsvalues)
				{
					$visits_settings[$vsvalues['ipid']] = $vsvalues;
				}
				$patient_details = array();

				foreach($active_patients['details'] as $pat_key => $pat_value)
				{
					$patient_details[$pat_value['ipid']]['id'] = $pat_value['id'];
					$patient_details[$pat_value['ipid']]['patient_name'] = $pat_value['last_name'] . ' ' . $pat_value['first_name'];
					$patient_details[$pat_value['ipid']]['last_name'] = $pat_value['last_name'];
					$patient_details[$pat_value['ipid']]['first_name'] = $pat_value['first_name'];
					$patient_details[$pat_value['ipid']]['street'] = $pat_value['street1'];
					$patient_details[$pat_value['ipid']]['zip'] = $pat_value['zip'];
					$patient_details[$pat_value['ipid']]['city'] = $pat_value['city'];
					if(!empty($pat_value['phone']))
					{
						$patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
					}
					elseif (!empty($pat_value['mobile']) && empty($pat_value['phone']))
					{
						$patient_details[$pat_value['ipid']]['phone'] = $pat_value['mobile'];
					}
					else
					{
						$patient_details[$pat_value['ipid']]['phone'] = '';
					}
					/* $patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
						$patient_details[$pat_value['ipid']]['mobile'] = $pat_value['mobile']; */
					if(!empty($patient_active_locations[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['actual_location'] = $patient_active_locations[$pat_value['ipid']];
					}

					// all patient visits planned for today
					$patient_details[$pat_value['ipid']]['planned_visits'] = $patient_visits[$pat_value['ipid']]['planned_visits'];

					if(!empty($visits_settings[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['visits_per_day'] = $visits_settings[$pat_value['ipid']]['visits_per_day'];
						$patient_details[$pat_value['ipid']]['visit_duration'] = $visits_settings[$pat_value['ipid']]['visit_duration'];
					}
					else
					{
						$patient_details[$pat_value['ipid']]['visits_per_day'] = '1';
						$patient_details[$pat_value['ipid']]['visit_duration'] = "60";
					}

					// patient - remaining visits per day
					$patient_details[$pat_value['ipid']]['visits_remaining_per_day'] = $patient_details[$pat_value['ipid']]['visits_per_day'] - $patient_visits[$pat_value['ipid']]['existing_visits'];
				}

				if($_REQUEST['dbga'] == 1)
				{
					print_R("\n number of active patients \n");
					print_R(count($patient_details));
					print_R("\n active patients \n");
					print_R($patient_details);
					exit;
				}
			}
			else
			{
				/* ###################### Active + Standby patients ############################### */

				if(strtotime($date) >= strtotime($today))
				{

					$active_standby_patients = $this->get_now_active_patients(true);
					//print_r($active_standby_patients);exit;
				}
				else
				{
					$sql = "p.ipid,e.ipid,a.ipid";
					$sql .= ",AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name";
					$sql .= ",AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name";
					$sql .= ",convert(AES_DECRYPT(p.zip,'" . Zend_Registry::get('salt') . "') using latin1) as zip";
					$sql .= ",convert(AES_DECRYPT(p.street1,'" . Zend_Registry::get('salt') . "') using latin1) as street1";
					$sql .= ",convert(AES_DECRYPT(p.city,'" . Zend_Registry::get('salt') . "') using latin1) as city";
					$sql .= ",convert(AES_DECRYPT(p.phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone";
					$sql .= ",convert(AES_DECRYPT(p.mobile,'" . Zend_Registry::get('salt') . "') using latin1) as mobile";
					$sql .= ",convert(AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') using latin1) as sex";

					if($logininfo->usertype == 'SA')
					{
						$sql = "p.ipid,e.ipid,a.ipid,";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
						$sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
					}

					$date_filter = array('0' => array('start' => $date, 'end' => $date));

					$activstandby_patients_ipids = Pms_CommonData::patients_active($sql, $clientid, $date_filter, false, "convert(AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') using latin1)", "ASC",false,false,'0',true); // BRE patient list
					foreach($activstandby_patients_ipids as $k_a => $p_values)
					{
						$active_standby_patients['ipids'][] = $p_values['ipid'];
						$active_standby_patients['details'][] = $p_values['PatientMaster'];
					}


				}

				/* ###################### users - get visits for today ############################### */

				$patient_plan_visits = new DailyPlanningVisits();
				$patient_plan_visits_array = $patient_plan_visits->get_patients_visits($clientid, $date);

				foreach($patient_plan_visits_array as $key => $pvvalues)
				{
					$client_users[$pvvalues['userid']]['planned_visits'][] = $pvvalues;

					if(in_array($pvvalues['ipid'], $active_standby_patients['ipids']))
					{
						$patient_visits[$pvvalues['ipid']]['planned_visits'][] = $pvvalues;
						$patient_visits[$pvvalues['ipid']]['existing_visits'] = count($patient_visits[$pvvalues['ipid']]['planned_visits']);
					}
				}

				/* ###################### Patients details ############################### */
				if(empty($active_standby_patients['ipids']))
				{
					$active_standby_patients['ipids'][] = "999999";
				}

				$pl = new PatientVisitsSettings();
				$pat_visits_settings = $pl->getPatientVisitsSettings($active_standby_patients['ipids']);
					
				//active location for all patients START
				$ipids_with_visits[0]['ipid'] = '999999999999';
				$incr = 1;
				foreach($patient_visits as $kipid => $v_p_visits)
				{
					$ipids_with_visits[$incr]['ipid'] = $kipid;
					$incr++;
				}
				$patients_active_location = PatientLocation::getActiveLocations($ipids_with_visits);
					
				//get all patients locations$locations_ids
				$locations_ids[] = '999999999';
				$location_cp_ipids[] = '99999999999';
				foreach($patients_active_location as $kpat=>$v_pat_loc)
				{
					$patient2location[$v_pat_loc['ipid']] = $v_pat_loc['location_id'];
					$locid = substr($v_pat_loc['location_id'], 0, 4);

					if($locid == '8888')
					{
						$location_cp_ipids[] = $v_pat_loc['ipid'];
					}
					else
					{
						$locations_ids[] = $v_pat_loc['location_id'];
					}
				}
					
				if($location_cp_ipids)
				{
					$contact_persons = ContactPersonMaster::get_contact_persons_by_ipids($location_cp_ipids, false, false);

					foreach($contact_persons as $k_ipid => $v_contact_persons)
					{
						$z[$k_ipid] = 1;
						$cnt_number[$k_ipid] = 1; // display contact number
						foreach($v_contact_persons as $value)
						{
							if($value['isdelete'] == '0')
							{
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson ' . $cnt_number[$k_ipid] . '(' . $value['cnt_last_name'] . ' ' . $value['cnt_first_name'] . ')';
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['street'] = $value['cnt_street1'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['zip'] = $value['cnt_zip'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['city'] = $value['cnt_city'];
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['phone'] = $value['cnt_phone'];
								$cnt_number[$k_ipid]++;
							}
							else
							{
								$master_locations[$k_ipid]['8888' . $z[$k_ipid]]['location'] = 'bei Kontaktperson';
							}
							$z[$k_ipid]++;
						}
					}
				}
					
				$locmaster = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->whereIn('id', $locations_ids)
				->orderBy('id ASC');
				$locations_master_res = $locmaster->fetchArray();
					
				foreach($locations_master_res as $k_lmaster => $v_lmaster)
				{
					$master_locations[$v_lmaster['id']]['id'] = $v_lmaster['id'];
					$master_locations[$v_lmaster['id']]['location'] = $v_lmaster['location'];
					$master_locations[$v_lmaster['id']]['location_type'] = $v_lmaster['location_type'];
					$master_locations[$v_lmaster['id']]['street'] = $v_lmaster['street'];
					$master_locations[$v_lmaster['id']]['zip'] = $v_lmaster['zip'];
					$master_locations[$v_lmaster['id']]['city'] = $v_lmaster['city'];
					$master_locations[$v_lmaster['id']]['phone'] = $v_lmaster['phone1'];
				}

				foreach($patient2location as $k_res_pat => $v_res_pat)
				{
					if(!empty($master_locations[$v_res_pat]))
					{
						$patient_active_locations[$k_res_pat] = $master_locations[$v_res_pat];
					}
					else
					{
						$patient_active_locations[$k_res_pat] = $master_locations[$k_res_pat][$v_res_pat];
					}
				}
				//active location for all patients END

				foreach($pat_visits_settings as $vskey => $vsvalues)
				{
					$visits_settings[$vsvalues['ipid']] = $vsvalues;
				}
				$patient_details = array();

				foreach($active_standby_patients['details'] as $pat_key => $pat_value)
				{
					$patient_details[$pat_value['ipid']]['id'] = $pat_value['id'];
					$patient_details[$pat_value['ipid']]['patient_name'] = $pat_value['last_name'] . ' ' . $pat_value['first_name'];
					$patient_details[$pat_value['ipid']]['last_name'] = $pat_value['last_name'];
					$patient_details[$pat_value['ipid']]['first_name'] = $pat_value['first_name'];
					$patient_details[$pat_value['ipid']]['street'] = $pat_value['street1'];
					$patient_details[$pat_value['ipid']]['zip'] = $pat_value['zip'];
					$patient_details[$pat_value['ipid']]['city'] = $pat_value['city'];
					if(!empty($pat_value['phone']))
					{
						$patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
					}
					elseif (!empty($pat_value['mobile']) && empty($pat_value['phone']))
					{
						$patient_details[$pat_value['ipid']]['phone'] = $pat_value['mobile'];
					}
					else
					{
						$patient_details[$pat_value['ipid']]['phone'] = '';
					}
					/* $patient_details[$pat_value['ipid']]['phone'] = $pat_value['phone'];
					 $patient_details[$pat_value['ipid']]['mobile'] = $pat_value['mobile']; */
					if(!empty($patient_active_locations[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['actual_location'] = $patient_active_locations[$pat_value['ipid']];
					}

					// all patient visits planned for today
					$patient_details[$pat_value['ipid']]['planned_visits'] = $patient_visits[$pat_value['ipid']]['planned_visits'];

					if(!empty($visits_settings[$pat_value['ipid']]))
					{
						$patient_details[$pat_value['ipid']]['visits_per_day'] = $visits_settings[$pat_value['ipid']]['visits_per_day'];
						$patient_details[$pat_value['ipid']]['visit_duration'] = $visits_settings[$pat_value['ipid']]['visit_duration'];
					}
					else
					{
						$patient_details[$pat_value['ipid']]['visits_per_day'] = '1';
						$patient_details[$pat_value['ipid']]['visit_duration'] = "60";
					}

					// patient - remaining visits per day
					$patient_details[$pat_value['ipid']]['visits_remaining_per_day'] = $patient_details[$pat_value['ipid']]['visits_per_day'] - $patient_visits[$pat_value['ipid']]['existing_visits'];
				}

				if($_REQUEST['dbg'] == 1)
				{
					print_R("\n number of active  patients \n");
					print_R($active_patients['ipids_active']);
					print_R("\n number of standby patients \n");
					print_R($standby_patients['ipids_standby']);
					print_R("\n active standby patients \n");

					print_R($patient_details);
					exit;
				}

			}
			/* ########################################################## */
			/* ##################  Check all duty users and see if they have day plan - if not- add them  ###################### */
			/* ########################################################## */
			foreach($client_roster_user_ids as $roster_user_id)
			{
				if(!in_array($roster_user_id, $userids2date))
				{
					$records_users_plan[] = array(
							"clientid" => $clientid,
							"userid" => $roster_user_id,
							"date" => $date
					);
				}
			}

			if(count($records_users_plan) > 0)
			{
				$collection = new Doctrine_Collection('DailyPlanningUsers');
				$collection->fromArray($records_users_plan);
				$collection->save();
				$this->_redirect(APP_BASE . "roster/dayplanningnew?date=" . strtotime($date));
			}

			$day_planning['plan_date'] = $date;
			$day_planning['users'] = $client_users;
			$day_planning['active_patients'] = $patient_details;

			foreach($day_planning['users'] as $ku => $uvs)
			{
				if(!empty($uvs['active_today']))
				{
					$user_active_today[] = $ku;
				}
			}
			$this->view->day_planning = $day_planning;


			/* ########################### ADD USERS TO PLAN & PDF ######################################### */
			if($this->getRequest()->isPost())
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}

				if($_POST['save_users_to_plan'] == "1")
				{
					/* ########################### ADD USERS TO PLAN ######################################### */
					if(empty($_POST['day_planning']['users']))
					{
						$_POST['day_planning']['users'][] = "999999";
					}

					foreach($user_active_today as $k => $user_ida)
					{
						if(!in_array($user_ida, $_POST['day_planning']['users']))
						{
							$clean_data_users[] = $user_ida;
						}
					}

					foreach($_POST['day_planning']['users'] as $k => $post_user)
					{
						if(!in_array($post_user, $clean_data_users) && !in_array($post_user, $user_active_today) && (in_array($post_user, $client_users_ids)))
						{
							$records_users_to_plan[] = array(
									"clientid" => $clientid,
									"userid" => $post_user,
									"date" => $date
							);
						}
					}

					if(!empty($clean_data_users))
					{
						$clean = $this->clean_today_plans($clientid, $date, $clean_data_users);
					}

					if(count($records_users_to_plan) > 0)
					{
						$collection = new Doctrine_Collection('DailyPlanningUsers');
						$collection->fromArray($records_users_to_plan);
						$collection->save();
					}
					$this->_redirect(APP_BASE . "roster/dayplanningnew?date=" . strtotime($date));
				}
				/* ########################### PRINT PDF ######################################### */
				elseif($_POST['pdf_print_action'] && $_POST['pdf_print_action'] == 1)
				{
					$post_data['day_planning'] = $day_planning;
					$post_data['clientid'] = $clientid;
					$this->generate_pdf($post_data, "Tagesplanung", "day_planning_pdf.html");
				}
			}
		}


		private function generate_pdf($post_data, $pdfname, $filename, $orientation = false, $background_pages = false)
		{

			setlocale(LC_ALL, 'de_DE.UTF8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$pdf_names = array(
					'PerformancePdf' => 'SAPV Leistungsnachweis',
					'PerformancePdfs' => 'SAPV Leistungsnachweis',
					'SocialcodePdf' => 'SGB V Abrechnung',
					'SocialcodePdfs' => 'SGB V Abrechnung',
					'MedipumpsControl' => 'Medikamenten Pumpen',
					'InvoiceJournal' => 'Medikamenten Pumpen',
					'Tagesplanung' => 'Tagesplanung',
			);
			if(is_array($filename))
			{
				foreach($filename as $k_file => $v_file)
				{
					$htmlform[$k_file] = Pms_Template::createTemplate($post_data, 'templates/' . $v_file);
					$html[$k_file] = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform[$k_file]);
				}
			}
			else
			{
				$htmlform = Pms_Template::createTemplate($post_data, 'templates/' . $filename);
				$html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
			}
			if($pdfname == 'PerformancePdf' || $pdfname == 'PerformancePdfs')
			{
				$pdf_type = '19';
			}
			else if($pdfname == 'SocialcodePdf' || $pdfname == 'SocialcodePdfs')
			{
				$pdf_type = '22';
			}
			$pdf = new Pms_PDF('L', 'mm', 'A4', true, 'UTF-8', false);
			$pdf->setDefaults(true); //defaults with header
			$pdf->setImageScale(1.6);
			$pdf->SetMargins(10, 5, 10); //reset margins
			$pdf->SetFont(PDF_FONT_NAME_MAIN, '', 11);
			$pdf->setPrintFooter(false); // remove black line at bottom
			$pdf->SetAutoPageBreak(TRUE, 10);

			//set page background for a defined page key in $background_pages array
			$bg_image = Pms_CommonData::getPdfBackground($post_data['clientid'], $pdf_type);
			if($bg_image !== false)
			{
				$bg_image_path = PDFBG_PATH . '/' . $post_data['clientid'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
				if(is_file($bg_image_path))
				{
					$pdf->setBackgroundImage($bg_image_path);
				}
			}

			if(is_array($html))
			{
				foreach($html as $k_html => $v_html)
				{
					if(is_array($orientation))
					{
						if(is_array($background_pages))
						{
							if(!in_array($k_html, $background_pages))
							{
								//unset page background for a nondefined page key in $background_pages array
								$pdf->setBackgroundImage();
							}
						}
						//each page has it`s own orientation
						$pdf->setHTML($v_html, $orientation[$k_html]);
					}
					else
					{
						//all pages one custom orientation
						$pdf->setHTML($v_html, $orientation);
					}
				}
			}
			else
			{
				if(empty($background_pages))
				{
					$pdf->setBackgroundImage();
				}
				$pdf->setHTML($html, $orientation);
			}

// 			$tmpstmp = substr(md5(time() . rand(0, 999)), 0, 12);
// 			mkdir('uploads/' . $tmpstmp);
			$tmpstmp = $pdf->uniqfolder(PDF_PATH);
			$pdf->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');

			$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
// 			$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
// 			exec($cmd);
			$zipname = $tmpstmp . ".zip";
			$filename = "uploads/" . $tmpstmp . ".zip";
			/*
			$con_id = Pms_FtpFileupload::ftpconnect();
			if($con_id)
			{
				$upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
				Pms_FtpFileupload::ftpconclose($con_id);
			}
			*/
			
			$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue ( PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf', strtolower(__CLASS__), NULL, true );
			//this is a foster pdf...	
			@unlink(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');

			if($pdfname == 'PerformancePdf' || $pdfname == 'PerformancePdfs')
			{
				$tabname = 'sapvinvoice';
			}
			else if($pdfname == 'SocialcodePdf' || $pdfname == 'SocialcodePdfs')
			{
				$tabname = 'sgbvinvoice';
			}
			else if($pdfname == 'InvoiceJournal')
			{
				$tabname = 'invoicejournal';
			}

			// 			$cust = new PatientFileUpload();
			// 			$cust->title = Pms_CommonData::aesEncrypt(addslashes($pdf_names[$pdfname]));
			// 			$cust->ipid = $post_data['ipid'];
			// 			$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION ['filename']); //$post['fileinfo']['filename']['name'];
			// 			$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
			// 			$cust->tabname = $tabname;
			// 			$cust->save();
			// 			$file_id = $cust->id;

			ob_end_clean();
			ob_start();

			$pdf->toBrowser($pdfname . '.pdf', 'D');
			exit;
		}

		public function get_now_active_patients($include_standby = false, $extra_ipids = array())
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$hidemagic = Zend_Registry::get('hidemagic');

			$sql = "id, ipid , isstandby, isdischarged";
			$sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
			$sql .= ",AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
			$sql .= ",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
			$sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
			$sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
			$sql .= ",convert(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1) as zip";
			$sql .= ",convert(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1) as street1";
			$sql .= ",convert(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1) as city";
			$sql .= ",convert(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone";
			$sql .= ",convert(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1) as mobile";
			$sql .= ",convert(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1) as sex";
				
			if($logininfo->usertype == 'SA')
			{
				$sql = "id, ipid, isstandby, isdischarged";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name ";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name ";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name ";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title ";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation ";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1 ";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2 ";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip ";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city ";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone ";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile ";
				$sql .= ", IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex ";
			}

			$sql_where = "p.isdischarged = 0 AND p.isdelete = 0 AND p.isstandbydelete = 0 AND e.clientid = " . $clientid;
			if($include_standby === false){
				$sql_where .= " AND p.isstandby = 0";
			}
				
			$patient = Doctrine_Query::create()
			->select($sql)
			->from('PatientMaster p')
			->where('(' . $sql_where . ')');

			//force-inlude the next ipids also ispc-1533
			if(is_array($extra_ipids) && count($extra_ipids)>0){
				$patient->orWhereIn('ipid', $extra_ipids);
			}
			$patient->leftJoin("p.EpidIpidMapping e");
			$patient->orderBy("convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1) ASC");

			$ipidarray = $patient->fetchArray();
				
			foreach($ipidarray as $k => $pat_values)
			{
				$patients['ipids'][] = $pat_values['ipid'];
				$patients['details'][$pat_values['ipid']] = $pat_values;
			}

			if($patients)
			{
				return $patients;
			}
			else
			{
				return false;
			}
				
				
				
		}

		public function newrosterAction()
		{

			setlocale(LC_ALL, 'de_DE.UTF-8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$usertype = $logininfo->usertype;
			$this->view->usertype = $usertype;
			$patient_master = new PatientMaster();

			$modules = new Modules();
			if($modules->checkModulePrivileges("112", $clientid))//Medication acknowledge
			{
				$this->view->sum_line = "1";
			} else
			{
				$this->view->sum_line = "0";
			}
			
			//		1. get working month

			/*
			 * TODO-1545 Ancuta: 08.05.2018
			 */
			// get first year where client has data
			$first_saved_entry = Doctrine_Query::create()
			->select('YEAR(duty_date) as first_saved_year')
			->from('Roster')
			->where('clientid = ' . $clientid)
			->andWhere('isdelete = "0"')
			->orderBy('duty_date ASC')
			->limit(1)
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
			
		    $past_years = 1;
			if ($first_saved_entry) {
			     $first_saved_year = $first_saved_entry['first_saved_year'];
			     //    TODO-2742 Ancuta 18.12.2019 :: Add current year to options - if the first save is not from curent year
			     if($first_saved_year > date("Y") ){
			         $first_saved_year = date("Y");
			     }
			     // -- 
			     if($first_saved_year > 2008){
    			     $past_years = date('Y') - $first_saved_year;
			     }
			}
			$this->view->options = Pms_CommonData::getMonths($past_years);
			
			
			if(strlen($_POST['month']) > 0)
			{
				$curent_month = $_POST['month'];
			}
			else if(strlen($_REQUEST['month']) > 0)
			{
				$curent_month = $_REQUEST['month'];
			}
			else
			{
				$curent_month = date("Y_m", time());
			}

			$this->view->curmonth = $curent_month;

			$month_start = str_replace("_", "-", $curent_month . "_01");
			$month_start_ts = strtotime(str_replace("_", "-", $curent_month . "_01"));

			$month_end = str_replace("_", "-", $curent_month . "_" . date('t', $month_start_ts));
			$month_end_ts = strtotime(str_replace("_", "-", $curent_month . "_" . date('t', $month_start_ts)));

			$month_days = $patient_master->getDaysInBetween($month_start, $month_end);
			$this->view->month_days = $month_days;


			//		2. get all groups and users, separate del and active users
			$docquery = Doctrine_Query::create()
			->select('*')
			->from('Usergroup')
			->where('clientid="' . $clientid . '"')
			->andWhere('isdelete="0"')
			->andWhere('isactive=1');
			$groups = $docquery->fetchArray();

			$groups_ids[] = '9999999999';
			foreach($groups as $k_gr => $v_gr)
			{
				$groups_details[$v_gr['id']] = $v_gr;

				$groups_ids[] = $v_gr['id'];
			}
			$this->view->groups = $groups_details;

			$doc = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('isdelete=0')
			->andWhere('isactive = "0"')
			->andWhereIn('groupid', $groups_ids);
			if($usertype != 'SA')
			{
				$doc->andWhere('clientid=' . $clientid);
				$doc->andWhere('usertype!="SA"');
			}

			$doc->orderBy('groupid, last_name ASC');
			$docarray = $doc->fetcharray();

			$roster_users_order = new RosterUsersOrder();
			$users_order = $roster_users_order->get_order($clientid, $userid);




			foreach($docarray as $k_doc => $v_doc)
			{
				$group2users[$v_doc['groupid']][0] = "";
				$group2users[$v_doc['groupid']][$v_doc['id']] = $v_doc['user_title'] . " " . $v_doc['last_name'] . ', ' . $v_doc['first_name'];


				$users['all'][$v_doc['id']] = $v_doc;

				if($v_doc['isdelete'] == '0')
				{
					$users['active'][] = $v_doc;
				}
				else
				{
					$users['deleted'][] = $v_doc;
				}
			}

			if($users_order)
			{

				foreach($users_order['users_order'] as $k_group => $v_group_users)
				{
					foreach($v_group_users as $k_user_order => $v_user)
					{
						if(array_key_exists($v_user, $group2users[$k_group]))
						{
							$sorted_group2users[$k_group][0] = '';
							$sorted_group2users[$k_group][$v_user] = $group2users[$k_group][$v_user];
						}
					}
				}

				//add newly added users which are not saved in users order
				foreach($docarray as $k_doc => $v_doc)
				{
					if(!array_key_exists($v_doc['id'], $sorted_group2users[$v_doc['groupid']]))
					{
						$sorted_group2users[$v_doc['groupid']][$v_doc['id']] = $group2users[$v_doc['groupid']][$v_doc['id']];
					}
				}


				if(!empty($sorted_group2users))
				{
					$group2users = $sorted_group2users;
				}
			}
				
			if($_REQUEST['delid'] > 0)
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}


				$upload_form = new Application_Form_RosterFileUpload();
				$upload_form->deleteFile($_REQUEST['delid']);
			}

			$this->view->users = $users;
			$this->view->groupUsers = $group2users;

			//get client users row amount
			$client_users_rows = new RosterClientUsersRows();
			$users_rows_ammount = $client_users_rows->get_client_users_rows($clientid);
			$this->view->user_rows = $users_rows_ammount;

			//get all client shifts
			$c_shifts = new ClientShifts();
			$client_shifts = $c_shifts->get_client_shifts($clientid);
			$client_shifts = $this->array_sort($client_shifts,'name');		
			
			$client_shifts_selectbox = array( "" => $this->view->translate('select_shift_option'));
			
			$shift_substitution = 0;
			foreach($client_shifts as $k_c_shift => $v_c_shift)
			{
				$client_shifts_arr[$v_c_shift['id']]['name'] = $v_c_shift['name'];
				$client_shifts_arr[$v_c_shift['id']]['color'] = $v_c_shift['color'];
				if(!empty($v_c_shift['shortcut']))
				{
					if(strlen($v_c_shift['shortcut']) > 3)
					{
						$client_shifts_arr[$v_c_shift['id']]['shortcut'] = mb_substr($v_c_shift['shortcut'], 0, 3, "UTF-8");
					}
					else
					{
						$client_shifts_arr[$v_c_shift['id']]['shortcut'] = $v_c_shift['shortcut'];
					}
				}
				else
				{
					$client_shifts_arr[$v_c_shift['id']]['shortcut'] = mb_substr($v_c_shift['name'], 0, 3, "UTF-8");
					$shift_substitution ++; 
				}
				
				
				$client_shifts_arr[$v_c_shift['id']]['valid_till_date'] = isset($v_c_shift['active_till']) ? strtotime($v_c_shift['active_till'])  : null;
				
				$client_shifts[$k_c_shift]['valid_till_date'] = isset($v_c_shift['active_till']) ? " (bis " . date("d.m.Y", strtotime($v_c_shift['active_till'])) .")": "";
				
				if ( (int)$k_c_shift > 0
						&& ( empty($v_c_shift['active_till']) 
							 || $month_start_ts <= strtotime($v_c_shift['active_till'])))
				{

								
					$client_shifts_selectbox [$k_c_shift] = $v_c_shift['name'] . "( ". $client_shifts_arr[$v_c_shift['id']]['shortcut'] .")";
					 
					if (isset($v_c_shift['active_till']) && $month_end_ts >= strtotime($v_c_shift['active_till'])) {
						$client_shifts_selectbox [$k_c_shift] .= " (bis ". date("d.m.Y", strtotime($v_c_shift['active_till'])) .")";
					}
					
				}
			}
				
			$this->view->client_shifts = $client_shifts;
			$this->view->client_shifts_min = $client_shifts_arr;
			$this->view->shift_substitution = $shift_substitution;
			$this->view->client_shifts_selectbox = $client_shifts_selectbox;

			//		3. process post data
			if($this->getRequest()->isPost() && $_POST['submit_buton'])
			{
				$has_edit_permissions = Links::checkLinkActionsPermission();

				if(!$has_edit_permissions) // if canedit = 0 - don't allow any changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}
				$post = $_POST;
				$roster_form = new Application_Form_Roster();
				$insert_roster = $roster_form->insert_data($clientid, $post, $month_start);

				$this->_redirect(APP_BASE . 'roster/newroster?month=' . $_POST['month']);
				exit;
			}

			//		3. get roster saved data
			$docid = Doctrine_Query::create()
			->select('*')
			->from('Roster')
			->where('clientid = ' . $clientid)
			->andWhere("duty_date between '" . $month_start . "' and '" . $month_end . "'")
			->andWhere('isdelete = "0"');
			$roster_arr = $docid->fetchArray();

			$used_shifts = array();
			foreach($roster_arr as $k_roster => $v_roster)
			{
				/* print_r("\n");
				 print_r($v_roster);
				 print_r("\n"); */
				if($v_roster['userid'] != '0')
				{
					$master_roster_data[$v_roster['userid']][$v_roster['duty_date']][$v_roster['row']] = $v_roster['shift'];
						
					$group_shifts_count[$v_roster['user_group']][$v_roster['duty_date']][$v_roster['shift']]++;;
					$group_shifts[$v_roster['user_group']][$v_roster['shift']] = 0;
				}
				$used_shifts[] = $v_roster['shift'];

			}
				

			$this->view->used_shifts= $used_shifts;
			$this->view->roster_saved_data = $master_roster_data;
				
			$this->view->group_shifts = $group_shifts;
			$this->view->group_shifts_count = $group_shifts_count;
			// print_r($group_shifts_count);exit;
				
			foreach($client_shifts as $k_c_shift => $v_c_shift)
			{
				if ( (int)$k_c_shift > 0
						&& ( (empty($v_c_shift['active_till'])
								|| $month_start_ts <= strtotime($v_c_shift['active_till']))) || in_array($k_c_shift,$used_shifts) )
				{
				
					$client_shifts_l_arr[$v_c_shift['id']]['name'] = $v_c_shift['name'];
					$client_shifts_l_arr[$v_c_shift['id']]['color'] = $v_c_shift['color'];
					if(!empty($v_c_shift['shortcut']))
					{
						if(strlen($v_c_shift['shortcut']) > 3)
						{
							$client_shifts_l_arr[$v_c_shift['id']]['shortcut'] = mb_substr($v_c_shift['shortcut'], 0, 3, "UTF-8");
						}
						else
						{
							$client_shifts_l_arr[$v_c_shift['id']]['shortcut'] = $v_c_shift['shortcut'];
						}
					}
					else
					{
						$client_shifts_l_arr[$v_c_shift['id']]['shortcut'] = mb_substr($v_c_shift['name'], 0, 3, "UTF-8");
					}
						
						
					$client_shifts_l_arr[$v_c_shift['id']]['valid_till_date'] = isset($v_c_shift['active_till']) ? strtotime($v_c_shift['active_till'])  : null;
						
					$client_shifts_l_arr[$k_c_shift]['valid_till_date'] = isset($v_c_shift['active_till']) ? " (bis " . date("d.m.Y", strtotime($v_c_shift['active_till'])) .")": "";
				}
			}
				
			//national holidays
			$nh = new NationalHolidays();
			$national_holiday = $nh->getNationalHoliday($clientid, $month_start, true);

			foreach($national_holiday as $k_holiday => $v_holiday)
			{
				$holiday_dates[] = date('Y-m-d', strtotime($v_holiday['NationalHolidays']['date']));
			}

			$this->view->national_holidays_js = json_encode($holiday_dates);
			$this->view->national_holidays = $holiday_dates;


			if($this->getRequest()->isPost())
			{
				if($_POST['print_pdf_buton']) {
					$post['users'] = $users;
					$post['client_shifts'] = $client_shifts;
					$post['client_shifts_min'] = $client_shifts_arr;
					$post['month_days_array'] = $month_days;
					$post['national_holidays'] = $holiday_dates;
					$post['roster_saved_data'] = $master_roster_data;
// 					$post['selected_period'] = $this->view->selected_period;
					$post['selected_period'] = date("m / Y",strtotime($month_start));
					$post['groupUsers'] = $group2users;
					$post['groups'] = $groups_details;
					$post['user_rows'] = $users_rows_ammount;
					$post['sum_line'] = $this->view->sum_line;
					$post['group_shifts'] = $group_shifts;
					$post['group_shifts_count'] = $group_shifts_count;
					$post['client_shifts_l_arr'] = $client_shifts_l_arr;
					$this->generatePdfNew(3, $post, 'printrosterpdf', "printrosterpdf.html");
				}
			}
			
			///upload data stuff
			if($this->getRequest()->isPost() && $_POST['fileuploads'] == "1")
			{


				$has_edit_permissions = Links::checkLinkActionsPermission();
				if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
				{
					$this->_redirect(APP_BASE . "error/previlege");
					exit;
				}

				$ftype = $_SESSION['filetype'];
				if($ftype)
				{
					$filetypearr = explode("/", $ftype);
					if($filetypearr[1] == "vnd.openxmlformats-officedocument.spreadsheetml.sheet")
					{
						$filetype = "XLSX";
					}
					elseif($filetypearr[1] == "vnd.openxmlformats-officedocument.wordprocessingml.document")
					{
						$filetype = "docx";
					}
					elseif($filetypearr[1] == "X-OCTET-STREAM")
					{
						$filetype = "PDF";
					}
					else
					{
						$filetype = $filetypearr[1];
					}
				}


				$upload_form = new Application_Form_RosterFileUpload();

				$a_post = $_POST;
				$a_post['clientid'] = $logininfo->clientid;
				$a_post['filetype'] = $_SESSION['filetype'];


				if($upload_form->validate($a_post))
				{
					$upload_form->insertData($a_post);
				}
				else
				{
					$upload_form->assignErrorMessages();
					$this->retainValues($_POST);
				}

				//remove session stuff
				$_SESSION['filename'] = '';
				$_SESSION['filetype'] = '';
				$_SESSION['filetitle'] = '';
				unset($_SESSION['filename']);
				unset($_SESSION['filetype']);
				unset($_SESSION['filetitle']);
			}

			$files = new RosterFileUpload();
			$filesData = $files->getClientFiles($clientid);

			$this->view->filesData = $filesData;
			$this->view->showInfo = $logininfo->showinfo;

			$allUsers = Pms_CommonData::getClientUsers($clientid);
			foreach($allUsers as $keyu => $user)
			{
				$all_users_array[$user['id']] = $user['user_title'] . " " . $user['last_name'] . ", " . $user['first_name'];
			}

			$this->view->allusers = $all_users_array;
		}

		public function shiftlistoldAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;


			$c_shift = new ClientShifts();
			$client_shifts = $c_shift->get_client_shifts($clientid);
			$this->view->client_shifts = $client_shifts;
				
			$dty_roster = new Roster();
			$dty_roster_arr = $dty_roster->find_shift_data($clientid);
				
			foreach($dty_roster_arr as $k_roster => $v_droster)
			{
				$shift_count[$v_droster['shift']] =  $v_droster['count'];
			}

			foreach($client_shifts as $k=>$client_shift_details)
			{
				if(isset($shift_count[$client_shift_details['id']]) || $shift_count[$client_shift_details['id']] > 0)
				{
					$shifts['used'][] = $client_shift_details['id'];
				}
				else
				{
					$shifts['not_used'][] = $client_shift_details['id'];
				}
			}
			$this->view->sfift_used = $shifts['used'];
				
				
				
		}

		public function addshiftAction()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
				
			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			if($this->getRequest()->isPost())
			{
				$post = $_POST;
				$client_shift_form = new Application_Form_ClientShifts();
				$insert_client_shifts = $client_shift_form->insert_data($post, $clientid);
				if($insert_client_shifts)
				{
					$this->view->error_message = $this->view->translate("recordinsertsucessfully");
					//$this->_redirect(APP_BASE . 'roster/shiftlist?flg=suc');
					//exit;
				}
				else
				{
					$this->_redirect(APP_BASE . 'roster/addshift');
					exit;
				}
			}
				
			//ispc-1855 pseudogroups_with_tours associate with shifts
			//get pseudogroup vith visiting rights
			$UserPseudoGroup = UserPseudoGroup :: get_userpseudo_with_make_visits($clientid);
			$pseudogroups_with_tours = array("0"=>"");
			foreach ($UserPseudoGroup as $id => $group_details){
				if ($group_details['makes_visits'] == "tours"){
					$pseudogroups_with_tours[$group_details['id']] = $group_details['servicesname'];
				}
			}
			$this->view->pseudogroups_with_tours = $pseudogroups_with_tours;
		}

		public function editshiftAction()
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$this->_helper->viewRenderer('addshift');

			$has_edit_permissions = Links::checkLinkActionsPermission();
			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}
			if($this->getRequest()->isPost())
			{
				$post = $_POST;
				$client_shift_form = new Application_Form_ClientShifts();
				$update_client_shifts = $client_shift_form->update_data($_REQUEST['sid'], $post, $clientid);

				if($update_client_shifts)
				{
					$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
					$this->_redirect(APP_BASE . 'roster/shiftlist?flg=suc&mes='.urlencode($this->view->error_message));
					exit;
				}
				else
				{
					$this->_redirect(APP_BASE . 'roster/editshift?sid=' . $_REQUEST['sid']);
					exit;
				}
			}

			if($_REQUEST['sid'])
			{
				$sid = $_REQUEST['sid'];

				$c_shifts = new ClientShifts();
				$client_shift_details = $c_shifts->get_shift_details($sid, $clientid);

				//ispc-1855 pseudogroups_with_tours associate with shifts
				//get pseudogroup vith visiting rights
				$UserPseudoGroup = UserPseudoGroup :: get_userpseudo_with_make_visits($clientid);
				$pseudogroups_with_tours = array("0"=>"");
				foreach ($UserPseudoGroup as $id => $group_details){
					if ($group_details['makes_visits'] == "tours"){
						$pseudogroups_with_tours[$group_details['id']] = $group_details['servicesname'];
					}
				}
				$this->view->pseudogroups_with_tours = $pseudogroups_with_tours;

				$this->retainValues($client_shift_details[$sid]);
			}
		}
		public function deleteAction()
		{
			$this->_helper->viewRenderer('list');
			$has_edit_permissions = Links::checkLinkActionsPermission();

			if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
			{
				$this->_redirect(APP_BASE . "error/previlege");
				exit;
			}

			if($_GET['id']){
				//	echo 'ok'; exit;
				$thrash = Doctrine::getTable('ClientShifts')->find($_GET['id']);
				$thrash->isdelete = 1;
				$thrash->save();
				
				$this->view->error_message = $this->view->translate("recorddeletedsucessfully");
				$this->_redirect(APP_BASE.'roster/shiftlist?flg=suc&mes='.urlencode($this->view->error_message));
				 
			}

			$this->view->delete_message = "Record deleted sucessfully";
		}
		function clean_today_plans2($clientid, $date, $user_ids)
		{

			if(!is_array($user_ids))
			{
				$user_ids = array($user_ids);
			}

			if(empty($user_ids))
			{
				$user_ids[] = "999999";
			}

			$dutyuset = Doctrine_Query::create()
			->update('DailyPlanningUsers')
			->set('isdelete', "1")
			->where(" clientid='" . $clientid . "' ")
			->andWhereIn("userid", $user_ids)
			->andWhere("DATE(date) =  DATE('" . $date . "')");
			$clean_users = $dutyuset->execute();

			if($clean_users)
			{
				$return['result']['users'] = "users removed";
			}
			else
			{
				$return['result']['users'] = "users error";
			}

			$dutyvset = Doctrine_Query::create()
			->update('DailyPlanningVisits2')
			->set('isdelete', "1")
			->where(" clientid='" . $clientid . "' ")
			->andWhereIn("userid", $user_ids)
			->andWhere("DATE(date) =  DATE('" . $date . "')");
			$clean_visits = $dutyvset->execute();


			if($clean_users)
			{
				$return['result']['patients'] = "patients removed";
			}
			else
			{
				$return['result']['patients'] = "patients error";
			}
			return $return;
		}

		function clean_today_plans($clientid, $date, $user_ids)
		{

			if(!is_array($user_ids))
			{
				$user_ids = array($user_ids);
			}

			if(empty($user_ids))
			{
				$user_ids[] = "999999";
			}

			$dutyuset = Doctrine_Query::create()
			->update('DailyPlanningUsers')
			->set('isdelete', "1")
			->where(" clientid='" . $clientid . "' ")
			->andWhereIn("userid", $user_ids)
			->andWhere("DATE(date) =  DATE('" . $date . "')");
			$clean_users = $dutyuset->execute();

			if($clean_users)
			{
				$return['result']['users'] = "users removed";
			}
			else
			{
				$return['result']['users'] = "users error";
			}

			$dutyvset = Doctrine_Query::create()
			->update('DailyPlanningVisits')
			->set('isdelete', "1")
			->where(" clientid='" . $clientid . "' ")
			->andWhereIn("userid", $user_ids)
			->andWhere("DATE(date) =  DATE('" . $date . "')");
			$clean_visits = $dutyvset->execute();


			if($clean_users)
			{
				$return['result']['patients'] = "patients removed";
			}
			else
			{
				$return['result']['patients'] = "patients error";
			}
			return $return;
		}

		public function printrosterAction()
		{

			setlocale(LC_ALL, 'de_DE.UTF-8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$usertype = $logininfo->usertype;
			$patient_master = new PatientMaster();
			$this->_helper->layout->setLayout('layout_totalblank');
			$previleges = new Pms_Acl_Assertion();
			$return = $previleges->checkPrevilege('Roster', $logininfo->userid, 'canview');

			if(!$return || !$clientid)
			{
				$this->_redirect(APP_BASE . "error/previlege");
			}
			$modules = new Modules();
			if($modules->checkModulePrivileges("112", $clientid))//Medication acknowledge
			{
				$this->view->sum_line = "1";
			} else
			{
				$this->view->sum_line = "0";
			}
			//		1. get working month
			$this->view->options = Pms_CommonData::getMonths();
			if(strlen($_POST['month']) > 0)
			{
				$curent_month = $_POST['month'];
			}
			else if(strlen($_REQUEST['month']) > 0)
			{
				$curent_month = $_REQUEST['month'];
			}
			else
			{
				$curent_month = date("Y_m", time());
			}

			$this->view->curmonth = $curent_month;

			$month_start = str_replace("_", "-", $curent_month . "_01");
			$month_start_ts = strtotime(str_replace("_", "-", $curent_month . "_01"));

			$month_end = str_replace("_", "-", $curent_month . "_" . date('t', $month_start_ts));
			$month_end_ts = strtotime(str_replace("_", "-", $curent_month . "_" . date('t', $month_start_ts)));

			$month_days = $patient_master->getDaysInBetween($month_start, $month_end);
			$this->view->month_days = $month_days;
			$this->view->selected_period = date("m / Y", strtotime($month_start));


			//		2. get all groups and users, separate del and active users
			$docquery = Doctrine_Query::create()
			->select('*')
			->from('Usergroup')
			->where('clientid="' . $clientid . '"')
			->andWhere('isdelete="0"')
			->andWhere('isactive=1');
			$groups = $docquery->fetchArray();

			$groups_ids[] = '9999999999';
			foreach($groups as $k_gr => $v_gr)
			{
				$groups_details[$v_gr['id']] = $v_gr;

				$groups_ids[] = $v_gr['id'];
			}
			$this->view->groups = $groups_details;

			$doc = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('isdelete=0')
			->andWhereIn('groupid', $groups_ids);
			if($usertype == 'SA')
			{
				$doc->andWhere('clientid=' . $clientid);
				$doc->andWhere('usertype!="SA"');
			}
			//$doc->andWhere("(isactive=0 OR DATE_FORMAT(isactive_date, '%Y-%m') >= '". date("Y-m", strtotime($month_start)) ."')");
			$doc->orderBy('groupid, last_name ASC');
			$docarray = $doc->fetcharray();

			foreach($docarray as $k_doc => $v_doc)
			{
				$group2users[$v_doc['groupid']][0] = "";
				//			$group2users[$v_doc['groupid']][$v_doc['id'] . '-' . $v_doc['groupid']] = $v_doc['last_name'] . ', ' . $v_doc['first_name'];
				$group2users[$v_doc['groupid']][$v_doc['id']] = $v_doc['user_title'] . " " . $v_doc['last_name'] . ', ' . $v_doc['first_name'];


				$users['all'][$v_doc['id']] = $v_doc;

				if($v_doc['isdelete'] == '0')
				{
					$users['active'][] = $v_doc;
				}
				else
				{
					$users['deleted'][] = $v_doc;
				}
			}

			$roster_users_order = new RosterUsersOrder();
			$users_order = $roster_users_order->get_order($clientid, $userid);

			if($users_order)
			{
				foreach($users_order['users_order'] as $k_group => $v_group_users)
				{
					foreach($v_group_users as $k_user_order => $v_user)
					{
						if(array_key_exists($v_user, $group2users[$k_group]))
						{
							$sorted_group2users[$k_group][0] = '';
							$sorted_group2users[$k_group][$v_user] = $group2users[$k_group][$v_user];
						}
					}
				}


				//add newly added users which are not saved in users order
				foreach($docarray as $k_doc => $v_doc)
				{
					if(!array_key_exists($v_doc['id'], $sorted_group2users[$v_doc['groupid']]))
					{
						$sorted_group2users[$v_doc['groupid']][$v_doc['id']] = $group2users[$v_doc['groupid']][$v_doc['id']];
					}
				}

				if(!empty($sorted_group2users))
				{
					$group2users = $sorted_group2users;
				}
			}

			$client_users_rows = new RosterClientUsersRows();
			$users_rows_ammount = $client_users_rows->get_client_users_rows($clientid);
			$this->view->user_rows = $users_rows_ammount;

			$this->view->users = $users;
				
			//get all client shifts
			$c_shifts = new ClientShifts();
			$client_shifts = $c_shifts->get_client_shifts($clientid);

			$shift_substitution = 0;
			foreach($client_shifts as $k_c_shift => $v_c_shift)
			{
				$client_shifts_arr[$v_c_shift['id']]['name'] = $v_c_shift['name'];
				$client_shifts_arr[$v_c_shift['id']]['color'] = $v_c_shift['color'];
				if(!empty($v_c_shift['shortcut']))
				{
					if(strlen($v_c_shift['shortcut']) > 3)
					{
						$client_shifts_arr[$v_c_shift['id']]['shortcut'] = mb_substr($v_c_shift['shortcut'], 0, 3, "UTF-8");
					}
					else
					{
						$client_shifts_arr[$v_c_shift['id']]['shortcut'] = $v_c_shift['shortcut'];
					}
				}
				else
				{
					$client_shifts_arr[$v_c_shift['id']]['shortcut'] = mb_substr($v_c_shift['name'], 0, 3, "UTF-8");
					$shift_substitution ++;
				}
				
				$client_shifts_arr[$v_c_shift['id']]['valid_till_date'] = isset($v_c_shift['active_till']) ? strtotime($v_c_shift['active_till'])  : null;
				
				$client_shifts[$k_c_shift]['valid_till_date'] = isset($v_c_shift['active_till']) ? " (bis " . date("d.m.Y", strtotime($v_c_shift['active_till'])) .")": "";
			}

			$this->view->client_shifts = $client_shifts;
			$this->view->client_shifts_min = $client_shifts_arr;
			$this->view->shift_substitution = $shift_substitution;

			//		3. process post data
			if($this->getRequest()->isPost() && $_POST['submit_buton'])
			{
				$post = $_POST;
				$roster_form = new Application_Form_Roster();
				$insert_roster = $roster_form->insert_data($clientid, $post, $month_start);

				$this->_redirect(APP_BASE . 'roster/newroster?month=' . $_POST['month']);
				exit;
			}

			//		3. get roster saved data
			$docid = Doctrine_Query::create()
			->select('*')
			->from('Roster')
			->where('clientid = ' . $clientid)
			->andWhere("duty_date between '" . $month_start . "' and '" . $month_end . "'")
			->andWhere('isdelete = "0"');
			$roster_arr = $docid->fetchArray();

			foreach($roster_arr as $k_roster => $v_roster)
			{
				if($v_roster['userid'] != '0')
				{
					$master_roster_data[$v_roster['userid']][$v_roster['duty_date']][$v_roster['row']] = $v_roster['shift'];

					$group_shifts_count[$v_roster['user_group']][$v_roster['duty_date']][$v_roster['shift']]++;;
					$group_shifts[$v_roster['user_group']][$v_roster['shift']] = 0;
				}
				$used_shifts[] = $v_roster['shift'];
			}
				
			foreach( $users['all'] as $userid => $val){
				if ($val['isactive'] == "1" && !isset($master_roster_data[$userid])){
					unset( $group2users[ $val['groupid'] ] [ $userid ] );
				}
			}
			$this->view->groupUsers = $group2users;
				
			$this->view->roster_saved_data = $master_roster_data;
			$this->view->group_shifts = $group_shifts;
			$this->view->group_shifts_count = $group_shifts_count;
			
				
			
			foreach($client_shifts as $k_c_shift => $v_c_shift)
			{
				if ( (int)$k_c_shift > 0
						&& ( (empty($v_c_shift['active_till'])
								|| $month_start_ts <= strtotime($v_c_shift['active_till']))) || in_array($k_c_shift,$used_shifts) )
				{
			
					$client_shifts_l_arr[$v_c_shift['id']]['name'] = $v_c_shift['name'];
					$client_shifts_l_arr[$v_c_shift['id']]['color'] = $v_c_shift['color'];
					if(!empty($v_c_shift['shortcut']))
					{
						if(strlen($v_c_shift['shortcut']) > 3)
						{
							$client_shifts_l_arr[$v_c_shift['id']]['shortcut'] = mb_substr($v_c_shift['shortcut'], 0, 3, "UTF-8");
						}
						else
						{
							$client_shifts_l_arr[$v_c_shift['id']]['shortcut'] = $v_c_shift['shortcut'];
						}
					}
					else
					{
						$client_shifts_l_arr[$v_c_shift['id']]['shortcut'] = mb_substr($v_c_shift['name'], 0, 3, "UTF-8");
					}
			
			
					$client_shifts_l_arr[$v_c_shift['id']]['valid_till_date'] = isset($v_c_shift['active_till']) ? strtotime($v_c_shift['active_till'])  : null;
			
					$client_shifts_l_arr[$k_c_shift]['valid_till_date'] = isset($v_c_shift['active_till']) ? " (bis " . date("d.m.Y", strtotime($v_c_shift['active_till'])) .")": "";
				}
			}
			$this->view->client_shifts_l_arr = $client_shifts_l_arr;
				
			
			$nh = new NationalHolidays();
			$national_holiday = $nh->getNationalHoliday($clientid, $month_start, true);
			
			foreach($national_holiday as $k_holiday => $v_holiday)
			{
				$holiday_dates[] = date('Y-m-d', strtotime($v_holiday['NationalHolidays']['date']));
			}
			$this->view->national_holidays = $holiday_dates;
			/*
			 ///upload data stuff
			 if($this->getRequest()->isPost() && $_POST['fileuploads'] == "1")
			 {
				$ftype = $_SESSION['filetype'];
				if($ftype)
				{
				$filetypearr = explode("/", $ftype);
				if($filetypearr[1] == "vnd.openxmlformats-officedocument.spreadsheetml.sheet")
				{
				$filetype = "XLSX";
				}
				elseif($filetypearr[1] == "vnd.openxmlformats-officedocument.wordprocessingml.document")
				{
				$filetype = "docx";
				}
				elseif($filetypearr[1] == "X-OCTET-STREAM")
				{
				$filetype = "PDF";
				}
				else
				{
				$filetype = $filetypearr[1];
				}
				}


				$upload_form = new Application_Form_RosterFileUpload();

				$a_post = $_POST;
				$a_post['clientid'] = $logininfo->clientid;
				$a_post['filetype'] = $_SESSION['filetype'];

				if($upload_form->validate($a_post))
				{
				$upload_form->insertData($a_post);
				}
				else
				{
				$upload_form->assignErrorMessages();
				$this->retainValues($_POST);
				}

				//remove session stuff
				$_SESSION['filename'] = '';
				$_SESSION['filetype'] = '';
				$_SESSION['filetitle'] = '';
				unset($_SESSION['filename']);
				unset($_SESSION['filetype']);
				unset($_SESSION['filetitle']);
				}

				$files = new RosterFileUpload();
				$filesData = $files->getClientFiles($clientid);
				//
				$this->view->filesData = $filesData;
				$this->view->showInfo = $logininfo->showinfo;

				$allUsers = Pms_CommonData::getClientUsers($clientid);
				foreach($allUsers as $keyu => $user)
				{
				$all_users_array[$user['id']] = $user['first_name'] . ", " . $user['last_name'];
				}

				$this->view->allusers = $all_users_array;
				*/
		}

		//new sort method (sorts umlauts using multibyte string)
		private function array_sort($array, $on = NULL, $order = SORT_ASC)
		{
			$new_array = array();
			$sortable_array = array();

			if(count($array) > 0)
			{
				foreach($array as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $k2 => $v2)
						{
							if($k2 == $on)
							{
								if($on == 'stratDate')
								{
									$sortable_array[$k] = strtotime($v2);
								}
								elseif($on == 'epid')
								{
									$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
								}
								else
								{
									//									$sortable_array[$k] = ucfirst($v2);
									$sortable_array[$k] = Pms_CommonData::mb_ucfirst($v2);
								}
							}
						}
					}
					else
					{
						if($on == 'stratDate')
						{
							$sortable_array[$k] = strtotime($v);
						}
						elseif($on == 'epid')
						{
							$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
						}
						else
						{
							//							$sortable_array[$k] = ucfirst($v);
							$sortable_array[$k] = Pms_CommonData::mb_ucfirst($v);
						}
					}
				}

				switch($order)
				{
					case SORT_ASC:
						//						asort($sortable_array);
						$sortable_array = Pms_CommonData::a_sort($sortable_array);
						break;
					case SORT_DESC:
						//						arsort($sortable_array);
						$sortable_array = Pms_CommonData::ar_sort($sortable_array);
						break;
				}

				foreach($sortable_array as $k => $v)
				{
					$new_array[$k] = $array[$k];
				}
			}

			return $new_array;
		}

		private function generatePdfNew($chk, $post, $pdfname, $filename)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$clientinfo = Pms_CommonData::getClientData($clientid);
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$excluded_keys = array(
					'stamp_block'
			);
			
			$post = Pms_CommonData::clear_pdf_data($post, $excluded_keys);		
			
			$htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);
			//print_r($htmlform);exit; 


		
			if($chk == 3)
			{
				
				$navnames = array(
						"printrosterpdf" => 'DienstPlan'
				);
		
				//$pdf = new Pms_PDF($orientation, 'mm', 'A4', true, 'UTF-8', false);
				if($pdfname == 'printrosterpdf')
				{
					$orientation = 'L';
					$bottom_margin = '10';
					$format = "A4";
				}
				
				$pdf = new Pms_PDF($orientation, 'mm', $format, true, 'UTF-8', false);
				$pdf->SetMargins(10, 5, 10); //reset margins
				$pdf->setDefaults(true, $orientation, $bottom_margin); //defaults with header
				$pdf->setImageScale(1.6);
				$pdf->format = $format;
				//$pdf->setPrintFooter(false); // remove black line at bottom
		
				switch($pdfname)
				{	
					case "printrosterpdf" :
						$pdf->setPrintFooter(true);
						$pdf->SetMargins(10, 5, 10); //reset margins
						$pdf->footer_text = $this->view->translate("This plan was printed on"). " " . date("d.m.Y");
						$pdf->setFooterType('1 of n date');
						break;
					default:
						$background_type = false;
						$pdf->SetMargins(10, 5, 10); //reset margins
						$pdf->setPrintFooter(false); // remove black line at bottom
						break;
				}
		
				$pdf->HeaderText = false;
		
				if($background_type != false)
				{
					$bg_image = Pms_CommonData::getPdfBackground($clientinfo[0]['id'], $background_type);
					if($bg_image !== false)
					{
						$bg_image_path = PDFBG_PATH . '/' . $clientinfo[0]['id'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
						if(is_file($bg_image_path))
						{
							$pdf->setBackgroundImage($bg_image_path);
						}
					}
				}
		
				$html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
		
				$excluded_css_cleanup_pdfs = array(
						'printrosterpdf'
				);
		
				if(!in_array($pdfname, $excluded_css_cleanup_pdfs))
				{
					$html = preg_replace('/style=\"(.*)\"/i', '', $html);
				}
				//echo $html; exit;
				$pdf->setHTML($html);
				
				//upload pdf to ftp as foster file
				$pdf->toFTP($pdfname, "uploads", NULL, true);
				
		
				
				/* generate comment in verlauf when pdf generated
				if($pdfname == "printrosterpdf")
				{
					$cust = new PatientCourse ();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("K");
					$cust->course_title = Pms_CommonData::aesEncrypt(addslashes('' . $navnames [$pdfname] . ' wurde erstellt'));
					$cust->tabname = $form_tabname;
					$cust->tabname = Pms_CommonData::aesEncrypt('' . $form_tabname . '');
					$cust->recordid = $recordid;
					$cust->user_id = $logininfo->userid;
					$cust->save();
				}*/
				
		
				if($pdfname != "participationpolicy_save"){
					ob_end_clean();
					ob_start();
					$pdf->toBrowser($pdfname . '.pdf', "d");
					exit;
				}
			}
		

		
		
			}
			
			//get view list shifts
			public function shiftlistAction(){
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
			
				//populate the datatables
				if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
					
					$dty_roster = new Roster();
					$dty_roster_arr = $dty_roster->find_shift_data($clientid);
					
					$total_assigned_sf = array();
					foreach($dty_roster_arr as $k_roster => $v_droster)
					{
						$total_assigned_sf[] = $v_droster['shift'];
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
							"0" => "name",
							"1" => "start_alias",
							"2" => "end_alias"
			
					);
					$columns_search_array = array(
							"0" => "name",
							"1" => "start",
							"2" => "end"		
					);
					
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
					
					if($order_column == "1" || $order_column == "2")
					{
						$columns_array[$order_column] = "CONCAT(SUBSTRING(".$columns_array[$order_column].", 1, 2), SUBSTRING(".$columns_array[$order_column].", 4, 2))";
						$columns_array[$order_column] = "CONVERT(".$columns_array[$order_column]." , UNSIGNED)";
						$order_by_str = addslashes($columns_array[$order_column].' '.$order_dir.' ');
					}
					else
					{					
						$order_by_str ='CONVERT(CONVERT('.addslashes(htmlspecialchars($columns_array[$order_column])).' USING BINARY) USING utf8) '.$order_dir;
					}
					
					// ########################################
					// #####  Query for count ###############
					$fdocarray = array();
					$fdoc1 = Doctrine_Query::create();
					$fdoc1->select('count(*)');
					$fdoc1->from('ClientShifts');
					$fdoc1->where("client = ?", $clientid);
					$fdoc1->andWhere("isdelete = 0  ");
			
					$fdocarray = $fdoc1->fetchArray();
					if(empty($fdocarray)){
						$full_count  = 1;
					}
					else 
					{
						$full_count  = $fdocarray[0]['count'];
					}
			
					/* ------------- Search options ------------------------- 
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
						//$fdoc1->andWhere("(lower(name) like ? or  DATE_FORMAT(start,'%H:%i') like ? or  DATE_FORMAT(end,'%H:%i') like ?)",
						//		array("%" . trim($search_value) . "%","%" . trim($search_value) . "%","%" . trim($search_value) . "%"));
					}
					//print_r($regexp_arr);
					//echo  $fdoc1->getSqlQuery(); exit;
					$fdocarray = $fdoc1->fetchArray();
					$filter_count  = $fdocarray[0]['count'];*/
			
					// ########################################
					// #####  Query for details ###############
					//$fdoclimit = array();
					$fdoc1->select('*,  DATE_FORMAT(start,"%H:%i") as start_alias,  DATE_FORMAT(end,"%H:%i") as end_alias');
					
					$fdoc1->orderBy($order_by_str);
					
					$fdoclimit = $fdoc1->fetchArray();
			
					//$fdoclimit = Pms_CommonData::array_stripslashes($fdoc1->fetchArray());
					
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
					
					//add hardcoded old event
					if(empty($fdoclimit))
					{
						$fdoclimit[0]['id'] = '0';
						$fdoclimit[0]['name'] = "Dienstplan";
						$fdoclimit[0]['start_alias'] = "";
						$fdoclimit[0]['end_alias'] = "";
						$fdoclimit[0]['color'] = '98FB98';
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
			
						if(in_array($mdata['id'], $total_assigned_sf))
						{
							$resulted_data[$row_id]['name'] = sprintf($link,'<span>!</span>'.$mdata['name']);
							$del_sf = 'no';
						}
						else
						{
							$resulted_data[$row_id]['name'] = sprintf($link,$mdata['name']);
							$del_sf = 'yes';
						}
			
						$resulted_data[$row_id]['start'] = sprintf($link, $mdata['start_alias']);
						$resulted_data[$row_id]['end'] = sprintf($link, $mdata['end_alias']);
						$resulted_data[$row_id]['color'] = '<div class="icon_color_placeholder" style="width: 26px; height: 24px; background: #' . $mdata['color'] . '"></div>';
						
						if($del_sf == 'yes')
						{
							if($mdata['id'] == '0')
							{
								$resulted_data[$row_id]['actions'] = '<div class="nodel">-</div><div class="nodel">-</div>';
							}
							else 
							{
								$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'roster/editshift?sid='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" del="'.$ask_on_del.'" rel="'.$mdata['id'].'" id="delete_'.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>';
							}	
						}
						else 
						{
							$resulted_data[$row_id]['actions'] = '<a href="'.APP_BASE .'roster/editshift?sid='.$mdata['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><div class="nodel">-</div>';
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
		
			
			private function __updateBoxOrder($data = ['col', 'order'])
			{
			    if (empty($data['col'])) {
			        return;    
			    }
			    
			    $userid = $this->logininfo->userid;
			     
			    $delete = new BoxOrder();
			    $del = $delete->deleteOrder($userid, $data['col']);
			     
			    if ( ! empty($data['order'])) {
			        $boxOrder = [];
			        foreach($data['order'] as $position => $item)
			        {
			            $boxOrder[] = [
			                'userid'    => $userid,
			                'boxcol'    => $data['col'],
			                'boxid'     => $item,
			                'boxorder'  => $position,
			            ];
			        }
			
			        $obj = new Doctrine_Collection('BoxOrder');
			        $obj->fromArray($boxOrder);
			        $obj->save();
			    }
			}

	}

	?>