<?php

	Doctrine_Manager::getInstance()->bindComponent('FormTypeActions', 'SYSDAT');

	class FormTypeActions extends BaseFormTypeActions {

		public function get_form_type_actions($is_dropdown = 0)
		{
			
			$Tr = new Zend_View_Helper_Translate();
				
			$types = Doctrine_Query::create()
				->select('*')
				->from('FormTypeActions');
			$types_res = $types->fetchArray();

			if($types_res)
			{
				
				if($is_dropdown == 1)
				{
					$types_array = array("0" => $Tr->translate('other_form_type'));
				
					foreach($types_res as $type)
					{
						$types_array[$type['id']] = $type['name'];
					}
				}
				else
				{
					foreach($types_res as $type)
					{
						$types_array[$type['id']] = $type;
					}
				}
			
				return $types_array;
			}
			else
			{
				return false;
			}
		}
	}

?>