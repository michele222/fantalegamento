<?php
include_once 'include/main.php';
$db = new db();

if($_GET['id'] && ctype_digit($_GET['id']))
	$squadra = $_GET['id'];
else if (!$user->guest)
	$squadra = $user->id;
else 
	$squadra = 1;
	
$g = getGiornata(); //giornata attuale

if ($_GET['giornata'] && ctype_digit($_GET['giornata']))
	$giornata = $_GET['giornata'];
else
	$giornata = $g-1;

$result = $db->query('SELECT squadra,stemma FROM utenti WHERE id='.$db->escape($squadra));
$row = $result->fetch_assoc();
$nomesquadra = $row['squadra'];
$stemma = $row['stemma'];
$result->free();

$html = new html("Punteggio ".$nomesquadra);
$html->title("Punteggio giornata ".$giornata,$stemma);
//print_r($_POST);
$html->tabletitle('Parametri');
echo <<< main
		<p>
		<form action="punteggio.php?" method="get">
			<label for giornata>Giornata:</label>&nbsp;<select id=giornata name=giornata>
main;
		for($i = 1; $i < $g; $i++)
		{
			echo '<option value="'.$i.'"';
			if ($i == $giornata)
				echo ' selected';
			echo '>'.$i.'</option>';
		}
		echo '</select>';
echo <<< main
			&nbsp;<label for id>Squadra:</label>
			&nbsp;<select id=id name=id>
main;
			
		$result = $db->query('SELECT id,squadra FROM utenti');
		while ($row = $result->fetch_assoc())
		{
			echo '<option value="'.$row['id'].'"';
			if ($squadra == $row['id'])
				echo ' selected';
			echo '>'.$row['squadra'].'</option>';
		}
		$result->free();
		echo '</select>&nbsp;';
		$html->submit('OK');
	echo '</form></p>';
$html->tabletitle('Punteggio giornata '.$giornata);
echo <<< main
		<table class="table">
			<tr>
				<th>ruolo</th>
				<th>num</th>
				<th>nome</th>
				<th>sost</th>
				<th>voto</th>
				<th>bonus</th>
				<th>punti</th>
			<tr>
main;
$Ruoli = array ('POR', 'DIF', 'CEN', 'ATT');

$result = $db->query('SELECT ruolo,nome,cognome,numero,giocgiorsq.giocatore AS giocatore,vale,gioca,magicpunti,6politico,voto,gfatti,gsubiti,gv,gp,assist,ammonito,espulso,rparati,rsbagliati,autogol,25min FROM giocatori,giocatoriformazioni,giocgiorsq,giocatorigiornate WHERE giocatori.id=giocatoriformazioni.giocatore AND giocatori.id=giocgiorsq.giocatore AND giocatori.id=giocatorigiornate.giocatore AND giocgiorsq.giornata='.$db->escape($giornata).' AND giocgiorsq.giornata=giocatorigiornate.giornata AND giocgiorsq.squadra='.$db->escape($squadra).' AND formazione=(SELECT id FROM formazioni WHERE giornata='.$db->escape($giornata).' AND squadra='.$db->escape($squadra).') ORDER BY giocatoriformazioni.pos');
while ($row = $result->fetch_assoc())
	$Giocatori[] = $row;
$result->free();
$vototot = 0;
$bonustot = 0;
for ($i = 0; $i < 18; $i++)
{
	if ($squadra == $user->id || time() >= _MERCATOINIZ_CHIUSO)
	{
		if ($i == 11)
			echo '<tr><th colspan=7>&nbsp;</th></tr>';
		if ($i % 2)
			echo '<tr>';
		else 
			echo '<tr class="disp">';
	}
	if (!$Giocatori[$i]['gioca'] && $i < 11)
		$sost = '<img src="'._IMGRETR.'" width=15 heigth=15 />';
	else if ($Giocatori[$i]['gioca'] && $i >= 11)
		$sost = '<img src="'._IMGPROM.'" width=15 heigth=15 />';
	else 	
		$sost = '&nbsp;';
	if ($Giocatori[$i]['voto'] != 0.0)
		$voto = $Giocatori[$i]['voto'];
	else 	
		$voto = '-';
	$bonus = '';
	for ($j = 0; $j < $Giocatori[$i]['6politico']; $j++)
	    $bonus .= '<img src="'._IMG6POL.'" width=15 heigth=15 alt="6 politico (+6)" title="6 politico (+6)" />';
	for ($j = 0; $j < ($Giocatori[$i]['gfatti'] - $Giocatori[$i]['gv'] - $Giocatori[$i]['gp']); $j++)
		$bonus .= '<img src="'._IMGGOL.'" width=15 heigth=15 alt="gol (+3)" title="gol (+3)" />';
	for ($j = 0; $j < $Giocatori[$i]['gv']; $j++)
		$bonus .= '<img src="'._IMGGOLV.'" width=15 heigth=15 alt="gol vittoria (+4)" title="gol vittoria (+4)" />';
	for ($j = 0; $j < $Giocatori[$i]['gp']; $j++)
		$bonus .= '<img src="'._IMGGOLP.'" width=15 heigth=15 alt="gol pareggio (+3.5)" title="gol pareggio (+3.5)" />';
	for ($j = 0; $j < $Giocatori[$i]['rparati']; $j++)
		$bonus .= '<img src="'._IMGRIGP.'" width=15 heigth=15 alt="rigore parato (+3)" title="rigore parato (+3)" />';
	for ($j = 0; $j < $Giocatori[$i]['assist']; $j++)
		$bonus .= '<img src="'._IMGASS.'" width=15 heigth=15 alt="assist (+1)" title="assist (+1)" />';
	for ($j = 0; $j < $Giocatori[$i]['rsbagliati']; $j++)
		$bonus .= '<img src="'._IMGRIGS.'" width=15 heigth=15 alt="rigore sbagliato (-3)" title="rigore sbagliato (-3)" />';
	for ($j = 0; $j < $Giocatori[$i]['autogol']; $j++)
		$bonus .= '<img src="'._IMGAUTO.'" width=15 heigth=15 alt="autogol (-2)" title="autogol (-2)" />';
	for ($j = 0; $j < $Giocatori[$i]['gsubiti']; $j++)
		$bonus .= '<img src="'._IMGGOLS.'" width=15 heigth=15 alt="gol subito (-1)" title="gol subito (-1)" />';	
	for ($j = 0; $j < $Giocatori[$i]['espulso']; $j++)
		$bonus .= '<img src="'._IMGESP.'" width=15 heigth=15 alt="espulsione (-1)" title="espulsione (-1)" />';	
	for ($j = 0; $j < $Giocatori[$i]['ammonito']; $j++)
		$bonus .= '<img src="'._IMGAMM.'" width=15 heigth=15 alt="ammonizione (-0.5)" title="ammonizione (-0.5)" />';
	if (!$bonus)
		$bonus = '&nbsp;';  
	$punti = number_format($Giocatori[$i]['magicpunti'], 1);
	if ($Giocatori[$i]['gioca'])
	{
		$voto = '<b>'.$voto.'</b>';
		$punti = '<b>'.$punti.'</b>';
		$vototot += $Giocatori[$i]['voto'];
		$bonustot += $Giocatori[$i]['magicpunti'] - $Giocatori[$i]['voto'];
	}
	if ($squadra == $user->id || time() >= _MERCATOINIZ_CHIUSO)
		echo '	<td>'.$Ruoli[$Giocatori[$i]['ruolo']].'</td>
				<td>'.$Giocatori[$i]['numero'].'</td>
				<td>'.$html->linkgiocatore($Giocatori[$i]['giocatore'],$Giocatori[$i]['nome'].' <b>'.$Giocatori[$i]['cognome']).'</b></td>
				<td>'.$sost.'</td>
				<td>'.$voto.'</td>
				<td height=20>'.$bonus.'</td>
				<td>'.$punti.'</td>
			</tr>';
}
$result = $db->query('SELECT formazioni.id AS id,punti,modulo,s1,spettatori,tifosi FROM formazioni LEFT JOIN partite ON formazioni.partita=partite.id WHERE giornata='.$db->escape($giornata).' AND squadra='.$db->escape($squadra));
if ($row = $result->fetch_assoc())
{
	$modulo = $row['modulo'];
	$ptitot = $row['punti'];
	$row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND gioca=1 AND giocatore<200')->fetch_assoc();
	$por = $row1['n'];
	$dif = substr($modulo,0,1);
	$modificatore = 0;
	if($por == 1 && $dif >= 4)
	{
		$row1 = $db->query('SELECT giocato, famedia, magicpunti, voto FROM giocatorigiornate WHERE giornata='.$giornata.' AND giocatore=(SELECT giocatore FROM giocatoriformazioni WHERE giocatore<200 AND gioca=1 AND formazione='.$row['id'].')')->fetch_assoc();
		if ($row1['giocato'] && $row1['famedia']) //voto regolare, sommo il voto
			$votodifesa = $row1['voto'];
		else //senza voto con bonus/malus, sommo i punti
			$votodifesa = $row1['magicpunti'];
		$result1 = $db->query('SELECT giocato, famedia, magicpunti, voto FROM giocatorigiornate WHERE giornata='.$giornata.' AND giocatore IN (SELECT giocatore FROM giocatoriformazioni WHERE giocatore>=200 AND giocatore<500 AND gioca=1 AND formazione='.$row['id'].') ORDER BY voto DESC');
		for ($i = 0; $i < 3; $i++)
		{
			$row1 = $result1->fetch_assoc();
			if ($row1['giocato'] && $row1['famedia']) //voto regolare, sommo il voto
				$votodifesa += $row1['voto'];
			else //senza voto con bonus/malus, sommo i punti
				$votodifesa += $row1['magicpunti'];
		}
		$result1->free();
		$votodifesa = $votodifesa / 4;
		if($votodifesa >= 7)
	  		$modificatore = 6;
		if($votodifesa < 7 && $votodifesa >= 6.5)
	  		$modificatore = 3;
		if($votodifesa < 6.5 && $votodifesa >= 6)
	  		$modificatore = 1;	
	}	
}
$vototot = number_format($vototot,1);
$bonustot = number_format($bonustot,1);
$tifosi = $ptitot - $bonustot - $vototot - $modificatore;
$bonus = $modificatore + $tifosi;
$result->free();
echo <<< main
			<tr>
				<th>$modulo</th>
				<th></th>
				<th>modif.difesa = $modificatore, bonus tifosi = $tifosi</th>
				<th>TOTALE:</th>
				<th>$vototot</th>
				<th>$bonustot + $bonus</th>
				<th>= $ptitot</th>
			<tr>
		</table>
main;

$db->close();
$html->close();
?>