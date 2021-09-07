<?php
/**
 * TableLogListener
 * add logging to tables since users don't know who broke what
 *
 * @package    ISPC
  * @author     al3x <ag@orw.ro>
 
 */
class TableLogListener extends Doctrine_Record_Listener
{

    public function preInsert(Doctrine_Event $event)
    {
        //we're not doing anything on insert
    }

    public function preUpdate(Doctrine_Event $event)
    {
        return;
        $invoker = $event->getInvoker();
        $modified = $invoker->getModified();
        $old = $invoker->getModified(true);
        $row = $invoker->toArray();
        $model = $invoker->getTable()->getComponentName();
        $ipid = null;
        $record_id = null;
        $savelog = false;
        
        switch ($model) {
            
            case 'PatientMaster':
                $savelog = true;
                $ipid = $invoker->ipid;
                break;
            
            default:
                break;
        }
        
        if($savelog) {
            $table_log = new TableLog();
            $table_log->set_new_record(array(
                'ipid' => $ipid,
                'record_id' => $record_id,
                'model' => $model,
                'old' => serialize($old),
                'modified' => serialize($modified),
                '`row`' => serialize($row)//MySQL 8 Ancuta 16.06.2021
            ));
        }
    }
}

?>