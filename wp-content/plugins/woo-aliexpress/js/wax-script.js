jQuery(document).ready(function($) {
	
	$('a.wax-update-now').live('click', function(e) {
		e.preventDefault();
		var elm = $(this);
		var id = elm.attr("data-id");
		elm.text('Please wait!');
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: wax_object.ajax_url,
			data: {
				'action' : 'wax_update_product_now',
				'data': id
			},
			success: function(response) {
				elm.text(response.msg);
			}
		});
	});
	
	$("#commission-rate").slider({
		range: true,
		min: 0.00,
		max: 0.51,
		step: 0.01,
		values: [ $( "#minRange" ).val(), $( "#maxRange" ).val() ],
		slide: function( event, ui ) {
			$( "#minRange" ).val( ui.values[ 0 ] );
			$( "#maxRange" ).val( ui.values[ 1 ] );
		}
	});
	
	$("#price-rate").slider({
		range: true,
		min: 0,
		max: 10000,
		values: [ $( "#minPrice" ).val(), $( "#maxPrice" ).val() ],
		slide: function( event, ui ) {
			$( "#minPrice" ).val( ui.values[ 0 ] );
			$( "#maxPrice" ).val( ui.values[ 1 ] );
		}
	});
	
	$("#credit-rate").slider({
		range: true,
		min: 0,
		max: 10000,
		values: [ $( "#startCredit" ).val(), $( "#endCredit" ).val() ],
		slide: function( event, ui ) {
			$( "#startCredit" ).val( ui.values[ 0 ] );
			$( "#endCredit" ).val( ui.values[ 1 ] );
		}
	});

	$("#product_categories").chosen({placeholder_text_multiple: 'Please select categories', create_option: true, skip_no_results: true, width: "50%"});
	
	var ajaxQueue = $({});
	var currentRequest = null;
	$.ajaxQueue = function( ajaxOpts ) {
		var oldComplete = ajaxOpts.complete;
		ajaxQueue.queue(function( next ) {
			ajaxOpts.complete = function() {
				if ( oldComplete ) {
					oldComplete.apply( this, arguments );
				}
				next();
			};
			currentRequest = $.ajax( ajaxOpts );
		});
	};

	$('#product-filter').live('submit', function(e) {
		e.preventDefault();
		$('.media-item').remove();
		
		var field = $('#product-filter #the-list').find('tr > th > input[type="checkbox"]:checked');
		
		if( $('#bulk-action-selector-top').val() == 'import' || $('#bulk-action-selector-bottom').val() == 'import' && field.length != 0 ){
			
			var progress_html = '<div class="media-item">';
  				progress_html += '<div class="progress">';
    			progress_html += '<div class="percent">0%</div>';
    			progress_html += '<div style="width: 0%;" class="bar"></div>';
  				progress_html += '</div>';
				progress_html += '</div>';
				
			$('.bulkactions').after(progress_html);
			
			var progress = 100 / field.length;
			var total = 0;
			
			$.each(field, function (index, elem) {
				
				var url = $(this).parents('tr').find('td.productTitle');
				var element = $(this).parents('tr').find('.importer');
				
				element.attr('data-cat', $("#categorie_id option:selected").val());
				element.attr('data-cats', $("#product_categories").val());
				element.attr('data-tag', $("#product_tags").val());
				
				var arr = {};
				$.each(element.data(), function(name, value) {
					arr[name] = element.attr("data-" + name);
				});
				
				$.ajaxQueue({
					type: "POST",
					dataType: 'json',
					url: wax_object.ajax_url,
					async: true,
					cache: false,
					data: {
						'action' : 'wax_ajax_insert_product',
						'data': arr
					},
					success: function(response) {
						url.html("<strong>" + response.msg + "</strong>");
						url.css('background','orange');
						url.css('color','white');
						total = total + progress;
						$('.percent').text( Math.round(total) + '%' );
						$('.bar').css({'width': total + '%'});
					}
				});
				
			});
		}else{
			$('.bulkactions').after('<div class="media-item"><p>Please select some products & Import products from Bulk Actions!</p></div>');
		}
	});
	
	$('.column-imageUrl a').live('click', function(e) {
		e.preventDefault();
		var elm = $(this);
		var url = elm.parents('tr').find('td.productTitle');
		url.css('background','orange');
		url.css('color','white');
		url.html("Please wait...");
		
		elm.attr('data-cat', $("#categorie_id option:selected").val());
		elm.attr('data-cats', $("#product_categories").val());
		elm.attr('data-tag', $("#product_tags").val());
		
		var arr = {};
		$.each(elm.data(), function(name, value) {
			arr[name] = elm.attr("data-" + name);
		});
		
		$.ajax({
			type: "POST",
			dataType: 'json',
			url: wax_object.ajax_url,
			data: {
				'action' : 'wax_ajax_insert_product',
				'data': arr
			},
			success: function(response) {
				url.html("<strong>" + response.msg + "</strong>");
			}
		});
		return false;
	});
	
});