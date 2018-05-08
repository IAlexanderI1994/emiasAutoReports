<?php header( 'Content-Type: text/html; charset=utf-8' );
require_once( 'functions.php' );
if ( $_POST['license'] ) {
	activate_license( $_POST['license'] );
}
if (! check_license( $_COOKIE['license'] ) ) {
	$error_msg = "<h1 class='bg-danger'> ВНИМАНИЕ! Ваша лицензия не активирована!<a href='license.php'>Активировать</a></h1>";
}
global $paths;
if ( isset( $_GET['clear_all'] ) && (int) $_GET['clear_all'] > 0 ) {
	clear_folders( $_GET['clear_all'] );
}
$message = '';
if ( isset( $_GET['msg'] ) && strlen( $_GET['msg'] ) > 0 ) {
	$message = explode( '-', $_GET['msg'] );
	if ( $message[1] == 1 ) {
		$message = '<p class="bg-success">' . $message[0] . '</p>';
	} else {
		$message = '';
	}
}

if ( isset( $_GET['license_activate'] ) ) {

	$_GET['license_activate'] == 1 ? $message = '<h3 class="bg-success">' . 'Лицензия успешно активирована' . '</h3>' : $message = '<h3 class="bg-danger">' . 'Вы ввели некорректный код! Попробуйте еще раз, при потворном возниковении ошибки, обратитесь в техническую поддержку!' . '</h3>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title>Сбор отчетов ЕМИАС</title>

	<!-- Bootstrap -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<style>
		.glyphicon {
			font-size: 25px;
		}

	</style>
</head>
<body>
<div class="container">
	<!-- Классы navbar и navbar-default (базовые классы меню) -->
	<nav class="navbar navbar-default">
		<div class="container-fluid">
			<!-- Заголовок -->
			<div class="navbar-header">
				Меню
			</div>
			<!-- Основная часть меню (может содержать ссылки, формы и другие элементы) -->
			<div class="collapse navbar-collapse" id="navbar-main">
				<ul class="nav navbar-nav navbar-right">
					<!-- Ссылка -->
					<li><a href="/">Главная</a></li>
					<li><a href="license.php">Лицензия</a></li>
				</ul>

			</div>
		</div>
	</nav>
	<div class="row"><h2 class="text-center text-primary">Сбор отчетов ЕМИАС</h2></div>