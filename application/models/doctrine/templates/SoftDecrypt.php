<?php

/**
 * SoftDecrypt 
 * rc0.1
 * @claudiu xx.08.2017 - rc to v1.0 -> is in production
 *
 *
 * @package    ISPC
 * @subpackage Application (2017-08-09)
 * @author     claudiu <office@originalware.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class SoftDecrypt extends Doctrine_Template 
{
	
	/**
	 * Array of Column names that should be encrypted
	 *
	 * @var string
	 */
	protected $_options = array(
			
	);
	
	
	protected $_listener;

	
	public function setTableDefinition()
	{
		
		//find columns that are defined with encrypted => true and append them too?
		//not because someone will double-encode columns
		
// 		$options = array();
// 		$columns = $this->getTable()->getColumns();
// 		foreach($columns as $key=>$val) {
// 			if( isset($val['encrypted']) && $val['encrypted'] === true) {
// 				$options[$key] = $key;
// 			}
// 		}
// 		$this->_options = Doctrine_Lib::arrayDeepMerge($this->_options, $options);
		
		
		if( ! empty($this->_options)) {
			
			$this->_listener = new SoftDecryptListener($this->_options);
			$this->addListener($this->_listener);
		}
	}

}

?>