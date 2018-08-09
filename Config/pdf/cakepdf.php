<?php

CakePlugin::load('CakePdf', array('bootstrap' => true, 'routes' => true));

Configure::write('CakePdf', array(
	'engine' => 'CakePdf.DomPdf',
	// TODO: just engine and crypto keys are used!!, others just via constructor!!
));

Configure::write('CakePdfDomPdf', array(
	'options' => array(
	    'print-media-type' => false,
	    'outline' => false,
	    'dpi' => 96,
	    'isRemoteEnabled' => true,
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
