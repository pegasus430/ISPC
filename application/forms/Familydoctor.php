<?php
//require_once("Pms/Form.php");

class Application_Form_Familydoctor extends Pms_Form 
{
    protected $_model = 'FamilyDoctor';
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_family_doctor' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //     	        "further_assessment",
                //     	        "training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //     	        "inclusion_measures",
            ],
        ],
    ];
    
    
    public function getVersorgerExtract($param = null) 
    {
        return array(
            array( "label" => $this->translate('practice'), "cols" => array("practice")),
            array( "label" => $this->translate('nice_name'), "cols" => array("nice_name")),
            array( "label" => $this->translate('practice_phone'), "cols" => array("phone_practice")),
            array( "label" => $this->translate('Mobile number'), "cols" => array("phone_private") ),
            array( "label" => $this->translate('fax'), "cols" => array("fax") ),
            array( "label" => $this->translate('shift_billing'), "cols" => array("shift_billing") ),
        );
    }

    public function getVersorgerAddress() 
    {
        return array(
            array(array("nice_name")),
            array(array("street1")),
            array(array('zip'), array("city")),
        );
    }
    
    
		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$val = new Pms_Validation();
			if(!$val->isstring($post['city']))
			{
				$this->error_message['city'] = $Tr->translate('city_error');
				$error = 7;
			}
			if($error == 0)
			{
				return true;
			}

			return false;
		}
    
		/**
		 * @auth Ancuta - 27.05.2019
		 * @param unknown $post
		 * @return boolean
		 */
		
		public function validateAdm($post)
		{
			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$val = new Pms_Validation();
			if(empty($post['fd_last_name']))
			{
				$this->error_message['doc_last_name'] = $Tr->translate('fd_lastname_error');
				$error = 1;
			}
			if(empty($post['fd_first_name']))
			{
				$this->error_message['doc_first_name'] = $Tr->translate('fd_firstname_error');
				$error = 2;
			}

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

			$fdoc = new FamilyDoctor();
			$fdoc->clientid = $logininfo->clientid;
			$fdoc->practice = $post['practice'];
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
			$fdoc->phone_practice = $post['phone_practice'];
			$fdoc->phone_cell = $post['phone_cell'];
			$fdoc->phone_private = $post['phone_private'];
			$fdoc->fax = $post['fax'];
			$fdoc->email=$post['email'];
			$fdoc->doctornumber = $post['doctornumber'];
			$fdoc->doctor_bsnr = $post['doctor_bsnr'];
			$fdoc->comments = $post['comments'];
			//ISPC-2272 (@ancuta 23.10.2018)
			$fdoc->debitor_number = $post['debitor_number'];
			//---
					
			$fdoc->save();

			return $fdoc;
		}

		public function InsertDataFromAdmission($post,$ipid = null)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$fdoc = new FamilyDoctor();
			$fdoc->clientid = $logininfo->clientid;
// 			$fdoc->last_name = $post['doclast_name'];
			
			$fdoc->first_name = $post['fd_first_name'];
			$fdoc->last_name = $post['fd_last_name'];
			$fdoc->title = $post['fd_title'];
			$fdoc->salutation = $post['fd_salutation'];
			
			$fdoc->street1 = $post['doc_street1'];
			$fdoc->zip = $post['doc_zip'];
			$fdoc->indrop = $post['indrop'];
			$fdoc->city = $post['doc_city'];
			$fdoc->phone_practice = $post['phone_practice'];
			$fdoc->phone_cell = $post['phone_cell'];
			$fdoc->phone_private = $post['phone_private'];
			$fdoc->fax = $post['doc_fax'];
			$fdoc->email=$post['email'];
			//ISPC-2272 (@ancuta 23.10.2018)
			$fdoc->debitor_number = $post['debitor_number'];
			
			$fdoc->shift_billing = (int)$post['shift_billing'];
			//---
			
			//$fdoc->self_id = ! empty($post['hidd_docid']) ? $post['hidd_docid'] : null;
			
			$fdoc->save();
			
			if (empty($post['hidd_docid'])) {
			    /*
			     * TODO
			     * optionA : send patient name to the users as plain text (no xxx ?),
			     * optionB - insert patient first, fetch id and then send link with assigned epid
			     */
			    $this->_manual_familydoc_message_send($fdoc,$ipid);
			}
			
			return $fdoc;
		}

		public function InsertFromTabData($post, $admission = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$docname = explode(",", $post['familydoc_id']);

			if(count($docname) > 0)
			{
				$last_name = $docname[0];
				$first_name = $docname[1];
			}
			else
			{
				$last_name = $post['familydoc_id'];
				$first_name = "";
			}

			if($post['hidd_docid'] > 0)
			{
				$newdoc = 0;
				$retain = Doctrine::getTable('FamilyDoctor')->find($post['hidd_docid']);
				if($retain)
				{
					$retainarr = $retain->toArray();

					if(count($retainarr) > 0)
					{
						if($retainarr['indrop'] == 1)
						{
							$newdoc = 1;
						}
					}
				}

				if($post['updatemain'] == 1 || $newdoc == 1)
				{
					if($admission)
					{
						$fdoc = Doctrine::getTable('FamilyDoctor')->find($post['hidd_docid']);
						$fdoc->first_name = $post['first_name'];
						$fdoc->last_name = $post['last_name'];
						$fdoc->title = $post['title'];
						$fdoc->salutation = $post['salutation'];
						$fdoc->street1 = $post['doc_street1'];
						$fdoc->zip = $post['doc_zip'];
						$fdoc->fax = $post['doc_fax'];
						$fdoc->email=$post['doc_email'];
						$fdoc->city = $post['doc_city'];
						$fdoc->phone_practice = $post['phone_practice'];
						$fdoc->phone_cell = $post['phone_cell'];
						$fdoc->phone_private = $post['phone_private'];
						$fdoc->doctornumber = $post['doctornumber'];
						$fdoc->doctor_bsnr = $post['doctor_bsnr'];
						$fdoc->practice = $post['practice'];
						//ISPC-2272 (@ancuta 23.10.2018)
						$fdoc->debitor_number = $post['debitor_number'];
						//---
						$fdoc->shift_billing = (int)$post['shift_billing'];
						$fdoc->save();
					}
					else
					{
						$fdoc = Doctrine::getTable('FamilyDoctor')->find($post['hidd_docid']);
						$fdoc->first_name = $post['first_name'];
						$fdoc->last_name = $post['last_name'];
						$fdoc->title = $post['title'];
						$fdoc->salutation = $post['salutation'];
						$fdoc->street1 = $post['street1'];
						$fdoc->zip = $post['zip'];
						$fdoc->fax = $post['fax'];
						$fdoc->email=$post['email'];
						$fdoc->city = $post['city'];
						$fdoc->phone_practice = $post['phone_practice'];
						$fdoc->phone_cell = $post['phone_cell'];
						$fdoc->phone_private = $post['phone_private'];
						$fdoc->doctornumber = $post['doctornumber'];
						$fdoc->doctor_bsnr = $post['doctor_bsnr'];
						$fdoc->comments = $post['comments'];
						$fdoc->practice = $post['practice'];
						//ISPC-2272 (@ancuta 23.10.2018)
						$fdoc->debitor_number = $post['debitor_number'];
						//---
						$fdoc->shift_billing = (int)$post['shift_billing'];
						$fdoc->save();
					}
				}
				else
				{
					if($admission)
					{
						$fdoc = new FamilyDoctor();
						$fdoc->first_name = $post['first_name'];
						$fdoc->last_name = $post['last_name'];
						$fdoc->title = $post['title'];
						$fdoc->salutation = $post['salutation'];
						$fdoc->street1 = $post['doc_street1'];
						$fdoc->zip = $post['doc_zip'];
						$fdoc->fax = $post['doc_fax'];
						$fdoc->email=$post['doc_email'];
						$fdoc->city = $post['doc_city'];
						$fdoc->indrop = 1;
						$fdoc->clientid = $clientid;
						$fdoc->phone_practice = $post['phone_practice'];
						$fdoc->phone_cell = $post['phone_cell'];
						$fdoc->phone_private = $post['phone_private'];
						$fdoc->doctornumber = $post['doctornumber'];
						$fdoc->doctor_bsnr = $post['doctor_bsnr'];
						$fdoc->practice = $post['practice'];
						//ISPC-2272 (@ancuta 23.10.2018)
						$fdoc->debitor_number = $post['debitor_number'];
						//---
						$fdoc->shift_billing = (int)$post['shift_billing'];
						$fdoc->save();
					}
					else
					{
						$fdoc = new FamilyDoctor();
						$fdoc->first_name = $post['first_name'];
						$fdoc->last_name = $post['last_name'];
						$fdoc->title = $post['title'];
						$fdoc->salutation = $post['salutation'];
						$fdoc->street1 = $post['street1'];
						$fdoc->zip = $post['zip'];
						$fdoc->fax = $post['fax'];
						$fdoc->email=$post['email'];
						$fdoc->city = $post['city'];
						$fdoc->indrop = 1;
						$fdoc->clientid = $clientid;
						$fdoc->phone_practice = $post['phone_practice'];
						$fdoc->phone_cell = $post['phone_cell'];
						$fdoc->phone_private = $post['phone_private'];
						$fdoc->doctornumber = $post['doctornumber'];
						$fdoc->doctor_bsnr = $post['doctor_bsnr'];
						$fdoc->comments = $post['comments'];
						$fdoc->practice = $post['practice'];
						//ISPC-2272 (@ancuta 23.10.2018)
						$fdoc->debitor_number = $post['debitor_number'];
						//---
						$fdoc->shift_billing = (int)$post['shift_billing'];
						$fdoc->save();
					}
				}
			}
			else
			{
				if($admission)
				{
					$fdoc = new FamilyDoctor();
					$fdoc->first_name = $post['first_name'];
					$fdoc->last_name = $post['last_name'];
					$fdoc->title = $post['title'];
					$fdoc->salutation = $post['salutation'];
					$fdoc->street1 = $post['doc_street1'];
					$fdoc->zip = $post['doc_zip'];
					$fdoc->fax = $post['doc_fax'];
					$fdoc->email=$post['doc_email'];
					$fdoc->city = $post['doc_city'];
					$fdoc->indrop = 1;
					$fdoc->clientid = $clientid;
					$fdoc->phone_practice = $post['phone_practice'];
					$fdoc->phone_cell = $post['phone_cell'];
					$fdoc->phone_private = $post['phone_private'];
					$fdoc->doctornumber = $post['doctornumber'];
					$fdoc->doctor_bsnr = $post['doctor_bsnr'];
					$fdoc->practice = $post['practice'];
					//ISPC-2272 (@ancuta 23.10.2018)
					$fdoc->debitor_number = $post['debitor_number'];
					//---
					$fdoc->save();
				}
				else
				{
					$fdoc = new FamilyDoctor();
					$fdoc->first_name = $post['first_name'];
					$fdoc->last_name = $post['last_name'];
					$fdoc->title = $post['title'];
					$fdoc->salutation = $post['salutation'];
					$fdoc->street1 = $post['street1'];
					$fdoc->zip = $post['zip'];
					$fdoc->fax = $post['fax'];
					$fdoc->email=$post['email'];
					$fdoc->city = $post['city'];
					$fdoc->indrop = 1;
					$fdoc->clientid = $clientid;
					$fdoc->phone_practice = $post['phone_practice'];
					$fdoc->phone_cell = $post['phone_cell'];
					$fdoc->phone_private = $post['phone_private'];
					$fdoc->doctornumber = $post['doctornumber'];
					$fdoc->doctor_bsnr = $post['doctor_bsnr'];
					$fdoc->practice = $post['practice'];
					//ISPC-2272 (@ancuta 23.10.2018)
					$fdoc->debitor_number = $post['debitor_number'];
					//---
					$fdoc->save();
				}
				$ipid = null;
				if(isset($post['ipid'])){
				    $ipid = $post['ipid'];
				}
				
				//164 = Family Doc manually added -> send message
				$this->_manual_familydoc_message_send($fdoc,$ipid);
			}

			return $fdoc;
		}

		public function UpdateData($post)
		{

			if ($fdoc = Doctrine::getTable('FamilyDoctor')->find($post['did'])) 
			{
    			$fdoc->practice = $post['practice'];
    			$fdoc->title = $post['title'];
    			if($post['clientid'] > 0)
    			{
    				$fdoc->clientid = $post['clientid'];
    			}
    			$fdoc->last_name = $post['last_name'];
    			$fdoc->first_name = $post['first_name'];
    			$fdoc->street1 = $post['street1'];
    			$fdoc->zip = $post['zip'];
    			$fdoc->salutation = $post['salutation'];
    			$fdoc->medical_speciality = $post['medical_speciality'];
    			$fdoc->city = $post['city'];
    			$fdoc->phone_practice = $post['phone_practice'];
    			$fdoc->phone_cell = $post['phone_cell'];
    			$fdoc->phone_private = $post['phone_private'];
    			$fdoc->fax = $post['fax'];
    			$fdoc->email=$post['email'];
    			$fdoc->doctornumber = $post['doctornumber'];
    			$fdoc->doctor_bsnr = $post['doctor_bsnr'];
    			$fdoc->comments = $post['comments'];
    			//ISPC-2272 (@ancuta 23.10.2018)
    			$fdoc->debitor_number = $post['debitor_number'];
    			//---
    			
    			$previous_shift_billing = $fdoc->shift_billing;
    			$fdoc->shift_billing = (int)$post['shift_billing'];
    			
    			$fdoc->save();
    			
    			
    			if ($fdoc->shift_billing != $previous_shift_billing) {
    			    //update all childrens shift_billing
    			    Doctrine_Query::create()
    			    ->update('FamilyDoctor')
    			    ->set('shift_billing', "?" , [$fdoc->shift_billing])
    			    ->where("self_id = ?", $fdoc->id)
    			    ->execute();
    			}
			
			}
		}


		
	/**
	 * FamilyDoctor formular
	 * @claudiu 27.11.2017
	 * @param array $options, optional values to populate the form
	 * @param string $elementsBelongTo, optional, defaults to $this->elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	public function create_form_family_doctor($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_family_doctor");
	    
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('familydoc'));
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
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2, 'openOnly' =>true)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' =>true )),
	
	        ),
	    ));
	    
	    $subform->addElement('hidden', 'practice', array(
	        'value'        => $options['practice'] ? $options['practice'] : 0 ,
	        'required'     => false,
	        'label'        => null,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	    
	        ),
	    ));
	    $subform->addElement('hidden', 'self_id', array(
	        'value'        => isset($options['self_id']) ? $options['self_id'] : $options['id'] ,
	        'required'     => false,
	        'label'        => null,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2, 'closeOnly' =>true)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'closeOnly' =>true )),
	             
	        ),
	    ));
	    
	    
	     
	    $subform->addElement('text', 'nice_name', array(
	        'value'        => $options['nice_name'] ,
	        'label'        => $this->translate('familydoc'),
	        //'placeholder' => 'Search my date',
	        'data-livesearch' => "FamilyDoctor",
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	
	        ),
	    ));
	    $subform->addElement('text', 'title', array(
	        'value'        => $options['title'] ,
	        'label'        => $this->translate('title'),
	        //'placeholder' => 'Search my date',
	        //'data-livesearch' => "ContactPerson",
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
	    
	    $notEmpty = new Zend_Validate_NotEmpty(["type"=> "string"]);
	    /*
	     * EXAMPLE to setup your own individual error 
	     */
// 	    $notEmpty->setMessage('Engslish error message so the dev can understand !!', 'isEmpty');

		//Maria:: Migration CISPC to ISPC 20.08.2020
		//ISPC-2624 Elena ? 
        $subform->addElement('text', 'first_name', array(
            'value'        => $options['first_name'] ,
            'label'        => $this->translate('firstname'),
            'required'     => false,               //ISPC-2490 Lore 26.02.2020// Maria:: Migration ISPC to CISPC 08.08.2020
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
	    	'value'        => $options['last_name'] ,
	        'label'        => $this->translate('lastname'),
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'validators'   => array($notEmpty),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));

	    $subform->addElement('text', 'salutation', array(
	    	'value'        => $options['salutation'] ,
	        'label'        => $this->translate('salutation'),
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
	        'value'        => $options['street1'] ,
	        'label'        => $this->translate('address'),
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
	        'value'        => $options['zip'] ,
	        'label'        => $this->translate('zip'),
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
	        'value'        => $options['city'],
	        'label'        => $this->translate('city'),
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
	        'value'        => $options['phone_practice'],
	        'label'        => $this->translate('practice_phone'),
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
	    $subform->addElement('text', 'phone_private', array(
	        'value'        => $options['phone_private'],
	        'label'        => $this->translate('Mobile number'),
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
	        'value'        => $options['fax'],
	        'label'        => $this->translate('fax'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
// 	        'validators'   => array('NotEmpty', 'EmailAddress'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'email', array(
	        'value'        => $options['email'],
	        'label'        => $this->translate('email'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('EmailAddress'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'doctornumber', array(
	        'value'        => $options['doctornumber'],
	        'label'        => $this->translate('LANR'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'doctor_bsnr', array(
	        'value'        => $options['doctor_bsnr'],
	        'label'        => $this->translate('bsnr'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));	   
	    $subform->addElement('textarea', 'comments', array(
	        'value'        => $options['comments'],
	        'label'        => $this->translate('comments'),
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
	        'cols'         => 60,
	    ));
	    
	    $subform->addElement('checkbox', 'fdoc_caresalone', array(
	        'value'        => $this->_patientMasterData['fdoc_caresalone'],
	        'label'        => $this->translate('familycare'),
	        'multiOptions' => array( 1 => ''),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));

	    
	    if($this->_patientMasterData['ModulePrivileges'][176]){ // ISPC-2257 Ancuta 12.10.2018
	        
    	    $subform->addElement('checkbox', 'shift_billing', array(
    	        'value'        => $options['shift_billing'],
    	        'label'        => 'shift_billing',
    	        'checkedValue'    => '1',
                'uncheckedValue'  => '0',
    	        'required'   => false,
    	        'filters'    => array('StringTrim'),
    	        'validators' => array('NotEmpty'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	            array('Label', array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	        ),
    	    ));
    	    
    	    
    	    
    	    //ISPC-2272 (@ancuta 23.10.2018)
    	    $subform->addElement('text', 'debitor_number', array(
    	        'value'        => $options['debitor_number'],
    	        'label'        => $this->translate('debitor_number'),
    	        'required'   => false,
    	        'filters'    => array('StringTrim'),
    	        'validators' => array('NotEmpty'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	            array('Label', array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	        ),
    	    ));
    	    
	    } 
	     
	    //@todo
// 	    $subform->addElement('checkbox', 'updatemain', array(
// 	        'value'        => $options['updatemain'],
// 	        'label'        => $this->translate('updatemain'),
// 	        'multiOptions' => array( 1 => ''),
// 	        'required'   => false,
// 	        'filters'    => array('StringTrim'),
// 	        'validators' => array('NotEmpty'),
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array('Errors'),
// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
// 	            array('Label', array('tag' => 'td')),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
// 	        ),
// 	    ));
	    
	    
	    /*
	     * ispc-2291
	     * ISPC-2332
	     */
	    if ($this->_patientMasterData['ModulePrivileges'][182] 
	        || $this->_patientMasterData['ModulePrivileges'][183] ) 
	    {
	        $subform->addElement('radio', 'infusion_protocol', array(
	             
	            'value'    => isset($options['infusion_protocol']) ? $options['infusion_protocol'] : null,
	             
	            'multiOptions' => $this->getColumnMapping('infusion_protocol'),
	            //'separator'    => PHP_EOL,
	            'label'      => "Doctor wants infusion protocol",
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array(
	                    'tag' => 'td',
	                    'class' => "label_same_size_auto",
	                    "openOnly" => true
	                )),
    	            array('Label', array('tag' => 'td', )),
	                array(array('row' => 'HtmlTag'), array(
	                    'tag' => 'tr', 
	                    "openOnly" => true
	                )),
	            ),
	            
	            'onChange' => "if (this.value == 'yes') {\$('.selector_infusion_protocol_freetext').show();} else {\$('.selector_infusion_protocol_freetext').hide();}",              
	        ));
	        
	        $display_none = $options['infusion_protocol'] == 'yes' ? '' : 'display_none';
	        $subform->addElement('text', 'infusion_protocol_freetext', array(
	        
	            'value'    => isset($options['infusion_protocol_freetext']) ? $options['infusion_protocol_freetext'] : null,
	        
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array(
	                    'tag' => 'td',
	                    "closeOnly" => true
	                )),
	                array(array('row' => 'HtmlTag'), array(
	                    'tag' => 'tr',
	                    "closeOnly" => true
	                )),
	            ),
	            'class' => "selector_infusion_protocol_freetext {$display_none}",
	            'style' => "width:80%; float:right; position: relative; top:-20px",
	            
	        ));
	    }
	    
	    /*
	     * ispc-2291
	     */
        if ($this->_patientMasterData['ModulePrivileges'][182])
        {
	        $subform->addElement('text', 'emergency_call_number', array(
	            'value'        => isset($options['emergency_call_number']) ? $options['emergency_call_number'] : null,
	            'label'        => 'Emergency call number',
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
	        
	        $subform->addElement('text', 'emergency_preparedness_1', array(
	            'value'        => isset($options['emergency_preparedness_1']) ? $options['emergency_preparedness_1'] : null,
	            'label'        => 'Emergency Preparedness',
	            'required'     => false,
	            'filters'      => array('StringTrim'),
	            'validators'   => array('NotEmpty'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'tagClass'=>'print_column_data', 'openOnly' => true)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
	            ),
	            'class' => 'emergency_preparedness_1',
	        ));
	        $subform->addElement('text', 'emergency_preparedness_2', array(
	            'value'        => isset($options['emergency_preparedness_2']) ? $options['emergency_preparedness_2'] : null,
	            'label'        => 'Emergency Preparedness',
	            'required'     => false,
	            'filters'      => array('StringTrim'),
	            'validators'   => array('NotEmpty'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	            ),
	            'class' => 'emergency_preparedness_2',
	        ));
	    }//eo module 182
	
	    return $this->filter_by_block_name($subform, $__fnName);
	
	}
			
	
	public function save_form_family_doctor($ipid =  null , $data = array())
	{
	    
	    $data['ipid'] = $ipid;
	    $data['clientid'] = $this->logininfo->clientid;
	    
	    if ( ! empty($data['id']) && isset($data['self_id']) && empty($data['self_id'])) {
	        unset($data['self_id']);
	    }
	    
	    
 
	    $entity = new FamilyDoctor();
		// Maria:: Migration ISPC to CISPC 08.08.2020	
	    $skip_listner = 0; 
	    if (  empty($data['id']) ){ // bypass only for new
    	    //IPSC-2614
	         $skip_listner = 1; 
    	    $pc_listener = $entity->getListener()->get('IntenseConnectionListener');
    	    $pc_listener->setOption('disabled', true);
    	    //--
	    }
	    
	    //ISPC-2807 Lore 24.02.2021
	    if(!isset($data['__thisIsNotThePatientsFamilyDoctor'])){           
	        $save_toVerlauf = $this->save_family_doctor_to_Verlauf($data);
	    }
	    //.
	    
	    /*
	     * bugfix 12.09.2018
	     * patients are saved with the original familydoc id ... so editing in one pacient will change other pacients
	     * first check if this is the original
	     */
	    if ( ! empty($data['id'])) {
	        if ($fdoc =  $entity->getTable()->findOneByIdAndIndrop( $data['id'], 0)) {
	            /*
	             * we create a new doctor that will be added to the pacient
	             */
	            $fdocArr = $fdoc->toArray();
	            
	            unset($fdocArr['id'], $fdocArr['isdelete'], $fdocArr['create_date'], $fdocArr['change_date'], $fdocArr['create_user'], $fdocArr['change_user']);
	            
	            $fdocArr['indrop'] = 1;
	            $fdocArr['clientid'] = $this->logininfo->clientid;
	            
	           $entity->fromArray($fdocArr);
	           $entity->save();
	           
	           $data['id'] = $entity->id;
	        }
	    }
	    
	    $fdoc =  $entity->findOrCreateOneBy( 'id', $data['id'], $data);
	    
	    if (empty($data['id']) && empty($data['self_id']) ) {
	        $this->_manual_familydoc_message_send($fdoc, $ipid);
	    }
	    
	    //IPSC-2614// Maria:: Migration ISPC to CISPC 08.08.2020	
	    if($skip_listner == 1){
    	    $pc_listener->setOption('disabled', false);
	    }
	    //--
	    
	    //ISPC-2614 Ancuta :: Hack to re-trigger listner -
	    $entity->comments = $data['comments'].' ';
	    $entity->save();
	    //-- 
	    
	    return $fdoc;
	}
	
	
	

	/**
	 * if you have module 164 = Family Doc manually added -> send message
	 * send message when a new dowctor was added
	 * 
	 * @param FamilyDoctor $fdoc
	 * @return void
	 */
	private function _manual_familydoc_message_send(FamilyDoctor $fdoc, $ipid = null)
	{
	
	    $modules = new Modules();
	    if( ! $modules->checkModulePrivileges(164)) {
	        return;
	    }
	    
	    $doctor_first_last_name = $fdoc->first_name . " " . $fdoc->last_name;
	   
        // TODO-2336 - Added By Ancuta
	    if($ipid){
	        $record = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid, Doctrine_Core::HYDRATE_RECORD);
	        if( ! empty($record)){
	            $patient_details = $record->toArray();
	            $epid = Pms_CommonData::getEpidFromId($patient_details['id']);
	            $pat_encoded_id = $patient_details['id'] ? Pms_Uuid::encrypt($patient_details['id']) : 0;
	            $patientLink = "<a href='patientcourse/patientcourse?id={$pat_encoded_id}'>{$epid}</a>";	             
	        }
	    }
	    else
	    {
	        
	        $patientMasterData =  $this->_patientMasterData;
	        $pat_encoded_id = $patientMasterData['id'] ? Pms_Uuid::encrypt($patientMasterData['id']) : 0;
    	    $patientLink = "<a href='patientcourse/patientcourse?id={$pat_encoded_id}'>{$patientMasterData['epid']}</a>";
	    }
	
	    $users = User::get_AllByClientid($this->logininfo->clientid, array('us.manual_familydoc_message', 'username'));
	    
	    //remove inactive and deleted, and the ones with clientid=0
	    $users = array_filter($users, function($user) {
	        return ( ! $user['isdelete']) && ( ! $user['isactive']) && ($user['clientid'] > 0) && ($user['UserSettings']['manual_familydoc_message'] == 'yes');
	    });
	    
	    
	    if (empty($users)) {
            return; // no settings
        }

        //remove inactive and deleted, and the ones with clientid=0
        $users_with_emails = array_filter($users, function($user) {
            return strlen(trim($user['emailid']));
        });

         
        $message_title = $this->translate("New FamilyDoctor was manualy added");
        $message_title_enc = Pms_CommonData::aesEncrypt($message_title);
         
         
        $message_body = sprintf($this->translate('New FamilyDoctor %s was manualy added, please take action on %s'), $doctor_first_last_name, $patientLink);
        $message_body = Pms_CommonData::br2nl($message_body);
        $message_body_enc = Pms_CommonData::aesEncrypt($message_body);

        $recipients = array_column($users, 'id');
         
        $records_template = array(
            "sender" => $this->logininfo->userid,
            "clientid" => $this->logininfo->clientid,
            "recipient" => null,
            "recipients" => implode(",", $recipients),
            "msg_date" => date("Y-m-d H:i:s", time()),
            "title" => $message_title_enc,
            "content" => $message_body_enc,
            "create_date" => date("Y-m-d", time()),
            "create_user" => $this->logininfo->userid,
        );
         
        $records_array = array();
        foreach($users as $user) {
            $record = $records_template;
            $record['recipient'] = $user['id'];
            $records_array[] = $record;
        }
        if ( ! empty($records_array)) {
            $collection = new Doctrine_Collection('Messages');
            $collection->fromArray($records_array);
            $collection->save();
        }
         
         
        //send email too ??
        $additional_text =
        $message_body = sprintf($this->translate('New FamilyDoctor %s was manualy added, please take action on %s'), $doctor_first_last_name, $patientMasterData['epid']);
        //$message_body .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>"; // link to ISPC
        // ISPC-2475 @Lore 31.10.2019// Maria:: Migration ISPC to CISPC 08.08.2020	
        $message_body .= $this->translate('system_wide_email_text_login');
        
        
        
        
        //TODO-3164 Ancuta 08.09.2020
        $email_data = array();
        $email_data['additional_text'] = $additional_text;
        $message_body = "";//overwrite
        $message_body = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
        //--

        $this->_mail_forceDefaultSMTP = false;
        foreach($users_with_emails  as $user) {
            $this->sendEmail( $user['emailid'] , "ISPC - {$message_title}", $message_body);
        }
         
        return;
	             
	}
	

	public function getColumnMapping($fieldName, $revers = false)
	{
	
	    //             $fieldName => [ value => translation]
	    $overwriteMapping = [
	        'infusion_protocol' => [//added like this cause translations
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
// 	        'alcohol_frequency' => [
// 	            ''  => '---' //extra empty value for select
// 	        ]
	         
	    ];
	
	
	    $values = Doctrine_Core::getTable($this->_model)->getEnumValues($fieldName);
	
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
	
	
	//ISPC-2807 Lore 24.02.2021
	public function save_family_doctor_to_Verlauf($post)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $userid = $logininfo->userid;
	    $decid = Pms_Uuid::decrypt($_REQUEST['id']);
	    $ipid = Pms_CommonData::getIpid($decid);
	    
	    //dd($post);
	    $model= new FamilyDoctor();
	    $old_vals_db_data = $model->getFamilyDoc($post['id']);
	    //dd($old_vals_db_data);
	    $course_title = '';
	    
	    if($post['id'] == '0'){
	        $course_title .= "Der Hausarzt wurde hinzugefügt: ".$post['nice_name'] . "\n\r";
	        
	    } else {
	        foreach($post as $key => $vals){
	            
	            //if($key != 'nice_name' && $key != 'clientid' && $key != 'ipid' && $key != 'self_id' && $key != 'id' ) {
	            //TODO-3930 Lore 08.03.2021
	            if($key != 'nice_name' && $key != 'clientid' && $key != 'ipid' && $key != 'self_id' && $key != 'id' && $key != 'wlassessment_id') {
	                
	                if(!(empty($vals)) || !(empty($old_vals_db_data[0][$key]))){
	                    if($old_vals_db_data[0][$key] != $vals ){
	                        if($key == 'infusion_protocol'){
	                            $yes_no = array('yes' => 'ja', 'no'=>'nein');
	                            $last_value = $old_vals_db_data[0][$key];
	                            //$course_title .= "Der ". $this->translate($key).' des Hausarzt wurde geändert: '.$yes_no[$last_value] .' -> '.$yes_no[$vals] . "\n\r";
	                            //TODO-3930 Lore 08.03.2021
	                            if($yes_no[$last_value] != $yes_no[$vals]){
	                                $course_title .= "Der ". $this->translate($key).' des Hausarzt wurde geändert: '.$yes_no[$last_value] .' -> '.$yes_no[$vals] . "\n\r";
	                            }
	                        }
	                        elseif($key == 'shift_billing' || $key == 'fdoc_caresalone'){
	                            $yes_no = array('1' => 'ja', '0'=>'nein');
	                            $last_value = $old_vals_db_data[0][$key];
	                            //$course_title .= "Der ". $this->translate($key).' des Hausarzt wurde geändert: '.$yes_no[$last_value] .' -> '.$yes_no[$vals] . "\n\r";
	                            //TODO-3930 Lore 08.03.2021
	                            if(isset($old_vals_db_data[0][$key]) && $yes_no[$last_value] != $yes_no[$vals]){
	                                $course_title .= "Der ". $this->translate($key).' des Hausarzt wurde geändert: '.$yes_no[$last_value] .' -> '.$yes_no[$vals] . "\n\r";
	                            }
	                                
	                        }else {
	                            $last_value = $old_vals_db_data[0][$key];
	                            $course_title .= "Der ". $this->translate($key).' des Hausarzt wurde geändert: '.$last_value .' -> '.$vals . "\n\r";
	                            
	                        }
	                    }
	                }
	            }
	        }
	    }
	    
	    $recordid = $post['id'];
	    if(!empty($course_title)){
	        $insert_pc = new PatientCourse();
	        $insert_pc->ipid =  $ipid;
	        $insert_pc->course_date = date("Y-m-d H:i:s", time());
	        $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
	        $insert_pc->tabname = Pms_CommonData::aesEncrypt($post['__category']);
	        $insert_pc->recordid = $recordid;
	        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($course_title));
	        $insert_pc->user_id = $userid;
	        $insert_pc->save();
	    }
	    
	    
	}
		
}

?>