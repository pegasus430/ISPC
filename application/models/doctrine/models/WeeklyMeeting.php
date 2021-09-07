<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('WeeklyMeeting', 'MDAT');

class WeeklyMeeting extends BaseWeeklyMeeting
{

    private function decryptFields($fields){

        foreach ($fields as $key=>$field){
            if ($key>5) break;
            $fields[$key]['course']=Pms_CommonData::aesDecrypt($fields[$key]['course']);
            $fields[$key]['main_problems']=Pms_CommonData::aesDecrypt($fields[$key]['main_problems']);

            $cat_list = array('medic', 'care', 'psy', 'social', 'spiritual', 'physio', 'breath');

            foreach($cat_list as $cat){
                $plan = $cat.'_plan';
                $goal = $cat.'_goal';
                $fields[$key][$plan]=Pms_CommonData::aesDecrypt($fields[$key][$plan]);
                $fields[$key][$goal]=Pms_CommonData::aesDecrypt($fields[$key][$goal]);
            }
        }
        return $fields;
    }

    public function getAllPatientforms($ipid, $week0=0){
        $query = Doctrine_Query::create()
            ->select("*")
            ->from('WeeklyMeeting')
            ->where("ipid='".$ipid."'")
            ->andwhere("isdelete='0'")
            ->orderBy("id DESC");

        if(!$week0){
            $query->andWhere("week>0");
        }
        $formsarr = $query->fetchArray();

        if($formsarr){
            return $this->decryptFields($formsarr);
        }
    }


    public function getPatientform($id){
        $query = Doctrine_Query::create()
            ->select("*")
            ->from('WeeklyMeeting')
            ->where("id='".$id."'");
        $formsarr = $query->fetchArray();

        if($formsarr){
            return $this->decryptFields($formsarr);
        }
    }

    /**
     * @return ipids with last finished tm >= 7 daysold.
     */
    public function get_tms_that_need_to_be_done(){
        $ipids=PatientReadmission::getActiveIpids();
        $sql = Doctrine_Query::create()
            ->select('t.ipid as ipid, MAX(t.date) as date')
            ->from('WeeklyMeeting t INDEXBY t.ipid')
            ->where('t.isdelete=0')
            ->andwhere('t.finished=1')
            ->andWhereIn('t.ipid',$ipids)
            ->groupBy('ipid');
        $tm_arr = $sql->fetchArray();

        $today=strtotime(date("d.m.Y"));

        $tm_dates=array();
        foreach($ipids as $ipid){
            if(isset($tm_arr[$ipid])){
                $then_date=$tm_arr[$ipid]['date'];
                $then=strtotime(date("d.m.Y",strtotime($then_date)));
                $diff=abs($today-$then);
                $tm_dates[$ipid]=intval($diff / (60*60*24)); //0==last tm was today, 2==last tm was two days ago
            }else{
                $tm_dates[$ipid]=999;
            }
        }
        $b = array_filter($tm_dates, function($v){
            return $v >= 7 && $v<900;});
        arsort($b);
        return $b;
    }

    public function get_tm_pdf($ipid, $fid){
        $thisview = new Zend_View();
        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $thisview->encid = Pms_Uuid::encrypt($decid);

        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $patientmaster = new PatientMaster();

        $thisview->setScriptPath(APPLICATION_PATH."/views/scripts/lmuteammeeting/");
        //$thisview->patientinfo = $patientmaster->getMasterData($decid, 1);


        $patient_details = $patientmaster->getMasterData($decid, 0);
        $thisview->patient_details=$patient_details;
        $pq = new User();
        $thisview->client_users = $pq->getUserByClientid($clientid);


        if (intval($fid)>0){
            $form_id=intval($fid);
            if ($thisview->forms[0]['id'] != $form_id) {
                $thisview->readonly=1;
                $thisview->forms = $this->getPatientform($form_id);

                $tmu=new WeeklyMeetingusers();
                $thisview->additional_users=	$tmu->getMeetingUsers($form_id);

            }
        }


        $postarray = array(
            'forms'             =>$thisview->forms,
            'patient_details'   =>$thisview->patient_details,
            'client_users'      =>$thisview->client_users,
            'additional_users'  =>$thisview->additional_users  );
        $pdfpath=$this->generate_pdf ($postarray, 'lmuteammeeting_pdf.html' );
        return $pdfpath;

    }

    private function generate_pdf ( $post_data, $filename )
    {
        $post_data = Pms_CommonData::clear_pdf_data($post_data, array('patientname', 'address', 'image', 'visits_array'));

        $htmlform = Pms_Template::createTemplate($post_data, 'templates/' . $filename);

        $pdf = new Pms_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setDefaults(true); //defaults with header
        $pdf->setImageScale(1.6);
        $pdf->SetMargins(10, 5, 10); //reset margins
        $html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
        $pdf->setHTML($html);
        //$filename = Pms_CommonData::uniqfolder($pdfname.'.pdf');
        $filename = Pms_CommonData::get_tempfilepath();
        $pdf->toFile($filename);

        return $filename;
    }


}

?>

