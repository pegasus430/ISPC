<?

function createmenu(&$wintoolbar)
{
			$items = new PhpExt_General_LazyComponent('Ext.Action');
			//$items->setProperty('text','List User group');
			
			$usergroupshandler = PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('usergroup','usergroups'));
",array());
			/*$usergroupshandler1 = PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('usergroup','listusergroup'));
",array());*/

			$userhandler = PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('usersgroup','usersgroup'));
",array());
			/*$userhandler1 = PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('usersgroup','listusersgroup'));
",array());*/

			$modulehandler = PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('managerights','userrights'));
",array());

			$modulehandler1	= PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('managerights','listmodules'));
",array());
			/*$assignhandler = PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('assignrights','rights'));
",array());*/			
			$assignhandler1 = PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('assignrights','listmodules'));
",array());

			$addresorcegrp_handler = PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('managerights','addresourcegroup'));
",array());

			$addresorcegrp_handler1 = PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('managerights','listresourcetype')); 
",array());

		//listresource
		//$listentity_handler1 = PhpExt_Javascript::functionDef('',"
		//  og.openLink(og.getUrl('manageentity','listentity')); 
		//",array());//listresource
			
			$languagehandler	= PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('language','listlanguages'));
",array());

			$directorygrouphandler	= PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('directorygroups','listdirectorygroups'));
",array());

			$directoryhandler	= PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('directory','listdirectory'));
",array());
			
			$directory_dirgroupshandler	= PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('language','listdirectorys_groups'));
",array());
			
			$userhandler = PhpExt_Javascript::functionDef('',"og.openLink('usersgroup/listusersgroup');",array('button','event'));			
						
			$items->setProperty('handler',$usergroupshandler);
			
			$items->setProperty('handler',$userhandler);
			
			$items->setProperty('handler',$modulehandler);
			
			$items->setProperty('handler',$assignhandler1);
			$items->setProperty('handler',$addresorcegrp_handler);
			$items->setProperty('handler',$addresorcegrp_handler1);
			
			$items->setProperty('handler',$languagehandler);
			$items->setProperty('handler',$directoryhandler);
			$items->setProperty('handler',$directorygrouphandler);
			$items->setProperty('handler',$directory_dirgroupshandler);
			//echo $submithandler->getJavascript();
			
			//$wintoolbar->addItem('asd',PhpExt_Javascript::variable('action'));
			/*$baseitem = new PhpExt_Menu_BaseItem();
			$baseitem->addTextItem('menu3','test',$submithandler);*/
						
			$listeners1 = new PhpExt_General_Object();
			$listeners1->setProperty('click',$modulehandler1);

			
			$item1 = new PhpExt_General_Object();
			$item1->setProperty("text","Resources");
			$item1->setProperty("iconCls","ico-resource");
			$item1->setProperty('listeners',$listeners1->getObject());
			
			
			$listeners2 = new PhpExt_General_Object();
			$listeners2->setProperty('click',$addresorcegrp_handler1);

			
			$item2 = new PhpExt_General_Object();
			$item2->setProperty("text","Resources Type");
			$item2->setProperty("iconCls","ico-rights");
			$item2->setProperty('listeners',$listeners2->getObject());
						
			$modules = new PhpExt_Menu_Menu();
			$modules->addItem("key",$item1);
			$modules->addItem("key2",$item2);
		
			$genobject = new PhpExt_General_Object();
			$genobject->setProperty("text","Resources");
			$genobject->setProperty("cls","ico-edit");
			$genobject->setProperty("menu",$modules->getJavascript(true,'resource'));
			
			
			/* international    */
			
			$listenerslang = new PhpExt_General_Object();
			$listenerslang->setProperty('click',$languagehandler);
			
			$itemlang = new PhpExt_General_Object();
			$itemlang->setProperty("text","Languages");
			$itemlang->setProperty("iconCls","ico-resource");
			$itemlang->setProperty('listeners',$listenerslang->getObject());
			
			$listenersdir = new PhpExt_General_Object();
			$listenersdir->setProperty('click',$directoryhandler);
			
			$itemdir = new PhpExt_General_Object();
			$itemdir->setProperty("text","Directory");
			$itemdir->setProperty("iconCls","ico-rights");
			$itemdir->setProperty('listeners',$listenersdir->getObject());
						
			$listenersdirgroup = new PhpExt_General_Object();
			$listenersdirgroup->setProperty('click',$directorygrouphandler);
			
			$itemdirgrp = new PhpExt_General_Object();
			$itemdirgrp->setProperty("text","Directory Groups");
			$itemdirgrp->setProperty("iconCls","ico-rights");
			$itemdirgrp->setProperty('listeners',$listenersdirgroup->getObject());
			
			
			
			$listenersalldir = new PhpExt_General_Object();
			$listenersalldir->setProperty('click',$directory_dirgroupshandler);
			
			$itemalldir = new PhpExt_General_Object();
			$itemalldir->setProperty("text","Directorys & Directory Groups");
			$itemalldir->setProperty("iconCls","ico-rights");
			$itemalldir->setProperty('listeners',$listenersalldir->getObject());
			
						
			$modulesinter = new PhpExt_Menu_Menu();
			$modulesinter->addItem("keylang",$itemlang);
			$modulesinter->addItem("keydir",$itemdir);
			$modulesinter->addItem("keydirgrp",$itemdirgrp);			
			//$modulesinter->addItem("keyalldir",$itemalldir);
			
			$genobject = new PhpExt_General_Object();
			$genobject->setProperty("text","International");
			$genobject->setProperty("cls","ico-edit");
			$genobject->setProperty("menu",$modulesinter->getJavascript(true,'international'));
			
			
			/* end international    */
			
			
			$item = new PhpExt_Toolbar_Item();
			$item->setText("User Groups");
			//$item->setMenu($menu);
			
			$item1 = new PhpExt_Toolbar_Item();
			$item1->setText("Users");
		//	$item1->setMenu($usermenu);
			
			$listeners = new PhpExt_General_Object();
			$listeners->setProperty('click',$userhandler);
			
			$addbuttons = new PhpExt_General_Object();
			$addbuttons->setProperty('listeners',$listeners->getObject());
			$addbuttons->setProperty('text','Users');
			$addbuttons->setProperty('iconCls','ico-overview');
								
			$item2 = new PhpExt_Toolbar_Item();
			$item2->setText("Resources");
			$item2->setCls("ico-resource");
			$item2->setMenu($modules);
			
			$item3 = new PhpExt_Toolbar_Item();
			$item3->setText("Assign Rights");
			//$item3->setMenu($assignmenu);
			
			$iteminter = new PhpExt_Toolbar_Item();
			$iteminter->setText("International");
			$iteminter->setCls("ico-resource");
			$iteminter->setMenu($modulesinter);
			
			//$item4 = new PhpExt_Toolbar_Item();
			//$item4->setText("Entities");
			//$item4->setMenu($entitymenu);
			
			$usergroupshandler1 = PhpExt_Javascript::functionDef('',"og.openLink('usergroup/listusergroup');",array('button','event'));
			$listeners1 = new PhpExt_General_Object();
			$listeners1->setProperty('click',$usergroupshandler1);
			
			$addbuttons1 = new PhpExt_General_Object();
			$addbuttons1->setProperty('listeners',$listeners1->getObject());
			$addbuttons1->setProperty('text','User Groups');
			$addbuttons1->setProperty('iconCls','ico-usergroup');
			
			$wintoolbar->addItem('test1',$addbuttons1);
		//	$wintoolbar->addItem("lista",$item);
			$wintoolbar->addSeparator("sep");
		//	$wintoolbar->addItem("listp",$item1);
			$wintoolbar->addItem('remove',$addbuttons);
			$wintoolbar->addSeparator("sepn");
			
			$wintoolbar->addItem("listc",$item2);	
			$wintoolbar->addSeparator("sepc");
			
			
			
			
			$assignhandler = PhpExt_Javascript::functionDef('',"og.openLink('assignrights/rights');",array('button','event'));
			
			$listeners2 = new PhpExt_General_Object();
			$listeners2->setProperty('click',$assignhandler);
			
			$addbuttons2 = new PhpExt_General_Object();
			$addbuttons2->setProperty('listeners',$listeners2->getObject());
			$addbuttons2->setProperty('text',' Privileges ');
			$addbuttons2->setProperty('iconCls','ico-rights');
						
			$wintoolbar->addItem('test2',$addbuttons2);
					
			//$wintoolbar->addItem("listd",$item3);
		//	$wintoolbar->addSeparator("sepe");
			//$wintoolbar->addItem("liste",$item4);
			$wintoolbar->addSeparator("sepd");
			
			$customerhandler = PhpExt_Javascript::functionDef('',"
            og.openLink(og.getUrl('customer','listcustomers'));
",array());

			$listeners = new PhpExt_General_Object();
			$listeners->setProperty('click',$customerhandler);
			
			$addbuttons = new PhpExt_General_Object();
			$addbuttons->setProperty('listeners',$listeners->getObject());
			$addbuttons->setProperty('text','Customers');
			$addbuttons->setProperty('iconCls','ico-overview');
			
			$wintoolbar->addItem('test3',$addbuttons);
			$wintoolbar->addSeparator("sepc");
			
			$wintoolbar->addItem("inter",$iteminter);	
			$wintoolbar->addSeparator("sepc");
			
			
			
}


?>