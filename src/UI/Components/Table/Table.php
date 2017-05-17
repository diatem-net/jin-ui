<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\UI\Components\Table;

use Jin2\UI\Components\AbstractUIComponent;
use Jin2\Db\Query\QueryResult;
use Jin2\Language\Translation;

/**
 * Composant UI Table (à utiliser en adjonction à un objet QueryResult)
 */
class Table extends AbstractUIComponent
{

  /**
   * @var Jin2\Db\Query\QueryResult   Datasource du composant
   */
  protected $datasource;

  /**
   * @var array   Tableau des noms de colonnes
   */
  protected $headers;

  /**
   * @var string  Prefixe utilisé pour les IDS des cellules header
   */
  protected $header_td_idPrefix = '_header_';

  /**
   * @var string  Préfixe utilisé pour les IDS des cellules standard
   */
  protected $line_td_idPrefix = '_line_';

  /**
   * @var int Index de début de parsing
   */
  protected $startIndex = 0;

  /**
   * @var int Index de fin de parsing
   */
  protected $endIndex = -1;

  /**
   * @var TableModel   Instance de TableModel définissant les règles d'affichage des cellules.
   */
  protected $tableModel;

  /**
   * @var array  Classes CSS pour les éléments du composant
   */
  protected $elementClasses = array(
    'th'           => array(),
    'tr'           => array(),
    'td'           => array(),
    'td_alternate' => array(),
    'thead'        => array(),
    'tbody'        => array(),
    'lines'        => array(),
    'columns'      => array()
  );

  /**
   * @var array  Attributs HTML pour les éléments du composant
   */
  protected $elementAttributes = array(
    'lines'        => array()
  );

  /**
   * @var array  Templates pour les éléments du composant
   */
  protected $elementTemplates = array(
    'tbody' => null,
    'thead' => null,
    'tr'    => null,
    'td'    => null,
    'th'    => null
  );

  /**
   * Constructeur
   */
  public function __construct()
  {
    parent::__construct();
    $this->tableModel = new TableModel();
  }

  /**
   * Implémente la fonction AbstractUIComponent::getType()
   *
   * @return string
   */
  public static function getType()
  {
    return 'table';
  }

  /**
   * Effectue le rendu du composant
   *
   * @return string
   */
  public function render()
  {
    $this->datasource->limitResults($this->startIndex, $this->endIndex);
    if ($this->datasource->count() == 0) {
      return Translation::get('table_empty');
    }

    $html = parent::render();
    $html = $this->renderHeaders($html);
    $html = $this->renderLines($html);

    return $html;
  }

  /**
   * Définit les en-tête de colonne. Si un tableau associatif est transmis il permet également de définir les colonnes que l'on souhaite afficher ainsi que l'ordre.
   *
   * @param array $headers    Noms des en-tête de colonne
   */
  public function setHeaders($headers)
  {
    $this->headers = $headers;
  }

  /**
   * Définit un TableModel appliquable à ce composant
   *
   * @param \jin\output\components\ui\table\TableModel $tm    TableModel Appliquable
   */
  public function setTableModel(TableModel $tm)
  {
    $this->tableModel = $tm;
  }

  /**
   * Définit la Datasource (QueryResult) appliquable au composant.
   *
   * @param Jin2\Db\Query\QueryResult $datasource
   */
  public function setDataSource(QueryResult $datasource)
  {
    $this->datasource = $datasource;
  }

  /**
   * Retourne la datasource (QueryResult) utilisé par le composant
   *
   * @return Jin2\Db\Query\QueryResult
   */
  public function getDataSource()
  {
    return $this->datasource;
  }

  /**
   * Redéfinit les index de parsing. Pour un affichage partiel.
   *
   * @param int $startIndex  Index de début de parsing
   * @param int $endIndex    Index de fin de parsing
   */
  public function setParsingIndexes($startIndex, $endIndex = -1)
  {
    $this->startIndex = $startIndex;
    $this->endIndex = $endIndex;
  }

  /**
   * Ajoute une classe CSS à un élement du composant
   *
   * @param  string|array $className  Classe CSS à appliquer
   * @param  string       $element    Element sur lequel appliquer. Element possibles : td,tr,th,tbody,thead
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

    if ($element == 'lines' || $element == 'columns') {
      // Dans le cas de lines et columns, on transmet un array de classes
      if (!is_array($className)) {
        $className = array($className);
      }
      $this->elementClasses[$element] = $className;
      return true;
    } else {
      // Dans les autres cas, on transmet une seule classe
      if (is_string($className) && array_search($className, $this->elementClasses[$element]) === false) {
        $this->elementClasses[$element][] = $className;
        return true;
      }
    }
    return false;
  }

  /**
   * Ajoute des attributs associés aux balises TR, par ligne.
   *
   * @param array $attributesArray	Tableau d'attributs. array(array('attr1NameLigne1' => 'val', 'attr2NameLigne2' => 'val'), array('attr1NameLigne2' => 'val'))
   */
  public function addAttributeByLine($attributesArray)
  {
    $this->elementAttributes['lines'] = $attributesArray;
  }

  /**
   * Effectue le rendu des headers
   *
   * @param  string $html  Html généré
   * @return string
   * @throws \Exception
   */
  protected function renderHeaders($html)
  {
    // On génère le contenu du THEAD
    $thead_content = $this->getElementTemplate('thead');

    // On génère le contenu du TR
    $tr_content = $this->getElementTemplate('tr');

    // On génère les headers qui doivent etre utilisés
    $headersUsed;
    if ($this->headers) {
      $hc = count($this->datasource->getHeaders());
      if ($hc == 0) {
        $hc = count($this->headers);
      }
      if ($hc != count($this->headers) && array_keys($this->headers) === range(0, count($this->headers) - 1)) {
        throw new \Exception('Le nombre d\'en-tête de colonne transmis dans les headers ne correspond pas aux données');
      }
      $headersUsed = $this->headers;
    } else {
      $headersUsed = $this->datasource->getHeaders();
    }

    // On génère le contenu du TH
    $th_content = $this->getElementTemplate('th');

    // On génère les colonnes
    $cols = '';
    $colI = 0;
    foreach ($headersUsed as $h) {
      $hc = $th_content;
      $hc = str_replace('%id%', $this->getId() . $this->header_td_idPrefix . $colI, $hc);
      $hc = str_replace('%value%', $h, $hc);

      $classes = '';
      if (isset($this->elementClasses['columns'][$h])) {
        if (is_array($this->elementClasses['columns'][$h])) {
          $classes = implode(' ', $this->elementClasses['columns'][$h]);
        } else {
          $classes = $this->elementClasses['columns'][$h];
        }
      } else if (isset($this->elementClasses['columns'][$colI])) {
        if (is_array($this->elementClasses['columns'][$colI])) {
          $classes = implode(' ', $this->elementClasses['columns'][$colI]);
        } else {
          $classes = $this->elementClasses['columns'][$colI];
        }
      }
      $hc = str_replace('%columnclass%', $classes, $hc);

      $cols .= $hc;
      $colI++;
    }

    // On remplace dans le TR
    $tr_content = str_replace('%items%', $cols, $tr_content);

    // On remplace dans le THEAD
    $thead_content = str_replace('%items%', $tr_content, $thead_content);

    // On remplace dans le HTML
    $html = str_replace('%headers%', $thead_content, $html);

    return $html;
  }

  /**
   * Effectue le rendu des lignes
   *
   * @param  string $html  Html généré
   * @return string
   */
  protected function renderLines($html)
  {
    $tbody_content = $this->getElementTemplate('tbody');

    $this->datasource->limitResults($this->startIndex, $this->endIndex);

    $lines = '';
    $i = 0;
    foreach ($this->datasource as $l) {
      $lines .= $this->renderLine($l, $i);
      $i++;
    }

    $tbody_content = str_replace('%items%', $lines, $tbody_content);
    $html = str_replace('%items%', $tbody_content, $html);

    return $html;
  }

  /**
   * Effectue le rendu d'une ligne
   *
   * @param array $line   Données de la ligne
   * @param int $lineNum  Numéro de la ligne
   * @return string
   */
  protected function renderLine($line, $lineNum)
  {
    // On génère le contenu du TR (avec le modificateur)
    $tr_content = $this->getElementTemplate('tr', $lineNum);

    // On génère le contenu du TD (avec le modificateur)
    $td_content = $this->getElementTemplate('td', $lineNum % 2 == 0);

    // On génère les colonnes
    $heads = $this->datasource->getHeaders();
    if ($this->headers && array_keys($this->headers) !== range(0, count($this->headers) - 1)) {
      $heads = array_keys($this->headers);
    }

    $cols = '';
    $colI = 0;
    foreach ($heads as $h) {
      $hc = $td_content;
      $hc = str_replace('%id%', $this->getId() . $this->header_td_idPrefix . $colI . '_' . $lineNum, $hc);

      $headName = '';
      if (isset($this->headers[$colI])) {
        $headName = $this->headers[$colI];
      }
      $hc = str_replace('%value%', $this->tableModel->renderCell($this->getId(), $h, $headName, $colI, $lineNum, $line[$h]), $hc);

      $classes = '';
      if (isset($this->elementClasses['columns'][$h])) {
        if (is_array($this->elementClasses['columns'][$h])) {
          $classes = implode(' ', $this->elementClasses['columns'][$h]);
        } else {
          $classes = $this->elementClasses['columns'][$h];
        }
      } else if (isset($this->elementClasses['columns'][$colI])) {
        if (is_array($this->elementClasses['columns'][$colI])) {
          $classes = implode(' ', $this->elementClasses['columns'][$colI]);
        } else {
          $classes = $this->elementClasses['columns'][$colI];
        }
      }
      $hc = str_replace('%columnclass%', $classes, $hc);

      $cols .= $hc;

      $colI++;
    }

    // On remplace dans le TR
    $tr_content = str_replace('%items%', $cols, $tr_content);

    return $tr_content;
  }

  /**
   * Retourne le template appliquable à un élément du composant
   *
   * @param  mixed  $modifier  Modificateur, à utiliser pour td (boolean) et tr (integer)
   * @return string
   */
  protected function getElementTemplate($element, $modifier = null)
  {
    $element = strtolower($element);
    if (!array_key_exists($element, $this->elementTemplates)) {
      throw new \Exception(sprintf('Element %s non reconnu.', $element));
      return '';
    }

    if (is_null($modifier) && !is_null($this->elementTemplates[$element])) {
      return $this->elementTemplates[$element];
    }

    $content = static::getAssetContent($element);
    switch ($element) {
      case 'th':
      case 'td':
        $classes = $this->elementClasses[$element];
        if ($modifier === true) {
          $classes = array_merge($classes, $this->elementClasses['td_alternate']);
        }
        $content = str_replace('%class%', '%columnclass%' . implode(' ', $classes), $content);
        break;
      case 'tr':
        $classes    = $this->elementClasses[$element];
        $attributes = '';
        if (is_integer($modifier)) {
          if (isset($this->elementClasses['lines'][$modifier + $this->startIndex])) {
            $classes = array_merge($classes, $this->elementClasses['lines'][$modifier + $this->startIndex]);
          }
          if (isset($this->elementAttributes['lines'][$modifier + $this->startIndex])) {
            foreach($this->elementAttributes['lines'][$modifier + $this->startIndex] AS $key => $value){
              $attributes .= sprintf(' %s="%s"', $key, $value);
            }
          }
        }
        $content = str_replace('%class%', implode(' ', $classes), $content);
        $content = str_replace('%attributes%', $attributes, $content);
        break;
      case 'thead':
      case 'tbody':
        $content = str_replace('%class%', implode(' ', $this->elementClasses[$element]), $content);
        break;
    }

    if (is_null($modifier)) {
      $this->elementTemplates[$element] = $content;
    }
    return $content;
  }

}
