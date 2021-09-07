<?php
/**
 * 
 * @author claudiu 
 * Jun 22, 2018
 * 
 * full re-write Nico's
 *
 */
class PatientVersorger extends BasePatientVersorger
{

    public static function updateEntry($ipid = '', $key = '', $value = '')
    {
        //delete the old value
        self::deleteEntry($ipid, $key);
        
        //insert new value
        $new = new PatientVersorger();
        $new->ipid = $ipid;
        $new->k = $key;
        $new->v = $value;
        $new->save();
    }

    public static function getEntry($ipid, $key)
    {
        $entry = Doctrine_Core::getTable('PatientVersorger')->findOneByIpidAndK($ipid, $key);
        
        if ($entry) {
            return $entry->v;
        } else {
            return array();
        }
        
        
    }
    
    public static function deleteEntry($ipid = '', $key = '')
    {
        
        return Doctrine_Query::create()
        ->delete('PatientVersorger')
        ->where("ipid = ?", $ipid)
        ->andWhere("k = ?", $key)
        ->execute()
        ;
        
    }
}