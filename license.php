<?php require_once( 'header.php' ); ?>
<?php

if ( has_license() ) {
	?>
	<p> Ваша лицензия активна до: <strong><?php echo get_license_expire();?></strong></p>
	<?php
	$get_license_text = '<h3>Продлить лицензию</h3>';

} else {
	echo "<h2 class='bg-danger'>Сожалеем, Ваша лицензия истекла!</h2>";
	$get_license_text = '<h3>Активировать лицензию</h3>';
}
echo $get_license_text;
?>	
<?= generate_license_code('21.05.2018 15'); ?>
	<?= $message; ?>
	<div class="row col-xs-6 col-sm-6 col-sm-6 col-lg-6">
		<form method="POST" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
			<div class="form-group">
				<label for="license">Введите код лицензии: </label>
				<input type="text" class="form-control" name="license" id="license">
			</div>
			<input type="hidden" value="1" name="create_report">
			<button type="submit" <?= disable_btn(); ?>class="btn btn-primary">Активировать</button>
		</form>
	</div>
<?php require_once( 'footer.php' ); ?>