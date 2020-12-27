<?php

session_start();

define('DSN', 'mysql:host=db;dbname=myapp;charset=utf8mb4');
define('DB_USER', 'myappuser');
define('DB_PASS', 'myapppass');
// define('SITE_URL', 'http://localhost:8562');
//phpの$_SERVER変数で取得できるので下記でもOK！
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);

try{
  $pdo = new PDO(DSN, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES => false
  ]);
} catch(PDOException $e) {
  echo $e->getMessage();
  exit;
}

//htmlタグをエスケープする関数
function h($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

//csrf対策...tokenがなかったら作成
function createToken() {
  if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
  }
}

//csrf対策...tokenをバリデーション
function validateToken() {
  if (
    //なかったらあかん
   empty($_SESSION['token']) ||
    //一致しなかったらあかん
   $_SESSION['token'] !== filter_input(INPUT_POST, 'token')
   ) {
     exit('Invaid post request');
   } 
}

//Todoを追加
function addTodo($pdo) {
  $title = trim(filter_input(INPUT_POST, 'title'));
  if ($title === '') {
    return;
  }

  $stmt = $pdo->prepare('INSERT INTO todos (title) VALUES (:title)');
  $stmt->bindValue('title', $title, PDO::PARAM_STR);
  $stmt->execute();

}

//Todoを取得
function getTodos($pdo) {
  $stmt = $pdo->query('SELECT * FROM todos ORDER BY id DESC');
  $todos = $stmt->fetchAll();
  return $todos;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  addTodo($pdo);

  header('Location: ' . SITE_URL);
  exit;
}

$todos = getTodos($pdo);

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="./css/style.css">
  <title>My Todos</title>
</head>
<body>
  <h1>Todos</h1>

  <!-- データを送信するときは必ずcsrf対策を施す -->
  <form action="" method="post">
    <input type="text" name="title" placeholder="Type new todo.">
    <!-- formタグ内にinput要素がひとつの場合エンターキーで送信できる -->
  </form>

  <ul>
    <?php foreach($todos as $todo): ?>
    <li>
      <input type="checkbox" <?= $todo->is_done ? 'checked' : ''; ?>>
      <span class="<?= $todo->is_done ? 'done' : ''; ?>"><?= h($todo->title); ?></span>
    </li>
    <?php endforeach; ?>
  </ul>


</body>
</html>
