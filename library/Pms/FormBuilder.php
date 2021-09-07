<?
class Pms_FormBuilder
{
		/*
		Build is used to output the forms data as a html form
		$data is an array generated on post from the builder.
		
		*/
		protected $_hepler;
		
		protected $textboxFields;
		
		public function __construct()
		{
			$this->_helper = new Pms_FormBuilder_Helper();
			
			$this->_pdfhelper = new Pms_FormBuilder_PdfHelper();
			
			
			$this->texttables = array(
			''=>'Select',
			'patient_health_insurance'=>'Patient Health Insurance',
			'patient_master'=>'Patient Master'
			);
			
			/*$this->optionstables = array(
			''=>'Select',
			'contactperson_master'=>'Patient Contacts',
			'patient_drugplan'=>'Patient Drugplan',
			'patient_diagnosis'=>'Patient Diagnosis',
			'patient_qpa_maping'=>'Patient QPA',
			'patient_location'=>'Patient Location'
			
			); 	*/
			
			$this->standalonetables = array(
			''=>'Select',
			'symptomatology_master'=>'Symptomatology Master',
			'course_shortcuts'=>'Course Shortcuts',
			'diagnosis'=>'diagnosis',
			'diagnosis_freetext'=>'diagnosis_freetext',
			'diagnosis_type'=>'diagnosis_type',
			'discharge_method'=>'discharge_method',
			'family_doctor'=>'family_doctor',
			'health_insurance'=>'health_insurance',
			'kbv_keytabs'=>'kbv_keytabs',
			'medication_master'=>'medication_master'
			);
			
			$this->tablefields = array(
			'' =>array(
										''=>'Select'
										
			),
			
			'patient_location'=>array(
										''=>'Select'
										
			),
			'patient_qpa_maping'=>array(
										''=>'Select',
										'first_name'=>'QPA First Name',
										'last_name'=>'QPA Last Name'
			),
			'patient_diagnosis'=>array(
										''=>'Select',
										'description' => 'Description'
			
			),	
			'patient_drugplan'=>array(
										''=>'Select',
										'description' => 'Description'
			
			),
			'contactperson_master'=>array(
										''=>'Select',
										'cnt_first_name' => 'Contact First Name',
										'cnt_last_name' => 'Contact Last Name'
			
			),
			'patient_master'=>array(
										''=>'Select',
										'first_name'=>'first_name',
										'middle_name'=>'middle_name',
										'last_name'=>'last_name',
										'title'=>'title',
										'street1'=>'street1',
										'street2'=>'street2',
										'zip'=>'zip',
										'city'=>'city',
										'phone'=>'phone',
										'mobile'=>'mobile',
										'birthd'=>'birthd'
			
			),
			'patient_health_insurance'=>array(
										''=>'Select',
										'insurance_no'=>'Insurance No'
			),
			'symptomatology_master'=>array(
								''=>'Select',
								'sym_description'=>'sym_description'
			),
			'course_shortcuts'=>array(
								''=>'Select',
								'course_fullname'=>'course_fullname'
			),
			'diagnosis'=>array(
								''=>'Select',
								'detail_code'=>'detail_code'
			),
			'diagnosis_freetext'=>array(
								''=>'Select',
								'free_name'=>'free_name'
			),
			'diagnosis_type'=>array(
								''=>'Select',
								'abbrevation'=>'abbrevation'
			),
			'discharge_method'=>array(
								''=>'Select',
								'abbr'=>'abbr'
			),
			'family_doctor'=>array(
								''=>'Select',
								'first_name'=>'first_name',
								'last_name'=>'last_name'
			),
			'health_insurance'=>array(
								''=>'Select',
								'name'=>'name',
								'name2'=>'name2'
			),
			'kbv_keytabs'=>array(
								''=>'Select',
								'dn'=>'dn'
			),
			'medication_master'=>array(
								''=>'Select',
								'name'=>'name'
			)
			
			);
		}
		
		function build($data)
		{
			//if (!isset($data['properties'])) return false;
			$elements = $data;
			
			foreach ($elements as $k => $val)
			{
				//if (!isset($data[$k])) $data[$k] = NULL;
				//if (!isset($val['values'])) $val['values'] = NULL;
				
				$elements[$k]['content'] = $val['content'];
				
				//$name = $k;
				$name = 'field_'.$val['id'];
				
				switch ($val['type'])
				{
					case 'text': $elements[$k]['html'] = $val['content']; break;
					case 'textarea':
						$elements[$k]['html'] = $this->_helper->form_textarea(array(
							'name' => $name,
							'rows' => 5,
							'cols' => 50,
							'value' => $val['content'],
							'class' => ((isset($val['required']))?'required'.((isset($val['required_vars']))?'{'.$val['required_vars'].'}':null):null)
						)); 
						break;
					case 'textbox': 
						$elements[$k]['html'] = $this->_helper->form_input(array(
							'name'=>$name,
							'value'=>$val['content'],
							'class' => ((isset($val['required']))?'required'.((isset($val['required_vars']))?'{'.$val['required_vars'].'}':null):null)
						));
						break;
					case 'dropdown': 
					
						if (!$val['options']) { unset($elements[$k]); break; }
						
						
						if(is_array($val['options'])){
						$options = $val['options'];
						}else{
						$options = explode(';',$val['options']);
						}
						if (empty($options)) {// unset($elements[$k]); break; 
							$options = array();
						}
						
						$elements[$k]['html'] = $this->_helper->form_dropdown($name,$options,$elements[$k]['content']); 
						break;
					case 'checkbox':
						$input = null;
						
						if (!$val['options']) { unset($elements[$k]); break; }
						$options = explode(';',$val['options']);
						if (empty($options)) { unset($elements[$k]); break; }
						
						$optarr = unserialize($val['content']);
						$input = '<table>';
						foreach ($options as $option) {
						$checked = false;
						if(is_array($optarr) && in_array($option,$optarr)){ $checked = true;}
							$input .= '<tr><td>'.$this->_helper->form_checkbox($name.'[]', $option, $checked).' '.$option.'</td></tr>';
						}
						$input .= '</table>';
						$elements[$k]['html'] = $input; 
						break;
					case 'checkboxmatrix':
						if (!$val['options']) { unset($elements[$k]); break; }
						$options = explode(';',$val['options']);
						if (!$val['columns']) { unset($elements[$k]); break; }
						$columns = explode(';',$val['columns']);
						
						
						
						$optarr = unserialize($val['content']);
						
						
						if (empty($options)) {// unset($elements[$k]); break; 
							$options = array();
						}
						
						if (empty($columns)) {// unset($elements[$k]); break; 
							$columns = array();
						}
						
						if (empty($optarr)) {// unset($elements[$k]); break; 
							$optarr = array();
						}
						
						$input = '<table><tr><td>&nbsp;</td>';
						
						foreach($columns as $colno=>$col)
						{
								$input.='<td>'.$col.'</td>';
						}
						
						$input .='</tr>';
						
						foreach ($options as $option) {
							
							
							$input.='<tr>';
							$input .= '<td>'.$option.'</td>';
							
							foreach($columns as $colno=>$col)
							{
								$checked = false;
								if(is_array($optarr[$colno]) && in_array($option,$optarr[$colno])){ $checked = true;}
								$chkname = $name."[".$colno."]";
								$input .= '<td>'.$this->_helper->form_checkbox($chkname.'[]', $option, $checked).'</td>';
							}
							
							$input.='</tr>';
						}
						
						$input.='</table>';
						
						$elements[$k]['html'] = $input; 
						
						break;
					case 'radio':
						$input = null;
						
						if (!$val['options']) { unset($elements[$k]); break; }
						$options = explode(';',$val['options']);
						if (empty($options)) { unset($elements[$k]); break; }
						
						$optarr = unserialize($val['content']);
						
						$input ='<table>';
						foreach ($options as $option) {
						$checked = false;
						if(is_array($optarr) && in_array($option,$optarr)){ $checked = true;}
							$input .= '<tr><td>'.$this->_helper->form_radio($name.'[]', $option,$checked).' '.$option.'</td></tr>';
						}
						$input .='</table>';
						$elements[$k]['html'] = $input; 
						break;
					case 'datetime':
						$elements[$k]['html'] = $this->_helper->form_input(array(
							'name'=>$name,
							'value'=>$val['content'],
							'class' => 'datepicker'.((isset($val['required']))?' required'.((isset($val['required_vars']))?'{'.$val['required_vars'].'}':null):null)
						));
						break;
					case 'fileupload':
						$elements[$k]['html'] = $this->_helper->form_upload(array(
							'name'=>$name,
							'class' => ((isset($val['required']))?'required'.((isset($val['required_vars']))?'{'.$val['required_vars'].'}':null):null)
						));
						break;
					case 'fbbutton':
						$elements[$k]['html'] = $this->_helper->form_input(array(
							'name'=>$name,
							'value'=>((isset($val['value']))?$val['value']:'Button'),
							'type'=>'button'
						));
						break;
				}
			}
			
			return $elements;
		}
		
		
		function buildvalues($data)
		{
			//if (!isset($data['properties'])) return false;
			$elements = $data;
			
			foreach ($elements as $k => $val)
			{
				//if (!isset($data[$k])) $data[$k] = NULL;
				//if (!isset($val['values'])) $val['values'] = NULL;
				
				$elements[$k]['content'] = $val['content'];
				
				//$name = $k;
				$name = 'field_'.$val['id'];
				
				switch ($val['type'])
				{
					case 'text': $elements[$k]['html'] = $val['content']; break;
					case 'textarea':
						$elements[$k]['html'] = $this->_pdfhelper->form_textarea(array(
							'name' => $name,
							'rows' => 5,
							'cols' => 50,
							'value' => $val['content'],
							'class' => ((isset($val['required']))?'required'.((isset($val['required_vars']))?'{'.$val['required_vars'].'}':null):null)
						)); 
						break;
					case 'textbox': 
						$elements[$k]['html'] = $this->_pdfhelper->form_input(array(
							'name'=>$name,
							'value'=>$val['content'],
							'class' => ((isset($val['required']))?'required'.((isset($val['required_vars']))?'{'.$val['required_vars'].'}':null):null)
						));
						break;
					case 'dropdown': 
					
						if (!$val['options']) { unset($elements[$k]); break; }
						
						
						if(is_array($val['options'])){
						$options = $val['options'];
						}else{
						$options = explode(';',$val['options']);
						}
						if (empty($options)) {// unset($elements[$k]); break; 
							$options = array();
						}
						
						$elements[$k]['html'] = $this->_pdfhelper->form_dropdown($name,$options,$elements[$k]['content']); 
						break;
					case 'checkbox':
						$input = null;
						
						if (!$val['options']) { unset($elements[$k]); break; }
						$options = explode(';',$val['options']);
						if (empty($options)) { unset($elements[$k]); break; }
						
						$optarr = unserialize($val['content']);
						
						foreach ($options as $option) {
						$checked = false;
						if(is_array($optarr) && in_array($option,$optarr)){ $checked = true;}
							$input .= $this->_pdfhelper->form_checkbox($name.'[]', $option, $checked).' '.$option.'<br/>';
						}
						$elements[$k]['html'] = $input; 
						break;
					case 'checkboxmatrix':
						if (!$val['options']) { unset($elements[$k]); break; }
						$options = explode(';',$val['options']);
						if (!$val['columns']) { unset($elements[$k]); break; }
						$columns = explode(';',$val['columns']);
						
						
						
						$optarr = unserialize($val['content']);
						
						
						if (empty($options)) {// unset($elements[$k]); break; 
							$options = array();
						}
						
						if (empty($columns)) {// unset($elements[$k]); break; 
							$columns = array();
						}
						
						if (empty($optarr)) {// unset($elements[$k]); break; 
							$optarr = array();
						}
						
						foreach ($options as $option) {
							
							
							$input.='<br/>';
							$input .= $option;
							foreach($columns as $colno=>$col)
							{
								$checked = false;
								if(is_array($optarr[$colno]) && in_array($option,$optarr[$colno])){ $checked = true;}
								$chkname = $name."[".$colno."]";
								$input .= $this->_pdfhelper->form_checkbox($chkname.'[]', $option, $checked);
							}
							
							$input.='<br/>';
						}
						$elements[$k]['html'] = $input; 
						
						break;
					case 'radio':
						$input = null;
						
						if (!$val['options']) { unset($elements[$k]); break; }
						$options = explode(';',$val['options']);
						if (empty($options)) { unset($elements[$k]); break; }
						
						$optarr = unserialize($val['content']);
						
						
						foreach ($options as $option) {
						$checked = false;
						if(is_array($optarr) && in_array($option,$optarr)){ $checked = true;}
							$input .= $this->_pdfhelper->form_radio($name.'[]', $option,$checked).' '.$option.'<br/>';
						}
						$elements[$k]['html'] = $input; 
						break;
					case 'datetime':
						$elements[$k]['html'] = $this->_pdfhelper->form_input(array(
							'name'=>$name,
							'value'=>$val['content'],
							'class' => 'datepicker'.((isset($val['required']))?' required'.((isset($val['required_vars']))?'{'.$val['required_vars'].'}':null):null)
						));
						break;
					case 'fileupload':
						break;
					case 'fbbutton':
						$elements[$k]['html'] = $this->_pdfhelper->form_input(array(
							'name'=>$name,
							'value'=>((isset($val['value']))?$val['value']:'Button'),
							'type'=>'button'
						));
						break;
				}
			}
			
			return $elements;
		}
		
		/*
		Element is generated and spat onscreen
		*/
		function element($attr)
		{
			$id = 'element_'.uniqid();
			switch($attr['type'])
			{
				case 'text': 
					$element = $this->_helper->form_textarea(array(
						'class' => 'wysiwyg',
						'id' => $id,
						'name' => $id,
						'rows' => 5,
						'cols' => 50
					)); 
					break;
				case 'textarea':
					$element = $this->_helper->form_textarea(array(
						'name' => $id,
						'rows' => 5,
						'cols' => 50
					)); 
					break;
				case 'textbox': $element = $this->_helper->form_input($id); break;
				case 'dropdown': $element = $this->_helper->form_dropdown($id,array(''=>'No Content')); break;
				case 'checkbox': $element = '<span class="values '.$id.'"><input type="checkbox"></span>'; break;
				case 'checkboxmatrix': $element = '<span class="values '.$id.'"><input type="checkbox"></span>'; break;
				case 'radio': $element = '<span class="values '.$id.'"><input type="radio"></span>'; break;
				case 'datetime': $element = $this->_helper->form_input(array('name'=>$id,'class'=>'datepicker')); break;
				case 'fileupload': $element = $this->_helper->form_upload($id); break;
				case 'fbbutton': $element = $this->_helper->form_input(array('name'=>$id,'value'=>'No Content','type'=>'button')); break;
				default: $element = null; break;
			}
			
			//give the text box a differnt label
			$label = ($attr['type'] == 'text') ? 'Static Text' : 'No Label';
			
			//basic output list element.
			$output = "
				<li>
				<label for='".$id."'><a href='#' rel='".$attr['type']."' class='properties tooltip' title='Edit'>".$label."</a></label>
					<div class='block'>
						<div class='handle'><span class='icon move'>Move</span></div>
						".$element."
						<span class='note ".$id."'></span>
					</div>
					<div class='clear'></div>
					<div class='attrs clear ".$id."'>
						<input type='hidden' name='properties[".$id."][type]' value='".$attr['type']."'/>
					</div>
				</li>
			";
			
			if ($element) {
				//set output to AJAX
				echo $output;
				exit;
			}
		}
		
		/*
		Builds a list of properties for the builder to display.
		*/
		function properties($attr)
		{
			if($attr['isedit']=='edit')
			{
				$this->editproperties($attr);
			}
			
			$output = null;
			
			$type = $attr['type'];
			$id = $attr['id'];
			
			//basic options
			
			$options = array(
				'Label' => $this->_helper->form_input(array('rel'=>'label[for='.$id.'] a','name'=>'label')),
				'Required' => array(
					'Yes' => $this->_helper->form_checkbox('required','1'),
					'Type' => $this->_helper->form_dropdown('required_vars',array(''=>'Text','email'=>'Email','number'=>'Number'))
				),
				'Description' => $this->_helper->form_input(array('name'=>'description','rel'=>'.note[class~='.$id.']')),
				
			);
			
			$seperate_help = '<span class="icon tooltip" title="Seperate multiple values with a semicolon;<br/>Eg: test;something;here">Help</span>';
			
			//specific options
		
			switch($type)
			{
				case 'textbox':
					$options['Linked Tables'] = $this->_helper->form_dropdown('linkedTables',$this->texttables);
					$options['Linked Fields'] = $this->_helper->form_dropdown('linkedFields',array(''=>'Select'),NULL,'id="linkedFields"');
					break;
				case 'dropdown':
					$options['Linked Tables'] = $this->_helper->form_dropdown('linkedTables',$this->standalonetables);
					$options['Linked Fields'] = $this->_helper->form_dropdown('linkedFields',array(''=>'Select'),NULL,'id="linkedFields"');
					$options['Options'] = $this->_helper->form_input(array('name'=>'values','class'=>'dropdown','rel'=>'select[name='.$id.']')).$seperate_help;
					break;
				case 'radio':
					$options['Options'] = $this->_helper->form_input(array('name'=>'values','class'=>'radio','rel'=>'span.values[class~='.$id.']')).$seperate_help;
					break;
				case 'checkbox':
					$options['Options'] = $this->_helper->form_input(array('name'=>'values','class'=>'checkbox','rel'=>'span.values[class~='.$id.']')).$seperate_help;
					break;
				case 'checkboxmatrix':
					$options['Columns'] = $this->_helper->form_input(array('name'=>'columns','class'=>'checkboxmatrix','rel'=>'span.values[class~='.$id.']'));
					$options['Options'] = $this->_helper->form_input(array('name'=>'values','class'=>'checkboxmatrix','rel'=>'span.values[class~='.$id.']')).$seperate_help;
					break;	
				case 'fbbutton':
					$options['Value'] = $this->_helper->form_input(array('name'=>'value','class'=>'button','rel'=>'input[name='.$id.']'));
					unset($options['Required']); //useless
					break;
				case 'text':
					//unset($options['Label']); //useless
					unset($options['Description']); //useless
					break;
				default: break;
			}
			
			//throw a delete on the bottom for good measure!
			$options['Delete'] = $this->_helper->form_input(array('rel'=>$id,'name'=>'remove','value'=>'Delete Element','type'=>'button','onclick'=>'Admin.formbuilder.remove(this);'));
			
			//spit out the options for ajax
			
			foreach ($options as $k => $option) {
				$output .= '<li class="'.$id.'">';
				$output .= '<b>'.$k.'</b>: ';
				$output .= '<ul>';
						if (is_array($option)) {
							foreach ($option as $sk => $sub) {
								$output .= '<li class="sub"><b>'.$sk.'</b>: '.$sub.'</li>';
							}
						} else {
							$output .= '<li class="sub">'.$option.'</li>';
						}
				$output .= '</ul>';
				$output .= '</li>';
			}
			
			echo $output;
			exit;
		}
		
		function editproperties($attr)
		{
			$output = null;
			
			$type = $attr['type'];
			$id = $attr['id'];
			
			//basic options
			
			$fld = Doctrine_Core::getTable('FbFormFields')->findBy('fieldid',$id);
			$fldarr = $fld->toArray();
			$fldarr = $fldarr[0];
			
			$isrequired = $fldarr['isrequired']==1 ? true : false;
			
				
			
			$options = array(
				'Label' => $this->_helper->form_input(array('rel'=>'label[for='.$id.'] a','name'=>'label','value'=>$fldarr['label'])),
				'Required' => array(
					'Yes' => $this->_helper->form_checkbox('required','1',$isrequired),
					'Type' => $this->_helper->form_dropdown('required_vars',array(''=>'Text','email'=>'Email','number'=>'Number'),$fldarr['validator'])
				),
				'Description' => $this->_helper->form_input(array('name'=>'description','rel'=>'.note[class~='.$id.']','value'=>$fldarr['description'])),
				
			);
			
			$seperate_help = '<span class="icon tooltip" title="Seperate multiple values with a semicolon;<br/>Eg: test;something;here">Help</span>';
			
			//specific options
		
			switch($type)
			{
				case 'textbox':
				
					
					$options['Linked Tables'] = $this->_helper->form_dropdown('linkedTables',$this->texttables,$fldarr['linkedtable']);
					$options['Linked Fields'] = $this->_helper->form_dropdown('linkedFields',$this->tablefields[$fldarr['linkedtable']],$fldarr['linkedfield'],'id="linkedFields"');
					break;
				case 'dropdown':
					$options['Linked Tables'] = $this->_helper->form_dropdown('linkedTables',$this->standalonetables,$fldarr['linkedtable']);
					$options['Linked Fields'] = $this->_helper->form_dropdown('linkedFields',$this->tablefields[$fldarr['linkedtable']],$fldarr['linkedfield'],'id="linkedFields"');
					$options['Options'] = $this->_helper->form_input(array('name'=>'values','class'=>'dropdown','rel'=>'select[name='.$id.']','value'=>$fldarr['options'])).$seperate_help;
					break;
				case 'radio':
					$options['Options'] = $this->_helper->form_input(array('name'=>'values','class'=>'radio','rel'=>'span.values[class~='.$id.']','value'=>$fldarr['options'])).$seperate_help;
					break;
				case 'checkbox':
					$options['Options'] = $this->_helper->form_input(array('name'=>'values','class'=>'checkbox','rel'=>'span.values[class~='.$id.']','value'=>$fldarr['options'])).$seperate_help;
					break;
				case 'checkboxmatrix':
					$options['Columns'] = $this->_helper->form_input(array('name'=>'columns','class'=>'checkboxmatrix','rel'=>'span.values[class~='.$id.']','value'=>$fldarr['columns']));
					$options['Options'] = $this->_helper->form_input(array('name'=>'values','class'=>'checkboxmatrix','rel'=>'span.values[class~='.$id.']','value'=>$fldarr['options'])).$seperate_help;
					break;	
				case 'fbbutton':
					$options['Value'] = $this->_helper->form_input(array('name'=>'value','class'=>'button','rel'=>'input[name='.$id.']'));
					unset($options['Required']); //useless
					break;
				case 'text':
					//unset($options['Label']); //useless
					unset($options['Description']); //useless
					break;
				default: break;
			}
			
			//throw a delete on the bottom for good measure!
						//spit out the options for ajax
			
			foreach ($options as $k => $option) {
				$output .= '<li class="'.$id.'">';
				$output .= '<b>'.$k.'</b>: ';
				$output .= '<ul>';
						if (is_array($option)) {
							foreach ($option as $sk => $sub) {
								$output .= '<li class="sub"><b>'.$sk.'</b>: '.$sub.'</li>';
							}
						} else {
							$output .= '<li class="sub">'.$option.'</li>';
						}
				$output .= '</ul>';
				$output .= '</li>';
			}
			
			echo $output;
			exit;
		}
		
		public function linkedfields($get)
		{
			//echo $this->_helper->form_dropdown('linkedFields',$this->tablefields[$get['tablename']],NULL,'id="linkedFields"');
			echo json_encode($this->tablefields[$get['tablename']]);
			exit;
		
		}
}
	

?>