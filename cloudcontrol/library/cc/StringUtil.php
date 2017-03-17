<?php
/**
 * Created by jensk on 17-3-2017.
 */

namespace library\cc;


class StringUtil
{
	/**
	 * Convert a string to url friendly slug
	 *
	 * @param string $str
	 * @param array  $replace
	 * @param string $delimiter
	 *
	 * @return mixed|string
	 */
	public static function slugify($str, $replace=array(), $delimiter='-') {
		if( !empty($replace) ) {
			$str = str_replace((array)$replace, ' ', $str);
		}

		$clean = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

		return $clean;
	}

	/**
	 * Selects the right font-awesome icon for each filetype
	 *
	 * @param $fileType
	 * @return string
	 */
	public static function iconByFileType($fileType) {
		$fileTypeIcons = array(
			'image' => 'file-image-o',
			'pdf' => 'file-pdf-o',
			'audio' => 'file-audio-o',
			'x-msdownload' => 'windows',
			'application/vnd.ms-excel' => 'file-excel-o',
			'application/msexcel' => 'file-excel-o',
			'application/xls' => 'file-excel-o',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'file-excel-o',
			'application/vnd.google-apps.spreadsheet' => 'file-excel-o',
			'application/msword' => 'file-word-o',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'file-word-o',
			'application/x-rar-compressed' => 'file-archive-o',
			'application/x-zip-compressed' => 'file-archive-o',
			'application/zip' => 'file-archive-o',
			'text' => 'file-text-o',
		);

		foreach ($fileTypeIcons as $needle => $icon) {
			if (strpos($fileType, $needle) !== false) {
				return $icon;
			}
		}

		return 'file-o';
	}

	/**
	 * Converts an amount of bytes to a human readable
	 * format
	 *
	 * @param $size
	 * @param string $unit
	 * @return string
	 */
	public static function humanFileSize($size,$unit="") {
		if( (!$unit && $size >= 1<<30) || $unit == "GB")
			return number_format($size/(1<<30),2)."GB";
		if( (!$unit && $size >= 1<<20) || $unit == "MB")
			return number_format($size/(1<<20),2)."MB";
		if( (!$unit && $size >= 1<<10) || $unit == "KB")
			return number_format($size/(1<<10),2)."KB";
		return number_format($size)." bytes";
	}

	/**
	 * @param $ptime
	 * @return string|void
	 */
	public static function timeElapsedString($ptime)
	{
		$etime = time() - $ptime;

		if ($etime < 1)
		{
			return '0 seconds';
		}

		$a = array( 365 * 24 * 60 * 60  =>  'year',
					30 * 24 * 60 * 60  =>  'month',
					24 * 60 * 60  =>  'day',
					60 * 60  =>  'hour',
					60  =>  'minute',
					1  =>  'second'
		);
		$a_plural = array( 'year'   => 'years',
						   'month'  => 'months',
						   'day'    => 'days',
						   'hour'   => 'hours',
						   'minute' => 'minutes',
						   'second' => 'seconds'
		);

		foreach ($a as $secs => $str)
		{
			$d = $etime / $secs;
			if ($d >= 1)
			{
				$r = round($d);
				return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
			}
		}
		return 0;
	}
}