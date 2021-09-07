<?php
// Maria:: Migration ISPC to CISPC 08.08.2020
class IntenseConnectionAdmissionsListener extends Doctrine_Record_Listener
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
//         if($ComponentName == 'PatientDischarge'){
//             dd($Invoker->data);
//         }
        $data = array();
        $data[] = array(
            'ipid' =>$Invoker->ipid,
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
    }
 
    
    
    public function preUpdate(Doctrine_Event $event)
    {
 

    }
    public function postUpdate(Doctrine_Event $event)
    {
        return;
        /* 
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
                    if (in_array('patient_falls', $IntenseConnectionsOptions) &&  in_array($ComponentName,array('PatientActive','PatientStandby','PatientStandbyDetails','PatientStandbyDelete','PatientStandbyDeleteDetails','PatientReadmission'))) {
                       // $patient_master->intense_connection_patient_admissions($share_info['source'], $share_info['target']);
                       
                        // delete data in target
                        $dism = Doctrine::getTable($ComponentName)->findByIpid($share_info['target']); // this should also add in log
                        if ($dism) {
                            $dism->delete();
                        }
                        
                        // FOR VERSORGER - we need the relevant conection IDS - like locations
                        $soure_data = Doctrine::getTable($ComponentName)->findByIpid($share_info['source']); // this should also add in log
                        if ($soure_data) {
                            $source_overwrite_data = array();
                            foreach ($soure_data as $s_key => $entries) {
                                $source_overwrite_data[$s_key]['ipid'] = $share_info['target'];
                                foreach ($entries as $column => $value) {
                                    if (! in_array($column, array(
                                        'id',
                                        'ipid',
                                        'change_date',
                                        'change_user',
                                        'status_period'
                                    ))) {
                                        $source_overwrite_data[$s_key][$column] = $value;
                                    }
                                }
                            }
                            
                            if (! empty($source_overwrite_data)) {
                                $collection = new Doctrine_Collection($ComponentName);
                                $collection->fromArray($source_overwrite_data);
                                $collection->save();
                            }
                        }
                        
                    }
                }
            }
        } */
    }
    
    
    public function preInsert(Doctrine_Event $event)
    { 
        return;
        
    }
    
    public function postInsert(Doctrine_Event $event)
    { 
        return;
        
    }
    
}

?>