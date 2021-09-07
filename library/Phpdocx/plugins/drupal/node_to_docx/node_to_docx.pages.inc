<?php

/**
 * @file
 * Public function to generate a DOCX.
 */

/**
 * Generate DOCX of a node.
 *
 * @return bool
 */
function node_to_docx_generate_docx($node) {

    if (variable_get('node_to_docx_type_' . $node->type) == 0) {
        drupal_goto(urlencode('node') . '/' . $node->nid);
        return;
    }

    if (!node_access('view', $node)) {
        drupal_set_message(t('You are not authorized to generate DOCX for this page.'), 'warning');
        drupal_goto(urlencode('node') . '/' . $node->nid);
        return;
    }
    if (empty($node)) {
        drupal_get_messages('error');
        drupal_set_message(t('DOCX cannot be generated for this path.'), 'error');
        return;
    }

    if (node_to_docx_library_exist() == TRUE) {
        $node_to_docx_filename = $node->nid . ' ' . $node->title;

        $view = node_view($node, 'DOCX');
        $content = drupal_render($view);

        _node_to_docx_generator($content, $node_to_docx_filename);
    } else {
        drupal_set_message(t('phpdocx is not included. Please copy phpdocx to "sites/all/libraries" directory or " /sites/your-domain-name/libraries/ directory " or "!default_module_path" directory.', array(
            '!default_module_path' => drupal_get_path('module', 'node_to_docx'),
        )), 'warning');
        drupal_goto(urlencode('node') . '/' . $node->nid);
        return;
    }
}
