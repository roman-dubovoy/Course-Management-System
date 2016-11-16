<?php
class LessonService{
    private static $instance = null;
    private $lessonModel;
    private $lectureModel;
    private $courseModel;
    private $testService;

    protected function __construct(LessonModel $lessonModel, LectureModel $lectureModel,
                                   TestService $testService, CourseModel $courseModel)
    {
        $this->lessonModel = $lessonModel;
        $this->lectureModel = $lectureModel;
        $this->courseModel = $courseModel;
        $this->testService = $testService;
    }
    
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self(LessonModel::getInstance(), LectureModel::getInstance(),
                TestService::getInstance(), CourseModel::getInstance());
        }
        return self::$instance;
    }

    public function addLesson(array $data){
        $lessonData = [
            'title' => $data['title'],
            'date' => time(),
            'id_course' => $data['id_course']
        ];
        /*
        if ($this->lessonModel->getLessonIdByTitle(array('title' => $data['title'], 'id_course' => $data['id_course']))) {
            throw new EntityAlreadyExistsException("Lesson {$data['title']} already exists in course with id: {$data['id_course']}.");
        }
        */
        $id_lesson = $this->lessonModel->addLesson($lessonData);
        if (!empty($id_lesson)) {
            $data['lecture']['date'] = time();
            $data['lecture']['id_lesson'] = $id_lesson;
            $this->lectureModel->addLecture($data['lecture']);
            if (!empty($data['test'])) {
                $data['test']['id_lesson'] = $id_lesson;
                $this->testService->addTest($data['test']);
            }
        }
    }

    public function getLessonsList($id_course){
        if ($this->courseModel->isCourseCreated($id_course)) {
            $lessonsList = $this->lessonModel->getLessonsListByCourseId($id_course);
            if (!empty($lessonsList)) {
                for ($i = 0; $i < count($lessonsList); $i++){
                    $lecture = $this->lectureModel->getLectureByLessonId($lessonsList[$i]['id_lesson']);
                    if (empty($lecture)) {
                        throw new EmptyEntityException("Lesson with id: {$lessonsList[$i]['id_lesson']} does not contain any lecture - lesson is empty.");
                    }
                    $lessonsList[$i]['lecture'] = $lecture;
                }
                return $lessonsList;
            } else {
                throw new EntityNotFoundException("LessonList by id_course: {$id_course} was not found.");
            }
        } else {
            throw new EntityNotFoundException("Course with id: {$id_course} does not exists.");
        }
    }
    
    public function getLesson($id_lesson){
        $lesson = $this->lessonModel->getLessonById($id_lesson);
        if (!empty($lesson)){
            $lecture = $this->lectureModel->getLectureByLessonId($lesson['id_lesson']);
            if (empty($lecture)) {
                throw new EntityNotFoundException("Lesson with id: {$lesson['id_lesson']} does not contain any lecture - lesson is empty.");
            }
            $lesson['lecture'] = $lecture;
            try {
                $test = $this->testService->getTest($lesson['id_lesson']);
                $mark = $test['mark'];
                unset($test['mark']);
                $lesson['mark'] = $mark;
                $lesson['id_test'] = $test['id_test'];
                $i = 0;
                foreach ($test['questions'] as $question) {
                    $lesson['test'][$i]['question'] = $question['question'];
                    foreach ($question['answers'] as $answer) {
                        if ($answer['is_correct'] == 1) {
                            $lesson['test'][$i]['id_correct_answer'] = $answer['id_answer'];
                            $lesson['test'][$i]['correct_answer'] = $answer['answer'];
                        } else {
                            $lesson['test'][$i]['id_incorrect_answers'][] = $answer['id_answer'];
                            $lesson['test'][$i]['incorrect_answers'][] = $answer['answer'];
                        }
                    }
                    $i++;
                }
            }catch (EntityNotFoundException $e){
                $lesson['test'] = '';
            }
            return $lesson;
        }
        else{
            throw new EntityNotFoundException("Lesson with id: {$id_lesson} does not exist.");
        }
    }
    
    public function deleteLesson($id_lesson){
        if ($this->lessonModel->isLessonCreated($id_lesson)) {
            $this->lessonModel->deleteLesson($id_lesson);
        }else{
            throw new EntityNotFoundException("Lesson with id: " . $id_lesson . "does not exists.");
        }
    }

    public function updateLesson(array $data){
        if ($this->lessonModel->isLessonCreated($data['id_lesson'])) {
            if (!$this->lessonModel->getLessonIdByTitle($data['title'])) {
                $this->lessonModel->updateLesson($data);
            }
            else{
                throw new EntityAlreadyExistsException("Lesson with title: {$data['title']} already exists.");
            }
        }
        else{
            throw new EntityNotFoundException("Lesson with id: {$data['id_lesson']} does not exist");
        }
    }
}