<?php
namespace Eidsonator\ReportsBundle\Classes\ReportFormats;
use Eidsonator\ReportsBundle\lib\PhpReports\ReportFormatBase;
use Eidsonator\ReportsBundle\lib\PhpReports\Report;
use Symfony\Component\HttpFoundation\Request;
class ChartReportFormat extends ReportFormatBase {
	public static function display(Report &$report, Request &$request) {
		if(!$report->options['has_charts']) return;
		
		//always use cache for chart reports
		//$report->use_cache = true;
		$template = '@EidsonatorReports/Default/html/chart_report.twig';
		$vars = [];
		$report->renderReportPage($template, $vars);
		return ["template" => $template, "vars" => $vars];
	}
}
