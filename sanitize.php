<?php
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
		$this->concurrent_letters();
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

	protected function concurrent_letters()
	{
		// Get individual words
		$individual_words = explode(' ', $this->str);

		// loop through words
		foreach ($individual_words as $k => $word)
		{
			// replace concurrent letters
			// this doesnt work if there are spaces inbetween concurrent letters
			$word2 = preg_replace('/(.)\\1+/i', '$1', $word); 

			// Check if the new trimmed word is in swear list
			if (in_array($word2, $this->words))
			{
				$this->str = str_replace($word, $word2, $this->str);
			}

			// if word length less than lowest word length in swear array
			// try and build a swear with neighbour characters
			$lengths = array_map('strlen', $this->words);

			if (strlen($word) < min($lengths))
			{
				$spaced_word = $individual_words[$k - 1] . ' ' . $word . ' ' . $individual_words[$k + 1];
				$word2 = preg_replace('/(.)\\1+/i', '$1', str_replace(' ', '', $spaced_word));

				// Check if the new word is in swear list
				if (in_array($word2, $this->words))
				{
					$this->str = str_replace(trim($spaced_word), str_repeat('*', strlen($word2)), $this->str);
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

		if ($this->str !== $this->orig)
		{
			return json_encode($this->output);
		}
	}


	public function __toString()
	{
		return json_encode($this->output);
	}
	
}