<?php
include_once 'include/main.php';

$id_first_match = 1;
$S[0] = array(13,5,1,25);
$S[1] = array(10,6,8,7);
$S[2] = array(2,24,18,23);
//$S[3] = array(6,29,9,3);
//$S[4] = array(30,11,25,15);
$G = array(5,6,7,8,9,10);
$sorteggiato = true; //metti true per mostrare la pagina

$num_gironi = 3;
$Squadre = array_merge($S[0],$S[1],$S[2]); //3 gironi
//$Squadre = array_merge($S[0],$S[1],$S[2],$S[3]); //4 gironi
//$Squadre = array_merge($S[0],$S[1],$S[2],$S[3],$S[4]); //5 gironi
$TDS = array(1);

if (isset($_GET['g']))
{
	$gir = $_GET['g'];
	if ($gir < 0)
		$gir = $num_gironi;
	else if ($gir > $num_gironi)//4)
		$gir = 0;
}
else
	$gir = 0;

$db = new db();

$giornata = getGiornata();

$html = new html("TORNEO D'ANDATA");
$html->title("Torneo d'Andata",_TALOGO);

$Gironi = array('A','B','C','D','E');
if($sorteggiato)
{
    echo '<div id="lefthalf">';

if ($gir<$num_gironi)
{
$html->tabletitle('Classifica girone <a href="torneoandata.php?g='.($gir-1).'"><</a> '.$Gironi[$gir].' <a href="torneoandata.php?g='.($gir+1).'">></a> ');
echo <<< main
	<table class="table">
		<tr>
			<th>pos</th>
			<th>squadra</th>
			<th>punti</th>
			<th>punti reali</th>
			<th>V</th>
			<th>N</th>
			<th>P</th>
			<th width=10>&nbsp;</th>
		<tr>
main;
$Classifica = array();
$string_partite = '';
for ($i = $id_first_match+(12*$gir); $i < $id_first_match+(12*$gir)+12; $i++)
	$string_partite .= $i.',';
$string_partite = '('.substr($string_partite, 0, -1).')';
$result = $db->query('SELECT id,squadra FROM utenti WHERE id IN ('.implode(',',$S[$gir]).')');
while ($row = $result->fetch_assoc())
{
	$Classifica['id'][$row['id']] = $row['id'];
	$Classifica['squadra'][$row['id']] = $row['squadra'];
	$Classifica['v'][$row['id']] = 0;
	$Classifica['n'][$row['id']] = 0;
	$Classifica['p'][$row['id']] = 0;
	$Classifica['punti'][$row['id']] = 0;
	$Classifica['puntir'][$row['id']] = 0;
}
$result->free();
$result = $db->query('SELECT s1,COUNT(id) AS n,SUM(punti1) AS punti FROM partite WHERE gol1>gol2 AND id IN '.$string_partite.' AND id NOT IN (SELECT partite.id FROM partite,formazioni WHERE partite.id=formazioni.partita AND giornata>='.$giornata.') GROUP BY s1');
while ($row = $result->fetch_assoc())
{
	$Classifica['v'][$row['s1']] = $row['n'];
	$Classifica['punti'][$row['s1']] = $row['n'] * 3;
	$Classifica['puntir'][$row['s1']] = $row['punti'];
}
$result->free();
$result = $db->query('SELECT s2,COUNT(id) AS n,SUM(punti2) AS punti FROM partite WHERE gol1<gol2 AND id IN '.$string_partite.' AND id NOT IN (SELECT partite.id FROM partite,formazioni WHERE partite.id=formazioni.partita AND giornata>='.$giornata.') GROUP BY s2');
while ($row = $result->fetch_assoc())
{
	$Classifica['v'][$row['s2']] += $row['n'];
	$Classifica['punti'][$row['s2']] += ($row['n'] * 3);
	$Classifica['puntir'][$row['s2']] += $row['punti'];
}
$result->free();
$result = $db->query('SELECT s1,COUNT(id) AS n,SUM(punti1) AS punti FROM partite WHERE gol1=gol2 AND id IN '.$string_partite.' AND id NOT IN (SELECT partite.id FROM partite,formazioni WHERE partite.id=formazioni.partita AND giornata>='.$giornata.') GROUP BY s1');
while ($row = $result->fetch_assoc())
{
	$Classifica['n'][$row['s1']] += $row['n'];
	$Classifica['punti'][$row['s1']] += $row['n'];
	$Classifica['puntir'][$row['s1']] += $row['punti'];
}
$result->free();
$result = $db->query('SELECT s2,COUNT(id) AS n,SUM(punti2) AS punti FROM partite WHERE gol1=gol2 AND id IN '.$string_partite.' AND id NOT IN (SELECT partite.id FROM partite,formazioni WHERE partite.id=formazioni.partita AND giornata>='.$giornata.') GROUP BY s2');
while ($row = $result->fetch_assoc())
{
	$Classifica['n'][$row['s2']] += $row['n'];
	$Classifica['punti'][$row['s2']] += $row['n'];
	$Classifica['puntir'][$row['s2']] += $row['punti'];
}
$result->free();
$result = $db->query('SELECT s1,COUNT(id) AS n,SUM(punti1) AS punti FROM partite WHERE gol1<gol2 AND id IN '.$string_partite.' AND id NOT IN (SELECT partite.id FROM partite,formazioni WHERE partite.id=formazioni.partita AND giornata>='.$giornata.') GROUP BY s1');
while ($row = $result->fetch_assoc())
{
	$Classifica['p'][$row['s1']] += $row['n'];
	$Classifica['puntir'][$row['s1']] += $row['punti'];
}
$result->free();
$result = $db->query('SELECT s2,COUNT(id) AS n,SUM(punti2) AS punti FROM partite WHERE gol1>gol2 AND id IN '.$string_partite.' AND id NOT IN (SELECT partite.id FROM partite,formazioni WHERE partite.id=formazioni.partita AND giornata>='.$giornata.') GROUP BY s2');
while ($row = $result->fetch_assoc())
{
	$Classifica['p'][$row['s2']] += $row['n'];
	$Classifica['puntir'][$row['s2']] += $row['punti'];
}
$result->free();
array_multisort($Classifica['punti'],SORT_DESC,$Classifica['puntir'],SORT_DESC,$Classifica['squadra'],$Classifica['id'],$Classifica['v'],$Classifica['n'],$Classifica['p']);
/*echo '<pre>';
print_r($Classifica);
echo '</pre>';*/
for ($i = 0; $i < 4; $i++)
{
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	echo '		<td>'.($i+1).'</td>
				<td><b>'.$html->linksquadra($Classifica['id'][$i],$Classifica['squadra'][$i]).'</b></td>
				<td><b>'.$Classifica['punti'][$i].'</b></td>
				<td>'.number_format($Classifica['puntir'][$i],1).'</td>
				<td>'.$Classifica['v'][$i].'</td>
				<td>'.$Classifica['n'][$i].'</td>
				<td>'.$Classifica['p'][$i].'</td>
				<td>&nbsp;</td>
				';
	echo '</tr>';
}
echo '</table>';
}
else
$html->tabletitle('Fase finale <a href="torneoandata.php?g=2"><</a> <a href="torneoandata.php?g=4">></a> ');
//$html->tabletitle('Fase finale <a href="torneoandata.php?g=3"><</a> <a href="torneoandata.php?g=5">></a> ');
//$html->tabletitle('Fase finale <a href="torneoandata.php?g=4"><</a> <a href="torneoandata.php?g=6">></a> ');
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
$result = $db->query('SELECT utenti.id AS id,utenti.squadra AS squadra,utenti.ordine AS ordine,SUM(punti) AS punti FROM formazioni,utenti WHERE formazioni.squadra=utenti.id AND giornata IN (SELECT id FROM giornate WHERE torneo LIKE "%Andata" AND id<='.$giornata.') GROUP BY utenti.squadra ORDER BY punti DESC, ordine');
$i = 0;
$tds = count($TDS);
while ($row = $result->fetch_assoc())
{
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	$i++;
	echo '		<td>'.$i.'</td>';
	if (in_array($row['id'], $Squadre))
	{
		echo '		<td><b>'.$html->linksquadra($row['id'],$row['squadra']).'</b></td>';
		if (in_array($row['id'], $TDS))
		{
			echo '	<td><a href="torneofinale.php" alt="Testa di serie al Torneo Finale">TDS</a></td>';
			$tds--;
		}
		else
			if ($i > 16 - $tds)
				echo '	<td><img src="'._IMGRETR.'" width=10 heigth=10/></td>';
			else 
				echo '	<td>&nbsp;</td>';
		echo '		<td><b>'.$row['punti'].'</b></td>';
	}
	else
	{ 
		echo '		<td>'.$html->linksquadra($row['id'],$row['squadra']).'</td>';
		if (in_array($row['id'], $TDS))
		{
			echo '	<td><a href="torneofinale.php" alt="Testa di serie al Torneo Finale">TDS</a></td>';
			$tds--;
		}
		else
			if ($i <= 16 - $tds)
				echo '	<td><img src="'._IMGPROM.'" width=10 heigth=10/></td>';
			else 
				echo '	<td>&nbsp;</td>';
		echo '		<td>'.$row['punti'].'</td>';
	}
	echo '</tr>';
}
echo '</table>';
$result->free();
echo '</div><div id="righthalf">';
if($gir<$num_gironi)
$html->tabletitle('Calendario girone <a href="torneoandata.php?g='.($gir-1).'"><</a> '.$Gironi[$gir].' <a href="torneoandata.php?g='.($gir+1).'">></a> ');
else
$html->tabletitle('Fase finale <a href="torneoandata.php?g=2"><</a> <a href="torneoandata.php?g=4">></a> ');
//$html->tabletitle('Fase finale <a href="torneoandata.php?g=3"><</a> <a href="torneoandata.php?g=5">></a> ');
//$html->tabletitle('Fase finale <a href="torneoandata.php?g=4"><</a> <a href="torneoandata.php?g=6">></a> ');
echo <<< main
	<table class="table">
main;
if($gir<$num_gironi)
$result = $db->query('SELECT DISTINCT partite.id,s1,s2,u1.squadra AS sq1,u2.squadra AS sq2,gol1,gol2,punti1,punti2,giornata FROM formazioni,partite,utenti AS u1,utenti AS u2 WHERE s1=u1.id AND s2=u2.id AND partita=partite.id AND partite.id IN '.$string_partite.' ORDER BY giornata, partite.id');
else
//$result = $db->query('SELECT DISTINCT partite.id,s1,s2,u1.squadra AS sq1,u2.squadra AS sq2,gol1,gol2,punti1,punti2,giornata,turno FROM formazioni,partite,utenti AS u1,utenti AS u2 WHERE s1=u1.id AND s2=u2.id AND partita=partite.id AND (formazioni.giornata=15 OR formazioni.giornata=16) ORDER BY giornata, partite.id');
$result = $db->query('SELECT DISTINCT partite.id,s1,s2,u1.squadra AS sq1,u2.squadra AS sq2,gol1,gol2,punti1,punti2,giornata,turno FROM formazioni,partite,utenti AS u1,utenti AS u2 WHERE s1=u1.id AND s2=u2.id AND partita=partite.id AND formazioni.giornata=11 ORDER BY giornata, partite.id');
$i = 0;
$g = 0;
while ($row = $result->fetch_assoc())
{
    if($gir<$num_gironi)
    {
	if (!($i % 2))
	{
		$g++;
		echo '	<tr>
					<th>Giornata '.$g.'</th>
					<th>'.date('H:i d/m/Y',strtotime(getGiornataData($G[$g-1]))).'</th>
				</tr>';
	}
    }
    else
    if (!($i % 4))
	{
		$g++;
		echo '	<tr>
					<th>'.$row['turno'].'</th>
					<th></th>
				</tr>';
	}
	if ($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	$i++;
	echo '		<td>'.$html->linksquadra($row['s1'],$row['sq1']).' - '.$html->linksquadra($row['s2'],$row['sq2']).'</td>';
	if($row['giornata'] < $giornata)
		echo '	<td>'.$row['gol1'].' - '.$row['gol2'].' ('.$html->linkpunteggio($row['s1'],$row['giornata'],$row['punti1']).' - '.$html->linkpunteggio($row['s2'],$row['giornata'],$row['punti2']).')</td>';
	else
		echo '	<td>&nbsp;</td>';
	echo '</tr>';
}
if($gir>=$num_gironi)
{
	//$result = $db->query('SELECT DISTINCT partite.id,s1,s2,u1.squadra AS sq1,u2.squadra AS sq2,gol1,gol2,punti1,punti2,giornata,turno FROM formazioni,partite,utenti AS u1,utenti AS u2 WHERE s1=u1.id AND s2=u2.id AND partita=partite.id AND (formazioni.giornata=17 OR formazioni.giornata=18) ORDER BY giornata, partite.id');
	$result = $db->query('SELECT DISTINCT partite.id,s1,s2,u1.squadra AS sq1,u2.squadra AS sq2,gol1,gol2,punti1,punti2,giornata,turno FROM formazioni,partite,utenti AS u1,utenti AS u2 WHERE s1=u1.id AND s2=u2.id AND partita=partite.id AND formazioni.giornata=12 ORDER BY giornata, partite.id');
	$i = 0;
	$g = 0;
	while ($row = $result->fetch_assoc())
	{
	
	    if (!($i % 2))
		{
			$g++;
			echo '	<tr>
						<th>'.$row['turno'].'</th>
						<th></th>
					</tr>';
		}
		if ($i % 2)
			echo '<tr>';
		else 
			echo '<tr class="disp">';
		$i++;
		echo '		<td>'.$html->linksquadra($row['s1'],$row['sq1']).' - '.$html->linksquadra($row['s2'],$row['sq2']).'</td>';
		if($row['giornata'] < $giornata)
			echo '	<td>'.$row['gol1'].' - '.$row['gol2'].' ('.$html->linkpunteggio($row['s1'],$row['giornata'],$row['punti1']).' - '.$html->linkpunteggio($row['s2'],$row['giornata'],$row['punti2']).')</td>';
		else
			echo '	<td>&nbsp;</td>';
		echo '</tr>';
	}
}
if($gir>=$num_gironi)
{
	$result = $db->query('SELECT DISTINCT partite.id,s1,s2,u1.squadra AS sq1,u2.squadra AS sq2,gol1,gol2,punti1,punti2,giornata,turno FROM formazioni,partite,utenti AS u1,utenti AS u2 WHERE s1=u1.id AND s2=u2.id AND partita=partite.id AND formazioni.giornata=13 ORDER BY giornata, partite.id');
	$i = 0;
	$g = 0;
	while ($row = $result->fetch_assoc())
	{
	
		$g++;
		echo '	<tr>
					<th>'.$row['turno'].'</th>
					<th></th>
				</tr>';

		if ($i % 2)
			echo '<tr>';
		else 
			echo '<tr class="disp">';
		$i++;
		echo '		<td>'.$html->linksquadra($row['s1'],$row['sq1']).' - '.$html->linksquadra($row['s2'],$row['sq2']).'</td>';
		if($g < $giornata-1)
			echo '	<td>'.$row['gol1'].' - '.$row['gol2'].' ('.$html->linkpunteggio($row['s1'],$row['giornata'],$row['punti1']).' - '.$html->linkpunteggio($row['s2'],$row['giornata'],$row['punti2']).')</td>';
		else
			echo '	<td>&nbsp;</td>';
		echo '</tr>';
	}
}
echo '</table>';
}
else
    $html->error("Sorteggio non ancora effettuato");
$result->free();
echo '</div>';

$db->close();
$html->close();
?>