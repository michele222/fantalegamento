<?php
//include_once 'class/user.class.php';
//include_once 'class/html.class.php';
include_once 'class/db.class.php';

$db = new db();

$query = 'INSERT INTO giocatorigiornate (giornata, giocatore, squadra, inlista, giocato, magicpunti, 6politico, famedia, voto, gfatti, gsubiti, gv, gp, assist, ammonito, espulso, rtirati, rcontro, rparati, rsbagliati, autogol, presente, titolare, 25min, casa, prezzo) VALUES';
$text = utf8_decode(str_replace('"','',file_get_contents('http://www.fantalegamento.it/giornata0.txt')));
//echo $text;
$lines = explode("\n",$text);
foreach ($lines as $line)
	if (strlen(trim($line))>0)
		$giocatori[] = explode("|",$line);
for ($i = 0; $i < count($giocatori); $i++)
{
	$query .= '(2,';
	for ($j = 0; $j < 28; $j++) //28 = numero campi file txt
		if ($j != 1 && $j != 2 && $j != 3 && $j != 5) //2 = nome, 3 = squadra, 5 = ruolo
			$query .= "'".$db->escape($giocatori[$i][$j])."',";
		else if ($j == 3) //nome squadra->iniziale in maiuscolo
			$query .= "'".$db->escape(ucfirst(strtolower($giocatori[$i][$j])))."',";
	$query = substr($query, 0, -1);		
	$query .= '),';
}
	
$query = substr($query, 0, -1);
/*echo '<pre>';
print_r ($giocatori);
echo '</pre>';*/
echo $query;
$db->query($query);
$db->close();

?>