<?php
include_once 'include/main.php';

$db = new db();
$html = new html("CALENDARIO SERIE A");

$html->title('Calendario',_SERIEALOGO);

$Calendario = Array();

echo '<table class="table">';

$result = $db->query('SELECT giornata, s1, s2 FROM calendarioa ORDER BY giornata, s1');
while ($row = $result->fetch_assoc())
	$Calendario[$row['giornata']][] = $row;
$result->free();
for ($i = 0; $i < 10; $i++) //10 righe
{
	echo '<tr>';
	for ($g = $i*4+1; $g <= $i*4+4 && $g <= 38; $g++)
		echo '<th>Giornata '.$g.'</th>';
	echo '</tr>';
	
	echo '<tr>';
	for ($g = $i*4+1; $g <= $i*4+4 && $g <= 38; $g++)
		echo '<th>'.date('H:i d/m/Y',strtotime(getGiornataData($g))).'</th>';
	echo '</tr>';
	
	for ($j = 0; $j < 10; $j++)
	{
		if($j % 2)
			echo '<tr>';
		else 
			echo '<tr class="disp">';
		for ($g = $i*4+1; $g <= $i*4+4 && $g <= 38; $g++)
			echo '<td>'.$html->linksquadraa($Calendario[$g][$j]['s1']).' - '.$html->linksquadraa($Calendario[$g][$j]['s2']).'</td>';
		echo '</tr>';
	}
}

echo '</table>';

$db->close();
$html->close();
?>