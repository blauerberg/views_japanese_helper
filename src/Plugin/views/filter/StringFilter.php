<?php

namespace Drupal\views_japanese_helper\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;

/**
 * Basic textfield filter to handle string filtering commands
 * including equality, like, not like, etc.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("japanese_string")
 */
class StringFilter extends \Drupal\views\Plugin\views\filter\StringFilter {

  protected function opContainsWord($field) {
    $where = $this->operator == 'word' ? db_or() : db_and();

    // Don't filter on empty strings.
    if (empty($this->value)) {
      return;
    }

    preg_match_all(static::WORDS_PATTERN, ' ' . mb_convert_kana($this->value, 's'), $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
      $phrase = FALSE;
      // Strip off phrase quotes
      if ($match[2]{0} == '"') {
        $match[2] = substr($match[2], 1, -1);
        $phrase = TRUE;
      }
      $words = trim($match[2], ',?!();:-');
      $words = $phrase ? array($words) : preg_split('/ /', $words, -1, PREG_SPLIT_NO_EMPTY);
      foreach ($words as $word) {
        $where->condition($field, '%' . db_like(trim($word, " ,!?")) . '%', 'LIKE');
      }
    }

    if (!$where) {
      return;
    }

    // previously this was a call_user_func_array but that's unnecessary
    // as views will unpack an array that is a single arg.
    $this->query->addWhere($this->options['group'], $where);
  }
}
