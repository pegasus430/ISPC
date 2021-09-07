<?php

	Doctrine_Manager::getInstance()->bindComponent('Anlage5Part1', 'MDAT');

	class Anlage5Part1 extends BaseAnlage5Part1 {

		//first table
		public function get_current_problems()
		{
			$current_problems = array(
				'1' => 'average_pain',
				'2' => 'max_pain',
				'3' => 'nausea',
				'4' => 'throw_up',
				'5' => 'constipation_diarrhea',
				'6' => 'breathlessness',
				'7' => 'weakness',
				'8' => 'fear',
				'9' => 'skin_problem',
			);

			return $current_problems;
		}

		public function get_problem_options()
		{
			$problems_options = array(
				'1' => 'nein',
				'2' => 'leicht',
				'3' => 'mittel',
				'4' => 'stark',
			);

			return $problems_options;
		}

		//second table
		public function get_pretreatment_problems()
		{
			$pretreatment_problems = array(
				'1' => 'pain',
				'2' => 'nausea',
				'3' => 'throw_up',
				'4' => 'constipation_diarrhea',
				'5' => 'breathlessness',
				'6' => 'weakness',
				'7' => 'fear',
				'8' => 'skin_problem',
			);

			return $pretreatment_problems;
		}

		public function get_pretreatment_options()
		{
			$pretreatment_options = array(
				'1' => 'nein',
				'2' => 'ja',
				'3' => 'bedarf',
			);

			return $pretreatment_options;
		}

		public function get_anlage5_data($ipid)
		{
			$selector = Doctrine_Query::create()
				->select('*')
				->from('Anlage5Part1')
				->where('ipid = "' . $ipid . '"')
				->andWhere('isdelete = "0"')
				->limit(1)
				->orderBy('id ASC');
			$selector_arr = $selector->fetchArray();

			if($selector_arr)
			{
				//get current problems data
				$current_problems = new Anlage5CurrentProblems();
				$current_problems_arr = $current_problems->get_form_current_problems($ipid, $selector_arr[0]['id']);

				if($current_problems_arr)
				{
					foreach($current_problems_arr as $k_cp => $v_cp)
					{
						$data[$v_cp['symptom']] = $v_cp;
					}

					$selector_arr[0]['current_problems_data'] = $data;
				}

				//get pretreatment data
				$pretreatment = new Anlage5Pretreatment();
				$pretreatment_arr = $pretreatment->get_form_pretreatment($ipid, $selector_arr[0]['id']);

				if($pretreatment_arr)
				{
					foreach($pretreatment_arr as $k_pr => $v_pr)
					{
						$data_pr[$v_pr['symptom']] = $v_pr;
					}

					$selector_arr[0]['pretreatment_data'] = $data_pr;
				}

				return $selector_arr;
			}
			else
			{
				return false;
			}
		}

	}

?>