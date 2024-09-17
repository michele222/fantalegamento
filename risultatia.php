<?php
include_once 'include/main.php';

$db = new db();
$html = new html("RISULTATI SERIE A");

$html->title('Risultati',_SERIEALOGO);
$html->tabletitle('Giornata '.getGiornata());

echo <<< main
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-202.png" />
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-206.1.png" />
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-206.2.png" />
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-206.3.png" />
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-206.4.png" />
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-206.5.png" />
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-206.6.png" />
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-206.7.png" />
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-206.8.png" />
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-206.9.png" />
		<img src="http://www.televideo.rai.it/televideo/pub/tt4web/Nazionale/page-206.10.png" /><br>
main;

$db->close();
$html->close();
?>