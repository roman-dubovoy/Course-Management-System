<?php
class AddmatModel extends Model{
    private static $instance;

    private function __construct(){}

    public static function getInstance()
    {
        if (is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addAdditionalMaterial(array $data)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "INSERT INTO additional_materials(name, href, id_lesson, id_add_mat_type)
                VALUES (:name, :href, :id_lesson, :id_add_mat_type)";
        $stmt = $link->prepare($sql);
        $stmt->execute([':name' => $data['name'], ':href' => $data['href'],
                        ':id_lesson' => $data['id_lesson'], 'id_add_mat_type' => $data['id_add_mat_type']]);
        AddmatModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function isAdditionalMaterialCreated($id_add_mat){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT * FROM additional_materials
                WHERE id_add_mat = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_add_mat, PDO::PARAM_INT);
        $stmt->execute();
        AddmatModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $addmat = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($addmat['id_add_mat']);
    }

    public function getAdditionalMaterialsListByLessonId($id_lesson)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_add_mat, additional_materials.name, href, add_mat_types.name AS add_mat_type 
                FROM additional_materials
                INNER JOIN add_mat_types
                ON additional_materials.id_add_mat_type = add_mat_types.id_add_mat_type
                WHERE id_lesson = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_lesson, PDO::PARAM_INT);
        $stmt->execute();
        AddmatModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $addMatList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $addMatList;
    }

    public function deleteAdditionalMaterial(array $data)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "DELETE FROM additional_materials
                WHERE id_add_mat = :id_add_mat AND id_lesson = :id_lesson";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        AddmatModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }
}