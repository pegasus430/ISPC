<?php
/**
 * 
 * @author claudiu
 *  
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * TODO 
 * when you upload for live, be sure to check/remove/update the conditions from /home/www/ispc2017_08/library/Pms/Plugin/Acl.php
 * in ACL there is a if elvi ignore loghed in user.... so be sure!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 *
 */
class ElviController extends Pms_Controller_Action {
        
    
    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();
        
        $this->_helper->layout->setLayout('layout_ajax');
        
        return $this->_elvi();
    }
    
    
    private function _elvi() 
    {
        
        $result = [];
        
        $body = $this->getRequest()->getRawBody();
        $data = Zend_Json::decode($body);
        
        if ( ! empty($data) && ! empty($data['processToken'])) {
            
            $action = ElviTransactionsTable::getInstance()->findOneBy('processToken', $data['processToken'], Doctrine_Core::HYDRATE_ARRAY);
            
            $result = ! empty($action['request']) ? $action['request'] : ['success' => false, 'message' => 'processToken not found'];
            
        } else {
            
            $result = ['success' => false, 'message' => 'processToken empty'];
            
        }
        
        $result = Zend_Json::encode($result); 
        
        if (APPLICATION_ENV != 'production') {
            //elvi request log
            $et = new ElviTest();
            $et->received = $body;
            $et->sent = $result;
            $et->save();
        }
        
//         $this->getHelper('Log')->debug("setBody: {$result}");
        
        $this->getResponse()->setHeader('Content-Type', 'application/json')->setBody($result)->sendResponse();

        exit;
        
    }

    
    
}
	