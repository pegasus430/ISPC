<?php

Doctrine_Manager::getInstance()->bindComponent('Stammdatenerweitert', 'MDAT');

class Stammdatenerweitert extends BaseStammdatenerweitert {

		public function getStammdatenerweitert($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('Stammdatenerweitert')
				->where("ipid= ? ", $ipid);
			$dropexec = $drop->execute();
			
			if($dropexec)
			{
				$droparray = $dropexec->toArray();
			}

			return $droparray;
		}

		/**
		 * 10.07.2018
		 * @return multitype:string
		 */
		public static function getOptionsHilfsmittel()
		{
		    return [
		        '1' => "O2",
		        '2' => "Toilettensitz",
		        '3' => "Pflegebett",
		        '4' => "Rollstuhl",
		        "5" => "Rollator",
		        "6" => "Nachtstuhl",
		        "7" => "Wechseldruckmatratze",   
		    ];
		}
		
		/**
		 * 10.07.2018
		 * @return multitype:string
		 */
		public static function getOptionsWunsch()
		{
		    return [
		        "1" => "Zu Hause bleiben können",
		        "2" => "kein Krankenhaus",
		        "3" => "Autonomie",
		        "4" => "Leidenslinderung",
		        "5" => "Symptomlinderung",
		        "6" => "mehr Kraft",
		        "7" => "wieder aufstehen können",
		        "8" => "noch eine Reise machen",
		        "9" => "In Ruhe gelassen werden",
		        "10" => "Keine Angabe",
		        "11" => "Frage nach aktiver Sterbehilfe",
		        "12" => "Lebensbeendigung",
		        "13" => "Expliziter Wunsch",
		    ];
		}
		
		
		
		public static function getFamilienstandfun()
		{
// 			$familienstandarray = array('1' => "ledig", '2' => "verwitwet", '3' => "geschieden", '4' => "Ehe / Partnerschaft");

// 			ISPC-1891
			$familienstandarray = array(
					'1' => "ledig",
					'2' => "verheiratet",
					'3' => "verwitwet",					
					'4' => "geschieden",
					'5' => "getrennt lebend",
					'6' => "unbek",
			        '7' => "Partnerschaft",					
			);

			return $familienstandarray;
		}

		public static function getStastszugehorigkeitfun()
		{
			$stastszugehorigkeitarray = array('' => "", '1' => "Deutsch", '2' => "andere");
			return $stastszugehorigkeitarray;
		}

		public static function getVigilanzfun()
		{
			$finalarray = array('1' => "wach", '2' => "somnolent", '3' => "komatös");
			return $finalarray;
		}

		public static function getOrientierungfun()
		{
			$finalarray = array('1' => "voll", '2' => "teilweise", '3' => "schwer einschätzbar", '4' => "weglaufgefährdet", '5' => "Verständigung eingeschränkt");
			return $finalarray;
		}

		/**
		 * 11.07.2018
		 * this is the subtab of getOrientierungfun -> 5
		 * @return multitype:string
		 */
		public static function getOrientierungfun2()
		{
		    return [
		        'sprachlich'  => 'sprachlich',
		        'kognitiv'    => 'kognitiv',	        
		        'horprobleme' => 'Hörprobleme',		        
		    ];
		}
		
		
		public static function getErnahrungfun()
		{
			$finalarray = array('1' => "selbstständig", '2' => "teilweise Hilfe", '3' => "vollst. Hilfe");
			return $finalarray;
		}

		public static function getAusscheidungfun()
		{
			$finalarray = array(
			    '1' => "selbstständig",
			    '2' => "teilw. Hilfe",
			    '3' => "vollst. Hilfe" ,
			    '4' => 'DK',
			    '5' => 'SPF',
			);
			return $finalarray;
		}

		public static function getKunstlichefun()
		{
			$finalarray = array('1' => "Darm", '2' => "Blase", '3' => "Luftröhre", '4' => "Ablaufsonde", '5' => "besonderer Aus-/ Eingang");
			return $finalarray;
		}

		public static function getRadioOptionsfun()
		{
			$finalarray = array('1' => "kein", '2' => "leicht", '3' => "mittel", '4' => "stark");
			return $finalarray;
		}

		public static function getLivingWill()
		{
			//$finalarray = array('1' => 'Ist vorhanden', '0' => 'Ist nicht vorhanden');
		    $finalarray = array('1' => 'Ist vorhanden', '0' => 'Ist nicht vorhanden', '2' => 'Ist nicht gewollt');    //ISPC-2671 Lore 07.09.2020
			return $finalarray;
		}
		

		/**
		 * ISPC-2508 Carmen
		 * @return string[][]
		 * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
		 */
		public static function getOldArtificialEntriesExits()
		{
			$finalarray = array(
				'Ausscheidung' => array(
						'4' => 'DK',
			   			'5' => 'SPF',),				
			    'Kunstliche' => array(
			    		'1' => 'Darm',
						'2' => 'Blase',
						'3' => 'Luftröhre',
						'4' => 'Ablaufsonde',
						'5' => 'besonderer Aus-/ Eingang',),
				'Ernahrung' => array(
						'peg' => 'PEG',
						'port' => 'PORT',
						'zvk' => 'ZVK',
						'magensonde' => 'Magensonde',
				)					
			);
			
			return $finalarray;
		}

		public function clone_record($ipid, $target_ipid)
		{
			$master_data = $this->getStammdatenerweitert($ipid);

			if($master_data)
			{
				foreach($master_data as $k_master_data => $v_master_data)
				{
					$cust = new Stammdatenerweitert();
					$cust->ipid = $target_ipid;
					$cust->familienstand = $v_master_data['familienstand'];
					$cust->vigilanz = $v_master_data['vigilanz'];
					$cust->ernahrung = $v_master_data['ernahrung'];
					$cust->orientierung = $v_master_data['orientierung'];
					$cust->kunstliche = $v_master_data['kunstliche'];
					$cust->kunstlichemore = $v_master_data['kunstlichemore'];
					$cust->ausscheidung = $v_master_data['ausscheidung'];
					$cust->ausgepragte = $v_master_data['ausgepragte'];
					$cust->sprachlich = $v_master_data['sprachlich'];
					$cust->kognitiv = $v_master_data['kognitiv'];
					$cust->anderefree = $v_master_data['anderefree'];
					$cust->horprobleme = $v_master_data['horprobleme'];
					$cust->stastszugehorigkeit = $v_master_data['stastszugehorigkeit'];
					//ISPC-2614 Ancuta 16.07.2020 :: mising from share 
					$stastszugehorigkeit2 = '2ndstastszugehorigkeit';
					$cust->{$stastszugehorigkeit2} = $v_master_data['2ndstastszugehorigkeit'];
					$anderefree2 = '2ndanderefree';
					$cust->{$anderefree2} = $v_master_data['2ndanderefree'];
					$cust->dolmetscher = $v_master_data['2ndanderefree'];
					// -- 
					$cust->schmerzen = $v_master_data['schmerzen'];
					$cust->neuropat = $v_master_data['neuropat'];
					$cust->viszerale = $v_master_data['viszerale'];
					$cust->respiratorische = $v_master_data['respiratorische'];
					$cust->atemnot = $v_master_data['atemnot'];
					$cust->reizhusten = $v_master_data['reizhusten'];
					$cust->verschleimung = $v_master_data['verschleimung'];
					$cust->gastrointestinale = $v_master_data['gastrointestinale'];
					$cust->aszites = $v_master_data['aszites'];
					$cust->ubelkeit = $v_master_data['ubelkeit'];
					$cust->bluterbrechen = $v_master_data['bluterbrechen'];
					$cust->durchfall = $v_master_data['durchfall'];
					$cust->obstipation = $v_master_data['obstipation'];
					$cust->soor = $v_master_data['soor'];
					$cust->schluckstorungen = $v_master_data['schluckstorungen'];
					$cust->neurologische = $v_master_data['neurologische'];
					$cust->angst = $v_master_data['angst'];
					$cust->depression = $v_master_data['depression'];
					$cust->unruhe = $v_master_data['unruhe'];
					$cust->desorientierung = $v_master_data['desorientierung'];
					$cust->krampfanfalle = $v_master_data['krampfanfalle'];
					$cust->lahmungen = $v_master_data['lahmungen'];
					$cust->gangunsicherheit = $v_master_data['gangunsicherheit'];
					$cust->schwindel = $v_master_data['schwindel'];
					$cust->sensibilitatsstogg = $v_master_data['sensibilitatsstogg'];
					$cust->ulzerierende = $v_master_data['ulzerierende'];
					$cust->decubitus = $v_master_data['decubitus'];
					$cust->exulcerationen = $v_master_data['exulcerationen'];
					$cust->lymph_odeme = $v_master_data['lymph_odeme'];
					$cust->urogenitale = $v_master_data['urogenitale'];
					$cust->harnverhalt = $v_master_data['harnverhalt'];
					$cust->soziale = $v_master_data['soziale'];
					$cust->lebensqualitat = $v_master_data['lebensqualitat'];
					$cust->organisationsprob = $v_master_data['organisationsprob'];
					$cust->finanzprobleme = $v_master_data['finanzprobleme'];
					$cust->sonstiges = $v_master_data['sonstiges'];
					$cust->fatique = $v_master_data['fatique'];
					$cust->juckreiz = $v_master_data['juckreiz'];
					$cust->kachexie = $v_master_data['kachexie'];
					$cust->hilfsmittel = $v_master_data['hilfsmittel'];
					$cust->wunsch = $v_master_data['wunsch'];
					$cust->wunschmore = $v_master_data['wunschmore'];
					$cust->sprachstorung = $v_master_data['sprachstorung'];
					$cust->ethische = $v_master_data['ethische'];
					$cust->sozial_rechtliche = $v_master_data['sozial_rechtliche'];
					$cust->unterstutzungsbedarf = $v_master_data['unterstutzungsbedarf'];
					$cust->existentielle = $v_master_data['existentielle'];
					$cust->save();
					return $cust->id;
				}
			}
		}

		


	/**
	 * 
	 * @param unknown $fieldName
	 * @param unknown $value
	 * @param array $data
	 * @param unknown $hydrationMode
	 * @return Doctrine_Record
	 */	
// 	public function findOrCreateOneBy($fieldName, $value, array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
// 	{
// 	    if ( is_null($value) || ! $entity = $this->getTable()->findOneBy($fieldName, $value, $hydrationMode)) {
	
// 	        if ($fieldName != $this->getTable()->getIdentifier()) {
// 	            $entity = $this->getTable()->create(array( $fieldName => $value));
// 	        } else {
// 	            $entity = $this->getTable()->create();
// 	        }	
// 	    }
// 	    unset($data[$this->getTable()->getIdentifier()]); // just in case
	
// 	    $entity->fromArray($data); //update
	
// 	    $entity->save(); //at least one field must be dirty in order to persist

// 	    return $entity;
// 	}
}

?>