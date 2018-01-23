<?php
namespace Eidsonator\SemanticReportsBundle\Classes\ReportFormats;

use Eidsonator\SemanticReportsBundle\lib\PhpReports\ReportFormatBase;
use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;
use Symfony\Component\HttpFoundation\Request;

class SqlReportFormat extends ReportFormatBase {
    public static function display(Report &$report, Request &$request)
    {
        parent::defaultHeaders( $report, 'application/sql', '.sql' );

        $vars = [];
        $template = '@SemanticReports/Default/sql/report.twig';
        $report->renderReportPage($template, $vars);
        
        return [ 'template' => $template, 'vars' => $vars ];
    }
}
