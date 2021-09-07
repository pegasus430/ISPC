<?php

require_once("Pms/Form.php");

class Application_Form_SocialCodePriceListGroups extends Pms_Form {

	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$validate = new Pms_Validation();

		if($validate->name($post['name']))
		{
			$this->error_message['name'] = $Tr->translate('group_name_required');
		}
		else
		{
			return false;
		}
	}

	public function save_price_list_group($post_data)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$p_list = new SocialCodePriceListGroups();
		$p_list->client = $clientid;
		$p_list->private = $post_data['private'];
		$p_list->name = $post_data['name'];
		$p_list->save();

		return $p_list->id;
	}

	public function edit_price_list_group($post_data, $group)
	{
		$edit_list = Doctrine_Core::getTable('SocialCodePriceListGroups')->findOneById($group);
		$edit_list->name = $post_data['name'];
		$edit_list->private = $post_data['private'];
		$edit_list->save();

		return $edit_list->id;
	}

	public function delete_price_list_group($group)
	{
		$del_list = Doctrine_Core::getTable('SocialCodePriceListGroups')->findOneById($group);
		$del_list->isdelete = '1';
		$del_list->save();
	}

}

?>