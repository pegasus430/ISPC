<?php

exit;

require_once('_init/init.backend.php');
require_once(ABSPATH . '/_config/config.backend.php');

require_once (LIB_PATH . '/_backend/Patient.php');

require_once (LIB_PATH . '/_backend/Export.php');

require_once(LIB_PATH . '/_survey/SurveyTake.php');
require_once(LIB_PATH . '/_survey/painPoolSurveyTake.php');

$export = new Export();
$survey_data = new painPoolSurveyTake();
$patient = new Patient();


if($_REQUEST['chid'])
{
    if ($_REQUEST['show_skipped'] == 1) {
        $remove_skipped = false;
    } else {
        $remove_skipped = true;
    }

	$chain_details = $survey_data->chain_get_details($_REQUEST['chid']); //chain details
	
	if(!$chain_details) {
		quit(DOMAIN);
	} else {
		$patient_id = $chain_details['details']['patient'];
		$pat_det = $patient->get_patient($patient_id);
//		if($pat_det['practice'] != $_SESSION['user']['practice']) {
//			quit(DOMAIN);
//		}
	}
	
	if($chain_details['details']['dummy'] == 'yes') {
		$is_dummy = true;
	} else {
		$is_dummy = false;
	}
	

  if($_REQUEST['dbg_html'] == 1) {
      $htmlpdf = $export->chain_generate_mpdf($_REQUEST['chid'], $form = true, $is_dummy, $remove_skipped);
      $htmlpdf = $export->html_prepare_for_pdf($htmlpdf);
      /*$htmlpdfall ='
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml"><head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="'.TEMPLATE_WWWPATH.'/styles/pdf/print.css"><title></title></head><body>
			'.$htmlpdf.'
	</body></html>';*/

	  $htmlpdfall ='<!DOCTYPE html>
<html><head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="'.TEMPLATE_WWWPATH.'/styles/pdf/survey-print-mpdf.css" /><title></title></head><body>
			'.$htmlpdf.'
	</body></html>';
  	echo $htmlpdfall;
  	
  } else {
	  $export->generate_mpdfs_chain($_REQUEST['chid'], $_SESSION['user']['practice'], $is_dummy); // dompdf method
  }
}
?>
