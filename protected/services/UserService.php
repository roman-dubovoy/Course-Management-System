<?php
class UserService{
    private static $instance = null;
    private $userModel;
    private $courseModel;

    protected function __construct(UserModel $userModel, CourseModel $courseModel)
    {
        $this->userModel = $userModel;
        $this->courseModel = $courseModel;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self(UserModel::getInstance(), CourseModel::getInstance());
        }
        return self::$instance;
    }

    public function registerUser(array $data){
        if ($this->userModel->isRegistered($data['email'])) {
            throw new UserExistsException("User {$data['email']}:{$data['password']} already exists");
        }
        $this->userModel->addUser($data);
            mail($data['email'], "Course Management System",
                "Congratulations, " . $data['name'] . "!\nYou've been successfully registered in Course Management System.\nWith best regards, CMS Team.");
    }

    public function authUser(array $data){
        $userInfo = $this->userModel->getUserByEmailPassword($data);
        if (empty($userInfo)){
            throw new AuthorizationException("No such user or password is not correct!");
        }
        else
            return $userInfo;
    }

    public function subscribeOnCourse(array $data)
    {
        if ($this->userModel->getUserById($data['id_u'])){
            if ($this->courseModel->isCourseCreated($data['id_course'])){
                $this->userModel->subscribeOnCourse($data);
            }
            else
                throw new EntityNotFoundException("Course with id: {$data['id_course']} was not found.");
        }
        else
            throw new EntityNotFoundException("User with id: {$data['id_u']} was not found.");
    }

    public function unsubscribeFromCourse(array $data)
    {
        if ($this->userModel->getUserById($data['id_u'])) {
            if ($this->courseModel->isCourseCreated($data['id_course'])) {
                $this->userModel->unsubscribeFromCourse($data);
            } else
                throw new EntityNotFoundException("Course with id: {$data['id_course']} was not found.");
        } else
            throw new EntityNotFoundException("User with id: {$data['id_u']} was not found.");
    }

    public function getSubscribedUsersListByPeriod(array $data){
        $subscribedUsersList = $this->userModel->getSubscribedUsersListByPeriod($data);
        if (!empty($subscribedUsersList)){
            return $subscribedUsersList;
        }
        else
            throw new EntityNotFoundException("Users subscribed in specified period were not found.");
    }
    
    public function getBestStudentsList(){
        $bestStudentsList = $this->userModel->getBestStudentsList();
        if (!empty($bestStudentsList)){
            return $bestStudentsList;
        }
        else
            throw new EntityNotFoundException("Best students list was not found");
    }

    public function getUsersListByNameFilter($name_filter){
        $usersList = $this->userModel->getUsersListByNameFilter($name_filter);
        if (!empty($usersList)){
            return $usersList;
        }
        else
            throw new EntityNotFoundException("No users with name starting with $name_filter...");
    }

    public function deleteUser($id_user)
    {
        if ($this->userModel->getUserById($id_user)) {
            $this->userModel->deleteUser($id_user);
        } else {
            throw new EntityNotFoundException("User with id: " . $id_user . "does not exists.");
        }
    }

    public function getOldestUsersListByRoles(){
        $usersList = $this->userModel->getOldestUsersListByRoles();
        if (!empty($usersList)){
            return $usersList;
        }
        else
            throw new EntityNotFoundException("Users were not found.");
    }

    public function getUsersAmountSubscribedForLastMonth()
    {
        $usersAmount = $this->userModel->getUsersAmountSubscribedForLastMonth();
        return $usersAmount;
    }

    public function getUsersAndPassedTestsAmountList()
    {
        $usersList = $this->userModel->getUsersAndPassedTestsAmountList();
        if (!empty($usersList)){
            return $usersList;
        }
        else
            throw new EntityNotFoundException("No users with passed tests.");
    }

    public function getUsersAndSubscriptionsAmountList()
    {
        $usersList = $this->userModel->getUsersAndSubscriptionsAmountList();
        if (!empty($usersList)){
            return $usersList;
        }
        else
            throw new EntityNotFoundException("No users subscribed on any course.");
    }
    
    public function getTeachersWithMaxCoursesAmount()
    {
        $teachers = $this->userModel->getTeachersWithMaxCoursesAmount();
        if (!empty($teachers)){
            return $teachers;
        }
        else
            throw new EntityNotFoundException("No teachers found.");
    }
    
    public function getStudentsWithNonePassedTests()
    {
        $studentsList = $this->userModel->getStudentsWithNonePassedTests();
        if (!empty($studentsList)){
            return $studentsList;
        }
        else
            throw new EntityNotFoundException("No students found.");
    }

    public function getTeachersWithNoneCreatedCourses()
    {
        $teachersList = $this->userModel->getTeachersWithNoneCreatedCoursesByWeek();
        if (!empty($teachersList)){
            return $teachersList;
        }
        else
            throw new EntityNotFoundException("No teachers found.");
    }
}