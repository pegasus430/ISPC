<?php

Doctrine_Manager::getInstance()->bindComponent('PatientTherapieplanung', 'IDAT');

class PatientTherapieplanung extends BasePatientTherapieplanung 
{
    

    public static function getCbValuesArray()
    {
        return [
            'ernahrungstherapie' => self::translate('ernahrungstherapie'),
            'infusionstherapie' => self::translate('infusionstherapie'),
            'antibiose_bei_pneumonie' => self::translate('antibiose_bei_pneumonie'),
            'antibiose_bei_HWI' => self::translate('antibiose_bei_HWI'),
            'tumorreduktionstherapie_chemo' => self::translate('tumorreduktionstherapie_chemo'),
            'krankenhausverlegung' => self::translate('krankenhausverlegung'),
            'lagerung_durch_pflege' => self::translate('lagerung_durch_pflege'),
            'orale_medikation_mehr' => self::translate('orale_medikation_mehr'),
            'blut_volumenersatztherapie' => self::translate('blut_volumenersatztherapie'),
            'palliative' => self::translate('palliative'),
        ];
    }
	    
		public function getTherapieplanungData($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientTherapieplanung')
				->where("ipid=?", $ipid);

			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}
		
		public function getTherapieplanung($ipid)
		{
			
			if(empty($ipid)){
				return false;
			}
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientTherapieplanung')
				->where("ipid=?", $ipid)
				->limit(1);
			$livearr = $drop->fetchOne(null, DOCTRINE_CORE::HYDRATE_ARRAY );;
			
			if( ! empty($livearr)){
				return $livearr;
			}
		}

		public function clone_record($ipid, $target_ipid)
		{
			$therapy = $this->getTherapieplanungData($ipid);

			if($therapy)
			{
				foreach($therapy as $k_therapy => $v_therapy)
				{
					$p_therapy = new PatientTherapieplanung();
					$p_therapy->ipid = $target_ipid;
					$p_therapy->ernahrungstherapie = $v_therapy['ernahrungstherapie'];
					$p_therapy->infusionstherapie = $v_therapy['infusionstherapie'];
					$p_therapy->antibiose_bei_pneumonie = $v_therapy['antibiose_bei_pneumonie'];
					$p_therapy->antibiose_bei_HWI = $v_therapy['antibiose_bei_HWI'];
					$p_therapy->tumorreduktionstherapie_chemo = $v_therapy['tumorreduktionstherapie_chemo'];
					$p_therapy->krankenhausverlegung = $v_therapy['krankenhausverlegung'];
					$p_therapy->lagerung_durch_pflege = $v_therapy['lagerung_durch_pflege'];
					$p_therapy->orale_medikation_mehr = $v_therapy['orale_medikation_mehr'];
					$p_therapy->blut_volumenersatztherapie = $v_therapy['blut_volumenersatztherapie'];
					$p_therapy->palliative = $v_therapy['palliative'];
					$p_therapy->freetext = $v_therapy['freetext'];
					$p_therapy->save();

					return $p_therapy->id;
				}
			}
			else
			{
				return false;
			}
		}

	}

?>