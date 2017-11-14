<?php

CakePlugin::load('CakePdf', array('bootstrap' => true, 'routes' => true));

Configure::write('CakePdf', array(
	'engine' => 'CakePdf.DomPdf',
	'options' => array(
	    'print-media-type' => false,
	    'outline' => false,
	    'dpi' => 96
	),
	'margin' => array(
	    'bottom' => 15,
	    'left' => 50,
	    'right' => 30,
	    'top' => 45
	),
	'orientation' => 'portrait',
	'download' => true
));

define('DOMPDF_ENABLE_REMOTE', true);
