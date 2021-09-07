<?php

	require_once("Pms/Form.php");

	class Application_Form_QuestionnaireC extends Pms_Form {

	    
	    /**
	     * MySQL 8 Ancuta 16.06.2021
	     * @param unknown $post
	     */
		public function insert_new_data($post)
		{
			if(strlen($post['ipid']) > '0')
			{
				foreach($post['row'] as $key => $value)
				{
				    $records[] = array(
				        'ipid' => $post['ipid'],
				        '`row`' => $key,
				        'value' => $value,
				        'isdelete' => '0'
				    );
				    /*
					$ins = new QuestionnaireC();
					$ins->ipid = $post['ipid'];
					$ins->row = $key;
					$ins->value = $value;
					$ins->isdelete = '0';
					$ins->save();*/
				}
				
				
				if(!empty($records))
				{
				    $collection = new Doctrine_Collection('QuestionnaireC');
				    $collection->fromArray($records);
				    $collection->save();
				}
			}
		}

		public function update_data($post)
		{
			if(strlen($post['ipid']) > '0')
			{
				foreach($post['row'] as $key => $value)
				{
					$update = Doctrine::getTable('QuestionnaireC')->findOneByIpidAndRowAndIsdelete($post['ipid'], $key, "0");
					if($update)
					{
						$update->value = $value;
						$update->save();
					}
				}
			}
		}

	}

?>