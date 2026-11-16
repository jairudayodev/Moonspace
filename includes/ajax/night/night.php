<?php

/**
 * ajax -> night ("Pergunta da Noite")
 *
 * @package Moonspace
 */

// fetch bootstrap
require('../../../bootstrap.php');

// check AJAX Request
is_ajax();

// user access
user_access(true);

// fetch night class
require_once(ABSPATH . 'includes/class-night.php');

try {

	$night = new Night($user);

	switch ((isset($_GET['do'])) ? $_GET['do'] : '') {
		case 'answer':
			$night->add_answer($_POST['answer']);
			break;

		case 'add_question':
			$night->add_question($_POST['question']);
			break;

		case 'delete_question':
			if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
				_error(400);
			}
			$night->delete_question($_POST['id']);
			break;

		case 'delete_answer':
			if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
				_error(400);
			}
			$night->delete_answer($_POST['id']);
			break;

		default:
			_error(400);
			break;
	}

	// return & exit
	return_json(array('callback' => 'window.location.reload();'));
} catch (Exception $e) {
	return_json(array('error' => true, 'message' => $e->getMessage()));
}
