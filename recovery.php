<?php
/**
 * Script for recovery files with icat
 *
 * @author Vitor Mattos vitor.mattos@gmail.com
 */

/**
 * Copyright (c) 2010, dealnews.com, Inc.
 * All rights reserved.
 * show a status bar in the console
 *
 * <code>
 * for($x=1;$x<=100;$x++){
 *
 *	 show_status($x, 100);
 *
 *	 usleep(100000);
 *
 * }
 * </code>
 *
 * @param   int	 $done   how many items are completed
 * @param   int	 $total  how many items are to be done total
 * @param   int	 $size   optional size of the status bar
 * @return  void
 *
 */
function show_status($done, $total, $size=30) {

	static $start_time;

	// if we go over our bound, just ignore it
	if($done > $total) return;

	if(empty($start_time)) $start_time=time();
	$now = time();

	$perc=(double)($done/$total);

	$bar=floor($perc*$size);

	$status_bar="\r[";
	$status_bar.=str_repeat("=", $bar);
	if($bar<$size){
		$status_bar.=">";
		$status_bar.=str_repeat(" ", $size-$bar);
	} else {
		$status_bar.="=";
	}

	$disp=number_format($perc*100, 0);

	$status_bar.="] $disp%  $done/$total";

	$rate = ($now-$start_time)/$done;
	$left = $total - $done;
	$eta = round($rate * $left, 2);

	$elapsed = $now - $start_time;

	$status_bar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";

	echo "$status_bar  ";

	flush();

	// when done, send a newline
	if($done == $total) {
		echo "\n";
	}

}

if(count($argv) <3) {
	echo "Informe o arquivo gerado pelo fls e o arquivo de imagem.\n" ;
	echo "o arquivo filelist é gerado com o seguinte comando:\n";
	echo "\tfls ipod.img -r -d -p -f fat > filelist.txt\n";
	echo "\tex: {$argv[0]} /path/to/filelist.txt /path/to/filesystem.img\n";
	exit();
}

$handle = @fopen($argv[1], "r");
if ($handle) {
	$count = exec('wc -l < ' . $argv[1]);
	$i = 0;
	while (($buffer = fgets($handle)) !== false) {
		show_status(++$i, $count, 30);

		$tmp = explode(":\t", $buffer);
		$tmp[0] = explode(' ', $tmp[0]);
		$tmp[1] = str_replace('$', '', $tmp[1]);
		$tmp[1] = str_replace("\n", '', $tmp[1]);

		if(in_array($tmp[0][0], array('d/d', '-/d'))) {
			exec('mkdir -p "'.$tmp[1].'"');
		} else {
			$last_line = system('icat -f ntfs -r -s '.$argv[2].' "'.$tmp[0][2].'" > "'.$tmp[1].'"', $retval);
			if($retval == 2) {
				exec('mkdir -p "'.substr($tmp[1], 0, strrpos($tmp[1], '/') ).'"');
				$last_line = system('icat -f ntfs -r -s '.$argv[2].' "'.$tmp[0][2].'" > "'.$tmp[1].'"', $retval);
				if($retval == 2)
					var_dump("\n".$last_line, $tmp[1]);
			}
		}
	}
	if (!feof($handle)) {
		echo "Error: unexpected fgets() fail\n";
	}
	fclose($handle);
}
