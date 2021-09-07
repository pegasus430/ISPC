<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientHospizvizits', 'MDAT');

	class PatientHospizvizits extends BasePatientHospizvizits {
	    
	    public static function defaultGroups() 
	    {
	        return [
	            1 => "Einsatzort / -art",
	            2 => "Absprache", //"Absprache mit/ Beratung von  FachkollegInnen",
	            3 => "palliativärztlich-pflegerische Maßnahmen", //ok
	            4 => "PBB", //"Teilversorgung - Psychosoziale Beratung und Begleitung",
	            5 => "Sozialrechtliche Beratung",
	            6 => "Ethisch-rechtliche Beratung",
	            7 => "Unterstützung in der Trauer",
	            8 => "Palliativmedizinische Leistungen",
	        ];
	    }
	    
	    public static function defaultCheckboxes() 
	    {
	        return [
	            "1" => 'Hausbesuch in Privatwohnung',
                "2" => 'Besuch im Krankenhaus / Palliativstation',
                "3" => 'Besuch in stationärer Pflegeeinrichtung / Hospiz',
                "6" => 'Telefonate/ E-Mails',
                "61" => '24h-Bereitschaft',
                "7" => '20.00 – 6.00 Uhr',
                "8" => 'behandelnde Ärzte',
                "9" => 'amb. und stationäre Pflege',
                "10" => 'Koordinatoren des amb. Hospiz',
                "11" => 'Med.-pfleg. Palliative Care Fachkraft',
                "12" => 'PalliativmedizinerIn',
                "19" => 'Klinik- Aufnahme bzw. Entlassung',
                "62" => 'HomeCare',
                "20" => 'Schmerztherapie Erstberatung',
                "21" => 'Verlaufskontrolle',
                "22" => 'REASSESSMENT Krisenintervention',
                "23" => 'Symptomkontrolle  Erstberatung',
                "24" => 'Verlaufskontrolle',
                "25" => 'REASSESSMENT Krisenintervention',
                "26" => 'individueller Notfallplan',
                "27" => 'Terminalphase',
                "28" => 'Maßnahmen im Todesfall',
                "31" => 'Medikation subkutan / i.v. / i.m.',
                "34" => 'Mundpflege',
                "35" => 'Wundversorgung',
                "36" => 'Abführmaßnahmen',
                "37" => 'Stomata-Kontinenzberatung',
                "38" => 'Lagerungsmaßnahmen',
                "39" => 'Einsatz von Hilfsmitteln',
                "40" => 'Krankheitsbewältigung',
                "41" => 'Unterstützung in akuten Krisen',
                "42" => 'Situation der Angehörigen/ Bezugspersonen',
                "43" => 'Familienkonferenz',
                "46" => 'Selbstbestimmung und Patientenverfügung',
                "63" => 'palliative Sedierung',
                "64" => 'Indikationsänderung',
                "51" => 'Unterstützung der Hinterbliebenen nach dem Tod',
                "53" => 'Schmerzpumpe: Anlage / Einstellung',
                "60" => 'Versorgung Port – zentraler Zugang',
                "4" => 'Besuch in Arztpraxis',
                "5" => 'Beratung im Büro',
                "13" => 'soziale Dienste',
                "14" => 'ehrenamtliche HospizhelferIn',
                "15" => 'therapeutische Hilfen',
                "16" => 'seelsorgerliche Fachstelle',
                "17" => 'sozial-rechtliche Beratung',
                "18" => 'fachliche Nacht- bzw. Sitzwache',
                "29" => 'Flüssigkeit und Ernährung',
                "30" => 'parenterale Ernährung',
                "32" => 'Dauerkatheter, Magensonde Anlage / Wechsel',
                "33" => 'Beratung zur Pflege/Anleitung Pflege',
                "44" => 'selbst erbracht',
                "45" => 'Vermittlung Fachstelle',
                "47" => 'Gesetzliche Vertretung',
                "48" => 'Erstellung individueller Verfügungen',
                "49" => 'Ethische Konfliktsituation',
                "50" => 'PEG als lebensverlängernde Maßnahme',
                "52" => 'Vermittlung in Trauerbegleitung',
                "54" => 'Pleurapunktion',
                "55" => 'Aszitespunktion',
                "56" => 'Transfusion',
                "57" => 'Medikamente epidural, intrathekal',
                "58" => 'Sonographie',
	        ];
	    }
	    
	    
	    public static function defaultGroupCheckboxes() 
	    {
	        return [
	            1 => [1, 2, 3, 6, 61, 7, 21, 4, 5, ],
	            2 => [8, 9, 10, 11, 12, 19, 62, 24, 13, 14, 15, 16, 17, 18, ],
	            3 => [20, 22, 23, 25, 26, 27, 28, 31, 34, 35, 36, 37, 38, 39, 29, 30, 32, 33, ],
	            4 => [40, 41, 42, 43, ],
	            5 => [44, 45, ],
	            6 => [46, 63, 64, 47, 48, 49, 50, ],
	            7 => [51, 52, ],
	            8 => [53, 60, 54, 55, 56, 57, 58, ],
	            
	        ];
	    }

		public function getPatienthospizvizits($ipid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientHospizvizits')
				->where('ipid = "' . $ipid . '"')
				->andWhere('isdelete = "0"');
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getfirstdatePatienthospizvizits($ipid)
		{
			$drop = Doctrine_Query::create()
				->select('hospizvizit_date, vw_id,type,create_date')
				->from('PatientHospizvizits')
				->where('ipid = "' . $ipid . '"  and isdelete = 0')
				->orderBy('hospizvizit_date, create_date ASC')
				->limit(1);
			$droparray = $drop->fetchArray();

			foreach($droparray as $k => $value)
			{
				if($value['type'] == "b" && $value['hospizvizit_date'] == "0000-00-00 00:00:00")
				{
					$value['hospizvizit_date'] = date('Y', strtotime($value['create_date'])) . "-01-01 00:00:00";
				}
				elseif($value['type'] == "b" && $value['hospizvizit_date'] != "0000-00-00 00:00:00")
				{
					$value['hospizvizit_date'] = $value['hospizvizit_date'];
				}
				else
				{
					if($value['hospizvizit_date'] != "0000-00-00 00:00:00")
					{
						$value['hospizvizit_date'] = $value['hospizvizit_date'];
					}
					else
					{
						$value['hospizvizit_date'] = $value['create_date'];
					}
				}

				$results[] = $value;
			}
			return $results;
		}

		public function getlastdatePatienthospizvizits($ipid)
		{
			$drop = Doctrine_Query::create()
				->select('hospizvizit_date, vw_id, type, create_date')
				->from('PatientHospizvizits')
				->where('ipid = "' . $ipid . '"  and isdelete = 0 ')
				->orderBy('hospizvizit_date DESC')
				->limit(1);
			$droparray = $drop->fetchArray();

			foreach($droparray as $k => $value)
			{
				if($value['type'] == "b" && $value['hospizvizit_date'] == "0000-00-00 00:00:00")
				{
					$value['hospizvizit_date'] = date('Y', strtotime($value['create_date'])) . '-12-31 00:00:00';
				}
				else if($value['type'] == "b" && $value['hospizvizit_date'] != "0000-00-00 00:00:00")
				{
					$value['hospizvizit_date'] = date('Y', strtotime($value['hospizvizit_date'])) . '-12-31 00:00:00';
				}
				else
				{
					if($value['hospizvizit_date'] != "0000-00-00 00:00:00")
					{
						$value['hospizvizit_date'] = $value['hospizvizit_date'];
					}
					else
					{
						$value['hospizvizit_date'] = $value['create_date'];
					}
				}
				$results[] = $value;
			}
			return $results;
		}

		public function getdurationPatienthospizvizits($ipid)
		{

			$drop = Doctrine_Query::create()
				->select('sum(besuchsdauer) as duration')
				->from('PatientHospizvizits')
				->where('ipid = "' . $ipid . '"  and isdelete = 0')
				->groupBy('ipid');
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getdistancePatienthospizvizits($ipid)
		{

			$drop = Doctrine_Query::create()
				->select('sum(fahrtkilometer) as distance')
				->from('PatientHospizvizits')
				->where('ipid = "' . $ipid . '"  and isdelete = 0')
				->groupBy('ipid');
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getPatienthospizvizitsById($id)
		{

			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientHospizvizits')
				->where('id = "' . $id . '" and isdelete="0" ');
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function gethospizvizits()
		{

			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientHospizvizits')
				->where('isdelete="0" ');
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function gethospizvreason_151015()
		{
			$Tr = new Zend_View_Helper_Translate();
			$blank = $Tr->translate('Grund');

			$titlearray = array();
			$titlearray = array(
						"" => $blank, 
						"1" => "Allgemein", 
						"2" => "Hausbesuch in Privatwohnung", 
						"3" => "Besuch im Krankenhaus /Palliativstation", 
						"4" => "Besuch in stationärer Pflegeeinrichtung", 
						"5" => "Besuch in stat. Pflegeeinrichtung / Hospiz", 
						"6" => "Besuch in Arztpraxis", 
						"7" => "Beratung im Büro", 
						"8" => "Sitzwache", 
						"9" => "Telefonate/ E-Mails/Briefe", 
						"10" => "Beratung im Klinikum",
						"11" => "Trauerbegleitung",
						"12" => "Palliativklinik-Präsenz"
					
			);
			return $titlearray;
		}
		
		
		public function gethospizvreason()
		{

		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    
		    $Tr = new Zend_View_Helper_Translate();
			$blank = $Tr->translate('Grund');

		    // get associated clients of current clientid START
		    $connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
		    if($connected_client){
		        $clientid = $connected_client;
		    } else{
		        $clientid = $logininfo->clientid;
		    }
		    $htypes = HospizVisitsTypes::get_client_hospiz_visits_types($clientid);
		    
		    
		    $titlearray[0] = $blank;
		    foreach($htypes as $gr_id =>$gr_data){
		        $titlearray[$gr_id] = $gr_data['grund'];
		    }
 
			return $titlearray;
		}

		public function getworkervisits($worker, $type = "n", $history = false)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientHospizvizits');

			if($history === false)
			{
				$drop->where('isdelete="0"');
			}

			$drop->andWhere('vw_id = ?', $worker)
				->andWhere('type=?', $type)
				->orderBy('hospizvizit_date DESC');
			$droparray = $drop->fetchArray();

			return $droparray;
		}

	}

?>