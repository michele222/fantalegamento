<div class="menu">
	<ul>
		<li><a href="index.php" onmouseover="menuDisplay('Blank')">Home</a>
		<?php 
		if (!$user->guest)
		echo
		'</li><li><a href="#" onmouseover="menuDisplay(\'_Squadra_\')">'.htmlspecialchars($user->squadra).'</a>';
		?>
		</li><li><a href="#" onmouseover="menuDisplay('Fantalegamento')">Fantalegamento</a>
		</li><li><a href="#" onmouseover="menuDisplay('Tornei')">Tornei</a></li>
		<?php 
		if (!$user->guest)
		echo
		'<li><a href="#" onmouseover="menuDisplay(\'Mercato\')">Mercato</a></li>';
		?>
		<li><a href="#" onmouseover="menuDisplay('SerieA')">Serie A</a></li>
		<li><a href="docs/regolamento.pdf" onmouseover="menuDisplay('Blank')">Regolamento</a></li>
		<?php 
		if ($user->admin)
		echo
		'<li><a href="admin.php" onmouseover="menuDisplay(\'Blank\')">Admin</a></li>';
		?>
		<li><a href="index.php?action=logout" onmouseover="menuDisplay('Blank')">Logout</a></li>
	</ul>
</div>
<div class="blankrow" id="Blank">
	&nbsp;
</div>
<div class="submenu" id="_Squadra_">
	<ul>
		<li><a href="squadra.php">Rosa</a></li>
		<li><a href="formazione.php">Formazione</a></li>
		<li><a href="punteggio.php">Punteggi</a></li>
		<li><a href="calendario.php">Calendario</a></li>
		<!---<li><a href="bacheca.php">Bacheca</a></li>--->
		<li><a href="parametri.php">Parametri</a></li>
	</ul>
</div>
<div class="submenu" id="Fantalegamento">
	<ul>
		<li><a href="squadre.php">Squadre</a></li>
		<li><a href="giornata.php">Ultimo turno</a></li>
		<li><a href="classifiche.php">Classifiche</a></li>
		<li><a href="albodoro.php">Albo d'oro</a></li>
		<!---<li><a href="records.php">Records</a></li>--->
	</ul>
</div>
<div class="submenu" id="Tornei">
	<ul>
		<li><a href="torneoqualificazione.php">Torneo di Qual.</a></li>
		<li><a href="torneoandata.php">Torneo d'Andata</a></li>
		<li><a href="torneoritorno.php">Torneo di Ritorno</a></li>
		<li><a href="fantalegamentocup.php">Fantalegamento Cup</a></li>
		<li><a href="torneofinale.php">Torneo Finale</a></li>
		<li><a href="supercoppa.php">Supercoppa</a></li>
	</ul>
</div>
<div class="submenu" id="Mercato">
	<ul>
		<li><a href="cambia.php">Cambia giocatori</a></li>
		<li><a href="ultimicambi.php">Ultimi cambi</a></li>
		<li><a href="lista.php">Lista giocatori</a></li>
		<li><a href="rosainiziale.php">Rosa iniziale</a></li>
	</ul>
</div>
<div class="submenu" id="SerieA">
	<ul>
		<li><a href="risultatia.php">Risultati</a></li>
		<li><a href="classificaa.php">Classifica</a></li>
		<li><a href="calendarioa.php">Calendario</a></li>
		<li><a href="movimenti.php">Movimenti</a></li>
	</ul>
</div>