<?php

namespace App;

use App\Context\ApplicationContext;
use App\Entity\Instructor;
use App\Entity\Learner;
use App\Entity\Lesson;
use App\Entity\MeetingPoint;
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

        try {
            $this->isLessonDataDefined($data);

            $lessonData = $data['lesson'];

            $lesson = LessonRepository::getInstance()->getById($lessonData->id);
            $meetingPoint = MeetingPointRepository::getInstance()->getById($lesson->meetingPointId);
            $instructorOfLesson = InstructorRepository::getInstance()->getById($lesson->instructorId);

            $text = $this->replaceInstructorInfos($instructorOfLesson, $text);

            $text = $this->replaceLessonSummary($lesson, $text);

            $text = $this->replaceMeetingInfo($meetingPoint, $lesson, $text);

            $text = $this->replaceUserInfo($data, $text);

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

    private function replaceInstructorInfos(Instructor $instructorOfLesson, $text)
    {
        $text = $this->updateText('[lesson:instructor_link]', 'instructors/' . $instructorOfLesson->id . '-' . urlencode($instructorOfLesson->firstname), $text);
        $text = $this->updateText('[lesson:instructor_name]', $instructorOfLesson->firstname, $text);
        if (isset($data['instructor']) and ($data['instructor'] instanceof Instructor))
            $text = $this->updateText('[instructor_link]', 'instructors/' . $data['instructor']->id . '-' . urlencode($data['instructor']->firstname), $text);
        else
            $text = $this->updateText('[instructor_link]', '', $text);

        return $text;
    }

    private function replaceLessonSummary(Lesson $lesson, $text)
    {
        $text = $this->updateText('[lesson:summary_html]', Lesson::renderHtml($lesson), $text);
        return $this->updateText('[lesson:summary]', Lesson::renderText($lesson), $text);
    }

    private function replaceMeetingInfo(MeetingPoint $meetingPoint, Lesson $lesson, $text)
    {
        $text = $this->updateText('[lesson:meeting_point]', $meetingPoint->name, $text);

        $text = $this->updateText('[lesson:start_date]', $lesson->start_time->format('d/m/Y'), $text);
        $text = $this->updateText('[lesson:start_time]', $lesson->start_time->format('H:i'), $text);
        return $this->updateText('[lesson:end_time]', $lesson->end_time->format('H:i'), $text);
    }

    private function replaceUserInfo($data, $text)
    {
        $user = $this->getUser($data);
        return $this->updateText('[user:first_name]', ucfirst(strtolower($user->firstname)), $text);
    }

    private function getUser($data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        return $this->checkIfUserDefinedInData($data) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser();
    }

    private function checkIfUserDefinedInData($data): bool
    {
        return isset($data['user']) and ($data['user'] instanceof Learner);
    }
}
