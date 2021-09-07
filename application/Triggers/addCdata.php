<?
// require_once 'Pms/Triggers.php';

class application_Triggers_addCdata extends Pms_Triggers{



	public function createFormCourseDocumentation()
	{
		return $this->view->render("trigger/formtriggerinputs/CourseDocumentation/addCdata.html");
	}
}
?>