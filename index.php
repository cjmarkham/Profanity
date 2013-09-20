<?php

$debug = isset($_SERVER['HTTP_REFERER']) ? strpos($_SERVER['HTTP_REFERER'], 'cjmarkham') != false : true;

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

set_error_handler(function($code, $error, $file, $line) use ($debug) {

	if ($code != 2048 && $code != 8) // Strict|Notice
	{
		if ($debug)
		{
			var_dump(array(
				'code' => $code,
				'error' => $error,
				'line' => $line,
				'file' => $file
			));
		}
		else
		{
			exit(json_encode(array(
				'error' => true,
				'response' => 'An internal error has occurred. Please try again later.'
			)));
		}
	}

});

include 'sanitize.php';
include '../../header.html';

?>

<div style="text-align:center">
	<form method="get" class="clearfix">
		<h1>
			Profanity Filter<br />
			<small>Enter a swear word below. Use spaces, symbols, anything you want.</small>
		</h1>
		<br />
		<input value="<?=$_GET['str']?>" type="text" style="font-size:20px;padding:20px;border-radius:10px" class="span6" placeholder="Enter swear word" name="str" /><br />
		<button style="font-size:20px;padding:20px !important;border-radius:10px;width:490px" class="btn btn-primary">Sanitize</button>
	</form>

	<?
	if (isset($_GET['str']) && strlen($_GET['str']) > 0)
	{
		$str = urldecode($_GET['str']);
		echo new Sanitize($str);
		echo '<hr />';
	}
?>
</div>

<? include 'geshi.php'; ?>

<fieldset>
	<legend>Get the code <small style="cursor:pointer;" onclick="el=document.getElementById('code');el.style.display=='none'?el.style.display='block':el.style.display='none'">Click to show</small></legend>
	<div style="display:none" id="code">
		<?
		$geshi = new GeSHi(file_get_contents('download.html'), 'php');

		$geshi->set_header_type(GESHI_HEADER_DIV);
		$geshi->set_tab_width(4);
		$geshi->set_overall_style('background: #fefefe; border:1px solid #dadada; padding:10px;', true);
		
		echo $geshi->parse_code();
		?>
	</div>
</fieldset>

<? include '../../footer.html'; ?>