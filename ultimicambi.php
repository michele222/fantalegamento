<?php
include_once 'include/main.php';

$db = new db();
$g = getGiornata();

if ($_GET['id'] && ctype_digit($_GET['id']) && $_GET['id'] > 0 && $_GET['id'] <= $g)
	$giornata = $_GET['id'];
else if ($_POST['giornata'])
	$giornata = $_POST['giornata'];
else
	$giornata = $g-1;

$html = new html("ULTIMI CAMBI");
$html->title("Ultimi cambi",_FLLOGO);

echo <<< main
		<p>
		<form action="ultimicambi.php" method="post">
			<label for giornata>Visualizza cambi da giornata:</label>&nbsp;<select id=giornata name=giornata>
main;
		for($i = 1; $i <= $g; $i++)
		{
			echo '<option value="'.$i.'"';
			if ($i == $giornata)
				echo ' selected';
			echo '>'.$i.'</option>';
		}
		echo '</select>';
		$html->submit('OK');
	echo '</p></form>';
$html->tabletitle('Elenco cambi');
echo <<< main
	<table class="table">
		<tr>
			<th>giornata</th>
			<th>squadra</th>
			<th>ruolo</th>
			<th>num</th>
			<th>ceduto</th>
			<th>squadra</th>
			<th>prezzo</th>
			<th>num</th>
			<th>acquistato</th>
			<th>squadra</th>
			<th>prezzo</th>
		<tr>
main;
if (time() >= _MERCATOINIZ_CHIUSO || $user->admin)
{
	$Ruoli = array ('POR', 'DIF', 'CEN', 'ATT');
	$result = $db->query('SELECT b.giornata, b.squadra, a.numero AS num_a, b.numero AS num_b, a.giocatore AS gioc_a, b.giocatore AS gioc_b FROM giocgiorsq AS a,giocgiorsq AS b WHERE a.giornata=b.giornata-1 AND a.pos=b.pos AND a.squadra=b.squadra AND a.giocatore<>b.giocatore AND b.giornata>='.$db->escape($giornata).' ORDER BY b.giornata DESC');
	$i = 0;
	while ($row = $result->fetch_assoc())
	{
		$result1 = $db->query('SELECT cognome, nome, ruolo FROM giocatori WHERE id='.$db->escape($row['gioc_a']));
		$gioc1 = $result1->fetch_assoc();
		$result1->free();
		$result1 = $db->query('SELECT cognome, nome FROM giocatori WHERE id='.$db->escape($row['gioc_b']));
		$gioc2 = $result1->fetch_assoc();
		$result1->free();
		$result1 = $db->query('SELECT squadra, prezzo FROM giocatorigiornate WHERE giocatore='.$db->escape($row['gioc_a']).' AND giornata='.$db->escape($row['giornata']-1));
		$gioc1 = array_merge($gioc1, $result1->fetch_assoc());
		$result1->free();
		$result1 = $db->query('SELECT squadra, prezzo FROM giocatorigiornate WHERE giocatore='.$db->escape($row['gioc_b']).' AND giornata='.$db->escape($row['giornata']-1));
		$gioc2 = array_merge($gioc2, $result1->fetch_assoc());
		$result1->free();
		if($i % 2)
			echo '<tr>';
		else 
			echo '<tr class="disp">';
		echo '		<td>'.$row['giornata'].'</td>
					<td><b>'.$html->linksquadra($row['squadra'],getSquadraNome($row['squadra'])).'</b></td>
					<td>'.$Ruoli[$gioc1['ruolo']].'</td>
					<td>'.$row['num_a'].'</td>
					<td>'.$html->linkgiocatore($row['gioc_a'],$gioc1['nome'].' <b>'.$gioc1['cognome']).'</b></td>
					<td>'.$gioc1['squadra'].'</td>
					<td>'.$gioc1['prezzo'].'</td>
					<td>'.$row['num_b'].'</td>
					<td>'.$html->linkgiocatore($row['gioc_b'],$gioc2['nome'].' <b>'.$gioc2['cognome']).'</b></td>
					<td>'.$gioc2['squadra'].'</td>
					<td>'.$gioc2['prezzo'].'</td>
					';
		echo '</tr>';
		$i++;
	}
	$result->free();
}
else 
	echo '<tr>
			<td colspan=11>Le rose sono nascoste fino al termine del mercato iniziale</td>
		  </tr>';
echo '</table>';

$db->close();
$html->close();
?>