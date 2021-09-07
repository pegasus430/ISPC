<?php
//ISPC-2697, elena, 13.11.2020

class Anordnung extends BaseAnordnung
{
    /**
     * for table generating purposes only
     * helpful if the table have more than 10 fields
     *
     * @return string
     * @throws Doctrine_Export_Exception
     * @throws Doctrine_Table_Exception
     */
    public function getSql(){

        $data = $this->getTable()->getExportableFormat();
        $export = new Doctrine_Export();
        $sql = $export->createTableSql($data['tableName'], $data['columns'], $data['options']);
        return ($sql) ;
    }

    public static function getPatientAnordnungen($ipid, $active = true, $notdeleted = true){

        $drop =  Doctrine_Query::create()
            ->select('*')
            ->from('Anordnung')
            ->where("ipid = ?", $ipid)
            ;
        if($active)
        {
            $drop->andWhere('is_active = 1');
        }
        if($notdeleted)
        {
            $drop->andWhere('isdelete = 0');
        }
        $droparray = $drop->fetchArray();
        return $droparray;

    }
    public static function getPatientAnordnungenWithout($ipid, $excludeType, $active = true, $notdeleted = true){

        $drop =  Doctrine_Query::create()
            ->select('*')
            ->from('Anordnung')
            ->where("ipid = ?", $ipid)
            ->andWhere('anordnung_type != ?', $excludeType)
            ;
        if($active)
        {
            $drop->andWhere('is_active = 1');
        }
        if($notdeleted)
        {
            $drop->andWhere('isdelete = 0');
        }
        $droparray = $drop->fetchArray();
        return $droparray;

    }
    /**
     * ISPC-2891 Ancuta 27.04.2021
     * @param array $ipids
     * @param unknown $excludeType
     * @param boolean $active
     * @param boolean $notdeleted
     * @return void|unknown
     */
    public static function getMultiplePatientAnordnungenWithout($ipids = array(), $excludeType, $active = true, $notdeleted = true){

        
        if(empty($ipids)){
            return;
        }
        $drop =  Doctrine_Query::create()
            ->select('*')
            ->from('Anordnung')
            ->whereIn("ipid", $ipids)
            ->andWhere('anordnung_type != ?', $excludeType)
            ;
        if($active)
        {
            $drop->andWhere('is_active = 1');
        }
        if($notdeleted)
        {
            $drop->andWhere('isdelete = 0');
        }
        $droparray = $drop->fetchArray();
        return $droparray;

    }

    public static function getPatientBeatmungAnordnungen($ipid, $active = true, $notdeleted = true){

        $drop =  Doctrine_Query::create()
            ->select('*')
            ->from('Anordnung')
            ->where("ipid = ?", $ipid)
            ->andWhere('anordnung_type=?', 'beatmung')
            ;
        if($active)
        {
            $drop->andWhere('is_active = 1');
        }
        if($notdeleted)
        {
            $drop->andWhere('isdelete = 0');
        }
        $droparray = $drop->fetchArray();
        return $droparray;

    }
    /**
     * ISPC-2891 Ancuta 27.04.2021
     * @param array $ipids
     * @param boolean $active
     * @param boolean $notdeleted
     * @return void|unknown
     */
    public static function getMultiplePatientBeatmungAnordnungen($ipids =array(), $active = true, $notdeleted = true){

        if(empty($ipids)){
            return;
        }
        $drop =  Doctrine_Query::create()
            ->select('*')
            ->from('Anordnung')
            ->whereIn("ipid", $ipids)
            ->andWhere('anordnung_type=?', 'beatmung')
            ;
        if($active)
        {
            $drop->andWhere('is_active = 1');
        }
        if($notdeleted)
        {
            $drop->andWhere('isdelete = 0');
        }
        $droparray = $drop->fetchArray();
        return $droparray;

    }


    public static function getPatientAnordnungenForMachine($ipid, $machine, $active = true, $notdeleted = true){

        $drop =  Doctrine_Query::create()
            ->select('*')
            ->from('Anordnung')
            ->where("ipid = ?", $ipid)
            ->andWhere('machine=?', $machine)
        ;
        if($active)
        {
            $drop->andWhere('is_active = 1');
        }
        if($notdeleted)
        {
            $drop->andWhere('isdelete = 0');
        }
        $droparray = $drop->fetchArray();
        return $droparray;

    }

    /**
     * @throws Doctrine_Query_Exception
     */
    public function deactivate(){
        $drop =  Doctrine_Query::create()
        ->update("Anordnung")
            ->set('timelinedata', '?', '')
            ->set('is_active', 0)
            ->where("id=?", $this->id);

        //print_r($drop->getSqlQuery(['id' => $this->id]));
        $drop->execute();

    }

    public function activate(){
        $drop =  Doctrine_Query::create()
        ->update("Anordnung")
            ->set('is_active', 1)
            ->where("id=?", $this->id);
        $drop->execute();

    }

    public function remove(){
        $drop =  Doctrine_Query::create()
        ->update("Anordnung")
            ->set('is_active', 0)
            ->set('isdelete', 1)
            ->where("id=?", $this->id);
        $drop->execute();

    }

    /**
     * rearranges timeline
     * new/changed timeline data have to be written
     * other data have to be corrected to prevent overlapping
     *
     * @param $ipid
     * @param $id_to_change
     * @param $time_from
     * @param $time_till
     */
    public function rearrangeTimeline($ipid, $id_to_change, $time_from, $time_till){
        $aAnordnungen = Anordnung::getPatientBeatmungAnordnungen($ipid, true);
        $time_from = intval($time_from);
        $time_till = intval($time_till);
        //ISPC-2906,Elena,27.04.2021
        //don't save and compare time parts if from and till are equal
        if($time_from == $time_till){
            return;
        }
        //ISPC-2906,Elena,27.04.2021
        $timelineRequestDataHours = [];
        for($i = $time_from;$i<= $time_till;$i++){
            $timelineRequestDataHours[] = $i;
        }


        foreach($aAnordnungen as $anordnung_item){
            $aTimeline = json_decode($anordnung_item['timelinedata'], true);
            $newTimeline = [];// $aTimeline;//ISPC-2906,Elena,27.04.2021
            $groupCounter = 0;
            if(count($aTimeline) == 0 && $anordnung_item['id'] == $id_to_change ){
                $newTimeline[] = ['from' => $time_from, 'till' => $time_till];
                //ISPC-2906,Elena,27.04.2021
                $groupCounter++;

            }

            $timegroup_found = false;
            foreach($aTimeline as $group){

                $from = intval($group['from']);
                $till = intval($group['till']);
                //ISPC-2906,Elena,27.04.2021
                $timelineGroupDataHours = [];
                for($i = $from;$i<= $till;$i++){
                    $timelineGroupDataHours[] = $i;
                }

                if($anordnung_item['id'] == $id_to_change){
                  // same Anordnung
                  if(array_intersect($timelineGroupDataHours, $timelineRequestDataHours)){ //ISPC-2906,Elena,27.04.2021
                      //same group, changed (larger, smaller, pushed right/left) or unchanged
                      $newTimeline[$groupCounter] = ['from' => $time_from, 'till' => $time_till];
                      $timegroup_found = true;

                  }else{
                      // new group
                      //$newTimeline[] = ['from' => $time_from, 'till' => $time_till];
                      //ISPC-2906,Elena,27.04.2021
                      if($from != $till){
                          $newTimeline[$groupCounter] = $group;
                      }


                  }

                }else{
                    //another Anordnung, have to be changed if needed
                    if($group['from'] > $time_from &&  $group['from'] < $time_till){
                        if($group['till'] > $time_till){
                            $from = $time_till; //push to right
                        }else{
                            $from = $time_till;
                            $till = $time_till;

                        }

                    }elseif(($group['till'] > $time_from) &&  ($group['till'] < $time_till)){
                        $till = $time_from; //push to left

                    }elseif(($group['from'] < $time_from ) && ($group['till'] > $time_till)){//<!-- ISPC-2816,Elena,12.02.2021-->
                        $till = $time_from;
                    }
                    //ISPC-2906,Elena,27.04.2021
                    if($from != $till){
                    $newTimeline[$groupCounter] = ['from' => $from, 'till' => $till];
                    }//ISPC-2906,Elena,27.04.2021

                }



                $groupCounter ++;


            }
            if(!$timegroup_found && ($anordnung_item['id'] == $id_to_change)){
                //new group
                //ISPC-2906,Elena,27.04.2021
                if(!in_array(['from' => $time_from, 'till' => $time_till], $newTimeline) && ($time_from != $time_till)){
                $newTimeline[] = ['from' => $time_from, 'till' => $time_till];
            }

            }//ISPC-2906,Elena,27.04.2021

            $entity = new Anordnung();
            $anordnung = $entity->getTable()->find($anordnung_item['id'], Doctrine_Core::HYDRATE_RECORD);

            $anordnung->setAnordnungTime($newTimeline);

        }
    }


    /**
     * saves timeline data (multiple entries) for Anordnung as json string
     *
     * @param $aTimeline
     * @param bool $toSave
     * @throws Doctrine_Connection_Exception
     */
    public function setAnordnungTime($aTimeline, $toSave = true){ //ISPC-2906,Elena,27.04.2021

        $timelinedataAsString = (json_encode($aTimeline));
        $this->timelinedata = $timelinedataAsString;
        //ISPC-2906,Elena,27.04.2021
        if($toSave){
	        $this->replace();
		}



    }

    /**
     * finds and removes timeline parts from Anordnung timelinedata
     * timeline part format : ['from' => $from, 'till' => $till]
     * //ISPC-2906,Elena,27.04.2021
     *
     * @param array $aParts
     * @param bool $toSave
     */
    public function removeAnordnungTimelineParts($aParts, $toSave = true){
        $timelinedataAsArray = json_decode($this->timelinedata, true);
        $newTimelineData = [];
        foreach($timelinedataAsArray as $timelinePart){
            foreach($aParts as $partToRemove){
                if( !(($timelinePart['from'] == $partToRemove['from']) && ($timelinePart['till'] == $partToRemove['till']))){
                    $newTimelineData[] = $timelinePart;
                }
            }
        }
        $this->setAnordnungTime($newTimelineData, $toSave);

    }

    public static function getPatientAnordnungenInactive($ipid){

        $drop =  Doctrine_Query::create()
            ->select('*')
            ->from('Anordnung')
            ->where("ipid = ?", $ipid);

        $drop->andWhere('is_active = 0');

        $droparray = $drop->fetchArray();
        return $droparray;

    }

    public static function getPatientBeatmungAnordnungenInactive($ipid){

        $drop =  Doctrine_Query::create()
            ->select('*')
            ->from('Anordnung')
            ->where("ipid = ?", $ipid);

        $drop->andWhere('is_active = 0');
        //TODO-3943,Elena,10.03.2021
        $drop->andWhere('isdelete = 0');
        $drop->andWhere('anordnung_type=?', 'beatmung');

        $droparray = $drop->fetchArray();
        return $droparray;

    }

    public static function groupactivate($aIds){
        $drop =  Doctrine_Query::create()
            ->update("Anordnung")
            ->set('is_active', 1)
            ->set('isdelete', 0) //TODO-3939,Elena,10.03.2021
            ->whereIn("id", $aIds);
        $drop->execute();


    }

}