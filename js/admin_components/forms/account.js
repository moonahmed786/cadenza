$(document).on('click', '.account-item', function(event) {
	event.preventDefault();
	var self = $(this);
	var uid = self.attr('data-uid');
	var cuid = self.attr('data-currentuid');
	var email = self.attr('data-email');
	self.text('Loading...').parent().css('opacity', '0.3');
	$.ajax({
		url: '',
		type: 'POST',
		dataType: 'json',
		data: {uid: uid,currentuid:cuid,email:email},
	})
	.always(function(res) {
		window.location.replace(res.destination);
	});
	
});
