<?php
include_once 'include/main.php';

$db = new db();
$html = new html("CLASSIFICA SERIE A");

$html->title('Classifica',_SERIEALOGO);
$html->tabletitle('Giornata '.getGiornata());

echo <<< main
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-203.png" />
main;

$db->close();
$html->close();
?>