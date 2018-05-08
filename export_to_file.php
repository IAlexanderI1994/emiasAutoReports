<?php /* Template name: Экспорт-файл*/
header( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
header( "Last-Modified: " . gmdate( "D,d M YH:i:s" ) . " GMT" );
header( "Cache-Control: no-cache, must-revalidate" );
header( "Pragma: no-cache" );
header( "Content-type: application/vnd.ms-excel" );
header( "Content-Disposition: attachment; filename=calls.xls" );
// Подключаем класс для работы с excel
require_once( 'PHPExcel.php' );
// Подключаем класс для вывода данных в формате excel
require_once( 'PHPExcel/Writer/Excel5.php' );
// Создаем объект класса PHPExcel
$xls = new PHPExcel();
// Устанавливаем индекс активного листа
$xls->setActiveSheetIndex( 0 );
// Получаем активный лист
$sheet = $xls->getActiveSheet();
// Подписываем лист
$sheet->setTitle( 'Звонки' );

// Вставляем текст в ячейку A1
$sheet->setCellValue( "A1", 'Звонки за указанный период' );


// Объединяем ячейки
$sheet->mergeCells( 'A1:L1' );
$sheet->getStyle( 'A1' )->getAlignment()->setHorizontal( PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
$xls->getActiveSheet()->getColumnDimension( 'A' )->setAutoSize( true );
$xls->getActiveSheet()->getColumnDimension( 'B' )->setAutoSize( true );
$xls->getActiveSheet()->getColumnDimension( 'C' )->setAutoSize( true );
$xls->getActiveSheet()->getColumnDimension( 'D' )->setAutoSize( true );
$xls->getActiveSheet()->getColumnDimension( 'E' )->setAutoSize( true );
$xls->getActiveSheet()->getColumnDimension( 'F' )->setAutoSize( true );
$xls->getActiveSheet()->getColumnDimension( 'G' )->setAutoSize( true );
$xls->getActiveSheet()->getColumnDimension( 'H' )->setAutoSize( true );
$xls->getActiveSheet()->getColumnDimension( 'I' )->setAutoSize( true );
$xls->getActiveSheet()->getColumnDimension( 'J' )->setAutoSize( true );
$xls->getActiveSheet()->getColumnDimension( 'K' )->setAutoSize( true );
$xls->getActiveSheet()->getColumnDimension( 'L' )->setAutoSize( true );


$sheet->setCellValue( "A2", '№ п/п' );
$sheet->setCellValue( "B2", 'Дата и час вызова' );
$sheet->setCellValue( "C2", 'Фамилия, имя, отчество больного' );
$sheet->setCellValue( "D2", 'Год рождения, возраст' );
$sheet->setCellValue( "E2", 'Адрес' );
$sheet->setCellValue( "F2", 'Участок №' );
$sheet->setCellValue( "G2", 'По какому поводу сделан вызов' );
$sheet->setCellValue( "H2", 'Вызов первичный, повторный, посещение активное' );
$sheet->setCellValue( "I2", 'Дата выполнения вызова' );
$sheet->setCellValue( "J2", 'Кем выполнен вызов' );
$sheet->setCellValue( "K2", 'Подпись выполнившего вызов' );
$sheet->setCellValue( "L2", 'Диагноз' );
$sheet->setCellValue( "M2", 'Оказанная помощь, куда больной направлен(для неотложной помощи)' );


if ( $_GET['date'] && $_GET['move'] == 'search_call' && $_GET['search_call'] && $_GET['type'] ) {
	$date          = esc_sql( $_GET['date'] );
	$date_sql      = "&date=$date";
	$status        = esc_sql( $_GET['status'] );
	$status_sql    = "&status=$status";
	$type          = esc_sql( $_GET['type'] );
	$type_sql      = "&type=$type";
	$search_result = exp_to_excel_calls( $date, $status, $type ); //получаем массив
	$i             = 3;
	$counter       = 1;
	foreach ( $search_result['result'] as $call_data ) {
		$call_id = $call_data->ID; //id звонка

		$explode     = explode( '_', $call_data->post_title ); //получаем данные из заголовка звонка
		$user_id     = $explode[1]; //id текущего юзера
		$call_result = $explode[2]; //результат звонка
		// в соответствии с цифрой выводим текстовый эквивалент
		switch ( $call_result ) {
			case 1:
				$call_result = 'Вызов врача на дом';
				break;
			case 2:
				$call_result = 'Запись на прием';
				break;
			case 3:
				$call_result = 'Констатация смерти';
				break;
			case 4:
				$call_result = 'Отказ';
				break;
			case 5:
				$call_result = 'Вызов ОНМПВН';
				break;

		}
		$status = $explode[3];
		$class  = $status;//статус звонка
		switch ( $status ) {
			case 'red':
				$call_result = 'Не распределен';
				break;
			case 'yellow':
				$call_result = 'Распределен';
				break;
			case 'green':
				$split       = explode( ' ',  change_full_date( $call_data->post_date ) );
				$call_result = $split[0];
				break;


		}
		$user_data = get_patient_info( $user_id );
		$doctor_id = $call_data->doc_id; //получаем id доктора
		$doc_FIO   = $call_data->doc_FIO;
		$birthday  = $user_data['birthday'] . ', ' . get_age( $user_data['birthday'] );
		//счетчик строк
		$sheet->setCellValue( "A" . $i, $counter );
		$sheet->setCellValue( "B" . $i, change_full_date( $call_data->post_date ) );
		$sheet->setCellValue( "C" . $i, $user_data['Surname'] . ' ' . $user_data['Name'] . ' ' . $user_data['Midname'] );
		$sheet->setCellValue( "D" . $i, $birthday );
		$sheet->setCellValue( "E" . $i, $call_data->call_address );
		$sheet->setCellValue( "F" . $i, $call_data->call_sector );
		$sheet->setCellValue( "G" . $i, $call_data->call_comment );
		$sheet->setCellValue( "H" . $i, '' );
		$sheet->setCellValue( "I" . $i, $call_result );
		$sheet->setCellValue( "J" . $i, $doc_FIO );
		$sheet->setCellValue( "K" . $i, '' );
		$sheet->setCellValue( "L" . $i, '' );
		$sheet->setCellValue( "M" . $i, '' );

		$i ++;
		$counter ++;
	}
	$all_calls = $i;
	$xls->getActiveSheet()->setAutoFilter( 'A2:L2' );
	$xls->getActiveSheet()
	    ->getStyle( 'A3:A' . $all_calls )
	    ->getNumberFormat()
	    ->setFormatCode(
		    PHPExcel_Style_NumberFormat::FORMAT_NUMBER
	    );

}

// Выводим содержимое файла
$objWriter = new PHPExcel_Writer_Excel5( $xls );
$objWriter->save( 'php://output' );


?>