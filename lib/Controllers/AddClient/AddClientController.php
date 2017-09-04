<?php

namespace Lib\Controllers\AddClient;

use Lib\Controllers\Controller as Controller;

class AddClientController extends Controller {
	protected function generateWord ($word_min_length = 1, $word_max_length = 12) {
		if (!mt_rand(0,4)) {return '';}
		$letters = ['א', 'ב', 'ג', 'ד', 'ה', 'ו', 'ז', 'ח', 'ט', 'י', 'כ', 'ל', 'מ', 'נ', 'ס', 'ע', 'פ', 'צ', 'ק', 'ר', 'ש', 'ת'];
		$word_length = mt_rand($word_min_length, $word_max_length);
		$name = '';
		for ($i=0; $i < $word_length; $i++) {
			$name .= $letters[mt_rand(0, count($letters)-1)];
		}
		if ($name) {
			$name = ' ' . $name;
		}
		return $name;
	}
	protected function generatePhrase ($q = '', $phrase_min_word_count = 1, $phrase_max_word_count = 6, $word_min_length = 1, $word_max_length = 10) {
		$words_count = mt_rand($phrase_min_word_count, $phrase_max_word_count);

		$phrase = '';
		if (strlen($word_max_length) > strlen($q)) {
			$word_max_length -= strlen($q);
		}

		for ($i=0; $i < $words_count; $i++) {
			$phrase .= $this->generateWord($word_min_length, $word_max_length);
		}
		if ($q) {
			$phrase = $q . trim($phrase);
		}
		return trim($phrase);
	}

	protected function rand_with_average ($start = 0, $end = 1000, $average_end = 50, $average_probability = 0.1)	{
		return mt_rand(0, 10) / 10 > $average_probability ? floor(mt_rand($start, $average_end)) : floor(mt_rand($start, $end));
	}
}