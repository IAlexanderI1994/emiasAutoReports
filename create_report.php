<?php header( 'Content-Type: text/html; charset=utf-8' );
require_once( 'PHPExcel.php' );
// Подключаем класс для вывода данных в формате excel
require_once( 'PHPExcel/Writer/Excel5.php' );
require_once( 'functions.php' );
include 'PHPExcel/IOFactory.php';

if ( isset( $_POST['create_report'] ) && is_correct_files() && check_license( $_COOKIE['license'] ) ) {
	$gp_name = 'ГП45';
	array_map( 'unlink', glob( "output/*" ) );
// Создаем объект класса PHPExcel
	$xls = new PHPExcel();

// 3 задача

	$needed_fields_third = array();
	$result_third        = array();
	$needed_fields_third = array(
		'Специальность' => 'C',
		'Время'         => 'G',
		'Врач'          => 'A',
	);
	$self_records        = array();
	foreach ( glob( 'input/3/*.*' ) as $filename ) {

//Поля для проверки (название -> номер поля)

		$alpha         = array(
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'X',
			'Y',
			'Z',
		);
		$inputFileName = $filename;

//  Read your Excel workbook
		try {
			$inputFileType = PHPExcel_IOFactory::identify( $inputFileName );
			$objReader     = PHPExcel_IOFactory::createReader( $inputFileType );
			$objPHPExcel   = $objReader->load( $inputFileName );
		} catch ( Exception $e ) {
			die( 'Error loading file "' . pathinfo( $inputFileName, PATHINFO_BASENAME ) . '": ' . $e->getMessage() );
		}
		$sheet         = $objPHPExcel->getSheet( 3 ); //НОМЕР ЛИСТА базовой выгрузки
		$highestRow    = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$rowData       = $sheet->rangeToArray( 'A' . 1 . ':' . $highestColumn . 1,
		                                       null,
		                                       true,
		                                       false );
//Избавляемся от внешнего массива
		$rowData = $rowData[0];

		asort( $needed_fields_third ); //Сортируем буквы по порядку(для того, чтобы можно было задать рейнжд)

		$doc_field    = $needed_fields_third['Врач'];
		$spec_field   = $needed_fields_third['Специальность'];
		$time_field   = $needed_fields_third['Время'];
		$first_letter = current( $needed_fields_third );
		$last_letter  = end( $needed_fields_third );
//  Loop through each row of the worksheet in turn
		$replace    = array( 'Аллергология и иммунология', 'Аллерголог и иммунолог', 'Врач-аллерголог-иммунолог', 'Инфекционные болезни', 'ия', 'Врач-' );
		$replace_on = array( 'Иммунолог', 'Иммунолог', 'Иммунолог', 'Инфекционист', '', '' );
		for ( $row = 5; $row <= $highestRow; $row++ ) {
			$doc_fio   = strval( $sheet->getCell( $doc_field . $row )->getValue() );
			$spec_name = strval( $sheet->getCell( $spec_field . $row )->getValue() );
			$time      = strval( $sheet->getCell( $time_field . $row )->getValue() );
			if ( is_string( $doc_fio ) && is_string( $spec_name ) && is_string( $time ) ) {

				$result_third[$spec_name][$doc_fio]['total_count'] ? $result_third[$spec_name][$doc_fio]['total_count']++ : $result_third[$spec_name][$doc_fio]['total_count'] = 1;
				if ( strlen( str_replace( ' ', '', $time ) ) == 0 ) {
					$result_third[$spec_name][$doc_fio]['untaken_count'] ? $result_third[$spec_name][$doc_fio]['untaken_count']++ : $result_third[$spec_name][$doc_fio]['untaken_count'] = 1;
				}
				else {
					$result_third[$spec_name][$doc_fio]['taken_count'] ? $result_third[$spec_name][$doc_fio]['taken_count']++ : $result_third[$spec_name][$doc_fio]['taken_count'] = 1;
				}
				$record_to = strval( $sheet->getCell( 'P' . $row )->getValue() );
				if ( trim( $doc_fio ) == trim( $record_to ) ) {
					$self_records[$spec_name][$doc_fio]['self_record'] ? $self_records[$spec_name][$doc_fio]['self_record']++ : $self_records[$spec_name][$doc_fio]['self_record'] = 1;
				}
				$result_third[$spec_name][$doc_fio]['untaken_percent'] = round( $result_third[$spec_name][$doc_fio]['untaken_count'] / $result_third[$spec_name][$doc_fio]['total_count'] * 100, 2 ) . '%';
			}
		}


	}

	$counter     = 2;
	$objPHPExcel = PHPExcel_IOFactory::load( "template/template.xlsx" );
	$objPHPExcel->setActiveSheetIndex( 3 );
	$title = 'п. 3_Несост приемы';
	$objPHPExcel->getActiveSheet()->setTitle( $title );
	foreach ( $result_third as $key => $value ) {
		foreach ( $value as $doc_name => $doc_data ) {
			$doc_data['taken_count'] ? true : $doc_data['taken_count'] = 0;
			$doc_data['untaken_count'] ? true : $doc_data['untaken_count'] = 0;
			$doc_data['untaken_percent'] ? true : $doc_data['untaken_percent'] = 0;
			$key = upFirstLetter( str_replace( $replace, $replace_on, trim( $key ) ) );
			$objPHPExcel->getActiveSheet()->SetCellValue( 'A' . $counter, $key );
			$objPHPExcel->getActiveSheet()->SetCellValue( 'B' . $counter, $doc_name );
			$objPHPExcel->getActiveSheet()->SetCellValue( 'C' . $counter, $doc_data['taken_count'] );
			$objPHPExcel->getActiveSheet()->SetCellValue( 'D' . $counter, $doc_data['untaken_count'] );
			$objPHPExcel->getActiveSheet()->SetCellValue( 'E' . $counter, $doc_data['untaken_percent'] );
			$objPHPExcel->getActiveSheet()->getStyle( 'A' . $counter . ':' . 'E' . $counter )->applyFromArray(
				array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					),
					'borders'   => array(
						'allborders' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
							'color' => array( 'rgb' => '000000' )
						)
					)

				)
			);
			$counter++;
		}
	}

	$objWriter = new PHPExcel_Writer_Excel2007( $objPHPExcel );
	$objWriter->save( 'output/result.xlsx' );
//Конец третьей проблемы


//Первая проблема
	$actual_specs        = array(
		'Аллергология и иммунология',
		'Гастроэнтерология',
		'Инфекционные болезни',
		'Кардиология',
		'Колопроктология',
		'Неврология',
		'Пульмонология',
		'Ревматология',
		'Эндокринология',
		'Онкология',
		'Гематология',
		'Нефрология'
	);
	$needed_fields_first = array(
		'Врач' => 'A',
	);
	$result_first        = array();
	foreach ( glob( 'input/1/*.*' ) as $filename ) {
		$inputFileName = $filename;
		try {
			$inputFileType = PHPExcel_IOFactory::identify( $inputFileName );
			$objReader     = PHPExcel_IOFactory::createReader( $inputFileType );
			$objPHPExcel   = $objReader->load( $inputFileName );
		} catch ( Exception $e ) {
			die( 'Error loading file "' . pathinfo( $inputFileName, PATHINFO_BASENAME ) . '": ' . $e->getMessage() );
		}
		$sheet         = $objPHPExcel->getSheet( 2 ); //НОМЕР ЛИСТА базовой выгрузки
		$highestRow    = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$rowData       = $sheet->rangeToArray( 'A' . 1 . ':' . $highestColumn . 1,
		                                       null,
		                                       true,
		                                       false );
//Избавляемся от внешнего массива
		$rowData = $rowData[0];

		asort( $needed_fields_first ); //Сортируем буквы по порядку(для того, чтобы можно было задать рейнжд)

		$doc_field    = $needed_fields_first['Врач'];
		$first_letter = current( $needed_fields_first );
		$last_letter  = end( $needed_fields_first );
		$spec_alpha   = array();

		$spec_alpha = find_columns( $sheet, $actual_specs, 'B', $highestColumn, 3 );

//  Loop through each row of the worksheet in turn

		for ( $row = 4; $row <= $highestRow; $row++ ) {

			$doc_fio = strval( $sheet->getCell( $doc_field . $row )->getValue() );
			foreach ( $spec_alpha as $key => $value ) {
				$records_count = (int)strval( $sheet->getCell( $value . $row )->getValue() );
				if ( $records_count > 0 && $doc_fio != 'Итого по специальностям' ) {
					$result_first[$key][$doc_fio]['total_records'] = $records_count;
					$result_first[$key]['full_records']            = (int)$sheet->getCell( $value . $highestRow )->getValue();
					$result_first[$key][$doc_fio]['percent']       = round( $result_first[$key][$doc_fio]['total_records'] / $result_first[$key]['full_records'] * 100, 2 ) . '%';

				}


			}

		}
	}

	$counter     = 2;
	$objPHPExcel = PHPExcel_IOFactory::load( "output/result.xlsx" );
	$objPHPExcel->setActiveSheetIndex( 2 );
	$title = 'п. 1_Выдача направлений';
	$objPHPExcel->getActiveSheet()->setTitle( $title );
	foreach ( $result_first as $spec_name => $doc_data_arr ) {
		foreach ( $doc_data_arr as $doc_name => $doc_data ) {
			if ( is_array( $result_first[$spec_name][$doc_name] ) ) {
				$replace    = array( 'Аллергология и иммунология', 'Аллерголог и иммунолог', 'Врач-аллерголог-иммунолог', 'Инфекционные болезни', 'ия', 'Врач-' );
				$replace_on = array( 'Иммунолог', 'Иммунолог', 'Иммунолог', 'Инфекционист', '', '' );
				$objPHPExcel->getActiveSheet()->SetCellValue( 'A' . $counter, 'Терапевт' );
				$objPHPExcel->getActiveSheet()->SetCellValue( 'B' . $counter, $doc_name );
				$objPHPExcel->getActiveSheet()->SetCellValue( 'C' . $counter, str_replace( $replace, $replace_on, trim( $spec_name ) ) );
				$objPHPExcel->getActiveSheet()->SetCellValue( 'D' . $counter, $doc_data['total_records'] );
				$objPHPExcel->getActiveSheet()->SetCellValue( 'E' . $counter, $result_first[$spec_name]['full_records'] );
				$objPHPExcel->getActiveSheet()->SetCellValue( 'F' . $counter, $doc_data['percent'] );
				$objPHPExcel->getActiveSheet()->getStyle( 'A' . $counter . ':' . 'F' . $counter )->applyFromArray(
					array(
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						),
						'borders'   => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN,
								'color' => array( 'rgb' => '000000' )
							)
						)

					)
				);
				$counter++;
			}
		}
	}

	$objWriter = new PHPExcel_Writer_Excel2007( $objPHPExcel );
	$objWriter->save( 'output/result.xlsx' );
//Конец первой проблемы

// Начало четвертой проблемы

	$needed_fields_fourth = array(
		'Дата и время создания'   => 'D',
		'Дата и время записи'     => 'J',
		'Врач'                    => 'A',
		'Специальность'           => 'B',
		'Выдан талон/направление' => 'C'
	);
	$result_fourth        = array();

	foreach ( glob( 'input/4/*.*' ) as $filename ) {
		$inputFileName = $filename;
		try {
			$inputFileType = PHPExcel_IOFactory::identify( $inputFileName );
			$objReader     = PHPExcel_IOFactory::createReader( $inputFileType );
			$objPHPExcel   = $objReader->load( $inputFileName );
		} catch ( Exception $e ) {
			die( 'Error loading file "' . pathinfo( $inputFileName, PATHINFO_BASENAME ) . '": ' . $e->getMessage() );
		}
		$sheet         = $objPHPExcel->getSheet( 2 ); //НОМЕР ЛИСТА базовой выгрузки
		$highestRow    = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$rowData       = $sheet->rangeToArray( 'A' . 1 . ':' . $highestColumn . 1,
		                                       null,
		                                       true,
		                                       false );
//Избавляемся от внешнего массива
		$rowData = $rowData[0];

		asort( $needed_fields_fourth ); //Сортируем буквы по порядку(для того, чтобы можно было задать рейнжд)
		$doc_field        = $needed_fields_fourth['Врач'];
		$start_date_field = $needed_fields_fourth['Дата и время создания'];
		$end_date_field   = $needed_fields_fourth['Дата и время записи'];
		$type_field       = $needed_fields_fourth['Выдан талон/направление'];
		$spec_field       = $needed_fields_fourth['Специальность'];
		$first_letter     = current( $needed_fields_fourth );
		$last_letter      = end( $needed_fields_fourth );
//  Loop through each row of the worksheet in turn
		$max_interval = 0;
		for ( $row = 5; $row <= $highestRow; $row++ ) {

			$doc_fio = strval( $sheet->getCell( $doc_field . $row )->getValue() );
			if ( strlen( $doc_fio ) < 2 ) {
				continue;
			}
			$start_date = explode( ' ', strval( $sheet->getCell( $start_date_field . $row )->getValue() ) );
			$start_date = $start_date[0];
			//Перевод из экселевского формата даты в нормальный UNIX
			$UNIX_DATE  = ( $start_date - 25569 ) * 86400;
			$start_date = gmdate( "d.m.Y", $UNIX_DATE );

			$end_date    = explode( ' ', strval( $sheet->getCell( $end_date_field . $row )->getValue() ) );
			$end_date    = $end_date[0];
			$spec_name   = str_replace( $replace, $replace_on, strval( $sheet->getCell( $spec_field . $row )->getValue() ) );
			$type_action = strval( $sheet->getCell( $type_field . $row )->getValue() );
			if ( $type_action == 'Выдан талон' ) {
				$interval = get_difference_in_days( $start_date, $end_date );
				if ( $interval > $result_fourth[$spec_name]['max_interval'] ) {
					$result_fourth[$spec_name]['max_interval'] = $interval;
				}
			}
			else {
				$interval = '-1';
			}

			$result_fourth[$spec_name][$doc_fio][$interval] ? $result_fourth[$spec_name][$doc_fio][$interval]++ : $result_fourth[$spec_name][$doc_fio][$interval] = 1;

		}


	}

	$days_arr  = array();
	$alpha_arr = array(
		'C',
		'D',
		'E',
		'F',
		'G',
		'H',
		'I',
		'J',
		'K',
		'L',
		'M',
		'N',
		'O',
		'P',
		'Q',
		'R',
		'S',
		'T',
		'U',
		'V',
		'W',
		'X',
		'Y',
		'Z',
		'AA',
		'AB',
		'AC',
		'AD',
		'AE',
		'AF',
		'AG',
		'AH',
		'AI',
		'AJ',
		'AK',
		'AL',
		'AM',
		'AN',
		'AO',
		'AP',
		'AQ',
		'AR',
		'AS',
		'AT',
		'AU',
		'AV',
		'AW',
		'AX',
		'AY',
		'AZ',
		'BA',
		'BB',
		'BC',
		'BD',
		'BE',
		'BF',
		'BG',
		'BH',
		'BI',
		'BJ',
		'BK',
		'BL',
		'BM',
		'BN',
		'BO',
		'BP',
		'BQ',
		'BR',
		'BS',
		'BT',
		'BU',
		'BV',
		'BW',
		'BX',
		'BY',
		'BZ',
		'CA',
		'CB',
		'CC',
		'CD',
		'CE',
		'CF',
		'CG',
		'CH',
		'CI',
		'CJ',
		'CK',
		'CL',
		'CM',
		'CN',
		'CO',
		'CP',
		'CQ',
		'CR',
		'CS',
		'CT',
		'CU',
		'CV',
		'CW',
		'CX',
		'CY',
		'CZ',
	);
	foreach ( $result_fourth as $spec => $spec_data ) {
		if ( is_array( $spec_data ) ) {
			foreach ( $spec_data as $doc_fio => $records ) {
				if ( is_array( $records ) ) {
					if ( !$result_fourth[$spec][$doc_fio]['-1'] ) {
						$result_fourth[$spec][$doc_fio]['-1'] = '';
						$days_arr[$spec][]                    = '-1';
					}


					ksort( $result_fourth[$spec][$doc_fio] );
					foreach ( $records as $day => $count ) {
						$days_arr[$spec][] = $day;

					}
				}

			}
		}
	}

	foreach ( $days_arr as $spec => $days ) {
		$days_arr[$spec] = array_unique( $days_arr[$spec] );
		sort( $days_arr[$spec] );
		$days_arr[$spec] = cross_array( $days_arr[$spec], $alpha_arr );
	}


	$counter     = 0;
	$objPHPExcel = PHPExcel_IOFactory::load( "output/result.xlsx" );
	$objPHPExcel->setActiveSheetIndex( 4 );
	$title = 'п. 4_Повтор. зап (по дням)';
	foreach ( $result_fourth as $spec_name => $doc_data_arr ) {
		$counter++;
		$objPHPExcel->getActiveSheet()->SetCellValue( 'D' . $counter, 'На какой день' );
		$objPHPExcel->getActiveSheet()->getStyle( 'D' . $counter )->getFont()->setBold( true );
		if ( count( $days_arr[$spec_name] ) > 1 ) {
			try {
				$objPHPExcel->getActiveSheet()->mergeCells( 'D' . $counter . ':' . end( $days_arr[$spec_name] ) . $counter );
			} catch ( Exception $e ) {
				echo "<pre>";
				echo $e->getMessage() . '<br>';
				echo "Специальность $spec_name и Последний элемент - " . end( $days_arr[$spec_name] ) . '<br>';
				print_r( $days_arr );
				echo "</pre>";

			}
		}


		$objPHPExcel->getActiveSheet()->getStyle( 'A' . $counter . ':' . end( $days_arr[$spec_name] ) . $counter )->applyFromArray(
			array(
				'fill'    => array(
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array( 'rgb' => '0070C0' )
				),
				'font'    => array(
					'bold'  => true,
					'color' => array( 'rgb' => 'FFFFFF' ),
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array( 'rgb' => '000000' )
					)
				)

			)
		);

		$counter++;
		$objPHPExcel->getActiveSheet()->SetCellValue( 'A' . $counter, 'Специальность' );
		$objPHPExcel->getActiveSheet()->SetCellValue( 'B' . $counter, 'Врач' );
		$objPHPExcel->getActiveSheet()->getStyle( 'A' . $counter . ':' . 'B' . $counter )->getFont()->setBold( true );
		$objPHPExcel->getActiveSheet()->getStyle( 'A' . $counter . ':' . end( $days_arr[$spec_name] ) . $counter )->applyFromArray(
			array(
				'fill'    => array(
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array( 'rgb' => 'DBE5F1' )
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array( 'rgb' => '000000' )
					)
				)
			)
		);


		foreach ( $days_arr[$spec_name] as $day => $alpha ) {

			$objPHPExcel->getActiveSheet()->SetCellValue( $alpha . $counter, $day );
			$objPHPExcel->getActiveSheet()->getStyle( $alpha . $counter )->getFont()->setBold( true );
		}
		$counter++;
		foreach ( $doc_data_arr as $doc_name => $doc_data ) {
			if ( is_array( $result_fourth[$spec_name][$doc_name] ) ) {
				$objPHPExcel->getActiveSheet()->SetCellValue( 'A' . $counter, $spec_name );
				$objPHPExcel->getActiveSheet()->SetCellValue( 'B' . $counter, $doc_name );
				foreach ( $doc_data as $day => $count ) {
					try {
						$objPHPExcel->getActiveSheet()->SetCellValue( $days_arr[$spec_name][$day] . $counter, $count );
					} catch ( Exception $e ) {
						echo "<h1>" . $spec_name . ' ' . $day . " " . $e->getMessage() . "</h1>";
					}

				}
				$objPHPExcel->getActiveSheet()->getStyle( 'A' . $counter . ':' . end( $days_arr[$spec_name] ) . $counter )->applyFromArray(
					array(
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN,
								'color' => array( 'rgb' => '000000' )
							)
						)
					)
				);
				$counter++;
			}
		}

	}

	$objWriter = new PHPExcel_Writer_Excel2007( $objPHPExcel );
	$objWriter->save( 'output/result.xlsx' );

//конец четвертой-a проблемы

	$result_fourth_b = array();
	foreach ( $result_third as $spec_name_third => $doc_data_arr_third ) {
		foreach ( $result_fourth as $spec_name_fourth => $doc_data_arr_fourth ) {
			if ( stripos( $spec_name_third, mb_strtolower( $spec_name_fourth, 'UTF-8' ) ) !== false ) {
				foreach ( $doc_data_arr_third as $doc_fio_third => $doc_data_third ) {
					foreach ( $doc_data_arr_fourth as $doc_fio_fourth => $doc_data_fourth ) {
						$new_doc_fio = explode( ' ', $doc_fio_third );
						if ( stripos( mb_strtolower( $doc_fio_fourth, 'UTF-8' ), mb_strtolower( $new_doc_fio[0], 'UTF-8' ) ) !== false ) {
							$self_records[$spec_name_third][$doc_fio_third]['self_record'] > 0 ? $result_fourth_b[$spec_name_fourth][$doc_fio_third]['self_record'] = $self_records[$spec_name_third][$doc_fio_third]['self_record'] : $result_fourth_b[$spec_name_fourth][$doc_fio_third]['self_record'] = 0;
							$result_fourth_b[$spec_name_fourth][$doc_fio_third]['taken_count'] = $doc_data_third['taken_count'];
							if ( $result_fourth_b[$spec_name_fourth][$doc_fio_third]['self_record'] * $result_fourth_b[$spec_name_fourth][$doc_fio_third]['taken_count'] > 0 ) {
								$result_fourth_b[$spec_name_fourth][$doc_fio_third]['self_percent'] = round( $result_fourth_b[$spec_name_fourth][$doc_fio_third]['self_record'] / $result_fourth_b[$spec_name_fourth][$doc_fio_third]['taken_count'] * 100, 2 ) . '%';

							}
							else {
								$result_fourth_b[$spec_name_fourth][$doc_fio_third]['self_percent'] = 0 . '%';
							}

						}
					}
				}

			}

		}

	}
	$counter     = 2;
	$objPHPExcel = PHPExcel_IOFactory::load( "output/result.xlsx" );
	$objPHPExcel->setActiveSheetIndex( 5 );
	$title = 'п. 4_Повтор. зап. (Уровень)';
	$objPHPExcel->getActiveSheet()->setTitle( $title );
	foreach ( $result_fourth_b as $spec_name => $doc_data_arr ) {
		foreach ( $doc_data_arr as $doc_name => $doc_data ) {
			if ( is_array( $result_fourth_b[$spec_name][$doc_name] ) ) {
				$doc_data['taken_count'] ? true : $doc_data['taken_count'] = 0;
				$doc_data['self_record'] ? true : $doc_data['self_record'] = 0;
				$doc_data['self_percent'] ? true : $doc_data['self_percent'] = 0;
				$objPHPExcel->getActiveSheet()->SetCellValue( 'A' . $counter, $spec_name );
				$objPHPExcel->getActiveSheet()->SetCellValue( 'B' . $counter, $doc_name );
				$objPHPExcel->getActiveSheet()->SetCellValue( 'C' . $counter, $doc_data['taken_count'] );
				$objPHPExcel->getActiveSheet()->SetCellValue( 'D' . $counter, $doc_data['self_record'] );
				$objPHPExcel->getActiveSheet()->SetCellValue( 'E' . $counter, $doc_data['self_percent'] );
				$objPHPExcel->getActiveSheet()->getStyle( 'A' . $counter . ':' . 'E' . $counter )->applyFromArray(
					array(
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						),
						'borders'   => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN,
								'color' => array( 'rgb' => '000000' )
							)
						)

					)
				);
				$counter++;
			}
		}
	}

	$objWriter = new PHPExcel_Writer_Excel2007( $objPHPExcel );
	$objWriter->save( 'output/Методические рекомендации_отчет ПРОБЛЕМЫ  ' . $_POST['month'] . ' 2-й уровень.xlsx' );
	file_force_download( 'output/Методические рекомендации_отчет ПРОБЛЕМЫ  ' . $_POST['month'] . ' 2-й уровень.xlsx' );

}
else {
	header( 'location: ' . home_url( 1 ) );
}


?>