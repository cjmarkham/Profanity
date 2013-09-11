<?php

function get_api_key($key)
{
	return Db::$local->fetch_assoc("SELECT * FROM apikeys WHERE apikey=? LIMIT 1", array(
		$key
	));
}

function generate_key($url = false)
{
	$url = ($url !== false) ? $url : $_SERVER['HTTP_REFERER'];
	$key = base64_encode($key . uniqid());

	return $key;
}

class Sanitize
{

	protected $words = array(
		'shit',
		'fucker',
		'fuck',
		'wank',
		'fucking',
		'bastard',
		'cunt',
		'crap',
		'cock',
		'cunt',
		'dickhead',
		'prick',
		'damn',
		'twat',
		'dick',
		'arse',
		'piss',
		'slut',
		'bollock',
		'clunge',
		'twat'
	);
	
	// sounds like
	protected $words2 = array(
		'motherfucker',
		'shit',
		'fuk',
		'fukin',
		'wank',
		'fucking',
		'bastard',
		'ass',
		'asshole',
		'arsehole',
		'nigger',
		'nigga',
		'faggot',
		'tosser',
		'tossa',
		'bugger',
		'cunt'
	);

	protected $symbols = array('$', '0', '(', '@', '3', '!', '1', '5');
	protected $letters = array('s', 'o', 'c', 'a', 'e', 'i', 'i', 's');
	protected $spaces = '/(\s{1,}|\-{1,}|\_{1,}|\.{1,}|\|{1,})/';

	protected $str;
	protected $orig;
	protected $output;

	public function __construct($str)
	{
		$this->orig = $this->str = strtolower($str);

		$this->sounds_like();
		$this->direct_match();
		$this->space_replace();

		return $this->output();
	}

	protected function sounds_like()
	{
		// Get individual words
		$individual_words = explode(' ', $this->str);

		// loop through words
		foreach ($individual_words as $k => $word)
		{
			// replace concurrent letters
			// this doesnt work if there are spaces inbetween concurrent letters
			$word2 = preg_replace('/(.)\\1+/i', '$1', $word); 

			if (in_array($word2, $this->words))
			{
				$this->str = str_replace($word, $word2, $this->str);
			}

			// Replace if in sounds like array
			foreach ($this->words2 as $_word)
			{
				if (metaphone($word) === metaphone($_word))
				{
					$this->str = str_replace($word, str_repeat('*', strlen($word)), $this->str);
				}
			}
		}
	}

	public function direct_match()
	{
		// simple preg replace of words in array
		foreach ($this->words as $word)
		{
			$this->str = str_replace($word, str_repeat('*', strlen($word)), $this->str);
		}
	}

	public function space_replace()
	{
		// trim whitespace to replace swears seperated by spaces
		$this->str = preg_replace($this->spaces, '-', $this->str);

		foreach ($this->words as $word)
		{
			// split the word by the letter
			$letters = str_split($word);
			// then join them via a dash
			$dashed_word = implode('-', $letters);

			// check to see if this dashed word is in the string
			$this->str = preg_replace('/' . $dashed_word . '/i', str_repeat('*', strlen($word)), $this->str);
		}

		// replace the dashed and re-add spaced
		$this->str = str_replace('-', ' ', $this->str);
	}
	
	protected function output()
	{
		$this->output = array(
			'original'  => $this->orig,
			'sanitized' => $this->str
		);

		if ($this->str == $this->orig)
		{
			Db::$master->prex("INSERT IGNORE INTO words (word) VALUES (?)", array(
				$this->str
			));
		}
		else
		{
			return json_encode($this->output);
		}
	}


	public function __toString()
	{
		return json_encode($this->output);
	}
	
}