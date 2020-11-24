<?php

class AtomParser {

    var $atomElements = []; // Atom elements
    var $atomData = []; // data from within Atom document
    //var $xmlValues = []; // values output from xml_parse_into_struct
    //var $xmlIndex = []; // index to xmlValues
    //var $encodingType = []; // Atom document's encoding, if specified in the document
    var $dateFormat = '';

    function __construct($file, $cacheDir = '', $dateFormat = '', $cacheExpires = 3000) {
        if ($dateFormat !== '') {
            $this->dateFormat = $dateFormat;
        }
        if ($cacheDir !== '') {
            $cacheFile = $cacheDir . '/atomcache_' . md5($file);
            if (file_exists($cacheFile) && time() < filemtime($cacheFile) + $cacheExpires) {
                // cache file is new enough
                $this->atomData = unserialize(join('', file($cacheFile)));
                // set 'cached' to 1 only if cached file is correct
                if ($this->atomData) {
                    $this->atomData['cached'] = 1;
                }
            } else {
                // cache file does not exist or is too old--create a new one
                $this->parse($file);
                $serialized = serialize($this->atomData);
                if ($f = fopen($cacheFile, 'w')) {
                    fwrite($f, $serialized, strlen($serialized));
                    fclose($f);
                }
                if ($this->atomData) {
                    $this->atomData['cached'] = 0;
                }
            }
        } else {
            // cache is disabled; load and parse the file directly
            $this->parse($file);
            if ($this->atomData) {
                $this->atomData['cached'] = 0;
            }
        }
    }
    
    function parse($file) {
        $xMLParser = xml_parser_create("");
        if ($xMLParser !== false) {
            xml_parser_set_option($xMLParser, XML_OPTION_CASE_FOLDING, 0); // turns off annoying case-folding (default is on and to all-caps)
            xml_set_object($xMLParser, $this); // allows use of parser within object
            xml_set_element_handler($xMLParser, "startElement", "endElement"); // assigns start and end event handlers for each element
            xml_set_character_data_handler($xMLParser, "dataHandler"); // assigns data event handler
            $fileHandle = fopen($file, "r");
            if ($fileHandle !== false) {
                while ($data = fread($fileHandle, 4096)) {
                    if (!xml_parse($xMLParser, $data, feof($fileHandle))) {
                        //"Atom feed parsing error (" . xml_get_current_line_number($xml_parser) . ") : " . xml_error_string(xml_get_error_code($xml_parser)) . "\n"
                    }
                }
                fclose($fileHandle);
            } else {
                //"Failed to open Atom feed: $file\n"
            }
            xml_parser_free($xMLParser);
            unset($xMLParser);
            $this->addConvertedPubDate();
        } else {
            //"Failed to create XML parser--make sure package xml-php is installed.\n"
        }
    }

//    function parse($file) {
//        $xMLParser = xml_parser_create("");
//        if ($xMLParser !== false) {
//            xml_parser_set_option($xMLParser, XML_OPTION_CASE_FOLDING, 0); // turns off annoying case-folding (default is on and to all-caps)
//            $data = file_get_contents($file);
//            if ($data !== false) {
//                if (!xml_parse_into_struct($xMLParser, $data, $this->xmlValues, $this->xmlIndex)) {
//                    //"Atom feed parsing error (" . xml_get_current_line_number($xMLParser) . ") : " . xml_error_string(xml_get_error_code($xMLParser)) . "\n"
//                }
//            } else {
//                //"Failed to get Atom feed: $file\n"
//            }
//            xml_parser_free($xMLParser);
//            unset($xMLParser);
//
//            $levels = []; // init the multiple-levels stacking array; note that the top level in the values output is 1, not 0
//            file_put_contents("/var/lib/torrentwatch-xa/dl_cache/stack.txt", "");
//            foreach ($this->xmlValues as $value) {
//                // loop through the values output from xml_parse_into_struct (we ignore the index)
//                file_put_contents("/var/lib/torrentwatch-xa/dl_cache/stack.txt", "value ====> " . print_r($value, true) . "\n", FILE_APPEND);
//                //file_put_contents("/var/lib/torrentwatch-xa/dl_cache/stack.txt", "levels 1 ====> " . print_r($levels, true) . "\n", FILE_APPEND);
//                if ($value['type'] === 'open' || $value['type'] === 'complete') { // if the value type is an open or complete
//                    if (!array_key_exists($value['level'], $levels)) { // and the value level isn't in the levels array
//                        $levels[$value['level']] = []; // create a blank array in the levels array with the value level as key
//                    }
//                }
//                //file_put_contents("/var/lib/torrentwatch-xa/dl_cache/stack.txt", "levels 2 ====> " . print_r($levels, true) . "\n", FILE_APPEND);
//                $oneLevelAbove = $value['level'] - 1; // number of the level one level above this one
//                $parentIndex = count($levels[$oneLevelAbove]) - 1;
//
//                switch ($value['type']) {
//                    case 'open':
//                        $value['children'] = []; // init a new children array
//                        $levels[$value['level']][] = $value; // push the value array onto the end of this level
//                        break;
//                    case 'close':
//                        $pop = array_pop($levels[$value['level']]); // detach the last element at the specified level--should be the matching open for this close
//                        $tag = $pop['tag'];
//
//                        if ($value['level'] > 1) { // check if there's a level above this one (top level is 1)
//                            if (!array_key_exists($tag, $levels[$oneLevelAbove][$parentIndex]['children'])) { // is this closing tag not in the parent's children?
//                                $levels[$oneLevelAbove][$parentIndex]['children'][$tag] = $pop['children']; // assign $pop's children to $levels at $tag
//                            } else if (is_array($levels[$oneLevelAbove][$parentIndex]['children'][$tag])) { // check if the parent's children has this tag already
//                                $levels[$oneLevelAbove][$parentIndex]['children'][$tag][] = $pop['children']; // if it does, add $pop's children to $levels at tail end of $tag
//                            }
//                        } else { // no parent level, we should be done with the whole XML document
//                            $this->atomData = [$pop['tag'] => $pop['children']];
//                        }
//                        break;
//                    case 'complete':
//                        //file_put_contents("/var/lib/torrentwatch-xa/dl_cache/hit.txt", $value['tag'] . " = " . $value['value'] . "\n", FILE_APPEND);
//                        // find the current insertion point (not the parent)
//                        // look for value or attributes
//                        // handle multiple identical tags at same insertion point
//                        $levels[$oneLevelAbove][$parentIndex]['children'][$value['tag']] = $value['value'];
//                }
//                file_put_contents("/var/lib/torrentwatch-xa/dl_cache/stack.txt", "levels END ====> " . print_r($levels, true) . "\n", FILE_APPEND);
//            }
//
//            file_put_contents("/var/lib/torrentwatch-xa/dl_cache/atomData.txt", print_r($this->atomData, true));
//
//            // final call to copy and convert 'published' to 'pubDate'
//            $this->addConvertedPubDate();
//        } else {
//            //"Failed to create XML parser--make sure package xml-php is installed.\n"
//        }
//    }

    function startElement($parser, $elementName, $attributes) {
        // NOTE: we assume that the element names are not case-folded and that the ones we care about happen to be lowercase
        if ($this->encodingType) {
            // content is encoded, so keep elements intact
            $tmpData = "<$elementName";
            if ($attributes) {
                foreach ($attributes as $key => $val) {
                    $tmpData .= " $key=\"$val\"";
                }
            }
            $tmpData .= ">";
            $this->dataHandler($parser, $tmpData);
        } else {
            if (!empty($attributes['href']) && !empty($attributes['rel']) && $attributes['rel'] == 'alternate') {
                $this->startElement($parser, 'link', []);
                $this->dataHandler($parser, $attributes['href']);
                $this->endElement($parser, 'link');
            }
            if (!empty($attributes['type'])) {
                $this->encodingType[$elementName] = $attributes['type'];
            }
            if (preg_match("/^(feed|entry)$/", $elementName)) {
                if ($this->atomElements) {
                    $depth = count($this->atomElements);
                    //list($parent, $num) = each($tmp = end($this->atomElements));
                    list($parent, $num) = each(end($this->atomElements)); //TODO fix "PHP Notice:  Only variables should be passed by reference"
                    if ($parent) {
                        $this->atomElements[$depth - 1][$parent][$elementName]++;
                    }
                }
                array_push($this->atomElements, array($elementName => []));
            } else {
                array_push($this->atomElements, $elementName);
            }
        }
    }

    function endElement($parser, $elementName) {
        // remove tag from tags array
        if ($this->encodingType) {
            if (isset($this->encodingType[$elementName])) {
                unset($this->encodingType[$elementName]);
                array_pop($this->atomElements);
            } else {
                if (!preg_match("/(br|img)/i", $elementName)) {
                    $this->dataHandler($parser, "</$elementName>");
                }
            }
        } else {
            array_pop($this->atomElements);
        }
    }

    function dataHandler($parser, $data) {
        if (trim($data)) {
            $evalCode = "\$this->atomData";
            foreach ($this->atomElements as $atomElement) {
                if (is_array($atomElement)) {
                    list($elementName, $indexes) = each($atomElement);
                    $evalCode .= "[\"$elementName\"]";
                    if (${$elementName}) {
                        $evalCode .= "[" . (${$elementName} - 1) . "]";
                    }
                    if ($indexes) {
                        extract($indexes);
                    }
                } else {
                    if (preg_match("/^([A-Za-z]+):([A-Za-z]+)$/", $atomElement, $matches)) {
                        $evalCode .= "[\"$matches[1]\"][\"$matches[2]\"]";
                    } else {
                        $evalCode .= "[\"$atomElement\"]";
                    }
                }
            }
            eval("$evalCode .= '" . addslashes($data) . "';");
        }
    }

    function changeDataEncoding($input, $outputEncoding) {
        if (function_exists('mb_detect_encoding')) {
            if (is_array($input)) {
                $encoding = mb_detect_encoding(print_r($input, true));
            } else if (is_string($input)) {
                $encoding = mb_detect_encoding($input);
            }
            switch ($encoding) {
                case 'ASCII':
                case $outputEncoding:
                    return $input;
                case '':
                    return mb_convert_encoding($input, $outputEncoding);
                default:
                    return mb_convert_encoding($input, $outputEncoding, $encoding);
            }
        } else {
            return $input;
        }
    }

    function getParsedData($outputEncoding = 'UTF-8') {
        // return data as array in specified encoding
        return $this->changeDataEncoding($this->atomData, $outputEncoding);
    }

    function addConvertedPubDate() {
        // loops through this->atomData and copies and converts 'published' elements to 'pubDate' elements
        for ($i = 0; $i < count($this->atomData['feed']['entry']); $i++) {
            $atomPubDate = $this->atomData['feed']['entry'][$i]['published'];
            if ($this->dateFormat !== '') {
                $convertedPubDate = date($this->dateFormat, strtotime($atomPubDate));
                if ($convertedPubDate !== false && $convertedPubDate !== '') {
                    $this->atomData['feed']['entry'][$i]['pubDate'] = $convertedPubDate;
                } else {
                    $this->atomData['feed']['entry'][$i]['pubDate'] = $this->atomData['feed']['entry'][$i]['published'];
                }
            } else {
                $this->atomData['feed']['entry'][$i]['pubDate'] = $this->atomData['feed']['entry'][$i]['published'];
            }
        }
    }

}
