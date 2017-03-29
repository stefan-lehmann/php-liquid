<?php

/**
 * This implements an abstract file system which retrieves template files named in a manner similar to Rails partials,
 * ie. with the template name prefixed with an underscore. The extension ".liquid" is also added.
 *
 * For security reasons, template paths are only allowed to contain letters, numbers, and underscore.
 *
 * @package Liquid
 * @copyright Copyright (c) 2011-2012 Harald Hanek,
 * fork of php-liquid (c) 2006 Mateo Murphy,
 * based on Liquid for Ruby (c) 2006 Tobias Luetke
 * @license http://harrydeluxe.mit-license.org
 */
class LiquidLocalFileSystem extends LiquidBlankFileSystem
{
  /**
   * The root path
   *
   * @var string
   */
  private $_root;


  /**
   * Constructor
   *
   * @param string $root The root path for templates
   * @return LiquidLocalFileSystem
   */
  public function __construct($root)
  {

    $this->_root = $root;
  }


  /**
   * Retrieve a template file
   *
   * @param string $templatePath
   * @return string
   */
  public function readTemplateFile($templatePath)
  {
    if (!($fullPath = $this->fullPath($templatePath))) {
      throw new LiquidException("No such template '$templatePath'");
    }
    return file_get_contents($fullPath);
  }


  /**
   * Resolves a given path to a full template file path, making sure it's valid
   *
   * @param string $templatePath
   * @return string
   */
  public function fullPath($templatePath)
  {
    return LIQUID_INCLUDE_ALLOW_EXT ? $this->getFullPathFromPath($templatePath) : $this->getFullPathFromName($templatePath);
  }

  private function getFullPathFromPath($templatePath)
  {

    $nameRegex = new LiquidRegexp('/^[^.\/][a-zA-Z0-9_\.\/\-]+$/');

    if (!$nameRegex->match($templatePath)) {
      throw new LiquidException("Illegal template name '$templatePath'");
    }

    $fullPath = (strpos($templatePath, '/') !== false)
        ? $this->_root . dirname($templatePath) . '/' . basename($templatePath)
        : $this->_root . $templatePath;

    $rootRegex = new LiquidRegexp('/' . preg_quote(realpath($this->_root), '/') . '/');

    if (!$rootRegex->match(realpath($fullPath))) {
      throw new LiquidException("Illegal template path '" . realpath($fullPath) . "'");
    }

    return $fullPath;
  }

  private function getFullPathFromName($templatePath)
  {

    $nameRegex = new LiquidRegexp('/^[^.\/][a-zA-Z0-9_\-\/]+$/');

    if (!$nameRegex->match($templatePath)) {
      throw new LiquidException("Illegal template name '$templatePath'");
    }

    $filename = basename($templatePath);

    $separator = '-';
    $filenameSegments = explode($separator, $filename);
    $section = array_shift($filenameSegments);
    $longname = implode($separator, $filenameSegments);
    $group = array_shift($filenameSegments);
    $name = implode($separator, $filenameSegments);

    switch($section) {
      case 'svg':
        $pattern = "#/.*/$longname.svg#i";
        break;

      default:
        $pattern = "#(.*/)?$section/([0-9]{2}-)?$group(/.*)?/$name.".LIQUID_INCLUDE_SUFFIX."#i";
    }

    $dir = new RecursiveDirectoryIterator($this->_root);
    $ite = new RecursiveIteratorIterator($dir);
    $matches = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
    foreach ($matches as $match) {
      return $match[0];
    }

    $fullPath = (strpos($templatePath, '/') !== false)
        ? $this->_root . dirname($templatePath) . '/' . LIQUID_INCLUDE_PREFIX . basename($templatePath) . '.' . LIQUID_INCLUDE_SUFFIX
        : $this->_root . $templatePath . '.' . LIQUID_INCLUDE_SUFFIX;

    return $fullPath;
  }
}
