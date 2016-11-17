<?php
include $_SERVER['DOCUMENT_ROOT']."/assets/settings.php";

class UserModel extends Model{
    private static $instance = null;

    protected function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addUser(array $data){
        $connection = PDOConnection::getInstance()->getConnection();
        $sql = "INSERT INTO users(name, password, email, register_date, role)
                VALUES(:name, :password, :email, :register_date, :role)";
        $stmt = $connection->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }
    
    public function isRegistered($email){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_u FROM users WHERE email = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($user['id_u']);
    }

    public function getUserByEmailPassword(array $data){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_u, name, password, email, register_date, role FROM users 
                WHERE email = :email AND password = :password";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user;
    }

    public function getUserById($id)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_u, name, password, email, register_date, role FROM users 
                WHERE id_u = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user;
    }

    public function subscribeOnCourse(array $data)
    {
        $connection = PDOConnection::getInstance()->getConnection();
        $sql = "INSERT INTO subscriptions(id_u, id_course, date)
                VALUES(:id_u, :id_course, :date)";
        $stmt = $connection->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function unsubscribeFromCourse(array $data)
    {
        $connection = PDOConnection::getInstance()->getConnection();
        $sql = "DELETE FROM subscriptions WHERE id_u = :id_u AND id_course = :id_course";
        $stmt = $connection->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function isSubscribed(array $data)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_sub, id_u, id_course, date FROM subscriptions 
                WHERE id_u = :id_u AND id_course = :id_course";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($subscription);
    }

    public function getSubscribedUsersListByPeriod(array $data){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT * FROM users 
                WHERE id_u IN(
                SELECT id_u FROM subscriptions WHERE id_course = :id_course AND (date BETWEEN :start_date AND :end_date)
                )";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $subscribedUsersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $subscribedUsersList;
    }

    public function getBestStudentsListByCourseId($id_course){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT * FROM users
                INNER JOIN (SELECT DISTINCT id_user, AVG(result) AS medium_mark
                FROM results 
                GROUP BY id_user
                HAVING id_course = :id_course
                ORDER BY medium_mark DESC
                LIMIT 10) ON users.id_u = id_user";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_course, PDO::PARAM_INT);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $bestStudentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $bestStudentsList;
    }

    public function deleteUser($id_user)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "DELETE FROM users WHERE id_u = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_user, PDO::PARAM_INT);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }
}