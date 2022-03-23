(function( $ ) {
	'use strict';
	$.migrateMute = true;
	$.migrateTrace = false;
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	var dateFormat = $('#date_format').val();
	$('input.date_picker').each(function () {
		var $this = $(this);
		var mySelf = $this.prop('id');
		console.log({$this});
		console.log(mySelf);
		var altField = '#' + mySelf + '_val';
		console.log('altField is: ' + altField);
		$this.attr("autocomplete", "off");
		$this.on('click', function(e) {
			e.preventDefault();
		});
		$this.datepicker({
			altField: altField,
			altFormat: 'yy-mm-dd',
			dateFormat: dateFormat
		});
	});

})( jQuery );
