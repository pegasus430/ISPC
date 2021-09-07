<?php

	Doctrine_Manager::getInstance()->bindComponent('VwLetterTemplates', 'SYSDAT');

	class VwLetterTemplates extends BaseVwLetterTemplates {

		public function get_template($clientid, $template = false, $limit = false)
		{
			if($template)
			{
				$res = Doctrine_Query::create()
					->select('*')
					->from('VwLetterTemplates')
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
                ->from('VwLetterTemplates')
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
            ->from('VwLetterTemplates')
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
	}

?>