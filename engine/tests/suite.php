<?php
/**
 * Runs unit tests.
 */

use Zend\Mail\Transport\InMemory as InMemoryTransport;

require_once __DIR__ . '/../../autoloader.php';

\Elgg\Application::start();

require_once __DIR__ . '/ElggCoreUnitTest.php';
require_once __DIR__ . '/ElggCoreGetEntitiesBaseTest.php';

// don't expect admin session for CLI
if (!TextReporter::inCli()) {
	elgg_admin_gatekeeper();
} else {
	$admin = array_shift(elgg_get_admins(array('limit' => 1)));
	if (!login($admin)) {
		echo "Failed to login as administrator.";
		exit(1);
	}
	global $CONFIG;
	$CONFIG->debug = 'NOTICE';
}

// turn off system log
elgg_unregister_event_handler('all', 'all', 'system_log_listener');
elgg_unregister_event_handler('log', 'systemlog', 'system_log_default_logger');

// turn off notifications
$notifications = _elgg_services()->notifications;
$events = $notifications->getEvents();
foreach ($events as $type => $subtypes) {
	foreach ($subtypes as $subtype => $actions) {
		$notifications->unregisterEvent($type, $subtype);
	}
}

// disable emails
_elgg_services()->setValue('mailer', new InMemoryTransport());

// Disable maximum execution time.
// Tests take a while...
set_time_limit(0);

$suite = new TestSuite('Elgg Core Unit Tests');

// emit a hook to pull in all tests
$test_files = elgg_trigger_plugin_hook('unit_test', 'system', null, array());
foreach ($test_files as $file) {
	$suite->addFile($file);
}

if (TextReporter::inCli()) {
	// In CLI error codes are returned: 0 is success
	$start_time = microtime(true);
	$reporter = new TextReporter();
	$result = $suite->Run($reporter) ? 0 : 1 ;
	echo sprintf("Time: %.2f seconds, Memory: %.2fMb\n",
		microtime(true) - $start_time,
		memory_get_peak_usage() / 1048576.0 // in megabytes
	);
	exit($result);
}

$old = elgg_set_ignore_access(true);
$suite->Run(new HtmlReporter('utf-8'));
elgg_set_ignore_access($old);
