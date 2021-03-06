<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 6/20/14
 * Time: 3:21 PM
 */

class Answer {

    const DELETED=1, ACTIVE=0;
    const CORRECT=1, WRONG=0;
    private $sqlDataBase;
    private $answerId;
    private $answerText;
    private $isCorrect;
    private $answerOrder;
    private $answerStatus;
    private $questionId;

    public function __construct(PDO $sqlDataBase)
    {
        $this->sqlDataBase = $sqlDataBase;
        $this->answerId = 0;
    }

    public function __destruct()
    {

    }

    /**
     * Load Answer into object
     * @param $answerId
     */
    public function LoadAnswer($answerId)
    {
        $queryAnswer = "SELECT * FROM answer WHERE answer_id=:answer_id";
        $answer = $this->sqlDataBase->prepare($queryAnswer);
        $answer->execute(array(':answer_id'=>$answerId));
        $answerArr = $answer->fetch(PDO::FETCH_ASSOC);

        $this->answerId = $answerId;
        $this->answerText= $answerArr['answer_text'];
        $this->isCorrect = $answerArr['correct_answer'];
        $this->questionId = $answerArr['question_id'];
        $this->answerOrder = $answerArr['order_num'];
        $this->answerStatus = $answerArr['status'];

    }

    /**Create a new answer and load it into this object
     * @param $answerText
     * @param $isCorrectOption
     * @param $questionId
     */
    public function CreateAnswer($answerText,$isCorrectOption,$questionId)
    {
        $orderNum = $this->GetLastAnswerNum($questionId)+1;
        $queryInsertAnswer = "INSERT INTO answer (answer_text, correct_answer,question_id,order_num,status)VALUES(:answer_text,:correct_answer,:question_id,:order_num,:status)";
        $insertAnswer = $this->sqlDataBase->prepare($queryInsertAnswer);
        $insertAnswer->execute(array(':answer_text'=>$answerText,':correct_answer'=>$isCorrectOption,':question_id'=>$questionId,':order_num'=>$orderNum,':status'=>Answer::ACTIVE));
        $this->answerId = $this->sqlDataBase->lastInsertId();
        if($this->answerId)
        {
            $this->answerText = $answerText;
            $this->isCorrect = $isCorrectOption;
            $this->questionId = $questionId;
        }
    }

    /**
     * Update answer after setting setters
     */
    public function UpdateAnswer()
    {
        $queryUpdateAnswer = "UPDATE answer SET answer_text=:answer_text, correct_answer=:correct_answer, order_num=:order_num, status=:status WHERE answer_id=:answer_id";
        $updateAnswer = $this->sqlDataBase->prepare($queryUpdateAnswer);
        $updateAnswer->execute(array(':answer_text'=>$this->answerText,':correct_answer'=>$this->isCorrect,':answer_id'=>$this->answerId,':order_num'=>$this->answerOrder,':status'=>$this->answerStatus));
    }

    /**Get the last answer number
     * @param $questionId
     * @return mixed
     */
    private function GetLastAnswerNum($questionId)
    {
        $queryLastOrderNum = "SELECT order_num FROM answer WHERE question_id=:question_id AND status=:status ORDER BY order_num DESC LIMIT 1";
        $lastOrderNum = $this->sqlDataBase->prepare($queryLastOrderNum);
        $lastOrderNum->execute(array(':question_id'=>$questionId,':status'=>Answer::ACTIVE));
        $lastOrderNumArr = $lastOrderNum->fetch(PDO::FETCH_ASSOC);

        return $lastOrderNumArr['order_num'];
    }

    /**Updates the order of the question
     * shifts all of the questions up or down depending on where the question was inserted
     * @param $newOrderNum
     */
    public function SetAnswerOrder($newOrderNum)
    {
        if($newOrderNum != $this->answerOrder && $newOrderNum <= $this->GetLastAnswerNum($this->questionId))
        {
            if($newOrderNum > $this->answerOrder)
            {
                //Shift answer stack down
                $queryShiftStack = "UPDATE answer SET order_num=order_num-1 WHERE order_num <= :new_order_num AND order_num > :old_order_num AND question_id=:question_id AND status=:status";
            }
            elseif($newOrderNum < $this->answerOrder)
            {
                //Shift answer stack up
                $queryShiftStack = "UPDATE answer SET order_num=order_num+1 WHERE order_num >= :new_order_num AND order_num < :old_order_num AND question_id=:question_id AND status=:status";
            }

            $shiftDownStack = $this->sqlDataBase->prepare($queryShiftStack);
            $shiftDownStack->execute(array(':new_order_num'=>$newOrderNum, ':old_order_num'=>$this->answerOrder,':question_id'=>$this->questionId, ':status'=>Answer::ACTIVE));
            $this->answerOrder = $newOrderNum;
            $this->UpdateAnswer();
        }
    }

    //Getters and setters
    /**
     * @param mixed $answerId
     */
    public function setAnswerId($answerId)
    {
        $this->answerId = $answerId;
    }

    /**
     * @return mixed
     */
    public function getAnswerId()
    {
        return $this->answerId;
    }

    /**
     * @param mixed $answerText
     */
    public function setAnswerText($answerText)
    {
        $this->answerText = $answerText;
    }

    /**
     * @return mixed
     */
    public function getAnswerText()
    {
        return $this->answerText;
    }

    /**
     * @param mixed $isCorrect
     */
    public function setIsCorrect($isCorrect)
    {
        $this->isCorrect = $isCorrect;
    }

    /**
     * @return mixed
     */
    public function getIsCorrect()
    {
        return $this->isCorrect;
    }

    /**
     * @param mixed $orderNum
     */
    public function setOrderNum($orderNum)
    {
        $this->answerOrder = $orderNum;
    }

    /**
     * @return mixed
     */
    public function getAnswerOrder()
    {
        return $this->answerOrder;
    }

    /**
     * @param mixed $answerStatus
     */
    public function setAnswerStatus($answerStatus)
    {
        $this->answerStatus = $answerStatus;
    }

    /**
     * @return mixed
     */
    public function getAnswerStatus()
    {
        return $this->answerStatus;
    }

} 