<?php

Doctrine_Manager::getInstance()->bindComponent('ComplaintFormHistory', 'MDAT');

class ComplaintFormHistory extends BaseComplaintFormHistory {
	
	/**
	 * translations are grouped into an array
	 * @var unknown
	 */
	const LANGUAGE_ARRAY    = 'complaint_lang';
	
	/**
	 * define the FORMID and FORMNAME, if you want to piggyback some triggers
	 * @var unknown
	 */
	const TRIGGER_FORMID    = null;
	const TRIGGER_FORMNAME  = 'frmcompaint_ispc2144';
	
	/**
	 * insert into patient_files will use this
	 */
	const PATIENT_FILE_TABNAME  = 'complaint_ispc2144';
	const PATIENT_FILE_TITLE    = 'Reklamations_PDF 2018'; //this will be translated
	
	/**
	 * insert into patient_course will use this
	 */
	const PATIENT_COURSE_TITLE      = 'complaint_PDF 2018 was created';
	const PATIENT_COURSE_TABNAME    = 'Complaint';
	const PATIENT_COURSE_TYPE       = 'F';
	
	public function findOrCreateOneByIdAndIpid($id = 0 , $ipid = '', array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
	{
		if ( empty($id) || ! $entity = $this->getTable()->findOneByIdAndIpid($id, $ipid)) {
	
			$entity = $this->getTable()->create(array( 'ipid' => $ipid));
		}
	
		unset($data[$this->getTable()->getIdentifier()]);
	
		$entity->fromArray($data); //update
	
		$entity->save(); //at least one field must be dirty in order to persist
	
		return $entity;
	}
	
	
	/**
	 * @Ancuta
	 * @param string $ipid
	 * @param unknown $hydrationMode
	 * @return void|Ambigous <Doctrine_Collection, multitype:>
	 */
	public function findByIpid( $ipid = '', $hydrationMode = Doctrine_Core::HYDRATE_ARRAY )
	{
		if (empty($ipid) || ! is_string($ipid)) {
	
			return;
	
		} else {
			return $this->getTable()->findBy('ipid', $ipid, $hydrationMode);
	
		}
	}
	
	
	/**
	 * @Ancuta
	 * @param string $ipid
	 * @param unknown $hydrationMode
	 * @return void|Ambigous <Doctrine_Collection, multitype:>
	 */
	public function findOneByIpid( $ipid = '', $hydrationMode = Doctrine_Core::HYDRATE_ARRAY )
	{
		if (empty($ipid) || ! is_string($ipid)) {
	
			return;
	
		} else {
			return $this->getTable()->createQuery()
			->where('ipid = ?')
			->orderBy('id DESC') // just in case the delete is not ok
			->limit(1)
			->fetchOne(array($ipid), $hydrationMode);
		}
	}

	
	public  function get_by_id ($id, $ipid = null)
	{
		$row = Doctrine_Query::create()
		->select('*')
		->from('ComplaintFormHistory')
		->where('id=?',$id)
		->andWhere('ipid =?',$ipid)
		->andWhere('isdelete = 0')
		->limit(1)
		->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
		
	
		return $row;
	}

	public  function get_all_by_id ($id, $ipid = null)
	{
		$rows = Doctrine_Query::create()
		->select('*')
		->from('ComplaintFormHistory')
		->where('formular_id=?',$id)
		->andWhere('formular_ipid =?',$ipid)
		->andWhere('isdelete = 0')
		->fetchArray(null, Doctrine_Core::HYDRATE_ARRAY);
	
	
		return $rows;
	}
	
	
	
	public function findOrCreateOneByIpidAndId($ipid = '', $id = 0, array $data = array())
	{
		if ( empty($id) || ! $entity = $this->getTable()->findOneByIpidAndId($ipid, $id)) {
	
			$entity = $this->getTable()->create(array('ipid' => $ipid));
			unset($data[$this->getTable()->getIdentifier()]);
		}
	
		$entity->fromArray($data); //update
	
		$entity->save(); //at least one field must be dirty in order to persist
	
		return $entity;
	}
 
	
	
	
}

?>