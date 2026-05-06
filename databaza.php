<?php
$host = "localhost";
$user = "root";
$password = "";

$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Chyba pripojenia: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS games_db";
$conn->query($sql);

$conn->select_db("games_db");

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL
)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    genre VARCHAR(100),
    platform VARCHAR(100),
    status VARCHAR(50),
    user_id INT
)";
$conn->query($sql);
?>