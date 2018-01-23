<?php

namespace Eidsonator\SemanticReportsBundle\Classes\ReportFormats;

use Symfony\Component\HttpFoundation\Request;

use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;

class XlsReportFormat extends XlsReportBase {

    public static function display( Report &$report, Request &$request ) {
        parent::displayEx( $report, $request,
            'Excel5', 'application/vnd.ms-excel', '.xls' );
    }
}
