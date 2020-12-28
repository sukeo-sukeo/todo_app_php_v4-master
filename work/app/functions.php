<?php

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

function getPdoInstance() {
  try{
    $pdo = new PDO(DSN, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
      PDO::ATTR_EMULATE_PREPARES => false
    ]);
    return $pdo;
  } catch(PDOException $e) {
    echo $e->getMessage();
    exit;
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

//checkboxを更新
function toggleTodo($pdo) {
  $id = filter_input(INPUT_POST, 'id');
  if (empty($id)) {
    return;
  }

  $stmt = $pdo->prepare('UPDATE todos SET is_done = NOT is_done WHERE id = :id');
  $stmt->bindValue('id', $id, PDO::PARAM_INT);
  $stmt->execute();
}

//削除ボタンの処理
function deleteTodo($pdo) {
  $id = filter_input(INPUT_POST, 'id');
  if (empty($id)) {
    return;
  }

  $stmt = $pdo->prepare('DELETE FROM todos WHERE id = :id');
  $stmt->bindValue('id', $id, PDO::PARAM_INT);
  $stmt->execute();
}

//Todoを取得
function getTodos($pdo) {
  $stmt = $pdo->query('SELECT * FROM todos ORDER BY id DESC');
  $todos = $stmt->fetchAll();
  return $todos;
}