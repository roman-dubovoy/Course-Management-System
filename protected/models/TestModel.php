<?php
class TestModel extends Model{
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

    public function addTest(array $data){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "INSERT INTO tests(mark, date, id_lesson) VALUES (:mark, :date, :id_lesson)";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        TestModel::checkErrorArrayEmptiness($stmt->errorInfo());
        return $this->getTestIdByLessonId($data['id_lesson']);
    }

    public function getTestIdByLessonId($id_lesson)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_test FROM tests WHERE id_lesson = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_lesson, PDO::PARAM_INT);
        $stmt->execute();
        TestModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $test = $stmt->fetch(PDO::FETCH_ASSOC);
        return $test['id_test'];
    }

    public function isTestCreated($id_test)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_test FROM tests WHERE id_test = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_test, PDO::PARAM_INT);
        $stmt->execute();
        TestModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $test = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($test['id_test']);
    }

    public function getTestByLessonId($id_lesson){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT * FROM tests WHERE  id_lesson = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_lesson, PDO::PARAM_INT);
        $stmt->execute();
        TestModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $test = $stmt->fetch(PDO::FETCH_ASSOC);
        return $test;
    }

    public function deleteTest($id_test){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "DELETE FROM tests WHERE id_test = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_test, PDO::PARAM_INT);
        $stmt->execute();
        TestModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function updateTest(array $data){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "UPDATE tests SET mark = :mark WHERE id_test = :id_test";
        $stmt = $link->prepare($sql);
        $stmt->execute(array(':mark' => $data['mark'], ':id_test' => $data['id_test']));
        TestModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function addResult(array $data)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "INSERT INTO results(id_user, id_test, result) 
                VALUES (:id_user, :id_test, :result)";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        TestModel::checkErrorArrayEmptiness($stmt->errorInfo());
        return $this->getResultByIduIdt(['id_user' => $data['id_user'], 'id_test' => $data['id_test']]);
    }

    public function getResultByIduIdt(array $data)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_user, id_test, result FROM results 
                WHERE  id_user = :id_user AND id_test = :id_test";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        TestModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function updateResult(array $data)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "UPDATE results 
                SET result = :result, id_test = :id_test, id_user = :id_user 
                WHERE id_test = :id_test AND id_user = :id_user";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        TestModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }
}