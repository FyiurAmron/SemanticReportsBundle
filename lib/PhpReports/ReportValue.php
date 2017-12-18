<?php

namespace Eidsonator\SemanticReportsBundle\lib\PhpReports;

class ReportValue
{
	public $key;
	public $i;
	
	public $original_value;
	public $filtered_value;
	public $html_value;
	public $chart_value;
	
	public $is_html;
	public $type;
	
	public $class;
	
	public function __construct($i, $key, $value) {
		$this->i = $i;
		$this->key = $key;
		$this->original_value = $value;
		$this->filtered_value = is_string($value)? strip_tags($value) : $value;
		$this->html_value = $value;
		$this->chart_value = $value;
		
		$this->is_html = false;
		$this->class = '';
		
		$this->type = $this->_getType($this->filtered_value);
	}
	
	public function addClass($class) {
		$this->class = trim($this->class . ' ' .$class);
	}
	
	public function setValue($value, $html = false) {		
		if(is_string($value)) $value = trim($value);
		
		if($html) {
			$this->is_html = true;
			$this->html_value = $value;
		}
		else {
			$this->is_html = false;
			$this->filtered_value = is_string($value)? htmlentities($value) : $value;
			$this->html_value = $value;
		}
		
		$this->type = $this->_getType($this->filtered_value);
	}
	
	protected function _getType($value) {
		if(is_null($value)) return null;
		elseif(trim($value) === '') return null;
		elseif(preg_match('/^([$%(\-+\s])*([0-9,]+(\.[0-9]+)?|\.[0-9]+)([$%(\-+\s])*$/',$value)) return 'number';
		elseif(strtotime($value)) return 'date';
		else return 'string';
	}
	protected function _getDisplayValue($value, $html=false, $date=false) {
		$type = $this->_getType($value);
		
		if($type === null) {
			return ($html && $this->is_html) ? '&nbsp;' : null;
		}
		elseif($type === 'number') {
			return $value;
		}
		elseif($type === 'date') {
			return ($date) ? date($date,strtotime($value)) : $value;
		}
		elseif($type === 'string') {
            return $value; // utf8_encode($value);
		}
	}
	
	public function getValue($html = false, $date = false) {
		if($html) {
			$return = $this->_getDisplayValue($this->html_value, true, $date);

			if($this->is_html) {
				return $return;
			}
			else {
				return htmlentities($return);
			}
		}
		else {
			return $this->_getDisplayValue($this->filtered_value, false, $date);
		}
	}
	
	public function getKeyCollapsed() {
		return trim(preg_replace(array('/\s+/','/[^a-zA-Z0-9_]*/'),array('_',''),$this->key),'_');
	}
}
