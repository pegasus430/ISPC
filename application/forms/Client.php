<?php

	require_once("Pms/Form.php");

	class Application_Form_Client extends Pms_Form {
		
		protected $_groups = null;
		
		

		public function getColumnMapping($fieldName, $revers = false)
		{
		
		    $hh = array_map(function ($hh) {return str_pad($hh, 2, "0", STR_PAD_LEFT);}, range(0, 23));
		    
		    $dd = array_map(function ($day) {
		        return date_create('Monday')->modify("+{$day} day")->format('l');
		    },
		    range(0, 6));
		    
		    //             $fieldName => [ value => translation]
		    $overwriteMapping = [
		        		        
		        'tourenplanung_workweek' => 
		            ["-1" => '---']//extra empty value for select
		            + array_combine($dd, $dd)
		        ,
		        
		        'tourenplanung_workhours' => 
		            ["-1" => '---']//extra empty value for select
		            + array_combine($hh, $hh)
		        ,
		        
		    ];
		
		
		    $values = Doctrine_Core::getTable('Client')->getEnumValues($fieldName);
		
		    $values = array_combine($values, array_map("self::translate", $values));
		
		    if (isset($overwriteMapping[$fieldName])) {
		        $values = $overwriteMapping[$fieldName] + $values;
		    }
		
		    return $values;
		
		}
		
		public function __construct($options = null)
		{
			if (isset($options['_groups'])) {
				$this->_groups = $options['_groups'];
				unset($options['_groups']);
			}
			
			parent::__construct($options);
		}

		public function validate($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if($_GET['id'] > 0)
			{
				$clientid = $_GET['id'];
			}
			else if($logininfo->clientid > 0)
			{
				$clientid = $logininfo->clientid;
			}
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();
			if(!$val->isstring($post['client_name']))
			{
				$this->error_message['client_name'] = $Tr->translate('enterclientname');
				$error = 1;
			}

			if(!$val->isstring($post['street1']))
			{
				$this->error_message['street1'] = $Tr->translate('enterstreetname');
				$error = 2;
			}
			if(!$val->isstring($post['postcode']))
			{
				$this->error_message['postcode'] = $Tr->translate('enterpostcode');
				$error = 4;
			}
			if(!$val->isstring($post['firstname']))
			{
				$this->error_message['firstname'] = $Tr->translate('enterfirstname');
				$error = 5;
			}
			if(!$val->isstring($post['lastname']))
			{
				$this->error_message['lastname'] = $Tr->translate('enterlastname');
				$error = 6;
			}
			if(!$val->email($post['emailid']))
			{
				$this->error_message['emailid'] = $Tr->translate('pleaseprovidevalidemail');
				$error = 7;
			}
			if(!is_numeric($post['maxcontact']))
			{
				$this->error_message['maxcontact'] = $Tr->translate('enternumericmax50');
				$error = 8;
			}
			if(!is_numeric($post['discharge_day_period']))
			{
				$this->error_message['discharge_day_period'] = $Tr->translate('enternumeric');
				$error = 8;
			}
			
			//ISPC-2417 Lore 29.08.2019 	    // Maria:: Migration ISPC to CISPC 08.08.2020
			//Demstepcare_upload - 10.09.2019 Ancuta
			if(!empty($_GET['id'])){
			    $previleges = new Modules();
			    $has195module = $previleges->checkModulePrivileges("195", $clientid);
			    if ($has195module){
			        if( $post['days_after_todo'] == 0)
			        {
			            $this->error_message['days_after_todo'] = $Tr->translate('daysaftertodo');
			            $error = 30;
			        }
			    }
			}

			
			if($post['maxcontact'] > 50)
			{
				$this->error_message['maxcontact'] = $Tr->translate('enternumericmax50');
				$error = 9;
			}

			if(!is_numeric($post['max_nurse_visits']))
			{
				$this->error_message['max_nurse_visits'] = $Tr->translate('enternumeric');
				$error = 10;
			}

			if(isset($_GET['id']))
			{
				$cust = Doctrine_Query::create()
					->select('*')
					->from('Client')
					->where("emailid ='" . addslashes(Pms_CommonData::aesEncrypt($post['emailid'])) . "' and id=" . $clientid);
				$custexec = $cust->execute();
				$custarr = $custexec->toArray();

				if(count($custarr) > 0)
				{
					if($custarr[0]['id'] != $_GET['id'])
					{
						$this->error_message['emailid'] = $Tr->translate("emailidalreadyexists");
						$error = 17;
					}
				}
			}
			//sepa_iban ,sepa_bic, sepa_ci
			if(strlen(trim($post['sepa_iban'])) > 0)
			{
				$iban_Validator = new Pms_SepaIbanValidator(array(
						'allow_non_sepa'=>false ,
						'iban'=>$post['sepa_iban']));
				
				$iban_is_valid = false;
				if ( !$iban_is_valid =  $iban_Validator->isValid() ){
					$this->error_message['sepa_iban'] = $Tr->translate('IBAN validation failed');
					$error = 1;
				}
				
				if ( $iban_is_valid && strlen(trim($post['sepa_bic'])) > 0 && ! $iban_Validator->bic_isValid($post['sepa_bic']) ){
					$this->error_message['sepa_bic'] = $Tr->translate('BIC validation failed');
					$error = 1;
				}
				
				//sepa_ci
				$sepa_ci_regex= "/^[a-zA-Z]{2,2}[0-9]{2,2}([A-Za-z0-9]|[\-|\.|]){3,3}([A-Za-z0-9]|[\-|\.]){1,28}$/";
				if (! preg_match( $sepa_ci_regex, trim($post['sepa_ci']))) {
					$this->error_message['sepa_ci'] = $Tr->translate('Creditor Identifier validation failed'). "<br>(ex: DE12XXX123456-xxx)";
					$error = 1;
				}
				
				
			}
			
			if(!is_numeric($post['contactform_default_visit_length']) || $post['contactform_default_visit_length'] < 1)
			{
				$this->error_message['contactform_default_visit_length'] = $Tr->translate('enternumeric');
				$error = 10;
			}
			
			//ISPC-2154
			if ( empty($post['epid_chars'])) {
			    $this->error_message['epid_chars'] = $Tr->translate('EPID Prefix cannot be empty');
			    $error = 10;
			} else {
			    //check for unique ipid
			    $cl_entity = new Client();
			    $epid_chars_owner = $cl_entity->getTable()->createQuery('cl')
			    ->select('id')
			    ->where('UPPER(epid_chars) = ? ')
			    ->andWhere('isdelete=0')
			    ->fetchOne(array(strtoupper($post['epid_chars'])), Doctrine_Core::HYDRATE_ARRAY);
			    
			    if ( ! empty($epid_chars_owner) && $epid_chars_owner['id'] != $post['clientid'] ) {
			        $this->error_message['epid_chars'] = $Tr->translate('EPID Prefix is allready in use');
			        $error = 10;
			    }
			    
			}
			//ISPC-2806 Dragos 28.01.2021
			if(!empty($_GET['id'])){
			    $previleges = new Modules();
			    $has249module = $previleges->checkModulePrivileges("249", $clientid);
			    if ($has249module) {
			    	if ($post['complaint']['status'] == 'enabled'
						&& (empty($post['complaint']['pharmacy_email']) || !$val->email($post['complaint']['pharmacy_email'])))
					{
						$this->getSubForm('complaint_settings')->getElement('pharmacy_email')->addError($Tr->translate('pleaseprovidevalidemail'));
						$error = 7;
					}
					if ($post['complaint']['status'] == 'enabled'
						&& (empty($post['complaint']['office_email']) || !$val->email($post['complaint']['office_email'])))
					{
						$this->getSubForm('complaint_settings')->getElement('office_email')->addError($Tr->translate('pleaseprovidevalidemail'));
						$error = 7;
					}
					if ($post['complaint']['status'] == 'enabled'
						&& (empty($post['complaint']['email_subject']) || !$val->isstring($post['complaint']['email_subject'])))
					{
						$this->getSubForm('complaint_settings')->getElement('email_subject')->addError($Tr->translate('E-mail subject required'));
						$error = 7;
					}
					if ($post['complaint']['status'] == 'enabled'
						&& (empty($post['complaint']['email_body']) || !$val->isstring($post['complaint']['email_body'])))
					{
						$this->getSubForm('complaint_settings')->getElement('email_body')->addError($Tr->translate('E-mail message required'));
						$error = 7;
					}
//
			    }
			}
				
			
			if($error == 0)
			{
				return true;
			}
			
			$this->error_message['message'] = $Tr->translate('error_message');
	
			return false;
		}

		public function copyclientvalidate($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();
			if($post['copyfromclient'] == 0)
			{
				$this->error_message['copyfromclient'] = $Tr->translate('selectfromclient');
				$error = 1;
			}
			if($post['copytoclient'] == 0)
			{
				$this->error_message['copytoclient'] = $Tr->translate('selecttoclient');
				$error = 1;
			}
			if(!is_array($post['copytable']))
			{
				$this->error_message['copytable'] = $Tr->translate('selecttabletocopy');
				$error = 1;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function CopyclientData($post)
		{
			$tablearray = array("1" => "Health Isurance", "2" => "Family Doctor", "3" => "Reffered By", "4" => "Triggers", "5" => "Symptomatology", "6" => "Course Shortcuts", "7" => "Discharge Location", "8" => "Discharge Method", "9" => "Navigation Menu", "10" => "Client Modules", "11" => "Extra Forms");

			if($post['copytable'][1] == 1)
			{
				$hinsu = Doctrine_Query::create()
					->select('*')
					->from('HealthInsurance')
					->where("clientid='" . $post['copyfromclient'] . "' and  isdelete=0");
				$hinsuexec = $hinsu->execute();
				$hinsuarray = $hinsuexec->toarray();
				if(count($hinsuarray) > 0)
				{

					foreach($hinsuarray as $key => $val)
					{
						$hinsu = new HealthInsurance();
						$hinsu->clientid = $post['copytoclient'];
						$hinsu->name = $val['name'];
						$hinsu->name2 = $val['name2'];
						$hinsu->street1 = $val['street1'];
						$hinsu->street2 = $val['street2'];
						$hinsu->zip = $val['zip'];
						$hinsu->city = $val['city'];
						$hinsu->phone = $val['phone'];
						$hinsu->phonefax = $val['phonefax'];
						$hinsu->kvnumber = $val['kvnumber'];
						$hinsu->iknumber = $val['iknumber'];
						$hinsu->comments = $val['comments'];
						$hinsu->valid_from = date("Y-m-d", time());
						$hinsu->save();
					}
				}
			}
			if($post['copytable'][2] == 2)
			{
				$fdoc = Doctrine_Query::create()
					->select('*')
					->from('FamilyDoctor')
					->where("clientid='" . $post['copyfromclient'] . "' and  isdelete=0");
				$fdocexec = $fdoc->execute();
				$fdocarray = $fdocexec->toarray();
				if(count($fdocarray) > 0)
				{
					foreach($fdocarray as $key => $val)
					{
						$fdoc = new FamilyDoctor();
						$fdoc->clientid = $post['copytoclient'];
						$fdoc->practice = $val['practice'];
						$fdoc->title = $val['title'];
						$fdoc->salutation = $val['salutation'];
						$fdoc->last_name = $val['last_name'];
						$fdoc->first_name = $val['first_name'];
						$fdoc->street1 = $val['street1'];
						$fdoc->zip = $val['zip'];
						$fdoc->city = $val['city'];
						$fdoc->phone_practice = $val['phone_practice'];
						$fdoc->phone_private = $val['phone_private'];
						$fdoc->fax = $val['fax'];
						$fdoc->save();
					}
				}
			}

			if($post['copytable'][3] == 3)
			{
				$pref = Doctrine_Query::create()
					->select('*')
					->from('PatientReferredBy')
					->where("clientid='" . $post['copyfromclient'] . "' and  isdelete=0");
				$prefexec = $pref->execute();
				$prefarray = $prefexec->toarray();
				if(count($prefarray) > 0)
				{
					foreach($prefarray as $key => $val)
					{
						$cust = new PatientReferredBy();
						$cust->referred_name = $val['referred_name'];
						$cust->clientid = $post['copytoclient'];
						$cust->save();
					}
				}
			}

			if($post['copytable'][4] == 4)
			{
				$ftrig = Doctrine_Query::create()
					->select('*')
					->from('FieldTrigger')
					->where("clientid='" . $post['copyfromclient'] . "'");
				$ftrigexec = $ftrig->execute();
				$ftrigarray = $ftrigexec->toarray();
				if(count($ftrigarray) > 0)
				{
					$qdel = Doctrine_Query::create()
						->update("FieldTrigger")
						->set("isdelete", "1")
						->where('clientid="' . $post['copytoclient'] . '"');
					$qdelexec = $qdel->execute();

					foreach($ftrigarray as $key => $val)
					{
						$trr = new FieldTrigger();
						$trr->fieldid = $val['fieldid'];
						$trr->clientid = $post['copytoclient'];
						$trr->triggerid = $val['triggerid'];
						$trr->formid = $val['formid'];
						$trr->event = $val['event'];
						$trr->operator = $val['operator'];
						$trr->operand = $val['operand'];
						$trr->inputs = $val['inputs'];
						$trr->isdelete = $val['isdelete'];
						$trr->save();
					}
				}
			}

			if($post['copytable'][5] == 5)
			{
				$symp = Doctrine_Query::create()
					->select('*')
					->from('SymptomatologyMaster')
					->where("clientid='" . $post['copyfromclient'] . "' and isdelete=0");
				$sympexec = $symp->execute();
				$symparray = $sympexec->toarray();
				if(count($symparray) > 0)
				{
					foreach($symparray as $key => $val)
					{
						$res = new SymptomatologyMaster();
						$res->sym_description = $val['sym_description'];
						$res->clientid = $post['copytoclient'];
						$res->min_alert = $val['min_alert'];
						$res->max_alert = $val['max_alert'];
						$res->alert_color = $val['alert_color'];
						$res->entry_date = $val['entry_date'];
						$res->input_value = $val['input_value'];
						$res->critical_value = $val['critical_value'];
						$res->save();
					}
				}
			}

			if($post['copytable'][6] == 6)
			{
				$cour = Doctrine_Query::create()
					->select('*')
					->from('Courseshortcuts')
					->where("clientid='" . $post['copyfromclient'] . "' and isdelete=0");
				$courexec = $cour->execute();
				$courarray = $courexec->toarray();
				if(count($courarray) > 0)
				{
					foreach($courarray as $key => $val)
					{
						$course = new Courseshortcuts();
						$course->clientid = $post['copytoclient'];
						$course->shortcut = $val['shortcut'];
						$course->isfilter = $val['isfilter'];
						$course->font_color = $val['font_color'];
						$course->isbold = $val['isbold'];
						$course->isitalic = $val['isitalic'];
						$course->isunderline = $val['isunderline'];
						$course->course_fullname = $val['course_fullname'];
						$course->save();
					}
				}
			}

			if($post['copytable'][7] == 7)
			{
				$disl = Doctrine_Query::create()
					->select('*')
					->from('DischargeLocation')
					->where("clientid='" . $post['copyfromclient'] . "' and isdelete=0");
				$dislexec = $disl->execute();
				$dislarray = $dislexec->toarray();
				if(count($dislarray) > 0)
				{
					foreach($dislarray as $key => $val)
					{
						$location = new DischargeLocation();
						$location->location = $val['location'];
						$location->clientid = $post['copytoclient'];
						$location->save();
					}
				}
			}

			if($post['copytable'][8] == 8)
			{
				$dism = Doctrine_Query::create()
					->select('*')
					->from('DischargeMethod')
					->where("clientid='" . $post['copyfromclient'] . "' and isdelete=0");
				$dismexec = $dism->execute();
				$dismarray = $dismexec->toarray();
				if(count($dismarray) > 0)
				{
					$qdel = Doctrine_Query::create()
						->update("DischargeMethod")
						->set("isdelete", "1")
						->where('clientid="' . $post['copytoclient'] . '"');
					$qdelexec = $qdel->execute();
					foreach($dismarray as $key => $val)
					{
						$location = new DischargeMethod();
						$location->abbr = $val['abbr'];
						$location->description = $val['description'];
						$location->clientid = $post['copytoclient'];
						$location->save();
					}
				}
			}

			if($post['copytable'][9] == 9)
			{
				$mcls = Doctrine_Query::create()
					->select('*')
					->from('MenuClient')
					->where("clientid='" . $post['copyfromclient'] . "'");
				$mclsexec = $mcls->execute();
				$mclsarray = $mclsexec->toarray();

				if(count($mclsarray) > 0)
				{
					foreach($mclsarray as $key => $val)
					{
						$mnc = new MenuClient();
						$mnc->menu_id = $val['menu_id'];
						$mnc->clientid = $post['copytoclient'];
						$mnc->save();
					}
				}
			}

			if($post['copytable'][10] == 10)
			{

				$q = Doctrine_Query::create()
					->select('*')
					->from('ClientModules')
					->where('clientid="' . $post['copyfromclient'] . '"');

				$clientpre = $q->execute();
				$clienpearray = $clientpre->toarray();
				if(count($clienpearray) > 0)
				{
					$qdel = Doctrine_Query::create()
						->delete("ClientModules")
						->where('clientid="' . $post['copytoclient'] . '"');
					$qdelexec = $qdel->execute();
					foreach($clienpearray as $key => $val)
					{
						$clientmodules = new ClientModules();
						$clientmodules->clientid = $post['copytoclient'];
						$clientmodules->moduleid = $val['moduleid'];
						$clientmodules->canaccess = $val['canaccess'];
						$clientmodules->save();
					}
				}
			}

			if($post['copytable'][11] == 11)
			{

				$q = Doctrine_Query::create()
					->select('*')
					->from('ExtraFormsClient')
					->where('clientid="' . $post['copyfromclient'] . '"');
				$clientpre = $q->execute();
				$clienpearray = $clientpre->toarray();
				if(count($clienpearray) > 0)
				{
					$qdel = Doctrine_Query::create()
						->delete("ExtraFormsClient")
						->where('clientid="' . $post['copytoclient'] . '"');
					$qdelexec = $qdel->execute();
					foreach($clienpearray as $key => $val)
					{
						$clientmodules = new ExtraFormsClient();
						$clientmodules->clientid = $post['copytoclient'];
						$clientmodules->formid = $val['formid'];
						$clientmodules->save();
					}
				}
			}

			if($post['copytable'][12] == 12)
			{

				$q = Doctrine_Query::create()
					->select('*')
					->from('Medication')
					->where('clientid="' . $post['copyfromclient'] . '"');

				$clientpre = $q->execute();
				$clienpearray = $clientpre->toarray();
				if(count($clienpearray) > 0)
				{
					$qdel = Doctrine_Query::create()
						->update("Medication")
						->set("isdelete", "1")
						->where('clientid="' . $post['copytoclient'] . '"');
					$qdelexec = $qdel->execute();
					foreach($clienpearray as $key => $val)
					{
						$clientmodules = new Medication();
						$clientmodules->clientid = $post['copytoclient'];
						$clientmodules->name = $val['name'];
						$clientmodules->pzn = $val['pzn'];
						$clientmodules->description = $val['description'];
						$clientmodules->package_size = $val['package_size'];
						$clientmodules->amount_unit = $val['amount_unit'];
						$clientmodules->price = $val['price'];
						$clientmodules->extra = $val['extra'];
						$clientmodules->manufacturer = $val['manufacturer'];
						$clientmodules->package_amount = $val['package_amount'];
						$clientmodules->isdelete = $val['isdelete'];
						$clientmodules->save();
					}
				}
			}

			if($post['copytable'][13] == 13)
			{

				$q = Doctrine_Query::create()
					->select('*')
					->from('TabMenuClient')
					->where('clientid="' . $post['copyfromclient'] . '"');

				$clientpre = $q->execute();
				$clienpearray = $clientpre->toarray();
				if(count($clienpearray) > 0)
				{
					$qdel = Doctrine_Query::create()
						->delete("TabMenuClient")
						->where('clientid="' . $post['copytoclient'] . '"');
					$qdelexec = $qdel->execute();
					foreach($clienpearray as $key => $val)
					{
						$clientmodules = new TabMenuClient();
						$clientmodules->clientid = $post['copytoclient'];
						$clientmodules->menu_id = $val['menu_id'];
						$clientmodules->save();
					}
				}
			}
			// Maria:: Migration CISPC to ISPC 22.07.2020
            if($post['copytable'][14] == 14) {
                //IM-144 Possibility to copy client's contactforms to another client
                // talkcontent settings
               // $talkcontentToCopy = Client::getClientconfig($post['copyfromclient'] , 'configtalkcontent');
                $talkcontentToCopy = ClientConfig::getConfig($post['copyfromclient'] , 'configtalkcontent');

                $q1 = Doctrine_Query::create()
                    ->select('*')
                    ->from('FormTypes')
                    ->where('clientid="' . $post['copyfromclient'] . '"')
                    ->andWhere('isdelete=0');

                $clientpre_formtypes = $q1->execute();
                $clienpre_formtypes_array = $clientpre_formtypes->toarray();
                $mapping_formtypes_array = [];

                foreach($clienpre_formtypes_array as $key => $val)
                {
                    $formTypes = new FormTypes();
                    $formTypes->clientid = $post['copytoclient'];
                    $formTypes->action = $val['action'];
                    $formTypes->name = $val['name'];
                    $formTypes->isdelete = $val['isdelete'];
                    $formTypes->calendar_color = $val['calendar_color'];
                    $formTypes->calendar_text_color = $val['calendar_text_color'];
                    $formTypes->save();
                    $mapping_formtypes_array[$val['id']] = $formTypes->id;

                }
                //`form_blocks_order`
                $qord = $q2 = Doctrine_Query::create()
                    ->select('*')
                    ->from('FormBlocksOrder')
                    ->where('client="' . $post['copyfromclient'] . '"');
                $clientpre_order = $qord->execute();
                $clientpre_order_array = $clientpre_order->toarray();
                foreach ($clientpre_order_array as $order_unit){
                    $blocksOrder = new FormBlocksOrder();
                    $blocksOrder->client = $post['copytoclient'];
                    $blocksOrder->box_order = $order_unit['box_order'];
                    $blocksOrder->form_type = $mapping_formtypes_array[$order_unit['form_type']];
                    $blocksOrder->save();

		}


                //FormTypePermissions
                $q2 = Doctrine_Query::create()
                    ->select('*')
                    ->from('FormTypePermissions')
                    ->where('clientid="' . $post['copyfromclient'] . '"');

                $clientpre_formtypeperms = $q2->execute();
                $clienpre_formtypeperms_array = $clientpre_formtypeperms->toarray();
                foreach($clienpre_formtypeperms_array as $key => $val)
                {
                    $formTypePermissions = new FormTypePermissions();
                    $formTypePermissions->clientid = $post['copytoclient'];
                    $formTypePermissions->groupid = $val['groupid'];
                    $formTypePermissions->type = $mapping_formtypes_array[$val['type']];
                    $formTypePermissions->value = $val['value'];
                    $formTypePermissions->save();

                }
                 //FormBlocksOptions
                $qopts = Doctrine_Query::create()
                    ->select('*')
                    ->from('FormBlocksOptions')
                    ->where('clientid="' . $post['copyfromclient'] . '"');

                $clientpre_formblockopts = $qopts->execute();
                $clienpre_formblockopts_array = $clientpre_formblockopts->toarray();
                foreach($clienpre_formblockopts_array as $key => $val){
                    $formBlocksOptions = new FormBlocksOptions();
                    $formBlocksOptions->clientid = $post['copytoclient'];
                    $formBlocksOptions->form_type =  $mapping_formtypes_array[$val['form_type']];
                    $formBlocksOptions->open = $val['open'];
                    $formBlocksOptions->block = $val['block'];
                    $formBlocksOptions->save();
                }

                 //FormsBlocks2Type
                $q3 = Doctrine_Query::create()
                    ->select('*')
                    ->from('FormBlocks2Type')
                    ->where('clientid="' . $post['copyfromclient'] . '"');

                $clientpre_formblocks2type = $q3->execute();
                $clienpre_formblocks2type_array = $clientpre_formblocks2type->toarray();
                foreach($clienpre_formblocks2type_array as $key => $val)
                {
                    //clientid
                    //form_type
                    //form_block
                    $formblock2type = new FormBlocks2Type();
                    $formblock2type->clientid = $post['copytoclient'];
                    $formblock2type->form_type = $mapping_formtypes_array[$val['form_type']];
                    $formblock2type->form_block = $val['form_block'];
                    $formblock2type->save();

                }
                //talkcontent settings
                $talkcontentCopied = ClientConfig::getConfig($post['copytoclient'] , 'configtalkcontent');
                if(!is_array($talkcontentCopied)){
                    $talkcontentCopied = [];
                }
                foreach($talkcontentToCopy as $contentItem){
                    $visible = [];
                    $contentItemVisible = $contentItem['visible'];
                    foreach($contentItemVisible as $visItem){
                        $visible[] =  $mapping_formtypes_array[intval($visItem)];
                    }
                    $contentItem['visible'] = $visible;

                    if(count($visible)>0){
                        $talkcontentCopied[] = $contentItem;
                    }
                }
                ClientConfig::saveConfig($post['copytoclient'], 'configtalkcontent', $talkcontentCopied);

                //FormBlockPermissions
                //saved permissions
                $qfto = $qfb = Doctrine_Query::create()
                    ->select('*')
                    ->from('FormBlockPermissions')
                    ->where('clientid="' . $post['copytoclient'] . '"');
                $clientto_formblockperms = $qfto->execute();
                $clientto_formblockperms_array = $clientto_formblockperms->toArray();
                $saved_block_permissions_array = [];
                foreach($clientto_formblockperms_array as $key => $val)
                {
                    $saved_block_permissions_array[$post['copytoclient']][$val['groupid']][$val['block']] = $val['id'];
                }

                $qfb = Doctrine_Query::create()
                    ->select('*')
                    ->from('FormBlockPermissions')
                    ->where('clientid="' . $post['copyfromclient'] . '"');

                $clientpre_formblockperms = $qfb->execute();
                $clienpre_formblockperms_array = $clientpre_formblockperms->toArray();

                foreach($clienpre_formblockperms_array as $key => $val)
                {
                    $formBlockPermissions = new FormBlockPermissions();
                    $formBlockPermissions->clientid = $post['copytoclient'];
                    $formBlockPermissions->groupid = $val['groupid'];
                    $formBlockPermissions->block = $val['block'];
                    $formBlockPermissions->value = $val['value'];
                    //if permissions exist for client, group and block, rewrite value, otherwise write new entry - elena
                    if(isset($saved_block_permissions_array[$post['copytoclient']][$val['groupid']][$val['block']])){
                        $formBlockPermissions->id = $saved_block_permissions_array[$post['copytoclient']][$val['groupid']][$val['block']];
                        $formBlockPermissions->replace();
                    }else{
                        $formBlockPermissions->save();
                    }


                }




            }
		}

		public function InsertData($post)
		{
			$cust = new Client();
			$cust->client_name = Pms_CommonData::aesEncrypt($post['client_name']);
			$cust->epid_chars = $post['epid_chars'];
			$cust->epid_start_no = $post['epid_start_no'];
			$cust->street1 = Pms_CommonData::aesEncrypt($post['street1']);
			$cust->street2 = Pms_CommonData::aesEncrypt($post['street2']);
			$cust->city = Pms_CommonData::aesEncrypt($post['city']);
			$cust->district = $post['district'];
			$cust->country = $post['country'];
			$cust->postcode = Pms_CommonData::aesEncrypt($post['postcode']);
			$cust->firstname = Pms_CommonData::aesEncrypt($post['firstname']);
			$cust->lastname = Pms_CommonData::aesEncrypt($post['lastname']);
			$cust->emailid = Pms_CommonData::aesEncrypt($post['emailid']);
			$cust->phone = Pms_CommonData::aesEncrypt($post['phone']);
			$cust->fax = Pms_CommonData::aesEncrypt($post['fax']);
			$cust->team_name = $post['team_name'];
			$cust->institutskennzeichen = Pms_CommonData::aesEncrypt($post['institutskennzeichen']);
			$cust->betriebsstattennummer = Pms_CommonData::aesEncrypt($post['betriebsstattennummer']);
			$cust->comment = Pms_CommonData::aesEncrypt($post['comment']);
			$cust->greetings = htmlspecialchars($post['greetings']);
			$cust->fileupoadpass = Pms_CommonData::aesEncrypt($this->str_rand(15, 'alphanum'));
			$cust->preregion = $post['preregion'];
			$cust->userlimit = $post['userlimit'];
			$cust->maxcontact = $post['maxcontact'];
			$cust->discharge_day_period = $post['discharge_day_period'];
			$cust->symptomatology_scale = $post['symptomatology_scale'];
			$cust->health_insurance_client = $post['health_insurance_client'];

			$cust->automatically_assign_users = $post['automatically_assign_users']; //ISPC-871

			$cust->dgp_user = Pms_CommonData::aesEncrypt($post['dgp_user']);


			$cust->lbg_sapv_provider = Pms_CommonData::aesEncrypt($post['lbg_sapv_provider']);
			$cust->lbg_street = Pms_CommonData::aesEncrypt($post['lbg_street']);
			$cust->lbg_postcode = Pms_CommonData::aesEncrypt($post['lbg_postcode']);
			$cust->lbg_city = Pms_CommonData::aesEncrypt($post['lbg_city']);
			$cust->lbg_institutskennzeichen = Pms_CommonData::aesEncrypt($post['lbg_institutskennzeichen']);

			$cust->invoice_number_type = $post['invoice_number_type'];
			$cust->invoice_due_days = $post['invoice_due_days'];

			if(!empty($post['dgp_pass']))
			{
				$cust->dgp_pass = Pms_CommonData::aesEncrypt($post['dgp_pass']);
			}

			if($post['inactivetime'] > 0)
			{
				$cust->inactivetime = $post['inactivetime'];
			}
			else
			{
				$cust->inactivetime = '15';
			}
			$cust->maintainance = $post['maintainance'];
			//Invoice Receipient
			$cust->recipient = $post['recipient'];
			$cust->max_nurse_visits = $post['max_nurse_visits'];
			$cust->emergencynr_a = $post['emergencynr_a'];
			$cust->emergencynr_b = $post['emergencynr_b'];
			$cust->billing_method = '';
			$cust->membership_billing_method = $post['membership_billing_method'];
			$cust->tagesplanung_standby_patients = $post['tagesplanung_standby_patients'];// ISPC-1170

			$cust->receipt_print_style = $post['receipt_print_style'];// ISPC-1458
			$cust->mandate_reference = $post['mandate_reference'];// ISPC - 1485
			$cust->new_medication_fields = $post['new_medication_fields'];// ISPC - 1624
			
			$cust->tagesplanung_default_visit_time = (int)$post['tagesplanung_default_visit_time']; //ispc-1533
			$cust->tagesplanung_only_user_with_shifts = (int)$post['tagesplanung_only_user_with_shifts']; //ispc-1533
			
			//ispc-1842
			if(strlen(trim($post['sepa_iban'])) > 0)
			{
				$iban_Validator = new Pms_SepaIbanValidator(array(
						'allow_non_sepa'=>false ,
						'iban'=>$post['sepa_iban']));
			
				$iban_is_valid = false;
				if ( $iban_is_valid =  $iban_Validator->isValid() ){
					$post['sepa_iban'] = $iban_Validator->iban_to_human_format();
				}
			
				if ( $iban_is_valid && strlen(trim($post['sepa_bic'])) > 0 && $iban_Validator->bic_isValid($post['sepa_bic']) ){
					$post['sepa_bic'] = $iban_Validator->bic_to_machine_format($post['sepa_bic']);
				}
			
			}
			$cust->sepa_iban = Pms_CommonData::aesEncrypt(trim($post['sepa_iban']));
			$cust->sepa_bic = Pms_CommonData::aesEncrypt(trim($post['sepa_bic']));
			$cust->sepa_ci = Pms_CommonData::aesEncrypt(trim($post['sepa_ci']));
			
			$cust->route_calculation = $post['route_calculation'];
			$cust->contactform_default_visit_length = $post['contactform_default_visit_length'];
			
			//ispc-1886
			if(strlen($post['dgp_transfer_date']) > 0 ){
				$cust->dgp_transfer_date = date("Y-m-d H:i:s",strtotime($post['dgp_transfer_date']));
			} else{
				$cust->dgp_transfer_date = "0000-00-00 00:00:00";
			}
			
			//ISPC-2161
			$cust->teammeeting_settings = $post['teammeeting_settings'];
			//ISPC-2271
			$cust->notfall_messages_settings = $post['notfall_messages_settings'];
			
			// ISPC-2272 (07.11.2018)
			$cust->company_number = Pms_CommonData::aesEncrypt(trim($post['company_number']));
			$cust->cost_center = Pms_CommonData::aesEncrypt(trim($post['cost_center']));
			//-- 
			
			//ISPC-2095
			$cust->tourenplanung_settings = $post['tourenplanung_settings'];
			
			//ISPC-2327 23.01.2019 Ancuta
			$cust->working_schedule = Pms_CommonData::aesEncrypt($post['working_schedule']);
			//--


			//ISPC-2311
			$cust->patient_course_settings = $post['patient_course_settings'];

			//ISPC-2417 Lore 29.08.2019
			$cust->days_after_todo = $post['days_after_todo'];
			
			
			// ISPC-2331 05.03.2019
				
			$cust->rlp_past_revenue = Pms_CommonData::aesEncrypt(trim($post['rlp_past_revenue']));
				
			if (!empty($post['sap_annually_hidden']) && !empty($post['sap_annually'])){
			    $date = DateTime::createFromFormat("Y-m-d",   $post['sap_annually_hidden']);
			    $cust->rlp_books_end_day = $date->format('j');
			    $cust->rlp_books_end_month = $date->format('n');
			}
			//--
			// Maria:: Migration ISPC to CISPC 08.08.2020
			//ISPC-2452 Ancuta 24.09.2019
			$cust->rlp_hi_account_number =Pms_CommonData::aesEncrypt(trim($post['rlp_hi_account_number'])); 
			$cust->rlp_pv_account_number =Pms_CommonData::aesEncrypt(trim($post['rlp_pv_account_number']));
			$cust->rlp_terms_of_payment =Pms_CommonData::aesEncrypt(trim($post['rlp_terms_of_payment'])); 
			//--
			
			//ISPC-2171 Ancuta 08.01.2020
			$cust->rlp_document_header_txt =Pms_CommonData::aesEncrypt(trim($post['rlp_document_header_txt']));
			// --
			
			//ISPC-2636 Lore 29.07.2020
			$cust->client_medi_sort = $post['client_medi_sort'];
			$cust->user_overwrite_medi_sort_option = $post['user_overwrite_medi_sort_option'];
			//.
			
			//ISPC-2769 Lore 06.01.2021
			$cust->show_medi_times_when_given = $post['show_medi_times_when_given'];
			
			//ISPC-2459 Ancuta 04.08.2020
			$cust->movement_start_number = $post['movement_start_number'];
			//-- 
			
			//ISPC-2827 Ancuta 26.03.2021
			$cust->efa_client = $post['efa_client'];
			//-- 
			//ISPC-2864 Ancuta 20.04.2021
			$cust->efa_problem_extension = $post['efa_problem_extension'];
			//--

			$cust->save();
			$id = $cust->id;

			$res = new DiagnosisType();
			$res->clientid = $id;
			$res->abbrevation = 'AD';
			$res->save();

			$res = new DiagnosisType();
			$res->clientid = $id;
			$res->abbrevation = 'ND';
			$res->save();

			$res = new DiagnosisType();
			$res->clientid = $id;
			$res->abbrevation = 'HD';
			$res->save();

			$res = new DiagnosisType();
			$res->clientid = $id;
			$res->abbrevation = 'DD';
			$res->save();


			$pc = new Courseshortcuts();
			$pcarr = $pc->getCourseData();

			if(count($pcarr) > 0)
			{
				for($i = 0; $i < count($pcarr); $i++)
				{
					$course = new Courseshortcuts();
					$course->clientid = $id;
					$course->shortcut = $pcarr[$i]['shortcut'];
					$course->course_fullname = $pcarr[$i]['course_fullname'];
					$course->save();
				}
			}
		}

		public function UpdateData($post)
		{
		    //$_GET['id'] left here for posterity... changed like this so you can't edit other id
		    
		    $id = isset($post['clientid']) ? $post['clientid'] : $_GET['id'];
		    
			$cust = Doctrine::getTable('Client')->find($id);
			$cust->client_name = Pms_CommonData::aesEncrypt($post['client_name']);

			$cust->street1 = Pms_CommonData::aesEncrypt($post['street1']);
			$cust->street2 = Pms_CommonData::aesEncrypt($post['street2']);
			$cust->city = Pms_CommonData::aesEncrypt($post['city']);
			$cust->district = $post['district'];
			$cust->country = $post['country'];
			$cust->postcode = Pms_CommonData::aesEncrypt($post['postcode']);
			$cust->firstname = Pms_CommonData::aesEncrypt($post['firstname']);
			$cust->lastname = Pms_CommonData::aesEncrypt($post['lastname']);
			$cust->emailid = Pms_CommonData::aesEncrypt($post['emailid']);
			$cust->preregion = $post['preregion'];
			$cust->phone = Pms_CommonData::aesEncrypt($post['phone']);
			$cust->fax = Pms_CommonData::aesEncrypt($post['fax']);
			$cust->team_name = $post['team_name'];
			$cust->institutskennzeichen = Pms_CommonData::aesEncrypt($post['institutskennzeichen']);
			$cust->betriebsstattennummer = Pms_CommonData::aesEncrypt($post['betriebsstattennummer']);
			$cust->comment = Pms_CommonData::aesEncrypt($post['comment']);
			$cust->greetings = htmlspecialchars($post['greetings']);

			$cust->symptomatology_scale = $post['symptomatology_scale'];
			$cust->health_insurance_client = $post['health_insurance_client'];

			$cust->automatically_assign_users = $post['automatically_assign_users']; // ISPC-871

			$cust->userlimit = $post['userlimit'];
			$cust->maxcontact = $post['maxcontact'];
			$cust->discharge_day_period = $post['discharge_day_period'];

			$cust->dgp_user = Pms_CommonData::aesEncrypt($post['dgp_user']);

			$cust->lbg_sapv_provider = Pms_CommonData::aesEncrypt($post['lbg_sapv_provider']);
			$cust->lbg_street = Pms_CommonData::aesEncrypt($post['lbg_street']);
			$cust->lbg_postcode = Pms_CommonData::aesEncrypt($post['lbg_postcode']);
			$cust->lbg_city = Pms_CommonData::aesEncrypt($post['lbg_city']);
			$cust->lbg_institutskennzeichen = Pms_CommonData::aesEncrypt($post['lbg_institutskennzeichen']);

			$cust->invoice_number_type = $post['invoice_number_type'];
			$cust->invoice_due_days = $post['invoice_due_days'];

			if(!empty($post['dgp_pass']))
			{
				$cust->dgp_pass = Pms_CommonData::aesEncrypt($post['dgp_pass']);
			}

			if($post['inactivetime'] > 0)
			{
				$cust->inactivetime = $post['inactivetime'];
			}
			else
			{
				$cust->inactivetime = '15';
			}

			$cust->maintainance = $post['maintainance'];
			$cust->max_nurse_visits = $post['max_nurse_visits'];
			$cust->emergencynr_a = $post['emergencynr_a'];
			$cust->emergencynr_b = $post['emergencynr_b'];
			$cust->billing_method = $post['billing_method'];
			$cust->membership_billing_method = $post['membership_billing_method'];
			$cust->tagesplanung_standby_patients = $post['tagesplanung_standby_patients'];// ISPC-1170
			$cust->receipt_print_style = $post['receipt_print_style'];// ISPC-1458
			if($post['ppun_allowed'])
			{
				$cust->ppun_start = $post['ppun_start'];
			}
			if($post['hi_debitornumber_allowed'])
			{
				$cust->hi_debitor_start = $post['hi_debitor_start'];
			}

			//invoice recipient
			$cust->recipient = $post['recipient'];
			$cust->mandate_reference = $post['mandate_reference'];// ISPC - 1485
			$cust->new_medication_fields = $post['new_medication_fields'];// ISPC - 1624
			$cust->tagesplanung_default_visit_time = (int)$post['tagesplanung_default_visit_time']; //ispc-1533
			$cust->tagesplanung_only_user_with_shifts = (int)$post['tagesplanung_only_user_with_shifts']; //ispc-1533
			
			//ispc-1842
			if(strlen(trim($post['sepa_iban'])) > 0)
			{
				$iban_Validator = new Pms_SepaIbanValidator(array(
						'allow_non_sepa'=>false ,
						'iban'=>$post['sepa_iban']));

				$iban_is_valid = false;
				if ( $iban_is_valid =  $iban_Validator->isValid() ){
					$post['sepa_iban'] = $iban_Validator->iban_to_human_format();
				}
				
				if ( $iban_is_valid && strlen(trim($post['sepa_bic'])) > 0 && $iban_Validator->bic_isValid($post['sepa_bic']) ){
					$post['sepa_bic'] = $iban_Validator->bic_to_machine_format($post['sepa_bic']);
				}
				
			}
			$cust->sepa_iban = Pms_CommonData::aesEncrypt(trim($post['sepa_iban'])); 
			$cust->sepa_bic = Pms_CommonData::aesEncrypt(trim($post['sepa_bic'])); 
			$cust->sepa_ci = Pms_CommonData::aesEncrypt(trim($post['sepa_ci'])); 
					
			$cust->route_calculation = $post['route_calculation'];
			$cust->contactform_default_visit_length = $post['contactform_default_visit_length'];
			
			//ispc-1886
			if(strlen($post['dgp_transfer_date']) > 0 ){
				$cust->dgp_transfer_date = date("Y-m-d H:i:s",strtotime($post['dgp_transfer_date']));
			} else{
				$cust->dgp_transfer_date = "0000-00-00 00:00:00";
			}
				
			//ISPC-2161
			$cust->teammeeting_settings = $post['teammeeting_settings'];
			
			//ISPC-2271
			$cust->notfall_messages_settings = $post['notfall_messages_settings'];
			

			// ISPC-2272 (07.11.2018)
			$cust->company_number = Pms_CommonData::aesEncrypt(trim($post['company_number']));
			$cust->cost_center = Pms_CommonData::aesEncrypt(trim($post['cost_center']));
			//--
			
			//ISPC-2095
			$cust->tourenplanung_settings = $post['tourenplanung_settings'];
			
			//ISPC-2327 23.01.2019 Ancuta
			$cust->working_schedule = Pms_CommonData::aesEncrypt($post['working_schedule']);
			//--
					
			//ISPC-2311
			$cust->patient_course_settings = $post['patient_course_settings'];
			
			//ISPC-2163
			$cust->activate_shortcut_v_settings = $post['activate_shortcut_settings'];
			
			//ISPC-2417 Lore 29.08.2019
			$cust->days_after_todo = $post['days_after_todo'];
			
			
			// ISPC-2331 05.03.2019
			
			$cust->rlp_past_revenue = Pms_CommonData::aesEncrypt(trim($post['rlp_past_revenue']));
			
			if (!empty($post['sap_annually_hidden']) && !empty($post['sap_annually'])){
    			$date = DateTime::createFromFormat("Y-m-d",   $post['sap_annually_hidden']);
	       		$cust->rlp_books_end_day = $date->format('j');
    			$cust->rlp_books_end_month = $date->format('n');
			}  
			//--
			
			
			//ISPC-2452 Ancuta 24.09.2019
			$cust->rlp_hi_account_number =Pms_CommonData::aesEncrypt(trim($post['rlp_hi_account_number'])); 
			$cust->rlp_pv_account_number =Pms_CommonData::aesEncrypt(trim($post['rlp_pv_account_number']));
			$cust->rlp_terms_of_payment =Pms_CommonData::aesEncrypt(trim($post['rlp_terms_of_payment'])); 
			//--
	
			//ISPC-2171 Lore 15.11.2019
			$cust->hospiz_hi_cont =Pms_CommonData::aesEncrypt(trim($post['hospiz_hi_cont']));
			$cust->hospiz_pv_cont =Pms_CommonData::aesEncrypt(trim($post['hospiz_pv_cont']));
			$cust->hospiz_const_center =Pms_CommonData::aesEncrypt(trim($post['hospiz_const_center'])); 
			//--

			
			//ISPC-2171 Ancuta 08.01.2020
			$cust->rlp_document_header_txt =Pms_CommonData::aesEncrypt(trim($post['rlp_document_header_txt']));
			// --
			
			//ISPC-2636 Lore 29.07.2020
			$cust->client_medi_sort = $post['client_medi_sort'];
			$cust->user_overwrite_medi_sort_option = $post['user_overwrite_medi_sort_option'];
			//.
			
			//ISPC-2769 Lore 06.01.2021
			$cust->show_medi_times_when_given = $post['show_medi_times_when_given'];
			
			//ISPC-2459 Ancuta 04.08.2020 
			$cust->movement_start_number = $post['movement_start_number'];
			//-- 
			
			//TODO-3365 Carmen 21.08.2020
			$cust->pharmaindex_settings = $post['pharmaindex_settings'];
			//--
			
			
			//ISPC-2827 Ancuta 26.03.2021
			$cust->efa_client = $post['efa_client'];
			//--
			//ISPC-2864 Ancuta 20.04.2021
			$cust->efa_problem_extension = $post['efa_problem_extension'];
			//--
 
			$cust->save();
		}

		private function str_rand($length = 8, $seeds = 'alphanum')
		{
			// Possible seeds
			$seedings['alpha'] = 'abcdefghijklmnopqrstuvwqyz';
			$seedings['numeric'] = '0123456789';
			$seedings['alphanum'] = 'abcdefghijklmnopqrstuvwqyz0123456789';
			$seedings['hexidec'] = '0123456789abcdef';

			// Choose seed
			if(isset($seedings[$seeds]))
			{
				$seeds = $seedings[$seeds];
			}

			// Seed generator
			list($usec, $sec) = explode(' ', microtime());
			$seed = (float) $sec + ((float) $usec * 100000);
			mt_srand($seed);

			// Generate
			$str = '';
			$seeds_count = strlen($seeds);

			for($i = 0; $length > $i; $i++)
			{
				$str .= $seeds{mt_rand(0, $seeds_count - 1)};
			}

			return $str;
		}

		
		public function UpdateSMTPData( $clientid = 0  , $post = array())
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
				
			$result = true;
			$old_smtp_password =  false;
			
			//invalidate a single previous custom smtp setting of this client
			$smtp_setings = Doctrine::getTable('ClientSMTPSettings')->findOneByClientidAndIsdelete ($clientid , 0);
			if ($smtp_setings instanceof ClientSMTPSettings) {
				
				$old_smtp_password = $smtp_setings->smtp_password;
				$smtp_setings->delete();
			}
			
			//encrypt multiple data in one single sql query
			$ecrypted_post = Pms_CommonData::aesEncryptMultiple($post);
			
			
			if ($post['password_changed'] =="0" && $old_smtp_password !== false) {
				$ecrypted_post['smtp_password'] = $old_smtp_password; 
			}
			
			
			if ($post['use_defaults'] != 'YES') {
				//insert a new setting
				$c_smtp_s = new ClientSMTPSettings();
				
				$c_smtp_s->clientid = $clientid;
				
				$c_smtp_s->sender_name = $ecrypted_post['sender_name']; //Pms_CommonData::aesEncrypt($post['sender_name']);
				$c_smtp_s->sender_email = $ecrypted_post['sender_email']; //Pms_CommonData::aesEncrypt($post['sender_email']);
				
				$c_smtp_s->smtp_server = $post['smtp_server'];
				$c_smtp_s->smtp_port = $post['smtp_port'] != "" ? $post['smtp_port'] : null;
				
				$c_smtp_s->smtp_username = $ecrypted_post['smtp_username']; //Pms_CommonData::aesEncrypt($post['smtp_username']);
				$c_smtp_s->smtp_password = $ecrypted_post['smtp_password']; //Pms_CommonData::aesEncrypt($post['smtp_password']);
				
				$c_smtp_s->ssl_require = ($post['ssl_require'] == "YES") ? "YES": "NO"; //type enum
				$c_smtp_s->ssl_port = $post['ssl_port'] != "" ? $post['ssl_port'] : null;
				$c_smtp_s->tls_require = ($post['tls_require'] == "YES") ? "YES": "NO"; //type enum
				$c_smtp_s->tls_port = $post['tls_port'] != "" ? $post['tls_port'] : null;
				
				$c_smtp_s->save();

				//TODO-3993 Dragos ISPC Exchange email issue

				//test smpt settings (tls ????)
				$config = $c_smtp_s->get_mail_transport_cfg($clientid);

				$validation = $c_smtp_s->validSMTP($config);
				$result = $validation;

				// -- //
				
			} 
			
			return $result;
			
		}
		
	public function create_form_teammeeting_settings( $options = array(), $elementsBelongTo = null )
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => ''));
	    $subform->setLegend($this->translate('Teammeeting client settings'));
	    $subform->setAttrib("class", "label_same_size");
	     
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
	    $teammeeting_settings_available = Doctrine_Core::getTable('Client')->getColumnDefinition('teammeeting_settings');
	    

	    foreach ($teammeeting_settings_available['values'] as $row) {
	        
	        $subform->addElement('radio',  $row, array(
	            //'isArray'      => true,
                'multiOptions' => array('no' => 'Nein', 'yes' => 'Ja'),
                'value'        => $options[$row],
                'label'        => $this->translate("teammeeting_settings_{$row}"),
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('NotEmpty'),
                'decorators' => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style' => "width:100px; display:block")),
                    array('Label', array('tag' => 'td' , 'style' => 'padding:10px; width:180px; display:block')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
                ),
                'separator' => PHP_EOL,
                
                'belongsTo' => 'teammeeting_settings',
                
                'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('tr')).show()} else {\$('.show_hide', \$(this).parents('tr')).hide()}",
            ));
	        
	        $display =  $options[$row] == 'yes' ? "" : "display:none";
            $subform->addElement('note',  "teammeeting_settings_{$row}_explain", array(
                'value' => $this->translate("teammeeting_settings_{$row}_explain"),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' =>'show_hide', 'style' => $display)),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
                ),
                'separator' => PHP_EOL
            ));
            
	    }
	    
	    
	    return $this->filter_by_block_name($subform , __FUNCTION__);
	}
	
	public function create_form_notfall_messages_settings( $options = array(), $elementsBelongTo = null )
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => ''));
		$subform->setLegend($this->translate('Notfall messages client settings'));
		$subform->setAttrib("class", "label_same_size");
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		$subform->addElement('multiCheckbox', 'notfall_messages_settings', array(
							'label'      => null,
	                        'required'   => false,
	                        'multiOptions' => $this->_groups,
	                        'value' => $options,
	                        'separator'  => '&nbsp;',
							'decorators' => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style' => "width:100%; display:block")),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
							),
						'style' => 'margin-right: 5px; display: block; float: left; margin-top: -2px;',
						'label_style' => 'display: block; line-height: 18px; float: left; margin-right: 5px;width: 300px;'
					));
		
		
		return $this->filter_by_block_name($subform , __FUNCTION__);
	}

	//ISPC-2417 Lore 29.08.2019 	    // Maria:: Migration ISPC to CISPC 08.08.2020
	public function create_form_days_after_todo( $options = array(), $elementsBelongTo = null )
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => ''));
	    $subform->setLegend($this->translate('TODO reminder'));
	    $subform->setAttrib("class", "label_same_size");
	    
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
	    $subform->addElement('text', 'days_after_todo', array(
	        'label'      => $this->translate('Days after TODO '),              //null 
	        'required'   => false,
	        'value'      => $options, 
	        'maxlength'  => '2',
	        'data-inputmask'   => "'mask': '9', 'repeat': 5, 'greedy': false",
	        'pattern'    => "[0-9]*",
	        'validators'   => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first' ,'class' => 'settings_row_ed_master')),
	            
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
	        ),
      	    ));
	    

	    
	    return $this->filter_by_block_name($subform , __FUNCTION__);
	}
		
	
	
	public function create_form_tourenplanung_settings( $options = array(), $elementsBelongTo = 'tourenplanung_settings' )
	{
	    
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_tourenplanung_settings");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => ''));
	    $subform->setLegend('Tourenplanung client settings');
	    $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    $subform->addElement('select', 'workweek_start', array(
	        'label'      => 'Tourenplanung work week',
	        'required'   => false,
	        'multiOptions' => $this->getColumnMapping('tourenplanung_workweek'),
	        'value' => isset($options['workweek_start']) ? $options['workweek_start'] : null,
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first' ,'class' => 'settings_row_ed_master')),
	            	      
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
	        ),	        
	    ));
// 	    $subform->addElement('select', 'workweek_end', array(
// 	        'label'      => 'Tourenplanung work week',
// 	        'required'   => false,
// 	        'multiOptions' => $this->getColumnMapping('tourenplanung_workweek'),
// 	        'value' => isset($options['workweek_end']) ? $options['workweek_end'] : null,
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array('Errors'),
// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),	    
// 	        ),
// 	    ));
	    $subform->addElement('note', 'workweek_end_description', array(
	        'label'      => null,
	        'required'   => false,
	         
	        'value' => $this->translate('Here you can specify which days of the week are relevant for tour planning and which day of the week you should start with.'),
	         
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	        ),
	    ));
	    
	    
	
	    $subform->addElement('select', 'workhours_start', array(
	        'label'      => 'Tourenplanung work hours',
	        'required'   => false,
	        'multiOptions' => $this->getColumnMapping('tourenplanung_workhours'),
	        'value' => isset($options['workhours_start']) ? $options['workhours_start'] : null,
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
	        ),
	    ));
	    $subform->addElement('select', 'workhours_end', array(
	        'label'      => null,
	        'required'   => false,
	        'multiOptions' => $this->getColumnMapping('tourenplanung_workhours'),
	        'value' => isset($options['workhours_end']) ? $options['workhours_end'] : null,
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	        ),
	    ));
	    $subform->addElement('note', 'workhours_end_description', array(
	        'label'      => null,
	        'required'   => false,
	        
	        'value' => $this->translate('Here you can specify which hours are unlocked for tour planning.'),
	        
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	        ),
	    ));
	
	
	    
	    
	    
	    
	    
	    
	    
	    return $this->filter_by_block_name($subform , __FUNCTION__);
	}
	
	
	public function create_form_patient_course_settings( $options = array(), $elementsBelongTo = 'patient_course_settings' )
	{
			
		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
		$this->mapValidateFunction($__fnName , "create_form_isValid");
			
		$this->mapSaveFunction($__fnName , "save_form_patient_course_settings");
			
			
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => 'patient-course-settings'));
		$subform->setLegend('Verlauf client settings');
		$subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	
			
		$this->__setElementsBelongTo($subform, $elementsBelongTo);
	
		$subform->addElement('text', 'v_color', array(
				'label'        => $this->translate('select calendar v color:'),
				'value'        => $options['v_color'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				'class' => 'colorSelector',
				'style' => 'background-color: ' . $options['v_color'],
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'id'=>'vcolorSelector',
						)),
						array(array('data' => 'HtmlTag'), array('tag' => 'td',  'class' => 'data-td')),
						array('Label', array('tag' => 'td', 'id' => 'v-color')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
				),
		));
	
		$subform->addElement('text', 'v_text_color', array(
				'label'        => $this->translate('select calendar v textcolor:'),
				'value'        => $options['v_text_color'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				'class' => 'colorSelector',
				'style' => 'background-color: ' . $options['v_text_color'],
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'id'=>'vtextcolorSelector',
						)),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'data-td')),
						array('Label', array('tag' => 'td', 'id' => 'v-text-color', 'placement' => 'PREPEND')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
				),
		));
	
		$subform->addElement('text', 'xt_color', array(
				'label'        => $this->translate('select calendar xt color:'),
				'value'        => $options['xt_color'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				'class' => 'colorSelector',
				'style' => 'background-color: ' . $options['xt_color'],
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'id'=>'xtcolorSelector',
						)),
						array(array('data' => 'HtmlTag'), array('tag' => 'td',  'class' => 'data-td')),
						array('Label', array('tag' => 'td', 'id' => 'xt-color')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
				),
		));
	
		$subform->addElement('text', 'xt_text_color', array(
				'label'        => $this->translate('select calendar xt textcolor:'),
				'value'        => $options['xt_text_color'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				'class' => 'colorSelector',
				'style' => 'background-color: ' . $options['xt_text_color'],
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'id'=>'xttextcolorSelector',
						)),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'data-td')),
						array('Label', array('tag' => 'td', 'id' => 'xt-text-color', 'placement' => 'PREPEND')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
				),
		));
	
		$subform->addElement('text', 'u_color', array(
				'label'        => $this->translate('select calendar u color:'),
				'value'        => $options['u_color'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				'class' => 'colorSelector',
				'style' => 'background-color: ' . $options['u_color'],
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'id'=>'ucolorSelector',
						)),
						array(array('data' => 'HtmlTag'), array('tag' => 'td',  'class' => 'data-td')),
						array('Label', array('tag' => 'td', 'id' => 'u-color')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
				),
		));
	
		$subform->addElement('text', 'u_text_color', array(
				'label'        => $this->translate('select calendar u textcolor:'),
				'value'        => $options['u_text_color'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				'class' => 'colorSelector',
				'style' => 'background-color: ' . $options['u_text_color'],
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'id'=>'utextcolorSelector',
						)),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'data-td')),
						array('Label', array('tag' => 'td', 'id' => 'u-text-color', 'placement' => 'PREPEND')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
				),
		));
	
		return $this->filter_by_block_name($subform , __FUNCTION__);
	}
	
	public function create_form_activate_shortcut_settings( $options = array(), $elementsBelongTo = 'activate_shortcut_settings' )
	{
			
		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
		$this->mapValidateFunction($__fnName , "create_form_isValid");
			
		$this->mapSaveFunction($__fnName , "save_form_activate_shortcut_settings");
			
			
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => 'activate_shortcut_settings', 'style' => 'width: 100%;'));
		$subform->setLegend('Shortcut client settings');
		$subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	
			
		$this->__setElementsBelongTo($subform, $elementsBelongTo);
	
		$subform->addElement('select', 'activate_shortcut_v_settings', array(
				'label'      => $this->translate('Activate Shortcut V in fb3 and fb8'),
				'required'   => false,
				'multiOptions' => array('yes' => 'Ja', 'no' => 'Nein'),
				'value' => isset($options['activate_shortcut_v_settings']) ? $options['activate_shortcut_v_settings'] : 'no',
				'id' => "activate_v_shortcut",
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
				),
		));
	
		return $this->filter_by_block_name($subform , __FUNCTION__);
	}
	
	public function create_form_activate_shortcut_yes_settings( $options = array(), $elementsBelongTo = null )
	{
			
		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
		$this->mapValidateFunction($__fnName , "create_form_isValid");
			
		$this->mapSaveFunction($__fnName , "save_form_activate_shortcut_yes_settings");
			
			
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => 'activate_shortcut_yes_settings'));
		$subform->setLegend('Shortcut client settings types');
		$subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	
			
		$this->__setElementsBelongTo($subform, $elementsBelongTo);
		
		$colopts = $this->getColumnMapping('activate_shortcut_v_settings');
		$multiopts = array(); 
		foreach($colopts as $colopt)
		{
			$multiopts[] = $colopt;
		}
	
		$subform->addElement('multiCheckbox', 'activate_shortcut_v_yes_settings', array(
							'label'      => null,
	                        'required'   => false,
	                        'multiOptions' => $multiopts,
							'value' => $options['activate_shortcut_v_yes_settings'] ? $options['activate_shortcut_v_yes_settings'] : null,
	                        'separator'  => '<br/>',
							'decorators' => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td')),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
					)));
	
		return $this->filter_by_block_name($subform , __FUNCTION__);
	}
	
	//TODO-3365 Carmen 21.08.2020
	public function create_form_pharmaindex_settings( $options = array(), $elementsBelongTo = null )
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => ''));
		$subform->setLegend($this->translate('Pharmaindex client settings'));
		$subform->setAttrib("class", "label_same_size");
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		 
		$pharmaindex_settings_available = Doctrine_Core::getTable('Client')->getColumnDefinition('pharmaindex_settings');
		 
	
		foreach ($pharmaindex_settings_available['values'] as $row) {
			 
			$subform->addElement('radio',  $row, array(
					//'isArray'      => true,
					'multiOptions' => array('no' => 'Nein', 'yes' => 'Ja'),
					'value'        => ($row == 'drug' || $row == 'atc') ? ($options[$row] ? $options[$row] : 'yes') : ($options[$row] ? $options[$row] : 'no'),
					'label'        => $this->translate("pharmaindex_settings_{$row}"),
					'required'     => false,
					'filters'      => array('StringTrim'),
					'validators'   => array('NotEmpty'),
					'decorators' => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style' => "width:100px; display:block")),
							array('Label', array('tag' => 'td' , 'style' => 'padding:10px; width:180px; display:block')),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
					),
					'separator' => PHP_EOL,
	
					'belongsTo' => 'pharmaindex_settings',
	
					'onChange' => "if(this.value == 'yes') {\$('.show_hide', \$(this).parents('tr')).show()} else {\$('.show_hide', \$(this).parents('tr')).hide()}",
					));
			 
			$display =  $options[$row] == 'yes' ? "" : "display:none";
			$subform->addElement('note',  "pharmaindex_settings_{$row}_explain", array(
					'value' => $this->translate("pharmaindex_settings_{$row}_explain"),
					'decorators' => array(
							'ViewHelper',
							array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' =>'show_hide', 'style' => $display)),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
					),
					'separator' => PHP_EOL
					));
	
		}
		 
		 
		return $this->filter_by_block_name($subform , __FUNCTION__);
	}
	//--

	//ISPC-2806 Dragos 27.01.2021
	public function create_form_compliant_settings( $options = array(), $elementsBelongTo = null, $posted_values = array() )
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => ''));
		$subform->setLegend($this->translate('Complaint client settings'));
		$subform->setAttrib("class", "label_same_size");
		$subform->setAttrib("id", "complaing_emails");

		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
				'elementsBelongTo' => $elementsBelongTo
			));
		}

		$status = isset($posted_values['status']) ? $posted_values['status']
			: (isset($options['status']) ? $options['status'] : 'disabled');

		$subform->addElement('select', 'status', array(
			'label'      => $this->translate('Complaint Additional E-mails'),
			'required'   => false,
			'multiOptions' => array('enabled' => 'Enabled', 'disabled' => 'Disabled'),
			'value' => $status,
			'id' => "activate_complaint_settings",
			'decorators' => array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first' ,'class' => 'settings_row_ed_master')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
			),
		));

		$subform->addElement('text', 'pharmacy_email', array(
			'label'        => $this->translate('Complaint Pharmacy E-mail'),
			'value'        => isset($posted_values['pharmacy_email']) ? $posted_values['pharmacy_email'] : $options['pharmacy_email'],
			'required'     => true,
			'filters'      => array('StringTrim'),
			'validators'   => array('NotEmpty'),
			'disable' => $status != 'enabled',
			'class' => 'w400',
			'style' => '',
			'decorators' => array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first' ,'class' => 'settings_row_ed_master')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
			),
		));

		$subform->addElement('text', 'office_email', array(
			'label'        => $this->translate('Complaint Office E-mail'),
			'value'        => isset($posted_values['office_email']) ? $posted_values['office_email'] : $options['office_email'],
			'required'     => true,
			'filters'      => array('StringTrim'),
			'validators'   => array('NotEmpty'),
			'disable' => $status != 'enabled',
			'class' => 'w400',
			'style' => '',
			'decorators' => array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first' ,'class' => 'settings_row_ed_master')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
			),
		));

		$subform->addElement('text', 'email_subject', array(
			'label'        => $this->translate('Complaint E-mail subject'),
			'value'        => isset($posted_values['email_subject']) ? $posted_values['email_subject'] : $options['email_subject'],
			'required'     => true,
			'filters'      => array('StringTrim'),
			'validators'   => array('NotEmpty'),
			'disable' => $status != 'enabled',
			'class' => '',
			'style' => '',
			'decorators' => array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first' ,'class' => 'settings_row_ed_master')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
			),
		));

		$subform->addElement('textarea', 'email_body', array(
			'label'        => $this->translate('Complaint E-mail message'),
			'value'        => isset($posted_values['email_body']) ? $posted_values['email_body'] : $options['email_body'],
			'required'     => true,
			'filters'      => array('StringTrim'),
			'validators'   => array('NotEmpty'),
			'disable' => $status != 'enabled',
			'class' => '',
			'style' => '',
			'decorators' => array(
				'ViewHelper',
				array('Errors'),
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first' ,'class' => 'settings_row_ed_master')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
			),
		));

		$this->addSubForm($subform,'complaint_settings');

		return $this->filter_by_block_name($subform , __FUNCTION__);
	}
	//--
		
}

?>
