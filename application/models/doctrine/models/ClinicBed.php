<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
	Doctrine_Manager::getInstance()->bindComponent('ClinicBed', 'SYSDAT');

	class ClinicBed extends BaseClinicBed {

		/**
		 * Find all ClinicBeds for Client.
		 *
		 * @param $clientid
		 * @return array
		 * @throws Zend_Exception
		 */
		public function getAllBeds($clientid)
		{
			$bedsAll = Doctrine_Query::create()
				->select('*, (CONVERT(AES_DECRYPT(bed_name,"' . Zend_Registry::get('salt') . '") using latin1))  as bed_name ')
				->from('ClinicBed')
				->where('isdelete = 0')
				->andWhere("client_id=?", $clientid );
			$bedAll = $bedsAll->fetchArray();

			return $bedAll;
		}

		/**
		 * Find one clinic-bed by id
		 *
		 * @param $bedid
		 * @param $clientid
		 * @return mixed
		 * @throws Zend_Exception
		 */
		public function find_bed_by_id($bedid, $clientid)
		{
			$bedsOne =  $this->getTable()->createQuery()
				->select('*, (CONVERT(AES_DECRYPT(bed_name,"' . Zend_Registry::get('salt') . '") using latin1))  as bed_name ')
				->from('ClinicBed')
				->where('isdelete = 0')
				->andWhere("id=?",$bedid )
				->andWhere("client_id=?",$clientid);
			$bed = $bedsOne->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);

			return $bed;
		}


	}

?>
