<?php
require_once("Pms/Form.php");

class Application_Form_PatientChurches extends Pms_Form
{
    
    public function getVersorgerExtract()
    {
        //Hospizverein
        //Versorgungs-Start
        //Versorgungs-Ende
    
        return  array(
            array("label"=>$this->translate('nice_name'), "cols"=>array("nice_name")),
            array("label"=>$this->translate('hospice_association'), "cols"=>array("Churches" => "name")),
            array("label"=>$this->translate('phone'), "cols"=>array("Churches" => "phone")),
            array("label"=>$this->translate('mobile'), "cols"=>array("Churches" => "phone_cell")),
        );
    }
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("Churches"=>"name")),
            array(array("nice_name")),
            array(array("Churches"=>"street")),
            array(array("Churches"=>"zip"), array("Churches"=>"city")),
        );
    }
    
    
	public function validate($post)
	{
		
	}

	public function insertdata ( $post )
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');

		$ins = new PatientChurches();
		$ins->ipid = $post['ipid'];
		$ins->chid = $post['hidd_chsid'];
		$ins->church_comment = $post['church_comment'];
		$ins->save();

		//return $ins;
	}

	public function updatedata ( $post )
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$upd = Doctrine::getTable('PatientChurches')->findOneById($post['id']);

		$upd->church_comment = $post['church_comment'];
		$upd->save();

		//return $upd;
	}
	
	public function changedata ( $post )
	{
		//$logininfo = new Zend_Session_Namespace('Login_Info');
		$upd = Doctrine::getTable('PatientChurches')->findOneById($post['id']);
		$upd->chid = $post['hidd_chsid'];
		$upd->church_comment = $post['church_comment'];
		$upd->save();
	
		//return $upd;
	}

	public function deletedata ( $chspid)
	{
		$del = Doctrine::getTable('PatientChurches')->findOneById($chspid);
		$del->isdelete = 1;
		$del->save();
	}
	
	


	/**
	 * @cla on 27.06.2018
	 *
	 * @param array $options, optional values to populate the form
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	public function create_form_patient_church($options =  array() , $elementsBelongTo = null)
	{
	    $this->mapValidateFunction(__FUNCTION__, "create_form_isValid");
	
	    $this->mapSaveFunction(__FUNCTION__, "save_form_patient_church");
	    
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend("church");
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
	
// 	            	    	            	    if (!empty($options)) dd($options);
	
	    if ( ! isset($options['Churches'])) {
	        $options['Churches'] = $options;
	    }
	
	
	
	
	    /* start with the hidden fields */
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options['id'] ? $options['id'] : 0 ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        //'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            // 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            // 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
	        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
	
	    ),
	    ));

        //TODO: add column self_id in Voluntaryworkers table.. so you know what-id you selected from the dropdown
        //this is id_master ??
        $subform->addElement('hidden', 'self_id', array(
            'value'        => $options['chid'] ? $options['chid'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
         
        $subform->addElement('hidden', 'chid', array(
            'value'        => $options['chid'] ? $options['chid'] : -1 ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            //'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                // 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                // 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
        ),
        ));



            /* visible inputs */
            $subform->addElement('text', 'name', array(
                'value'        => $options['Churches']['name'] ,
                'label'        => 'Pfarre',
                'required'     => true,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'data-livesearch'  => 'Churches',
            ));

			//Maria:: Migration CISPC to ISPC 20.08.2020
			//ISPC-2624 Elema ? 
	    $subform->addElement('text', 'contact_firstname', array(
            'value'        => $options['Churches']['contact_firstname'] ,
            'label'        => 'contact_firstname',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
            $subform->addElement('text', 'contact_lastname', array(
                'value'        => $options['Churches']['contact_lastname'] ,
                'label'        => 'contact_lastname',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'data-livesearch'  => 'Churches',
            ));
            $subform->addElement('text', 'street', array(
                'value'        => $options['Churches']['street'] ,
                'label'        => 'address',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
            $subform->addElement('text', 'zip', array(
                'value'        => $options['Churches']['zip'] ,
                'label'        => 'zip',
                'data-livesearch'  => 'zip',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),doctornumber
            ));
            $subform->addElement('text', 'city', array(
                'value'        => $options['Churches']['city'],
                'label'        => 'city',
                'data-livesearch'   => 'city',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
            $subform->addElement('text', 'phone', array(
                'value'        => $options['Churches']['phone'],
                'label'        => 'phone',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));
            $subform->addElement('text', 'phone_cell', array(
                'value'        => $options['Churches']['phone_cell'],
                'label'        => 'mobile',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));

            $subform->addElement('text', 'email', array(
                'value'        => $options['Churches']['email'],
                'label'        => 'email',
                'required'   => false,
                'filters'    => array('StringTrim'),
    	        'validators' => array('NotEmpty', 'EmailAddress'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
            ));

             
            $subform->addElement('textarea', 'church_comment', array(
                'value'        => $options['church_comment'],
                'label'        => 'comments',
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'rows'         => 3,
                //'cols'         => 60,
            ));



            return $subform;
	
	}
	
	/**
	 *
	 * @param string $ipid
	 * @param array $data
	 * @param number $indrop 0 = in the liveSearch, 1 = not
	 * @return void|Doctrine_Record
	 */
	public function save_form_patient_church($ipid =  '', $data = array(), $indrop = 1)
	{
	    $patientModel   = 'PatientChurches';
	    $relationModel  = 'Churches';
	
	    $ipid = ! empty($ipid) ? $ipid : $this->_ipid ;
	
	    if (empty($ipid) || empty($data)) {
	        return;//fail-safe
	    }
	
	    $entity = new $patientModel();
	    //IPSC-2614
	    $pc_listener = $entity->getListener()->get('IntenseConnectionListener');
	    $pc_listener->setOption('disabled', true);
	    //--
	    $entity = $entity->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	
	    if ( ! $entity) {
	        return; //fail-safe
	    }
	
	    $localField = null;
	    $foreignField = null;
	
	    if ($relation = $entity->getTable()->getRelation($relationModel, false)) {
	        $relation = $relation->toArray();
	        $localField = $relation['local'];
	        $foreignField = $relation['foreign'];
	    }
	
	
	    if ( ! is_null($localField) && ! is_null($foreignField) && $data[$localField] != $entity->{$localField}) {
	        $data[$localField] = $entity->{$localField};
	    }
	
	    $data['indrop'] = $indrop;
	
	
	    $relationEntity = new $relationModel();
	    $relationEntity = $relationEntity->findOrCreateOneBy(['id', 'clientid'], [$data[$localField], $this->logininfo->clientid], $data);
	
	    if ($relationEntity && ! is_null($localField) && $entity->{$localField} != $relationEntity->{$foreignField}) {
	        //it was a new one
	        $entity->{$localField} = $relationEntity->{$foreignField};
	        $entity->save();
	
	        //             if (empty($data['self_id']) ) {
	        //                 $this->_manual_voluntaryworker_message_send($relationEntity);
	        //             }
	    }
	
	    //IPSC-2614
	    $pc_listener->setOption('disabled', false);
	    //--
	    
	    //ISPC-2614 Ancuta :: Hack to re-trigger listner -
	    $entity->church_comment = $data['church_comment'].' ';
	    $entity->save();
	    //-- 
	    
	    return $entity;
	
	}
	
	
}
?>