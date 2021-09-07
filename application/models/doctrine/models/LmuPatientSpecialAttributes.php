<?php
Doctrine_Manager::getInstance()->bindComponent('LmuPatientSpecialAttributes', 'MDAT');
class LmuPatientSpecialAttributes extends BaseLmuPatientSpecialAttributes
{

public function updateKarnofsky($ipid, $karnofsky){
	$record = Doctrine::getTable('LmuPatientSpecialAttributes')->findOneByIpid($ipid);
	if ($record) {
		$record->karnofsky = $karnofsky;
		$record->save();
	} else {
		$record = new LmuPatientSpecialAttributes();
		$record->ipid = $ipid;
		$record->karnofsky = $karnofsky;
		$record->save();
	}
}

public  function updatePriority($ipid, $prio){
    $prio=intval($prio);
    if($prio<0){
        $prio=0;
    }
    if($prio>4){
        $prio=4;
    }
    $record = Doctrine::getTable('LmuPatientSpecialAttributes')->findOneByIpid($ipid);
    if ($record) {
        $record->priority = $prio;
        $record->save();
    } else {
        $record = new LmuPatientSpecialAttributes();
        $record->ipid = $ipid;
        $record->priority = $prio;
        $record->save();
    }
}

    /**
     * Get last Prio vom course and set that val to actual prio val
     * @param $ipid
     */
    public static function checkPrio($ipid){
        $visitblocksq=Doctrine_Query::create()
            ->select('*')
            ->from('PatientCourse')
            ->where('ipid = ?', $ipid)
            ->andWhere('course_type=?', Pms_CommonData::aesEncrypt('!'))
            ->andWhere('wrong = 0')
            ->orderBy('course_date DESC')
            ->limit(1);
        $visitblocks=$visitblocksq->fetchArray();

        if(count($visitblocks)>0){
            $prio=Pms_CommonData::aesDecrypt($visitblocks[0]['course_title']);
            $l=new LmuPatientSpecialAttributes();
            $l->updatePriority($ipid, $prio);
        }
    }

    /**
     * Get Array with Karnofskys for given timespan sorted by time.
     * Also returns corresponding ECOG-Values.
     * @param string    $start  Example: 2017-01-01 00:00:00
     * @param string    $end    Example: 2017-01-01 00:00:00
     * @return array [][date, karnofsky, ecog]
     */
    public static function get_karnofskys_in_timespan($ipid, $start, $end){
        $karnofskys=array();
        $kar_to_ecog=array(
            100=>0,
            90=>0,
            80=>1,
            70=>1,
            60=>2,
            50=>2,
            40=>3,
            30=>3,
            20=>4,
            10=>4,
            0=>5
        );


        $allcontactformsq = Doctrine_Query::create()
            ->select('id, start_date')
            ->from('ContactForms')
            ->where('ipid = ?', $ipid)
            ->andwhere('start_date < ?', $end)
            ->andWhere('start_date > ?', $start)
            ->andWhere('isdelete = 0')
            ->orderBy('start_date');
        $allcontactforms=$allcontactformsq->fetchArray();

        $cformids=array();
        $cformid_to_start_date=array();
        foreach($allcontactforms as $cform){
            $cformids[]=$cform['id'];
            $cformid_to_start_date[$cform['id']] = $cform['start_date'];
        }

        $visitblocksq=Doctrine_Query::create()
            ->select('id,karnofsky,contact_form_id')
            ->from('FormBlockLmuVisit')
            ->where('ipid = ?', $ipid)
            ->andWhereIn('contact_form_id', $cformids)
            ->andWhere('isdelete = 0');
        $visitblocks=$visitblocksq->fetchArray();

        foreach ($visitblocks as $visitblock){
            $ecog=null;
            $kar=$visitblock['karnofsky'];

            if($kar!=null){
                $kar=intval($kar);
                $ecog=$kar_to_ecog[$kar];
            }else{
                $ecog=-1;
            }



            $karnofskys[]=array(
                'date'=>$cformid_to_start_date[$visitblock['contact_form_id']],
                'karnofsky'=>$kar,
                'ecog'=>$ecog
            );
        }


        $course_entries_q=Doctrine_Query::create()
            ->select("id,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as karnofsky, done_date")
            ->from('PatientCourse')
            ->where('ipid = ?', $ipid)
            ->andwhere('course_type = ?',Pms_CommonData::aesEncrypt('%'))
            ->andWhere('done_date>=?',$start)
            ->andWhere('done_date<=?',$end)
            ->andWhere('wrong = 0');
        $course_entries=$course_entries_q->fetchArray();


        foreach ($course_entries as $entry){
            $ecog=null;
            $kar=$entry['karnofsky'];

            if($kar!=null){
                $kar=intval($kar);
            }

            $ecog=$kar_to_ecog[$kar];

            $karnofskys[]=array(
                'date'=>$entry['done_date'],
                'karnofsky'=>$kar,
                'ecog'=>$ecog
            );
        }


        //sort
        $dates=array_column($karnofskys, 'date');
        array_multisort($dates, $karnofskys);
        return $karnofskys;
    }


public function updateOberarzt($ipid, $userid){
    $logininfo= new Zend_Session_Namespace('Login_Info');
    $record = Doctrine::getTable('LmuPatientSpecialAttributes')->findOneByIpid($ipid);
    if ($record) {
        $record->oberarzt = $userid;
        $record->save();
    } else {
        $record = new LmuPatientSpecialAttributes();
        $record->ipid = $ipid;
        $record->oberarzt = $userid;
        $record->save();
    }


}

    public function updateAssistenzarzt($ipid, $userid){
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $record = Doctrine::getTable('LmuPatientSpecialAttributes')->findOneByIpid($ipid);
        if ($record) {
            $record->oberarzt = $userid;
            $record->save();
        } else {
            $record = new LmuPatientSpecialAttributes();
            $record->ipid = $ipid;
            $record->assistenzarzt = $userid;
            $record->save();
        }


    }

public function getPatientsOberarzt($ipids){
    $logininfo= new Zend_Session_Namespace('Login_Info');
    if (!$ipids){
        return array();
    }
    if (!is_array($ipids)){
        $ipids=array($ipids);
    }

    $q=Doctrine_Query::create()
        ->select("ipid,epid")
        ->from('EpidIpidMapping')
        ->wherein('ipid', $ipids);
    $epids=$q->fetchArray();

    $epid_to_ipid=array_combine(array_column($epids,'epid'),array_column($epids,'ipid'));



    $lmuup=new WeeklyUserprivileges();//Maria:: Migration CISPC to ISPC 22.07.2020
    $oas=$lmuup->getPrevilegedUsers (  $logininfo->clientid, 'oberarzt');


    $assignid = Doctrine_Query::create()
        ->select('*')
        ->from('PatientQpaMapping')
        ->wherein("epid",array_keys($epid_to_ipid,'epid'))
        ->andWhereIn('userid',$oas);
    $qpas = $assignid->fetchArray();



    $out=array();
    foreach ($qpas as $usertopatient){
        $out[$epid_to_ipid[$usertopatient['epid']]]=$usertopatient['userid'];
    }
    return $out;

    die('OK');
    $lup=new WeeklyUserprivileges();//Maria:: Migration CISPC to ISPC 22.07.2020

    $returnarray=array();

    foreach ($ipids as $ipid){
        $docs = $lup->getPatientsOberaerzte($ipid);
        $returnarray[$ipid]=$docs[0];
    }

    return $returnarray;
}

public function updateCaseNumber($ipid, $case_number){
	$record = Doctrine::getTable('LmuPatientSpecialAttributes')->findOneByIpid($ipid);
	if ($record) {
		$record->case_number = $case_number;
		$record->save();
	} else {
		$record = new LmuPatientSpecialAttributes();
		$record->ipid = $ipid;
		$record->case_number = $case_number;
		$record->save();
	}
}

public function updateSpecialAttributes($ipid, $attributes){
	$record = Doctrine::getTable('LmuPatientSpecialAttributes')->findOneByIpid($ipid);
	if (!$record) {
		$record = new LmuPatientSpecialAttributes();
		$record->ipid = $ipid;
	}

    foreach(array('phase','karnofsky', 'bewusstsein', 'orient_ort', 'orient_zeit', 'orient_person','orient_situation','keineorient')as $item){
        if(in_array($item,array_keys($attributes))) {
            $record->$item = $attributes[$item];
        }
    }
	$record->save();
	
	if( isset($attributes['phase']) && $attributes['phase']>-1){
		$record = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
		$record->traffic_status = $attributes['phase'];
		$record->save();
		}
}


public function getSpecialAttributes($ipid){	
	$record = Doctrine::getTable('LmuPatientSpecialAttributes')->findOneByIpid($ipid);
    if (!$record) {
        $record = new LmuPatientSpecialAttributes();
        $record->ipid = $ipid;
        $record->save();
    }
	$record2 = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
	$record->phase = $record2->traffic_status;
	return $record;
}

public function getCaseNo($ipid){	
	$record = Doctrine::getTable('LmuPatientSpecialAttributes')->findOneByIpid($ipid);

	return $record->case_number;
}

    public function updateInfection($ipid, $infpost){
        $record = Doctrine::getTable('LmuPatientSpecialAttributes')->findOneByIpid($ipid);
        if (!$record) {
            $record = new LmuPatientSpecialAttributes();
            $record->ipid = $ipid;
        }

        $record->infection=json_encode($infpost);
        $record->save();
    }
    public function getInfectionblock($ipid){
        $record = Doctrine::getTable('LmuPatientSpecialAttributes')->findOneByIpid($ipid);

        if($record) {
            $inf=$record->infection;
            return json_decode($record->infection,1);
        }else{
            return array();
        }
    }
}
?>
