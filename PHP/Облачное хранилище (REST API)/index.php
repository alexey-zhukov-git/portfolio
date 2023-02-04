<?php

require_once 'autoload.php';
require_once 'config.php';

$user = new User($servername, $username, $password);
$admin = new Admin($servername, $username, $password);
$file = new File($servername, $username, $password);

$elements = explode('/', $_SERVER['REQUEST_URI']);
$lastElement = end($elements);
if(is_numeric($lastElement)) {
    $id = $lastElement;
}

if(filter_var($lastElement, FILTER_VALIDATE_EMAIL) !== false) {
    $email = $lastElement;
    switch ($_SERVER['REQUEST_URI']) {
        case "/user/search/$email":
            $user->searchMethod($email);
    }
}
if(isset($elements['3']) && isset($elements['4'])) {
    if(is_numeric($elements['3']) && $elements['4']) {
        $file_id = $elements['3']; $user_id = $elements['4'];
        switch ($_SERVER['REQUEST_URI']) {
            case "/files/share/$file_id/$user_id":
                switch($_SERVER['REQUEST_METHOD']) {
                    case 'PUT':
                        $file->addAccess($file_id, $user_id);
                        break;
                    case 'DELETE':
                        $file->deleteAccess($file_id, $user_id);
                        break;
                }
                break;
        }
    }
}


if(isset($id)) {
    switch ($_SERVER['REQUEST_URI']) {
        case "/user/$id":
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $user->userInfo($id);
                    break;
                case 'DELETE':
                    $user->deleteUser($id);
                    break;
            }
            break;
        case "/admin/user/$id":
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $admin->userInfo($id);
                    break;
                case 'DELETE':
                    $admin->deleteUser($id);
                    break;
                case 'PUT':
                    $admin->userUpdate($_POST, $id);
                    break;
                }
                break;
        case "/file/$id":
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $file->fileInfo($id);
                    break;
                case 'DELETE':
                    $file->fileDelete($id);
                    break;
            }
            break;
        case "/directory/$id":
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $file->dirInfo($id);
                    break;
                case 'DELETE':
                    $file->dirDelete($id);
                    break;
            }
            break;
        case "files/share/$id":
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $file->allowedUsers($id);
                    break;
            }
            break;
    }
}

switch ($_SERVER['REQUEST_URI']) {
    case '/user':
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $user->usersList();
                break;
            case 'POST':
                try {
                    $user->addUser($_POST);
                } catch (Exception $e) {
                    echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
                }                
                break;
        }    
        break;  
    case '/user/login':
        switch($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $user->login($_POST['email'], $_POST['password']);
                break;
        }
        break;
    case '/user/logout':
        $user->logout();
        break;
    case '/user/link';
        $user->linkToEmail($_POST['email']);
        break;
    case '/admin/user':
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $admin->usersList();
                break;
        }
        break;
    case "/file":
        switch($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                try {
                    $file->filesList();
                } catch (Exception $e) {
                    echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
                }                
                break;
            case 'POST':
                try {
                    $file->addFile($_POST);
                } catch (Exception $e) {
                    echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
                }                
                break;
            case 'PUT':
                try {
                    $file->renameFile();
                } catch (Exception $e) {
                    echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
                }
                break;
        }
        break;
        case "/directory":
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    try {
                        $file->createDir($_POST);
                    } catch (Exception $e) {
                        echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
                    }                
                    break;
                case 'PUT':
                    try {
                        $file->renameDir();
                    } catch (Exception $e) {
                        echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
                    }                
                    break;
                }
            break;
}