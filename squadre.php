<?php
include_once 'include/main.php';

$db = new db();

$html = new html('SQUADRE');
	
$html->title('Squadre',_FLLOGO);
$html->tabletitle('Elenco squadre ordinate per ranking');

$result = $db->query('SELECT id,squadra,stemma,nazionalita,nome,cognome,allenatore,stadio FROM utenti ORDER BY ordine');
$i = 0;
while ($row = $result->fetch_assoc())
{
	$html->title_squadra($row,$i);
	$i = !$i;
}

$result->free();
$db->close();
$html->close();
?>