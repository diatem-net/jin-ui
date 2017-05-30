<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\UI\Components;

use Jin2\Assets\AssetsInterface;
use Jin2\Language\Translation;

/**
 * Classe parent de tout composant de type UI
 */
abstract class AbstractUIComponent implements AssetsInterface
{

  /**
   * @var string  Id du composant
   */
  protected $id = '';

  /**
   * @var string Personnalisation de la balise style
   */
  protected $style = '';

  /**
   * @var array Classes appliquées
   */
  protected $classes = array();

  /**
   * @var array Attributs ajoutés
   */
  protected $attributes = array();

  /**
   * @var string|array  Valeur actuelle.
   */
  protected $value = null;

  /**
   * Constructeur
   */
  public function __construct()
  {
    $root =  __DIR__
      . DIRECTORY_SEPARATOR .'..'
      . DIRECTORY_SEPARATOR .'..'
      . DIRECTORY_SEPARATOR .'..'
      . DIRECTORY_SEPARATOR .'languages';
    Translation::addStorage($root, 0);
    Translation::loadFile('uicomponents');
  }

  /**
   * Retourne le type de composant
   *
   * @return string
   */
  abstract public static function getType();

  /**
   * Implements getAssetUrl function
   *
   * @param string $key
   * @return string
   */
  public static function getAssetUrl($key)
  {
    $root =  __DIR__
      . DIRECTORY_SEPARATOR .'..'
      . DIRECTORY_SEPARATOR .'..'
      . DIRECTORY_SEPARATOR .'..'
      . DIRECTORY_SEPARATOR .'assets'
      . DIRECTORY_SEPARATOR .static::getType();
    return sprintf('%s%s%s.tpl', $root, DIRECTORY_SEPARATOR, $key);
  }

  /**
   * Implements getAssetContent function
   *
   * @param string $key
   * @return string
   */
  public static function getAssetContent($key)
  {
    if ($url = static::getAssetUrl($key)) {
      return file_get_contents($url, FILE_USE_INCLUDE_PATH);
    }
    return null;
  }

  /**
   * Retourne l'identifiant
   *
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Modifie l'identifiant
   *
   * @param string $id
   */
  public function setId($id)
  {
    $this->id = $id;
  }

  /**
   * Définit ce qui sera affiché dans la balise style du composant
   *
   * @param string $style Déclaration CSS
   */
  public function setStyle($style)
  {
    $this->style = $style;
  }

  /**
   * Retourne ce qui est affiché dans la balise style du composant
   */
  public function getStyle()
  {
    return $this->style;
  }

  /**
   * Applique une nouvelle classe CSS
   *
   * @param  string $className Nom de la classe à appliquer
   * @return boolean  Retourne FALSE si cette classe était déjà appliquée
   */
  public function addClass($className)
  {
    if (!is_numeric(array_search($className, $this->classes))) {
      $this->classes[] = $className;
      return true;
    }
    return false;
  }

  /**
   * Supprime une classe CSS appliquée
   *
   * @param  string $className Nom de la classe à supprimer
   * @return boolean  Retourne FALSE si cette classe n'était pas appliquée
   */
  public function removeClass($className)
  {
    $pos = array_search($className, $this->classes);
    if (is_numeric($pos)) {
      unset($this->classes[$pos]);
      return true;
    }
    return false;
  }

  /**
   * Retourne un tableau des classes CSS appliquées
   *
   * @return array
   */
  public function getClasses()
  {
    return $this->classes;
  }

  /**
   * Ajoute un nouvel attribut
   *
   * @param  string $attributeName  Nom de l'attribut
   * @param  string $attributeValue Value de l'attribut
   * @return boolean               Retourne FALSE si cet atribut était déjà ajouté
   */
  public function addAttribute($attributeName, $attributeValue)
  {
    if (!array_key_exists($attributeName, $this->attributes)) {
      $this->attributes[$attributeName] = $attributeValue;
      return true;
    }
    return false;
  }

  /**
   * Supprime un attribut ajouté
   *
   * @param  string $attributeName Nom de l'attribut
   * @return boolean              Retourne FALSE si cet attribut n'était pas ajouté
   */
  public function removeAttribute($attributeName)
  {
    if (isset($this->attributes[$attributeName])) {
      unset($this->attributes[$attributeName]);
      return true;
    }
    return false;
  }

  /**
   * Retourne un tableau des attributs ajoutés
   * @return array
   */
  public function getAttributes(){
      return $this->attributes;
  }

  /**
   * Rendu par défaut d'un composant
   *
   * @return  string
   */
  public function render()
  {
    return $this->replaceMagicFields(static::getAssetContent('html'));
  }

  /**
   * Remplace les champs magiques des assets - concernant uniquement les champs magiques des composants de type UI
   * @param  string $html  HTML à inspeter
   * @return string
   */
  public function replaceMagicFields($html)
  {
    $html = str_replace('%id%', $this->getId(), $html);
    $html = str_replace('%class%', implode(' ', $this->classes), $html);
    $html = str_replace('%style%', $this->getStyle(), $html);

    $strAttributes = '';
    foreach ($this->attributes as $key => $value) {
      $strAttributes .= ' ' . $key . '="' . $value . '"';
    }
    $html = str_replace('%attributes%', $strAttributes, $html);
    return $html;
  }

}