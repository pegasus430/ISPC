/**
 * smtpsettings.js
 */


function ssl_require_changed(_this)
{
	if ($(_this).val() == "YES") {
		$(".ssl_require_port").show();
		$("#smtp_port").hide();
	} else {
		$(".ssl_require_port").hide();
	
		if ($('.tls_require:checked').val() == "NO" ){
			$("#smtp_port").show();
		}
	}
}

function tls_require_changed(_this)
{
	if ($(_this).val() == "YES") {
		$(".tls_require_port").show();
		$("#smtp_port").hide();
	} else {
		$(".tls_require_port").hide();
		
		if ($('.ssl_require:checked').val() == "NO" ){
			$("#smtp_port").show();
		}
	}
}

function smtp_settings_changed( _this )
{
	if ($(_this).val() == "NO") {
		$("#smtp_settings_change_customer").show();
		
		$("#smtp_port").show();
		
	} else {
		$("#smtp_settings_change_customer").hide();
	}
}
	
function smtp_password_changed( _this ) {
	$("#password_changed").val("1");
}
