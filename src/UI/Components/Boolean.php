<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\UI\Components;

/**
 * Affiche une valeur booléenne.
 */
class Boolean extends AbstractUIComponent
{

  /**
   * Implémente la fonction AbstractUIComponent::getType()
   *
   * @return string
   */
  public static function getType()
  {
    return 'boolean';
  }

  /**
    * Effectue le rendu du composant
    * @return string
    */
  public function render()
  {
    $html = parent::render();

    if (is_null($this->value)) {
      $html = str_replace('%value%', '', $html);
    } else if ($this->value) {
      $content = static::getAssetContent('true');
      $html = str_replace('%value%', $content, $html);
    } else {
      $content = static::getAssetContent('false');
      $html = str_replace('%value%', $content, $html);
    }
    return $html;
  }

  /**
   * Retourne la valeur courante
   * @return boolean
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
    * Définit la valeur du composant
    * @param mixed $value  Valeur (boolean ou 1|0)
    * @throws \Exception
    */
  public function setValue($value)
  {
    if (is_null($value)) {
      $this->value = null;
    } else if (is_bool($value)) {
      $this->value = $value;
    } else if (is_numeric($value)) {
      if ($value == 0) {
        $this->value = false;
      } else{
        $this->value = true;
      }
    } else if ($value == '') {
      $this->value = null;
    } else{
      throw new \Exception(sprintf('Valeur %s non appliquable a un composant de type Boolean', $value));
    }
  }

}
