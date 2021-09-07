<?php

require_once("Pms/Form.php");

class Application_Form_InternalInvoicePriceList extends Pms_Form
{

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

		if($validate->isdate($post['start']) && $validate->isdate($post['end']))
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
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$p_list = new InternalInvoicePriceList();
		$p_list->clientid = $clientid;
		$p_list->price_sheet = $post_data['price_sheet'];
		$p_list->start = date('Y-m-d H:i:s', strtotime($post_data['start']));
		$p_list->end = date('Y-m-d H:i:s', strtotime($post_data['end']));
		$p_list->save();

		return $p_list->id;
	}

	public function edit_list($post_data, $list)
	{
		$edit_list = Doctrine_Core::getTable('InternalInvoicePriceList')->findOneById($list);
		$edit_list->price_sheet = $post_data['price_sheet'];
		$edit_list->start = date('Y-m-d H:i:s', strtotime($post_data['start']));
		$edit_list->end = date('Y-m-d H:i:s', strtotime($post_data['end']));
		$edit_list->save();

		return $edit_list->id;
	}

	public function delete_price_list($list)
	{
		$del_list = Doctrine_Core::getTable('InternalInvoicePriceList')->findOneById($list);
		$del_list->isdelete = '1';
		$del_list->save();
	}

	public function save_prices_groups($post_data, $list)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
	}

	public function save_prices_bonuses($post_data, $list)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
	}

	public function save_prices_actions($post_data, $list)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
	}

	public function add_pricelist_actions($action_id, $list)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$prices = new InternalInvoicePriceActions();
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