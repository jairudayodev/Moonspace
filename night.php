<?php

/**
 * night ("Pergunta da Noite")
 *
 * @package Moonspace
 */

// fetch bootloader
require('bootloader.php');

// user access
user_access();

// fetch night class
require_once(ABSPATH . 'includes/class-night.php');

$view = (isset($_GET['view'])) ? $_GET['view'] : '';

try {

	$night = new Night($user);

	switch ($view) {
		case '':
			// page header
			page_header($system['system_title'] . ' - Pergunta da Noite');

			// tonight
			$night_date = Night::current_night_date();
			$question = $night->get_question($night_date);
			$my_answer = $night->get_my_answer($night_date);
			$answers_count = $night->count_answers($night_date);
			/* as respostas dos outros só aparecem depois de responder (admin/moderador vê para moderar) */
			$can_see_answers = ($my_answer || $night->is_staff());
			$answers = ($question && $can_see_answers) ? $night->get_answers($night_date) : [];

			/* assign variables */
			$smarty->assign('night_label', Night::format_night_date($night_date));
			$smarty->assign('night_start_hour', Night::START_HOUR);
			$smarty->assign('question', $question);
			$smarty->assign('my_answer', $my_answer);
			$smarty->assign('answers_count', $answers_count);
			$smarty->assign('can_see_answers', $can_see_answers);
			$smarty->assign('answers', $answers);
			$smarty->assign('max_answer_length', Night::MAX_ANSWER_LENGTH);
			break;

		case 'minhas':
			// page header
			page_header($system['system_title'] . ' - Minhas noites');

			// archive
			$smarty->assign('nights', $night->get_my_nights());
			break;

		case 'perguntas':
			// admin only
			if (!$user->_is_admin) {
				_error(404);
			}

			// page header
			page_header($system['system_title'] . ' - Perguntas da Noite');

			// questions
			$smarty->assign('questions', $night->get_questions());
			$smarty->assign('max_question_length', Night::MAX_QUESTION_LENGTH);
			break;

		default:
			_error(404);
			break;
	}

	/* assign variables */
	$smarty->assign('view', $view);
	$smarty->assign('night_is_staff', $night->is_staff());
} catch (Exception $e) {
	_error(__("Error"), $e->getMessage());
}

// page footer
page_footer("night");
