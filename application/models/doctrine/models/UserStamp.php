<?php

	Doctrine_Manager::getInstance()->bindComponent('UserStamp', 'SYSDAT');

	class UserStamp extends BaseUserStamp {

		public function getUserStamp($userid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('UserStamp')
				->where('userid=?', $userid);
			$dropexec = $drop->execute();

			if($dropexec)
			{
				$stamparr = $dropexec->toArray();
				return $stamparr;
			}
		}

		public function getUserStampById($userid, $stamp_id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('UserStamp')
				->where('userid=?', $userid)
				->andWhere('id=?', $stamp_id);
			$stamparr = $drop->fetchArray();

			return $stamparr;
		}

		public function getLastUserStamp($userid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('UserStamp')
				->where('userid=?', $userid)
				->andWhere('valid_till =?', '0000-00-00 00:00:00')
				->andWhere('isdelete = ?', '0')
				->orderBy("id DESC")
				->limit("1");
			$dropexec = $drop->execute();

			if($dropexec)
			{
				$stamparr = $dropexec->toArray();
				return $stamparr;
			}
		}

		public function getLatestUsermStamp($userid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('UserStamp')
				->where('userid=?', $userid)
				->andWhere('valid_till=?', '0000-00-00 00:00:00')
				->orderBy('id DESC')
				->limit(1);
			$stamparr = $drop->fetchArray();

			return $stamparr;
		}

		public function getAllActiveUserStamp($userid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('UserStamp')
				->where('userid=?', $userid)
				->andWhere('valid_till=?', '0000-00-00 00:00:00')
				->andWhere('isdelete =?', '0') ;
			$stamparr = $drop->fetchArray();

			return $stamparr;
		}

		public function getAllUsersActiveStamps($user_array)
		{
			/*if(empty($user_array))
			{
				$user_array['999999999999'] = 'XXXXXXXX';
			}*/
			if(!empty($user_array))
			{
				$drop = Doctrine_Query::create()
					->select('*')
					->from('UserStamp')
					->whereIn('userid', $user_array)
					->andWhere("valid_till=?", '0000-00-00 00:00:00')
					->andWhere('isdelete =?', '0');
				$stamparr = $drop->fetchArray();

				return $stamparr;
			}
		}

	}

?>