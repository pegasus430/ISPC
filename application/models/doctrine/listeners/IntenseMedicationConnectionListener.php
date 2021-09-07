<?php
/**
 * ISPC-2614  // Maria:: Migration ISPC to CISPC 08.08.2020
 * @author  Jul 20, 2020  ancuta
 *
 */
class IntenseMedicationConnectionListener extends Doctrine_Record_Listener
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
        $Invoker = $event->getInvoker();
        $ComponentName = $Invoker->getTable()->getComponentName();

        if (! isset($_GET['id'])) {
            $ipid = $event->getInvoker()->ipid;
        } else {
            $pid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpid($pid);
        }
        $int_connection = new IntenseConnections();
        $share_direction = $int_connection->get_intense_connection_by_ipid($ipid);
        
        foreach ($share_direction as $direction_k => $share_info) {
            if (! empty($share_info['intense_connection'])) {
                
                foreach ($share_info['intense_connection'] as $con => $con_ionfo) {
                    $IntenseConnectionsOptions = array_column($con_ionfo['IntenseConnectionsOptions'], 'option_name');
                    
                    if (in_array($ComponentName, $IntenseConnectionsOptions)) {

                        // delete data in target  
                        $dism = Doctrine::getTable($ComponentName)->findByIpid($share_info['target']); // this should also add in log
                        if ($dism) {
                            $dism->delete();
                        }
                        
                        if ($ComponentName == 'PatientDrugPlan') {
                            $PatientDrugPlan = new PatientDrugPlan();
                            $PatientDrugPlan->clone_records($share_info['source'], $share_info['target'], $share_info['target_client'], $share_info['source_client']);
                        }
                        
                    }
                }
            }
        }
    }
    
    
    public function postInsert(Doctrine_Event $event)
    { 
        $Invoker = $event->getInvoker();
        $ComponentName = $Invoker->getTable()->getComponentName();
        
        if(!empty ($Invoker->_modified )){
            $event->skipOperation();
        }
     
        if (! isset($_GET['id'])) {
            
            $ipid = $event->getInvoker()->ipid;
        } else {
            
            $pid = Pms_Uuid::decrypt($_GET['id']);
            $ipid = Pms_CommonData::getIpid($pid);
        }
        
        $int_connection = new IntenseConnections();
        $share_direction = $int_connection->get_intense_connection_by_ipid($ipid);
        
        foreach ($share_direction as $direction_k => $share_info) {
            if (! empty($share_info['intense_connection'])) {
                foreach ($share_info['intense_connection'] as $con => $con_ionfo) {
                    $IntenseConnectionsOptions = array_column($con_ionfo['IntenseConnectionsOptions'], 'option_name');
                    if ( in_array($ComponentName, $IntenseConnectionsOptions)) {
                        
                        // delete data in target
                        $dism = Doctrine::getTable($ComponentName)->findByIpid($share_info['target']); // this should also add in log
                        if ($dism) {
                            $dism->delete();
                        }
                        
                        if ($ComponentName == 'PatientDrugPlan') {
                            $PatientDrugPlan = new PatientDrugPlan();
                            $PatientDrugPlan->clone_records($share_info['source'], $share_info['target'], $share_info['target_client'], $share_info['source_client']);
                        }
                    }
                }
            }
        }
        
        return;
        
    }
    
}

?>