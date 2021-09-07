tinyMCE.init({

	// General options
	//plugins :"-example",
	mode : "exact",
	language : "en",
	elements : "invoice_address, sapv_footer, sgbv_footer, reminder_text",
	theme : "advanced",
	relative_urls : false,
	absolute_urls : true,

	file_browser_callback : "openSwampyBrowser",
	//entity_encoding : "raw",




	plugins : "spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

	// Theme options
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,||,justifyleft,justifycenter,justifyright,justifyfull,|",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_buttons4 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : false,
	content_css : res_file_path+"/css/style.css",
	template_replace_values : {
		username : "Some User",
		staffid : "991234"
	}
});
