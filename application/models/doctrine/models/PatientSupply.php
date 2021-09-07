<?php

Doctrine_Manager::getInstance()->bindComponent('PatientSupply', 'IDAT');

class PatientSupply extends BasePatientSupply 
{
    
	    
	public static  function getCbValuesArray()
	{
	    return [
	        'even' => self::translate('even'),
	        'spouse' => self::translate('spouse'),
	        'member' => self::translate('supply_member'),
	        'private_support' => self::translate('support'),
	        'nursing' => self::translate('nursing'),
	        'palliativpflegedienst' => self::translate('palliativpflegedienst'),
	        'heimpersonal' => self::translate('heimpersonal'),
	    ];
	}

		public function getpatientSupplyData($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientSupply')
				->where("ipid='" . $ipid . "'");
			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}

		public function clone_record($ipid, $target_ipid)
		{
			$patient_supply = $this->getpatientSupplyData($ipid);

			if($patient_supply)
			{
				foreach($patient_supply as $k_patsup => $v_patsup)
				{
					$ps = new PatientSupply();
					$ps->ipid = $target_ipid;
					$ps->even = $v_patsup['even'];
					$ps->spouse = $v_patsup['spouse'];
					$ps->member = $v_patsup['member'];
					$ps->private_support = $v_patsup['private_support'];
					$ps->nursing = $v_patsup['nursing'];
					$ps->palliativpflegedienst = $v_patsup['palliativpflegedienst'];
					$ps->heimpersonal = $v_patsup['heimpersonal'];
					$ps->save();
				}
			}
			else
			{
				return false;
			}
		}

	}

?>