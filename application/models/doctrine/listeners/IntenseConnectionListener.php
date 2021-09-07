<?php
/**
 * ISPC-2614  // Maria:: Migration ISPC to CISPC 08.08.2020
 * @author  Jul 16, 2020  ancuta
 *
 */
class IntenseConnectionListener extends Doctrine_Record_Listener
{ 
    /**
     * Array of options
     *
     * @var string
     */
    protected $_options = array();
    
    /**
     * __construct
     *
     * @param string $options
     * @return void
     */
    public function __construct(array $options)
    {
        $this->_options = $options;
    }
    
    public function preDelete(Doctrine_Event $event)
    {
        if (Zend_Controller_Front::getInstance()->getRequest()->getControllerName() == 'patient'
            && Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'patientmasteradd')
        {
            return;
        }
        
        $Invoker = $event->getInvoker();
        $ComponentName = $Invoker->getTable()->getComponentName();

        $data = array();
        $data[] = array(
            'ipid' => isset($Invoker->ipid) ? $Invoker->ipid : "",
            'parent_table' =>$ComponentName,
            'parent_table_id' =>$Invoker->id,
            'model_name' =>$ComponentName,
            'details' =>serialize($Invoker->data)
        );
        
        if (!empty($data)){
            $collection = new Doctrine_Collection('IntenseConnectionsLog');
            $collection->fromArray($data);
            $collection->save();
            
        }
        return;
    }
    
    public function postUpdate(Doctrine_Event $event)
    {
        if (Zend_Controller_Front::getInstance()->getRequest()->getControllerName() == 'patient'
            && Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'patientmasteradd')
        {
            return;
        }
        
        
        $Invoker = $event->getInvoker();
        $ComponentName = $Invoker->getTable()->getComponentName();

        // stamdatem and Versorger
        if (! empty($_REQUEST['__category'])) {
            $OptionName = $_REQUEST['__category'];
        }
        
        if($_REQUEST['__action'] != 'saveVersorger' && $ComponentName == "FamilyDoctor"){
            return;
        }
        if (! isset($_GET['id'])) {

            $ipid = $event->getInvoker()->ipid;
        } else {

            $pid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpid($pid);
        }

        $int_connection = new IntenseConnections();
        $share_direction = $int_connection->get_intense_connection_by_ipid($ipid);
        
        if(empty($share_direction)){
            return;
        }
        
        $obj = new $ComponentName();
        $obj_columns = $obj->getTable()->getColumns();
        

        foreach ($share_direction as $direction_k => $share_info) {
            
            if (! empty($share_info['intense_connection'])) {
                $option2option_type = array();
                foreach ($share_info['intense_connection'] as $con => $con_ionfo) {
                    $IntenseConnectionsOptions = array_column($con_ionfo['IntenseConnectionsOptions'], 'option_name');
                    foreach($con_ionfo['IntenseConnectionsOptions'] as $kco=>$co){
                        $option2option_type[$co['option_name']] = $co['option_type'];
                    }
                    if (in_array($OptionName, $IntenseConnectionsOptions) || in_array($ComponentName, $IntenseConnectionsOptions)) {
                        if(isset($OptionName)){
                            $type_ident =  $option2option_type[$OptionName];
                        } else{
                            $type_ident =  $option2option_type[$ComponentName];
                        }
         
                        if($type_ident == 'patient_suppliers'){
                            
                            // get source data 
                            if(isset($OptionName)){
             
                                if($OptionName != 'FamilyDoctor'){
                                    // delete data in target - Alter SoftDelete to allow HARD DELETE
                                    $pph = new $OptionName();
                                    $pc_listener = $pph->getListener()->get('SoftdeleteListener');
                                    if($pc_listener){
                                        $pc_listener->setOption('hardDelete',true);
                                    }
                                    $dism = Doctrine::getTable($ComponentName)->findByIpid($share_info['target']); // this should also add in log
                                    
                                    if ($dism) {
                                        $dism->delete();
                                    }
                                    if($pc_listener){
                                        $pc_listener->setOption('hardDelete',false);
                                    }
                                    
                                    
                                } else{
                                    $p_master = Doctrine::getTable('PatientMaster')->findOneByIpid($share_info['target']);
                                    $p_master->familydoc_id = 0;
                                    $p_master->save();
                                }
                             
                                    
                                    
                                if (in_array($OptionName, array(
                                    'PatientPharmacy',
                                    'PatientPhysiotherapist',
                                    'PatientSupplies',
                                    'PatientSuppliers',
                                    'PatientHomecare',
                                    'PatientChurches',
                                    'PatientPflegedienste',
                                    'PatientSpecialists',
                                ))) {
                                    $opt_obj = new $OptionName();
                                    $opt_obj->clone_records($share_info['source'], $share_info['target'], $share_info['target_client']);
                                }
                                elseif($OptionName == 'FamilyDoctor'){
                                    
                                    
                                    $save_fam_doc_id = array();
                                    $record = Doctrine::getTable('PatientMaster')->findOneByIpid($share_info['source'], Doctrine_Core::HYDRATE_RECORD);
                                    
                                    if( ! empty($record)){
                                        $patient_details = $record->toArray();
                                        $save_fam_doc_id[$share_info['source']] = $patient_details['familydoc_id'];
                                        
                                        if($save_fam_doc_id[$share_info['source']] !=0){
                                            // Family Doctor copy
                                            $FamilyDoctor = new FamilyDoctor();
                                            $family_doctor = $FamilyDoctor->clone_record($save_fam_doc_id[$share_info['source']] , $share_info['target_client']);
                                            
                                            if($family_doctor){
                                                //Update PM with follownig data:
                                                $p_master = Doctrine::getTable('PatientMaster')->findOneByIpid($share_info['target']);
                                                $p_master->familydoc_id = $family_doctor;
                                                $p_master->save();
                                            }
                                        }
                                    }
                                    
                                }
                                elseif($OptionName == 'PatientHealthInsurance'){
                                    $PatientHealthInsurance = new PatientHealthInsurance();
                                    $PatientHealthInsurance->clone_record($share_info['source'], $share_info['target'], $share_info['target_client']);
                                } 
                                elseif($OptionName == 'PatientVoluntaryworkers'){
                                    //Ehrenamtliche
                                    $PatientVoluntaryworkers = new PatientVoluntaryworkers();
                                    $PatientVoluntaryworkers->clone_records($share_info['source'], $share_info['target'],$share_info['target_client']);
                                } 
                                elseif($OptionName == 'PatientHospiceassociation'){
                                    $PatientHospiceassociation = new PatientHospiceassociation();
                                    $PatientHospiceassociation->clone_records($share_info['source'], $share_info['target'],$share_info['target_client']);
                                } 
                                
                            } 
                            
                        }
                        else
                        {
                                if(array_key_exists('ipid',$obj_columns)){
                                    
                                    if($ComponentName == 'PatientMaintainanceStage'  || $ComponentName == 'ContactPersonMaster'){
            
                                        $pph = new $ComponentName();
                                        $pc_listener = $pph->getListener()->get('SoftdeleteListener');
                                        if($pc_listener){
                                            $pc_listener->setOption('hardDelete',true);
                                        }
                                        $dism = Doctrine::getTable($ComponentName)->findByIpid($share_info['target']); // this should also add in log
                                        
                                        if ($dism) {
                                            $dism->delete();
                                        }
                                        if($pc_listener){
                                            $pc_listener->setOption('hardDelete',false);
                                        }
                                    
                                    } else{
                                        
                                        // delete data in target
                                        $dism = Doctrine::getTable($ComponentName)->findByIpid($share_info['target']); // this should also add in log
                                        if ($dism) {
                                            $dism->delete();
                                        }
                                    }
                                    
                                    if($ComponentName == 'ContactPersonMaster'){
                                        
                                        $ContactPersonMaster = new ContactPersonMaster();
                                        $ContactPersonMaster->clone_records($share_info['source'], $share_info['target'],$share_info['source_client'], $share_info['target_client']);
                                        
                                    } else{
                                            
                                        // FOR VERSORGER - we need the relevant conection IDS - like locations
                                        $soure_data = Doctrine::getTable($ComponentName)->findByIpid($share_info['source']); // this should also add in log
                
                                        if ($soure_data) {
                                            $soure_data_array = $soure_data->toArray();
                                            $source_overwrite_data = array();
                                            
                                            $valid_soure_data_array = $soure_data_array;
                                            if(array_key_exists('isdelete',$obj_columns)){
                                                $valid_soure_data_array = array_filter($soure_data_array, function($var) {
                                                    return $var['isdelete'] == 0;
                                                });
                                            }
                                            
                                            foreach ($valid_soure_data_array as $s_key => $entries) {
                                                $source_overwrite_data[$s_key]['ipid'] = $share_info['target'];
                                                foreach ($entries as $column => $value) {
                                                    if (! in_array($column, array(
                                                        'id',
                                                        'ipid',
                                                        'change_date',
                                                        'change_user',
                                                        'status_period',
                                                        'clientid'
                                                    ))) {
                                                        
                                                        if(array_key_exists('clientid',$obj_columns)){
                                                            $source_overwrite_data[$s_key]['clientid'] = $share_info['target_client'];
                                                        }
                                                        
                                                        $source_overwrite_data[$s_key][$column] = $value;
                                                        
                                                    }
                                                }
                                            }
                                            
                                            $IntenseConnectionListener = null;
                                            $IntenseConnectionListener_disabled = false;
                                            if (($IntenseConnectionListener = Doctrine_Core::getTable($ComponentName)->getRecordListener()->get('IntenseConnectionListener'))) {
                                                $IntenseConnectionListener_disabled = $IntenseConnectionListener->getOption('disabled');
                                                $IntenseConnectionListener->setOption('disabled', true);
                                            }
                                            
                                            if (! empty($source_overwrite_data)) {
                                                $collection = new Doctrine_Collection($ComponentName);
                                                $collection->fromArray($source_overwrite_data);
                                                $collection->save();
                                            }
                                            
                                            if ($IntenseConnectionListener) {
                                                $IntenseConnectionListener->setOption('disabled', $IntenseConnectionListener_disabled);
                                            }
                                        }
                                    }
                            }
                        }
                        
                    }
                }
            }
        }
    }
    
    
    
    public function postInsert(Doctrine_Event $event)
    { 
        
        if (Zend_Controller_Front::getInstance()->getRequest()->getControllerName() == 'patient'
            && Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'patientmasteradd')
        {
            return;
        }
        
        $Invoker = $event->getInvoker();
        $ComponentName = $Invoker->getTable()->getComponentName();
 
        if(!empty ($Invoker->_modified )){
            $event->skipOperation();
        }
        
        // stamdatem and Versorger
        if (! empty($_REQUEST['__category'])) {
            $OptionName = $_REQUEST['__category'];
        }
        

        if($_REQUEST['__action'] == 'saveVersorger' && ! in_array($OptionName,array('PatientHealthInsurance','FamilyDoctor'))){
            return;
        }
        
        if($_REQUEST['__action'] != 'saveVersorger' && $ComponentName == "FamilyDoctor"){
            return;
        }

        
        if (! isset($_GET['id'])) {
            $ipid = $event->getInvoker()->ipid;
        } else {
            $pid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpid($pid);
        }
        
        $int_connection = new IntenseConnections();
        $share_direction = $int_connection->get_intense_connection_by_ipid($ipid);
        
        if(empty($share_direction)){
            return;
        }
        
        
        // Skip versorger: Allow only for special cases 
        if($_REQUEST['__action'] == 'saveVersorger' && ! in_array($OptionName,array('PatientHealthInsurance','FamilyDoctor'))){
            return;
        }
        
        foreach ($share_direction as $direction_k => $share_info) {
            if (! empty($share_info['intense_connection'])) {
                foreach ($share_info['intense_connection'] as $con => $con_ionfo) {
                    $IntenseConnectionsOptions = array_column($con_ionfo['IntenseConnectionsOptions'], 'option_name');
                    if (in_array($OptionName, $IntenseConnectionsOptions) || in_array($ComponentName, $IntenseConnectionsOptions)) {
                        
                        if($ComponentName == 'PatientMaintainanceStage' || $ComponentName == 'ContactPersonMaster'){
                            
                            $pph = new $ComponentName();
                            $pc_listener = $pph->getListener()->get('SoftdeleteListener');
                            if($pc_listener){
                                $pc_listener->setOption('hardDelete',true);
                            }
                            $dism = Doctrine::getTable($ComponentName)->findByIpid($share_info['target']); // this should also add in log
                            
                            if ($dism) {
                                $dism->delete();
                            }
                            if($pc_listener){
                                $pc_listener->setOption('hardDelete',false);
                            }
                            
                        } else{
                        
                            if($ComponentName != 'FamilyDoctor' ){
                                // delete data in target
                                $dism = Doctrine::getTable($ComponentName)->findByIpid($share_info['target']); // this should also add in log
                                if ($dism) {
                                    $delete_data = $dism->toArray();
                             
                                    if(!empty($delete_data)){
                                        foreach($delete_data as $kd=>$dd){
                                            $individual_dism = Doctrine::getTable($ComponentName)->findByIpidAndId($share_info['target'],$dd['id']); // this should also add in log
                                            if($individual_dism){
                                                $individual_dism->delete();
                                            }
                                        }
                                    }
                                }
                            }
                            
                            
                        }
                        
                        if($ComponentName == 'ContactPersonMaster'){
                            
                            $ContactPersonMaster = new ContactPersonMaster();
                            $ContactPersonMaster->clone_records($share_info['source'], $share_info['target'],$share_info['source_client'], $share_info['target_client']);
                            
                        } elseif($ComponentName == 'FamilyDoctor') {
                            
                            $save_fam_doc_id = array();
                            $record = Doctrine::getTable('PatientMaster')->findOneByIpid($share_info['source'], Doctrine_Core::HYDRATE_RECORD);
                            if( ! empty($record)){
                                $patient_details = $record->toArray();
                                $save_fam_doc_id[$share_info['source']] = $patient_details['familydoc_id'];
                            
                                // Family Doctor copy
                                $FamilyDoctor = new FamilyDoctor();
                                $family_doctor = $FamilyDoctor->clone_record($save_fam_doc_id[$share_info['source']] , $share_info['target_client']);

                                if($family_doctor){
                                    //Update PM with follownig data:
                                    $p_master = Doctrine::getTable('PatientMaster')->findOneByIpid($share_info['target']);
                                    $p_master->familydoc_id = $family_doctor;
                                    $p_master->save();
                                }
                            }
                            
                        } else { 
                            
                            $soure_data = Doctrine::getTable($ComponentName)->findByIpid($share_info['source']); // this should also add in log
                            if ($soure_data) {
                                foreach ($soure_data as $s_key => $entries) {
                                    $insert_obj = new $ComponentName();
                                    
                                    $IntenseConnectionListener = null;
                                    $IntenseConnectionListener_disabled = false;
                                    if (($IntenseConnectionListener = Doctrine_Core::getTable($ComponentName)->getRecordListener()->get('IntenseConnectionListener'))) {
                                        $IntenseConnectionListener_disabled = $IntenseConnectionListener->getOption('disabled');
                                        $IntenseConnectionListener->setOption('disabled', true);
                                    }
                                    
                                    
                                    $obj_columns = $insert_obj->getTable()->getColumns();
                                    $insert_obj->ipid = $share_info['target'];
                                    foreach ($entries as $column => $value) {
                                        if (! in_array($column, array('id','ipid','change_date','change_user','status_period','clientid'))) {
                                            $insert_obj->{$column} = $value;
                                        }
                                    }
                                    if(array_key_exists('clientid', $obj_columns)){
                                        $insert_obj->clientid = $share_info['target_client'];
                                    }
                                    $insert_obj->save();
                                    
                                    if ($IntenseConnectionListener) {
                                        $IntenseConnectionListener->setOption('disabled', $IntenseConnectionListener_disabled);
                                    }
                                }
                      
                            }
                        }
                    }
                }
            }
        }
        
        return;
        
    }
    
}

?>