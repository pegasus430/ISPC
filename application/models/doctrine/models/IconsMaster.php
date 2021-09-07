<?php

	Doctrine_Manager::getInstance()->bindComponent('IconsMaster', 'SYSDAT');

	class IconsMaster extends BaseIconsMaster {

		/**
		 * ISPC-1896
		 * you can put this seetings as json_encode into IconsMaster -> icon_settings, and will use them instead of this defined here
		 */
		protected $_system_icon_settings = array(
		
				"model_icon_nomber_xxx"	=> array(
						array(	"type"		=> "checkbox",
								"name"		=> "checkbox_NAME",
								"values"	=> array(1),
								"default"	=> 0,
								"label"		=> "language['extra_icon_settings'][ this_cb_label ]",
								"attr"		=> null //array of attributes to use on this element
						),
						array(	"type"		=> "text",
								"name"		=> "text_NAME",
								"values"	=> array(),
								"default"	=> '',
								"label"		=> "language['extra_icon_settings'][ this_text_label ]",
								"attr"		=> null //array of attributes to use on this element
						),
						array(	"type" 		=> "radio",
								"name"		=> "radio_NAME",
								"values"	=> array(0=>"rad0",1=>"rad1"),
								"default"	=> 0,
								"label"		=> "language['extra_icon_settings'][ this_radio_label ]",
								"attr"		=> null //array of attributes to use on this element
						),
						array(	"type"		=> "select",
								"name"		=> "select_NAME",
								"values"	=> array(0=>"option0",1=>"option1",2,3,4,5),
								"default"	=> 5,
								"label"		=> "language['extra_icon_settings'][ this_select_label ]",
								"attr"		=> null //array of attributes to use on this element
						),
						array(	"type"		=> "textarea",
								"name"		=> "textarea_NAME",
								"values"	=> array(1),
								"default"	=> 0,
								"label"		=> "lebel",
								"attr"		=> null //array of attributes to use on this element
						),
						array(	"type"		=> "textarea77", // !!! this will result in error, since this helper dosen't exists
								"name"		=> "textarea_NAME",
								"values"	=> array(1),
								"default"	=> 0,
								"label"		=> "lebel",
								"attr"		=> null //array of attributes to use on this element
						),
				),
		
				//49 = vital_signs_icon
				"49" => array(
		
						array(	"type"		=> "checkbox",
								"name"		=> "weight",
								"values"	=> array(1),
								"default"	=> 1, // this was allready shown
								"label"		=> "vital_signs_icon_weight",
								"attr"		=> null
						),
						array(	"type"		=> "checkbox",
								"name"		=> "head_circumference",
								"values"	=> array(1),
								"default"	=> 0, 
								"label"		=> "vital_signs_icon_head_circumference",
								"attr"		=> null
						),
						array(	"type"		=> "checkbox",
								"name"		=> "waist_circumference",
								"values"	=> array(1),
								"default"	=> 1,
								"label"		=> "vital_signs_icon_waist_circumference",
								"attr"		=> null
						),
						array(	"type"		=> "checkbox",
								"name"		=> "height",
								"values"	=> array(1),
								"default"	=> 0,
								"label"		=> "vital_signs_icon_height",
								"attr"		=> null
						),
						array(	"type"		=> "checkbox",
								"name"		=> "oxygen_saturation",
								"values"	=> array(1),
								"default"	=> 0,
								"label"		=> "vital_signs_icon_oxygen_saturation",
								"attr"		=> null
						),
						array(	"type"		=> "checkbox",
								"name"		=> "temperature",
								"values"	=> array(1),
								"default"	=> 0,
								"label"		=> "vital_signs_icon_temperature",
								"attr"		=> null
						),
						array(	"type"		=> "checkbox",
								"name"		=> "blood_sugar",
								"values"	=> array(1),
								"default"	=> 0,
								"label"		=> "vital_signs_icon_blood_sugar",
								"attr"		=> null
						),
						array(	"type"		=> "checkbox",
								"name"		=> "blood_pressure",
								"values"	=> array(1),
								"default"	=> 0,
								"label"		=> "vital_signs_icon_blood_pressure",
								"attr"		=> null
						),
						array(	"type"		=> "checkbox",
								"name"		=> "bas",
								"values"	=> array(1),
								"default"	=> 0,
								"label"		=> "vital_signs_icon_bas",
								"attr"		=> null
						),
						array(	"type"		=> "checkbox",
								"name"		=> "allways_display",
								"values"	=> array(1),
								"default"	=> 0, 
								"label"		=> "vital_signs_icon_allways_display",
								"attr"		=> null
						),
				         array(	"type"		=> "checkbox",    //ISPC-1439 @Lore   03.10.2019
				                "name"		=> "bowel_movement",  
				                "values"	=> array(1),
				                "default"	=> 0,
				                "label"		=> "bowel_movement_icon",
				                "attr"		=> null
				    ),
				)
		);
		
		public function get_system_icon_settings(){
			return $this->_system_icon_settings;
			
		}
		public function get_system_icons($clientid, $icons_id = false, $hide_subicons = true, $only_subicons = false)
		{
		    
		    if ($icons_id === false) {
		        //original, do not filter by icons
		    } elseif (is_null($icons_id) || (is_array($icons_id) && empty($icons_id))) {
		        return; 
		        //return was created for ISPC-2138 -> PatientMaster::getMasterData()->$sys_icons->get_system_icons()
		    }
		    
			$logininfo = new Zend_Session_Namespace('Login_Info');

			// display icons by module visibility
			$showinfo = new Modules();
			$excluded_icons = array();

			
			$user2location = $showinfo->checkModulePrivileges("94", $logininfo->clientid);
			if(!$user2location)
			{
				array_push($excluded_icons, "40"); //  HEIMNETZ - user associated to locations
			}
			
			
			$teammmetingicons = $showinfo->checkModulePrivileges("178", $logininfo->clientid);
			if(!$teammmetingicons)
			{
				array_push($excluded_icons, "64"); //ISPC2261
				array_push($excluded_icons, "65"); //ISPC2261
				array_push($excluded_icons, "66"); //ISPC2261
				array_push($excluded_icons, "79");     //TODO-3707 Lore 06.01.2021
			}
			
			//Maria:: Migration CISPC to ISPC 22.07.2020
            $reassessmenticons = $showinfo->checkModulePrivileges("1008", $logininfo->clientid);
            if(!$reassessmenticons)
            {
                array_push($excluded_icons, "10006"); //ISPC2476
            }
            //ISPC-2912,Elena,25.05.2021
            $btmicons = $showinfo->checkModulePrivileges("1021", $logininfo->clientid);
            if(!$btmicons)
            {
                array_push($excluded_icons, "10010"); //ISPC2476
            }

			
			$s_icns = Doctrine_Query::create()
				->select('*')
				->from('IconsMaster');
			if($icons_id)
			{
				if(is_array($icons_id))
				{
					$s_icns->whereIn('id', $icons_id);
				}
				else
				{
					$s_icns->where('id = ? ', $icons_id);
				}
			}

			if(!empty($excluded_icons )){
				$s_icns->andWhereNotIn('id', $excluded_icons);
			}
			
			if($hide_subicons)
			{
				$s_icns->andWhere('function != ""');
			}

			if($only_subicons)
			{
				$s_icns->andWhere('function = ""');
			}

			$s_icns->orderBy('id ASC');

			$sys_icons = $s_icns->fetchArray();
			
			if (empty($sys_icons)) {
				$sys_items[] = '999999999';
			}
			foreach($sys_icons as $k_sys_icon => $v_sys_icon)
			{
				$sys_items[] = $v_sys_icon['id'];
			}

			$sys_custom = new IconsClient();
			$sys_icons_custom = $sys_custom->get_client_system_icons($clientid, $sys_items);

			$sys_custom_client = array();
			
			foreach($sys_icons_custom as $k_sys_custom => $v_sys_custom)
			{
				$sys_custom_client[$v_sys_custom['icon_id']] = $v_sys_custom;
			}

			foreach($sys_icons as $k_sys_icons => $v_sys_icons)
			{
				$system_icons[$v_sys_icons['id']] = $v_sys_icons;

				if(isset($sys_custom_client[$v_sys_icons['id']]) && $sys_custom_client[$v_sys_icons['id']])
				{
					$system_icons[$v_sys_icons['id']]['custom'] = $sys_custom_client[$v_sys_icons['id']];
					$system_icons[$v_sys_icons['id']]['color'] = $sys_custom_client[$v_sys_icons['id']]['color'];
					if(!empty($sys_custom_client[$v_sys_icons['id']]['image']))
					{
						$system_icons[$v_sys_icons['id']]['image'] = $sys_custom_client[$v_sys_icons['id']]['image'];
					}
				}
			}


			return $system_icons;
		}



		//Maria:: Migration CISPC to ISPC 22.07.2020
		public static function traffic_light_icons($filename, $status)
		{
			$icon_statuses = array('1' => 'green', '2' => 'yellow', '3' => 'red', '4' => 'black');
			$file_arr = explode('_', $filename);
			$file_last_part = explode('.', $file_arr[3]);
			$file_arr[3] = $icon_statuses[$status] . '.' . $file_last_part[1];

			return implode('_', $file_arr);
		}

	}

?>