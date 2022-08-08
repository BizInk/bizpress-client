jQuery(function($){
	var height = 0;
	$('.cxbc-posts-list .cxbc-post-title').each(function(index, el) {
		if ( height < $(this).height() ) {
			height = $(this).height();
		}
	});
	$('.cxbc-posts-list .cxbc-post-title').height(height);

	var title_height = 0;
	$('.cxbc-posts-list .cxbc-post-title').each(function(index, el) {
		if ( title_height < $(this).outerHeight() ) {
			title_height = $(this).outerHeight();
		}
	});
	
	$('.cxbc-posts-list .cxbc-post-title span').height(title_height);
	
});

window.addEventListener('message', function (e) {
	if (e.data.hasOwnProperty("masterHeight")) {
		jQuery("#myframe").css("height", e.data.masterHeight);
		console.log("iFrame Height",e.data.masterHeight);      
	}
});

function setMasterHeight(iframe) {
	iframe.contentWindow.postMessage("masterHeight", "*");   
}