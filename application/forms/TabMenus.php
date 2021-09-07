<?php
require_once("Pms/Form.php");

class Application_Form_TabMenus extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['menu_title'])){
			$this->error_message['menu_title']=$Tr->translate('menu_error'); $error=1;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
			
		$tabsmenu = Doctrine_Query::create()
		->select('*')
		->from('TabMenus')
		->where('parent_id= ?', $post['parent_id'])
		->limit(1)
		->orderBy('sortorder DESC');
		$tabsmenu->getSqlQuery();
		$getlast = $tabsmenu->execute();
			
		if($getlast)
		{
			$lastarr = $getlast->toArray();
			$lastsort = $lastarr[0]['sortorder'];
		}



		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$cust = new TabMenus();
		$cust->menu_title = $post['menu_title'];
		$cust->parent_id = $post['parent_id'];
		$cust->menu_link = $post['menu_link'];
		$cust->sortorder = $lastsort+1;
		$cust->save();


		
		//save new menu into Links table - 2017.04 extra
		if (!empty($cust->id) && $cust->id > 0 && trim($cust->menu_link) !="") {
				
			$links_model =  new Links();
		
			if ( ! $links_model->assert_links_exists($cust->menu_link) ){
				//insert new link
				$links_model->menu = $cust->id;
				$links_model->master_link = $cust->parent_id;
				$links_model->link = $cust->menu_link;
				$links_model->issadmin = 0;
				$links_model->iscadmin = 0;
				$links_model->ispatientonly = 1;
				$links_model->save();
			}
		}
		
		
		if(count($post['clients']>0))
		{
			for($i=0;$i<count($post['clients']);$i++)
			{
				$mnc = new TabMenuClient();
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

		$cust = Doctrine::getTable('TabMenus')->find($_GET['id']);
		$cust->menu_title = $post['menu_title'];
		$cust->menu_link = $post['menu_link'];
		$cust->parent_id = $post['parent_id'];
		$cust->save();
			
		$q = Doctrine_Query::create()
		->delete('TabMenuClient')
		->where('menu_id= ?', $_GET['id']);
		$cs = $q->execute();
			
		if(count($post['clients']>0))
		{
			for($i=0;$i<count($post['clients']);$i++)
			{

				$mnc = new TabMenuClient();
				$mnc->menu_id = $_GET['id'];
				$mnc->clientid = $post['clients'][$i];
				$mnc->save();
			}
		}

	}

	public function sortMenus($mid,$srt)
	{
		$mn = Doctrine::getTable('TabMenus')->find($mid);
		$menuarr = $mn->toArray();

		if($srt=="up")
		{
			$whr = "sortorder < ".$menuarr['sortorder']."";
			$ord = "sortorder DESC";
		}
		if($srt=="dn")
		{
			$whr = "sortorder > ".$menuarr['sortorder']."";
	 	$ord = "sortorder ASC";
		}
			
		$fdoc = Doctrine_Query::create()
		->select('*')
		->from('TabMenus')
		->limit(1)
		->where("parent_id = '".$menuarr['parent_id']."'")
		->andWhere('id!='.$mid.'')
		->andWhere('isdelete=0')
		->andwhere($whr)
		->orderBy($ord);

		$srtmn = $fdoc->execute();
		$srtarr = $srtmn->toArray();
			
		if(isset($srtarr[0]['sortorder']) && isset($menuarr['sortorder']))
		{

			$cust2 = Doctrine::getTable('TabMenus')->find($mid);
			$cust2->sortorder =  $srtarr[0]['sortorder'];
			$cust2->save();


			$cust = Doctrine::getTable('TabMenus')->find($srtarr[0]['id']);
			$cust->sortorder = $menuarr['sortorder'];
			$cust->save();
		}
	}
}


?>