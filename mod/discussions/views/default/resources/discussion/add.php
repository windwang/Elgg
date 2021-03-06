<?php

elgg_gatekeeper();

$guid = get_input('guid');
$container = get_entity($guid);

// Make sure user has permissions to add a topic to container
if (!$container->canWriteToContainer(0, 'object', 'discussion')) {
	register_error(elgg_echo('actionunauthorized'));
	forward(REFERER);
}

$title = elgg_echo('discussion:addtopic');

elgg_push_breadcrumb($group->name, "discussion/owner/$group->guid");
elgg_push_breadcrumb($title);

$body_vars = discussion_prepare_form_vars();
$content = elgg_view_form('discussion/save', array(), $body_vars);

$params = array(
	'content' => $content,
	'title' => $title,
	'sidebar' => elgg_view('discussion/sidebar/edit'),
	'filter' => '',
);
$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);