<?php
/**
 * 
 * @author carmen
 * 
 * 21.09.2018
 *
 */
class Application_Form_PatientNutritionalStatus extends Pms_Form
{
	protected $title = null;
	
	protected $title_italic = null;
	
	protected $_bmi_legend = null;
	
	protected $_last_3_6_month_weight_proc_legend = null;
	
	protected $_acute_illness_legend = null;
	
	public function __construct($options = null)
	{
		
		//print_r($this->_patientMasterData); exit;
		parent::__construct($options);
		$this->title = '<h2>Erfassung des Ernährungszustandes mit<font style="font-weight: bold;">&nbsp;MUST</font></h2>';
		$this->title_italic = 'Malnutrition Universal Screening Tool';
		
		$this->_bmi_legend = '<table style="width: 145px; font-size: 10px;" cellpadding="3" cellspacing="3" border="0">';
		$this->_bmi_legend .= '<tr><td style="width: 55px; border: 0px; padding-left: 10px;" colspan="2">BMI</td><td style="text-align: center; border: 0px;">Punktzahl</td></tr>';
		$this->_bmi_legend .= '<tr><td style="width: 55px; border: 0px;  padding-left: 10px;">größer 20</td><td style="border: 0px;">=</td><td style="text-align: center; border: 0px;">0</td></tr>';
		$this->_bmi_legend .= '<tr><td style="width: 55px; border: 0px;  padding-left: 10px;">18,5 - 20</td><td style="border: 0px;">=</td><td style="text-align: center; border: 0px;">1</td></tr>';
		$this->_bmi_legend .= '<tr><td style="width: 55px; border: 0px;  padding-left: 10px;">kleiner 18,5</td><td style="border: 0px;">=</td><td style="text-align: center; border: 0px;">2</td></tr>';
		$this->_bmi_legend .= '</table>';
		
		$this->_last_3_6_month_weight_proc_legend = '<table style="width: 145px; font-size: 10px;" cellpadding="3" cellspacing="3" border="0">';
		$this->_last_3_6_month_weight_proc_legend .= '<tr><td style="width: 55px; border: 0px; padding-left: 10px;" colspan="2">%-Wert</td><td style="text-align: center; border: 0px;">Punktzahl</td></tr>';
		$this->_last_3_6_month_weight_proc_legend .= '<tr><td style="width: 55px; border: 0px;  padding-left: 10px;">bis 5</td><td style="border: 0px;">=</td><td style="text-align: center; border: 0px;">0</td></tr>';
		$this->_last_3_6_month_weight_proc_legend .= '<tr><td style="width: 55px; border: 0px;  padding-left: 10px;">5 - 10</td><td style="border: 0px;">=</td><td style="text-align: center; border: 0px;">1</td></tr>';
		$this->_last_3_6_month_weight_proc_legend .= '<tr><td style="width: 55px; border: 0px;  padding-left: 10px;">über 10</td><td style="border: 0px;">=</td><td style="text-align: center; border: 0px;">2</td></tr>';
		$this->_last_3_6_month_weight_proc_legend .= '</table>';
		
		$this->_acute_illness_legend = '<table style="width: 65px; font-size: 10px;" cellpadding="3" cellspacing="3" border="0">';
		$this->_acute_illness_legend .= '<tr><td style="width: 55px; border: 0px; padding-left: 10px;">Punktzahl</td></tr>';
		$this->_acute_illness_legend .= '<tr><td style="width: 55px; border: 0px; padding-left: 10px;">Nein = 0</td></tr>';
		$this->_acute_illness_legend .= '<tr><td style="width: 55px; border: 0px; padding-left: 10px;">Ja = 2</td></tr>';
		$this->_acute_illness_legend .= '</table>';
		
	}
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
	}
	
	public function create_form_must( $options = array(), $elementsBelongTo = null)
	{
		 
		$this->clearDecorators();
		//$this->addDecorator('HtmlTag', array('tag' => 'table'));
		$this->addDecorator('FormElements');
		//$this->addDecorator('Fieldset', array());
		$this->addDecorator('Form');
	
		if ( ! is_null($elementsBelongTo)) {
			$this->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		$this->addElement('note', 'label_form_title', array(
				'value' => $this->title,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center', 
								'style' => 'width: 700px;'))
				),
		));
		$this->addElement('note', 'elem_sep0', array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'width: 700px; line-height: 5px;'))
				),
		));
		
		$this->addElement('note', 'label_form_title_italic', array(
				'value' => $this->title_italic,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'width: 700px; font-style: italic; font-size: 14px;'))
				),
		));
		
		$this->addElement('note', 'elem_sep1', array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
						'style' => 'width: 700px; line-height: 10px;'))
				),
		));
		
		$this->addElement('note', 'label_current_weight', array(
				'value' => $this->translate('label_current_weight'),
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
						'style' => 'width: 250px; line-height: 27px; float: left; font-size: 14px;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 700px;',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND),
						)
				)
		));
		
		$this->addElement('text', 'current_weight', array(
				'value'        => $options['form_id'] ? $options['current_weight'] : '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				// 		    'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array(
								'tag' => 'div',
								'style'=>'width: 142px; float: left;')),
				),
				'style' => 'width: 125px; border: 0px; border-bottom: 1px solid #000; background: #fff; font-size: 14px; height: 16px;'
		));
		
		$this->addElement('note', 'label_now_date', array(
				'value' => $this->translate('label_now_date'),
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'width: 45px; line-height: 27px; float: left; font-size: 14px;')),
				)
		));
		
		$this->addElement('text', 'now_date', array(
				'value'        => $options['form_id'] ? ($options['now_date'] != '0000-00-00 00:00:00' ? date('d.m.Y', strtotime($options['now_date'])) : '')  : date('d.m.Y'),
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array(
								'tag' => 'div',
								'style'=>'width: 175px; float: left;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 700px;',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND),
						),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
				'class' => 'must_date',
				'style' => 'width: 100px; padding-left: 10px; border: 0px; border-bottom: 1px solid #000; background: #fff; font-size: 14px;  height: 16px;',
		));
		
		$this->addElement('note', 'elem_sep2', array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'line-height: 10px;'))
				),
		));
		
		$this->addElement('note', 'label_last_3_6_month_weight', array(
				'value' => $this->translate('label_last_3_6_month_weight'),
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'width: 250px; line-height: 27px; float: left;  font-size: 14px;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 700px;',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND),
						)
				)
		));
		
		$this->addElement('text', 'last_3_6_month_weight', array(
				'value'        => $options['form_id'] ? $options['last_3_6_month_weight'] : '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				// 		    'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array(
								'tag' => 'div',
								'style'=>'width: 142px; float: left;')),
				),
				'style' => 'width: 125px; border: 0px; border-bottom: 1px solid #000; background: #fff; font-size: 14px; height: 16px;',
		));
		
		$this->addElement('note', 'label_past_date', array(
				'value' => $this->translate('label_past_date'),
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'width: 45px; line-height: 27px; float: left;  font-size: 14px;')),
				)
		));
		
		$this->addElement('text', 'past_date', array(
				'value'        => $options['form_id'] ? ($options['past_date'] != '0000-00-00 00:00:00' ? date('d.m.Y', strtotime($options['past_date'])) : '') : '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array(
								'tag' => 'div',
								'style'=>'width: 175px; float: left;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 700px;',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND),
						),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
				'class' => 'must_date',
				'style' => 'width: 100px; border: 0px; padding-left: 10px; border-bottom: 1px solid #000; background: #fff; font-size: 14px; height: 16px;',
		));
		
		$this->addElement('note', 'label_height', array(
				'value' => $this->translate('label_height'),
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'width: 250px; line-height: 27px; float: left;  font-size: 14px;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 700px;',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND),
						)
				)
		));
		
		$this->addElement('text', 'height', array(
				'value'        => $options['height'] != '' ? $options['height'] : '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				// 		    'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array(
								'tag' => 'div',
								'style'=>'width: 142px; float: left;')),
				),
				'style' => 'width: 125px; border: 0px; border-bottom: 1px solid #000; background: #fff; font-size: 14px; height: 16px;',
		));
		
		$this->addElement('note', 'label_unit_height', array(
				'value' => 'm',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'width: 5px; line-height: 27px; float: left; font-size: 14px;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 700px;',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND),
						),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				)
		));
		
		$this->addElement('note', 'label_square_height', array(
				'value' => $this->translate('label_square_height').'&sup2;',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'width: 250px; line-height: 27px; float: left;  font-size: 14px;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 700px;',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND),
						)
				)
		));
		
		$this->addElement('text', 'square_height', array(
				'value'        => $options['form_id'] ? $options['square_height'] : '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				// 		    'validators'   => array('NotEmpty'),
				'readOnly' => true,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array(
								'tag' => 'div',
								'style'=>'width: 142px; float: left;')),
				),
				'style' => 'width: 125px; border: 0px; border-bottom: 1px solid #000; background: #fff; font-size: 14px; height: 16px;',
		));
		
		$this->addElement('note', 'label_unit_square_height', array(
				'value' => 'm&sup2;',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'width: 5px; line-height: 27px; float: left; font-size: 14px;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 700px;',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND),
						),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				)
		));
		
		$this->addElement('note', 'elem_sep3', array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'line-height: 40px; border-bottom: 1px solid #000; margin-bottom: 20px; width: 550px;'))
				),
		));
		
		$subtable = new Zend_Form_SubForm();
		//$subform->removeDecorator('Fieldset');
		$subtable->setDecorators( array(
				'FormElements',
				array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable', 'cellpadding'=>"0", 'cellspacing'=>"0", 'style' => 'width: 660px; font-size: 14px;')),
				//'Fieldset',
		));
		
		$subsubrow = $this->subFormTableRow();
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border: 0px;';
			
		$elementDecorators[$i][$j]['colspan'] = "7";
		$subsubrow->addElement('note', 'label_bmi', array(
				'value' => $this->translate('label_bmi'),
				'decorators' => $elementDecorators
		));
		$subtable->addSubForm($subsubrow, 'bmi_label');
		
		$subsubrow = $this->subFormTableRow();
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 50px; line-height: 17px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border: 0px; width: 50px; height: 80px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'bmi_text', array(
				'value' => $this->translate('bmi_text'),
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array('Label', array('placement' => 'APPEND', 'escape' => false, 'style' => 'border: 0px;'));
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 280px; line-height: 17px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; width: 280px; border: 0px; height: 80px; vertical-align: middle;';
		
		$subsubrow->addElement('text', 'bmi', array(
				'label'        => $this->translate('bmi_unit').'&sup2;',
				'value'        => $options['form_id'] ? $options['bmi'] : '',
				'required'     => false,
				'readonly'     => true,
				'id' => 'bmi',
				'decorators' => $elementDecorators,
				'style' => 'margin: 0px; border: 0px; background: #fff; font-size: 14px; height: 16px; padding: 0px;border-bottom: 1px solid #000; width: 100px;'
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'background: #71C671; border-radius: 5px; width: 160px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'width: 160px; background: #F0FFF0; padding: 0px; border: 0px; height: 80px; color: #fff;';
		
		$elementDecorators[$i][$j]['colspan'] = '2';
		
		$subsubrow->addElement('note', 'bmi_legend', array(
				'value' => $this->_bmi_legend,
				'escape' => false,
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('ldiv' => 'HtmlTag'), array('tag' => 'div',
											'style' => 'background: #71C671; height: 15px; width: 30px;'));		
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 0px; border: 0px; background: #F0FFF0; width: 30px; height: 80px; vertical-align: middle';
		
		$subsubrow->addElement('note', 'bmi_arrow_left', array(
				'value' => '',
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('ldiv' => 'HtmlTag'), array('tag' => 'div',
				'style' => 'height: 0px; width: 0px; background: transparent; border: 10px solid transparent;  border-left-width: 20px; border-right-width: 0px; border-left-color: #71C671; '));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 0px; border: 0px; background: #F0FFF0; width: 20px; height: 80px; vertical-align: middle';
		
		$subsubrow->addElement('note', 'bmi_arrow_right', array(
				'value' => '',
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 14px; padding-top: 23px; padding-right: 46px; padding-bottom: 23px; padding-left: 46px; background: lightgray; border-radius: 5px; height: 16px; border: 2px solid #228B22; text-align: center;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 0px; border: 0px; width: 120px; height: 80px; background: #DBFEF8; vertical-align: middle; padding-left: 5px;';
		
		/*$subsubrow->addElement('note', 'bmi_score', array(
				'value' => 'test',
				'decorators' => $elementDecorators
		));*/
		$subsubrow->addElement('text', 'bmi_score', array(
				'label'        => null,
				'value'        => $options['form_id'] ? $options['bmi_score'] : '',
				'required'     => false,
				'readonly'     => true,
				'id' => 'bmi_score',
				'decorators' => $elementDecorators,
				'style' => 'margin: 0px; border: 0px; background: transparent; font-size: 14px; height: 16px; padding: 0px; width: 10px;'
		));		
		
		$subtable->addSubForm($subsubrow, 'bmi');
		
		$subsubrow = $this->subFormTableRow();
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border: 0px; width: 50px; height: 40px; vertical-align: middle;';
		
		$elementDecorators[$i][$j]['colspan'] = '7';
		
		$subsubrow->addElement('note', 'sep_row1', array(
				'value' => '&nbsp;',
				'decorators' => $elementDecorators
		));
		$subtable->addSubForm($subsubrow, 'sep_row1');
		
		$subsubrow = $this->subFormTableRow();
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border: 0px;';
			
		$elementDecorators[$i][$j]['colspan'] = "7";
		$subsubrow->addElement('note', 'label_last_3_6_month_weight_proc', array(
				'value' => $this->translate('label_last_3_6_month_weight_proc'),
				'decorators' => $elementDecorators
		));
		$subtable->addSubForm($subsubrow, 'last_3_6_month_weight_label_proc');
		
		$subsubrow = $this->subFormTableRow();
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array('Label', array('placement' => 'APPEND', 'escape' => false, 'style' => 'border: 0px;'));
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 280px; line-height: 17px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; width: 330px; border: 0px; height: 80px; vertical-align: middle;';
		
		$elementDecorators[$i][$j]['colspan'] = '2';
		
		$elementDecorators[$i][$j]['rowspan'] = '2';
		
		$subsubrow->addElement('text', 'last_3_6_month_weight_proc', array(
				'label'        => '%',
				'value'        => $options['form_id'] ? $options['last_3_6_month_weight_proc'] : '',
				'required'     => false,
				'readonly'     => true,
				'id' => 'last_3_6_month_weight_proc',
				'decorators' => $elementDecorators,
				'style' => 'margin: 0px; border: 0px; background: #fff; font-size: 14px; height: 16px; padding: 0px;border-bottom: 1px solid #000; width: 100px;'
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'height: 38px; line-height: 20px; width: 160px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'font-size: 9px; background: #F0FFF0; padding: 0px; border: 0px; width: 160px; height: 40px; color: #464646; text-align: center;';
		
		$elementDecorators[$i][$j]['colspan'] = '2';
		
		$subsubrow->addElement('note', 'last_3_6_month_weight_proc_legend_first', array(
				'value' => $this->translate('last_3_6_month_weight_proc_legend_first'),
				'escape' => false,
				'decorators' => $elementDecorators
		));
		

		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('ldiv' => 'HtmlTag'), array('tag' => 'div',
				'style' => 'background: #71C671; height: 15px; width: 30px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 0px; border: 0px; background: #F0FFF0; width: 30px; height: 80px; vertical-align: middle';
		
		$elementDecorators[$i][$j]['rowspan'] = '2';
		
		$subsubrow->addElement('note', 'last_3_6_month_weight_proc_arrow_left', array(
				'value' => '',
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('ldiv' => 'HtmlTag'), array('tag' => 'div',
				'style' => 'height: 0px; width: 0px; background: transparent; border: 10px solid transparent;  border-left-width: 20px; border-right-width: 0px; border-left-color: #71C671; '));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 0px; border: 0px; background: #F0FFF0; width: 20px; height: 80px; vertical-align: middle';
		
		$elementDecorators[$i][$j]['rowspan'] = '2';
		
		$subsubrow->addElement('note', 'last_3_6_month_weight_proc_arrow_right', array(
				'value' => '',
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 14px; padding-top: 23px; padding-right: 46px; padding-bottom: 23px; padding-left: 46px; background: lightgray; border-radius: 5px; height: 16px; border: 2px solid #228B22; text-align: center;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 0px; border: 0px; width: 120px; height: 80px; background: #DBFEF8; vertical-align: middle; padding-left: 5px;';
		
		$elementDecorators[$i][$j]['rowspan'] = '2';
		
		/*$subsubrow->addElement('note', 'last_3_6_month_weight_score', array(
				'value' => 'test',
				'decorators' => $elementDecorators
		));*/
		
		$subsubrow->addElement('text', 'last_3_6_month_weight_proc_score', array(
				'label'        => null,
				'value'        => $options['form_id'] ? $options['last_3_6_month_weight_proc_score'] : '',
				'required'     => false,
				'readonly'     => true,
				'id' => 'last_3_6_month_weight_proc_score',
				'decorators' => $elementDecorators,
				'style' => 'margin: 0px; border: 0px; background: transparent; font-size: 14px; height: 16px; padding: 0px; width: 10px;'
		));
		
		$subtable->addSubForm($subsubrow, 'last_3_6_month_weight_proc_row1');
		
		$subsubrow = $this->subFormTableRow();
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'background: #71C671; border-radius: 5px; width: 160px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'background: #F0FFF0; padding: 0px; border: 0px; width: 160px; height: 80px; color: #fff;';
		
		$elementDecorators[$i][$j]['colspan'] = '2';
		
		$subsubrow->addElement('note', 'last_3_6_month_weight_proc_legend', array(
				'value' => $this->_last_3_6_month_weight_proc_legend,
				'escape' => false,
				'decorators' => $elementDecorators
		));		
		
		$subtable->addSubForm($subsubrow, 'last_3_6_month_weight_proc_row2');
		
		$subsubrow = $this->subFormTableRow();
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border: 0px; width: 50px; height: 40px; vertical-align: middle;';
		
		$elementDecorators[$i][$j]['colspan'] = '7';
		
		$subsubrow->addElement('note', 'sep_row2', array(
				'value' => '&nbsp;',
				'decorators' => $elementDecorators
		));
		$subtable->addSubForm($subsubrow, 'sep_row2');
		
		$subsubrow = $this->subFormTableRow();
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border: 0px;';
			
		$elementDecorators[$i][$j]['colspan'] = "7";
		$subsubrow->addElement('note', 'label_acute_illness', array(
				'value' => $this->translate('label_acute_illness'),
				'decorators' => $elementDecorators
		));
		$subtable->addSubForm($subsubrow, 'acute_illness_label');
		
		$subsubrow = $this->subFormTableRow();
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array('Label', array('placement' => 'PREPEND', 'style' => 'display: inline-block; width: 40px;'));
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 280px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; width: 50px; border: 0px; height: 40px; vertical-align: bottom;';
		
		$elementDecorators[$i][$j]['colspan'] = '2';
		
		$subsubrow->addElement('multiCheckbox', 'acute_illness_yes', array(
				'label'      => 'JA',
				'required'   => false,
				'multiOptions' => array('yes' => ''),
				'id' => 'acute_illness_yes',
				//'label_style' => 'border-bottom: 1px solid #000;',
				'value' => $options['form_id'] ? $options['acute_illness_yes'] : '',
				'style' => 'margin-left: 5px;',
				'decorators' => $elementDecorators,
				'onChange' => "calcscore(\$(this))",
		
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'background: #F0FFF0; line-height: 12px; padding-left: 5px; padding-right: 5px; width: 70px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'font-size: 9px; color: #464646; background: #F0FFF0; padding: 0px; border: 0px; width: 80px; height: 80px; vertical-align: middle;';
		
		$elementDecorators[$i][$j]['rowspan'] = '2';
		
		$subsubrow->addElement('note', 'acute_illness_legend_first', array(
				'value' => $this->translate('acute_illness_legend_first'),
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'padding-top: 5px; background: #71C671; border-radius: 5px; width: 80px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'background: #F0FFF0; padding: 0px; border: 0px; width: 80px; height: 80px; color: #fff; vertical-align: middle;';
		
		$elementDecorators[$i][$j]['rowspan'] = '2';
		
		$subsubrow->addElement('note', 'acute_illness_legend', array(
				'value' => $this->_acute_illness_legend,
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('ldiv' => 'HtmlTag'), array('tag' => 'div',
				'style' => 'background: #71C671; height: 15px; width: 30px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 0px; border: 0px; background: #F0FFF0; width: 30px; height: 80px; vertical-align: middle';
		
		$elementDecorators[$i][$j]['rowspan'] = '2';
		
		$subsubrow->addElement('note', 'acute_illness_arrow_left', array(
				'value' => '',
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('ldiv' => 'HtmlTag'), array('tag' => 'div',
				'style' => 'height: 0px; width: 0px; background: transparent; border: 10px solid transparent;  border-left-width: 20px; border-right-width: 0px; border-left-color: #71C671; '));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 0px; border: 0px; background: #F0FFF0; width: 20px; height: 80px; vertical-align: middle';
		
		$elementDecorators[$i][$j]['rowspan'] = '2';
		
		$subsubrow->addElement('note', 'acute_illness_arrow_right', array(
				'value' => '',
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 14px; padding-top: 23px; padding-right: 46px; padding-bottom: 23px; padding-left: 46px; background: lightgray; border-radius: 5px; height: 16px; border: 2px solid #228B22; text-align: center;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 0px; border: 0px; width: 120px; height: 80px; background: #DBFEF8; vertical-align: middle; padding-left: 5px;';
		
		$elementDecorators[$i][$j]['rowspan'] = '2';
		
		/*$subsubrow->addElement('note', 'acute_illness_score', array(
				'value' => 'test',
				'decorators' => $elementDecorators
		));*/
		
		$subsubrow->addElement('text', 'acute_illness_score', array(
				'label'        => null,
				'value'        => $options['form_id'] ? $options['acute_illness_score'] : '',
				'required'     => false,
				'readonly'     => true,
				'id' => 'acute_illness_score',
				'decorators' => $elementDecorators,
				'style' => 'margin: 0px; border: 0px; background: transparent; font-size: 14px; height: 16px; padding: 0px; width: 10px;'
		));
		
		$subtable->addSubForm($subsubrow, 'acute_illness_row1');
		
		$subsubrow = $this->subFormTableRow();
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array('Label', array('placement' => 'PREPEND', 'style' => 'display: inline-block; width: 40px;'));
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 280px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; width: 50px; border: 0px; height: 40px; vertical-align: top;';
		
		$elementDecorators[$i][$j]['colspan'] = '2';
		
		$subsubrow->addElement('multiCheckbox', 'acute_illness_no', array(
				'label'      => 'NEIN',
				'required'   => false,
				'multiOptions' => array('no' => ''),
				//'label_style' => 'border-bottom: 1px solid #000;',
				'value' => $options['form_id'] ? $options['acute_illness_no'] : '',
				'style' => 'margin-left: 5px;',
				'id' => 'acute_illness_no',
				'decorators' => $elementDecorators,
				'onChange' => "calcscore(\$(this))",
		
		));
		
		$subtable->addSubForm($subsubrow, 'acute_illness_row2');
		
		$subsubrow = $this->subFormTableRow();
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border: 0px; width: 50px; height: 40px; vertical-align: middle;';
		
		$elementDecorators[$i][$j]['colspan'] = '7';
		
		$subsubrow->addElement('note', 'sep_row3', array(
				'value' => '&nbsp;',
				'decorators' => $elementDecorators
		));
		$subtable->addSubForm($subsubrow, 'sep_row3');
		
		$subsubrow = $this->subFormTableRow();
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'font-size: 12px; padding: 1px; border: 0px; width: 50px; height: 80px; vertical-align: middle;';
		
		$elementDecorators[$i][$j]['colspan'] = '3';
		
		$subsubrow->addElement('note', 'total_text', array(
				'value' => $this->translate('total_text'),
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'background: #DBFEF8; border-radius: 5px; height: 14px; width: 90px; padding-top: 33px; padding-bottom: 33px; padding-left: 5px; width: 65px;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'background: #fff; padding: 0px; border: 0px; width: 20px; height: 80px; color: #464646; vertical-align: middle; padding-left: 60px;';
		
		$elementDecorators[$i][$j]['colspan'] = '3';
		
		$subsubrow->addElement('note', 'total_legend', array(
				'value' => $this->translate('total_legend'),
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 14px; padding-top: 23px; padding-right: 46px; padding-bottom: 23px; padding-left: 46px; background: #fff; border-radius: 5px; height: 16px; border: 2px solid #228B22; text-align: center;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 0px; border: 0px; width: 120px; height: 80px; background: #DBFEF8; vertical-align: middle; padding-left: 5px;';
		
		/*$subsubrow->addElement('note', 'total_score', array(
				'value' => 'test',
				'decorators' => $elementDecorators
		));*/
		
		$subsubrow->addElement('text', 'total_score', array(
				'label'        => null,
				'value'        => $options['form_id'] ? $options['total_score'] : '',
				'required'     => false,
				'readonly'     => true,
				'id' => 'total_score',
				'decorators' => $elementDecorators,
				'style' => 'margin: 0px; border: 0px; background: transparent; font-size: 14px; height: 16px; padding: 0px; width: 10px;'
		));
		
		$subtable->addSubForm($subsubrow, 'total');
		
		$this->addSubForm($subtable, 'must_table');
		
		$subtable = new Zend_Form_SubForm();
		//$subform->removeDecorator('Fieldset');
		$subtable->setDecorators( array(
				'FormElements',
				array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable', 'cellpadding'=>"0", 'cellspacing'=>"0", 'style' => 'width: 655px; font-size: 14px;')),
				//'Fieldset',
		));
		
		$subsubrow = $this->subFormTableRow();
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border: 0px; width: 50px; height: 30px; vertical-align: middle;';
		
		$elementDecorators[$i][$j]['colspan'] = '3';
		
		$subsubrow->addElement('note', 'sep_row_top', array(
				'value' => '&nbsp;',
				'decorators' => $elementDecorators
		));
		$subtable->addSubForm($subsubrow, 'sep_row_top');
		
		$subsubrow = $this->subFormTableRow();
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: green; padding: 1px; border: 0px; width: 100px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'score_0', array(
				'value' => $this->translate('score_0'),
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: green; padding: 1px; border: 0px; width: 5px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'sep_equal', array(
				'value' => ' = ',
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: green; padding: 1px; border: 0px; width: 150px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'risc_level_0', array(
				'value' => $this->translate('risc_level_0'),
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
// 		$elementDecorators[$i] =  array(array("img" => "HtmlTag"), array(
// 				"tag" => "img",
// 				"src" => APP_BASE."/images/arrow_green.jpg",
// 				"align" => "middle",
// 				"width" => "16",
// 				"height" => "15"
// 		));
		$i++;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: green; padding: 1px; border: 0px; width: 400px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'todo_text_0', array(
				'value' => $this->translate('todo_text_0'),
				'escape' => false,
				'decorators' => $elementDecorators
		));
		
		$subtable->addSubForm($subsubrow, 'first_legend');
		
		$subsubrow = $this->subFormTableRow();
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: #efc313; padding: 1px; border: 0px; width: 100px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'score_1', array(
				'value' => $this->translate('score_1'),
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: #efc313; padding: 1px; border: 0px; width: 5px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'sep_equal', array(
				'value' => ' = ',
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: #efc313; padding: 1px; border: 0px; width: 150px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'risc_level_1', array(
				'value' => $this->translate('risc_level_1'),
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
// 		$elementDecorators[$i] =  array(array("img" => "HtmlTag"), array(
// 				"tag" => "img",
// 				"src" => APP_BASE."/images/arrow_orange.jpg",
// 				"align" => "middle",
// 				"width" => "16",
// 				"height" => "15"
// 		));
		$i++;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: #efc313; padding: 1px; border: 0px; width: 400px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'todo_text_1', array(
				'value' => $this->translate('todo_text_1'),
				'escape' => false,
				'decorators' => $elementDecorators
		));
		
		$subtable->addSubForm($subsubrow, 'second_legend');
		
		$subsubrow = $this->subFormTableRow();
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: red; padding: 1px; border: 0px; width: 100px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'score_2', array(
				'value' => $this->translate('score_2'),
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: red; padding: 1px; border: 0px; width: 5px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'sep_equal', array(
				'value' => ' = ',
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: red; padding: 1px; border: 0px; width: 150px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'risc_level_2', array(
				'value' => $this->translate('risc_level_2'),
				'decorators' => $elementDecorators
		));
		
		$elementDecorators = array();
		$i = 0;
// 		$elementDecorators[$i] =  array(array("img" => "HtmlTag"), array(
// 				"tag" => "img",
// 				"src" => APP_BASE."/images/arrow_red.jpg",
// 				"align" => "middle",
// 				"width" => "16",
// 				"height" => "15"
// 		));
		$i++;
		$elementDecorators[$i] = 'ViewHelper';
		
		$i++;
		
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'color: red; padding: 1px; border: 0px; width: 400px; height: 30px; vertical-align: middle;';
		
		$subsubrow->addElement('note', 'todo_text_2', array(
				'value' => $this->translate('todo_text_2'),
				'decorators' => $elementDecorators
		));
		
		$subtable->addSubForm($subsubrow, 'third_legend');
		
		$subsubrow = $this->subFormTableRow();		
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border: 0px; width: 50px; height: 30px; vertical-align: middle;';
		
		$elementDecorators[$i][$j]['colspan'] = '3';
		
		$subsubrow->addElement('note', 'sep_row_down', array(
				'value' => '&nbsp;',
				'decorators' => $elementDecorators
		));
		$subtable->addSubForm($subsubrow, 'sep_row_down');
		
		$this->addSubForm($subtable, 'legend_table');
		
		//add action buttons
		$actions = $this->_create_formular_actions($options['formular'] , 'formular');
		$this->addSubform($actions, 'form_actions');
	
		return $this;
	
	
	}
	
	private function _create_formular_actions($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->clearDecorators()
		->setDecorators( array(
				'FormElements',
				array('HtmlTag',array('tag'=>'div', 'class' => 'formular_actions')),
		));
			
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
	
		$el = $this->createElement('button', 'button_action', array(
			 'type'         => 'submit',
			 'value'        => 'save',
			 // 	        'content'      => $this->translate('submit'),
			 'label'        => $this->translator->translate('kh_save'),
			 // 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
			 'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value; form_submit_validate();',
			 'decorators'   => array('ViewHelper'),
	
		));
		$subform->addElement($el, 'save');
	
			
		/*$el = $this->createElement('button', 'button_action', array(
			 'type'         => 'submit',
			 'value'        => 'printpdf',
			 // 	        'content'      => $this->translate('submit'),
			 'label'        => $this->translator->translate('kh_generate_pdf'),
			 // 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
			 'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
			 'decorators'   => array('ViewHelper'),
	
		));
		$subform->addElement($el, 'printpdf');*/
			
			
		return $subform;
	
	}
	
	public function save_form_nutritional_status($ipid = null, array $data = array())
	{
		if (empty($ipid)) {
			throw new Exception('Contact Admin, formular cannot be saved.', 0);
		}
		if($data['now_date'] != "")
		{
			$data['now_date'] = date('Y-m-d', strtotime($data['now_date']));
		}
		
		if($data['past_date'] != "")
		{
			$data['past_date'] = date('Y-m-d', strtotime($data['past_date']));
		}
		
		$entitybs  = new PatientNutritionalStatus();
		$nutritionalstatus =  $entitybs->findOrCreateOneByIpidAndId($ipid, $id, $data);
	
			return $nutritionalstatus;
		}
	
}