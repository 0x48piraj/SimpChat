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
        <title>Mailbox</title>
    </head>
    <body>
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
<?php
if(isset($_SESSION['username']))
{
$rcvdMsg = mysqli_query($link, 'select pm.id, pm.message, pm.title, pm.timestamp, pm.tag, users.id userid, users.username from pm, users where pm.recipient="'.$_SESSION['userid'].'" and users.id=pm.sender order by pm.timestamp desc');
$sentMsg = mysqli_query($link, 'select pm.id, pm.message, pm.title, pm.timestamp, pm.tag, users.id userid, users.username from pm, users where pm.sender="'.$_SESSION['userid'].'" and users.id=pm.recipient order by pm.timestamp desc');
?>
<h3>Received messages (<?php echo intval(mysqli_num_rows($rcvdMsg)); ?>):</h3>
<table>
	<tr>
    	<th class="title_cell">Title</th>
        <th>Sender</th>
        <th>Time</th>
        <th>Message</th>
    </tr>
<?php

while($dn1 = mysqli_fetch_array($rcvdMsg))
{
?>
	<tr>
    	<td class="left"><b><?php echo htmlentities($dn1['title'], ENT_QUOTES, 'UTF-8'); ?></b></td>
    	<td><?php echo htmlentities($dn1['username'], ENT_QUOTES, 'UTF-8'); ?></td>
    	<td><?php echo date('Y/m/d H:i:s' ,$dn1['timestamp']); ?></td>
        <td>
		<?php
			$cipher = "aes-128-gcm";
			$ivlen  = openssl_cipher_iv_length($cipher);
			$key    = getKey($_SESSION['userid'], $dn1['userid']);
			$method = openssl_get_cipher_methods();
			if (in_array($cipher, $method)) {
				$c    = base64_decode($dn1['message']);
				$iv   = substr($c, 0, $ivlen);
				$hmac = substr($c, $ivlen, $sha2len=32);
				$ciphertext_raw = substr($c, $ivlen+$sha2len);
				
				$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
				if (!hash_equals($hmac, $calcmac)) {	//PHP 5.6+ timing attack safe comparison
					$decrypted = "Message decryption integrity failed.";
				}
				else {
					$decrypted = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv, $dn1['tag']);
				}
			}
			else $decrypted = "Decryption algorithm unsupported.";
			
			echo $decrypted;
		?>
		</td>
    </tr>
<?php
}

if(intval(mysqli_num_rows($rcvdMsg))==0)
{
?>
	<tr>
    	<td colspan="4" class="center">You have no received messages.</td>
    </tr>
<?php
}
?>
</table>
<br />
<h3>Sent messages (<?php echo intval(mysqli_num_rows($sentMsg)); ?>):</h3>
<table>
	<tr>
    	<th class="title_cell">Title</th>
        <th>Receiver</th>
        <th>Time</th>
        <th>Message</th>
    </tr>
<?php

while($dn2 = mysqli_fetch_array($sentMsg))
{
?>
	<tr>
    	<td class="left"><b><?php echo htmlentities($dn2['title'], ENT_QUOTES, 'UTF-8'); ?></b></td>
    	<td><?php echo htmlentities($dn2['username'], ENT_QUOTES, 'UTF-8'); ?></td>
    	<td><?php echo date('Y/m/d H:i:s' ,$dn2['timestamp']); ?></td>
		<td>
		<?php
			$cipher = "aes-128-gcm";
			$ivlen  = openssl_cipher_iv_length($cipher);
			$key    = getKey($_SESSION['userid'], $dn2['userid']);
			$method = openssl_get_cipher_methods();
			if (in_array($cipher, $method)) {
				$c    = base64_decode($dn2['message']);
				$iv   = substr($c, 0, $ivlen);
				$hmac = substr($c, $ivlen, $sha2len=32);
				$ciphertext_raw = substr($c, $ivlen+$sha2len);
				
				$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
				if (!hash_equals($hmac, $calcmac)) {	//PHP 5.6+ timing attack safe comparison
					$decrypted = "Message decryption integrity failed.";
				}
				else {
					$decrypted = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv, $dn2['tag']);
				}
			}
			else $decrypted = "Decryption algorithm unsupported.";
			
			echo $decrypted;
		?>
		</td>
    </tr>
<?php
}

if(intval(mysqli_num_rows($sentMsg))==0)
{
?>
	<tr>
    	<td colspan="4" class="center">You have no sent messages.</td>
    </tr>
<?php
}
?>
</table>
<?php
}
else
{
	echo 'You must be logged in to access this page.';
}
?>
		</div>
	</body>
</html>
