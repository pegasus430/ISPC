<?php

	Doctrine_Manager::getInstance()->bindComponent('VoluntaryworkersIcons', 'SYSDAT');

	class VoluntaryworkersIcons extends BaseVoluntaryworkersIcons {

		public function get_icons($vw_ids)
		{
			$icns = Doctrine_Query::create()
				->select('*')
				->from('VoluntaryworkersIcons')
				->where(' isdelete = 0 ');
			if(is_array($vw_ids))
			{
				$icns->andWhereIn('vw_id', $vw_ids);
			}
			else
			{
				$icns->andWhere('vw_id ="' . $vw_ids . '"');
			}
			$icns->orderBy('id ASC');
			$icons = $icns->fetchArray();

			return $icons;
		}

		public function get_icons_allowed($vw_ids, $allowed_icons = false)
		{
			$icns = Doctrine_Query::create()
				->select('*')
				->from('VoluntaryworkersIcons');
			if(is_array($vw_ids))
			{
				$icns->andWhereIn('vw_id', $vw_ids);
			}
			else
			{
				$icns->andWhere('vw_id="' . $vw_ids . '"');
			}

			if($allowed_icons)
			{
				if(is_array($allowed_icons))
				{
					$icns->andWhereIn('icon_id', $allowed_icons);
				}
				else
				{
					$icns->andWhere('icon_id = "' . $allowed_icons . '"');
				}
			}

			$icns->orderBy('id ASC');
			$icons = $icns->fetchArray();

			return $icons;
		}

		public function filter_icons($vw_ids=false, $icons = false)
		{
			if($icons)
			{
				//filter
				$icns = Doctrine_Query::create()
					->select('*')
					->from('VoluntaryworkersIcons')
					->where('isdelete=0');
				if($vw_ids)
				{
    				if(is_array($vw_ids))
    				{
    					$icns = $icns->andWhereIn('vw_id', $vw_ids);
    				}
    				else
    				{
    					$icns = $icns->where('vw_id LIKE "' . $vw_ids . '"');
    				}
				}

				
				if(is_array($icons))
				{
					$icns = $icns->andWhereIn('icon_id', $icons);
				}
				else
				{
					$icns = $icns->andWhere('icon_id = "' . $icons . '"');
				}
				$icns->orderBy('id ASC');

				$icons_res = $icns->fetchArray();

				$vw_ids_out[] = '999999999';
				foreach($icons_res as $k_icon_res => $v_icon_res)
				{
					$vw_ids_out[] = $v_icon_res['vw_id'];
				}
			}
			else
			{ // do not filter.. no icons selected...
				$vw_ids_out = $vw_ids;
			}

			return $vw_ids_out;
		}
		
	}

?>