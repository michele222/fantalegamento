<?php
include_once 'include/main.php';

$db = new db();
$html = new html("CAMBIA GIOCATORI");

$g = getGiornata();

$result = $db->query('SELECT giocatore FROM giocgiorsq WHERE squadra='.$user->id);
if($result->num_rows) //se è stata fatta la rosa iniziale
{
	$result->free();
	
	if(isset($_POST['submit']))
	{
		$Params = $_POST;
		$ok = 1;
		$user->reload(); //Aggiunto per correggere l'errore nei cambi
		$totale = $user->soldi;
		$cambi = $user->cambi;
		$result = $db->query('SELECT pos,numero,a.giocatore AS giocatore,a.prezzo AS prezzo,inlista FROM giocatorigiornate AS a, giocgiorsq AS b WHERE a.giocatore=b.giocatore AND a.giornata=(b.giornata-1) AND b.squadra='.$user->id.' AND b.giornata='.(getGiornata()).' ORDER BY pos');
		while ($row = $result->fetch_assoc())
		{
			$Giocatori[$row['pos']] = $row['giocatore']; //vecchi giocatori
			$Prezzi[$row['pos']] = $row['prezzo']; //prezzi dei vecchi giocatori
			$Num[$row['pos']] = $row['numero']; //numeri dei vecchi giocatori
			$InLista[$row['pos']] = $row['inlista']; //vecchio giocatore in lista?
		}
		$result->free();
		for ($i = 0; $i < 25; $i++)
			if ($Params['giocatori'][$i])
			{
				//prezzo nuovo giocatore
				$result = $db->query('SELECT prezzo, inlista FROM giocatorigiornate WHERE giocatore='.$Params['giocatori'][$i].' AND giornata='.(getGiornata()-1));
				$row = $result->fetch_assoc();
				$totale = $totale - $row['prezzo'] + $Prezzi[$i]; //totale=totale-prezzonuovo+prezzovecchio
				if ($InLista[$i])
					$cambi--;
				$result->free();
				for ($j = 0; $j < 25; $j++)
				{
					if (($i != $j) && (($Params['giocatori'][$i] == $Params['giocatori'][$j]) || ($Params['giocatori'][$i] == $Giocatori[$j])))
					{
						$ok = 0;
						$error = "Hai selezionato lo stesso giocatore pi&ugrave; volte";
					}
					else if (($i != $j) && (($Params['num'][$i] == $Params['num'][$j]) || ($Params['num'][$i] == $Num[$j])))
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
		if ($cambi < 0)
		{
			$ok = 0;
			$error = "Non hai a disposizione abbastanza cambi";
		}
		$fuorilista = getFuoriLista($user->id);
		if (!getMercatoStatus() && !is_array($fuorilista))
		{
			$ok = 0;
			$error = "Il mercato &egrave; chiuso";
		}
		if ($ok)
		{
			$commit = 1;
			$db->begin();
			for ($i = 0; $i < 25; $i++)
				if ($commit && $Params['giocatori'][$i])
				{
					$commit = $db->query('UPDATE giocgiorsq SET giocatore='.$Params['giocatori'][$i].',numero='.$Params['num'][$i].' WHERE giornata='.$g.' AND squadra='.$user->id.' AND pos='.$i);
					if ($commit)
						$commit = $db->query('UPDATE giocatoriformazioni SET giocatore='.$Params['giocatori'][$i].' WHERE giocatore='.$Giocatori[$i].' AND formazione IN (SELECT id FROM formazioni WHERE giornata>='.$g.' AND squadra='.$user->id.')');
				}
			if ($commit)
				$db->commit();
			else 
			{
				$db->rollback();
				$error = 'Errore nella compilazione della rosa: contattare l\'amministratore';
			}
			
			$result = $db->query('SELECT c.squadra AS squadrab,count(a.giocatore) AS incomune FROM giocgiorsq AS a, giocgiorsq AS b, utenti AS c WHERE a.giocatore=b.giocatore AND a.squadra<>b.squadra AND b.squadra=c.id AND a.giornata=b.giornata AND a.giornata='.$g.' AND a.squadra='.$user->id.' GROUP BY a.squadra,b.squadra HAVING COUNT(a.giocatore)>10');
			if($row = $result->fetch_assoc()) //due squadre troppo uguali
				$error = 'ATTENZIONE! La tua squadra &egrave; troppo simile a '.htmlspecialchars($row['squadrab']).': avete '.$row['incomune'].' giocatori in comune (il massimo &egrave; 10). Risolvi il problema o potresti incorrere in penalit&aacute;';
			
			$db->query('UPDATE utenti SET soldi='.$totale.', cambi='.$cambi.' WHERE id='.$user->id);
			$result->free();
		}
			
	}
}
else //rosa iniziale non ancora fatta
	echo 'Attenzione! Non hai ancora completato la rosa iniziale. <a href="rosainiziale.php">Clicca qui</a> per crearla.';
	//header('Location: rosainiziale.php');

$script = '
function changeGiocatore(row,select,tot)
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
	for (i=0;i<25;i++)
		if (document.getElementById("prezzo"+i).innerHTML)
			tot = tot - parseInt(document.getElementById("prezzo"+i).innerHTML) + parseInt(document.getElementById("prezzoold"+i).innerHTML);
	document.getElementById("tot").innerHTML = "resto: "+tot;
}';
$html->script($script);
$html->title('Cambia giocatori',$user->stemma);
//echo 'Il mercato &eacute; chiuso fino al termine della stagione';
$html->tabletitle('Cambi');
if (isset($error))
	$html->error($error);
echo <<< main
		<form action="cambia.php" method="post">
		<table class="table">
			<tr>
				<th>ruolo</th>
				<th>numero</th>
				<th>nome</th>
				<th>squadra</th>
				<th>prezzo</th>
				<th>numero</th>
				<th>sostituto</th>
				<th>squadra</th>
				<th>prezzo</th>
			<tr>
main;
$Ruoli = array ('POR', 'DIF', 'CEN', 'ATT');
$user->reload();
$cambi = $user->cambi;
$tot = $user->soldi;
$result = $db->query('SELECT b.id AS giocatore,b.nome AS nome,b.cognome AS cognome,b.ruolo AS ruolo,c.squadra AS squadra,c.prezzo AS prezzo,c.inlista AS inlista,c.proprietari AS proprietari,d.numero AS numero FROM giocatori AS b, datiultimagiornata AS c,giocgiorsq AS d WHERE b.id=c.giocatore AND c.giocatore=d.giocatore AND d.giornata='.getGiornata().' AND d.squadra='.$db->escape($user->id).' GROUP BY d.pos order by d.pos');
$i = 0;
while ($row = $result->fetch_assoc())
{
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	echo '		<td>'.$Ruoli[$row['ruolo']].'</td>
				<td>'.$row['numero'].'</td>
				<td>'.$html->linkgiocatore($row['giocatore'],$row['nome'].' <b>'.$row['cognome']).'</b></td>
				<td>'.$html->linksquadraa($row['squadra']).'</td>
				<td id="prezzoold'.$i.'" name="prezzoold'.$i.'">'.$row['prezzo'].'</td>
				<td><select id="num[]" name="num[]">';
	for ($j = 1; $j < 100; $j++)
	{
		echo '<option value="'.$j.'"';
		if ($row['numero'] == $j)
			echo ' selected';
		echo '>'.$j.'</option>';
	}
	echo		'</select></td>
				<td><select id="giocatori[]" name="giocatori[]" onchange=changeGiocatore('.$i.',this,'.$tot.')><option value=0>Non cambiare</option>';
	
	$squadra = $prezzo = '';
	$Giocatori_cambiabili = array();
	if ($g == (_G_MERCATO_PARZIALE_2+1))
	{
		$result1 = $db->query('SELECT a.giocatore AS giocatore FROM giocatorigiornate AS a LEFT JOIN giocatorigiornate AS b ON a.giocatore=b.giocatore WHERE a.giornata='.$db->escape(_G_MERCATO_PARZIALE_1).' AND b.giornata='.$db->escape(_G_MERCATO_PARZIALE_2).' AND (a.squadra<>b.squadra OR b.inlista=0)');
		while ($row1 = $result1->fetch_assoc())
			$Giocatori_cambiabili[] = $row1['giocatore'];
		$result1->free();
	}
	
	if ($g != (_G_MERCATO_PARZIALE_2+1) || in_array($row['giocatore'], $Giocatori_cambiabili))
	{
		if (time() < _MERCATOINIZ_CHIUSO) //Versione con cambi liberi (inizio campionato)
			$result1 = $db->query('SELECT giocatore,nome,cognome,squadra,prezzo FROM giocatorigiornate,giocatori WHERE giocatore=giocatori.id AND giornata='.(getGiornata()-1).' AND ruolo='.$row['ruolo'].' AND inlista=1');
		else //Versione normale (5 cambi)
			$result1 = $db->query('SELECT giocatore,nome,cognome,squadra,prezzo FROM giocatorigiornate,giocatori WHERE giocatore=giocatori.id AND giornata='.(getGiornata()-1).' AND ruolo='.$row['ruolo'].' AND inlista=1 AND giocatori.id NOT IN (SELECT DISTINCT giocatore FROM giocgiorsq WHERE giornata='.(getGiornata()-1).') ORDER BY cognome');
		//$result = $db->query('SELECT giocatore,nome,cognome,squadra,prezzo FROM lista WHERE ruolo='.$ruolo);
		while ($row1 = $result1->fetch_assoc())
				{
					echo '<option value="'.$row1['giocatore'].'"';
					if ($Params['giocatori'][$i] == $row1['giocatore'])
					{
						echo ' selected';
						$squadra = $row1['squadra'];
						$prezzo = $row1['prezzo'];
					}
					echo '>'.$row1['nome'].' '.$row1['cognome'].' ('.$row1['squadra'].' - '.$row1['prezzo'].')</option>';
				}
		$result1->free();
		$tot -= $prezzo;
	}
	
	echo 	'</select></td>
				<td id="squadra'.$i.'" name="squadra'.$i.'">'.$squadra.'</td>
				<td id="prezzo'.$i.'" name="prezzo'.$i.'">'.$prezzo.'</td>
			</tr>';
	$i++;
}
$result->free();
echo <<< main
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th>
main;
				$fuorilista = getFuoriLista($user->id);
				if (getMercatoStatus())// || is_array($fuorilista))
					$html->submit('Invia');
				else
					echo 'Mercato chiuso';
				echo <<< main
				</th>
				<th id="cambi" name="cambi">cambi: $cambi</th>
				<th id="tot" name="tot">resto: $tot</th>
			<tr>
		</table>
		</form>
main;

$db->close();
$html->close();
?>