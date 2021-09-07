;
/**
 * overview.jsISPC-2827 
 * @date 29.03.2021 
 * @author @ancuta
 *
 */
if (typeof (DEBUG) !== 'undefined' && window.DEBUG === true) {
	console.info('custom view js included : ' + document.currentScript.src);
}

$(document).ready(function() {
});


function DoNav(theUrl) {
	document.location.href = appbase + theUrl;
}


