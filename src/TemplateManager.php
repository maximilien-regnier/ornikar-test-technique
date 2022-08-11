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

            $text = $this->updateText('[lesson:instructor_link]', 'instructors/' . $instructorOfLesson->id . '-' . urlencode($instructorOfLesson->firstname), $text);

            $text = $this->updateText('[lesson:summary_html]', Lesson::renderHtml($lesson), $text);

            $text = $this->updateText('[lesson:summary]', Lesson::renderText($lesson), $text);

            $text = $this->updateText('[lesson:instructor_name]', $instructorOfLesson->firstname, $text);

            if ($meetingPoint) {
                $text = $this->updateText('[lesson:meeting_point]', $meetingPoint->name, $text);
            }


            $text = $this->updateText('[lesson:start_date]', $lessonData->start_time->format('d/m/Y'), $text);
            $text = $this->updateText('[lesson:start_time]', $lessonData->start_time->format('H:i'), $text);
            $text = $this->updateText('[lesson:end_time]', $lessonData->end_time->format('H:i'), $text);


            if (isset($data['instructor']) and ($data['instructor'] instanceof Instructor))
                $text = $this->updateText('[instructor_link]', 'instructors/' . $data['instructor']->id . '-' . urlencode($data['instructor']->firstname), $text);
            else
                $text = $this->updateText('[instructor_link]', '', $text);

            /*
             * USER
             * [user:*]
             */
            $_user = (isset($data['user']) and ($data['user'] instanceof Learner)) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser();
            if ($_user) {
                $text = $this->updateText('[user:first_name]', ucfirst(strtolower($_user->firstname)), $text);

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

    private function updateText($textToReplace, $replacementText, $text)
    {
        if ($this->checkTextToReplaceExist($textToReplace, $text)) {
            return str_replace($textToReplace, $replacementText, $text);
        } else {
            return $text;
        }
    }

    private function checkTextToReplaceExist($textToReplace, $text): bool
    {
        return strpos($text, $textToReplace) !== false;
    }

}
