/***************************/
//@Author: Adrian "yEnS" Mato Gondelle
//@website: www.yensdesign.com
//@email: yensamg@gmail.com
//@license: Feel free to use it, but keep this credits please!					
/***************************/

//SETTING UP OUR POPUP
//0 means disabled; 1 means enabled;
var popupStatus = 0;

//loading popup with jQuery magic!
function loadPopup(){
	//loads popup only if it is disabled
	if(popupStatus==0){
		$("#backgroundPopup").css({
			"opacity": "0.7"
		});
		$("#backgroundPopup").fadeIn("slow");
		$("#popupContact").fadeIn("slow");
		popupStatus = 1;
	}
}

//disabling popup with jQuery magic!
function disablePopup(){
	//disables popup only if it is enabled
	if(popupStatus==1){
		$("#backgroundPopup").fadeOut("slow");
		$("#popupContact").fadeOut("slow");
		popupStatus = 0;
	}
}

//centering popup
function centerPopup(srcs){
	
	if(srcs.sr!="inline"){ //not inline modal
	    document.getElementById('add_family_doc').src = srcs.sr;
	}
	document.getElementById('add_family_doc').height = srcs.ht;
	document.getElementById('add_family_doc').width = srcs.wt;
	//request data for centering
	var windowWidth = document.documentElement.clientWidth;
	var windowHeight = document.documentElement.clientHeight;
	
	var popupHeight = $("#popupContact").height();
	var popupWidth = $("#popupContact").width();
	
	var vl = (windowHeight-popupHeight)/2;

	if(typeof(window.pageYOffset)=='number')
	{
		popuptop=parseInt(window.pageYOffset)+parseInt((windowHeight-popupHeight)/2)+'px';
	}
	else if(document.body)
	{
		popuptop=parseInt(document.documentElement.scrollTop)+parseInt((windowHeight-popupHeight)/2)+'px';
	}
	else if(document.documentElement)
	{
		popuptop=parseInt(document.documentElement.scrollTop)+parseInt((windowHeight-popupHeight)/2)+'px';
	}
	
	//centering
	$("#popupContact").css({
		"position": "absolute",
		"top": popuptop,
		"left": windowWidth/2-popupWidth/2
	});
	//only need force for IE6
	
	$("#backgroundPopup").css({
		"height": windowHeight
	});
	
}



//CONTROLLING EVENTS IN jQuery
$(document).ready(function(){
	
	//LOADING POPUP
	//Click the button event!
	$("#button").click(function(){
		//centering with css
		//centerPopup();
		//load popup
		loadPopup();
	});
				
	//CLOSING POPUP
	//Click the x event!
	$("#popupContactClose").click(function(){
		document.getElementById('add_family_doc').src = "";
		var cururl = window.location.pathname;
		var index = cururl.lastIndexOf("/");
		var filename = cururl.substr(index);
		
		if(filename == "/overview"){
		    location.reload();
		}
//		if(filename == "/kvnoassessment"){
//			location.reload();
//		}
		disablePopup();
	});
	/*//Click out event!
	$("#backgroundPopup").click(function(){
		disablePopup();
	});*/
	//Press Escape event!
	$(document).keypress(function(e){
		if(e.keyCode==27 && popupStatus==1){
		    var cururl = window.location.pathname;
		    var index = cururl.lastIndexOf("/");
		    var filename = cururl.substr(index);

		    if(filename == "/overview"){
			location.reload();
		    }
		    disablePopup();
		}
	});

});