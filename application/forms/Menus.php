<?php

require_once("Pms/Form.php");

class Application_Form_Menus extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['menu_title'])){
			$this->error_message['menu_title']=$Tr->translate('menu_error'); $error=1;
		}
		if(!$val->isstring($post['left_position'])){
			if(!$val->isstring($post['top_position'])){
				$this->error_message['menu_position']=$Tr->translate('position_error'); $error=1;
			}
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		if($post['top_position']==1)
		{
			$where = " and top_position=1";
		}
		if($post['left_position']==1)
		{
			$where = " and left_position=1";
		}
		$fdoc = Doctrine_Query::create()
		->select('*')
		->from('Menus')
		->limit(1);

		if($post['top_position']==1)
		{
			$fdoc->where("parent_id=".$post['parent_id']." and top_position=1");
			$fdoc->orderBy('sortorder_top DESC');
				
				
			$fdocexec = $fdoc->execute();
			$topmenuarray = $fdocexec->toArray();
		}

		if($post['left_position']==1)
		{
			$fdoc->where("parent_id=".$post['parent_id']." and left_position=1");
			$fdoc->orderBy('sortorder DESC');
			$fdocexec = $fdoc->execute();
			$leftmenuarray = $fdocexec->toArray();
		}
			
			

		if(count($topmenuarray)<1)
		{
			$sortorder_top = 0;
		}elseif($post['top_position']==1)
		{
			$sortorder_top = $topmenuarray[0]['sortorder_top'];
		}

		if(count($leftmenuarray)<1)
		{
			$sortorder = 0;
		}elseif($post['left_position']==1)
		{
			$sortorder = $leftmenuarray[0]['sortorder'];
		}
			


		$cust = new Menus();
		$cust->menu_title = $post['menu_title'];
		$cust->parent_id = $post['parent_id'];
		$cust->menu_link = $post['menu_link'];		
		$cust->left_position = $post['left_position'];
		$cust->top_position = $post['top_position'];
		//ISPC-2782 CRISTI.C		
		$cust->menu_info = $post['menu_info'];
		$cust->group_menu_id = $post['group_menu_id'];
		//
		$cust->foradmin = $post['foradmin'];
		$cust->forsuperadmin = $post['forsuperadmin'];
		if($post['top_position']==1)
		{
			$cust->sortorder_top = $sortorder_top+1;
		}
		if($post['left_position']==1)
		{
			$cust->sortorder = $sortorder+1;
		}
		$cust->openin = $post['openin'];
		$cust->save();

		
		//save new menu into Links table - 2017.04 extra
		if (!empty($cust->id) && $cust->id > 0 && trim($cust->menu_link) !="") {
			
			$links_model =  new Links();
		
			if ( ! $links_model->assert_links_exists($cust->menu_link) ){
				//insert new link
				$links_model->menu = $cust->id;
				$links_model->master_link = $cust->parent_id;
				$links_model->link = $cust->menu_link;
				//ISPC-2782 CRISTI.C
				//$links_model->menu_info = $cust->menu_info;
				//$links_model->group_menu_id = $post['group_menu_id'];
				//
				$links_model->issadmin = (int)$cust->forsuperadmin;
				$links_model->iscadmin = (int)$cust->foradmin;
				$links_model->ispatientonly = 0;				
				$links_model->save();			
			}
		}
	

		if(count($post['clients']>0))
		{
			for($i=0;$i<count($post['clients']);$i++)
			{
				$mnc = new MenuClient();
				$mnc->menu_id = $cust->id;
				$mnc->clientid = $post['clients'][$i];
				$mnc->save();
			}
		}
		return $cust;
	}

	public function UpdateData($post)
	{
			
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$cust = Doctrine::getTable('Menus')->find($_GET['id']);
		if ($cust) {
			$original_menu_link = $cust->menu_link;
		}
		$cust->menu_title = $post['menu_title'];
		$cust->left_position = $post['left_position'];
		$cust->top_position = $post['top_position'];
		$cust->menu_link = $post['menu_link'];
		//ISPC-2782 CRISTI.C
		$cust->menu_info = $post['menu_info'];
		$cust->group_menu_id = $post['group_menu_id'];
		//
		$cust->parent_id = $post['parent_id'];
		$cust->foradmin = $post['foradmin'];
		$cust->forsuperadmin = $post['forsuperadmin'];
		$cust->openin = $post['openin'];
		$cust->save();
			
		$q = Doctrine_Query::create()
		->delete('MenuClient')
		->where('menu_id= ?', $_GET['id']);
		$cs = $q->execute();
			
		if(count($post['clients']>0))
		{
			for($i=0;$i<count($post['clients']);$i++)
			{

				$mnc = new MenuClient();
				$mnc->menu_id = $_GET['id'];
				$mnc->clientid = $post['clients'][$i];
				$mnc->save();
			}
		}
		
		
		
		//save/update new menu into Links table - 2017.05 extra
		if ($cust && !empty($cust->id) && $cust->id > 0 && trim($cust->menu_link) != "") {
				
			$links_model =  new Links();
		
			if ( ! $links_model->assert_links_exists($original_menu_link , $cust->id) ){
				//insert new link
				$links_model->menu = $cust->id;
				$links_model->master_link = $cust->parent_id;
				$links_model->link = $cust->menu_link;
				//ISPC-2782 CRISTI.C
				$cust->menu_info = $post['menu_info'];
				$cust->group_menu_id = $post['group_menu_id'];
				//
				$links_model->issadmin = (int)$cust->forsuperadmin;
				$links_model->iscadmin = (int)$cust->foradmin;
				$links_model->ispatientonly = 0;
				$links_model->save();
			} else {
				//update older values
				$links_update = Doctrine::getTable('Links')->findOneByLinkAndMenu($original_menu_link , $cust->id );
				if ($links_update) {
					$links_update->menu = $cust->id;
					$links_update->master_link = $cust->parent_id;
					$links_update->link = $cust->menu_link;
					//ISPC-2782 CRISTI.C
					$cust->menu_info = $post['menu_info'];
					$cust->group_menu_id = $post['group_menu_id'];
					//
					$links_update->issadmin = (int)$cust->forsuperadmin;
					$links_update->iscadmin = (int)$cust->foradmin;
					$links_update->ispatientonly = 0;
					$links_update->save();
				}
			}
		}

	}
	 
	public function sortMenus($mid,$srt)
	{
		$mn = Doctrine::getTable('Menus')->find($mid);
		$menuarr = $mn->toArray();

		if($_GET['pos']=="top")
		{
			$sorder = "sortorder_top";
			$poscond = "top_position = 1";
			$parentcond = "1";
		}
		else
		{
	 	$sorder = "sortorder";
	 	$poscond = "left_position = 1";
	 	$parentcond = 'parent_id='.$menuarr['parent_id'].'';
		}

		if($srt=="up")
		{
			$whr = $sorder."<".$menuarr[$sorder]."";
			$ord = $sorder.' DESC';
		}
		if($srt=="dn")
		{
			$whr = $sorder.">".$menuarr[$sorder]."";
	 	$ord = $sorder.' ASC';
		}
		 
		 
		$fdoc = Doctrine_Query::create()
		->select('*')
		->from('Menus')
		->limit(1)
		->where($parentcond)
		->andWhere('id!='.$mid.'')
		->andWhere($whr)
		->andWhere($poscond)
		->orderBy($ord);

		$srtmn = $fdoc->execute();
		$srtarr = $srtmn->toArray();


			
		if(isset($srtarr[0][$sorder]) && isset($menuarr[$sorder]))
		{

			$cust2 = Doctrine::getTable('Menus')->find($mid);
			$cust2->$sorder =  $srtarr[0][$sorder];
			$cust2->save();
				

			$cust = Doctrine::getTable('Menus')->find($srtarr[0]['id']);
			$cust->$sorder = $menuarr[$sorder];
			$cust->save();
		}
			

	}
		
}


?>