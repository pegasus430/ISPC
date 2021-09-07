<?php

	Doctrine_Manager::getInstance()->bindComponent('MemberIcons', 'SYSDAT');

	class MemberIcons extends BaseMemberIcons {

		public function get_icons($member_ids)
		{
			$icns = Doctrine_Query::create()
				->select('*')
				->from('MemberIcons')
				->where(' isdelete = 0 ');
			if(is_array($member_ids))
			{
				$icns->andWhereIn('member_id', $member_ids);
			}
			else
			{
				$icns->andWhere('member_id = ?',  $member_ids);
			}
			$icns->orderBy('id ASC');
			$icons = $icns->fetchArray();

			$icons_member = array();
			//print_r($icons);
			foreach($icons as $k_icon => $v_icon)
			{
				$icons_member[$v_icon['member_id']][$v_icon['icon_id']] = $v_icon;
			}
			
			return $icons_member;
			
		}

		public function get_icons_allowed($member_ids, $allowed_icons = false)
		{
			$icns = Doctrine_Query::create()
				->select('*')
				->from('MemberIcons');
			if(is_array($member_ids))
			{
				$icns->andWhereIn('member_id', $member_ids);
			}
			else
			{
				$icns->andWhere('member_id= ?', $member_ids);
			}

			if($allowed_icons)
			{
				if(is_array($allowed_icons))
				{
					$icns->andWhereIn('icon_id', $allowed_icons);
				}
				else
				{
					$icns->andWhere('icon_id = ?', $allowed_icons);
				}
			}

			$icns->orderBy('id ASC');
			$icons = $icns->fetchArray();

			return $icons;
		}

		public function filter_icons($member_ids=false, $icons = false)
		{
			$member_id_arr = false;
			if($icons !== false)
			{
				//filter
				$icns = Doctrine_Query::create()
					->select('member_id')
					->from('MemberIcons')
					->where('isdelete=0');
				if($member_ids !== false)
				{
    				if( ! is_array($member_ids))
    				{
    					$member_ids = array($member_ids);
    				}
    				
    				$icns = $icns->andWhereIn('member_id', $member_ids);
				}

				if( ! is_array($icons)) {
					$icons = array($icons);
				}
				$icns = $icns->andWhereIn('icon_id', $icons);
				
				$icons_res = $icns->fetchArray();
				if ( ! empty($icons_res)){
					$member_id_arr = array_unique(array_column($icons_res, 'member_id'));
				}
			}
			
			return $member_id_arr;
		}
		
		public function set_member_icon($member_id, $custom_icon, $clientid)
		{
			if (is_array($custom_icon)){
				//set isdeleted
				$this->set_remove_member_icon($member_id , $custom_icon);
				//save all new
				foreach($custom_icon as $k=>$v){
					if(($fl = Doctrine::getTable('MemberIcons')->findByMember_idAndIcon_id( $member_id , $v))
							&& ($fl{0}->id > 0) )
					{						
						$fl = $fl{0};
						$fl->isdelete = 0;
						$fl->save();
					}
					else{
						//new insert
						$cust_icons = new MemberIcons();
						$cust_icons->clientid = $clientid;
						$cust_icons->member_id = $member_id;
						$cust_icons->icon_id = $v;
						$cust_icons->save();
					}
				}		
			}
			else{
				//set all isdeleted
				$this->set_remove_member_icon($member_id);
			}
		}
		public function set_remove_member_icon($member_id, $custom_icon = false)
		{
	
			$q = Doctrine_Query::create()
			->update(' MemberIcons ')
			->set('isdelete','"1"')
			->Where("member_id = ?", $member_id);
			if( $custom_icon ){
				$q->andWhereNotIn('icon_id', $custom_icon);
			}
			
			$r = $q->execute();
		}
		
	}

?>