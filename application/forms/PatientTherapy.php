<?php
//ISPC-2774 Carmen 16.12.2020
class Application_Form_PatientTherapy extends Pms_Form
{
    protected $_model = 'PatientTherapy';
    protected $_categoriesForms_belongsTo = '';
    
//     protected $_block_name_allowed_inputs =  array(
//         "WlAssessment" => [
//             'create_form_maintenance_stage' => [
//                 'xxxxx',
//                 'first_name',
//                 'last_name',
//                 'birthd',
//                 'street1',
//                 'zip',
//                 'city',
//                 'phone',
    
//             ],
//         ],
//     );

    public function __construct($options = null)
    {
    	if($options['_categoriesForms_belongsTo'])
    	{
    
    		$this->_categoriesForms_belongsTo = $options['_categoriesForms_belongsTo'];
    		unset($options['_categoriesForms_belongsTo']);
    	}
    
    	parent::__construct($options);
    
    }
    
    public function getVersorgerExtract() {
        return array(
            array( "label" => $this->translate('therapy'), "cols" => array("therapy", "therapy")),
            //TODO-3830 Ancuta 09.02.2021
            array( "label" => ' ', "cols" => array("extratherapy_needed") ),
            array( "label" => ' ', "cols" => array("extratherapy_more_text")),
            //--
            /* array( "label" => $this->translate('horherstufung'), "cols" => array("horherstufung", "h_fromdate")),
            array( "label" => $this->translate('rejected_date'), "cols" => array("rejected_date", "rejected_date")),        //ISPC-2668 Lore 11.09.2020
            array( "label" => $this->translate('opposition_date'), "cols" => array("opposition_date", "opposition_date")),      //ISPC-2668 Lore 11.09.2020 */
        );
    }
    
    public function getVersorgerAddress()
    {
        return null;
    }
    
    protected $_block_feedback_options = [
        
    ];
    
    
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientTherapy';

	public function create_form_therapy ($values =  array() , $elementsBelongTo = null)
	{
	    
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	    
	    $this->mapSaveFunction(__FUNCTION__ , "save_therapy");
	    
	    
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table'));
		$subform->setLegend($this->translate('therapies'));
		$subform->setAttrib("class", "label_same_size");
		
		//$this->__setElementsBelongTo($subform, $elementsBelongTo);
		$elementsBelongTo = $this->getElementsBelongTo();
		$subform->addElement('hidden', 'id', array(
				'belongsTo' => $elementsBelongTo,
				'value'        => $values['id'] ? $values['id'] : 0 ,
				'required'     => false,
				'filters'      => array('StringTrim'),
				'validators'   => array('NotEmpty'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
						array('Label', array('tag' => 'td')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
		
				),
		));
		
		$therapy_index = array_search($values['therapy'], $this->getColumnMapping('therapy'));
		$subform->addElement('select', 'therapy', array(
				'belongsTo' => $elementsBelongTo,
				'value'        => $therapy_index != "" ? $therapy_index : null,
				'multiOptions' => $this->getColumnMapping('therapy'),
				'label'        => self::translate('therapy'),
				'required'     => true,
// 				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						array('Label', array('tag' => 'td')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
				),
				'onChange' => '$(".extratherapy_needed", $(this).parents("table")).show(); $(".extratherapy_more", $(this).parents("table")).show();  $("#extratherapy_more").val(""); $("#extratherapy_more").val("");',
		));
		
		$elementsBelongTo = $this->getElementsBelongTo().'[extratherapy]';
		
		$display = $therapy_index != '' ? null : 'display:none';
		$needed_index = array_search($values['extratherapy']['needed'], $this->getColumnMapping('extratherapy')['needed']);
		$subform->addElement('radio', 'needed', array(
				'belongsTo' => $elementsBelongTo,
				'value'        => $needed_index,
				'required'     => false,
				'multiOptions' => $this->getColumnMapping('extratherapy')['needed'],
				'separator'    => '<br />',
				//'label'        => self::translate('extratherapy_needed'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'extratherapy_needed', 'style' => $display )),
				),
				'id' => 'extratherapy_needed',
		));
		
		$subform->addElement('text', 'extratherapy_more', array(
				'belongsTo' => $elementsBelongTo,
				//'label' 	   => self::translate('extratherapy_more'),
				'value'        => $values['extratherapy']['extratherapy_more'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				//'placeholder'  => $this->translate('freetext'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'extratherapy_more', 'style' => $display )),
				),
				'id' => 'extratherapy_more',
		));
		
		return $this->filter_by_block_name($subform,  __FUNCTION__);
	}	
	
	public function save_therapy($ipid= '', $data = array()){
		
		
		if(empty($ipid) || empty($data)) {
			return;
		}
		
		$data_final = $data[$this->_categoriesForms_belongsTo][$this->_model];
		$data_final['therapy'] = $this->getColumnMapping('therapy')[$data_final['therapy']];
		$data_final['extratherapy']['needed'] = $this->getColumnMapping('extratherapy')['needed'][$data_final['extratherapy']['needed']];
		
		//dd($data);
		$data_final['ipid'] = $ipid;
		if($data_final['id'] == '')
		{
			$data_final['id'] = null;
		}
		
		$entity = PatientTherapyTable::getInstance()->findOrCreateOneBy(array('id', 'ipid'), array($data_final['id'], $ipid), $data_final);
	}
		
	public function getColumnMapping($fieldName, $revers = false)
	{
	
		//             $fieldName => [ value => translation]
		$overwriteMapping = [
	
		];
	
		$values = PatientTherapyTable::getInstance()->getEnumValues($fieldName);	
	
		
		if( $fieldName == 'therapy')
		{
			$values_empty[''] = self::translate('select');
			$values = $values_empty+ $values;
		}
	
		if($fieldName == 'extratherapy'){
			$new_values = array();
			foreach($values as $key => $vals){
				$new_values[$key] = $vals;
			}
			$values_empty[''] = $this->translate('select');
			$values = $values_empty + $new_values;
		}
		 
		return $values;
	
	}
	
}

?>