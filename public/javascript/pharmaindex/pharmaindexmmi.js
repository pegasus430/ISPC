function pharmaindex() {
	var that = this;
	var actual_request = "";
	var tabs = {};

	function request_new() {
	    if(actual_request != "") {
	    	actual_request.abort();
	    }
	}

	function request_finished() {
	    if(actual_request != "") {
	    	actual_request = "";
	    }
	}

	function showtab(name) {
	    $.each(tabs, function (key, value) {
	    	$(value).hide();
	    });
	    $(tabs[name]).show();

	    if(name != "throbber" && name != "none" && name != "none2") {
			pi_productname.show();
			pi_companyname.show();
			pi_iconsbar.show();
			pi_tabsbarcontainer.show();
	    } else {
			pi_productname.hide();
			pi_companyname.hide();
			pi_iconsbar.hide();
			pi_tabsbarcontainer.hide();
	    }
	}

	var priceprint = function(p) {
		if (p == undefined)
			return "";
		p = p.toString();
		if (p.indexOf('.') < 0) {
			p = p + ".00";
		}
		while (p.indexOf('.') > (p.length - 3)) {
			p = p + "0";
		}
		p = p.replace('.', ',');
		return p;
	}

	function calculateAge(dob) {
	    var age;

	    var dobParts = dob.split(".");

	    var dobDate = new Date();

	    dobDate.setDate(parseInt(dobParts[0]));
	    dobDate.setMonth(parseInt(dobParts[1]));
	    dobDate.setYear(parseInt(dobParts[2]));

	    var dobDay = dobDate.getDate();
	    var dobMonth = dobDate.getMonth();
	    var dobYear = dobDate.getYear();

	    var nowDate = new Date();

	    var nowDay = nowDate.getDate();
	    var nowMonth = nowDate.getMonth();
	    var nowYear = nowDate.getYear();

	    age = nowYear - dobYear;

	    if(nowMonth < (dobMonth - 1)) {
	    	age--;
	    }

	    if(((dobMonth - 1) == nowMonth) && (nowDay < dobDay)) {
	    	age--;
	    }

	    return age;
	}

	var selecttab = function (obj) {

	    $("#pi_mainwin .pi_tab").css('background-color', '');
	    $(obj).css('background-color', '#f2f5f7');


	    if($(obj).hasClass('pi_preisvergleich_tab')) {
			getpricecomparison();
			showtab('tab3');
			PRICE_COMPARISONGROUP2 = 0;
	    }

		if($(obj).hasClass('pi_fachinformationen_tab')) {
			var pid = last_products.PRODUCT[selected_product].ID;
			$(pi_tab2).empty();
			showtab('tab2');
	
			$(pi_tab2).append('<div style="text-align:center;"><img src="images/loading1.gif" style="margin-top:100px;"></div>');
			request_new();
			actual_request = $.get(that.ajaxPath + "/getdocuments?pid=" + pid, function (data) {
			    request_finished();
			    var json = jQuery.parseJSON(data);
			    if(json.COUNT > 0) {
					var list = json.DOCUMENT[0].CATEGORY_LIST;
					var contents = {};
					var sortlist = [];
		
					for(var i = 0; i < list.length; i++) {
					    sortlist.push(list[i].NAME_SORT);
					    contents[list[i].NAME_SORT] = list[i];
					}
					sortlist.sort();
		
					var fachinfohead = $('<ol id="pi_fachinformationen_head"></ol>');
		
					var fachinfo = $('<div id="pi_fachinformationen_content"></div>');
		
					for(var i = 0; i < sortlist.length; i++) {
					    var link = $('<li class="pi_inlink" style="cursor:pointer;">' + contents[sortlist[i]].NAME + "</li>");
					    $(link).attr('pi_scrollto', '#pi_fi_' + contents[sortlist[i]].NAME_SORT);
					    fachinfohead.append(link);
					    fachinfo.append('<h2 style="margin-top:20px;" id="pi_fi_' + contents[sortlist[i]].NAME_SORT + '">' + contents[sortlist[i]].NAME + '</h2>' + contents[sortlist[i]].CONTENT);
					}
					$(pi_tab2).empty();
					$(pi_tab2).append("<b>Inhalt</b><br/><br/>");
					$(pi_tab2).append(fachinfohead);
					$(pi_tab2).append(fachinfo);
	
			    } else {
					$(pi_tab2).show().empty();
					$(pi_tab2).append("<p>Es liegen keine Fachinformationen vor.</p>");
					showtab('tab2');
			    }
			});
		}

	    if($(obj).hasClass('pi_basisinformationen_tab')) {
			var pid = last_products.PRODUCT[selected_product].ID;
			$(pi_tab2).empty();
			showtab('tab2');
	
			$(pi_tab2).append('<div style="text-align:center;"><img src="images/loading1.gif" style="margin-top:100px;"></div>');
			request_new();
			actual_request = $.get(that.ajaxPath + "/getdocumentsbase?pid=" + pid, function (data) {
			    request_finished();
			    var json = jQuery.parseJSON(data);
	
			    if(json.COUNT > 0) {
				var list = json.DOCUMENT[0].CATEGORY_LIST;
				var contents = {};
				var sortlist = [];
	
				for(var i = 0; i < list.length; i++) {
				    sortlist.push(list[i].NAME_SORT);
				    contents[list[i].NAME_SORT] = list[i];
				}
				sortlist.sort();
	
				var fachinfohead = $('<ol id="pi_basisinformationen_head"></ol>');
	
				var fachinfo = $('<div id="pi_basisinformationen_content"></div>');
	
				for(var i = 0; i < sortlist.length; i++) {
				    var link = $('<li class="pi_inlink" style="cursor:pointer;">' + contents[sortlist[i]].NAME + "</li>");
				    $(link).attr('pi_scrollto', '#pi_fi_' + contents[sortlist[i]].NAME_SORT);
				    fachinfohead.append(link);
				    fachinfo.append('<h2 style="margin-top:20px;" id="pi_fi_' + contents[sortlist[i]].NAME_SORT + '">' + contents[sortlist[i]].NAME + '</h2>' + contents[sortlist[i]].CONTENT);
				}
				$(pi_tab2).empty();
				$(pi_tab2).append("<b>Inhalt</b><br/><br/>");
				$(pi_tab2).append(fachinfohead);
				$(pi_tab2).append(fachinfo);
	
			    } else {
				$(pi_tab2).show().empty();
				$(pi_tab2).append("<p>Es liegen keine Basisinformationen vor.</p>");
				showtab('tab2');
			    }
			});
	    }

	    if($(obj).hasClass('pi_praeparat_tab')) {
	    	showtab('tab1');
	    }

	    if($(obj).hasClass('pi_hersteller_tab')) {
			var companyId = last_products.PRODUCT[selected_product].COMPANYID;
	
			actual_request = $.post(that.ajaxPath + "/getcompany", {companyId: companyId}, function (data) {
			    $(pi_tab3).empty();
	
			    request_finished();
			    var json = jQuery.parseJSON(data);
	
			    if(json.COUNT > 0) {
				var contactLength = json.COMPANY[0].ADDRESS.CONTACT_LIST.length;
	
				var html;
	
				html = '<h1 style="color: rgb(34, 34, 34); margin-bottom: 10px; display: block;">' + json.COMPANY[0].NAME + '</h1>';
	
				html += '<p><b>';
				html += 'Anschrift:';
				html += '</b></p>';
	
				html += '<p>';
				html += json.COMPANY[0].ADDRESS.STREET + ' ';
				html += json.COMPANY[0].ADDRESS.STREETNUMBER + '<br />';
				html += json.COMPANY[0].ADDRESS.ZIP + ' ';
				html += json.COMPANY[0].ADDRESS.CITY + '<br />';
				html += '</p>';
	
	
				for(var i = 0; i < contactLength; i++) {
				    if(json.COMPANY[0].ADDRESS.CONTACT_LIST[i].CONTACTTYPECODE == 'T') {
						html += '<p><b>';
						html += 'Telefon:';
						html += '</b></p>';
		
						html += '<p>';
						html += json.COMPANY[0].ADDRESS.CONTACT_LIST[i].CONTACTENTRY;
						html += '</p>';
				    }
	
				    if(json.COMPANY[0].ADDRESS.CONTACT_LIST[i].CONTACTTYPECODE == 'F') {
						html += '<p><b>';
						html += 'Fax:';
						html += '</b></p>';
		
						html += '<p>';
						html += json.COMPANY[0].ADDRESS.CONTACT_LIST[i].CONTACTENTRY;
						html += '</p>';
				    }
	
				    if(json.COMPANY[0].ADDRESS.CONTACT_LIST[i].CONTACTTYPECODE == 'I') {
						html += '<p><b>';
						html += 'Web:';
						html += '</b></p>';
		
						html += '<p>';
						html += json.COMPANY[0].ADDRESS.CONTACT_LIST[i].CONTACTENTRY;
						html += '</p>';
				    }
	
				    if(json.COMPANY[0].ADDRESS.CONTACT_LIST[i].CONTACTTYPECODE == 'E') {
						html += '<p><b>';
						html += 'E-Mail:';
						html += '</b></p>';
		
						html += '<p>';
						html += json.COMPANY[0].ADDRESS.CONTACT_LIST[i].CONTACTENTRY;
						html += '</p>';
				    }
				}
	
				pi_tab3.append(html);
			    }
			});
	
			showtab('tab3');
	    }
	};

	var mapicons = {
	    'AM': ['Arzneimittel', 'AM.png'],
	    'AP*': ['Apothekenpflichtig, Ausnahmeliste beachten', 'Apa.png'],
	    'SCHW': ['Hinweise zu Schwangerschaft beachten', 'schwanger_aktiv.png'],
	    'STILL': ['Hinweise zu Stillzeit beachten', 'stillen_aktiv.png'],
	    'MOE': ['Mörserbar', 'moerserbar.png'],
	    'NTL': ['Nicht teilbar', 'teilbarnein.png'],
	    'IMP': ['Import-Präparat', 'imp.png'],
	    'NL': ['Negativliste', 'nl.png'],
	    'REA': ['Reaktionsvermögen kann beeinträchtigt werden', 'REA.png'],
	    'AMR': ['Arzneimittelrichtlinie beachten! anlage III', 'AMR.png'],
	    'RP': ['Verschreibungspflichtig', 'Rp.png'],
	    'DOP': ['Bei bestimmten Sportarten im Wettkampf verboten', 'doping.png'],
	    'MP': ['Medizinprodukt', 'MP.png'],
	    'NAP': ['Nicht apothekenpflichtig', 'NAp.png'],
	    'HOMOEO': ['Homöopathisches Arzneimittel', 'homoeopatisch.png'],
	    'PRISCUS': ['Hinweise der PRISCUS Liste beachten', 'priscus.png'],
	    'AP': ['Apothekenpflichtig', 'Ap.png'],
	    'APL': ['APL', 'ApL.png'],
	    'TL2': ['In 2 Teile teilbar', 'teilbar2.png'],
	    'TL3': ['In 3 Teile teilbar', 'teilbar3.png'],
	    'TL4': ['In 4 Teile teilbar', 'teilbar4.png'],
	    'TLES': ['Teilbar zum erleichterten Schlucken', 'teilbar_es.png'],
	    'TLGD': ['In gleiuche Teile teilbar', 'teilbar_gd.png'],
	    'NAM': ['Nicht Arzneimittel', 'NAM.png'],
	    'NEM': ['Nahrungsergänzung', 'NE.png'],
	    'DIAET2': ['Sonstiges Diätetikum', 'DIA2.png'],
	    'BTM': ['Betäubungsmittel', 'BTM.png'],
	    'TL': ['Teilbar', 'teilbar.png'],
	    'VM': ['Verbandmittel', 'VM.png'],
	    'UEBERWACH': ['Besondere Überwachung erforderlich', 'UEBERWACH.png'],
	    'FICTAM': ['Fiktiv zugelassenes Arzneimittel', 'FICTAM.png'],
	    'RPL': ['Rezeptpflichtiges Lifestyle-Präparat', 'RPL.png'],
	    'PFL': ['Pflanzliches Arzenimittel', 'pflanzlich.png'],
	    'DIAET': ['Diätetikum gemäß § 31 SGB V ', 'DIA.png'],
	    'EBD': ['Ergänzende bilanzierte Diät', 'EBD'],
	    'KOS': ['Kosmetikum', 'KOS.png'],
	    'TFG': ['Transfusionsgesetz', 'TFG.png'],
	    'TREZ': ['Rezeptpflichtig: T-Rezept', 'RPT.png'],
	    'RPLA': ['Rezeptpflichtiges Lifestyle-Präparat mit Ausnahmen', 'RPLA.png']
	};

	var amrPath = 'AMR';

	var pi_window = $('<div id="pi_mainwin" style="padding:4px;"></div>');
	var pi_leftcol = $('<div class="pi_leftcol" id="pi_leftcol"></div>');

	var searchType1 = $('<input name="searchType" type="radio" value="1" checked>');
	var searchType2 = $('<input name="searchType" type="radio" value="2">');
	var searchType3 = $('<input name="searchType" type="radio" value="3">');

	//search method strict(strikt) or full text(Volltextsuche)
	var searchMethod1 = $('<input name="searchMethod" type="radio" value="0" checked>');
	var searchMethod2 = $('<input name="searchMethod" type="radio" value="1">');

	var pi_searchbox = $('<input type="text" id="pi_searchbox" style="width:180px;" />');

	var pi_suggestbox = $('<div style="z-index: 2; position: absolute; height: 430px; width: 350px; border: 1px solid #BBBBBB; background-color: #F2F2F2; padding: 5px; display: none; overflow: hidden; overflow-y: auto">Suche...</div>');

	var pi_otcWarningHidden = $('<input type="hidden" id="otcWarning" value="0"/>');
	var pi_otcWarning = $('<div style="display: none; z-index: 4; position: absolute; top: 25%; left: 25%; height: 120px; width: 500px; border: 1px solid #BBBBBB; background-color: #F2F2F2; overflow: hidden;"></div>');
	var pi_otcWarningTitle = $('<div style="width: 100%; background-color: #888888; padding: 5px;"><b>OTC Warnung</b></div>');
	var pi_otcWarningContent = $('<div style="padding: 5px;">Dieses Präparat ist nicht rezeptpflichtig. Möchten Sie es trotzdem verordnen?<br /><br /><br /><input class="otcAbort" type="button" value="Nein" style="float: right; margin-left: 5px;"/><input class="otcGreen" type="button" value="Grünes Rezept" style="float: right; margin-left: 5px;"/><input class="otcPrivat" type="button" value="Privatrezept" style="float: right; margin-left: 5px;"/><input class="otcGkv" type="button" value="GKV-Rezept" style="float: right"/></div>');

	var pi_btmWarningHidden = $('<input type="hidden" id="otcWarning" value="0"/>');
	var pi_btmWarning = $('<div style="display: none; z-index: 4; position: absolute; top: 25%; left: 25%; height: 120px; width: 500px; border: 1px solid #BBBBBB; background-color: #F2F2F2; overflow: hidden;"></div>');
	var pi_btmWarningTitle = $('<div style="width: 100%; background-color: #888888; padding: 5px;"><b>BTM Warnung</b></div>');
	var pi_btmWarningContent = $('<div style="padding: 5px;">Dieses Präparat ist ein BTM Produkt. Möchten Sie es trotzdem verordnen?<br /><br /><br /><input class="btmAbort" type="button" value="Nein" style="float: right; margin-left: 5px;"/><input class="btmYes" type="button" value="Ja" style="float: right; margin-left: 5px;"/></div>');

	var pi_amrHidden = $('<input type="hidden" id="amrShowHidden" value="0"/>');
	var pi_amr = $('<div style="display: none; z-index: 4; position: absolute; top: 8%; left: 2%; height: 425px; width: 850px; border: 1px solid #BBBBBB; background-color: #F2F2F2; overflow: hidden;"></div>');
	var pi_amrTitle = $('<div style="width: 100%; background-color: #888888; padding: 5px;"><b>Arzneimittelrichtlinen</b></div>');
	var pi_amrContent = $('<div style="padding: 5px;"></div>');

	var pi_amrContent2 = '<a href="' + amrPath + '/Arzneimittelrichtlinien_Grafik.pdf" target="_blank">Arzneimittel-Richtlinien grafische Darstellung, Stand Januar 2012</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/Arzneimittelrichtlinien.pdf" target="_blank">Arzneimittel-Richtlinien / AM-RL, in der Fassung vom 08. Dezember 2008 / 22. Januar 2009, zuletzt geändert am 22. Mai 2014, in Kraft getreten 09. August 2014</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/anlage_I.pdf" target="_blank">Anlage I: OTC-Übersicht, Stand 05. Juni 2013</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/anlage_II.pdf" target="_blank">Anlage II: Lifestyle Arzneimittel (früher: Anlage 8), Stand 11. Juni 2014</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/anlage_III.pdf" target="_blank">Anlage III: Übersicht über Verordnungseinschränkungen und -ausschlüsse (früher u.a. Anlage 10), Stand 13. Mai 2014</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/anlage_IV.pdf" target="_blank">Anlage IV: Therapiehinweise (früher: Anlage 4), Stand 08. Juli 2014</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/anlage_V.pdf" target="_blank">Anlage V: Übersicht der verordnungsfähigen Medizinprodukte (früher Anlage 12), Stand 22. Mai 2014</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/anlage_VI.pdf" target="_blank">Anlage VI: Off-label-use (früher: Anlage 9), Stand 30. Juli 2014</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/anlage_VII.pdf" target="_blank">Anlage VII: Aut idem (früher: Anlage 5), Stand 09. August 2014</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/anlage_VIII.pdf" target="_blank">Anlage VIII: Hinweise zu Analogpräparaten (früher Anlage 6), Stand 01. April 2009</a><br /><br />';
	pi_amrContent2 += '<a href="http://www.g-ba.de/informationen/beschluesse/zur-anlage/7/" target="_blank">Anlage IX: Festbetragsgruppenbildung (früher: Anlage 2)</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/anlage_X.pdf" target="_blank">Anlage X: Aktualisierung von Vergleichsgrößen, Stand 03. September 2013</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/anlage_XI.pdf" target="_blank">Anlage XI: Verordnung besonderer Arzneimittel (früher: Anlage 13), außer Kraft getreten am 01. Januar 2011</a><br /><br />';
	pi_amrContent2 += '<a href="http://www.g-ba.de/informationen/richtlinien/anlage/169/" target="_blank">Anlage XII: Frühe Nutzenbewertung</a><br /><br />';
	pi_amrContent2 += '<a href="' + amrPath + '/negativliste.pdf" target="_blank">Arzneimittelübersicht zur sogenannten Negativliste (früher Anlage 3) Letzte Änderung 18. Oktober 2003</a><br /><br />';

	var pi_searchbutt = $('<input type="button" value="Suchen" id="pi_searchbutt" />');
	var pi_amrbutton = $('<input type="button" value="AMR öffnen" id="pi_amrshow" />');

	var pi_productselect = $('<select size="25" id="resizable" class="pi_productselect"></select>');
	var pi_productcount = $('<p></p>');

	var pi_throbber_tab = $('<div style="text-align:center;" id="pi_throbber_tab"><img src="images/loading1.gif" style="margin-top:100px;"></div>');
	var pi_none_tab = $('<div style="text-align:center; margin-top:80px; font-size:15px; font-weight:bold;">Geben Sie links ins Suchfeld einen Suchbegriff ein <br /> und wählen Sie dann aus der Liste darunter ein Produkt aus.</div>');
	var pi_none2_tab = $('<div style="text-align:center; margin-top:80px; font-size:15px; font-weight:bold;">Die Suche erzielte keine Treffer.</div>');

	var boxSize = $('<div class="pkg_sizes_selection"></div>');

	tabs['throbber'] = pi_throbber_tab;
	tabs['none'] = pi_none_tab;
	tabs['none2'] = pi_none2_tab;


	// Search Methods
	var pi_search_methods = $('<div class="pi_leftcol_sm"></div>');
	$(pi_search_methods).append(searchMethod1).append(searchMethod2);
	$(searchMethod1).after(' strikt&nbsp;&nbsp;');
	$(searchMethod2).after(' Volltextsuche&nbsp;&nbsp;');
	$(pi_leftcol).append($(pi_search_methods));


	var pi_search_box_div = $('<div class="pi_leftcol_sb"></div>');
	
	$(pi_search_box_div).append(pi_searchbox);
	$(pi_search_box_div).append(pi_suggestbox);
	$(pi_searchbox).after(pi_searchbutt);
	$(pi_leftcol).append($(pi_search_box_div));
	
	
	// Search Tyles - Produkt - Wirkstoff - ATC
	var pi_search_types = $('<div class="pi_leftcol_st"></div>');
	$(pi_search_types).append(searchType1);
	$(pi_search_types).append(searchType2);
	$(pi_search_types).append(searchType3);
	$(searchType1).after(' Produkt&nbsp;&nbsp;');
	$(searchType2).after(' Wirkstoff&nbsp;&nbsp;');
	$(searchType3).after(' ATC');
	$(pi_leftcol).append($(pi_search_types));

	
	
	$(pi_leftcol).append($(pi_productselect).wrap("<div></div>"));
	$(pi_leftcol).append($(pi_productcount).wrap("<div></div>"));

	$(pi_leftcol).append($(pi_amrbutton).wrap("<div></div>"));

	$(pi_window).append(pi_leftcol);

	$(pi_otcWarning).append(pi_otcWarningTitle);
	$(pi_otcWarning).append(pi_otcWarningContent);
	$(pi_window).append(pi_otcWarning);
	$(pi_window).append(pi_otcWarningHidden);

	$(pi_btmWarning).append(pi_btmWarningTitle);
	$(pi_btmWarning).append(pi_btmWarningContent);
	$(pi_window).append(pi_btmWarning);
	$(pi_window).append(pi_btmWarningHidden);

	$(pi_amr).append(pi_amrTitle);
	$(pi_amrContent).append(pi_amrContent2);
	$(pi_amr).append(pi_amrContent);
	$(pi_window).append(pi_amr);
	$(pi_window).append(pi_amrHidden);


	var pi_right_col = $('<div class="pi_right_col"></div>');
	$(pi_right_col).append(pi_throbber_tab);
	$(pi_right_col).append(pi_none_tab);
	$(pi_throbber_tab).hide();
	$(pi_none_tab).hide();
	$(pi_right_col).append(pi_none2_tab);
	$(pi_none2_tab).hide();
	
	var pi_productname = $('<h1 style="color:#222; margin-bottom: 10px"></h1>');
	var pi_companyname = $('<h2 style="color:#111; font-size:12px; font-weight:normal;"></h2>');
	var pi_iconsbar = $('<div style="margin-top:4px;margin-bottom:4px;" class="pi_iconsbar"></div>');

	var pi_tabsbarcontainer = $('<div class="tabsbarcontainer" style="background-color:#888888;overflow:hidden;"></div>');

	var pi_praeparat_tab = $('<div class="pi_praeparat_tab pi_tab">Präparat</div>');
	var pi_fachinformationen_tab = $('<div class="pi_fachinformationen_tab pi_tab">Fachinformationen</div>');
	var pi_basisinformationen_tab = $('<div class="pi_basisinformationen_tab pi_tab">Basisinformationen</div>');
	var pi_preisvergleich_tab = $('<div class="pi_preisvergleich_tab pi_tab">Preisvergleich</div>');
	var pi_hersteller_tab = $('<div class="pi_hersteller_tab pi_tab">Hersteller</div>');

	$(pi_tabsbarcontainer).append(pi_praeparat_tab);
	$(pi_tabsbarcontainer).append(pi_fachinformationen_tab);
	$(pi_tabsbarcontainer).append(pi_basisinformationen_tab);
	$(pi_tabsbarcontainer).append(pi_preisvergleich_tab);
	$(pi_tabsbarcontainer).append(pi_hersteller_tab);

	var pi_packageselect = $('<div class="pi_packageselect" style="height:160px;overflow:auto;"></div>');
	/*var pi_prodinfo=$('<div class="pi_prodinfo" style="height:235px; overflow:auto;"></div>');*/
	var pi_prodinfo = $('<div class="pi_prodinfo" style="height:380px; overflow:auto;"></div>');
	var pi_prodinfo_table = $('<table style="margin-top: 10px"></table>');
	var pi_tab1 = $('<div class="pi_tab1"></div>');
	var pi_tab2 = $('<div class="pi_tab2" style="height:400px;overflow:auto;"></div>');
	var pi_tab3 = $('<div class="pi_tab3" style="height:400px;overflow:hidden;"></div>');
	tabs['tab1'] = pi_tab1;
	tabs['tab2'] = pi_tab2;
	tabs['tab3'] = pi_tab3;

	$(pi_right_col).append(pi_productname);
	$(pi_right_col).append(pi_companyname);
	$(pi_right_col).append(pi_iconsbar);
	$(pi_right_col).append(pi_tabsbarcontainer);
	$(pi_right_col).append(pi_tab1);
	$(pi_right_col).append(pi_tab2);
	$(pi_right_col).append(pi_tab3);
	pi_tab2.hide();
	pi_tab3.hide();
	/*$(pi_tab1).append(pi_packageselect);
	 $(pi_tab1).append('<div style="height:4px; background-color:#444;"></div>');*/
	$(pi_tab1).append(pi_prodinfo);
	$(pi_prodinfo).append(pi_prodinfo_table);
	var pi_prodinfo_amr = $('<div style="margin-top:12px; line-height: 14px"></div>');
	$(pi_prodinfo).append(pi_prodinfo_amr);

	$(pi_window).append(pi_right_col);
	$('body').append(pi_window);


	var last_products = new Array();
	var selected_product = 0;



	$('body').on('click', '.pi_tab', function () {
	    selecttab(this);
	});

	$('body').on('change', '.pi_productselect', function () {
		pi_tab3.empty();
	    var i = $(this).prop("selectedIndex");
	    
	    selected_product = i;
	    
	    if(last_products.PRODUCT[i] != undefined) {
	    	var pid = last_products.PRODUCT[i].ID;
	    } else {
	    	pid = 0;
	    }
	    
		if(pid > 0) {
			pi_otcWarningHidden.val(0);
			pi_otcWarningHidden.val(last_products.PRODUCT[i].OTC_FLAG);
	
			$(pi_iconsbar).empty();
	
			selecttab(pi_praeparat_tab);
			$(pi_productname).html(last_products.PRODUCT[i].NAME_HTML);
	
	//            var buttonWithoutPrice = $('<input type="button" value="Produkt verschreiben" style="float: right">');
	//            buttonWithoutPrice.unbind('click').bind('click', function(){
	//                showOtcWarning(last_products.PRODUCT[selected_product].NAME_HTML);
	//            });
	
			$(pi_companyname).html(last_products.PRODUCT[i].COMPANYNAME);
			var num_icons = last_products.PRODUCT[i].ICONCODE_LIST.length;
	
			for(var j = 0; j < num_icons; j++) {
			    if(mapicons[last_products.PRODUCT[i].ICONCODE_LIST[j]] != null) {
	
				$(pi_iconsbar).append($('<img class="medIcon" data-tooltip="' + mapicons[last_products.PRODUCT[i].ICONCODE_LIST[j]][0] + '" src="' + that.imagePath + '/' + mapicons[last_products.PRODUCT[i].ICONCODE_LIST[j]][1] + '">'));
			    } else {
				$(pi_iconsbar).append("<b>" + last_products.PRODUCT[i].ICONCODE_LIST[j] + "</b>");
			    }
			}
			$(pi_iconsbar).find('img').css('vertical-align', 'top').css('margin', '2px');
	
			medIconAlt();
	
			var wirkstoffe = [];
			var aequivalenz = [];
			var sonstigebestandteile = [];
			$(pi_prodinfo_table).empty();
			if(last_products.PRODUCT[i].ITEM_LIST != undefined) {
			    var comp_elems = last_products.PRODUCT[i].ITEM_LIST[0].COMPOSITIONELEMENTS_LIST;
	
			    if(typeof (comp_elems) == 'object') {
					for(var j = 0; j < comp_elems.length; j++) {
					    if(comp_elems[j].MOLECULETYPECODE == "A") {
							var unit = comp_elems[j].MOLECULEUNITCODE;
							var name = comp_elems[j].MOLECULENAME;
							var mass = comp_elems[j].MASSFROM;
							if(mass && unit) {
							    name = name + "(" + mass + " " + unit.toLowerCase() + ")";
							}
			
							wirkstoffe.push(name);
			
							var equivalent = comp_elems[j].EQUIVALENT_LIST;
			
							if(typeof (equivalent) == "object") {
							    for(var k = 0; k < equivalent.length; k++) {
									var unit = equivalent[k].MOLECULEUNITCODE;
									var name = equivalent[k].MOLECULENAME;
									var mass = equivalent[k].MASSFROM;
				
									if(mass && unit) {
									    name = name + "(" + mass + " " + unit.toLowerCase() + ")";
									}
									aequivalenz.push(name);
							    }
							}
					    }
					    if(comp_elems[j].MOLECULETYPECODE == "I") {
							var unit = comp_elems[j].MOLECULEUNITCODE;
							var name = comp_elems[j].MOLECULENAME;
							var mass = comp_elems[j].MASSFROM;
							if(mass && unit) {
							    name = name + " (" + mass + " " + unit.toLowerCase() + ")";
							}
			
							sonstigebestandteile.push(name);
					    }
					}
		
					var wirkstoffcell = "";
					for(var j = 0; j < wirkstoffe.length; j++) {
					    if(j == 0) {
					    	wirkstoffcell = wirkstoffe[j];
					    } else {
					    	wirkstoffcell = wirkstoffcell + ", " + wirkstoffe[j];
					    }
					}
		
					if(wirkstoffcell != "") {
					    wirkstoffcell = $("<tr><td style='width:150px; line-height: 20px'><b>Wirkstoffe:</b></td><td style='line-height: 20px'>" + wirkstoffcell + "</td></tr>")
					}
		
					$(pi_prodinfo_table).append(wirkstoffcell);
		
					var aequivalenzcell = "";
					for(var j = 0; j < aequivalenz.length; j++) {
					    if(j == 0) {
					    	aequivalenzcell = aequivalenz[j];
					    } else {
					    	aequivalenzcell = aequivalenzcell + ", " + aequivalenz[j];
					    }
					}
		
					if(aequivalenzcell != "") {
					    aequivalenzcell = $("<tr><td style='width:150px; line-height: 20px'><b>Äquivalenzen:</b></td><td style='line-height: 20px'>" + aequivalenzcell + "</td></tr>")
					}
		
					$(pi_prodinfo_table).append(aequivalenzcell);
		
					var sonstigebestandteilecell = "";
					for(var j = 0; j < sonstigebestandteile.length; j++) {
					    if(j == 0) {
					    	sonstigebestandteilecell = sonstigebestandteile[j];
					    } else {
					    	sonstigebestandteilecell = sonstigebestandteilecell + ", " + sonstigebestandteile[j];
					    }
					}
					if(sonstigebestandteilecell != "") {
					    sonstigebestandteilecell = $("<tr><td style='width:150px; line-height: 20px'><b>Sonstige Bestandteile:</b></td><td style='line-height: 20px'>" + sonstigebestandteilecell + "</td></tr>")
					}
		
					$(pi_prodinfo_table).append(sonstigebestandteilecell);
				}
	
			    var atc_elems = last_products.PRODUCT[i].ITEM_LIST[0].ATCCODE_LIST;
	
			    if(atc_elems != undefined) {
					var atc_cell = "";
					for(var j = 0; j < atc_elems.length; j++) {
					    var atc_c = atc_elems[j].PARENT.CODE;
					    var atc_n = atc_elems[j].PARENT.NAME;
					    var atc_name = atc_c + " - " + atc_n;
		
					    var atc_c2 = atc_elems[j].CODE;
					    var atc_n2 = atc_elems[j].NAME;
					    var atc_name2 = atc_c2 + " - " + atc_n2;
		
					    if(atc_name2 != "") {
					    	atc_name += '<br />' + atc_name2;
					    }
		
					    if(atc_cell == "") {
					    	atc_cell = atc_name;
					    } else {
					    	atc_cell = atc_cell + "<br>" + atc_name;
					    }
					}
		
					$(pi_prodinfo_table).append("<tr><td style='width:150px; line-height: 20px'><b>ATC</b></td><td style='line-height: 20px'>" + atc_cell + "</td></tr>");
			    }
			}
	
	
			var icd_elems = last_products.PRODUCT[i].ICD10CODE_LIST;
			if(icd_elems != undefined) {
			    var icd_cell = "";
			    for(var j = 0; j < icd_elems.length; j++) {
					var icd_c = icd_elems[j].CODE;
					var icd_n = icd_elems[j].NAME;
					var icd_name = icd_c + " - " + icd_n;
					if(icd_cell == "") {
					    icd_cell = icd_name;
					} else {
					    icd_cell = icd_cell + "<br>" + icd_name;
					}
			    }
	
			    $(pi_prodinfo_table).append("<tr><td style='width:150px; line-height: 20px'><b>ICD10</b></td><td style='line-height: 20px'>" + icd_cell + "</td></tr>");
			}
			$(pi_prodinfo_amr).empty();
			$(pi_prodinfo_amr).append('<div style="text-align:center;" id="pi_throbber_tab"><br /><br />Suche Richtlinien...<br /><img src="images/loading1.gif" style="margin-top:10px;"></div>');
	
			var lid = selected_product;
			$.get(that.ajaxPath + "/getamr?pid=" + last_products.PRODUCT[i].ID, function (data) {
	
			    if(lid == selected_product) {
					$(pi_prodinfo_amr).empty();
					var json = jQuery.parseJSON(data);
					$.each(json.AMR, function (akey, amr) {
					    if(typeof (amr.TEXT) != 'undefined') {
							var amrtitle, amrtext;
			
							if(that.patientAge <= amr.AGETO || typeof (amr.AGETO) == 'undefined') {
							    amrtitle = $("<h2 style='margin-bottom: 10px'>" + amr.TITLE + "</h2>");
							    var text = amr.TEXT.replace('<b>', '');
							    text = text.replace('</b>', '');
							    amrtext = $("<p>" + text + "</p>");
							} else {
							    amrtitle = $("<h2 style='margin-bottom: 10px'>Keine weiteren Informationen</h2>");
							    amrtext = '';
							}
			
							$(pi_prodinfo_amr).append($('<div></div>').append(amrtitle).append(amrtext));
					    }
					});
			    }
			});
	
				var json = last_products.PRODUCT[i].PACKAGE_LIST;
				var showPriceTab = 0;
				var count = json.length;
				var table = $("<table class='pharmaindextable packagestable' style='margin:1px;'>");
				var row = $("<tr>");
				$(row).append('<th style="width:300px;">Packungen</th>');
				$(row).append('<th style="width:100px;">ZuZa</th>');
				$(row).append('<th style="width:100px;">VS</th>');
				$(row).append('<th style="width:100px;">FB</th>');
				$(row).append('<th style="width:100px;">AVP</th>');
				$(row).append('<th style="width:100px;">FB-Diff.</th>');
				$(row).append('<th style="width:100px;">PZN</th>');
				table.append(row);
		//            pi_preisvergleich_tab.hide();
				for(var i = 0; i < count; i++) {
				    var row = $("<tr>").attr('rowid', i);
				    $(row).append('<td class="pi_pack_name">' + json[i].NAME + '</td>');
				    $(row).append('<td class="pi_pack_zuza">' + priceprint(json[i].PRICE_PATIENTPAYMENT) + '</td>');
				    $(row).append('<td class="pi_pack_vs">' + priceprint(json[i].SALESTATUSCODE) + '</td>');
				    $(row).append('<td class="pi_pack_fb">' + priceprint(json[i].PRICE_FIXED) + '</td>');
				    $(row).append('<td class="pi_pack_fbdiff">' + priceprint(json[i].PRICE_PHARMACYSALE) + '</td>');
				    var diff = "";
				    if(json[i].PRICE_PHARMACYSALE > 0 && json[i].PRICE_FIXED > 0) {
						var diff = json[i].PRICE_PHARMACYSALE - json[i].PRICE_FIXED;
						diff = priceprint(Math.round(diff * 100) / 100);
				    }
				    $(row).append('<td class="pi_pack_fbdiff">' + diff + '</td>');
				    $(row).append('<td class="pi_pack_pzn">' + json[i].PZN + '</td>');
				    table.append(row);
		
				    if(json[i].PRICE_COMPARISONGROUP2 != undefined) {
						pi_preisvergleich_tab.show();
						showPriceTab = 1;
				    }
				}
		//moved to the pricetab which was changed to be always shown
		//            if(showPriceTab == 0){
		//                $(pi_iconsbar).append(buttonWithoutPrice);
		//            }
				$(pi_packageselect).html(table);
			}

	});


	$('body').on("click", ".pi_inlink", function () {
	    $(pi_tab2).animate({scrollTop: 0}, 0);
	    
	    var ele = $(this).attr('pi_scrollto');
	    var y = $(ele).offset().top - $(pi_tab2).offset().top - 8;

	    $(pi_tab2).animate({scrollTop: y}, 200);

	});

	$('body').on('change', '#pi_searchbox', function () {
	    /*searchkeyword();*/
	});

	$('body').on('click', function () {
	    pi_suggestbox.hide();
	});

	$('body').on('click', '#pi_searchbutt', function () {
	    searchkeyword();
	});

	$('body').on('click', '#pi_amrshow', function () {

	    var hide = $(pi_amrHidden).val();

	    if(hide == 0) {
			pi_amr.show();
			$(pi_amrHidden).val(1);
			$(this).val('AMR schließen');
	    } else {
			pi_amr.hide();
			$(pi_amrHidden).val(0);
			$(this).val('AMR öffnen');
	    }
	});

	$(pi_searchbox).keyup(function (e) {
	    if(e.which == 13) {
			searchkeyword();
			pi_suggestbox.hide();
	    } else {
			if(that.use_suggestions == '1')
			{
			    pi_suggestbox.empty();
			    pi_suggestbox.html('Suche...');
			    pi_suggestbox.show();
			    getSuggestions($(this).val());
			}
	    }
	});

	$('body').on('change', 'input[name="PRICE_COMPARISONGROUP2"]', function () {
	    PRICE_COMPARISONGROUP2 = $(this).val();
	    getpricecomparison();
	});


	$('body').on('click', '.packagestable td', function () {
	    if(that.mode == "catalog") {
			var row_id = $(this).parents('tr').attr('rowid');
			var i = $(pi_productselect).prop("selectedIndex");
			var item = last_products.PRODUCT[i].PACKAGE_LIST[row_id];
			var mname = last_products.PRODUCT[i]['NAME_HTML'];
			var pname = item['NAME'];
			var ppzn = item['PZN'];
			var punit = item['SIZE_UNITCODE'];
			$(that.input_active_row).find(that.input_medname).val(mname);
			$(that.input_active_row).find(that.input_dosageunit).val(punit);
			$(pi_window).dialog('close');
	    }
	});

	$('body').on('click', '.pricecomparison td', function () {
	    var row_id = $(this).parents('tr').attr('rowid');
	    var i = $(pi_productselect).prop("selectedIndex");
			    var item = that.pricecomplist['PPACKAGE'][row_id];
			    var num_icons = item.PRODUCT.ICONCODE_LIST.length;
			    var rname = item['NAME_RECIPE'];
			    var pname = item['PRODUCT']['NAME_HTML'];
			    var ppzn = item['PZN'];
			    var punit = item['SIZE_UNITCODE'];

			    for(var i = 0; i < num_icons; i++) {
					if(item.PRODUCT.ICONCODE_LIST[i] == 'BTM') {
					    that.otcWarningSw = '1';
					}
			    }
	    	
	    if(that.otcWarningSw == '1') {
			pi_btmWarning.show();
	
			$('body').on('click', '.btmAbort', function () {
			    pi_btmWarning.hide();
	
			    $('#receipt_type').val("kv_blank");
	
			    $('.receipt_background').css({
				backgroundImage: 'url(' + that.imagePath + '/kv_blank.png)'
			    });
	
			    $('#rceipt_form_NameGebInpt').css({
				top: '38px'
			    });
	
	//		    $('.rcb').removeAttr('checked');
	//		    $('.rcb_dummy').css('background','none');
			    $('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();
	
			    if(that.mode == "recipe") {
					$(that.input_active_row).find(that.input_medname).val(pname);
					$(that.input_active_row).find(that.input_dosageunit).val(punit);
					$(that.input_active_row).find(that.input_to_recipe).val(rname);
			    } else {
			    	$(that.input_active_row).find(that.input_medname).val(pname);
			    	$(that.input_active_row).find(that.input_dosageunit).val(punit);
			    }
	
			    if(that.callback != undefined) {
				showOtcWarning(rname);
			    }
			});
	
			$('body').on('click', '.btmYes', function () {
			    pi_btmWarning.hide();
	
			    $('#receipt_type').val("kv_btm");
	
			    $('.receipt_background').css({
				backgroundImage: 'url(' + that.imagePath + '/kv_blank_btm.png)'
			    });
	
			    $('#rceipt_form_NameGebInpt').css({
				top: '38px'
			    });
	
	//		    $('.rcb').removeAttr('checked', false);
	//		    $('.rcb_dummy').css('background','none');
	//		    $('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').hide();
	
			    if(that.mode == "recipe") {
					$(that.input_active_row).find(that.input_medname).val(pname);
					$(that.input_active_row).find(that.input_dosageunit).val(punit);
					$(that.input_active_row).find(that.input_to_recipe).val(rname);
			    } else {
					$(that.input_active_row).find(that.input_medname).val(pname);
					$(that.input_active_row).find(that.input_dosageunit).val(punit);
			    }
	
			    if(that.callback != undefined) {
				showOtcWarning(rname);
			    }
			});
		} else {
			if(that.mode == "recipe") {
			    $(that.input_active_row).find(that.input_medname).val(pname);
			    $(that.input_active_row).find(that.input_dosageunit).val(punit);
			    $(that.input_active_row).find(that.input_to_recipe).val(rname);
			} else {
			    $(that.input_active_row).find(that.input_medname).val(pname);
			    $(that.input_active_row).find(that.input_dosageunit).val(punit);
			}
	
			if(that.callback != undefined) {
			    showOtcWarning(rname);
			}
	    }
//	    $(pi_window).dialog('close');
	});

	$('body').on('click', 'input[name="btnsubmit"]', function () {
	    //print();
	});

	var PRICE_COMPARISONGROUP2 = 0;

	function getpricecomparison() {
	    pi_tab3.empty();
	    boxSize.html('<p style="margin-top: 5px">Packungsgröße:</p>');
	    
	    var pcg_list = {};
	    var pid = PRICE_COMPARISONGROUP2;

	
	    if(last_products.PRODUCT[selected_product] != undefined) {
	    		
				var packageList = last_products.PRODUCT[selected_product].PACKAGE_LIST;
				
				var size = packageList.length;
				
				for(var i = 0; i < size; i++) {
				    if(packageList[i].PRICE_COMPARISONGROUP2 != undefined) {
						var x = packageList[i].ID;

						var checked = "";
						var name = "";
			
						if(x == pid) {
			                checked = 'checked="checked"';
						    pid = packageList[i].PZN;
						}
			
						if(packageList[i].SIZE_NORMSIZECODE != undefined) {
						    name = packageList[i].SALESSTATUSCODE + '' + packageList[i].SIZE_NORMSIZECODE;
						} else {
						    name = packageList[i].SIZE_AMOUNT;
						}
						
						boxSize.append('<input id="p_size_'+ x +'" name="PRICE_COMPARISONGROUP2" type="radio" value="' + x + '"' + checked + ' /><label for="p_size_'+ x +'">' + name + '</label>');
						pi_tab3.append(boxSize);
						
				    }
				    if(pid < 1) {
				    	pid = packageList[i].PZN;
				    }
				}
				
		/*
		 $.each(last_products.PRODUCT[selected_product].PACKAGE_LIST, function(key, val){
		 if (val.PRICE_COMPARISONGROUP2 != undefined){
		 var x = val.PRICE_COMPARISONGROUP2;
		 pcg_list[x] = val.SIZE_AMOUNT;
		 if (val.SIZE_NORMSIZECODE!=undefined){
		 pcg_list[x]=val.SIZE_NORMSIZECODE;
		 switch(val.SIZE_NORMSIZECODE){
		 case "3":
		 pcg_list[x]="N3";
		 break;
		 case "2":
		 pcg_list[x]="N2";
		 break;
		 case "1":
		 pcg_list[x]="N1";
		 break;
		 case "N":
		 pcg_list[x]=val.SIZE_AMOUNT;
		 break;
					    	}
					    }
					    if(pid < 1) {
		 pid = x;
				}
		    }
		 });
		 */
	    } else {
	    	pid = 0;
	    }

	    if(pid == undefined) {
	    	pid = 0;
	    }

	    var throbberp = $("<div style='text-align:center; margin-top:32px;'><img src='images/loading1.gif'></div>");
	    pi_tab3.append(throbberp);

	    request_new();
	    var ik_no = $(that.ikno).val();
	    actual_request = $.get(that.ajaxPath + "/getpricecomparison?pid=" + pid + "&ikno=" + ik_no, function (data) {
			request_finished();
			var json = jQuery.parseJSON(data);
			that.pricecomplist = json;

			var pc_table = $("<table class='sortable pharmaindextable pricecomparison'></table>");
	
			var pc_table_body = $("<tbody></tbody>");
	
			var pc_count = 0;
				$.each(json.PPACKAGE, function (key, val) {
		
				    var row = $("<tr></tr>");
		    		$(row).attr('rowid', pc_count);
				    pc_count++;
				    discount = "";
				    if(val.DISAGR_ALTERNATIVEEXIST_FLAG == 1) {
				    	discount = '<div style="background-color:#d3c500;color:white;font-weight:bold;text-align:center;">R</div>';
				    } else if(val.DISAGR_TYPECODE > 0) {
				    	discount = '<div style="background-color:#009b00;color:white;font-weight:bold;text-align:center;">R</div>';
				    } else {
				    	discount = ' - ';
				    }
				    
				    row.append($("<td>" + discount + "</td>"));
				    row.append($("<td>" + val.NAME + "</td>"));
				    row.append($("<td class='price'>" + priceprint(val.PRICE_PATIENTPAYMENT) + "</td>"));
				    
				    row.append($("<td class='price'>" + priceprint(val.PRICE_FIXED) + "</td>"));
				    row.append($("<td class='price'>" + priceprint(val.PRICE_PHARMACYSALE) + "</td>"));
				    row.append($("<td>" + "</td>"));
		
				    var diff = "";
				    if(val.PRICE_PHARMACYSALE > 0 && val.PRICE_FIXED > 0) {
						var diff_t = val.PRICE_PHARMACYSALE - val.PRICE_FIXED;
						diff = priceprint(Math.round(diff_t * 100) / 100);
						if((Math.round(diff_t * 100) / 100) > 0)
						    diff = " +" + diff;
				    }
		
				    row.append($("<td class='price'>" + diff + "</td>"));
				    row.append($("<td>" + val.COMPANYNAME + "</td>"));
				    pc_table_body.append(row);
				});
	
		var buttonWithoutPrice = $('<input type="button" value="Produkt verschreiben" style="float: left">');
			buttonWithoutPrice.unbind('click').bind('click', function () {
			    showOtcWarning(last_products.PRODUCT[selected_product].NAME_HTML);
			});
	
		if(pc_count > 0) {
		    pi_tab3.append("<div style='padding:10px;'>Preisvergleich auf Wirkstoffbasis ohne Berücksichtigung der Indikation.</div>");
			    pi_tab3.append(pc_table);
			    pc_table.append("<thead style='cursor: pointer'><tr><th style='width:7px;'>Rabatt</th><th style='width:170px;'>Packungen</th><th style='width:40px;'>ZuZa.</th><th style='width:40px;'>FB</th><th style='width:40px;'>AVP</th><th style='width:50px;'>Rabattwert</th><th style='width:50px;'>Diff.</th><th>Hersteller</th></tr></thead>");
			    pc_table.append(pc_table_body);
			    pc_table.tablesorter({sortList: [[4, 0], ], textExtraction: myTextExtraction});
			    pc_table.wrap('<div style="height: 272px; overflow: auto;"></div>');
	
			    pi_tab3.append('<div style="margin-top: 3px"><p>Anzahl Preisergebnis: ' + pc_count + '</p></div>');
			}
			else
			{
			    var no_price_table = $('<table style="width:100%;"></table>');
			    var _row = $('<tr></tr>');
	
			    var r1 = $('<tr></tr>').append($('<td></td>').append(buttonWithoutPrice));
			    no_price_table.append(r1);
	
			    var r2 = $('<tr></tr>').append($('<td></td>').append('Für dieses Präparat werden keine Preisinformationen zur Verfügung gestellt.'));
			    no_price_table.append(r2);
	
			    pi_tab3.append(no_price_table);
			}
			$(throbberp).hide();
	    });
	}

	var myTextExtraction = function (node)
	{
	    // extract data from markup and return it
	    var x = $(node).text();
	    x = x.replace(',', '.');
	    return x;
	}

	function searchkeyword() {
	    pi_suggestbox.hide();
	    pi_otcWarning.hide();
	    $(pi_productselect).empty();
	    $(pi_productcount).html('Anzahl Suchergebnis: 0');
	    var searchtext = pi_searchbox.val();

	    var searchType = $('input[name="searchType"]:checked').val();
	    var searchMethod = $('input[name="searchMethod"]:checked').val();
	    /*var searchType = 1;*/

	    if(searchtext != "") {
			if(searchType == 1) {
			    request_finished();
			    showtab('throbber');
			    request_new();
			    var ik_no = $(that.ikno).val();
			    
			    actual_request = $.get(that.ajaxPath + "/getproducts?searchtext=" + searchtext + '&ik_no=' + ik_no + '&sm=' + searchMethod, function (data) {
					request_finished();
		
					var json = jQuery.parseJSON(data);
					var count = json.PRODUCT.length;
					/*var count=json.SUGGESTION.length;*/
		
					if(count > 0) {
					    last_products = json;
					    $(pi_productselect).empty();
					    for(var i = 0; i < count; i++) {
							var discagr_flag = json.PRODUCT[i].DISAGR_FLAG;
							var css = '';
							if(discagr_flag != null && discagr_flag == 1) {
							    css = 'style="background-color: green;"';
							} else if(json.PRODUCT[i].DISAGR_ALTERNATIVEEXIST_FLAG != null && json.PRODUCT[i].DISAGR_ALTERNATIVEEXIST_FLAG > 0) {
							    css = 'style="background-color: yellow;"';
							}
			
							$(pi_productselect).append('<option value="'+ json.PRODUCT[i].ID +'" ' + css + '>' + json.PRODUCT[i].NAME_HTML + '</option>');
							/*$(pi_productselect).append('<option>'+json.SUGGESTION[i].LABEL+'</option>');*/
			
					    }
					    if($(pi_productselect).children().length > 0) {
							$(pi_productselect).prop("selectedIndex", 0).change();
			
							showtab('tab1');
					    } else {
					    	showtab('none2');
					    }
					    $(pi_productcount).html('Anzahl Suchergebnis: ' + count);
					    
					} else {
						
					    actual_request = $.get(that.ajaxPath + "/getproductsbycompany?searchtext=" + searchtext, function (data) {
							request_finished();
			
							var json = jQuery.parseJSON(data);
							var count = json.PRODUCT.length;
			
							last_products = json;
							$(pi_productselect).empty();
							for(var i = 0; i < count; i++) {
							    var discagr_flag = json.PRODUCT[i].DISAGR_FLAG;
							    var css = '';
							    if(discagr_flag != null && discagr_flag == 1) {
								css = 'style="background-color: green;"';
							    } else if(json.PRODUCT[i].DISAGR_ALTERNATIVEEXIST_FLAG != null && json.PRODUCT[i].DISAGR_ALTERNATIVEEXIST_FLAG > 0) {
								css = 'style="background-color: yellow;"';
							    }
			
							    $(pi_productselect).append('<option  value="'+ json.PRODUCT[i].ID +'" >' + json.PRODUCT[i].NAME_HTML + '</option>');
							}
							if($(pi_productselect).children().length > 0) {
							    $(pi_productselect).prop("selectedIndex", 0).change();
							    showtab('tab1');
							} else {
							    showtab('none2');
							}
							$(pi_productcount).html('Anzahl Suchergebnis: ' + count);
					    });
					}
			    });
			} else if(searchType == 2) {
			    request_finished();
			    showtab('throbber');
			    var ik_no = $(that.ikno).val();
			    
			    actual_request = $.get(that.ajaxPath + "/getmolecule?searchtext=" + searchtext + '&ik_no=' + ik_no, function (data) {
					request_finished();
		
					var json = jQuery.parseJSON(data);
					var count = json.PRODUCT.length;
		
					last_products = json;
					$(pi_productselect).empty();
					for(var i = 0; i < count; i++) {
					    var discagr_flag = json.PRODUCT[i].DISAGR_FLAG;
					    var css = '';
					    if(discagr_flag != null && discagr_flag == 1) {
					    	css = 'style="background-color: green;"';
					    } else if(json.PRODUCT[i].DISAGR_ALTERNATIVEEXIST_FLAG != null && json.PRODUCT[i].DISAGR_ALTERNATIVEEXIST_FLAG > 0) {
					    	css = 'style="background-color: yellow;"';
					    }
		
					    $(pi_productselect).append('<option  value="'+ json.PRODUCT[i].ID +'">' + json.PRODUCT[i].NAME_HTML + '</option>');
					}
					if($(pi_productselect).children().length > 0) {
					    $(pi_productselect).prop("selectedIndex", 0).change();
					    showtab('tab1');
					} else {
					    showtab('none2');
					}
					$(pi_productcount).html('Anzahl Suchergebnis: ' + count);
			    });
			    
			} else if(searchType == 3) {
			    request_finished();
			    showtab('throbber');
			    var ik_no = $(that.ikno).val();
			    
			    actual_request = $.get(that.ajaxPath + "/getatc?searchtext=" + searchtext + '&ik_no=' + ik_no, function (data) {
					request_finished();
		
					var json = jQuery.parseJSON(data);
					var count = json.PRODUCT.length;
		
					last_products = json;
					$(pi_productselect).empty();
					for(var i = 0; i < count; i++) {
					    var discagr_flag = json.PRODUCT[i].DISAGR_FLAG;
					    var css = '';
					    if(discagr_flag != null && discagr_flag == 1) {
					    	css = 'style="background-color: green;"';
					    } else if(json.PRODUCT[i].DISAGR_ALTERNATIVEEXIST_FLAG != null && json.PRODUCT[i].DISAGR_ALTERNATIVEEXIST_FLAG > 0) {
					    	css = 'style="background-color: yellow;"';
					    }
		
					    $(pi_productselect).append('<option  value="'+ json.PRODUCT[i].ID +'">' + json.PRODUCT[i].NAME_HTML + '</option>');
					}
					if($(pi_productselect).children().length > 0) {
					    $(pi_productselect).prop("selectedIndex", 0).change();
					    showtab('tab1');
					} else {
					    showtab('none2');
					}
					$(pi_productcount).html('Anzahl Suchergebnis: ' + count);
			    });
			}
	    } else {
			showtab('none');
			$(pi_productcount).html('Anzahl Suchergebnis: 0');
	    }
	}

	function getSuggestions(val) {

	    if(that.use_suggestions == '1')
	    {
			var searchType = $('input[name="searchType"]:checked').val();
			showtab('throbber');
			if(searchType == 1) {
			    request_new();
			    
			    actual_request = $.get(that.ajaxPath + "/getsuggestions?searchtext=" + val, function (data) {
					request_finished();
					pi_suggestbox.empty();
					var html = '';
					var htmlObj = '';
					var json = jQuery.parseJSON(data);
					var count = json.COUNT;
		
					if(count > 0) {
					    for(var i = 0; i < count; i++) {
					    	htmlObj += '<p class="suggestResult" style="cursor: pointer" onmouseover="this.style.backgroundColor=\'#D4D4D4\'" onmouseout="this.style.backgroundColor=\'#F2F2F2\'">' + json.SUGGESTION[i].LABEL + '</p>';
					    }
		
		
					} else {
					    htmlObj = $('<p>Keine Vorschläge</p>');
					}
		
					pi_suggestbox.show();
					pi_suggestbox.append(htmlObj);
		
					$('body').on('click', '.suggestResult', function () {
					    request_finished();
					    var value = $(this).html();
		
					    pi_searchbox.val(value);
					    searchkeyword();
		
					});
			    });
			}
	    }
	}

	function showOtcWarning(name) {
	    if(that.patientAge <= 12) {
			that.callback(name);
			$(pi_window).dialog('close');
		} else {
			if(pi_otcWarningHidden.val() == 1) {
			    pi_otcWarning.show();
	
			    $('body').on('click', '.otcAbort', function () {
					pi_otcWarning.hide();
		
					$('#receipt_type').val("kv_blank");
		
					$('.receipt_background').css({
					    backgroundImage: 'url(' + that.imagePath + '/kv_blank.png)'
					});
		
					$('#rceipt_form_NameGebInpt').css({
					    top: '38px'
					});
		
					$('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();
			    });
	
			    $('body').on('click', '.otcGreen', function () {
					pi_otcWarning.hide();
		
					$('.receipt_background').css({
					    backgroundImage: 'url(' + that.imagePath + '/kv_blank_green.png)'
					});
		
					//set hidden text to value "otcGreen - kv_green"
					$('#receipt_type').val("kv_green");
		
					$('#rceipt_form_NameGebInpt').css({
					    top: '38px'
					});
		
					$('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').hide();
		
					that.callback(name);
					$(pi_window).dialog('close');
			    });
	
			    $('body').on('click', '.otcPrivat', function () {
					pi_otcWarning.hide();
					//set hidden text to value "otcPrivat - kv_blue"
					$('#receipt_type').val("kv_blue");
		
					$('.receipt_background').css({
					    backgroundImage: 'url(' + that.imagePath + '/kv_blank_blue.png)'
					});
		
					$('#rceipt_form_NameGebInpt').css({
					    top: '45px'
					});
		
					$('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();
					$('#rceipt_form_VertagsNrinpt').hide();
		
					that.callback(name);
					$(pi_window).dialog('close');
			    });
	
			    $('body').on('click', '.otcGkv', function () {
					pi_otcWarning.hide();
					//set hidden text to value "otcGkv - kv_blank"
					$('#receipt_type').val("kv_blank");
		
					$('.receipt_background').css({
					    backgroundImage: 'url(' + that.imagePath + '/kv_blank.png)'
					});
		
					$('#rceipt_form_NameGebInpt').css({
					    top: '38px'
					});
		
					$('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();
		
					that.callback(name);
					$(pi_window).dialog('close');
			    });
			} else {
			    that.callback(name);
			    $(pi_window).dialog('close');
			}
	    }
	}

	function print() {
	    var count = 0;
	    var checkbox = new Array();

	    $('input[name="getiuhrfrei[]"]').each(function () {
			if($(this).attr('checked') == 'checked') {
			    checkbox[count] = 1;
			} else {
			    checkbox[count] = 0;
			}
			count++;
	    });

	    if($('input[name="bvg"]').attr('checked') == 'checked') {
			checkbox[count] = 1;
			count++;
	    } else {
			checkbox[count] = 0;
			count++;
	    }

	    if($('input[name="mttel"]').attr('checked') == 'checked') {
			checkbox[count] = 1;
			count++;
	    } else {
			checkbox[count] = 0;
			count++;
	    }

	    if($('input[name="soff"]').attr('checked') == 'checked') {
			checkbox[count] = 1;
			count++;
	    } else {
			checkbox[count] = 0;
			count++;
	    }

	    if($('input[name="bedaf"]').attr('checked') == 'checked') {
			checkbox[count] = 1;
			count++;
	    } else {
			checkbox[count] = 0;
			count++;
	    }

	    if($('input[name="pricht"]').attr('checked') == 'checked') {
			checkbox[count] = 1;
			count++;
	    } else {
			checkbox[count] = 0;
			count++;
	    }

	    var insurancecomname = $('input[name="insurancecomname"]').val();

	    var patientfirstname = $('input[name="patientfirstname"]').val();
	    var patientlastname = $('input[name="patientlastname"]').val();

	    var street = $('input[name="street"]').val();
	    var zipcode = $('input[name="zipcode"]').val();
	    var city = $('input[name="city"]').val();

	    var birthdate = $('input[name="birthdate"]').val();

	    var kassenno = $('input[name="kassenno"]').val();
	    var insuranceno = $('input[name="insuranceno"]').val();
	    var status = $('input[name="status"]').val();

	    var betriebsstatten_nr = $('input[name="betriebsstatten_nr"]').val();
	    var lanr = $('input[name="lanr"]').val();
	    var datum = $('input[name="datum"]').val();

	    var med1 = $('input[name="med1"]').val();
	    var med2 = $('input[name="med4"]').val();
	    var med3 = $('input[name="med7"]').val();

	    var jsonData = {
			checkbox: checkbox,
			insurancecomname: insurancecomname,
			patientfirstname: patientfirstname,
			patientlastname: patientlastname,
			street: street,
			zipcode: zipcode,
			city: city,
			birthdate: birthdate,
			kassenno: kassenno,
			insuranceno: insuranceno,
			status: status,
			betriebsstatten_nr: betriebsstatten_nr,
			lanr: lanr,
			datum: datum,
			med1: med1,
			med2: med2,
			med3: med3,
			outputPath: that.outputPath
	    };

	    jsonData = JSON.stringify(jsonData);

	    $.post(that.ajaxPath + '/' + that.printpath, {jsonData: jsonData}, function (data) {
	    	window.open(that.fullOutputPath + '/' + data);
	    })
	}

	function medIconAlt() {
	    var moveLeft = 350;
	    var moveDown = 110;

	    $(".medIcon").hover(
		    function () {
				var tooltip = $(this).attr("data-tooltip");
				$(this).before('<div id="tooltip" style="position: absolute; padding: 5px; z-index: 10; border: 1px solid #BBBBBB; background-color: #F2F2F2;"></div>');
				$("div#tooltip").text(tooltip);
		    },
		    function () {
		    	$("div#tooltip").remove();
		    }
	    );

	    $(".medIcon").mousemove(function (e) {
		$("div#tooltip").css('left', moveLeft);
	    });
	}

	function set_receipt_kv_blank()
	{
	    $('#receipt_type').val("kv_blank");

	    $('.receipt_background').css({
	    	backgroundImage: 'url(' + that.imagePath + '/kv_blank.png)'
	    });

	    $('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();
	    $('#line3').val($('#custom_line_3').val());
	    
	}
	
	function set_receipt_kv_aid()
	{
		$('#receipt_type').val("kv_aid");
		
		$('.receipt_background').css({
			backgroundImage: 'url(' + that.imagePath + '/kv_blank.png)'
		});
		
		$('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();

		if($('#custom_line_3').val() != ""){
			$('#line3').val($('#custom_line_3').val());	
		} else{
			$('#line3').val($('#main_diagnosis').val());
		}
		if(that.mode == "recipe_switch_only") {
			$('#line3').val($('#main_diagnosis').val());
		}
		
	}

	function set_receipt_kv_green() {
	    $('.receipt_background').css({
		backgroundImage: 'url(' + that.imagePath + '/kv_blank_green.png)'
	    });

	    //set hidden text to value "otcGreen - kv_green"
	    $('#receipt_type').val("kv_green");

	    $('#rceipt_form_NameGebInpt').css({
	    	top: '38px'
	    });

	    $('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').hide();
	    $('#line3').val($('#custom_line_3').val());
	}

	function set_receipt_kv_btm() {
	    $('#receipt_type').val("kv_btm");

	    $('.receipt_background').css({
	    	backgroundImage: 'url(' + that.imagePath + '/kv_blank_btm.png)'
	    });

	    $('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();
	    $('#line3').val($('#custom_line_3').val());
	}

	function set_receipt_kv_blue()
	{
	    $('.receipt_background').css({
	    	backgroundImage: 'url(' + that.imagePath + '/kv_blank_blue.png)'
	    });

	    $('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();
	    $('#rceipt_form_VertagsNrinpt').hide()
	    $('#line3').val($('#custom_line_3').val());
	}
	  function byId(id) { return document.getElementById(id); }
	  
	  function set_width() {
	    var value = byId('rwidth').value;
	    byId('resizable').style.width = value + "px";
	  } 
	  
	this.install = function () {
		$( "#resizable" ).resizable({ 
			alsoResize: '#pi_leftcol,.pi_leftcol_sm,.pi_leftcol_sb,.pi_leftcol_st',
			handles: 'e, w'
		});
		
	    if(that.mode == "catalog") {
			var button = $(that.buttonhtml);
			$(button).addClass("pi_butt_searchmedi");
			$(that.buttonanchor).after(button);

			/*$('body').on('click', ".pi_butt_searchmedi", function(){*/
			$('.pi_butt_searchmedi').unbind('click').bind('click', function () {

				$(pi_window).dialog({'width': 900, 'height': 565, 'title': 'MMI PHARMINDEX', 'resizable': 'fixed'});

				showtab('none');
				that.input_active_row = $(this).parents(that.input_rowparent);
				var v = $(this).parents(that.input_rowparent).find(that.input_medname).val();
				$(pi_searchbox).val(v);
				searchkeyword();
			});
	    }
		
	    if(that.mode == "recipe") {
			//Click on MMI from #mediplan_dialog
			$('body').on('click', that.input_receipe_butt, function () {

				$(pi_window).dialog({'width': 900, 'height': 565, 'title': 'MMI PHARMINDEX', 'resizable': 'fixed'});
				if($('#mediplan_dialog'))
				{
					$('#mediplan_dialog').dialog('close');
				}

				showtab('none');
				that.input_active_row = $(this).parents(that.input_rowparent);
				var v = $(this).parents(that.input_rowparent).find(that.input_medname).val();
				$(pi_searchbox).val(v);
				searchkeyword();
				pi_amr.hide();

				var dob = $('input[name="birthdate"]').val();
				that.patientAge = calculateAge(dob);
				that.otcWarningSw = "0";
			});

			$('body').on('change', '#receipt_type', function () {
				var method_prefix = "set_receipt_";
				var receipt = $(this).val();

				if(receipt == "kv_blank" || receipt == "kv_btm" || receipt == "kv_green" || receipt == "kv_blue" || receipt == "kv_aid") {
					eval(method_prefix + receipt + "()");
				}
			});

			if(that.default_type == "kv_blank" || that.default_type == "kv_btm" || that.default_type == "kv_green" || that.default_type == "kv_blue" || that.default_type == "kv_aid") {
				//init default type(the one loaded from db)
				eval("set_receipt_"+that.default_type+"()");
			}
	    }
		
		if(that.mode == "recipe_switch_only") {
			$('body').on('change', '#receipt_type', function () {
				var method_prefix = "set_receipt_";
				var receipt = $(this).val();

				if(receipt == "kv_blank" || receipt == "kv_btm" || receipt == "kv_green" || receipt == "kv_blue" || receipt == "kv_aid") {
					eval(method_prefix + receipt + "()");
				}
			});
			
			if(that.default_type == "kv_blank" || that.default_type == "kv_btm" || that.default_type == "kv_green" || that.default_type == "kv_blue" || that.default_type == "kv_aid")
			{
				//init default type(the one loaded from db)
				eval("set_receipt_"+that.default_type+"()");
			}
		}
	    $(pi_window).hide();
	}
}
