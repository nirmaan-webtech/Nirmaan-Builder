<?php
/**
 * WordPress-backed REST persistence smoke test.
 *
 * Run from the WordPress root:
 *   wp eval-file wp-content/themes/nirmaan-builder/tests/wp/round-trip.php
 *
 * Requires an administrator account with the Nirmaan Builder capabilities.
 */
if (!defined('ABSPATH')) {
    fwrite(STDERR, "Run this file with wp eval-file from a bootstrapped WordPress install.\n");
    exit(1);
}
if (!class_exists('NirmaanBuilder\\Rest')) {
    fwrite(STDERR, "Nirmaan Builder REST class is not loaded. Activate the theme first.\n");
    exit(1);
}
$admins = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
if (!$admins) {
    fwrite(STDERR, "No administrator user found.\n");
    exit(1);
}
wp_set_current_user((int) $admins[0]);
foreach (array('use_nirmaan_builder','use_nirmaan_modules','edit_nirmaan_content') as $cap) {
    if (!current_user_can($cap)) {
        fwrite(STDERR, "Administrator is missing required capability: {$cap}\n");
        exit(1);
    }
}
function nb_test_request($method, $route, $payload = null) {
    $request = new WP_REST_Request($method, $route);
    if (null !== $payload) {
        $request->set_header('content-type', 'application/json');
        $request->set_body(wp_json_encode($payload));
    }
    return rest_do_request($request);
}
function nb_assert($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}
$post_id = wp_insert_post(array('post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Nirmaan Builder REST round-trip test'), true);
if (is_wp_error($post_id)) {
    throw new RuntimeException($post_id->get_error_message());
}
try {
    $first = array('nodes' => array(array('id' => 'roundtrip-heading', 'type' => 'heading', 'settings' => array('content' => 'Version one', 'level' => 2))));
    $second = array('nodes' => array(array('id' => 'roundtrip-heading', 'type' => 'heading', 'settings' => array('content' => 'Version two', 'level' => 2))));
    $saved = nb_test_request('POST', '/nirmaan-builder/v1/content/' . $post_id, $first);
    nb_assert(200 === $saved->get_status(), 'Initial content save failed: ' . wp_json_encode($saved->get_data()));
    $saved = nb_test_request('POST', '/nirmaan-builder/v1/content/' . $post_id, $second);
    nb_assert(200 === $saved->get_status(), 'Second content save failed: ' . wp_json_encode($saved->get_data()));
    $loaded = nb_test_request('GET', '/nirmaan-builder/v1/content/' . $post_id);
    nb_assert(200 === $loaded->get_status(), 'Content reload failed.');
    nb_assert('Version two' === $loaded->get_data()['document']['nodes'][0]['settings']['content'], 'Reloaded document does not match latest save.');
    $revisions = nb_test_request('GET', '/nirmaan-builder/v1/revisions/content/' . $post_id);
    nb_assert(200 === $revisions->get_status() && !empty($revisions->get_data()['revisions']), 'Revision was not recorded.');
    $revision_id = $revisions->get_data()['revisions'][0]['revision_id'];
    $restored = nb_test_request('POST', '/nirmaan-builder/v1/revisions/content/' . $post_id, array('part' => 'body', 'revision_id' => $revision_id));
    nb_assert(200 === $restored->get_status() && !empty($restored->get_data()['restored']), 'Revision restore failed.');
    nb_assert('Version one' === $restored->get_data()['document']['nodes'][0]['settings']['content'], 'Restored revision content is incorrect.');
    $draft = array('nodes' => array(array('id' => 'roundtrip-heading', 'type' => 'heading', 'settings' => array('content' => 'Autosave draft', 'level' => 2))));
    $autosaved = nb_test_request('POST', '/nirmaan-builder/v1/autosave/content/' . $post_id, array('part' => 'body', 'document' => $draft));
    nb_assert(200 === $autosaved->get_status() && !empty($autosaved->get_data()['saved']), 'Autosave write failed.');
    $autosave = nb_test_request('GET', '/nirmaan-builder/v1/autosave/content/' . $post_id . '?part=body');
    nb_assert(200 === $autosave->get_status() && 'Autosave draft' === $autosave->get_data()['autosave']['document']['nodes'][0]['settings']['content'], 'Autosave reload failed.');
    echo "PASS: REST save, reload, revision restore and autosave round-trip.\n";
} finally {
    wp_delete_post($post_id, true);
}
