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
		<title>Sign up</title>
	</head>
	<body>
<?php
// check if the form has been sent
if(isset($_POST['username'], $_POST['password'], $_POST['passverif'], $_POST['email']) and $_POST['username'] != '')
{
	// remove slashes depending on the configuration
	if(get_magic_quotes_gpc())
	{
		$_POST['username']  = stripslashes($_POST['username']);
		$_POST['password']  = stripslashes($_POST['password']);
		$_POST['passverif'] = stripslashes($_POST['passverif']);
		$_POST['email']  	= stripslashes($_POST['email']);
	}
	// check if the two passwords are identical
	$errors = [];
	if($_POST['password'] == $_POST['passverif'])
	{
		// check if the choosen password is strong enough.
		if(checkPassword($_POST['password'], $errors))
		{
			// check if the email form is valid
			if(preg_match('#^(([a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+\.?)*[a-z0-9!\#$%&\\\'*+/=?^_`{|}~-]+)@(([a-z0-9-_]+\.?)*[a-z0-9-_]+)\.[a-z]{2,}$#i', $_POST['email']))
			{
				// protect the variables
				$username = mysqli_real_escape_string($link, $_POST['username']);
				$password = mysqli_real_escape_string($link, $_POST['password']);
				$email	  = mysqli_real_escape_string($link, $_POST['email']);
				$salt	  = (string)rand(10000, 99999);	     // generate a five digit salt
				$password = hash("sha512", $salt.$password); // compute the hash of salt concatenated to password
				// check if there is no other user with the same username
				$dn = mysqli_num_rows(mysqli_query($link, 'select id from users where username="'.$username.'"'));
				if($dn == 0)
				{
					// We save the informations to the databse
					if(mysqli_query($link, 'insert into users(username, password, email, salt) values ("'.$username.'", "'.$password.'", "'.$email.'","'.$salt.'")'))
					{
						// We dont display the form
						$form = false;
?>
		<div class="message">You have successfuly been signed up. You can now log in.<br />
        <a href="login.php">Log in</a></div>
<?php
					}
					else
					{
						// Otherwise, we say that an error occured
						$form	= true;
						$message = 'An error occurred while signing up.';
					}
				}
				else
				{
					// Otherwise, we say the username is not available
					$form	= true;
					$message = 'The username is already in use, please choose another one.';
				}
			}
			else
			{
				// Otherwise, we say the email is not valid
				$form	= true;
				$message = 'The email adress is invalid.';
			}
		}
		else
		{
			// Otherwise, we say the password is too weak
			$form	= true;
			$message = '';
			foreach ($errors as $item)
				$message = $message.$item."<BR>";
		}
	}
	else
	{
		// Otherwise, we say the passwords are not identical
		$form	 = true;
		$message = 'The passwords are not identical.';
	}
}
else
{
	$form = true;
}
if ($form) {
	//We display a message if necessary
	if(isset($message)) echo '<div class="message">'.$message.'</div>';

	//We display the form again
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
			<form action="sign_up.php" method="post">
				Please fill in the following form to sign up:<br />
				<div class="center">
					<label for="username">Username</label><input type="text" name="username" value="<?php if(isset($_POST['username'])){echo htmlentities($_POST['username'], ENT_QUOTES, 'UTF-8');} ?>" /><br />
					<label for="password">Password<span class="small"> (10 characters min.)</span></label><input type="password" name="password" /><br />
					<label for="passverif">Password<span class="small"> (verification)</span></label><input type="password" name="passverif" /><br />
					<label for="email">Email</label><input type="text" name="email" value="<?php if(isset($_POST['email'])){echo htmlentities($_POST['email'], ENT_QUOTES, 'UTF-8');} ?>" /><br />
					<input type="submit" value="Sign up" />
				</div>
			</form>
		</div>
<?php
}
?>
	</body>
</html>
