<?php

function sanitizeString(string $string): string
{
    $string = strip_tags($string);
    $string = htmlentities($string);
    return $string;
}

function emailCheckIfExists(string $email, $connection)
{
    $sql = "SELECT * FROM users WHERE email = :email;";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':email', $email);
    $statement->execute();
    if($statement->fetchAll(PDO::FETCH_ASSOC)) {
        throw new Exception('Пользователь с заданым email уже существует.');
    } else return true;
}

class User
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
        $sql = "SELECT * FROM users";
        $statement = $this->connection->prepare($sql);
        $statement->execute();
        var_dump($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function addUser(array $user): void
    { 
        if(empty($user['email']) === true || empty($user['password']) === true || $user['role'] === true) {
            throw new Exception('Одно или несколько полей не заполнено,');
        } else {
            $email = sanitizeString($user['email']);
            if(filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                if(emailCheckIfExists($email, $this->connection) === true) {
                    $password = sanitizeString($user['password']);
                    $role = sanitizeString($user['role']);
                    $password = password_hash($password, PASSWORD_DEFAULT);
                    $userDir = str_replace('.', '0', $email); //не нравится мне точка в имени будущей директории, заменяю её
                    $userHomeDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $userDir;
                    $sql = "INSERT INTO users (email, password, role, userHomeDir) VALUES (:email, :password, :role, :userHomeDir);";                    
                    $statement = $this->connection->prepare($sql);
                    $statement->bindParam(':email', $email);
                    $statement->bindParam(':password', $password);
                    $statement->bindParam(':role', $role);
                    $statement->bindParam(':userHomeDir', $userHomeDir);        
                    $statement->execute();
                    mkdir($userHomeDir, 0777);
                }                                    
            } else {
                throw new Exception('Введён некорректный email,');
            }            
        }           
    }

    public function deleteUser(int $id): void
    {
        $sql = "DELETE FROM users WHERE id = :id;";        
        $statement = $this->connection->prepare($sql);
        $statement->bindParam(':id', $id);
        $statement->execute();
    }

    public function userInfo($id): void
    {
        $sql = "SELECT * FROM users WHERE id = :id;";
        $statement = $this->connection->prepare($sql);
        $statement->bindParam(':id', $id);
        $statement->execute();
        echo json_encode($statement->fetch(PDO::FETCH_ASSOC));        
    }

    public function login($email, $password): void
    {
        $sql = "SELECT id, email, password FROM users";
        $statement = $this->connection->prepare($sql);
        $statement->execute();
        foreach($statement->fetchAll(PDO::FETCH_ASSOC) as $val ) {
            if($val['email'] == $email && password_verify($password, $val['password'])) {
                $token = rand(100000, 999999);
                $hashToken = password_hash($token, PASSWORD_BCRYPT);
                $st = $this->connection->prepare("UPDATE users SET token = :token WHERE id = :id");
                $st->bindParam(':id', $val['id']);
                $st->bindParam(':token', $hashToken);
                $st->execute();
                setcookie('token', $hashToken, time() + 3600, '/');
            }  
        }
    }

    public function logout(): void
    {
        setcookie(session_name(), '', time() - 2592000, '/');
        setcookie('token', '', time() - 2592000, '/');
    }

    public function linkToEmail($to): void
    {
        $subject = 'Ссылка на восстановление пароля';
        $message = $_SERVER['SERVER_NAME'] . '/user/recovery';
        mail($to, $subject, $message);
    }

    public function searchMethod($email)
    {
        //какой-то поиск пользователя по емэйл
    }
}