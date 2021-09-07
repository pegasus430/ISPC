;
/**
 * wallnews.js
 * @date 16.11.2018
 * @author @cla
 *
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : '+document.currentScript.src);
}

var overviewcolumn_content_item21_datatable = null;

$(document).ready(function (){
	overviewcolumn_content_item21_datatable = drawDataTableWallNews();
});



function drawDataTableWallNews() {
	return $('#datatable_wall_news').dataTable({
		"language": {
            "url" : appbase + "/javascript/data_tables/de_language.json",
        },
        
        sDom: 
            //'<"search"f>'+
            //'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"l>'+
            'rt'+
            '<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">lip>',
      
        
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "pageLength" : 25,
//        "pagingType": "simple",
//        "sPaginationType": "simple",
         
        "bInfo":	false,
//      
        "filter":	false,
        "bFilter":	false,
        
        "bSort":	false,
        
        "autoWidth": true,
        "scrollX": true,
        "scrollCollapse": true,
            
        'serverSide': true,
        "bServerSide": true,

        'processing': true,
        "bProcessing": true,
//        

        //"sAjaxSource": window.location.href,
        "sAjaxSource": appbase + 'overview/wallnews',


        "fnServerData": function ( sSource, aoData, fnCallback, oSettings ) {
          
            if (oSettings.jqXHR) {
                oSettings.jqXHR.abort();
            }      
            aoData.push({ "name": "__action", "value": "fetchWallNewsList" });

            var _f_keyword = oSettings.oPreviousSearch.sSearch;
            if (_f_keyword != '') {
                aoData.push({ "name": "f_keyword", "value":  _f_keyword });
            }


            oSettings.jqXHR = $.ajax( {
                "dataType": 'json',
                "type": "POST",
                "url": sSource,
                   "data": aoData,
                   "success": fnCallback
          });
        },

//
        columns: [
                  { 
                	  visible: false,
                      data: 'id',
                      className: "id",
                      name:"id",
                  },
                  
                  { 
                	  visible: false,
                      data: 'userid',
                      className: "userid",
                      name:"userid", 
                  },
                  { 
                      data: 'user_nice_name',
                      className: "user_nice_name",
                      name:"user_nice_name",
                      title:"" ,  
                  },
                  
                  { 
                      data: 'news_date',
                      className: "news_date",
                      name:"news_date",
                      title:"" , 
                  },
                  
                  { 
                	  visible: false,
                      data: 'patient_nice_name',
                      className: "patient_nice_name",
                      name:"patient_nice_name",
                  },
                  
                  { 
                	  visible: false,
                      data: 'patient_id',
                      className: "patient_id",
                      name:"patient_id",
                  },
                  
                  { 
                	  visible: false,
                      data: 'news_content',
                      className: "news_content",
                      name:"news_content",
                  },  
                  
                 ],
//        
                        
         order: [[ 0, "asc" ]],

         

         "drawCallback": function ( settings ) 
         {
             
             this.api().rows( {page:'current'} ).every(function() {
            	    _odd = $(this.node()).hasClass('odd') ? 'odd' : '';
            	    _even = $(this.node()).hasClass('even') ? 'even' : '';

                    var _that = this;
                    
            	    var _data = this.data();
            	    
                    this.child( datatable_format_child_row(_data) , "child " + _odd + " " + _even).show();

                    
                    $(this.child()).find('.action_delete').on('click', function(){
                        jConfirm(translate('Confirm Wallnews delete message'), '', function(r) {
        					if(r)
        					{
            					var _url = appbase + "overview/overview";
            					var _dataPost = {"__action":  "deleteWallNews", "wallNews": {'id': _data.id} };

        						$.post(_url, _dataPost, function(result) {

                                    if (result.hasOwnProperty('success') && result.success == true) {
//                                     	_that.child().remove();
                                    	_that.remove();
                                    }
                                });	
        					}
        				});
        				return false;
                        
                    });
                    
                    $(this.child()).find('.action_edit').on('click', function(){
                        add_new_wallnews(_that);
                    });
                    
            });
  
         },
    

    });    
}





/* Formatting function for row details - modify as you need */
function datatable_format_child_row ( data ) {

	var _patient_span = '',
	_content_span = '',
	_reedit_span = '';

	if (data.hasOwnProperty('news_content') && data.news_content != '') {
		_content_span = '<div class="news_content">' + data.news_content.replace(/\n/g,"<br>") + '</div>';
	}
		
	if (data.hasOwnProperty('patient_nice_name') && data.patient_nice_name != ''){
		_patient_span = '<div class="patient_nice_name"><a href="patientcourse/patientcourse?id='+data.patient_id+'">' 
			+ translate('patient')
			+ ": "
			+ data.patient_nice_name 
			+ '</a></div>';
	}

    if ( data.userid == window.box_21_userid) {
    	_reedit_span = '<div class="actions">'
        	+ '<i class="icon-btn action action_edit" data-btnaction="work-edit" title="'+translate('details')+'"></i>'
        	+ '<i class="icon-btn action action_delete" data-btnaction="work-delete" title="'+translate('delete')+'"></i>'
        	+ '</div>';
    }
    
	return "<div>" + _patient_span + _content_span + _reedit_span + "</div>";
}



function add_new_wallnews(_that) {

	$('<div>').append($("#box_21_dialog form").clone())
	.appendTo('body')	
    .dialog({
        'autoOpen'  : true,
        'modal'     : true,
        'closeOnEscape' : true,
        'dialogClass'   : 'dialog_add_new_wallnews',
        'title'     :  ((typeof(_that) === 'object') ? translate("Edit WallNews") : translate("Add new WallNews")),
        'width'     : '400px',
        'close'     : function () {
        	$(this).dialog('destroy').remove();
        },
        'open'      : function (){
            if (typeof(_that) === 'object') {
                //this is edit mode
                var _data = _that.data();
                $(this).find("input[name*='\[id\]']").val(_data.id);
                $(this).find("textarea[name*='\[content\]']").val($("<div/>").html(_data.news_content).text());
                
                if (_data.patient_id != '') {
                	
                	var _selectOpt = $(this).find("select[name*='\[patient\]'] option").filter(function () { return $(this).val() == _data.patient_id; }).val() || false;
	               
                	if ( ! _selectOpt) {
                		_selectOpt = $(this).find("select[name*='\[patient\]'] option").filter(function () { return $(this).html() == _data.patient_nice_name; }).val();
                	}                	
                	$(this).find("select[name*='\[patient\]']").val(_selectOpt);
	                
                }
            }
        },
        'buttons'   : [
                 {
                     text: translate('cancel'),
                     click: function() {
                         $(this).dialog('close');
                     }
                 },

                 {
                     text: translate('save'), 
                     click: function(){

                            var _url = appbase + "overview/overview";

                            var _data=$('form', this).serializeObject() || {};
                            _data.__action = "addWallNews";                   
                                     
                            $.post(_url, _data, function(result) {

                                if (result.hasOwnProperty('success') && result.success == true && typeof(overviewcolumn_content_item21_datatable) == 'object') {                                    

                                    var _patient_nice_name = $("#box_21_dialog select option[value='"+_data.wallNews.patient+"']").text() || '';

                                    
                                    var _dataRow = {
                                        "id":               result._id,

                                        
                                        "user_nice_name":   result.user_nice_name,
                                        "userid":           result.userid,
                                        
                                        "news_content":     _data.wallNews.content,
                                        "news_date":        result.news_date,
                                        
                                        "patient_id":       _data.wallNews.patient,
                                        "patient_nice_name": (_data.wallNews.patient !=0 ? _patient_nice_name : ''),
                                    };

                                    if (typeof(_that) ==='object') {
                                        //this was an update
                                        _that.data(_dataRow).invalidate().draw();
                                    } else {
                                        dt = overviewcolumn_content_item21_datatable.api();
                                        //this was an insert
                                    	dt.row.add(_dataRow);
                                    	var aiDisplayMaster = overviewcolumn_content_item21_datatable.fnSettings()["aiDisplayMaster"];
                                        aiDisplayMaster.unshift(aiDisplayMaster.pop());
                                        dt.draw();
                                    }
                                }
                            });
                            
                    	    $(this).dialog('close');
                     }
                 }
        ],
    });


}

