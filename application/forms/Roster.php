<?php
require_once("Pms/Form.php");
class Application_Form_Roster extends Pms_Form
{

    /**
     *
     * @cla on 21.03.2019 - new clear_roster_by_cells , and insert all within a collection
     * 
     * @param int $clientid
     * @param array $post
     * @param unknown $month_start - not used
     * @return Ambigous <multitype:, multitype:NULL unknown >
     */
    public function insert_data ( $clientid = 0, $post = null, $month_start = null )
    {
        if (empty($post) || empty($clientid) || ! is_array($post)) {
            
            return; //fail-safe
        }
        
        $user_ids =  array_keys($post['duty']);
        
        $users2groups = [];
        
        if ( ! empty($user_ids)) 
        {
            $users_details = Doctrine_Query::create()
            ->select('id, groupid')
            ->from('User')
            ->whereIn('id', $user_ids)
            ->fetchArray();
            
            $users2groups = array_column($users_details, 'groupid', 'id');            
        }
        
        $this->clear_roster_by_cells($clientid, $post);
        
        $insertRosterData = [];
        
        foreach ($post['duty'] as $k_user_id => $v_user_data)
        {
            foreach ($v_user_data as $k_roster_date => $v_shift_data)
            {
                $date = date('Y-m-d', $k_roster_date);
                foreach ($v_shift_data as $k_row => $v_shift_id)
                {
                    if (!empty($v_shift_id) || $v_shift_id == '0')
                    {
        
                        $insertRosterData[] = [
                            'clientid'  => $clientid,
                            'duty_date' => $date,
                            'userid'    => $k_user_id,
                            'user_group'    => $users2groups[$k_user_id],
                            'shift'     => $v_shift_id,
                            '`row`'       => $k_row,//MySQL 8 Ancuta 16.06.2021
                            'isdelete'  => 0,
                        ];
                    }
                }
            }
        }
        
        $RosterCollection = new Doctrine_Collection('Roster');
        $RosterCollection->fromArray($insertRosterData);
        $RosterCollection->save();   
        
        return $RosterCollection->getPrimaryKeys();
    }
    
    
    /**
     * @cla on 21.03.2019 - new logic to be like Archivable and not SoftDelete
     * @param int $clientid
     * @param array $days
     */
    public function clear_roster_by_cells ( $clientid = 0 , $days = null )
    {
        if(!empty($clientid)) {
    
            $logininfo = new Zend_Session_Namespace('Login_Info');
            
    
            $create_user = $logininfo->userid;
            $create_date = date('Y-m-d H:i:s');
            
            $conn = Doctrine_Manager::getInstance()->getConnection('SYSDAT');
            
            $sqlInsert = "INSERT INTO `duty_roster_archived`
                    SELECT *, NULL, :create_user, :create_date FROM `duty_roster` WHERE `clientid` = :clientid AND `userid` = :userid AND `duty_date` = :duty_date AND `row` = :row LIMIT 1
                    #NULL is the PK 
                ";
            $queryInsert = $conn->prepare($sqlInsert);

            $sqlDelete = "DELETE FROM `duty_roster` WHERE `clientid` = :clientid AND `userid` = :userid AND `duty_date` = :duty_date AND `row` = :row LIMIT 1";
            $queryDelete = $conn->prepare($sqlDelete);    
            
            
            //deleted days
            
            $deleted_cells = explode(',',$days['deleted_cells']);
            $deleted_cells = array_unique($deleted_cells);
            
            if(!empty($deleted_cells) && is_array($deleted_cells)) {
                foreach($deleted_cells as $cell) {
                    if(!empty($cell)) {
                        $cell_exp = explode('_', $cell);
                        $cell_date = date('Y-m-d', $cell_exp[0]);
                        if($cell_date && !empty($cell_exp[1]) && (!empty($cell_exp[2]) || $cell_exp[2] == '0')) {
                            
                            $bindDataInsert = [
                                'create_user'   => $create_user,
                                'create_date'   => $create_date,
                                'clientid'  => $clientid,
                                'userid'    => $cell_exp[1],
                                'duty_date' => $cell_date,
                                'row'       => $cell_exp[2],
                            ];
                            $bindDataDelete = [
                                'clientid'  => $clientid,
                                'userid'    => $cell_exp[1],
                                'duty_date' => $cell_date,
                                'row'       => $cell_exp[2]
                            ];                            
                            //TODO: you can set isdelete=1 to this items, so you know the user REALLY just wanted to delete just this ..
                            $queryInsert->execute($bindDataInsert);
                            $queryInsert->closeCursor();
                            
                            $queryDelete->execute($bindDataDelete);                            
                            $queryDelete->closeCursor();
                        }
                    }
                }
            }
    
            //delete days that have shifts too, just to be safe
            foreach ($days['duty'] as $k_user_id => $v_user_data)
            {
                foreach ($v_user_data as $k_roster_date => $v_shift_data)
                {
                    $date = date('Y-m-d', $k_roster_date);
                    foreach ($v_shift_data as $k_row => $v_shift_id)
                    {
                        if (!empty($v_shift_id) || $v_shift_id == '0' && !empty($date))
                        {
                            
                            $bindDataInsert = [
                                'create_user'   => $create_user,
                                'create_date'   => $create_date,
                                'clientid'  => $clientid,
                                'userid'    => $k_user_id,
                                'duty_date' => $date,
                                'row'       => $k_row,
                            ];
                            $bindDataDelete = [
                                'clientid'  => $clientid,
                                'userid'    => $k_user_id,
                                'duty_date' => $date,
                                'row'       => $k_row,
                            ];
                            
                            $queryInsert->execute($bindDataInsert);
                            $queryInsert->closeCursor();
                            
                            $queryDelete->execute($bindDataDelete);                            
                            $queryDelete->closeCursor();
                        }
                    }
                }
            }
        }
    
    }
    
    
    
    /**
     * @deprecated - this was the original insert_data()
     */
	public function insert_data_OLD ( $clientid, $post, $month_start )
	{
		
		//clearing old data moved just before first insert
		//if(count($post['duty']) > '0')
		//{
		//	$clear_roster = $this->clear_roster_old_data($clientid, $month_start);
		//}
		
		$user_ids[] = '999999999999999';
		foreach ($post['duty'] as $k_userid => $v_userdata)
		{
			$user_ids[] = $k_userid;
		}

		$get_users_details = Doctrine_Query::create()
			->select('*')
			->from('User')
			->whereIn('id', $user_ids);
		$users_details = $get_users_details->fetchArray();

		foreach ($users_details as $k_user_det => $v_user_det)
		{
			$users2groups[$v_user_det['id']] = $v_user_det['groupid'];
		}
		
		
		//delete now, insert after
		
		$this->clear_roster_by_cells($clientid, $post);
		
		foreach ($post['duty'] as $k_user_id => $v_user_data)
		{
			foreach ($v_user_data as $k_roster_date => $v_shift_data)
			{
				$date = date('Y-m-d', $k_roster_date);
				foreach ($v_shift_data as $k_row => $v_shift_id)
				{
					if (!empty($v_shift_id) || $v_shift_id == '0')
					{
						
						$ins_roster = new Roster();
						$ins_roster->clientid = $clientid;
						$ins_roster->duty_date = $date;
						$ins_roster->userid = $k_user_id;
						$ins_roster->user_group = $users2groups[$k_user_id];
						$ins_roster->shift = $v_shift_id;
						$ins_roster->row = $k_row;
						$ins_roster->isdelete = '0';
						$ins_roster->save();
					}
				}
			}
		}
		
//		if(count($post['duty']) > '0')
//		{
//			$clear_roster = $this->clear_roster_old_data($clientid, $month_start);
//			
//			//insert many with one query!!
//			$collection = new Doctrine_Collection('Roster');
//			$collection->fromArray($record_data);
//			$collection->save();
//		}
	}

	public function clear_roster_old_data ( $clientid, $start )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		if (strlen($clientid) > 0 && strlen($start) > 0)
		{
			$Q = Doctrine_Query::create()
				->update('Roster')
				->set('isdelete', "1")
				->set('change_date', 'NOW()')
				->set('change_user', $logininfo->userid)
				->where("clientid='" . $clientid . "'")
				->andWhere('MONTH(duty_date) = MONTH("' . $start . '") AND YEAR(duty_date) = YEAR("' . $start . '")')
				->andWhere('isdelete = "0"');
			$Q->execute();

			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * @deprecated - this was the original clear_roster_by_cells()
	 */
	public function clear_roster_by_cells_OLD ( $clientid, $days )
	{
		if(!empty($clientid)) {
			
			$logininfo = new Zend_Session_Namespace('Login_Info');

			//deleted days

			$deleted_cells = explode(',',$days['deleted_cells']);
			$deleted_cells = array_unique($deleted_cells);

			if(!empty($deleted_cells) && is_array($deleted_cells)) {
				foreach($deleted_cells as $cell) {
					if(!empty($cell)) {
						$cell_exp = explode('_', $cell);
						$cell_date = date('Y-m-d', $cell_exp[0]);
						if($cell_date && !empty($cell_exp[1]) && (!empty($cell_exp[2]) || $cell_exp[2] == '0')) {
							$del_q = Doctrine_Query::create()
							->update('Roster')
							->set('isdelete', "1")
							->set('change_date', 'NOW()')
							->set('change_user', $logininfo->userid)
							->where("clientid='" . $clientid . "'")
							->andWhere('duty_date = "'. $cell_date .'"')
							->andWhere('userid = "'. $cell_exp[1] .'"')
							->andWhere('`row` = "'. $cell_exp[2].'"')
							->andWhere('isdelete = "0"');
							$del_q->execute();
						}
					}
				}
			}

			//delete days that have shifts too, just to be safe
			foreach ($days['duty'] as $k_user_id => $v_user_data)
			{
				foreach ($v_user_data as $k_roster_date => $v_shift_data)
				{
					$date = date('Y-m-d', $k_roster_date);
					foreach ($v_shift_data as $k_row => $v_shift_id)
					{
						if (!empty($v_shift_id) || $v_shift_id == '0' && !empty($date))
						{
							$del_q = Doctrine_Query::create()
							->update('Roster')
							->set('isdelete', "1")
							->set('change_date', 'NOW()')
							->set('change_user', $logininfo->userid)
							->where("clientid='" . $clientid . "'")
							->andWhere('duty_date= "'.$date.'"')
							->andWhere('userid= "'. $k_user_id.'"')
							->andWhere('`row` = "'. $k_row.'"')
							->andWhere('isdelete = "0"');
							$del_q->execute();
						}
					}
				}
			}
		}

	}

}
?>