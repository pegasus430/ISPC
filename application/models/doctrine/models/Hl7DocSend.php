<?php
Doctrine_Manager::getInstance()->bindComponent('Hl7DocSend', 'MDAT');

/**
 * @author Nico
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class Hl7DocSend extends BaseHl7DocSend
{


    /**
     * Find out what tags are configured to be sendable
     */
    public static function get_sendable_tags($clientid, $all_tags=0){
        $cconfig=ClientConfig::getConfig($clientid, 'sendabletags');
        $fdoc1 = Doctrine_Query::create();
        $fdoc1->select('*');
        $fdoc1->from('PatientFileTags pft INDEXBY pft.id');
        $fdoc1->Where("pft.client = ".$clientid);
        $fdoc1->andWhere("pft.isdelete = 0  ");
        $available_tags = $fdoc1->fetchArray();
        foreach($available_tags as $tid=>$content){
            if(isset($cconfig[$tid])){
                $available_tags[$tid]['sendable'] = $cconfig[$tid]['sendable'];
                $available_tags[$tid]['form_type']=0;
                if(isset($available_tags[$tid]['form_type'])) {
                    $available_tags[$tid]['form_type'] = $cconfig[$tid]['form_type'];
                }
                $available_tags[$tid]['comment'] = $cconfig[$tid]['comment'];
                $available_tags[$tid]['extrainfo'] = $cconfig[$tid]['extrainfo'];
            }else{
                if(!$all_tags) {
                    unset($available_tags[$tid]);//return only sendable tags
                }
            }
        }

        return $available_tags;
    }

    /**
     * generate a unique id to be able to group files belonging together
     */
    public static function generate_root_id(){
        return time() . rand(10000, 99999);
    }

    /**
     * on saving a report add the report data to this table
     */
    public static function register_saved_report($ipid, $clientid, $cf_id, $uid, $pdfid, $signed_status=1){
        $cust = new Hl7DocSend();
        $cust->clientid=$clientid;
        $cust->ipid=$ipid;
        $cust->cf_id=$cf_id;
        $cust->uid=$uid;
        $cust->file_id=$pdfid;
        $cust->signed_status=$signed_status;
        $cust->save();
    }

    /**
     * on transmitting a file, register it
     */
    public static function register_file($ipid, $clientid, $pdfid){
        $entry = Doctrine::getTable('Hl7DocSend')->findOneByIpidAndFileId($ipid, $pdfid);
        if(!$entry) {
            $uid = Hl7DocSend::generate_root_id();
            $entry = new Hl7DocSend();
            $entry->clientid = $clientid;
            $entry->ipid = $ipid;
            $entry->uid = $uid;
            $entry->file_id = $pdfid;
            $entry->save();
        }
        return $entry;
    }

    /**
     * return tag for this form
     */
    public static function get_pmdreporttag($clientid, $form_type){
        $cconfig=ClientConfig::getConfig($clientid, 'sendabletags');
        $pmd_tags=array();
        foreach($cconfig as $conf){
            if($conf['sendable']==2) {
                if (in_array($form_type, $conf['form_type'])) {
                    $pmd_tags[] = $conf['id'];
                }
            }
        }
        return $pmd_tags;
    }


    /**
     * return all sendable files
     * and add information about the tags that make the file sendable.
     * @author Nico
     */
    public static function get_files_for_hl7transmit($ipid, $clientid){
        $patient_filesQ = Doctrine_Query::create()
            ->select("  id,
                        AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type,
                        tabname,
                        create_date,
                        recordid")
            ->from('PatientFileUpload p INDEXBY p.id')
            ->where('ipid="' . $ipid . '"')
            ->andwhere('isdeleted=0');
        $filearray = $patient_filesQ->fetchArray();

        $file_array_ids=array_keys($filearray);

        $trans_infoQ = Doctrine_Query::create()
            ->select("*")
            ->from('Hl7DocSend p INDEXBY p.id')
            ->where('ipid=?',$ipid)
            ->andwhere('isdelete=0');
        $trans_array = $trans_infoQ->fetchArray();



        $cfids=array_column(array_filter($trans_array, function($in){if(strlen($in['cf_id'])){return $in;}}),'cf_id');

        $cf_parentsQ = Doctrine_Query::create()
            ->select("id, parent")
            ->from('ContactForms p INDEXBY p.id')
            ->where('ipid="' . $ipid . '"')
            ->andwhere('parent>0')
            ->andwhereIn('id',$cfids);
        $cf_to_parents = $cf_parentsQ->fetchArray();

        $allcfparents=array_merge($cfids, array_column($cf_to_parents, 'parent'));
        $cf_datesQ = Doctrine_Query::create()
            ->select("id, parent, date")
            ->from('ContactForms p INDEXBY p.id')
            ->where('ipid="' . $ipid . '"')
            ->andwhereIn('id',$allcfparents);
        $cf_to_dates = $cf_datesQ->fetchArray();


        $cfmasters=array();
        foreach ($cf_to_dates as $cp){
            $cfmasters[$cp['id']]=$cp;
        }

        $fileid_to_trans=array();
        foreach ($trans_array as $entry){
            $fileid_to_trans[$entry['file_id']] = $entry;
        }


        $tags_info=Hl7DocSend::get_sendable_tags($clientid);

        $all_files_tags = PatientFile2tags::get_files_tags($file_array_ids);

        $out=array();
        foreach($all_files_tags as $file_id=>$tags){
            foreach($tags as $tag_id){
                if (isset( $tags_info[$tag_id])){
                    if(!isset($out[$file_id])){
                        $out[$file_id]=$filearray[$file_id];
                    }
                    $out[$file_id]['uid']=0;
                    if(isset($fileid_to_trans[$file_id])){
                        $out[$file_id]['transferinfo']=$fileid_to_trans[$file_id];
                        $out[$file_id]['uid']=$fileid_to_trans[$file_id]['uid'];
                        $mycfid=$fileid_to_trans[$file_id]['cf_id'];
                        $parent=$mycfid;
                        if(isset($cf_to_parents[$mycfid])){
                            $parent=$cf_to_parents[$mycfid]['parent'];
                        }
                        $out[$file_id]['cf_parent']=$parent;
                        $out[$file_id]['cf_date']=$cf_to_dates[$parent]['date'];
                    }

                    $out[$file_id]['tag']=$tags_info[$tag_id];
                    //only one magic tag per file!
                    //if user adds more magic tags to one file, we have a mess
                }
            }
        }

        return $out;
    }

    /**
     * get the filepath by file_id
     * this is copied over from stats controller
     * @author Nico
     */
    public static function get_filepath($ipid, $doc_id){
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $patient = Doctrine_Query::create()
            ->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
					AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
					AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
            ->from('PatientFileUpload')
            ->where('id= ?', $doc_id);
        $fl = $patient->execute();


        if($fl)
        {
            $flarr = $fl->toArray();

            $explo = explode("/", $flarr[0]['file_name']);

            $fdname = $explo[0];
            $flname = utf8_decode($explo[1]);

            //medication plan pdf issue only
            if($flarr[0]['title'] == "Medikamentenplan" && strpos($flarr[0]['file_name'], '.zip') !== false)
            {
                //overwrite folder name so the file can be downloaded
                $fdname = str_replace(".zip", "", $flname);

                //overwrite the file name so the file can be served to user
                $flname = "medication.pdf";
            }
        }

        /*
                    $con_id = Pms_FtpFileupload::ftpconnect();

                    if($con_id)
                    {
                        $old = false;
                        if($_REQUEST['old'])
                        {
                            $old = true;
                        }
                        $upload = Pms_FtpFileupload::filedownload($con_id, 'uploads/' . $fdname . '.zip', 'uploads/' . $fdname . '.zip', $old);
                        Pms_FtpFileupload::ftpconclose($con_id);
                    }
        */

        //			check if was uploaded after/in T0 date
        $client_merge_date = strtotime('2013-12-18 13:00:00'); //client merge date
        $req_file_date = strtotime(date('Y-m-d H:i:s', strtotime($flarr[0]['create_date'])));
        $file_password = '';

        //if uploaded before client_merge_date use old client(62) password to open it
        if($req_file_date < $client_merge_date && $logininfo->clientid == '61')
        {
            //check the patient in epid_ipid
            $patient_epid = Pms_CommonData::getEpidcharsandNum($ipid);

            if($patient_epid['char'] == 'DST') //patient belongs to the OLD client(62)
            {
                //OLD client upload password
                $file_password = 'j5qqil01gklqolq';

            }
            else //patient not found in old client => use curent client pass
            {
                $file_password = $logininfo->filepass;
            }
        }
        else //uploaded after client_merge_date use new client password to open it
        {
            $file_password = $logininfo->filepass;
        }
        /*
                    $cmd = "unzip -P " . $file_password . " uploads/" . $fdname . ".zip;";
                    exec($cmd);
        */

// 			$create_date =  date ("Y-m-d", strtotime($flarr[0]['create_date']));
// 			$today_date =  date("Y-m-d");
// 			if ($create_date == $today_date) {
// 				//file was created today... firt search localhost to download
// 				$first_location2search = 'local';
// 			} else {
// 				$first_location2search = 'ftp';
// 			}

        $old = $_REQUEST['old'] ? true : false;
        if (($path = Pms_CommonData::ftp_download('uploads/' . $fdname . '.zip' , $file_password , $old , null , $flarr[0]['file_name'], "PatientFileUpload", $flarr[0]['id'] )) === false){
            //failed to download/extract file
            $path = "uploads/" . $fdname ;
        }
        //returns the full path to the file
        //$path = $_SERVER['DOCUMENT_ROOT'] . "/uploads/" . $fdname . "/"; // change the path to fit your websites document structure
        $fullPath = $path . "/". $flname;

        if( ! file_exists($fullPath)) {

            $flname = $explo[1];
            $fullPath = $path . "/". $flname;

            if( ! file_exists($fullPath)) {

                $flname = Pms_CommonData::unicode_conv($flname , true);
                $fullPath = $path . "/". $flname;

                if( ! file_exists($fullPath)) {
                    $flname = Pms_CommonData::unicode_conv($flname , false);
                    $fullPath = $path . "/". $flname;
                }

            }
        }

        return $fullPath;
    }

    public function mark_sent(){
        if($this->uid > 0){
            //mute all previous versions
            $entries = Doctrine::getTable('Hl7DocSend')->findByIpidAndUid($this->ipid, $this->uid);
            foreach ($entries as $candidate){
                if($candidate->id < $this->id){
                    $candidate->muted=1;
                    $candidate->save();
                }
            }
        }
        $this->sent_date=date('Y-m-d H:i:s');
        $this->muted=1;
        $this->save();
    }

    public static function get_client_todos($clientid){
        $trans_infoQ = Doctrine_Query::create()
            ->select("p.*")
            ->from('Hl7DocSend p INDEXBY p.id')
            ->where('p.clientid=?',$clientid)
            ->andwhere('p.isdelete=0')
        ->andWhere('p.muted=0')
        ->groupBy('p.uid');
        $trans_array = $trans_infoQ->fetchArray();

        $ipids=array_column($trans_array,'ipid');
        $ipids=array_unique($ipids);

        $files=array_column($trans_array,'file_id');

        $files_infoQ = Doctrine_Query::create()
            ->select("p.*,
                        AES_DECRYPT(p.title,'" . Zend_Registry::get('salt') . "') as title,
						AES_DECRYPT(p.file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(p.file_type,'" . Zend_Registry::get('salt') . "') as file_type
            ")
            ->from('PatientFileUpload p INDEXBY p.id')
            ->whereIn('p.id',$files);

        $files_array=$files_infoQ->fetchArray();

        $pm=new PatientMaster();
        $pmdata=$pm->get_multiple_patients_details($ipids);

        foreach($trans_array as $i=>$a){
            $trans_array[$i]['patient']=$pmdata[$a['ipid']]['last_name'] . ", " . $pmdata[$a['ipid']]['first_name'];
            $trans_array[$i]['patient_epid']=$pmdata[$a['ipid']]['EpidIpidMapping'];
            $trans_array[$i]['patient_decid']=$pmdata[$a['ipid']]['id'];


            $trans_array[$i]['file']=$files_array[$a['file_id']];
        }

        return $trans_array;
    }
}