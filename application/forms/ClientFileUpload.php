<?php
//require_once ("Pms/Form.php");

class Application_Form_ClientFileUpload extends Pms_Form
{

    public function validate($post)
    {
        $Tr = new Zend_View_Helper_Translate();
        
        $error = 0;
        $val = new Pms_Validation();
        
        if (strlen($_SESSION['filename']) < 1) {
            $this->error_message['filename'] = $Tr->translate("uploadcsvfile");
            $error = 2;
        }
        
        if ($error == 0) {
            return true;
        }
        
        return false;
    }

    /**
     *
     * @param array $post            
     * @throws Zend_Exception - this should be implemented ?
     * @return ClientFileUpload
     */
    public function InsertData($post = array())
    {
        if (! file_exists($_SESSION['zipname'])) {
            // at this time just log.. should be changed into : throw new Zend_Exception() ??
            $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/app.log');
            if ($log = new Zend_Log($writer)) {
                $log->crit('Saving in db a link to a file that does NOT exists ! ... download will fail');
            }
        }
        
        if (strlen($post['title']) < 1) {
            $fl = explode(".", $_SESSION['filetitle']);
            
            $post['title'] = $fl[0];
        }
        
        $clientid = isset($post['clientid']) ? $post['clientid'] : $this->logininfo->clientid;
        
        $filepass = isset($post['filepass']) ? $post['filepass'] : $this->logininfo->filepass;
        
        $cust = new ClientFileUpload();
        $cust->title = Pms_CommonData::aesEncrypt($post['title']);
        $cust->clientid = $clientid;
        $cust->file_name = Pms_CommonData::aesEncrypt(addslashes($_SESSION['filename'])); // $post['fileinfo']['filename']['name'];
        $cust->file_type = Pms_CommonData::aesEncrypt($post['filetype']);
        $cust->tabname = $post['tabname'];
        $cust->folder = ! empty($post['folder_id']) ? $post['folder_id'] : null;
        $cust->recordid = $post['recordid'];
        $cust->parent_id = ! empty($post['parent_id']) ? $post['parent_id'] : null;
        
        $cust->save();
        
        if ($cust->id) {
            $ftp_put_queue_result = Pms_CommonData::ftp_put_queue($_SESSION['zipname'], "clientuploads", array(
                "is_zipped" => true,
                "file_name" => $_SESSION['filename'],
                "insert_id" => $cust->id,
                "db_table" => "ClientFileUpload"
            ), $foster_file = false, $clientid, $filepass);
        }
        
        return $cust;
    }

    /**
     * update @cla on 18.04.2018
     * changed to delete files only from $this->logininfo->clientid || $clientid
     *
     * @param number $id            
     * @param string $tabname            
     * @param number $clientid            
     */
    public function deleteFile($id = 0, $tabname = false, $clientid = null)
    {
        if (empty($id)) {
            return;
        }
        
        $clientid = ! empty($clientid) ? $clientid : $this->logininfo->clientid;
        
        $cfu_obj = Doctrine::getTable('ClientFileUpload')->createQuery('del_file')
            ->update()
            ->set('isdeleted', 1)
            ->where('id = ?', $id)
            ->andWhere('clientid = ?', $clientid);
        
        if ($tabname) {
            $cfu_obj->andWhere('tabname = ?', $tabname);
        }
        
        $cfu_obj->execute();
    }
    
    /**
     * @cla on 23.04.2018 created for misc/uploadfiles, to replace this->InsertData
     * 
     * @param array $data
     * @return ClientFileUpload
     */
    public function InsertNewRecord( $data = array()) {
        
        
        $data_encrypted = Pms_CommonData::aesEncryptMultiple($data);
        
        $cust = new ClientFileUpload();
        
        $cust->title = $data_encrypted['title'];
        $cust->file_name = $data_encrypted['file_name'];
        $cust->file_type = $data_encrypted['file_type'];
        
        $cust->clientid = $data['clientid'];
        
        $cust->tabname = ! empty($data['tabname']) ? $data['tabname'] : null;
        $cust->folder = ! empty($data['folder']) ? $data['folder'] : null;
        $cust->recordid = ! empty($data['recordid']) ? $data['recordid'] : null;
        $cust->parent_id = ! empty($data['parent_id']) ? $data['parent_id'] : null;
        
        $cust->save();
        
        return $cust;
        
    }
    
}

?>