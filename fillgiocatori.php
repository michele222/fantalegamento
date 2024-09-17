<?php
//include_once 'class/user.class.php';
//include_once 'class/html.class.php';
include_once 'class/db.class.php';

$db = new db();

$query = 'INSERT INTO giocatori (id,ruolo,nome,cognome) VALUES';
$text = utf8_decode(str_replace('"','',file_get_contents('http://www.fantalegamento.it/giornata00.txt')));
//echo $text;
$lines = explode("\n",$text);
foreach ($lines as $line)
	if (strlen(trim($line))>0)
		$giocatori[] = explode("|",$line);
for ($i = 0; $i < count($giocatori); $i++)
{
	$cognome = $nome = '';
	$pieces = explode(" ",$giocatori[$i][2]);
	foreach ($pieces as $piece)
	{
		$nomeinit = 0; //se il nome � iniziato, il resto � sicuramente nome...
		if(ctype_upper(preg_replace("/[^a-zA-Z\s]/", "", $piece)) && !$nomeinit)
			$cognome .= ' '.ucfirst(strtolower($piece));
		else
		{
			$nome .= ' '.ucfirst($piece);
			$nomeinit = 1;
		}
	}
	$cognome = trim($cognome);
	$nome = trim($nome);
	//echo $giocatori[$i][0].': <b>'.$cognome.'</b> '.$nome.' ('.$giocatori[$i][2].')<br>';
	$query .= '('.$db->escape($giocatori[$i][0]).','.$db->escape($giocatori[$i][5]).',\''.$db->escape($nome).'\',\''.$db->escape($cognome).'\'),';
}
$query = substr($query, 0, -1);
/*echo '<pre>';
print_r ($giocatori);
echo '</pre>';*/
$db->query($query);
$db->close();

?>