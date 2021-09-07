var h_loading = function(){

var dlist = '<div class="DropDwnTable" style="margin-top:5px;margin-left:85px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble"><tr class="bluerow"><td><img src="images/loading.gif"></td></tr></table></div>';
 document.getElementById('healthinsudropdown').style.display = "";
 document.getElementById('healthinsudropdown').innerHTML=dlist;

}

function healthinsDropDown(ltrval)
{


	ajaxCallserver({url:appbase+'patient/healthinsdropdown',
				   callLoading:h_loading,
				   method:'POST',
				   data:{ltr:$("#company_name").val()}
				   });
}


var healthinsurance;
var healthdropdiv = function(params){

var dlist = '<div class="DropDwnTable" id="healthinsudropdowninner" style="height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble">';
	healthinsurance = params.healthinsurance;

	//alert(dv);
	if(healthinsurance.length<1)
	{
		 $('#healthinsudropdown').hide('slow');

	}

	if(healthinsurance.length>0){dlist+='<tr class="bluerow"><td class="first"><strong>'+translate('patient_health_insurance')+'</strong></td><td class="first"><strong>'+translate('city')+'</strong></td><td class="first"><strong>'+translate('kvnumber')+'</strong></td><td class="first"><strong>'+translate('iknumber')+'</strong></td><td class="first"><strong>'+translate('select')+'</strong></td></tr>';}
	else
	{
	 dlist+='<tr><td colspan="2" class="BlueBox">'+translate('noresultfound')+'</td></tr>';

	}

	for(i=0;i<params.healthinsurance.length;i++)
	{
		var client_class = '';
		if(params.healthinsurance[i].onlyclients == '1')
		{
			client_class = 'bolderrowhealthinsu';
		}
		else
		{
			client_class = '';
		}

		dlist+='<tr class="bluerow '+client_class+'" onmousedown="selecthealthinsu('+i+')"><td class="first">'+params.healthinsurance[i].name +'</td><td class="first">'+params.healthinsurance[i].city +'</td><td class="first">'+params.healthinsurance[i].kvnumber +'</td><td class="first">'+params.healthinsurance[i].iknumber +'</td><td>'+translate('select')+'</td></tr>';
	}
	 dlist+= "</table></div>";


	if(healthblur==false)
	{
		document.getElementById('healthinsudropdown').style.display = "";

	}

	 document.getElementById('healthinsudropdown').innerHTML=dlist;
	// document.getElementById('msg').innerHTML = "If doctor is not found in list. please add new doctor from here";

}

function selecthealthinsu(i)
{

	document.getElementById('company_name').value =healthinsurance[i].name;
	document.getElementById('hdn_companyid').value =healthinsurance[i].id;
	document.getElementById('institutskennzeichen').value =healthinsurance[i].iknumber;
	document.getElementById('healthinsudropdown').style.display = "none";
	$('#kvk_no').val(healthinsurance[i].kvnumber);
	document.getElementById('street').value =healthinsurance[i].street1;
	document.getElementById('zip').value =healthinsurance[i].zip;
	document.getElementById('city').value =healthinsurance[i].city;
	//document.getElementById('hidd_docid').value =doctors[i].id;

	var t = setTimeout(function(){$("#company_name").trigger('blur');},15);
}

var livesearchhealthup = function( e,ele ){
		//alert($(ele).attr('id'));
		id = $(ele).attr('id');
		healthblur = false;
		//id = id.substring(t.length);



		if( e.keyCode == 40 ) {
			diagsearch = false;
			if( $("#healthinsudropdown").find('.focused' ).length < 1 ) {
				//alert($("#diagnodropdown"+id+'tr:first'));
				//$("#diagnodropdown"+id+' tr:first').removeClass('bluerow');

				$("#healthinsudropdown tr:first").addClass('focused');
				//alert($("#diagnodropdown"+dv+'tr:first'));
				//alert('$("#diagnodropdown"+dv+'tr:first')');
				//$('.list_name p:first' ).addClass( 'highlight' );

			} else {
				if( $("#healthinsudropdown").find( '.focused' ).next().length < 1 ) {
					$("#healthinsudropdown").find( '.focused' ).removeClass( 'focused' );
					$("#healthinsudropdown tr:first").addClass('focused');
				} else {
					var itsNext = $("#healthinsudropdown").find( '.focused' ).removeClass( 'focused' ).next().addClass( 'focused' );
					if( itsNext.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 40 });
					} else {

						var activeItem = $("#healthinsudropdown").find( '.focused' )[0];
						offsetTop = activeItem.offsetTop;
						var container = $('#healthinsudropdowninner');

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
			//e.stopPropagation();
			return;
		}
		if( e.keyCode == 38 ) {

			diagsearch = false;
			if( $("#healthinsudropdown").find( '.focused' ).length < 1 ) {
				$("#healthinsudropdown tr:last").addClass('focused');
			} else {
				if( $("#healthinsudropdown").find( '.focused' ).prev().length < 1 ) {
					$("#healthinsudropdown").find( '.focused' ).removeClass( 'focused' );
					$("#healthinsudropdown tr:last").addClass('focused');
				} else {
					var itsPrev = $("#healthinsudropdown").find( '.focused' ).removeClass( 'focused' ).prev().addClass( 'focused' );
					if( itsPrev.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 38 });
					} else {
						var activeItem = $("#healthinsudropdown").find( '.focused' )[0];
						offsetTop = activeItem.offsetTop;
						var container = $('#healthinsudropdowninner');

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

			e.preventDefault();
			return;
		}

		if( e.keyCode == 13 ) {
			if(!diagsearch)
			{
				$("#healthinsudropdown").find( '.focused' ).trigger( 'mousedown' );

				diagsearch = true;
			}
			e.preventDefault();
			//alert(e.target);
			return false;
		}

		if(e.keyCode == 9)
		{
			$("#healthinsudropdown"+id).hide();
			return;
		}

		setTimeout(function(){healthinsDropDown()},400);

	}


