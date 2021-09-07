var search_loading_share = function(){
	$('#searchdropdown_share').slideDown('slow');
	var dlist = '<div class="SearchDropDwnTable" style="margin-top:5px;margin-left:35px;"><table border="0" class="navigateable" align="left" cellpadding="0" cellspacing="0" class="SearchDropdwntble"><tr class="bluerow"><td><img src="images/loading.gif"></td></tr></table></div>';
	document.getElementById('searchdropdown_share').style.display = "";
	document.getElementById('searchdropdown_share').innerHTML=dlist;

}

var refs;
var searchdropdiv_share = function(params){
	var trcolor;
	refs = params.refs;

	if(refs.length==1)
	{
		shortcut.add("Enter",function() {
//			document.location.href = appbase+'invoice/createinvoice?inv_id=&invoicepid='+refs[0].id;
			$('#searchdropdown_share').slideUp('slow');

			var selected_list = '<table class="datatable" style="width:100%"><thead><tr class="BlueBg" ><td class="first" width="233">'+translate('epid')+'</td><td class="first" width="233">'+translate('firstname')+'</td><td width="200">'+translate('lastname')+' </td><td width="200">'+translate('admissiondate')+' </td><td width="200">'+translate('dateofbirth')+' </td></tr></thead><tbody>';
			selected_list+='<tr class="row">';
			selected_list+='<td class="first" width="233">'+refs[i].epid +'</td>';
			selected_list+='<td class="first" width="233">'+refs[i].first_name +'</td>';
			selected_list+='<td width="200">'+refs[i].last_name +'</td>';
			selected_list+='<td width="200">'+refs[i].admission_date +'</td>';
			selected_list+='<td width="200">'+refs[i].birthd +'</td>';
			selected_list+='</tr></tbody></table>';
			$('#searchdropdown_share_selected').html(selected_list);
			$('#patientid').val(refs[0].id);
			$('#searchdropdown_share_selected').slideDown('slow');
			toggle_shortcuts();


		});
	}
	if(refs.length<1)
	{
		$("#searchdropdown_share").css({
			'height':'50px'
		});
		$('#searchdropdown_share').hide('slow');
		var dlist ='<div style="margin-top:5px;margin-left:35px;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="overviewdatatable"><tr><td colspan="3" class="BlueBox">'+translate('noresultfound')+'</td></tr></table>';
	}
	else
	{
		$("#searchdropdown_share").css({
			'height':'205px'
		});
		var dlist = '<div class="grid"><table border="0" align="left" cellpadding="0" cellspacing="0" class="overviewdatatable" id="topsearchtable" width="100%">';
		dlist+='<thead><tr class="BlueBg" ><td class="first" width="233">'+translate('epid')+'</td><td class="first" width="233">'+translate('firstname')+'</td><td width="200">'+translate('lastname')+' </td><td width="200">'+translate('admissiondate')+' </td><td width="200">'+translate('dateofbirth')+' </td></tr></thead><tbody>';

	}
	var currentstatus = '9999999';

	for(i=0;i<params.refs.length;i++)
	{
		if(params.refs[i].status != currentstatus){
//			if(params.refs[i].status == '0'){
//				dlist+= ' <tr><td colspan="5">';
//				dlist+= '<div class="searchledgend">';
//				dlist+= '<div class="activerow">aktive Patienten</div>';
//				dlist+= '</div></td></tr>';
//			}else if(params.refs[i].status == '1'){
//				dlist+= ' <tr><td colspan="5">';
//				dlist+= '<div class="searchledgend">';
//				dlist+= '<div class="dischrgedrow">Entlassene Patienten</div>';
//				dlist+= '</div></td></tr>';
//			}else if(params.refs[i].status == '2'){
//				dlist+= ' <tr><td colspan="5">';
//				dlist+= '<div class="searchledgend">';
//				dlist+= '<div class="standbyrow">Anfragen</div>';
//				dlist+= '</div></td></tr>';
//			}
			
			if(params.refs[i].status == '0'){
				dlist+= ' <tr><td colspan="5">';
				 dlist+= '<div class="searchledgend">';
				 dlist+= '<div class="activerow">aktive Patienten</div>';
				 dlist+= '</div></td></tr>';
			}else if(params.refs[i].status == '1'){
				dlist+= ' <tr><td colspan="5">';
				 dlist+= '<div class="searchledgend">';
				 dlist+= '<div class="dischrgedrow">Entlassene Patienten</div>';
				 dlist+= '</div></td></tr>';
			}else if(params.refs[i].status == '2'){
				dlist+= ' <tr><td colspan="5">';
				 dlist+= '<div class="searchledgend">';
				 dlist+= '<div class="standbyrow">Anfragen</div>';
				 dlist+= '</div></td></tr>';
			}else if(params.refs[i].status == '3'){
				dlist+= ' <tr><td colspan="5">';
				 dlist+= '<div class="searchledgend">';
				 dlist+= '<div class="standbyrow">Gelöschte Anfragen</div>';
				 dlist+= '</div></td></tr>';
			}

			currentstatus = params.refs[i].status;

		}
		dlist+='<tr class="row" onclick="selectSharePatient(\''+params.refs[i].id+'\', \''+params.refs[i].epid+'\', \''+escape(params.refs[i].first_name)+'\', \''+escape(params.refs[i].last_name)+'\', \''+params.refs[i].admission_date+'\', \''+params.refs[i].birthd+'\')">';
		dlist+='<td class="first" width="233">'+params.refs[i].epid +'</td>';
		dlist+='<td class="first" width="233">'+params.refs[i].first_name +'</td>';
		dlist+='<td width="200">'+params.refs[i].last_name +'</td>';
		dlist+='<td width="200">'+params.refs[i].admission_date +'</td>';
		dlist+='<td width="200">'+params.refs[i].birthd +'</td>';
		dlist+='</tr>';
	}

	dlist+= '</tbody</table> </div>';


	document.getElementById('searchdropdown_share').style.display = "";
	document.getElementById('searchdropdown_share').innerHTML=dlist;

}

function selectSharePatient(pid, epid, firstname, lastname, admission, dob)
{
	//	document.location.href = appbase+'invoice/createinvoice?inv_id=&invoicepid='+i;
	$('#searchdropdown_share').slideUp('slow');

	var selected_list = '<table class="datatable" style="width:100%"><thead><tr class="BlueBg" ><td class="first" width="233">'+translate('epid')+'</td><td class="first" width="233">'+translate('firstname')+'</td><td width="200">'+translate('lastname')+' </td><td width="200">'+translate('admissiondate')+' </td><td width="200">'+translate('dateofbirth')+' </td></tr></thead><tbody>';
	selected_list+='<tr class="row">';
	selected_list+='<td class="first" width="233">'+epid +'</td>';
	selected_list+='<td class="first" width="233">'+unescape(firstname)+'</td>';
	selected_list+='<td width="200">'+unescape(lastname) +'</td>';
	selected_list+='<td width="200">'+admission +'</td>';
	selected_list+='<td width="200">'+dob +'</td>';
	selected_list+='</tr></tbody></table>';
	$('#patientid').val(pid);

	$('#searchdropdown_share_selected').html(selected_list);

	$('#searchdropdown_share_selected').slideDown('slow');
	$('#patientsearch_share').val(epid);
	toggle_shortcuts()
}

var livedirectsearchkeup_share =   function( e,ele ){
	id = $(ele).attr('id');


	if( e.keyCode == 40 ) {

		if( $( '.focused' ).length < 1 ) {
			$("#searchdropdown_share tbody tr:first").addClass('focused');
		} else {
			if( $( '.focused' ).next().length < 1 ) {
				$( '.focused' ).removeClass( 'focused' );
				$("#searchdropdown_share tr:first").addClass('focused');
			} else {
				var itsNext = $( '.focused' ).removeClass( 'focused' ).next().addClass( 'focused' );
				if( itsNext.is( ':hidden' ) ) {
					$( this ).trigger( {
						type : 'keyup',
						keyCode : 40
					});
				} else {
					$( '.focused' )[0].scrollIntoView();
				}
			}
		}
		return;
	}
	if( e.keyCode == 38 ) {
		if( $( '.focused' ).length < 1 ) {
			$("#searchdropdown_share tr:last").addClass('focused');
		} else {
			if( $( '.focused' ).prev().length < 1 ) {
				$( '.focused' ).removeClass( 'focused' );
				$("#searchdropdown_share tr:last").addClass('focused');
			} else {
				var itsPrev = $( '.focused' ).removeClass( 'focused' ).prev().addClass( 'focused' );
				if( itsPrev.is( ':hidden' ) ) {
					$( this ).trigger( {
						type : 'keyup',
						keyCode : 38
					});
				} else {
					$( '.focused' )[0].scrollIntoView();
				}
			}
		}
		return;
	}
	if( e.keyCode == 13 ) {
		$( '.focused' ).trigger( 'click' );
		return;
	}

	newsearch_share($(ele).val());

}

