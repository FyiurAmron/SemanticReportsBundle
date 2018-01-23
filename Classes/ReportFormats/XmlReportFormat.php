<?php

namespace Eidsonator\SemanticReportsBundle\Classes\ReportFormats;

use Eidsonator\SemanticReportsBundle\lib\PhpReports\ReportFormatBase;
use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;
use Symfony\Component\HttpFoundation\Request;

class XmlReportFormat extends ReportFormatBase
{
    public static function display(Report &$report, Request &$request)
    {
        parent::defaultHeaders( $report, 'application/xml', '.xml' );

        $datasets = [];
        $dataset_format = false;

        if (isset($_GET['datasets'])) {
            $dataset_format = true;
            $datasets = $_GET['datasets'];
            // If all the datasets should be included
            if ($datasets === 'all') {
                $datasets = array_keys($report->options['DataSets']);
            } elseif (!is_array($datasets)) { // If just a single dataset was specified, make it an array
                $datasets = explode(',', $datasets);
            }
        } else {
            $i = 0;
            if (isset($_GET['dataset'])) {
                $i = $_GET['dataset'];
            } elseif (isset($report->options['default_dataset'])) {
                $i = $report->options['default_dataset'];
            }
            $i = intval($i);

            $datasets = [ $i ];
        }
        $vars = [
            'datasets' => $datasets,
            'dataset_format' => $dataset_format
        ];
        $template = '@SemanticReports/Default/xml/report.twig';
        $report->renderReportPage($template, $vars);
        
        return [ 'template' => $template, 'vars' => $vars ];
    }
}
