<?php
/**
 * @cla on 11.06.2018
 * + $this->_client_hasModule()
 * ex: IF ($this->_client_hasModule(111)) THEN ...
 * please do not use $modules = new Module(); $modules->checkModulePrivileges("111", $logininfo->clientid) 
 * 
 * 
 * IF A FUNCTION IS NON-STATIC .. CALL IT FROM AN INSTANCE !
 * 
 */
Doctrine_Manager::getInstance()->bindComponent('IconsPatient', 'SYSDAT');

class IconsPatient extends BaseIconsPatient 
{
    
    /**
     * @var array
     */
    private $_clientModules = null;
    
    /**
     * finds patients with icons
     * IM-59
     *
     * @param $icons
     * @return array //Maria:: Migration CISPC to ISPC 22.07.2020
     */
    public static function getPatientsWithIcon($icons){
        //IM-59
        if(!is_array($icons)){
            $icons=array($icons);
        }

        $query = Doctrine_Query::create()
            ->select('ipid')
            ->from('IconsPatient')
            ->whereIn('icon_id', $icons)
            //->andWhere('isdelete = "0"') // this field doesn't exist in ambu - elena
        ;
        $res = $query->fetchArray();

        $return=array();
        foreach ($res as $item){
            $return[]=$item['ipid'];
        }

        return $return;
    }

    /**
     * !! this replaces new Module()...
     * 
     * @param number $module_id
     * @return boolean
     */
    private function _client_hasModule( $module_id = 0) 
    {
       
        if ($module_id > 0 
            && ! is_null($this->_clientModules) 
            && isset( $this->_clientModules[$module_id])) 
        {
            return true;
            
        } else {
            
            return false;
        }
    }
    
    public function __construct() {
        
        $modules = new Modules();
        
        $this->_clientModules = $modules->get_client_modules();
        
        parent::__construct();
    }

		public function get_patient_icons($ipids)
		{
			$icns = Doctrine_Query::create()
				->select('*')
				->from('IconsPatient');
			if(is_array($ipids))
			{
				$icns->andWhereIn('ipid', $ipids);
			}
			else
			{
				$icns->andWhere('ipid="' . $ipids . '"');
			}
			$icns->orderBy('id ASC');
			$icons = $icns->fetchArray();

			return $icons;
		}

		public function get_patient_icons_allowed($ipids, $allowed_icons = false)
		{
			$icns = Doctrine_Query::create()
				->select('*')
				->from('IconsPatient');
			if(is_array($ipids))
			{
				$icns->andWhereIn('ipid', $ipids);
			}
			else
			{
				$icns->andWhere('ipid="' . $ipids . '"');
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

    /**
     * @author Elena
     * ISPC-2476 RE-Assessment Nordrhein
     *
     * @param $ipids
     * @return array //Maria:: Migration CISPC to ISPC 22.07.2020
     */
		public function get_reassessment_data($ipids){
        $result = array();
        if (empty($ipids))
        {
            return $result; //fail-safe
        }
        if(!is_array($ipids)){
            $ipids = [$ipids];
        }

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $query = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockKeyValue')
            ->whereIn('ipid', $ipids)
            ->andWhere('block=?', 'reassessment')
            ->andWhere('isdelete=?', 0)
            ->orderBy('id DESC')
        ;
        $res = $query->fetchArray();
        $last_reassessment = array();
        $reassessment_summary = array();
        $reassessments_next = array();
        $reassessments_complete = array();
        $reassessments_ipids = [];
        foreach($res as $entry){
            if(!isset($reassessments[$entry['ipid']])){
                $reassessments[$entry['ipid']] = [];
                $reassessments_ipids[] = $entry['ipid'];

            }
            $data = json_decode($entry['v']);
            if(isset($data->next_assessment_at) && strval($data->next_assessment_at) !== 'false'){

                if(!isset( $reassessments_next[$entry['ipid']])){
                    $reassessments_next[$entry['ipid']] = $data->next_assessment_at;
                }else{
                    if(date_create_from_format('d.m.Y', $data->next_assessment_at) > date_create_from_format('d.m.Y', $reassessments_next[$entry['ipid']] )){
                        $reassessments_next[$entry['ipid']] = $data->next_assessment_at;
                    }

                }
            }


            if(isset($data->completed_at) && $data->completed_at !== 'false'){
                if(!isset($last_reassessment[$entry['ipid']])){
                    $last_reassessment[$entry['ipid']] = $data->completed_at;
                    $reassessment_summary[$entry['ipid']] = isset($data->summary->text) ? $data->summary->text : '';
                }else{
                    $completed_at = $data->completed_at;
                    if(date_create_from_format('d.m.Y', $completed_at) > date_create_from_format('d.m.Y', $last_reassessment[$entry['ipid']])){
                        $last_reassessment[$entry['ipid']] = $data->completed_at;
                        $reassessment_summary[$entry['ipid']] = isset($data->summary->text) ? $data->summary->text : '';
                    }
                }

            }


      }
        $reassessment_data = [];
        $reassessment_data['ipids'] = $reassessments_ipids;
        $reassessment_data['patient_reassessments_data'] = $last_reassessment;
        $reassessment_data['patient_reassessments_summary'] = $reassessment_summary;
        $reassessment_data['patient_reassessments_next'] = $reassessments_next;

        //print_r($reassessments);
        return $reassessment_data;

    }


		public function filter_patient_icons($ipids, $icons = false)
		{
			if($icons)
			{
				//filter
				$icns = Doctrine_Query::create()
					->select('*')
					->from('IconsPatient');
				if(is_array($ipids))
				{
					$icns = $icns->whereIn('ipid', $ipids);
				}
				else
				{
					$icns = $icns->where('ipid LIKE "' . $ipids . '"');
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

				$ipids_out[] = '999999999';
				foreach($icons_res as $k_icon_res => $v_icon_res)
				{
					$ipids_out[] = $v_icon_res['ipid'];
				}
			}
			else
			{ // do not filter.. no icons selected...
				$ipids_out = $ipids;
			}

			return $ipids_out;
		}

		public function get_sapv_patients($ipids = false, $filter = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$sapv_arr = array('1' => 'b', '2' => 'k', '3' => 't', '4' => 'v');
			$sapv_status_array = SapvVerordnung::getSapvRadios();

			$sapv_data = array();
			$sapv_data_all = array();
			
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid, e.epid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
					$active_patients[$v_act_ipid['ipid']] = $v_act_ipid; //patient details
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}


			if($filter)
			{
				$filter_arr = explode('_', $filter);
			}

			$act_ipids = array_unique($act_ipids);

			//get patient(s) sapvs
			$sapv_res = SapvVerordnung::get_today_active_sapvs($act_ipids);

			//get sapv images custom or not
			$icons_client = new IconsMaster();
			$icons_sapv = $icons_client->get_system_icons($clientid, false, false, true);

			foreach($icons_sapv as $k_icon_sapv => $v_icon_sapv)
			{
				$icons_sapv_map[$v_icon_sapv['name']] = $v_icon_sapv;
			}

			$sapv_loop_verordnet = array();
			$sapv_loop_statuses = array();

			foreach($sapv_res as $k_sapv => $v_sapv)
			{
				if(!empty($v_sapv['verordnet']))
				{
					if($v_sapv['status'] == '0')
					{
						$sapv_status = '3';
					}
					else
					{
						$sapv_status = $v_sapv['status'];
					}

					if(empty($sapv_loop_verordnet))
					{
						$high_veordnet_loop[$v_sapv['ipid']][] = '0';
					}
					else
					{
						$high_veordnet_loop[$v_sapv['ipid']][] = end($sapv_loop_verordnet);
					}

					$sapv_verordnet_details = explode(',', $v_sapv['verordnet']);
					asort($sapv_verordnet_details);
					$high_verordnet = end($sapv_verordnet_details);

					$sapv_loop_statuses[$v_sapv['ipid']][] = $sapv_status;

					//get status 2 only or status 1 or 3 if 2 is not present in loop array && only highest verordnet
					if(($sapv_status == '2' || ( ($sapv_status == 1 || $sapv_status == 3) && !in_array('2', $sapv_loop_statuses[$v_sapv['ipid']]) )) && $high_verordnet >= $high_veordnet_loop)
					{
						$sapv_loop_verordnet[] = $high_verordnet;
						asort($sapv_loop_verordnet);
					}
				}
			}

			foreach($sapv_res as $k_sapv => $v_sapv)
			{
				if(!empty($v_sapv['verordnet']))
				{
					if($v_sapv['status'] == '0')
					{
						$sapv_status = '3';
					}
					else
					{
						$sapv_status = $v_sapv['status'];
					}

					if(count($sapv_loop_verordnet[$v_sapv['ipid']]) == '0')
					{
						$high_veordnet_loop = '0';
					}
					else
					{
						$high_veordnet_loop = end($sapv_loop_verordnet[$v_sapv['ipid']]);
					}

					$sapv_verordnet_details[$v_sapv['ipid']] = explode(',', $v_sapv['verordnet']);
					asort($sapv_verordnet_details[$v_sapv['ipid']]);
					$high_verordnet = end($sapv_verordnet_details[$v_sapv['ipid']]);

					$sapv_loop_statuses[$v_sapv['ipid']][] = $sapv_status;

					//get status 2 only or status 1 or 3 if 2 is not present in loop array && only highest verordnet
					if(($sapv_status == '2' || ( ($sapv_status == 1 || $sapv_status == 3) && !in_array('2', $sapv_loop_statuses[$v_sapv['ipid']]) )) && $high_verordnet >= $high_veordnet_loop)
					{
						$sapv_loop_verordnet[$v_sapv['ipid']][] = $high_verordnet;
						asort($sapv_loop_verordnet[$v_sapv['ipid']]);

						$sapv_data['ipids'][] = $v_sapv['ipid'];
						$sapv_data['details'][$v_sapv['ipid']][] = $v_sapv;
						$sapv_data['last'][$v_sapv['ipid']] = $v_sapv;
						$sapv_data['last'][$v_sapv['ipid']]['max_verordnet'] = $high_verordnet;
						$sapv_data['last'][$v_sapv['ipid']]['max_verordnet_patientinfo'] = $sapv_verordnets[$high_verordnet];
						$sapv_data['last'][$v_sapv['ipid']]['image'] = $icons_sapv_map['sapv_' . $sapv_arr[$high_verordnet] . '_' . $sapv_status]['image'];
						$sapv_data['last'][$v_sapv['ipid']]['name'] = 'sapv_' . $sapv_arr[$high_verordnet] . '_' . $sapv_status;
						$sapv_data['last'][$v_sapv['ipid']]['color'] = $icons_sapv_map['sapv_' . $sapv_arr[$high_verordnet] . '_' . $sapv_status]['color'];
						$sapv_data['last'][$v_sapv['ipid']]['status'] = $sapv_status;
						$sapv_data['last'][$v_sapv['ipid']]['sapv_status'] = $sapv_status_array[$sapv_status];
					}
				}
			}

			if($filter)
			{
				$filter_arr = explode('_', $filter);
				foreach($sapv_data['last'] as $k_ipid => $v_sapv_last)
				{
					if($v_sapv_last['status'] == $filter_arr['2'] && $v_sapv_last['max_verordnet'] == array_search($filter_arr[1], $sapv_arr))
					{
						$sapv_data_all['ipids'][] = $k_ipid;
					}
				}
			}
			else
			{
				$sapv_data_all['ipids'] = $sapv_data['ipids'];
			}

			$sapv_data['ipids'] = array_values(array_unique($sapv_data_all['ipids']));

			return $sapv_data;
		}
			
		public function OLD_get_workers_patients($ipids = false)
		{
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
		
				//get client patients
				$actpatient = Doctrine_Query::create()
				->select("p.ipid, e.epid")
				->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');
		
				$actipidarray = $actpatient->fetchArray();
		
				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
					$active_patients[$v_act_ipid['ipid']] = $v_act_ipid; //patient details
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}
		
			//get patients voluntary worker
			$workers = Doctrine_Query::create()
			->select('*')
			->from('PatientVoluntaryworkers')
			->whereIn('ipid', $act_ipids)
			->andWhere('isdelete = "0"')
			->andWhere('vwid != "0"')
			->andWhere('date(start_date) <= CURDATE() ')
			->andWhere('end_date = "0000-00-00 00:00:00"  OR  date(end_date) >= CURDATE() ')
			->orderBy('id desc');
			$workers_res = $workers->fetchArray();
		
			foreach($workers_res as $k_worker => $v_worker)
			{
				$patients_with_workers[] = $v_worker['ipid'];
			}
				
			if(empty( $patients_with_workers)){
				$patients_with_workers[] = "999999999";
			}
				
			$patients_with_workers = array_unique($patients_with_workers);
				
			// get discharged patients
			$patients_d = Doctrine_Query::create()
			->select("*")
			->from('PatientDischarge')
			->whereIn('ipid', $patients_with_workers)
			->andWhere('isdelete = "0"')
			->OrderBy('discharge_date desc')
			->groupBy('ipid');
			$discharge_array = $patients_d->fetchArray();
		
			$discharged_patients[] = '999999999';
			foreach($discharge_array as $pk => $dis_data){
				$discharge_data[$dis_data['ipid']] =$dis_data['discharge_date'];
				$discharged_patients[] = $dis_data['ipid'];
			}
				
				
				
				
			$workers_data_sql['workers_ids'][] = '999999999';
			foreach($workers_res as $k_worker => $v_worker)
			{
				$workers_data_sql['workers_ids'][] = $v_worker['vwid'];
			}
		
			$workers_data_sql['workers_ids'] = array_values(array_unique($workers_data_sql['workers_ids']));
		
			//get patients workers data(master)
			$master_workers = Doctrine_Query::create()
			->select('*')
			->from('Voluntaryworkers')
			->whereIn('id', $workers_data_sql['workers_ids'])
			->andWhere('isdelete = "0"');
			$master_workers_res = $master_workers->fetchArray();
		
			foreach($master_workers_res as $k_master_worker => $v_master_worker)
			{
				$master_worker[$v_master_worker['id']] = $v_master_worker;
			}
		
				
			foreach($workers_res as $k_worker_r => $v_worker_r)
			{
				if(in_array($v_worker_r['ipid'],$discharged_patients)){
					 
					if($v_worker_r['end_date'] == "0000-00-00 00:00:00" ){
						$v_worker_r['end_date'] =  $discharge_data[$v_worker_r['ipid']];
					}
		
					if($v_worker_r['end_date'] != "0000-00-00 00:00:00" && strtotime($v_worker_r['end_date']) >= time() ){
						$workers_data['workers_ids'][] = $v_worker_r['vwid'];
						$workers_data['patient_workers_data'][$v_worker_r['ipid']][$v_worker_r['vwid']] = $v_worker_r;
						$workers_data['master_workers_data'][$v_worker_r['ipid']][$v_worker_r['vwid']] = $master_worker[$v_worker_r['vwid']];
						$workers_data['ipids'][] = $v_worker_r['ipid'];
					}
		
				} else {
					if( ($v_worker_r['end_date'] != "0000-00-00 00:00:00" && strtotime($v_worker_r['end_date']) >= time()) || $v_worker_r['end_date'] == "0000-00-00 00:00:00"   ){
						$workers_data['workers_ids'][] = $v_worker_r['vwid'];
						$workers_data['patient_workers_data'][$v_worker_r['ipid']][$v_worker_r['vwid']] = $v_worker_r;
						$workers_data['master_workers_data'][$v_worker_r['ipid']][$v_worker_r['vwid']] = $master_worker[$v_worker_r['vwid']];
						$workers_data['ipids'][] = $v_worker_r['ipid'];
					}
					 
				}
				 
				 
			}
				
			return $workers_data;
		}
		
		//ISPC-1958
		public function get_workers_patients($ipids = false)
		{
			$act_ipids = array();
			$active_patients = array();
			$workers_data = array();
			
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
		
				//get client patients
				$actpatient = Doctrine_Query::create()
				->select("p.ipid, e.epid")
				->from('PatientMaster p')
				->leftJoin("p.EpidIpidMapping e")
				->where('e.clientid = ?' , $clientid)
				->andwhere('p.isdelete = 0')
				->fetchArray();
		
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
					$active_patients[$v_act_ipid['ipid']] = $v_act_ipid; //patient details
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}
		
			if (empty($act_ipids)) {
				return; //no ipids
			}
				
				
			//get patients voluntary worker
			$all_workers_res = Doctrine_Query::create()
			->select('
					pvw.id,
					pvw.ipid,
					pvw.vwid,
					pvw.vw_comment,
					pvw.start_date,
					pvw.end_date,
					pvw.create_date,
					pvw.change_date,
					vw.*
			')
			->from('PatientVoluntaryworkers pvw')
			->whereIn('pvw.ipid', $act_ipids)
			->andWhere('pvw.isdelete = 0')
			->andWhere('pvw.vwid != 0')
// 			->andWhere('DATE(pvw.start_date) <= CURDATE() ')
			->orderBy('id DESC')
			->innerJoin("pvw.Voluntaryworkers vw")
			->fetchArray();
			
			$all_workers = array();
			$workers_res = array();
			
			$today = strtotime('today');
			
			if ( ! empty($all_workers_res)) {
			    
    			foreach ($all_workers_res as $row) {
    			    
    			    $row['Localized_start_date'] = (! empty($row['start_date']) && $row['start_date'] != '0000-00-00 00:00:00') ? date('d.m.Y', strtotime($row['start_date'])) : null;
    			    $row['Localized_end_date'] = (! empty($row['end_date']) && $row['end_date'] != '0000-00-00 00:00:00') ? date('d.m.Y', strtotime($row['end_date'])) : null;

    			    $all_workers[$row['ipid']][$row['id']] = $row;
    			    
    			    //get only the ones that have allready started the work on him
    			    if (empty($row['start_date']) 
    			        || $row['start_date'] == "0000-00-00 00:00:00" 
    			        || strtotime($row['start_date']) <= $today) 
    			    {
    			        $workers_res[$row['id']] = $row;
    			    }
    			}
			}
			$workers_data['all_workers_res'] = $all_workers; // this will be used in stammdaten
			
			
			
			
			if ( empty($workers_res)) {
				return $workers_data; //no voluntary worker for this ipids
			}

			$patients_with_workers =  array_unique(array_column($workers_res, 'ipid'));
											
			// get discharged patients
			$discharge_array = Doctrine_Query::create()
			->select("id, ipid, discharge_date, create_date")
			->from('PatientDischarge INDEXBY ipid')
			->whereIn('ipid', $patients_with_workers)
			->andWhere('isdelete = 0')
			->orderBy('discharge_date desc')
			->groupBy('ipid')
			->fetchArray();
			
			
			foreach($workers_res as $row)
			{

				$workers_data['workers_ids'][] = $row['vwid']; //$v_worker_r['vwid'];
				$workers_data['patient_workers_data'][$row['ipid']][$row['vwid']] = $row;
				$workers_data['ipids'][] = $row['ipid'];
				
				$row['Voluntaryworkers']['pvw_comment'] = $row['vw_comment'];
				
				
				if ($row['end_date'] == "0000-00-00 00:00:00") {
					if (array_key_exists($key, $discharge_array)) {
						//this ipid is discharged, but the ehremanliche is set as active
						//this IF would have never existed, if we had prevented adding vw's to discharged or dead patients
						
						$date_when_vw_was_added = $row['change_date'];
						if ($date_when_vw_was_added == "0000-00-00 00:00:00") {
							$date_when_vw_was_added = $row['create_date'];
						}
						
						//the next IF is NOT 100% correct... because discharge_date is manualy entered.. we should use create_date of discharge_date ?
						
						if ( strtotime($discharge_array[ $row['ipid'] ] ['discharge_date']) < strtotime($date_when_vw_was_added) ) {
							//vw was added after the discharge, so it's  a New-Active vw
							$workers_data['master_workers_data'][$row['ipid']] ['active'] [] = $row['Voluntaryworkers'];
							
						} else {
							//vw was added before the discharge, so it's an OLD vw that does not have a end_date
							$workers_data['master_workers_data'][$row['ipid']] ['old'] [] = $row['Voluntaryworkers'];
							
						}
						
					} else {
						//this vw is New-Active
						$workers_data['master_workers_data'][$row['ipid']] ['active'] [] = $row['Voluntaryworkers'];
					}
				} 
				else {
					if (array_key_exists($key, $discharge_array)) {
						//this should be marked as OLD allways
						$workers_data['master_workers_data'][$row['ipid']] ['old'] [] = $row['Voluntaryworkers'];
					} else {
						//check if end_date is in the past
						if ( strtotime('today UTC') > strtotime($row['end_date']) ) {
							//this is OLD vw
							$workers_data['master_workers_data'][$row['ipid']] ['old'] [] = $row['Voluntaryworkers'];
							
						} else {
							//this vw is still active
							$workers_data['master_workers_data'][$row['ipid']] ['active'] [] = $row['Voluntaryworkers'];
						}
					}
					
				}
				
				ksort($workers_data['master_workers_data'][$row['ipid']]);
				
			}

			return $workers_data;
		}
		
		
		public function get_vollversorgung_patients($ipids = false, $deleted = false)
		{

			if(!$ipids)
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid, e.epid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
				}
			}
			else
			{
				if(is_array($ipids))
				{//ipid list
					$act_ipids = $ipids;
				}
				else
				{//only one ipid
					$act_ipids[] = $ipids;
				}
			}
			$act_ipids[] = '99999999999';
			$vv_pats = Doctrine_Query::create()
				->select("p.ipid, e.epid")
				->from('PatientMaster p')
				->whereIn('ipid', $act_ipids)
				->andWhere('vollversorgung = 1');
			$vv_patients = $vv_pats->fetchArray();

			$v_vv_patients_ipids[] = '999999';
			foreach($vv_patients as $k_vv_patient => $v_vv_patient)
			{
				$v_vv_patients_ipids[] = $v_vv_patient['ipid'];
			}

			$vv_hist = Doctrine_Query::create()
				->select("*")
				->from('VollversorgungHistory')
				->whereIn('ipid', $v_vv_patients_ipids);

			if($deleted === false)
			{
				$vv_hist->andWhere('isdelete ="0"');
			}

			$vv_hist->orderBy("id ASC");
			$vv_history = $vv_hist->fetchArray();

			$date_types = array('1' => 'start', '2' => 'end');
			$vv_history_periods['ipids'][] = '999999999';
			foreach($vv_history as $k_vv_hist => $v_vv_hist)
			{
				$vv_history_periods[$v_vv_hist['ipid']][$date_types[$v_vv_hist['date_type']]][] = $v_vv_hist;
				$vv_history_periods['ipids'][] = $v_vv_hist['ipid'];
			}

			$vv_history_periods['ipids'] = array_values(array_unique($vv_history_periods['ipids']));
			return $vv_history_periods;
		}

		public function get_pflegedienst_patients($ipids = false, $deleted = false)
		{
		    $patients_pfleges = array();
		    
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid, e.epid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}
//			print_r($act_ipids);
			$patient_pfleges = Doctrine_Query::create()
				->select('*')
				->from('PatientPflegedienste')
				->WhereIn('ipid', $act_ipids)
				->andWhere('isdelete = 0')
				->andWhere('pflege_emergency = 0 OR pflege_emergency = 1 ') // 0 -  just pflege, 1 - pflege and emergency pflege
				->orderBy('create_date desc');

			$pat_pfleges_res = $patient_pfleges->fetchArray();

			$pflege_ids[] = '999999999';
			foreach($pat_pfleges_res as $k_pat_pflege => $v_pat_pflege)
			{
				$pflege_ids[] = $v_pat_pflege['pflid'];
			}

			$pfleges = Doctrine_Query::create()
				->select('*')
				->from('Pflegedienstes')
				->WhereIn('id', $pflege_ids)
				->andWhere('isdelete = 0')
				->orderBy('id ASC');

			$pfleges_res = $pfleges->fetchArray();

			foreach($pfleges_res as $k_pflege => $v_pflege)
			{
				$pflegedienstes[$v_pflege['id']] = $v_pflege;
			}

//			$patients_pfleges['ipids'][] = '999999999';
			foreach($pat_pfleges_res as $k_pflege_m => $v_pflege_m)
			{
				$patients_pfleges['patient_pflege'][$v_pflege_m['ipid']][$v_pflege_m['pflid']] = $v_pflege_m;
				$patients_pfleges['master_pflege'][$v_pflege_m['ipid']][$v_pflege_m['pflid']] = $pflegedienstes[$v_pflege_m['pflid']];
				$patients_pfleges['ipids'][] = $v_pflege_m['ipid'];
				$patients_pfleges['pflege_ipids'][] = $v_pflege_m['ipid'];
			}
			$patients_pfleges['ipids'] = array_values(array_unique($patients_pfleges['ipids']));

			return $patients_pfleges;
		}

		/**
		 * auth ?
		 * 
		 * TODO-2792 Ancuta - changed function -  added condition for living will tab only(08.01.2020)
		 * @param boolean $ipids
		 * @return void|array
		 */
		public function get_living_will_patients($ipids = false)
		{
			if (empty($ipids)) {
				return;
			}
			
			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			else
			{
				$ipids = $ipids;
			}
			
			
			$acp_m = new PatientAcp();
			$acp_ipids_data= $acp_m->getByIpid($ipids);
		
			
			foreach($acp_ipids_data as $k_ipid => $v_data)
			{
				foreach($v_data as $k=> $ldata){
					if($ldata['division_tab'] == 'living_will' && $ldata['active'] == "yes"){
						$patients['living_will'][$ldata['ipid']][] = $ldata;
						$patients['ipids'][] = $ldata['ipid'];
					}
				}
			}
			$patients['ipids'] = array_values(array_unique($patients['ipids']));
			
			return $patients;
		}
		

		public function get_living_will_patients_171018($ipids = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			//get client patients
			$actpatient = Doctrine_Query::create()
				->select("ipid, living_will, living_will_from, living_will_deposited")
				->from('PatientMaster p');
			$actpatient->leftJoin("p.EpidIpidMapping e");
			$actpatient->where('e.clientid = ' . $clientid);
			$actpatient->andwhere('p.isdelete = 0');
			$actpatient->andwhere('p.living_will = 1'); //get only those with living_will = 1;

			if($ipids)
			{
				if(is_array($ipids))
				{
					$actpatient->andWhereIn('ipid', $ipids);
				}
				else
				{
					$actpatient->andWhere('ipid LIKE "' . $ipids . '"');
				}
			}

			$actipidarray = $actpatient->fetchArray();

//			$patients['ipids'][] = '999999999';
			foreach($actipidarray as $k_act_ipid => $v_act_ipid)
			{
				$patients['living_will'][$v_act_ipid['ipid']] = $v_act_ipid;
				$patients['ipids'][] = $v_act_ipid['ipid'];
			}
			$patients['ipids'] = array_values(array_unique($patients['ipids']));

			return $patients;
		}

		public function get_patients_status($ipids = false, $filter = false, $filter_value = false, $details_included = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$sys_icons = new IconsMaster();
			$sys_sub_icons = $sys_icons->get_system_icons($clientid, false, false, false);
			foreach($sys_sub_icons as $k_sys_sub_icon => $v_sys_sub_icon)
			{
				if(empty($v_sys_sub_icon['function']))
				{
					$sys_subicons[$v_sys_sub_icon['name']] = $v_sys_sub_icon;
				}
				else
				{
					$system_icons[$v_sys_sub_icon['name']] = $v_sys_sub_icon;
				}
			}

			if($details_included)
			{
				if(is_array($ipids))
				{
					foreach($ipids as $pat_ipid => $pat_details)
					{
						$patients[] = $pat_ipid;

						$patient_status[$pat_ipid]['locations'] = array();
						$patient_status[$pat_ipid]['last_location'] = array();
						$patient_status[$pat_ipid]['discharge_details'] = array();
						$patient_status[$pat_ipid]['isdischarged'] = $pat_details['isdischarged'];
						$patient_status[$pat_ipid]['isstandby'] = $pat_details['isstandby'];
						$patient_status[$pat_ipid]['isstandbydelete'] = $pat_details['isstandbydelete'];
						$patient_status[$pat_ipid]['inhosp'] = '0'; //default
						$patient_status[$pat_ipid]['isdead'] = '0'; //default
						$patient_status[$pat_ipid]['traffic_status'] = $pat_details['traffic_status']; //default
						$patient_status[$pat_ipid]['condition'] = 'patient_status_icon';

						if($pat_details['isstandby'] == '1')
						{
							$patient_status[$pat_ipid]['condition'] = 'is_standby_icon';
							$patient_status[$pat_ipid]['show'] = $sys_subicons['is_standby_icon'];
						}
						else if($pat_details['isstandbydelete'] == '1')
						{
							$patient_status[$pat_ipid]['condition'] = 'is_standbydelete_icon';
							$patient_status[$pat_ipid]['show'] = $sys_subicons['is_standbydelete_icon'];
						}
						else if($pat_details['isdischarged'] == '1')
						{
							$patient_status[$pat_ipid]['condition'] = 'is_discharged_icon';
							$patient_status[$pat_ipid]['show'] = $sys_subicons['is_discharged_icon'];
						}

						if($patient_status[$pat_ipid]['condition'] == 'patient_status_icon')
						{
							$patient_status[$pat_ipid]['show'] = $system_icons['patient_status_icon']; //default
							$patient_status[$pat_ipid]['show']['image'] = $sys_icons->traffic_light_icons($system_icons['patient_status_icon']['image'], $pat_details['traffic_status']); //default
						}
					}

					if(empty($patients))
					{
						$patients[] = '999999999';
					}
				}
			}
			else
			{
				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("*")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				if($ipids)
				{
					if(is_array($ipids))
					{
						$actpatient->andWhereIn('e.ipid', $ipids);
					}
					else
					{
						$actpatient->andWhere('e.ipid LIKE "' . $ipids . '"');
					}
				}

				$actipidarray = $actpatient->fetchArray();
				$patients[] = '999999999';
				foreach($actipidarray as $k_actipidarray => $v_actipidarray)
				{
					$patients[] = $v_actipidarray['ipid'];

					$patient_status[$v_actipidarray['ipid']]['locations'] = array();
					$patient_status[$v_actipidarray['ipid']]['last_location'] = array();
					$patient_status[$v_actipidarray['ipid']]['discharge_details'] = array();
					$patient_status[$v_actipidarray['ipid']]['isdischarged'] = $v_actipidarray['isdischarged'];
					$patient_status[$v_actipidarray['ipid']]['isstandby'] = $v_actipidarray['isstandby'];
					$patient_status[$v_actipidarray['ipid']]['isstandbydelete'] = $v_actipidarray['isstandbydelete'];
					$patient_status[$v_actipidarray['ipid']]['inhosp'] = '0'; //default
					$patient_status[$v_actipidarray['ipid']]['isdead'] = '0'; //default
					$patient_status[$v_actipidarray['ipid']]['traffic_status'] = $v_actipidarray['traffic_status']; //default
					$patient_status[$v_actipidarray['ipid']]['condition'] = 'patient_status_icon';

					if($v_actipidarray['isstandby'] == '1')
					{
						$patient_status[$v_actipidarray['ipid']]['condition'] = 'is_standby_icon';
						$patient_status[$v_actipidarray['ipid']]['show'] = $sys_subicons['is_standby_icon'];
					}
					else if($v_actipidarray['isstandbydelete'] == '1')
					{
						$patient_status[$v_actipidarray['ipid']]['condition'] = 'is_standbydelete_icon';
						$patient_status[$v_actipidarray['ipid']]['show'] = $sys_subicons['is_standbydelete_icon'];
					}
					else if($v_actipidarray['isdischarged'] == '1')
					{
						$patient_status[$v_actipidarray['ipid']]['condition'] = 'is_discharged_icon';
						$patient_status[$v_actipidarray['ipid']]['show'] = $sys_subicons['is_discharged_icon'];
					}

					if($patient_status[$v_actipidarray['ipid']]['condition'] == 'patient_status_icon')
					{
						$patient_status[$v_actipidarray['ipid']]['show'] = $system_icons['patient_status_icon']; //default
						$patient_status[$v_actipidarray['ipid']]['show']['image'] = $sys_icons->traffic_light_icons($system_icons['patient_status_icon']['image'], $v_actipidarray['traffic_status']); //default
					}
				}
			}

			$patients = array_values(array_unique($patients));

			//ISPC-1948 Lore 21.08.2020
			//get master locations in_holiday
			$holidayids = Doctrine_Query::create()
			->select("id")
			->from('Locations')
			->where('isdelete = 0')
			->andWhere('client_id ="' . $clientid . '"')
			->andWhere('location_type = 11');
			$holi_array = $holidayids->fetchArray();
			
			$holi_ids = array();
			foreach($holi_array as $holiday)
			{
			    $holi_ids[] = $holiday['id'];
			}
			//.
			


			//Nico 27.10.2020:: ISPC-2696 No Hospital icon for clinic
            $omit_hospital_icon = Modules::checkModulePrivileges("1013", $clientid);
            if($omit_hospital_icon){
                $hosp_ids[] = '999999999';
            }else {
				//get master locations
				$hospitalids = Doctrine_Query::create()
// 				->select("*")
					->select("id")
					->from('Locations')
					->where('isdelete = 0')
					->andWhere('client_id ="' . $clientid . '"')
					->andWhere('location_type =1');
				$hosp_array = $hospitalids->fetchArray();

				$hosp_ids[] = '999999999';
				foreach($hosp_array as $hospital)
				{
					$hosp_ids[] = $hospital['id'];
				}
			}
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->whereIn('ipid', $patients)
				->andWhere('isdelete="0"')
				->andWhere("valid_till='0000-00-00 00:00:00'")
				->orderBy('valid_from ASC');
			$patient_locations = $patloc->fetchArray();

			foreach($patient_locations as $k_pat_loc => $v_pat_loc)
			{
				$patient_status[$v_pat_loc['ipid']]['locations'][] = $v_pat_loc;
				$patient_status[$v_pat_loc['ipid']]['last_location'] = $v_pat_loc;


				//parse all locations-> last ipid location remains set
				if(in_array($v_pat_loc['location_id'], $hosp_ids))
				{
					$patient_status[$v_pat_loc['ipid']]['inhosp'] = '1';

					if($patient_status[$v_pat_loc['ipid']]['condition'] != 'is_discharged_icon' && $patient_status[$v_pat_loc['ipid']]['condition'] != 'is_standby_icon' && $patient_status[$v_pat_loc['ipid']]['condition'] != 'is_standbydelete_icon'
					)
					{
						$patient_status[$v_pat_loc['ipid']]['condition'] = 'is_hospital_icon';
						$patient_status[$v_pat_loc['ipid']]['show'] = $sys_subicons['is_hospital_icon'];
					}
				}
				//ISPC-1948 Lore 21.08.2020
				elseif(in_array($v_pat_loc['location_id'], $holi_ids)){

				    $patient_status[$v_pat_loc['ipid']]['inhosp'] = '0';
				    
				    if($patient_status[$v_pat_loc['ipid']]['condition'] != 'is_discharged_icon' && $patient_status[$v_pat_loc['ipid']]['condition'] != 'is_standby_icon' && $patient_status[$v_pat_loc['ipid']]['condition'] != 'is_standbydelete_icon'
				        )
				    {
				        $patient_status[$v_pat_loc['ipid']]['condition'] = 'is_holiday_icon';
				        $patient_status[$v_pat_loc['ipid']]['show'] = $sys_subicons['is_holiday_icon'];
				    }
				}
				//.
				else
				{
					$patient_status[$v_pat_loc['ipid']]['inhosp'] = '0';
				}
			}

			//check if dead
			$distod = Doctrine_Query::create()
// 				->select("*")
				->select("id")
				->from('DischargeMethod')
				->Where("isdelete = 0")
				->andWhere('clientid="' . $clientid . '"')
				->andWhere("abbr = 'TOD' or abbr = 'tod' or abbr='Tod' or abbr='Verstorben' or abbr='verstorben'  or abbr='VERSTORBEN'");
			$tod_array = $distod->fetchArray();

			$dead_ids[] = "999999999";
			foreach($tod_array as $tod_method)
			{
				$dead_ids[] = $tod_method['id'];
			}

			$dispat = Doctrine_Query::create()
				->select("*")
				->from("PatientDischarge")
				->whereIn('ipid', $patients)
				->andWhere('isdelete = "0"');
			$discharged_arr = $dispat->fetchArray();

			foreach($discharged_arr as $k_discharged => $v_discharged)
			{
				$patient_status[$v_discharged['ipid']]['discharge_details'] = $v_discharged;

				if(in_array($v_discharged['discharge_method'], $dead_ids))
				{
					$patient_status[$v_discharged['ipid']]['isdead'] = '1';
					$patient_status[$v_discharged['ipid']]['condition'] = 'is_dead_icon';
					$patient_status[$v_discharged['ipid']]['show'] = $sys_subicons['is_dead_icon'];
				}
				else
				{
					$patient_status[$v_discharged['ipid']]['isdead'] = '0';
				}
			}

			//check death button
			$death_button = Doctrine_Query::create()
// 				->select('*')
				->select('id, ipid')
				->from('PatientDeath')
				->whereIn('ipid', $patients)
				->andWhere('isdelete="0"');
			$death_button_arr = $death_button->fetchArray();

			foreach($death_button_arr as $k_death_button => $v_death_button)
			{
				$patient_status[$v_death_button['ipid']]['condition'] = 'is_dead_icon';
				$patient_status[$v_death_button['ipid']]['show'] = $sys_subicons['is_dead_icon'];
			}

			foreach($patient_status as $k_pat_status => $v_pat_status)
			{
				if($filter && !$filter_value)
				{
					if($v_pat_status['condition'] == $filter)
					{
						$patient_status_arr[$k_pat_status] = $v_pat_status;
						$patient_status_arr['ipids'][] = $k_pat_status;
					}
				}
				else if($filter && $filter_value)
				{
					if($v_pat_status['condition'] == $filter && $v_pat_status['traffic_status'] == $filter_value)
					{
						$patient_status_arr[$k_pat_status] = $v_pat_status;
						$patient_status_arr['ipids'][] = $k_pat_status;
					}
				}
				else
				{
					$patient_status_arr[$k_pat_status] = $v_pat_status;
					$patient_status_arr['ipids'][] = $k_pat_status;
				}
			}

			//ipids and status data
			return $patient_status_arr;
		}

		public function get_emergency_nursing($ipids)
		{
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid, e.epid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}
			//print_r($act_ipids);
			$patient_pfleges = Doctrine_Query::create()
				->select('*')
				->from('PatientPflegedienste')
				->WhereIn('ipid', $act_ipids)
				->andWhere('isdelete = 0')
				->andWhere('pflege_emergency = 1 OR pflege_emergency = 2  ') // 1- pflege & emergency pflege, 2- just emergency pflege
				->orderBy('create_date desc');

			$pat_pfleges_res = $patient_pfleges->fetchArray();

			$pflege_ids[] = '999999999';
			foreach($pat_pfleges_res as $k_pat_pflege => $v_pat_pflege)
			{
				$pflege_ids[] = $v_pat_pflege['pflid'];
			}

			$pfleges = Doctrine_Query::create()
				->select('*')
				->from('Pflegedienstes')
				->WhereIn('id', $pflege_ids)
				->andWhere('isdelete = 0')
				->orderBy('id ASC');
			$pfleges_res = $pfleges->fetchArray();

			foreach($pfleges_res as $k_pflege => $v_pflege)
			{
				$pflegedienstes[$v_pflege['id']] = $v_pflege;
			}

//			$patients_pfleges['ipids'][] = '999999999';
			foreach($pat_pfleges_res as $k_pflege_m => $v_pflege_m)
			{
				$patients_pfleges['patient_pflege_emergency'][$v_pflege_m['ipid']][$v_pflege_m['pflid']] = $v_pflege_m;
				$patients_pfleges['master_pflege_emergency'][$v_pflege_m['ipid']][$v_pflege_m['pflid']] = $pflegedienstes[$v_pflege_m['pflid']];
				$patients_pfleges['ipids'][] = $v_pflege_m['ipid'];
			}
			$patients_pfleges['ipids'] = array_values(array_unique($patients_pfleges['ipids']));

			return $patients_pfleges;
		}

		public function go_to_visitform($ipids)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$clientid = $logininfo->clientid;
			$groupid = $logininfo->groupid;
			$userid = $logininfo->userid;
			$ug = new Usergroup();

			//get master group based on curent user group
			if($groupid)
			{
				$user_master_group = $ug->getMasterGroup($groupid);
			}

			$groups_visit_form = new GroupsVisitForms();
			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			else
			{
				$ipids = $ipids;
			}


// 			$group_link = $groups_visit_form->get_group_link($clientid, $user_master_group);
			$group_link = $groups_visit_form->get_group_link_and_type($clientid, $user_master_group);

			foreach($ipids as $k_ipid => $v_ipid)
			{
				$user_group_visit_link['group_icon'] = $group_link;
			}
			$user_group_visit_link['ipids'] = $ipids;

			if(!$clientid)
			{
				return false;
			}

			return $user_group_visit_link;
		}

		public function family_doctor($ipids, $patient2family_docs = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$family_doc_ids = array();
			
			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			else
			{
				$ipids = $ipids;
			}

			if($patient2family_docs)
			{
				foreach($patient2family_docs as $ipid => $docid)
				{
					$family_doc_ids[] = $docid;
				}

				$ipid2docids = $patient2family_docs;
			}
			else
			{
				$pm_query = Doctrine_Query::create()
					->select('ipid, familydoc_id')
					->from('PatientMaster')
					->whereIn('ipid', $ipids);
				$pm_res = $pm_query->fetchArray();

				//$family_doc_ids[] = '99999999999';

				foreach($pm_res as $k_pm_res => $v_pm_res)
				{
					$family_doc_ids[] = $v_pm_res['familydoc_id'];
					$ipid2docids[$v_pm_res['ipid']] = $v_pm_res['familydoc_id'];
				}
			}

			if (empty($family_doc_ids)) {
			    return; //fail-safe
			}
			
			
			$f_doc_query = Doctrine_Query::create()
				->select('*')
				->from('FamilyDoctor')
				->whereIn('id', $family_doc_ids)
				->andWhere('isdelete = "0"');
			$f_doc_res = $f_doc_query->fetchArray();

			foreach($f_doc_res as $k_f_doc_res => $v_f_doc_res)
			{
				$f_doctor_details[$v_f_doc_res['id']] = $v_f_doc_res;
			}

			foreach($ipids as $k_ipid => $v_ipid)
			{
				if($ipid2docids[$v_ipid] != '0')
				{
					$patient_family_doc['family_doc_data'][$v_ipid] = $f_doctor_details[$ipid2docids[$v_ipid]];
					$patient_family_doc['ipids'][] = $v_ipid;
				}
			}

			return $patient_family_doc;
		}

		public function sgbv_icon($ipids)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$usertype = $logininfo->usertype;

			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			else
			{
				$ipids = $ipids;
			}

			$sgbv_res = new SgbvForms();
			$sgbv_arr = $sgbv_res->get_all_patients_active_sgbv($ipids);

			foreach($sgbv_arr as $k_data => $v_data)
			{
				$sgbv_ipids['sgbv_data'][$v_data['ipid']] = $v_data;
				$sgbv_ipids['ipids'][] = $v_data['ipid'];
			}

			return $sgbv_ipids;
		}

		public function get_patient_suppliers($ipids)
		{
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid, e.epid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
					$active_patients[$v_act_ipid['ipid']] = $v_act_ipid; //patient details
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}

			//get patients and master suppliers
			$master_suppliers = PatientSupplies::get_patients_supplies($act_ipids);

			foreach($master_suppliers as $k_pat_supplier => $v_pat_supplier)
			{
				if(strlen($v_pat_supplier['Supplies']['logo']) > '0')
				{
					$suppliers_data['patient_supplier_data'][$v_pat_supplier['ipid']]['logo'] = $v_pat_supplier['Supplies']['logo'];
				}

				$suppliers_data['patient_supplier_data'][$v_pat_supplier['ipid']][$v_pat_supplier['id']] = $v_pat_supplier;
				$suppliers_data['master_supplier_data'][$v_pat_supplier['ipid']][$v_pat_supplier['id']] = $v_pat_supplier['Supplies'];
				$suppliers_data['ipids'][] = $v_pat_supplier['ipid'];

				$suppliers_data['ipids'] = array_values(array_unique($suppliers_data['ipids']));
			}

			return $suppliers_data;
		}

		public function get_patient_specialists($ipids)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$specialists_types = new SpecialistsTypes();
			$sp_types = $specialists_types->get_specialists_types($clientid);

			$sp_array = array();
			foreach($sp_types as $k_sp => $v_sp)
			{
				$sp_array[$v_sp['id']] = $v_sp['name'];
			}

			if(!$ipids)
			{
				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid, e.epid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
					$active_patients[$v_act_ipid['ipid']] = $v_act_ipid; //patient details
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}

			//get patients and master specialists
			$master_specialists = PatientSpecialists::get_patient_specialists($act_ipids, true);
			$specialists_data = array();
			foreach($master_specialists as $k_pat_specialist => $v_pat_specialist)
			{
				$specialists_data['patient_specialists_data'][$v_pat_specialist['ipid']][$v_pat_specialist['id']] = $v_pat_specialist;
				$specialists_data['master_specialists_data'][$v_pat_specialist['ipid']][$v_pat_specialist['id']] = $v_pat_specialist['master'];
				$specialists_data['master_specialists_data'][$v_pat_specialist['ipid']][$v_pat_specialist['id']]['medical_speciality_text'] = $sp_array[$v_pat_specialist['master']['medical_speciality']];
				$specialists_data['ipids'][] = $v_pat_specialist['ipid'];

				$specialists_data['ipids'] = array_values(array_unique($specialists_data['ipids']));
			}

			return $specialists_data;
		}

		public function memo_icon($ipids)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$usertype = $logininfo->usertype;

			$memo_res = new PatientMemo();
			$patients_memo = $memo_res->get_multiple_patient_memo($ipids);

			$allow = true;
			foreach($ipids as $k_ipid => $v_ipid)
			{
				if(array_key_exists($v_ipid, $patients_memo) && $allow === true)
				{
					$master_data['ipids'][] = $v_ipid;
				}

				if(array_key_exists($v_ipid, $patients_memo) && in_array($v_ipid, $master_data['ipids']))
				{
					$master_data['memo_data'][$v_ipid]['memo'] = htmlspecialchars(strip_tags($patients_memo[$v_ipid], '<br>'));
				}
			}
			return $master_data;
		}

		public function get_patient_diagnosis($ipids, $search_icon = false, $list = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			else
			{
				$ipids = $ipids;
			}

			if($search_icon)
			{
				$cust = Doctrine_Query::create()
					->select("id,ipid")
					->from('PatientDiagnosis');
				$cust->andWhereIn('ipid', $ipids);
				$cust->groupBy('ipid');
				$cust->orderBy('id ASC');
				$darray = $cust->fetchArray();

				$patient_diagnosis['ipids'] = array();
				foreach($darray as $k => $dvalues)
				{
					if(!in_array($dvalues['ipid'], $patient_diagnosis['ipids']))
					{
						$patient_diagnosis['ipids'][] = $dvalues['ipid'];
					}
				}
				$patient_diagnosis['ipids'] = array_unique($patient_diagnosis['ipids']);
				return $patient_diagnosis;
			}
			else
			{
				$dg = new DiagnosisType();
				$abb2 = "'HD','ND'";
				$ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
				$comma = ",";
				$typeid = "'0'";

				foreach($ddarr2 as $key => $valdia)
				{
					$typeid .=$comma . "'" . $valdia['id'] . "'";
					$comma = ", ";
					$type_details[$valdia['id']] = $valdia['abbrevation'];
					$type_array[] = $valdia['id'];
				}

				/* -------------------------Get Patients -  Diagnosis data------------------------------------------ */
				$patdia = new PatientDiagnosis();
				$dianoarray = $patdia->get_multiple_patients_diagnosis($ipids, $type_array);				
				
				//RWH - ISPC-950
				//sort by icd
				//ISPC - 2364 - sort by user
				$pdiaord = PatientDiagnoOrderTable::getInstance()->findDiagnoOrder($ipids);
				if(empty($pdiaord))
				{
					$dianoarray = $this->array_sort($dianoarray, 'icdnumber', SORT_ASC);
					$dianoarray = array_values($dianoarray);
				
				$patientmeta = new PatientDiagnosisMeta();
				$meta_data = $patientmeta->get_multiple($ipids);
				
				if(!empty($meta_data))
				{
					$diagnosismeta = new DiagnosisMeta();
					$master_meta = $diagnosismeta->getDiagnosisMetaData();
					foreach($master_meta as $k=>$meta_details){
						$meta_details_arr[$meta_details['id']] = $meta_details['meta_title'];
					}
					
					foreach($meta_data as $k=>$pmeta){
						$diagno_meta2pdiagnoid[$pmeta['diagnoid']][] = $meta_details_arr[$pmeta['metaid']];
						$diagno_meta_ids2pdiagnoid[$pmeta['diagnoid']][] = $pmeta['metaid'];
					}
					
					
					if(!empty($diagno_meta2pdiagnoid))
					{
						foreach($dianoarray as $k_diag => $v_diag)
						{
							$dianoarray[$k_diag]['meta'] = $diagno_meta2pdiagnoid[$v_diag['pdid']]; 
							$dianoarray[$k_diag]['meta_ids'] = $diagno_meta_ids2pdiagnoid[$v_diag['pdid']]; 
						}
					}
				}
// 				print_r($meta_data); 
// 				print_r($meta_details_arr); 
// 				print_r($diagno_meta2pdiagnoid); 
// 				print_r($dianoarray);
// 				 exit;
				//sort by type
				foreach($ddarr2 as $k_diag_type => $v_diag_type)
				{
					foreach($dianoarray as $k_diag => $v_diag)
					{
						if($v_diag_type['id'] == $v_diag['diagnosis_type_id'])
						{
							$dianoarray_sorted[] = $v_diag;
						}
					}
				}

				}
				else
				{
					$dianoarray = array_column($dianoarray, null, 'pdid');
					$pdorder = $pdiaord[0]['diagno_order'];
					
					$diagnaddother = array_diff(array_keys($dianoarray), $pdorder);
					$diagnremoveother = array_diff($pdorder, array_keys($dianoarray));
					
					if($diagnaddother)
					{
						$pdorder = array_merge($pdorder, $diagnaddother);							
					}
					
					if($diagnremoveother)
					{
						$pdorder = array_diff($pdorder, $diagnremoveother);	
					}
						
					$dianoarray_sorted = [];
					foreach ($pdorder as $pdid) {
						$dianoarray_sorted[] = $dianoarray[$pdid];
					}
					//$dianoarray = $dianoarray_sorted;
				}
				$dianoarray = $dianoarray_sorted;
				//RWH end
// print_r($dianoarray); exit;  
				foreach($dianoarray as $diangosis)
				{
					
					if(!empty($diangosis['meta'])){
						$metastasen[$diangosis['ipid']] = '<span class="icon_meta_line" id="diagnom_'.$diangosis['pdid'].'">(Metastasierung: '.implode(", ",$diangosis['meta']).') <a href="javascript:void(0)" class="remove_meta" data-diagno_id="'.$diangosis['pdid'].'" data-meta_ids="'.implode(",",$diangosis['meta_ids']).'"></a></span>';
					} else{
						$metastasen[$diangosis['ipid']] = "";
					}
					if($diangosis['icdnumber'])
					{
						$diagnosisarr[$diangosis['ipid']][$type_details[$diangosis['diagnosis_type_id']]][] = $diangosis['icdnumber'] . ' | ' . $diangosis['diagnosis'].$metastasen[$diangosis['ipid']];
						$diagnosisarr_list[$diangosis['ipid']][] = htmlentities($type_details[$diangosis['diagnosis_type_id']] . ' | ' . $diangosis['icdnumber'] . ' | ' . $diangosis['diagnosis'].$metastasen[$diangosis['ipid']], ENT_QUOTES, "UTF-8");
					}
					else
					{
						$diagnosisarr[$diangosis['ipid']][$type_details[$diangosis['diagnosis_type_id']]][] = $diangosis['diagnosis'].$metastasen[$diangosis['ipid']];
						$diagnosisarr_list[$diangosis['ipid']][] = htmlentities($type_details[$diangosis['diagnosis_type_id']] . ' | ' . $diangosis['diagnosis'].$metastasen[$diangosis['ipid']], ENT_QUOTES, "UTF-8");
					}
				}

				foreach($ipids as $k_ipid => $v_ipid)
				{
					if(!empty($diagnosisarr[$v_ipid]))
					{
						$patient_diagnosis['diagnosis_data'][$v_ipid] = $diagnosisarr[$v_ipid];
						$patient_diagnosis['ipids'][] = $v_ipid;
					}

					if(!empty($diagnosisarr_list[$v_ipid]))
					{
						$patient_diagnosis_list[$v_ipid]['list_diagnosis_data'] = implode("<br/>", $diagnosisarr_list[$v_ipid]);
					}
				}

				
// 				print_r($patient_diagnosis); exit;
				
				if($list)
				{
					return $patient_diagnosis_list;
				}
				else
				{
					return $patient_diagnosis;
				}
			}
		}

		public function get_patient_medication_02032020($ipids, $search_icon = false, $list = false)
		{
		    
			$logininfo = new Zend_Session_Namespace('Login_Info');

			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			else
			{
				$ipids = $ipids;
			}

// 			$modules = new Modules();
// 			if ($modules->checkModulePrivileges("111", $logininfo->clientid) ||
// 			    $modules->checkModulePrivileges(155, $logininfo->clientid))//Medication acknowledge
		    if ($this->_client_hasModule(111) 
		        || $this->_client_hasModule(155))
			{
			    $acknowledge_func = '1';
			    	
			    // Get declined data
			    //$declined_ids2ipids = PatientDrugPlanAlt::get_declined_patients_drugplan_alt($ipids);
			    $declined_ids2ipids = PatientDrugPlanAlt::get_declined_patients_drugplan_alt($ipids,false,false,true); // TODO-2320 - added params to retrive ALL denied  20.05.2019 @Ancuta
			    
			    foreach($ipids as $kd=>$ipidd)
			    {
			        foreach($declined_ids2ipids[$ipidd] as $kdd=>$declined_ids)
			        {
			            $declined[] = $declined_ids;
			        }
			        
			    }
			    
			    if(empty($declined)){
			        $declined[] = "XXXXXXXXXXXXXXXXXXXXX";
			    }
			    	
			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patients_drugplan_alt($ipids,false,false,true);
			     
			    foreach($ipids as $k=>$ipid)
			    {
			        foreach($non_approved[$ipid]['change'] as $drugplan_id =>$not_approved)
			        {
			            $not_approved_ids[] = $not_approved['drugplan_id'];
			            
			            if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
			                $newly_not_approved[] = $not_approved['drugplan_id'];;
			            }
			        }
			    }
			     
			    if(empty($not_approved_ids)){
			        $not_approved_ids[] = "XXXXXXXXXXXXXXXXXXXXX";
			    }
			    if(empty($newly_not_approved)){
			        $newly_not_approved[] = "XXXXXXXXXXXXXXXXXXXXX";
			    }
			}
			else
			{
			    $acknowledge_func = '0';
			}
			
			if($search_icon)
			{
				$drugs = Doctrine_Query::create();
				$drugs->select('id,ipid');
				$drugs->from('PatientDrugPlan');
				$drugs->whereIn('ipid', $ipids);
				$drugs->andWhere("isdelete = '0'");
				$drugs->andWhere("treatment_care = '0'");
				if($acknowledge_func =="1"){
				    $drugs->andWhereNotIn('id',$declined); // remove declined
				    $drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
				}
				$drugs->groupBy('ipid');
				$drugs->orderBy("id ASC");
				$drugsarray = $drugs->fetchArray();

				foreach($drugsarray as $key => $drugp)
				{
					$patient_medication_data['ipids'][] = $drugp['ipid'];
				}

				return $patient_medication_data;
			}
			else
			{
				$drugs = Doctrine_Query::create()
					->select('*')
					->from('PatientDrugPlan')
					->whereIn('ipid', $ipids)
					->andWhere("isdelete = '0'")
					->andWhere("treatment_care = '0'");
					if($acknowledge_func =="1"){
					    $drugs->andWhereNotIn('id',$declined); // remove declined
				        $drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
					}
					$drugs->orderBy("id ASC");
				$drugsarray = $drugs->fetchArray();

				foreach($drugsarray as $key => $drugp)
				{
					$master_meds[] = $drugp['medication_master_id'];
					$cocktail_ids[] = $drugp['cocktailid'];
					$drug_ids[] = $drugp['id'];
				}
				
				$drugs_dosage_array = array();
                if(!empty($drug_ids)){
    				$drugs_extra = Doctrine_Query::create()
    				->select('*')
    				->from('PatientDrugPlanExtra')
    				->whereIn('ipid', $ipids)
    				->andWhere("isdelete = '0'")
    				->andWhereIn("drugplan_id",$drug_ids);
    				$drugsarray_extra = $drugs_extra->fetchArray();
    				
    				if(!empty($drugsarray_extra)){
    				    
        				// get details for indication
        				$indications_array = MedicationIndications::client_medication_indications($clientid);
        				
        				foreach ($indications_array as $ki => $ind_value) {
        				    $indication[$ind_value['id']]['name'] = $ind_value['indication'];
        				    $indication[$ind_value['id']]['color'] = $ind_value['indication_color'];
        				}
        				
        				foreach($drugsarray_extra as $k=>$extra_data){
        				    $drug_indication[$extra_data['drugplan_id']] = $indication[$extra_data['indication']];
        				}
        				
    				}
    				
    				
    				
    				
    				$drugs_dosages = Doctrine_Query::create()
    				->select('*')
    				->from('PatientDrugPlanDosage')
    				->whereIn('ipid', $ipids)
    				->andWhere("isdelete = '0'") 
    				->andWhereIn("drugplan_id",$drug_ids)
    				->orderBy("dosage_time_interval ASC")
    				->fetchArray();
    				
    				if(!empty($drugs_dosages)){
    				    foreach($drugs_dosages as $k_drug => $v_drug)
    				    {
    				    	//ISPC-2329 Carmen 13.01.2020
    				        //$time = date("H:i",strtotime($v_drug['dosage_time_interval']));
    				    	$time = substr($v_drug['dosage_time_interval'], 0, 5);
    				        $drugs_dosage_array[$v_drug['ipid']][$v_drug['drugplan_id']][$time] = $v_drug['dosage'];
    				    }
    				     
    				}
//     				dd($drugs_dosage_array);
    				
                }
				
				
				
				if(empty($master_meds))
				{
					$master_meds['999999999'] = 'XXXXX';
				}

				$medic = Doctrine_Query::create()
					->select('*')
					->from('Medication')
					->whereIn("id", $master_meds);
				$master_medication = $medic->fetchArray();

				foreach($master_medication as $k_medi => $v_medi)
				{
					$medications[$v_medi['id']] = $v_medi['name'];
				}

				// get cocktaildetails
				
				// get schmerzpumpe details
				$cocktail_ids = array_unique($cocktail_ids);
				
				if(count($cocktail_ids) == 0)
				{
				    $cocktail_ids[] = '999999';
				}
				
				$cocktailsC = new PatientDrugPlanCocktails();
				$cocktails = $cocktailsC->getDrugCocktails($cocktail_ids);
				
				
				// get nutrition medication
				$master_medarr_nutrittion = Nutrition::getMedicationNutritionById($master_meds);
				
				if(!empty($master_medarr_nutrittion))
				{
				
				    foreach($master_medarr_nutrittion as $kn_medi => $vn_medi)
				    {
				        $nutrition_medications[$vn_medi['id']] = $vn_medi['name'];
				    }
				}
				
    			$medication_extra  = PatientDrugPlanExtra::get_patients_drugplan_extra($ipids,$clientid);
    			
				foreach($drugsarray as $key => $drugp)
				{

				    $dosageh = $drugp['dosage'];
				    if( ! empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
				         
				        $drugp['dosage'] = ' | ' . implode("-",$drugs_dosage_array[$drugp['ipid']][$drugp['id']]);
				    } else {
				        
    				    if($drugp['dosage'])
    					{
    					    // TODO-2504 + TODO-2526  Corrected By Ancuta 03.09.2019
    					    if($drugp['isschmerzpumpe'] == '1'){
    					        
    					        $dosage_value = "";
    					        $dosage_value = str_replace(",",".",$drugp['dosage']);
    					        
    					        $dosage24h = 24 * $dosage_value;
    					        
    					        $unit ="";
    					        $unit = !empty($medication_extra[$drugp['id']]['unit']) ? $medication_extra[$drugp['id']]['unit'] : "";
    					        
    					        $drugp['dosage'] = ' | '.round($drugp['dosage'], 2).''.$unit.'/h';
    					        $drugp['dosage'] .= ' ['.round($dosage24h, 2).''.$unit.'/24h]';
    					 
    					        
    					    }    					    
    					    else
    					    {
    					        
        						$drugp['dosage'] = ' | ' . $drugp['dosage'];    						
    					    }
    					}
				    }
				    
					if($drugp['comments'])
					{
						$drugp['comments'] = ' | ' . $drugp['comments'];
					}
					
					

					if($drugp['isbedarfs'] == '1')
					{
					    //ISPC-2110 p.4
					    if ( ! empty($drugp['dosage_interval'])) {
					        if (empty($drugp['dosage'])) {
					            $drugp['dosage'] = ' |';
					        }
					        $drugp['dosage'] .= " Intervall: " . $drugp['dosage_interval'];    //TODO-3243 Lore 26.06.2020
					    }
					    
						$type = "N";
						$patient_medication[$drugp['ipid']]['N']['medications'][] = $medications[$drugp['medication_master_id']] . $drugp['dosage'] . $drugp['comments'];
					    $patient_medication[$drugp['ipid']]['N']['indications'][] = $drug_indication[$drugp['id']];
					}
					elseif($drugp['iscrisis'] == '1')
					{
						$type = "KM";
						$patient_medication[$drugp['ipid']][$type]['medications'][] = $medications[$drugp['medication_master_id']] . $drugp['dosage'] . $drugp['comments'];
						$patient_medication[$drugp['ipid']][$type]['indications'][] = $drug_indication[$drugp['id']];
					}
					elseif($drugp['isivmed'] == '1')
					{
						$patient_medication[$drugp['ipid']]['I']['medications'][] = $medications[$drugp['medication_master_id']] . $drugp['dosage']. $drugp['comments'] ;
						$patient_medication[$drugp['ipid']]['I']['indications'][] = $drug_indication[$drugp['id']];
						$type = "I";
					}
					elseif($drugp['isschmerzpumpe'] == '1')
					{  
						$patient_medication[$drugp['ipid']]['Q'][$drugp['cocktailid']]['medications'][] = $medications[$drugp['medication_master_id']] . $drugp['dosage'] ;
						$patient_medication[$drugp['ipid']]['Q'][$drugp['cocktailid']]['indications'][] = $drug_indication[$drugp['id']];
						$patient_medication[$drugp['ipid']]['Q'][$drugp['cocktailid']]['cocktail_details']['comment'] = $cocktails[$drugp['cocktailid']]['description'] ;
						//TODO-2504 -Lore 19.08.2019
						$patient_medication[$drugp['ipid']]['Q'][$drugp['cocktailid']]['cocktail_details']['flussrate'] = $cocktails[$drugp['cocktailid']]['flussrate'] ;
						$type = "Q";
					}
					elseif($drugp['isintubated'] == '1') //ISPC-2176 p6
					{
					    $sh_isintubated = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
						$patient_medication[$drugp['ipid']][$sh_isintubated]['medications'][] = $medications[$drugp['medication_master_id']] . $drugp['dosage']. $drugp['comments'] ;
						$patient_medication[$drugp['ipid']][$sh_isintubated]['indications'][] = $drug_indication[$drugp['id']];
						$type = "I";
					}
					else
					{
                        if($drugp['isnutrition'] == '1'){
    						$patient_medication[$drugp['ipid']]['M']['medications'][] = $nutrition_medications[$drugp['medication_master_id']] . $drugp['dosage']. $drugp['comments'] ;
    						$patient_medication[$drugp['ipid']]['M']['indications'][] = $drug_indication[$drugp['id']];
                        }
                        else
                        {
    						$patient_medication[$drugp['ipid']]['M']['medications'][] = $medications[$drugp['medication_master_id']] . $drugp['dosage']. $drugp['comments'];
    						$patient_medication[$drugp['ipid']]['M']['indications'][] = $drug_indication[$drugp['id']];
                            
                        }
					    
						$type = "M";
					}
					
					if($drugp['isbedarfs'] == '1')
					{
    					$patient_medication_list[$drugp['ipid']][] = htmlentities($type . ' | ' . $medications[$drugp['medication_master_id']] . $drugp['dosage'] . $drugp['comments'], ENT_QUOTES, "UTF-8");
					}
					elseif($drugp['iscrisis'] == '1'){
						
						$patient_medication_list[$drugp['ipid']][] = htmlentities($type . ' | ' . $medications[$drugp['medication_master_id']] . $drugp['dosage'] . $drugp['comments'], ENT_QUOTES, "UTF-8");
					}
					
					if($drugp['isivmed'] == '1')
					{
    					$patient_medication_list[$drugp['ipid']][] = htmlentities($type . ' | ' . $medications[$drugp['medication_master_id']] . $drugp['dosage'] . $drugp['comments'], ENT_QUOTES, "UTF-8");
					} 
					else
					{
    					$patient_medication_list[$drugp['ipid']][] = htmlentities($type . ' | ' . $medications[$drugp['medication_master_id']] . $drugp['dosage']. $drugp['comments'], ENT_QUOTES, "UTF-8");
					}
				}

				foreach($ipids as $k_ipid => $v_ipid)
				{
					if(!empty($patient_medication[$v_ipid]))
					{
						$patient_medication_data['medication_data'][$v_ipid] = $patient_medication[$v_ipid];
						$patient_medication_data['ipids'][] = $v_ipid;
					}

					if(!empty($patient_medication_list[$v_ipid]))
					{
						$patient_medication_data_list[$v_ipid]['list_medication_data'] = implode("<br/>", $patient_medication_list[$v_ipid]);
					}
				}
				
				
// 				print_r($patient_medication_data); exit;;
				if($list)
				{
					return $patient_medication_data_list;
				}
				else
				{
					return $patient_medication_data;
				}
			}
		}

		public function has_painmedication_icon($ipids, $search_icon = true)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$usertype = $logininfo->usertype;

			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			else
			{
				$ipids = $ipids;
			}

			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlan')
				->whereIn('ipid', $ipids)
				->andWhere("isdelete = '0'")
				->andWhere("isschmerzpumpe = '1'")
				->orderBy("id ASC");
			$drugsarray = $drugs->fetchArray();
			
			if($search_icon)
			{
				foreach($drugsarray as $key_drg => $v_drg)
				{
					$pain_med_patients['ipids'][] = $v_drg['ipid'];
				}
			}
			else 
			{
				foreach($drugsarray as $key_drg => $v_drg)
				{
					$pain_med_patients['pain_medication'][$v_drg['ipid']] = $v_drg;
					$pain_med_patients['ipids'][] = $v_drg['ipid'];
				}
			}
			
			return $pain_med_patients;
		}

		public function get_patient_pharmacy($ipids)
		{
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid, e.epid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
					$active_patients[$v_act_ipid['ipid']] = $v_act_ipid; //patient details
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}

			//get patients pharmacy
			$pharmacy_data = PatientPharmacy::get_patients_pharmacy($act_ipids);

			foreach($pharmacy_data as $k_pat_pharmacy => $v_pat_pharmacies)
			{
				foreach($v_pat_pharmacies as $k_phar => $v_pat_pharmacy)
				{
					$pat_pharmacy_data['patient_pharmacy_data'][$v_pat_pharmacy['ipid']][$v_pat_pharmacy['id']] = $v_pat_pharmacy;
					$pat_pharmacy_data['ipids'][] = $k_pat_pharmacy;
				}

				$pat_pharmacy_data['ipids'] = array_values(array_unique($pat_pharmacy_data['ipids']));
			}

			return $pat_pharmacy_data;
		}

		public function get_patient_healthinsurance($ipids,$tab_ipids_query = false)
		{
		    $hi_data = array();
		    
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				if($tab_ipids_query){
    				$actpatient->andWhere($tab_ipids_query);
				}
				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
					$active_patients[$v_act_ipid['ipid']] = $v_act_ipid; //patient details
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}

			$pat_health_insurances = PatientHealthInsurance::get_multiple_patient_healthinsurance($act_ipids, true);

			//get hi statuses
			$hi_status_array = KbvKeytabs::getKbvKeytabs(1);

			$company_ids = array();
			
			if ( ! empty($pat_health_insurances)) {
			    
    			foreach ($pat_health_insurances as $k_ipid => $v_hi_data) {
    			    
    				if(strlen($v_hi_data['company_name']) > 0) {
    				    
    					if(strlen($v_hi_data['insurance_status'])) {
    						$v_hi_data['__insurance_status'] = $v_hi_data['insurance_status'];
    						$v_hi_data['insurance_status'] = $hi_status_array[$v_hi_data['insurance_status']];
    					}
    					$hi_data['patient_hi_data'][$v_hi_data['ipid']][$v_hi_data['id']] = $v_hi_data;
    					$hi_data['ipids'][] = $k_ipid;
    					$company_ids[] = $v_hi_data['companyid'];
    				}
    				
    			}
    			
    			$hi_data['ipids'] = array_values(array_unique($hi_data['ipids']));

    			$hi_data['subdivizion_details'] = $this->_get_patient_healthinsurance_subdivisions($hi_data['ipids'], $company_ids);
			
			} else {
			    
			    $hi_data['subdivizion_details'] = $this->_get_patient_healthinsurance_subdivisions($ipids = null, $company_ids = null);
			}
			
			return $hi_data;
		}
		
		public function get_patient_healthinsurance_150908($ipids)
		{
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid, e.epid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
					$active_patients[$v_act_ipid['ipid']] = $v_act_ipid; //patient details
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}

			$pat_health_insurances = PatientHealthInsurance::get_multiple_patient_healthinsurance($act_ipids, true);

			//get hi statuses
			$hi_status_array = KbvKeytabs::getKbvKeytabs(1);

			foreach($pat_health_insurances as $k_ipid => $v_hi_data)
			{
				if(strlen($v_hi_data['company_name']) > 0)
				{
					if(strlen($v_hi_data['insurance_status']))
					{
						$v_hi_data['insurance_status'] = $hi_status_array[$v_hi_data['insurance_status']];
					}

					$hi_data['patient_hi_data'][$v_hi_data['ipid']][$v_hi_data['id']] = $v_hi_data;
					$hi_data['ipids'][] = $k_ipid;
				}
			}

			return $hi_data;
		}

		public function get_patient_versorger($ipids)
		{
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid, e.epid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
					$active_patients[$v_act_ipid['ipid']] = $v_act_ipid; //patient details
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}

			//get patients pharmacy
			$suppliers_data = PatientSuppliers::get_patients_suppliers($act_ipids);

			foreach($suppliers_data as $k_pat_supp => $v_pat_supp)
			{
				foreach($v_pat_supp as $kpat_supplyer => $vpat_supplyer)
				{
					$pat_supplyer_data['patient_supplyer_data'][$vpat_supplyer['ipid']][$vpat_supplyer['id']] = $vpat_supplyer;
					$pat_supplyer_data['ipids'][] = $vpat_supplyer['ipid'];
				}

				$pat_supplyer_data['ipids'] = array_values(array_unique($pat_supplyer_data['ipids']));
			}

			return $pat_supplyer_data;
		}

		private function array_sort($array, $on = NULL, $order = SORT_ASC)
		{
			$new_array = array();
			$sortable_array = array();

			if(count($array) > 0)
			{
				foreach($array as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $k2 => $v2)
						{
							if($k2 == $on)
							{
								if($on == 'birthd' || $on == 'admissiondate' || $on == 'admission_date' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'letter_date')
								{

									if($on == 'birthdyears')
									{
										$v2 = substr($v2, 0, 10);
									}
									$sortable_array[$k] = strtotime($v2);
								}
								elseif($on == 'epid')
								{
									$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
								}
								elseif($on == 'percentage')
								{
									$sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
								}
								else
								{
									$sortable_array[$k] = ucfirst($v2);
								}
							}
						}
					}
					else
					{
						if($on == 'birthd' || $on == 'admission_date' || $on == 'admissiondate' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'letter_date')
						{
							if($on == 'birthdyears')
							{
								$v = substr($v, 0, 10);
							}
							$sortable_array[$k] = strtotime($v);
						}
						elseif($on == 'epid' || $on == 'percentage')
						{
							$sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
						}
						elseif($on == 'percentage')
						{
							$sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
						}
						else
						{
							$sortable_array[$k] = ucfirst($v);
						}
					}
				}

				switch($order)
				{
					case SORT_ASC:
//						asort($sortable_array);
						//$SORTABLE_ARRAY = PMS_COMMONDATA::A_SORT($SORTABLE_ARRAY); 
						$sortable_array = Pms_CommonData::a_sort($sortable_array);
						break;
					case SORT_DESC:
//						arsort($sortable_array);
						$sortable_array = Pms_CommonData::ar_sort($sortable_array);
						break;
				}

				foreach($sortable_array as $k => $v)
				{
					$new_array[$k] = $array[$k];
				}
			}

			return $new_array;
		}

		public function get_patients_assigned_users($ipids = false, $tab_ipids_query = false)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
			$userlist = Doctrine_Query::create()
			->select("*")
			->from("User")
			->where("clientid = " . $clientid . "")
			->andWhere('isdelete = "0"')
			->andWhere('isactive="0"');
			$client_user_array = $userlist->fetchArray();
			
			
			foreach ($client_user_array as $kuser => $user_values){
				$client_users_ids[] = $user_values['id'];
				$users_data[$user_values['id']] = $user_values;
			}
 
			if(empty($cient_users_ids)){
				$client_users_ids[] = "XXXXXX";
			}
			
			$patient = Doctrine_Query::create();
			$patient->select('p.ipid,e.epid,q.userid as assigned_user_id');
			$patient->from('PatientMaster p');
			$patient->leftJoin("p.EpidIpidMapping e");
			$patient->leftJoin("e.PatientQpaMapping q");
			$patient->where("p.isdelete = 0");
			$patient->andWhere('q.clientid =e.clientid and q.clientid = ' . $clientid);
			$patient->andwhereIn('q.userid', $client_users_ids);

			if(!$ipids && $tab_ipids_query)
			{
				//no ipid list.. use TAB related ipids
				$patient->andWhere($tab_ipids_query);
			}
			else
			{
				if(is_array($ipids))
				{
					$sql_ipids_array = $ipids;
				}
				else
				{
					$sql_ipids_array[] = $ipids;
				}

				$patient->andwhereIn('p.ipid', $sql_ipids_array);
			}
			$patientidarray = $patient->fetchArray();

			foreach($patientidarray as $kp => $udata)
			{
				foreach($udata['EpidIpidMapping']['PatientQpaMapping'] as $user)
				{
					$users_array[] = $user['assigned_user_id'];
					$assigend_users_array[$udata['ipid']][] = $user['assigned_user_id'];
				}
			}

			if(empty($users_array))
			{
				$users_array[] = "XXXXXX";
			}

// 			$user_m = new User();
// 			$users_data = $user_m->getMultipleUserDetails($users_array);

			foreach($assigend_users_array as $patient_ipid => $user_ids)
			{
				foreach($user_ids as $k => $uid)
				{
					$patients_assigned_users['patient_assigned_users'][$patient_ipid][$uid] = $users_data[$uid];
				}
				$patients_assigned_users['ipids'][] = $patient_ipid;
			}

			return $patients_assigned_users;
		}
		
		public function get_patients_assigned_users_150908($ipids = false, $deleted = false)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			
			$userlist = Doctrine_Query::create()
			->select("*")
			->from("User")
			->where("clientid = " . $clientid . "")
			->andWhere('isdelete = "0"')
			->andWhere('isactive="0"');
			$client_user_array = $userlist->fetchArray();
			
			
			foreach ($client_user_array as $kuser => $user_values){
				$client_users_ids[] = $user_values['id'];
				$users_data[$user_values['id']] = $user_values;
			}
 
			if(empty($cient_users_ids)){
				$client_users_ids[] = "XXXXXX";
			}
			
			$patient = Doctrine_Query::create();
			$patient->select('p.ipid,e.epid,q.userid as assigned_user_id');
			$patient->from('PatientMaster p');
			$patient->leftJoin("p.EpidIpidMapping e");
			$patient->leftJoin("e.PatientQpaMapping q");
			$patient->where("p.isdelete = 0");
			$patient->andWhere('q.clientid =e.clientid and q.clientid = ' . $clientid);
			$patient->andwhereIn('q.userid', $client_users_ids);

			if(!$ipids)
			{
				//no ipid list.. use client ipids
			}
			else
			{
				if(is_array($ipids))
				{
					$sql_ipids_array = $ipids;
				}
				else
				{
					$sql_ipids_array[] = $ipids;
				}

				$patient->andwhereIn('p.ipid', $sql_ipids_array);
			}
			$patientidarray = $patient->fetchArray();

			foreach($patientidarray as $kp => $udata)
			{
				foreach($udata['EpidIpidMapping']['PatientQpaMapping'] as $user)
				{
					$users_array[] = $user['assigned_user_id'];
					$assigend_users_array[$udata['ipid']][] = $user['assigned_user_id'];
				}
			}

			if(empty($users_array))
			{
				$users_array[] = "XXXXXX";
			}

// 			$user_m = new User();
// 			$users_data = $user_m->getMultipleUserDetails($users_array);

			foreach($assigend_users_array as $patient_ipid => $user_ids)
			{
				foreach($user_ids as $k => $uid)
				{
					$patients_assigned_users['patient_assigned_users'][$patient_ipid][$uid] = $users_data[$uid];
				}
				$patients_assigned_users['ipids'][] = $patient_ipid;
			}

			return $patients_assigned_users;
		}

		/*
		public function get_physiotherapist_patients($ipids = false, $deleted = false)
		{
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid, e.epid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}
			//			print_r($act_ipids);
			$patient_physio = Doctrine_Query::create()
				->select('*')
				->from('PatientPhysiotherapist')
				->WhereIn('ipid', $act_ipids)
				->andWhere('isdelete = 0')
				->orderBy('create_date desc');

			$pat_physio_res = $patient_physio->fetchArray();

			$physio_ids[] = '999999999';
			foreach($pat_physio_res as $k_pat_physio => $v_pat_physio)
			{
				$physio_ids[] = $v_pat_physio['physioid'];
			}

			$physio = Doctrine_Query::create()
				->select('*')
				->from('Physiotherapists')
				->WhereIn('id', $physio_ids)
				->andWhere('isdelete = 0')
				->orderBy('id ASC');

			$physio_res = $physio->fetchArray();

			foreach($physio_res as $k_physio => $v_physio)
			{
				$physiotherapist[$v_physio['id']] = $v_physio;
			}

			//			$patients_physio['ipids'][] = '999999999';
			foreach($pat_physio_res as $k_physio_m => $v_physio_m)
			{
				$patients_physio['patient_physio'][$v_physio_m['ipid']][$v_physio_m['physioid']] = $v_physio_m;
				$patients_physio['master_physio'][$v_physio_m['ipid']][$v_physio_m['physioid']] = $physiotherapist[$v_physio_m['physioid']];
				$patients_physio['ipids'][] = $v_physio_m['ipid'];
				$patients_physio['physio_ipids'][] = $v_physio_m['ipid'];
			}
			$patients_physio['ipids'] = array_values(array_unique($patients_physio['ipids']));

			return $patients_physio;
		}
		*/
		
		
		public function get_physiotherapist_patients($ipids = false, $deleted = false)
		{
		    
		    $act_ipids = array();
		    
		    $patients_physio =  array(); //result
		    
		    if ( ! $ipids ) {
		        //no ipid list.. use client ipids
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		    
		        //get client patients
		        $actipidarray = Doctrine_Query::create()
		        ->select("p.ipid")
		        ->from('PatientMaster p')
		        ->leftJoin("p.EpidIpidMapping e")
		        ->where('e.clientid = ?', $clientid)
		        ->andwhere('p.isdelete = 0')
		        ->fetchArray();
		    
		        if ( ! empty($actipidarray)) {
		            $act_ipids = array_column($actipidarray, 'ipid');
		        }
		    
		    } else {
		    
		        $act_ipids = is_array($ipids) ? $ipids : array($ipids);
		    }
		    
		    if (empty($act_ipids)) {
		        return $patients_physio; //fail-safe
		    }
		    
		    $pat_physio_res = Doctrine_Query::create()
		    ->select('pphy.*, phy.*')
		    ->from('PatientPhysiotherapist pphy')
		    ->leftJoin('pphy.Physiotherapists phy')
		    ->WhereIn('pphy.ipid', $act_ipids)
		    ->andWhere('pphy.isdelete = 0')
		    ->orderBy('pphy.create_date DESC')
		    ->fetchArray();
		
		    if ( ! empty($pat_physio_res)) {
		        
		        foreach ($pat_physio_res as $k_physio_m => $v_physio_m) {
		            
    		        $patients_physio['patient_physio'][$v_physio_m['ipid']][$v_physio_m['physioid']] = $v_physio_m;
    		        $patients_physio['master_physio'][$v_physio_m['ipid']][$v_physio_m['physioid']] = $v_physio_m['Physiotherapists'];
    		        $patients_physio['ipids'][] = $v_physio_m['ipid'];
    		        $patients_physio['physio_ipids'][] = $v_physio_m['ipid'];
    		    }
    		    
    		    $patients_physio['ipids'] = array_values(array_unique($patients_physio['ipids']));
		    }
		
		    return $patients_physio;
		}
		
		

		/*
		public function get_homecare_patients($ipids = false, $deleted = false)
		{
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;

				//get client patients
				$actpatient = Doctrine_Query::create()
					->select("p.ipid, e.epid")
					->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');

				$actipidarray = $actpatient->fetchArray();

				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}
			//			print_r($act_ipids);
			$patient_homes = Doctrine_Query::create()
				->select('*')
				->from('PatientHomecare')
				->WhereIn('ipid', $act_ipids)
				->andWhere('isdelete = 0')
				->orderBy('create_date desc');

			$pat_homes_res = $patient_homes->fetchArray();

			$home_ids[] = '999999999';
			foreach($pat_homes_res as $k_pat_home => $v_pat_home)
			{
				$home_ids[] = $v_pat_home['homeid'];
			}

			$homes = Doctrine_Query::create()
				->select('*')
				->from('Homecare')
				->WhereIn('id', $home_ids)
				->andWhere('isdelete = 0')
				->orderBy('id ASC');

			$homes_res = $homes->fetchArray();

			foreach($homes_res as $k_home => $v_home)
			{
				$homecares[$v_home['id']] = $v_home;
			}

			$patients_homes =  array();
			
			foreach($pat_homes_res as $k_homes_m => $v_homes_m)
			{
				$patients_homes['patient_homes'][$v_homes_m['ipid']][$v_homes_m['homeid']] = $v_homes_m;
				$patients_homes['master_homes'][$v_homes_m['ipid']][$v_homes_m['homeid']] = $homecares[$v_homes_m['homeid']];
				$patients_homes['ipids'][] = $v_homes_m['ipid'];
				$patients_homes['homes_ipids'][] = $v_homes_m['ipid'];
			}
			$patients_homes['ipids'] = array_values(array_unique($patients_homes['ipids']));

			return $patients_homes;
		}
		*/

		

		/**
		 * @cla fn rewrite on 26.06.2018
		 * 
		 * @param array $ipids
		 * @param string $deleted
		 * @return multitype:multitype:
		 */
		public function get_homecare_patients($ipids = false)
		{
		    $act_ipids = array();
		    
		    $patients_homes =  array(); //result
		    
		    if ( ! $ipids ) {
		        //no ipid list.. use client ipids
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		
		        //get client patients
		        $actipidarray = Doctrine_Query::create()
		        ->select("p.ipid")
		        ->from('PatientMaster p')
		        ->leftJoin("p.EpidIpidMapping e")
		        ->where('e.clientid = ?', $clientid)
		        ->andwhere('p.isdelete = 0')
		        ->fetchArray();
		
		        if ( ! empty($actipidarray)) {
		            $act_ipids = array_column($actipidarray, 'ipid');
		        }
		        
		    } else {
		        
		        $act_ipids = is_array($ipids) ? $ipids : array($ipids);
		    }
		    
		    if (empty($act_ipids)) {
		        return $patients_homes; //fail-safe
		    }
		    
		    
		    $pat_homes_res = Doctrine_Query::create()
		    ->select('ph.*, h.*')
		    ->from('PatientHomecare ph')
		    ->leftJoin("ph.Homecare h")
		    ->whereIn('ph.ipid', $act_ipids)
		    ->andWhere('ph.isdelete = 0')
		    ->orderBy('ph.create_date DESC')
		    ->fetchArray();
		
		    if ( ! empty($pat_homes_res)) {
		        
    		    foreach ($pat_homes_res as $k_homes_m => $v_homes_m) {
    		        
    		        $patients_homes['patient_homes'][$v_homes_m['ipid']][$v_homes_m['homeid']] = $v_homes_m;
    		        $patients_homes['master_homes'][$v_homes_m['ipid']][$v_homes_m['homeid']] = $v_homes_m['Homecare'];
    		        $patients_homes['ipids'][] = $v_homes_m['ipid'];
    		        $patients_homes['homes_ipids'][] = $v_homes_m['ipid'];
    		        
    		    }
    		    
    		    $patients_homes['ipids'] = array_values(array_unique($patients_homes['ipids']));
		    }
		    
		    return $patients_homes;
		}
		
		

		public function get_patients_location_users($ipids = false, $deleted = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
				
			$userlist = Doctrine_Query::create()
			->select("*")
			->from("User")
			->where("clientid = " . $clientid . "")
			->andWhere('isdelete = "0"')
			->andWhere('isactive="0"');
			$client_user_array = $userlist->fetchArray();
				
			foreach ($client_user_array as $kuser => $user_values){
				$client_users_ids[] = $user_values['id'];
				$users_data[$user_values['id']] = $user_values;
			}
		
			if(empty($cient_users_ids)){
				$client_users_ids[] = "XXXXXX";
			}
			
			$patient = Doctrine_Query::create();
			$patient->select('e.ipid,e.epid,l.location_id');
			$patient->from("EpidIpidMapping e");
			$patient->leftJoin("e.PatientLocation l ON l.ipid = e.ipid");
			$patient->where('l.clientid =e.clientid and l.clientid = ' . $clientid);
			$patient->andWhere('l.isdelete = 0');
			$patient->andWhere('l.valid_till = "0000-00-00 00:00:00"');
		
			
			if(!$ipids)
			{
				//no ipid list.. use client ipids
			}
			else
			{
				if(is_array($ipids))
				{
					$sql_ipids_array = $ipids;
				}
				else
				{
					$sql_ipids_array[] = $ipids;
				}
		
				$patient->andwhereIn('e.ipid', $sql_ipids_array);
			}
			$patientidarray = $patient->fetchArray();
			
			
			foreach($patientidarray as $kp => $udata)
			{
				foreach($udata['PatientLocation'] as $k=>$locations)
				{
					$location_id_array[] = $locations['location_id'];
					$patient2location[$udata['ipid']] = $locations['location_id'];
					$location2patient[$locations['location_id']] = $udata['ipid'];
				}
			}
			if(empty($location_id_array))
			{
				$location_id_array[] = "XXXXXX";
			}
			$location_id_array= array_unique($location_id_array);

			
			// get assigend users to location
			$users2location_array = Users2Location::get_location_users($location_id_array);
			
			foreach($users2location_array as $k=>$ul_value){
				$location_users[$ul_value['location']][$ul_value['user'] ] = $users_data[$ul_value['user']];
			}
			
			foreach($patient2location as $pipid=>$locationdata){
				if($location_users[$locationdata]){
					$patients_location_users['patient_location_users'][$pipid] = $location_users[$locationdata];
					$patients_location_users['ipids'][] = $pipid;
				}
				
			}
		//	print_r($patients_location_users);exit;
			return $patients_location_users;
		}
		
		

		public function allergies_icon($ipids)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
		
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$usertype = $logininfo->usertype;
		
		
			if(is_array($ipids))
			{
				$ipids_array = $ipids;
			}
			else
			{
				$ipids_array[] = $ipids;
			}
		
			$allerg_res = new PatientDrugPlanAllergies();
			$patients_allergies = $allerg_res->get_multiple_patient_allergies($ipids_array);
			$allow = true;
			foreach($ipids_array as $k_ipid => $v_ipid)
			{
				if(array_key_exists($v_ipid, $patients_allergies) && $allow === true)
				{
					$master_data['ipids'][] = $v_ipid;
				}
		
				if(array_key_exists($v_ipid, $patients_allergies) && in_array($v_ipid, $master_data['ipids']))
				{
					$master_data['allergies_data'][$v_ipid]['allergies'] = htmlspecialchars(strip_tags($patients_allergies[$v_ipid], '<br>'));
				}
			}
			
			return $master_data;
		}	
		

		/**
		 * @cla on 11.06.2018
		 * @deprecated
		 */
		public function get_register_status_OLD($ipids)
		{
		    /* ISPC-1775,ISPC-1678 */
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$usertype = $logininfo->usertype;

			$modules = new Modules();
			if($modules->checkModulePrivileges("126", $logininfo->clientid))//register 
			{
    			
    		
    			if(is_array($ipids))
    			{
    				$ipids_array = $ipids;
    			}
    			else
    			{
    				$ipids_array[] = $ipids;
    			}
    			
    			$q = Doctrine_Query::create()
    			->select('e.*,p.*')
    			->from('EpidIpidMapping e INDEXBY e.ipid')
    			->leftJoin('e.PatientMaster p')
    			->where('e.clientid = "' . $clientid . '"')
    			->andWhere('p.isdelete = 0')
    			->andWhere('p.isstandby = 0')
    			->andWhere('p.isstandbydelete = 0')
    			->andWhereIn('p.ipid',$ipids_array);
    			$patientlimit_all = $q->fetchArray();
    			
    			$all_patients[]="9999999";
    			foreach($patientlimit_all as $ff=>$pd){
    			    $all_patients[] = $pd['ipid'];
    			}
    		
    			$all_submited_patients =  DgpPatientsHistory::submited_patients($patientlimit_all);
    			$completed_patients = DgpKern::patients_filled_status($ipids_array);
    
    
    			foreach($ipids_array as $k_ipid => $v_ipid)
    			{
    			    
    			    if(in_array($v_ipid,$completed_patients)){
    			        $status = "filled";
    			    } else {
    			        $status = "not_filled";
    			    }
    			    
    			    if(in_array($v_ipid,$all_submited_patients['submited'])){
    			        $status .= "submited";
    			        $pat_status[$v_ipid] = "submited";
    			    } else {
    			        $status .= "not_submited";
    			        $pat_status[$v_ipid] = "not_submited";
    			    }
    		
    				$master_data['register_status'][$v_ipid]['status'] = $status;
    				
    				$master_data['ipids'][] = $v_ipid;
    			}
			} else{
			    $master_data = array();
			}
			
			return $master_data;
		}	

		
		/**
		 * @cla on 11.06.2018
		 * ISPC-2198
		 * 
		 * TODO : modify this fn, 
		 * intersect $completed_forms with $patientsFalls, via patient_eadmission_ID
		 * to know exactly for witch fall the form is red, blue or green,
		 * and on mouseover add this info for the not-filled/not-submited ones
		 * 
		 * 
		 * @param unknown $ipids
		 * @return multitype:
		 */
		public function get_register_status($ipids)
		{
		    $result = array();
		    
		    if (empty($ipids) 
		        || ! $this->_client_hasModule(126)) //126 = dgp register module
		    {
		        return $result; //fail-safe
		    }
		    
		 
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    
		    $patients_ipids = is_array($ipids) ? $ipids : array($ipids);
		    
		    $completed_forms = DgpKern::findFormsCompletedOfIpids($patients_ipids);
		    
		    $count_completed_forms = array();
		    
		    foreach ($completed_forms as $form) {
		        $count_completed_forms[$form['ipid']] ++;
		    }
		    
		    $patientsFalls = PatientReadmission::findFallsOfIpids($ipids);
		    
		    
		    $ipids_completed = array();
		    if ( ! empty($completed_forms)) {
		        $ipids_completed = array_unique(array_column($completed_forms, 'ipid'));
		    }
		    $patients_submited_status =  DgpPatientsHistory::findSubmitedStatusOfIpids($ipids_completed, array($logininfo->clientid));
		    
		    //submited
		    $ipids_submited = array_filter ( $patients_submited_status, function($val) {return $val == 'submited' ;});
		    $ipids_submited = array_keys($ipids_submited);
		    
		    //ready_to_send
		    $ipids_notsubmited = array_filter ( $patients_submited_status, function($val) {return $val == 'not_submited' ;});
		    $ipids_notsubmited = array_keys($ipids_notsubmited);
		    
		    //no-completed
		    $ipids_notcompleted = array_diff($patients_ipids, $ipids_notsubmited, $ipids_submited);
		    
		    
		   
		    foreach($ipids_submited as $v_ipid) {
		        $result['register_status'][$v_ipid]['status'] = 'filledsubmited';
		        $result['ipids'][] = $v_ipid;
		    }
		    
		    foreach($ipids_notsubmited as $v_ipid) {
		        $result['register_status'][$v_ipid]['status'] = 'fillednot_submited';
		        $result['ipids'][] = $v_ipid;
		    }
		   
		    foreach($ipids_notcompleted as $ipid) {
		        $result['register_status'][$v_ipid]['status'] = 'not_fillednot_submited';
		        $result['ipids'][] = $v_ipid;
		    }
		    
		    
		    foreach ($patients_ipids as $ipid) {
		        
		        if ( ! in_array($ipid, $result['ipids'])) {

		            $result['register_status'][$ipid]['status'] = 'not_fillednot_submited';
		            $result['ipids'][] = $ipid;
		        
		        } elseif ($count_completed_forms[$ipid] < count($patientsFalls[$ipid])) {
		                
	                $result['register_status'][$ipid]['status'] = 'not_fillednot_submited';
      
		        }
		        
		    }
		    
		    return $result;
		}
		
		
		

		public function get_diagno_act_patients($ipids = false, $filter = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;

		    //$modules = new Modules();
		    
// 		    if($modules->checkModulePrivileges("127", $logininfo->clientid))
		    if ($this->_client_hasModule(127)) 
		    {
    		    if(!$ipids)
    		    {
    		        //no ipid list.. use client ipids
    		        //get client patients
    		        $actpatient = Doctrine_Query::create()
    		        ->select("p.ipid, e.epid")
    		        ->from('PatientMaster p');
    		        $actpatient->leftJoin("p.EpidIpidMapping e");
    		        $actpatient->where('e.clientid = ' . $clientid);
    		        $actpatient->andwhere('p.isdelete = 0');
    		
    		        $actipidarray = $actpatient->fetchArray();
    		
    		        $act_ipids[] = '999999999';
    		        foreach($actipidarray as $k_act_ipid => $v_act_ipid)
    		        {
    		            $act_ipids[] = $v_act_ipid['ipid'];
    		            $active_patients[$v_act_ipid['ipid']] = $v_act_ipid; //patient details
    		        }
    		    }
    		    else
    		    {
    		        if(is_array($ipids))
    		        {
    		            $act_ipids = $ipids;
    		        }
    		        else
    		        {
    		            $act_ipids[] = $ipids;
    		        }
    		    }
    		
    		
    		    if($filter)
    		    {
    		        $filter_arr = explode('_', $filter);
    		    }
    
    		    $act_ipids = array_unique($act_ipids);
    		
    		    //get patient(s) sapvs
    		    $sapv_res = PatientDiagnosisAct::get_today_active_acts($act_ipids);
    		    
    		    //get sapv images custom or not
    		    $icons_client = new IconsMaster();
    		    $icons_sapv = $icons_client->get_system_icons($clientid, false, false, true);
    		
    		    foreach($icons_sapv as $k_icon_sapv => $v_icon_sapv)
    		    {
    		        $icons_sapv_map[$v_icon_sapv['name']] = $v_icon_sapv;
    		    }
    		
    		    foreach($sapv_res as $k_sapv => $v_sapv)
    		    {
    		        if(!empty($v_sapv['act']) && $v_sapv['act'] != 0 )
    		        {
    	
    	                $sapv_data['ipids'][] = $v_sapv['ipid'];
    	                $sapv_data['details_diagno_act'][$v_sapv['ipid']][] = $v_sapv;
    	                $sapv_data['last_diagno_act'][$v_sapv['ipid']] = $v_sapv;
    	                $sapv_data['last_diagno_act'][$v_sapv['ipid']]['image'] = $icons_sapv_map['diagno_act_' . $v_sapv['act']]['image'];
    	                $sapv_data['last_diagno_act'][$v_sapv['ipid']]['name'] = 'diagno_act_' . $v_sapv['act'];
    	                $sapv_data['last_diagno_act'][$v_sapv['ipid']]['color'] = $icons_sapv_map['diagno_act_' . $v_sapv['act']]['color'];
    	                $sapv_data['last_diagno_act'][$v_sapv['ipid']]['status'] = $v_sapv['act'];
    		        }
    		    }
    		
    		    if($filter)
    		    {
    		        $filter_arr = explode('_', $filter);
    		       
    		        foreach($sapv_data['last_diagno_act'] as $k_ipid => $v_sapv_last)
    		        {
    		            if($v_sapv_last['status'] == $filter_arr['2'])
    		            {
    		                $sapv_data_all['ipids'][] = $k_ipid;
    		            }
    		        }
    		    }
    		    else
    		    {
    		        $sapv_data_all['ipids'] = $sapv_data['ipids'];
    		    }
    		
    		    $sapv_data['ipids'] = array_values(array_unique($sapv_data_all['ipids']));
		    }	
		    else 
		    {
		        $sapv_data = array();
		    }
		    
		    return $sapv_data;
		}
		


		public function get_scheduled_medication($ipids)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    $usertype = $logininfo->usertype;
		
		    if(!is_array($ipids))
		    {
		        $ipids = array($ipids);
		    }
		    else
		    {
		        $ipids = $ipids;
		    }
		     
		    //ISPC-2329 pct.z) Lore 26.08.2019
		    $actpatient = Doctrine_Query::create()
		    ->select("ipid")
		    ->from('PatientMaster ')
		    ->whereIn('ipid', $ipids)
		    ->andWhere('isdischarged = 0');	    
		    
		    $actipidarray = $actpatient->fetchArray();
		    
		    if (empty($actipidarray)){
		        return;
		    }
		    else {
		        $act_ipids = array();
		        foreach($actipidarray as $k_act_ipid => $v_act_ipid)
		        {
		            $act_ipids[] = $v_act_ipid['ipid'];
		        }
		        $ipids=$act_ipids;
		    }
		    //.
		    
		    $modules = new Modules();
		    if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
// 		    if ($this->_client_hasModule(111)) //111 = Medication acknowledge
		    {
		        $acknowledge_func = '1';
		    
		        // Get declined data
		        // $declined_ids2ipids = PatientDrugPlanAlt::get_declined_patients_drugplan_alt($ipids);
		        $declined_ids2ipids = PatientDrugPlanAlt::get_declined_patients_drugplan_alt($ipids,false,false,true); // TODO-2320 - added params to retrive ALL denied  20.05.2019 @Ancuta
		        
		        foreach($ipids as $kd=>$ipidd)
		        {
		            foreach($declined_ids2ipids[$ipidd] as $kdd=>$declined_ids)
		            {
		                $declined[] = $declined_ids;
		            }
		             
		        }
		         
		        if(empty($declined)){
		            $declined[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		    
		        //get non approved data
		        $non_approved = PatientDrugPlanAlt::get_patients_drugplan_alt($ipids,false,false,true);
		    
		        foreach($ipids as $k=>$ipid)
		        {
		            foreach($non_approved[$ipid]['change'] as $drugplan_id =>$not_approved)
		            {
		                $not_approved_ids[] = $not_approved['drugplan_id'];
		                 
		                if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
		                    $newly_not_approved[] = $not_approved['drugplan_id'];;
		                }
		            }
		        }
		    
		        if(empty($not_approved_ids)){
		            $not_approved_ids[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		        if(empty($newly_not_approved)){
		            $newly_not_approved[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		    }
		    else
		    {
		        $acknowledge_func = '0';
		    }
		    	
            $scheduled_date = "ADDDATE(`administration_date`, `days_interval` ) AS scheduled_date";
            $sql_scheduled_date= "DATE(scheduled_date) <= CURDATE()";
		    
		    
		    $drugs = Doctrine_Query::create()
		    ->select("*,"  . $scheduled_date . "")
		    ->from('PatientDrugPlan')
		    ->whereIn('ipid', $ipids)
		    ->andWhere("isdelete = '0'")
// 		    ->andWhere("scheduled = '1'")
		    ->andWhere("scheduled = '1' OR has_interval = '1' ")
		    ->andWhere("administration_date != '0000-00-00 00:00:00'");
		    if($acknowledge_func == "1")
		    {
		        $drugs->andWhereNotIn('id',$declined); // remove declined
		        $drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
		    }
		    $drugs->having($sql_scheduled_date);
		    $drugs->orderBy("id ASC");
		    $drugsarray = $drugs->fetchArray();
		    
// print_r($drugsarray);
		    
		    $master_medications_ids = array();
		    foreach($drugsarray as $key_drg => $v_drg)
		    {
		        $master_medications_ids[] = $v_drg['medication_master_id'];
		        $drug_ids[] = $v_drg['id'];
		    }
		    
		    $master_tr_array = array(); 
		    if( ! empty($master_medications_ids)){
		    	//get the data from master medications array
		    	$med = new Medication();
		    	$master_tr_array = $med->master_medications_get($master_medications_ids, false);
		    }
		    

		    
		    

		    if(!empty($drug_ids)){
		        $drugs_extra = Doctrine_Query::create()
		        ->select('*')
		        ->from('PatientDrugPlanExtra')
		        ->whereIn('ipid', $ipids)
		        ->andWhere("isdelete = '0'")
		        ->andWhereIn("drugplan_id",$drug_ids);
		        $drugsarray_extra = $drugs_extra->fetchArray();
		    
		        if(!empty($drugsarray_extra)){
		    
		            // get details for indication
		            $indications_array = MedicationIndications::client_medication_indications($clientid);
		    
		            foreach ($indications_array as $ki => $ind_value) {
		                $indication[$ind_value['id']]['name'] = $ind_value['indication'];
		                $indication[$ind_value['id']]['color'] = $ind_value['indication_color'];
		            }
		    
		            foreach($drugsarray_extra as $k=>$extra_data){
		                $drug_indication[$extra_data['drugplan_id']] = $indication[$extra_data['indication']];
		            }
		    
		        }
		    }
		    
		    
		    
		    
		    foreach($drugsarray as $key => $drugp)
		    {
		        	
		        if($drugp['days_interval'])
		        {
		            $drugp['days_interval'] =  $drugp['days_interval'];
		        }
		        $patient_medication[$drugp['ipid']][$drugp['id']]['medication_name'] = $master_tr_array[$drugp['medication_master_id']]  ;
		        $patient_medication[$drugp['ipid']][$drugp['id']]['dosage'] = $drugp['dosage'];
		        $patient_medication[$drugp['ipid']][$drugp['id']]['indications'] = $drug_indication[$drugp['id']] ;
		        $patient_medication[$drugp['ipid']][$drugp['id']]['comments'] = $drugp['comments'];
		        $patient_medication[$drugp['ipid']][$drugp['id']]['days_interval'] =   $drugp['days_interval'];
		        $patient_medication[$drugp['ipid']][$drugp['id']]['due_date'] = $drugp['scheduled_date'];
		        
		    }
		    
		    foreach($ipids as $k_ipid => $v_ipid)
		    {
		        if(!empty($patient_medication[$v_ipid]))
		        {
		            $patient_medication_data['scheduled_medication_data'][$v_ipid] = $patient_medication[$v_ipid];
		            $patient_medication_data['ipids'][] = $v_ipid;
		        }
		    }
		    
// 		    print_r($patient_medication_data);
// 		    exit;
		    
		    return $patient_medication_data;
		}		

    /**
     * btm medis for patient
     *
     * @param $ipids
     * @return array|void
     * @author elena
     *
     * ISPC-2912,Elena,25.05.2021
     */
		public function get_btm_medication($ipids)
		{

		    //echo 'get_btm_medication';
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    $usertype = $logininfo->usertype;

		    if(!is_array($ipids))
		    {
		        $ipids = array($ipids);
		    }
		    else
		    {
		        $ipids = $ipids;
		    }

		    //ISPC-2329 pct.z) Lore 26.08.2019
		    $actpatient = Doctrine_Query::create()
		    ->select("ipid")
		    ->from('PatientMaster ')
		    ->whereIn('ipid', $ipids)
		    ->andWhere('isdischarged = 0');

		    $actipidarray = $actpatient->fetchArray();

		    if (empty($actipidarray)){
		        return;
		    }
		    else {
		        $act_ipids = array();
		        foreach($actipidarray as $k_act_ipid => $v_act_ipid)
		        {
		            $act_ipids[] = $v_act_ipid['ipid'];
		        }
		        $ipids=$act_ipids;
		    }
		    //.

		    $modules = new Modules();
		    if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
// 		    if ($this->_client_hasModule(111)) //111 = Medication acknowledge
		    {
		        $acknowledge_func = '1';

		        // Get declined data
		        // $declined_ids2ipids = PatientDrugPlanAlt::get_declined_patients_drugplan_alt($ipids);
		        $declined_ids2ipids = PatientDrugPlanAlt::get_declined_patients_drugplan_alt($ipids,false,false,true); // TODO-2320 - added params to retrive ALL denied  20.05.2019 @Ancuta

		        foreach($ipids as $kd=>$ipidd)
		        {
		            foreach($declined_ids2ipids[$ipidd] as $kdd=>$declined_ids)
		            {
		                $declined[] = $declined_ids;
		            }

		        }

		        if(empty($declined)){
		            $declined[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }

		        //get non approved data
		        $non_approved = PatientDrugPlanAlt::get_patients_drugplan_alt($ipids,false,false,true);

		        foreach($ipids as $k=>$ipid)
		        {
		            foreach($non_approved[$ipid]['change'] as $drugplan_id =>$not_approved)
		            {
		                $not_approved_ids[] = $not_approved['drugplan_id'];

		                if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
		                    $newly_not_approved[] = $not_approved['drugplan_id'];;
		                }
		            }
		        }

		        if(empty($not_approved_ids)){
		            $not_approved_ids[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		        if(empty($newly_not_approved)){
		            $newly_not_approved[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		    }
		    else
		    {
		        $acknowledge_func = '0';
		    }

            //$scheduled_date = "ADDDATE(`administration_date`, `days_interval` ) AS scheduled_date";
            //$sql_scheduled_date= "DATE(scheduled_date) <= CURDATE()";


		    $drugs = Doctrine_Query::create()
		    ->select("*" )
		    ->from('PatientDrugPlan')
		    ->whereIn('ipid', $ipids)
		    ->andWhere("isdelete = '0'");
// 		    ->andWhere("scheduled = '1'")
		    //->andWhere("scheduled = '1' OR has_interval = '1' ")
		    //->andWhere("administration_date != '0000-00-00 00:00:00'");
		    if($acknowledge_func == "1")
		    {
		        $drugs->andWhereNotIn('id',$declined); // remove declined
		        $drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
		    }
		    //$drugs->having($sql_scheduled_date);
		    $drugs->orderBy("id ASC");
		    $drugsarray = $drugs->fetchArray();


		    $master_medications_ids = array();
		    foreach($drugsarray as $key_drg => $v_drg)
		    {
		        $master_medications_ids[] = $v_drg['medication_master_id'];
		        $drug_ids[] = $v_drg['id'];
		    }

		    $master_tr_array = array();
		    if( ! empty($master_medications_ids)){
		    	//get the data from master medications array
		    	$med = new Medication();
                $medic = Doctrine_Query::create()
                    ->select('*') // @claudiu i've changed it back to *
// 				->select('id, name')
                    ->from('Medication INDEXBY id')
// 				->where("id IN (".$medication_ids_str.")");
                    ->whereIn("id", $master_medications_ids);
                $medic->andWhere('isdelete = "0"');
                $medic->andWhere('is_btm = "1"');
                $medicarr = $medic->fetchArray();
                foreach($medicarr as $md){
                    $master_tr_array[$md['id']] = $md;
                }
		    	//$master_tr_array = $med->master_medications_get($master_medications_ids, false, true);
		    }
		    $drug_ids_btm = [];
            foreach($drugsarray as $key_drg => $v_drg){
                if(isset($master_tr_array[$v_drg['medication_master_id']] )){
                    $drug_ids_btm[] = $v_drg['id'];
                }
            }

//print_r($drug_ids_btm);



		    if(!empty($drug_ids_btm)){
		        $drugs_extra = Doctrine_Query::create()
		        ->select('*')
		        ->from('PatientDrugPlanExtra')
		        ->whereIn('ipid', $ipids)
		        ->andWhere("isdelete = '0'")
		        ->andWhereIn("drugplan_id",$drug_ids_btm);
		        $drugsarray_extra = $drugs_extra->fetchArray();

		        if(!empty($drugsarray_extra)){

		            // get details for indication
		            $indications_array = MedicationIndications::client_medication_indications($clientid);

		            foreach ($indications_array as $ki => $ind_value) {
		                $indication[$ind_value['id']]['name'] = $ind_value['indication'];
		                $indication[$ind_value['id']]['color'] = $ind_value['indication_color'];
		            }

		            foreach($drugsarray_extra as $k=>$extra_data){
		                $drug_indication[$extra_data['drugplan_id']] = $indication[$extra_data['indication']];
		            }

		        }
		    }


//print_r($master_tr_array);

		    foreach($drugsarray as $key => $drugp)
		    {
                if(in_array($drugp['id'], $drug_ids_btm)){
                    $patient_medication[$drugp['ipid']][$drugp['id']]['medication_name'] = $master_tr_array[$drugp['medication_master_id']] ['name'] ;
                    $patient_medication[$drugp['ipid']][$drugp['id']]['dosage'] = $drugp['dosage'];
                    $patient_medication[$drugp['ipid']][$drugp['id']]['indications'] = $drug_indication[$drugp['id']] ;
                    $patient_medication[$drugp['ipid']][$drugp['id']]['comments'] = $drugp['comments'];
                    //$patient_medication[$drugp['ipid']][$drugp['id']]['days_interval'] =   $drugp['days_interval'];
                   // $patient_medication[$drugp['ipid']][$drugp['id']]['due_date'] = $drugp['scheduled_date'];

                }


		    }

		    foreach($ipids as $k_ipid => $v_ipid)
		    {
		        if(!empty($patient_medication[$v_ipid]))
		        {
		            $patient_medication_data['btm_medication_data'][$v_ipid] = $patient_medication[$v_ipid];
		            $patient_medication_data['ipids'][] = $v_ipid;
		        }
		    }

		  // print_r($patient_medication_data);
// 		    exit;

		    return $patient_medication_data;
		}

		
		public function has_bpmedication_icon($ipids)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    $usertype = $logininfo->usertype;
		
		    if(!is_array($ipids))
		    {
		        $ipids = array($ipids);
		    }
		    else
		    {
		        $ipids = $ipids;
		    }
		
		    
		    
// 		    $modules = new Modules();
// 		    if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
		    if ($this->_client_hasModule(111)) //111 = Medication acknowledge
		    {
		        $acknowledge_func = '1';
		    
		        // Get declined data
		        $declined_ids2ipids = PatientDrugPlanAlt::get_declined_patients_drugplan_alt($ipids,false,false,true); // TODO-2320 - added params to retrive ALL denied  20.05.2019 @Ancuta

		        foreach($ipids as $kd=>$ipidd)
		        {
		            foreach($declined_ids2ipids[$ipidd] as $kdd=>$declined_ids)
		            {
		                $declined[] = $declined_ids;
		            }
		             
		        }
		         
		        if(empty($declined)){
		            $declined[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		    
		        //get non approved data
		        $non_approved = PatientDrugPlanAlt::get_patients_drugplan_alt($ipids,false,false,true);
		    
		        foreach($ipids as $k=>$ipid)
		        {
		            foreach($non_approved[$ipid]['change'] as $drugplan_id =>$not_approved)
		            {
		                $not_approved_ids[] = $not_approved['drugplan_id'];
		                 
		                if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
		                    $newly_not_approved[] = $not_approved['drugplan_id'];;
		                }
		            }
		        }
		    
		        if(empty($not_approved_ids)){
		            $not_approved_ids[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		        if(empty($newly_not_approved)){
		            $newly_not_approved[] = "XXXXXXXXXXXXXXXXXXXXX";
		        }
		    }
		    else
		    {
		        $acknowledge_func = '0';
		    }
		    	
		    
		    
		    $drugs = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientDrugPlan')
		    ->whereIn('ipid', $ipids)
		    ->andWhere("isdelete = '0'")
		    ->andWhere("treatment_care = '1'");
		    if($acknowledge_func =="1"){
		        $drugs->andWhereNotIn('id',$declined); // remove declined
		        $drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
		    }
		    $drugs->orderBy("id ASC");
		    $drugsarray = $drugs->fetchArray();
		
		    
		    foreach($drugsarray as $key_drg => $v_drg)
		    {
		        $master_medications_ids[] = $v_drg['medication_master_id'];
		    }
		    
		    if(empty($master_medications_ids)){
		        $master_medications_ids[] = "99999999";
		    }
		    //get the data from master medications array
		    $med = new MedicationTreatmentCare();
		    $master_tr_array = $med->master_medications_get($master_medications_ids, false);

		    
		    foreach($drugsarray as $key => $drugp)
		    {
		        	
		        if($drugp['comments'])
		        {
		            $drugp['comments'] = ' | ' . $drugp['comments'];
		        }
		        $patient_medication[$drugp['ipid']]['BP']['medications'][] = $master_tr_array[$drugp['medication_master_id']]  . $drugp['comments'];
		        
		    }
		    
		    foreach($ipids as $k_ipid => $v_ipid)
		    {
		        if(!empty($patient_medication[$v_ipid]))
		        {
		            $patient_medication_data['medication_data'][$v_ipid] = $patient_medication[$v_ipid];
		            $patient_medication_data['ipids'][] = $v_ipid;
		        }
		    }
		    
		    
		    
		    return $patient_medication_data;
		}		

		
		
		
		public function get_patient_vital_signs($ipids)
		{
			if (empty($ipids)) {
				return;
			}
			
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    $usertype = $logininfo->usertype;	
		    $extra_icon_settings = false;
		    $patient_weight_data = array(); // array to gather the return values
		    
		    if(!is_array($ipids))
		    {
		    	//ISPC-1896 get extra only if one patient, and not a list
		    	$icon_settings_arr = IconsClient::get_client_icon_settings("49", $clientid );		    			    	
		    	if (!empty($icon_settings_arr['icon_settings']) && !is_null($icon_settings = json_decode($icon_settings_arr['icon_settings'], true))) {
		    		$extra_icon_settings = $icon_settings;
		    	}
		    	$original_ipid = $ipids;
		        $ipids = array($ipids);
		    }
		    else
		    {
		        $ipids = $ipids;
		    }
		    
		    $weight = FormBlockVitalSigns::get_patients_weight_chart($ipids,false);
		  
		    foreach($ipids as $k_ipid => $v_ipid)
		    {
		        if(!empty($weight[$v_ipid]))
		        {
		            $patient_weight_data['weight_chart'][$v_ipid] = $weight[$v_ipid];
		            if ($extra_icon_settings !== false) {
		            	$patient_weight_data['icon_settings'][$v_ipid] = $extra_icon_settings;
		            }
		            $patient_weight_data['ipids'][] = $v_ipid;
		        }
		    }
		    if ($extra_icon_settings !== false && $extra_icon_settings['allways_display'] == 1) {
		    	//force icon display
		    	if (!in_array($original_ipid, $patient_weight_data['ipids'])){
		    		$patient_weight_data['ipids'][] = $original_ipid;
		    		$patient_weight_data['allways_display'][$original_ipid] = true;
		    		$patient_weight_data['icon_settings'][$original_ipid] = $extra_icon_settings;
		    	}
		    	
		    	
		    } 
		    return $patient_weight_data;
		}		
 
		/**
		 * 
		 * @param unknown $ipids
		 * @return multitype:
		 * @deprecated - this fn is flawed for multiple ipids , also todoers pseudogroups are not here
		 * when get_patient_todos_V2 will be done, it will replace this fn, until then please don't use any more this 
		 */
		public function get_patient_todos($ipids)
		{
		    $patient_todos_data = array();
		    $todo_data = array();
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    	
		    if(!is_array($ipids))
		    {
		        $ipids = array($ipids);
		    }
		    else
		    {
		        $ipids = $ipids;
		    }
		
		    $todos = new ToDos();
		    $usr = new User();
		    $grp = new Usergroup();
		    
			//get connected user details
		    $userconnected = $usr->getUserDetails($userid, $active = false);
		    
		    // get all users details
		    $cl_users = $usr->get_client_users($clientid,"1");

		    // get all group details
		    $cl_groups_arr = $grp->get_clients_groups(array($clientid));
		    foreach($cl_groups_arr as $k=>$grd){
		        $cl_groups[$grd['id']] = $grd['groupname'];
		    }
		    	
		    //TODO-3784 Lore 27.01.2021
		    // get all pseudogroups details
		    $psdgrp = new UserPseudoGroup();
		    $cl_psdgrp = $psdgrp->get_pseudogroups_for_todo($clientid);
		    $selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
		    //.
		    
		    $td = Doctrine_Query::create()
		    ->select("*")
		    ->from('ToDos')
		    ->where('client_id="' . $clientid . '"')
		    ->andWhereIn('ipid', $ipids)
		    ->andWhere('isdelete=0')
		    ->orderBy('iscompleted ASC,  until_date ASC, ipid ASC');
		    $todosdata = $td->fetchArray();
		    
		    $firsttime = true;
		    $todorow = 1;
		    
		    //print_r($todosdata); exit;
		    foreach($todosdata as $k=>$tdata)
		    {
                if($tdata['course_id'] != 0 ){
                    $todo_grouped[$tdata['ipid']][$tdata['course_id']][] = $tdata;
                } 
		    }
		    
		    
		    foreach($todosdata as $k=>$tdata)
		    {
		    	if($firsttime === true)
		    	{
		    		$todoid = array();
		    		$todouser_name=array();
		    		$todogroup_name= array();
		    		$todoipid = $tdata['ipid'];
		    		$tododata = $tdata['todo'];
		    		$todountil = $tdata['until_date'];
		    		$todo_data[$tdata['ipid']][$todorow]['todo'] = $tdata['todo'];
		    		$todo_data[$tdata['ipid']][$todorow]['triggered_by'] = $tdata['triggered_by'];
		    		$todo_data[$tdata['ipid']][$todorow]['until_date'] = date('d.m.Y', strtotime($tdata['until_date']));
		    		if($tdata['create_date'] != "0000-00-00 00:00:00")
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['create_date'] = date('d.m.Y', strtotime($tdata['create_date']));
		    		}
		    		else
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['create_date'] = "";
		    		}
		    		if($tdata['iscompleted'] == '1') {
		    			$todo_data[$tdata['ipid']][$todorow]['iscompleted'] = 1;
		    			$todo_data[$tdata['ipid']][$todorow]['complete_user'] = $cl_users[$tdata['complete_user']];
		    			if($tdata['complete_date'] != "0000-00-00 00:00:00")
		    			{
		    				$todo_data[$tdata['ipid']][$todorow]['complete_date'] = date('d.m.Y H:i', strtotime($tdata['complete_date'])); // verificare data
		    				$todo_data[$tdata['ipid']][$todorow]['complete_comment'] = $tdata['complete_comment'];
		    			}
		    			else
		    			{
		    				$todo_data[$tdata['ipid']][$todorow]['complete_date'] = "";
		    			}
		    		}
		    		else
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['iscompleted'] = 0;
		    			$todo_data[$tdata['ipid']][$todorow]['complete_user'] = "";
		    			$todo_data[$tdata['ipid']][$todorow]['complete_date'] = "";
		    	
		    		}
		    		$firsttime = false;
		    	}
		    	
		    	if($tododata == $tdata['todo'] && $todountil == $tdata['until_date'])
		    	{
		    		$todo_data[$tdata['ipid']][$todorow][$tdata['id']] = $tdata;
		    		$todoid[] = $tdata['id'];
		    		
		    		if($tdata['user_id'] != '0')
		    		{
		    			$todouser_name[] = $cl_users[$tdata['user_id']];
		    		}
		    		
		    		if($tdata['group_id'] != '0')
		    		{
		    			$todogroup_name[] = $cl_groups[$tdata['group_id']];
		    		}
		    		
		    		
		    		if($tdata['user_id'] == $userid || $tdata['group_id'] == $userconnected[0]['groupid'])
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['hasrighttocomplete'] = 1;
		    		}
		    		
		    		
		    	}
		    	else
		    	{
		    		
		    		/*$todo_data[$tdata['ipid']][$todorow]['row_id'] = $todorow;
		    		$todo_data[$tdata['ipid']][$todorow]['ids'] = $todoid;
		    		
		    		if(!empty($todouser_name))
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['user_name'] = implode('; ', $todouser_name);
		    		}
		    		else 
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['user_name'] = "";
		    		}
		    		
		    		if(!empty($todogroup_name))
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['group_name'] = implode('; ', $todogroup_name);
		    		}
		    		else 
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['group_name'] = "";
		    		}*/
		    		
		    		$todo_data[$todoipid][$todorow]['row_id'] = $todorow;
		    		$todo_data[$todoipid][$todorow]['ids'] = $todoid;
		    		
		    		if(!empty($todouser_name))
		    		{
		    			$todo_data[$todoipid][$todorow]['user_name'] = implode('; ', $todouser_name);
		    		}
		    		else
		    		{
		    			$todo_data[$todoipid][$todorow]['user_name'] = "";
		    		}
		    		
		    		if(!empty($todogroup_name))
		    		{
		    			$todo_data[$todoipid][$todorow]['group_name'] = implode('; ', $todogroup_name);
		    		}
		    		else
		    		{
		    			$todo_data[$todoipid][$todorow]['group_name'] = "";
		    		}
		    		
		    		$todoipid = $tdata['ipid'];
		    		$tododata = $tdata['todo'];
		    		$todountil = $tdata['until_date'];
		    		$todorow++;
		    		$todo_data[$tdata['ipid']][$todorow]['todo'] = $tdata['todo'];
		    		$todo_data[$tdata['ipid']][$todorow]['triggered_by'] = $tdata['triggered_by'];
		    		$todo_data[$tdata['ipid']][$todorow]['until_date'] = date('d.m.Y', strtotime($tdata['until_date']));
		    		if($tdata['create_date'] != "0000-00-00 00:00:00")
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['create_date'] = date('d.m.Y', strtotime($tdata['create_date']));
		    		}
		    		else
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['create_date'] = "";
		    		}
		    		if($tdata['iscompleted'] == '1') {
		    			$todo_data[$tdata['ipid']][$todorow]['iscompleted'] = 1;
		    			$todo_data[$tdata['ipid']][$todorow]['complete_user'] = $cl_users[$tdata['complete_user']];
		    			if($tdata['complete_date'] != "0000-00-00 00:00:00")
		    			{
		    				$todo_data[$tdata['ipid']][$todorow]['complete_date'] = date('d.m.Y H:i', strtotime($tdata['complete_date'])); // verificare data
		    				$todo_data[$tdata['ipid']][$todorow]['complete_comment'] = $tdata['complete_comment'];
		    			}
		    			else
		    			{
		    				$todo_data[$tdata['ipid']][$todorow]['complete_date'] = "";
		    			}
		    		}
		    		else
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['iscompleted'] = 0;
		    			$todo_data[$tdata['ipid']][$todorow]['complete_user'] = "";
		    			$todo_data[$tdata['ipid']][$todorow]['complete_date'] = "";
		    		}
		    		$todoid = array();
		    		$todouser_name =array();
		    		$todogroup_name = array();
		    		
		    		$todo_data[$tdata['ipid']][$todorow][$tdata['id']] = $tdata;
		    		$todoid[] = $tdata['id'];
		    		
		    		if($tdata['user_id'] != '0')
		    		{
		    			$todouser_name[] = $cl_users[$tdata['user_id']];
		    		}
		    		else
		    		{
		    			$todouser_name[] = "-";
		    		}
		    		
		    		if($tdata['group_id'] != '0')
		    		{
		    			$todogroup_name[] = $cl_groups[$tdata['group_id']];
		    		}
		    		else
		    		{
		    		    //$todogroup_name[] = "-";
		    		    //TODO-3784 Lore 27.01.2021
		    		    $have_pseudo = '0';
		    		    if( !empty($tdata['additional_info']))
		    		    {
		    		        $add_info = explode(";",$tdata['additional_info']);
		    		        foreach($add_info as $infos){
		    		            if( strpos($infos, $selectbox_separator_string["pseudogroup"], 0) === 0 ) {
		    		                $have_pseudo = '1';
		    		            }
		    		        }
		    		    } 
		    		    if($have_pseudo == '0'){
		    		        $todogroup_name[] = "-";
		    		    }
		    		    //.

		    		}
		    		
		    		if($tdata['user_id'] == $userid || $tdata['group_id'] == $userconnected[0]['groupid'])
		    		{
		    			$todo_data[$tdata['ipid']][$todorow]['hasrighttocomplete'] = 1;
		    		}
		    		
		    	}
		    	
		    	if(strlen($tdata['additional_info'])  > 0 ){
		    	    $aditional_info = array();
		    	    $ad_info = explode(";",$tdata['additional_info']);
		    	    foreach($ad_info as $info){
		    	        if(substr($info,0,1) == 'g')
		    	        {
// 		    	            $todo_data[$tdata['ipid']][$todorow]['additional_info'][] = $cl_groups[substr($info,1)];
		    	            $aditional_info[] = $cl_groups[substr($info,1)];
		    	        }
		    	        elseif(substr($info,0,1) == 'u')
		    	        {
// 		    	            $todo_data[$tdata['ipid']][$todorow]['additional_info'][] = $cl_users[substr($info,1)];
		    	            $aditional_info[] = $cl_users[substr($info,1)];
		    	        } else {
// 		    	            $todo_data[$tdata['ipid']][$todorow]['additional_info'][] = "alle";
		    	            $aditional_info[] = "alle";
		    	        }
		    	        
		    	        //TODO-3784 Lore 27.01.2021
		    	        if( strpos($info, $selectbox_separator_string["pseudogroup"], 0) === 0 ) {
		    	            $psdgrp_id = substr($info,12);
		    	            if(!in_array($cl_psdgrp[$psdgrp_id]['servicesname'],$todogroup_name)){
		    	                $todogroup_name[] = $cl_psdgrp[$psdgrp_id]['servicesname'];
		    	            }
		    	        }
		    	         
		    	    }
		    	}
		    	
		    	
		    }
		   
		    $todo_data[$tdata['ipid']][$todorow]['row_id'] = $todorow;
		    $todo_data[$tdata['ipid']][$todorow]['ids'] = $todoid;
		    
		    if(!empty($todouser_name))
		    {
		    	$todo_data[$tdata['ipid']][$todorow]['user_name'] = implode('; ', $todouser_name);
		    }
		    else
		    {
		        if(!empty($aditional_info )){
		            
    		    	$todo_data[$tdata['ipid']][$todorow]['user_name'] = implode("; ", $aditional_info);
		        } 
		        else
		        {
    		    	$todo_data[$tdata['ipid']][$todorow]['user_name'] = "";
		        }
		        
		    }
		    
		    if(!empty($todogroup_name))
		    {
		    	$todo_data[$tdata['ipid']][$todorow]['group_name'] = implode('; ', $todogroup_name);
		    }
		    else
		    {
		        if(!empty( $aditional_info)){
		        
		            $todo_data[$tdata['ipid']][$todorow]['group_name'] = implode("; ", $aditional_info);
		        } else{
		            $todo_data[$tdata['ipid']][$todorow]['group_name'] = "";
		        }
		    }
		    
 		 
		    foreach($ipids as $k_ipid => $v_ipid)
		    {
		        if(!empty($todo_data[$v_ipid]))
		        {
		            $patient_todos_data['todo_data'][$v_ipid] = $todo_data[$v_ipid];
		            $patient_todos_data['ipids'][] = $v_ipid;
		        }
		    }
// 		    print_r($patient_todos_data);; exit;
		    //var_dump($patient_todos_data); exit;
		    return $patient_todos_data;
		}
		
		
		/*
		 * @author claudiu
		 * on 28.02.2018
		Problem-1 with inaktive/deleted users that needs to be addressed before it grows !
		example for USER-1 and PATIENT-1
		
		Example-1:
		- assign a TODO : "do job 1" from PATIENT-1 to USER-1
		- now make USER-1 inaktive(or delete)
		the todo is now listed in the icon and in the verlauf of the patient without an asignee
		
		proposition for verlauf and todo icon: inaktive/deleted USER-1 should be displayed with a strikethrough
		
		
		
		Example-2:
		- go to Interne Rechnungen SH > Interne Rechnungen, and find a USER-1
		- make USER-1 inaktive or deleted
		you end up with the numbers on tab show something and invoices with this USER-1 are not shown
		
		proposition: diplay all invoices even if user is inaktive/deleted, and put the same Strikethrough
		
		
		To resume: something that has a inaktive/deleted users should still be visible and marked accordingly
		
		
		
		Problem-2 with TODOs that needs to be addressed
		example for USER-1 and PATIENT-1
		
		Example-1:
		- assign a TODO : "do job 1", due date 01.03.2018, from PATIENT-1 to USER-1
		- assign again a TODO with same details: "do job 1", due date 01.03.2018, from PATIENT-1 to USER-1
		
		you endup with 2 verlauf entryes, and only 1 entry in patient's todo-ICON
		
- if you inserted in the same minute, the 2 verlaufs are grouped together, if not you have 2 rows
- checking from the icon the todo as completed, has 2 posibilities
	- if you inserted in the same minute, marks them both in the verlauf as completed only , and in icon the entry is green
	- if you inserted not in the same minute, marks only one in verlauf as done, and in the icon the entry is red
		
proposition: do not group in the icon */
		public function get_patient_todos_V2($ipids)
		{
		    
		}
		
		public function get_tourplaning_details( $ipids , $clientid = false)
		{
			if (empty($ipids)) {
				return;
			}

			if ($clientid === false){
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
				$userid = $logininfo->userid;
				$usertype = $logininfo->usertype;
			}
			$patient_visits =  array();
			
			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			else
			{
				$ipids = $ipids;
			}
			
			$pat_visits_settings = PatientVisitsSettings :: getPatientVisitsSettingsV2( $ipids , false, true);

			if ( count( $pat_visits_settings ) > 0 ){
				//get also the user that can visit
				$grups = array();
				$allsers = array();
				$visiting_users = User :: get_all_visiting_users_and_groups( $clientid , false, false);
				$visiting_users_array = $visiting_users['user_details'];
				foreach($visiting_users['grups'] as $group_id => $users ){
					$grups[$group_id] = $users['groupname'];
					$allsers = ( $allsers + $users );
					
				}
				
				$visiting_users = array(
						"grups" =>$grups,
						"user" =>$allsers,
						"pseudogrups" => $visiting_users['pseudogrups']
							
				);
				
				$patient_visits['tourplan_settings_users']= $visiting_users;
				$visiting_users['grups']  = $visiting_users['grups'] + $visiting_users['user'];
				
				foreach($pat_visits_settings as $ipid => $visit)
				foreach($visit as $user => $type){
					reset($type); 
					$type = key($type);
					
					if (	($visiting_users_array[ $user ] ['makes_visits'] == "0")
							||
							(	( $visiting_users_array[ $user ] ['isactive'] == "1" )
									&&
								( strtotime($visiting_users_array[ $user ] ['isactive_date']) <= strtotime(date("Y-m-d") ))
							)
						)
					{
						//this doctor was marked as cannot make visit, but he allready has assigned ones
						$pat_visits_settings[$ipid][$user]['is_disabled'] = true;
					}
					
					if ($type == "pseudogrups"){
						if (empty($visiting_users['pseudogrups'][$user])){
							unset( $pat_visits_settings[$ipid][$user] );
						}
					}else{
						if (empty($visiting_users['grups'][$user])){
							unset( $pat_visits_settings[$ipid][$user] );
						}
					}
						
				}
				
				
			}
					
			
			foreach($ipids as $k_ipid => $v_ipid)
			{
				if(!empty($pat_visits_settings[$v_ipid]))
				{
					$patient_visits['tourplan_settings'][$v_ipid] =  $pat_visits_settings[$v_ipid];		
					$patient_visits['ipids'][] = $v_ipid;
				}
			}
			//print_r($patient_visits);die();
			return ($patient_visits);
			
		}
		
		/**
		 * @Ancuta on 21.11.2018
		 * @deprecated
	     * Initial : ISPC-1897 bacteria = germination
		 */
		public function get_patient_germination_status_OLD($ipids) 
		{
			if(empty($ipids)) {
				return;
			}
			if (! is_array($ipids) ) {
				$ipids = array($ipids);
			}
			$germination_status =  array();
			$pat_germs = new PatientGermination();
			$results = $pat_germs->getPatientGermination($ipids , array('colums2fetch'=>'id, ipid, germination_cbox, germination_text'));

			foreach($results  as $ipid => $values) {	
						
				if ( ! empty($values['germination_text']) && trim($values['germination_cbox']) == "1") {
					//show ICON B if there is text added to the text field and the checkbox IS clicked
					$germination_status['germination_status'][$ipid]['status'] = "germination_icon_red";
					$germination_status['germination_status'][$ipid]['germination_cbox'] = trim($values['germination_cbox']);
					//$germination_status['germination_status'][$ipid]['germination_text'] = htmlentities(trim($values['germination_text']));
					$germination_status['germination_status'][$ipid]['germination_text'] = htmlspecialchars(trim($values['germination_text']));
					$germination_status['ipids'][] = $ipid;
					
				} elseif (! empty($values['germination_text']) ) {
					//show ICON A if there is text added to the text field and the checkbox is NOT clicked
					$germination_status['germination_status'][$ipid]['status'] = "germination_icon_nocolor";
					$germination_status['germination_status'][$ipid]['germination_cbox'] = trim($values['germination_cbox']);
					//$germination_status['germination_status'][$ipid]['germination_text'] = htmlentities(trim($values['germination_text']));
					$germination_status['germination_status'][$ipid]['germination_text'] = htmlspecialchars(trim($values['germination_text']));
					$germination_status['ipids'][] = $ipid;
				}				
			}
		
			return ($germination_status);
			
			
		}
		
		
		/**
		 * @Ancuta on 21.11.2018
		 * TODO-1890
		 *
		 * Initial request: ISPC-1897 bacteria = germination 
		 *  
		 * @param unknown $ipids
		 * @return multitype:
		 */
		public function get_patient_germination_status($ipids) 
		{
			if(empty($ipids)) {
				return;
			}
			if (! is_array($ipids) ) {
				$ipids = array($ipids);
			}
			$germination_status =  array();
			$pat_germs = new PatientGermination();
			$results = $pat_germs->getPatientGermination($ipids , array('colums2fetch'=>'id, ipid, germination_cbox, germination_text, iso_cbox'));

			foreach($results  as $ipid => $values) {	
						
				if ( trim($values['germination_cbox']) == "1" && trim($values['iso_cbox']) == "1" ) {
					//show ICON B if there is text added to the text field and the checkbox IS clicked
					$germination_status['germination_status'][$ipid]['status'] = "germination_icon_red";
					$germination_status['germination_status'][$ipid]['germination_cbox'] = trim($values['germination_cbox']);
					$germination_status['germination_status'][$ipid]['iso_cbox'] = trim($values['iso_cbox']);
					//$germination_status['germination_status'][$ipid]['germination_text'] = htmlentities(trim($values['germination_text']));
					$germination_status['germination_status'][$ipid]['germination_text'] = htmlspecialchars(trim($values['germination_text']));
					$germination_status['ipids'][] = $ipid;
					
				} elseif ( trim($values['germination_cbox']) == "1" && trim($values['iso_cbox']) != "1" ) {
					//show ICON A if there is text added to the text field and the checkbox is NOT clicked
					$germination_status['germination_status'][$ipid]['status'] = "germination_icon_nocolor";
					$germination_status['germination_status'][$ipid]['germination_cbox'] = trim($values['germination_cbox']);
					$germination_status['germination_status'][$ipid]['iso_cbox'] = trim($values['iso_cbox']);
					//$germination_status['germination_status'][$ipid]['germination_text'] = htmlentities(trim($values['germination_text']));
					$germination_status['germination_status'][$ipid]['germination_text'] = htmlspecialchars(trim($values['germination_text']));
					$germination_status['ipids'][] = $ipid;
				}				
			}
		
			return ($germination_status);
			
			
		}
		
		
		public function get_patient_nutrition_form($ipids)
		{
			if (empty($ipids)) {
				return;
			}
			
			$icon_status =  array();
			
			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			else
			{
				$ipids = $ipids;
			}
			
			$pat_nf = new NutritionFormular();
			$results = $pat_nf->get_multiple_by_ipid($ipids , array('colums2fetch'=>'id, ipid, formular_values'));
					
			foreach($results  as $key => $values) {
						
				if ( ! empty($values['formular_values']) && is_array($values['formular_values'])) {
									
					foreach($values['formular_values'] as $k=>$v) {
						
						if (isset($v['freetext']) && trim($v['freetext']) != '') {
							//we have one
							$icon_status['ipids'][] = $values['ipid'];
							$icon_status['nutrition_form'][$values['ipid']]['freetext'] = nl2br(htmlentities($v['freetext']));
								
						}
					}
				}
			}
			
			return ($icon_status);
				
		}
		

		// we can do a grafic for this icon .. cause we have multiple contactform with this data ?
		public function get_patient_form_block_ventilation($ipids)
		{
			if (empty($ipids)) {
				return;
			}
			
			$icon_status =  array();
				
			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			else
			{
				$ipids = $ipids;
			}
			
			$pat_fbv = new FormBlockVentilation();
			$results = $pat_fbv->get_multiple_by_ipid($ipids , array('colums2fetch'=>'*'));
			
				
			foreach($results  as $key => $values) {
				//we have one
				$icon_status['ipids'][] = $values['ipid'];
				$icon_status['form_block_ventilation'][$values['ipid']] = $values;
		
			}
		
						
			return ($icon_status);
		}
		
		
		
		public function get_maintenancestage_patientsdd($ipids){

			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}
			
			$start_date = date('Y-m-d');
			$end_date = date('Y-m-d');
			
			$drop = Doctrine_Query::create()
			->select("*")
			->from('PatientMaintainanceStage')
			->whereIn("ipid", $ipids_arr)
			->andWhere("('" . date("Y-m-d", strtotime($start_date)) . "' <= tilldate or '0000-00-00' = tilldate)  and '" . date("Y-m-d", strtotime($end_date)) . "' >= fromdate ")
			->orderBy('fromdate,create_date asc');
			$loc = $drop->fetchArray();
		 
			
			return $loc;
			
			
		}
		public function get_maintenancestage_patients($ipids = false, $filter = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		
			
			 // see if client has block
	 
			if(!$ipids)
			{
				//no ipid list.. use client ipids
				//get client patients
				$actpatient = Doctrine_Query::create()
				->select("p.ipid, e.epid")
				->from('PatientMaster p');
				$actpatient->leftJoin("p.EpidIpidMapping e");
				$actpatient->where('e.clientid = ' . $clientid);
				$actpatient->andwhere('p.isdelete = 0');
	
				$actipidarray = $actpatient->fetchArray();
	
				$act_ipids[] = '999999999';
				foreach($actipidarray as $k_act_ipid => $v_act_ipid)
				{
					$act_ipids[] = $v_act_ipid['ipid'];
					$active_patients[$v_act_ipid['ipid']] = $v_act_ipid; //patient details
				}
			}
			else
			{
				if(is_array($ipids))
				{
					$act_ipids = $ipids;
				}
				else
				{
					$act_ipids[] = $ipids;
				}
			}
	
	
			if($filter)
			{
				$filter_arr = explode('_', $filter);
			}
	
			$act_ipids = array_unique($act_ipids);
	
			//get patient(s) sapvs
			
			$start_date = date('Y-m-d');
			$end_date = date('Y-m-d');
				
			$drop = Doctrine_Query::create()
			->select("*")
			->from('PatientMaintainanceStage')
			->whereIn("ipid", $act_ipids)
// 			->andWhere("('" . date("Y-m-d", strtotime($start_date)) . "' <= tilldate or '0000-00-00' = tilldate)  and '" . date("Y-m-d", strtotime($end_date)) . "' >= fromdate ")
			->andWhere("(CURRENT_DATE()  <= tilldate or '0000-00-00' = tilldate)  and CURRENT_DATE()  >= fromdate ")
// 			->andWhere("stage>0")
			->andWhere("stage !='' ")
// 			->orderBy('fromdate,create_date asc');
			->orderBy('fromdate desc');
			
			if(!is_array($ipids) && count($act_ipids) == 1){
				$drop->limit(1);
			}
			$maintenancestage_res = $drop->fetchArray();
	
			//get sapv images custom or not
			$icons_client = new IconsMaster();
			$icons_sapv = $icons_client->get_system_icons($clientid, false, false, true);
	
			foreach($icons_sapv as $k_icon_sapv => $v_icon_sapv)
			{
				$icons_map[$v_icon_sapv['name']] = $v_icon_sapv;
			}
			foreach($maintenancestage_res as $k => $val)
			{
				if(!empty($val['stage']) && $val['stage'] != "0"   )
				{
					 
					$maintenancestage['ipids'][] = $val['ipid'];
					$maintenancestage['details_current_maintenancestage'][$val['ipid']] = $val;
					$maintenancestage['current_maintenancestage'][$val['ipid']] = $val;
					$maintenancestage['current_maintenancestage'][$val['ipid']]['image'] = $icons_map['maintenancestage_' . $val['stage']]['image'];
					$maintenancestage['current_maintenancestage'][$val['ipid']]['name'] = 'maintenancestage_' . $val['stage'];
					$maintenancestage['current_maintenancestage'][$val['ipid']]['color'] = $icons_map['maintenancestage_' . $val['stage']]['color'];
					$maintenancestage['current_maintenancestage'][$val['ipid']]['status'] = $val['stage'];
				}
			}
	
			if($filter)
			{
//				dd($maintenancestage);
				$filter_arr = explode('_', $filter);
				 
				foreach($maintenancestage['current_maintenancestage'] as $k_ipid => $v_sapv_last)
				{
					if($v_sapv_last['stage'] == $filter_arr['1'])
					{
						$maintenancestage_all['ipids'][] = $k_ipid;
					}
				}
			}
			else
			{
				$maintenancestage_all['ipids'] = $maintenancestage['ipids'];
			}
	
			$maintenancestage['ipids'] = array_values(array_unique($maintenancestage_all['ipids']));
	 
			return $maintenancestage;
		}
		
		
	/**
	 * @cla on 27.06.2018
	 * TODO: this is NOT an icon, and is manualy triggered ! create an icon for it if you want, and auto-call
	 *  
	 * @param array|string $ipids
	 * @return unknown|multitype:multitype:
	 */
	public function get_patient_churches($ipids = array())
	{
	
	    $result = array();
	
	    if (empty($ipids)) {
	        return $result; //fail-safe
	    }
	
	    $ipids = is_array($ipids) ? $ipids : array($ipids);
	    
	    $pat_churches_res = Doctrine_Query::create()
	    ->select('pc.*, c.*')
	    ->from('PatientChurches pc')
	    ->leftJoin('pc.Churches c')
	    ->WhereIn('pc.ipid', $ipids)
	    ->andWhere('pc.isdelete = 0')
	    ->orderBy('pc.create_date DESC')
	    ->fetchArray();
	
	    
	    if ( ! empty($pat_churches_res)) {
	
	        foreach ($pat_churches_res as $row) {
	
	            $result['patient_churches_data'][$row['ipid']][$row['id']] = $row;
	            $result['ipids'][] = $row['ipid'];
	        }
	
	        $result['ipids'] = array_values(array_unique($result['ipids']));
	    }

	    return $result;
	}
	
	
	/**
	 * @cla on 27.06.2018
	 * TODO: this is NOT an icon, and is manualy triggered ! create an icon for it if you want, and auto-call
	 *
	 * @param array|string $ipids
	 * @return unknown|multitype:multitype:
	 */
	public function get_patient_hospiceassociation($ipids = array())
	{
	
	    $result = array();
	
	    if (empty($ipids)) {
	        return $result; //fail-safe
	    }
	    
	    $ipids = is_array($ipids) ? $ipids : array($ipids);
	    
	    $pat_hospiceassociation_res = Doctrine_Query::create()
	    ->select('ph.*, h.*')
	    ->from('PatientHospiceassociation ph')
	    ->leftJoin('ph.Hospiceassociation h')
	    ->WhereIn('ph.ipid', $ipids)
	    ->andWhere('ph.isdelete = 0')
	    ->orderBy('ph.create_date DESC')
	    ->fetchArray();
	
	    if ( ! empty($pat_hospiceassociation_res)) {
	
	        foreach ($pat_hospiceassociation_res as $row) {
	
	            $result['patient_hospiceassociation_data'][$row['ipid']][$row['id']] = $row;
	            $result['ipids'][] = $row['ipid'];
	        }
	
	        $result['ipids'] = array_values(array_unique($result['ipids']));
	    }
	    
	    return $result;
	}
	
	
	
	/**
	 * @cla on 27.06.2018
	 * TODO: this is NOT an icon, and is manualy triggered ! create an icon for it if you want, and auto-call
	 *
	 * 
	 * @param array|string $ipids
	 * @return multitype:
	 */
	private function _get_patient_healthinsurance_subdivisions($ipids = array() , $companyids = array()) 
	{
	    $result = array();
	    	    
	    $ipids = is_array($ipids) ? $ipids : array($ipids);
	    $companyids = is_array($companyids) ? $companyids : array($companyids);
	    
	    $companyids[] = 0; // allow those added manualy.. that have 0
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	        	    
	    //get health insurance subdivizions
	    $symperm = new HealthInsurancePermissions();
	    $divisions = $symperm->getClientHealthInsurancePermissions($clientid);
	    $result['divisions'] = $divisions;
	    
	    if (empty($ipids) || empty($companyids)) {
	        return $result;
	    }
	    
        if( ! empty($divisions)) { 
            
            $hi2s_arr = Doctrine_Query::create()
            ->select("*,AES_DECRYPT(ins2s_name,'" . Zend_Registry::get('salt') . "') as ins2s_name,
						AES_DECRYPT(ins2s_insurance_provider,'" . Zend_Registry::get('salt') . "') as ins2s_insurance_provider,
						AES_DECRYPT(ins2s_contact_person,'" . Zend_Registry::get('salt') . "') as ins2s_contact_person,
						AES_DECRYPT(ins2s_street1,'" . Zend_Registry::get('salt') . "') as ins2s_street1,
						AES_DECRYPT(ins2s_street2,'" . Zend_Registry::get('salt') . "') as ins2s_street2,
						AES_DECRYPT(ins2s_zip,'" . Zend_Registry::get('salt') . "') as ins2s_zip,
						AES_DECRYPT(ins2s_city,'" . Zend_Registry::get('salt') . "') as ins2s_city,
						AES_DECRYPT(ins2s_phone,'" . Zend_Registry::get('salt') . "') as ins2s_phone,
						AES_DECRYPT(ins2s_phone2,'" . Zend_Registry::get('salt') . "') as ins2s_phone2,
						AES_DECRYPT(ins2s_post_office_box,'" . Zend_Registry::get('salt') . "') as ins2s_post_office_box,
						AES_DECRYPT(ins2s_post_office_box_location,'" . Zend_Registry::get('salt') . "') as ins2s_post_office_box_location,
						AES_DECRYPT(ins2s_zip_mailbox,'" . Zend_Registry::get('salt') . "') as ins2s_zip_mailbox,
						AES_DECRYPT(ins2s_email,'" . Zend_Registry::get('salt') . "') as ins2s_email,
						AES_DECRYPT(comments,'" . Zend_Registry::get('salt') . "') as comments,
						AES_DECRYPT(ins2s_fax,'" . Zend_Registry::get('salt') . "') as ins2s_fax,
						AES_DECRYPT(ins2s_iknumber,'" . Zend_Registry::get('salt') . "') as ins2s_iknumber,
						AES_DECRYPT(ins2s_ikbilling,'" . Zend_Registry::get('salt') . "') as ins2s_ikbilling,
						AES_DECRYPT(ins2s_debtor_number,'" . Zend_Registry::get('salt') . "') as ins2s_debtor_number,
						AES_DECRYPT(ins2s_kvnumber,'" . Zend_Registry::get('salt') . "') as ins2s_kvnumber ")
			->from("PatientHealthInsurance2Subdivisions")
			->whereIn("company_id" , $companyids)
			->andWhereIn("ipid", $ipids)
            ->fetchArray();
            
            foreach($hi2s_arr as $row) {
                $result[$row['ipid']][$row['subdiv_id']] = $row;
    
            }
        }
        
	    return $result;
	}
	
	
	/**
	 * @cla on 04.07.2018
	 * TODO: this is NOT an icon, and is manualy triggered ! create an icon for it if you want, and auto-call
	 *
	 * @param unknown $ipids
	 * @return void|multitype:
	 */
	public function get_patient_remedies($ipids = array() ) 
	{
	    if (empty($ipids)) {
	        return; //fail-safe
	    }
	    
	    $ipids = is_array($ipids) ? $ipids : array($ipids);
	    
	    $PatientRemedies_res = Doctrine_Query::create()
	    ->select('*')
	    ->from('PatientRemedies')
	    ->whereIn('ipid', $ipids)
	    ->andWhere("isdelete=0")
	    ->fetchArray();
	    
	    if ( ! empty($PatientRemedies_res)) {
	        
	        foreach ($PatientRemedies_res as $row) {
	    
	            $result['patient_remedies_data'][$row['ipid']][$row['id']] = $row;
	            $result['ipids'][] = $row['ipid'];
	        }
	    
	        $result['ipids'] = array_values(array_unique($result['ipids']));
	    }
	     
	    return $result;
	    
	}
		
	
	public function get_patient_contact_persons_custodians ($ipids = array() )
	{
	    if (empty($ipids)) {
	        return; //fail-safe
	    }
	    
	    //TODO-3187 Ancuta 05.06.2020 - re-add module created in ISPC-1539
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $modules = new Modules();
	    if(!$modules->checkModulePrivileges("102",$clientid))
	    {
	        // Do not show if NO module
	        return;
	    }
	    // -- 
	    
	    
        $result = [];
        	    
	    $ipids = is_array($ipids) ? $ipids : array($ipids);
	    
	    $cps = ContactPersonMaster::getAllPatientContact($ipids); //this are grouped by ipid
	    
	    if ( ! empty($cps)) {
	    
	        foreach ($cps as $kipid => $rows) {
	            $rows = array_filter($rows, function ($row) {
	                return ! empty($row['cnt_custody']) || $row['cnt_custody_val'] == 1;
	            });
	            
	            if ( ! empty($rows)) {
	                
	                ContactPersonMaster::beautifyName($rows);
	                
	                usort($rows, array(new Pms_Sorter('cnt_custody_val'), "_number_desc"));
	                
                    $result['patient_contact_persons_custodians'][$kipid] = $rows;
    	                 
    	            $result['ipids'][] = $kipid;
	            }
	            
	            
	        }
	    
	        $result['ipids'] = array_values(array_unique($result['ipids']));
	    }
	    
	    return $result;
	    
	     
	}
	
	// ISPC-2564 Andrei 26.05.2020
	public function get_icon_patient_rass ($ipids = array() )
	{
	    if (empty($ipids)) {
	        return; //fail-safe
	    }
	    
	    $result = [];
	    
	    $ipids = is_array($ipids) ? $ipids : array($ipids);
	    
	    $rass = PatientRassTable::getAllPatientRass($ipids); //this are grouped by ipid
	    
	    if ( ! empty($rass)) {
	        
	        foreach ($rass as $kipid => $rows) {
	            $result['patient_rass'][$kipid] = $rows;
	            $result['ipids'][] = $kipid;
	        }
	        
	        $result['ipids'] = array_values(array_unique($result['ipids']));
	    }
	    
	    return $result;
	    
	}
	
	public function get_icon_patient_measure($ipids)
	{
		if (empty($ipids)) {
			return;
		}
		
		$ipids = is_array($ipids) ? $ipids : array($ipids);
			
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$usertype = $logininfo->usertype;
		
		$result = array();
	
		$pcrpr = new PatientCurrentProblems();
		$problems = $pcrpr->get_patients_problems($ipids, 'measure');
		
		if ( ! empty($problems)) {
		
			foreach ($problems as $row) {
		
				$result['measure'][$row['ipid']] = $row;
				$result['ipids'][] = $row['ipid'];
			}
		
			$result['ipids'] = array_values(array_unique($result['ipids']));
		}
		else
		{
			foreach($ipids as $vipid)
			{
				$result['ipids'][] = $vipid;
			}
		}
		 
		return $result;
	}
	
	public function get_icon_patient_current_situation($ipids)
	{
		if (empty($ipids)) {
			return;
		}
	
		$ipids = is_array($ipids) ? $ipids : array($ipids);
			
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$usertype = $logininfo->usertype;
	
		$result = array();
	
		$pcrpr = new PatientCurrentProblems();
		$problems = $pcrpr->get_patients_problems($ipids, 'current_situation');
	
		if ( ! empty($problems)) {
	
			foreach ($problems as $row) {
	
				$result['current_situation'][$row['ipid']] = $row;
				$result['ipids'][] = $row['ipid'];
			}
	
			$result['ipids'] = array_values(array_unique($result['ipids']));
		}
		else
		{
			foreach($ipids as $vipid)
			{
				$result['ipids'][] = $vipid;
			}
		}
			
		return $result;
	}
	
	public function get_icon_patient_sapv_appl($ipids)
	{
		if (empty($ipids)) {
			return;
		}
	
		$ipids = is_array($ipids) ? $ipids : array($ipids);
			
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$usertype = $logininfo->usertype;
	
		$result = array();
	
		$pcrpr = new PatientCurrentProblems();
		$problems = $pcrpr->get_patients_problems($ipids, 'sapv_appl');
	
		if ( ! empty($problems)) {
	
			foreach ($problems as $row) {
	
				$result['sapv_appl'][$row['ipid']] = $row;
				$result['ipids'][] = $row['ipid'];
			}
	
			$result['ipids'] = array_values(array_unique($result['ipids']));
		}
		else 
		{
			foreach($ipids as $vipid)
			{
				$result['ipids'][] = $vipid;
			}
		}
			
		return $result;
	}
	
	/*
	 * TODO-3707 Lore 06.01.2021
	 * ISPC-2261
	 */
	public function get_icon_patient_ventilation($ipids)
	{
	    if (empty($ipids)) {
	        return;
	    }
	    
	    $ipids = is_array($ipids) ? $ipids : array($ipids);
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    $usertype = $logininfo->usertype;
	    
	    $result = array();
	    
	    $pcrpr = new PatientCurrentProblems();
	    $problems = $pcrpr->get_patients_problems($ipids, 'ventilation');
	    
	    if ( ! empty($problems)) {
	        
	        foreach ($problems as $row) {
	            
	            $result['ventilation'][$row['ipid']] = $row;
	            $result['ipids'][] = $row['ipid'];
	        }
	        
	        $result['ipids'] = array_values(array_unique($result['ipids']));
	    }
	    else
	    {
	        foreach($ipids as $vipid)
	        {
	            $result['ipids'][] = $vipid;
	        }
	    }
	    
	    return $result;
	}
	
	
	public function get_icon_patient_active_emergencyplan_version($ipids)
	{
		//ISPC - 2129
		if (empty($ipids)) {
			return;
		}
		
		$ipids = is_array($ipids) ? $ipids : array($ipids);
		
		$actv = new PatientFileVersion();
		$factv = $actv->get_reset_active_version($ipids, true);
		
		$result = array();
		
		if ( ! empty($factv)) {
		
			foreach ($factv as $row) {
		
				$result['activefileup'][$row['PatientFileUpload']['ipid']] = $row['file'];
				$result['ipids'][] = $row['PatientFileUpload']['ipid'];
			}
		
			$result['ipids'] = array_values(array_unique($result['ipids']));
		}
		else
		{
		    // TODO-2690 Lore  26.11.2019
		    // comented - so the icon it is NOT shown  if no data
            /* 			
            foreach($ipids as $vipid)
			{
				$result['ipids'][] = $vipid;
			} 
			*/
		    $result['ipids']= array();
		    
		}
		//var_dump($result); exit;	
		return $result;
		
	}
	
	public function get_icon_patient_verordnung($ipids)
	{
		//ISPC - 1148 - not used yet
		if (empty($ipids)) {
			return;
		}
	
		$ipids = is_array($ipids) ? $ipids : array($ipids);
		
		//get sapv images custom or not
		$icons_client = new IconsMaster();
		$icons_sapv = $icons_client->get_system_icons($clientid, false, false, true);
		
		$entity = new SapvVerordnung();
		$sapv_saved = $entity->_fetch_multiple_SAPV_division($ipids);		
		
		if ( ! empty($sapv_saved)) {
			$sapv_saved_active= array();
			foreach ($sapv_saved as $sapv_ipid) {
				foreach($sapv_ipid as $row)
				{
					$sapv_saved_active[$row['ipid']] = $row;
					$sapv_saved_active['ipids'][] = $row['ipid'];
				}
			}
			
			$last_sapv = array();
			foreach ($sapv_saved as $sapv_ipid) {
				$verordnungam_max = strtotime($sapv_ipid[0]['verordnungam']);
				foreach($sapv_ipid as $row)
				{
					if($verordnungam_max <= strtotime($row['verordnungam']))
					{
						$last_sapv[$row['ipid']] = $row;
						$last_sapv['ipids'][] = $row['ipid'];
					}
				}
			}
			
		}
		
		$sapvicon = array();
		foreach($ipids as $ipid)
		{
			if(in_array($ipid, $sapv_saved_active['ipids']))
			{
				$sapv_icon[$ipid] = $sapv_saved_active[$ipid];
			}
			else if(in_array($ipid, $last_sapv['ipids']))
			{
				$sapv_icon[$ipid] = $last_sapv[$ipid];
			}
		}
		
		if ( ! empty($sapv_icon)) {
		
			foreach ($sapv_icon as $row) {		
					$result['patient_verordnungs'][$row['ipid']] = $row;
					$result['ipids'][] = $row['ipid'];
			}
		
			$result['patient_verordnungs']['icons'] = $icons_sapv;
			$result['ipids'] = array_values(array_unique($result['ipids']));
		}
		//var_dump($result); exit;
		return $result;
	
	}
	
	

    /**
     * @author Ancuta
     * 12.09.2019
     * ISPC-2411
     * function edited on 16.12.2019  
     * @param unknown $ipids
     * @return void|Ambigous <multitype:string , multitype:>
     */
	public function get_icon_patient_ipos ($ipids = array() )
	{
	    if (empty($ipids)) {
	        return; //fail-safe
	    }
	    $ipids = is_array($ipids) ? $ipids : array($ipids);

	    //TODO-3534 Ancuta 20.10.2020
	    if (Zend_Registry::isRegistered('mypain')) {
	        $mypain_cfg = Zend_Registry::get('mypain');
	        $survey_id = $mypain_cfg['ipos']['survey'];
	    }
	    else 
	    {
	        return; //fail-safe
	    }
	    
	    if(!isset($survey_id) || empty($survey_id)){
	        return; //fail-safe
	    }
	    // -- 

	    
	    $result = [];
	    // check if ipos data
	    $surveys_q = Doctrine_Query::create()
	    ->select("pss.id as pateint, sp2c.id as patient_chain_id,  pss.*,sp2c.*")
	    ->from('PatientSurveySettings pss');
	    $surveys_q->leftJoin("pss.SurveyPatient2chain sp2c");
	    $surveys_q->where('pss.id = sp2c.patient');
	    $surveys_q->andWhereIn('pss.ipid',$ipids);
	    $surveys_q->andwhere('sp2c.isdelete = 0');
	    $surveys_q->andwhere('sp2c.end != "0000-00-00 00:00:00" ');
	    $survey_data_array = $surveys_q->fetchArray();


	    
	    
	    $chain_ids  = array();
	    $chain_id2ipid  = array();
	    $chain_id2date  = array();
	    foreach($survey_data_array as $k=>$pss){
	        if(!empty($pss['SurveyPatient2chain'])){
	            foreach($pss['SurveyPatient2chain'] as $survey_k=>$chain_row){
        	       $chain_ids[] =$chain_row['id'];
        	       $chain_id2ipid [$chain_row['id']]= $pss['ipid'];
        	       $chain_id2date [$chain_row['id']]= $chain_row['end']; //ISPC-2411 Ancuta punctul 1 :: 04.06.2020  
	            }
	        }
	    }
	    
	    if(empty($chain_ids)){
	        $result['ipids']= array();;
	        return;
	    }
	    

	    // get_ survey scores definition
	    $survey_scores_definition = Doctrine_Query::create()
	    ->select("*")
	    ->from('SurveySurveyScores')
	    ->where('survey = ?',$survey_id)//TODO-3534 Ancuta 20.10.2020
	    ->fetchArray();
	     
	    $score_definition = array();
	    $result['patient_ipos_results']['labels'] = array(
	        'date'=>'Datum',
	        //'status'=>'Status', //Ancuta 04.05.2020 - commented - as status is no longer listed 
	    );
        $result['patient_ipos_results']['labels']['f10'] = "Ermittlung"; //Ancuta 04.05.2020
	    foreach($survey_scores_definition as $k=>$sc_Def){
	        $score_definition[$sc_Def['id']][$sc_Def['survey']] = $sc_Def['score'];
	        $result['patient_ipos_results']['labels'][$sc_Def['score']] = $sc_Def['score'];
	    }
        $result['patient_ipos_results']['labels']['f1'] = "Hauptprobleme";
        $result['patient_ipos_results']['labels']['additional_sym'] = "zus. Symptom";
	    
	    // get all chain results
	    $chain_results_q= Doctrine_Query::create()
	    ->select("*")
	    ->from('SurveyResultScores')
	    ->whereIn("survey_took",$chain_ids)
	    ->fetchArray();
	    
	    if(empty($chain_results_q)){
	        $result['ipids']= array();;
	        return;
	    }
	    
	    
	    // get all survey results
	    $survey_results_q= Doctrine_Query::create()
	    ->select("*")
	    ->from('SurveyResults')
	    ->whereIn("survey_took",$chain_ids)
	    ->fetchArray();
	    
	    
	    if(empty($chain_results_q)){
	        $result['ipids']= array();;
	        return;
	    }

	    // get status details
	    $status_array = Doctrine_Query::create()
	    ->select("*")
	    ->from('PatientCrisisHistory')
	    ->whereIn("ipid",$ipids)
	    ->orderBy("status_date ASC")
	    ->fetchArray();
	    
	    $status_arr=array();
	    foreach($status_array as $k=>$st){
	        $status_arr[$st['ipid']][] = $st;
	    }
	    
	    $patient_day2status = array();
	    foreach($ipids as $k => $ipid){
            $sident = 0;
            foreach($status_arr[$ipid]  as $sk => $sdata){
                 
                $gap = 0 ;
                if($status_arr[$ipid][$sk+1]['status_date']){
                     
                    $date1 = new DateTime($sdata['status_date']);
                    $date2 = new DateTime($status_arr[$ipid][$sk+1]['status_date']);
                    $gap =  date_diff($date1, $date2)->days;
                } else{
                    if($sdata['status_date']< date("Y-m-d H:i:s")){
                        $date1 = new DateTime($sdata['status_date']);
                        $date2 = new DateTime(date("Y-m-d H:i:s"));
                        $gap =  date_diff($date1, $date2)->days;
                    }	                    
                }
                 
                $add_date ='+'.$gap. 'days';
                $prs[$ipid][$sident]['start'] = $sdata['status_date'];
                $prs[$ipid][$sident]['end'] = date("Y-m-d H:i:s",strtotime($add_date,strtotime($sdata['status_date'])));
                $prs[$ipid][$sident]['days'] = PatientMaster::getDaysInBetween(date("Y-m-d", strtotime($prs[$ipid][$sident]['start'])), date("Y-m-d", strtotime($prs[$ipid][$sident]['end'])), false);
                $prs[$ipid][$sident]['status'] = $sdata['crisis_status'];
                
                if($sdata['crisis_status'] != $status_arr[$ipid][$sk+1]['crisis_status'] || $gap > 1
                ){
                    $sident++;
                }
            }
            
            foreach($prs[$ipid] as $int=>$intvalues){
                foreach($intvalues['days'] as $ik=>$status_day){
                    $patient_day2status[$ipid][$status_day] = $intvalues['status']; 
                    
                }
            }
	    }

	    $pm_status_arr = Doctrine_Query::create()
	    ->select("ipid,traffic_status,admission_date,isdischarged")
	    ->from('PatientMaster')
	    ->whereIn("ipid",$ipids)
	    ->fetchArray();
	      
	    $status_details = array();
	    foreach($pm_status_arr as $k=>$pdata){
	        $status_details[$pdata['ipid']]['start'] = $pdata['admission_date'];
	        $status_details[$pdata['ipid']]['end'] = date('Y-m-d H:i:s');
	        $status_details[$pdata['ipid']]['days'] = PatientMaster::getDaysInBetween(date("Y-m-d", strtotime( $status_details[$pdata['ipid']]['start'] )), date("Y-m-d", strtotime($status_details[$pdata['ipid']]['end'] )), false);
	        $status_details[$pdata['ipid']]['status'] = $pdata['traffic_status'];
	    }
	    
	    foreach($ipids as $k => $ipid){
	        if(empty($patient_day2status[$ipid])){
	     
	            foreach($status_details[$ipid]['days'] as $iks=>$pm_status_day){
	                $patient_day2status[$ipid][$pm_status_day] = $status_details[$ipid]['status'];
	            }
	        }
	    }
	    $survey_results = array();
	    foreach($chain_results_q as $l=>$row){
	        $score_name = $score_definition[$row['score']][$row['survey']];
	        $pateint_ipid = $chain_id2ipid[$row['survey_took']];
	        $survey_results[ $pateint_ipid ][$row['survey_took']]['date']      = date('d.m.Y',strtotime( $chain_id2date[$row['survey_took']])) ;
	        $survey_results[ $pateint_ipid ][$row['survey_took']]['status']    = $patient_day2status[$pateint_ipid][date('Y-m-d',strtotime( $chain_id2date[$row['survey_took']]))];
	        $survey_results[ $pateint_ipid ][$row['survey_took']][$score_name] =  (int)$row['value'] != '0' ? ((int)$row['value']/2)-1 : '';
// 	        $survey_results[ $pateint_ipid ][$row['survey_took']][$score_name] = (int)$row['value'];
// 	        $survey_results[ $pateint_ipid ][$row['survey_took']][$score_name] = $row['value'];
	    }
	   
// 	    1adfdf7e186e91af98834c7cae90829dca1442ec
	    $f10_mapping = array('1'=> 'Selbst','2'=> 'Angehörige','3'=> 'Mitarbeitende',);//Ancuta 04.05.2020
	    $f1_question_answer = array();
	    $f10_question_answer = array();
	    $additional_sym = array();
	    if(!empty($survey_results_q)){
	        foreach($survey_results_q as $k=>$sr_data){
	            $pateint_ipid = $chain_id2ipid[$sr_data['survey_took']];
	            if($sr_data['question'] == '8694' && $sr_data['answered'] == '1'  ){
// 	                $survey_results[ $pateint_ipid ][$sr_data['survey_took']]["HaupPLM"][] = $sr_data['answer'];
	                $f1_question_answer[ $pateint_ipid ][$sr_data['survey_took']][] = $sr_data['answer'];
	            }
	            
	            //Ancuta 04.05.2020
	            if($sr_data['question'] == '8700' && $sr_data['answered'] == '1'  ){
	                $f10_question_answer[ $pateint_ipid ][$sr_data['survey_took']] = $f10_mapping[$sr_data['answer']] ;
	            }
	            //Ancuta 05.05.2020
                if($sr_data['question'] == '8857' && $sr_data['answered'] == '1' ){
                    
                    if(! is_numeric($sr_data['answer'])){
                        $additional_sym[ $pateint_ipid ][$sr_data['survey_took']][$sr_data['row']]['name'] = $sr_data['answer'];
                    } else{
                        $additional_sym[ $pateint_ipid ][$sr_data['survey_took']][$sr_data['row']]['value'] = $sr_data['answer'];
                    }
	            }
	            // --
	        }
	    }
	    foreach($ipids as $ipid){
	        foreach($survey_results[$ipid] as $stook=>$sc_data){
    	        if(!empty($f1_question_answer[$ipid])){
    	            $survey_results[$ipid][$stook]['f1'] = implode('; ',$f1_question_answer[$ipid][$stook]); 
        	    }
        	    //Ancuta 04.05.2020
    	        if(!empty($f10_question_answer[$ipid])){
    	            $survey_results[$ipid][$stook]['f10'] = $f10_question_answer[$ipid][$stook]; 
        	    }
        	    //Ancuta 05.05.2020
        	    if(!empty($additional_sym[$ipid][$stook])){
        	        $survey_results[$ipid][$stook]['additional_sym'] = $additional_sym[$ipid][$stook]; 
        	    }
        	    //--
	        }
	    } 
	    //
	    if(empty($survey_results)){
	        $result['ipids']= array();;
	        return;
	    }
	     
	    if ( ! empty($survey_results)) {
	        
	        foreach ($survey_results as $kipid => $rows) {
	      
	                if ( ! empty($rows)) {
	                     
	                    $result['patient_ipos_results'][$kipid] = $rows;
	
	                    $result['ipids'][] = $kipid;
	                }
	        }
	         
	        $result['ipids'] = array_values(array_unique($result['ipids']));
	    }
	    
	    return $result;
	
	}
	
	
	/**
	 * @author ANcuta
	 * 05.09.2019
	 * ISPC-2420
	 * Demstepcare_upload - 10.09.2019 Ancuta
	 * @param unknown $ipids
	 * @return multitype:|Ambigous <multitype:, unknown, multitype:unknown >
	 * New upload 08.10.2019
	 */
	
	
	public function get_demstepcare_status_notcompleted($ipids)
	{
	    $result = array();
	
	    if (empty($ipids)
	        || ! $this->_client_hasModule(196)) //196 = demstepcare module
	    {
	        return $result; //fail-safe
	    }
	
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	
	    $patients_ipids = is_array($ipids) ? $ipids : array($ipids);
	
	    
	    $demstepcare_forms = array();
	    $patients_d = Doctrine_Query::create()
	    ->select("*")
	    ->from('PatientDemstepcare')
	    ->whereIn('ipid', $patients_ipids);
	    $demstepcare_forms = $patients_d->fetchArray();
	    
	    $completed_forms = array();
	    foreach($demstepcare_forms as $k=>$dms){
	        if($dms['dementia_diagnosis'] != "0" && $dms['cerebral_imaging'] != "0" && $dms['laboratory'] != "0")
	        $completed_forms[] = $dms;
	    }
	    $ipids_completed = array();
	    if ( ! empty($completed_forms)) {
	        $ipids_completed = array_unique(array_column($completed_forms, 'ipid'));
	    }
	    
	    foreach ($patients_ipids as $ipid) {
	
	        if ( in_array($ipid,$ipids_completed)) {
	
// 	            $result['demstepcare_status'][$ipid]['status'] = 'dsc_completed';
// 	            $result['ipids'][] = $ipid;
	
	        } else {
	
	            $result['demstepcare_status'][$ipid]['status'] = 'dsc_not_completed';
	            $result['ipids'][] = $ipid;
	
	        }
	    }
	    
	    
	    return $result;
	}
	
	/**
	 * @author Ancuta
	 * 16.09.2019
	 * ISPC-2455 Demstepcarestatus
	 */
	public function get_demstepcare_status_completed($ipids)
	{
	    $result = array();
	
	    if (empty($ipids)
	        || ! $this->_client_hasModule(196)) //196 = demstepcare module
	    {
	        return $result; //fail-safe
	    }
	
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	
	    $patients_ipids = is_array($ipids) ? $ipids : array($ipids);
	
	    
	    $demstepcare_forms = array();
	    $patients_d = Doctrine_Query::create()
	    ->select("*")
	    ->from('PatientDemstepcare')
	    ->whereIn('ipid', $patients_ipids);
	    $demstepcare_forms = $patients_d->fetchArray();
	    
	    $completed_forms = array();
	    foreach($demstepcare_forms as $k=>$dms){
	        if($dms['dementia_diagnosis'] != "0" && $dms['cerebral_imaging'] != "0" && $dms['laboratory'] != "0")
	        $completed_forms[] = $dms;
	    }
	    if(empty($completed_forms)){
	         return $result; //fail-safe
	    }
	    
	    $ipids_completed = array();
	    if ( ! empty($completed_forms)) {
	        $ipids_completed = array_unique(array_column($completed_forms, 'ipid'));
	    }
	    
	    foreach ($patients_ipids as $ipid) {
	
	        if ( in_array($ipid,$ipids_completed)) {
	
	            $result['demstepcare_status'][$ipid]['status'] = 'dsc_completed';
	            $result['ipids'][] = $ipid;
	        }
	    }
	    
	    
	    return $result;
	}
	
	/**
	 * @author Carmen
	 * 30.01.2020
	 * ISPC-2508 Künstliche Zugänge - Ausgänge icon
	 * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
	 */
	public static function get_patient_artificial_entries_exits_expired($ipids)
	{
		$Tr = new Zend_View_Helper_Translate();
		$result = array();
	
		if (empty($ipids))
		{
			return $result; //fail-safe
		}
	
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
	
		$ipids = is_array($ipids) ? $ipids : array($ipids);
	
		//get the options box from the client list
		$client_options = ArtificialEntriesExitsListTable::getInstance()->findByClientid($clientid, Doctrine_Core::HYDRATE_ARRAY);
		 
		$patients_artificial_entries_exits_expired = array();
		$patientsart = Doctrine_Query::create()
		->select("*")
		->from('PatientArtificialEntriesExits')
		->whereIn('ipid', $ipids)
		->andWhere('isremove  = "0"')
		->fetchArray();		 
		
		if(empty($patientsart)){
			return $result; //fail-safe
		}
		//var_dump($patientsart); exit;
		
		$current_date = date('Y-m-d H:i:s');
		foreach($patientsart as $kr => $vr)
		{
			$optkey = array_search($vr['option_id'], array_column($client_options, 'id'));
			if($optkey !== false)
    		{
    			$option_valability = $client_options[$optkey]['days_availability'];
    			$option_name = $client_options[$optkey]['name'];
    		}
    		
    		$option_age =  Pms_CommonData::get_days_number_between($current_date, $vr['option_date']);
    		
    		if($option_valability > 0 && $option_age > $option_valability)
    		{
    			$result['patient_artificial_entries_exits_expired'][$vr['ipid']][$vr['id']]['option_name'] = $option_name;
    			$result['patient_artificial_entries_exits_expired'][$vr['ipid']][$vr['id']]['option_date'] = date('d.m.Y', strtotime($vr['option_date']));
    			$result['patient_artificial_entries_exits_expired'][$vr['ipid']][$vr['id']]['option_localization'] = $vr['option_localization'];
    			$result['patient_artificial_entries_exits_expired'][$vr['ipid']][$vr['id']]['option_age'] = $option_age .' ' . $Tr->translate('days');
    			/* $result['patient_artificial_entries_exits_expired'][$vr['ipid']][$vr['id']]['actions'] = '<span class="set_patient_artificial_setting" data-action ="remove" data-setting_id = "' . $vr["id"] . '"><img title="'.$Tr->translate("notneeded").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_remove.png" /></span>';
    			$result['patient_artificial_entries_exits_expired'][$vr['ipid']][$vr['id']]['actions'] .= '<span class="set_patient_artificial_setting" data-action="refresh" data-setting_id = "' . $vr["id"] . '"><img title="'.$Tr->translate("refresh").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_renew.png" /></span>';
    			$result['patient_artificial_entries_exits_expired'][$vr['ipid']][$vr['id']]['actions'] .= '<span class="set_patient_artificial_setting" data-action="delete" data-setting_id = "' . $vr["id"] . '"><img title="'.$Tr->translate("delete").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_delete.png" /></span>'; */
    			$result['patient_artificial_entries_exits_expired'][$vr['ipid']][$vr['id']]['actions'] .= '<span class="set_patient_artificial_setting" data-recid = "' . $vr["id"] . '" data-openfrom="icon"><img title="'.$Tr->translate("actions").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/edit.png" /></span>'; //ISPC-2508 Carmen 25.05.2020 new design
    			$result['ipids'][] = $vr['ipid'];
    			
    		}    		
		}
		
		$result['ipids'] = array_values(array_unique($result['ipids']));
		//var_dump($result); exit;
		return $result;
	}
	
	/**
	 * @author ANcuta 10.02.2020
	 * ISPC-2507
	 * @param unknown $ipids
	 * @return unknown|array[]
	 */
	public function get_pharma_drugplan_request_v1($ipids){
	    
	    $Tr = new Zend_View_Helper_Translate();
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	        $userid = $logininfo->userid;
	        $usertype = $logininfo->usertype;
	        
	        if(!is_array($ipids))
	        {
	            $ipids = array($ipids);
	        }
	        else
	        {
	            $ipids = $ipids;
	        }
	        
	        
	        // get only request of patient is they are assigned to current doctor 
	        $patient_request_pending_array = Doctrine_Query::create()
	        ->select('*')
	        ->from('PharmaRequestsReceived')
	        ->whereIn("ipid", $ipids  )
// 	        ->andWhere("doctor_id = ? ", $userid )
// 	        ->andWhere("processed = ? ", "no" )
	        ->fetcharray();
	        
	        if(empty($patient_request_pending_array)){
	            return $result; //fail-safe
	        }
	        $usr = new User();
	        $all_users = $usr->getUserByClientid($clientid, '1', true);
	        

	        $result = array();
	        $request_entries = array();
	        foreach($patient_request_pending_array as $k=>$req_data){
	            $request_entries[$req_data['ipid']][$req_data['processed']][$req_data['request_id']]['request_id'] = $req_data['request_id'];
	            $request_entries[$req_data['ipid']][$req_data['processed']][$req_data['request_id']]['request_user_name'] = $all_users[$req_data['request_user']];
	            $request_entries[$req_data['ipid']][$req_data['processed']][$req_data['request_id']]['doctor_names'][] = $all_users[$req_data['doctor_id']];
	            $request_entries[$req_data['ipid']][$req_data['processed']][$req_data['request_id']]['processed'] = ($req_data['processed'] == 'yes')? 'Ja':'Nein';
	            $request_entries[$req_data['ipid']][$req_data['processed']][$req_data['request_id']]['status'] = $req_data['processed'];
	            $request_entries[$req_data['ipid']][$req_data['processed']][$req_data['request_id']]['create_date'] = date('d.m.Y H:i',strtotime($req_data['create_date']));
	        }
	        
	        foreach($request_entries as $ipid => $request_details)
	        {
	            foreach($request_details['no'] as $request_id  => $req){
	                
	                $result['pharma_drugplan_request'][$ipid][$request_id]['user'] = $req['request_user_name'];
	                $result['pharma_drugplan_request'][$ipid][$request_id]['request_date'] = $req['create_date'];
	                $result['pharma_drugplan_request'][$ipid][$request_id]['sent_to'] = implode(';<br/> ',$req['doctor_names']);
	                $result['pharma_drugplan_request'][$ipid][$request_id]['processed'] = $req['processed'];
	                $result['pharma_drugplan_request'][$ipid][$request_id]['status'] = $req['status'];
	                $result['pharma_drugplan_request'][$ipid][$request_id]['link'] =  '<a href="'.APP_BASE .'pharmarequests/requestpage?request_id='.$request_id.'" target="_blank">'.$Tr->translate('Go to request page').'</a> ';
	                $result['ipids'][] = $ipid;
	            }
	            foreach($request_details['yes'] as $request_id  => $req){
	                
	                $result['pharma_drugplan_request'][$ipid][$request_id]['user'] = $req['request_user_name'];
	                $result['pharma_drugplan_request'][$ipid][$request_id]['request_date'] = $req['create_date'];
	                $result['pharma_drugplan_request'][$ipid][$request_id]['sent_to'] = implode(';<br/> ',$req['doctor_names']);
	                $result['pharma_drugplan_request'][$ipid][$request_id]['processed'] = $req['processed'];
	                $result['pharma_drugplan_request'][$ipid][$request_id]['status'] = $req['status'];
	                $result['pharma_drugplan_request'][$ipid][$request_id]['link'] =  '<a href="'.APP_BASE .'pharmarequests/requestpage?request_id='.$request_id.'" target="_blank">'.$Tr->translate('Go to request page').'</a> ';
	                $result['ipids'][] = $ipid;
	            }
	        }
	        
	        $result['ipids'] = array_values(array_unique($result['ipids']));
	        //var_dump($result); exit;
	        return $result;
	        
	}
	
	
	/**
	 * ISPC-2507 Ancuta 24.02.2020
	 * 
	 * @param unknown $ipids
	 * @return unknown|array[]
	 */
	public function get_pharma_drugplan_request($ipids){
	    
	    $Tr = new Zend_View_Helper_Translate();
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	        $userid = $logininfo->userid;
	        $usertype = $logininfo->usertype;
	        
	        if(!is_array($ipids))
	        {
	            $ipids = array($ipids);
	        }
	        else
	        {
	            $ipids = $ipids;
	        }
	        $result = array();
	        
	        $patient_request_pending_array = Doctrine_Query::create()
	        ->select('*')
	        ->from('PharmaPatientRequests')
	        ->whereIn("ipid", $ipids  )
    	    ->andWhere("processed = ? ", "no" )
	        ->fetcharray();
	        
	        if(empty($patient_request_pending_array)){
	            return $result; //fail-safe
	        }
	        
	        
	        // TODO-3462 20.10.2020
	        $drugs = array();
	        foreach($patient_request_pending_array as $k=>$d){
	            $drugs[] = $d['drugplan_id'];
	        }
	        
	        $drugplan_info = Doctrine_Query::create()
	        ->select('*')
	        ->from('PatientDrugPlan')
	        ->whereIn("ipid", $ipids  )
	        ->andWhereIn("id", $drugs  )
	        ->fetcharray();
	        
	        $remove_request = array();
	        foreach($drugplan_info as $kd=>$pd){
	            if($pd['isdelete'] == 1 ){
	                $remove_request[$pd['ipid']][] = $pd['id'];
	            }
	        }

	        foreach($patient_request_pending_array as $kr=>$req_f){
	            if(in_array($req_f['drugplan_id'],$remove_request[$req_f['ipid']])){
	                unset($patient_request_pending_array[$kr]);
	            }
	        }
	        // -- 
	        
	        
	        foreach($patient_request_pending_array as $k=>$req){
	            $result['ipids'][] = $req['ipid'];
	        }
	         
	        return $result;
	        
	}
	
	
	/**
	 * @author Ancuta
	 * ISPC-2432
	 * 12.02.2020
	 * @param unknown $ipids
	 * @return unknown|array[]
	 */
	
	public function get_mePatient_active_devices($ipids){
	    
	    $Tr = new Zend_View_Helper_Translate();
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    $usertype = $logininfo->usertype;
	    
	    if(!is_array($ipids))
	    {
	        $ipids = array($ipids);
	    }
	    else
	    {
	        $ipids = $ipids;
	    }
	    
	    // get active devices of patient
	    $patient_active_devices_array = Doctrine_Query::create()
	    ->select('*')
	    ->from('MePatientDevices')
	    ->whereIn("ipid", $ipids  )
        ->andWhere("active = ? ", "yes" )
	    ->fetcharray();
	    
	    $result = array();
	    if(empty($patient_active_devices_array)){
	        return $result; //fail-safe
	    }
 
        foreach($patient_active_devices_array as $key_device => $v_device)
        {
            $result['get_mePatient_active_devices'][$v_device['ipid']] = $v_device;
            $result['ipids'][] = $v_device['ipid'];
        }
	  
        return $result;
	     
	}
	
	public function get_patient_medication($ipids, $search_icon = false, $list = false)
	{
		//ISPC-2547 Carmen 28.02.2020 new medi icon design and functionality
		// #ISPC-2512PatientCharts
		$logininfo = new Zend_Session_Namespace('Login_Info');
	
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
	
		if(!is_array($ipids))
		{
			$ipids = array($ipids);
		}
		else
		{
			$ipids = $ipids;
		}
		$else_planned_medis = 0;
		$exclude_drugplans_from_icon  =array();
		if($this->_client_hasModule(250)){
		    $else_planned_medis = 1;
		    $today = strtotime(date('Y-m-d'));
		    $patient_drugplan_ids2planned_actions = PatientDrugplanPlanning::get_planned_drugs2ipids($ipids);
		    
		    foreach($patient_drugplan_ids2planned_actions as $p_ipid=>$planned_data){
		        foreach($planned_data as $drugplan_id=>$plan){
		            $action_Date_time = strtotime(date('Y-m-d',strtotime($plan['action_date'])));
		            
		            if($plan['action'] == 'add' && $action_Date_time > $today){
		                $exclude_drugplans_from_icon[] = $drugplan_id;
		            } else{
		                if($plan['action'] == 'remove' && $action_Date_time <= $today){
		                    $exclude_drugplans_from_icon[] = $drugplan_id;
		                }
		            }
		        }
		    }
		}
		
		
		if ($this->_client_hasModule(111)
				|| $this->_client_hasModule(155))
		{
			$acknowledge_func = '1';
	
			// Get declined data
			$declined_ids2ipids = PatientDrugPlanAlt::get_declined_patients_drugplan_alt($ipids,false,false,true); // TODO-2320 - added params to retrive ALL denied  20.05.2019 @Ancuta
			 
			$declined = array();
			foreach($ipids as $kd=>$ipidd)
			{
				foreach($declined_ids2ipids[$ipidd] as $kdd=>$declined_ids)
				{
					$declined[] = $declined_ids;
				}
				 
			}
			
			//get non approved data
			$non_approved = PatientDrugPlanAlt::get_patients_drugplan_alt($ipids,false,false,true);
	
			$not_approved_ids = array();
			$newly_not_approved = array();
			foreach($ipids as $k=>$ipid)
			{
				foreach($non_approved[$ipid]['change'] as $drugplan_id =>$not_approved)
				{
					$not_approved_ids[] = $not_approved['drugplan_id'];
					 
					if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
						$newly_not_approved[] = $not_approved['drugplan_id'];;
					}
				}
			}
			
		}
		else
		{
			$acknowledge_func = '0';
		}
			
		if($search_icon)
		{
			$drugs = Doctrine_Query::create();
			$drugs->select('id,ipid');
			$drugs->from('PatientDrugPlan');
			$drugs->whereIn('ipid', $ipids);
			$drugs->andWhere("isdelete = '0'");
			$drugs->andWhere("treatment_care = '0'");
			if($acknowledge_func =="1"){
				$drugs->andWhereNotIn('id',$declined); // remove declined
				$drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
			}
			
			//ISPC-2797 Ancuta 18.02.2021
			if($else_planned_medis =="1" && !empty($exclude_drugplans_from_icon)){
			    $drugs->andWhereNotIn('id',$exclude_drugplans_from_icon); // remove declined
			}
			//--
			$drugs->groupBy('ipid');
			$drugs->orderBy("id ASC");
			$drugsarray = $drugs->fetchArray();
	
			foreach($drugsarray as $key => $drugp)
			{
				$patient_medication_data['ipids'][] = $drugp['ipid'];
			}
			
			return $patient_medication_data;
		}
		else
		{
			$drugs = Doctrine_Query::create()
			->select('*')
			->from('PatientDrugPlan')
			->whereIn('ipid', $ipids)
			->andWhere("isdelete = '0'")
			->andWhere("treatment_care = '0'");
			if($acknowledge_func =="1"){
				$drugs->andWhereNotIn('id',$declined); // remove declined
				$drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
			}
			
			//ISPC-2797 Ancuta 18.02.2021
			if($else_planned_medis =="1" && !empty($exclude_drugplans_from_icon)){
			    $drugs->andWhereNotIn('id',$exclude_drugplans_from_icon); // remove declined
			}
			//--
			
			$drugs->orderBy("id ASC");
			$drugsarray = $drugs->fetchArray();
			
			if(empty($drugsarray))
			{
				return;
			}
	
			$master_meds = array();
			$cocktail_ids = array();
			$pp_pumpe_ids = array();
			$drug_ids = array();
			foreach($drugsarray as $key => $drugp)
			{
				$master_meds[] = $drugp['medication_master_id'];
				$cocktail_ids[] = $drugp['cocktailid'];
				$pp_pumpe_ids[] = $drugp['pumpe_id'];
				$drug_ids[] = $drugp['id'];
			}
	
			$drugs_dosage_array = array();
			if(!empty($drug_ids)){
				$drugs_extra = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanExtra')
				->whereIn('ipid', $ipids)
				->andWhere("isdelete = '0'")
				->andWhereIn("drugplan_id",$drug_ids);
				$drugsarray_extra = $drugs_extra->fetchArray();
	
				if(!empty($drugsarray_extra)){
	
					// get details for indication
					$indications_array = MedicationIndications::client_medication_indications($clientid);
	
					foreach ($indications_array as $ki => $ind_value) {
						$indication[$ind_value['id']]['name'] = $ind_value['indication'];
						$indication[$ind_value['id']]['color'] = $ind_value['indication_color'];
					}
	
					foreach($drugsarray_extra as $k=>$extra_data){
						$drug_indication[$extra_data['drugplan_id']] = $indication[$extra_data['indication']];
					}
	
				}
				
				//ISPC-2583 Carmen 27.04.2020
				$color_map = array(
						'given' => 'green',
						'not_given' => 'red',
						'given_different_dosage' => 'blue',
						'not_taken_by_patient' => 'yellow',						
				);
				//--
				
				$dosage_given = PatientDrugPlanDosageGivenTable::getInstance()->findAllIpidsCurrentDayGiven($ipids);
				$dosage_given_ipid = array();
				foreach($dosage_given as $kg => $vg)
				{
					//$dosage_given_ipid[$vg['ipid']][$vg[drugplan_id]][substr($vg['dosage_time_interval'], 0, 5)] = substr($vg['dosage_time_interval'], 0, 5);
					//ISPC-2583 Carmen 27.04.2020
					$given = array(
						'drugplan_id' => $vg['drugplan_id'],
						'dosage' => $vg['dosage'], 
						'dosage_status' => $vg['dosage_status'],
						'dosage_time_interval' => $vg['dosage_time_interval'],
						'dosage_date' => $vg['dosage_date'],
						'documented_info' => $vg['documented_info'],						
						'not_given_reason' => $vg['not_given_reason'],
						'dosage_status_color' => $color_map[$vg['dosage_status']],
					);
					
					$dosage_given_ipid[$vg['ipid']][$vg[drugplan_id]][substr($vg['dosage_time_interval'], 0, 5)] = $given;
					//--					
				}
				
				$drugs_dosages = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanDosage')
				->whereIn('ipid', $ipids)
				->andWhere("isdelete = '0'")
				->andWhereIn("drugplan_id",$drug_ids)
				->orderBy("dosage_time_interval ASC")
				->fetchArray();
	
				if(!empty($drugs_dosages)){
					foreach($drugs_dosages as $k_drug => $v_drug)
					{
						//ISPC-2329 Carmen 13.01.2020
						//$time = date("H:i",strtotime($v_drug['dosage_time_interval']));
						$time = substr($v_drug['dosage_time_interval'], 0, 5);
						$drugs_dosage_array[$v_drug['ipid']][$v_drug['drugplan_id']][$time]['value'] = ($v_drug['dosage'] == '! ALTE DOSIERUNG!' ? 'ALTE' : $v_drug['dosage']);
						if($dosage_given_ipid[$vg['ipid']][$v_drug['drugplan_id']][$time])
						{
							//$drugs_dosage_array[$v_drug['ipid']][$v_drug['drugplan_id']][$time]['given'] = '1';
							$drugs_dosage_array[$v_drug['ipid']][$v_drug['drugplan_id']][$time]['given'] = $dosage_given_ipid[$vg['ipid']][$v_drug['drugplan_id']][$time];
						}
						else
						{
							$drugs_dosage_array[$v_drug['ipid']][$v_drug['drugplan_id']][$time]['given'] = '0';
						}
							
					}
						
				}
				//     				dd($drugs_dosage_array);
	
			}
	
			$medic = Doctrine_Query::create()
			->select('*')
			->from('Medication')
			->whereIn("id", $master_meds);
			$master_medication = $medic->fetchArray();
	
			foreach($master_medication as $k_medi => $v_medi)
			{
				$medications[$v_medi['id']] = $v_medi['name'];
			}
	
			// get cocktaildetails	
			// get schmerzpumpe details
			$cocktail_ids = array_unique($cocktail_ids);
	
			$cocktailsC = new PatientDrugPlanCocktails();
			$cocktails = $cocktailsC->getDrugCocktails($cocktail_ids);
	
			//ISPC-2833 Ancuta 04.03.2021
			// get ispumpe details
			$pp_pumpe_ids = array_unique($pp_pumpe_ids);
	
			$ispumpe_obj = new PatientDrugplanPumpe();
			$pumpe_data = $ispumpe_obj->get_perfusor_pumpes($pp_pumpe_ids);
	 
			// get nutrition medication
			$master_medarr_nutrittion = Nutrition::getMedicationNutritionById($master_meds);
	
			if(!empty($master_medarr_nutrittion))
			{
	
				foreach($master_medarr_nutrittion as $kn_medi => $vn_medi)
				{
					$nutrition_medications[$vn_medi['id']] = $vn_medi['name'];
				}
			}
	
			$medication_extra  = PatientDrugPlanExtra::get_patients_drugplan_extra($ipids,$clientid);
			
			//ISPC-2636 Lore 29.07.2020
			foreach($drugsarray as $key => $vals){
			    $drugsarray[$key]['medication'] = $medications[$vals['medication_master_id']];
			    
		        foreach($medication_extra[$vals['id']] as $k => $vv){
		            if($k == 'indication'){
		                $drugsarray[$key]['indication'] = $vv['name'];
		                $drugsarray[$key]['indication_color'] = $vv['color'];
		            }else{
		                $drugsarray[$key][$k] = $vv;
		            }
		        }
			    
			}
			
			$cust = Doctrine_Query::create()
			->select("client_medi_sort, user_overwrite_medi_sort_option")
			->from('Client')
			->where('id = ?',  $clientid);
			$cust->getSqlQuery();
			$disarray = $cust->fetchArray();
			
			$client_medi_sort = $disarray[0]['client_medi_sort'];
			$user_overwrite_medi_sort_option = $disarray[0]['user_overwrite_medi_sort_option'];
			
			$uss = Doctrine_Query::create()
			->select('*')
			->from('UserTableSorting')
			->Where('client = ?', $clientid)
			->orderBy('change_date DESC')
			->limit(1);
			$uss_arr = $uss->fetchArray();
			$last_sort_order = unserialize($uss_arr[0]['value']);
			//dd($last_sort_order[0][1]);
			
			$saved_order = !empty($client_medi_sort) ? $client_medi_sort : "medication"; 

			if($user_overwrite_medi_sort_option != '0'){
			    $uomso = Doctrine_Query::create()
			    ->select('*')
			    ->from('UserSettingsMediSort')
			    ->Where('clientid = ?', $clientid)
			    ->orderBy('create_date DESC')
			    ->limit(1);
			    $uomso_arr = $uomso->fetchArray();
			    
			    if(!empty($uomso_arr)){
			        $saved_order = !empty($uomso_arr[0]['sort_column']) ? $uomso_arr[0]['sort_column'] : "medication";//Ancuta 17.09.2020-- Issue if empty
			    }
			}
			
			// ############ APPLY SORTING ##############
			$sort_oorder = 'SORT_ASC';
			if(!empty($last_sort_order[0][1]) && $last_sort_order[0][1] == 'desc'){
			    $sort_oorder = 'SORT_DESC';
			}
			$keys = array_column($drugsarray, $saved_order);
			array_multisort($keys, $sort_oorder, $drugsarray);
			//dd($saved_order,$drugsarray);
			//.
			
			if ($this->_client_hasModule(141))
			{
				$medopt = new MedicationOptions();
				$medopt_det = $medopt->client_saved_medication_options($clientid);
			}
			else 
			{
				$medopt_det = array('actual' => array('time_schedule' => '1'), 'isivmed' => array('time_schedule' => '1'));
			}
			
			foreach($ipids as $ipid)
			{
				
			
			$patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid,array_keys($medopt_det));
			
			if($patient_time_scheme['patient']){
				foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
				{
					if($med_type != "new"){
						$set = 0;
						foreach($dos_data  as $int_id=>$int_data)
						{
							if(in_array($med_type,$patient_time_scheme['patient']['new'])){
									
								$interval_array['interval'][$ipid][$med_type][$int_id]['time'] = $int_data;
								$interval_array['interval'][$ipid][$med_type][$int_id]['custom'] = '1';
									
								/* $dosage_settings[$med_type][$set] = $int_data;
								$set++;
									
								$dosage_intervals[$med_type][$int_data] = $int_data; */
							}
							else
							{
									
									
								$interval_array['interval'][$ipid][$med_type][$int_id]['time'] = $int_data;
								$interval_array['interval'][$ipid][$med_type][$int_id]['custom'] = '0';
								$interval_array['interval'][$ipid][$med_type][$int_id]['interval_id'] = $int_id;
									
								/* $dosage_settings[$med_type][$set] = $int_data;
								$set++;
									
								$dosage_intervals[$med_type][$int_data] = $int_data; */
							}
						}
					}
				}
			}
			else
			{
				foreach($patient_time_scheme['client']  as $med_type=>$mtimes)
				{
						
					$inf=1;
					$setc= 0;
					foreach($mtimes as $int_id=>$int_data){
							
						$interval_array['interval'][$ipid][$med_type][$inf]['time'] = $int_data;
						$interval_array['interval'][$ipid][$med_type][$inf]['custom'] = '1';
						/* $dosage_settings[$med_type][$setc] = $int_data;
						$setc++; */
						$inf++;
							
						/* $dosage_intervals[$med_type][$int_data] = $int_data; */
					}
				}
			}
		
			}
		
			//ISPC-2786 Ancuta 11.01.2021
			$usr = new User();
			$user_details = $usr->getUserByClientid($clientid, '1', true);
			$user_details["-1"] = 'Anderer Arzt';
			$user_details["-2"] = 'Hausarzt';
			$user_details["-3"] = 'Facharzt';
			$user_details["-4"] = 'Krankenhaus'; //ISPC - 2284
			$user_details["-5"] = 'Selbstmedikation'; //ISPC-2329
			//--
			
			foreach($drugsarray as $key => $drugp)
			{
				if($drugp['isbedarfs'] == '1')
				{
					$type = "N";
					
					if($medopt_det['isbedarfs']['time_schedule'] == '1')
					{
						if(!$patient_medication[$drugp['ipid']][$type]['interval'])
						{
							$patient_medication[$drugp['ipid']][$type]['interval'] = $interval_array['interval'][$drugp['ipid']]['isbedarfs'];
							$interval_arr_isbed = array_values($interval_array['interval'][$drugp['ipid']]['isbedarfs']);
						}
						
						if( empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
							if(strpos($drugp['dosage'],"-")){
								$drugs_dosage_array[$drugp['ipid']][$drugp['id']] = explode('-', $drugp['dosage']);
								if(count($drugs_dosage_array[$drugp['ipid']][$drugp['id']]) <= count($interval_arr_isbed)){
									foreach($interval_arr_isbed as $kt => $vt)
									{
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = ($drugs_dosage_array[$drugp['ipid']][$drugp['id']][$kt] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugs_dosage_array[$drugp['ipid']][$drugp['id']][$kt]);
										if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
										{
											//$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']];
										}
										else
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
										}
									}
								}
								else 
								{
									foreach($interval_arr_isbed as $kt => $vt)
									{
										if($kt == 0)
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = 'ALTE';
										}
										elseif($kt == 1)
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = $drugp['dosage'];
										}
										else 
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = '';
										}
										if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
										{
											//$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']];
										}
										else
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
										}
										
									}
								}
							}
							else 
							{
								foreach($interval_arr_isbed as $kt => $vt)
								{
									if($kt == 0)
									{
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = 'ALTE';
									}
									elseif($kt == 1)
									{
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = $drugp['dosage'];
									}
									else
									{
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = '';
									}
									if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
									{
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']];
									}
									else
									{
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
									}
								
								}
							}
						}
						else
						{
							foreach($interval_arr_isbed as $kt => $vt)
							{
								if($drugs_dosage_array[$drugp['ipid']][$drugp['id']][$vt['time']])
								{
									$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']] = $drugs_dosage_array[$drugp['ipid']][$drugp['id']][$vt['time']];
								}
								else
								{
									$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
								}
							}
						}
					}
					else 
					{
						if( ! empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
							$drugs_dosage_arr = array();
							foreach($drugs_dosage_array[$drugp['ipid']][$drugp['id']] as $kd => $vd)
							{
								$drugs_dosage_arr[] = $vd['value'];
							}
						
							$drugp['dosage'] = implode("-", $drugs_dosage_arr);
						
						}
						$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage']['dosage'] = ($drugp['dosage'] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugp['dosage']);
						/* if($dosage_given_ipid[$drugp['ipid']][$drugp['id']]['00:00'])
						{
							$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage']['given'] = '1';
						} */
						$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage']['given'] = '0';
					}
					$patient_medication[$drugp['ipid']][$type][$drugp['id']]['medications'] = $medications[$drugp['medication_master_id']];					
					$patient_medication[$drugp['ipid']][$type][$drugp['id']]['comments'] = $drugp['comments'];
					$patient_medication[$drugp['ipid']][$type][$drugp['id']]['indications'] = $drug_indication[$drugp['id']];
					//ISPC-2786 Cristi.C 11.01.2021
					if($drugp['verordnetvon'] != 0 )
					{
					    $patient_medication[$drugp['ipid']][$type][$drugp['id']]['verordnetvon'] = $user_details[$drugp['verordnetvon']];
					}
					else
					{
					    $patient_medication[$drugp['ipid']][$type][$drugp['id']]['verordnetvon'] = "";
					}
					//--
					
					//ISPC-2110 p.4
					if ( ! empty($drugp['dosage_interval'])) {
						$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage_interval'] = $drugp['dosage_interval'];
					}
					
								
					//ISPC-2786 Ancuta 11.01.2021
					if($drugp['verordnetvon'] != 0 )
					{
					    $patient_medication[$drugp['ipid']][$type][$drugp['id']]['verordnetvon'] = $user_details[$drugp['verordnetvon']];
					}
					else
					{
					    $patient_medication[$drugp['ipid']][$type][$drugp['id']]['verordnetvon'] = "";
					}
					//-- 
					
				}
				elseif($drugp['iscrisis'] == '1')
				{
					$type = "KM";
					$patient_medication[$drugp['ipid']][$type][$drugp['id']]['medications'] = $medications[$drugp['medication_master_id']];
					$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage']['dosage'] = ($drugp['dosage'] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugp['dosage']);
					$patient_medication[$drugp['ipid']][$type][$drugp['id']]['comments'] = $drugp['comments'];
					$patient_medication[$drugp['ipid']][$type][$drugp['id']]['indications'] = $drug_indication[$drugp['id']];
				    if ( ! empty($drugp['dosage_interval'])) {
						$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage_interval'] = $drugp['dosage_interval'];
					}
					/* if($dosage_given_ipid[$drugp['ipid']][$drugp['id']]['00:00'])
					{
						$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage']['given'] = '1';
					} */
					$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage']['given'] = '0';
					
					
					//ISPC-2786 Ancuta 11.01.2021
					if($drugp['verordnetvon'] != 0 )
					{
					    $patient_medication[$drugp['ipid']][$type][$drugp['id']]['verordnetvon'] = $user_details[$drugp['verordnetvon']];
					}
					else
					{
					    $patient_medication[$drugp['ipid']][$type][$drugp['id']]['verordnetvon'] = "";
					}
					//-- 
					
				}
				elseif($drugp['isivmed'] == '1')
				{
					$type = "I";
					
					if($medopt_det['isivmed']['time_schedule'] == '1')
					{
						if(!$patient_medication[$drugp['ipid']][$type]['interval'])
						{
							$patient_medication[$drugp['ipid']][$type]['interval'] = $interval_array['interval'][$drugp['ipid']]['isivmed'];
							$interval_arr_isiv = array_values($interval_array['interval'][$drugp['ipid']]['isivmed']);
						}
						
						if( empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
						if(strpos($drugp['dosage'],"-")){
								$drugs_dosage_array[$drugp['ipid']][$drugp['id']] = explode('-', $drugp['dosage']);
								if(count($drugs_dosage_array[$drugp['ipid']][$drugp['id']]) <= count($interval_arr_isiv)){
									foreach($interval_arr_isiv as $kt => $vt)
									{
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = ($drugs_dosage_array[$drugp['ipid']][$drugp['id']][$kt] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugs_dosage_array[$drugp['ipid']][$drugp['id']][$kt]);
										if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
										{
											//$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
										}
										else
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
										}
									}
								}
								else 
								{
									foreach($interval_arr_isiv as $kt => $vt)
									{
										if($kt == 0)
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = 'ALTE';
										}
										elseif($kt == 1)
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = $drugp['dosage'];
										}
										else 
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = '';
										}
										if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
										{
											//$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
										}
										else
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
										}
										
									}
								}
							}
							else 
							{
								foreach($interval_arr_isiv as $kt => $vt)
								{
									if($kt == 0)
									{
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = 'ALTE';
									}
									elseif($kt == 1)
									{
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = $drugp['dosage'];
									}
									else
									{
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = '';
									}
									if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
									{
										//$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
									}
									else
									{
										$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
									}
								
								}
							}
						}
						else
						{
							foreach($interval_arr_isiv as $kt => $vt)
							{
								if($drugs_dosage_array[$drugp['ipid']][$drugp['id']][$vt['time']])
								{
									$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']] = $drugs_dosage_array[$drugp['ipid']][$drugp['id']][$vt['time']];
								}
								else 
								{
									$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
								}
							}
						}
					}
					else
					{
						if( ! empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
						
						if( ! empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
							$drugs_dosage_arr = array();
							foreach($drugs_dosage_array[$drugp['ipid']][$drugp['id']] as $kd => $vd)
							{
								$drugs_dosage_arr[] = $vd['value'];
							}
						
							$drugp['dosage'] = implode("-", $drugs_dosage_arr);
									
							}
						
						}
						$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage']['dosage'] = ($drugp['dosage'] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugp['dosage']);
					}
					$patient_medication[$drugp['ipid']][$type][$drugp['id']]['medications'] = $medications[$drugp['medication_master_id']];
					$patient_medication[$drugp['ipid']][$type][$drugp['id']]['comments'] = $drugp['comments'] ;
					$patient_medication[$drugp['ipid']][$type][$drugp['id']]['indications'] = $drug_indication[$drugp['id']];
 
					
					//ISPC-2786 Ancuta 11.01.2021
					if($drugp['verordnetvon'] != 0 )
					{
					    $patient_medication[$drugp['ipid']][$type][$drugp['id']]['verordnetvon'] = $user_details[$drugp['verordnetvon']];
					}
					else
					{
					    $patient_medication[$drugp['ipid']][$type][$drugp['id']]['verordnetvon'] = "";
					}
					//-- 
				}
				elseif($drugp['isschmerzpumpe'] == '1')
				{
					$type = "Q";
					//TODO-3829 Ancuta 23.02.2021
					if($this->_client_hasModule(240)){
    					// TODO-2504 + TODO-2526  Corrected By Ancuta 03.09.2019
    					$dosage_value = "";
    					$dosage_value = str_replace(",",".",$drugp['unit_dosage']);
    					
    					$dosage24h = 24 * $dosage_value;
    					
    					$unit ="";
    					$unit = !empty($medication_extra[$drugp['id']]['unit']) ? $medication_extra[$drugp['id']]['unit'] : "i.E.";
    					//$drugp['dosage'] = round($dosage_value,3).''.$unit.'/h'; //TODO-3670 Ancuta 08.12.2020 - remove round
    					//TODO-3923 Lore 05.03.2021
    					$drugp['dosage'] = $dosage_value.''.$unit.'/h'; //TODO-3670 Ancuta 08.12.2020 - remove round
    					//TODO-3670 Ancuta 01.02.2021 -use manual - if manuel empty, then  calculate  
    					if($medication_extra[$drugp['id']]['unit_dosage_24h']){
        					$drugp['dosage'] .= ' ['.$medication_extra[$drugp['id']]['unit_dosage_24h'].''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3
    					} else{
        					//$drugp['dosage'] .= ' ['.round($dosage24h, 3).''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3
    					    //TODO-3923 Lore 05.03.2021
    					    $drugp['dosage'] .= ' ['.$dosage24h.''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3
        					
    					}
    					//--
					} else{
					    
					
    					// TODO-2504 + TODO-2526  Corrected By Ancuta 03.09.2019
    					$dosage_value = "";
    					$dosage_value = str_replace(",",".",$drugp['dosage']);
    					
    					$dosage24h = 24 * $dosage_value;
    					
    					$unit ="";
    					$unit = !empty($medication_extra[$drugp['id']]['unit']) ? $medication_extra[$drugp['id']]['unit'] : "i.E.";//TODO-3829 Ancuta 23.02.2021
    					//$drugp['dosage'] = ' | '.round($drugp['dosage'], 2).''.$unit.'/h';
    					// Ancuta - Pumpe-dosage 10.12.2020 - added round 3 !!!
    					//$drugp['dosage'] = round($dosage_value,3).''.$unit.'/h'; //TODO-3670 Ancuta 08.12.2020 - remove round
    					//TODO-3923 Lore 05.03.2021
    					$drugp['dosage'] = $dosage_value.''.$unit.'/h'; //TODO-3670 Ancuta 08.12.2020 - remove round
    					
    					//TODO-3670 Ancuta 01.02.2021 -use manual - if manuel empty, then  calculate  
    					if($medication_extra[$drugp['id']]['dosage_24h_manual']){
        					$drugp['dosage'] .= ' ['.$medication_extra[$drugp['id']]['dosage_24h_manual'].''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3
    					} else{
        					//$drugp['dosage'] .= ' ['.round($dosage24h, 3).''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3
    					    //TODO-3923 Lore 05.03.2021
        					$drugp['dosage'] .= ' ['.$dosage24h.''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3
        					
    					}
    					//--
					}
					
					
					
					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['medications'][] = $medications[$drugp['medication_master_id']];
					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['dosage'][] = ($drugp['dosage'] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugp['dosage']);
					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['indications'][] = $drug_indication[$drugp['id']];
					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['cocktail_details']['comment'] = $cocktails[$drugp['cocktailid']]['description'];
					//TODO-2504 -Lore 19.08.2019
					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['cocktail_details']['flussrate'] = $cocktails[$drugp['cocktailid']]['flussrate'];
					//added for charts Carmen 15.05.2020
					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['cocktail_details']['bolus'] = $cocktails[$drugp['cocktailid']]['bolus'];
					
					
					
					//ISPC-2786 Ancuta 11.01.2021
					if($drugp['verordnetvon'] != 0 )
					{
					    $patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['verordnetvon'][] = $user_details[$drugp['verordnetvon']];
					}
					else
					{
					    $patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['verordnetvon'][] = "";
					}
					//-- 
					
				}
				//ISPC-2833
				elseif($drugp['ispumpe'] == '1')
				{ 
					$type = "QP";
                    //TODO-3829 Ancuta 23.02.2021
                    //ISPC-2914,Elena,10.05.2021
                    // Pumpe/Perfusor seems to have no structure differences with Schmerzpumpe
                    if($this->_client_hasModule(240)){
                        // TODO-2504 + TODO-2526  Corrected By Ancuta 03.09.2019
                        $dosage_value = "";
                        $dosage_value = str_replace(",",".",$drugp['unit_dosage']);

                        $dosage24h = 24 * $dosage_value;

                        $unit ="";
                        $unit = !empty($medication_extra[$drugp['id']]['unit']) ? $medication_extra[$drugp['id']]['unit'] : "i.E.";
                        //$drugp['dosage'] = round($dosage_value,3).''.$unit.'/h'; //TODO-3670 Ancuta 08.12.2020 - remove round
                        //TODO-3923 Lore 05.03.2021
                        $drugp['dosage'] = $dosage_value.''.$unit.'/h'; //TODO-3670 Ancuta 08.12.2020 - remove round
                        //TODO-3670 Ancuta 01.02.2021 -use manual - if manuel empty, then  calculate
                        if($medication_extra[$drugp['id']]['unit_dosage_24h']){
                            $drugp['dosage'] .= ' ['.$medication_extra[$drugp['id']]['unit_dosage_24h'].''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3
                        } else{
                            //$drugp['dosage'] .= ' ['.round($dosage24h, 3).''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3
                            //TODO-3923 Lore 05.03.2021
                            $drugp['dosage'] .= ' ['.$dosage24h.''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3

                        }
                        //--
                    } else{


                        // TODO-2504 + TODO-2526  Corrected By Ancuta 03.09.2019
                        $dosage_value = "";
                        $dosage_value = str_replace(",",".",$drugp['dosage']);

                        $dosage24h = 24 * $dosage_value;

                        $unit ="";
                        $unit = !empty($medication_extra[$drugp['id']]['unit']) ? $medication_extra[$drugp['id']]['unit'] : "i.E.";//TODO-3829 Ancuta 23.02.2021
                        //$drugp['dosage'] = ' | '.round($drugp['dosage'], 2).''.$unit.'/h';
                        // Ancuta - Pumpe-dosage 10.12.2020 - added round 3 !!!
                        //$drugp['dosage'] = round($dosage_value,3).''.$unit.'/h'; //TODO-3670 Ancuta 08.12.2020 - remove round
                        //TODO-3923 Lore 05.03.2021
                        $drugp['dosage'] = $dosage_value.''.$unit.'/h'; //TODO-3670 Ancuta 08.12.2020 - remove round

                        //TODO-3670 Ancuta 01.02.2021 -use manual - if manuel empty, then  calculate
                        if($medication_extra[$drugp['id']]['dosage_24h_manual']){
                            $drugp['dosage'] .= ' ['.$medication_extra[$drugp['id']]['dosage_24h_manual'].''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3
                        } else{
                            //$drugp['dosage'] .= ' ['.round($dosage24h, 3).''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3
                            //TODO-3923 Lore 05.03.2021
                            $drugp['dosage'] .= ' ['.$dosage24h.''.$unit.'/24h]';//TODO-3670 Ancuta 08.12.2020 - changed  from 2 decimals to 3

                        }
                        //--
                    }



                    $patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['medications'][] = $medications[$drugp['medication_master_id']];
                    $patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['dosage'][] = ($drugp['dosage'] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugp['dosage']);
                    $patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['indications'][] = $drug_indication[$drugp['id']];
                    $patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['cocktail_details']['comment'] = $cocktails[$drugp['cocktailid']]['description'];
                    //TODO-2504 -Lore 19.08.2019
                    $patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['cocktail_details']['flussrate'] = $cocktails[$drugp['cocktailid']]['flussrate'];
                    //added for charts Carmen 15.05.2020
                    $patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['cocktail_details']['bolus'] = $cocktails[$drugp['cocktailid']]['bolus'];



                    //ISPC-2786 Ancuta 11.01.2021
                    if($drugp['verordnetvon'] != 0 )
                    {
                        $patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['verordnetvon'][] = $user_details[$drugp['verordnetvon']];
                    }
                    else
                    {
                        $patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['verordnetvon'][] = "";
                    }
// 					$dosage_value = "";
// 					$dosage_value = str_replace(",",".",$drugp['dosage']);
// 					$unit ="";
// 					$unit = !empty($medication_extra[$drugp['id']]['unit']) ? $medication_extra[$drugp['id']]['unit'] : "i.E.";//TODO-3829 Ancuta 23.02.2021
// 					$drugp['dosage'] = $dosage_value.''.$unit.'/h'; //TODO-3670 Ancuta 08.12.2020 - remove round
					
// 					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['medications'][] = $medications[$drugp['medication_master_id']];
// 					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['dosage'][] = ($drugp['dosage'] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugp['dosage']);
// 					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['indications'][] = $drug_indication[$drugp['id']];
// 					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['pumpe_details']['comment'] = $pumpe_data[$drugp['pumpe_id']]['description'];
// 					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['pumpe_details']['flussrate'] = $pumpe_data[$drugp['pumpe_id']]['flussrate'];
// 					$patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['pumpe_details']['bolus'] = $pumpe_data[$drugp['pumpe_id']]['bolus'];
// 				    $patient_medication[$drugp['ipid']][$type][$drugp['cocktailid']]['verordnetvon'][] = "";
			 
				    
				}
				//
				elseif($drugp['isintubated'] == '1') //ISPC-2176 p6
				{
					$type = "I";
					$sh_isintubated = PatientDrugPlan::ISINTUBATED_VERLAUF_SHORTCUT;
					
					if($medopt_det['isintubated']['time_schedule'] == '1')
					{
						if(!$patient_medication[$drugp['ipid']][$sh_isintubated]['interval'])
						{
							$patient_medication[$drugp['ipid']][$sh_isintubated]['interval'] = $interval_array['interval'][$drugp['ipid']]['isintubated'];
							$interval_arr_intub = array_values($interval_array['interval'][$drugp['ipid']]['isintubated']);
						}
						
						if( empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
						if(strpos($drugp['dosage'],"-")){
								$drugs_dosage_array[$drugp['ipid']][$drugp['id']] = explode('-', $drugp['dosage']);
								if(count($drugs_dosage_array[$drugp['ipid']][$drugp['id']]) <= count($interval_arr_intub)){
									foreach($interval_arr_intub as $kt => $vt)
									{
										$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['value'] = ($drugs_dosage_array[$drugp['ipid']][$drugp['id']][$kt] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugs_dosage_array[$drugp['ipid']][$drugp['id']][$kt]);
										if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
										{
											//$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
											$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
										}
										else
										{
											$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
										}
									}
								}
								else 
								{
									foreach($interval_arr_intub as $kt => $vt)
									{
										if($kt == 0)
										{
											$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['value'] = 'ALTE';
										}
										elseif($kt == 1)
										{
											$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['value'] = $drugp['dosage'];
										}
										else 
										{
											$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['value'] = '';
										}
										if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
										{
											//$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
											$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
										}
										else
										{
											$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
										}
										
									}
								}
							}
							else 
							{
								foreach($interval_arr_intub as $kt => $vt)
								{
									if($kt == 0)
									{
										$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['value'] = 'ALTE';
									}
									elseif($kt == 1)
									{
										$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['value'] = $drugp['dosage'];
									}
									else
									{
										$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['value'] = '';
									}
									if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
									{
										//$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
										$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
									}
									else
									{
										$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
									}
								
								}
							}
						}
						else
						{
							foreach($interval_arr_intub as $kt => $vt)
							{	
								if($drugs_dosage_array[$drugp['ipid']][$drugp['id']][$vt['time']])
								{
									$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']] = $drugs_dosage_array[$drugp['ipid']][$drugp['id']][$vt['time']];
								}
								else 
								{
									$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
								}
							}
						}
					}
					else
					{
						if( ! empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
					
						if( ! empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
							$drugs_dosage_arr = array();
							foreach($drugs_dosage_array[$drugp['ipid']][$drugp['id']] as $kd => $vd)
							{
								$drugs_dosage_arr[] = $vd['value'];
							}
						
							$drugp['dosage'] = implode("-", $drugs_dosage_arr);
									
							}
					
						}
						$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['dosage']['dosage'] = ($drugp['dosage'] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugp['dosage']);
					}
					
					$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['medications'] = $medications[$drugp['medication_master_id']];
					$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['comments'] = $drugp['comments'] ;
					$patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['indications'] = $drug_indication[$drugp['id']];
					
					//ISPC-2786 Ancuta 11.01.2021
					if($drugp['verordnetvon'] != 0 )
					{
					    $patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['verordnetvon'] = $user_details[$drugp['verordnetvon']];
					}
					else
					{
					    $patient_medication[$drugp['ipid']][$sh_isintubated][$drugp['id']]['verordnetvon'] = "";
					}
					
				}
				else
				{
					$type = "M";
					if($drugp['isnutrition'] == '1'){
						$sh_isnutrition = 'ER';
						
						if($medopt_det['isnutrition']['time_schedule'] == '1')
						{
							if(!$patient_medication[$drugp['ipid']][$sh_isnutrition]['interval'])
							{
								$patient_medication[$drugp['ipid']][$sh_isnutrition]['interval'] = $interval_array['interval'][$drugp['ipid']]['isnutrition'];
								$interval_arr_nutr = array_values($interval_array['interval'][$drugp['ipid']]['isnutrition']);
							}							
							
							if( empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
								if(strpos($drugp['dosage'],"-")){
									$drugs_dosage_array[$drugp['ipid']][$drugp['id']] = explode('-', $drugp['dosage']);
									if(count($drugs_dosage_array[$drugp['ipid']][$drugp['id']]) <= count($interval_arr_nutr)){
										foreach($interval_arr_nutr as $kt => $vt)
										{
											$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['value'] = ($drugs_dosage_array[$drugp['ipid']][$drugp['id']][$kt] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugs_dosage_array[$drugp['ipid']][$drugp['id']][$kt]);
											if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
											{
												//$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
												$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
											}
											else
											{
												$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
											}
										}
									}
									else 
									{
										foreach($interval_arr_nutr as $kt => $vt)
										{
											if($kt == 0)
											{
												$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['value'] = 'ALTE';
											}
											elseif($kt == 1)
											{
												$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['value'] = $drugp['dosage'];
											}
											else 
											{
												$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['value'] = '';
											}
											if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
											{
												//$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
												$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
											}
											else
											{
												$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
											}
											
										}
									}
								}
								else 
								{
									foreach($interval_arr_nutr as $kt => $vt)
									{
										if($kt == 0)
										{
											$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['value'] = 'ALTE';
										}
										elseif($kt == 1)
										{
											$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['value'] = $drugp['dosage'];
										}
										else
										{
											$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['value'] = '';
										}
										if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
										{
											//$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
											$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
										}
										else
										{
											$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
										}
									
									}
								}
							}
							else
							{
								foreach($interval_arr_nutr as $kt => $vt)
								{
									if($drugs_dosage_array[$drugp['ipid']][$drugp['id']][$vt['time']])
									{
										$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']] = $drugs_dosage_array[$drugp['ipid']][$drugp['id']][$vt['time']];
									}
									else 
									{
										$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
									}
								}
							}							
						}
						else
						{
							if( ! empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
									
							if( ! empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
								$drugs_dosage_arr = array();
								foreach($drugs_dosage_array[$drugp['ipid']][$drugp['id']] as $kd => $vd)
								{
									$drugs_dosage_arr[] = $vd['value'];
								}
							
								$drugp['dosage'] = implode("-", $drugs_dosage_arr);
										
								}
									
							}
							$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['dosage']['dosage'] = ($drugp['dosage'] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugp['dosage']);
						}
	
						$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['medications'] = $nutrition_medications[$drugp['medication_master_id']];
						$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['comments'] = $drugp['comments'] ;
						$patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['indications'] = $drug_indication[$drugp['id']];
						
						
						//ISPC-2786 Ancuta 11.01.2021
						if($drugp['verordnetvon'] != 0 )
						{
						    $patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['verordnetvon'] = $user_details[$drugp['verordnetvon']];
						}
						else
						{
						    $patient_medication[$drugp['ipid']][$sh_isnutrition][$drugp['id']]['verordnetvon'] = "";
						}
						
					}
					elseif($drugp['scheduled'] == '1')
					{
						$sh_scheduled = 'SC';
						$patient_medication[$drugp['ipid']][$sh_scheduled][$drugp['id']]['medications'] = $medications[$drugp['medication_master_id']];
						$patient_medication[$drugp['ipid']][$sh_scheduled][$drugp['id']]['dosage']['dosage'] = ($drugp['dosage'] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugp['dosage']);
						$patient_medication[$drugp['ipid']][$sh_scheduled][$drugp['id']]['comments'] = $drugp['comments'] ;
						$patient_medication[$drugp['ipid']][$sh_scheduled][$drugp['id']]['indications'] = $drug_indication[$drugp['id']];				
						
						//ISPC-2786 Ancuta 11.01.2021
						if($drugp['verordnetvon'] != 0 )
						{
						    $patient_medication[$drugp['ipid']][$sh_scheduled][$drugp['id']]['verordnetvon'] = $user_details[$drugp['verordnetvon']];
						}
						else
						{
						    $patient_medication[$drugp['ipid']][$sh_scheduled][$drugp['id']]['verordnetvon'] = "";
						}
					}
					else
					{
						
						if($medopt_det['actual']['time_schedule'] == '1')
						{
							if(!$patient_medication[$drugp['ipid']][$type]['interval'])
							{
								$patient_medication[$drugp['ipid']][$type]['interval'] = $interval_array['interval'][$drugp['ipid']]['actual'];
								$interval_arr_actual= array_values($interval_array['interval'][$drugp['ipid']]['actual']);
							}							
							
							if( empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
								if(strpos($drugp['dosage'],"-")){
									$drugs_dosage_array[$drugp['ipid']][$drugp['id']] = explode('-', $drugp['dosage']);
									if(count($drugs_dosage_array[$drugp['ipid']][$drugp['id']]) <= count($interval_arr_actual)){
										foreach($interval_arr_actual as $kt => $vt)
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = ($drugs_dosage_array[$drugp['ipid']][$drugp['id']][$kt] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugs_dosage_array[$drugp['ipid']][$drugp['id']][$kt]);
											if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
											{
												//$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
												$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
											}
											else
											{
												$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
											}
										}
									}
									else 
									{
										foreach($interval_arr_actual as $kt => $vt)
										{
											if($kt == 0)
											{
												$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = 'ALTE';
											}
											elseif($kt == 1)
											{
												$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = $drugp['dosage'];
											}
											else 
											{
												$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = '';
											}
											if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
											{
												//$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
												$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
											}
											else
											{
												$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
											}
											
										}
									}
								}
								else 
								{
									foreach($interval_arr_actual as $kt => $vt)
									{
										if($kt == 0)
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = 'ALTE';
										}
										elseif($kt == 1)
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = $drugp['dosage'];
										}
										else
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['value'] = '';
										}
										if($dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']])
										{
											//$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $vt['time'];
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = $dosage_given_ipid[$drugp['ipid']][$drugp['id']][$vt['time']]; //ISPC-2583 Carmen 27.04.2020
										}
										else
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
										}
									
									}
								}
							}
							else 
							{
								foreach($interval_arr_actual as $kt => $vt)
								{
										if($drugs_dosage_array[$drugp['ipid']][$drugp['id']][$vt['time']])
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']] = $drugs_dosage_array[$drugp['ipid']][$drugp['id']][$vt['time']];
										}
										else 
										{
											$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage'][$vt['time']]['given'] = '0';
										}
								}
							}
						}
						else
						{
						    //TODO-3972 Lore 23.03.2021
/* 							if( ! empty($drugs_dosage_array[$drugp['ipid']][$drugp['id']])){
    							$drugs_dosage_arr = array();
    							foreach($drugs_dosage_array[$drugp['ipid']][$drugp['id']] as $kd => $vd)
    							{
    								$drugs_dosage_arr[] = $vd['value'];
    							}
    						
    							$drugp['dosage'] = implode("-", $drugs_dosage_arr);
    									
    						} */
							//dd($drugs_dosage_arr,$drugp['dosage']);
							$patient_medication[$drugp['ipid']][$type][$drugp['id']]['dosage']['dosage'] = ($drugp['dosage'] == '! ALTE DOSIERUNG!' ? 'ALTE' : $drugp['dosage']);
						}						
						$patient_medication[$drugp['ipid']][$type][$drugp['id']]['medications'] = $medications[$drugp['medication_master_id']];
						$patient_medication[$drugp['ipid']][$type][$drugp['id']]['comments'] = $drugp['comments'] ;
						$patient_medication[$drugp['ipid']][$type][$drugp['id']]['indications'] = $drug_indication[$drugp['id']];
						//ISPC-2786 Ancuta 11.01.2021
						if($drugp['verordnetvon'] != 0 )
						{
						    $patient_medication[$drugp['ipid']][$type][$drugp['id']]['verordnetvon'] = $user_details[$drugp['verordnetvon']];
						}
						else
						{
						    $patient_medication[$drugp['ipid']][$type][$drugp['id']]['verordnetvon'] = "";
						}						
					}					
					
					
				}
				
				
				if($drugp['isbedarfs'] == '1')
				{
					$patient_medication_list[$drugp['ipid']][] = htmlentities($type . ' | ' . $medications[$drugp['medication_master_id']] . $drugp['dosage'] . $drugp['comments'], ENT_QUOTES, "UTF-8");
				}
				elseif($drugp['iscrisis'] == '1'){
	
					$patient_medication_list[$drugp['ipid']][] = htmlentities($type . ' | ' . $medications[$drugp['medication_master_id']] . $drugp['dosage'] . $drugp['comments'], ENT_QUOTES, "UTF-8");
				}
					
				if($drugp['isivmed'] == '1')
				{
					$patient_medication_list[$drugp['ipid']][] = htmlentities($type . ' | ' . $medications[$drugp['medication_master_id']] . $drugp['dosage'] . $drugp['comments'], ENT_QUOTES, "UTF-8");
				}
				else
				{
					$patient_medication_list[$drugp['ipid']][] = htmlentities($type . ' | ' . $medications[$drugp['medication_master_id']] . $drugp['dosage']. $drugp['comments'], ENT_QUOTES, "UTF-8");
				}
				
			}
			//print_r($patient_medication); exit;
		//exit;	
			//sort $patient_medication array to be the same order like in medication page
			$med_sort = array("M", "N", "KM", "I", "ER", "Q", "QP", "SC", "PM");//ISPC-2914,Elena,10.05.2021, Pumpe/Perfusor have to be insterted in array
			$patient_medication_unsorted = $patient_medication;
			$patient_medication = array();
			
			foreach($med_sort as $key => $value){ // loop
			    foreach ($patient_medication_unsorted as $patid => $patdetails) {
			    	foreach($patdetails as $k => $val)
			    	{
				        if ($k === $value) {
				           $patient_medication[$patid][$k] = $val;
				        }
			    	}
			    }            
			}
			
// 			dd($patient_medication);
// 			print_r($patient_medication); exit;
			foreach($ipids as $k_ipid => $v_ipid)
			{
				if(!empty($patient_medication[$v_ipid]))
				{
					$patient_medication_data['medication_data'][$v_ipid] = $patient_medication[$v_ipid];
					$patient_medication_data['ipids'][] = $v_ipid;
				}
	
				if(!empty($patient_medication_list[$v_ipid]))
				{
					$patient_medication_data_list[$v_ipid]['list_medication_data'] = implode("<br/>", $patient_medication_list[$v_ipid]);
				}
			}
			
			// 13.03.2020  Ancuta added: to "clear" special chars  
			$patient_medication_data = Pms_CommonData::clear_pdf_data($patient_medication_data);
			// -- 
			if($list)
			{
				return $patient_medication_data_list;
			}
			else
			{
				return $patient_medication_data;
			}
		}
	}
	





    /**
     *
     * Get an icon with number of the bed the (clinic-)patient is assigend to.
     *
     * The icon ist generated during a clinic-bed is created.</p>
     * In the system-icons there is a "master-icon" called "bed.svg".
     *
     * When creating a bed, a new icon is generated with the given
     * token of the bed.
     *
     * The icons are saved in ./icons_system/client-id/bedicons/
     * The parameter 'filter' is set to true, when this icon is choosen as a filter-option
     * in the PatientController->fetchoveralllistAction()
     * Maria:: Migration CISPC to ISPC 22.07.2020
     */
    public function get_icon_clinic_patient_bed_mapping($ipids,$filter = false)
    {

       if (empty($ipids)) {
            return;
        }


        $ipids = is_array($ipids) ? $ipids : array($ipids);

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $sys_icons = new IconsMaster();
        $clinicBed = new ClinicBed();
        $patientClinicBed = new PatientClinicBed();
        //ISPC-2858,Elena,19.03.2021
        $clientAlwaysHasIcon = false;
        $modules =  new Modules();
    	$clientModules = $modules->get_client_modules($clientid);


    	if($clientModules['1018'])
        {
            $clientAlwaysHasIcon = true;
        }

        $sys_sub_icons = $sys_icons->get_system_icons($clientid, false, false, false);
        foreach($sys_sub_icons as $k_sys_sub_icon => $v_sys_sub_icon)
        {
            if(!empty($v_sys_sub_icon['function']))
            {
                $system_icons[$v_sys_sub_icon['name']] = $v_sys_sub_icon;
            }
        }

        $result = array();

        foreach($ipids as $ipid) {

            //get the Bed of the Patient
            $bedZuordnung = $patientClinicBed->get_patient_bed_assignment($ipid, $clientid);
            $bed = $clinicBed->find_bed_by_id($bedZuordnung['bed_id'], $clientid);
            //ISPC-2682, elena, 05.10.2020
            $model = new PatientCaseStatus();
            $arr_case_status = $model->get_list_patient_status($ipid, $clientid, true);
            $open_station_case = false;
            foreach($arr_case_status as $case_status){
                if($case_status['case_type'] == 'station'){
                    $open_station_case = true;
                }
            }
            // show bed icon only if status case with type station is open
            //ISPC-2858,Elena,19.03.2021
            if($clientAlwaysHasIcon){
                if($filter && !$bed)
                    continue;
            }else{
            if(($filter && !$bed) || !$open_station_case)
                continue;
            }

            $result['ipids'][] = $ipid;

            $icon = $system_icons['icon_clinic_patient_bed_mapping'];
            $icon['is_default'] = TRUE;

            if ($bed) {
                $icon['image'] = $clientid . '/bedicons/' . $bed['icon_name'];
                $icon['is_default'] = FALSE;
            }

            $result['calculate_image'][$ipid] = $icon;

        }


        return $result;

    }

    /**
     *
     * Get an icon or a list of icons for PatienClinicCase (IM-12).
     * A patient can have zero to many cases.
     * Icons are identified only for open cases (discharge date is enpty)
     */
    public function get_icon_clinic_case($ipids, $filter = false, $filter_value = false)
    {

        if (empty($ipids)) {
            return;
        }

        $ipids = is_array($ipids) ? $ipids : array($ipids);

        if($filter) {
            $filter = str_replace('clinic_case_', '', $filter);
        }

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $sys_icons = new IconsMaster();
        $sys_sub_icons = $sys_icons->get_system_icons($clientid, false, false, false);

        $system_icons['station'] = $sys_sub_icons[10002];
        $system_icons['konsil'] = $sys_sub_icons[10003];
        $system_icons['ambulant'] = $sys_sub_icons[10004];
        $system_icons['sapv'] = $sys_sub_icons[10005];

        $model = new PatientCaseStatus();

        $result = array();

        foreach($ipids as $ipid) {

            //get the case_status of the patient
            $case_status = $model->get_list_patient_status($ipid, $clientid, true);

            $list_status = array();

            foreach ($case_status as $value) {
                $case_type = $value['case_type'] != '' ? $value['case_type'] : 'station'; //default
                $list_status[] = $case_type;
            }
            $list_status = array_values(array_unique($list_status)); //remove duplicate elements
            $standby = array_search('standby', $list_status);
            if($standby !== false){
               unset( $list_status[$standby]); // there is no icon for the standby-list
            }


            if($filter && !in_array($filter, $list_status))
                continue;

            $result['ipids'][] = $ipid;

            foreach ($list_status as $key => $status) {
                $result['clinic_case_icon'][$ipid]['show'][$key] = $system_icons[$status];
                $result['clinic_case_icon'][$ipid]['show'][$key]['image'] = $system_icons[$status]['image'];
                $result['clinic_case_icon'][$ipid]['show'][$key]['name'] = $system_icons[$status]['name'];
            }
        }

        return $result;

    }

    /**
     *
     * Get an icon for discharge-planning clinic (IM-3)//Maria:: Migration CISPC to ISPC 22.07.2020
     */
    public function get_entlassplan($ipids){

        if (empty($ipids)) {
            return;
        }

        $ipids = is_array($ipids) ? $ipids : array($ipids);


        // get private patient
        $ppC = Doctrine_Query::create()
            ->select('ipid')
            ->from('FormGenericSimpleForm')
            ->whereIn('ipid', $ipids)
            ->andWhere('isdelete = "0"');
        $pPatientsC = $ppC->fetchArray();

        $result = array();
        $icon = array();
        $icon['image'] = 'entlmngmnt.png';

        foreach ($pPatientsC as $patient_ipid)
        {
            $result['ipids'][] = $patient_ipid['ipid'];
            $result['entlassplan_clinic'][$patient_ipid['ipid']]= $icon;
        }
        return $result;
    }
    
    //TODO-3377 Carmen 27.08.2020
    public function has_power_of_attorney_icon($ipids)
    {
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = $logininfo->clientid;
    	$userid = $logininfo->userid;
    	$usertype = $logininfo->usertype;
    	
    	$modules =  new Modules();
    	$clientModules = $modules->get_client_modules($clientid);
    	$has_power_of_attorney_patients = array();
    	
    	if($clientModules['237'])
    	{
	    	if(!is_array($ipids))
	    	{
	    		$ipids = array($ipids);
	    	}
	    	else
	    	{
	    		$ipids = $ipids;
	    	}
			
	    	$salt =  Zend_Registry::get('salt');
				
			$sql = "*,AES_DECRYPT(cnt_first_name, '{$salt}') as cnt_first_name";
			$sql .=",AES_DECRYPT(cnt_middle_name, '{$salt}') as cnt_middle_name";
			$sql .=",AES_DECRYPT(cnt_last_name, '{$salt}') as cnt_last_name";
			$sql .= ",AES_DECRYPT(cnt_title, '{$salt}') as cnt_title";
			$sql .=",AES_DECRYPT(cnt_street1, '{$salt}') as cnt_street1";
			$sql .= ",AES_DECRYPT(cnt_street2, '{$salt}') as cnt_street2";
			$sql .= ",AES_DECRYPT(cnt_zip, '{$salt}') as cnt_zip";
			$sql .=",AES_DECRYPT(cnt_city, '{$salt}') as cnt_city";
			$sql .=",AES_DECRYPT(cnt_phone, '{$salt}') as cnt_phone";
			$sql .=",AES_DECRYPT(cnt_email, '{$salt}') as cnt_email";
			$sql .= ",AES_DECRYPT(cnt_mobile, '{$salt}') as cnt_mobile";
			$sql .= ",AES_DECRYPT(cnt_comment, '{$salt}') as cnt_comment";
			$sql .=",AES_DECRYPT(cnt_nation, '{$salt}') as cnt_nation";
			$sql .=",AES_DECRYPT(cnt_custody, '{$salt}') as cnt_custody";
			
			$cpm = new ContactPersonMaster();
			$q =  $cpm->getTable()->createQuery()
			->select($sql)
			->whereIn("ipid" , $ipids)
			->andWhere('isdelete = 0')
			->andWhere('cnt_hatversorgungsvollmacht = 1')
			->fetchArray();
	    	
	    	foreach($q as $key_cp => $v_cp)
	    	{
	    		$has_power_of_attorney_patients['has_power_of_attorney'][$v_cp['ipid']][] = $v_cp;
	    		$has_power_of_attorney_patients_ipids[] = $v_cp['ipid'];
	    	}   
	    	
	    	$has_power_of_attorney_patients['ipids'] = array_unique($has_power_of_attorney_patients_ipids);
    	}
    	
    	return $has_power_of_attorney_patients;
    }
    //--
	
    /**
     * ISPC-2695 Ancuta 03.11.2020
     * @param array $ipids
     * @return void|string|array[]
     */
    public function get_icon_mePatient_faces ($ipids = array() )
    {
     
//         return; 
        if (empty($ipids)) {
            return; //fail-safe
        }
        $ipids = is_array($ipids) ? $ipids : array($ipids);
        
        if (Zend_Registry::isRegistered('mepatient')) {
            $mepatient_cfg = Zend_Registry::get('mepatient');
        }
        else
        {
            return; //fail-safe
        }
        
        // iau din  survey_patient2chain - caut dupa ipid, iau survey_took,
        //cu survey took ma duc in survey_results  -   apoi iau detaliile din survey_quesions in functie de survey si question  < ca sa le afisez labelurile
//         pms9004
//         26fac84612b98aaf5569cfdacc76d2cf61b78d64

        $result = [];
        $surveys_q = Doctrine_Query::create()
        ->select("*")
        ->from("SurveyPatient2chain");
        $surveys_q->whereIn('ipid',$ipids);
        $surveys_q->andwhere('isdelete = 0');
        $surveys_q->andwhere('end != "0000-00-00 00:00:00" ');
        $survey_data_array = $surveys_q->fetchArray();

        if(empty($survey_data_array)){
            return;
        }
        
        //SurveyResults
        $survey_took_ids = array();
        $patients2survey_took_ids = array();
        $patient2chain = array();
        $master_chain_ids= array();
        $patient_surveys = array();
        foreach($survey_data_array as $k=>$sd){
            $patient_surveys[$sd['ipid']][$sd['id']] = $sd;
            $survey_took_ids[] = $sd['id'];
            $patients2survey_took_ids[$sd['ipid']][] = $sd['id'];
            
            $patient2chain[$sd['ipid']][$sd['id']] = $sd;
            $master_chain_ids[] = $sd['survey_id'];
        }
        $master_chain_ids = array_values(array_unique($master_chain_ids));
        
        $surveys_chain_q = Doctrine_Query::create()
        ->select("*")
        ->from("SurveyMasterChains indexby id");
        $surveys_chain_q ->whereIn('id',$master_chain_ids);
        $survey_chain_data_array = $surveys_chain_q ->fetchArray();
        
        if(empty($survey_data_array)){
            return;
        }
        
        if(empty($survey_took_ids)){
            return;
        }
        
        $surveys_sr_slq = Doctrine_Query::create()
        ->select("*")
        ->from("SurveyResults");
        $surveys_sr_slq->whereIn('survey_took',$survey_took_ids);
        $surveys_sr_slq->andwhere('isdelete = 0');
        $survey_results_array = $surveys_sr_slq->fetchArray();
        
        $surveys2survey_took= array();
        $question_ids = array();
        $survey_ids = array();
        
        foreach($survey_results_array as $k=>$sr){
            $surveys2survey_took[$sr['survey_took']][] = $sr;
            $survey_ids[] = $sr['survey'];
            $question_ids[] = $sr['question'];
        }

        $needed2patient = array();
        foreach($patients2survey_took_ids as $ipid=>$stooks){
            foreach($stooks as $sk=>$stook_id){
                if(count($surveys2survey_took[$stook_id]) == 1){
                    $patient_surveys[$ipid][$stook_id]['answers'] = end($surveys2survey_took[$stook_id]);
                    $needed2patient[$ipid][$stook_id] = end($surveys2survey_took[$stook_id]);
                } else{
                    unset($patient_surveys[$ipid][$stook_id]);
                }
            }
        }

        if(empty($needed2patient)){
            return;
        }

        //check if survey has only ONE question 
        $surveys_sq_slq = Doctrine_Query::create()
        ->select("s.*,q.*")
        ->from("SurveySurveys s");
        $surveys_sq_slq->leftJoin("s.SurveyQuestions q indexby id");
        $surveys_sq_slq->whereIn('q.id',$question_ids);
        $surveys_sq_slq->andWhereIn('s.id',$survey_ids);
        $survey_info_array = $surveys_sq_slq->fetchArray();
        
        
        if(empty($survey_info_array)){
            return;
        }
        
        
        //Chack quesion faces settings
        $faces_info = Doctrine_Query::create()
        ->select("question as questionid,nooffaces as nooffaces,direction as direction")
        ->from("SurveyAnswersFaces indexby questionid")
        ->fetchArray();
        
        $allowed_surveys = array();
        foreach($survey_info_array as $ssk=>$s_survey_data){
            if(isset($s_survey_data['SurveyQuestions']) && count($s_survey_data['SurveyQuestions']) == '1'){
                foreach($s_survey_data['SurveyQuestions'] as $sqk=>$question_data){
                    if($question_data['type'] == 'faces'){
                        $allowed_surveys[$s_survey_data['id']] =  $s_survey_data;
                    }
                }
            }
        }
        
        $result['mePatient_faces_results']['labels'] = array(
            'date'=>'Datum',
            //'status'=>'Status', //Ancuta 04.05.2020 - commented - as status is no longer listed
        );
        $master_chain_names =  array();
        foreach($survey_chain_data_array as $chain_id=>$chain_data){
            $master_chain_names[$chain_id] = $chain_data['name'];
            $result['mePatient_faces_results']['labels'][$chain_id] = $chain_data['name'];
        }
        
        $survey_results = array();
        $x = 0 ;
        
        
        $set_3_asc = array('1','2','3');
        $set_3_desc = array('3','2','1');
        
        foreach($ipids as $ipid){
            
            if(! empty($patient_surveys[$ipid])){
                foreach($patient_surveys[$ipid] as $survey_took=>$data){
                    // check if ansfers are allowed 
                    if(empty($data['answers'])){
                        //skip
                    } else{
                        if(array_key_exists($data['answers']['survey'],$allowed_surveys) && array_key_exists($data['answers']['question'],$allowed_surveys[$data['answers']['survey']]['SurveyQuestions']) ) {
//                             $survey_results[ $ipid ][$data['survey_id']][$x]['date']      = date('d.m.Y',strtotime( $data['end'] )) ;
//                             $survey_results[ $ipid ][$data['survey_id']][$x][$master_chain_names[$data['survey_id']]]      = $data['answers']['answer'] ;
                            $survey_results[ $ipid ][$data['survey_id']][$survey_took]['date']      = date('d.m.Y',strtotime( $data['end'] )) ;
                            
//                             $survey_results[ $ipid ][$data['survey_id']][$survey_took][$master_chain_names[$data['survey_id']]] = $data['answers']['answer'] ;
                            $survey_results[ $ipid ][$data['survey_id']][$survey_took][$master_chain_names[$data['survey_id']]] = 'set_of_'.$faces_info[$data['answers']['question']]['nooffaces'].'-'.$data['answers']['answer'] ;
                            
                            $x++;
                        }
                    }
                    
                }
            }
        }
        // raspunsurile sunt de la 1 la 6
        // in functie de numarul de faces - afisam valoarea 
        
        //$faces_info
        if(empty($survey_results)){
            $result['ipids']= array();;
            return;
        }
        
        if ( ! empty($survey_results)) {
            
            foreach ($survey_results as $kipid => $rows) {
                
                if ( ! empty($rows)) {
                    
                    $result['mePatient_faces_results'][$kipid] = $rows;
                    
                    $result['ipids'][] = $kipid;
                }
            }
            
            $result['ipids'] = array_values(array_unique($result['ipids']));
        }
        
//         dd($result);
        return $result;
        
    }
    
    
    
}

?>