<?php
Doctrine_Manager::getInstance()->bindComponent('ReceiptItems', 'SYSDAT');
/**
 * 
 * @author claudiu
 *
 */
abstract class BaseReceiptItems extends Doctrine_Record {

	function setTableDefinition()
	{

		$this->setTableName('receipt_items');
		
        $this->hasColumn('id', 'integer', 11, array(
             'type' => 'integer',
             'length' => 11,
             'fixed' => false,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             ));
        
        $this->hasColumn('receipt_id', 'integer', 11, array(
        		'type' => 'integer',
        		'length' => 11,
        		'fixed' => false,
        		'unsigned' => true,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        		'comment' => "id from table receipts" 
        ));

        $this->hasColumn('source', 'enum', null, array(
					'type' => 'enum',
					'notnull' => true,
					'default' => 'custom',
					'values' => array(
							'personal',
							'mmi_receipt_dropdown',
							'mmi_notreceipt_dropdown',
							'mmi_dialog_product',
							'mmi_dialog_price',
							'custom'
			
					), // in the future there could be also vitabook
					'comment'=>'personal=from the personal list, mmi_= taken from mmi, custom=written or changed by hand'
		));
        
        $this->hasColumn('dbf_id', 'integer', 11, array(
        		'type' => 'integer',
        		'length' => 11,
        		'fixed' => false,
        		'unsigned' => true,
        		'primary' => false,
        		'notnull' => true,
        		'autoincrement' => false,
        		'comment' => "id of this medication, in mmi or personaldrugs table" 
        ));

        $this->hasColumn('medication', 'varchar', 255, array(
        		'type' => 'varchar',
        		'length' => 255,
        		'fixed' => false,
        		'notnull' => true,
        ));
        
        $this->hasColumn('custom_line', 'varchar', 255, array(
        		'type' => 'varchar',
        		'length' => 255,
        		'fixed' => false,
        		'notnull' => true,
        ));
        
        $this->hasColumn('pzn', 'integer', 8, array(
        		'type' => 'integer',
//         		'columnDefinition'=>"INT(8) UNSIGNED ZEROFILL",
        		'zerofill' => true,
        		'length' => 8,
        		'fixed' => true,
        		'notnull' => true,
        ));
        
        

		$this->index('id', array(
				'fields' => array('id'),
				'primary' => true
		));
		
		$this->index('receipts_id+isdelete', array(
				'fields' => array('receipts_id' , 'isdelete')
		));
		
		
	}

	
    public function setUp()
    {
        parent::setUp();
        
        $this->actAs(new Timestamp());
        
        
        $this->actAs(new Softdelete());
        
        $this->hasOne('Receipts', array(
        		'local' => 'receipt_id',
        		'foreign' => 'id'
        ));
        
    }
}

?>