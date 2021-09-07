/**
 * @cla on 01.11.2018
 * add here fn you want available in all mobile/layout_new.phtml
 */

jQuery(document).ready(function() {
	
	//page blockUI on datatables loading
	$(document).on( 'processing.dt', function ( e, oSettings, bShow) {
		
	//    var api = new $.fn.dataTable.Api( oSettings );
		if (bShow) {
		    $.blockUI({
		        message: '<h2>' + translate('processing') + '</h2><div class="spinner_square"></div>'
		    });
		} else {
		    $.unblockUI();
		}
	}) ;
});

jQuery.fn.dataTableExt.oPagination.two_button = {
    "fnInit": function ( oSettings, nPaging, fnCallbackDraw )
    {
	    
        var nPrevious = document.createElement( 'span' );
        var nNext = document.createElement( 'span' );
        var nThPageText = document.createElement( 'span' );
        
   	 
        nPrevious.appendChild( document.createTextNode( oSettings.oLanguage.oPaginate.sPrevious ) );
        nNext.appendChild( document.createTextNode( oSettings.oLanguage.oPaginate.sNext ) );
        nThPageText.appendChild( document.createTextNode( "1 of n" ) );
 
        nPrevious.className = "paginate_button previous fg-button ui-button ui-state-default";
        nNext.className="paginate_button next fg-button ui-button ui-state-default";
        nThPageText.className="paginate_middle_text fg-button ui-button ui-state-default";
 
        nPaging.appendChild( nPrevious );
        nPaging.appendChild( nThPageText );
        nPaging.appendChild( nNext );
 

 
        $(nPrevious).click( function() {
        	if (! $(this).hasClass('ui-state-disabled')) {
	            oSettings.oApi._fnPageChange( oSettings, "previous" );
	            fnCallbackDraw( oSettings );
        	}
        } );
 
        $(nNext).click( function() {
	        if (! $(this).hasClass('ui-state-disabled')) {
	            oSettings.oApi._fnPageChange( oSettings, "next" );
	            fnCallbackDraw( oSettings );
	        }
        } );
 
      
        /* Disallow text selection */
        $(nPrevious).bind( 'selectstart', function () { return false; } );
        $(nNext).bind( 'selectstart', function () { return false; } );


        
    },
 
 
    "fnUpdate": function ( oSettings, fnCallbackDraw )
    {
        if ( !oSettings.aanFeatures.p )
        {
            return;
        }
 
        /* Loop over each instance of the pager */
        var an = oSettings.aanFeatures.p;

        var _page = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1;
        var _pageT = Math.ceil(oSettings._iRecordsDisplay / oSettings._iDisplayLength);
        
        for ( var i=0, iLen=an.length ; i<iLen ; i++ )
        {
            var buttons = an[i].getElementsByTagName('span');

            buttons[1].textContent = _page + ' / ' + _pageT;
            
            if ( oSettings._iDisplayStart === 0 )
            {
                buttons[0].className = "fg-button ui-button ui-state-default ui-state-disabled paginate_disabled_previous ";
                buttons[0].disabled = true;
            }
            else
            {
                buttons[0].className = "fg-button ui-button ui-state-default paginate_enabled_previous";
                buttons[0].disabled = false;
            }
 
            if ( oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay() )
            {
                buttons[2].className = "fg-button ui-button ui-state-default ui-state-disabled paginate_disabled_next";
                buttons[2].disabled = true;
            }
            else
            {
                buttons[2].className = "fg-button ui-button ui-state-default paginate_enabled_next";
                buttons[2].disabled = false;
            }
        }
    }
};
