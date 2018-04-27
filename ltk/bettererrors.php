<?php namespace ltk;
class bettererrors { // From ltk-svn: ^/trunk/common/php/bettererrors.php@4946
	
	// error code reference
	// from http://php.net/manual/en/errorfunc.constants.php
	// 1		E_ERROR
	// 2		E_WARNING
 	// 4		E_PARSE
	// 8		E_NOTICE
	// 16		E_CORE_ERROR
	// 32		E_CORE_WARNING
	// 64		E_COMPILE_ERROR
	// 128		E_COMPILE_WARNING
	// 256		E_USER_ERROR
	// 512		E_USER_WARNING
	// 1024		E_USER_NOTICE
	// 2048		E_STRICT (Since PHP 5 but not included in E_ALL until PHP 5.4.0)
	// 4096		E_RECOVERABLE_ERROR (Since PHP 5.2.0)
	// 8192		E_DEPRECATED (Since PHP 5.2.0)
	// 16384	E_USER_DEPRECATED (Since PHP 5.3.0)
	// 32767	E_ALL (32767 in PHP 5.4.x, 30719 in PHP 5.3.x, 6143 in PHP 5.2.x, 2047 previously)
	// error code reference
	
	static function init($options=array()) {
		// defaults
		$default_print_errors = true;
		$default_htmlentities_charset = 'ISO-8859-1';
		$default_display_errors = true;
		$default_error_reporting_level = E_ERROR | E_WARNING | E_PARSE | E_NOTICE |
		E_CORE_ERROR | E_CORE_WARNING |	E_COMPILE_ERROR | E_COMPILE_WARNING |
		E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE;
		$default_fix_default_timezone = true;
		// options
		$print_errors = isset($options['print-errors']) ? $options['print-errors'] : $default_print_errors;
		$htmlentities_charset = isset($options['htmlentities-charset']) ? $options['htmlentities-charset'] : $default_htmlentities_charset;
		$display_errors = isset($options['display-errors']) ? $options['display-errors'] : $default_display_errors;
		$error_reporting_level = isset($options['error-reporting-level']) ? $options['error-reporting-level'] : $default_error_reporting_level;
		$fix_default_timezone = isset($options['fix-default-timezone']) ? $options['fix-default-timezone'] : $default_fix_default_timezone;
		// set options
		bettererrors::print_errors($print_errors);
		bettererrors::htmlentities_charset($htmlentities_charset);
		ini_set("display_errors", $display_errors);
		error_reporting($error_reporting_level);
		if ($fix_default_timezone) {
			// prevent an annoying warning from the date() and phpinfo() functions.
			bettererrors::fix_default_timezone();
		}
		// we can't use a static function as an error handler, create an object and use its methods (right here only)
		$obj = new bettererrors();
		// set error handler
		$old_error_handler = set_error_handler(array($obj, 'handler'));
	}
	static function print_errors($set=null) {
		static $print_errors = true;
		if (isset($set)) { $print_errors = $set; }
		return $print_errors;
	}
	static function htmlentities_charset($set=null) {
		static $htmlentities_charset = 'ISO-8859-1';
		if (isset($set)) { $htmlentities_charset = $set; }
		return $htmlentities_charset;
	}
	static function events() {
		return array(
			'report-html',
			'add-info',
			'skip-trace'
		);
	}
	static function listeners($add=null) {
		static $listeners = array();
		if (!empty($add)) { $listeners[] = $add; }
		return $listeners;
	}
	static function addlistener($event, $callback) {
		$events = bettererrors::events();
		if (!in_array($event, $events)) { trigger_error("No such event '$event'.", E_USER_ERROR); }
		if (!is_callable($callback)) { trigger_error("Not a valid callback: ".print_r($callback, true).".", E_USER_ERROR); }
		bettererrors::listeners(array('event'=>$event, 'callback'=>$callback));
	}
	static function runlisteners($event, $params=array()) {
		$listeners = bettererrors::listeners();
		$results = array();
		foreach ($listeners as $listener) {
			$thisevent = $listener['event'];
			$callback = $listener['callback'];
			if (!is_callable($callback)) { continue; }
			if ($thisevent == $event) {
				$results[] = call_user_func_array($callback, $params);
			}
		}
		return $results;
	}
	static function fix_default_timezone() {
		// prevents an annoying warning when using the date() and phpinfo() functions.
		if (function_exists("date_default_timezone_set")) {
			date_default_timezone_set('UTC');
		}
	}
	static function handler($errno, $errstr, $errfile, $errline, $errcontext) {
		// make an error array object
		$err = array(
			'errno'=>$errno,
			'errstr'=>$errstr,
			'errfile'=>$errfile,
			'errline'=>$errline,
			'errcontext'=>$errcontext
		);
		// get the backtrace from this point
		$backtrace = debug_backtrace();
		// remove the reference to this function
		array_shift($backtrace);
		// call the error handler on the errobj and backtrace
		bettererrors::handle($err, $backtrace);
	}
	static function handle($err, $backtrace, $error_reporting_level=null) {
		// extract error data
		$errno = $err['errno'];
		// normalize backtrace, make sure each element has all fields and remove references to this class
		$backtrace = bettererrors::normalize_backtrace($backtrace);
		// get error reporting level if needed
		$error_reporting_level = !empty($error_reporting_level) ? $error_reporting_level : error_reporting();
		// if the current $errno is not included in the error reporting level, return
		if (($error_reporting_level &~ $errno) == $error_reporting_level) { return; }
		// prevent errors in this class from causing an infinite loop
		if (bettererrors::in_error()) { return; } else { bettererrors::in_error(true); }
		// get all content in the output buffer
		$pagecontent = bettererrors::get_buffered_content();
		// render a better error report
		$errorreport = bettererrors::getreport($err, $backtrace);
		// run error report html listeners (used to track and log errors)
		$errorreport_arr = is_array($errorreport) ? $errorreport : array($errorreport);
		bettererrors::runlisteners('report-html', $errorreport_arr);
		// show errors
		bettererrors::show_error($errorreport, $pagecontent);
		// output the rest of the output buffer just in case there is something still in it
		print bettererrors::get_buffered_content();
		// exit to stop php from running any more code
		exit;
	}
	static function errorcomment($comment, $options=array()) {
		// options
		$info = isset($options['info']) ? $options['info'] : true;
		$trace = isset($options['trace']) ? $options['trace'] : false;
		// make an error array object
		$err = array(
			'errno'=>'COMMENT',
			'errstr'=>$comment,
			'errfile'=>bettererrors::get_calling_file(),
			'errline'=>bettererrors::get_calling_line(),
			'errcontext'=>array()
		);
		// extract error data
		$errno = $err['errno'];
		// normalize backtrace, make sure each element has all fields and remove references to this class
		$backtrace = array();
		// prevent errors in this class from causing an infinite loop
		if (bettererrors::in_error()) { return; } else { bettererrors::in_error(true); }
		// get all content in the output buffer
		$pagecontent = bettererrors::get_buffered_content();
		// render a better error report
		$errorreport = bettererrors::getreport($err, $backtrace, array('info'=>$info, 'trace'=>$trace));
		// run error report html listeners (used to track and log errors)
		$errorreport_arr = is_array($errorreport) ? $errorreport : array($errorreport);
		bettererrors::runlisteners('report-html', $errorreport_arr);
		// show errors
		bettererrors::show_error($errorreport, $pagecontent);
		// output the rest of the output buffer just in case there is something still in it
		print bettererrors::get_buffered_content();
		// set in error flag to false
		bettererrors::in_error(false);
	}
	static function getreport($err, $backtrace, $options=array()) {
		// options
		$info = isset($options['info']) ? $options['info'] : true;
		$trace = isset($options['trace']) ? $options['trace'] : true;
		// extract error data
		$errno = $err['errno'];
		$errstr = $err['errstr'];
		$errfile = $err['errfile'];
		$errline = $err['errline'];
		$errcontext = $err['errcontext'];
		// make unique id for this error block
		$uniqueid = 'bettererror'.str_replace(array(" ", "."), array('', ''), microtime());
		$errtypes = bettererrors::errortype();
		$errtype = $errtypes[$errno];
	    $errcolors = bettererrors::errorcolor();
		$errcolor = $errcolors[$errno];
		$errlinestr = !empty($errline) ? 'line '.$errline : '';
		$errtitle = $errtype.' : '.date('Y-m-j H:i:s', time());
		$errtitlemsg = '<big><strong>'.$errtitle.'</strong></big><br/>'.$errstr;
		$errmsgtitle = $errstr.'<br/><big><strong>'.$errtitle.'</strong></big>';
		$errloc = basename($errfile).'<br/>'.$errlinestr;
		$errloc2 = basename($errfile).' '.$errlinestr;
		// start buffering
		ob_start();
		// print out error report
		print bettererrors::geterrorreminder($uniqueid, $errtitlemsg, $errloc2, $errcolor);
		print bettererrors::geterrorrlocation($uniqueid);
		if ($info):
		print bettererrors::geterrorinfo($uniqueid);
		endif;
		if ($trace):
		print bettererrors::geterrorreminder($uniqueid, $errstr, $errloc2, $errcolor);
		print bettererrors::geterrorrlocation($uniqueid);
		print bettererrors::geterrortrace($uniqueid, $backtrace, $errno, $errstr, $errfile, $errline, $errcontext);
		endif;
		if ($info || $trace):
		print bettererrors::geterrorreminder($uniqueid, $errtitlemsg, $errloc2, $errcolor);
		print bettererrors::geterrorrlocation($uniqueid);
		endif;
		$errorhtml = ob_get_clean();
		$errorhtml = bettererrors::geterrorframe($uniqueid, $errorhtml);
		// return error
		return $errorhtml;
	}
	static function geterrorframe($uniqueid, $errorhtml) {
		// start output buffering
		ob_start();
		// draw error frame
		?><div id="<?php print $uniqueid?>">
			<style>
			div#<?php print $uniqueid?> { width:100%;line-height:normal; }
			div#<?php print $uniqueid?> div.bettererror-close { float:right; }
			div#<?php print $uniqueid?> div.bettererror-close a { position:absolute;color:#000;background:#fff;text-decoration:none;font-family:verdana;font-size:8px;display:block;padding:0px 4px 2px 4px;border:1px solid black;cursor:pointer; }
			div#<?php print $uniqueid?> div.bettererror-close-top a { margin:-5px; }
			div#<?php print $uniqueid?> div.bettererror-close-bottom a { margin:-8px; }
			div#<?php print $uniqueid?> div.bettererror-close a:hover { background:#fd9; }
			fieldset.bettererror-frame { background:white;padding:0;border:1px solid black;width:auto;overflow:hidden; }
			</style>
			<fieldset class="bettererror-frame">
				<div class="bettererror-close bettererror-close-top"><a onclick="document.getElementById('<?php print $uniqueid; ?>').style.display = 'none';">x</a></div>
				<div class="bettererror-content"><?php print $errorhtml; ?></div>
				<div class="bettererror-close bettererror-close-bottom"><a onclick="document.getElementById('<?php print $uniqueid; ?>').style.display = 'none';">x</a></div>
			</fieldset>
		</div><?php
		// return buffered frame
		return ob_get_clean();
	}
	static function geterrorbanner($uniqueid, $errtitle, $errloc, $errmsg, $errcolor) {
		// start output buffering
		ob_start();
		// draw error banner
		?><div class="bettererror-banner">
			<style>
			div#<?php print $uniqueid?> div.bettererror-banner {  }
			div#<?php print $uniqueid?> div.bettererror-header { padding:10px;color:white;font-family:georgia;font-size:16px;background:<?php print $errcolor?>; }
			div#<?php print $uniqueid?> div.bettererror-message { font-family:georgia;font-size:14px;color:white;padding:10px;margin:0; }
			</style>
			<div class="bettererror-header">
				<div class="bettererror-title">
					<span style="float:right;text-align:right">
					 <?php //print $errloc;?>
					</span>
					<strong>
					 <?php print strtoupper($errtitle); ?>
					</strong>
				</div>
				<!-- ><div class="bettererror-message">
					<?php //print str_replace("\n", "\n<br/>", $errmsg)?>
				</div>< -->
		</div><?php
		// return buffered banner
		return ob_get_clean();
	}
	static function geterrorinfo($uniqueid) {
		$file = basename($_SERVER['PHP_SELF']);
		$displayfile = basename(dirname(dirname($_SERVER['PHP_SELF'])))."/".basename(dirname($_SERVER['PHP_SELF']))."/".basename($_SERVER['PHP_SELF']);
		$extras = bettererrors::runlisteners('add-info');
		// start output buffering
		ob_start();
		// draw error banner
		?><div class="bettererror-info">
			<style>
			div#<?php print $uniqueid?> div.bettererror-info {font-family:verdana; }
			div#<?php print $uniqueid?> div.bettererror-info pre { padding:0;margin:10px 0;border:0;font-size:inherit;font-family:inherit;font-size:8px; }
			div#<?php print $uniqueid?> div.bettererror-info div.location-info { padding:5px;color:#fff;background:#333;font-family:verdana;font-size:10px; }
			div#<?php print $uniqueid?> div.bettererror-info div.environment-info { padding:0 5px 0 5px;color:#333;font-size:10px;font-size:10px;margin-top:10px;margin-left:10px; }
			</style>
			<?php  ?>
			<div class="environment-info">
			 	<?php if (!empty($_SERVER['PHP_SELF'])) { ?><pre>PHP_SELF<br/> <?php print $_SERVER['PHP_SELF']; ?></pre><?php }?>
				<?php if (!empty($_SERVER['QUERY_STRING'])) { ?><pre>QUERY_STRING<br/> <?php print $_SERVER['QUERY_STRING']; ?></pre><?php }?>
				<?php if (!empty($_SERVER['HTTP_USER_AGENT'])) { ?><pre>HTTP_USER_AGENT<br/> <?php print $_SERVER['HTTP_USER_AGENT']; ?></pre><?php }?>
				<?php if (!empty($_SERVER['HTTP_REFERER'])) { ?><pre>HTTP_REFERER<br/> <?php print $_SERVER['HTTP_REFERER']; ?></pre><?php }?>
				<?php if (!empty($_GET)) { ?><pre>$_GET <?php print htmlentities(print_r($_GET, true), ENT_COMPAT, bettererrors::htmlentities_charset()); ?></pre><?php } ?>
				<?php if (!empty($_POST)) { ?><pre>$_POST <?php print htmlentities(print_r($_POST, true), ENT_COMPAT, bettererrors::htmlentities_charset()); ?></pre><?php } ?>
				<?php if (!empty($_FILES)) { ?><pre>$_FILES <?php print htmlentities(print_r($_FILES, true), ENT_COMPAT, bettererrors::htmlentities_charset()); ?></pre><?php } ?>
				<?php foreach ($extras as $extra): ?>
				<?php if (empty($extra)) { continue; } ?>
				<?php if (is_array($extra)) { ?><pre><?php print htmlentities(print_r($extra, true), ENT_COMPAT, bettererrors::htmlentities_charset()); ?></pre><?php } ?>
				<?php if (!is_array($extra)) { ?><pre><?php print htmlentities($extra, ENT_COMPAT, bettererrors::htmlentities_charset()); ?></pre><?php } ?>
				<?php endforeach; ?>
			</div>
		</div><?php
		// return buffered banner
		return ob_get_clean();
	}
	static function geterrorrlocation($uniqueid) {
		// start output buffering
		$file = basename($_SERVER['PHP_SELF']);
		$displayfile = basename(dirname(dirname($_SERVER['PHP_SELF'])))."/".basename(dirname($_SERVER['PHP_SELF']))."/".basename($_SERVER['PHP_SELF']);
		ob_start();
		// draw error banner
		?><div class="bettererror-location">
			<style>
			div#<?php print $uniqueid?> div.bettererror-location { padding:5px;color:#fff;background:#333;font-family:verdana;font-size:10px; }
			</style>
			<span style="float:right"><?php print $displayfile; ?></span>
			page: <?php print $file?>
		</div><?php
		// return buffered banner
		return ob_get_clean();
	}
	static function geterrorreminder($uniqueid, $errmsg, $errloc, $errcolor) {
		// start output buffering
		ob_start();
		// draw error banner
		?><div class="bettererror-reminder">
			<style>
			div#<?php print $uniqueid?> div.bettererror-reminder { background:<?php print $errcolor?>;color:white;padding:5px;font-size:10px;font-family:verdana; }
			</style>
			<span style="float:right"><?php print $errloc; ?></span>
			<?php print str_replace("\n", "\n<br/>", $errmsg); ?>
		</div><?php
		// return buffered banner
		return ob_get_clean();
	}
	static function geterrortrace($uniqueid, $debug_backtrace, $errno, $errmsg, $filename, $linenum, $context) {
		// remove unwanted classes from the error trace
		$backtrace = array_reverse(bettererrors::extend_backtrace($debug_backtrace), true);
		// scan the trace to find trigger and culprit classes
		$usererror = $backtrace[0]['calling-function-triggered'];
		// get files
		$files = array();
		foreach ($backtrace as $depth=>$trace) {
			$file = isset($trace['file']) ? $trace['file'] : '';
			$line = isset($trace['line']) ? $trace['line'] : '';
			// skips
			$backtrace[$depth]['skip'] = false;
			if (isset($file) && strpos($file, "eval()'d code") !== false) { $backtrace[$depth]['skip'] = true; }
			if ($backtrace[$depth]['skip']) { continue; }
			// get files
			$files[$file] = (!empty($file) && is_file($file)) ? file_get_contents($file) : "";
			$lines[$file] = explode("\n", $files[$file]);
			foreach ($lines[$file] as $key=>$value) { $lines[$file][$key] = " ".($key+1)."  ".$value; }
			$backtrace[$depth]['displayfile'] = basename(dirname(dirname($file)))."/".basename(dirname($file))."/".basename($file);
			$backtrace[$depth]['prelines'][$file."_".$line] = array_slice($lines[$file], max($line - 5, 0), 4 - (max($line - 5, 0) - ($line - 5)));
			$backtrace[$depth]['theline'][$file."_".$line] = current(array_slice($lines[$file], max($line - 1, 0), 1));
			$backtrace[$depth]['postlines'][$file."_".$line] = ""; //implode("\n", array_slice($lines[$file], $line, 2));
		}
		// start output buffering
		ob_start();
		// draw error trace
		?><div class="bettererror-trace">
			<style>
			/* layout */
			div#<?php print $uniqueid;?> div.bettererror-trace { margin:0px;padding:8px 5px;overflow:hidden; }
			div#<?php print $uniqueid;?> div.file-trace { margin:8px;padding:4px;overflow:hidden; }
			div#<?php print $uniqueid;?> div.function-trace { margin:8px;padding:4px;overflow:hidden; }
			div#<?php print $uniqueid;?> pre.trace-pre { color:#666;font-size:10px;margin:0px;padding:0px;border:0px;overflow:hidden; }
			div#<?php print $uniqueid;?> div.trace-method-arguments table.trace-arguments-table { width:100%; }
			div#<?php print $uniqueid;?> div.trace-method-arguments table.trace-arguments-table td.trace-argument-cell { padding:1px;font-family:courier;padding:2px;font-size:10px;font-family:courier;vertical-align:top; }
			div#<?php print $uniqueid;?> div.trace-start-location { text-align:left;margin-bottom:1px;font-size:12px;font-weight:normal;padding:5px;font-family:courier; }
			div#<?php print $uniqueid;?> div.trace-end-location { text-align:left;font-size:10px;font-weight:normal;padding:5px;font-family:courier; }
			div#<?php print $uniqueid;?> div.trace-function-location { text-align:left;margin-bottom:1px;font-size:12px;font-weight:normal;padding:5px;font-family:courier; }
			div#<?php print $uniqueid;?> ul.trace-code-lines { margin:0;padding:0;list-style:none; }
			div#<?php print $uniqueid;?> ul.trace-code-lines li.trace-line { margin:0 0 1px 0;padding:2px 0;}
			div#<?php print $uniqueid;?> ul.trace-code-lines li.trace-line div.line-star { float:right;font-family:verdana;font-size:10px;font-weight:bold;padding:0 4px; }
			/* regular colors */
			div#<?php print $uniqueid;?> div.bettererror-trace { background:#ddd; }
			div#<?php print $uniqueid;?> div.file-trace { background:#fff; }
			div#<?php print $uniqueid;?> div.function-trace { background:#fff; }
			div#<?php print $uniqueid;?> div.file-trace div.trace-start-location { color:#000;background:#d4ddd0; }
			div#<?php print $uniqueid;?> div.file-trace div.trace-end-location { color:#000;background:#e8e8e8; }
			div#<?php print $uniqueid;?> div.file-trace ul.trace-code-lines li.trace-line { color:#666;background:#f4f4f4; }
			div#<?php print $uniqueid;?> div.function-trace div.trace-method-arguments table.trace-arguments-table td.trace-argument-cell { color:#666;background:#f4f4f4; }
			div#<?php print $uniqueid;?> div.function-trace div.trace-function-location { color:#000;background:#d4ddd0; }
			/* trigger-error */
			div#<?php print $uniqueid;?> div.file-trace div.trigger-error div.trace-start-location { color:#000;background:#e4e4e4; }
			div#<?php print $uniqueid;?> div.file-trace div.trigger-error div.trace-end-location { color:#000;background:#e4e4e4; }
			div#<?php print $uniqueid;?> div.file-trace div.trigger-error ul.trace-code-lines li.trace-line { color:#666;background:#e8e8e8 }
			div#<?php print $uniqueid;?> div.function-trace div.trigger-error div.trace-method-arguments table.trace-arguments-table td.trace-argument-cell { color:#666;background:#e8e8e8; }
			div#<?php print $uniqueid;?> div.function-trace div.trigger-error div.trace-function-location { color:#000;background:#e4e4e4; }
			/* culprit-func */
			div#<?php print $uniqueid;?> div.function-trace div.culprit-func div.trace-method-arguments table.trace-arguments-table td.trace-argument-cell { color:#666;background:#f4f4f4; }
			div#<?php print $uniqueid;?> div.function-trace div.culprit-func div.trace-function-location { color:#000;background:#edc; }
			<?php if (!$usererror) { ?>div#<?php print $uniqueid;?> div.file-trace div.culprit-func div.trace-start-location { color:#000;background:#edc; }<?php } ?>
			</style><?php
			// loop through each trace and output
			$count = 0;
			foreach ($backtrace as $depth=>$trace) {
				// skip classes if flagged as skip
				$skiptrace = bettererrors::runlisteners('skip-trace', array($trace));
				if (in_array("1", $skiptrace)) $trace['skip'] = true;
				if ($trace['skip']) { continue; }
				// increment counter
				$count++;
				// basic trace variables
				$file = $trace['file'];
				$line = $trace['line'];
				// get extended backtrace variables
				$calling_file_path = $trace['calling-file-path'];
				$calling_file_short_path = $trace['calling-file-short-path'];
				$calling_file_name = $trace['calling-file-name'];
				$calling_file_line = $trace['calling-file-line'];
				$calling_function_object = $trace['calling-function-object'];
				$calling_function_class = $trace['calling-function-class'];
				$calling_function_call_type = $trace['calling-function-call-type'];
				$calling_function_name = $trace['calling-function-name'];
				$calling_function_args = $trace['calling-function-args'];
				$called_function_object = $trace['called-function-object'];
				$called_function_class = $trace['called-function-class'];
				$called_function_call_type = $trace['called-function-call-type'];
				$called_function_name = $trace['called-function-name'];
				$called_function_args = $trace['called-function-args'];
				$display_calling_function_args = $trace['display-calling-function-args'];
				$display_called_function_args = $trace['display-called-function-args'];
				$display_calling_function = $trace['display-calling-function'];
				$display_called_function = $trace['display-called-function'];
				$calling_function_triggered = $trace['calling-function-triggered'];
				$calling_class_triggered = $trace['calling-class-triggered'];
				$blame_calling_class = $trace['blame-calling-class'];
				$blame_calling_function = $trace['blame-calling-function'];
				$error_right_here = $trace['error-right-here'];
				$error_inside_function = $trace['error-inside-function'];
				$trigger_error_function = $trace['trigger-error-function'];
				// classes
				$cssclasses = "";
				if ($calling_function_triggered) $cssclasses .= ' trigger-error';
				if ($calling_class_triggered) $cssclasses .= ' trigger-class';
				if ($calling_function_triggered) $cssclasses .= ' trigger-func';
				if ($blame_calling_class) $cssclasses .= ' culprit-class';
				if ($blame_calling_function) $cssclasses .= ' culprit-func';
				if (!$usererror && $calling_function_triggered) $displayname = $errmsg;
				// lines
				$prelines = !empty($trace['prelines'][$file."_".$line]) ? $trace['prelines'][$file."_".$line] : array();
				$theline = !empty($trace['theline'][$file."_".$line]) ? $trace['theline'][$file."_".$line] : '';
				
				// output trace for this file
				if (!empty($calling_file_name)):
				?><div class="file-trace">
					<div class="<?php print $cssclasses; ?>">
						<div class="trace-start-location">
							<span style="float:right"><?php print $calling_file_short_path; ?></span>
							<span><?php print !empty($display_calling_function) ? $display_calling_function : $calling_file_name; ?></span>
						</div>
						<ul class="trace-code-lines"><?php
							if (!empty($prelines)): foreach ($prelines as $preline):
							?><li class="trace-line green-line"><pre class="trace-pre"><?php print htmlentities($preline, ENT_COMPAT, bettererrors::htmlentities_charset())?></pre></li><?php
							endforeach; endif; ?>
							<li class="trace-line green-line"><div class="line-star">*</div><pre class="trace-pre"><?php print htmlentities($theline, ENT_COMPAT, bettererrors::htmlentities_charset())?></pre></li>
						</ul>
						<div class="trace-end-location">
							<span style="float:right">line <?php print $calling_file_line; ?></span>
							<?php if (!$error_right_here): ?><span><?php print $display_called_function; ?></span><?php endif; ?>
							<?php if ($error_right_here): ?><span><?php print ($error_inside_function) ? $errmsg : $display_called_function; ?></span><?php endif; ?>
						</div>
					</div>
				</div><?php
				endif;
				// output trace for the function called from this class
				if (!empty($called_function_name) && !empty($called_function_args)):
				?><div class="function-trace">
					<div class="<?php print $cssclasses; ?>">
						<div class="trace-function-location">
							<span style="float:right">line <?php print $calling_file_line; ?></span>
							<span><?php print $display_called_function; ?></span>
						</div>
						<div class="trace-method-arguments">
							<table cellpadding="0" cellspacing="2" class="trace-arguments-table">
							<?php foreach ($called_function_args as $key=>$arg):?>
							<tr>
								<td class="trace-argument-cell" style="width:30px;vertical-align:top;">arg<?php print $key; ?></td>
								<td class="trace-argument-cell"><pre class="trace-pre"><?php print bettererrors::get_arg_display($arg); ?></pre></td>
							</tr>
							<?php endforeach; ?>
							</table>
						</div>
					</div>
				</div><?php
				endif;
				
				// output trace for the function called from this class
				if ($error_right_here && $error_inside_function):
				$thiscontext = array();
				foreach ($context as $key=>$arg) {
					if (strpos($theline, '$'.$key) !== false) { $thiscontext[$key] = $arg; };
				}
				if (!empty($thiscontext)):
				?><div class="function-trace">
					<div class="trigger-error">
						<div class="trace-function-location">
							<span style="float:right">line <?php print $calling_file_line; ?></span>
							<span>Variables from this line:</span>
						</div>
						<div class="trace-method-arguments">
							<table cellpadding="0" cellspacing="2" class="trace-arguments-table">
							<?php foreach ($thiscontext as $key=>$arg):?>
							<tr>
								<td class="trace-argument-cell" style="width:30px;vertical-align:top;">$<?php print $key; ?></td>
								<td class="trace-argument-cell"><pre class="trace-pre"><?php print bettererrors::get_arg_display($arg); ?></pre></td>
							</tr>
							<?php endforeach; ?>
							</table>
						</div>
					</div>
				</div><?php
				endif;
				endif;
			}
		?></div><?php
		// return buffered banner
		return ob_get_clean();
	}
	static function errorcolor() {
		$errorcolor = array(
	    	E_ERROR              => '#900',
	        E_WARNING            => '#960',
	        E_PARSE              => '#906',
	        E_NOTICE             => '#990',
	        E_CORE_ERROR         => 'blue',
	        E_CORE_WARNING       => 'blue',
	        E_COMPILE_ERROR      => 'blue',
	        E_COMPILE_WARNING    => 'blue',
	        E_USER_ERROR         => 'blue',
	        E_USER_WARNING       => 'blue',
	        E_USER_NOTICE        => 'blue',
			'COMMENT' 			 => 'green'
		);
		if (defined('E_STRICT')) {
			$errorcolor[E_STRICT] = '#990'; // same color as E_NOTICE
		}
		if (defined('E_RECOVERABLE_ERROR')) {
			$errorcolor[E_RECOVERABLE_ERROR] = '#900'; // same color as E_ERROR
		}
		if (defined('E_DEPRECATED')) {
			$errorcolor[E_DEPRECATED] = '#990'; // same color as E_NOTICE
		}
		if (defined('E_USER_DEPRECATED')) {
			$errorcolor[E_USER_DEPRECATED] = 'blue'; // same color as E_USER_ERROR
		}
		return $errorcolor;
	}
	static function errortype() {
		$errortype = array(
	        E_ERROR              => 'Error',
	        E_WARNING            => 'Warning',
	        E_PARSE              => 'Parsing Error',
	        E_NOTICE             => 'Notice',
	        E_CORE_ERROR         => 'Core Error',
	        E_CORE_WARNING       => 'Core Warning',
	        E_COMPILE_ERROR      => 'Compile Error',
	        E_COMPILE_WARNING    => 'Compile Warning',
	        E_USER_ERROR         => 'User Error',
	        E_USER_WARNING       => 'User Warning',
	        E_USER_NOTICE        => 'User Notice',
			'COMMENT'			 => 'Comment'
	    );
		if (defined('E_STRICT')) {
			$errortype[E_STRICT] = 'Strict Notice';
		}
		if (defined('E_RECOVERABLE_ERROR')) {
			$errortype[E_RECOVERABLE_ERROR] = 'Recoverable Error';
		}
		if (defined('E_DEPRECATED')) {
			$errortype[E_DEPRECATED] = 'Deprecated Notice';
		}
		if (defined('E_USER_DEPRECATED')) {
			$errortype[E_USER_DEPRECATED] = 'User Deprecated';
		}
		return $errortype;
	}
	static function in_error($set=null) {
		// static variable to store in error flag
		static $in_error = false;
		// if something has been passed, set the value of the flag
		if (isset($set)) $in_error = $set;
		// return the value of the flag
		return $in_error;
	}
	static function show_error($errorreport, $pagecontent) {
		// get the value of the show errors flag
		$showerrors = bettererrors::print_errors();
		// if showerrors flag is true, show this error
		if ($showerrors) {
			print $errorreport."\n".$pagecontent."\n";
		}
	}
	static function normalize_backtrace($backtrace) {
		// normalize the backtrace and remove array items referring to this class
		foreach ($backtrace as $key=>$trace) {
			// normalize this item
			if (!array_key_exists('function', $backtrace[$key])) $backtrace[$key]['function'] = '';
			if (!array_key_exists('line', $backtrace[$key])) $backtrace[$key]['line'] = '';
			if (!array_key_exists('file', $backtrace[$key])) $backtrace[$key]['file'] = '';
			if (!array_key_exists('class', $backtrace[$key])) $backtrace[$key]['class'] = '';
			if (!array_key_exists('object', $backtrace[$key])) $backtrace[$key]['object'] = '';
			if (!array_key_exists('type', $backtrace[$key])) $backtrace[$key]['type'] = '';
			if (!array_key_exists('args', $backtrace[$key])) $backtrace[$key]['args'] = array();
			// if this item refers to this class, remove it
			//if ($backtrace[$key]['class'] == __CLASS__) unset($backtrace[$key]);
		}
		// return normalized backtrace
		return $backtrace;
	}
	static function extend_backtrace($backtrace) {
		// lasttrace starts as an empty trace, and is updated to always point to the previous trace
		$lasttrace = array(
			'function'=>'',
			'line'=>'',
			'file'=>'',
			'class'=>'',
			'object'=>'',
			'type'=>'',
			'args'=>array()
		);
		// reverse the array and preserve key values
		$reversed = array_reverse($backtrace, true);
		// loop through the trace and extend it to include more user-friendly variables
		foreach ($reversed as $tracekey=>$trace) {
			// information about the calling file
			$trace['calling-file-path'] = $trace['file'];
			$trace['calling-file-short-path'] = !empty($trace['file']) ? basename(dirname(dirname($trace['file'])))."/".basename(dirname($trace['file']))."/".basename($trace['file']) : '';
			$trace['calling-file-name'] = basename($trace['file']);
			$trace['calling-file-line'] = $trace['line'];
			// information about the calling function
			$trace['calling-function-object'] = $lasttrace['object'];
			$trace['calling-function-class'] = $lasttrace['class'];
			$trace['calling-function-call-type'] = $lasttrace['type'];
			$trace['calling-function-name'] = $lasttrace['function'];
			$trace['calling-function-args'] = $lasttrace['args'];
			// information about the function being called
			$trace['called-function-object'] = $trace['object'];
			$trace['called-function-class'] = $trace['class'];
			$trace['called-function-call-type'] = $trace['type'];
			$trace['called-function-name'] = $trace['function'];
			$trace['called-function-args'] = $trace['args'];
			// some useful additional parameters -
			// display calling function arguments
			$trace['display-calling-function-args'] = '';
			foreach ($trace['calling-function-args'] as $key=>$arg) { $trace['display-calling-function-args'] .= "\$arg$key "; }
			$trace['display-calling-function-args'] = trim($trace['display-calling-function-args']);
			// display called function arguments
			$trace['display-called-function-args'] = '';
			foreach ($trace['called-function-args'] as $key=>$arg) { $trace['display-called-function-args'] .= "\$arg$key "; }
			$trace['display-called-function-args'] = trim($trace['display-called-function-args']);
			// display calling function
			switch ($trace['calling-function-call-type']):
			case '=>': case '::': $trace['display-calling-function'] = $trace['calling-function-class'].$trace['calling-function-call-type'].$trace['calling-function-name']."(".$trace['display-calling-function-args'].")"; break;
			default: $trace['display-calling-function'] = empty($trace['calling-function-name']) ? '' : $trace['calling-function-name']."(".$trace['display-calling-function-args'].")"; break;
			endswitch;
			// display called function
			switch ($trace['called-function-call-type']):
			case '->': case '::': $trace['display-called-function'] = $trace['called-function-class'].$trace['called-function-call-type'].$trace['called-function-name']."(".$trace['display-called-function-args'].")"; break;
			default: $trace['display-called-function'] = empty($trace['called-function-name']) ? '' : $trace['called-function-name']."(".$trace['display-called-function-args'].")"; break;
			
			endswitch;
			// update the backtrace array
			$backtrace[$tracekey] = $trace;
			// update lasttrace
			$lasttrace = $trace;
		}
		// temporary variables for the next loop
		$triggerclasscomplete = false;
		$blameclasscomplete = false;
		$errorherecomplete = false;
		$storedtriggerclass = null;
		$storedblameclass = null;
		// define trigger-error-function, trigger-error-class,
		foreach ($backtrace as $tracekey=>$trace) {
			if ($trace['called-function-name'] == 'trigger_error' && $trace['called-function-class'] =='' && !$errorherecomplete) {
				$errorherecomplete = true;
				$trace['error-right-here'] = true;
				$trace['trigger-error-function'] = true;
				$trace['error-inside-function'] = false;
			}
			elseif (!$errorherecomplete) {
				$errorherecomplete = true;
				$trace['error-right-here'] = true;
				$trace['trigger-error-function'] = false;
				$trace['error-inside-function'] = true;
			}
			else {
				$trace['error-right-here'] = false;
				$trace['trigger-error-function'] = false;
				$trace['error-inside-function'] = false;
			}
			// get trigger-error-function, trigger-error-class, blame-this-class, and blame-this-function for each trace
			if ($trace['called-function-name'] == 'trigger_error' && $trace['called-function-class'] == '' && !$triggerclasscomplete) {
				$storedtriggerclass = $trace['calling-function-name'];
				$trace['calling-class-triggered'] = true;
				$trace['calling-function-triggered'] = true;
			}
			elseif ($storedtriggerclass == $trace['calling-function-class'] && !$triggerclasscomplete) {
				$trace['calling-class-triggered'] = true;
				$trace['calling-function-triggered'] = false;
			}
			elseif (!$triggerclasscomplete) {
				$triggerclasscomplete = true;
				$trace['calling-class-triggered'] = false;
				$trace['calling-function-triggered'] = false;
			}
			else {
				$trace['calling-class-triggered'] = false;
				$trace['calling-function-triggered'] = false;
			}
			// get blame-this-class and blame-this-function
			if (empty($storedblameclass) && $triggerclasscomplete && !$blameclasscomplete) {
				$storedblameclass = $trace['calling-function-class'];
				$trace['blame-calling-function'] = true;
				$trace['blame-calling-class'] = true;
			}
			elseif ($storedblameclass == $trace['calling-function-class'] && $triggerclasscomplete && !$blameclasscomplete) {
				$trace['blame-calling-function'] = false;
				$trace['blame-calling-class'] = true;
			}
			elseif ($triggerclasscomplete && !$blameclasscomplete) {
				$blameclasscomplete = true;
				$trace['blame-calling-function'] = false;
				$trace['blame-calling-class'] = false;
			}
			else {
				$trace['blame-calling-function'] = false;
				$trace['blame-calling-class'] = false;
			}
			// set trace as this trace
			$backtrace[$tracekey] = $trace;
		}
		// return extended backtrace
		return $backtrace;
	}
	static function get_buffered_content() {
		// string to store buffered content
		$buffered = "";
		// get all buffered content until all buffers are closed
		while (ob_get_status()) { $buffered = ob_get_clean(); }
		// return buffered content
		return $buffered;
	}
	static function get_calling_file() {
		$backtrace = debug_backtrace();
		return $backtrace[1]['file'];
	}
	static function get_calling_line() {
		$backtrace = debug_backtrace();
		return $backtrace[1]['line'];
	}
	static function get_arg_display($arg) {
		if (is_object($arg)) {
			return htmlentities('object of class '.get_class($arg), ENT_COMPAT, bettererrors::htmlentities_charset());
		}
		if (is_resource($arg)) {
			return htmlentities('resource of type '.get_resource_type($arg), ENT_COMPAT, bettererrors::htmlentities_charset());
		}
		return (is_array($arg)) ? htmlentities(print_r($arg, true), ENT_COMPAT, bettererrors::htmlentities_charset()) : htmlentities($arg, ENT_COMPAT, bettererrors::htmlentities_charset());
	}
}
