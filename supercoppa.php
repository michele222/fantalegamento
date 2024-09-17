<?php
include_once 'include/main.php';

$db = new db();

$giornata = getGiornata();

$html = new html("SUPERCOPPA");
$html->title("Supercoppa",_SCLOGO);

echo '<div id="lefthalf">';
$html->tabletitle('Turni');
echo <<< main
	<table class="table">
main;
$result = $db->query('SELECT DISTINCT partite.id,s1,s2,u1.squadra AS sq1,u2.squadra AS sq2,gol1,gol2,punti1,punti2,turno,formazioni.giornata FROM formazioni,partite,utenti AS u1,utenti AS u2 WHERE s1=u1.id AND s2=u2.id AND partita=partite.id AND giornata IN (SELECT id FROM giornate WHERE torneo=\'Supercoppa\') ORDER BY giornata, partite.id');
$i = 0;
$g = 0;
$turno_prec = -1;
while ($row = $result->fetch_assoc())
{
	if ($row['turno'] != $turno_prec)
	{
		$g++;
		echo '	<tr>
					<th>'.$row['turno'].'</th>
					<th>'.date('H:i d/m/Y',strtotime(getGiornataData($row['giornata']))).'</th>
				</tr>';
	}
	if ($i  % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	$i++;
	$text_s1 = $html->linksquadra($row['s1'],$row['sq1']);
	/*if ($row['punti1'] > $row['punti2'])
		$text_s1 = '<b>'.$text_s1.'</b>';*/
	$text_s2 = $html->linksquadra($row['s2'],$row['sq2']);
	/*if ($row['punti2'] > $row['punti1'])
		$text_s2 = '<b>'.$text_s2.'</b>';*/
	echo '		<td>'.$text_s1.' - '.$text_s2.'</td>';
	if($g < $giornata-1)
		echo '	<td>'.$row['gol1'].' - '.$row['gol2'].' ('.$html->linkpunteggio($row['s1'],$row['giornata'],$row['punti1']).' - '.$html->linkpunteggio($row['s2'],$row['giornata'],$row['punti2']).')</td>';
	else
		echo '	<td>&nbsp;</td>';
	echo '</tr>';
	$turno_prec = $row['turno'];
}
echo '</table>';
$result->free();
echo '</div><div id="righthalf">';
$html->tabletitle('Classifica a punti');
echo <<< main
	<table class="table">
		<tr>
			<th>pos</th>
			<th>squadra</th>
			<th>&nbsp;</th>
			<th>punti</th>
		<tr>
main;
$result = $db->query('SELECT utenti.id AS id,utenti.squadra AS squadra,utenti.ordine AS ordine,SUM(punti) AS punti FROM formazioni,utenti WHERE formazioni.squadra=utenti.id AND giornata IN (SELECT id FROM giornate WHERE torneo=\'Supercoppa\' AND id<='.$giornata.') GROUP BY utenti.squadra ORDER BY punti DESC, ordine');
$i = 0;
while ($row = $result->fetch_assoc())
{
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	$i++;
	echo '		<td>'.$i.'</td>';
	echo '		<td><b>'.$html->linksquadra($row['id'],$row['squadra']).'</b></td>';
	echo '		<td>&nbsp;</td>';
	echo '		<td><b>'.$row['punti'].'</b></td>';

	echo '</tr>';
}
echo '</table>';
$result->free();
echo '</div>';

$db->close();
$html->close();
?>