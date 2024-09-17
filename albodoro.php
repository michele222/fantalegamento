<?php
include_once 'include/main.php';

$html = new html("NON ATTIVA");
$html->title("Pagina non attiva",_WIP);

echo <<< main
Questa pagina non &egrave; inclusa nella versione base del nuovo sito.<br>Sar&agrave; attivata durante le prime giornate di campionato
main;

$html->close();
?>