<?php

require_once("Pms/Form.php");

class Application_Form_News extends Pms_Form
{
	public function validate($post)
	{
		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
		if(!$val->isstring($post['news_title'])){
			$this->error_message['news_title']=$Tr->translate('enternewstitle'); $error=1;
		}
		if(!$val->isstring($post['news_content'])){
			$this->error_message['news_content']=$Tr->translate('enternewscontent'); $error=2;
		}
		if(!$val->isstring($post['news_date'])){
			$this->error_message['news_date']=$Tr->translate('enternewsdate'); $error=3;
		}


		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		$news_date = explode(".",$post['news_date']);

		$user = new News();
		$user->news_title =  Pms_CommonData::aesEncrypt($post['news_title']);
		$user->news_content = Pms_CommonData::aesEncrypt($post['news_content']);
		$user->news_date = $news_date[2]."-".$news_date[1]."-".$news_date[0];
		$user->issystem = $_POST['issystem'];
        //ISPC-2421,Elena,22.04.2021
		$aClients = [];
		if($logininfo->usertype=='SA')
		{
            //ISPC-2421,Elena,22.04.2021
		    if(count($post['clientid']) > 0){
                $aClients = $post['clientid'];
                $user->clientid = -1;
            }
		    /*elseif($post['clientid']>0)
			{
				$user->clientid = $post['clientid'];
			}*/else{
				$user->clientid = $logininfo->clientid;
			}

		}else{

			$user->clientid = $logininfo->clientid;
			$user->userid = $logininfo->userid;
		}
		if($user->clientid >= 0){//ISPC-2421,Elena,22.04.2021
		$user->save();

		if(count($post['userid'])>0)
		{
			foreach($post['userid'] as $key=>$val)
			{
				$newsmap = new NewsMaping();
				$newsmap->userid = $val;
				if($logininfo->usertype=='SA')
				{
					$newsmap->clientid = $post['clientid'];
				}else{
					$newsmap->clientid = $logininfo->clientid;
				}
				$newsmap->newsid = $user->id;
				$newsmap->save();
					
			}
		}else{


			$newsmap = new NewsMaping();
			if(strlen($post['clientid'])>0)
			{
				$newsmap->clientid = $post['clientid'];
			}else{
					
				$newsmap->clientid = $logininfo->clientid;
			}
			$newsmap->userid = 0;

			$newsmap->newsid = $user->id;
			$newsmap->save();
		}
        }else{//ISPC-2421,Elena,22.04.2021
		    foreach($aClients as $client_id){
		        $clientuser = clone $user;
		        $clientuser->clientid = $client_id;
		        $clientuser->save();
                if(count($post['userid'])>0)
                {
                    foreach($post['userid'] as $key=>$val)
                    {
                        $newsmap = new NewsMaping();
                        $newsmap->userid = $val;
                        if($logininfo->usertype=='SA')
                        {
                            $newsmap->clientid = $client_id;
                        }else{
                            $newsmap->clientid = $logininfo->clientid;
                        }
                        $newsmap->newsid = $clientuser->id;
                        $newsmap->save();

                    }
                }else{


                    $newsmap = new NewsMaping();
                    if(count($aClients)>0)
                    {
                        $newsmap->clientid = $client_id;
                    }else{

                        $newsmap->clientid = $logininfo->clientid;
                    }
                    $newsmap->userid = 0;

                    $newsmap->newsid = $clientuser->id;
                    $newsmap->save();
                }

            }
	}



	}

	public function UpdateData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$news_date = explode(".",$post['news_date']);

		$user = Doctrine::getTable('News')->find($_GET['id']);
		$user->news_title = Pms_CommonData::aesEncrypt($post['news_title']);
		$user->news_content = Pms_CommonData::aesEncrypt($post['news_content']);
		$user->news_date = $news_date[2]."-".$news_date[1]."-".$news_date[0];
		$user->issystem = $_POST['issystem'];
		if($logininfo->usertype=='SA')
		{
			if($post['clientid']>0)
			{
				$user->clientid = $post['clientid'];
			}else{
				$user->clientid = $logininfo->clientid;
			}

		}else{
			$user->clientid = $logininfo->clientid;
			$user->userid = $logininfo->userid;
		}
			
		$user->assign_user = $post['userid'];
		$user->save();

		if(count($post['userid'])>0)
		{
			$del = Doctrine_Query::create()
			->delete('NewsMaping')
			->where("newsid= ?", $_GET['id']);
			$del->execute();

			foreach($post['userid'] as $key=>$val)
			{
				$newsmap = new NewsMaping();
				$newsmap->userid = $val;
				if($logininfo->usertype=='SA')
				{
					$newsmap->clientid = $post['clientid'];
				}else{
					$newsmap->clientid = $logininfo->clientid;
				}
				$newsmap->newsid = $_GET['id'];
				$newsmap->save();
					
			}
		}else{

			$newsmap = new NewsMaping();
			$newsmap->userid = 0;
			if(count($post['clientid'])>0)
			{
				$newsmap->clientid = $post['clientid'];
			}else{
					
				$newsmap->clientid = $logininfo->clientid;
			}
			$newsmap->newsid = $user->id;
			$newsmap->save();
		}
	}
}

?>