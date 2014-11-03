// Jquery with no conflict
(function($){
	$(document).ready(function($) {
		$( '#amznpmnts_settings_form input' ).bind( "change click select", function() {
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				$( '.updated.fade' ).css( 'display', 'none' );
				$( '#amznpmnts_settings_notice' ).css( 'display', 'block' );
			};
		});
		$( ' #amznpmnts_settings_form select').focus( function() {
			$( '.updated.fade' ).css( 'display', 'none' );
			$( '#amznpmnts_settings_notice' ).css( 'display', 'block' );
		});	
	});
})(jQuery);