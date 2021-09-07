<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('FormLock', 'SYSDAT');

class FormLock extends BaseFormLock
{

    /**
     * returns username if form is locked by another user
     * returns true if form is locked by this user itself or if it is not locked
     */
    public static function locker($encid, $form, $forminner){
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $userid=$logininfo->userid;

        $decid = Pms_Uuid::decrypt($encid);
        $ipid = Pms_CommonData::getIpid($decid);

        $timespan=120;//120secs
        list($usec, $sec) = explode(" ", microtime());
        $now_s=$sec-$timespan;


        $drop = Doctrine_Query::create()
            ->select('id')
            ->from('FormLock')
            ->where("ipid=?",$ipid)
            ->andWhere("form=?",$form)
            ->andWhere("forminner=?",$forminner)
            ->andWhere("lockdate>?",$now_s)
            ->limit(1);
        ;
        $droparray = $drop->fetchArray();

        if($droparray){
            $entry=Doctrine::getTable('FormLock')->findOneBy('id', $droparray[0]['id']);
            if(intval($entry->user)!==intval($userid)){
                $uname=User::getUsernameById($entry->user);
                return ($uname);
            }
        }else{
            $entry = new FormLock();
            $entry->ipid=$ipid;
            $entry->form=$form;
            $entry->forminner=$forminner;
            $entry->user=$userid;
        }

        $entry->lockdate=$sec;
        $entry->save();

        $now_s=$now_s-20;
        $q = Doctrine_Query::create()
            ->delete('FormLock a')
            ->where("a.lockdate<?",$now_s);
        $q->execute();


        return true;

    }

    /**
     * The widget to enable form-locking
     * usage: <?php echo FormLock::lockerwidget($this->encid, 'newcontactform', $this->form_type_id);?>
     */

    public static function lockerwidget($encid, $form, $forminner){
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $userid=$logininfo->userid;

        $view = new Zend_View();
        $view->setScriptPath(APPLICATION_PATH."/views/scripts/templates/");
        $view->encid=$encid;
        $view->form=$form;
        $view->forminner=$forminner;
        $view->user=$userid;
        $view->delay=80000; //80000 means: check the lock every 80s on ajax/formlock
        $html = $view->render('form_lock_widget.html');

        return ($html);
    }

}



?>
