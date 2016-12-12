<?php
include $_SERVER['DOCUMENT_ROOT'] . "/assets/settings.php";

class UserModel extends Model
{
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

    public function addUser(array $data)
    {
        $connection = PDOConnection::getInstance()->getConnection();
        $sql = "INSERT INTO users(name, password, email, register_date, role)
                VALUES(:name, :password, :email, :register_date, :role)";
        $stmt = $connection->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function isRegistered($email)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_u 
                FROM users
                WHERE email = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($user['id_u']);
    }

    public function getUserByEmailPassword(array $data)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_u, name, password, email, register_date, role FROM users 
                WHERE email = :email AND password = :password";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user;
    }

    public function getUserById($id)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_u, name, password, email, register_date, role 
                FROM users 
                WHERE id_u = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user;
    }

    public function subscribeOnCourse(array $data)
    {
        $connection = PDOConnection::getInstance()->getConnection();
        $sql = "INSERT INTO subscriptions(id_u, id_course, date)
                VALUES(:id_u, :id_course, :date)";
        $stmt = $connection->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function unsubscribeFromCourse(array $data)
    {
        $connection = PDOConnection::getInstance()->getConnection();
        $sql = "DELETE FROM subscriptions 
                WHERE id_u = :id_u AND id_course = :id_course";
        $stmt = $connection->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function isSubscribed(array $data)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_sub, id_u, id_course, date 
                FROM subscriptions 
                WHERE id_u = :id_u AND id_course = :id_course";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($subscription);
    }

    public function getSubscribedUsersListByPeriod(array $data)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT users.id_u, name, email, role, subscriptions.date AS subscription_date, title AS course_title
                FROM users 
                INNER JOIN subscriptions
                INNER JOIN courses
                ON users.id_u = subscriptions.id_u
                AND subscriptions.id_course = courses.id_course
                WHERE date BETWEEN :start_date AND :end_date";
        $stmt = $link->prepare($sql);
        $stmt->execute($data);
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $subscribedUsersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $subscribedUsersList;
    }

    public function getBestStudentsList()
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT DISTINCT users.id_u, name, email, medium_mark
                FROM users
                INNER JOIN (SELECT DISTINCT id_user, AVG(result) AS medium_mark
                            FROM results
                            GROUP BY id_user
                            ORDER BY medium_mark DESC
                            LIMIT 10) user_result  
                INNER JOIN subscriptions
                ON users.id_u = user_result.id_user 
                AND users.id_u = subscriptions.id_u
                ORDER BY medium_mark DESC";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $bestStudentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $bestStudentsList;
    }

    public function getUsersListByNameFilter($name_filter)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $name_filter = $name_filter . "%";
        $sql = "SELECT id_u, name, email, register_date, role 
                FROM users 
                WHERE name LIKE ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $name_filter, PDO::PARAM_STR);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $usersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $usersList;
    }

    public function deleteUser($id_user)
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "DELETE FROM users 
                WHERE id_u = ?";
        $stmt = $link->prepare($sql);
        $stmt->bindParam(1, $id_user, PDO::PARAM_INT);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
    }

    public function getOldestUsersListByRoles()
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT name, email, register_date, role FROM users 
                GROUP BY role
                HAVING register_date <= ALL (SELECT register_date 
                                             FROM users temp_users
                                             WHERE temp_users.role = users.role)";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $usersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $usersList;
    }

    public function getUsersAmountSubscribedForLastMonth()
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT COUNT(id_sub)
                FROM subscriptions
                WHERE DATE_FORMAT(FROM_UNIXTIME(date), '%m') = MONTH(NOW()) - 1";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $subscriptionAmount = $stmt->fetch(PDO::FETCH_NUM);
        return $subscriptionAmount;
    }

    public function getUsersAndPassedTestsAmountList()
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_u, name, email, COUNT(id_result) AS passed_tests_amount
                FROM users
                INNER JOIN results
                ON users.id_u = results.id_user
                GROUP BY results.id_user";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $userList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $userList;
    }

    public function getUsersAndSubscriptionsAmountList()
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT users.id_u, name, email, COUNT(id_sub) AS subscriptions_amount
                FROM users
                INNER JOIN subscriptions
                ON users.id_u = subscriptions.id_u
                GROUP BY subscriptions.id_u";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $usersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $usersList;
    }

    public function getTeachersWithMaxCoursesAmount()
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT id_u, name, email, COUNT(id_course) AS courses_amount
                FROM users
                INNER JOIN courses
                ON users.id_u = courses.id_auth
                GROUP BY courses.id_auth
                HAVING COUNT(id_course) >= ALL( SELECT COUNT(id_course)
                                                FROM courses
                                                GROUP BY id_auth )";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $teacher = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $teacher;
    }

    public function getStudentsWithNonePassedTests()
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT users.id_u, users.name, users.email
                FROM users
                LEFT JOIN (
                    SELECT id_u, name, email
                    FROM users
                    INNER JOIN results
                    ON users.id_u = results.id_user
                ) AS student_with_passed_tests
                ON users.id_u = student_with_passed_tests.id_u
                WHERE student_with_passed_tests.id_u IS NULL AND role = 'student'";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $studentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $studentsList;
    }

    public function getTeachersWithNoneCreatedCoursesByWeek()
    {
        $link = PDOConnection::getInstance()->getConnection();
        $sql = "SELECT users.id_u, users.name, users.email
                FROM users
                LEFT JOIN (
                    SELECT id_u, name, email
                    FROM users
                    INNER JOIN courses
                    ON users.id_u = courses.id_auth
                    WHERE DATE_FORMAT(FROM_UNIXTIME(courses.date), '%u') = WEEK(NOW()) - 1
                ) AS teachers_with_created_courses
                ON users.id_u = teachers_with_created_courses.id_u
                WHERE teachers_with_created_courses.id_u IS NULL AND role = 'lecturer'";
        $stmt = $link->prepare($sql);
        $stmt->execute();
        UserModel::checkErrorArrayEmptiness($stmt->errorInfo());
        $teachersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $teachersList;
    }
}