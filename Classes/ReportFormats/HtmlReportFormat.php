<?php

namespace Eidsonator\SemanticReportsBundle\Classes\ReportFormats;

use Eidsonator\SemanticReportsBundle\lib\PhpReports\ReportFormatBase;
use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;
use Symfony\Component\HttpFoundation\Request;

class HtmlReportFormat extends ReportFormatBase {

    public static function display( Report &$report, Request &$request ) {
        //determine if this is an asyncronous report or not
        //$report->async = !$request->query->has('content_only');
        /*
        if ($request->query->has('no_async')) {
            $report->async = false;
        }
        */
        $report->async = false;

        //if we're only getting the report content
        $template = ( $request->query->has( 'content_only' ) )
          ? '@SemanticReports/Default/html/content_only.twig'
          : '@SemanticReports/Default/html/report.twig';

        try {
            $additional_vars = [];
            if ($request->query->has('no_charts')) {
                $additional_vars['no_charts'] = true;
            }

            $report->renderReportPage($template, $additional_vars);

            return [ 'template' => $template, 'vars' => $additional_vars ];
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

            return [ 'template' => $template, 'vars' => $vars ];
        }
    }
}
