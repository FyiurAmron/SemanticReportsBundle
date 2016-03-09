<?php

namespace Eidsonator\ReportsBundle\Classes\ReportFormats;

use Eidsonator\ReportsBundle\lib\PhpReports\ReportFormatBase;
use Eidsonator\ReportsBundle\lib\PhpReports\Report;
use Symfony\Component\HttpFoundation\Request;

class TableReportFormat extends ReportFormatBase
{
    public static function display(Report &$report, Request &$request)
    {
        $report->options['inline_email'] = true;
        $report->use_cache = true;
        $template = '@EidsonatorReports/Default/html/table.twig';
        $vars = [];
        try {
            $report->renderReportPage($template, $vars);
        } catch (\Exception $e) {
        }
        return ["template" => $template, "vars" => $vars];
    }
}
