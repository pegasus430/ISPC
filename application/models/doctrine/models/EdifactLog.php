<?php
/**
 * Class EdifactLog
 *  Nico :: DemSTepCare - Special EDIFACT-Billing ISPC-2598
 */
Doctrine_Manager::getInstance()->bindComponent('EdifactLog', 'MDAT');

class EdifactLog extends BaseEdifactLog
{

    public static function create_entry($trustcenter, $clientid, $send_ik, $rec_ik, $billing_id){


        $sr = Doctrine_Query::create()
            ->select('count(id) as cc')
            ->from('EdifactLog')
            ->where('clientid=?',$clientid)
            ->andWhere('send_ik=?',$send_ik)
            ->andWhere('rec_ik=?',$rec_ik)
            ->andWhere('trustcenter=?',$trustcenter);
        $srarray=$sr->fetchArray();

        $i=intval($srarray[0]['cc']);
        $i++;

        $sr = Doctrine_Query::create()
            ->select('count(id) as cc')
            ->from('EdifactLog')
            ->where('clientid=?',$clientid)
            ->andWhere('send_ik=?',$send_ik)
            ->andWhere('trustcenter=?',$trustcenter);
        $srarray=$sr->fetchArray();

        $k=intval($srarray[0]['cc']);
        $k++;

        $new = new EdifactLog();
        $new->trustcenter=$trustcenter;
        $new->clientid=$clientid;
        $new->send_ik=$send_ik;
        $new->rec_ik=$rec_ik;
        $new->fileno_ik=$i;
        $new->fileno_trust=$k;
        $new->billing_id=$billing_id;
        $new->save();

        return $new;
    }




}