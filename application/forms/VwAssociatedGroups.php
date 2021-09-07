<?php

	require_once("Pms/Form.php");

	class Application_Form_VwAssociatedGroups extends Pms_Form {


	    function associated_groups_create($name = '', $status = '0')
	    {
	        $group = new VwAssociatedGroups();
	        $group->name = $name;
	        $group->status = $status;
	        $group->save();
	    
	        return $group->id;
	    }
	    
	    function associated_groups_mark_deleted($group)
	    {
	        $cl_group = Doctrine_Query::create()
	        ->update("VwAssociatedGroups")
	        ->set('status', "1")
	        ->where("id='" . $group . "'");
	        $cl_group->execute();
	    
	        return $cl_group->id;
	    }
	    
	}

?>