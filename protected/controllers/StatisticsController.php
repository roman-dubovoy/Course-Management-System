<?php
class StatisticsController{
    private $courseService;
    private $userService;

    public function __construct()
    {
        $this->courseService = CourseService::getInstance();
        $this->userService = UserService::getInstance();
    }

    public function getStatisticsAction()
    {
        try {
            $coursesAmount = $this->courseService->getCoursesAmountForLastWeek();
            if (!empty($coursesAmount)){
                $data['Amount of courses created for previous week'] = $coursesAmount[0];
            }
            $subscriptionsAmount = $this->userService->getUsersAmountSubscribedForLastMonth();
            if (!empty($subscriptionsAmount)){
                $data['Amount of subscriptions for previous month'] = $subscriptionsAmount[0];
            }
            if (empty($data)){
                HTTPResponseBuilder::getInstance()->sendFailRespond(404, "Not found", "Statistics data was not found.");
            }
            FrontController::getInstance()->setBody(json_encode($data));
            http_response_code(200);
        }
        catch (StatementExecutionException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
        catch (PDOException $e){
            HTTPResponseBuilder::getInstance()->sendFailRespond(500, "Internal error", $e->getMessage());
        }
    }
}