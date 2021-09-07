<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('Extraopsleistung', 'MDAT');

class Extraopsleistung extends BaseExtraopsleistung{

	public static function getEntries($ipid, $caseid, $include_delete=0)
	{

        $fdoc = Doctrine_Query::create()
            ->select('*')
            ->from('Extraopsleistung')
            ->where('ipid = ?', $ipid)
            ->andWhere("caseid=?",$caseid)
            ->andWhere("isdelete=?",$include_delete);

        $rows = $fdoc->fetchArray();


        //sort by date
        if($rows && count($rows)>1){
            $dcol=array_column($rows, 'done_date');
            array_multisort($dcol, $rows);
        }

        //escape
        $cols=Extraopsleistung::getColkeys();
        foreach ($rows as $k=>$row){
            foreach ($cols as $col) {
                $rows[$k][$col]=htmlspecialchars($row[$col]);
            }
        }

        return $rows;
    }

    public static function getOpsEntries($ipid){
        $cases=PatientReadmission::get_cases_by_id($ipid);

        $fdoc = Doctrine_Query::create()
            ->select('*')
            ->from('Extraopsleistung')
            ->where('ipid = ?', $ipid)
            ->andWhere("isdelete=?",0);
        $rows = $fdoc->fetchArray();

        $opstimes=array();
        foreach ($rows as $row){
            $day=$row['done_date'];
            $day=explode(' ', $day);
            $day=$day[0];
            $opstimes[]=array(
                'minutes'=>$row['mins'],
                'day'=>$day,
                'type'=>$cases[$row['caseid']]['case_type'],
                'profs'=>array($row['done_group']),
                'done_date'=>$row['done_date'],
                'mins_patient'=>$row['mins_patient'],
                'mins_angehoerige'=>$row['mins_angehoerige'],
                'mins_systemisch'=>$row['mins_systemisch'],
                'mins_profi'=>$row['mins_profi'],
                'is_extraopsleistung'=>$row['memo'],
            );
        }

        return $opstimes;

    }

    public static function deleteEntry($id){
        $entry = Doctrine::getTable('Extraopsleistung')->findOneById($id);
        if($entry) {
            $entry->isdelete = 1;
            $entry->save();

            $course=Doctrine::getTable('PatientCourse')->findOneByTabnameAndRecordid(Pms_CommonData::aesEncrypt('extraopsleistung'),$entry->id);
            if($course) {
                $course->wrong = 1;
                $course->save();
            }
        }

    }

    public static function createEntry($post){

        $logininfo = new Zend_Session_Namespace('Login_Info');

        $new = new Extraopsleistung();
        $new->modifyEntry($post);

        $cust = new PatientCourse();
        $cust->ipid = $post['ipid'];
        $cust->course_date = date("Y-m-d H:i:s",time());
        $cust->done_date=$new->done_date;
        $cust->tabname=Pms_CommonData::aesEncrypt('extraopsleistung');
        $cust->course_type=Pms_CommonData::aesEncrypt("K");
        $entry="Erbrachte Leistung: ";
        $entry=$entry . $post['mins'] . " Minuten ";
        if(strlen($post['memo'])>0){
            $entry=$entry . " für ". $post['memo'] ." ";
        }
        if(strlen($post['done_name'])>0){
            $entry=$entry . "erbracht durch ". $post['done_name'] ." ";
        }
        if(strlen($post['done_group'])>0){
            $entry=$entry . "(". $post['done_group'] .") ";
        }
        $entry=trim($entry);

        $cust->course_title=Pms_CommonData::aesEncrypt($entry);
        $cust->user_id = $logininfo->userid;
        $cust->recordid=$new->id;
        $cust->save();


    }

    public static function updateEntry($id, $post){
        $entry = Doctrine::getTable('Extraopsleistung')->findOneById($id);
        $entry->modifyEntry($post);
    }

    public function modifyEntry($post){

        $date=$post['done_date'] . " ". $post['time'] .":00";
        $date=strtotime($date);
        $post['done_date']=date('Y-m-d H:i:s',$date);
        $cols=Extraopsleistung::getColkeys();
        foreach($cols as $col){
            $this->$col = $post[$col];
        }
        $this->save();


    }

    public static function getColkeys(){
        $cols=array('ipid','caseid','done_date','mins','mins_patient','mins_angehoerige','mins_profi','mins_systemisch','done_group','done_name','memo');
        return $cols;
    }
}
?>