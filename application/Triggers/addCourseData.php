<?
// require_once 'Pms/Triggers.php';

class application_Triggers_addCourseData extends Pms_Triggers{

	public function createFormPatient()
	{
		return $this->view->render("trigger/formtriggerinputs/patientAdd/addCourseData.html");
	}

	public function triggeraddCourseData($event,$inputs,$fieldname,$fieldid,$eventid,$gpost)
	{
		$ipid= $event->getInvoker()->ipid;
		$isstandby= $gpost['isstandby'];

		$locationid= $gpost['location_id'];
		$ltyp = new Locations();
		$loctype = $ltyp->getLocationbyId($locationid);
		$locationtype = $loctype[0]['location_type'];
        
		$course_type = $inputs['course_type'];
		$course_title = $inputs['course_title'];

       
		if($locationtype == '2' && !$isstandby) { // default "L" entry for patients with location status hospiz

			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->course_type=Pms_CommonData::aesEncrypt('L');
			$cust->course_title=Pms_CommonData::aesEncrypt('92011');
			$cust->user_id = $userid;
			$cust->save();
				
		} elseif($course_type != 'L' || !$isstandby) { //no "L" default entry for standby patients

			$logininfo= new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s",time());
			$cust->isstandby = $isstandby;
			$cust->course_type=Pms_CommonData::aesEncrypt($course_type);
			$cust->course_title=Pms_CommonData::aesEncrypt($course_title);
			$cust->user_id = $userid;
			$cust->save();
				
		}  else {
				
		}
	}
}
?>