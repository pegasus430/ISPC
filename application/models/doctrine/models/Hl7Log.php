<?php
Doctrine_Manager::getInstance()->bindComponent('Hl7Log', 'SYSDAT');

class Hl7Log extends BaseHl7Log
{

    public function log_hl7($message = '', $level = 0)
    {
        $mail_log = new Hl7Log();
        $mail_log->message = $message;
        $mail_log->date = date("Y-m-d H:i:s", time());
        $mail_log->level = $level;
        $mail_log->save();
    }
}
?>
