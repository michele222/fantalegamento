<?php
include_once 'include/main.php';

$db = new db();
$g_attuale = getGiornata();

if ($_GET['id'] && ctype_digit($_GET['id']) && $_GET['id'] > 1 && $_GET['id'] < $g_attuale)
	$giornata = $_GET['id'];
else if ($_POST['giornata'])
	$giornata = $_POST['giornata'];
else
	$giornata = $g_attuale-1;

$html = new html("GIORNATA ".$giornata);
$html->title("Giornata ".$giornata,_FLLOGO);

$html->tabletitle('Selezione giornata');
echo <<< main
		<p>
		<form action="giornata.php?" method="post">
			<label for giornata>Giornata:</label>&nbsp;<select id=giornata name=giornata>
main;
		for($i = 1; $i < $g_attuale; $i++)
		{
			echo '<option value="'.$i.'"';
			if ($i == $giornata)
				echo ' selected';
			echo '>'.$i.'</option>';
		}
		echo '</select>';
		$html->submit('OK');
	echo '</p></form>';
$html->tabletitle('Classifica di giornata');
echo <<< main
	<table class="table">
		<tr>
			<th>pos</th>
			<th>squadra</th>
			<th>punti</th>
			<th>gol</th>
			<th>gol panc</th>
			<th>pti panc</th>
			<th>pti max</th>
			<th>voto max</th>
			<th>pti max panc</th>
			<th>voto max panc</th>
			<th>ranking prec</th>
			<th>tifosi</th>
		<tr>
main;
$result = $db->query('SELECT utenti.id AS id,utenti.squadra AS squadra,punti,formazioni.ordine AS ordine,goltit,golpan,ptipan,ptimax,votmax,ptimaxpan,votmaxpan,rank,formazioni.tifosi AS tifosi FROM formazioni,utenti WHERE formazioni.squadra=utenti.id AND giornata='.$db->escape($giornata).' AND formazioni.ordine>0 ORDER BY formazioni.ordine');
$i = 0;
while ($row = $result->fetch_assoc())
{
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	echo '		<td>'.$row['ordine'].'</td>
				<td><b>'.$html->linksquadra($row['id'],$row['squadra']).'</b></td>
				<td><b>'.$html->linkpunteggio($row['id'],$giornata,$row['punti']).'</b></td>
				<td>'.$row['goltit'].'</td>
				<td>'.$row['golpan'].'</td>
				<td>'.$row['ptipan'].'</td>
				<td>'.$row['ptimax'].'</td>
				<td>'.$row['votmax'].'</td>
				<td>'.$row['ptimaxpan'].'</td>
				<td>'.$row['votmaxpan'].'</td>
				<td>'.$row['rank'].'</td>
				<td>'.$row['tifosi'].'</td>
				';
	echo '</tr>';
	$i++;
}
echo '</table>';
$result->free();

$db->close();
$html->close();
?>