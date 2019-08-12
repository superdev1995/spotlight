<?php

use Dompdf\Dompdf;
use Dompdf\Options;

$app->group('/export', function() use($app) {
	/***************************************************************************
	 * POST 'export/pdf'
	 *
	 * Export sent data to pdf
	 **************************************************************************/
	$this->post('/pdf', function ($req, $res, $args) use ($app) {
		$data = $req->getParsedBody();
		$options = new Options();
		$options->set('isRemoteEnabled', true);
		$dompdf = new Dompdf($options);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->loadHtml($data['html']);
		$dompdf->render();
		$dompdf->stream();
	})->setName('generatePDF');
});