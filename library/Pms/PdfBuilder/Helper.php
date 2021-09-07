<?php  

class Pms_PdfBuilder_Helper{

		function form_open($action = '', $attributes = '', $hidden = array())
		{
				$CI =& get_instance();
		
				if ($attributes == '')
				{
					$attributes = 'method="post"';
				}
		
				$action = ( strpos($action, '://') === FALSE) ? $CI->config->site_url($action) : $action;
		
				$form = '<form action="'.$action.'"';
			
				$form .= _attributes_to_string($attributes, TRUE);
			
				$form .= '>';
		
				if (is_array($hidden) AND count($hidden) > 0)
				{
					$form .= form_hidden($hidden);
				}
		
				return $form;
		}

		function form_open_multipart($action, $attributes = array(), $hidden = array())
		{
				$attributes['enctype'] = 'multipart/form-data';
				return form_open($action, $attributes, $hidden);
		}

		function form_hidden($name, $value = '')
		{
				if ( ! is_array($name))
				{
					return '<input type="hidden" name="'.$name.'" value="'.form_prep($value).'" />';
				}
		
				$form = '';
		
				foreach ($name as $name => $value)
				{
					$form .= "\n";
					$form .= '<input type="hidden" name="'.$name.'" value="'.form_prep($value).'" />';
				}
		
				return $form;
		}
		
		function form_input($data = '', $value = '', $extra = '')
		{
				$defaults = array('type' => 'text', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);
		
				return "<input ".$this->_parse_form_attributes($data, $defaults).$extra." />";
		}

		function form_password($data = '', $value = '', $extra = '')
		{
			if ( ! is_array($data))
			{
				$data = array('name' => $data);
			}
	
			$data['type'] = 'password';
			return form_input($data, $value, $extra);
		}

		function form_upload($data = '', $value = '', $extra = '')
		{
				if ( ! is_array($data))
				{
					$data = array('name' => $data);
				}
		
				$data['type'] = 'file';
				return $this->form_input($data, $value, $extra);
		}


		function form_textarea($data = '', $value = '', $extra = '')
		{
				$defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'cols' => '90', 'rows' => '12');
		
				if ( ! is_array($data) OR ! isset($data['value']))
				{
					$val = $value;
				}
				else
				{
					$val = $data['value']; 
					unset($data['value']); // textareas don't use the value attribute
				}
		
				return "<textarea ".$this->_parse_form_attributes($data, $defaults).$extra.">".$val."</textarea>";
		}


		function form_dropdown($name = '', $options = array(), $selected = array(), $extra = '')
		{
				if ( ! is_array($selected))
				{
					$selected = array($selected);
				}
		
				// If no selected state was submitted we will attempt to set it automatically
				if (count($selected) === 0)
				{
					// If the form name appears in the $_POST array we have a winner!
					if (isset($_POST[$name]))
					{
						$selected = array($_POST[$name]);
					}
				}
		
				if ($extra != '') $extra = ' '.$extra;
		
				$multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';
		
				$form = '<select name="'.$name.'"'.$extra.$multiple.">\n";
			
				foreach ($options as $key => $val)
				{
					$key = (string) $key;
		
					if (is_array($val))
					{
						$form .= '<optgroup label="'.$key.'">'."\n";
		
						foreach ($val as $optgroup_key => $optgroup_val)
						{
							$sel = (in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';
		
							$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string) $optgroup_val."</option>\n";
						}
		
						$form .= '</optgroup>'."\n";
					}
					else
					{
						$sel = (in_array($key, $selected)) ? ' selected="selected"' : '';
		
						$form .= '<option value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
					}
				}
		
				$form .= '</select>';
		
				return $form;
		}


		function form_checkbox($data = '', $value = '', $checked = FALSE, $extra = '')
		{
			$defaults = array('type' => 'checkbox', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);
	
			if (is_array($data) AND array_key_exists('checked', $data))
			{
				$checked = $data['checked'];
	
				if ($checked == FALSE)
				{
					unset($data['checked']);
				}
				else
				{
					$data['checked'] = 'checked';
				}
			}
	
			if ($checked == TRUE)
			{
				$defaults['checked'] = 'checked';
			}
			else
			{
				unset($defaults['checked']);
			}
	
			return "<input ".$this->_parse_form_attributes($data, $defaults).$extra." />";
		}


		function form_radio($data = '', $value = '', $checked = FALSE, $extra = '')
		{
				if ( ! is_array($data))
				{	
					$data = array('name' => $data);
				}
		
				$data['type'] = 'radio';
				return $this->form_checkbox($data, $value, $checked, $extra);
		}

		function form_submit($data = '', $value = '', $extra = '')
		{
				$defaults = array('type' => 'submit', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);
		
				return "<input ".$this->_parse_form_attributes($data, $defaults).$extra." />";
		}


		function form_reset($data = '', $value = '', $extra = '')
		{
				$defaults = array('type' => 'reset', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);
		
				return "<input ".$this->_parse_form_attributes($data, $defaults).$extra." />";
		}


		function form_button($data = '', $content = '', $extra = '')
		{
				$defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'type' => 'button');
		
				if ( is_array($data) AND isset($data['content']))
				{
					$content = $data['content'];
					unset($data['content']); // content is not an attribute
				}
		
				return "<button ".$this->_parse_form_attributes($data, $defaults).$extra.">".$content."</button>";
		}


		function form_label($label_text = '', $id = '', $attributes = array())
		{
	
			$label = '<label';
	
			if ($id != '')
			{
				 $label .= " for=\"$id\"";
			}
	
			if (is_array($attributes) AND count($attributes) > 0)
			{
				foreach ($attributes as $key => $val)
				{
					$label .= ' '.$key.'="'.$val.'"';
				}
			}
	
			$label .= ">$label_text</label>";
	
			return $label;
		}


		function form_fieldset($legend_text = '', $attributes = array())
		{
				$fieldset = "<fieldset";
		
				$fieldset .= _attributes_to_string($attributes, FALSE);
		
				$fieldset .= ">\n";
		
				if ($legend_text != '')
				{
					$fieldset .= "<legend>$legend_text</legend>\n";
				}
		
				return $fieldset;
		}


		function form_fieldset_close($extra = '')
		{
			return "</fieldset>".$extra;
		}


		function form_close($extra = '')
		{
				return "</form>".$extra;
		}


		function form_prep($str = '')
		{
				// if the field name is an array we do this recursively
				if (is_array($str))
				{
					foreach ($str as $key => $val)
					{
						$str[$key] = $this->form_prep($val);
					}
		
					return $str;
				}
		
				if ($str === '')
				{
					return '';
				}
		
				$temp = '__TEMP_AMPERSANDS__';
		
				// Replace entities to temporary markers so that 
				// htmlspecialchars won't mess them up
				$str = preg_replace("/&#(\d+);/", "$temp\\1;", $str);
				$str = preg_replace("/&(\w+);/",  "$temp\\1;", $str);
		
				$str = htmlspecialchars($str);
		
				// In case htmlspecialchars misses these.
				$str = str_replace(array("'", '"'), array("&#39;", "&quot;"), $str);
		
				// Decode the temp markers back to entities
				$str = preg_replace("/$temp(\d+);/","&#\\1;",$str);
				$str = preg_replace("/$temp(\w+);/","&\\1;",$str);
		
				return $str;
		}


		function set_value($field = '', $default = '')
		{
				if (FALSE === ($OBJ =& _get_validation_object()))
				{
					if ( ! isset($_POST[$field]))
					{
						return $default;
					}
		
					return form_prep($_POST[$field]);
				}
		
				return form_prep($OBJ->set_value($field, $default));
		}


		function set_select($field = '', $value = '', $default = FALSE)
		{
				$OBJ =& _get_validation_object();
		
				if ($OBJ === FALSE)
				{
					if ( ! isset($_POST[$field]))
					{
						if (count($_POST) === 0)
						{
							return ' selected="selected"';
						}
						return '';
					}
		
					$field = $_POST[$field];
		
					if (is_array($field))
					{
						if ( ! in_array($value, $field))
						{
							return '';
						}
					}
					else
					{
						if (($field == '' OR $value == '') OR ($field != $value))
						{
							return '';
						}
					}
		
					return ' selected="selected"';
				}
		
				return $OBJ->set_select($field, $value, $default);
		}


		function set_checkbox($field = '', $value = '', $default = FALSE)
		{
				$OBJ =& _get_validation_object();
		
				if ($OBJ === FALSE)
				{ 
					if ( ! isset($_POST[$field]))
					{
						if (count($_POST) === 0)
						{
							return ' checked="checked"';
						}
						return '';
					}
		
					$field = $_POST[$field];
					
					if (is_array($field))
					{
						if ( ! in_array($value, $field))
						{
							return '';
						}
					}
					else
					{
						if (($field == '' OR $value == '') OR ($field != $value))
						{
							return '';
						}
					}
		
					return ' checked="checked"';
				}
		
				return $OBJ->set_checkbox($field, $value, $default);
		}


		function set_radio($field = '', $value = '', $default = FALSE)
		{
				$OBJ =& _get_validation_object();
		
				if ($OBJ === FALSE)
				{
					if ( ! isset($_POST[$field]))
					{
						if (count($_POST) === 0)
						{
							return ' checked="checked"';
						}
						return '';
					}
		
					$field = $_POST[$field];
					
					if (is_array($field))
					{
						if ( ! in_array($value, $field))
						{
							return '';
						}
					}
					else
					{
						if (($field == '' OR $value == '') OR ($field != $value))
						{
							return '';
						}
					}
		
					return ' checked="checked"';
				}
		
				return $OBJ->set_radio($field, $value, $default);
		}


		function form_error($field = '', $prefix = '', $suffix = '')
		{
				if (FALSE === ($OBJ =& _get_validation_object()))
				{
					return '';
				}
		
				return $OBJ->error($field, $prefix, $suffix);
		}


		function validation_errors($prefix = '', $suffix = '')
		{
				if (FALSE === ($OBJ =& _get_validation_object()))
				{
					return '';
				}
		
				return $OBJ->error_string($prefix, $suffix);
		}


		function _parse_form_attributes($attributes, $default)
		{
				if (is_array($attributes))
				{
					foreach ($default as $key => $val)
					{
						if (isset($attributes[$key]))
						{
							$default[$key] = $attributes[$key];
							unset($attributes[$key]);
						}
					}
		
					if (count($attributes) > 0)
					{
						$default = array_merge($default, $attributes);
					}
				}
		
				$att = '';
		
				foreach ($default as $key => $val)
				{
					if ($key == 'value')
					{
						$val = $this->form_prep($val);
					}
		
					$att .= $key . '="' . $val . '" ';
				}
		
				return $att;
		}


		function _attributes_to_string($attributes, $formtag = FALSE)
		{
				if (is_string($attributes) AND strlen($attributes) > 0)
				{
					if ($formtag == TRUE AND strpos($attributes, 'method=') === FALSE)
					{
						$attributes .= ' method="post"';
					}
		
					return ' '.$attributes;
				}
			
				if (is_object($attributes) AND count($attributes) > 0)
				{
					$attributes = (array)$attributes;
				}
		
				if (is_array($attributes) AND count($attributes) > 0)
				{
					$atts = '';
			
					if ( ! isset($attributes['method']) AND $formtag === TRUE)
					{
						$atts .= ' method="post"';
					}
		
					foreach ($attributes as $key => $val)
					{
						$atts .= ' '.$key.'="'.$val.'"';
					}
		
					return $atts;
				}
		}


// ------------------------------------------------------------------------

/**
 * Validation Object
 *
 * Determines what the form validation class was instantiated as, fetches
 * the object and returns it.
 *
 * @access	private
 * @return	mixed
 */
	function &_get_validation_object()
	{
			$CI =& get_instance();
	
			// We set this as a variable since we're returning by reference
			$return = FALSE;
	
			if ( ! isset($CI->load->_ci_classes) OR  ! isset($CI->load->_ci_classes['form_validation']))
			{
				return $return;
			}
	
			$object = $CI->load->_ci_classes['form_validation'];
	
			if ( ! isset($CI->$object) OR ! is_object($CI->$object))
			{
				return $return;
			}
	
			return $CI->$object;
	}
}
?>