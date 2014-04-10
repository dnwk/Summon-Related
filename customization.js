// Detail page script v1.20.02-001 Jan 03 2013 Kun Lin


function isNumber(n)
{
   return n == parseFloat(n);
}
function isEven(n) 
{
  return isNumber(n) && (n % 2 == 0);
}


$(document).ready(function() {
//setup two Ajax Queues and run parallels. 
var ajaxQueue1 = $({});

$.ajaxQueue1 = function( ajaxOpts ) {
    var jqXHR1,
        dfd1 = $.Deferred(),
        promise1 = dfd1.promise();

    // run the actual query
    function doRequest( next ) {
        jqXHR1 = $.ajax( ajaxOpts );
        jqXHR1.done( dfd1.resolve )
            .fail( dfd1.reject )
            .then( next, next );
    }

    // queue our ajax request
    ajaxQueue1.queue( doRequest );

    // add the abort method
    promise1.abort = function( statusText ) {

        // proxy abort to the jqXHR if it is active
        if ( jqXHR1 ) {
            return jqXHR1.abort( statusText );
        }

        // if there wasn't already a jqXHR we need to remove from queue
        var queue1 = ajaxQueue1.queue(),
            index = $.inArray( doRequest, queue1 );

        if ( index > -1 ) {
            queue1.splice( index, 1 );
        }

        // and then reject the deferred
        dfd1.rejectWith( ajaxOpts.context || ajaxOpts, [ promise1, statusText, "" ] );
        return promise1;
    };

    return promise1;
};


var ajaxQueue2 = $({});

$.ajaxQueue2 = function( ajaxOpts ) {
    var jqXHR2,
        dfd2 = $.Deferred(),
        promise2 = dfd2.promise();

    // run the actual query
    function doRequest( next ) {
        jqXHR2 = $.ajax( ajaxOpts );
        jqXHR2.done( dfd2.resolve )
            .fail( dfd2.reject )
            .then( next, next );
    }

    // queue our ajax request
    ajaxQueue2.queue( doRequest );

    // add the abort method
    promise2.abort = function( statusText ) {

        // proxy abort to the jqXHR if it is active
        if ( jqXHR2 ) {
            return jqXHR2.abort( statusText );
        }

        // if there wasn't already a jqXHR we need to remove from queue
        var queue2 = ajaxQueue2.queue(),
            index = $.inArray( doRequest, queue1 );

        if ( index > -1 ) {
            queue2.splice( index, 1 );
        }

        // and then reject the deferred
        dfd2.rejectWith( ajaxOpts.context || ajaxOpts, [ promise2, statusText, "" ] );
        return promise2;
    };

    return promise2;
};
						   



	if(location.hostname=='cua.pre.summon.serialssolutions.com'||location.hostname=='cua.summon.serialssolutions.com'){
		$('div#availabilityService').remove();
		$(".thumbnail-frame img").each(function(){var c=$(this);c.attr("src",c.data("src"))});
		$('div.summary').empty();
		var positioncount=0;
		$("div.document").each(function(){
			
		
			if($(this).attr('type')=='Conference Proceeding'||$(this).attr('type')=='Audio Recording'||$(this).attr('type')=='Book'||$(this).attr('type')=='eBook'||$(this).attr('type')=='Video Recording'||$(this).attr('type')=='Dissertation'){
						 var idbook=$(this).attr('id');
						var itemtype= $(this).attr('type');
						 var sitem = $(this).find('div.metadata');
						 $(sitem).empty();
						 $(sitem).css("min-height","25px");
						 $(sitem).css('border','1px solid #E6E6E6');	
						 $(sitem).css('background','url("http://www.lib.cua.edu/common/summon/rta/ajax-summon.gif") no-repeat left');	
						
						//run two request queue
						if(isEven(positioncount) ){
							    var reqs = $.ajaxQueue2({
									  url: "http://www.lib.cua.edu/common/summon/rta/rta.php",
									  data: {id: idbook, itemtypes: itemtype},
									  cache: true,
									  timeout: 10000,
									  dataType: "jsonp",
									  error: function(){
										   $(sitem).css('background','none').html('<div class="content-type" style="width:95%;clear:both;float:none; margin-left:5px; margin-top:5px;margin-bottom:2px"><div style="clear:both"><div style="float:left;width:175px"><b>Error</b></div><div style="margin-left:160px">Could not retrive item status. Please click on the title link for more information.</div></div></div>');
										   
									  },
									  success: function(data) { 
										  var items = {};
										  var wrlc = [];
										  var cua =[];
										  var links=[];
										   var itembas = {};
										    $(sitem).css('background','none');
										 $.each(data, function(key,val) {
											libcode=val['library'];
											if(val['library']=='Catholic'){
												
											cua.push('<div style="clear:both"><div style="float:left;width:175px"><b>'+val['location']+'</b></div><div style="margin-left:160px">'+val['availability']+"<b>&nbsp;"+val['link']+"&nbsp;</b>"+val['callno']+'</div></div>'); 	
											
											}else{
												if(!$.isArray(items[libcode])) items[libcode]=[];
					
											if(val['priority']=='primary'){
											items[libcode].unshift("<b>"+val['location']+":</b>&nbsp;"+val['availability']+' ');
											itembas[libcode]=1;
											}else{
												if(val['priority']=='additional'){
												items[libcode].push("<span class='additional'>|-<b>"+val['location']+":</b>&nbsp;"+val['availability']+' </span>');	
												}else{
													if(itembas[libcode]==1){
													items[libcode].push("<span class='additional'>|-<b>"+val['location']+":</b>&nbsp;"+val['availability']+' </span>');
													}else{
													items[libcode].push("<b>"+val['location']+":</b>&nbsp;"+val['availability']+' ');
													}
												}
											}
											//make sure link exist before pushing
										//	if(val['link']!='')	 links.push(val['link']);
											if(val['request']== true && typeof val['requestbib'] == 'number') links.push('<a href="https://www.aladin.wrlc.org/Z-WEB/CLSReqForm?srcid=summon:RTA&bibid='+val['requestbib']+'">Request</a>');
											}						
											
									});//end each
										 
										var bhtml='<div class="content-type" style="width:95%;clear:both;float:none; margin-left:5px; margin-top:5px;margin-bottom:2px"><div class="format format_generic_sm format_book_sm"></div><div class="text">Book:';
										if (typeof cua !== 'undefined' && cua.length > 0) {
										bhtml+='This item is owned by CUA:</div><div class="cuaitem" style="width:95%;clear:both;float:none; margin-left:5px; margin-top:0px;margin-bottom:2px"><p style="margin-top:0px">'+cua.join('')+"</p></div>";
										} 
									
										  for(x in items){
												
												for (var i=0;i<items[x].length;i++)
												{
													wrlc.push(items[x][i]);
												
												}
																							
										  }
						if (typeof cua !== 'undefined' && cua.length > 0&&typeof wrlc !== 'undefined' && wrlc.length > 0) {
										bhtml+='<hr style="clear:both"/><div>';
										}
										
										if (typeof wrlc !== 'undefined' && wrlc.length > 0) {
											bhtml+='This item is available at these WRLC Libraries and more: &nbsp;';
											if(typeof links !== 'undefined' && links.length > 0){
												bhtml+=links[0];
											}
											
											
											
											bhtml+='</div><div class="wrlcitem" style="float:none;clear:both;margin-left:5px;margin-top:2px;margin-bottom:5px;margin-right:10px;padding-botton:10px;"><span style="text-indent:0px !important;clear:both;margin-left:5px;"><br/>'+wrlc.join(' <br/>')+"</span></div>";
										} 	 
										bhtml+='</div>';
										 $(sitem).html(bhtml);
									}//end success
						
							 });//end ajax
				
							
						}else{
							
							    var reqs = $.ajaxQueue1({
									  url: "http://www.lib.cua.edu/common/summon/rta/rta.php",
									  data: {id: idbook, itemtypes: itemtype},
									  cache: true,
									  timeout: 10000,
									  dataType: "jsonp",
									    error: function(){
										   $(sitem).css('background','none').html('<div class="content-type" style="width:95%;clear:both;float:none; margin-left:5px; margin-top:5px;margin-bottom:2px"><div style="clear:both"><div style="float:left;width:175px"><b>Error</b></div><div style="margin-left:160px">Could not retrive item status. Please click on the title link for more information.</div></div></div>');
									  },
									  success: function(data) { 
										  var items = {};
										  var wrlc = [];
										  var cua =[];
										  var links=[];
										   var itembas = {};
										    $(sitem).css('background','none');
										 $.each(data, function(key,val) {
											libcode=val['library'];
											if(val['library']=='Catholic'){
												
											cua.push('<div style="clear:both"><div style="float:left;width:175px"><b>'+val['location']+'</b></div><div style="margin-left:160px">'+val['availability']+"<b>&nbsp;"+val['link']+"&nbsp;</b>"+val['callno']+'</div></div>'); 	
											
											}else{
												if(!$.isArray(items[libcode])) items[libcode]=[];
					
											if(val['priority']=='primary'){
											items[libcode].unshift("<b>"+val['location']+":</b>&nbsp;"+val['availability']+' ');
											itembas[libcode]=1;
											}else{
												if(val['priority']=='additional'){
												items[libcode].push("<span class='additional'>|-<b>"+val['location']+":</b>&nbsp;"+val['availability']+' </span>');	
												}else{
													if(itembas[libcode]==1){
													items[libcode].push("<span class='additional'>|-<b>"+val['location']+":</b>&nbsp;"+val['availability']+' </span>');
													}else{
													items[libcode].push("<b>"+val['location']+":</b>&nbsp;"+val['availability']+' ');
													}
												}
											}
											//make sure link exist before pushing
											//if(val['link']!='')	 links.push(val['link']);
											if(val['request']== true && typeof val['requestbib'] == 'number') links.push('<a href="https://www.aladin.wrlc.org/Z-WEB/CLSReqForm?srcid=summon:RTA&bibid='+val['requestbib']+'">Request</a>');
											}						
											
									});//end each
										 
										var bhtml='<div class="content-type" style="width:95%;clear:both;float:none; margin-left:5px; margin-top:5px;margin-bottom:2px"><div class="format format_generic_sm format_book_sm"></div><div class="text">Book:';
										if (typeof cua !== 'undefined' && cua.length > 0) {
										bhtml+='This item is owned by CUA:</div><div class="cuaitem" style="width:95%;clear:both;float:none; margin-left:5px; margin-top:0px;margin-bottom:2px"><p style="margin-top:0px">'+cua.join('')+"</p></div>";
										} 
									
										  for(x in items){
												
												for (var i=0;i<items[x].length;i++)
												{
													wrlc.push(items[x][i]);
												
												}
																							
										  }
						if (typeof cua !== 'undefined' && cua.length > 0&&typeof wrlc !== 'undefined' && wrlc.length > 0) {
										bhtml+='<hr style="clear:both"/><div>';
										}
										
										if (typeof wrlc !== 'undefined' && wrlc.length > 0) {
											bhtml+='This item is available at these WRLC Libraries and more: &nbsp;';
											if(typeof links !== 'undefined' && links.length > 0){
												bhtml+=links[0];
											}
											
											
											
											bhtml+='</div><div class="wrlcitem" style="float:none;clear:both;margin-left:5px;margin-top:2px;margin-bottom:5px;margin-right:10px;padding-botton:10px;"><span style="text-indent:0px !important;clear:both;margin-left:5px;"><br/>'+wrlc.join(' <br/>')+"</span></div>";
										} 	 
										bhtml+='</div>';
										 $(sitem).html(bhtml);
									}//end success
								 
							 }).fail(function(){
								 $(sitem).css('background','none').html('<div class="content-type" style="width:95%;clear:both;float:none; margin-left:5px; margin-top:5px;margin-bottom:2px"><div style="clear:both"><div style="float:left;width:175px"><b>Error</b></div><div style="margin-left:160px">Could not retrive item status. Please click on the title link for more information.</div></div></div>');
							 });//end ajax
				
							
							
						}
						
						
						
				}
			positioncount=positioncount+1;
				});//end each div

	
	}//end if

	

});
