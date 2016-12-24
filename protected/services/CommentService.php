<?php
class CommentService{
    private static $instance = null;
    private $commentModel;
    private $userModel;
    private $lessonModel;
    
    protected function __construct(CommentModel $commentModel, UserModel $userModel, LessonModel $lessonModel){
        $this->commentModel = $commentModel;
        $this->userModel = $userModel;
        $this->lessonModel = $lessonModel;
    }
    
    public static function getInstance(){
        if (is_null(self::$instance)){
            self::$instance = new self(CommentModel::getInstance(), UserModel::getInstance(), LessonModel::getInstance());
        }
        return self::$instance;
    }

    public function addComment(array $data){
        if ($this->userModel->getUserById($data['id_u'])){
            if ($this->lessonModel->isLessonCreated($data['id_lesson'])){
                $this->commentModel->addComment($data);
            }
            else
                throw new EntityNotFoundException("Lesson with id: {$data['id_lesson']} does not exist.");
        }
        else
            throw new EntityNotFoundException("User with id: {$data['id_u']} does not exist.");
    }

    public function getCommentsList($id_lesson){
        if ($this->lessonModel->isLessonCreated($id_lesson)){
            $commentsList = $this->commentModel->getCommentsListByLessonId($id_lesson);
            if (!empty($commentsList)){
                return $commentsList;
            }
            else
               throw new EntityNotFoundException("Comments were not found by id_lesson: $id_lesson");
        }
        else
            throw new EntityNotFoundException("Lesson with id: $id_lesson does not exist.");
    }
    
    public function deleteComment(array $data){
        if ($this->userModel->getUserById($data['id_u'])){
            if ($this->commentModel->isCommentCreated($data['id_comment'])){
                $this->commentModel->deleteCommentByUserId($data);
            }
            else
                throw new EntityNotFoundException("Comment with id: {$data['id_comment']} does not exist.");
        }
        else
            throw new EntityNotFoundException("User with id: {$data['id_u']} does not exist.");
    }
}