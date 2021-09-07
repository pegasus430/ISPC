<?php
require_once ("Pms/Form.php");

class Application_Form_BraAnlage5Products extends Pms_Form
{

    public function insert($ipid, $post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        if(strlen($post['anlage5_id']) > 0 ){
            foreach($post['product_data'] as $prod_id => $prod_details){
                if($prod_details['qty'] > 0){
                    $records[] = array(
                        "ipid" => $ipid,
                        "anlage5_id" => $post['anlage5_id'],
                        "start_date" => date('Y-m-d H:i:s',strtotime($post['start_date'])),
                        "end_date" => date('Y-m-d H:i:s',strtotime($post['end_date'])),
                        "shortcut" => $prod_id,
                        "qty" => $prod_details['qty'],
                        "price" => $prod_details['price'],
                        "total" => $prod_details['total']
                    );
                }
            }
            if(!empty($records)){
                $collection = new Doctrine_Collection('BraAnlage5Products');
                $collection->fromArray($records);
                $collection->save();
            }
        } 
    }
}
?>
