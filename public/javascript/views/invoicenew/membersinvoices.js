/**
 * membersinvoices.js
 * intoduced on ISPC-1956
 */


function payments_markaspaid_or_delete( action,  _this_single ) 
{
	if (typeof action ==='undefined') {
		return;
	}
	
	var dialog_title = "";
	var dialog_text = "";
	var dialog_info = "";
	
	if (action == "delete") {
		dialog_title = translate('payments_table_lang')['payments_delete_title'];
		dialog_text = translate('payments_table_lang')['payments_delete_text'];
	} else if (action == "markaspaid") {
		dialog_title = translate('payments_table_lang')['payments_markaspaid_title'];
		dialog_text = translate('payments_table_lang')['payments_markaspaid_text'];
	}
	

	
	var url = appbase + 'invoicenew/payments_markaspaid';
	var invoices = new Array;
	
	if (arguments.length == 2 && typeof arguments[1] == 'object') {
		//uncheck all from before
		$("#member_payments input.checkBox").prop('checked', false);
		
		var _this = arguments[1];
		var _parent_tr = $(_this).closest("tr");
		var _cbox = $("input.checkBox", _parent_tr);		
		_cbox.prop('checked', true);
		invoices.push(_cbox.val());
		
		dialog_info = "<br/>" + $(".member_invoice", _parent_tr).html() + " - " + $(".amount", _parent_tr).html();	

		
//		url += '?action=invoice-payments&invoices=' + invoices.toString();
	}
	else if ($("form#invoice_payments").length) {
		$(".checkBox:checked",$("#download_sepa_xml_batch_button").closest("form#invoice_payments")).each(function(){
			invoices.push(this.value);
		});
//		url += '?action=invoice-payments&invoices=' + invoices.toString();
	}		
	
	
	dialog_text = dialog_text.format(dialog_text, dialog_info );

	
	if (invoices.length == 0 ){
		setTimeout(function () {alert(translate("no invoice selected"));}, 50);
	}else{
				 
		$('#dialog_payments_markaspaid_or_delete').dialog({
			
			dialogClass: "markaspaid_sepaxml_dialog",
		    autoOpen: true,
		    closeOnEscape: true,
		    
		    open: function () {
		    	
		    	$(".info", this).html(dialog_text);
		    	
		    	$(".markaspaid_comment", this).val("");
		    	
		    	$(".markaspaid_date", this).datepicker('setDate', new Date());	    	
		    	
		    },
		    
		    beforeClose: function () {
		        //return false; // don't allow to close
		    },
		    
		    close: function (event, ui) {
				//dialog was closed
		    },

		    buttons: [
		    {
		        text: translate('yesconfirm'),
		        click: function () {

		        	if (checkclientchanged()){
		        		var date = $(".markaspaid_date", $(this).dialog()).val();
			        	var comment = $(".markaspaid_comment", $(this).dialog()).val();
			        	
			        	$("#invoice_payments > input#action").val(action);
			        	$("#invoice_payments > input.date").val(date);
			        	$("#invoice_payments > input.comment").val(comment);
						
			        	$("#invoice_payments").submit();
						
			        	$(this).dialog("close");

		        	}
		        	
		        },

		    },
		    
		    //cancel button
		    {
		        text: translate('cancel'),
		        click: function () {
		        	$(this).dialog("close");
		        },

		    }
		    
		    ],
		        
		    modal: true,
		    title: dialog_title,
		    minWidth: 350,
		    minHeight: 250,
		});
		
	
		return false;
		
	}
	return false;
	
}



function getQueryParams(qs) {
	
	qs = qs || window.location.search;
    qs = qs.split('+').join(' ');

    var params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
    }

    return params;
}