<?php
include_once 'include/main.php';

if($_GET['id'] && ctype_digit($_GET['id']))
	$squadra = $_GET['id'];
else if (!$user->guest)
	$squadra = $user->id;
else 
	$squadra = 1;

$db = new db();

$result = $db->query('SELECT squadra,stemma FROM utenti WHERE id='.$db->escape($squadra));
$row = $result->fetch_assoc();
$nomesquadra = $row['squadra'];
$stemma = $row['stemma'];
$result->free();

$html = new html("Calendario ".$nomesquadra);

$html->title("Calendario",$stemma);
$html->tabletitle('Calendario');

echo <<< main
		<table class="table">
			<tr>
				<th>gior</th>
				<th>data</th>
				<th>torneo</th>
				<th>turno</th>
				<th>stadio</th>
				<th>partita</th>
				<th>risultato</th>
				<th>punti</th>
				<th>class</th>
			<tr>
main;
$result = $db->query('SELECT giornata, turno,s1,s2,stadio,gol1,gol2,punti,punti1,punti2,ordine FROM formazioni LEFT JOIN partite ON (partita=partite.id) WHERE squadra='.$db->escape($squadra).' ORDER BY giornata');
while ($row = $result->fetch_assoc())
{
	$Calendario[$row['giornata']] = $row;
	if ($row['giornata'] >= getGiornata())
		$Calendario[$row['giornata']]['gol1'] = $Calendario[$row['giornata']]['gol2'] = $Calendario[$row['giornata']]['punti1'] = $Calendario[$row['giornata']]['punti2'] = '';
}
$result->free();
for ($g = 1; $g <=38; $g++)
{
	if($g % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	if (!isset($Calendario[$g]['giornata'])) //giornata libera
		echo '	<td>'.$g.'</td>
				<td>'.date('D d/m/Y H:i',strtotime(getGiornataData($g))).'</td>
				<td>'.getGiornataTorneo($g).'</b></td>
				<td>&nbsp;</td>
				<td>Giornata libera</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>'.$html->linkpunteggio($squadra,$g,$Calendario[$g]['punti']).'</td>
				<td>'.($Calendario[$g]['ordine'] ? '<a href="giornata.php?id='.$g.'">'.$Calendario[$g]['ordine'].'</a>' : '').'</td>';
	else
		echo '	<td>'.$g.'</td>
				<td>'.date('D d/m/Y H:i',strtotime(getGiornataData($g))).'</td>
				<td>'.getGiornataTorneo($g).'</b></td>
				<td>'.$Calendario[$g]['turno'].'</td>
				<td>'.($Calendario[$g]['stadio']==$user->stadio?'<b>':'').$Calendario[$g]['stadio'].($Calendario[$g]['stadio']==$user->stadio?'</b>':'').'</td>
				
				<td>'.($Calendario[$g]['s1']==$user->id?'<b>':'').$html->linksquadra($Calendario[$g]['s1'],getSquadraNome($Calendario[$g]['s1'])).($Calendario[$g]['s1']==$user->id?'</b>':'').
				' - '.($Calendario[$g]['s2']==$user->id?'<b>':'').$html->linksquadra($Calendario[$g]['s2'],getSquadraNome($Calendario[$g]['s2'])).($Calendario[$g]['s2']==$user->id?'</b>':'').'</td>
				
				<td>'.($Calendario[$g]['s1']==$user->id?'<b>':'').$Calendario[$g]['gol1'].($Calendario[$g]['s1']==$user->id?'</b>':'').
				' - '.($Calendario[$g]['s2']==$user->id?'<b>':'').$Calendario[$g]['gol2'].($Calendario[$g]['s2']==$user->id?'</b>':'').
				' ('.($Calendario[$g]['s1']==$user->id?'<b>':'').$html->linkpunteggio($Calendario[$g]['s1'],$g,$Calendario[$g]['punti1']).($Calendario[$g]['s1']==$user->id?'</b>':'').
				' - '.($Calendario[$g]['s2']==$user->id?'<b>':'').$html->linkpunteggio($Calendario[$g]['s2'],$g,$Calendario[$g]['punti2']).($Calendario[$g]['s2']==$user->id?'</b>':'').')</td>
				
				<td>'.$html->linkpunteggio($squadra,$g,$Calendario[$g]['punti']).'</td>
				<td>'.($Calendario[$g]['ordine'] ? '<a href="giornata.php?id='.$g.'">'.$Calendario[$g]['ordine'].'</a>' : '').'</td>';
}

echo '</table>';

$db->close();
$html->close();
?>