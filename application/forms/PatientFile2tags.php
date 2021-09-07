<?php

	require_once("Pms/Form.php");

	class Application_Form_PatientFile2tags extends Pms_Form {

		public function insert_file_tags($file_id = false, $tags_ids = false, $tag_tabname = false)
		{
			if($file_id && $tag_tabname && $tags_ids === false)
			{
				//get tags ids from tags tabnames
				$tags_ids = PatientFileTags::get_tabname_tagids($tag_tabname, true);
			}
			
			if($file_id && $tags_ids)
			{
				foreach($tags_ids as $k_tag => $v_tag_id)
				{
					$collection_data[] = array(
						'file' => $file_id,
						'tag' => $v_tag_id,
					);
				}
			
				$collection = new Doctrine_Collection('PatientFile2tags');
				$collection->fromArray($collection_data);
				$collection->save();
			}
		}
		
		public function edit_tag($post)
		{
			if($post['tag_id'] > '0')
			{
				$existing_tag = Doctrine::getTable('PatientFileTags')->findOneById($post['tag_id']);
	
				if($existing_tag)
				{
					$existing_tag->tag = $post['tag_name'];
					$existing_tag->save();
				}
			}
		}
		
		public function delete_tag($tagid = false)
		{
			if($tagid)
			{
				$doctrine_q = Doctrine_Query::create()
					->update('PatientFileTags')
					->set('isdelete', "1")
					->where('id = "'.$tagid.'"')
					->andWhere('isdelete = "0"');
				$doctrine_q->execute();
				
				$doctrine_q = Doctrine_Query::create()
					->update('PatientFile2tags')
					->set('isdelete', "1")
					->where('tag = "'.$tagid.'"')
					->andWhere('isdelete = "0"');
				$doctrine_q->execute();
			}
		}
	}

?>