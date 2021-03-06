<?php
/**
 * Members plugin initialization
 *
 * To adding a list page, handle the hook (members:list, <page_name>) and return the HTML for the list.
 *
 * To alter the navigation tabs, use the hook (members:config, tabs) which receives the array used to build them.
 */

elgg_register_event_handler('init', 'system', 'members_init');

/**
 * Initialize page handler and site menu item
 */
function members_init() {
	elgg_register_page_handler('members', 'members_page_handler');

	$item = new ElggMenuItem('members', elgg_echo('members'), 'members');
	elgg_register_menu_item('site', $item);

	$list_types = array('newest', 'alpha', 'popular', 'online');

	foreach ($list_types as $type) {
		elgg_register_plugin_hook_handler('members:list', $type, "members_list_$type");
		elgg_register_plugin_hook_handler('members:config', 'tabs', "members_nav_$type");
	}
}

/**
 * Returns content for the "popular" page
 *
 * @param string      $hook        "members:list"
 * @param string      $type        "popular"
 * @param string|null $returnvalue list content (null if not set)
 * @param array       $params      array with key "options"
 * @return string
 */
function members_list_popular($hook, $type, $returnvalue, $params) {
	if ($returnvalue !== null) {
		return;
	}

	$options = $params['options'];
	$options['relationship'] = 'friend';
	$options['inverse_relationship'] = false;
	return elgg_list_entities_from_relationship_count($options);
}

/**
 * Returns content for the "newest" page
 *
 * @param string      $hook        "members:list"
 * @param string      $type        "newest"
 * @param string|null $returnvalue list content (null if not set)
 * @param array       $params      array with key "options"
 * @return string
 */
function members_list_newest($hook, $type, $returnvalue, $params) {
	if ($returnvalue !== null) {
		return;
	}
	return elgg_list_entities($params['options']);
}

/**
 * Returns content for the "online" page
 *
 * @param string      $hook        "members:list"
 * @param string      $type        "online"
 * @param string|null $returnvalue list content (null if not set)
 * @param array       $params      array with key "options"
 * @return string
 */
function members_list_online($hook, $type, $returnvalue, $params) {
	if ($returnvalue !== null) {
		return;
	}
	return get_online_users();
}

/**
 * Returns content for the "alphabetical" page
 *
 * @param string      $hook        "members:list"
 * @param string      $type        "alpha"
 * @param string|null $returnvalue list content (null if not set)
 * @param array       $params      array with key "options"
 * @return string
 */
function members_list_alpha($hook, $type, $returnvalue, $params) {
	if ($returnvalue !== null) {
		return;
	}
	
	$dbprefix = elgg_get_config('dbprefix');
	$options = elgg_extract('options', $params);
	
	$options['joins'][] = "JOIN {$dbprefix}users_entity ue ON e.guid = ue.guid";
	$options['order_by'] = 'ue.name ASC';
	
	return elgg_list_entities($options);
}

/**
 * Appends "popular" tab to the navigation
 *
 * @param string $hook        "members:config"
 * @param string $type        "tabs"
 * @param array  $returnvalue array that build navigation tabs
 * @param array  $params      unused
 * @return array
 */
function members_nav_popular($hook, $type, $returnvalue, $params) {
	$returnvalue['popular'] = array(
		'title' => elgg_echo('sort:popular'),
		'url' => "members/popular",
	);
	return $returnvalue;
}

/**
 * Appends "newest" tab to the navigation
 *
 * @param string $hook        "members:config"
 * @param string $type        "tabs"
 * @param array  $returnvalue array that build navigation tabs
 * @param array  $params      unused
 * @return array
 */
function members_nav_newest($hook, $type, $returnvalue, $params) {
	$returnvalue['newest'] = array(
		'title' => elgg_echo('sort:newest'),
		'url' => "members",
	);
	return $returnvalue;
}

/**
 * Appends "online" tab to the navigation
 *
 * @param string $hook        "members:config"
 * @param string $type        "tabs"
 * @param array  $returnvalue array that build navigation tabs
 * @param array  $params      unused
 * @return array
 */
function members_nav_online($hook, $type, $returnvalue, $params) {
	$returnvalue['online'] = array(
		'title' => elgg_echo('members:label:online'),
		'url' => "members/online",
	);
	return $returnvalue;
}

/**
 * Appends "alphabetical" tab to the navigation
 *
 * @param string $hook        "members:config"
 * @param string $type        "tabs"
 * @param array  $returnvalue array that build navigation tabs
 * @param array  $params      unused
 * @return array
 */
function members_nav_alpha($hook, $type, $returnvalue, $params) {
	$returnvalue['alpha'] = array(
		'title' => elgg_echo('sort:alpha'),
		'url' => "members/alpha",
	);
	return $returnvalue;
}


/**
 * Members page handler
 *
 * @param array $page url segments
 * @return bool
 */
function members_page_handler($page) {
	if (empty($page[0])) {
		$page[0] = 'newest';
	}

	set_input('page', $page[0]);

	if ($page[0] == 'search') {
		echo elgg_view_resource('members/search');
	} else {
		echo elgg_view_resource('members/index');
	}
	return true;
}
