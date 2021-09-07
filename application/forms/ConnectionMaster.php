<?php
/**
 * @author Ancuta
 * ISPC-2612
 */
require_once ("Pms/Form.php");

class Application_Form_ConnectionMaster extends Pms_Form
{

    private $triggerformid = ConnectionMaster::TRIGGER_FORMID;

    private $triggerformname = ConnectionMaster::TRIGGER_FORMNAME;

    public function validate($post)
    {
        //return true;

        $Tr = new Zend_View_Helper_Translate();

        $error = 0;

        if (empty($post['clientid'])) {
            $this->error_message["clientid"] = $Tr->translate('the parent must be set for this connection');
            $error = 1;
        }

        
        if (empty($post['ConnectionFollowersClients'])) {
            $this->error_message["ConnectionFollowersClients"] = $Tr->translate('Please select following clients for current connection');
            $error = 2;
        }
        else{
            
            foreach($post['ConnectionFollowersClients'] as $k=>$follower){
                if( $post['clientid'] ==$follower){
                    unset($post['ConnectionFollowersClients'][$k]);
                }
            }
            
            if (empty($post['ConnectionFollowersClients'])) {
                $this->error_message["ConnectionFollowersClientsParent"] = $Tr->translate('Please select following clients for current connection - different from PARENT');
                $error = 2.1;
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
        if (  ! empty($post['ConnectionFollowersClients'])) {
            $ccf = 0 ;
            foreach($post['ConnectionFollowersClients'] as $f_clientid){
                if( $f_clientid != $post['clientid']){ //Parrent it is not allowed to be also a child
                    $post['ConnectionFollowers'][$ccf]['clientid'] = $f_clientid;
                    $ccf++;
                }
            }
        }
        
        $params = array();
        if(isset($post['list_category'])){
            $params['list_category'] = $post['list_category'];
        }
        
        if (empty($post['connection_id'])) {
            // NEW CONNECTION
            $new_conection = ConnectionMasterTable::getInstance()->findOrCreateOneBy('id', null, $post);
            $connection_id = $new_conection->id;

            
            // PARAMS FOR SYNC            
            $params['list_type'] = $post['list_type'];
            $params['connection_id'] = $connection_id;
            $params['parent'] = $post['clientid'];
            $params['followers'] = $post['ConnectionFollowersClients'];
            
            $this->bulk_connection_sync($params);
                            
        } else {
            
            $params['connection_id'] = $post['connection_id'];
            $params['list_type'] = $post['list_type'];
            
            
            // first get existing info for this connection - to see what has changed
            if($params['list_category']){
                $existing_connection= ConnectionMasterTable::_find_connection_details($post['list_type'],$post['connection_id'],$params['list_category']);
            } else{
                $existing_connection= ConnectionMasterTable::_find_connection_details($post['list_type'],$post['connection_id']);
            }
            
            $existing_parent = $existing_connection[$post['connection_id']]['master_client'];
            $existing_followers = array_column($existing_connection[$post['connection_id']]['ConnectionFollowers'],'clientid');
            
            
            
            
            // update ConnectionFollowers
            $q = Doctrine_Query::create()
            ->update('ConnectionFollowers')
            ->set('isdelete','1')
            ->where("connection_id= ?", $params['connection_id']);
            $q->execute();
            
            //Update Connection Master
            ConnectionMasterTable::getInstance()->findOrCreateOneBy('id', $params['connection_id'], $post);
            
            
            
            
            // If parent has changed
            if($existing_parent != $post['clientid']){
                
                // revert all  followers
                $params['parent'] = $existing_parent;
                $params['followers'] = $existing_followers;
                $revert_result = $this->bulk_connection_revert($params);
                
                if($revert_result) {
                    // re-sync with new Info
                    $params['parent'] = $post['clientid'];
                    $params['followers'] = $post['ConnectionFollowersClients'];
                    
                    $this->bulk_connection_sync($params);
                    
                } else {
                    // something happened!
                }
            }
            else
            {
                // if an OLD follower was removed
                // old followers must be reverted
                $revert_followers = array();
                $revert_followers = array_diff($existing_followers, $post['ConnectionFollowersClients']);
                
                if(!empty($revert_followers)){
                    
                    // revert connections
                    $params['parent'] = $post['clientid'];
                    $params['followers'] = $revert_followers;
                    $this->bulk_connection_revert($params);
                }
                
                // if a NEW followers were added sync only new
                $new_followers = array_diff($post['ConnectionFollowersClients'],$existing_followers);
                
                if(!empty($new_followers)){
                    
                    $params['parent'] = $post['clientid'];
                    $params['followers'] = $new_followers;
                    $this->bulk_connection_sync($params);
                }
                
            }
        }

    }

    public function bulk_medi_connection_sync($params = array())
    {
        if (empty($params)) {
            return false;
        }
        
        if($params['list_type'] != 'MedicationsSetsList'){
            return;
        }
        
        // get lists details 
        $lists_details = Pms_CommonData::connection_lists();
        
        $list_model = $params['list_type'];
        $list_info = $lists_details[$list_model];
        $client_column = isset($list_info['client_column']) && !empty($list_info['client_column']) ? $list_info['client_column'] : 'clientid';
        $isdelete_column  = isset($list_info['isdelete_column']) && !empty($list_info['isdelete_column']) ? $list_info['isdelete_column'] : 'isdelete';
        $list_ident_column = isset($list_info['list_ident_column']) && !empty($list_info['list_ident_column']) ? $list_info['list_ident_column'] : '';
        $list_ident_value = isset($list_info['list_ident_value']) && strlen($list_info['list_ident_value'])>0 ? $list_info['list_ident_value'] : '0';
        $except_columns_array = isset($list_info['except_columns']) && !empty($list_info['except_columns']) ? $list_info['except_columns'] : array('id','clientid','create_user','change_user','create_date','change_date');

        $special_identify_column = isset($list_info['special_identify_column']) && strlen($list_info['special_identify_column'])>0 ? $list_info['special_identify_column'] : '';
        
        
 

        // Maria:: Migration ISPC to CISPC 08.08.2020
        // FIRST! 
        // hide data in followers
        $update_followers = Doctrine_Query::create()
        ->update('MedicationsSetsList')
        ->set('connection_id',"?", $params['connection_id'])
        ->whereIn('`'.$client_column.'`',$params['followers'])
        ->andWhere('`'.$isdelete_column.'` = ?',0);
        $update_followers->execute();
        
        
        // SECOND: get  master data 
        $parent_data_sql = Doctrine_Query::create()
        ->select("mi.*,msi.*")
        ->from('MedicationsSetsList as  mi')
        ->leftJoin('mi.MedicationsSetsItems msi')
        ->where('mi.isdelete = ?', 0)
        ->andWhere('mi.clientid= ?', $params['parent'] )
        ->andWhere('mi.connection_id is null' )
        ->andWhere('mi.master_id is null' );
        $parent_data_array = $parent_data_sql->fetchArray();
        
        
        // check if parent info exists in follower 
        //MedicationIndications
        
        $followers_indication = Doctrine_Query::create()
        ->select('*')
        ->from('MedicationIndications')
        ->fetchArray();
        $indication2follower2master_id = array();
        $indication_info = array();
        foreach($followers_indication as $k=>$mv){
            if(in_array($mv['clientid'],$params['followers']) && $mv['master_id'] !=  null ){
                $indication2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
            } elseif($mv['clientid'] == $params['parent']){
                $indication_info[$mv['clientid']] [$mv['id']] = $mv;
            }
        }

        
        // Medication_unit
        $followers_units = Doctrine_Query::create()
        ->select('*')
        ->from('MedicationUnit')
        ->fetchArray();
        
        
        $units2follower2master_id = array();
        $unit_info = array();
        foreach($followers_units as $k=>$mv){
            
            if(in_array($mv['clientid'],$params['followers']) && $mv['master_id'] !=  null ){
                $units2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
            } elseif($mv['clientid'] == $params['parent']){
                $unit_info[$mv['clientid']] [$mv['id']] = $mv;
            }
            
        }
        
        // Medication_type
        $mt = new MedicationType();
        $mt_columns = $mt->getTable()->getColumns();
        $followers_types = Doctrine_Query::create()
        ->select('*')
        ->from('MedicationType')
//         ->whereIn('clientid',$params['followers'])
//         ->andWhere('master_id is not null')
        ->fetchArray();
        
        
        $types2follower2master_id = array();
        $types_info = array();
        foreach($followers_types as $k=>$mv){
            
            if(in_array($mv['clientid'],$params['followers']) && $mv['master_id'] !=  null ){
                $types2follower2master_id  [$mv['clientid']] [$mv['master_id']] = $mv['id'];
            } elseif($mv['clientid'] == $params['parent']){
                $types_info[$mv['clientid']] [$mv['id']] = $mv;
            }
        }
        
        
        // Medication_type
        $mf = new MedicationFrequency();
        $mf_columns = $mf->getTable()->getColumns();
        $followers_freq = Doctrine_Query::create()
        ->select('*')
        ->from('MedicationFrequency')
        ->fetchArray();
        
        $freq2follower2master_id = array();
        $freq_info = array();
        foreach($followers_freq as $k=>$mv){
            if(in_array($mv['clientid'],$params['followers']) && $mv['master_id'] !=  null ){
                $freq2follower2master_id  [$mv['clientid']] [$mv['master_id']] = $mv['id'];
            } elseif($mv['clientid'] == $params['parent']){
                $freq_info[$mv['clientid']] [$mv['id']] = $mv;
            }
        }

        
        
        
        // Medication_dosage_form
        $df = new MedicationDosageform();
        $df_columns = $df->getTable()->getColumns();
        
        $followers_dosage = Doctrine_Query::create()
        ->select('*')
        ->from('MedicationDosageform')
        ->fetchArray();
        
        $dosagesforms2follower2master_id = array();
        $dosagef_info = array();
        
        foreach($followers_dosage as $k=>$mv)
        {
            if(in_array($mv['clientid'],$params['followers']) && $mv['master_id'] !=  null ){
                $dosagesforms2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
            } elseif($mv['clientid'] == $params['parent']){
                $dosagef_info[$mv['clientid']] [$mv['id']] = $mv;
            }
        }
        
 
        $entity_pc = new MedicationsSetsList();
        $sets_columns = $entity_pc->getTable()->getColumns();
        
        
        $entity_items = new MedicationsSetsItems();
        $sets_items_columns = $entity_items->getTable()->getColumns();
        
        
        $entity_med = new Medication();
        $med_columns = $entity_med->getTable()->getColumns();
        
        // Third: Copy data to follower
        foreach($parent_data_array as $setk=>$set){
            
            foreach($params['followers'] as $f_cleint_id)
            {
                // insert set 
                $insert_set = new MedicationsSetsList();
                foreach($sets_columns as $column_name => $cinfo){
                    if(!in_array($column_name,$except_columns_array)){
                        if($column_name == 'set_indication'){
                            $insert_set->{$column_name} = $indication2follower2master_id [$f_cleint_id] [$set['set_indication']];
                        } else{
                            $insert_set->{$column_name} = $set[$column_name];
                        }
                    }
                }
                $insert_set->clientid = $f_cleint_id;
                $insert_set->connection_id = $params['connection_id'];
                $insert_set->master_id = $set['id'];
                $insert_set->save();
                $set_id = $insert_set->id;
                
                
                $old_medi2new = array();
                if(!empty($set['MedicationsSetsItems'])){
                    foreach($set['MedicationsSetsItems'] as $item_key =>$item_Data) {
                        // first insert medications
                        $medication_id = $item_Data['medication_id'];
                        
                        if ($item_Data['medication_id']) {
                            $medcaarray = array();
                            $medca = Doctrine::getTable('Medication')->find($item_Data['medication_id']);
                            if (! empty($medca)) {
                                $medcaarray = $medca->toArray();
                                if ($medcaarray) {
                                    $imed = new Medication();
                                    foreach ($med_columns as $cname => $cdata) {
                                        if (! in_array($cname, $except_columns_array)) {
                                            $imed->{$cname} = $medcaarray[$cname];
                                        }
                                    }
                                    $imed->clientid = $f_cleint_id;
                                    $imed->connection_id = $params['connection_id'];
                                    $imed->master_id = $medcaarray['id'];
                                    $imed->save();

                                    $medication_id = $imed->id;
                                }
                            }
                            $old_medi2new[$item_Data['medication_id']] = $medication_id;
                        }
                        
                        // insert  dosage_form
                        if ($item_Data['med_dosage_form']) {
                            // get info for this dosage info, if already on 
                            foreach($item_Data['med_dosage_form'] as $kf=>$dosagef_id){
                                // Check if value NOT in followe 
                                if( empty($dosagesforms2follower2master_id[$f_cleint_id][$dosagef_id] )){

                                    
                                    //insert in follower and get id
                                    $insert_df = new MedicationDosageform();
                                    foreach($df_columns as $cn=>$ci){
                                        if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                            $insert_df->{$cn} = $dosagef_info[$params['parent']] [$dosagef_id] [$cn];
                                        }
                                    }
                                    $insert_df->clientid = $f_cleint_id;
                                    $insert_df->extra  = 1;
                                    $insert_df->save();
                                    $new_df_id = $insert_df->id;
                                    
                                    /// remove entry
                                    unset($item_Data['med_dosage_form'][$kf]);
                                    // add new  df added in follower
                                    $item_Data['med_dosage_form'][] =$new_df_id; 
                                    
                                } else{
                                    
                                    // remove entry
                                    unset($item_Data['med_dosage_form'][$kf]);
                                     // insert in array, the coresponding  entry from follower 
                                    $item_Data['med_dosage_form'][] = $dosagesforms2follower2master_id[$f_cleint_id][$dosagef_id]; // add new  df added in follower
                                }
                            }
                        }
                        
                        if ($item_Data['type']) {
                            // get info for this dosage info, if already on 
                            foreach($item_Data['type'] as $kf=>$stype_id){
                                // Check if value NOT in followe 
                                if( empty($types2follower2master_id[$f_cleint_id][$stype_id] )){

                                    
                                    //insert in follower and get id
                                    $insert_t = new MedicationType();
                                    foreach($mt_columns as $cn=>$ci){
                                        if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                            $insert_t->{$cn} = $types_info[$params['parent']] [$stype_id] [$cn];
                                        }
                                    }
                                    $insert_t->clientid = $f_cleint_id;
                                    $insert_t->extra  = 1;
                                    $insert_t->save();
                                    $new_t_id = $insert_t->id;
                                    
                                    /// remove entry
                                    unset($item_Data['type'][$kf]);
                                    // add new  df added in follower
                                    $item_Data['type'][] =$new_t_id; 
                                    
                                } else{
                                    
                                    // remove entry
                                    unset($item_Data['type'][$kf]);
                                     // insert in array, the coresponding  entry from follower 
                                    $item_Data['type'][] = $types2follower2master_id[$f_cleint_id][$stype_id]; // add new  df added in follower
                                }
                            }
                        }
                        
                        
                        
                        if ($item_Data['frequency']) {
                            // get info for this dosage info, if already on 
                            foreach($item_Data['frequency'] as $kff=>$freq_id){
                                // Check if value NOT in followe 
                                if( empty($freq2follower2master_id[$f_cleint_id][$freq_id] )){

                                    //insert in follower and get id
                                    $insert_f = new MedicationFrequency();
                                    foreach($mf_columns as $cn=>$ci){
                                        if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                            $insert_f->{$cn} = $freq_info[$params['parent']] [$freq_id] [$cn];
                                        }
                                    }
                                    $insert_f->clientid = $f_cleint_id;
                                    $insert_f->extra  = 1;
                                    $insert_f->save();
                                    $new_f_id = $insert_f->id;
                                    
                                    /// remove entry
                                    unset($item_Data['frequency'][$kff]);
                                    // add new  df added in follower
                                    $item_Data['frequency'][] = $new_f_id; 
                                    
                                } else{
                                    
                                    // remove entry
                                    unset($item_Data['frequency'][$kff]);
                                     // insert in array, the coresponding  entry from follower 
                                    $item_Data['frequency'][] = $freq2follower2master_id[$f_cleint_id][$freq_id]; // add new  df added in follower
                                }
                            }
                        }
                        
                        // Insert Medication items 
                        $insert_set_item = new MedicationsSetsItems();
                        foreach($sets_items_columns as $column_item_name => $cinfo){
                            if(!in_array($column_item_name,$except_columns_array)){
                                if($column_item_name=='medication_id'){
                                    $insert_set_item->medication_id = $medication_id;
                                } else{
                                    $insert_set_item->{$column_item_name} = $item_Data[$column_item_name];
                                }
                            }
                        }
                        $insert_set_item->bid = $set_id;
                        $insert_set_item->connection_id = $params['connection_id'];
                        $insert_set_item->master_id = $set['id'];
                        $insert_set_item->save();
                        
                    }
                }
                
                
                
                
                
                // insert dosage for each set and medication
                $dosage_insert = array();
                
                // for each set - get medication dosage
                $dparent_data_sql = Doctrine_Query::create()
                ->select("*")
                ->from('MedicationsSetsItemsDosage')
                ->where('isdelete = ?', 0)
                ->andWhere('bid_id = ?', $set['id'] );
                $set_dosages = $dparent_data_sql->fetchArray();
                
                $dosages2set2medication = array();
                foreach($set_dosages as $k=>$set_Data){
                    $dosages2set2medication[$set_Data['bid_id']] [$set_Data['medication_id']][] = $set_Data;
                }
                
                if(!empty($dosages2set2medication[$set['id']])){
                    foreach($dosages2set2medication[$set['id']] as $old_medication_id => $dosage_data){
                        foreach($dosage_data as $k=>$dds){
                            $dosage_insert[] = array(
                                'bid_id' =>$set_id, // new set id 
                                'medication_id' =>$old_medi2new[$dds['medication_id']], // new set id 
                                'dosage' =>$dds['dosage'], 
                                'dosage_time_interval' =>$dds['dosage_time_interval'] 
                            );
                        }
                        
                    }
                    if(!empty($dosage_insert)){
                        $collection = new Doctrine_Collection(MedicationsSetsItemsDosage);
                        $collection->fromArray($dosage_insert);
                        $collection->save();
                    }
                }
            }
        }
    }
    

    public function bulk_medi_bedarf_connection_sync($params = array())
    {
        if (empty($params)) {
            return false;
        }
        
        if($params['list_type'] != 'BedarfsmedicationMaster'){
            return;
        }
        
        // get lists details 
        $lists_details = Pms_CommonData::connection_lists();
        
        $list_model = $params['list_type'];
        $list_info = $lists_details[$list_model];
        $client_column = isset($list_info['client_column']) && !empty($list_info['client_column']) ? $list_info['client_column'] : 'clientid';
        $isdelete_column  = isset($list_info['isdelete_column']) && !empty($list_info['isdelete_column']) ? $list_info['isdelete_column'] : 'isdelete';
        $list_ident_column = isset($list_info['list_ident_column']) && !empty($list_info['list_ident_column']) ? $list_info['list_ident_column'] : '';
        $list_ident_value = isset($list_info['list_ident_value']) && strlen($list_info['list_ident_value'])>0 ? $list_info['list_ident_value'] : '0';
        $except_columns_array = isset($list_info['except_columns']) && !empty($list_info['except_columns']) ? $list_info['except_columns'] : array('id','clientid','create_user','change_user','create_date','change_date');

        $special_identify_column = isset($list_info['special_identify_column']) && strlen($list_info['special_identify_column'])>0 ? $list_info['special_identify_column'] : '';
        
        
 

        
        // FIRST! 
        // hide data in followers
        $update_followers = Doctrine_Query::create()
        ->update($list_model)
        ->set('connection_id',"?", $params['connection_id'])
        ->whereIn('`'.$client_column.'`',$params['followers'])
        ->andWhere('`'.$isdelete_column.'` = ?',0);
        $update_followers->execute();
        
        
        //get master data
        $parent_data_sql = Doctrine_Query::create()
        ->select("*")
        ->from($list_model)
        ->where('`'.$isdelete_column.'` = ?', 0)
        ->andWhere('`'.$client_column.'`= ?', $params['parent'] )
        ->andWhere('connection_id is null' )
        ->andWhere('master_id is null' );
        $parent_data_array = $parent_data_sql->fetchArray();
        
        ///for  each bid, get medications liste
        
        
        $bids = array_column($parent_data_array,'id');
        $medications_lists = Doctrine_Query::create()
        ->select("*")
        ->from('Bedarfsmedication')
        ->where('isdelete = ?', 0)
        ->andWhereIn('bid',$bids)
        ->fetchArray();
        
        $master_bids_medications = array();
        if(!empty($medications_lists)){
            foreach($medications_lists as $bmed => $bmed_data){
                $master_bids_medications[$bmed_data['bid']][] = $bmed_data;
            }
        }
        
        foreach($parent_data_array as $k=>$bid_data){
            $parent_data_array[$k]['Bedarfmedication'] = $master_bids_medications[$bid_data['id']];
        }
        
        // check if parent info exists in follower
        //MedicationIndications
        
        $mi = new MedicationIndications();
        $mi_columns = $mi->getTable()->getColumns();
        $followers_indication = Doctrine_Query::create()
        ->select('*')
        ->from('MedicationIndications')
        ->fetchArray();
        $indication2follower2master_id = array();
        $indication_info = array();
        foreach($followers_indication as $k=>$mv){
            if(in_array($mv['clientid'],$params['followers']) && $mv['master_id'] !=  null ){
                $indication2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
            } elseif($mv['clientid'] == $params['parent']){
                $indication_info[$mv['clientid']] [$mv['id']] = $mv;
            }
        }
        
        
        // Medication_unit
        $mu = new MedicationUnit();
        $mu_columns = $mu->getTable()->getColumns();
        $followers_units = Doctrine_Query::create()
        ->select('*')
        ->from('MedicationUnit')
        ->fetchArray();
        
        
        $units2follower2master_id = array();
        $unit_info = array();
        foreach($followers_units as $k=>$mv){
            
            if(in_array($mv['clientid'],$params['followers']) && $mv['master_id'] !=  null ){
                $units2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
            } elseif($mv['clientid'] == $params['parent']){
                $unit_info[$mv['clientid']] [$mv['id']] = $mv;
            }
            
        }
        
        // Medication_type
        $mt = new MedicationType();
        $mt_columns = $mt->getTable()->getColumns();
        $followers_types = Doctrine_Query::create()
        ->select('*')
        ->from('MedicationType')
        //         ->whereIn('clientid',$params['followers'])
        //         ->andWhere('master_id is not null')
        ->fetchArray();
        
        
        $types2follower2master_id = array();
        $types_info = array();
        foreach($followers_types as $k=>$mv){
            
            if(in_array($mv['clientid'],$params['followers']) && $mv['master_id'] !=  null ){
                $types2follower2master_id  [$mv['clientid']] [$mv['master_id']] = $mv['id'];
            } elseif($mv['clientid'] == $params['parent']){
                $types_info[$mv['clientid']] [$mv['id']] = $mv;
            }
        }
        
        
        // Medication_f
        $mf = new MedicationFrequency();
        $mf_columns = $mf->getTable()->getColumns();
        $followers_freq = Doctrine_Query::create()
        ->select('*')
        ->from('MedicationFrequency')
        ->fetchArray();
        
        $freq2follower2master_id = array();
        $freq_info = array();
        foreach($followers_freq as $k=>$mv){
            if(in_array($mv['clientid'],$params['followers']) && $mv['master_id'] !=  null ){
                $freq2follower2master_id  [$mv['clientid']] [$mv['master_id']] = $mv['id'];
            } elseif($mv['clientid'] == $params['parent']){
                $freq_info[$mv['clientid']] [$mv['id']] = $mv;
            }
        }
        
        
        
        
        // Medication_dosage_form
        $df = new MedicationDosageform();
        $df_columns = $df->getTable()->getColumns();
        
        $followers_dosage = Doctrine_Query::create()
        ->select('*')
        ->from('MedicationDosageform')
        ->fetchArray();
        
        $dosagesforms2follower2master_id = array();
        $dosagef_info = array();
        
        foreach($followers_dosage as $k=>$mv)
        {
            if(in_array($mv['clientid'],$params['followers']) && $mv['master_id'] !=  null ){
                $dosagesforms2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
            } elseif($mv['clientid'] == $params['parent']){
                $dosagef_info[$mv['clientid']] [$mv['id']] = $mv;
            }
        }
        
        
        $entity_med = new Medication();
        $med_columns = $entity_med->getTable()->getColumns();
        
        $entity_bmed = new Bedarfsmedication();
        $bmed_columns = $entity_bmed->getTable()->getColumns();
        
        $entity_bmedm = new BedarfsmedicationMaster();
        $bmeds_columns = $entity_bmedm->getTable()->getColumns();
        
        
        
        
        
        // Third: Copy data to follower
        foreach($parent_data_array as $setk=>$set){
            
            foreach($params['followers'] as $f_cleint_id)
            {
                
                // insert set
                $insert_set = new BedarfsmedicationMaster();
                foreach($bmeds_columns as $column_name => $cinfo){
                    if(!in_array($column_name,$except_columns_array)){
                       $insert_set->{$column_name} = $set[$column_name];
                    }
                }
                $insert_set->clientid = $f_cleint_id;
                $insert_set->connection_id = $params['connection_id'];
                $insert_set->master_id = $set['id'];
                $insert_set->save();
                $set_id = $insert_set->id;
                
                
                if(!empty($set['Bedarfmedication'])){
                    foreach($set['Bedarfmedication'] as $item_key =>$item_Data) {
                        // first insert medications
                        $medication_id = $item_Data['medication_id'];
                        
                        if ($item_Data['medication_id']) {
                            $medcaarray = array();
                            $medca = Doctrine::getTable('Medication')->find($item_Data['medication_id']);
                            if (! empty($medca)) {
                                $medcaarray = $medca->toArray();
                                if ($medcaarray) {
                                    $imed = new Medication();
                                    foreach ($med_columns as $cname => $cdata) {
                                        if (! in_array($cname, $except_columns_array)) {
                                            $imed->{$cname} = $medcaarray[$cname];
                                        }
                                    }
                                    $imed->clientid = $f_cleint_id;
                                    $imed->connection_id = $params['connection_id'];
                                    $imed->master_id = $medcaarray['id'];
                                    $imed->save();
                                    
                                    $medication_id = $imed->id;
                                }
                            }
                        }
                        
                        
                        
                        // insert  dosage_form
                        if ($item_Data['dosage_form']) {
                            // Check if value NOT in followe
                            if( empty($dosagesforms2follower2master_id[$f_cleint_id][$item_Data['dosage_form']] )){
                                
                                
                                //insert in follower and get id
                                $insert_df = new MedicationDosageform();
                                foreach($df_columns as $cn=>$ci){
                                    if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                        $insert_df->{$cn} = $dosagef_info[$params['parent']] [$item_Data['dosage_form']] [$cn];
                                    }
                                }
                                $insert_df->clientid = $f_cleint_id;
                                $insert_df->extra  = 1;
                                $insert_df->save();
                                $new_df_id = $insert_df->id;
                                
                      
                                $item_Data['dosage_form'] =$new_df_id;
                                
                            } else{
                                // insert in array, the coresponding  entry from follower
                                $item_Data['dosage_form'] = $dosagesforms2follower2master_id[$f_cleint_id][$item_Data['dosage_form']]; // add new  df added in follower
                            }
                        }
                        
                        // insert  unit
                        if ($item_Data['unit']) {
                            // Check if value NOT in followe
                            if( empty($units2follower2master_id[$f_cleint_id][$item_Data['unit']] )){
                                
                                
                                //insert in follower and get id
                                $insert_df = new MedicationUnit();
                                foreach($mu_columns as $cn=>$ci){
                                    if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                        $insert_df->{$cn} = $unit_info[$params['parent']] [$item_Data['unit']] [$cn];
                                    }
                                }
                                $insert_df->clientid = $f_cleint_id;
                                $insert_df->extra  = 1;
                                $insert_df->save();
                                $new_df_id = $insert_df->id;
                                
                      
                                $item_Data['unit'] =$new_df_id;
                                
                            } else{
                                // insert in array, the coresponding  entry from follower
                                $item_Data['unit'] = $units2follower2master_id[$f_cleint_id][$item_Data['unit']]; // add new  df added in follower
                            }
                        }
                        
                        
                        if ($item_Data['type']) {
                            // get info for this dosage info, if already on
                            // Check if value NOT in followe
                            if( empty($types2follower2master_id[$f_cleint_id][$item_Data['type']] )){
                                
                                
                                //insert in follower and get id
                                $insert_t = new MedicationType();
                                foreach($mt_columns as $cn=>$ci){
                                    if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                        $insert_t->{$cn} = $types_info[$params['parent']] [$item_Data['type']] [$cn];
                                    }
                                }
                                $insert_t->clientid = $f_cleint_id;
                                $insert_t->extra  = 1;
                                $insert_t->save();
                                $new_t_id = $insert_t->id;
                                
                                // add new  df added in follower
                                $item_Data['type'] =$new_t_id;
                                
                            } else{
                                
                                // insert in array, the coresponding  entry from follower
                                $item_Data['type'] = $types2follower2master_id[$f_cleint_id][$item_Data['type']]; // add new  df added in follower
                            }
                        }
                        
                        
                        
                        if ($item_Data['indication']) {
                            // get info for this dosage info, if already on
                            // Check if value NOT in followe
                            if( empty($indication2follower2master_id[$f_cleint_id][$item_Data['indication']] )){
                                
                                
                                //insert in follower and get id
                                $insert_t = new MedicationIndications();
                                foreach($mi_columns as $cn=>$ci){
                                    if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                        $insert_t->{$cn} = $types_info[$params['parent']] [$item_Data['indication']] [$cn];
                                    }
                                }
                                $insert_t->clientid = $f_cleint_id;
                                $insert_t->extra  = 1;
                                $insert_t->save();
                                $new_t_id = $insert_t->id;
                                
                                // add new  df added in follower
                                $item_Data['indication'] =$new_t_id;
                                
                            } else{
                                
                                // insert in array, the coresponding  entry from follower
                                $item_Data['indication'] = $indication2follower2master_id[$f_cleint_id][$item_Data['indication']]; // add new  df added in follower
                            }
                        }
       
                        
                        // Insert Medication items
                        $insert_set_item = new Bedarfsmedication();
                        foreach($bmed_columns  as $column_item_name => $cinfo){
                            if(!in_array($column_item_name,$except_columns_array)){
                                if($column_item_name=='medication_id'){
                                    $insert_set_item->medication_id = $medication_id;
                                } else{
                                    $insert_set_item->{$column_item_name} = $item_Data[$column_item_name];
                                }
                            }
                        }
                        $insert_set_item->bid = $set_id;
                        $insert_set_item->connection_id = $params['connection_id'];
                        $insert_set_item->master_id = $set['id'];
                        $insert_set_item->save();
                        
                    }
                }
                
            }
        }
 
    }
    
    
    public function bulk_connection_sync($params = array())
    {
        if (empty($params)) {
            return false;
        }

        if($params['list_type'] == 'MedicationsSetsList'){
            $this->bulk_medi_connection_sync($params);
            return;
        }

        
        if($params['list_type'] == 'BedarfsmedicationMaster'){
            $this->bulk_medi_bedarf_connection_sync($params);
            return;
        }
        
        // get lists details 
        $lists_details = Pms_CommonData::connection_lists();
        
        $list_model = $params['list_type'];
        $list_info = $lists_details[$list_model];
        $client_column = isset($list_info['client_column']) && !empty($list_info['client_column']) ? $list_info['client_column'] : 'clientid';
        $isdelete_column  = isset($list_info['isdelete_column']) && !empty($list_info['isdelete_column']) ? $list_info['isdelete_column'] : 'isdelete';
        $list_ident_column = isset($list_info['list_ident_column']) && !empty($list_info['list_ident_column']) ? $list_info['list_ident_column'] : '';
        $list_ident_value = isset($list_info['list_ident_value']) && strlen($list_info['list_ident_value'])>0 ? $list_info['list_ident_value'] : '0';
        $except_columns_array = isset($list_info['except_columns']) && !empty($list_info['except_columns']) ? $list_info['except_columns'] : array('id','clientid','create_user','change_user','create_date','change_date');

        $special_identify_column = isset($list_info['special_identify_column']) && strlen($list_info['special_identify_column'])>0 ? $list_info['special_identify_column'] : '';

        // hide data in followers
        // mark as deleted all adn add connection id OR just add connection ID for "special lists"
        try {
            
            if( ! empty($list_ident_column)){ // For lists like Locations
                
                // Normal lists with livesearch have indrop or extra 
                $update_followers = Doctrine_Query::create()
                ->update($list_model)
                ->set('`'.$isdelete_column.'`', 1)
                ->set('connection_id',"?", $params['connection_id'])
                ->whereIn('`'.$client_column.'`',$params['followers'])
                ->andWhere('`'.$list_ident_column.'` = ?', $list_ident_value)
                ->andWhere('`'.$isdelete_column.'` = ?',0);
                $update_followers->execute();
                
            } else {
                // "special" lists if marcked as deleted - will no longer be listed in app 
                // we set connection_id to know - which were active at the moment the connection was created 
                $update_followers = Doctrine_Query::create()
                ->update($list_model)
                ->set('connection_id',"?", $params['connection_id'])
                ->whereIn('`'.$client_column.'`',$params['followers'])
                ->andWhere('`'.$isdelete_column.'` = ?',0);
                $update_followers->execute();
            }
        }
        catch (Exception $e) {
           // var_dump($e->getMessage()); exit;
        }

            
        //get master data
        $parent_data_sql = Doctrine_Query::create()
        ->select("*")
        ->from($list_model)
        ->where('`'.$isdelete_column.'` = ?', 0)
        ->andWhere('`'.$client_column.'`= ?', $params['parent'] )
        ->andWhere('connection_id is null' )
        ->andWhere('master_id is null' );
        if($list_ident_column && $list_ident_column!= ""){
            $parent_data_sql->andWhere('`'.$list_ident_column.'` = ?', $list_ident_value);
        }
        // For special casess where multiple are in the same table
        if($special_identify_column && $special_identify_column!= "" && !empty($params['list_category'])){
            $parent_data_sql->andWhere('`'.$special_identify_column.'` = ?', $params['list_category']);
        }
        $parent_data_array = $parent_data_sql->fetchArray();
            
        /* if($list_model=='BedarfsmedicationMaster' && !empty($parent_data_array)){
            
            $bids_ids = array_column($parent_data_array, 'id');
            
            if(!empty($bids_ids)){
                $bedm = new Bedarfsmedication();
                $a_medic = $bedm->getbedarfsmedication($_GET['bid']);
                
         
                
                $medications_lines2bids_q = Doctrine_Query::create()
                ->select("*")
                ->from('Bedarfsmedication')
                ->whereIn("bid='",$bids_ids);
                $medications_lines2bids = $medications_lines2bids_q->fetchArray();
                
                if(!empty($medications_lines2bids)){
                    
                    foreach($medications_lines2bids as $k => $medi_lines){
                    }
                    
                    $medication_ids = array_column($medications_lines2bids,'medication_id'); 
                    // get medication 
                    
                    $bedm = new Medication();
                    $a_medic = $bedm->getMedicationById($medication_ids);
                    
                    
                    $unit_ids = array_column($medications_lines2bids,'unit'); 
                    
                    $type_ids = array_column($medications_lines2bids,'type'); 
                    
                    $indication_ids = array_column($medications_lines2bids,'indication'); 
                    
                    $dosage_form_ids = array_column($medications_lines2bids,'dosage_form');
                    
                }
                
            }
        } */
        
        
        
        
        //##############
        // Some special cases:
        $sp_types_connection = array();
        if($list_model == 'Specialists') {
            // get specialists types 
            $types = new SpecialistsTypes();
            $source_specialists_types = $types->get_specialists_types($params['parent']);
            // Insert parent types  to each follower 
            if(!empty($source_specialists_types))
            {
                foreach($params['followers'] as $f_cleint_id)
                {
                    // insert specialists types  for target client
                    foreach($source_specialists_types as $spt_key=>$source_spt_value)
                    {
                        $insert_sp_types = new SpecialistsTypes();
                        $insert_sp_types->clientid = $f_cleint_id;
                        $insert_sp_types->connection_id = $params['connection_id'];
                        $insert_sp_types->master_id = $source_spt_value['id'];
                        $insert_sp_types->name = $source_spt_value['name'];
                        $insert_sp_types->save();
                        $sp_types_connection[$f_cleint_id][$source_spt_value['id']] = $insert_sp_types->id;
                    }
                }
            }
            
        }
        
        $service_group_connection = array();
        if($list_model == 'CareservicesItems') {
            // get specialists types 
            $groups = new CareservicesGroups();
            $source_groups = $groups->get_client_groups($params['parent']);
            // Insert parent types  to each follower 
            if(!empty($source_groups))
            {
                // DELETE -  existing ones?
                foreach($params['followers'] as $f_cleint_id)
                {
                    //  insert specialists types  for target client
                    foreach($source_groups as $spt_key=>$source_spt_value)
                    {
                        $insert_s_group= new CareservicesGroups();
                        $insert_s_group->client = $f_cleint_id;
                        $insert_s_group->groupname = $source_spt_value['groupname'];
                        $insert_s_group->save();
                        $service_group_connection[$f_cleint_id][$source_spt_value['id']] = $insert_s_group->id;
                    }
                }
            }
        }
        
        
        //###################
        if($list_model == 'HealthInsurance'){
            
            $hi_company_ids = array_column($parent_data_array, 'id');
            if($hi_company_ids){
                $h2s_q = Doctrine_Query::create()
                ->select('*')
                ->from('HealthInsurance2Subdivisions')
                ->whereIn("company_id",$hi_company_ids)
                ->andWhere("isdelete = 0")
                ->andWhere("onlyclients = 1 ");
                $h2s_array = $h2s_q->fetchArray();
            }
             
            $sub = array();
            if(!empty($h2s_array)){
                foreach($h2s_array as $k=>$s){
                    $sub[$s['company_id']][]= $s;
                }
            }
            
            
            $entity_pc = new $list_model();
            $model_columns = $entity_pc->getTable()->getColumns();
            foreach($params['followers'] as $f_cleint_id)
            {
                foreach($parent_data_array as $key=>$data)
                {
                    $insert_values = new HealthInsurance();
                    foreach($model_columns as $column_name =>$colum_data)
                    {
                        if(!in_array($column_name,$except_columns_array)){
                            $insert_values->$column_name = $data[$column_name];
                        }
                    }
                    $insert_values->$client_column = $f_cleint_id; 
                    $insert_values->connection_id = $params['connection_id'];
                    $insert_values->master_id = $data['id'];
                    $insert_values->save();
                    
                    $inserted_id = $insert_values->id;
                    
                    
                    if(!empty($sub[$data['id']]) && $inserted_id){
                        
                        foreach($sub[$data['id']] as $k=>$sdata){
                            
                            $insert_s_values = new HealthInsurance2Subdivisions();
                            foreach($sdata as $field=>$value){
                                
                                if(!in_array($field,$except_columns_array))
                                {
                                    if($field == "clientid"){
                                        $insert_s_values->clientid = $f_cleint_id;
                                    }
                                    elseif($field == "company_id"){
                                        $insert_s_values->company_id = $inserted_id;
                                    }
                                    else
                                    {
                                        $insert_s_values->$field = $value;
                                    }
                                }
                            }
                            $insert_s_values->save();
                        }
                    }
                    
                }
            }
            
         } elseif($list_model == 'CareservicesGroups') {
             
 
            
        } else {
            $entity_pc = new $list_model();
            $model_columns = $entity_pc->getTable()->getColumns();
            
            // pepare all data for each child client id 
            $i=0;
            $child_data = array();
            foreach($params['followers'] as $f_cleint_id){
                
                foreach($parent_data_array as $key=>$data){
                    
                    foreach($model_columns as $column_name =>$colum_data){
                        
                        if(!in_array($column_name,$except_columns_array)){
                            
                            $child_data[$i][$column_name] = $data[$column_name];
                            
                        }
                    }
                    
                    $child_data[$i][$client_column] = $f_cleint_id; // rewrite  clientid column
                    $child_data[$i]['connection_id'] = $params['connection_id'];
                    $child_data[$i]['master_id'] = $data['id'];
    
                    if($list_model == 'Specialists') {
                        if($sp_types_connection[$f_cleint_id][$data['medical_speciality']])
                        {
//                             $child_data[$i]['medical_speciality'] =  $sp_types_connection[$f_cleint_id][data['medical_speciality']];
                            $child_data[$i]['medical_speciality'] =  $sp_types_connection[$f_cleint_id][$data['medical_speciality']];
                        }
                        else
                        {
                            $child_data[$i]['medical_speciality']  = "0";
                        }
                    }
    
                    if($list_model == 'CareservicesItems') {
                        if($service_group_connection[$f_cleint_id][$data['group_id']])
                        {
                            $child_data[$i]['group_id'] =  $service_group_connection[$f_cleint_id][$data['group_id']];
                        }
                        else
                        {
                            $child_data[$i]['group_id']  = "0";
                        }
                    }
                    
                    $i++;
                }
            }
            
            // insert data 
            if (! empty($child_data)) {
                $collection = new Doctrine_Collection($list_model);
                $collection->fromArray($child_data);
                $collection->save();
            }
                
        }
        
        
        
        
        
        
    }
    
    public function bulk_connection_revert ($params){
        
        if (empty($params)) {
            return false;
        }
        
        // get lists details
        $lists_details = Pms_CommonData::connection_lists();
        
        $list_model = $params['list_type'];
        $list_info = $lists_details[$list_model];
        $client_column = isset($list_info['client_column']) && !empty($list_info['client_column']) ? $list_info['client_column'] : 'clientid';
        $isdelete_column  = isset($list_info['isdelete_column']) && !empty($list_info['isdelete_column']) ? $list_info['isdelete_column'] : 'isdelete';
        $list_ident_column = isset($list_info['list_ident_column']) && !empty($list_info['list_ident_column']) ? $list_info['list_ident_column'] : '';
        $list_ident_value = isset($list_info['list_ident_value']) && strlen($list_info['list_ident_value'])>0 ? $list_info['list_ident_value'] : '0';
         
        
        // in followers hide data from master  that came from a connection sync
        // set connection to null, master_id to null, isdelete to 1
        // where connection = connection id, master_id != null and isdelete = 0  and indrop  = 0
        try {
            if(!empty($list_info['patient_connection']))
            {
                $patient_model = $list_info['patient_connection']['patient_model'];
                $patient_column_corelation = $list_info['patient_connection']['patient_db_column_name'];
                
                
                $parent_data_sql = Doctrine_Query::create()
                ->select("*")
                ->from($list_model)
                ->where('`'.$isdelete_column.'` = ?', 0)
                ->andWhereIn('`'.$client_column.'`', $params['followers'] )
                ->andWhere('connection_id = ?',$params['connection_id'] );
                if($list_ident_column && $list_ident_column != ""){
                    $parent_data_sql->andWhere('`'.$list_ident_column.'` = ?', $list_ident_value);
                }
                $parent_data_array = $parent_data_sql->fetchArray();

                // get all used  ids 
                $used_data_ids = array();
                $used_data_ids = array_column($parent_data_array, 'id');
                
                if(!empty($used_data_ids) ){
                    
                    $parent_pdata_sql = Doctrine_Query::create()
                    ->select("*")
                    ->from($patient_model)
                    ->whereIn('`'.$patient_column_corelation.'`', $used_data_ids);
                    $patient_data_array = $parent_pdata_sql->fetchArray();
                    
                    $user_master_ids = array();
                    $user_master_ids = array_column($patient_data_array, $patient_column_corelation);
                }
                
                   //  if they were used - do not remove below
                if($user_master_ids){
                    
                    $keep_used_items_q = Doctrine_Query::create()
                    ->update($list_model)
                    ->set('connection_id', 'null')
                    ->set('master_id','null')
                    ->whereIn('`'.$client_column.'`',$params['followers'])
                    ->andWhere('connection_id = ?', $params['connection_id'])
                    ->andWhere('master_id is NOT null')
                    ->andWhereIn('id',$user_master_ids);
                    if($list_ident_column && $list_ident_column != ""){
                        $keep_used_items_q->andWhere('`'.$list_ident_column.'` = ?', $list_ident_value);
                    }
                    $keep_used_items_q->andWhere('`'.$isdelete_column.'` = ?',0);
//                     var_Dump($keep_used_items_q->getSqlQuery());
                    $keep_used_items_q->execute();
                }
            }
            
            
            $remove_connection_data = Doctrine_Query::create()
            ->update($list_model)
            ->set('`'.$isdelete_column.'`', 1)
            ->set('connection_id', 'null')
            ->set('master_id','null')
            ->whereIn('`'.$client_column.'`',$params['followers'])
            ->andWhere('connection_id = ?', $params['connection_id'])
            ->andWhere('master_id is NOT null');
            if($list_ident_column){
                $remove_connection_data->andWhere('`'.$list_ident_column.'` = ?', $list_ident_value);
            }
            $remove_connection_data->andWhere('`'.$isdelete_column.'` = ?',0);
            $remove_connection_data->execute();
        
            
            
            
            if($list_model == 'Specialists') {
                // get specialists types
                $remove_connection_data = Doctrine_Query::create()
                ->update('SpecialistsTypes')
                ->set('isdelete', 1)
                ->set('connection_id', 'null')
                ->set('master_id','null')
                ->whereIn('clientid',$params['followers'])
                ->andWhere('connection_id = ?', $params['connection_id'])
                ->andWhere('master_id is NOT null');
                $remove_connection_data->andWhere('`'.$isdelete_column.'` = ?',0);
                $remove_connection_data->execute();
            }
            
            $connection_data_removed = true;
                
        }
        catch (Exception $e) {
            //var_dump($e->getMessage()); exit;
        }
        
        
        // Show OLD folower data  that was marked as deleted when connection was created
        // set connection to null, master_id to null, isdelete to 0
        // where connection = connection id, master_id == null and isdelete = 1  and indrop  = 0
        
        // For special lists as Locations - the Old entries were not set as deleted! 
        try {
            if($list_ident_column){
                // this means that isdelete was set to 1 when connection was made, and can be reverted
                $reactivate_old_data = Doctrine_Query::create()
                ->update($list_model)
                ->set('`'.$isdelete_column.'`','?', 0)
                ->set('connection_id',"null")
                ->set('master_id',"null")
                ->whereIn('`'.$client_column.'`',$params['followers'])
                ->andWhere('connection_id = ?',$params['connection_id'])
                ->andWhere('master_id is null')
                ->andWhere('`'.$list_ident_column.'` = ?', $list_ident_value)
                ->andWhere('`'.$isdelete_column.'` = ?',1);
                $reactivate_old_data->execute();
            }
            else
            {
                //for special Lists, isdelete was not set to 1, but it was added the connection id  - to know qich to hide/show at revert 
                $reactivate_old_data = Doctrine_Query::create()
                ->update($list_model)
                ->set('connection_id',"null")
                ->set('master_id',"null")
                ->whereIn('`'.$client_column.'`',$params['followers'])
                ->andWhere('connection_id = ?',$params['connection_id'])
                ->andWhere('master_id is null');
                $reactivate_old_data->execute();
            }
            
            $data_reactivated = true;
        }
        catch (Exception $e) {
            //var_dump($e->getMessage()); exit;
        }
        
        if($connection_data_removed && $data_reactivated){
            return  true;
        } else{
            return false;
        }
        
    }
    
}
?>