<?php

namespace App;

use App\Context\ApplicationContext;
use App\Entity\Instructor;
use App\Entity\Learner;
use App\Entity\Lesson;
use App\Entity\Template;
use App\Repository\InstructorRepository;
use App\Repository\LessonRepository;
use App\Repository\MeetingPointRepository;
use Exception;

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        try {
            $this->isLessonDataDefined($data);

            $lessonData = $data['lesson'];

            $lesson = LessonRepository::getInstance()->getById($lessonData->id);
            $meetingPoint = MeetingPointRepository::getInstance()->getById($lessonData->meetingPointId);
            $instructorOfLesson = InstructorRepository::getInstance()->getById($lessonData->instructorId);

            if (strpos($text, '[lesson:instructor_link]') !== false) {
                $text = str_replace('[instructor_link]', 'instructors/' . $instructorOfLesson->id . '-' . urlencode($instructorOfLesson->firstname), $text);
            }

            $containsSummaryHtml = strpos($text, '[lesson:summary_html]');
            $containsSummary = strpos($text, '[lesson:summary]');

            if ($containsSummaryHtml !== false) {
                $text = str_replace(
                    '[lesson:summary_html]',
                    Lesson::renderHtml($lesson),
                    $text
                );
            }
            if ($containsSummary !== false) {
                $text = str_replace(
                    '[lesson:summary]',
                    Lesson::renderText($lesson),
                    $text
                );
            }

            (strpos($text, '[lesson:instructor_name]') !== false) and $text = str_replace('[lesson:instructor_name]', $instructorOfLesson->firstname, $text);


            if ($lesson->meetingPointId) {
                if (strpos($text, '[lesson:meeting_point]') !== false)
                    $text = str_replace('[lesson:meeting_point]', $meetingPoint->name, $text);
            }

            if (strpos($text, '[lesson:start_date]') !== false)
                $text = str_replace('[lesson:start_date]', $lessonData->start_time->format('d/m/Y'), $text);

            if (strpos($text, '[lesson:start_time]') !== false)
                $text = str_replace('[lesson:start_time]', $lessonData->start_time->format('H:i'), $text);

            if (strpos($text, '[lesson:end_time]') !== false)
                $text = str_replace('[lesson:end_time]', $lessonData->end_time->format('H:i'), $text);


            if (isset($data['instructor']) and ($data['instructor'] instanceof Instructor))
                $text = str_replace('[instructor_link]', 'instructors/' . $data['instructor']->id . '-' . urlencode($data['instructor']->firstname), $text);
            else
                $text = str_replace('[instructor_link]', '', $text);

            /*
             * USER
             * [user:*]
             */
            $_user = (isset($data['user']) and ($data['user'] instanceof Learner)) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser();
            if ($_user) {
                (strpos($text, '[user:first_name]') !== false) and $text = str_replace('[user:first_name]', ucfirst(strtolower($_user->firstname)), $text);
            }

        } catch (Exception $exception) {

        }



        return $text;
    }

    /**
     * @throws Exception
     */
    private function isLessonDataDefined($data)
    {
        if (!(isset($data['lesson']) and $data['lesson'] instanceof Lesson)) {
            throw new Exception('Lesson not found in given data');
        }
    }

    private function replaceText(){

    }
}
