jQuery(document).ready(function() {
	// Trigger the ML modal
	$('.field-medialibraryfield').on('click', function () {
		ml_source_input = this;
		localStorage.setItem('add-to-field', 'yes');
		$('#nav .ml-link').trigger('click');
	});

	// Add a file preview
	$('.field-medialibraryfield').each(function () {
		var self = $(this),
			file_path = self.find('input:first-of-type').val(),
			file_type = file_path.split('.')[1],
			allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

		if (allowed_types.includes(file_type)) {
			self.append('<div class="preview"><img src="' + Symphony.Context.get('root') + file_path + '" /></div>')
		}
	});
});