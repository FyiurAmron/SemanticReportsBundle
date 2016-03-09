<?php

namespace Eidsonator\SemanticReportsBundle\lib\PhpReports;

use Eidsonator\SemanticReportsBundle\lib\PhpReports\Report;

abstract class ReportTypeBase
{
	public static function init(Report &$report)
    {
		
	}
	
	public static function openConnection(&$report)
    {

	}
	
	public static function closeConnection(&$report)
    {
		
	}
	
	public static function getVariableOptions($params, &$report)
    {
		return array();
	}
	
	public static function run(&$report)
    {}
}
