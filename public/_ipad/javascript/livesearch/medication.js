function printObject(o) {
	var out = '';
	for (var p in o) {
		out += p + ': ' + o[p] + '\n';
	}
	alert(out);
}

var Timeout_livemedicationsearchkeup;
var livemedicationsearchkeup =   function( e,ele ){
	
	
	clearTimeout(Timeout_livemedicationsearchkeup); //! i don't stop the ajax
	//alert($(ele).attr('id'));
	id = $(ele).attr('id');
	newid =  id.replace('medication','');


	medblur[id] = false;
	var t = 'medication';
	$('#hidd_medication'+newid).val("");
	id = id.substring(t.length);
	medblur = false;
	if( e.keyCode == 40 ) {
		diagsearch = false;
		if( $("#medidropdown"+id).find('.focused' ).length < 1 ) {
			//alert($("#diagnodropdown"+id+'tr:first'));
			//$("#diagnodropdown"+id+' tr:first').removeClass('bluerow');

			$("#medidropdown"+id+' tr:first').addClass('focused');
		//alert($("#diagnodropdown"+dv+'tr:first'));
		//alert('$("#diagnodropdown"+dv+'tr:first')');
		//$('.list_name p:first' ).addClass( 'highlight' );
		} else {
			if(  $("#medidropdown"+id).find('.focused' ).next().length < 1 ) {
				$("#medidropdown"+id).find('.focused' ).removeClass( 'focused' );
				$("#medidropdown"+id+' tr:first').addClass('focused');
			} else {
				var itsNext =  $("#medidropdown"+id).find('.focused' ).removeClass( 'focused' ).next().addClass( 'focused' );
				if( itsNext.is( ':hidden' ) ) {
					$( this ).trigger( {
						type : 'keyup',
						keyCode : 40
					});
				} else {
					var activeItem =  $("#medidropdown"+id).find('.focused' )[0];
					offsetTop = activeItem.offsetTop;
					var container = $('#medidropdowninner'+id);

					upperBound = container.scrollTop();
					lowerBound = upperBound + 175 - 35;
					if (offsetTop < upperBound) {
						container.scrollTop(offsetTop);
					} else if (offsetTop > lowerBound) {
						container.scrollTop(offsetTop - 175 + 35);
					}
				}
			}
		}
		return;
	}
	if( e.keyCode == 38 ) {
		diagsearch = false;
		if(  $("#medidropdown"+id).find( '.focused' ).length < 1 ) {
			$("#medidropdown"+id+' tr:last').addClass('focused');
		} else {
			if(  $("#medidropdown"+id).find( '.focused' ).prev().length < 1 ) {
				$("#medidropdown"+id).find( '.focused' ).removeClass( 'focused' );
				$("#medidropdown"+id+' tr:last').addClass('focused');
			} else {
				var itsPrev = $("#medidropdown"+id).find('.focused' ).removeClass( 'focused' ).prev().addClass( 'focused' );
				if( itsPrev.is( ':hidden' ) ) {
					$( this ).trigger( {
						type : 'keyup',
						keyCode : 38
					});
				} else {
					var activeItem =  $("#medidropdown"+id).find( '.focused' )[0];
					offsetTop = activeItem.offsetTop;
					var container = $('#medidropdowninner'+id);

					upperBound = container.scrollTop();
					lowerBound = upperBound + 175 - 35;
					if (offsetTop < upperBound) {
						container.scrollTop(offsetTop);
					} else if (offsetTop > lowerBound) {
						container.scrollTop(offsetTop - 175 + 35);
					}
				}
			}
		}
		return;
	}
	if( e.keyCode == 13 ) {
		if(!diagsearch)
		{
			$("#medidropdown"+id).find('.focused' ).trigger( 'mousedown' );


			diagsearch = true;
		}
		e.preventDefault();
		//alert(e.target);
		return false;
	}

	if(e.keyCode == 9)
	{
		$("#medidropdown"+id).hide();
		return;
	}

	
	Timeout_livemedicationsearchkeup = setTimeout(function(){
		medicationDropDown(id)
	}, 500);

}


var livemedicationsearchkeupBtm =   function( e,ele ){
	//alert($(ele).attr('id'));
	id = $(ele).attr('id');
	newid =  id.replace('medication','');


	medblur[id] = false;
	var t = 'medication';
	$('#hidd_medication'+newid).val("");
	id = id.substring(t.length);
	medblur = false;
	if( e.keyCode == 40 ) {
		diagsearch = false;
		if( $("#medidropdown"+id).find('.focused' ).length < 1 ) {
			//alert($("#diagnodropdown"+id+'tr:first'));
			//$("#diagnodropdown"+id+' tr:first').removeClass('bluerow');

			$("#medidropdown"+id+' tr:first').addClass('focused');
		//alert($("#diagnodropdown"+dv+'tr:first'));
		//alert('$("#diagnodropdown"+dv+'tr:first')');
		//$('.list_name p:first' ).addClass( 'highlight' );
		} else {
			if(  $("#medidropdown"+id).find('.focused' ).next().length < 1 ) {
				$("#medidropdown"+id).find('.focused' ).removeClass( 'focused' );
				$("#medidropdown"+id+' tr:first').addClass('focused');
			} else {
				var itsNext =  $("#medidropdown"+id).find('.focused' ).removeClass( 'focused' ).next().addClass( 'focused' );
				if( itsNext.is( ':hidden' ) ) {
					$( this ).trigger( {
						type : 'keyup',
						keyCode : 40
					});
				} else {
					var activeItem =  $("#medidropdown"+id).find('.focused' )[0];
					offsetTop = activeItem.offsetTop;
					var container = $('#medidropdowninner'+id);

					upperBound = container.scrollTop();
					lowerBound = upperBound + 175 - 35;
					if (offsetTop < upperBound) {
						container.scrollTop(offsetTop);
					} else if (offsetTop > lowerBound) {
						container.scrollTop(offsetTop - 175 + 35);
					}
				}
			}
		}
		return;
	}
	if( e.keyCode == 38 ) {
		diagsearch = false;
		if(  $("#medidropdown"+id).find( '.focused' ).length < 1 ) {
			$("#medidropdown"+id+' tr:last').addClass('focused');
		} else {
			if(  $("#medidropdown"+id).find( '.focused' ).prev().length < 1 ) {
				$("#medidropdown"+id).find( '.focused' ).removeClass( 'focused' );
				$("#medidropdown"+id+' tr:last').addClass('focused');
			} else {
				var itsPrev = $("#medidropdown"+id).find('.focused' ).removeClass( 'focused' ).prev().addClass( 'focused' );
				if( itsPrev.is( ':hidden' ) ) {
					$( this ).trigger( {
						type : 'keyup',
						keyCode : 38
					});
				} else {
					var activeItem =  $("#medidropdown"+id).find( '.focused' )[0];
					offsetTop = activeItem.offsetTop;
					var container = $('#medidropdowninner'+id);

					upperBound = container.scrollTop();
					lowerBound = upperBound + 175 - 35;
					if (offsetTop < upperBound) {
						container.scrollTop(offsetTop);
					} else if (offsetTop > lowerBound) {
						container.scrollTop(offsetTop - 175 + 35);
					}
				}
			}
		}
		return;
	}
	if( e.keyCode == 13 ) {
		if(!diagsearch)
		{
			$("#medidropdown"+id).find('.focused' ).trigger( 'mousedown' );


			diagsearch = true;
		}
		e.preventDefault();
		//alert(e.target);
		return false;
	}

	if(e.keyCode == 9)
	{
		$("#medidropdown"+id).hide();
		return;
	}

	setTimeout(function(){
		medicationDropDownBtm(id)
	},100);

}
function delmedicationRow(did,patidencrypted)
{

	if(did && $('#hidd_medication'+did).val())
	{

		ajaxCallserver({
			url:'patient/patientmedication?id='+patidencrypted+'&delid='+did+'&mid='+did+'&act=del' //mid for trigger when deleteing the row
		});
	}
}

function medicationDropDown(mid)
{

	var ltrval = document.getElementById('medication'+mid).value;

	if(ltrval)
	{
		ajaxCallserver({
			url:'medication/fetchdropdown',
			callLoading:m_loading,
			loadingOptions:{
				id:mid
			},
			method:'POST',
			data:{
				ltr:ltrval,
				mid:mid
			}
		});
	}
	else
	{
		$('#medidropdown'+mid).hide('slow');
	}
}
function medicationDropDownBtm(mid)
{

	var ltrval = document.getElementById('medication'+mid).value;

	if(ltrval)
	{
		ajaxCallserver({
			url:'medication/fetchdropdown',
			callLoading:m_loading,
			loadingOptions:{
				id:mid
			},
			method:'POST',
			data:{
				ltr:ltrval,
				mid:mid
			}
		});
	}
	else
	{
		$('#medidropdown'+mid).hide('slow');
	}
}
function delMedications(divid,did)
{

	if(did)
	{
		ajaxCallserver({
			url:'medication/bedarfsmedicationedit?delid='+did
			});
	}
	$(divid).remove();
}
function removeMedication(mid)
{
	if(mid>0)
	{
		ajaxCallserver({
			url:'patientform/removekvnomedis?mid='+mid
			});
	}
	$("#line_"+mid).remove();
//dcntr--;
}
function removeMedicationKvno(mid,pid)
{
	if(mid>0)
	{
		ajaxCallserver({
			url:'patient/patientmedication?mid='+mid+'&id='+pid+'&act=del'
			});
	}
	$("#line_"+mid).remove();
//dcntr--;
}
function renewMedicationKvno(mid,pid)
{
	if(mid>0){
		ajaxCallserver({
			url:'patient/patientmedication?mid='+mid+'&id='+pid+'&act=rnw'
			});
	}
	$("#line_r_"+mid).remove();
//dcntr--;
}
var m_loading = function(options){
	var divid = options.id;
	var dlist = '<div class="DropDwnTable" style="margin-top:5px;margin-left:35px; height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble"><tr class="bluerow"><td><img src="images/loading.gif"></td></tr></table></div>';
	document.getElementById('medidropdown'+divid).style.display = "";
	document.getElementById('medidropdown'+divid).innerHTML=dlist;

}

var dg;
var medicdropdiv = function(params){

	var dv = params.didiv;

	var dlist = '<div class="DropDwnTable" id="medidropdowninner'+dv+'" style="height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble">';
	dg = params.medicationarray;
	
	
	if (typeof dg == 'object') {
		
		dlist+='<tr class="bluerow"><td class="first"><strong>'+translate('medication')+' </strong></td><td class="first"><strong>'+translate('select')+'</strong></td></tr>';

		if ( ! $.isEmptyObject(dg.mmi)) {
			
			dlist+='<tr class="focused">'
				+ '<th colspan=2 ><b>'+translate('MMI RESULTS') +'</b></td>'
				+ '</tr>';
		
			$.each(dg.mmi, function(i, val){
				dlist+='<tr class="bluerow" onmousedown="selectMedicationrow(null, null, this, \''+i+'\' , \'mmi\')">'
				+ '<td class="first">'+val.name +'</td>'
				+ '<td>'+translate('select')+'</td>'
				+ '</tr>';
			});
		}
		 
		if ( ! $.isEmptyObject(dg.personal)) {
			
			dlist+='<tr class="focused">'
				+ '<th colspan=2 ><b>'+translate('PERSONAL RESULTS') +'</b></td>'
				+ '</tr>';
		
			$.each(dg.personal, function(i, val){
				dlist+='<tr class="bluerow" onmousedown="selectMedicationrow(null, null, this, \''+i+'\' , \'personal\')">'
				+ '<td class="first">'+val.name +'</td>'
				+ '<td>'+translate('select')+'</td>'
				+ '</tr>';
			});
		}
		
		if ($.isEmptyObject(dg.mmi) && $.isEmptyObject(dg.personal)) {
			dlist+='<tr class="focused">'
				+ '<th colspan=2 >'+translate('noresultfound') +'</td>'
				+ '</tr>';
			var t = setTimeout(function(){
				$('[id^="medication"]', $('#medidropdown'+dv).parents('tr')).trigger('blur');
			},1000);
			$('#medidropdown'+dv).hide();
		}
		
		
	} else {
		//old way
		if(dg.length>0){
			dlist+='<tr class="bluerow"><td class="first"><strong>'+translate('medication')+' </strong></td><td class="first"><strong>'+translate('select')+'</strong></td></tr>';
		}
		
		if(dg.length<1)
		{
			$('#medidropdown'+dv).hide('slow');
			//$('#medication'+dv).val('');
			document.getElementById('hidd_medication'+dv).value ="";
		}

		for(i=0;i<dg.length;i++)
		{
			dlist+='<tr class="bluerow" onmousedown="selectMedicationrow('+i+','+dv+')"><td class="first">'+dg[i].name +'</td><td>'+translate('select')+'</td></tr>';
		}
	}

	dlist+= "</table></div>";

	if(medblur['medication'+dv] == false)
	{
		document.getElementById('medidropdown'+dv).style.display = "";
	}
	document.getElementById('medidropdown'+dv).innerHTML=dlist;
// document.getElementById('msg').innerHTML = "If Medication is not found in list. please add new diagnois";
}

var cn=jsmedcount;
function selectMedicationrow(i,divs, _this_row, _this_i, _this_type)
{
	if (typeof _this_row !== 'undefined' && _this_row !== null) {
		
		$owner_row = $(_this_row).parents('div').parents('tr');

		$('[id^="medication"]', $owner_row).val(dg[_this_type][_this_i].name);
		if(_this_type == "mmi"){
			$('[id^="hidd_medication"]', $owner_row).val("");
		} else{
			$('[id^="hidd_medication"]', $owner_row).val(dg[_this_type][_this_i].id);
		}

		var t = setTimeout(function(){
			$('[id^="medication"]', $owner_row).trigger('blur');
		},55);
		
		$('div[id^="medidropdown"]', $owner_row).hide();
	
				
	} else {
		//old way
		document.getElementById('medication'+divs).value =dg[i].name;
		document.getElementById('medidropdown'+divs).style.display = "none";
		if(_this_type == "mmi"){
			document.getElementById('hidd_medication'+divs).value = "";
		}else{
			document.getElementById('hidd_medication'+divs).value =dg[i].id;
		}
		// 	document.getElementById('dosage'+divs).value =dg[i].dosage;
		// 	document.getElementById('comments'+divs).value =dg[i].comments;
	
		var t = setTimeout(function(){
			$('#medication'+divs).trigger('blur');
		},55);
	}
}

var cn=jsmedcount;

function selectmedication()
{
	var row = document.createElement('tr');
	row.id = "tab"+cn;
	var cell1 = document.createElement('td');
	var cell2 = document.createElement('td');
	var cell3 = document.createElement('td');
	var cell4 = document.createElement('td');

	var ip1 = document.createElement('input');
	ip1.name = "medication["+cn+"]";
	ip1.id = "medication"+cn;
	ip1.type = "text";
	// ip1.size = "45";
	ip1.className = "livesearchmedinp med";
	//ip1.tempid = cn
	// ip1.onkeyup=function(){medicationDropDown(this.tempid);};
	ip1.value = "";



	var ipelement = document.createElement('input');
	ipelement.name = "hidd_medication["+cn+"]";
	ipelement.id = "hidd_medication"+cn;
	ipelement.type = "hidden";

	var divs = document.createElement('div');
	divs.id = "medidropdown"+cn;
	divs.style.position = "absolute";
	divs.className = "livesearchdropdown";

	var ip2 = document.createElement('input');
	ip2.name = "dosage["+cn+"]";
	ip2.id = "dosage"+cn;
	ip2.type = "text";
	ip2.className = "dosage";
	ip2.value = "";

	var ip3 = document.createElement('input');
	ip3.name = "comments["+cn+"]";
	ip3.id = "comments"+cn;
	ip3.type = "text";
	ip3.className = "med";
	ip3.value = "";




	cell4.innerHTML = '<a href="javascript:void(0)" onclick="removeElem(\'#tab'+cn+'\')"><img src="_ipad/images/action_delete.png" border="0" /></a>';

	cell1.appendChild(ip1);
	cell2.appendChild(ip2);
	cell3.appendChild(ip3);
	cell1.appendChild(ipelement);
	cell1.appendChild(divs);

	row.appendChild(cell1);
	row.appendChild(cell2);
	row.appendChild(cell3);
	row.appendChild(cell4);

	$('#medicationgrid_contenttable').append(row);


	$('#medication'+cn).bind('keyup',function(e){
		livemedicationsearchkeup(e,$(this))
		});
	medblur['medication'+cn] = true;

	$('#medication'+cn).bind('blur',function(){

		var t = setTimeout(function(){

			$('#medidropdown'+cn).hide();

		},5);

	});

	$("#medidropdown"+cn).bind('mousedown',function(){


		var t = setTimeout(function(){
			$("#medidropdown"+cn).show();
			$('#medication'+cn).focus();
		},10);

	});

	cn++;
}

function selectbedarfmedication()
{
	var row = document.createElement('tr');
	row.id = "tab"+cn;
	var cell1 = document.createElement('td');
	var cell2 = document.createElement('td');
	var cell3 = document.createElement('td');
	var cell4 = document.createElement('td');

	var ip1 = document.createElement('input');
	ip1.name = "medication["+cn+"]";
	ip1.id = "medication"+cn;
	ip1.type = "text";
	// ip1.size = "45";
	ip1.className = "livesearchmedinp medicdesc";
	//ip1.tempid = cn
	// ip1.onkeyup=function(){medicationDropDown(this.tempid);};
	ip1.value = "";



	var ipelement = document.createElement('input');
	ipelement.name = "hidd_medication["+cn+"]";
	ipelement.id = "hidd_medication"+cn;
	ipelement.type = "hidden";

	var divs = document.createElement('div');
	divs.id = "medidropdown"+cn;
	divs.style.position = "absolute";
	divs.className = "livesearchdropdown";

	var drids = document.createElement('input');
	drids.name = "drid["+cn+"]";
	drids.id = "drid"+cn;
	drids.type = "hidden";


	cell2.innerHTML = '<a href="javascript:void(0)" onclick="delMedications(\'#tab'+cn+'\',\'\')"><img src="_ipad/images/action_delete.png" border="0" /></a>';

	cell1.appendChild(ip1);
	cell1.appendChild(ipelement);
	cell1.appendChild(drids);
	cell1.appendChild(divs);

	row.appendChild(cell1);
	row.appendChild(cell2);

	$('#medicationgrid_contenttable').append(row);


	$('#medication'+cn).bind('keyup',function(e){
		livemedicationsearchkeup(e,$(this))
		});
	medblur['medication'+cn] = true;

	$('#medication'+cn).bind('blur',function(){

		var t = setTimeout(function(){

			$('#medidropdown'+cn).hide();

		},5);

	});

	$("#medidropdown"+cn).bind('mousedown',function(){


		var t = setTimeout(function(){
			$("#medidropdown"+cn).show();
			$('#medication'+cn).focus();
		},10);

	});

	cn++;
}



var cnt = medcount;
function createnewmedic()
{
	var usrstr = "";
	for(var i in jsusers)
	{
		usrstr +='<option value="'+i+'">'+jsusers[i]+'</option>';
	}



	var userdrop = '<select name="add[verordnetvon]['+cnt+']">'+usrstr+'</select>';

	var trInnerHtml = $('<tr id="medadd'+cnt+'"><td align="left" valign="top"><input name="add[medication]['+cnt+']" value="" id="medication'+cnt+'" type="text" class="livesearchmedinp med"><input name="add[hidd_medication]['+cnt+']" value="" id="hidd_medication'+cnt+'" type="hidden"><div id="medidropdown'+cnt+'" style="position:absolute;display:none;" class="listPatMedEd livesearchdropdown"></div></td><td align="left" valign="top"><input  name="add[dosage]['+cnt+']" id="dosage" class="dosage" value=""  /></td><td align="left" valign="top"><input  name="add[comments]['+cnt+']" value=""  /></td><td align="left" valign="top">'+userdrop+'</td><td width="120" align="left" valign="top"><a href="javascript:void(0)" onclick="delmedicationRow('+cnt+');removeElem(\'#medadd'+cnt+'\')"><img src="_ipad/images/action_delete.png" border="0" /></a></td></tr>');

	$('#medaddtable').append(trInnerHtml);


	$('#medication'+cnt).bind('keyup',function(e){
		livemedicationsearchkeup(e,$(this))
		});
	medblur['medication'+cnt] = true;

	$('#medication'+cnt).bind('blur',function(){

		var t = setTimeout(function(){

			$('#medidropdown'+cnt).hide();

		},5);

	});

	$("#medidropdown"+cnt).bind('mousedown',function(){


		var t = setTimeout(function(){
			$("#medidropdown"+cnt).show();
			$('#medication'+cnt).focus();
		},10);

	});

	cnt++;

}

function createnewmedicedit()
{

	var usrstr = "";

	for(var i in jsusers)
	{
		usrstr +='<option value="'+i+'">'+jsusers[i]+'</option>';
	}

	var userdrop = '<select name="verordnetvon['+cnt+']">'+usrstr+'</select>';
	var trInnerHtml = $('<tr id="line_'+cnt+' alt="'+cnt+'"><td align="left" class="date_line_'+cnt+'" valign="top">&nbsp;</td><td align="left" valign="top"> <input name="medication['+cnt+']" value="" id="medication'+cnt+'" type="text" autocomplete="off" class="livesearchmedinp med meds_line_'+cnt+'"><input name="hidd_medication['+cnt+']" value="" id="hidd_medication'+cnt+'" type="hidden"><input type="hidden" id="drid" name="drid['+cnt+']" value="" /><div id="medidropdown'+cnt+'" style="position:absolute;display:none;" class="listPatMedEd livesearchdropdown"></div></td><td align="left" valign="top"><input  name="dosage['+cnt+']" id="dosage" class="dosage dsg_line_'+cnt+'" value=""  /></td><td align="left" id="line_'+cnt+'_row" valign="top"><input  name="comments['+cnt+']" class="med comm_line_'+cnt+'" value=""  /></td><td width="120" id="line_'+cnt+'_row" align="left" valign="top" class="usrname_line_'+cnt+'"></td><td align="left" id="line_'+cnt+'_row"  valign="top">'+userdrop+'</td></tr>');

	$('#medicationedit').append(trInnerHtml);


	$('#medication'+cnt).bind('keyup',function(e){
		livemedicationsearchkeup(e,$(this));
	});
	medblur['medication'+cnt] = true;

	$('#medication'+cnt).bind('blur',function(){

		var t = setTimeout(function(){

			$('#medidropdown'+cnt).hide();

		},5);

	});

	$("#medidropdown"+cnt).bind('mousedown',function(){


		var t = setTimeout(function(){
			$("#medidropdown"+cnt).show();
			$('#medication'+cnt).focus();
		},10);

	});

	cnt++;
}
function createnewschmerzpumpe()
{
	var usrstr = "";
	for(var i in jsusers)
	{
		usrstr +='<option value="'+i+'">'+jsusers[i]+'</option>';
	}



	var userdrop = '<select name="add[verordnetvon]['+cnt+']">'+usrstr+'</select>';

	var trInnerHtml = $('<tr id="medadd'+cnt+'"><td align="left" valign="top"><input name="add[medication]['+cnt+']" value="" id="medication'+cnt+'" type="text" class="livesearchmedinp med"><input name="add[hidd_medication]['+cnt+']" value="" id="hidd_medication'+cnt+'" type="hidden"><div id="medidropdown'+cnt+'" style="position:absolute;display:none;" class="listPatMedEd livesearchdropdown"></div></td><td align="left" valign="top"><input  name="add[dosage]['+cnt+']" id="dosage" class="dosage" value=""  /></td><td align="left" valign="top">'+userdrop+'</td><td align="left" valign="top"><a href="javascript:void(0)" onclick="delmedicationRow('+cnt+');removeElem(\'#medadd'+cnt+'\')"><img src="_ipad/images/action_delete.png" border="0" /></a></td></tr>');

	$('#medaddtable').append(trInnerHtml);


	$('#medication'+cnt).bind('keyup',function(e){
		livemedicationsearchkeup(e,$(this))
	});
	medblur['medication'+cnt] = true;

	$('#medication'+cnt).bind('blur',function(){

		var t = setTimeout(function(){

			$('#medidropdown'+cnt).hide();

		},5);

	});

	$("#medidropdown"+cnt).bind('mousedown',function(){


		var t = setTimeout(function(){
			$("#medidropdown"+cnt).show();
			$('#medication'+cnt).focus();
		},10);

	});

	cnt++;
}

function createnewschmerzpumpeedit()
{
	var usrstr = "";
	for(var i in jsusers)
	{
		usrstr +='<option value="'+i+'">'+jsusers[i]+'</option>';
	}



	var userdrop = '<select name="verordnetvon['+cnt+']">'+usrstr+'</select>';

	var trInnerHtml = $('<tr id="medadd'+cnt+'"><td></td><td align="left" valign="top"><input name="medication['+cnt+']" value="" id="medication'+cnt+'" type="text" class="livesearchmedinp med"><input name="hidd_medication['+cnt+']" value="" id="hidd_medication'+cnt+'" type="hidden"><div id="medidropdown'+cnt+'" style="position:absolute;display:none;" class="listPatMedEd livesearchdropdown"></div></td><td align="left" valign="top"><input  name="dosage['+cnt+']" id="dosage" class="dosage" value=""  /></td><td></td><td align="left" valign="top">'+userdrop+'</td><td align="left" valign="top"><a href="javascript:void(0)" onclick="removeElem(\'#medadd'+cnt+'\')"><img src="_ipad/images/action_delete.png" border="0" /></a></td></tr>');

	$(trInnerHtml).insertBefore('#cDetails');

	$('#medication'+cnt).bind('keyup',function(e){
		livemedicationsearchkeup(e,$(this))
	});
	medblur['medication'+cnt] = true;

	$('#medication'+cnt).bind('blur',function(){

		var t = setTimeout(function(){

			$('#medidropdown'+cnt).hide();

		},5);

	});

	$("#medidropdown"+cnt).bind('mousedown',function(){


		var t = setTimeout(function(){
			$("#medidropdown"+cnt).show();
			$('#medication'+cnt).focus();
		},10);

	});

	cnt++;
}

function createnewmedicrowbedarf()
{

	var usrstr = "";
	for(var i in jsusers)
	{
		usrstr +='<option value="'+i+'">'+jsusers[i]+'</option>';
	}



	var userdrop = '<select name="verordnetvon['+cnt+']">'+usrstr+'</select>';

	var trInnerHtml = $('<tr id="medadd'+cnt+'"><td align="left" valign="top"><input name="addbedarf[medication]['+cnt+']" value="" id="medication'+cnt+'" type="text" class="livesearchmedinp med"><input name="addbedarf[hidd_medication]['+cnt+']" value="" id="hidd_medication'+cnt+'" type="hidden"><div id="medidropdown'+cnt+'" style="position:absolute;display:none;" class="listPatMedEd livesearchdropdown"></div></td><td align="left" valign="top"><input  name="addbedarf[dosage]['+cnt+']" id="dosage" class="dosage" value=""  /></td><td align="left" valign="top"><input  name="addbedarf[comments]['+cnt+']" value=""  /></td><td align="left" valign="top">'+userdrop+'</td><td width="120" align="left" valign="top"><a href="javascript:void(0)" onclick="delmedicationRow('+cnt+');removeElem(\'#medadd'+cnt+'\')"><img src="_ipad/images/action_delete.png" border="0" /></a></td></tr>');

	$('#medbedarfaddtable').append(trInnerHtml);


	$('#medication'+cnt).bind('keyup',function(e){
		livemedicationsearchkeup(e,$(this))
		});
	medblur['medication'+cnt] = true;

	$('#medication'+cnt).bind('blur',function(){

		var t = setTimeout(function(){

			$('#medidropdown'+cnt).hide();

		},5);

	});

	$("#medidropdown"+cnt).bind('mousedown',function(){


		var t = setTimeout(function(){
			$("#medidropdown"+cnt).show();
			$('#medication'+cnt).focus();
		},10);

	});

	cnt++;
}



function createnewmedicorder()
{

	var usrstr = "";
	for(var i in jsmedis) {
		usrstr +='<option value="'+i+'">'+jsmedis[i]+'</option>';
	}



	var userdrop = '<select style="width:100%" name="medication['+cnt+']" style=width:100%;>'+usrstr+'</select>';

	var trInnerHtml = $('<tr id="medadd'+cnt+'"><td align="left" valign="top">'+userdrop+'</td><td width="60" align="left" valign="top"><a href="javascript:void(0)" onclick="removeElem(\'#medadd'+cnt+'\')"><img src="_ipad/images/action_delete.png" border="0" /></a></td></tr>');

	$('#medaddtable').append(trInnerHtml);

	cnt++;
}

