<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientLives', 'IDAT');

	class PatientLives extends BasePatientLives {

	    public static function getCbValuesArray()
	    {
	        return [
	            'alone' => self::translate('alone'),
	            'house_of_relatives' => self::translate('houseofrelatives'),
	            'apartment' => self::translate('apartment'),
	            'home' => self::translate('home'),
	            'hospiz' => self::translate('Hospiz'),
	            'with_partner' => self::translate('with partner'),
	            'with_child' => self::translate('with child'),
	            'sonstiges' => self::translate('other'),
	    
	        ];
	    }
	    
		public function getpatientLivesData($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientLives');

			if(is_array($ipid))
			{
				$drop->whereIn('ipid', $ipid);
			}
			else
			{
				$drop->where("ipid='" . $ipid . "'");
			}

			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}

		public function clone_record($ipid, $target_ipid)
		{
			$lives_data = $this->getpatientLivesData($ipid);

			if($lives_data)
			{
				foreach($lives_data as $k_lives => $v_lives)
				{
					$liv = new PatientLives();
					$liv->ipid = $target_ipid;
					$liv->alone = $v_lives['alone'];
					$liv->house_of_relatives = $v_lives['house_of_relatives'];
					$liv->apartment = $v_lives['apartment'];
					$liv->home = $v_lives['home'];
					$liv->hospiz = $v_lives['hospiz'];
					$liv->sonstiges = $v_lives['sonstiges'];
					$liv->with_partner = $v_lives['with_partner'];
					$liv->with_child = $v_lives['with_child'];
					$liv->save();
				}
			}
		}

	}

?>