<?php
/**
 * 
 * @author claudiu
 *
 */

class ReceiptItems extends BaseReceiptItems 
{

	public function get_items_by_receipt_id( $receipt_id , $isdelete = 0 )
	{
		$receipts_items = Doctrine_Query::create()
			->select("id, source, dbf_id, medication, custom_line, pzn")
			->from("ReceiptItems")
			->where(" receipt_id = ? ", $receipt_id)
			->andWhere(" isdelete = ? ", (int)$isdelete)
			->fetchArray();
		
			return $receipts_items;
			
	}
	
	
	//TODO-3766 Lore 20.01.2021
	public function get_items_by_receipt_id_all( $receipt_id)
	{
	    $receipts_items = Doctrine_Query::create()
	    ->select("*")
	    ->from("ReceiptItems")
	    ->where(" receipt_id = ? ", $receipt_id)
	    ->fetchArray();
	    
	    return $receipts_items;
	    
	}

}

?>