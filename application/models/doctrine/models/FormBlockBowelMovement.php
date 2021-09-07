<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlockBowelMovement', 'MDAT');

	class FormBlockBowelMovement extends BaseFormBlockBowelMovement {

		public function getPatientFormBlockBowelMovement($ipid, $contact_form_id, $allow_deleted = false)
		{

			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockBowelMovement')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('contact_form_id ="' . $contact_form_id . '"');

			if(!$allow_deleted)
			{
				$groups_sql->andWhere('isdelete = 0');
			}

			$groupsarray = $groups_sql->fetchArray();


			if($groupsarray)
			{
				return $groupsarray;
			}
		}

		public function get_multiple_block_bra_sapv($ipid, $contact_forms_ids)
		{

			$contact_forms_ids[] = '999999999';

			$block_data = Doctrine_Query::create()
				->select('*')
				->from('FormBlockBowelMovement')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhereIn('contact_form_id', $contact_forms_ids)
				->andWhere('isdelete="0"');
			$block_data_res = $block_data->fetchArray();

			if($block_data_res)
			{
				foreach($block_data_res as $k_block_res => $v_block_res)
				{
					$block_data_arr[$v_block_res['contact_form_id']] = $v_block_res;
				}

				return $block_data_arr;
			}
			else
			{
				return false;
			}
		}
		
		/*
		 * ISPC-1439 @Lore 03.10.2019
		 * #ISPC-2512PatientCharts
		 * */
		public static function get_patients_chart($ipids, $period = false)
		{
		    if ( empty($ipids)) {
		        return;
		    }
		    
		    if( ! is_array($ipids))
		    {
		        $ipids = array($ipids);
		    }
		    else
		    {
		        $ipids = $ipids;
		    }
		    
		    
		    $cf = new ContactForms();
		    $delcf = $cf->get_patients_deleted_contactforms($ipids);
		    
		    $delcform = array();
		    
		    foreach ($delcf as $key_ipid => $valcf)
		    {
		        foreach($valcf as $kdcf=>$vcfdel)
		        {
		            $delcform[] = $vcfdel;
		        }
		    }
		    
		        
		    $sql_period_params = array();
		    
		    if($period)
		    {
		        // 		        $sql_period = ' AND DATE(signs_date) != "1970-01-01"  AND DATE(signs_date) BETWEEN DATE("'.$period['start'].'") AND  DATE("'.$period['end'].'") ';
		        
		        $sql_period = ' (DATE(bowel_movement_date) != "1970-01-01" AND DATE(bowel_movement_date) BETWEEN DATE(?) AND DATE(?) ) ';
		        
		        $sql_period_params = array( $period['start'], $period['end'] );
		    }
		    else
		    {
		        $sql_period = ' DATE(bowel_movement_date) != "1970-01-01"  ';
		    }
		    
		    $patient = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockBowelMovement')
		    ->where('isdelete= "0" ')
		    ->andWhere('bowel_movement != 0')
		    ->andWhere('DATE(bowel_movement_date) != "0000-00-00" ')
		    ->andWhereIn('ipid', $ipids)
		    ->orderBy('bowel_movement_date ASC');
		    
		    if ( ! empty($delcform)) {
		        $patient->andwhereNotIn("contact_form_id",$delcform);
		    }
		    
		    if ( ! empty($sql_period)) {
		        $patient->andWhere( $sql_period , $sql_period_params);
		    }
		    
		    
		    $patientlimit = $patient->fetchArray();
		    
		    $info_chart = array();
		    
		    foreach($patientlimit as $key_cf  => $val_cf)
		    {
		        $info_chart[$val_cf['ipid']][$key_cf]['bowel_movement'] = $val_cf['bowel_movement'];
		        $info_chart[$val_cf['ipid']][$key_cf]['bowel_movement_description'] = $val_cf['bowel_movement_description'];
		        $info_chart[$val_cf['ipid']][$key_cf]['bowel_movement_date'] = $val_cf['bowel_movement_date'];
		        
		    }
		    
		    return $info_chart;
		}
		

	}

?>