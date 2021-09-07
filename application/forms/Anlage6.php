<?php
require_once("Pms/Form.php");
class Application_Form_Anlage6 extends Pms_Form
{
	public function validate ( $post )
	{

	}

	public function InsertData ( $post, $ipid, $clientid, $shortcut )
	{
		$pm = new PatientMaster();
		$selected_month = $post['select_month'];

		if (!function_exists('cal_days_in_month'))
		{
			$daysin_month = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
		}
		else
		{
			$daysin_month = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
		}

		$start = date('Y-m-d', strtotime($selected_month . '-01'));
		$end = date('Y-m-d', strtotime($selected_month . '-' . $daysin_month));
		$month_days = $pm->getDaysInBetween($start, $end);

		foreach ($month_days as $key => $day)
		{
			if (strlen($post[$shortcut][date('d', strtotime($day))]) > 0)
			{
				$selected = '1';
			}
			else
			{
				$selected = '0';
			}

			$records[] = array(
					"clientid" => $clientid,
					"ipid" => $ipid,
					"shortcut" => strtoupper($shortcut),
					"date" => date('Y-m-d H:i:s', strtotime($day)),
					"value" => $selected
			);
		}

		$clear_month_entryes = $this->clear_month_data($start, $clientid, $ipid, $shortcut);

		if ($clear_month_entryes)
		{
			//insert many with one query!!
			$collection = new Doctrine_Collection('Anlage6');
			$collection->fromArray($records);
			$collection->save();
		}
	}

	public function clear_month_data ( $start_day, $client, $ipid, $shortcut )
	{
		if (strlen($start_day) > 0 && strlen($shortcut) > 0)
		{
			$Q = Doctrine_Query::create()
			->delete('Anlage6')
			->where("clientid='" . $client . "'")
			->andWhere('ipid LIKE "' . $ipid . '"')
			->andWhere('MONTH(date) = MONTH("' . $start_day . '") AND YEAR(date) = YEAR("' . $start_day . '")')
			->andWhere('shortcut="' . strtoupper($shortcut) . '"');
			$Q->execute();

			return true;
		}
		else
		{
			return false;
		}
	}

}
?>