<?php
/**
 * Update whole File for TODO-4163
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class Application_Form_FormBlockPcoc extends Pms_Form
{

    public function render_cf_block_form($options, $ipid, $clientid){
        $newview = new Zend_View();

        $newview->box_open=$options['box_open'];
        $newview->setScriptPath(APPLICATION_PATH."/views/scripts/templates/");
        $boxes = ClientConfig::getConfig($clientid, 'config_pcoc');
        $forms_no_shortstatus = ClientConfig::getConfigOrDefault($clientid, 'config_pcoc_full');
        $sections = FormBlockPcoc::$sections;

        if($options['formular_type']=="pdf"){
            $newview->pdf=true;
        }
        $tid = $options['form_type_id'];

        //START HistoryPrefill
        if(!isset($options['pcoc_done']) ){
            $options['pcoc_done']=1;
        }
        if(($options['contact_form_id']<1 || !$options['misc_date'] ) && $options['formular_type']!=="pdf") {
            $options['pcoc_full']=0;
            $options['ipos_add']=FormBlockPcoc::get_last_ipos_adds($ipid);
            $options['ipos_add'][]=['key'=>'', 'value'=>''];

            //prefill with last block
            $pre_options=FormBlockPcoc::get_last_values($ipid);
            if(count($pre_options)){
                unset($pre_options['pcoc_full']);
                $options=$pre_options;
                unset($options['misc_date']);
                unset($options['misc_dateh']);
                unset($options['misc_datem']);
                unset($options['pcoc_assessment']);
                unset($options['shortstatus']);
            }else{
                $options['pcoc_full']=1; //If no prefills: always take full pcoc
            }
            $options['pcoc_done']=0;
        }
        //END History Prefill

        $implicit_full=1;
        foreach ($sections as $sectionkey => $foo) {
            if (isset($boxes[$tid][$sectionkey]) && $boxes[$tid][$sectionkey]) {
                $options[$sectionkey . "_enabled"] = 1;
            } else {
                $options[$sectionkey . "_enabled"] = 0;
                $implicit_full=0;
            }
        }

        if(!$options['pcoc_done']){
            $implicit_full=0;
        }

        if($implicit_full){
            $options['pcoc_full']=1;
        }

        if(!isset($options['shortstatus'])){
            $options['shortstatus']=1;
        }
        if(is_array($forms_no_shortstatus) && isset($forms_no_shortstatus[$tid]) && $forms_no_shortstatus[$tid]){
            $options['shortstatus']=0;
        }
        if($options['pcoc_full']){
            $options['shortstatus']=0;
        }

        if($options['contact_form_id']>0){
            $options['ipos_add']=json_decode($options['ipos_add'],1);
        }

        $options['last_status']=FormBlockPcoc::get_actual_status($ipid);
        $pd=new PatientDischarge();
        $deaths=$pd->getPatientsDeathDate($clientid, [$ipid], true );

        $options['death']='0';
        if(is_array($deaths) && isset($deaths[$ipid])){
            $options['death']=$deaths[$ipid];
        }

        $newview->options=$options;

        $newview->must_be_filled=ClientConfig::getConfigOrDefault($clientid, 'config_pcoc_required');;

        $html = $newview->render('form_block_pcoc.html');

        return $html;
    }

    /**
     * Nico 3.12.2020 IM-147
     */
    public function save_form_pcoc($ipid, $data_post, $data_block, $clientid){

        if($data_block['pcoc_done']=="0"){
            //block overriden
            return;
        }

        $data_block['ipid']=$ipid;
        $data_block['contact_form_id']=$data_post['contact_form_id'];

        //$data_block['misc_date']=date("Y-m-d H:i:00", strtotime($data_block['misc_date'] . " " . $data_block['misc_dateh'] .":".$data_block['misc_datem']));
        //take time from form, no time is user entered inside pcoc form any more
        $data_block['misc_date']=date("Y-m-d H:i:00", strtotime($data_post['__formular']['date'] . " " . $data_post['__formular']['begin_date_h'] .":".$data_post['__formular']['begin_date_m']));

        $data_block['ipos_add']=json_encode($data_block['ipos_add']);
        $last_status=FormBlockPcoc::get_actual_status($ipid);
        $data_block['clientid']=$clientid;

        $x=new FormBlockPcoc();
        $cnames=$x->getTable()->getColumnNames();
        foreach ($cnames as $cname){
            if(!in_array($cname, ['create_date', 'create_user', 'change_date', 'change_user']))
                $x->$cname = $data_block[$cname];
        }
        $x->save();


        if(
            $last_status['phase_phase']!=$data_block['phase_phase']
            && intval($data_block['phase_phase'])<5
            && intval($data_block['phase_phase'])>0
        ) {
            if($last_status['misc_date']<=$data_block['misc_date']){
                PatientMaster::change_traffic_status($data_block['phase_phase'], $ipid);

            }
        }

        if($data_block['pcoc_done']) {
            $newview = new Zend_View();
            $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");
            $newview->options = $data_block;
            $html = $newview->render('form_block_pcoc_course.html');
            ContactForms::add_recorddata($html, 'pcoc', $data_post['__formular']);
        }

        if(isset($data_post['__formular']['old_contact_form_id']) && $data_post['__formular']['old_contact_form_id']>0) {
            $old=new FormBlockPcoc();
            $oid=$old->getTable()->findOneBy('contact_form_id', $data_post['__formular']['old_contact_form_id']);
            if($oid && $oid->id>0) {
                $oid->isdelete=1;
                $oid->save();
            }
        }
    }


}