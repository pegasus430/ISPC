<?php
/**
 * 
 * @author claudiu
 * 
 * @update 19.12.2017: used in WlAssessment
 * @update 05.01.2018: skip TriggerListener fo the PatientCourse, so we don't waste time 
 * @update 24.01.2018: used in multiple form block from ContactForm, listener is disabled by default in this models 
 * @update 17.12.2018: done_name was added to saved fields
 * 
 * @package    ISPC
 * @subpackage Application (2017-08-14)
 * @author     claudiu <office@originalware.com>
 * ISPC-2654 Carmen dublat postinsert
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class PostUpdateWriteToPatientCourseListener extends Doctrine_Record_Listener 
{
	
	protected $_options = array();
	
	/**
	 * 
	 * @var Zend_Translate
	 */
	protected $translator = null;
	
	/**
	 *
	 * @var Zend_View
	 */
	protected $view = null;
	
	
	public function __construct(array $options)
	{
		$this->_options = $options;
		
		
	}	
	
	public function postUpdate(Doctrine_Event $event)
	{
		$Invoker = $event->getInvoker();
	
		if ((empty($Invoker->ipid) && empty($this->_options['ipid']))
				|| empty($this->_options['course_title'])
				|| empty($this->_options['course_type']) )
		{
			//dd($this->_options, $Invoker->toArray());
			return;
		}
	
		$logininfo = new Zend_Session_Namespace('Login_Info');
	
		$primaryKey = $Invoker->getTable()->getIdentifier();
	
		// 		if (count($this->_options['patient_course']) != count($this->_options['patient_course'], COUNT_RECURSIVE)) {
		// 		    //multiple inserts
		// 		}
	
		$to_encrypt =  array(
				'tabname'         => ! empty($this->_options['tabname']) ? $this->_options['tabname'] : $Invoker->getTable()->getComponentName(),
				'course_type'     => $this->_options['course_type'],
				'course_title'    => $this->_options['course_title'],
		);
	
	
		if ( ! empty($this->_options['done_name'])) {
			$to_encrypt['done_name'] = $this->_options['done_name'];
		}
	
		$encrypted = Pms_CommonData::aesEncryptMultiple( $to_encrypt );
	
	
		$entity_pc = new PatientCourse();
	
		//skip Trigger(), not used at this time
		$entity_pc->triggerformid = null;
		$entity_pc->triggerformname = null;
	
	
		//this will assign ALL the columns, did not used ->create($data = array()) or ->fromArray($data = array());
		//set/change after this foreach the columns we need encrypted or auto
		$pc_columns = $entity_pc->getTable()->getColumns();
		foreach ($pc_columns as $col_name => $col_val) {
			if (isset($this->_options[$col_name])) {
				$entity_pc->$col_name = $this->_options[$col_name];
			}
		}
	
	
		$entity_pc->ipid              = ( ! empty($this->_options['ipid'])) ? $this->_options['ipid'] : $Invoker->ipid;
		$entity_pc->recordid          = ( ! empty($this->_options['recordid'])) ? $this->_options['recordid'] : $Invoker->$primaryKey;
		$entity_pc->course_date       = ( ! empty($this->_options['course_date'])) ? $this->_options['course_date'] : date("Y-m-d H:i:s", time());
		$entity_pc->user_id           = ( ! empty($this->_options['user_id'])) ? $this->_options['user_id'] : $logininfo->userid;
	
		//this 3 are encrypted
		$entity_pc->tabname           = $encrypted['tabname'];
		$entity_pc->course_type       = $encrypted['course_type'];
		$entity_pc->course_title      = $encrypted['course_title'];
	
		if ( ! empty($this->_options['done_name']) && ! empty($encrypted['done_name'])) {
			$entity_pc->done_name      = $encrypted['done_name'];
		}
	
		$entity_pc->save();
	
	
		$new_pc_id = $entity_pc->id;
			
		if ((int)$new_pc_id > 0 && $Invoker->getTable()->hasColumn('patient_course_id')) {
	
			$Invoker->patient_course_id = $new_pc_id;
			$Invoker->save();
		}
	
	}
	

}
?>