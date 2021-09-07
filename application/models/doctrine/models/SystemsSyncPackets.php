<?php
Doctrine_Manager::getInstance()->bindComponent('SystemsSyncPackets', 'SYSDAT');
class SystemsSyncPackets extends BaseSystemsSyncPackets
{	
    public static function createPacket($ipid, $datapacket, $actionname, $outgoing=1){
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $payload = $datapacket;

        if($actionname=="BA"){
            //just add non-empty blocks
            $payload=array();
            foreach ($datapacket as $key => $data_arr){
                $data_str = implode($data_arr);
                if(strlen($data_str)>0){
                    $payload[$key] = $datapacket[$key];
                }
            }
        }

        if(count($payload)>0) {
            
            Zend_Json::$useBuiltinEncoderDecoder = true;
            
            $new = new SystemsSyncPackets();
            $new->ipid = $ipid;
            $new->clientid = $clientid;
            $new->actionname = $actionname;
//             $new->payload = json_encode($payload);
            $new->payload = Zend_Json::encode($payload);
            $new->outgoing = $outgoing;
            $new->save();
        }
    }

    public static function get_ba_widget($ipid){
        $sql = Doctrine_Query::create()
            ->select('*')
            ->from('SystemsSyncPackets')
            ->where('ipid=?',$ipid)
            ->andwhere('outgoing=0')
            ->andwhere('actionname=?','BA')
            ->orderBy('id DESC')
            ->limit(1);     //all we are interested in is the most recent entry
        $patients = $sql->fetchArray();

        if($patients){
//             $newview = new Zend_View();
//             $newview->mode="html";
//             $newview->data=$patients[0];
//             $newview->setScriptPath(APPLICATION_PATH."/views/scripts/templates/");
//             $rendered = $newview->render('ba_prefill_widget.html');

//             return $rendered;
            $data = $patients[0];
            
            return $data;

        }else{
            return "";
        }
    }

    public static function get_ba_data($id, $mark_as_done=0){
        $data = Doctrine::getTable('SystemsSyncPackets')->findOneById($id);

        if($data){
            //mark as done
            if($mark_as_done) {
                $data->done = 1;
                $data->save();
            }
            $out=$data['payload'];
            $out=json_decode($out,1);
            return $out;
        }
    }

    
    public static function get_med_widget_data($ipid){
    	$sql = Doctrine_Query::create()
    	->select('*')
    	->from('SystemsSyncPackets')
    	->where('ipid=?',$ipid)
    	->andwhere('outgoing=0')
    	->andwhere('actionname=?','med')
    	->orderBy('id DESC')
    	->limit(1);     //all we are interested in is the most recent entry
    	$patients = $sql->fetchArray();
    
    	return $patients;
    }
    
    public static function get_med_data($id, $mark_as_done=0){
    	$data = Doctrine::getTable('SystemsSyncPackets')->findOneById($id);
    
    	if($data){
    		//mark as done
    		if($mark_as_done) {
    			$data->done = 1;
    			$data->save();
    		}
    		$out=$data['payload'];
    		$out=json_decode($out,1);
    		return $out;
    	}
    }
    
    
    /**
     * Sent by Nico - Added by Ancuta 23.08.2017 
     * @param unknown $ipid
     * @return Ambigous <multitype:, Doctrine_Collection>
     */
    public static function get_diag_widget_data($ipid){
    	$sql = Doctrine_Query::create()
    	->select('*')
    	->from('SystemsSyncPackets')
    	->where('ipid=?',$ipid)
    	->andwhere('outgoing=0')
    	->andwhere('actionname=?','diag')
    	->orderBy('id DESC')
    	->limit(1);     //all we are interested in is the most recent entry
    	$patients = $sql->fetchArray();
    
    	return $patients;
    }
    
    /**
     * 
     * @param unknown $id
     * @param number $mark_as_done
     * @return mixed
     */
    public static function get_diag_data($id, $mark_as_done=0){
    	$data = Doctrine::getTable('SystemsSyncPackets')->findOneById($id);
    
    	if($data){
    		//mark as done
    		if($mark_as_done) {
    			$data->done = 1;
    			$data->save();
    		}
    		$out=$data['payload'];
    		$out=json_decode($out,1);
    		return $out;
    	}
    }
    
    
    
    
}
?>