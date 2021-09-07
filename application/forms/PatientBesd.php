<?php
/**
 * 
 * @author carmen
 * 
 * 31.08.2018
 *
 */
class Application_Form_PatientBesd extends Pms_Form
{
	protected $title_center = null;
	
	protected $_catscores = null;
	
	protected $_rowspanscore = null;
	
	public function __construct($options = null)
	{
		
		//print_r($this->_patientMasterData); exit;
		parent::__construct($options);
		$this->title_center = '<h2>BEurteilung von Schmerzen bei Demenz (BESD)</h2>'; 
		
		if (isset($options['_catscores'])) {
			$this->_catscores = $options['_catscores'];
			unset($options['_catscores']);
		}
		
		if (isset($options['_rowspanscore'])) {
			$this->_rowspanscore = $options['_rowspanscore'];
			unset($options['_rowspanscore']);
		}
		
	}
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
	}
	
	public function create_form_besdsurvey( $options = array(), $elementsBelongTo = null)
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
		
		$this->addElement('note', 'label_form_title_center', array(
				'value' => $this->title_center,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center', 
								'style' => 'width: 600px; text-align: center;'))
				),
		));
		
		$this->addElement('note', 'elem_sep1', array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
						'style' => 'width: 600px; line-height: 10px;'))
				),
		));
		
		$this->addElement('Checkbox', 'calm', array(
				'label' => $this->translate('calm'),
				'value' => $options['calm']['value'],
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Label', array('placement' => 'APPEND')),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'div_calm',
								'style' => 'width: 80px; font-size: 16px; line-height: 21px; font-weight: bold;'
						))
				),
		
		));
		
		$this->addElement('Checkbox', 'mob', array(
				'label' => $this->translate('mob'),
				'value' => $options['mob']['value'],
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array('Label', array('placement' => 'APPEND')),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'div_mob',
								'style' => 'width: 405px; float: left; margin-right: 10px; font-size: 16px; line-height: 21px; font-weight: bold;'
						))
				),
		
		));
		
		$this->addElement('text', 'mob_by', array(
				'value'        => $options['mob_by']['value'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'div_mobby',
										'style' => 'float: left;'
						)),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', 'style'=>'clear: both; line-height: 21px;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
				'style' => 'margin: 0px; border: 0px; border-bottom: 1px solid #000; background: #fff; font-size: 14px; height: 16px;'
		));
		
		$this->addElement('note', 'elem_sep2', array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'line-height: 10px;'))
				),
		));
		
		unset($options['ipid']);
		unset($options['create_date']);
		unset($options['create_user']);
		unset($options['change_date']);
		unset($options['change_user']);
		unset($options['isdelete']);
		//print_r($options); exit;
		
		foreach($options as $kcat=>$vcat)
		{
			
			$subtable = new Zend_Form_SubForm();
			//$subform->removeDecorator('Fieldset');
			$subtable->setDecorators( array(
					'FormElements',
					array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable', 'cellpadding'=>"0", 'cellspacing'=>"0", "style"=>'width: 600px;')),
					//'Fieldset',
			));
			
			if($kcat == 'id') continue;
			$subsubrow = $this->subFormTableRow();
			
			$elementDecorators = array();
			$i = 0;
			$elementDecorators[$i] = 'ViewHelper';
			$i++;
			$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
			$i++;
			$j = 0;
			$elementDecorators[$i][$j]['data'] = 'HtmlTag';
			$j++;
				
			$elementDecorators[$i][$j]['tag'] = 'td';
			
			$elementDecorators[$i][$j]['style'] = 'padding: 1px; font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000;  border-left: 1px solid #000; border-right: 1px solid #000; width: 470px; font-size: 16px;';
			
			$elementDecorators[$i][$j]['colspan'] = "2";
			$subsubrow->addElement('note', 'label_'.$kcat, array(
					'value' => $this->translate($kcat),
					'decorators' => $elementDecorators
			));
			$elementDecorators[$i][$j]['colspan'] = "";
			$elementDecorators[$i][$j]['style'] = 'padding: 1px; font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000; border-right: 1px solid #000; width: 35px; text-align: center;';
			
			$subsubrow->addElement('note', 'label_no', array(
					'value' => 'nein',
					'decorators' => $elementDecorators
			));
			$elementDecorators[$i][$j]['style'] = 'padding: 1px; font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000; border-right: 1px solid #000; width: 35px; text-align: center;';
			$subsubrow->addElement('note', 'label_yes', array(
					'value' => 'ja',
					'decorators' => $elementDecorators
			));
			$elementDecorators[$i][$j]['style'] = 'padding: 1px; font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000; border-right: 1px solid #000; width: 51px; text-align: center;';
			$subsubrow->addElement('note', 'label_score', array(
					'value' => $this->translate('besd_score'),
					'decorators' => $elementDecorators
			));
	
			$subtable->addSubForm($subsubrow, $kcat.'head');
				$lastprop = end($vcat['colprop']['values']);
				$rowsprop = count($vcat['colprop']['values']);
				foreach($vcat['colprop']['values'] as $krow=>$vrow)
				{		
					
					$subsubrow = $this->subFormTableRow();
			
					$elementDecorators = array();
					$i = 0;
					$elementDecorators[$i] = 'ViewHelper';
					$i++;
					$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
					$i++;
					$j = 0;
					$elementDecorators[$i][$j]['data'] = 'HtmlTag';
					$j++;
						
					$elementDecorators[$i][$j]['tag'] = 'td';
					if($vrow == $lastprop)
					{
						$elementDecorators[$i][$j]['style'] = 'padding: 1px; border-left: 1px solid #000; border-right: 1px solid #000; width: 470px;';
					}
					else 
					{
						$elementDecorators[$i][$j]['style'] = 'padding: 1px; border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; width: 470px;';
					}
					
					$elementDecorators[$i][$j]['colspan'] = "2";
					$subsubrow->addElement('note', 'label_'.$vrow, array(
							'value' => $this->translate($vrow),
							'decorators' => $elementDecorators
					));
					$elementDecorators[$i][$j]['colspan'] = "";
					if($vrow == $lastprop)
					{
						$elementDecorators[$i][$j]['style'] = 'padding: 1px; border-right: 1px solid #000; width: 35px; text-align: center';
					}
					else 
					{
						$elementDecorators[$i][$j]['style'] = 'padding: 1px; border-bottom: 1px solid #000; border-right: 1px solid #000; width: 35px; text-align: center';
					}
					$subsubrow->addElement('multiCheckbox', $krow.'_no', array(
					'label'      => null,
					'required'   => false,
					'multiOptions' => array('no' => ''),
					'value' => $options[$kcat]['value'][$krow],
					'decorators' => $elementDecorators,
					'onChange' => "calcscore(\$(this))",
	
					));
					if($vrow == $lastprop)
					{
						$elementDecorators[$i][$j]['style'] = 'padding: 1px; border-right: 1px solid #000; width: 35px; text-align: center';
					}
					else 
					{
						$elementDecorators[$i][$j]['style'] = 'padding: 1px; border-bottom: 1px solid #000; border-right: 1px solid #000; width: 35px; text-align: center';
					}
					$subsubrow->addElement('multiCheckbox', $krow.'_yes', array(
							'label'      => null,
							'required'   => false,
							'multiOptions' => array('yes' => ''),
							'value' => $options[$kcat]['value'][$krow],
							'decorators' => $elementDecorators,
							'class' => 'yesbox',
							'data-score' => $this->_catscores[$kcat][$vrow],
							'data-cat' => $kcat,
							'onChange' => "calcscore(\$(this))",
					
					));
					
					$elementDecorators[$i][$j]['style'] = 'padding: 1px; border-bottom: 1px solid #000; border-right: 1px solid #000;text-align: center; vertical-align: middle; font-size: 16px; width: 51px;  font-weigt: bold';
					
					if($score != $this->_catscores[$kcat][$vrow])
					{
						
						if(($rowsprop - $krow) == $this->_rowspanscore[$kcat]['count'][$this->_catscores[$kcat][$vrow]])
						{
							$elementDecorators[$i][$j]['style'] = 'padding: 1px; border-right: 1px solid #000;text-align: center; vertical-align: middle; font-size: 16px; width: 51px; font-weigt: bold';
						}
						
						$score = $this->_catscores[$kcat][$vrow];
						$elementDecorators[$i][$j]['rowspan'] = $this->_rowspanscore[$kcat]['count'][$this->_catscores[$kcat][$vrow]];
						$subsubrow->addElement('note', 'score', array(
								'value' => $this->_catscores[$kcat][$vrow],
								'decorators' => $elementDecorators
						));
					}
					else 
					{
						
						$elementDecorators[$i][$j]['rowspan'] = '';
					}
					
					
					$elementDecorators[$i][$j]['rowspan'] = '';
				
				$subtable->addSubForm($subsubrow, $krow);
			
		}
			//$rowsubform->addSubForm($subtable, $kcat);
			$this->addSubForm($subtable, $kcat);
			//exit;
		}
		
		$subtable = new Zend_Form_SubForm();
		//$subform->removeDecorator('Fieldset');
		$subtable->setDecorators( array(
				'FormElements',
				array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable', 'cellpadding'=>"0", 'cellspacing'=>"0", "style"=>"width: 600px;")),
				//'Fieldset',
		));
		
		$subsubrow = $this->subFormTableRow();
			
		$elementDecorators = array();
		$i = 0;
		$elementDecorators[$i] = 'ViewHelper';
		$i++;
		$elementDecorators[$i] = array('Label', array('placement' => 'APPEND'));
		$i++;
		$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
		$i++;
		$j = 0;
		$elementDecorators[$i][$j]['data'] = 'HtmlTag';
		$j++;
		
		$elementDecorators[$i][$j]['tag'] = 'td';
			
			
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; font-weight: bold; border-top: 1px solid #000; border-left: 1px solid #000; border-bottom: 1px solid #000; line-height: 22px; width: 235px; font-size: 16px;';
			
		$subsubrow->addElement('note', 'label_total_1', array(
				'value' => 'TOTAL',
				'decorators' => $elementDecorators
		));
		
		$elementDecorators[$i][$j]['style'] = 'padding-left: 0px; padding-right: 1px; padding-top: 1px; padding-bottom: 1 px; text-align: right; font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000; border-right: 1px solid #000; line-height: 22px; width: 234px; font-size: 16px;';
		$subsubrow->addElement('note', 'label_total_2', array(
				'value' => '/ von max.',
				'decorators' => $elementDecorators
		));
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border-top: 1px solid #000; border-bottom: 1px solid #000; border-right: 1px solid #000; line-height: 22px; width: 35px; font-size: 16px;';
		$subsubrow->addElement('note', 'label_no', array(
				'value' => '&nbsp;',
				'decorators' => $elementDecorators
		));
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border-top: 1px solid #000; border-bottom: 1px solid #000; border-right: 1px solid #000; line-height: 22px; width: 35px; font-size: 16px;';
		$subsubrow->addElement('note', 'label_yes', array(
				'value' => '&nbsp;',
				'decorators' => $elementDecorators
		));
		$elementDecorators[$i][$j]['style'] = 'padding: 1px; border-top: 1px solid #000; border-bottom: 1px solid #000; border-right: 1px solid #000; line-height: 22px; width: 51px; font-size: 16px;';
		
		$subsubrow->addElement('text', 'total', array(
				'label'        => '/10',
				'value'        => $options['total'],
				'required'     => false,
				'readonly'     => true,
				'id' => 'total',
				'decorators' => $elementDecorators,
				'style' => 'margin: 0px; border: 0px; background: #fff; font-size: 16px; padding: 0px;border-bottom: 1px solid #000; width: 20px;'
		));
			
		$subtable->addSubForm($subsubrow, 'total_row');
		$this->addSubForm($subtable, 'total_table');
	
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
			 'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
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
	
	public function save_form_besdsurvey($ipid = null, array $data = array())
	{
		if (empty($ipid)) {
			throw new Exception('Contact Admin, formular cannot be saved.', 0);
		}
		//print_r($data); exit;
		
		$entitybs  = new PatientBesd();
		$besdsurvey =  $entitybs->findOrCreateOneByIpidAndId($ipid, $id, $data);
	
			return $besdsurvey;
		}
	
}