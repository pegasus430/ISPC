<?php

class Application_Form_PatientCrisisHistory extends Pms_Form
{
    protected $_model = 'PatientCrisisHistory';

    public function getVersorgerExtract()
    {
        return array(
            array(
                "label" => $this->translate('Status'),
                "cols" => array(
                    "crisis_status"
                ),
                "vsprintf_named" => '<span class="crisis_status st_{crisis_status} "></span>'
            ),
            array(
                "label" => $this->translate('Datum'),
                "cols" => array(
                    "status_date"
                )
            ),
            array(
                "label" => $this->translate('Benutzer'),
                "cols" => array(
                    "status_create_user",
                    "status_create_user_nice_name"
                ),
                "vsprintf_named" => '<span class="user_{status_create_user} ">{status_create_user_nice_name}</span>'
            )
        );
    }

    
    public function getVersorgerAddress()
    {
        return null;
    }
        
   
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientCrisisHistory';
    

	public function create_form_PatientCrisisHistory($values =  array() , $elementsBelongTo = null)
	{  
	    	    
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	    
	    //$this->mapSaveFunction(__FUNCTION__ , "save_crisishistory");
	    
	   
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('crisishistory'));
	    $subform->setAttrib("class", "label_same_size");
	    
	    
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    	    
   
	    return $this->filter_by_block_name($subform,  __FUNCTION__);
	      
	}
		
	public function InsertData($post)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $userid = $logininfo->userid;
	
	    $admis_date = explode(".", $post['admission_date']);
	    if(empty($post['admission_date'])){	      
	        $admission_date = date("Y-m-d H:i:s", time());
	    }else {
	        $admission_date = $admis_date[2] . "-" . $admis_date[1] . "-" . $admis_date[0] . " " . $post['adm_timeh'] . ":" . $post['adm_timem'];
	    }

	    
	    
	    $frm = new PatientCrisisHistory();
	    $frm->ipid = $post['ipid'];
	    $frm->status_date = $admission_date;
	    $frm->crisis_status = '1';
	    $frm->status_create_user = $userid;
	    $frm->save();    	    
	 
	}
	
}




?>