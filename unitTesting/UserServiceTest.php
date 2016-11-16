<?php
require $_SERVER['DOCUMENT_ROOT'] . "/protected/models/UserModel.php";
require $_SERVER['DOCUMENT_ROOT'] . "/protected/models/CourseModel.php";

class UserServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var UserModel
     */
    private $userModel;
    /**
     * @var CourseModel
     */
    private $courseModel;

    public function setUp()
    {
        $this->userModel = $this->getMock('UserModel', array('isRegistered', 'addUser', 'getUserByEmailPassword',
            'getUserById', 'subscribeOnCourse', 'unsubscribeFromCourse', 'isSubscribed'),
            array(), '', false);
        $this->assertInstanceOf('UserModel', $this->userModel);
        $this->userModel->method('isRegistered')->willReturn(false);
        $this->userModel->method('getUserByEmailPassword')
            ->willReturn(array(
                'id_u' => "1",
                'name' => "Roman",
                "password" => "65e84be33532fb784c48129675f9eff3a682b27168c0ea744b2cf58ee02337c5",
                'email' => "roman@ghmail.com",
                'register_date' => "123456123456",
                'role' => "lecturer"
            ));

        $this->courseModel = $this->getMock('CourseModel', array(), array(), '', false);
    }

    /**
     * @param $userData
     * @throws Exception
     * @dataProvider registrationProvider
     */
    public function testRegisterUser($userData)
    {
        $isUserRegistered = $this->userModel->isRegistered($userData['email']);
        $this->assertEquals(false, $isUserRegistered);
        if ($isUserRegistered) {
            throw new Exception("User {$userData['email']}:{$userData['password']} already exists");
        }
        $this->userModel->addUser($userData);
    }

    /**
     * @param array $data
     * @return mixed
     * @throws Exception
     * @dataProvider authProvider
     */
    public function testAuthUser(array $data)
    {
        $userInfo = $this->userModel->getUserByEmailPassword($data);
        $this->assertEquals(array(
            'id_u' => "1",
            'name' => "Roman",
            "password" => "65e84be33532fb784c48129675f9eff3a682b27168c0ea744b2cf58ee02337c5",
            'email' => "roman@ghmail.com",
            'register_date' => "123456123456",
            'role' => "lecturer"
        ), $userInfo);
        if (empty($userInfo)) {
            throw new Exception("No such user or password is not correct!");
        } else {
            return $userInfo;
        }
    }

    public function testSubscribeOnCourse()
    {

    }

    public function testUnsubscribeFromCourse()
    {

    }

    public function registrationProvider()
    {
        return array(
            [array('name' => "Roman", 'email' => "roman@gmail.com", 'password' => "qwerty1", 'role' => "lecturer")]
        );
    }

    public function authProvider()
    {
        return array(
            [array(
                "password" => "65e84be33532fb784c48129675f9eff3a682b27168c0ea744b2cf58ee02337c5",
                'email' => "roman@ghmail.com")
            ]
        );
    }
}
