<?php

namespace Eidsonator\ReportsBundle\lib\PhpReports;

use Eidsonator\ReportsBundle\lib\PhpReports\Report;

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
