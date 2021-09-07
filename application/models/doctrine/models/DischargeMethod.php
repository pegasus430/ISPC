<?php

	Doctrine_Manager::getInstance()->bindComponent('DischargeMethod', 'SYSDAT');

	class DischargeMethod extends BaseDischargeMethod {

	    public static function getDischargeMethod($cid, $isdrop = 0, $sorted = false,$show_only_from_master = false)
		{
	        //ISPC-2612 Ancuta 25.06.2020 - 28.06.2020
			$Tr = new Zend_View_Helper_Translate();
			$epid = Doctrine_Query::create()
				->select('*')
				->from('DischargeMethod')
				->where('clientid=' . $cid . '');
				if($show_only_from_master){//ISPC-2612 Ancuta 27.06.2020
				    $epid->andWhere('connection_id is NOT null');
				    $epid->andWhere('master_id is NOT null');
			    }
            $epid->andWhere('isdelete=0');
			$epidexec = $epid->execute();
			$disarray = $epidexec->toArray();

			if($isdrop == 1)
			{
			    
			    if($sorted){
			        
			        foreach($disarray as $discharge)
			        {
			            $discharges_array[$discharge['id']] = $discharge['description'];
			        }
    				$sorted_array = Pms_CommonData::a_sort($discharges_array);
    				
			        $discharges = array("" => $Tr->translate('selectdischarge'));
			        
    				foreach($sorted_array as $did =>$dvalue){
    					$discharges[$did] = $dvalue;
    				}
				
			    } 
			    else
			    {
    				$discharges = array("" => $Tr->translate('selectdischarge'));
    
    				foreach($disarray as $discharge)
    				{
    					$discharges[$discharge['id']] = $discharge['description'];
    				}
			    }
				
				return $discharges;
				
				
			}
			elseif($isdrop == 3)
			{
				foreach($disarray as $discharge)
				{
					$discharges[$discharge['id']] = strtolower($discharge['abbr']);
				}
				return $discharges;
			}
			else
			{
				return $disarray;
			}
		}
		
		public function get_client_discharge_method($cid,$dead_methods = false,$string_ids = false)
		{
			$Tr = new Zend_View_Helper_Translate();
			$epid = Doctrine_Query::create()
				->select('*')
				->from('DischargeMethod')
				->where('clientid=' . $cid . '')
				->andWhere('isdelete=0');
			$epidexec = $epid->execute();
			$disarray = $epidexec->toArray();
			
			$dead_methods_abbr = array('TOD','tod','Verstorben','verstorben','VERSTORBEN','Tod','TODNA');
			
			$dm_string = '"0", ';
			foreach($disarray as $discharge)
			{
			    $discharges[$discharge['id']] = $discharge['description'];
			    
			    if(in_array($discharge['abbr'],$dead_methods_abbr)){
                    $dead_methods_ids[] = $discharge['id'];
                    $dm_string  .= '"'. $discharge['id'] .'", ';
			    }
			}
			
			
			if($dead_methods ){
			    if(empty($dead_methods_ids)){
			        $dead_methods_ids[] = "99999999999";
			    }
			    
			    if($string_ids){
			        $dead_methods_ids  = $dm_string;
			    }
			    
			    
			    return $dead_methods_ids;
			} else{
			    return $discharges;
			}
		}
 
		/*ISPC-2457 Lore 16.07.2020*/
		public function get_client_discharge_method_abbr($cid,$dead_methods = false,$string_ids = false)
		{
		    $Tr = new Zend_View_Helper_Translate();
		    $epid = Doctrine_Query::create()
		    ->select('*')
		    ->from('DischargeMethod')
		    ->where('clientid=' . $cid . '')
		    ->andWhere('isdelete=0');
		    $epidexec = $epid->execute();
		    $disarray = $epidexec->toArray();
		    
		    $dead_methods_abbr = array('TOD','tod','Verstorben','verstorben','VERSTORBEN','Tod','TODNA');
		    
		    $dm_string = '"0", ';
		    foreach($disarray as $discharge)
		    {
		        $discharges[$discharge['id']] = $discharge['abbr'];
		        
		        if(in_array($discharge['abbr'],$dead_methods_abbr)){
		            $dead_methods_ids[] = $discharge['id'];
		            $dm_string  .= '"'. $discharge['id'] .'", ';
		        }
		    }
		    
		    
		    if($dead_methods ){
		        if(empty($dead_methods_ids)){
		            $dead_methods_ids[] = "99999999999";
		        }
		        
		        if($string_ids){
		            $dead_methods_ids  = $dm_string;
		        }
		        
		        
		        return $dead_methods_ids;
		    } else{
		        return $discharges;
		    }
		}
		
		public function getDischargeMethodById($did)
		{

			$epid = Doctrine_Query::create()
				->select('*')
				->from('DischargeMethod')
				->where('id=' . $did . '');
			$epidexec = $epid->execute();
			if($epidexec)
			{
				$disarray = $epidexec->toArray();
				return $disarray;
			}
		}

		
		public function get_report_discharge_method($cid)
		{
		
		    $q = Doctrine_Query::create()
		    ->select('*')
		    ->from('DischargeMethod')
		    ->where('clientid=' . $cid . '')
		    ->andWhere('anlage_6_report=1')
		    ->andWhere('isdelete=0');
		    $dm = $q->fetchArray();
		
		    if($dm){
		        foreach($dm as $k=>$d){
		            $report_dm[] = $d['id'];
		        }
		        return $report_dm;
		    }
		}
		//Maria:: Migration CISPC to ISPC 22.07.2020
        public static function getDeathMethod($clientid){
            $dmethod_q = Doctrine_Query::create()
                ->select('*')
                ->from('DischargeMethod')
                ->where('clientid=?',$clientid)
                ->andWhere('isdelete=0');
            $dmethod_arr = $dmethod_q->fetchArray();

            $dmethod_ids=array();

            foreach($dmethod_arr as $dmethod){
                $descr=strtolower($dmethod['description']);
                $abbr=strtolower($dmethod['abbr']);
                if($abbr=="verstorben" || $descr=="verstorben" || $abbr=="tod" || $abbr=="tod" || $abbr=="gestorben" || $abbr=="exitus"){
                    $dmethod_ids[]=$dmethod['id'];
                }

            }

            return $dmethod_ids;
        }
		
	}

?>