var l_loading = function(){

var dlist = '<div class="DropDwnTable" style="margin-top:5px;margin-left:120px;  height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble"><tr class="bluerow"><td><img src="images/loading.gif"></td></tr></table></div>';
 document.getElementById('locdropdown').style.display = "";
 document.getElementById('locdropdown').innerHTML=dlist;
	 
}

function locationDropDown(ltrval)
{
  ajaxCallserver({url:'patient/locationdropdown',
				  callLoading:l_loading,
				   method:'POST',
				  data:{ltr:$("#location_id").val()}
				  });	
}


var locations;
var locdropdiv = function(params){

var dlist = '<div class="DropDwnTable" id="locdropdowninner" style="margin-top:5px;margin-left:120px; height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble livesearchdropdown">';
	locations = params.locations;
	if(locations.length<1)
	{
	  //  dlist+='<tr><td colspan="2" class="BlueBox">No Results Found</td></tr>';
		document.getElementById('hidd_location_id').value  = "";
		$('#locdropdown').hide('slow');
	}
	
	for(i=0;i<params.locations.length;i++)
	{
		dlist+='<tr class="bluerow" onclick="selectLoc('+i+')"><td >'+params.locations[i].location +'</td></tr>';
	}
	 dlist+= "</table></div>";
	 
	 document.getElementById('locdropdown').style.display = "";
	 document.getElementById('locdropdown').innerHTML=dlist;
	// document.getElementById('msg').innerHTML = "If doctor is not found in list. please add new doctor from here";
	 
}

function selectLoc(i)
{
	document.getElementById('location_id').value =locations[i].location;
	document.getElementById('locdropdown').style.display = "none";
	document.getElementById('hidd_location_id').value =locations[i].id;
}


	
	var livesearchlocationkeup = function( e,ele ){
		//alert($(ele).attr('id'));
		id = $(ele).attr('id');
		fdocblur = false;
		//id = id.substring(t.length);
		
		if( e.keyCode == 40 ) {
			diagsearch = false;
			if( $("#locdropdown").find( '.focused' ).length < 1 ) {
				//alert($("#diagnodropdown"+id+'tr:first'));
				//$("#diagnodropdown"+id+' tr:first').removeClass('bluerow');
				
				$("#locdropdown tr:first").addClass('focused');
				//alert($("#diagnodropdown"+dv+'tr:first'));
				//alert('$("#diagnodropdown"+dv+'tr:first')');
				//$('.list_name p:first' ).addClass( 'highlight' );
				
			} else {
				if( $("#locdropdown").find( '.focused' ).next().length < 1 ) {
					$("#locdropdown").find( '.focused' ).removeClass( 'focused' );
					$("#locdropdown tr:first").addClass('focused');
				} else {
					var itsNext = $("#locdropdown").find( '.focused' ).removeClass( 'focused' ).next().addClass( 'focused' );
					if( itsNext.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 40 });
					} else {
						
						var activeItem = $("#locdropdown").find( '.focused' )[0];
						offsetTop = activeItem.offsetTop;
						var container = $('#locdropdowninner'+id);
							
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
			if( $("#locdropdown").find( '.focused' ).length < 1 ) {
				$("#locdropdown tr:last").addClass('focused');
			} else {
				if( $("#locdropdown").find( '.focused' ).prev().length < 1 ) {
					$("#locdropdown").find( '.focused' ).removeClass( 'focused' );
					$("#locdropdown tr:last").addClass('focused');
				} else {
					var itsPrev = $("#locdropdown").find( '.focused' ).removeClass( 'focused' ).prev().addClass( 'focused' );
					if( itsPrev.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 38 });
					} else {
						var activeItem = $("#locdropdown").find( '.focused' )[0];
						offsetTop = activeItem.offsetTop;
						var container = $('#locdropdowninner'+id);
							
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
				$("#locdropdown").find( '.focused' ).trigger( 'click' );
				e.preventDefault();
				
				diagsearch = true;
			}
			//alert(e.target);
			return false;
		}
		
		if(e.keyCode == 9)
		{
			$("#locdropdown"+id).hide();
			return;	
		}
		
		setTimeout(function(){locationDropDown()},400);
		
	}
	
	