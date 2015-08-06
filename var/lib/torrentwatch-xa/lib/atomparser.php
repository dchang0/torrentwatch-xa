<?PHP
    # Original PHP code by Chirp Internet: www.chirp.com.au
    # Please acknowledge use of this code by including this header.

    class myAtomParser
    {
        # keeps track of current and preceding elements
        var $tags = array();

        # array containing all feed data
        var $output = array();

        # return value for display functions
        var $retval = "";

        var $encoding = array();

        # default caching time (50min)

        # constructor for new object
        function myAtomParser($file, $cache_dir = '', $cache_time = 3000)
        {
            // Cache routine from lastRSS.php
            if($cache_dir != '') {
                $cache_file = $cache_dir . '/atomcache_' . md5($file);
                $timedif = @(time() - filemtime($cache_file));
                if($timedif < $cache_time) {
                    // New Enough
                    $this->output = unserialize(join('', file($cache_file)));
                    //set 'cached' to 1 only if cached file is correct
                    if($this->output) $this->output['cached'] = 1;
                } else {
                    // cached file is too old, create new
                    $this->Parse($file);
                    $serialized = serialize($this->output);
                    if ($f = @fopen($cache_file, 'w')) {
                        fwrite ($f, $serialized, strlen($serialized));
                        fclose($f);
                    }
                    if ($this->output) $this->output['cached'] = 0;
                }
            }
            // If CACHE DISABLED >> load and parse the file directly
            else {
                $this->Parse($file);
                if($this->output) $this->output['cached'] = 0;
            }
            return;
        }

        // Old Constructor
        function Parse($file) {
            # instantiate xml-parser and assign event handlers
            $xml_parser = xml_parser_create("");
            xml_set_object($xml_parser, $this);
            xml_set_element_handler($xml_parser, "startElement", "endElement");
            xml_set_character_data_handler($xml_parser, "parseData");

            # open file for reading and send data to xml-parser
            $fp = fopen($file, "r") or die("myAtomParser: Could not open $file for input");
            while($data = fread($fp, 4096)) {
                xml_parse($xml_parser, $data, feof($fp)) or die(
                    sprintf("myAtomParser: Error <b>%s</b> at line <b>%d</b><br>",
                    xml_error_string(xml_get_error_code($xml_parser)),
                    xml_get_current_line_number($xml_parser))
                );
            }
            fclose($fp);

            # dismiss xml parser
            xml_parser_free($xml_parser);
        }

        function startElement($parser, $tagname, $attrs)
        {
            if($this->encoding) {
                # content is encoded - so keep elements intact
                $tmpdata = "<$tagname";
         if($attrs) foreach($attrs as $key => $val) $tmpdata .= " $key=\"$val\"";
                $tmpdata .= ">";
                $this->parseData($parser, $tmpdata);
            } else {
                if($attrs['HREF'] && $attrs['REL'] && $attrs['REL'] == 'alternate') {
                    $this->startElement($parser, 'LINK', array());
                    $this->parseData($parser, $attrs['HREF']);
                    $this->endElement($parser, 'LINK');
                }
                if($attrs['TYPE']) $this->encoding[$tagname] = $attrs['TYPE'];

                # check if this element can contain others - list may be edited
                if(preg_match("/^(FEED|ENTRY)$/", $tagname)) {
                    if($this->tags) {
                        $depth = count($this->tags);
                        list($parent, $num) = each($tmp = end($this->tags));
                        if($parent) $this->tags[$depth-1][$parent][$tagname]++;
                    }
                    array_push($this->tags, array($tagname => array()));
                } else {
                    # add tag to tags array
                    array_push($this->tags, $tagname);
                }
            }
        }

        function endElement($parser, $tagname)
        {
            # remove tag from tags array
            if($this->encoding) {
                if(isset($this->encoding[$tagname])) {
                    unset($this->encoding[$tagname]);
                    array_pop($this->tags);
                } else {
                    if(!preg_match("/(BR|IMG)/", $tagname)) $this->parseData($parser, "</$tagname>");
                }
            } else {
                array_pop($this->tags);
            }
        }

        function parseData($parser, $data)
        {
            # return if data contains no text
            if(!trim($data)) return;
            $evalcode = "\$this->output";
            foreach($this->tags as $tag) {
                if(is_array($tag)) {
                    list($tagname, $indexes) = each($tag);
                    $evalcode .= "[\"$tagname\"]";
                    if(${$tagname}) $evalcode .= "[" . (${$tagname} - 1) . "]";
                    if($indexes) extract($indexes);
                } else {
                    if(preg_match("/^([A-Z]+):([A-Z]+)$/", $tag, $matches)) {
                        $evalcode .= "[\"$matches[1]\"][\"$matches[2]\"]";
                    } else {
                        $evalcode .= "[\"$tag\"]";
                    }
                }
            }

            if(isset($this->encoding['CONTENT']) && $this->encoding['CONTENT'] == "text/plain") {
                $data = "<pre>$data</pre>";
            }

            eval("$evalcode .= '" . addslashes($data) . "';");
        }

        # display a single feed as HTML
        function display_feed($data, $limit)
        {
            extract($data);
            if($TITLE) {
                # display feed information
                $this->retval .= "<h1>";
                if($LINK) $this->retval .= "<a href=\"$LINK\" target=\"_blank\">";
                $this->retval .= stripslashes($TITLE);
                if($LINK) $this->retval .= "</a>";
                $this->retval .= "</h1>\n";
                if($TAGLINE) $this->retval .= "<P>" . stripslashes($TAGLINE) . "</P>\n\n";
                $this->retval .= "<div class=\"divider\"><!-- --></div>\n\n";
            }
            if($ENTRY) {
                # display feed entry(s)
                foreach($ENTRY as $item) {
                    $this->display_entry($item, "FEED");
                    if(is_int($limit) && --$limit <= 0) break;
                }
            }
        }

        # display a single entry as HTML
        function display_entry($data, $parent)
        {
            extract($data);
            if(!$TITLE) return;

            $this->retval .=    "<p><b>";
            if($LINK) $this->retval .=    "<a href=\"$LINK\" target=\"_blank\">";
            $this->retval .= stripslashes($TITLE);
            if($LINK) $this->retval .= "</a>";
            $this->retval .=    "</b>";
            if($ISSUED) $this->retval .= " <small>($ISSUED)</small>";
            $this->retval .=    "</p>\n";

            if($AUTHOR) {
                $this->retval .=    "<P><b>Author:</b> " . stripslashes($AUTHOR['NAME']) . "</P>\n\n";
            }
            if($CONTENT) {
                $this->retval .=    "<P>" . stripslashes($CONTENT) . "</P>\n\n";
            } elseif($SUMMARY) {
                $this->retval .=    "<P>" . stripslashes($SUMMARY) . "</P>\n\n";
            }
        }

        function fixEncoding($input, $output_encoding)
        {
            return $input;
            //TODO figure out why below code is bypassed by return $input above
            $encoding = mb_detect_encoding($input);
            switch($encoding) {
                case 'ASCII':
                case $output_encoding:
                    return $input;
                case '':
                    return mb_convert_encoding($input, $output_encoding);
                default:
                    return mb_convert_encoding($input, $output_encoding, $encoding);
            }
        }

        # display entire feed as HTML
        function getOutput($limit=false, $output_encoding='UTF-8')
        {
            $this->retval = "";
            $start_tag = key($this->output);

            switch($start_tag) {
                case "FEED":
                    foreach($this->output as $feed) $this->display_feed($feed, $limit);
                    break;
                default:
                    die("Error: unrecognized start tag '$start_tag' in getOutput()");
            }

            return $this->fixEncoding($this->retval, $output_encoding);
        }

        # return raw data as array
        function getRawOutput($output_encoding='UTF-8')
        {
            return $this->fixEncoding($this->output, $output_encoding);
        }
    }
?>
