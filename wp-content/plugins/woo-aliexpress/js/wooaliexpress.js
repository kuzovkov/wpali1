jQuery( document ).ready(function( $ ) {
	
	$('a[href*="click.aliexpress"]').each(function(index, element) {
        if(wax_object.tab == 'yes'){
			$(element).attr('target','_blank');
		}
	});
		
	$('a.single_add_to_cart_button, a[href*="click.aliexpress"]').live('click', function(e) {
		var id = 0;
		if( $(this).attr("data-id") ){
			id = $(this).attr("data-id");
		}else if( $('.sku').text() ) {
			id = $('a.single_add_to_cart_button').attr("data-id");
		}else if( $(this).attr("data-product_id") ){
			id = $(this).attr("data-product_id");
		}
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: wax_object.ajax_url,
			data: {
				'action' : 'wax_external_link_click',
				'data': id
			}
		});
	});
	
});

/*jQuery( document ).ready(function( $ ) {
	
	$('form[action*="click.aliexpress"]').each(function(index, element) {
        if(wax_object.tab == 'yes'){
			$(element).attr('target','_blank');
		}
	});
		
	$('input.single_add_to_cart_button, form[action*="click.aliexpress"]').live('click', function(e) {
		var id = 0;
		if( $(this).attr("data-id") ){
			id = $(this).attr("data-id");
		}else if( $('.sku').text() ) {
			id = $('a.single_add_to_cart_button').attr("data-id");
		}else if( $(this).attr("data-product_id") ){
			id = $(this).attr("data-product_id");
		}
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: wax_object.ajax_url,
			data: {
				'action' : 'wax_external_link_click',
				'data': id
			}
		});
	});
	
});*/