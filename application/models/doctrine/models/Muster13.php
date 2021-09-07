<?php
Doctrine_Manager::getInstance()->bindComponent('Muster13', 'MDAT');

class Muster13 extends BaseMuster13 {

	public function get_muster13_patient_data($ipid, $prid=0)
	{
		$selector = Doctrine_Query::create()
		->select('*')
		->from('Muster13')
		->where('ipid = ?', $ipid);
		
		if($prid > 0)
		{
			$selector->andWhere('id = ?', $prid);
		}
		
		$selector_res = $selector->fetchArray();

		if($selector_res)
		{
			return $selector_res[0];
		}
		else
		{
			return false;
		}
	}
	
	public function get_multiple_muster13s($ms13ids = false, $client = false)
	{
		if($ms13ids && $client)
		{
			//$ms13ids[] = '99999999999999';
			if(!empty($ms13ids))
			{
				$ms13ids_q = Doctrine_Query::create()
					->select("*")
					->from('Muster13')
					->whereIn("id", $ms13ids)
					->andWhere("client = ?", $client)
					->andWhere('isdelete = ?', "0");
				$ms13ids_res = $ms13ids_q->fetchArray();
				if($ms13ids_res)
				{
					foreach($ms13ids_res as $k_receipt => $v_receipt)
					{
						$ms13ids_data[$v_receipt['id']] = $v_receipt;
					}
	
					return $ms13ids_data;
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
	}

    /**
     * ISPC-2530, elena, 12.10.2020
     * @return string[]
     */
	public static function getVerordungGroups(){
	    $aGroup = [
	      0 =>  'Physiotherapie',
          1 =>  'Podologische Therapie',
          2 =>  'Stimm-, Sprech- Sprach- und Schlucktherapie',
          3 =>  'Ergotherapie',
          4 =>  'Ernährungstherapie'
        ];
	    return $aGroup;
    }
}
?>