<?php
class UserController{
    private $userService;

    public function __construct()
    {
        $this->userService = UserService::getInstance();
    }

    public function registerUserAction()
    {
        $data = [
            'name' => strip_tags(trim($_POST['name'])),
            'email' => strip_tags(trim($_POST['email'])),
            'password' => hash("sha256", strip_tags(trim($_POST['password']))),
            'role' => strip_tags(trim($_POST['role'])),
            'register_date' => time()
        ];
        foreach ($data as $key=>$value){
            if(empty($value)){
                HTTPResponseBuilder::getInstance()->sendFailRespond(400, "Missing params", "Missing param: `$key`");
            }
        }
        try {
            $this->userService->registerUser($data);
            http_response_code(201);
        }catch (UserExistsException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(403, "Collision", $e->getMessage());
        }
        catch (StatementExecutionException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
        catch (PDOException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function meAction()
    {
        $data = [
            'email' => strip_tags(trim($_POST['email'])),
            'password' => hash("sha256", strip_tags(trim($_POST['password'])))
        ];
        foreach ($data as $key=>$value){
            if (empty($value)){
                HTTPResponseBuilder::getInstance()->sendFailRespond(400, "Missing params", "Missing param: `$key`");
            }
        }
        try {
            $userInfo = $this->userService->authUser($data);
            FrontController::getInstance()->setBody(json_encode($userInfo));
        } catch (PDOException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
        catch (StatementExecutionException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
        catch (AuthorizationException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(401, "User unauthorized", $e->getMessage());
        }
    }
    
    public function subscribeAction()
    {
        $data = [
            'id_course' => strip_tags(trim($_POST['id_course'])),
            'id_u' => strip_tags(trim($_POST['id_u']))
        ];
        foreach ($data as $key => $value) {
            if (empty($value)) {
                HTTPResponseBuilder::getInstance()->sendFailRespond(400, "Missing params", "Missing param: `$key`");
            }
        }
        $data['date'] = time();
        try {
            $this->userService->subscribeOnCourse($data);
        } catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (EntityNotFoundException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function unsubscribeAction()
    {
        $data = [
            'id_course' => strip_tags(trim($_POST['id_course'])),
            'id_u' => strip_tags(trim($_POST['id_u']))
        ];
        foreach ($data as $key => $value) {
            if (empty($value)) {
                HTTPResponseBuilder::getInstance()->sendFailRespond(400, "Missing params", "Missing param: `$key`");
            }
        }
        try {
            $this->userService->unsubscribeFromCourse($data);
        } catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (EntityNotFoundException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "User not found", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function getSubscribedUsersListAction()
    {
        $data = [
            'start_date' => strip_tags(trim($_POST['start_date'])),
            'end_date' => strip_tags(trim($_POST['end_date']))
        ];
        foreach ($data as $key=>$value) {
            if (empty($value)){
                HTTPResponseBuilder::getInstance()->sendFailRespond(400, "Missing params", "Missing param: `$key`");
            }
        }
        try{
            $subscribedUsersList = $this->userService->getSubscribedUsersListByPeriod($data);
            FrontController::getInstance()->setBody(json_encode($subscribedUsersList));
        }catch (EntityNotFoundException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        } catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function getBestStudentListAction()
    {
        try{
            $bestStudentsList = $this->userService->getBestStudentsList();
            FrontController::getInstance()->setBody(json_encode($bestStudentsList));
        }catch (EntityNotFoundException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        } catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function getUsersListByNameFilterAction()
    {
        $name_filter = strip_tags(trim($_POST['name_filter']));
        if (empty($name_filter)) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(400, "Missing params", "Missing param: name_filter");
        }
        try{
            $usersList = $this->userService->getUsersListByNameFilter($name_filter);
            FrontController::getInstance()->setBody(json_encode($usersList));
        }catch (EntityNotFoundException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        }
        catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }


    public function deleteUserAction()
    {
        $id_user = strip_tags(trim($_POST['id_user']));
        if (empty($id_user)) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(400, "Missing params", "Missing param: $id_user");
        }
        try {
            $this->userService->deleteUser($id_user);
        } catch (EntityNotFoundException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "User not found", $e->getMessage());
        } catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function getOldestUsersListByRolesAction()
    {
        try{
            $usersList = $this->userService->getOldestUsersListByRoles();
            FrontController::getInstance()->setBody(json_encode($usersList));
        }catch (EntityNotFoundException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        } catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function getUsersAmountSubscribedForLastMonthAction()
    {
        try{
            $userAmount = $this->userService->getUsersAmountSubscribedForLastMonth();
            FrontController::getInstance()->setBody(json_encode(['userAmountSubscribedForLastMonth' => $userAmount]));
        }catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function getUsersAndPassedTestsAmountListAction()
    {
        try{
            $usersList = $this->userService->getUsersAndPassedTestsAmountList();
            FrontController::getInstance()->setBody(json_encode($usersList));
        }catch (EntityNotFoundException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        } catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }
    
    public function getUsersAndSubscriptionsAmountListAction()
    {
        try{
            $usersList = $this->userService->getUsersAndSubscriptionsAmountList();
            FrontController::getInstance()->setBody(json_encode($usersList));
        }catch (EntityNotFoundException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        } catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function getTeachersWithMaxCoursesAmountAction(){
        try{
            $teachers = $this->userService->getTeachersWithMaxCoursesAmount();
            FrontController::getInstance()->setBody(json_encode($teachers));
        }catch (EntityNotFoundException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        } catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function getStudentsWithNonePassedTestsAction(){
        try{
            $studentsList = $this->userService->getStudentsWithNonePassedTests();
            FrontController::getInstance()->setBody(json_encode($studentsList));
        }catch (EntityNotFoundException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        } catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }

    public function getTeachersWithNoneCreatedCoursesAction(){
        try{
            $teachersList = $this->userService->getTeachersWithNoneCreatedCourses();
            FrontController::getInstance()->setBody(json_encode($teachersList));
        }catch (EntityNotFoundException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", $e->getMessage());
        } catch (PDOException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        } catch (StatementExecutionException $e) {
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }
}