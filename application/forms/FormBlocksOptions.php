<?php
require_once("Pms/Form.php");
class Application_Form_FormBlocksOptions extends Pms_Form
{

	public function open_form_blocks ( $client, $form_type, $post_data )
	{
		if ($client && $form_type)
		{
			$clear_perms = $this->clear_block_permisions($client, $form_type);

			foreach ($post_data['open'] as $block_key => $block_value)
			{
				$assign_arr[] = array(
						'clientid' => $client,
						'form_type' => $form_type,
						'block' => $block_key,
						'open' => $block_value,
				);
			}

			if (count($assign_arr) > 0)
			{
				$collection = new Doctrine_Collection('FormBlocksOptions');
				$collection->fromArray($assign_arr);
				$collection->save();
			}

			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	/**
	 * TODO-3843 Ancuta 11.02.2021
	 * @param unknown $client
	 * @param unknown $form_type
	 * @param unknown $post_data
	 * @return boolean
	 */
	public function save_form_blocks_options ( $client, $form_type, $post_data )
	{
	 
		if ($client && $form_type)
		{
			$clear_perms = $this->clear_block_permisions($client, $form_type);

			// process data first
			$post_array = array();
			foreach ($post_data['assign'] as $block_key => $block_value)
			{
			    if( $block_value == 1){
    			    if(isset($post_data['open'][$block_key]) && $post_data['open'][$block_key] == 1){
    			        $post_array[$block_key]['open'] = 1; 
    			    } else{
    			        $post_array[$block_key]['open'] = 0; 
    			    }
    			    
    			    if(isset($post_data['write2course_recorddata'][$block_key]['write2recorddata']) && $post_data['write2course_recorddata'][$block_key]['write2recorddata'] == 1){
    			        $post_array[$block_key]['write2recorddata'] = 1; 
    			    } else{
    			        $post_array[$block_key]['write2recorddata'] = 0; 
    			    }
    			    
    			    if(isset($post_data['write2course_recorddata'][$block_key]['write2recorddata_color']) ){
    			        $post_array[$block_key]['write2recorddata_color'] = $post_data['write2course_recorddata'][$block_key]['write2recorddata_color']; 
    			    }else{
    			        $post_array[$block_key]['write2recorddata_color'] = ""; 
    			    }

                    //TODO-4035 Nico 12.04.2021
                    if(isset($post_data['write2course_recorddata'][$block_key]['write2shortcut']) ){
                        $post_array[$block_key]['write2shortcut'] = $post_data['write2course_recorddata'][$block_key]['write2shortcut'];
                    }else{
                        $post_array[$block_key]['write2shortcut'] = "";
                    }
			    }
			    
			}
			
			
			foreach($post_array as $block => $block_data){
			    
				$options_arr[] = array(
						'clientid' => $client,
						'form_type' => $form_type,
				        'block' => $block,
				        'open' => $block_data['open'],
				        'write2recorddata' => $block_data['write2recorddata'],
				        'write2recorddata_color' => $block_data['write2recorddata_color'],
                        'write2shortcut' => $block_data['write2shortcut'],//TODO-4035 Nico 12.04.2021
				);
			}
			
			if (count($options_arr) > 0)
			{
				$collection = new Doctrine_Collection('FormBlocksOptions');
				$collection->fromArray($options_arr);
				$collection->save();
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	public function clear_block_permisions ( $client, $form_type )
	{
		$del_assigned = Doctrine_Query::create()
		->delete('*')
		->from('FormBlocksOptions')
		->where('clientid="' . $client . '"')
		->andWhere('form_type="' . $form_type . '"');
		$del_assigned_exec = $del_assigned->execute();
	}

}
?>