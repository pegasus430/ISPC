<?php
/**
 * 
 * @author claudiu✍ 
 * Jan 4, 2019
 *
 */
class Application_Form_AssessmentProblemCourse extends Pms_Form
{
	
    protected $_model = 'AssessmentProblemCourse';
    
	private $triggerformid = AssessmentProblemCourse::TRIGGER_FORMID;
	private $triggerformname = AssessmentProblemCourse::TRIGGER_FORMNAME;
	protected $_translate_lang_array = AssessmentProblemCourse::LANGUAGE_ARRAY;
	
	
	protected $_block_feedback_options = [
	];
	
	
	public function isValid($data)
	{
	    return parent::isValid($data);   
	}
	
	
	
	public function create_form_problem_course_newentry($options = array(), $elementsBelongTo = null)
	{	    
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_problem_course_newentry");
	     
	    
	    $subform = $this->subFormTable();
	    $subform->setLegend($this->translate('New Verlauf'));
	    $subform->setAttrib("class", "label_same_size_80 {$__fnName}");
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    $subform->addElement('text', 'course_date', array(
	        'label'        => 'Date / Time',
	        'required'     => false,
	        'value'        => ! empty($options['course_date']) ? $options['course_date'] : null,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'data-inputmask'  => "'alias': 'datetime', 'inputFormat': 'dd.mm.yyyy HH:MM', 'placeholder': 'dd.mm.yyyy hh:mm', 'leapday': '-02-29' , 'regex': '(0[1-9]|1[0-9]|2[0-9]|3[0-1]).(0[1-9]|1[0-2]).(201[4-9]|202[0-9]) ([0-1][0-9]|2[0-3]).([0-5][0-9])', 'greedy' : false, 'clearmaskonlostfocus': false, 'autoUnmask': true",
	        'class' => 'datetimepicker',
	    ));
	    $subform->addElement('text', 'how_long', array(
	        'label'        => 'Duration (may be 0)',
	        'required'     => false,
	        'value'        => ! empty($options['how_long']) ? $options['how_long'] : null,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'data-inputmask'   => "'mask': '9', 'repeat': 5, 'greedy': false",
	        'pattern'          => "[0-9]*",
	    ));
	    $subform->addElement('text', 'driving_distance', array(
	        'label'        => 'Driving distance',
	        'required'     => false,
	        'value'        => ! empty($options['driving_distance']) ? $options['driving_distance'] : null,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'data-inputmask'   => "'mask': '9', 'repeat': 5, 'greedy': false",
	        'pattern'          => "[0-9]*",
	    ));
	    $subform->addElement('textarea', 'freetext', array(
	        'label'        => 'comments',
	        'required'     => false,
	        'value'        => ! empty($options['freetext']) ? nl2br($options['freetext']) : null,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'rows' => 3,
	    
	    ));
	    
	    
	    return $subform;
	     
	}
	
	//todo: move to a new form Application_Form_AssessmentProblemCourse
	public function save_form_problem_course_newentry($ipid = '', $data = [])
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	     
	    return AssessmentProblemCourseTable::getInstance()->findOrCreateOneBy('id', null, $data);
	}
	
	
	
	
	public function create_form_problem_course_list($options = array(), $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__;
	    
	    $tableSubform = $this->subFormTable([
	        'id' => null,
	        'class'    => $__fnName,
	        'columns'=> [
	            $this->translate('Date / Time'),
	            $this->translate('Duration (may be 0)'),
	            $this->translate('driving_distance'),
	            $this->translate('comments'),
	        ]]);
// 	    $tableSubform->removeDecorator('Fieldset');
	    
// 	    $tableSubform->addDecorators(array(
// 	        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 4)),
// 	        array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'selector_row_problem_course display_none alwaysPrint')),
// 	    ));
	    
	    
	    $this->__setElementsBelongTo($tableSubform, $elementsBelongTo);
	    
	    $rows = [];
	    
	    foreach ($options['AssessmentProblemCourse'] as $one_course)
	    {
	        $row = $this->subFormTableRow();
	         
	    
	        $row->addElement('note', 'course_date', array(
	            'value'            => isset($one_course['course_date']) ? $one_course['course_date'] : '-',
	            'decorators'       => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array(
	                    'tag' => 'td',
	                )),
	            ),
	        ));
	         
	        $row->addElement('note', 'how_long', array(
	            'value'            => isset($one_course['how_long']) ? $one_course['how_long'] : '-',
	            'decorators'       => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array(
	                    'tag' => 'td',
	                )),
	            ),
	        ));
	        
	        $row->addElement('note', 'driving_distance', array(
	            'value'            => isset($one_course['driving_distance']) ? $one_course['driving_distance'] : '-',
	            'decorators'       => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array(
	                    'tag' => 'td',
	                )),
	            ),
	        ));
	        
	        $row->addElement('note', 'freetext', array(
	            'value'            => isset($one_course['freetext']) ? nl2br($one_course['freetext']) : '-',
	            'decorators'       => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array(
	                    'tag' => 'td',
	                )),
	            ),
	        ));	 
	        
	        $rows[] = $row;
	    }

	    
	    $tableSubform->addSubForms($rows);
	    
	     
	    return $tableSubform;
	}
	
	
    
    
    public function getColumnMapping($fieldName, $revers = false) 
    {
        

        $overwriteMapping = [
//             $fieldName => [ value => translation]
            "participant_autocomplete_manual" => [          
//                "user"          => "Benutzer",
                "Family doctor" => $this->translate('family_doc'),//"Hausarzt",
                "Specialist"    => "Facharzt",
                "Case manager"  => "Fallmanager",
                "Relatives"     => "Angehörige",
            ],
        ];
        
        
        $values = AssessmentProblemsTable::getInstance()->getEnumValues($fieldName);
        
        $values = array_combine($values, array_map("self::translate", $values));
        
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
        
        return $values;
        
    }
        
   
    
    
}

