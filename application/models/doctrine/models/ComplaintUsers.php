<?php
class ComplaintUsers extends BaseComplaintUsers {
	
	public static function get_complaint_users($client = false, $minimal = true) {
		
		if ($client) {
			
			$sel = Doctrine_Query::create ()
			->select ( '*' )
			->from ( 'ComplaintUsers' )
			->where ( 'isdelete="0"' )
			->andWhere ( 'clientid = ? ', $client );
			$sel_res = $sel->fetchArray ();
			
			if ($sel_res) {
				if ($minimal) {
					foreach ( $sel_res as $k_sel => $v_sel ) {
						if($v_sel['open_case'] == "1"){
							$selected_users['open_case'] [] = $v_sel ['userid'];
						}
						if($v_sel['close_case'] == "1"){
							$selected_users['close_case'] [] = $v_sel ['userid'];
						}
						
					}
					
					return $selected_users;
				} else {
					
					return $sel_res;
					
// 					return User::getUsersDetails ( array_column ( $sel_res, 'userid' ) );
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

?>