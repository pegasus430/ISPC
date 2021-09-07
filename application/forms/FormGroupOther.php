<?php
/**
 * 
 * @author carmen
 * 
 * 06.08.2019
 * ISPC-2370
 * elena, ISPC-2627 ISPC: new form Krankenbeförderung 2020 20.08.2020 , file was missing in my branch
 * Maria:: Migration CISPC to ISPC 02.09.2020
 */
class Application_Form_FormGroupOther extends Pms_Form
{
	protected $_setgroup_oth = null;
	
	protected $_elementsBelongTo = null;
	
	public function __construct($options = null)
	{	
		if (isset($options['_setgroup_oth'])) {
			$this->_setgroup_oth = $options['_setgroup_oth'];
			unset($options['_setgroup_oth']);
		}
		
		if (isset($options['elementsBelongTo'])) {
			$this->_elementsBelongTo = $options['elementsBelongTo'];
			unset($options['elementsBelongTo']);
		}
	
		parent::__construct($options);
		
		//$this->_kvheader_lang = $this->translate ('kvheader_lang');
        //​
	}	
	
	public function isValid($data)
	{
	    
	    return parent::isValid($data);
	    
	}	
	
	public function _create_form_groupother($options = array(), $elementsBelongTo = null)
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
        //​
		$othnr = count($this->_setgroup_oth['types']);
		
		foreach($this->_setgroup_oth['types'] as $kb => $vb)
		{	
			
		if($this->_setgroup_oth[$kb]['y_offset'] > 0)
			{
				$posy = $this->_setgroup_oth['first_but_pos_top']+(($kb)*$this->_setgroup_oth['height_dummy'])+(($kb)*$this->_setgroup_oth[$kb]['y_offset']);
			}
			else
			{
				$posy = $this->_setgroup_oth['first_but_pos_top'];
			}
			if($this->_setgroup_oth[$kb]['x_offset'] > 0)
			{
				$posx = $this->_setgroup_oth['first_but_pos_left']+(($kb)*$this->_setgroup_oth['width_dummy'])+(($kb)*$this->_setgroup_oth[$kb]['x_offset']);
				$posx_label = $posx +(($kb-1)*$this->_setgroup_oth['width_dummy'])+($kb-1)*$this->_setgroup_oth['x_offset_label'];
			}
			else
			{
				$posx = $this->_setgroup_oth['first_but_pos_left'];
			}
			
			if($kb == 0 && $othnr == 1)
			{
				//$style = 'left: '.$this->_setgroup_oth['first_but_pos_left'];
				//$style .= 'px; top: ' . $this->_setgroup_oth['first_but_pos_top']. 'px;';
				//$style .= 'px; font-size: ' . $this->_setgroup_but1['font_dummy_text'] .'px;';
				/* $style_label = 'left: '. ($this->_setgroup_but1['first_but_pos_left'] + $this->_setgroup_but1['width_dummy'] + 10);
				$style_label .= 'px; top: ' . $this->_setgroup_but1['first_but_pos_top']. 'px;'; */
				$class = $this->_setgroup_oth['name'][$kb].'_'.$vb;
				$class_label = $this->_setgroup_oth['name'][$kb].'_'.$vb.'_label';
				
				switch($vb)
				{
					case 'text':
						if($this->_setgroup_oth['maxlength'][$kb] != "")
						{
							$maxlength = $this->_setgroup_oth['maxlength'][$kb];
						}
						else
						{
							$maxlength = "";
						}
						$empty = $subtable->createElement('text', $this->_setgroup_oth['name'][$kb].'_'.$vb, array(
								'label'        => $this->_setgroup_oth['label'][$kb],
								'value'        => $options[$this->_setgroup_oth['name'][$kb].'_'.$vb],
								'required'     => false,
								'decorators' => array(
										'ViewHelper',
										array('Errors'),
										array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'class' => $class, 'style' => $style,)),
										array('Label', array('class' => $class_label, 'placement' => $this->_setgroup_oth['label_placement'][$kb])),
										array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'position: relative;')),
										array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'')),
										array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'closeOnly' => true)),
								),
								'id' => $this->_setgroup_oth['name'][$kb].'_'.$vb,
								'maxlength' => $maxlength,
						));
						break;
					case 'textarea':
						break;
					case 'hidden':
						break;
					default:
						break;
				}
				$subtable->addElement($empty, '_arr_'.$kb);
			}
			else if($kb == 0 && $othnr > 1)
			{
				//$style = 'left: '.$this->_setgroup_oth['first_but_pos_left'];
				//$style .= 'px; top: ' . $this->_setgroup_oth['first_but_pos_top']. 'px;';
				//$style .= 'px; font-size: ' . $this->_setgroup_but1['font_dummy_text'] .'px;';
				/* $style_label = 'left: '. ($this->_setgroup_but1['first_but_pos_left'] + $this->_setgroup_but1['width_dummy'] + 10);
				$style_label .= 'px; top: ' . $this->_setgroup_but1['first_but_pos_top']. 'px;'; */
				$class = $this->_setgroup_oth['name'][$kb].'_'.$vb;
				$class_label = $this->_setgroup_oth['name'][$kb].'_'.$vb.'_label';
				
				switch($vb)
				{
					case 'text':
						if($this->_setgroup_oth['maxlength'][$kb] != "")
						{
							$maxlength = $this->_setgroup_oth['maxlength'][$kb];
						}
						else
						{
							$maxlength = "";
						}
						$empty = $subtable->createElement('text', $this->_setgroup_oth['name'][$kb].'_'.$vb, array(
								'label'        => $this->_setgroup_oth['label'][$kb],
								'value'        => $options[$this->_setgroup_oth['name'][$kb].'_'.$vb],
								'required'     => false,
								'decorators' => array(
										'ViewHelper',
										array('Errors'),
										array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'class' => $class, 'style' => $style,)),
										array('Label', array('class' => $class_label, 'placement' => $this->_setgroup_oth['label_placement'][$kb])),
										array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'position: relative;')),
										array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'', 'openOnly' => true)),
										array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'openOnly' => true)),
								),
								'id' => $this->_setgroup_oth['name'][$kb].'_'.$vb,
								'maxlength' => $maxlength,
						));
						break;
					case 'textarea':
						break;
					case 'hidden':
						break;
					default:
						break;
				}
				$subtable->addElement($empty, '_arr_'.$kb);
			}
			else if($kb == $othnr)
			{
				//$style = 'left: '.$this->_setgroup_oth['first_but_pos_left'];
				//$style .= 'px; top: ' . $this->_setgroup_oth['first_but_pos_top']. 'px;';
				//$style .= 'px; font-size: ' . $this->_setgroup_but1['font_dummy_text'] .'px;';
				/* $style_label = 'left: '. ($this->_setgroup_but1['first_but_pos_left'] + $this->_setgroup_but1['width_dummy'] + 10);
				$style_label .= 'px; top: ' . $this->_setgroup_but1['first_but_pos_top']. 'px;'; */
				$class = $this->_setgroup_oth['name'][$kb].'_'.$vb;
				$class_label = $this->_setgroup_oth['name'][$kb].'_'.$vb.'_label';
				
				switch($vb)
				{
					case 'text':
						if($this->_setgroup_oth['maxlength'][$kb] != "")
						{
							$maxlength = $this->_setgroup_oth['maxlength'][$kb];
						}
						else
						{
							$maxlength = "";
						}
						$empty = $subtable->createElement('text', $this->_setgroup_oth['name'][$kb].'_'.$vb, array(
								'label'        => $this->_setgroup_oth['label'][$kb],
								'value'        => $options[$this->_setgroup_oth['name'][$kb].'_'.$vb],
								'required'     => false,
								'decorators' => array(
										'ViewHelper',
										array('Errors'),
										array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'class' => $class, 'style' => $style,)),
										array('Label', array('class' => $class_label, 'placement' => $this->_setgroup_oth['label_placement'][$kb])),
										array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'position: relative;')),
										array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'', 'closeOnly' => true)),
										array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'closeOnly' => true)),
								),
								'id' => $this->_setgroup_oth['name'][$kb].'_'.$vb,
								'maxlength' => $maxlength,
						));
						break;
					case 'textarea':
						break;
					case 'hidden':
						break;
					default:
						break;
				}
				$subtable->addElement($empty, '_arr_'.$kb);
			}
			else
			{
				//$style = 'left: '.$this->_setgroup_oth['first_but_pos_left'];
				//$style .= 'px; top: ' . $this->_setgroup_oth['first_but_pos_top']. 'px;';
				//$style .= 'px; font-size: ' . $this->_setgroup_but1['font_dummy_text'] .'px;';
				/* $style_label = 'left: '. ($this->_setgroup_but1['first_but_pos_left'] + $this->_setgroup_but1['width_dummy'] + 10);
				 $style_label .= 'px; top: ' . $this->_setgroup_but1['first_but_pos_top']. 'px;'; */
				$class = $this->_setgroup_oth['name'][$kb].'_'.$vb;
				$class_label = $this->_setgroup_oth['name'][$kb].'_'.$vb.'_label';
				
				switch($vb)
				{
					case 'text':
						if($this->_setgroup_oth['maxlength'][$kb] != "")
						{
							$maxlength = $this->_setgroup_oth['maxlength'][$kb];
						}
						else
						{
							$maxlength = "";
						}
						$empty = $subtable->createElement('text', $this->_setgroup_oth['name'][$kb].'_'.$vb, array(
								'label'        => $this->_setgroup_oth['label'][$kb],
								'value'        => $options[$this->_setgroup_oth['name'][$kb].'_'.$vb],
								'required'     => false,
								'decorators' => array(
										'ViewHelper',
										array('Errors'),
										array(array('stag' => 'HtmlTag'), array('tag' => 'span', 'class' => $class, 'style' => $style,)),
										array('Label', array('class' => $class_label, 'placement' => $this->_setgroup_oth['label_placement'][$kb])),
										array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'style' => 'position: relative;')),
										//array(array('tdtag' => 'HtmlTag'), array('tag'=>'td', 'class'=>'')),
										//array(array('trtag' => 'HtmlTag'), array('tag'=>'tr', 'class'=>'', 'closeOnly' => true)),
								),
								'id' => $this->_setgroup_oth['name'][$kb].'_'.$vb,
								'maxlength' => $maxlength,
						));
						break;
					case 'textarea':
						break;
					case 'hidden':
						break;
					default:
						break;
				}
				$subtable->addElement($empty, '_arr_'.$kb);
			}
		}
		
	    return $subtable;
	}
	
}

