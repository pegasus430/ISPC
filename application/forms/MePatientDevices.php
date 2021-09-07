<?php
/**
 * 
 * @author Ancuta
 * ISPC-2432
 * 13.01.2020
 *
 */
class Application_Form_MePatientDevices extends Pms_Form
{
	
    protected $_model = 'MePatientDevices';
    
	private $triggerformid = 0; //use 0 if you want not to trigger
	
	private $triggerformname = "frmMePatientDevices";  //define the name if you want to piggyback some triggers
		
	protected $_translate_lang_array = 'MePatientDevices_box_lang';
	
	public function isValid($data)
	{
	    return parent::isValid($data);   
	}
	
	public function validateDeviceCode($code){
	    /*
	     The activation code is the last 8 chars of a md5 hash created from:
	     QR identifier + device id + program + device password + current date (dd.mm.yyyy
	     format)
	     e.g.  md5(abcedf12ISPC988303ISPC PMS_TESTJ4gtXHC5SLy603.01.2020) =
	     9bf184ca085ba6438094414a8794aa58 => activation code = 8794aa58
	     */
	    
	    
	    
	    
	    return true;
	}
	
	
	public function getVersorgerExtract() {
	    return array(
	        
	        array( "label" => $this->translate('mePatient_device_name'), "cols" => array("device_name")),
	        array( "label" => $this->translate('mePatient_device_type'), "cols" => array("device_type_name")),
	        array( "label" => $this->translate('mePatient_device_active'), "cols" => array("active_name")),
// 	        array( "label" => $this->translate('mePatient_device_surveys'), "cols" => array("device_surveys_name")), // The survey names are ugly, and need to me separated by comma
	    );
	}
	
	
	
	public function create_form_MePatientDevices( $options = array(), $elementsBelongTo = null)
	{
// 	    dd($options);
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_MePatientDevices");
	    
	    //@todo $subform or $this? this is the question
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('mePatient_Devices'));
	    $subform->setAttrib("class", "label_same_size  {$__fnName}"); //has_feedback_options
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options['id'] ? $options['id'] : 0 ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	            
	        ),
	    ));
	    
	    $subform->addElement('text', 'device_name', array(
	        'value'        => $options['device_name'] ,
	        'label'        => $this->translate('mePatient_device_name'),
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            
	        ),
	    ));
	    
	    $subform->addElement('select', 'device_type', array(
	        'value'        => $options['device_type'],
	        'label'        => $this->translate('mePatient_device_type'),
	        'multiOptions' => array( '' => '' , 'android' => 'android_radio', 'ios' => 'ios_radio'),
	        'required'   => true,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('select', 'allow_photo_upload', array(
	        'value'        => $options['allow_photo_upload'],
	        'label'        => $this->translate('mePatient_allow_photo_upload'),
	        'multiOptions' => array( '' => '–' , 'yes' => 'yes_radio', 'no' => 'no_radio'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	   
	    $client_surveys = MePatientSurveysTable::find_survey_ByClientid($this->logininfo->clientid);
	    
	    $surveys = array();
	    if(!empty($client_surveys)){
	        foreach($client_surveys as $survey_id => $survey_details ){
	            $surveys[$survey_details['id']] = $survey_details['survey_name'];
	        }
	    }

	    
	    $subform->addElement('multiCheckbox', 'device_surveys', array(
	        'label'      => $this->translate('mePatient_device_surveys'),
	        'separator'  => '&nbsp;',
	        'required'   => false,
	        'multiOptions'=> $surveys,
	        'value' => $options['device_surveys'],
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>'3','class'=>'devices_survey_list')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    
	    
 
	    return $this->filter_by_block_name($subform, $__fnName);
	    
	    
	    
	}
 
	
	
	
	function device_internal_id_generate()
	{
	    $qcode_nr = rand(100000,999999); //232323
	    $qcode = 'ISPC'.$qcode_nr;
	    
	    $devices_codes = Doctrine_Query::create()
	    ->select('*')
	    ->from('MePatientDevices')
	    ->where("device_internal_id =?", $qcode)
	    ->fetchArray();
	    
	    
	    
	    if(!empty($devices_codes))
	    {
	        return $this->device_internal_id_generate();
	    }
	    else
	    {
	        return $qcode;
	    }
	    
	}
	
	
	public function save_form_MePatientDevices($ipid =  null , $data = array())
	{
	    if (empty($ipid) || ! is_array($data)) {
	        return;
	    }
	    
	    // generate internal id
	    if(empty($data['id'])){
	        // generate new
    	    $data['device_internal_id'] = $this->device_internal_id_generate();
    	    
    	    // ANOTHER CHECK BEFOR INSERTING
    	    $devices_codes = Doctrine_Query::create()
    	    ->select('*')
    	    ->from('MePatientDevices')
    	    ->where("device_internal_id =?", $data['device_internal_id'])
    	    ->fetchArray();
    	    
    	    if(!empty($devices_codes)){
        	    $data['device_internal_id'] = $this->device_internal_id_generate();
    	    }
    	    // 
    	    
	    }
	    if(!empty($data['device_surveys'])){
	        foreach($data['device_surveys'] as $k=>$survey_id){
	            $data['MePatientDevicesSurveys'][$k]['ipid'] = $ipid;
	            $data['MePatientDevicesSurveys'][$k]['survey_id'] = $survey_id;
	            $data['MePatientDevicesSurveys'][$k]['clientid'] = $this->logininfo->clientid;
	       }
	    }
	    
	    //print_r($data); exit;
	    
	    $entity = new MePatientDevices();
	    
	    $resultObj = $entity->findOrCreateOneByIpidAndId( $ipid, $data['id'], $data);
	     
	    
	    return $resultObj;

	}
	
	private function _save_box_History($ipid, $newEntity, $fieldname, $formid, $division_tab )
	{
	
	    $newModifiedValues = $newEntity->getLastModified();
	    
	    if (isset($newModifiedValues[$fieldname])) {
	        
	        $new_values = $newModifiedValues[$fieldname];
	        
	        if($fieldname == 'receiver')
	        {
	        	if(substr($data['receiver'], 0, 1) == 'p')
	        	{
	        		$new_values = $this->_patientMasterData['last_name'] . ' ' . $this->_patientMasterData['first_name'] . ' - ' . $this->_patientMasterData['email'];
	        	}
	        	else
	        	{
	        		$new_values = $this->_patientMasterData['ContactPersonMaster'][substr($data['receiver'], 1)]['cnt_last_name'] . ' ' . $this->_patientMasterData['ContactPersonMaster'][substr($data['receiver'], 1)]['cnt_first_name'] . ' - ' . $this->_patientMasterData['ContactPersonMaster'][substr($data['receiver'], 1)]['cnt_email'];
	        	}
	        }
	        
	        $history = [
	            'ipid' => $ipid,
	            'clientid' => $this->logininfo->clientid,
	            'formid' => $formid,
	            'fieldname' => $fieldname,
	            'fieldvalue' => $new_values,
	        ];
	        
	        $newH = new BoxHistory();
	        $newH->fromArray($history);
	        $newH->save();
	
	    }
	
	}

	
	
	public function getSurveysArray()
	{
	    
	    $client_surveys = MePatientSurveysTable::find_survey_ByClientid($this->logininfo->clientid);
	    
	    $fd = new FamilyDegree();
	    $getFamilyDegrees_values = array();
	    
	    $rows =$fd->getTable()->findBy('clientid', $this->logininfo->clientid)->toArray();
	    
	    
	    if ( ! empty($rows))
	        foreach ($rows as $row) {
	            if($row['isdelete'] == 0){ // TODO-2262 - FamilyDegree  does not heve soft delete - isdelete=1  must be manualy removed
	                $getFamilyDegrees_values[$row['id']] = $row['family_degree'];
	            }
	        }
	    
	    
	    uasort($getFamilyDegrees_values, array(new Pms_Sorter(), "_strnatcmp"));
	    
	    $getFamilyDegrees_values = array( ''=> $this->translate('pleaseselect')) + $getFamilyDegrees_values;
	    
	    return $getFamilyDegrees_values;

	}
	
}

