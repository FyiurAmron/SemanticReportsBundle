<?php

namespace Eidsonator\ReportsBundle\lib\PhpReports;

use Eidsonator\ReportsBundle\lib\PhpReports\Report;
use Symfony\Component\HttpFoundation\Request;

abstract class ReportFormatBase
{
	public static function display(Report &$report, Request &$request) {

	}
	
	public static function prepareReport(Report $report) {

		$environment =  'main';//$_SESSION['environment'];
		//todo make this stateful?

		$macros = array();
		if(isset($_GET['macros'])) {
			$macros = $_GET['macros'];
		}

		$report = new Report($report->report, $macros, $environment, null, $report->getContainer(), $report->getController());

		return $report;
	}
}
