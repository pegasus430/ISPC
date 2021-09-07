<?php
require_once("Pms/Form.php");

class Application_Form_PatientRemedy extends Pms_Form 
{
    
    public function getVersorgerExtract()
    {
        return  array(
            array("label"=>$this->translate('Patient_tool'), "cols"=>array("remedies")),
//             array("label"=>$this->translate('supplies'), "cols"=>array("Supplies" => "nice_name")),
            array("label"=>$this->translate('supplies'), "cols"=>array("Supplies" => "supplier")),
            
        );
    }
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("remedies")),
            array(array("Supplies"=>"nice_name")),
        );
    }
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientRemedy';
    
	
	public function validate($post)
 	{
 		//print_r($post);
 		//exit();
 		
 		$Tr = new Zend_View_Helper_Translate();
 
 		$error = 0;
 		//$val = new Pms_Validation();
 		foreach($post['row'] as  $key => $value)
 		{
 			if(empty($value['remedy']))
 			{
 				$error = 1;
 				$this->error_message['remedy'] = $Tr->translate ('insert_name_remedy(Hilfsmittel II)');
 				
 			}
 			
 		}
 		
 		
 		if($error == 0)
 		{
 			return true;
 		}else{
 			return false;
 		}
 
 		
 	}
	
	public function insert_Data ($post, $ins_master = false)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
	
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpId($decid);
		if($ins_master)
		{
			foreach($ins_master as $km=>$vm)
			{
				$supp_arr[$km] = $vm;
			}
		}
		if(empty($post['supplier']) && strlen($post['supplier'])==0)
		{		
			//$post['supplier']="null";
			$post['supplier_id']="null";
		}
		else 
		{
			if($post['supplier_master_id'] != '')
			{ 			
				$sup = Doctrine::getTable('Supplies')->find($post['supplier_master_id']);
				$sdata = $sup->toArray();
				//var_dump($sdata);
				if($sdata)
				{
					if ($sdata['indrop'] == '0')
					{
						if(!$ins_master)
						{
							$fdoc = new Supplies();
							$fdoc->supplier = $sdata['supplier'];
							$fdoc->first_name = $sdata['first_name'];
							$fdoc->last_name = $sdata['last_name'];
							$fdoc->salutation = $sdata['salutation'];
							$fdoc->street1 = $sdata['street1'];
							$fdoc->zip = $sdata['zip'];
							$fdoc->fax = $sdata['fax'];
							$fdoc->email = $sdata['email'];
							$fdoc->city = $sdata['city'];
							$fdoc->phone = $sdata['phone'];
							if(strlen($sdata['logo']) > '0')
							{
								$fdoc->logo = $sdata['logo'];
							}
							$fdoc->indrop = 1;
							$fdoc->clientid = $clientid;
							$fdoc->save();
							
							$pfl_cl = new PatientSupplies();
							$pfl_cl->ipid = $ipid;
							$pfl_cl->supplier_id = $fdoc->id;
							$pfl_cl->save();
								
							$post['supplier_id'] = $fdoc->id;
								
							$supp_arr[$sdata['id']]['ps_id'] = $fdoc->id;
							//var_dump($supp_arr);
						}
						else
						{
								if(!$ins_master[$sdata['id']])
								{
									$fdoc = new Supplies();
									$fdoc->supplier = $sdata['supplier'];
									$fdoc->first_name = $sdata['first_name'];
									$fdoc->last_name = $sdata['last_name'];
									$fdoc->salutation = $sdata['salutation'];
									$fdoc->street1 = $sdata['street1'];
									$fdoc->zip = $sdata['zip'];
									$fdoc->fax = $sdata['fax'];
									$fdoc->email = $sdata['email'];
									$fdoc->city = $sdata['city'];
									$fdoc->phone = $sdata['phone'];
									if(strlen($sdata['logo']) > '0')
									{
										$fdoc->logo = $sdata['logo'];
									}
									$fdoc->indrop = 1;
									$fdoc->clientid = $clientid;
									$fdoc->save();
						
									$pfl_cl = new PatientSupplies();
									$pfl_cl->ipid = $ipid;
									$pfl_cl->supplier_id = $fdoc->id;
									$pfl_cl->save();
			
									$post['supplier_id'] = $fdoc->id;
							
									$supp_arr[$sdata['id']]['ps_id'] = $fdoc->id;
									//var_dump($supp_arr);
								}
								else 
								{
									$post['supplier_id'] = $ins_master[$sdata['id']]['ps_id'];
									
								}
							}
					
					}
					else
					{
						$post['supplier_id'] = $sdata['id'];			
					}
				}
			}
			else
			{
				$fdoc = new Supplies();
				$fdoc->supplier = $post['supplier'];
				$fdoc->indrop = 1;
				$fdoc->clientid = $clientid;
				$fdoc->save();
				
				$pfl_cl = new PatientSupplies();
				$pfl_cl->ipid = $ipid;
				$pfl_cl->supplier_id = $fdoc->id;
				$pfl_cl->save();
					
				$post['supplier_id'] = $fdoc->id;
				
			}
		}

		$rm= new PatientRemedies();
		$rm->ipid = $ipid;
		$rm->remedies = $post['remedy'];
		$rm->supplier = $post['supplier_id'];
		$rm->save();

		return $supp_arr;
	}
	
	public function update_data ($post, $ins_master = false)
	{
		//update
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpId($decid);
		if($ins_master)
		{
			foreach($ins_master as $km=>$vm)
			{
				$supp_arr[$km] = $vm;
			}
		}
		if(empty($post['supplier']) && strlen($post['supplier'])==0)
		{
			//$post['supplier']="null";
			$post['supplier_id']="null";
		}
		else 
		{
			if($post['supplier_master_id'] != '')
			{			
				$sup = Doctrine::getTable('Supplies')->find($post['supplier_master_id']);
				$sdata = $sup->toArray();
				
				//var_dump($sdata);
				if($sdata)
				{
					if ($sdata['indrop'] == '0')
					{
						if(!$ins_master)
						{
							$fdoc = new Supplies();
							$fdoc->supplier = $sdata['supplier'];
							$fdoc->first_name = $sdata['first_name'];
							$fdoc->last_name = $sdata['last_name'];
							$fdoc->salutation = $sdata['salutation'];
							$fdoc->street1 = $sdata['street1'];
							$fdoc->zip = $sdata['zip'];
							$fdoc->fax = $sdata['fax'];
							$fdoc->email = $sdata['email'];
							$fdoc->city = $sdata['city'];
							$fdoc->phone = $sdata['phone'];
							if(strlen($sdata['logo']) > '0')
							{
								$fdoc->logo = $sdata['logo'];
							}
							$fdoc->indrop = 1;
							$fdoc->clientid = $clientid;
							$fdoc->save();
								
							$pfl_cl = new PatientSupplies();
							$pfl_cl->ipid = $ipid;
							$pfl_cl->supplier_id = $fdoc->id;
							$pfl_cl->save();
						
							$post['supplier_id'] = $fdoc->id;
						
							$supp_arr[$sdata['id']]['ps_id'] = $fdoc->id;
							//var_dump($supp_arr);
						}
						else
						{
							if(!$ins_master[$sdata['id']])
							{	
								$fdoc = new Supplies();
								$fdoc->supplier = $sdata['supplier'];
								$fdoc->first_name = $sdata['first_name'];
								$fdoc->last_name = $sdata['last_name'];
								$fdoc->salutation = $sdata['salutation'];
								$fdoc->street1 = $sdata['street1'];
								$fdoc->zip = $sdata['zip'];
								$fdoc->fax = $sdata['fax'];
								$fdoc->email = $sdata['email'];
								$fdoc->city = $sdata['city'];
								$fdoc->phone = $sdata['phone'];
								if(strlen($sdata['logo']) > '0')
								{
									$fdoc->logo = $sdata['logo'];
								}
								$fdoc->indrop = 1;
								$fdoc->clientid = $clientid;
								$fdoc->save();
						
								$pfl_cl = new PatientSupplies();
								$pfl_cl->ipid = $ipid;
								$pfl_cl->supplier_id = $fdoc->id;
								$pfl_cl->save();
			
								$post['supplier_id'] = $fdoc->id;
							
								$supp_arr[$sdata['id']]['ps_id'] = $fdoc->id;
							}
							else 
							{
								$post['supplier_id'] = $ins_master[$sdata['id']]['ps_id'];						
							}
						}
					}
					else
					{
						$post['supplier_id'] = $sdata['id'];
					}				
				}
			}
			else
			{
				if($post['supplier_old'] != $post['supplier'])
				{
					$fdoc = new Supplies();
					$fdoc->supplier = $post['supplier'];
					$fdoc->indrop = 1;
					$fdoc->clientid = $clientid;
					$fdoc->save();
					
					$pfl_cl = new PatientSupplies();
					$pfl_cl->ipid = $ipid;
					$pfl_cl->supplier_id = $fdoc->id;
					$pfl_cl->save();
					
					$post['supplier_id'] = $fdoc->id;
				}			
			}			
		}

		//var_dump($post['supplier_id']);
		$q = Doctrine_Query::create()
		->update('PatientRemedies')
		->set('remedies','?', $post['remedy'])
		->set('supplier','?', $post['supplier_id'])
		->where("id = ?", $post['id_update']);
		$q->execute();
		
		//var_dump($supp_arr);
		return $supp_arr;
		
	}
	
	
	/**
	 * remedies are linked to a supplies
	 */
	public function create_form_remedies_2_supplies($options =  array() , $elementsBelongTo = null)
	{
	    $this->mapValidateFunction(__FUNCTION__, "create_form_isValid");
	
	    $this->mapSaveFunction(__FUNCTION__, "save_form_remedies_2_supplies");
	
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend("Patient_tool");
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
	
// 	            	            	    	            	    if (!empty($options)) dd($options);
	
	    if ( ! isset($options['Supplies'])) {
	        $options['Supplies'] = $options;
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
	
        $subform->addElement('hidden', 'supplier_indrop', array(
            'value'        => isset($options['supplier_indrop']) ? $options['supplier_indrop'] : -1 ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
        $subform->addElement('hidden', 'supplier', array(
            'value'        => ! empty($options['supplier']) &&  $options['supplier'] != 'null' ? $options['supplier'] : null ,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array('ViewHelper'),
        ));
        
        
         
        $subform->addElement('hidden', 'remedy_id', array(
            'value'        => $options['remedy_id'] ? $options['remedy_id'] : -1 ,
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
        $subform->addElement('text', 'remedies', array(
            'value'        => $options['remedies'] ,
            'label'        => 'Patient_tool',
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
            'data-livesearch'  => 'RemedyAid',
        ));
        $subform->addElement('text', 'nice_name', array(
            'value'        => ! empty($options['Supplies']['supplier']) && $options['Supplies']['supplier'] != 'null' ? $options['Supplies']['supplier'] : "" ,
            'label'        => 'supplier',
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
            'data-livesearch'  => 'RemedySupplies',
        ));
	
        
        
	
	
        return $subform;
	
	}
	
	
	
	
	public function create_form_remedies($values =  array() , $elementsBelongTo = null)
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend($this->translate('Remedy:'));
	    $subform->setAttrib("class", "label_same_size");
	
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	
	    $remedies = PatientRemedies::getDefaultRemedies();
	    
	    $subform->addElement('multiCheckbox', 'remedies', array(
	        'label'      => null,
	        'separator'  => '&nbsp;',
	        'required'   => false,
	        'multiOptions'=> $remedies,
	        'value' => $values,
	        'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        )
	    ));
	    
	
	    return $subform;
	}
	
	public function save_form_remedies($ipid =  '' , $data = array())
	{
	    //cb
	    if(empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    $result = array();
	    $entity = new PatientRemedies();
	    
	    if(! is_array($data['remedies'])) {
	        $data['remedies'] = array($data['remedies']);
	    }
	    	    
	    $remedies = PatientRemedies::getDefaultRemedies();
	    
	    $to_delete =  array_diff($remedies, $data['remedies']);
	    
	    if (! empty($to_delete)) {
	       $entity->deleteRemedies($ipid, array_values($to_delete));
	    }	    
	    
	    $data['supplier'] = 'null';

	    //insert/update one by one
	    foreach ($data['remedies'] as $remedy) {
	        $row =  $data;
	        $row['remedies'] = $remedy;
	        
	        $result[] = $entity->findOrCreateOneByIpidAndRemedies($ipid, $remedy, $row);
	        
	    }
	    
	    return $result;
	
	}
	
	

	public function save_form_remedies_2_supplies($ipid =  '' , $data = array())
	{
	
	    
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    

	    if ($data['supplier_indrop'] == '0' && ! empty($data['supplier'])) {
	        
	        //we must add this supplier_id to our list
	        $supplies = new Supplies();
	       
	        if ($originalSupplies = $supplies->getTable()->findOneByIdAndClientid ($data['supplier'], $this->logininfo->clientid)) {
    	        
	            $dataOoriginalSupplies = $originalSupplies->toArray();
	            $dataOoriginalSupplies['indrop'] = 1;
	            
	            $newSupplies = $supplies->findOrCreateOneBy('id', null, $dataOoriginalSupplies);

	            $patientSupplies = new PatientSupplies();
	            $newPatientSupplies = $patientSupplies->findOrCreateOneBy('id', null, array(
	                'ipid' => $ipid,
	                'supplier_id' => $newSupplies->id,
	                'supplier_comment' => $newSupplies->comments
	            ));
	            
	            $data['supplier'] = $newSupplies->id;
	            
	        }
	        
	    } else {
	        //we allready have this supplier_id, we just link to it
	        //$data['supplier'] = $data['supplier_id'];
	    }
	
	    $data['ipid'] = $ipid;
	    
	    $entity = new PatientRemedies();
	    return $entity->findOrCreateOneBy('id', $data['id'], $data);
	
	
	}
	
	
}

?>