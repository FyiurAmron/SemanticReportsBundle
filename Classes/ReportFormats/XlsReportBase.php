<?php

namespace Eidsonator\SemanticReportsBundle\Classes\ReportFormats;

use Symfony\Component\HttpFoundation\Request;

use Eidsonator\SemanticReportsBundle\lib\PhpReports\{
  ReportFormatBase,
  Report
};

abstract class XlsReportBase extends ReportFormatBase {

    public static function displayEx( Report &$report, Request &$request,
      string $excelVersion, string $mimeType, string $extension ) {

        $report->use_cache = true;

        $report->run();

        if ( !$report->options['DataSets'] ) {
            return;
        }

        $phpExcel = self::getExcelRepresentation( $report );

        $objWriter = \PHPExcel_IOFactory::createWriter( $phpExcel, $excelVersion );
        
        parent::defaultHeaders( $report, $mimeType, $extension );

        $objWriter->save( 'php://output' );
    }
    
    protected static function columnLetter( $c ) {
    
        $letter = '';
        $c = intval($c);
        if ($c <= 0) {
            return $letter;
        }

        while ($c != 0) {
            $p = ($c - 1) % 26;
            $c = intval(($c - $p) / 26);
            $letter = chr(65 + $p);// . $letter;
        }

        return $letter;
    }

    public static function getExcelRepresentation( &$report ) {
    
        $phpExcel = new \PHPExcel();

        $phpExcel->getProperties()->setCreator( 'PHP-Reports' )
                                     ->setLastModifiedBy( 'PHP-Reports' )
                                     ->setTitle( '' )
                                     ->setSubject( '' )
                                     ->setDescription( '' );

        foreach ( $report->options['DataSets'] as $i => $dataset ) {
            $phpExcel->createSheet( $i );
            self::addSheet( $phpExcel, $dataset, $i );
        }

        $phpExcel->setActiveSheetIndex(0);

        return $phpExcel;
    }

    public static function addSheet( $phpExcel, $dataset, $i ) {
        $rows = [];
        $row = [];
        $cols = 0;
        $first_row = $dataset['rows'][0];
        foreach ($first_row['values'] as $key => $value) {
            $row[] = $value->key;
            $cols++;
        }
        $rows[] = $row;
        $row = [];

        foreach ($dataset['rows'] as $r) {
            foreach ($r['values'] as $key => $value) {
                $row[] = $value->getValue();
            }
            $rows[] = $row;
            $row = [];
        }

        $phpExcel->setActiveSheetIndex( $i )->fromArray( $rows, null, 'A1' );
        
        $as = $phpExcel->getActiveSheet();

        $as->setAutoFilter( 'A1:'.self::columnLetter( $cols ).count( $rows ) );

        for ( $a = 1; $a <= $cols; $a++ ) {
            $as->getColumnDimension( self::columnLetter( $a ) )->setAutoSize( true );
        }

        if ( isset( $dataset['title'] ) ) {
            $as->setTitle( $dataset['title'] );
        }

        return $phpExcel;
    }

}
