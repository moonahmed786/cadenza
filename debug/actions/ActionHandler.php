<?php
class ActionHandler {
	
	static function action($action) {
		$outputType = 'json'; // standard output type for AJAX error response
		if ($action == 'viewAttachment') {
			$outputType = 'html'; // attachments simply output to the browser in a new window, rather than providing a standard AJAX response
		}
		elseif (substr($action, 0, 6) == 'upload' && isset($_REQUEST['iframe']) && $_REQUEST['iframe']) {
			$outputType = 'iframe'; // special output type for AJAX error responses when uploading using iframe method
		}
		ErrorHandler::setOutputType($outputType);
		
		$response = null;
		if (Core::isEnvironment('maintenance') || !Login::isLoggedIn()) {
			$redirect_uri = Core::cadenzaUrl('index.php');
			$response = array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'disable_unsafe_navigation'=>true);
		}
		else {
			$action_ok = false;
			$class = null;
			if (Login::isLoggedInAsStudent()) {
				$class = 'StudentActions';
			}
			elseif (Login::isLoggedInAsTeacher()) {
				$class = 'TeacherActions';
			}
			elseif (Login::isLoggedInAsAdmin()) {
				$class = 'AdminActions';
			}
			$func = $action;
			if (is_callable(array($class, $func))) {
				Session::remove('action_ok'); // just incase
				$response = call_user_func(array($class, $func)); // sets action_ok true in session if action is allowed
				if (Session::is('action_ok')) {
					$action_ok = true;
					if (is_array($response)) {
						if (!isset($response['result'])) {
							$response['result'] = 'success';
						}
					}
					elseif ($response === null) {
						$response = array('result'=>'success');
					}
					else {
						$response = array('result'=>'success', 'html'=>$response);
					}
				}
				Session::remove('action_ok');
			}
			if (!$action_ok) {
				$redirect_uri = Core::cadenzaUrl('index.php');
				$response = array('result'=>'redirect', 'destination'=>filter_var($redirect_uri, FILTER_SANITIZE_URL), 'message'=>Language::getText('error', 'invalid_action'), 'disable_unsafe_navigation'=>true);
			}
		}
		// adjust response according to output type
		switch ($outputType) {
			case 'json':
				$response = json_encode($response);
				break;
			case 'html':
				if (isset($response['result'])) {
					if ($response['result'] == 'success' && isset($response['html'])) {
						$response = $response['html'];
					}
					elseif ($response['result'] == 'redirect' && isset($response['destination'])) {
						$response = "<script>window.location = '".$response['destination']."'</script>";
					}
				}
				break;
			case 'iframe':
				$response = '<meta charset="UTF-8" /><textarea data-type="application/json">'.json_encode($response).'</textarea>';
				break;
		}
		// output response
		print $response;
	}
	
}
