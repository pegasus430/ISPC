<?php
/**
 * 
 * @author claudiu
 * @date xx.11.2017 
 *
 * 
 * SA cannot have elvi account, it has no clientid
 *
 */
/*
 * user.connect
- for this to work i need a correct usernameAtVendor.. so i ALLWAYS have to call user.create before any user.connect
- then request my ISPC-user to provide me his original Elvi-username, and then i must add it to my newly created usernameAtVendor and only after that call user.connect
- the end result - in the elvi account of this user, on the left, he will get a message he must Agree to, so he is `connected` to our organization
- after he Agrees, i can directly call the user.login by using this new usernameAtVendor

to add a user inside a group... all the steps must be finished before i can do'it


elvi question:

Because you have removed: organization.create & organization.addOrRemoveUser, I fail to understand the hole concept of ORGANIZATION in the context of ISCP, and how this would benefit us.

- now there are only 3 action i can perform: organization.info, organization.list, organization.connect
- organization.info & organization.list are ONLY for infos, and ONLY after we are connected with them,
- organization.connect : is only after my usernameAtVendor is allready added to one... 
`Any user who is part of an organization with necessary rights is able to create and provide an api token which acts as identifier`

- I fail to see WHO creates organizations and WHO adds users to it ??? 
- please explain, if you removed the 2 action for create & addOrRemoveUser, what took their place? 

group.addOrRemoveViewer
your @dev should take some usability lessons...
how the fuck do i know if a user is add or not to a group? 
i call fn.. and if it says it was removed i call it again or till it returns the reponse " group . viewer " : " added "?
or if i want to remove him the reverse logic? 

 */
    
class ElviService implements Doctrine_Overloadable {

    
    /**
     * if user clicks icon to login into elvi, and he does not have an account allready, we auto-create one for him
     * without requestion him if he allready has an account
     * 
     * @var Boolean
     */
    private $_autoCreateElviUser = false;
    
    
    /**
     * @var Zend_Http_Client
     */
    protected $_httpService =  null;
    
    /**
     * @var ElviTransactions
     */
    protected $_elviTransaction =  null;
    
    /**
     * 
     * @var ElviUsers
     */
    protected $_elviUser =  null;
    
    /**
     * 
     * @var ElviOrganizations
     */
    protected $_elviOrganization =  null;
    
    
    /**
     * 
     * @var ElviGroups
     */
    protected $_elviGroup =  null;
    
    
    /**
     * 
     * @var User
     */
    protected $_ispcUser =  null;
    
    /**
     * 
     * @var string for now.. not PatientMaster
     */
    protected $_ipid = null;

    
    /**
     * elvi configs
     * @var array
     */
    protected $_elvi_config;
    
    /**
     * this are from thr elvi pdf
     */
    private $__elvi_error_code = array(
        '002' => 'vendor not found',
        '008' => 'object not found',
        '009' => 'element creation failed',
        '011' => 'credentials missing',
        '012' => 'invalid argument state',
        '018' => 'internal error',
        '019' => 'operation not permitted',
        '020' => 'duplicate username',
        '026' => 'activation required',
        '030' => 'vendor account blocked due to several auth fails',
        '032' => 'callback failed, got no response',
        '032' => 'failed to parse the response_elviTransaction',
        '034' => 'duplicate userNameExternal',
        
        ''  => 'Not implemented yet. Updates are done only by user',//@claudiu
        '033' => 'Unable to parse VerificationResponse',//@claudiu
    );
    
    
    private $__ispc_error_code = array(
        '001' => 'Cannot create elVi user, you have no ispc user',
        '002' => 'Failed token, contact admin',
        '003' => 'Current user has no clientid',
        '004' => 'You must first create an elVi account',
    );
    
    
    /**
     * 
     * ispc<->elvi profiles map
     * do NOT add a ispc group in multiple elvi
     */
    private $__elvi_profileType = [
        /*
         * ispc groups @17.09.2018
        8 => Apotheke
        4 => Arzt
        9 => Hausarzt
        7 => Hospiz
        10 => Hospizverein
        6 => Koordination
        3 => ohne Gruppe
        5 => Pflege
        12 => Sanitatshaus
        11 => Students
        */
        
        /*
         * this groups must be the same from  ElviUser->profileType 
         */
        'notSpecified'      => [3],
        'nurse'             => [5],
        'doctor'            => [4, 9],
        'organizationStaff' => [6],
        'user'              => [8, 7, 10, 12, 11],
        
        'visitor'           => [],
        'patient'           => [],
    ];
    
    
    /**
     * 
     * @var Zend_View_Helper_Translate
     */
    private $_translator = null;
    
    
    public function __construct($processToken = null, Zend_Http_Client $httpService = null) 
    {
        	
        $this->_elvi_config = Zend_Registry::get('elvi');        

        $httpConfig = array(
            'timeout'       => 30,// Default = 10
            'useragent'     => 'Zend_Http_Client-ISPC',// Default = Zend_Http_Client
        );
        
        if (is_null($httpService) ) {
            $this->_httpService =  new Zend_Http_Client(null, $httpConfig);
        } else {
            if ($httpService instanceof Zend_Http_Client) {
                $this->_httpService = $httpService;
            } else {
                throw new Exception( __METHOD__ . ' _httpService not instance of Zend_Http_Client, contact admin', 0 ); 
            }
        }

        $this->_elviTransaction = ElviTransactionsTable::getInstance()->findOrCreateOneBy('processToken', $processToken);
        
        if (empty($this->_elviTransaction->processToken)) {
            throw new Exception( __METHOD__ . ' _elviTransaction processToken failed, contact admin'. var_dump($this->_elviTransaction), 0 );
        }
        
        
        $this->_translator = new Zend_View_Helper_Translate();
        
    }
   
    
    /**
     * proxy methods
     */
    public function setHttpConfig($config = array()) {
        $this->_httpService->setConfig($config);
    }
    public function getLastResponse() {
        return $this->_httpService->getLastResponse();
    }
    public function getLastRequest() {
        return $this->_httpService->getLastRequest();
    }

    /**
     * getter needed only if is new
     * @return ElviUsers
     */
    public function getElviUser() {
        return $this->_elviUser;
    }
    
    /**
     * getter needed only if is new
     * @return ElviGroups
     */
    public function getElviGroup() {
        return $this->_elviGroup;
    }
    
    /**
     *
     * @return ElviTransactions
     */
    public function getElviTransaction() {
        return $this->_elviTransaction;
    }
    
    
    
    /**
     * magique
     * @param unknown $name
     * @param unknown $arguments
     */
    public function __call ( $name , $arguments = null ) {
        
        foreach ($arguments as $arg) {
            
            if ($arg instanceof ElviUsers) {
                
                $this->_elviUser = $arg;
                 
            } elseif ($arg instanceof ElviGroups) {
                
                $this->_elviGroup = $arg;
                
            } elseif ($arg instanceof User) {
                
                $this->_ispcUser = $arg;
            }
        }
        
        
        if (method_exists($this, $name)) {
            return call_user_func_array( array($this , $name ) , $arguments );
        } else {
            return [''];
        }
    }
    

    
    /*
     * you must correctly create a user in the first place, cause there is no update 
     */
    private function _user_create()
    {
        $action = 'user.create';
        
        if (empty($this->_ispcUser)) {
            return [
                'success' => false,
                '__ispc' => ['message' => "you have no _ispcUser"],
            ];
        }
        
        
        if ( ! empty($this->_elviUser) && ! empty($this->_elviUser->usernameAtVendor)) {
            return [
                'success' => false,
                '__ispc' => ['message' => "you allready have a _elviUser"],
            ];
            
        } else {
            
            //if not exists, create one now
            $this->__ispc_findOrCreate_elviUser();
        }

        
        $gender = [
            'male',
            'female',
            'notSpecified',
        ];
        
        $request = array(
            'action'            => $action,
            'userData'          => [
                'usernameAtVendor'  => $this->_elviUser->usernameAtVendor,
                'userpassAtVendor'  => $this->_elviUser->userpassAtVendor,
                'profileType'       => $this->_elviUser->profileType, //"doctor",
                'preferredLang'     => 'de',
//                 'username'          => $this->_elviUser->username, // removed in v.2, you cannot set this
//                 'pass'              => $this->_elviUser->password, // removed in v.2
                'firstname'         => $this->_ispcUser->first_name,
                'lastname'          => $this->_ispcUser->last_name,
                'title'             => $this->_ispcUser->user_title,
                'email'             => $this->_ispcUser->emailid, // elVi password recovery
                'gender'            => 'notSpecified',// do we have a gender? male|female|notSpecified
//                 'accessToken'       => '', //removed in v.2, 
//                 'organizationId'    => -1, //removed in v.2, 
//                 'userId'            => -1, //removed in v.2,                
            ],
        ); 

        $this->_elviTransaction->request = $request; 
        $this->_elviTransaction->save();          
        
        
        return $this->processAndCallback();        
    }
    
    private function _user_create_callback($params = null)
    {
        $result = $this->_elviTransaction->response;
        
        
        if ( ! empty($result['content']['vendor2user']) 
            && $result['content']['vendor2user']['usernameAtVendor'] == $this->_elviUser->usernameAtVendor) 
        {
            $this->_elviUser->userIdExternal = $result['content']['vendor2user']['userId'];
            $this->_elviUser->save();
        }
        
        
        return $result;
    }
    
    private function _user_connect( $elVi_username = null)
    {
        if (empty($this->_elviUser)) {
            return [
                'success' => false,
                '__ispc' => ['message' => "you have no _elviUser"],
            ]; //fail-safe
        }
        
        $action = 'user.connect';
        
        if (empty($this->_elviUser->username) 
            && ! empty($elVi_username) 
            && is_string($elVi_username)) 
        {
            $this->_elviUser->username =  $elVi_username;
            $this->_elviUser->save();
        }
        
        
        $request = array(
            'action'    => $action,
            'userData' => [
                'usernameAtVendor'  => $this->_elviUser->usernameAtVendor,
                'userpassAtVendor'  => $this->_elviUser->userpassAtVendor,
                'username'          => $this->_elviUser->username, 
            ],
        );

        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
        
        return $this->processAndCallback();
    }
    private function _user_connect_callback($params = null)
    {
        $result = $this->_elviTransaction->response;
        
        return $result;
    }
    
    private function _user_login()
    {
        /*
         * auto-create the user if not exists.. without requestion him if he allready has an account
         */
        if ($this->_autoCreateElviUser 
            && (empty($this->_elviUser)
                || empty($this->_elviUser->usernameAtVendor)
                )
            ) 
        {
            /*
             * for now, we only create account for the loghedin user 
             */
            if (empty($this->_ispcUser)) {
                
                return [
                    'success' => false, 
                    '__ispc' => ['message' => $this->__ispc_error_code['001']],
                ];
                
            } else {       
//                 return $this->_user_create_login(); //removed in v.2
                return $this->_user_create(); 
            }

        } elseif (empty($this->_elviUser) || empty($this->_elviUser->usernameAtVendor)) {
            
            return [
                    'success' => false, 
                    '__ispc' => [
                        'message' => $this->__ispc_error_code['004'],
                        'create_new_elviUser' => true,
                    ],
            ];
            
        } else {    
            /*
             * else semi-normal login
             */
            
            if ($this->_elviUser->state == "PENDING") {
                
                $this->_user_info();
                
                if ($this->_elviUser->state == "ACCEPTED") {   
                    
                    $this->_group_addMember();
                    $this->_group_addViewer();
                }
            }
            
            /*
             * finaly try to login... we ignore the state to fetch the message from elvi if error
             */
            
            $action = 'user.login';
        
            $request = array(
                'action'    => $action,
                'userData' => [
                    'usernameAtVendor'  => $this->_elviUser->usernameAtVendor,
                    'userpassAtVendor'  => $this->_elviUser->userpassAtVendor,  
                ],
            );
                
            $this->_elviTransaction->request = $request;
            $this->_elviTransaction->save();
            
            return $this->processAndCallback();
        }
    }
    
    private function _user_login_callback($params = null)
    {
        $result = $this->_elviTransaction->response;
    
        $result['__ispc'] = [
            'iframe_url'    => null,
            'message'       => null,
        ];
        
        if ( ! empty($result['content']['authenticationToken'])) {
            
            $result['__ispc']['iframe_url'] = $this->_elvi_config['url'] . $this->_elvi_config['slash_numbersign'] . $result['content']['authenticationToken'];
            
        } else {
            
            $result['__ispc']['message'] = $this->__ispc_error_code['002'];
        }
        
    
        return $result;
    }
    
    /**
     * create If Not Exists !
     *  
     * @return void|boolean|ElviUsers
     */
    private function __ispc_findOrCreate_elviUser()
    {
        if (empty($this->_ispcUser) || ! empty($this->_elviUser)) {
            return; //fail-safe
        }
        
        $ispc_master_group = Usergroup::getMasterGroup($this->_ispcUser->groupid);
        
        $elvi_master_group = array_filter($this->__elvi_profileType, function($items) use ($ispc_master_group) {
            return in_array($ispc_master_group, $items);
        });
        
        $elvi_master_group = ! empty($elvi_master_group) ? key($elvi_master_group) : 'notSpecified';
       
        $this->_elviUser = ElviUsersTable::getInstance()->createIfNotExistsOneBy('user_id', $this->_ispcUser->id, [
            'userpassAtVendor'  => $this->_ispcUser->id,
            'usernameAtVendor'  => $this->_ispcUser->id,            
            'profileType'       => $elvi_master_group,
        ]);
    
        return $this->_elviUser;
    }
    
    private function __ispc_findOrCreate_elviOrganization()
    {
        
        $this->_elviOrganization = ElviOrganizationsTable::getInstance()->findOrCreateOneBy('clientid', $this->_ispcUser->clientid, []);
        
        $this->_organization_create();
        
        
    }
    
    
    
    /**
     * create If Not Exists !
     *
     * @return void|boolean|ElviGroups
     */
    private function __ispc_findOrCreate_elviGroup()
    {
        if (empty($this->_ispcUser) || ! empty($this->_elviGroup)) {
            return [
                'success' => false,
                '__ispc' => ['message' => 'failed __ispc_findOrCreate_elviGroup'],
            ];//fail-safe
        }
        
        $ispcGroup = Doctrine_Core::getTable('Usergroup')->findOneBy('id', $this->_ispcUser->groupid);
        
        if (empty($ispcGroup)) {
            return [
                'success' => false,
                '__ispc' => ['message' => 'failed __ispc_findOrCreate_elviGroup, cannot find _ispcUser groupid'],
            ];//fail-safe
        }
        
         
        
        /*
         * elVi answer:
         * Create a group
         * Add each user as a member who should be listed on others buddylist.
         * Add each user who should see the people from this list by default as viewer of this group.
         * But keep in mind not to blow up the contact list of each user. They are not able to delete those users coming from groups from their buddylist
         * For this purpose you have to set the group type to authorized. I changed it manually in this case and now it works.
         */
        
        $this->_elviGroup = ElviGroupsTable::getInstance()->createIfNotExistsOneBy('ispc_groupid', $ispcGroup->id, [
            'groupTitle'        => $ispcGroup->groupname,
            'userIdExternal'    => null, //owner of this group...
            'visibility'        => 'authorized',
            'groupIdentifier'   => true,
        ]);

        return $this->_elviGroup;
    }
    
    
    /*
     * this action was removed from this version 1.0.2
    private function _user_create_login()
    {
        $action = 'user.create&login';
    
        $request = array(
            'action'            => $action,
            'userNameExternal'  => $this->_elviUser->userNameExternal,
            'passExternal'      => $this->_elviUser->passExternal,
            'username'          => $this->_elviUser->username, // elvi will set this 2 as random if we don't prefill
            'pass'              => $this->_elviUser->password,
            'firstname'         => $this->_ispcUser->first_name,
            'lastname'          => $this->_ispcUser->last_name,
            'title'             => $this->_ispcUser->user_title,
            'email'             => $this->_ispcUser->emailid, // elVi password recovery
            'gender'            => 'notSpecified',// do we have a gender? male|female|notSpecified
            'profileType'       => $this->_elviUser->profileType, //"doctor",  $ispc_user->usertype or  $ispc_user->groupid  ??
            'accessToken'       => '',
            'organizationId'    => -1, //will elVi create an account for each cleintid?
            'userId'            => -1,
        );

        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
         
        return $this->processAndCallback(true, '_user_login_callback');
    }
    */
    
    
    private function _user_list()
    {
        $action = 'user.list';
        
        $request = array(
            'action'    => $action,
            'userData'  => [
                'listByState' => null,
            ],
        ); 
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
                
        return $this->processAndCallback();        
    }
    
    private function _user_list_callback($params = null) 
    {
        $result = $this->_elviTransaction->response;
          
        if ( ! empty($process_id)) {
            //@todo
            //update just this one
        }
        
        foreach ($result['content']['userList']  as $user) {      

            if (empty($user['usernameAtVendor'])) {
                continue; //fail-safe
            }
            
            $ett = ElviUsersTable::getInstance()->createIfNotExistsOneBy('usernameAtVendor', $user['usernameAtVendor'], [
                'userIdExternal'    => $user['userId'],
                'state'             => $user['state'],
            ]);
            
            if (empty($ett->user_id)) {
                //this was new... something is wrong, please check
                $_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
                $_logger->error('this elVi user does not exists in our table, please check the error : '. PHP_EOL . print_r($ett->toArray(), true));
            }
        }
        
        return $result;
    }
    
    
    private function _user_info()
    {
        if (empty($this->_elviUser)) {
            return [
                'success' => false,
                '__ispc' => ['message' => "you have no _elviUser"],
            ]; //fail-safe
        }
        
        $action = 'user.info';
        
        $request = array(
            'action'            => $action,
            'userData'  => [
                'userId' => ! empty($this->_elviUser->userIdExternal) ? $this->_elviUser->userIdExternal : null,
                'usernameAtVendor' => ! empty($this->_elviUser->usernameAtVendor) ? $this->_elviUser->usernameAtVendor : null,
            ],
        );
        
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
    
        return $this->processAndCallback();
    }
    
    private function _user_info_callback($params = null)
    {
        $result = $this->_elviTransaction->response;        

        if ( ! empty($result['content']['user']['usernameAtVendor']) 
            && $result['content']['user']['usernameAtVendor'] == $this->_elviUser->usernameAtVendor) 
        {
            $this->_elviUser->userIdExternal    = $result['content']['user']['userId'];
            $this->_elviUser->state             = $result['content']['user']['state'];
            $this->_elviUser->save();
        }
    
        return $result;
    }
    
    /*
     * 'user.update' was removed on 2.1 .. i never go a chance to make-it to work becaue it was not enabled on dev server 
     * is enabled after i call user.connect?
     */
    /*
    private function _user_update()
    {
        $action = 'user.update';
    
        $request = array(
            'action'                => $action,
            
            'userInfo'  => [
                'userId'            => ! empty($this->_elviUser->userIdExternal) ? $this->_elviUser->userIdExternal : null,
                'userNameExternal'  => ! empty($this->_elviUser->userNameExternal) ? $this->_elviUser->userNameExternal : null,
                
                "activated"         => true,
                'firstname'         => ! empty($this->_ispcUser->first_name) ? $this->_ispcUser->first_name :  null,
                'lastname'          => ! empty($this->_ispcUser->last_name) ? $this->_ispcUser->last_name : null,
                'title'             => ! empty($this->_ispcUser->user_title) ? $this->_ispcUser->user_title : null,
                'email'             => ! empty($this->_ispcUser->emailid) ? $this->_ispcUser->emailid : null,

                'gender'            => 'notSpecified',// do we have a gender? male|female|notSpecified
                
                'profileType'       => ! empty($this->_elviUser->profileType) ? $this->_elviUser->profileType : null, //"doctor",  $ispc_user->usertype or  $ispc_user->groupid  ??
                'accessToken'       => '',
                'organizationId'    => -1, //will elVi create an account for each cleintid?
                'userId'            => -1,
                
                'userNameExternal'  => ! empty($this->_elviUser->userNameExternal) ? $this->_elviUser->userNameExternal : null,
                'passExternal'      => ! empty($this->_elviUser->passExternal) ? $this->_elviUser->passExternal : null,
            ],
        );
    
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
    
        return $this->processAndCallback();
    }
    
    private function _user_update_callback($params = null)
    {
        //this fn was never finished
    
        $result = $this->_elviTransaction->response;
    
        foreach ($result['content']['internalGroupList'] as $internalGroupList) {
            //what do we update?
    
        }
    
        return $result;
    }
    */
    
    
    
    
    
    
    
    /**
     * user can only be updated from his elvi account.... this function is not implemented in api by elVi yet
     * @param ElviUsers $elvi_user
     * @param User $ispc_user
     * @return mixed
     */
    /*
    private function _user_update(ElviUsers $elvi_user, User $ispc_user)
    {
        $request = array(
            'action'            => 'user.update',
            'username'          => $elvi_user->username,
            'pass'              => $elvi_user->password,
            'userId'            => $elvi_user->userIdExternal,
    
            'firstname'         => $ispc_user->first_name,
            'lastname'          => $ispc_user->last_name,
            'title'             => $ispc_user->user_title,
            'email'             => '',//$ispc_user->emailid, // share this with elVi?
            'gender'            => '',// do we have a gender?
            'profileType'       => '', //"doctor",  $ispc_user->usertype or  $ispc_user->groupid  ??
            'accessToken'       => '',
            'organizationId'    => -1, //will elVi create an account for each cleintid?
    
        );
    
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
    
        $success = $this->processAndCallback('_user_list');
    
        return $success ? $success : json_decode($this->_elviTransaction->response);
    }
    */
    
    
    
    
    
    
    private function _organization_connect()
    {
        $action = 'organization.connect';
    
        $request = array(
            'action'            => $action,
            'organizationData'  => [
                'identifier'    => 'aVeryCrypticCode',
                'userId'        => $this->_elviUser->userIdExternal
            ],
        );
    
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
    
        return $this->processAndCallback();
    }
    
    private function _organization_connect_callback($params = null)
    {
        $result = $this->_elviTransaction->response;
    
        foreach ($result['content']['organizationList']  as $organization) {
            //what do we update?
        }
    
        return $result;
    
    }
    
    
    private function _organization_list()
    {
        $action = 'organization.list';
        
        $request = array(
            'action'            => $action,
        );
    
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
    
        return $this->processAndCallback();
    }
    
    private function _organization_list_callback($params = null) 
    {
        $result = $this->_elviTransaction->response;
        
        foreach ($result['content']['organizationList']  as $organization) {
            //what do we update?
        }
    
        return $result;
        
    }
    
    
    /*
     * Got: organization.info but its not enabled
     * is enabled after i call organization.connect?
     * 
     */
    private function _organization_info()
    {
        $action = 'organization.info';
    
        $request = array(
            'action'            => $action,
            'organizationData'  => [
                'identifier' => 'whatF-ingID cause i cant create one'
            ],
        );
    
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
    
        return $this->processAndCallback();
    }
    
    private function _organization_info_callback($params = null)
    {
        $result = $this->_elviTransaction->response;
    
        foreach ($result['content']['organizationList']  as $organization) {
            //what do we update?
        }
    
        return $result;
    
    }
    
    
    /*
     * action was removed in v2
    private function _organization_create()
    {
        
        if (empty($this->_elviOrganization)) {
            
            $this->_elviOrganization = ElviOrganizationsTable::getInstance()->findOneBy('clientid', $this->_ispcUser->clientid, Doctrine_Core::HYDRATE_RECORD);
            
            if ( ! empty($this->_elviOrganization)) {
                return $this->_elviOrganization;
            } 
        } else {
            
            return $this->_elviOrganization;
        }
        
        
        if (empty($this->_ispcUser->clientid)) {
            //fail-safe
            return [
                'success' => false,
                '__ispc' => [
                    'iframe_url'    => null,
                    'message'       => $this->__ispc_error_code['003'],
                ],  
            ];
        }

        $action = 'organization.create';
    
        $client_details = (new Client())->findOneById($this->_ispcUser->clientid, Doctrine_Core::HYDRATE_ARRAY);        

        $request = array(
            'action'            => $action,
            'organizationInfo'  => [
                //"organizationId"    => -1,
                //'NONE','PRACTICE','ORGANIZATION','SOCIETY','COMPANY'
                'organizationType'  => 'ORGANIZATION', // i've chosen this... i have no extra documentation at this time, and NONE does not work
                'name'              => $client_details['client_name'],
                
                //"street": null,
                //"poCode": null,
                //"city": null,
                //"countryCode": null,
                //"email": null,
                //"phoneNo": null,
                //"mobileNo": null,
                //"faxNo": null,
                //"website": null,
                
            ],
        );
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
    
        return $this->processAndCallback();
    }
    
    private function _organization_create_callback($params = null)
    {
        $result = $this->_elviTransaction->response;
       
        if ($result['content']['organization']['id']) {
            
            $this->_elviOrganization = ElviOrganizationsTable::getInstance()->createIfNotExistsOneBy('clientid', $this->_ispcUser->clientid, [
                'organizationId' => $result['content']['organization']['id'],
                'organizationType' => $result['content']['organization']['organizationType'],
            ]);
            
        }
        
        return $result;
    
    }
    */
    
    
    
    
    
    
    
    
    
    
    
    
    /*
    private function _accesstoken_create(ElviUsers $elvi_user, $ipid = null) 
    {
        
        $this->_ipid = $ipid;
         
        $result = '{"success":true,"content":{"accesstoken":{"creationDate":1510748386370,"id":198,"token":"4Y9MXU1V","invalidated":false,"onetimeUserName":"Dev Claudiu 822772","comments":null,"expireDate":null,"hostUserId":264,"hostOrganizationId":-1},"handledAction":"accesstoken.create"},"messages":[],"errCodes":[],"serverTime":1510748386117}';
        
        $result = json_decode($result);
        
        return $this->_accesstoken_create_callback($result);
        
        $patient =  ['fullname' => 'Dev Claudiu ' . rand(999,999999)];
        $request = array(
            'action'            => 'accesstoken.create',
            'userId'            => $elvi_user->userIdExternal,
//             'organizationId'    => -1,//$elvi_user->organizationId,
            
            'lastname'          => $patient['fullname'],
        );
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
        
        $success = $this->processAndCallback('_accesstoken_create_callback');
        
        $result_elvi = ($this->_httpService->getLastResponse()->getBody());
    }
    */
    
    /*
    private function _accesstoken_create_callback($result = null)
    {
        $r = new stdClass();
        $r->success = true;
        
        if ( ! isset($result->content->accesstoken) || empty($result->content->accesstoken->token)) {
            //something is elvi wrong, kids must wear protection
            $r->success = false;
        } else {
            
            $save_data = array(
                'user_id' => $this->_elviUser->user_id,
                'ipid' => $this->_ipid,
                'token' => $result->content->accesstoken->token,
                'token_id' => $result->content->accesstoken->id,
                'token_creationDate' => $result->content->accesstoken->creationDate,
                'onetimeUserName' => $result->content->accesstoken->onetimeUserName,
            );
            
            dd($save_data,$this->_elviUser);
            //save token in elvi-tokens
            //this fn was never finished
            
        }
       
        return $r;
    }
    */
    
    
    private function _accesstoken_list()
    {
        $action = 'accesstoken.list';
        
        $request = array(
            'action'            => $action,
            'userId'            => $this->_elviUser->userIdExternal,
        );
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
    
        return $this->processAndCallback();
    }
    
    
    
    private function _accesstoken_list_callback($params = null)
    {
        //this fn was never finished
        
        $result = $this->_elviTransaction->response;
        
        
        foreach ($result['content']['accesstokenList'] as $accesstokenList) {
            //what do we update?
            
        }
        
        return $result;
        
    } 
    
    
    
    
    private function _group_create()
    {
        if (empty($this->_ispcUser)) {
            return [
                'success' => false,
                '__ispc' => ['message' => "you have no _ispcUser"],
            ];
        }
        
        if ( ! empty($this->_elviGroup)) {
            return [
                'success' => false,
                '__ispc' => ['message' => "you allready have this _elviGroup 1"],
            ];
        
        } else {
        
            //if not exists, create one now
            $this->__ispc_findOrCreate_elviGroup();
        }
        
        
        if ( ! empty($this->_elviGroup->groupId)) {
            return [
                'success' => false,
                '__ispc' => ['message' => "you allready have this _elviGroup 2"],
            ];
        
        }
        
        
        $action = 'group.create';
    
        $request = array(
            'action'            => $action,
            'groupData'     => [
                'identifier'    => $this->_elviGroup->groupIdentifier,
                'title'         => $this->_elviGroup->groupTitle,
                'visibility'    => $this->_elviGroup->visibility,
                'userId'        => $this->_elviGroup->userIdExternal,
            ],
        );
        
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
    
        return $this->processAndCallback();
    }

    private function _group_create_callback($params = null)
    {
    
        $result = $this->_elviTransaction->response;
    
        
        if ( ! empty($result['content']['group'])
            && $result['content']['group']['groupIdentifier'] == $this->_elviGroup->groupIdentifier)
        {
            $this->_elviGroup->groupId = $result['content']['group']['groupId'];
            $this->_elviGroup->save();
        }
        
        return $result;
    }
    
    
    
    
    
    private function _group_list()
    {
        $action = 'group.list';
    
        $request = array(
            'action'            => $action,
        );
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
        
        return $this->processAndCallback();
    }
    private function _group_list_callback($params = null)
    {
        $result = $this->_elviTransaction->response;
    
    
        foreach ($result['content']['groupList']  as $group) {
        
            if (empty($group['groupIdentifier'])) {
                continue; //fail-safe
            }

            $egt = ElviGroupsTable::getInstance()->findOrCreateOneBy('groupIdentifier', $group['groupIdentifier'], [
                'groupId'       => $group['groupId'],
                'groupTitle'    => $group['groupTitle'],
            ]);
        
//             if (empty($egt->ispc_groupid)) {
//                 //this was new... something is wrong, please check
//                 $_logger = Zend_Controller_Action_HelperBroker::getStaticHelper('Log');
//                 $_logger->error('this elVi group does not exists in our table, please check the error : '. PHP_EOL . print_r($egt->toArray(), true));
//             }
        }
        
    
        return $result;
    }
      
    private function _group_addMember()
    {
        $result = $this->_group_addOrRemoveMember();
        
        if ($result['success'] == true
            && $result['content']['group.member'] == "removed"
            && $result['content']['group.removedUserId'] == $this->_elviUser->userIdExternal)
        {
            $result = $this->_group_addOrRemoveMember();
        }
        
        return $result;
    }
    
    private function _group_removeMember()
    {
        $result = $this->_group_addOrRemoveMember();
        
        if ($result['success'] == true 
            && $result['content']['group.member'] == "added" 
            && $result['content']['group.addedUserId'] == $this->_elviUser->userIdExternal) 
        {
            $result = $this->_group_addOrRemoveMember();
        }
        
        return $result;
    }
    
    private function _group_addOrRemoveMember()
    {
        if (empty($this->_ispcUser)) {
            return [
                'success' => false,
                '__ispc' => ['message' => "you have no _ispcUser"],
            ];
        }
        
        if (empty($this->_elviUser)) {
            return [
                'success' => false,
                '__ispc' => ['message' => "you have no _elviUser"],
            ];
        }
        
        if ($this->_elviUser->state != 'ACCEPTED') {
            return [
                'success' => false,
                '__ispc' => ['message' => "_elviUser must accept connection before you can add it to a group"],
            ];
        }
        
        if (empty($this->_elviGroup)) {
            $this->_group_create();
        }
        
        
        $action = 'group.addOrRemoveMember';
    
        $request = array(
            'action'    => $action,
            'groupData' => [
                'identifier'    => $this->_elviGroup->groupIdentifier,
                'userId'        => $this->_elviUser->userIdExternal,
            ],
            
        );
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
    
        return $this->processAndCallback();
    }
    
    private function _group_addOrRemoveMember_callback($params = null)
    {
        $result = $this->_elviTransaction->response;
        
        return $result;
    }
    
    
    
    
    
    
    
    
    
    private function _group_addViewer()
    {
        $result = $this->_group_addOrRemoveViewer();
    
        if ($result['success'] == true
            && $result['content']['group.viewer'] == "removed"
            && $result['content']['group.removedUserId'] == $this->_elviUser->userIdExternal)
        {
            $result = $this->_group_addOrRemoveViewer();
        }
    
        return $result;
    }
    
    private function _group_removeViewer()
    {
        $result = $this->_group_addOrRemoveViewer();
    
        if ($result['success'] == true
            && $result['content']['group.viewer'] == "added"
            && $result['content']['group.addedUserId'] == $this->_elviUser->userIdExternal)
        {
            $result = $this->_group_addOrRemoveViewer();
        }
    
        return $result;
    }
    
    private function _group_addOrRemoveViewer()
    {
        if (empty($this->_ispcUser)) {
            return [
                'success' => false,
                '__ispc' => ['message' => "you have no _ispcUser"],
            ];
        }
    
        if (empty($this->_elviUser)) {
            return [
                'success' => false,
                '__ispc' => ['message' => "you have no _elviUser"],
            ];
        }
    
        if ($this->_elviUser->state != 'ACCEPTED') {
            return [
                'success' => false,
                '__ispc' => ['message' => "_elviUser must accept connection before you can add it to a group"],
            ];
        }
    
        if (empty($this->_elviGroup)) {
            $this->_group_create();
        }
    
    
        $action = 'group.addOrRemoveViewer';
    
        $request = array(
            'action'    => $action,
            'groupData' => [
                'identifier'    => $this->_elviGroup->groupIdentifier,
                'userId'        => $this->_elviUser->userIdExternal,
            ],
    
        );
        $this->_elviTransaction->request = $request;
        $this->_elviTransaction->save();
    
        return $this->processAndCallback();
    }
    
    private function _group_addOrRemoveViewer_callback($params = null)
    {
        $result = $this->_elviTransaction->response;
    
        return $result;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public function processAndCallback($trigger_callback_fn = true, $callback_fn = null) 
    {
        
        $timestamp = round(microtime(true) * 1000);
        
        $signum_step1 = Zend_Crypt::hash('SHA512', $this->_elvi_config['step1']['password']);
        $signum_step2 = Zend_Crypt::hash('SHA512', $signum_step1 . $timestamp );
        
        $data = array(
            'credentials' => array(
                'identifier'    => $this->_elvi_config['step1']['identifier'],
                'signum'        => $signum_step2,
                'timestamp'     => $timestamp,
            ),
            'processToken' => $this->_elviTransaction->processToken,
        );
        
        //Zend_Json::$useBuiltinEncoderDecoder = true;
        $data = Zend_Json::encode($data);
        
        $this->_elvi_action =  '/startProcess'; // added like this... BUT in the docs is only one
        
        $url = $this->_elvi_config['url'] . $this->_elvi_config['path'] . $this->_elvi_config['endpoint'];
        
        $this->_httpService->setUri(Zend_Uri_Http::fromString($url));
        
        
        
        // step 1 :: connect with elvi
        $this->_httpService->setRawData($data, 'application/json;charset=UTF-8')->request('POST');       

        //step 3 :: response from elvi as string
        $result = $this->_httpService->getLastResponse()->getBody();
        
        $result = Zend_Json::decode($result, Zend_Json::TYPE_ARRAY);
        
        $this->_elviTransaction->response = $result;
        $this->_elviTransaction->save();
        
        $result ['__ispc_debug'] ['callback_fn'] = $callback_fn;
        
        $result ['__ispc_debug']['line'] .= __LINE__ . ">>" ;
        
        if ($trigger_callback_fn && filter_var($result['success'], FILTER_VALIDATE_BOOLEAN) ) {
            
            $result ['__ispc_debug']['line'] .= __LINE__ . ">>" ;
            
            $handledAction = $result['content']['handledAction'];
            
            if (is_null ($callback_fn)) {

                $result ['__ispc_debug']['line'] .= __LINE__ . ">>" ;
                
                $callback_fn = "_" . str_replace([".", "&"], "_", $handledAction) . "_callback";
            }
            
            $result ['__ispc_debug']['line'] .= __LINE__ . ">>" ;
            
            if ( ! empty($handledAction) && method_exists($this, $callback_fn)) {
                
                $result ['__ispc_debug']['line'] .= __LINE__ . ">>" ;
                
                $result = $this->$callback_fn(func_get_args());
                
                $result ['__ispc_debug']['callback_fn'] = $callback_fn;
            }
        }
        
        $result ['__ispc_debug']['line'] .= __LINE__ . ">>" ;
        
        $result ['__ispc_debug']['authenticationObject'] = Zend_Json::decode($data);
        $result ['__ispc_debug']['_elvi_config'] = $this->_elvi_config;
        $result ['__ispc_debug']['_elvi_uri'] = $url;
        $result ['__ispc_debug']['request'] = $this->_elviTransaction->request;
        
        ;
        
        
        return $result;
    }
    
    
    
}