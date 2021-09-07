<?php
//ISPC-2697, elena, 09.11.2020

/**
 * Class Machine
 *
 * it is a basic Machine class, implemented for ventilation machine (Beatmung)
 * it can be user for other devices with diverses parameters too
 */
class Machine extends BaseMachine
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


    /**
     * finds machines for client and type, for example, for machine_type 'beatmung'
     *
     * @param $clientid
     * @param $type
     * @return array
     */
    public function getClientMachinesForType($clientid, $type){
        $drop = Doctrine_Query::create()
            ->select('*')
            ->from('Machine')
            ->where('clientid=?', $clientid)
            ->andWhere("machine_type=?" , $type );
        $droparray = $drop->fetchArray();
        $aRetValue = [];
        foreach($droparray as $entry){
            $aRet = $entry;
            $aRet['name'] = $entry['machine_name'];
            $aRet['parameters'] = json_decode( $aRet['parameters'], true);
            $aRetValue[] = $aRet;
        }

        return $aRetValue;
    }

    public function getClientMachinesWithoutType($clientid, $excludeType){
        $drop = Doctrine_Query::create()
            ->select('*')
            ->from('Machine')
            ->where('clientid=?', $clientid)
            ->andWhere("machine_type != ?" , $excludeType );
        $droparray = $drop->fetchArray();
        $aRetValue = [];
        foreach($droparray as $entry){
            $aRet = $entry;
            $aRet['name'] = $entry['machine_name'];
            $aRet['parameters'] = json_decode( $aRet['parameters'], true);
            $aRetValue[] = $aRet;
        }
#print_r($aRetValue);
        return $aRetValue;

    }

    public function getTypes(){
        $aTypes = [
            'beatmung' => 'Beatmung',
            'Cough Assist' => 'Hustenhelfer',
            'Vernebler' => 'Vernebler',
            'Temperierung' => 'Temperierung'
        ];
        return $aTypes;
    }




}