jQuery( function ( $ ) {

	$( '#ph_dismiss_notice_jupix_export_enquiries' ).click(function(e)
	{
		e.preventDefault();
		
		var data = {
			'action': 'propertyhive_dismiss_notice_jupix_export_enquiries'
		};

		$.post( ajaxurl, data, function(response) {
			$( '#ph_notice_jupix_export_enquiries' ).fadeOut();
		});
	});

});