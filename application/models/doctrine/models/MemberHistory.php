<?php
Doctrine_Manager::getInstance ()->bindComponent ( 'MemberHistory', 'SYSDAT' );
class MemberHistory extends BaseMemberHistory 
{

	public static function get_member_history_difference($clientid = 0, $member_id = 0, $type = 0) 
	{
		$clientid = ( int ) $clientid;
		$member_id = ( int ) $member_id;
		
		$usr = Doctrine_Query::create ()->select ( 't1.dt_datetime, t2.action, t1.id as id, t1.create_user,
			   IF(t1.type != t2.type, CONCAT(t1.type, " ||| ",  t2.type), NULL) as type,
			   IF(t1.auto_member_number != t2.auto_member_number, CONCAT(t1.auto_member_number, " ||| ",  t2.auto_member_number), NULL) as auto_member_number,
			   IF(t1.member_number != t2.member_number, CONCAT(t1.member_number, " ||| ",  t2.member_number), NULL) as member_number,
			   IF(t1.member_company != t2.member_company, CONCAT(t1.member_company, " ||| ",  t2.member_company), NULL) as member_company,
			   IF(t1.title != t2.title, CONCAT(t1.title, " ||| ",  t2.title), NULL) as title,
			   IF(t1.salutation_letter != t2.salutation_letter, CONCAT(t1.salutation_letter, " ||| ",  t2.salutation_letter), NULL) as salutation_letter,
			   IF(t1.salutation != t2.salutation, CONCAT(t1.salutation, " ||| ",  t2.salutation), NULL) as salutation,
			   IF(t1.first_name != t2.first_name, CONCAT(t1.first_name, " ||| ",  t2.first_name), NULL) as first_name,
			   IF(t1.last_name != t2.last_name, CONCAT(t1.last_name, " ||| ",  t2.last_name), NULL) as last_name,
			   IF(t1.gender != t2.gender, CONCAT(t1.gender, " ||| ",  t2.gender), NULL) as gender,
			   IF(t1.birthd != t2.birthd, CONCAT(t1.birthd, " ||| ",  t2.birthd), NULL) as birthd,
			   IF(t1.phone != t2.phone, CONCAT(t1.phone, " ||| ",  t2.phone), NULL) as phone,
			   IF(t1.private_phone != t2.private_phone, CONCAT(t1.private_phone, " ||| ",  t2.private_phone), NULL) as private_phone,
			   IF(t1.mobile != t2.mobile, CONCAT(t1.mobile, " ||| ",  t2.mobile), NULL) as mobile,
			   IF(t1.email != t2.email, CONCAT(t1.email, " ||| ",  t2.email), NULL) as email,
			   IF(t1.website != t2.website, CONCAT(t1.website, " ||| ",  t2.website), NULL) as website,
			   IF(t1.fax != t2.fax, CONCAT(t1.fax, " ||| ",  t2.fax), NULL) as fax,
			   IF(t1.street1 != t2.street1, CONCAT(t1.street1, " ||| ",  t2.street1), NULL) as street1,
			   IF(t1.street2 != t2.street2, CONCAT(t1.street2, " ||| ",  t2.street2), NULL) as street2,
			   IF(t1.zip != t2.zip, CONCAT(t1.zip, " ||| ",  t2.zip), NULL) as zip,
			   IF(t1.city != t2.city, CONCAT(t1.city, " ||| ",  t2.city), NULL) as city,
			   IF(t1.country != t2.country, CONCAT(t1.country, " ||| ",  t2.country), NULL) as country,
			   IF(t1.profession != t2.profession, CONCAT(t1.profession, " ||| ",  t2.profession), NULL) as profession,
			   IF(t1.clientid != t2.clientid, CONCAT(t1.clientid, " ||| ",  t2.clientid), NULL) as clientid,
			   IF(t1.isdelete != t2.isdelete, CONCAT(t1.isdelete, " ||| ",  t2.isdelete), NULL) as isdelete,
			   IF(t1.merged_parent != t2.merged_parent, CONCAT(t1.merged_parent, " ||| ",  t2.merged_parent), NULL) as merged_parent,
			   IF(t1.merged_slave != t2.merged_slave, CONCAT(t1.merged_slave, " ||| ",  t2.merged_slave), NULL) as merged_slave,
			   IF(t1.inactive != t2.inactive, CONCAT(t1.inactive, " ||| ",  t2.inactive), NULL) as inactive,
			   IF(t1.inactive_from != t2.inactive_from, CONCAT(t1.inactive_from, " ||| ",  t2.inactive_from), NULL) as inactive_from,
			   IF(t1.status != t2.status, CONCAT(t1.status, " ||| ",  t2.status), NULL) as status,
			   IF(t1.shortname != t2.shortname, CONCAT(t1.shortname, " ||| ",  t2.shortname), NULL) as shortname,
			   IF(t1.bank_name != t2.bank_name, CONCAT(t1.bank_name, " ||| ",  t2.bank_name), NULL) as bank_name,
			   IF(t1.bank_account_number != t2.bank_account_number, CONCAT(t1.bank_account_number, " ||| ",  t2.bank_account_number), NULL) as bank_account_number,
			   IF(t1.bank_number != t2.bank_number, CONCAT(t1.bank_number, " ||| ",  t2.bank_number), NULL) as bank_number,
			   IF(t1.iban != t2.iban, CONCAT(t1.iban, " ||| ",  t2.iban), NULL) as iban,
			   IF(t1.bic != t2.bic, CONCAT(t1.bic, " ||| ",  t2.bic), NULL) as bic,
			   IF(t1.account_holder != t2.account_holder, CONCAT(t1.account_holder, " ||| ",  t2.account_holder), NULL) as account_holder,
			   IF(t1.mandate_reference != t2.mandate_reference, CONCAT(t1.mandate_reference, " ||| ",  t2.mandate_reference), NULL) as mandate_reference,
			   IF(t1.mandate_reference_date != t2.mandate_reference_date, CONCAT(t1.mandate_reference_date, " ||| ",  t2.mandate_reference_date), NULL) as mandate_reference_date,
			   IF(t1.payment_method_id != t2.payment_method_id, CONCAT(t1.payment_method_id, " ||| ",  t2.payment_method_id), NULL) as payment_method_id,
			   IF(t1.remarks != t2.remarks, CONCAT(t1.remarks, " ||| ",  t2.remarks), NULL) as remarks,
			   IF(t1.memos != t2.memos, CONCAT(t1.memos, " ||| ",  t2.memos), NULL) as memos,
			   IF(t1.comments != t2.comments, CONCAT(t1.comments, " ||| ",  t2.comments), NULL) as comments,
			   IF(t1.img_path != t2.img_path, CONCAT(t1.img_path, " ||| ",  t2.img_path), NULL) as img_path,
			   IF(t1.vw_id != t2.vw_id, CONCAT(t1.vw_id, " ||| ",  t2.vw_id), NULL) as vw_id,
				t2.change_user
			' )->from ( 'MemberHistory t1' )->innerJoin ( 't1.MemberHistory as t2 ON t1.id = t2.id' )->where ( 't1.id=' . $member_id . ' and t1.clientid=' . $clientid . ' AND t1.action=\'update\' and ((t1.revision = 1 AND t2.revision = 1) OR t2.revision = t1.revision+1)' )->orderBy ( 't1.id ASC, t2.revision ASC' );
		$results = $usr->execute ( array (), Doctrine_Core::HYDRATE_SCALAR );
		
		// compare with current data
		$usr = Doctrine_Query::create ()->select ( ' t1.dt_datetime as dt_datetime,
					 t1.id as id, t1.create_user,
			   IF(t1.type != t2.type, CONCAT(t1.type, " ||| ",  t2.type), NULL) as type,
			   IF(t1.auto_member_number != t2.auto_member_number, CONCAT(t1.auto_member_number, " ||| ",  t2.auto_member_number), NULL) as auto_member_number,
			   IF(t1.member_number != t2.member_number, CONCAT(t1.member_number, " ||| ",  t2.member_number), NULL) as member_number,
			   IF(t1.member_company != t2.member_company, CONCAT(t1.member_company, " ||| ",  t2.member_company), NULL) as member_company,
			   IF(t1.title != t2.title, CONCAT(t1.title, " ||| ",  t2.title), NULL) as title,
			   IF(t1.salutation_letter != t2.salutation_letter, CONCAT(t1.salutation_letter, " ||| ",  t2.salutation_letter), NULL) as salutation_letter,
			   IF(t1.salutation != t2.salutation, CONCAT(t1.salutation, " ||| ",  t2.salutation), NULL) as salutation,
			   IF(t1.first_name != t2.first_name, CONCAT(t1.first_name, " ||| ",  t2.first_name), NULL) as first_name,
			   IF(t1.last_name != t2.last_name, CONCAT(t1.last_name, " ||| ",  t2.last_name), NULL) as last_name,
			   IF(t1.gender != t2.gender, CONCAT(t1.gender, " ||| ",  t2.gender), NULL) as gender,
			   IF(t1.birthd != t2.birthd, CONCAT(t1.birthd, " ||| ",  t2.birthd), NULL) as birthd,
			   IF(t1.phone != t2.phone, CONCAT(t1.phone, " ||| ",  t2.phone), NULL) as phone,
			   IF(t1.private_phone != t2.private_phone, CONCAT(t1.private_phone, " ||| ",  t2.private_phone), NULL) as private_phone,
			   IF(t1.mobile != t2.mobile, CONCAT(t1.mobile, " ||| ",  t2.mobile), NULL) as mobile,
			   IF(t1.email != t2.email, CONCAT(t1.email, " ||| ",  t2.email), NULL) as email,
			   IF(t1.website != t2.website, CONCAT(t1.website, " ||| ",  t2.website), NULL) as website,
			   IF(t1.fax != t2.fax, CONCAT(t1.fax, " ||| ",  t2.fax), NULL) as fax,
			   IF(t1.street1 != t2.street1, CONCAT(t1.street1, " ||| ",  t2.street1), NULL) as street1,
			   IF(t1.street2 != t2.street2, CONCAT(t1.street2, " ||| ",  t2.street2), NULL) as street2,
			   IF(t1.zip != t2.zip, CONCAT(t1.zip, " ||| ",  t2.zip), NULL) as zip,
			   IF(t1.city != t2.city, CONCAT(t1.city, " ||| ",  t2.city), NULL) as city,
			   IF(t1.country != t2.country, CONCAT(t1.country, " ||| ",  t2.country), NULL) as country,
			   IF(t1.profession != t2.profession, CONCAT(t1.profession, " ||| ",  t2.profession), NULL) as profession,
			   IF(t1.clientid != t2.clientid, CONCAT(t1.clientid, " ||| ",  t2.clientid), NULL) as clientid,
			   IF(t1.isdelete != t2.isdelete, CONCAT(t1.isdelete, " ||| ",  t2.isdelete), NULL) as isdelete,
			   IF(t1.merged_parent != t2.merged_parent, CONCAT(t1.merged_parent, " ||| ",  t2.merged_parent), NULL) as merged_parent,
			   IF(t1.merged_slave != t2.merged_slave, CONCAT(t1.merged_slave, " ||| ",  t2.merged_slave), NULL) as merged_slave,
			   IF(t1.inactive != t2.inactive, CONCAT(t1.inactive, " ||| ",  t2.inactive), NULL) as inactive,
			   IF(t1.inactive_from != t2.inactive_from, CONCAT(t1.inactive_from, " ||| ",  t2.inactive_from), NULL) as inactive_from,
			   IF(t1.status != t2.status, CONCAT(t1.status, " ||| ",  t2.status), NULL) as status,
			   IF(t1.shortname != t2.shortname, CONCAT(t1.shortname, " ||| ",  t2.shortname), NULL) as shortname,
			   IF(t1.bank_name != t2.bank_name, CONCAT(t1.bank_name, " ||| ",  t2.bank_name), NULL) as bank_name,
			   IF(t1.bank_account_number != t2.bank_account_number, CONCAT(t1.bank_account_number, " ||| ",  t2.bank_account_number), NULL) as bank_account_number,
			   IF(t1.bank_number != t2.bank_number, CONCAT(t1.bank_number, " ||| ",  t2.bank_number), NULL) as bank_number,
			   IF(t1.iban != t2.iban, CONCAT(t1.iban, " ||| ",  t2.iban), NULL) as iban,
			   IF(t1.bic != t2.bic, CONCAT(t1.bic, " ||| ",  t2.bic), NULL) as bic,
			   IF(t1.account_holder != t2.account_holder, CONCAT(t1.account_holder, " ||| ",  t2.account_holder), NULL) as account_holder,
			   IF(t1.mandate_reference != t2.mandate_reference, CONCAT(t1.mandate_reference, " ||| ",  t2.mandate_reference), NULL) as mandate_reference,
			   IF(t1.mandate_reference_date != t2.mandate_reference_date, CONCAT(t1.mandate_reference_date, " ||| ",  t2.mandate_reference_date), NULL) as mandate_reference_date,
			   IF(t1.payment_method_id != t2.payment_method_id, CONCAT(t1.payment_method_id, " ||| ",  t2.payment_method_id), NULL) as payment_method_id,
			   IF(t1.remarks != t2.remarks, CONCAT(t1.remarks, " ||| ",  t2.remarks), NULL) as remarks,
			   IF(t1.memos != t2.memos, CONCAT(t1.memos, " ||| ",  t2.memos), NULL) as memos,
			   IF(t1.comments != t2.comments, CONCAT(t1.comments, " ||| ",  t2.comments), NULL) as comments,
			   IF(t1.img_path != t2.img_path, CONCAT(t1.img_path, " ||| ",  t2.img_path), NULL) as img_path,
			   IF(t1.vw_id != t2.vw_id, CONCAT(t1.vw_id, " ||| ",  t2.vw_id), NULL) as vw_id,
				t2.change_user	
			' )->from ( 'MemberHistory t1' )->innerJoin ( 't1.Member as t2 ON t1.id = t2.id' )->where ( 't1.id=\'' . $member_id . '\' and t1.clientid=\'' . $clientid . "'" . " AND t1.action='update'" )->andWhere ( "revision IN( SELECT MAX(revision) FROM member_history WHERE id = '" . $member_id . "'  GROUP BY id)" )->groupBy ( 't1.id' )->limit ( 1 );
		$r = $usr->execute ( array (), Doctrine_Core::HYDRATE_SCALAR );
		
		$results = array_merge ( $results, $r );
		foreach ( $results as $k => $v ) {
			$results [$k] ['masort'] = strtotime ( $v ['t1_dt_datetime'] );
			$results [$k] ['tab_block'] = 'member';
		}
		self::masort ( $results, 'masort' );
		
		// get family updates if any
		$t1_type = implode ( " ", array_column ( $results, 't1_type' ) );
		if ($type == 'family' || strpos ( $t1_type, "family" ) !== false) {
			$member_family_history = self::get_member_family_history_difference ( $clientid, $member_id );
			if (is_array ( $member_family_history ) && count ( $member_family_history ) > 0) {
				$results = array_merge ( $results, $member_family_history );
				self::masort ( $results, 'masort' );
			}
		}
		
		$member2memberships_history = self::get_member2memberships_history_difference ( $clientid, $member_id );
		if (is_array ( $member2memberships_history ) && count ( $member2memberships_history ) > 0) {
			$results = array_merge ( $results, $member2memberships_history );
			self::masort ( $results, 'masort' );
		}
		
		$member_donations_history = self::get_member_donations_history_difference ( $clientid, $member_id );
		if (is_array ( $member_donations_history ) && count ( $member_donations_history ) > 0) {
			$results = array_merge ( $results, $member_donations_history );
			self::masort ( $results, 'masort' );
		}
		
		return $results;
	}
	private function masort(&$data, $sortby) 
	{
		if (is_array ( $sortby )) {
			$sortby = join ( ',', $sortby );
		}
		
		uasort ( $data, create_function ( '$a,$b', '$skeys = split(\',\',\'' . $sortby . '\');
	        foreach($skeys as $key){
	            if( ($c = strcasecmp($a[$key],$b[$key])) != 0 ){
	                return($c);
	            }
	        }
	        return($c); ' ) );
	}
	
	private function get_member_family_history_difference($clientid = 0, $member_id = 0) 
	{
		$usr = Doctrine_Query::create ()->select ( '
					t1.dt_datetime, t2.action, t1.id as id, t1.create_user,
   IF(t1.type != t2.type, CONCAT(t1.type, " ||| ",  t2.type), NULL) as type,
   IF(t1.member_id != t2.member_id, CONCAT(t1.member_id, " ||| ",  t2.member_id), NULL) as member_id,
   IF(t1.title != t2.title, CONCAT(t1.title, " ||| ",  t2.title), NULL) as title,
   IF(t1.salutation_letter != t2.salutation_letter, CONCAT(t1.salutation_letter, " ||| ",  t2.salutation_letter), NULL) as salutation_letter,
   IF(t1.salutation != t2.salutation, CONCAT(t1.salutation, " ||| ",  t2.salutation), NULL) as salutation,
   IF(t1.first_name != t2.first_name, CONCAT(t1.first_name, " ||| ",  t2.first_name), NULL) as first_name,
   IF(t1.last_name != t2.last_name, CONCAT(t1.last_name, " ||| ",  t2.last_name), NULL) as last_name,
   IF(t1.gender != t2.gender, CONCAT(t1.gender, " ||| ",  t2.gender), NULL) as gender,
   IF(t1.birthd != t2.birthd, CONCAT(t1.birthd, " ||| ",  t2.birthd), NULL) as birthd,
t2.change_user
		
' )->

		from ( 'MemberFamilyHistory t1' )->innerJoin ( 't1.MemberFamilyHistory as t2 ON t1.id = t2.id' )->where ( 't1.member_id=' . $member_id . '  AND t1.action=\'update\' and ((t1.revision = 1 AND t2.revision = 1) OR t2.revision = t1.revision+1)' )->orderBy ( 't1.id ASC, t2.revision ASC' );
		
		$results = $usr->execute ( array (), Doctrine_Core::HYDRATE_SCALAR );
		
		$usr = Doctrine_Query::create ()->select ( ' t1.dt_datetime,
					 t1.id as id, t1.create_user,
   IF(t1.type != t2.type, CONCAT(t1.type, " ||| ",  t2.type), NULL) as type,
   IF(t1.member_id != t2.member_id, CONCAT(t1.member_id, " ||| ",  t2.member_id), NULL) as member_id,
   IF(t1.title != t2.title, CONCAT(t1.title, " ||| ",  t2.title), NULL) as title,
   IF(t1.salutation_letter != t2.salutation_letter, CONCAT(t1.salutation_letter, " ||| ",  t2.salutation_letter), NULL) as salutation_letter,
   IF(t1.salutation != t2.salutation, CONCAT(t1.salutation, " ||| ",  t2.salutation), NULL) as salutation,
   IF(t1.first_name != t2.first_name, CONCAT(t1.first_name, " ||| ",  t2.first_name), NULL) as first_name,
   IF(t1.last_name != t2.last_name, CONCAT(t1.last_name, " ||| ",  t2.last_name), NULL) as last_name,
   IF(t1.gender != t2.gender, CONCAT(t1.gender, " ||| ",  t2.gender), NULL) as gender,
   IF(t1.birthd != t2.birthd, CONCAT(t1.birthd, " ||| ",  t2.birthd), NULL) as birthd,
t2.change_user
		
' )->from ( 'MemberFamilyHistory t1' )->innerJoin ( 't1.MemberFamily as t2 ON t1.id = t2.id' )->

		where ( "t1.member_id='" . $member_id . "'" . " AND t1.action='update'" )->andWhere ( "revision IN( SELECT MAX(revision) FROM member_family_history WHERE $member_id = '" . $member_id . "'  GROUP BY id)" )->groupBy ( 't1.id' )->limit ( 1 );
		$r = $usr->execute ( array (), Doctrine_Core::HYDRATE_SCALAR );
		// print_r($r);
		// print_r($usr->getSqlQuery());die();
		/*
		 * if($r[0]['t2_dt_datetime']){
		 * $r[0]['t1_dt_datetime'] = $r[0]['t2_dt_datetime'];
		 * }
		 */
		$results = array_merge ( $results, $r );
		foreach ( $results as $k => $v ) {
			$results [$k] ['masort'] = strtotime ( $v ['t1_dt_datetime'] );
			$results [$k] ['tab_block'] = 'memberfamily';
		}
		self::masort ( $results, 'masort' );
		
		return $results;
	}
	private function get_member2memberships_history_difference($clientid = 0, $member_id = 0) 
	{
		$usr = Doctrine_Query::create ()->select ( 't1.id, t1.revision,t1.dt_datetime,t2.change_user,
					IF(t1.membership != t2.membership, CONCAT(t1.membership, " ||| ",  t2.membership), NULL) as membership,
					IF(t1.membership_price != t2.membership_price, CONCAT(t1.membership_price, " ||| ",  t2.membership_price), NULL) as membership_price,
					IF(t1.start_date != t2.start_date, CONCAT(t1.start_date, " ||| ",  t2.start_date), NULL) as start_date,
					IF(t1.end_date != t2.end_date, CONCAT(t1.end_date, " ||| ",  t2.end_date), NULL) as end_date,
					IF(t1.end_reasonid != t2.end_reasonid, CONCAT(t1.end_reasonid, " ||| ",  t2.end_reasonid), NULL) as end_reasonid,
					IF(t1.isdelete != t2.isdelete, CONCAT(t1.isdelete, " ||| ",  t2.isdelete), NULL) as isdelete
					' )->from ( 'Member2MembershipsHistory t1' )->innerJoin ( 't1.Member2MembershipsHistory as t2 ON t1.id = t2.id' )->where ( 't1.member=' . $member_id . ' and t1.clientid=' . $clientid . ' AND t1.action=\'update\' and ((t1.revision = 1 AND t2.revision = 1) OR t2.revision = t1.revision+1)' );
		$results = $usr->execute ( array (), Doctrine_Core::HYDRATE_SCALAR );
		
		// compare with current data
		$usr = Doctrine_Query::create ()->select ( 't1.id,MAX(revision), t1.dt_datetime, t2.change_user,
					IF(t1.membership != t2.membership, CONCAT(t1.membership, " ||| ",  t2.membership), NULL) as membership,
					IF(t1.membership_price != t2.membership_price, CONCAT(t1.membership_price, " ||| ",  t2.membership_price), NULL) as membership_price,
					IF(t1.start_date != t2.start_date, CONCAT(t1.start_date, " ||| ",  t2.start_date), NULL) as start_date,
					IF(t1.end_date != t2.end_date, CONCAT(t1.end_date, " ||| ",  t2.end_date), NULL) as end_date,
					IF(t1.end_reasonid != t2.end_reasonid, CONCAT(t1.end_reasonid, " ||| ",  t2.end_reasonid), NULL) as end_reasonid,
					IF(t1.isdelete != t2.isdelete, CONCAT(t1.isdelete, " ||| ",  t2.isdelete), NULL) as isdelete
					' )->from ( 'Member2MembershipsHistory t1' )->innerJoin ( 't1.Member2Memberships as t2 ON t1.id = t2.id' )->where ( 't1.member=' . $member_id . ' AND t1.clientid=' . $clientid . " AND t1.action='update'" )->andWhere ( "revision IN( SELECT MAX(revision) FROM member2memberships_history WHERE member = '" . $member_id . "'  GROUP BY id)" )->groupBy ( 'id' );
		$r = $usr->execute ( array (), Doctrine_Core::HYDRATE_SCALAR );
		// array_walk($r, create_function('&$val', 'print_r($val);$val = trim(rtrim($val)); '));
		
		$results = array_merge ( $results, $r );
		
		foreach ( $results as $k => $v ) {
			$results [$k] ['masort'] = strtotime ( $v ['t1_dt_datetime'] );
			$results [$k] ['tab_block'] = 'member2memberships';
		}
		self::masort ( $results, 'masort' );
		
		// get all with insert
		$usr = Doctrine_Query::create ()->select ( 'dt_datetime as t1_dt_datetime, UNIX_TIMESTAMP(dt_datetime) as masort, create_user, action, \'member2memberships\' as tab_block, \' ||| \' as insert  ' )->from ( 'Member2MembershipsHistory' )->where ( 'member=' . $member_id . ' and clientid=' . $clientid . ' AND action=\'insert\' and revision = 1 ' );
		$r = $usr->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		$results = array_merge ( $results, $r );
		self::masort ( $results, 'masort' );
		
		return $results;
	}
	
	private function get_member_donations_history_difference($clientid = 0, $member_id = 0) 
	{
		$usr = Doctrine_Query::create ()->select ( 't1.id, t1.revision,t1.dt_datetime,t2.change_user,
					IF(t1.donation_date != t2.donation_date, CONCAT(t1.donation_date, " ||| ",  t2.donation_date), NULL) as donation_date,
					IF(t1.amount != t2.amount, CONCAT(t1.amount, " ||| ",  t2.amount), NULL) as amount,
					IF(t1.isdelete != t2.isdelete, CONCAT(t1.isdelete, " ||| ",  t2.isdelete), NULL) as isdelete
					' )->from ( 'MemberDonationsHistory t1' )->innerJoin ( 't1.MemberDonationsHistory as t2 ON t1.id = t2.id' )->where ( 't1.member=' . $member_id . ' and t1.clientid=' . $clientid . ' AND t1.action=\'update\' and ((t1.revision = 1 AND t2.revision = 1) OR t2.revision = t1.revision+1)' );
		$results = $usr->execute ( array (), Doctrine_Core::HYDRATE_SCALAR );
		
		// compare with current data
		$usr = Doctrine_Query::create ()->select ( 't1.id,MAX(revision), t1.dt_datetime ,t2.change_user,
					IF(t1.donation_date != t2.donation_date, CONCAT(t1.donation_date, " ||| ",  t2.donation_date), NULL) as donation_date,
					IF(t1.amount != t2.amount, CONCAT(t1.amount, " ||| ",  t2.amount), NULL) as amount,
					IF(t1.isdelete != t2.isdelete, CONCAT(t1.isdelete, " ||| ",  t2.isdelete), NULL) as isdelete
					' )->from ( 'MemberDonationsHistory t1' )->innerJoin ( 't1.MemberDonations as t2 ON t1.id = t2.id' )->where ( 't1.member=' . $member_id . ' AND t1.clientid=' . $clientid . " AND t1.action='update'" )->andWhere ( "revision IN( SELECT MAX(revision) FROM member_donations_history WHERE member = '" . $member_id . "'  GROUP BY id)" )->groupBy ( 'id' );
		$r = $usr->execute ( array (), Doctrine_Core::HYDRATE_SCALAR );
		
		$results = array_merge ( $results, $r );
		
		foreach ( $results as $k => $v ) {
			$results [$k] ['masort'] = strtotime ( $v ['t1_dt_datetime'] );
			$results [$k] ['tab_block'] = 'memberdonations';
		}
		self::masort ( $results, 'masort' );
		
		// get all with insert
		$usr = Doctrine_Query::create ()->select ( 'dt_datetime as t1_dt_datetime, UNIX_TIMESTAMP(dt_datetime) as masort, create_user, action, \'memberdonations\' as tab_block, \' ||| \' as insert  ' )->from ( 'MemberDonationsHistory' )->where ( 'member=' . $member_id . ' and clientid=' . $clientid . ' AND action=\'insert\' and revision = 1 ' );
		$r = $usr->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		$results = array_merge ( $results, $r );
		self::masort ( $results, 'masort' );
		
		// echo "<pre>";
		// print_r($results);
		return $results;
	}
	
	//un-merge NOT possible = not requested
	public function verify_if_unmerge_is_possible($clientid = 0, $member_id = 0) 
	{
		
		$usr = Doctrine_Query::create ();
		$usr->select ( '*' );
		$usr->from ( 'MemberHistory' );
		$usr->where ( 'id= ? ', $member_id );
		$usr->andWhere ( 'clientid= ?', $clientid );
		$usr->limit ( 1 );
		$results = $usr->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		$results = array_values ( $results );
		$results = $results [0];
		
		if (is_array ( $results )) {
			// member details modified, not possible
			return false;
			//return array("error"=>Zend_View_Helper_Translate::translate("member profile edited after merge"));
		}
		
		//verify if donations was moddified
		$usr = Doctrine_Query::create ();
		$usr->select ( '*' );
		$usr->from ( 'MemberDonationsHistory' );
		$usr->where ( 'clientid= ?', $clientid );
		$usr->andwhere ( 'member= ? ', $member_id );
		$usr->andwhere ( 'action=\'update\''  );
		$usr->limit ( 1 );
		
		$results = $usr->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		$results = array_values ( $results );
		$results = $results [0];
		if (is_array ( $results )) {
			// member donations modified, not possible
			return false;
			//return array("error"=>Zend_View_Helper_Translate::translate("donations edited after merge"));
		}
		
		
		//verify if member_family was moddified
		$usr = Doctrine_Query::create ();
		$usr->select ( '*' );
		$usr->from ( 'MemberFamilyHistory' );
		$usr->where ( 'clientid= ?', $clientid );
		$usr->andwhere ( 'member_id= ? ', $member_id );
		$usr->andwhere ( 'action=\'update\''  );
		$usr->limit ( 1 );
		$results = $usr->execute ( array (), Doctrine_Core::HYDRATE_ARRAY );
		$results = array_values ( $results );
		$results = $results [0];
		if (is_array ( $results )) {
			// member donations modified, not possible
			return false;
			//return array("error"=>Zend_View_Helper_Translate::translate("family member edited after merge"));
		}
		
		return true;
		
	}
}
?>