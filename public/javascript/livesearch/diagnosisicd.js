
function diagnosisIcdDropDown(did)
{
	var tabname = "dig"
	var ltrval = document.getElementById('icdnumber'+did).value;

	if(ltrval)
	{

		ajaxCallserver({
			url:'diagnosis/fetchdropdown',
			callLoading:icd_loading,
			loadingOptions:{
				id:did
			},
			method:'POST',
			data:{
				ltr:ltrval,
				did:did,
				tb:tabname,
				srch:'icdnumber'
			}
		});
	}
	else
	{

		$('#icddiagnodropdown'+did).hide('slow');
//		document.getElementById('icddiagnodropdown'+dv).innerHTML = '';
		document.getElementById('icddiagnodropdown'+did).innerHTML = '';
	}
}


var icd_loading = function(options){
	var divid = options.id;
	var dlist = '<div class="DropDwnTable"  style="margin-top:5px;margin-left:35px; height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble"><tr class="bluerow"><td><img src="images/loading.gif"></td></tr></table></div>';
	document.getElementById('icddiagnodropdown'+divid).style.display = "";
	document.getElementById('icddiagnodropdown'+divid).innerHTML=dlist;

}

var dg;
var icddiagnodropdiv = function(params){

	var dv = params.didiv;

	var dlist = '<div class="DropDwnTable" id="icddiagnodropdowninner'+dv+'" style="height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble">';
	dg = params.diagnosisarray;




	//alert(dv);
	if(dg.length<1)
	{
		$('#icddiagnodropdown'+dv).hide('slow');

	}



	if(params.tabname=="dig")
	{
		dlist+='<tr class="bluerow"><td class="first"><strong>'+translate('icdcode')+'</strong></td><td class="first"><strong>'+translate('description')+'</strong></td></tr>';
		for(i=0;i<dg.length;i++)
		{
			dlist+='<tr class="bluerow" onmousedown="selectIcdDiagnosis('+i+','+dv+')"><td class="first">'+dg[i].icd_primary +'</td><td class="first">'+dg[i].description +'</td></tr>';
		}
	}
	dlist+= "</table></div>";



	document.getElementById('hidd_diagnosis'+dv).value="";
	if(icddiagnosisblur['icdnumber'+dv]==false)
	{
		document.getElementById('icddiagnodropdown'+dv).style.display = "";
	}
	document.getElementById('icddiagnodropdown'+dv).innerHTML=dlist;

}

function selectIcdDiagnosis(i,did)
{

	if($('#course_type'+did).val() == "D" || $('#course_type'+did).val() == "d" || $('#course_type'+did).val() == "H" || $('#course_type'+did).val() == "h"){

		text = dg[i].id+'|'+dg[i].icd_primary+'|'+dg[i].description;
		//	$('#course_title'+did).val(text);
		document.getElementById('course_title'+did).value =text;
	}
	document.getElementById('icdnumber'+did).value =dg[i].icd_primary;
	document.getElementById('diagnosis'+did).value =dg[i].description;
	document.getElementById('hidd_icdnumber'+did).value =dg[i].id;
	document.getElementById('hidd_diagnosis'+did).value =dg[i].id;
	document.getElementById('icddiagnodropdown'+did).style.display = "none";
	$('#hidd_tab'+did).val("dig");
	$('#icddiagnodropdown'+did).fadeOut(200);

	var t = setTimeout(function(){
		$('#icdnumber'+did).trigger('blur');
	},15);
}


var icddiagsearch = false;
var icdlivesearchkeup =   function( e,ele ){


	var id = $(ele).attr('id');
	icddiagnosisblur[id] = false;
	var t = 'icdnumber';
	id = id.substring(t.length);

	$('#hidd_tab'+id).val("text");
	$('#hidd_diagnosis'+id).val("");
	$('#hidd_icdnumber'+id).val("");



	if( e.keyCode == 40 ) {

		icddiagsearch = false;
		if( $("#icddiagnodropdown"+id).find( '.focused' ).length < 1 ) {
			//alert($("#icddiagnodropdown"+id+'tr:first'));
			//$("#icddiagnodropdown"+id+' tr:first').removeClass('bluerow');

			$("#icddiagnodropdown"+id+' tr:first').addClass('focused');

		//alert($("#icddiagnodropdown"+dv+'tr:first'));
		//alert('$("#icddiagnodropdown"+dv+'tr:first')');
		//$('.list_name p:first' ).addClass( 'highlight' );
		} else {
			if( $("#icddiagnodropdown"+id).find( '.focused' ).next().length < 1 ) {
				$("#icddiagnodropdown"+id).find( '.focused' ).removeClass( 'focused' );
				$("#icddiagnodropdown"+id+' tr:first').addClass('focused');
			} else {
				var itsNext = $("#icddiagnodropdown"+id).find( '.focused' ).removeClass( 'focused' ).next().addClass( 'focused' );
				if( itsNext.is( ':hidden' ) ) {
					$( this ).trigger( {
						type : 'keyup',
						keyCode : 40
					});
				} else {
					var activeItem = $("#icddiagnodropdown"+id).find( '.focused' )[0];

					offsetTop = activeItem.offsetTop;
					var container = $('#icddiagnodropdowninner'+id);

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
		icddiagsearch = false;
		if( $("#icddiagnodropdown"+id).find( '.focused' ).length < 1 ) {
			$("#icddiagnodropdown"+id+' tr:last').addClass('focused');
		} else {
			if( $("#icddiagnodropdown"+id).find( '.focused' ).prev().length < 1 ) {
				$("#icddiagnodropdown"+id).find( '.focused' ).removeClass( 'focused' );
				$("#icddiagnodropdown"+id+' tr:last').addClass('focused');
			} else {
				var itsPrev = $("#icddiagnodropdown"+id).find( '.focused' ).removeClass( 'focused' ).prev().addClass( 'focused' );
				if( itsPrev.is( ':hidden' ) ) {
					$( this ).trigger( {
						type : 'keyup',
						keyCode : 38
					});
				} else {
					var activeItem = $("#icddiagnodropdown"+id).find('.focused' )[0];
					offsetTop = activeItem.offsetTop;
					var container = $('#icddiagnodropdowninner'+id);

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
		if(!icddiagsearch)
		{
			$("#icddiagnodropdown"+id).find( '.focused' ).trigger( 'onmousedown' );


			icddiagsearch = true;
		}
		e.preventDefault();
		//alert(e.target);
		return false;
	}

	if(e.keyCode == 9)
	{
		$("#icddiagnodropdown"+id).hide();
		return;
	}
	/*if( $.trim( this.value ) == '' ) {
			$( '.list_name' ).hide();
			return;
		}*/

	setTimeout(function(){
		diagnosisIcdDropDown(id)
	},10);

}
function removeElem(ids)
{
	$(ids).remove();
}