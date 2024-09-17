<?php
include_once 'class/user.class.php';
include_once 'class/html.class.php';
include_once 'class/db.class.php';
include_once 'include/functions.php';

$db = new db();
$html = new html();

if (isset($_GET['attiva']) && isset($_GET['id'])) //attivazione squadra
{
	$result = $db->query("UPDATE utenti SET attivo=1 WHERE id = '".$db->escape($_GET['id'])."'");
	//processo di login
	header('Location: login.php?attiva=1');
}
if (isset($_POST['submit']) && !isset($_POST['rinnova'])) //l'utente ha immesso i dati
{
	if (isset($_POST['rinnovaok']) && $_POST['rinnovaok'] == 1) //l'utente ha rinnovato i dati
	{
		$error = 'Rinnovo ok';//
		$rinnovaok = 1;
	}
	else //l'utente ha immesso i dati per nuova iscrizione
	{
		$error = 'Nuova iscrizione ok';//
		$rinnovaok = 0;
	}
	
	$username = $_POST['username'];
	$pwd = $_POST['pwd'];
	$pwd2 = $_POST['pwd2'];
	$nome = ucwords($_POST['nome']);
    $cognome = ucwords($_POST['cognome']);
    $nazionalita = $_POST['nazione'];
    $email = $_POST['email'];
    $email2 = $_POST['email2'];
    $squadra = ucwords($_POST['squadra']);
    $stadio = ucwords($_POST['stadio']);
    $allenatore = ucwords($_POST['allenatore']);
    $stemma = _DEFAULTLOGO;
    $ordine = $_POST['ordine'];
    $tifosi = $_POST['tifosi'];
    
    $checkok = 1;
    //validazione campi
	if (strlen($username) < 3)
	{
		$error = "Nickname troppo corto";
		$checkok = 0;
	}	
	
	if (!ctype_alnum($username))
	{
		$error = "Nickname pu&oacute; contenere solo caratteri alfanumerici";
		$checkok = 0;
	}			
	
	$result = $db->query("SELECT nick FROM utenti WHERE nick = '".$db->escape($username)."'");
	if ($result->num_rows > 0)
	{
		$error = "Nickname esistente";
		$checkok = 0;
	}
	$result->free();
	
	if (strlen($squadra) < 3)
	{
		$error = "Nome squadra troppo corto";
		$checkok = 0;
	}
	
	/*if (!preg_match('/^[a-zA-Z0-9.\'\ ]{0,20}$/i', $squadra)) 
	{
		$error = "Nome squadra pu&oacute; contenere solo caratteri alfanumerici, punti, spazi o apostrofi";
		$checkok = 0;
	}*/
	
	$result = $db->query("SELECT squadra FROM utenti WHERE squadra = '".$db->escape($squadra)."'");
	if ($result->num_rows > 0)
	{
		$error = "Nome squadra esistente";
		$checkok = 0;
	}
	$result->free();
	
	if (strlen($pwd) < 3)
	{
		$error = "Password troppo corta";
		$checkok = 0;
	}		
	
	if ( $pwd != $pwd2 )
	{
		$error = "Le password non corrispondono";
		$checkok = 0;
	}	
	
	if ( $email != $email2 )
	{
		$error = "Gli indirizzi email non corrispondono";
		$checkok = 0;
	}
	
	if (strlen($nome) < 1)
	{
		$error = "Il campo nome &egrave; obbligatorio";
		$checkok = 0;
	}
	
	if (strlen($cognome) < 1)
	{
		$error = "Il campo cognome &egrave; obbligatorio";
		$checkok = 0;
	}
	
	if (strlen($email) < 1)
	{
		$error = "Il campo e-mail &egrave; obbligatorio";
		$checkok = 0;
	}
	
	if (strlen($stadio) < 1)
	{
		$error = "Il campo stadio &egrave; obbligatorio";
		$checkok = 0;
	}
	
	if (strlen($allenatore) < 1)
	{
		$error = "Il campo allenatore &egrave; obbligatorio";
		$checkok = 0;
	}
	
    if ($checkok && $_FILES['stemma']['error'] == UPLOAD_ERR_OK) //upload stemma ok (solo se il resto è ok)
    {
    	$ext = strtolower(pathinfo($_FILES['stemma']['name'],PATHINFO_EXTENSION));
    	switch ($ext)
    	{
    		case 'jpg': case 'jpeg': case 'gif': case 'png': case 'bmp':
    			break; //tipo file ok
    		default:
    			$error = "Tipo di file non corretto";
				$checkok = 0;
    	}
    	$destfile = 'images/stemmi/'.$username.'.'.$ext;
    	if ($moveok = move_uploaded_file($_FILES['stemma']['tmp_name'], $destfile))
    		$stemma = $destfile;
    	else
    	{
    		$error = "Errore nel caricamento dello stemma";
    		$checkok = 0;
    	}
    }
    else
    	switch ($_FILES['stemma']['error'])
    	{	//gestione errori: pag.489
    		case 1:
    		case 2:
		    	$error = "Lo stemma caricato &egrave; troppo grande";
		    	$checkok = 0;
		    	break;
    		case 3:
    			$error = "Errore nel caricamento dello stemma: riprovare";
		    	$checkok = 0;
		    	break;
    	}
	
	if ($checkok) //procedo alla registrazione
	{
		$query = "INSERT INTO utenti (";
		if ($rinnovaok) // se rinnova l'iscrizione, recupero l'id dall'ordine dell'anno precedente
			$query .= "id,";
		else	//se è un nuovo utente, i tifosi sono 0
			$tifosi = 0;
		$query .= "nick,nome,cognome,pwd,nazionalita,email,stadio,allenatore,tifosi,squadra,stemma,ordine)
		VALUES (";
		if ($rinnovaok)
			$query .= "'".$db->escape($ordine)."',"; //id=ordine
		$query .= "'".$db->escape($username)."',
		'".$db->escape($nome)."',
		'".$db->escape($cognome)."',
		'".$db->escape(sha1($pwd))."',
		'".$db->escape($nazionalita)."',
		'".$db->escape($email)."',
		'".$db->escape($stadio)."',
		'".$db->escape($allenatore)."',
		'".$db->escape($tifosi)."',
		'".$db->escape($squadra)."',
		'".$db->escape($stemma)."',
		'".( $rinnovaok? $db->escape($ordine) : 30)."')";
		if ($db->query($query))
		{
			$id = $db->lastId();
			if ($rinnovaok) //cancello record in tabella exutenti
				$db->query("DELETE FROM exutenti WHERE nick='".$db->escape($username)."'");
			else //nuovo utente
				$db->query("UPDATE utenti SET ordine=id WHERE nick='".$db->escape($username)."'");
				
			for ($i = 1; $i <= 38; $i++)
				$db->query("INSERT INTO formazioni (squadra,giornata) VALUES (".$id.",".$i.")");
			
			sendActivationEmail($id);
			//processo di login
			header('Location: login.php?signup='.$username);
			
		}
		else
			$error = 'Errore nell\'iscrizione: contattare l\'amministratore via e-mail a info@fantalegamento.it';
	}
    
}
else if(isset($_POST['rinnova'])) //l'utente ha immesso usr e pwd per il rinnovo
{
	$result = $db->query("SELECT * FROM exutenti WHERE nick='".$db->escape($_POST['username'])."' AND pwd='".$db->escape(sha1($_POST['pwd']))."'");
	if (!$result->num_rows)
	{
		$error = 'Nickname o password errati';
		$renew_error = 1;
	}
	else
	{
		$row = $result->fetch_assoc();

		$username = $row['nick'];
       	$nome = $row['nome'];
       	$cognome = $row['cognome'];
       	$nazionalita = $row['nazionalita'];
       	$email = $row['email'];
       	$squadra = $row['squadra'];
       	$stadio = $row['stadio'];
       	$allenatore = $row['allenatore'];
       	//$stemma = $row['stemma'];
       	$ordine = $row['ordine'];
       	$tifosi = $row['tifosi'];
       	$rinnovaok = 1;
        	
       	$result->free();
	}
}

$html->head('REGISTRAZIONE');
$html->bodyon();
$html->container();
$html->header();
$html->main();
echo '<div align=center>';
$html->title('Registrazione','images/logo_fantalegamento.png');
//print_r($_POST);

	if (isset($error))
		$html->error($error);
	echo <<< main
		<form action="signup.php" method="post" enctype="multipart/form-data">
		<input type=hidden name="rinnovaok" value=$rinnovaok>
		<input type=hidden name="ordine" value=$ordine>
		<input type=hidden name="tifosi" value=$tifosi>
			<table width=100% border='0' align=left cellpadding='5' cellspacing='2'>
				<tr>
					<td width=50% align=right>
						<label for="username">Nickname</label>
					</td>
					<td align=left>
main;
					$html->input("username",15,10,$username);
					echo <<< main
					</td>
				</tr>
				<tr>
					<td width=50% align=right>
						<label for="pwd">Password</label>
					</td>
					<td align=left>
main;
					$html->input("pwd",15,10,'',"password");
					echo <<< main
					</td>
				</tr>
main;
				if(isset($_GET['renew']) || $renew_error)
				{
					echo <<< main
					<tr>
					<td colspan=2 align=center>
main;
					$html->submit("Rinnova");
					echo <<< main
					<input type=hidden name=rinnova value=1>
					</td>
				</tr>
main;
				}
				else
				{
				echo <<< main
				<tr>
					<td width=50% align=right>
						<label for="pwd2">Riscrivi password</label>
					</td>
					<td align=left>
main;
					$html->input("pwd2",15,10,'',"password");
					echo <<< main
					</td>
				</tr>
				<tr>
					<td width=50% align=right>
						<label for="email">E-mail</label>
					</td>
					<td align=left>
main;
					$html->input("email",60,50,$email);
					echo <<< main
					</td>
				</tr>
				<tr>
					<td width=50% align=right>
						<label for="email">Riscrivi e-mail</label>
					</td>
					<td align=left>
main;
					$html->input("email2",60,50,$email2);
					echo <<< main
					</td>
				</tr>
				<tr>
					<td width=50% align=right>
						<label for="nome">Nome</label>
					</td>
					<td align=left>
main;
					$html->input("nome",25,20,$nome);
					echo <<< main
					</td>
				</tr>
				<tr>
					<td width=50% align=right>
						<label for="cognome">Cognome</label>
					</td>
					<td align=left>
main;
					$html->input("cognome",25,20,$cognome);
					echo <<< main
					</td>
				</tr>
				
				<tr>
					<td width=50% align=right>
						<label for="squadra">Nome squadra</label>
					</td>
					<td align=left>
main;
					$html->input("squadra",25,20,$squadra);
					echo <<< main
					</td>
				</tr>
				<tr>
					<td width=50% align=right>
						<label for="stadio">Stadio</label>
					</td>
					<td align=left>
main;
					$html->input("stadio",35,30,$stadio);
					echo <<< main
					</td>
				</tr>
				<tr>
					<td width=50% align=right>
						<label for="allenatore">Allenatore</label>
					</td>
					<td align=left>
main;
					$html->input("allenatore",35,30,$allenatore);
					echo <<< main
					</td>
				</tr>
				<tr>
					<td width=50% align=right>
						<label for="nazione">Nazione</label>
					</td>
					<td align=left>
						<select name=nazione>
           					  <option value=AF>Afganistan</option>
							  <option value=AL>Albania</option>
							  <option value=DZ>Algeria</option>
							  <option value=AD>Andorra</option>
							  <option value=AO>Angola</option>
							  <option value=AN>Antille Olandesi</option>
							  <option value=SA>Arabia Saudita</option>
							  <option value=AR>Argentina</option>
							  <option value=AM>Armenia</option>
							  <option value=AW>Aruba</option>
							  <option value=AU>Australia</option>
							  <option value=AT>Austria</option>
							  <option value=AZ>Azerbaigian</option>
							  <option value=BS>Bahama</option>
							  <option value=BH>Bahrain</option>
							  <option value=BD>Bangladesh</option>
							  <option value=BB>Barbados</option>
							  <option value=BE>Belgio</option>
							  <option value=BZ>Belize</option>
							  <option value=BJ>Benin</option>
							  <option value=BM>Bermude</option>
							  <option value=BT>Bhutan</option>
							  <option value=BY>Bielorussia</option>
							  <option value=BO>Bolivia</option>
							  <option value=BA>Bosnia-Erzegovina</option>
							  <option value=BW>Botswana</option>
							  <option value=BR>Brasile</option>
							  <option value=BN>Brunei</option>
							  <option value=BG>Bulgaria</option>
							  <option value=BF>Burkina Faso</option>
							  <option value=BI>Burundi</option>
							  <option value=KH>Cambogia</option>
							  <option value=CM>Camerun</option>
							  <option value=CA>Canada</option>
							  <option value=CV>Capo Verde</option>
							  <option value=TD>Ciad</option>
							  <option value=CL>Cile</option>
							  <option value=CN>Cina</option>
							  <option value=CY>Cipro</option>
							  <option value=CO>Colombia</option>
							  <option value=KM>Comore</option>
							  <option value=CD>Congo</option>
							  <option value=CK>Cook</option>
							  <option value=KP>Corea del Nord</option>
							  <option value=KR>Corea del Sud</option>
							  <option value=CI>Costa d'Avorio</option>
							  <option value=CR>Costarica</option>
							  <option value=HR>Croazia</option>
							  <option value=CU>Cuba</option>
							  <option value=DK>Danimarca</option>
							  <option value=DM>Dominica</option>
							  <option value=EC>Ecuador</option>
							  <option value=EG>Egitto</option>
							  <option value=SV>El Salvador</option>
							  <option value=AE>Emirati Arabi Uniti</option>
							  <option value=ER>Eritrea</option>
							  <option value=EE>Estonia</option>
							  <option value=ET>Etiopia</option>
							  <option value=FJ>Figi</option>
							  <option value=PH>Filippine</option>
							  <option value=FI>Finlandia</option>
							  <option value=FR>Francia</option>
							  <option value=GA>Gabon</option>
							  <option value=GM>Gambia</option>
							  <option value=GE>Georgia</option>
							  <option value=DE>Germania</option>
							  <option value=GH>Ghana</option>
							  <option value=JM>Giamaica</option>
							  <option value=JP>Giappone</option>
							  <option value=GI>Gibilterra</option>
							  <option value=DJ>Gibuti</option>
							  <option value=JO>Giordania</option>
							  <option value=GB>Gran Bretagna</option>
							  <option value=GR>Grecia</option>
							  <option value=GD>Grenada</option>
							  <option value=GL>Groenlandia</option>
							  <option value=GP>Guadalupa</option>
							  <option value=GT>Guatemala</option>
							  <option value=GY>Guiana</option>
							  <option value=GF>Guiana Francese</option>
							  <option value=GN>Guinea</option>
							  <option value=GQ>Guinea Equatoriale</option>
							  <option value=GW>Guinea-Bissau</option>
							  <option value=HT>Haiti</option>
							  <option value=HN>Honduras</option>
							  <option value=HK>Hong Kong</option>
							  <option value=IN>India</option>
							  <option value=ID>Indonesia</option>
							  <option value=IR>Iran</option>
							  <option value=IQ>Iraq</option>
							  <option value=IE>Irlanda</option>
							  <option value=IS>Islanda</option>
							  <option value=KY>Isole Cayman</option>
							  <option value=FO>Isole Faroe</option>
							  <option value=SB>Isole Salomone</option>
							  <option value=IL>Israele</option>
							  <option selected value=IT>Italia</option>
							  <option value=KZ>Kazakstan</option>
							  <option value=KE>Kenya</option>
							  <option value=KG>Kirghizistan</option>
							  <option value=KI>Kiribati</option>
							  <option value=KW>Kuwait</option>
							  <option value=LA>Laos</option>
							  <option value=LS>Lesotho</option>
							  <option value=LV>Lettonia</option>
							  <option value=LB>Libano</option>
							  <option value=LR>Liberia</option>
							  <option value=LY>Libia</option>
							  <option value=LI>Liechtenstein</option>
							  <option value=LT>Lituania</option>
							  <option value=LU>Lussemburgo</option>
							  <option value=MK>Macedonia</option>
							  <option value=MG>Madagascar</option>
							  <option value=MW>Malawi</option>
							  <option value=MY>Malaysia</option>
							  <option value=MV>Maldive</option>
							  <option value=ML>Mali</option>
							  <option value=MT>Malta</option>
							  <option value=MA>Marocco</option>
							  <option value=MR>Mauritania</option>
							  <option value=MU>Maurizio</option>
							  <option value=MX>Messico</option>
							  <option value=FM>Micronesia</option>
							  <option value=MD>Moldavia</option>
							  <option value=MC>Monaco</option>
							  <option value=MN>Mongolia</option>
							  <option value=ME>Montenegro</option>
							  <option value=MS>Montserrat</option>
							  <option value=MZ>Mozambico</option>
							  <option value=MM>Myanmar</option>
							  <option value=NA>Namibia</option>
							  <option value=NR>Nauru</option>
							  <option value=NP>Nepal</option>
							  <option value=NI>Nicaragua</option>
							  <option value=NE>Niger</option>
							  <option value=NG>Nigeria</option>
							  <option value=NU>Niue</option>
							  <option value=NO>Norvegia</option>
							  <option value=NC>Nuova Caledonia</option>
							  <option value=NZ>Nuova Zelanda</option>
							  <option value=NL>Olanda</option>
							  <option value=OM>Oman</option>
							  <option value=PK>Pakistan</option>
							  <option value=PW>Palau</option>
							  <option value=PA>Panama</option>
							  <option value=PG>Papua Nuova Guinea</option>
							  <option value=PY>Paraguay</option>
							  <option value=PE>Per&ugrave;</option>
							  <option value=PL>Polonia</option>
							  <option value=PT>Portogallo</option>
							  <option value=QA>Qatar</option>
							  <option value=CZ>Repubblica Ceca</option>
							  <option value=CF>Repubblica Centroafricana</option>
							  <option value=DO>Repubblica Dominicana</option>
							  <option value=RE>Riunione</option>
							  <option value=RO>Romania</option>
							  <option value=RU>Russia</option>
							  <option value=RW>Rwanda</option>
							  <option value=SH>S.Elena</option>
							  <option value=KN>S.Kitts e Nevis</option>
							  <option value=LC>S.Lucia</option>
							  <option value=SM>S.Marino</option>
							  <option value=ST>S.Tom&eacute; e Principe</option>
							  <option value=VC>S.Vincenzo e Grenadine</option>
							  <option value=WS>Samoa Occidentale</option>
							  <option value=SC>Seicelle</option>
							  <option value=SN>Senegal</option>
							  <option value=RS>Serbia</option>
							  <option value=SL>Sierra Leone</option>
							  <option value=SG>Singapore</option>
							  <option value=SY>Siria</option>
							  <option value=SK>Slovacchia</option>
							  <option value=SI>Slovenia</option>
							  <option value=SO>Somalia</option>
							  <option value=ES>Spagna</option>
							  <option value=LK>Sri Lanka</option>
							  <option value=US>Stati Uniti d'America</option>
							  <option value=ZA>Sudafrica</option>
							  <option value=SD>Sudan</option>
							  <option value=SU>Sudan del Sud</option>
							  <option value=SR>Suriname</option>
							  <option value=SE>Svezia</option>
							  <option value=CH>Svizzera</option>
							  <option value=SZ>Swaziland</option>
							  <option value=TJ>Tadjikistan</option>
							  <option value=TH>Tailandia</option>
							  <option value=TW>Taiwan</option>
							  <option value=TZ>Tanzania</option>
							  <option value=TL>Timor Est</option>
							  <option value=TG>Togo</option>
							  <option value=TK>Tokelau</option>
							  <option value=TO>Tonga</option>
							  <option value=TT>Trinidad e Tobago</option>
							  <option value=TN>Tunisia</option>
							  <option value=TR>Turchia</option>
							  <option value=TM>Turkmenistan</option>
							  <option value=TC>Turks e Caicos</option>
							  <option value=TV>Tuvalu</option>
							  <option value=UA>Ucraina</option>
							  <option value=UG>Uganda</option>
							  <option value=HU>Ungheria</option>
							  <option value=UY>Uruguay</option>
							  <option value=UZ>Uzbekistan</option>
							  <option value=VU>Vanuatu</option>
							  <option value=VA>Vaticano</option>
							  <option value=VE>Venezuela</option>
							  <option value=VN>Vietnam</option>
							  <option value=YE>Yemen</option>
							  <option value=ZM>Zambia</option>
							  <option value=ZW>Zimbabwe</option>
					   </select>
					</td>
				</tr>
				<tr>
					<td width=50% align=right>
						<label for="stemma">Stemma</label>
					</td>
					<td align=left>
main;
					$html->input("stemma",'','','',"file");
					echo <<< main
					</td>
				</tr>
				<tr>
					<td colspan=2 align=center>
main;
					$html->submit("Invia");
					echo <<< main
					</td>
				</tr>
main;
				} //fine parte da visualizzare solo se non si rinnova l'iscrizione
			echo <<< main
			</table>
		</form>
main;
		echo <<< main
		<br>
		<i>Registrandoti, accetti il <a href="docs/regolamento.pdf">regolamento</a> in ogni sua parte</i>
main;
echo '</div>';
$html->close();
$db->close();

?>