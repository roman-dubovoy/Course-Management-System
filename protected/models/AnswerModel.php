<?php
class AnswerModel extends Model{
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

    public function addAnswer(array $data){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "INSERT INTO answers(answer, date, is_correct, id_question) VALUES (:answer, :date, :is_correct, :id_question)";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        AnswerModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function isAnswerCreated($id_answer){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_answer FROM answers WHERE id_answer = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_answer, PDO::PARAM_INT);
        $stmt->execute();
        AnswerModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $answer = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($answer['id_answer']);
    }

    public function getAnswersListByQuestionId($id_question){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_answer, answer, is_correct FROM answers WHERE id_question = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_question, PDO::PARAM_INT);
        $stmt->execute();
        AnswerModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $answersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $answersList;
    }

    public function deleteAnswer($id_answer){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "DELETE FROM answers WHERE id_answer = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_answer, PDO::PARAM_INT);
        $stmt->execute();
        AnswerModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function updateAnswer(array $data){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "UPDATE answers SET answer = :answer WHERE id_answer = :id_answer";
        $stmt = $link->prepare($sql);
        $stmt->execute(array(':answer' => $data['answer'], ':id_answer' => $data['id_answer']));
        AnswerModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function getAnswerById($id_answer)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_answer, answer, date, is_correct, id_question 
                FROM answers WHERE  id_answer = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_answer, PDO::PARAM_INT);
        $stmt->execute();
        AnswerModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $answer = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $answer;
    }
}