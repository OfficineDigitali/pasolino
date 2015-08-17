function selectFirst(selector) {
	var first = $(selector).find('option:first').val();
	$(selector).val(first);
	return first;
}

function mutualShow(sibling, slug) {
	$(sibling).hide();
	$('#' + slug).show();
}

$(document).ready(function() {
	/*
		Initing profile selection for incoming projects
	*/
	$('.profile-select').hide();
	$('.profile-opts').hide();

	$('#group-select').change(function() {
		var slug = $(this).val();
		mutualShow('.profile-select', slug);
		var first = selectFirst('#' + slug);
		mutualShow('.profile-opts', first);
	});

	$('.profile-select').change(function() {
		var slug = $(this).val();
		mutualShow('.profile-opts', slug);
	});

	var first = selectFirst('#group-select');
	selectFirst('#' + first);
	$('.profile-select:first').show();
	$('.profile-opts:first').show();

	/*
		On form submit, clean up unrequired components from the DOM
	*/
	$('#newProject').submit(function() {
		var group = $('#group-select').val();
		var profile = $('#' + group).val();
		$('.profile-opts[id!=' + profile + ']').remove();
		return true;
	});

	/*
		Removing a project, with related confirmation
	*/
	$('.remove-project').click(function() {
		if(confirm("Are you absolutely sure??? Removing this project all related files will be cancelled, including the final rendering!!!")) {
			var id = $(this).closest('.row').find('input[name=project_id]').val();
			$.ajax('/projects/' + id, {
				type: 'DELETE',
				data: {
					_token: $('input[name=_token]').val()
				},
				complete: function() {
					location.reload();
				}
			});
		}
	});
});
