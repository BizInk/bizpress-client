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


	$('.bizpress_pagnation').show();
	$('.cxbc-posts-list .cxbc-posts-list-bottom').children('.cxbc-posts-list-page').hide();
	$('.cxbc-posts-list .cxbc-posts-list-bottom').children('.cxbc-posts-list-page').first().show();
	create_triggers();
	function create_triggers(){
		$('.bizpress_pagnation_link_page').click(function(e) {
			e.preventDefault();
			console.log("BisPress New Page: "+$(this).data('page'));
			$('.bizpress_pagnation').data('page',$(this).data('page'));
	
			$('.bizpress_pagnation_link_page').removeClass('active');
			$(this).addClass('active');
			$('.cxbc-posts-list .cxbc-posts-list-bottom').children('.cxbc-posts-list-page').each(function(i, el) {
				if( $(el).data('page') == $('.bizpress_pagnation').data('page') ) {
					$(el).show();
				} else {
					$(el).hide();
				}
			});
			draw_paganation($(this).data('page'),$('.bizpress_pagnation').data('totalpages'));
		});
	
		$('.bizpress_pagnation_link_prev').click(function(e) {
			e.preventDefault();
			let page = parseInt($('.bizpress_pagnation').data('page')) - 1;
			let totalpages = $('.bizpress_pagnation').data('totalpages');
			console.log("BisPress New Page: "+page);
			if(page > 0){
				$('.bizpress_pagnation').data('page',page);
				$('.bizpress_pagnation_link_page').removeClass('active');
				$('.bizpress_pagnation_link_page').each(function(i, el) {
					if( $(el).data('page') == page ) {
						$(el).addClass('active');
					}
				});
				$('.cxbc-posts-list .cxbc-posts-list-bottom').children('.cxbc-posts-list-page').each(function(i, el) {
					if( $(el).data('page') == page ) {
						$(el).show();
					} else {
						$(el).hide();
					}
				});
				draw_paganation(page,totalpages);
			}
		});
		$('.bizpress_pagnation_link_next').click(function(e) {
			e.preventDefault();
			let page = parseInt($('.bizpress_pagnation').data('page')) + 1;
			let totalpages = $('.bizpress_pagnation').data('totalpages');
			console.log("BisPress New Page: "+page);
			if(page <= totalpages){
				$('.bizpress_pagnation').data('page',page);
				$('.bizpress_pagnation_link_page').removeClass('active');
				$('.bizpress_pagnation_link_page').each(function(i, el) {
					if( $(el).data('page') == page ) {
						$(el).addClass('active');
					}
				});
				$('.cxbc-posts-list .cxbc-posts-list-bottom').children('.cxbc-posts-list-page').each(function(i, el) {
					if( $(el).data('page') == page ) {
						$(el).show();
					} else {
						$(el).hide();
					}
				});
				draw_paganation(page,totalpages);
			}
		});	
	}

	
	function draw_paganation(page,totalpages){
		$('.bizpress_pagnation').data('page',page);
		$('.bizpress_pagnation').data('totalpages',totalpages);
		$('.bizpress_pagnation_links').children().remove();
		$('.bizpress_pagnation_links').append('<a class="bizpress_pagnation_link bizpress_pagnation_link_prev" href="#prev" title="Previous">&lt;</a>');
		for (var i = 1; i <= totalpages; i++) {
			if(totalpages < 10){
				var isActive = "";
				if(i == page){
					isActive = " active";
				}
				$('.bizpress_pagnation_links').append('<a class="bizpress_pagnation_link bizpress_pagnation_link_page '+isActive+'" href="#page'+i+'" data-page="'+i+'" title="Page '+i+'">'+i+'</a>');
			}
			else{
				if(i == page || i == totalpages || i == page-1 || i == page+1 || i == page-2 || i == page+2){
					var isActive = "";
					if(i == page){
						isActive = " active";
					}
					$('.bizpress_pagnation_links').append('<a class="bizpress_pagnation_link bizpress_pagnation_link_page '+isActive+'" href="#page'+i+'" data-page="'+i+'" title="Page '+i+'">'+i+'</a>');
				}
				else if(i == page-3 || i == page+3){
					$('.bizpress_pagnation_links').append('<a class="bizpress_pagnation_link bizpress_pagnation_link_page" href="#page'+i+'" data-page="'+i+'" title="Page '+i+'">...</a>');
				}
			}
		}
		$('.bizpress_pagnation_links').append('<a class="bizpress_pagnation_link bizpress_pagnation_link_next" href="#next" title="Next">&gt;</a>');
		create_triggers();
	}

});