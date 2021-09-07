<?php

	Doctrine_Manager::getInstance()->bindComponent('KvnoAssessment', 'MDAT');

	class KvnoAssessment extends BaseKvnoAssessment {

		public function getPatientAssessment($ipid)
		{
			$kvnoassessment = Doctrine_Query::create()
				->select("*")
				->from('KvnoAssessment')
				->where("ipid='" . $ipid . "'");
			$kvnoassessmentarray = $kvnoassessment->fetchArray();

			return $kvnoassessmentarray;
		}

		public function getAllAssesmentsInPeriod($ipids, $start_date, $end_date)
		{
			$reass = Doctrine_Query::create()
				->select("*")
				->from('KvnoAssessment')
				->where('ipid in(' . $ipids . ')')
				->andWhere('iscompleted = 1')
				->andWhere('reeval BETWEEN "' . date("Y-m-d H:i:s", $start_date) . '" AND "' . date("Y-m-d H:i:s", $end_date) . '"');
			$reassarray = $reass->fetchArray();

			return $reassarray;
		}

		
	/**
	 * @cla on 05.06.2018 for ISPC-2198
	 * 
	 * @param string $ipid
	 * @param string $start_date
	 * @param string $end_date
	 * @param unknown $hydrationMode
	 * @return void|Doctrine_Collection
	 */
	public function findCompletedInPeriod($ipid = '', $start_date = '', $end_date = '', $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
	{
	    if (empty($ipid) 
	        || empty($start_date)
	        || empty($end_date)
        ) {
            return;
        }

        return $this->getTable()->createQuery('ka')
        ->select('*')
		->where('ipid = ?',  $ipid)
		->andWhere('iscompleted = 1')
		->andWhere('completed_date BETWEEN ? AND ? ', array( date("Y-m-d H:i:s", strtotime($start_date)),  date("Y-m-d H:i:s", strtotime($end_date))) )
		->orderBy('completed_date ASC')
        ->execute(null, $hydrationMode);
	}	
}

?>