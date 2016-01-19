jQuery(document).ready(function($) {

	$('.acf-cleaner-delete').click(function(e){
		e.preventDefault();
		var self = $(this);

		$(this).html('Removing...');

		var data = {
			type: $(this).data('type'),
			key: $(this).data('key'),
			action: 'remove_meta_tags'
		};

		$.ajax({
			url: ajaxurl,
			data: data,
			type: 'POST',
			dataType: 'JSON',
			success: function(data){
				self.parents('tr').remove();
				location.reload();
			},
			error: function(error){

			}
		});
	});

});