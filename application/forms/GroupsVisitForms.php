<?php
require_once("Pms/Form.php");
class Application_Form_GroupsVisitForms extends Pms_Form
{

	public function insert_data ( $post )
	{
	    $contact_ft_array = array();
	    
		if ($post['group_visit_form'])
		{
			foreach ($post['group_visit_form'] as $k_group => $k_tabmenu)
			{
			    if(strpos($k_tabmenu,"_")){
			        $contact_ft_array[$k_group] = explode("_",$k_tabmenu);
			    }
			    
				if (strlen($k_tabmenu) > 0)
				{
					if (strlen($_SESSION['filename'][$k_group]) > 0)
					{
						$image = $_SESSION['filename'][$k_group];
					}
					else
					{
						$image = $post['image'][$k_group];
					}

					if(is_array($contact_ft_array[$k_group]) && count($contact_ft_array[$k_group]) == 2){
					    
    					$records[] = array(
    							'client' => $post['clientid'],
    							'groupid' => $k_group,
    							'tabmenu' =>  $contact_ft_array[$k_group][0],
    							'form_type' => $contact_ft_array[$k_group][1],
    							'image' => $image
    					);
					    
					} else {
					   $records[] = array(
							'client' => $post['clientid'],
							'groupid' => $k_group,
							'tabmenu' => $k_tabmenu,
							'form_type' => "0",
							'image' => $image
					);
					    
					}
					
				}
			}
			
// 			print_r($records); exit;
			 
			$clear_old_data = $this->clear_client_groups($post['clientid']);
			//
			//insert many with one query!!
			$collection = new Doctrine_Collection('GroupsVisitForms');
			$collection->fromArray($records);
			$collection->save();
		}
		else
		{
			return false;
		}
	}

	public function clear_client_groups ( $client )
	{
		$Q = Doctrine_Query::create()
		->delete('GroupsVisitForms')
		->where("client='" . $client . "'");
		$Q->execute();
	}

}
?>