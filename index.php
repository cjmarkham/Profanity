<?php
error_reporting(E_ALL & ~E_NOTICE);

function sanitize($str)
{
	$orig = $str;
	
	$words = array(
		'shit',
		'fucker',
		'fuck',
		'wank',
		'fucking',
		'bastard',
		'cunt',
		'bitch',
		'crap',
		'cock',
		'cunt',
		'dickhead',
		'prick',
		'damn',
		'twat',
		'dick',
		'arse'
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
		'nigga'
	);

	$symbols = array('$', '0', '(');
	$letters = array('s', 'o', 'c');

	echo '--Orignal String--<br />'.$str.'<br /><br />';
	$str = str_replace($symbols, $letters, $str);
	
	$check = explode(' ', $str);

	foreach($check as $k => $c)
	{
		$c = trim($c);

		if (in_array($c.$check[$k+1], $words))
		{
			$str = str_replace(array($c.' '.$check[$k+1]), array(str_repeat('*', strlen($c.$check[$k+1]))), $str);
		}

		foreach ($words2 as $w)
		{
			if (metaphone(trim($c)) === metaphone(trim($w)) || $percentage >= 75)
			{
				$str = str_replace($c, str_repeat('*', strlen($c)) ,$str);
			}
		}
	}
	
	// simple preg replace of words in array
	foreach ($words as $w)
	{
		$str = str_replace($w, str_repeat('*', strlen($w)), $str);
	}
	
	// trim whitespace to replace swears seperated by spaces
	echo '<br />--Trimming Spaces--<br />';

	$str = preg_replace('/\s{1,}/', '-', $str);

	foreach ($words as $k => $w)
	{
		$_w = str_split($w);
		$_w = implode('-', $_w);

		$str = preg_replace('/'.$_w.'/', str_repeat('*', strlen($w)), $str);
	}

	$str = str_replace('-', ' ', $str);

	echo '<br />--Sanitized output (JSON)--<br />';
	echo json_encode(array(
		'original' => $orig,
		'sanitized' => $str
	));
}
?>