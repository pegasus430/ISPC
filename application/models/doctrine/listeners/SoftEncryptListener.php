<?php
/**
 * SoftEncryptListener 
 * rc0.1
 * @claudiu xx.08.2017 - rc to v1.0 -> is in production
 * 
 * @claudiu 14.11.2017 - introduced postInsert and postUpdate
 * this 2 fn are not tested, and are implemented since elVi
 * 
 * @claudiu 15.11.2017 - @todo: important!!, fn must be changed to work with serialized columns
 *
 *
 * @package    ISPC
 * @subpackage Application (2017-08-09)
 * @author     claudiu <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class SoftEncryptListener extends Doctrine_Record_Listener 
{

	/**
	 * Array of Column names that should be encrypted
	 *
	 * @var string
	 */
	protected $_options = array();
	
	protected $_originals = array();
	/**
	 * __construct
	 *
	 * @param string $options
	 * @return void
	 */
	public function __construct(array $options)
	{
		$this->_options = $options;
	}


	public function preInsert(Doctrine_Event $event)
	{
		$invoker = $event->getInvoker();
				
		$toencrypt = array();
		
		foreach($this->_options as $column) 
		{			
			if( ! empty($invoker->$column)) 
			{
				$toencrypt[$column] = $invoker->$column;				
			}
		}	
		
		if( ! empty($toencrypt))
		{
			
			$encrypted = Pms_CommonData::aesEncryptMultiple($toencrypt);

			foreach($this->_options as $column) 
			{
				if( ! empty($invoker->$column) 
						&& ! empty($encrypted[$column])) 
				{
				    
				    $this->_originals[$column] =  $invoker->$column;
					
// 				    $invoker->$column = $encrypted[$column];
					
					$invoker->set($column, $encrypted[$column], __CLASS__);
						
					
				}
			}
		}		
	}
	
	
	public function preUpdate(Doctrine_Event $event)
	{
		$invoker = $event->getInvoker();
		
		$last_modified_cols = $invoker->getModified();
				
		$toencrypt = array();
		
		foreach($this->_options as $column)
		{
			if( array_key_exists($column, $last_modified_cols) 
					&& ! empty($invoker->$column))
			{
				$toencrypt[$column] = $invoker->$column;
			}
		}
		
		if( ! empty($toencrypt))
		{
				
			$encrypted = Pms_CommonData::aesEncryptMultiple($toencrypt);
		
			foreach($this->_options as $column)
			{
				if( array_key_exists($column, $last_modified_cols) 
						&& ! empty($invoker->$column) 
						&& ! empty($encrypted[$column])) 
				{
				    $this->_originals[$column] =  $invoker->$column;
					$invoker->$column = $encrypted[$column];
				}
			}
		}
		return; 	
	}

	
	// separate encrypt from decrypt logic
	// public function preHydrate() was moved in SoftDecryptListener
	
	
	//post Insert/Update, keep in our app the un-encrypted values
	public function postInsert(Doctrine_Event $event)
	{
	    if ( ! empty ($this->_originals)) {
	        $invoker = $event->getInvoker();
	        foreach($this->_options as $column)
	        {
	            if( ! empty($invoker->$column) && ! empty($this->_originals[$column]))
	            {
// 	                $invoker->$column = $this->_originals[$column];
	                $invoker->set($column, $this->_originals[$column], __CLASS__);
	            }
	        }
	    }
	    return;
	}
	public function postUpdate(Doctrine_Event $event)
	{
	    if ( ! empty ($this->_originals)) {
	        $invoker = $event->getInvoker();
	        foreach($this->_options as $column)
	        {
	            if( ! empty($invoker->$column) && ! empty($this->_originals[$column]))
	            {
	                $invoker->$column = $this->_originals[$column];
	            }
	        }
	    }
	    return;
	}
	
	
//	@claudiu 15.11.2017 - @todo
//	public function preSerialize (Doctrine_Event $event)
//	{
//	    die_claudiu($event);
//	    
//	}
//	public function postSerialize (Doctrine_Event $event)
//	{
//	    die_claudiu($event);
//	    
//	}

}

?>