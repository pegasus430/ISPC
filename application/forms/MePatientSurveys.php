<?php
/**
 * ISPC-2432 Ancuta 13.01.2020
 */

require_once("Pms/Form.php");

class Application_Form_MePatientSurveys extends Pms_Form
{
	public function validate($post)
	{

		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
	    if(!$val->isstring($post['survey_name'])){
			$this->error_message['survey_name']=$Tr->translate("please_add_survey_name"); $error=1;
		}
		if(!$val->isstring($post['survey_url'])){
			$this->error_message['survey_url']= $Tr->translate('please_add_survey_url'); $error=2;
		}
		if(!$val->isstring($post['painpool_survey_id'])){
			$this->error_message['survey_painpool_survey_id']= $Tr->translate('please_add_survey_painpool_id'); $error=3;
		}
		
		if(count($post['clients'])<1){
			$this->error_message['survey_clients']= $Tr->translate('please_add_survey_clients'); $error=4;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

 
 

	public function save_survey($post)
	{
	    
	    if(!empty($post['clients'])){
	        foreach($post['clients'] as $k=>$clid){
	            $post['MePatientSurveysClients'][$k]['clientid']=$clid;
	        }
	    }
	    
	    //MEP-209 Ancuta 19.10.2020 
	    //MEP-208,MEP-209,MEP-210 Ancuta 19.10.2020
	    // first remove clients 
	    $this->survey_clear_clients($post['survey_id']);
	    //
	    MePatientSurveysTable::getInstance()->findOrCreateOneBy('id', $post['survey_id'], $post);
	    
	    /* 
	    $mps = new MePatientSurveys();
	    $mps->survey_name = $post['survey_name'];
	    $mps->survey_url = $post['survey_url'];
	    $mps->painpool_survey_id = $post['painpool_survey_id']; 
	    $mps->save();
	    
	    if($mps->id>0 && !empty($post['clients']))
	    {
	        $survey_id =$mps->id;
	        $res = array();
	        foreach($post['clients'] as $clientid){
	            $res[]=array(
	                'survey_id' => $survey_id,
	                'clientid' => $clientid
	            );
	        }
	        
	        if($res){
    	        $collection = new Doctrine_Collection('MePatientSurveysClients');
    	        $collection->fromArray($res);
    	        $collection->save();
	        }
	    }
         */
	    
	    
	    
	}

	public function save_survey_old($post)
	{
	    $mail = new MePatientSurveys();
	    $mail->survey_name = $post['survey_name'];
	    $mail->survey_url = $post['survey_url'];
	    $mail->painpool_survey_id = $post['painpool_survey_id']; 
	    $mail->save();
	    
	    if($mail->id>0 && !empty($post['clients']))
	    {
	        $survey_id =$mail->id;
	        $res = array();
	        foreach($post['clients'] as $clientid){
	            $res[]=array(
	                'survey_id' => $survey_id,
	                'clientid' => $clientid
	            );
	        }
	        
	        if($res){
    	        $collection = new Doctrine_Collection('MePatientSurveysClients');
    	        $collection->fromArray($res);
    	        $collection->save();
	        }
	    }

	}
 
	/**
	 * MEP-209 Ancuta 19.10.2020
	 * MEP-208,MEP-209,MEP-210 Ancuta 19.10.2020
	 * @param number $survey_id
	 * @return void|boolean
	 */
	public function survey_clear_clients($survey_id = 0){
	    if(empty($survey_id)){
	        return;
	    }
	    
	    // Has softdelete
        Doctrine_Query::create()
        ->delete('MePatientSurveysClients')
        ->where('survey_id = ?', $survey_id)
        ->execute();
	    
	}
}

?>