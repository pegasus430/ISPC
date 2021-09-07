//<script type="text/javascript">
//
//   function getcourseinfo(par)
//   {
//      ajaxCallserver({url:'patient/doctorlettercourse?shrt='+par.name+'&id='+'<? echo $_GET['id'];?>'});
//   }
//
//</script>
//<script type="text/javascript">
//	tinyMCE.init({
//
//// General options
////plugins :"-example",
//mode : "exact",
//language : "en",
//elements : "content",
//theme : "advanced",
//relative_urls : false,
//absolute_urls : true,
//
//file_browser_callback : "openSwampyBrowser",
//
//
//
//
//
//plugins : "spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
//
//// Theme options
//        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,||,justifyleft,justifycenter,justifyright,justifyfull,|",
//        theme_advanced_buttons2 : "",
//        theme_advanced_buttons3 : "",
//        theme_advanced_buttons4 : "",
//        theme_advanced_toolbar_location : "top",
//        theme_advanced_toolbar_align : "left",
//        theme_advanced_statusbar_location : "bottom",
//        theme_advanced_resizing : false,
//
//content_css : "<? echo RES_FILE_PATH;  ?>/css/style.css",
//
//template_replace_values : {
//
//username : "Some User",
//
//staffid : "991234"
//
//}
//
//
//});
//
// var doctletter = function(params){
//
//	   var comma = "";
//	   var sels = document.getElementById('selectedchecks').value;
//	   if(sels.length>0)
//	   {
//	      comma = ",";
//	   }
//	   sels = sels+comma+params.selectedchecks;
//	   document.getElementById('selectedchecks').value = sels;
//       if(params.countblocks>0)
//	   {
//      	tinyMCE.activeEditor.setContent( tinyMCE.activeEditor.getContent()+params.refs);
//	   }
//
//	 // tinyMCE.activeEditor.updateContent('content');
//   }
//
//
//
//</script>

$(function() {


	$( "#dialog_addressbook" ).dialog({
		resizable: false,
		modal: true,
		autoOpen: false,
		width:750,
		height:500,
		open: function(){
			 $('input[name=recipient]:checked').each(function(){
				 $(this).attr('checked', false);
			 })
		},
		close: function(){
			$('#addressbook-container').remove();
			$('#addressbook-tabs').hide();
			$('#pat').show();
		},
		buttons: {
			"Adresse auswählen": function() {
				var rid = $('input[name=recipient]:checked').val(); //recipient id

				if(!rid){
					alert('Bitte wählen Sie Empfänger.');

				} else {
				//to avoid errors if nothing is selected
				var rid_array = rid.split('-'); //2 strings (1 is type second is entity id

					$.ajax({
						url: "patient/abimportfetchajax",
						type: "POST",
						data: {type : rid_array[0], eid : rid_array[1]},
						dataType: "json",
						success: function(response){
							$('#invoice_address').val(response.address);
							$('#dialog_addressbook').dialog( "close" );
						}
					});
				}
			},
			"Abbrechen": function() {
				$( this ).dialog( "close" );
			}
		}

	});

	$('#abook').live('click', function(){
		$( "#dialog_addressbook" ).dialog('open');
		if(hide_patient_tab == "1"){
			ajaxCallserver({callLoading:pl_loading,url:'addressbook/fetchlist?source=brief&type=All'});
			$('#All').click();

		} else {
			$('#patienttab').click();
			$('#pat').show();
		}


	});

	//  When user clicks on tab, this code will be executed
	$("#tabsaddr li").live('click',function() {
		//  First remove class "active" from currently active tab
		$("#tabsaddr li").removeClass('active');

		//  Now add class "active" to the selected/clicked tab
		$(this).addClass("active");

		//  Hide all tab content
		$(".tabaddr_content").hide();

		//  Here we get the href value of the selected tab
//		var selected_tab = $(this).find("a").attr("href"); //to avoid following error "Uncaught Syntax error, unrecognized expression: #"

		//  Show the selected tab content
//		$(selected_tab).fadeIn(); //to avoid following error "Uncaught Syntax error, unrecognized expression: #"

		//  At the end, we add return false so that the click on the link is not executed
		return false;
	});

	$('.addr-letter').live('click',function() {
		ajaxCallserver({callLoading:pl_loading,url:'addressbook/fetchlist/?source=brief&slet='+$(this).attr('rel') + '&type=' + $('#type_c').val()});

		$('.addr-letter').each(function() {
			$(this).removeClass('selected');
		});
		$('#fav-filter').parent().css('background','#fff');
		$(this).addClass('selected');

		return false;
	});

	$('#default-all').live('click',function() {
		ajaxCallserver({callLoading:pl_loading,url:'addressbook/fetchlist?source=brief&type=' + $('#type_c').val()});
		$('.addr-letter').each(function() {
			$(this).removeClass('selected');
		});
		$('#fav-filter').parent().css('background','#fff');
		return false;
	});

	$('#fav-filter').live('click',function() {
		ajaxCallserver({callLoading:pl_loading,url:'addressbook/fetchlist/?source=brief&fav=1&type=' + $('#type_c').val()});

		$('.addr-letter').each(function() {
			$(this).removeClass('selected');
		});

		$(this).parent().css('background','black');

		return false;
	});

	$('.addr-more').live('click',function() {
		$(this).parent().find('.addr-details').toggle();
		return false;
	});


	$('tr.alternable').live('mouseover',function(){
		$(this).addClass('hover');
	});

	$('tr.alternable').live('mouseout',function(){
		$(this).removeClass('hover');
	});

	$('.addr-star').live('click',function(){
		var fav_id = $(this).attr('rel');
		var user_id = $('#user_'+fav_id).val();
		var type = $('#type_'+fav_id).val();
		var isfavorite = $(this).attr('title');

		if(isfavorite != '') {
			$.get('addressbook/deladdrfavorite/?usr_id='+user_id+'&fav_id='+fav_id+'&type='+type, function(data) {
				//alert(data);
			});
			$(this).removeClass('favorite');
		} else {
			$.get('addressbook/addaddrfavorite/?usr_id='+user_id+'&fav_id='+fav_id+'&type='+type, function(data) {
				//alert(data);
			});
			$(this).addClass('favorite');
		}

		return false;
	});
	//other ajax buttons
	$('.tabf').live('click',function() {
		var thetype = $(this).attr('id');
		$('#type_c').val(thetype);
		$('#addressbook-tabs').show();
		ajaxCallserver({callLoading:pl_loading,url:'addressbook/fetchlist?source=brief&type='+ thetype});
		$('.addr-letter').each(function() {
			$(this).removeClass('selected');
		});
		$('#fav-filter').parent().css('background','#fff');
		$('#pat').hide();
		return false;
	});
	//locations button
	$('.tabl').live('click',function() {
		var thetype = $(this).attr('id');
		$('#type_c').val(thetype);
		$('#addressbook-tabs').hide();
		ajaxCallserver({callLoading:pl_loading,url:'locations/fetchlist?source=brief&clm=pk&ord=ASC&pgno=0'});
		$('#pat').hide();
		return false;
	});
	//sonstiges button
	$('.tabs').live('click',function() {
		var thetype = $(this).attr('id');
		$('#type_c').val(thetype);
		$('#addressbook-tabs').hide();
		ajaxCallserver({callLoading:pl_loading,url:'locations/fetchuserlist?source=brief'});
		$('#pat').hide();
		return false;
	});

	//pattient tab
	$('#patienttab').live('click', function(){
		$('#addressbook-container').remove();
		$('#locationFetchlist_contenttable').remove();
		$('#addressbook-tabs').hide();
		$('#pat').show();
		return false;
	});

	//show/hide more details
	$('.row').live('click', function(){
		var id = $(this).attr('alt');

		if($('#moreinfo-'+id).hasClass("open")){
			$('#moreinfo-'+id).removeClass("open");
			//				$('#moreinfo-'+id).toggle("slow");
			$('#moreinfo-'+id).slideUp("slow");

		} else {
			//				$('#moreinfo-'+id).toggle("slow");
			$('#moreinfo-'+id).slideDown("slow");
			$('#moreinfo-'+id).addClass("open");
		}
	});

	//edit
	$('.edit').live('click', function(){
		var idloc = $(this).attr('alt');

		//populate form fields
		$('#fname').val($('#fname-'+idloc).val());
		$('#lname').val($('#lname-'+idloc).val());
		$('#companyname').val($('#companyname-'+idloc).val());
		$('#street').val($('#street-'+idloc).val());
		$('#zipcodex').val($('#zip-'+idloc).val());
		$('#cityx').val($('#city-'+idloc).val());
		$('#phone1').val($('#phone1-'+idloc).val());
		$('#phone2').val($('#phone2-'+idloc).val());
		$('#faxx').val($('#fax-'+idloc).val());
		$('#comment').text($('#comment-'+idloc).val());

		//hidden id for update
		$('#hiddedtid').val(idloc);
	});
	//reset
	$('#clear').live('click', function() {
		$('#fname').val("");
		$('#lname').val("");
		$('#companyname').val("");
		$('#street').val("");
		$('#zipcodex').val("");
		$('#cityx').val("");
		$('#phone1').val("");
		$('#phone2').val("");
		$('#faxx').val("");
		$('#comment').text("");
		$('#hiddedtid').val("");
	});
//	// delete uncomment if delete is nedded in ab modal from briefe
//	$('.delete').live('click', function(){
//		var delid= $(this).attr('alt');
//
//		jConfirm("<? echo $this->translate('confirmdeleterecord'); ?>", "<? echo $this->translate('confirmdeletetitle'); ?>", function(r) {
//			if(r){
//				ajaxCallserver({url:'patient/addressbook?id=<?php echo $_REQUEST["id"]; ?>&step=del&delid='+delid});
//				$('#listlocations_trcontent'+delid).remove();
//			}
//		});
//	});

	$('#submitsecond').live('click', function(){
		$('#userlocation').submit();
	});
	$('#addressbook-tabs').hide();


	$('.alternable').live('click', function(){
		$(this).children('.radio').find('input').attr('checked', true);
	});
});

var pl_loading = function()
{
	var dlist = '<br /><div class="loadingdiv" align="center" style="width: 660px;float: left; height:100%; vertical-align:middle;margin-top: 50px;"><img src="'+res_file_path+'/images/loader_transparent.gif" width="32"><br />	Loading... please wait</div>';
	document.getElementById('content_dialog').innerHTML = dlist;
}

var callBack = function(params)
{
	document.getElementById('content_dialog').innerHTML = params.patientlist;
}

function toggleDiv(id) {

	if($('#moreinfo-'+id).hasClass("open")){
		$('#moreinfo-'+id).removeClass("open");
		//				$('#moreinfo-'+id).toggle("slow");
		$('#moreinfo-'+id).slideUp("slow");
	} else {
		//				$('#moreinfo-'+id).toggle("slow");
		$('#moreinfo-'+id).slideDown("slow");
		$('#moreinfo-'+id).addClass("open");
	}
}
