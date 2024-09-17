<?php
include_once 'include/main.php';

if($_GET['id'] && ctype_digit($_GET['id']))
	$giocatore = $_GET['id'];
else 
	$giocatore = 101;

$db = new db();

$g = getGiornata();

$result = $db->query('SELECT * FROM giocatori,datiultimagiornata WHERE id=giocatore AND id='.$db->escape($giocatore));
if ($row = $result->fetch_assoc())
{
	$Ruoli = array('Portiere','Difensore','Centrocampista','Attaccante');
	$nome = $row['nome'].' '.$row['cognome'];
	$squadra = $row['squadra'];
	$inlista = $row['inlista'];
	$stemma = 'images/stemmia/'.strtolower($row['squadra']).'.png';
	$prezzo = $row['prezzo'];
	$ruolo = $Ruoli[$row['ruolo']];
	$stadio = $row['stadio'];
}
else 
	header('index.php');
$result->free();

$html = new html($nome);

$html->title($nome,$stemma);
echo 'Ruolo: <i>'.$ruolo.'</i> - Squadra: <i>'.$squadra.'</i> - Prezzo: <i>'.$prezzo.'</i>';
if (!$inlista)
	echo '<i> (FUORI LISTA)</i>';

if (time() >= _MERCATOINIZ_CHIUSO)
{
	$html->tabletitle('Proprietari');
	echo <<< main
		<table class="table">
			<tr>
				<th>squadra</th>
				<th>num</th>
				<th>gior. acquisto</th>
				<th>gior. vendita</th>
				<th>pr. acquisto</th>
				<th>pr. vendita</th>
			<tr>
main;

	$result = $db->query('SELECT squadra,numero,MIN(giocgiorsq.giornata) as acq,MAX(giocgiorsq.giornata) as ven FROM giocgiorsq WHERE giocatore='.$db->escape($giocatore).' GROUP BY squadra, numero ORDER BY MIN(giocgiorsq.giornata)');
	$i = 0;
	while ($row = $result->fetch_assoc())
	{
		$row1 = $db->query('SELECT prezzo FROM giocatorigiornate WHERE giocatore='.$db->escape($giocatore).' AND giornata='.($row['acq']-1))->fetch_assoc();
		$prezzoacq = $row1['prezzo'];
		$row1 = $db->query('SELECT prezzo FROM giocatorigiornate WHERE giocatore='.$db->escape($giocatore).' AND giornata='.($row['ven']))->fetch_assoc();
		$prezzoven = $row1['prezzo'];
		if ($row['ven'] == $g)
		{
			$row['ven'] = '-';
			$prezzoven = '-';
		}
		if($i % 2)
			echo '<tr>';
		else 
			echo '<tr class="disp">';
		echo '		<td><b>'.$html->linksquadra($row['squadra'],getSquadraNome($row['squadra'])).'</b></td>
					<td>'.$row['numero'].'</td>
					<td>'.$row['acq'].'</td>
					<td>'.$row['ven'].'</td>
					<td>'.$prezzoacq.'</td>
					<td>'.$prezzoven.'</td>
				</tr>';
		$i++;
	}
	$result->free();
	echo '</table>';
}
	
$html->tabletitle('Statistiche');

echo <<< main
		<table class="table">
			<tr>
				<th>giornata</th>
				<th>squadra</th>
				<th>prezzo</th>
				<th>presente</th>
				<th>voto</th>
				<th>punti</th>
				<th>gol</th>
				<th>gv</th>
				<th>gp</th>
				<th>assist</th>
				<th>amm</th>
				<th>esp</th>
				<th>rig</th>
				<th>aut</th>
			<tr>
main;

$result = $db->query('SELECT giornata,squadra,prezzo,inlista,giocato,magicpunti,voto,gfatti,gsubiti,gv,gp,assist,ammonito,espulso,rtirati,rcontro,rparati,rsbagliati,autogol,titolare FROM giocatorigiornate WHERE giocatore='.$db->escape($giocatore).' ORDER BY giornata');
$i = 0;
while ($row = $result->fetch_assoc())
{
	if ($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	echo '		<td>'.$row['giornata'].'</td>';
	if ($row['inlista'])
	{
		echo '		<td>'.$html->linksquadraa($row['squadra']).'</td>
					<td>'.$row['prezzo'].'</td>
					<td>'.$row['giocato'].'('.$row['titolare'].')</td>
					<td>'.number_format($row['voto'],1).'</td>
					<td>'.number_format($row['magicpunti'],1).'</td>';
		if ($giocatore > 200)
			echo	'<td>'.$row['gfatti'].'</td>';
		else //portiere
			echo	'<td>-'.$row['gsubiti'].'</td>';
		echo 		'<td>'.$row['gv'].'</td>
					<td>'.$row['gp'].'</td>
					<td>'.$row['assist'].'</td>
					<td>'.$row['ammonito'].'</td>
					<td>'.$row['espulso'].'</td>';
		if ($giocatore > 200)
			echo 	'<td>'.($row['rtirati']-$row['rsbagliati']).'/'.$row['rtirati'].'</td>';
		else //portiere
			echo	'<td>'.$row['rparati'].'/'.$row['rcontro'].'</td>';
		echo		'<td>'.$row['autogol'].'</td>';
	}
	else 
		echo '<td colspan=13>Fuori lista</td>';
	echo '	</tr>';
	$i++;
}
$result->free();

echo '</table>';

$db->close();
$html->close();
?>