<?php

// Maria:: Migration ISPC to CISPC 08.08.2020
class Net_EDIFACT_PKCS
{
    public $path="";
    public $filepath="";
    public $filename="";

    public $zipfile="";
    public $enccontent="";


    public function sign_message($msg, $filename, $cert, $privkey, $recipcert){
        $this->path =  PDF_PATH ."/". Pms_CommonData::uniqfolder(PDF_PATH, 'EDIFACTBILL');
        $this->filename=$filename;
        $filepath=$this->path."/".$filename;
        $this->filepath=$filepath;

        file_put_contents($filepath.".o", $msg);

        //openssl_pkcs7_encrypt($filepath.".o",$filepath.".e", $recipcert, array(), 0, OPENSSL_CIPHER_3DES );
        $recipcert=$recipcert->cert;
        openssl_pkcs7_encrypt($filepath.".o",$filepath.".e", $recipcert, array(), 0, 1 );

        openssl_pkcs7_sign ( $filepath.".e" , $filepath , $cert , $privkey , array() );

        if(!file_exists($filepath)){
            return false;
        }
        else{
            $this->enccontent=file_get_contents($filepath);
            return true;
        }
    }

    public function add_auffile($contents){
        file_put_contents($this->filepath.".AUF", $contents);
    }

    public function zip(){
        $cmd= "cd ".$this->path."; zip " . $this->filename.".zip ".$this->filename." ".$this->filename.".AUF";
        shell_exec($cmd);
        $zippath=$this->filepath.".zip";
        if(!file_exists($zippath)){
            return false;
        }
        else{
            $this->zipfile=file_get_contents($zippath);
            return true;
        }

    }

    public function to_browser(){
        $zippath=$this->filepath.".zip";
        $fp = fopen($zippath, 'rb');
        ob_end_clean();
        ob_start();
        header("Content-Type: application/zip");
        header("Content-Length: " . filesize($zippath));
        header('Content-Disposition: attachment; filename="'.$this->filename.'.zip"');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        fpassthru($fp);
        exit;
    }

}