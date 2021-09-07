<?php

class ContactController extends Zend_Controller_Action
{
	public $act;
	public function init()
	{
		/* Initialize action controller here */
	}

	public function contactAction()
	{
			
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		if($this->getRequest()->isPost())
		{
			$contact_form = new Application_Form_Contact();
			$contat = $contact_form->validate($_POST);

			if($contact_form->validate($_POST))
			{
				$contact_form->SendMail($_POST);
				$this->view->error_message_ok = $this->view->translate('thankyouforcontactingusourtechnicalteamwillcontactyousoon');
			}else
			{
				$contact_form->assignErrorMessages();
			}
		}
			
		$loguser = Doctrine::getTable('User')->find($logininfo->userid);
		$logarray = $loguser->toArray();
		$this->view->name = $logarray['last_name']." ".$logarray['first_name'];
		$this->view->emailid = $logarray['emailid'];
	}



	public function golmalAction()
	{
		
		$this->_redirect(APP_BASE."error/previlege");
		exit;
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		if($this->getRequest()->isPost())
		{

			foreach($_POST['name'] as $key=>$val)
			{
				$health = Doctrine::getTable('FamilyDoctor')->find($key);
				$health->first_name = $val;
				$health->last_name = $_POST['lastname'][$key];
				$health->street1 = $_POST['street1'][$key];
				$health->save();
			}

		}
		$offset = $_GET['pageno']*100;
		$this->view->pagenoplus = $_GET['pageno']+1;
		$usr = Doctrine_Query::create()
		->select('*')
		->from('FamilyDoctor')
		->limit("100")
		->offset($offset);

		$usra = $usr->execute();
		if($usra)
		{
			$healtharr = $usra->toArray();

			$inputs = "";
			foreach($healtharr as $key=>$val)
			{
				$fname = $val['first_name'];
				$lname = $val['last_name'];
				$street1 = $val['street1'];
					
				$inputs .= '<input type="text" name="name['.$val['id'].']" value="'.$fname.'"><input type="text" name="lastname['.$val['id'].']" value="'.$lname.'"><input type="text" name="street1['.$val['id'].']" value="'.$street1.'">';
			}
			$this->view->inputs = $inputs;
		}
	}


	public function golmal1Action()
	{
		$this->_redirect(APP_BASE."error/previlege");
		exit;
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$this->_helper->viewRenderer('golmal');
		$offset = $_GET['pageno']*100;
		$this->view->pagenoplus = $_GET['pageno']+1;
		$ipids = array
		(0 =>'00436b6fa9c36b993f4952c5245e393805f438f3',
				1 =>'004d8ace798f3dcb91f128f95d8e67f83f13dcbd',
				2 =>'005bd47d5dbcf0684b4f76c6dd915efb9a13e58d',
				3 =>'0218f87660ff01b09f23c5df62e7c2e614c15bf6',
				4 =>'02a0443a6b6495ed3a265bd24a63f2319d44c411',
				5 =>'031a401015328a85ee1b9fbef20515f65ae617bb',
				6 =>'045ff177a8b1ed555970c006190d9c85433a8782',
				7 =>'04f052a2e78456b553752a74ca341de9b6e598e6',
				8 =>'05d2540288b83c494723fbb390b583e30c04f57b',
				9 =>'05e4e3537c4ed13e98ac04f0e90a8f0c7ddf761a',
				10 =>'062df1ede38fdd26ba4248c3442b8ec85dca6f9f',
				11 =>'067fe25f4074d8119eff1a9a2ae7216e96cd4188',
				12 =>'06b4573f09876703a9012390546539aa0bc079c2',
				13 =>'06b5e84b4d2caf4e3fb03d012744f821a61501fc',
				14 =>'06bede700aba53bf49ad3ef1a289ae9c78bb2db7',
				15 =>'06c62dc5788f386a79f62cafeffb34650fa81cd3',
				16 =>'0712b1ce30ad9ca51757b0dfe92c93bbf185e21b',
				17 =>'0793b3107f5e4aa0e55ad4a5b5795eb8860b8389',
				18 =>'08d08f727563c4e48462f45fd5af522790cd03f8',
				19 =>'09244bee73720e6cea0aa3ec9e0ff5ed8d4c4fa0',
				20 =>'09e6044849600d8ec9acb5899c7497bdcfde61ee',
				21 =>'0ad5813b64bc4d3caa156fc915c44b1905887449',
				22 =>'0c556c35d88f13e3841fd59cfefda5041899feb9',
				23 =>'0cf893c30a8b7e803288f461ea01e5d67be52256',
				24 =>'0d325c798f1bfdf5b2932346a58cfc576e540787',
				25 =>'0d42ee53773a3dacd24abcd861f90247c747cfff',
				26 =>'0e1c1c51d4eaf3ea7cf832a9bacf41e6d79636f9',
				27 =>'0e1fd412d751d40fb907b6ef97a63a52fc46f9a1',
				28 =>'0e33a7967c51bbd99bebffd00d88f0563b329c06',
				29 =>'0e4ca210ef633147e2403ed82404bfaa8f023637',
				30 =>'0e69953f7a7f73f0408acd5b0ff6e4a337b35b2a',
				31 =>'0f26ccbd93143178d9ed2286a71f0170c8296fdc',
				32 =>'0fbedeb5f707be3d0dec79ea3029f14228751235',
				33 =>'0fd926e6a29f3d31f56c8539c2431b944198bf34',
				34 =>'10c53d2f4e886077830d5f15a5c81832f85ac5dd',
				35 =>'1114d136b60614a694d0064951129802aac4435b',
				36 =>'11621d8b16ab17ba89b6d4e4cf845f178f94b169',
				37 =>'11fd52eac6aeaa9b34a31ece8d22e96add351042',
				38 =>'124e8541f5d5f994ba048b3d8b1e108adc997814',
				39 =>'12a9b28c230fe06d961f80146aac7b2f71e6a7c8',
				40 =>'1387b233c804a00affd1518d4964ed387c09d7a3',
				41 =>'13bd102365554fded0d9ffd458dce80ce4ab93aa',
				42 =>'148d35325612e5d61ba7963a60df3c2a5cb43c41',
				43 =>'150c702d5a95677fddc9c943c31fd9203a6127e7',
				44 =>'156288d5ed20e06c0159e4b67734e00c2d2b5d67',
				45 =>'15b987c1e11cbcf4ef51e5295c827c7076bf6d5d',
				46 =>'162c3b71cc415c9ad0f41e3faf8600bdd84fc045',
				47 =>'168e5ed20b83a438f533e43343d02e3c1e2e33ab',
				48 =>'16944aad156cf804e78bebe3b3e5b624d5d03fae',
				49 =>'16d137f627041763b6ac84799886d1f3ad4e1a6d',
				50 =>'16ec5240770adb0897036ff2b02094a5ec1e12e8',
				51 =>'184b920d94e32410fccf05910ff4790060d0f16f',
				52 =>'18dcdd4b2901a6cb19692383bc3440262adb78d0',
				53 =>'19400420c66d80a0e47a472200455e3e4cc75a0a',
				54 =>'198b15b8f2e2c944b6a46bf4250ad445a73a4671',
				55 =>'1a4069e5647b9a5893a5f9a3d479d825ca0eac48',
				56 =>'1a6b68162f4b741f1df98dc861cbbe30a6c898e7',
				57 =>'1af87dd19ea74ecdc54a9cf31245f679e12562fb',
				58 =>'1ba3d89a781217dd0ff92598b2a7637b0649c07a',
				59 =>'1bc19deb31a5f1d30b4603b9551ea8213a04da79',
				60 =>'1bce8a2fb3a4a8c3164edda50741818e4ef8d63e',
				61 =>'1be1c16b9853d550cb104f27cb3f4c4a761fde57',
				62 =>'1bf991af251ec092ff6d9441d7fb4404681d2e07',
				63 =>'1c34258ce9de7c4ad875bfd9c381dc5a7d173497',
				64 =>'1d0b181125bbbd86e95eca632091a82fec5f866d',
				65 =>'1dd797edcc7fb042097505f4f5c956cf90be1cc2',
				66 =>'1ecc72e59e127658b13c3046157cd558e4f92bf5',
				67 =>'2093cec41b8c897f2ca11adaf086c84e51d5c126',
				68 =>'20b322d5659b8c02299fb803842f3c9f98076474',
				69 =>'213da87ea5f3b48c5a6c70d2d2d91db83d4e6823',
				70 =>'22d72018e7af36d04162f4dcb1cbffd620fc55f4',
				71 =>'2308fd20cf484d71c3a5886a319d8da6dac96792',
				72 =>'23640aa70dcf75fbc8efef2d4721f3823b09b1b9',
				73 =>'2381eee93f040bbe70614f22cde6a8cf88f3be27',
				74 =>'240430109b200517821ddab4d283a813fd929317',
				75 =>'240584de68b255652934924c29dc7a8a6fd5235b',
				76 =>'243f73f022fab7bdc9b83cc8858f0f4faaa3a734',
				77 =>'24dfd18c467b29c5a983998d874e69fee10b9c74',
				78 =>'26edb3c495d4d4ff4d62f057fcdac54ed45c5a31',
				79 =>'270ae765bd16cb10978999d19e20c41552218a39',
				80 =>'271dccd80e972af13ad202beae221a7af448fee5',
				81 =>'274c71a4a654de8e500460beea4f921e508f721c',
				82 =>'278e216cab8f613ab5a86d5d068a8f11aecc55f1',
				83 =>'27e50a20a3dd53789155313492b6637d9e4b6c8d',
				84 =>'28287fb2c442da273b13d76c8497c86613c0b1d9',
				85 =>'28313e7fcd0795f812221651bd52361a38988657',
				86 =>'294b9e0d66904dd2243a6c7af80ebd2211a6469c',
				87 =>'2957b6381d011015cd287f9073f4ad1a46a50ea1',
				88 =>'297fed1efc752d5bfbd970dcaae9ad42d7ce10ea',
				89 =>'2c1bbd93cee8c1772af04f2eded6bacba751d38d',
				90 =>'2d8880a5630474c5ae51df55bf602764ea10241d',
				91 =>'2e9bc98909795521e0953a69dfe91c86792d5e5a',
				92 =>'2f69fcb9d92f3eba677e4b0a8f56a49be6e016d0',
				93 =>'2fdfbd9b75de68e501c2de83cf9282148af69865',
				94 =>'308a5c3fec99282bde4954a4df1abddb70159ea7',
				95 =>'308b12f5c4de8d9731c9f133a891e0b460472af4',
				96 =>'310cc8fc38999b1b74dd64e884a15ca81538a521',
				97 =>'3188e07b271a8b511b9e9296f9674f4270fd17e2',
				98 =>'319666e7a48dd770ac77feccba8ea55b131b588c',
				99 =>'31e7b402b4ceebfe64068bdf47468cb7391db091',
				100 =>'329121f64e59279292b7bdceff6be496892f986b',
				101 =>'33288563a6d5b55e795f43aa0de2495349a4a9d2',
				102 =>'335d1d4744502dacde060f71fce190e474e2f436',
				103 =>'338f6fc3f2e181dbe6ff4ae98fc1e3e9074c5466',
				104 =>'33dfabb2133ba2c804404af91e5c5b3b2c3ff0ba',
				105 =>'33f757bc5f4b234e01546c47f3af1b5f712b0e00',
				106 =>'34f53cecdfb7b0b6aabccda431c77d832221fed7',
				107 =>'366af2ca3d337c9b09d9d79446f5bb812eecde37',
				108 =>'36ce98101d50226285c72f5ddf0c0dfe2a4044cf',
				109 =>'36dbc6936c70b0d315410b236e2849348231af0f',
				110 =>'37d529291f4a1a06dc4bbf5b16f34eec5282bbad',
				111 =>'380252d112b2d8a08e5cb4759c8154295f6c64be',
				112 =>'388d7162c5a9c13efe299b3946e3d2be47fd1512',
				113 =>'38b4caf76e99dcc1a231cd0e2f97d5241a07076d',
				114 =>'39353b1f9f321f9c3a3372dd30d93575c45ba69c',
				115 =>'3a5d79160315e14cd6a9c3bed9aaa31e789860ef',
				116 =>'3baf6d014dc426a44f833433f70324d45d0f68c1',
				117 =>'3be8557016cb74b3b6f3e5f02e13e63206758fbd',
				118 =>'3c888f8db92db51c381dbba28083b6e04526df8c',
				119 =>'3ce228975d87438924cd1496815bdbe71b2ee25e',
				120 =>'3e1f0d2b24b82da034899e5fe44407b9ae931612',
				121 =>'3f4c603d6dc68c003f288efeb14ed6538c5d56d7',
				122 =>'408302a4fb6d09b4027d1cd384f317c6cd274a58',
				123 =>'4108565e80dabe62e5d6850e1546aa8c0b6a2b7c',
				124 =>'419d8e4aec54df0894fda5833f40a1e404791694',
				125 =>'41e468826609d0833cc71947516b76ef34ea2b3c',
				126 =>'4217bc4d59c9deb36fa9c0d0f02187400055c9f3',
				127 =>'421f03dd29bec56c2ae7f25724103119dc84fd6d',
				128 =>'433cd8e6ee1e836bca92cbcf5bdea6784a178d02',
				129 =>'43ab05e38d0eb1b59357ebdbd0864aa373d52042',
				130 =>'442463592472bfea58305821c28b4cba977d451c',
				131 =>'4522bdf52c2b5d9c604ccec5a578815afba599f0',
				132 =>'4583bf3df5ddad8cd59ab6a17f4d930911599605',
				133 =>'45912929c53c83498aeb2c17d6df2e869bed5cea',
				134 =>'4633158bb0632e7d3f37ae80a91955cc35a034b0',
				135 =>'46fc2ce864a330be3776653e21b515b318929ba9',
				136 =>'470e1bd4d16e5a30b5138e9e1d472c97896e2322',
				137 =>'48cb363402b00247abf746455c56fcdf6cbc07dd',
				138 =>'4a0e75cad9939657393963e3aa7c602fc89cbeb9',
				139 =>'4b1cd6b2cae70c453ec098bdcaeb187cea4073df',
				140 =>'4b61cdc6eecea3ee354d05c2c8a1c270c2f2c104',
				141 =>'4b8d582aa36a56941df8b8f46fc8623e948de39e',
				142 =>'4c0f8de3d7b6a51ea2a9ccd8e8421c8e8420d056',
				143 =>'4c43900e438d7f9c4ec475e258246a47b4a1d879',
				144 =>'4ca28c3cd4697f24e63bd08603e4460144190eea',
				145 =>'4cc4793b5393227f6cde63522df85f8c5f7c8e92',
				146 =>'4d1aaddd7004920740dd247486632beba5f26f04',
				147 =>'4d377b36aa155b09093604d27ea9b08d0adabe7e',
				148 =>'4d4530a68616c841c4c8abf5feeab23b05a0d0cf',
				149 =>'4d72a1d2e9ed0a0615abc910133cc0e83c4f5375',
				150 =>'4e453db87d9e6a8941a73ecbe4744db6b43ec8d8',
				151 =>'4eb23b9a8c1938a255a26bf139ee8851d2c5f985',
				152 =>'4f3af0f1db17384f401ef529e48a103d484e5d2c',
				153 =>'4f5de2f33593e13e8bf63700c1f546d177affe5c',
				154 =>'4f68e14f8854548fe2b4ab98bbddfe7833dda48e',
				155 =>'501a0b68a6e707a15abcf5c62678c08e6e569844',
				156 =>'509765151bc0f9ce92e707188d295cf46b645ed1',
				157 =>'50cbedf215e3cb3e9b24d7ab540da1a87d2d325c',
				158 =>'511c0727975f276bcea307c1ed054a1ac51c1759',
				159 =>'511d75c09833bacd3c307b8e73f302a0161336e9',
				160 =>'51cd2d3827cb33f2a6cb56cada5521e80fc4ff56',
				161 =>'51f489e996ce327b17800f6fed526237edce2a03',
				162 =>'529f77790cbf3ce578ac660621a102eb0378d19d',
				163 =>'545b859bcecb232bb67caac969aa1cc51a8a0b45',
				164 =>'5470ddad92b9979ef7b8c659cbc84ae38a6ef078',
				165 =>'54df37435e2042c0d8827d1b56de97ecbabd2d1f',
				166 =>'5565707b5874c2d4152f2004c5c5929615c88da2',
				167 =>'55a85c44be00071a11cb0ea37111da92e7ee25fe',
				168 =>'55edfcfd5b0977b74110edecd9a242ec6f286580',
				169 =>'560a19a9a7a0001e0bc2a31e6c6230a6635e3100',
				170 =>'567647dbd0feb9d473db2cc456a7d46fe64f97af',
				171 =>'5709c391d60dc3d4bcb80453d1d233d3d54f65fc',
				172 =>'58508d745bd6604ade2fa0a4d34c85e970bff48e',
				173 =>'58d65d77cbce8417c8ce33d2694ceaaff531647c',
				174 =>'5917d3767cb2143813cfb25085cc2fbf821df0f2',
				175 =>'597ed1c6c6d06cdf851707b2e5487fe12db8bc65',
				176 =>'5a38a1b709e0b72ce7ad4beb51f55b4960a55265',
				177 =>'5c649e850064e2cbeef60803d5ccda729ca38f29',
				178 =>'5c877f831c698ef75c6f0d06e44f0fbcb97785e4',
				179 =>'5d6da81d380231fca4d73cb9259ae8cf8b1e5a2d',
				180 =>'5e0cbf0d0cd6267ee3056d366ca6cfef3b6accc6',
				181 =>'5e8825c58cdd471f4f29d0f6533c96600ff033ed',
				182 =>'5f84fecbabfb9d0b1b4071675e125fb466285b1d',
				183 =>'5fa3574159522fe6fc17317e4b3770d551189c81',
				184 =>'5fd709bbd97906e632182cff134020cb8ce86e63',
				185 =>'604d10a3d148fb7ed45c6adbdda5fb0cfd0d9ef9',
				186 =>'615e862486fd1bcbc733f58da25639dc8b0d666c',
				187 =>'622fb5cb06febfaad7acb37cbb8a7ab08789356d',
				188 =>'6233106803770a9907437b653b53f96228ea613b',
				189 =>'625337293c5a6697a37bb94727654c07738c18b2',
				190 =>'626915f13b0162af98de80be2c8206c8b7f189b7',
				191 =>'6314bf67f3df21f5d2a100fb6187985ea4060614',
				192 =>'6377f33f428110bba2babb8b574e744a825e8047',
				193 =>'6442ac9e2708a08cfa92c3e72d4b840d0df46f7b',
				194 =>'654eef024348709bbd1cd1692ae124c3c6190782',
				195 =>'656e74409339cc01db674a819b24e2a896e21f7a',
				196 =>'659b00c2638ca32fc48dab81050b7fa09b6fae6c',
				197 =>'661f3f153a4f90c2aee3624f4bb5d3aeb5551085',
				198 =>'66615a7f5b88e90f119641b4b65c874ba9f73af9',
				199 =>'668001ddd3a5def6102ffb1ed6f0a60dbfbca64c',
				200 =>'66c882cf7d1b5d80b780cb53d9129c8de2042a4a',
				201 =>'68559ad851113e5489811e8382aff9d67d3561e0',
				202 =>'68d1d7479b95830e2e33024b1e7a446bf4f0cb34',
				203 =>'68d5852292c99ba996c1888f9eb87a8a0e430d7b',
				204 =>'6926a834126b395f49f4a545c38833081ef426eb',
				205 =>'692bdb43c0c5ff9b5fef2524ec7507a99c96edb8',
				206 =>'694d31ebc4cdabad7f32f548f3331d06a081dc87',
				207 =>'69ae25e59ffce6632b3d1ca1e12abf07478a85e5',
				208 =>'6a331f69e3389ee65baf163ab264f4fe426746ae',
				209 =>'6ca99519e5eeeb93097e16d2aa586a1cc88f7995',
				210 =>'6cd00d4b1242028fff2a10a35ce94802b6f870e5',
				211 =>'6d1e95c0924a01737166b6b52f94064a06d97237',
				212 =>'6d6e083ba9fae96be65752cca13a397ad3ae5ab9',
				213 =>'6da53bf7d05e9f49f01d39c8adbae93b2533e0ea',
				214 =>'6dfffff829ca8a282d6c56ed342d060efece2d27',
				215 =>'6e0331837bbd30631bbedb90066119122280937e',
				216 =>'6f1adcdc42efbd1edff91ae11e6a050bb20ba8d9',
				217 =>'6fb6c4abaaa1737ca2811485fd5d94675a7a8808',
				218 =>'702b03d050116ae2b54b0ca30c323836917613dc',
				219 =>'70bfd0ee09a73755d48ee7d752945e7b59bbfd90',
				220 =>'711c7ad5e76ec83e59b51d66cea2efebe1fc7a9b',
				221 =>'72f990cde5ad35f851b5a8d73776546254ccdd09',
				222 =>'738a2e4b762d88b2978cc529be09ebb10551e625',
				223 =>'7585e6f2969b434cd9cfcaf31b8d73ddf530a724',
				224 =>'75f59dca46a0b95c233d47c416c59f6396d28be8',
				225 =>'7718d00c9274098b1a9d6152bc80fe602cce8d73',
				226 =>'772c19e818dec0c22af7d40ab99531e3ef72368e',
				227 =>'77f2ce35e882b5b5a0f40361f7741a6374b99228',
				228 =>'7833889f99934d8f5f8af843b6f8b71932c34aab',
				229 =>'7847c43677ce26a12835ba1987c536e73aa4c06b',
				230 =>'797ac3c8a017e02f781182e0e0628a21c6e71ba1',
				231 =>'7a4e9b3b9054df1ce92bf48118e0b4fd96b7c146',
				232 =>'7b8e757ce5488c04e74bd7a05b41a5faa366dc05',
				233 =>'7c5227f8031f17fe9f453455c8223cef96425602',
				234 =>'7cd1532366d7d65fc86606ba91842a263447639a',
				235 =>'7db027c34bc1402bfb66c551d64a54d6f588328c',
				236 =>'7dcbcfe26920ff774ca884fdc19f8533e44c0d15',
				237 =>'7ec59a72f9e49c5904b3b05aa1f04d3b2fcb21b3',
				238 =>'7f5b1760c4a7478c0d349290be6131b2089c921b',
				239 =>'801504b77359673ee2e22b66b0a58bb25a5c4b30',
				240 =>'805759848b091869159dbfb0dc5053823dbbfc75',
				241 =>'8062196836807f91818d32eb898c75632a783e90',
				242 =>'80d821a4526b14835f03cbffd9314c5f0350f74d',
				243 =>'815e59cc3baa0e9322997837a86ad32ae7515e56',
				244 =>'81a40d668273f86e4622aa781bfcf7dfaa291a0c',
				245 =>'81e5930bc4cf0ffe9cf7f5f998dc9e6c4c30afbe',
				246 =>'8289c0596a763bd0e2358ec548c8f32006c625c6',
				247 =>'830e6bc5e47f1ec0f5be5777c050c01e930554f0',
				248 =>'843c053f2a1fa8423eeec0ce486734f61c4a190b',
				249 =>'8443b8ba73462808baa1cae165e7703c6a3212a2',
				250 =>'84cabd3c1fec1739389909af072138c506b6c46b',
				251 =>'8540309bca7147e3f034144a47cb635481513989',
				252 =>'8739ac72493455d8003afc4d03e42f8d22bcbe81',
				253 =>'8756cbeee22c54c2270a707e4451354fd7d607b8',
				254 =>'87ab8d11277ed5c0fca7904f198e8d15e9da2366',
				255 =>'89924ad036941afdb3c900691237c539535d0a14',
				256 =>'89f6f5073840c165e8670757636a9c3d9aab6941',
				257 =>'8a5ac0965ce7bbc70081d8e3ca884ef27df900da',
				258 =>'8ab1a057432bb8881491fa8b74a85aa42d7bb464',
				259 =>'8addf38209db9534a12f5fef8ce5f18151704216',
				260 =>'8b267e245d06e67d5c391cda1a57c07960edd084',
				261 =>'8b86f83b12435b634bcf43ad4e53e0d89a3600c6',
				262 =>'8baecc92a1353b199bcf1fa901437f9adc1946f2',
				263 =>'8c30ea3b8eccc80a3e36e6cdf2292d010b35a7f5',
				264 =>'8c54464ae8d0bb7c91c992503b8d25d5c866184a',
				265 =>'8c5da3142da7b53ff250d4dcf179d12539462642',
				266 =>'8caf689d943def5202013e156df692e8572d0541',
				267 =>'8d289188b0f296b9e6acdf9cdcc2111a6670075e',
				268 =>'8d6950228597d68fa9f4340937b852a7e4f1a46e',
				269 =>'8e626e5841836690d7799d498aaf5dc8f1791c6c',
				270 =>'8f0f2e605246b92e1892e888f7b3a72c26a8f72b',
				271 =>'8f1b177c3d1a3648fc941a76ab5056628624f369',
				272 =>'8fd31e22c6f4ef3f86f8c52878d259d902488a28',
				273 =>'9048fea599d6a5b9fcf39f7f918863d1363abdf9',
				274 =>'904c90c49e91c104911905b6164b6c586a67cbb5',
				275 =>'9059717387dc8b1710d62dd318e6076eebde24c2',
				276 =>'90a02c6d7ac56b69926cd933df02598d39216f92',
				277 =>'90dd6a07f615d721eddfb027a5c738fc0ca6a026',
				278 =>'91371c4438bd9b22e94ef05f6e9acf5f69b543ed',
				279 =>'91ac299ccf0b4b9d3f66b446384adb7282727306',
				280 =>'93045574b2ceb00c9d40789a4e99d0ac27edd696',
				281 =>'9419d93e3528ff2854c0943e849c78c5144aba5c',
				282 =>'95e475f2fde3a945096f89abfe16fd71fa75d3ea',
				283 =>'96aec346f90f3117d286562950fe51bf4669bb44',
				284 =>'975f7f8cfcf0ae1b01e7a84fc6af434f99481a22',
				285 =>'9847f1904e6511d9f780d46f5c4436fb3d70fe97',
				286 =>'98abdf63dca0591bcdb792479c41092b31c90557',
				287 =>'990aac865e5890e21eaae90c8d78c7928f8d4574',
				288 =>'99fc1514b06c2ca49b9717707af0fea842b10661',
				289 =>'9a049939738e322be9774df50a3ad53903103a26',
				290 =>'9a0fde2ceef67516ac5ec3ee09fd77950993179e',
				291 =>'9b27edaa1e35c7020968c5a32b498151eacede6e',
				292 =>'9d6655af4b1c410793aa494b58f4a24598ffecb8',
				293 =>'9e453db583a375d5a8d05d1300e95c7e49c90a8c',
				294 =>'9e9d3c94554099c55afaaf04d1ce066ce6ec4a85',
				295 =>'9f4eef99c06d2e66ed3698921b0a4b438ebab35a',
				296 =>'9f665dd24d4c03f765e48fbe1a0ee3ca049f370a',
				297 =>'9fb185d89bb5b02534002d636f9eee52191b5f44',
				298 =>'a077ad7118ef831dd4543c512fbbf0bb403dea1c',
				299 =>'a0fa15024be9bc55258a3618f443d0fec791063c',
				300 =>'a16008076ab462c1d777e48660677f91ea6a4e4d',
				301 =>'a28689984b06065595a9d2f6edf3d39ad49cb415',
				302 =>'a5347e8cb37ad03fdcbd0452af82fd0da7e33a2c',
				303 =>'a5599cb85ca27e1343be4e46d3595fbec4ef51d1',
				304 =>'a691d5fa10876dc3b9c7f1caac1e87899d95e70e',
				305 =>'a70973f574fc95c8703f204760b8f9c848f7a1d1',
				306 =>'a73800ec7dcda0bf290e31fc002015ba29dbd32c',
				307 =>'a839df07904e0b6667ccc61fc87c0c678e787347',
				308 =>'a8974fb85b9b2917ffdf47dcc7080d05a23f8fe4',
				309 =>'a8bd090698afd6fa3596deb92afc445abf98af12',
				310 =>'a91331d90ce8b5cc3432e654a3e498bf30a36c71',
				311 =>'aa422329a2dc3bcf3dbbf78f802df10cb0bed3fb',
				312 =>'aa7d509014b45399b3e7d8323620a9a32e6b4f78',
				313 =>'aa80c72033e5ba9d6d82141f9f30b36ef0dae131',
				314 =>'aaf0add44e7de4e3a1c3ede470d6cc9626fc8fae',
				315 =>'aafb7114a9a6b9a7963b520f82dda7cec90205a7',
				316 =>'ab1f78239a4839d57f9762d4bcef5fc06b3e70fe',
				317 =>'ab2672ba222b9fc5ea8a4a63a3def87ff3e88a1e',
				318 =>'ab5982d9fb2eb3b48a09583cfc24a25cdd644117',
				319 =>'aba2480c4812d573b563e7d9a9284c33ffbe69d6',
				320 =>'ac7deed6b730d8b5afa48263cf6e807daa662fb9',
				321 =>'ad7370e289f212c5bdcc3546d634c8c5f047dc0f',
				322 =>'adb82e21dce473b0a50cd7567012486024ab2681',
				323 =>'add18974acfb4a09f9d3313b3cf99d32abf6ab17',
				324 =>'aead46ff1b9a40ea25fee1ad5be89190dc846c39',
				325 =>'af1fcae21587ad376fab4598b32414f51975041d',
				326 =>'af5616cb5897c4a89971bc78ce15467d2fa2eb72',
				327 =>'af97f5e0df24ba4b5cb9805795614b37503cf5af',
				328 =>'b0d006b1e0875e81ae5d63f04cf8a10265675f6c',
				329 =>'b17b7b699296e0b8db0e4f9b45f021914857ecfd',
				330 =>'b1d9332bb6abad9374f25e06d56ce20d29140e3e',
				331 =>'b1e1fc7be59b9c373800fd0bd76e9d4adeebb6b9',
				332 =>'b1e2a4dc445154571234142000ca938c62907841',
				333 =>'b34210532bc41064a45cab92f7adf3990717b78a',
				334 =>'b5f23f31dc1b995dcc297838179b21f62b9cafa4',
				335 =>'b60381425189980cbd71839dd886b30ef3af1294',
				336 =>'b8c36c92bb32300fdba34bd50f0b9b5a0819deaf',
				337 =>'b9c48d115958f22ca40fde0c6b56eba9b088acd3',
				338 =>'ba1690c0dd1c4068eda979789403bcd84cafc68e',
				339 =>'ba7c87124d35c8a8b1dcf938e1f1eb29e1759760',
				340 =>'ba87b64239cb0784a73a0b1a678a0cc33fd25c24',
				341 =>'ba98b1cb0456298d7ce284b21ca77d73f186f630',
				342 =>'baa13f634b7bb2d7e119beb6b4f1bbbccd4173cf',
				343 =>'bb452538c2f33d6a1b10226539606d038bb69bb3',
				344 =>'bb6c9b5af51e91423a1aa1272c4db31009b5ee4a',
				345 =>'bc492dbc9680678993214e17ff9ea3446b43b8b2',
				346 =>'bdab496402f29b463b3815a60017a42589f5b26a',
				347 =>'beff6b300cea87f9d7ca5ac11f691c5461bc6ca3',
				348 =>'bf1608199945f4290f814d581a97876547e4bdd2',
				349 =>'c1cea07ac3d6ff70034e2960dec06f2b1c99f926',
				350 =>'c2dc849ab9897d58d1c365ce5aa3ad3ccacbe5cd',
				351 =>'c32581a579bb43350d48791960e08624df08689c',
				352 =>'c3cb70e30e8dc488f7071b5a9a3f3ca6f16b7743',
				353 =>'c493d4d2f7a89940b3075a3455d449a391e028b6',
				354 =>'c559b921488e759859825048e756e386153891c6',
				355 =>'c673d908d72f786123635f1f0a0a1bac87e0d8e9',
				356 =>'c7ed27ec1e7be46b82feba080b73bb42c28d79c0',
				357 =>'c8b908820e568855a55eaab26aaeb1c9c057ee54',
				358 =>'c97099e50a13e447ebbc13439f18dc5c02fa15ae',
				359 =>'ca0686de4df6eb5461dddbdc2b4c3c75452a6a86',
				360 =>'cb818c5b4d43d4f05a8260dfdb9ed87827427d36',
				361 =>'cb984c9858f6b791d4f15976b1af01b058d15b3e',
				362 =>'cbc62b8a5e725cbb7d180a7cc99b249f7c7e28af',
				363 =>'cc95f1fa164f6f5d47b5238e78a55d27ca86a0bb',
				364 =>'ccb354435021a5f0d7a39fd7d137e680dc5ecad9',
				365 =>'ccd0b6e72f659d1ed7e99332e1e4cc1aec757c05',
				366 =>'cd6987e01659f4aac62195582434130cd9a001f4',
				367 =>'cde898a6cfc72d8941907faba52f230bcb49c166',
				368 =>'ce61721a169d6e88d3f29725c93ed774522ef44a',
				369 =>'cec12ee596a2e739c3ed73f908e17af39fc54334',
				370 =>'cefd8504942edf5fa338771d1d4efdcf6049c5b2',
				371 =>'cefdbba4d29851f8d9be40ff6cbe306d63f11b28',
				372 =>'cf2078af3425b3f22027c9f9c142af895ea67211',
				373 =>'cf2ba029aee0310165144b115fbdfb26e395b2c4',
				374 =>'cf729a6e71310516749fd38f19951911d58fc904',
				375 =>'d03367cb8b838f1841244edfaecc337507f62619',
				376 =>'d0b781250a4c07fff2b8d4f3a90694eb2d7aaad1',
				377 =>'d1f29776a39def3e48088d52883b598e02f3cbfc',
				378 =>'d1ff55e0ccab02942a95eaa8c337d5593bcab513',
				379 =>'d23e056c732e741afe29342b3b290afc84e765c1',
				380 =>'d270bf8bce51697f898ed8ac5adf09a67347a3e2',
				381 =>'d2ecea279cbc71cabf43dfe2889e96e61942b7da',
				382 =>'d3141b954bd99c7ed3ed7a997e62877fdbd59050',
				383 =>'d359666bb3dd86f83e2d6e4feb0da9682d12b836',
				384 =>'d3dc560986eade9a8c264873d62f3545492d6c4a',
				385 =>'d3eef3bd691e509020aad7499063d413e40c920f',
				386 =>'d4b9f3bac157da1d6729b07efa86702f10816cde',
				387 =>'d66bd890a640d9549b9199f37808f309dd329c4e',
				388 =>'d715c311610407b7b776104cc38b3a1b6de3172c',
				389 =>'d76e0c06c48698bf9c30d372b20d154c79e6eede',
				390 =>'d805a393f11d682566e226613910ed0c9832034f',
				391 =>'d8ecb81320755167eb6a6aa0dbf957c9be785331',
				392 =>'d94a1ace858722d71c99ea73549f99e281597630',
				393 =>'d987aa3896af7c8b8f1ccabd9a40566f5765b427',
				394 =>'d9aea0dbe031e754e9007d451630fdbce325890c',
				395 =>'da28af634ec924d7b188e0d3363b4d2fdc1f3b45',
				396 =>'da8771323f2b98d777e688491a9b8048b5f784d0',
				397 =>'dac4fb4cf2b857ac34aef86c6cbe9b16e82afaad',
				398 =>'db083960c0fb37a3590a8c5d82d078c6529edaf5',
				399 =>'dc0e80b912cabd74248ba10d0142fbf7c97a1707',
				400 =>'dcba569b376e4270642a82d519651fe4ff5fd2de',
				401 =>'dd49c7d336a292902a26b7df4a58084d3b0ec072',
				402 =>'dd837643e1781107906a79b73d1c7b3b92ae9518',
				403 =>'dde1b05ad0f99ae2da447cf064a39cad9ad99977',
				404 =>'de0661b55d5b1d01f6a085d48790a014026bb7eb',
				405 =>'de24989df65d8a8a75ab5a5b9df92220a3f325d4',
				406 =>'de74466876ed545226cee3059ea607212963f22d',
				407 =>'df9194fdc3a006debe5dc9d33f67dea6bdba19bf',
				408 =>'df99297f5f662210b87efcb01e2c21d0c18a5977',
				409 =>'dfa3a7b9655541fe5c91b38acf2615a7a9047684',
				410 =>'dfb97b4802a9d403303dc5229434d4612f73b50c',
				411 =>'dfe317364f30cf7f8903ad40b3c396a90e375373',
				412 =>'e022e72feaea4f0aa90f64026cc138d05b80bfe4',
				413 =>'e0250c557bc280e58c6f54b683eba5eef4f84d4e',
				414 =>'e03b9284686a0c00d8f646614a413eab4610a7e4',
				415 =>'e24c5950e1d668f1c58de8fdffd80725cb36e552',
				416 =>'e270f6675e0e87baa93855258c277d8593f9b6fa',
				417 =>'e3d08d886d39977a191fd4cd69538a3ede85c546',
				418 =>'e4f2a4b851e8b3fc21071a52f5c7cc8878057ed1',
				419 =>'e4f549a3fd85b1d4f2d91a7ab2aac5beecd9c7b6',
				420 =>'e58ca80be58771e816192a682b5dd062265044c0',
				421 =>'e6494ef8a814edb94485f6092ccbad39f5c2686e',
				422 =>'e66e9586a51275cca36e83299899de71feee9aa6',
				423 =>'e7355d00eda9a3b22f9718580575517b1db9d65b',
				424 =>'e769be7004fe496883a863c6a12853a416553519',
				425 =>'e819f3b03330133b1d0113a50c3261eb92dc2aa1',
				426 =>'e8bfdad280f161ed487e45321b1576a5feda5c04',
				427 =>'e9c6708637f13597274725ad5126aa39a1455fcf',
				428 =>'e9d1b49873a483c347234eb66cf14925a4ad4f2a',
				429 =>'ea23ac30c04039a9eec6b74088fe559f08e11ef7',
				430 =>'eb3e497ca8962ea9d9b103fe09aa5445497e0aec',
				431 =>'ebcbbf2bdf94ff25d0ced1af0ef5c2d409e075b5',
				432 =>'ec23d8aa4102ac1bbb5aa05d86fe0c7871c60c2a',
				433 =>'ec336d72412b743bd25cd8df375a37c88ba38192',
				434 =>'ecec8541ebc2f418c237fdc31cd80e1c472d0a1a',
				435 =>'ed16e9aa416e6eed2cd04cc8f58865ac0d7fd49f',
				436 =>'ed1d1a0da3e583ceab7a8f966b4e867fd092f287',
				437 =>'ed59ee32da568ebcd4dc792749ee41f2184db57d',
				438 =>'eec2bdad96211f4e6f5bcb65f71ce98a6c276ec7',
				439 =>'eec6797cf5e9aceec937a1111887f80ff8f016e7',
				440 =>'eec8e9ced45256ce34e2a939b35e2b0eb80a45cc',
				441 =>'eed3a53b439a0efa3685e3e2301b941acff1f95a',
				442 =>'eefe638143209e21c40d029a8aa42a6abb2add41',
				443 =>'ef01f0d68c25c23c9de033d1fd9edfd4d4b847cd',
				444 =>'f0138e2093aa38164e332bd2152f54fcb894baea',
				445 =>'f0fda60df94d5c17579bb45956d3701711a4af85',
				446 =>'f1204ace976b9939a6a2380b83ec49a4393df142',
				447 =>'f1833b0b80922218952a1d71a1e0dc366ed1557b',
				448 =>'f1c481cf5b5960073c1247f23bd148a00b70d4fa',
				449 =>'f1d6b64daa992cf61b0047eec5595ec471e79a32',
				450 =>'f247f9fa509ca618046936f81bd4d61cfdfaf2cb',
				451 =>'f422ff2f00c3b403014203b7ca898208d35bac2f',
				452 =>'f45e0d6dd6eaca4c2c6d5e8967837631778be263',
				453 =>'f4f99bd60127fde9c4bdcec14338fe416773ef37',
				454 =>'f611308d7fed0176499ab0b2a96097d92e480c9e',
				455 =>'f685f078bea98c076bc4ef8f76adaa9c5cb50e01',
				456 =>'f7435cd1bf946e17f442f3d4d5943d181491bf6f',
				457 =>'f79e6ff54ed9ba4e0be19fc5e6ce0a5a818d1e70',
				458 =>'f9c83ccc31f63504091878d9d84b2f4c7c605754',
				459 =>'fa417a1c13e22e6c0eed057af9c292889d865f0a',
				460 =>'fa90fbb1dc3c848ba65339dfbb133d79fec56721',
				461 =>'fb7c5851a8de79a14958029ef4af1aa9fb79890b',
				462 =>'fb99db6f5a802bacdfdf279b7560a61125a3e049',
				463 =>'fbb281e01253377b6d97ad4926386a95846c5128',
				464 =>'fce97adb91de51146f4ffe47e74fcc17658963ef',
				465 =>'fdb1bd5030efa5ec3d57b7119665c02d05338fce',
				466 =>'fe81a1050fc256bb88ca9747f80df2b982aac83d',
				467 =>'fe9316d268da52fe0f97259960823c258787b92e',
				468 =>'fea2572c2f2bc8cd17c08a75ac94aef2139e365f',
				469 =>'fecc9786965e61ff39132a70fd37a2223a87664e',
				470 =>'ffa021a330936fcd7c304d46315fc0092e3bb603'
		);
			
		if($this->getRequest()->isPost())
		{

			foreach($ipids as $key=>$val)
			{
				$abb = "'ND'";
				$dg = new DiagnosisType();
				$dsad = $dg->getDiagnosisTypes($clientid,$abb);
				$uptype = $dsad[0]['id'];
					
				$dms = new DiagnosisMeta();
				$diagnosismeta  =$dms->getDiagnosisMetaData(1);
					
				$metas = Doctrine_Query::create()
				->select("*")
				->from('PatientDiagnosisMeta')
				->where('ipid = "'.$val.'"');
				$met = $metas->execute();
				if($met)
				{
					$metaarr = $met->toArray();


					foreach($metaarr as $k=>$v)
					{

						$mid = $v['metaid'];

						$apost = array();

						if($mid>0)
						{
							$apost['ipid'] = $val;
							$apost['dtype'] = $uptype;
							$apost['icd'] = $v['metaid'];
							$apost['clientid'] = $clientid;
							$apost['diagnosis'] = $diagnosismeta[$v['metaid']];
							$cd++;
						}

						$patdigno = new Application_Form_PatientDiagnosis();
						$patdigno->UpdateMetatoDiagnosis($apost);
					}
				}

			}//forech

			exit;
		}
	}

	public function golmal3Action()
	{
		$this->_redirect(APP_BASE."error/previlege");
		exit;
			
		$logininfo= new Zend_Session_Namespace('Login_Info');
		if($this->getRequest()->isPost())
		{

			$quama = "";
			foreach($_POST['record'] as $key=>$val)
			{
				$rd.=$quamma.$val;
				$quamma = ",";

			}
			$up = Doctrine_Query::create()
			->select('*')
			->from('Medication')
			->where('id in ('.$rd.') and extra=1');
			$chk =  $up->execute();

			if($chk)
			{
				$chkarr = $chk->toArray();
				print_r($chkarr);
				exit;
			}


		}
		$offset = $_GET['pageno']*100;
		$this->view->pagenoplus = $_GET['pageno']+1;
			
		$qpa1 = Doctrine_Query::create()
		->select("*,AES_DECRYPT(course_type,'".Zend_Registry::get('salt')."') as course_type,
				AES_DECRYPT(course_title,'".Zend_Registry::get('salt')."') as course_title")
				->from('PatientCourse')
				->where('recordid=22938');
			
		$usra = $qpa1->execute();
		if($usra)
		{
			$healtharr = $usra->toArray();

			print_r($healtharr);
			exit;
			$inputs = "";
			foreach($healtharr as $key=>$val)
			{
				$recordid = $val['recordid'];
					
				$inputs .= '<input type="text" name="record[]" value="'.$recordid.'">';
			}
			$this->view->inputs = $inputs;
		}
			
			
	}

	public function locationeditajaxAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		//ajax request should have this (removes the menus queryes = faster)
		$this->_helper->layout->setLayout('layout_ajax');

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;

		$patient_loc = new PatientLocation();
		$ls = new Locations();
		$patientmaster = new PatientMaster();

		$locations_form = new Application_Form_PatientLocation();


		if($_REQUEST['delid'] > 0)
		{
			//COMMENTED TO REMOVE THE LOCATIONS ON SUBMIT (ISPC-1256)
//			$pat_loc_res = $patient_loc->getLocationById($_REQUEST['delid']);
//			$carr = $pat_loc_res[0];
//
//			$locarr = $ls->getLocationbyId($carr['location_id']);
//
//			$parr = $patientmaster->getMasterData(null, 0, 1, $carr['ipid']);
//
//			if($parr['kontactnumber'] == $locarr[0]['phone1'])
//			{
//				$pmaster = Doctrine_Query::create()
//					->update('PatientMaster')
//					->set('kontactnumber', '" "')
//					->set('change_date', '"' . date('Y-m-d H:i:s', time()) . '"')
//					->set('change_user', '"' . $userid . '"')
//					->where('ipid LIKE "' . $carr['ipid'] . '"');
//				$update_res = $pmaster->execute();
//			}
//
//			$patloc = Doctrine_Query::create()
//				->update('PatientLocation')
//				->set('isdelete', '"1"')
//				->set('change_date', '"' . date('Y-m-d H:i:s', time()) . '"')
//				->set('change_user', '"' . $userid . '"')
//				->where('id = "' . $_REQUEST['delid'] . '"');
//			$patloc_res = $patloc->execute();
//
//			//check for next location
//			$patarray = $patient_loc->getNextLocation($carr['valid_from'], $carr['ipid']);
//
//			if(count($patarray) > 0)
//			{
//				//update the next location valid_from with deleted location valid from to keep the timeline continuuous
//				$patloc_next = Doctrine_Query::create()
//					->update('PatientLocation')
//					->set('valid_from', '"' . $carr['valid_from'] . '"')
//					->set('change_date', '"' . date('Y-m-d H:i:s', time()) . '"')
//					->set('change_user', '"' . $userid . '"')
//					->where('id = "' . $patarray[0]['id'] . '"');
//				$patloc_next_res = $patloc_next->execute();
//			}
		}
		else
		{
			$a_post = $_REQUEST;
			$a_post['ipid'] = $ipid;

			$parr = $patientmaster->getMasterData(null, 0, 1, $ipid);
			$a_post['kontactnumbertype'] = $parr['kontactnumbertype'];

			if($locations_form->newvalidate($a_post))
			{
				$loc = $locations_form->insert_location_between($a_post);
				$lid = $loc->id;
				$errors = array($this->view->translate("recordinsertsucessfully"));
			}
			else
			{
				$errors = $locations_form->getErrorMessages();
			}

			$response = array();
			$response['msg'] = "Success";
			$response['error'] = $errors;
			$response['callBack'] = "callBack";
			$response['callBackParameters'] = array();
			$response['callBackParameters']['lid'] = $lid;
			echo json_encode($response);
			exit;
		}
		exit;
	}

}

?>