<?php
require_once('_init/init.backend.php');
require_once(ABSPATH . '/_config/config.backend.php');

require_once (LIB_PATH . '/_backend/Patient.php');

require_once (LIB_PATH . '/_backend/Export.php');
require_once (LIB_PATH . '/_backend/ExportISPC.php');

require_once(LIB_PATH . '/_survey/SurveyTake.php');
require_once(LIB_PATH . '/_survey/painPoolSurveyTake.php');

$export = new ExportISPC();


$pdf_disk_path = $export->generate_mpdfs_chain($chain_id, $patient_details, $outputfolder); 

?>
