<?php
include_once 'include/main.php';

if($_GET['id'] && ctype_digit($_GET['id']))
	$squadra = $_GET['id'];
else if (!$user->guest)
	$squadra = $user->id;
else 
	$squadra = 1;

$db = new db();

$result = $db->query('SELECT id,squadra,stemma,nazionalita,nome,cognome,allenatore,stadio FROM utenti WHERE id='.$db->escape($squadra));
$row = $result->fetch_assoc();
$nomesquadra = $row['squadra'];
$stemma = $row['stemma'];
$nazionalita = $row['nazionalita'];
$presidente = $row['nome'].' '.$row['cognome'];
$allenatore = $row['allenatore'];
$stadio = $row['stadio'];
$result->free();

$html = new html($nomesquadra);

$html->title_squadra($row);
$html->tabletitle('Rosa');

echo <<< main
		<table class="table">
			<tr>
				<th>ruolo</th>
				<th>num</th>
				<th>nome</th>
				<th>squadra</th>
				<th>prezzo</th>
				<th>presenze</th>
				<th>mv</th>
				<th>gol</th>
				<th>gv</th>
				<th>gp</th>
				<th>assist</th>
				<th>amm</th>
				<th>esp</th>
				<th>rig</th>
				<th>aut</th>
			</tr>
main;
if ($squadra == $user->id || time() >= _MERCATOINIZ_CHIUSO)
{
	$Ruoli = array ('POR', 'DIF', 'CEN', 'ATT');
	$result = $db->query('SELECT a.giocatore AS giocatore,b.nome AS nome,b.cognome AS cognome,b.ruolo AS ruolo,c.squadra AS squadra,c.prezzo AS prezzo,c.inlista AS inlista,c.proprietari AS proprietari,d.numero AS numero,sum(a.giocato) AS giocato,sum(a.6politico) AS 6politico,(sum((a.famedia * a.voto)) / sum(a.famedia)) AS media,sum(a.gfatti) AS gfatti,sum(a.gsubiti) AS gsubiti,sum(a.gv) AS gv,sum(a.gp) AS gp,sum(a.assist) AS assist,sum(a.ammonito) AS ammonito,sum(a.espulso) AS espulso,sum(a.rtirati) AS rtirati,sum(a.rcontro) AS rcontro,sum(a.rparati) AS rparati,sum(a.rsbagliati) AS rsbagliati,sum(a.autogol) AS autogol,sum(a.presente) AS presente,sum(a.titolare) AS titolare,sum(a.25min) AS 25min FROM giocatorigiornate AS a, giocatori AS b, datiultimagiornata AS c,giocgiorsq AS d WHERE a.giocatore=b.id AND a.giocatore=c.giocatore AND a.giocatore=d.giocatore AND a.giornata=(d.giornata-1) AND d.squadra='.$db->escape($squadra).' GROUP BY a.giocatore HAVING a.giocatore IN (SELECT giocatore FROM giocgiorsq WHERE giornata='.getGiornata().' AND squadra='.$db->escape($squadra).') ORDER BY d.pos');
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
					<td>'.$row['prezzo'].'</td>
					<td>'.$row['giocato'].'('.$row['titolare'].')</td>
					<td>'.number_format($row['media'],2).'</td>';
		if ($row['ruolo'])
			echo	'<td>'.$row['gfatti'].'</td>';
		else //portiere
			echo	'<td>-'.$row['gsubiti'].'</td>';
		echo 		'<td>'.$row['gv'].'</td>
					<td>'.$row['gp'].'</td>
					<td>'.$row['assist'].'</td>
					<td>'.$row['ammonito'].'</td>
					<td>'.$row['espulso'].'</td>';
		if ($row['ruolo'])
			echo 	'<td>'.($row['rtirati']-$row['rsbagliati']).'/'.$row['rtirati'].'</td>';
		else //portiere
			echo	'<td>'.$row['rparati'].'/'.$row['rcontro'].'</td>';
		echo		'<td>'.$row['autogol'].'</td>
				</tr>';
		$i++;
	}
	$result->free();
}
else 
	echo '<tr>
			<td colspan=15>Le rose sono nascoste fino al termine del mercato iniziale</td>
		  </tr>';
/*
$html->tabletitle('Cambi');

echo <<< main
		<table class="table">
			<tr>
				<th>giornata</th>
				<th>ruolo</th>
				<th>num</th>
				<th>nome (acquistato)</th>
				<th>squadra</th>
				<th>prezzo</th>
				<th>num</th>
				<th>nome (ceduto)</th>
				<th>squadra</th>
				<th>prezzo</th>
			<tr>
main;
$result = $db->query('SELECT a.giornata AS giornata,a.numero AS num1,b.numero AS num2,c.nome AS nome1,c.cognome AS cognome1,d.nome AS nome2,d.cognome AS cognome2,e.prezzo AS prezzo1,f.prezzo AS prezzo2 FROM giocgiorsq AS a, giocgiorsq AS b, giocatori AS c, giocatori AS d,(SELECT giocatore,giornata,prezzo FROM giocatorigiornate)AS e,(SELECT giocatore,giornata,prezzo FROM giocatorigiornate)AS f WHERE a.squadra=b.squadra AND a.squadra='.$db->escape($squadra).' AND a.pos=b.pos AND a.giornata=(b.giornata-1) AND a.giornata=e.giornata AND (b.giornata-1)=f.giornata AND a.giocatore=e.giocatore AND b.giocatore=f.giocatore AND a.giocatore=c.id AND b.giocatore=d.id ORDER BY a.giornata');
$i = 0;
while ($row = $result->fetch_assoc())
{
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	echo '		<td>'.$Ruoli[$row['ruolo']].'</td>
				<td>'.$row['numero'].'</td>
				<td>'.$row['nome'].' <b>'.$row['cognome'].'</b></td>
				<td>'.$row['squadra'].'</td>
				<td>'.$row['prezzo'].'</td>
				<td>'.$row['giocato'].'('.$row['titolare'].')</td>
				<td>'.number_format($row['media'],2).'</td>';
	if ($row['ruolo'])
		echo	'<td>'.$row['gfatti'].'</td>';
	else //portiere
		echo	'<td>-'.$row['gsubiti'].'</td>';
	echo 		'<td>'.$row['gv'].'</td>
				<td>'.$row['gp'].'</td>
				<td>'.$row['assist'].'</td>
				<td>'.$row['ammonito'].'</td>
				<td>'.$row['espulso'].'</td>';
	if ($row['ruolo'])
		echo 	'<td>'.($row['rtirati']-$row['rsbagliati']).'/'.$row['rtirati'].'</td>';
	else //portiere
		echo	'<td>'.$row['rparati'].'/'.$row['rcontro'].'</td>';
	echo		'<td>'.$row['autogol'].'</td>
			</tr>';
	$i++;
}
$result->free();
*/
echo '</table>';

$db->close();
$html->close();
?>