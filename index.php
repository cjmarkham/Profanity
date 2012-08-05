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
	
	foreach($words as $w)
	{
		$str = preg_replace('/'.$w.'/i', str_repeat('*', strlen($w)), $str);
		
		$check = explode(" ",$str) ;

		foreach($check as $c)
		{
			if(metaphone(trim($c)) == metaphone(trim($w)))
			{
				$str = preg_replace('/'.$c.'/im', str_repeat('*', strlen($c)) ,$str) ;
			}
		}
	}

	echo '<div>'.nl2br($orig).'</div>';
	echo '<div>'.nl2br($str).'</div>';
}

if ($_GET['str'])
{
	$str = $_GET['str'];
	sanitize($str);
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