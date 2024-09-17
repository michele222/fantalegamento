<?php
class html
{
	function __construct($title = 0)
	{
		global $user;
		if($title)
		{
			$this->head($title);
			$this->bodyon();
			$this->container();
			$this->header();
			$this->menu($user);
			$this->main();
		}
	}
	
	function close()
	{
		$this->divoff();
		$this->footer();
		$this->divoff();
		$this->bodyoff();
	}
	
	public function head($title)
	{
		echo <<<HEAD
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html>
		<head>
			<meta http-equiv="content-type" content="text/html; charset=utf-8" />
			<title>FANTALEGAMENTO 2020/21 - $title</title>
			<link rel="stylesheet" type="text/css" href="css/fantalegamento.css" />
			<link rel="icon" href="favicon.ico" />
			<script type="text/javascript">
HEAD;
			include_once 'script/fantalegamento.js';
			echo <<<HEAD
			</script>
		</head>
HEAD;
	}
	
	public function container(){echo '<div id="container">';}
	
	public function header($admin = 0)
	{
		if($admin)
		{
			$admin = '<a href=\'admin.php\'>';
			$a = '</a>';
		}
		else 
			$admin = $a = '';
		echo <<<HEADER
		<div id="header">
			$admin<img src='images/title.jpg' width=100% heigth=100%>$a
		</div>
HEADER;
	}
	
	public function script($script)
	{
		echo <<<SCRIPT
		<script type="text/javascript">
		$script
		</script>
SCRIPT;
	}
	
	public function main(){echo '<div id="main">';}
	
	public function lefttable(){echo '<div id="lefttable">';}
	
	public function bodyon(){echo '<body>';}
	
	public function bodyoff(){echo '</body>';}
	
	public function divoff() {echo '</div>';}
	
	public function error($text) {echo '<div class="errmsg">'.$text.'</div>';}
	
	public function linkgiocatore($id, $text) {return '<a href="giocatore.php?id='.$id.'">'.$text.'</a>';}
	
	public function linksquadra($id, $text) {return '<a href="squadra.php?id='.$id.'">'.$text.'</a>';}
	
	//public function linksquadraa($squadra) {return '<a href="squadraa.php?id='.$squadra.'"><img class="stemmino" src="images/stemmia/'.$squadra.'.png" />'.$squadra.'</a>';}
	public function linksquadraa($squadra) {return '<a href="squadraa.php?id='.$squadra.'">'.$squadra.'</a>';}
	
	public function linkpunteggio($squadra, $giornata, $text) {return '<a href="punteggio.php?giornata='.$giornata.'&id='.$squadra.'">'.$text.'</a>';}
	
	public function submit($text, $id = 0)
	{
		echo '<input type="submit" class="submit" value="'.$text.'"';
		if ($id)
			echo 'name="'.$id.'" id="'.$id.'" ';
		else 
			echo 'name="submit" ';
		echo '>';
	}
	
	public function input($name, $size = 0, $maxlen = 0, $value = '', $type = 'text')
	{
		echo '<input type="'.$type.'" name="'.$name.'" id="'.$name.'"';
		if ($size)
			echo ' size='.$size;
		if ($maxlen)
			echo ' maxlength='.$maxlen;
		if ($value)
			echo ' value="'.htmlspecialchars($value).'"';
		echo ' />';
	}
	
	public function textarea($name, $rows = 1, $cols = 30, $value = '')
	{
		echo '<textarea name="'.$name.'" id="'.$name.'"';
		if ($rows)
			echo ' rows='.$rows;
		if ($cols)
			echo ' cols='.$cols;
		echo '>'.htmlspecialchars($value).'</textarea>';
	}
	
	public function menu($user)
	{
		include_once 'include/menu.php';
	}
	
	public function footer()
	{
		echo <<< FOOTER
		<div id="footer">
FOOTER;
		$this->date();
		echo <<< FOOTER
		- Fantalegamento 2020/21
		</div>
FOOTER;
	}
	
	public function date()
	{
		echo date('d/m/Y H:i:s');
	}
	
	public function title($title, $img=0)
	{
		echo '<h2>';
		if($img)
			echo '<img src="'.$img.'" />';
		echo $title.'</h2>';
	}
	
	public function title_squadra($row, $disp=0)
	{
		echo '<div class="team_container'.($disp?'_disp':'').'"><div class="img_squadra_container"><img class="img_squadra" src="';
		if(!$row['stemma'] || $row['stemma'] == "_DEFAULTLOGO")
			echo _DEFAULTLOGO;
		else
			echo $row['stemma'];
		echo '" /></div><div class="title_squadra">'.$row['squadra'].'<img class="bandiera" src="images/bandiere/'.$row['nazionalita'].'.gif" width=30></div>
		<br>Presidente: <i>'.$row['nome'].' '.$row['cognome'].'</i>
		<br>Allenatore: <i>'.$row['allenatore'].'</i>
		<br>Stadio: <i>'.$row['stadio'].'</i>
		<br><a class="button" href="squadra.php?id='.$row['id'].'">ROSA</a><a class="button" href="formazione.php?id='.$row['id'].'">FORMAZIONE</a></div>';
		
	}
	
	public function tabletitle($title)
	{
		/*echo <<< TABLE
		<table class="table">
			<tr>
				<th>
					Rosa
				</th>
			</tr>
		</table>
TABLE;*/
		echo <<< TABLE
		<div class="tabletitle">
			$title
		</div>
TABLE;
	}
	
	public function tablesortheader($page, $field, $text)
	{
		echo "<th><a href='".$page."?orderby=".$field."&order=ASC'>&#60;</a>".$text."<a href='".$page."?orderby=".$field."&order=DESC'>&#62;</a></th>";
	}
}
?>