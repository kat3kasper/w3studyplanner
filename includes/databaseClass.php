<?php

	class database
	{
		private $link;
		private $res;
		private $host;
		private $user;
		private $pass;
		private $db;
		
		//Sets user, pass, host, and connects
		public function setup($u, $p, $h, $db)
		{
			$this->user = $u;
			$this->pass = $p;
			$this->host = $h;
			$this->db = $db;
			
			if(isset($this->link))
				$this->disconnect();
			
			$this->connect();
		}
		
		//Change database to use
		public function pick_db($db)
		{
			$this->db = $db;
		}
		
		//Destructor disconnects
		public function __destruct()
		{
			$this->disconnect();
		}
		
		//Close connection to database
		public function disconnect()
		{
			if(isset($this->link))
				mysql_close($this->link);
			
			unset($this->link);
		}
		
		//Connect/reconnect to database
		public function connect()
		{
			if(!isset($this->link))
			{
				try
				{
					if(!($this->link = mysql_connect($this->host, $this->user, $this->pass)))
						throw new Exception("Cannot connect to " . $this->host);
				} catch(Exception $e)
				{
					echo $e->getMessage() . "<br/>\n";
					exit;
				}
			}
			else
			{
				$this->disconnect();
				$this->connect();
			}
		}
		
		//Sends an SQL query
		public function send_sql($sql)
		{
			if(!isset($this->link))
				$this->connect();
			
			try
			{
				if(!($succ = mysql_select_db($this->db)))
					throw new Exception("Could not select database " . $this->db);
				
				if(!($this->res = mysql_query($sql, $this->link)))
					throw new Exception("Could not send query");
			} catch(Exception $e)
			{
				echo $e->getMessage() . "<br/>\n";
				echo mysql_error();
				exit;
			}
			
			return $this->res;
		}
		
		//Shows contents of $res as table
		public function printout()
		{
			if(isset($this->res) && (mysql_num_rows($this->res) > 0))
			{
				mysql_data_seek($this->res, 0);
				$num = mysql_num_fields($this->res);
				
				echo "<table border=1>";
				echo "<tr>";
				
				for($i = 0; $i < $num; $i++)
				{
					echo "<th>";
					echo mysql_field_name($this->res, $i);
					echo "</th>";
				}
				
				echo "</tr>";
				
				while($row = mysql_fetch_row($this->res))
				{
					echo "<tr>";
					foreach($row as $elem)
						echo "<td>" . $elem . "</td>";
					
					echo "</tr>";
				}
				
				echo "</table>";
			}
			else
				echo "There is nothing to print!<br/>\n";
		}
		
		//Returns array with next row
		public function next_row()
		{
			if(isset($this->res))
				return mysql_fetch_row($this->res);
			
			echo "You need to make a query first!";
			return false;
		}
		
		//Returns last AUTO_INCREMENT data created
		public function insert_id()
		{
			if(isset($this->link))
			{
				$id = mysql_insert_id($this->link);
				if($id == 0)
					echo "You did not insert an element that cause an auto-increment ID to be created!";
				
				return $id;
			}
			
			echo "You are not connected to the database!";
			return false;
		}
		
		//Creates a new DB and uses it
		public function new_db($name)
		{
			if(!isset($this->link))
				$this->connect();
			
			$query = "CREATE DATABASE IF NOT EXISTS " . $name;
			try
			{
				if(!(mysql_query($query, $this->link)))
					throw new Exception("Cannot create database " . $name);
				
				$this->db = $name;
			} catch(Exception $e)
			{
				echo $e->getMessage() . "<br/>\n";
				echo mysql_error();
				exit;
			}
		}
	}
?> 