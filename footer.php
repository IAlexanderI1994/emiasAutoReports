</div>


<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>
<script>
	jQuery('#main_table input[type="checkbox"]').change(function () {
		var disabled = 1;
		jQuery('#main_table input[type="checkbox"]').each(function () {
			if (jQuery(this).is(':checked')) {
				disabled = 0;
			}
		});
		if (disabled == 0) {
			jQuery('.clear-all').removeAttr('disabled');
		}
		else {
			jQuery('.clear-all').attr('disabled', 'disabled');
		}
	});
	jQuery('.clear-all').bind('click', function () {
		var delete_numbers = [];
		var num_string = '';
		var answer = confirm('Вы уверены, что хотите очистить выбранные папки? Отменить это действие будет невозможно!');
		if (answer) {
			jQuery('#main_table input[type="checkbox"]').each(function () {
				if (jQuery(this).is(':checked')) {
					delete_numbers[delete_numbers.length] = jQuery(this).val();
				}
			});
			num_string = delete_numbers.join('-');
			location.href = '?clear_all=' + num_string;
		}
	});

</script>
</body>
</html>