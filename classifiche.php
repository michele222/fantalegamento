<?php
include_once 'include/main.php';

$db = new db();
$g = getGiornata();

if ($_GET['id'] && ctype_digit($_GET['id']) && $_GET['id'] > 1 && $_GET['id'] < $g)
	$giornata = $_GET['id'];
else if ($_POST['giornata'])
	$giornata = $_POST['giornata'];
else
	$giornata = $g-1;

$html = new html("CLASSIFICHE");
$html->title("Classifiche alla giornata ".$giornata,_FLLOGO);

$html->tabletitle('Selezione giornata');
echo <<< main
		<p>
		<form action="classifiche.php?" method="post">
			<label for giornata>Giornata:</label>&nbsp;<select id=giornata name=giornata>
main;
		for($i = 1; $i < $g; $i++)
		{
			echo '<option value="'.$i.'"';
			if ($i == $giornata)
				echo ' selected';
			echo '>'.$i.'</option>';
		}
		echo '</select>';
		$html->submit('OK');
	echo '</p></form>';
$html->tabletitle('Ranking');
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
		<tr>
main;
$P = array(); //serve per classifica punti partecipazione
$result = $db->query('SELECT utenti.id AS id,utenti.squadra AS squadra,utenti.ordine AS ordine,SUM(punti) AS punti,SUM(goltit) AS goltit,SUM(golpan) AS golpan,SUM(ptipan) AS ptipan,MAX(ptimax) AS ptimax,MAX(votmax) AS votmax,MAX(ptimaxpan) AS ptimaxpan,MAX(votmaxpan) AS votmaxpan FROM formazioni,utenti WHERE formazioni.squadra=utenti.id AND giornata<='.$db->escape($giornata).' GROUP BY utenti.squadra ORDER BY punti DESC, goltit DESC, golpan DESC, ptipan DESC, ptimax DESC, votmax DESC, ptimaxpan DESC, votmaxpan DESC');
$i = 0;
while ($row = $result->fetch_assoc())
{
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	$i++;
	echo '		<td>'.$i.'</td>
				<td><b>'.$html->linksquadra($row['id'],$row['squadra']).'</b></td>
				<td><b>'.$row['punti'].'</b></td>
				<td>'.$row['goltit'].'</td>
				<td>'.$row['golpan'].'</td>
				<td>'.$row['ptipan'].'</td>
				<td>'.$row['ptimax'].'</td>
				<td>'.$row['votmax'].'</td>
				<td>'.$row['ptimaxpan'].'</td>
				<td>'.$row['votmaxpan'].'</td>
				';
	echo '</tr>';
	
	//serve per classifica punti partecipazione
	$P['ranking'][$row['id']] = $i;
	$P['mercati'][$row['id']] = 0;
	$P['rose'][$row['id']] = 0;
	$P['formazioni'][$row['id']] = 0;
	$P['nomi'][$row['id']] = $row['squadra'];
}
echo '</table>';
$result->free();
$html->tabletitle('Medagliere');
echo <<< main
	<table class="table">
		<tr>
			<th>pos</th>
			<th>squadra</th>
			<th>1</th>
			<th>2</th>
			<th>3</th>
		<tr>
main;
$result = $db->query('SELECT utenti.id AS id,utenti.squadra AS squadra, pri.n AS primi, sec.n AS secondi, ter.n AS terzi FROM utenti LEFT JOIN (SELECT squadra,count(squadra) AS n FROM formazioni WHERE giornata<='.$db->escape($giornata).' AND ordine=1 GROUP BY squadra) AS pri ON utenti.id=pri.squadra LEFT JOIN (SELECT squadra,count(squadra) AS n FROM formazioni WHERE giornata<='.$db->escape($giornata).' AND ordine=2 GROUP BY squadra) AS sec ON utenti.id=sec.squadra LEFT JOIN (SELECT squadra,count(squadra) AS n FROM formazioni WHERE giornata<='.$db->escape($giornata).' AND ordine=3 GROUP BY squadra) AS ter ON utenti.id=ter.squadra ORDER BY pri.n DESC, sec.n DESC, ter.n DESC, utenti.squadra');
$i = 0;
while ($row = $result->fetch_assoc())
{
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	$i++;
	if (!$row['primi'])
		$row['primi']=0;
	if (!$row['secondi'])
		$row['secondi']=0;
	if (!$row['terzi'])
		$row['terzi']=0;
	echo '		<td>'.$i.'</td>
				<td><b>'.$html->linksquadra($row['id'],$row['squadra']).'</b></td>
				<td>'.$row['primi'].'</td>
				<td>'.$row['secondi'].'</td>
				<td>'.$row['terzi'].'</td>
				';
	echo '</tr>';
}
echo '</table>';
$result->free();
$html->tabletitle('Classifica tifosi (Golden Team)');
echo <<< main
	<table class="table">
		<tr>
			<th>pos</th>
			<th>squadra</th>
			<th>tifosi</th>
		<tr>
main;
$result = $db->query('SELECT utenti.id AS id,utenti.squadra AS squadra, formazioni.tifosi AS tifosi FROM utenti,formazioni WHERE utenti.id=formazioni.squadra AND giornata='.$db->escape($giornata).' ORDER BY tifosi DESC, squadra');
$i = 0;
while ($row = $result->fetch_assoc())
{
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	$i++;
	echo '		<td>'.$i.'</td>
				<td><b>'.$html->linksquadra($row['id'],$row['squadra']).'</b></td>
				<td>'.$row['tifosi'].'</td>
				';
	echo '</tr>';
}
echo '</table>';
$result->free();

echo '</table>';
//$result->free();

$db->close();
$html->close();
?>