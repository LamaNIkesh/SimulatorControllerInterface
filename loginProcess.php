<?php 
include("head.html")

 ?>

<?php
//variables for database connection
$server = 'localhost';
$user = 'root';
$pass = '';
$db = 'Registration';
$flag = 0;

try{
	
	$connection = mysqli_connect("$server",$user,$pass,$db);
	//echo $_POST['user'];


	$flag = 0;
	$username = mysqli_real_escape_string($connection, $_POST['username']);
	$password = mysqli_real_escape_string($connection,$_POST['password']);
	$password = md5($password);


//query the database for user
	$result = mysqli_query($connection, "select * from Signup where username ='$username' and password = '$password'") 
			or die("No user found!!!!".mysql_error());
	$row = mysqli_fetch_array($result);

	
	//if result matched number of rows will be 1

	if($row['username']== $username && $row['password'] == $password){
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $password;
		$_SESSION['useremail'] = $row['email'];
		$flag = 1;
		$_SESSION['flag'] = $flag;
		$_SESSION['loginfail'] = 0;
		header('Location: home.php'); 
	}
	else{
		//$error = "Your login name or password is invalid";
		//echo 'Your login name or password is invalid';
	
		$_SESSION['loginfail'] = 1;
		header('Location:login.php');
		
			}


	}
catch(PDOException $e)
{
	echo "Connection failed: ". $e->getMessage();
	$flag = 1;
}

mysqli_close($connection);
?>
