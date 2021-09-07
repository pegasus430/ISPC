;
/**
 * patientoveralllist.mobile.js
 * @date 01.11.2018
 * @author @cla
 * 
 * remember this file can overwrite patientoveralllist.js
 * needs window.patient_patientfileupload_createTagsRights
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}


var dataTablePatientsActive = null;
var dataTablePatientsSelectedTab = null;

$(document).ready(function() {

	dataTablePatientsActive  = drawDataTablePatients();

    var dataTablePatientsSearch = function(){
    	if (typeof dataTablePatientsActive == 'object') {
    		dataTablePatientsActive.search($('#patientsearch_second').val()).draw() ;
    	}
    };


    //attach header search to dTable, and remove .liveSearch from it
    $('#patientsearch_second')
	.off('.liveSearch')
	.delayKeyup(dataTablePatientsSearch, 500)
	.on('blur', function(){
		if (typeof dataTablePatientsActive == 'object') {
			dataTablePatientsActive.search('');
		}
	});

        
});

function changePatientlistTab(_this) {
	
	if ($(_this).hasClass('active'))
		return false;
	
	dataTablePatientsSelectedTab = $(_this).attr('rel');
	
	$(_this)
	.parent().find('a').removeClass('active')
	.end().end()
	.addClass('active')
	;
	
	dataTablePatientsActive.draw();
	
	return false; 
}


function drawDataTablePatients() {
	
    return $('.selector_allpatientlist_table').DataTable({
    	
        // ADD language
	    "language": {
	        "url" : appbase + "/javascript/data_tables/de_language.json",
	    },
        //don't add r to sDom here, there is a processing.dt fn attached to all
        sDom: 
           //'<"search"f>'+
           //'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"l>'+
           't'+
           '<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">lip>',
        
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],

		"pageLength" : 25,
		"sPaginationType": "two_button",
		
		"bInfo": false,
		
		"searchDelay" : 300,
		
		"filter": true,
		
		"bFilter": true,
		"bSort" : true,
		
		'serverSide': true,
		"bServerSide": true,
		
		'processing': true,
		"bProcessing": true,
		
		"autoWidth": false,
		"scrollX": true,
		"scrollCollapse": true,
        
        //"sAjaxSource": window.location.href,
        "sAjaxSource": appbase + 'patient/fetchoveralllist',


        "fnServerData": function ( sSource, aoData, fnCallback, oSettings ) {
          
            if (oSettings.jqXHR) {
                oSettings.jqXHR.abort();
            }      
            aoData.push({ "name": "__action", "value": "fetchMobileList" });
            

            //next are added to match the ones used in patient/fetchoveralllist
            aoData.push({ "name": "limit", "value": oSettings._iDisplayLength });
            //aoData.push({ "name": "start", "value":  oSettings._iDisplayStart });
            
            aoData.push({ "name": "ord", "value":  oSettings.aoColumns[oSettings.aaSorting[0][0]].name });
            aoData.push({ "name": "sort", "value":  oSettings.aaSorting[0][1] });
            
            var _page = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1;
            aoData.push({ "name": "page", "value":  _page });

            var _f_keyword = oSettings.oPreviousSearch.sSearch;
            if (_f_keyword != '') {
                aoData.push({ "name": "f_keyword", "value":  _f_keyword });
            }

            if (dataTablePatientsSelectedTab == null){
            	dataTablePatientsSelectedTab = 'active';
            }
            	
            aoData.push({ "name": "f_status", "value":  dataTablePatientsSelectedTab });

            oSettings.jqXHR = $.ajax( {
                "dataType": 'json',
                "type": "POST",
                "url": sSource,
                   "data": aoData,
                   "success": fnCallback
          });
        },


        columns: [
                  {
                	  orderable: true, 
                	  data: null, 
                	  className: "status", 
                	  name:"traffic_status", 
                	  title:"" , 
                	  "width": "10px" 
        		  },
        		  
        		  {
        			  orderable: true,
        			  data: "last_name",
        			  className: "name",
        			  name:"ln",
        			  title: translate('last_name')
    			  },
    			  
    			  {
    				  orderable: true, 
    				  data: "first_name", 
    				  className: "name", 
    				  name:"fn", 
    				  title: translate('first_name') 
				  },
				  
				  { 
					  visible:false, 
					  data: "epid", 
					  className: "epid", 
					  name:"epid", 
					  title:translate('Epid'), 
					  width : "60px" 
				  },
                  
                 ],
        
                        
         order: [[ 1, "asc" ]],

         
         "fnRowCallback": function( nRow, aData, iDisplayIndex ) {

             if (aData.hasOwnProperty('traffic_status') && aData.traffic_status != null && aData.traffic_status != 'null' ) {
                 $('td:eq(0)', nRow).html('<img src="' + appbase + aData.traffic_status.src + '" title="' + aData.traffic_status.title +'"/>');
             } else {
            	 $('td:eq(0)', nRow).html('-');
             }

             $(nRow).on('click', function() {
                 window.location = appbase + 'patientcourse/patientcourse?id=' + aData.enc_id;
             });
         },
    

    });    
}