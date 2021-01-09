<?php

include "libs/simplehtmldom_1_9_1/simple_html_dom.php";

function get_css_files($html){
	$links = $html -> find('link');
	$css_files = [];
	foreach($links as $link){
		$css_file = $link -> getAttribute('href');
		if(strpos($css_file, '.css') !== false){
			$css_files[] = $css_file; 
		}
	}

	return $css_files;
}

function css_parse($css){
	$css = str_replace( ['{', '}'] , '', $css);
	$lines = explode("\n", $css);
	$clean_lines = [];
	foreach($lines as $line){
		$l = trim($line);
		if(!strlen($l)){
			continue;
		}

		$clean_lines[] = $l;
	}

	$result = [];
	$selector = 'unknown';
	foreach($clean_lines as $line){
		if($line[0] == '.' or $line[0] == '#'){
			$selector = $line;
			$result[$selector] = [];
			continue;
		}

		$result[$selector][] = $line;
	}

	return $result;
}

function start_point($file, $output_file){
	if(!file_exists($file)){
		die("File not exists");
	}

	$html = file_get_html($file);

	$css_files = get_css_files($html);
	$css = [];
	foreach($css_files as $css_file){
		$css = array_merge($css, css_parse(
			file_get_contents( str_replace( basename($file), $css_file, $file ) )
		));
	}

	foreach($css as $css_selector => $css_block){
		$elems = $html -> find($css_selector);
		foreach($elems as $elem){
			$elem -> setAttribute('style', implode(' ', $css_block));
		}
	}

	file_put_contents($output_file, $html);
}


// RUN
if(isset($argv[1]) and isset($argv[2]) and strlen($argv[1]) and strlen($argv[2])){
	start_point($argv[1], $argv[2]);
}