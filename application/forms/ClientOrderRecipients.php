<?php
/**
 * 
 * @author carmen
 * 
 * 19.12.2018 ISPC-2281
 *
 */
class Application_Form_ClientOrderRecipients extends Pms_Form
{
	
	public function __construct($options = null)
	{
		
		parent::__construct($options);
		
	}
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
	}


	public function save_client_recipients($post)
	{
		$clear_client_recipients = self::clear_client_recipients($post['clientid']);
		$save_client_recipients = self::insert_client_recipients($post);
	}
	
	private static function insert_client_recipients($post)
	{
		if(!empty($post))
		{
			$post['recids'] = array_values(array_unique($post['recids']));
	
			foreach($post['recids'] as $k_usr_id => $v_usr_id)
			{
				$insert_data[] = array(
						'clientid' => $post['clientid'],
						'userid' => $v_usr_id
				);
			}
	
			if(!empty($insert_data))
			{
				$collection = new Doctrine_Collection('ClientOrderRecipients');
				$collection->fromArray($insert_data);
				$collection->save();
			}
		}
	}
	
	private static function clear_client_recipients($clientid = false)
	{
		if(!$clientid)
		{
			return;
		}
		
		$q = Doctrine_Query::create()
		->update('ClientOrderRecipients')
		->set('isdelete', "1")
		->where('clientid = ?', $clientid)
		->andWhere('isdelete = "0"');
		$q->execute();
	}
		
}
		