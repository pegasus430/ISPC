<?php
/**
 * Class EdifactCerts
 *  Nico :: DemSTepCare - Special EDIFACT-Billing ISPC-2598
 */
Doctrine_Manager::getInstance()->bindComponent('EdifactCerts', 'SYSDAT');

class EdifactCerts extends BaseEdifactCerts
{

    /**
     * @param $instr String of annahme.key
     */
    public static function parse_keylist_itsg($instr){

        $keys=explode("\r\n\r\n",$instr);
        $result=[];

        foreach ($keys as $key){
            if(strlen($key)>10){
                $key=str_replace("\r",'',$key);
                $key="-----BEGIN CERTIFICATE-----\n" . $key . "\n-----END CERTIFICATE-----";
                $new=EdifactCerts::parse_key($key);
                if($new){
                    $result[]=$new;
                }
            }
        }
        return $result;
    }

    public static function parse_key($key){
        $parsed=openssl_x509_parse($key);

        if(isset($parsed['subject']) && isset($parsed['subject']['OU']) && isset($parsed['subject']['OU'][1])){
            $new=[];
            $new['name']=$parsed['subject']['OU'][0];
            $new['ik']= preg_replace( '/[^0-9]/', '', $parsed['subject']['OU'][1] );
            $new['valid']=date('Y-m-d',$parsed['validTo_time_t']);
            $new['key']=$key;
            return $new;
        }
        return false;
    }

    public static function add_public_key($trustcenter, $key){
        $key_parsed=EdifactCerts::parse_key($key);

        if(!$key_parsed){
            return false;
        }
        $new = Doctrine::getTable('EdifactCerts')->findByIkAndTypeAndTrustcenter($key_parsed['ik'], 'partner_public', $trustcenter);


        if(!count($new)) {
            $new = new EdifactCerts();
        }else{
            $new=$new[0];
        }


        $new->trustcenter=$trustcenter;
        $new->type='partner_public';
        $new->ik=$key_parsed['ik'];
        $new->name=$key_parsed['name'];
        $new->valid=$key_parsed['valid']." 00:00:00";
        $new->cert=$key_parsed['key'];
        $new->save();
    }

    public static function list_ik_public_keys($trustcenter){
        $out=Doctrine::getTable('EdifactCerts')->findByTypeAndTrustcenter('partner_public', $trustcenter);
        return $out;
    }

    public static function get_ik_public_key($trustcenter, $ik){
        $out=Doctrine::getTable('EdifactCerts')->findByTypeAndTrustcenterAndIk('partner_public', $trustcenter, $ik);
        if(count($out)){
            return $out[0];
        }
        else{
            return false;
        }
    }

    public static function get_private_key($trustcenter, $ik){
        $cert=Doctrine::getTable('EdifactCerts')->findOneByIkAndTypeAndTrustcenter($ik, 'cert_public', $trustcenter);
        $key=Doctrine::getTable('EdifactCerts')->findOneByIkAndTypeAndTrustcenter($ik, 'key_private', $trustcenter);
        If($key){
            $key['cert']=Pms_CommonData::aesDecrypt($key['cert']);
        }
        return [$cert, $key];
    }

    public static function add_private_key($trustcenter, $ik, $key){
        $new = Doctrine::getTable('EdifactCerts')->findByIkAndTypeAndTrustcenter($ik, 'key_private', $trustcenter);
        if(!count($new)) {
            $new = new EdifactCerts();
        }else{
            $new=$new[0];
        }
        $new->trustcenter=$trustcenter;
        $new->type='key_private';
        $new->ik=$ik;
        $new->cert=Pms_CommonData::aesEncrypt($key);
        $new->save();
    }
    public static function add_public_cert($trustcenter, $ik, $cert){
        $key_parsed=openssl_x509_parse($cert);

        $new = Doctrine::getTable('EdifactCerts')->findByIkAndTypeAndTrustcenter($ik, 'cert_public', $trustcenter);
        if(!count($new)) {
            $new = new EdifactCerts();
        }else{
            $new=$new[0];
        }
        $new->trustcenter=$trustcenter;
        $new->type='cert_public';
        $new->ik=$ik;
        $new->cert=$cert;
        $new->valid=date('Y-m-d',$key_parsed['validTo_time_t']);
        $new->save();
    }

    public static function get_client_ik($clientid){
        $new = Doctrine::getTable('Client')->findOneById($clientid);

        return Pms_CommonData::aesDecrypt($new->institutskennzeichen);
    }
}