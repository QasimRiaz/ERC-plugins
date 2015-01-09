jQuery.noConflict();
function callauthentication() {
      var jaxrel = fromphp.jaxfile;
      
      var valset = {
        action: 'give_mycallauthentication'
    };
    
    
        jQuery.post(jaxrel, valset, function(dat, status) {
       
        window.location.replace(dat);
        
        
    });
   
  
        
   
    
    
    
        

    
}
