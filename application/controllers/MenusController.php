<?php

class MenusController extends Zend_Controller_Action
{

	public function addmenuAction()
	{
			
		$cl = new Client();
		$clientarr = $cl->getClientData();
		$grid = new Pms_Grid($clientarr,1,count($clientarr),"listclientsformenus.html");
		$this->view->clientlist = $grid->renderGrid();
			
			
		if($this->getRequest()->isPost())
		{
			$menu_form = new Application_Form_Menus();

			if($menu_form->validate($_POST))
			{
				$menu = $menu_form->InsertData($_POST);
				$this->view->errror_message = "Menu Added Successfully";
				$this->_redirect(APP_BASE."menus/menulist");
			}
			else
			{
				$menu_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
			
		$mn = new Menus();
		$this->view->menuposition  = $mn->getPosition();
		$this->view->openins  = $mn->getWinOption();
		$this->view->menusdrop = $mn->getMenus();
		//ISPC-2782 CRISTI C. 27.01.2021
		$this->view->getgroups = MenuGroups::getGroups();
	}

	public function editmenuAction()
	{
			
		$this->_helper->viewRenderer('addmenu');
			
		$mn = new Menus();
		$this->view->menuposition  = $mn->getPosition();
		$this->view->openins  = $mn->getWinOption();
		$this->view->menusdrop = $mn->getMenus();
		//ISPC-2782 CRISTI C. 27.01.2021
		$this->view->getgroups = MenuGroups:: getGroups();
		if($this->getRequest()->isPost())
		{

			$menu_form = new Application_Form_Menus();

			if($menu_form->validate($_POST))
			{
				$menu_form->UpdateData($_POST);
				$this->view->error_message = $this->view->translate("recordupdatedsucessfully");
				$this->_redirect(APP_BASE."menus/menulist");
			}else
			{
				$menu_form->assignErrorMessages();
				$this->retainValues($_POST);
			}
		}
			
		if(strlen($_GET['id'])>0)
		{
			$doc = Doctrine::getTable('Menus')->find($_GET['id']);
			$docarray = $doc->toArray();
			$this->retainValues($docarray);
			if($docarray['left_position']==1)
			{
				$this->view->leftcheck = 'checked="checked"';
			}
			if($docarray['top_position']==1)
			{
				$this->view->topcheck = 'checked="checked"';
			}

			$fdoc = Doctrine_Query::create()
			->select('*')
			->from('MenuClient')
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
		$grid = new Pms_Grid($clientarr,1,count($clientarr),"listclientsformenus.html");
		$this->view->clientlist = $grid->renderGrid();
			
	}

	public function menulistAction()
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('menus',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}
	}

	public function fetchlistAction()
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$previleges = new Pms_Acl_Assertion();
		$return = $previleges->checkPrevilege('menus',$logininfo->userid,'canview');

		if(!$return)
		{
			$this->_redirect(APP_BASE."error/previlege");
		}


		$columnarray = array("pk"=>"id","mn"=>"menu_title","lnk"=>"menu_link");
		$orderarray = array("ASC"=>"DESC","DESC"=>"ASC");

		$this->view->order = $orderarray[$_GET['ord']];
		$this->view->{$_GET['clm']."order"} = $orderarray[$_GET['ord']];

		$fdoc = Doctrine_Query::create()
		->select('count(*)')
		->from('Menus')
		->where('isdelete = ?',0)
		->andWhere('left_position = 1')
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

			
		$grid = new Pms_Grid($a_mod,1,$fdocarray[0]['count'],"listmenus.html");
		$this->view->menusgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("menusnavigation.html",5,$_GET['pgno'],$limit);
			
			
		/////////////////////////////////////////
		$fdoc = Doctrine_Query::create()
		->select('count(*)')
		->from('Menus')
		->where('isdelete = ?',0)
		->andWhere('top_position = 1')
		//->andWhere('parent_id = 0')
		->orderBy('sortorder_top ASC');
			
		$fd = $fdoc->execute();
		$fdarray = $fd->toArray();
			
		$limit = 50;
		$fdoc->select('*');
		$fdoc->limit($limit);
		$fdoc->offset($_GET['pgno']*$limit);
			
		$fdlimitexec = $fdoc->execute();
		$fdlimit = $fdlimitexec->toArray();

		$grid = new Pms_Grid($fdlimit,1,$fdarray[0]['count'],"listtopmenus.html");
		$this->view->topmenusgrid = $grid->renderGrid();
		$this->view->navigation = $grid->dotnavigation("menusnavigation.html",5,$_GET['pgno'],$limit);
		///////////////////////////////////////////

		$response['msg'] = "Success";
		$response['error'] = "";
		$response['callBack'] = "callBack";
		$response['callBackParameters'] = array();
		$response['callBackParameters']['menuslist'] =$this->view->render('menus/fetchlist.html');
			
		echo json_encode($response);
		exit;
	}

	public function sortingAction()
	{
			
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$menu_form = new Application_Form_Menus();
		$sort = $menu_form->sortMenus($_GET['sid'],$_GET['dir']);

		$this->_redirect(APP_BASE."menus/menulist");
			
	}


	/*
	 * @cla on 24.04.2018 + on delete , rename the menu from Links by appending  _isDeleted_
	 * link is unique, so after delete you could not re-add the same link
	 */
	public function deletemenuAction()
	{
		$this->_helper->viewRenderer('menulist');

		$mn = Doctrine::getTable('Menus')->find($_GET['id']);
		$mn->isdelete = 1;
		$mn->save();
		
		
		$link = $mn->menu_link;
		
		
		$folder = Doctrine_Query::create()
		->update('Links')
		->set('link', '?' , "_isDeleted_" . $link)
		->where('menu = ? ' , $_GET['id'])
		->andWhere('link = ? ' , $link)
		->execute();
		
		$this->_redirect(APP_BASE."menus/menulist");
			
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
		->from('Menus')
		->where('parent_id='.$parentid)
		->andWhere('isdelete = ?',0)
		->andWhere('left_position = 1')
		->orderBy("sortorder",'ASC');
		$folderexec = $folder->execute();
		$folderarray = $folderexec->toArray();

		foreach($folderarray as $key=>$val)
		{
			array_push($a_mod,array('space'=>$space,'menu_title'=>$val['menu_title'],'menu_link'=>$val['menu_link'],'id'=>$val['id']));
			$this->getmodulehierarchy($a_mod,$val['id'],$space."&nbsp;&nbsp;&nbsp;");
		}
			
		return ;

	}
	
	
	//ISPC-2782
	public function submenuspageAction(){
	    $logininfo= new Zend_Session_Namespace('Login_Info');
	    
	    if($logininfo->usertype == 'SA') {
	        $isadmin = 1;
	    } elseif($logininfo->usertype == 'CA' || $logininfo->usertype == 'SCA') {
	        $isadmin = 2;
	    } else {
	        $isadmin = 0;
	    }
	    
	    $menu_id = 30; // At the momment this is hardcoded to be listed ONLY  for menu id 30 (Mandanten- Einstellungen)  
	    
// 	    $menuarr    = Menus::getLeftParentMenus($isadmin);
	    $submenuarr = Menus::getAllLeftSubMenus();
	    $available_menus = array();
	    if(!empty($submenuarr[$menu_id])){
	        $available_menus = $submenuarr[$menu_id];
	    }

	    $groupped_menus = array();
	    foreach($available_menus as $k => $smenu){
	        if(!isset($smenu['group_menu_id'])){
	            $smenu['group_menu_id'] = 0 ;
	        }
	        if($smenu['parent_id'] == $menu_id){
	            $groupped_menus[$smenu['group_menu_id']][] = $smenu;
	        }
	    }
// 	    dd($groupped_menus);
	    $menus_groups_info = array("0"=>"Default");
	    $this->view->menus_groups_info = $menus_groups_info;
	    ksort($groupped_menus);
	    $this->view->groupped_menus = $groupped_menus;
	    
	    $menu_groups = MenuGroups::getGroups(true);
	    $menu_groups[0]['menu_group_name'] = "Mandanten-Einstellungen";
	    $this->view->menu_groups = $menu_groups;
	    
	}
	
	
	//ISPC-2782 Ancuta 26.01.2021
	public function menu2iconAction(){
	    exit;
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    $filename_falls = PUBLIC_PATH ."/import/menu2icons.csv";
	    $handle_fall = fopen($filename_falls, "r");
	    $delimiter_falls = ";";
	    //parse csv into an array
	    while(($data_falls = fgetcsv($handle_fall, NULL, $delimiter_falls)) !== FALSE)
	    {
	        foreach($data_falls as $k_data => $v_data)
	        {
	            $data_falls[$k_data] = htmlspecialchars($v_data);
	        }
	        
	        
	        $csv_data[] = $data_falls;
	    }
	    
	    foreach($csv_data as $csv_k => $csv_row){
	        $menu_link = $csv_row['2'];
	        $menu_icon = $csv_row['1'];
	        $menu_q= Doctrine_Query::create()
	        ->select('*')
	        ->from('Menus')
	        ->where('menu_link =?',$menu_link)
	        ->andwhere('isdelete = 0');
	        $menu_arr = $menu_q->fetchArray();
	        if( !empty($menu_arr)){
	            foreach($menu_arr as $k=>$menu_item){
	                
	                $data = Doctrine::getTable('Menus')->find($menu_item['id']);
	                if ($data instanceof Menus) {
	                    $data->menu_icon = trim($menu_icon);
	                    $data->save();
	                }
	                
	            }
	        }
	        
	    }
	    
	    fclose($handle_fall);
	    
	    $menu_q= Doctrine_Query::create()
	    ->select('*')
	    ->from('Menus')
	    ->where('isdelete = 0');
	    $menu_arr = $menu_q->fetchArray();
	    
	    $table_html = $this->view->tabulate($menu_arr);
	    
	    echo $table_html;
	    exit;
	}
	//ISPC-2782 Ancuta 26.01.2021
	public function menugroupsAction(){
	
	    if($this->getRequest()->isPost())
	    {
	        $post = $_POST;
	        $cust = new MenuGroups();
	        $cust->menu_group_name = $post['menu_group_name'];
	        $cust->info = $post['info'];
	        $cust->save();
	    }
	    
	}
	//ISPC-2782 Ancuta 26.01.2021
	public function menu2menugroupsAction(){
	    $menu_id = 30;
	    $submenuarr = Menus::getAllLeftSubMenus();
	    $available_menus = array();
	    if(!empty($submenuarr[$menu_id])){
	        $available_menus = $submenuarr[$menu_id];
	    }
	    $this->view->available_menus = $available_menus;
	    $menu_groups = MenuGroups::getGroups(true);
	    $menu_groups_array[0] ="----";
	    foreach($menu_groups as $k=>$mg){
	        $menu_groups_array[$mg['id']] = $mg['menu_group_name'];
	    }
	    $this->view->menu_groups = $menu_groups_array;
	    
// 	    dd($menu_groups,$available_menus);
	    if($this->getRequest()->isPost())
	    {
	        foreach($_POST['m2mg'] as $menu_id=>$mdata){
	            $setGroups= Doctrine_Query::create()
	            ->update('Menus')
	            ->set('group_menu_id', '?' ,$mdata['group_menu_id'])
	            ->where('id = ? ' , $menu_id)
	            ->andWhere('menu_link = ? ' , $mdata['menu_link'])
	            ->execute();
	        }

	        $this->_redirect(APP_BASE."menus/menu2menugroups?msg=Success");
	    }
	}
	
}
?>