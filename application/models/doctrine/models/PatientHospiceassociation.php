<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientHospiceassociation', 'SYSDAT');

	class PatientHospiceassociation extends BasePatientHospiceassociation {

		public function getPatientHospiceassociation($ipid, $h_association_id = false)
		{
			$sql = "*, id as h_association_id";
			$sql .=",hospice_association as h_association";
			$sql .=",h_association_comment as h_association_comment";
			$sql .=",street1 as h_association_street";
			$sql .=",zip as h_association_zip";
			$sql .=",city as h_association_city";
			$sql .=",phone_practice as h_association_phone_practice";
			$sql .=",phone_emergency as h_association_phone_emergency";
			$sql .=",fax as h_association_fax";

			if($h_association_id)
			{
				$q = "PatientHospiceassociation.h_association_id = " . $h_association_id . " and";
			}
			else
			{
				$q = "";
			}
			$drop = Doctrine_Query::create()
				->select($sql)
				->from('Hospiceassociation')
				->leftJoin('PatientHospiceassociation')
				->where("" . $q . " PatientHospiceassociation.h_association_id = Hospiceassociation.id and PatientHospiceassociation.ipid='" . $ipid . "' and PatientHospiceassociation.isdelete = 0 ");
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getPatientLastHospiceAssociation($ipid)
		{

			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientHospiceassociation')
				->where("ipid='" . $ipid . "' and isdelete = 0")
				->orderBy('id desc')
				->limit(1);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function clone_records($ipid, $target_ipid, $target_client)
		{
			$pfleges = $this->getPatientHospiceassociation($ipid);
			if($pfleges)
			{
				foreach($pfleges as $k_pfl => $v_pfl)
				{
					$pfl = new Hospiceassociation();
					$master_pfl = $pfl->clone_record($v_pfl['id'], $target_client);

					if($master_pfl)
					{

						$pfl_cl = new PatientHospiceassociation();
						
						//ISPC-2614 Ancuta 20.07.2020 :: deactivate listner for clone
						$pc_listener = $pfl_cl->getListener()->get('IntenseConnectionListener');
						$pc_listener->setOption('disabled', true);
						//--
						$pfl_cl->ipid = $target_ipid;
						$pfl_cl->h_association_id = $master_pfl;
						$pfl_cl->h_association_comment = $v_pfl['h_association_comment'];
						//ISPC-2614 Ancuta 16.07.2020	:: activate lister after clone
						$pc_listener->setOption('disabled', false);
						//--
						$pfl_cl->save();
					}
				}
				return $pfl->id;
			}
		}

		public function get_patient_hospiceassociations($ipids, $fetch_master_data = false)
		{
			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}

			if(count($ipids_arr) == 0)
			{
				$ipids_arr[] = '9999999999';
			}

			$Q = Doctrine_Query::create()
				->select('*')
				->from('PatientHospiceassociation')
				->whereIn('ipid', $ipids_arr)
				->andWhere('isdelete="0"')
				->orderBy('create_date DESC');
			$q_res = $Q->fetchArray();

			if($q_res)
			{
				if($fetch_master_data)
				{
					$hosp_assoc_ids[] = '999999999999';
					foreach($q_res as $k_res => $v_res)
					{
						$patient_hosp_assoc_details[$v_res['h_association_id']] = $v_res;
						$hosp_assoc_ids[] = $v_res['h_association_id'];
					}

					$hosp_assoc = new Hospiceassociation();
					$hosp_assoc_details = $hosp_assoc->get_hospiceassociations($hosp_assoc_ids);

					foreach($hosp_assoc_details as $k_hosp_assoc => $v_hosp_assoc)
					{
						$patient_hosp_assoc_details[$v_hosp_assoc['id']]['master'] = $v_hosp_assoc;
					}

					if($patient_hosp_assoc_details)
					{
						return $patient_hosp_assoc_details;
					}
				}
				else
				{
					return $q_res;
				}
			}
			else
			{
				return false;
			}
		}

	public static function beautifyName( &$usrarray )
	{
	    //mb_convert_case(nice_name, MB_CASE_TITLE, 'UTF-8'); ?
	    if ( empty($usrarray) || ! is_array($usrarray)) {
	        return;
	    }
	
	
	    foreach ( $usrarray as &$k )
	    {
	
	        if ( ! is_array($k) || isset($k['nice_name'])) {
	            continue; // varaible allready exists, use another name for the variable
	        }
	        if (isset($k['Hospiceassociation'])){
	            $k ['nice_name'] = trim($k['Hospiceassociation']['last_name']);
	            $k ['nice_name'] .= trim($k['Hospiceassociation']['first_name']) != "" ? (", " . trim($k['Hospiceassociation']['first_name'])) : "";
	        } else {
	            $k ['nice_name'] = trim($k['last_name']);
	            $k ['nice_name'] .= trim($k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
	        }
	    }
	}
}

?>