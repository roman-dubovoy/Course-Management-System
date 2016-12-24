<?php
class AddmatService{
    private static $instance;
    private $addmatModel;
    private $lessonModel;

    private function __construct(AddmatModel $addmatModel, LessonModel $lessonModel)
    {
        $this->addmatModel = $addmatModel;
        $this->lessonModel = $lessonModel;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)){
            self::$instance = new self(AddmatModel::getInstance(), LessonModel::getInstance());
        }
        return self::$instance;
    }
    
    public function addAdditionalMaterials(array $data){
        if ($this->lessonModel->isLessonCreated($data['id_lesson'])){
            $this->addmatModel->addAdditionalMaterial($data);
        }
        else
            throw new EntityNotFoundException("Lesson with id: {$data['id_lesson']} does not exist.");
    }

    public function getAdditionalMaterialsList($id_lesson){
        if ($this->lessonModel->isLessonCreated($id_lesson)){
            $addmatList = $this->addmatModel->getAdditionalMaterialsListByLessonId($id_lesson);
            if (!empty($addmatList)){
                return $addmatList;
            }
            else
                throw new EntityNotFoundException("Additional materials were not found by lesson id: $id_lesson.");
        }
        else
            throw new EntityNotFoundException("Lesson with id: $id_lesson does not exist.");
    }

    public function deleteAdditionalMaterial(array $data){
        if ($this->lessonModel->isLessonCreated($data['id_lesson'])){
            if ($this->addmatModel->isAdditionalMaterialCreated($data['id_add_mat'])){
                $this->addmatModel->deleteAdditionalMaterial($data);
            }
            else
                throw new EntityNotFoundException("Additional material with id: {$data['id_add_mat']} does not exist.");
        }
        else
            throw new EntityNotFoundException("Lesson with id: {$data['id_lesson']} does not exist.");
    }
}