<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\UI\Components;

use Jin2\UI\Components\Table\Table;

/**
 * Affiche une date.
 */
class Pagination extends AbstractUIComponent
{

  /**
  * @var Jin2\UI\Table\Table  Composant UI Table sur lequel effectuer la pagination
  */
  private $targetTableComponent;

  /**
  * @var int	Nombre maximum de résultats par page
  */
  private $maxByPage = 20;

  /**
  * @var boolean Préserve les autres paramètres GET
  */
  private $preserveQueryString = true;

  /**
  * @var int	Page courante
  */
  private $currentPage = 1;

  /**
  * @var string  Nom de l'argument transmis en GET pour modifier la pagination (Dans un fonctionnement classique)
  */
  private $argumentName = 'p';

  /**
  * @var int Nombre max de pages affichées (nombre impair)
  */
  private $maxShowedPages = 3;

  /**
  * @var boolean Définit que l'utilisateur a forcé l'affichage d'une page spécifique. (Sinon déterminé automatiquement)
  */
  private $forcedPage = false;

  /**
  * @var int	Nombre de pages
  */
  private $nbPages = 0;

  /**
  * @var int	Nombre total de résultats
  */
  private $resultsCount;

  /**
  * @var array  Classes CSS pour les éléments du composant
  */
  private $elementClasses = array(
    'first'        => array(),
    'last'         => array(),
    'page'         => array(),
    'selectedpage' => array(),
    'next'         => array(),
    'previous'     => array()
  );

  /**
  * @var array  Templates pour les éléments du composant
  */
  private $elementTemplates = array(
    'first'        => null,
    'last'         => null,
    'page'         => null,
    'selectedpage' => null,
    'next'         => null,
    'previous'     => null
  );

  /**
   * Surcharge du constructeur pour récupérer la page actuelle
   */
  function __construct()
  {
    parent::__construct();

    if (isset($_GET[$this->argumentName]) && !$this->forcedPage) {
      $this->currentPage = $_GET[$this->argumentName];
    }
  }

  /**
   * Implémente la fonction AbstractUIComponent::getType()
   *
   * @return string
   */
  public static function getType()
  {
    return 'pagination';
  }

  /**
   * Effectue un rendu du composant
   *
   * @return string
   */
  public function render()
  {
    $html = parent::render();

    $items = '';

    // Affichage lien FIRST
    if ($this->currentPage > 1) {
      $first = $this->getElementTemplate('first');
      $first = str_replace('%page%', 1, $first);
      $items .= $first;
    }

    // Affichage previous
    if ($this->currentPage > 1) {
      $prev = $this->getElementTemplate('previous');
      $prev = str_replace('%page%', $this->currentPage - 1, $prev);
      $items .= $prev;
    }

    // Affichage des pages
    $startPage = $this->currentPage - floor($this->maxShowedPages / 2);
    $endPage   = $this->currentPage + floor($this->maxShowedPages / 2);
    if ($startPage < 1) {
      $endPage += (1 - $startPage);
      $startPage = 1;
    }
    if ($endPage > $this->nbPages) {
      $endPage = $this->nbPages;
    }
    if (($endPage - $startPage + 1) != $this->maxShowedPages) {
      $startPage = $endPage - $this->maxShowedPages + 1;
      if ($startPage < 1) {
        $startPage = 1;
      }
    }
    for ($i = $startPage; $i <= $endPage; $i++) {
      if ($this->currentPage == $i) {
        $page = $this->getElementTemplate('selectedpage');
      } else {
        $page = $this->getElementTemplate('page');
      }
      $page = str_replace('%page%', $i, $page);
      $items .= $page;
    }

    // Affichage next
    if ($this->currentPage < $this->nbPages) {
      $next = $this->getElementTemplate('next');
      $next = str_replace('%page%', $this->currentPage + 1, $next);
      $items .= $next;
    }

    // Affichage last
    if ($this->currentPage != $this->nbPages && $this->nbPages > 0) {
      $last = $this->getElementTemplate('last');
      $last = str_replace('%page%', $this->nbPages, $last);
      $items .= $last;
    }

    $html = str_replace('%items%', $items, $html);

    return $html;
  }

  /**
   * Définit le composant UI Table sur lequel s'applique la pagination
   *
   * @param Jin2\UI\Table\Table  $table
   */
  public function setTable(Table $table)
  {
    $this->targetTableComponent = $table;
    $this->updateTable();
  }

  /**
   * Redéfinit le nombre maximum de résultats à afficer par page
   *
   * @param int $nb	Nombre de résultats
   */
  public function setMaxResultsByPage($nb)
  {
    $this->maxByPage = $nb;
    $this->updateTable();
  }

  /**
   * Retourne le nombre maximum de résultats à afficer par page
   *
   * @return int
   */
  public function getMaxResultsByPage()
  {
    return $this->maxByPage;
  }

  /**
   * Choisit de préserver ou non la QueryString à travers les pages
   *
   * @param boolean $preserve Choix (true = préserver)
   */
  public function preserveQueryString($preserve)
  {
    $this->preserveQueryString = $preserve;
    $this->updateTable();
  }

  /**
   * Redéfinit la page courante
   *
   * @param int $page Numéro de page. (1 = première page)
   */
  public function setCurrentPage($page)
  {
    $this->currentPage = $page;
    $this->forcedPage = true;
    $this->updateTable();
  }

  /**
   * Retourne le numéro dee la page courante (1 = première page)
   *
   * @return int
   */
  public function getCurrentPage()
  {
    return $this->currentPage;
  }

  /**
   * Redéfinit le nom de l'argument transmis en GET pour modifier la pagination
   *
   * @param string $argumentName
   */
  public function setArgumentName($argumentName)
  {
    $this->argumentName = $argumentName;
  }

  /**
   * Retourne le nom de l'argument actuellement transmis en GET pour modifier la pagination
   *
   * @return string
   */
  public function getArgumentName()
  {
    return $this->argumentName;
  }

  /**
   * Définit le nombre de résultats, pour calculer le nombre de pages
   *
   * @param integer $resultsCount
   */
  public function setResultsCount($resultsCount)
  {
    $this->resultsCount = $resultsCount;
    $this->nbPages = ceil($this->resultsCount / $this->maxByPage);
  }

  /**
   * Retourne le nombre de pages
   *
   * @return integer
   */
  public function getNbPages()
  {
    return $this->nbPages;
  }

  /**
   * Ajoute une classe CSS appliquable à un élément du composant.
   *
   * @param  string $className  Classe CSS à appliquer
   * @param  string $element    Element sur lequel appliquer la classe. (first, last, page, selectedpage, next, previous)
   * @return boolean
   * @throws \Exception
   */
  public function addElementClass($className, $element)
  {
    $element = strtolower($element);
    if (!array_key_exists($element, $this->elementClasses)) {
      throw new \Exception(sprintf('Element %s non reconnu.', $element));
      return false;
    }

    if (array_search($className, $this->elementClasses[$element]) === false) {
      $this->elementClasses[$element][] = $className;
      return true;
    }
    return false;
  }

  /**
   * Met à jour les données de la Table liée après une modification des paramètres
   */
  private function updateTable()
  {
    if ($this->targetTableComponent !== null) {
      if (!$this->resultsCount) {
        $ds = $this->targetTableComponent->getDataSource();
        $this->setResultsCount($ds->count());
      }

      $imin = ($this->currentPage - 1) * $this->maxByPage;
      $imax = $imin + $this->maxByPage - 1;

      if ($imax > ($this->resultsCount - 1)) {
        $imax = $this->resultsCount - 1;
      }

      $this->targetTableComponent->setParsingIndexes($imin, $imax);
    }
  }

  /**
   * Retourne la QueryString à utiliser, selon le paramètre preserveQueryString défini
   *
   * @return string
   */
  private function getQueryString()
  {
    $qs = '?';
    if ($this->preserveQueryString && isset($_GET) && count($_GET) > 0) {
      foreach ($_GET as $key => $value) {
        if ($key != $this->argumentName) {
          $qs .= $key . '=' . $value . '&';
        }
      }
    }
    return $qs;
  }

  /**
   * Retourne le template appliquable à un élément du composant
   *
   * @return string
   */
  private function getElementTemplate($element)
  {
    $element = strtolower($element);
    if (!array_key_exists($element, $this->elementTemplates)) {
      throw new \Exception(sprintf('Element %s non reconnu.', $element));
      return '';
    }

    if (!is_null($this->elementTemplates[$element])) {
      return $this->elementTemplates[$element];
    }

    $content = static::getAssetContent($element);
    $content = str_replace('%class%', implode(' ', $this->elementClasses[$element]), $content);
    $content = str_replace('%querystring%', $this->getQueryString(), $content);
    $content = str_replace('%argumentname%', $this->argumentName, $content);
    $this->elementTemplates[$element] = $content;
    return $content;
  }

}

