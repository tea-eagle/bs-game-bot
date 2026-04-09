<?php

namespace App\Telegram\Database;

use mysqli;

class MySql implements Database
{
	private $conn;

	function __construct($host, $user, $pass, $dbname)
	{
		$this->conn = new mysqli($host, $user, $pass, $dbname);

		if ($this->conn->connect_error) {
		    die('Ошибка подключения: ' . $this->conn->connect_error);
		}

		$this->conn->set_charset('utf8mb4');
	}

	public function getConnection(): mysqli
    {
        return $this->conn;
    }

    public function __destruct()
    {
        if (isset($this->conn)) {
            $this->conn->close();
        }
    }
}
