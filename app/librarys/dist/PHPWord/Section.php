<?php
/**
 * PHPWord
 *
 * Copyright (c) 2011 PHPWord
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 010 PHPWord
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    Beta 0.6.3, 08.07.2011
 */


/**
 * PHPWord_Section
 *
 * @category   PHPWord
 * @package    PHPWord_Section
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Section {
	
	/**
	 * Section count
	 * 
	 * @var int
	 */
	private $_sectionCount;
	
	/**
	 * Section settings
	 * 
	 * @var PHPWord_Section_Settings
	 */
	private $_settings;
	
	/**
	 * Section Element Collection
	 * 
	 * @var array
	 */
	private $_elementCollection = array();
	
	/**
	 * Section Header
	 * 
	 * @var PHPWord_Section_Header
	 */
	private $_header = null;
	
	/**
	 * Section Footer
	 * 
	 * @var PHPWord_Section_Footer
	 */
	private $_footer = null;
	
	
	/**
	 * Create a new Section
	 * 
	 * @param int $sectionCount
	 * @param mixed $settings
	 */
	public function __construct($sectionCount, $settings = null) {
		$this->_sectionCount = $sectionCount;
		$this->_settings = new PHPWord_Section_Settings();
		
		if(!is_null($settings) && is_array($settings)) {
			foreach($settings as $key => $value) {
				if(substr($key, 0, 1) != '_') {
					$key = '_'.$key;
				}
				$this->_settings->setSettingValue($key, $value);
			}
		}
	}
	
	/**
	 * Get Section Settings
	 * 
	 * @return PHPWord_Section_Settings
	 */
	public function getSettings() {
		return $this->_settings;
	}
	
	/**
	 * Add a Text Element
	 * 
	 * @param string $text
	 * @param mixed $styleFont
	 * @param mixed $styleParagraph
	 * @return PHPWord_Section_Text
	 */
	public function addText($text, $styleFont = null, $styleParagraph = null) {
		$givenText = utf8encode_dummy($text);
		$text = new PHPWord_Section_Text($givenText, $styleFont, $styleParagraph);
		$this->_elementCollection[] = $text;
		return $text;
	}
	private function runDOMNode(DOMNode $domNode, $tree, $styles = [], $params = []) {
		foreach ($domNode->childNodes as $node) {
			$tree2 = $tree;
			$styles2 = $styles;
			$params2 = $params;
			$src = null;

			if ($node->hasAttributes()) {
				foreach ($node->attributes as $attr) {
					$name = $attr->nodeName;
					$value = $attr->nodeValue;
					if($name == 'style' && !empty($value)) {
						preg_match_all("/([\w-]+)\s*:\s*([^;]+)\s*;?/", $value, $matches, PREG_SET_ORDER);
						if(!empty($matches)) {
							foreach ($matches as $match) {
								$results[$match[1]] = $match[2];
								if($match[1] == 'font-size') {
									$styles2['size'] = (int) $match[2];
								} elseif($match[1] == 'text-align') {
									$params2['align'] = $match[2];
								} elseif($match[1] == 'vertical-align') {
									$params2['valign'] = $match[2];
								} elseif($match[1] == 'width') {
									$params2['width'] = (int) $match[2];
								} elseif($match[1] == 'height') {
									$params2['height'] = (int) $match[2];
								} elseif($match[1] == 'color') {
									$styles2['color'] = str_replace('#', '', $match[2]);
								} elseif($match[1] == 'background') {
									$styles2['bgColor'] = str_replace('#', '', $match[2]);
                } elseif($match[1] == 'border-color') {
                  $styles2['borderColor'] = str_replace('#', '', $match[2]);
                } elseif($match[1] == 'border-size') {
                  $styles2['borderSize'] = (int) $match[2];
                } elseif($match[1] == 'border-style') {
                  $styles2['border'] = $match[2];
                }
							}
						}
					} elseif($name == 'src' && !empty($value)) {
						$src = $value;
					} elseif($name == 'width' && !empty($value)) {
						$params2['width'] = (int) $value;
					} elseif($name == 'height' && !empty($value)) {
						$params2['height'] = (int) $value;
					}
				}
			}

			if($node->nodeName == 'table') {
				$tree2 = $tree->addTable();

			} elseif($node->nodeName == 'tr') {
				$h = !empty($params2['height']) ? $params2['height'] : 25;
				$tree2->addRow($h * 900 / 50);

			} elseif($node->nodeName == 'td') {
				$w = !empty($params2['width']) ? $params2['width'] : 50;
				$tree2 = $tree->addCell($w * 9000 / 1000, $styles2);

			} elseif($node->nodeName == 'th') {
				$styles2['bold'] = true;
				$w = !empty($params2['width']) ? $params2['width'] : 50;
				$tree2 = $tree->addCell($w * 9000 / 1000, $styles2);

			} elseif($node->nodeName == 'ul') {
				$tree2->addTextBreak(1);

			} elseif($node->nodeName == 'br') {
				$tree2->addTextBreak(1);

			} elseif($node->nodeName == 'p') {
				$tree2 = $tree->createTextRun();

			} elseif($node->nodeName == 'h1') {
				$styles2['size'] = 30;

			} elseif($node->nodeName == 'h2') {
				$styles2['size'] = 20;

			} elseif($node->nodeName == 'h3') {
				$styles2['size'] = 15;

			} elseif(in_array($node->nodeName, ['b','strong'])) {
				$styles2['bold'] = true;

			} elseif($node->nodeName == 'i') {
				$styles2['italic'] = true;
			}

			$text = !is_null($node->textContent) && $node->textContent != '' ? trim($node->textContent) : null;
			if($node->hasChildNodes()) {
				self::runDOMNode($node, $tree2, $styles2, $params2);

			} elseif($node->nodeName == '#text' && !is_null($text) && strlen($text) > 1) {
				$tree2->addText($node->textContent, $styles2, $params2);

			} elseif($node->nodeName == 'li') {
				$listStyle = array('listType'=>PHPWord_Style_ListItem::TYPE_NUMBER_NESTED);
				$tree2->addListItem($node->textContent, 0, null, $listStyle, null);

			} elseif($node->nodeName == 'img' && !empty($src)) {
				$tree2->addImage($src, $params2);
			}
		}
	}
	public function addHtml($code) {
		$doc = new DOMDocument();
		$doc->loadHTML($code);
		self::runDOMNode($doc, $this);
	}
	public function addFile($file, $data = null) {
		if(!file_exists($file)) {
			_404('file-no-exists');
			return;
		}
		if(!is_null($data)) {
			extract($data);
		}
		ob_start();
    	require($file);
    $html = ob_get_contents();
		return $this->addHtml($html);
	}
	/**
	 * Add a Link Element
	 * 
	 * @param string $linkSrc
	 * @param string $linkName
	 * @param mixed $styleFont
	 * @param mixed $styleParagraph
	 * @return PHPWord_Section_Link
	 */
	public function addLink($linkSrc, $linkName = null, $styleFont = null, $styleParagraph = null) {
		$linkSrc = utf8encode_dummy($linkSrc);
		if(!is_null($linkName)) {
			$linkName = utf8encode_dummy($linkName);
		}
		
		$link = new PHPWord_Section_Link($linkSrc, $linkName, $styleFont, $styleParagraph);
		$rID = PHPWord_Media::addSectionLinkElement($linkSrc);
		$link->setRelationId($rID);
		
		$this->_elementCollection[] = $link;
		return $link;
	}
	
	/**
	 * Add a TextBreak Element
	 * 
	 * @param int $count
	 */
	public function addTextBreak($count = 1) {
		for($i=1; $i<=$count; $i++) {
			$this->_elementCollection[] = new PHPWord_Section_TextBreak();
		}
	}
	
	/**
	 * Add a PageBreak Element
	 */
	public function addPageBreak() {
		$this->_elementCollection[] = new PHPWord_Section_PageBreak();
	}
	
	/**
	 * Add a Table Element
	 * 
	 * @param mixed $style
	 * @return PHPWord_Section_Table
	 */
	public function addTable($style = null) {
		$table = new PHPWord_Section_Table('section', $this->_sectionCount, $style, $this->_settings);
		$this->_elementCollection[] = $table;
		return $table;
	}
	
	/**
	 * Add a ListItem Element
	 * 
	 * @param string $text
	 * @param int $depth
	 * @param mixed $styleFont
     * @param mixed $styleList
	 * @param mixed $styleParagraph
	 * @return PHPWord_Section_ListItem
	 */
	public function addListItem($text, $depth = 0, $styleFont = null, $styleList = null, $styleParagraph = null) {
		$text = utf8encode_dummy($text);
		$listItem = new PHPWord_Section_ListItem($text, $depth, $styleFont, $styleList, $styleParagraph);
		$this->_elementCollection[] = $listItem;
		return $listItem;
	}
	
	/**
	 * Add a OLE-Object Element
	 * 
	 * @param string $src
	 * @param mixed $style
	 * @return PHPWord_Section_Object
	 */
	public function addObject($src, $style = null) {
		$object = new PHPWord_Section_Object($src, $style);
		
		if(!is_null($object->getSource())) {
			$inf = pathinfo($src);
			$ext = $inf['extension'];
			if(strlen($ext) == 4 && strtolower(substr($ext, -1)) == 'x') {
				$ext = substr($ext, 0, -1);
			}
			
			$iconSrc = PHPWORD_BASE_PATH . 'PHPWord/_staticDocParts/';
			if(!file_exists($iconSrc.'_'.$ext.'.png')) {
				$iconSrc = $iconSrc.'_default.png';
			} else {
				$iconSrc .= '_'.$ext.'.png';
			}
			
			$rIDimg = PHPWord_Media::addSectionMediaElement($iconSrc, 'image');
			$data = PHPWord_Media::addSectionMediaElement($src, 'oleObject');
			$rID = $data[0];
			$objectId = $data[1];
			
			$object->setRelationId($rID);
			$object->setObjectId($objectId);
			$object->setImageRelationId($rIDimg);
			
			$this->_elementCollection[] = $object;
			return $object;
		} else {
			trigger_error('Source does not exist or unsupported object type.');
		}
	}
	
	/**
	 * Add an Image Element
	 * 
	 * @param string $src
	 * @param mixed $style
	 * @return PHPWord_Section_Image
	 */
	public function addImage($src, $style = null) {
		$image = new PHPWord_Section_Image($src, $style);
		
		if(!is_null($image->getSource())) {
			$rID = PHPWord_Media::addSectionMediaElement($src, 'image');
			$image->setRelationId($rID);
			
			$this->_elementCollection[] = $image;
			return $image;
		} else {
			trigger_error('Source does not exist or unsupported image type.');
		}
	}
	
	/**
	 * Add a by PHP created Image Element
	 * 
	 * @param string $link
	 * @param mixed $style
	 * @return PHPWord_Section_MemoryImage
	 */
	public function addMemoryImage($link, $style = null) {
		$memoryImage = new PHPWord_Section_MemoryImage($link, $style);
		if(!is_null($memoryImage->getSource())) {
			$rID = PHPWord_Media::addSectionMediaElement($link, 'image', $memoryImage);
			$memoryImage->setRelationId($rID);
			
			$this->_elementCollection[] = $memoryImage;
			return $memoryImage;
		} else {
			trigger_error('Unsupported image type.');
		}
	}
	
	/**
	 * Add a Table-of-Contents Element
	 * 
	 * @param mixed $styleFont
	 * @param mixed $styleTOC
	 * @return PHPWord_TOC
	 */
	public function addTOC($styleFont = null, $styleTOC = null) {
		$toc = new PHPWord_TOC($styleFont, $styleTOC);
		$this->_elementCollection[] = $toc;
		return $toc;
	}
	
	/**
	 * Add a Title Element
	 * 
	 * @param string $text
	 * @param int $depth
	 * @return PHPWord_Section_Title
	 */
	public function addTitle($text, $depth = 1) {
		$text = utf8encode_dummy($text);
		$styles = PHPWord_Style::getStyles();
		if(array_key_exists('Heading_'.$depth, $styles)) {
			$style = 'Heading'.$depth;
		} else {
			$style = null;
		}
		
		$title = new PHPWord_Section_Title($text, $depth, $style);
		
		$data = PHPWord_TOC::addTitle($text, $depth);
		$anchor = $data[0];
		$bookmarkId = $data[1];
		
		$title->setAnchor($anchor);
		$title->setBookmarkId($bookmarkId);
		
		$this->_elementCollection[] = $title;
		return $title;
	}
	
	/**
	 * Create a new TextRun
	 * 
	 * @return PHPWord_Section_TextRun
	 */
	public function createTextRun($styleParagraph = null) {
		$textRun = new PHPWord_Section_TextRun($styleParagraph);
		$this->_elementCollection[] = $textRun;
		return $textRun;
	}
	
	/**
	 * Get all Elements
	 * 
	 * @return array
	 */
	public function getElements() {
		return $this->_elementCollection;
	}
	
	/**
	 * Create a new Header
	 * 
	 * @return PHPWord_Section_Header
	 */
	public function createHeader() {
		$header = new PHPWord_Section_Header($this->_sectionCount, $this->_settings);
		$this->_header = $header;
		return $header;
	}
	
	/**
	 * Get Header
	 * 
	 * @return PHPWord_Section_Header
	 */
	public function getHeader() {
		return $this->_header;
	}
	
	/**
	 * Create a new Footer
	 * 
	 * @return PHPWord_Section_Footer
	 */
	public function createFooter() {
		$footer = new PHPWord_Section_Footer($this->_sectionCount, $this->_settings);
		$this->_footer = $footer;
		return $footer;
	}
	
	/**
	 * Get Footer
	 * 
	 * @return PHPWord_Section_Footer
	 */
	public function getFooter() {
		return $this->_footer;
	}
}
?>
