<?php
/**
 * // Maria:: Migration ISPC to CISPC 08.08.2020
 * @author Ancuta
 * 10.01.2020
 * ISPC-2432 Ancuta 13.01.2020
 *
 * ! be aware ! i've setup the viewRenderer to use .phtml !
 *
 */

require_once 'Com/Tecnick/Barcode/autoload.php';
require_once 'Com/Tecnick/Color/autoload.php';

// require (realpath(dirname(__FILE__)) . '/../library/Com/Tecnick/Barcode/autoload.php');
// require (realpath(dirname(__FILE__)) . '/../library/Com/Tecnick/Color/autoload.php');


class MepatientController extends Pms_Controller_Action 
{
    public function init()
    {
    	/* Initialize action controller here */
    	$this->setActionsWithJsFile([
    			"surveys", 
    			"addsurvey", 
    	]);
    	//phtml is the default for zf1 ... but on bootstrap you manualy set html :(
    	$this->getHelper('viewRenderer')->setViewSuffix('phtml');
    	 
    }
       
    
    public function surveysAction()
    {
        $this->view->category = $this->getParam('category');
        $this->view->usertype = $this->logininfo->usertype;
        
        if($_REQUEST['action'])
        {
            if($_REQUEST['action'] == 'delete' && $_REQUEST['id'])
            {
                $matord = new MePatientSurveys();
                $matr = $matord->getTable()->find($_REQUEST['id'], Doctrine_Core::HYDRATE_RECORD);
                $matr->isdelete = '1';
                $matr->save();
                
                $this->_redirect(APP_BASE . "mepatient/surveys");
            }
        }
        
        //populate the datatables
        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
            
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            
            $sort_col_dir = $this->getRequest()->getPost('sSortDir_0');
            $sort_col_dir = $sort_col_dir == 'asc' ? 'ASC' : 'DESC';
            
            $sort_col_idx = $this->getRequest()->getPost('iSortCol_0');
            $sort_col_name = $this->getRequest()->getPost('mDataProp_' . $sort_col_idx);
            
            $limit = $this->getRequest()->getPost('iDisplayLength');
            $offset = $this->getRequest()->getPost('iDisplayStart');
            
            $category = $this->getRequest()->getPost('category');
            
            $search_value = $this->getRequest()->getPost('sSearch');
            
            $columns_array = array(
                "0" => "survey_name",
                "1" => "survey_url"
            );
            $columns_search_array = $columns_array;
            
            $order_by = '';
            
            $tobj = new MePatientSurveys(); //obj used as table
            
            
            if ( ! empty($sort_col_name) && $tobj->getTable()->hasColumn($sort_col_name)) {
                //$order_by = $sort_col_name . ' ' . $sort_col_dir;
                
                $chars[ 'Ä' ] = 'Ae';
                $chars[ 'ä' ] = 'ae';
                $chars[ 'Ö' ] = 'Oe';
                $chars[ 'ö' ] = 'oe';
                $chars[ 'Ü' ] = 'Ue';
                $chars[ 'ü' ] = 'ue';
                $chars[ 'ß' ] = 'ss';
                
                $colch =addslashes(htmlspecialchars($sort_col_name));
                
                foreach($chars as $kch=>$vch)
                {
                    $colch = 'REPLACE('.$colch.', "'.$kch.'", "'.$vch.'")';
                }
                
                $order_by ='LOWER('.$colch.') '.$sort_col_dir;
            }
            
            $tcol = $tobj->getTable()->createQuery('q');
            $tcol->select('q.*,sc.*');
            $tcol->andWhere("isdelete = 0"); 
            $full_count  = $tcol->count();
            
            /* ------------- Search options ------------------------- */
            if (isset($search_value) && strlen(trim($search_value)) > 0)
            {
                $comma = '';
                $filter_string_all = '';
                
                foreach($columns_search_array as $ks=>$vs)
                {
                    $filter_string_all .= $comma.$vs;
                    $comma = ',';
                }
                
                $regexp = trim($search_value);
                Pms_CommonData::value_patternation($regexp);
                
                $searchstring = mb_strtolower(trim($search_value), 'UTF-8');
                $searchstring_input = trim($search_value);
                if(strpos($searchstring, 'ae') !== false || strpos($searchstring, 'oe') !== false || strpos($searchstring, 'ue') !== false)
                {
                    if(strpos($searchstring, 'ss') !== false)
                    {
                        $ss_flag = 1;
                    }
                    else
                    {
                        $ss_flag = 0;
                    }
                    $regexp = Pms_CommonData::complete_patternation($searchstring_input, $regexp, $ss_flag);
                }
                
                $filter_search_value_arr[] = 'CONVERT( CONCAT_WS(\' \','.$filter_string_all.' ) USING utf8 ) REGEXP ?';
                $regexp_arr[] = $regexp;
                
                $tcol->andWhere($filter_search_value_arr[0] , $regexp_arr);
                $filter_count  = $tcol->count();
            }
            else
            {
                $filter_count = $full_count;
            }
            
            if ( ! empty($order_by)) {
                $tcol->orderBy($order_by);
            }
            
            if ( ! empty($limit)) {
                $tcol->limit((int)$limit);
            }
            
            if ( ! empty($offset)) {
                $tcol->offset((int)$offset);
            }
            $tcol->leftJoin('q.MePatientSurveysClients as sc');
            $tcol_arr = $tcol->fetchArray();
            $clientsarray = array();
            $clientsarray = Client::get_all_clients();

            $resulted_data = array();
            foreach($tcol_arr as $row)
            {
                $survey_clients = array();
                $survey_clients_str = "";
                if(!empty($row['MePatientSurveysClients'])){
                    foreach($row['MePatientSurveysClients'] as $k=>$cl_sur){
//                         $survey_clients[$cl_sur['survey_id']][] = '<li>'.$clientsarray[$cl_sur['clientid']]['client_name'].'</li>';
                        $survey_clients_str .= '<span class="clname">'.$clientsarray[$cl_sur['clientid']]['client_name'].'</span>';
                    }
                }
                
                $data = array(
                    'survey_name' => 	$row['survey_name'],
                    'survey_url' => $row['survey_url'],
//                     'clients' => implode(",<br/>",$survey_clients[$row['id']]),
                    'clients' => $survey_clients_str,
                    'actions' => '<a href="'.APP_BASE .'mepatient/addsurvey?sid='.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/edit.png" /> </a><a href="javascript:void(0);"  class="delete" rel="'.$row['id'].'" id="delete_'.$row['id'].'"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a>'
                );
                array_push($resulted_data, $data);
            }
            
            $result = array(
                'draw' => $this->getRequest()->getPost('sEcho'),
                'recordsTotal' => $full_count,
                'recordsFiltered' => $filter_count,
                'data' => $resulted_data
            );
            
            $this->_helper->json->sendJson($result);
            exit; //for readability
        }
        
        
    }
    
    private function retainValues($values)
    {
        foreach($values as $key => $val)
        {
            $this->view->$key = $val;
        }
    }
    
    
    public function addsurveyAction() {
        
        if($this->getRequest()->isPost())
        {
            $message_form = new Application_Form_MePatientSurveys();
            
            if($message_form->Validate($_POST))
            {
                if(!empty($_REQUEST['sid'])){
                    $_POST['survey_id'] = $_REQUEST['sid'];
                }
                
                $message_form->save_survey($_POST);
                $this->_redirect(APP_BASE . "mepatient/surveys");
                
            }
            else
            {
                $this->retainValues($_POST);
                $message_form->assignErrorMessages();
            }
        }
        
        
        // get data for survey 
        if(!empty($_REQUEST['sid'])){
            
            $survey_id = $_REQUEST['sid'];
            $survey_details_arr = MePatientSurveysTable::find_survey_Byid($survey_id);
            $survey_details = array();
            
            if(!empty($survey_details_arr[$survey_id])){
                
                $survey_details = $survey_details_arr[$survey_id];
                
                $this->view->survey_name = $survey_details['survey_name'];
                $this->view->survey_url = $survey_details['survey_url'];
                $this->view->painpool_survey_id = $survey_details['painpool_survey_id'];
                
                if(!empty($survey_details['MePatientSurveysClients'])){
					//MEP-225 Ancuta 21.10.2020 -- if post with no clients- list with no clients, do not list the existing clients 
                    if($this->getRequest()->isPost() && empty($_POST['clients']))
                    {
                        $this->view->clients =  array();
                    } else {
                        $this->view->clients =  array_column($survey_details['MePatientSurveysClients'], 'clientid');
                    }
					// --
                }
            }
        }
        
        // get all active clients
        $clientsarray = Client::get_all_clients();
        $this->view->clientsarray = $clientsarray;
        
    }
    
    
    public function deviceqrcodeAction(){
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        $msg = "";
        if(empty($_REQUEST['__device_id']) || empty($_REQUEST['pateintid'])){
            $msg ='No patient or device data ';

            echo  $msg;
            exit;
        }
        // get device data 
        $device_id = $_REQUEST['__device_id'];
        
        $decid = Pms_Uuid::decrypt($_REQUEST['pateintid']);
        $ipid = Pms_CommonData::getIpid($decid);
        
        $device_data_arr = array();
        $device_data_arr = MePatientDevicesTable::find_patient_deviceByIpidAndId($ipid,$device_id);
        
        if(empty($device_data_arr)){
            $msg ='No  device data ';
            
            echo  $msg;
            exit;
        }
        
        $device_data = array();
        $device_data = $device_data_arr[$device_id];
        
        if($device_data['active'] != 'no'){
            $msg ='device active';
        }
        
        $text_arr = array();
        
        //######################################3
        //Q:abcedf12| <-- QR identifier, 8 chars -->
        $qr_identifier_text = '';
        
        $MePatientQrCodes_obj = new MePatientQrCodes();
        $qr_identifier = $MePatientQrCodes_obj->qr_identifier_generate();
        
        $text_arr['Q'] =
        $qr_identifier_text = 'Q:'.$qr_identifier;
        
        //######################################3
        //D:ISPC988303| <!-- Device ID -->
        $device_id = "0";
        if( empty($device_data['device_internal_id']) ){
            $msg ='Error no device id';
        } 
        $device_internal_id  = $device_data['device_internal_id'];
        
        $text_arr['D'] =
        $device_id_text    = 'D:'.$device_internal_id;

        
        //######################################3
        //P:ISPC PMS_TEST| <!-- Program name -->
        // get client details
        $client_array = array();
        $Client_obj = new Client();
        $client_array = $Client_obj->findOneById($this->logininfo->clientid);
        
        $text_arr['P'] =
        $program_name_text    = 'P:ISPC '.$client_array['client_name'];
        $program_name    = 'ISPC '.$client_array['client_name'];
        
        //######################################3
        //I:y| <!-- Can this device upload photos y/n -->
        $upload_photos = $device_data['allow_photo_upload'] == 'yes' ? 'y' : 'n';
        
        $text_arr['I'] =
        $upload_photos_text    = 'I:'.$upload_photos;
        
        
        //######################################3
        //C:147\Schmerzfragebogen V2015.2 + QLIP\http://some/url/to/chain/147.xml| <!--survey id/survey name/URL of XML -->
        
        
        $devices_surveys  = array();
        if(!empty($device_data['MePatientDevicesSurveys'])){
            $devices_surveys = $device_data['MePatientDevicesSurveys'];
        }
        $survey_ids = array();
        foreach($devices_surveys as $k=>$ds){
            $survey_ids[] = $ds['survey_id'];
        }
        // get cleint dsurveys details
        $survey_details_arr = MePatientSurveysTable::find_multiple_survey_Byids($survey_ids);
        
        $devices_surveys_str ="";
        $survey_lines = array();
        if (! empty($survey_details_arr)) {
            foreach ($survey_details_arr as $cs => $sdata) {
                if(in_array($sdata['id'],$survey_ids)){
                    $survey_line = array();
                    $survey_line['id'] = $sdata['painpool_survey_id'];
                    $survey_line['name'] = trim($sdata['survey_name']);
                    $survey_line['url'] = trim($sdata['survey_url']);
                    $delimiter= "\\";//ISSUE ??? 
                    $survey_lines[]  ='C:'.implode($delimiter,$survey_line);
                }
            }
            $devices_surveys_str = implode('|',$survey_lines); 
        }
        $text_arr['C'] =
        $surveys_text = $devices_surveys_str;

        
        $qr_code_text = "";
        $qr_code_text = implode('|',$text_arr);
        
        
        //FIRST SAVE QR CODE
        $save_qrCode = new MePatientQrCodes();
        $save_qrCode->device_id = $device_data['id'];
        $save_qrCode->qr_identifier = $qr_identifier;
        $newTime = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +5 minutes"));
        $save_qrCode->expiration_date = $newTime;
        $save_qrCode->qr_text = $qr_code_text;
        $save_qrCode->save();
        
        if($save_qrCode->id){
        
            //GENERATE QR CODE - PNG 
            $barcode = new \Com\Tecnick\Barcode\Barcode();
//             $data = ' QCode expired |  QCode expired |  QCode expired |  QCode expired |  QCode expired |  QCode expired |  QCode expired |  QCode expired |  QCode expired |  QCode expired |  QCode expired ';
            $data = $qr_code_text;
            $typed = 'QRCODE';
            $bobj = $barcode->getBarcodeObj($typed, $data, -4, -4, 'black', array(0, 0, 0, 0));
            $qr_img .= "<img alt=\"QrCode\" src=\"data:image/png;base64,".base64_encode($bobj->getPngData())."\" />";
            $qr_img .= "<input type=\"hidden\" name=\"qr_identifier\" value=\"".$qr_identifier."\" />";
            $qr_img .= "<input type=\"hidden\" name=\"program\" value=\"".$program_name."\" />";
            $qr_img .= "<input type=\"hidden\" name=\"qr_text\" value=\"".$qr_code_text."\" />";

            echo $qr_img;
            exit;
        } else{
            
            echo 'ERROR - qr not saved';
            exit;
        }
    }
    
    
    public function validatedeviceinputAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        if(empty($_REQUEST['__input_type']) ){
            return false;
            exit;
        }
        $result = array();
        switch ($_REQUEST['__input_type']){
            case 'password':
                /*
                The device password should be a 12 chars random generated string containing lower
                & uppercase letters and numbers, the final service validates the password against
                previous rules.
                */
                $pass = $_REQUEST['__text'];
                $ucl = preg_match('/[A-Z]/', $pass); // Uppercase Letter
                $lcl = preg_match('/[a-z]/', $pass); // Lowercase Letter
                $dig = preg_match('/\d/', $pass); // Numeral
                $nos = preg_match('/\W/', $pass); // Non-alpha/num characters (allows underscore)
                $size = strlen($pass);
 
                if ($size==12 && $ucl && $lcl && $dig && !$nos) { // Negated on $nos here as well
                    $result['valid'] = 1;
                    $result['msg'] = "success";
                } else {
                    $result['valid'] = 0;
                    $result['msg'] = $this->view->translate("Password must have 12 chars, alphanumeric and must contain at lieast one upper case letter");
                }
                
                break;
            case 'activation_code':
                /*
                The activation code is the last 8 chars of a md5 hash created from:
                QR identifier + device id + program + device password + current date (dd.mm.yyyy
                format)
                e.g.  md5(abcedf12ISPC988303ISPC PMS_TESTJ4gtXHC5SLy603.01.2020) =
                9bf184ca085ba6438094414a8794aa58 => activation code = 8794aa58
                */
                
                
                
                break;
        }
        
 
        echo json_encode($result);
        exit;
        
        
        
    }

}
?>