<?php
include_once 'include/main.php';

$giornata = getGiornata();
if ($giornata < 10)
	$giornata_txt = '0'.$giornata;
else
	$giornata_txt = $giornata;

$db = new db();
$html = new html("AGGIORNAMENTO");

$html->title('Aggiornamento',_FLLOGO);
$html->tabletitle('Giornata '.$giornata);

$db->begin();
$ok = 1;

$query = 'INSERT INTO giocatorigiornate (giornata, giocatore, squadra, inlista, giocato, magicpunti, 6politico, famedia, voto, gfatti, gsubiti, gv, gp, assist, ammonito, espulso, rtirati, rcontro, rparati, rsbagliati, autogol, presente, titolare, 25min, casa, prezzo) VALUES';
$text = utf8_decode(str_replace('"','',file_get_contents('http://www.fantalegamento.it/giornata'.$giornata_txt.'.txt')));
//echo $text;
$lines = explode("\n",$text);
foreach ($lines as $line)
	if (strlen(trim($line))>0)
		$giocatori[] = explode("|",$line);
for ($i = 0; $i < count($giocatori); $i++)
{
	$query .= '('.$giornata.',';
	for ($j = 0; $j < 28; $j++) //28 = numero campi file txt
		if ($j != 1 && $j != 2 && $j != 3 && $j != 5) //1 = giornata, 2 = nome, 3 = squadra, 5 = ruolo
			$query .= "'".$db->escape($giocatori[$i][$j])."',";
		else if ($j == 3) //nome squadra->iniziale in maiuscolo
			$query .= "'".$db->escape(ucfirst(strtolower($giocatori[$i][$j])))."',";
	$query = substr($query, 0, -1);		
	$query .= '),';
	
	$vale = $giocatori[$i][6];
	$punti = $giocatori[$i][7] + $giocatori[$i][13] + ($giocatori[$i][14] / 2); //punti=magicpunti+golvittoria+golpareggio
	if ($ok)
		$ok = $db->query('UPDATE giocatoriformazioni SET vale='.$vale.',punti='.$punti.' WHERE giocatore='.$giocatori[$i][0].' AND formazione IN (SELECT id FROM formazioni WHERE giornata='.$giornata.')');
	if ($ok)
		echo 'Giocatore '.$giocatori[$i][0].', gioca '.$giocatori[$i][6].', punti '.$giocatori[$i][7].'<br>';
}
	
$query = substr($query, 0, -1);
if ($ok)
	$ok = $db->query($query);
if ($ok)
	echo 'Inseriti nuovi records in giocatorigiornate<br>';

if ($ok)
	$ok = $db->query('DELETE FROM giocatori');
if ($ok)
	echo 'Cancellati dati giocatori<br>';
$query = 'INSERT INTO giocatori (id,ruolo,nome,cognome) VALUES';
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
if ($ok)
	$ok = $db->query($query);
if ($ok)
	echo 'Inseriti nuovi records in giocatori<br>';
	
if ($ok)
	$ok = $db->query('UPDATE giocatoriformazioni SET gioca=1 WHERE vale=1 AND pos<11 AND formazione IN (SELECT id FROM formazioni WHERE giornata='.$giornata.')');
if ($ok)
	echo 'giocatoriformazioni: update intermedio 1<br>';
if ($ok)
	$ok = $db->query('UPDATE giocatoriformazioni SET gioca=0 WHERE vale=0 AND pos>=11 AND formazione IN (SELECT id FROM formazioni WHERE giornata='.$giornata.')');
if ($ok)
	echo 'giocatoriformazioni: update intermedio 2<br>';
$result = $db->query('SELECT a.id AS id FROM formazioni AS a,formazioni AS b WHERE a.data=b.data AND a.squadra=b.squadra AND a.giornata='.$giornata.' AND b.giornata=a.giornata-1');
$ingroup = '';
while ($row = $result->fetch_assoc())
	$ingroup .= $row['id'].',';
$result->free();
if ($ok && $ingroup)
	$ok = $db->query('UPDATE formazioni SET tifosi=tifosi-1000 WHERE id IN ('.substr($ingroup, 0, -1).') AND tifosi>=1000');
if ($ok)
	echo 'formazioni: update intermedio<br>';
$result = $db->query('SELECT id,squadra,modulo,partita FROM formazioni WHERE giornata='.$giornata);
while ($row = $result->fetch_assoc())
{
	$row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND gioca=1 AND giocatore<200')->fetch_assoc();
	$por = $row1['n'];
	$row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND gioca=1 AND giocatore>=200 AND giocatore<500')->fetch_assoc();
	$dif = $row1['n'];
	$row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND gioca=1 AND giocatore>=500 AND giocatore<800')->fetch_assoc();
	$cen = $row1['n'];
	$row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND gioca=1 AND giocatore>=800')->fetch_assoc();
	$att = $row1['n'];
	$sostituzioni = 0;
	$giocanoin = $por + $dif + $cen + $att;
	$i = 11; //indice: parto dal portiere di riserva
	while ($giocanoin < 11 && $sostituzioni < 3 && $i < 18) //svolgo i cambi
	{
		$result1 = $db->query('SELECT giocatore,vale FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND pos='.$i);
		$row1 = $result1->fetch_assoc();
		if ($row1['vale'])
		{
			switch ($row1['giocatore'])
			{
				case ($row1['giocatore'] < 200): $por++; break;
				case ($row1['giocatore'] >= 200 && $row1['giocatore'] < 500): $dif++; break;
				case ($row1['giocatore'] >= 500 && $row1['giocatore'] < 800): $cen++; break;
				case ($row1['giocatore'] >= 800): $att++; break;
			}
			$sostvalida = 1;
			if ($por > 1 || $dif > 5 || $cen > 5 || $att > 3) //controllo modulo
	    		$sostvalida=0;
	  		if ($dif == 5 && $cen == 5)
	    		$sostvalida=0;
	  		if ($dif > 4 && $att == 3)
	    		$sostvalida=0;
	  		if ($cen > 4 && $att == 3)
	    		$sostvalida=0;
	  		if ($dif + $cen + $att > 10)
	    		$sostvalida=0;
	    	if (!$sostvalida)
	    	{
		    	switch ($row1['giocatore'])
				{
					case ($row1['giocatore'] < 200): $por--; break;
					case ($row1['giocatore'] >= 200 && $row1['giocatore'] < 500): $dif--; break;
					case ($row1['giocatore'] >= 500 && $row1['giocatore'] < 800): $cen--; break;
					case ($row1['giocatore'] >= 800): $att--; break;
				}
	    	}
	    	else //sostituzione valida
	    	{
	    		$sostituzioni++;
	    		$giocanoin++;
	    		if ($ok)
	    			$ok = $db->query('UPDATE giocatoriformazioni SET gioca=1 WHERE formazione='.$row['id'].' AND giocatore='.$row1['giocatore']);
	    		if ($ok)
					echo 'sostituzione<br>';
	    	}
		}
		$i++;
		$result1->free();
	}
	if ($ok)
		$ok = $db->query('UPDATE giocatoriformazioni SET gioca=0 WHERE formazione='.$row['id'].' AND (gioca<>1 OR gioca IS NULL)');
	$modulo = $dif.$cen.$att;
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
	$row1 = $db->query('SELECT SUM(punti) AS punti FROM giocatoriformazioni WHERE gioca=1 AND formazione='.$row['id'])->fetch_assoc();
	$punti = $row1['punti'] + $modificatore;
	if ($row['partita'])
	{
		$spettatori = 0;
		$result1 = $db->query('SELECT stadio,s1,s2 FROM partite WHERE id IN (SELECT partita FROM formazioni WHERE giornata='.$giornata.') and s1='.$row['squadra'].' AND stadio=(SELECT stadio FROM utenti WHERE id='.$row['squadra'].')');
		if ($row1 = $result1->fetch_assoc()) //gioca in casa
		{
			$row2 = $db->query('SELECT tifosi FROM utenti WHERE id='.$row1['s1'])->fetch_assoc();
			$tifosi1 = $row2['tifosi'];
			$spettatori += $tifosi1;
			$row2 = $db->query('SELECT tifosi FROM utenti WHERE id='.$row1['s2'])->fetch_assoc();
			$tifosi2 = ($row2['tifosi'] / 10);
			$spettatori += $tifosi2;
			echo 'Partita '.$row['partita'].': spettatori '.$spettatori.', di cui '.$tifosi1.' per squadra '.$row1['s1'].' e '.$tifosi2.' per squadra '.$row1['s2'];
			$punti += intval(($tifosi1 - $tifosi2) / 10000) * 0.5;
			echo '. Totale punti: '.$punti.'<br>';
		}
		else //campo neutro
		{
			$row2 = $db->query('SELECT tifosi FROM utenti WHERE id=(SELECT s1 FROM partite WHERE id='.$row['partita'].')')->fetch_assoc();
			$tifosi1 = ($row2['tifosi'] * 0.6);
			$spettatori += $tifosi1;
			$row2 = $db->query('SELECT tifosi FROM utenti WHERE id=(SELECT s2 FROM partite WHERE id='.$row['partita'].')')->fetch_assoc();
			$tifosi2 = ($row2['tifosi'] * 0.6);
			$spettatori += $tifosi2;
		}
		$result1->free();
	}
	$gols = 0;
  	$xpti = 65.5;
  	while ($xpti < $punti)
  	{
    	$gols++;
		$xpti = $xpti+6;
  	}
  	$Squadre[$row['squadra']]['giocanoin'] = $giocanoin;
  	$Squadre[$row['squadra']]['gols'] = $gols;
  	$goltit = $golpan = $ptipan = $ptimax = $votmax = $ptimaxpan = $votmaxpan = $rank = 0;
  	$result1 = $db->query('SELECT SUM(gfatti) AS goltit, MAX(magicpunti) AS ptimax, MAX(voto) AS votmax FROM giocatorigiornate WHERE giornata='.$giornata.' AND giocatore IN (SELECT giocatore FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND gioca=1)');
  	if ($row1 = $result1->fetch_assoc())
  	{
  		if ($row1['goltit'])
  			$goltit = $row1['goltit'];
  		if ($row1['ptimax'])
  			$ptimax = $row1['ptimax'];
  		if ($row1['votmax'])
  			$votmax = $row1['votmax'];
  	}
  	$result1->free();
  	$result1 = $db->query('SELECT SUM(gfatti) AS golpan, SUM(magicpunti) AS ptipan, MAX(magicpunti) AS ptimaxpan, MAX(voto) AS votmaxpan FROM giocatorigiornate WHERE giornata='.$giornata.' AND giocatore IN (SELECT giocatore FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND gioca=0)');
  	if ($row1 = $result1->fetch_assoc())
  	{
  		if ($row1['golpan'])
  			$golpan = $row1['golpan'];
  		if ($row1['ptipan'])
  			$ptipan = $row1['ptipan'];
  		if ($row1['ptimaxpan'])
  			$ptimaxpan = $row1['ptimaxpan'];
  		if ($row1['votmaxpan'])
  			$votmaxpan = $row1['votmaxpan'];
  	}
  	$result1->free();
  	$result1 = $db->query('SELECT ordine FROM utenti WHERE id='.$row['squadra']);
  	if ($row1 = $result1->fetch_assoc())
  		$rank = $row1['ordine'];
  	$result1->free();
  	if ($ok)
  		$ok = $db->query('UPDATE formazioni SET modulo=\''.$modulo.'\', punti='.$punti.', goltit='.$goltit.', golpan='.$golpan.', ptipan='.$ptipan.', ptimax='.$ptimax.', votmax='.$votmax.', ptimaxpan='.$ptimaxpan.', votmaxpan='.$votmaxpan.', rank='.$rank.' WHERE id='.$row['id']);
  	if ($ok)
		echo 'formazioni: update modulo e punti<br>';
  	if ($ok && $row['partita'])
  		$ok = $db->query('UPDATE partite SET punti1='.$punti.',gol1='.$gols.',spettatori='.$spettatori.' WHERE id=(SELECT partita FROM formazioni WHERE id='.$row['id'].') AND s1='.$row['squadra']);
  	if ($ok && $row['partita'])
		echo 'partite: update in casa<br>';
  	if ($ok && $row['partita'])
  		$ok = $db->query('UPDATE partite SET punti2='.$punti.',gol2='.$gols.' WHERE id=(SELECT partita FROM formazioni WHERE id='.$row['id'].') AND s2='.$row['squadra']);
  	if ($ok && $row['partita'])
		echo 'partite: update in trasferta<br>';
}		
$result->free();
if ($ok)
	echo 'Aggiornata giocatoriformazioni e formazioni<br>';
	
$result = $db->query('SELECT * FROM partite WHERE id IN (SELECT partita FROM formazioni WHERE giornata='.$giornata.')');
while ($row = $result->fetch_assoc())
{
	if (($row['punti1'] < 60) && ($row['punti2'] > 60) && (($row['punti2'] - $row['punti1']) >= 4))
	{
	    $row['gol2'] += 1;
	    if ($ok)
	    	$ok = $db->query('UPDATE partite SET gol2='.$row['gol2'].' WHERE id='.$row['id']);
	}
	if (($row['punti2'] < 60) && ($row['punti1'] > 60) && (($row['punti1'] - $row['punti2']) >= 4))
	{
	    $row['gol1'] += 1;
	    if ($ok)
	    	$ok = $db->query('UPDATE partite SET gol1='.$row['gol1'].' WHERE id='.$row['id']);
	}
	if ($row['gol1'] > $row['gol2'])
	{
		$Squadre[$row['s1']]['trisultato'] = 1000;
		$Squadre[$row['s2']]['trisultato'] = -500;
	}
	else if ($row['gol2'] > $row['gol1'])
	{
		$Squadre[$row['s1']]['trisultato'] = -1000;
		$Squadre[$row['s2']]['trisultato'] = 1500;
	}
	else 
	{
		$Squadre[$row['s1']]['trisultato'] = 0;
		$Squadre[$row['s2']]['trisultato'] = 500;
	}
	$Squadre[$row['s1']]['gols'] = $row['gol1'];
	$Squadre[$row['s2']]['gols'] = $row['gol2'];
}
$result->free();

$posizione = 1;
$result = $db->query('SELECT id,squadra,punti,tifosi FROM formazioni WHERE giornata='.$giornata.' ORDER BY punti DESC, goltit DESC, golpan DESC, ptipan DESC, ptimax DESC, votmax DESC, ptimaxpan DESC, votmaxpan DESC, rank DESC');
while ($row = $result->fetch_assoc())
{
	$tifosi = $row['tifosi'] + ($Squadre[$row['squadra']]['gols'] + $Squadre[$row['squadra']]['giocanoin'] - 10) * 100 + $Squadre[$row['squadra']]['trisultato'];
	if ($ok)
		$ok = $db->query('UPDATE utenti SET tifosi='.$tifosi.' WHERE id='.$row['squadra']);
	if ($ok)
		$ok = $db->query('UPDATE formazioni SET ordine='.$posizione.', tifosi='.$tifosi.' WHERE id='.$row['id']);
	if ($ok)
		$ok = $db->query('UPDATE formazioni SET tifosi='.$tifosi.' WHERE squadra='.$row['squadra'].' AND giornata='.($giornata+1));
	$posizione++;
}
$result->free();

$posizione = 1;
$result = $db->query('SELECT squadra FROM formazioni WHERE giornata<='.$giornata.' GROUP BY squadra ORDER BY SUM(punti) DESC, SUM(goltit) DESC, SUM(golpan) DESC, SUM(ptipan) DESC, SUM(ptimax) DESC, SUM(votmax) DESC, SUM(ptimaxpan) DESC, SUM(votmaxpan) DESC');
while ($row = $result->fetch_assoc())
{
	if ($ok)
		$ok = $db->query('UPDATE utenti SET ordine='.$posizione.' WHERE id='.$row['squadra']);
	$posizione++;
}
$result->free();
	
if ($ok)
	$ok = $db->query('INSERT INTO giocgiorsq (giornata,giocatore,squadra,pos,numero) (SELECT '.($giornata+1).',giocatore,squadra,pos,numero FROM giocgiorsq WHERE giornata='.$giornata.')');
if ($ok)
	echo 'Inseriti nuovi records in giocgiorsq<br>';
	
$result = $db->query('SELECT a.data AS data,a.modulo AS modulo,a.id AS a_id,b.id AS b_id FROM formazioni AS a, formazioni AS b WHERE a.squadra=b.squadra AND a.giornata='.$giornata.' AND b.giornata=a.giornata+1 AND b.data IS NULL');
while ($row = $result->fetch_assoc())
{
	if ($ok)
		$ok = $db->query('INSERT INTO giocatoriformazioni (formazione,pos,giocatore) (SELECT '.$row['b_id'].',pos,giocatore FROM giocatoriformazioni WHERE formazione='.$row['a_id'].')');
	if ($ok)
		$ok = $db->query('UPDATE formazioni SET data=\''.$row['data'].'\',modulo=\''.$row['modulo'].'\' WHERE id='.$row['b_id']);
}		
$result->free();
if ($ok)
	echo 'Inseriti nuovi records in giocatoriformazioni<br>';
	
if ($ok)
	$ok = $db->query('UPDATE utenti SET tifosi=0 WHERE tifosi>250000');
if ($ok)
	$ok = $db->query('UPDATE formazioni SET tifosi=0 WHERE tifosi>250000');
if ($ok)
	echo 'Corretti tifosi in overflow<br>';

if ($ok && $user->admin)
{
	$db->commit();
	echo 'PROCESSO TERMINATO CORRETTAMENTE';
}
else 
{
	$db->rollback();
	echo 'ERRORE!';
}

$db->close();
$html->close();

?>