jQuery(function($){

	$('.bizpress-glossary .bizpress-glossary-header-before').click(function(e) {
		e.preventDefault();
		var id = $(this).parent().parent().data('id');
		var current_tab = parseInt($(this).parent().parent().data('selected')) + -1;
		if( current_tab == null || current_tab < 0 ){
			current_tab = 0;
		}
		change_glossary_display(id, current_tab);
		$(this).parent().parent().data('selected',current_tab);
	});
	$('.bizpress-glossary .bizpress-glossary-header-after').click(function(e) {
		e.preventDefault();
		var id = $(this).parent().parent().data('id');
		var current_tab = parseInt($(this).parent().parent().data('selected')) + 1;
		var last_item = parseInt($(this).parent().parent().data('last'));
		if( current_tab == null || current_tab > last_item ){
			current_tab = last_item;
		}
		change_glossary_display(id, current_tab);
		$(this).parent().parent().data('selected',current_tab);
	});

	$('.bizpress-glossary .bizpress-glossary-header-list-wrap .bizpress-glossary-header-list .bizpress-glossary-header-item').click(function(e) {
		e.preventDefault();
		var id = $(this).parent().parent().parent().parent().data('id');
		var current_tab = parseInt($(this).data('tab'));
		var last_item = parseInt($(this).parent().parent().parent().data('last'));
		if( current_tab == null || current_tab < 0 ){
			current_tab = 0;
		}
		if( current_tab == null || current_tab > last_item ){
			current_tab = last_item;
		}
		$('.bizpress-glossary .bizpress-glossary-header-list-wrap .bizpress-glossary-header-list .bizpress-glossary-header-item').removeClass('active');

		change_glossary_display(id, current_tab);
		$(this).parent().parent().parent().parent().data('selected',current_tab);
		$(this).addClass('active');
	});

	function change_glossary_display(glossary_id, changeTo = 0){
		$('.bizpress-glossary .bizpress-glossary-header-list-wrap .bizpress-glossary-header-list .bizpress-glossary-header-item.active').removeClass('active');
		$('.bizpress-glossary .bizpress-glossary-content .bizpress-glossary-components.active').removeClass('active');


		$('#'+glossary_id+' .bizpress-glossary-content .bizpress-glossary-components').each(function(index, el) {
			if( $(el).data('tab') == changeTo ){
				$(this).addClass('active');
			}
		});

		$('#'+glossary_id+' .bizpress-glossary-header-list-wrap .bizpress-glossary-header-list .bizpress-glossary-header-item').each(function(index, el) {
			if( $(el).data('tab') == changeTo ){
				$(this).addClass('active');
			}
		});

		
		var header_item_width = $('#'+glossary_id+' .bizpress-glossary-header-list-wrap .bizpress-glossary-header-list .bizpress-glossary-header-item').width() * 6.1;
		var header_width = $('#'+glossary_id+' .bizpress-glossary-header-list-wrap').width() - header_item_width;
		var calc_width = header_item_width * changeTo;

		if( calc_width > header_width){
			$('#'+glossary_id+' .bizpress-glossary-header-list-wrap .bizpress-glossary-header-list').css('transform','translateX(-'+calc_width+'px)');
		}
		else if(calc_width < header_width){
			$('#'+glossary_id+' .bizpress-glossary-header-list-wrap .bizpress-glossary-header-list').css('transform','translateX(0px)');

		}
		console.log("BizPress Glossary: "+glossary_id+" Tab: "+changeTo + " Width: "+header_width+" Item Width: "+header_item_width+" Calc Width: "+calc_width);
	}


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