<?php
$paths   = array(
	'input/1',
	'input/3',
	'input/4',
);
$gp_name = 'ГП45';
$decr    = '19050394';
function date_reverse( $date ) {
	$arr = explode( '.', $date );

	return implode( '-', array_reverse( $arr ) );
}

function get_difference_in_days( $datetime1, $datetime2 ) { //

	$datetime1 = date_create( date_reverse( $datetime1 ) );


	$datetime2 = date_create( date_reverse( $datetime2 ) );
	$dDiff     = $datetime1->diff( $datetime2 );

	return $dDiff->days;
}

function get_difference( $datetime1 ) { //
	$datetime1 = explode( ' ', $datetime1 );
	$datetime1 = date_create( date_reverse( $datetime1[0] ) . $datetime1[1] . ':00:00' );

	$today     = Date( 'Y-m-d H:i:s' );
	$datetime2 = date_create( $today );
	$interval  = date_diff( $datetime2, $datetime1 );
	if ( $interval == 0 ) {
		return true;
	}
	$result = substr( $interval->format( '%R%a' ), 0, 1 );
	if ( $result == '+' ) {
		return true;
	} else {
		return false;
	}
}

function cross_array( $arr1, $arr2 ) {
	$cross_array = array();
	foreach ( $arr1 as $key => $value ) {
		$cross_array[$value] = $arr2[$key];
	}

	return $cross_array;

}

/**
 * @param array $field_names
 * @param int $start_column
 * @param int $end_column
 *
 * @return mixed
 */
function find_columns( $sheet, $field_names = array(), $start_column, $lastColumn, $row ) {
	$lastColumn++;
	$fields_with_letters = array();
	for ( $column = $start_column; $column != $lastColumn; $column++ ) {
		$cell = strval( $sheet->getCell( $column . $row ) );
		if ( in_array( $cell, $field_names ) ) {
			$fields_with_letters[$cell] = $column;
		}
	}
	asort( $fields_with_letters );

	return $fields_with_letters;
}

function file_force_download( $file ) {
	if ( file_exists( $file ) ) {
		// сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
		// если этого не сделать файл будет читаться в память полностью!
		if ( ob_get_level() ) {
			ob_end_clean();
		}
		// заставляем браузер показать окно сохранения файла
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=' . basename( $file ) );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $file ) );
		// читаем файл и отправляем его пользователю
		if ( $fd = fopen( $file, 'rb' ) ) {
			while ( !feof( $fd ) ) {
				print fread( $fd, 1024 );
			}
			fclose( $fd );
		}
		exit;
	}
}

function month_options() {
	$output = '';
	$months = array(
		'Январь',
		'Февраль',
		'Март',
		'Апрель',
		'Май',
		'Июнь',
		'Июль',
		'Август',
		'Сентябрь',
		'Октябрь',
		'Ноябрь',
		'Декабрь'

	);
	foreach ( $months as $month ) {
		$output .= '<option value="' . mb_strtolower( $month, 'UTF-8' ) . '">' . $month . '</option>';
	}

	return $output;

}

function get_folder_count_files() {
	global $paths;
	$result_array = array();
	foreach ( $paths as $dir ) {

		$fi                 = new FilesystemIterator( $dir, FilesystemIterator::SKIP_DOTS );
		$result_array[$dir] = iterator_count( $fi );

	}

	return $result_array;
}

function is_correct_files() {
	$correct_counter = 0;
	$result_array    = get_folder_count_files();
	foreach ( $result_array as $count ) {
		if ( $count > 0 ) {
			$correct_counter++;
		}

	}

	if ( $correct_counter == count( $result_array ) ) {
		return true;
	} else {
		return false;
	}

}

function disable_btn() {
	$disabled = '';
	if ( !is_correct_files() ) {
		$disabled = 'disabled="disabled"';
	}

	return $disabled;
}

function folder_data_table() {
	$output = '';

	global $paths;
	$completion = get_folder_count_files( $paths );
	foreach ( $completion as $dir => $count ) {
		if ( strlen( $dir ) > 3 ) {


			$number = explode( '/', $dir )[1];
			if ( $count > 0 ) {
				$count++;


				$output       .= '<tr>';
				$output       .= '<td class="text-center" style="vertical-align: middle; font-size: 20px" rowspan="' . $count . '"><strong>' . $number . '</strong></td>';
				$output       .= '</tr>';
				$check_result = '<td class="text-center" style="vertical-align: middle" rowspan="' . ( $count - 1 ) . '"><span class="glyphicon glyphicon-ok-circle text-success" aria-hidden="true"></span></td>';
				$checkbox     = '<td class="text-center" style="vertical-align: middle" rowspan="' . ( $count - 1 ) . '"><input type="checkbox" name="check" value="' . $number . '"></td>';
				foreach ( glob( $dir . '/*.*' ) as $filename ) {


					$output           .= '<tr>';
					$output           .= '<td>';
					$correct_filename = iconv( mb_detect_encoding( $filename, mb_detect_order(), true ), "UTF-8", $filename );
					$output           .= '<em>' . explode( '/', $correct_filename )[2] . '</em>';
					$output           .= '</td>';
					$output           .= '<td>';
					$output           .= '<em>' . date( "F d Y H:i:s.", filemtime( $filename ) ) . '</em>';
					$output           .= '</td>';
					if ( strlen( $check_result ) > 0 ) {
						$output .= $check_result;
						$output .= $checkbox;

						$check_result = '';
					}
					$output .= '</tr>';

				}
				$output .= '';
				$output .= '</tr>';


			} else {
				$output       .= '<tr>';
				$output       .= '<td class="text-center" style="vertical-align: middle; font-size: 20px" rowspan="' . 1 . '">' . $number . '</td>';
				$check_result = '<td class="text-center" style="vertical-align: middle"><span class="glyphicon glyphicon glyphicon-remove-circle
 text-danger" aria-hidden="true"></span></td>';
				$output       .= '<td>';
				$output       .= '<p class="text-danger"><strong>ОШИБКА: Файлы отсутствуют</strong></p>';
				$output       .= '</td>';
				$output       .= '<td>';
				$output       .= '<p class="text-danger"><strong>ОШИБКА: Файлы отсутствуют</strong></p>';
				$output       .= '</td>';
				$output       .= $check_result;
				$output       .= '<td class="text-center" style="vertical-align: middle" rowspan="' . ( $count - 1 ) . '"></td>';
				$output       .= '';
				$output       .= '</tr>';
			}
		}
	}

	return $output;
}

function clear_folders( $del_string ) {
	$del_array = array();
	if ( strlen( $del_string ) > 0 ) {
		$del_array = explode( '-', $del_string );
		if ( count( $del_array ) > 0 ) {
			foreach ( $del_array as $dir ) {
				array_map( 'unlink', glob( 'input/' . $dir . "/*" ) );
			}
			header( 'Location: /index.php?msg=Выбранные папки успешно очищены!-1' );

		}

	}

	return "<p class='bg-danger text-white'> Не выбраны папки для удаления!</p>";

}

function has_license() {
	if ( strlen( $_COOKIE['license'] ) ) {
		return true;
	} else {
		return false;
	}
}

function encode( $unencoded, $key ) {
	$newstr = '';
	$string = base64_encode( $unencoded );

	$arr = array();
	$x   = 0;
	while ( $x++ < strlen( $string ) ) {
		$arr[$x - 1] = md5( md5( $key . $string[$x - 1] ) . $key );
		$newstr      .= $arr[$x - 1][3] . $arr[$x - 1][6] . $arr[$x - 1][1] . $arr[$x - 1][2];
	}

	return $newstr;
}

function decode( $encoded, $key ) {
	$strofsym = "qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM=";
	$x        = 0;
	while ( $x++ <= strlen( $strofsym ) ) {
		$tmp     = md5( md5( $key . $strofsym[$x - 1] ) . $key );
		$encoded = str_replace( $tmp[3] . $tmp[6] . $tmp[1] . $tmp[2], $strofsym[$x - 1], $encoded );
	}

	return base64_decode( $encoded );
}

function validateDate( $date, $format = 'Y-m-d H:i:s' ) {
	$d = DateTime::createFromFormat( $format, $date );

	return $d && $d->format( $format ) == $date;
}

function activate_license( $license_code ) {
	if ( check_license( $license_code ) ) {
		setcookie( "license", $license_code, time() + 3600 * 24 * 30 * 3 );
		$license_activate_result = 1;
	} else {
		$license_activate_result = 0;

	}
	header( 'Location: license.php?license_activate=' . $license_activate_result );
}

function check_license( $license_code ) {
	global $decr;
	$expire = decode( $license_code, $decr );
	if ( validateDate( $expire, 'd.m.Y H' ) && get_difference( $expire ) ) {
		return true;
	} else {
		return false;
	}
}

function generate_license_code( $dt ) {
	global $decr;
	$code = '';
	if ( validateDate( $dt, 'd.m.Y H' ) ) {
		$code = encode( $dt, $decr );
	} else {
		$code = 'Что-то пошло не так!';
	}

	return $code;
}

function get_license_expire() {
	global $decr;
	$expire = decode( $_COOKIE['license'], $decr );

	return $expire . ":00";
}

function upFirstLetter( $str, $encoding = 'UTF-8' ) {
	return mb_strtoupper( mb_substr( $str, 0, 1, $encoding ), $encoding )
	       . mb_substr( $str, 1, null, $encoding );
}

function home_url( $need_port = 0 ) {
	$home_url = 'http://';
	$need_port === 1 ? $home_url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'] : $home_url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	return $home_url;
}

?>
