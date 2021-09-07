<?php
/**
 * 
 * @author carmen
 * 
 * 06.08.2019
 * created for ISPC-2454 
 * updated for ISPC-2396 by carmen 08.10.2019
 * used also in ISPC-2465
 */
class Application_Form_FormGroupButton extends Pms_Form
{
	protected $_setgroup_but = null;
	
	protected $_external_obj = null;
	
	protected $_elementsBelongTo = null;
	
	protected $_forme_mode;
	
	public function __construct($options = null)
	{	
		if (isset($options['_setgroup_but'])) {
			$this->_setgroup_but = $options['_setgroup_but'];
			unset($options['_setgroup_but']);
		}
		if (isset($options['_external_obj'])) {
			$this->_external_obj = $options['_external_obj'];
			unset($options['_external_obj']);
		}
		
		if (isset($options['elementsBelongTo'])) {
			$this->_elementsBelongTo = $options['elementsBelongTo'];
			unset($options['elementsBelongTo']);
		}
		
		if (isset($options['_forme_mode'])) {
			$this->_forme_mode = $options['_forme_mode'];
			unset($options['_forme_mode']);
		}
	
		parent::__construct($options);
		
		//$this->_kvheader_lang = $this->translate ('kvheader_lang');

	}	
	
	public function isValid($data)
	{
	    
	    return parent::isValid($data);
	    
	}	
	
	public function _create_form_groupbutton($options = array(), $elementsBelongTo = null)
	{
	    $subtable = new Zend_Form_SubForm();
		$subtable->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
		));
		
		if($this->_elementsBelongTo)
		{
			$this->__setElementsBelongTo($subtable, $this->_elementsBelongTo );
		}
		else if ( ! is_null($elementsBelongTo)) {
			$subtable->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}

		for($kb =1 ; $kb <= $this->_setgroup_but['butnr']; $kb++)
		{
			if($this->_setgroup_but['y_offset'][$kb-1] > 0)
			{
				$posy = $this->_setgroup_but['first_but_pos_top']+$this->_setgroup_but['y_offset'][$kb-1];
			}
			else
			{
				$posy = $this->_setgroup_but['first_but_pos_top'];
			}
			if($this->_setgroup_but['x_offset'][$kb-1] > 0)
			{
				$posx = $this->_setgroup_but['first_but_pos_left']+$this->_setgroup_but['x_offset'][$kb-1];
				
			}
			else
			{
				$posx = $this->_setgroup_but['first_but_pos_left'];
			}
			$posx_label = $posx +$this->_setgroup_but['width_dummy']+$this->_setgroup_but['x_offset_label'][$kb-1];
			$style = 'left: '. $posx;
			$style .= 'px; top: ' . $posy. 'px;';
			//$style .= 'px; font-size: ' . $this->_setgroup_but['font_dummy_text'] .'px;';
			$style_label = 'left: '. $posx_label;
			$style_label .= 'px; top: ' . $posy. 'px;';
			$style_label .= ' width: ' . $this->_setgroup_but['label_width'][$kb-1]. 'px;';
			$style_label .= ' height: ' . $this->_setgroup_but['label_height'][$kb-1]. 'px;';
			if($this->_setgroup_but['label_wrap'][$kb-1])
			{
				$style_label .= ' line-height: 1';
			}
			if($kb == 1 && $kb != $this->_setgroup_but['butnr'])
			{
				switch($this->_setgroup_but['type'])
				{
					case 'radio':
						$empty = $subtable->createElement($this->_setgroup_but['type'], $this->_setgroup_but['name'].'[]', array(
								'label'        => 'test',
								'isArray' => true,
								'multiOptions' => array($kb => ''),
								'value' => $options[0] == $kb ? $kb : '0',
								'decorators' => array(
										'ViewHelper',
								),
								'class' => $this->_setgroup_but['name'],
								//'style' => 'display: none',
						));
					break;
					case 'checkbox':
						$empty = $subtable->createElement($this->_setgroup_but['type'], $this->_setgroup_but['name'].'[]', array(
								'label'        => $this->_setgroup_but['label_text'][$kb-1],
								'isArray' => true,
								'value' => !empty($this->_setgroup_but['values']) ? $this->_setgroup_but['values'][$kb-1] : $options[$kb-1] == $kb ? $kb : '0',
								'checkedValue' => !empty($this->_setgroup_but['values']) ? $this->_setgroup_but['values'][$kb-1] : $kb,
								'uncheckedValue' => '0',
								'decorators' => array(
										'ViewHelper',
								),
								'id' => $this->_setgroup_but['name'].$kb,
								'class' => $this->_setgroup_but['name'],
								//'style' => 'display: none',
						));
					break;
					default:
					break;
				}
				if($this->_forme_mode)
				{
					$empty->addDecorators(array(
							array('Label', array('placement' => APPEND, 'escape' => false)),
							array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'itemdiv',)),
							array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;', 'openOnly' => true)),
							array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'openOnly' => true)),
					));
				}
				else 
				{
					$empty->addDecorators(array(
							array('Label', array('placement' => APPEND, 'style' => 'display: none;')),
							array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'openOnly' => true, 'style' => 'position: relative;')),
							array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;', 'openOnly' => true)),
							array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'openOnly' => true)),
					));
				}
				$subtable->addElement($empty, '_arr_'.$kb);
			}
			else if($kb == 1 && $kb == $this->_setgroup_but['butnr'])
			{
				switch($this->_setgroup_but['type'])
				{
					case 'radio':
						$empty = $subtable->createElement($this->_setgroup_but['type'], $this->_setgroup_but['name'].'[]', array(
						'label'        => 'test',
						'isArray' => true,
						'multiOptions' => array($kb => ''),
						'value' => $options[0] == $kb ? $kb : '0',
						'decorators' => array(
								'ViewHelper',
						),
						'class' => $this->_setgroup_but['name'],
						//'style' => 'display: none',
						));
						break;
					case 'checkbox':
						$empty = $subtable->createElement($this->_setgroup_but['type'], $this->_setgroup_but['name'].'[]', array(
						'label'        => $this->_setgroup_but['label_text'][$kb-1],
						'isArray' => true,
						'value' => !empty($this->_setgroup_but['values']) ? $this->_setgroup_but['values'][$kb-1] : $options[$kb-1] == $kb ? $kb : '0',
						'checkedValue' => !empty($this->_setgroup_but['values']) ? $this->_setgroup_but['values'][$kb-1] : $kb,
						'uncheckedValue' => '0',
						'decorators' => array(
								'ViewHelper',
						),
						'id' => $this->_setgroup_but['name'].$kb,
						'class' => $this->_setgroup_but['name'],
						//'style' => 'display: none',
						));
						break;
					default:
						break;
				}
				if($this->_forme_mode)
				{
					if(!$this->_setgroup_but['has_external_obj'] && $this->_external_obj == "")
					{
						$empty->addDecorators(array(
								array('Label', array('placement' => APPEND, 'escape' => false)),
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'itemdiv',)),
								array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;')),
								array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'')),
						));
					}
					else 
					{
						$empty->addDecorators(array(
								array('Label', array('placement' => APPEND), 'style' => 'display: none;'),
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'position: relative;')),
								array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;')),
								//array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'')),
						));
					}
				}
				else 
				{
					$empty->addDecorators(array(
							array('Label', array('placement' => APPEND, 'style' => 'display: none;')),
							array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'openOnly' => true, 'style' => 'position: relative;')),
							array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;',)),
							array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'openOnly' => true)),
					));
				}
				$subtable->addElement($empty, '_arr_'.$kb);
			}
			else if($kb == $this->_setgroup_but['butnr'])
			{
				switch($this->_setgroup_but['type'])
				{
					case 'radio':
						$empty = $subtable->createElement($this->_setgroup_but['type'], $this->_setgroup_but['name'].'[]', array(
						'label'        => 'test',
						'isArray' => true,
						'multiOptions' => array($kb => ''),
						'value' => $options[0] == $kb ? $kb : '0',
						'decorators' => array(
								'ViewHelper',
						),
						'class' => $this->_setgroup_but['name'],
						//'style' => 'display: none',
						));
						break;
					case 'checkbox':
						$empty = $subtable->createElement($this->_setgroup_but['type'], $this->_setgroup_but['name'].'[]', array(
						'label'        => $this->_setgroup_but['label_text'][$kb-1],
						'isArray' => true,
						'value' => !empty($this->_setgroup_but['values']) ? $this->_setgroup_but['values'][$kb-1] : $options[$kb-1] == $kb ? $kb : '0',
						'checkedValue' => !empty($this->_setgroup_but['values']) ? $this->_setgroup_but['values'][$kb-1] : $kb,
						'uncheckedValue' => '0',
						'decorators' => array(
								'ViewHelper',
						),
						'id' => $this->_setgroup_but['name'].$kb,
						'class' => $this->_setgroup_but['name'],
						//'style' => 'display: none',
						));
						break;
					default:
						break;
				}
				if($this->_forme_mode)
				{
					if(!$this->_setgroup_but['has_external_obj'] && $this->_external_obj == "")
					{
						$empty->addDecorators(array(
								array('Label', array('placement' => APPEND, 'escape' => false)),
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'itemdiv',)),
								array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;', 'closeOnly' => true)),
								array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'closeOnly' => true)),
						));
					}
					else 
					{
						$empty->addDecorators(array(
								array('Label', array('placement' => APPEND, 'style' => 'display: none;')),
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'position: relative;')),
								array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;', 'closeOnly' => true)),
								//array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'closeOnly' => true)),
						));
					}
				}
				else
				{
					$empty->addDecorators(array(
							array('Label', array('placement' => APPEND, 'style' => 'display: none;')),
							array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'openOnly' => true, 'style' => 'position: relative;')),
					));
				}
				$subtable->addElement($empty, '_arr_'.$kb);
			}
			else 
			{
				switch($this->_setgroup_but['type'])
				{
					case 'radio':
						$empty = $subtable->createElement($this->_setgroup_but['type'], $this->_setgroup_but['name'].'[]', array(
						'label'        => 'test',
						'isArray' => true,
						'multiOptions' => array($kb => ''),
						'value' => $options[0] == $kb ? $kb : '0',
						'decorators' => array(
								'ViewHelper',
						),
						'class' => $this->_setgroup_but['name'],
						//'style' => 'display: none',
						));
						break;
					case 'checkbox':
						$empty = $subtable->createElement($this->_setgroup_but['type'], $this->_setgroup_but['name'].'[]', array(
						'label'        => $this->_setgroup_but['label_text'][$kb-1],
						'isArray' => true,
						'value' => !empty($this->_setgroup_but['values']) ? $this->_setgroup_but['values'][$kb-1] : $options[$kb-1] == $kb ? $kb : '0',
						'checkedValue' => !empty($this->_setgroup_but['values']) ? $this->_setgroup_but['values'][$kb-1] : $kb,
						'uncheckedValue' => '0',
						'decorators' => array(
								'ViewHelper',
						),
						'id' => $this->_setgroup_but['name'].$kb,
						'class' => $this->_setgroup_but['name'],
						//'style' => 'display: none',
						));
						break;
					default:
						break;
				}
				
				if($this->_forme_mode)
				{
					$empty->addDecorators(array(
							array('Label', array('placement' => APPEND, 'escape' => false)),
							array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'itemdiv',)),
					));
				}
				else 
				{
					$empty->addDecorators(array(
							array('Label', array('placement' => APPEND, 'style' => 'display: none;')),
							array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'openOnly' => true, 'style' => 'position: relative;')),
					));
				}
				
				$subtable->addElement($empty, '_arr_'.$kb);
			}
			
			if(!$this->_forme_mode)
			{	
				if($kb == $this->_setgroup_but['butnr'])
				{
					if(!$this->_setgroup_but['has_external_obj'] && $this->_external_obj == "")
					{
						if($this->_setgroup_but['has_dummy'] && !$this->_setgroup_but['label'])
						{
							$subtable->addElement('note', 'label_dummy'.$kb, array(
									'value' => $this->_setgroup_but['text_dummy'][$kb-1],
									'decorators' => array(
											'ViewHelper',
											array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'id' => $this->_setgroup_but['name'].'_dummy'.$kb, 'class' => $this->_setgroup_but['name'].'_dummy', 'style' => $style,)),
											array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
											array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;', 'closeOnly' => true)),
											array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'closeOnly' => true)),
									),
							));
						}
						else if($this->_setgroup_but['has_dummy'] && $this->_setgroup_but['label'])
						{
							$subtable->addElement('note', 'label_dummy'.$kb, array(
									'value' => $this->_setgroup_but['text_dummy'][$kb-1],
									'decorators' => array(
											'ViewHelper',
											array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'id' => $this->_setgroup_but['name'].'_dummy'.$kb, 'class' => $this->_setgroup_but['name'].'_dummy', 'style' => $style,)),
											//array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
									),
							));
							$subtable->addElement('note', 'text_label_dummy'.$kb, array(
									'value' => $this->_setgroup_but['label_text'][$kb-1],
									'decorators' => array(
											'ViewHelper',
											array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'class' => $this->_setgroup_but['name'].'_label', 'style' => $style_label,)),
											array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
											array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;', 'closeOnly' => true)),
											array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'closeOnly' => true)),
									),
							));
						}
						else
						{
							$subtable->addElement('note', 'text_label_dummy'.$kb, array(
									'value' => '&nbsp;',
									'decorators' => array(
											'ViewHelper',
											array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'id' => $this->_setgroup_but['name'].'_dummy'.$kb, 'class' => $this->_setgroup_but['name'].'_dummy', 'style' => $style,)),
											array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
											array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;', 'closeOnly' => true)),
											array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'closeOnly' => true)),
									),
							));
						}
					}
					else 
					{
						if($this->_setgroup_but['has_dummy'] && !$this->_setgroup_but['label'])
						{
							$subtable->addElement('note', 'label_dummy'.$kb, array(
									'value' => $this->_setgroup_but['text_dummy'][$kb-1],
									'decorators' => array(
											'ViewHelper',
											array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'id' => $this->_setgroup_but['name'].'_dummy'.$kb, 'class' => $this->_setgroup_but['name'].'_dummy', 'style' => $style,)),
											array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
											array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;', 'closeOnly' => true)),
									),
							));
						}
						else if($this->_setgroup_but['has_dummy'] && $this->_setgroup_but['label'])
						{
							$subtable->addElement('note', 'label_dummy'.$kb, array(
									'value' => $this->_setgroup_but['text_dummy'][$kb-1],
									'decorators' => array(
											'ViewHelper',
											array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'id' => $this->_setgroup_but['name'].'_dummy'.$kb, 'class' => $this->_setgroup_but['name'].'_dummy', 'style' => $style,)),
											//array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
									),
							));
							$subtable->addElement('note', 'text_label_dummy'.$kb, array(
									'value' => $this->_setgroup_but['label_text'][$kb-1],
									'decorators' => array(
											'ViewHelper',
											array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'class' => $this->_setgroup_but['name'].'_label', 'style' => $style_label,)),
											array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
											array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;', 'closeOnly' => true)),
									),
							));
						}
						else
						{
							$subtable->addElement('note', 'text_label_dummy'.$kb, array(
									'value' => '&nbsp;',
									'decorators' => array(
											'ViewHelper',
											array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'id' => $this->_setgroup_but['name'].'_dummy'.$kb, 'class' => $this->_setgroup_but['name'].'_dummy', 'style' => $style,)),
											array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
											array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'grouptd', 'style' => 'vertical-align: top;', 'closeOnly' => true)),
									),
							));
						}
					}
				}
				else
				{
					if($this->_setgroup_but['has_dummy'] && !$this->_setgroup_but['label'])
					{
						$subtable->addElement('note', 'label_dummy'.$kb, array(
								'value' => $this->_setgroup_but['text_dummy'][$kb-1],
								'decorators' => array(
										'ViewHelper',
										array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'id' => $this->_setgroup_but['name'].'_dummy'.$kb, 'class' => $this->_setgroup_but['name'].'_dummy', 'style' => $style,)),
										array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
								),
						));
					}
					else if($this->_setgroup_but['has_dummy'] && $this->_setgroup_but['label'])
					{
						$subtable->addElement('note', 'label_dummy'.$kb, array(
								'value' => $this->_setgroup_but['text_dummy'][$kb-1],
								'decorators' => array(
										'ViewHelper',
										array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'id' => $this->_setgroup_but['name'].'_dummy'.$kb, 'class' => $this->_setgroup_but['name'].'_dummy', 'style' => $style,)),
										//array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
								),
						));
						$subtable->addElement('note', 'text_label_dummy'.$kb, array(
								'value' => $this->_setgroup_but['label_text'][$kb-1],
								'decorators' => array(
										'ViewHelper',
										array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'class' => $this->_setgroup_but['name'].'_label', 'style' => $style_label,)),
										array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
								),
						));
					}
					else
					{
						$subtable->addElement('note', 'text_label_dummy'.$kb, array(
								'value' => '&nbsp;',
								'decorators' => array(
										'ViewHelper',
										array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'id' => $this->_setgroup_but['name'].'_dummy'.$kb, 'class' => $this->_setgroup_but['name'].'_dummy', 'style' => $style,)),
										array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'closeOnly' => true, 'style' => 'position: relative;')),
								),
						));
					}
				}
			}
		}
				
				
				
		if($this->_setgroup_but['has_external_obj'] && $this->_external_obj != "")
		{
			$subtable->addElement('note', 'label_ext_form'.$kd, array(
					'value' => $this->_external_obj,
					'decorators' => array(
							'ViewHelper',
							array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'',)),
					array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'closeOnly' => true)),
					),
			));
		}
			
				
		
	    return $subtable;
	}
	
}

