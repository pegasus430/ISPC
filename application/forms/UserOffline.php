<?php
//require_once ("Pms/Form.php");

class Application_Form_UserOffline extends Pms_Form
{

    public function validate($post)
    {
        $error = 0;
        $val = new Pms_Validation();
        $Tr = new Zend_View_Helper_Translate();
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $clientid = $logininfo->clientid;
        
        $has_offline_user = false;
        if ($post['has_offline_user'] == '1') {
            $has_offline_user = true;
        }
        
        // check if there is a client selected
        if ($clientid == '0') {
            $this->error_message['form'] = $Tr->translate('selectclient');
            $error = 11;
        }
        
        // check if user is filled
        if (! $val->isstring($post['username']) && $error == '0') {
            $this->error_message['username'] = $Tr->translate('offline_username_error');
            $error = 1;
        }
        
        // check if user has minimal requirement chars (6)
        if (strlen($post['username']) <= '5' && $error == '0') {
            $this->error_message['username'] = $Tr->translate('offline_username_length_error');
            $error = 2;
        }
        
        // check if user exists
        if (UserOffline::check_username_taken($post['username'], $userid) && $error == '0') {
            $this->error_message['username'] = $Tr->translate('offline_username_allready_exists');
            $error = 3;
        }
        
        if ($has_offline_user === true) {
            // check if passwords match only if first password is filled in edit mode
            if ($val->isstring($post['password_first'])) {
                if (! $val->isstring($post['password_second']) && $error == '0') {
                    $this->error_message['password'] = $Tr->translate('offline_password_mismatch');
                    $error = 4;
                } else 
                    if ($post['password_first'] !== $post['password_second'] && $error == '0') {
                        $this->error_message['password'] = $Tr->translate('offline_password_mismatch');
                        $error = 5;
                    }
            }
        } else {
            
            // *must* check if passwords match for first user creation
            if (! $val->isstring($post['password_first']) && $error == '0') {
                $this->error_message['password'] = $Tr->translate('offline_password_mismatch');
                $error = 7;
            } else 
                if (! $val->isstring($post['password_second']) && $error == '0') {
                    $this->error_message['password'] = $Tr->translate('offline_password_mismatch');
                    $error = 8;
                } else 
                    if ($post['password_first'] !== $post['password_second'] && $error == '0') {
                        $this->error_message['password'] = $Tr->translate('offline_password_mismatch');
                        $error = 9;
                    }
        }
        
        if ($error == '0') {
            return true;
        } else {
            return false;
        }
    }

    public function insert_offline_user($post)
    {
        $Tr = new Zend_View_Helper_Translate();
        $validation = new Pms_Validation();
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $clientid = $logininfo->clientid;
        $has_offline_user = $post['has_offline_user'];
        
        // insert or update offline user for a userid
        if ($has_offline_user == '0') {
            // do insert
            $ins = new UserOffline();
            $ins->userid = $userid;
            $ins->clientid = $clientid;
            $ins->username = $post['username'];
            $ins->password = md5(trim(rtrim($post['password_first'])));
            $ins->save();
            
            if ($ins->id) {
                return true;
            } else {
                return false;
            }
        } else {
            // do update for username
            $upd = Doctrine_Query::create()
            ->update('UserOffline')
            ->set('username', '?', $post['username'])
            ->where('userid = ?', $userid)
            ->andWhere('clientid = ?', $clientid)
            ->execute();
            
            // second password check
            if ($post['has_offline_user'] 
                && $post['password_first'] === $post['password_second'] 
                && strlen($post['password_first']) > 0) 
            {
                // password edit
                $upd_pass = Doctrine_Query::create()
                ->update('UserOffline')
                ->set('password', '?', md5($post['password_first']))
                ->where('userid = ?', $userid)
                ->andWhere('clientid = ?', $clientid)
                ->execute();
            }
            
            return true;
        }
    }
    
    
    public function validate_offline_user($post) 
    {
        $error = '0';
   
        // check if user has minimal requirement chars (6)
        if ( ! empty($post['username']) && strlen($post['username']) <= '5') {
            $this->error_message['offline_username'] = $this->translate('offline_username_length_error');
            $error = 2;
        }
        
        // check if user exists
        if ($error == '0' && ! empty($post['username']) && ! UserOffline::assertUsernameUnique($post['username'], $post['userid'])) {
            $this->error_message['offline_username'] = $this->translate('offline_username_allready_exists');
            $error = 3;
        }
        
        if ($error == '0') {
            return true;
        } else {
            return false;
        }
    }
    
    public function save_offline_user($post)
    {
        if (empty($post['userid']) || ! isset($post['clientid']) || empty($post['username'])) {
            return; //fail-safe
        }
        
        if ($post['password_changed'] != 1 || empty($post['password'])) {
            unset($post['password']);
        } else {
            $post['password'] = md5(trim($post['password']));
        }

        $obj = new UserOffline();
        
        return $obj->findOrCreateOneBy("userid", $post['userid'], $post);
    }
}

?>