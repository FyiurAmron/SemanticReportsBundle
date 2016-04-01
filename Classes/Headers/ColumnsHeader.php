<?php

namespace Eidsonator\SemanticReportsBundle\Classes\Headers;

use Eidsonator\SemanticReportsBundle\lib\PhpReports\HeaderBase;
use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;

class ColumnsHeader extends HeaderBase
{
    public static function init($params, Report &$report)
    {
        foreach ($params['columns'] as $column => $options) {
            if (!isset($options['type'])) {
                throw new \Exception("Must specify column type for column $column");
            }
            $type = $options['type'];
            unset($options['type']);
            $report->addFilter($params['dataset'], $column, $type, $options);
        }
    }

    public static function parseShortcut($value) 
    {
        if (preg_match('/^[0-9]+\:/', $value)) {
            $dataset = substr($value, 0, strpos($value, ':'));
            $value = substr($value, strlen($dataset) + 1);
        } else {
            $dataset = 0;
        }

        $parts = explode(',', $value);
        $params = array();
        $i = 1;
        foreach ($parts as $part) {
            $type = null;
            $options = null;

            $part = trim($part);
            //special cases
            //'rpadN' or 'lpadN' where N is number of spaces to pad
            if (substr($part, 1, 3)==='pad') {
                $type = 'padding';

                $options = array(
                    'direction' => $part[0],
                    'spaces' => intval(substr($part, 4))
                );
            } elseif (substr($part, 0, 4)==='link') {
                //link or link(display) or link_blank or link_blank(display)
                //link(display) or link_blank(display)
                if (strpos($part, '(') !== false) {
                    list($type,$display) = explode('(', substr($part, 0, -1), 2);
                } else {
                    $type = $part;
                    $display = 'link';
                }

                $blank = ($type == 'link_blank');
                $type = 'link';

                $options = array(
                    'display'=>$display,
                    'blank'=>$blank
                );
            } elseif (in_array($part, array('html', 'raw'))) { //synonyms for 'html'
                $type = 'html';
            } elseif ($part === 'url') { //url synonym for link
                $type = 'link';
                $options = array(
                    'blank'=>false
                );
            } elseif ($part === 'bar') {
                $type = 'bar';
                $options = array();
            } elseif ($part === 'pre') {
                $type = 'pre';
            } else { //normal case
                $type = 'class';
                $options = array(
                    'class'=>$part
                );
            }

            $options['type'] = $type;

            $params[$i] = $options;

            $i++;
        }

        return array(
            'dataset'=>$dataset,
            'columns'=>$params
        );
    }
}
