<?php
include_once 'class/user.class.php';
include_once 'class/html.class.php';
include_once 'class/db.class.php';

$offline = 0; //mettere a 1 per mandare il sito offline

if (isset($_GET['attiva'])) //se l'utente arriva dalla attivazione
	$error = 'Attivazione avvenuta correttamente: effettua il login per iniziare a giocare';
	
if (isset($_GET['signup'])) //se l'utente arriva dalla registrazione
{
	$username = $_GET['signup'];
	$error = 'Iscrizione avvenuta correttamente: attiva la tua squadra per iniziare a giocare';
}
else 
	$username = $_POST['username'];

if ($_GET['guest'] == 1 && !$offline)
{
	$user = new user();
	$_SESSION['user'] = $user;
	header('Location: index.php');
}
else if (isset($_POST['username']) && !$offline)
{
	$user = new user($_POST['username'],sha1($_POST['pwd']));
	if(!$user->error)
	{
		$_SESSION['user'] = $user;
		header('Location: index.php');
	}
	else 
		$error = $user->error;
}

$db = new db();
$html = new html();
$html->head('LOGIN');
$html->bodyon();
$html->container();
$html->header();
$html->main();
echo '<div align=center>';
if ($offline)
	$html->title('Sito in preparazione per la nuova stagione','images/logo_fantalegamento.png');
else
{
	$html->title('Login','images/logo_fantalegamento.png');
	echo <<< main
		<form action="login.php" method="post">
			<table width=100% border='0' align=left cellpadding='5' cellspacing='2'>
				<tr>
					<td width=50% align=right>
						<label for="username">Nickname</label>
					</td>
					<td align=left>
main;
					$html->input("username",15,10,$username);
					echo <<< main
					</td>
				</tr>
				<tr>
					<td width=50% align=right>
						<label for="pwd">Password</label>
					</td>
					<td align=left>
main;
					$html->input("pwd",15,10,'',"password");
					echo <<< main
					</td>
				</tr>
				<tr>
					<td colspan=2 align=center>
main;
					$html->submit("Login");
					echo <<< main
					</td>
				</tr>
			</table>
		</form>
main;
		if (isset($error))
			$html->error($error);
		//$html->error('ATTENZIONE: Sito in fase di test! Le iscrizioni non sono ancora aperte.');
		/*echo <<< main
		<br>
		<i>Hai partecipato alla scorsa edizione?</i>
		<br>
		<a href="signup.php?renew=1">Rinnova iscrizione</a>
		<br>


		<br>
		<i>Non sei ancora registrato?</i>
		<br>
		<a href="signup.php">Registrati</a>

			
			<br>
		<a href="login.php?guest=1">Accedi come ospite</a>
main;*/
}
echo '</div>';
$html->close();
$db->close();

?>