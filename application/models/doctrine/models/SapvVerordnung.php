<?php

	Doctrine_Manager::getInstance()->bindComponent('SapvVerordnung', 'IDAT');

	class SapvVerordnung extends BaseSapvVerordnung {

	    /**
	     * translations are grouped into an array
	     * @var unknown
	     */
	    const LANGUAGE_ARRAY    = 'SapvVerordnung_lang';
	    
	    
	    public static function getDefaultStatusColors() {
           return array(
	        //status => color
	        "1" => "red",
	        "2" => "green",
	        "3" => "#C0C0C0",
           );
	    }
	    /*
	     * formatter, NOT getter from db
	     * explode the $verordnet and return-it as TV,VV
	     */
	    public static function getVerordnetAsShorttext ($verordnet = null) {
	        
	        if (empty($verordnet)) return;
	        
	        $short = func_num_args() == 2 ? func_get_arg(1) : true;
	        $statuses = Pms_CommonData::getSapvCheckBox($short);
	        
	        $verordnet_arr= array(); //result

	        $verordnet = explode(',', $verordnet);
	        
	        foreach ($verordnet as $one) {
	           array_push($verordnet_arr, $statuses[$one]);
	        }
	        
	        return implode(', ', $verordnet_arr);
	    }
	    /*
	     * formatter, NOT getter from db
	     * explode the $verordnet and return-it as Beratung, Koordination
	     */
	    public static function getVerordnetAsLongtext ($verordnet = null) {
	        return self::getVerordnetAsShorttext($verordnet, false);
	    }
	    
	    
	    public static function getSapvRadios()
	    {
	        return array(
	            '1' => self::translate('abgelehnt'),
	            '2' => self::translate('genehmigt'),
	            '3' => self::translate('keineAngabe'),
	        );
	    }
	    
	    public static function getSapvExtraStatusesRadios()
	    {
            //ISPC-2539, elena, 23.10.2020
	        $logininfo = new Zend_Session_Namespace('Login_Info');
            $retValue = null;


	        $aDefault = array(
	            '0' => self::translate('empty_option_'),
	            '1' => self::translate('missing'),
	            '2' => self::translate('is ordered'),
	            '3' => self::translate('ordered but not sent'),
	            '4' => self::translate('sent as fax'),
	            '5' => self::translate('original is available'),
	        );
            if(!$logininfo->clientid){
                $retValue = $aDefault;
            }else{
                $strVerordnungOptions  = ClientConfig::getConfig($logininfo->clientid, 'verordnungoptions');
                try{
                    $aOpts = json_decode($strVerordnungOptions);

                   if(is_array($aOpts)){
                    $retValue['0'] = self::translate('empty_option_');
                    $counter = 1;
                    foreach($aOpts as $opt){
                        $retValue[strval($counter)] = $opt->name;
                        $counter++;
                    }
                   }else{
                       $retValue = $aDefault;
                   }
                }catch(Exception $e){

                    $retValue = $aDefault;
                }

            }

            return $retValue;


	    }


        /**
         * ISPC-2539, elena, 26.10.2020
         * returns statuses colors for client, with fallback on default options
         *
         * @return array
         */
	    public function getSapvExtraStatusesColor(){
            //ISPC-2539, elena, 26.10.2020
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $retValue = null;
            //ISPC-2539, elena, 05.11.2020 - fix no client settings
            $aDefault = array(
                '0' => 'red',
                '1' => 'red',
                '2' => 'red',
                '3' => 'red',
                '4' => 'red',
                '5' => 'green',
            );

            /*
             * ISPC-2539, elena, 05.11.2020 - fix no client settings
            $aDefault = array(
                '0' => self::translate('empty_option_'),
                '1' => self::translate('missing'),
                '2' => self::translate('is ordered'),
                '3' => self::translate('ordered but not sent'),
                '4' => self::translate('sent as fax'),
                '5' => self::translate('original is available'),
            );*/
            if(!$logininfo->clientid){
                $retValue = $aDefault;
            }else{
                $strVerordnungOptions  = ClientConfig::getConfig($logininfo->clientid, 'verordnungoptions');
                try{
                    $aOpts = json_decode($strVerordnungOptions);
                    //ISPC-2539, elena, 05.11.2020 - fix no client settings
                    if(!is_array($aOpts) || count($aOpts) == 0){
                        //there are no client settings, fallback on default
                        return $aDefault;
                    }
                    $retValue['0'] = 'red';
                    $counter = 1;
                    foreach($aOpts as $opt){
                        $retValue[strval($counter)] = $opt->color;
                        $counter++;
                    }
                }catch(Exception $e){
                    $retValue = $aDefault;
                }

            }

            return $retValue;

        }
	    
	    public static function getSapvExtraRadios()
	    {
	        return array(
	            '1' => self::translate('gefaxt'),
	            '2' => self::translate('fehlt'),
	            '3' => self::translate('geschickt'),
	        );
	    
	    }

        /**
         * ISPC-2539, elena, 26.10.2020
         *
         * extends SapvVerordnungData with colors of options
         *
         * @param $sapvarr
         * @return array
         */
	    public function getSapvVerordnungDataWithColors($sapvarr, $extraStatusesRadios = null){
	        $sapvarrExtended = [];
	        if($extraStatusesRadios == null){
                $extraStatusesRadios = $this->getSapvExtraStatusesRadios();
            }

            foreach($sapvarr as $key => $onesapv){
                $onesapv['color_primary'] = $extraStatusesRadios[$onesapv['primary_set']];
                $onesapv['color_secondary'] = $extraStatusesRadios[$onesapv['secondary_set']];
                $sapvarrExtended[] = $onesapv;
            }

            return $sapvarrExtended;

        }

        /**
         * ISPC-2539, elena, 28.10.2020
         *
         * @param $ipid
         * @param $id
         * @param $primary
         * @throws Doctrine_Query_Exception
         */
        public function setPrimarySet($ipid, $id, $primary){
            $update_op = Doctrine_Query::create()
            ->update('SapvVerordnung')


                ->set('primary_set',$primary)
                //->set('change_date', '?',date('Y-m-d H:i:s'))
                //->set('change_user', '?',$userid)
                ->where("ipid = ?", $ipid)
                ->andWhere("id = ?", $id)
                ->execute();
            ;
        }

	     /**
         * ISPC-2539, elena, 28.10.2020
         *
         * @param $ipid
         * @param $id
         * @param $secondary
         * @throws Doctrine_Query_Exception
         */
        public function setSecondarySet($ipid, $id, $secondary){
            $update_op = Doctrine_Query::create()
            ->update('SapvVerordnung')


                ->set('secondary_set',$secondary)
                //->set('change_date', '?',date('Y-m-d H:i:s'))
                //->set('change_user', '?',$userid)
                ->where("ipid = ?", $ipid)
                ->andWhere("id = ?", $id)
                ->execute();
            ;
        }

		public function getSapvVerordnungData($ipids = array())
		{
			//ISPC - 1148
			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			
			if(empty($ipids))
			{
				return;
			}
			
			$drop = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				//->where("ipid =  ? AND isdelete=0" , $ipid)
				->whereIn('ipid', $ipids)
				->andWhere('isdelete="0"')
				->orderBy("ipid, id")
				->fetchArray();
			
// 			$dropexec = $drop->execute();
			if($drop)
			{
// 				$droparray = $dropexec->toArray();
				return $drop;
			}
		}

		public function getSapvVerordnungDataId($ipid, $id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where("ipid='" . $ipid . "' and isdelete=0 and id ='" . $id . "'")
				->orderBy("id");
			$dropexec = $drop->execute();
			if($dropexec)
			{
				$droparray = $dropexec->toArray();
				return $droparray;
			}
		}

		public function getFirstSapvVerordnungData($ipid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where("ipid='" . $ipid . "'  and isdelete=0")
				->orderBy('id asc')
				->limit(1);
			$dropexec = $drop->execute();

			if($dropexec)
			{
				$droparray = $dropexec->toArray();
				return $droparray;
			}
		}

		public function getLastSapvVerordnungData($ipid, $active_period = false)
		{
			if($active_period === true)
			{
				$active_period_sql = "AND verordnungbis >='" . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y'))) . " 00:00:00'";
			}
			else
			{
				$active_period_sql = '';
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where("ipid='" . $ipid . "'  and isdelete=0 " . $active_period_sql . " ")
				->orderBy('id desc')
				->limit(1);
			$dropexec = $drop->execute();

			if($dropexec)
			{
				$droparray = $dropexec->toArray();
				return $droparray;
			}
		}

		public function getLastSapvVerordnungsortDate($ipid)
		{
			$conn = Doctrine_Manager::getInstance()->getCurrentConnection('IDAT');
			$q = 'SELECT * FROM `patient_sapvverordnung` pv
					WHERE pv.id = (
					SELECT pv2.id
					FROM `patient_sapvverordnung` pv2
					WHERE pv.ipid = pv2.ipid
					AND pv2.isdelete = 0
					ORDER BY pv2.`verordnungbis` DESC
					LIMIT 1 )
					AND pv.ipid IN (' . $ipid . ')
					AND pv.verordnungam != "1970-01-01 00:00:00"
					AND pv.verordnungbis != "1970-01-01 00:00:00"
					AND pv.verordnungbis != "000-00-00 00:00:00"
					AND pv.verordnungam <= pv.verordnungbis
					AND pv.verordnet != ""
					AND pv.verordnungbis >= "' . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 30, date('Y'))) . ' 00:00:00"
					AND pv.isdelete = 0
					ORDER BY pv.verordnungbis ASC';

			$r = $conn->execute($q)->fetchAll();

			if(count($r) > '0')
			{
				return $r;
			}
			else
			{
				return false;
			}
		}

		public function getlastsapvpopups($ipid, $days = 30)
		{
			$conn = Doctrine_Manager::getInstance()->getCurrentConnection('IDAT');
			$q = 'SELECT * FROM `patient_sapvverordnung` pv
					WHERE pv.id = (
					SELECT pv2.id
					FROM `patient_sapvverordnung` pv2
					WHERE pv.ipid = pv2.ipid
					AND pv2.isdelete = 0
					ORDER BY pv2.`verordnungbis` DESC
					LIMIT 1 )
					AND pv.ipid IN (' . $ipid . ')
					AND pv.verordnungam != "1970-01-01 00:00:00"
					AND pv.verordnungbis != "1970-01-01 00:00:00"
					AND pv.verordnungbis != "000-00-00 00:00:00"
					AND pv.verordnungam <= pv.verordnungbis
					AND pv.verordnet != ""
					AND (pv.verordnungbis >= "' . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $days, date('Y'))) . ' 00:00:00"
						and pv.verordnungbis <= "' . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + $days, date('Y'))) . ' 00:00:00")
					AND pv.isdelete = 0
					ORDER BY pv.verordnungbis ASC';

			$r = $conn->execute($q)->fetchAll();
			if(count($r) > '0')
			{
				return $r;
			}
			else
			{
				return false;
			}
		}

		public function getSapvVerordnungDistinct($ipid)
		{

			$drop = Doctrine_Query::create()
				->select('distinct(verordnet) as verordnet')
				->from('SapvVerordnung')
				->where("ipid='" . $ipid . "'  and isdelete=0");
			$dropexec = $drop->execute();

			if($dropexec)
			{
				$droparray = $dropexec->toArray();
				return $droparray;
			}
		}

		public function getSapvVerordnungById($vid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where("id='" . $vid . "'  and isdelete=0");
			$dropexec = $drop->execute();

			if($dropexec)
			{
				$droparray = $dropexec->toArray();
				return $droparray;
			}
		}

		function getipidfromclientid($clientid)
		{
			//get user's patients by permission
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$user_patients = PatientUsers::getUserPatients($logininfo->userid);
			$lastipid = Doctrine_Query::create()
				->select('e.ipid')
				->from('EpidIpidMapping e')
				->where("e.clientid = " . $clientid);
			$newipidval = $lastipid->getDql();

			$actipid = Doctrine_Query::create()
				->select('p.ipid')
				->from('PatientMaster p')
				->where("p.ipid in (" . $newipidval . ") and p.ipid in (" . $user_patients['patients_str'] . ") and p.isdelete=0 and p.isdischarged=0");
			$actipidarray = $actipid->fetchArray();

			$comma = ",";
			$actipidval = "'0'";
			foreach($actipidarray as $key => $val)
			{
				$actipidval .= $comma . "'" . $val['ipid'] . "'";
				$comma = ",";
			}

			return $actipidval;
		}

		function get_active_ipid_client($clientid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$user_patients = PatientUsers::getUserPatients($logininfo->userid); //get user's patients by permission
			$lastipid = Doctrine_Query::create()
				->select('e.ipid')
				->from('EpidIpidMapping e')
				->where("e.clientid = " . $clientid);
			$newipidval = $lastipid->getDql();

			$actipid = Doctrine_Query::create()
				->select('p.ipid')
				->from('PatientMaster p')
				->where("p.ipid in (" . $newipidval . ") and p.ipid in (" . $user_patients['patients_str'] . ") and p.isdelete=0 and p.isdischarged=0 and p.isstandby=0 and p.isstandbydelete = 0 ");
			$actipidarray = $actipid->fetchArray();

			$comma = ",";
			$actipidval = "'0'";
			foreach($actipidarray as $key => $val)
			{
				$actipidval .= $comma . "'" . $val['ipid'] . "'";
				$comma = ",";
			}
			return $actipidval;
		}
/*
		public static function getSapvRadios()
		{
			$Tr = new Zend_View_Helper_Translate();
			$abgelehnt = $Tr->translate('abgelehnt');
			$genehmigt = $Tr->translate('genehmigt');
			$keineAngabe = $Tr->translate('keineAngabe');

			$verordnetarray = array('1' => $abgelehnt, '2' => $genehmigt, '3' => $keineAngabe);
			return $verordnetarray;
		}
		
		public static function getSapvExtraRadios()
		{
			$Tr = new Zend_View_Helper_Translate();
			$gefaxt = $Tr->translate('gefaxt');
			$fehlt = $Tr->translate('fehlt');
			$geschickt = $Tr->translate('geschickt');

			$verordnetarray = array('1' => $gefaxt, '2' => $fehlt, '3' => $geschickt);
			return $verordnetarray;
		}
		
		public static function getSapvExtraStatusesRadios()
		{
			$Tr = new Zend_View_Helper_Translate();
			$empty = $Tr->translate('empty_option_');
			$missing = $Tr->translate('missing');
			$is_ordered = $Tr->translate('is ordered');
			$ordered_but_not_sent = $Tr->translate('ordered but not sent');
			$sent_as_fax = $Tr->translate('sent as fax');
			$original_is_available = $Tr->translate('original is available');

			$verordnetarray = array(
				'0' => $empty,
				'1' => $missing,
				'2' => $is_ordered,
				'3' => $ordered_but_not_sent,
				'4' => $sent_as_fax,
				'5' => $original_is_available
			);
			return $verordnetarray;
		}
*/
		

		public static function getFormoneFirstSapv($ipid)
		{
			$drop = Doctrine_Query::create()
				->select('*, MIN( id )')
				->from('SapvVerordnung')
				->where("ipid='" . $ipid . "' and isdelete=0")
				->andWhere("status != 1")
				->orderBy("id ASC")
				->limit('1');
			$dropexec = $drop->execute();

			if($dropexec)
			{
				$droparray = $dropexec->toArray();

				return $droparray;
			}
		}

		public static function getFormoneAllSapv($ipid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where("ipid='" . $ipid . "' and isdelete=0")
				->andWhere("status != 1")
				->orderBy("id ASC");
			$dropexec = $drop->execute();

			if($dropexec)
			{
				$droparray = $dropexec->toArray();

				return $droparray;
			}
		}

		public function getPatientsSapvVerordnungDetails($ipids, $all_data = false, $active_sapv = false)
		{
			if( empty($ipids)){
				return false;
			}
			
			foreach($ipids as $ipid)
			{
				$ipid_str .= '"' . $ipid['ipid'] . '",';
				$ipidz[$ipid['ipid']] = $ipid;

				if($all_data)
				{
					$ipidz_simple[] = $ipid;
				}
				else
				{
					$ipidz_simple[] = $ipid['ipid'];
				}

				if($active_sapv)
				{
					$active_sapv_sql = "AND verordnungbis >='" . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y'))) . " 00:00:00'";
				}
				else
				{
					$active_sapv_sql = '';
				}
			}

			$sapv = Doctrine_Query::create()
				->select("*")
				->from('SapvVerordnung')
				->whereIn('ipid', $ipidz_simple)
				->andWhere('isdelete = 0 ' . $active_sapv_sql . ' ')
				->orderBy('id');
// 			echo $sapv->getSqlQuery(); exit;
			$sapv_data = $sapv->fetchArray();

			foreach($sapv_data as $sapv_item)
			{
				if($all_data)
				{
					$patient_sapvdata[$sapv_item['ipid']][] = $sapv_item;
				}
				else
				{
					$patient_sapvdata[$sapv_item['ipid']]['ipid'] = $sapv_item['ipid'];
					$patient_sapvdata[$sapv_item['ipid']]['verordnet'] = $sapv_item['verordnet'];
					$patient_sapvdata[$sapv_item['ipid']]['status'] = $sapv_item['status'];
				}
			} 
			
			return $patient_sapvdata;
		}

		public function getPatientSapvVerordnungDetails($ipid, $all_data = false, $active_sapv = false)
		{
		    /* ISPC-1775,ISPC-1678 */
		    if($active_sapv)
		    {
		        $active_sapv_sql = "AND verordnungbis >='" . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y'))) . " 00:00:00'";
		    }
		    else
		    {
		        $active_sapv_sql = '';
		    }
		    	
		    $sapv = Doctrine_Query::create()
		    ->select("*")
		    ->from('SapvVerordnung')
		    ->where("ipid='" . $ipid . "'")
		    ->andWhere('isdelete = 0 ' . $active_sapv_sql . ' ')
		    ->orderBy('id');
// 		    			echo $sapv->getSqlQuery(); exit;
		    $sapv_data = $sapv->fetchArray();
		
		    foreach($sapv_data as $sapv_item)
		    {
		        if($all_data)
		        {
		            $patient_sapvdata[$sapv_item['ipid']][] = $sapv_item;
		        }
		        else
		        {
		            $patient_sapvdata[$sapv_item['ipid']]['ipid'] = $sapv_item['ipid'];
		            $patient_sapvdata[$sapv_item['ipid']]['verordnet'] = $sapv_item['verordnet'];
		            $patient_sapvdata[$sapv_item['ipid']]['status'] = $sapv_item['status'];
		        }
		    } return $patient_sapvdata;
		}
		
		
		
		public static function getFormoneAllSapvInPeriods($ipid, $patientCycles)
		{
			//To DO need to be changed in a elegant manner
			if(is_array($patientCycles))
			{
				foreach($patientCycles as $cKey => $cVal)
				{
					$drop = Doctrine_Query::create()
						->select('*')
						->from('SapvVerordnung')
						->where("ipid='" . $ipid . "'")
						->andWhere('isdelete=0');
					$drop = $drop->andWhere("status != 1");

					$sql_period[$cKey] = "(verordnungam BETWEEN '" . date("Y-m-d H:i:s", strtotime($cVal['start'])) . "' AND '" . date("Y-m-d H:i:s", strtotime($cVal['end'])) . "') OR (verordnungbis BETWEEN '" . date("Y-m-d H:i:s", strtotime($cVal['start'])) . "' AND '" . date("Y-m-d H:i:s", strtotime($cVal['end'])) . "') OR (verordnungam <= '" . date("Y-m-d H:i:s", strtotime($cVal['start'])) . "' AND (verordnungbis >= '" . date("Y-m-d H:i:s", strtotime($cVal['end'])) . "' )) OR (verordnungam <= '" . date("Y-m-d H:i:s", strtotime($cVal['start'])) . "' AND (verordnungbis <= '" . date("Y-m-d H:i:s", strtotime($cVal['end'])) . "' )  AND (verordnungbis >= '" . date("Y-m-d H:i:s", strtotime($cVal['start'])) . "' )) OR (verordnungam >= '" . date("Y-m-d H:i:s", strtotime($cVal['start'])) . "' AND verordnungam <= '" . date("Y-m-d H:i:s", strtotime($cVal['end'])) . "' AND (verordnungbis >= '" . date("Y-m-d H:i:s", strtotime($cVal['end'])) . "' ))";
					$drop = $drop->andWhere($sql_period[$cKey]);
					$drop = $drop->orderBy("id ASC");
//					print_r($drop->getSqlQuery().";\n\n");
					$verordnungs[$cKey] = $drop->fetchArray();
				}
			}


			if(count($verordnungs) > 0)
			{
				return $verordnungs;
			}
		}

		public function clone_records($ipid, $target_ipid, $target_client)
		{
			$fam_doc = new FamilyDoctor();
			$spec_doc = new Specialists();
			$loc = new Locations;

			$sapvs = $this->getSapvVerordnungData($ipid);
			if($sapvs)
			{
				foreach($sapvs as $key => $v_sapv)
				{
					if($v_sapv['verordnet_von'] > 0)
					{
						//clone family doctor or specialist too
						if ($v_sapv['verordnet_von_type'] == 'family_doctor') 
						{						
							$doc_id = $fam_doc->clone_record($v_sapv['verordnet_von'], $target_client);
							$doc_type = $v_sapv['verordnet_von_type'];
						}
						else if($v_sapv['verordnet_von_type'] == 'specialists') 
						{
							$doc_id = $spec_doc->clone_record($v_sapv['verordnet_von'], $target_client);
							$doc_type = $v_sapv['verordnet_von_type'];
						}
						else 
						{
							$loc_arr = $loc->getLocationbyId($v_sapv['verordnet_von']);
							$docform = new Application_Form_Familydoctor();
							$doc_arr['doclast_name'] = $loc_arr[0]['location'];
							$doc_arr['indrop'] = 1;
							$doc_id = $docform->InsertData($doc_arr);
							$doc_type = 'family_doctor';
						}
					}
					else
					{
						$doc_id = $v_sapv['verordnet_von'];
					}

					$cust = new SapvVerordnung();
					$cust->ipid = $target_ipid;
					$cust->sapv_order = $v_sapv['sapv_order'];
					$cust->verordnet_von = $doc_id;
					$cust->verordnet_von_type = $doc_type;
					$cust->extra_set = $v_sapv['extra_set'];
					$cust->verordnungam = $v_sapv['verordnungam'];
					$cust->verordnungbis = $v_sapv['verordnungbis'];
					$cust->regulation_start = $v_sapv['regulation_start'];
					$cust->regulation_end = $v_sapv['regulation_end'];
					$cust->verorddisabledate = $v_sapv['verorddisabledate'];
					$cust->approved_date = $v_sapv['approved_date'];
					$cust->approved_number = $v_sapv['approved_number'];
					$cust->verordnet = $v_sapv['verordnet'];
					$cust->status = $v_sapv['status'];
					$cust->after_opposition = $v_sapv['after_opposition'];
					$cust->fromdate = $v_sapv['fromdate'];
					$cust->tilldate = $v_sapv['tilldate'];
					$cust->save();
				}
			}
		}

		public function getSapvInPeriod($ipid, $start, $end)
		{
			$dropSapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('"' . date('Y-m-d', strtotime($start)) . '" <= verordnungbis')
				->andWhere('"' . date('Y-m-d', strtotime($end)) . '" >= verordnungam')
				->andWhere('verordnungam != "0000-00-00 00:00:00"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00"')
				->andWhere('isdelete=0')
				->andWhere('status != 1 ')
				->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();

			if($droparray)
			{
				return $droparray;
			}
			else
			{
				return false;
			}
		}

		public function get_sapvs_in_period($ipids, $start, $end)
		{
			if ( empty($ipids) || ! is_array($ipids)) {
				return false;
			}

			$dropSapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->whereIn('ipid', $ipids)
				->andWhere('"' . date('Y-m-d', strtotime($start)) . '" <= verordnungbis')
				->andWhere('"' . date('Y-m-d', strtotime($end)) . '" >= verordnungam')
				->andWhere('verordnungam != "0000-00-00 00:00:00"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00"')
				->andWhere('isdelete=0')
				->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();

			if($droparray)
			{
				return $droparray;
			}
			else
			{
				return false;
			}
		}

		public function get_today_active_sapvs($ipids)
		{
			if(is_array($ipids))
			{
				$act_ipids = $ipids;
			}
			else
			{
				$act_ipids = array($ipids);
			}

			$sapv = Doctrine_Query::create()
				->select("*")
				->from('SapvVerordnung')
				->whereIn('ipid', $act_ipids)
				->andWhere('isdelete = 0')
				->andWhere(' DATE("' . date('Y-m-d H:i:s', time()) . '") BETWEEN `verordnungam` AND `verordnungbis`')
				->orderBy('verordnungam, verordnungbis ASC');
			$sapv_res = $sapv->fetchArray();

			if($sapv_res)
			{
				return $sapv_res;
			}
			else
			{
				return false;
			}
		}

		public function get_today_active_highest_sapv($ipids, $get_next_sapv = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(is_array($ipids))
			{
				$act_ipids = $ipids;
			}
			else
			{
				$act_ipids = array($ipids);
			}

			$sapv_verordnets = Pms_CommonData::get_sapv_verordnets();

			$sapv_status_array = $this->getSapvRadios();
			$extra_set_array = $this->getSapvExtraRadios();

			$sapv_res = $this->get_today_active_sapvs($act_ipids);

			if($sapv_res)
			{
				$modules = new Modules();
				$sapv_extra = false;
				if($modules->checkModulePrivileges("69", $clientid))
				{
					$sapv_extra = true;
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

//							$sapv_data['ipids'][] = $v_sapv['ipid']; //used only in icons
//							$sapv_data['details'][$v_sapv['ipid']][] = $v_sapv;
							$sapv_data['last'][$v_sapv['ipid']] = $v_sapv;
							$sapv_data['last'][$v_sapv['ipid']]['max_verordnet'] = $high_verordnet;
							$sapv_data['last'][$v_sapv['ipid']]['max_verordnet_patientinfo'] = $sapv_verordnets[$high_verordnet];
							$sapv_data['last'][$v_sapv['ipid']]['status'] = $sapv_status;
							$sapv_data['last'][$v_sapv['ipid']]['sapv_status'] = $sapv_status_array[$sapv_status];


							$sapv_extra_set = $extra_set_array[$v_sapv['extra_set']];

							if($sapv_extra === true && $v_sapv['extra_set'] != 0)
							{
								$sapv_data['last'][$v_sapv['ipid']]['sapv_extra_status'] .= " (" . $sapv_extra_set . ")";
							}

							if($get_next_sapv)
							{
								$actual_sapv_data[] = $v_sapv;
							}
						}
					}
				}

				if($get_next_sapv && !empty($actual_sapv_data))
				{
					//prepare actual sapv array to be used in gathering next sapvs
					$next_sapv_data = $this->get_next_sapvs($actual_sapv_data);
					if($next_sapv_data)
					{
						$sapv_data['next'] = $next_sapv_data;
					}
				}
				return $sapv_data;
			}
			else
			{
				return false;
			}
		}

		public function get_next_sapvs($actual_sapv_data)
		{
			if(!empty($actual_sapv_data))
			{
				foreach($actual_sapv_data as $k_sapv_data => $v_sapv_data)
				{
					$sql_parts[] = '((ipid LIKE "' . $v_sapv_data['ipid'] . '" AND verordnungam >= "' . date('Y-m-d', strtotime($v_sapv_data['verordnungbis'])) . '") OR ((ipid LIKE "' . $v_sapv_data['ipid'] . '" AND  verordnungam > "' . date('Y-m-d', strtotime($v_sapv_data['verordnungam'])) . '" AND  verordnungam <= "' . date('Y-m-d', strtotime($v_sapv_data['verordnungbis'])) . '" AND verordnungbis >= "' . date('Y-m-d', strtotime($v_sapv_data['verordnungbis'])) . '")))';
				}
				$sql_next_sapvs = implode(" OR ", $sql_parts);

				$sapv = Doctrine_Query::create()
					->select("*")
					->from('SapvVerordnung')
					->where($sql_next_sapvs)
					->andWhere('isdelete = 0')
					->orderBy('verordnungam, verordnungbis ASC');
				$sapv_res = $sapv->fetchArray();

				if($sapv_res)
				{
					foreach($sapv_res as $k_sapv_data => $v_sapv_data)
					{
						$sapv_res_arr[$v_sapv_data['ipid']] = $v_sapv_data;
					}

					return $sapv_res_arr;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function get_multiple_last_sapvs($ipids, $all_data = false, $active_sapv = false)
		{
			if( empty($ipids) )
			{
				return false;
			}
			
			if(is_array($ipids))
			{
				$pat_ipids = $ipids;
			}
			else
			{
				$pat_ipids = array($ipids);
			}


			if($active_sapv)
			{
				$active_sapv_sql = "AND verordnungbis >='" . date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y'))) . " 00:00:00'";
			}
			else
			{
				$active_sapv_sql = '';
			}

			$sapv = Doctrine_Query::create()
				->select("*")
				->from('SapvVerordnung')
				->whereIn('ipid', $pat_ipids)
				->andWhere('isdelete = 0 ' . $active_sapv_sql . ' ')
				->andWhere('verordnungam != "0000-00-00 00:00:00" AND DATE(verordnungam) != "1970-01-01"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00" AND DATE(verordnungbis) != "1970-01-01"')
				->andWhere('verordnet != ""')
				->orderBy('verordnungbis ASC');
			$sapv_data = $sapv->fetchArray();

			if($sapv_data)
			{
				foreach($sapv_data as $k_sapv => $v_sapv)
				{
					$patient_sapvs[$v_sapv['ipid']][] = $v_sapv;
				}
				foreach($patient_sapvs as $k_sapv_item => $v_sapv_items)
				{
					$last_sapv_item = end($v_sapv_items);

					if($all_data)
					{
						$patient_sapvdata[$last_sapv_item['ipid']][] = $last_sapv_item;
					}
					else
					{
						$patient_sapvdata[$last_sapv_item['ipid']]['ipid'] = $last_sapv_item['ipid'];
						$patient_sapvdata[$last_sapv_item['ipid']]['verordnet'] = $last_sapv_item['verordnet'];
						$patient_sapvdata[$last_sapv_item['ipid']]['status'] = $last_sapv_item['status'];
					}
				}
				return $patient_sapvdata;
			}
			else
			{
				return false;
			}
		}

		public function get_all_sapvs($ipids)
		{
			if(is_array($ipids))
			{
				$act_ipids = $ipids;
			}
			else
			{
				$act_ipids = array($ipids);
			}

			if ( empty($act_ipids)){
				return false;
			}
			$sapv = Doctrine_Query::create()
				->select("*")
				->from('SapvVerordnung')
				->whereIn('ipid', $act_ipids)
				->andWhere('isdelete = 0')
				->andWhere('verordnungam != "0000-00-00 00:00:00" AND DATE(verordnungam) != "1970-01-01"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00" AND DATE(verordnungbis) != "1970-01-01"')
				->andWhere('verordnet != ""')
				->orderBy('verordnungam, verordnungbis ASC');
			$sapv_res = $sapv->fetchArray();


			$sapv_verordnetvon_ids = array();
			foreach($sapv_res as $k_sapv => $v_sapv)
			{
				$sapv_verordnetvon_ids[] = $v_sapv['verordnet_von'];
			}
			$fam_doc = array();
			if ( ! empty($sapv_verordnetvon_ids)) {
				$fam_doc = FamilyDoctor::get_family_doctors_multiple($sapv_verordnetvon_ids);
			}

			foreach($sapv_res as $kk_sapv => $vv_sapv)
			{
				$sapv_res[$kk_sapv]['fam_doc'] = $fam_doc[$vv_sapv['verordnet_von']];
			}

			if($sapv_res)
			{
				return $sapv_res;
			}
			else
			{
				return false;
			}
		}

		public function get_patients_valid_sapv($ipids)
		{
// 			$pm = new PatientMaster();

			if(is_array($ipids))
			{
				$act_ipids = $ipids;
			}
			else
			{
				$act_ipids = array($ipids);
			}

			$sapv = Doctrine_Query::create()
				->select("*")
				->from('SapvVerordnung')
				->whereIn('ipid', $act_ipids)
				->andWhere('isdelete = 0')
				->andWhere('verordnungbis != "0000-00-00 00:00:00"')
				->andWhere('isdelete = "0"')
				->andWhere('verordnungbis >= verordnungam')
				->orderBy('verordnungam, verordnungbis ASC');
			$sapv_res = $sapv->fetchArray();

			if($sapv_res)
			{
				foreach($sapv_res as $k_sapv => $v_sapv)
				{
					if($v_sapv['status'] == "1" &&  ($v_sapv['verorddisabledate'] == '0000-00-00 00:00:00' || $v_sapv['verorddisabledate'] == '1970-01-01 00:00:00') )
					{
					} 
					else
					{
						if($v_sapv['status'] == '1' && $v_sapv['verorddisabledate'] != '0000-00-00 00:00:00' && $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00')
						{
							$v_sapv ['verordnungbis'] = date('Y-m-d', strtotime($v_sapv['verorddisabledate']));
						}
						
						$start_sapv = date('Y-m-d', strtotime($v_sapv['verordnungam']));
						$end_sapv = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
						$verordnet = max(explode(',', $v_sapv['verordnet']));
	
						if(empty($sapv_days[$verordnet]))
						{
							$sapv_days[$verordnet] = array();
						}
// 						$sapv_days[$verordnet] = array_merge($sapv_days[$verordnet], $pm->getDaysInBetween($start_sapv, $end_sapv));
						$sapv_days[$verordnet] = array_merge($sapv_days[$verordnet], PatientMaster::getDaysInBetween($start_sapv, $end_sapv));
					}
					
					
				}

				return $sapv_days;
			}
			else
			{
				return false;
			}
		}

		public function get_patient_first_sapv($ipid)
		{
			$sapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where('ipid="' . $ipid . '"')
				->andWhere('verordnungam != "0000-00-00 00:00:00" AND DATE(verordnungam) != "1970-01-01"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00" AND DATE(verordnungbis) != "1970-01-01"')
				->andWhere('verordnet != ""')
				->andWhere('isdelete="0"')
				->orderBy('verordnungam asc')
				->limit(1);
			$sapv_res = $sapv->fetchArray();

			if($sapv_res)
			{
				return $sapv_res;
			}
			else
			{
				return false;
			}
		}
		// Maria:: Migration ISPC to CISPC 08.08.2020
		public function get_patient_last_sapv($ipid)
		{
		    $sapv = Doctrine_Query::create()
		    ->select('*')
		    ->from('SapvVerordnung')
		    ->where('ipid="' . $ipid . '"')
		    ->andWhere('verordnungam != "0000-00-00 00:00:00" AND DATE(verordnungam) != "1970-01-01"')
		    ->andWhere('verordnungbis != "0000-00-00 00:00:00" AND DATE(verordnungbis) != "1970-01-01"')
		    ->andWhere('verordnet != ""')
		    ->andWhere('isdelete="0"')
		    ->orderBy('verordnungam desc')
		    ->limit(1);
		    $sapv_res = $sapv->fetchArray();
		    
		    if($sapv_res)
		    {
		        return $sapv_res;
		    }
		    else
		    {
		        return false;
		    }
		}
		

		/**
		 * TODO-2626
		 * @auth Lore 31.10.2019
		 * @param unknown $ipid
		 * @param boolean $order
		 * @return array|Doctrine_Collection|boolean
		 */
		public function get_patient_first_last_sapv($ipid, $order = false)
		{
		    
			$sapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where('ipid= ? ',$ipid)
				->andWhere('verordnungam != "0000-00-00 00:00:00" AND DATE(verordnungam) != "1970-01-01"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00" AND DATE(verordnungbis) != "1970-01-01"')
				->andWhere('verordnet != ""')
				->andWhere('isdelete="0"');
			    if($order){
				    $sapv->andWhere('sapv_order= ?', $order);
			    }
				$sapv->orderBy('verordnungam desc')
				->limit(1);
			$sapv_res = $sapv->fetchArray();

			if($sapv_res)
			{
				return $sapv_res;
			}
			else
			{
				return false;
			}
		}

		//$excluded_sapvs is used to exclude first sapv and get the rest sapvs(following)
		public function get_patient_following_sapvs($ipid, $excluded_sapvs = false)
		{
			$sapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where('ipid="' . $ipid . '"')
				->andWhere('verordnungam != "0000-00-00 00:00:00" AND DATE(verordnungam) != "1970-01-01"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00" AND DATE(verordnungbis) != "1970-01-01"')
				->andWhere('verordnet != ""')
				->andWhere('isdelete="0"')
				->orderBy('verordnungam asc');

			if($excluded_sapvs)
			{
				if(is_array($excluded_sapvs) && !emty($excluded_sapvs))
				{
					$sapv->whereNotIn('id', $excluded_sapvs);
				}
				else if(strlen($excluded_sapvs) > '0' && $excluded_sapvs > '0')
				{
					$sapv->andWhere('id != "' . $excluded_sapvs . '"');
				}
			}

			$sapv_res = $sapv->fetchArray();

			if($sapv_res)
			{
				return $sapv_res;
			}
			else
			{
				return false;
			}
		}

		//ISPC-2312 Ancuta 08.12.2020
		public function get_patients_sapv_periods($ipids, $all_sapv_data = false)
		{
			$all_sapvs = self::get_all_sapvs($ipids);

			$types2shortcut= array (1=>"BE",2=>"KO",3=>"TV",4=>"VV");
			if($all_sapvs)
			{
				foreach($all_sapvs as $k_sapv => $v_sapv)
				{

					if($all_sapv_data)
					{
						$sapv_periods[$v_sapv['ipid']][$v_sapv['id']] = $v_sapv;
					}

					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['sapv_order'] = $v_sapv['sapv_order'];
					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
					
					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['period'] = date('d.m.Y', strtotime($v_sapv['verordnungam'])).' - '.date('d.m.Y', strtotime($v_sapv['verordnungbis']));
					
					if($v_sapv['regulation_start'] != "0000-00-00 00:00:00"){
    					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['regulation_start'] = date('d.m.Y', strtotime($v_sapv['regulation_start']));
					} else {
    					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['regulation_start'] = date('d.m.Y', strtotime($v_sapv['verordnungam']));
					}
					
					if($v_sapv['regulation_end'] != "0000-00-00 00:00:00"){
    					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['regulation_end'] = date('d.m.Y', strtotime($v_sapv['regulation_end']));
					} else {
    					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['regulation_end'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
					}

					if($v_sapv['status'] == '1' && strtotime($v_sapv['verorddisabledate']) < strtotime($v_sapv['verordnungbis']) && $v_sapv['verorddisabledate'] != '0000-00-00 00:00:00' && $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00')
					{
						$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['disabled'] = date('d.m.Y', strtotime($v_sapv['verorddisabledate']));
						//rewrite end sapv with new disabled date
						$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['end_disabled'] = date('d.m.Y', strtotime($v_sapv['verordnungbis']));
						$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['end'] = date('d.m.Y', strtotime($v_sapv['verorddisabledate']));
					}
					
					$temp_sapv_verordnet[$v_sapv['ipid']] = explode(',', $v_sapv['verordnet']);
					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['types_str'] = $v_sapv['verordnet'];
					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['types_arr'] = explode(',', $v_sapv['verordnet']);
					foreach($sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['types_arr'] as $k=>$type_id){
					    $sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['sh_types_arr'][] = $types2shortcut[$type_id];
					}
					
					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['highest'] = max($temp_sapv_verordnet[$v_sapv['ipid']]);
					
					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['days'] = PatientMaster::getDaysInBetween($sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['start'], $sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['end']);
					array_walk($sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['days'], function(&$value) {
						$value = date('d.m.Y', strtotime($value));
					});
					
					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['status'] = $v_sapv['status'];
					if($v_sapv['status'] == '1'){
					    if(strtotime($v_sapv['verorddisabledate']) < strtotime($v_sapv['verordnungbis']) && $v_sapv['verorddisabledate'] != '0000-00-00 00:00:00' && $v_sapv['verorddisabledate'] != '1970-01-01 00:00:00'){
        					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['status_denied'] = "partially";
					    } else{
        					$sapv_periods[$v_sapv['ipid']][$v_sapv['id']]['status_denied'] = "in_full";
					    }
					}
				}

				return $sapv_periods;
			}
			else
			{
				return false;
			}
		}

//		$data[0] = array('ipid','start_period','end_period');
		public function get_multiple_last_sapvs_inperiod($data, $all_data = false, $multiple = false)
		{
			foreach($data as $k_data => $v_data)
			{
				$sql_data[] = "(`ipid` LIKE '" . $v_data['ipid'] . "' AND (`verordnungam` BETWEEN '" . $v_data['start_period'] . "' AND '" . $v_data['end_period'] . "' OR `verordnungbis` BETWEEN '" . $v_data['start_period'] . "' AND '" . $v_data['end_period'] . "') )";
			}

			$sql_str = implode(' OR ', $sql_data);

			$sapv = Doctrine_Query::create()
				->select("*")
				->from('SapvVerordnung')
				->where('isdelete = 0')
				->andWhere('verordnungam != "0000-00-00 00:00:00" AND DATE(verordnungam) != "1970-01-01"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00" AND DATE(verordnungbis) != "1970-01-01"')
				->andWhere('verordnet != ""')
				->orderBy('verordnungbis ASC');
			if($sql_data)
			{
				$sapv->andWhere($sql_str);
			}
			$sapv_data = $sapv->fetchArray();


			if($sapv_data)
			{
				foreach($sapv_data as $k_sapv => $v_sapv)
				{
					$patient_sapvs[$v_sapv['ipid']][] = $v_sapv;
				}

				foreach($patient_sapvs as $k_sapv_item => $v_sapv_items)
				{
					$last_sapv_item = end($v_sapv_items);

					if($all_data)
					{
						if($multiple)
						{
							$patient_sapvdata[$last_sapv_item['ipid']] = $v_sapv_items;
						}
						else
						{
							$patient_sapvdata[$last_sapv_item['ipid']] = $last_sapv_item;
						}
					}
					else
					{
						$patient_sapvdata[$last_sapv_item['ipid']]['id'] = $last_sapv_item['id'];
						$patient_sapvdata[$last_sapv_item['ipid']]['ipid'] = $last_sapv_item['ipid'];
						$patient_sapvdata[$last_sapv_item['ipid']]['verordnet'] = $last_sapv_item['verordnet'];
						$patient_sapvdata[$last_sapv_item['ipid']]['status'] = $last_sapv_item['status'];
					}
				}

				return $patient_sapvdata;
			}
			else
			{
				return false;
			}
		}

		//BAY INVOICE Tagepauschale helpers
		public function get_multi_high_sapv($ipids, $day2verordnet = false)
		{
			if( empty($ipids)){
				return array();
			}
			
			$patientmaster = new PatientMaster();

			$dropSapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->whereIn('ipid', $ipids)
//				->andWhere('verordnungbis >= "' . date('Y-m-d', strtotime($current_period['start'])) . '"')
//				->andWhere('verordnungam <= "' . date('Y-m-d', strtotime($current_period['end'])) . '"')
				->andWhere('verordnungam != "0000-00-00 00:00:00"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00"')
				->andWhere('isdelete=0')
				->andWhere('verordnet!=""')
				->andWhere('status != 1 ')
				->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();

			$all_sapv_days = array();
			$temp_sapv_days = array();

			foreach($droparray as $k_sapv => $v_sapv)
			{
				$s_start[$v_sapv['ipid']] = date('Y-m-d', strtotime($v_sapv['verordnungam']));
				$s_end[$v_sapv['ipid']] = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
				$temp_sapv_days[$v_sapv['ipid']] = $patientmaster->getDaysInBetween($s_start[$v_sapv['ipid']], $s_end[$v_sapv['ipid']]);

				foreach($temp_sapv_days[$v_sapv['ipid']] as $k_tsapv => $v_tsapv)
				{
					$temp_sapv_verordnet[$v_sapv['ipid']] = explode(',', $v_sapv['verordnet']);

					if(empty($all_sapv_days[$v_sapv['ipid']][$v_tsapv]))
					{
						$all_sapv_days[$v_sapv['ipid']][$v_tsapv] = array();
					}
					$all_sapv_days[$v_sapv['ipid']][$v_tsapv] = array_merge_recursive($all_sapv_days[$v_sapv['ipid']][$v_tsapv], $temp_sapv_verordnet[$v_sapv['ipid']]);

					$all_sapv_days[$v_sapv['ipid']][$v_tsapv] = array_values(array_unique($all_sapv_days[$v_sapv['ipid']][$v_tsapv]));
					asort($all_sapv_days[$v_sapv['ipid']][$v_tsapv]);

					//get KO verordnets
					if(end($all_sapv_days[$v_sapv['ipid']][$v_tsapv]) == '2' && $day2verordnet)
					{
						$all_sapv_days[$v_sapv['ipid']]['KOverordnets'][$v_tsapv] = $v_sapv['id'];
					}
				}
			}

			return $all_sapv_days;
		}

		public function get_multi_high_sapv_period_denied($ipids)
		{
			$patientmaster = new PatientMaster();

			$dropSapv = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->whereIn('ipid', $ipids)
//				->andWhere('verordnungbis >= "' . date('Y-m-d', strtotime($current_period['start'])) . '"')
//				->andWhere('verordnungam <= "' . date('Y-m-d', strtotime($current_period['end'])) . '"')
				->andWhere('verordnungam != "0000-00-00 00:00:00"')
				->andWhere('verordnungbis != "0000-00-00 00:00:00"')
				->andWhere('isdelete=0')
				->andWhere('status = 1')
				->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();

			$denied_sapv_days = array();
			$temp_sapv_days = array();

			foreach($droparray as $k_sapv => $v_sapv)
			{
				$s_start[$v_sapv['ipid']] = date('Y-m-d', strtotime($v_sapv['verordnungam']));

				if($v_sapv['verorddisabledate'] != '0000-00-00 00:00:00' && date('Y-m-d', strtotime($v_sapv['verorddisabledate'])) != '1970-01-01')
				{
					$s_end[$v_sapv['ipid']] = date('Y-m-d', strtotime($v_sapv['verorddisabledate']));
				}
				else
				{
					$s_end[$v_sapv['ipid']] = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
				}

				$temp_sapv_days[$v_sapv['ipid']] = $patientmaster->getDaysInBetween($s_start[$v_sapv['ipid']], $s_end[$v_sapv['ipid']]);

				foreach($temp_sapv_days[$v_sapv['ipid']] as $k_tsapv => $v_tsapv)
				{
					$temp_sapv_verordnet[$v_sapv['ipid']] = explode(',', $v_sapv['verordnet']);

					if(empty($denied_sapv_days[$v_sapv['ipid']][$v_tsapv]))
					{
						$denied_sapv_days[$v_sapv['ipid']][$v_tsapv] = array();
					}
					$denied_sapv_days[$v_sapv['ipid']][$v_tsapv] = array_merge_recursive($denied_sapv_days[$v_sapv['ipid']][$v_tsapv], $temp_sapv_verordnet[$v_sapv['ipid']]);

					$denied_sapv_days[$v_sapv['ipid']][$v_tsapv] = array_values(array_unique($denied_sapv_days[$v_sapv['ipid']][$v_tsapv]));
					asort($denied_sapv_days[$v_sapv['ipid']][$v_tsapv]);
				}
			}

			return $denied_sapv_days;
		}
		
		public function get_verordnet_von_old($verordnet_von, $verordnet_von_type = false, $extra=false) //ISPC-1837
		{
			if(!$extra)
			{
				$verordner = "";
			}
			else
			{
				$verordner = array();
			}
				
			if($verordnet_von) {
				if(!$verordnet_von_type || $verordnet_von_type == 'family_doctor')
				{
					$fdoc = new FamilyDoctor();
					$docarray = $fdoc->getFamilyDoc($verordnet_von);
					if(!empty($docarray))
					{
						if(!$extra)
						{
							$verordner = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
						}
						else {
							$verordner['name'] = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
							$verordner['extra'] = $docarray[0]['street1'] . " " . $docarray[0]['street2'] . "\n " . $docarray[0]['zip'] . " " . $docarray[0]['city'] . "\n" . $docarray[0]['phone_practice'];
						}
					}
				}
				else if($verordnet_von_type == 'specialists')
				{
					$spec = new Specialists();
					$docarray = $spec->get_specialist($verordnet_von);
					if(!empty($docarray))
					{
						if(!$extra)
						{
							$verordner = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
						}
						else {
							$verordner['name'] = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
							$verordner['extra'] = $docarray[0]['street1'] . " " . $docarray[0]['street2'] . "\n " . $docarray[0]['zip'] . " " . $docarray[0]['city'] . "\n" . $docarray[0]['phone_practice'];
						}
					}
				}
				else
				{
					$hosploc = new Locations();
					$docarray = $hosploc->getLocationbyId($verordnet_von);
						
					if(!empty($docarray))
					{
						if(!$extra)
						{
							$verordner = $docarray[0]['location'];
						}
						else {
							$verordner = $docarray[0]['location'];
							$verordner['extra'] = $docarray[0]['street'] . "\n " . $docarray[0]['zip'] . " " . $docarray[0]['city'] . "\n" . $docarray[0]['phone1'];
						}
		
					}
				}
			}
				
			return $verordner;
				
		}
		
		public function get_all_sapvs_new($ipids, $clientid, $sepa=",", $int_cond=false, $sv_status=false, $ordered=false, $verordnet=false, $year = false) //ISPC - 1837 //ISPC-2391,Elena,11.01.2021
		{
			
			if(is_array($ipids))
			{
				$act_ipids = $ipids;
			}
			else
			{
				//$act_ipids = array($ipids);
				$actipids = explode($sepa, $ipids);
				foreach($actipids as $act_ipid)
				{
					$act_ipids[] = str_replace("'", "", $act_ipid);
				}
			}
			//var_dump($act_ipids); exit;
			$sapv = Doctrine_Query::create()
			->select("*")
			->from('SapvVerordnung')
			->whereIn('ipid', $act_ipids)
			->andWhere('isdelete = 0');
			//ISPC-2391,Elena,11.01.2021
            //TODO-3994,Elena,26.03.2021 test
            $sapv->andWhere('verordnungam != "0000-00-00 00:00:00" AND DATE(verordnungam) != "1970-01-01"');
            $sapv->andWhere('verordnungbis != "0000-00-00 00:00:00" AND DATE(verordnungbis) != "1970-01-01"');

			 if($year){
                 $sapv->andWhere('YEAR(verordnungam)=?', $year);
             }



			//ISPC -2101 - add sapv order
			$sapv_res_notordered = $sapv->fetchArray();
			
			$sapv_notordered_arr = array();
			foreach($sapv_res_notordered as $kspvno=>$vsapvno)
			{
				$sapv_notordered_arr[$vsapvno['ipid']][] = $vsapvno;
			}
			
			foreach($sapv_notordered_arr as $kspno=>$vspno)
			{
				foreach($vspno as $keysp=>$vsp)
				{
					if($keysp == 0)
					{
						if($vsp['sapv_order'] == '0' || $vsp['sapv_order'] == '1')
						{
							$sapv_order_name[$vsp['id']]['sapv_order_name'] = 'Erstverordnung';
						}
						else 
						{
							$sapv_order_name[$vsp['id']]['sapv_order_name'] = 'Folgeverordnung';
						}
					}
					else 
					{
						if($vsp['sapv_order'] == '0')
						{
							$sapv_order_name[$vsp['id']]['sapv_order_name'] = 'Folgeverordnung';
						}
						else 
						{
							if($vsp['sapv_order'] == '1')
							{
								$sapv_order_name[$vsp['id']]['sapv_order_name'] = 'Erstverordnung';
							}
							else 
							{
								$sapv_order_name[$vsp['id']]['sapv_order_name'] = 'Folgeverordnung';
							}
						}
					}
				}
			}
			//var_dump($sapv_order_name); exit;
			//ISPC -2101 - add sapv order
			if($int_cond)
			{
				$sapv->andWhere($int_cond);			
			}
			if($sv_status)
			{
				$sapv->andWhere($sv_status);
			}
			if($verordnet) 
			{
				$sapv->andWhere($verordnet);
			}
			if($ordered)
			{
				$sapv->orderBy($ordered);
			}
			$sapv_res = $sapv->fetchArray();
		
		//var_dump($sapv_res); exit;
			$client_family_doctors = FamilyDoctor::client_family_doctors($clientid);
			$spec = Doctrine_Query::create()
			->select('*')
			->from('Specialists')
			->where("clientid= '" . $clientid . "' ");
			$droparray = $spec->fetchArray();
			
			if($droparray)
			{
				foreach($droparray as $drop_item)
				{
					$client_specialists[$drop_item['id']] = $drop_item;
				}
			}
			$hosploc = Doctrine_Query::create()
			->select('*, AES_DECRYPT(location,"' . Zend_Registry::get('salt') . '") as location')
			->from('Locations')
			->where("client_id= '" . $clientid . "' ")
			->andWhere('location_type = 7 or location_type=1');;
			$droparray = $hosploc->fetchArray();

			if($droparray)
			{
				foreach($droparray as $drop_item)
				{
					$client_hosplocations[$drop_item['id']] = $drop_item;
				}
			}
		
			foreach($sapv_res as $kk_sapv => $vv_sapv)
			{
				if($vv_sapv['verordnet_von_type'] == 'family_doctor')
				{
					$sapv_res[$kk_sapv]['verordner_type'] =  "Hausarzt";//ISPC-2105
					$sapv_res[$kk_sapv]['verordner'] = $client_family_doctors[$vv_sapv['verordnet_von']]['last_name'];
					if(strlen($client_family_doctors[$vv_sapv['verordnet_von']]['first_name'])>0)
					{
						$sapv_res[$kk_sapv]['verordner'] .=  ", ".$client_family_doctors[$vv_sapv['verordnet_von']]['first_name'];
					}
				}
				else if($vv_sapv['verordnet_von_type'] == 'specialists')
				{
					$sapv_res[$kk_sapv]['verordner_type'] =  "Facharzt";//ISPC-2105
					$sapv_res[$kk_sapv]['verordner'] = $client_specialists[$vv_sapv['verordnet_von']]['last_name'];
					if(strlen($client_specialists[$vv_sapv['verordnet_von']]['first_name'])>0)
					{
						$sapv_res[$kk_sapv]['verordner'] .=  ", ".$client_specialists[$vv_sapv['verordnet_von']]['first_name'];
					}
				}
				else
				{
					$sapv_res[$kk_sapv]['verordner_type'] = "Aufenthaltsort";//ISPC-2105
					$sapv_res[$kk_sapv]['verordner'] = $client_hosplocations[$vv_sapv['verordnet_von']]['location'];
				}
				$sapv_res[$kk_sapv]['sapv_order_name'] = $sapv_order_name[$vv_sapv['id']]['sapv_order_name'];
			}
			//var_dump($sapv_res); exit;
			if($sapv_res)
			{
				return $sapv_res;
			}
			else
			{
				return false;
			}
		}

		
		//fn created for TODO-1035 used only on rpassessment !(*)
		public function get_patient_sapv_order_first($ipid)
		{
			$sapv = Doctrine_Query::create()
			->select('id, sapv_order, verordnungam, verordnungbis, verordnet')
			->from('SapvVerordnung')
			->where('ipid = ? ', $ipid )
			->andWhere('verordnungam != "0000-00-00 00:00:00" AND DATE(verordnungam) != "1970-01-01"')
			->andWhere('verordnungbis != "0000-00-00 00:00:00" AND DATE(verordnungbis) != "1970-01-01"')
			->andWhere('verordnet != ""')
			->andWhere('isdelete="0"')
			->orderBy('FIELD(sapv_order, 1, 0 ,2) , verordnungam DESC')
			->limit(1)
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);

			return $sapv;
		}
		//fn created for TODO-1035 used only on rpassessment !(*)
		public function get_patient_sapv_order_last($ipid, $used_sapv_id = '0')
		{
			$sapv = Doctrine_Query::create()
			->select('id, sapv_order, verordnungam, verordnungbis, verordnet')
			->from('SapvVerordnung')
			->where('ipid= ?' , $ipid)
			->andWhere('verordnungam != "0000-00-00 00:00:00" AND DATE(verordnungam) != "1970-01-01"')
			->andWhere('verordnungbis != "0000-00-00 00:00:00" AND DATE(verordnungbis) != "1970-01-01"')
			->andWhere('verordnet != ""')
			->andWhere('isdelete="0"')
			->andWhere('id != ?',$used_sapv_id)
			->orderBy('FIELD(sapv_order, 2, 0 ,1) , verordnungam DESC')
			->limit(1)
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
			
			return $sapv;
		}
		
		
		
		
		
		
		/**
		 * TODO-1466
		 * @param unknown $ipids
		 * @param string $current_period
		 * @param unknown $hospital_days
		 * @return string
		 */
		
		public function get_period_sapvs($ipids, $current_period = false, $hospital_days)
		{
			$patientmaster = new PatientMaster();
			if(count($hospital_days) == 0)
			{
				$hospital_days[] = '999999999999';
			}
		
			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr[] = $ipids;
			}
		
			if($current_period)
			{
				foreach($current_period as $k_ipid => $v_current_per_data)
				{
					$sql_where[] = '(`ipid` LIKE "' . $k_ipid . '" AND (DATE(`verordnungbis`) >= "' . date('Y-m-d', strtotime($v_current_per_data['start'])) . '" AND DATE(`verordnungam`) <= "' . date('Y-m-d', strtotime($v_current_per_data['end'])) . '")) ';
					$period_days[$k_ipid] = $patientmaster->getDaysInBetween($v_current_per_data['start'], $v_current_per_data['end']);
				}
			}
		
			$dropSapv = Doctrine_Query::create()
			->select('*')
			->from('SapvVerordnung')
			->where('status != "1"')
			->andWhere(implode(" OR ", $sql_where))
			->andWhere('verordnungam != "0000-00-00 00:00:00"')
			->andWhere('verordnungbis != "0000-00-00 00:00:00"')
			->andWhere('isdelete=0')
			->orderBy('verordnungam ASC');
			$droparray = $dropSapv->fetchArray();
		
			$all_sapv_days = array();
			$temp_sapv_days = array();
		
			foreach($droparray as $k_sapv => $v_sapv)
			{
		
				$s_start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
				$s_end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		
				$temp_sapv_days = $patientmaster->getDaysInBetween($s_start, $s_end);
		
				$relevant_sapvs_days[$v_sapv['ipid']]['start'][] = $s_start;
				$relevant_sapvs_days[$v_sapv['ipid']]['end'][] = $s_end;
		
		
				if($v_sapv['approved_date'] != "0000-00-00 00:00:00")
				{
					$sapvs_details[$v_sapv['id']]['approved_date'] = date('d.m.Y', strtotime($v_sapv['approved_date']));
				}
		
				if(strlen(trim(rtrim($v_sapv['approved_number']))) > '0')
				{
						
					$sapvs_details[$v_sapv['id']]['approved_number'] = $v_sapv['approved_number'];
				}
		
				foreach($temp_sapv_days as $k_tsapv => $v_tsapv)
				{
					if(in_array($v_tsapv, $period_days[$v_sapv['ipid']]) && !in_array($v_tsapv, $hospital_days[$v_sapv['ipid']]))
					{
						$temp_sapv_verordnet[$v_sapv['ipid']] = explode(',', $v_sapv['verordnet']);
		
						if(empty($all_sapv_days[$v_sapv['ipid']][$v_tsapv]))
						{
							$all_sapv_days[$v_sapv['ipid']][$v_tsapv] = array();
						}
		
						$all_sapv_days[$v_sapv['ipid']][$v_tsapv] = array_merge_recursive($all_sapv_days[$v_sapv['ipid']][$v_tsapv], $temp_sapv_verordnet[$v_sapv['ipid']]);
						$all_sapv_days[$v_sapv['ipid']][$v_tsapv] = array_values(array_unique($all_sapv_days[$v_sapv['ipid']][$v_tsapv]));
					}
				}
			}
		
			foreach($all_sapv_days as $k_ipid => $v_sapv_days)
			{
				foreach($v_sapv_days as $k_s_day => $v_s_day)
				{
					if(in_array($k_s_day, $period_days[$k_ipid]) && !in_array($k_s_day, $hospital_days[$k_ipid]))
					{
						$all_sapv_days_arr[$k_ipid][$k_s_day] = $v_s_day;
					}
					$all_sapv_days_arr[$k_ipid]['relevant_sapvs_days'] = $relevant_sapvs_days[$k_ipid];
				}
			}
				
			$all_sapv_days_arr[$v_sapv['ipid']]['sapv_details'][$v_sapv['id']] = $sapvs_details[$v_sapv['id']];
		
			return $all_sapv_days_arr;
		}
		
		
		
	
	
	
	/**
	 * taken from PatientControll -> patientdetailsAction
	 * 
	 * used in PatientMaster->getMasterData_extradata()
	 * 
	 * @param string $ipid
	 * @return Ambigous <multitype:, Doctrine_Collection>
	 */
	public function fetch_SapvVerordnung($ipid = '')
	{
	    if (empty($ipid)) {
	        return; //fail-safe
	    }
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid; 
	    
	    $sapv_perm = new SapvverordnungPermissions();
	    $clientsapv_subdivision = $sapv_perm->getClientSapvverordnungpermissions($clientid);
	    
	    $sapv_subdivizions = [];
	    
	    if ($clientsapv_subdivision) {
	        foreach ($clientsapv_subdivision as $kh => $sub) {
	            $sapv_subdivizions[] = $sub['subdiv_id'];
	        }
	    } else {
	        $sapv_subdivizions[] = '1'; // set DEFAULT sapv box
	    }
	    
	    $divisions = []; 
	    
	    if (in_array(1, $sapv_subdivizions)) {
	        
	        $data = $this->_fetch_SAPV_division($ipid);
	        
	        if (empty($data)) {
	             
	            //no data yet...
	            $row = [];
	            $row['__division'] = 1;
	            $row['__division_legend'] = 'SAPV';
	            $divisions[] = $row;
	             
	        } else {
	            
    	        foreach( $data as $row) {
    	            
    	            $row['__division'] = 1; 
    	            $row['__division_legend'] = 'SAPV';
    	             
    	            $divisions[] = $row;
    	        }
	        }
	    }
	    
	    
	    
	    if (in_array(2, $sapv_subdivizions)) {
	        
	        $data = $this->_fetch_SGBV_division($ipid);
	        
	        if (empty($data['patient_sgbv'])) {
	            
	            //no data yet...
	            $row = [];
	            $row['__division'] = 2;
	            $row['__division_legend'] = 'SGBV';
	            $divisions[] = $row;
	            
	        } else {
    	        
    	        foreach( $data['patient_sgbv'] as $row) {
    
    	            
    	            $row['__division'] = 2;
    	            $row['__division_legend'] = 'SGBV';
    	            
    	            $row['patient_sgbv_actions'] =  $data['patient_sgbv_actions'][$row['id']] ;
    	            $row['patient_sgbv_actions_foc'] =  $data['patient_sgbv_actions_foc'][$row['id']] ;
    	            $row['sgbv_status'] =  $data['sgbv_status'];
    	            
    	            $divisions[] = $row;
    	        }
	        }
	    }
	    
	    
	    //3 this is harcoded with a simple text
	    if (in_array(3, $sapv_subdivizions)) {
	        
	        $divisions[] = [
	            '__division' => 3,
	            '__division_legend' => 'Pflegevertrag nach SGBXI',
	        ];
	    }
	    
	    
	    //4 this is harcoded with a simple text
	    if (in_array(4, $sapv_subdivizions)) {
	        
	        $divisions[] = [
	            '__division' => 4,
	            '__division_legend' => 'Überweisungsschein für ärztliche Leistung',
	        ];
	    }
	    
	    if (in_array(5, $sapv_subdivizions)) {
	        
	        $data = $this->_fetch_Pflegebesuche_division($ipid);
	        $data['__division'] = 5;
	        $data['__division_legend'] = 'Pflegebesuche';
	        
	        $divisions[] = $data;
	    }
	    
	    
	    
	    return $divisions;
	    
	}
	

	
	private function _fetch_SGBV_division($ipid = '') 
	{
	    if (empty($ipid)) {
	        return; //fail-safe
	    }
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $sgbv_details = new SgbvForms();
		$patient_sgbv_array = $sgbv_details->getallPatientSgbvForm($ipid);
		$sgbv_status = Pms_CommonData::getSgbvStatusRadio();
		$pdata['sgbv_status'] = $sgbv_status;

		$patient_sgbv_ids[] = '999999999';
		foreach($patient_sgbv_array as $sk => $sgbvvalues)
		{
			$patient_sgbv[$sgbvvalues['id']] = $sgbvvalues;
			$patient_sgbv_ids[] = $sgbvvalues['id'];
		}

		$sgbv_items = new SgbvFormsItems();
		$sgbv_actions = $sgbv_items->getPatientSgbvFormItems($ipid, $patient_sgbv_ids);

		$social_code_actions = new SocialCodeActions();
		$sc_actions = $social_code_actions->getAllCientSgbvActions($clientid);
		foreach($sc_actions as $k_action => $v_action)
		{
			$client_sc_actions[$v_action['id']] = $v_action;
		}

		foreach($sgbv_actions as $k_sgbv_act => $v_sgbv_act)
		{
			if($client_sc_actions[$v_sgbv_act['action_id']]['custom'] == '1' && $client_sc_actions[$v_sgbv_act['action_id']]['parent'] != '0')
			{
				$sgbv_act_details[$v_sgbv_act['sgbv_form_id']][$v_sgbv_act['action_id']] = $client_sc_actions[$client_sc_actions[$v_sgbv_act['action_id']]['parent']]['action_name'];
			}
			else
			{
				$sgbv_act_details[$v_sgbv_act['sgbv_form_id']][$v_sgbv_act['action_id']] = $client_sc_actions[$v_sgbv_act['action_id']]['action_name'];
			}
			$sgbv_act_details_free[$v_sgbv_act['sgbv_form_id']][$v_sgbv_act['action_id']] = $v_sgbv_act['free_of_charge'];
		}

		$pdata['patient_sgbv'] = $patient_sgbv;
		$pdata['patient_sgbv_actions'] = $sgbv_act_details;
		$pdata['patient_sgbv_actions_foc'] = $sgbv_act_details_free;
		
		return $pdata;
			
	}
	
	private function _fetch_SAPV_division($ipid = '')
	{
	    if (empty($ipid)) {
	        return; //fail-safe
	    }

	    $sapv_primary_status = Modules::checkModulePrivileges(70) ? true : false;// primary status : Verordnungs
	    $sapv_secondary_status = Modules::checkModulePrivileges(71) ? true : false;// secondary status : Verordnung 2nd Page
	    $sapv_bra_options = Modules::checkModulePrivileges(97) == true ? true : false;// secondary status : Verordnung 2nd Page
	
	    $saved = $this->getSapvVerordnungData($ipid);

	    $extraradioarr = $this->getSapvExtraStatusesRadios();
	     
	    $statuscolorsarray = $this->getDefaultStatusColors();
	    //ISPC-2539, elena, 26.10.2020
        $extraStatusesRadios = $this->getSapvExtraStatusesRadios();
        $extraStatusesColor = $this->getSapvExtraStatusesColor();
	    // 	    dd($extraradioarr);
	
	    if ( ! empty($saved)) {
	
	        array_multisort(array_column($saved, 'verordnungam'), SORT_ASC, $saved);
	
	        foreach ($saved as $keys => &$row) {
	
	            $row['verordnet_von_nice_name'] = $this->get_verordnet_von($row['verordnet_von'], $row['verordnet_von_type']);
	
	            if ($keys == '0') {
	                if($row['sapv_order'] == '1') {
	                    $row['sapv_order_name'] = 'Erstverordnung';
	                } elseif($row['sapv_order'] == '2') {
	                    $row['sapv_order_name'] = 'Folgeverordnung';
	                } else {
	                    $row['sapv_order_name'] = 'Erstverordnung';
	                }
	            } else {
	                if ($row['sapv_order'] == '1') {
	                    $row['sapv_order_name'] = 'Erstverordnung';
	                } elseif($row['sapv_order'] == '2') {
	                    $row['sapv_order_name'] = 'Folgeverordnung';
	                } else {
	                    $row['sapv_order_name'] = 'Folgeverordnung';
	                }
	            }
	
	            $row['verordnet_longtext'] = $this->getVerordnetAsLongtext($row['verordnet']);
	            $row['verordnet_short'] = $this->getVerordnetAsShorttext($row['verordnet']);
	            // 	                dd($row);
	
	            $legend = "";
	            if ($sapv_primary_status) {
	
	                $primary = $this->translate('Primary Status') . ": " . $extraradioarr[$row['primary_set']];
	
	                if(strtolower(trim($extraradioarr[$row['primary_set']])) == 'als original vorhanden') {
	                    $legend .= '<ul class="sapv_primary_green"><li class="hover_pr" title="' . $primary . '"></li></ul>';
	                } else {
                        //ISPC-2539, elena, 26.10.2020
	                    $legend .= '<ul class="sapv_primary_' . $extraStatusesColor[$row['primary_set']] .  '"><li class="hover_pr" title="' . $primary . '"></li></ul>';
	                }
	            }
	
	            if($sapv_secondary_status){
	
	                $secondary = $this->translate('2nd Page') . ": " . $extraradioarr[$row['secondary_set']];
	
	                if($extraradioarr[$row['secondary_set']] == 'als Original vorhanden') {
	                    $legend .= '<ul class="sapv_secondary_green"><li class="hover_sec" title="' . $secondary . '"></li></ul>';
	                } else {
	                    //ISPC-2539, elena, 26.10.2020
	                    $legend .= '<ul class="sapv_secondary_'. $extraStatusesColor[$row['secondary_set']] . '"><li class="hover_sec" title="' . $secondary . '"></li></ul>';
	                }
	            }
	
	            $row['dotsLegend'] = empty($legend) ? " " : $legend;
	
	            $row['bra_options_formated'] = null;
	            if($sapv_bra_options && strlen($row['bra_options']) > 0) {
	                $row['bra_options_formated'] = implode(", ", explode(',', $row['bra_options']));
	            }
	             
	            $row['status_color'] = $statuscolorsarray[$row['status']];
	             
	            // 	            dd($row);
	        }
	    }
	     
	     
	    return $saved;
	}
	
	private function _fetch_Pflegebesuche_division($ipid = '')
	{
	    if (empty($ipid)) {
	        return; //fail-safe
	    }
	     
	    $pavt = new PatientApprovedVisitTypes();
		$pavt_active = $pavt->get_active_patient_approved_visit_type($ipid, true); // ipid, true- check for current day
		$all_pavt = $pavt->get_all_patient_approved_visit_type($ipid);

		foreach($patient_history as $date => $type)
		{
			if($type == 1)
			{
				$admissions_dates[] = $date;
			}
		}

		$pdata['only_default'] = 0;

		if(empty($all_pavt))
		{
			$pavt_active['visit_type'] = $default_pavt;
			$all_pavt[0]['visit_type'] = $default_pavt;
			$all_pavt[0]['start_date'] = date('d.m.Y', strtotime($admissions_dates[0])); // first admission ever
			$default_visit_from = date('d.m.Y', strtotime($admissions_dates[0])); // first admission ever
			$pavt_default = $all_pavt[0];
			$pdata['only_default'] = 1;
		}
		$pdata['pavt_default'] = $pavt_default;
		$pdata['approved_visit_type'] = $pavt_active['visit_type'];
		$pdata['approved_visit_type_history'] = $all_pavt;
		
		return $pdata;
	     
	}
		
	public function getsapvnoinfpopups($ipids, $days = 30)
	{
		//ISPC - 2125 - alerts if a verordnung is after XX days still in mode Keine Angabe
		if(is_array($ipids))
		{
			$ipids_arr = $ipids;
		}
		else 
		{
			$ipids_arr = array($ipids);
		}
		
		$comp_date = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $days, date('Y'))) . ' 00:00:00';
		$current_date = date('Y-m-d 00:00:00');
		
		$q = Doctrine_Query::create()
			->select('*')
			->from('SapvVerordnung pv')			
			->where('pv.verordnungam != "1970-01-01 00:00:00"')
			->andWhere('pv.verordnungbis != "1970-01-01 00:00:00"')
			->andWhere('pv.verordnungbis != "000-00-00 00:00:00"')
			->andWhere('pv.verordnungam <= pv.verordnungbis')
			->andWhere('pv.verordnet != ""')
			->andWhere('pv.verordnungam <= ?', $comp_date)
			->andWhere('pv.verordnungbis >= ?', $current_date)
			->andWhere('pv.status = "3"')
			->andWhere('pv.isdelete = 0')
			->andWhereIn('pv.ipid', $ipids_arr)
			->orderBy('pv.ipid asc, pv.verordnungam asc');
		
		$r = $q->fetchArray();
		
		if(count($r) > '0')
		{
			return $r;
		}
		else
		{
			return false;
		}
	}
	
	public function _fetch_multiple_SAPV_division($ipids = array())
	{
		//ISPC - 1148
	    if (empty($ipids)) {
	        return; //fail-safe
	    }
	     
	    $sapv_primary_status = Modules::checkModulePrivileges(70) ? true : false;// primary status : Verordnungs
	    $sapv_secondary_status = Modules::checkModulePrivileges(71) ? true : false;// secondary status : Verordnung 2nd Page
	    $sapv_bra_options = Modules::checkModulePrivileges(97) == true ? true : false;// secondary status : Verordnung 2nd Page
	
	    $saved = $this->getSapvVerordnungData($ipids);

	    $verordnet_von_arr = array();
	    $verordnet_von_data= array();
	   
	    $kipid = "";
	    foreach($saved as $kr=>$vr)
	    {
	    	if(!in_array($vr['verordnet_von'], $verordnet_von_arr))
	    	{
		    	$verordnet_von_arr[] = $vr['verordnet_von'];
		    	$verordnet_von_type_arr[]= $vr['verordnet_von_type'];
	    	}
	    	
	    	if($vr['ipid'] != $kipid)
	    	{
	    		$saved_ipids[$vr['ipid']] = array();
	    		$saved_ipids[$vr['ipid']][] = $vr;
	    		$kipid = $vr['ipid'];
	    	}
	    	else 
	    	{
	    		$saved_ipids[$vr['ipid']][] = $vr;
	    	}
	    	
	    }
	    
	    if(!empty($verordnet_von_arr))
	    {
	   	 $verordnet_von_data = $this->get_verordnet_von($verordnet_von_arr, $verordnet_von_type_arr);
	    }
	    //var_dump($verordnet_von_data); exit;
	    $extraradioarr = $this->getSapvExtraStatusesRadios();
	     
	    $statuscolorsarray = $this->getDefaultStatusColors();
	    // 	    dd($extraradioarr);
        //ISPC-2539, elena, 26.10.2020
        $extraStatusesColor = $this->getSapvExtraStatusesColor();
	
	    if ( ! empty($saved)) {
	
	        foreach ($saved_ipids as $keys => &$row) {
				foreach($row as $kr=>&$vr)
				{
	            	$vr['verordnet_von_nice_name'] = $verordnet_von_data[$vr['verordnet_von']];
	
		            if ($kr == '0') {
		                if($vr['sapv_order'] == '1') {
		                    $vr['sapv_order_name'] = 'Erstverordnung';
		                } elseif($vr['sapv_order'] == '2') {
		                    $vr['sapv_order_name'] = 'Folgeverordnung';
		                } else {
		                    $vr['sapv_order_name'] = 'Erstverordnung';
		                }
		            } else {
		                if ($vr['sapv_order'] == '1') {
		                    $vr['sapv_order_name'] = 'Erstverordnung';
		                } elseif($vr['sapv_order'] == '2') {
		                    $vr['sapv_order_name'] = 'Folgeverordnung';
		                } else {
		                    $vr['sapv_order_name'] = 'Folgeverordnung';
		                }
		            }
	
		            $vr['verordnet_longtext'] = $this->getVerordnetAsLongtext($vr['verordnet']);
		            $vr['verordnet_short'] = $this->getVerordnetAsShorttext($vr['verordnet']);
		            // 	                dd($row);
		
		            $legend = "";
		            if ($sapv_primary_status) {
		
		                $primary = $this->translate('Primary Status') . ": " . $extraradioarr[$vr['primary_set']];
		                if(strtolower(trim($extraradioarr[$vr['primary_set']])) == 'als original vorhanden') {
		                    $legend .= '<ul class="sapv_primary_green"><li class="hover_pr" title="' . $primary . '"></li></ul>';
		                } else {
		                    //ISPC-2539, elena, 26.10.2020
		                    $legend .= '<ul class="sapv_primary_' . $extraStatusesColor[$vr['primary_set']] . '"><li class="hover_pr" title="' . $primary . '"></li></ul>';
		                }
		            }
		
		            if($sapv_secondary_status){
		
		                $secondary = $this->translate('2nd Page') . ": " . $extraradioarr[$vr['secondary_set']];
		
		                if($extraradioarr[$vr['secondary_set']] == 'als Original vorhanden') {
		                    $legend .= '<ul class="sapv_secondary_green"><li class="hover_sec" title="' . $secondary . '"></li></ul>';
		                } else {
		                    //ISPC-2539, elena, 26.10.2020
		                    $legend .= '<ul class="sapv_secondary_' .  $extraStatusesColor[$vr['secondary_set']] . '"><li class="hover_sec" title="' . $secondary . '"></li></ul>';
		                }
		            }
		
		            $vr['dotsLegend'] = empty($legend) ? " " : $legend;
		
		            $vr['bra_options_formated'] = null;
		            if($sapv_bra_options && strlen($vr['bra_options']) > 0) {
		                $vr['bra_options_formated'] = implode(", ", explode(',', $vr['bra_options']));
		            }
		             
		            $vr['status_color'] = $statuscolorsarray[$vr['status']];
		            // 	            dd($row);
	        	}
	        	//var_dump($row); exit;
	        }
	    }
	     
	     
	    return $saved_ipids;
	}
	

	public function get_verordnet_von($verordnet_von, $verordnet_von_type = false, $extra=false) //ISPC-1837+ISPC-1148
	{
		$multi = true;
		if(!is_array($verordnet_von))
		{
			$verordnet_von = array($verordnet_von);
			$verordnet_von_type = array($verordnet_von_type);
			$multi = false;
		}
		
		if(!$multi)
		{
			if(!$extra)
			{
				$verordner = "";
			}
			else
			{
				$verordner = array();
			}
		}
		else
		{
			$verordner = array();
		}
		
		foreach($verordnet_von as $kr=>$vr)
		{				
			if(!$verordnet_von_type[$kr] || $verordnet_von_type[$kr] == 'family_doctor')
			{
				$fdoc = new FamilyDoctor();
				$docarray = $fdoc->getFamilyDoc($vr);
				if(!empty($docarray))
				{
					if(!$extra)
					{
						if(!$multi)
						{
							$verordner = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
						}
						else
						{
							$verordner[$vr] = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
						}
					}
					else {
							
						if(!$multi)
						{
							$verordner['name'] = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
							$verordner['extra'] = $docarray[0]['street1'] . " " . $docarray[0]['street2'] . "\n " . $docarray[0]['zip'] . " " . $docarray[0]['city'] . "\n" . $docarray[0]['phone_practice'];
						}
						else
						{
							$verordner[$vr]['name'] = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
							$verordner[$vr]['extra'] = $docarray[0]['street1'] . " " . $docarray[0]['street2'] . "\n " . $docarray[0]['zip'] . " " . $docarray[0]['city'] . "\n" . $docarray[0]['phone_practice'];
						}
					}
				}
			}
			else if($verordnet_von_type[$kr] == 'specialists')
			{
				$spec = new Specialists();
				$docarray = $spec->get_specialist($vr);
				if(!empty($docarray))
				{
					if(!$extra)
					{
						if(!$multi)
						{
							$verordner = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
						}
						else
						{
							$verordner[$vr] = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
						}
					}
					else {
						if(!$multi)
						{
							$verordner['name'] = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
							$verordner['extra'] = $docarray[0]['street1'] . " " . $docarray[0]['street2'] . "\n " . $docarray[0]['zip'] . " " . $docarray[0]['city'] . "\n" . $docarray[0]['phone_practice'];
						}
						else
						{
							$verordner[$vr]['name'] = $docarray[0]['last_name'] . ", " . $docarray[0]['first_name'];
							$verordner[$vr]['extra'] = $docarray[0]['street1'] . " " . $docarray[0]['street2'] . "\n " . $docarray[0]['zip'] . " " . $docarray[0]['city'] . "\n" . $docarray[0]['phone_practice'];
						}
					}
				}
			}
			else
			{
				$hosploc = new Locations();
				$docarray = $hosploc->getLocationbyId($vr);
					
				if(!empty($docarray))
				{
					if(!$extra)
					{
						if(!$multi)
						{
							$verordner = $docarray[0]['location'];
						}
						else
						{
							$verordner[$vr] = $docarray[0]['location'];
						}
					}
					else 
					{
						if(!$multi)
						{
							$verordner = $docarray[0]['location'];
							$verordner['extra'] = $docarray[0]['street'] . "\n " . $docarray[0]['zip'] . " " . $docarray[0]['city'] . "\n" . $docarray[0]['phone1'];
						}
						else
						{
							$verordner[$vr] = $docarray[0]['location'];
							$verordner[$vr]['extra'] = $docarray[0]['street'] . "\n " . $docarray[0]['zip'] . " " . $docarray[0]['city'] . "\n" . $docarray[0]['phone1'];
						}
					}
	
				}
			}
		}
		
		return $verordner;
			
	}
	
	public function _fetch_all_sapv_in_period($ipids = array(), $active_cond = '')
	{	
		if(empty($ipids))
		{
			return;
		}
		
		$int_cond = '';
		if($active_cond != '')
		{
			$s = array('%date_start%', '%date_end%');
			$r = array('verordnungam', 'verordnungbis');
			$int_cond = str_replace($s, $r, $active_cond['interval_sql']);
		}
			
		$drop = Doctrine_Query::create()
		->select('*')
		->from('SapvVerordnung')
		->whereIn('ipid', $ipids)
		->andWhere('isdelete="0"')
		->andWhere($int_cond)
		->orderBy('ipid, id')
		->fetchArray();
			
		// 			$dropexec = $drop->execute();
		if($drop)
		{
			// 				$droparray = $dropexec->toArray();
			return $drop;
		}
	}


    /**
     * ISPC-2391, elena, 09.09.2020
     *
     * @param $ipids
     * @param $status
     * @param $permitted_only boolean //if true, only permitted SapvVersorgungen
     * @return float|int
     */
	public function get_average_active_time_by_status($ipids, $status, $permitted_only = true, $year = false){//ISPC-2391,Elena,11.01.2021
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
	    //koordination
        //TODO-3994,Elena,08.04.2021
        $statQuery = Doctrine_Query::create()
            ->select("*, GROUP_CONCAT(verordnet) as vero")
            ->from('SapvVerordnung')
            ->whereIn('ipid', $ipids);

            //->andWhere("verordnet  like ?", "%". $status . "%")
        //TODO-3994,Elena,08.04.2021
        if($year) {
            $statQuery->andWhere('YEAR(verordnungam) <= ?', $year)
                ->andWhere('YEAR(verordnungbis) >= ?', $year);
        }

        $statQuery->andWhere('isdelete=?', 0)
            ->groupBy('ipid')
        ;
        //ISPC-2391,Elena,11.01.2021
        if($permitted_only){
            $statQuery->andWhere('status=?', 2);
        }
//TODO-3994,Elena,08.04.2021
        $statarray = $statQuery->fetchArray();
        $stat_ipids = [];
        foreach($statarray as $stat){
//TODO-3994,Elena,08.04.2021
            if(!in_array( $stat['ipid'],$stat_ipids)){
                $sapv = explode(',', $stat['vero']);

                //max (verordnet) only? or each?
                $s = max($sapv);
                if(intval($s) == $status){
                    $stat_ipids[] = $stat['ipid'];
                }
                /*
                if(in_array($status, $sapv)){
                    $stat_ipids[] = $stat['ipid'];
                }*/

            }


        }
        //TODO-3994,Elena,08.04.2021
        if(empty($stat_ipids)){
            return 0;
        }
        $stat_count_ipids = count($stat_ipids);
        $alldays = 0;

        $sql = 'e.epid, p.ipid, e.ipid,';
        $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
        $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
        $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
        $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
        $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
        $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';

        $conditions_all['periods'][0]['start'] = $year . '-01-01';
        $conditions_all['periods'][0]['end'] = $year . '-12-31';
        $conditions_all['client'] = $clientid;
        $conditions_all['ipids'] = $stat_ipids;
        $patient_days_overall = Pms_CommonData::patients_days($conditions_all, $sql);
//print_r($patient_days_overall);
        foreach($patient_days_overall as $daysdata){
            $alldays += $daysdata['real_active_days_no'];
        }

        $stat_count_ipids = count($stat_ipids);
        //TODO-3994,Elena,08.04.2021
        $average_stat_days_raw = ($stat_count_ipids > 0) ? ($alldays)/$stat_count_ipids : 0;
        return $average_stat_days_raw;
    }


    /**
     * ISPC-2391, elena, 10.09.2020
     *
     * @param $ipids
     * @param $status
     * @param $permitted_only boolean if true, only permitted entries
     * @return array
     */
    public function get_data_by_status($ipids, $status, $permitted_only = true, $year = false){

        $statQuery = Doctrine_Query::create()
            ->select("*")
            ->from('SapvVerordnung')
            ->whereIn('ipid', $ipids)
            ->andWhere("verordnet  like ?", "%". $status . "%")
            ->andWhere('isdelete=?', 0)
        ;
        if($permitted_only){
            $statQuery->andWhere('status=?', 2);
        }
        //ISPC-2391,Elena,11.01.2021
        if($year){
            $statQuery->andWhere('YEAR(verordnungam)=?', $year) ;
        }
        $statarray = $statQuery->fetchArray();
        $retValue = [];

        foreach($statarray as $stat){
            $ipid = $stat['ipid'];
            if(!isset($retValue[$ipid])){
                $retValue[$ipid] = [];
            }
            $retValue[$ipid][] = $stat;
        }

        return $retValue;

    }
		
		
}

?>