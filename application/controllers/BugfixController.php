<?php
/**
 * 
 * @author Ancuta 
 * Feb 20 2019
 * 
 *
 */
class BugfixController extends Pms_Controller_Action {
    
    public function init()
    {
    	$logininfo = new Zend_Session_Namespace('Login_Info');
	    if($logininfo->usertype != 'SA')
	    {
	        die(" normal Ben ? ");
	    }
    
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
	    
    }
    
    
    
    
    
    
    /**
     * Ancuta 20.02.2019
     * Fix for TODO-2125
     */
    public function btmpatienthistoryfixAction()
    {
        
//         $up_ipids = array("715df97474522c56cad9c039473f9b23a3e5bd74");
        $med_hisptory = Doctrine_Query::create()
        ->select('*')
        ->from('MedicationPatientHistory indexby id')
        ->andWhere('isdelete = "0"')
        ->andWhere('self_id != "0"')
//         ->andWhereIn('ipid',$up_ipids)
        ->fetchArray();
        
        
        $ipids = array();
        foreach($med_hisptory as $k=>$mhis){
            $ipids[] = $mhis['ipid'];
        }

        
        $patient = Doctrine_Query::create()
        ->select('*')
        ->from('EpidIpidMapping')
        ->whereIn("ipid", $ipids);
        $patient_details = $patient->fetchArray();
        $ipid2epid = array();
        foreach($patient_details as $ep=>$epid){
            $ipid2epid[$epid['ipid']] = $epid['epid'];
        }
        
        
        foreach($med_hisptory as $k=>$mhis){
        
            if( $mhis['done_date'] == "0000-00-00 00:00:00"
                && !empty($mhis['self_id'])
                && $mhis['self_id'] != 0
                && $mhis['isdelete'] != 1
                && $mhis['methodid'] == 8
                && $mhis['ipid']  == $med_hisptory[$mhis['self_id']]['ipid']
                && $mhis['medicationid']  == $med_hisptory[$mhis['self_id']]['medicationid']
        
            ){
                $update[] = $ipid2epid[$mhis['ipid']].' > '.$mhis['ipid'].' - '.$mhis['id'].'> '.$mhis['self_id'].'--'.$med_hisptory[$mhis['self_id']]['done_date'];

                //re-update first row
                $frm1 = Doctrine::getTable('MedicationPatientHistory')->find($mhis['id']);
                if ($frm1 instanceof MedicationPatientHistory) {
                    $frm1->verlauf_hide = '1';
                    $frm1->done_date = $med_hisptory[$mhis['self_id']]['done_date'];
                    $frm1->save();
                }
            }
        }
               
        
        
        echo "Start:<pre/>";
        print_R($update);
        exit;
        
        
    }
    

    /**
     * TODO-2159
     * start and end invoices issues
     *  
     */
    public function updateisysAction(){
        exit;
        
        $invoices = Doctrine_Query::create()
        ->select('*')
        ->from('InvoiceSystem indexby id')
        ->fetchArray();
        
        
//         dd("VASILE", $invoices);
        foreach($invoices as $k_inv => $inv_data ){
            if($inv_data['end_active'] != "0000-00-00 00:00:00") {
                
                $frm1 = Doctrine::getTable('InvoiceSystem')->find($inv_data['id']);
                if ($frm1 instanceof InvoiceSystem) {
                    $frm1->end_active = date("Y-m-d 23:59:59",strtotime($inv_data['end_active']));
                    $frm1->invoice_end  = date("Y-m-d 23:59:59",strtotime($inv_data['invoice_end']));
                    $frm1->save();
                }
            }
        }
        
        echo "Done"; 
        exit;
    }
    
    
    
    
    private function  _ob_flush()
    {
        ob_end_flush();
        ob_flush();
        flush();
        ob_start();
    }
    
    
    
    public function todo2193bugfixAction()
    {
exit;
        $onlyThisIpids = [];
        $cfAfterThisDatetime = '2019-01-14 14:00:00';
        $limit = 0;
        
        $this->_user_data = [];
        $this->_client_data = [];
        $this->_ipid_location = [];
        
        $driving_time_limit = Pms_CommonData::driving_time_limit();
        
        echo "bugfix Distance<hr>\n";
    
        $runNOW = $this->getRequest()->getParam('runNOW', false);
        
        $salt = Zend_Registry::get('salt');
        
        if ( ! Doctrine_Core::getTable('ContactForms')->hasRelation('FormBlockDrivetimedoc')) {
            Doctrine_Core::getTable('ContactForms')->hasOne('FormBlockDrivetimedoc', ['local' => 'id', 'foreign' => 'contact_form_id']);
        }
        Doctrine_Core::getTable('ContactForms')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
        Doctrine_Core::getTable('ContactForms')->getRecordListener()->get('ContactForms2PatientCourseListener')->setOption('disabled', true);

        Doctrine_Core::getTable('FormBlockDrivetimedoc')->getRecordListener()->get('PostInsertWriteToPatientCourse')->setOption('disabled', true);
        Doctrine_Core::getTable('FormBlockDrivetimedoc')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
        
        Doctrine_Core::getTable('PatientCourse')->getRecordListener()->get('ContactForms2PatientCourseListener')->setOption('disabled', true);
        Doctrine_Core::getTable('PatientCourse')->getRecordListener()->get('PatientInsertListener')->setOption('disabled', true);
        Doctrine_Core::getTable('PatientCourse')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
        $listenerChain = Doctrine_Core::getTable('PatientCourse')->getRecordListener();
        $i = 0;
        while ($listener = $listenerChain->get($i))
        {
            $i++;
            $listener->setOption('disabled', true);
        }
        
        
        $cfsq = Doctrine_Core::getTable('ContactForms')->createQuery('cf')
        ->select('cf.* , fbd.*')
        ->leftJoin('cf.FormBlockDrivetimedoc fbd')
        ->where('create_date > ?', $cfAfterThisDatetime)
        ->andWhere('isdelete = 0')
//         ->fetchArray()
        ;
        
        if ( ! empty($onlyThisIpids) && is_array($onlyThisIpids)) {
            $cfsq->andWhereIn('ipid', $onlyThisIpids);
        }
        
        if ( ! empty($limit)) {
            $cfsq->limit((int)$limit); 
        }
        
        $cfs =  $cfsq->fetchArray();
        
        
        
        foreach ($cfs as $cf) {
            
            echo "<hr>cf id: {$cf['id']} ipid:{$cf['ipid']}<br>".PHP_EOL;
            
            $this->_ob_flush();
            
            //get pc,
            $pcs = Doctrine_Core::getTable('PatientCourse')->createQuery()
            ->select('id, aes_decrypt(tabname, :salt) as tabname, aes_decrypt(course_title, :salt) as course_title')
            ->where('ipid = :ipid')
            ->andWhere('done_name = aes_encrypt(:done_name, :salt)')
            ->andWhere('done_id = :cf_id' )
            ->andWhere("aes_decrypt(tabname, :salt) IN ( 'fahrtzeit_block', 'fahrtstreke_km_block', 'FormBlockDrivetimedoc')")
            ->fetchArray([
                'salt' => $salt,
                'ipid' => $cf['ipid'],
                'done_name' => ContactForms::PatientCourse_DONE_NAME,
                'cf_id' => $cf['id']
            ])
            ;
            
            if (empty ($pcs)) {
                continue;
            }
            
            $newRoute = $this->_getRoute($cf['ipid'], $cf['create_user'] ,$cf['create_date']);
            
            
            if (! empty($newRoute) && ! empty ($newRoute['length']) && ! empty ($newRoute['duration'])) {
                
                $newRoute['length'] = str_replace(' km', '', $newRoute['length']);
                
                
                //patientcourse that must be updated
                $pc_fahrtzeit_blocks_MUSTFIXT = false;
                $pc_fahrtstreke_km_blocks_MUSTFIXT = false;
                $pc_FormBlockDrivetimedoc_MUSTFIXT = false;
                
                
                
                $distanceFAIL0 = floatval(str_replace(',', '.', str_replace('.', '', $newRoute['__debug']['distance'])));
                $distanceFAIL = number_format($distanceFAIL0 * 0.001, '2');
                $distanceFAIL2 = number_format($distanceFAIL0 * 0.0001, '2');

                $durationFAIL  = floatval(str_replace(',', '.', str_replace('.', '', $newRoute['__debug']['duration'])));
                $real_duration = round($durationFAIL / 60); //in minutes only... google api returns seconds
                $real_duration2 = round($durationFAIL / 600); //in minutes only... google api returns seconds
                $durationFAIL = $real_duration > $driving_time_limit ? $driving_time_limit : $real_duration;
                $durationFAIL2 = $real_duration2 > $driving_time_limit ? $driving_time_limit : $real_duration2;
                
                echo "<b>distanceFAIL v1: {$distanceFAIL} distanceFAIL v2:{$distanceFAIL2}</b><br>";
                echo "<b>durationFAIL v1: {$durationFAIL} durationFAIL v2:{$durationFAIL2}</b><br>";
//                 dd($distanceFAIL,$durationFAIL);
                
                
//                 $newRoute['length'] = rand(180,300);
//                 $newRoute['duration'] = rand(10,180);

                print_r($newRoute);
                echo PHP_EOL. "<br>";
                
                
                
                if ($cf['fahrtzeit'] != $newRoute['duration']) {
                
                    if ($durationFAIL == $cf['fahrtzeit'] || $durationFAIL2 == $cf['fahrtzeit'] ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_fahrtzeit_blocks_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                    echo "<b>old fahrtzeit {$cf['fahrtzeit']} -> new duration {$newRoute['duration']}</b><br/>";
                
                }
                if ($cf['fahrtstreke_km'] != $newRoute['length']) {
                
                    if ($distanceFAIL == $cf['fahrtstreke_km'] || $distanceFAIL2 == $cf['fahrtstreke_km'] ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_fahrtstreke_km_blocks_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                    echo "<b>old fahrtstreke_km {$cf['fahrtstreke_km']} -> new length {$newRoute['length']}</b><br/>";
                }
                
                
                //update ContactForms
                if ( (! empty($cf['fahrtstreke_km']) || ! empty($cf['fahrtzeit'])) && ($pc_fahrtzeit_blocks_MUSTFIXT || $pc_fahrtstreke_km_blocks_MUSTFIXT) ) {
                    $q = Doctrine_Query::create()
                    ->update('ContactForms')
                    ->where("id = ?", $cf['id'])
                    ;
                    
                    if ($pc_fahrtzeit_blocks_MUSTFIXT) {
                        $q->set('fahrtzeit', "?" , [$newRoute['duration']]);
                    }
                    
                    if ($pc_fahrtstreke_km_blocks_MUSTFIXT) {
                        $q->set('fahrtstreke_km', "?" , [$newRoute['length']]);
                    }
                    
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }
                    
                    
                }
                
                
                if ($cf['FormBlockDrivetimedoc']['fahrtzeit1'] != $newRoute['duration']) {
                
                    if ($durationFAIL == $cf['FormBlockDrivetimedoc']['fahrtzeit1'] || $durationFAIL2 == $cf['FormBlockDrivetimedoc']['fahrtzeit1'] ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_FormBlockDrivetimedoc_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                
                    echo "<b>old FormBlockDrivetimedoc fahrtzeit1 {$cf['FormBlockDrivetimedoc']['fahrtzeit1']} -> new duration {$newRoute['duration']}</b><br/>";
                
                }
                if ($cf['FormBlockDrivetimedoc']['fahrtstreke_km1'] != $newRoute['length']) {
                
                    if ($distanceFAIL == $cf['FormBlockDrivetimedoc']['fahrtstreke_km1']  || $distanceFAIL2 == $cf['FormBlockDrivetimedoc']['fahrtstreke_km1']  ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_FormBlockDrivetimedoc_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                    echo "<b>old FormBlockDrivetimedoc fahrtstreke_km1 {$cf['FormBlockDrivetimedoc']['fahrtstreke_km1']} -> new length {$newRoute['length']}</b><br/>";
                }
                
                
                if ($pc_FormBlockDrivetimedoc_MUSTFIXT && ! empty($cf['FormBlockDrivetimedoc']['id'])) {
                    $q = Doctrine_Query::create()
                    ->update('FormBlockDrivetimedoc')
                    ->set('fahrtzeit1', "?" , $newRoute['duration'])
                    ->set('fahrtstreke_km1', "?" , $newRoute['length'])
                    ->where("id = ?", $cf['FormBlockDrivetimedoc']['id'])
                    ;
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": FIX: ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }
                }
                
                
                
                
                //update patientcourse
                $fahrtzeit_blocks = array_filter($pcs, function($row){return $row['tabname'] == 'fahrtzeit_block';});
                $fahrtstreke_km_blocks = array_filter($pcs, function($row){return $row['tabname'] == 'fahrtstreke_km_block';});
                $FormBlockDrivetimedoc = array_filter($pcs, function($row){return $row['tabname'] == 'FormBlockDrivetimedoc';});
                
                
                $fahrtzeit_blocks_IDS = array_column($fahrtzeit_blocks, 'id');
                $fahrtstreke_km_blocks_IDS = array_column($fahrtstreke_km_blocks, 'id');
                $FormBlockDrivetimedoc_IDS = array_column($FormBlockDrivetimedoc, 'id');
                
                
                
                
                
                //$fahrtstreke_km_blocks
                if ( ! empty($fahrtstreke_km_blocks_IDS) && $pc_fahrtstreke_km_blocks_MUSTFIXT) {
                    $course_title = "Fahrtstrecke: " . $newRoute['length'] ;
                    if ($cf['expert_accompanied'] === 'yes') {
                        $course_title .= PHP_EOL ."Begleitung durch eine Ligetis Fachkraft: Ja";
                    } elseif($cf['expert_accompanied'] === 'no') {
                        $course_title .= PHP_EOL ."Begleitung durch eine Ligetis Fachkraft: Nein";
                    }
                    $q = Doctrine_Query::create()
                    ->update('PatientCourse')
                    ->set('course_title', "AES_ENCRYPT(?, ?)" , [$course_title, $salt])
                    ->whereIn("id", $fahrtstreke_km_blocks_IDS)
                    ;
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": {$course_title} ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }                    
                }
                
                //$fahrtzeit_blocks
                if ( ! empty($fahrtzeit_blocks_IDS) && $pc_fahrtzeit_blocks_MUSTFIXT) {
                    $course_title = "Fahrtzeit: " . $newRoute['duration'];
                    
                    $q = Doctrine_Query::create()
                    ->update('PatientCourse')
                    ->set('course_title', "AES_ENCRYPT(?, ?)" , [$course_title, $salt])
                    ->whereIn("id", $fahrtzeit_blocks_IDS)
                    ;
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": {$course_title} ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }
                }
                
                
                
                if ( ! empty($FormBlockDrivetimedoc_IDS) && $pc_FormBlockDrivetimedoc_MUSTFIXT) {
                    $course_title_lines = [];
                    $course_title_lines[] = "Fahrtzeit:" . $newRoute['duration'] ;
                    $course_title_lines[] = "Fahrtstrecke:" . $newRoute['length'] ;
                    
                    if ( ! empty($cf['FormBlockDrivetimedoc']['fahrt_doc1'])) {
                        $course_title_lines[] = "Dokumentationszeit:" . $cf['FormBlockDrivetimedoc']['fahrt_doc1'] ;
                    }
                    $course_title = implode("\n", $course_title_lines);
                    
                    $q = Doctrine_Query::create()
                    ->update('PatientCourse')
                    ->set('course_title', "AES_ENCRYPT(?, ?)" , [$course_title, $salt])
                    ->whereIn("id", $FormBlockDrivetimedoc_IDS)
                    ;
                    
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": {$course_title} ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }

                }
                
            }            
            //get location at that datetime
        }
        

        echo "<br>TOTAL cf's processed : " .count($cfs);
        
    
        if (! $runNOW) {
            echo  "<hr><a href='?runNOW=1'>click 2 LIVE RUN</a>". PHP_EOL , "<br/>";
        }
        die("<hr>The NinjaTurtle has crossed the Finish line");
    
    }
    
    
    
    public function todo2193bugfixnurseAction()
    {
	exit;        
        $onlyThisIpids_a = array( );
      
        $onlyThisIpids = []; 
//         $onlyThisIpids = ["fa7fba60c27379bce6edec3f76c61ecceb1e5c93"]; 
//         $onlyThisIpids = ["ed6fe80535d2a3c101de7a9a9cefb4e09f8fcfd5"]; 
//         $onlyThisIpids = ["a28b13086140b397403ff7ae3119b75a5610fd47"]; 
      
        $cfAfterThisDatetime = '2019-03-14 15:00:00';
        $limit = 0;
        
        $this->_user_data = [];
        $this->_client_data = [];
        $this->_ipid_location = [];
        
        $driving_time_limit = Pms_CommonData::driving_time_limit();
        
        echo "bugfix Distance<hr>\n";
    
        $runNOW = $this->getRequest()->getParam('runNOW', false);
        
        $salt = Zend_Registry::get('salt');
        
        Doctrine_Core::getTable('KvnoNurse')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
        
        Doctrine_Core::getTable('PatientCourse')->getRecordListener()->get('PatientInsertListener')->setOption('disabled', true);
        Doctrine_Core::getTable('PatientCourse')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
        $listenerChain = Doctrine_Core::getTable('PatientCourse')->getRecordListener();
        $i = 0;
        while ($listener = $listenerChain->get($i))
        {
            $i++;
            $listener->setOption('disabled', true);
        }
        
        
        $cfsq = Doctrine_Core::getTable('KvnoNurse')->createQuery('cf')
        ->select('cf.*  ')
        ->where('create_date > ?', $cfAfterThisDatetime)
        ->andWhere('isdelete = 0')
//         ->fetchArray()
        ;
        
        if ( ! empty($onlyThisIpids) && is_array($onlyThisIpids)) {
            $cfsq->andWhereIn('ipid', $onlyThisIpids);
        }
        
        if ( ! empty($limit)) {
            $cfsq->limit((int)$limit); 
        }
        
        $cfs =  $cfsq->fetchArray();
        
        
        
        foreach ($cfs as $cf) {
            
            echo "<hr>cf id: {$cf['id']} ipid:{$cf['ipid']}<br>".PHP_EOL;
            
            $this->_ob_flush();
            
            //get pc,
            $pcs = Doctrine_Core::getTable('PatientCourse')->createQuery()
            ->select('id, aes_decrypt(tabname, :salt) as tabname, aes_decrypt(course_title, :salt) as course_title,aes_decrypt(course_type, :salt) as course_type')
            ->where('ipid = :ipid')
            ->andWhere(' done_name = aes_encrypt(:done_name, :salt) OR  done_name = aes_encrypt(:done_name2, :salt) OR  done_name = aes_encrypt(:done_name3, :salt) OR  done_name = aes_encrypt(:done_name4, :salt) ')
            ->andWhere('done_id = :cf_id' )
            ->fetchArray([
                'salt' => $salt,
                'ipid' => $cf['ipid'],
                'done_name' => "kvno_nurse_form",
                'done_name2' => "lvn_nurse_form",
                'done_name3' => "wl_nurse_form",
                'done_name4' => "sakvno_nurse_form",
                'cf_id' => $cf['id']
            ])
            ;
            
            if (empty ($pcs)) {
                continue;
            }
            
//             print_r($pcs);
            
            $newRoute = $this->_getRoute($cf['ipid'], $cf['create_user'] ,$cf['create_date']);
            
            
            if (! empty($newRoute) && ! empty ($newRoute['length']) && ! empty ($newRoute['duration'])) {
                
                $newRoute['length'] = str_replace(' km', '', $newRoute['length']);
                
                
                //patientcourse that must be updated
                $pc_fahrtzeit_blocks_MUSTFIXT = false;
                $pc_fahrtstreke_km_blocks_MUSTFIXT = false;
                $pc_FormBlockDrivetimedoc_MUSTFIXT = false;
                
                
                
                $distanceFAIL0 = floatval(str_replace(',', '.', str_replace('.', '', $newRoute['__debug']['distance'])));
                $distanceFAIL = number_format($distanceFAIL0 * 0.001, '2');
                $distanceFAIL2 = number_format($distanceFAIL0 * 0.0001, '2');

                $durationFAIL  = floatval(str_replace(',', '.', str_replace('.', '', $newRoute['__debug']['duration'])));
                $real_duration = round($durationFAIL / 60); //in minutes only... google api returns seconds
                $real_duration2 = round($durationFAIL / 600); //in minutes only... google api returns seconds
                $durationFAIL = $real_duration > $driving_time_limit ? $driving_time_limit : $real_duration;
                $durationFAIL2 = $real_duration2 > $driving_time_limit ? $driving_time_limit : $real_duration2;
                
                echo "<b>distanceFAIL v1: {$distanceFAIL} distanceFAIL v2:{$distanceFAIL2}</b><br>";
                echo "<b>durationFAIL v1: {$durationFAIL} durationFAIL v2:{$durationFAIL2}</b><br>";
//                 dd($distanceFAIL,$durationFAIL);
                
                
//                 $newRoute['length'] = rand(180,300);
//                 $newRoute['duration'] = rand(10,180);

                print_r($newRoute);
                echo PHP_EOL. "<br>";
                
                
                
                if ($cf['fahrtzeit'] != $newRoute['duration']) {
                
                    if ($durationFAIL == $cf['fahrtzeit'] || $durationFAIL2 == $cf['fahrtzeit'] ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_fahrtzeit_blocks_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                    echo "<b>old fahrtzeit {$cf['fahrtzeit']} -> new duration {$newRoute['duration']}</b><br/>";
                
                }
                if ($cf['fahrtstreke_km'] != $newRoute['length']) {
                         $cf['fahrtstreke_km'] = str_replace("km", "", $cf['fahrtstreke_km']);
                         $cf['fahrtstreke_km'] = trim($cf['fahrtstreke_km']);
                    
                    if ($distanceFAIL == $cf['fahrtstreke_km'] || $distanceFAIL2 == $cf['fahrtstreke_km'] ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_fahrtstreke_km_blocks_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                    echo "<b>old fahrtstreke_km {$cf['fahrtstreke_km']} -> new length {$newRoute['length']}</b><br/>";
                }
                
                
                //update  Forms
                if ( (! empty($cf['fahrtstreke_km']) || ! empty($cf['fahrtzeit'])) && ($pc_fahrtzeit_blocks_MUSTFIXT || $pc_fahrtstreke_km_blocks_MUSTFIXT) ) {
                    $q = Doctrine_Query::create()
                    ->update('KvnoNurse')
                    ->where("id = ?", $cf['id'])
                    ;
                    
                    if ($pc_fahrtzeit_blocks_MUSTFIXT) {
                        $q->set('fahrtzeit', "?" , [$newRoute['duration']]);
                    }
                    
                    if ($pc_fahrtstreke_km_blocks_MUSTFIXT) {
                        $q->set('fahrtstreke_km', "?" , [$newRoute['length']]);
                    }
                    
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }
                    
                    
                }
                
                
        
          
                
                
                
                
                //update patientcourse
                
//                 strpos($row['course_title'] , "Fahrtzeit: ") === 0 ) {
//                     //update fahrtzeit_block
//                     $tabname = $fahrtzeit_block;
//                     // 	                        dd($row['course_type'], $row, $tabname, $cnt);
//                 }
                $fahrtzeit_blocks = array();
                $fahrtstreke_km_blocks = array();
                foreach($pcs as $k=>$row){
//                     print_R($row['course_type']);
//                     var_Dump(strpos($row['course_title'] , "Fahrtzeit"));
                    if($row['course_type'] == "K" && strpos($row['course_title'] , "Fahrtzeit") === 0 ) {
                        $fahrtzeit_blocks[] = $row; 
                    }
                    if($row['course_type'] == "K" && strpos($row['course_title'] , "Fahrtstrecke") === 0) {
                        $fahrtstreke_km_blocks[] = $row; 
                    }
                }
                echo "<br/>";
                print_R($fahrtzeit_blocks);
                echo "<br/>";
                print_R($fahrtstreke_km_blocks);
                echo "<br/>";
//                 $fahrtzeit_blocks = array_filter($pcs, function($row){return $row['tabname'] == 'fahrtzeit_block';});
//                 $fahrtstreke_km_blocks = array_filter($pcs, function($row){return $row['tabname'] == 'fahrtstreke_km_block';});
                
                
                $fahrtzeit_blocks_IDS = array_column($fahrtzeit_blocks, 'id');
                $fahrtstreke_km_blocks_IDS = array_column($fahrtstreke_km_blocks, 'id');
                
                
                
                
                
                //$fahrtstreke_km_blocks
                if ( ! empty($fahrtstreke_km_blocks_IDS) && $pc_fahrtstreke_km_blocks_MUSTFIXT) {
                    $course_title = "Fahrtstrecke: " . $newRoute['length'] ;
                    if ($cf['expert_accompanied'] === 'yes') {
                        $course_title .= PHP_EOL ."Begleitung durch eine Ligetis Fachkraft: Ja";
                    } elseif($cf['expert_accompanied'] === 'no') {
                        $course_title .= PHP_EOL ."Begleitung durch eine Ligetis Fachkraft: Nein";
                    }
                    $q = Doctrine_Query::create()
                    ->update('PatientCourse')
                    ->set('course_title', "AES_ENCRYPT(?, ?)" , [$course_title, $salt])
                    ->whereIn("id", $fahrtstreke_km_blocks_IDS)
                    ;
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": {$course_title} ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }                    
                }
                
                //$fahrtzeit_blocks
                if ( ! empty($fahrtzeit_blocks_IDS) && $pc_fahrtzeit_blocks_MUSTFIXT) {
                    $course_title = "Fahrtzeit: " . $newRoute['duration'];
                    
                    $q = Doctrine_Query::create()
                    ->update('PatientCourse')
                    ->set('course_title', "AES_ENCRYPT(?, ?)" , [$course_title, $salt])
                    ->whereIn("id", $fahrtzeit_blocks_IDS)
                    ;
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": {$course_title} ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }
                }
                
  
            }            
            //get location at that datetime
        }
        

        echo "<br>TOTAL cf's processed : " .count($cfs);
        
    
        if (! $runNOW) {
            echo  "<hr><a href='?runNOW=1'>click 2 LIVE RUN</a>". PHP_EOL , "<br/>";
        }
        die("<hr>The NinjaTurtle has crossed the Finish line");
    
    }
    
    
    
    public function todo2193bugfixdoctorAction()
    {
	exit;
        
        $onlyThisIpids_a = array( );
      
        $onlyThisIpids = []; 
//         $onlyThisIpids = ["fa7fba60c27379bce6edec3f76c61ecceb1e5c93"]; 
//         $onlyThisIpids = ["ed6fe80535d2a3c101de7a9a9cefb4e09f8fcfd5"]; 
//         $onlyThisIpids = ["db1e48d31f51c82ac60b522ec51375cae4b962c6"]; 
      
        $cfAfterThisDatetime = '2019-03-14 15:00:00';
        $limit = 0;
        
        $this->_user_data = [];
        $this->_client_data = [];
        $this->_ipid_location = [];
        
        $driving_time_limit = Pms_CommonData::driving_time_limit();
        
        echo "bugfix Distance<hr>\n";
    
        $runNOW = $this->getRequest()->getParam('runNOW', false);
        
        $salt = Zend_Registry::get('salt');
        
        Doctrine_Core::getTable('KvnoDoctor')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
        
        Doctrine_Core::getTable('PatientCourse')->getRecordListener()->get('PatientInsertListener')->setOption('disabled', true);
        Doctrine_Core::getTable('PatientCourse')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
        $listenerChain = Doctrine_Core::getTable('PatientCourse')->getRecordListener();
        $i = 0;
        while ($listener = $listenerChain->get($i))
        {
            $i++;
            $listener->setOption('disabled', true);
        }
        
        
        $cfsq = Doctrine_Core::getTable('KvnoDoctor')->createQuery('cf')
        ->select('cf.*  ')
        ->where('create_date > ?', $cfAfterThisDatetime)
        ->andWhere('isdelete = 0')
//         ->fetchArray()
        ;
        
        if ( ! empty($onlyThisIpids) && is_array($onlyThisIpids)) {
            $cfsq->andWhereIn('ipid', $onlyThisIpids);
        }
        
        if ( ! empty($limit)) {
            $cfsq->limit((int)$limit); 
        }
        
        $cfs =  $cfsq->fetchArray();
        
        
        
        foreach ($cfs as $cf) {
            
            echo "<hr>cf id: {$cf['id']} ipid:{$cf['ipid']}<br>".PHP_EOL;
            
            $this->_ob_flush();
            
            //get pc,
            $pcs = Doctrine_Core::getTable('PatientCourse')->createQuery()
            ->select('id, aes_decrypt(tabname, :salt) as tabname, aes_decrypt(course_title, :salt) as course_title,aes_decrypt(course_type, :salt) as course_type')
            ->where('ipid = :ipid')
            ->andWhere(' done_name = aes_encrypt(:done_name, :salt) OR  done_name = aes_encrypt(:done_name2, :salt) OR  done_name = aes_encrypt(:done_name3, :salt)   ')
            ->andWhere('done_id = :cf_id' )
            ->fetchArray([
                'salt' => $salt,
                'ipid' => $cf['ipid'],
                'done_name' => "kvno_doctor_form",
                'done_name2' => "sakvno_doctor_form",
                'done_name3' => "wl_doctor_form",
                'cf_id' => $cf['id']
            ])
            ;
            if (empty ($pcs)) {
                continue;
            }
            
//             print_r($pcs);
            
            $newRoute = $this->_getRoute($cf['ipid'], $cf['create_user'] ,$cf['create_date']);
            
            
            if (! empty($newRoute) && ! empty ($newRoute['length']) && ! empty ($newRoute['duration'])) {
                
                $newRoute['length'] = str_replace(' km', '', $newRoute['length']);
                
                
                //patientcourse that must be updated
                $pc_fahrtzeit_blocks_MUSTFIXT = false;
                $pc_fahrtstreke_km_blocks_MUSTFIXT = false;
                $pc_FormBlockDrivetimedoc_MUSTFIXT = false;
                
                
                
                $distanceFAIL0 = floatval(str_replace(',', '.', str_replace('.', '', $newRoute['__debug']['distance'])));
                $distanceFAIL = number_format($distanceFAIL0 * 0.001, '2');
                $distanceFAIL2 = number_format($distanceFAIL0 * 0.0001, '2');

                $durationFAIL  = floatval(str_replace(',', '.', str_replace('.', '', $newRoute['__debug']['duration'])));
                $real_duration = round($durationFAIL / 60); //in minutes only... google api returns seconds
                $real_duration2 = round($durationFAIL / 600); //in minutes only... google api returns seconds
                $durationFAIL = $real_duration > $driving_time_limit ? $driving_time_limit : $real_duration;
                $durationFAIL2 = $real_duration2 > $driving_time_limit ? $driving_time_limit : $real_duration2;
                
                echo "<b>distanceFAIL v1: {$distanceFAIL} distanceFAIL v2:{$distanceFAIL2}</b><br>";
                echo "<b>durationFAIL v1: {$durationFAIL} durationFAIL v2:{$durationFAIL2}</b><br>";
//                 dd($distanceFAIL,$durationFAIL);
                
                
//                 $newRoute['length'] = rand(180,300);
//                 $newRoute['duration'] = rand(10,180);

                print_r($newRoute);
                echo PHP_EOL. "<br>";
                
                
                
                if ($cf['fahrtzeit'] != $newRoute['duration']) {
                
                    if ($durationFAIL == $cf['fahrtzeit'] || $durationFAIL2 == $cf['fahrtzeit'] ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_fahrtzeit_blocks_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                    echo "<b>old fahrtzeit {$cf['fahrtzeit']} -> new duration {$newRoute['duration']}</b><br/>";
                
                }
                if ($cf['fahrtstreke_km'] != $newRoute['length']) {
                         $cf['fahrtstreke_km'] = str_replace("km", "", $cf['fahrtstreke_km']);
                         $cf['fahrtstreke_km'] = trim($cf['fahrtstreke_km']);
                    
                    if ($distanceFAIL == $cf['fahrtstreke_km'] || $distanceFAIL2 == $cf['fahrtstreke_km'] ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_fahrtstreke_km_blocks_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                    echo "<b>old fahrtstreke_km {$cf['fahrtstreke_km']} -> new length {$newRoute['length']}</b><br/>";
                }
                
                
                //update  Forms
                if ( (! empty($cf['fahrtstreke_km']) || ! empty($cf['fahrtzeit'])) && ($pc_fahrtzeit_blocks_MUSTFIXT || $pc_fahrtstreke_km_blocks_MUSTFIXT) ) {
                    $q = Doctrine_Query::create()
                    ->update('KvnoDoctor')
                    ->where("id = ?", $cf['id'])
                    ;
                    
                    if ($pc_fahrtzeit_blocks_MUSTFIXT) {
                        $q->set('fahrtzeit', "?" , [$newRoute['duration']]);
                    }
                    
                    if ($pc_fahrtstreke_km_blocks_MUSTFIXT) {
                        $q->set('fahrtstreke_km', "?" , [$newRoute['length']]);
                    }
                    
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }
                    
                    
                }
                
                
        
          
                
                
                
                
                //update patientcourse
                
//                 strpos($row['course_title'] , "Fahrtzeit: ") === 0 ) {
//                     //update fahrtzeit_block
//                     $tabname = $fahrtzeit_block;
//                     // 	                        dd($row['course_type'], $row, $tabname, $cnt);
//                 }
                $fahrtzeit_blocks = array();
                $fahrtstreke_km_blocks = array();
                foreach($pcs as $k=>$row){
//                     print_R($row['course_type']);
//                     var_Dump(strpos($row['course_title'] , "Fahrtzeit"));
                    if($row['course_type'] == "K" && strpos($row['course_title'] , "Fahrtzeit") === 0 ) {
                        $fahrtzeit_blocks[] = $row; 
                    }
                    if($row['course_type'] == "K" && strpos($row['course_title'] , "Fahrtstrecke") === 0) {
                        $fahrtstreke_km_blocks[] = $row; 
                    }
                }
                echo "<br/>";
                print_R($fahrtzeit_blocks);
                echo "<br/>";
                print_R($fahrtstreke_km_blocks);
                echo "<br/>";
//                 $fahrtzeit_blocks = array_filter($pcs, function($row){return $row['tabname'] == 'fahrtzeit_block';});
//                 $fahrtstreke_km_blocks = array_filter($pcs, function($row){return $row['tabname'] == 'fahrtstreke_km_block';});
                
                
                $fahrtzeit_blocks_IDS = array_column($fahrtzeit_blocks, 'id');
                $fahrtstreke_km_blocks_IDS = array_column($fahrtstreke_km_blocks, 'id');
                
                
                
                
                
                //$fahrtstreke_km_blocks
                if ( ! empty($fahrtstreke_km_blocks_IDS) && $pc_fahrtstreke_km_blocks_MUSTFIXT) {
                    $course_title = "Fahrtstrecke: " . $newRoute['length'] ;
                    if ($cf['expert_accompanied'] === 'yes') {
                        $course_title .= PHP_EOL ."Begleitung durch eine Ligetis Fachkraft: Ja";
                    } elseif($cf['expert_accompanied'] === 'no') {
                        $course_title .= PHP_EOL ."Begleitung durch eine Ligetis Fachkraft: Nein";
                    }
                    $q = Doctrine_Query::create()
                    ->update('PatientCourse')
                    ->set('course_title', "AES_ENCRYPT(?, ?)" , [$course_title, $salt])
                    ->whereIn("id", $fahrtstreke_km_blocks_IDS)
                    ;
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": {$course_title} ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }                    
                }
                
                //$fahrtzeit_blocks
                if ( ! empty($fahrtzeit_blocks_IDS) && $pc_fahrtzeit_blocks_MUSTFIXT) {
                    $course_title = "Fahrtzeit: " . $newRoute['duration'];
                    
                    $q = Doctrine_Query::create()
                    ->update('PatientCourse')
                    ->set('course_title', "AES_ENCRYPT(?, ?)" , [$course_title, $salt])
                    ->whereIn("id", $fahrtzeit_blocks_IDS)
                    ;
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": {$course_title} ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }
                }
                
  
            }            
            //get location at that datetime
        }
        

        echo "<br>TOTAL cf's processed : " .count($cfs);
        
    
        if (! $runNOW) {
            echo  "<hr><a href='?runNOW=1'>click 2 LIVE RUN</a>". PHP_EOL , "<br/>";
        }
        die("<hr>The NinjaTurtle has crossed the Finish line");
    
    }
    
    
    
    public function todo2193bugfixcoordAction()
    {
    exit;
        
        $onlyThisIpids_a = array( );
      
        $onlyThisIpids = []; 
//         $onlyThisIpids = ["fa7fba60c27379bce6edec3f76c61ecceb1e5c93"]; 
//         $onlyThisIpids = ["ed6fe80535d2a3c101de7a9a9cefb4e09f8fcfd5"]; 
//         $onlyThisIpids = ["db2fe02820df92292d35414b1541f39ef23b6678"]; 
      
        $cfAfterThisDatetime = '2019-03-14 15:00:00';
        $limit = 0;
        
        $this->_user_data = [];
        $this->_client_data = [];
        $this->_ipid_location = [];
        
        $driving_time_limit = Pms_CommonData::driving_time_limit();
        
        echo "bugfix Distance<hr>\n";
    
        $runNOW = $this->getRequest()->getParam('runNOW', false);
        
        $salt = Zend_Registry::get('salt');
        
        Doctrine_Core::getTable('VisitKoordination')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
        
        Doctrine_Core::getTable('PatientCourse')->getRecordListener()->get('PatientInsertListener')->setOption('disabled', true);
        Doctrine_Core::getTable('PatientCourse')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
        $listenerChain = Doctrine_Core::getTable('PatientCourse')->getRecordListener();
        $i = 0;
        while ($listener = $listenerChain->get($i))
        {
            $i++;
            $listener->setOption('disabled', true);
        }
        
        
        $cfsq = Doctrine_Core::getTable('VisitKoordination')->createQuery('cf')
        ->select('cf.*  ')
        ->where('create_date > ?', $cfAfterThisDatetime)
//         ->andWhere('isdelete = 0')
//         ->fetchArray()
        ;
        
        if ( ! empty($onlyThisIpids) && is_array($onlyThisIpids)) {
            $cfsq->andWhereIn('ipid', $onlyThisIpids);
        }
        
        if ( ! empty($limit)) {
            $cfsq->limit((int)$limit); 
        }
        
        $cfs =  $cfsq->fetchArray();
        
        
        
        foreach ($cfs as $cf) {
            
            echo "<hr>cf id: {$cf['id']} ipid:{$cf['ipid']}<br>".PHP_EOL;
            
            $this->_ob_flush();
            
            //get pc,
            $pcs = Doctrine_Core::getTable('PatientCourse')->createQuery()
            ->select('id, aes_decrypt(tabname, :salt) as tabname, aes_decrypt(course_title, :salt) as course_title,aes_decrypt(course_type, :salt) as course_type')
            ->where('ipid = :ipid')
            ->andWhere(' done_name = aes_encrypt(:done_name, :salt) ')
            ->andWhere('done_id = :cf_id' )
            ->fetchArray([
                'salt' => $salt,
                'ipid' => $cf['ipid'],
                'done_name' => "visit_koordination_form",
                'cf_id' => $cf['id']
            ])
            ;
            if (empty ($pcs)) {
                continue;
            }
            
//             print_r($pcs);
            
            $newRoute = $this->_getRoute($cf['ipid'], $cf['create_user'] ,$cf['create_date']);
            
            
            if (! empty($newRoute) && ! empty ($newRoute['length']) && ! empty ($newRoute['duration'])) {
                
                $newRoute['length'] = str_replace(' km', '', $newRoute['length']);
                
                
                //patientcourse that must be updated
                $pc_fahrtzeit_blocks_MUSTFIXT = false;
                $pc_fahrtstreke_km_blocks_MUSTFIXT = false;
                $pc_FormBlockDrivetimedoc_MUSTFIXT = false;
                
                
                
                $distanceFAIL0 = floatval(str_replace(',', '.', str_replace('.', '', $newRoute['__debug']['distance'])));
                $distanceFAIL = number_format($distanceFAIL0 * 0.001, '2');
                $distanceFAIL2 = number_format($distanceFAIL0 * 0.0001, '2');

                $durationFAIL  = floatval(str_replace(',', '.', str_replace('.', '', $newRoute['__debug']['duration'])));
                $real_duration = round($durationFAIL / 60); //in minutes only... google api returns seconds
                $real_duration2 = round($durationFAIL / 600); //in minutes only... google api returns seconds
                $durationFAIL = $real_duration > $driving_time_limit ? $driving_time_limit : $real_duration;
                $durationFAIL2 = $real_duration2 > $driving_time_limit ? $driving_time_limit : $real_duration2;
                
                echo "<b>distanceFAIL v1: {$distanceFAIL} distanceFAIL v2:{$distanceFAIL2}</b><br>";
                echo "<b>durationFAIL v1: {$durationFAIL} durationFAIL v2:{$durationFAIL2}</b><br>";
//                 dd($distanceFAIL,$durationFAIL);
                
                
//                 $newRoute['length'] = rand(180,300);
//                 $newRoute['duration'] = rand(10,180);

                print_r($newRoute);
                echo PHP_EOL. "<br>";
                
                
                
                if ($cf['fahrtzeit'] != $newRoute['duration']) {
                
                    if ($durationFAIL == $cf['fahrtzeit'] || $durationFAIL2 == $cf['fahrtzeit'] ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_fahrtzeit_blocks_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                    echo "<b>old fahrtzeit {$cf['fahrtzeit']} -> new duration {$newRoute['duration']}</b><br/>";
                
                }
                if ($cf['fahrtstreke_km'] != $newRoute['length']) {
                         $cf['fahrtstreke_km'] = str_replace("km", "", $cf['fahrtstreke_km']);
                         $cf['fahrtstreke_km'] = trim($cf['fahrtstreke_km']);
                    
                    if ($distanceFAIL == $cf['fahrtstreke_km'] || $distanceFAIL2 == $cf['fahrtstreke_km'] ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_fahrtstreke_km_blocks_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                    echo "<b>old fahrtstreke_km {$cf['fahrtstreke_km']} -> new length {$newRoute['length']}</b><br/>";
                }
                
                
                //update  Forms
                if ( (! empty($cf['fahrtstreke_km']) || ! empty($cf['fahrtzeit'])) && ($pc_fahrtzeit_blocks_MUSTFIXT || $pc_fahrtstreke_km_blocks_MUSTFIXT) ) {
                    $q = Doctrine_Query::create()
                    ->update('VisitKoordination')
                    ->where("id = ?", $cf['id'])
                    ;
                    
                    if ($pc_fahrtzeit_blocks_MUSTFIXT) {
                        $q->set('fahrtzeit', "?" , [$newRoute['duration']]);
                    }
                    
                    if ($pc_fahrtstreke_km_blocks_MUSTFIXT) {
                        $q->set('fahrtstreke_km', "?" , [$newRoute['length']]);
                    }
                    
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }
                    
                    
                }
                
                
        
          
                
                
                
                
                //update patientcourse
                
//                 strpos($row['course_title'] , "Fahrtzeit: ") === 0 ) {
//                     //update fahrtzeit_block
//                     $tabname = $fahrtzeit_block;
//                     // 	                        dd($row['course_type'], $row, $tabname, $cnt);
//                 }
                $fahrtzeit_blocks = array();
                $fahrtstreke_km_blocks = array();
                foreach($pcs as $k=>$row){
//                     print_R($row['course_type']);
//                     var_Dump(strpos($row['course_title'] , "Fahrtzeit"));
                    if($row['course_type'] == "K" && strpos($row['course_title'] , "Fahrtzeit") === 0 ) {
                        $fahrtzeit_blocks[] = $row; 
                    }
                    if($row['course_type'] == "K" && strpos($row['course_title'] , "Fahrtstrecke") === 0) {
                        $fahrtstreke_km_blocks[] = $row; 
                    }
                }
                echo "<br/>";
                print_R($fahrtzeit_blocks);
                echo "<br/>";
                print_R($fahrtstreke_km_blocks);
                echo "<br/>";
//                 $fahrtzeit_blocks = array_filter($pcs, function($row){return $row['tabname'] == 'fahrtzeit_block';});
//                 $fahrtstreke_km_blocks = array_filter($pcs, function($row){return $row['tabname'] == 'fahrtstreke_km_block';});
                
                
                $fahrtzeit_blocks_IDS = array_column($fahrtzeit_blocks, 'id');
                $fahrtstreke_km_blocks_IDS = array_column($fahrtstreke_km_blocks, 'id');
                
                
                
                
                
                //$fahrtstreke_km_blocks
                if ( ! empty($fahrtstreke_km_blocks_IDS) && $pc_fahrtstreke_km_blocks_MUSTFIXT) {
                    $course_title = "Fahrtstrecke: " . $newRoute['length'] ;
                    if ($cf['expert_accompanied'] === 'yes') {
                        $course_title .= PHP_EOL ."Begleitung durch eine Ligetis Fachkraft: Ja";
                    } elseif($cf['expert_accompanied'] === 'no') {
                        $course_title .= PHP_EOL ."Begleitung durch eine Ligetis Fachkraft: Nein";
                    }
                    $q = Doctrine_Query::create()
                    ->update('PatientCourse')
                    ->set('course_title', "AES_ENCRYPT(?, ?)" , [$course_title, $salt])
                    ->whereIn("id", $fahrtstreke_km_blocks_IDS)
                    ;
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": {$course_title} ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }                    
                }
                
                //$fahrtzeit_blocks
                if ( ! empty($fahrtzeit_blocks_IDS) && $pc_fahrtzeit_blocks_MUSTFIXT) {
                    $course_title = "Fahrtzeit: " . $newRoute['duration'];
                    
                    $q = Doctrine_Query::create()
                    ->update('PatientCourse')
                    ->set('course_title', "AES_ENCRYPT(?, ?)" , [$course_title, $salt])
                    ->whereIn("id", $fahrtzeit_blocks_IDS)
                    ;
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": {$course_title} ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }
                }
                
  
            }            
            //get location at that datetime
        }
        

        echo "<br>TOTAL cf's processed : " .count($cfs);
        
    
        if (! $runNOW) {
            echo  "<hr><a href='?runNOW=1'>click 2 LIVE RUN</a>". PHP_EOL , "<br/>";
        }
        die("<hr>The NinjaTurtle has crossed the Finish line");
    
    }
    
    
    
    public function todo2193bugfixbayAction()
    {
    exit;
        
        $onlyThisIpids_a = array( );
      
        $onlyThisIpids = []; 
//         $onlyThisIpids = ["6ce116047bdb50936da989f1bd26b88591330b32"]; 
      
        $cfAfterThisDatetime = '2019-03-14 15:00:00';
        $limit = 0;
        
        $this->_user_data = [];
        $this->_client_data = [];
        $this->_ipid_location = [];
        
        $driving_time_limit = Pms_CommonData::driving_time_limit();
        
        echo "bugfix Distance<hr>\n";
    
        $runNOW = $this->getRequest()->getParam('runNOW', false);
        
        $salt = Zend_Registry::get('salt');
        
        Doctrine_Core::getTable('BayernDoctorVisit')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
        
        Doctrine_Core::getTable('PatientCourse')->getRecordListener()->get('PatientInsertListener')->setOption('disabled', true);
        Doctrine_Core::getTable('PatientCourse')->getRecordListener()->get('TimestampListener')->setOption('disabled', true);
        $listenerChain = Doctrine_Core::getTable('PatientCourse')->getRecordListener();
        $i = 0;
        while ($listener = $listenerChain->get($i))
        {
            $i++;
            $listener->setOption('disabled', true);
        }
        
        
        $cfsq = Doctrine_Core::getTable('BayernDoctorVisit')->createQuery('cf')
        ->select('cf.*  ')
        ->where('create_date > ?', $cfAfterThisDatetime)
//         ->andWhere('isdelete = 0')
//         ->fetchArray()
        ;
        
        if ( ! empty($onlyThisIpids) && is_array($onlyThisIpids)) {
            $cfsq->andWhereIn('ipid', $onlyThisIpids);
        }
        
        if ( ! empty($limit)) {
            $cfsq->limit((int)$limit); 
        }
        
        $cfs =  $cfsq->fetchArray();
        
        
        
        foreach ($cfs as $cf) {
            
            echo "<hr>cf id: {$cf['id']} ipid:{$cf['ipid']}<br>".PHP_EOL;
            
            $this->_ob_flush();
            
            //get pc,
            $pcs = Doctrine_Core::getTable('PatientCourse')->createQuery()
            ->select('id, done_id,aes_decrypt(tabname, :salt) as tabname, aes_decrypt(course_title, :salt) as course_title,aes_decrypt(course_type, :salt) as course_type')
            ->where('ipid = :ipid')
            ->andWhere(' done_name = aes_encrypt(:done_name, :salt) ')
            ->andWhere('done_id = :cf_id' )
            ->fetchArray([
                'salt' => $salt,
                'ipid' => $cf['ipid'],
                'done_name' => "bayern_doctorvisit",
                'cf_id' => $cf['id']
            ])
            ;
            if (empty ($pcs)) {
                continue;
            }
            
//             print_r($pcs);
            
            $newRoute = $this->_getRoute($cf['ipid'], $cf['create_user'] ,$cf['create_date']);
            
            
            if (! empty($newRoute) && ! empty ($newRoute['length']) && ! empty ($newRoute['duration'])) {
                
                $newRoute['length'] = str_replace(' km', '', $newRoute['length']);
                
                
                //patientcourse that must be updated
                $pc_fahrtzeit_blocks_MUSTFIXT = false;
                $pc_fahrtstreke_km_blocks_MUSTFIXT = false;
                $pc_FormBlockDrivetimedoc_MUSTFIXT = false;
                
                
                
                $distanceFAIL0 = floatval(str_replace(',', '.', str_replace('.', '', $newRoute['__debug']['distance'])));
                $distanceFAIL = number_format($distanceFAIL0 * 0.001, '2');
                $distanceFAIL2 = number_format($distanceFAIL0 * 0.0001, '2');

                $durationFAIL  = floatval(str_replace(',', '.', str_replace('.', '', $newRoute['__debug']['duration'])));
                $real_duration = round($durationFAIL / 60); //in minutes only... google api returns seconds
                $real_duration2 = round($durationFAIL / 600); //in minutes only... google api returns seconds
                $durationFAIL = $real_duration > $driving_time_limit ? $driving_time_limit : $real_duration;
                $durationFAIL2 = $real_duration2 > $driving_time_limit ? $driving_time_limit : $real_duration2;
                
                echo "<b>distanceFAIL v1: {$distanceFAIL} distanceFAIL v2:{$distanceFAIL2}</b><br>";
                echo "<b>durationFAIL v1: {$durationFAIL} durationFAIL v2:{$durationFAIL2}</b><br>";
//                 dd($distanceFAIL,$durationFAIL);
                
                
//                 $newRoute['length'] = rand(180,300);
//                 $newRoute['duration'] = rand(10,180);

                print_r($newRoute);
                echo PHP_EOL. "<br>";
                
                
                
                if ($cf['fahrtzeit'] != $newRoute['duration']) {
                
                    if ($durationFAIL == $cf['fahrtzeit'] || $durationFAIL2 == $cf['fahrtzeit'] ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_fahrtzeit_blocks_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                    echo "<b>old fahrtzeit {$cf['fahrtzeit']} -> new duration {$newRoute['duration']}</b><br/>";
                
                }
                if ($cf['fahrtstreke_km'] != $newRoute['length']) {
                         $cf['fahrtstreke_km'] = str_replace("km", "", $cf['fahrtstreke_km']);
                         $cf['fahrtstreke_km'] = trim($cf['fahrtstreke_km']);
                    
                    if ($distanceFAIL == $cf['fahrtstreke_km'] || $distanceFAIL2 == $cf['fahrtstreke_km'] ) {
                        echo "<font color='red'>this must be fixed</font>";
                        $pc_fahrtstreke_km_blocks_MUSTFIXT = true;
                    } else {
                        echo "<font color='green'>this will not be fixed, is ok</font>";
                    }
                    echo "<b>old fahrtstreke_km {$cf['fahrtstreke_km']} -> new length {$newRoute['length']}</b><br/>";
                }
                
                
                //update  Forms
                if ( (! empty($cf['fahrtstreke_km']) || ! empty($cf['fahrtzeit'])) && ($pc_fahrtzeit_blocks_MUSTFIXT || $pc_fahrtstreke_km_blocks_MUSTFIXT) ) {
                    $q = Doctrine_Query::create()
                    ->update('BayernDoctorVisit')
                    ->where("id = ?", $cf['id'])
                    ;
                    
                    if ($pc_fahrtzeit_blocks_MUSTFIXT) {
                        $q->set('fahrtzeit', "?" , [$newRoute['duration']]);
                    }
                    
                    if ($pc_fahrtstreke_km_blocks_MUSTFIXT) {
                        $q->set('fahrtstreke_km', "?" , [$newRoute['length']]);
                    }
                    
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }
                    
                    
                }
                
                
        
          
                
                
                
                
                //update patientcourse
                
//                 strpos($row['course_title'] , "Fahrtzeit: ") === 0 ) {
//                     //update fahrtzeit_block
//                     $tabname = $fahrtzeit_block;
//                     // 	                        dd($row['course_type'], $row, $tabname, $cnt);
//                 }
                $fahrtzeit_blocks = array();
                $fahrtstreke_km_blocks = array();
                foreach($pcs as $k=>$row){
//                     print_R($row['course_type']);
//                     var_Dump(strpos($row['course_title'] , "Fahrtzeit"));
                    if($row['course_type'] == "K" && strpos($row['course_title'] , "Fahrtzeit") === 0 ) {
                        $fahrtzeit_blocks[] = $row; 
                    }
                    if($row['course_type'] == "K" && strpos($row['course_title'] , "Fahrtstrecke") === 0) {
                        $fahrtstreke_km_blocks[] = $row; 
                    }
                }
                echo "<br/>";
                print_R($fahrtzeit_blocks);
                echo "<br/>";
                print_R($fahrtstreke_km_blocks);
                echo "<br/>";
//                 $fahrtzeit_blocks = array_filter($pcs, function($row){return $row['tabname'] == 'fahrtzeit_block';});
//                 $fahrtstreke_km_blocks = array_filter($pcs, function($row){return $row['tabname'] == 'fahrtstreke_km_block';});
                
                
                $fahrtzeit_blocks_IDS = array_column($fahrtzeit_blocks, 'id');
                $fahrtstreke_km_blocks_IDS = array_column($fahrtstreke_km_blocks, 'id');
                
                
                
                
                
                //$fahrtstreke_km_blocks
                if ( ! empty($fahrtstreke_km_blocks_IDS) && $pc_fahrtstreke_km_blocks_MUSTFIXT) {
                    $course_title = "Fahrtstrecke: " . $newRoute['length'] ;
                    if ($cf['expert_accompanied'] === 'yes') {
                        $course_title .= PHP_EOL ."Begleitung durch eine Ligetis Fachkraft: Ja";
                    } elseif($cf['expert_accompanied'] === 'no') {
                        $course_title .= PHP_EOL ."Begleitung durch eine Ligetis Fachkraft: Nein";
                    }
                    $q = Doctrine_Query::create()
                    ->update('PatientCourse')
                    ->set('course_title', "AES_ENCRYPT(?, ?)" , [$course_title, $salt])
                    ->whereIn("id", $fahrtstreke_km_blocks_IDS)
                    ;
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": {$course_title} ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }                    
                }
                
                //$fahrtzeit_blocks
                if ( ! empty($fahrtzeit_blocks_IDS) && $pc_fahrtzeit_blocks_MUSTFIXT) {
                    $course_title = "Fahrtzeit: " . $newRoute['duration'];
                    
                    $q = Doctrine_Query::create()
                    ->update('PatientCourse')
                    ->set('course_title', "AES_ENCRYPT(?, ?)" , [$course_title, $salt])
                    ->whereIn("id", $fahrtzeit_blocks_IDS)
                    ;
                    if ($runNOW) {
                        $q->execute();
                    } else {
                        echo  __LINE__ , ": {$course_title} ", $q->getSqlQuery() , PHP_EOL , "<br/>";
                    }
                }
                
  
            }            
            //get location at that datetime
        }
        

        echo "<br>TOTAL cf's processed : " .count($cfs);
        
    
        if (! $runNOW) {
            echo  "<hr><a href='?runNOW=1'>click 2 LIVE RUN</a>". PHP_EOL , "<br/>";
        }
        die("<hr>The NinjaTurtle has crossed the Finish line");
    
    }
    
    
    
    private function _getRoute($ipid, $user_id, $cf_date) 
    {
        
        $route = [];
        if ( ! isset($this->_user_data[$user_id])) {
            /* ---------------------------Get User details --  address-------------------------- */
            $this->_user_data[$user_id] = Pms_CommonData::getUserData($user_id);
        }        
        $userdata = $this->_user_data[$user_id];
        $user_address = $userdata[0]['street1'] . ',' . $userdata[0]['zip'] . ',' . $userdata[0]['city'];

        
        $client_id = $userdata[0]['clientid'];
        
        if ( ! isset($this->_client_data[$client_id])) {
            /* ---------------------------Get Client details --  address-------------------------- */
            $this->_client_data[$client_id] = Pms_CommonData::getClientData($client_id);
        }
        $clientdata = $this->_client_data[$client_id];
        $client_address = $clientdata[0]['street1'] . ',' . $clientdata[0]['postcode'] . ',' . $clientdata[0]['city'];
        
        
        
        
        if($userdata[0]['km_calculation_settings'] == "user")
        {
            $s = $user_address;
        }
        else if($userdata[0]['km_calculation_settings'] == "client")
        {
            $s = $client_address;
        }
        else if($userdata[0]['km_calculation_settings'] == "disabled")
        {
            $s = "";
        }
        
        
        if ( ! isset($this->_ipid_location[$ipid][$cf_date])) {
            $ploc = new PatientLocation();
            $this->_ipid_location[$ipid][$cf_date] = $ploc->get_multiple_period_locations($ipid, ['start' => $cf_date, 'end' => $cf_date]);
        }
        $plocarray = $this->_ipid_location[$ipid][$cf_date];
        $plocarray =  $plocarray[0]['master_details'];
        
        
        
        
//         if(empty($plocarray) || $plocarray[0]['location_type'] == '5')
//         {
//             $sql = ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
//             $sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
//             $sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
//             $pm = Doctrine_Query::create()
//             ->select($sql)
//             ->from('PatientMaster pm')
//             ->where('ipid = ?', $ipid)
//             ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
            
            
//             $plocarray[0]['street'] = $pm['street1'];
//             $plocarray[0]['zip'] = $pm['zip'];
//             $plocarray[0]['city'] = $pm['city'];
//             $plocarray[0]['location_type'] = '__FETCHED__';
            
//             $this->_ipid_location[$ipid] = $plocarray;
            
//         }
        $f = $plocarray['street'] . ',' . $plocarray['zip'] . ',' . $plocarray['city'];
        
        if(strlen($f) > 2 && strlen($s) > 2)
        {
            $route = Pms_CommonData::getRouteLength($s, $f);
            
            $route['__from'] = $s;
            $route['__to'] = $f;
            
        }
        return $route;
    }


    /**
     * When changing inside Mandate-Eninslesungen all the patients that already have assigned this doctor to switch the option
     * we must link all familydocs clones with the original one from /familydoctor/familydoctorlist
     *
     * @return boolean
     */
    public function iscp2339fixAction()
    {
    exit;
        echo "update FamilyDoctor SET self_id <hr>\n";
    
        if ( ! Doctrine_Core::getTable('FamilyDoctor')->hasRelation('FamilyDoctor')) {
            Doctrine_Core::getTable('FamilyDoctor')->hasMany('FamilyDoctor', ['local' => 'clientid', 'foreign' => 'clientid']);
        }
    
        $conn = Doctrine_Manager::getInstance()->getConnection('SYSDAT');
    
        $fakeOffset = 0;
        $limitPerStep = 1000;
        $thereIsMore = true;
        $stepCounter = 0;
    
        while ($thereIsMore) {
    
    
    
            $originals = Doctrine_Query::create()
            ->select('fdoc1.id')
            ->from('FamilyDoctor fdoc1')
            ->where("fdoc1.indrop = 0")
            ->andWhere("fdoc1.isdelete = 0")
            ->andWhere("fdoc1.id > {$fakeOffset}")
            ->andWhere("fdoc1.clientid > 0")
            ->andWhere("fdoc1.first_name != ''")
            ->andWhere("fdoc1.last_name != ''")
            //             ->andWhere("fdoc1.zip != ''")
            ->limit($limitPerStep)
            ->orderBy("id ASC")
            ->fetchArray()
            ;
            $originalsIDs = array_column($originals, 'id');
    
    
            if (empty($originalsIDs)) {
                $thereIsMore = false;
                break;
            }
    
    
            $unknown = Doctrine_Query::create()
            ->select('fdoc1.id, fdoc1.first_name, fdoc1.last_name, fdoc2.id')
            ->from('FamilyDoctor fdoc1')
            ->leftJoin("fdoc1.FamilyDoctor fdoc2 ON (fdoc2.id>{$fakeOffset} AND fdoc1.clientid = fdoc2.clientid AND fdoc2.isdelete=0 AND fdoc1.first_name=fdoc2.first_name AND fdoc1.last_name=fdoc2.last_name AND fdoc1.zip = fdoc2.zip AND fdoc2.indrop=1  AND fdoc2.self_id IS NULL)")
            ->whereIn("fdoc1.id", $originalsIDs)
            ->fetchArray()
            ;
    
            $unknown = array_filter($unknown, function($row){
                return ! empty($row['FamilyDoctor']);
            });
    
                foreach ($unknown as $original)
                {
    
                    $childrenIDs = implode(", ", array_column($original['FamilyDoctor'], 'id'));
                    //                 $sql_update = "UPDATE `family_doctor` SET `self_id` = :self_id  WHERE `id` IN ( :child_ids ) ";
                    //                 $params = array(
                    //                     'self_id'   => $original['id'],
                    //                     'child_ids'  => $childrenIDs
                    //                 );
    
                    //                 $stmt = $conn->execute($sql_update, $params);
    
                    $sql_update = "UPDATE `family_doctor` SET `self_id` = {$original['id']} WHERE `id` IN ( {$childrenIDs} ) ";
                    $stmt = $conn->execute($sql_update);
                    $stmt->closeCursor();
                }
    
                //change
                $fakeOffset = max($originalsIDs);
    
    
                echo 'step: ', ++$stepCounter , ' updated: ', count($unknown) ,  ' , new fakeOffset:' , $fakeOffset , '<br>';
                $this->_ob_flush();
    
    
        }
    
        die("<hr>The NinjaTurtle has crossed the Finish line");
    
    }
    
    public function activateshortcutvclientsettingsAction(){
    	//ISPC-2163
    	$this->_helper->layout->setLayout('layout');
    	$this->_helper->viewRenderer->setNoRender();
    
    	// get all clients
    	$clt = Doctrine_Query::create()
    	->select("*")
    	->from('Client')
    	// 		->whereIn('id', array(1, 121))
    	;
    	//->whereNotIn('id', array(1, 121));
    	//->orderBy("id ASC");
    	$cltarray = $clt->fetchArray();
    	//var_dump($cltarray);exit;
    
    	foreach($cltarray as  $cl_data)
    	{
    		$modules =  new Modules();
    		$clientModulesset = $modules->get_client_modules($cl_data['id']);
    
    		//Activate Shortcut V in fb3 and fb8
    		if($clientModulesset['79'])
    		{
    			$cust = Doctrine::getTable('Client')->find($cl_data['id']);
    			$activate_shortcut_settings = array();
    			$activate_shortcut_settings['activate_shortcut_v_settings'] = 'yes';
    			$activate_shortcut_settings['activate_shortcut_v_yes_settings'] = array('0');
    			
	    		$cust->activate_shortcut_v_settings = $activate_shortcut_settings;
	    		$cust->save();
    		}    
    	}
    	
    	$custmod = Doctrine::getTable('Modules')->find('79');
    	$custmod->isdelete = '1';
    	$custmod->save();
    	
    	exit;
    }
    
    
    public function updateofflinecourseAction(){
        exit;
        
        set_time_limit(10 * 60);
         
        $start = microtime(true);
         
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
         

        $q = Doctrine_Query::create()
        ->select('*')
        ->from('PatientSync')
        ->limit(100);
        $q_res = $q->fetchArray();
        
        $ipids_str="";
        if(!empty($q_res)){
            foreach($q_res as $k=>$ps){
                $ipids_str .= '"'.$ps['ipid'].'",'; 
                
            }
        }
        $ipids_str = substr($ipids_str, 0, -1);
//         dd($ipids_str);
//         dd($patient_sync);
            
        $conn = Doctrine_Manager::getInstance()->getConnection('MDAT');
         
        $contact_form_encrypted = Pms_CommonData::aesEncrypt("contact_form");
        
        $sql_txt = "
    	    	    SELECT
    	        pc.id as pc_id,
    	        pc.done_id as pc_done_id ,
    	        cf.id as cf_id,
    	        aes_decrypt(pc.tabname, 'encrypt'),
    	        aes_decrypt(pc.course_title, 'encrypt'),
    	        aes_decrypt(pc.done_name, 'encrypt')  
    	    	    FROM `patient_course` pc
    	    	    LEFT JOIN contact_forms cf ON (cf.ipid = pc.ipid AND cf.id = pc.done_id)
        	    WHERE
                    cf.id is not NULL
    	           AND pc.done_name = ''
                   AND pc.ipid in (".$ipids_str.")
     	        LIMIT 300000
        
    	    ";//AND pc.done_id = 0
   
/*         print_r($sql_txt); exit;
        
        
        exit;
        exit; */
        
        $collection = $conn->execute($sql_txt);
        	
        $cnt = 0;
        while ($row = $collection->fetch(PDO::FETCH_ASSOC)) {
             
            if ($row['pc_done_id'] > 0) {
        
                $sql_update = "UPDATE `patient_course` SET `done_name` = :done_name WHERE `id`= :pc_id";
            	    
                $params = array(
                    'done_name'    => $contact_form_encrypted,
                    'pc_id'        => $row['pc_id']
                );
            } else {
        
                $sql_update = "UPDATE `patient_course` SET `done_name` = :done_name, `done_id` = :done_id WHERE `id`= :pc_id";
                $params = array(
                    'done_name'    => $contact_form_encrypted,
                    'done_id'      => $row['pf_recordid'],
                    'pc_id'        => $row['pc_id']
                );
            }
            $stmt = $conn->execute($sql_update, $params);
            $stmt->closeCursor();
            $cnt++;
             
            $row = null;
            unset($row);
        }
        	
        $collection = null;
        unset($collection);
        
        echo "done";
        exit;
    }

    


    public function fdshinvoicesAction(){
        set_time_limit(10 * 60);
         
        $start = microtime(true);
         
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
         
        $invoices_arr = Doctrine_Query::create()->select("*")
        ->from('ShShiftsInternalInvoices')
        ->where('user_type = "familly_doctor"' )
        ->fetchArray();
        
        
    
        
        foreach($invoices_arr as $k_inv => $inv_data ){
            if($inv_data['invoice_end'] != "0000-00-00 00:00:00") {
        
                $frm1 = Doctrine::getTable('ShShiftsInternalInvoices')->find($inv_data['id']);
                if ($frm1 instanceof ShShiftsInternalInvoices) {
                    $frm1->invoice_end  = date("Y-m-t 23:59:59",strtotime($inv_data['invoice_end']));
                    $frm1->save();
                }
            }
        }
        
        echo "done"; exit;
    }

    
    public function shshiftsinvoicesAction(){
        set_time_limit(10 * 60);
         
        $start = microtime(true);
         
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
         
        $invoices_arr = Doctrine_Query::create()->select("*")
        ->from('ShShiftsInternalInvoices')
        ->fetchArray();
        
        
    
        foreach($invoices_arr as $k_inv => $inv_data ){
            if($inv_data['start_active'] == "0000-00-00 00:00:00") {
                $frm1 = Doctrine::getTable('ShShiftsInternalInvoices')->find($inv_data['id']);
                if ($frm1 instanceof ShShiftsInternalInvoices) {
                    $frm1->start_active  = $inv_data['invoice_start'];
                    $frm1->save();
                }
            }
            if($inv_data['end_active'] == "0000-00-00 00:00:00") {
                $frm1 = Doctrine::getTable('ShShiftsInternalInvoices')->find($inv_data['id']);
                if ($frm1 instanceof ShShiftsInternalInvoices) {
                    $frm1->end_active  = $inv_data['invoice_end'];
                    $frm1->save();
                }
            }
        }
        
        echo "done"; exit;
    }

    


    public function repfamAction(){
    	exit;
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
    
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
         
        $clientid = 249;
        $fdoc1 = Doctrine_Query::create();
        $fdoc1->select('id,self_id,last_name,first_name,street1,doctornumber,doctor_bsnr,indrop');
        $fdoc1->from('FamilyDoctor');
        $fdoc1->where("clientid = ?", $clientid);
        $fdoc1->andWhere("isdelete = 0  ");
        $fdocarray = $fdoc1->fetchArray();
//         dd($fdocarray);
        
        
        foreach($fdocarray as $k=>$fd){
            $far[trim($fd['last_name']).'-'.trim($fd['first_name']).'-'.trim($fd['street1'])][]  = $fd['doctornumber'].' - '.$fd['indrop'].' - '.$fd['id'].' - '.$fd['self_id']; 
        }
        dd($far);
        
    }



    public function internalinvoicesAction(){
        set_time_limit(10 * 60);
         
        $start = microtime(true);
         
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
         
        $invoices_arr = Doctrine_Query::create()->select("*")
        ->from('InternalInvoices')
        ->fetchArray();
    
    
    
        foreach($invoices_arr as $k_inv => $inv_data ){
            if($inv_data['start_active'] == "0000-00-00 00:00:00") {
                $frm1 = Doctrine::getTable('InternalInvoices')->find($inv_data['id']);
                if ($frm1 instanceof InternalInvoices) {
                    $frm1->start_active  = $inv_data['invoice_start'];
                    $frm1->save();
                }
            }
            if($inv_data['end_active'] == "0000-00-00 00:00:00") {
                $frm1 = Doctrine::getTable('InternalInvoices')->find($inv_data['id']);
                if ($frm1 instanceof InternalInvoices) {
                    $frm1->end_active  = $inv_data['invoice_end'];
                    $frm1->save();
                }
            }
        }
    
        echo "done"; exit;
    }
    
    public function addcourseshortcut4allclientsAction(){
    	$this->_helper->layout->setLayout('layout');
    	$this->_helper->viewRenderer->setNoRender();
    	
    	$courseshorctcut_to_be_add = array(
    			'shortcut' => 'TD',
    			'course_fullname' => 'Teambesprechung Details',
    			'isfilter' => '1',
    			'isbold' => '0',
    			'isitalic' => '0',
    			'isunderline' => '0',
    			'font_color' => 'ff6600'
    	);
    			
    	$courseshortcut_to_be_followed = 'K';
    
    	// get all clients
    	$clt = Doctrine_Query::create()
    	->select("*")
    	->from('Client')
    	// 		->whereIn('id', array(1, 121))
    	;
    	//->whereNotIn('id', array(1, 121));
    	//->orderBy("id ASC");
    	$cltarray = $clt->fetchArray();
    	//var_dump($cltarray);exit;
    
    	$courseshorctcut_to_be_add_arr = array();
    	foreach($cltarray as  $cl_data)
    	{
    		$data[] = array(
    			'shortcut' => $courseshorctcut_to_be_add['shortcut'],
    			'course_fullname' => $courseshorctcut_to_be_add['course_fullname'],
    			'clientid' => $cl_data['id'],
    			'isfilter' => $courseshorctcut_to_be_add['isfilter'],
    			'isbold' => $courseshorctcut_to_be_add['isbold'],
    			'isitalic' => $courseshorctcut_to_be_add['isitalic'],
    			'isunderline' => $courseshorctcut_to_be_add['isunderline'],
    			'font_color' => $courseshorctcut_to_be_add['font_color']    				
    		);
    	}
    	
    	//insert the shortcut for all the clients
    	$collection = new Doctrine_Collection('Courseshortcuts');
    	$collection->fromArray($data);
    	$collection->save();
    	
    	if($courseshortcut_to_be_followed != "")
    	{
	    	//get details for the shortcut_to_be_followed
	    	$courseshortcut_to_be_followed_ids = CourseshortcutsTable::findCourseShortcutDetailsbyshortcut($courseshortcut_to_be_followed);
	    	//get details for the shortcut_to_be_added after they were added
	    	$courseshortcut_to_be_added_ids = CourseshortcutsTable::findCourseShortcutDetailsbyshortcut($courseshorctcut_to_be_add['shortcut']);
	    	//print_r($courseshortcut_to_be_added_ids); exit;
	    	
	    	$data = array();    	
	    	foreach($courseshortcut_to_be_added_ids as $ksht=>$vsht)
	    	{
				$shortcut4client_key = array_search($vsht['clientid'], array_column($courseshortcut_to_be_followed_ids, 'clientid'));
				if($shortcut4client_key !== false)
				{
					//get permissions for every shortcut to be followed by client
		    		$courseshortcut_to_be_followed_permissions = GroupCourseDefaultPermissionsTable::findCourseShortcutPermissionsbyshortcutid($courseshortcut_to_be_followed_ids[$shortcut4client_key]['shortcut_id']);
		    		
		    		if(!empty($courseshortcut_to_be_followed_permissions))
		    		{
		    			foreach($courseshortcut_to_be_followed_permissions as $kp=>$vp)
		    			{
		    				$vp['shortcutid'] = $vsht['shortcut_id'];
		    				unset($vp['id']);
		    				//set the permissions for the shortcut to be added by client
		    				$data[] = $vp;
		    			}
		    			
		    		}
				}
	    	}
	    	
	    	if($data)
	    	{
	    		//print_r($data); exit;
	    		//insert the permission for the shortcut to be added for all the clients
	    		 $collection = new Doctrine_Collection('GroupCourseDefaultPermissions');
	    		 $collection->fromArray($data);
	    		 $collection->save();
	    	}
	    	
    	}
    	
    	exit;
    }


    
    public function updatefdocclientsAction(){
        $this->_helper->layout->setLayout('layout');
        $this->_helper->viewRenderer->setNoRender();

        
        
        
        
        
    }
    
    
    public function patientcrisishistoryAction(){
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" nu e SA ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $xtype = 'K';
        $xcrisis = 'Der Status des Patienten wurde auf';
        $idlas=0;
        
//         $file_location = APPLICATION_PATH . '/../public/run/';
//         $file_name = 'lastidcrisishistory.txt';
        
        $patcrisisc = Doctrine_Query::create()
        ->select("ipid, user_id, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, course_date,  create_user as status_create_user ")
        ->from('PatientCourse')
        ->where('course_type = AES_ENCRYPT("K", "' . Zend_Registry::get('salt') . '")')
        ->andwhere('
            AES_DECRYPT(course_title,"' . Zend_Registry::get('salt') . '")   LIKE "Der Status des Patienten wurde auf normal, keine Krise gesetzt" 
            or  AES_DECRYPT(course_title,"' . Zend_Registry::get('salt') . '")   LIKE "Der Status des Patienten wurde auf Achtung, instabil gesetzt" 
            or  AES_DECRYPT(course_title,"' . Zend_Registry::get('salt') . '")   LIKE "Der Status des Patienten wurde auf Achtung, Verschlechterung gesetzt" 
            or  AES_DECRYPT(course_title,"' . Zend_Registry::get('salt') . '")   LIKE "Der Status des Patienten wurde auf Krise gesetzt" 
            ')
            ->andWhere('id >=?',$idlas)
        ->limit(1000);
        $patcrisis_arr = $patcrisisc->fetchArray();
        
 
        
        $verde =  'Der Status des Patienten wurde auf normal, keine Krise gesetzt';
        $galben = 'Der Status des Patienten wurde auf Achtung, instabil gesetzt';
        $galben_old = 'Der Status des Patienten wurde auf Achtung, Verschlechterung gesetzt';
        $rosu =   'Der Status des Patienten wurde auf Krise gesetzt';
        
        foreach ( $patcrisis_arr  as $key=> $sdata){
            
            $ipid = $sdata['ipid'];
            $user_id = $sdata['user_id'];
            $status_date = $sdata['course_date'];
            $status_create_user = $sdata['status_create_user'];
            $cris_status='';

            if ($sdata['course_title'] == $verde){
                $cris_status= '1';
            }
            if ($sdata['course_title'] == $galben || $sdata['course_title']== $galben_old){
                $cris_status= '2';
            }
            if ($sdata['course_title'] == $rosu){
                $cris_status= '3';
            }
            
            
            $havecrisis = Doctrine_Query::create()
            ->select('*')
            ->from('PatientCrisisHistory')
            ->where('ipid =?',$ipid)
            ->andwhere('status_date = ?', $status_date)
            ->andwhere('crisis_status = ?', $cris_status);
            $havecrisis_arr = $havecrisis->fetchArray();
            
            if (empty($havecrisis_arr) && ! empty($cris_status)){
                
                $ins = new PatientCrisisHistory;
                $ins->ipid = $sdata['ipid'];
                $ins->status_date = $sdata['course_date'];
                $ins->crisis_status = $cris_status;
                $ins->status_create_user = $sdata['status_create_user'];
                $ins-> save();
            }
        }
        echo "done";
        exit;
    }
    
    
    public function addicon2clientAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $clientid = "269";
        $icon_id = "1719";
        
//         $clientid = "213";
//         $icon_id = "1317";
        
        $actpatient = Doctrine_Query::create();
        $actpatient->select("*");
        $actpatient->from('EpidIpidMapping');
        $actpatient->where('clientid = ?', $clientid);
        $actpatient->limit(10);
        $actipidarray = $actpatient->fetchArray();
        
        
        /* $iconsq = Doctrine_Query::create();
        $iconsq->select("*");
        $iconsq->from('IconsPatient');
        $iconsq->where('icon_id = ?', $icon_id);
        $iconsq->andWhere('isdelete = "0"'); //ISPC-2396 add isdelete
        $iconsq_Ar = $iconsq->fetchArray(); */
        
        $iconsq_Ar = IconsPatientTable::findAllIconsPatientbyIconId($icon_id); //ISPC-2396 Carmen 09.10.2019
        
        //var_dump($iconsq_Ar); exit;
        $patients_with_icons = array();
        foreach($iconsq_Ar as $k=>$kp){
            $patients_with_icons[] =  $kp['ipid'];
        }
        
        $inserted = array();
        foreach($actipidarray as $k=>$data){
            if(!in_array($data['ipid'],$patients_with_icons)){
                
                $ins = new IconsPatient;
                $ins->ipid = $data['ipid'];
                $ins->icon_id= $icon_id;
                $ins-> save();
                $inserted[] = $data['ipid'];
            }
        }
        
        echo "DONE";
        echo "<br/>";
        print_r(count($inserted));
        echo "<br/>";
        echo "<pre>";
        print_r($inserted);
        exit;
    }
    

    public function hl7insertAction(){
        exit;
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        
        $message['1'] = "MSH|^~\&|SAP-ISH|162-0001|||20190722102753||ADT^A04|41221595|P|2.5|||AL|NE|DE|ISO8859-15|
EVN|A04|20190722102753||NP41I0||20190722000100|
PID|||0003010006^^^^PI||Florie^Hans^^^^^L||19470416|M|||Grashofstr. 86^^Essen^^45133^DE||413625^^PH||D|||||||||||||||N|
PV1||O|PMA9^^^PM|||^^|||0000074095^Czyborra^Peter-Boris^^^^Dr. med.|||||||N|||0004537870^^^^VN||K|||K||||||||||||||||||||20190722000100|||||||V|
PV2||A^Ambulanter Fall^ISH|||||2^4^SA^A|
IN1|1||0104080005^^^^HC~0104940005^^^^NII~0000403066^^^^FI|BARMER|Friedrichstr. 45^^Essen^^45128^DE||0800 3330092 PFLEGEKASSE^^FX|||||19470416|99991231||M|^^^^^|||^^^^^|||01|||||||00000000||||||1|Z386476175|
ZBE|000453787000001|20190722000100||INSERT|";
        
        $message['2'] = "MSH|^~\&|SAP-ISH|162-0001|||20190730081113||ADT^A04|41319648|P|2.5|||AL|NE|DE|ISO8859-15|
EVN|A04|20190730081113||NP41I0||20190730000100|
PID|||0003014102^^^^PI||Frede^Stefanie^^^^^L||19520414|F|||Schaffelhofer Weg 7^^Essen- Überruhr^^45277^DE||404360^^PH||D|||||||||||||||N|
PV1||O|PMA9^^^PM|||^^|||0000022342^Steinebach^Inga^^^^|||||||N|||0004540279^^^^VN||K|||K||||||||||||||||||||20190730000100|||||||V|
PV2||A^Ambulanter Fall^ISH|||||2^4^SA^A|
IN1|1||0104080005^^^^HC~0104940005^^^^NII~0000403066^^^^FI|BARMER|Friedrichstr. 45^^Essen^^45128^DE||0800 3330092 PFLEGEKASSE^^FX|||||19520414|99991231||M|^^^^^|||^^^^^|||01|||||||00000000||||||1|O514237593|
ZBE|000454027900001|20190730000100||INSERT|";
        
        $message['3'] = "MSH|^~\&|SAP-ISH|162-0001|||20190730074313||ADT^A04|41318734|P|2.5|||AL|NE|DE|ISO8859-15|
EVN|A04|20190730074313||NP41I0||20190730000100|
PID|||0003014098^^^^PI||Munsch^Ulrich^^^^^L||19791219|M|||Ligusterweg 15^^Essen- Bredeney^^45133^DE||015786852219^^PH||D|||||||||||||||N|
PV1||O|PMA9^^^PM|||^^|||0000001043^Hauser^Gerhard^^^^Dr. med.|||||||N|||0004540264^^^^VN||K|||K||||||||||||||||||||20190730000100|||||||V|
PV2||A^Ambulanter Fall^ISH|||||2^4^SA^A|
IN1|1||0104212505^^^^HC~0104212505^^^^NII~0000401107^^^^FI|AOK Rheinland/Hamburg|Friedrich-Ebert-Str. 49^^Essen^^45127^DE||0201 2011-0^^PH~0201 2011 246^^FX|||||19791219|99991231||M|^^^^^|||^^^^^|||01|||||||00000000||||||1|A702706403|
ZBE|000454026400001|20190730000100||INSERT|";
        
       foreach($message as $k=>$mess){
            $nh = new Hl7MessagesReceived();
            $nh->client_id = "252";
            $nh->port = "20252";
            $nh->message = $mess;
            $nh->fetched_by_master = "YES";
            $nh->create_user = "338";
            $nh->create_date = date('Y-m-d H:i:s');
            $nh->save();
       }
        
    }
    
    /**
     * FUnction to test ISPC-2417
     * @author Loredana
     * 02.09.2019
     */
    
    public function todo2417todoreminderAction(){
        
        echo "JOB: TODO Reminder <hr>\n";
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $funct = new Messages();
        $apelfunc = $funct->todo_reminder_notification();
        
        echo "done";
        exit;
        //http://10.0.0.36/ispc/public/bugfix/todo2417todoreminder
    }


    
    /**
     * Fn to share specific patients courses
     * @author Ancuta
     * 
     */
    public function sharespecificpatientsAction(){
    
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    
    
    
//         $source_ipid = "296aaf7ecf78d1dad5df2a7e35e66f3e62547cf4";
//         $target_ipid = "1343afe83e4a9f4f241303e15ae21e347819e204";

        // TODO-2527 Ancuta - 04.09.2019
        $source_ipid = "54275db93c6daf5c57dca9871178f3ad3839e846";
        $target_ipid = "86e7d2394d1a8608f6cf5c37b3819334c2befaa6";
        $connection_date="2008-01-01";
    
        $ps =  new PatientsShare();
        $share = $ps->share_shortcuts_s2t($source_ipid,$target_ipid,$connection_date);
    
        echo "done"; exit;
    }

    
    public function mypainpingAction(){
    
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    
 
        $mypain = new Pms_MyPain();
        echo "<pre/>";
        echo "PING<br/>";
        
        $ping_result = $mypain->send_ping();
        
        var_dump($ping_result);
        
        echo "done"; exit;
    }

    
    public function cleandutyAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        
        $month = "08";
        $year = "2019";
        
        $client = "101";

       $duty_data= Doctrine_Query::create()
            ->select('*')
            ->from('Roster')
            ->where('clientid =?',$client)
            ->andwhere('month(duty_date) = ?', $month)
            ->andwhere('year(duty_date) = ?', $year)
            ->fetchArray();

       $user2date2shifts  =array();
       foreach($duty_data as $k=>$d){
           $user2date2shifts[$d['userid']][$d['duty_date']][$d['shift']][]=$d;
       }
       dd($user2date2shifts);
       
       $delete_entries  = array();
       foreach($user2date2shifts as $user_id=>$duty_date){
           foreach($duty_date as $date=>$shifts){
               foreach($shifts as $shift=>$entryes_per_row){
                   if(count($entryes_per_row[0]) == 2){
                       $delete_entries[$user_id][$date][$shift]['0']= end($entryes_per_row[0]);
                   }
                   if(count($entryes_per_row[1]) == 2){
                       $delete_entries[$user_id][$date][$shift]['1']= end($entryes_per_row[1]);
                   }
               }
           }
       }
        
       
       dd($delete_entries );
        
    }

    
    
    public function dcommentsAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        echo "<pre/>";
        
        
        //get all  diagnosis with comments
        $diagno_comments = Doctrine_Query::create()
        ->select('id,ipid,comments,change_user,change_date')
        ->from('PatientDiagnosis')
        ->where('comments != ""')
        ->limit(500)
        ->fetchArray();
                
        print_r("\n 1 diagnosis with comments \n");
        print_r($diagno_comments);
        
        $updated = array();
        $update_diagno = 0;
 
        foreach($diagno_comments as $k=>$diagno){
            
            $update_diagno[$diagno['id']] = 0;
            
            // if comment found in patient_drugplan  comment- clear comment in diagnosis
            $pd_comments = Doctrine_Query::create()
            ->select('id,ipid,comments,comments as maste_comment')
            ->from('PatientDrugPlan')
            ->where('ipid = ?',$diagno['ipid'])
            ->andWhere('trim(comments) = ?',trim($diagno['comments']))
            ->fetchArray();
            
            if(!empty($pd_comments)){
                print_r("\n 2 medication that have comment  ".$diagno['comments']."  \n");
                print_R($pd_comments);

                $qfd = Doctrine_Query::create()
                ->update('PatientDiagnosis')
                ->set('comments',"''")
                ->set('change_date',"'".$diagno['change_date']."'" )
                ->set('change_user',"'".$diagno['change_user']."'" )
                ->where("id = ".$diagno['id'])
                ->andWhere("ipid = '".$diagno['ipid']."'")
                ->execute();
                
                print_r("\n 2a Diagno updated  \n");
                print_R($diagno);
                
                $updated[] = $diagno;
                
                
            } else {
                
                // check in history
                $pdh_comments = Doctrine_Query::create()
                ->select('id,ipid,pd_comments')
                ->from('PatientDrugPlanHistory')
                ->where('ipid = ?',$diagno['ipid'])
                ->andWhere('trim(pd_comments) = ?',trim($diagno['comments']))
                ->fetchArray();
                
                
                if(!empty($pdh_comments)){
                    print_r("\n 3 medication-history that have comment  ".$diagno['comments']."  \n");
                    print_r($pdh_comments);
                    
                    
                    $qf = Doctrine_Query::create()
                    ->update('PatientDiagnosis')
                    ->set('comments',"''")
                    ->set('change_date',"'".$diagno['change_date']."'" )
                    ->set('change_user',"'".$diagno['change_user']."'" )
                    ->where("id = ".$diagno['id'])
                    ->andWhere("ipid = '".$diagno['ipid']."'")
                    ->execute();
                    print_r("\n 3a Diagno updated  \n");
                    print_R($diagno);
                    $updated[] = $diagno;
                }
            }
            

            
        }
        
        print_r("\n diagnosis updated:  \n");
        print_r($updated);
        
        
        
exit;        
    }
    

    
    public function gapcheckAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        echo "<pre/>";
        // select all ipids that have messaged proccesd this month 
        $proccesd_messages = Doctrine_Query::create()
        ->select('*,hl7_pm.ipid as ipid, AES_DECRYPT(message,"' . Zend_Registry::get('salt') . '") as message')
        ->from('Hl7MessagesReceived hl7_mr')
        ->leftJoin("hl7_mr.Hl7MessagesProcessed hl7_pm")
        ->where("DATE(hl7_pm.create_date) >= '2019-09-20' ")
        ->andWhere("hl7_pm.messages_processed_ID IS NOT NULL")
        ->orderBy('hl7_pm.create_date DESC')
        ->fetchArray( )
        ;
        

        $ipids = array();
        foreach($proccesd_messages as $km => $row){
            $ipids[] = $row['ipid'];
        }
        $ipids = array_unique($ipids);

        //patient days
        $conditions['client'] = "1";
        $conditions['ipids'] = $ipids;
        $conditions['periods'][0]['start'] = '2019-10-01';
        $conditions['periods'][0]['end'] = date('Y-m-d');
        
        //be aware of date d.m.Y format here
        $patient_days = Pms_CommonData::patients_days($conditions);
//         real_active_days

   
        $active_days =array();
        $ipid2epid = array();
        $final_data = array();
        $treatment_days = array();
        foreach($patient_days as $k_ipid=>$pdata){
            $active_days[$k_ipid] = $pdata['real_active_days'];
            $treatment_days[$k_ipid] = $pdata['treatment_days'];            
            
            $ipid2epid[$k_ipid] = $pdata['details']['epid'];
            
        }
        
//        dd($ipid2epid);
//print_R(count($proccesd_messages )); exit;
        //print_R($active_days);exit;        
//        print_R($proccesd_messages);exit;
        
        
        
        foreach($proccesd_messages as $km => $row){
            $message = new Net_HL7_Message(trim($row['message']));
            
            $msgType = $message->getSegmentFieldAsString(0, 9); // Example: "ADT^A08"
            $msgDate = $message->getSegmentFieldAsString(0, 7);
            $proccesd_messages[$km]['MESSAGE_Date'] = $msgDate;
            
            $zbe = $message->getSegmentsByName("ZBE");
            
            if (sizeof($zbe) > 0) {
        
                $zbe = $zbe[0];
                $full_movement_number = $zbe->getField(1);
                $zdate = $zbe->getField(2);
                $zbe = $zbe->getField(4);
                $zbe_Date = date('d.m.Y',strtotime($zdate)); 
                $proccesd_messages[$km]['zbe_Date'] = date('Y-m-d',strtotime($zdate)); 
                $proccesd_messages[$km]['full_movement_number'] =$full_movement_number ; 
                $proccesd_messages[$km]['movement_number'] = substr($full_movement_number,-5); 
                
                
                //$am_ipid2date[$row['ipid']][$zbe_Date ]['msg'] = $row['message'];
                $am_ipid2date[$row['ipid']][$zbe_Date ]['msg_date'] = date('Y-m-d',strtotime($zdate)); 
                $am_ipid2date[$row['ipid']][$zbe_Date ]['msg_type'] = $msgType; 
                $am_ipid2date[$row['ipid']][$zbe_Date ]['movement_number'] =substr($full_movement_number,-5); 
            }
        }

//         print_r($am_ipid2date); exit;
//         print_r($proccesd_messages); exit;

        $no_messages = array();
        foreach($ipids as $ipid){
            foreach($active_days[$ipid] as $active_date){
                if(empty($am_ipid2date[$ipid][$active_date ])){
                    $no_messages[$ipid][] = $active_date ;
                    $final_data[$ipid2epid[$ipid]]['active_days_without _message'][] = $active_date ;
                }
            }
        }
        
        foreach($no_messages as $ipid=> $ndays){
            $final_data[$ipid2epid[$ipid]]['active_days'] = $active_days[$ipid] ;
            $final_data[$ipid2epid[$ipid]]['treatment_days'] = $treatment_days[$ipid] ;
        }
        
        print_R($final_data); exit;
       print_R($no_messages);exit;
        
    }
 
    //ISPC-2482 Lore 22.11.2019
     public function updategroupdefaultvisibilityAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        
        //check if has been already called
        $already_called = Doctrine_Query::create()
        ->select('*')
        ->from('GroupDefaultVisibility')
        ->where('groupid is not NULL ');
        $already_called_arr= $already_called->fetchArray();
        
        if (!empty($already_called_arr)){
            
            
            echo "<pre>";
            echo "--- already_called ----";
            echo "--- exit; ----";
            exit;
            
        }
       
        // first get all groups to master groups 
        
        $users_groups_arr = Doctrine_Query::create()
        ->select('*')
        ->from('Usergroup')
        ->where('isdelete=0')
         ->fetchArray();
         
         if(empty($users_groups_arr)){
             
             echo "<pre>";
             echo "--- No user groups ----";
             echo "--- exit; ----";
             exit;
         }
         
         $client2mg2ug  =array();
         foreach($users_groups_arr as $k=>$ug){
             $client2mg2ug[$ug['clientid']][$ug['groupmaster']][] = $ug['id'];
         }

        //get the rights for update...
        $group_visibility = Doctrine_Query::create()
        ->select('*')
        ->from('GroupDefaultVisibility');
        $group_visibility_arr= $group_visibility->fetchArray();

        
        
        $no_groups2master = array();
        $inseted = array();
        if (!empty($group_visibility_arr))
        {
            foreach($group_visibility_arr as $key => $gv_values)
            {
                $q = Doctrine_Query::create()
                ->delete('GroupDefaultVisibility')
                ->where('clientid=?', $gv_values['clientid'])
                ->andWhere('master_group_id=?', $gv_values['master_group_id'])
                ->andWhere('groupid is NULL ');
                $q->execute();
                
                if(!empty($client2mg2ug[$gv_values['clientid']][$gv_values['master_group_id']]))
                {
                    foreach($client2mg2ug[$gv_values['clientid']] as $master_group => $client_groups)
                    {
                        if($master_group == $gv_values['master_group_id'])
                        {
                            foreach ($client_groups as $cl_gr_id)
                            {
                                $grp = new GroupDefaultVisibility();
                                $grp->master_group_id = $gv_values['master_group_id'];
                                $grp->clientid = $gv_values['clientid'];
                                $grp->groupid = $cl_gr_id;
                                $grp->save();
                                
                                $inseted[] = array(
                                    'clientid'=>$gv_values['clientid'],
                                    'master_group_id'=>$gv_values['master_group_id'],
                                    'groupid'=>$cl_gr_id
                                ) ;
                            }
                        }
                    }
                } else{
                    $no_groups2master[] = array(
                        'clientid'=>$gv_values['clientid'],
                        'master_group_id'=>$gv_values['master_group_id'],
                    ) ;
                }
            }
        }
        
        echo "<pre>";
        echo "--- DONE ----";
        print_R($inseted); 
        echo "--- no_groups2master ----";
        print_R($no_groups2master); 
        echo "--- exit; ----";
        exit;
        
    }  
    
    //ISPC-2482 Lore 22.11.2019
    // Ancuta rewrite
    public function updategroupsecrecyvisibilityAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        
        //check if has been already called
        $already_called = Doctrine_Query::create()
        ->select('*')
        ->from('GroupSecrecyVisibility')
        ->where('groupid is not NULL ');
        $already_called_arr= $already_called->fetchArray();
        
        if (!empty($already_called_arr)){
            
            
            echo "<pre>";
            echo "--- S already_called ----";
            echo "--- exit; ----";
            exit;
            
        }
       
        // first get all groups to master groups 
        
        $users_groups_arr = Doctrine_Query::create()
        ->select('*')
        ->from('Usergroup')
        ->where('isdelete=0')
         ->fetchArray();
         
         if(empty($users_groups_arr)){
             
             echo "<pre>";
             echo "--- No user groups ----";
             echo "--- exit; ----";
             exit;
         }
         
         $client2mg2ug  =array();
         foreach($users_groups_arr as $k=>$ug){
             $client2mg2ug[$ug['clientid']][$ug['groupmaster']][] = $ug['id'];
         }

        //get the rights for update...
        $group_visibility = Doctrine_Query::create()
        ->select('*')
        ->from('GroupSecrecyVisibility');
        $group_visibility_arr= $group_visibility->fetchArray();

        
        
        $no_groups2master = array();
        $inseted = array();
        if (!empty($group_visibility_arr))
        {
            foreach($group_visibility_arr as $key => $gv_values)
            {
                $q = Doctrine_Query::create()
                ->delete('GroupSecrecyVisibility')
                ->where('clientid=?', $gv_values['clientid'])
                ->andWhere('master_group_id=?', $gv_values['master_group_id'])
                ->andWhere('groupid is NULL ');
                $q->execute();
                
                if(!empty($client2mg2ug[$gv_values['clientid']][$gv_values['master_group_id']]))
                {
                    foreach($client2mg2ug[$gv_values['clientid']] as $master_group => $client_groups)
                    {
                        if($master_group == $gv_values['master_group_id'])
                        {
                            foreach ($client_groups as $cl_gr_id)
                            {
                                $grp = new GroupSecrecyVisibility();
                                $grp->master_group_id = $gv_values['master_group_id'];
                                $grp->clientid = $gv_values['clientid'];
                                $grp->groupid = $cl_gr_id;
                                $grp->save();
                                
                                $inseted[] = array(
                                    'clientid'=>$gv_values['clientid'],
                                    'master_group_id'=>$gv_values['master_group_id'],
                                    'groupid'=>$cl_gr_id
                                ) ;
                            }
                        }
                    }
                } else{
                    $no_groups2master[] = array(
                        'clientid'=>$gv_values['clientid'],
                        'master_group_id'=>$gv_values['master_group_id'],
                    ) ;
                }
            }
        }
        
        echo "<pre>";
        echo "--- DONE ----";
        print_R($inseted); 
        echo "--- no_groups2master ----";
        print_R($no_groups2master); 
        echo "--- exit; ----";
        exit;
    }  
    
    //Lore 27.11.2019
/*     public function updatedbreportpermissionsAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        
        
        //get the rights for update...
        $repo_perm = Doctrine_Query::create()
        ->select('*')
        ->from('ReportPermission indexBy clientid');
        $repo_perm_arr= $repo_perm->fetchArray();
        
        if (!empty($repo_perm_arr)){
            
            foreach($repo_perm_arr as $key => $list_repo){
                
                // marchez tot ce am in db ca isdelete
                $q = Doctrine_Query::create()
                ->update('ReportPermission')
                ->set('isdelete',"1");
                $q->execute();
                
                 $clientid = $list_repo['clientid'];
                 $report_id_str = $list_repo['report_id'];
                 
                 $elements = explode(",", $report_id_str);
                 
                 foreach($elements as $key ){
                    
                    $grp = new ReportPermission();
                    $grp->clientid = $clientid;
                    $grp->report_id = trim($key);
                    $grp->save();
                }
            }
            echo "Gata update-ul <pre/>";
            exit;
        }
    } */
    
    
    //ISPC-2482 Lore 13.12.2019
    public function updatepatientgroupsAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($logininfo->usertype != 'SA')
        {
            die(" normal Ben ? ");
        }
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        //check if has been already called
        $already_called = Doctrine_Query::create()
        ->select('*')
        ->from('PatientGroups')
        ->where('master_groupid != 0 ');
        $already_called_arr= $already_called->fetchArray();
        
        if (!empty($already_called_arr)){

            echo "<pre>";
            echo "--- S already_called ----";
            echo "--- exit; ----";
            exit;      
        }
       
        
        // first get all groups to master groups
        $users_groups_arr = Doctrine_Query::create()
        ->select('*')
        ->from('Usergroup')
        ->where('isdelete = 0')
        ->fetchArray();
        
        if(empty($users_groups_arr)){
            
            echo "<pre>";
            echo "--- No user groups ----";
            echo "--- exit; ----";
            exit;
        }
        
        $client2mg2ug  =array();
        foreach($users_groups_arr as $k=>$ug){
            $client2mg2ug[$ug['clientid']][$ug['groupmaster']][] = $ug['id'];
        }
        
        
        //get the rights for update...
        $patient_groups = Doctrine_Query::create()
        ->select('*')
        ->from('PatientGroups');
        $patient_groups_arr= $patient_groups->fetchArray();
        

        $no_groups2master = array();
        $inseted = array();
        
        if (!empty($patient_groups_arr))
        {
            foreach($patient_groups_arr as $key => $gv_values)
            {
                $custmod = Doctrine::getTable('PatientGroups')->find($gv_values['id']);
                $custmod->isdelete = '1';
                $custmod->save();
                
                if(!empty($client2mg2ug[$gv_values['clientid']][$gv_values['groupid']] ))
                {
                    foreach($client2mg2ug[$gv_values['clientid']][$gv_values['groupid']] as $k => $client_gr_id)
                    {
                        $grp = new PatientGroups();
                        $grp->master_groupid = $gv_values['groupid'];
                        $grp->clientid = $gv_values['clientid'];
                        $grp->groupid = $client_gr_id;
                        $grp->ipid = $gv_values['ipid'];
                        $grp->save();
                        
                        $inseted[] = array(
                            'clientid'=>$gv_values['clientid'],
                            'master_group_id'=>$gv_values['groupid'],
                            'groupid'=>$client_gr_id
                        ) ;
                    }
                } else{
                    $no_groups2master[] = array(
                        'clientid'=>$gv_values['clientid'],
                        'master_groupid'=>$gv_values['master_groupid'],
                    ) ;
                }
            }
        }
        
        echo "<pre>";
        echo "--- DONE ----";
        print_R($inseted);
        echo "--- no_groups2master ----";
        print_R($no_groups2master);
        echo "--- exit; ----";
        exit;
    }
    
    public function addartificialentriesexitsAction(){
    		
    	$this->_helper->layout->setLayout('layout');
    	$this->_helper->viewRenderer->setNoRender();
    		
    	// get all clients
    	$clt = Doctrine_Query::create()
    	->select("id")
    	->from('Client');
    	//->whereIn('id', array(101));
    	//->whereNotIn('id', array(1, 102, 72, 91));
    	//->orderBy("id ASC");
    	$cltarray = $clt->fetchArray();
    
    	
    	$data = array();
    	foreach($cltarray as  $cl_id)
    	{
    		$data[] = array(
    			'name' => 'Port',
    			'type' => 'entry',
    			'localization_available' => 'yes',
    			'days_availability' => 7,
    			'clientid' => $cl_id['id']
    	);
    	
    	$data[] = array(
    			'name' => 'ZVK',
    			'type' => 'entry',
    			'localization_available' => 'no',
    			'days_availability' => 0,
    			'clientid' => $cl_id['id']
    	);
    	
    	$data[] = array(
    			'name' => 'PEG',
    			'type' => 'entry',
    			'localization_available' => 'no',
    			'days_availability' => 0,
    			'clientid' => $cl_id['id']
    	);
    	
    	$data[] = array(
    			'name' => 'DK',
    			'type' => 'exit',
    			'localization_available' => 'no',
    			'days_availability' => 21,
    			'clientid' => $cl_id['id']
    	);
    	}
    	
    	//insert the defaultart for all the clients
    	$collection = new Doctrine_Collection('ArtificialEntriesExitsList');
    	$collection->fromArray($data);
    	$collection->save();
    	
    	
    	exit;
    }
    
    //ISPC-2550 Lore 17.02.2020
/*     public function addfaxtopatientcontactphonefromlocationsAction(){
        
        $this->_helper->layout->setLayout('layout');
        $this->_helper->viewRenderer->setNoRender();

        $pcp = Doctrine_Query::create()
        ->select("*")
        ->from('PatientContactphone')
        ->where('isdelete = 0')
        ->andwhere('parent_table =? ', 'Locations');
        $pcp_arr = $pcp->fetchArray();
        
        if(!empty($pcp_arr)){
            
            $lm = Doctrine_Query::create()
            ->select("*")
            ->from('Locations indexBy id')
            ->where('isdelete = 0');
            $lm_arr = $lm->fetchArray();
            
            foreach($pcp_arr as $key => $vals){
                
                $fax = $lm_arr[$vals['table_id']]['fax'];
                
                $updfax = Doctrine::getTable('PatientContactphone')->find($vals['id']);
                $updfax->fax = $fax;
                $updfax->save();
                
            }
        }
               
        echo "<pre>";
        echo "--- DONE ----";
        exit;
        
    } */
    
    public function addipid2assessmentproblemsAction(){
    		
    	$this->_helper->layout->setLayout('layout');
    	$this->_helper->viewRenderer->setNoRender();
    	
    	exit;
    	
    	$manager = Doctrine_Manager::getInstance();
		$manager->setCurrentConnection('MDAT');
		$conn = $manager->getCurrentConnection();

    	$conn->execute("UPDATE assessment_problems ap SET ap.ipid = (SELECT DISTINCT ma.ipid FROM mambo_assessment ma WHERE ma.id = ap.assessment_id) where ap.assessment_name = 'MamboAssessment' and ap.benefit_plan='yes'");
    	
    	exit;
    }
    
    //ISPC-2841 Lore 06.04.2021
    public function updateshowinchartlikebuttonAction()
    {
        //ERROR REPORTING 
/*         ini_set('display_errors', 1); 
        ini_set('display_startup_errors', 1); 
        error_reporting(E_ALL); */
        
        $this->_helper->layout->setLayout('layout');
        $this->_helper->viewRenderer->setNoRender();
        
        //    	exit;
        
        $client_events = Doctrine_Query::create()->select("*")
        ->from("ClientEvents")
        ->Where('isdelete = 0')
        ->fetchArray();
        
        $nr_updated = 0;
        foreach($client_events as $k => $v){
            // update
            $cl_upd = Doctrine::getTable('ClientEvents')->find($v['id']);
            if($cl_upd){
                $cl_upd->show_in_chart = $v['canaccess'];
                $cl_upd->save();
                $nr_updated++;
            }
        }

        echo "<pre>";
        echo "DONE !! Updated " . $nr_updated. " records ";
        exit;
        
    }
    
    //TODO-3930 Lore 15.04.2021
    public function updatepatientcoursewlassessmentAction()
    {
        
        $this->_helper->layout->setLayout('layout');
        $this->_helper->viewRenderer->setNoRender();
        
        //    	exit;
        $conn = Doctrine_Manager::getInstance()->getConnection('MDAT');
        
        $patient_course = Doctrine_Query::create()
        ->select("id, ipid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
					AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
					AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
		->from('PatientCourse')
		->Where("AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') = 'Wlassessment_PDF 2017 was created' ")
		->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'WlAssessment' ")
        ->limit(100);
		$patient_course_arr = $patient_course->fetchArray();
		
		$nr_updated = 0;
		foreach($patient_course_arr as $k => $v){
		    // update
		    /*             $pc_upd = Doctrine::getTable('PatientCourse')->find($v['id']);
		     if($pc_upd){
		     $pc_upd->course_title = Pms_CommonData::aesEncrypt( 'Wlassessment_PDF2017 wurde erstellt' );
		     $pc_upd->save();
		     $nr_updated++;
		     } */
		    
		    $sql_update = "UPDATE `patient_course` SET `course_title` = :course_title  WHERE `id`= :pc_id ";
		    $params = array(
		        'course_title' => Pms_CommonData::aesEncrypt( 'Wlassessment_PDF2017 wurde erstellt' ),
		        'pc_id'        => $v['id']
		    );
		    $stmt = $conn->execute($sql_update, $params);
		    $nr_updated++;
		    
		}
		
		echo "<pre>";
		echo "DONE !! Updated " . $nr_updated. " records ";
		exit;
        
    }
}

