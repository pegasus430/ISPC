<?php
/**
 * SoftDecryptListener 
 * rc0.1
 * @claudiu xx.08.2017 - rc to v1.0 -> is in production
 * 
 * @claudiu 27.03.2017
 * + $dql_select_ipos = ($dql_select_ipos == "*") ?  $params['alias'] . ".". $dql_select_ipos : $dql_select_ipos;
 *
 *
 * @package    ISPC
 * @subpackage Application (2017-08-09)
 * @author     claudiu <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class SoftDecryptListener extends Doctrine_Record_Listener 
{

	/**
	 * Array of Column names that should be encrypted
	 *
	 * @var string
	 */
	protected $_options = array();
	
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

	
	public function preDqlSelect(Doctrine_Event $event)
	{	
		
// 		die("ssssss");
		
		$salt = Zend_Registry::get('salt');
		
		$hidemagic = Zend_Registry::get('hidemagic');
		
		$last_ipid_session = new Zend_Session_Namespace('last_ipid');
				
		$params = $event->getParams();
				
		$ComponentName = $event->getInvoker()->getTable()->getComponentName();
		
		$query = $event->getQuery();
		
// 		$test = $query->getDql();
		
		$dql_select = $query->getDqlPart('select');

		if ( empty($dql_select)) {

			$rootAlias = $query->getRootAlias();
			
			$query->addSelect($rootAlias . '.*');
			
		}		

		$allfield = $params['alias'] . '.*'; 
		
		if ( ! $query->isSubquery() || ($query->isSubquery() && $query->contains(' ' . $params['alias'] . ' ')) ) 
		{
			$dql_select = $query->getDqlPart('select');

			foreach($this->_options as $column)
			{
				$field = $params['alias'] . '.' . $column;

				$field_encrypted = $ComponentName . '.' . $column ;//. '.decrypt';			
				$field_column = $params['alias'] . '.' . $column ;//. '.decrypt';	

				$dql_select_ipos = is_array($dql_select) ? implode(', ', $dql_select) : $dql_select;
				$dql_select_ipos = ($dql_select_ipos == "*") ?  $params['alias'] . ".". $dql_select_ipos : $dql_select_ipos;
								
				if (stripos($dql_select_ipos, $allfield) !== false 
				    || stripos($dql_select_ipos, $field_column) !==false  
				    || stripos($dql_select_ipos, $column) !==false) 
				{    
					
				    $dqlselects = "AES_DECRYPT({$field}, '{$salt}' ) AS {$field_encrypted}";
					 										
					$query->addSelect($dqlselects);
					
				}
			}
		}
		
		return;
	
	}
	
	
	
	public function preHydrate( Doctrine_Event $event )
	{
		$data = $event->data;

		$params = $event->getParams();
		
		$ComponentName = $event->getInvoker()->getComponentName();
				
		foreach($this->_options as $column)
		{
			
			$field_decrypted = $ComponentName . '.' . $column ;//. '.decrypt';
			
			$field_original = $ComponentName . '.' . $column . '.original' ;

			if( ! empty($data[$column]) && ! empty($data[$field_decrypted]) )
			{
				$data[$field_original] = $data[$column];
				
				$data[$column] = $data[$field_decrypted];				
			}
		}

		$event->data = $data;
	}
	

}

?>