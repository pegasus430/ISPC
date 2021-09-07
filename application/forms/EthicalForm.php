<?php

require_once("Pms/Form.php");

class Application_Form_EthicalForm extends Pms_Form
{
	public function InsertData($post)
	{
		
		$sql = new EthicalForm();
		$sql->ipid = $post['ipid'];
		$sql->capacitytoconsent = $post['capacitytoconsent'];
		$sql->expressionofwill = $post['expressionofwill'];
		
		
		$sql->living_checked = $post['living_checked'];
		$sql->living_situation = $post['living_situation'];
		if(is_array($post['justificationforomission']))
		{
			$sql->justificationforomission = implode(",",$post['justificationforomission']);
			
		}
		
	if(is_array($post['nolongerindexed']))
			{
				  $sql->nolongerindexed = implode(",",$post['nolongerindexed']);
			}
		$sql->nolongerindexed_textarea= $post['nolongerindexed_textarea'];
	if(is_array($post['patientexpectations']))
			{
				$sql->patientexpectations = implode(",",$post['patientexpectations']);
			}
		$sql->patientexpectations_explicitrequest = $post['patientexpectations_explicitrequest'];
	if(is_array($post['consentdiscussion']))
			{
				$sql->consentdiscussion = implode(",",$post['consentdiscussion']);
			}
		$sql->consentdiscussion_explicitrequest = $post['consentdiscussion_explicitrequest'];
	 if(is_array($post['expectationsfamily']))
			{
				$sql->expectationsfamily = implode(",",$post['expectationsfamily']);
			}
		
		if(!empty($post['expectationsfamily_withpatient']))
		{
			$sql->expectationsfamily_withpatient= date('Y-m-d', strtotime($post['expectationsfamily_withpatient']));
		}else{
			$sql->expectationsfamily_withpatient='0000-00-00';
		}
		
		if(!empty($post['expectationsfamily_withsupervisor']))
		{
			$sql->expectationsfamily_withsupervisor= date('Y-m-d', strtotime($post['expectationsfamily_withsupervisor']));
		}else{
			$sql->expectationsfamily_withsupervisor='0000-00-00';
		}
		
		if(!empty($post['expectationsfamily_withfamily']))
		{
			$sql->expectationsfamily_withfamily= date('Y-m-d', strtotime($post['expectationsfamily_withfamily']));
		}else{
			$sql->expectationsfamily_withfamily='0000-00-00';
		}
		if(!empty($post['expectationsfamily_withotherservices']))
		{
			$sql->expectationsfamily_withotherservices = date('Y-m-d', strtotime($post['expectationsfamily_withotherservices']));
		}else{
			$sql->expectationsfamily_withotherservices ='0000-00-00';
		}
		
	if(is_array($post['consensusbetween']))
		{
			$sql->consensusbetween = implode(",",$post['consensusbetween']);
		}
		$sql->furtherinformation= $post['furtherinformation'];
		$sql->save();
		
	}
	
	public function UpdateData($post)
	{
		    
			$sql = Doctrine::getTable('EthicalForm')->findBy('ipid',( $post['ipid']))->getFirst();
			$sql->capacitytoconsent = $post['capacitytoconsent'];
			$sql->expressionofwill = $post['expressionofwill'];
			
	       
			
			$sql->living_checked = $post['living_checked'];
			$sql->living_situation = $post['living_situation'];
			if(is_array($post['justificationforomission']))
			{
				  $sql->justificationforomission = implode(",",$post['justificationforomission']);
			}
	        if(is_array($post['nolongerindexed']))
			{
				  $sql->nolongerindexed = implode(",",$post['nolongerindexed']);
			}
			
			$sql->nolongerindexed_textarea= $post['nolongerindexed_textarea'];
			if(is_array($post['patientexpectations']))
			{
				$sql->patientexpectations = implode(",",$post['patientexpectations']);
			}
			$sql->patientexpectations_explicitrequest = $post['patientexpectations_explicitrequest'];
			if(is_array($post['consentdiscussion']))
			{
				$sql->consentdiscussion = implode(",",$post['consentdiscussion']);
			}
			$sql->consentdiscussion_explicitrequest = $post['consentdiscussion_explicitrequest'];
	       if(is_array($post['expectationsfamily']))
			{
				$sql->expectationsfamily = implode(",",$post['expectationsfamily']);
			}
			
	    if(!empty($post['expectationsfamily_withpatient']))
		{
			$sql->expectationsfamily_withpatient= date('Y-m-d', strtotime($post['expectationsfamily_withpatient']));
		}else{
			$sql->expectationsfamily_withpatient='0000-00-00';
		}
		
		if(!empty($post['expectationsfamily_withsupervisor']))
		{
			$sql->expectationsfamily_withsupervisor= date('Y-m-d', strtotime($post['expectationsfamily_withsupervisor']));
		}else{
			$sql->expectationsfamily_withsupervisor='0000-00-00';
		}
		
		if(!empty($post['expectationsfamily_withfamily']))
		{
			$sql->expectationsfamily_withfamily= date('Y-m-d', strtotime($post['expectationsfamily_withfamily']));
		}else{
			$sql->expectationsfamily_withfamily='0000-00-00';$sql->capacitytoconsent = $post['capacitytoconsent'];
			$sql->expressionofwill = $post['expressionofwill'];
			
	       
			
			$sql->living_checked = $post['living_checked'];
			$sql->living_situation = $post['living_situation'];
			if(is_array($post['justificationforomission']))
			{
				  $sql->justificationforomission = implode(",",$post['justificationforomission']);
			}
	        if(is_array($post['nolongerindexed']))
			{
				  $sql->nolongerindexed = implode(",",$post['nolongerindexed']);
			}
			
			$sql->nolongerindexed_textarea= $post['nolongerindexed_textarea'];
			if(is_array($post['patientexpectations']))
			{
				$sql->patientexpectations = implode(",",$post['patientexpectations']);
			}
			$sql->patientexpectations_explicitrequest = $post['patientexpectations_explicitrequest'];
			if(is_array($post['consentdiscussion']))
			{
				$sql->consentdiscussion = implode(",",$post['consentdiscussion']);
			}
			$sql->consentdiscussion_explicitrequest = $post['consentdiscussion_explicitrequest'];
	       if(is_array($post['expectationsfamily']))
			{
				$sql->expectationsfamily = implode(",",$post['expectationsfamily']);
			}
			
	    if(!empty($post['expectationsfamily_withpatient']))
		{
			$sql->expectationsfamily_withpatient= date('Y-m-d', strtotime($post['expectationsfamily_withpatient']));
		}else{
			$sql->expectationsfamily_withpatient='0000-00-00';
		}
		
		if(!empty($post['expectationsfamily_withsupervisor']))
		{
			$sql->expectationsfamily_withsupervisor= date('Y-m-d', strtotime($post['expectationsfamily_withsupervisor']));
		}else{
			$sql->expectationsfamily_withsupervisor='0000-00-00';
		}
		
		if(!empty($post['expectationsfamily_withfamily']))
		{
			$sql->expectationsfamily_withfamily= date('Y-m-d', strtotime($post['expectationsfamily_withfamily']));
		}else{
			$sql->expectationsfamily_withfamily='0000-00-00';
		}
		if(!empty($post['expectationsfamily_withotherservices']))
		{
			$sql->expectationsfamily_withotherservices = date('Y-m-d', strtotime($post['expectationsfamily_withotherservices']));
		}else{
			$sql->expectationsfamily_withotherservices ='0000-00-00';
		}
		if(is_array($post['consensusbetween']))
		{
			$sql->consensusbetween = implode(",",$post['consensusbetween']);
		}
			
			$sql->furtherinformation= $post['furtherinformation'];
		}
		if(!empty($post['expectationsfamily_withotherservices']))
		{
			$sql->expectationsfamily_withotherservices = date('Y-m-d', strtotime($post['expectationsfamily_withotherservices']));
		}else{
			$sql->expectationsfamily_withotherservices ='0000-00-00';
		}
		if(is_array($post['consensusbetween']))
		{
			$sql->consensusbetween = implode(",",$post['consensusbetween']);
		}
			
			$sql->furtherinformation= $post['furtherinformation'];
			$sql->save();
	   
	    }
	
}
?>