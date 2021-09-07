<?php

	Doctrine_Manager::getInstance()->bindComponent('MembersSepaXml', 'SYSDAT');
	
	
	class MembersSepaXml extends BaseMembersSepaXml {

		private $first_batch_insert_id = 0; //this is used to connect sepa files ( 1 or many , never empty)
		
		private $args = array();
		
// 		public function set_save_sepa_file($clientid, $memberid, $invoiceid, $filename_nice , $ftp_file , $invoiceid_extra = 0 , $batch_mode)
		public function set_save_sepa_file( $args = array())
		{
			$this->args = func_get_arg(0);
			
			$insert = new MembersSepaXml();
			
			$insert->clientid = $this->args['clientid'];
			$insert->memberid = $this->args['memberid'];
			$insert->invoiceid = $this->args['invoiceid'];
			$insert->invoiceid_extra = $this->args['invoiceid_extra'];
			$insert->paymentid = $this->args['paymentid'];
			$insert->filename_nice = $this->args['filename_nice'];
			$insert->ftp_file = $this->args['ftp_file'];
			$insert->file_type = 'xml';
			$insert->status = 0;
			$insert->isdelete = 0;
			
			
			if ($this->first_batch_insert_id != 0) {
				$insert->batchid = $this->first_batch_insert_id;
			}
			
			$insert->save();
			
			if ($insert->id)
			{
				if ($this->first_batch_insert_id == 0) {
					
					$insert->batchid =
					$this->first_batch_insert_id = $insert->id;
					
					$insert->save();
				}
				
				return $insert->id;
			}
			else
			{
				return false;
			}

			
		}
		
		/*
		 * if no results return false
		 * if $memberid is string it will return array
		 * if $memberid is array it will return multidimensional-array of memberid
		 */
		public function get_member_sepa_files($memberid = false , $clientid = false)
		{
			$is_array = true;
			if( !is_array($memberid) || empty($memberid) ){
				$is_array = false;
				$memberid = (empty($memberid)) ? array('0') : array($memberid);
			}
			
			$query = Doctrine_Query::create()
				->select("*, IF(batchid = 0, ftp_file, batchid) as group_by_batch")
				->from('MembersSepaXml INDEXBY id')
				->whereIn("memberid" , $memberid)
				->andWhere('clientid = ?' , $clientid)
				->andWhere('isdelete = "0"')
				->groupBy("group_by_batch")
				->orderBy("create_date ASC");
			
				
			$query_res = $query->fetchArray();
			if (empty($query_res)){
				$result = false;
			}
			elseif ($is_array){
				foreach ($query_res as $row){
					$result[ $row['memberid'] ] [ $row['id'] ] = $row;
				}	
			}else{
				$result = $query_res;
			}
			return $result;
			
		}

		
		public function get_sepa_files_by_id($id = 0 , $clientid = 0)
		{

			$query = Doctrine_Query::create()
			->select("*")
			->from('MembersSepaXml')
			->where("id = ? " , $id)
			->andWhere('clientid = ?' , $clientid)
			->andWhere('isdelete = "0"')
			->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
			
			if (empty($query)) {
				
				$result = false;
				
			}
			else {
				
				$result = $query;
			}

			return $result;
				
		}

		public function get_sepa_files_by_invoiceid($invoiceid = 0 , $clientid = 0)
		{
		
			$query = Doctrine_Query::create()
			->select("*")
			->from('MembersSepaXml')
			->where("invoiceid = ? " , $invoiceid)
			->andWhere('clientid = ?' , $clientid)
			->andWhere('isdelete = "0"')
			->limit(1);

			$query_res = $query->fetchArray();
			if (empty($query_res)){
				$result = false;
			}
			elseif(is_array($query_res)){
				$query_res = array_values($query_res);
				$result = $query_res[0];
			}
			
			return $result;
		
		}

		public function get_client_sepa_files($clientid = false, $filters = array())
		{
			$query = Doctrine_Query::create()
			->select("*")
			->from('MembersSepaXml INDEXBY id')
			->Where('clientid = :clientid' , array("clientid"=>$clientid))
			->andWhere('isdelete = :isdelete' , array("isdelete"=>"0"))
			->orderBy("id DESC");

			if (is_array($filters))
			foreach ($filters  as $row) {

				if ( ! empty($row['where']) && is_string($row['where'])) {
				
					$query->andWhere($row['where'], $row['params']);
							
				} 
				
				elseif ( ! empty($row['whereIn']) && is_array($row['params'])) {
				
					$query->andWhereIn($row['whereIn'], $row['params']);
							
				} 
				
				elseif ( ! empty($row['limit'])) {

					$query->limit($row['limit']);
				}
				
				elseif ( ! empty($row['offset'])) {

					$query->offset($row['offset']);
				}
			}
			
// 			Pms_DoctrineUtil::get_raw_sql($query);die();
			
			$query_res = $query->fetchArray();
			if (empty($query_res)){
				$result = false;
			}else{
				$result = $query_res;
			}
			return $result;
				
		}
		
		
		public function get_sepa_files_by_ftp_file($ftp_file = '', $clientid = 0) 
		{
			$query = Doctrine_Query::create()
			->select("*")
			->from('MembersSepaXml')
			->Where('clientid = ?' , $clientid)
			->andWhere("ftp_file = ? " , $ftp_file)
			->andWhere('isdelete = "0"')
			->fetchArray();

			return $query;
		
		}
		
		public function get_sepa_files_by_batchid($batchid = 0 , $clientid = 0) 
		{
			$query = Doctrine_Query::create()
			->select("*")
			->from('MembersSepaXml')
			->Where('clientid = ?' , $clientid)
			->andWhere("batchid = ? " , $batchid)
			->andWhere('isdelete = "0"')
			->fetchArray();
				
			return $query;
		}
		
		
		//this is a status update
		public function set_status_and_comment($status = 0, $comment="", $ids = array(), $clientid = 0 )
		{
			//$status ="3" => paid		

			if ( empty($ids)) {
				return;
			}
			
			if ( ! is_array($ids)) {
				$ids = array($ids);
			}		
					
			$q = Doctrine_Query::create()
			->update('MembersSepaXml')
			->set('status', '?' , (int)$status)
			->set('comment', '?' , $comment)
			->whereIn('id', $ids)
			->andWhere('clientid = ? ', $clientid)
			->andWhere('status != ? ', $status)
			->execute();
			
		}
		
		
	}

?>