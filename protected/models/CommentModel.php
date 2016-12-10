<?php
class CommentModel extends Model{
    private static $instance;
    
    protected function __construct(){
        
    }
    
    public static function getInstance(){
        if (is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function addComment(array $data){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "INSERT INTO comments(comment, date, id_u, id_lesson) VALUES (:comment, :date, :id_u, :id_lesson)";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        CommentModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function isCommentCreated($id_comment){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT * FROM comments WHERE id_com = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_comment, PDO::PARAM_INT);
        $stmt->execute();
        CommentModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($comment['id_com']);
    }
    
    public function getCommentsListByLessonId($id_lesson){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_com, comment, date, users.id_u, users.name, users.email 
                FROM comments
                INNER JOIN users
                ON comments.id_u = users.id_u
                WHERE comments.id_lesson = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_lesson, PDO::PARAM_INT);
        $stmt->execute();
        CommentModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $commentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $commentsList;
    }

    public function deleteCommentByUserId(array $data){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "DELETE FROM comments WHERE id_com = :id_comment AND id_u = :id_u";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        CommentModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }
}