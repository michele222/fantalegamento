<?php
require_once 'class/db.class.php';

class user
{
	
	public $id, 	//id utente da tabella
	$idSession, 	//id tabella sessions
	$session, 		//id sessione
	$ip,
	$login,
	$logout,
	$nick,
	$pwd,			//password (già in sha1)
	$nome,
	$cognome,
	$nazionalita,
	$email,
	$squadra,
	$stadio,
	$allenatore,
	$stemma,
	$soldi,
	$cambi,
	$ordine,
	$tifosi,
	$guest,
	$error,			//stringa di errore ritornata dal costruttore
	$admin,
	$attivo;
	
	private $db;
	
	function __construct($nick = '', $pwd = '')
	{
		session_start();
		
		$this->error = '';
		$this->session = session_id();
		$this->ip = $_SERVER['REMOTE_ADDR'];
		$this->login = date("Y-m-d H:i:s");
		$this->logout = date("Y-m-d H:i:s");
		
		if (!$nick) //guest
		{
			$this->id = 0;
			$this->guest = true;
		}
		else 
		{
			$this->guest = false;
			$this->nick = $nick;
			$this->pwd = $pwd;
			
			$this->loadFromDB($nick, $pwd);
		}
		
		if (!$this->error)
		{
			$this->db = new db();
			$this->db->query("INSERT INTO sessions (userid,session,ip,status,login,logout) VALUES ('".$this->db->escape($this->id)."','".$this->db->escape($this->session)."','".$this->db->escape($this->ip)."',1,'".$this->db->escape($this->login)."','".$this->db->escape($this->logout)."')");
			$this->idSession = $this->db->lastId();
			$this->db->close();
		}

	}
	
	public function loadFromDB($nick, $pwd)
	{
		$this->db = new db();
		$result = $this->db->query("SELECT * FROM utenti WHERE nick='".$this->db->escape($nick)."' AND pwd='".$this->db->escape($pwd)."'");
		if (!$result->num_rows)
			$this->error = 'Nickname o password errati';
		else
		{
			$row = $result->fetch_assoc();

			$this->id = $row['id'];
        	$this->nome = $row['nome'];
        	$this->cognome = $row['cognome'];
        	$this->nazionalita = $row['nazionalita'];
        	$this->email = $row['email'];
        	$this->squadra = $row['squadra'];
        	$this->stadio = $row['stadio'];
        	$this->allenatore = $row['allenatore'];
        	$this->stemma = $row['stemma'];
        	$this->soldi = $row['soldi'];
        	$this->cambi = $row['cambi'];
        	$this->ordine = $row['ordine'];
        	$this->tifosi = $row['tifosi'];
        	$this->admin = $row['admin'];
        	$this->attivo = $row['attivo'];
        	
        	if (!$this->attivo)
				$this->error = 'Non hai ancora attivato la tua squadra: controlla la tua casella email';
        	
        	$result->free();
		}
		$this->db->close();
	}
	
	public function reload()
	{
		$this->loadFromDB($this->nick,$this->pwd);
	}
	
	public function refresh()
	{
		$this->db = new db();
		$this->logout = date("Y-m-d H:i:s");
		$this->db->query("UPDATE sessions SET logout='".$this->db->escape($this->logout)."' WHERE id='".$this->db->escape($this->idSession)."'");
		$this->db->close();
	}
	
	public function refreshAll()
	{
		$this->db = new db();
		$date = date("Y-m-d H:i:s");
		$this->db->query("UPDATE sessions SET status=0 WHERE logout < DATE_SUB('".$this->db->escape($date)."',INTERVAL 30 MINUTE)");
		$this->db->close();
	}
	
	public function logout()
	{
		$this->db = new db();
		$this->logout = date("Y-m-d H:i:s");
		$this->db->query("UPDATE sessions SET logout='".$this->db->escape($this->logout)."', status=0 WHERE id='".$this->db->escape($this->idSession)."'");
		$this->db->close();
	}
	
	public function getStatus()
	{
		$this->db = new db();
		$result = $this->db->query("SELECT status FROM sessions WHERE id='".$this->db->escape($this->idSession)."'");
		$row = $result->fetch_assoc();
		$this->db->close();
		return $row['status'];
	}
	
}
?>