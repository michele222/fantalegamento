<?php
include_once 'include/main.php';
$db = new db();

if($_GET['id'] && ctype_digit($_GET['id']))
	$squadra = $_GET['id'];
else if (!$user->guest)
	$squadra = $user->id;
else 
	$squadra = 1;
	
$g = getGiornata(); //giornata attuale

if ($_POST['cambiagiornata']) //è stato premuto il pulsante Cambia Giornata
	$giornata = $_POST['giornata'];
else if ($_GET['giornata'] && ctype_digit($_GET['giornata']))
	$giornata = $_GET['giornata'];
else if($_POST['giornata']) //è stato premuto il pulsante Cambia Modulo
{
	$giornata = $_POST['giornata'];
	$result = $db->query('SELECT id FROM formazioni WHERE giornata='.$db->escape($giornata).' AND squadra='.$db->escape($squadra));
	if (getFormazioneStatus($giornata))
	{
		if ($row = $result->fetch_assoc())
		{
			$db->query('UPDATE formazioni SET data=NULL, modulo=\''.$_POST['modulo'].'\' WHERE id='.$row['id']);
			$db->query('DELETE FROM giocatoriformazioni WHERE formazione='.$row['id']);
		}
		else
			$db->query('INSERT INTO formazioni (squadra,giornata,modulo) VALUES ('.$db->escape($squadra).','.$db->escape($giornata).',\''.$_POST['modulo'].'\')');
	}
	else
		$error = 'Non puoi cambiare il modulo per la giornata scelta';
	$result->free();
}
else
	$giornata = $g;
	
if ($_POST['submit'] == 'Invia')
{
	$ok = 1;
	for ($i = 0; $i < 18; $i++)
		if ($_POST['giocatori'][$i])
		{
			for ($j = 0; $j < 18; $j++)
				if (($i != $j) && ($_POST['giocatori'][$i] == $_POST['giocatori'][$j]))
				{
					$ok = 0;
					$error = "Hai selezionato lo stesso giocatore pi&ugrave; volte";
				}
		}
		else
		{
			$ok = 0;
			$error = "La formazione &egrave; incompleta";
		}
	if (!getFormazioneStatus($giornata))
	{
		$ok = 0;
		$error = "Il tempo &egrave; scaduto";
	}
	if ($ok)
	{
		$dif = $cen = $att = 0;
		for ($i = 0; $i < 18; $i++)
			if($i > 0 && $i < 11)
			    /*//UPDATE 23/02/2020 traquartisti codice circa 700 possono essere cen o att. serve nuovo controllo
				if($_POST['giocatori'][$i] > 200 && $_POST['giocatori'][$i] <= 500)
					$dif++;
				else if($_POST['giocatori'][$i] > 500 && $_POST['giocatori'][$i] <= 800)
					$cen++;
				else if($_POST['giocatori'][$i] > 800)
					$att++;*/
			{
			    $result = $db->query('SELECT ruolo FROM giocatori WHERE id='.$db->escape($_POST['giocatori'][$i]));
			    $row1 = $result->fetch_assoc();
    	         switch ($row1['ruolo'])
    	         {
    	             case ($row1['ruolo'] == 1): $dif++; break;
    	             case ($row1['ruolo'] == 2): $cen++; break;
    	             case ($row1['ruolo'] == 3): $att++; break;
    	         }
			    $result->free();
			}
		$modulo=$dif.$cen.$att;
		$data = date("Y-m-d H:i:s");
		$result = $db->query('SELECT id FROM formazioni WHERE giornata='.$db->escape($giornata).' AND squadra='.$db->escape($squadra));
		if ($row = $result->fetch_assoc())
		{
			$idformazione = $row['id'];
			$db->query('UPDATE formazioni SET modulo=\''.$modulo.'\', data=\''.$data.'\' WHERE id='.$idformazione);
			$db->query('DELETE FROM giocatoriformazioni WHERE formazione='.$idformazione);
		}
		else
		{
			$db->query('INSERT INTO formazioni (squadra,giornata,modulo,data) VALUES ('.$db->escape($squadra).','.$db->escape($giornata).',\''.$db->escape($modulo).'\',\''.$db->escape($data).'\')');
			$idformazione = $db->lastId();
		}
		$result->free();
		$commit = 1;
		$db->begin();
		for ($i = 0; $i < 18; $i++)
			if ($commit)
				$commit = $db->query('INSERT INTO giocatoriformazioni (formazione,pos,giocatore) VALUES ('.$idformazione.','.$i.','.$db->escape($_POST['giocatori'][$i]).')');
		if ($commit)
		{
			$db->commit();
			header('location: index.php');
		}
		else 
		{
			$db->rollback();
			$error = 'Errore nella compilazione della formazione: contattare l\'amministratore';
		}
	}
}

$result = $db->query('SELECT squadra,stemma FROM utenti WHERE id='.$db->escape($squadra));
$row = $result->fetch_assoc();
$nomesquadra = $row['squadra'];
$stemma = $row['stemma'];
$result->free();
$Moduli = array ('343','352','451','442','433','541','532');

$html = new html("Formazione ".$nomesquadra);
$html->title("Formazione",$stemma);
//print_r($_POST);
$result = $db->query('SELECT data,modulo FROM formazioni WHERE giornata='.$db->escape($giornata).' AND squadra='.$db->escape($squadra));
if ($row = $result->fetch_assoc())
{
	$modulo = $row['modulo'];
	if ($row['data'])
		$inviata = 'Inviata il '.$row['data'];
	else 
		$inviata = 'Non ancora inviata';
}
else
{
	$modulo = '343';
	$inviata = 'Non ancora inviata';
}
$result->free();
if($squadra == $user->id)
{
	$html->tabletitle('Parametri');
	echo <<< main
			<p>
			<form action="formazione.php?" method="post">
				<label for giornata>Giornata:</label>&nbsp;<select id=giornata name=giornata>
main;
	        $start_giornata = 1;
	        if ($g>1 && $g<=38)
	            $start_giornata = $g;
	        if (!getFormazioneStatus($g))
	            $start_giornata = $g+1;
			for($i = $start_giornata; $i <= 38; $i++)
			{
				echo '<option value="'.$i.'"';
				if ($i == $giornata)
					echo ' selected';
				echo '>'.$i.'</option>';
			}
			echo '</select>';
	echo <<< main
				<input type="submit" class="submit" name="cambiagiornata" id="cambiagiornata" value="Cambia Giornata">
				&nbsp;<label for modulo>Modulo:</label>
				&nbsp;<select id=modulo name=modulo>
main;
			
			foreach ($Moduli as $mod)
			{
				echo '<option value="'.$mod.'"';
				if ($modulo == $mod)
					echo ' selected';
				echo '>'.$mod.'</option>';
			}
			echo '</select>&nbsp;';
			$html->submit('Cambia Modulo');
		echo '</form></p>';
}
$html->tabletitle('Formazione giornata '.$giornata);
if (isset($error))
	$html->error($error);
echo <<< main
		<form action="formazione.php" method="post">
		<table class="table">
			<tr>
				<th>ruolo</th>
				<th>num</th>
				<th>nome</th>
			<tr>
main;
$Ruoli = array ('POR', 'DIF', 'CEN', 'ATT', '-');
if ($_POST['submit'] == 'Invia') //giocatori da post
	for ($i = 0; $i < 18; $i++)
		$Giocatori[$i]['giocatore'] = $_POST['giocatori'][$i];
else //giocatori da db
{
	if ($giornata <= $g) //query con controllo in giocgiorsq
		$result = $db->query('SELECT ruolo,nome,cognome,numero,giocgiorsq.giocatore AS giocatore FROM giocatori,giocatoriformazioni,giocgiorsq WHERE giocatori.id=giocatoriformazioni.giocatore AND giocatori.id=giocgiorsq.giocatore AND giocgiorsq.giornata='.$db->escape($giornata).' AND giocgiorsq.squadra='.$db->escape($squadra).' AND formazione=(SELECT id FROM formazioni WHERE giornata='.$db->escape($giornata).' AND squadra='.$db->escape($squadra).') ORDER BY giocatoriformazioni.pos');
	else //query senza controllo in giocgiorsq (non ci sono i record delle giornate future)
		$result = $db->query('SELECT ruolo,nome,cognome,numero,giocgiorsq.giocatore AS giocatore FROM giocatori,giocatoriformazioni,giocgiorsq WHERE giocatori.id=giocatoriformazioni.giocatore AND giocatori.id=giocgiorsq.giocatore AND giocgiorsq.giornata='.$g.' AND giocgiorsq.squadra='.$db->escape($squadra).' AND formazione=(SELECT id FROM formazioni WHERE giornata='.$db->escape($giornata).' AND squadra='.$db->escape($squadra).') ORDER BY giocatoriformazioni.pos');
	while ($row = $result->fetch_assoc())
		$Giocatori[] = $row;
	$result->free();
}
for ($i = 0; $i < 18; $i++)
{
	switch ($i)
	{
		case 11:
			echo '<tr><th colspan=3>&nbsp;</th></tr>';
		case 0:
			$ruolo = 0;
			break;
		case ($i <= substr($modulo,0,1)):
			$ruolo = 1;
			break;
		case ($i <= substr($modulo,0,1) + substr($modulo,1,1)):
			$ruolo = 2;
			break;
		case ($i <= substr($modulo,0,1) + substr($modulo,1,1) + substr($modulo,2,1)):
			$ruolo = 3;
			break;
		default:
			$ruolo = 4;
			$query = 'SELECT giocatore,nome,cognome,numero FROM giocatori,giocgiorsq WHERE giocatore=giocatori.id AND giornata='.$g.' AND squadra='.$db->escape($squadra).' ORDER BY numero';
			break;
	}
	if ($ruolo != 4)
		$query = 'SELECT giocatore,nome,cognome,numero FROM giocatori,giocgiorsq WHERE giocatore=giocatori.id AND giornata='.$g.' AND squadra='.$db->escape($squadra).' AND ruolo='.$ruolo.' ORDER BY numero';
	if ($squadra == $user->id || time() >= _MERCATOINIZ_CHIUSO)
	{
		if ($i % 2)
			echo '<tr>';
		else 
			echo '<tr class="disp">';
	}
	if ($squadra != $user->id || !getFormazioneStatus($giornata))
	{
		if ($squadra == $user->id || time() >= _MERCATOINIZ_CHIUSO)
			echo '	<td>'.$Ruoli[$Giocatori[$i]['ruolo']].'</td>
					<td>'.$Giocatori[$i]['numero'].'</td>
					<td>'.$html->linkgiocatore($Giocatori[$i]['giocatore'],$Giocatori[$i]['nome'].' <b>'.$Giocatori[$i]['cognome']).'</b></td>
				</tr>';
	}
	else
	{ 
		echo '		<td>'.$Ruoli[$ruolo].'</td>
					<td></td>
					<td><select id="giocatori[]" name="giocatori[]"><option value=0>Seleziona un giocatore</option>';
			$result = $db->query($query);
			while ($row = $result->fetch_assoc())
			{
				echo '<option value="'.$row['giocatore'].'"';
				if ($Giocatori[$i]['giocatore'] == $row['giocatore'])
					echo ' selected';
				echo '>'.$row['numero'].'.'.$row['nome'].' '.$row['cognome'].'</option>';
			}
			$result->free();
			echo 	'</select></td>
			</tr>';
	}
}

echo <<< main
			<tr>
				<th>$modulo</th>
				<th>$inviata</th>
				<th>
main;
				if (getFormazioneStatus($giornata) && ($squadra == $user->id))
					$html->submit('Invia');
				else if ($squadra == $user->id)
					echo 'Tempo scaduto!';
				$html->input('modulo',3,3,$modulo,'hidden');
				$html->input('giornata',2,2,$giornata,'hidden');
				echo <<< main
				</th>
			<tr>
		</table>
		</form>
main;

$db->close();
$html->close();
?>