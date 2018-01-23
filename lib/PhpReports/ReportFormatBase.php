<?php

namespace Eidsonator\SemanticReportsBundle\lib\PhpReports;

use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;
use Symfony\Component\HttpFoundation\Request;

abstract class ReportFormatBase
{
	public static function display( Report &$report, Request &$request ) {
	}
	
	protected static function defaultHeaders( $report, $contentType, $extension ) {
	    $filename = preg_replace( [ '/[\s]+/', '/[^0-9a-zA-Z\-_\.]/' ], [ '_', '' ], $report->options['Name'] );

	    header( 'Content-type: '.$contentType );
	    header( 'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0' );
        header( 'Pragma: no-cache' );
        header( 'Expires: Thu, 19 Nov 1981 08:52:00 GMT' );
        header( 'Content-Disposition: attachment;filename="'.$filename.$extension.'"');
	}
	
	public static function prepareReport( Report $report ) {

		$environment =  'prod';
		//todo make this stateful?

		$macros = [];
		if( isset( $_GET['macros'] ) ) {
			$macros = $_GET['macros'];
		}

		$report = new Report( $report->report, $macros, $environment, null, $report->getContainer(), $report->getController() );

		return $report;
	}
}
