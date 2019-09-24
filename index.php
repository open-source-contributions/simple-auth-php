<?php

require_once __DIR__ . '/vendor/autoload.php';

use Carbon\Carbon;
use Dotenv\Dotenv;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['result' => 'Sorry. This method is not accepted.']);
    exit(0);
}

$dotEnv = Dotenv::create(__DIR__);
$dotEnv->load();

$databaseDriver = getenv('DATABASE_DRIVER');
$databaseName = getenv('DATABASE_NAME');
$databaseUser = getenv('DATABASE_USER');
$databasePassword = getenv('DATABASE_PASSWORD');

$action = $_POST['action'] ?? 'none';

if ($action === 'none') {
    echo json_encode(['result' => 'Sorry. The action is missing.']);
    exit(0);
}

if ($action === 'login') {
    $user = $_POST['account'] ?? 'none';
    $password = $_POST['password'] ?? 'none';
    $sql = 'select account, password from accounts where account = :account';
    $dsn = sprintf("mysql:host=localhost;dbname=%s;charset=utf8mb4", $databaseName);
    $options = [
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $pdo = new PDO($dsn, $databaseUser, $databasePassword, $options);
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':account' => $user]);
        $result = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) !== 1) {
            echo json_encode(['result' => 'Account Auth is failed.']);
            exit(0);
        } else {
            $hashedPassword = $result[0]['password'];

            if (password_verify($password, $hashedPassword) === false) {
                echo json_encode(['result' => 'Password Auth is failed.']);
                exit(0);
            }

            $expired = new Carbon('Asia/Taipei');
            $expiredDate = $expired->addDays(1)->format('Y-m-d H:m:s');
            $token = hash('sha512', $expiredDate);

            $sql = 'insert into tokens(token, expired) values(:token, :expired)';
            $stmt = $pdo->prepare($sql);

            $stmt->execute([':token' => $token, ':expired' => $expiredDate]);
            $result = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['result' => 'Auth is successful.', 'token' => $token]);
            exit(0);
        }

        $stmt = null;
        $pdo = null;
    } catch (\Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['result' => 'Connection is failed.']);
        exit(0);
    }

    if ($user === 'none' || $password === 'none') {
        echo json_encode(['result' => 'The user or password is missing']);
        exit(0);
    }
} else if ($action === 'logout') {
    $dsn = sprintf("mysql:host=localhost;dbname=%s;charset=utf8mb4", $databaseName);
    $options = [
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $token = $_POST['token'] ?? 'none';

    if ($token === 'none') {
        echo json_encode(['result' => 'Logout is done.']);
    }

    try {
        $sql = 'select count(*) from tokens where token = :token';
        $pdo = new PDO($dsn, $databaseUser, $databasePassword, $options);
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        $result = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) === 0) {
            echo json_encode(['result' => 'Logout is done.']);
        } else {
            $sql = 'delete from tokens where token = :token';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':token' => $token]);
            echo json_encode(['result' => 'Logout is done.']);
        }

        $stmt = null;
        $pdo = null;
    } catch (\Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['result' => 'Connection is failed.']);
    }
} else if ($action === 'status') {
    $dsn = sprintf("mysql:host=localhost;dbname=%s;charset=utf8mb4", $databaseName);
    $options = [
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $token = $_POST['token'] ?? 'none';

    if ($token === 'none') {
        echo json_encode(['result' => 'Logout is done.']);
    }

    try {
        $sql = 'select count(*) from tokens where token = :token';
        $pdo = new PDO($dsn, $databaseUser, $databasePassword, $options);
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        $result = (array) $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) !== 1) {
            echo json_encode(['result' => 'It is not verified.']);
        } else {
            $expiredDate = $result[0]['expired'];
            $expiredTimestamp = Carbon::parse($expiredDate)->timestamp;
            $dateTimestamp = Carbon::now()->timestamp;

            if ($expiredTimestamp - $dateTimestamp <= 0) {
                $sql = 'delete from tokens where token = :token';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':token' => $token]);
                echo json_encode(['result' => 'Token is expired. It should be logout.']);
            } else {
                echo json_encode(['result' => 'Token is live.']);
            }
        }

        $stmt = null;
        $pdo = null;
    } catch (\Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['result' => 'Connection is failed.']);
    }
} else {
    echo json_encode(['result' => 'Invalid actions.']);
    exit(0);
}
