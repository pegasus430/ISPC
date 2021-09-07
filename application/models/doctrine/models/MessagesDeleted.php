<?php

Doctrine_Manager::getInstance()->bindComponent('MessagesDeleted', 'MDAT');

class MessagesDeleted extends BaseMessagesDeleted {

	/**
	 * insert messages_id into the db
	 * messages_id that are in this table, are considered deleted by the user, 
	 * and will no longer show in the user's mailbox
	 * 
	 * @param array $insert_data    messages_id to be deleted
	 * @return boolean	
	 */
	public function set_messages( $insert_data = array() )
	{
		if ( count($insert_data) > 0 ){
	      	$collection = new Doctrine_Collection('MessagesDeleted');
	      	$collection->fromArray( $insert_data );
	      	$collection->save();
	      	return true;
		}else{
			return false;
		}
	}


}
?>