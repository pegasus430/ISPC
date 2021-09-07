<?php
Doctrine_Manager::getInstance()->bindComponent('SystemsSyncConnections', 'SYSDAT');
class SystemsSyncConnections extends BaseSystemsSyncConnections
{
    /**
     * @param $connection
     * @param $clientid
     * @return mixed array('receive'=>array(), 'send'=>array())
     */

    public static function getConnectionShortcuts($connection, $clientid){

        $cs=Doctrine::getTable('SystemsSyncConnections')->findOneByClientidAndConnection($clientid,$connection);

        if($cs){
            $ser=$cs->shortcuts;
            $arr=json_decode($ser);
            return $arr;
        }

    }

    public static function setConnectionShortcuts($connection, $clientid, $shortcuts){

        $cs=Doctrine::getTable('SystemsSyncConnections')->findOneByClientidAndConnection($clientid,$connection);

        if(!$cs){
            $cs = new SystemsSyncConnections();
            $cs->connection=$connection;
            $cs->clientid=$clientid;
        }
        $cs->shortcuts=json_encode($shortcuts);
        $cs->save();

    }

    public static function setConnectionConfig($connection, $clientid, $conf){

        $cs=Doctrine::getTable('SystemsSyncConnections')->findOneByClientidAndConnection($clientid,$connection);

        if(!$cs){
            $cs = new SystemsSyncConnections();
            $cs->connection=$connection;
            $cs->clientid=$clientid;
        }
        $cs->connection=$conf['id'];//renaming id
        $cs->config=json_encode(array(
            'name'=>$conf['name'],
            'url'=>$conf['url'],
            'user'=>$conf['user'],
            'pass'=>$conf['pass'],
            'localuserid'=>$conf['localuserid']

        ));
        $cs->save();

    }

    public static function getConnectionConfig( $connection, $clientid){

        $cs=Doctrine::getTable('SystemsSyncConnections')->findOneByClientidAndConnection($clientid,$connection);
        $conf=(object) json_decode($cs->config);
        $conf->local=$cs->clientid;
        $conf->id=$cs->connection;

        return $conf;


    }

    public static function getConnections( $clientid){

        $cs=Doctrine::getTable('SystemsSyncConnections')->findByClientid($clientid);
        $configs=array();
        foreach($cs as $conn) {
            $conn->id;//renaming id
            $conf=(object) json_decode($conn->config);
            $conf->local=$conn->clientid;
            $conf->id=$conn->connection;
            $configs[]=$conf;
        }

        return $configs;


    }
}
?>