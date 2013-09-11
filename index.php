<?php

$raw = false;

if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'cjmarkham') == false)
{
	$raw = true;
}

$debug = isset($_SERVER['HTTP_REFERER']) ? strpos($_SERVER['HTTP_REFERER'], 'cjmarkham') != false : true;

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

set_error_handler(function($code, $error, $file, $line) use ($debug) {

	if ($code != 2048 && $code != 8) // Strict
	{
		if ($debug)
		{
			$array = array(
				'code' => $code,
				'error' => $error,
				'line' => $line,
				'file' => $file
			);

			var_dump($array);
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

include 'config.php';
include 'db.php';

Db::$local = Db::connect($db_config['local']);
Db::$master = Db::connect($db_config['master']);

include 'sanitize.php';

if (isset($_GET['str']) && $raw)
{
	if (!isset($_GET['apikey']))
	{
		exit(json_encode(array(
			'error' => true,
			'response' => 'No API key given'
		)));
	}

	$key = get_api_key($_GET['apikey']);

	if (!$key)
	{
		exit(json_encode(array(
			'error' => true,
			'response' => 'Invalid API key specified'
		)));
	}
	
	if ($key['site'] != rtrim($_SERVER['HTTP_REFERER'], '/'))
	{
		exit(json_encode(array(
			'error' => true,
			'response' => 'API key does not match referer ' . $_SERVER['HTTP_REFERER']
		)));
	}

	$str = urldecode($_GET['str']);

	$sanitize = new Sanitize($str, true);
	exit ($sanitize);
}

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
	if (isset($_GET['str']))
	{
		$str = urldecode($_GET['str']);
		$start = microtime(true);
		echo new Sanitize($str);
		$end = round(microtime(true) - $start, 4);
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
	<legend>Recent Words <small>words that bypassed filter</small></legend>
	<?=implode(',', $recent)?>
</fieldset>

<? include 'geshi.php'; ?>

<fieldset>
	<legend>API Documentation <small style="cursor:pointer;" onclick="el=document.getElementById('docs');el.style.display=='none'?el.style.display='block':el.style.display='none'">Click to show</small></legend>
	<div style="display:none" id="docs">
		<p>Sanitization takes place via a <code>GET</code> request.</p>
		<p>There are several ways to perform this request but you will need an active API key in order to do so</p>
		<p>API keys can currently only be obtained via emailing <a href="mailto:carl@cjmarkham.co.uk">carl@cjmarkham.co.uk</a> and supplying your name and website you wish the key to be bound to</p>
		<br />
		<h4>jQuery <code>getJSON</code> method</h4>
		<?php 

		$js = "$.getJSON('http://cjmarkham.co.uk/projects/profanity?str={YOUR_URLENCODED_STRING}&apikey={YOUR_APIKEY}', function (response) {
	response = JSON.parse(response);
	// See the JSON structure above for info on how to use the response
});";

		$geshi = new GeSHi($js, 'php');

		$geshi->set_header_type(GESHI_HEADER_DIV);
		$geshi->set_tab_width(4);
		$geshi->set_overall_style('background: #fefefe; border:1px solid #dadada; padding:10px;', true);
		
		echo $geshi->parse_code();

		?>
		<br />
		<h4>PHP <code>cURL</code> method</h4>
		<p>Your server must have the <code>php_curl</code> extension enabled</p>
		<?php 

		$php = <<<EOM
\$url = 'http://cjmarkham.co.uk/projects/profanity?str={YOUR_URLENCODED_STRING}&apikey={YOUR_APIKEY}';
\$ch = curl_init(\$url);
\$response = curl_exec(\$ch);
curl_close(\$ch);
EOM;

		$geshi = new GeSHi($php, 'php');

		$geshi->set_header_type(GESHI_HEADER_DIV);
		$geshi->set_tab_width(4);
		$geshi->set_overall_style('background: #fefefe; border:1px solid #dadada; padding:10px;', true);
		
		echo $geshi->parse_code();

		?>

		<br />
		<h4>PHP <code>file_get_contents</code> method</h4>

		<?php 

		$php = <<<EOM
\$url = 'http://cjmarkham.co.uk/projects/profanity?str={YOUR_URLENCODED_STRING}&apikey={YOUR_APIKEY}';
\$response = file_get_contents(\$url);
EOM;

		$geshi = new GeSHi($php, 'javascript');

		$geshi->set_header_type(GESHI_HEADER_DIV);
		$geshi->set_tab_width(4);
		$geshi->set_overall_style('background: #fefefe; border:1px solid #dadada; padding:10px;', true);
		
		echo $geshi->parse_code();

		?>
	</div>
</fieldset>
<?
echo 'Took '.$end.' seconds to sanitize';
include '../../footer.html';
?>