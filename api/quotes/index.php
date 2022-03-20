<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

    include_once '../../config/Database.php';

    try {
        //connect to db
        $database = new Database();
        $db = $database->connect();
        //if db connect error
    } catch (PDOException $e) {
        error_log($e);
        http_response_code(500);
        echo json_encode(
            array('message' => "Database connection failed")
        );
        return;
    }
        //inputs from user
    $input = json_decode(file_get_contents("php://input"), true);
        //staments for requests
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $acceptParams = [ 'id' => 'q', 'quote' => 'q', 'authorId' => 'q', 'categoryId' => 'q', 'author' => 'a', 'category' => 'c' ];
            $params = array_values(array_filter(array_keys($acceptParams), function($key) { return isset($_GET[$key]); }));
            $query = 'SELECT q.id, q.quote, a.author, c.category FROM quotes q LEFT JOIN authors a ON q.authorId = a.id LEFT JOIN categories c ON q.categoryId = c.id';
            if (count($params) > 0) {
                $query .= ' WHERE ' . join(' AND ', array_map(function($key) use($acceptParams) { return $acceptParams[$key] . '.' . $key . ' = ?'; }, $params));
            }
            $stmt = $db->prepare($query);
            $stmt->execute(array_map(function($key) { return $_GET[$key]; }, $params));
            $rowCount = $stmt->rowCount();
            if ($rowCount > 0) {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                http_response_code(200);
                if (isset($_GET['random']) && $_GET['random'] == 'true') {
                    echo json_encode($rows[rand(0, $rowCount - 1)]);
                } else {
                    echo json_encode(!isset($_GET['id']) ? $rows : $rows[0]);
                }
            } else {
                http_response_code(200);
                echo json_encode(
                    array('message' => "No Quotes Found")
                );
            }
            break;
        case 'POST':
            $acceptParams = [ 'quote', 'authorId', 'categoryId' ];
            foreach ($acceptParams as $param) {
                if (!isset($input[$param])) {
                    http_response_code(200);
                    echo json_encode(
                        array('message' => "Missing Required Parameters")
                    );
                    return;
                }
            }
            $query = 'INSERT INTO quotes (quote, authorId, categoryId) VALUES (:quote, :authorId, :categoryId)';
            $stmt = $db->prepare($query);
            try {
                $stmt->execute(array_filter($input, function($key) use($acceptParams) { return in_array($key, $acceptParams); }, ARRAY_FILTER_USE_KEY));
            } catch (PDOException $ex) {
                http_response_code(200);
                $message = $ex->getMessage();
                if (strpos($message, 'authorId') !== false) {
                    echo json_encode(
                        array('message' => "authorId Not Found")
                    );
                } else if (strpos($message, 'categoryId') !== false) {
                    echo json_encode(
                        array('message' => "categoryId Not Found")
                    );
                } else {
                    throw $ex;
                }
                return;
            }
            http_response_code(201);
            echo json_encode([
                'id' => $db->lastInsertId(),
                'quote' => $input['quote'],
                'authorId' => $input['authorId'],
                'categoryId' => $input['categoryId']
            ]);
            break;
        case 'PUT':
            $acceptParams = [ 'id', 'quote', 'authorId', 'categoryId' ];
            foreach ($acceptParams as $param) {
                if (!isset($input[$param])) {
                    http_response_code(200);
                    echo json_encode(
                        array('message' => "Missing Required Parameters")
                    );
                    return;
                }
            }
            $query = 'UPDATE quotes SET quote = :quote, authorId = :authorId, categoryId = :categoryId WHERE id = :id';
            $stmt = $db->prepare($query);
            try {
                $stmt->execute(array_filter($input, function($key) use($acceptParams) { return in_array($key, $acceptParams); }, ARRAY_FILTER_USE_KEY));
            } catch (PDOException $ex) {
                http_response_code(200);
                $message = $ex->getMessage();
                if (strpos($message, 'authorId') !== false) {
                    echo json_encode(
                        array('message' => "authorId Not Found")
                    );
                } else if (strpos($message, 'categoryId') !== false) {
                    echo json_encode(
                        array('message' => "categoryId Not Found")
                    );
                } else {
                    throw $ex;
                }
                return;
            }
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode([
                    'id' => $input['id'],
                    'quote' => $input['quote'],
                    'authorId' => $input['authorId'],
                    'categoryId' => $input['categoryId']
                ]);
            } else {
                http_response_code(200);
                echo json_encode(
                    array('message' => "No Quotes Found")
                );
            }
            break;
        case 'DELETE':
            $acceptParams = [ 'id' ];
            foreach ($acceptParams as $param) {
                if (!isset($input[$param])) {
                    http_response_code(200);
                    echo json_encode(
                        array('message' => "Missing Required Parameters")
                    );
                    return;
                }
            }
            $query = 'DELETE FROM quotes WHERE id = :id';
            $stmt = $db->prepare($query);
            try {
                $stmt->execute(array_filter($input, function($key) use($acceptParams) { return in_array($key, $acceptParams); }, ARRAY_FILTER_USE_KEY));
            } catch (PDOException $ex) {
                http_response_code(200);
                throw $ex;
            }
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode([
                    'id' => $input['id']
                ]);
            } else {
                http_response_code(200);
                echo json_encode(
                    array('message' => "No Quotes Found")
                );
            }
            break;
    }