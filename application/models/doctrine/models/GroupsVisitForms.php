<?php

	Doctrine_Manager::getInstance()->bindComponent('GroupsVisitForms', 'SYSDAT');

	class GroupsVisitForms extends BaseGroupsVisitForms {

		public function get_groups_links($clientid)
		{
			$gl = Doctrine_Query::create()
				->select('*')
				->from('GroupsVisitForms')
				->where('client = "' . $clientid . '"');
			$group_links = $gl->fetchArray();

			if($group_links)
			{
				return $group_links;
			}
			else
			{
				return false;
			}
		}

		public function get_group_link($clientid, $group)
		{
			$gl = Doctrine_Query::create()
				->select('*')
				->from('GroupsVisitForms')
				->where('client = "' . $clientid . '"')
				->andWhere('groupid = "' . $group . '"');
			$group_links = $gl->fetchArray();

			$tabmenu_ids[] = '9999999999';
			if($group_links)
			{
				$tabmenu_ids[] = $group_links[0]['tabmenu'];
			}

			$tb = new TabMenus();
			$get_link = $tb->get_menus_details($tabmenu_ids);

			if($get_link)
			{
				return $get_link;
			}
			else
			{
				return false;
			}
		}

		public function get_group_link_and_type($clientid, $group)
		{
			$gl = Doctrine_Query::create()
				->select('*')
				->from('GroupsVisitForms')
				->where('client = "' . $clientid . '"')
				->andWhere('groupid = "' . $group . '"');
			$group_links = $gl->fetchArray();

			$tabmenu_ids[] = '9999999999';
			if($group_links)
			{
				$tabmenu_ids[] = $group_links[0]['tabmenu'];
			}

			$tb = new TabMenus();
			$get_link = $tb->get_menus_details($tabmenu_ids);

			$link = $get_link[0];
			$link['form_type'] = $group_links[0]['form_type'];
			
			if($link)
			{
				return $link;
			}
			else
			{
				return false;
			}
		}

		public function remove_icon($clientid, $groupid)
		{
			$del_icon = Doctrine_Query::create()
				->update('GroupsVisitForms')
				->set('image', '""')
				->where('client = "' . $clientid . '"')
				->andWhere('groupid = "' . $groupid . '"');
			$del_icon_res = $del_icon->fetchArray();

			//in case the user uploads an image
			//and then deletes it...
			//the image was stored in sesion
			$_SESSION['file'][$groupid] = "";
			$_SESSION['filetype'][$groupid] = "";
			$_SESSION['filetitle'][$groupid] = "";
			$_SESSION['filename'][$groupid] = "";
		}

	}

?>