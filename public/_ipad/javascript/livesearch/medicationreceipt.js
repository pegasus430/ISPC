function medicationReceiptDropDown(mid)
{

	var ltrval = document.getElementById('med'+mid).value;
	
	if(ltrval)
	{
		ajaxCallserver({
			url:'medication/fetchdropdownreceipt',
			callLoading:mr_loading,
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


var mr_loading = function(options){
var divid = options.id;
var dlistreceipt = '<div class="DropDwnTable" style="margin-top:5px;margin-left:35px; height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble"><tr class="bluerow"><td><img src="images/loading.gif"></td></tr></table></div>';
document.getElementById('medidropdown'+divid).style.display = "";
document.getElementById('medidropdown'+divid).innerHTML=dlistreceipt;

}

var dg;
var medicdropdiv = function(params){

var dv = params.didiv;

var dlistreceipt = '<div class="DropDwnTable" id="medidropdowninner'+dv+'" style="height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble">';
dg = params.medicationarray;


if(dg.length>0){dlistreceipt+='<tr class="bluerow"><td class="first"><strong>'+translate('medication')+' </strong></td><td class="first"><strong>'+translate('select')+'</strong></td></tr>';}



if(dg.length<1)
{
	$('#medidropdown'+dv).hide('slow');
//	$('#medication'+dv).val('');
//	document.getElementById('hidd_medication'+dv).value ="";
}

for(i=0;i<dg.length;i++)
{
	dlistreceipt+='<tr class="bluerow" onmousedown="selectReceiptMedicationrow('+i+','+dv+')"><td class="first">'+dg[i].name +' Packungsgröße('+dg[i].pkgsz+')</td><td>'+translate('select')+'</td></tr>';
}
 dlistreceipt+= "</table></div>";

 if(medblur['medication'+dv] == false)
 {
	document.getElementById('medidropdown'+dv).style.display = "";
 }
 document.getElementById('medidropdown'+dv).innerHTML=dlistreceipt;
// document.getElementById('msg').innerHTML = "If Medication is not found in list. please add new diagnois";
}


function selectReceiptMedicationrow(i,divs)
{
	document.getElementById('med'+divs).value =dg[i].name+' Packungsgröße('+dg[i].pkgsz+')';
	document.getElementById('medidropdown'+divs).style.display = "none";
//	document.getElementById('hidd_medication'+divs).value =dg[i].id;
// 	document.getElementById('dosage'+divs).value =dg[i].dosage;
// 	document.getElementById('comments'+divs).value =dg[i].comments;
    
	var t = setTimeout(function(){$('#med'+divs).trigger('blur');},55);
}

var livemedicationreceiptsearchkeup =   function( e,ele ){
		//alert($(ele).attr('id'));
		id = $(ele).attr('id');
		newid =  id.replace('med','');
		
        
		medblur[id] = false;
		var t = 'med';
//		$('#hidd_medication'+newid).val("");
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
						$( this ).trigger( {type : 'keyup', keyCode : 40});
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
						$( this ).trigger( {type : 'keyup', keyCode : 38});
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
		
		setTimeout(function(){medicationReceiptDropDown(id)},100);
		
	}