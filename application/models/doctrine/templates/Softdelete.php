<?php

class Softdelete extends Doctrine_Template 
{
	
	/**
	 * Array of SoftDelete options
	 *
	 * @var string
	 */
	protected $_options = array(
			'name'          =>  'isdelete',
			'type'          =>  'boolean',
			'length'        =>  null,
			'options'       =>  array(
					'notnull' => false
			),
			'hardDelete' => false
	);
	
	
	protected $_listener;
	
	
	public function __construct(array $options = array())
	{
	    $this->_options = Doctrine_Lib::arrayDeepMerge($this->_options, $options);
	    
	    parent::__construct($options);
	}
	
	/**
	 * Set table definition for SoftDelete behavior
	 *
	 * @return void
	 */
	public function setTableDefinition()
	{

		//default 0 means the row is considered a NON-deleted one
		$this->hasColumn($this->_options['name'], $this->_options['type'], 1, array(
				'type' => 'integer',
				'length' => 1,
				'fixed' => false,
				'unsigned' => false,
				'primary' => false,
				'default' => '0',
				'notnull' => true,
				'autoincrement' => false,
		));
			
		
		$this->_listener = new SoftdeleteListener($this->_options);
		$this->addListener($this->_listener, 'SoftdeleteListener');
	}

	
	
	public function hardDelete($conn = null)
	{
		if ($conn === null) {
			$conn = $this->_table->getConnection();
		}
		$this->_listener->hardDelete(true);
		$result = $this->_invoker->delete();
		$this->_listener->hardDelete(false);
		return $result;
	}
}

?>