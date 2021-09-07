<?
//Maria:: Migration CISPC to ISPC 22.07.2020
class Adminhl7Controller extends Zend_Controller_Action
{
	public function init()
	{
        $return=0;
        $logininfo= new Zend_Session_Namespace('Login_Info');

        $this->clientid=$logininfo->clientid;
        if($logininfo->usertype=='SA' || $logininfo->usertype=='CA')
        {
            $return=1;
        }

        if(!$return)
        {
            $this->_redirect(APP_BASE."error/previlege");
        }
	}



    public function socketadminAction(){
        $this->view->clientid=$this->clientid;
        $procs_list=Net_ProcessHL7::manage_server('monitor',['clientid'=>$this->clientid]);
        $this->view->procmon=implode("\n",$procs_list);

        if(isset($_POST) && isset($_POST['reset'])){
            $o=Net_ProcessHL7::manage_server('start',['clientid'=>$this->clientid]);
            if($o=="OK"){
                $this->view->message="Der Server ist in wenigen Minuten empfangsbereit.";
            }else{
                $this->view->message="FEHLER:".$o;
            }
        }

        $logfind = Doctrine_Query::create()
            ->select('id, message, level, date')
            ->from('Hl7Log')
            ->orderby('date desc')
            ->limit(2000);
        $log_rows= $logfind->fetchArray();

        $this->view->last_adt = "vor mehr als 10 Tagen!";
        $this->view->last_orm = "vor mehr als 10 Tagen!";

        foreach ($log_rows as $entry){
            $dec =Pms_CommonData::aesDecrypt($entry['message']);
            $msg = ($dec) ? $dec: $entry['message'];
            if (strpos($msg,"|ADT^")>1){
                $this->view->last_adt=date("d.m.Y H:i",strtotime($entry['date']));
                break;
            }
        }
        foreach ($log_rows as $entry){
            $dec =Pms_CommonData::aesDecrypt($entry['message']);
            $msg = ($dec) ? $dec: $entry['message'];
            if (strpos($msg,"|ORM^")>1){
                $this->view->last_orm=date("d.m.Y H:i",strtotime($entry['date']));
                break;
            }
        }

    }

	public function socketlogAction()
	{
		
		$start=strtotime ("now - 1 day");
		$end=strtotime ("now + 1 day");

		if (isset($_GET["start"]) && isset($_GET["end"])) {
			$start	=strtotime ($_GET["start"]);
			$end = strtotime ($_GET["end"]);
			}	
		$start = strftime("%Y-%m-%d %H:%M", $start);
		$end = strftime("%Y-%m-%d %H:%M", $end);
	
		$this->view->starttime = $start;
		$this->view->endtime = $end;	
		
		$logfind = Doctrine_Query::create()
			->select('id, message, level, date')
			->from('Hl7Log')
			->where("date between '". $start ."' and '" . $end . "'")
			->orderby('date desc');
		$log_rows= $logfind->fetchArray();



		
		$this->view->historyrows = array();

		foreach ($log_rows as $entry){
			$dec =Pms_CommonData::aesDecrypt($entry['message']);
			$msg = ($dec) ? $dec: $entry['message'];
			$this->view->historyrows[] = array('id'=>$entry['id'], 'level'=>$entry['level'], 'message'=>$msg, 'date'=>$entry['date']);
			}

		}

    public function socketsimAction(){

        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid=$logininfo->clientid;
        if ($_POST){
            $conf = array (
                //the clientid for this server
                'clientid'		=>	$clientid,
                //the userid this server has
                'userid'		=>	1,
                //everything is logged to db is encrypted if true
                'encryptlog'	=>  false,
                //0=print all, 2 print only errors
                'verbosity' 	=>	0,
                //we dont have real productivity data: mark patient as testpatient
                'testdata'      =>  1
            );

            Net_ProcessHL7::process_message($_POST['data'],$conf);

            $this->view->caseno=$_POST['caseno'];
            $this->view->patfname=$_POST['patfname'];
            $this->view->patlname=$_POST['patlname'];
            $this->view->patno=$_POST['patno'];
            $this->view->birth=$_POST['birth'];
        }

    }


    public function opsconfigAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $hardcoded_ops_groupnames=array(
            'Arzt',
            'Pflege',
            'Sozialarbeit',
            'Atemtherapie',
            'Psychologie',
            'Krankengymnastik',
            'Apotheke',
            'Seelsorge'
        );

        $this->view->hardcoded_ops_groupnames=$hardcoded_ops_groupnames;

        $usergroups=Usergroup::getClientGroupsMap($clientid);
        $this->view->usergroups=$usergroups;


        if($_POST){
            $newconfig=array();
            $newconfig['groups']=array();
            foreach ($hardcoded_ops_groupnames as $groupname){
                $newconfig['groups'][$groupname] = $_POST['groups_' . $groupname];
            }

            foreach ($_POST['groups_custom_names'] as $k=>$v){
                $newconfig['groupscustom'][$v]=$_POST['groups_custom_' . $k];
            }

            foreach ($_POST['internal_oe'] as $k=>$v){
                $newconfig['internal_oe'][$k]=$v;
            }
            $oe_items=array(
                'ops_oe_ambulant_erb1',
                'ops_oe_ambulant_erb2',
                'ops_oe_ambulant_anf1',
                'ops_oe_ambulant_anf2',
                'ops_oe_station_erb1',
                'ops_oe_station_erb2',
                'ops_oe_station_anf1',
                'ops_oe_station_anf2',
                'ops_oe_konsil_erb1',
                'ops_oe_konsil_erb2',
                'ops_oe_konsil_anf1',
                'ops_oe_konsil_anf2',
                'ops_oe_sapv_erb1',
                'ops_oe_sapv_erb2',
                'ops_oe_sapv_anf1',
                'ops_oe_sapv_anf2',
                'ops_oe_station_klau',
                'ops_oe_konsil_klau',
                'ops_oe_ambulant_klau',
                'ops_oe_sapv_klau',
                'ba_formid',
                'ops_prefcode_station',
                'ops_prefcode_konsil',
                'ops_prefcode_ambulant',
                'ops_prefcode_sapv',
                'all_times_internal'
            );

            foreach ($oe_items as $opt){
                $newconfig[$opt]="";
                if(isset($_POST[$opt])){
                    $newconfig[$opt]=$_POST[$opt];
                }
            }

            $newconfig['codes']=array();
            $opts=array(
                'ops_includet_patient',
                'ops_includet_angeh',
                'ops_includet_prof',
                'ops_includet_sys',
                'ops_only_internal',
            );

            $cases=array(
                'ops_case_station',
                'ops_case_konsil',
                'ops_case_ambulant',
                'ops_case_sapv'
            );



            foreach ($_POST['ops_name'] as $opsk=>$opsname){
                if($opsname!=""){
                    $opsconf=array();
                    $opsconf['name']=$opsname;
                    $opsconf['goal_groups']=$_POST['ops_goal_groups'][$opsk];
                    $opsconf['goal_mins']=$_POST['ops_goal_mins'][$opsk];
                    $opsconf['joined_groups']=$_POST['ops_joined_groups'][$opsk];
                    $opsconf['ignored_groups']=$_POST['ops_ignored_groups'][$opsk];
                    $opsconf['ops_computetype']=$_POST['ops_computetype'][$opsk];

                    foreach ($opts as $opt){
                        $opsconf[$opt]=false;
                        if(isset($_POST[$opt][$opsk])){
                            $opsconf[$opt]=true;
                        }
                    }

                    foreach ($cases as $opt){
                        $opsconf[$opt]=false;
                        if(isset($_POST[$opt][$opsk])){
                            $opsconf[$opt]=true;
                        }
                    }


                    $minsmap=array();

                    foreach ($_POST['ops_code_code'][$opsk] as $min_k=>$min_name){
                        if($min_name!="") {
                            $minsmap[] = array('name' => $min_name, 'mins' => $_POST['ops_code_mins'][$opsk][$min_k]);
                        }
                    }

                    $opsconf['minutes']=$minsmap;

                    $newconfig['codes'][]=$opsconf;
                }

            }
            if(count($newconfig['codes'])<1){
                $newconfig=array('name'=>'', 'goal_groups'=>'');
                foreach ($opts as $opt){
                    $opsconf[$opt]=false;
                }
                foreach ($cases as $opt){
                    $opsconf[$opt]=false;
                }
                $newconfig['codes'][]=$newconfig;
            }

            ClientConfig::saveConfig($clientid, 'opsconfig', $newconfig);
            if($_GET['load_default']){
                $this->_redirect(APP_BASE . "adminhl7/opsconfig");

            }
        }


        if($_GET['load_default']){
            $default='{"internal_oe":["PAGL23","","",""],"ops_oe_ambulant_erb1":"a1","ops_oe_ambulant_erb2":"a2","ops_oe_ambulant_anf1":"a3","ops_oe_ambulant_anf2":"a4","ops_oe_station_erb1":"PAGL23","ops_oe_station_erb2":"PAAL","ops_oe_station_anf1":"s3","ops_oe_station_anf2":"s4","ops_oe_konsil_erb1":"p1","ops_oe_konsil_erb2":"p2","ops_oe_konsil_anf1":"p3","ops_oe_konsil_anf2":"p4","ops_oe_sapv_erb1":"v1","ops_oe_sapv_erb2":"v2","ops_oe_sapv_anf1":"v3","ops_oe_sapv_anf2":"v4","ops_oe_station_klau":"","ops_oe_konsil_klau":"1","ops_oe_ambulant_klau":"1","ops_oe_sapv_klau":"","codes":[{"name":"8-98e","goal_groups":"3","goal_mins":"360","joined_groups":["Arzt","Pflege",""],"ignored_groups":[""],"ops_computetype":"weekly","ops_includet_patient":true,"ops_includet_angeh":true,"ops_includet_prof":true,"ops_includet_sys":true,"ops_only_internal":false,"ops_case_station":true,"ops_case_konsil":false,"ops_case_ambulant":false,"ops_case_sapv":false,"minutes":[{"name":"8-98e.0","mins":"0"},{"name":"8-98e.1","mins":"1"},{"name":"8-98e.2","mins":"2"},{"name":"8-98e.3","mins":"3"},{"name":"8-98e.4","mins":"4"},{"name":"8-98e.5","mins":"5"},{"name":"8-98e.6","mins":"6"},{"name":"8-98e.7","mins":"7"},{"name":"8-98e.8","mins":"8"},{"name":"8-98e.9","mins":"9"}]},{"name":"8-98h.0","goal_groups":"1","goal_mins":"560","joined_groups":[""],"ignored_groups":["Seelsorge","Apotheke","Atemtherapie",""],"ops_computetype":"sum_only","ops_includet_patient":true,"ops_includet_angeh":true,"ops_includet_prof":false,"ops_includet_sys":false,"ops_only_internal":false,"ops_case_station":false,"ops_case_konsil":true,"ops_case_ambulant":false,"ops_case_sapv":false,"minutes":[{"name":"8-98h.00","mins":"0"},{"name":"8-98h.01","mins":"120"},{"name":"8-98h.02","mins":"240"},{"name":"8-98h.03","mins":"360"},{"name":"8-98h.04","mins":"540"},{"name":"8-98h.05","mins":"720"},{"name":"8-98h.06","mins":"900"},{"name":"8-98h.07","mins":"1200"},{"name":"8-98h.08","mins":"1500"},{"name":"8-98h.09","mins":"2100"},{"name":"8-98h.0a","mins":"2700"},{"name":"8-98h.0b","mins":"3300"}]},{"name":"1-774","goal_groups":"0","goal_mins":"360","joined_groups":[""],"ignored_groups":[""],"ops_computetype":"sum_only","ops_includet_patient":true,"ops_includet_angeh":true,"ops_includet_prof":true,"ops_includet_sys":true,"ops_only_internal":true,"ops_case_station":true,"ops_case_konsil":true,"ops_case_ambulant":true,"ops_case_sapv":true,"minutes":[{"name":"1-774","mins":"0"}]},{"name":"NURSAPVUndAmbu","goal_groups":"3","goal_mins":"360","joined_groups":[""],"ignored_groups":[""],"ops_computetype":"weekly","ops_includet_patient":true,"ops_includet_angeh":true,"ops_includet_prof":true,"ops_includet_sys":true,"ops_only_internal":false,"ops_case_station":false,"ops_case_konsil":false,"ops_case_ambulant":true,"ops_case_sapv":true,"minutes":[{"name":"SAPV","mins":"0"}]},{"name":"8-982","goal_groups":"3","goal_mins":"360","joined_groups":["Arzt","Pflege",""],"ignored_groups":[""],"ops_computetype":"weekly","ops_includet_patient":true,"ops_includet_angeh":true,"ops_includet_prof":true,"ops_includet_sys":true,"ops_only_internal":false,"ops_case_station":false,"ops_case_konsil":false,"ops_case_ambulant":false,"ops_case_sapv":false,"minutes":[{"name":"8-982.0","mins":"0"},{"name":"8-982.1","mins":"1"},{"name":"8-982.2","mins":"2"},{"name":"8-982.3","mins":"3"},{"name":"8-982.4","mins":"4"},{"name":"8-982.5","mins":"5"},{"name":"8-982.6","mins":"6"},{"name":"8-982.7","mins":"7"},{"name":"8-982.8","mins":"8"},{"name":"8-982.9","mins":"9"}]},{"name":"OHNE","goal_groups":"0","goal_mins":"0","joined_groups":["Arzt",""],"ignored_groups":[""],"ops_computetype":"sum_only","ops_includet_patient":true,"ops_includet_angeh":true,"ops_includet_prof":true,"ops_includet_sys":true,"ops_only_internal":true,"ops_case_station":true,"ops_case_konsil":true,"ops_case_ambulant":true,"ops_case_sapv":true,"minutes":[{"name":"OHNE","mins":"0"}]}]}';
            $default=json_decode($default,1);
            $this->view->config=$default;
        }else {
            $this->view->config = ClientConfig::getConfig($clientid, 'opsconfig');
        }

    }


    public function serverinputAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->setLayout('layout_ajax');

        if ($_POST['msg']) {
            $clientid=intval($_REQUEST['cid']);
            if($clientid<0){
                die('clientid nicht gesetzt');
            }

            $msg=urldecode($_POST['msg']);
            $msg=unserialize($msg);
            $logininfo= new Zend_Session_Namespace('Login_Info');
            $logininfo->clientid=$clientid;
                $conf = array (
                    //the clientid for this server
                    'clientid'		=>	$clientid,
                    //the userid this server has
                    'userid'		=>	$logininfo->userid,
                    //everything is logged to db is encrypted if true
                    'encryptlog'	=>  false,
                    //0=print all, 2 print only errors
                    'verbosity' 	=>	0,
                    //we dont have real productivity data: mark patient as testpatient
                    'testdata'      =>  0
                );

                Net_ProcessHL7::process_message($msg,$conf);
                exit();
        }
        else{
            echo "no POST data.";
            exit();
        }
    }

}
