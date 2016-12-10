<?php
class AddmatController{
    private $addmatService;
    
    public function __construct()
    {
        $this->addmatService = AddmatService::getInstance();
    }

    public function addAdditionalMaterialsAction(){
        $commentsList = json_decode(file_get_contents("php://input"), true);
        foreach ($commentsList as $comment) {
            $commentData = [
                'name' => strip_tags(trim($comment['name'])),
                'href' => strip_tags(trim($comment['href'])),
                'id_lesson' => strip_tags($comment['id_lesson']),
                'id_add_mat_type' => strip_tags(trim($comment['id_add_mat_type']))
            ];
            foreach ($commentData as $key => $value) {
                if (empty($value)) {
                    HTTPResponseBuilder::getInstance()->sendFailRespond(400, "Missing params", "Missing param: `$key`");
                }
            }
            try {
                $this->addmatService->addAdditionalMaterials($commentData);
                http_response_code(201);
            } catch (EntityNotFoundException $e) {
                HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
            } catch (StatementExecutionException $e) {
                HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
            } catch (PDOException $e) {
                HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
            }
        }
    }

    public function getAdditionalMaterialsListAction(){
        $id_lesson = strip_tags(trim($_POST['id_lesson']));
        if (empty($id_lesson)){
            HTTPResponseBuilder::getInstance()->sendFailRespond(400, "Missing params", "Missing param: id_lesson");
        }
        try{
            $addMatList = $this->addmatService->getAdditionalMaterialsList($id_lesson);
            FrontController::getInstance()->setBody(json_encode($addMatList));
        }catch (EntityNotFoundException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        }
        catch (StatementExecutionException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
        catch (PDOException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function deleteAdditionalMaterialAction(){
        $data = [
            'id_add_mat' => strip_tags(trim($_POST['id_add_mat'])),
            'id_lesson' => strip_tags(trim($_POST['id_lesson']))
        ];
        foreach ($data as $key=>$value){
            if (empty($value)){
                HTTPResponseBuilder::getInstance()->sendFailRespond(400, "Missing params", "Missing param: `$key`");
            }
        }
        try{
            $this->addmatService->deleteAdditionalMaterial($data);
            http_response_code(200);
        }catch (EntityNotFoundException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        }
        catch (StatementExecutionException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
        catch (PDOException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }
}