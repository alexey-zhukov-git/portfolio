<?php

class File
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
    
    public function filesList(): void
    {
        $statement = $this->connection->prepare("SELECT * FROM users");
        $statement->execute();
        $usersListArray = $statement->fetchAll(PDO::FETCH_ASSOC);
        if(isset($_COOKIE['token'])) {
            foreach($usersListArray as $key => $val) {
                if($_COOKIE['token'] == $val['token']) {
                    //в задании требуется вывести файлы, окей, выведем рекурсивно все файлы в домашней директории юзера
                    $it  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($val['userHomeDir']));
                    foreach($it as $val) {
                        if(is_file($val)) {
                            echo $val . PHP_EOL;
                        }    
                    }
                }             
            }
        } else {
            header("HTTP/2.0 403 Forbidden");
            throw new Exception('Вы не авторизованы.');
        }
    }

    public function addFile(array $dirName): void
    {
        $statement = $this->connection->prepare("SELECT * FROM users");
        $statement->execute();
        $usersListArray = $statement->fetchAll(PDO::FETCH_ASSOC);
        if(isset($_COOKIE['token'])) {
            foreach($usersListArray as $val) {
                if($_COOKIE['token'] == $val['token']) {
                    $userId = $val['id'];
                    $filename = $_FILES['filename']['name'];
                    $sql = "INSERT INTO files (userId, filename) VALUES (:userId, :filename);";                    
                    $statement = $this->connection->prepare($sql);
                    $statement->bindParam(':userId', $userId);
                    $destinationName = $dirName['dirName'] . '/' . $_FILES['filename']['name'];
                    $statement->bindParam(':filename', $destinationName);
                    $statement->execute();
                    move_uploaded_file($_FILES['filename']['tmp_name'], $destinationName);
                }              
            }
        } else {
            header("HTTP/2.0 403 Forbidden");
            throw new Exception('Вы не авторизованы.');
        }
    }

    public function fileInfo(int $id): void
    {
        $statement = $this->connection->prepare("SELECT * FROM files");
        $statement->execute();
        $filesListArray = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach($filesListArray as $key => $val) {
            if($val['id'] == $id) {
                var_dump($val);
            }
        }
    }

    public function renameFile(): void
    {
        // парсим входящий http запрос и помещаем данные в $_REQUEST
        $request = new Restful\Parser();
        $request->parse();

        $statement = $this->connection->prepare("SELECT * FROM users");
        $statement->execute();
        $usersListArray = $statement->fetchAll(PDO::FETCH_ASSOC);
        if(isset($_COOKIE['token'])) {
            foreach($usersListArray as $val) {
                if($_COOKIE['token'] == $val['token']) {
                    if(rename($_REQUEST['from'], $_REQUEST['to']) === false) {
                        throw new Exception('Что-то пошло не так. Проверьте входные данные.');
                    }                   
                }              
            }
            $sql = "UPDATE files SET filename = :to WHERE filename = :from;";                    
            $statement = $this->connection->prepare($sql);
            $statement->bindParam(':to', $_REQUEST['to']);
            $statement->bindParam(':from', $_REQUEST['from']);
            $statement->execute();
        } else {
            header("HTTP/2.0 403 Forbidden");
            throw new Exception('Вы не авторизованы.');
        }
    }

    public function fileDelete($id)
    {                  
        $statement = $this->connection->prepare("SELECT filename FROM files WHERE id = :id;");
        $statement->bindParam(':id', $id);
        $statement->execute();       
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if(unlink($result['filename']) === false) {
            throw new Exception('Что-то пошло не так.');
        }
        $statement = $this->connection->prepare("DELETE FROM files WHERE id = :id;");
        $statement->bindParam(':id', $id);
        $statement->execute();
    }

    public function createDir($dirName)
    {
        if(mkdir($dirName['dirName'], 0777) === false) {
            throw new Exception('Что-то пошло не так.');
        }
        $sql = "INSERT INTO dirs (dir) VALUES (:dir);";                    
        $statement = $this->connection->prepare($sql);
        $statement->bindParam(':dir', $dirName['dirName']);
        $statement->execute();
    }

    public function renameDir()
    {
        // парсим входящий http запрос и помещаем данные в $_REQUEST
        $request = new Restful\Parser();
        $request->parse();

        if(rename($_REQUEST['from'], $_REQUEST['to']) === false) {
            throw new Exception('Что-то пошло не так.');
        }

        $sql = "UPDATE dirs SET dir = :to WHERE dir = :from;";                    
        $statement = $this->connection->prepare($sql);
        $statement->bindParam(':to', $_REQUEST['to']);
        $statement->bindParam(':from', $_REQUEST['from']);
        $statement->execute();
    }

    public function dirInfo($id)
    {
        $statement = $this->connection->prepare("SELECT dir FROM dirs WHERE id = :id;");
        $statement->bindParam(':id', $id);
        $statement->execute();       
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $it  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($result['dir']));
        foreach($it as $val) {
            if(is_file($val)) {
                echo $val . PHP_EOL;
            }    
        }
    }

    public function dirDelete($id)
    {
        $statement = $this->connection->prepare("SELECT dir FROM dirs WHERE id = :id;");
        $statement->bindParam(':id', $id);
        $statement->execute();       
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        rmdir($result['dir']); //вообще, директория должна быть пустая, или рекурсивно удалять вместе со всем содержимым
    }

    public function allowedUsers($fileId)
    {
        $statement = $this->connection->prepare("SELECT FROM fileAccess WHERE fileId = :fileId;");
        $statement->bindParam(':fileId', $file_id);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
        
    }

    public function addAccess($file_id, $user_id) 
    {
        $sql = "INSERT INTO fileAccess (fileId, userId) VALUES (:fileId, :userId);";                    
        $statement = $this->connection->prepare($sql);
        $statement->bindParam(':fileId', $file_id);
        $statement->bindParam(':userId', $user_id);
        $statement->execute();
    }

    public function deleteAccess($file_id, $user_id)
    {
        $statement = $this->connection->prepare("DELETE FROM fileAccess WHERE fileId = :fileId AND userId = :userId;");
        $statement->bindParam(':fileId', $file_id);
        $statement->bindParam(':userId', $user_id);
        $statement->execute();
    }
}