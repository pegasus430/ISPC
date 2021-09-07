<?php

	require_once("Pms/Form.php");

	class Application_Form_InvoiceSystem extends Pms_Form {
    
    // used in edit
    public function validate($post)
    {
        $Tr = new Zend_View_Helper_Translate();
        $validate = new Pms_Validation();
        
        if ($post['period_id'] == '0') {
            $this->error_message['invoice_vv'] = $Tr->translate('no_invoice_vv');
            return false;
        }
        
        if (! $validate->isdate($post['curent_sapv_from'])) {
            $this->error_message['sapv_date_from'] = $Tr->translate('invalid_sapv_start_date');
            return false;
        }
        
        if (! $validate->isdate($post['curent_sapv_till'])) {
            $this->error_message['sapv_date_till'] = $Tr->translate('invalid_sapv_end_date');
            return false;
        }
        
        if (! $validate->isdate($post['invoice_date_from'])) {
            $this->error_message['invoice_date_from'] = $Tr->translate('invalid_invoice_start_date');
            return false;
        }
        
        if (! $validate->isdate($post['invoice_date_till'])) {
            $this->error_message['invoice_date_till'] = $Tr->translate('invalid_invoice_end_date');
            return false;
        }
        
        if ($validate->isdate($post['invoice_date_from']) && $validate->isdate($post['invoice_date_till'])) {
            $start = strtotime($post['invoice_date_from']);
            $end = strtotime($post['invoice_date_till']);
        }
        
        if ($validate->isdate($post['curent_sapv_from']) && $validate->isdate($post['curent_sapv_till'])) {
            $start_sapv = strtotime($post['curent_sapv_from']);
            $end_sapv = strtotime($post['curent_sapv_till']);
        }
        
        if ($start_sapv <= $end_sapv) {
            if ($start <= $end) {
                return true;
            } else {
                $this->error_message['date'] = $Tr->translate('invalid_invoice_date_period');
                return false;
            }
        } else {
            $this->error_message['sapv_date'] = $Tr->translate('invalid_sapv_date_period');
            return false;
        }
    }

    public function insert_invoice($invoice_type = false, $master_data_all = false)
    {
        if ( ! $invoice_type){
            return false;
        }
        foreach ($master_data_all['ipids'] as $k_ipid => $v_ipid) {
            $sys_invoices = new InvoiceSystem();
            
            $invoice_data = $master_data_all['patients'][$v_ipid]['invoice_data'];
            $clientid = $master_data_all['client']['id'];
            
            $sapv_id = $invoice_data['sapv']['id'];
            $admission_id = $invoice_data['admissionid'];
            
            $quarter_id = $invoice_data['quarterid'];
            
            // invoice period dates
            if (date("Y", strtotime($invoice_data['period']['start'])) != "1970" && strlen($invoice_data['period']['start']) > '0') {
                $current_period_start = date('Y-m-d 00:00:00', strtotime($invoice_data['period']['start']));
            } else {
                $current_period_start = "0000-00-00 00:00:00";
            }
            
            if (date("Y", strtotime($invoice_data['period']['end'])) != "1970" && strlen($invoice_data['period']['end']) > '0') {
                $current_period_end = date('Y-m-d 23:59:59', strtotime($invoice_data['period']['end']));
            } else {
                $current_period_end = "0000-00-00 00:00:00";
            }
            
            // invoice number and prefix
            $bw_sapv_invoice_number = $sys_invoices->get_next_invoice_number($invoice_type, $master_data_all['client']['id'], true);
            $prefix = $bw_sapv_invoice_number['prefix'];
            $invoicenumber = $bw_sapv_invoice_number['invoicenumber'];
            
            // insert invoice
            $ins_inv = new InvoiceSystem();
            $ins_inv->invoice_type = $invoice_type;
            
            $ins_inv->invoice_start = $current_period_start;
            $ins_inv->invoice_end = $current_period_end;
            $ins_inv->start_active = $current_period_start;
            $ins_inv->end_active = $current_period_end;
            /* 
			// TODO-1815 12.09.2018 Ancuta
            $ins_inv->start_active = date('Y-m-d H:i:s', strtotime($invoice_data['first_active_day']));
            $ins_inv->end_active = date('Y-m-d H:i:s', strtotime($invoice_data['last_active_day']));
             */
            
            $ins_inv->start_sapv = date('Y-m-d H:i:s', strtotime($invoice_data['first_sapv_day']));
            $ins_inv->end_sapv = date('Y-m-d H:i:s', strtotime($invoice_data['last_sapv_day']));
            $ins_inv->sapv_approve_date = date('Y-m-d H:i:s', strtotime($invoice_data['sapv_approve_date']));
            $ins_inv->sapv_approve_nr = $invoice_data['sapv_approve_nr'];
            $ins_inv->ipid = $v_ipid;
            $ins_inv->client = $clientid;
            
            //ISPC-2585 Ancuta 15.06.2020
            if(!empty($master_data_all['userid'])){
                $ins_inv->userid = $master_data_all['userid'];
            }
            if(!empty($master_data_all['external_invoice_id'])){
                $ins_inv->external_invoice_id = $master_data_all['external_invoice_id'];
            }
            // --
            $ins_inv->show_boxes = 'show_box_active,show_box_patient,show_box_sapv';               //ISPC-2747 pct.b Lore 27.11.2020
            
            $ins_inv->prefix = $prefix;
            $ins_inv->invoice_number = $invoicenumber;
            $ins_inv->invoice_total = Pms_CommonData::str2num($master_data_all['grand_total'][$v_ipid]);
            $ins_inv->status = '1'; // DRAFT - ENTWURF
            $ins_inv->address = (strlen($invoice_data['patient_address']) > '0') ? $invoice_data['patient_address'] : $invoice_data['address'];
            $ins_inv->footer = $invoice_data['footer'];
            $ins_inv->sapvid = $sapv_id;
            $ins_inv->admissionid = $admission_id;
            if(!empty($quarter_id)){
                $ins_inv->quarterid = $quarter_id;
            }
            $ins_inv->completed_date = date('Y-m-d H:i:s');
            $ins_inv->save();
            $inserted_id = $ins_inv->id;
            
            foreach ($master_data_all['invoice_items'][$v_ipid] as $k_shortcut_inv => $v_values_inv) {
                $invoice_items[] = array(
                    'invoice' => $inserted_id,
                    'invoice_type' => $invoice_type,
                    'client' => $clientid,
                    'shortcut' => $v_values_inv['shortcut'],
                    'description' => $v_values_inv['description'],//ISPC-2461 Ancuta
                    'location_type' => $v_values_inv['location_type'],
                    'qty' => $v_values_inv['qty'],
                    'price' => $v_values_inv['price'],
                    'total' => $v_values_inv['shortcut_total']
                );
            }
            
            $inserted_ids[] = $inserted_id;
        }
        
        if (count($invoice_items) > 0) {
            // insert many records with one query!!
            $collection = new Doctrine_Collection('InvoiceSystemItems');
            $collection->fromArray($invoice_items);
            $collection->save();
        }
        
        return $inserted_ids;
    }

    public function create_invoice($invoice_type = false, $clientid, $post)
    {
        if ( ! $invoice_type){
            return false;
        }
        
        $Tr = new Zend_View_Helper_Translate();
        
        $ins_inv = new InvoiceSystem();
        $ins_inv->invoice_type = $invoice_type;
        
        $ins_inv->ipid = $post['ipid'];
        $ins_inv->client = $clientid;
        
        $ins_inv->krankenkasse = $post['krankenkasse'];
        $ins_inv->patient_name = $post['patient_name'];
        $ins_inv->geb = $post['geb'];
        $ins_inv->kassen_nr = $post['kassen_nr'];
        $ins_inv->versicherten_nr = $post['versicherten_nr'];
        $ins_inv->ins_status = $post['status'];
        $ins_inv->betriebsstatten_nr = $post['betriebsstatten_nr'];
        $ins_inv->arzt_nr = $post['arzt_nr'];
        $ins_inv->topdatum = $post['topdatum'];
        $ins_inv->client_ik = $post['client_ik'];
        
        $ins_inv->invoice_start = date('Y-m-d H:i:s', strtotime($post['invoice_date_from']));
        $ins_inv->invoice_end = date('Y-m-d H:i:s', strtotime($post['invoice_date_till']));
        $ins_inv->main_diagnosis = $post['main_diagnosis'];
        
        $ins_inv->sapv_id = date('Y-m-d H:i:s', strtotime($post['sapv_id']));
        $ins_inv->sapv_start = date('Y-m-d H:i:s', strtotime($post['curent_sapv_from']));
        $ins_inv->sapv_end = date('Y-m-d H:i:s', strtotime($post['curent_sapv_till']));
        
        $ins_inv->prefix = $post['prefix'];
        $ins_inv->invoice_number = $post['invoice_number'];
        $ins_inv->invoice_total = Pms_CommonData::str2num($post['grand_total']);
        
        $ins_inv->show_boxes = 'show_box_active,show_box_patient,show_box_sapv';               //ISPC-2747 pct.b Lore 27.11.2020
        
        $ins_inv->status = '1'; // DRAFT - ENTWURF
        $ins_inv->stample = $post['stample'];
        
        if (! empty($post['sapv_erst'])) {
            $ins_inv->sapv_erst = $post['sapv_erst'];
        } else {
            $ins_inv->sapv_erst = "0";
        }
        
        if (! empty($post['sapv_folge'])) {
            $ins_inv->sapv_folge = $post['sapv_folge'];
        } else {
            $ins_inv->sapv_folge = "0";
        }
        
        $ins_inv->date_delivery = $post['date_delivery'];
        $ins_inv->sig_date = $post['sig_date'];
        $ins_inv->bottom_signature = $post['bottom_signature'];
        
        $ins_inv->save();
        $inserted_id = $ins_inv->id;
        
        if ($inserted_id) {
            foreach ($post['items'] as $k_item => $v_item) {
                $description = $Tr->translate($k_item);
                $invoice_items[] = array(
                    /* 'invoice' => $inserted_id,
                    'invoice_type' => $invoice_type,
                    'client' => $clientid,
                    'shortcut' => $v_values_inv['shortcut'],
                    'qty' => $v_values_inv['qty'],
                    'price' => $v_values_inv['price'],
                    'total' => $v_values_inv['shortcut_total'], */
                    
                    
                    'invoice' => $inserted_id,
                    'invoice_type' => $invoice_type,
                    'client' => $clientid,
                    'shortcut' => $v_item['shortcut'],
                    'description' => $description,
                    'qty_home' => $v_item['qty_gr']['p_home'],
                    'qty_nurse' => $v_item['qty_gr']['p_nurse'],
                    'qty_hospiz' => $v_item['qty_gr']['p_hospiz'],
                    'price_home' => Pms_CommonData::str2num($v_item['price_gr']['p_home']),
                    'price_nurse' => Pms_CommonData::str2num($v_item['price_gr']['p_nurse']),
                    'price_hospiz' => Pms_CommonData::str2num($v_item['price_gr']['p_hospiz']),
                    'total_home' => Pms_CommonData::str2num($v_item['total']['p_home']),
                    'total_nurse' => Pms_CommonData::str2num($v_item['total']['p_nurse']),
                    'total_hospiz' => Pms_CommonData::str2num($v_item['total']['p_hospiz']),
                    'isdelete' => '0'
                );
            }
            
            $collection = new Doctrine_Collection('RpInvoiceItems');
            $collection->fromArray($invoice_items);
            $collection->save();
            
            return true;
        } else {
            return false;
        }
    }

    public function edit_invoice($invoice_type = false, $invoice, $clientid, $post, $status)
    {
        
        if ( ! invoice_type){
            return false;
        }
        
        if ( ! $invoice || empty($invoice)) {
            return false;
        }
//         dd($post);
        $invoice_obj = Doctrine_Query::create()
        ->select("*")
        ->from('InvoiceSystem')
        ->Where("id = ?", $invoice)
        ->andWhere('invoice_type = ?',$invoice_type)
        ->andWhere('isdelete = 0')
        ->fetchOne();
        
        if( empty($invoice_obj)){
            return false;
        }
      
//         dd($status,$invoice_obj->toArray());
        
        // update initial invoice
        if ($status == '4') {
            /* $update_invoice = Doctrine_Query::create()
            ->update("InvoiceSystem")
            ->set('isdelete', '1')
            ->where('id = ?', $invoice)
            ->andWhere('invoice_type = ?', $invoice_type)
            ->andWhere('status != "3"')
            ->andWhere('status != "5"');
            $update_invoice->execute(); */
            if($invoice_obj->status != "3" && $invoice_obj->status != "5"  ){
                $invoice_obj->set("isdelete", "1");
            } 
            
        }
        
        if ($status != '1' && $status != '4' && strlen($post['pdf']) == 0) {
            /* $update_invoice = Doctrine_Query::create()
            ->update("InvoiceSystem")
            ->where('id = ?', $invoice)
            ->andWhere('invoice_type = ?', $invoice_type)
            ->set('invoice_number', "?", $post['invoice_number'])
            ->set('prefix', "?", $post['prefix'])
            ->set('completed_date', "?", date('Y-m-d H:i:s', strtotime($post['completed_date'])));
            $update_invoice->execute(); */
            
            $invoice_obj->set('invoice_number',$post['invoice_number'])
            ->set('prefix',$post['prefix'])
            ->set('completed_date',date('Y-m-d H:i:s', strtotime($post['completed_date'])));
        }
        
        // update recipient && footer (html update issues)
        
        
        if ($status != '0') // dont change status when is paid and edited
        {
//             $update_invoice_status = Doctrine_Query::create()->update("InvoiceSystem")
//                 ->set('status', $status)
//                 ->where('id = ?', $invoice)
//                 ->andWhere('invoice_type = ?', $invoice_type)
//                 ->andWhere('status != "3"')
//                 ->andWhere('status != "5"');
            
//             $update_invoice_status->execute();
            
            if($invoice_obj->status != "3" && $invoice_obj->status != "5"  ){
                $invoice_obj->set("status",$status);
            }
            
        }
        
        $invoice_obj
        ->set('address',  $post['invoice']['address'])
        ->set('footer',  $post['footer'])
        ->save();
        
       /*  $update_invoice = Doctrine::getTable('InvoiceSystem')->findOneById($invoice);
        $update_invoice->address = $post['invoice']['address'];
        $update_invoice->footer = $post['footer'];
        $update_invoice->save(); */
       
       
        //mark as deleted all items
        self::delete_all_items($invoice_type,$invoice);

        //insert all form items
        $new_invoice_total = '';
        foreach($post['custom'] as $k_item => $v_custom)
        {
            $new_invoice_items[] = array(
                'invoice' => $invoice,
                'invoice_type' => $invoice_type,
                'client' => $clientid,
                'shortcut' => $post['shortcut'][$k_item],
                'description' => $post['description'][$k_item],
                'location_type' => $post['location_type'][$k_item],
                'qty' => $post['qty'][$k_item],
                'price' => Pms_CommonData::str2num($post['price'][$k_item]),
                'total' => Pms_CommonData::str2num($post['total'][$k_item]),
                'custom' => $v_custom,
                'isdelete' => '0',
            );
        }
        
        
        //update invoice total
        if($new_invoice_items)
        {
            $collection = new Doctrine_Collection('InvoiceSystemItems');
            $collection->fromArray($new_invoice_items);
            $collection->save();
        
            $new_invoice_total = $post['invoice_total'];
        
            //update invoice total
           /*  $update_invoice = Doctrine_Query::create()
            ->update("InvoiceSystem")
            ->set('invoice_total', "?", $new_invoice_total)
            ->where('id = ?',  $invoice )
            ->andWhere('invoice_type = ?', $invoice_type)
            ->execute(); */
            
            
            $invoice_obj
            ->set('invoice_total', $new_invoice_total)
            ->save();
        }
        
        
     /*    if ($update_invoice) {
            return true;
        } else {
            return false;
        } */
      
    }

    public function delete_invoice($invoice_type = false, $invoice)
    {
        if ( ! $invoice_type){
            return false;
        }
        
        if ( empty($invoice)){
            return false;
        } 
        
        $update_invoice = Doctrine_Query::create()
            ->update("InvoiceSystem")
            ->set('status', '4')
            ->set('isdelete', '1')
            ->where('id = ?',$invoice)
            ->andWhere('invoice_type = ?',$invoice_type);
        $update_invoice->execute();
        
        if ($update_invoice) {
            return true;
        } else {
            return false;
        }
    }

    public function delete_multiple_invoices($invoice_type = false, $iids)
    {
        
        if ( ! $invoice_type){
            return false;
        }
        if ( empty($iids)){
            return false;
        }
        
        if (count($iids) > 0) {
            $delInvoices = Doctrine_Query::create()
                ->update("InvoiceSystem")
                ->set('isdelete', "1")
                ->set('status', "4")
                ->whereIn('id', $iids)
                ->andWhere('invoice_type = ? ', $invoice_type)
                ->andWhere('isdelete = 0');
            $d = $delInvoices->execute();
        }
    }

    public function ToggleStatusInvoices($invoice_type = false, $iids, $status, $clientid = false)
    {
        if ( ! $invoice_type){
            return false;
        }
        // setStatus of multiple client invoices **
        if (count($iids) > 0) {
            /* ---------------------- Status Client Invoice START ------------------- */
            $statusInvoices = Doctrine_Query::create()
            ->update("InvoiceSystem")
            ->set('status', $status);
            
            if ($status == "3") // paid
            {
                $statusInvoices->set('paid_date', "NOW()");
            }
            // reset paid date if invoice is running out of payments (user deletes them)
            if ($status == "1" || $status == "2") // draft or unpaid
            {
                $statusInvoices->set('paid_date', "NULL");
                if ($status == '2') {
                    $statusInvoices->set('completed_date', "NOW()");
                }
            }
            $statusInvoices->whereIn('id', $iids)
            ->andWhere('invoice_type = ? ', $invoice_type)
            ->andWhere('isdelete =0')
            ->execute();
            
            // generate new rechnung number for completed invoices
            if ($status == '2' && $clientid) {
                $invoice = new InvoiceSystem();
                
                foreach ($iids as $k_inv_nr => $v_inv_nr) {
                    $invoice_number = $invoice->get_next_invoice_number($invoice_type,$clientid);
                    $prefix = $invoice_number['prefix'];
                    $invoicenumber = $invoice_number['invoicenumber'];
                    
                    $update_inv = Doctrine_Core::getTable('InvoiceSystem')->findOneByIdAndStatusAndIsdeleteAndPrefix($v_inv_nr, '2', '0', 'TEMP_');
                    // avoid errors
                    if ($update_inv) {
                        $update_inv->prefix = $prefix;
                        $update_inv->invoice_number = $invoicenumber;
                        $update_inv->save();
                    }
                }
            }
        }
    }

    public function submit_payment($invoice_type = false, $post)
    {
        if( ! $invoice_type){
            return false;
        }
        /* ------------------- Get Client Invoice Payments START -------------- */
        $invoices = Doctrine_Query::create()
            ->select("*, SUM(amount) as PaidAmount")
            ->from('InvoiceSystemPayments')
            ->Where("invoice= ?", $post['invoiceId'])
            ->andWhere('isdelete = 0')
            ->andWhere('invoice_type = ?',$invoice_type);
        $itemInvArray = $invoices->fetchArray();
        /* ------------------- Update Client Invoice START -------------- */
        $lastPay = end($itemInvArray);
        
        
        /* 
        $updateCI = Doctrine::getTable('InvoiceSystem')->findOneById($post['invoiceId']);
        $curentInvoiceArr = $updateCI->toArray(); */

        $current_invoice_obj = Doctrine_Query::create()
        ->select("*")
        ->from('InvoiceSystem')
        ->Where("id = ?", $post['invoiceId'])
        ->andWhere('invoice_type = ?',$invoice_type)
        ->andWhere('isdelete = 0')
        ->fetchOne();
      
        if( empty($current_invoice_obj)){
            return false;
        }
        
        if ($post['paymentAmount'] == "0.00" && $current_invoice_obj['invoice_total'] != '0.00' && $post['mark_as_paid'] == "1") {
            if ($lastPay['PaidAmount']) {
                $paid_ammount = $lastPay['PaidAmount'];
            } else {
                $paid_ammount = "0.00";
            }
            
            // add full payment in case of mark as paid for non 0.00 invoice
            $post['paymentAmount'] = ($current_invoice_obj['invoice_total'] - $paid_ammount);
        }
        // bccomp returns 0 if the two operands are equal, 1 if the left_operand is larger than the right_operand, -1 otherwise.
        if (empty($itemInvArray[0]['id'])) {
            if (bccomp($current_invoice_obj['invoice_total'], Pms_CommonData::str2num($post['paymentAmount'])) == 0) {
                $status = "3"; // completed
            } else 
                if (bccomp($current_invoice_obj['invoice_total'], Pms_CommonData::str2num($post['paymentAmount'])) == 1) {
                    $status = "5"; // partial
                }
        } else {
            $paid_value = Pms_CommonData::str2num($lastPay['PaidAmount'] + Pms_CommonData::str2num($post['paymentAmount']));
            $total_value = $current_invoice_obj['invoice_total'];
            
            if (bccomp($paid_value, $total_value) == 0) {
                $status = "3"; // completed
            } else {
                $status = "5"; // partial
            }
        }
        
        
        $current_invoice_obj->set("status", $status)->save();
        
        /* ------------------- Add Invoice Payment START -------------- */
        $invPayment = new InvoiceSystemPayments();
        $invPayment->invoice = $post['invoiceId'];
        $invPayment->invoice_type = $invoice_type;
        $invPayment->amount = Pms_CommonData::str2num($post['paymentAmount']);
        $invPayment->comment = $post['paymentComment'];
        $invPayment->paid_date = date("Y-m-d H:i:s", strtotime($post['paymentDate']));
        $invPayment->isdelete = "0";
        $invPayment->save();
    }

    public function archive_multiple_invoices($invoice_type = false,$iids, $clientid)
    {
        if( ! $invoice_type){
            return false;
        }
        if (empty($iids)) {
            return false;
        }
        
        $iids = array_values(array_unique($iids));
        
        if (count($iids) > 0) {
            $archive_invoices = Doctrine_Query::create()->update("InvoiceSystem")
                ->set('isarchived', "1")
                ->whereIn('id', $iids)
                ->andWhere('client = ?', $clientid)
                ->andWhere('invoice_type = ?', $invoice_type)
                ->andWhere('isdelete ="0"');
            $archive = $archive_invoices->execute();
        }
    }

    private function delete_all_items($invoice_type = false, $invoice)
    {
        if (! $invoice_type) {
            return false;
        }
        if (empty($invoice)) {
            return false;
        }
        
        $del_inv_items = Doctrine_Query::create()
            ->delete("InvoiceSystemItems")
            ->where('invoice = ?', $invoice)
            ->andWhere('invoice_type = ?', $invoice_type)
            ->execute();
    }
    
    /*
     * ISPC-2747 Lore 23.11.2020
     */
    public function validate_custom_invoice($post)
    {
        $error = 0;
        $Tr = new Zend_View_Helper_Translate();
        $validator = new Pms_Validation();
        
        if(!$validator->isstring($post['prefix']))
        {
            $this->error_message['prefix'] = $Tr->translate('bay_custom_invoice_prefix_required');
            $error = 1;
        }
        
        if(!$validator->isstring($post['invoice_number']) && $error != "1")
        {
            $this->error_message['invoice_number'] = $Tr->translate('bay_custom_invoice_invoice_number_required');
            $error = 2;
        }
        
        if(!$validator->isstring($post['start_active']) && !$validator->isdate($post['start_active']) && $error != "1" && $error != "2")
        {
            $this->error_message['start_active'] = $Tr->translate('bay_custom_invoice_start_active_required');
            $error = 3;
        }
        
        if(!$validator->isstring($post['end_active']) && !$validator->isdate($post['end_active']) && $error != "1" && $error != "2" && $error != '3')
        {
            $this->error_message['end_active'] = $Tr->translate('bay_custom_invoice_end_active_required');
            $error = 4;
        }
        
        if($error == 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /*
     * ISPC-2747 Lore 23.11.2020
     */
    public function insert_custom_invoice($post)
    {
        
        $format = 'Y-m-d H:i:s';
        if(!empty($post['start_active']))
        {
            $invoice_start = date($format, strtotime($post['start_active']));
        }
        else
        {
            $invoice_start = "0000-00-00 00:00:00";
        }
        
        if(!empty($post['end_active']))
        {
            $invoice_end = date($format, strtotime($post['end_active']));
        }
        else
        {
            $invoice_end = "0000-00-00 00:00:00";
        }
        
        
        if(!empty($post['start_active']))
        {
            $start_active = date($format, strtotime($post['start_active']));
        }
        else
        {
            $start_active = '0000-00-00 00:00:00';
        }
        
        if(!empty($post['end_active']))
        {
            $end_active = date($format, strtotime($post['end_active']));
        }
        else
        {
            $end_active = '0000-00-00 00:00:00';
        }
        
        if(!empty($post['start_sapv']))
        {
            $start_sapv = date($format, strtotime($post['start_sapv']));
        }
        else
        {
            $start_sapv = '0000-00-00 00:00:00';
        }
        
        if(!empty($post['birthd']))
        {
            $birthdate = date('Y-m-d', strtotime($post['birthd']));
        }
        else
        {
            $birthdate = "0000-00-00";
        }
        
        if(!empty($post['end_sapv']))
        {
            $end_sapv = date($format, strtotime($post['end_sapv']));
        }
        else
        {
            $end_sapv = '0000-00-00 00:00:00';
        }
        
        if(!empty($post['sapv_approve_date']))
        {
            $sapv_approve_date = date($format, strtotime($post['sapv_approve_date']));
        }
        else
        {
            $sapv_approve_date = '0000-00-00 00:00:00';
        }
        
        //ISPC-2747 pct.b Lore 27.11.2020
        $show_boxes = '';
        if(isset($post['show_box_active'])){
            $show_boxes .= 'show_box_active,';
        }
        if(isset($post['show_box_patient'])){
            $show_boxes .= 'show_box_patient,';
        }
        if(isset($post['show_box_sapv'])){
            $show_boxes .= 'show_box_sapv,';
        }
        
        $ins_inv = new InvoiceSystem();
        
        $ins_inv->invoice_type = $post['invoice_type'];
        
        $ins_inv->invoice_start = $invoice_start;
        $ins_inv->invoice_end = $invoice_end;
        $ins_inv->start_active = $start_active; //first product day in period
        $ins_inv->end_active = $end_active; //last product day in period
        $ins_inv->start_sapv = $start_sapv; //allready formated as db format
        $ins_inv->end_sapv = $end_sapv; //allready formated as db format
        $ins_inv->sapv_approve_date = $sapv_approve_date;
        $ins_inv->sapv_approve_nr = $post['sapv_approve_nr'];
        $ins_inv->ipid = $post['ipid'];
        
        $ins_inv->client = $post['clientid'];
        $ins_inv->userid = $post['user'];
        
        $ins_inv->client_name = $post['client_ik'];
        $ins_inv->prefix = $post['prefix'];
        $ins_inv->invoice_number = $post['invoice_number'];
        $ins_inv->invoice_total = $post['invoice_total'];
        $ins_inv->address = $post['address'];
        $ins_inv->footer = $post['footer'];
        
        $ins_inv->first_name = $post['first_name'];
        $ins_inv->last_name = $post['last_name'];
        $ins_inv->birthdate = $birthdate;
        $ins_inv->street = $post['street'];
        $ins_inv->patient_care = $post['patient_pflegestufe'];
        $ins_inv->insurance_no = $post['insurance_no'];
        $ins_inv->debtor_number = $post['debtor_number'];
        $ins_inv->ppun = $post['ppun'];
        $ins_inv->paycenter = $post['paycenter'];
        
        $ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020
        $ins_inv->custom_invoice = 'custom_invoice';      //ISPC-2747 pct.b Lore 27.11.2020
        
        $ins_inv->status = '1'; // DRAFT - ENTWURF
        $ins_inv->isdelete = '0';
        $ins_inv->record_id = '0';
        $ins_inv->storno = '0';
        $ins_inv->save();
        $ins_id = $ins_inv->id;
        
        if($ins_id)
        {
            foreach($post['row'] as $k_inv => $v_inv)
            {
                $invoice_items_arr[] = array(
                    'invoice' => $ins_id,
                    'invoice_type' => $post['invoice_type'],
                    'client' => $post['clientid'],
                    'name' => $post['name'][$k_inv],
                    'shortcut' => $post['shortcut'][$k_inv],
                    'description' => $post['name'][$k_inv],
                    'qty' => $post['qty'][$k_inv],
                    'price' => $post['price'][$k_inv],
                    'total' => $post['total'][$k_inv],
                    'custom' => '1',
                    'isdelete' => '0'
                );
            }
            
            if(count($invoice_items_arr) > 0)
            {
                //insert many records with one query!!
                $collection = new Doctrine_Collection('InvoiceSystemItems');
                $collection->fromArray($invoice_items_arr);
                $collection->save();
            }
        }
        
        return $ins_id;
    }
    
    public function update_custom_invoice($invoice_id = false, $post, $status = false)
    {
        if($invoice_id)
        {
            $inserted_id = '';
            $invoice_total = '';
            $invoice_items = array();
            $format = 'Y-m-d H:i:s';
            
            
            if(!empty($post['completed_date']))
            {
                $completed_date = date($format, strtotime($post['completed_date']));
            }
            else
            {
                $completed_date = "0000-00-00 00:00:00";
            }
            
            if(!empty($post['start_active']))
            {
                $invoice_start = date($format, strtotime($post['start_active']));
            }
            else
            {
                $invoice_start = "0000-00-00 00:00:00";
            }
            
            if(!empty($post['end_active']))
            {
                $invoice_end = date($format, strtotime($post['end_active']));
            }
            else
            {
                $invoice_end = "0000-00-00 00:00:00";
            }
            
            
            
            if(!empty($post['start_active']))
            {
                $start_active = date($format, strtotime($post['start_active']));
            }
            else
            {
                $start_active = '0000-00-00 00:00:00';
            }
            
            if(!empty($post['end_active']))
            {
                $end_active = date($format, strtotime($post['end_active']));
            }
            else
            {
                $end_active = '0000-00-00 00:00:00';
            }
            
            if(!empty($post['start_sapv']))
            {
                $start_sapv = date($format, strtotime($post['start_sapv']));
            }
            else
            {
                $start_sapv = '0000-00-00 00:00:00';
            }
            
            if(!empty($post['birthd']))
            {
                $birthdate = date('Y-m-d', strtotime($post['birthd']));
            }
            else
            {
                $birthdate = "0000-00-00";
            }
            
            if(!empty($post['end_sapv']))
            {
                $end_sapv = date($format, strtotime($post['end_sapv']));
            }
            else
            {
                $end_sapv = '0000-00-00 00:00:00';
            }
            
            if(!empty($post['sapv_approve_date']))
            {
                $sapv_approve_date = date($format, strtotime($post['sapv_approve_date']));
            }
            else
            {
                $sapv_approve_date = '0000-00-00 00:00:00';
            }
            
            //ISPC-2747 pct.b Lore 27.11.2020
            $show_boxes = '';
            if(isset($post['show_box_active'])){
                $show_boxes .= 'show_box_active,';
            }
            if(isset($post['show_box_patient'])){
                $show_boxes .= 'show_box_patient,';
            }
            if(isset($post['show_box_sapv'])){
                $show_boxes .= 'show_box_sapv,';
            }
            
            $ins_inv = Doctrine::getTable('InvoiceSystem')->findOneById($invoice_id);
            $ins_inv_data = $ins_inv->toArray();
            
            if($status)
            {
                //dont delete invoices paid and partialy paid
                if($status == '4' && $ins_inv_data['status'] != '3' && $ins_inv_data['status'] != '5')
                {
                    $ins_inv->isdelete = "1";
                }
                
                if($status != '0' && $status != '1' && $status != '4')
                {
                    $ins_inv->completed_date = $completed_date;
                }
                
                if($status != '0' && $ins_inv_data['status'] != '3' && $ins_inv_data['status'] != '5') //dont change status when is paid and edited
                {
                    $ins_inv->status = $status;
                }
            }
            
           
            $ins_inv->invoice_start = $invoice_start;
            $ins_inv->invoice_end = $invoice_end;
            $ins_inv->start_active = $start_active; //first product day in period
            $ins_inv->end_active = $end_active; //last product day in period
            $ins_inv->start_sapv = $start_sapv; //allready formated as db format
            $ins_inv->end_sapv = $end_sapv; //allready formated as db format
            $ins_inv->sapv_approve_date = $sapv_approve_date;
            $ins_inv->sapv_approve_nr = $post['sapv_approve_nr'];
            $ins_inv->ipid = $post['ipid'];
            
            $ins_inv->client = $post['clientid'];
            $ins_inv->userid = $post['user'];
            
            $ins_inv->client_name = $post['client_ik'];
            $ins_inv->prefix = $post['prefix'];
            $ins_inv->invoice_number = $post['invoice_number'];
            $ins_inv->invoice_total = $post['invoice_total'];
            $ins_inv->address = $post['address'];
            $ins_inv->footer = $post['footer'];
            
            $ins_inv->show_boxes = $show_boxes;               //ISPC-2747 pct.b Lore 27.11.2020
            
            $ins_inv->first_name = $post['first_name'];
            $ins_inv->last_name = $post['last_name'];
            $ins_inv->birthdate = $birthdate;
            $ins_inv->street = $post['street'];
            $ins_inv->patient_care = $post['patient_pflegestufe'];
            $ins_inv->insurance_no = $post['insurance_no'];
            $ins_inv->debtor_number = $post['debtor_number'];
            $ins_inv->ppun = $post['ppun'];
            $ins_inv->paycenter = $post['paycenter'];
            
            $ins_inv->save();
            $ins_id = $ins_inv->id;
            
            if($ins_id)
            {
                foreach($post['row'] as $k_inv => $v_inv)
                {
                    $invoice_items_arr[] = array(
                        'invoice' => $ins_id,
                        'invoice_type' => $ins_inv_data['invoice_type'],
                        'client' => $post['clientid'],
                        'name' => $post['name'][$k_inv],
                        'shortcut' => $post['shortcut'][$k_inv],
                        'description' => $post['name'][$k_inv],
                        'qty' => $post['qty'][$k_inv],
                        'price' => $post['price'][$k_inv],
                        'total' => $post['total'][$k_inv],
                        'custom' => '1',
                        'isdelete' => '0'
                    );
                }
                
                if(count($invoice_items_arr) > 0)
                {
                    self::delete_items($ins_id);
                    
                    //insert many records with one query!!
                    $collection = new Doctrine_Collection('InvoiceSystemItems');
                    $collection->fromArray($invoice_items_arr);
                    $collection->save();
                }
            }
            
            return $ins_id;
        }
        else
        {
            return false;
        }
    }
    
    private function delete_items($invoice)
    {
        if($invoice)
        {
            $q = Doctrine_Query::create()
            ->update('InvoiceSystemItems')
            ->set('isdelete', "1")
            ->where('invoice = "' . $invoice . '"')
            ->andWhere('isdelete = "0"');
            $q_res = $q->execute();
        }
    }
    
 }

?>