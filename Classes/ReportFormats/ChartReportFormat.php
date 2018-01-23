<?php
namespace Eidsonator\SemanticReportsBundle\Classes\ReportFormats;

use Eidsonator\SemanticReportsBundle\lib\PhpReports\ReportFormatBase;
use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;
use Symfony\Component\HttpFoundation\Request;

class ChartReportFormat extends ReportFormatBase {

    public static function display( Report &$report, Request &$request ) {
        if ( !$report->options['has_charts'] ) {
            return;
        }

        //always use cache for chart reports
        //$report->use_cache = true;
        $template = '@SemanticReports/Default/html/chart_report.twig';
        $vars = [];
        $report->renderReportPage($template, $vars);

        return [ 'template' => $template, 'vars' => $vars ];
    }
}
