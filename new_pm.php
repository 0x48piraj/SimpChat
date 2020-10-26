<?php
include('config.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.7/semantic.js"></script>
        <script src="assets/script.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.7/semantic.min.css">
        <link href="assets/style.css" rel="stylesheet" title="Style" />
		<title>New PM</title>
	</head>
	<body>

<?php

if (isset($_SESSION['username'])) {
	$form     = true;
	$otitle   = '';
	$orecip   = '';
	$omessage = '';

	if (isset($_POST['title'], $_POST['recip'], $_POST['message'])) {
		$otitle   = $_POST['title'];
		$orecip   = $_POST['recip'];
		$omessage = $_POST['message'];

		if (get_magic_quotes_gpc()) {
			$otitle   = stripslashes($otitle);
			$orecip   = stripslashes($orecip);
			$omessage = stripslashes($omessage);
		}

		if ($_POST['title'] != '' and $_POST['recip'] != '' and $_POST['message'] != '') {

			$title   = mysqli_real_escape_string($link, $otitle);
			$recip   = mysqli_real_escape_string($link, $orecip);
			$message = mysqli_real_escape_string($link, nl2br(htmlentities($omessage, ENT_QUOTES, 'UTF-8')));

			$dn1 = mysqli_fetch_array(mysqli_query($link, 'select count(id) as recip, id as recipid from users where username="'.$recip.'"'));
			if ($dn1['recip'] == 1) {

				if ($dn1['recipid'] != $_SESSION['userid']) {

					$cipher = "aes-128-gcm";
					$ivlen  = openssl_cipher_iv_length($cipher);
					$iv     = openssl_random_pseudo_bytes($ivlen);
					$key    = getKey($_SESSION['userid'], $dn1['recipid']);
					$tag    = null;
					$method = openssl_get_cipher_methods();
					if (in_array($cipher, $method)) {
						$iv = openssl_random_pseudo_bytes($ivlen);
						$ciphertext_raw = openssl_encrypt($message, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv, $tag);
						$hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
						$ciphertext = base64_encode($iv.$hmac.$ciphertext_raw);    // store $cipher, $hmac and $iv for decryption later
						if (mysqli_query($link, 'insert into pm (title, sender, recipient, message, timestamp, tag) values ("'.$title.'", "'.$_SESSION['userid'].'", "'.$dn1['recipid'].'", "'.$ciphertext.'", "'.time().'", "'.$tag.'")')) {
?>
		<div class="message">The message has successfully been sent.<br />
		<a href="mailbox.php">Mailbox</a></div>

<?php
							$form = false;
						}
						else $error = 'An error occurred while sending the message.';//Otherwise, we say that an error occured
					}
					else $error = 'Error while sending the message.';//Otherwise, we say the user cannot send a message to himself
				}
				else $error = 'You cannot send a message to yourself.';//Otherwise, we say the user cannot send a message to himself
			}
			else $error = 'The recipient does not exist.';//Otherwise, we say the recipient does not exists
		}
		else $error = 'Please fill in all of the fields.';//Otherwise, we say a field is empty
	}

	if ($form) {

		if (isset($error)) echo '<div class="message">'.$error.'</div>';

?>

		<div class="ui inverted menu">
            <div class="ui container">
              <a href="https://github.com/0x48piraj/SimpChat" target="_blank" class="header item">
                <img class="logo" src="http://placeimg.com/30/25/any"> 
                 SimpChat 
               </a>
              <a href="index.php" class="item"><i class="home icon"></i>Home</a>
              <?php
                //If the user is logged in, we display links to see the list of users, his/her pms and a link to log out
                if (isset($_SESSION['username'])) {
              ?>
              <div class="ui simple dropdown item">
                Mail <i class="dropdown icon"></i>
                <div class="menu">
                  <a class="item" href="new_pm.php">Compose</a>
                  <a class="item" href="mailbox.php">Mailbox</a>
                  <div class="divider"></div>
                  <div class="header">Info</div>
                  <a class="item" href="users.php">Users</a>
                </div>
              </div>
              <div class="right menu">
                <div class="item">
                  <a class="ui basic teal button" href="logout.php"><i class="sign in icon"></i>Logout</a>
                </div>
                </div>
            <?php
            }
            else {
            //Otherwise, we display a link to sign up / log in
            ?>
              <div class="right menu">
                <div class="item">
                  <a class="ui basic teal button" href="login.php"><i class="sign in icon"></i>Log in</a>
                </div>
                <div class="item">
                <a class="ui basic blue button" href="sign_up.php"><i class="add user icon"></i>Sign up</a>
                </div>
                </div>
                <?php
                }
                ?>
            </div>
          </div>
		<div class="content">
			<h1>New PM</h1>
			<form action="new_pm.php" method="post">
				Please fill the following form to send a PM.<br />
				<label for="recip">Recipient<span class="small"> (Username)</span></label><input type="text" value="<?php echo htmlentities($orecip, ENT_QUOTES, 'UTF-8'); ?>" id="recip" name="recip" /><br />
				<label for="title">Title</label><input type="text" value="<?php echo htmlentities($otitle, ENT_QUOTES, 'UTF-8'); ?>" id="title" name="title" /><br />
				<label for="message">Message</label><textarea cols="40" rows="5" id="message" name="message"><?php echo htmlentities($omessage, ENT_QUOTES, 'UTF-8'); ?></textarea><br />
				<input type="submit" value="Send" />
			</form>
		</div>
<?php
	}
}
else echo '<div class="message">You must be logged in to access this page.</div>';
?>
	</body>
</html>
