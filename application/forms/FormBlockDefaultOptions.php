<?php
require_once("Pms/Form.php");
class Application_Form_FormBlockDefaultOptions extends Pms_Form
{
	public function save_default_options($block){
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		//TODO-3561 Ancuta 02.12.2020 - first check again if client already has info added for this block and we do not allow options to be added if they already exist 
		$blocks_settings = new FormBlocksSettings();
		$client_block_data = $blocks_settings->get_block($clientid, $block, true);
		
		if (! empty($client_block_data)) { 
		    return;
		}
		//--
		
		$blocks_defaults = new FormBlockDefaultOptions();
		$blocks_defaults_array = $blocks_defaults->get_block_defaut_options($block);
		foreach($blocks_defaults_array as $k=>$def){
			$records_todo[] = array(
					"clientid"	 => $clientid,
					"block"	 => $def['block'],
					"option_name"	 => $def['option_name'],
					"shortcut"		 => strtoupper($def['shortcut']),
                    "form_item_class"=> $def['op_class'],//ISPC-2487 // Maria:: Migration ISPC to CISPC 08.08.2020	
					"available"		 => '1'
			);
		}
		if(count($records_todo) > 0)
		{
			$collection = new Doctrine_Collection('FormBlocksSettings');
			$collection->fromArray($records_todo);
			$collection->save();
		}
	}
}
?>