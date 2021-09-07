<?php

require_once("Pms/Form.php");

class Application_Form_Homecare extends Pms_Form 
{
    
    public function getVersorgerExtract()
    {
        return  array(
            array("label"=>$this->translate('homecare'), "cols"=>array("Homecare"=>"homecare")),
            array("label"=>$this->translate('nice_name'), "cols"=>array("nice_name")),
            array("label"=>$this->translate('practice_phone'), "cols"=>array("Homecare" => "phone_practice")),
            array("label"=>$this->translate('fax'), "cols"=>array("Homecare" => "fax")),
        );
    }
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("Homecare"=>"homecare")),
            array(array("nice_name")),
            array(array("Homecare"=>"street1")),
            array(array("Homecare"=>"zip"), array("Homecare"=>"city")),
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

			$fdoc = new Homecare();
			$fdoc->clientid = $logininfo->clientid;
			$fdoc->homecare = $post['homecare'];
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


			if(!empty($post['hidd_homeid']))
			{
				$fdoc_get = Doctrine::getTable('Homecare')->find($post['hidd_homeid']);
				if($fdoc_get)
				{
					$home_data = $fdoc_get->toArray();
					$homecare_logo = $home_data['logo'];
				}
				else
				{
					$homecare_logo = '';
				}
			}

			if(!empty($_REQUEST['homeid']))
			{
				if(!empty($post['hidd_homeid']))
				{
					
					
					if($fdoc = Doctrine::getTable('Homecare')->findByIdAndIndrop($post['hidd_homeid'],1)){
						$fdoc = $fdoc{0};
					}
					else
					{
						$fdoc = new Homecare();
					}
					$fdoc->homecare = $post['homecare'];
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
					$fdoc->phone_practice = $post['phone_practice'];
					$fdoc->phone_emergency = $post['phone_emergency'];
					$fdoc->ik_number = $post['ik_number'];
					
					$fdoc->ipid = $post['ipid'];
					$fdoc->is_contact = $post['is_contact'];
					
					$fdoc->save();
				}
				else
				{
					$fdoc = new Homecare();
					$fdoc->homecare = $post['homecare'];
					$fdoc->first_name = $post['first_name'];
					$fdoc->last_name = $post['last_name'];
					$fdoc->salutation = $post['salutation'];
					$fdoc->street1 = $post['street1'];
					$fdoc->zip = $post['zip'];
					$fdoc->fax = $post['fax'];
					$fdoc->email = $post['email'];
					$fdoc->city = $post['city'];
					$fdoc->indrop = 1;
					$fdoc->clientid = $clientid;
					$fdoc->phone_practice = $post['phone_practice'];
					$fdoc->phone_emergency = $post['phone_emergency'];
					$fdoc->logo = $homecare_logo;
					//$fdoc->palliativpflegedienst =$post['palliativpflegedienst'];
					$fdoc->ik_number = $post['ik_number'];
					
					$fdoc->ipid = $post['ipid'];
					$fdoc->is_contact = $post['is_contact'];
					
					$fdoc->save();
				}
			}
			else
			{
				$fdoc = new Homecare();
				$fdoc->homecare = $post['homecare'];
				$fdoc->first_name = $post['first_name'];
				$fdoc->last_name = $post['last_name'];
				$fdoc->salutation = $post['salutation'];
				$fdoc->street1 = $post['street1'];
				$fdoc->zip = $post['zip'];
				$fdoc->fax = $post['fax'];
				$fdoc->email = $post['email'];
				$fdoc->city = $post['city'];
				$fdoc->indrop = 1;
				$fdoc->clientid = $clientid;
				$fdoc->phone_practice = $post['phone_practice'];
				$fdoc->phone_emergency = $post['phone_emergency'];
				$fdoc->logo = $homecare_logo;
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
			$fdoc = Doctrine::getTable('Homecare')->find($post['id']);
			$fdoc->homecare = $post['homecare'];
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
			$fdoc->salutation = $post['salutation'];
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

				//move icon file to desired destination /public/icons/clientid/homecare/icon_db_id.ext
				$icon_upload_path = 'icons_system/' . $_SESSION['filename'];
				$icon_new_path = 'icons_system/' . $clientid . '/homecare/' . $inserted_icon_id . '.' . $filename_ext;

				copy($icon_upload_path, $icon_new_path);
				unlink($icon_upload_path);

				$update = Doctrine::getTable('Homecare')->find($inserted_icon_id);
				$update->logo = $clientid . '/homecare/' . $inserted_icon_id . '.' . $filename_ext;
				$update->save();
			}
		}

		
    
	/**
	 * @cla on 26.06.2018 .. this should be in PatientHomecare
	 *
	 * @param array $options, optional values to populate the form
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	public function create_form_patient_homecare($options =  array() , $elementsBelongTo = null)
	{
	    $this->mapValidateFunction(__FUNCTION__, "create_form_isValid");
	
	    $this->mapSaveFunction(__FUNCTION__, "save_form_patient_homecare");
	    

	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend("homecare");
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
	
// 	            	    if (!empty($options)) dd($options);
	
	    if ( ! isset($options['Homecare'])) {
	        $options['Homecare'] = $options;
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

        //TODO: add column self_id in Homecare table.. so you know what-id you selected from the dropdown
        $subform->addElement('hidden', 'self_id', array(
            'value'        => $options['homeid'] ? $options['homeid'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
         
        $subform->addElement('hidden', 'homeid', array(
            'value'        => $options['homeid'] ? $options['homeid'] : -1 ,
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
        $subform->addElement('text', 'homecare', array(
            'value'        => $options['Homecare']['homecare'] ,
            'label'        => 'homecare',
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
            'data-livesearch'  => 'Homecare',
        ));


		//Maria:: Migration CISPC to ISPC 20.08.2020
		//ISPC-2624 Elena ? 
        $subform->addElement('text', 'first_name', array(
            'value'        => $options['Homecare']['first_name'] ,
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
            'value'        => $options['Homecare']['last_name'] ,
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
            'value'        => $options['Homecare']['salutation'] ,
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
            'value'        => $options['Homecare']['street1'] ,
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
            'value'        => $options['Homecare']['zip'] ,
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
            'value'        => $options['Homecare']['city'],
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
            'value'        => $options['Homecare']['phone_practice'],
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
            'value'        => $options['Homecare']['phone_emergency'],
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
            'value'        => $options['Homecare']['fax'],
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
            'value'        => $options['Homecare']['email'],
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

         
        $subform->addElement('textarea', 'home_comment', array(
            'value'        => ! empty($options['home_comment']) ?  $options['home_comment'] : $options['Homecare']['comments'],
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
            'value'        => $options['Homecare']['is_contact'],
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

        return $this->filter_by_block_name($subform, __FUNCTION__);
	
	}
	
	

	/**
	 *
	 * @param string $ipid
	 * @param array $data
	 * @param number $indrop 0 = in the liveSearch, 1 = not
	 * @return void|Doctrine_Record
	 */
	public function save_form_patient_homecare($ipid =  '', $data = array(), $indrop = 1)
	{
	    $patientModel   = 'PatientHomecare';
	    $relationModel  = 'Homecare';
	
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
	    $entity->home_comment = $data['home_comment'].' ';
	    $entity->save();
	    //-- 
	
	    return $entity;
	
	}
}

?>