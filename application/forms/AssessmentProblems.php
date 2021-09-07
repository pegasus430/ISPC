<?php
/**
 * 
 * @author claudiu✍ 
 * Jan 4, 2019
 *
 */
class Application_Form_AssessmentProblems extends Pms_Form
{
	
    protected $_model = 'AssessmentProblems';
    
	private $triggerformid = AssessmentProblems::TRIGGER_FORMID;
	private $triggerformname = AssessmentProblems::TRIGGER_FORMNAME;
	protected $_translate_lang_array = AssessmentProblems::LANGUAGE_ARRAY;
	
	
	protected $_block_feedback_options = [
	];
	
	public function getColumnMapping($fieldName, $revers = false)
	{
	    $overwriteMapping = [
	    //             $fieldName => [ value => translation]
	    ];
	
	
	    $values = AssessmentProblemsTable::getInstance()->getEnumValues($fieldName);
	
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
	
	
	public function isValid($data)
	{
	    return parent::isValid($data);   
	}
	
	
	private function _create_formular_actions($options = array(), $elementsBelongTo = null)
	{   	    
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_actions dontPrint')),
	    ));
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
       
        
	    $el = $this->createElement('button', '__action', array(
	        'type'         => 'submit',
	        'value'        => 'save',
	        'label'        => $this->translate('submit'),
	        'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
	        'decorators'   => array('ViewHelper'),
	        'class'        => 'btnSubmit2018 dontPrint',
	    
	    ));
	    $subform->addElement($el, 'save');
	    
	   // Maria:: Migration ISPC to CISPC 08.08.2020
	   /*  $el = $this->createElement('button', '__action', array(
	        'type'         => 'submit',
	        'value'        => 'print_pdf',
	        'label'        => $this->translate('Print 2 PDF only dev'),
	        'onclick'      => '$(this).parents("form").attr("target", "_blank"); window.formular_button_action = this.value;',
	        'decorators'   => array('ViewHelper'),
	        'class'        => 'btnSubmit2018 print_icon_bg dontPrint',
	    
	    ));
	    if (APPLICATION_ENV !='production') {
	       $subform->addElement($el, 'print_pdf');
	    } */
	    
	    
	    $el = $this->createElement('button', '__action', array(
	        'type'         => 'button',
	        'value'        => 'add_custom_problem',
	        'label'        => $this->translate('Add new custom Problem'),
	        'onclick'      => 'add_custom_problem(this); return false;',
	        'decorators'   => array('ViewHelper'),
	        'class'        => 'btnSubmit2018 plus_icon_bg dontPrint',
	         
	    ));
	    $subform->addElement($el, 'add_custom_problem');
	
	    
	    $el = $this->createElement('button', '__action', array(
	        'type'         => 'button',
	        'value'        => 'qtip_toggler',
	        'onclick'      => 'if ($(this).data(\'togglestatus\') == \'open\') {$(this).data(\'togglestatus\', \'closed\').text(window.translate(\'Open all courses\')); $(\'.toggler_course\').addClass(\'isHidden\').removeClass(\'isShown\'); $(".selector_row_problem_course").hide(); } else {$(this).data(\'togglestatus\', \'open\').text(window.translate(\'Close all courses\')); $(\'.toggler_course\').addClass(\'isShown\').removeClass(\'isHidden\'); $(".selector_row_problem_course").show();}; return false;',
	        'decorators'   => array('ViewHelper'),
	        'class'        => 'btnSubmit2018 dontPrint no_bg qtip_toggler',

	        'label'        => $this->translate('Open all courses'),
	        'data-togglestatus' => 'closed',
	        
	    ));
	    if ($options['__hasProblemCourses']) {
	       $subform->addElement($el, 'toggle_courses');
	    }
	    
	    
	    //Add csrf here and not in [formular], for easier test
	    $subform->addElement('hash', '__token', array(
	        'ignore'       => true,
	        //'session'     => new Zend_Session_Namespace("hashu"),
	    // 		    'strict'       => false,
	        'timeout'      => 3600, // it takes more than 1h to edit this?
	        'salt'         => "{$this->_block_name}_{$this->_patientMasterData['ipid']}",
	        'decorators'   => array('ViewHelper'),
	    ));
	    
	    return $subform;
	
	}
	
	
	public function create_form_problems_benefitplan( $options = array(), $elementsBelongTo = null)
	{
		$this->setDecorators(array(
		    'FormElements',
// 		    array('HtmlTag',array('tag' => 'table')),
		    'Form'
		));
		
		$this->__setElementsBelongTo($this, $elementsBelongTo);
		
		// Maria:: Migration ISPC to CISPC 08.08.2020
		//ISPC-2293 Carmen 10.06.2020
		//$AssessmentProblems = call_user_func_array('array_merge', array_column($options, 'AssessmentProblems'));
		$AssessmentProblems = $options;
		//--
		array_walk($AssessmentProblems, function(&$item) {
		    $item['__status'] = $item['AssessmentProblemStatus']['status'] ;
		});
		
		$AssessmentProblemCourses = call_user_func_array('array_merge', array_column($AssessmentProblems, 'AssessmentProblemCourse'));
		$options['__hasProblemCourses'] = ! empty($AssessmentProblemCourses);
		
		
		$order_by_status = [
		    'open',
		    'todo',
		    'in progress',
		    'done',
		]; 
		usort($AssessmentProblems, array(new Pms_Sorter('__status', $order_by_status), "_customorder"));
		
		
		$pageProblems = [];
		$assessment_problems_legends = $this->translate('assessment_problems_legends');
		
		foreach ($AssessmentProblems as $one_problem)
		{
		    
		    $problemsRows = $this->create_one_problem_rows($one_problem, null);

		    $pageProblems =  array_merge($pageProblems, $problemsRows);
		    
		}

	    $table = $this->subFormTable([
	        'id' => "assessment_problems_table",
	        'class'    => null,
	        'columns'=> [
	            $this->translate('Topic'),
	            $this->translate('Who should do it'),
    	        $this->translate('Status'),
    	        $this->translate('Add Entry'),
	    ]]);
	    $table->removeDecorator('Fieldset');
	
// 	    $table->addDecorators(array(
// 	        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 5)),
// 	        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
// 	     ));

        $table->addSubForms($pageProblems);
        $this->addSubForm($table, 'AssessmentProblems');
		
		
		
		//add action buttons
		$actions = $this->_create_formular_actions($options , "__formular");
		$this->addSubform($actions, 'form_actions');
		
		
		return $this;
		
	}
	

	/**
	 * 
	 * @param unknown $options
	 * @param string $elementsBelongTo
	 * @return multitype:Zend_Form_SubForm
	 */
	public function create_one_problem_rows($options = array(), $elementsBelongTo = null)
	{
	    
	    $assessment_problems_legends = $this->translate('assessment_problems_legends');
	    
	    $rows = [];
	    
	    $af_aps = new Application_Form_AssessmentProblemStatus();
	    $af_apc = new Application_Form_AssessmentProblemCourse();

	    $one_problem =  $options;
	    
	    if (empty($one_problem['fn_name'])) 
	    {
            
	        $subformTopic = new Zend_Form_SubForm();
	        $subformTopic->setDecorators(array('FormElements'));
	        $this->__setElementsBelongTo($subformTopic, $one_problem['id']);
            $subformTopic->addElement('text', 'fn_name', array(
                'value'         => null,
                'placeholder'   => $this->translate('Add your custom problem topic here'),
                'decorators'       => array(
                    'ViewHelper',
                    array('Errors'),
                ),
                'style' => 'width: 100%',
            ));
            $subformTopic->addElement('hidden', 'assessment_id', array(
                'value'            => $options['assessment_id'],
                'decorators'       => array(
                    'ViewHelper',
                ),
            ));
            
            $subformTopic->addElement('hidden', 'assessment_name', array(
                'value'            => 'MamboAssessment',
                'decorators'       => array(
                    'ViewHelper',
                ),
            ));
            
            $subformTopic->addElement('hidden', 'parent_table', array(
                'value'            => '__custom_value_manual_added',
                'decorators'       => array(
                    'ViewHelper',
                ),
            ));
            $subformTopic->addElement('hidden', 'benefit_plan', array(
                'value'            => 'yes',
                'decorators'       => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'closeOnly' => true,
                    )),
                ),
            ));
            
            
            
            
//             $rows[] = $subformTopic;
        }
	    

        $statusClass = $one_problem['__status'] ? 'hasStatus'. $this->filterName($one_problem['__status']) : '';
            
        $row = $this->subFormTableRow(['class' => "topic_row_main {$statusClass}"]);
        
        
//         if (empty($elementsBelongTo)) {
//             $this->__setElementsBelongTo($row, "AssessmentProblems55[{$one_problem['idx']}]");
//         } else {
//             $this->__setElementsBelongTo($row, $elementsBelongTo);
//         }
            
        if ( ! empty($one_problem['fn_name'])) {
        
            
            
	        $row->addElement('note', 'fn_name', array(
	            'value'            => $one_problem['parent_table'] == '__custom_value_manual_added' ? $one_problem['fn_name'] : $assessment_problems_legends[$one_problem['parent_table'] . " " . $one_problem['fn_name']],
	            'decorators'       => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array(
	                    'tag' => 'td',
	                )),
	            ),
	        ));
        } else {
            
            $row->addElement('note', 'fn_name', array(
                'value'            => $subformTopic->render(),
                'decorators'       => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                ),
            ));
        }
        
        $row->addElement('hidden', 'participant_type', array(
            'value'            => ! empty($one_problem['AssessmentProblemStatus']['participant_type']) ? $one_problem['AssessmentProblemStatus']['participant_type'] : null,
            'decorators'       => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'openOnly' => true,
                )),
            ),
            'class' => "selector_autocomplete_type dontPrint",
        ));
        
        $row->addElement('hidden', 'participant_id', array(
            'value'            => ! empty($one_problem['AssessmentProblemStatus']['participant_id']) ? $one_problem['AssessmentProblemStatus']['participant_id'] : null,
            'decorators'       => array(
                'ViewHelper',
                array('Errors'),
            ),
            'class' => "selector_autocomplete_id dontPrint",
        ));
        
        $row->addElement('text', 'participant_name', array(
            'value'            => ! empty($one_problem['AssessmentProblemStatus']['participant_name']) ? $one_problem['AssessmentProblemStatus']['participant_name'] : null,
            'decorators'       => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'closeOnly' => true,
                )),
            ),
            'class'                    => 'autocomplete selector_autocomplete_label',
            'data-autocomplete_manual' => Zend_Json::encode(array_values($af_apc->getColumnMapping('participant_autocomplete_manual'))),
        ));
        
        $row->addElement('select', 'status', array(
            'multiOptions' => $af_aps->getColumnMapping('status'),
            'value'            => ! empty($one_problem['AssessmentProblemStatus']['status']) ? $one_problem['AssessmentProblemStatus']['status'] : null,
            'decorators'       => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
            ),
        ));
        


        $row->addElement('button', 'new_course', array(
            'value'        => 'new_course',
            'label'        => '',//'Add new course',
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'openOnly' => true,
                )),
            ),
            'class'        => 'add_new dontPrint',
            'onclick'      => '$(this).parents(\'tr\').nextAll(\'.selector_add_new_course_row:first\').toggle().find(\'input, textarea\').prop(\'disabled\', function(i, v) { return !v; }); $(this).blur(); return false;',
        ));
        
        $display_none = empty($one_problem['AssessmentProblemCourse']) ? "display_none" : '';
        $row->addElement('button', 'toggle_course', array(
            'value'        => 'toggle_course',
            'label'        => '',//'Add new course',
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'closeOnly' => true,
                )),
            ),
            'class'        => "toggler_course isHidden dontPrint $display_none",
            'onclick'      => '$(this).toggleClass(\'isShown\').toggleClass(\'isHidden\').parents(\'tr\').nextAll(\'.selector_row_problem_course:first\').toggle(); $(this).blur(); return false;',
        ));
         
        
        $this->__setElementsBelongTo($row, "{$one_problem['id']}[AssessmentProblemStatus]");
        
        $rows[] = $row;
        
        

        /*
         * add new course row
         */
        $newentry = $af_apc->create_form_problem_course_newentry(null, "{$one_problem['id']}[AssessmentProblemCourse][__new_course]");
        $newentry->addDecorators(array(
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 4)),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'display_none selector_add_new_course_row dontPrint')),
        ));
        foreach ($newentry->getElements() as $element) {
            $element->setAttrib('disabled', true);
        }
        $rows[] = $newentry;
         
        
        /*
         * list old course row
         */
        if ( ! empty($one_problem['AssessmentProblemCourse'])) {
            
	        $child = $af_apc->create_form_problem_course_list($one_problem, "{$one_problem['id']}[AssessmentProblemCourse]");
	        //selector_add_new_course_row
	        
	        
	        $child->removeDecorator('Fieldset');
	        $child->addDecorators(array(
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'selector_row_problem_course display_none alwaysPrintTR')),
	        ));
	        
	        $rows[] = $child;
        }  
	    
	    return $rows;
	}
	

	
	
	
	
	
	
	
	
	/**
	 * 
	 * @param unknown $ipid
	 * @param unknown $data
	 * @throws Exception
	 * @return NULL|Doctrine_Record
	 */
	public function save_form_problems_benefitplan($ipid, $data = array())
	{ 
	    if (empty($ipid)) {
	        throw new Exception('Contact Admin, formular cannot be saved.', 0);
	    }
	    
	    $entitys = [];
	    
	    
	    
	    
	    foreach ($data['AssessmentProblems'] as $id => $problem) {
    	    //todo: validate this id belongs to this patient

		    // Maria:: Migration ISPC to CISPC 08.08.2020
    	    //ISPC-2293 Carmen 10.06.2020
	        //$entitys[] = AssessmentProblemsTable::getInstance()->findOrCreateOneBy('id', $id, $problem);
	        $problem['assessment_name'] = '';
	    	$entitys[] = AssessmentProblemsTable::getInstance()->findOrCreateOneBy(array('id', 'ipid'), array($id, $ipid), $problem);
	    	//--
	    }
	    
	    return $entitys;
    }
	
	
    
    
    

   
    
    
}

