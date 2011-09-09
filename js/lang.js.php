/**
 *
 * usage guidelines:
 * This file should be included at the top of every template file.
 * N.B. The path should NOT include the .php file extension.
 * The path therefore should look like:
 * Config::getRelativeRoot().'/js/jang.js'   
 * 
 * The jtext object can be used at any time anywhere in the site.
 * Quite simply to get internationalised version of "Hour" the syntax is:
 * jtext.get("HR");    
 */    

function JText(){
  test = "test1";
  lang = new Array();
  lang["HR"] = "<?php echo JText::_('HR'); ?>";
  lang["MN"] = "<?php echo JText::_('MN'); ?>";
}

jtext = new JText();
JText.prototype.get = function(variable){  
  var ret = lang[variable];    
  if(typeof ret == 'undefined'){
    alert("\""+ variable + "\" was not found in the js language library");
  }
  else{
    return ret;
  }
}

JText.prototype.sprintf = function(variable,variable2){
  str = 'Jtext sprintf function not yet written:\n'+variable+'\n'+variable2;
  alert(str);
  return str;  
}


<?php

exit();

?>