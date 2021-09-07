<?php

	Doctrine_Manager::getInstance()->bindComponent('InvoiceSettings', 'SYSDAT');

	class InvoiceSettings extends BaseInvoiceSettings {

		public function getClientInvoiceSettings($clientid, $form_type = false, $invoice_number_type = '0')
		{
			//21.06.2013 check if individual or collective price settings
			$client = new Client();
			$current_client_data = $client->getClientDataByid($clientid);

			if($current_client_data[0]['invoice_number_type'] == '1') //collective settings
			{
				if($form_type)
				{
					foreach($form_type as $k_form_type => $v_form_type)
					{
						$sel_array[$v_form_type]['invoice_prefix'] = $current_client_data[0]['invoice_number_prefix'];
						$sel_array[$v_form_type]['invoice_start'] = $current_client_data[0]['invoice_number_start'];
					}

					return $sel_array;
				}
			}
			else //individual settings
			{
				$select = Doctrine_Query::create()
					->select('*')
					->from('InvoiceSettings')
					->where('client = "' . $clientid . '"');
				if($form_type)
				{
					if(is_array($form_type))
					{
						//doctrine error if keys are not numeric "Invalid parameter number: parameter was not defined"
						$form_type = array_values($form_type);
						$select->andWhereIn('invoice_type', $form_type);
					}
					else
					{
						$select->andWhere('invoice_type = "' . $form_type . '"');
					}
				}

				$sel_res = $select->fetchArray();

				if($sel_res)
				{
					foreach($sel_res as $k_res => $v_res)
					{
						$sel_array[$v_res['invoice_type']] = $v_res;

						if(strlen($v_res['invoice_start']) == '0')
						{
							$sel_array[$v_res['invoice_type']]['invoice_prefix'] = '';
							$sel_array[$v_res['invoice_type']]['invoice_start'] = '';
						}
					}

					return $sel_array;
				}
				else
				{
					return false;
				}
			}
		}

		public function get_all_invoices_high_number($clientid,$invoice_type = false )
		{
			$client_invoices = new ClientInvoices();
			$hi_invoices = new HiInvoices();
			$bw_invoices = new BwInvoices();
			$bw_invoices_new = new BwInvoicesNew();
			$bre_invoices = new BreInvoices();
			$sgbv_invoices = new SgbvInvoices();
			$mp_invoices = new MedipumpsInvoices();
			$he_invoices = new HeInvoices();
			$sgbxi_invoices = new SgbxiInvoices();
			$bayern_invoices = new BayernInvoices();
			$rp_invoices = new RpInvoices();
			$rlp_invoices = new RlpInvoices();
			$bre_hospiz_invoices = new BreHospizInvoices();
			$sh_invoices = new ShInvoices();
			$new_bayern_invoices = new BayernInvoicesNew();
			$invoices_arr = Pms_CommonData::get_invoice_types();
			$sh_internal_invoices = new ShInternalInvoices();
			$sh_shifts_internal_invoices = new ShShiftsInternalInvoices();
			$members_invoices = new MembersInvoices();
			$hospiz_invoices = new HospizInvoices();
			// one table to rulle them all
			$invoices_system = new InvoiceSystem();
			
			//add for ISPC-2532 by Carmen 14.02.2020
			$mp_invoices_new = new MedipumpsInvoicesNew();
			$bra_invoices= new BraInvoices();
			//--
			
			$all_invoice_settings = $this->getClientInvoiceSettings($clientid, $invoices_arr);

			//get each invoice type current high number
			$high_invoice_number = array();
			$high_invoice_number['client'] = $client_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['by_invoice']['invoice_prefix']);
			$high_invoice_number['hi'] = $hi_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['nie_patient_invoice']['invoice_prefix']);
			$high_invoice_number['bw'] = $bw_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['bw_sapv_invoice']['invoice_prefix']);
			$high_invoice_number['bw_new'] = $bw_invoices_new->get_highest_invoice_number($clientid, $all_invoice_settings['bw_sapv_invoice_new']['invoice_prefix']);
			$high_invoice_number['bre'] = $bre_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['bre_sapv_invoice']['invoice_prefix']);
			$high_invoice_number['sgbv'] = $sgbv_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['bw_sgbv_invoice']['invoice_prefix']);
			$high_invoice_number['mp'] = $mp_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['bw_mp_invoice']['invoice_prefix']);
			$high_invoice_number['he'] = $he_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['he_invoice']['invoice_prefix']);
			$high_invoice_number['sgbxi'] = $sgbxi_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['bw_sgbxi_invoice']['invoice_prefix']);
			$high_invoice_number['bayern'] = $bayern_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['bayern_invoice']['invoice_prefix']);
			// //TODO-3680 Ancuta 11.12.2020  - Changed from rp_invoice to rpinvoice - as this was changed by Carmen 
			//$high_invoice_number['rp'] = $rp_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['rp_invoice']['invoice_prefix']);
			$high_invoice_number['rp'] = $rp_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['rpinvoice']['invoice_prefix']);
			//--
			$high_invoice_number['rlp'] = $rlp_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['rlp_invoice']['invoice_prefix']);
			$high_invoice_number['sh'] = $sh_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['sh_invoice']['invoice_prefix']);
			$high_invoice_number['bre_hospiz'] = $bre_hospiz_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['bre_hospiz_sapv_invoice']['invoice_prefix']);
			$high_invoice_number['new_bayern_invoice'] = $new_bayern_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['new_bayern_invoice']['invoice_prefix']);
			$high_invoice_number['sh_internal'] = $sh_internal_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['sh_internal_invoice']['invoice_prefix']);
			$high_invoice_number['sh_shifts_internal'] = $sh_shifts_internal_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['sh_shifts_internal_invoice']['invoice_prefix']);
			$high_invoice_number['members'] = $members_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['members_invoice']['invoice_prefix']);
			$high_invoice_number['hospiz_invoice'] = $hospiz_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['hospiz_invoice']['invoice_prefix']);
			
			//ISPC-2214
			if(!empty($invoice_type)){
    			$high_invoice_number[$invoice_type] = $invoices_system->get_highest_invoice_number($invoice_type,$clientid, $all_invoice_settings[$invoice_type]['invoice_prefix']);
			}
			
			//add for ISPC-2532 by Carmen 14.02.2020
			$high_invoice_number['mp_new'] = $mp_invoices_new->get_highest_invoice_number($clientid, $all_invoice_settings['bw_medipumps_invoice']['invoice_prefix']);
			$high_invoice_number['bra'] = $bra_invoices->get_highest_invoice_number($clientid, $all_invoice_settings['bra_invoice']['invoice_prefix']);
			//--
			
			foreach($high_invoice_number as $k_invoice => $v_invoice)
			{
				if(!empty($v_invoice))
				{
					if($k_invoice == 'client')
					{
						$master_high_numbers['number'][] = $v_invoice['rnummer'];
						$master_high_numbers['prefix'][] = $v_invoice['prefix'];
					}
					else
					{
						$master_high_numbers['number'][] = $v_invoice['invoice_number'];
						$master_high_numbers['prefix'][] = $v_invoice['prefix'];
					}
				}
			}

			return $master_high_numbers;
		}

	}

?>