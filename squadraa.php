<?php
include_once 'include/main.php';

$db = new db();

$squadra = $db->escape($_GET['id']);
$stemma = 'images/stemmia/'.strtolower($squadra).'.png';

$html = new html($squadra);

$html->title($squadra,$stemma);
$html->tabletitle('Rosa');

$result = $db->query('SELECT * FROM lista WHERE squadra =\''.$squadra.'\' ORDER BY ruolo, cognome');
echo '<table class="table">
		<tr>
			<th>ruolo</th>
			<th>nome</th>
			<th>prezzo</th>
			<th>di</th>
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
		</tr>';
$i = 0;
$ruolo = -1;
$Ruoli = array ('POR', 'DIF', 'CEN', 'ATT');
while ($row = $result->fetch_assoc())
{
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	echo '		<td>'.$Ruoli[$row['ruolo']].'</td>
				<td>'.$html->linkgiocatore($row['giocatore'],$row['nome'].' <b>'.$row['cognome']).'</b></td>
				<td>'.$row['prezzo'].'</td>
				<td>'.(time()>=_MERCATOINIZ_CHIUSO?$row['proprietari']:'').'</td>
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
				';
	echo '</tr>';
	$i++;
}
echo '</table>';
$result->free();

$db->close();
$html->close();
?>