<?php
require_once("Pms/Form.php");
class Application_Form_Icons extends Pms_Form
{

	public function validate ( $post )
	{
		$Tr = new Zend_View_Helper_Translate();
		$error = 0;
		$val = new Pms_Validation();
		if (!$val->isstring($post['name']))
		{
			$this->error_message['name'] = $Tr->translate('icon_name_error');
			$error = 1;
		}


		if ($error == 0)
		{
			return true;
		}

		return false;
	}
	public function validatesystemicon ( $post )
	{
		$Tr = new Zend_View_Helper_Translate();
		$error = 0;
		$val = new Pms_Validation();
		if (!$val->isstring($post['icon_color']))
		{
			$this->error_message['icon_color'] = $Tr->translate('icon_color_error');
			$error = 1;
		}


		if ($error == 0)
		{
			return true;
		}

		return false;
	}

	public function insert_icon_data ( $post, $type = false )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$res = new IconsClient();
		$res->client_id = $clientid;
		$res->name = $post['name'];
		$res->color = $post['icon_color'];
		$res->image = $post['filename'];
		if($type){
    		$res->type = $type;
		} else{
    		$res->type = 'patient';
		}
		$res->isdelete = '0';
		$res->save();

		if(!empty($_SESSION['filename']))
		{
		    if($type){
	   	       $this->move_uploaded_icon($res->id,$type);
		    } else{
    		    $this->move_uploaded_icon($res->id);
		    }
		}
	}

	public function delete_client_icon ( $clientid, $icon_id )
	{
		$cust = Doctrine::getTable('IconsClient')->findOneByIdAndClientId($icon_id, $clientid);
		if ($cust)
		{
			$cust->isdelete = '1';
			$cust->save();
		}

		//ISPC-2396 Carmen 08.10.2019 
		//delete the icon from all other patients
		/* $q = Doctrine_Query::create()
		->delete()
		->from('IconsPatient')
		->where("icon_id = '".$icon_id."'"); 

		$r = $q->execute(); */
		
		$delentity = IconsPatientTable::deleteAllIconsPatientsByIpidandIconId(null, $icon_id);
	}

	public function update_icon_data ( $clientid = 0, $post = array(), $type = null)
	{
		$cust = Doctrine::getTable('IconsClient')->findOneByIdAndClientId($_REQUEST['id'], $clientid);
		if ($cust)
		{
			$cust->name = $post['name'];
			$cust->color = $post['icon_color'];
			$cust->save();

			if($post['fileuploads'] == '1')
			{
				if (!$_SESSION['filename'])
				{
					$cust->image = '';
				}
				else
				{
					$this->move_uploaded_icon($_REQUEST['id'],$type);
				}
			} else {
				//cleanup uploaded file if change image is not checked
				unlink('icons_system/'.$post['filename']);
			}
			$cust->save();
		}
	}

	public function update_system_icon($clientid, $post)
	{
		$sys_icons = new IconsMaster();
		$system_icons = $sys_icons->get_system_icons($clientid, $_REQUEST['sid'], false, false);

		//edit
		if(!empty($_REQUEST['cid']) && !empty($_REQUEST['sid']))
		{
			$cust_edit = Doctrine::getTable('IconsClient')->findOneByIdAndIconIdAndClientId($_REQUEST['cid'], $_REQUEST['sid'] ,$clientid);
			$cust_edit->color = $post['icon_color'];
			if (!empty($post['icon_settings'])) {
				$cust_edit->icon_settings = json_encode($post['icon_settings']);
			}
			$cust_edit->save();
			if($post['fileuploads'] == '1')
			{
				if (!$_SESSION['filename'])
				{
					$cust_edit->image = '';
				}
				else
				{
					$this->move_uploaded_system_icon($_REQUEST['cid'], $system_icons[$_REQUEST['sid']]['name']);
				}
			} else {
				//cleanup uploaded file if change image is not checked
				unlink('icons_system/'.$post['filename']);
			}
			$cust_edit->save();
		}
		else if(empty($_REQUEST['cid']))
		{ //insert new custom sys icon
			$res = new IconsClient();
			$res->icon_id = $_REQUEST['sid'];
			$res->client_id = $clientid;
			$res->color = $post['icon_color'];
			$res->image = $post['filename'];
			$res->isdelete = '0';
			
			if (!empty($post['icon_settings'])) {
				$res->icon_settings = json_encode($post['icon_settings']);
			}
			
			$res->save();

			if(!empty($_SESSION['filename']))
			{
				$this->move_uploaded_system_icon($res->id, $system_icons[$_REQUEST['sid']]['name']);
			}
			else
			{
				$this->move_uploaded_system_icon($res->id, $system_icons[$_REQUEST['sid']]['name'], true, $system_icons[$_REQUEST['sid']]['image']);
			}
		}
	}

	public function assign_patient_icon($ipid, $custom_icon)
	{
		$cust_icons = new IconsPatient();
		$cust_icons->ipid = $ipid;
		$cust_icons->icon_id = $custom_icon;
		$cust_icons->save();
	}

	public function remove_patient_icon($ipid, $custom_icon)
	{
		//ISPC-2396 Carmen 08.10.2019
		/* $q = Doctrine_Query::create()
		->delete()
		->from('IconsPatient')
		->where("icon_id = '".$custom_icon."'")
		->andWhere("ipid LIKE '".$ipid."'");

		$r = $q->execute(); */
		$delentity = IconsPatientTable::deleteAllIconsPatientsByIpidandIconId($ipid, $custom_icon);
	}

	public function assign_vw_icon($vw_id, $custom_icon, $clientid)
	{
		$cust_icons = new VoluntaryworkersIcons();
		$cust_icons->clientid = $clientid;
		$cust_icons->vw_id = $vw_id;
		$cust_icons->icon_id = $custom_icon;
		$cust_icons->save();
	}

	
	public function remove_vw_icon($vw_id, $custom_icon)
	{
		/*
		$q = Doctrine_Query::create()
		->delete()
		->from('VoluntaryworkersIcons')
		->where("icon_id = '".$custom_icon."'")
		->andWhere("vw_id = '".$vw_id."'");
		*/
		$q = Doctrine_Query::create()
		->update(VoluntaryworkersIcons)
		->set('isdelete', "'1'")
		->where("icon_id = '".$custom_icon."'")
		->andWhere("vw_id = '".$vw_id."'");

		$r = $q->execute();
	}


	
	private function move_uploaded_icon($inserted_icon_id, $type = false)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		//move icon file to desired destination /public/icons/clientid/icon_db_id.ext
		$icon_upload_path = 'icons_system/' . $_SESSION['filename'];
		
		if($type){
            $icon_new_path = 'icons_system/' . $clientid . '/'.$type.'/' . $inserted_icon_id . '.' . $_SESSION['filetype'];
		} else {
    		$icon_new_path = 'icons_system/' . $clientid . '/' . $inserted_icon_id . '.' . $_SESSION['filetype'];
		    
		}

		copy($icon_upload_path, $icon_new_path);
		unlink($icon_upload_path);

		$update = Doctrine::getTable('IconsClient')->find($inserted_icon_id);
		if($type){
	       	$update->image = $clientid. '/' .$type. '/' . $inserted_icon_id . '.' . $_SESSION['filetype'];
		} else{
    		$update->image = $clientid . '/' . $inserted_icon_id . '.' . $_SESSION['filetype'];
		}
		$update->save();
	}
	
	
	private function move_uploaded_system_icon($inserted_icon_id, $icon_name, $copy_default = false, $default_image = false)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		if($copy_default === false)
		{
			//move icon file to desired destination /public/icons/clientid/icon_db_id.ext
			$icon_upload_path = 'icons_system/' . $_SESSION['filename'];
			$icon_new_path = 'icons_system/' . $clientid . '/' . $icon_name . '.' . $_SESSION['filetype'];
			$icon_string = $clientid . '/' . $icon_name . '.' . $_SESSION['filetype'];

			copy($icon_upload_path, $icon_new_path);
			unlink($icon_upload_path);
		}
		else
		{

			//move icon file to desired destination /public/icons/clientid/icon_db_id.ext
			$icon_upload_path = 'icons_system/' . $default_image;
			$icon_new_path = 'icons_system/' . $clientid . '/' . $default_image;
			$icon_string = $clientid . '/' . $default_image;

			while (!is_dir('icons_system/' . $clientid))
			{
				mkdir('icons_system/' . $clientid);
				if ($i >= 50)
				{
					exit; //failsafe
				}
				$i++;
			}

			copy($icon_upload_path, $icon_new_path);
		}



		$update = Doctrine::getTable('IconsClient')->find($inserted_icon_id);
		$update->image = $icon_string;
		$update->save();
	}

	public function update_traffic_icons($clientid, $post)
	{
		$sys_icons = new IconsMaster();
		$system_icons = $sys_icons->get_system_icons($clientid, $_REQUEST['sid'], false, false);

		//edit
		if(!empty($_REQUEST['cid']) && !empty($_REQUEST['sid']))
		{
			$cust_edit = Doctrine::getTable('IconsClient')->findOneByIdAndIconIdAndClientId($_REQUEST['cid'], $_REQUEST['sid'] ,$clientid);
			$cust_edit->color = $post['icon_color'];
			$cust_edit->save();
			if($post['fileuploads'] == '1')
			{
				if (!$_SESSION['filename'])
				{
					$cust_edit->image = '';
				}
				else
				{
					$cust_edit->image = $post['filename'];
				}
			} else {
				//cleanup uploaded file if change image is not checked
				unlink('icons_system/'.$post['filename']);
			}
			$cust_edit->save();
		}
		else if(empty($_REQUEST['cid']))
		{ //insert new custom sys icon
			$res = new IconsClient();
			$res->icon_id = $_REQUEST['sid'];
			$res->client_id = $clientid;
			$res->color = $post['icon_color'];
			$res->image = $post['filename'];
			$res->isdelete = '0';
			$res->save();
		}
	}

}
?>