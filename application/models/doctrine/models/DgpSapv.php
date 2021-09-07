<?php

	Doctrine_Manager::getInstance()->bindComponent('DgpSapv', 'MDAT');

	class DgpSapv extends BaseDgpSapv {

		public function checkCompletedSapv($ipid, $sapv_id)
		{
		    /* ISPC-1775,ISPC-1678 */
			$drop = Doctrine_Query::create()
				->select('*')
				->from('SapvVerordnung')
				->where("ipid='" . $ipid . "' and isdelete=0")
				->orderBy("id");
			$dropexec = $drop->execute();
			if($dropexec)
			{
				$droparray = $dropexec->toArray();

				return $droparray;
			}
		}

	}

?>