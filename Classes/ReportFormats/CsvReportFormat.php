<?php

namespace Eidsonator\SemanticReportsBundle\Classes\ReportFormats;

use Eidsonator\SemanticReportsBundle\lib\PhpReports\ReportFormatBase;
use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;
use Symfony\Component\HttpFoundation\Request;

class CsvReportFormat extends ReportFormatBase {

    public static function display( Report &$report, Request &$request ) {
    
        parent::defaultHeaders( $report, 'application/csv', '.csv' );

        $report->use_cache = true;

        $i = 0;
        if ( isset( $_GET['dataset'] ) ) {
            $i = $_GET['dataset'];
        } else if ( isset( $report->options['default_dataset'] ) ) {
            $i = $report->options['default_dataset'];
        }
        $i = intval($i);

        $vars = [ 'dataset' => $i ];
        $template = '@SemanticReports/Default/csv/report.twig';

        $report->renderReportPage( $template, $vars );
        
        return [ 'template' => $template, 'vars' => $vars ];
//		if(trim($data)) echo $data;
    }
}
