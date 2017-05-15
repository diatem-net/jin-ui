<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\UI\Components;

/**
 * Affiche une valeur booléenne.
 */
class Currency extends AbstractUIComponent
{

  /**
   * Implémente la fonction AbstractUIComponent::getType()
   *
   * @return string
   */
  public static function getType()
  {
    return 'currency';
  }

  /**
   * Retourne la valeur courante
   * @return string
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * Définit la valeur courante
   * @param string $value  Montant (format libre)
   */
  public function setValue($value)
  {
    $this->value = $value;
  }

}
