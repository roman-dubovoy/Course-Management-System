<?php
include_once "settings.php";
include_once $_SERVER['DOCUMENT_ROOT']."/protected/library/PDOConnection.php";

try {
    //localhost change to host before deployment!!!
    $connect = new PDO("mysql:host=cms-course-work.com", DB_USER, DB_PASSWORD);
    $connect->exec("CREATE DATABASE IF NOT EXISTS `cms.com`");
} catch (PDOException $e) {
    echo $e->getCode() . ": " . $e->getMessage();
    exit();
}

//Connect
try{
    $link = PDOConnection::getInstance()->getConnection();
}catch (PDOException $e){
    echo $e->getCode().": ".$e->getMessage();
    exit();
}

//users
$sql = "CREATE TABLE IF NOT EXISTS users
(id_u INT(11) NOT NULL AUTO_INCREMENT,
 name VARCHAR(75) NOT NULL,
 password VARCHAR(200) NOT NULL,
 email VARCHAR(50) NOT NULL,
 register_date INT(14) NOT NULL,
 role VARCHAR(20) NOT NULL,
 additional_info VARCHAR(200) DEFAULT NULL,
 PRIMARY KEY (id_u),
 UNIQUE (email))";
try{
    $link->exec($sql);
    if (!empty($link->errorInfo()[1])) {
        print_r($link->errorInfo());
    }
}catch (PDOException $e){
    echo $e->getCode().": ".$e->getMessage();
    exit();
}

//categories of courses
$sql = "CREATE TABLE IF NOT EXISTS courses_categories
(id_category INT(11) NOT NULL AUTO_INCREMENT,
name VARCHAR(100) NOT NULL,
UNIQUE (name),
PRIMARY KEY(id_category))";
try{
    $link->exec($sql);
    if (!empty($link->errorInfo()[1])) {
        print_r($link->errorInfo());
    }
}catch (PDOException $e) {
    echo $e->getCode() . ": " . $e->getMessage();
    exit();
}


$sql = "SELECT * FROM courses_categories";
try{
    $stmt = $link->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($result)){
        $sql = "INSERT INTO courses_categories (name) 
        VALUES ('Humanitarian sciences'), ('Business'), ('Data sciences'), 
        ('Computer sciences'), ('Medical and Biological sciences'), ('Mathematics and Logic'),
        ('Personal Development'), ('Natural and Technical sciences'), ('Social sciences'),
        ('Languages'), ('Design'), ('Hobbies')";
        $link->exec($sql);
        if (!empty($link->errorInfo()[1])) {
            print_r($link->errorInfo());
        }
    }
}catch (PDOException $e) {
    echo $e->getCode() . ": " . $e->getMessage();
    exit();
}

//courses
$sql = "CREATE TABLE IF NOT EXISTS courses
(id_course INT(11) NOT NULL AUTO_INCREMENT,
title VARCHAR(100) NOT NULL,
description TEXT NOT NULL,
date INT(11) NOT NULL,
id_auth INT(11),
id_category INT(11),
additional_info VARCHAR(200) DEFAULT NULL,
UNIQUE (title),
PRIMARY KEY (id_course),
FOREIGN KEY (id_auth) REFERENCES users(id_u)
ON DELETE SET NULL
ON UPDATE CASCADE,
FOREIGN KEY (id_category) REFERENCES courses_categories(id_category)
ON DELETE SET NULL
ON UPDATE CASCADE)";
try{
    $link->exec($sql);
    if (!empty($link->errorInfo()[1])) {
        print_r($link->errorInfo());
    }
} catch (PDOException $e) {
    echo $e->getCode() . ": " . $e->getMessage();
    exit();
}

//lessons
$sql = "CREATE TABLE IF NOT EXISTS lessons
(id_lesson INT(11) NOT NULL AUTO_INCREMENT,
title VARCHAR(100) NOT NULL,
date INT(14) NOT NULL,
id_course INT(11) NOT NULL,
PRIMARY KEY (id_lesson),
FOREIGN KEY (id_course) REFERENCES courses(id_course)
ON DELETE CASCADE
ON UPDATE CASCADE)";
try{
    $link->exec($sql);
    print_r($link->errorInfo());
}catch (PDOException $e){
    echo $e->getCode(). ": " . $e->getMessage();
    exit();
}

//lectures
$sql = "CREATE TABLE IF NOT EXISTS lectures
(id_lecture INT(11) NOT NULL AUTO_INCREMENT,
title VARCHAR(100) NOT NULL,
content TEXT NOT NULL,
date INT(14) NOT NULL,
id_lesson INT(11) NOT NULL,
PRIMARY KEY (id_lecture),
FOREIGN KEY (id_lesson) REFERENCES lessons(id_lesson)
ON DELETE CASCADE
ON UPDATE CASCADE)";
try{
    $link->exec($sql);
    print_r($link->errorInfo());
}catch (PDOException $e){
    echo $e->getCode(). ": " . $e->getMessage();
    exit();
}

//tests
$sql = "CREATE TABLE IF NOT EXISTS tests
(id_test INT(11) NOT NULL AUTO_INCREMENT,
mark DECIMAL(5,2) DEFAULT 0.0,
date INT(14) NOT NULL,
id_lesson INT(11) NOT NULL,
PRIMARY KEY (id_test),
FOREIGN KEY (id_lesson) REFERENCES lessons(id_lesson)
ON DELETE CASCADE
ON UPDATE CASCADE)";
try{
    $link->exec($sql);
    print_r($link->errorInfo());
}catch (PDOException $e){
    echo $e->getCode(). ": " . $e->getMessage();
    exit();
}

//questions
$sql = "CREATE TABLE IF NOT EXISTS questions
(id_question INT(11) NOT NULL AUTO_INCREMENT,
question VARCHAR(350) NOT NULL,
points DECIMAL(5,2) DEFAULT 0.0,
date INT(14) NOT NULL,
id_test INT(11) NOT NULL,
PRIMARY KEY (id_question),
FOREIGN KEY (id_test) REFERENCES tests(id_test)
ON DELETE CASCADE
ON UPDATE CASCADE)";
try{
    $link->exec($sql);
    print_r($link->errorInfo());
}catch (PDOException $e){
    echo $e->getCode(). ": " . $e->getMessage();
    exit();
}

//answers. Field is_correct point correct(1) or incorrect(0) the answer is.
$sql = "CREATE TABLE IF NOT EXISTS answers
(id_answer INT(11) NOT NULL AUTO_INCREMENT,
answer VARCHAR(350) NOT NULL,
date INT(14) NOT NULL,
is_correct INT(5) NOT NULL,
id_question INT(11) NOT NULL,
PRIMARY KEY (id_answer),
FOREIGN KEY (id_question) REFERENCES questions(id_question)
ON DELETE CASCADE
ON UPDATE CASCADE)";
try{
    $link->exec($sql);
    if (!empty($link->errorInfo()[1])) {
        print_r($link->errorInfo());
    }
} catch (PDOException $e) {
    echo $e->getCode() . ": " . $e->getMessage();
    exit();
}

//subscriptions
$sql = "CREATE TABLE IF NOT EXISTS subscriptions
(id_sub INT(11) NOT NULL AUTO_INCREMENT,
id_u INT(11) NOT NULL,
id_course INT(11) NOT NULL,
date INT(11) NOT NULL,
PRIMARY KEY (id_sub),
FOREIGN KEY (id_u) REFERENCES users(id_u)
ON DELETE CASCADE
ON UPDATE CASCADE,
FOREIGN KEY (id_course) REFERENCES courses(id_course)
ON DELETE CASCADE
ON UPDATE CASCADE)";
try {
    $link->exec($sql);
    if (!empty($link->errorInfo()[1])) {
        print_r($link->errorInfo());
    }
} catch (PDOException $e) {
    echo $e->getCode() . ": " . $e->getMessage();
    exit();
}

//comments
$sql = "CREATE TABLE IF NOT EXISTS comments
(id_comment INT(11) NOT NULL AUTO_INCREMENT,
comment TEXT NOT NULL,
date INT(14) NOT NULL,
id_u INT(11) NOT NULL,
id_lesson INT(11) NOT NULL,
PRIMARY KEY (id_comment),
FOREIGN KEY (id_u) REFERENCES users(id_u)
ON DELETE CASCADE
ON UPDATE CASCADE,
FOREIGN KEY (id_lesson) REFERENCES lessons(id_lesson)
ON DELETE CASCADE
ON UPDATE CASCADE)";
try {
    $link->exec($sql);
    if (!empty($link->errorInfo()[1])) {
        print_r($link->errorInfo());
    }
} catch (PDOException $e) {
    echo $e->getCode() . ": " . $e->getMessage();
    exit();
}

//results
$sql = "CREATE TABLE IF NOT EXISTS results
(id_result INT(11) NOT NULL AUTO_INCREMENT,
id_user INT(11) NOT NULL,
id_test INT(11) NOT NULL,
result INT(11) NOT NULL,
PRIMARY KEY (id_result),
FOREIGN KEY (id_user) REFERENCES users(id_u)
ON DELETE CASCADE
ON UPDATE CASCADE,
FOREIGN KEY (id_test) REFERENCES tests(id_test)
ON DELETE CASCADE
ON UPDATE CASCADE)";
try {
    $link->exec($sql);
    if (!empty($link->errorInfo()[1])) {
        print_r($link->errorInfo());
    }
} catch (PDOException $e) {
    echo $e->getCode() . ": " . $e->getMessage();
    exit();
}

//additional materials types
$sql = "CREATE TABLE IF NOT EXISTS add_mat_types
(id_add_mat_type INT(11) NOT NULL AUTO_INCREMENT,
name VARCHAR(100) NOT NULL,
PRIMARY KEY (id_add_mat_type),
UNIQUE (name))";
try{
    $link->exec($sql);
    if (!empty($link->errorInfo()[1])){
        print_r($link->errorInfo());
    }
}catch (PDOException $e){
    echo $e->getCode() . ": " . $e->getMessage();
    exit();
}


$sql = "SELECT * FROM add_mat_types";
try{
    $stmt = $link->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($result)){
        $sql = "INSERT INTO add_mat_types (name) 
        VALUES ('Video'), ('Audio'), ('Document'), 
        ('Text')";
        $link->exec($sql);
        if (!empty($link->errorInfo()[1])) {
            print_r($link->errorInfo());
        }
    }
}catch (PDOException $e) {
    echo $e->getCode() . ": " . $e->getMessage();
    exit();
}


//additional materials
$sql = "CREATE TABLE IF NOT EXISTS additional_materials
(id_add_mat INT(11) NOT NULL AUTO_INCREMENT,
name VARCHAR(50) NOT NULL,
href VARCHAR(200) NOT NULL,
id_lesson INT(11) NOT NULL,
id_add_mat_type INT(11) NOT NULL,
PRIMARY KEY (id_add_mat),
FOREIGN KEY (id_lesson) REFERENCES lessons(id_lesson)
ON DELETE CASCADE 
ON UPDATE CASCADE,
FOREIGN KEY (id_add_mat_type) REFERENCES add_mat_types(id_add_mat_type)
ON DELETE CASCADE 
ON UPDATE CASCADE)";
try{
    $link->exec($sql);
    if (!empty($link->errorInfo()[1])){
        print_r($link->errorInfo());
    }
}catch (PDOException $e){
    echo $e->getCode() . ": " . $e->getMessage();
    exit();
}

echo "Completed";
