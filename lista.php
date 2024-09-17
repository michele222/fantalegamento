<?php
include_once 'include/main.php';

if ($_POST['loaded'])
{
	$Params = $_POST;
	$_SESSION['params'] = $Params;
}
else
{
	$Params = $_SESSION['params'];
}

$db = new db();
$html = new html("LISTA GIOCATORI");
$html->title('Lista Giocatori',_SERIEALOGO);
$html->tabletitle('Ricerca giocatori');
echo <<< main
		<form action="lista.php" method="post">
			<p>
main;
			echo '<input type=checkbox id=por name=por';
			if ($Params['por'] || !$Params['loaded'])
				echo ' checked';
			echo '><label for="por">Portieri</label>';
			echo '<input type=checkbox id=dif name=dif';
			if ($Params['dif'] || !$Params['loaded'])
				echo ' checked';
			echo '><label for="dif">Difensori</label>';
			echo '<input type=checkbox id=cen name=cen';
			if ($Params['cen'] || !$Params['loaded'])
				echo ' checked';
			echo '><label for="cen">Centrocampisti</label>';
			echo '<input type=checkbox id=att name=att';
			if ($Params['att'] || !$Params['loaded'])
				echo ' checked';
			echo '><label for="att">Attaccanti</label>';
echo <<< main
			<br>
			<label for="nome">Nome:</label>&nbsp;
main;
			$html->input('nome',35,30,$Params['nome']);
			echo '&nbsp;<label for "squadra">Squadra:</label>&nbsp;<select id=squadra name=squadra>
					<option value=""';
			if (!$Params['squadra'])
				echo ' selected';
			echo	'>TUTTE</option>';
			$result = $db->query('SELECT DISTINCT s1 FROM calendarioa ORDER BY s1');
			while ($row = $result->fetch_assoc())
			{
				echo '<option value="'.$row['s1'].'"';
				if ($Params['squadra'] == $row['s1'])
					echo ' selected';
				echo '>'.$row['s1'].'</option>';
			}
			$result->free();
			echo '</select>&nbsp;';
			echo '<label for="prezzo">Prezzo max:</label>&nbsp;';
			$html->input('prezzo',2,2,$Params['prezzo']);
			if (time() >= _MERCATOINIZ_CHIUSO)
			{
				echo '<input type=checkbox id=liberi name=liberi';
				if ($Params['liberi'])
					echo ' checked';
				echo '><label for="liberi">Cerca solo giocatori liberi</label>';
			}
			echo '<br>';
			$html->submit('Invia');
			$html->input('loaded',1,1,1,'hidden');
	echo '</p></form>';

/*$query = '	SELECT giocatore,b.nome,b.cognome,b.ruolo,c.squadra,c.prezzo,SUM(giocato),SUM(6politico),SUM(famedia*voto)/SUM(famedia),SUM(gfatti),SUM(gsubiti),SUM(gv),SUM(gp),SUM(assist),SUM(ammonito),SUM(espulso),SUM(rtirati),SUM(rcontro),SUM(rparati),SUM(rsbagliati),SUM(autogol),SUM(presente),SUM(titolare),SUM(25min)
			FROM giocatorigiornate AS a, giocatori AS b, (SELECT giocatore,squadra,inlista,prezzo FROM giocatorigiornate WHERE giornata='.(getGiornata()-1).') AS c
			WHERE a.giocatore=b.id AND a.giocatore=c.giocatore AND c.inlista=1
			GROUP BY giocatore
			ORDER BY giocatore
';*/
$query = 'SELECT * FROM lista WHERE ruolo IN (';
if ($Params['por'])
	$query .= '0,';
if ($Params['dif'])
	$query .= '1,';
if ($Params['cen'])
	$query .= '2,';
if ($Params['att'])
	$query .= '3,';
$query .= '4) ';
if(strlen(trim($Params['nome']))>0)
	$query .= "AND (nome LIKE '%".$db->escape($Params['nome'])."%' OR cognome LIKE '%".$db->escape($Params['nome'])."%') ";
if(strlen(trim($Params['squadra']))>0)
	$query .= "AND squadra='".$db->escape($Params['squadra'])."' ";
if(strlen(trim($Params['prezzo']))>0)
	$query .= "AND prezzo<='".$db->escape($Params['prezzo'])."' ";
if ($Params['liberi'])
	$query .= 'AND proprietari=0 ';
$query .= ' ORDER BY ruolo, ';
if(isset($_GET['orderby']))
	$query .= $db->escape($_GET['orderby']).' '.$db->escape($_GET['order']);
else 
	$query .= 'cognome';
$result = $db->query($query);

$i = 0;
$ruolo = -1;
$Ruoli = array ('Portieri','Difensori','Centrocampisti','Attaccanti');
while ($row = $result->fetch_assoc())
{
	if ($row['ruolo'] != $ruolo)
	{
		if ($ruolo > -1)
			echo '</table>';
		$ruolo = $row['ruolo'];
		$html->tabletitle($Ruoli[$ruolo]);
		echo '<table class="table"><tr>';
			$html->tablesortheader('lista.php','cognome','nome');
			$html->tablesortheader('lista.php','squadra','squadra');
			$html->tablesortheader('lista.php','prezzo','prezzo');
			if (time() >= _MERCATOINIZ_CHIUSO)
				$html->tablesortheader('lista.php','proprietari','di');
			else 
				echo '<th>di</th>';
			$html->tablesortheader('lista.php','giocato','presenze');
			$html->tablesortheader('lista.php','media','mv');
			if ($row['ruolo'])
				$html->tablesortheader('lista.php','gfatti','gol');
			else //portiere
				$html->tablesortheader('lista.php','gsubiti','gol');
			$html->tablesortheader('lista.php','gv','gv');
			$html->tablesortheader('lista.php','gp','gp');
			$html->tablesortheader('lista.php','assist','assist');
			$html->tablesortheader('lista.php','ammonito','amm');
			$html->tablesortheader('lista.php','espulso','esp');
			if ($row['ruolo'])
				$html->tablesortheader('lista.php','rsbagliati','rig');
			else //portiere
				$html->tablesortheader('lista.php','rparati','rig');
			$html->tablesortheader('lista.php','autogol','aut');
		echo '</tr>';
	}
	if($i % 2)
		echo '<tr>';
	else 
		echo '<tr class="disp">';
	echo '		<td>'.$html->linkgiocatore($row['giocatore'],$row['nome'].' <b>'.$row['cognome']).'</b></td>
				<td>'.$html->linksquadraa($row['squadra']).'</td>
				<td>'.$row['prezzo'].'</td>
				<td>'.(time() >= _MERCATOINIZ_CHIUSO? $row['proprietari'] : '').'</td>
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