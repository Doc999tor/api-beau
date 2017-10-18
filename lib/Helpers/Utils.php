<?php
namespace Lib\Helpers;

class Utils {
	public static function returnBytes ($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);

		switch($last) {
		    // The 'G' modifier is available since PHP 5.1.0
		    case 'g': $val *= 1024;
		    case 'm': $val *= 1024;
		    case 'k': $val *= 1024;
		}

		return $val;
	}

	public static function generateWord ($word_min_length = 2, $word_max_length = 12) {
		if (!mt_rand(0,4)) {return '';}
		$letters = ['א', 'ב', 'ג', 'ד', 'ה', 'ו', 'ז', 'ח', 'ט', 'י', 'כ', 'ל', 'מ', 'נ', 'ס', 'ע', 'פ', 'צ', 'ק', 'ר', 'ש', 'ת'];
		$word_length = mt_rand($word_min_length, $word_max_length);

		$name = '';
		for ($i=0; $i < $word_length; $i++) {
			$name .= $letters[mt_rand(0, count($letters)-1)];
		}
		return $name;
	}
	public static function generatePhrase ($q = '', $phrase_min_word_count = 1, $phrase_max_word_count = 6, $word_min_length = 2, $word_max_length = 10) {
		$words_count = mt_rand($phrase_min_word_count, $phrase_max_word_count);

		$phrase = [];
		if ($q && $word_max_length > mb_strlen($q)) {
			$word_max_length -= mb_strlen($q);
		}

		for ($i=0; $i < $words_count; $i++) {
			$phrase[$i] = self::generateWord($word_min_length, $word_max_length);
		}
		return $q . trim(implode(' ', $phrase));
	}

	public static function rand_with_average ($start = 0, $end = 1000, $average_end = 50, $average_probability = 0.1) {
		return mt_rand(0, 10) / 10 > $average_probability ? floor(mt_rand($start, $average_end)) : floor(mt_rand($start, $end));
	}
}