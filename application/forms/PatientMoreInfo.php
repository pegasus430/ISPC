<?

class Application_Form_PatientMoreInfo extends Pms_Form{


	public function InsertData($post)
	{
		$frm = new PatientMoreInfo();
		$frm->ipid = $post['ipid'];
		$frm->dk = $post['dk'];
		$frm->peg = $post['peg'];
		$frm->port = $post['port'];
		$frm->pumps = $post['pumps'];
		$frm->zvk = $post['zvk'];
		$frm->save();
	}

	public function UpdateData($post)
	{
		$q = Doctrine_Query::create()
		->update('PatientMoreInfo')
		->set('dk', "'".$post['dk']."'")
		->set('peg', "'".$post['peg']."'")
		->set('port', "'".$post['port']."'")
		->set('pumps', "'".$post['pumps']."'")
		->set('zvk', "'".$post['zvk']."'")
		->where("ipid = '".$post['ipid']."'");
		$q->execute();

	}
	public function InsertMoreInfoData($ipid =  null , $data = array())
	{
		if (empty($ipid) || ! is_array($data)) {
			return;
		}
		// 	    $controller = Zend_Controller_Front::getInstance();
		
		$entity = new PatientMoreInfo();
		$result = $entity->findOrCreateOneByIpidAndId($ipid, $data['id'], $data);
		return $result;
	}

}

?>