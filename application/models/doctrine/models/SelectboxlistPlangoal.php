<?php
/**
* Maria:: Migration CISPC to ISPC 22.07.2020
*/

Doctrine_Manager::getInstance()->bindComponent('SelectboxlistPlangoal', 'MDAT');
class SelectboxlistPlangoal extends BaseSelectboxlistPlangoal
{

    public function getList ($listname)
    {
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid=$logininfo->clientid;

        $groups_sql = Doctrine_Query::create()
            ->select('*')
            ->from('SelectboxlistPlangoal')
            ->where('clientid = ?', $clientid)
            ->andWhere('isdelete = 0')
            ->andWhere('listname = ?',$listname);
        $groupsarray = $groups_sql->fetchArray();

        $returnarray = array();

        foreach ($groupsarray as $row){
            if(!$returnarray[$row['goal']]){
                $returnarray[$row['goal']]=array('category'=>$row['category'], 'plan'=>array($row['plan'],), 'dbid'=>array($row['id'],));
            }
            else{
                $returnarray[$row['goal']]['plan'][]=$row['plan'];
                $returnarray[$row['goal']]['dbid'][]=$row['id'];
            }
        }

        return $returnarray;

    }

    public function getListOrDefault($listname)
    {
        $list = $this->getList($listname);
        if(!$list){
            $list = ClientConfig::getDefaultConfig($listname);
        }

        return $list;

    }

    public function replaceList ( $listname, $listarray)
    {

        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid=$logininfo->clientid;

        $Q = Doctrine_Query::create()
            ->update('SelectboxlistPlangoal')
            ->set('isdelete','1')
            ->where('clientid = ?', $clientid)
            ->andWhere('isdelete = 0')
            ->andWhere('listname = ?',$listname);
        $result = $Q->execute();

        foreach ($listarray as $item){
            foreach($item['plan'] as $plan){
                if($plan){
                    $sbl = new SelectboxlistPlangoal();
                    $sbl->clientid=$clientid;
                    $sbl->listname=$listname;
                    $sbl->category=$item['category'];
                    $sbl->goal=$item['goal'];
                    $sbl->plan=trim($plan);
                    $sbl->save();
                }
            }
        }
    }

}
?>
