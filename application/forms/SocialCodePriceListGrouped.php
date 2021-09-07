<?php

require_once("Pms/Form.php");

class Application_Form_SocialCodePriceListGrouped extends Pms_Form {

	public function insert_pricelist_grouped($list = false, $group = false)
	{
		if($group && $list)
		{
			$pl_grouped = new SocialCodePriceListGrouped();
			$pl_grouped->price_list = $list;
			$pl_grouped->groupid = $group;
			$pl_grouped->save();
		}
	}

	public function update_pricelist_grouped($list = false, $group = false)
	{
		if($group && $list > '0' && $list)
		{
			$update = Doctrine_Core::getTable('SocialCodePriceListGrouped')->findOneByPriceListAndIsdelete($list, '0');
			if($update)
			{ //update if found
				$update->groupid = $group;
				$update->save();
			}
			else
			{ //insert as new if not found
				$pl_grouped = new SocialCodePriceListGrouped();
				$pl_grouped->price_list = $list;
				$pl_grouped->groupid = $group;
				$pl_grouped->save();
			}
		}
	}

}

?>