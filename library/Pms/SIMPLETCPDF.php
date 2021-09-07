<?php

	/* PDF generating class, agregating all features, uses TcPDF */

	set_time_limit(0);

	/**
	 * main configuration file
	 */
	require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/config/tcpdf_config.php');
	require_once(APPLICATION_PATH . '/../library/Pms/TCPDF/tcpdf.php');

	class Pms_simpletcpdf extends TCPDF {

		public function Header()
		{
			
		}

		public function Footer()
		{
			
		}

		public function upload_pdf($file_data = false)
		{
			if(strlen($file_data['pdfname']) > '0' && strlen($file_data['password']) > '0')
			{
				$pdfname = $file_data['pdfname'];
				$zip_password = $file_data['password'];

				$tmpstmp = $this->uniqfolder(PDF_PATH);
				$file_name_real = basename($tmpstmp);

				$this->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
				$pdf_filename = $tmpstmp . '/' . $pdfname . '.pdf';

				$cmd = "zip -9 -r -P " . $zip_password . " uploads/" . $tmpstmp . ".zip " . "uploads/" . $tmpstmp . "; rm -r " . PDF_PATH . "/" . $tmpstmp;

				exec($cmd);
				$zipname = $file_name_real . ".zip";
				$filename = "uploads/" . $file_name_real . ".zip";

				/* $con_id = Pms_FtpFileupload::ftpconnect();
				if($con_id)
				{
					$upload = Pms_FtpFileupload::fileupload($con_id, PDF_PATH . "/" . $zipname, $filename);
					Pms_FtpFileupload::ftpconclose($con_id);
				} */

				//this file is allready zipped
				$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH . "/" . $zipname , "uploads" ,
						array(
								"is_zipped" => true,
								"file_name" => $pdf_filename,
								"insert_id" => NULL,
								"db_table"	=> "PatientFileUpload",
						));
				
				$return_data['pdf_filename'] = $pdf_filename;
				$return_data['zip_filename'] = $filename;

				return $return_data;
			}
			else
			{
				return false;
			}
		}

		public function uniqfolder($path)
		{
			$i = 0;
			$dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
			while(!is_dir($path . '/' . $dir))
			{
				$dir = substr(md5(rand(1, 9999) . microtime()), 0, 10);
				mkdir($path . '/' . $dir);
				if($i >= 50)
				{
					exit; //failsafe
				}
				$i++;
			}

			return $dir;
		}

		public function toFile($path)
		{
			$this->Output($path, 'F');
		}

		public function toBrowser($title, $style = 'I')
		{
			$this->Output($title . '.pdf', $style);
		}

	}

?>