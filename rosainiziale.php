<?php
include_once 'include/main.php';

$db = new db();
$html = new html("ROSA INIZIALE");

$g = getGiornata(); //giornata attuale

$result = $db->query('SELECT giocatore FROM giocgiorsq WHERE squadra='.$user->id);
if(!$result->num_rows) //se non è ancora stata fatta la rosa iniziale
{
	$result->free();
	
	if(isset($_POST['submit']))
	{
		$Params = $_POST;
		$ok = 1;
		$totale = 250;
		for ($i = 0; $i < 25; $i++)
		{
			$result = $db->query('SELECT prezzo FROM giocatorigiornate WHERE giocatore='.$Params['giocatori'][$i].' AND giornata='.(getGiornata()-1));
			$row = $result->fetch_assoc();
			$totale -= $row['prezzo'];
			$result->free();
			if (!$Params['giocatori'][$i])
			{
				$ok = 0;
				$error = "Non hai selezionato tutti i 25 giocatori";
			}
			else
				for ($j = 0; $j < $i; $j++)
				{
					if (($i != $j) && ($Params['giocatori'][$i] == $Params['giocatori'][$j]))
					{
						$ok = 0;
						$error = "Hai selezionato lo stesso giocatore pi&ugrave; volte";
					}
					else if (($i != $j) && ($Params['num'][$i] == $Params['num'][$j]))
					{
						$ok = 0;
						$error = "Hai assegnato lo stesso numero a pi&ugrave; giocatori";
					}
				}
		}
		if ($totale < 0)
		{
			$ok = 0;
			$error = "Non hai a disposizione abbastanza soldi";
		}
		if (!getMercatoStatus() && !getFormazioneStatus($g))
		{
			$ok = 0;
			$error = "Il mercato &egrave; chiuso";
		}
		if ($ok)
		{
			$commit = 1;
			$db->begin();
			for ($i = 0; $i < 25; $i++)
				if ($commit)
					$commit = $db->query('INSERT INTO giocgiorsq (giocatore,giornata,squadra,pos,numero) VALUES ('.$Params['giocatori'][$i].','.$g.','.$user->id.','.$i.','.$Params['num'][$i].')');
			if ($commit)
				$db->commit();
			else 
			{
				$db->rollback();
				$error = 'Errore nella compilazione della rosa: contattare l\'amministratore';
			}
			
			$result = $db->query('SELECT c.squadra AS squadrab,count(a.giocatore) AS incomune FROM giocgiorsq AS a, giocgiorsq AS b, utenti AS c WHERE a.giocatore=b.giocatore AND a.squadra<>b.squadra AND b.squadra=c.id AND a.giornata=b.giornata AND a.giornata='.$g.' AND a.squadra='.$user->id.' GROUP BY a.squadra,b.squadra HAVING COUNT(a.giocatore)>10');
			if($row = $result->fetch_assoc()) //due squadre troppo uguali
			{
				$commit = 0;
				$error = 'La tua squadra &egrave; troppo simile a '.htmlspecialchars($row['squadrab']).': avete '.$row['incomune'].' giocatori in comune (il massimo &egrave; 10)';
				$db->query('DELETE FROM giocgiorsq WHERE giornata='.$g.' AND squadra='.$user->id);
			}
			else
			{
				$db->query('UPDATE utenti SET soldi='.$totale.', tifosi=(tifosi+'.((250-$totale)*160).') WHERE id='.$user->id);
				header('Location: squadra.php');
			}
			$result->free();
		}
			
	}
	else 
	{
		$Params['num'] = array (1,12,22,2,3,4,5,13,14,15,16,6,7,8,17,18,19,20,21,9,10,11,23,24,25); 
	}
}
else //rosa iniziale già fatta
{
	header('Location: squadra.php');
	$error = 'Hai gi&agrave; presentato la rosa iniziale per questa stagione: usa la pagina MERCATO-CAMBI per cambiare giocatori';
}
$script = '
function changeGiocatore(row,select)
{
	if(select.selectedIndex == 0)
	{
		document.getElementById("squadra"+row).innerHTML = "";
		document.getElementById("prezzo"+row).innerHTML = "";
	}
	else
	{
		document.getElementById("squadra"+row).innerHTML = select.options[select.selectedIndex].text.split("(")[1].split(" ")[0];
		document.getElementById("prezzo"+row).innerHTML = select.options[select.selectedIndex].text.split("(")[1].split(" ")[2].split(")")[0];
	}
	tot = 250;
	for (i=0;i<25;i++)
		tot = tot - document.getElementById("prezzo"+i).innerHTML;
	document.getElementById("tot").innerHTML = "resto: "+tot;
}';
$html->script($script);
$html->title('Rosa iniziale',$user->stemma);
//echo 'Il mercato &eacute; chiuso fino al termine della stagione';
$html->tabletitle('Rosa');
if (isset($error))
	$html->error($error);
//print_r($_POST);
echo <<< main
		<form action="rosainiziale.php" method="post">
		<table class="table">
			<tr>
				<th>ruolo</th>
				<th>numero</th>
				<th>nome</th>
				<th>squadra</th>
				<th>prezzo</th>
			<tr>
main;
$Ruoli = array ('POR', 'DIF', 'CEN', 'ATT');
$ruolo = 0;
$tot = 250;
for ($i = 0; $i < 25; $i++)
{
	switch ($i)
	{
		case 3: $ruolo = 1;
				break;
		case 11: $ruolo = 2;
				break;
		case 19: $ruolo = 3;
				break;
	}
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	echo '		<td>'.$Ruoli[$ruolo].'</td>
				<td><select id="num[]" name="num[]">';
	for ($j = 1; $j < 100; $j++)
	{
		echo '<option value="'.$j.'"';
		if ($Params['num'][$i] == $j)
			echo ' selected';
		echo '>'.$j.'</option>';
	}
	echo		'</select></td>
				<td><select id="giocatori[]" name="giocatori[]" onchange=changeGiocatore('.$i.',this)><option value=0>Seleziona un giocatore</option>';
	
	$squadra = $prezzo = '';
	//$result = $db->query('SELECT giocatore,nome,cognome,squadra,prezzo FROM giocatorigiornate,giocatori WHERE giocatore=giocatori.id AND giornata='.(getGiornata()-1).' AND ruolo='.$ruolo.' AND inlista=1 ORDER BY cognome');
	$result = $db->query('SELECT giocatore,nome,cognome,squadra,prezzo FROM giocatorigiornate,giocatori WHERE giocatore=giocatori.id AND giornata='.(getGiornata()-1).' AND ruolo='.$ruolo.' AND inlista=1 AND giocatori.id NOT IN (SELECT DISTINCT giocatore FROM giocgiorsq WHERE giornata='.(getGiornata()-1).') ORDER BY cognome');
	//$result = $db->query('SELECT giocatore,nome,cognome,squadra,prezzo FROM lista WHERE ruolo='.$ruolo);
	while ($row = $result->fetch_assoc())
			{
				echo '<option value="'.$row['giocatore'].'"';
				if ($Params['giocatori'][$i] == $row['giocatore'])
				{
					echo ' selected';
					$squadra = $row['squadra'];
					$prezzo = $row['prezzo'];
				}
				echo '>'.$row['nome'].' '.$row['cognome'].' ('.$row['squadra'].' - '.$row['prezzo'].')</option>';
			}
	$result->free();
	$tot -= $prezzo;
	echo 	'</select></td>
				<td id="squadra'.$i.'" name="squadra'.$i.'">'.$squadra.'</td>
				<td id="prezzo'.$i.'" name="prezzo'.$i.'">'.$prezzo.'</td>
			</tr>';
}
echo <<< main
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th>
main;
				//if (getMercatoStatus() || getFormazioneStatus($g))    //se non voglio iscritti a mercato chiuso
				if (getFormazioneStatus($g))      //se voglio altri iscritti a mercato chiuso
					$html->submit('Invia');
				else
					echo 'Mercato chiuso';
				echo <<< main
				</th>
				<th id="tot" name="tot">resto: $tot</th>
			<tr>
		</table>
		</form>
main;

$db->close();
$html->close();
?>