
/*!
 * Product specifications plugin
 * Javascript codes loaded in admin area
 *
 * @package Product Specifications Plugin
 * @since   1.0
*/
/*!
 * jQuery outerHTML
 */
jQuery.fn.outerHTML = function(s) {
	return s ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
};

/*!
 * Animate.CSS
 */
jQuery.fn.extend({
    animateCss: function (animationName) {
        var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
        $(this).addClass('animated ' + animationName).on(animationEnd, function() {
            $(this).removeClass('animated ' + animationName);
        });
    }
});

function dwDismissAdminNotice(event, id) {
	event.preventDefault();
	var $notice = jQuery(event.target).parents('.notice');

	jQuery.ajax({
		method: 'POST',
		url: dwspecs_plugin.ajaxurl,
		data: {
			action: 'dw_dismiss_admin_notice',
			id: id
		},
		success: function(res) {
			$notice.remove();
		}
	});
}

/*!
 * jQuery Strip tags
 * Strips html tags from a string
 *
 * @author Am!n <www.dornaweb.com>
 */
jQuery.extend({
	stripTags : function(s) {
		return s ? s.replace(/(<([^>]+)>)/ig,"") : "";
	}
});

(function( $ ){
	/**
	 * modal definations
	 */

	window.globalmodal = new tingle.modal({
		footer: false,
		stickyFooter: false,
		closeMethods: ['overlay', 'button', 'escape'],
		closeLabel: "Close",
		beforeOpen: function beforeOpen() {}
	});

	$(function () {
		$('[data-modal][data-autoopen]').trigger('click');
	});

	$(document).on('click', '[data-modal]', function (e) {
		e.preventDefault();
		var $target = $($(this).data('modal'));

		if ($target.length) {
			window.globalmodal.setContent($target.html());
			$(document).trigger('modal_content_loaded');
			window.globalmodal.open();

			if ($(this).data('classes')) {
				$('.tingle-modal').addClass($(this).data('classes'));
			}
		}
	});


	/*!
	 * Tab Boxes by dornaweb
	 * @author Am!n - http://www.dornaweb.com
	 * @version 1.2.2
	 */
	$(document).on('ready dw_dynamic', function(){
		$(".tab-boxes").each(function() {
			var tabs = $(this);

			$("ul.tabs li",tabs).each(function() {
				if( $(this).hasClass("active") ) {

					/** Ajax load if not loaded already **/
					if( $(this).data("ajax-id") && $( $(this).attr("data-target") ).length === 0 ) {
						var ajax_id = $(this).data("ajax-id");

						$.ajax({
							method  : "GET",
							url     : ajax_url,
							data    : { action : "load_news_tab", newsid: ajax_id },
							success : function( msg ) {
								tabs.find(".tab-contents").append(msg);
								$(document).trigger('dwtabs-loaded', [true]);
							}
						});
					}

					/** Normal load **/
					else{
						$( $(this).attr("data-target") ).show();
						$(document).trigger('dwtabs-loaded', [true]);
					}
				}

				else {
					$( $(this).attr("data-target") ).hide();
				}
			});

			$("ul.tabs li:not('.exclude')",tabs).click( function() {
				var tab = $(this);
				var tabtar = tab.attr("data-target");

				if( !tab.hasClass("active") ) {
					$("ul.tabs li",tabs).removeClass("active");
					tab.addClass("active");
					$(".tab-content",tabs).hide();

					/** Ajax load **/
					if( tab.data("ajax-id") && $(tabtar).length === 0 ) {
						var ajax_id = tab.data("ajax-id");

						$.ajax({
							method  : "GET",
							url     : ajax_url,
							data    : { action : "load_news_tab", newsid: ajax_id },
							success : function( msg ) {
								tabs.find(".tab-contents").append(msg);
								$(document).trigger('dwtabs-loaded', [true]);
							}
						});
					}

					/** Normal load **/
					else {
						$(tabtar).fadeIn("medium");
						$(document).trigger('dwtabs-loaded', [true]);
					}
				}
			});
		});
	});

	/*!
	 * Sortable attr. groups
	*/
	var fixHelper = function(e, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());
		});

		return ui;
	};

	$(document).on('ready dw_dynamic', function(){
		$('.dpws-sortable').sortable({
			helper: fixHelper,
			cancel: ""
		});
	});

	/**
	 * Specification tables in products edit pages
	*/
	$(function(){
		var initial_val = $('select#spec_tables_list').val();
		$('select#spec_tables_list').on('change', function(e){
			var post_id   = $(this).data('postid');
			var loading   = document.getElementById('loading_svg').innerHTML;
			var container = $('#specifications_table_wrapper');

			if( $(this).val() !== 0 && $(this).val() !== '0' ) {
				if( ( initial_val == 0 || initial_val == '0' ) || window.confirm('Are you sure you want to switch table? all unsaved settings will be lost.') ){
					container.html( loading );
					$.post(
						dwspecs_plugin.ajaxurl,
						{
							action: 'dwps_load_table',
							specs_table: $(this).val(),
							post_id: post_id
						},
						function( response ){
							container.html( response.data || '' );

							setTimeout(function(){
								$(document).trigger('dw_dynamic', [true]);
							}, 250);
						}
					);
				}
				else{
					$(this).val( $.data(this, 'current') );
					return false;
				}
			} else {
				if( window.confirm('Are you sure you want to switch table? all unsaved settings will be lost.') ){
					container.html('');
				}
				else {
					$(this).val( $.data(this, 'current') );
					return false;
				}
			}

			initial_val = $(this).val();

			$.data(this, 'current', $(this).val());
		});


		$(document).on('change', '.dw-table-field-wrap .customvalue-switch', function(){
			var select = $(this).parents('li').find('select');
			var tinput = $(this).parents('li').find('input.select-custom');

			if( $(this).is(':checked') ) {
				select.prop('disabled', true);
				tinput.prop('disabled', false);
			} else {
				select.prop('disabled', false);
				tinput.prop('disabled', true);
			}
		});

		$(document).on('ready dw_dynamic', function(){
			$('.dw-table-field-wrap .customvalue-switch').change();
		});

	});


	/*!
	 * Handling Group/attribute CU( Create-Update )
	*/
	$(function(){
		$(document).on('click', 'a[data-dwpsmodal]', function(e){
			e.preventDefault();

			var template = document.getElementById('modify_form_template').innerHTML;
			var texts    = $.parseJSON( Mustache.render( document.getElementById('dwps_texts_template').innerHTML ) );
			var elem 	 = $(this);
			var is_add   = $( e.target ).is( $("#dpws_add_new_btn") ) ? true : false;

			if( elem.hasClass('edit') )
				var edit_id = elem.data('id') || false;
			else
				var edit_id = false;

			var modal_title = is_add ? texts.add_title : texts.edit_title;

			// Create add form modal
			if( is_add ) {
				window.globalmodal.setContent(Mustache.render( template ));
				window.globalmodal.open();

				setTimeout(function(){
					window._group = $('#attr_group').clone();
				}, 600);
			}

			// create edit form modal
			else {
				var data = {
					action : 'dwps_edit_form',
					id     : edit_id,
					type   : elem.data('type')
				};

				elem.css( 'opacity', '0.4' );
				$.get( dwspecs_plugin.ajaxurl, data, function(e){
					window.globalmodal.setContent(e);
					window.globalmodal.open();

					setTimeout( function(){
						$('#attr_type').change();
					}, 100 );

					setTimeout(function(){
						window._group = $('#attr_group').clone();
					}, 600);

					elem.css( 'opacity', '1' );
			    });

			}
		});

		/**
		 * Add/edit form ajax
		*/
		$(document).on('submit', '#dwps_modify_form', function(e){
			e.preventDefault();

			var form = $(this);
			var data = $(this).serializeArray();

			$.post(dwspecs_plugin.ajaxurl, data, function(response) {
				// append success/error message to end of the form
				form.find('.result-msg').remove();
				form.append( '<span class="result-msg"><span class="msg '+ (response.success ? 'success' : 'error') +'">' + response.data.message + '</span></span>' );

				// validation check
				if( !response.success ) {
					for( i = 0; i <= response.data.where.length; i++ ){
						form.find( response.data.where[i] ).addClass('validation-error');
					}
					return;
				}

				form.find('.validation-error').removeClass('validation-error');

				// Add handler
				$('#dwps_table_wrap').load( window.location.href + ' #dwps_table' );

				setTimeout(function(){
					window.globalmodal.close();
				}, 1000);
			});
		});

		// Load groups of specific tables
		$(function(){
			$(document).on('change', '#attr_table', function(){
				var _groups = window._group.html();
				var tables  = $.parseJSON( JSON.stringify( $(this).data('tables') )  );
				var groups  = $('#attr_group');
				var value   = $(this).val();

				if( $(this).val() != '' ) {
					var arr = $.map(tables, function( v, i ) {
						return ( v.table_id == value ? v : null );
					});

					var options = '';
					$.each( arr[0].groups, function( i, v ){
						options += '<option value="' + v.term_id + '">' + v.name + '</option>';
					} );

					groups.find('option:not(:first-child)').remove();
					groups.append( options );
				} else{
					if( typeof _groups !== 'undefined' ){
						groups.html( _groups );
					}
				}

			});
		});

		// Default value & options by field type
		var val_opt_form = function(){
			var elem 		  = $('#attr_type');
			var form 		  = elem.parents('form');
			var default_value = $('input#attr_default[data-initial]').val() || '';

			switch ( elem.val() ) {
				case 'text':
				default :
					$('#attr_values').prop('disabled', true).parent().hide();

					$('#default_value_wrap').html('<input type="text" name="attr_default" id="attr_default" value="'+ default_value +'">').parent().show();

					break;

				case 'select':
				case 'radio' :
					$('#attr_values').prop('disabled', false).parent().show();

					if( form.find('#attr_values').length ) {
					var values = new Array();

					if( form.find('#attr_values').val().length > 0 )
						values = form.find('#attr_values').val().split("\n");

						var defaults = '<select name="attr_default" id="attr_default">';
						defaults    += '<option value="">----</option>';
						for( i = 0; i < values.length; i++ ){
							var value    = values[ i ];
							var selected = default_value != '' && default_value == encodeURIComponent( value ) ? ' selected' : '';

							defaults += '<option value="'+ encodeURIComponent( value ) +'"'+ selected +'>' + value + '</option>';
						}
						defaults += '</select>';

						$('#default_value_wrap').html(defaults).parent().show();
					}

					break;

				case 'textarea' :
					$('#attr_values').prop('disabled', true).parent().hide();

					$('#default_value_wrap').html('<textarea name="attr_default" id="attr_default">'+ default_value +'</textarea>').parent().show();

					break;

				case 'true_false' :
					$('#attr_values').prop('disabled', true).parent().hide();
					var checked = default_value == 'yes' ? ' checked' : '';
					$('#default_value_wrap').html('<input type="checkbox" name="attr_default" id="attr_default"'+ checked +'>').parent().show();

				break;
			}
		}

		$(document).on('ready change', function(e) {
			if( e.type == 'ready' || $(e.target).is( $('#attr_type') ) ) val_opt_form();

		}).change();

		$(document).on('change', '#attr_type', function() {
			$('#attr_values').change(function(){
				$('#attr_type').change();
			});
		});

		/**
		* Bulk delete
		*/
		$(function(){
			$(document).on('change', '.dwps-table .check-column input', function(){
				var table = $(this).parents('.dwps-table');

				if( table.find('tbody').has('input[type=checkbox]:checked').length ) {
					$('#dpws_bulk_delete_btn').removeAttr("disabled");
				} else{
					$('#dpws_bulk_delete_btn').attr("disabled", "disabled");
				}
			});
		});

		/* Modal close button */
		$(document).on('click', '.js-modal-close', function(e){
			e.preventDefault();

			window.globalmodal.close();
		});

		/**
		 * Delete stuff ( single delete and bulk delete )
		*/
		$(document).on('click', '#dwps_table .delete, #dpws_bulk_delete_btn', function(e){
			e.preventDefault();
			var id = $(this).data('id');
			var template = $.parseJSON(  Mustache.render( document.getElementById('dwps_delete_template').innerHTML ) );

			if( $(this).is('#dpws_bulk_delete_btn') ){
				var id = $('input.dlt-bulk-group:checked').map(function(){return $(this).val();}).get();
			}


			if( !$(this).is('[disabled]') ){
				var confirm = window.confirm(template.modal.content);


				if (confirm) {
					var action = template.data.type == 'attribute' ? 'dwps_modify_attributes' : 'dwps_modify_groups';

					$.post(dwspecs_plugin.ajaxurl, {action : action, do: 'delete', id: id }, function(response) {
						console.log( response );

						if( response.success ) {
							$('#dwps_table_wrap').load( window.location.href + ' #dwps_table' );

							setTimeout(function(){
								window.globalmodal.close();
							}, 1000);
							return;
						}

						window.globalmodal.setContent('Could not delete the group');
						window.globalmodal.open();
					});
				}
			}
		});

		/**
		 * Re-arrange handler
		*/
		$(document).on('click', '#dwps_table .re-arange', function(e){
			e.preventDefault();
			var texts = $.parseJSON( Mustache.render( document.getElementById('dwps_texts_template').innerHTML ) );

			$.get( dwspecs_plugin.ajaxurl, { action: 'dwps_group_rearrange_form', id: $(this).data('id') }, function( response ){
				window.globalmodal.setContent(response);
				window.globalmodal.open();

				setTimeout( function(){
					$(document).trigger('dw_dynamic', [true]);
				}, 100 );
			} );


		});

		$(document).on('click', 'input[type="checkbox"][readonly]', function(event){
			event.preventDefault();
		});

		/**
		 * Table groups handler
		*/
		$(function(){
			var result_container = $('.table-groups-list');

			$(document).on('change', '.dwsp-meta-item input[type=checkbox]', function(e){
				var val = $(this).val();

				if( $(this).is(':checked') ) {
					var elem = '<li><input checked type="checkbox" name="groups[]" value="'+ val +'" readonly>' + $(this).parent().find('span').text() + '</li>';
					result_container.append( elem );
				} else{
					if( result_container.has('input[value='+ val +']').length ){
						result_container.find('input[value='+ val +']').parents('li').remove();
					}
				}
			});
		});

	});

	$('#dwps_import_data_form').submit(function(e) {
		e.preventDefault();

		var data = new FormData(this);

		$('#dwspecs_import_results').html('<div class="notice"><p>'+ dwspecs_plugin.i18n.importing_message + '</p></div>');

		$.ajax({
			type: 'POST',
			cache: false,
			contentType: false,
			processData: false,
			url: dwspecs_plugin.ajaxurl,
			data: data,
			success: function(res) {
				if (res.success) {
					$('#dwspecs_import_results').html('<div class="updated success"><p>'+ res.data.message + '</p></div>')

				} else {
					$('#dwspecs_import_results').html('<div class="error"><p>'+ res.data.message + '</p></div>')

				}
			},
			error: function() {
				$('#dwspecs_import_results').html('<div class="error"><p>'+ dwspecs_plugin.i18n.unknown_error + '</p></div>')
			}
		})
	})

})(jQuery);
