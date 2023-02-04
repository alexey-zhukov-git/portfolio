<?php

class Admin
{
    public $connection;
    
    public function __construct($servername, $username, $password)
    {
        try {
            $this->connection = new PDO("mysql:host=$servername;dbname=final", $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
          echo "Connection failed: " . $e->getMessage();
        }
    }

    public function usersList(): void
    {
        $statement = $this->connection->prepare("SELECT * FROM users");
        $statement->execute();
        $usersListArray = $statement->fetchAll(PDO::FETCH_ASSOC);
        if(isset($_COOKIE['token'])) {
            foreach($usersListArray as $key => $val) {
                if($_COOKIE['token'] == $val['token'] && $val['role'] == 'admin') {
                    echo json_encode($usersListArray);
                }             
            }
        } else {
            header("HTTP/2.0 403 Forbidden");
            echo 'Вы не авторизованы';
        }        
    }

    public function userInfo($id): void
    {
        $statement = $this->connection->prepare("SELECT * FROM users");
        $statement->execute();
        $usersListArray = $statement->fetchAll(PDO::FETCH_ASSOC);
        if(isset($_COOKIE['token'])) {
            foreach($usersListArray as $key => $val) {
                if($_COOKIE['token'] == $val['token'] && $val['role'] == 'admin') {
                    $st = $this->connection->prepare("SELECT * FROM users WHERE id = :id;");
                    $st->bindParam(':id', $id);
                    $st->execute();
                    echo json_encode($st->fetch(PDO::FETCH_ASSOC));
                }             
            }
        } else {
            header("HTTP/2.0 403 Forbidden");
            echo 'Вы не авторизованы';
        }   
    }

    public function deleteUser($id): void
    {
        $statement = $this->connection->prepare("SELECT * FROM users");
        $statement->execute();
        $usersListArray = $statement->fetchAll(PDO::FETCH_ASSOC);
        if(isset($_COOKIE['token'])) {
            foreach($usersListArray as $key => $val) {
                if($_COOKIE['token'] == $val['token'] && $val['role'] == 'admin') {
                    $st = $this->connection->prepare("DELETE FROM users WHERE id = :id;");
                    $st->bindParam(':id', $id);
                    $st->execute();
                }              
            }
        } else {
            header("HTTP/2.0 403 Forbidden");
            echo 'Вы не авторизованы';
        }   
    }

    public function userUpdate($user, $id): void
    {
        $statement = $this->connection->prepare("SELECT * FROM users");
        $statement->execute();
        $usersListArray = $statement->fetchAll(PDO::FETCH_ASSOC);
        if(isset($_COOKIE['token'])) {
            foreach($usersListArray as $key => $val) {
                if($_COOKIE['token'] == $val['token'] && $val['role'] == 'admin') {
                    $sql = "UPDATE users SET id = :id, email = :email, password = :password, role = :role WHERE id = :id;";
                    $password = password_hash($user['password'], PASSWORD_DEFAULT);      
                    $st = $this->connection->prepare($sql);
                    $st->bindParam(':id', $id);
                    $st->bindParam(':email', $user['email']);
                    $st->bindParam(':password', $password);
                    $st->bindParam(':role', $user['role']);
                    $st->execute($user);
                }               
            }
        } else {
            header("HTTP/2.0 403 Forbidden");
            echo 'Вы не авторизованы';
        }   
    }
}