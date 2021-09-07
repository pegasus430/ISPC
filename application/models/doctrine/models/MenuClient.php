<?php
Doctrine_Manager::getInstance()->bindComponent('MenuClient', 'SYSDAT');

class MenuClient extends BaseMenuClient
{

    /**
     * @since 15.06.2018 - rewrite @cla
     * 
     * @param number $cid
     * @return string
     */
    public function getMenusByClient($cid = 0)
    {
        $result = "'0'";
        
        if (empty($cid)) {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $cid = $logininfo->clientid;
        }
        
        $menu_array = Doctrine_Query::create()
        ->select('id, menu_id')
        ->from('MenuClient')
        ->where('clientid = ?', $cid)
        ->fetchArray();
        
        if ( ! empty($menu_array)) {
            
            $menu_ids = array_column($menu_array, 'menu_id');
            
            $menu_ids = implode("', '", $menu_ids);
            
            $result =  "'" . $menu_ids . "'";
            
        }
        
        return $result;
    }
}

?>