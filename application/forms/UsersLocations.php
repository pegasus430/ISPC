<?
class Application_Form_UsersLocations extends Pms_Form {
	public function InsertData($post) {
		$frm = new UsersLocations();
		$frm->user_id = $post['userid'];
		$frm->client_id = $post['clientid'];
		$frm->first_name = $post['fname'];
		$frm->last_name = $post['lname'];
		$frm->company_name = $post['companyname'];
		$frm->street = $post['street'];
		$frm->zip = $post['zip'];
		$frm->city = $post['city'];
		$frm->phone1 = $post['phone1'];
		$frm->phone2 = $post['phone2'];
		$frm->fax = $post['fax'];
		$frm->comment = $post['comment'];
		$frm->isdelete = "0";
		$frm->save();
	}

	public function UpdateData($post) {

	    

	    $fdoc = Doctrine::getTable('UsersLocations')->find($post['hiddedtid']);
	    if($fdoc){
    		$fdoc->first_name = $post['fname'];
    		$fdoc->last_name = $post['lname'];
    		$fdoc->company_name = $post['companyname'];
    		$fdoc->street = $post['street'];
    		$fdoc->zip = $post['zip'];
    		$fdoc->city = $post['city'];
    		$fdoc->phone1 = $post['phone1'];
    		$fdoc->phone2 = $post['phone2'];
    		$fdoc->fax = $post['fax'];
    		$fdoc->comment = $post['comment'];
	        $fdoc->save();
	    }
	}
}

?>