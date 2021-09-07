<?php
/**
 * PatientAnlage2Kinder.php
 * @author Ancuta
 * ISPC-2882
 * 21.04.2021
 *
 */
class Application_Form_PatientAnlage2Kinder extends Pms_Form
{ 
    
    public function save_form($ipid = null, array $data = array())
    {
         if (empty($ipid)) {
            throw new Exception('Contact Admin, formular cannot be saved.', 0);
         }

         if($data['saved_id'] == '')
         {
            $data['saved_id'] = null;
         }
         
         if(strlen($data['crisis_intervention_date'])){
             $data['crisis_intervention_date'] = date('Y-m-d',strtotime($data['crisis_intervention_date']));
         }
         
         
         
         if(strlen($data['form_date'])){
             $data['form_date'] = date('Y-m-d',strtotime($data['form_date']));
         }
         
//          dd($data);
         $entity = PatientAnlage2KinderTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['saved_id'], $ipid], $data);
         
         return $entity; 
    }
	
}

