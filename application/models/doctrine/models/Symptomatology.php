<?php

	Doctrine_Manager::getInstance()->bindComponent('Symptomatology', 'MDAT');

	class Symptomatology extends BaseSymptomatology {

		public $triggerformid = 16;
		public $triggerformname = "frmpatientsymptomatology";

		public function getPatientSymptpomatology($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$cust = Doctrine_Query::create()
				->select('*')
				->from('Symptomatology')
				->andWhere('clientid="' . $clientid . '"')
				->where('ipid = "' . $ipid . '" and setid="0"')
				->orderBy('symptomid,entry_date,id');
			$track = $cust->execute();
			if($track)
			{
				$darray = $track->toArray();

				return $darray;
			}
		}

		public function getPatientSymptpomatologyBySet($ipid, $setid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$cust = Doctrine_Query::create()
				->select('*')
				->from('Symptomatology')
				->andWhere('clientid="' . $clientid . '"')
				->where('ipid = "' . $ipid . '" and setid="' . $setid . '"')
				->orderBy('symptomid,entry_date,id');
			$track = $cust->execute();

			if($track)
			{
				$darray = $track->toArray();

				return $darray;
			}
		}

		public function getPatientSymptpomatologyLast($ipid, $setid = 1) //HOPE symptomatology set
		{
		    /* ISPC-1775,ISPC-1678 */
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$cust = Doctrine_Query::create()
				->select('date_format(entry_date,"%Y-%m-%d %H:%i:%s") as last_date, setid, create_user')
				->from('Symptomatology')
				->andWhere('clientid="' . $clientid . '"')
				->where('ipid = "' . $ipid . '"')
				->andWhere('setid = "' . $setid . '"')
				->orderBy('entry_date desc')
				->limit(1);
			//echo $cust->getSqlQuery();
			$lastsym = $cust->fetchArray();

			if($lastsym)
			{
				$symset = new SymptomatologyValues();
				$sympsetdet = $symset->getSymptpomatologyValues($lastsym[0]['setid']);

				$lastsymdate = strtotime($lastsym[0]['last_date']);
				$lastsymdateplus = date('Y-m-d H:i:s', $lastsymdate + 3);
				$lastsymdateminus = date('Y-m-d H:i:s', $lastsymdate - 3);

				$symptoms = Doctrine_Query::create()
					->select('*')
					->from('Symptomatology')
					->andWhere('clientid="' . $clientid . '"')
					->where('ipid = "' . $ipid . '" and setid = "' . $lastsym[0]['setid'] . '" and date_format(entry_date,"%Y-%m-%d %H:%i:%s") <= "' . $lastsymdateplus . '" and date_format(entry_date,"%Y-%m-%d %H:%i:%s") >= "' . $lastsymdateminus . '"')
					->andWhere('create_user = "'.$lastsym[0]['create_user'].'"')
					->orderBy('symptomid,entry_date,id');
				//echo $symptoms->getSqlQuery();
				$lastsymptoms = $symptoms->fetchArray();

				foreach($lastsymptoms as $key => $lastsymp)
				{
					$lastsymptoms[$key]['sym_description'] = $sympsetdet[$lastsymp['symptomid']];
				}

				return $lastsymptoms;
			}
		}

		public function getPatientSymptpomatologyFirst($ipid, $setid = 1) //HOPE symptomatology set
		{
		    /* ISPC-1775,ISPC-1678 */
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$cust = Doctrine_Query::create()
				->select('date_format(entry_date,"%Y-%m-%d %H:%i:%s") as last_date, setid, create_user')
				->from('Symptomatology')
				->andWhere('clientid="' . $clientid . '"')
				->where('ipid = "' . $ipid . '"')
				->andWhere('setid = "' . $setid . '"')
				->orderBy('entry_date ASC')
				->limit(1);
			//echo $cust->getSqlQuery();
			$lastsym = $cust->fetchArray();

			if($lastsym)
			{
				$symset = new SymptomatologyValues();
				$sympsetdet = $symset->getSymptpomatologyValues($lastsym[0]['setid']);

				$lastsymdate = strtotime($lastsym[0]['last_date']);
				$lastsymdateplus = date('Y-m-d H:i:s', $lastsymdate + 3);
				$lastsymdateminus = date('Y-m-d H:i:s', $lastsymdate - 3);

				$symptoms = Doctrine_Query::create()
					->select('*')
					->from('Symptomatology')
					->andWhere('clientid="' . $clientid . '"')
					->where('ipid = "' . $ipid . '" and setid = "' . $lastsym[0]['setid'] . '" and date_format(entry_date,"%Y-%m-%d %H:%i:%s") <= "' . $lastsymdateplus . '" and date_format(entry_date,"%Y-%m-%d %H:%i:%s") >= "' . $lastsymdateminus . '"')
					->andWhere('create_user = "'.$lastsym[0]['create_user'].'"')
					->orderBy('symptomid,entry_date,id');
				//echo $symptoms->getSqlQuery();
				$lastsymptoms = $symptoms->fetchArray();

				foreach($lastsymptoms as $key => $lastsymp)
				{
					$lastsymptoms[$key]['sym_description'] = $sympsetdet[$lastsymp['symptomid']];
				}

				return $lastsymptoms;
			}
		}

		public function getPatientSymptpomatologyLastEntered($ipid, $setid = 1) //gets values by set from multiples entries, latest value entered (not null)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$symset = new SymptomatologyValues();
			$sympsetdet = $symset->getSymptpomatologyValues($setid);

			if($sympsetdet)
			{
				foreach($sympsetdet as $symptom)
				{
					$lastval = Doctrine_Query::create()
						->select('*')
						->from('Symptomatology')
						->where('ipid = "' . $ipid . '" and setid = "' . $setid . '" and symptomid = "' . $symptom['id'] . '"')
						->andWhere('input_value is not NULL')
						->orderBy('entry_date desc')
						->limit(1);
					$lastvalue = $lastval->fetchArray();

					$lastsymptoms[$symptom['id']]['description'] = utf8_encode($symptom['value']);
					$lastsymptoms[$symptom['id']]['custom_description'] = utf8_encode($lastvalue[0]['custom_description']);
					$lastsymptoms[$symptom['id']]['value'] = $lastvalue[0]['input_value'];
				}

				return $lastsymptoms;
			}
		}

		public function getPatientSymptpomatologyFirstAdm($ipid, $setid = 1, $admission_date) //HOPE symptomatology set
		{
		    /* ISPC-1775,ISPC-1678 */
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$lastsym = Doctrine_Query::create()
				->select('date_format(entry_date,"%Y-%m-%d %H:%i:%s") as last_date, setid, create_user')
				->from('Symptomatology')
				->where('ipid = ?', $ipid)
				->andWhere('setid = ?', $setid)
				->andWhere('DATE(entry_date) >= ?', date("Y-m-d",strtotime($admission_date)))
				->orderBy('entry_date ASC')
                ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);

			if($lastsym)
			{
				$symset = new SymptomatologyValues();
				$sympsetdet = $symset->getSymptpomatologyValues($lastsym['setid']);

				$lastsymdate = strtotime($lastsym['last_date']);
				$lastsymdateplus = date('Y-m-d H:i:s', $lastsymdate + 3);
				$lastsymdateminus = date('Y-m-d H:i:s', $lastsymdate - 3);

				$symptoms = Doctrine_Query::create()
					->select('*')
					->from('Symptomatology')
					->where('ipid = ?',$ipid)
					->andWhere('setid = ? ',$lastsym['setid'] )
					->andWhere('date_format(entry_date,"%Y-%m-%d %H:%i:%s") <=  ? ',$lastsymdateplus )
					->andWhere('date_format(entry_date,"%Y-%m-%d %H:%i:%s") >=  ? ',$lastsymdateminus )
					->andWhere('create_user = ?', $lastsym['create_user'])
					->orderBy('symptomid,entry_date,id')
				    ->fetchArray();
				
				$lastsymptoms = $symptoms;
				
				foreach($lastsymptoms as $key => $lastsymp)
				{
					$lastsymptoms[$key]['sym_description'] = $sympsetdet[$lastsymp['symptomid']];
					
					$firstsymptoms[$lastsymp['symptomid']]['description'] = utf8_encode($sympsetdet[$lastsymp['symptomid']]['value']);
					$firstsymptoms[$lastsymp['symptomid']]['custom_description'] = utf8_encode($lastsymp['custom_description']);
					$firstsymptoms[$lastsymp['symptomid']]['value'] = $lastsymp['input_value'];
					$firstsymptoms[$lastsymp['symptomid']]['entry_date'] = $lastsymp['entry_date'];
					
					
				}
				
				return $firstsymptoms;
			}
		}

		public function getPatientSymptpomatologyLastEnteredAdm($ipid, $setid = 1, $discharge_date) //gets values by set from multiples entries, latest value entered (not null)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$symset = new SymptomatologyValues();
			$sympsetdet = $symset->getSymptpomatologyValues($setid);

			
			if($sympsetdet)
			{
			    $lastsymptoms = array();
				foreach($sympsetdet as $symptom)
				{
					$lastval = Doctrine_Query::create()
						->select('*')
						->from('Symptomatology')
						->where('ipid = ?', $ipid)
						->andWhere('setid = ?', $setid)
						->andWhere('symptomid = ?',$symptom['id'])
						->andWhere('input_value is not NULL')
						->andWhere('DATE(entry_date) <= ?',date("Y-m-d",strtotime($discharge_date)))
						->orderBy('entry_date desc')
					    ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);

					$lastsymptoms[$symptom['id']]['description'] = utf8_encode($symptom['value']);
					$lastsymptoms[$symptom['id']]['custom_description'] = utf8_encode($lastval['custom_description']);
					$lastsymptoms[$symptom['id']]['value'] = $lastval['input_value'];
					$lastsymptoms[$symptom['id']]['entry_date'] = $lastval['entry_date'];
				}
				
				return $lastsymptoms;
			}
		}

		
		/**
		 * @author Ancuta 
		 * ISPC-2512 
		 * @param unknown $ipid
		 * @param array $period
		 * @param number $setid
		 * @return unknown
		 * #ISPC-2512PatientCharts
		 */
		public function get_patient_symptpomatology_period($clientid = 0,$ipid, $period = array(), $setid = 1) //HOPE symptomatology set
		{
		    if(empty($clientid ) && empty($ipid)){
		        return;
		    }
		    $sql_period_params = array();
		    
		    if($period)
		    {
		        $sql_period = ' (DATE(entry_date) != "1970-01-01" AND entry_date BETWEEN ? AND ? ) ';
		        
		        $sql_period_params = array( $period['start'], $period['end'] );
		    }
		    else
		    {
		        $sql_period = ' DATE(status_date) != "1970-01-01"  ';
		    }
		    
		    $cust = Doctrine_Query::create()
		    ->select('*')
		    ->from('Symptomatology')
		    ->andWhere('clientid= ?', $clientid )
		    ->where('ipid = ?',$ipid )
		    ->andWhere('setid = ?',$setid)
		    ->orderBy('entry_date asc');
		    if ( ! empty($sql_period)) {
		        $cust->andWhere( $sql_period , $sql_period_params);
		    }
		    $sym = $cust->fetchArray();
		    
		    $color_mapping_old = array(
		        "0"=>"0fba29",
		        "1"=>"0fba29",
		        "2"=>"67c230",
		        "3"=>"b4c837",
		        "4"=>"ebcd3b",
		        "5"=>"edb935",
		        "6"=>"f09a2c",
		        "7"=>"f37b23",
		        "8"=>"f65c1b",
		        "9"=>"fa330f",
		        "10"=>"ff0000",
		    );
		    
		    /*
		    0 #2bae2f
		    1 #e9d149
		    2 #ffa500
		    3 #dc4646
		    */
		    
// 		    $none = array(0);
// 		    $weak = array(1,2,3,4);
// 		    $average = array(5,6,7);
// 		    $strong = array(8,9,10);
		    
		    $color_mapping = array(
		        "0"=>"2bae2f",
		        
		        "1"=>"e9d149",
		        "2"=>"e9d149",
		        "3"=>"e9d149",
		        "4"=>"e9d149",
		        
		        "5"=>"ffa500",
		        "6"=>"ffa500",
		        "7"=>"ffa500",
		        
		        "8"=>"dc4646",
		        "9"=>"dc4646",
		        "10"=>"dc4646",
		    );
		    
		    if($sym)
		    {
		        $symset = new SymptomatologyValues();
		        $sympsetdet = $symset->getSymptpomatologyValues($setid);

		        // add symptom names and colors
		        foreach($sym as $key => $symp)
		        {
		            $sym[$key]['symptom_name'] = $sympsetdet[$symp['symptomid']]['sym_description'];
		            $sym[$key]['symptom_value_color'] = $color_mapping[$symp['input_value']];
		        }

		        return $sym;
		    }
		}
		
	}

?>