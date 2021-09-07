<?php 
/**
 * 
 * @author claudiu 
 * May 10, 2018
 *
 */
class Application_Form_Projects extends Pms_Form
{

    private $_date_format_datepicked = Zend_Date::DAY.'.'.Zend_Date::MONTH.'.'.Zend_Date::YEAR;
    private $_date_format_db = Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY;
    
    
    //private $triggerformid = Projects::TRIGGER_FORMID;
    //private $triggerformname = Projects::TRIGGER_FORMNAME;
    //protected $_translate_lang_array = Projects::LANGUAGE_ARRAY;

    public function isValid($data = array())
    {
    	$result = true;

    	switch ($data["step"]) {

    		case 'add_new_project' :
    			
    			if (empty($data["name"])
    			     || empty($data["open_from"]) 
    	    	     //|| empty($data["open_till"]) 
    	    	     || empty($data["prepare_from"]) 
    	    	     || empty($data["prepare_till"])) 
    			{
    	    		$result =  false;
    	    		$this->error_message[] = $this->translate("Value is required and can't be empty");
    	    	
    			} else if ( ! empty($data["open_from"]) && ! empty($data["open_till"]) ) {
    			    
    			    $date1 = new Zend_Date($data['open_from'], $this->_date_format_datepicked);
    			    $date2 = new Zend_Date($data['open_till'], $this->_date_format_datepicked);
    			    if ($date1->compareDate($date2) == 1) {
    			        $result =  false;
    			        $this->error_message[] = $this->translate("[Open from date is after end date]");
    			    }
    			    
    			} else {
    	    	  /*
	    	        $date1 = new Zend_Date($data['open_from'], $this->_date_format_datepicked);    	    	    
	    	        $date2 = new Zend_Date($data['open_till'], $this->_date_format_datepicked);
    	    	    if ($date1->compareDate($date2) == 1) {
    	    	        $result =  false;
    	    	        $this->error_message[] = $this->translate("[Open from date is after end date]");
    	    	    }
    	    	    */
    	    	   /*
    	    	    $date3 = new Zend_Date($data['prepare_from'], $this->_date_format_datepicked);
    	    	    $date4 = new Zend_Date($data['prepare_till'], $this->_date_format_datepicked);
    	    	    if ($date3->compareDate($date4) == 1) {
    	    	        $result =  false;
    	    	        $this->error_message[] = $this->translate("[Prepare from date is after end date]");
    	    	    }
    	    	    
    	    	    if ($date3->compareDate($date1) == 1) {
    	    	        $result =  false;
    	    	        $this->error_message[] = $this->translate("[Prepare from date is after Open from date]");
    	    	    }
    	    	    */
    	    	}
    	    	
	    	break;
	    	
	    	case 'add_project_comments' :
	    	    if (empty($data['comment'])) {
	    	        $result =  false;
	    	        $this->error_message[] = $this->translate("[Comment cannot be empty]");
	    	    }
	    	    break;
	    	    
    	    case 'add_project_work' :         	    
    	        $work = end($data['project_work']);
    	        if (empty($work['work_description'])) {
    	            $result =  false;
    	            $this->error_message[] = $this->translate("[Work Description cannot be empty]");
    	        }
    	        break;
    	        
    	        
    		case 'add_project_files' :
    		    if (empty($data['qquuid'])) {
    		        $result = false;
    		        $this->error_message[] = $this->translate("[You must first select a file]");
    		    }
    		    break;
    		    
    		case "add_project_outside_participant" :
    		    
    		    $first = reset($data['project_outside_participants']);
    		    
    		    $this->mapValidateFunction( 'create_form_add_project_outside_participants', 'create_form_isValid');
    		    
    		    $validated = $this->triggerValidateFunction('create_form_add_project_outside_participants', array($first));
    		    
    		    $result = $validated === true || $validated instanceof Zend_Form ? true : false;
    		    
    		    break;
    		    
    		    
    	
    	    default: 
    	    	$result =  false;
    	    	$this->error_message[] = $this->translate("[validation failed, unknown action]");
    	}
    	
    	
    	
    	if ( ! $result) {
    		return false;
    	} else {    		
            return parent::isValid($data);
    	}
    }
    
    
    public function create_form_new_Project(array $values = array(array()), $elementsBelongTo = null)
    {
        
    }
    
    public function delete_Project($data =  array())
    {
        $docquery = Doctrine_Query::create()
		->delete('Projects')
		->where('project_ID = ?', $data['project_ID'])
        ->andWhere('client_id = ?', $data['client_id'])
        ->execute()
        ;
    }
    
    public function delete_ProjectComments($data =  array())
    {
        $docquery = Doctrine_Query::create()
        ->delete('ProjectComments')
        ->where('project_comment_ID = ?', $data['project_comment_ID'])
        ->andWhere('project_ID = ?', $data['project_ID'])
        ->execute()
        ;
    }
    
    public function delete_ProjectParticipants($data =  array())
    {
        $docquery = Doctrine_Query::create()
        ->delete('ProjectParticipants')
        ->where('project_participant_ID = ?', $data['project_participant_ID'])
        ->andWhere('project_ID = ?', $data['project_ID'])
        ->execute()
        ;
    }
    
    public function delete_ProjectOutsideParticipants($data =  array())
    {
        $docquery = Doctrine_Query::create()
        ->delete('ProjectOutsideParticipants')
        ->where('project_outside_participant_ID = ?', $data['project_outside_participant_ID'])
        ->andWhere('project_ID = ?', $data['project_ID'])
        ->execute()
        ;
    }
    
    
    
    public function save_Project($data =  array()) 
    {
    	if (isset($data['open_from'])) {
            $date = new Zend_Date($data['open_from'], $this->_date_format_datepicked);            
    		$data['open_from']  = $date->toString($this->_date_format_db);
    		
    	}
    	
    	if ( ! empty($data['open_till'])) {
    		$date = new Zend_Date($data['open_till'], $this->_date_format_datepicked);
    		$data['open_till']  = $date->toString($this->_date_format_db);
    	} else {
    	    $data['open_till'] = NULL;
    	}
    	
    	if (isset($data['prepare_from'])) {
    		$date = new Zend_Date($data['prepare_from'], $this->_date_format_datepicked);
    		$data['prepare_from']  = $date->toString($this->_date_format_db);
    	}
    	
    	if (isset($data['prepare_till'])) {
    		$date = new Zend_Date($data['prepare_till'], $this->_date_format_datepicked);
    		$data['prepare_till']  = $date->toString($this->_date_format_db);
    	}
    	
    	if (empty($data['project_ID'])) {
    	    unset($data['project_ID']);
    	}
    	
    	$entity  = new Projects();
    	$project =  $entity->findOrCreateOneBy('project_ID', $data['project_ID'], $data);
    	
	    return $project;
    	
    }
    
    
    
    
    
    public function create_form_add_project_work ($values =  array() , $elementsBelongTo = null)
    {
        
        $this->mapValidateFunction(__FUNCTION__, "create_form_isValid");
        
        $this->mapSaveFunction(__FUNCTION__, "save_form_add_project_work");
         
        
        $subform = $this->subFormTable(array(
            'columns' => null,
            'class' => 'PatientSavoirTable',
        ));
        /*
        $subform = new Zend_Form_SubForm();
        $subform->clearDecorators();
        $subform->addDecorator('Fieldset', array('legend'=>$this->translate('[Add Project Work]')));
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->addDecorator('FormElements');
        */
        $subform->setLegend('[Add Project Work]');
        
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
        
        // 	            	    	            	    if (!empty($options)) dd($options);
        
        
        /* start with the hidden fields */
        $subform->addElement('hidden', 'project_participant_ID', array(
            'value'        => $options['project_participant_ID'] ? $options['project_participant_ID'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            //'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
            ),
        ));
        
        $subform->addElement('hidden', 'project_ID', array(
            'value'        => $options['project_ID'] ? $options['project_ID'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
        $subform->addElement('hidden', 'participant_type', array(
            'value'        => $options['participant_type'] ? $options['participant_type'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
             
        $subform->addElement('hidden', 'participant_id', array(
            'value'        => $options['participant_id'] ? $options['participant_id'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            //'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
            ),
        ));
    
        
        
        $subform->addElement('text', 'participant_name', array(
            'label'        => '[Project Work done by]',
            'value'        => isset($values['participant_name']) ? $values['participant_name'] : '',
//             'placeholder'   => 'User, VW or CustomEdit',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            
            'data-livesearch'       => 'unifiedProvider',
            'data-livesearch_options'  => json_encode(['limitSearchGroups'=> ['user', 'voluntaryworker']]),
            
        ));
        
      
        
        
        $subform->addElement('text', 'work_date', array(
            'label'        => '[Project Work Date]',
            'value'        => $values['work_date'],
            'placeholder'   => 'dd.mm.yyyy',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'class' => 'date',
        ));
        
        $subform->addElement('text', 'work_duration', array(
            'label'        => '[Project Work Duration]',
            'value'        => $values['work_date'],
            'placeholder'   => $this->translate('[in minutes]'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'work_driving_distance ', array(
            'label'        => '[Project Work Driving Distance]',
            'value'        => $values['work_driving_distance'],
            'placeholder'   => $this->translate('[in km]'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'work_driving_time', array(
            'label'        => '[Project Work Driving Time]',
            'value'        => $values['work_driving_time'],
            'placeholder'   => $this->translate('[in minutes]'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
             

        $subform->addElement('textarea', 'work_description', array(
            'label'        => null,
            'value'        => $values['work_description'],
            'placeholder'   => $this->translate('[Please describe the work you have done]'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element' , 'colspan' => 2)),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'class' => 'elastics',
            'rows'  => '6',
        ));
        
        $subform->addElement('button', 'save_button', array(
            'type'         => 'submit',
            'label'        => 'save',
            'value'        => 'save',
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element' , 'colspan' => 2)),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'class' => 'btnSubmit2018',
        ));
            return $subform;
             
    }
    
    
    public function save_form_add_project_work($data = array())
    {
        
        $allowed_participant_types = Doctrine_Core::getTable('ProjectParticipants')->getEnumValues('participant_type');
        
        $save_records = array();
        
        foreach ($data['project_work'] as $row) {
            
            
            
            if (empty($row['participant_type']) || $row['participant_type'] == 'manual') {
                //this was manualy added
                
                $row['participant_id'] = null;
                $row['participant_type'] = 'manual';
                
            } else if ( ! in_array($row['participant_type'], $allowed_participant_types)) {
                
                $row['participant_name'] =  null;
                
                continue;
            }
            
            
            if ( ! empty($row['work_date'])) {
                $date = new Zend_Date($row['work_date'],  $this->_date_format_datepicked);
                $row['work_date']  = $date->toString($this->_date_format_db);
            }

            
            
            if ( ! empty($row['project_participant_ID'])) {
                //this a update, save now
                $pp_obj = new ProjectParticipants();
                $update = $pp_obj->findOrCreateOneByPrimaryKeyAndProjectId($row['project_participant_ID'], $data['project_ID'], $row);
            }
            else {
                //this is new work
                $save_records[] = array(
                    'project_ID' => $data['project_ID'], //use this, cause _assert_correct_project_ID
                    
                    'participant_type' => $row['participant_type'],
                    'participant_id' => $row['participant_id'],
                    'participant_name' => $row['participant_name'],
                    'work_description' => $row['work_description'],
                    'work_date' => $row['work_date'],
                    'work_duration' => $row['work_duration'],
                    'work_driving_distance' => $row['work_driving_distance'],
                    'work_driving_time' => $row['work_driving_time'],
                );
            }
            
        }
        
        if ( ! empty($save_records)) {
            $collection = new Doctrine_Collection('ProjectParticipants');
            $collection->fromArray($save_records);
            $collection->save();
        }
        
    }
    
    public function save_form_add_project_files($data = array())
    {
    }
    
    public function save_form_add_project_comments($data = array())
    {
        $entity  = new ProjectComments();
        $projectComment =  $entity->findOrCreateOneBy('project_comment_ID', $data['project_comment_ID'], $data);
         
        return $projectComment;
    }
    
    
    
    
    

    public function create_form_add_project_outside_participants ($values =  array() , $elementsBelongTo = null)
    {
    
        $this->mapValidateFunction(__FUNCTION__, "create_form_isValid");
    
        $this->mapSaveFunction(__FUNCTION__, "save_form_add_outside_participants");
         
    
        $subform = $this->subFormTable(array(
            'columns' => null,
            'class' => 'PatientSavoirTable',
        ));
        
        $subform->setLegend('[Add Project Outside Participant]');
         
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        // 	            	    	            	    if (!empty($options)) dd($options);
        
    
        /* start with the hidden fields */
        $subform->addElement('hidden', 'project_outside_participant_ID', array(
            'value'        => $values['project_outside_participant_ID'] ? $values['project_outside_participant_ID'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            //'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
            ),
        ));
        $subform->addElement('hidden', 'participant_type', array(
            'value'        => $values['participant_type'] ? $values['participant_type'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
        $subform->addElement('hidden', 'participant_id', array(
            'value'        => $values['participant_id'] ? $values['participant_id'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            //'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
            ),
        ));
        $subform->addElement('hidden', 'project_ID', array(
            'value'        => $values['project_ID'] ? $values['project_ID'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            //'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
            ),
        ));
    
    
    
        $subform->addElement('text', 'first_name', array(
            'label'        => 'first_name',
            'value'        => isset($values['first_name']) ? $values['first_name'] : '',
            'required'     => true,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
    
            'data-livesearch'   => 'unifiedProvider',
            'data-livesearch_options'  => json_encode(['limitSearchGroups'=> ['member', 'voluntaryworker']]),
        ));
    
        $subform->addElement('text', 'last_name', array(
            'label'        => 'last_name',
            'value'        => $values['last_name'],
            'required'     => true,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
    
        $subform->addElement('text', 'title_prefix', array(
            'label'        => 'title_prefix',
            'value'        => $values['work_date'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'title_suffix', array(
            'label'        => 'title_suffix',
            'value'        => $values['title_suffix'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $subform->addElement('text', 'salutation', array(
            'label'        => 'salutation',
            'value'        => $values['salutation'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $subform->addElement('text', 'street', array(
            'label'        => 'street',
            'value'        => $values['street'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $subform->addElement('text', 'zip', array(
            'label'        => 'zip',
            'value'        => $values['zip'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'data-livesearch' => 'zip'
        ));
        
        $subform->addElement('text', 'city', array(
            'label'        => 'city',
            'value'        => $values['city'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'data-livesearch' => 'city'
        ));
        
        $subform->addElement('text', 'email', array(
            'label'        => 'email',
            'value'        => $values['email'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty', 'EmailAddress'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $subform->addElement('text', 'mobile', array(
            'label'        => 'mobile',
            'value'        => $values['mobile`'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'phone', array(
            'label'        => 'phone',
            'value'        => $values['phone'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
    
        $subform->addElement('textarea', 'comment', array(
            'label'        => 'comment',
            'value'        => $values['comment'],
//             'placeholder'   => $this->translate('[Please add comment]'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element' )),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'class' => 'elastics',
            'rows'  => '6',
        ));
    
        $subform->addElement('button', 'save_button', array(
            'type'         => 'submit',
            'label'        => 'save',
            'value'        => 'save',
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element' , 'colspan' => 2)),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'class' => 'btnSubmit2018',
    
        ));
        return $subform;
         
    }
    
    public function save_form_add_project_outside_participants($data = array())
    {
    
        $save_records = array();
    
        foreach ($data['project_outside_participants'] as $row) {
    
            foreach ($row as &$element) {
                if (empty($element)) {
                    $element = null;
                }
            }
    
            if (empty($row['project_ID'])) {
                $row['project_ID'] = $data['project_ID'];
            }
            
            
            $OutsideParticipants_obj =  new ProjectOutsideParticipants();
            $new_OutsideParticipants = $OutsideParticipants_obj->findOrCreateOneBy("project_outside_participant_ID", $row['project_outside_participant_ID'], $row);
    
        }
    
        return $new_OutsideParticipants;
    
    }
    
    
    
}