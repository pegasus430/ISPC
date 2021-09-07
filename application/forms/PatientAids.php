<?php
//ISPC-2381 Carmen 11.01.2021
class Application_Form_PatientAids extends Pms_Form
{
    protected $_model = 'PatientAids';
    protected $_categoriesForms_belongsTo = '';
    
//     protected $_block_name_allowed_inputs =  array(
//         "WlAssessment" => [
//             'create_form_maintenance_stage' => [
//                 'xxxxx',
//                 'first_name',
//                 ).show();).show();).show();'last_name',
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
            array( "label" => '', "cols" => array("aid", "aid")),
            array( "label" => '', "cols" => array("extraaid")),
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
    protected $_translate_lang_array = 'Form_PatientAids';

	public function create_form_patient_aids ($values =  array() , $elementsBelongTo = null)
	{
	    
	    //$this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	    
	    $this->mapSaveFunction(__FUNCTION__ , "save_patient_aids");
	    
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table', 'id' => 'aidtable'));
		$subform->setLegend($this->translate('patient_aids'));
		$subform->setAttrib("class", "label_same_size");
		
		//$this->__setElementsBelongTo($subform, $elementsBelongTo);
		$elementsBelongTo = $this->getElementsBelongTo();
		$elementsBelongTo_init = $elementsBelongTo;
		
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
				'id' => 'recid', 
		));
		
		$aid_index = array_search($values['aid'], $this->getColumnMapping('aid'));
		$subform->addElement('select', 'aid', array(
				'belongsTo' => $elementsBelongTo,
				'value'        => $aid_index != "" ? $aid_index : null,
				'multiOptions' => $this->getColumnMapping('aid'),
				'label'        => self::translate('aid'),
				'required'     => true,
// 				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						array('Label', array('tag' => 'td')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
				),
				'onChange' => 'if($(this).val() != "") {$(".extraaid_needed", $(this).parents("table")).show(); $(".needed").val([2]); $(".extrafieldsrow").remove();} else {$(".extraaid_needed", $(this).parents("table")).hide(); $(".needed").val([2]); $(".extrafieldsrow").remove()}',
				'id' => 'aid'
		));
		
		$subform->addElement('note', 'Note_aid_err', array(
				'value'        => $this->translate('aid_err'),
				'decorators'   => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array(
								'tag' => 'td', 'colspan' => 2,
						)),
						array(array('row' => 'HtmlTag'), array(
								'tag'      => 'tr', 'id' => 'aid_error', 'style' => 'display: none;'
						)),
				),
		));
		
		$elementsBelongTo = $this->getElementsBelongTo().'[extraaid]';
		
		$display = $aid_index != '' ? null : 'display:none';
		$needed_index = array_search($values['extraaid']['needed'], $this->getColumnMapping('extraaid')['needed']);
		$subform->addElement('radio', 'needed', array(
				'belongsTo' => $elementsBelongTo,
				'value'        => $needed_index != "" ? $needed_index : "2",
				'required'     => false,
				'multiOptions' => $this->getColumnMapping('extraaid')['needed'],
				'separator'    => '<br />',
				//'label'        => self::translate('extratherapy_needed'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'extraaid_needed', 'style' => $display )),
				),
				'class' => 'needed',
				'data-belongsto' => $elementsBelongTo_init,
				'onChange' => 'if($(this).val() == 3) {show_aids_extrafields(this);} else {$(".extrafieldsrow").remove()}'
		));
		
		if($needed_index == 3)
		{
			$extrafieldform = $this->create_patient_aids_extrafields($values, $elementsBelongTo);
			$subform->addSubForm($extrafieldform, 'extra');
		}
		
		
		return $this->filter_by_block_name($subform,  __FUNCTION__);
	}
	
	public function create_patient_aids_extrafields ($values =  array() , $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->clearDecorators()
		->setDecorators( array(
				'FormElements',
				//array('HtmlTag',array('tag'=>'table', 'class' => 'formular_actions', 'style' => 'border: 1px solid #000;')),
		));
		
		if(!$values['aid'])
		{
			$aidid = $values['aidid'];
			$aid = $this->getColumnMapping('aid')[$aidid];
		}
		else 
		{
			$this->__setElementsBelongTo($subform, $elementsBelongTo);
			$aid = $values['aid'];
		}
		
		if(strpos($aid, " ") !== false)
		{
			$aidarr = explode(" ", $aid);
			$aidforswitch = implode("_", $aidarr);
		}
		else 
		{
			$aidforswitch = $aid;
		}
		
		$index = !$values['aid'] ? $values['aidid'] : array_search($aid, $this->getColumnMapping('aid'));
		$elementsBelongTo = $values['belongsTo'].'[extraaid]';
		switch($aidforswitch)
		{
			case 'Sehhilfe':
			case 'Zahnspange':
			case 'Matratze':
				$subform->addElement('multiCheckbox', 'aids_type', array(
						'belongsTo' => $elementsBelongTo,
						'label'      => self::translate('aids_type'),
						'multiOptions' => $this->getAidsRadiobyAid()[$aid]['aids_type'],
						'required'   => false,
						'value'    => $values['extraaid']['aids_type'],
						'filters'    => array('StringTrim'),
						'validators' => array('NotEmpty'),
						'decorators' =>   array(
								'ViewHelper',
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
				));
				
				$subform->addElement('text', 'aids_other_type', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_other_type'),
						'value'        => $values['extraaid']['aids_other_type'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
						 
				));
				if($aidforswitch == 'Sehhilfe')
				{
					$subform->addElement('text', 'aids_diopter_right_eye', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('aids_diopter_right_eye'),
							'value'        => $values['extraaid']['aids_diopter_right_eye'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
								
					));
					$subform->addElement('text', 'aids_diopter_left_eye', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('aids_diopter_left_eye'),
							'value'        => $values['extraaid']['aids_diopter_left_eye'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
					
					));
				}
				else if($aidforswitch == 'Matratze')
				{
					$subform->addElement('text', 'aids_manufaturer_or_model', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_manufaturer_or_model'),
						'value'        => $values['extraaid']['aids_manufaturer_or_model'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
						 
				));
				}
				
				$display = !empty($values['extraaid']) ? ($values['extraaid']['aids_first_prescription'] != "" ? "" : 'display: none;') : "";
				$subform->addElement('text', 'aids_first_prescription', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_first_prescription'),
						'value'        => $values['extraaid']['aids_first_prescription'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'class'        => 'date',
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'aids_first_prescription extrafieldsrow'.' '.$index, 'style' => $display)),
						),
						'onChange' => 'if($(this).val() != "") {$(this).closest("tr").nextAll(".aids_first_prescription_unknown").eq(0).hide(); $(this).closest("tr").nextAll(".aids_first_prescription_unknown").eq(0).find(".aids_first_prescription_unknown").val("0");} else {$(this).closest("tr").nextAll(".aids_first_prescription_unknown").eq(0).show(); }',
							
				));
				
				$display = !empty($values['extraaid']) ? ($values['extraaid']['aids_first_prescription_unknown'] != 'Unbekannt' ? 'display: none;' : '') : '';
				$subform->addElement('checkbox', 'aids_first_prescription_unknown', array(
						'belongsTo' => $elementsBelongTo,
						'checkedValue'    => 'Unbekannt',
						'uncheckedValue'  => '0',
						'value'        => $values['extraaid']['aids_first_prescription_unknown'],
						'label'        => "Unbekannt",
						'required'   => false,
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'aids_first_prescription_unknown extrafieldsrow'.' '.$index, 'style' => $display)),
						),
						'class' => 'aids_first_prescription_unknown',
						'onChange' => 'if(!$(this).is(":checked")) {$(this).closest("tr").prevAll(".aids_first_prescription").eq(0).show(); } else {$(this).closest("tr").prevAll(".aids_first_prescription").eq(0).hide(); $(this).closest("tr").prevAll(".aids_first_prescription").eq(0).find(".date").val("");; $(this).val("Unbekannt");}',
				));
				
				if($aidforswitch == 'Sehhilfe' || $aidforswitch == 'Zahnspange')
				{
					$subform->addElement('text', 'aids_last_change', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('aids_last_change'),
							'value'        => $values['extraaid']['aids_last_change'] != "" ? $values['extraaid']['aids_last_change'] : date('d.m.Y', time()),
							'required'     => false,
							'filters'      => array('StringTrim'),
							'class'        => 'date',
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
								
					));
					$subform->addElement('text', 'aids_last_change_info', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('aids_last_change_info'),
							'value'        => $values['extraaid']['aids_last_change_info'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
					
					));
				}
				$subform->addElement('text', 'aids_supplier', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_supplier'),
						'value'        => $values['extraaid']['aids_supplier'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
				
				));
				
				$subform->addElement('text', 'aids_special_features', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_special_features'),
						'value'        => $values['extraaid']['aids_special_features'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
							
				));
				break;
			case 'Gaumenplatte':
			case 'Prothese':
			case 'Hörgerät':
			case 'Sitzschale':
			case 'Gehstützen':
			case 'Rollator':
			case 'Rollstuhl':
			case 'Therapiestuhl':
			case 'Rehabuggy':
			case 'Stehtrainer':
			case 'Pflegebett':
			case 'Transportliege':
			case 'Maxi-Cosi':
			case 'Kindersitz':
			case 'Kinderwagen':
			case 'Lagerungshilfen':
			case 'Toilettenstuhl':
			case 'Ernährungspumpe':
			case 'PCA-Pumpe':
			case 'Infusionspumpe':
			case 'Sonstige_Pumpe':
			case 'Absauggerät':
			case 'Beatmungsgerät':
			case 'Inhalationsgerät':
			case 'Pulsoxymeter':
			case 'Sauerstoffflasche':
			case 'Sauerstoffkonzentrator':
			case 'Lifter':
				if($aidforswitch == 'Lifter')
				{
					$subform->addElement('text', 'aids_cloth_size', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('aids_cloth_size'),
							'value'        => $values['extraaid']['aids_cloth_size'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
								
					));
				}
				
				if($aidforswitch != 'Lifter')
				{
				$subform->addElement('text', 'aids_manufaturer_or_model', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_manufaturer_or_model'),
						'value'        => $values['extraaid']['aids_manufaturer_or_model'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
							
				));
				}
				if($aidforswitch != 'Maxi-Cosi' && $aidforswitch != 'Kindersitz' && $aidforswitch != 'Kinderwagen' && $aidforswitch != 'Lagerungshilfen' && $aidforswitch != 'Toilettenstuhl')
				{
					$display = !empty($values['extraaid']) ? ($values['extraaid']['aids_first_prescription'] != "" ? "" : 'display: none;') : '';
					$subform->addElement('text', 'aids_first_prescription', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_first_prescription'),
						'value'        => $values['extraaid']['aids_first_prescription'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'class'        => 'date',
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'aids_first_prescription extrafieldsrow'.' '.$index, 'style' => $display)),
						),
						'onChange' => 'if($(this).val() != "") {$(this).closest("tr").nextAll(".aids_first_prescription_unknown").eq(0).hide(); $(this).closest("tr").nextAll(".aids_first_prescription_unknown").eq(0).find(".aids_first_prescription_unknown").val("0");} else {$(this).closest("tr").nextAll(".aids_first_prescription_unknown").eq(0).show(); }',
							
				));
				
				$display = !empty($values['extraaid']) ? ($values['extraaid']['aids_first_prescription_unknown'] != 'Unbekannt' ? 'display: none;' : '') : '';
				$subform->addElement('checkbox', 'aids_first_prescription_unknown', array(
						'belongsTo' => $elementsBelongTo,
						'checkedValue'    => 'Unbekannt',
						'uncheckedValue'  => '0',
						'value'        => $values['extraaid']['aids_first_prescription_unknown'],
						'label'        => "Unbekannt",
						'required'   => false,
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'aids_first_prescription_unknown extrafieldsrow'.' '.$index, 'style' => $display)),
						),
						'class' => 'aids_first_prescription_unknown',
						'onChange' => 'if(!$(this).is(":checked")) {$(this).closest("tr").prevAll(".aids_first_prescription").eq(0).show(); } else {$(this).closest("tr").prevAll(".aids_first_prescription").eq(0).hide(); $(this).closest("tr").prevAll(".aids_first_prescription").eq(0).find(".date").val("");; $(this).val("Unbekannt");}',
				));
				}
				if($aidforswitch == 'Lifter')
				{
					$subform->addElement('text', 'aids_manufaturer_or_model', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('aids_manufaturer_or_model'),
							'value'        => $values['extraaid']['aids_manufaturer_or_model'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
								
					));
				}
				$subform->addElement('text', 'aids_supplier', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_supplier'),
						'value'        => $values['extraaid']['aids_supplier'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
				
				));
				
				if($aidforswitch == 'Therapiestuhl' || $aidforswitch == 'Rehabuggy' || $aidforswitch == 'Stehtrainer')
				{
					$subform->addElement('text', 'wearing_time', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('wearing_time'),
							'value'        => $values['extraaid']['wearing_time'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
					
					));
				}
				
				$subform->addElement('text', 'aids_special_features', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_special_features'),
						'value'        => $values['extraaid']['aids_special_features'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
							
				));
				if($aidforswitch == 'Ernährungspumpe' || $aidforswitch == 'PCA-Pumpe' || $aidforswitch == 'Infusionspumpe' || $aidforswitch == 'Sonstige_Pumpe')
				{
					$subform->addElement('text', 'current_running_rate', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('current_running_rate'),
							'value'        => $values['extraaid']['current_running_rate'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
					
					));
				}
				break;
			case 'Orthesen':
			case 'Orthopädische_Schuhe':
			case 'Korsett':
				if($aidforswitch == 'Orthesen' || $aidforswitch == 'Orthopädische_Schuhe')
				{
					$subform->addElement('multiCheckbox', 'aids_localization', array(
							'belongsTo' => $elementsBelongTo,
							'label'      => self::translate('aids_localization'),
							'multiOptions' => $this->getAidsRadiobyAid()[$aid]['aids_localization'],
							'required'   => false,
							'value'    => $values['extraaid']['aids_localization'],
							'filters'    => array('StringTrim'),
							'validators' => array('NotEmpty'),
							'decorators' =>   array(
									'ViewHelper',
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
					));
					
					$subform->addElement('text', 'aids_other_localization', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('aids_other_localization'),
							'value'        => $values['extraaid']['aids_other_localization'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
								
					));
				}
				else 
				{
					$subform->addElement('multiCheckbox', 'aids_wearing_time_period', array(
							'belongsTo' => $elementsBelongTo,
							'label'      => self::translate('aids_wearing_time_period'),
							'multiOptions' => $this->getAidsRadiobyAid()[$aid]['aids_wearing_time_period'],
							'required'   => false,
							'value'    => $values['extraaid']['aids_wearing_time_period'],
							'filters'    => array('StringTrim'),
							'validators' => array('NotEmpty'),
							'decorators' =>   array(
									'ViewHelper',
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
					));
						
					$subform->addElement('text', 'aids_other_wearing_time_period', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('aids_other_wearing_time_period'),
							'value'        => $values['extraaid']['aids_other_wearing_time_period'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
					
					));
					
					$bruises_index = array_search($values['extraaid']['aids_bruises'], $this->getAidsRadiobyAid()[$aid]['aids_bruises']);
					$subform->addElement('radio', 'aids_bruises', array(
							'belongsTo' => $elementsBelongTo,
							'label'      => self::translate('aids_bruises'),
							'multiOptions' => $this->getAidsRadiobyAid()[$aid]['aids_bruises'],
							'required'   => false,
							'value'    => $bruises_index,
							'filters'    => array('StringTrim'),
							'validators' => array('NotEmpty'),
							'decorators' =>   array(
									'ViewHelper',
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
							'onChange' => 'if($(this).val() != "3") {$(this).closest("tr").nextAll(".other_bruises").eq(0).hide(); $(this).closest("tr").nextAll(".other_bruises").eq(0).find(".other_bruises").val("");} else {$(this).closest("tr").nextAll(".other_bruises").eq(0).show(); }',
					));
					
					$display = $bruises_index != '' ? null : 'display:none';
					$subform->addElement('text', 'aids_other_bruises', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('aids_other_bruises'),
							'value'        => $values['extraaid']['aids_other_bruises'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'other_bruises extrafieldsrow'.' '.$index, 'style' => $display)),
							),
							'class' => 'other_bruises',	
					));
				}
				
				$subform->addElement('text', 'wearing_time', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('wearing_time'),
						'value'        => $values['extraaid']['wearing_time'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
							
				));
				
				if($aidforswitch == 'Orthesen')
				{
					
					$socks_index = array_search($values['extraaid']['aids_put_on_socks'], $this->getAidsRadiobyAid()[$aid]['aids_put_on_socks']);
					$subform->addElement('radio', 'aids_put_on_socks', array(
							'belongsTo' => $elementsBelongTo,
							'label'      => self::translate('aids_put_on_socks'),
							'multiOptions' => $this->getAidsRadiobyAid()[$aid]['aids_put_on_socks'],
							'required'   => false,
							'value'    => $socks_index != "" ? $socks_index : "2",
							'filters'    => array('StringTrim'),
							'validators' => array('NotEmpty'),
							'decorators' =>   array(
									'ViewHelper',
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
							),
							'onChange' => 'if($(this).val() != "3") {$(this).closest("tr").nextAll(".other_put_on_socks").eq(0).hide(); $(this).closest("tr").nextAll(".other_put_on_socks").eq(0).find(".other_put_on_socks").val("");} else {$(this).closest("tr").nextAll(".other_put_on_socks").eq(0).show(); }',
					));
					
					$display = $socks_index != '' ? null : 'display:none';
					$subform->addElement('text', 'aids_other_put_on_socks', array(
							'belongsTo' => $elementsBelongTo,
							'label'        => self::translate('aids_other_put_on_socks'),
							'value'        => $values['extraaid']['aids_other_put_on_socks'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							'decorators'   => array(
									'ViewHelper',
									array('Errors'),
									array(array('data' => 'HtmlTag'), array('tag' => 'td')),
									array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
									array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'other_put_on_socks extrafieldsrow'.' '.$index, 'style' => $display)),
							),
							'class' => 'other_put_on_socks',
					
					));
				}
				
				$display = !empty($values['extraaid']) ? ($values['extraaid']['aids_first_prescription'] != "" ? "" : 'display: none;') : '';
				$subform->addElement('text', 'aids_first_prescription', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_first_prescription'),
						'value'        => $values['extraaid']['aids_first_prescription'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'class'        => 'date',
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'aids_first_prescription extrafieldsrow'.' '.$index, 'style' => $display)),
						),
						'onChange' => 'if($(this).val() != "") {$(this).closest("tr").nextAll(".aids_first_prescription_unknown").eq(0).hide(); $(this).closest("tr").nextAll(".aids_first_prescription_unknown").eq(0).find(".aids_first_prescription_unknown").val("0");} else {$(this).closest("tr").nextAll(".aids_first_prescription_unknown").eq(0).show(); }',
							
				));
				
				$display = !empty($values['extraaid']) ? ($values['extraaid']['aids_first_prescription_unknown'] != 'Unbekannt' ? 'display: none;' : '') : '';
				$subform->addElement('checkbox', 'aids_first_prescription_unknown', array(
						'belongsTo' => $elementsBelongTo,
						'checkedValue'    => 'Unbekannt',
						'uncheckedValue'  => '0',
						'value'        => $values['extraaid']['aids_first_prescription_unknown'],
						'label'        => "Unbekannt",
						'required'   => false,
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'aids_first_prescription_unknown extrafieldsrow'.' '.$index, 'style' => $display)),
						),
						'class' => 'aids_first_prescription_unknown',
						'onChange' => 'if(!$(this).is(":checked")) {$(this).closest("tr").prevAll(".aids_first_prescription").eq(0).show(); } else {$(this).closest("tr").prevAll(".aids_first_prescription").eq(0).hide(); $(this).closest("tr").prevAll(".aids_first_prescription").eq(0).find(".date").val("");; $(this).val("Unbekannt");}',
				));
				
				$subform->addElement('text', 'aids_manufaturer_or_model', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_manufaturer_or_model'),
						'value'        => $values['extraaid']['aids_manufaturer_or_model'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
							
				));
				
				$subform->addElement('text', 'aids_supplier', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_supplier'),
						'value'        => $values['extraaid']['aids_supplier'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
				
				));
				
				$subform->addElement('text', 'aids_special_features', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_special_features'),
						'value'        => $values['extraaid']['aids_special_features'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
							
				));
				
				break;
			
			case Kommunikationshilfen:
				$subform->addElement('multiCheckbox', 'aids_communication_help', array(
						'belongsTo' => $elementsBelongTo,
						'label'      => self::translate('aids_communication_help'),
						'multiOptions' => $this->getAidsRadiobyAid()[$aid]['aids_communication_help'],
						'required'   => false,
						'value'    => $values['extraaid']['aids_communication_help'],
						'filters'    => array('StringTrim'),
						'validators' => array('NotEmpty'),
						'decorators' =>   array(
								'ViewHelper',
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
				));
				
				$subform->addElement('text', 'aids_other_communication_help', array(
						'belongsTo' => $elementsBelongTo,
						'label'        => self::translate('aids_other_communication_help'),
						'value'        => $values['extraaid']['aids_other_communication_help'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators'   => array(
								'ViewHelper',
								array('Errors'),
								array(array('data' => 'HtmlTag'), array('tag' => 'td')),
								array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
								array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'extrafieldsrow'.' '.$index)),
						),
							
				));
				break;
			default:
				break;
		}
	
		return $subform;
	}
	
	public function save_patient_aids($ipid= '', $data = array()){
		
		
		if(empty($ipid) || empty($data)) {
			return;
		}
		
		if($data['mode'] && $data['mode'] == 'add')
		{
			unset($data['mode']);
			$setdata = 0;
			
			foreach($data as $kaid => $vaid)
			{
				if($vaid['aid'] != "")
				{
					$data_bulk[$setdata]['ipid'] = $ipid;
					$data_bulk[$setdata]['aid'] = $this->getColumnMapping('aid')[$vaid['aid']];
					$data_bulk[$setdata]['extraaid'] = $vaid['extraaid'];
					$data_bulk[$setdata]['extraaid']['needed'] = $this->getColumnMapping('extraaid')['needed'][$vaid['extraaid']['needed']];
					$data_bulk[$setdata]['extraaid']['aids_put_on_socks'] = $this->getAidsRadiobyAid()[$data_bulk[$setdata]['aid']]['aids_put_on_socks'][$vaid['extraaid']['aids_put_on_socks']];
					$data_bulk[$setdata]['extraaid']['aids_bruises'] = $this->getAidsRadiobyAid()[$data_bulk[$setdata]['aid']]['aids_bruises'][$vaid['extraaid']['aids_bruises']];					
				}
				$setdata++;
				
			}
			//var_dump($data_bulk); exit;
			//insert bulk data
			$collection = new Doctrine_Collection('PatientAids');
			$collection->fromArray($data_bulk);
			$collection->save();
			
		}
		else 
		{
			//$data_final = $data[$this->_categoriesForms_belongsTo][$this->_model];
			$data['aid'] = $this->getColumnMapping('aid')[$data['aid']];
			$data['extraaid']['needed'] = $this->getColumnMapping('extraaid')['needed'][$data['extraaid']['needed']];
			$data['extraaid']['aids_put_on_socks'] = $this->getAidsRadiobyAid()[$data['aid']]['aids_put_on_socks'][$data['extraaid']['aids_put_on_socks']];
			$data['extraaid']['aids_bruises'] = $this->getAidsRadiobyAid()[$data['aid']]['aids_bruises'][$data['extraaid']['aids_bruises']];
		
			//dd($data);
			$data['ipid'] = $ipid;
			if($data['id'] == '')
			{
				$data['id'] = null;
			}
			
			$entity = PatientAidsTable::getInstance()->findOrCreateOneBy(array('id', 'ipid'), array($data['id'], $ipid), $data);
		}
	}
		
	public function getColumnMapping($fieldName, $revers = false)
	{
	
		//             $fieldName => [ value => translation]
		$overwriteMapping = [
	
		];
	
		$values = PatientAidsTable::getInstance()->getEnumValues($fieldName);	
	
		
		if( $fieldName == 'aid')
		{
			$values_empty[''] = self::translate('select');
			$values = $values_empty+ $values;
		}
	
		if($fieldName == 'extraaid'){
			$new_values = array();
			foreach($values as $key => $vals){
				$new_values[$key] = $vals;
			}
			$values_empty[''] = $this->translate('select');
			$values = $values_empty + $new_values;
		}
		
		return $values;
	
	}
	
	private function getAidsRadiobyAid()
	{
		return array(
				'Sehhilfe' => array(
					'aids_type' => array('Brille' => 'Brille', 'Kontaktlinsen' => 'Kontaktlinsen')
				),
				'Zahnspange' => array(
					'aids_type' => array('Fest' => 'Fest', 'Lose' => 'Lose')
				),
				'Matratze' => array(
					'aids_type' => array('Sterntaler' => 'Sterntaler', 'Wechseldruckmatratze' => 'Wechseldruckmatratze')
				),
				'Orthesen' => array(
					'aids_localization' => array('Fuß links' => 'Fuß links', 'Fuß rechts' => 'Fuß rechts', 'Hand links' => 'Hand links', 'Hand rechts' => 'Hand rechts'),
					'aids_put_on_socks' => array('1' => 'Nein', '2' => 'Neutral', '3' => 'Ja')						
				),
				'Orthopädische Schuhe' => array(
					'aids_localization' => array('Links' => ' Links', 'Rechts' => 'Rechts')
				),
				'Korsett' => array(
					'aids_wearing_time_period' => array('Tags' => 'Tags', 'Nachts' => 'Nachts'),
					'aids_bruises' => array('1' => 'Nein', '2' => 'Neutral', '3' => 'Ja')
				),
				'Kommunikationshilfen' => array(
					'aids_communication_help' => array('Buzzer' => 'Buzzer', 'Bildkarten' => 'Bildkarten', 'mit Schrifteingabe' => 'mit Schrifteingabe', 'mit Symboleingabe' => 'mit Symboleingabe', 'mit Symbol- und Schrifteingabe' => 'mit Symbol- und Schrifteingabe', 'mit Aufnahmefunktion' => 'mit Aufnahmefunktion', 'mit Augensteuerung' => 'mit Augensteuerung', 'Tablet' => 'Tablet')
				),
				
		);
	}
	
	public function create_form_patient_aids_for_add ($values =  array() , $elementsBelongTo = null)
	{
		//$this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	    
	    $this->mapSaveFunction(__FUNCTION__ , "save_patient_aids");
	    
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table', 'id' => 'aidtable'));
		$subform->setLegend($this->translate('patient_aids'));
		$subform->setAttrib("class", "label_same_size");
		
		if(!empty($values)){
			$elementsBelongTo_init = $elementsBelongTo;
		}
		else 
		{
			//$this->__setElementsBelongTo($subform, $elementsBelongTo);
			$elementsBelongTo = $this->getElementsBelongTo().'[0]';
			$elementsBelongTo_init = $elementsBelongTo;
		}
		
		$subform->addElement('hidden', 'belongsTo', array(
				'value'        => $this->getElementsBelongTo(),
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
		
		$aid_index = array_search($values['aid'], $this->getColumnMapping('aid'));
		$subform->addElement('select', 'aid', array(
				'belongsTo' => $elementsBelongTo,
				'value'        => $aid_index != "" ? $aid_index : null,
				'multiOptions' => $this->getColumnMapping('aid'),
				'label'        => self::translate('aid'),
				'required'     => true,
// 				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true)),
						array('Label', array('tag' => 'td')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
				),
				'OnFocus' => 'this.oldvalue = this.value',
				'onChange' => 'if($(this).val() != "") {$(this).closest("tr").nextAll("tr.extraaid_needed").eq(0).show(); $(this).closest("tr").nextAll("tr.extraaid_needed").eq(0).find(".needed").val([2]); if(this.oldvalue != "") {$("."+this.oldvalue).remove()};} else {$(this).closest("tr").nextAll("tr.extraaid_needed").eq(0).hide(); if(this.oldvalue != "") {$("."+this.oldvalue).remove()};}',
				//'id' => 'aid'
		));
		
		if(!empty($values))
		{
			$subform->addElement('note', 'deleteformnewrow', array(
					//'belongsTo' => 'set['.$krow.']',
					'value' => '<span title="'.$this->translate("delete new aid").'" onclick="$(this).closest(\'tr\').nextAll(\'tr.extraaid_needed\').eq(0).remove(); if($(this).closest(\'tr\').find(\'select\').val() != \'\') {$(\'.\'+$(this).closest(\'tr\').find(\'select\').val()).remove()}; $(this).closest(\'tr\').remove(); "><img src="'.RES_FILE_PATH.'/images/action_delete.png" style="margin-right: 5px;"/></span>',
					'decorators'   => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
							//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
					),
			));
		}
		
		$subform->addElement('note', 'Note_aid_err', array(
				'value'        => $this->translate('aid_err'),
				'decorators'   => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array(
								'tag' => 'td', 'colspan' => 2,
						)),
						array(array('row' => 'HtmlTag'), array(
								'tag'      => 'tr', 'id' => 'aid_error', 'style' => 'display: none;'
						)),
				),
		));
		
		$elementsBelongTo = $elementsBelongTo_init.'[extraaid]';
		
		$display = $aid_index != '' ? null : 'display:none';
		$needed_index = array_search($values['extraaid']['needed'], $this->getColumnMapping('extraaid')['needed']);
		$subform->addElement('radio', 'needed', array(
				'belongsTo' => $elementsBelongTo,
				'value'        => $needed_index != "" ? $needed_index : "2",
				'required'     => false,
				'multiOptions' => $this->getColumnMapping('extraaid')['needed'],
				'separator'    => '<br />',
				//'label'        => self::translate('extratherapy_needed'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'extraaid_needed', 'style' => $display )),
				),
				'class' => 'needed',
				'data-belongsto' => $elementsBelongTo_init,
				'onChange' => 'if($(this).val() == 3) {show_aids_extrafields(this);} else {$("."+$(this).closest("tr").prev().prev().find("select").val()).remove()}'
		));
		
		if(empty($values))
		{
			$subform->addElement('hidden', 'mode', array(
					'belongsTo' => $this->getElementsBelongTo(),
					'value'        => 'add',
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
			
			$subform_add = new Zend_Form_SubForm();
			$subform_add->removeDecorator('DtDdWrapper');
			$subform_add->removeDecorator('Fieldset');
			$subform_add->addDecorator('HtmlTag', array('tag' => 'table',));
			$subform_add->addElement('note', 'addformnewrow', array(
					//'belongsTo' => 'set['.$krow.']',
					'value' => '<span title="'.$this->translate("add new aid").'" onclick="addnewpatientaids(this, $(\'#belongsTo\').val())"><img src="'.RES_FILE_PATH.'/images/btttt_plus.png" style="margin-right: 5px;"/></span>',
					'decorators'   => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style' => 'border: 0px;')),
							//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr',)),
					),
			));
			
			$subform->addSubForm($subform_add, 'addform');
		}
		
		return $this->filter_by_block_name($subform,  __FUNCTION__);
	}
	
}

?>