<?php

function sanitize($str)
{
	$orig = $str;
	
	$words = array(
		'shit',
		'fuck',
		'wank',
		'fucking',
		'bastard',
		'cunt'
	);
	
	$words2 = array(
		'shit',
		'fuck',
		'wank',
		'fucking',
		'bastard'
	);
	
	$check = explode(" ",$str);
	echo '--Calculating levenshtein distance--<br />';
	foreach($check as $c)
	{
		foreach ($words2 as $w)
		{
			$lev = levenshtein($c, $w);
			$max = max(strlen($c), strlen($w));
						
			$percentage = round((1 - $lev / max(strlen($c), strlen($w))) * 100, 2);
			echo $c.' is a '.$percentage.'% match with '.$w.'<br />';
			
			if(metaphone(trim($c)) === metaphone(trim($w)))
			{
				$str = preg_replace('/'.$c.'/i', str_repeat('*', strlen($c)) ,$str);
			}
		}
	}
	
	foreach($words as $w)
	{
		$str = preg_replace('/'.$w.'/i', str_repeat('*', strlen($w)), $str);
	}

	echo '<br />--Sanitized output (JSON)--<br />';
	echo json_encode(array(
		'original' => $orig,
		'sanitized' => $str
	));
}

if ($_GET['str'])
{
	$str = $_GET['str'];
	return sanitize($str);
}
?>

<style>
body
{
	margin:100px auto;
	width:900px;
	text-align:center;
}

div
{
	padding:5px;
	border:1px solid #dadada;
	width:400px;
	margin:0 auto 10px auto;
}

</style>

<form method="get">
	<input type="text" style="width:340px" placeholder="Enter swear" name="str" />
	<input type="submit" value="Sanitize" />
</form>