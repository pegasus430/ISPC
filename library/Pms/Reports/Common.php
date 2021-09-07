<?php
class  Pms_Reports_Common{
    
    public static $logininfo=null;
    
    public function __construct(){
        
        $this->logininfo = new Zend_Session_Namespace('Login_Info');
    }
    
    
    public static function getSqlString()
    {
        $sql = 'e.epid, p.ipid, e.ipid,';
        $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
        $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
        $sql .= "AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') as gender,";
        $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
        $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
        $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
        $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
        $sql .= "IF(p.admission_date != '0000-00-00',DATE_FORMAT(p.admission_date,'%d\.%m\.%Y'),'') as day_of_admission,";
        $sql .= "IF(p.birthd != '0000-00-00',DATE_FORMAT(p.birthd,'%d\.%m\.%Y'),'') as birthd,";
        $sql .= "p.familydoc_id,";
        
        return $sql; 
    }
    
    public function getActiveIpids()
    {
        
        $whereepid = self::getDocCondition();
    
        $patient = Doctrine_Query::create()
        ->select("*,AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,convert(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1) as zip,convert(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1) as street1,convert(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1) as city,convert(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1) as phone")
        ->from('PatientMaster p')
        ->where('isdelete = 0 and isdischarged = 0')
        ->andWhere('isstandbydelete = 0')
        ->andWhere('isstandby = 0')
        ->orderBy("convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1) ASC");
    
        $patient->leftJoin("p.EpidIpidMapping e");
        $patient->andWhere($whereepid . ' e.clientid = ' . $this->logininfo->clientid);
        $patient->orderBy("convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1) ASC");
    
        $patientexec = $patient->execute();
        $ipidarray = $patientexec->toArray();
    
    
        foreach($ipidarray as $patient)
        {
            $activipids[] = $patient['ipid'];
        }
    
        return $activipids;
    }
    
    

    public static function getDocCondition($user = false)
    {
        if($_POST['doctorname'] != 0)
        {
            if($user)
            {
                $whereepid = $_POST['doctorname'];
            }
            else
            {
                $eipd = Doctrine_Query::create()
                ->select('*')
                ->from('PatientQpaMapping')
                ->where('userid = ?', $_POST['doctorname']);
    
                $epidarray = $eipd->fetchArray();
    
                if($epidarray && count($epidarray) > 0)
                {
    
                    $comma = ",";
                    // 						$epidval = "'0'";
                    $epidval = array();
                    foreach($epidarray as $key => $val)
                    {
                        // 							$epidval .= $comma . "'" . $val['epid'] . "'";
                        $epidval[] = $val['epid'] ;
                        $comma = ",";
                    }
                    $epidval =  implode("','", $epidval);
                    $epidval = "'".$epidval."'";
    
    
                    $whereepid = "epid in (" . $epidval . ") and ";
                }
                else
                {
                    // 						$whereepid = "epid in ('XXXXXXXXXX') and "; //force to get nothing
                    $whereepid = "epid IS NULL AND "; //force to get nothing
                }
            }
        }

        return $whereepid;
    }
    

    
    public static function getDocOrGroupCondition($return_user_patients = false, $return_group_patients = false)
    {
        if($_POST['user_or_group'] != "0")
        {
            $ug_info = explode("-",$_POST['user_or_group']);
             
            if($ug_info[0] == "user")
            {
                $user_id = $ug_info[1];
                $whereepid = $user_id;
            }
            else if($ug_info[0] == "group")
            {
                $group_id = $ug_info[1];
                $user_groups = Usergroup::get_groups_users($group_id,$this->logininfo->clientid,true);
                $whereepid = $user_groups;
            }
             
            if($return_user_patients)
            {
    
                $eipd = Doctrine_Query::create()
                ->select('*')
                ->from('PatientQpaMapping')
                ->whereIn('userid ' . $user_id);
    
                $epidarray = $eipd->fetchArray();
    
                if($epidarray)
                {
    
                    $comma = ",";
                    $epidval = "'0'";
                    foreach($epidarray as $key => $val)
                    {
                        $epidval .= $comma . "'" . $val['epid'] . "'";
                        $comma = ",";
                    }
    
                    $whereepid = "epid in (" . $epidval . ") and ";
                }
                else
                {
                    $whereepid = "epid in ('XXXXXXXXXX') and "; //force to get nothing
                }
    
            }
            elseif($return_group_patients)
            {
                if(empty($user_groups))
                {
                    $user_groups['XXXXXXXXXXXXXXX'] = "99999999";
                }
                 
                $eipd = Doctrine_Query::create()
                ->select('*')
                ->from('PatientQpaMapping')
                ->whereIn('userid ' . $user_groups);
    
                $epidarray = $eipd->fetchArray();
    
                if($epidarray)
                {
    
                    $comma = ",";
                    $epidval = "'0'";
                    foreach($epidarray as $key => $val)
                    {
                        $epidval .= $comma . "'" . $val['epid'] . "'";
                        $comma = ",";
                    }
    
                    $whereepid = "epid in (" . $epidval . ") and ";
                }
                else
                {
                    $whereepid = "epid in ('XXXXXXXXXX') and "; //force to get nothing
                }
            }
        }
        return $whereepid;
    }
    
    
}