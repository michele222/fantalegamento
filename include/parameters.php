<?php

date_default_timezone_set('Europe/Amsterdam');

define("_STAGIONE", "2020/21");
define("_MERCATOINIZ_CHIUSO", strtotime("2020-09-19 00:00:00")); //nasconde rose e formazioni altrui fino a questa data
define("_G_MERCATO_PARZIALE_1", 0); //I giocatori che hanno cambiato squadra dalla giornata 0...
define("_G_MERCATO_PARZIALE_2", 3); //...alla giornata 3, possono essere cambiati alla giornata 3

define("_LOG_GIORNATA", "update_giornata_log.txt");

define("_DEFAULTLOGO", "images/lnd.png");
define("_FLLOGO", "images/logo_fantalegamento.png");
define("_SERIEALOGO", "images/logoseriea.jpg");
define("_WIP", "images/wip.png");

define("_TQLOGO", "images/torneoqualificazione.gif");
define("_TALOGO", "images/torneoandata.gif");
define("_TRLOGO", "images/torneoritorno.gif");
define("_FCLOGO", "images/fantalegamentocup.gif");
define("_TFLOGO", "images/torneofinale.jpg");
define("_SCLOGO", "images/logo_fantalegamento.png");

define("_IMGPROM", "images/promosso.gif");
define("_IMGRETR", "images/retrocesso.gif");

define("_IMGGOL", "images/gol.gif");
define("_IMGGOLV", "images/gv.gif");
define("_IMGGOLP", "images/gp.gif");
define("_IMGRIGP", "images/rigparato.gif");
define("_IMGRIGS", "images/rigsbagliato.gif");
define("_IMGAMM", "images/giallo.gif");
define("_IMGESP", "images/rosso.gif");
define("_IMGAUTO", "images/autogol.gif");
define("_IMGGOLS", "images/subito.gif");
define("_IMGASS", "images/assist.gif");
define("_IMG6POL", "images/6pol.gif");

?>