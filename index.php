<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

define('RPC_HOSTNAME', '127.0.0.1');
define('RPC_PORT', '11812');


function getPost($key = null) {
	if(empty($_POST)) return null;
	if(!$key) return $_POST;
	if(isset($_POST[$key])) return $_POST[$key];
	return null;
}

function request($method, $body) {
	$url = RPC_HOSTNAME . ':' . RPC_PORT;
	$body = (!is_string($body)) ? json_encode($body) : $body;
	$options = [
	  'http' => [
	    'method' => $method,
	    'header' => implode("\r\n",[
	        'Accept: */*',
	    ]),
	    'ignore_errors' => true,
	    'content' => $body
	  ],
	  'ssl' => [
	    'verify_peer' => false,
	    'verify_peer_name' => false
	  ]
	];
	$context = stream_context_create($options);
	$response = file_get_contents($url, false, $context);
	return $response;
}

function processData() {
	if(!getPost())return "";
	$txid = getPost('txid');
	if(!$txid) return "Missing Transaction Hash or Height";
	$address = getPost('address');
	if(!$address) return "Missing Wallet Address";
	$txkey = getPost('txkey');
	$isHeight = is_integer($txid);
	if($isHeight || !$txkey) return "Checking by height or no txkey is not yet implemented";
	$response = request('check_tx_key',compact('address','txkey','txid'));
	if(!$response) return "Unable to get a response for request";
	$response = json_decode($response, true);
	if(!isset($response['results']) || empty($response['results'])) return "Unable to get a results from response";
	$output = "<table><tbody>";
	foreach($response['results'] as $key => $value) {
		$output .= "<tr><th>$key</th><td>$value</td>";
	}
	$output .= "</tbody></table>";
	return $output;

}

$flashMessage = processData();


?>


<html data-theme="dark">
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/picocss/1.5.0/pico.min.css" integrity="sha512-H5iGCGcmwcVcBQHyOkESTji2i7HlomeQq9k/uZQZqNJyrNylDEHi8udQ/To8QSFP8vlWho87HeRR25AY2Xxl7w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
  <main class="container">
  	  <div class="grid" style='margin-bottom:1em;'>
  	  	<h1 style="text-align: center;"> SCALA MINI EXPLORER</h1>
  	  </div>
  	 <?php if($flashMessage):?>
  	  <div class="grid">
  	  	<article aria-label="Message"><?=$flashMessage;?></article>
  	  </div>
  	<?php else:?>
  		 <div class="grid" style='margin-bottom:5em;'>
  	  	<div>
  	  	This mini version of the explorer is to tests our upcoming v8 for any bugs and issues. If you want current chain go to <a href='https://explorer.scalaproject.io'>Explorer</a>
  	  </div>
  	  </div>
  	<?php endif;?>
  	 

<form method="post" accept-charset="utf-8" role="form" action=<?php echo $_SERVER['PHP_SELF'];?>>
  <div class="grid">
    <label for="txid">
     Transaction Hash or Height
      <input type="text" name="txid" placeholder="Transaction Hash or Height" required>
    </label>

    <label for="txkey">
      Transaction Key (optional)
      <input type="text" name="txkey" placeholder="Transaction Key (optional)">
    </label>

  </div>

  
  <label for="address">Wallet Address</label>
  <input type="text" name="address" placeholder="Walelt address" required>

  <!-- Button -->
  <button type="submit">Submit</button>

</form>

  </main>
</body>
</html>
