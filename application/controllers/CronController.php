<?php
/**
 *  
 * Mar 7, 2018
 * 
 * $this->log_info(__METHOD__. ':' . __LINE__ .' ' . 'YOUR MESSAGE HERE');
 * $this->log_error(__METHOD__. ':' . __LINE__ .' ' . 'YOUR ERROR HERE');
 * 
 * minute(m), hour(h), day of month(dom), month(mon), day of week(dow) COMMAND
 * 
 * #ISPC LIVE CRONS
 * 15 * * * * wget --timeout=3000 --tries=1 -O /dev/null http://62.138.248.68/cron/run
 * * 1 * * * wget -O /dev/null http://62.138.248.68/cron/rundaily
 * *\/20 * * * * wget -O /dev/null http://62.138.248.68/cron/runsharefile
 * *\/10 * * * * wget -O /dev/null http://62.138.248.68/cron/runshareshortcuts
 * *\/15 * * * * wget -O /dev/null http://62.138.248.68/cron/runsharemedications
 * 50 5 * * * wget -O /dev/null http://62.138.248.68/cron/rundaily6am
 * 50 3 * * * wget -O /dev/null http://62.138.248.68/cron/runmemberinvoices
 * *\/7 * * * * wget -O /dev/null http://62.138.248.68/cron/ftp_upload
 * 15 *\/6  * * * wget -O /dev/null http://62.138.248.68/cron/rundgp
 * *\/10  * * * * wget -O /dev/null http://62.138.248.68/cron/hl7_fetchFromServers
 * *\/11  * * * * wget -O /dev/null http://62.138.248.68/cron/hl7_processMessages
 * 
 * 0 1 1 * * wget -O /dev/null http://62.138.248.68/cron/zip_logs
 * 
 * 
 * 
 * TODO: add this new, but first verify if ftp_dowload uses just the queue folder or also the uploads/cleintuploads folder for the download
 * ? ? * * * wget -O /dev/null http://62.138.248.68/cron/clean_empty_folders_in_uploads
 * ? ? * * * wget -O /dev/null http://62.138.248.68/cron/clean_files_by_age
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */
class CronController extends Zend_Controller_Action
{

    private $start_timer = 0;

    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();
        
        $this->_helper->layout->setLayout('layout_ajax');
        
        $this->start_timer = microtime(true);
    }
    
    
    /*
     * TODO set_exception_handler for this controller ... 
     * this user is NOT loghed-in, so is not catched by errorController->error
     * UNTIL then, a try-catch was added on each fn
     * 
     * https://framework.zend.com/manual/1.11/en/zend.controller.exceptions.html
     *
     */
    /*
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
        
        @set_exception_handler(array($this, "exception_handler"));
     
    }

    public function exception_handler($exception = null, $code = null)
    {
        dd(func_get_args());
        
        $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
    }
    */

    public function runAction()
    {
        try {
            
            $start = microtime(true);
            
            $mess = new Messages();
            $mess->organisation(); // every time the overview it is entered
            
            $end = microtime(true) - $start;
            
            $log_message = __METHOD__. ':' . __LINE__ .' ' . 'RUN:: Organisation check -  was executed (' . round($end, 0) . ' Seconds )';
            $this->log_info($log_message);

        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    
    public function rundgpAction()
    {
        try {
            
            $start_dgp = microtime(true);
            
            $dgp = new DgpPatientsHistory();
            $dgp->dgp_auto_export_v3(); // auto submit patients - to DGP interface
            
            $end_dgp = microtime(true) - $start_dgp;
            
            $log_message = 'RUN:: Dgp auto submit -  was executed (' . round($end_dgp, 0) . ' Seconds )';
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $log_message);
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function runsharefileAction()
    {
        try {
            $start = microtime(true);
            
            $share_files = new PatientsShare();
            $share_files->sharing_files();
            
            $end = microtime(true) - $start;
            
            $log_message = 'RUN SHARE:: Share files (' . round($end, 0) . ' Seconds )' . " shared: {$share_files->sharing_files_counter} files";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $log_message);
            
            exit();
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function runsharemedicationsAction()
    {
        try {
            
            $start = microtime(true);
            
            $share = new PatientsShare();
            $share->sharing_drug_plans();
            
            $end = microtime(true) - $start;
            
            $log_message = 'RUN SHARE:: Share drug plans  (' . round($end, 0) . ' Seconds )';
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $log_message);
            
            exit();
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function runshareshortcutsAction()
    {
        try {
            
            $start = microtime(true);
            
            $share_sh = new PatientsShare();
            $share_sh->share_shortcuts(true); // share only for patients from specific clients SH and BAY
            
            $end = microtime(true) - $start;
            
            $log_message = 'RUN SHARE:: Share CLIENT  shortcuts  (' . round($end, 0) . ' Seconds )';
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $log_message);
            
            exit();
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function runallshareshortcutsAction()
    {
        try {
            
            $start = microtime(true);
            
            $share_sh = new PatientsShare();
            $share_sh->share_shortcuts(); // share all shortcuts, added after sync, and not shared.
            
            $end = microtime(true) - $start;
            
            $log_message = 'RUN SHARE:: Share CLIENT  shortcuts  (' . round($end, 0) . ' Seconds )';
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $log_message);
            
            exit();
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function runshareshortcutsipidsAction()
    {
        try {
            $start = microtime(true);
            
            $share_sh = new PatientsShare();
            $share_sh->share_shortcuts_ipids(); // share all shortcuts, added after sync, and not shared.
            
            $end = microtime(true) - $start;
            
            $log_message = 'RUN SHARE:: Share shortcuts special_ipids  (' . round($end, 0) . ' Seconds )';
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $log_message);
            
            exit();
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function rundailyAction()
    {
        try {
            $log_message = 'RUN SHARE:: Share shortcuts special_ipids  (' . round($end, 0) . ' Seconds )';
            $this->log_info('---------------------------------------------------------------------------------- ');
            $this->log_info($log_message);
            
            set_time_limit(60 * 8);
            
            $start_master = microtime(true);
            
            $mess = new Messages();
            
            $start = microtime(true);
            $anlage6weeks = $mess->anlage6weeks(); // once a day - uses runfile.txt
            $end = microtime(true) - $start;
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'RUN DAILY:: Anlage 6 weeks check -  was executed  (' . round($end, 0) . ' Seconds )');
            
            $start = microtime(true);
            $anlage4weeks = $mess->anlage4weeks(); // once a day - uses runfile_volversorgung.txt
            $end = microtime(true) - $start;
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'RUN DAILY:: Anlage 4 weeks check -  was executed  (' . round($end, 2) . ' Seconds )');
            
            $start = microtime(true);
            $mess->anlage25days(); // once a day - uses runfile25daysvv.txt
            $end = microtime(true) - $start;
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'RUN DAILY:: anlage25days check -  was executed  (' . round($end, 2) . ' Seconds )');
            
            $start = microtime(true);
            $mess->send_coordinator_todos(); // once a day - usses public/run/
            $end = microtime(true) - $start;
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'RUN DAILY:: Sent todos to coordinations -  was executed  (' . round($end, 2) . ' Seconds )');
            
            $start = microtime(true);
            $vw_colorstatus = new VwColorStatuses();
            $vw_colorstatus->reactivate_status();
            $end = microtime(true) - $start;
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'RUN DAILY:: Vw - status green was created for all ended status periods  (' . round($end, 2) . ' Seconds )');
            
            // ispc 1739 p.27
            $start = microtime(true);
            Member::set_active_inactiv_members();
            $end = microtime(true) - $start;
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'RUN DAILY:: Member - set active/inactive for all memberships  (' . round($end, 2) . ' Seconds )');
            
            
            //ispc 2079
            $start = microtime(true);
            PatientHealthInsurance::reset_exemption_till_date();
            $end = microtime(true) - $start;
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'RUN DAILY:: Patient health insurance - remove "free of charge"(rezeptgebuhrenbefreiung) for all patients  with   exemption_till_date in the past ('.round($end, 2) .' Seconds )');
            
            //ISPC-2417 Ancuta 18.12.2019
            //Ancuta commented  on 12.02.2020
            /* 
            $start = microtime(true);
            $mess->todo_reminder_notification(); 
            $end = microtime(true) - $start;
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'RUN DAILY:: Sent reminders for TODO -  was executed  (' . round($end, 2) . ' Seconds )');
             */
            //--            
            
            // ispc 1842 - auto-generate member invoices for each sepa settings
            // $start = microtime(true);
            // $cleintids = Client::get_all_clients_ids();
            // foreach($cleintids as $id){
            // MembersSepaSettings :: set_autogenerate_memeber_invoices($id);
            // }
            // $time_elapsed_secs = microtime(true) - $start;
            // echo "Members -> sepa_settings -> invoices for all " . count($cleintids) . " clients, performed in ".round($time_elapsed_secs, 0)." Seconds";
            // $log->info("RUN DAILY:: Members -> sepa_settings -> invoices for all " . count($cleintids) . " clients, performed in ".round($time_elapsed_secs, 0)." Seconds");
            // echo '<br/>';
            
            $end_master = microtime(true) - $start_master;
            
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . "RUN DAILY:: TOTAL " . round($end_master, 2) . " Seconds");
            
            exit();
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    
    
    
    public function runtodoreminderAction()
    {
             
        try {
            
            $start_dgp = microtime(true);
            
            $mess = new Messages();
            $mess->todo_reminder_notification();
            
            $end_dgp = microtime(true) - $start_dgp;
            
            $log_message = 'RUN DAILY:: Sent reminders for TODO -  was executed  (' . round($end_dgp, 0) . ' Seconds )';
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $log_message);
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
    
    
    

    private static function datesOfNextWeek($offset = 1)
    {
        $dates = array();
        $date = time(); // get current date.
        
        if ((int) $offset < 1) {
            $date = strtotime("last Monday");
        } else {
            while (date('w', $date += 86400 * $offset) != 1); // find the next Monday.
        }
        
        // while (date('w', $date += 86400*$offset) != 1); // find the next Monday.
        for ($i = 0; $i < 7; $i ++) { // get the 7 dates from it.
            $dates[] = date('Y-m-d', $date + $i * 86400);
        }
        return $dates;
    }

    public function rundaily6amAction()
    {
        try {
            // $this->_helper->viewRenderer->setNoRender ();
            // $this->_helper->layout->setLayout ( 'layout_ajax' );
            set_time_limit(60 * 6);
            
            /*
             * Run cron job only if it isn't already running
             *
             * from bash:
             * http://stackoverflow.com/questions/2366693/run-cron-job-only-if-it-isnt-already-running
             * rsanden's solution
             *
             * int getmypid ( void ) (PHP 4, PHP 5, PHP 7)
             * Returns the current PHP process ID, or FALSE on error.
             *
             */
            // ispc 1533 auto-assign it is run only on Friday
            if (date("N") == 5) { // ispc 1855 changed from 7=Sunday into 5=Friday
                $start = microtime(true);
                // $datesOfNextWeek = self :: datesOfNextWeek();
                
                $datesOfNextWeek_1 = self::datesOfNextWeek(1);
                $datesOfNextWeek_2 = self::datesOfNextWeek(2);
                
                /*
                 * ispc 1855 - changed from 1 week into 2 weeks in advance
                 * no changes ware made to the actual autoassign function,
                 * so this means
                 * the autoassign for 2nd week will be performed allways-TWICE,
                 * what will be the drop in performance ? ( -2x since it is 'run twice' )
                 * how can this be prevented ? taking into account the fact that patients have allready saved-settings
                 *
                 * option:
                 * on patient >> edit settings - we autoassign for the next 2 weeks (this edit is performed only manualy by the client, and it's allready being added/performed because of the 2 weeks in advance request )
                 * here on cronjob to autoassign for the second week only
                 * create a script and run-once for the patients with SAVED settings, to trigger-update them
                 *
                 */
                
                $datesOfNextWeek = array_merge($datesOfNextWeek_1, $datesOfNextWeek_2);
                
                $cleintids = Client::get_all_clients_ids();
                
                $this->log_info( __METHOD__. ':' . __LINE__ .' ' . "Start " . __FUNCTION__ . ' for clients_count=' . count($cleintids) . " days_count=" . count($datesOfNextWeek));
                
                // it will perform the job for each day in the next week
                foreach ($cleintids as $id) {
                    
                    $start_client = microtime(true);
                    
                    $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'Start clientid = ' . $id);
                    
                    foreach ($datesOfNextWeek as $day) {
                        DailyPlanningVisits::set_autoasign_visits_cronjob($id, $day, false, true);
                    }
                    
                    $time_elapsed_client = microtime(true) - $start_client;
                    
                    $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'End clientid = ' . $id . ", took " . round($time_elapsed_client));
                }
                $time_elapsed_secs = microtime(true) - $start;
                $msg = "Roster -> dayplanningnew -> AutoAssign visits for all " . count($cleintids) . " clients, performed in " . round($time_elapsed_secs, 0) . " Seconds";
                
                
                $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $msg);
            }
            
            // send emails for /studypool/settings
            $this->_studypool_survey();
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    private function _studypool_survey()
    {
        try {
            set_time_limit(60 * 6);
            
            $start = microtime(true);
            
            $cleintids = Client::get_all_clients_ids();
            
            $cleintids_with_module_154 = Modules::clients2modules(154, $cleintids);
            $emails_2_contacts_cnt = 0;
            $pdf_2_contacts_cnt = 0;
            
            foreach ($cleintids_with_module_154 as $id) {
                
                $form = new Application_Form_Studypool(); //why in foreach? i forgot
                $result = $form->create_studypool_emails_letters($id);
                
                if (is_array($result)) {
                    $emails_2_contacts_cnt += $result['emails_2_contacts_cnt'];
                    $pdf_2_contacts_cnt += $result['pdf_2_contacts_cnt'];
                }
//                 $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $id . ": " . print_r($result, true) . "<br />" . PHP_EOL);
            }
            
            $end = microtime(true) - $start;
            
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($end, 2) . " Seconds ) : emails_2_contacts_cnt:{$emails_2_contacts_cnt},  pdf_2_contacts_cnt:{$pdf_2_contacts_cnt}";
            
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /**
     * @author Ancuta
     * 12.09.2019
     * ISPC-2411
     * used to send email - with survey link
     * 
     */
    public function rundailysendsurveysAction()
    {
        try {
            set_time_limit(60 * 6);
            
            $this->_send_client_survey();
            
        } catch (Exception $e) {
    
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    private function _send_client_survey()
    {
        try {
            set_time_limit(60 * 6);
            
            $start = microtime(true);
            
            $cleintids = Client::get_all_clients_ids();
            
            $cleintids_with_module_197 = Modules::clients2modules(197, $cleintids);
            
       
            $emails_2_contacts_cnt = 0;
            $pdf_2_contacts_cnt = 0;
            
            if (Zend_Registry::isRegistered('mypain')) {
                $mypain_cfg = Zend_Registry::get('mypain');
                $this->_ipos_survey_id = $mypain_cfg['ipos']['chain'];
            } 
            else 
            {
                $message = "[ " . __FUNCTION__ . " ] -  : MyPain Confing missing";
                $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
                exit; //my-pain not configured
                
            }
            //dd($cleintids_with_module_197);
            foreach ($cleintids_with_module_197 as $id) {
                
                $form = new Application_Form_ClientSurveySettings(); //why in foreach? i forgot
                $result = $form->create_survey_emails($id,$this->_ipos_survey_id);
                
                if (is_array($result)) {
                    $emails_2_contacts_cnt += $result['emails_2_recipients_cnt'];
                }
//                 $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $id . ": " . print_r($result, true) . "<br />" . PHP_EOL);
            }
            
            $end = microtime(true) - $start;
            
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($end, 2) . " Seconds ) : sent_emails_cnt :{$emails_2_contacts_cnt}";
            
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
    /**
     * @author Ancuta
     * 13.09.2019
     * ISPC-2411
     * used to retrieve survey results
     *
     */
    public function rundailygetsurveysAction()
    {
        try {
            set_time_limit(60 * 6);
            
            $this->_fetch_client_survey_data();
    
        } catch (Exception $e) {
    
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
    
    private function _fetch_client_survey_data()
    {
        try {
            set_time_limit(60 * 6);
            
            $start = microtime(true);
            
            $cleintids = Client::get_all_clients_ids();
            
            $cleintids_with_module_197 = Modules::clients2modules(197, $cleintids);
            
       
            $emails_2_contacts_cnt = 0;
            $pdf_2_contacts_cnt = 0;
            
            if (Zend_Registry::isRegistered('mypain')) {
                $mypain_cfg = Zend_Registry::get('mypain');
                $this->_ipos_survey_id = $mypain_cfg['ipos']['chain'];
            } 
            else 
            {
                $message = "[ " . __FUNCTION__ . " ] -  : MyPain Confing missing";
                $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
                exit; //my-pain not configured
                
            }
            
            
            $srult_for_patients_cnt= 0;
            foreach ($cleintids_with_module_197 as $id) {
                
                $form = new Application_Form_ClientSurveySettings(); //why in foreach? i forgot
                $result = $form->_fetch_survey_data($id,$this->_ipos_survey_id);
                
                if (is_array($result)) {
                    $srult_for_patients_cnt += $result['score_retrived_for'];
                }
            }
            
            $end = microtime(true) - $start;
           
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($end, 2) . " Seconds ) : results retrived for :{$srult_for_patients_cnt}";
            
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /*
     * this is a test action for us dev's
     * do NOT use result of this fn or other stuff from this, in production
     */
    public function testAction()
    {
        if ( APPLICATION_ENV !== 'development') {
            return;
        }
        
        
        $this->_helper->viewRenderer->setNoRender ();
        $this->_helper->layout->setLayout ( 'layout' );
        
        $share_files = new PatientsShare();
        $share_files->sharing_files();
        
        
        ddecho($share_files->sharing_files_counter);
        
        
        return;
        die("@dev testing zone");
        
    }

    public function ftpuploadAction()
    {
        try {
            // */15 * * * * wget http://10.0.0.36/ispc20172/public/cron/ftp_upload
            
            /**
             * $max_duration = maximum number of seconds, before we say this function is broken/failed
             */
            $max_duration = 60 * 30; // = 1800seconds = 30minutes
            
            /**
             * limit how many files to upload each time function runs
             * $upload_limit = NULL; => unlimited, upload all
             */
            // $upload_limit = 100;
            $upload_limit = NULL;
            
          
            
            $lock_file = sys_get_temp_dir() . "/" . "cronjob_lock_" . __CLASS__ . "_" . __FUNCTION__;
            $now_time = time();
            
            if (is_file($lock_file)) {
                // cronjob in progress.. or failed
                
                $lock_file_filetime = filemtime($lock_file);
                
                if (($now_time - $lock_file_filetime) > $max_duration) {
                    // cronjob failed
                    
                    $message = "Cron took longer than {$max_duration} seconds, lock file still exists at:{$lock_file} , filemtime={$lock_file_filetime}... die().";
    
                    $this->log_error(__METHOD__. ':' . __LINE__ .' ' . $message);
                    
                    die($message);
                }
                
            } elseif ($filehandle = fopen($lock_file, "w+")) {
                // start new ftp upload
                fwrite($filehandle, $now_time . "\n" . date("Y-m-d H:i:s"));
                
                // if (!flock($filehandle, LOCK_EX | LOCK_NB))
                // {
                // //failed to start new upload
                // $message = "Cannot lock the file {$lock_file}";
                // self::errormail($message);
                // self::log_error($message);
                // die($message);
                // }
                
                $start = microtime(true);
                
                Pms_CommonData::ftp_put_cron_upload($upload_limit);
                
                $end = microtime(true) - $start;
                
                // flock($filehandle, LOCK_UN); // don't forget to release the lock
                fclose($filehandle);
                unlink($lock_file);
                
                $message = "[ " . __FUNCTION__ . " ] - took (" . round($end, 2) . " Seconds )";
                
                $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
                    
            } else {
                // cannot create lock file
                $message = "Cannot create a lock file for this cronjob - {$lock_file}";
                
                $this->log_error( __METHOD__. ':' . __LINE__ .' ' . $message);
                
            }
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /**
     * clean_empty_folders_in_uploads
     * 
     * clean Empty Folders from this parent folders $dir2clean
     * 
     * only if folder is older then x minutes = $folder_minimum_age
     */
    public function cleanemptyfoldersinuploadsAction()
    {
        /*
         * if testmode, then unlink(file) is bypassed...
         * testmode=true is for dry-run
         * [production] you must check public/logs/croninfo/today.log
         * [development] and [staging] it also echoes
         */
        $testMode = true;
        
        try {
            
            $folder_minimum_age = 60 * 60 * 24 * 30; // 30 days in seconds
                                                
            // $today_unix_time = strtotime("today +2day"); // test
            $today_unix_time = strtotime("today");
            
            $dir2clean = array();
            $dir2clean[] = PDF_PATH;
            $dir2clean[] = CLIENTUPLOADS_PATH;
            $dir2clean[] = FTP_DOWNLOAD_PATH;
            $dir2clean[] = FTP_QUEUE_PATH;
            
            // $this->_helper->viewRenderer->setNoRender ();
            // $this->_helper->layout->setLayout ( 'layout_ajax' );
            
            $rmdir_counter = 0;
            
            foreach ($dir2clean as $dir) {
                
                if (! is_dir($dir) || ! is_readable($dir)) {
                    continue;
                }
                
                $handle = opendir($dir);
                
                while (false !== ($entry = readdir($handle))) {
                    
                    if ($entry != "." && $entry != "..") {
                        
                        $empty_folder = $dir . "/" . $entry;
                        
                        if ($this->_isEmptyFolder($empty_folder) === true) {
                            
                            $filemtime = filemtime($empty_folder);
                            
                            // echo $today_unix_time ." ";
                            // echo $filemtime ."<hr>";
                            // echo $entry ." EMPTY <hr>";
                            
                            if (($today_unix_time - $filemtime) > $folder_minimum_age) {
                                
                                // delete folder!
                                // echo "!!!!delete folder <hr>";
                                if ($testMode) {
                                   $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'testMode without rmdir ' . $empty_folder);                                    
                                }
                                else {
                                    if (rmdir($empty_folder)) {
                                        $rmdir_counter ++;
                                        $this->log_info( __METHOD__. ':' . __LINE__ .' ' . ' rmdir ' . $empty_folder);
                                    } 
                                }
                            }
                        }
                    }
                }
                
                closedir($handle);
            }
            $time_elapsed = microtime(true) - $this->start_timer;
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($time_elapsed, 2) . " Seconds ), deleted " . $rmdir_counter . " folders";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    private function _isEmptyFolder($dir)
    {
        if ( ! is_dir($dir) || ! is_readable($dir)) {
            return false;
        }
        
        if ($handle = opendir($dir)) {
        
            while (false !== ($entry = readdir($handle))) {
                
                if ($entry != "." && $entry != "..") {
                    
                    closedir($handle);
                    return false;
                }
            }
            
            closedir($handle);
            
            return true;
        }
        
        return false;
    }

    
    
    
    private function log_error($message = '')
    {
        if ($logger = $this->getHelper('Log')) {
            $logger->cronerror($message);
        }   
    }

    private function log_info($message = '')
    {
        if ($logger = $this->getHelper('Log')) {
            $logger->croninfo($message);
        }
    }

    public function runsepaAction()
    {
        return; // !!! this is now disabled
        
        // ispc 1842 - auto-generate member invoices for each sepa settings
        $start = microtime(true);
        $cleintids = Client::get_all_clients_ids();
        foreach ($cleintids as $id) {
            MembersSepaSettings::set_autogenerate_memeber_invoices($id);
        }
        $time_elapsed_secs = microtime(true) - $start;
        
        $this->log_info("RUN DAILY:: Members -> sepa_settings -> invoices for all " . count($cleintids) . " clients, performed in " . round($time_elapsed_secs, 0) . " Seconds");
        
    }

    /**
     * this is the development fn,
     * this will act like a proxy for dev.smart-q.de:10088 <-> developer's pc
     */
    private function __elviAction()
    {
        $this->getHelper('Log')->info("========= " . __FUNCTION__ . " =========");
        // this is step2 from elvi pdf
        
        // debug only
        $this->_elvi_config = Zend_Registry::get('elvi');
        
        $url = 'http://10.0.0.36/ispc2017_08/public/elvi/claudiu';
        
        $body = $this->getRequest()->getRawBody();
        
        
        // @claudiu my debug
        // elvi request log
        $et = new ElviTest();
        $et->received = $body;
        
        
        if (empty($body)) {
            // exit by default
            $et->sent = array('invalid 1, received body was empty, we replied: invalid request');
            $et->save();
            
            $data['messages'] = array(
                0 => 'invalid request'
            );
            
            $this->_helper->json->sendJson($data);
            
            $this->getHelper('Log')->info("sendJson: {$data}");
            
            exit(); // here for readability
        }
        
        $et->save();
        
        // $body = '{"processToken":"69dfe8cb-b4e7-4fcc-8b58-b910ebfee52b","identifier":"elvisystem","pass":"develvi","hash":"dc1765fa42ab52ccd4df4467310a3478c1180e1bf1ab000ab1f325c758040100"}';
        $data = Zend_Json::decode($body);
        
        
        $processToken = $data['processToken'];
        $hash = $data['hash'];
        // @todo remove this ternary in production , leave from _config
        $elvi_identifier = ! empty($data['identifier']) ? $data['identifier'] : $this->_elvi_config['step2']['identifier'];
        $elvi_password = ! empty($data['pass']) ? $data['pass'] : $this->_elvi_config['step2']['password'];
        
        
        if (empty($processToken) || empty($hash)) {
            // exit by default
            $et->sent = array('invalid 2');
            $et->save();
            
            $data['messages'] = array(
                0 => 'invalid request'
            );
            
            $this->getHelper('Log')->info("A2 sendJson: ". print_r($data, true) );
            $this->_helper->json->sendJson($data);
            
            
            exit(); // here for readability
        }
        
        // o hash = sha256(processToken+identifier+password+currentDate(dd.mm.yyyy)+salt
//         $compiled_hash = Zend_Crypt::hash('SHA256', $processToken . $elvi_identifier . $elvi_password . date('d.m.Y') . $this->_elvi_config['step2']['salt']);
        $compiled_hash = Zend_Crypt::hash('SHA512', $processToken . $elvi_identifier . $elvi_password . date('d.m.Y'));
        
        
        if ($compiled_hash != $hash) {
            // exit by invalid hash
            
            $et->sent = array( 'invalid 3, invalid hash' );
            $et->save();
            
            $data['messages'] = array(
                1 => 'invalid hash'
            );
            
            $data['serverTime'] = time();
            
            $this->_helper->json->sendJson($data);
            
            exit(); // here for readability
        }
        
        
        
        //@claudiu my debug
        $cl = new Zend_Http_Client($url);
        $cl->setRawData($body, 'application/json;charset=UTF-8')->request('POST');
        
        $data = $cl->getLastResponse()->getRawBody();

        $this->getResponse()->setHeader('Content-Type', 'application/json')->setBody($data)->sendResponse();
        
        $et->sent = $data;
        $et->save();
        
        exit;
    }

    /**
     * this is the production/staging fn,
     */
    public function elviAction()
    {
        // debug only
        $this->_elvi_config = Zend_Registry::get('elvi');
    
        $body = $this->getRequest()->getRawBody();
        $data = [];
        
        if (APPLICATION_ENV != 'production') {
            $et = new ElviTest();
            $et->received = $body;
            $et->save();
        }
    
    
        if (empty($body)) {
            // exit by default, fail-safe, this should never happen
            
            $error_message = "invalid 1, received body was empty";
            
            if (APPLICATION_ENV != 'production') {
                $et->sent = array($error_message);
                $et->save();
            }
    
            $data['messages'] = array(
                0 => $error_message
            );
    
            $this->_helper->json->sendJson($data);
    
            $this->getHelper('Log')->log($error_message);
    
            exit(); // here for readability
        }
    
        $data = Zend_Json::decode($body);
            
        $processToken = $data['processToken'];
        $hash = $data['hash'];
        
        // in production we use from _config, on dev there is a ternary exp
        $elvi_identifier = $this->_elvi_config['step2']['identifier'];
        $elvi_password = $this->_elvi_config['step2']['password'];   
    
        
        if (empty($processToken) || empty($hash)) {
            // exit by default, fail-safe, this should never happen
            
            $error_message = "invalid 2 , empty token or hash";
            
            if (APPLICATION_ENV != 'production') {
                $et->sent = array($error_message);
                $et->save();
            }
    
            $data['messages'] = array(
                0 => $error_message
            );
    
            $this->getHelper('Log')->log($error_message);
            $this->_helper->json->sendJson($data);
    
    
            exit(); // here for readability
        }
        
        $compiled_hash = Zend_Crypt::hash('SHA512', $processToken . $elvi_identifier . $elvi_password . date('d.m.Y'));
    
    
        if ($compiled_hash != $hash) {
            // exit by invalid hash, fail-safe, this should never happen
    
            $error_message ="invalid 3, invalid hash";
            if (APPLICATION_ENV != 'production') {
                $et->sent = array($error_message);
                $et->save();
            }
    
            $data['messages'] = array(
                0 => $error_message
            );
    
            $data['serverTime'] = time();
    
            $this->getHelper('Log')->log($error_message);
            
            $this->_helper->json->sendJson($data);
    
    
            exit(); // here for readability
        }
    
    
        $action = ElviTransactionsTable::getInstance()->findOneBy('processToken', $processToken, Doctrine_Core::HYDRATE_ARRAY);
        
        $result = (! empty($action) && ! empty($action['request'])) ? $action['request'] : ['messages' => "invalid 4, processToken not found"];
    
        $result = Zend_Json::encode($result);
        
        $this->getResponse()
        ->setHeader('Content-Type', 'application/json')
        ->setBody($result)
        ->sendResponse();
        
        
        if (APPLICATION_ENV != 'production') {
            $et->sent = $result;
            $et->save();
        }
        
        exit();
    
        return;
    }
    
    
    
    
    
    
    
    
    
    public function runmemberinvoicesAction()
    {
        try {
            set_time_limit(60 * 10); // this needed?
            
            /*
             * start foreach Clientid auto-generate Ungenerated MemberInvoices for Memberships
             * TODO : optimize fetchUngeneratedMemberInvoicesByClientid and generatemembersinvoice if they are slow
             */
            $cleintids = Client::get_all_clients_ids();
            $invoice_counter = 0;
            foreach ($cleintids as $id) {
                
                $mi = new MembersInvoices();
                $ungenerated_invoices_data = $mi->fetchUngeneratedMemberInvoicesByClientid($id);
                $generated_temp_invoices = array();
                if (! empty($ungenerated_invoices_data)) {
                    
                    $af_mi = new Application_Form_MembersInvoices();
                    $generated_temp_invoices = $af_mi->generatemembersinvoice($ungenerated_invoices_data, $id);
                    $invoice_counter += count($generated_temp_invoices);
                }
            }
            
            $time_elapsed_secs = microtime(true) - $this->start_timer;
            $message = "RUN DAILY:: Members -> auto-generate membership invoices for " . count($cleintids) . " clients, total invoices gen " . $invoice_counter . ", performed in " . round($time_elapsed_secs, 0) . " Seconds";
            
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            /*
             * eo foreach Clientid auto-generate Ungenerated MemberInvoices for Memberships
             */
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
    
    
    
    /**
     * http://php.net/manual/ro/function.readdir.php
     * frasq at frasq dot org ¶
     */
    private function _listdiraux($dir, &$files) {
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $filepath = $dir == '.' ? $file : $dir . '/' . $file;
            if (is_link($filepath))
                continue;
            if (is_file($filepath))
                $files[] = $filepath;
            else if (is_dir($filepath))
                $this->_listdiraux($filepath, $files);
        }
        closedir($handle);
    }
    
    
    /**
     * clean_files_by_age
     *
     * clean files from this parent folders $dir2clean
     *
     * only if $file_filters
     */
    public function cleanfilesbyageAction()
    {
        /*
         * if testmode, then unlink(file) is bypassed...
         * testmode=true is for dry-run
         * [production] you must check public/logs/croninfo/today.log 
         * [development] and [staging] it also echoes 
         */
        $testMode = false;
        
        /*
         * file from $dir2clean are moved here
         */
        $trash_destination_path = dirname(APPLICATION_PATH) . '/ftp_trash';
       
        try {
            
            $dir2clean = array(
                PDFDOCX_PATH,
                PDF_PATH,
                CLIENTUPLOADS_PATH, 
                FTP_DOWNLOAD_PATH,
                //FTP_QUEUE_PATH, //this should not be here ..
                APPLICATION_PATH . "/../public/log", // must be the same log folder defined in logs.ini
            );
            
            //filter the files
            $file_filters = array(
                
                //mandatory, 
                //number, in seconds, 10days = 60 * 60 * 24 * 10, 30days= 60 * 60 * 24 * 30
                'minimum_age' => 60 * 60 * 24 * 30, 
                
                //mandatory,
                //string or array,  case IN-sensitive, file extensions without the .dot
                //only this extensions can be deleted
                'extension' => array('docx', 'pdf', 'zip', 'log'),
                
                //optional
                //string or array, case sensitive, file name starts with
                //'prefix' => 'invoice', 
                'prefix' => NULL, 
                
                //optional
                //string, file owner user
                'owner_user' => 'www-data', 
                
                //optional
                //string, file owner group
                'owner_group' => 'www-data',
                
            );
            
            if ( ! is_array($file_filters['extension'])) $file_filters['extension'] = array($file_filters['extension']);
            if ( ! empty($file_filters['prefix']) && ! is_array($file_filters['prefix'])) $file_filters['prefix'] = array($file_filters['prefix']);
            
            $file_filters['extension'] = array_map('strtolower', $file_filters['extension']);
    
            if ( empty($file_filters['minimum_age'])) {
                die('mandatory file minimum_age');
            }
            
            if ( empty($file_filters['extension'])) {
                die('mandatory file extension');
            }
            
            
            // $today_unix_time = strtotime("today +2day"); // test
            $today_unix_time = strtotime("today");
            
    
            $this->log_info('start to clear files older than '. date("d.m.Y H:i", ($today_unix_time - $file_filters['minimum_age'])) . PHP_EOL . 'from folders:'.print_r($dir2clean, true));
            
            $rmdir_counter = 0;
        
            foreach ($dir2clean as $dir) {
        
                if ( ! is_dir($dir) || ! is_readable($dir)) {
                    continue;
                }
    
                $files = array();
                $this->_listdiraux($dir, $files);
                sort($files, SORT_LOCALE_STRING);
                
                
                foreach ($files as $file) {
                    
                    $fileowner = fileowner($file);
                    
                    if ( ! empty($file_filters['owner_user'])) {
                        $posix_getpwuid = posix_getpwuid($fileowner);
                        if ($posix_getpwuid['name'] != $file_filters['owner_user']) {
                            //user owner do not match
                            continue;
                        }
                    }
                    
                    if ( ! empty($file_filters['owner_group'])) {
                        $posix_getgrgid = posix_getgrgid($fileowner);
                        if ($posix_getgrgid['name'] != $file_filters['owner_group']) {
                            //group owner do not match
                            continue;
                        }
                    }
                    
                    $filemtime = filemtime($file);
                    if (($today_unix_time - $filemtime) < (int)$file_filters['minimum_age']) {
                        //file is too new
                        continue;
                    }
                    
                    //mixed pathinfo ( string $path [, int $options = PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME ] )
                    $pathinfo = pathinfo ( $file);
                    
                    if ( ! in_array(strtolower($pathinfo['extension']), $file_filters['extension'])) {
                        //file extension do not match
                        continue;
                    }
                    
                    if ( ! empty($file_filters['prefix']) && ($prefix = Pms_CommonData::strpos_arr($pathinfo['filename'], $file_filters['prefix'], 0)) === false) {
                        //file prefix do not match
                        continue;
                    }
                    
                    
                    //file passed the filtering... do something nice to it : DELETE
                    if ($testMode) {

                        $this->log_info( __METHOD__. ':' . __LINE__ .' ' . 'testMode without unlinked ' . $file);
                        
                    } else {
                        
                        //move the file
                        $pathinfo_file = realpath(pathinfo($file, PATHINFO_DIRNAME));
                        
                        if ($pathinfo_file)
                        {
                            $trash_target = $trash_destination_path . $pathinfo_file;
                            
                            if ( ! is_dir($trash_target)) {
                        
                                umask(022);
                                mkdir($trash_target, 0755, true);
                            }
                        
                            $cmd_mv = "mv -t '{$trash_target}' '{$file}'";
                        
                            @exec($cmd_mv);
                        }
                        
                        //remove the file
                        //@unlink($file);
                        
                        $this->log_info( __METHOD__. ':' . __LINE__ .' ' . ' moved ' . $file);
                    }
                    
                }
            }
            
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        
    }
    
    
    /**
     * hl7_fetchFromServers
     * @cla on 02.07.2018
     *  
     * works ONLY if you have same Zend_Registry::get('salt')
     * correct way would be to have different keys for each server.. and master knows slave key, or slave send plain text
     * 
     */
    public function hl7fetchfromserversAction() 
    {
        try {
            
            Hl7MessagesReceived::cronjob_hl7_fetch_from_servers();
    
            $time_elapsed = microtime(true) - $this->start_timer;
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($time_elapsed, 2) . " Seconds )";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
        } catch (Exception $e) {
        
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        
    }
    
    
    
    
    /**
     * hl7_processMessages
     * @cla on 02.07.2018
     * 
     * limit(1000) messages to process in one pass
     * 
     */
    public function hl7processmessagesAction()
    {
        try {
            
            $limit = 1000;
        
            Hl7MessagesProcessed::cronjob_hl7_process_messages($limit);
            
            $time_elapsed = microtime(true) - $this->start_timer;
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($time_elapsed, 2) . " Seconds )";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
            exit;
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());            
        }
        
    }

    /**
     * ISPC-2459
     *  activation for all patients in the night at 00:15 to activate all active patients.
     */
    public function hl7sendactivationtoserversAction()
    {
        if ( ! ($hl7_send_cfg = $this->getInvokeArg('bootstrap')->getOption('HL7_send')) ){
            return;
        }
        
        
        $hl7_clients= array();
        foreach ($hl7_send_cfg as $client_id => $hl7s) {
            if ($hl7s['ft1']['host'] && $hl7s['ft1']['port']) {
                $hl7_clients[] = $client_id;
            }
        }
      
        $messages = array();
        $serverHL7_addr = "";
        $serverHL7_port = "";
        $hl7_proxy_sender_url = "";
        $resultsACK = array();
        
    
        foreach($hl7_clients as $k=>$client_id){
            
            $serverHL7_addr = $hl7_send_cfg[$client_id]['ft1']['host'];
            $serverHL7_port = $hl7_send_cfg[$client_id]['ft1']['port'];
            $hl7_proxy_sender_url = $hl7_send_cfg[$client_id]['ft1']['proxy_sender_url'];
            
            try {
                
                $messages[$client_id] = Hl7MessagesSent::cronjob_hl7_sendActivation_messages($client_id);
 
                if(!empty($messages[$client_id])){
                    $sent_days2ipid = array();
                    foreach ($messages[$client_id] as $ipid => $msg_info ){
                            
                        
                        if($msg_info['client'] == $client_id){
                            
                            $message_string = $msg_info['msg'];
                            $item_day = $msg_info['day'];
                            
                            // check if message was sent for ipid, for curent day
                            $allow_date_for_activation = false;
                            $allow_date_for_activation = Hl7MessagesSent::find_sent_by_patient_and_day($ipid,$item_day);
                          
                            if( $allow_date_for_activation){
        
                                $messageRESPONSE = '';
                                $send_ok =  null; // if MSA-1 == AA => 'yes', elseif other code => 'no',  else => 'null'
                                
                                try {
                                    
                                    if ( ! empty($hl7_proxy_sender_url)) {
                                        $messageRESPONSE = $this->__invoicesnew_hl7_activation_transmit_CURL($hl7_proxy_sender_url, $serverHL7_addr, $serverHL7_port, $message_string);
                                    } else {
                                        $hl7_connection = new Net_HL7_Connection($serverHL7_addr, $serverHL7_port);
                                        $messageRESPONSE = $hl7_connection->send($message_string);
                                    }
                                    
                                    if ( ! empty($messageRESPONSE)) {
                                        $messageMSA = new Net_HL7_Message($messageRESPONSE);
                                        $MSA = $messageMSA->getSegmentsByName('MSA')[0];
                                        if ($MSA instanceof Net_HL7_Segments_MSA && $MSA->getAcknowledgementCode() == "AA") {
                                            //all was ok?
                                            $send_ok =  'yes';
                                        } else {
                                            $send_ok =  'no';// you have as AE or AC .. get messages for error it they send any
                                        }
                                    }
                                } catch (Exception $e) {
                                    
                                }
                                
                                
                                $resultsACK[$ipid][$item_day] = Hl7MessagesSentTable::getInstance()->findOrCreateOneBy(
                                    ['parent_table',  'ipid',    'item_day',     'message_type'],
                                    ['CronActivation', $ipid, $item_day, 'ADT^A08'],
                                    [
                                        'client_id'       => $client_id,
                                        'port'            => $port,
                                        'message'         => $message_string,
                                        'message_ack'     => $messageRESPONSE,
                                        'send_trys'       => new Doctrine_Expression('send_trys + 1'),
                                        'send_ok'         => $send_ok,
                                    ]
                                    );
                                
                                $sent_days2ipid[$ipid][] = $item_day;
                            }
                        }
                    }
                }
                
                $time_elapsed = microtime(true) - $this->start_timer;
                $message = "[ " . __FUNCTION__ . " ] - took (" . round($time_elapsed, 2) . " Seconds )";
                $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
                
            } catch (Exception $e) {
                
                $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
            }
        }
    }
    
    /**
     * ISPC-2459
     * COPY of __invoicesnew_hl7_activation_transmit_CURL from InvoicenewController.php
     * @param string $hl7_proxy_sender_url
     * @param string $host
     * @param string $port
     * @param string $message
     * @return string
     */
    private function __invoicesnew_hl7_activation_transmit_CURL( $hl7_proxy_sender_url = '' , $host = '', $port = '', $message = '')
    {
        
        
        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setConfig(array(
            'curloptions' => array(
                CURLOPT_FOLLOWLOCATION  => false,
                CURLOPT_MAXREDIRS      => 0,
                CURLOPT_RETURNTRANSFER  => true,
                
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                
                CURLOPT_TIMEOUT => 15,
                CURLINFO_CONNECT_TIME => 16,
                CURLOPT_CONNECTTIMEOUT => 17,
                // 	            CURLOPT_COOKIE => $_req_cookie,
            )
        ));
        
        $httpConfig = array(
            'timeout'      => 10,// Default = 10
            'useragent'    => 'Zend_Http_Client-ISPC-HL7-CURL',// Default = Zend_Http_Client
            'keepalive'    => true,
        );
        $httpService =  new Zend_Http_Client(null, $httpConfig);
        $httpService->setAdapter($adapter);
        $httpService->setCookieJar(false);
        
        $httpService->setUri(Zend_Uri_Http::fromString($hl7_proxy_sender_url));
        $httpService->setMethod('POST');
        
        $httpService->setParameterPost([
            'port'        => $port,
            'host'        => $host,
            'message'     => base64_encode($message),
            '_hash'       => hash("crc32b", $message . $host . $port),
        ]);
        
        
        try{
            $lastReq = $httpService->request();
            
            sleep(1); //wait for the previous request to be completed
            // 		        $httpService->resetParameters(true);
            
            if ( ! $lastReq->isError()) {
                
                $httpService->resetParameters(true);
                
                return $lastReq->getBody();
                
            } else {
                
                $log_text = "__invoicesnew_hl7_activation_transmit_CURL: we had errors:" . PHP_EOL ;
                $log_text .= "request:" . $httpService->getLastRequest() .PHP_EOL;
                if($httpService->getLastResponse()){
                    $log_text .= 'response:' . $httpService->getLastResponse()->asString();
                } else{
                    $log_text .= 'NO response Y';
                }
                
                $this->getHelper('Log')->error ( $log_text );
                
                
            }
            
            // Ancuta 12.05.2020
            unset($httpService);
            // --
            
        } catch (Zend_Http_Client_Exception $e) {
            
            try {
                
                // NEW
                $log_text = "__invoicesnew_hl7_activation_transmit_CURL: Zend_Http_Client_Exception:" . $e->getMessage() . PHP_EOL;
                $log_text .= "request:" . $httpService->getLastRequest() .PHP_EOL;
                if($httpService->getLastResponse()){
                    $log_text .= 'response:' . $httpService->getLastResponse()->asString();
                } else{
                    $log_text .= 'NO response X';
                }
                
                //$this->_log_info($log_text);
                $this->getHelper('Log')->error ($log_text);
                //  ---
                
            } catch (Exception $ee) {
                
                $this->getHelper('Log')->error ("__invoicesnew_hl7_ft1_transmit_CURL: Exception:" . $ee->getMessage() . PHP_EOL . $message);
                
                $this->getHelper('Log')->error ($ee->getMessage() . "\n" . $message);
            }
            
            
        }
        
    }
    
    
    
    /**
     * on 1st of each month, zip all *.log files from/to /log/_folder/archive_month_year_day.zip
     * 
     * @return boolean
     */
    public function ziplogsAction()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        
        $resourcesOptions = $bootstrap->getOption('resources');
        
        $logersOptions =  isset($resourcesOptions['log']) ? $resourcesOptions['log'] : [];
        
        foreach ($logersOptions as $logger => $options) {
        
            if (isset($options['writerName']) && $options['writerName'] == "Stream") {
        
                $logFolders = $options['writerParams']['stream'] . '/' . $logger . '/';
                
                if (is_dir($logFolders)) {
                    
                    $zip_file_name = "archive_" . date("Y_m_d") . ".zip";
                    
                    $cmd_create_zip = "sh -c \"cd '{$logFolders}'  && zip -9 -r {$zip_file_name} *.log  && rm *.log\";";
                    
                    @exec($cmd_create_zip);
                }
                
            }
        }
        
    }
    
    
    /**
     * ISPC-2432
     * get payload from proxy server
     */
    public function mepatientfetchfromserversAction()
    {
       return;
        try {
            
            MePatientRequestsReceived::cronjob_mePatient_fetch_from_servers();
            
            $time_elapsed = microtime(true) - $this->start_timer;
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($time_elapsed, 2) . " Seconds )";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        
    }
    

    /**
     * ISPC-2432
     * proccess payload received from proxy server
     */
    public function mepateintprocessrequestsAction()
    {
        return;
        try {
            
            $limit = 1000;
            
            MePatientRequestsProcessed::cronjob_mePatient_process_requests($limit);
            
            $time_elapsed = microtime(true) - $this->start_timer;
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($time_elapsed, 2) . " Seconds )";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
            exit;
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        
    }
    
    /**
     * ISPC-2432
     * send devices to  proxy server
     */
    public function mepatientsenddevicesserversAction()
    {
        return;
        try {

            MePatientDevices::cronjob_mePatient_sendDevices_to_servers();
            
            $time_elapsed = microtime(true) - $this->start_timer;
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($time_elapsed, 2) . " Seconds )";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        
    }
    
    /**
     * ISPC-2432
     * get devices from  proxy server
     * registration_id !!! 
     */
    public function mepatientgetdevicesfromserversAction()
    {
        return;
        
        try {

            MePatientDevices::cronjob_mePatient_getDevices_from_servers();
            
            $time_elapsed = microtime(true) - $this->start_timer;
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($time_elapsed, 2) . " Seconds )";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        
    }


    /**
     * ISPC-2432
     * send push notifications
     */
    public function mepatientsendpushnotificationsAction()
    {
        try {

            MePatientDevices::cronjob_mePatient_sendPush_notifications();
            
            $time_elapsed = microtime(true) - $this->start_timer;
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($time_elapsed, 2) . " Seconds )";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
        
    }
    
    /**
     * ISPC-2609 Ancuta 28.08.2020
     */
    public function processprintjobsAction(){

        
        $this->_helper->viewRenderer->setNoRender();
        
        try {
            $print_job_limit = 5;
            $print_process = $this->__cronjob_proccess_print_jobs($print_job_limit);
            
            $time_elapsed = microtime(true) - $this->start_timer;
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($time_elapsed, 2) . " Seconds )";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    
    }
    
    public function processjobitemAction(){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        //TODO-3668 Ancuta 14.12.2020 - added member
        $logininfo->allow_cron_in_controllers = array('cron','invoicenew','invoice','patientform','internalinvoice','voluntaryworkers','member','patientformnew');
        
        $this->_helper->viewRenderer->setNoRender();
        $item_params = $this->getRequest()->getParams();
        
        if(empty($item_params['item_id'])){
            return '0';
            exit();
        }
        // get print item information
       $item_info_arr =  PrintJobsBulkTable::__get_item_info($item_params['item_id']);
       if(empty($item_info_arr)){
           return '0';
           exit();
       }
 
       $item_info = $item_info_arr[0];
       
       
       if(!empty($item_info['PrintJobsItems']['0']['invoice_id']) || !empty($item_info['PrintJobsItems']['0']['item_id']) || !empty($item_info['PrintJobsItems']['0']['ipid'])){
           
           switch ($item_info['print_controller']){
               case 'invoicenew':
                    include 'InvoicenewController.php';
                    $invoiceController = new InvoicenewController($this->_request, $this->_response);
                    
                    $inv_id = $item_info['PrintJobsItems']['0']['invoice_id'];
                    $print_params = array();
                    $print_params['invoices'] = array($inv_id);
                    $print_params['batch_print'] = '1';
                    $print_params['only_pdf'] = '1';
                    $print_params['get_pdf'] = '0';
                    $print_params['print_job'] = '1';
                    
                    $print_params['clientid'] = $item_info['clientid'];
                    $print_params['userid'] = $item_info['user'];
                    $print_params['invoice_type'] = $item_info['invoice_type'];
                    $print_params['print_job_id'] = $item_info['id'];
                    
                    $print_params['batch_temp_folder'] = $item_info['user'].'_'.$item_info['invoice_type'].'_'.$item_info['id'];
                    
                    $fname = 'pdf_invoice_'.$inv_id.'.pdf';
                    
                    $result = $invoiceController->{$item_info['print_function']}($print_params);
                    
                    //var_Dump($result); exit;
                    break;
                
               case 'invoice':
                   include 'InvoiceController.php';
                   $invoiceController = new InvoiceController($this->_request, $this->_response);
                       
                    $inv_id = $item_info['PrintJobsItems']['0']['invoice_id'];
                    $print_params = array();
                    $print_params['invoices'] = array($inv_id);
                    $print_params['batch_print'] = '1';
                    $print_params['only_pdf'] = '1';
                    $print_params['get_pdf'] = '0';
                    $print_params['print_job'] = '1';
                    
                    $print_params['clientid'] = $item_info['clientid'];
                    $print_params['userid'] = $item_info['user'];
                    $print_params['invoice_type'] = $item_info['invoice_type'];
                    $print_params['print_job_id'] = $item_info['id'];
                    $print_params['print_controller'] = $item_info['print_controller'];
                    
                    $print_params['batch_temp_folder'] = $item_info['user'].'_'.$item_info['invoice_type'].'_'.$item_info['id'];
          
                    $args = array();
                    $args['clientid'] =$item_info['clientid'];
                    $args['userid'] =$item_info['user'];
                    $args['invoices'] = array($inv_id);
                    $args['iid'] =$inv_id;
                    $args['invoiceid'] =$inv_id;
                    $args['mode'] ="pdfs";
                    $args['pdfquick'] ="1";
                    $args['bulk_print'] ="1";
                    $args['print_controller'] = $item_info['print_controller'];
                    $args['controller'] = $item_info['print_controller'];
                    $args['inv_type'] = $item_info['invoice_type'];//nd_users
                    $args['batch_temp_folder'] = $item_info['user'].'_'.$item_info['invoice_type'].'_'.$item_info['id'];;
                    
                    $result = $invoiceController->{$item_info['print_function']}($args);
                    
                    $fname = 'pdf_invoice_'.$inv_id.'.pdf';
                    
                    break;
                
               case 'internalinvoice':
                   include 'InternalinvoiceController.php';
                   $invoiceController = new InternalinvoiceController($this->_request, $this->_response);
                       
                    $inv_id = $item_info['PrintJobsItems']['0']['invoice_id'];
                    $print_params = array();
                    $print_params['invoices'] = array($inv_id);
                    $print_params['batch_print'] = '1';
                    $print_params['only_pdf'] = '1';
                    $print_params['get_pdf'] = '0';
                    $print_params['print_job'] = '1';
                    
                    $print_params['clientid'] = $item_info['clientid'];
                    $print_params['userid'] = $item_info['user'];
                    $print_params['invoice_type'] = $item_info['invoice_type'];
                    $print_params['print_job_id'] = $item_info['id'];
                    $print_params['print_controller'] = $item_info['print_controller'];
                    
                    $print_params['batch_temp_folder'] = $item_info['user'].'_'.$item_info['invoice_type'].'_'.$item_info['id'];
          
                    $args = array();
                    $args['clientid'] =$item_info['clientid'];
                    $args['userid'] =$item_info['user'];
                    $args['invoices'] = array($inv_id);
                    $args['iid'] =$inv_id;
                    $args['invoiceid'] =$inv_id;
                    $args['mode'] ="pdfs";
                    $args['pdfquick'] ="1";
                    $args['bulk_print'] ="1";
                    $args['print_controller'] = $item_info['print_controller'];
                    $args['controller'] = $item_info['print_controller'];
                    $args['inv_type'] = $item_info['invoice_type'];//nd_users
                    $args['batch_temp_folder'] = $item_info['user'].'_'.$item_info['invoice_type'].'_'.$item_info['id'];;
                    
                    $result = $invoiceController->{$item_info['print_function']}($args);
                    
                    $fname = 'pdf_invoice_'.$inv_id.'.pdf';
                    
                    break;
                    
                    
               case 'patientform':
                   include 'PatientformController.php';
                   $invoiceController = new PatientformController($this->_request, $this->_response);
                       
                    $inv_id = $item_info['PrintJobsItems']['0']['invoice_id'];
                    $print_params = array();
                    $print_params['invoices'] = array($inv_id);
                    $print_params['batch_print'] = '1';
                    $print_params['only_pdf'] = '1';
                    $print_params['get_pdf'] = '0';
                    $print_params['print_job'] = '1';
                    
                    $print_params['clientid'] = $item_info['clientid'];
                    $print_params['userid'] = $item_info['user'];
                    $print_params['invoice_type'] = $item_info['invoice_type'];
                    $print_params['print_job_id'] = $item_info['id'];
                    $print_params['print_controller'] = $item_info['print_controller'];
                    
                    $print_params['batch_temp_folder'] = $item_info['user'].'_'.$item_info['invoice_type'].'_'.$item_info['id'];
          
                    $args = array();
                    $args['clientid'] =$item_info['clientid'];
                    $args['userid'] =$item_info['user'];
                    $args['invoices'] = array($inv_id);
                    $args['iid'] =$inv_id;
                    $args['invoiceid'] =$inv_id;
                    $args['mode'] ="pdfs";
                    $args['pdfquick'] ="1";
                    $args['bulk_print'] ="1";
                    $args['print_controller'] = $item_info['print_controller'];
                    $args['controller'] = $item_info['print_controller'];
                    $args['inv_type'] = $item_info['invoice_type'];//nd_users
                    $args['batch_temp_folder'] = $item_info['user'].'_'.$item_info['invoice_type'].'_'.$item_info['id'];;
                    
                    $result = $invoiceController->{$item_info['print_function']}($args);
                    
                    $fname = 'pdf_invoice_'.$inv_id.'.pdf';
                    
                    break;
                
               case 'patientformnew':
                   include 'PatientformnewController.php';
                   $PatientformnewController = new PatientformnewController($this->_request, $this->_response);
                   
                    $db_print_params =  unserialize($item_info['print_params']);
                    $ipid = $item_info['PrintJobsItems']['0']['ipid'];
                    $item_id = $item_info['PrintJobsItems']['0']['id'];
                    $print_params = array();
                    $print_params['invoices'] = array($ipid);
                    $print_params['batch_print'] = '1';
                    $print_params['only_pdf'] = '1';
                    $print_params['get_pdf'] = '0';
                    $print_params['print_job'] = '1';
                    
                    $print_params['clientid'] = $item_info['clientid'];
                    $print_params['userid'] = $item_info['user'];
                    $print_params['invoice_type'] = $item_info['invoice_type'];
                    $print_params['print_job_id'] = $item_info['id'];
                    $print_params['print_controller'] = $item_info['print_controller'];
                    
                    $print_params['batch_temp_folder'] = $item_info['user'].'_'.$item_info['invoice_type'].'_'.$item_info['id'];
          
                    $args = array();
                    $args['clientid'] =$item_info['clientid'];
                    $args['userid'] =$item_info['user'];
                    $args['ipids'] = array($ipid);
                    $args['ipid'] = $ipid;
                    $args['month'] = $db_print_params['month'];
                    $args['bulk_print'] ="1";
                    $args['print_controller'] = $item_info['print_controller'];
                    $args['controller'] = $item_info['print_controller'];
                    $args['inv_type'] = $item_info['invoice_type'];//nd_users
                    $args['batch_temp_folder'] = $item_info['user'].'_'.$item_info['invoice_type'].'_'.$item_info['id'];;
                    
                    $result = $PatientformnewController->{$item_info['print_function']}($args);
                    
                    $epid = Pms_CommonData::getEpid($ipid);
                    $fname = 'pdf_sh_'.$epid.'.pdf';
                    
                    break;
                
                
               case 'voluntaryworkers':
                    include 'VoluntaryworkersController.php';
                    $controller_name= new VoluntaryworkersController($this->_request, $this->_response);
                    
                    $item_id = $item_info['PrintJobsItems']['0']['item_id'];
                    $print_params = array();
                    $print_params['vws'] = array($item_id);
                    $print_params['print_job'] = '1';
                    $print_params['vw_id'] = $item_id;
                    
                    $print_params['clientid'] = $item_info['clientid'];
                    $print_params['userid'] = $item_info['user'];
                    $print_params['template_id'] = $item_info['template_id'];
                    $print_params['print_job_id'] = $item_info['id'];
                    
                    $print_params['batch_temp_folder'] = $item_info['user'].'_'.$item_info['print_function'].'_'.$item_info['id'];
                    
                    $result = $controller_name->{$item_info['print_function']}($print_params);
                    $fname = 'merged_voluntaryworker_letters_'.$item_id.'.pdf';
                    break;
                
               case 'member'://TODO-3668 Ancuta 14.12.2020 - added member
                    include 'MemberController.php';
                    $controller_name= new MemberController($this->_request, $this->_response);
                    
                    $item_id = $item_info['PrintJobsItems']['0']['item_id'];
                    $print_params = array();
                    $print_params['members_ids'] = array($item_id);
                    $print_params['print_job'] = '1';
                    $print_params['member_id'] = $item_id;
                    
                    $print_params['clientid'] = $item_info['clientid'];
                    $print_params['userid'] = $item_info['user'];
                    $print_params['template_id'] = $item_info['template_id'];
                    $print_params['print_job_id'] = $item_info['id'];
                    
                    $print_params['batch_temp_folder'] = $item_info['user'].'_'.$item_info['print_function'].'_member_'.$item_info['id'];
                    
                    $result = $controller_name->{$item_info['print_function']}($print_params);
                    $fname = 'merged_member_letters_'.$item_id.'.pdf';
                    break;
           }
            
            $pdf_path = PDFDOCX_PATH . '/' . $item_info['clientid'] . '/' . $print_params['batch_temp_folder'];

            if( ! file_exists($pdf_path.'/'.$fname)){
                $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $pdf_path.'/'.$fname.':: FILE DOES NOT EXIST - will try again');
                echo "0";
                return '0';
                exit();
            }
            
                unset($logininfo->allow_cron_in_controllers);
            if($result){
                echo "1";
                return '1';
                exit();
            } else {
                echo "0";
                return '0';
                exit();
            }
       } else{
           unset($logininfo->allow_cron_in_controllers);
                echo "0";
          return '0';
          exit();
       }
        
    }
    
     /**
     * ISPC-2609 Ancuta 28.08.2020
     */
    private function __cronjob_proccess_print_jobs($print_job_limit = 5){
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        //TODO-3668 Ancuta 14.12.2020 - added member
        $logininfo->allow_cron_in_controllers = array('cron','invoicenew','invoice','patientform','internalinvoice','voluntaryworkers','member','patientformnew');

        set_time_limit(60 * 15);
        
        $appInfo = Zend_Registry::get('appInfo');
        $app_path  = 	isset($appInfo['appCronPath']) && !empty($appInfo['appCronPath']) ? $appInfo['appCronPath'] : false;
 
        if( ! $app_path){
            $msg ="__Please check config, as the Cron path was not set___ ";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $msg);
            return;
        }
            
        
        //if an active printjob return
        $msg ="";
        $jobs_in_progress = array();
        $jobs_in_progress= Doctrine_Query::create()
        ->select('count(*) AS in_progress')
        ->from('PrintJobsBulk')
        ->whereIn("status",array('in_progress'))
        ->orderBy('create_date ASC')
        ->groupBy('status')
        ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
        
        if($jobs_in_progress['in_progress'] >= $print_job_limit){
            $msg ="__Number of jobs in progress: ".$jobs_in_progress['in_progress']." exceeds the limit of ".$print_job_limit."... Please wait! ___ ";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $msg);
            return;
        }
 
        $msg ="";
        $jobs_q= Doctrine_Query::create()
        ->select('pj.*,pji.*, ')
        ->from('PrintJobsBulk pj')
        ->leftJoin('pj.PrintJobsItems pji')
        ->whereIn("status",array('active'))
        ->orderBy('create_date ASC')
        ->limit(1)
        ->fetchArray();
        
        if(empty($jobs_q)){
            $msg ="__No print jobs for proccess ___ ";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $msg);
            return ;
        }
       
        $jobs2proccess = array();
        foreach($jobs_q as $pk=>$job){
           if($job['status'] == 'active'){
                $jobs2proccess[] = $job;
            }
        }
       
        
        if(empty($jobs2proccess)){
            $msg ="__No print jobs for proccess - no active ___ ";
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $msg);
            return ;
        }
        
        usort($jobs2proccess, array(new Pms_Sorter('create_date'), "_date_compare"));
      

        //process, only one?
        $generete_individual_pdfs = array();
        foreach($jobs2proccess as $k => $v){
            // update  job, set as  in progress
            $pjb_obj = Doctrine::getTable('PrintJobsBulk')->find($v['id']);
            if($pjb_obj){
                $pjb_obj->status = 'in_progress';
                $pjb_obj->save();
            }
           
            while(PrintJobsItemsTable::__checkjobsforitems($v['id'])) {
                
                $handles = array();
                
                $next_items = PrintJobsItemsTable::__getnextitems($v['id'],10);
                foreach($next_items as $k=>$item){
                    $function_path = $app_path.'/cron/processjobitem?item_id='.$item['id'];
                    $handles[$item['id']] = popen('curl -s '.$function_path.' 2>&1', 'r');
                }
                if(is_array($handles) && sizeof($handles) > 0) {
                    foreach($handles as $item_id=>$handle) {
                        
                        $result = fread($handle,1024);
                        fclose($handle);
                        
                        $pji_obj = Doctrine::getTable('PrintJobsItems')->find($item_id);
                        if($pji_obj){
                            if($result == 1){
                                $pji_obj->status = 'processed';
                            } else{
                                $pji_obj->status = 'error';
                                $pji_obj->tries = $pji_obj->tries+1;
                            }
                            $pji_obj->save();
                        }
                    }
                }
                
            }
            
            if($v['print_function'] == 'export_letters'){
                if($v['print_controller'] == 'member'){//TODO-3668 Ancuta 14.12.2020
                    $batch_temp_folder[$v['id']] = $v['user'].'_'.$v['print_function'].'_member_'.$v['id'];;
                } else{
                    $batch_temp_folder[$v['id']] = $v['user'].'_'.$v['print_function'].'_'.$v['id'];;
                }
            } else
            {
                $batch_temp_folder[$v['id']] = $v['user'].'_'.$v['invoice_type'].'_'.$v['id'];;
            }
            
            if( is_dir(PDFDOCX_PATH . '/' . $v['clientid'] . '/' . $batch_temp_folder[$v['id']]))
            {
                $pdf_path = PDFDOCX_PATH . '/' . $v['clientid'] . '/' . $batch_temp_folder[$v['id']];
            }
            
            if(empty($pdf_path)){
                // write log
                $message =  "Something went wrong, batch print folder missing";
                $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
                $pjb_obj->status = 'error';
                $pjb_obj->save();
                
                return;
            }
            
            $title = $batch_temp_folder[$v['id']].'_final';
            
            $file_name = $batch_temp_folder[$v['id']].'_final.pdf';
            
            $merged_other_filename = $pdf_path.'/'.$file_name;
            
            foreach($v['PrintJobsItems'] as $items=>$item_data){
                if($item_data['item_type'] == 'voluntaryWorker'){
                    $fname = 'merged_voluntaryworker_letters_'.$item_data['item_id'].'.pdf';
                }
                //TODO-3668 Ancuta 14.12.2020 - added member
                else if($item_data['item_type'] == 'member_letter'){
                    $fname = 'merged_member_letters_'.$item_data['item_id'].'.pdf';
                }
                else if($item_data['item_type'] == 'shbulkfiles'){
                    $epid = Pms_CommonData::getEpid($item_data['ipid']);
                    $fname = 'pdf_sh_'.$epid.'.pdf';
                }
                //--
                else
                {
                    $fname = 'pdf_invoice_'.$item_data['invoice_id'].'.pdf';
                }

                if(file_exists($pdf_path.'/'.$fname) )
                {
                    $generete_individual_pdfs[$v['id']][] = $pdf_path.'/'.$fname;
                } else{
                    $missing_files[$v['id']][] = $item_data['invoice_id'];
                }
            }
            
            
            if(empty($generete_individual_pdfs[$v['id']])){
                // write log
                $message =  "Something went wrong, NO FILES GENERATED";
                $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
                $pjb_obj->status = 'error';
                $pjb_obj->save();
                
                return;
            }
            
            if(!empty($missing_files[$v['id']])){
                
                // write log
                $message =  "Something went wrong, NO ALL FILES were GENERATED";
                $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
                $pjb_obj->status = 'error';
                $pjb_obj->save();
                
                return;
            }
            
            
            //merge all files existing in $batch_temp_files!
            $merge = new MultiMerge();
            $merge_process = $merge->mergePdf($generete_individual_pdfs[$v['id']], $merged_other_filename);
            
            if(file_exists($merged_other_filename))
            {
                
                $client_data = Pms_CommonData::getClientDataFp($v['clientid']);
                $file_password = $client_data[0]['fileupoadpass'];
                
                
                $tmpstmp = Pms_CommonData::uniqfolder(CLIENTUPLOADS_PATH);
                
                $new_file = CLIENTUPLOADS_PATH . '/' . $tmpstmp . '/'.$title.'.pdf' ;
                
                copy($merged_other_filename, $new_file);
                
                $ses_file_name = $tmpstmp . '/'.$title.'.pdf' ;
                
                $ftp_put_queue = Pms_CommonData :: ftp_put_queue(CLIENTUPLOADS_PATH . '/'. $ses_file_name  , 'clientuploads', $is_zipped = NULL, $foster_file = false , $ftpclientid = $v['clientid'], $ftpfilepass = $file_password);
                
                $af_cfu = new Application_Form_ClientFileUpload();
                $data2save = array(
                    'clientid' => $v['clientid'],
                    'title' => $title ,
                    'file_type' => 'pdf',
                    'file_name' => $ses_file_name,
                    'folder' => 0,
                    'tabname' => 'cron_print_job',
                    'recordid' => $v['id'],
                    'parent_id' => null,
                );
                $record = $af_cfu->InsertNewRecord($data2save);
                $saved_file_id  = $record->id;
                
                
                // update job, set as completed, and add link
                if($pjb_obj && $saved_file_id){
                    $pjb_obj->status = 'completed';
                    $pjb_obj->client_file_id = $saved_file_id;
                    $pjb_obj->save();
                    
                    foreach($generete_individual_pdfs[$v['id']] as $k=>$fname){
                       unlink($fname);
                    }
                    $msg = "_COMPLETED_";
                    $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $msg);
                    
                } else {
                    // write log
                    $message =  "Something went wrong, file not created";
                    $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
                    $pjb_obj->status = 'error';
                    $pjb_obj->save();
                }
            } 
            else
            {
                // write log
                $message =  "Something went wrong, Merged file does not exist";
                $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
                $pjb_obj->status = 'error';
                $pjb_obj->save();
                
            }
            
            
            
          /*   if($v['print_controller'] == 'invoicenew'){
          
                // first we generate PDF files 
                foreach($jobs2proccess[$k]['params']['invoices'] as $k=>$inv_id){
                    
                    $print_params = array();
                    $print_params['invoices'] = array($inv_id);
                    $print_params['batch_print'] = '1';
                    $print_params['only_pdf'] = '1';
                    $print_params['get_pdf'] = '0';
                    $print_params['print_job'] = '1';
                    
                    $print_params['clientid'] = $v['clientid'];
                    $print_params['userid'] = $v['user'];
                    $print_params['invoice_type'] = $v['invoice_type'];
                    $print_params['print_job_id'] = $v['id'];
                    
                    $print_params['batch_temp_folder'] = $v['user'].'_'.$v['invoice_type'].'_'.$v['id'];
                    $batch_temp_folder[$v['id']] = $print_params['batch_temp_folder'];
                    
                    //$generete_individual_pdfs[$v['id']][] = $invoiceController->{$v['print_function']}($print_params);
                    $generated_file = $invoiceController->{$v['print_function']}($print_params);
                    if(!empty($generated_file)){
                        $generete_individual_pdfs[$v['id']][] = $generated_file;
                    }
                }
                // We merge files
                if(!empty($generete_individual_pdfs[$v['id']])){
                    if( is_dir(PDFDOCX_PATH . '/' . $v['clientid'] . '/' . $batch_temp_folder[$v['id']]))
                    {
                        $pdf_path = PDFDOCX_PATH . '/' . $v['clientid'] . '/' . $batch_temp_folder[$v['id']];
                    }
                    $title = $batch_temp_folder[$v['id']].'_final';
                    
                    $file_name = $batch_temp_folder[$v['id']].'_final.pdf';
                    
                    $merged_other_filename = $pdf_path.'/'.$file_name;
                    
                    
                    //merge all files existing in $batch_temp_files!
                    $merge = new MultiMerge();
                    $merge_process = $merge->mergePdf($generete_individual_pdfs[$v['id']], $merged_other_filename);

                    if(file_exists($merged_other_filename))
                    {
                        
                        
                        $client_data = Pms_CommonData::getClientDataFp($v['clientid']);
                        $file_password = $client_data[0]['fileupoadpass'];
                        
                        
                        $tmpstmp = Pms_CommonData::uniqfolder(CLIENTUPLOADS_PATH);
                        
                        $new_file = CLIENTUPLOADS_PATH . '/' . $tmpstmp . '/'.$title.'.pdf' ;
                        
                        copy($merged_other_filename, $new_file);
                        
                        $ses_file_name = $tmpstmp . '/'.$title.'.pdf' ;

                        $ftp_put_queue = Pms_CommonData :: ftp_put_queue(CLIENTUPLOADS_PATH . '/'. $ses_file_name  , 'clientuploads', $is_zipped = NULL, $foster_file = false , $ftpclientid = $v['clientid'], $ftpfilepass = $file_password);
                        
                        $af_cfu = new Application_Form_ClientFileUpload();
                        $data2save = array(
                            'clientid' => $v['clientid'],
                            'title' => $title ,
                            'file_type' => 'pdf',
                            'file_name' => $ses_file_name,
                            'folder' => 0,
                            'tabname' => 'cron_print_job',
                            'recordid' => $v['id'],
                            'parent_id' => null,
                        );
                        $record = $af_cfu->InsertNewRecord($data2save);
                        $saved_file_id  = $record->id;
                        
                        
                        // update job, set as completed, and add link
                        if($pjb_obj && $saved_file_id){
                            $pjb_obj->status = 'completed';
                            $pjb_obj->client_file_id = $saved_file_id;
                            $pjb_obj->save();
                            
                            foreach($generete_individual_pdfs[$v['id']] as $k=>$fname){
                                unlink($fname);
                            }
                            $msg = "_COMPLETED_";
                            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $msg);
                            
                        } else {
                            // write log
                            $message =  "Something went wrong, file not created";
                            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
                            $pjb_obj->status = 'canceled';
                            $pjb_obj->save();
                        }
                    }
                } else{
                    
                    if($pjb_obj){
                        
                        
                        $msg = "_CANCELED - no file was returned from generation _";
                        $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $msg);
                        
                        
                        $pjb_obj->status = 'canceled';
                        $pjb_obj->save();
                    }
                    
                }
            } */
        }
        
        // unset    allow_cron_in_controllers so the controllers that are being used by cron - to show no client page, if no client is selected
        unset($logininfo->allow_cron_in_controllers);
    }
  
    /**
     * ISPC-2609 Ancuta 28.08.2020
     * not used !!!!! remove
     */
    private function upload_prinjob_file($clientid, $source_path = false, $file_name = "", $print_job_id = 0 ,  $foster_file = false)
    {
        $extension = explode(".", $file_name);
        
        $filetype = $extension[count($extension) - 1];
        $filetitle = $extension[0];
        $timestamp_filename = time() . "_file." . $extension[count($extension) - 1];
        
        $path = CLIENTUPLOADS_PATH;
        
        $dir = Pms_CommonData::uniqfolder(CLIENTUPLOADS_PATH);
        
        $folderpath = $path . '/' . $dir;
        
        $filename = $folderpath . "/" . trim($timestamp_filename);
        
        $zipname = $folderpath . ".zip";
        
        @move_uploaded_file($source_path, $filename);
        
        
        $client_data = Pms_CommonData::getClientDataFp($clientid);
        $file_password = $client_data[0]['fileupoadpass'];
        
        $cmd = "zip -9 -j -P " . $file_password . " " . $zipname. " " . $filename . "; rm -f " . $filename;
        @exec($cmd);
        
        $af_cfu = new Application_Form_ClientFileUpload();
        $data2save = array(
            'clientid' => $clientid,
            'title' => $filetitle ,
            'file_type' => 'pdf',
//             'file_name' => $file_name,
            'folder' => 0,
            'tabname' => 'cron_print_job',
            'recordid' => $print_job_id,
            'parent_id' => null,
        );
        
        $record = $af_cfu->InsertNewRecord($data2save);
        $saved_file_id  = $record->id;
        
 
        return $saved_file_id;
    }
    
    
    /**
     * ISPC-2609 Ancuta 28.08.2020
     * not used
     */
    private function system_file_upload($clientid, $source_path = false , $foster_file = false,$print_job_id = 0)
    {
        if($source_path)
        {
            if ($foster_file == true) {
                $legacy_path = strtolower(__CLASS__);
            } else {
                $legacy_path = "uploads";
            }
            
            //prepare unique upload folder
            //				$tmpstmp = $this->uniqfolder(PDF_PATH);
            $tmpstmp = Pms_CommonData::uniqfolder(PDF_PATH);
            
            //get upload folder name
            $tmpstmp_filename = basename($tmpstmp);
            
            //get original file name
            $file_name_real = basename($source_path);
            
            $source_path_info = pathinfo($source_path);
            
            //construct upload folder, file destination
            $destination_path = PDF_PATH . "/" . $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];
            $db_filename_destination = $tmpstmp . '/' . $source_path_info['filename'] . '.' . $source_path_info['extension'];
            
            //do a copy (from place where the pdf is generated to upload folder
            copy($source_path, $destination_path);
            
            
            $client_data = Pms_CommonData::getClientDataFp($clientid);
            $file_password = $client_data[0]['fileupoadpass'];
            
            $upload = Pms_CommonData :: ftp_put_queue($destination_path ,  $legacy_path, $is_zipped = NULL, $foster_file ,$clientid , $file_password );
            
            $post = array(
                'title' => $source_path_info['filename'] ,
                'clientid' => $clientid,
                //'file_name' =>
                'filetype' => $source_path_info['extension'],
                'tabname'=> 'cron_print_job',
                'recordid' => $print_job_id
            );
            
            $upload_form = new Application_Form_ClientFileUpload();
            $client_file =$upload_form->InsertData($post);
            
            $saved_file = $client_file->id;
            
            return $saved_file;
        }
    }
  
    /**
     *  //ISPC-2474 Ancuta 23.10.2020
     */
    public function rungatherpatient4deletionAction()
    {
        try {
            set_time_limit(60 * 6);
            
            $this->_fetch_patients_for_deletion();
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
    /**
     *  //ISPC-2474 Ancuta 23.10.2020
     */
    private function _fetch_patients_for_deletion()
    {
        try {
            set_time_limit(60 * 6);
            
            $start = microtime(true);
            
            $clientids = Client::get_all_clients_ids();
            
            $clients_with_module_243 = Modules::clients2modules(243, $clientids);
            
            // get all discharged patients for clients with module,  where the discharge date is older than 10 years
            // for all this patients - go throu every model, and check the lates change date or create date 
            // populate a new table 
            
            $pm =  new Patient4Deletion();
            $pm->set_patients4deletion($clients_with_module_243,10);
            
            $end = microtime(true) - $start;
            
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($end, 2) . " Seconds ) : ";
            
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
    /**
     *  //ISPC-2474 Ancuta 23.10.2020
     */
    public function processpatientdeletionAction()
    {
        try {
            set_time_limit(60 * 6);
            
            $this->_execute_patient_deletion();
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
    
    /**
     *  //ISPC-2474 Ancuta 23.10.2020
     */
    private function _execute_patient_deletion(){
        
        try {
            
            set_time_limit(60 * 6);
            
            $start = microtime(true);
            
            $clientids = Client::get_all_clients_ids();
            
            $clients_with_module_243 = Modules::clients2modules(243, $clientids);
            
            $pm =  new Patient4Deletion();
            foreach($clients_with_module_243 as $clientid){
                $pm->process_patientdeletion($clientid);
            }
            
            $end = microtime(true) - $start;
            
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($end, 2) . " Seconds ) : ";
            
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
  
    /**
     * ISPC-2797 Ancuta 18.02.2021
     */
    public function processmedicationplansAction()
    {
        try {
            set_time_limit(60 * 6);
            
            $this->_process_planned_medication();
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    /**
     * ISPC-2797 Ancuta 18.02.2021
     */
    private function _process_planned_medication(){
        
        try {
            
            set_time_limit(60 * 6);
            
            $start = microtime(true);
            
            $clientids = Client::get_all_clients_ids();
            
            $clients_with_module_250 = Modules::clients2modules(250, $clientids);

            $pm =  new PatientDrugplanPlanning();
            foreach($clients_with_module_250 as $clientid){
                $pm->proccess_planned_medications($clientid);
            }
            
            $end = microtime(true) - $start;
            
            $message = "[ " . __FUNCTION__ . " ] - took (" . round($end, 2) . " Seconds ) : ";
            
            $this->log_info( __METHOD__. ':' . __LINE__ .' ' . $message);
            
            
        } catch (Exception $e) {
            
            $this->log_error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }
    
}
 

?>