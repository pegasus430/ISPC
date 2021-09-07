<?php

Doctrine_Manager::getInstance()->bindComponent('FormBlocksOrder', 'MDAT');

class FormBlocksOrder extends BaseFormBlocksOrder 
{

		public function get_blocks_order($clientid, $form_type = null)
		{
			$sel_ord = Doctrine_Query::create()
				->select('*')
				->from('FormBlocksOrder')
				->where('client="' . $clientid . '"');
			if($form_type)
			{
				$sel_ord->andWhere('form_type="' . $form_type . '"');
			}
			$sel_ord_res = $sel_ord->fetchArray();

			if($sel_ord_res)
			{
				return $sel_ord_res;
			}
			else
			{
				return false;
			}
		}

		
	public static function fetch_multiple_forms($form_type = [], $clientid = null )
	{
	    if (empty($form_type)) {
	        return; //fail-safe
	    }
	    
	    $form_type =  is_array($form_type) ? array_values($form_type) : [$form_type];
	    
	    if (empty($clientid)) {
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	    }
	    
	    
	    $qr =  Doctrine_Query::create()
	    ->from('FormBlocksOrder indexBy form_type')//ALTER TABLE `form_blocks_order` ADD UNIQUE( `client`, `form_type`); 
	    ->select('*')
	    ->where('client = ? ', $clientid)
	    ->andWhereIn('form_type', $form_type)
	    ->fetchArray();
	    
	    if ( ! empty($qr))
    	    foreach ($qr as &$row) {
    	        $row['box_order'] = array_map('trim', explode(',', $row['box_order']));
    	    }
	    
	    return $qr;
	
	}
}

?>