<?php

/**
 * Class SelectboxlistsController
 *
 * Administration of the Lists for the Modul "Team-Meeting" in ISPC Clinic.
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class SelectboxlistsController extends Zend_Controller_Action
{
    public $act;

    public function init()
    {

    }

    public function indexAction()
    {

        $listsmodel = new Selectboxlist();


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $listsmodel = new SelectboxlistPlangoal();
        $listsmodelsel = new Selectboxlist();

        if ($this->getRequest()->isPost()) {

            if ($_POST['plangoalentries']) {
                foreach ($_POST['plangoalentries'] as $rownr => $entry) {
                    $_POST['plangoalentries'][$rownr]['plan'] = explode("\n", $entry['plan']);
                }
                $listsmodel->replaceList('goalsandplans', $_POST['plangoalentries']);
            }

            if ($_POST['placesofdeathlist']) {
                $entries = array_filter($_POST['placesofdeathlist']);
                $listsmodelsel->replaceList('placesofdeathlist', $entries);
            }
        }

        $this->view->placesofdeathlist = $listsmodelsel->getListOrDefault('placesofdeathlist'); //read the list of places of death
        $this->view->list_goalsandplans = $listsmodel->getListOrDefault('goalsandplans');


        $this->view->professions_conf = Client::getClientconfig($clientid, 'lmutm_profsmap');
    }

    public function controldischargeplanningclinicAction()
    {


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $listsmodel = new Selectboxlist();

        if ($this->getRequest()->isPost()) {

            if ($_POST['supplieslist']) {
                $entries = array_filter($_POST['supplieslist']);
                $listsmodel->replaceList('supplieslist', $entries);
            }

            if ($_POST['contactlist']) {
                $contact_list = array();
                foreach ($_POST['contactlist'] as $entries => $value) {
                    if ($value[0] == 'CHOOSE_CONTACT')
                        continue;
                    $contact_list[] = $value;
                }
                if (count($contact_list) > 0)
                    $listsmodel->replaceList('contactlist', $contact_list);
            }

            if ($_POST['dischargelist']) {
                $dischargelist = array();
                foreach ($_POST['dischargelist'] as $entries => $value) {
                    if (empty($value[0]))
                        continue;
                    $value = preg_replace("/[\r\n]+/", "\r\n", $value); //remove duplicate
                    $value = preg_replace("/^\r\n/", "", $value); //remove first
                    $value = preg_replace("/\r\n$/", "", $value); //remove last
                    $dischargelist[] = $value;
                }
                if (count(dischargelist) > 0)
                    $listsmodel->replaceList('dischargelist', $dischargelist);
            }
        }

        $versorger = new ClinicVersorger();

        $this->view->supplieslist = $listsmodel->getListOrDefault('supplieslist'); //read the list of supplies
        $this->view->contactlist = $listsmodel->getList('contactlist', true); //read the list of contacts
        $this->view->dischargelist = $listsmodel->getListOrDefault('dischargelist', true); //read the list of contacts
        $this->view->versorgerlist = $versorger->getCategoriesSortByLabel();

    }

    public function controlcareprocessclinicAction()
    {


        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        if ($this->getRequest()->isPost()) {

            if ($_POST['careprocesslist']) {
                ClientConfig::saveConfig($clientid, 'careprocesslist', $_POST['careprocesslist']);
            }

        }


        // read the client-config
        $this->view->careprocesslist = ClientConfig::getConfigOrDefault($clientid, 'careprocesslist');
    }

    public function controlcontactformblockclinicAction()
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        //save the changes
        if ($this->getRequest()->isPost()) {
            if ($_POST['configjob']) { //IM-47
                $config = $_POST['configjob'];
                $config['items'] = array_filter($config['items']);
                ClientConfig::saveConfig($clientid, 'configjob', $config);
            }

            if ($_POST['configdepression']) { //IM-47
                $config = $_POST['configdepression'];
                $config= array_filter($config);
                ClientConfig::saveConfig($clientid, 'configdepression', $config);
            }
            if ($_POST['configtalkwith']) { //IM-56
                $config = $_POST['configtalkwith'];
                $config= array_filter($config);
                ClientConfig::saveConfig($clientid, 'configtalkwith', $config);
            }
            //Start TODO-4163
            if ($_POST['config_pcoc']) { //IM-147
                $config = $_POST['config_pcoc'];
                $config= array_filter($config);
                ClientConfig::saveConfig($clientid, 'config_pcoc', $config);
            }
            if ($_POST['config_pcoc_full']) { //IM-147
                $config = $_POST['config_pcoc_full'];
                $config= array_filter($config);
                ClientConfig::saveConfig($clientid, 'config_pcoc_full', $config);
            }
            if ($_POST['config_pcoc_charticon']) { //IM-147
                $config = $_POST['config_pcoc_charticon'];
                $config= array_filter($config);
                ClientConfig::saveConfig($clientid, 'config_pcoc_charticon', $config);
            }
            if ($_POST['config_pcoc_chartmode']) {
                $config = $_POST['config_pcoc_chartmode'];
                $config= array_filter($config);
                ClientConfig::saveConfig($clientid, 'config_pcoc_chartmode', $config);
            }
            if(isset($_POST['config_pcoc_required'])){
                $config = $_POST['config_pcoc_required'];
                ClientConfig::saveConfig($clientid, 'config_pcoc_required', $config);
            }
            //END TODO-4163
        }

        $system_tokens = array();

        $shortcutsSQL = Doctrine_Query::create()
            ->select('*')
            ->from('Courseshortcuts')
            ->where('isdelete=0')
            ->andWhere('clientid=?', $clientid)
            ->orderBy('shortcut ASC');

        $shortcuts = $shortcutsSQL->fetchArray();
        foreach ($shortcuts as $shortcut) {
            $system_tokens[$shortcut['shortcut_id']] = $shortcut['shortcut'] . ' - ' . $shortcut['course_fullname'];
        }



        $this->view->config_job = ClientConfig::getConfigOrDefault($clientid, 'configjob'); //IM-47 - Konfiguration for the 'Job-Background'
        $this->view->config_depression = ClientConfig::getConfigOrDefault($clientid, 'configdepression'); //IM-51 - Konfiguration for the 'Screening for Depression'
        $this->view->system_tokens = $system_tokens;
        $this->view->config_talkwith = ClientConfig::getConfigOrDefault($clientid, 'configtalkwith'); //IM-56- Konfiguration for the 'Talk with'


        //Start TODO-4163
        //IM-147 start
        $blockformsSQL = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlocks2Type')
            ->where('form_block=?','pcoc')
            ->andWhere('clientid=?', $clientid);

        $blockforms = $blockformsSQL->fetchArray();
        $blockforms=array_column($blockforms, 'form_type');

        $formsSQL = Doctrine_Query::create()
            ->select('*')
            ->from('FormTypes')
            ->andWhere('clientid=?', $clientid)
            ->andWhere('isdelete=0')
            ->andWhereIn('id', $blockforms);
        $forms=$formsSQL->fetchArray();

        $pcoc_forms=Array();
        foreach ($forms as $form){
            $pcoc_forms[$form['id']]=$form['name'];
        }
        $this->view->pcoc_forms=$pcoc_forms;
        $this->view->config_pcoc = ClientConfig::getConfigOrDefault($clientid, 'config_pcoc'); //IM-147- Konfiguration for PCOC Block
        $this->view->config_pcoc_full = ClientConfig::getConfigOrDefault($clientid, 'config_pcoc_full'); //IM-147- Konfiguration for PCOC Block
        $this->view->config_pcoc_required = ClientConfig::getConfigOrDefault($clientid, 'config_pcoc_required'); //IM-147- Konfiguration for PCOC Block
        //$this->view->config_pcoc_allchart = ClientConfig::getConfigOrDefault($clientid, 'config_pcoc_allchart'); //IM-147- Konfiguration for PCOC Block

        //IM-147 end

        $this->view->config_pcoc_charticon = ClientConfig::getConfigOrDefault($clientid, 'config_pcoc_charticon');
        $this->view->config_pcoc_chartmode = ClientConfig::getConfigOrDefault($clientid, 'config_pcoc_chartmode');
        //END TODO-4163
    }

    public function controltalkformblockclinicAction()
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $form_types = new FormTypes();
        $form_types=$form_types->get_form_types($clientid);

        $form_types_blocks = new FormBlocks2Type();

        $forms=array();
        foreach($form_types as $form){
            $saved_blocks = $form_types_blocks->get_form_types_blocks($clientid, $form['id']);
            foreach($saved_blocks as $block){
                if ($block['form_block']=="talkcontent"){
                    $forms[]=$form;
                }
            }
        }


        if($_POST){
            $post=json_decode($_POST['data'],1);
            ClientConfig::saveConfig($clientid, 'configtalkcontent', $post);
            die('OK');
        }


        $savedconfig=ClientConfig::getConfig($clientid, 'configtalkcontent');
        $config=array();
        $groups=array();
        foreach ($savedconfig as $conf){
            $config[]=$conf;
            foreach ($conf['visible'] as $group){
                if(!in_array($group, $groups)) {
                    $groups[] = $group;
                }
            }
        }

        //blank entries at the end
        $groups[]="";
        $config[]=array('label'=>'','is_headline'=>'','is_freetext'=>'','visible'=>array());


        //$this->view->config=ClientConfig::getConfig($clientid, 'configtalkcontent');


        $this->view->forms=$forms;
        $this->view->config=$config;
        $this->view->groups=$groups;

    }

 	/**
     * ISPC-2539, elena, 23.10.2020
     */
    public function verordnungoptionsAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        //$this->_helper->layout->setLayout('layout_ajax');
        $userid = $logininfo->userid;

        if(!$clientid || !$userid){
            die();
        }

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost('option');
            $retValue = [];
            for($i=0;$i<count($post['name']);$i++){

                $option = [];
                $option['name'] = $post['name'][$i];
                $option['color'] = $post['color'][$i];

                $retValue[] = $option;
            }

            ClientConfig::saveConfig($clientid, 'verordnungoptions', json_encode($retValue));
            $this->_redirect(APP_BASE . 'selectboxlists/verordnungoptions');
        }


        $savedoptions=ClientConfig::getConfig($clientid, 'verordnungoptions');

        $this->view->options = json_decode($savedoptions);

    }


    /**
     * ISPC-2697, Elena, 30.10.2020
     */
    public function beatmungsmachineAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        //$this->_helper->layout->setLayout('layout_ajax');
        $userid = $logininfo->userid;



        if(!$clientid || !$userid){
            die();
        }
        $retValue = [];

        $oMachine = new Machine();
        $machines =$oMachine->getClientMachinesForType($clientid, 'beatmung');

        $machine = Doctrine::getTable('Machine')->find($_GET['opt']);


        $this->view->machines = $machines;
        $this->view->machine = $machine;

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            //print_r($post);
            //die();
            if(isset($post['remove_set'])){
                $set_to_remove = intval($post['remove_set']);

                $sets = [];
                if(isset($machine->parameters)){
                    //ISPC-2843,Elena,26.02.2020
                    $params = json_decode($machine->parameters, true);

                    //ISPC-2843,Elena,26.02.2020
                    for($i=0;$i<count($params);$i++){
                        if($i !=  $set_to_remove){
                            //ISPC-2843,Elena,26.02.2020
                            $sets[] = $params[$i];
                        }
                    }

                }
                //ISPC-2843,Elena,26.02.2020
                $machine->parameters = json_encode($sets);


                $machine->replace();
                $this->_redirect(APP_BASE . 'selectboxlists/beatmungsmachine?opt=' . $_GET['opt']);

            }elseif(isset($post['machine_name_save'])){

                $machine->machine_name = $post['machine_name_save'];
                $machine->replace();

                $this->_redirect(APP_BASE . 'selectboxlists/beatmungsmachine?opt=' . $_GET['opt']);

            }
            else{
                $optionnumber = $post['optionnumber'];
                $machine_number = $_GET['opt'];

                $sets = [];
                $set = [];
                $set['label'] = $post['label'];
                $set['list'] = $post['list'];
                $set['unit'] = $post['unit'];
                $set['has_alarm'] = $post['has_alarm'];
                $set['alarm_higher'] = $post['alarm_higher_result'];
                $set['alarm_lower'] = $post['alarm_lower_result'];
                $set['has_emergency'] = $post['has_emergency'];
                $set['value'] = $post['value'];
                $set['validation'] = $post['validation'];
                if(intval($set['has_emergency']) == 1){
                    $set['emergency'] = $post['emergency_result'];
                }
                $sets = json_decode($machine->parameters, true);


                //$sets = $machine['sets'];
                for($i=0;$i<count($sets);$i++){
                    if($i == $optionnumber){
                        $sets[$i] = $set;
                    }
                }
                if($optionnumber == -1){
                    $sets[] = $set;
                }

                $machine->parameters = json_encode($sets);
                $machine->replace();



                $this->_redirect(APP_BASE . 'selectboxlists/beatmungsmachine?opt=' . $_GET['opt']);


            }

        }


        //$machines = json_decode(ClientConfig::getConfig($clientid, 'beatmung'), true);
        //print_r($beatmung);

        //$this->view->machines = $machines;

    }

    /**
     * ISPC-2697, Elena, 10.11.2020
     *
     * @throws Exception
     */
    public function beatmungAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        //$this->_helper->layout->setLayout('layout_ajax');
        $userid = $logininfo->userid;
        $oMachine = new Machine();
        //print_r($oMachine->getSql());

        if(!$clientid || !$userid){
            die();
        }

        $machines =$oMachine->getClientMachinesForType($clientid, 'beatmung');

        $other_machines = $oMachine->getClientMachinesWithoutType($clientid, 'beatmung');

        //$this->view->machines = $machines;
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if(isset($post['newmachine'])){
                $newsets = [];
                $cust = new Machine();
                $cust->clientid = $clientid;
                if(!isset($post['mtype'])){
                    $cust->machine_type = 'beatmung';
                }else{
                    $cust->machine_type = $post['mtype'];
                }

                $cust->machine_name = $post['newmachine'];
                $cust->parameters = json_encode($newsets);

                $cust->save();
                $this->_redirect(APP_BASE . 'selectboxlists/beatmung');
            }
        }
        $this->view->machines = $machines;
        $this->view->other_machines = $other_machines;
        $this->view->mtypes = $oMachine->getTypes();


    }


    /**
     * IM-162,elena,10.12.2020
     */
    public function meetingtextblocksAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        //$this->_helper->layout->setLayout('layout_ajax');
        $userid = $logininfo->userid;

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost('textblocks');


            ClientConfig::saveConfig($clientid, 'meetingtextblocks', json_encode($post));
            $this->_redirect(APP_BASE . 'selectboxlists/meetingtextblocks');
        }


        $savedoptions=ClientConfig::getConfig($clientid, 'meetingtextblocks');

        $this->view->blocks = json_decode($savedoptions);

    }

    /**
     * ISPC-2903,Elena,26.04.2021
     */
    public function chartsettingsAction(){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost('chartsettings');
            ClientConfig::saveConfig($clientid, 'chartsettings', json_encode($post));

            //ISPC-2901
            $post = $this->getRequest()->getPost('collapsed');
            ClientConfig::saveConfig($clientid, 'charts_items_collapsed', json_encode($post));

        }

        $savedoptions=ClientConfig::getConfig($clientid, 'chartsettings');
        $this->view->chartsettings = json_decode($savedoptions);

        $savedoptions=ClientConfig::getConfig($clientid, 'charts_items_collapsed');
        $this->view->collapsed = json_decode($savedoptions);
    }

}

?>
