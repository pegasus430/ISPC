<?php
require_once("Pms/Form.php");
class Application_Form_Vacations extends Pms_Form
{

	public function validate_timeline ( $post )
	{
		if (strtotime($post['end_date']) >= strtotime($post['start_date']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function is_overlapped ( $userid, $start, $end )
	{
		if ($userid && !empty($start) && !empty($end))
		{
			$vacations = Doctrine_Query::create()
			->select('*')
			->from('UserVacations')
			->where('userid="' . $userid . '"');
			$user_vacations = $vacations->fetchArray();

			$overlapped = false;
			foreach ($user_vacations as $k_vacation => $v_vacation)
			{
				$r1start = strtotime($start);
				$r1end = strtotime($end);

				$r2start = strtotime($v_vacation['start']);
				$r2end = strtotime($v_vacation['end']);

				if (Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end))
				{
					if ($r1start == $r2end && $r1end >= $r1start)
					{
						$overlapped = false;
					}
					else
					{
						$overlapped = true; //overlapped
					}
				}
			}
			return $overlapped;
		}
	}

	public function save_vacation ( $post )
	{
		if (!empty($post['start_date']) && !empty($post['end_date']))
		{
			$vacation = new UserVacations();
			$vacation->userid = $post['userid'];
			$vacation->start = date('Y-m-d H:i:s', strtotime($post['start_date']));
			$vacation->end = date('Y-m-d H:i:s', strtotime($post['end_date']));
			$vacation->save();

			return $vacation->id;
		}
		else
		{
			return false;
		}
	}

	public function save_vacation_replacements ( $userid, $vacation, $replacements )
	{
		$del_old = Doctrine_Query::create()
		->delete("VacationsReplacements")
		->where('userid="' . $userid . '"')
		->andWhere('vacation="' . $vacation . '"');
		$del_old_exec = $del_old->execute();

		foreach ($replacements as $ipid => $value)
		{
			$records[] = array(
					"vacation" => $vacation,
					"userid" => $userid,
					"ipid" => $ipid,
					"replacement" => $value
			);
		}

		//insert many with one query!!
		$collection = new Doctrine_Collection('VacationsReplacements');
		$collection->fromArray($records);
		$collection->save();
	}

	public function edit_period ( $vacation, $post )
	{


		$upd = Doctrine_Query::create()
		->update('UserVacations')
		->set('start', "'" . date('Y-m-d H:i:s', strtotime($post['start'])) . "'")
		->set('end', "'" . date('Y-m-d H:i:s', strtotime($post['end'])) . "'")
		->where('id="' . $vacation . '"');
		$exec = $upd->execute();
	}

	public function delete_vacation($vacation)
	{
		$del_vacation = Doctrine_Query::create()
		->delete('UserVacations')
		->where('id="'.$vacation.'"');
		$del_vacation->execute();

		$del_replacements = Doctrine_Query::create()
		->delete('VacationsReplacements')
		->where('vacation = "'.$vacation.'"');
		$del_replacements->execute();

		if($del_vacation)
		{
			return true;
		}
		else
		{
			return false;
		}

	}

}
?>