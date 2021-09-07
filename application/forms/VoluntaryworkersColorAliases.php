<?php
/**
 * 
 * @author lore
 * 
 * 24.07.2019 ISPC-2401
 *
 */
class Application_Form_VoluntaryworkersColorAliases extends Pms_Form
{
	private $_color_alias_profiles = null;
	
	public function __construct($options = null)
	{	
		if (isset($options['_color_alias_profiles'])) {
		    $this->_color_alias_profiles = $options['_color_alias_profiles'];
			unset($options['_color_alias_profiles']);
		}
		//print_r($this->_patientMasterData); exit;
		parent::__construct($options);
		
	}
	
	public function isValid($data)
	{
        if($data['settingstable']['profile_settings']['margin_top'] && !is_numeric($data['settingstable']['profile_settings']['margin_top']))
		{
			return false;
		}
		elseif($data['settingstable']['profile_settings']['margin_bottom'] && !is_numeric($data['settingstable']['profile_settings']['margin_bottom']))
		{
			return false;
		}
		elseif($data['settingstable']['profile_settings']['margin_left'] && !is_numeric($data['settingstable']['profile_settings']['margin_left']))
		{
			return false;
		}
		elseif($data['settingstable']['profile_settings']['margin_right'] && !is_numeric($data['settingstable']['profile_settings']['margin_right']))
		{
			return false;
		}
		return parent::isValid($data);
	}
	
	public function create_form_addvolunatryworkerscoloraliases( $options = array(), $elementsBelongTo = null)
	{
	    
	    // get associated clients of current clientid START
	    $connected_client = VwGroupAssociatedClients::connected_parent($this->logininfo->clientid);
	    if($connected_client){
	        $clientid = $connected_client;
	    } else{
	        $clientid = $this->logininfo->clientid;
	    }
	    
	    
		if($options['id']['value'] != '')
		{
    		$fieldsetname = $this->translator->translate('voluntaryworkerscoloraliases_edit');
		}
		else 
		{
			$fieldsetname = $this->translator->translate('voluntaryworkerscoloraliases_add');

		}
		
		$this->clearDecorators();
		//$this->addDecorator('HtmlTag', array('tag' => 'table'));
		$this->addDecorator('FormElements');
		$this->addDecorator('Fieldset', array('legend' => $fieldsetname));
		$this->addDecorator('Form');		

		/* $this->setOptions(array(
				'elementsBelongTo' => $this->_block_name
		));	 */			
			
		//add hidden
		$this->addElement('hidden', 'block_name', array(
				'value' => $this->_block_name,
				'readonly' => true,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
				),
		));
		
		//add hidden
		$this->addElement('hidden', 'id', array(
				'value' => $options['id']['value'],
				'readonly' => true,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
				),
		));
		
		//add hidden
		$this->addElement('hidden', 'clientid', array(
		    'value' => $options['clientid']['value'] != '' ? $options['clientid']['value'] : $clientid,
				'readonly' => true,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
				),
		));
		
		$subtable = new Zend_Form_SubForm();
		//$subform->removeDecorator('Fieldset');
		$subtable->setDecorators( array(
				'FormElements',
				array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable', 'cellpadding'=>"0", 'cellspacing'=>"0", "style"=>'width: 600px;')),
				//'Fieldset',
		));
		
		
		if($this->_block_name == 'VOLUNTARYWORKERSCOLORALIASES')
		{	   
		    
	        $saved_colors = VoluntaryworkersColorAliasesTable::findAllVoluntaryworkerscoloraliases($clientid);
		    $saved_colors_ids = array_map(function($plan) {
		        return $plan['color'];
		    }, $saved_colors);

		   
		   $all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($clientid);
		        
		   
		   $unset_colors = array();
		        
			foreach($all_colors as $key=>$val)
			{
				if(!in_array($key, $saved_colors_ids))
				{
					$unset_colors[$key] = $val;
				}
			}
			
			//add hidden
			$this->addElement('hidden', 'unset_colors_nr', array(
					'value' => count($unset_colors),
					'readonly' => true,
					'decorators' => array(
							'ViewHelper',
							array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
					),
			));
			
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
				
				
			$subsubrow->addElement('note', 'label_color_alias', array(
					'value' => $this->translate('label_color_alias'),
					'decorators' => $elementDecorators
			));
				
			if($options['id']['value'])
			{
				$subsubrow->addElement('note', 'label_color_alias', array(
					'value' => "<b>".$all_colors[$options['color']['value']]."</b>",
					'decorators' => $elementDecorators
				));
				//add hidden
				$subsubrow->addElement('hidden', 'color', array(
						'label'      => null,
						'required'   => false,
						'value' => $options['color']['value'],
						'decorators' => $elementDecorators
				
				));
			}
			else 
			{			
					$subsubrow->addElement('select', 'color', array(
							'multiOptions' => $unset_colors,
							'value'            => $options['color']['value'],
							'decorators' => $elementDecorators
					
					));
			}
			
			$subtable->addSubForm($subsubrow, '1');
				
			$subsubrow = $this->subFormTableRow();
	
			$elementDecorators = array();
			$i = 0;
			$elementDecorators[$i] = 'ViewHelper';
			$i++;
			$elementDecorators[$i] = 'Errors';
			$i++;
			$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
			$i++;
			$j = 0;
			$elementDecorators[$i][$j]['data'] = 'HtmlTag';
			$j++;
				
			$elementDecorators[$i][$j]['tag'] = 'td';
			
			
			$subsubrow->addElement('note', 'label_colorname', array(
					'value' => $this->translate('label_colorname'),
					'decorators' => $elementDecorators
			));
			
		/*	$subsubrow->setOptions(array(
					'elementsBelongTo' => 'colorname'
			));
		*/	
			$subsubrow->addElement('text', 'colorname', array(
			'label'      => null,
			'required'   => false,
			'value' => $options['colorname']['value'],
			'decorators' => $elementDecorators,
			));
			
			$subtable->addSubForm($subsubrow, '2');
		
			//$rowsubform->addSubForm($subtable, $kcat);
			$this->addSubForm($subtable, 'settingstable');
			//exit;
		}
		
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
				
		return $subform;
	
	}
	
	public function save_form_voluntaryworkerscoloraliases(array $data = array())
	{
		//print_r($data); exit;
		if($data['id'] == '')
		{
			$data['id'] = null;
		}
		
		$table = 'VoluntaryworkersColorAliasesTable';
		
		
		foreach($data['settingstable'] as $kr=>$vr)
		{
			if($kr == 'colorname' || $kr == 'profile_settings')
			{
				$data[$kr] = $vr;
				continue;
			}
			foreach($vr as $key=>$val)
			{
				$data[$key] = $val;
			}
		}
		
		unset($data['settingstable']);
		unset($data['block_name']);
		unset($data['formular']);
		//var_dump($data);exit;
		$entity = $table::getInstance()->findOrCreateOneBy(['id'], [$data['id']], $data);
	
		return $entity;
	}
	
		
}