

function consoleOpen(){
	//alert('open');
	console.log('open');	
	var debugConsole = document.getElementById('phpconsole');
	debugConsole.style['height'] = '300px';
}


function consoleClose(){
	console.log('close');
	var debugConsole = document.getElementById('phpconsole');
	debugConsole.style['height'] = '40px';

}

function highlight(item){
	if(item.selected){
		item.style['backgroundColor'] = 'transparent';
		item.selected = false;
	}
	else{
		item.style['backgroundColor'] = '#99ccff';
		item.selected = true;
	}
}