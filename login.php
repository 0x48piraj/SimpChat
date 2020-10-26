<!-- Authenticate a registered user. -->
<?php
include('config.php');

// if the session is active redirect to the landing page
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['username'], $_POST['password'])) {
    $ousername = '';
	// check if the form has been sent
	if(isset($_POST['username'], $_POST['password']))
	{
		// remove slashes depending on the configuration
		if(get_magic_quotes_gpc())
		{
			$ousername = stripslashes($_POST['username']);
			
			$username  = mysqli_real_escape_string($link, stripslashes($_POST['username']));
			$password  = stripslashes($_POST['password']);
		}
		else
		{
			$username = mysqli_real_escape_string($link, $_POST['username']);
			$password = $_POST['password'];
		}
		// fetch the password of the user
		$req = mysqli_query($link, 'select password,id,salt from users where username="'.$username.'"');
		$dn  = mysqli_fetch_array($req);
		$password = hash("sha512", $dn['salt'].$password); // salt the password and hash it
		
		// compare the salted password hash with the real one, and check if the user exists
		if ($dn['password'] == $password and mysqli_num_rows($req)>0) {
			// save the user name in the session username and the user Id in the session userid
			$_SESSION['username'] = $_POST['username'];
			$_SESSION['userid'] = $dn['id'];
			
			header("Location: index.php");
		}
		else {
			// Otherwise, the credentials are incorrect
			$message = 'Incorrect username or password!';
		}
	}
}
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
        <title>Login</title>
    </head>
    <body>
		<?php if(isset($message)) echo '<div class="message">'.$message.'</div>'; ?>
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
			<form action="login.php" method="post">
				<div class="center">
					<label for="username">Username</label><input type="text" name="username" id="username" value="<?php echo htmlentities($username, ENT_QUOTES, 'UTF-8'); ?>" /><br />
					<label for="password">Password</label><input type="password" name="password" id="password" /><br />
					<input type="submit" value="Log in" />
				</div>
			</form>
		</div>
	</body>
</html>
