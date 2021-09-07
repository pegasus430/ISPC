<?php
/**
 * 
 * @author claudiu
 * 
 * SPENDER tweak
 * ispc 1881 - separate into members(added via members tab) and donors(added via spender tab)
 *
 */

class MemberReferalTab extends BaseMemberReferalTab 
{
	
	public static function get_donors($clientid = 0)
	{
		$donors = Doctrine_Query::create()
		->select('id, memberid')
		->from('MemberReferalTab')
		->where('clientid = :clientid')
		->andWhere('referal_tab = :referal_tab')
		->andWhere('isdelete = 0')
		->fetchArray( array("clientid" => $clientid , "referal_tab" => "donors"));
		
		return $donors;
		
	} 
	
	public static function get_members($clientid = 0)
	{
		$members = Doctrine_Query::create()
		->select('id, memberid')
		->from('MemberReferalTab')
		->where('clientid = :clientid')
		->andWhere('referal_tab = :referal_tab')
		->andWhere('isdelete = 0')
		->fetchArray( array("clientid" => $clientid , "referal_tab" => "members"));
		
		return $members;
	}
	
	
	public static function set_referal_tab($clientid = 0 , $memberid = 0 , $referal_tab = "members" )
	{
		$fdoc = Doctrine::getTable('MemberReferalTab')->findOneByClientidAndMemberid( $clientid , $memberid);
		if ($fdoc instanceof MemberReferalTab) {
			//update
			if ($fdoc->referal_tab != $referal_tab) {
				//@todo maybe isdelete old and insert new to remember this action
				$fdoc->referal_tab = $referal_tab;
				$fdoc->save();
			}
		} else {
			//insert new
			$fdoc = new MemberReferalTab();
			$fdoc->clientid = $clientid;
			$fdoc->memberid = $memberid;
			$fdoc->referal_tab = $referal_tab;
			$fdoc->save();
			
		}
		return $fdoc->id;
	
	}

	
	public function DeleteData( $id )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
	
		$mrt = Doctrine::getTable('MemberReferalTab')->find($id);
	
		if ($mrt instanceof MemberReferalTab ) {
			if ($clientid == $mrt->clientid) {
				$mrt->delete();
				return true;
			}
		}
	
		$Tr = new Zend_View_Helper_Translate();
		$btmseal_lang = $Tr->translate('btmseal_lang');
		$this->error_message['delete'] = $btmseal_lang['error_delete_fail'];
		return false;
	}

}

?>