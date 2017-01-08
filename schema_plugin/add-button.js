jQuery(document).ready(function($) {
	
	// For addding form sections...
	var button = $('#ajax-create-field');	
	var button_delete = $('#ajax-remove-field');
	
	var form = $('.plugin-form-1');
	var form_wrap = $('#plugin');
	
	var id = 1;
	var form_num = 1;
	button.click(function() {
		post();
	});
	
	button_delete.click(function() {
		remove();
	});
	
	function post() {
		 
		var data = {
			action : 'process_form',
		};

		jQuery.post(the_ajax_script.ajaxurl, data, function(response) {
			
			var object = $('.form-counter');	
			var value = $('.form-counter').attr('value'); // current value of the field	
			
			var int_val = parseInt(value);
			int_val++;
			object.attr('value', int_val);
			$('#add-new').submit();		
					
		});

		return false;
		
	}
	
	function remove() {
		 
		var data = {
			action : 'process_form',
		};

		jQuery.post(the_ajax_script.ajaxurl, data, function(response) {
			
			var object = $('.form-counter');	
			var value = $('.form-counter').attr('value'); // current value of the field	
			
			var int_val = parseInt(value);
			if (value > 1) {
				int_val--;
			} else {
				int_val = 1;
				alert("You can't remove any more fields!");
			}
			
			object.attr('value', int_val);
			$('#add-new').submit();		
					
		});

		return false;
		
	}

		
});