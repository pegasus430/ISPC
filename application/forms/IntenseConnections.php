<?php
/**
 * @author Ancuta
 * ISPC-2614
 * 06.07.2020
 */
require_once ("Pms/Form.php");

class Application_Form_IntenseConnections extends Pms_Form
{

    private $triggerformid = IntenseConnections::TRIGGER_FORMID;

    private $triggerformname = IntenseConnections::TRIGGER_FORMNAME;

    public function validate($post)
    {
        //return true;

        
        $intense_options = Pms_CommonData::intense_connection_options();
        $all_lists_connections = ConnectionMasterTable::_find_all_lists_connections();
//         dd($post,$intense_options,$all_lists_connections);
//         dd($intense_options);
        $Tr = new Zend_View_Helper_Translate();

        $error = 0;

        // get connections between the selected clients 
        
        if (empty($post['parent'])) {
            $this->error_message["parent"] = $Tr->translate('Please select client #1');
            $error = 1;
        }
        if (empty($post['child'])) {
            $this->error_message["child"] = $Tr->translate('Please select client #2');
            $error = 2;
        }
        
        if (! empty($post['parent']) && !empty($post['child'])) {
            // check if clients are  already connected
            $existing_conntections = IntenseConnectionsTable::_find_intense_connectionBetweenClients($post['parent'],$post['child'],$post['connection_id']);
            if($existing_conntections){
                $this->error_message["connected_clients"] = $Tr->translate('The selected clients already have an intense connection set!');
                $error = 3;
            }
        }

        
        
        if (empty($post['connection_options'])) {
            $this->error_message["connection_options_err"] = $Tr->translate('Please select connection options ');
            $error = 4;
        }
        else
        {
            foreach($post['connection_options'] as  $ident_group_name => $option_ident ){
                    
                    foreach($option_ident as $kb => $block_name)
                    {
                        if(  empty ( $intense_options[$ident_group_name][$block_name]['master_lists'] ) ) {
                            
                        } elseif( ! empty ( $intense_options[$ident_group_name][$block_name]['master_lists'] ) ) {
                            if($block_name == 'PatientVoluntaryworkers' || $block_name == 'PatientHospizvizits'  || $block_name == 'PatientHospiceassociation' ){
                                // check if clients have connected voluntary workers 
                  
                                $qq = Doctrine_Query::create()
                                ->select('*')
                                ->from('VwGroupAssociatedClients')
                                ->whereIn('client',array($post['parent'],$post['child']))
                                ->andWhere('status="0"');
                                $res_subquery = $qq->fetchArray();
                                
                                $clients2group = array();
                                foreach ($res_subquery as $k => $grinfo) {
                                    $clients2group[$grinfo['group_id']]['all'][] = $grinfo['client'];
                                    if ($grinfo['parent'] == 1) {
                                        $clients2group[$grinfo['group_id']]['parent'] = $grinfo['client'];
                                    } else {
                                        $clients2group[$grinfo['group_id']]['childs'][] = $grinfo['client'];
                                    }
                                }
                                $shared_group = array();
                                foreach($clients2group as $gr_id =>$info){
                                    if(in_array($post['parent'],$info['all']) &&   in_array($post['child'],$info['all']) ){
                                        $shared_group[] = $gr_id ;
                                    }
                                }
                             
                                if(empty($shared_group)){
                                    $this->error_message["connection_options"][$ident_group_name][$block_name] = $Tr->translate('lists used for this connection, are not connected betweeen the selected clients  ').'[Voluntary_workers]';
                                    $error++;
                                }
                                
                                
                            } else{
                                foreach($intense_options[$ident_group_name][$block_name]['master_lists'] as $list_model){
                                    
                                    // if one is parent and the other is a child, or both are children
                                    if( ( !empty($all_lists_connections[$list_model]['parent2child'][$post['parent']]) && $all_lists_connections[$list_model]['parent2child'][$post['parent']] == $post['child'] )
                                        ||  ( !empty($all_lists_connections[$list_model]['child2parent'][$post['child']]) && $all_lists_connections[$list_model]['child2parent'][$post['child']] == $post['parent']) 
                                        ||  ( !empty($all_lists_connections[$list_model]['child2parent'][$post['parent']]) && $all_lists_connections[$list_model]['child2parent'][$post['parent']] == $post['child']) 
                                        || ( in_array($post['child'],$all_lists_connections[$list_model]['children']) && in_array($post['parent'],$all_lists_connections[$list_model]['children'])  )
                                        ){
                                        //list is connected  allow connection
                                    } else{
    //                                     $this->error_message["connection_options"][$ident_group_name][$block_name] .= $Tr->translate('lists used for this connection, are not connected betweeen the selected clients  ').'['.$list_model.']';
                                        $this->error_message["connection_options"][$ident_group_name][$block_name] = $Tr->translate('lists used for this connection, are not connected betweeen the selected clients  ').'['.$list_model.']';
                                        $error++;
                                    }
                                    
                                }
                            }
                            
                        }
                        
                    }
            }
 
        }
        
        if ($error == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function save_connection_details($post)
    {
        
        // PREAPARE POST
        $data = array();
        if(!empty($post['parent']) && !empty($post['child'])){
            // parent
            $data['IntenseConnectionsClients'][] =array(
//                 'intense_connection_id' => $connection_id,
                'clientid' => $post['parent'],
                'connection_parent' => 'yes'
            );
            // child
            $data['IntenseConnectionsClients'][] =array(
//                 'intense_connection_id' => $connection_id,
                'clientid' => $post['child'],
                'connection_parent' => 'no'
            );
            
            /* if( ! empty($clients)){
             $collection = new Doctrine_Collection(IntenseConnectionsClients);
             $collection->fromArray($clients);
             $collection->save();
             } */
        }
        if(!empty($post['connection_options']) ) {
            
            foreach($post['connection_options'] as $opt_type=>$options){
                foreach($options as $option_name){
                    $data['IntenseConnectionsOptions'][] = array(
//                         'intense_connection_id' => $connection_id,
                        'option_name' =>$option_name,
                        'option_type' =>$opt_type
                    );
                    
                }
            }
        }
        
        $intense_connection_id = null;
        if (!empty($post['connection_id'])) {
            $intense_connection_id = $post['connection_id'];
        }

        IntenseConnectionsTable::getInstance()->findOrCreateOneBy('id', $intense_connection_id, $data);
         
    }
    
}
?>