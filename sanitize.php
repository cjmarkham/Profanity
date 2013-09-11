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

function sanitize($str, $raw = false)
{
	$orig = $str;
	
	$str = strtolower($str);

	$words = array(
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
	$words2 = array(
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

	$symbols = array('$', '0', '(', '@', '3', '!', '1', '5');
	$letters = array('s', 'o', 'c', 'a', 'e', 'i', 'i', 's');
	$spaces = '/(\s{1,}|\-{1,}|\_{1,}|\.{1,}|\|{1,})/';

	$str = $symbol_replace = str_replace($symbols, $letters, $str);
	
	// Get individual words
	$check = explode(' ', $str);

	// loop through words
	foreach($check as $k => $c)
	{
		$c = trim($c);
		$c2 = preg_replace('/(.)\\1+/i', '$1', $c); 

		if (in_array($c2, $words))
		{
			$str = str_replace($c, $c2, $str);
		}

		// Replace if in sounds like array
		foreach ($words2 as $w)
		{
			if (metaphone($c) === metaphone($w))
			{
				$str = str_replace($c, str_repeat('*', strlen($c)), $str);
			}
		}
	}

	// simple preg replace of words in array
	foreach ($words as $w)
	{
		$str = str_replace($w, str_repeat('*', strlen($w)), $str);
	}

	// trim whitespace to replace swears seperated by spaces
	$str = preg_replace($spaces, '-', $str);

	// split swear array and add
	foreach ($words as $k => $w)
	{
		$_w = str_split($w);
		$_w = implode('-', $_w);

		$str = preg_replace('/'.$_w.'/i', str_repeat('*', strlen($w)), $str);
	}

	$str = str_replace('-', ' ', $str);
	
	$json = array(
		'original'  => $orig,
		'sanitized' => $str
	);

	if ($str == $orig)
	{
		Db::$master->prex("INSERT IGNORE INTO words (word) VALUES (?)", array($str));
	}
	else
	{
		if (!$raw) 
		{
			$json['assumed'] = $symbol_replace;
			echo '<fieldset><legend>Epic Fail <small>You failed to bypass the filter</small></legend>';
			echo '<pre style="text-align:left">';
			echo "
			{
			    \"original\"  : '{$json['original']}',
			    \"sanitized\" : '{$json['sanitized']}',
			    \"assumed\"   : '{$json['assumed']}'
			}
			";
			echo '</pre></fieldset>';
		}
		else
		{
			return json_encode($json);
		}
	}

	
}