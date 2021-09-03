<?php
class MarkovChain
{
  private int $order;
  private $duplicates;
  private $start;

  /**
   * order indicates how many previous characters to take into account when picking the next.
   * A lower number represents more random words, whereas a higher number will result in words
   * that match the input words more closely.
   */
  function __construct(int $order, $words)
  {
    $this->order = $order;
    $this->duplicates = ['children' => []];
    $this->start = ['character' => '', 'neighbors' => []];
    $this->init($words);
  }

  private function init($words)
  {
    $map = [];
    foreach ($words as $word) {
      $this->addToDuplicatesTrie(strtolower($word));
      unset($previous);
      $previous = &$this->start;
      $key = '';
      for ($i = 0; $i < strlen($word); ++$i) {
        $ch = $word[$i];
        $key .= $ch;
        if (strlen($key) > $this->order) {
          $key = substr($key, 1);
        }
        unset($newNode);
        $newNode = &$map[$key] ?? null;
        if (is_null($newNode)) {
          $newNode = ['character' => $ch, 'neighbors' => []];
          $map[$key] = $newNode;
        }
        // array_push($previous['neighbors'], $newNode);
        $previous['neighbors'][] = &$newNode;
        unset($previous);
        $previous = &$newNode;
      }
      //link to end node.
      $previous['neighbors'][] = null;
    }
  }

  /**
   * Adds a word and all its substrings to a duplicates trie to
   * ensure that generated words are never an exact match or substring
   * of a word in the input dictionary. Building a trie allows us
   * to efficiently search for these duplicates later without
   * having to do O(N) comparision checks over the entire dictionary
   */
  private function addToDuplicatesTrie($word)
  {
    if (strlen($word) > 1) {
      $this->addToDuplicatesTrie(substr($word, 1));
    }
    unset($currentNode);
    $currentNode = &$this->duplicates;
    for ($i = 0; $i < strlen($word); ++$i) {
      $childNode = &$currentNode['children'][$word[$i]] ?? null;
      if (is_null($childNode)) {
        $childNode = ['children' => []];
        $currentNode['children'][$word[$i]] = $childNode;
        $currentNode;
      }
      unset($currentNode);
      $currentNode = &$childNode;
    }
  }

  /**
   * Check to see if a word is a match to any substring in the input
   * dictionary in O(N) time, where N is the number of characters in the
   * word rather than the number of words in the dictionary.
   * @param {string} word The word we want to find out whether it is a
   * duplicate of a substring in the input dictionary.
   */
  private function isDuplicate($word)
  {
    $word = strtolower($word);
    $currentNode = $this->duplicates;
    for ($i = 0; $i < strlen($word); ++$i) {
      $childNode = $currentNode['children'][$word[$i]] ?? null;
      if (is_null($childNode)) {
        return false;
      }
      $currentNode = $childNode;
    }
    return true;
  }

  public function generate(
    $minLength = 0,
    $maxLength = 0,
    $allowDuplicates = true,
    $maxAttempts = 25,
    $random = null, # Constant expression contains invalid operations
  ) {
    if (is_null($random)) {
      $random = function () {
        return (float)rand() / (float)getrandmax();
      }; # https://stackoverflow.com/questions/17690165/php-equivalent-of-javascript-math-random
    }

    $word = null;
    $repeat = null;
    $attempts = 0;

    do {
      $repeat = false;
      $nextNodeIndex = floor($random() * count($this->start['neighbors']));
      $currentNode = $this->start['neighbors'][$nextNodeIndex] ?? null;
      $word = '';
      while (!is_null($currentNode) && ($maxLength <= 0 || strlen($word) <= $maxLength)) {
        $word .= $currentNode['character'];
        $nextNodeIndex = floor($random() * count($currentNode['neighbors']));
        $currentNode = $currentNode['neighbors'][$nextNodeIndex] ?? null;
      }
      if (
        ($maxLength > 0 && strlen($word) > $maxLength) ||
        strlen($word) < $minLength
      ) {
        $repeat = true;
      }
    } while (
      // we don't want to output any exact replicas from the input dictionary
      ($repeat || (!$allowDuplicates && $this->isDuplicate($word))) &&
      ($maxAttempts <= 0 || ++$attempts < $maxAttempts)
    );
    if ($maxAttempts > 0 && $attempts >= $maxAttempts) {
      throw new Exception('Unable to generate a word with the given parameters after ' . $attempts . ' attempts');
      // return 'Unable to generate a word with the given parameters after ' . $attempts . ' attempts';
    }
    return $word;
  }
};
