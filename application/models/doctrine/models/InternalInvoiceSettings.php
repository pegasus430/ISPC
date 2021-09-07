<?php
Doctrine_Manager::getInstance()->bindComponent('InternalInvoiceSettings', 'SYSDAT');

class InternalInvoiceSettings extends BaseInternalInvoiceSettings
{

    public function getUserInternalInvoiceSettings($user, $clientid)
    {
        $user_detauls = new User();
        $user_data = $user_detauls->getUsersDetails($user);
        $user_initials[$user] = mb_substr($user_data[$user]['first_name'], 0, 1, "UTF-8") . "" . mb_substr($user_data[$user]['last_name'], 0, 1, "UTF-8");
        
        $select = Doctrine_Query::create()->select('*')
            ->from('InternalInvoiceSettings')
            ->where(' user = ?', $user)
            ->andWhere('client = ?', $clientid);
        $sel_res = $select->fetchArray();
        
        if ($sel_res) {
            foreach ($sel_res as $k_res => $v_res) {
                $sel_array[$v_res['user']] = $v_res;
                
                if (strlen($v_res['invoice_start']) == '0') {
                    $sel_array[$v_res['user']]['invoice_prefix'] = '';
                    $sel_array[$v_res['user']]['invoice_start'] = '';
                }
            }
        } else {
            // Default values
            $sel_array[$user]['invoice_prefix'] = $user_initials[$user];
            $sel_array[$user]['invoice_start'] = '1000';
            $sel_array[$user]['invoice_pay_days'] = '30';
        }
        
        return $sel_array;
    }

    public function get_users_InternalInvoiceSettings($clientid, $users = array(), $client_invoice_due_date = false)
    {
        if (empty($clientid)) {
            return array();
        }
        
        if (! $client_invoice_due_date) {
            
            $client_details_m = new Client();
            $client_details = $client_details_m->getClientDataByid($clientid);
            
            $client_invoice_due_days = $client_details[0]['invoice_due_days'];
        }
        
        if (empty($users)) {
            
            $users = array();
            $select_users = Doctrine_Query::create()->select('id')
                ->from('User')
                ->where('clientid = ?', $clientid)
                ->andWhere('isdelete = 0 ')
                ->fetchArray();
            
            if (! empty($select_users)) {
                foreach ($select_users as $k => $udata) {
                    $users[] = $udata['id'];
                }
            }
        }
        
        if (empty($users)) {
            return array();
        }
        $select = Doctrine_Query::create()->select('*')
            ->from('InternalInvoiceSettings INDEXBY user')
            ->whereIn(' user', $users)
            ->andWhere('client = ?', $clientid)
            ->fetchArray();
        
        if (! empty($select)) {
            
            foreach ($users as $user) {
                
                if (! isset($select[$user])) {
                    if (! empty($client_invoice_due_days)) {
                        
                        $select[$user]['invoice_pay_days'] = $client_invoice_due_days; // CLIENT SETTINGS
                    } else {
                        
                        $select[$user]['invoice_pay_days'] = '30'; // DEFAULT
                    }
                }
            }
            
            return $select;
        } else {
            
            return;
        }
    }
}

?>