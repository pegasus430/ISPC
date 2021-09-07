<?php

// require_once 'Pms/Triggers.php';
	class application_Triggers_addValuetoToSapvfb3 extends Pms_Triggers {

		public function triggeraddValuetoToSapvfb3($event, $inputs, $fieldname, $fieldid, $eventid, $gpost)
		{
			

			if($fieldname == "course_type" && isset($_POST["course_title"]))
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
			
				$course_type = $event->getinvoker()->course_type;
				$course_type = Pms_CommonData::aesDecrypt($course_type);
				$patient_course_id = $event->getinvoker()->id;

				if($course_type == 'XT' || $course_type == 'V' && empty($event->getinvoker()->source_ipid)) //Telefonat or Koordonation
				{

					switch($course_type)
					{
						case 'XT':
							$course_title = explode('|', Pms_CommonData::aesDecrypt($event->getinvoker()->course_title));
							$course_data = $course_title;

							$ipid = $event->getinvoker()->ipid;
							$call_date = $course_data[2];
							$telefonat = '6';
							$minutes = $course_data[0];

							$sp = new Sapsymptom();
							$sp->ipid = $ipid;
							$sp->sapvalues = $telefonat;
							$sp->gesamt_zeit_in_minuten = $minutes;
							$sp->save();

							$idsapsym = $sp->id;

							$sp = Doctrine::getTable('Sapsymptom')->find($idsapsym);
							$sp->create_date = date('Y-m-d H:i:00', strtotime($call_date)); // add also the minute field
							$sp->patient_course_id = $patient_course_id;
							$sp->save();
							break;
							//ISPC-2163+2374
						/* case 'V':
							$course_title = explode('|', Pms_CommonData::aesDecrypt($event->getinvoker()->course_title));
							$course_data = $course_title;
							
							$ipid = $event->getinvoker()->ipid;
							$call_date = date('Y-m-d H:i:00', strtotime($course_data[2]));
							$minutes = $course_data[0];

							$sp = new Sapsymptom();
							$sp->ipid = $ipid;
							//$sp->sapvalues = '999';
							$sp->sapvalues = '8,999'; // ISPC-2163 Carmen 29.03.2019
							$sp->gesamt_zeit_in_minuten = $minutes;
							$sp->patient_course_id = $patient_course_id;
							$sp->save();
							
							if($sp->id)
							{
								$sp = Doctrine::getTable('Sapsymptom')->find($sp->id);
								$sp->create_date = $call_date; 
								$sp->save();
							}

							break; */


						default:
							break;
					}
				}
			}
		}

	}

?>
