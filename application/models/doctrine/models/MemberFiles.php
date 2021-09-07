<?php

	Doctrine_Manager::getInstance()->bindComponent('MemberFiles', 'SYSDAT');

	class MemberFiles extends BaseMemberFiles {

		public function get_template($clientid, $template = false, $limit = false)
		{
			if($template)
			{
				$res = Doctrine_Query::create()
					->select('*')
					->from('MemberLetterTemplates')
					->where('clientid = "' . $clientid . '"')
					->andWhere('id = "' . $template . '"')
					->andWhere('isdeleted = "0"');
				if($limit)
				{
					$res->limit($limit);
				}

				$res_arr = $res->fetchArray();

				if($res_arr)
				{
					return $res_arr;
				}
				else
				{
					return false;
				}
			}
		}

        public function get_letter_template($clientid)
        {
            $res = Doctrine_Query::create()->select('*')
                ->from('MemberLetterTemplates')
                ->where('clientid = "' . $clientid . '"')
                ->andWhere('isdeleted = "0"')
                ->orderBy('id DESC');
            
            $res->limit("1");
            
            $res_arr = $res->fetchArray();
            
            if ($res_arr) {
                return $res_arr;
            } else {
                return false;
            }
        }

        
        public function get_all_letter_templates($clientid)
        {
            $res = Doctrine_Query::create()
            ->select('*')
            ->from('MemberLetterTemplates')
            ->where('clientid = "' . $clientid . '"')
            ->andWhere('isdeleted = "0"')
            ->orderBy('title ASC');
//             echo $res->getSqlQuery(); exit;
            $res_arr = $res->fetchArray();
        
            if ($res_arr) {
                return $res_arr;
            } else {
                return false;
            }
        }
	
		public function get_files($clientid, $member_id = false, $isdeleted = 0){

			$fdocarray= false;
			$res = Doctrine_Query::create()
			->select('*')
			->from('MemberFiles')
			->where('clientid = ?' , $clientid )
			->andWhere('member_id = ?' , $member_id )
			->orderBy('template_id,revision ASC');
			
			if (isdeleted == 0 ){
				$res->andWhere('isdeleted = "0"');
			}
			
			//echo $res->getSqlQuery(); //exit;
			$res_arr = $res->fetchArray();
			
			foreach($res_arr as $k=>$v){
				$new_arr[$v['parent_id']][$v['revision']] = $v;
				ksort($new_arr[$v['parent_id']]);
			}
			ksort($new_arr);
			
			//echo "<pre>";
			//print_r($new_arr);
			//print_r($res_arr);
			//die();
			/*
			if ($fdoc = Doctrine::getTable('MemberFiles')->findByClientidAndMember_idAndIsdeleted($clientid, $member_id , $isdeleted))
			{
				$fdocarray = $fdoc->toArray();	
			}
		*/
			return $new_arr;
		}
		
		
		public static function get_files_by_id( $ids = array())
		{
			$result = array();
			
			if (empty($ids) || ! is_array($ids)){
				return $result;
			}

			$res = Doctrine_Query::create()
			->select('*')
			->from('MemberFiles')
			->whereIn('id' , $ids )
			->fetchArray();
				
			foreach($res as $row){
				$result[$row['id']] = $row;
			}
			
			return $result;
		}
	
	}

?>