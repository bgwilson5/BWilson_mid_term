<?php
    class Database {
        //DB Params
        private $host = 'acw2033ndw0at1t7.cbetxkdyhwsb.us-east-1.rds.amazonaws.com';
        private $db_name = 'f93h1qjc7jbuh4ux';
        private $username = 'tp4a8htat23uu52s';
        private $password = 'cx2afrmfkvsu70vp';
        private $conn;

        //DB connet
        public function connect() {
            $this->conn = null;

            try {
                $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name,
            $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e){
                echo 'Connection Error: ' . $e->getMessage();
            }

            return $this->conn;
        }
    }
