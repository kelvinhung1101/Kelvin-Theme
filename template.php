<?php

require_once(drupal_get_path('theme', 'kelvin') . "/phpQuery-onefile.php");
require_once(drupal_get_path('theme', 'kelvin') . "/kelvin_helper.php");

/**
 * Implements template_preprocess_html().
 */
function kelvin_preprocess_html(&$vars) {
  if (!empty($vars['page']['featured'])) {
    $vars['classes_array'][] = 'featured';
  }

  if ($vars['is_admin']) {
    $vars['classes_array'][] = 'admin';
  }

  $vars['classes_array'][] = 'dir-' . $vars['language']->dir;

  if (!$vars['is_front']) {
    // Add unique classes for each page and website section
    $path = drupal_get_path_alias($_GET['q']);
    $temp = explode('/', $path, 2);
    $section = array_shift($temp);
    $page_name = array_shift($temp);

    if (isset($page_name)) {
      $vars['classes_array'][] = drupal_html_id('page-' . $page_name);
    }

    $vars['classes_array'][] = drupal_html_id('section-' . $section);

    if (arg(0) == 'node') {
      if (arg(1) == 'add') {
        if ($section == 'node') {
          array_pop($vars['classes_array']); // Remove 'section-node'
        }
        $vars['classes_array'][] = 'section-node-add'; // Add 'section-node-add'
      } elseif (is_numeric(arg(1)) && (arg(2) == 'edit' || arg(2) == 'delete')) {
        if ($section == 'node') {
          array_pop($vars['classes_array']); // Remove 'section-node'
        }
        $vars['classes_array'][] = 'section-node-' . arg(2); // Add 'section-node-edit' or 'section-node-delete'
      }
    }
  }

  if (isset($vars['head_title_array']['title'])) {
    $temp = str_replace("&amp;", "", $vars['head_title_array']['title']);
    $vars['classes_array'][] = preg_replace('/\W+/', '-', strtolower($temp));
  }

  $title = "";
  if (isset($vars['title'])) {
    $title = $vars['title'];
  } elseif (isset($vars['head_title_array']['title'])) {
    $title = $vars['head_title_array']['title'];
  }
  $tmp = to_id($title);
  $vars['classes_array'][] = $tmp;
  $vars['theme_hook_suggestions'][] = 'page__' . $tmp;
}

/**
 * Implements template_preprocess_page().
 */
function kelvin_preprocess_page(&$vars) {
  if (isset($vars['node_title'])) {
    $vars['title'] = $vars['node_title'];
  }

  // Site navigation links.
  $vars['main_menu_links'] = '';
  //  var_dump($vars);
  if (isset($vars['main_menu'])) {
    $vars['main_menu_links'] = theme('links__system_main_menu', array(
      'links' => $vars['main_menu'],
      'attributes' => array(
        'id' => 'main-menu-links',
        'class' => array('inline', 'main-menu'),
      ),
      'heading' => array(
        'text' => t('Main menu'),
        'level' => 'h2',
        'class' => array('element-invisible'),
      ),
    ));
  }
  $vars['secondary_menu_links'] = '';
  if (isset($vars['secondary_menu'])) {
    $vars['secondary_menu_links'] = theme('links__system_secondary_menu', array(
      'links' => $vars['secondary_menu'],
      'attributes' => array(
        'id' => 'secondary-menu-links',
        'class' => array('inline', 'secondary-menu'),
      ),
      'heading' => array(
        'text' => t('Secondary menu'),
        'level' => 'h2',
        'class' => array('element-invisible'),
      ),
    ));
  }

  // Since the title and the shortcut link are both block level elements,
  // positioning them next to each other is much simpler with a wrapper div.
  if (!empty($vars['title_suffix']['add_or_remove_shortcut']) && $vars['title']) {
    // Add a wrapper div using the title_prefix and title_suffix render elements.
    $vars['title_prefix']['shortcut_wrapper'] = array(
      '#markup' => '<div class="shortcut-wrapper clearfix">',
      '#weight' => 100,
    );
    $vars['title_suffix']['shortcut_wrapper'] = array(
      '#markup' => '</div>',
      '#weight' => -99,
    );
    // Make sure the shortcut link is the first item in title_suffix.
    $vars['title_suffix']['add_or_remove_shortcut']['#weight'] = -100;
  }
}

/**
 * Implements template_preprocess_node().
 *
 * Adds extra classes to node container for advanced theming
 */
function kelvin_preprocess_node(&$vars) {
  // Striping class
  $vars['classes_array'][] = 'node-' . $vars['zebra'];

  // Node is published
  $vars['classes_array'][] = ($vars['status']) ? 'published' : 'unpublished';

  // Node has comments?
  $vars['classes_array'][] = ($vars['comment']) ? 'with-comments' : 'no-comments';

  if ($vars['sticky']) {
    $vars['classes_array'][] = 'sticky'; // Node is sticky
  }

  if ($vars['promote']) {
    $vars['classes_array'][] = 'promote'; // Node is promoted to front page
  }

  if ($vars['teaser']) {
    $vars['classes_array'][] = 'node-teaser'; // Node is jisplayed as teaser.
  }

  if ($vars['uid'] && $vars['uid'] === $GLOBALS['user']->uid) {
    $classes[] = 'node-mine'; // Node is authored by current user.
  }

  $vars['submitted'] = t('Submitted by !username on ', array('!username' => $vars['name']));
  $vars['submitted_date'] = t('!datetime', array('!datetime' => $vars['date']));
  $vars['submitted_pubdate'] = format_date($vars['created'], 'custom', 'Y-m-d\TH:i:s');

  $type = $vars['node']->type;
  if ($type == 'page') $type = "basicpage";
  $alias = to_id(drupal_get_path_alias('node/' . $vars['node']->nid));
  $vars['theme_hook_suggestions'][] = "node__" . $alias;
}

/**
 * Implements template_preprocess_block().
 */
function kelvin_preprocess_block(&$vars, $hook) {
  // Add a striping class.
  $vars['classes_array'][] = 'block-' . $vars['zebra'];

  $vars['title_attributes_array']['class'][] = 'block-title';

  // In the header region visually hide block titles.
  if ($vars['block']->region == 'header') {
    $vars['title_attributes_array']['class'][] = 'element-invisible';
  }
}

function kelvin_css_alter(&$style) {
  // Unset Drupal's annoying preset css
  unset($style['modules/system/system.theme.css']);
  unset($style['modules/system/system.menus.css']);
  unset($style['modules/user/user.css']);
}
