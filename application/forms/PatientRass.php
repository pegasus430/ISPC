<?php 
// ISPC-2564 Andrei 20.05.2020

class Application_Form_PatientRass extends Pms_Form 
{
    protected $title_center = null;
    
    protected $title_label_1 = null;
    
    protected $title_label_2 = null;
    
    protected $content_label = null;
    
    protected $_client_data = null;
    
    public function __construct($options = null)
    {
        if($options['_client_data'])
        {
            //$this->_client_data = $options['_client_data'];
            unset($options['_client_data']);
        }
        
        parent::__construct($options);
        //print_r($this->_client_data); exit;
        $this->title_center = '<h2 style="font-size: 18px; color: #555;">RASS</h2>';
        $this->title_label_1 = '<h1>Abstufung</h1>';
        $this->title_label_2 = '<h1>Durchführung</h1>';
        $this->content_label = '
        	<ol class="orasslist">
				<li>Patienten beobachten. Ist er wach und ruhig (Score 0)? Oder ist der Patient unruhig oder agitiert (Score +1 bis +4 entsprechend der jeweiligen Beschreibung)?</li>
				<li>Wenn der Patient nicht wach ist, mit lauter Stimme beim Namen ansprechen und Patienten auffordern, die Augen zu öffnen und den Sprecher anzusehen. Wie lange kann der Patient den Blickkontakt aufrechterhalten? 
	    			<ul class="urasslist">
	     				<li>Patient erwacht mit anhaltendem Augenöffnen und Augenkontakt. (Score –1)</li>
	       				<li>Patient erwacht mit Augenöffnen und Blickkontakt, aber nicht anhaltend. (Score –2)</li>
	       				<li>Patient zeigt irgendeine Bewegung auf Ansprache, aber keinen Blickkontakt. (Score –3)</li>
	    			</ul>
				</li>
    			<li>Falls der Patient auf Ansprache nicht reagiert, Patient körperlich durch Schütteln an den Schultern oder – wenn dies nicht hilft – durch Reiben des Sternums stimulieren. 
       				<ul class="urasslist">
       					<li>Patient zeigt irgendeine Bewegung auf körperlichen Reiz. (Score –4)</li>
       					<li>Patient zeigt keine Reaktion auf irgendeinen Reiz. (Score –5)</li>
       				</ul>
    			</li>
			</ol>
			<p class="prass">Am Ende erhält man eine Zahl zwischen −5 und +4. Es ist keine Rechnung notwendig.</p>
			<p>Es ist wichtig mindestens alle 6 Stunden und bei Bedarf zu messen, um Änderungen in der Sedierung oder Opioidtherapie zu erfassen.</p>';
 
    }
    
    public function isValid($data)
    {
            
        return parent::isValid($data);
            
    }
    
       
    public function create_form_patient_rass($options = array(), $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName , "create_form_isValid");
        
        $this->mapSaveFunction($__fnName , "save_form_patient_rass");
        
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
								'style' => 'width: 750px; text-align: center; font-weight: bold; padding: 10px;'))
				),
		));
		
		$this->addElement('hidden', 'id', array(
				'label'        => null,
				'value'        => ! empty($options['id']) ? $options['id'] : '',
				'required'     => false,
				'readonly'     => true,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'dontPrint',
								'style' => 'width: 750px; line-height: 10px; font-weight: bold; padding: 10px;'))
				),
		));		

		$this->addElement('text', 'form_date', array(
				'label'      => $this->translate('Form date:'),
				'required'   => true,
				'value'    => ! empty($options['form_date']) ? date('d.m.Y', strtotime($options['form_date'])) : date('d.m.Y'),
				'filters'    => array('StringTrim'),
				'validators' => array('NotEmpty'),
				'class'    => 'date formular_date',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'width: 750px; line-height: 10px; font-weight: bold; padding: 10px;'))
				),
		));
	
		$this->addElement('note', 'label_form_1', array(
				'value' => $this->title_label_1,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
						'style' => 'width: 750px; line-height: 10px; font-weight: bold; padding: 10px;'))
				),
		));

        $this->addElement('radio',  'responsiveness', array(
            'value'        => $options['responsiveness'],
            'separator'    => '<br>',            
            'multiOptions' =>  PatientRass::getPatientRassRadios(),            
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                //'ViewHelper',
                array('Errors'),
            )
            
        ));
       
        $fields = $this->getElements();
        $table = "<table class='SimpleTable' style='width: 750px;'>";
        $table .= '<tr>';
        $table .= '<td style="width: 20px; font-weight: bold;">';
        $table .= '';
        $table .= '</td>';
        $table .= '<td style="width: 30px; font-weight: bold;">';
        $table .= '<label>Wert</label>';
        $table .= '</td>';
        $table .= '<td style="width: 150px; font-weight: bold;">';
        $table .= '<label>Bezeichnung</label>';
        $table .= '</td>';
        $table .= '<td style="width: 550px; font-weight: bold;">';
        $table .= '<label>Erläuterung</label>';
        $table .= '</td>';
        $table .= '</tr>';
        
        foreach($fields['responsiveness']->getMultiOptions() as $value => $label) {
        	$table .= '<tr>';
        	$table .= '<td style="width: 20px;">';
        	 
        	if((int)$value === (int)$options['responsiveness'] && $options['responsiveness'] != "")
        	{
        		$table .= '<input type="radio" value="' . $value . '" name="responsiveness" checked="checked" /> ';
        	}
        	else 
        	{
        		$table .= '<input type="radio" value="' . $value . '" name="responsiveness" /> ';
        	}
        	
        	$table .= '</td>';
        	$table .= '<td style="width: 30px;">';
        	$table .= '<label>' . $value . '</label>';
        	$table .= '</td>';
        	$table .= '<td style="width: 150px;">';
        	$table .= '<label>' . $label . '</label>';
        	$table .= '</td>';
        	$table .= '<td style="width: 550px;">';
        	$table .= '<label>' . PatientRass::getPatientRassRadiosExplanations()[$value] . '</label>';
        	$table .= '</td>';
        	$table .= '</tr>';
        }
        $table .= '</table>'; 
        
        
        $this->addElement('note', 'table_content', array(
        		'value' => $table,
        		'decorators' => array(
        				'ViewHelper',
        				array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
        						'style' => 'width: 750px; line-height: 10px; font-weight: bold; padding: 10px;'))
        		),
        ));
        
        $this->addElement('note', 'label_form_2', array(
        		'value' => $this->title_label_2,
        		'decorators' => array(
        				'ViewHelper',
        				array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
        						'style' => 'width: 750px; line-height: 10px; font-weight: bold; padding: 10px;'))
        		),
        ));
        
        $this->addElement('note', 'label_form_3', array(
        		'value' => $this->content_label,
        		'decorators' => array(
        				'ViewHelper',
        				array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
        						'style' => 'width: 750px; padding: 10px;'))
        		),
        ));
        
       
        //add action buttons
        $actions = $this->_create_form_actions('formular');
        $this->addSubform($actions, 'form_actions');
        
        return $this;
        
        
    }
    
    
    
    private function _create_form_actions($elementsBelongTo = null)
    {
        $subform = new Zend_Form_SubForm();
        $subform->clearDecorators()
        ->setDecorators( array(
            'FormElements',
            array('HtmlTag',array('tag'=>'div', 'class' => 'form_actions')),
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
            'label'        => $this->translate('submit'),
            // 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'patientrass_form\');',
            'onclick'      => '$(this).parents("form").attr("target", "_self"); window.form_button_action = this.value;',
            'decorators'   => array('ViewHelper'),
            
        ));
        $this->addElement($el, 'save');
        
        /* $el = $this->createElement('button', 'button_action', array(
            'type'         => 'submit',
            'value'        => 'print_pdf',
            // 	        'content'      => $this->translate('generatepdf'),
            'label'        => $this->translate('save AND Print'),
            // 	        'onclick'      => '$(this).parents("form").attr("target", "_blank"); if(checkclientchanged(\'patientrass_form\')){ setTimeout("window.location.reload()", 1000); return true;} else {return false;}',
            'onclick'      => '$(this).parents("form").attr("target", "_blank"); window.form_button_action = this.value;',
            'decorators'   => array('ViewHelper'),
            
        ));
        $subform->addElement($el, 'print_pdf'); */
        
        return $subform;
    }
    
    
    public function save_form_patient_rass($ipid, $data = array())
    {
        if (empty($ipid)) {
            throw new Exception('Contact Admin, form cannot be saved.', 0);
        }
        //print_r($data); exit;
        if($data['id'] == '')
        {
        	$data['form_id'] = null;
        }
        else 
        {
        	$data['form_id'] = $data['id'];
        }
       
        if($data['form_date'] != "")
        {
            $data['form_date'] = date('Y-m-d', strtotime($data['form_date']));
        }
        
        
        $entity = PatientRassTable::getInstance()->findOrCreateOneBy(['ipid', 'id'], [$ipid, $data['form_id']], $data);
        //print_r($data); exit;
        
        return $entity;
    }
    
}
