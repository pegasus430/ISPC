<?php
/**
 * 
 * @author claudiu 
 * May 10, 2018
 * 
 * ! be aware ! i've setup the viewRenderer to use .phtml !
 *
 */
class ProjectsController extends Pms_Controller_Action {
    
    
    private $_date_format_datepicked = Zend_Date::DAY.'.'.Zend_Date::MONTH.'.'.Zend_Date::YEAR;
    private $_date_format_datetime = Zend_Date::DAY.'.'.Zend_Date::MONTH.'.'.Zend_Date::YEAR . " ". Zend_Date::HOUR.":".Zend_Date::MINUTE;
    private $_date_format_db = Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY;
    
    /**
     * 
     * @var Projects->toArray()
     */
    private $_asserted_project = null;
    
    public function init()
    {
	    //phtml is the default for zf1 ... but on bootstrap you manualy set html :(
        $this->getHelper('viewRenderer')->setViewSuffix('phtml');
        
        array_push($this->actions_with_js_file, "overview");
        //array_push($this->actions_with_patientinfo_and_tabmenus, "overview");
    
    }
    
    
    public function overviewAction()
    {
        
        $step = null;
        if ($this->getRequest()->isPost()) {
            $step = $this->getRequest()->getPost('step', null);
        } 
        if (is_null($step)) {
            $step = $this->getRequest()->getParam('step');
        }
        
        
        $this->_populateCurrentMessages();
        
        $this->view->selected_tab = $this->getRequest()->getParam('selected_tab') ? $this->getRequest()->getParam('selected_tab') : $step;
        
        switch ($step) {
            
            case "fetch_projects_list" :
                $this->_overview_fetch_projects_list();
                break;
                
            case "edit_project" :
            case "add_new_project" : // edit project uses the same fn, it just adds to the post the project_ID
                $this->_overview_add_new_project();
                break;
                
            case "delete_project" :
                $this->_overview_delete_project();
                break;
                
            case "view_project" :
                $this->_overview_view_project();
                break;
                
            case "export_project" :
                $this->_overview_export_project();
                break;
                
            case "add_project_work" :
                $this->_overview_add_project_work();
                break;
                
            case "add_project_files" :
                $this->_overview_add_project_files();
                break;
            
            case "add_project_comments" :
                $this->_overview_add_project_comments();
                break;
                
            case "delete_project_entry" :
                $this->_overview_delete_project_entry();
                break;
            
            case "add_project_outside_participant" :
                $this->_overview_add_project_outside_participant();
            default:
                break;
        }
                       
        
        
    }
    
    
    private function _populateCurrentMessages()
    {
        $this->view->SuccessMessages = array_merge(
            $this->_helper->flashMessenger->getMessages('SuccessMessages'),
            $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
        );
        $this->view->ErrorMessages = array_merge(
            $this->_helper->flashMessenger->getMessages('ErrorMessages'),
            $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
        );
        
        $this->_helper->flashMessenger->clearMessages('ErrorMessages');
        $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');
        
        $this->_helper->flashMessenger->clearMessages('SuccessMessages');
        $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
    }
    
    
    private function _assert_correct_project_ID($project_ID = 0 , $client_id = 0)
    {
        if (empty($project_ID)) {
            return false;
        }
        
        if (empty($client_id)) {
            $client_id = $this->logininfo->clientid;
        }
        
        
        $p_obj = new Projects(); 
        $project = $p_obj->getTable()->createQuery('p')
        ->select('*')
        ->where('project_ID = ?' , $project_ID)
        ->andwhere('client_id = ?' , $client_id)
        ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY)
        ;
        
        $this->_asserted_project = $project;
        
        if ( empty($project)) {
            return false;
        } else {
            return true;
        }
        
    }
    
    
    private function _overview_add_project_work()
    {
        if ( ! $this->getRequest()->isPost()) {
            return false;
        }
        
        $project_ID = $this->getRequest()->getPost('project_ID');
        
        if ( ! $this->_assert_correct_project_ID($project_ID)) {
            throw new Zend_Exception($this->translate('[project not found, please contact admin]'), 0);
        }
        
        
        $post =  $this->getRequest()->getPost();
        
        $post['client_id'] = $this->logininfo->clientid; //force cleintid to be the same with the loghend in one
        $post['step'] = 'add_project_work';
        
        $af_p = new Application_Form_Projects();
        
        if ($af_p->isValid($post)) {
            $r = $af_p->save_form_add_project_work($post);
            $this->_helper->flashMessenger->addMessage( $this->translate('message_info_suc'),  'SuccessMessages');
            
        } else {
            $errors = $af_p->getErrorMessages();
            $errors =  implode("\n", $errors);
            $this->_helper->flashMessenger->addMessage( $this->translate('message_info_err'),  'ErrorMessages');
            $this->_helper->flashMessenger->addMessage( $errors,  'ErrorMessages');
        }
        
        
        $this->redirect(
            APP_BASE .  $this->getRequest()->getControllerName(). "/". $this->getRequest()->getActionName()
            . "?step=view_project"
            . "&project_ID=" . $project_ID
            . "&selected_tab=" . $post['step'],
            array("exit" => true));        
        exit; //for readability
    }
    
    
    private function _overview_add_project_outside_participant() 
    {
        if ( ! $this->getRequest()->isPost()) {
            return false;
        }
        
        $project_ID = $this->getRequest()->getPost('project_ID');
        
        if ( ! $this->_assert_correct_project_ID($project_ID)) {
            throw new Zend_Exception($this->translate('[project not found, please contact admin]'), 0);
        }
        
        
        $post =  $this->getRequest()->getPost();
        
        $post['client_id'] = $this->logininfo->clientid; //force cleintid to be the same with the loghend in one
        $post['step'] = 'add_project_outside_participant';
        
        $af_p = new Application_Form_Projects();
        
        if ($af_p->isValid($post)) {
            $r = $af_p->save_form_add_project_outside_participants($post);
            $this->_helper->flashMessenger->addMessage( $this->translate('message_info_suc'),  'SuccessMessages');
        
        } else {
            $errors = $af_p->getErrorMessages(1);
            $errors =  implode("\n", $errors);
            $this->_helper->flashMessenger->addMessage( $this->translate('message_info_err'),  'ErrorMessages');
            $this->_helper->flashMessenger->addMessage( $errors,  'ErrorMessages');
        }
        
        
        $this->redirect(
            APP_BASE .  $this->getRequest()->getControllerName(). "/". $this->getRequest()->getActionName()
            . "?step=view_project"
            . "&project_ID=" . $project_ID
            . "&selected_tab=" . $post['step'],
            array("exit" => true));
        exit; //for readability
    }
    
    
    
    
    
    
    private function _overview_add_project_files()
    {
        if ( ! $this->getRequest()->isPost()) {
            return false;
        }
        
        $project_ID = $this->getRequest()->getPost('project_ID');
        
        if ( ! $this->_assert_correct_project_ID($project_ID)) {
            throw new Zend_Exception($this->translate('[project not found, please contact admin]'), 0);
        }
        
        
        $action = Projects::CLIENT_FILES_TABNAME . "_" . $project_ID; //this must match the one defined in html or js (upload action name)
        
        $post =  $this->getRequest()->getPost();
        $post['client_id'] = $this->logininfo->clientid; //force cleintid to be the same with the loghend in one
        $post['step'] = 'add_project_files';
        
        $af_p = new Application_Form_Projects();
        
        $success = false;
        $errors = '';
        
        
        if ($af_p->isValid($post)) {
            
            $af_cfu = new Application_Form_ClientFileUpload();
            
            $recordid = $post['project_ID'];            
            $clientid = $post['client_id'];
            $folder_id = isset($post['folder_id']) ? $post['folder_id'] : 0;
            $qquid_s = $post['qquuid'];
            $qquuid_title_s = $post['qquuid_title'];

            
            foreach ( $qquid_s as $k => $qquid) {
            
                $uploadedFile = $this->get_last_uploaded_file($action, $qquid, $clientid);
            
                $uploadedFile = $uploadedFile[$qquid];
            
                if ( ! $uploadedFile || ! $uploadedFile['isZipped']) {
                    continue;
                }
            
                $file_name = pathinfo($uploadedFile['filepath'], PATHINFO_FILENAME) . "/" . $uploadedFile['fileInfo']['name'];
            
                $data2save = array(
                    'clientid' => $uploadedFile['clientid'],
                    'title' => ! empty($qquuid_title_s[$k]) ? $qquuid_title_s[$k] : pathinfo($uploadedFile['filename'], PATHINFO_FILENAME),
                    'file_type' => strtoupper(pathinfo($uploadedFile['filename'], PATHINFO_EXTENSION)),
                    'file_name' => $file_name,
                    'folder' => $folder_id,
                    'tabname' => Projects::CLIENT_FILES_TABNAME,
                    'recordid' => $recordid,
                    'parent_id' => null,
                );
            
                $record = $af_cfu->InsertNewRecord($data2save);
                 
                if ($record->id) {
                    $ftp_put_queue_result = Pms_CommonData::ftp_put_queue(
                        $uploadedFile['filepath'],
                        $uploadedFile['legacy_path'],
                        array(
                            "is_zipped" => true,
                            "file_name" => $file_name,
                            "insert_id" => $record->id,
                            "db_table" => "ClientFileUpload"
                        ),
                        $foster_file = false,
                        $uploadedFile['clientid'],
                        $uploadedFile['filepass']
                    );
            
                    if ($ftp_put_queue_result) {
                        //delete the file from uploaded location... it is now in the ft_que_path
                        $this->delete_last_uploaded_file($action, $qquid, $clientid);
                    }
            
                }
            }
            
            $this->_helper->flashMessenger->addMessage( $this->translate('message_info_suc'),  'SuccessMessages');
            
        } else {
            $errors = $af_p->getErrorMessages();
            $errors =  implode("\n", $errors);
            $this->_helper->flashMessenger->addMessage( $this->translate('message_info_err'),  'ErrorMessages');
            $this->_helper->flashMessenger->addMessage( $errors,  'ErrorMessages');
        }
        
        $this->redirect(
            APP_BASE .  $this->getRequest()->getControllerName(). "/". $this->getRequest()->getActionName()
            . "?step=view_project"
            . "&project_ID=" . $project_ID
            . "&selected_tab=" . $post['step'],
            array("exit" => true));        
        exit; //for readability
    }
    
    private function _overview_add_project_comments()
    {
        if ( ! $this->getRequest()->isPost()) {
            return false;
        }
        
        $project_ID = $this->getRequest()->getPost('project_ID');
        
        if ( ! $this->_assert_correct_project_ID($project_ID)) {
            throw new Zend_Exception($this->translate('[project not found, please contact admin]'), 0);
        }
        
        $post =  $this->getRequest()->getPost();
        $post['client_id'] = $this->logininfo->clientid; //force cleintid to be the same with the loghend in one
        $post['step'] = 'add_project_comments';
        
        $af_p = new Application_Form_Projects();
        
        $success = false;
        $errors = '';
        
        if ($af_p->isValid($post)) {
            $r = $af_p->save_form_add_project_comments($post);
            $this->_helper->flashMessenger->addMessage( $this->translate('message_info_suc'),  'SuccessMessages');
        } else {
            $errors = $af_p->getErrorMessages();
            $errors =  implode("\n", $errors);
            $this->_helper->flashMessenger->addMessage( $this->translate('message_info_err'),  'ErrorMessages');
            $this->_helper->flashMessenger->addMessage( $errors,  'ErrorMessages');
        }
        
        $this->redirect(
            APP_BASE .  $this->getRequest()->getControllerName(). "/". $this->getRequest()->getActionName()
            . "?step=view_project"
            . "&project_ID=" . $project_ID
            . "&selected_tab=" . $post['step'],
            array("exit" => true));        
        exit; //for readability
    }
        
    
    private function _overview_delete_project_entry() 
    {
        //this uses GET
        $project_ID = $this->getRequest()->getParam('project_ID');
        $type = $this->getRequest()->getParam('type');
        $row_ID = $this->getRequest()->getParam('row_ID');
        
        if ( ! $this->_assert_correct_project_ID($project_ID)) {
            throw new Zend_Exception($this->translate('[project not found, please contact admin]'), 0);
        }
        
        if ( ! in_array($type, array('ProjectParticipants', 'ProjectComments', 'ProjectFiles', 'ProjectOutsideParticipants'))) {
            throw new Zend_Exception($this->translate('[project entry not found, please contact admin]'), 0);
        }
        
        $data = array(
            'project_ID' => $project_ID,
            'client_id' => $this->logininfo->clientid
        );
        
        $af_p = new Application_Form_Projects();
        switch ($type) {
            case 'ProjectParticipants':
                $data['project_participant_ID'] = $row_ID;
                $af_p->delete_ProjectParticipants($data);
                break;
                
            case 'ProjectComments':
                $data['project_comment_ID'] = $row_ID;
                $af_p->delete_ProjectComments($data);
                break;
                
            case 'ProjectOutsideParticipants':
                $data['project_outside_participant_ID'] = $row_ID;
                $af_p->delete_ProjectOutsideParticipants($data);
                break;
        }
        
        $this->_helper->flashMessenger->addMessage( $this->translate('message_info_suc'),  'SuccessMessages');
        
        
        $selected_tab = $this->getRequest()->getParam('selected_tab') ? $this->getRequest()->getParam('selected_tab') : '';
        
        
        $this->redirect(
            APP_BASE .  $this->getRequest()->getControllerName(). "/". $this->getRequest()->getActionName()
            . "?step=view_project"
            . "&project_ID=" . $project_ID
            . "&selected_tab=" . $selected_tab,
            array("exit" => true));
        
        exit; //for readability
    }
    
    /**
     * fn was created with the ideea that at a later date you will export multiple projects in the same xls
     * for now it is just one
     */
    private function _overview_export_project()
    {
        $project_IDs = $this->getRequest()->getParam('project_ID');
        $project_IDs = explode(',', $project_IDs);
        $project_IDs = array_filter($project_IDs, 'is_numeric');
        
        $projects = array();
        
        foreach ($project_IDs as $project_ID) {
            $this->getRequest()->setParam('project_ID', $project_ID);
            $project = $this->_overview_view_project();
            array_push($projects, $project);
        }
        
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        
        $cnt = 0;
        
        $total_projects = count($projects);
        
        foreach ($projects as $project) {
            
            $this->_overview_excel_sheet_header($xls, $project);
    
            $this->_overview_excel_sheet_fill($xls, $project);
            
            $this->_overview_excel_sheet_footer($xls, $project);
            
            $cnt++;
            
            if ($total_projects > $cnt) {
//             if (next($projects)==true) {
                $xls->createSheet();
                $xls->setActiveSheetIndex($cnt);
            }
            
        }
        
        
        $xls->setActiveSheetIndex(0);
        
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"{$this->translate("[Projects Export.XLS]")}.xls\"");
        header("Cache-Control: max-age=0");
        
        $objWriter = PHPExcel_IOFactory::createWriter($xls, "Excel5");
        $objWriter->save("php://output");
        exit;
        dd($project);
        
    }
    
    private function _overview_excel_sheet_header (PHPExcel $xls, $project = array()) 
    {
        $sheet = $xls->getActiveSheet();
        
        $sheetTitle = strlen($project['name']) > 31 ? substr($project['name'], 0, 28) . ".." : $project['name'];
        
        $sheet->setTitle($sheetTitle);
        
        $sheet->getRowDimension(1)->setRowHeight(20);
        
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle("A1")->getFont()->setSize(16);
        $sheet->setCellValue('A1', $project['name']);
        
        $sheet->getStyle('A1')->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'aed0ea')
                )
            )
        );
        

        $row2 = ! empty($this->project['description']) ? $project['description'] . "\n" : '';
        $row2 .= $this->translate('[Projects Open]') . ": " . $project['open_from'] . ' - ' . $project['open_till'] . "\n";
        $row2 .= $this->translate('[Projects Prepare]') . ": " . $project['prepare_from'] . ' - ' . $project['prepare_till'] . "\n";
        $row2 .= $this->translate('[Projects Created on]') . ": " . $project['create_date'] . " " . $this->translate('by') . ": " . $project['create_user_nice_name'] . "\n";
             
        $sheet->mergeCells('A2:G2');
        $sheet->getRowDimension(2)->setRowHeight(60);
        $sheet->setCellValue('A2', $row2);
        
        $sheet->getStyle('A2')->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'F8F8F8')
                )
            )
        );
        
        
        
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        
        return $xls;
    }
    
    private function _overview_excel_sheet_fill (PHPExcel $xls, $project = array())
    {
        $blackBold = array(
            "font" => array(
                "bold" => true,
                "color" => array("rgb" => "000"),
            ),
        );
        
        $sheet = $xls->getActiveSheet();
        
        $row_start = 3;

        $sheet->setCellValue("A{$row_start}", 'Nr')
        ->setCellValue("B{$row_start}", $this->translate('[XLS Project Work done by]'))
        ->setCellValue("C{$row_start}", $this->translate('[XLS Project work done]'))
        ->setCellValue("D{$row_start}", $this->translate('[XLS Project date]'))
        ->setCellValue("E{$row_start}", $this->translate('[XLS Project duration]'))
        ->setCellValue("F{$row_start}", $this->translate('[XLS Project driving distance]'))
        ->setCellValue("G{$row_start}", $this->translate('[XLS Project driving time]'));
        
        $sheet->getStyle("A{$row_start}")->applyFromArray($blackBold);
        $sheet->getStyle("B{$row_start}")->applyFromArray($blackBold);
        $sheet->getStyle("C{$row_start}")->applyFromArray($blackBold);
        $sheet->getStyle("D{$row_start}")->applyFromArray($blackBold);
        $sheet->getStyle("E{$row_start}")->applyFromArray($blackBold);
        $sheet->getStyle("F{$row_start}")->applyFromArray($blackBold);
        $sheet->getStyle("G{$row_start}")->applyFromArray($blackBold);
        
        $row_start = 4;
        
        $cnt = 0;
        foreach ($project['ProjectParticipants'] as $row) {
            
            $sheet->setCellValue("A{$row_start}", ++$cnt)
            ->setCellValue("B{$row_start}", $row['participant_nice_name'])
            ->setCellValue("C{$row_start}", $row['work_description'])
            ->setCellValue("D{$row_start}", date('d.m.Y', strtotime($row['work_date'])))
            ->setCellValue("E{$row_start}", $row['work_duration'])
            ->setCellValue("F{$row_start}", $row['work_driving_distance'])
            ->setCellValue("G{$row_start}", $row['work_driving_time']);
            
            $row_start++;
        }
        
        $row_start++;
        $row_start++;
        
      
        if (! empty($project['ProjectComments'])) {
            
            $sheet->mergeCells("A{$row_start}:D{$row_start}");
            $sheet->setCellValue("A{$row_start}", $this->translate('[XLS Projects Comments]'));
            $sheet->getStyle("A{$row_start}")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'F8F8F8')
                    ),
                    "font" => array(
                        "bold" => true,
                        "color" => array("rgb" => "000"),
                    ),
                )
            );
            
            $row_start++;
            foreach ($project['ProjectComments'] as $row) {
                
                $sheet->setCellValue("A{$row_start}", '')
                ->setCellValue("B{$row_start}", $row['create_date_formated'])
                ->setCellValue("C{$row_start}", $row['create_user_nice_name'])
                ->setCellValue("D{$row_start}", $row['comment']);
                
                $row_start++;
            }
        }
        
        
        $row_start++;
        $row_start++;
        
        $count_out = 0;
        
        
        
        
        
        if (! empty($project['ProjectOutsideParticipants'])) {
        
            $sheet->mergeCells("A{$row_start}:M{$row_start}");
            $sheet->setCellValue("A{$row_start}", $this->translate('[XLS Projects Outside Participants]'));
            $sheet->getStyle("A{$row_start}")->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'F8F8F8')
                    ),
                    "font" => array(
                        "bold" => true,
                        "color" => array("rgb" => "000"),
                    ),
                )
            );
            
            
            $row_start++;
            
            $sheet->setCellValue("A{$row_start}", 'Nr')
            ->setCellValue("B{$row_start}", $this->translate('first_name'))
            ->setCellValue("C{$row_start}", $this->translate('last_name'))
            ->setCellValue("D{$row_start}", $this->translate('title_prefix'))
            ->setCellValue("E{$row_start}", $this->translate('title_suffix'))
            ->setCellValue("F{$row_start}", $this->translate('salutation'))
            ->setCellValue("G{$row_start}", $this->translate('street'))
            ->setCellValue("H{$row_start}", $this->translate('zip'))
            ->setCellValue("I{$row_start}", $this->translate('city'))
            ->setCellValue("J{$row_start}", $this->translate('email'))
            ->setCellValue("K{$row_start}", $this->translate('mobile'))
            ->setCellValue("L{$row_start}", $this->translate('phone'))
            ->setCellValue("M{$row_start}", $this->translate('comment'));
            
            $sheet->getStyle("A{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("B{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("C{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("D{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("E{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("F{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("G{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("H{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("I{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("J{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("K{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("L{$row_start}")->applyFromArray($blackBold);
            $sheet->getStyle("M{$row_start}")->applyFromArray($blackBold);
            
            $row_start++;
            
            foreach ($project['ProjectOutsideParticipants'] as $row) {
        
                $sheet->setCellValue("A{$row_start}", ++$count_out)
                ->setCellValue("B{$row_start}", $row['first_name'])
                ->setCellValue("C{$row_start}", $row['last_name'])
                ->setCellValue("D{$row_start}", $row['title_prefix'])
                ->setCellValue("E{$row_start}", $row['title_suffix'])
                ->setCellValue("F{$row_start}", $row['salutation'])
                ->setCellValue("G{$row_start}", $row['street'])
                ->setCellValue("H{$row_start}", $row['zip'])
                ->setCellValue("I{$row_start}", $row['city'])
                ->setCellValue("J{$row_start}", $row['email'])
                ->setCellValue("K{$row_start}", $row['mobile'])
                ->setCellValue("L{$row_start}", $row['phone'])
                ->setCellValue("M{$row_start}", $row['comment']);
        
                $row_start++;
            }
        }
        
        
        
        
        
        return $xls;
    }
    
    private function _overview_excel_sheet_footer (PHPExcel $xls, $project = array())
    {
        return $xls;
    }
    
    
    //because ClientFileUpload as ProjectFiles doesn NOT use Softdelete behaviour... i had to re-edit SoftdeleteListener
    private function _overview_view_project()
    {
        $this->_helper->viewRenderer('overview-view-project');
        
        $p_obj = new Projects(); //obj used as table
        $project = $p_obj->getTable()->createQuery('p')
        ->select('
            p.*, 
            pc.*,
            pp.*,
            pf.*,
            pop.*,
            AES_DECRYPT(pf.title, :aesKey) AS pf.title,
            AES_DECRYPT(pf.file_name, :aesKey) pf.file_name,
            AES_DECRYPT(pf.file_type, :aesKey) pf.file_type
            ')
        ->leftJoin('p.ProjectComments pc')
        ->leftJoin('p.ProjectParticipants pp')
        ->leftJoin('p.ProjectOutsideParticipants pop')
        ->leftJoin("p.ProjectFiles pf ON (pf.recordid=p.project_ID AND pf.clientid=:clientID AND pf.tabname = :fileTabname and pf.isdeleted = 0)")
        ->where('p.project_ID = :projectID')
        ->andwhere('p.client_id = :clientID')
        ->fetchOne(array(
            "aesKey" => Zend_Registry::get('salt'),
            "fileTabname" => Projects::CLIENT_FILES_TABNAME,
            "projectID" => (int)$this->getRequest()->getParam('project_ID'),
            "clientID" =>  $this->logininfo->clientid
        ), Doctrine_Core::HYDRATE_ARRAY)
        ;
        
        /*
         * //filter by participant
         * ->leftJoin('p.ProjectParticipants pp ON pp.project_ID = p.project_ID AND pp.project_ID = :projectID AND pp.participant_type = :participantType')
         * ->andwhere('pp.project_participant_ID IS NOT NULL')
         * $participantType = 'user'|'voluntaryworker'
        */
        
        if ( empty($project)) {
            throw new Zend_Exception($this->translate('[project not found, please contact admin]'), 0);
        }

        $date = new Zend_Date($project['open_from'], $this->_date_format_db);
        $project['open_from']  = $date->toString($this->_date_format_datepicked);
        
        if ( ! empty($project['open_till'])) {
            $date = new Zend_Date($project['open_till'], $this->_date_format_db);
            $project['open_till']  = $date->toString($this->_date_format_datepicked);
        }
        
        $date = new Zend_Date($project['prepare_from'], $this->_date_format_db);
        $project['prepare_from']  = $date->toString($this->_date_format_datepicked);
        
        $date = new Zend_Date($project['prepare_till'], $this->_date_format_db);
        $project['prepare_till']  = $date->toString($this->_date_format_datepicked);
        
        

        $date = new Zend_Date($project['create_date']);
        $project['create_date']  = $date->toString($this->_date_format_datepicked);
        
        
        $users_id = array($project['create_user']);
        $vws_id =  array();
        
        if ( ! empty($project['ProjectParticipants'])) {
            $users = array_filter($project['ProjectParticipants'], function($user) {
                return $user['participant_type'] == 'user';
            });
            $users_id = array_column($users, 'participant_id');
            
            $users_id = array_merge($users_id, array_column($project['ProjectParticipants'], 'create_user'));
            
            $voluntaryworkers = array_filter($project['ProjectParticipants'], function($user) {
                return $user['participant_type'] == 'voluntaryworker';
            });
            $vws_id = array_column($voluntaryworkers, 'participant_id');    
        }
        
        if ( ! empty($project['ProjectComments'])) {
            $users_id = array_merge($users_id, array_column($project['ProjectComments'], 'create_user'));
        }
        
        if ( ! empty($project['ProjectFiles'])) {
            $users_id = array_merge($users_id, array_column($project['ProjectFiles'], 'create_user'));
        }
        
        if ( ! empty($project['ProjectOutsideParticipants'])) {
            $users_id = array_merge($users_id, array_column($project['ProjectOutsideParticipants'], 'create_user'));
        }

        
        $users = User::getUsersNiceName($users_id);
        $voluntaryworkers = Voluntaryworkers::getVoluntaryworkersNiceName($vws_id);
        
        $project['create_user_nice_name'] = $users[$project['create_user']] ['nice_name'];
        
        
        if ( ! empty($project['ProjectFiles'])) {
            foreach ($project['ProjectFiles'] as &$row) {
                $row['create_user_nice_name'] = $users[$row['create_user']] ['nice_name'];

                $date = new Zend_Date($row['create_date']);
                $row['create_date_formated']  = $date->toString($this->_date_format_datetime);
                
                $row['__type'] = 'ProjectFiles';
            }
            usort($project['ProjectFiles'], array(new Pms_Sorter('create_date'), "_date_compare"));
            
        }
        if ( ! empty($project['ProjectParticipants'])) {
            foreach ($project['ProjectParticipants'] as &$row) {
                
                switch ($row['participant_type']) {
                    case "user" :
                        $row['participant_nice_name'] = $users[$row['participant_id']] ['nice_name'] . " (".$this->translate('user').")";
                        break;
                    case "voluntaryworker" :
                        $row['participant_nice_name'] = $voluntaryworkers[$row['participant_id']] ['nice_name'] . " (".$this->translate('voluntaryworker').")";
                        break;
                    case "manual" :
                        $row['participant_nice_name'] = $row['participant_name'];
                        break;
                }
                
                $row['create_user_nice_name'] = $users[$row['create_user']] ['nice_name'];
               
                if ( ! empty($row['create_date'])) {
                    $date = new Zend_Date($row['create_date']);
                    $row['create_date_formated']  = $date->toString($this->_date_format_datetime);
                }
                
                $row['work_date_4usort'] = $row['work_date'];
                if ( ! empty($row['work_date'])) {
                    $date = new Zend_Date($row['work_date']);
                    $row['work_date']  = $date->toString($this->_date_format_datepicked);
                }
                
                $row['__type'] = 'ProjectParticipants';
            }
            usort($project['ProjectParticipants'], array(new Pms_Sorter('work_date_4usort'), "_date_compare"));
        }
        if ( ! empty($project['ProjectComments'])) { 
            foreach ($project['ProjectComments'] as &$row) {
                $row['create_user_nice_name'] = $users[$row['create_user']] ['nice_name'];
                
                $date = new Zend_Date($row['create_date']);
                $row['create_date_formated']  = $date->toString($this->_date_format_datetime);
                
                $row['__type'] = 'ProjectComments';
            }
        }
        
        
        
        if ( ! empty($project['ProjectOutsideParticipants'])) {
            
            ProjectOutsideParticipants::beautifyName($project['ProjectOutsideParticipants']);
            
            foreach ($project['ProjectOutsideParticipants'] as &$row) {
                $row['create_user_nice_name'] = $users[$row['create_user']] ['nice_name'];
        
                $date = new Zend_Date($row['create_date']);
                $row['create_date_formated']  = $date->toString($this->_date_format_datetime);
        
                $row['__type'] = 'ProjectOutsideParticipants';
            }
        }
        
        $ProjectCourse =  array_merge($project['ProjectComments'], $project['ProjectFiles'], $project['ProjectParticipants'], $project['ProjectOutsideParticipants']);
        $project['ProjectCourse'] = $ProjectCourse;
        
        usort($project['ProjectCourse'], array(new Pms_Sorter('create_date'), "_date_compare"));
        
        $this->view->project = $project;
        
        return $project;
    }
    
    /**
     * TODO : restrict delete/edit for the closed projects, now i've added this only in js
     * @throws Zend_Exception
     */
    private function _overview_delete_project() 
    {
//         if ( ! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
//             throw new Zend_Exception('!isXmlHttpRequest', 0);
//         }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        $project_ID = $this->getRequest()->getParam('project_ID');
        
        if ( ! $this->_assert_correct_project_ID($project_ID)) {
            throw new Zend_Exception($this->translate('[project not found, please contact admin]'), 0);
        }
        
        $post = array(
            'project_ID' => $project_ID ,
            'client_id' => $this->logininfo->clientid
        );
        
        $af_p = new Application_Form_Projects();
        $r = $af_p->delete_Project($post);
        
        $this->_helper->flashMessenger->addMessage( $this->translate('message_info_suc'),  'SuccessMessages');
        
        $this->redirect(
            APP_BASE .  $this->getRequest()->getControllerName(). "/". $this->getRequest()->getActionName()
            . "?selected_tab=" . $post['selected_tab'],
            array("exit" => true));
        
        exit; //for readability
    }
    
    
    /**
     * projects prepare_from - prepare_till dates are calculated by me
     * 
     * @throws Exception
     */
    private function _overview_add_new_project()
    {
        if ( ! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();
        
        $post =  $this->getRequest()->getPost();
        $post['client_id'] = $this->logininfo->clientid; //force cleintid to be the same with the loghend in one
        
        
        $project_ID = $this->getRequest()->getPost('project_ID');
        if ( ! empty($project_ID)) {
            //edit the project... so verify it this is yours
            if ( ! $this->_assert_correct_project_ID($project_ID)) {
                throw new Zend_Exception($this->translate('[project not found, please contact admin]'), 0);
            }
            
            $edited_project = $this->_asserted_project;
            
            $prepare_from = new Zend_Date($edited_project['prepare_from'], $this->_date_format_db);
            $prepare_till = new Zend_Date($edited_project['prepare_till'], $this->_date_format_db);
            
            $open_from = new Zend_Date($post['open_from'], $this->_date_format_datepicked);
//             $open_till = new Zend_Date($post['open_till'], $this->_date_format_datepicked);
            
            $post['prepare_from'] = ($prepare_from->compareDate($open_from) == 1) ? $post['open_from'] : $prepare_from->toString($this->_date_format_datepicked);
//             $post['prepare_till'] = ($prepare_till->compareDate($open_till) == 1) ? $post['open_till'] : $prepare_till->toString($this->_date_format_datepicked);
            $post['prepare_till'] = ($prepare_till->compareDate($open_from) == 1) ? $post['open_from'] : $prepare_till->toString($this->_date_format_datepicked);
            
            
        } else {
            //this is a a new projects insert
            //setup prepare from-till 
            //if ( ! empty($post['open_from']) && ! empty($post['open_till'])) {
            if ( ! empty($post['open_from'])) {
                $date = new Zend_Date($post['open_from'], $this->_date_format_datepicked);
                $today = new Zend_Date();
                
                $post['prepare_from'] = $date->isLater($today) ? $today->toString($this->_date_format_datepicked) : $post['open_from'];
                $post['prepare_till'] = $post['open_from'];
            }
            
        }
        
        $af_p = new Application_Form_Projects();
        
        $success = false;
        $errors = '';
        
        if ($af_p->isValid($post)) {
            $r = $af_p->save_Project($post);
            $success = true;
        } else {
            $success = false;
        	$errors = $af_p->getErrorMessages();
        	$errors =  implode("<br/>", $errors);
        }
        
        $result = array(
            'success' => $success,
            'errors' => $errors,
            'redrawTab' => true,
        );
        
        $this->_helper->getHelper('json')->sendJson($result);
        exit; //for readability
        
    }
    
    private function _overview_fetch_projects_list()
    {
        
        if ( ! $this->getRequest()->isXmlHttpRequest() && APPLICATION_ENV != 'development') {
            throw new Zend_Exception('!isXmlHttpRequest', 0);
        }
        
        $this->_helper->layout->setLayout('layout_ajax');
        $this->_helper->viewRenderer->setNoRender();

        $sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
        $sort_col_dir = $sort_col_dir == 'asc' ? 'ASC' : 'DESC';
        
        $sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
        $sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
        
        $limit = $this->getRequest()->getPost('iDisplayLength');
        $offset = $this->getRequest()->getPost('iDisplayStart');
        
        $order_by = '';
        
        $p_obj = new Projects(); //obj used as table
        
        
        if ( ! empty($sort_col_name) && $p_obj->getTable()->hasColumn($sort_col_name)) {
            $order_by = $sort_col_name . ' ' . $sort_col_dir;
        }
        
        $projects = $p_obj->getTable()->createQuery('p');
        $projects->select('*');
        $projects->where('client_id = ?' , $this->logininfo->clientid);
        
        switch ($this->getRequest()->getPost('project_status')) {
            
            case "projects_open":
                $projects->andWhere('open_from <= CURDATE()');
                $projects->andWhere('(open_till IS NULL OR open_till >= CURDATE())');
                break;
                
            case "projects_prepare":
                
//                 $projects->andWhere('(open_from > CURDATE()  OR open_till < CURDATE())');
                $projects->andWhere('open_from > CURDATE()');
                $projects->andWhere('prepare_from <= CURDATE()');
                $projects->andWhere('prepare_till >= CURDATE()');
                break;
                
            case "projects_closed":
//                 $projects->andWhere('(open_from > CURDATE()  OR open_till < CURDATE())');
                $projects->andWhere('open_till IS NOT NULL');
                $projects->andWhere('open_till < CURDATE()');
//                 $projects->andWhere('(prepare_from > CURDATE()  OR prepare_till < CURDATE())');
                break;
                
        }
        
        
        if ( ! empty($order_by)) {
            $projects->orderBy($order_by);
        }
        
        if ( ! empty($limit)) {
            $projects->limit((int)$limit);
        }
        
        if ( ! empty($offset)) {
            $projects->offset((int)$offset);
        }
        
        $prjs = $projects->fetchArray();
        
        
        //count total projects
        if ( ! empty($limit)) {
            $total_projects = $projects->count();
        } else {
            $total_projects = count($prjs);
        }
        
        
        $all_projects = array();
        
        foreach ($prjs as $row) {
            
            $date = new Zend_Date($row['open_from'], $this->_date_format_db);
            $row['open_from']  = $date->toString($this->_date_format_datepicked);
            
            if ( ! empty($row['open_till'])) {
                $date = new Zend_Date($row['open_till'], $this->_date_format_db);
                $row['open_till']  = $date->toString($this->_date_format_datepicked);
            }
            
            $date = new Zend_Date($row['prepare_from'], $this->_date_format_db);
            $row['prepare_from']  = $date->toString($this->_date_format_datepicked);
            
            $date = new Zend_Date($row['prepare_till'], $this->_date_format_db);
            $row['prepare_till']  = $date->toString($this->_date_format_datepicked);
            
            $data =  array(
                'debug'             => '1', //add debug info on devmode
                'project_ID'        => $row['project_ID'], //int 
                'name'              => $row['name'], //string
                'description'       => $row['description'], //string
                'project_status'    => $this->getRequest()->getPost('project_status'), //enum, data NOT used
                'open_from'         => $row['open_from'], //date
                'open_till'         => $row['open_till'], //date
                'prepare_from'      => $row['prepare_from'], //date
                'prepare_till'      => $row['prepare_till'], //date                
            );
            array_push($all_projects, $data);
            
        }
        
        
        $result = array(
            'draw' => $this->getRequest()->getPost('sEcho'),
            'recordsTotal' =>  $total_projects,
            'recordsFiltered' => $total_projects, //TODO add a search in the datatable
            'data' => $all_projects
            
        );
        
        $this->_helper->getHelper('json')->sendJson($result);
        exit; //for readability
	}
	
	
	
}