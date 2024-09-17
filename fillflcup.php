<?php
//include_once 'class/user.class.php';
//include_once 'class/html.class.php';
include_once 'class/db.class.php';

$db = new db();
$result = $db->query('SELECT id,stadio FROM utenti');
while ($row = $result->fetch_assoc())
{
	$db->query('UPDATE partite SET stadio="'.$db->escape($row['stadio']).'" WHERE s1='.$row['id']);
	/*for ($g = 21; $g <= 21; $g++)
		$db->query('UPDATE formazioni SET turno="Ottavi di finale", partita=(SELECT id FROM partite WHERE (s1='.$row['id'].' OR s2='.$row['id'].') AND id BETWEEN 129 AND 132) WHERE squadra='.$row['id'].' AND giornata='.$g);*/
}
$db->close();

?>