<?
class TabmenusController extends Zend_Controller_Action
{
	public function addmenuAction()
	{
		$cl = new Client();
		$clientarr = $cl->getClientData();
		$grid = new Pms_Grid($clientarr,1,count($clientarr),"listclientsformenus.html");
		$this->view->clientlist = $grid->renderGrid();
			
		if($this->getRequest()->isPost())
		{
			$menu_form = new Application_Form_TabMenus();
				
			if($menu_form->validate($_POST))
			{
				$menu = $menu_form->InsertData($_POST);
				$this->view->errror_message = "Menu Added Successfully";
				$this->_redirect(APP_BASE."tabmenus/menulist");
			}
			else
			{
				$menu_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
			
// 		$mn = new TabMenus();
// 		$this->view->menusdrop = $mn->getMenus();
		$a_mod= array();
		$this->getmodulehierarchy($a_mod,0,'');
		
		
		$menusdrop = array();
		$menusdrop = array("0" => "Select Parent");
		
		foreach($a_mod as $k=>$md)
		{
		    $menusdrop[$md['id']] = str_replace('&nbsp;'," - ",$md['space']).$md['menu_title'];
		}
		
		$this->view->menusdrop = $menusdrop; 
	}

	public function editmenuAction()
	{
		$this->_helper->viewRenderer('addmenu');
			
		$mn = new TabMenus();
		$this->view->menusdrop = $mn->getMenus();
			
		if($this->getRequest()->isPost())
		{
			$menu_form = new Application_Form_TabMenus();
			if($menu_form->validate($_POST))
			{
				$menu_form->UpdateData($_POST);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE."tabmenus/menulist");
			}
			else
			{
				$menu_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
			
		if(strlen($_GET['id'])>0)
		{
			$doc = Doctrine::getTable('TabMenus')->find($_GET['id']);
			$docarray = $doc->toArray();
			$this->retainValues($docarray);
			$fdoc = Doctrine_Query::create()
			->select('*')
			->from('TabMenuClient')
			->where('menu_id = ?', $_GET['id']);
			$mncd = $fdoc->execute();
			if($mncd)
			{
				$mcarr = $mncd->toArray();
					
				for($i=0;$i<count($mcarr);$i++)
				{
					$mncarr[] = $mcarr[$i]['clientid'];
				}

				$this->view->mncarr = $mncarr;
			}
		}
		$cl = new Client();
		$clientarr = $cl->getClientData();
		$grid = new Pms_Grid($clientarr,1,count($clientarr),"listclientsfortabmenus.html");
		$this->view->clientlist = $grid->renderGrid();
	}

	public function menulistAction()
	{
		$fdoc = Doctrine_Query::create()
		->select('*')
		->from('TabMenus')
		->where('isdelete = ?',0)
		->orderBy('sortorder ASC');
		$fdocexec = $fdoc->execute();
		$fdocarray = $fdocexec->toArray();
		$logininfo= new Zend_Session_Namespace('Login_Info');
	}

	public function fetchlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$columnarray = array("pk"=>"id","mn"=>"menu_title","lnk"=>"menu_link");
		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");

		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];

		$fdoc = Doctrine_Query::create()
		->select('count(*)')
		->from('TabMenus')
		->where('isdelete = ?',0)
		->andWhere('parent_id =0')
		->orderBy('sortorder ASC');
			
		$fdocexec = $fdoc->execute();
		$fdocarray = $fdocexec->toArray();
			
		$limit = 50;
		$fdoc->select('*');
		$fdoc->limit($limit);
		$fdoc->offset($_GET['pgno']*$limit);
			
		$fdoclimitexec = $fdoc->execute();
		$fdoclimit = $fdoclimitexec->toArray();
			
		$a_mod= array();
		$this->getmodulehierarchy($a_mod,0,'');

			
		$grid = new Pms_Grid($a_mod,1,$fdocarray[0]['count'],"listtabmenus.html");
		$this->view->menusgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("menusnavigation.html",5,$_GET['pgno'],$limit);

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['menuslist'] =$this->view->render('tabmenus/fetchlist.html');
			
		echo json_encode($response);
		exit;
	}

	public function sortingAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$menu_form = new Application_Form_TabMenus();
		$sort = $menu_form->sortMenus($_GET['sid'],$_GET['dir']);
		$this->_redirect(APP_BASE."tabmenus/menulist");
			
	}
		
	public function deletemenuAction()
	{
		$this->_helper->viewRenderer('menulist');
		$mn = Doctrine::getTable('TabMenus')->find($_GET['id']);
		$mn->isdelete = 1;
		$mn->save();
			
	}
		
		
	private function retainValues($values)
	{
		foreach($values as $key=>$val)
		{
			$this->view->$key = $val;
		}
	}
		

	private function getmodulehierarchy(&$a_mod,$parentid,$space)
	{
		$folder = Doctrine_Query::create()
		->select('*')
		->from('TabMenus')
		->where('parent_id = ?', $parentid)
		->andWhere('isdelete = ?',0)
		->orderBy("sortorder ASC");
			
		$folderexec = $folder->execute();
		$folderarray = $folderexec->toArray();
		foreach($folderarray as $key=>$val)
		{
			array_push($a_mod,array('space'=>$space,'menu_title'=>$val['menu_title'],'menu_link'=>$val['menu_link'],'id'=>$val['id']));
			$this->getmodulehierarchy($a_mod,$val['id'],$space."&nbsp;&nbsp;&nbsp;");
		}
			
		return ;

	}


}
?>