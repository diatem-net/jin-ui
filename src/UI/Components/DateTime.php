<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\UI\Components;

/**
 * Affiche un datetime.
 */
class DateTime extends AbstractUIComponent
{

  /**
   * Implémente la fonction AbstractUIComponent::getType()
   *
   * @return string
   */
  public static function getType()
  {
    return 'datetime';
  }

  /**
   * Retourne la valeur courante
   * @param  string $format  (optional) Format de datetime en sortie. (Par défaut d/m/Y H:i:s)
   * @return string
   */
  public function getValue($format = 'd/m/Y H:i:s')
  {
    return $this->value->format($format);
  }

  /**
   * Définit la valeur courante
   * @param string $value  Datetime sous forme de chaîne de caractères
   */
  public function setValue($value)
  {
    $this->value = new \DateTime($value);
  }

  /**
   * Retourne la valeur au format DateTime
   * @return \DateTime
   */
  public function getDateTimeValue()
  {
    return $this->value;
  }

  /**
   * Définit la valeur courante
   * @param \DateTime $datetime  Objet DateTime
   */
  public function setDateTimeValue(\DateTime $datetime)
  {
    $this->value = $datetime;
  }

  /**
   * Rendu par défaut d'un composant
   *
   * @return  string
   */
  public function render()
  {
    $content = parent::render();
    $content = str_replace('%value%', $this->getValue(), $content);
    return $content;
  }

}
