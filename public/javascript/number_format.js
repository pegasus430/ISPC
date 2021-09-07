function number_format (number, decimals, dec_point, thousands_sep) {
    // http://kevin.vanzonneveld.net
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +     bugfix by: Michael White (http://getsprink.com)
    // +     bugfix by: Benjamin Lupton
    // +     bugfix by: Allan Jensen (http://www.winternet.no)
    // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +     bugfix by: Howard Yeend
    // +    revised by: Luke Smith (http://lucassmith.name)
    // +     bugfix by: Diogo Resende
    // +     bugfix by: Rival
    // +      input by: Kheang Hok Chin (http://www.distantia.ca/)
    // +   improved by: davook
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Jay Klehr
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Amir Habibi (http://www.residence-mixte.com/)
    // +     bugfix by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +      input by: Amirouche
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: number_format(1234.56);
    // *     returns 1: '1,235'
    // *     example 2: number_format(1234.56, 2, ',', ' ');
    // *     returns 2: '1 234,56'
    // *     example 3: number_format(1234.5678, 2, '.', '');
    // *     returns 3: '1234.57'
    // *     example 4: number_format(67, 2, ',', '.');
    // *     returns 4: '67,00'
    // *     example 5: number_format(1000);
    // *     returns 5: '1,000'
    // *     example 6: number_format(67.311, 2);
    // *     returns 6: '67.31'
    // *     example 7: number_format(1000.55, 1);
    // *     returns 7: '1,000.6'
    // *     example 8: number_format(67000, 5, ',', '.');
    // *     returns 8: '67.000,00000'
    // *     example 9: number_format(0.9, 0);
    // *     returns 9: '1'
    // *    example 10: number_format('1.20', 2);
    // *    returns 10: '1.20'
    // *    example 11: number_format('1.20', 4);
    // *    returns 11: '1.2000'
    // *    example 12: number_format('1.2000', 3);
    // *    returns 12: '1.200'
    // *    example 13: number_format('1 000,50', 2, '.', ' ');
    // *    returns 13: '100 050.00'
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}


function assertPositiveInteger( testNumber , thousands_sep ) {    
	return testNumber.match(/^\d+$/) && parseInt(testNumber) >= 0;
}


function str2number_format ( str ) {
	/** 
	  * 2017.06.02 - claudiu 
	  * format a suposed german-string-number into a number you can use on js parseFloat
	  * result is string
	  * 
	  * regex for inputs: .replace(/[^0-9,-]/g, "").replace(/(?!^)-/g, '').replace(/[,](?=.*[,])/g, "").replace(/[,]/g, ".")
	  * replace all that is not [0-9]|,|-
	  * leave only first minus
	  * leave only last comma
	  * replace comma with dot
	  * 
	  * str2number_format(123,77)					=> 123.77
	  * str2number_format(123,123.77)				=> 123.12377
	  * str2number_format(12.3123.77)				=> 12312377 
	  * str2number_format(12..31.23,77)				=> 123123.77 
	  * str2number_format(-1A...2BcD-3E..,5x.5y)	=> -123.55
	  *
	 **/
	return (str + '').replace(/[^0-9,-]/g, "").replace(/(?!^)-/g, '').replace(/[,](?=.*[,])/g, "").replace(/[,]/g, ".");
}

function german_format(number) {
	var postComma, preComma, stringReverse, _ref;
	stringReverse = function(str) {
		return str.split('').reverse().join('');
	};

	_ref = number.toFixed(2).split('.'), preComma = _ref[0], postComma = _ref[1];

	//handle - sign
	var modified_preComma = String(Number(preComma)*(-1)).length;

	var minus_sign = '';
	if(preComma.length > modified_preComma) {
		minus_sign = '-';
		preComma = String(Number(preComma)*(-1));
	} else {
		minus_sign = '';
	}

	preComma = stringReverse(stringReverse(preComma).match(/.{1,3}/g).join('.'));

	return minus_sign + preComma + "," + postComma;
}