<?php

include_once 'class/db.class.php';

function getGiornata()
{
	$db = new db();
	$result = $db->query("SELECT MAX(giornata) AS g FROM giocatorigiornate");
	$db->close();
	if ($row = $result->fetch_assoc())
		return $row['g']+1;
	else 
		return 0;
}

//ritorna la data di giornata se esiste, 0 altrimenti
function getGiornataData($g)
{
	$db = new db();
	$result = $db->query("SELECT data FROM giornate WHERE id=".$g);
	$db->close();
	if ($row = $result->fetch_assoc())
		return $row['data'];
	else
		return 0;
}

//ritorna il torneo di una giornata, 0 altrimenti
function getGiornataTorneo($g)
{
	$db = new db();
	$result = $db->query("SELECT torneo FROM giornate WHERE id=".$g);
	$db->close();
	if ($row = $result->fetch_assoc())
		return $row['torneo'];
	else
		return 0;
}

//ritorna il turno di una giornata per una determinata squadra
function getSquadraTurno($squadra, $g)
{
	$db = new db();
	$result = $db->query("SELECT turno FROM formazioni WHERE squadra=".$squadra." AND giornata=".$g);
	$db->close();
	if ($row = $result->fetch_assoc())
		return $row['turno'];
	else
		return 0;
}

//ritorna la data di fine mercato se aperto, 0 altrimenti
function getMercatoStatus()
{
	$db = new db();
	$g = getGiornata();
	$data = date("Y-m-d H:i:s");
	$result = $db->query("SELECT fine FROM mercato WHERE '".$data."' BETWEEN inizio AND fine AND giornata<=".$g);
	$db->close();
	if ($row = $result->fetch_assoc())
		return $row['fine'];
	else
		return 0;
}

//ritorna la data di fine invio formazione se possibile, 0 altrimenti
function getFormazioneStatus($g)
{
	$db = new db();
	$data = date("Y-m-d H:i:s");
	$result = $db->query("SELECT DATE_SUB(data, INTERVAL 1 HOUR) AS data1 FROM giornate WHERE '".$data."'< DATE_SUB(data, INTERVAL 1 HOUR) AND id=".$g);
	$db->close();
	if ($row = $result->fetch_assoc())
		return $row['data1'];
	else
		return 0;
}

//ritorna la lista dei giocatori fuori lista per una squadra, 0 altrimenti
function getFuoriLista($utente)
{
	$db = new db();
	$g = getGiornata();
	$data = date("Y-m-d H:i:s");
	$result = $db->query("SELECT a.giocatore FROM giocatorigiornate AS a,giocgiorsq AS b WHERE a.giornata=b.giornata-1 AND a.giocatore=b.giocatore AND b.squadra=".$utente." AND a.inlista=0 AND b.giornata=".$g);
	$db->close();
	$ret = array();
	while ($row = $result->fetch_assoc())
		$ret[] = $row['giocatore'];
	if (count($ret))
		return $ret;
	return 0;
}

//ritorna il nome di una squadra dall'id, 0 altrimenti
function getSquadraNome($id)
{
	if (!$id)
		return '';
	$db = new db();
	$result = $db->query("SELECT squadra FROM utenti WHERE id=".$id);
	$db->close();
	if ($row = $result->fetch_assoc())
		return $row['squadra'];
	else
		return 0;
}

//manda l'email di attivazione all'utente
function sendActivationEmail($id)
{
	if (!$id)
		return '';
	$db = new db();
	$result = $db->query("SELECT nick, nome, cognome, squadra, email FROM utenti WHERE id=".$id);
	$db->close();
	if ($row = $result->fetch_assoc())
	{
		$subject = 'Attivazione della squadra su Fantalegamento.it';
		$message = '
		<html>
			<head>
  				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   				<title>Registrazione a Fantalegamento.it</title>
			</head>
			<body>
   				<p>Cara/o '.$row['nome'].',</p>
   				<p>grazie per esserti iscritta/o al Fantalegamento!<br>
   				Per attivare la tua squadra e rendere valida la tua iscrizione, segui l\'indirizzo qui sotto:<br>
   				<a href="http://www.fantalegamento.it/signup.php?attiva=1&id='.$id.'">http://www.fantalegamento.it/signup.php?attiva=1&id='.$id.'</a></p>
   				<p>Una volta attivata la tua squadra, potrai accedere al sito <a href="http://www.fantalegamento.it">www.fantalegamento.it</a> e cominciare a giocare.</p>
   				<p>I tuoi dati di registrazione sono i seguenti:<br>
   				Nome: '.$row['nome'].'<br>
   				Cognome: '.$row['cognome'].'<br>
   				Nickname: '.$row['nick'].'<br>
   				Squadra: '.$row['squadra'].'<br>
   				</p>
   				<p>Se hai dimenticato la tua password o se hai qualche domanda da fare, non esitare a mandare una mail all\'indirizzo <a href="mailto:info@fantalegamento.it">info@fantalegamento.it</a>.</p>
   				<p>Lo staff di www.fantalegamento.it ti augura un buon campionato!</p>
			</body>
		</html>';
		$headers = 'From: Fantalegamento <noreply@fantalegamento.it>' . "\r\n" .
    				'Reply-To: info@fantalegamento.it' . "\r\n" .
					'Content-type: text/html; charset=utf-8' . "\r\n";
    				'X-Mailer: PHP/' . phpversion();

		mail($row['email'], $subject, $message, $headers);
		return 1;
	}
	else
		return 0;
}

//update di giornata
function update_giornata($giornata, $text)
{
	$db = new db();
	$log = 'Update giornata '.$giornata.' - '.date('d-m-Y H:i:s').PHP_EOL;
	
	$db->begin();
	$ok = 1;
	
	$query = 'INSERT INTO giocatorigiornate (giornata, giocatore, squadra, inlista, giocato, magicpunti, 6politico, famedia, voto, gfatti, gsubiti, gv, gp, assist, ammonito, espulso, rtirati, rcontro, rparati, rsbagliati, autogol, presente, titolare, 25min, casa, prezzo) VALUES';
	$text = utf8_decode(str_replace('"','',$text));
	//echo $text;
	$lines = explode("\n",$text);
	foreach ($lines as $line)
		if (strlen(trim($line))>0)
			$giocatori[] = explode("|",$line);
	for ($i = 0; $i < count($giocatori); $i++)
	{
		$punti = $giocatori[$i][10] + (6 * $giocatori[$i][8]) + (3 * $giocatori[$i][11]) - $giocatori[$i][12] + $giocatori[$i][13] + ($giocatori[$i][14] / 2) + $giocatori[$i][15] - ($giocatori[$i][16] / 2) - $giocatori[$i][17] + (3 * $giocatori[$i][20]) - (3 * $giocatori[$i][21]) - (2 * $giocatori[$i][22]);
		$vale = $giocatori[$i][6];
		/*if ($vale && !$giocatori[$i][8] && !$giocatori[$i][7]) //sv che conta lo stesso
			$punti += 6;*/
		//if ($vale && !$giocatori[$i][8] && !$giocatori[$i][10]) //sv che conta lo stesso (szszesny sv giornata 5 2018-19. !0.0 non ritorna 1)
		if ($vale && !$giocatori[$i][8] && !$giocatori[$i][9]) //sv che conta lo stesso
			$punti = $giocatori[$i][7];
		$query .= '('.$giornata.',';
		for ($j = 0; $j < 28; $j++) //28 = numero campi file txt
			if ($j != 1 && $j != 2 && $j != 3 && $j != 5 && $j != 7) //1 = giornata, 2 = nome, 3 = squadra, 5 = ruolo, 7 = magic punti
				$query .= "'".$db->escape($giocatori[$i][$j])."',";
			else if ($j == 3) //nome squadra->iniziale in maiuscolo
				$query .= "'".$db->escape(ucfirst(strtolower($giocatori[$i][$j])))."',";
			else if ($j == 7)
				$query .= "'".$db->escape($punti)."',";
		$query = substr($query, 0, -1);		
		$query .= '),';
		
		if ($ok)
		{
			$ok = $db->query('UPDATE giocatoriformazioni SET vale='.$vale.',punti='.$punti.' WHERE giocatore='.$giocatori[$i][0].' AND formazione IN (SELECT id FROM formazioni WHERE giornata='.$giornata.')');
			$log .= 'Giocatore '.$giocatori[$i][0].', gioca '.$giocatori[$i][6].', magicpunti '.$giocatori[$i][7].', punti '.$punti.($giocatori[$i][7]!=$punti?' MISMATCH':'').PHP_EOL;
			$error_code = 1;
		}
	}
		
	$query = substr($query, 0, -1);
	if ($ok)
	{
		$ok = $db->query($query);
		$log .= 'Inseriti nuovi records in giocatorigiornate'.PHP_EOL;
		$error_code = 2;
	}
	
	if ($ok)
	{
		$ok = $db->query('DELETE FROM giocatori');
		$log .= 'Cancellati dati giocatori'.PHP_EOL;
		$error_code = 3;
	}
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
	{
		$ok = $db->query($query);
		$log .= 'Inseriti nuovi records in giocatori'.PHP_EOL;
		$error_code = 4;
	}
		
	if ($ok)
	{
		$ok = $db->query('UPDATE giocatoriformazioni SET gioca=1 WHERE vale=1 AND pos<11 AND formazione IN (SELECT id FROM formazioni WHERE giornata='.$giornata.')');
		$log .= 'giocatoriformazioni: update intermedio 1'.PHP_EOL;
		$error_code = 5;
	}
	if ($ok)
	{
		$ok = $db->query('UPDATE giocatoriformazioni SET gioca=0 WHERE vale=0 AND pos>=11 AND formazione IN (SELECT id FROM formazioni WHERE giornata='.$giornata.')');
		$log .= 'giocatoriformazioni: update intermedio 2'.PHP_EOL;
		$error_code = 6;
	}
	$result = $db->query('SELECT a.id AS id FROM formazioni AS a,formazioni AS b WHERE a.data=b.data AND a.squadra=b.squadra AND a.giornata='.$giornata.' AND b.giornata=a.giornata-1');
	$ingroup = '';
	while ($row = $result->fetch_assoc())
		$ingroup .= $row['id'].',';
	$result->free();
	if ($ok && $ingroup)
	{
		$ok = $db->query('UPDATE formazioni SET tifosi=tifosi-1000 WHERE id IN ('.substr($ingroup, 0, -1).') AND tifosi>=1000');
		$log .= 'formazioni: update intermedio'.PHP_EOL;
		$error_code = 7;
	}
	$result = $db->query('SELECT id,squadra,modulo,partita FROM formazioni WHERE giornata='.$giornata);
	while ($row = $result->fetch_assoc())
	{
		/*$row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND gioca=1 AND giocatore<200')->fetch_assoc();
		$por = $row1['n'];
		$row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND gioca=1 AND giocatore>=200 AND giocatore<500')->fetch_assoc();
		$dif = $row1['n'];
		$row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND gioca=1 AND giocatore>=500 AND giocatore<800')->fetch_assoc();
		$cen = $row1['n'];
		$row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND gioca=1 AND giocatore>=800')->fetch_assoc();
		$att = $row1['n'];*/ //UPDATE 23/02/2020: con i trequartisti serve controllare il ruolo, non il codice. Giocatori con codice 700 circa possono essere cen o att
	    $row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni LEFT JOIN giocatori ON giocatoriformazioni.giocatore=giocatori.id WHERE formazione='.$row['id'].' AND gioca=1 AND ruolo=0')->fetch_assoc();
	    $por = $row1['n'];
	    $row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni LEFT JOIN giocatori ON giocatoriformazioni.giocatore=giocatori.id WHERE formazione='.$row['id'].' AND gioca=1 AND ruolo=1')->fetch_assoc();
	    $dif = $row1['n'];
	    $row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni LEFT JOIN giocatori ON giocatoriformazioni.giocatore=giocatori.id WHERE formazione='.$row['id'].' AND gioca=1 AND ruolo=2')->fetch_assoc();
	    $cen = $row1['n'];
	    $row1 = $db->query('SELECT count(giocatore) AS n FROM giocatoriformazioni LEFT JOIN giocatori ON giocatoriformazioni.giocatore=giocatori.id WHERE formazione='.$row['id'].' AND gioca=1 AND ruolo=3')->fetch_assoc();
	    $att = $row1['n'];
		$sostituzioni = 0;
		$giocanoin = $por + $dif + $cen + $att;
		$log .= 'Formazione '.$row['id']." modulo ".$por.$dif.$cen.$att.": giocano in ".$giocanoin.PHP_EOL;
		$i = 11; //indice: parto dal portiere di riserva
		while ($giocanoin < 11 && $sostituzioni < 3 && $i < 18) //svolgo i cambi
		{
		    //$result1 = $db->query('SELECT giocatore,vale FROM giocatoriformazioni WHERE formazione='.$row['id'].' AND pos='.$i); //UPDATE 23/02/2020: con i trequartisti serve controllare il ruolo, non il codice. Giocatori con codice 700 circa possono essere cen o att
		    $result1 = $db->query('SELECT giocatore,vale,ruolo FROM giocatoriformazioni LEFT JOIN giocatori ON giocatoriformazioni.giocatore=giocatori.id WHERE formazione='.$row['id'].' AND pos='.$i);
			$row1 = $result1->fetch_assoc();
			if ($row1['vale'])
			{
				/*switch ($row1['giocatore'])
				{
					case ($row1['giocatore'] < 200): $por++; break;
					case ($row1['giocatore'] >= 200 && $row1['giocatore'] < 500): $dif++; break;
					case ($row1['giocatore'] >= 500 && $row1['giocatore'] < 800): $cen++; break;
					case ($row1['giocatore'] >= 800): $att++; break;
				}*/ //UPDATE 23/02/2020: con i trequartisti serve controllare il ruolo, non il codice. Giocatori con codice 700 circa possono essere cen o att
			    switch ($row1['ruolo'])
			    {
			        case 0: $por++; break;
			        case 1: $dif++; break;
			        case 2: $cen++; break;
			        case 3: $att++; break;
			    }
			    $log .= 'Formazione '.$row['id']." vuole modulo ".$por.$dif.$cen.$att.": ";
			    $sostvalida = 1;
				if ($por > 1 || $dif > 5 || $cen > 5 || $att > 3) //controllo modulo
				{
		    		$sostvalida=0;
		    		$log .= "invalida: troppi giocatori in un ruolo".PHP_EOL;
				}
		  		if ($dif == 5 && $cen == 5)
		  		{
		    		$sostvalida=0;
		    		$log .= "invalida: 5 difensori e 5 centrocampisti".PHP_EOL;
		  		}
		  		if ($dif > 4 && $att == 3)
		  		{
		    		$sostvalida=0;
		    		$log .= "invalida: >4 difensori e 3 attaccanti".PHP_EOL;
		  		}
		  		if ($cen > 4 && $att == 3)
		  		{
		    		$sostvalida=0;
		    		$log .= "invalida: >4 centrocampisti e 3 attaccanti".PHP_EOL;
		  		}
		  		if ($dif + $cen + $att > 10)
		  		{
		    		$sostvalida=0;
		    		$log .= "invalida: >10 giocatori".PHP_EOL;
		  		}
		    	if (!$sostvalida)
		    	{
			    	/*switch ($row1['giocatore'])
					{
						case ($row1['giocatore'] < 200): $por--; break;
						case ($row1['giocatore'] >= 200 && $row1['giocatore'] < 500): $dif--; break;
						case ($row1['giocatore'] >= 500 && $row1['giocatore'] < 800): $cen--; break;
						case ($row1['giocatore'] >= 800): $att--; break;
			    	}*/ //UPDATE 23/02/2020: con i trequartisti serve controllare il ruolo, non il codice. Giocatori con codice 700 circa possono essere cen o att
		    	    switch ($row1['ruolo'])
		    	    {
		    	        case 0: $por--; break;
		    	        case 1: $dif--; break;
		    	        case 2: $cen--; break;
		    	        case 3: $att--; break;
		    	    }
		    	}
		    	else //sostituzione valida
		    	{
		    	    $log .= "valida".PHP_EOL;
		    		$sostituzioni++;
		    		$giocanoin++;
		    		if ($ok)
		    		{
		    			$ok = $db->query('UPDATE giocatoriformazioni SET gioca=1 WHERE formazione='.$row['id'].' AND giocatore='.$row1['giocatore']);
		    			$log .= 'sostituzione in formazione '.$row['id']." modulo ".$por.$dif.$cen.$att.": entra ".$row1['giocatore']." ruolo ".$row1['ruolo'].PHP_EOL;
		    			$error_code = 8;
		    		}
		    	}
			}
			$i++;
			$result1->free();
		}
		if ($ok)
		{
			$ok = $db->query('UPDATE giocatoriformazioni SET gioca=0 WHERE formazione='.$row['id'].' AND (gioca<>1 OR gioca IS NULL)');
			$error_code = 9;
		}
		$modulo = $dif.$cen.$att;
		$modificatore = 0;
		if($por == 1 && $dif >= 4)
		{
		    //print_r($db->query('SELECT * FROM giocatorigiornate WHERE giornata='.$giornata.' AND giocatore=(SELECT giocatore FROM giocatoriformazioni WHERE giocatore<200 AND gioca=1 AND formazione='.$row['id'].')')->fetch_assoc());
		    $result3 = $db->query('SELECT giocato, famedia, magicpunti, voto FROM giocatorigiornate WHERE giornata='.$giornata.' AND giocatore=(SELECT giocatore FROM giocatoriformazioni WHERE giocatore<200 AND gioca=1 AND formazione='.$row['id'].')');
		    if($result3)
		        $row1 = $result3->fetch_assoc();
		    else 
		        print_r($result3);
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
				$log .= 'Partita '.$row['partita'].': spettatori '.$spettatori.', di cui '.$tifosi1.' per squadra '.$row1['s1'].' e '.$tifosi2.' per squadra '.$row1['s2'];
				$punti += intval(($tifosi1 - $tifosi2) / 10000) * 0.5;
				$log .= '. Totale punti: '.$punti.PHP_EOL;
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
	  	{
	  		$ok = $db->query('UPDATE formazioni SET modulo=\''.$modulo.'\', punti='.$punti.', goltit='.$goltit.', golpan='.$golpan.', ptipan='.$ptipan.', ptimax='.$ptimax.', votmax='.$votmax.', ptimaxpan='.$ptimaxpan.', votmaxpan='.$votmaxpan.', rank='.$rank.' WHERE id='.$row['id']);
			$log .= 'formazioni: update modulo e punti'.PHP_EOL;
			$error_code = 10;
	  	}
	  	if ($ok && $row['partita'])
	  	{
	  		$ok = $db->query('UPDATE partite SET punti1='.$punti.',gol1='.$gols.',spettatori='.$spettatori.' WHERE id=(SELECT partita FROM formazioni WHERE id='.$row['id'].') AND s1='.$row['squadra']);
	  		$log .= 'partite: update in casa'.PHP_EOL;
	  		$error_code = 11;
	  	}
	  	if ($ok && $row['partita'])
	  	{
	  		$ok = $db->query('UPDATE partite SET punti2='.$punti.',gol2='.$gols.' WHERE id=(SELECT partita FROM formazioni WHERE id='.$row['id'].') AND s2='.$row['squadra']);
	  		$log .= 'partite: update in trasferta'.PHP_EOL;
	  		$error_code = 12;
	  	}
	}		
	$result->free();
	if ($ok)
		$log .= 'Aggiornata giocatoriformazioni e formazioni'.PHP_EOL;
		
	$result = $db->query('SELECT * FROM partite WHERE id IN (SELECT partita FROM formazioni WHERE giornata='.$giornata.')');
	while ($row = $result->fetch_assoc())
	{
		if (($row['punti1'] < 60) && ($row['punti2'] > 60) && (($row['punti2'] - $row['punti1']) >= 4))
		{
		    $row['gol2'] += 1;
		    if ($ok)
		    {
		    	$ok = $db->query('UPDATE partite SET gol2='.$row['gol2'].' WHERE id='.$row['id']);
		    	$error_code = 13;
		    }
		}
		if (($row['punti2'] < 60) && ($row['punti1'] > 60) && (($row['punti1'] - $row['punti2']) >= 4))
		{
		    $row['gol1'] += 1;
		    if ($ok)
		    {
		    	$ok = $db->query('UPDATE partite SET gol1='.$row['gol1'].' WHERE id='.$row['id']);
		    	$error_code = 14;
		    }
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
	    $tifosi_precedenti = $row['tifosi'];
	    if ($tifosi_precedenti == 0) //correzione tifosi prima giornata
	    {
	        $result_fix = $db->query('SELECT tifosi FROM utenti WHERE id='.$row['squadra']);
	        $row_fix = $result_fix->fetch_assoc();
	        if ($row_fix['tifosi'] > 0)
	           $tifosi_precedenti = $row_fix['tifosi'];
	        $result_fix->free();	        
	    }
	    $tifosi = $tifosi_precedenti + ($Squadre[$row['squadra']]['gols'] + $Squadre[$row['squadra']]['giocanoin'] - 10) * 100 + $Squadre[$row['squadra']]['trisultato'];
		if ($tifosi < 0) //aggiunto 23/9/2020 dava errore 15 su tifosi negativi
		    $tifosi = 0;
		if ($ok)
		{
			$ok = $db->query('UPDATE utenti SET tifosi='.$tifosi.' WHERE id='.$row['squadra']);
			$error_code = 15;
		}
		if ($ok)
		{
			$ok = $db->query('UPDATE formazioni SET ordine='.$posizione.', tifosi='.$tifosi.' WHERE id='.$row['id']);
			$error_code = 16;
		}
		if ($ok)
		{
			$ok = $db->query('UPDATE formazioni SET tifosi='.$tifosi.' WHERE squadra='.$row['squadra'].' AND giornata='.($giornata+1));
			$error_code = 17;
		}
		$posizione++;
	}
	$result->free();
	
	$posizione = 1;
	$result = $db->query('SELECT squadra FROM formazioni WHERE giornata<='.$giornata.' GROUP BY squadra ORDER BY SUM(punti) DESC, SUM(goltit) DESC, SUM(golpan) DESC, SUM(ptipan) DESC, SUM(ptimax) DESC, SUM(votmax) DESC, SUM(ptimaxpan) DESC, SUM(votmaxpan) DESC');
	while ($row = $result->fetch_assoc())
	{
		if ($ok)
		{
			$ok = $db->query('UPDATE utenti SET ordine='.$posizione.' WHERE id='.$row['squadra']);
			$error_code = 18;
		}
		$posizione++;
	}
	$result->free();
		
	if ($ok)
	{
		$ok = $db->query('INSERT INTO giocgiorsq (giornata,giocatore,squadra,pos,numero) (SELECT '.($giornata+1).',giocatore,squadra,pos,numero FROM giocgiorsq WHERE giornata='.$giornata.')');
		$log .= 'Inseriti nuovi records in giocgiorsq'.PHP_EOL;
		$error_code = 19;
	}
		
	$result = $db->query('SELECT a.data AS data,a.modulo AS modulo,a.id AS a_id,b.id AS b_id FROM formazioni AS a, formazioni AS b WHERE a.squadra=b.squadra AND a.giornata='.$giornata.' AND b.giornata=a.giornata+1 AND b.data IS NULL');
	while ($row = $result->fetch_assoc())
	{
		if ($ok)
		{
			$ok = $db->query('INSERT INTO giocatoriformazioni (formazione,pos,giocatore) (SELECT '.$row['b_id'].',pos,giocatore FROM giocatoriformazioni WHERE formazione='.$row['a_id'].')');
			if ($row['data'] == '') //hotfix errore 20 su data nulla 23/9/2020
			     $ok = $db->query('UPDATE formazioni SET data=NULL, modulo=\''.$row['modulo'].'\' WHERE id='.$row['b_id']);
			else
			     $ok = $db->query('UPDATE formazioni SET data=\''.$row['data'].'\',modulo=\''.$row['modulo'].'\' WHERE id='.$row['b_id']);
			$error_code = 20;
		}
	}		
	$result->free();
	if ($ok)
		$log .= 'Inseriti nuovi records in giocatoriformazioni'.PHP_EOL;
		
	if ($ok)
	{
		$ok = $db->query('UPDATE utenti SET tifosi=0 WHERE tifosi>250000');
		$error_code = 21;
	}
	if ($ok)
	{
		$ok = $db->query('UPDATE formazioni SET tifosi=0 WHERE tifosi>250000');
		$log .= 'Corretti tifosi in overflow'.PHP_EOL;
		$error_code = 22;
	}
	
	if ($ok)
	{
		$return = 1;
		$db->commit();
		$log .= 'PROCESSO TERMINATO CORRETTAMENTE';
	}
	else 
	{
		$return = 0;
		$db->rollback();
		$log .= 'ERRORE '.$error_code;
	}
	
	$log_file = fopen(_LOG_GIORNATA, "w+");
	fwrite($log_file, $log);
	fclose($log_file);
	$db->close();
	
	return $return;
}
?>