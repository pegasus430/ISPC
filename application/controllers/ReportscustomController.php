<?php
class ReportscustomController extends Zend_Controller_Action
{
// Maria:: Migration ISPC to CISPC 08.08.2020
    public function init()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $this->clientid = $logininfo->clientid;
        $this->userid = $logininfo->userid;
    }

    public function listAction()
    {
        set_time_limit(0);
        $clientid = $this->clientid;
    }
	     

    public function getlistAction()
    {
        $clientid = $this->clientid;
        $this->_helper->viewRenderer->setNoRender();
         
        $limit = $_REQUEST['length'];
        $offset = $_REQUEST['start'];
        $search_value = $_REQUEST['search']['value'];
         
        if(!empty($_REQUEST['order'][0]['column'])){
            $order_column = $_REQUEST['order'][0]['column'];
        } else{
            $order_column = "1";
        }
        $order_dir = $_REQUEST['order'][0]['dir'];
         
        $columns_array = array(
            "1" => "name",
            "2" => "description",
            "3" => "create_date"
        );
         
        $order_by_str = $columns_array[$order_column].' '.$order_dir.' ';
         
        if ($clientid > 0)
        {
            $where = ' and clientid=' . $clientid;
        }
        else
        {
            $where = ' and clientid=0';
        }
         
        // ########################################
        // #####  Query for count ###############
        $fdoc1 = Doctrine_Query::create();
        $fdoc1->select('count(*)');
        $fdoc1->from('Reportscustom');
        $fdoc1->where("isdelete = 0  " . $where." OR system =1");
        $fdoc1->andWhere("issaved = 1  ");
        /* ------------- Search options ------------------------- */
        if (isset($search_value) && strlen($search_value) > 0)
        {
            $fdoc1->andWhere("name like '%" . trim($search_value) . "%'  or description like '%" . trim($search_value) . "%'   ");
        }
        $fdoc1->orderBy($order_by_str);
        $fdocexec = $fdoc1->execute();
        $fdocarray = $fdocexec->toArray();
         
        $full_count  = $fdocarray[0]['count'];
         
        // ########################################
        // #####  Query for details ###############
        $vw_sql = '*,';
        $fdoc1->select($vw_sql);
        $fdoc1->where("isdelete = 0  " . $where." OR system =1");
        $fdoc1->andWhere("issaved = 1  ");
        /* ------------- Search options ------------------------- */
        if (isset($search_value) && strlen($search_value) > 0)
        {
            $fdoc1->andWhere("name like '%" . trim($search_value) . "%'  or description like '%" . trim($search_value) . "%'   ");
        }
        if($limit != "-1"){ // -1 = list all
            $fdoc1->limit($limit);
            $fdoc1->offset($offset);
        }
        $fdoclimitexec = $fdoc1->execute();
        $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
         
        $report_ids[] = '99999999999999';
        foreach ($fdoclimit as $key => $report)
        {
            $fdoclimit_arr[$report['id']] = $report;
            $report_ids[] = $report['id'];
        }
        
        $all_users = Pms_CommonData::get_client_users($clientid, true);
        
        foreach($all_users as $keyu => $user)
        {
            $all_users_array[$user['id']] = $user['user_title'] ." ". $user['last_name'] . ", " . $user['first_name'];
        }
        
        
        $row_id = 0;
        $link = "";
        $resulted_data = array();
        foreach($fdoclimit_arr as $report_id =>$report_data){
            $link = '%s ';
             
            $resulted_data[$row_id]['name'] = sprintf($link,$report_data['name']);
            $resulted_data[$row_id]['description'] = sprintf($link,$report_data['description']);
            $resulted_data[$row_id]['create_date'] = sprintf($link,date('d.m.Y H:i',strtotime($report_data['create_date'])));
            $resulted_data[$row_id]['created_by'] = sprintf($link,$all_users_array[$report_data['create_user']]);

            $resulted_data[$row_id]['actions'] = "";
            $resulted_data[$row_id]['actions'] .= '<a href="javascript:void(0);" class="ui-state-default ui-corner-all generate" data-id="'.$report_data['id'].'" > '.$this->view->translate("generate").' </a>';
            $resulted_data[$row_id]['actions'] .= '<br /><a href="javascript:void(0);" class="ui-state-default ui-corner-all edit" data-id="'.$report_data['id'].'" data-saved="'.$report_data['issaved'].'" > '.$this->view->translate("edit").' </a>';
            $resulted_data[$row_id]['actions'] .= '<br /><a href="javascript:void(0);" class="ui-state-default ui-corner-all duplicate" data-id="'.$report_data['id'].'" > '.$this->view->translate("duplicate_report").' </a>';
            $resulted_data[$row_id]['actions'] .= '<br /><a href="javascript:void(0);" class="ui-state-default ui-corner-all delete" rel="'.$report_data['id'].'" id="delete_'.$report_data['id'].'">'.$this->view->translate("delete").'</a>';
             
            $row_id++;
        }
         
        $response['draw'] = $_REQUEST['draw']; //? get the sent draw from data table
        $response['recordsTotal'] = $full_count;
        $response['recordsFiltered'] = $full_count; // ??
        $response['data'] = $resulted_data;
         
        echo json_encode($response);
        exit;
    }
	    
    public function step1Action()
    {
        set_time_limit(0);
        $this->_helper->layout->setLayout('layout_ajax');
        $clientid = $this->clientid;
        
        // step 1
        $search_array = ReportscustomSearch::get_search_criterias();
        $Tr = new Zend_View_Helper_Translate();
        
        foreach($search_array as $k=>$s_data){
            $translated_data[strtolower($Tr->translate($s_data['search']))] = $s_data; 
        }
        ksort($translated_data);
        
        foreach($translated_data as $translate_key=>$svalue){
            $search[$svalue['id']]=$svalue;
        }
        
        $this->view->search_criterias =$search;
        
        foreach($search as $k=>$sd)
        {
            if(!in_array($sd['type'],$stypes))
            {
                $stypes[] = $sd['type'];
            }
        } 
        $this->view->search_criterias_types = $stypes;
        
        $sapv_statuses = SapvVerordnung::getSapvRadios();
        $this->view->sapv_statuses = $sapv_statuses;
        
        $sapv_verordnets = Pms_CommonData::getSapvCheckBox();
        $this->view->sapv_types = $sapv_verordnets;
        
        $dis = new DischargeMethod();
        $discharge_methods = $dis->getDischargeMethod($clientid, 1);
        foreach($discharge_methods as $t=>$data){
            if(strlen($t) =="0" ){ // remove empty option
                unset($discharge_methods[$t]);
            }
        }
        $this->view->discharge_methods = $discharge_methods;
        
        
        /* ################ GET SAVED REPORT DATA ################################# */
        if($_REQUEST['report_id'] && strlen($_REQUEST['report_id']) > 0 ){
            $report_id = $_REQUEST['report_id'];
            
            // REPORT DETAILS
            $report['details'] = Reportscustom::get_report($report_id);
            
            // REPORT - PERIOD
            $report_period = ReportscustomPeriods::get_report_period($report_id);
            if($report_period){
                $report['period'] =$report_period[$report_id]; 
            }

            // REPORT - GROUPS
            $report_groups = ReportscustomGroups2Report::get_report_groups($report_id);
            //ispc 1953
            $report_groups_ispc_1953 = $report_groups;
            
            if($report_groups){
                $report_groups_ids = $report_groups [$report_id];
                $groups_data  = ReportscustomGroups ::get_groups_details($report_groups_ids);
                $search_data = ReportscustomSearch2Group::get_groups_search_details($report_groups_ids);
                
                foreach( $report_groups_ids as $group_id){
                    $report['groups'][$group_id]['name'] = $groups_data[$group_id]['group_name'];
                    $report['groups'][$group_id]['group_id'] = $group_id;
                    $report['groups'][$group_id]['search2group'] = $search_data[$group_id];
                    foreach($search_data[$group_id] as $search_id => $search_details){
                        $report['groups'][$group_id]['search_ids2group'][] = $search_id;
                        if($search_details['negation'] == "1"){
                            $report['groups'][$group_id]['negation_search_ids2group'][] = $search_id;
                        }
                        
                    }
                }

            }
            $this->view->saved_data= $report;
        }
        

        /* ########################## SAVE ################################# */
        $response['error'] = array();
        
        if ($this->getRequest()->isPost()) {
            $post = $_POST;
        
            // ##################
            // VALIDATION - START
            // ##################
            
            if (!empty($post['report']['period']) && $post['issaved'] != 1) {
                if($post['report']['period']['type'] == "1" && sizeof($post['report']['period']['year'])==0  ){
                    $response['error'][]  = 1;
                    $response['error_text']['year_filter']  = $this->view->translate("Please select year!");
                }
                
                if($post['report']['period']['type'] == "2") {
                    
                    if( empty($post['report']['period']['start_date'])  ){
                        $response['error'][]  = 1;
                        $response['error_text']['start_date']  = $this->view->translate("Please select start date!");
                    }
                    
                    if( !empty($post['report']['period']['start_date']) && strtotime(date('d.m.Y',strtotime($post['report']['period']['start_date']))) > strtotime(date('d.m.Y')) ){
                        $response['error'][]  = 1;
                        $response['error_text']['start_date']  = $this->view->translate("The start date in future error");
                    }
                }
            }
            
            if (!empty($post['report']['groups'])) {
                foreach ($post['report']['groups'] as $group_id => $gr_data) {

                    if(strlen($gr_data['name']) == 0){
                        $response['error'][] = 1;
                        $response['error_text']['gr_'.$group_id]  = $this->view->translate("Group name is mandatory!");                        
                    }
                    
                    foreach ($gr_data['search2group'] as $search_id => $search_details) {
                        if (isset($search_details['value']) && !empty($search_details['value']) ) {
                            $search_criterias[$group_id][] = $search_details['value'];
                        }
                    }
                    
                    if(count($search_criterias[$group_id]) == 0){
                        $response['error'][]  = 1;
                        $response['error_text']['scg_'.$group_id]  = $this->view->translate("Please select search criterias!");
                    }
                    
                }
            }
            
            // ##################
            // VALIDATION - END
            // ##################            

            if(count($response['error']) > 0) {
                echo json_encode($response);
                exit;
            }  else  {
                
                
                // FIRST SAVE
                if (empty($_REQUEST['report_id']) 
                		&& (empty($_POST['report_id']) || strlen(($_POST['report_id'])) == 0 )) 
                {
            
                    // SAVE REPORT - DETAILS
                    $reportq = new Reportscustom();
                    $reportq->clientid = $clientid;
                    $reportq->name = "Temp";
                    $reportq->description = $post['description'];
                    $reportq->group_operator= $post['report']['details']['group_operator'];;
                    $reportq->system = "0";
                    $reportq->save();
            
                    if ($reportq) {
                        $report_id = $reportq->id;
                        $response['report_id'] = $report_id;
            
                        // SAVE REPORT - PERIOD
                        if (!empty($post['report']['period']) && $post['issaved'] != 1) {// save only if report issaved != 1
                            $reportq_period = new ReportscustomPeriods();
                            $reportq_period->report_id = $report_id;
                            $reportq_period->clientid = $clientid;
                            $reportq_period->type = $post['report']['period']['type'];
                            if ( $post['report']['period']['type'] == "1") {
            
                                if (!empty($post['report']['period']['month'])) {
                                    $reportq_period->months = serialize($post['report']['period']['month']);
                                }
                                if (!empty($post['report']['period']['quarter'])) {
                                    $reportq_period->quarters = serialize($post['report']['period']['quarter']);
                                }
            
                                if (!empty($post['report']['period']['year'])) {
                                    $reportq_period->years = serialize($post['report']['period']['year']);
                                }
                            } elseif ( $post['report']['period']['type'] == "2") {
                                if (!empty($post['report']['period']['start_date'])) {
                                    $reportq_period->start_date = date('Y-m-d H:i:s', strtotime($post['report']['period']['start_date']));
                                }
                                if (!empty($post['report']['period']['end_date'])) {
                                    $reportq_period->end_date = date('Y-m-d H:i:s', strtotime($post['report']['period']['end_date']));
                                }
                            }
                            
                            if($reportq_period->trySave()){
                                $response['error'] = 0;
                            } else{
                                $response['error']  = 1;
                            }
                            
                        }
            
                        // SAVE REPORT - GROUPS
                        if (!empty($post['report']['groups'])) {
                            foreach ($post['report']['groups'] as $group_id => $gr_data) {
                                // SAVE GROUP        
                                $reportq_groups = new ReportscustomGroups();
                                $reportq_groups->clientid = $clientid;
                                $reportq_groups->group_name = $gr_data['name'];
                                $reportq_groups->save();
            
            
                                // GET INSERTED GROUP ID
                                $new_group_id = $reportq_groups->id;
                                
                                
                                // ASSOCIATE GROUP TO REPORT
                                if ($new_group_id) {
                                    $rg2r = new ReportscustomGroups2Report();
                                    $rg2r->report_id = $report_id;
                                    $rg2r->group_id = $new_group_id;
                                    $rg2r->save();
                                }
            
                                
                                // ASSOCIATE SEARCH CRITERIAS TO GROUP
                                if ($new_group_id && ! empty($gr_data['search2group'])) {
                                    foreach ($gr_data['search2group'] as $search_id => $search_details) {
            
                                        if ($search_details['negation']) {
                                            $search_details['negation'] = "1";
                                        } else {
                                            $search_details['negation'] = "0";
                                        }
            
                                        if (strlen($search_details['value']) > 0) {
                                            $search2groups_arr[] = array(
                                                "group_id" => $new_group_id,
                                                "search_id" => $search_id,
                                                "negation" => $search_details['negation'],
                                                "options" => $search_details['options']
                                            );
                                        }
                                    }
            
                                    if (! empty($search2groups_arr)) {
                                        $collection = new Doctrine_Collection('ReportscustomSearch2Group');
                                        $collection->fromArray($search2groups_arr);
                                        $collection->save();
                                    }
                                }
                            }
                        }
                    }
                }
                else
                {
                    //UPDATE REPORT
                    $report_id = $_POST['report_id'];
                    $response['report_id'] = $report_id;
    
                    $rcustom = Doctrine::getTable('Reportscustom')->find($report_id);
                    if($rcustom && $rcustom->group_operator != $post['report']['details']['group_operator']){
	                    $rcustom->group_operator = $post['report']['details']['group_operator'];
	                    $rcustom->save();
                    }
                    
                    //ispc_1953 - delete groups from this report
                    if( ! empty($report_groups_ispc_1953 [$report_id])) {
                    	
                    	$report_groups_ispc_1953_ids = $report_groups_ispc_1953 [$report_id];
                    
	                    $this_raport_groups = array();
	                    if (!empty($post['report']['groups'])) {
	                    	$this_raport_groups = array_column($post['report']['groups'], 'group_id');
	                    }
	                    $deleted_group_ids = array_diff($report_groups_ispc_1953_ids , $this_raport_groups);
	                   	//set this group_isd as deleted
	                    foreach ($deleted_group_ids as $group_id) {
	                    	
		                   	$rcustom_groups2Report = Doctrine::getTable('ReportscustomGroups2Report')->findOneByReportIdAndGroupIdAndIsdelete($report_id , $group_id , 0);
		                   	if ($rcustom_groups2Report) {
		                  		$rcustom_groups2Report->isdelete = 1;
		                   		$rcustom_groups2Report->save();
		                   	}
	                    }
	                    
                    
                    }
                    
                    // CHECK GROUPS
                    if (!empty($post['report']['groups'])) {
                        foreach ($post['report']['groups'] as $group_id => $gr_data) {
            
                            // CHECK IF GROUP IS NEW
                            if($gr_data['new'] == "1"){
                                // INSERT NEW GROUP
                                $reportq_groups = new ReportscustomGroups();
                                $reportq_groups->clientid = $clientid;
                                $reportq_groups->group_name = $gr_data['name'];
                                $reportq_groups->save();
            
                                // GET INSERTED GROUP ID
                                $new_group_id = $reportq_groups->id;
                                
                                // ASSOCIATE NEW GROUP TO REPORT
                                if ($new_group_id) {
                                    $rg2r = new ReportscustomGroups2Report();
                                    $rg2r->report_id = $report_id;
                                    $rg2r->group_id = $new_group_id;
                                    $rg2r->save();
            
                                }
            
                                
                                // ASSOCIATE SEARCH CRITERIAS TO GROUP
                                if ($new_group_id && ! empty($gr_data['search2group'])) {
                                    foreach ($gr_data['search2group'] as $search_id => $search_details) {
            
                                        if ($search_details['negation']) {
                                            $search_details['negation'] = "1";
                                        } else {
                                            $search_details['negation'] = "0";
                                        }
            
                                        if (strlen($search_details['value']) > 0) {
                                            $search2groups_new_arr[] = array(
                                                "group_id" => $new_group_id,
                                                "search_id" => $search_id,
                                                "negation" => $search_details['negation'],
                                                "options" => $search_details['options']
                                            );
                                        }
                                    }
            
                                    if (! empty($search2groups_new_arr)) {
                                        $collection = new Doctrine_Collection('ReportscustomSearch2Group');
                                        $collection->fromArray($search2groups_new_arr);
                                        $collection->save();
                                    }
                                }
                            }
                            else //EDIT EXISTING GROUPS
                            {
                                //UPDATE GROUP DETAILS
                                $reportq_groups = Doctrine::getTable('ReportscustomGroups')->find($group_id);
                                $reportq_groups->group_name = $gr_data['name'];
                                $reportq_groups->save();
            
                                if (!empty($gr_data['search2group'])) {
                                    
                                    // CLEAR EXISTING SEARCH CRITERIAS FROM GROUP
                                    $this->clear_group_search_criterias($group_id);
                                    
                                    // ASSOCIATE SEARCH CRITERIAS TO GROUP
                                    foreach ($gr_data['search2group'] as $search_id => $search_details) {
                                        if ($search_details['negation']) {
                                            $search_details['negation'] = "1";
                                        } else {
                                            $search_details['negation'] = "0";
                                        }
            
                                        if (strlen($search_details['value']) > 0) {
                                            $search2groups_ext_arr[] = array(
                                                "group_id" => $group_id,
                                                "search_id" => $search_id,
                                                "negation" => $search_details['negation'],
                                                "options" => $search_details['options']
                                            );
                                        }
                                    }
                                    if (! empty($search2groups_ext_arr)) {
                                        $collection = new Doctrine_Collection('ReportscustomSearch2Group');
                                        $collection->fromArray($search2groups_ext_arr);
                                        $collection->save();
                                    }
                                }
            
            
            
                            }
                        }
                    }
                }
            }
        echo json_encode($response);
        exit;
        }
    }
	    
	    
    public function step2Action()
    {
        set_time_limit(0);
        $clientid = $this->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        
        $report_search_type = "";
        if($_REQUEST['report_id'] && strlen($_REQUEST['report_id']) > 0 ){
            
            $report_id = $_REQUEST['report_id'];
            // REPORT - GROUPS
            $report_groups = ReportscustomGroups2Report::get_report_groups($report_id);
            
            if($report_groups)
            {
                $report_groups_ids = $report_groups [$report_id];
                $groups_data  = ReportscustomGroups ::get_groups_details($report_groups_ids);
                $search_data = ReportscustomSearch2Group::get_groups_search_details($report_groups_ids);
            
                foreach( $report_groups_ids as $group_id)
                {
                    foreach($search_data[$group_id] as $search_id => $search_details)
                    {
                        $report_search_type = $search_details['system']['type'];
                    }
                }
            }
            
            // get saved data
            $columns_data_arr = ReportscustomColumns2Report::get_report_columns($_REQUEST['report_id']);
            $columns_data = $columns_data_arr[$_REQUEST['report_id']];
            
            foreach($columns_data  as $ck =>$cv){
                $report['columns']['columns_ids'][] = $cv['column_id'];
                $report['columns'][$cv['report_id']][$cv['column_id']] = $cv['id'];
            }
            $this->view->saved_columns = $report['columns'];

            $this->view->show_combinable = 1;
        }
        
        
        
        $columns_array = ReportscustomColumns::get_columns($report_search_type);
        
        $average_cols = array();
        $median_cols = array();
        
        foreach($columns_array as $cxk => $v_col ){
            if($v_col['allow_average'] == "1"){
                $average_cols[] = $v_col['id'];
            }
            if($v_col['allow_median'] == "1"){
                $median_cols[] = $v_col['id'];
            }
        }
        
        
        
         
        $Tr = new Zend_View_Helper_Translate();
        
        foreach($columns_array as $k=>$s_data){
            $translated_data[strtolower($Tr->translate($s_data['column_name']))] = $s_data;
            $columns_details[$s_data['id']] = $s_data;
        }

        ksort($translated_data);
        $columns_array_final = array_values($translated_data) ;
        
        $this->view->columns = $columns_array_final;
        
        
        $response['error']  = 0;
        $response['skipstep3'] = "0";
        
        if ($this->getRequest()->isPost()) {

            $post = $_POST;

            $response['report_id']  = $post['report_id'];

            // ##################
            // VALIDATION - START
            // ##################
            $selected = 0;
            foreach ($post['report']['columns'] as $column_id => $column_details) {
                if(!empty($column_details['column_id'])) {
                    $selected += 1;
                }
            }
            if($selected == 0){
                $response['error'] = 1;
                $response['error_text'] = $this->view->translate("Please select columns!");
            }
            // ##################
            // VALIDATION - END
            // ##################
            
            
            if($response['error'] == "1"){
                echo json_encode($response);
                exit;
            } 
            else
            {
                if (! empty($post['report']['columns'])) {
                    foreach ($post['report']['columns'] as $column_id => $column_details) {
                        
                        if (isset($column_details['id']) && ! empty($column_details['id'])) {
                            $reportq_c2r = Doctrine::getTable('ReportscustomColumns2Report')->find($column_details['id']);
                        } else {
                            $reportq_c2r = new ReportscustomColumns2Report();
                        }
                        $reportq_c2r->report_id = $post['report_id'];
                        
                        if (isset($column_details['column_id']) && ! empty($column_details['column_id'])) {
                            $reportq_c2r->column_id = $column_details['column_id'];
                            $reportq_c2r->isdelete = "0";
                        } else {
                            $reportq_c2r->isdelete = "1";
                        }
                        
                        if ( (isset($column_details['id']) && ! empty($column_details['id'])) || (isset($column_details['column_id']) && ! empty($column_details['column_id'])) ) {
                            if($reportq_c2r->trySave()){
                               $response['error'] = 0;
                            } else{
                               $response['error']  = 1;
                            }
                        }
                    }
              
                    $columns_data_arr = ReportscustomColumns2Report::get_report_columns($_REQUEST['report_id']);
                    $columns_data = $columns_data_arr[$_REQUEST['report_id']];
                    
                    $response['step3_columns_ids'] = array();
                    foreach($columns_data  as $ck =>$cv){
                        if($columns_details[$cv['column_id']]['type'] != "o"){
                            
                            if(in_array($cv['column_id'],$average_cols) ||  in_array($cv['column_id'],$median_cols) )
                            {
                                $response['step3_columns_ids'][] = $cv['column_id'];
                            }
                            
                            $available_cols['step3_columns_ids_sort'][] = $cv['column_id']; 
                        }
                    }
                    
                    if(empty($response['step3_columns_ids'])  && empty($available_cols['step3_columns_ids_sort']) ){
                        $response['skipstep3'] = "1";         
                                       
                    } else{
                        if(count($available_cols['step3_columns_ids_sort']) > 1) {
                            $response['skipstep3'] = "0";                        
                        } else{
                            $response['skipstep3'] = "1";                        
                        }
                    }
                }
                
                echo json_encode($response);
                exit;
            }
        }
        
    }
	    
    public function step3Action()
    {
        set_time_limit(0);
        $clientid = $this->clientid;
        $this->_helper->layout->setLayout('layout_ajax');

        $columns_array = ReportscustomColumns::get_columns();

        $allow_ordering_columns = true;
        $this->view->allow_ordering_columns = $allow_ordering_columns;
        
        $average_cols = array();
        $median_cols = array();
        $allowed_cols = array();
        
        foreach($columns_array as $cxk => $v_col ){
            
            if($v_col['allow_average'] == "1"){
                $average_cols[] = $v_col['id'];
            }
            if($v_col['allow_median'] == "1"){
                $median_cols[] = $v_col['id'];
            }
            
            if($v_col['allow_average'] == "1" || $v_col['allow_median'] == "1" ){
                $allowed_cols[] = $v_col['id'];
            }
            
            $columns_details[$v_col['id']] = $v_col;
        }
        
        $this->view->allowed_cols= $allowed_cols;
        $this->view->columns = $columns_array;
        
        
        if($_REQUEST['report_id'] && strlen($_REQUEST['report_id']) > 0 ){
            // get saved data
            
            $columns_data_arr = ReportscustomColumns2Report::get_report_columns($_REQUEST['report_id'],$allow_ordering_columns);
            $columns_data = $columns_data_arr[$_REQUEST['report_id']];
            
            $mora_columns = array();
            foreach($columns_data  as $ck =>$cv){
                if($columns_details[$cv['column_id']]['type'] != "o")
                {
                    $report['columns']['columns_ids'][] = $cv['column_id'];
                    $report['columns'][$cv['report_id']][$cv['column_id']]['line_id'] = $cv['id'];
                    $report['columns'][$cv['report_id']][$cv['column_id']]['median'] = $cv['show_median'];
                    $report['columns'][$cv['report_id']][$cv['column_id']]['average'] = $cv['show_average'];
                    $report['columns'][$cv['report_id']][$cv['column_id']]['column_name'] = $columns_details[$cv['column_id']]['column_name'];
                    $report['columns'][$cv['report_id']][$cv['column_id']]['order_number'] = $cv['order_number'];
    
                    if($columns_details[$cv['column_id']]['allow_average'] == "1" || $columns_details[$cv['column_id']]['allow_median'] == "1" ){
                        $mora_columns[] = $cv['id'];
                    }
                }
            }

            $this->view->report_ma_columns = $mora_columns;
            $this->view->saved_columns = $report['columns'];
        }
        
        // POST
        $response['error']  = 0;
        
        if ($this->getRequest()->isPost()) {
            
            $post = $_POST;
            $response['report_id']  = $post['report_id'];
            
            
            if (! empty($post['report']['columns'])) {
                foreach ($post['report']['columns'] as $column_id => $column_details) {
        
                    if (isset($column_details['line_id']) && !empty($column_details['line_id'])) {
                        $reportq_c2r_ma = Doctrine::getTable('ReportscustomColumns2Report')->find($column_details['line_id']);
                    } else {
                        $reportq_c2r_ma = new ReportscustomColumns2Report();
                    }
                    $reportq_c2r_ma->report_id = $post['report_id'];
                    $reportq_c2r_ma->column_id = $column_id;
                    
                    $reportq_c2r_ma->order_number = $column_details['order_number'];
                    
                    if (isset($column_details['median']) && !empty($column_details['median'])) {
                        $reportq_c2r_ma->show_median = "1";
                    } else {
                        $reportq_c2r_ma->show_median = "0";
                    }
                    
                    if (isset($column_details['average']) && !empty($column_details['average'])) {
                        $reportq_c2r_ma->show_average = "1";
                    } else {
                        $reportq_c2r_ma->show_average = "0";
                    }
                    
                    if($reportq_c2r_ma->trySave()){
                        $response['error'] = 0;
                    } else{
                        $response['error']  = 1;
                    }
                }
            }
        
            echo json_encode($response);
            exit;
        }
        
    }
	    
    public function step4Action()
    {
        set_time_limit(0);
        $clientid = $this->clientid;
        $this->_helper->layout->setLayout('layout_ajax');
        
        if($_REQUEST['report_id'] && strlen($_REQUEST['report_id']) > 0 ){
            // get saved data
            $report_details = Reportscustom::get_report($_REQUEST['report_id']);
            if($report_details['issaved'] == "0"){
                $report['name'] = "";
            } else{
                $report['name'] = $report_details['name'];
            }
            $report['description'] = $report_details['description'];
            $report['issaved'] = $report_details['issaved'];
            $this->view->report = $report;
            
            
            //get report coulmns
            $Tr = new Zend_View_Helper_Translate();
            $report_columns_array = ReportscustomColumns2Report::get_report_columns_details($_REQUEST['report_id']);
            foreach($report_columns_array[$_REQUEST['report_id']] as $cr=>$cr_det ){
                if($cr_det['ReportscustomColumns']['type'] != "c"){
                    $not_sortable[] = $cr_det['ReportscustomColumns']['column_name']; 
                }
                
                if($cr_det['ReportscustomColumns']['sortable'] == "1"){
                    $sortable_columns[$cr_det['column_id']] = $cr_det['ReportscustomColumns']['column_name'];

                    $translated_data[strtolower($Tr->translate($cr_det['ReportscustomColumns']['column_name']))]['column_id'] = $cr_det['column_id'];
                    $translated_data[strtolower($Tr->translate($cr_det['ReportscustomColumns']['column_name']))]['column_name'] = $cr_det['ReportscustomColumns']['column_name'];;
                    
                }    
            }
            ksort($translated_data);
            foreach($translated_data as $cname=>$cdata){
                $sortable_columns[$cdata['column_id']] = $Tr->translate($cdata['column_name']);
            }
            $this->view->sortable_columns = $sortable_columns;
            
        }
        
        
        // POST
        $response['error']  = 0;
        $response['error_name']  = 0;
        
        if ($this->getRequest()->isPost()) {
            $post = $_POST;
            $response['export_type']= $post['generate'];
            
            if(!empty($post['sortby'])){
                $response['sortby'] = $post['sortby'];
            } else{
                $response['sortby']= "";
            }
            
            
            if (!empty($post['report_id'])) {
                $response['report_id']  = $post['report_id'];

                if($post['issaved'] == 1 && (empty($post['name']) || strlen($post['name']) == "0")){
                    $response['error_name'] = 1;
                } else {
                    $reportq_details = Doctrine::getTable('Reportscustom')->find($post['report_id']);
                    $reportq_details->name = $post['name'];
                    $reportq_details->description = $post['description'];
                    $reportq_details->issaved = $post['issaved'];
                    if($reportq_details->trySave()){
                        $response['error'] = 0;
                    } else{
                        $response['error']  = 1;
                    }
                }
            }
            
            echo json_encode($response);
            exit;
         
        }
    }
    
    
    
    private function clear_group_search_criterias($group){
        $user = $this->userid;
        $current_time = date("Y-m-d H:i:s"); 
        
        $cl_group = Doctrine_Query::create()
            ->update("ReportscustomSearch2Group")
            ->set('isdelete', "1")
            ->set('change_date', '"'.$current_time .'"')
            ->set('change_user', $user )
            ->where("group_id='" . $group . "'");
        $cl_group->execute();
    }
    
    
    
    public function deleteAction ()
    {
        $this->_helper->viewRenderer('list');
        $has_edit_permissions = Links::checkLinkActionsPermission();
        
        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
        {
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }
        
        if($_GET['id']){
            $thrash = Doctrine::getTable('Reportscustom')->find($_GET['id']);
            $thrash->isdelete = 1;
            $thrash->save();
            
            $this->_redirect(APP_BASE . "reportscustom/list");
            exit;
        }    
    }
    
    
    public function duplicateAction ()
    {
        $clientid = $this->clientid;
        
        $this->_helper->viewRenderer('list');
        $has_edit_permissions = Links::checkLinkActionsPermission();
        
        if(!$has_edit_permissions) // if canedit = 0 - don't allow any additions or changes
        {
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }
        
        if($_GET['id']){
            $details = Doctrine::getTable('Reportscustom')->find($_GET['id']);
            if($details){
                
                $reportq = new Reportscustom();
                $reportq->clientid = $clientid;
                $reportq->name = $details['name'].' (Copy)';
                $reportq->description = $details['description'];
                $reportq->issaved = $details['issaved'];
                $reportq->system = "0";
                $reportq->save();
                
                if($reportq->trySave()){
                    $response['error'] = 0;
                } else{
                    $response['error']  = 1;
                }
                
                if ($reportq) {
                
                    $report_id = $reportq->id;
                    $response['report_id'] = $report_id;
                
                    // REPORT - PERIOD
                    $report_period = ReportscustomPeriods::get_report_period($_GET['id']);
                    if($report_period){
                        $report['period'] =$report_period[$_GET['id']];
                    }
                    
                    
                    // SAVE REPORT - PERIOD
                    if($report_period[$_GET['id']]){
                        
                        $reportq_period = new ReportscustomPeriods();
                        $reportq_period->report_id = $report_id;
                        $reportq_period->clientid = $clientid;
                        $reportq_period->type = $report['period']['type'];
                        
                        if ($report['period']['type'] == "1") {
                        
                            if (!empty($report['period']['months'])) {
                                $reportq_period->months = serialize($report['period']['months']);
                            }
                            if (!empty($report['period']['quarters'])) {
                                $reportq_period->quarters = serialize($report['period']['quarters']);
                            }
                        
                            if (!empty($report['period']['years'])) {
                                $reportq_period->years = serialize($report['period']['years']);
                            }
                        }  elseif ( $report['period']['type'] == "2") {
                            if (!empty($report['period']['start_date'])) {
                                $reportq_period->start_date = date('Y-m-d H:i:s', strtotime($report['period']['start_date']));
                            }
                            if (!empty( $report['period']['end_date'])) {
                                $reportq_period->end_date = date('Y-m-d H:i:s', strtotime($report['period']['end_date']));
                            }
                        }
                        if($reportq_period->trySave()){
                            $response['error'] = 0;
                        } else{
                            $response['error']  = 1;
                        }
                    }
                    
                    
                    // REPORT - GET GROUPS
                    $report_groups = ReportscustomGroups2Report::get_report_groups($_GET['id']);
                    
                    if($report_groups){
                        $report_groups_ids = $report_groups [$_GET['id']];
                        $groups_data  = ReportscustomGroups ::get_groups_details($report_groups_ids);
                        $search_data = ReportscustomSearch2Group::get_groups_search_details($report_groups_ids);
                        foreach( $report_groups_ids as $group_id){
                            $report['groups'][$group_id]['name'] = $groups_data[$group_id]['group_name'];
                            $report['groups'][$group_id]['group_id'] = $group_id;
                            $report['groups'][$group_id]['search2group'] = $search_data[$group_id];
                            foreach($search_data[$group_id] as $search_id => $search_details){
                                $report['groups'][$group_id]['search_ids2group'][] = $search_id;
                                if($search_details['negation'] == "1"){
                                    $report['groups'][$group_id]['negation_search_ids2group'][] = $search_id;
                                }
                    
                            }
                        }
                        
                        
                        // SAVE GROUPS
                        if (!empty($report['groups'])) {
                            foreach ($report['groups'] as $group_id => $gr_data) {
                        
                                $reportq_groups = new ReportscustomGroups();
                                $reportq_groups->clientid = $clientid;
                                $reportq_groups->group_name = $gr_data['name'];
                                $reportq_groups->save();
                        
                                // GET INSERTED GROUP ID
                                $new_group_id = $reportq_groups->id;
                                if ($new_group_id) {
                        
                                    $rg2r = new ReportscustomGroups2Report();
                                    $rg2r->report_id = $report_id;
                                    $rg2r->group_id = $new_group_id;
                                    $rg2r->save();
                                }
                                if ($new_group_id && ! empty($gr_data['search2group'])) {
                                    foreach ($gr_data['search2group'] as $search_id => $search_details) {
                        
                                        $search2groups_arr[] = array(
                                            "group_id" => $new_group_id,
                                            "search_id" => $search_id,
                                            "negation" => $search_details['negation'],
                                            "options" => $search_details['options']
                                        );
                                    }
                        
                                    if (! empty($search2groups_arr)) {
                                        $collection = new Doctrine_Collection('ReportscustomSearch2Group');
                                        $collection->fromArray($search2groups_arr);
                                        $collection->save();
                                    }
                                }
                            }
                        }
                    }
                    
                    // REPORT COLUMNS
                    $columns_data_arr = ReportscustomColumns2Report::get_report_columns($_GET['id']);
                    $columns_data = $columns_data_arr[$_GET['id']];

                    // Save columns
                    foreach ($columns_data as $k => $column_details) {
                    
                        $columns2report_arr[] = array(
                            "report_id" => $report_id,
                            "column_id" => $column_details['column_id'],
                            "show_average" => $column_details['show_average'],
                            "show_median" => $column_details['show_median']
                        );
                    }
                    
                    if (! empty($columns2report_arr)) {
                        $collection = new Doctrine_Collection('ReportscustomColumns2Report');
                        $collection->fromArray($columns2report_arr);
                        $collection->save();
                    }
                }
            }
            $this->_redirect(APP_BASE . "reportscustom/list");
            exit;
            
        } else{
            $this->_redirect(APP_BASE . "reportscustom/list");
            exit;
        }    
    }

    
    public function generateAction()
    {
        set_time_limit(0);
        $this->_helper->layout->setLayout('layout_ajax');
        $clientid = $this->clientid;
    
        if(empty($_REQUEST)){
            $this->_redirect(APP_BASE . "reportscustom/list");
            exit;
        }
        
        /* ################ GET SAVED REPORT DATA ################################# */
        if($_REQUEST['report_id'] && strlen($_REQUEST['report_id']) > 0 ){
            $report_id = $_REQUEST['report_id'];
    
            // REPORT DETAILS
            $report['details'] = Reportscustom::get_report($report_id);

            // REPORT - PERIOD
            $report_period = ReportscustomPeriods::get_report_period($report_id);
            if($report_period){
                $report['period'] =$report_period[$report_id];
            }
            
            $this->view->saved_data= $report;
            
            
            // REPORT - COLUMNS
            $Tr = new Zend_View_Helper_Translate();
            $report_columns_array = ReportscustomColumns2Report::get_report_columns_details($_REQUEST['report_id'],true);
            foreach($report_columns_array[$_REQUEST['report_id']] as $cr=>$cr_det ){
                if($cr_det['ReportscustomColumns']['type'] != "c"){
                    $not_sortable[] = $cr_det['ReportscustomColumns']['column_name'];
                }
            
                if($cr_det['ReportscustomColumns']['sortable'] == "1"){
                    $sortable_columns[$cr_det['column_id']] = $cr_det['ReportscustomColumns']['column_name'];
            
                    $translated_data[strtolower($Tr->translate($cr_det['ReportscustomColumns']['column_name']))]['column_id'] = $cr_det['column_id'];
                    $translated_data[strtolower($Tr->translate($cr_det['ReportscustomColumns']['column_name']))]['column_name'] = $cr_det['ReportscustomColumns']['column_name'];;
            
                }
            }
            ksort($translated_data);
            foreach($translated_data as $cname=>$cdata){
                $sortable_columns[$cdata['column_id']] = $Tr->translate($cdata['column_name']);
            }
            $this->view->sortable_columns = $sortable_columns;
            
        }
         $response['error'] = array();
            
         
        /* ########################## SAVE ################################# */
        if ($this->getRequest()->isPost() ||  $_REQUEST['generate'] == "1") {
            
            if(!empty($_POST))
            {
                $post = $_POST;
            } 
            else
            {
                $post['report'] = $report;
            }
  

            // ##################
            // VALIDATION - START
            // ##################
            
            if (!empty($post['report']['period']) && !empty($_POST)) {
                
                if($post['report']['period']['type'] == "1" && sizeof($post['report']['period']['year'])==0  ){
                    $response['error'][]  = 1;
                    $response['error_text']['year_filter']  = $this->view->translate("Please select year!");
                }
            
                if($post['report']['period']['type'] == "2") {
            
                    if( empty($post['report']['period']['start_date'])  ){
                        $response['error'][]  = 1;
                        $response['error_text']['start_date']  = $this->view->translate("Please select start date!");
                    }
            
                    if( !empty($post['report']['period']['start_date']) && strtotime(date('d.m.Y',strtotime($post['report']['period']['start_date']))) > strtotime(date('d.m.Y')) ){
                        $response['error'][]  = 1;
                        $response['error_text']['start_date']  = $this->view->translate("The start date in future error");
                    }
                }
                
            }
            
            // ##################
            // VALIDATION - END
            // ##################
            if(count($response['error']) > 0) 
            {
                echo json_encode($response);
                exit;
            }
            else  
            {
                $generate['report_id'] = $_REQUEST['report_id'];
                $generate['months'] = $post['report']['period']['month'];
                $generate['quarters'] = $post['report']['period']['quarter'];
                $generate['years'] = $post['report']['period']['year'];
                
                if(isset($_REQUEST['sortby'])){
                    $generate['sortby'] = $_REQUEST['sortby'];
                } else if(!empty($post['sortby'])){
                    $generate['sortby'] = $post['sortby'];
                } else {
                    $generate['sortby'] = "";
                }
                
                
                if($post['report']['period']['type'] == "2") 
                {
                    $generate['custom_period']['start'] = $post['report']['period']['start_date'];
                    $generate['custom_period']['end'] = $post['report']['period']['end_date'];
                } 
                else
                {
                    $generate['custom_period'] = false; 
                }
                
                if($post['report']['period']['type'] != "3") 
                {
                    $generate['has_date_filter'] = "1";
                    
                } 
                else
                {
                    $generate['has_date_filter'] = false;
                }
                
                if(isset($_REQUEST['export_type'])){
                    $generate['export_type'] = $_REQUEST['export_type'];
                }
                else
                {
                    $generate['export_type'] = $post['generate'];
                }
                
                if($_REQUEST['report_id'])
                {
                    $report_id = $_REQUEST['report_id'];
                    
                    $report_details = $this->report_data($generate , $generate['export_type']);
                    
                    // Maria:: Migration ISPC to CISPC 08.08.2020
                    //ISPC-2534 Lore 21.02.2020
                    $report_columnss = $report_details['columns'];
                    $repo_colmn = array();
                    foreach($report_columnss as $key => $colmn){
                        $repo_colmn[] = $colmn['column'];
                    }
                    //.
                    
                    
                    if(is_array($report_details) && sizeof($report_details) > 0)
                    {
                        if(empty($generate['export_type']))
                        {
                            $output = 'screen';
                        }
                        else
                        {
                            $output = $generate['export_type'];
                        }
                
                        if($output == 'excel')
                        {
/*                             //ISPC-2534 Lore 21.02.2020
                            if(in_array('contact_forms_leistung_koordination', $repo_colmn)){
                                //$this->export_xlsx($report_id, $report_details);
                                $this->generatePHPExcel($report_id, $report_details);                             
                                
                            }else {
                                $this->generatePHPExcel($report_id, $report_details);
                                
                            } */
                            $this->generatePHPExcel($report_id, $report_details);
                        }
                        else
                        {
                            $this->generate_html($report_id, $report_details, $output);
                        }
                    }
                    else
                    {
                        exit;
                    }
                }
                else
                {
                    exit;
                }
            }
            
        }
    }
    
    #########################################################
    ###################### GENERATE #######################
    #########################################################
    /* private function generateReport($report_id, $export_details)
    {
        if($report_id){
            
            $report_details = $this->report_data($export_details);
            
            if(is_array($report_details) && sizeof($report_details) > 0)
            {
                if(empty($export_details['export_type']))
                {
                    $output = 'screen';
                }
                else
                {
                    $output = $export_details['export_type'];
                }
        
                if($output == 'excel')
                {
                    $this->generatePHPExcel($report_id, $reportdata);
                }
                else
                {
                    $this->generate_HTML($report, $reportdata, $output);
                }
            }
            else
            {
                exit;
            }
        } 
        else
        {
            exit;
        }
    } */

    
    
    private function report_data($data , $export_type = false)
    {
        // System data
        $clientid = $this->clientid;
        $search_criterias = ReportscustomSearch::get_search_criterias();
        $columns_array = ReportscustomColumns::get_columns();
        
        
        // REPORT  - ID 
        $report_id = $data['report_id'];
        
        
        // REPORT -  DETAILS
        $report['details'] = Reportscustom::get_report($report_id);


        
        if(empty($data['custom_period']) && empty($data['quarters']) && empty($data['years']) && empty($data['months']))
        {
            // SAVED - REPORT PERIOD
            $saved_report_period = ReportscustomPeriods::get_report_period($report_id);
        
            if($saved_report_period)
            {
                $save_report['period'] = $saved_report_period[$report_id];
                $report['period_type'] = $save_report['period']['type']; 
                if($save_report['period']['type'] == "1")
                {
                    $data['quarters'] =   $save_report['period']['quarters'];
                    $data['years'] = $save_report['period']['years'];
                    $data['months'] = $save_report['period']['months'];
                }
                elseif($save_report['period']['type'] == "2")
                {
                    $data['custom_period']['start'] = $save_report['period']['start_date'];
                    $data['custom_period']['end'] = $save_report['period']['end_date'];
                }
                elseif($save_report['period']['type'] == "3")
                {
                    $data['has_date_filter'] = false;
                }
            }
        }
        
        // REPORT  - PERIOD
        if($data['has_date_filter'])
        {
            if($data['custom_period'])
            {
        
                $report['period'][0]['start'] =  date('Y-m-d',strtotime($data['custom_period']['start']));
        
                if(!empty( $data['custom_period']['end']))
                {
                    $report['period'][0]['end'] =  date('Y-m-d',strtotime($data['custom_period']['end']));
                } 
                else
                {
                    $report['period'][0]['end'] =   date('Y-m-d', strtotime('today midnight'));
                }
            } 
            else 
            {
                $repo_period = Pms_CommonData::getPeriodDates($data['quarters'], $data['years'],$data['months']);
                foreach($repo_period['start'] as $keyd => $startDate)
                {
                    $report['period'][$keyd]['start'] = date("Y-m-d", strtotime($startDate));
                    
                    if(strtotime($repo_period['end'] [$keyd]) > strtotime(date("Y-m-d")))
                    {
                        $report['period'][$keyd]['end'] =  date('Y-m-d', strtotime('today midnight'));
                    }
                    else
                    {
                        $report['period'][$keyd]['end'] = date("Y-m-d", strtotime($repo_period['end'][$keyd]));
                    }
                }
            }
        }
        else 
        {
            $report['period'][0]['start'] =  date('2008-01-01');
            $report['period'][0]['end'] =  date('Y-m-d');
        }
        
        // REPORT - Columns
        $columns_data_arr = ReportscustomColumns::report_columns($report_id);
        $report['columns'] = $columns_data_arr[$report_id];
        
        // REPORT - GROUPS
        $report_groups = ReportscustomGroups2Report::get_report_groups($report_id);
        if($report_groups)
        {
            $report_groups_ids = $report_groups [$report_id];
            $groups_data  = ReportscustomGroups ::get_groups_details($report_groups_ids);
            $search_data = ReportscustomSearch2Group::get_groups_search_details($report_groups_ids);
        
            foreach( $report_groups_ids as $group_id)
            {
                $report['groups'][$group_id]['name'] = $groups_data[$group_id]['group_name'];
                $report['groups'][$group_id]['group_id'] = $group_id;
                $report['groups'][$group_id]['search2group'] = $search_data[$group_id];
                
                foreach($search_data[$group_id] as $search_id => $search_detailss)
                {
                    $report['groups'][$group_id]['db_name'][$search_detailss['system']['db_name']][$search_id] =$search_detailss['system']['search']; 
                    $report['groups'][$group_id]['search_ids2group'][] = $search_id;
                    $report['groups'][$group_id]['search_names2group'][] = $search_detailss['system']['search'];
                    $report['groups'][$group_id]['search_types'][] = $search_detailss['system']['type'];
                    if($search_detailss['negation'] == "1")
                    {
                        $report['groups'][$group_id]['negation_search_ids2group'][] = $search_id;
                    }
                }
            }
        }
        // CLIENT RELATED DETAILLS
        $dead_methods_ids = DischargeMethod::get_client_discharge_method($clientid,true,true);
        
        $discharge_methods = DischargeMethod::get_client_discharge_method($clientid);
        
        $discharge_locations = DischargeLocation::getDischargeLocation($clientid,1);
        
        $client_locations = Locations::getAllLocations($ipid = false, $letter = false, $keyword = false, $arrayids = false, $clientid);
        
        //ISPC-1948 Ancuta 25.08.2020 holiday location
        //1 =  hospital
        //7 = palliativstation
        //11 = holliday
        $hospital_peers = array('1','7','11');
        // --
        
        
        $client_hospital_location_ids_str .= '"0",';
        foreach($client_locations  as $k=>$loc_details)
        {
            $client_location_details[$loc_details ['id']] = $loc_details;
             
            if( in_array($loc_details ['location_type'],$hospital_peers) ) //ISPC-1948 Ancuta 25.08.2020 holiday location
            {
                $client_hospital_location_ids[] = $loc_details ['id']; 
                $client_hospital_location_ids_str .= '"'.$loc_details ['id'].'",'; 
            }
        }
        
        if(empty($client_hospital_location_ids))
        {
            $client_hospital_location_ids[] = "99999999999";
        }
        
        //GET CLIENT USERS
        $user = new User();
        $client_users_arr = $user->getUserByClientid($clientid, '0', true);
        
        foreach($client_users_arr as $k_usr => $v_usr)
        {
            $client_users[$v_usr['id']] = $v_usr;
            $client_users_names[$v_usr['id']]['name'] = $v_usr['user_title'] . ' ' . $v_usr['last_name'] . ', ' . $v_usr['first_name'];
            $client_users_ids[] = $v_usr['id'];
        }
        
        //CLIENT PFLEGEDIENSTE
        $pflegedienste = array();
        $clpflearray = Doctrine_Query::create()
        ->select("id,nursing")
        ->from('Pflegedienstes')
//         ->where('clientid = ' . $clientid) // TODO-1793
        ->fetchArray();
        
        foreach($clpflearray as $val)
        {
            $pflegedienste[$val['id']]['id'] = $val['id'];
            $pflegedienste[$val['id']]['name'] = $val['nursing'];
        }
        
        //CLIENT PHARMACY
        $clph = Doctrine_Query::create()
        ->select("*")
        ->from('Pharmacy')
        ->where('clientid = ' . $clientid.'  OR  clientid = 0');
        $clpharray = $clph->fetchArray();
        
        $pharmacy = array();
        foreach($clpharray as $val)
        {
            $pharmacy[$val['id']]['id'] = $val['id'];
            $pharmacy[$val['id']]['name'] = $val['pharmacy'];
        }

        
        // GET ALL CLIENT PATIENTS
        $patients_ipids = array();
        $q_idat = Doctrine_Query::create();
        $q_idat->select('e.ipid');
        $q_idat->from('EpidIpidMapping e INDEXBY e.ipid');
        $q_idat->leftJoin('e.PatientMaster p');
        $q_idat->where('e.clientid= "'.$clientid.'" ');
        $q_idat->andWhere('p.isdelete = 0');
        $q_idat->andWhere('p.isstandbydelete = 0');
        $q_idat->orderBy('e.ipid ASC');
        
        $client_patients = $q_idat->fetchArray();
        
        $client_patients_ipids_str = '"0",';
        foreach($client_patients as $k=>$pipid)
        {
            $patients_ipids[0][] = $pipid['ipid'];
            $client_patients_ipids[] = $pipid['ipid'];
            $client_patients_ipids_str .= '"'.$pipid['ipid'].'",';
        }
        
        if(empty($client_patients_ipids))
        {
            $client_patients_ipids[] = "9999999999999";
        }
        
        $group_patients_ipids = array();
        
        $search_ip_sapv_data = array("sapv_in_report", "sapv_specific_status_in_report","sapv_specific_type_in_report","first_verordnung");
        $search_ip_discharge_data = array("died_in_report","discharged_in_report","discharged_in_report_specific_dtype" , "discharged_and_then_died");
        $search_ip_admission_data = array("active_in_report","first_admission_in_report");
        
        if($report['groups'])
        {
            foreach($report['groups'] as $gr_id => $gr_data)
            {
                $sql_active[$gr_id] = '';
                $sql_locations[$gr_id] = '';
                $sql_discharge[$gr_id] = '';
                $sql_died[$gr_id] = '';
                $sql_discharged_and_then_died[$gr_id] = '';
                $sql_active_not_standby[$gr_id] = '';
        
                $sql_sapv[$gr_id] = '';
                $sql_sapv_period[$gr_id] = '';
                $sql_discharge_spec_type[$gr_id] = '';
                $sql_sapv_spec_status_inperiod[$gr_id] = '';
                $sql_sapv_spec_type_inperiod[$gr_id]  = '';
                $sql_sapv_spec_type[$gr_id]  = '';
                $sql_sapv_spec_status[$gr_id]  = '';
                $sql_sapv_overall[$gr_id]  = '';
                $hospital_in_period_sql[$gr_id] = "";
                $first_admission_condition[$gr_id] = "";
                $first_sapv_condition[$gr_id] = "";
                $teammeeting_sql[$gr_id]  = "";
                
                $sql_standby[$gr_id] = null;
                $sql_standby_negate[$gr_id] = null;
                $sql_period_standby[$gr_id] = array();
                $sql_period_standby_str[$gr_id] = null;
                
                $member_donations_sql[$gr_id]  = "";
                $member_membership_sql_str[$gr_id]  = "";
                $member_membership_end_sql_str[$gr_id] ="";
                
                $member_donation_amount[$gr_id] = "";
                $custom_added_member_donation_q[$gr_id] = "";
                
                
                
                $used_sapv_in_period[$gr_id] = array(); 
                $used_discharge_in_period[$gr_id] = array(); 
                $used_adm_in_period[$gr_id] = array(); 
                $has_sapv_condition[$gr_id] = "";
                
                $sql_default_period_str[$gr_id] = "";
                $sql_default_period[$gr_id] = "";
                
                $sql_course_period_str[$gr_id] = "";
                $sql_course_period[$gr_id] = "";

                foreach($gr_data['search2group'] as $search_id => $search_details)
                {
                    if($search_details['negation']=="1")
                    {
                        $equal = " != ";
                        $in = " NOT IN ";
                        $like = " NOT LIKE ";
                        $not = "NOT";
                        $compare = "<";
                        $is_null = " IS NOT NULL ";
                    }
                    else
                    {
                        $equal = " = ";
                        $in = " IN ";
                        $like = " LIKE ";
                        $not = "";
                        $compare = ">";
                        $is_null = " IS NULL ";
                    }
                    
                    //@TODO  NEGATE periods 
                    
                    $group_search_data[$gr_id][] = $not.' '.$search_details['system']['search'].' value = '.$search_details['options'];
                    
                    // CREATE PERIODS 
                    foreach($report['period'] as $period) 
                    {
                        // DEFAULT PERIOD - 
                        if($search_details['system']['search'] == "first_admission_in_report" || $search_details['system']['search'] == "first_verordnung"  )
                        {                            
                            if($search_details['negation']== "1")
                            {
                                $sql_default_period_str[$gr_id] .= ' AND ( NOT (%date% BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '")  )';
                            }
                            else
                            {
                                $sql_default_period_str[$gr_id] .= ' OR ((%date% BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '")  )';
                            }
                        }

                        
                        // Report course PERIOD - 
                        if($search_details['system']['search'] == "course_search"  )
                        {                            
							$sql_course_period_str[$gr_id] .= ' OR ((%date% BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '")  )';
							
							if($search_details['negation']== "1")
							{
								$sql_course_negate[$gr_id] = '1';
							}
							else
							{
								$sql_course_negate[$gr_id] = '0';
							}
                        }
                        
                            
                        
                        // ACTIVE PERIOD
//                         if(in_array($search_details['system']['search'], $gr_data['search_names2group']) && in_array($search_details['system']['search'], $search_ip_admission_data) && empty($used_adm_in_period[$gr_id]))
                        if(in_array($search_details['system']['search'], $gr_data['search_names2group']) && in_array($search_details['system']['search'], $search_ip_admission_data) && (empty($used_adm_in_period[$gr_id]) || !in_array($search_details['system']['search'].$period['start'],$used_adm_in_period[$gr_id]))   )
                        {
                            if($search_details['negation']== "1")
                            {
                                $sql_active[$gr_id] .= ' AND  ( NOT (a.start BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") AND ( NOT (a.end BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '")) AND NOT (a.start <= "' . $period['start'] . '" AND (a.end = "0000-00-00" OR a.end >= "' . $period['end'] . '")))';
                            } 
                            else
                            {
                                $sql_active[$gr_id] .= ' OR ((a.start BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (a.end BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (a.start <= "' . $period['start'] . '" AND (a.end = "0000-00-00" OR a.end >= "' . $period['end'] . '")))';
                            }
                            
                            
                            $used_adm_in_period[$gr_id][] = $search_details['system']['search'].$period['start'];
                            
                        }
                        
                        // HOSPITAL / LOCATIONS  PERIODS
                        if($search_details['system']['search'] == "hospital_stays_in_report")
                        {
                            $sql_locations[$gr_id] .= ' OR ((date(l.valid_from) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (date(l.valid_till) BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (date(l.valid_from) <= "' . $period['start'] . '" AND (date(l.valid_till) = "0000-00-00" OR date(l.valid_till) >= "' . $period['end'] . '")))';
                        }
                        
                        
                        // DISCHARGE  PERIODS       
//                         if(in_array($search_details['system']['search'], $gr_data['search_names2group']) && in_array($search_details['system']['search'], $search_ip_discharge_data) && empty($used_discharge_in_period[$gr_id])) 
                        if(in_array($search_details['system']['search'], $gr_data['search_names2group']) && in_array($search_details['system']['search'], $search_ip_discharge_data)) 
                        {// commented the empty condition for $used_discharge_in_period - TODO-1687
                            $sql_discharge_query[$gr_id] .= ' OR (date(d.discharge_date) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '")';
                            $used_discharge_in_period[$gr_id][] = $search_details['system']['search'];
                        }
                        
                        
                        // SAPV PERIOD  
//                         if(in_array($search_details['system']['search'], $gr_data['search_names2group']) && in_array($search_details['system']['search'], $search_ip_sapv_data) &&  empty($used_sapv_in_period[$gr_id][$period['start']])     ) 
                        if(in_array($search_details['system']['search'], $gr_data['search_names2group']) && in_array($search_details['system']['search'], $search_ip_sapv_data) && (empty($used_sapv_in_period[$gr_id][$period['start']]) || !in_array($search_details['system']['search'].$period['start'],$used_sapv_in_period[$gr_id][$period['start']]) )    ) 
                        {
                            $sql_sapv_period[$gr_id] .= ' OR   ( date(s.verordnungam) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR  (date(s.verordnungbis) BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR ( date(s.verordnungam) <= "' . $period['start'] . '" AND date(s.verordnungbis) >= "' . $period['end'] . '")  ';
                            
//                             $sql_sapv_period[$gr_id] .= ' OR (((date(s.verorddisabledate) = "0000-00-00") OR (s.verorddisabledate >= s.verordnungbis) ) AND ( date(s.verordnungam) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (date(s.verordnungbis) BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (date(s.verordnungam) <= "' . $period['start'] . '" AND (date(s.verordnungbis) = "0000-00-00" OR date(s.verordnungbis) >= "' . $period['end'] . '"))';
//                             $sql_sapv_period[$gr_id] .= ' OR (((date(s.verorddisabledate) != "0000-00-00") AND (s.verorddisabledate < s.verordnungbis) ) AND ( date(s.verordnungam) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (date(s.verorddisabledate) BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (date(s.verordnungam) <= "' . $period['start'] . '" AND (date(s.verorddisabledate) = "0000-00-00" OR date(s.verorddisabledate) >= "' . $period['end'] . '"))))';

                            $used_sapv_in_period[$gr_id][] = $search_details['system']['search'].$period['start'];
                        }
                        
                        // Team meeting
                        if($search_details['system']['search'] == "attended_team_meetings_in_report")
                        {
                            $teammeeting_sql[$gr_id] .= ' ( DATE(t.date) >= DATE("' . $period['start'] . '") AND DATE(t.date) <= DATE("' . $period['end'] . '") )  OR ';
                        }
                        
                        
                        
                        
                        // Member donations in period
                        if($search_details['system']['search'] == "members_donation_in_period")
                        {
                            $member_donations_sql[$gr_id] .= ' ( DATE(md.donation_date) >= DATE("' . $period['start'] . '") AND DATE(md.donation_date) <= DATE("' . $period['end'] . '") )  OR ';
                        }
                        
                        // Member membership end  in period
                        if($search_details['system']['search'] == "members_membership_end_in_period")
                        {
                            $member_membership_end_sql_str[$gr_id] .= ' ( DATE(m2m.end_date) >= DATE("' . $period['start'] . '") AND DATE(m2m.end_date) <= DATE("' . $period['end'] . '") )  OR ';
                        }
                        
                        //Membership period
                        if($search_details['system']['search'] == "members_in_period")
                        {
                            $member_membership_sql_str[$gr_id] .= ' OR   ( date(m2m.start_date) BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR  (date(m2m.end_date) BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR ( date(m2m.start_date) <= "' . $period['start'] . '" AND date(m2m.end_date) >= "' . $period['end'] . '")  ';
                        }
                        
                       
                        //period for was_standby or not_active
                        if($search_details['system']['search'] == "was_standby" || $search_details['system']['search'] == "not_active")
                    	{
                    		if(empty($period['end']))
                    		{
                    			$period['end'] = date('Y-m-d', strtotime('+1 day'));
                    		}
                        	$sql_period_standby[$gr_id][] = ' ((ps.start BETWEEN "' . $period['start'] . '" AND "' . $period['end'] . '") OR (ps.end BETWEEN "' . $period['start'] . '"	AND "' . $period['end'] . '") OR (ps.start <= "' . $period['start'] . '" AND (ps.end = "0000-00-00" OR ps.end >= "' . $period['end'] . '")))';
                        }
                       
                        
                    } /* -- PERIOD FOREACH END -- */
                 
                    
                    if(  $search_details['system']['search'] == "first_admission_in_report" || $search_details['system']['search'] == "first_verordnung")
                    {
                        if($search_details['negation'] == "1")
                        {
                            $sql_default_period[$gr_id] = ' (' . substr($sql_default_period_str[$gr_id], 4) . ') ';
                        }
                        else
                        {
                            $sql_default_period[$gr_id] = ' (' . substr($sql_default_period_str[$gr_id], 4) . ') ';
                        }
                    }                    
                    
                    if($search_details['system']['search'] == "course_search"  )
                    {
                        if($search_details['negation'] == "1")
                        {
                            $sql_course_period[$gr_id] = ' (' . substr($sql_course_period_str[$gr_id], 4) . ') ';
                        }
                        else
                        {
                            $sql_course_period[$gr_id] = ' (' . substr($sql_course_period_str[$gr_id], 4) . ') ';
                        }
                    }                    
                    
                    // ACTIVE 
                    if($search_details['system']['search'] == "active_in_report")
                    {
                        if($search_details['negation'] == "1")
                        {
//                             $sql_active[$gr_id] = ' AND (' . substr($sql_active[$gr_id], 3) . ') ';
                            $sql_active[$gr_id] = ' AND (' . substr($sql_active[$gr_id], 4) . ') '; // !!!!!!!
                        } 
                        else
                        {
                            $sql_active[$gr_id] = ' AND (' . substr($sql_active[$gr_id], 4) . ') ';
                        }
                        
                        $sql_active_not_standby[$gr_id] = 'p.isstandby = 0'; // ISPC-1908: 01.03.2017  
                        
                    }
                    
                    
                    // FIRST ADMISSION IN PERIOD
                    if($search_details['system']['search'] == "first_admission_in_report")
                    {
                        $first_admission_condition[$gr_id] = "1";

                        if($search_details['negation'] == "1")
                        {
                            $first_admission_negation[$gr_id] = " != ";
                        } 
                        else
                        {
                            $first_admission_negation[$gr_id] = " = ";
                        }
                    }
                     
                    
                    
                    // DISCHARGE
                    if($search_details['system']['search'] == "died_in_report" 
                    		|| $search_details['system']['search'] == "discharged_in_report"  
                    		|| $search_details['system']['search'] == "discharged_in_report_specific_dtype"
                    		|| $search_details['system']['search'] == "discharged_and_then_died")
                    {
                        if($search_details['system']['search'] == "died_in_report" )
                        {
                           $sql_died[$gr_id] = ' AND d.discharge_method '.$in.' ('.substr($dead_methods_ids, 0,-2).') '; 
                        }  
                        
                        if($search_details['system']['search'] == "discharged_in_report_specific_dtype"  && !empty($search_details['options']))
                        {
                           $sql_discharge_spec_type[$gr_id] = ' AND d.discharge_method '.$equal.' "'.$search_details['options'].'" '; 
                        }  
                        
                        
                        //ISPC-1945 death after discharge
                        if($search_details['system']['search'] == "discharged_and_then_died" )
                        {
                        	if($search_details['negation']=="1") {
                        		$discharged_and_then_died_in = " IN "; //reverser negate logic
                        	} else {
                        		$discharged_and_then_died_in = " NOT IN ";
                        	}
                        	$sql_discharged_and_then_died[$gr_id] = ' AND d.discharge_method ' . $discharged_and_then_died_in . ' ('.substr($dead_methods_ids, 0,-2).') ';
                        	
                        	$pd_obj = new PatientDeath();
                        	$patients_death = $pd_obj->get_patients_death($client_patients_ipids);
                        	
                        	if ( ! empty($patients_death)) {
                        		
                        		$patients_death_ipid_arr = array_column($patients_death, "ipid");
                        		$sql_discharged_and_then_died[$gr_id] .= ' AND e.ipid ' . $in . ' (\''.implode("', '", $patients_death_ipid_arr).'\') ';
                        		
                        	} else {
                        		
                        		$sql_discharged_and_then_died[$gr_id] .= ' AND e.ipid ' . $is_null;
                        	}
                        }
                        
                        //TODO-4135 Ancuta 18.05.2021
                        //$sql_discharge[$gr_id] = ' AND d.isdelete = "0" AND (' . substr($sql_discharge_query[$gr_id], 3) . ') '
                        $sql_discharge[$gr_id] = ' AND (' . substr($sql_discharge_query[$gr_id], 3) . ') '
                        		. $sql_died[$gr_id]
                        		. ' '
                        		. $sql_discharge_spec_type[$gr_id]
                        		. ' ' 
                        		. $sql_discharged_and_then_died[$gr_id]
                        		. ' ';
                        //-- 
                    }
                    
                    
                    // HOSPITAL STAYS                    
                    if($search_details['system']['search'] == "hospital_stays_in_report" || $search_details['system']['search'] == "hospital_stays_overall")
                    {
                        $in_hospital_sql[$gr_id] = ' AND location_id '.$in.' ('.substr($client_hospital_location_ids_str,0,-1).')';           

                        if($search_details['system']['search'] == "hospital_stays_in_report")
                        {
                            $sql_locations[$gr_id] = ' AND l.isdelete = "0" AND (' . substr($sql_locations[$gr_id], 3) . ') '.$in_hospital_sql[$gr_id].' ';
                        } 
                        else 
                        {
                            $sql_locations[$gr_id] = ' AND l.isdelete = "0" '.$in_hospital_sql[$gr_id].' ';
                        }
                    }
                    
                    // SAPV DETAILS - STATUSES AND TYPES - IN PERIOD
                    if($search_details['system']['search'] == "sapv_specific_status_in_report" && !empty($search_details['options']) )
                    {
                        $sql_sapv_spec_status_inperiod[$gr_id] = 'AND s.status '.$equal.' "'.$search_details['options'].'" ';
                    }
                    
                    if($search_details['system']['search'] == "sapv_specific_type_in_report" && !empty($search_details['options']) )
                    {
                        $sql_sapv_spec_type_inperiod[$gr_id] = ' AND s.verordnet '.$like.'  "%'.$search_details['options'].'%" ';
                    } 
                    
                    if($sql_sapv_period[$gr_id])
                    {
                        $sql_sapv_in_period[$gr_id] = 'AND (' . substr($sql_sapv_period[$gr_id], 3) . ')    ';
                    }

                    if(strlen($sql_sapv_period[$gr_id]) > 0 )
                    {
                        $sql_sapv[$gr_id] = ' AND s.isdelete = "0" '.$sql_sapv_in_period[$gr_id].'  ';
                    }
                    
                    if(strlen($sql_sapv_spec_type_inperiod[$gr_id])>0)
                    {
                        $sql_sapv[$gr_id] .= ''.$sql_sapv_spec_type_inperiod[$gr_id].' ';
                    }
                    
                    if(strlen($sql_sapv_spec_status_inperiod[$gr_id])>0)
                    {
                        $sql_sapv[$gr_id] .= ' '.$sql_sapv_spec_status_inperiod[$gr_id].'  ';
                    }

                    // FIRST SAPV IN PERIOD
                    if($search_details['system']['search'] == "first_verordnung")
                    {
                        $first_sapv_condition[$gr_id] = "1";

                        if($search_details['negation']=="1")
                        {
                            $first_sapv_negation[$gr_id] = " != ";
                        } 
                        else
                        {
                            $first_sapv_negation[$gr_id] = " = ";
                        }
                    } 
                    
                    // SAPV DETAILS - STATUSES AND TYPES - OVERALL
                    if($search_details['system']['search'] == "sapv_specific_status" && !empty($search_details['options']) )
                    {
                        $sql_sapv_spec_status[$gr_id] = '   AND s.status '.$equal.' "'.$search_details['options'].'" ';
                    }  
    
                    if($search_details['system']['search'] == "sapv_specific_type" && !empty($search_details['options']) )
                    {
                        $sql_sapv_spec_type[$gr_id] = ' AND s.verordnet '.$like.'  "%'.$search_details['options'].'%" ';
                    }  
                    
                    if(strlen($sql_sapv_spec_type[$gr_id]) > 0 || strlen($sql_sapv_spec_status[$gr_id])>0)
                    {
                        $sql_sapv_overall[$gr_id] = ' AND s.isdelete = "0" AND date(s.verordnungam) != "0000-00-00" AND date(s.verordnungbis) != "0000-00-00"  '.$sql_sapv_spec_type[$gr_id].' '.$sql_sapv_spec_status[$gr_id].'';
                    }
                    
                    
                    if($search_details['system']['search'] == "has_sapv")
                    {
                        $has_sapv_condition[$gr_id] = "1";
                        if($search_details['negation'] != "1")
                        {
                            $sql_has_sapv[$gr_id] = ' AND s.isdelete = "0"  AND date(s.verordnungam) != "0000-00-00" AND date(s.verordnungbis) != "0000-00-00"';
                            $sql_has_sapv_negate[$gr_id] = '0';
                        } 
                        else
                        {
                            $sql_has_sapv[$gr_id] = ' AND 1 ';
                            $sql_has_sapv_negate[$gr_id] = '1';
                        }
                    }
                    else
                    {
                        $has_sapv_condition[$gr_id] = "0";
                    }
                    
                    // STANDBY
                    if($search_details['system']['search'] == "was_standby" || $search_details['system']['search'] == "not_active")
                    {
                    	//$sql_standby[$gr_id] = 'p.isstandby '.$equal.' 1';
                    		
                    	if ( ! empty($sql_period_standby[$gr_id])) {
                    		$sql_period_standby_str[$gr_id] = " AND ( " . implode(" OR ", $sql_period_standby[$gr_id]) . " )";
                    	}
                    	
                        if($search_details['negation'] == "1") {
                        	$sql_standby_negate[$gr_id] = true;
                        } else {
                        	$sql_standby_negate[$gr_id] = false;
                        }
                        
                        $sql_standby[$gr_id] = 'e.PatientStandby ps ON e.ipid = ps.ipid '. $sql_period_standby_str[$gr_id];
                        	 
                    } 
                    
                    
                    
                    // SPECIFIC ICD
                    if($search_details['system']['search'] == "specific_icd" && !empty($search_details['options']))
                    {
                        $icd_specific[$gr_id] = $search_details['options'];

                        // Get specific diagnostics by icd
                        $diagnosisfreetext = Doctrine_Query::create()
                        ->select('id')
                        ->from('DiagnosisText')
                        ->where("clientid = '".$clientid."' ")
                        ->andWhere("trim(lower(icd_primary )) like trim(lower('%" . $icd_specific[$gr_id] . "%'))");
                        $diagnosisfreetext_ids_arr =$diagnosisfreetext->fetchArray();

                        
                        $custom_added_icds_str ='"0",';
                        foreach($diagnosisfreetext_ids_arr as $k_ficd =>$v_ficd)
                        {
                            $custom_added_icds[$gr_id][] = $v_ficd['id'];
                            $custom_added_icds_str .='"'.$v_ficd['id'].'",';
                        }
                        
                        if(!empty($custom_added_icds[$gr_id]))
                        {
                            $custom_added_icd_q[$gr_id] = ' AND diagnosis_id '.$in.' ('.substr($custom_added_icds_str,0,-1).') ';
                        }
                        
                        $diagnosis = Doctrine_Query::create()
                        ->select("id")
                        ->from("Diagnosis")
                        ->where("trim(lower(icd_primary )) like trim(lower('%" . $icd_specific[$gr_id] . "%'))");
                        $diagnosisarray = $diagnosis->fetchArray();
                        
                        $selected_icd_str ='"0",';
                        foreach($diagnosisarray  as $k_sicd =>$v_sicd)
                        {
                            $selected_icds[$gr_id][] = $v_sicd['id'];
                            $selected_icd_str .='"'.$v_sicd['id'].'",';
                        }
                        
                        if(!empty($selected_icds[$gr_id]))
                        {
                            $selected_icd_q[$gr_id] = ' OR diagnosis_id '.$in.' ('.substr($selected_icd_str,0,-1).') ';
                        }
                        
                         if(strlen($selected_icd_q[$gr_id]) > 0 || strlen($custom_added_icd_q[$gr_id])>0)
                         {
                            $icd_q = Doctrine_Query::create()
                            ->select("ipid")
                            ->from('PatientDiagnosis')
                            ->where('1 '.$custom_added_icd_q[$gr_id].' '.$selected_icd_q[$gr_id].' ')
                            ->orderBy('id ASC');
                            $special_icd_data[$gr_id]  = $icd_q->fetchArray();
                            
                            $icd_patients_str = '"0",';
                            $icd_patients_array[$gr_id] = array();
                            foreach($special_icd_data[$gr_id] as $icd => $icd_pat)
                            {
                                if(!in_array($icd_pat['ipid'],$icd_patients_array[$gr_id]))
                                {
                                    $icd_patients_array[$gr_id][] = $icd_pat['ipid'];
                                    $icd_patients_str .= '"'.$icd_pat['ipid'].'",';
                                }
                            }
                         }
                      }
                      
                      // SPECIFIC HEALTH INSURANCE
                    if($search_details['system']['search'] == "specific_health_insurance" && !empty($search_details['options']))
                    {
                      $health_insurance_string[$gr_id] = $search_details['options'];
                      
                      $hi_master_q = Doctrine_Query::create()
                        ->select('health.id')
                        ->from('HealthInsurance health')
                        ->where("trim(lower(health.name)) like trim(lower('%" .  $health_insurance_string[$gr_id] . "%'))")
                        ->andWhere(' health.isdelete= 0 ')
                        ->andWhere(' health.extra= 0 ')
                        ->andWhere(' health.onlyclients="1" ')
                        ->andWhere(' health.clientid="' . $clientid . '" ');
                      
                      $hi_master_qarr[$gr_id] = $hi_master_q->fetchArray();
                    
                      if($hi_master_qarr[$gr_id])
                      {
                          //$health_insurance_master_ids ='"0",'; TODO-3134 Carmen 04.05.2020 
                          foreach($hi_master_qarr[$gr_id] as $k=>$him_val)
                          {
                              $health_insurance_master_ids .='"'.$him_val['id'].'",'; 
                          }
                          
                          $hi_master_sql_str[$gr_id] = " OR ph.companyid ".$in." (".substr($health_insurance_master_ids,0,-1).") ";
                          //TODO-3134 Carmen 04.05.2020
                          $sql_health[$gr_id] = "AND ( TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(ph.company_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci ".$like." TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($health_insurance_string[$gr_id]) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci ".$hi_master_sql_str[$gr_id].")" ;
                      }
                      else 
                      {
                      	$sql_health[$gr_id] = "AND ( TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(ph.company_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci ".$like." TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($health_insurance_string[$gr_id]) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci )" ;
                      }                     
 
                         //$sql_health[$gr_id] = "AND ( TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(ph.company_name,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci ".$like." TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($health_insurance_string[$gr_id]) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci ".$hi_master_sql_str[$gr_id].")" ;
                      //--
                    }
                    
                    //COURSE SEARCH
                    if($search_details['system']['search'] == "course_search" && !empty($search_details['options']))
                    {
                    	
                      $course_search_string[$gr_id] = $search_details['options'];
                      
                      $patient_course_q[$gr_id] = Doctrine_Query::create()
                          ->select("ipid,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title")
                          ->from('PatientCourse')
                          ->whereIn('ipid',$client_patients_ipids)
                          ->andWhere(' wrong = 0 ')
//                           ->andWhere(" TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci ".$like." TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($course_search_string[$gr_id]) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci  ");
                          ->andWhere(" TRIM(LOWER(CONVERT(CONVERT(AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') USING utf8) USING latin1)))  COLLATE latin1_german2_ci LIKE TRIM(LOWER(CONVERT(CONVERT( '%" . utf8_decode($course_search_string[$gr_id]) . "%' USING utf8) USING latin1))) COLLATE latin1_german2_ci  ");
                          if(strlen($sql_course_period[$gr_id])>0){
                                $patient_course_q[$gr_id]->andWhere('(' . str_replace('%date%', 'DATE(course_date)', $sql_course_period[$gr_id]) . ')');
                          }
                          
//                           echo $patient_course_q[$gr_id]->getSqlQuery();
//                            exit;
                      $patient_course_qarr[$gr_id] = $patient_course_q[$gr_id]->fetchArray();
//                     print_r($patient_course_qarr); exit;
                      if($patient_course_qarr[$gr_id])
                      {
                          $patient_course_ipids_str ='"COURSE'.$gr_id.'",'; 
                          $patient_course_ipids[$gr_id] = array(); 
                          foreach($patient_course_qarr[$gr_id] as $pk=>$pc_val)
                          {
                              if(!in_array($pc_val['ipid'],$patient_course_ipids[$gr_id]))
                              {
                                  $patient_course_ipids[$gr_id][] = $pc_val['ipid'];
                                  $patient_course_ipids_str  .='"'.$pc_val['ipid'].'",'; 
                              }
                          }
                      }
                      else
                      {
                          $patient_course_ipids_str ='"COURSE'.$gr_id.'",'; 
                          $patient_course_ipids[$gr_id][] = "999999999999999999";
                      }
                    }
                    
                    
                    
                    if($search_details['system']['search'] == "members_donation_in_period" && !empty($search_details['options']))
                    {
                        $member_donation_amount[$gr_id] = $search_details['options'];
                        $custom_added_member_donation_q[$gr_id] = ' md.amount '.$compare.'  "'.$member_donation_amount[$gr_id].'"  ';
                    }
                    
                    
                    
                } /* GROUP SEARCH CRITERIAS FOREACH END */
                
                //######################################################################
                // MASTER QUERY  IDAT - FOR PATIENT SEARCH
                //######################################################################
                if(in_array("patient", $gr_data['search_types'])){
                        
                    $q_idat = Doctrine_Query::create();
                    $q_idat->select('e.ipid,e.epid,s.verordnungam,s.verordnungbis');
                    $q_idat->from('EpidIpidMapping e INDEXBY e.ipid');
                    $q_idat->leftJoin('e.PatientMaster p');
    
                    if(strlen($sql_active[$gr_id]) > 0 || $first_admission_condition[$gr_id] == "1")
                    {
                        if( $first_admission_condition[$gr_id] == "1" )
                        {
                            
                            $in =  $q_idat->createSubquery()
                            ->select('b.id')
                            ->from('PatientActive b')
                            ->where('a.ipid = b.ipid')
                            ->orderBy('b.start ASC')
                            ->limit('1');
                            $q_idat->leftJoin('e.PatientActive a ON a.ipid = e.ipid  AND a.id '.$first_admission_negation[$gr_id].' (' . $in->getDql() . ')   AND (' . str_replace('%date%', 'a.start', $sql_default_period[$gr_id]) . ')    ');
                        } 
                        else
                        {
                            $q_idat->leftJoin('e.PatientActive a ON a.ipid = e.ipid' . $sql_active[$gr_id] . ' ');
                        }   
                    }
    
                    
                    if(strlen($sql_discharge[$gr_id]) > 0)
                    {
                        $q_idat->leftJoin('e.PatientDischarge d ON d.ipid = e.ipid' . $sql_discharge[$gr_id] . ' INDEXBY d.id');
                    }
                    
                    if(strlen($sql_sapv[$gr_id])>0 || strlen($sql_sapv_overall[$gr_id]) > 0 || $has_sapv_condition[$gr_id] == "1" || $first_sapv_condition[$gr_id] == "1" )
                    {
                        if($sql_has_sapv_negate[$gr_id] == "1"){
                            
                            $sql_sapv[$gr_id] = "";
                            $sql_sapv_overall[$gr_id] = "";
                            $first_sapv_condition[$gr_id] = "0";
                        }
                        
                        if( $first_sapv_condition[$gr_id] == "1" )
                        {
                            
                            $sapv_subquery =  $q_idat->createSubquery()
                            ->select('ss.id')
                            ->from('SapvVerordnung ss')
                            ->where('s.ipid = ss.ipid')
                            ->andWhere('ss.isdelete = 0')
                            ->orderBy('ss.verordnungam ASC')
                            ->limit('1');
                            
                            $q_idat->leftJoin('e.SapvVerordnung s ON s.ipid = e.ipid  AND s.isdelete = "0"  AND date(s.verordnungam) != "0000-00-00" AND date(s.verordnungbis) != "0000-00-00"   AND s.id '.$first_sapv_negation[$gr_id].' (' . $sapv_subquery->getDql() . ') AND (' . str_replace('%date%', 's.verordnungam', $sql_default_period[$gr_id]) . ') ');
                        } 
                        else
                        {
                            $q_idat->leftJoin('e.SapvVerordnung s ON s.ipid = e.ipid '.$sql_has_sapv[$gr_id].' '. $sql_sapv[$gr_id] . ' '.$sql_sapv_overall[$gr_id].'  INDEXBY s.id');
                        }
                    }
                    
                    if(strlen($sql_locations[$gr_id]) > 0)
                    {
                        $q_idat->leftJoin('e.PatientLocation l ON l.ipid = e.ipid' . $sql_locations[$gr_id] . ' AND l.discharge_location = 0 INDEXBY l.id');
                    }
                    
                    if(strlen($health_insurance_string[$gr_id]) > 0)
                    {
                        $q_idat->leftJoin('e.PatientHealthInsurance ph ON ph.ipid = e.ipid   ' . $sql_health[$gr_id] . '  INDEXBY ph.id');
                    }
                    
                    $q_idat->where('e.clientid= "'.$clientid.'" ');
                    
                    if(strlen($sql_active[$gr_id]) > 0 || $first_admission_condition[$gr_id] == "1")
                    {
                        $q_idat->andWhere('a.ipid IS NOT NULL');
                    }
                    
    
                    if(strlen($sql_discharge[$gr_id]) > 0)
                    {
                        $q_idat->andWhere('d.ipid IS NOT NULL');
                    }

                    
                    if(strlen($sql_sapv[$gr_id])>0 || strlen($sql_sapv_overall[$gr_id]) > 0 || $has_sapv_condition[$gr_id] == "1" || $first_sapv_condition[$gr_id] == "1" )
                    {
                        $q_idat->andWhere('s.ipid IS NOT NULL ');
                    }
                    
                    if($sql_has_sapv_negate[$gr_id] == 1)
                    {
                        $q_idat->andWhere('s.ipid IS NULL ');
                    }
                    
                    if(strlen($sql_locations[$gr_id]) > 0 )
                    {
                        $q_idat->andWhere('l.ipid IS NOT NULL ');
                    }
                    
                    
                    if(strlen($health_insurance_string[$gr_id]) > 0)
                    {
                        $q_idat->andWhere('ph.ipid IS NOT NULL');
                    }
                    
                    
                    if(strlen($sql_active_not_standby[$gr_id]) > 0)
                    {
                        $q_idat->andWhere($sql_active_not_standby[$gr_id]);
                    } 
                    
                    
                    
//                     if(strlen($sql_standby[$gr_id]) > 0)
//                     {
//                         $q_idat->andWhere($sql_standby[$gr_id]);
//                     } 
                    
                    if( ! is_null($sql_standby[$gr_id]))
                    {
                    	if ( $sql_standby_negate[$gr_id] ) {
                    		$q_idat->leftJoin($sql_standby[$gr_id]);
                    		$q_idat->andWhere('ps.ipid IS NULL');
                    	} else {
                    		$q_idat->innerJoin($sql_standby[$gr_id]);
                    	}
                    	
                    }
                    
                    
                    
                    if(strlen($icd_specific[$gr_id]) > 0)
                    {
                        $q_idat->andWhere('p.ipid IN ('.substr($icd_patients_str,0,-1).')  ');
                    }
                    
                    if(strlen($course_search_string[$gr_id]) > 0)
                    {
                    	if($sql_course_negate[$gr_id] == "1"){
	                        $q_idat->andWhere('p.ipid NOT IN ('.substr($patient_course_ipids_str,0,-1).')  ');
                    	} else{
	                        $q_idat->andWhere('p.ipid IN ('.substr($patient_course_ipids_str,0,-1).')  ');
                    	}
                    }
                    
                    $q_idat->andWhere('p.isdelete = 0');
                    $q_idat->andWhere('p.isstandbydelete = 0');
                    $q_idat->orderBy('e.ipid ASC');
                    
//                     echo $q_idat->getSqlQuery();
                    $patients[$gr_id]  = $q_idat->fetchArray();
                }
//             exit;    

                
                if(in_array("teammeeting", $gr_data['search_types'])){
                    
                    $teammeeting_q = Doctrine_Query::create();
                    $teammeeting_q->select('t.*,at.*');
                    $teammeeting_q->from('TeamMeeting t INDEXBY t.id');
                    $teammeeting_q->leftJoin('t.TeamMeetingAttendingUsers at ON at.meeting = t.id AND at.client ="'.$clientid.'"   INDEXBY at.id');
                    $teammeeting_q->where('client = '.$clientid);
                    if( strlen($teammeeting_sql[$gr_id]) > 0){
                        $teammeeting_q->andWhere('' . substr($teammeeting_sql[$gr_id], 0, -4) . '');
                    }
                    $teammeeting_q->andWhere('at.id IS NOT NULL');
                    $teammeeting_result[$gr_id] = $teammeeting_q->fetchArray();
                }
                
                
                if(in_array("member", $gr_data['search_types'])){
                    
                    if($member_membership_sql_str[$gr_id])
                    {
                        $member_membership_sql[$gr_id] = 'AND (' . substr($member_membership_sql_str[$gr_id], 3) . ')    ';
                    }
                    
                    
                    if(strlen($member_membership_end_sql_str[$gr_id]) > 0 ){
                        $member_membership_end_sql[$gr_id] = ' AND '.substr($member_membership_end_sql_str[$gr_id], 0, -4);
                    }
                    
                    //$member_select_str = "m.*,IF(m.gender != 0,IF(m.gender = 1, 'männlich','weiblich'),'keine Angabe'  )  as gender";
                    //ISPC-2442 @Lore   30.09.2019
                    $member_select_str = "m.*,IF(m.gender != 0,IF(m.gender = 1, 'männlich',IF(m.gender = 2, 'weiblich','keine Angabe')), 'divers'  )  as gender";
                    $member_select_str .= ",md.*";
                    $member_select_str .= ",m2m.*";
                    
                    $member_q = Doctrine_Query::create();
                    $member_q ->select($member_select_str);
                    $member_q ->from('Member m');
                    $member_q ->where('m.clientid = '.$clientid);

                    $member_q ->leftJoin('m.Member2Memberships m2m ON m2m.member = m.id AND m2m.clientid = "'.$clientid.'" AND m2m.isdelete = 0   '.$member_membership_sql[$gr_id] .'   '.$member_membership_end_sql[$gr_id].'');
                    if(strlen($member_membership_sql[$gr_id]) > 0 || strlen($member_membership_end_sql[$gr_id])> 0 ){
                        $member_q->andWhere('m2m.id IS NOT NULL');
                    }
//                     if(strlen($member_membership_end_sql[$gr_id])>0){
//                         $member_q->andWhere(" 22222222 " . substr($member_membership_end_sql[$gr_id], 0, -4)."");
//                     }
                   
                    
                    
                    $member_q ->leftJoin('m.MemberDonations md ON md.member = m.id AND md.clientid = "'.$clientid.'" AND md.isdelete = 0');
                    if(strlen($member_donations_sql[$gr_id])>0){
                        $member_q->andWhere("" . substr($member_donations_sql[$gr_id], 0, -4)."");
                        if(strlen($custom_added_member_donation_q[$gr_id]) > 0 ){
                            $member_q->andWhere($custom_added_member_donation_q[$gr_id]);
                        }
                        $member_q->andWhere('md.id IS NOT NULL');
                    }
                    
//      echo $member_q->getSqlQuery();
//                     exit;
                    $member_result[$gr_id] = $member_q ->fetchArray();
                }
            } /* GROUPS FOREACH END */ 
            

            
         //   print_r($patients); exit;
            
            if($report['details']['group_operator'] == "andg")
            {
                // TEAM MEETINGS : intersect NOT applied
                foreach($teammeeting_result as $t_group_id =>$t_gr_values){
                    foreach($t_gr_values as $k=>$team_data){
                        $team_meetings_ids[] = $team_data['id'];
                    }
                }
                
                //MEMBERS : intersect NOT applied
                foreach($member_result as $m_group_id =>$m_gr_values){
                    foreach($m_gr_values as $km=>$member_data){
                        $member_details[] = $member_data;
                    }
                }
            	//TODO-2986 Ancuta 09.03.2020 - add condition to intersect ONLY if there are more den oane search groups and  operant "AND" is selected!
            	if(count($report['groups']) > 1){
                	// PATIENTS: intersect applied
                	foreach($patients as $p_gr_id => $patd)
                	{
                		foreach($patd as $k=>$pipid)
                		{
                			$patients_ipids_groups[$p_gr_id][] = $pipid['ipid'];
                		}
                	}
                	if(count($patients_ipids_groups) > 1) {
    	            	$group_patients_ipids_keys = call_user_func_array('array_intersect',$patients_ipids_groups);  
    	            	$group_patients_ipids = array_values($group_patients_ipids_keys);  
                	} else {
                		$group_patients_ipids = array();
                	}            	
	
            	} else {
            	    
            	    foreach($patients as $p_gr_id => $patd)
            	    {
            	        foreach($patd as $k=>$pipid)
            	        {
            	            $group_patients_ipids[] = $pipid['ipid'];
            	        }
            	    }
            	    
            	}
            	
            	
            }
            else
            { // OR operator between groups
            	
            	// TEAM MEETINGS
	            foreach($teammeeting_result as $t_group_id =>$t_gr_values){
	                foreach($t_gr_values as $k=>$team_data){
	                    $team_meetings_ids[] = $team_data['id']; 
	                }
	            }
	            
	            //MEMBERS
	            foreach($member_result as $m_group_id =>$m_gr_values){
	                foreach($m_gr_values as $km=>$member_data){
	                    $member_details[] = $member_data; 
	                }
	            }
	            
	            // PATIENTS
	            foreach($patients as $p_gr_id => $patd)
	            {
	                foreach($patd as $k=>$pipid)
	                {
	                    if(!in_array($pipid['ipid'],$group_patients_ipids))
	                    {
	                        $group_patients_ipids[] = $pipid['ipid'];
	                    }
	                    $patients_ipids[$p_gr_id][] = $pipid['ipid'];
	                }
	            }
	               
            }
        } /* GROUP CONDITION END */
//        print_r($patients_ipids); 
//        exit;
// print_r($member_details); exit;
        if(sizeof($group_patients_ipids) > 0)
        {
            $patients_ipids_array = array_values(array_intersect( $patients_ipids[0],$group_patients_ipids));
        }

        if(empty($patients_ipids_array)){
            $patients_ipids_array[] = "999999999999999";
        }
        
        foreach($report['columns'] as $kk => $col_value ){
            $reports_column_names[$col_value['search_type']][] = $col_value['column'];            
            $overall_reports_column_names[] = $col_value['column'];            
        }

        //GET CLIENT FAMILY DOCTORS
        if(in_array("family_doctor",$overall_reports_column_names) || in_array("family_doctor_data",$overall_reports_column_names))
        {
            
             $client_familidoc_array = FamilyDoctor::getFamilyDoctors(false, false, false, false,true);
             
             foreach($client_familidoc_array as $fd_key => $fd_value)
             {
                 $client_family_doctors[$fd_value['id']]['name'] = "";
                 $client_family_doctors[$fd_value['id']]['full_data'] = "";
                 $client_family_doctors[$fd_value['id']]['address'] = "";
    
                 if(strlen($fd_value['title']) > 0 ){
                     $client_family_doctors[$fd_value['id']]['name'] = $fd_value['title'];
                     $client_family_doctors[$fd_value['id']]['full_data'] = $fd_value['title'];
                     $client_family_doctors[$fd_value['id']]['address'] = $fd_value['title'];
                 }
                 
                 if(strlen($fd_value['last_name']) > 0 || strlen($fd_value['first_name']) > 0){
                     $client_family_doctors[$fd_value['id']]['name'] .= $fd_value['last_name'] . ", " . $fd_value['first_name'] . " ";
                     $client_family_doctors[$fd_value['id']]['full_data'] .= $fd_value['last_name'] . ", " . $fd_value['first_name'] . " ";
                     $client_family_doctors[$fd_value['id']]['address'] .= $fd_value['last_name'] . ", " . $fd_value['first_name'] . " ";
                 }
                 
                 if(strlen($fd_value['phone_practice']) > 0)
                 {
                     $client_family_doctors[$fd_value['id']]['full_data'] .= $fd_value['phone_practice'] . ", ";
                 }
                 
                 if(strlen($fd_value['street1']) > 0)
                 {
                     $client_family_doctors[$fd_value['id']]['full_data'] .='<br />'. $fd_value['street1'] . ", ";
                     $client_family_doctors[$fd_value['id']]['address'] .='<br />'. $fd_value['street1'] . ", ";
                 }
                 
                 if(strlen($fd_value['zip']) > 0)
                 {
                     $client_family_doctors[$fd_value['id']]['full_data'] .= '<br />'.$fd_value['zip'] . ", ";
                     $client_family_doctors[$fd_value['id']]['address'] .= '<br />'.$fd_value['zip'] . ", ";
                 }
                 if(strlen($fd_value['city']) > 0)
                 {
                     $client_family_doctors[$fd_value['id']]['full_data'] .= $fd_value['city'];
                     $client_family_doctors[$fd_value['id']]['address'] .= $fd_value['city'];
                 }
             }
        }
        //GET CLIENT MEMBERSHIPS
        if(in_array("member_membership",$overall_reports_column_names)  )
        {
             $client_memberships_array = Memberships::get_memberships($clientid);
             
             foreach($client_memberships_array as $cm_key => $cm_value)
             {
                 $client_memberships[$cm_value['id']] = $cm_value;
             }
             
             $client_memberships_end_array = MemberMembershipEnd::get_list($clientid,0);
             foreach($client_memberships_end_array as $cm_key => $cm_value)
             {
                 $client_memberships_end[$cm_value['id']] = $cm_value['description'];
             }
             
        }
      
        
        // ENGAGE (19.03.2018)
        $resulted_data = array();
        
        
        $sapvsql_data="";
        foreach($report['columns'] as $kk => $col_value )
        {
            foreach($patients_ipids_array as $pk =>$pid)
            {
                if($pid != "999999999999999")
                {
                    
                    if($col_value['column_type'] != "o")
                    {
                        $resulted_data[$pid][$col_value['column']] = "";
                    }
                }
            }
        }
        
        //CLIENT VOLUNTARYWORKERS
        if(in_array("voluntaryworker",$overall_reports_column_names)  )
        {
        	// get associated clients of current clientid START
        	$connected_client = VwGroupAssociatedClients::connected_parent($clientid);
        	if($connected_client){
        		$clientid_vw = $connected_client;
        	} else{
        		$clientid_vw = $clientid;
        	}
        
        	$clvw = Doctrine_Query::create()
        		->select("*")
        		->from('Voluntaryworkers')
        		->where('clientid =?',$clientid_vw);
        	$clvwarray = $clvw->fetchArray();
        
        	foreach($clvwarray as $val)
        	{
        		$voluntaryworker[$val['id']]['id'] = $val['id'];
        		$voluntaryworker[$val['id']]['name'] = $val['last_name'].' '.$val['first_name'];
       		}
        }
  //print_r($voluntaryworker);exit;      
        foreach($report['columns'] as $kk => $col_value )
        {
            switch ($col_value['column']) 
            {
                case "patient_nr":
                case "surname": 
                case "firstname": 
                case "birthd":
                case "zip":
                case "city": 
                case "street": 
                case "gender": 
                case "age":
                case "day_of_admission":
                case "family_doctor":
                case "family_doctor_data":
                case "contact_phone_number":
                case "active_location":
                case "patient_street_zip_city": // ISPC-1723   
                
                    $patient_sql_str = "p.ipid,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as firstname";
                    $patient_sql_str .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as surname";
                    $patient_sql_str .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street";
                    $patient_sql_str .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
                    $patient_sql_str .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
                    $patient_sql_str .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
                    $patient_sql_str .= ",AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') as  contact_phone_number";
                    $patient_sql_str .= ",kontactnumbertype as kontactnumbertype";
                    $patient_sql_str .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
                    $patient_sql_str .= ",IF(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') != 0,IF(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') = 1, 'männlich','weiblich'),'keine Angabe'  )  as gender";
                    $patient_sql_str .= ",IF(admission_date != '0000-00-00',DATE_FORMAT(admission_date,'%d\.%m\.%Y'),'') as day_of_admission";
                    $patient_sql_str .= ",IF(birthd != '0000-00-00',DATE_FORMAT(birthd,'%d\.%m\.%Y'),'') as birthd";
                    $patient_sql_str .= ',(YEAR(NOW()) - YEAR(birthd)) as age';
                    $patient_sql_str .= ",e.epid as patient_nr";
                    $patient_sql_str .= ",e.epid,e.ipid";
                    $patient_sql_str .= ",p.familydoc_id as family_doctor";
                    $patient_sql_str .= ",p.familydoc_id as family_doctor_data";
                    
                    $patients_query = Doctrine_Query::create()
                        ->select($patient_sql_str)
                        ->from('PatientMaster p INDEXBy p.ipid')
                        ->Where('p.isdelete = 0')
                        ->andWhereIn('p.ipid',$patients_ipids_array);
                    $patients_query->leftJoin("p.EpidIpidMapping e ON e.ipid = p.ipid INDEXBY e.ipid");
                    
                    //ISPC-2045
                    $patients_query->leftJoin("p.PatientContactphone pcp ON p.ipid = pcp.ipid");
                    $patients_query->addSelect("pcp.*");
                    
                    $patients_query->orderBy('CONVERT(AES_DECRYPT(p.last_name, "' . Zend_Registry::get('salt') . '") using utf8) COLLATE utf8_general_ci ASC');
                    $patients_query_arr = $patients_query->fetchArray();

                    
                    foreach($patients_query_arr as $pipid =>$pdata)
                    {
                        $patient_data[$pipid] = $pdata;
                        if($col_value['column'] == "family_doctor")
                        {
                           $resulted_data[$pipid][$col_value['column']] = $client_family_doctors[$pdata[$col_value['column']]]['name'];
                        } 
                        else  if($col_value['column'] == "family_doctor_data")
                        {
                           $resulted_data[$pipid][$col_value['column']] = $client_family_doctors[$pdata[$col_value['column']]]['full_data'];
                        }
                        elseif ( $col_value['column'] == "contact_phone_number" && ! empty($pdata['PatientContactphone']) ) {

                        	//ISPC-2045
                        	$PatientContactphone = array_column($pdata['PatientContactphone'], 'phone_number');
                        	$resulted_data[$pipid][$col_value['column']] = implode("; ", $PatientContactphone);
                        } 
                        else
                        {
                           $resulted_data[$pipid][$col_value['column']] = $pdata[$col_value['column'] ];
                           
                           if($col_value['column'] == "age")
                           {
                                $extra_data[$col_value['column']][] = $pdata[$col_value['column'] ];
                           }
                           
                            if($col_value['column'] == "patient_street_zip_city")
                            {
                                //$resulted_data[$pipid][$col_value['column']]   =  $pdata['street']  . '<br />' . $pdata['zip'] . ' ' . $pdata['city'];
                                // TODO-2955 Lore 26.02.2020 / TODO-2980 Lore 06.03.2020
                                $resulted_data[$pipid][$col_value['column']]   =  $pdata['street']  .(!empty($pdata['street']) ? ', ' : '' ). $pdata['zip'] . (!empty($pdata['zip']) ? ', ' : ' ' ) . $pdata['city'];
                            }
                           
                        }
                    }
                    
                    
                    if($col_value['column'] == "active_location")
                    {
                      
                        $contact = new ContactPersonMaster();
                        $contactpersons_loc_array = $contact->get_contact_persons_by_ipids($patients_ipids_array, false, false);
                        
                        $patloc = Doctrine_Query::create()
                        ->select('*')
                        ->from('PatientLocation')
                        ->whereIn('ipid', $patients_ipids_array)
                        ->andWhere('isdelete="0"')
                        ->andWhere("valid_till='0000-00-00 00:00:00'")
                        ->orderBy('id DESC');
                         $patlocarray = $patloc->fetchArray();
                        
                        if($patlocarray)
                        {
                            foreach($patlocarray as $k_loc => $v_loc)
                            {
                                $locid = substr($v_loc['location_id'], 0, 4);
                                if($locid == "8888")
                                {
                                    $patient_location_id = $v_loc['location_id'];
                                    $z = 1;
                                    $cnt_number = 1;
                                    foreach($contactpersons_loc_array[$v_loc['ipid']] as $k => $value_cnt)
                                    {
                                        if($value_cnt['isdelete'] == '0')
                                        {
                                            $pat_locarrayl[$v_loc['ipid']]['8888' . $z]['location_name'] = 'bei Kontaktperson ' . $cnt_number . ' (' . $value_cnt['cnt_last_name'] . ' ' . $value_cnt['cnt_first_name'] . ')';
                                            $pat_locarrayl[$v_loc['ipid']]['8888' . $z]['location_street'] = $value_cnt['cnt_street1'];
                                            $pat_locarrayl[$v_loc['ipid']]['8888' . $z]['location_zip'] = $value_cnt['cnt_zip'];
                                            $pat_locarrayl[$v_loc['ipid']]['8888' . $z]['location_city'] = $value_cnt['cnt_city'];
                                            $cnt_number++;
                                        }
                                        else
                                        {
                                            $pat_locarrayl[$v_loc['ipid']]['8888' . $z]['location_name'] = 'bei Kontaktperson ';
                                        }
                                        $z++;
                                    }
                                    $patlocarrayFinal[$v_loc['ipid']] = $pat_locarrayl[$v_loc['ipid']][$patient_location_id];
                                }
                                else
                                {
                                    $patlocarrayFinal[$v_loc['ipid']]['location_name'] = $client_location_details[$v_loc['location_id']]['location'];
                                    $patlocarrayFinal[$v_loc['ipid']]['location_type'] = $client_location_details[$v_loc['location_id']]['location_type'];
                                    
                                    if($patlocarrayFinal[$v_loc['ipid']]['location_type'] == "5")
                                    {
                                        $patlocarrayFinal[$v_loc['ipid']]['location_street'] = $patient_data[$v_loc['ipid']]['street'];
                                        $patlocarrayFinal[$v_loc['ipid']]['location_zip'] = $patient_data[$v_loc['ipid']]['zip'];
                                        $patlocarrayFinal[$v_loc['ipid']]['location_city'] = $patient_data[$v_loc['ipid']]['city'];
                                    }
                                    else
                                    {
                                        $patlocarrayFinal[$v_loc['ipid']]['location_street'] = $client_location_details[$v_loc['location_id']]['street'];
                                        $patlocarrayFinal[$v_loc['ipid']]['location_zip'] = $client_location_details[$v_loc['location_id']]['zip'];
                                        $patlocarrayFinal[$v_loc['ipid']]['location_city'] = $client_location_details[$v_loc['location_id']]['city'];
                                    }
                                }
                            }
                        }
                        
                        foreach($patlocarrayFinal as $pid => $loc_details)
                        {
                            $resulted_data[$pid][$col_value['column']] =  $loc_details['location_name'] . '<br />' . $loc_details['location_street'] . '<br />' . $loc_details['location_zip'] . ' ' . $loc_details['location_city'];
                        }
                    }
                    
                    break; 
     
                    
                case "discharge_date":
                case "discharge_method":
                case "discharge_location":
                    $patient_discharge_sql_str = "d.ipid,d.discharge_method,d.discharge_location";
                    $patient_discharge_sql_str .= ",IF(discharge_date != '0000-00-00',DATE_FORMAT(discharge_date,'%d\.%m\.%Y'),'') as discharge_date";
                    
                    $patients_discharge_query = Doctrine_Query::create()
                    ->select($patient_discharge_sql_str)
                    ->from('PatientDischarge d INDEXBY d.ipid')
                    ->whereIn('d.ipid', $patients_ipids_array)
                    ->andWhere('d.isdelete = "0"');
                    $patients_discharge_query_arr = $patients_discharge_query->fetchArray();
                    
                    foreach($patients_discharge_query_arr as $dipid =>$dis_data)
                    {
                        if($col_value['column'] == "discharge_method")
                        {
                            $resulted_data[$dipid][$col_value['column']] = $discharge_methods[$dis_data[$col_value['column']]];
                        }
                        elseif ($col_value['column'] == "discharge_location" && strlen($dis_data[$col_value['column']]) > 0)
                        {
                            $resulted_data[$dipid][$col_value['column']] = $discharge_locations[$dis_data[$col_value['column']]];
                        }
                        elseif($col_value['column'] == "discharge_date")
                        {
                            $resulted_data[$dipid][$col_value['column']] = $dis_data[$col_value['column']];
                        }
                    }
                    break;
                    
                case "treatment_days_overall":
                case "hospital_stays_overall":
                case "locations_overall":
                case "treatment_days_in_period":
                case "hospital_stays_in_period":
                case "locations_in_period":
                    
                    $pl_model =new PatientLocation();
                    $reasons = $pl_model->getReasons();
                    $hdocs = $pl_model->getHospDocs();
                    $transports = $pl_model->getTransports();
                    
                    
                    $sql = 'e.epid, p.ipid, e.ipid,';
                    $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
                    $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
                    $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
                    $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
                    $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
                    $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
                    
                    
                    if($col_value['column'] == "treatment_days_in_period" || $col_value['column'] == "hospital_stays_in_period" || $col_value['column'] == "locations_in_period"  )
                    {
                        $conditions_ov['periods'] = $report['period'];
                    } 
                    else
                    {
                        $conditions_ov['periods'] = array('0' => array('start' => "2007-01-01", 'end' => date("Y-m-d")));
                    }
                                        
                    $conditions_ov['include_standby'] = true;
                    $conditions_ov['client'] = $clientid;
                    $conditions_ov['ipids'] = $patients_ipids_array;
                    
                    $overall_patient_details = Pms_CommonData::patients_days($conditions_ov,$sql);

                    foreach ( $overall_patient_details as $tr_o_ipid => $tr_o_data)
                    {
                        if($col_value['column'] == "treatment_days_overall" || $col_value['column'] == "treatment_days_in_period")
                        {
                            $resulted_data[$tr_o_ipid][$col_value['column']] = $tr_o_data['treatment_days_no'];
                            $extra_data[$col_value['column']][] = $tr_o_data['treatment_days_no'];
                        } 
                        else if($col_value['column'] == "hospital_stays_overall" || $col_value['column'] == "hospital_stays_in_period")
                        {
                            
                            
                            $hso[$tr_o_ipid] = 0;
                            $ldata[$tr_o_ipid] = array();
                            foreach($tr_o_data['locations'] as $location_id_o =>$location_details_o)
                            {
                            	if(empty($resulted_data[$tr_o_ipid][$col_value['column']])){
//                             		$resulted_data[$tr_o_ipid][$col_value['column']] = array();
                            	}
                            	
                                if($location_details_o['type'] == "1")
                                {
//                                     $resulted_data[$tr_o_ipid][$col_value['column']][$hso]["lenght"] = count($location_details_o['days']);
//                                     $resulted_data[$tr_o_ipid][$col_value['column']][$hso]["period"] = $location_details_o['period']['start'] . ' -> '.$location_details_o['period']['end'];
//                                     $resulted_data[$tr_o_ipid][$col_value['column']][$hso]["reason"] = $reasons[$location_details_o['reason']];
//                                     $resulted_data[$tr_o_ipid][$col_value['column']][$hso]["hospdoc"] = $hdocs[$location_details_o['hospdoc']];
//                                     $resulted_data[$tr_o_ipid][$col_value['column']][$hso]["transport"] = $transports[$location_details_o['transport']];


                                	$location_text = "";
                                    $location_text .= count($location_details_o['days']);
                                    $location_text .= ' / '.$location_details_o['period']['start'] . ' -> '.$location_details_o['period']['end'];
                                    $location_text .=' / Grund der Aufnahme: '.$reasons[$location_details_o['reason']];
                                    $location_text .=' / Einweisender Arzt: '.$hdocs[$location_details_o['hospdoc']];
                                    $location_text .=' / Transportmittel: '.$transports[$location_details_o['transport']];
                                
                                    //TODO-3617 Ancuta 
                                    $ldata[$tr_o_ipid][] = $location_text;
                                    //$resulted_data[$tr_o_ipid][$col_value['column']][$hso[$tr_o_ipid]] = $location_text;
                                    //--
                                    $hso[$tr_o_ipid]++;
                                    
                                }
                            }
                            //TODO-3617
                            $resulted_data[$tr_o_ipid][$col_value['column']] = implode('<br /><hr/>',$ldata[$tr_o_ipid]);
                            //--
                        }
                        else if($col_value['column'] == "locations_overall" || $col_value['column'] == "locations_in_period")
                        {
                            $lo[$tr_o_ipid] = 0;
                            foreach($tr_o_data['locations'] as $location_id_o => $location_details_o)
                            {
                            	if( empty( $resulted_data[$tr_o_ipid][$col_value['column']] ) ){
                            		$resulted_data[$tr_o_ipid][$col_value['column']] = array();
                            	}
                            	if(empty($resulted_data[$tr_o_ipid][$col_value['column']][$lo[$tr_o_ipid]])){
//                             		$resulted_data[$tr_o_ipid][$col_value['column']][$lo[$tr_o_ipid]] = array();
                            	}
                                                   	
                                if($location_details_o['type'] == "5")
                                {
                                    $resulted_data[$tr_o_ipid][$col_value['column']][$lo[$tr_o_ipid]] = $client_location_details[$location_details_o['location_id']]['location'] . "<br />" . $tr_o_data['details']['street'] . "<br />" . $tr_o_data['details']['zip'] . " " . $tr_o_data['details']['city'];
                                    $lo[$tr_o_ipid]++;
                                } 
                                else
                                {
                                    if(!empty($client_location_details[$location_details_o['location_id']]['location']))
                                    {
                                        $resulted_data[$tr_o_ipid][$col_value['column']][$lo[$tr_o_ipid]] = $client_location_details[$location_details_o['location_id']]['location'] . "<br />" . $client_location_details[$location_details_o['location_id']]['street'] . "<br />" . $client_location_details[$location_details_o['location_id']]['zip'] . " " . $client_location_details[$location_details_o['location_id']]['city'];
                                        $lo[$tr_o_ipid]++;
                                    }
                                }
                            }
                        }
                    }
                    break;
                    
                case "health_insurance":
                case "is_health_insurance_private":
                case "is_privat_patient":
                // ISPC-1723    
                case "health_insurance_ik":
                case "health_insurance_number":
                case "health_insurance_patient_number":
                case "health_insurance_status":
                // ISPC 1739
                case "health_insurance_beneficiaries":
                    
                    $healtharray_q_res = array();
                    $Health = Doctrine_Query::create()
                    ->select("*,AES_DECRYPT(company_name,'" . Zend_Registry::get('salt') . "') as company_name,
                        AES_DECRYPT(insurance_status,'" . Zend_Registry::get('salt') . "') as health_insurance_status,
                        institutskennzeichen as health_insurance_ik,
                        kvk_no as health_insurance_number,
                        insurance_no as health_insurance_patient_number ")
                    ->from('PatientHealthInsurance ph')
                    ->wherein('ph.ipid',$patients_ipids_array);
                    $healtharray_q_res = $Health->fetchArray();
                    
                    $healtharray  = array();
                    foreach($healtharray_q_res as $hk=>$hdata){
                        $healtharray[$hdata['ipid']] = $hdata; 
                    }
                    
                    $company_ids = array();
                    foreach($healtharray as $ph_ipid=>$patient_health)
                    {
                        $company_ids[] = $patient_health['companyid'];
                    }
                    
                    
                    $hi_statuses = array("F"=>"Familienangehoerige", "M"=>"Mitglied","N"=>"Nicht GKV-Versicherte","R"=>"Rentner","S"=>"Summe MFR");
                    
                    $health_insurances_res  = array();
                    if(!empty($company_ids)){
                        $health_insurances = Doctrine_Query::create()
                        ->select("*")
                        ->from('HealthInsurance INDEXBY id')
                        ->where('isdelete = 0')
                        ->andWhereIn('id',$company_ids);
                        $health_insurances_res = $health_insurances->fetchArray();
                    }
                    
                    $private_companyes = array();
                    foreach($health_insurances_res as $h_id=>$h_data)
                    {
                        if($h_data['he_price_list_type'] == "privat")
                        {
                            $private_companyes[] = $h_id;
                        }
                        $hi_company[$h_id] = $h_data;
                    }
                    
                    foreach($healtharray as $ph_ipid=>$patient_health)
                    {
                        if($col_value['column'] == "is_privat_patient")
                        {
                            
                            if($patient_health['privatepatient'] == "1")
                            {
                                $resulted_data[$ph_ipid][$col_value['column']] = "Ja";
                            } 
                            else 
                            {
                                $resulted_data[$ph_ipid][$col_value['column']] = "Nein";
                            }
                        }
                        else if($col_value['column'] == "health_insurance")
                        {
                            $resulted_data[$ph_ipid][$col_value['column']] = $patient_health["company_name"];
                        }
                        
                        // ISPC-1723    
                        else if($col_value['column'] == "health_insurance_ik")
                        {
                            $resulted_data[$ph_ipid][$col_value['column']] = $patient_health["health_insurance_ik"];
                        }
                        else if($col_value['column'] == "health_insurance_number")
                        {
                            $resulted_data[$ph_ipid][$col_value['column']] = $patient_health["health_insurance_number"];
                        }
                        else if($col_value['column'] == "health_insurance_patient_number")
                        {
                            $resulted_data[$ph_ipid][$col_value['column']] = $patient_health["health_insurance_patient_number"];
                        }
                        else if($col_value['column'] == "health_insurance_status")
                        {
                            if($hi_statuses[$patient_health["health_insurance_status"]]){
                                $resulted_data[$ph_ipid][$col_value['column']] = $hi_statuses[$patient_health["health_insurance_status"]];
                             } 
                             else
                             {
                                $resulted_data[$ph_ipid][$col_value['column']] = "";
                             }
                        }
                        else if($col_value['column'] == "is_health_insurance_private")
                        {
                            if(in_array($patient_health['companyid'],$private_companyes))
                            {
                                $resulted_data[$ph_ipid][$col_value['column']] = "Ja";
                            } 
                            else
                            {
                                $resulted_data[$ph_ipid][$col_value['column']] = "Nein";
                            }
                        }
                        else if($col_value['column'] == "health_insurance_beneficiaries")
                        {
                            
                            if($patient_health['privatepatient'] == "1" )
                            {
                                if($patient_health['private_valid_contribution'] == "1" )
                                {
                                    $resulted_data[$ph_ipid][$col_value['column']] = "Ja";
                                } 
                                else 
                                {
                                    $resulted_data[$ph_ipid][$col_value['column']] = "Nein";
                                }
                            } 
                            else
                            {
                                $resulted_data[$ph_ipid][$col_value['column']] = "Nein";
                            }
                        }
                    }
                    break;
                    
    
                case "icd_main":
                case "icd_side":
                case "diagno_description_main":
                case "diagno_description_side":
                    $dg = new DiagnosisType();
                    $abb2 = "'HD','ND'";
                    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
                    
                    foreach($ddarr2 as $key => $valdia)
                    {
                        if($valdia['abbrevation'] == "HD")
                        {
                            $dtype['main'][] = $valdia['id'];
                        } 
                        else
                        {
                            $dtype['side'][] = $valdia['id'];
                        }
                        
                    	$diagnosis_type[] = $valdia['id'];
                    }
                    
                    if(empty($diagnosis_type))
                    {
                    	$diagnosis_type[] = "99999999999999999999999999";
                    }
                    $patdia = new PatientDiagnosis();
                    $dianoarray = $patdia->get_multiple_finaldata($patients_ipids_array, $diagnosis_type);
                    
                    foreach($dianoarray as $kg=>$diangosis)
                    {
                        foreach($dtype as $diag_tp =>$tpids){
                            if($col_value['column'] == "icd_main" || $col_value['column'] == "icd_side" )
                            {
                                if(in_array($diangosis['diagnosis_type_id'],$tpids))
                                {
                                    if(!in_array($diangosis['icdnumber'],$resulted_data[$diangosis['ipid']]['icd_'.$diag_tp]))
                                    {
                                    	if(empty($resulted_data[$diangosis['ipid']]['icd_'.$diag_tp])){
                                    		$resulted_data[$diangosis['ipid']]['icd_'.$diag_tp] = array();
                                    	}
                                    	
                                    	
                                        if(strlen($diangosis['icdnumber']) > 0 )
                                        {
                                            $resulted_data[$diangosis['ipid']]['icd_'.$diag_tp][] = $diangosis['icdnumber'];
                                        } 
                                        else
                                        {
                                            $resulted_data[$diangosis['ipid']]['icd_'.$diag_tp][] = "-";
                                        }
                                    }
                                }
                            } 
                            elseif($col_value['column'] == "diagno_description_main" || $col_value['column'] == "diagno_description_side" )
                            {
                                if(in_array($diangosis['diagnosis_type_id'],$tpids))
                                {
                                    if(!in_array($diangosis['diagnosis'],$resulted_data[$diangosis['ipid']]['diagno_description_'.$diag_tp]))
                                    {
                                    	if(empty($resulted_data[$diangosis['ipid']]['diagno_description_'.$diag_tp])){
                                    		$resulted_data[$diangosis['ipid']]['diagno_description_'.$diag_tp] = array();
                                    	}
                                    	
                                        $resulted_data[$diangosis['ipid']]['diagno_description_'.$diag_tp][] = $diangosis['diagnosis'];
                                    }
                                }
                                
                            }
                            
                        }
                    }
                    break;
                    
                        
                case "sapv_status":
                case "sapv_from_date":
                case "sapv_till_date":
                case "sapv_till_from_date_type":
                    $sapv_statuses = SapvVerordnung::getSapvRadios();
                    $sapv_verordnets = Pms_CommonData::getSapvCheckBox();
                    
                    foreach($report['period'] as $period)
                    {
                        $sql_data[] = "( ( DATE(`verordnungam`) BETWEEN '" . $period['start'] . "' AND '" . $period['end'] . "' OR DATE(`verordnungbis`) BETWEEN '" . $period['start'] . "' AND '" . $period['end'] . "') OR (DATE(`verordnungam`) <= '" . $period['start'] . "' AND  DATE(`verordnungbis`) >= '" . $period['end'] . "') )";
                    }
                    
                    $sql_str = implode(' OR ', $sql_data);
                    
                    $sapv = Doctrine_Query::create()
                    ->select("*")
                    ->from('SapvVerordnung')
                    ->where('isdelete = 0')
                    ->andWhereIn('ipid',$patients_ipids_array);
                    if($sql_data)
                    {
                        $sapv->andWhere($sql_str);
                    }
                    $sapv->andWhere('verordnet != ""')
                    ->orderBy('verordnungbis ASC');
                    $sapv_data = $sapv->fetchArray();
                    
                    //1spc-1867
                    $verordnetarray = Pms_CommonData::getSapvCheckBox(true);
                    foreach($sapv_data as $k_sapv => $v_sapv)
                    {
                        if($v_sapv['ipid'] != $current_ipid){
                            $ss = 0;
                            $sf = 0;
                            $st = 0;
                            $su = 0;
                        }
                        
                        
                        if(empty($resulted_data[$v_sapv['ipid']][$col_value['column']])){
                        	$resulted_data[$v_sapv['ipid']][$col_value['column']] = array();
                        }
                        
                        if($col_value['column'] == "sapv_status" )
                        {
                            $resulted_data[$v_sapv['ipid']][$col_value['column']][$ss] = $sapv_statuses[$v_sapv['status']];
                            
                        $ss++;
                        }
                        
                        if($col_value['column'] == "sapv_from_date" )
                        {
                            $resulted_data[$v_sapv['ipid']][$col_value['column']][$sf] = date('d.m.Y',strtotime($v_sapv['verordnungam']));
                        $sf++;
                        }
                        
                        if($col_value['column'] == "sapv_till_date" )
                        {
                            $resulted_data[$v_sapv['ipid']][$col_value['column']][$st] = date('d.m.Y',strtotime($v_sapv['verordnungbis']));
                        $st++;
                        }
                        if($col_value['column'] == "sapv_till_from_date_type" )
                        {
                        	$verordnet = explode(",", $v_sapv['verordnet']);
                        	$comma = '';
                        	if ($export_type == "screen"){
                        		$ver = '<br>';
                        	}else{
                        		$ver = "\n";
                        	}
                        	for($i = 0; $i < count($verordnet); $i++)
                        	{
                        		$ver .= $comma . $verordnetarray[$verordnet[$i]];
                        		$comma = ", ";
                        	}
                        	
                        	
                        	$resulted_data[$v_sapv['ipid']][$col_value['column']][$su] = date('d.m.Y',strtotime($v_sapv['verordnungam'])) . " - " . date('d.m.Y',strtotime($v_sapv['verordnungbis'])) . $ver;
                        $su++;
                        }
                        
                        
                        $current_ipid = $v_sapv['ipid'];
                    }
                    break;
                            
                case "treated_by":
                    $q_treated_by = Doctrine_Query::create();
                    $q_treated_by->select('e.*,q.*');
                    $q_treated_by->from('EpidIpidMapping e INDEXBY e.ipid');
                    $q_treated_by->leftJoin('e.PatientQpaMapping q ON e.epid = q.epid  INDEXBY q.id');
                    $q_treated_by->where('e.clientid= "'.$clientid.'" ');
                    $q_treated_by->andWhereIn('e.ipid',$patients_ipids_array);
                    $patients_tr  = $q_treated_by->fetchArray();
                    
                    foreach($patients_tr as $e_ipid => $treated_by_data)
                    {
                        foreach($treated_by_data['PatientQpaMapping'] as $lk =>$u_value)
                        {
                        	if(empty($resulted_data[$e_ipid][$col_value['column']])){
                        		$resulted_data[$e_ipid][$col_value['column']] = array();
                        	}
                        	
                            if($client_users_names[$u_value['userid']]){
                                $resulted_data[$e_ipid][$col_value['column']][] = $client_users_names[$u_value['userid']]['name'];
                            }
                        }
                    }
                    break;
                            
                case "memo_content":
                    $memos = new PatientMemo();
                    $patients_memo = $memos->get_multiple_patient_memo($patients_ipids_array);
                    
                    foreach($patients_memo as $memo_ipid => $memo_content)
                    {
                        $memo_data[$memo_ipid] = $memo_content;
                    }
                        
                    foreach($patients_ipids_array as $k=>$ipid){
                        if($memo_data[$ipid])
                        {
                            $resulted_data[$ipid][$col_value['column']] = $memo_data[$ipid];
                        } 
                        else
                        {
                            $resulted_data[$ipid][$col_value['column']] = "";
                        }
                    }
                    
                    break;
                            
                case "contact_person":
                case "quality_contact_person":
                case "grief_contact_person":
                case "voll_contact_person":
                    $contact = new ContactPersonMaster();
                    $contactpersons_array = $contact->get_contact_persons_by_ipids($patients_ipids_array, false, false);

                    foreach($contactpersons_array as $cnt_ipid =>$cnts)
                    {
                        
                        foreach($cnts as $k => $cnt_data)
                        {
                        	if(empty($resulted_data[$cnt_ipid][$col_value['column']])){
                        		$resulted_data[$cnt_ipid][$col_value['column']] = array();
                        	}
                            
                            if($col_value['column'] == "contact_person")
                            {
                                $resulted_data[$cnt_ipid][$col_value['column']][] = $cnt_data['cnt_last_name'].' '.$cnt_data['cnt_first_name'];
                            } 
                            
                            if($col_value['column'] == "quality_contact_person" )
                            {
                                if($cnt_data['quality_control'] == "1")
                                {
                                    $resulted_data[$cnt_ipid][$col_value['column']][] = $cnt_data['cnt_last_name'].' '.$cnt_data['cnt_first_name'];
                                }
                            }
                            
                            if($col_value['column'] == "grief_contact_person" )
                            {
                                if($cnt_data['notify_funeral'] == "1")
                                {
                                    $resulted_data[$cnt_ipid][$col_value['column']][] = $cnt_data['cnt_last_name'].' '.$cnt_data['cnt_first_name'];
                                }
                            }
                            
                            if($col_value['column'] == "voll_contact_person" )
                            {
                                if($cnt_data['cnt_hatversorgungsvollmacht'] == "1")
                                {
                                    $resulted_data[$cnt_ipid][$col_value['column']][] = $cnt_data['cnt_last_name'].' '.$cnt_data['cnt_first_name'];
                                }
                            }
                        }
                    }
                  
                    break;
                            
                case "nurse_services":
                    $patientpfle = Doctrine_Query::create()
                    ->select("*")
                    ->from('PatientPflegedienste p')
                    ->whereIn('p.ipid', $patients_ipids_array)
                    ->andwhere('p.isdelete = 0');
                    $patientpflearray = $patientpfle->fetchArray();
                    
                    foreach($patientpflearray as $k => $pval)
                    {
                    	if ( ! is_array($resulted_data[$pval['ipid']][$col_value['column']])) {
                    		$resulted_data[$pval['ipid']][$col_value['column']] = array();
                    	}
                        $resulted_data[$pval['ipid']][$col_value['column']][] = $pflegedienste[$pval['pflid']]['name'];
                    }
                    break;
                        
                    
                case "assigned_pharmacy":
                    $patientpfle = Doctrine_Query::create()
                    ->select("*")
                    ->from('PatientPharmacy p')
                    ->whereIn('p.ipid', $patients_ipids_array)
                    ->andwhere('p.isdelete = 0');
                    $patientpharray = $patientpfle->fetchArray();
                    
                    foreach($patientpharray as $k => $phval)
                    {
                    	if(empty($resulted_data[$phval['ipid']][$col_value['column']])){
                    		$resulted_data[$phval['ipid']][$col_value['column']] = array();
                    	}
                    	
                        $resulted_data[$phval['ipid']][$col_value['column']][] = $pharmacy[$phval['pharmacy_id']]['name'];
                    }
                    break;
    
                        
                case "care_level_data":
                    $patientstage = Doctrine_Query::create()
                    ->select("*")
                    ->from('PatientMaintainanceStage p')
                    ->whereIn('p.ipid', $patients_ipids_array)
                    ->andWhere('tilldate = "0000-00-00"')
                    ->orderby('id DESC');
                    $patientstagearray = $patientstage->fetchArray();
                    
                    foreach($patientstagearray as $stage)
                    {
                        $stagestr = "";
                        $stagestr .=$stage['stage'] . '';
                        
                        if($stage['erstantrag'] == 1)
                        {
                            $stagestr .="<br />Erstantrag";
                        }
                        if($stage['horherstufung'] == 1)
                        {
                            $stagestr .="<br />Höherstufung beantragt";
                        }
                        
                        $resulted_data[$stage['ipid']][$col_value['column']] = $stagestr;
                    }
                        break;
                            
                            
                case "active_medication":
                case "bedarf_medication":
                case "iv_medication":
                    $drugs = Doctrine_Query::create()
                    ->select('*')
                    ->from('PatientDrugPlan')
                    ->whereIn('ipid', $patients_ipids_array)
                    ->andWhere("isdelete = '0'")
                    ->andWhere("isnutrition = '0'");
                    
                    if($col_value['column'] == "iv_medication")
                    {
                        $drugs->andWhere("isivmed = '1'");
                    } else{
                        $drugs->andWhere("isivmed = '0'");
                    }
                    
                    if($col_value['column'] == "bedarf_medication")
                    {
                        $drugs->andWhere("isbedarfs = '1'");
                    } else{
                        $drugs->andWhere("isbedarfs = '0'");
                    }
                    
                    $drugs->andWhere("isschmerzpumpe = '0'")
                    ->andWhere("treatment_care = '0'")
                    ->andWhere("ispumpe = '0'")//ISPC-2833 Ancuta 26.02.2021
                    ->orderBy("id ASC");
                    $drugsarray = $drugs->fetchArray();
                
                    
                    foreach($drugsarray as $key => $drugp)
                    {
                        $master_meds[] = $drugp['medication_master_id'];
                    }
                    if(empty($master_meds))
                    {
                        $master_meds['999999999'] = 'XXXXX';
                    }
                    
                    $medic = Doctrine_Query::create()
                    ->select('*')
                    ->from('Medication')
                    ->whereIn("id", $master_meds);
                    $master_medication = $medic->fetchArray();
                    
                    foreach($master_medication as $k_medi => $v_medi)
                    {
                        $medications[$v_medi['id']] = $v_medi['name'];
                    }
                    
                    foreach($drugsarray as $key => $drugp)
                    {
                    	if(empty($resulted_data[$drugp['ipid']][$col_value['column']])){
                    		$resulted_data[$drugp['ipid']][$col_value['column']] = array();
                    	}
                    	
                        $resulted_data[$drugp['ipid']][$col_value['column']][] =$medications[$drugp['medication_master_id']] . ' | ' . $drugp['dosage'];
                    }
                    break;
                            
                                       
                 case "contact_forms_delegation":                //ISPC-2533 Lore 05.02.2020 
                 case "contact_forms_leistung_koordination":     //ISPC-2534 Lore 10.02.2020 
                 case "contact_forms_filled":     
                   
                     
                     
                    if ($col_value['column'] == "contact_forms_delegation")  {
                        foreach($report['period'] as $period)
                        {
                            $sql_cff_visit_data[] = "( date(`date`) BETWEEN '" . $period['start'] . "' AND '" . $period['end'] . "' )";
                        }
                        $sql_cff_str = implode(' OR ', $sql_cff_visit_data);
                    }
                    elseif ($col_value['column'] == "contact_forms_leistung_koordination") {
                        foreach($report['period'] as $period)
                        {
                            $sql_cff_visit_data[] = "( date(`date`) BETWEEN '" . $period['start'] . "' AND '" . $period['end'] . "' )";
                        }
                        $sql_cff_str = implode(' OR ', $sql_cff_visit_data);
                    }
                    else
                    {
                        foreach($report['period'] as $period)
                        {
                            $sql_cff_visit_data[] = "( `%date%` BETWEEN '" . $period['start'] . "' AND '" . $period['end'] . "' )";
                        }
                        $sql_cff_str = implode(' OR ', $sql_cff_visit_data);
                    }

                   
                    // GET DELETED CONTACT 
                    $deleted_cff = Doctrine_Query::create()
                    ->select("id,ipid,recordid,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
                    ->from('PatientCourse')
                    ->where('wrong = 1')
                    ->andWhereIn('ipid', $patients_ipids_array)
                    ->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
                    ->andWhere(" tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'" . '   ');
                    $deleted_cff_array = $deleted_cff->fetchArray();
                    
                    foreach($deleted_cff_array as $k_del_visit => $v_del_visit)
                    {
                        $del_cff_visits[$v_del_visit['tabname']][] = $v_del_visit['recordid'];
                    }
                    
                    if ($col_value['column'] == "contact_forms_delegation" || $col_value['column'] == "contact_forms_leistung_koordination"){
                        
                        $contact_form_f_q = Doctrine_Query::create()
                        ->select("*,c.ipid,c.id")
                        ->from("ContactForms c indexBy id")
                        ->whereIn('c.ipid', $patients_ipids_array)
                        ->andWhere('c.isdelete = 0')
                        ->andWhere('('. str_replace('date(`date`)', 'date(`billable_date`)', $sql_cff_str) .')' );
                        
                    }
                    else
                    {
                        $contact_form_f_q = Doctrine_Query::create()
                        ->select("*,c.ipid,c.id")
                        ->from("ContactForms c")
                        ->whereIn('c.ipid', $patients_ipids_array)
                        ->andWhere('c.isdelete = 0')
                        ->andWhere('('. str_replace('%date%', 'billable_date', $sql_cff_str) .')' );
                        
                    }                        

                    if(!empty($del_cff_visits['contact_form'])){
	                    $contact_form_f_q->andWhereNotIn('c.id', $del_cff_visits['contact_form']);
                    }
                    
                    
                    $contact_form_f_arr = $contact_form_f_q->fetchArray();

                   
                    if (!empty($contact_form_f_arr)){
                        
                        foreach($contact_form_f_arr as $key => $val){
                            $contact_form_ids_arr[] = $val['id'];
                        }
                        
                        $data_deleg = Doctrine_Query::create()
                        ->select('*')
                        ->from('FormBlockDelegation')
                        ->whereIn('contact_form_id', $contact_form_ids_arr)
                        ->andWhere("isdelete = '0'")
                        ->orderBy("id ASC");
                        $data_deleg_array = $data_deleg->fetchArray();
                        
                        
                        $data_deleg = array();
                        foreach($data_deleg_array as $k=>$ds){
                            $data_deleg[$ds['ipid']][] = $ds;
                        }
                        
                        if ( $col_value['column'] == "contact_forms_delegation"){
                            
                            foreach($patients_ipids_array as $ipid){
                                
                                if(!empty($data_deleg[$ipid])){
                                    foreach($data_deleg[$ipid] as $k=>$val ){
                                        $deleg_array = array();
                                        $deleg_array['date']                      = date('d.m.Y H:i',strtotime($contact_form_f_arr[$val['contact_form_id']]['start_date']));
                                        $deleg_array['medication_check_sgbv']     = $val['medication_check_sgbv'];
                                        $deleg_array['wound_care_sgbv']           = $val['wound_care_sgbv'];
                                        $deleg_array['catheter_replacement_sgbv'] = $val['catheter_replacement_sgbv'];
                                        $deleg_array['blood_collection_sgbv']     = $val['blood_collection_sgbv'];
                                        $deleg_array['inr_measurement_sgbv']      = $val['inr_measurement_sgbv'];
                                        $deleg_array['bz_measurement_sgbv']       = $val['bz_measurement_sgbv'];
                                        $deleg_array['injection_sgbv']            = $val['injection_sgbv'];
                                        $deleg_array['vaccination_sgbv']          = $val['vaccination_sgbv'];
                                        
                                        if(!is_array($resulted_data[$val['ipid']][$col_value['column']])){
                                            $resulted_data[$val['ipid']][$col_value['column']] = array();
                                        }
                                        
                                        $resulted_data[$val['ipid']][$col_value['column']][] = $deleg_array;
                                        $deleg_array = array();
                                    }
                                    
                                } else{
                                    
                                    $deleg_array = array();
                                    $deleg_array['date']                      = '-';
                                    $deleg_array['medication_check_sgbv']     = '-';
                                    $deleg_array['wound_care_sgbv']           = '-';
                                    $deleg_array['catheter_replacement_sgbv'] = '-';
                                    $deleg_array['blood_collection_sgbv']     = '-';
                                    $deleg_array['inr_measurement_sgbv']      = '-';
                                    $deleg_array['bz_measurement_sgbv']       = '-';
                                    $deleg_array['injection_sgbv']            = '-';
                                    $deleg_array['vaccination_sgbv']          = '-';
                                    if(!is_array($resulted_data[$ipid][$col_value['column']])){
                                        $resulted_data[$ipid][$col_value['column']] = array();
                                    }
                                    
                                    $resulted_data[$ipid][$col_value['column']][] = $deleg_array;
                                    $deleg_array = array();
                                    
                                }
                                
                            }
                            
                        }
                        //.
                      
                        //ISPC-2534 Lore 10.02.2020
                        $data_coord = Doctrine_Query::create()
                        ->select('*')
                        ->from('FormBlockCoordinatorActions')
                        ->whereIn('contact_form_id', $contact_form_ids_arr)
                        ->andWhereIn("ipid",$patients_ipids_array)
                        ->andWhere("isdelete = '0'")
                        ->orderBy("id ASC");
                        $data_coord_array = $data_coord->fetchArray();
                        
                        if($col_value['column'] == "contact_forms_leistung_koordination"){
                            
                            if (!empty($data_coord_array) ){
                                
                                $sel_sett = Doctrine_Query::create()
                                ->select('*')
                                ->from('FormBlocksSettings indexBy id')
                                ->where('clientid = ? ', $clientid )
                                ->andWhere('block = ?',"coordinator_actions")
                                ->andWhere('isdelete = 0');
                                $sel_sett_res = $sel_sett->fetchArray();
                                
                                $fb2ipid = array();
                                foreach($data_coord_array as $kye => $val){
                                    $fb2ipid[$val['ipid']][$val['contact_form_id']][$val['action_id']]=$val;
                                }
                                
                                
                                foreach ($patients_ipids_array as $ipid)
                                {
                                    $x_cf_id = 0; 
                                    if(!empty($fb2ipid[$ipid])){
                                        
                                        foreach($fb2ipid[$ipid] as $cf_id=>$actions )
                                        {
                                            //$x_cf_id = $x_cf_id + 1;
                                            foreach($sel_sett_res as $key_fbs => $val_fbs)
                                            {
                                                if(!is_array($resulted_data[$ipid][$col_value['column']])){
                                                    $resulted_data[$ipid][$col_value['column']] = array();
                                                }
                                                
                                                $resulted_data[$ipid][$col_value['column']][$x_cf_id]['date'] = date('d.m.Y H:i',strtotime($contact_form_f_arr[$cf_id]['start_date']));
                                                
                                                
                                                if( $val_fbs['form_item_class'] == 'ca_incoming_info_path' ){
                                                    if(!empty($actions[$key_fbs])){
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['hand_strength_training'] = $actions[$key_fbs]['hand_strength_training'];
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['pain_diary'] = $actions[$key_fbs]['pain_diary'];
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['sleep_diary'] = $actions[$key_fbs]['sleep_diary'];
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['incontinence_protocol'] = $actions[$key_fbs]['incontinence_protocol'];
                                                    } else{
                                                        
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['hand_strength_training'] = "-";
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['pain_diary'] = "-";
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['sleep_diary'] = "-";
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['incontinence_protocol'] = "-";
                                                    }
                                                }else{
                                                    if(!empty($actions[$key_fbs])){
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['receives_services'] = $actions[$key_fbs]['receives_services'];
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['is_requested'] = $actions[$key_fbs]['is_requested'];
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['redirected'] = $actions[$key_fbs]['redirected'];
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['informed'] = $actions[$key_fbs]['informed'];
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['informaction_commented'] = $actions[$key_fbs]['action_comment'];
                                                    } else{
                                                        
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['receives_services'] = "-";
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['is_requested'] = "-";
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['redirected'] = "-";
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['informed'] = "-";
                                                        $resulted_data[$ipid][$col_value['column']][$x_cf_id][$val_fbs['option_name']]['informaction_commented'] = "-";
                                                    }
                                                }
                                            }
                                            $x_cf_id ++;
                                        }
                                        
                                    } else{
                                        
                                        foreach($sel_sett_res as $key=>$val){
                                            
                                            if(!is_array($resulted_data[$ipid][$col_value['column']])){
                                                $resulted_data[$ipid][$col_value['column']] = array();
                                            }
                                            
                                            $resulted_data[$ipid][$col_value['column']][0]['date'] = '-';
                                            if($val['form_item_class'] == 'ca_state'){
                                                $resulted_data[$ipid][$col_value['column']][0][$val['option_name']]['receives_services'] = '-';
                                                $resulted_data[$ipid][$col_value['column']][0][$val['option_name']]['is_requested'] = '-';
                                                $resulted_data[$ipid][$col_value['column']][0][$val['option_name']]['redirected'] = '-';
                                                $resulted_data[$ipid][$col_value['column']][0][$val['option_name']]['informed'] = '-';
                                                $resulted_data[$ipid][$col_value['column']][0][$val['option_name']]['informaction_commented'] = '-';
                                                
                                            }else{
                                                $resulted_data[$ipid][$col_value['column']][0][$val['option_name']]['hand_strength_training'] = '-';
                                                $resulted_data[$ipid][$col_value['column']][0][$val['option_name']]['pain_diary'] = '-';
                                                $resulted_data[$ipid][$col_value['column']][0][$val['option_name']]['sleep_diary'] = '-';
                                                $resulted_data[$ipid][$col_value['column']][0][$val['option_name']]['incontinence_protocol'] = '-';
                                            }
                                        }
                                        
                                    }
                                    
                                }
                                
                                
                            } else {
                                
                                $sel_sett = Doctrine_Query::create()
                                ->select('*')
                                ->from('FormBlocksSettings indexBy id')
                                ->where('clientid = ? ', $clientid )
                                ->andWhere('block = ?',"coordinator_actions")
                                ->andWhere('isdelete = 0');
                                $sel_sett_res = $sel_sett->fetchArray();
                                
                                $coord_array = array();
                                $coord_array['date']                      = '-';
                                foreach ($patients_ipids_array as $ipid){
                                    foreach($sel_sett_res as $key=>$val){
                                        if($val['form_item_class'] == 'ca_state'){
                                            $coord_array[$val['option_name']]['receives_services'] = '-';
                                            $coord_array[$val['option_name']]['is_requested'] = '-';
                                            $coord_array[$val['option_name']]['redirected'] = '-';
                                            $coord_array[$val['option_name']]['informed'] = '-';
                                            $coord_array[$val['option_name']]['informaction_commented'] = '-';
                                            
                                        }else{
                                            $coord_array[$val['option_name']]['hand_strength_training'] = '-';
                                            $coord_array[$val['option_name']]['pain_diary'] = '-';
                                            $coord_array[$val['option_name']]['sleep_diary'] = '-';
                                            $coord_array[$val['option_name']]['incontinence_protocol'] = '-';
                                        }
                                    }
                                    
                                    $resulted_data[$ipid][$col_value['column']][0] = $coord_array;
                                }
                                
                            }
                            
                        }
                        //.
                        
                        
                        if ( $col_value['column'] != "contact_forms_delegation"  && $col_value['column'] != "contact_forms_leistung_koordination" ){
                            foreach($contact_form_f_arr as $cf => $value_cf)
                            {
                                $resulted_data[$value_cf['ipid']][$col_value['column']] += "1";
                            }
                        }
                        
                    }

                    

                    break;
                        
                        
                case "xt_amount_and_time":
                case "u_minutes":
                case "v_minutes":
                case "contacts_per_day":
                case "xt_amount_and_time_in_period"://TODO-2579 Loradana+Ancuta 08.10.2019
                    foreach($report['period'] as $period)
                    {
                        $sql_pc_data[] = "( date(`done_date`) BETWEEN '" . $period['start'] . "' AND '" . $period['end'] . "' )";
                        $sql_visit_data[] = "( `%date%` BETWEEN '" . $period['start'] . "' AND '" . $period['end'] . "' )";
                    }
                    
                    $sql_pc_str = implode(' OR ', $sql_pc_data);
                    $sql_visit_str = implode(' OR ', $sql_visit_data);
                    
                    $previleges = new Modules();
                    $modulepriv = $previleges->checkModulePrivileges("55", $clientid);
                    $modulepriv_bay = $previleges->checkModulePrivileges("60", $clientid);
                    
                    if($modulepriv || $modulepriv_bay)
                    {
                        $lnrquery = " OR AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'XT'";
                    }
                    else
                    {
                        $lnrquery = "";
                    }
                    
                    $course_sql = Doctrine_Query::create()
                        ->select("ipid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,create_user,	done_date,date(done_date) as done_day,")
                        ->from('PatientCourse')
                        ->whereIn('ipid', $patients_ipids_array)
                        ->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'U' OR AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'V'  " . $lnrquery . "");
                        if( $col_value['column'] != "xt_amount_and_time") // xt search for overall amount and time
                        {
                            $course_sql->andWhere('(' . $sql_pc_str . ' )	');
                        }
                        $course_sql->andWhere("wrong = 0")
                        ->andWhere('source_ipid = ""')
                        ->orderBy('done_date ASC');
                    $course_sql_array = $course_sql->fetchArray();
                    
                    
                    foreach($course_sql_array as $course_key => $course_val)
                    {
                        $done_date_Ymd = date('Y-m-d',strtotime($course_val['done_date']));
                        $coursearr = explode("|", $course_val['course_title']);
                        $consulting_array[ $course_val['ipid'] ][] = $course_val['course_title'];
                        
                        if($col_value['column'] == "u_minutes" && strtolower($course_val['course_type']) == "u" )
                        {
                            if(count($coursearr) == 3)
                            { //method implemented with 3 inputs
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type'])]['minutes'] +=intval($coursearr[0]);
                            }
                            else if(count($coursearr) != 3 && count($coursearr) < 3)
                            { //old method before anlage 10
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type'])]['minutes'] +=intval($coursearr[0]);
                            }
                            else if(count($coursearr) != 3 && count($coursearr) > 3)
                            { //new method (U) 3 inputs and 1 select newly added in verlauf
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type'])]['minutes'] +=intval($coursearr[1]);
                            }
                        } 
                        elseif($col_value['column'] == "v_minutes" && strtolower($course_val['course_type']) == "v" )
                        {
                            if(count($coursearr) == 3)
                            { //method implemented with 3 inputs
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type'])]['minutes'] +=intval($coursearr[0]);
                            }
                            else if(count($coursearr) != 3 && count($coursearr) < 3)
                            { //old method before anlage 10
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type'])]['minutes'] +=intval($coursearr[0]);
                            }
                            else if(count($coursearr) != 3 && count($coursearr) > 3)
                            { //new method (U) 3 inputs and 1 select newly added in verlauf
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type'])]['minutes'] +=intval($coursearr[1]);
                            }
                        }
                        elseif($col_value['column'] == "xt_amount_and_time" && strtolower($course_val['course_type']) == "xt" )
                        {//TODO-2579 Loradana+Ancuta 08.10.2019
                            if(count($coursearr) == 3)
                            { //method implemented with 3 inputs
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type'])]['minutes'] +=intval($coursearr[0]);
                            }
                            else if(count($coursearr) != 3 && count($coursearr) < 3)
                            { //old method before anlage 10
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type'])]['minutes'] +=intval($coursearr[0]);
                            }
                            else if(count($coursearr) != 3 && count($coursearr) > 3)
                            { //new method (U) 3 inputs and 1 select newly added in verlauf
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type'])]['minutes'] +=intval($coursearr[1]);
                            }

                        }
                        elseif($col_value['column'] == "xt_amount_and_time_in_period" && strtolower($course_val['course_type']) == "xt" )
                        {
                            if(count($coursearr) == 3)
                            { //method implemented with 3 inputs
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type']).$col_value['column']]['minutes'] +=intval($coursearr[0]);
                            }
                            else if(count($coursearr) != 3 && count($coursearr) < 3)
                            { //old method before anlage 10
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type']).$col_value['column']]['minutes'] +=intval($coursearr[0]);
                            }
                            else if(count($coursearr) != 3 && count($coursearr) > 3)
                            { //new method (U) 3 inputs and 1 select newly added in verlauf
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type']).$col_value['column']]['minutes'] +=intval($coursearr[1]);
                            }
                        }
                        
                        
                        
                        if($course_val['course_type'] == "XT"){
                            if($col_value['column'] == "xt_amount_and_time_in_period"){
                                //TODO-2579 Loradana+Ancuta 08.10.2019
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type']).$col_value['column'] ]['amount'] += 1;
                            } else{
                                $course_data[$course_val['ipid']][strtolower($course_val['course_type'])]['amount'] += 1;
                                
                            }
                        }
                        
                        
                        if($course_val['course_type'] == "XT" && $col_value['column'] == "contacts_per_day"){
                            $phone_cals[$course_val['ipid']][$done_date_Ymd] +=1;
                            $contacts_per_day[$course_val['ipid']][$done_date_Ymd] +=1;
                            
                            $overall_contacts[$course_val['ipid']] +=1 ;
                        }
                    }
                    
                    foreach($course_data as $course_ipid => $cs_data)
                    {
                        foreach($cs_data as $csh =>$csh_data)
                        {
                            if($col_value['column'] == "u_minutes" && $csh == "u" )
                            {
                                $resulted_data[$course_ipid][$col_value['column']] = $csh_data['minutes'];
                            } 
                            else if($col_value['column'] == "v_minutes" && $csh == "v" )
                            {
                                $resulted_data[$course_ipid][$col_value['column']] = $csh_data['minutes'];
                            }
                            else if($col_value['column'] == "xt_amount_and_time" && $csh == "xt" )
                            {
                                $resulted_data[$course_ipid][$col_value['column']] = $csh_data['amount'].' / '.$csh_data['minutes'];
                            }
                            else if($col_value['column'] == "xt_amount_and_time_in_period" && $csh == "xtxt_amount_and_time_in_period" )
                            {
                                $resulted_data[$course_ipid][$col_value['column']] = $csh_data['amount'].' / '.$csh_data['minutes'];
                            }
                        }
                    }
                    
                    if($col_value['column'] == "contacts_per_day" )
                    {
                        // GET active details of patients
                        $sql_ac = 'e.epid, p.ipid, e.ipid,';
                        $conditions_ac['periods'] = $report['period'];
                        $conditions_ac['client'] = $clientid;
                        $conditions_ac['ipids'] = $patients_ipids_array;
                        
                        $ac_patient_details = Pms_CommonData::patients_days($conditions_ac,$sql_ac);
                        
                        foreach ( $ac_patient_details as $ac_ipid => $ac_data)
                        {
                            $active_days_in_period[$ac_ipid]['list'] = $ac_data['active_days'];
                            $active_days_in_period[$ac_ipid]['count'] = count($ac_data['active_days']);
                        }
                            
                        // GET DELETED VISITS / CONTACTS 
                        $deleted_visits = Doctrine_Query::create()
                        ->select("id,ipid,recordid,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
                        ->from('PatientCourse')
                        ->where('wrong = 1')
                        ->andWhereIn('ipid', $patients_ipids_array)
                        ->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
                        ->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('visit_koordination_form')) . "'" . ' OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("kvno_doctor_form")) . '" OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("kvno_nurse_form")) . '"  OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("wl_doctor_form")) . '"  OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("wl_nurse_form")) . '"   OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("bayern_doctorvisit")) . '" OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("contact_form")) . '"  ');
                        $deleted_visits_array = $deleted_visits->fetchArray();
                        
                        $del_visits['kvno_doctor_form'][] = '999999999999';
                        $del_visits['kvno_nurse_form'][] = '999999999999';
                        $del_visits['wl_doctor_form'][] = '999999999999';
                        $del_visits['wl_nurse_form'][] = '999999999999';
                        $del_visits['visit_koordination_form'][] = '999999999999';
                        $del_visits['bayern_doctorvisit'][] = '999999999999';
                        $del_visits['contact_form'][] = '999999999999';
                        
                        foreach($deleted_visits_array as $k_del_visit => $v_del_visit)
                        {
                            $del_visits[$v_del_visit['tabname']][] = $v_del_visit['recordid'];
                        }
                    
                        // GET CONTACT FORMS
                        $contact_form_q = Doctrine_Query::create()
                        ->select("*,c.ipid,c.id")
                        ->from("ContactForms c")
                        ->whereIn('c.ipid', $patients_ipids_array)
                        ->andWhere('c.isdelete = 0')
                        ->andWhereNotIn('c.id', $del_visits['contact_form'])
                        ->andWhere('('. str_replace('%date%', 'billable_date', $sql_visit_str) .')' );
                        $contact_form_arr = $contact_form_q->fetchArray();
                        
                        foreach($contact_form_arr as $cf => $value_cf)
                        {
                            $visits[$value_cf['ipid']]['contact_form'][] = $value_cf;
                        }
                        
                        //GET KVNO DOCTOR VISITS
                        $kvno_doctor_visits = Doctrine_Query::create()
                        ->select("*")
                        ->from("KvnoDoctor")
                        ->whereIn('ipid', $patients_ipids_array)
                        ->andWhereNotIn('id', $del_visits['kvno_doctor_form'])
                        ->andWhere('('. str_replace('%date%', 'vizit_date', $sql_visit_str) .')' )
                        ->orderBy('vizit_date ASC');
                        $kvno_doctor_visits_arr = $kvno_doctor_visits->fetchArray();
                        
                        foreach($kvno_doctor_visits_arr as $doc => $value_doc)
                        {
                            $visits[$value_doc['ipid']]['doctor_visit'][] = $value_doc;
                        }
                        
                        // GET KVNO NURSE VISITS
                        $kvno_nurse_visits = Doctrine_Query::create()
                        ->select("*,  if( id <> 0, 'nurse_visit', '')  as type")
                        ->from("KvnoNurse")
                        ->whereIn('ipid', $patients_ipids_array)
                        ->andWhere('isdelete = 0')
                        ->andWhereNotIn('id', $del_visits['kvno_nurse_form'])
                        ->andWhere('('. str_replace('%date%', 'vizit_date', $sql_visit_str) .')' )
                        ->orderBy('vizit_date ASC');
                        $kvno_nurse_visits_arr = $kvno_nurse_visits->fetchArray();
                        
                        foreach($kvno_nurse_visits_arr as $nurse => $value_nurse)
                        {
                            $visits[$value_nurse['ipid']]['nurse_visit'][] = $value_nurse;
                        }
                    
                        // GET KVNO KOORDINATION VISITS
                        $kvno_koordination_visits = Doctrine_Query::create()
                        ->select("*,  if( id <> 0, 'koordination_visit', '')  as type")
                        ->from("VisitKoordination")
                        ->whereIn('ipid', $patients_ipids_array)
                        ->andWhereNotIn('id', $del_visits['visit_koordination_form'])
                        ->andWhere('('. str_replace('%date%', 'visit_date', $sql_visit_str) .')' )
                        ->orderBy('visit_date ASC');
                        $kvno_koordination_visits_arr = $kvno_koordination_visits->fetchArray();
                        
                        foreach($kvno_koordination_visits_arr as $koordination => $value_koordination)
                        {
                            $visits[$value_koordination['ipid']]['koordinator_visit'][] = $value_koordination;
                            
                        }
                        
                        //  GET BAYERN DOCTOR VISITS
                        $bayern_visits = Doctrine_Query::create()
                        ->select("*,  if( id <> 0, 'bayern_visit', '')  as type")
                        ->from("BayernDoctorVisit")
                        ->whereIn('ipid', $patients_ipids_array)
                        ->andWhereNotIn('id', $del_visits['bayern_doctorvisit'])
                        ->andWhere('('. str_replace('%date%', 'visit_date', $sql_visit_str) .')' )
                        ->orderBy('visit_date ASC');
                        $bayern_visits_arr = $bayern_visits->fetchArray();
                        
                        foreach($bayern_visits_arr as $bayern => $value_bayern)
                        {
                            $visits[$value_bayern['ipid']]['bayern_visit'][] = $value_bayern;
                        }
                        
                        foreach($visits as $v_ipid => $visit_types)
                        {
                            foreach($visit_types as $type =>$visits_data)
                            {
                                foreach ($visits_data as $vn =>$v_data)
                                {
                                    if($type == "doctor_visit" || $type == "nurse_visit")
                                    {
                                        $contact_date = date("Y-m-d",strtotime($v_data['vizit_date']));
                                    }
                                    else if($type == "koordinator_visit" || $type == "bayern_visit")
                                    {
                                        $contact_date = date("Y-m-d",strtotime($v_data['visit_date']));
                                        
                                    }
                                    else
                                    {
                                        $contact_date = date("Y-m-d",strtotime($v_data['billable_date']));
                                    }
                                    
                                   $contacts_per_day[$v_ipid][$contact_date] +=1 ;
                                   $overall_contacts[$v_ipid] +=1 ;
                                }
                            }
                        }
                        
                        foreach($overall_contacts as $oc_ipid => $oc_count)
                        {
                            //$resulted_data[$oc_ipid][$col_value['column']] =  round($oc_count / $active_days_in_period[$oc_ipid]['count'] , 2);;
                            $resulted_data[$oc_ipid][$col_value['column']] = $oc_count.' ('.round($oc_count / $active_days_in_period[$oc_ipid]['count'] , 2).'  pro Tag)'; // ISPC -1730, 15.06.2016 
                        }
                    }
                    break;
                            
                            
                            
                case "transferred_by";
                    $tr_patients_q = Doctrine_Query::create()
                        ->select("count(pm.ipid) as count, pm.referred_by")
                        ->from('PatientMaster pm')
                        ->whereIn('pm.ipid',$patients_ipids_array)
                        ->groupBy('pm.referred_by');
                        $tr_pat_array = $tr_patients_q->fetchArray();
                    
                        foreach($tr_pat_array as $refarr)
                        {
                            $total += $refarr['count'];
                        }
                    
                    $refarray = PatientReferredBy::getPatientReferredByreport($clientid, 0);
                    
                    foreach($refarray as $refname)
                    {
                        $refnamearray[$refname['id']] = $refname['referred_name'];
                    }
                    $refnamearray[0] = 'keine Angabe';
                    
                    foreach($tr_pat_array as $key => $refarr)
                    {
                        $resulted_data[$key]['referredby'] = $refnamearray[$refarr['referred_by']];
                        $resulted_data[$key]['count'] = $refarr['count'];
                        $resulted_data[$key]['percentage'] = round(($refarr['count'] / $total * 100), 2) . ' %';
                    }
                    
                    break;
                            
                case "discharge_statistics":
                    $patients_discharge_st_query = Doctrine_Query::create()
                    ->select("count(ipid) as count, discharge_location")
                    ->from('PatientDischarge ')
                    ->whereIn('ipid', $patients_ipids_array)
                    ->andWhere('isdelete = "0"')
                    ->groupBy('discharge_location');
                    $patients_discharge_st_query_arr = $patients_discharge_st_query->fetchArray();
                    
                    
                    foreach($patients_discharge_st_query_arr as $dis_arr)
                    {
                        $total += $dis_arr['count'];
                    }
                    $discharge_locations[0] = "Kein";
                    
                    foreach($patients_discharge_st_query_arr as $key =>$dis_st_data)
                    {
                        $resulted_data[$key]['discharge_location'] = $discharge_locations[$dis_st_data['discharge_location']];
                        $resulted_data[$key]['count'] = $dis_st_data['count'];
                        $resulted_data[$key]['percentage'] = round(($dis_st_data['count'] / $total * 100), 2) . ' %';
                    }
                    break;
    
                                
                case "meeting_atended_inperiod":
                    // GET CLIENT TEAM MEETINGS
                    $team_meeting_m = new TeamMeeting();
                    $client_team_meetings = $team_meeting_m->get_client_team_meetings_report_period($clientid, $report['period']);
                    
                    foreach($client_team_meetings as $meeting_id => $meeting_data)
                    {
                        $meetingids[] = $meeting_data['id'];
                        $meeting_details [$meeting_data['id']] = $meeting_data;
                    }
                    
                    // GET MEETING ATTENDING_USERS
                    $meeting_attending_users_array = TeamMeetingAttendingUsers::get_team_multiple_meetings_attending_users($meetingids, $clientid);
                    
                    foreach($meeting_attending_users_array as $k_mau => $v_mau)
                    {
                        $attended[$v_mau['user']]['last_name'] = $client_users[$v_mau['user']]['last_name'];
                        $attended[$v_mau['user']]['first_name'] = $client_users[$v_mau['user']]['first_name'];
                        $attended[$v_mau['user']]['meetings'][] = $v_mau['meeting'];
                    }
                    $ko= 0 ;
                    foreach($client_users as $user_id =>$att_user)
                    {
                        if($att_user['usertype'] != "SA")
                        {
                            $resulted_data[$ko]['lastname'] = $att_user['last_name'];
                            $resulted_data[$ko]['firstname'] = $att_user['first_name'];
                            
                            if(!empty($attended[$user_id]['meetings']))
                            {
                                $resulted_data[$ko]['attented_meetings'] = count($attended[$user_id]['meetings']);
                            } 
                            else
                            {
                                $resulted_data[$ko]['attented_meetings'] = "-";
                            }
                            $ko++;
                        }
                    }
                    break;
    

                case "member_first_name": //Vorname
                case "member_last_name": //Nachname
                case "member_title": //Titel
                case "member_company": //Firma / Institution
                case "member_birthd": //Geb.-Datum
                case "member_gender": //Geschlecht
                case "member_street1": //Straße
                case "member_street2": //Postzusatz
                case "member_zip": //PLZ
                case "member_city": //Stadt
                case "member_country": //Land
                case "member_phone": //Telefon
                case "member_mobile": //Mobiltelefon
                case "member_email": //Email Adresse
                case "member_website": //website
                case "member_status": //Status
                case "member_profession": //Beruf
                case "member_memos": //Bemerkungen
                case "member_comments": //Kommentare
                case "member_membership": //Mitgliedschaft
                case "member_membership_start": //Mitgliedschaft Start
                case "member_membership_end": //Mitgliedschaft Ende
                case "member_donation_amount": //Spende Betrag
                case "member_donation_date": //Spende Datum
                    
                    // use member result
                    $m_membership_columns_array  = array("member_membership","member_membership_start","member_membership_end");   
                    $m_donation_columns_array  = array("member_donation_amount","member_donation_date");

                    $member_col = "";
                    $member_col = str_replace("member_","",$col_value['column']);
                    $mr = 0;
                    
                    foreach($member_details as $k=>$m_data){
                        
                        if( !in_array($col_value['column'],$m_membership_columns_array) &&  !in_array($col_value['column'],$m_donation_columns_array)){
                            
                            if($member_col == "birthd"){
                                if($m_data['birthd'] != "0000-00-00"){
                                    $resulted_data[$mr]['member_'.$member_col] = date('d.m.Y',strtotime($m_data[$member_col]));
                                }
                            }
                            else 
                            {
                                $resulted_data[$mr]['member_'.$member_col] = $m_data[$member_col];
                            }
                        }
                        elseif(in_array($col_value['column'],$m_donation_columns_array) && !empty($m_data['MemberDonations']))
                        {
                            foreach($m_data['MemberDonations'] as $k=>$donations){
                                if($col_value['column'] == "member_donation_amount" )
                                {
                                    $resulted_data[$mr]['member_donation_amount'][] = $donations['amount'] ;
                                }
                                if($col_value['column'] == "member_donation_date" )
                                {
                                    $resulted_data[$mr]['member_donation_date'][] = date('d.m.Y',strtotime($donations['donation_date'])) ;
                                }
                                
                            }
                        }
                        elseif(in_array($col_value['column'],$m_membership_columns_array) && !empty($m_data['Member2Memberships']))
                        {
                            foreach($m_data['Member2Memberships'] as $k=>$membersh){
                                if($col_value['column'] == "member_membership" )
                                {
                                    $resulted_data[$mr]['member_membership'][] = $client_memberships[$membersh['membership']]['membership'] ;
                                }
                                if($col_value['column'] == "member_membership_start" )
                                {
                                    $resulted_data[$mr]['member_membership_start'][] = date('d.m.Y',strtotime($membersh['start_date'])) ;
                                }
                                if($col_value['column'] == "member_membership_end" )
                                {
                                    if($membersh['end_date'] != "0000-00-00 00:00:00"){
                                        if($client_memberships_end[$membersh['end_reasonid']]){
                                            $end_reason =  " (".$client_memberships_end[$membersh['end_reasonid']].")";
                                        }else{
                                            $end_reason = "";
                                        }
                                        
                                        $resulted_data[$mr]['member_membership_end'][] = date('d.m.Y',strtotime($membersh['end_date']))." ".$end_reason ;
                                    } else{
                                        $resulted_data[$mr]['member_membership_end'][] = "-";
                                    }
                                }
                                
                            }
                            
                        }
                        
                    $mr++;
                    }
                    
                    break;

                    case "working_vw_time":
                        foreach($report['period'] as $period)
                        {
                            $sql_vw_visit_data[] = "( `%date%` BETWEEN '" . $period['start'] . "' AND '" . $period['end'] . "' )";
                        }
                    
                        $sql_vw_str = implode(' OR ', $sql_vw_visit_data);
                    
                        $vw_hpvisits_q = Doctrine_Query::create()
                        ->select("*,h.ipid,h.id")
                        ->from("PatientHospizvizits h")
                        ->whereIn('h.ipid', $patients_ipids_array)
                        ->andWhere('h.isdelete = 0')
                        ->andWhere('('. str_replace('%date%', 'hospizvizit_date', $sql_vw_str) .')' );
                        $vw_hpvisits_arr = $vw_hpvisits_q->fetchArray();

                        if($vw_hpvisits_arr)
                        {
                            foreach($vw_hpvisits_arr as $k_hpv => $value_hpv)
                            {
                                if(Pms_Validation::integer($value_hpv['besuchsdauer'])){
                                    $resulted_data[$value_hpv['ipid']][$col_value['column']] += (int)$value_hpv['besuchsdauer'];
                                } else {
                                   $resulted_data[$value_hpv['ipid']][$col_value['column']] += "0";
                                }
                            }
                        }
                        
                        break;
                        
                        case "voluntaryworker":
                        	foreach($report['period'] as $period)
                        	{
                        		$sql_vw_attached_start[] = "( `start_date` BETWEEN '" . $period['start'] . "' AND '" . $period['end'] . "' )";
                        		$sql_vw_attached_end[] = "( `end_date` BETWEEN '" . $period['start'] . "' AND '" . $period['end'] . "' )";
                        	}
                        	
                        	$sql_vw_str_start = implode(' OR ', $sql_vw_attached_start);
                        	$sql_vw_str_end = implode(' OR ', $sql_vw_attached_end);
                        	
                        	$sql_vw_str = $sql_vw_str_start . ' OR ' . $sql_vw_str_end;
                        	$patientvw = Doctrine_Query::create()
                        	->select("*")
                        	->from('PatientVoluntaryworkers v')
                        	->whereIn('v.ipid', $patients_ipids_array)
                        	->andwhere('v.isdelete = 0')
                        	->andWhere($sql_vw_str);
                        	
                        	$patientvwarray = $patientvw->fetchArray();
                        	//print_r($patientvwarray); exit;
                        	foreach($patientvwarray as $k_vw => $vwval)
                        	{
                        		if ( ! is_array($resulted_data[$vwval['ipid']][$col_value['column']])) {
                        			$resulted_data[$vwval['ipid']][$col_value['column']] = array();
                        		}
                        		if(array_key_exists($vwval['vwid'], $voluntaryworker))
                        		{
                        			$resulted_data[$vwval['ipid']][$col_value['column']][] = $voluntaryworker[$vwval['vwid']]['name'];
                        		}
                        	
                        	}
                        	
                        	break;
                    
                        	case "icon":
                        		//ISPC - 2236 -new report - display criteria
                        		$sys_icons = new IconsMaster();
                        		$client_icons = new IconsClient();
                        		$patient_icons = new IconsPatient();
                        		
                        		$patientMasterData = Doctrine_Query::create()
                        		->select("*,AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,convert(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1) as zip,convert(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1) as street1,convert(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1) as city,convert(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone,convert(AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') using latin1) as kontactnumber")
                        		->from('PatientMaster p indexBy p.ipid')
                        		->whereIn('ipid', $patients_ipids_array);
                        		
                        		$pat_ipids_details = $patientMasterData->fetchArray();
                        		
                        		$patient_ipids_simple = array_values(array_unique($patients_ipids_array));
                        		
                        		if(empty($pat_ipids_details))
                        		{
                        			$pat_ipids_details[] = array();
                        		}
                        		
                        		if(empty($patient_ipids_simple))
                        		{
                        			$patient_ipids_simple[] = array();
                        		}
                        		
                        		$modules = new Modules();
                        		if($modules->checkModulePrivileges("67", $clientid))
                        		{
                        			$sgbvperms = true;
                        		}
                        		else
                        		{
                        			$sgbvperms = false;
                        		}
                        		
                        		$client_icons_details = $client_icons->get_client_icons($clientid);
                        		$all_system_icons = $sys_icons->get_system_icons($clientid);
                        		foreach($all_system_icons as $k_all_sys_icons => $v_all_sys_icons)
                        		{
                        			if($k_all_sys_icons == '26' && $sgbvperms)
                        			{
                        				$system_icons_perms[$k_all_sys_icons] = $v_all_sys_icons;
                        			}
                        			else if($k_all_sys_icons != '26')
                        			{
                        				$system_icons_perms[$k_all_sys_icons] = $v_all_sys_icons;
                        			}
                        		}
                        		
                        		//get system icon column data!
                        		$icons_data = array();
					            foreach($all_system_icons as $ks_sys_icon => $vs_sys_icon)
								{
									if($vs_sys_icon['function'] != 'go_to_visitform') //exclude visitform icon from filtering
									{
										if($vs_sys_icon['function'] == "get_patients_status")
										{
											$icon_result = $patient_icons->{$vs_sys_icon['function']}($pat_ipids_details, false, false, $details_included = true);
										}
										elseif($vs_sys_icon['function'] == "get_patient_medication")
										{
											$icon_result = $patient_icons->{$vs_sys_icon['function']}($patient_ipids_simple);
										}
										elseif($vs_sys_icon['function'] == "get_patient_diagnosis")
										{
											$icon_result = $patient_icons->{$vs_sys_icon['function']}($patient_ipids_simple);
										}
										elseif($vs_sys_icon['function'] == "has_painmedication_icon")
										{
											$icon_result = $patient_icons->{$vs_sys_icon['function']}($patient_ipids_simple, false);
										}
										else
										{
											$icon_result = $patient_icons->{$vs_sys_icon['function']}($patient_ipids_simple);
										}
										$iconstemp = $icon_result;
										unset($icon_result['ipids']);
										
										if($icon_result)
										{
											$icons_data = array_merge_recursive($icons_data, $iconstemp);
				
											//$patients_icons_details[$ks_sys_icon] = $icons_data['ipids'];
											$patients_icons_details[$ks_sys_icon] = array_values(array_unique($iconstemp['ipids']));
										}
									}
								}
								//print_r($patients_icons_details); exit;
                        		//reverse previous arr $patients_icons_details and construct mapped array
				            	foreach($patients_icons_details as $id_sys_icon => $icon_ipids)
								{
									if(is_numeric($id_sys_icon)) //make sure this key is always numeric... no need for others
									{
										foreach($icon_ipids as $k_icon_ipid => $v_icon_ipid)
										{
											if($v_icon_ipid != '999999999')
											{
												if($id_sys_icon != "6" && $id_sys_icon != "28")
												{ // status and memo
													$patient_icons_data[$v_icon_ipid]['icons_system'][] = $this->view->translate($all_system_icons[$id_sys_icon]['name']) . ';';
												}
											}
										}
									}
								}
                        		
                        		//get custom icons for all patients
                        		$c_custom_icons = $patient_icons->get_patient_icons($patients_ipids_array);
                        		
                        		foreach($c_custom_icons as $k_col_cust_icons => $v_col_cust_icons)
                        		{
                        			$patient_icons_data[$v_col_cust_icons['ipid']]['icons_custom'][] = $client_icons_details[$v_col_cust_icons['icon_id']]['name'] . ';';
                        		}
                        		
                        		foreach($patient_icons_data as $kip=>$vip)
                        		{
                        			
                        			$resulted_data[$kip][$col_value['column']] = implode('<br />', $vip['icons_system']) . '<br /> ' . implode('<br />', $vip['icons_custom']);
                        		}
                        		
                        		break;
                        	                        	
                	    
                default:
                    break;
            }
        }
        //echo "<pre>";
       //print_r($resulted_data); exit;
        $resuldet_items = array_keys($resulted_data);
        foreach($report['columns'] as $kck => $ccol_value )
        {
            if($extra_data[$ccol_value['column']]){
                $resulted_extra[$ccol_value['column']]['average'] = round(array_sum($extra_data[$ccol_value['column']]) / count($resuldet_items),2); 
                $resulted_extra[$ccol_value['column']]['median'] = Pms_CommonData::calculate_median($extra_data[$ccol_value['column']]); 
            }
        }
        
//         print_r($resulted_data); exit;
        
        if(!empty($resulted_data) && !empty($data['sortby']) && $data['sortby'] != 0 ){
            foreach($columns_array as $k=>$cdata){
                $column_data_array[$cdata['id']] =   $cdata['column_name'];
            } 
            
            if($column_data_array[$data['sortby']]){
                $sortarr = trim($column_data_array[$data['sortby']]);
            
                $resulted_data = $this->array_sort($resulted_data, $sortarr, SORT_ASC);
            }
        }
        $report['data'] = $resulted_data;
        $report['data_extra'] = $resulted_extra;
        
        return $report;
    }
    
    private function generate_html($report_id,$data,$output = false)
    {
        $all_columns_from_report =   array_column($data['columns'],'column');    //ISPC-2533 Lore 06.02.2020 & ISPC-2534 Lore 13.02.2020
        
        //ISPC-2650 Lore 18.08.2020
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $client_details = Pms_CommonData::getClientData($clientid);
        $client_name =  $client_details[0]['team_name'] ;
        
        // REPORT PERIOD
        if($data['period_type'] != "3")
        {
            if(count($data['period']) > 0)
            {
                $periods_no = count($data['period']);
                $table = '<table class="datatable" cellpadding="5" cellspacing="0" border="0" style="width:30%;">
        				<tr>
        				<th rowspan="' . ($periods_no + 1) . '" style="width:42%;">' . $this->view->translate('reportperiods') . '</th>
        				<th>' . $this->view->translate('rfrom') . '</th>
        				<th>' . $this->view->translate('rtill') . '</th>
        				</tr>';
            
                foreach($data['period'] as $k_period => $value)
                {
                    $table .= '<tr>';
                    $table .= '<td style="width:10%;">' . date('d.m.Y', strtotime($value['start'])) . '</td>';
                    $table .= '<td style="width:10%;">' . date('d.m.Y', strtotime($value['end'])) . '</td>';
                    $table .= '</tr>';
                }
                $table .= '</table>';
            }
        }

        
        if(strlen($data['details']['name']) > 0)
        {
            $report_name  = $data['details']['name'];
        } 
        else
        {
            $report_name  = 'Bericht # ';
        }
        
        $html = "";
        $html = '<h3 style=" width:100%; line-height:25px; font-size: 18px; font-family : Arial;">Name des Berichts: ' . $report_name . '</h3>';
        //ISPC-2650 Lore 18.08.2020
        $html .= "";
        $html .= '<h3 style=" width:100%; line-height:25px; font-size: 18px; font-family : Arial;">Mandant: ' . $client_name . '</h3>';
        
        $html .= $table;
        $html .='<table class="datatable" cellpadding="5" cellspacing="0" border="1" width="80%">';
        $html .='<tr>';
        
        
        //ISPC-2533 Lore 06.02.2020   &&   //ISPC-2534 Lore 10.02.2020
        if(in_array('contact_forms_delegation',$all_columns_from_report) && in_array('contact_forms_leistung_koordination',$all_columns_from_report) ){
            $html .= '<th width="1%" rowspan="4">' . $this->view->translate('no') . '</th>';
        } 
        elseif(in_array('contact_forms_delegation',$all_columns_from_report) && !in_array('contact_forms_leistung_koordination',$all_columns_from_report) ){
            $html .= '<th width="1%" rowspan="2">' . $this->view->translate('no') . '</th>';
        } 
        elseif(!in_array('contact_forms_delegation',$all_columns_from_report) && in_array('contact_forms_leistung_koordination',$all_columns_from_report) ){          
            $html .= '<th width="1%" rowspan="3">' . $this->view->translate('no') . '</th>';
        } 
        else
        {
            $html .= '<th width="1%">' . $this->view->translate('no') . '</th>';
            
        }
        
     
        
        //dd($data);
        foreach($data['columns'] as $ck => $column)
        {
            if($column['column_type'] != "o")
            {
                $rspsan="1";
                $colspan="1";
                
             
               if( in_array('contact_forms_delegation',$all_columns_from_report) && in_array('contact_forms_leistung_koordination',$all_columns_from_report) )  {
                    
                    if( ! in_array( $column['column'], array("contact_forms_leistung_koordination", "contact_forms_delegation" ) ) ){
                        $rspsan="4";
                    } else{
                        $rspsan="1";
                    }
                    
                    
                }
                elseif( in_array('contact_forms_delegation',$all_columns_from_report) && !in_array('contact_forms_leistung_koordination',$all_columns_from_report) )
                {
                    if( $column['column'] != "contact_forms_delegation"  ){
                        $rspsan="2";
                    }
                }
                elseif( !in_array('contact_forms_delegation',$all_columns_from_report) && in_array('contact_forms_leistung_koordination',$all_columns_from_report) )
                {
                    if( $column['column'] != "contact_forms_leistung_koordination" ){
                        $rspsan="3";
                    }
                }else{
                    $rspsan="1";
                }
                

                if(in_array('contact_forms_delegation',$all_columns_from_report) && $column['column'] == "contact_forms_delegation"){
                    $colspan="9";
                }
                
                if(in_array('contact_forms_leistung_koordination',$all_columns_from_report) && $column['column'] == "contact_forms_leistung_koordination"){
                    $colspan= 68*5+4+1  ;            // ="345";
                }

              
                $html .= '<th width="10%" rowspan="'.$rspsan.'" colspan="'.$colspan.'" >' . $this->view->translate($column['column']) . '</th>';

                $report_columns[] = $column['column'];
                if($column['report_show_average'] == "1"){
                    $report_columns_avg[] = $column['column'];
                }
                if($column['report_show_median'] == "1"){
                    $report_columns_median[] = $column['column'];
                }
                
            }
            else 
            {
                switch ($column['column'])
                {
                    case "discharge_statistics";    
                        $html .= '<th width="10%">' . $this->view->translate('discharge_location') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('count') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('percentage') . '</th>';
                        $report_columns = array("0"=>"discharge_location","1"=>"count","2"=>"percentage");
                        
                    break;    
                    case "transferred_by";    
                        $html .= '<th width="10%">' . $this->view->translate('referredby') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('count') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('percentage') . '</th>';
                        $report_columns = array("0"=>"referredby","1"=>"count","2"=>"percentage");
                    break;  
                    case "meeting_atended_inperiod";    
                        $html .= '<th width="10%">' . $this->view->translate('lastname') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('firstname') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('attented_meetings') . '</th>';
                        $report_columns = array("0"=>"lastname","1"=>"firstname","2"=>"attented_meetings");
                    break;  
 
                }
                
            }
        }

        $html .= '</tr>';

        
        if(in_array('contact_forms_delegation',$all_columns_from_report) && in_array('contact_forms_leistung_koordination',$all_columns_from_report)  ){
            foreach($data['columns'] as $ck => $column){
                
                if($column['column'] == "contact_forms_delegation"  || $column['column'] == "contact_forms_leistung_koordination"){
                    
                        if($column['column'] == "contact_forms_delegation" ){
                            
                            $html .= '<tr>';
                            $html .= '<th width="10%" rowspan="3" >' . $this->view->translate('date') . '</th>';
                            $html .= '<th width="10%" rowspan="3" >' . $this->view->translate('delegation_medication_check_sgbv') . '</th>';
                            $html .= '<th width="10%" rowspan="3" >' . $this->view->translate('delegation_wound_care_sgbv') . '</th>';
                            $html .= '<th width="10%" rowspan="3" >' . $this->view->translate('delegation_catheter_replacement_sgbv') . '</th>';
                            $html .= '<th width="10%" rowspan="3" >' . $this->view->translate('delegation_blood_collection_sgbv') . '</th>';
                            $html .= '<th width="10%" rowspan="3" >' . $this->view->translate('delegation_inr_measurement_sgbv') . '</th>';
                            $html .= '<th width="10%" rowspan="3" >' . $this->view->translate('delegation_bz_measurement_sgbv') . '</th>';
                            $html .= '<th width="10%" rowspan="3" >' . $this->view->translate('delegation_injection_sgbv') . '</th>';
                            $html .= '<th width="10%" rowspan="3" >' . $this->view->translate('delegation_vaccination_sgbv') . '</th>';
                            $html .= '</tr>';
                            
                        }
                        
                        if($column['column'] == "contact_forms_leistung_koordination" ){
                            $sel_sett = Doctrine_Query::create()
                            ->select('*')
                            ->from('FormBlocksSettings indexBy id')
                            ->where('clientid = ? ', $this->clientid )
                            ->andWhere('block = ?',"coordinator_actions")
                            ->andWhere('isdelete = 0');
                            $sel_sett_res = $sel_sett->fetchArray();
                            
                            $html .= '<tr>';
                            $html .= '<th width="10%" rowspan="2" >' . $this->view->translate('date') . '</th>';
                            foreach($sel_sett_res as $key=>$val){
                                if($val['form_item_class'] == 'ca_state'){
                                    $html .= '<th width="10%" colspan="5">' . $val['option_name'] . '</th>';
                                }else{
                                    $html .= '<th width="10%" colspan="4">' . $val['option_name'] . '</th>';
                                }
                            }
                            $html .= '</tr>';

                            $html .= '<tr>';
                                                        
                            
                            foreach($sel_sett_res as $key=>$val){
                                if($val['form_item_class'] == 'ca_state'){
                                    $html .= '<th width="10%">' . $this->view->translate('ca_receives_services') . '</th>';
                                    $html .= '<th width="10%">' . $this->view->translate('ca_is_requested') . '</th>';
                                    $html .= '<th width="10%">' . $this->view->translate('ca_redirected') . '</th>';
                                    $html .= '<th width="10%">' . $this->view->translate('ca_informed') . '</th>';
                                    $html .= '<th width="10%">' . $this->view->translate('ca_action_comment') . '</th>';
                                }else{
                                    $html .= '<th width="10%">' . $this->view->translate('hand_strength_training') . '</th>';
                                    $html .= '<th width="10%">' . $this->view->translate('pain_diary') . '</th>';
                                    $html .= '<th width="10%">' . $this->view->translate('sleep_diary') . '</th>';
                                    $html .= '<th width="10%">' . $this->view->translate('incontinence_protocol') . '</th>';
                                    $html .= '</tr>';
                                }
                            }
                            $html .= '</tr>';               

                            
                        }
 
                } 
            }
        } else {
            
            
            //ISPC-2533 Lore 06.02.2020
            if(in_array('contact_forms_delegation',$all_columns_from_report)  ){
                
                $html .= '<tr>';
                
                $html .= '<th width="10%">' . $this->view->translate('date') . '</th>';
                $html .= '<th width="10%">' . $this->view->translate('delegation_medication_check_sgbv') . '</th>';
                $html .= '<th width="10%">' . $this->view->translate('delegation_wound_care_sgbv') . '</th>';
                $html .= '<th width="10%">' . $this->view->translate('delegation_catheter_replacement_sgbv') . '</th>';
                $html .= '<th width="10%">' . $this->view->translate('delegation_blood_collection_sgbv') . '</th>';
                $html .= '<th width="10%">' . $this->view->translate('delegation_inr_measurement_sgbv') . '</th>';
                $html .= '<th width="10%">' . $this->view->translate('delegation_bz_measurement_sgbv') . '</th>';
                $html .= '<th width="10%">' . $this->view->translate('delegation_injection_sgbv') . '</th>';
                $html .= '<th width="10%">' . $this->view->translate('delegation_vaccination_sgbv') . '</th>';
                
                $html .= '</tr>';
                
            }
            
            //ISPC-2534 Lore 10.02.2020
            if(in_array('contact_forms_leistung_koordination',$all_columns_from_report)  ){
                
                $clientid = $this->clientid;
                $sel_sett = Doctrine_Query::create()
                ->select('*')
                ->from('FormBlocksSettings indexBy id')
                ->where('clientid = ? ', $clientid )
                ->andWhere('block = ?',"coordinator_actions")
                ->andWhere('isdelete = 0');
                $sel_sett_res = $sel_sett->fetchArray();
                
                $html .= '<tr>';
                $html .= '<th width="10%" rowspan="2" >' . $this->view->translate('date') . '</th>';
                foreach($sel_sett_res as $key=>$val){
                    if($val['form_item_class'] == 'ca_state'){
                        $html .= '<th width="10%" colspan="5">' . $val['option_name'] . '</th>';
                    }else{
                        $html .= '<th width="10%" colspan="4">' . $val['option_name'] . '</th>';
                    }
                }
                $html .= '</tr>';
                
                $html .= '<tr>';
                foreach($sel_sett_res as $key=>$val){
                    if($val['form_item_class'] == 'ca_state'){
                        $html .= '<th width="10%">' . $this->view->translate('ca_receives_services') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('ca_is_requested') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('ca_redirected') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('ca_informed') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('ca_action_comment') . '</th>';
                    }else{
                        $html .= '<th width="10%">' . $this->view->translate('hand_strength_training') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('pain_diary') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('sleep_diary') . '</th>';
                        $html .= '<th width="10%">' . $this->view->translate('incontinence_protocol') . '</th>';
                        $html .= '</tr>';
                    }
                }
                $html .= '</tr>';
                
            }
            
            
        }

        
        foreach($data['data'] as $key => $row)
        {
            foreach($row as $row_key => $value)
            {
                if(is_array($value) && sizeof($value) > 1)
                {
                    $multiple_rows[$key][$row_key] = sizeof($value);
                }
            }
        }
                
        $clientid = $this->clientid;
        $sel_sett = Doctrine_Query::create()
        ->select('*')
        ->from('FormBlocksSettings indexBy id')
        ->where('clientid = ? ', $clientid )
        ->andWhere('block = ?',"coordinator_actions")
        ->andWhere('isdelete = 0');
        $sel_sett_res = $sel_sett->fetchArray();
        
        $rowcount = 1;
        //dd($data['data']);
        foreach($data['data'] as $key => $row)
        {
            if($multiple_rows[$key])
            {
                $max_row_span =  max(array_values($multiple_rows[$key]));
            }
            else
            {
                $max_row_span = 1;
            }
           
           $first_shown = false;
           
            for ($i = 0; $i < $max_row_span; $i++){
                
                $html .='<tr class="row">';
                if($first_shown == false)
                {
                    $html .='<td valign="top" rowspan="'.$max_row_span.'">' . $rowcount . '</td>';
                }
                
                foreach($report_columns as $ck=>$col)
                {
                    $row_span = 1;
                    
                    if(!is_array($row[$col]))
                    {
                        $row_span = $max_row_span;
                    }
                    else 
                    {
                        if(max(array_keys($row[$col])) <= $i)
                        {
                            $row_span = $max_row_span - $i;
                        } 
                        else
                        {
                            $row_span = 1;
                        }
                            
                    }
                    
                    if(!empty($row[$col][$i]) && is_array($row[$col]))
                    {
                        //ISPC-2533 Lore 06.02.2020
                        if ($col == 'contact_forms_delegation'){           
                            foreach($row[$col][$i] as $vval){
                                $html.= '<td valign="top" rowspan="'.$row_span.'">' .$vval . '</td>';
                            } 
                        } 
                        //ISPC-2534 Lore 10.02.2020
                        elseif ($col == 'contact_forms_leistung_koordination'){            
                            foreach($row[$col][$i] as $vval){
                                if (!is_array($vval)){
                                    $html.= '<td valign="top" rowspan="'.$row_span.'">' .$vval . '</td>';
                                } else {
                                    foreach($vval as $f_vval){
                                        $html.= '<td valign="top" rowspan="'.$row_span.'">' .$f_vval . '</td>';
                                    } 
                                }

                            } 
                        } 
                        else {

                            $html.= '<td valign="top" rowspan="'.$row_span.'">' . $row[$col][$i] . '</td>';
                        }
                    }
                    else if(!empty($row[$col]) && $first_shown == false)
                    {
                        if ($col == 'contact_forms_delegation'){
                            $html.= '<td valign="top" rowspan="'.$row_span.'" colspan="9" ></td>';
                        } 
                        elseif ($col == 'contact_forms_leistung_koordination'){
                            foreach($sel_sett_res as $key=>$val){
                                if($val['form_item_class'] == 'ca_state'){
                                    $html.= '<td valign="top" rowspan="'.$row_span.'" colspan="5" ></td>';
                                    
                                }else{
                                    $html.= '<td valign="top" rowspan="'.$row_span.'" colspan="4" ></td>';
                                }
                            }
                        } else{
                            $html.= '<td valign="top" rowspan="'.$row_span.'">' . $row[$col] . '</td>';
                        }
                    }
                    else
                    {
                        if($first_shown == false){
                            if ($col == 'contact_forms_delegation'){
                                
                                $html.= '<td  rowspan="'.$row_span.'"></td>';
                                $html.= '<td  rowspan="'.$row_span.'"></td>';
                                $html.= '<td  rowspan="'.$row_span.'"></td>';
                                $html.= '<td  rowspan="'.$row_span.'"></td>';
                                $html.= '<td  rowspan="'.$row_span.'"></td>';
                                $html.= '<td  rowspan="'.$row_span.'"></td>';
                                $html.= '<td  rowspan="'.$row_span.'"></td>';
                                $html.= '<td  rowspan="'.$row_span.'"></td>';
                                $html.= '<td  rowspan="'.$row_span.'"></td>';
                                
                            } 
                            elseif ($col == 'contact_forms_leistung_koordination'){
                                foreach($sel_sett_res as $key=>$val){
                                    $html.= '<td  rowspan="'.$row_span.'"></td>';
                                }                               
                            }
                            else{
                                $html.= '<td  rowspan="'.$row_span.'" ></td>';
                                
                            }
                        }
                    }
                }
                $first_shown = true;
                
                $html.= '</tr>';
            }
            $rowcount++;
        }
        
        if(!empty($report_columns_avg))
        {
            $html .='<tr class="row"><td valign="top" >Durchschnitt</td>';
            foreach($report_columns as $k =>$col){
                if( $data['data_extra'][$col]['average'])
                {
                    $html.= '<td valign="top">' . $data['data_extra'][$col]['average'] . '</td>';
                }
                else 
                {
                    $html.= '<td valign="top"> </td>';
                }
            }
            $html .='</tr>';
        }
            
        if(!empty($report_columns_median))
        {
            $html .='<tr class="row"><td valign="top" >Median</td>';
            foreach($report_columns as $k =>$col){
                if( $data['data_extra'][$col]['median'])
                {
                    $html.= '<td valign="top">' . $data['data_extra'][$col]['median'] . '</td>';
                }
                else
                {
                    $html.= '<td valign="top"> </td>';
                }
            }
            $html .='</tr>';
        }
        
        
        
        $html.="</table>";		
        
        if($output == "screen" || !$output)
		{
			$html = '<link href="' . APP_BASE . 'css/reports.css?'.date('Ymd', time()).'" rel="stylesheet" type="text/css" />' . $html;
			echo $html;
			exit;
		}
		elseif($output == "printing")
		{
			$html = '<link href="' . APP_BASE . 'css/reports.css?'.date('Ymd', time()).'" rel="stylesheet" type="text/css" />' . $html;

			echo $html;
			echo "<SCRIPT type='text/javascript'>";
			echo "window.print();";
			echo "</SCRIPT>";
			exit;
		}
    }
    
    
    

    private function generatePHPExcel($report_id,$data)
    {
        $Tr = new Zend_View_Helper_Translate();
        //ISPC-2650 Lore 18.08.2020
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $client_details = Pms_CommonData::getClientData($clientid);
        $client_name =  $client_details[0]['team_name'] ;
        
        if(strlen($data['details']['name']) > 0)
        {
            $report_name  = $data['details']['name'];
        }
        else
        {
            $report_name  = 'Bericht # ';
        }
 
        // Create new PHPExcel object
        $excel = new PHPExcel();
        	
        $excel->getDefaultStyle()->getFont()
        ->setSize(10);
        /* ->setName('Verdana') */
        /* ->setBold(true) */
        	
        $xls = $excel->getActiveSheet();
    
        $line= 1;
        $xls->setCellValue("A".$line, 'Name des Berichts: ' . utf8_decode($report_name))->mergeCells('A'.$line.':'.chr($columns_nr+65).$line.'');
        $line++;
        //ISPC-2650 Lore 18.08.2020
        $xls->setCellValue("A".$line, 'Mandant: ' . $client_name);
        $line++;
        
        if($data['period_type'] != "3")
        {
            if(count($data['period']) > 0)
            {
                $periods_no = count($data['period']);
                $xls->setCellValue("A".$line, $this->view->translate('reportperiods'));
                $xls->setCellValue("B".$line, $this->view->translate('rfrom'));
                $xls->setCellValue("C".$line, $this->view->translate('rtill'));
                $line++; //next line
        
                foreach($data['period'] as $k_period => $value)
                {
                    $cp = 1;
                    $xls->setCellValue("B" . $line, date('d.m.Y',strtotime($value['start'])));
                    $xls->setCellValue("C" . $line, date('d.m.Y',strtotime($data['period'][$k_period]['end'])));
                    $line++;
                }
            $line++; //leave one line between periods and table
            }
        }
        $line++;
        
        $xls->setCellValue("A" . $line, $this->view->translate('no'));
        $columns = array_values($data['columns']);
        
        
        foreach($columns as $k=>$cl){
             switch ($cl['column'])
             {
                 case "discharge_statistics";
                 $report_columns_spec = array("0"=>array('column' => "discharge_location"),"1"=>array('column' => "count"),"2"=>array('column' => "percentage"));
             
                 break;
                 
                 case "transferred_by";
                     $report_columns_spec = array("0"=>array('column' => "referredby"),"1"=>array('column' => "count"),"2"=>array('column' => "percentage"));
                 break;
                 
                 case "meeting_atended_inperiod";
                    $report_columns_spec = array("0"=>array('column' => "lastname"),"1"=>array('column' => "firstname"),"2"=>array('column' => "attented_meetings"));
                 break;
         
                 default:
                     $report_columns_spec = null;
                 break;
             }
         }

         if(!empty($report_columns_spec))
         {
             $columns = $report_columns_spec;
         }
 
         
        //ISPC-2533 & ISPC-2534 Lore 19.02.2020
         $afostdeleg = 0;
         $afostcoord = 0;
         $report_columns = array();
         $display_columns = array();
         
         foreach($columns as $cl_key => $cl_data ){
             
             $cl_key += 1 + $afostdeleg + $afostcoord;
             
             if($cl_data['column'] == "contact_forms_delegation" ){
                 $afostdeleg = 8;
             }
             
             if($cl_data['column'] == "contact_forms_leistung_koordination" ){
                 $afostcoord = 344;
             } 
                          
             $pos = floor($cl_key/26);
             
             if($pos == 0) {
                 $col_xls = chr($cl_key + 65);
             } else {
                 $col_xls = chr($pos + 64).chr(($cl_key - $pos * 26) + 65);
             } 
             
             $report_columns[ $col_xls ] =  $cl_data;
             
             $display_columns[ $cl_data['column']] =  $col_xls ;   
             
         }
         
        
        foreach($report_columns as $ck => $column)
        { 
            if($column['column_type'] != "o")
            {
                $xls->setCellValue($ck.$line, utf8_decode($this->view->translate($column['column'])) );
                               
                if($column['report_show_average'] == "1"){
                    $report_columns_avg[] = $column['column'];
                }
                if($column['report_show_median'] == "1"){
                    $report_columns_median[] = $column['column'];
                }
            }
        }        
        $line++;
        
        //ISPC-2533 & ISPC-2534 Lore 19.02.2020
        $all_columns_from_report =   array_column($data['columns'],'column');   
        
        foreach($report_columns as $ck => $column)
        {
            if($column['column_type'] != "o")
            {
                if($column['column'] == 'contact_forms_delegation' ){
                    
                    $colnewline = $ck;
                    $xls->setCellValue($colnewline++.$line, utf8_decode($this->view->translate('date')) );                    
                    $xls->setCellValue($colnewline++.$line, utf8_decode($this->view->translate('delegation_medication_check_sgbv')) );
                    $xls->setCellValue($colnewline++.$line, utf8_decode($this->view->translate('delegation_wound_care_sgbv')) );
                    $xls->setCellValue($colnewline++.$line, utf8_decode($this->view->translate('delegation_catheter_replacement_sgbv')) );
                    $xls->setCellValue($colnewline++.$line, utf8_decode($this->view->translate('delegation_blood_collection_sgbv')) );
                    $xls->setCellValue($colnewline++.$line, utf8_decode($this->view->translate('delegation_inr_measurement_sgbv')) );
                    $xls->setCellValue($colnewline++.$line, utf8_decode($this->view->translate('delegation_bz_measurement_sgbv')) );
                    $xls->setCellValue($colnewline++.$line, utf8_decode($this->view->translate('delegation_injection_sgbv')) );
                    $xls->setCellValue($colnewline++.$line, utf8_decode($this->view->translate('delegation_vaccination_sgbv')) );
                                        
                }

                if($column['column'] == 'contact_forms_leistung_koordination' ){
                    
                    $sel_sett = Doctrine_Query::create()
                    ->select('*')
                    ->from('FormBlocksSettings indexBy id')
                    ->where('clientid = ? ', $this->clientid )
                    ->andWhere('block = ?',"coordinator_actions")
                    ->andWhere('isdelete = 0');
                    $sel_sett_res = $sel_sett->fetchArray();
                      
                    $rd2newcol = $ck;
                                       
                    $xls->setCellValue($rd2newcol++.$line, utf8_decode($this->view->translate('date')) );                   
                    
                    foreach($sel_sett_res as $key=>$val){
                        
                        $xls->setCellValue($rd2newcol++.$line, html_entity_decode($val['option_name'], ENT_QUOTES, 'UTF-8') );
                        $xls->setCellValue($rd2newcol++.$line, '' );
                        $xls->setCellValue($rd2newcol++.$line, '' );
                        $xls->setCellValue($rd2newcol++.$line, '' );
                        
                        if($val['form_item_class'] == 'ca_state'){
                            $xls->setCellValue($rd2newcol++.$line, '' );    // most have 5 columns 
                        }
                        
            
                    }
                    $line++;
                    
                    
                    $rd3newcol = $ck;
                    
                    $xls->setCellValue($rd3newcol++.$line, "" );
                    
                    foreach($sel_sett_res as $key=>$val){
                        if($val['form_item_class'] == 'ca_state'){
                            
                            $xls->setCellValue($rd3newcol++.$line, html_entity_decode($this->view->translate('ca_receives_services'), ENT_QUOTES, 'UTF-8') );
                            $xls->setCellValue($rd3newcol++.$line, html_entity_decode($this->view->translate('ca_is_requested'), ENT_QUOTES, 'UTF-8') );
                            $xls->setCellValue($rd3newcol++.$line, html_entity_decode($this->view->translate('ca_redirected'), ENT_QUOTES, 'UTF-8') );
                            $xls->setCellValue($rd3newcol++.$line, html_entity_decode($this->view->translate('ca_informed'), ENT_QUOTES, 'UTF-8') );
                            $xls->setCellValue($rd3newcol++.$line, html_entity_decode($this->view->translate('ca_action_comment'), ENT_QUOTES, 'UTF-8') );
                            
                        }else{
                            $xls->setCellValue($rd3newcol++.$line, html_entity_decode($this->view->translate('hand_strength_training'), ENT_QUOTES, 'UTF-8') );
                            $xls->setCellValue($rd3newcol++.$line, html_entity_decode($this->view->translate('pain_diary'), ENT_QUOTES, 'UTF-8') );
                            $xls->setCellValue($rd3newcol++.$line, html_entity_decode($this->view->translate('sleep_diary'), ENT_QUOTES, 'UTF-8') );
                            $xls->setCellValue($rd3newcol++.$line, html_entity_decode($this->view->translate('incontinence_protocol'), ENT_QUOTES, 'UTF-8') );

                        }
                    }
                    
                }
                
            }
        }
        if(in_array('contact_forms_delegation',$all_columns_from_report) || in_array('contact_forms_leistung_koordination',$all_columns_from_report) ){
            $line++;           
        }
        
               
        
        //remove all non related data
        $rep_clumns= array();
        $rep_clumns = array_keys($display_columns);
        
        foreach($data['data'] as $key_date => $row)
        {
            foreach($row as $col=>$value)
            {
                if(!in_array($col,$rep_clumns)){
                    unset($data['data'][$key_date][$col]);
                }
            }
        }
        
        $row_nr = "1";
        
        foreach($data['data'] as $key_date => $row)
        {
            $lineinserted = 0;
            
            $xls->setCellValue("A".$line, $row_nr);
            
            foreach($row as $col=>$value)
            {   
                                                
                if(is_array($value) && sizeof($value) >= 1)
                {
                    $currentrow = $line;
                    $cntSapv = count($value);
                    
                    for($s=0; $s<$cntSapv; $s++)
                    {
                        $colcellmulti = $display_columns[$col];
                        
                        if( !is_array($value[$s]) ){
                            
                            $xls->setCellValue($colcellmulti++.$currentrow, $value[$s]);
                            
                        } else {
                            
                            foreach($value[$s] as $keyCell => $valCell){
                                
                                if(is_array($valCell)){
                                    foreach ($valCell as $label=>$ddata){
                                        $ddata = str_replace("<br />", "\n", $ddata);
                                        $ddata = str_replace("<hr/>", "\n", $ddata);//TODO-3617 Ancuta 18.11.2020
                                        $xls->setCellValue($colcellmulti++.$currentrow, $ddata);
                                    }
                                } else{
                                    // daca e coloana de date
                                    $valCell = str_replace("<br />", "\n", $valCell);
                                    $valCell = str_replace("<hr/>", "\n", $valCell);//TODO-3617 Ancuta 18.11.2020
                                    $xls->setCellValue($colcellmulti++.$currentrow, $valCell);
                                }
                                
                            }
                            
                        }
                        $all[$key_date][] = $currentrow;
                        $currentrow++;
                        $lineinserted = max($s,$lineinserted);
                    }
                    
                }
                else
                {
                    $value = str_replace("<br />", "\n", $value);
                    $value = str_replace("<hr/>", "\n", $value);//TODO-3617 Ancuta 18.11.2020
                    $xls->setCellValue($display_columns[$col].$line, $value);
                    
                }

                
            }
            if($all[$key_date]){
                $line = max( $all[$key_date]);
            }else{
                $line = $line + $lineinserted;
            }
            
            $line++;
            $row_nr++;
        }
        

        
        if(!empty($report_columns_avg)){
            $xls->setCellValue("A".$line, "Durchschnitt");
            
            foreach($report_columns as $ck => $column)
            {
                if($column['report_show_average'] == "1")
                {            
                    $xls->setCellValue($ck.$line,  $data['data_extra'][$column['column']]['average'] );
                }
            }
        }
        $line++;
        if(!empty($report_columns_median)){
            $xls->setCellValue("A".$line, "Median");
            
            foreach($report_columns as $ck => $column)
            {
                if($column['report_show_median'] == "1")
                {            
                    $xls->setCellValue($ck.$line,  $data['data_extra'][$column['column']]['median'] );
                }
            }
        }
        
        $file = str_replace(" ", "_", $report_name);
        $fileName = $file . ".xls";
        	
        	
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$fileName.'"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');  
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007'); // ISPC-2534 Lore 25.02.2020   Excel5 Writer have limit of 256 columns
        $objWriter->save('php://output');
        exit;
    }
    
    
    

    private function array_sort($array, $on = NULL, $order = SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();
        
        $date_array = array("birthd","discharge_date","day_of_admission","sapv_from_date","sapv_till_date");
        
        if(count($array) > 0)
        {
            foreach($array as $k => $v)
            {
                if(is_array($v))
                {
                    foreach($v as $k2 => $v2)
                    {
                        if($k2 == $on)
                        {
                            if(in_array($on,$date_array))
                            {
    
                                if($on == 'birthdyears')
                                {
                                    $v2 = substr($v2, 0, 10);
                                }
                                $sortable_array[$k] = strtotime($v2);
                            }
                            elseif($on == 'patient_nr')
                            {
                                $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
                            }
                            elseif($on == 'epid')
                            {
                                $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
                            }
                            elseif($on == 'percentage')
                            {
                                $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                            }
                            else
                            {
                                $sortable_array[$k] = ucfirst($v2);
                            }
                        }
                    }
                }
                else
                {
                    if(in_array($on,$date_array))
                    {
                        if($on == 'birthdyears')
                        {
                            $v = substr($v, 0, 10);
                        }
                        $sortable_array[$k] = strtotime($v);
                    }
                    elseif($on == 'patient_nr')
                    {
                        $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
                    }
                    elseif($on == 'epid')
                    {
                        $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
                    }
                    elseif($on == 'percentage')
                    {
                        $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                    }
                    else
                    {
                        $sortable_array[$k] = ucfirst($v);
                    }
                }
            }
            //			$collator = new Collator('de_DE');
            switch($order)
            {
                case SORT_ASC:
                    //					$collator->asort($sortable_array);
                    $sortable_array = Pms_CommonData::a_sort($sortable_array);
                    break;
    
                case SORT_DESC:
                    //					$collator->asort($sortable_array); //collator does not have a arsort equivalent
                    //					$sortable_array = array_reverse($sortable_array, true);
                    $sortable_array = Pms_CommonData::ar_sort($sortable_array);
    
                    break;
            }
    
            foreach($sortable_array as $k => $v)
            {
                $new_array[$k] = $array[$k];
            }
        }
    
        return $new_array;
    }
    
    
    /**
     * ISPC-2534 Lore 24.02.2020
     * @param  $report_id
     * @param  $data
     */
    private function export_xlsx($report_id, $data)
    {
        
        $this->xlsBOF();
        //ISPC-2650 Lore 18.08.2020
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $client_details = Pms_CommonData::getClientData($clientid);
        $client_name =  $client_details[0]['team_name'] ;
        
        if(strlen($data['details']['name']) > 0)
        {
            $report_name  = $data['details']['name'];
        }
        else
        {
            $report_name  = 'Bericht # ';
        }
        
        
        
        $line= 1;
        $this->xlsWriteLabel($line, 0, 'Name des Berichts: ' . utf8_decode($report_name));
        
        $line++;
        $line++;
        
        //ISPC-2650 Lore 18.08.2020
        $this->xlsWriteLabel($line, 0, "Mandant: " . $client_name . "");
        $line++;
        $line++;
        
        if($data['period_type'] != "3")
        {
            if(count($data['period']) > 0)
            {
                $periods_no = count($data['period']);
                
                $this->xlsWriteLabel($line, 0, $this->view->translate('reportperiods'));
                $this->xlsWriteLabel($line, 1, $this->view->translate('rfrom'));
                $this->xlsWriteLabel($line, 2, $this->view->translate('rtill'));
                
                $line++; //next line
                
                foreach($data['period'] as $k_period => $value)
                {
                    $this->xlsWriteLabel($line, 1, date('d.m.Y',strtotime($value['start'])));
                    $this->xlsWriteLabel($line, 2, date('d.m.Y',strtotime($data['period'][$k_period]['end'])));
                    
                    $line++;
                }
                $line++; //leave one line between periods and table
            }
        }
        $line++;
        
        $this->xlsWriteLabel($line, 0, $this->view->translate('no'));
        
        $columns = array_values($data['columns']);
        
        
        foreach($columns as $k=>$cl){
            switch ($cl['column'])
            {
                case "discharge_statistics";
                $report_columns_spec = array("0"=>array('column' => "discharge_location"),"1"=>array('column' => "count"),"2"=>array('column' => "percentage"));
                
                break;
                
                case "transferred_by";
                $report_columns_spec = array("0"=>array('column' => "referredby"),"1"=>array('column' => "count"),"2"=>array('column' => "percentage"));
                break;
                
                case "meeting_atended_inperiod";
                $report_columns_spec = array("0"=>array('column' => "lastname"),"1"=>array('column' => "firstname"),"2"=>array('column' => "attented_meetings"));
                break;
                
                default:
                    $report_columns_spec = null;
                    break;
            }
        }
        
        if(!empty($report_columns_spec))
        {
            $columns = $report_columns_spec;
        }
        
        
        //ISPC-2533 & ISPC-2534 Lore 19.02.2020
        $afostdeleg = 0;
        $afostcoord = 0;
        $report_columns = array();
        $display_columns = array();
        
        foreach($columns as $cl_key => $cl_data ){
            
            $cl_key += 1 + $afostdeleg + $afostcoord;
            
            if($cl_data['column'] == "contact_forms_delegation" ){
                $afostdeleg = 8;
            }
            
            if($cl_data['column'] == "contact_forms_leistung_koordination" ){
                $afostcoord = 344;
            }
            
            $pos = floor($cl_key/26);
            
            if($pos == 0) {
                $col_xls = chr($cl_key + 65);
            } else {
                $col_xls = chr($pos + 64).chr(($cl_key - $pos * 26) + 65);
            }
            
            $report_columns[ $col_xls ] =  $cl_data;
            
            $display_columns[ $cl_data['column']] =  $col_xls ;
            
        }
        
        
        foreach($report_columns as $ck => $column)
        {
            $cknumber = ord($ck) - 65 ;
            
            if($column['column_type'] != "o")
            {
                $this->xlsWriteLabel($line, $cknumber, utf8_decode($this->view->translate($column['column'])) );
                
                if($column['report_show_average'] == "1"){
                    $report_columns_avg[] = $column['column'];
                }
                if($column['report_show_median'] == "1"){
                    $report_columns_median[] = $column['column'];
                }
            }
        }
        $line++;
        
        
        //ISPC-2533 & ISPC-2534 Lore 19.02.2020
        foreach($report_columns as $ck => $column)
        {
            $cknumber = ord($ck) -65 ;
            
            if($column['column_type'] != "o")
            {
                if($column['column'] == 'contact_forms_delegation' ){
                    
                    $colnewline = $cknumber;
                    
                    $this->xlsWriteLabel($line, $colnewline++, utf8_decode($this->view->translate('date')) );
                    $this->xlsWriteLabel($line, $colnewline++, utf8_decode($this->view->translate('delegation_medication_check_sgbv')) );
                    $this->xlsWriteLabel($line, $colnewline++, utf8_decode($this->view->translate('delegation_wound_care_sgbv')) );
                    $this->xlsWriteLabel($line, $colnewline++, utf8_decode($this->view->translate('delegation_catheter_replacement_sgbv')) );
                    $this->xlsWriteLabel($line, $colnewline++, utf8_decode($this->view->translate('delegation_blood_collection_sgbv')) );
                    $this->xlsWriteLabel($line, $colnewline++, utf8_decode($this->view->translate('delegation_inr_measurement_sgbv')) );
                    $this->xlsWriteLabel($line, $colnewline++, utf8_decode($this->view->translate('delegation_bz_measurement_sgbv')) );
                    $this->xlsWriteLabel($line, $colnewline++, utf8_decode($this->view->translate('delegation_injection_sgbv')) );
                    $this->xlsWriteLabel($line, $colnewline++, utf8_decode($this->view->translate('delegation_vaccination_sgbv')) );
                }
                
                if($column['column'] == 'contact_forms_leistung_koordination' ){
                    
                    $sel_sett = Doctrine_Query::create()
                    ->select('*')
                    ->from('FormBlocksSettings indexBy id')
                    ->where('clientid = ? ', $this->clientid )
                    ->andWhere('block = ?',"coordinator_actions")
                    ->andWhere('isdelete = 0');
                    $sel_sett_res = $sel_sett->fetchArray();
                    
                    $rd2newcol = $cknumber;
                    
                    $this->xlsWriteLabel($line,$rd2newcol++, utf8_decode($this->view->translate('date')) );
                    
                    foreach($sel_sett_res as $key=>$val){
                        
                        if($val['form_item_class'] == 'ca_state'){
                            
                            $this->xlsWriteLabel($line,$rd2newcol++, utf8_decode($val['option_name']) );
                            $this->xlsWriteLabel($line,$rd2newcol++, '' );
                            $this->xlsWriteLabel($line,$rd2newcol++, '' );
                            $this->xlsWriteLabel($line,$rd2newcol++, '' );
                            $this->xlsWriteLabel($line,$rd2newcol++, '' );
                        }else{
                            $this->xlsWriteLabel($line,$rd2newcol++, utf8_decode($val['option_name']) );
                            $this->xlsWriteLabel($line,$rd2newcol++, '' );
                            $this->xlsWriteLabel($line,$rd2newcol++, '' );
                            $this->xlsWriteLabel($line,$rd2newcol++, '' );
                            
                        }
                        
                        
                    }
                    $line++;
                    
                    
                    $rd3newcol = $cknumber;
                    
                    $this->xlsWriteLabel($line,$rd3newcol++, "" );
                    
                    foreach($sel_sett_res as $key=>$val){
                        if($val['form_item_class'] == 'ca_state'){
                            
                            $this->xlsWriteLabel($line,$rd3newcol++, utf8_decode($this->view->translate('ca_receives_services')) );
                            $this->xlsWriteLabel($line,$rd3newcol++, utf8_decode($this->view->translate('ca_is_requested')) );
                            $this->xlsWriteLabel($line,$rd3newcol++, utf8_decode($this->view->translate('ca_redirected')) );
                            $this->xlsWriteLabel($line,$rd3newcol++, utf8_decode($this->view->translate('ca_informed')) );
                            $this->xlsWriteLabel($line,$rd3newcol++, utf8_decode($this->view->translate('ca_action_comment')) );
                            
                        }else{
                            $this->xlsWriteLabel($line,$rd3newcol++, utf8_decode($this->view->translate('hand_strength_training')) );
                            $this->xlsWriteLabel($line,$rd3newcol++, utf8_decode($this->view->translate('pain_diary')) );
                            $this->xlsWriteLabel($line,$rd3newcol++, utf8_decode($this->view->translate('sleep_diary')) );
                            $this->xlsWriteLabel($line,$rd3newcol++, utf8_decode($this->view->translate('incontinence_protocol')) );
                            
                        }
                    }
                    
                }
                
            }
        }
        $line++;
        
        
        $rep_clumns= array();
        $rep_clumns = array_keys($display_columns);
        
        foreach($data['data'] as $key_date => $row)
        {
            foreach($row as $col=>$value)
            {
                if(!in_array($col,$rep_clumns)){
                    unset($data['data'][$key_date][$col]);
                }
            }
        }
        
        $row_nr = "1";
        
        $insnewrow = $line;
        //dd($data['data']);
        
        foreach($data['data'] as $key_date => $row)
        {
            $line = $insnewrow;
            
            $this->xlsWriteNumber($line, 0, $row_nr);
            //dd($row);
            foreach($row as $col=>$value)
            {
                $disp_col_nr = ord($display_columns[$col]) - 65 ;
                
                if(is_array($value) && sizeof($value) >= 1)
                {
                    
                    $insnewrow = $line;
                    $currentrow = $line;
                    
                    $cntSapv = count($value);

                    
                    for($s = 0; $s <= $cntSapv; $s++)
                    {
                        $colcellmulti = $disp_col_nr;
                        
                        foreach($value[$s] as $keyCell => $valCell){
                                                        
                            if(is_array($valCell)){
                                foreach ($valCell as $label=>$ddata){
                                    $this->xlsWriteLabel($currentrow, $colcellmulti++, utf8_decode($ddata));
                                }
                            } else{
                                // daca e coloana de date
                                $this->xlsWriteLabel($currentrow, $colcellmulti++, utf8_decode($valCell));
                            }
                          
                        }
                        $all[$key_date][] = $currentrow;
                        $currentrow++;
                        if ($s>0 ){
                            $insnewrow++;
                            
                        }
                        
                    }
                    
                }
                else
                {
                    $value = str_replace("<br />", "\n", $value);
                    $this->xlsWriteLabel($line, $disp_col_nr, utf8_decode($value));
                }
            }
            if($all[$key_date]){
                $line = max( $all[$key_date]);
            }
            $line++;
            $row_nr++;
        }
        
        if(!empty($report_columns_avg)){
            $this->xlsWriteNumber($line, 0 , "Durchschnitt");
            
            foreach($report_columns as $ck => $column)
            {   
                $cknumber = ord($ck) -65 ;
                if($column['report_show_average'] == "1")
                {
                    $this->xlsWriteNumber($line, $cknumber,  $data['data_extra'][$column['column']]['average'] );
                }
            }
        }
        $line++;
        if(!empty($report_columns_median)){
            $this->xlsWriteNumber($line, 0 , "Median");
            
            
            foreach($report_columns as $ck => $column)
            {
                $cknumber = ord($ck) -65 ;
                if($column['report_show_median'] == "1")
                {
                    $this->xlsWriteNumber($line, $cknumber,  $data['data_extra'][$column['column']]['median'] );
                }
            }
        }
        
        
        
        
        $this->xlsEOF();
        
        $file = str_replace(" ", "_", $report_name);
        $fileName = $file . ".xls";
        
        
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=" . $fileName);
        exit;
    }
    
    
    
    /* PHPDOCX WORD AND PDF END */
    private function xlsBOF()
    {
        echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
        return;
    }
    
    private function xlsEOF()
    {
        echo pack("ss", 0x0A, 0x00);
        return;
    }
    
    private function xlsWriteNumber($Row, $Col, $Value)
    {
        echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
        echo pack("d", $Value);
        return;
    }
    
    private function xlsWriteLabel($Row, $Col, $Value)
    {
        $L = strlen($Value);
        echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
        echo $Value;
        return;
    }
    
    
        
  
    
    
}
?>
