<?php
/**
 * ISPC-2312 Ancuta 08.12.2020
 */

	require_once("Pms/Form.php");

	class Application_Form_ClientInvoiceMultiplePermissions extends Pms_Form {

		public function insert_invoice_permissions($post)
		{
			if($post)
			{
				$clientid = $post['clientid'];

				foreach($post['canview'] as $k_val => $v_val)
				{
					$perms_data[] = array(
						'clientid' => $clientid,
						'invoice' => $v_val,
						'canadd' => "1",
						'canview' => "1",
						'canedit' => "1",
						'candelete' => "1",
						'isdelete' => "0",
					);
				}

				if(count($perms_data) > '0')
				{
					self::remove_permissions($clientid);

					$collection = new Doctrine_Collection('ClientInvoiceMultiplePermissions');
					$collection->fromArray($perms_data);
					$collection->save();
				}
			}
		}

		public function remove_permissions($clientid)
		{
			$q = Doctrine_Query::create()
				->update('ClientInvoiceMultiplePermissions')
				->set('isdelete', "1")
				->where('clientid = "' . $clientid . '"')
				->andWhere('isdelete = "0"');
			$q->execute();
		}

	}

?>