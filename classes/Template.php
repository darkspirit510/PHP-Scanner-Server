<?php

class Template {

	protected $_file;
    protected $_data = array();
	
	public function __construct($filename) {
		$file = 'res/tpl/' . $filename . '.tpl';
		
		if(!file_exists($file)) {
			throw new Exception("");
		}
		
		$this -> _file = $file;
    }

    public function set($key, $value) {
        $this -> _data[$key] = $value;
        return $this;
    }

    public function render() {
		if (preg_match_all("/{{(.*?)}}/", $template, $m)) {
			foreach ($m[1] as $i => $varname) {
				$template = str_replace($m[0][$i], sprintf('%s', $varname), $template);
			}
		}
    }
}