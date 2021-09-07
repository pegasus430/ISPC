<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientFile2tags', 'MDAT');

	class PatientFile2tags extends BasePatientFile2tags {

		public function get_files_tags($fileids)
		{
			$res = Doctrine_Query::create()
				->select('*')
				->from('PatientFile2tags')
				->whereIn('file', $fileids)
				->andWhere('isdelete = "0"');
			$res_array = $res->fetchArray();

			if($res_array)
			{
				foreach($res_array as $k_res => $v_res)
				{
					$file_tags[$v_res['file']][] = $v_res['tag'];
				}

				return $file_tags;
			}
		}

		public function get_tag_files($patient_files = false, $tags)
		{
			if(is_array($tags))
			{
				$tags_arr = $tags;
			}
			else
			{
				$tags_arr = array($tags);
			}
			$tags_arr[] = '9999999';

			if($patient_files)
			{

				$res = Doctrine_Query::create()
					->select('*')
					->from('PatientFile2tags')
					->whereIn('tag', $tags_arr)
					->andWhereIn('file', $patient_files)
					->andWhere('isdelete = "0"');
				$res_array = $res->fetchArray();

				if($res_array)
				{
					foreach($res_array as $k_res => $v_res)
					{
						$file_tags[] = $v_res['file'];
					}

					return $file_tags;
				}
			}
		}

	}

?>