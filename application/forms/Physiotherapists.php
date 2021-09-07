<?php

require_once("Pms/Form.php");

class Application_Form_Physiotherapists extends Pms_Form 
{
    
    public function getVersorgerExtract()
    {
        return  array(
            array("label"=>$this->translate('physiotherapist'), "cols"=>array("Physiotherapists"=>"physiotherapist")),
            array("label"=>$this->translate('nice_name'), "cols"=>array("nice_name")),
            array("label"=>$this->translate('practice_phone'), "cols"=>array("Physiotherapists" => "phone_practice")),
            array("label"=>$this->translate('fax'), "cols"=>array("Physiotherapists" => "fax")),
        );
    }
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("Physiotherapists"=>"homecare")),
            array(array("nice_name")),
            array(array("Physiotherapists"=>"street1")),
            array(array("Physiotherapists"=>"zip"), array("Physiotherapists"=>"city")),
        );
    }
    
    

		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$val = new Pms_Validation();
			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function InsertData($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if($post['indrop'] == 1)
			{
				if(strlen($post['doclast_name']) > 0)
				{
					$post['last_name'] = $post['doclast_name'];
				}
			}

			$fdoc = new Physiotherapists();
			$fdoc->clientid = $logininfo->clientid;
			$fdoc->physiotherapist = $post['physiotherapist'];
			$fdoc->title = $post['title'];
			$fdoc->salutation = $post['salutation'];
			$fdoc->medical_speciality = $post['medical_speciality'];
			$fdoc->clientid = $logininfo->clientid;
			$fdoc->last_name = $post['last_name'];
			$fdoc->first_name = $post['first_name'];
			$fdoc->street1 = $post['street1'];
			$fdoc->zip = $post['zip'];
			$fdoc->indrop = $post['indrop'];
			$fdoc->city = $post['city'];
			$fdoc->doctornumber = $post['doctornumber'];
			$fdoc->phone_practice = $post['phone_practice'];
			$fdoc->phone_emergency = $post['phone_emergency'];
			$fdoc->fax = $post['fax'];
			$fdoc->phone_private = $post['phone_private'];
			$fdoc->email = $post['email'];
			$fdoc->comments = $post['comments'];
			$fdoc->ik_number = $post['ik_number'];
			$fdoc->save();

			if($post['chg_icon'] == '1')
			{
				$this->move_uploaded_icon($fdoc->id);
			}
			return $fdoc;
		}

		public function InsertFromTabData($post)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(!empty($post['hidd_physioid']))
			{
				$fdoc_get = Doctrine::getTable('Physiotherapists')->find($post['hidd_physioid']);
				if($fdoc_get)
				{
					$physio_data = $fdoc_get->toArray();
					$physio_logo = $physio_data['logo'];
				}
				else
				{
					$physio_logo = '';
				}
			}

			if(!empty($_REQUEST['physioid']))
			{
				if(!empty($post['hidd_physioid']))
				{
					if($fdoc = Doctrine::getTable('Physiotherapists')->findByIdAndIndrop($post['hidd_physioid'],1)){
						$fdoc = $fdoc{0};
					}
					else
					{
						$fdoc = new Physiotherapists();
					}
					$fdoc->physiotherapist = $post['physiotherapist'];
					$fdoc->clientid = $clientid;
					$fdoc->indrop = 1;
					$fdoc->first_name = $post['first_name'];
					$fdoc->last_name = $post['last_name'];
					$fdoc->salutation = $post['salutation'];
					$fdoc->street1 = $post['street1'];
					$fdoc->zip = $post['zip'];
					$fdoc->fax = $post['fax'];
					$fdoc->email = $post['email'];
					$fdoc->city = $post['city'];
					$fdoc->logo = $physio_logo;
					$fdoc->phone_practice = $post['phone_practice'];
					$fdoc->phone_emergency = $post['phone_emergency'];
					$fdoc->ik_number = $post['ik_number'];
					
					$fdoc->ipid = $post['ipid'];
					$fdoc->is_contact = $post['is_contact'];
					
					$fdoc->save();
				}
				else
				{
					$fdoc = new Physiotherapists();
					$fdoc->physiotherapist = $post['physiotherapist'];
					$fdoc->first_name = $post['first_name'];
					$fdoc->last_name = $post['last_name'];
					$fdoc->salutation = $post['salutation'];
					$fdoc->street1 = $post['street1'];
					$fdoc->zip = $post['zip'];
					$fdoc->fax = $post['fax'];
					$fdoc->email = $post['email'];
					$fdoc->city = $post['city'];
					$fdoc->logo = $physio_logo;
					$fdoc->indrop = 1;
					$fdoc->clientid = $clientid;
					$fdoc->phone_practice = $post['phone_practice'];
					$fdoc->phone_emergency = $post['phone_emergency'];
					//$fdoc->palliativpflegedienst =$post['palliativpflegedienst'];
					$fdoc->ik_number = $post['ik_number'];
					
					$fdoc->ipid = $post['ipid'];
					$fdoc->is_contact = $post['is_contact'];
					
					$fdoc->save();
				}
			}
			else
			{
				$fdoc = new Physiotherapists();
				$fdoc->physiotherapist = $post['physiotherapist'];
				$fdoc->first_name = $post['first_name'];
				$fdoc->last_name = $post['last_name'];
				$fdoc->salutation = $post['salutation'];
				$fdoc->street1 = $post['street1'];
				$fdoc->zip = $post['zip'];
				$fdoc->fax = $post['fax'];
				$fdoc->email = $post['email'];
				$fdoc->city = $post['city'];
				$fdoc->logo = $physio_logo;
				$fdoc->indrop = 1;
				$fdoc->clientid = $clientid;
				$fdoc->phone_practice = $post['phone_practice'];
				$fdoc->phone_emergency = $post['phone_emergency'];
				//$fdoc->palliativpflegedienst =$post['palliativpflegedienst'];
				$fdoc->ik_number = $post['ik_number'];
				
				$fdoc->ipid = $post['ipid'];
				$fdoc->is_contact = $post['is_contact'];
				
				$fdoc->save();
			}
			return $fdoc;
		}

		public function UpdateData($post)
		{
			$fdoc = Doctrine::getTable('Physiotherapists')->find($post['id']);
			$fdoc->physiotherapist = $post['physiotherapist'];
			$fdoc->title = $post['title'];
			if($post['clientid'] > 0)
			{
				$fdoc->clientid = $post['clientid'];
			}
			$fdoc->last_name = $post['last_name'];
			$fdoc->first_name = $post['first_name'];
			$fdoc->salutation = $post['salutation'];
			$fdoc->street1 = $post['street1'];
			$fdoc->zip = $post['zip'];
			$fdoc->medical_speciality = $post['medical_speciality'];
			$fdoc->city = $post['city'];
			$fdoc->phone_practice = $post['phone_practice'];
			$fdoc->phone_private = $post['phone_private'];
			$fdoc->phone_emergency = $post['phone_emergency'];
			$fdoc->fax = $post['fax'];
			$fdoc->email = $post['email'];
			$fdoc->doctornumber = $post['doctornumber'];
			$fdoc->comments = $post['comments'];
			$fdoc->ik_number = $post['ik_number'];
			$fdoc->save();

			if(!empty($_SESSION['filename']) && $post['chg_icon'] == '1')
			{
				$this->move_uploaded_icon($post['id']);
			}
		}

		private function move_uploaded_icon($inserted_icon_id)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			if(!empty($_SESSION['filename']))
			{
				$filename_arr = explode(".", $_SESSION['filename']);

				if(count($filename_arr >= '2'))
				{
					$filename_ext = $filename_arr[count($filename_arr) - 1];
				}
				else
				{
					$filename_ext = 'jpg';
				}

				//move icon file to desired destination /public/icons/clientid/pflege/icon_db_id.ext
				$icon_upload_path = 'icons_system/' . $_SESSION['filename'];
				$icon_new_path = 'icons_system/' . $clientid . '/physiotherapist/' . $inserted_icon_id . '.' . $filename_ext;

				copy($icon_upload_path, $icon_new_path);
				unlink($icon_upload_path);

				$update = Doctrine::getTable('Physiotherapists')->find($inserted_icon_id);
				$update->logo = $clientid . '/physiotherapist/' . $inserted_icon_id . '.' . $filename_ext;
				$update->save();
			}
		}



	/**
	 * @cla on 26.06.2018 .. this should be in PatientPhysiotherapists
	 *
	 * @param array $options, optional values to populate the form
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	public function create_form_patient_physiotherapist($options =  array() , $elementsBelongTo = null)
	{
	    $this->mapValidateFunction(__FUNCTION__, "create_form_isValid");
	
	    $this->mapSaveFunction(__FUNCTION__, "save_form_patient_physiotherapist");

	    	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend("physiotherapist");
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
	
// 	    	            	    if (!empty($options)) dd($options);
	
	    if ( ! isset($options['Physiotherapists'])) {
	        $options['Physiotherapists'] = $options;
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

        //TODO: add column self_id in Physiotherapists table.. so you know what-id you selected from the dropdown
        $subform->addElement('hidden', 'self_id', array(
            'value'        => $options['physioid'] ? $options['physioid'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
         
        $subform->addElement('hidden', 'physioid', array(
            'value'        => $options['physioid'] ? $options['physioid'] : -1 ,
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
        $subform->addElement('text', 'physiotherapist', array(
            'value'        => $options['Physiotherapists']['physiotherapist'] ,
            'label'        => 'physiotherapist',
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
            'data-livesearch'  => 'Physiotherapists',
        ));

		//Maria:: Migration CISPC to ISPC 20.08.2020
		//ISPC-2624 Elena ? 
        $subform->addElement('text', 'first_name', array(
            'value'        => $options['Physiotherapists']['first_name'] ,
            'label'        => 'firstname',
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
        $subform->addElement('text', 'last_name', array(
            'value'        => $options['Physiotherapists']['last_name'] ,
            'label'        => 'lastname',
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
        $subform->addElement('text', 'salutation', array(
            'value'        => $options['Physiotherapists']['salutation'] ,
            'label'        => 'salutation',
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
        $subform->addElement('text', 'street1', array(
            'value'        => $options['Physiotherapists']['street1'] ,
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
            'value'        => $options['Physiotherapists']['zip'] ,
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
            'value'        => $options['Physiotherapists']['city'],
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
        $subform->addElement('text', 'phone_practice', array(
            'value'        => $options['Physiotherapists']['phone_practice'],
            'label'        => 'practice_phone',
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
        $subform->addElement('text', 'phone_emergency', array(
            'value'        => $options['Physiotherapists']['phone_emergency'],
            'label'        => 'Emergency telephone',
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

        $subform->addElement('text', 'fax', array(
            'value'        => $options['Physiotherapists']['fax'],
            'label'        => 'fax',
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
            'value'        => $options['Physiotherapists']['email'],
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

         
        $subform->addElement('textarea', 'physio_comment', array(
            'value'        => ! empty($options['physio_comment']) ?  $options['physio_comment'] : $options['Physiotherapists']['comments'],
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

        $subform->addElement('checkbox', 'is_contact', array(
            'value'        => $options['Physiotherapists']['is_contact'],
            'label'        => $this->translate('real_contact_number'),
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('Int'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
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
	public function save_form_patient_physiotherapist($ipid =  '', $data = array(), $indrop = 1)
	{
	    $patientModel   = 'PatientPhysiotherapist';
	    $relationModel  = 'Physiotherapists';
	
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
	    $entity->physio_comment = $data['physio_comment'].' ';
	    $entity->save();
	    //-- 
	
	    return $entity;
	
	}
}

?>