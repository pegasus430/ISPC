<?php
Doctrine_Manager::getInstance()->bindComponent('CertifiedDevices', 'SYSDAT');

class CertifiedDevices extends BaseCertifiedDevices
{

    public function get_certified_devices($clientid)
    {}
    
    
    
    public static function fetch_devices($userid = 0, $clientid = 0)
    {
        return Doctrine_Query::create()
        ->select('*')
        ->from('CertifiedDevices')
        ->where('userid = ?', $userid)
        ->andWhere('clientid = ?', $clientid)
        ->fetchArray();
    }
    
    
    public function findOrCreateOneByClientidAndUseridAndDeviceid($clientid = 0, $userid = 0, $deviceid = '', $data) 
    {
        
        if ( ! $entity = $this->getTable()->findOneByClientidAndUseridAndDeviceid($clientid, $userid, $deviceid, Doctrine_Core::HYDRATE_RECORD)) {
            //this is insert
           
            $entity = $this->getTable()->create(["userid" => $userid, "deviceid" => $deviceid, "clientid" => $clientid ]);
            
            $entity->assignDefaultValues(false);
        
        } else {
            //this is update
        }
        
        unset($data[$this->getTable()->getIdentifier()]);
        
        $entity->fromArray($data); //update
         
        $entity->save(); //at least one field must be dirty in order to persist
         
        return $entity;
    }
    
}


?>