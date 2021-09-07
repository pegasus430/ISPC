

function diagnosisDropDown_ed(did)
{

 var tabname = "dig"
 var ltrval = document.getElementById('diagnosis_ed'+did).value;
 


 if(ltrval)
 {
   if(ltrval.length>1)
   {
  // 	 ltrval = encodeURI(ltrval);
	ajaxCallserver({url:'diagnosis/fetchdropdown',
				   callLoading:e_loading,
				   loadingOptions:{id:did},
				   method:'POST',
				   data:{ltr:ltrval,did:did,tb:tabname,cfun:3}
				   });
   }
   
 }
 else
 {
  // alert("provide diagnosis");
   $('#diagnodropdown_ed'+did).hide('slow');
  
	//document.getElementById('pdid_ed'+did).value="";
   
 }
} 

function removeDiaElem(ids,rid)
{
  ajaxCallserver({url:'diagnosis/removerecord?rid='+rid});
  $(ids).remove();
}

var e_loading = function(options){
var divid = options.id;
var dlist = '<div class="DropDwnTable" style="margin-top:5px;margin-left:35px; height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble"><tr class="bluerow"><td><img src="images/loading.gif"></td></tr></table></div>';
document.getElementById('diagnodropdown_ed'+divid).style.display = "";
document.getElementById('diagnodropdown_ed'+divid).innerHTML=dlist;

//jQuery.tableNavigation();	 
}

var dg;
var diagnodropdivs = function(params){
	

var dv = params.didiv;

var dlist = '<div class="DropDwnTable" id="diagnodropdowninner_ed'+dv+'" style="height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble">';
	dg = params.diagnosisarray;
	
	
   

	//alert(dv);
	if(dg.length<1)
	{
	 	dlist+='<tr><td colspan="2" class="bluerow">'+translate('noresultsfoundpleaseaddnewdiagnosis')+'</td></tr>';
		 $('#diagnodropdown_ed'+dv).hide('slow');
	}
	
	
	 if(dg.length<1)
	 {
	    // dlist+='<tr><td colspan="2" class="BlueBox">No Results Found Please Try Another Word</td></tr>';
		 
	 }
	 else
	 {
		dlist+='<tr class="bluerow"><td class="first"><strong>'+translate('icdcode')+'</strong></td><td class="first"><strong>'+translate('description')+'</strong></td></tr>';	  
		for(i=0;i<dg.length;i++)
		{

			dlist+='<tr class="bluerow" onmousedown="selectDiagnosis_ed('+i+','+dv+')"><td class="first">'+dg[i].icd_primary +'</td><td class="first">'+dg[i].description +'</td></tr>';
		}
	 }
	 dlist+= "</table></div>";
	


	
	document.getElementById('hidd_diagnosis_ed'+dv).value="";
	if(diagnosisblur_ed['diagnosis_ed'+dv]==false){
	document.getElementById('diagnodropdown_ed'+dv).style.display = "";
	}
	document.getElementById('diagnodropdown_ed'+dv).innerHTML=dlist;
	//jQuery.tableNavigation();
	/*if($('#diagnodropdown'+dv).height()>175){
		$('#diagnodropdown'+dv).css{height:'175px'});
		
	}*/
	
	//$(".Dropdwntble:first").get(0).focus();
	
	 //document.getElementById('msg').innerHTML = "If diagnosis is not found in list. please add new diagnois";
}

function selectDiagnosis_ed(i,did)
{
	 document.getElementById('icdnumber_ed'+did).value =dg[i].icd_primary ;
	 document.getElementById('diagnosis_ed'+did).value = dg[i].description;	
	 document.getElementById('hidd_diagnosis_ed'+did).value =dg[i].id;
	 document.getElementById('hidd_icdnumber_ed'+did).value =dg[i].id;
	 $('#diagnodropdown_ed'+did).hide();
	 $('#hidd_tab_ed'+id).val("dig");
	 
	 var t = setTimeout($('#diagnosis_ed'+did).trigger('blur'),15);
	 
	
}


  function addnewdiagnosis(cn)
  {
     
	 dcntr = cn;
	 var row = document.createElement('tr');
	 row.id = "stab_ed"+cn;
     var cell1 = document.createElement('td');
	 var cell2 = document.createElement('td');
	 var cell3 = document.createElement('td');
	 var cell4 = document.createElement('td');
	 
	 var ip1 = document.createElement('input');
	 ip1.name = "diagnosis_ed["+cn+"]";
	 ip1.id = "diagnosis_ed"+cn;
	 ip1.type = "text";
	// ip1.tempid = cn;
	 ip1.className = "livesearchinp diagdesc";
	 //ip1.onkeyup=function(){diagnosisDropDown(this.tempid);};
	 ip1.value = "";
	 
	 var ip3 = document.createElement('input');
	 ip3.name = "icdnumber_ed["+cn+"]";
	 ip3.id = "icdnumber_ed"+cn;
	 ip3.type = "text";
	 ip3.tempid = cn;
	 ip3.className = "diagicd livesearchicdinp_ed";
	 //ip3.onkeyup=function(){diagnosisDropDown(this.tempid);};
	 ip3.value = "";
	 
	 
	 var ip2 = document.createElement('input');
	 ip2.name = "hidd_diagnosis_ed["+cn+"]";
	 ip2.id = "hidd_diagnosis_ed"+cn;
	 ip2.type = "hidden";
	 ip2.value = "";
	 
	 
	 
	 var ip4 = document.createElement('input');
	 ip4.name = "hidd_icdnumber_ed["+cn+"]";
	 ip4.id = "hidd_icdnumber_ed"+cn;
	 ip4.type = "hidden";
	 ip4.value = "";
	 
	 var ip5 = document.createElement('input');
	 ip5.name = "hidd_ids_ed["+cn+"]";
	 ip5.id = "hidd_ids_ed"+cn;
	 ip5.type = "hidden";
	 ip5.value = "";
	 
	 var ip6 = document.createElement('input');
	 ip6.name = "hidd_tab_ed["+cn+"]";
	 ip6.id = "hidd_tab_ed"+cn;
	 ip6.type = "hidden";
	 ip6.value = "";
	 
	 var ip7 = document.createElement('input');
	 ip7.name = "diagnosis_type_id_ed["+cn+"]";
	 ip7.id = "diagnosis_type_id_ed"+cn;
	 ip7.type = "hidden";
	 ip7.value = "";
	 
	 
	 var divs = document.createElement('div');
	 divs.id = "icddiagnodropdown_ed"+cn;
	 divs.className = "livesearchdropdown";
	 divs.style.position = "absolute";
	 
	 
	 var divs2 = document.createElement('div');
	 divs2.id = "diagnodropdown_ed"+cn;
	 divs2.className = "livesearchdropdown";
	 divs2.style.position = "absolute";
	 
	 cell1.appendChild(ip1);
	 cell1.appendChild(divs2);
	
	 cell2.appendChild(ip2);
	 cell2.appendChild(ip3);
	 cell2.appendChild(ip4);
	 cell2.appendChild(ip5);
	 cell2.appendChild(ip6);
	 cell2.appendChild(ip7);
	 
	 cell2.appendChild(divs);
	 row.appendChild(cell2);
	 
	/* var chk3 = document.createElement('input');
	 chk3.type = "checkbox";
	 chk3.name = "icd["+cn+"]";
	 chk3.tid = cn
	 chk3.onclick = function(){diagnosisDropDown(this.tid);};
	 chk3.id = "icd"+cn;
	 chk3.value="1";*/
	 
	 //cell3.innerHTML = '<a href="javascript:void(0)" onclick="removeElem(\'#stab'+cn+'\')"><img src="images/action_delete.png" border="0" /></a>';
	
	 
	
	 
	 
	 row.appendChild(cell1);
	 row.appendChild(cell3);
	// row.appendChild(cell4);
	 
	
	 $('#samtab').append(row);
    
	
	
	$('#diagnosis_ed'+cn).bind('keydown',function(e){livesearchkeup_ed(e,$(this))});
	diagnosisblur['diagnosis_ed'+cn] = true;
	
	/*$('#icdnumber'+cn).bind('keydown',function(e){icdlivesearchkeup(e,$(this))});
	icddiagnosisblur['icdnumber'+cn] = true;*/
	
  cn++;
  
  }


var icdLiveSearch = function(e,ele){
	
	id = $(ele).attr('id');
	var t = 'icdnumber_ed';
	id = id.substring(t.length);
		
	//$('#hidd_tab_ed'+id).val("text");
		//$('#hidd_diagnosis_ed'+id).val("");
		$('#hidd_icdnumber_ed'+id).val("");
	
}

 var diagsearch_ed = false; 
 var livesearchkeup_ed =   function( e,ele ){
	
		
		id = $(ele).attr('id');
		diagnosisblur_ed[id] = false;
		var t = 'diagnosis_ed';
		id = id.substring(t.length);
		
		$('#hidd_tab_ed'+id).val("text");
		$('#hidd_diagnosis_ed'+id).val("");
		
		if( e.keyCode == 40 ) {
			
			  //e.stopImmediatePropagation();
			 //e.preventDefault();
			 //e.stopPropagation();
			diagsearch_ed = false;
			if( $( '.focused' ).length < 1 ) {
				//alert($("#diagnodropdown"+id+'tr:first'));
				//$("#diagnodropdown"+id+' tr:first').removeClass('bluerow');
				
				$("#diagnodropdown_ed"+id+' tr:first').addClass('focused');
				//alert($("#diagnodropdown"+dv+'tr:first'));
				//alert('$("#diagnodropdown"+dv+'tr:first')');
				//$('.list_name p:first' ).addClass( 'highlight' );
			} else {
				if( $( '.focused' ).next().length < 1 ) {
					$( '.focused' ).removeClass( 'focused' );
					$("#diagnodropdown_ed"+id+' tr:first').addClass('focused');
				} else {
					var itsNext = $( '.focused' ).removeClass( 'focused' ).next().addClass( 'focused' );
					if( itsNext.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 40 });
					} else {
						//$( '.focused' )[0].scrollIntoView();
						
						var activeItem = $( '.focused' )[0];
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
			diagsearch_ed = false;
			if( $( '.focused' ).length < 1 ) {
				$("#diagnodropdown_ed"+id+' tr:last').addClass('focused');
			} else {
				if( $( '.focused' ).prev().length < 1 ) {
					$( '.focused' ).removeClass( 'focused' );
					$("#diagnodropdown_ed"+id+' tr:last').addClass('focused');
				} else {
					var itsPrev = $( '.focused' ).removeClass( 'focused' ).prev().addClass( 'focused' );
					if( itsPrev.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 38 });
					} else {
						//$( '.focused' )[0].scrollIntoView();
						
						var activeItem = $( '.focused' )[0];
						offsetTop = activeItem.offsetTop;
						var container = $('#diagnodropdowninner_ed'+id);
							
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
			if(!diagsearch_ed)
			{
				$( '.focused' ).trigger( 'mousedown' );
				
				
				diagsearch_ed = true;
			}
			e.preventDefault();
			//alert(e.target);
			return false;
		}
		
		if(e.keyCode == 9)
		{
			$("#diagnodropdown_ed"+id).hide();
			return;	
		}
		
		if( e.keyCode == 27 ) {
			
			$("#diagnodropdown_ed"+id).hide();
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
		setTimeout(function(){diagnosisDropDown_ed(id)},100);
		
	}
	function removeElem(ids)
	{
 		 $(ids).remove();
	}