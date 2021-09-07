<?php

require_once("Pms/Form.php");

class Application_Form_SocialCodePriceList extends Pms_Form {

	public function validate_period($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$validate = new Pms_Validation();

		if(!$validate->isdate($post['start']))
		{
			$this->error_message['date'] = $Tr->translate('invalid_start_date');
		}
		else if(!$validate->isdate($post['end']))
		{
			$this->error_message['date'] = $Tr->translate('invalid_end_date');
		}
			
		if(empty($post['group']))
		{
			$this->error_message['group'] = $Tr->translate('select_group_err');
		}

		if($validate->isdate($post['start']) && $validate->isdate($post['end']) && !empty($post['group']))
		{
			$start = strtotime($post['start']);
			$end = strtotime($post['end']);

			if($start <= $end)
			{

				return true;
			}
			else
			{
				$this->error_message['date'] = $Tr->translate('invalid_date_period');
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	public function save_price_list($post_data)
	{
		$pl_grouped = new Application_Form_SocialCodePriceListGrouped();
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$p_list = new SocialCodePriceList();
		$p_list->clientid = $clientid;
		$p_list->price_sheet = $post_data['price_sheet'];
		$p_list->start = date('Y-m-d H:i:s', strtotime($post_data['start']));
		$p_list->end = date('Y-m-d H:i:s', strtotime($post_data['end']));
		$p_list->private = $post_data['private'];
		$p_list->save();

		$inserted_id = $p_list->id;

		//add pricelist group to socialcode_price_list_grouped
		$pl_grouped->insert_pricelist_grouped($inserted_id, $post_data['group']);


		return $inserted_id;
	}

	public function edit_list($post_data, $list)
	{
		$pl_grouped = new Application_Form_SocialCodePriceListGrouped();

		//update it
		if(empty($post_data['group']))
		{
			$group = '0';
		}
		else
		{
			$group = $post_data['group'];
		}
			

		$edit_list = Doctrine_Core::getTable('SocialCodePriceList')->findOneById($list);
		$edit_list->price_sheet = $post_data['price_sheet'];
		$edit_list->start = date('Y-m-d H:i:s', strtotime($post_data['start']));
		$edit_list->end = date('Y-m-d H:i:s', strtotime($post_data['end']));
		$edit_list->private = $post_data['private'];
		$edit_list->save();
			
		//add pricelist group to socialcode_price_list_grouped
		$pl_grouped->update_pricelist_grouped($list, $group);

		return $edit_list->id;
	}

	public function delete_price_list($list)
	{
		$del_list = Doctrine_Core::getTable('SocialCodePriceList')->findOneById($list);
		$del_list->isdelete = '1';
		$del_list->save();
	}

	public function save_prices_groups($post_data, $list)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$this->clear_entries($clientid, $list, 'SocialCodePriceGroups');


		foreach($post_data as $group_id => $group_price)
		{
			$prices = new SocialCodePriceGroups();
			$prices->list = $list;
			$prices->clientid = $clientid;
			$prices->groupid = $group_id;
			$prices->price = Pms_CommonData::str2num($group_price['price']);
			$prices->save();
		}
	}

	public function save_prices_bonuses($post_data, $list)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$this->clear_entries($clientid, $list, 'SocialCodePriceBonuses');


		foreach($post_data as $bonus_id => $bonus_price)
		{
			$prices = new SocialCodePriceBonuses();
			$prices->list = $list;
			$prices->clientid = $clientid;
			$prices->bonusid = $bonus_id;
			$prices->price = Pms_CommonData::str2num($bonus_price['price']);
			$prices->save();
		}
	}

	public function save_prices_actions($post_data, $list)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$this->clear_entries($clientid, $list, 'SocialCodePriceActions');

		foreach($post_data as $k_shortcut => $shortcut_data)
		{
			$prices = new SocialCodePriceActions();
			$prices->list = $list;
			$prices->clientid = $clientid;
			$prices->actionid = $shortcut_data;
			$prices->save();
		}
	}

	public function add_pricelist_actions($action_id, $list)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$prices = new SocialCodePriceActions();
		$prices->list = $list;
		$prices->clientid = $clientid;
		$prices->actionid = $action_id;
		$prices->save();
	}

	private function clear_entries($clientid = false, $list = false, $table = false)
	{
		if($clientid && $table && $list)
		{
			$Q = Doctrine_Query::create()
			->delete($table)
			->where("clientid='" . $clientid . "'")
			->andWhere('list = "' . $list . '"');
			$Q->execute();
		}
	}

}

?>