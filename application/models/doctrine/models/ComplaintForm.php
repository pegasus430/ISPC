<?php

Doctrine_Manager::getInstance()->bindComponent('ComplaintForm', 'MDAT');

class ComplaintForm extends BaseComplaintForm {
	
	/**
	 * translations are grouped into an array
	 * @var unknown
	 */
	const LANGUAGE_ARRAY    = 'complaintform_lang';
	
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
	const PATIENT_COURSE_TABNAME_PDF    = 'complaintform_pdf';
	const PATIENT_COURSE_TABNAME    = 'complaintform_save';
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
		->from('ComplaintForm')
		->where('id=?',$id)
		->andWhere('ipid =?',$ipid)
		->andWhere('isdelete = 0')
		->limit(1)
		->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
		
	
		return $row;
	}
	
	
	
	public function findOrCreateOneByIpidAndId($ipid = '', $id = 0, array $data = array())
	{
		$history_array = array();
		
		if ( empty($id) || ! $entity = $this->getTable()->findOneByIpidAndId($ipid, $id)) {
	
			$entity = $this->getTable()->create(array('ipid' => $ipid));
			unset($data[$this->getTable()->getIdentifier()]);

		} else {
			//get currecnt data and insert in history
			$current_data = $this->get_by_id($id,$ipid);
			
			foreach($current_data as $field => $s_value){
				$history_array[0]['formular_'.$field] = $s_value; 
			}
		}
	
		$entity->fromArray($data); //update
	
		$entity->save(); //at least one field must be dirty in order to persist
	

		// get data for history - when the form is new
		if(empty($history_array)){
			
			$history_array[0]['formular_id'] = $entity->id;
			foreach($data as $field => $s_value){
				$history_array[0]['formular_'.$field] = $s_value;
			}
		}
		
		if(!empty($history_array)){
		
			$collection = new Doctrine_Collection('ComplaintFormHistory');
			$collection->fromArray($history_array);
			$collection->save();
		}
		
		
		return $entity;
	}
 
	
	
	
}

?>