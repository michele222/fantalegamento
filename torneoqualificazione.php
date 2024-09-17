<?php
include_once 'include/main.php';

$db = new db();

$TDS = array(1,11);
$urna = 1;
$qualificate = 0;
$pos_ultima_qualificata = 11; //versione a 12
//$pos_ultima_qualificata = 14; //versione a 16
//$pos_ultima_qualificata = 18; //versione a 20

$giornata = getGiornata();

$html = new html("TORNEO DI QUALIFICAZIONE");
$html->title("Torneo di Qualificazione",_TQLOGO);

$html->tabletitle('Classifica');
echo <<< main
	<table class="table">
		<tr>
			<th>pos</th>
			<th>squadra</th>
			<th>punti</th>
			<th></th>
		<tr>
main;
$result = $db->query('SELECT utenti.id AS id,utenti.squadra AS squadra,utenti.ordine AS ordine,SUM(punti) AS punti,SUM(goltit) AS goltit,SUM(golpan) AS golpan,SUM(ptipan) AS ptipan,MAX(ptimax) AS ptimax,MAX(votmax) AS votmax,MAX(ptimaxpan) AS ptimaxpan,MAX(votmaxpan) AS votmaxpan FROM formazioni,utenti WHERE formazioni.squadra=utenti.id AND giornata BETWEEN 1 AND 4 GROUP BY utenti.squadra ORDER BY punti DESC, goltit DESC, golpan DESC, ptipan DESC, ptimax DESC, votmax DESC, ptimaxpan DESC, votmaxpan DESC, ordine');
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
				<td>';
	if ($i <= $pos_ultima_qualificata || in_array($row['id'], $TDS))
	{
		if (in_array($row['id'], $TDS)) 
		{
			echo 'qualificata di diritto - urna '.$urna;
			if ($i <= $pos_ultima_qualificata)	//tds nelle prime 14
				$pos_ultima_qualificata++;
		}
		else
			echo 'qualificata - urna '.$urna;
		$qualificate++;
		if ($qualificate%3 == 0)		//versione 12 squadre
		//if ($qualificate%4 == 0) 		//versione 16 squadre
		//if ($qualificate%5 == 0)		//versione 20 squadre
			$urna++;
	}
	else 
		echo 'non qualificata';
	echo '		</td>
			</tr>';
}
echo '</table>';
$result->free();

$db->close();
$html->close();
?>