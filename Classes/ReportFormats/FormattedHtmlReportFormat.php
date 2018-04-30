<?php

namespace Eidsonator\SemanticReportsBundle\Classes\ReportFormats;

use Eidsonator\SemanticReportsBundle\lib\PhpReports\ReportFormatBase;
use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;
use Symfony\Component\HttpFoundation\Request;

class FormattedHtmlReportFormat extends ReportFormatBase
{
    public static function display(Report &$report, Request &$request)
    {
        $report->async = false;
        $template = '@SemanticReports/Default/html/formatted_content_only.twig';        
        try {
            $additional_vars = [];
            /*
            if ($request->query->has('no_charts')) {
                $additional_vars['no_charts'] = true;
            }
            */

            $report->renderReportPage($template, $additional_vars);
            return ["template" => $template, "vars" => $additional_vars];
        } catch (\Exception $e) {
            // dump( $e );
            if ($request->query->has('content_only')) {
                $template = '@SemanticReports/Default/html/blank_page.twig';
            }

            $vars = [
                'title'=>$report->report,
                'header'=>'<h2>There was an error running your report</h2>',
                'error'=>$e->getMessage(),
                'content'=>"<h2>Report Query</h2>".$report->options['Query_Formatted'],
            ];

            return ["template" => $template, "vars" => $vars];

        }
    }
}
