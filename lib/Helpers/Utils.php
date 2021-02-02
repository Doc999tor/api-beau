<?php
namespace Lib\Helpers;

class Utils {
	public static $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];

	public static function returnBytes (string $val):int {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		$bytes = (int)substr($val, 0, -1);

		switch($last) {
		    // The 'G' modifier is available since PHP 5.1.0
		    case 'g': $bytes *= 1024;
		    case 'm': $bytes *= 1024;
		    case 'k': $bytes *= 1024;
		}

		return $bytes;
	}

	public static function generateWord (int $word_min_length = 2, int $word_max_length = 12) {
		if (!mt_rand(0,4)) {return '';}
		// $letters = ['א', 'ב', 'ג', 'ד', 'ה', 'ו', 'ז', 'ח', 'ט', 'י', 'כ', 'ל', 'מ', 'נ', 'ס', 'ע', 'פ', 'צ', 'ק', 'ר', 'ש', 'ת'];
		$word_length = mt_rand($word_min_length, $word_max_length);

		$name = '';
		for ($i=0; $i < $word_length; $i++) {
			$name .= self::$letters[mt_rand(0, count(self::$letters)-1)];
		}
		return $name;
	}
	public static function generatePhrase (
		string $q = '',
		int $phrase_min_word_count = 1,
		int $phrase_max_word_count = 6,
		int $word_min_length = 2,
		int $word_max_length = 10
	) {
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

	public static function rand_with_average (
		int $start = 0,
		int $end = 1000,
		int $average_end = 50,
		float $average_probability = 0.1
	) {
		return mt_rand(0, 10) / 10 > $average_probability ? floor(mt_rand($start, $average_end)) : floor(mt_rand($start, $end));
	}

	public static function getRandomAddress(): string {
		if (empty($addressHandler)) {
			$addressHandler = fopen($_SERVER['DOCUMENT_ROOT'] . '/metadata/addresses_israel.php', 'r');
		}

		$max_addresses = 48345;
		$line_counter = 1;
		$needed_line = mt_rand($line_counter, $max_addresses);

		while ($line_counter++ < $needed_line) { fgets($addressHandler); }

		$random_address = json_decode(fgets($addressHandler), true);

		return $random_address['settlement'] . ', ' . $random_address['street'] . ', ' . mt_rand(1, 100);
	}
}
