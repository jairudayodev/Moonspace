<?php

/**
 * class -> night
 * "Pergunta da Noite": uma pergunta por noite para todos;
 * as respostas dos outros só aparecem depois que você responde.
 *
 * @package Moonspace
 */

class Night
{
    /* a noite começa neste horário (fuso abaixo) e dura até o mesmo horário do dia seguinte */
    const START_HOUR = 20;
    const TIMEZONE = 'America/Sao_Paulo';
    const MAX_ANSWER_LENGTH = 500;
    const MAX_QUESTION_LENGTH = 255;
    const ANSWERS_LIMIT = 200;
    const ARCHIVE_LIMIT = 365;

    private $user;


    /**
     * __construct
     *
     * @param User $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }


    /**
     * current_night_date
     * Data (Y-m-d) da noite em andamento. Antes das 20h ainda vale a noite de ontem.
     *
     * @return string
     */
    public static function current_night_date()
    {
        $now = new DateTime('now', new DateTimeZone(self::TIMEZONE));
        if ((int) $now->format('G') < self::START_HOUR) {
            $now->modify('-1 day');
        }
        return $now->format('Y-m-d');
    }


    /**
     * format_night_date
     *
     * @param string $night_date
     * @return string
     */
    public static function format_night_date($night_date)
    {
        $date = DateTime::createFromFormat('Y-m-d', $night_date);
        return ($date) ? $date->format('d/m/Y') : $night_date;
    }


    /**
     * get_question
     * Pergunta da noite. Na primeira visita da noite, sorteia entre as menos usadas
     * (assim não precisa de tarefa agendada no servidor).
     *
     * @param string $night_date
     * @return array|null
     */
    public function get_question($night_date)
    {
        global $db;
        $question = $this->_fetch_question($night_date);
        if ($question || $night_date != self::current_night_date()) {
            return $question;
        }
        $get_pick = $db->query("SELECT q.question_id FROM night_questions q LEFT JOIN night_days d ON q.question_id = d.question_id GROUP BY q.question_id ORDER BY COUNT(d.night_date) ASC, RAND() LIMIT 1") or _error("SQL_ERROR_THROWEN");
        if ($get_pick->num_rows == 0) {
            return null;
        }
        $question_id = $get_pick->fetch_assoc()['question_id'];
        /* INSERT IGNORE: se duas pessoas abrirem ao mesmo tempo, vale a primeira */
        $db->query(sprintf("INSERT IGNORE INTO night_days (night_date, question_id) VALUES (%s, %s)", secure($night_date), secure($question_id, 'int'))) or _error("SQL_ERROR_THROWEN");
        return $this->_fetch_question($night_date);
    }


    /**
     * get_my_answer
     *
     * @param string $night_date
     * @return array|null
     */
    public function get_my_answer($night_date)
    {
        global $db;
        $get_answer = $db->query(sprintf("SELECT answer_id, answer, created_at FROM night_answers WHERE night_date = %s AND user_id = %s", secure($night_date), secure($this->user->_data['user_id'], 'int'))) or _error("SQL_ERROR_THROWEN");
        return ($get_answer->num_rows > 0) ? $get_answer->fetch_assoc() : null;
    }


    /**
     * count_answers
     *
     * @param string $night_date
     * @return integer
     */
    public function count_answers($night_date)
    {
        global $db;
        $get_count = $db->query(sprintf("SELECT COUNT(*) as count FROM night_answers a INNER JOIN users u ON a.user_id = u.user_id WHERE a.night_date = %s AND u.user_banned = '0'", secure($night_date))) or _error("SQL_ERROR_THROWEN");
        return (int) $get_count->fetch_assoc()['count'];
    }


    /**
     * get_answers
     * Respostas da noite, sem usuários banidos e sem quem bloqueou/foi bloqueado pelo visitante.
     *
     * @param string $night_date
     * @return array
     */
    public function get_answers($night_date)
    {
        global $db, $system;
        $where_blocks = "";
        if (!$this->_is_staff()) {
            $viewer_id = secure($this->user->_data['user_id'], 'int');
            $where_blocks = sprintf(' AND a.user_id NOT IN (SELECT blocked_id FROM users_blocks WHERE user_id = %1$s) AND a.user_id NOT IN (SELECT user_id FROM users_blocks WHERE blocked_id = %1$s)', $viewer_id);
        }
        $answers = [];
        $get_answers = $db->query(sprintf("SELECT a.answer_id, a.answer, a.created_at, u.user_id, u.user_name, u.user_firstname, u.user_lastname, u.user_gender, u.user_picture, u.user_verified FROM night_answers a INNER JOIN users u ON a.user_id = u.user_id WHERE a.night_date = %s AND u.user_banned = '0'%s ORDER BY a.created_at DESC LIMIT %s", secure($night_date), $where_blocks, self::ANSWERS_LIMIT)) or _error("SQL_ERROR_THROWEN");
        while ($answer = $get_answers->fetch_assoc()) {
            $answer['user_picture'] = get_picture($answer['user_picture'], $answer['user_gender']);
            $answer['name'] = ($system['show_usernames_enabled']) ? $answer['user_name'] : $answer['user_firstname'] . " " . $answer['user_lastname'];
            $answer['is_mine'] = ($answer['user_id'] == $this->user->_data['user_id']);
            $answers[] = $answer;
        }
        return $answers;
    }


    /**
     * add_answer
     *
     * @param string $answer
     * @return void
     */
    public function add_answer($answer)
    {
        global $db;
        $answer = trim((string) $answer);
        if (is_empty($answer)) {
            throw new Exception("Escreva a sua resposta antes de enviar.");
        }
        if (mb_strlen($answer, 'UTF-8') > self::MAX_ANSWER_LENGTH) {
            throw new Exception("A resposta pode ter no máximo " . self::MAX_ANSWER_LENGTH . " caracteres.");
        }
        $night_date = self::current_night_date();
        if (!$this->get_question($night_date)) {
            throw new Exception("Ainda não há pergunta para esta noite.");
        }
        $db->query(sprintf("INSERT IGNORE INTO night_answers (night_date, user_id, answer, created_at) VALUES (%s, %s, %s, %s)", secure($night_date), secure($this->user->_data['user_id'], 'int'), secure($answer), secure(gmdate('Y-m-d H:i:s')))) or _error("SQL_ERROR_THROWEN");
        if ($db->affected_rows == 0) {
            throw new Exception("Você já respondeu a pergunta desta noite.");
        }
    }


    /**
     * get_my_nights
     * Arquivo pessoal: tudo o que o usuário já respondeu.
     *
     * @return array
     */
    public function get_my_nights()
    {
        global $db;
        $nights = [];
        $get_nights = $db->query(sprintf("SELECT a.night_date, a.answer, a.created_at, q.question FROM night_answers a LEFT JOIN night_days d ON a.night_date = d.night_date LEFT JOIN night_questions q ON d.question_id = q.question_id WHERE a.user_id = %s ORDER BY a.night_date DESC LIMIT %s", secure($this->user->_data['user_id'], 'int'), self::ARCHIVE_LIMIT)) or _error("SQL_ERROR_THROWEN");
        while ($night = $get_nights->fetch_assoc()) {
            $night['night_label'] = self::format_night_date($night['night_date']);
            $nights[] = $night;
        }
        return $nights;
    }


    /**
     * get_questions (admin)
     *
     * @return array
     */
    public function get_questions()
    {
        global $db;
        $this->_check_admin();
        $questions = [];
        $get_questions = $db->query("SELECT q.question_id, q.question, COUNT(d.night_date) as times_used, MAX(d.night_date) as last_used FROM night_questions q LEFT JOIN night_days d ON q.question_id = d.question_id GROUP BY q.question_id, q.question ORDER BY times_used ASC, q.question_id DESC") or _error("SQL_ERROR_THROWEN");
        while ($question = $get_questions->fetch_assoc()) {
            $question['last_used_label'] = ($question['last_used']) ? self::format_night_date($question['last_used']) : null;
            $questions[] = $question;
        }
        return $questions;
    }


    /**
     * add_question (admin)
     *
     * @param string $question
     * @return void
     */
    public function add_question($question)
    {
        global $db;
        $this->_check_admin();
        $question = trim((string) $question);
        if (is_empty($question)) {
            throw new Exception("Escreva a pergunta.");
        }
        if (mb_strlen($question, 'UTF-8') > self::MAX_QUESTION_LENGTH) {
            throw new Exception("A pergunta pode ter no máximo " . self::MAX_QUESTION_LENGTH . " caracteres.");
        }
        $db->query(sprintf("INSERT INTO night_questions (question, created_at) VALUES (%s, %s)", secure($question), secure(gmdate('Y-m-d H:i:s')))) or _error("SQL_ERROR_THROWEN");
    }


    /**
     * delete_question (admin)
     * Só apaga perguntas que nunca foram usadas, para não esvaziar o arquivo de ninguém.
     *
     * @param integer $question_id
     * @return void
     */
    public function delete_question($question_id)
    {
        global $db;
        $this->_check_admin();
        $check = $db->query(sprintf("SELECT COUNT(*) as count FROM night_days WHERE question_id = %s", secure($question_id, 'int'))) or _error("SQL_ERROR_THROWEN");
        if ($check->fetch_assoc()['count'] > 0) {
            throw new Exception("Esta pergunta já foi usada e faz parte do histórico, por isso não pode ser apagada.");
        }
        $db->query(sprintf("DELETE FROM night_questions WHERE question_id = %s", secure($question_id, 'int'))) or _error("SQL_ERROR_THROWEN");
    }


    /**
     * delete_answer (admin ou moderador)
     *
     * @param integer $answer_id
     * @return void
     */
    public function delete_answer($answer_id)
    {
        global $db;
        if (!$this->_is_staff()) {
            throw new Exception(__("You don't have the permission to do this"));
        }
        $db->query(sprintf("DELETE FROM night_answers WHERE answer_id = %s", secure($answer_id, 'int'))) or _error("SQL_ERROR_THROWEN");
    }


    /**
     * is_staff
     *
     * @return boolean
     */
    public function is_staff()
    {
        return $this->_is_staff();
    }


    /* ------------------------------- */
    /* helpers */
    /* ------------------------------- */

    private function _fetch_question($night_date)
    {
        global $db;
        $get_question = $db->query(sprintf("SELECT q.question_id, q.question FROM night_days d INNER JOIN night_questions q ON d.question_id = q.question_id WHERE d.night_date = %s", secure($night_date))) or _error("SQL_ERROR_THROWEN");
        return ($get_question->num_rows > 0) ? $get_question->fetch_assoc() : null;
    }

    private function _is_staff()
    {
        return ($this->user->_is_admin || $this->user->_is_moderator);
    }

    private function _check_admin()
    {
        if (!$this->user->_is_admin) {
            throw new Exception(__("You don't have the permission to do this"));
        }
    }
}
