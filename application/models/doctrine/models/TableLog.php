<?php
/**
 * ISPC-2429 Alex 10.12.2020
 */
	Doctrine_Manager::getInstance()->bindComponent('TableLog', 'SYSDAT');

	class TableLog extends BaseTableLog { 
	    
	    public function set_new_record($params = array())
	    {
	        
	        if (empty($params) || !is_array($params)) {
	            return false;// something went wrong
	        }
	        
	        foreach ($params as $k => $v)
	            if (isset($this->{$k})) {
	                $this->{$k} = $v;
	            }
	        
	        $this->save();
	        return $this->id;
	        
	    }
	    
	}

?>