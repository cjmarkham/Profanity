<?php
error_reporting(E_ALL & ~E_NOTICE);

include '../../header.html';
include 'config.php';
include 'db.php';

Db::$local = Db::connect($db_config['local']);
Db::$master = Db::connect($db_config['master']);

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

	$do_lev = true;
	$do_preg = true;
	$do_trim = true;

	// find word and calculate the context in which it was used
	foreach ($words as $k => $w)
	{
		if (strpos($str, $w) !== false)
		{
			// get 3 words back and 3 words forward
			$max_count = 1;
			$before = 3;
			$after = 3;

			$string_words = str_word_count($str,1);
			$word_position_pos = array_keys(str_word_count($str, 2));

			$count = 0;
			$found = array();

			while ($count < $max_count) 
			{
				if (($word_position = array_search($w,$string_words)) === false)
				{
					break;
				}

				++$count;
				if (($word_position + $after) >= count($string_words))
				{
					$after = count($string_words) - $word_position - 1;
				}

				$start_pos = $word_position_pos[$word_position - $before];
				$end_pos = $word_position_pos[$word_position + $after] + strlen($string_words[$word_position + $after]);

				$string_words = array_slice($string_words,$word_position + 1);
				$word_position_pos = array_slice($word_position_pos, $word_position + 1);

				$found[] = substr($str, $start_pos, $end_pos - $start_pos);
			}

			if (!empty($found))
			{
				foreach ($found as $context)
				{
					$_found = explode(' ', $context);
					$key = array_search($w, $_found);
					unset($_found[$key]);
					
					if (empty($_found))
					{
						break;
					}

					$_words = explode(' ', $context);

					foreach ($_words as $k => $_w)
					{
						if (in_array($_w.$_words[$k+1], $words))
						{
							$str = str_replace(array($_w.' '.$_words[$k+1]), array(str_repeat('*', strlen($_w.$_words[$k+1]))), $str);
						}
					}
				}
			}
		}
	}

	$symbols = array('$', '0', '(');
	$letters = array('s', 'o', 'c');

	echo '--Orignal String--<br />'.$str.'<br /><br />';
	$str = str_replace($symbols, $letters, $str);
	
	// calculate levenshtein distance and replace words in string
	// which sound like words in array
	$check = explode(' ', $str);

	echo '--Calculating levenshtein distance--<br />';

	foreach($check as $k => $c)
	{
		$c = trim($c);

		if (in_array($c.$check[$k+1], $words))
		{
			$str = str_replace(array($c.' '.$check[$k+1]), array(str_repeat('*', strlen($c.$check[$k+1]))), $str);
		}

		foreach ($words2 as $w)
		{
			$lev = levenshtein($c, $w);
						
			$percentage = (int) round((1 - $lev / max(strlen($c), strlen($w))) * 100, 2);
			
			if (metaphone(trim($c)) === metaphone(trim($w)) || $percentage >= 75)
			{
				$str = str_replace($c, str_repeat('*', strlen($c)) ,$str);
			}
		}
	}
	
	// simple preg replace of words in array
	foreach ($words as $w)
	{
		if (preg_match_all('/'.$w.'/i', $str, $matches))
		{
			$str = preg_replace('/'.$w.'/i', str_repeat('*', strlen($w)), $str);
		}
	}
	
	// trim whitespace to replace swears seperated by spaces
	echo '<br />--Trimming Spaces--<br />';

	$str = preg_replace('/\s{1,}/', '-', $str);

	foreach ($words as $k => $w)
	{
		$_w = str_split($w);
		$_w = implode('-', $_w);

		if (preg_match_all('/'.$_w.'/', $str, $match))
		{
			$str = preg_replace('/'.$_w.'/', str_repeat('*', strlen($w)), $str);
		}
	}

	$str = str_replace('-', ' ', $str);

	echo '<br />--Sanitized output (JSON)--<br />';
	echo json_encode(array(
		'original' => $orig,
		'sanitized' => $str
	));

	if ($str == $orig)
	{
		Db::$master->prex("INSERT IGNORE INTO words (word) VALUES (?)", array($str));
	}
	else
	{
		echo '<br /><br /><h1>Epic Fail <small>You failed to bypass the filter</small></h1>';
	}
}
?>

<div style="text-align:center">
	<form method="get" class="clearfix">
		<h2>
			Profanity Filter
			<small>Enter a swear word below. Use spaces, symbols, anything you want.</small>
		</h2>
		<input value="<?=$_GET['str']?>" type="text" style="font-size:20px;padding:20px;border-radius:10px" class="span6" placeholder="Enter swear word" name="str" /><br />
		<button style="font-size:20px;padding:20px !important;border-radius:10px;width:490px" class="btn btn-primary">Sanitize</button>
	</form>

	<?
	if (isset($_GET['str']))
	{
		$str = urldecode($_GET['str']);
		echo sanitize($str);
		echo '<hr />';
	}
?>
</div>
<?
$recent = array();
$results = Db::$local->fetch_results("SELECT * FROM words LIMIT 50");
foreach ($results as $r)
{
	$recent[] = '<span class="btn">'.$r['word'].'</span>';
}
?>

<fieldset>
	<legend>Recent Words</legend>
	<?=implode(',', $recent)?>
</fieldset>

<?
include '../../footer.html';
?>