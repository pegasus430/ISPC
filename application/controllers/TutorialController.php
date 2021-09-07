<?php

/**
 * Class TutorialController
 * ISPC-2562, elena, 24.08.2020 page for videos and files
 * Maria:: Migration CISPC to ISPC 02.09.2020
 */
class TutorialController extends Pms_Controller_Action {
    //permitted mime types, extend if needed
    public $mime_types = [
    'image/gif', 'image/jpeg', 'image/png',
    'application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'video/mpeg', 'video/mp4', 'application/mp4', 'video/quicktime'
    ];
    public function init()
    {
        /* Initialize action controller here */
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if(!$logininfo->clientid)
        {
            //redir to select client error
            $this->_redirect(APP_BASE . "error/noclient");
            exit;
        }
    }

    public function fileuploadAction(){

        $max_post = ini_get('post_max_size');

        $max_upload = ini_get('upload_max_filesize');
        $this->_helper->layout->setLayout('layout_ajax');
        //echo $max_upload;
        $this->view->max_post = $max_post;
        $this->view->max_upload = $max_upload;
        $this->view->message = '';
        $this->view->uploaderror = '';

       if(intval($_POST['tutorial']['sent']) == 1){

        $post = $this->getRequest()->getPost('tutorial', null);

        $uploaddir =  PUBLIC_PATH .'/tutorialfiles/';

        $uploadfile = $uploaddir . '/' . basename($_FILES['tutorial_file']['name']);
        $description = $post['tutorial_description'];
        $tutorialFile = new TutorialFile();
        $tutorialFile->file_name = $_FILES['tutorial_file']['name'];
        $tutorialFile->file_foldername =  $_FILES['tutorial_file']['name'];
        $tutorialFile->file_description = $description;
        $tutorialFile->mime_type = $_FILES['tutorial_file']['type'];

        $mimetype = mime_content_type($_FILES['tutorial_file']['tmp_name']);
        //check whether mime type permitted
        if(in_array($mimetype, $this->mime_types))   {
            if (move_uploaded_file($_FILES['tutorial_file']['tmp_name'], $uploadfile)) {
                //$this->view->message = 'Die Datei wurde erfolgreich hochgeladen';
                $tutorialFile->save();
                echo '<script>
                    alert("Die Datei wurde erfolgreich hochgeladen");
                     window.location.href = "' . APP_BASE . '/tutorial/fileupload";
 
                    </script>' ;


            } else {
                //echo "Möglicherweise eine Dateiupload-Attacke!\n";
                $this->view->uploaderror = $this->translate('upload_error'); //'Die Datei wurde nicht hochgeladen';

            }

        }else{
            $this->view->uploaderror = $this->translate('upload_error_mime'); //'Unzulässiges Dateityp';
        }

       }

    }

    /**
     * edits tutorial
     *
     * @throws Doctrine_Connection_Exception
     */
    public function editAction(){
        $max_post = ini_get('post_max_size');

        $max_upload = ini_get('upload_max_filesize');
        $this->view->max_post = $max_post;
        $this->view->max_upload = $max_upload;

        $cmsPage = new CmsPage();
        $cmsPage->page_name = 'tutorial_page';
        $page_content = '';
        $page_data = $cmsPage->getLastVersion($cmsPage->page_name);

        if(is_array($page_data) && count($page_data) >0 && isset($page_data['page_content'])){
            $page_content = $page_data['page_content'];
            $cmsPage->id = $page_data['id'];
        }
        $this->view->page_content = $page_content;
        if(isset($_POST['tutorial']['content'])  && strlen(trim($_POST['tutorial']['content'])) > 0 ){
            $post = $this->getRequest()->getPost('tutorial', null);
            //print_r($post);
            $cmsPage->page_content = $post['content'];
            if($cmsPage->id > 0){
                $cmsPage->replace();
            }else{
                $cmsPage->save();
            }
            $this->view->page_content = $post['content'];


        }

    }

    /**
     * shows uploaded files, with description an upload date/time (tinymce url dialog)
     */
    public function filelistAction(){
       //$this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');
        $tutFilesQ = Doctrine_Query::create()
            ->select('*')
            ->from('TutorialFile')
            ->orderBy('id DESC');

        $tutFiles = $tutFilesQ->fetchArray();
        $this->view->tutorialfiles = $tutFiles;

    }

    /**
     * shows tutorial
     */
    public function showAction(){

        $cmsPage = new CmsPage();
        $cmsPage->page_name = 'tutorial_page';
        $page_data = $cmsPage->getLastVersion($cmsPage->page_name);
        //print_r($page_data);
        $this->view->page_data = $page_data;

    }


}

?>