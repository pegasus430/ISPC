<?php

require_once("Pms/Form.php");

class Application_Form_PatientDrugPlanAllergies extends Pms_Form{

	public function validate($post)
	{

	}

	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$cust = new PatientDrugPlanAllergies();
		$cust->ipid = $ipid;
		$cust->clientid = $clientid;
		$cust->allergies_comment = $post['allergies_comment'];
		$cust->save();

	}

	public function UpdateData($post)
	{
		$q = Doctrine_Query::create()
		->update('PatientDrugPlanAllergies')
		->set('allergies_comment', '?', $post['allergies_comment'])
		->where("ipid = ?", $post['ipid'])
		->execute();

	}



	public function create_form_alergies ($options =  array() , $elementsBelongTo = null)
	{
	     
	    $subform = $this->subFormTable();
	    $subform->setLegend($this->translate('allergies_comment'));
	    $subform->setAttrib("class", "label_same_size_auto");
	     
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
	    $subform->addElement('textarea', 'allergies_comment', array(
	        'value'        => $options['allergies_comment'],
	        'rows'         => 3,
	        'cols'         => 60,
	        //'label'        => 'allergies_comment',
	        'placeholder'  => $this->translate('no alergies'),
	        'required'     => false,
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
// 	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    
	     
	     
	    return $subform;
	}

	
	
	
	public function save_form_alergies($ipid='',$data = array())
	{

	    $logininfo = $this->logininfo;
    	
    	if(empty($ipid) || empty($data)) {
    		return;
    	}

    	if( is_null($logininfo) || ! isset($data['clientid']) ||  ! isset($data['userid']) ){
    		$logininfo = new Zend_Session_Namespace('Login_Info');
    		$data['clientid'] = $logininfo->clientid;
    		$data['userid'] = $logininfo->userid;
    	}

    	$entity = new PatientDrugPlanAllergies();
    	return $entity->findOrCreateOneBy('ipid', $ipid, $data);
	
	}
}
?>