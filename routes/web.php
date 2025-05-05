<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pdf-prueba', function () {
    $mpdf = new \Mpdf\Mpdf(
        [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 5,
            'margin_footer' => 5,
            'showWatermarkText' => true,
        ]
    );

    $htmlContent =  view('pdf.AcademicRecord.index')->render();
    $htmlHeader =  view('pdf.AcademicRecord._header')->render();
    $htmlFooter =  view('pdf.AcademicRecord._footer')->render();
    $mpdf->SetWatermarkText('VISTA PREVIA',  0.1);

    $mpdf->SetHTMLHeader($htmlHeader);
    $mpdf->SetHTMLFooter($htmlFooter);
    $mpdf->SetTitle('PDF de Prueba');

    $mpdf->WriteHTML($htmlContent);

    return response($mpdf->Output('', 'S'), 200)
        ->header('Content-Type', 'application/pdf');
});
