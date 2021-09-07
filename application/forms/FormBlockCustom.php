<?php
/**
 * 
 * @author carmen✍ 
 * Sep 16, 2019
 * ISPC-2454  // Maria:: Migration ISPC to CISPC 08.08.2020	
 *
 */
class Application_Form_FormBlockCustom extends Pms_Form
{
    
    protected $_model = 'FormBlockCustom';
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockCustom::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array();
    protected $_block_feedback_options =  array();
    
    //protected $_block_items = null;
    protected $_block_setting_data = null;
    protected $_block_items_type = null;
    protected $_block_content = null;
   	protected $_block_name = null;
    protected $_block_abbrev = null;
    protected $_block_id = null;
    
    public function __construct($options = null)
    {
    	if($options['_block_setting_data'])
    	{
    		$this->_block_setting_data = $options['_block_setting_data'];
    		unset($options['_block_setting_data']);
    	}
    	
    	$this->_block_items_type = $this->_block_setting_data['block_type'];
    	
    	$block_content = array_column($this->_block_setting_data['block_content'], 'content_item');
    	
    	$key_content = 1;
    	foreach($block_content as $kb=>$vb)
    	{
    		$this->_block_content[$key_content] = $vb;
    		$key_content++;
    	}
    	
    	$this->_block_name = $this->_block_setting_data['block_name'];
    	
    	$this->_block_abbrev = $this->_block_setting_data['block_abbrev'];
    	
    	$this->_block_id = $this->_block_setting_data['id'];
    		//var_dump($this->_block_content); exit;
    	parent::__construct($options);
    	//print_r($this->_client_data); exit;
    
    }


    public function getColumnMapping($fieldName, $revers = false)
    {
    
        //             $fieldName => [ value => translation]
        $overwriteMapping = [
    
             
        ];
    
    
        $values = FormBlockPunctureTable::getInstance()->getEnumValues($fieldName);
    
         
        $values = array_combine($values, array_map("self::translate", $values));
    
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
    
        return $values;
    
    }
    
    
	public function create_form_custom ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_custom");
	    
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	   
	    $subform->setLegend($this->_block_name);
	    //cbrdList is the one for mobile
	    $subform->setAttrib("class", "label_same_size_auto {$__fnName}"); 
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);

	    $subform->addElement('hidden', 'id', array(
	        'label'        => null,
	        'value'        => ! empty($values['id']) ? $values['id'] : '',
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	           	array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style' => 'border: 0px;', 'colspan' => '3')),
	    		array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'    =>'dontPrint')),
	        ),
	    ));
	    
	    $subform->addElement('hidden', 'block_id', array(
	    		'label'        => null,
	    		'value'        => ! empty($values['id']) ? $values['block_id'] : $this->_block_id,
	    		'required'     => false,
	    		'readonly'     => true,
	    		'filters'      => array('StringTrim'),
	    		'decorators' => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '3')),
	    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'    =>'dontPrint')),
	    		),
	    ));
	    
	    
	    	$subcontentform = new Zend_Form_SubForm();
	    	$subcontentform->clearDecorators()
	    	->setDecorators( array(
	    			'FormElements',
	    			//array('HtmlTag',array('tag'=>'table', 'class' => 'formular_actions', 'style' => 'border: 1px solid #000;')),
	    	));
	    	
	    	$this->__setElementsBelongTo($subcontentform, 'block_content');
	    	switch($this->_block_items_type)
	    	{
	    		case 'checkbox':
	    			
    				$subcontentform->addElement('multiCheckbox', 'chkitem', array(
    						'label'      => false,
    						'multiOptions' => $this->_block_content,
    						'required'   => false,
    						'value'    => $values['block_content']['chkitem'],
    						'filters'    => array('StringTrim'),
    						'validators' => array('NotEmpty'),
    						'decorators' => array(
    								'ViewHelper',
    								array('Errors'),
    								array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '3')),
    								array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    						),
    				));
    				break;
    				
		    		case 'radio':
		    			$subcontentform->addElement('radio', 'raditem', array(
		    					'value'        => $values['block_content']['raditem'],
		    					'multiOptions' => $this->_block_content,
		    					'separator'    => '<br />',
		    					'label'        => '',
		    					'decorators' => array(
		    							'ViewHelper',
		    							array('Errors'),
		    							array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '3')),
	    								array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		    					),
		    			));
		    		break;
		    		case 'text':
		    			$nri = 0;
		    			foreach($this->_block_content as $kbc =>$vbc)
		    			{
			    			$textel = $subcontentform->createElement('text', 'textitem', array(
			    					'isArray'     => true,
			    					'value'        => !empty($values['block_content']['textitem'][$nri]) ? $values['block_content']['textitem'][$nri] : null,
			    					//'label'        => $this->translate('label item'),	
			    					'label'        => $vbc,
			    					'required'     => false,
			    					'filters'      => array('StringTrim'),
			    					'validators'   => array('NotEmpty'),
			    					'decorators'   => array(
			    							'ViewHelper',
			    							array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2')),
			    							array('Label', array(
			    									'tag' => 'td',
			    									'tagClass'=>'print_column_first'
	        
			    							)),
		    								array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
			    					),
			    					'id' => 'textitem'.$nri,
			    			));
			    			$subcontentform->addElement($textel, 'text'.$nri);
			    			$nri++;
		    			}
		    		break;
		    		case 'textarea':
		    			$nri = 0;
		    			foreach($this->_block_content as $kbc =>$vbc)
		    			{
		    				$textareael = $subcontentform->createElement('textarea', 'textareaitem', array(
		    						'isArray'     => true,
		    						'value'        => $values['block_content']['textareaitem'][$nri],
		    						//'label'        => $this->translate('label item'),
		    						'label'        => $vbc,
		    						'required'     => false,
		    						'filters'      => array('StringTrim'),
		    						'validators'   => array('NotEmpty'),
		    						'decorators'   => array(
		    								'ViewHelper',
		    								array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2')),
		    								array('Label', array(
		    										'tag' => 'td',
		    										'tagClass'=>'print_column_first'
	    
		    								)),
		    								array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		    						),
		    						'id' => 'textareaitem'.$nri,
		    				));
		    				$subcontentform->addElement($textareael, 'textarea'.$nri);
		    				$nri++;
		    			}
		    			break;
		    		default:
		    		break;	    			
	    		
	    	}
	    	$subform->addSubform($subcontentform, 'block_content');
	   
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
		
	public function save_form_custom ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_custom_copy_old_if_not_allowed($ipid , $data);
	    
	    //create patientcourse
	    $this->__save_form_custom_patient_course($ipid , $data);
	    
	    //set the old block values as isdelete
	    $this->__save_form_custom_clear_block_data($ipid, $data['__formular']['old_contact_form_id'], $this->_block_id);
	    
	    if(in_array($this->_block_abbrev, $data['__formular']['allowed_blocks']))
	    {
	    	$entity = FormBlockCustomTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
		}
	    
	    return $entity;
	}
	
	/**
	 * !! $data used by reference 
	 * 
	 * copy-paste the old saved values of the block, when this user has no access to this block
	 * 
	 * @param string $ipid
	 * @param array $data
	 */
	private function __save_form_custom_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data) 
	        || in_array($this->_block_abbrev, $data['__formular']['allowed_blocks'])
	        ) 
	    {
	        return;
	    }
	   
	    $oldValues = FormBlockCustomTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, $this->_block_id, Doctrine_Core::HYDRATE_ARRAY);
	    
	    if ( ! empty($oldValues)) {
	        
	        unset($oldValues[FormBlockCustomTable::getInstance()->getIdentifier()]);
    	    
	        $data = array_merge($data, $oldValues);
	        $data['contact_form_id'] = $data['__formular']['contact_form_id'];
	        
	        $entity = FormBlockCustomTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    }
	    
	}

	/**
	 * write or erase the patientcourse text
	 * 
	 * @param string $ipid
	 * @param unknown $data
	 */
	private function __save_form_custom_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data) 
	        || ! in_array($this->_block_abbrev, $data['__formular']['allowed_blocks'])) 
	    {
	        return;
	    }
	    
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse 
	    $formular          = $data['__formular'];
	    
	    if ( ! in_array($this->_block_abbrev, $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	    
	    
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_custom_patient_course_format($data);
	    
	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	        
    	    $oldValues = FormBlockCustomTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, $this->_block_id, Doctrine_Core::HYDRATE_ARRAY);
    	    
    	    if (empty($oldValues)) { 
    	        
    	        //missing previous values, so we save
    	        $save_2_PC = true ;
    	        
    	    } else {
    	        
    	        $course_arr_OLD =  $this->__save_form_custom_patient_course_format($oldValues);
    	        
    	        if ($course_arr_OLD === $course_arr) {
    	            //same pc... nothing to insert
    	        } else {
    	            $save_2_PC = true ;
    	        }
    	        
    	    }
	        
	    }
	    
	    
	    if ($save_2_PC
	        && ! empty($course_arr)
	        && ($pc_listener = FormBlockCustomTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
	    {
	        $course_str =  implode(PHP_EOL, $course_arr);
	        $pc_listener->setOption('disabled', false);
	        $pc_listener->setOption('course_title', $course_str);
	        $pc_listener->setOption('done_date', date('Y-m-d H:i:s', strtotime($data['__formular']['date'] . ' ' . $data['__formular']['begin_date_h'] . ':' . $data['__formular']['begin_date_m'] . ':00' )));
	        $pc_listener->setOption('user_id', $this->logininfo->userid);
	    
	    } elseif ($save_2_PC
	        && empty($course_arr) 
	        && ! empty($formular['old_contact_form_id']))
	    {
	        //must manualy remove from PC this option
            $pc_entity = new PatientCourse();
            $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockPuncture::PATIENT_COURSE_TABNAME);
        
	    }
	    
	}
	
	
	/**
	 * format the patientcourse title message
	 * 
	 * @param unknown $data
	 * @return multitype:string
	 */
	private function __save_form_custom_patient_course_format($data = []) 
	{
	    $course_arr = [];
	     
	    
	    
	    return $course_arr;
	}

	/**
	 * set isdelete = 1 for the old block
	 * 
	 * @param string $ipid
	 * @param number $contact_form_id
	 * @return boolean
	 */
	private function __save_form_custom_clear_block_data($ipid = '', $contact_form_id = 0, $block_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid) && !empty($block_id)) 
	    {
	        FormBlockCustomTable::getInstance()->createQuery('del')
	        ->delete()
	        ->where("contact_form_id = ?", $contact_form_id)
	        ->andWhere('ipid = ?', $ipid)
	        ->andWhere('block_id = ?', $block_id)
	        ->execute();
	        
	        return true;
	    }
	}
	
	 
	
}