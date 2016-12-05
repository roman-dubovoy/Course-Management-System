<?php
class CourseModel extends Model{
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

    /**
     * Adds new course in DB
     * @param array $data
     * @throws StatementExecutionException
     */
    public function addCourse(array $data){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "INSERT INTO courses(title, description, date, id_auth, id_category) VALUES(:title, :description, :date, :id_auth, :id_category)";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function getCoursesCategoriesList(){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT * FROM courses_categories";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $coursesCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $coursesCategories;
    }

    /**
     * Checks if course with such title exists in DB. It's needed because title is unique.
     * @param $title
     * @return bool
     * @throws StatementExecutionException
     */
    public function isCourseWithTitleExists($title){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_course FROM courses WHERE title = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $title, PDO::PARAM_STR);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($course['id_course']);
    }

    /**
     * Checks if course with such id exists in DB.
     * @param $id_course
     * @return bool
     * @throws StatementExecutionException
     */
    public function isCourseCreated($id_course){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_course FROM courses WHERE id_course = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_course, PDO::PARAM_STR);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($course['id_course']);
    }

    /**
     * Gets course which title equal to pointed title.
     * @param $title
     * @return mixed
     * @throws StatementExecutionException
     */
    public function getCourseByTitle($title){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT * FROM courses WHERE title = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $title, PDO::PARAM_STR);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        return $course;
    }

    /**
     * Gets list of courses of definite lecturer by his email.
     * @param $email_lecturer
     * @return array
     * @throws StatementExecutionException
     */
    public function getCoursesListByLecturerEmail($email_lecturer){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_course, title, description, id_category 
                FROM courses 
                WHERE id_auth = (SELECT id_u FROM users WHERE email = ?)
                ORDER BY title ASC";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $email_lecturer, PDO::PARAM_STR);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $courses;
    }

    /**
     * Gets list of courses which user subscribed on.
     * @param $id_user
     * @return array
     * @throws StatementExecutionException
     */
    public function getCoursesListByUserSubscription($id_user){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT * FROM courses LEFT JOIN subscriptions 
                ON courses.id_course = subscriptions.id_course
                WHERE id_u = ?
                ORDER BY title ASC";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_user, PDO::PARAM_INT);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $courses;
    }

    /**
     * Get all existing courses from DB.
     * @return array
     */
    public function getAllCoursesList(){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT * FROM courses";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $allCoursesList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $allCoursesList;
    }

    public function getCoursesListByPeriod(array $data){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_course, title, description, courses.date AS creation_date, users.name AS author_name, 
                       users.email AS author_email, courses_categories.name AS course_category
                FROM courses INNER JOIN users INNER JOIN courses_categories
                ON courses.id_auth = users.id_u AND courses.id_category = courses_categories.id_category
                WHERE courses.date BETWEEN :start_date AND :end_date";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $coursesList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $coursesList;
    }

    public function getCoursesListForTeacher($id_user){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT courses.id_course, title, description, 
                       courses_categories.name AS course_category, courses.date AS creation_date
                FROM courses 
                INNER JOIN subscriptions
                INNER JOIN users
                INNER JOIN courses_categories
                ON courses.id_course = subscriptions.id_course
                AND subscriptions.id_u = users.id_u
                AND courses.id_category = courses_categories.id_category
                WHERE id_auth <> ? AND role = 'lecturer'";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_user, PDO::PARAM_INT);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $coursesListForTeacher = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $coursesListForTeacher;
    }

    public function getCoursesAmountForLastWeek(){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT COUNT(id_course) AS courses_amount FROM courses 
                WHERE DATE_FORMAT(FROM_UNIXTIME(date), '%u') = WEEK(NOW()) - 1";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($link->errorInfo());
        $amount = $stmt->fetch(PDO::FETCH_ASSOC)['courses_amount'];
        return $amount;
    }

    /**
     * In each category finds the course which was created earlier then all others.
     * Returns the list of such courses.
     */
    public function getOldestCoursesListByCategories(){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT title, name AS category, date
                FROM courses INNER JOIN courses_categories
                ON courses.id_category = courses_categories.id_category
                GROUP BY courses.id_category
                HAVING date <= ALL(SELECT date
                                   FROM courses temp_courses
                                   WHERE temp_courses.id_category = courses.id_category)";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $coursesList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $coursesList;
    }

    /**
     * Deletes course from DB by it id.
     * @param $title
     * @throws StatementExecutionException
     */
    public function deleteCourse($title){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "DELETE FROM courses WHERE title = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $title, PDO::PARAM_STR);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    /**
     * Updates course in DB.
     * @param array $data
     * @throws StatementExecutionException
     */
    public function updateCourse(array $data){
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "UPDATE courses 
                SET title = :title, description = :description, id_category = :id_category 
                WHERE id_course = :id_course";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function getCoursesListByTitleFilter($titleFilter)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $titleFilter = $titleFilter . '%';
        $sql = "SELECT id_course, title, description, date, courses_categories.name AS category
                FROM courses
                INNER JOIN courses_categories
                ON courses.id_category = courses_categories.id_category
                WHERE title LIKE ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $titleFilter, PDO::PARAM_STR);
        $stmt->execute();
        CourseModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $coursesList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $coursesList;
    }
}