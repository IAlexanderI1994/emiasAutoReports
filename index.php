<?php require_once( 'header.php' );?>
	<?= $message; ?>
	<table id="main_table" class="table table-bordered">
		<thead>
		<tr>
			<th class="text-center">№ задачи</th>
			<th>Загруженные файлы</th>
			<th>Дата изменения</th>
			<th class="text-center">Наличие файлов</th>
			<th class="text-center">Выбрать</th>
		</tr>
		</thead>
		<tbody>
		<?= folder_data_table(); ?>
		</tbody>
	</table>

	<div class="row col-xs-2 col-sm-2 col-sm-2 col-lg-2">
		<form method="POST" action="<?= home_url(1) ?>create_report.php">
			<div class="form-group">
				<label for="month">Отчетный месяц</label>
				<select class="form-control" name="month" id="month">
					<?= month_options(); ?>
				</select>
			</div>
			<input type="hidden" value="1" name="create_report">
			<button type="submit" <?= disable_btn(); ?>class="btn btn-primary">Сформировать отчет</button>
		</form>


	</div>
	<div class="row col-xs-3 col-sm-3 col-sm-3 col-lg-3 pull-right">
		<label><br></label>
		<button class="btn btn-danger clear-all" disabled="disabled">Очистить выбранные папки</button>
	</div>
<?php require_once( 'footer.php' );?>