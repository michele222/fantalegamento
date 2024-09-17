<?php
//include_once 'class/user.class.php';
//include_once 'class/html.class.php';
include_once 'class/db.class.php';

$db = new db();

$id_first_match = 60;
$S[0] = array(13,2,5,23);
$S[1] = array(6,10,24,25);
$S[2] = array(18,1,8,7);
//$S[3] = array(6,29,9,3);
//$S[4] = array(30,11,25,15);
$G = array(17,18,19,20,21,22);

$P[0][0] = array(0,1);
$P[0][1] = array(2,3);
$P[1][0] = array(1,2);
$P[1][1] = array(3,0);
$P[2][0] = array(0,2);
$P[2][1] = array(1,3);
$P[3][0] = array(2,0);
$P[3][1] = array(3,1);
$P[4][0] = array(1,0);
$P[4][1] = array(3,2);
$P[5][0] = array(0,3);
$P[5][1] = array(2,1);

$Squadre = array_merge($S[0],$S[1],$S[2]); //3
//$Squadre = array_merge($S[0],$S[1],$S[2],$S[3]); //4
//$Squadre = array_merge($S[0],$S[1],$S[2],$S[3],$S[4]); //5
$idpartita = $id_first_match;

for ($girone = 0; $girone < 3; $girone++) //3
//for ($girone = 0; $girone < 4; $girone++) //4
//for ($girone = 0; $girone < 5; $girone++) //5
{
	for ($g = 0; $g < 6; $g++)
	{
		for ($i = 0; $i < 2; $i++)
		{
			$s1 = $S[$girone][$P[$g][$i][0]];
			$s2 = $S[$girone][$P[$g][$i][1]];
			$db->query('INSERT INTO partite (id,s1,s2) VALUES ('.$idpartita.','.$s1.','.$s2.')');
			$db->query('UPDATE formazioni SET turno="Giornata '.($g+1).'", partita='.$idpartita.' WHERE (squadra='.$s1.' OR squadra='.$s2.') AND giornata='.$G[$g]);
			echo 'Girone '.$girone.' Giornata '.($g+1).' ('.$G[$g].' di A) Partita '.$idpartita.': '.$s1.' - '.$s2.'<br>';
			$idpartita++;
		}
	}
}

$result = $db->query('SELECT id,stadio FROM utenti');
while ($row = $result->fetch_assoc())
{
	$db->query('UPDATE partite SET stadio="'.$db->escape($row['stadio']).'" WHERE s1='.$row['id'].' AND id>='.$id_first_match);
	/*for ($g = 18; $g <= 24; $g++)
		$db->query('UPDATE formazioni SET turno="Giornata '.($g-17).'", partita=(SELECT id FROM partite WHERE (s1='.$row['id'].' OR s2='.$row['id'].') AND id BETWEEN '.($g*8-7).' AND '.($g*8).') WHERE squadra='.$row['id'].' AND giornata='.($g+1));
*/
}
$db->close();

?>