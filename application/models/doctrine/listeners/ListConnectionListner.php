<?php

/**
 * ListConnectionListner 
 * Ancuta 
 * ISPC-2612 Ancuta 29.06.2020  // Maria:: Migration ISPC to CISPC 08.08.2020
 * @package    ISPC
 */
class ListConnectionListner extends Doctrine_Record_Listener
{

    /**
     * Array of Column names that should be encrypted
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
    
    public function postInsert(Doctrine_Event $event)
    {
        $Invoker = $event->getInvoker();
        $ComponentName = $Invoker->getTable()->getComponentName();

        
        if($ComponentName == 'MedicationsSetsItems'){
            if(!isset($Invoker->bid)){
                return;
            }
            
            
            // get client id from
            $parent_data_sql = Doctrine_Query::create()
            ->select("*")
            ->from('MedicationsSetsList')
            ->where('id= ?',$Invoker->bid);
            $parent_data_array = $parent_data_sql->fetchArray();
           
            if(!empty($parent_data_array)){
                $current_client = $parent_data_array[0]['clientid'];
            }
            else
            {
                return;
            }
            
            
            $ComponentNameRelated = "MedicationsSetsList";
            $connection_data = ConnectionMasterTable::_find_parent_connection_details($ComponentNameRelated,$current_client);
            
            
            if(empty($connection_data)){
                return;
            }
            
            $current_connection_parents = array();
            $current_connection_followers  = array();
            $current_connection_parents = array_column($connection_data, 'clientid');
            
            $current_connection_id = 0 ;
            foreach($connection_data as $con_key => $con_data){
                $current_connection_followers = array_column($con_data['ConnectionFollowers'],'clientid');
                $current_connection_id = $con_data['id'];
                
            }
            
            if( !in_array($current_client,$current_connection_parents) && !empty($current_connection_id)){
                return;
            }
            
            // get bid in follower 
            $followersd = Doctrine_Query::create()
            ->select("*")
            ->from('MedicationsSetsList')
            ->whereIn('clientid',$current_connection_followers)
            ->andWhere('connection_id is not null')
            ->andWhere('master_id is not null')
            ->fetchArray();
            
            $bid2follower = array();
            foreach($followersd as $k=>$f){
                if($f['master_id'] == $Invoker->bid){
                    $bid2follower[$f['clientid']] = $f['id'];
                }
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
                if(in_array($mv['clientid'],$current_connection_followers) && $mv['master_id'] !=  null ){
                    $indication2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
                } elseif($mv['clientid'] == $current_client){
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
                
                if(in_array($mv['clientid'],$current_connection_followers) && $mv['master_id'] !=  null ){
                    $units2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
                } elseif($mv['clientid'] == $current_client){
                    $unit_info[$mv['clientid']] [$mv['id']] = $mv;
                }
                
            }
            
            // Medication_type
            $mt = new MedicationType();
            $mt_columns = $mt->getTable()->getColumns();
            $followers_types = Doctrine_Query::create()
            ->select('*')
            ->from('MedicationType')
            //         ->whereIn('clientid',$current_connection_followers)
            //         ->andWhere('master_id is not null')
            ->fetchArray();
            
            
            $types2follower2master_id = array();
            $types_info = array();
            foreach($followers_types as $k=>$mv){
                
                if(in_array($mv['clientid'],$current_connection_followers) && $mv['master_id'] !=  null ){
                    $types2follower2master_id  [$mv['clientid']] [$mv['master_id']] = $mv['id'];
                } elseif($mv['clientid'] == $current_client){
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
                if(in_array($mv['clientid'],$current_connection_followers) && $mv['master_id'] !=  null ){
                    $freq2follower2master_id  [$mv['clientid']] [$mv['master_id']] = $mv['id'];
                } elseif($mv['clientid'] == $current_client){
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
                if(in_array($mv['clientid'],$current_connection_followers) && $mv['master_id'] !=  null ){
                    $dosagesforms2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
                } elseif($mv['clientid'] == $current_client){
                    $dosagef_info[$mv['clientid']] [$mv['id']] = $mv;
                }
            }
             
            $entity_items = new MedicationsSetsItems();
            $sets_items_columns = $entity_items->getTable()->getColumns();
            
            $entity_med = new Medication();
            $med_columns = $entity_med->getTable()->getColumns();
            
            
            $item_Data = array();
            $set_id = 0 ;
            foreach($current_connection_followers as $f_cleint_id)
            {
                // insert set
                $set_id = $bid2follower[$f_cleint_id];
                
                 // first insert medications
                if($Invoker->medication_id){
                    $item_Data['medication_id'] = $Invoker->medication_id;
                    $medication_id = $item_Data['medication_id'];
                    if ($item_Data['medication_id']) {
                        $medcaarray = array();
                        $medca = Doctrine::getTable('Medication')->find($item_Data['medication_id']);
                        if (! empty($medca)) {
                            $medcaarray = $medca->toArray();
                            if ($medcaarray) {
                                $imed = new Medication();
                                foreach ($med_columns as $cname => $cdata) {
                                    if (! in_array($cname, array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))) {
                                        $imed->{$cname} = $medcaarray[$cname];
                                    }
                                }
                                $imed->clientid = $f_cleint_id;
                                $imed->connection_id = $current_connection_id;
                                $imed->master_id = $medcaarray['id'];
                                $imed->save();
                                
                                $medication_id = $imed->id;
                            }
                        }
                        $old_medi2new[$item_Data['medication_id']] = $medication_id;
                    }
                }
                
                
                // insert  dosage_form
                if ($Invoker->med_dosage_form) {
                    $item_Data['med_dosage_form'] = $Invoker->med_dosage_form;
                    // get info for this dosage info, if already on
                    foreach($item_Data['med_dosage_form'] as $kf=>$dosagef_id){
                        // Check if value NOT in followe
                        if( empty($dosagesforms2follower2master_id[$f_cleint_id][$dosagef_id] )){
                            
                            
                            //insert in follower and get id
                            $insert_df = new MedicationDosageform();
                            foreach($df_columns as $cn=>$ci){
                                if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                    $insert_df->{$cn} = $dosagef_info[$current_client] [$dosagef_id] [$cn];
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
                
                
                if ($Invoker->type) {
                    $item_Data['type'] = $Invoker->type;
                    // get info for this dosage info, if already on
                    foreach($item_Data['type'] as $kf=>$stype_id){
                        // Check if value NOT in followe
                        if( empty($types2follower2master_id[$f_cleint_id][$stype_id] )){
                            
                            
                            //insert in follower and get id
                            $insert_t = new MedicationType();
                            foreach($mt_columns as $cn=>$ci){
                                if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                    $insert_t->{$cn} = $types_info[$current_client] [$stype_id] [$cn];
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
                
                
                
                if ($Invoker->frequency) {
                    $item_Data['frequency'] = $Invoker->frequency;
                    // get info for this dosage info, if already on
                    foreach($item_Data['frequency'] as $kff=>$freq_id){
                        // Check if value NOT in followe
                        if( empty($freq2follower2master_id[$f_cleint_id][$freq_id] )){
                            
                            //insert in follower and get id
                            $insert_f = new MedicationFrequency();
                            foreach($mf_columns as $cn=>$ci){
                                if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                    $insert_f->{$cn} = $freq_info[$current_client] [$freq_id] [$cn];
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
                    if(!in_array($column_item_name,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                        if($column_item_name=='medication_id'){
                            $insert_set_item->medication_id = $medication_id;
                        }elseif($column_item_name=='frequency'){
                            $insert_set_item->frequency = $item_Data['frequency'];
                            
                        }elseif($column_item_name=='type'){
                            $insert_set_item->type = $item_Data['type'];
                            
                            
                        }elseif($column_item_name=='med_dosage_form'){
                            $insert_set_item->med_dosage_form = $item_Data['med_dosage_form'];
                            
                        } else{
                            $insert_set_item->{$column_item_name} = $Invoker->$column_item_name;
                        }
                    }
                }
                $insert_set_item->bid = $set_id;
                $insert_set_item->connection_id = $current_connection_id;
                $insert_set_item->master_id = $Invoker->id;
                $insert_set_item->isdelete = $Invoker->isdelete;
                $insert_set_item->save();
                
                
                                
                
                
                
                // insert dosage for each set and medication
                $dosage_insert = array();
                
                // for each set - get medication dosage
                $dparent_data_sql = Doctrine_Query::create()
                ->select("*")
                ->from('MedicationsSetsItemsDosage')
                ->where('isdelete = ?', 0)
                ->andWhere('bid_id = ?',  $Invoker->bid);
                $set_dosages = $dparent_data_sql->fetchArray();
                
                $dosages2set2medication = array();
                foreach($set_dosages as $k=>$set_Data){
                    $dosages2set2medication[$set_Data['bid_id']] [$set_Data['medication_id']][] = $set_Data;
                }
                
                if(!empty($dosages2set2medication[$old_medication_id])){
                    foreach($dosages2set2medication[$old_medication_id] as $old_medication_id => $dosage_data){
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
            
            return;
        }
        
        
        // HARDCODEDE FOR   Bedarfsmedication and  BedarfsmedicationMaster
        if($ComponentName == 'Bedarfsmedication'){
            if(!isset($Invoker->bid)){
                return;
            }
            
            // get client id from
            $parent_data_sql = Doctrine_Query::create()
            ->select("*")
            ->from('BedarfsmedicationMaster')
            ->where('id= ?',$Invoker->bid);
            $parent_data_array = $parent_data_sql->fetchArray();
           
            if(!empty($parent_data_array)){
                $current_client = $parent_data_array[0]['clientid'];
            }
            else
            {
                return;
            }
            
            
            $ComponentNameRelated = "BedarfsmedicationMaster";
            $connection_data = ConnectionMasterTable::_find_parent_connection_details($ComponentNameRelated,$current_client);
            
            
            if(empty($connection_data)){
                return;
            }
            
            $current_connection_parents = array();
            $current_connection_followers  = array();
            $current_connection_parents = array_column($connection_data, 'clientid');
            
            $current_connection_id = 0 ;
            foreach($connection_data as $con_key => $con_data){
                $current_connection_followers = array_column($con_data['ConnectionFollowers'],'clientid');
                $current_connection_id = $con_data['id'];
                
            }
            
            if( !in_array($current_client,$current_connection_parents) && !empty($current_connection_id)){
                return;
            }
            
            // get bid in follower 
            $followersd = Doctrine_Query::create()
            ->select("*")
            ->from('BedarfsmedicationMaster')
            ->whereIn('clientid',$current_connection_followers)
            ->andWhere('connection_id is not null')
            ->andWhere('master_id is not null')
            ->fetchArray();
            
            $bid2follower = array();
            foreach($followersd as $k=>$f){
                if($f['master_id'] == $Invoker->bid){
                    $bid2follower[$f['clientid']] = $f['id'];
                }
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
                if(in_array($mv['clientid'],$current_connection_followers) && $mv['master_id'] !=  null ){
                    $indication2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
                } elseif($mv['clientid'] == $current_client){
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
                
                if(in_array($mv['clientid'],$current_connection_followers) && $mv['master_id'] !=  null ){
                    $units2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
                } elseif($mv['clientid'] == $current_client){
                    $unit_info[$mv['clientid']] [$mv['id']] = $mv;
                }
                
            }
            
            // Medication_type
            $mt = new MedicationType();
            $mt_columns = $mt->getTable()->getColumns();
            $followers_types = Doctrine_Query::create()
            ->select('*')
            ->from('MedicationType')
            //         ->whereIn('clientid',$current_connection_followers)
            //         ->andWhere('master_id is not null')
            ->fetchArray();
            
            
            $types2follower2master_id = array();
            $types_info = array();
            foreach($followers_types as $k=>$mv){
                
                if(in_array($mv['clientid'],$current_connection_followers) && $mv['master_id'] !=  null ){
                    $types2follower2master_id  [$mv['clientid']] [$mv['master_id']] = $mv['id'];
                } elseif($mv['clientid'] == $current_client){
                    $types_info[$mv['clientid']] [$mv['id']] = $mv;
                }
            }
            
            
            // MedicationFrequency
            $mf = new MedicationFrequency();
            $mf_columns = $mf->getTable()->getColumns();
            $followers_freq = Doctrine_Query::create()
            ->select('*')
            ->from('MedicationFrequency')
            ->fetchArray();
            
            $freq2follower2master_id = array();
            $freq_info = array();
            foreach($followers_freq as $k=>$mv){
                if(in_array($mv['clientid'],$current_connection_followers) && $mv['master_id'] !=  null ){
                    $freq2follower2master_id  [$mv['clientid']] [$mv['master_id']] = $mv['id'];
                } elseif($mv['clientid'] == $current_client){
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
                if(in_array($mv['clientid'],$current_connection_followers) && $mv['master_id'] !=  null ){
                    $dosagesforms2follower2master_id [$mv['clientid']] [$mv['master_id']] = $mv['id'];
                } elseif($mv['clientid'] == $current_client){
                    $dosagef_info[$mv['clientid']] [$mv['id']] = $mv;
                }
            }
            $entity_bmed = new Bedarfsmedication();
            $bmed_columns = $entity_bmed->getTable()->getColumns();
            
            $entity_med = new Medication();
            $med_columns = $entity_med->getTable()->getColumns();
            
            $item_Data = array();
            $set_id = 0 ;
            foreach($current_connection_followers as $f_cleint_id)
            {
                
                // insert set
                $set_id = $bid2follower[$f_cleint_id];

                
                 // first insert medications
                if($Invoker->medication_id){
                    $item_Data['medication_id'] = $Invoker->medication_id;
                    $medication_id = $item_Data['medication_id'];
                    if ($item_Data['medication_id']) {
                        $medcaarray = array();
                        $medca = Doctrine::getTable('Medication')->find($item_Data['medication_id']);
                        if (! empty($medca)) {
                            $medcaarray = $medca->toArray();
                            if ($medcaarray) {
                                $imed = new Medication();
                                foreach ($med_columns as $cname => $cdata) {
                                    if (! in_array($cname, array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))) {
                                        $imed->{$cname} = $medcaarray[$cname];
                                    }
                                }
                                $imed->clientid = $f_cleint_id;
                                $imed->connection_id = $current_connection_id;
                                $imed->master_id = $medcaarray['id'];
                                $imed->save();
                                
                                $medication_id = $imed->id;
                            }
                        }
                        $old_medi2new[$item_Data['medication_id']] = $medication_id;
                    }
                }
      
                
                // insert  dosage_form
                if ($Invoker->dosage_form) {
                    $item_Data['dosage_form'] = $Invoker->dosage_form;
                    // Check if value NOT in followe
                    if( empty($dosagesforms2follower2master_id[$f_cleint_id][$item_Data['dosage_form']] )){
                        
                        //insert in follower and get id
                        $insert_df = new MedicationDosageform();
                        foreach($df_columns as $cn=>$ci){
                            if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                $insert_df->{$cn} = $dosagef_info[$current_client] [$item_Data['dosage_form']] [$cn];
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
                if ($Invoker->unit) {
                    $item_Data['unit'] = $Invoker->unit;
                    // Check if value NOT in followe
                    if( empty($units2follower2master_id[$f_cleint_id][$item_Data['unit']] )){
                        
                        //insert in follower and get id
                        $insert_df = new MedicationUnit();
                        foreach($mu_columns as $cn=>$ci){
                            if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                $insert_df->{$cn} = $unit_info[$current_client] [$item_Data['unit']] [$cn];
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
                
                
                
                if ($Invoker->type) {
                    $item_Data['type'] = $Invoker->type;
                    // get info for this dosage info, if already on
                    // Check if value NOT in followe
                    if( empty($types2follower2master_id[$f_cleint_id][$item_Data['type']] )){
                        
                        //insert in follower and get id
                        $insert_t = new MedicationType();
                        foreach($mt_columns as $cn=>$ci){
                            if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                $insert_t->{$cn} = $types_info[$current_client] [$item_Data['type']] [$cn];
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
                
                if ($Invoker->indication) {
                    $item_Data['indication'] = $Invoker->indication;
                    // get info for this dosage info, if already on
                    // Check if value NOT in followe
                    if( empty($indication2follower2master_id[$f_cleint_id][$item_Data['indication']] )){
                        
                        
                        //insert in follower and get id
                        $insert_t = new MedicationIndications();
                        foreach($mi_columns as $cn=>$ci){
                            if(!in_array($cn ,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                                $insert_t->{$cn} = $types_info[$current_client] [$item_Data['indication']] [$cn];
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
                foreach($bmed_columns as $column_item_name => $cinfo){
                    if(!in_array($column_item_name,array('id','clientid','connection_id','master_id','create_user','change_user','create_date','change_date'))){
                        if($column_item_name=='medication_id'){
                            $insert_set_item->medication_id = $medication_id;
                            
                        }elseif($column_item_name=='type'){
                            $insert_set_item->type = $item_Data['type'];
                            
                        }elseif($column_item_name=='unit'){
                            $insert_set_item->unit = $item_Data['unit'];
                            
                            
                        }elseif($column_item_name=='indication'){
                            $insert_set_item->indication = $item_Data['indication'];
                            
                            
                        }elseif($column_item_name=='dosage_form'){
                            $insert_set_item->dosage_form = $item_Data['dosage_form'];
                            
                        } else{
                            $insert_set_item->{$column_item_name} = $Invoker->$column_item_name;
                        }
                    }
                }
                $insert_set_item->bid = $set_id;
                $insert_set_item->connection_id = $current_connection_id;
                $insert_set_item->master_id = $Invoker->id;
                $insert_set_item->isdelete = $Invoker->isdelete;
                $insert_set_item->save();
          
            }
            
            return;
        }
        
        
        $lists = Pms_CommonData::connection_lists();
        
        $ComponentCategory = null;
        if(isset($Invoker->category) ){
            $ComponentCategory = $Invoker->category;
            $list_info = $lists[$ComponentName.'.'.$ComponentCategory];
        } else {
            $list_info = $lists[$ComponentName];
        }

        
        
        $client_column = isset($list_info['client_column']) && !empty($list_info['client_column']) ? $list_info['client_column'] : 'clientid';
        $except_columns_array = isset($list_info['except_columns']) && !empty($list_info['except_columns']) ? $list_info['except_columns'] : array('id','clientid','create_user','change_user','create_date','change_date');
        $list_ident_column = isset($list_info['list_ident_column']) && !empty($list_info['list_ident_column']) ? $list_info['list_ident_column'] : '';
        $list_ident_value = isset($list_info['list_ident_value']) && strlen($list_info['list_ident_value'])>0 ? $list_info['list_ident_value'] : '0';
        
        // THis is neede ONLY when values are added in the master lists - not in patient
        if(isset($list_ident_column) && !empty($list_ident_column)){
            if($Invoker->{$list_ident_column}!= $list_ident_value){
                return;
            }
        }
        
        $current_client = $Invoker->{$client_column};
        $connection_data = array();
        if($ComponentCategory){
            $connection_data = ConnectionMasterTable::_find_parent_connection_details2category($ComponentName,$current_client,null,$ComponentCategory);
        } else{
            $connection_data = ConnectionMasterTable::_find_parent_connection_details($ComponentName,$current_client);
        }
        
        if(empty($connection_data)){
            return;
        }
        $current_connection_parents = array();
        $current_connection_followers  = array();
        $current_connection_parents = array_column($connection_data, 'clientid');
        
        $current_connection_id = 0 ; 
        foreach($connection_data as $con_key => $con_data){
            $current_connection_followers = array_column($con_data['ConnectionFollowers'],'clientid');
            $current_connection_id = $con_data['id'];
            
        }
        
        if( !in_array($current_client,$current_connection_parents) && !empty($current_connection_id)){
            return;
        }
        
        $entity_pc = new $ComponentName();
        $model_columns = $entity_pc->getTable()->getColumns();
        $new_record_data = array();
        $cl_in = 0 ; 
        foreach($current_connection_followers as $f_client_id)
        {
            foreach($model_columns as $column_name => $cinfo)
            {
                if (!in_array($column_name,$except_columns_array )){
                    $new_record_data[$cl_in][$column_name] = $Invoker->{$column_name};
                }
                
                $new_record_data[$cl_in]['connection_id'] =  $current_connection_id;
                $new_record_data[$cl_in]['master_id'] = $Invoker->id;
                $new_record_data[$cl_in][$client_column] = $f_client_id;
            }
            $cl_in++;
        }
        
        if(!empty($new_record_data)){
            $collection = new Doctrine_Collection($ComponentName);
            $collection->fromArray($new_record_data);
            $collection->save();
        }
         
    }

    public function postUpdate(Doctrine_Event $event)
    {
        $Invoker = $event->getInvoker();
        $ComponentName = $Invoker->getTable()->getComponentName();
        
        $lists = Pms_CommonData::connection_lists();
        
        if($ComponentName == 'Bedarfsmedication'){
            return;
        }
        
        // For special lists - where multiple are saved in the same table 
        $ComponentCategory = null;
        if(isset($Invoker->category) ){
            $ComponentCategory = $Invoker->category;
            $list_info = $lists[$ComponentName.'.'.$ComponentCategory];
        } else {
            $list_info = $lists[$ComponentName];
        }
        
        
        
        $client_column = isset($list_info['client_column']) && !empty($list_info['client_column']) ? $list_info['client_column'] : 'clientid';
        $except_columns_array = isset($list_info['except_columns']) && !empty($list_info['except_columns']) ? $list_info['except_columns'] : array('id','clientid','create_user','change_user','create_date','change_date');
        
        if(!isset($Invoker->{$client_column})){
            return;
        }
        $current_client = $Invoker->{$client_column};
        
        $connection_data = array();
        
        if($ComponentCategory){
            $connection_data = ConnectionMasterTable::_find_parent_connection_details2category($ComponentName,$current_client,null,$ComponentCategory);
        } else{
            $connection_data = ConnectionMasterTable::_find_parent_connection_details($ComponentName,$current_client);
        }
        
        
        if(empty($connection_data)){
            return;
        }
        $current_connection_parents = array();
        $current_connection_followers  = array();
        $current_connection_parents = array_column($connection_data, 'clientid');
        
        $current_connection_id = 0 ;
        foreach($connection_data as $con_key => $con_data){
            $current_connection_followers = array_column($con_data['ConnectionFollowers'],'clientid');
            $current_connection_id = $con_data['id'];
            
        }
        if( !in_array($current_client,$current_connection_parents) && !empty($current_connection_id)){
            return;
        }
        /*
        $last_modified = $Invoker->getLastModified(true);
        
        
        //  For NOW, this works - later maybe we can find a better method!  
        // UPDATE DATA FOR EACH FOLLOWER! 
        foreach($current_connection_followers as $f_client_id)
        {
            $update_follower_data = Doctrine_Query::create()
                ->update($ComponentName);
                foreach($last_modified as $column_name => $old_value){
                    if (!in_array($column_name,$except_columns_array )){
                        $update_follower_data ->set('`'.$column_name.'`','?',  $Invoker->{$column_name});
                    }
                }
            $update_follower_data->where('connection_id = ?', $current_connection_id)
                ->andWhere('master_id = ?', $Invoker->id)
                ->andWhere('`'.$client_column.'`= ?', $f_client_id );
            $update_follower_data->execute();
            
        }
        */
        
        
        $entity_pc = new $ComponentName();
        $model_columns = $entity_pc->getTable()->getColumns();
        
        //  For NOW, this works - later maybe we can find a better method!
        // UPDATE DATA FOR EACH FOLLOWER!
        
        // get alll values from follwers related to this master id 
        $master_id =  $Invoker->id;
        $data_sql = Doctrine_Query::create()
        ->select("*")
        ->from($ComponentName)
        ->where('connection_id = ?', $current_connection_id)
        ->andWhere('master_id = ?', $master_id)
        ->andWhereIn('`'.$client_column.'`', $current_connection_followers );
        $folower_data_array = $data_sql->fetchArray();
        
        foreach($folower_data_array as $k=> $fdata){
            $stmb = Doctrine::getTable($ComponentName)->find($fdata['id']);
            if($stmb){
                foreach($model_columns as $column_name => $cinfo)
                {
                    if (!in_array($column_name,$except_columns_array ) && !in_array($column_name, array('connection_id','master_id',$client_column))){
                        $stmb->$column_name = $Invoker->{$column_name};
                    }
                }
                $stmb->save();
            }
            
        }
        
/*         foreach($current_connection_followers as $f_client_id)
        {
            $update_follower_data = Doctrine_Query::create()
            ->update($ComponentName);
            foreach($model_columns as $column_name => $cinfo)
            {
                if (!in_array($column_name,$except_columns_array )){
                    $update_follower_data ->set('`'.$column_name.'`','?',  $Invoker->{$column_name});
                }
            }
            $update_follower_data->where('connection_id = ?', $current_connection_id)
            ->andWhere('master_id = ?', $Invoker->id)
            ->andWhere('`'.$client_column.'`= ?', $f_client_id );
            $update_follower_data->execute();
            
        } */
        
 
    }
}
