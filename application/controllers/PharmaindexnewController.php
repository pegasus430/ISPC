<?php

	/**
	 * Class PharmaindexController
	 *
	 * This Controller is aproxy for function calls to external MMI Service Platform
	 */
	class PharmaindexnewController extends Zend_Controller_Action {

		public function init()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');
		}

		public function getinsuranceAction()
		{
			$return = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['name']));
			echo $return;
			exit;
		}

		public function getdocumentsAction()
		{
			$return = $this->callServicePlatform('getDocuments', array('productid' => $_REQUEST['pid'], 'documenttypecode' => "SPC", 'contentfilter' => '0'));
			echo $return;
			exit;
		}

		public function getdocumentsbaseAction()
		{
			$return = $this->callServicePlatform('getDocuments', array('productid' => $_REQUEST['pid'], 'documenttypecode' => "BI", 'contentfilter' => '0'));
			echo $return;
			exit;
		}

		public function getmoleculeAction()
		{
			$searchText = $_REQUEST['searchtext'];
			$searchText = explode(' ', $searchText);

			for($i = 0; $i < sizeof($searchText); $i++)
			{
				$searchParams['name'] = $searchText[$i];
				$return = $this->callServicePlatform('getMolecules', $searchParams);
				$moleculeId = json_decode($return);

				$search[] = $moleculeId->MOLECULE[0]->ID;
			}

			$searchParams = '';
			$return = '';
			$sizeOfSearch = sizeof($search);

			if($sizeOfSearch > 0)
			{
				$iknrGroupId = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ik_no']));
				$iknrGroupId = json_decode($iknrGroupId);
				$iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;

				$searchParams['moleculeid_andlist'] = $search;

				$searchParams['moleculetype'] = 'ACTIVE';
				$searchParams['maxresult'] = 100;
				$searchParams['insurancegroupid'] = $iknrGroupId;

				$return = $this->callServicePlatform('getProducts', $searchParams);
			}

			echo $return;
			exit;
		}

		public function getproductsAction()
		{
			$iknrGroupId = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ik_no']));
			$iknrGroupId = json_decode($iknrGroupId);
			$iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;

			$searchParams['name'] = '' . $_REQUEST['searchtext'] . '';

			$searchParams['moleculename'] = null;
			$searchParams['companyname'] = null;
			$searchParams['moleculetype'] = null;
			$searchParams['fulltextsearch'] = $_REQUEST['sm'];
			$searchParams['disabledobjects'] = array();
			$searchParams['maxresult'] = 100;
			$searchParams['pzn_orlist'] = null;
			$searchParams['insurancegroupid'] = $iknrGroupId;

			$searchParams['assortment'] = null;
			$searchParams['tolerance'] = 0;
			$searchParams['sortorder'] = 'NONE';

			$return = $this->callServicePlatform('getProducts', $searchParams);
			
			echo $return;
			exit;
		}

		//used in patient medications livesearch
		public function getproductsmedilsAction()
		{
			$iknrGroupId = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ik_no']));
			$iknrGroupId = json_decode($iknrGroupId);
			$iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;

			$searchParams['name'] = '' . $_REQUEST['searchtext'] . '';

			$searchParams['moleculename'] = null;
			$searchParams['companyname'] = null;
			$searchParams['moleculetype'] = null;
			$searchParams['fulltextsearch'] = $_REQUEST['sm'];
			$searchParams['disabledobjects'] = array();
			$searchParams['maxresult'] = 100;
			$searchParams['pzn_orlist'] = null;
			$searchParams['insurancegroupid'] = $iknrGroupId;

			$searchParams['assortment'] = null;
			$searchParams['tolerance'] = 0;
			$searchParams['sortorder'] = 'NONE';

			$return = $this->callServicePlatform('getProducts', $searchParams);

			
			if($return)
			{
				$drop_array = json_decode($return);

				$i = "1";
				foreach($drop_array->PRODUCT as $key => $val)
				{
					$droparray[$i]['id'] = $i;
					$droparray[$i]['name'] = $val->NAME;
					$droparray[$i]['comment'] = "";
					//this is the increment to know which line to fill in admission medis form
					$droparray[$i]['row'] = $_REQUEST['row'];
					$i++;
				}

				// Maria:: Migration ISPC to CISPC 08.08.2020
				if($_REQUEST['client'] && strlen($_REQUEST['client'])>0 && is_numeric($_REQUEST['client']) ) {				
				// insert personal data
				$clientid = $_REQUEST['client'];
				$modules =  new Modules();
				$clientModules = $modules->get_client_modules($clientid);
				
				    $querystr = "
    			     select m.id,m.name,m.comment, m.pkgsz from
            			(select distinct(name),min(id)as id, package_size as pkgsz, comment as comment
            			from medication_master
            			where clientid = '" . $clientid . "'
            			and extra=0
            			and isdelete=0
            			group by name)as m
            			inner join medication_master b on m.id=b.id
            			where(trim(lower(m.name)) like trim(lower(:search_string)))
            			and isdelete=0
            			and clientid = '" . $clientid . "'
            			and extra=0";
				    
    				$manager = Doctrine_Manager::getInstance();
    				$manager->setCurrentConnection('SYSDAT');
    				$conn = $manager->getCurrentConnection();
    				
    				$query = $conn->prepare($querystr);
    				
    				$search_string = addslashes(urldecode(trim($_REQUEST['searchtext']) . "%"));
    				$query->bindValue(':search_string', $search_string);
    				
    				$dropexec = $query->execute();
    				$personal_drop_array = $query->fetchAll();

    				foreach($personal_drop_array as $key => $val)
    				{
    				    $personal_droparray[$key+100000]['id'] = $val['id'];
    				    $personal_droparray[$key+100000]['name'] = html_entity_decode($val['name'], ENT_QUOTES, "UTF-8");
    				    $personal_droparray[$key+100000]['comment'] = html_entity_decode($val['comment'], ENT_QUOTES, "UTF-8");
    				    //this is the increment to know which line to fill in admission medis form
    				    $personal_droparray[$key+100000]['row'] = $_REQUEST['row'];
    				}
    				
    				// Maria:: Migration ISPC to CISPC 08.08.2020
    				//$this->view->personal_droparray = $personal_droparray;
        			$show_personal = "1";
        			//ISPC - 2439
        			if($clientModules['192'])
        			{
        				$this->view->firstarray = $personal_droparray;
        				$this->view->secondarray = $droparray;
        				$this->view->firsttitle = $this->view->translate('PERSONAL RESULTS');
        				$this->view->secondtitle = $this->view->translate('MMI RESULTS');
        			}
        			else 
        			{
        				$this->view->firstarray = $droparray;
        				$this->view->secondarray = $personal_droparray;
        				$this->view->firsttitle = $this->view->translate('MMI RESULTS');
        				$this->view->secondtitle = $this->view->translate('PERSONAL RESULTS');
        			}
        			//ISPC - 2439
				} else{
    			     $show_personal = "0";	
				}
				
				$this->view->show_personal = $show_personal;
				$this->view->droparray = $droparray;
			}

			//init is stopping the zend renderer for all functions... so we use this
			echo $this->view->render('pharmaindex/getproductsmedils.html');
			exit;
		}
		
		//used in patient medications livesearch
		public function getproductsmedils_150825Action()
		{
			$iknrGroupId = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ik_no']));
			$iknrGroupId = json_decode($iknrGroupId);
			$iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;

			$searchParams['name'] = '' . $_REQUEST['searchtext'] . '';

			$searchParams['moleculename'] = null;
			$searchParams['companyname'] = null;
			$searchParams['moleculetype'] = null;
			$searchParams['fulltextsearch'] = $_REQUEST['sm'];
			$searchParams['disabledobjects'] = array();
			$searchParams['maxresult'] = 100;
			$searchParams['pzn_orlist'] = null;
			$searchParams['insurancegroupid'] = $iknrGroupId;

			$searchParams['assortment'] = null;
			$searchParams['tolerance'] = 0;
			$searchParams['sortorder'] = 'NONE';

			$return = $this->callServicePlatform('getProducts', $searchParams);

			if($return)
			{
				$drop_array = json_decode($return);

				$i = "1";
				foreach($drop_array->PRODUCT as $key => $val)
				{
					$droparray[$i]['id'] = $i;
					$droparray[$i]['name'] = $val->NAME;
					$droparray[$i]['comment'] = "";
					//this is the increment to know which line to fill in admission medis form
					$droparray[$i]['row'] = $_REQUEST['row'];
					$i++;
				}
				

				$this->view->droparray = $droparray;
			}

			//init is stopping the zend renderer for all functions... so we use this
			echo $this->view->render('pharmaindex/getproductsmedils.html');
			exit;
		}

		public function getpricecomparisonAction()
		{
			//GET INSURANCE COMPANY GROUP ID
			$insid = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ikno']));
			$insid = json_decode($insid);
			$insid = $insid->INSURANCECOMPANYGROUP[0]->ID;

			$return = $this->callServicePlatform('getPackages', array('pricecomparisonpzn' => $_REQUEST['pid'], 'maxresult' => 100, 'insurancegroupid' => $insid));
			echo $return;
			exit;
		}

		public function getamrAction()
		{
			$return = $this->callServicePlatform('getAMR', array('productid' => $_REQUEST['pid'], 'regulationtypecode_orlist' => array(1, 3, 4)));
			echo $return;
			exit;
		}

		public function getpackagesAction()
		{
			$return = $this->callServicePlatform('getPackages', array('productid_orlist' => array($_REQUEST['pid']), 'insurancegroupid' => $_REQUEST['ikno']));
			echo $return;
			exit;
		}

		public function getcatalogsAction()
		{
			$return = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => '109028580'));
			#echo $return;
			$return = json_decode($return);
			echo $return;
			exit;
		}

		public function getatcAction()
		{
			$iknrGroupId = $this->callServicePlatform('getInsuranceCompanyGroups', array('iknr' => $_REQUEST['ik_no']));
			$iknrGroupId = json_decode($iknrGroupId);
			$iknrGroupId = $iknrGroupId->INSURANCECOMPANYGROUP[0]->ID;

			$search[] = strtoupper($_REQUEST['searchtext']);
			$searchParams['atccode_orlist'] = $search;

			$searchParams['moleculename'] = null;
			$searchParams['moleculetype'] = null;
			$searchParams['maxresult'] = 100;
			$searchParams['sortorder'] = 'KBV';
			$searchParams['insurancegroupid'] = $iknrGroupId;

			$return = $this->callServicePlatform('getProducts', $searchParams);

			echo $return;
			exit;
		}

		public function getcompanyAction()
		{
			$searchParams['companyid_orlist'][] = $_POST['companyId'];
			$return = $this->callServicePlatform('getCompanies', $searchParams);
			echo $return;
			exit;
		}

		public function getproductsbycompanyAction()
		{
			$searchParams['companyname'] = '%' . $_REQUEST['searchtext'] . '%';
			$searchParams['maxresult'] = 100;
			$return = $this->callServicePlatform('getProducts', $searchParams);
			echo $return;
			exit;
		}

		public function getsuggestionsAction()
		{
			$searchParams['searchname'] = '' . $_REQUEST['searchtext'] . '';
			$searchParams['resulttype'] = 'PRODUCT';
			$searchParams['toplistsize'] = '10';

			$return = $this->callServicePlatform('getSuggestions', $searchParams);

			echo $return;
			exit;
		}

		/*
		 * @param $function: (getCompanies, getProducts, ..)
		 */

		private function callServicePlatform($function, $params = 0)
		{
			$url = "http://" . Zend_Registry::get('mmilicserver') . "/rest/pharmindexv2";

			$logininfo = new Zend_Session_Namespace('Login_Info');

			$licensekey = Zend_Registry::get('mmilicserial');
			$licensename = Zend_Registry::get('mmilicname');

			$curl = curl_init();
			$url = $url . '/' . $function . '/' . $licensekey . '/' . $licensename . '/';
				
			if($params != 0)
			{
				$params = urlencode(json_encode($params));
				$params = str_replace('+', '%20', $params);
			}
			else
			{
				$params = "{}";
			}

			$url = sprintf("%s%s", $url, $params);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			if($_REQUEST['urlx'])
			{
				print_r(urldecode($url));
			}
			$result = curl_exec($curl);
			curl_close($curl);

			return $result;
			exit;
		}

	}
	