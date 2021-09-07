<?php
Doctrine_Manager::getInstance()->bindComponent('Stammblattlmu', 'MDAT');

class Stammblattlmu extends BaseStammblattlmu
{

    public function get_all_entries($ipid)
    {
        $hquery = Doctrine_Query::create()
            ->select('*')
            ->from('Stammblattlmu INDEXBY id')
            ->where("ipid='" . $ipid . "'")
            ->andwhere("isdelete = 0")
            ->orderBy("create_date DESC");
        $harray = $hquery->fetchArray();
        
        if ($harray) 
        {
            return $harray;
        } 
        else 
        {
            return false;
        }
    }

    
    public function get_entry($ipid,$id)
    {
        $hquery = Doctrine_Query::create()
            ->select('*')
            ->from('Stammblattlmu')
            ->where("ipid='" . $ipid . "'")
            ->andWhere("id='" . $id . "'")
            ->andwhere("isdelete = 0")
            ->orderBy("create_date ASC");
        $harray = $hquery->fetchArray();
        
        if ($harray) 
        {
            $stamblatt_data = $harray[0];
            return $stamblatt_data ;
        } 
        else 
        {
            return false;
        }
    }
}

?>