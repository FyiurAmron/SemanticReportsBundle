<?php

namespace Eidsonator\SemanticReportsBundle\Classes\ReportFormats;

use Symfony\Component\HttpFoundation\Request;
use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;

class XlsxReportFormat extends XlsReportBase {

    public static function display(Report &$report, Request &$request) {
        parent::displayEx( $report, $request,
            'Excel2007', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '.xlsx' );
    }
    
}
