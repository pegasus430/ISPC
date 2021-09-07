<?php
require_once("Pms/Form.php");
class Application_Form_Anlage5part2 extends Pms_Form
{

	public function validate ( $post )
	{

	}

	public function insert_data ( $ipid, $post, $current_period )
	{
		foreach ($post['medi'] as $k_id_medi => $v_medi)
		{
			$selector = Doctrine_Query::create()
			->select('*')
			->from('Anlage5Part2')
			->where('ipid = "' . $ipid . '" ')
			->andWhere('diagnosis = "' . $post['medi_row'][$k_id_medi] . '"')
			->andWhere('DATE(date) = DATE("'.date('Y-m-d H:i:s', strtotime($post['medi_date'][$k_id_medi])).'")');
			$selector_res = $selector->fetchArray();

			if ($selector_res)
			{
				//do update
				if (strlen($post['medi_dosage'][$k_id_medi]) > '0')
				{
					$medi_dosage = ' - ' . $post['medi_dosage'][$k_id_medi];
				}
				else
				{
					$medi_dosage = '';
				}

				$medication = $selector_res[0]['medication'];
				$medication .= ', ' . $post['medi_name'][$k_id_medi] . $medi_dosage;

				$update = Doctrine_Query::create()
				->update('Anlage5Part2')
				->set('medication', '"' . $medication . '"')
				->where('id = "' . $selector_res[0]['id'] . '"');
				$update->execute();
			}
			else
			{
				//do insert
				$insert = new Anlage5Part2();
				$insert->ipid = $ipid;
				$insert->diagnosis = $post['medi_row'][$k_id_medi];
				$insert->medication = $post['medi_name'][$k_id_medi] . ' - ' . $post['medi_dosage'][$k_id_medi];
				$insert->date = date('Y-m-d H:i:s', strtotime($post['medi_date'][$k_id_medi]));
				$insert->save();
			}
		}
	}

	public function edit_anlage5part2_entries ( $post )
	{
		foreach ($post['edit_anlage_medi'] as $k_id => $v_value)
		{
			$update = Doctrine_Query::create()
			->update('Anlage5Part2')
			->set('medication', '"' . $v_value . '"')
			->where('id = "' . $k_id . '"');
			$update_res = $update->execute();
		}
	}

}
?>