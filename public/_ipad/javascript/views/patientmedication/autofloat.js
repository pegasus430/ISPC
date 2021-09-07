/*2020-06-12 medikation table expand buttons auto-float */

/*ISPC-2329 Lore 16.06.2020*/
function mediAutoFloat() {
	if ($( window ).width() > 400 ) {
		$( "table.medikation" ).each(function( index ) {
		if (($( window ).width() - 202) < $(this).width()) {
				
				$( this ).find( ".details.expand_details" ).css({position: "relative", overflow: "visible"});
							
				var leftpadding;
				leftpadding = $(window).width() - $(this).width() - 233 + $( window ).scrollLeft();
				
				if (leftpadding > 0) leftpadding = 0;
				else if (leftpadding < -555) {leftpadding = -555;}
 
				$( this ).find( ".details.expand_details > button" ).css({
					position: "absolute",
					left: leftpadding,
					height: "100%",
					top: "0",
					display:"block"
					});
			} else {
				$( this ).find( ".details.expand_details > button" ).css({
					position: "static",
					left: "auto",
					height: "100%",
					top: "auto",
					display: "none" 
					});
			}	
		});
	}
}

 

$(window).on("load resize scroll",function(e){
    mediAutoFloat()
})
