<?php
/*
 * @claudiu
 * 
 * TODO: @date: 09.01.2018 update listener to work with cascade = array(delete); UnitOfWork->_cascadeDelete(Doctrine_Record $record, array &$deletions)
 * 
 * + @cla on 16.05.2018
 */

class SoftdeleteListener extends Doctrine_Record_Listener 
{

	/**
	 * Array of SoftDelete options
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

	/**
	 * Set the hard delete flag so that it is really deleted
	 *
	 * @param boolean $bool
	 * @return void
	 */
	public function hardDelete($bool)
	{
		$this->_options['hardDelete'] = $bool;
	}
	
	
	/**
	 * Skip the normal delete options so we can override it with our own
	 *
	 * @param Doctrine_Event $event
	 * @return void
	 */
	public function preDelete(Doctrine_Event $event)
	{
		$name = $this->_options['name'];
		$invoker = $event->getInvoker();
	
		if ($this->_options['type'] == 'timestamp') {
			$invoker->$name = date('Y-m-d H:i:s', time());
		} else if ($this->_options['type'] == 'boolean') {
			$invoker->$name = true;
		}
	
		if ( ! $this->_options['hardDelete']) {
			$event->skipOperation();
		}
	}
	
	/**
	 * Implement postDelete() hook and set the deleted flag to true
	 *
	 * @param Doctrine_Event $event
	 * @return void
	 */
	public function postDelete(Doctrine_Event $event)
	{
		if ( ! $this->_options['hardDelete']) {
			$event->getInvoker()->save();
		}
	}

	
	//not implemented... must fix some errors before
	//@claudiu 20.08.2017 -rc-fix
	//@claudiu xx.08.2017 - rc to v1.0 -> is in production
	//@claudiu 16.04.2018 - added change_date & change_user because this listener may be after the Timestamp listener
	
	
	/**
	 * Implement preDqlDelete() hook and modify a dql delete query so it updates the deleted flag
	 * instead of deleting the record
	 *
	 * @param Doctrine_Event $event
	 * @return void
	 */
	public function preDqlDelete(Doctrine_Event $event)
	{	
		$params = $event->getParams();
		$field = $params['alias'] . '.' . $this->_options['name'];
		$query = $event->getQuery();
		if ( ! $query->contains($field)) {
			$query->from('')->update($params['component']['table']->getOption('name') . ' ' . $params['alias']);
	
			if ($this->_options['type'] == 'timestamp') {
				$query->set($field, '?', date('Y-m-d H:i:s', time()));
				$query->addWhere($field . ' IS NULL');
			} else if ($this->_options['type'] == 'boolean') {
				$query->set($field, $query->getConnection()->convertBooleans(true));
				$query->addWhere(
						$field . ' = ' . $query->getConnection()->convertBooleans(false)
				);
				
				if ($params['component']['table']->hasColumn('change_date') && ! $query->contains("change_date")) {
				    $query->set($params['alias'] . '.' . 'change_date', '?', date("Y-m-d H:i:s", time()));
				}
					
				if ($params['component']['table']->hasColumn('change_user') && ! $query->contains("change_user") ) {
				    $logininfo = new Zend_Session_Namespace('Login_Info');				
				    $query->set( $params['alias'] . '.' . 'change_user', '?', (int)$logininfo->userid);
				}
				
			}
		}
	}
	
	
	/**
	 * @cla on 16.05.2018
	 * + || ( ! $query->isSubquery() && ! empty($params['alias']) && ! $query->contains($field))
	 * 
	 * Implement preDqlSelect() hook and add the deleted flag to all queries for which this model
	 * is being used in.
	 *
	 * @param Doctrine_Event $event
	 * @return void
	 */
	public function preDqlSelect(Doctrine_Event $event)
	{
		$params = $event->getParams();
		$field = $params['alias'] . '.' . $this->_options['name'];
		$query = $event->getQuery();
	
		// We only need to add the restriction if:
		// 1 - We are in the root query
		// 1'- We are in the root query , but you have a field that contains our name (stripos(isdeleted, isdelete) == true).. so we try with alias 
		// 2 - We are in the subquery and it defines the component with that alias
// 		if (( ! $query->isSubquery() || ($query->isSubquery() && $query->contains(' ' . $params['alias'] . ' '))) && ! $query->contains($field)) {
        // TODO - if without Alias -  do ! 
		if (( ! $query->isSubquery() && ! $query->contains($this->_options['name']))
		    || ( ! $query->isSubquery() && ! empty($params['alias']) && ! $query->contains($field)) // @cla on 16.05.2018
			|| ($query->isSubquery() && $query->contains(' ' . $params['alias'] . ' ') && ! $query->contains($field))
		) {
			if ($this->_options['type'] == 'timestamp') {
				$query->addPendingJoinCondition($params['alias'], $field . ' IS NULL');
			} else if ($this->_options['type'] == 'boolean') {
				$query->addPendingJoinCondition(
						$params['alias'], $field . ' = ' . $query->getConnection()->convertBooleans(false)
				);
			}
		}
	}
	
	
	
	
	


}

?>