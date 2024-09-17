<?php
include_once 'include/main.php';

$db = new db();
$g_attuale = getGiornata();

if ($_GET['id'] && ctype_digit($_GET['id']) && $_GET['id'] > 0 && $_GET['id'] <= $g_attuale)
	$giornata = $_GET['id'];
else if ($_POST['giornata'])
	$giornata = $_POST['giornata'];
else
	$giornata = $g_attuale-1;

$html = new html("MOVIMENTI GIOCATORI");
$html->title("Movimenti giocatori",_SERIEALOGO);

echo <<< main
		<p>
		<form action="movimenti.php" method="post">
			<label for giornata>Visualizza movimenti da giornata:</label>&nbsp;<select id=giornata name=giornata>
main;
		for($i = 1; $i <= $g_attuale; $i++)
		{
			echo '<option value="'.$i.'"';
			if ($i == $giornata)
				echo ' selected';
			echo '>'.$i.'</option>';
		}
		echo '</select>';
		$html->submit('OK');
	echo '</p></form>';
$html->tabletitle('Giocatori usciti dalle liste');
echo <<< main
	<table class="table">
		<tr>
			<th>giornata</th>
			<th>ruolo</th>
			<th>nome</th>
			<th>squadra</th>
			<th>prezzo</th>
		<tr>
main;
$Ruoli = array ('POR', 'DIF', 'CEN', 'ATT');
$result = $db->query('SELECT b.giornata, b.squadra, b.giocatore, b.prezzo FROM giocatorigiornate AS a,giocatorigiornate AS b WHERE a.giornata=b.giornata-1 AND a.giocatore=b.giocatore AND b.inlista=0 AND a.inlista=1 AND b.giornata>='.$db->escape($giornata).' ORDER BY b.giornata DESC');
$i = 0;
while ($row = $result->fetch_assoc())
{
	$result1 = $db->query('SELECT cognome, nome, ruolo FROM giocatori WHERE id='.$db->escape($row['giocatore']));
	$gioc1 = $result1->fetch_assoc();
	$result1->free();
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	echo '		<td>'.$row['giornata'].'</td>
				<td>'.$Ruoli[$gioc1['ruolo']].'</td>
				<td>'.$html->linkgiocatore($row['giocatore'],$gioc1['nome'].' <b>'.$gioc1['cognome']).'</b></td>
				<td>'.$html->linksquadraa($row['squadra']).'</td>
				<td>'.$row['prezzo'].'</td>
				';
	echo '</tr>';
	$i++;
}
echo '</table>';
$result->free();

$html->tabletitle('Nuovi giocatori');
echo <<< main
	<table class="table">
		<tr>
			<th>giornata</th>
			<th>ruolo</th>
			<th>nome</th>
			<th>squadra</th>
			<th>prezzo</th>
		<tr>
main;
$result = $db->query('SELECT MIN(giornata) AS giornata, giocatore FROM giocatorigiornate WHERE giornata>='.$db->escape($giornata).' AND giocatore NOT IN (SELECT giocatore FROM giocatorigiornate WHERE giornata<'.$db->escape($giornata).') GROUP BY giocatore ORDER BY giornata DESC');
$i = 0;
while ($row = $result->fetch_assoc())
{
	$result1 = $db->query('SELECT cognome, nome, ruolo, squadra, prezzo FROM giocatori,giocatorigiornate WHERE giocatori.id=giocatorigiornate.giocatore AND giornata='.$db->escape($row['giornata']).' AND giocatori.id='.$db->escape($row['giocatore']));
	$gioc1 = $result1->fetch_assoc();
	$result1->free();
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	echo '		<td>'.$row['giornata'].'</td>
				<td>'.$Ruoli[$gioc1['ruolo']].'</td>
				<td>'.$html->linkgiocatore($row['giocatore'],$gioc1['nome'].' <b>'.$gioc1['cognome']).'</b></td>
				<td>'.$html->linksquadraa($gioc1['squadra']).'</td>
				<td>'.$gioc1['prezzo'].'</td>
				';
	echo '</tr>';
	$i++;
}
echo '</table>';
$result->free();

$html->tabletitle('Giocatori che hanno cambiato squadra');
echo <<< main
	<table class="table">
		<tr>
			<th>giornata</th>
			<th>ruolo</th>
			<th>nome</th>
			<th>vecchia squadra</th>
			<th>nuova squadra</th>
			<th>prezzo</th>
		<tr>
main;
$result = $db->query('SELECT b.giornata, a.squadra AS squadra1, b.squadra AS squadra2, b.giocatore, b.prezzo FROM giocatorigiornate AS a,giocatorigiornate AS b WHERE a.giornata=b.giornata-1 AND a.giocatore=b.giocatore AND a.squadra<>b.squadra AND b.giornata>='.$db->escape($giornata).' ORDER BY b.giornata DESC');
$i = 0;
while ($row = $result->fetch_assoc())
{
	$result1 = $db->query('SELECT cognome, nome, ruolo FROM giocatori WHERE id='.$db->escape($row['giocatore']));
	$gioc1 = $result1->fetch_assoc();
	$result1->free();
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	echo '		<td>'.$row['giornata'].'</td>
				<td>'.$Ruoli[$gioc1['ruolo']].'</td>
				<td>'.$html->linkgiocatore($row['giocatore'],$gioc1['nome'].' <b>'.$gioc1['cognome']).'</b></td>
				<td>'.$html->linksquadraa($row['squadra1']).'</td>
				<td>'.$html->linksquadraa($row['squadra2']).'</td>
				<td>'.$row['prezzo'].'</td>
				';
	echo '</tr>';
	$i++;
}
echo '</table>';
$result->free();

$db->close();
$html->close();
?>