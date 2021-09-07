var h_loading = function(){

var dlist = '<div class="DropDwnTable" style="margin-top:5px;margin-left:85px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble"><tr class="bluerow"><td><img src="images/loading.gif"></td></tr></table></div>';
 document.getElementById('patdropdown').style.display = "";
 document.getElementById('patdropdown').innerHTML=dlist;
	 
}

function dropDownchange(ltrval)
{
 	//alert('patient/fetchpatientdropdown?ltr='+$("#last_name").val());
	// ajaxCallserver({url:'patient/fetchpatientdropdown?ltr='+ltrval,callLoading:h_loading});
 	ajaxCallserver({url:'patient/fetchpatientdropdown',
					callLoading:h_loading,
					method:'POST',
				   data:{ltr:$("#last_name").val()}
					});	
} 

var patient;
var patientdropdiv = function(params){

var dlist = '<div class="DropDwnTable" id="patdropdowninner" style="height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble">';
	patient = params.patient;
	if(patient.length>0){dlist+='<tr class="bluerow"><td class="first" valign="top"><strong>'+translate('lastname')+'</strong></td><td class="first" valign="top"><strong>'+translate('firstname')+'</strong></td><td class="first" valign="top"><strong>'+translate('city')+'</strong></td><td valign="top">&nbsp;</td></tr>';}
	else
	{
	 dlist+='<tr><td colspan="2" class="BlueBox" valign="top">'+translate('noresultfound')+'</td></tr>';
	}
	
	for(i=0;i<params.patient.length;i++)
	{
		dlist+='<tr class="bluerow" onclick="selectPatient('+i+')"><td class="first" valign="top">'+params.patient[i].last_name +'</td><td class="first" valign="top">'+params.patient[i].first_name +'</td><td class="first" valign="top">'+params.patient[i].city +'</td><td valign="top">'+translate('select')+'</td></tr>';
	}
	 dlist+= "</table></div>";
	 
	
	if(patblur==false)
	{
		document.getElementById('patdropdown').style.display = "";
	}
	
	 document.getElementById('patdropdown').innerHTML=dlist;
	// document.getElementById('msg').innerHTML = "If doctor is not found in list. please add new doctor from here";
	 
}

function selectPatient(i)
{
	
	document.getElementById('last_name').value =patient[i].last_name;
	document.getElementById('epid').value =patient[i].epid;
	document.getElementById('patdropdown').style.display = "none";
	//document.getElementById('hidd_docid').value =doctors[i].id;
}

var livesearchpat = function( e,ele ){
		//alert($(ele).attr('id'));
		id = $(ele).attr('id');
		patblur = false;
		//id = id.substring(t.length);
		
		if( e.keyCode == 40 ) {
			diagsearch = false;
			if( $( '.focused' ).length < 1 ) {
				//alert($("#diagnodropdown"+id+'tr:first'));
				//$("#diagnodropdown"+id+' tr:first').removeClass('bluerow');
				
				$("#patdropdown tr:first").addClass('focused');
				//alert($("#diagnodropdown"+dv+'tr:first'));
				//alert('$("#diagnodropdown"+dv+'tr:first')');
				//$('.list_name p:first' ).addClass( 'highlight' );
				
			} else {
				if( $( '.focused' ).next().length < 1 ) {
					$( '.focused' ).removeClass( 'focused' );
					$("#patdropdown tr:first").addClass('focused');
				} else {
					var itsNext = $( '.focused' ).removeClass( 'focused' ).next().addClass( 'focused' );
					if( itsNext.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 40 });
					} else {
						
						var activeItem = $( '.focused' )[0];
						offsetTop = activeItem.offsetTop;
						var container = $('#patdropdowninner');
							
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
			if( $( '.focused' ).length < 1 ) {
				$("#patdropdown tr:last").addClass('focused');
			} else {
				if( $( '.focused' ).prev().length < 1 ) {
					$( '.focused' ).removeClass( 'focused' );
					$("#patdropdown tr:last").addClass('focused');
				} else {
					var itsPrev = $( '.focused' ).removeClass( 'focused' ).prev().addClass( 'focused' );
					if( itsPrev.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 38 });
					} else {
						var activeItem = $( '.focused' )[0];
						offsetTop = activeItem.offsetTop;
						var container = $('#patdropdowninner');
							
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
				$( '.focused' ).trigger( 'click' );
				e.preventDefault();
				
				diagsearch = true;
			}
			//alert(e.target);
			return false;
		}
		
		if(e.keyCode == 9)
		{
			$("#patdropdown"+id).hide();
			return;	
		}
		
		setTimeout(function(){dropDownchange()},400);
		
	}


