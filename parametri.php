<?php
include_once 'include/main.php';

$db = new db();

if (isset($_POST['submit'])) //l'utente ha immesso i dati
{
	$username = $user->nick;
	$pwd = $_POST['pwd'];
	$pwd2 = $_POST['pwd2'];
	$nome = ucwords($_POST['nome']);
    $cognome = ucwords($_POST['cognome']);
    $email = $_POST['email'];
    $allenatore = ucwords($_POST['allenatore']);
    $stemma = $user->stemma;
    
    $checkok = 1;
    //validazione campi
	
	if (strlen($pwd) < 3)
	{
		$error = "Password troppo corta";
		$checkok = 0;
	}		
	
	if ( $pwd != $pwd2 )
	{
		$error = "Le password non corrispondono";
		$checkok = 0;
	}	
	
	if (strlen($nome) < 1)
	{
		$error = "Il campo nome &egrave; obbligatorio";
		$checkok = 0;
	}
	
	if (strlen($cognome) < 1)
	{
		$error = "Il campo cognome &egrave; obbligatorio";
		$checkok = 0;
	}
	
	if (strlen($email) < 1)
	{
		$error = "Il campo e-mail &egrave; obbligatorio";
		$checkok = 0;
	}
	
	if (strlen($allenatore) < 1)
	{
		$error = "Il campo allenatore &egrave; obbligatorio";
		$checkok = 0;
	}
	
    if ($checkok && $_FILES['stemma']['error'] == UPLOAD_ERR_OK) //upload stemma ok (solo se il resto è ok)
    {
    	$ext = strtolower(pathinfo($_FILES['stemma']['name'],PATHINFO_EXTENSION));
    	switch ($ext)
    	{
    		case 'jpg': case 'jpeg': case 'gif': case 'png': case 'bmp':
    			break; //tipo file ok
    		default:
    			$error = "Tipo di file non corretto";
				$checkok = 0;
    	}
    	$destfile = 'images/stemmi/'.$username.'.'.$ext;
    	if ($moveok = move_uploaded_file($_FILES['stemma']['tmp_name'], $destfile))
    		$stemma = $destfile;
    	else
    	{
    		$error = "Errore nel caricamento dello stemma";
    		$checkok = 0;
    	}
    }
    else
    	switch ($_FILES['stemma']['error'])
    	{	//gestione errori: pag.489
    		case 1:
    		case 2:
		    	$error = "Lo stemma caricato &egrave; troppo grande";
		    	$checkok = 0;
		    	break;
    		case 3:
    			$error = "Errore nel caricamento dello stemma: riprovare";
		    	$checkok = 0;
		    	break;
    	}
	
	if ($checkok) //procedo alla registrazione
	{
		$query = "UPDATE utenti SET
		nome='".$db->escape($nome)."',
		cognome='".$db->escape($cognome)."',
		pwd='".$db->escape(sha1($pwd))."',
		email='".$db->escape($email)."',
		allenatore='".$db->escape($allenatore)."',
		stemma='".$db->escape($stemma)."'
		WHERE id=".$db->escape($user->id);
		if ($db->query($query))
		{
			//TODO: if cambio allenatore -> news:'tizio ha cambiato allenatore'
			$user->loadFromDB($username, sha1($pwd));
		}
		else
			$error = 'Errore nell\'aggiornamento: contattare l\'amministratore via e-mail a info@fantalegamento.it';		
	}
    
}

$html = new html("PARAMETRI");
$html->title('Parametri',$user->stemma);
	echo <<<main
		<form action="parametri.php" method="post" enctype="multipart/form-data">
			<table border='0' align=left cellpadding='5' cellspacing='2'>
				<tr>
					<td align=left>
						<label for="username">Nickname</label>
					</td>
					<td align=left>
					$user->nick
					</td>
				</tr>
				<tr>
					<td align=left>
						<label for="pwd">Password</label>
					</td>
					<td align=left>
main;
					$html->input("pwd",15,10,'',"password");
					echo <<< main
					</td>
				</tr>
				<tr>
					<td align=left>
						<label for="pwd2">Riscrivi password</label>
					</td>
					<td align=left>
main;
					$html->input("pwd2",15,10,'',"password");
					echo <<< main
					</td>
				</tr>
				<tr>
					<td align=left>
						<label for="email">E-mail</label>
					</td>
					<td align=left>
main;
					$html->input("email",60,50,$user->email);
					echo <<< main
					</td>
				</tr>
				<tr>
					<td align=left>
						<label for="nome">Nome</label>
					</td>
					<td align=left>
main;
					$html->input("nome",25,20,$user->nome);
					echo <<< main
					</td>
				</tr>
				<tr>
					<td align=left>
						<label for="cognome">Cognome</label>
					</td>
					<td align=left>
main;
					$html->input("cognome",25,20,$user->cognome);
					//TODO: bandiera per nazionalità
					echo <<< main
					</td>
				</tr>
				<tr>
					<td align=left>
						<label for="nazione">Nazione</label>
					</td>
					<td align=left>
					$user->nazionalita
					</td>
				</tr>
				<tr>
					<td align=left>
						<label for="squadra">Nome squadra</label>
					</td>
					<td align=left>
					$user->squadra
					</td>
				</tr>
				<tr>
					<td align=left>
						<label for="stadio">Stadio</label>
					</td>
					<td align=left>
					$user->stadio
					</td>
				</tr>
				<tr>
					<td align=left>
						<label for="tifosi">Tifosi</label>
					</td>
					<td align=left>
					$user->tifosi
					</td>
				</tr>
				<tr>
					<td align=left>
						<label for="allenatore">Allenatore</label>
					</td>
					<td align=left>
main;
					$html->input("allenatore",35,30,$user->allenatore);
					echo <<< main
					</td>
				</tr>
				<tr>
					<td align=left>
						<label for="stemma">Stemma</label>
					</td>
					<td align=left>
main;
					$html->input("stemma",'','','',"file");
					echo <<< main
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td align=left>
main;
					$html->submit("Invia");
					echo <<< main
					</td>
				</tr>
			</table>
		</form>
main;
	if (isset($error))
		$html->error($error);
$db->close();
$html->close();
?>