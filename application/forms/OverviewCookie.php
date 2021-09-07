<?php

class Application_Form_OverviewCookie extends Pms_Form {

	public function InsertData($post) {

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;

		if($userid > 0)
		{
			$drop = Doctrine_Query::create()
			->select("*")
			->from('OverviewCookie')
			->where("userid= ? ", $userid)
			->andWhere("page_name= ? ", $post['page_name']);
			$retainarr = $drop->fetchArray();

			if(count($retainarr) > 0)
			{
				$frm = Doctrine::getTable('OverviewCookie')->find($retainarr[0]['id']);
				$frm->page_name = $post['page_name'];
				$frm->cookie = $post['cookie'];
				$frm->useroption = $post['useroption'];
				$frm->save();
			}
			else
			{
				$frm = new OverviewCookie();
				$frm->userid = $userid;
				$frm->page_name = $post['page_name'];
				$frm->cookie = $post['cookie'];
				$frm->useroption = $post['useroption'];
				$frm->save();
			}
		}
	}
}
?>