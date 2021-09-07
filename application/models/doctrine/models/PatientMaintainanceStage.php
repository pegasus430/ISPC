<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientMaintainanceStage', 'IDAT');

	class PatientMaintainanceStage extends BasePatientMaintainanceStage {

		//this should be used to pupulate the selectboxes
		public static function get_MaintainanceStage_array()
		{
		    return [
	    		'' => self::translate('pleaseselect'),
	    		'keine' => self::translate('keine'),
	    		1 => '1',
	    		2 => '2',
	    		3 => '3',
	    		4 => '4',
	    		5 => '5'		        
		    ];
		}
		
		/**
		 * @cla on 04.12.2018
		 * for columns that hold INTs and are not defined as ENUMs
		 * 
		 * @param string $column
		 * @param bool $reverser, if you need string = int
		 * @return multitype:
		 */
		public static function _mapping_columns($column = '' , $reverser = false) 
		{
		    $result  = [];
		    
            $mapping = [
                
    		    "status" => [
    		        "1" => self::translate('liegt vor'),
    		        "2" => self::translate('wurde beantragt'),
    		        "3" => self::translate('Höherstufung beantragt'),
    		        "4" => self::translate('Widerspruch / Klage eingereicht'),
    		        "5" => self::translate('Erstantrag vornehmen'),
    		    ],
            ];
            
            $result = isset ($mapping[$column]) ? $mapping[$column] : [];
            
            if ($reverser) {
                $result = array_flip ($result);
            }
            
            return $result;
            
		}
		
		
		
		
		public function getpatientMaintainanceStage($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*, 
						IF(e_fromdate = '0000-00-00', fromdate, IF(e_fromdate = '1970-01-01', fromdate, e_fromdate)) as e_fromdate,
						IF(h_fromdate = '0000-00-00', fromdate, IF(h_fromdate = '1970-01-01', fromdate, h_fromdate)) as h_fromdate
						")
				->from('PatientMaintainanceStage')
				->where("ipid= ?", $ipid)
// 				->andWhere("stage>0")
				->orderBy('fromdate asc');
			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}

		public static function getLastpatientMaintainanceStage($ipid)
		{// changed  to current ISPC-2078
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientMaintainanceStage')
				->where("ipid=?", $ipid)
				->andWhere('((CURRENT_DATE() BETWEEN fromdate AND tilldate) OR (CURRENT_DATE() >= fromdate and tilldate = "0000-00-00"))')
				->orderBy('fromdate desc')
				->limit(1);
			
			$loc = $drop->execute();
			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}
		
		

		public function getFirstpflegestufeMaintainanceStage($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientMaintainanceStage')
				->where("ipid= ?", $ipid)
				->orderBy('create_date asc')
				;
			$crdate = $drop->execute();

			if($crdate)
			{
				$livearr = $crdate->toArray();
				return $livearr;
			}
		}

		public function getLastpflegestufeMaintainanceStage($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientMaintainanceStage')
				->where("ipid= ?", $ipid)
				->orderBy('create_date desc')
				->limit(1);
			$crdate = $drop->execute();

			if($crdate)
			{
				$livearr = $crdate->toArray();
				return $livearr;
			}
		}

		public function getMaintainanceDrop()
		{
			$Tr = new Zend_View_Helper_Translate();
			$degreearr = array("" => $Tr->translate('pleaseselect'), '1' => '1', '2' => '2', '3' => '3', 'antrag' => 'Antrag');
			return $degreearr;
		}

		public function getpatientMaintainanceStageInPeriod($ipid, $startDate, $endDate)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientMaintainanceStage')
				->where("ipid= ?", $ipid)
				->andWhere("('" . date("Y-m-d", strtotime($startDate)) . "' <= tilldate or '0000-00-00' = tilldate)  and '" . date("Y-m-d", strtotime($endDate)) . "' >= fromdate ")
				->orderBy('fromdate,create_date asc');
			$loc = $drop->fetchArray();


			return $loc;
		}
		
		public function getpatientHighestMaintainanceStageInPeriod($ipid, $startDate, $endDate)
		{
			$drop = Doctrine_Query::create()
				->select("*,stage, cast( replace( stage, '+', '.5' ) AS decimal( 10, 2 ) ) AS stage_alias")
				->from('PatientMaintainanceStage')
				->where("ipid= ?", $ipid)
				->andWhere("('" . date("Y-m-d", strtotime($startDate)) . "' <= tilldate or '0000-00-00' = tilldate)  and '" . date("Y-m-d", strtotime($endDate)) . "' >= fromdate ")
				->orderBy("cast( replace( stage, '+', '.5' ) AS decimal( 10, 2 ) ) DESC")
			    ->limit("1");
			$loc = $drop->fetchArray();

			return $loc;
		}

		public function get_multiple_patatients_mt_period($ipids, $start_date, $end_date)
		{

			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}

			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientMaintainanceStage')
				->whereIn("ipid", $ipids_arr)
				->andWhere("('" . date("Y-m-d", strtotime($start_date)) . "' <= tilldate or '0000-00-00' = tilldate)  and '" . date("Y-m-d", strtotime($end_date)) . "' >= fromdate ")
				->orderBy('fromdate,create_date asc');
			$loc = $drop->fetchArray();


			return $loc;
		}

		public function get_multiple_patients_mt_period($ipids, $multiple_periods = false)
		{
		    if(empty($ipids)){
		        return false;
		    }
			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}

			$sql_w = array();
			foreach($ipids_arr as $v_ipid)
			{
				if(!empty($multiple_periods[$v_ipid]))
				{
					$start_date = $multiple_periods[$v_ipid]['start'];
					$end_date = $multiple_periods[$v_ipid]['end'];

					$sql_w[] = ' (`ipid` LIKE "' . $v_ipid . '" AND ("' . date("Y-m-d", strtotime($start_date)) . '" <= `tilldate` OR "0000-00-00" = `tilldate`)  AND "' . date("Y-m-d", strtotime($end_date)) . '" >= `fromdate`) ';
				}
			}
			
			if(empty($sql_w))
			{
				//reset array
				$sql_w[] = ' (`ipid` IN (' . implode(",", $ipids_arr) . '))';
			}
			

			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientMaintainanceStage')
				->where(implode(" OR ", $sql_w))
				->orderBy('fromdate, create_date asc');
			$loc = $drop->fetchArray();

			return $loc;
		}

		public function clone_record($ipid, $target_ipid)
		{
			$patient_maintainance_stage = $this->getLastpatientMaintainanceStage($ipid);

			if($patient_maintainance_stage)
			{
				foreach($patient_maintainance_stage as $k_patms => $v_patms)
				{
					$pms = new PatientMaintainanceStage();
					$pms->ipid = $target_ipid;
					$pms->fromdate = $v_patms['fromdate'];
					$pms->tilldate = $v_patms['tilldate'];
					$pms->stage = $v_patms['stage'];
					$pms->erstantrag = $v_patms['erstantrag'];
					$pms->horherstufung = $v_patms['horherstufung'];
					//ISPC-2668 Lore 11.09.2020
					$pms->rejected_date = $v_patms['rejected_date'];
					$pms->opposition_date = $v_patms['opposition_date'];
					//.
					$pms->save();
				}
			}
			else
			{
				return false;
			}
		}

		
		/**
		 * @deprecated use parent::findOrCreateOneByIpidAndId
		 */
		public function findOrCreateOneById($value, array $data = array(), $hydrationMode = null)
		{
			if (is_null($value) || empty($data['stage'])){
				return;
			} 
			
			if (is_null($value) ||  ! $entity = $this->getTable()->findOneByIdAndIpid($value, $data['ipid'])) {
				unset($data['id']);
				$entity = $this->getTable()->create(array(
// 						$fieldName => $value
				));
			}
			$entity->fromArray($data); //update
		
			$entity->save(); //at least one field must be dirty in order to persist

			return $entity;
		}

		
		
		public function delete_row( $id = null )
		{
			if (( ! is_null($id)) && ($obj = $this->getTable()->find($id)))
			{
				$obj->delete();
				return true;
		
			} else {
				return false;
			}
		}	
	}

?>