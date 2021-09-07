<?php

Doctrine_Manager::getInstance()->bindComponent('MemberPaymentMethod', 'SYSDAT');

class MemberPaymentMethod extends BaseMemberPaymentMethod
{

    public static function get_list($client = 0, $isdeleted = 0)
    {
        $fdocarray = Doctrine::getTable('MemberPaymentMethod')->createQuery('mpm indexBy id')
            ->select('*')
            ->where('clientid = ?', $client)
            ->andWhere('isdelete = ?', $isdeleted)
            ->fetchArray();
        
        return $fdocarray;
    }
	
}