$(function(){
   
   $('.AddButton').click(function(){

	var aid = $(this).attr('id').substr(('addm_').length);

	if($('#meta_'+aid).hasClass("hideMeta"))
		{
			
				
				$('#meta_'+aid).removeClass('hideMeta');
				$('#meta_'+aid).show();
				 $("#AddImg"+aid).attr("src","images/action_minus.png");
			
		}
		else
		{
			if($("#meta_title_"+aid+"_0").val()==0 && $("#meta_title_"+aid+"_0").val()==0 && $("#meta_title_"+aid+"_0").val()==0)
			{
				$('#meta_'+aid).addClass('hideMeta');
				$('#meta_'+aid).hide();
				 $("#AddImg"+aid).attr("src","images/action_add.png");
				
				
			}
		}
	
	
	
	});

});




	
	


function deldiagnosisRow(ids,did)
{
	$(ids).remove();
	
	if(did)
	{
	  ajaxCallserver({url:'patient/patdiagnoedit?delid='+did});
	 } 
} 

function diagnosisDropDown(did)
{

 /*if(document.getElementById('icd'+did).checked=="1")
 {
   var tabname = "dig";
 }
 else
 {
  var tabname = "text";	 
 }
 */


 var tabname = "dig"
 var ltrval = document.getElementById('diagnosis'+did).value;
 


 if(ltrval)
 {
   if(ltrval.length>1)
   {
  // 	 ltrval = encodeURI(ltrval);
 // alert('diagnosis/fetchdropdown?ltr='+ltrval+'&did='+did+'&tb='+tabname);
	ajaxCallserver({url:'diagnosis/fetchdropdown',
				   callLoading:d_loading,
				   loadingOptions:{id:did},
				   method:'POST',
				   data:{ltr:ltrval,did:did,tb:tabname}
				   });
	 
   }
  
 }
 else
 {
  // alert("provide diagnosis");
   $('#diagnodropdown'+did).hide('slow');
  
 }
} 

function removeDiaElem(ids,rid)
{
  ajaxCallserver({url:'diagnosis/removerecord?rid='+rid});
  $(ids).remove();
}
function removeElements(id1,id2)
{
	$(id1).remove();
	$(id2).remove();
	//dcntr--;
}

function removeElementsfromTab(id1,id2,rid)
{
	if(rid>0)
	{
	   ajaxCallserver({url:'diagnosis/removerecord?rid='+rid});	
	}
	$(id1).remove();
	$(id2).remove();
	//dcntr--;
}


var d_loading = function(options){
var divid = options.id;
var dlist = '<div class="DropDwnTable" style="margin-top:5px;margin-left:35px; height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble"><tr class="bluerow"><td><img src="images/loading.gif"></td></tr></table></div>';
document.getElementById('diagnodropdown'+divid).style.display = "";
document.getElementById('diagnodropdown'+divid).innerHTML=dlist;

//jQuery.tableNavigation();	 
}

var dg;
var diagnodropdiv = function(params){

var dv = params.didiv;

var dlist = '<div class="DropDwnTable" id="diagnodropdowninner'+dv+'" style="height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble">';
	dg = params.diagnosisarray;
	
	
   

	//alert(dv);
	if(dg.length<1)
	{
	 	dlist+='<tr><td colspan="2" class="bluerow">'+translate('noresultsfoundpleaseaddnewdiagnosis')+'</td></tr>';
		$('#diagnodropdown'+dv).hide('slow');
		

	}
	
	
	
	if(params.tabname=="text")
	{
		for(i=0;i<dg.length;i++)
		{
			dlist+='<tr class="bluerow" onmousedown="selectDiagnosis2('+i+','+dv+')"><td class="first">'+dg[i].free_name +'</td><td class="first">'+dg[i].free_desc +'</td></tr>';
		}
		dlist+= "</table></div>";
	}
	
	if(params.tabname=="dig")
	{
	 if(dg.length<1)
	 {
	    // dlist+='<tr><td colspan="2" class="BlueBox">No Results Found Please Try Another Word</td></tr>';
		 
	 }
	 else
	 {
		dlist+='<tr class="bluerow"><td class="first"><strong>'+translate('icdcode')+'</strong></td><td class="first"><strong>'+translate('description')+'</strong></td></tr>';	  
		for(i=0;i<dg.length;i++)
		{

			dlist+='<tr class="bluerow" onmousedown="selectDiagnosis('+i+','+dv+')"><td class="first">'+dg[i].icd_primary +'</td><td class="first">'+dg[i].description +'</td></tr>';
		}
	 }
	 dlist+= "</table></div>";
	}


	
	document.getElementById('hidd_diagnosis'+dv).value="";
	if(diagnosisblur['diagnosis'+dv]==false){
	document.getElementById('diagnodropdown'+dv).style.display = "";
	}
	document.getElementById('diagnodropdown'+dv).innerHTML=dlist;
	
}

function selectDiagnosis(i,did)
{

if($('#course_type'+did).val() == "D" || $('#course_type'+did).val() == "d" || $('#course_type'+did).val() == "H" || $('#course_type'+did).val() == "h"){
	
	text = dg[i].id+'|'+dg[i].icd_primary+'|'+dg[i].description;
//	$('#course_title'+did).val(text);
	document.getElementById('course_title'+did).value =text;
}	
	document.getElementById('icdnumber'+did).value =dg[i].icd_primary ;
	 document.getElementById('diagnosis'+did).value = dg[i].description;	
	 document.getElementById('hidd_diagnosis'+did).value =dg[i].id;
	 document.getElementById('hidd_icdnumber'+did).value =dg[i].id;
	 
	 $('#hidd_tab'+id).val("dig");
		
	 document.getElementById('diagnodropdown'+did).style.display = "none";
	 //$('#diagnodropdown'+did).fadeOut(200);
	 var t = setTimeout(function(){$('#diagnosis'+did).trigger('blur');},15);
	
	
}

function selectDiagnosis2(i,did)
{
	document.getElementById('diagnosis'+did).value =dg[i].free_name;
	document.getElementById('hidd_diagnosis'+did).value =dg[i].id;
	document.getElementById('diagnodropdown'+did).style.display = "none";
	 var t = setTimeout(function(){$('#diagnosis'+did).trigger('blur');},15);
	return;

}



  var cns=jcount;
 

  function admission_selectdignosis()
  {
     dcntr = cns;
	 var radiostr = "";
	
	 for(var i=0;i<jarr.length;i++)
	 {
		 
		 radiostr += '<td><input type="radio" name="dtype['+cns+']" id="dtype'+cns+'" value="'+jarr[i].id+'"></td>';
	 }
	 
	
	 var dignometa = "";
	 for(var i in jsdiagnosismeta)
	 {
		 if(i=="" || i ==0 || i == "undefined"){
			 dignometa +='<option value="'+i+'" selected="selected">'+jsdiagnosismeta[i]+'</option>';
		 } else {
			 dignometa +='<option value="'+i+'">'+jsdiagnosismeta[i]+'</option>';
		 }
		 
	 }
	 
	  var dignometa1 = '<select name="meta_title['+cns+'][]" id="meta_title_'+cns+'_0">'+dignometa+'</select>';
	  var dignometa2 = '<select name="meta_title['+cns+'][]" id="meta_title_'+cns+'_1">'+dignometa+'</select>';
	  var dignometa3 = '<select name="meta_title['+cns+'][]" id="meta_title_'+cns+'_2">'+dignometa+'</select>';
	  
	 
	 var trInnerHtml = $('<tr id="stab'+cns+'"><td valign="top" id="listadmissiondiagnosistd_i'+cns+'"><input name="icdnumber['+cns+']" id="icdnumber'+cns+'" value="" class="livesearchicdinp diagicd" type="text" /><div id="icddiagnodropdown'+cns+'" style="position: absolute; display:none;" class="medicationDiagnDrp icdlivesearchdropdown"></div></td><td valign="top" id="listadmissiondiagnosistd_d'+cns+'"><input name="diagnosis['+cns+']" id="diagnosis'+cns+'" value="" size="33" class="livesearchinp diagdesc" type="text" /><input name="hidd_icdnumber['+cns+']" value="" id="hidd_icdnumber'+cns+'" type="hidden"><input name="hidd_diagnosis['+cns+']" value="" id="hidd_diagnosis'+cns+'" type="hidden"><input type="hidden" id="hidd_tab'+cns+'" name="hidd_tab['+cns+']" value="" /><div id="diagnodropdown'+cns+'" style="position: absolute; display:none;" class="medicationDiagnDrp livesearchdropdown"></div></td>'+radiostr+'<td class="" valign="middle" align="center"><a class="AddButton" id="addm_'+cns+'"><img src="images/action_add.png" border="0" id="AddImg'+cns+'"></a></td><td  valign="middle" align="center"><a href="javascript:void(0)" onclick="removeElements(\'#stab'+cns+'\',\'#meta_'+cns+'\')"><img src="images/action_delete.png" border="0" /></a></td></tr><tr id="meta_'+cns+'" style="display:none" class="hideMeta"><td>&nbsp;</td><td colspan="5"><table ><tr><td ><label class="forlabel">Metastasierung</label>'+dignometa1+'</td></tr><tr><td ><label class="forlabel">Metastasierung</label>'+dignometa2+'</td></tr><tr><td ><label class="forlabel">Metastasierung</label>'+dignometa3+'</td></tr></table></td></tr>');
	 
	  $('#samtab').append(trInnerHtml);
	 
	 
	 $('#stab'+cns).find(".AddButton").click(function(){
		var aid = $(this).attr('id').substr(('addm_').length);
		
		
		
		
		if($('#meta_'+aid).hasClass("hideMeta"))
		{
			
				
				$('#meta_'+aid).removeClass('hideMeta');
				$('#meta_'+aid).show();
				 $("#AddImg"+aid).attr("src","images/action_minus.png");
			
		}
		else
		{
			if($("#meta_title_"+aid+"_0").val()==0 && $("#meta_title_"+aid+"_0").val()==0 && $("#meta_title_"+aid+"_0").val()==0)
			{
				$('#meta_'+aid).addClass('hideMeta');
				$('#meta_'+aid).hide();
				 $("#AddImg"+aid).attr("src","images/action_add.png");
				
				
			}
		}
	});
	 
	 $('#diagnosis'+cns).bind('keydown',function(e){livesearchkeup(e,$(this))});
	diagnosisblur['diagnosis'+cns] = true;
	
	
	$('#diagnosis'+cns).bind('blur',function(){
	
		var t = setTimeout(function(){
			
			$('#diagnosis'+cns).hide();
			
		},5);
											
	});
	
	$("#diagnodropdown"+cns).bind('mousedown',function(){
	
		
		var t = setTimeout(function(){$("#diagnodropdown"+cns).show();$('#diagnosis'+cns).focus();},10);
		
	});
	
	$('#icdnumber'+cns).bind('keydown',function(e){icdlivesearchkeup(e,$(this))});
	icddiagnosisblur['icdnumber'+cns] = true;
	
	$('#icdnumber'+cns).bind('blur',function(){
	
		var t = setTimeout(function(){
			
			$('#icdnumber'+cns).hide();
			
		},5);
											
	});
	
	  cns++;
	  dcntr++;
  }
  

  function selectdignosis()
  {
 
     dcntr = cnt;
	  var radiostr = "";
	 for(var i=0;i<jarr.length;i++)
	 {
		 
		 radiostr += '<td><input type="radio" name="dtype['+cnt+']" id="dtype'+cnt+'" value="'+jarr[i].id+'"></td>';
	 }
	 
	  var dignometa = "";
	 for(var i in jsdiagnosismeta)
	 {
		 dignometa +='<option value="'+i+'">'+jsdiagnosismeta[i]+'</option>';
	 }
	 
	  var dignometa1 = '<select name="meta_title['+cnt+'][]" id="meta_title_'+cnt+'_0">'+dignometa+'</select>';
	  var dignometa2 = '<select name="meta_title['+cnt+'][]" id="meta_title_'+cnt+'_1">'+dignometa+'</select>';
	  var dignometa3 = '<select name="meta_title['+cnt+'][]" id="meta_title_'+cnt+'_2">'+dignometa+'</select>';
	  
	 var trInnerHtml = $('<tr id="stab'+cnt+'"><td valign="top" id="listadmissiondiagnosistd_i'+cnt+'"><input name="icdnumber['+cnt+']" id="icdnumber'+cnt+'" value="" class="livesearchicdinp diagicd" type="text" /><div id="icddiagnodropdown'+cnt+'" class="icdlivesearchdropdown" style="position: absolute; display:none;"></div></td><td valign="top" id="listadmissiondiagnosistd_d'+cnt+'"><input name="diagnosis['+cnt+']" id="diagnosis'+cnt+'" value="" size="33" class="livesearchinp diagdesc" type="text" /><input name="hidd_icdnumber['+cnt+']" value="" id="hidd_icdnumber'+cnt+'" type="hidden"><input name="hidd_diagnosis['+cnt+']" value="" id="hidd_diagnosis'+cnt+'" type="hidden"><input name="hidd_ids['+cnt+']" value="" id="hidd_ids'+cnt+'" type="hidden"><input name="hidd_tab['+cnt+']" value="" id="hidd_tab'+cnt+'" type="hidden"><div id="diagnodropdown'+cnt+'" class="samtablistDiognoDrp livesearchdropdown" style="position: absolute; display:none;"></div>'+radiostr+'</td><td valign="middle" align="center"><a class="AddButton" id="addm_'+cnt+'"><img src="images/action_add.png" border="0" id="AddImg'+cnt+'"></a></td><td  valign="middle" align="center" id="listadmissiondiagnosisdeltd_d'+cnt+'"><a href="javascript:void(0)" onclick="removeElementsfromTab(\'#stab'+cnt+'\',\'#meta_'+cnt+'\',\'\')"><img src="images/action_delete.png" border="0" /></a></td></tr><tr id="meta_'+cnt+'" style="display:none" class="hideMeta"><td>&nbsp;</td><td colspan="5"><table ><tr><td ><label class="forlabel">Metastasierung</label>'+dignometa1+'</td></tr><tr><td ><label class="forlabel">Metastasierung</label>'+dignometa2+'</td></tr><tr><td><label class="forlabel">Metastasierung</label>'+dignometa3+'</td></tr></table></td></tr>');
	 
	 $('#samtab').append(trInnerHtml);
	 
	$('#stab'+cnt).find(".AddButton").click(function(){
		var aid = $(this).attr('id').substr(('addm_').length);
		
		
		
		
		if($('#meta_'+aid).hasClass("hideMeta"))
		{
			
				$('#meta_'+aid).removeClass('hideMeta');
				$('#meta_'+aid).show();
				 $("#AddImg"+aid).attr("src","images/action_minus.png");
			
		}
		else
		{
			if($("#meta_title_"+aid+"_0").val()==0 && $("#meta_title_"+aid+"_0").val()==0 && $("#meta_title_"+aid+"_0").val()==0)
			{
				$('#meta_'+aid).addClass('hideMeta');
				$('#meta_'+aid).hide();
				 $("#AddImg"+aid).attr("src","images/action_add.png");
				
				
			}
		}
	});

	
	$('#diagnosis'+cnt).bind('keydown',function(e){livesearchkeup(e,$(this))});
	diagnosisblur['diagnosis'+cnt] = true;
	
	$('#diagnosis'+cnt).bind('blur',function(){
	
		var t = setTimeout(function(){
			
			$('#diagnosis'+cnt).hide();
			
		},5);
											
	});
	
	$("#diagnodropdown"+cnt).bind('mousedown',function(){
	
		
		var t = setTimeout(function(){$("#diagnodropdown"+cnt).show();$('#diagnosis'+cnt).focus();},10);
		
	});
	
	$('#icdnumber'+cnt).bind('keydown',function(e){icdlivesearchkeup(e,$(this))});
	icddiagnosisblur['icdnumber'+cnt] = true;
	
	/* var row = document.createElement('tr');
	 row.id = "stab"+cnt;
     var cell1 = document.createElement('td');
	 var cell2 = document.createElement('td');
	 var cell3 = document.createElement('td');
	 var cell4 = document.createElement('td');
	 var cell5 = document.createElement('td');
	 
	 var ip1 = document.createElement('input');
	 ip1.name = "diagnosis["+cnt+"]";
	 ip1.id = "diagnosis"+cnt;
	 ip1.type = "text";
	// ip1.tempid = cnt
	// ip1.size = "33";
	 ip1.className = "livesearchinp diagdesc";
	 //ip1.onkeyup=function(){diagnosisDropDown(this.tempid);};
	 ip1.value = "";
	 
	 var ip3 = document.createElement('input');
	 ip3.name = "icdnumber["+cnt+"]";
	 ip3.id = "icdnumber"+cnt;
	 ip3.type = "text";
	 ip3.className = "livesearchicdinp diagicd";
	 //ip3.tempid = cnt
	// ip3.onkeyup=function(){diagnosisDropDown(this.tempid);};
	 ip3.value = "";
	 
	 
	 var ip2 = document.createElement('input');
	 ip2.name = "hidd_diagnosis["+cnt+"]";
	 ip2.id = "hidd_diagnosis"+cnt;
	 ip2.type = "hidden";
	 ip2.value = "";
	 
	 var ip4 = document.createElement('input');
	 ip4.name = "hidd_icdnumber["+cnt+"]";
	 ip4.id = "hidd_icdnumber"+cnt;
	 ip4.type = "hidden";
	 ip4.value = "";
	 
	 var ips = document.createElement('input');
	 ips.name = "hidd_tab["+cnt+"]";
	 ips.id = "hidd_tab"+cnt;
	 ips.type = "hidden";
	 ips.value = "";
	 
	 var divs = document.createElement('div');
	 divs.id = "icddiagnodropdown"+cnt;
	 divs.className = "livesearchdropdown";
	 divs.style.position = "absolute";
	 
	 
	 var divs2 = document.createElement('div');
	 divs2.id = "diagnodropdown"+cnt;
	 divs2.className = "livesearchdropdown";
	 divs2.style.position = "absolute";
	 
	 cell1.appendChild(ip1);
	 cell1.appendChild(ips);
	 cell1.appendChild(divs2);
	 cell2.appendChild(ip2);
	 cell2.appendChild(ip3);
	 cell2.appendChild(ip4);
	 cell2.appendChild(divs);
	 row.appendChild(cell2);
	 
	
	 
	 cell3.innerHTML = '<a href="javascript:void(0)" onclick="removeElem(\'#stab'+cnt+'\')"><img src="images/action_delete.png" border="0" /></a>';
	 cell5.innerHTML = '<a class="AddButton" id="addm_'+cnt+'"><img src="images/action_add.png" border="0" id="AddImg'+cnt+'" /></a>'; 
	 
	
	 
	 
	 row.appendChild(cell1);
	 
	 for(i=0;i<tarr.length;i++)
	 {
		 var cell = document.createElement('td');
		 
		 var chk1 = document.createElement('input');
		 chk1.type = "radio";
		 chk1.name = "dtype" + "["+cnt+"]";
		 chk1.id = "dtype"+cnt;
		 chk1.value=tarr[i].id;
		 cell.appendChild(chk1);
		 row.appendChild(cell);
	 }
	 row.appendChild(cell5);
	 row.appendChild(cell3);
	// row.appendChild(cell4);
	
	 var row2 = document.createElement('tr');
	 row2.id = "meta_"+cnt;
	 row2.style.display="none";
	 
	 var metatd1 = document.createElement('td');
	 row2.appendChild(metatd1);
	 
	 var metatd2 = document.createElement('td');
	 metatd2.colspan = 5;
	 
	 var metatab = document.createElement('table');
	 var subrow1 = document.createElement('tr');
	 var subrow2 = document.createElement('tr');
	 var subrow3 = document.createElement('tr');
	 
	 var subtd1 = document.createElement('td');
	 var subtd2 = document.createElement('td');
	 var subtd3 = document.createElement('td');
	 
	 var lbl1 = document.createElement('label');
	 lbl1.className = "forlabel";
	 lbl1.innerHTML = "Metastasierung";
	 
	 var lbl2 = document.createElement('label');
	 lbl2.className = "forlabel";
	 lbl2.innerHTML = "Metastasierung";
	 
	 var lbl3 = document.createElement('label');
	 lbl3.className = "forlabel";
	 lbl3.innerHTML = "Metastasierung";
	 
	 
	 var drop1 = document.createElement('select');
	 drop1.name = "meta_title["+cnt+"][]";
	 
	 var drop2 = document.createElement('select');
	 drop2.name ="meta_title["+cnt+"][]";
	 
	 var drop3 = document.createElement('select');
	 drop3.name = "meta_title["+cnt+"][]";
	 

	 for(i in jsdiagnosismeta)
	 {
		 var option1 = document.createElement('option');
		 option1.value = i;
		 option1.appendChild(document.createTextNode(jsdiagnosismeta[i]));
		 
		 var option2 = document.createElement('option');
		 option2.value = i;
		 option2.appendChild(document.createTextNode(jsdiagnosismeta[i]));
		 
		 var option3 = document.createElement('option');
		 option3.value = i;
		 option3.appendChild(document.createTextNode(jsdiagnosismeta[i]));
		 
		 drop1.appendChild(option1);
		 drop2.appendChild(option2);
		 drop3.appendChild(option3);
	 }
	 
     subtd1.appendChild(lbl1); 
	 subtd2.appendChild(lbl2); 
	 subtd3.appendChild(lbl3);
	 
	 subtd1.appendChild(drop1); 
	 subtd2.appendChild(drop2); 
	 subtd3.appendChild(drop3);
	 
	 
	 subrow1.appendChild(subtd1);
	 subrow2.appendChild(subtd2);
	 subrow3.appendChild(subtd3);
	 
	 metatab.appendChild(subrow1);
	 metatab.appendChild(subrow2);
	 metatab.appendChild(subrow3);
	 
	 row2.appendChild(metatab);
	 
	
	 $('#samtab').append(row);
     $('#samtab').append(row2);
	 
	 $('#stab'+cnt).find(".AddButton").click(function()
	{
		var aid = $(this).attr('id').substr(('addm_').length);
		
		if($('#meta_'+aid).is(":visible"))
		{
			 $("#meta_title["+aid+"]").val('');	
			 $("#AddImg"+aid).attr("src","images/action_add.png");	 
		}
		else
		{
			$("#AddImg"+aid).attr("src","images/action_minus.png");	
		}
		
		$('#meta_'+aid).toggle();
		
	});

	
	$('#diagnosis'+cnt).bind('keydown',function(e){livesearchkeup(e,$(this))});
	diagnosisblur['diagnosis'+cnt] = true;
	
	
	$('#icdnumber'+cnt).bind('keydown',function(e){icdlivesearchkeup(e,$(this))});
	icddiagnosisblur['icdnumber'+cnt] = true;*/
	
	cnt++;
    dcntr++;
  
  }
  
 var diagsearch = false; 
 var livesearchkeup =   function( e,ele ){
	
		
		id = $(ele).attr('id');
		
		
		
		diagnosisblur[id] = false;
		var t = 'diagnosis';
		id = id.substring(t.length);
		
		$('#hidd_tab'+id).val("text");
		$('#hidd_diagnosis'+id).val("");
		
		if( e.keyCode == 40 ) {
			
			  //e.stopImmediatePropagation();
			 //e.preventDefault();
			 //e.stopPropagation();
			diagsearch = false;
			if( $("#diagnodropdown"+id).find( '.focused' ).length < 1 ) {
				//alert($("#diagnodropdown"+id+'tr:first'));
				//$("#diagnodropdown"+id+' tr:first').removeClass('bluerow');
				
				$("#diagnodropdown"+id+' tr:first').addClass('focused');
				//alert($("#diagnodropdown"+dv+'tr:first'));
				//alert('$("#diagnodropdown"+dv+'tr:first')');
				//$('.list_name p:first' ).addClass( 'highlight' );
			} else {
				if( $("#diagnodropdown"+id).find( '.focused' ).next().length < 1 ) {
					$("#diagnodropdown"+id).find( '.focused' ).removeClass( 'focused' );
					$("#diagnodropdown"+id+' tr:first').addClass('focused');
				} else {
					var itsNext = $("#diagnodropdown"+id).find( '.focused' ).removeClass( 'focused' ).next().addClass( 'focused' );
					if( itsNext.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 40 });
					} else {
						//$( '.focused' )[0].scrollIntoView();
						
						var activeItem = $("#diagnodropdown"+id).find( '.focused' )[0];
						offsetTop = activeItem.offsetTop;
						var container = $('#diagnodropdowninner'+id);
							
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
			if( $("#diagnodropdown"+id).find( '.focused' ).length < 1 ) {
				$("#diagnodropdown"+id+' tr:last').addClass('focused');
			} else {
				if( $("#diagnodropdown"+id).find( '.focused' ).prev().length < 1 ) {
					$("#diagnodropdown"+id).find( '.focused' ).removeClass( 'focused' );
					$("#diagnodropdown"+id+' tr:last').addClass('focused');
				} else {
					var itsPrev = $("#diagnodropdown"+id).find( '.focused' ).removeClass( 'focused' ).prev().addClass( 'focused' );
					if( itsPrev.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 38 });
					} else {
						//$( '.focused' )[0].scrollIntoView();
						
						var activeItem = $("#diagnodropdown"+id).find( '.focused' )[0];
						offsetTop = activeItem.offsetTop;
						var container = $('#diagnodropdowninner'+id);
							
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
				$("#diagnodropdown"+id).find( '.focused' ).trigger( 'onmousedown' );
				
				
				diagsearch = true;
			}
			e.preventDefault();
			//alert(e.target);
			return false;
		}
		
		if(e.keyCode == 9)
		{
			$("#diagnodropdown"+id).hide();
			return;	
		}
		
		if( e.keyCode == 27 ) {
			
			$("#diagnodropdown"+id).hide();
			return;	
		}
		if( e.keyCode == 8 ) {
			
			return;	
		}
		/*if( $.trim( this.value ) == '' ) {
			$( '.list_name' ).hide();
			return;
		}*/
		 //e.stopImmediatePropagation();
      	 //e.preventDefault();
		setTimeout(function(){diagnosisDropDown(id)},100);
		
	}
	function removeElem(ids)
	{
 		 $(ids).remove();
	}