<?php
//ISPC-2669 Lore 23.09.2020

require_once("Pms/Form.php");

class Application_Form_PatientHandicappedCard extends Pms_Form
{
    protected $_model = 'PatientHandicappedCard';

    public function getVersorgerExtract($param = null)
    {
        return array(
            array( "label" => $this->translate('since'), "cols" => array("since_date_show")),
            array( "label" => $this->translate('hc_approved_by'), "cols" => array("approved_option_x")),
            array( "label" => $this->translate('marks'), "cols" => array("marks_option_x")),
        );
    }

    
    public function getVersorgerAddress()
    {

    }
     
   
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientHandicappedCard';
    
	
    public function create_form_block_patient_hc($options =  array() , $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName, "create_form_isValid");
        
        $this->mapSaveFunction($__fnName, "save_form_handicapped_card");
        
        $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
        $subform->setLegend('block_patient_hc');
        $subform->setAttrib("class", "label_same_size {$__fnName}");
        
        
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        
        $subform->addElement('hidden', 'id', array(
            'value'        => $options['id'] ? $options['id'] : 0 ,
            'required'     => false,
            'label'        => null,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 3)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => array('display:none') )),
                
            ),
        ));
        
        
        //FIRST
        $subform->addElement('text',  'since_date', array(
            'value'        => empty($options['since_date']) || $options['since_date'] == "0000-00-00 00:00:00" || $options['since_date'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['since_date'])),
            'label'        => $this->translate('since'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','colspan'=>2, 'class' => 'marks_options')),
                array('Label', array('tag' => 'td', 'tagClass'=>'marks_label')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'class' => 'date allow_future',
            'data-mask' => "99.99.9999",
            'data-altformat' => 'yy-mm-dd',
        ));
        
        
        
        //SECOND
        
        $approved_handicapped_arr = PatientHandicappedCard::getApprovedHandicapped();
        $subform->addElement('radio',  'approved_option', array(
            'value'        => $options['approved_option'],
            'label'        => $this->translate('hc_approved_by'),
            'required'     => false,
            'multiOptions' => $approved_handicapped_arr,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2, 'style'=>array())),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr'  )),
            ),
            'onChange' => "if(this.value == '2') { $('.approved_date_valid', $(this).parents('table')).show();} else { $('.approved_date_valid', $(this).parents('table')).hide();} ",
        ));
        
        $display_approved_date = $options['approved_option'] == 2 ? '' : array('display:none');
        $subform->addElement('text',  'approved_date', array(
            'value'        => empty($options['approved_date']) || $options['approved_date'] == "0000-00-00 00:00:00" || $options['approved_date'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['approved_date'])),
            'label'        => '',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','colspan'=>2, 'class'=>'approved_date_valid vabase', 'style' => $display_approved_date)),
                array('Label', array('tag' => 'td', 'class'=>'approved_date_valid', 'style' => $display_approved_date)),
                array(array('row' => 'HtmlTag'), array( 'tag' => 'tr' )),
            ),
            'class' => 'date allow_future',
            'data-mask' => "99.99.9999",
            'data-altformat' => 'yy-mm-dd',
        ));
        
        //THIRD
        $mh_arr = PatientHandicappedCard::getMarksHandicapped();
        $subform->addElement('multiCheckbox', 'marks_option', array(
            'label'      => "marks",
            'multiOptions' => $mh_arr,
            'required'   => false,
            'value'    => isset($options['marks_option']) && ! is_array($options['marks_option']) ? array_map('trim', explode(",", $options['marks_option'])) : $options['marks_option'],
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td','colspan'=>2, 'class' => 'marks_options')),
                array('Label', array('tag' => 'td', 'tagClass'=>'marks_label ')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        return $this->filter_by_block_name($subform, $__fnName);
        
    }
    
	
    public function create_form_patient_handicapped_card($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_handicapped_card");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table','style'=>array('width:100%')));
	    $subform->setLegend($this->translate('PatientHandicappedCard_legend'));
	    $subform->setAttrib("class", "label_same_size {$__fnName}");
	    
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options['id'] ? $options['id'] : 0 ,
	        'required'     => false,
	        'label'        => null,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 3)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	            
	        ),
	    ));
	    
	    //FIRST
	    $subform->addElement('text',  'since_date', array(
	        'value'        => empty($options['since_date']) || $options['since_date'] == "0000-00-00 00:00:00" || $options['since_date'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['since_date'])),
	        'label'        => $this->translate('since'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td','colspan'=>2, 'class' => 'marks_options')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'marks_label ')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altformat' => 'yy-mm-dd',
	    ));
	    
	    
	    
	    
	    //SECOND
	    
	    $subform->addElement('note', 'notes', array(
	        'label'            => "",
	        'value'            => '',
	        'decorators'       => array(
	            'ViewHelper',
	            array('Label'),
	            array('Errors'),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr','OpenOnly'=>true )),
	        ),
	    ));
	    
 	    $approved_handicapped_arr = PatientHandicappedCard::getApprovedHandicapped();	    
	    $subform->addElement('radio',  'approved_option', array(
	        'value'        => $options['approved_option'],
	        'label'        => $this->translate('hc_approved_by'),
	        'required'     => false,
	        'multiOptions' => $approved_handicapped_arr,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	        ),
	        'onChange' => "if(this.value == '2') { $('.approved_date_valid', $(this).parents('table')).show();} else { $('.approved_date_valid', $(this).parents('table')).hide();} ",
	    ));
	    
	    
	    
	    $display_approved_date = $options['approved_option'] == 2 ? '' : array('display:none');
	    $subform->addElement('text',  'approved_date', array(
	        'value'        => empty($options['approved_date']) || $options['approved_date'] == "0000-00-00 00:00:00" || $options['approved_date'] == "1970-01-01 00:00:00" ? "" : date('d.m.Y', strtotime($options['approved_date'])),
	        'label'        => '',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'approved_date_valid vabase', 'style' => $display_approved_date)),
	        ),
	        'class' => 'date allow_future',
	        'data-mask' => "99.99.9999",
	        'data-altformat' => 'yy-mm-dd',
	    ));
	    
	    $subform->addElement('note', 'note', array(
	        'label'            => "",
	        'value'            => '',
	        'decorators'       => array(
	            'ViewHelper',
	            array('Label'),
	            array('Errors'),
	            array(array('row' => 'HtmlTag'), array( 'tag' => 'tr','CloseOnly'=>true )),
	        ),
	    ));
	    
	    //THIRD
	    
	    $mh_arr = PatientHandicappedCard::getMarksHandicapped();
	    $subform->addElement('multiCheckbox', 'marks_option', array(
	        'label'      => "marks",
	        'multiOptions' => $mh_arr,
	        'required'   => false,
	        'value'    => isset($options['marks_option']) && ! is_array($options['marks_option']) ? array_map('trim', explode(",", $options['marks_option'])) : $options['marks_option'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td','colspan'=>2, 'class' => 'marks_options')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'marks_label ')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    	    
	    return $this->filter_by_block_name($subform, $__fnName);
	                    
	}
	
	
	public function save_form_handicapped_card($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    //dd($data);
	    
	    $decid = Pms_Uuid::decrypt($_GET['id']);
	    $ipid = Pms_commonData::getIpid($decid);
	    
	    $data['since_date'] = !empty($data['since_date']) ? date("Y-m-d", strtotime($data['since_date'])) : null;
	    $data['approved_date'] = $data['approved_option'] == 2 && !empty($data['approved_date']) ? date("Y-m-d", strtotime($data['approved_date'])) : null;
	    $data['marks_option'] = isset($data['marks_option']) ?  implode(",", $data['marks_option']) : null;
	    
	    $r = PatientHandicappedCardTable::getInstance()->findOrCreateOneBy( ['ipid'], [$ipid], $data );
	    	    
	    return $r;
	    
	}
	

	
	
	public function InsertData($post)
	{	

	    //$post['marks_option'] = isset($post['marks_option']) ?  implode(",", $post['marks_option']) : null;
	    
	    $frm = new PatientHandicappedCard();
	    $frm->ipid = $post['ipid'];
	    $frm->since_date = !empty($post['since_date']) ? date("Y-m-d", strtotime($post['since_date'])) : null;
	    $frm->approved_option = $post['approved_option'];
	    $frm->approved_date = $post['approved_option'] == 2 && !empty($post['approved_date']) ? date("Y-m-d", strtotime($post['approved_date'])) : null;
	    $frm->marks_option = isset($post['marks_option']) ?  implode(",", $post['marks_option']) : null;
	    $frm->save();    	    
	 
	}
	
	public function UpdateData($post)
	{
	    
	    if ($fdoc = Doctrine::getTable('PatientHandicappedCard')->find($post['ipid']))
	    {

	        $fdoc->since_date = !empty($post['since_date']) ? date("Y-m-d", strtotime($post['since_date'])) : null;
	        $fdoc->approved_option = $post['approved_option'];
	        $fdoc->approved_date = $post['approved_option'] == 2 && !empty($post['approved_date']) ? date("Y-m-d", strtotime($post['approved_date'])) : null;
	        $fdoc->marks_option = isset($post['marks_option']) ?  implode(",", $post['marks_option']) : null;
	        $fdoc->save();
	        
	        
	    }
	}
	

	
	
}




?>