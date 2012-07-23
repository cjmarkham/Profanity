<?php

$words = array(
	'shit',
	'fuck',
	'wank',
	'fucking',
	'bastard',
	'cunt'
);

$str = $_GET['str'];
$orig = $str;

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

?>

<style>
body
{
	margin:100px auto;
	width:900px;
	text-align:center;
}

div, ul
{
	padding:5px;
	border:1px solid #dadada;
	width:400px;
	margin:0 auto 10px auto;
	list-style:none;
}

</style>

<form method="get">
	<textarea rows="5" cols="40" placeholder="Enter swear" name="str"></textarea>
	<br />
	<input type="submit" value="Sanitize" />
</form>

Sanitized words:

<ul>
<?php foreach ($words as $w) { ?>
	<li><?php echo $w; ?></li>
<?php } ?>
</ul>