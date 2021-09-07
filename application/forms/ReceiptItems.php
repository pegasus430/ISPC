<?php

require_once("Pms/Form.php");

class Application_Form_ReceiptItems extends Pms_Form {

	public function insert_collection_receipt_item ( $collection )
	{	
		$dc_ri = new Doctrine_Collection('ReceiptItems');
		$dc_ri->fromArray($collection);
		$dc_ri->save();
	}
	

	public function delete_receipt_items ( $receipt_id )
	{

		$del = Doctrine::getTable('ReceiptItems')->findByReceiptIdAndIsdelete( $receipt_id , "0");
		
		if ($del instanceof Doctrine_Collection) {
			$del->delete();
		}
	}
			
}

?>