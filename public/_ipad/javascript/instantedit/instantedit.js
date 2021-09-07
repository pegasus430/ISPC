function datosServidor() {
};

datosServidor.prototype.iniciar = function() {
	try {
		// Mozilla / Safari
		this._xh = new XMLHttpRequest();
	} catch (e) {
		// Explorer
		var _ieModelos = new Array(
		'MSXML2.XMLHTTP.5.0',
		'MSXML2.XMLHTTP.4.0',
		'MSXML2.XMLHTTP.3.0',
		'MSXML2.XMLHTTP',
		'Microsoft.XMLHTTP'
		);
		var success = false;
		for (var i=0;i < _ieModelos.length && !success; i++) {
			try {
				this._xh = new ActiveXObject(_ieModelos[i]);
				success = true;
			} catch (e) {
			}
		}
		if ( !success ) {
			return false;
		}
		return true;
	}
}

datosServidor.prototype.ocupado = function() {
	estadoActual = this._xh.readyState;
	return (estadoActual && (estadoActual < 4));
}

datosServidor.prototype.procesa = function() {
	if (this._xh.readyState == 4 && this._xh.status == 200) {
		this.procesado = true;
	}
}

datosServidor.prototype.enviar = function(urlget,datos) {
//	alert(urlget);
//	alert(datos);
	if (!this._xh) {
		this.iniciar();
	}
	if (!this.ocupado()) {
		this._xh.open("POST",urlget,false);
		this._xh.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		//ISPC-2890,Elena,19.04.2021
		this._xh.send("content="+ encodeURIComponent(datos));
		if (this._xh.readyState == 4 && this._xh.status == 200) {
			return this._xh.responseText;
		}

	}
	return false;
}


var urlBase_memo = "ajax/savememo";
var urlBase_whitebox = "ajax/savewhitebox"; //Maria:: Migration CISPC to ISPC 22.07.2020
var urlBase_allergy = "ajax/saveallergies";

var urlBase_usertextbox = "ajax/saveclientuserbox";		//ISPC-2827 Lore 30.03.2021

var formVars = "";
var changing = false;


function fieldEnter(campo,evt,idfld) {
	evt = (evt) ? evt : window.event;
	if (evt.keyCode == 13 && campo.value!="") {
		elem = document.getElementById( idfld );
		remotos = new datosServidor;

		if(idfld == 'allergies_pat'){
			urlBase = urlBase_allergy;
		} else if (idfld == 'patient_memo') {
			urlBase = urlBase_memo;
		} else if (idfld == 'user_text_box') {		//ISPC-2827 Lore 30.03.2021
			urlBase = urlBase_usertextbox;
		} else{
			urlBase = urlBase_whitebox;
		}
		var content = campo.value;
//		nt = remotos.enviar(urlBase + "?fieldname=" +encodeURIComponent(elem.id)+ "&content="+encodeURIComponent(campo.value)+"&"+formVars,"");
		nt = remotos.enviar(urlBase + "?fieldname=" +encodeURIComponent(elem.id)+"&"+formVars,"", content);
		//remove glow
		noLight(elem);
		elem.innerHTML = nt;
		changing = false;
		return false;
	} else {
		return true;
	}


}

function fieldBlur(campo,idfld) {
//	if (campo.value!="") {
		elem = document.getElementById( idfld );
		remotos = new datosServidor;

	if(idfld == 'allergies_pat'){
		urlBase = urlBase_allergy;
	} else if (idfld == 'patient_memo') {
		urlBase = urlBase_memo;
	} else if (idfld == 'user_text_box') {		//ISPC-2827 Lore 30.03.2021
		urlBase = urlBase_usertextbox;
	} else{
		urlBase = urlBase_whitebox;
	}

		var content = campo.value;
//		nt = remotos.enviar(urlBase + "?fieldname=" +encodeURIComponent(elem.id)+ "&content="+encodeURIComponent(campo.value)+"&"+formVars,"");
		nt = remotos.enviar(urlBase + "?fieldname=" +encodeURIComponent(elem.id)+"&"+formVars, content);
		elem.innerHTML = nt;
		changing = false;
		return false;
//	}
}

//edit field created
function editBox(actual) {
	//alert(actual.nodeName+' '+changing);
	if(!changing){
//		width = widthEl(actual.id) + 20;
		width = 770;
		height =heightEl(actual.id) + 2;
//console.log(actual.id);
		var addStyle = "";
		if(actual.id == "patient_whitebox"){
			addStyle = "resize: none;";
		}
//		if(height < 40){
//			if(width < 100)	width = 150;
//			actual.innerHTML = "<input id=\""+ actual.id +"_field\" style=\"width: "+width+"px; height: "+height+"px;\" maxlength=\"254\" type=\"text\" value=\"" + actual.innerHTML + "\" onkeypress=\"return fieldEnter(this,event,'" + actual.id + "')\" onfocus=\"highLight(this);\" onblur=\"noLight(this); return fieldBlur(this,'" + actual.id + "');\" />";
//		}else{
			if(height < 50) height = 50;
			var emptyTextPlaceholder = (actual.getAttribute('data-emptytext'));
			if(actual.innerHTML == 'Dies ist ein Memo Feld, klicken Sie hier um etwas einzutragen.') { actual.innerHTML = ''; }
			if(actual.innerHTML == 'Mögliche Allergien und Kommentare können hier eingetragen werden') { actual.innerHTML = ''; }	//TODO 2853 Lore 27.01.2020
			if(actual.innerHTML == 'Dies ist ein Empty Feld, klicken Sie hier um etwas einzutragen.') { actual.innerHTML = ''; }	//ISPC-2827 Lore 31.03.2021
			if(actual.innerHTML == emptyTextPlaceholder) { actual.innerHTML = ''; }
			actual.innerHTML = "<textarea name=\"textarea\" id=\""+ actual.id +"_field\" style=\"width: "+width+"px; height: "+height+"px;" + addStyle + "\" onfocus=\"highLight(this);\" onblur=\"noLight(this); return fieldBlur(this,'" + actual.id + "');\">" + br2nl(actual.innerHTML) + "</textarea>";
//		}
		changing = true;
	}

		actual.firstChild.focus();
}



//find all span tags with class editText and id as fieldname parsed to update script. add onclick function
function editbox_init(){
	if (!document.getElementsByTagName){ return; }
	var spans = document.getElementsByTagName("span");

	// loop through all span tags
	for (var i=0; i<spans.length; i++){
		var spn = spans[i];

        	if (((' '+spn.className+' ').indexOf("editText") != -1) && (spn.id)) {
			spn.onclick = function () { editBox(this); }
			spn.style.cursor = "pointer";
			spn.title = "Klicken Sie zum Bearbeiten!";
       		}

	}


}

//crossbrowser load function
function addEvent(elm, evType, fn, useCapture)
{
	if (elm.addEventListener){
		elm.addEventListener(evType, fn, useCapture);
		return true;
	} else if (elm.attachEvent){
		var r = elm.attachEvent("on"+evType, fn);
		return r;
	} else {
		//alert("Please upgrade your browser to use full functionality on this page");
	}
}

//get width of text element
function widthEl(span){
	if(document.layers){
		w=document.layers[span].clip.width;
	} else if (document.all && !document.getElementById){
		w=document.all[span].offsetWidth;
	} else if(document.getElementById){
		w=document.getElementById(span).offsetWidth;
	}
	return w;
}

//get height of text element
function heightEl(span){

	if(document.layers){
		h=document.layers[span].clip.height;
	} else if (document.all && !document.getElementById){
		h=document.all[span].offsetHeight;
	} else if(document.getElementById){
		h=document.getElementById(span).offsetHeight;
	}
	return h;
}

function highLight(span){
	//span.parentNode.style.border = "2px solid #D1FDCD";
	//span.parentNode.style.padding = "0";
//	span.style.border = "1px solid #54CE43";
}

function noLight(span){
	//span.parentNode.style.border = "0px";
	//span.parentNode.style.padding = "2px";
	span.style.border = "0px";
}

//sets post/get vars for update
function setVarsForm(vars){
	formVars  = vars;
}
//change br 2 nl when editing
function br2nl (varTest){
	return varTest.replace(/<br>/g, "\r").replace(/<BR>/g, "\r");
}

addEvent(window, "load", editbox_init);