;
/**
 * patientfileupload.mobile.js
 * @date 01.11.2018
 * @author @cla
 * 
 * remember this file can overwrite patientfileupload.js
 * needs window.patient_patientfileupload_createTagsRights
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

var dataTablePatientFiles = null;
	

$(document).ready(function() {
	
	dataTablePatientFiles =  drawDataTablePatientFiles();


	$("#fileFilterTags")
	.chosen({

		placeholder_text_multiple   : translate('Alle Etiketten'),
        inherit_select_classes      : true,
        allow_single_deselect       : true,
        display_selected_options    : false,
        /*width   : '50px',*/
        height: "45px",
        multiple: true,
        "search_contains": true,
        no_results_text: translate('noresultfound'),
        
        "data-row_vsprintf"     : '<span class=\"%2%\" >%1%<span style="float:right">%3%</span></span>' ,
        "data-choice_vsprintf"  : '<span class=\"choice\ %2%" >%1%</style>' ,
        "data-search_vsprintf"  : '%1%'
        
    });


    $("#fileAddTags")
	.chosen({

		placeholder_text_multiple   : translate('Please select a tag or write a new one'),
        inherit_select_classes      : true,
        allow_single_deselect       : true,
        display_selected_options    : false,
        /*width   : '50px',*/
        height: "45px",
        multiple: true,
        "search_contains": true,
        "enable_split_word_search" : false,
        no_results_text: translate('noresultfound'),
        
        "data-row_vsprintf"     : '<span class=\"%2%\" >%1%</span>' ,
        "data-choice_vsprintf"  : '<span class=\"choice\ %2%" >%1%</style>' ,
        "data-search_vsprintf"  : '%1%'
    })
    .on("chosen:update_results_content", function(evt, data) {

    	if (window.patient_patientfileupload_createTagsRights != 1) {
    		return;
    	}
        
    	var _searchText = $(data.chosen.search_field).val();
    	var _parent = $(data.chosen.search_field).parent();
		if (_searchText != '' && _searchText != data.chosen.options.placeholder_text_multiple) {

			if ($('option[value="' +_searchText+ '"]', this).length > 0) {

				if (_parent.find("#selector_createNewTag").length) {
    				_parent.find("#selector_createNewTag").remove();
				}
				
		    } else {

			    if (_parent.find("#selector_createNewTag").length) {
			    	
                    _parent.find("#selector_createNewTag").val(translate('Add `%1%` as new file-tag').format('', _searchText));

			    } else {
				    
    			    var _createNewTag = $('<input>', {
        				type	: "button",
        				'class'	: 'createNewTagBtn',
        				'id'	: 'selector_createNewTag',
        				value 	: translate('Add `%1%` as new file-tag').format('', _searchText),
        				'click' : function (){
            				var _searchText = $(data.chosen.search_field).val();
            				$('<option>', {
                				"selected"  : true,
                				"data-2"    : "client_tags",
                				"data-1"    : _searchText,
                				"value"     : _searchText,
                				"text"      : _searchText
            				}).appendTo($('#fileAddTags').find("optgroup#fileAddTags-optgroup-client_tags").last());
            				$('#fileAddTags').trigger("chosen:updated");
            				
        				}
        			});
        			
    			    $(data.chosen.search_field).after(_createNewTag);
    			
			    }

		    }	
		}
	})
	.on("chosen:hiding_dropdown", function (evt, data) {
		$(data.chosen.search_field).parent().find("#selector_createNewTag").remove();
	})
    ;


});


function drawDataTablePatientFiles() {

    return $('.selector_allpatientfile_table').DataTable({
        // ADD language
        "language": {
            "url": appbase + "/javascript/data_tables/de_language.json",
        },
        sDom:
            //'<"search"f>'+
            //'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"l>'+
            't' +
            '<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">lip>',
        "lengthMenu": [
            [10, 25, 50],
            [10, 25, 50]
        ],
        "pageLength" : 25,
//      "pagingType": "simple",
//      "pagingType": "full_numbers_no_ellipses",
        "sPaginationType": "two_button",
     
        searchDelay: 300,

        bInfo: false,
        filter: true,

        "bFilter": true,
        "bSort": true,

        "autoWidth": true,



        "scrollX": true,
        "scrollCollapse": true,

        'serverSide': true,
        "bProcessing": true,

        'processing': true,
        "bServerSide": true,


        //"sAjaxSource": window.location.href,
        "sAjaxSource": appbase + 'patient/fetchpatientfile?id=' + window.idpd,


        "fnServerData": function(sSource, aoData, fnCallback, oSettings) {

            if (oSettings.jqXHR) {
                oSettings.jqXHR.abort();
            }
            aoData.push({
                "name": "__action",
                "value": "fetchMobileList"
            });

            //f_keyword

            //next are added to match the ones used in patient/fetchoveralllist
            aoData.push({
                "name": "limit",
                "value": oSettings._iDisplayLength
            });
            //aoData.push({ "name": "start", "value":  oSettings._iDisplayStart });

            var _page = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1;
            aoData.push({
                "name": "page",
                "value": _page
            });


            var _sortColumn = $("#fileSortingArrows").val() || 'date',
            _sortOrder = $("#fileSortingArrows option:checked").data('order') || 'asc';
            
            
            aoData.push({
                "name": "clm",
                "value": _sortColumn
//                "value": oSettings.aoColumns[oSettings.aaSorting[0][0]].name
            });
            aoData.push({
                "name": "ord",
                "value": _sortOrder
//                "value": oSettings.aaSorting[0][1]
            });

            
            
            $.map($("#fileFilterTags").val() || [], function (i){
            	aoData.push({
                    "name": "fileFilterTags[]",
                    "value": i
                });
            });


//             var _fileFilterTags = $('#fileFilterTags').map(function() {
//                 return this.value;
//             });

//             aoData.push({
//                 "name": "fileFilterTags",
//                 "value": _fileFilterTags
//             });
            

//             return false;
            
            
//             var _f_keyword = oSettings.oPreviousSearch.sSearch;
//             if (_f_keyword != '') {
//                 aoData.push({
//                     "name": "f_keyword",
//                     "value": _f_keyword
//                 });
//             }


            oSettings.jqXHR = $.ajax({
                "dataType": 'json',
                "type": "POST",
                "url": sSource,
                "data": aoData,
                "success": fnCallback
            });
        },



        "autoWidth": false,
        "scrollX": true,
        "scrollCollapse": true,

        columns: [{
                data: "file_type",
                orderable: true,
                type: 'html',
                className: "filetype",
                name: "ftype",
                
                title: '',

                render: function ( data, type, row ) {
//                 	return data;
                	return '<div class="filetype"><span>' + data + '</span></div>';
                },
                width: '10px'
            },
            
            {
            	type: 'html',
                orderable: true,
                data: "title",
                className: "filename",
                name: "title",
                title: translate('filename'),
                render: function ( data, type, row ) {
                	var _tagsDiv = '';
                    if (row.tags != null) {
                    	_tagsDiv = '<div class="filetags" style="float:right; position:absolute; top:5px; right:0"></div>';
                    }
                	return '<div class="filename">' + data + '</div>' + _tagsDiv;
                }
            },

            {
            	visible:false,
            	type: 'html',
                orderable: false,
                data: "tags",
                className: "filetags",
                name: "tags",
                title: translate('tags'),
                render: function ( data, type, row ) {
                	var _tagsDiv = '';
                    if (row.tags != null) {
                    	_tagsDiv = '<div class="filetags"></div>';
                    }
                	return _tagsDiv;
                },
                width: '30px'
            },

            {
                visible:false,
                orderable: true,
                data: "create_date",
                className: "createdate",
                name: "date",
                title: translate('create_date'),
                width: '100px',
                render: function ( data, type, row ) {
                	return (new Date(data)).toLocaleDateString('de-DE', { year: 'numeric', month: 'numeric', day: 'numeric' });
                }
            },

            {
            	type: 'html',
                orderable: false,
                data: null,
                type: 'html',
                className: "delete_file",
                name: "delete_file",
                title: "",
                render: function ( data, type, row ) {
                	return '<a href="javascript:pfileremove(' + row.id + ', \'' + row.title + '\');" class="btnDelete"></a>';
                },
                width: '10px'
            }

        ],


        order: [
            [3, "asc"]
        ],


        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {

            $(nRow).addClass('doc');
            
            // Cell click
//         	$('td.filetype, td.filename, td.createdate', nRow).on('click', function() {
        	$('td.filename div.filename', nRow).on('click', function() {
    			window.location.href = appbase + "stats/patientfileupload?id="+ window.idpd + "&doc_id=" + aData.id;
            	
        	});
        	$('td.filetype', nRow).qtip({    
                content: {
               	 title :null,
                    text: function() {
                        return aData.create_date_de;
                    }
                },
                show: { 
               	 solo: true 
           	 },
                hide:{
               	 delay:500,
                    fixed:true
                },
                style: { 
                    width: '150px',
                    'min-height': '35px',
                    padding: 5,
                    color: 'black',
                    textAlign: 'left',
                    border: {
                        width: 1,
                        radius: 3
                    },
                    tip: 'centerLeft',
                    classes: { 
                        tooltip: 'ui-widget', 
                        tip: 'ui-widget', 
                        title: 'ui-widget-header', 
                        content: 'ui-widget-content' 
                    } 
                },
                
                position: {
    				my: 'center left',  // Position my top left...
    			    at: 'center right' // at the bottom right of...
			    },
            });

        	
        	
        	if (aData.tags != null) {
            	$('.filetags', nRow).qtip({    
                     content: {    
    
//                          title: function(){
//                         	 return "Tags ";
//                     	 },
                    	 title :null,
                    	 
                         text: function() {
                        	 var _tags = [];
                             for(var key in aData.tags) {
                                 _tags.push(aData.tags[key]);
                             }
                             return  _tags.join(", ");
                             
                         }
                     },
                     show: { 
                    	 solo: true 
                	 },
                     hide:{
                    	 delay:500,
                         fixed:true
                     },
                     style: { 
                         width: '150px',
                         'min-height': '35px',
                         padding: 5,
                         color: 'black',
                         textAlign: 'left',
                         border: {
                             width: 1,
                             radius: 3
                         },
                         tip: 'centerRight',
                         classes: { 
                             tooltip: 'ui-widget', 
                             tip: 'ui-widget', 
                             title: 'ui-widget-header', 
                             content: 'ui-widget-content' 
                         } 
                     },
                     
                     position: {
         				my: 'center right',  // Position my top left...
         			    at: 'center left' // at the bottom right of...
     			    },
                 });

        	 }









        	         
        },


        "initComplete": function(settings, json) { 
        	
        	$('.dataTables_scrollHead thead').hide();
//            this.DataTable().columns.adjust().draw();
//             $('.datatable', $(ui.panel)).DataTable().columns.adjust();
        },

    });
}


function pfileremove(_idFile, _titleFile){
	jConfirm(translate('confirmdeleterecord') + "\n<br/>" + _titleFile, translate('confirmdeletetitle'), function(r) {
		if(r) {	
			window.location.href = appbase + "patient/patientfileremove?id="+ window.idpd + "&did=" + _idFile;
		}
	});
}

function dataTablePatientFilesSearch(_val, _type) {
	
	if (_type === 'sort') {
        $("#fileSortingArrows option")
        .removeClass("selected")
        .each(function(_v){
        	$(this).text(translate('Sort by ' + this.value));
    	})
        ;            
        
        var _sortOrder = $("#fileSortingArrows option:checked").data('order') || 'asc';
        
        
        $("#fileSortingArrows option[value='"+_val+"']")
        .toggle()
        .addClass("selected")
        .text( translate("icon sort order " +_sortOrder) + " " + translate("Sorted by " + _val));
	}
	
    if (typeof dataTablePatientFiles == 'object') {
    	dataTablePatientFiles.draw();
    }
}