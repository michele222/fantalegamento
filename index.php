<?php
include_once 'include/main.php';

if($_GET['action'] == 'logout')
{
	$user->logout();
	session_destroy();
	header('Location: login.php');
}

$db = new db();
$html = new html("HOME");

if (isset($_POST['submitnews']))
    if (strlen(trim($_POST['text']))>0)
	   $db->query('INSERT INTO news (timestamp,mittente,destinatario,testo) VALUES (\''.date('Y-m-d H:i:s').'\','.($_POST['adminchk']?0:$user->id).','.$db->escape($_POST['squadra']).',\''.$db->escape(trim($_POST['text'])).'\')');

if (isset($_POST['submitcomment']))
    if (strlen(trim($_POST['text']))>0)
	   $db->query('INSERT INTO commenti (data,autore,news,testo) VALUES (\''.date('Y-m-d H:i:s').'\','.($_POST['adminchkcomm']?0:$user->id).','.$db->escape($_POST['news']).',\''.$db->escape(trim($_POST['text'])).'\')');


//$html->title('News',_FLLOGO);
$g = getGiornata();

$g_selezionata = min($g,38);
if (isset($_GET['g']))
    if ($_GET['g']>=1 && $_GET['g']<=38)
        $g_selezionata = intval($_GET['g']);
        
$formazione = getFormazioneStatus($g);
$mercato = getMercatoStatus();
$giornata = getGiornataData($g);
$giornata_ultima = getGiornataData($g-1);
$giornata_sel = ($g==$g_selezionata?$giornata:getGiornataData($g_selezionata));
$torneo = getGiornataTorneo($g);
$torneo_ultima = getGiornataTorneo($g-1);
$torneo_sel = ($g==$g_selezionata?$torneo:getGiornataTorneo($g_selezionata));
$Ruoli = array('por','dif','cen','att');
switch ($torneo_sel)
{
	case "Torneo di Qualificazione":
		$torneo_logo = _TQLOGO;
		break;
	case "Torneo d'Andata":
		$torneo_logo = _TALOGO;
		break;
	case "Torneo di Ritorno": 
		$torneo_logo = _TRLOGO;
		break;
	case "Torneo Finale": 
		$torneo_logo = _TFLOGO;
		break;
	case "Fantalegamento Cup": 
		$torneo_logo = _FCLOGO;
		break;
	case "Supercoppa": 
		$torneo_logo = _SCLOGO;
		break;
}
$turno = getSquadraTurno($user->id, $g);
$turno_sel = ($g==$g_selezionata?$turno:getSquadraTurno($user->id, $g_selezionata));
	echo <<<main
		<div id="lefttable">
main;
			$html->tabletitle('Benvenuto, '.($user->guest?'ospite':$user->nome));
	echo <<<main
			<table width=100% cellspacing=0>
				<tr>
					<td width=50% rowspan=2>
main;
					echo '<div class="img_squadra_container"><img class="img_squadra" src="'.($user->guest?_DEFAULTLOGO:$user->stemma).'" /></div>';
	echo <<<main
					</td>
					<td width=50% align=center>invio formazione<br><b>
main;
					if ($formazione)
						echo date('H:i d/m/Y',strtotime($formazione));
					else
						echo "<div style='color:red'>CHIUSO</div>";
					echo <<< main
					</b></td></tr>
					<tr><td width=50% align=center>mercato<br><b>
main;
					if ($mercato)
						echo date('H:i d/m/Y',strtotime($mercato));
					else
						echo "<div style='color:red'>CHIUSO</div>";
					echo <<< main
					</b></td>
				</tr>
			</table>
main;
		if ($g <= 38)
		{
			$html->tabletitle("Prossimo turno - Giornata ".$g);
			echo <<< main
			<table width=100% cellspacing=0>
				</tr>
				<tr>
					<td colspan=2 align=center>
main;
					if ($giornata)
					{
						setlocale(LC_TIME, "it_IT");
						echo utf8_encode(strftime("%A %d %B %Y, ore %H:%M",strtotime($giornata)));
					}
					echo <<< main
					</td>
				</tr>
				<tr>
					<td colspan=2 align=center>
main;
					if ($torneo)
						echo utf8_encode($torneo);
					else 
						echo '&nbsp;';
	/*				if (!$user->guest && $turno)
						echo ' - '.utf8_encode($turno);*/
					echo <<< main
					</td>
				</tr>
			</table>
            <table width=100% cellspacing=0 class="table">
main;
        	$result = $db->query('SELECT DISTINCT partite.id,s1,s2,u1.squadra AS sq1,u2.squadra AS sq2,turno FROM formazioni,partite,utenti AS u1,utenti AS u2 WHERE s1=u1.id AND s2=u2.id AND partita=partite.id AND giornata='.$g.' ORDER BY partite.id');
    		$i = 0;
    		$turno_prec = -1;
    		while ($row = $result->fetch_assoc())
    		{
    		    if ($row['turno'] != $turno_prec)
    		        echo '<tr><th>'.$row['turno'].'</th></tr>';
    		    if ($i % 2)
    		        echo '<tr>';
    	        else
    	            echo '<tr class="disp">';
                $i++;
                $text_s1 = $html->linksquadra($row['s1'],$row['sq1']);
                $text_s2 = $html->linksquadra($row['s2'],$row['sq2']);
                echo '		<td>'.$text_s1.' - '.$text_s2.'</td>';
                echo '</tr>';
                $turno_prec = $row['turno'];
    		}
    		if ($i == 0) //no partite in programma nel turno successivo
    		  if ($torneo == 'Torneo di Qualificazione')
    		      echo '<tr><td>Nessuna partita in programma</td></tr>';
    		  else 
    		      echo '<tr><td>Sorteggio non ancora effettuato</td></tr>';
    		echo '</table>';
    		$result->free();
        }
		
        if ($g > 1)
        {
            $html->tabletitle("Ultimo turno - Giornata ".($g-1));
    		echo <<< main
    			<table width=100% cellspacing=0>
    				</tr>
    				<tr>
    					<td colspan=2 align=center>
main;
    		if ($giornata_ultima)
    		{
    		    setlocale(LC_TIME, "it_IT");
    		    echo utf8_encode(strftime("%A %d %B %Y, ore %H:%M",strtotime($giornata_ultima)));
    		}
    		echo <<< main
    					</td>
    				</tr>
    				<tr>
    					<td colspan=2 align=center>
main;
    		if ($torneo_ultima)
    		    echo utf8_encode($torneo_ultima);
    		    else
    		        echo '&nbsp;';
    		        /*				if (!$user->guest && $turno)
    		         echo ' - '.utf8_encode($turno);*/
    		        echo <<< main
    					</td>
    				</tr>
    			</table>
                <table width=100% cellspacing=0 class="table">
main;
		
    		$result = $db->query('SELECT DISTINCT partite.id,s1,s2,u1.squadra AS sq1,u2.squadra AS sq2,gol1,gol2,punti1,punti2,turno FROM formazioni,partite,utenti AS u1,utenti AS u2 WHERE s1=u1.id AND s2=u2.id AND partita=partite.id AND giornata='.($g-1).' ORDER BY partite.id');
    		$i = 0;
    		$x = 0;
    		$turno_prec = -1;
    		while ($row = $result->fetch_assoc())
    		{
    		    if ($row['turno'] != $turno_prec)
    		    {
    		        $x++;
    		        echo '<tr><th colspan=2>'.$row['turno'].'</th></tr>';
    		    }
    		    if ($i % 2)
    		        echo '<tr>';
    	        else
    	            echo '<tr class="disp">';
                $i++;
                $text_s1 = $html->linksquadra($row['s1'],$row['sq1']);
                $text_s2 = $html->linksquadra($row['s2'],$row['sq2']);
                echo '		<td>'.$text_s1.' - '.$text_s2.'</td>';
                echo '	<td>'.$row['gol1'].' - '.$row['gol2'].' ('.$html->linkpunteggio($row['s1'],$g-1,$row['punti1']).' - '.$html->linkpunteggio($row['s2'],$g-1,$row['punti2']).')</td>';
                echo '</tr>';
                $turno_prec = $row['turno'];
    		}
    		$result->free();
    		if ($i == 0) //no partite in programma nel turno precedente
    		{
    		    $result = $db->query('SELECT utenti.id AS id,utenti.squadra AS squadra,punti,formazioni.ordine AS ordine FROM formazioni,utenti WHERE formazioni.squadra=utenti.id AND giornata='.($g-1).' AND formazioni.ordine>0 ORDER BY formazioni.ordine');
    		    $i = 0;
    		    while ($row = $result->fetch_assoc())
    		    {
    		        if($i % 2)
    		            echo '<tr>';
    		        else
    		            echo '<tr class="disp">';
    		        echo '		<td>'.$row['ordine'].'</td>
        				<td>'.$html->linksquadra($row['id'],$row['squadra']).'</td>
        				<td>'.$html->linkpunteggio($row['id'],$g-1,$row['punti']).'</td>
        				';
	                echo '</tr>';
	                $i++;
    		    }
    		    $result->free();
    		}
	   }
	   echo <<< main
		  </table>
        </div>
		<div id="centertable">
			<table id="maintable">
				<tr>
					<th colspan=8 align=center>GIORNATA 
main;

			if ($g_selezionata > 1)
			    echo '<a href="index.php?g='.($g_selezionata-1).'"><</a> ';
			echo $g_selezionata;
			if ($g_selezionata < 38)
			    echo ' <a href="index.php?g='.($g_selezionata+1).'">></a>';
					
            echo <<< main
                    </th>
				</tr>
				<tr>
					<td colspan=8 align=center>
main;
					if ($giornata_sel)
					{
						setlocale(LC_TIME, "it_IT");
						echo utf8_encode(strftime("%A %d %B %Y, %H:%M",strtotime($giornata_sel)));
					}
					echo <<< main
					</td>
				</tr>
				<tr>
					<td colspan=8 align=center>
main;
					if ($torneo_sel)
						echo utf8_encode($torneo_sel);
					else 
						echo '&nbsp;';
					if (!$user->guest && $turno_sel)
						echo ' - '.utf8_encode($turno_sel);
					echo <<< main
					</td>
				</tr>
main;
		if (!$user->guest)
		{
			$result = $db->query('SELECT formazioni.id AS formazione,stadio,s1,s2 FROM formazioni LEFT JOIN partite ON (partita=partite.id) WHERE squadra='.$user->id.' AND giornata='.$g_selezionata);
			$row = $result->fetch_assoc();
			if (!$row['s1'])
			{
				$id1 = $user->id;
				$squadra1 = $user->squadra;
				$stemma1 = $user->stemma;
				$allenatore1 = $user->allenatore;
			}
			else 
			{
				$result1 = $db->query('SELECT id,squadra,stemma,allenatore FROM utenti WHERE id='.$row['s1']);
				$row1 = $result1->fetch_assoc();
				$id1 = $row1['id'];
				$squadra1 = $row1['squadra'];
				$stemma1 = $row1['stemma'];
				$allenatore1 = $row1['allenatore'];
				$result1->free();
				$result1 = $db->query('SELECT id,squadra,stemma,allenatore FROM utenti WHERE id='.$row['s2']);
				$row1 = $result1->fetch_assoc();
				$id2 = $row1['id'];
				$squadra2 = $row1['squadra'];
				$stemma2 = $row1['stemma'];
				$allenatore2 = $row1['allenatore'];
				$result1->free();
			}
			
			$result1 = $db->query('SELECT data FROM formazioni WHERE giornata='.$g.' AND squadra='.(!$row['s1']?$user->id:$row['s1']));
			$row1 = $result1->fetch_assoc();
			$agg1 = $row1['data'];
			$result1->free();
			if ($row['s1'])
			{
				$result1 = $db->query('SELECT data FROM formazioni WHERE giornata='.$g.' AND squadra='.$row['s2']);
				$row1 = $result1->fetch_assoc();
				$agg2 = $row1['data'];
				$result1->free();
			}
			echo '<tr><td colspan=8 align=center>'.$row['stadio'].'</td></tr>';
			echo '<tr>	<td align=left class="c5"><div class="img_squadra_container_100"><img class="img_squadra" src="'.$stemma1.'" /></div></td>
						<td align=right colspan=2><b>'.$html->linksquadra($id1, $squadra1).'</b></td>
						<td align=center colspan=2><div class="img_squadra_container_60"><img class="img_squadra" src="'.$torneo_logo.'" /></div></td>
						<td align=left colspan=2><b>'.(!$row['s1']?'':$html->linksquadra($id2, $squadra2)).'</b></td>
						<td align=right class="c5">'.(!$row['s1']?'':'<div class="img_squadra_container_100"><img class="img_squadra" src="'.$stemma2.'" /></div>').'</td>
				</tr>';
					
			if (!$row['s1']) //nessuna partita => giornata libera
			{
				if ($row['formazione'])
				{
					$result1 = $db->query('SELECT giocatori.id AS id,cognome,numero,ruolo,datiultimagiornata.squadra FROM giocatori,giocatoriformazioni,giocgiorsq,datiultimagiornata WHERE giocatori.id=giocatoriformazioni.giocatore AND giocatori.id=giocgiorsq.giocatore AND giocgiorsq.giornata='.$g.' AND giocgiorsq.squadra='.$user->id.' AND giocatori.id=datiultimagiornata.giocatore AND formazione=(SELECT id FROM formazioni WHERE giornata='.$g.' AND squadra='.$user->id.') ORDER BY giocatoriformazioni.pos');
					while ($row1 = $result1->fetch_assoc())
						$F1[] = $row1;
					$result1->free();
				}
			}
			else 
			{
				$result1 = $db->query('SELECT giocatori.id AS id,cognome,numero,ruolo,datiultimagiornata.squadra FROM giocatori,giocatoriformazioni,giocgiorsq,datiultimagiornata WHERE giocatori.id=giocatoriformazioni.giocatore AND giocatori.id=giocgiorsq.giocatore AND giocgiorsq.giornata='.$g.' AND giocgiorsq.squadra='.$row['s1'].' AND giocatori.id=datiultimagiornata.giocatore AND formazione=(SELECT id FROM formazioni WHERE giornata='.$g.' AND squadra='.$row['s1'].') ORDER BY giocatoriformazioni.pos');
				while ($row1 = $result1->fetch_assoc())
					$F1[] = $row1;
				$result1->free();
				$result1 = $db->query('SELECT giocatori.id AS id,cognome,numero,ruolo,datiultimagiornata.squadra FROM giocatori,giocatoriformazioni,giocgiorsq,datiultimagiornata WHERE giocatori.id=giocatoriformazioni.giocatore AND giocatori.id=giocgiorsq.giocatore AND giocgiorsq.giornata='.$g.' AND giocgiorsq.squadra='.$row['s2'].' AND giocatori.id=datiultimagiornata.giocatore AND formazione=(SELECT id FROM formazioni WHERE giornata='.$g.' AND squadra='.$row['s2'].') ORDER BY giocatoriformazioni.pos');
				while ($row1 = $result1->fetch_assoc())
					$F2[] = $row1;
				$result1->free();
			}
			echo '<tr><th colspan=8>Titolari</th></tr>';
			for ($i = 0; $i < 18; $i++)
			{
				if ($i == 11)
					echo '<tr><th colspan=8>Panchina</th></tr>';
				echo '<tr>	<td class="c5"></td>
							<td class="c30" align=right>'.$html->linkgiocatore($F1[$i]['id'],$F1[$i]['cognome']).'</td>
							<td class="c5" align=center><div class="img_squadra_container_small"><img class="img_squadra" src="images/stemmia/'.strtolower($F1[$i]['squadra']).'.png" /></div></td>
							<td class="c5 '.$Ruoli[$F1[$i]['ruolo']].'" align=center>'.$F1[$i]['numero'].'</td>';
				if ($row['s1'])			
					echo '	<td class="c5 '.$Ruoli[$F2[$i]['ruolo']].'" align=center>'.$F2[$i]['numero'].'</td>
							<td class="c5" align=center><div class="img_squadra_container_small"><img class="img_squadra" src="images/stemmia/'.strtolower($F2[$i]['squadra']).'.png" /></div></td>
							<td class="c30" align=left>'.$html->linkgiocatore($F2[$i]['id'],$F2[$i]['cognome']).'</td>
							<td class="c5"></td></tr>';
				else 
					echo '	<td class="c5"></td><td class="c5"></td><td class="c30"></td><td class="c5"></td></tr>';
			}
			echo '<tr><td colspan=2 align=right>'.$allenatore1.'</td><td colspan=4 align=center><i>Allenatore</i></td><td colspan=2 align=left>'.(!$row['s1']?'':$allenatore2).'</td></tr>';
			echo '<tr><td colspan=2 align=right>'.date('H:i d/m/Y',strtotime($agg1)).'</td><td colspan=4 align=center><i>Inviata</i></td><td colspan=2 align=left>'.(!$row['s1']?'':date('H:i d/m/Y',strtotime($agg2))).'</td></tr>';

			$result->free();
		}
			echo <<< main
			</table>
		</div>
		<div id="righttable">
main;
	switch ($_GET['filtro'])
	{
		case 'personali':
			$newstitle = 'News personali';
			$query = ' AND mittente <> 0';
			break;
		case 'ufficiali':
			$newstitle = 'News ufficiali';
			$query = ' AND mittente = 0';
			break;
		default:
			$newstitle = 'News'; 
			$query = '';
	}
	$html->tabletitle($newstitle);
	echo <<<main
			<a href="index.php">Tutte</a> - <a href="index.php?filtro=personali">Solo personali</a> - <a href="index.php?filtro=ufficiali">Solo ufficiali</a>
			<ul class="news">
				<form action="index.php" method="post">
main;
	if (!$user->guest)
	{
		echo '<li>Rilascia un comunicato per <select name=squadra><option value=0 selected>Tutti</option>';
		$result = $db->query('SELECT id,squadra FROM utenti');
		while ($row = $result->fetch_assoc())
			echo '<option value="'.$row['id'].'">'.$row['squadra'].'</option>';
		$result->free();
		echo '</select>&nbsp;';
		if ($user->admin)
			$html->input('adminchk',0,0,'admin','checkbox');
		$html->submit('Invia','submitnews');
		$html->textarea('text',2,30);
		echo '</li>';
	}
	echo '</form>';
	$query = 'SELECT id,timestamp,mittente,destinatario,testo FROM news WHERE (destinatario=0 OR destinatario='.$user->id.($user->guest?'':' OR mittente='.$user->id).')'.$query. ' ORDER BY timestamp DESC';
	$result = $db->query($query);
	$i = 1;
	while ($row = $result->fetch_assoc())
	{
		$row['timestamp'] = date('d/m H:i',strtotime($row['timestamp']));
		if (!$row['mittente'])
			$mittente = 'Fantalegamento';
		else if ($row['mittente'] == $user->id && $row['destinatario'])
			$mittente = $html->linksquadra($user->id,$user->squadra).' per '.$html->linksquadra($row['destinatario'],getSquadraNome($row['destinatario']));
		else
			$mittente = $html->linksquadra($row['mittente'],getSquadraNome($row['mittente']));
		$result1 = $db->query('SELECT data,autore,testo FROM commenti WHERE news='.$row['id'].' ORDER BY data');
		if ($db->affectedRows() == 1)
			$commenti = '1 commento';
		else 
			$commenti = ''.$db->affectedRows().' commenti';
		$commenti = '<a href=# onclick=changeVisibility("commenti_'.$row['id'].'")>'.$commenti.'</a>';
		if ($i % 2)
			$class = ' class="disp"';
		else 	
			$class = '';
		echo '<li'.$class.'>
				<b>'.$row['timestamp'].' - '.$mittente.'</b> - <small>'.$commenti.'</small><br><i>"'.nl2br($row['testo']).'"</i>
				<ul class="comments" id="commenti_'.$row['id'].'">
					<form action="index.php" method="post">';
		$j = $i+1;
		while ($row1 = $result1->fetch_assoc())
		{
			$row1['data'] = date('d/m H:i',strtotime($row1['data']));
			if (!$row1['autore'])
				$mittente = 'Fantalegamento';
			else 
				$mittente = $html->linksquadra($row1['autore'],getSquadraNome($row1['autore']));
			if ($j % 2)
				$class = ' class="disp"';
			else 	
				$class = '';
			echo '<li'.$class.'>
					<b>'.$row1['data'].' - '.$mittente.'</b>: <i>"'.nl2br($row1['testo']).'"</i>
				  </li>';
			$j++;
		}
		$result1->free();
		if (!$user->guest)
		{
			echo '<li>';
			$html->textarea('text',1,25);
			$html->input('news',10,10,$row['id'],'hidden');
			if ($user->admin)
				$html->input('adminchk',0,0,'admin','checkbox');
			$html->submit('Invia','submitcomment');
			echo '</li>';
		}
		echo '		</form>
				</ul>
			  </li>';
		$i++;
	}
	$result->free();
	
$db->close();
$html->close();
?>