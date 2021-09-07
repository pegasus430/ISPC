<?php
	Doctrine_Manager::getInstance()->bindComponent('FormBlockServiceEntry', 'SYSDAT');

	class FormBlockServiceEntry extends BaseFormBlockServiceEntry {

	public function getFormBlockServiceEntryData()
	{
		
		$set = Doctrine_Query::create()
		->select('id,item_name')
		->from('FormBlockServiceEntry se')
		->where('isdelete = 0');
		$value = $set->fetchArray();
		
		if($value)
		{
			foreach($value as $sk => $sv)
			{
				$service_array[$sv['id']] = $sv;
				$service_array[$sv['id']]['item_name'] = utf8_encode($sv['item_name']);
			}
			return $service_array;
		}
	}
}