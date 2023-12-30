<?php

/**
 * Class with methods for conversion from remarkup to markdown .md files.
 */
class PhrictionToMd{


    /**
     * Database connection.
     *
     * @var \PDO
     */
    protected $pdo;


    /**
     * Configuration.
     *
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->pdo = Helpers::getDbConnection();
        $this->config = Helpers::getConfig();
    }


    /**
     * Writes all .md files.
     *
     * @return string
     */
    public function exportAll($export_dir){

        $phriction_data = Helpers::getPhrictionData();

        echo "Writing .md files:\n";
        foreach ($phriction_data as $phriction_page){

            $path = $this->getFilePath($export_dir, $phriction_page['revisions'][0]['slug'], $phriction_page['revisions'][0]['title']);
            $content = $this->convert($phriction_page['revisions'][0]['content']);

            if($content && $this->writeMdFile($path, $content)){
                echo "  $path\n";
            }
        }
    }


    /**
     * Returns path to .md file.
     *
     * @param $export_dir
     * @param $slug
     * @param $title
     * @return string
     */
    protected function getFilePath($export_dir, $slug, $title){

        $path = trim($slug);
        $path = ltrim($path, '/');
        $path = rtrim($path, '/');
        $path = dirname($path);

        if($path == '.' or $path == '/')
            $path = '';

        if($path)
            $path = $export_dir . '/' . $path . '/' . $this->getFileName($title);
        else
            $path = $export_dir  . '/' . $this->getFileName($title);

        return $path;
    }


    /**
     * Writes an .md file.
     *
     * @return bool
     */
    protected function writeMdFile($file, $content){

        $directory = dirname($file);
        if (!file_exists($directory)) {
            mkdir($directory);
        }

        return file_put_contents($file, $content);
    }


    /**
     * Returns name for .md file.
     *
     * @param $document_title
     * @return string
     */
    protected function getFileName($document_title){
        $file_name = str_replace(array('/'), array('-'), $document_title);
        $file_name .= '.md';
        return $file_name;
    }


    /**
     * Converts remarkup to markdown.
     *
     * @param string $content remarkup content
     * @return string
     */
    protected function convert($content){

        $all_tags = array_values($this->config['tags']);
        $default_modifiers = 'mui'; // default modifiers
        $keep_blocks =[];
        $block_index = 0;

        // convert all new lines to \n
        $content = Helpers::unixNewLines($content);

        // convert new lines
        //$content = $this->convertNewLines($content);

        // keep blocks that must stay as is
        foreach ($all_tags as $tags){
            if(isset($tags['keep_block_content'])){

                $block_index ++;

                if(isset($tags['modifiers']))
                    $modifiers = $tags['modifiers'];
                else
                    $modifiers = $default_modifiers;

                if(isset($tags['ph']['start']) && isset($tags['ph']['end'])) {
                    $search = '#' . $tags['ph']['start'] . '(.*?)' . $tags['ph']['end'] . '#' . $modifiers;
                }

                preg_match_all($search, $content, $matches);

                if(isset($matches[1])){
                    $keep_blocks[$block_index] = $matches[1];
                }

                $replace = $tags['ph']['start'] . "INSERT_BLOCK_{$block_index}_PLACEHOLDER" . $tags['ph']['end'];
                $content = preg_replace($search, $replace, $content);
            }
        }

        // convert new lines
        $content = $this->convertNewLines($content);

        // replace all tags
        foreach ($all_tags as $tags){

            if(isset($tags['add_modifier']))
                $modifiers = $tags['modifiers'];
            else
                $modifiers = $default_modifiers;

            if(isset($tags['ph']['start']) && isset($tags['ph']['end'])) {
                $search = '#' . $tags['ph']['start'] . '(.*?)' . $tags['ph']['end'] . '#' . $modifiers;
                $replace = $tags['md']['start'] . '$1' . $tags['md']['end'];
            }

            if(isset($tags['ph']['sarch']) && isset($tags['md']['replace'])) {
                $search = '#' . $tags['ph']['sarch'] . '#' . $modifiers;
                $replace = $tags['md']['replace'];
            }

            $content = preg_replace($search, $replace, $content);
        }

        // convert links
        $content = $this->convertLinks($content);

        $content = $this->convertNumberedLists($content);

        // convert tables
        //$content = $this->convertTables($content);

        // return blocks
        if($keep_blocks){
            foreach ($keep_blocks as $block_index => $tag_blocks){
                $placeholder = "INSERT_BLOCK_{$block_index}_PLACEHOLDER";
                foreach ($tag_blocks as $block){;
                    // replace only first occurrence of placeholder
                    $pos = strpos($content, $placeholder);
                    if ($pos !== false) {
                        $content = substr_replace($content, $block, $pos, strlen($placeholder));
                    }
                }
            }
        }

        return $content;
    }


    /**
     * Converts phriction links to markdown links.
     *
     * @param string $content phriction wiki text
     * @return string
     */
    protected function convertLinks($content){
        $content = preg_replace_callback('/\[\[(.*?)\]\]/u', array($this, 'makeMarkdownLinks'), $content);
        return $content;
    }


    /**
     * Helper for convertLinks() method.
     *
     * @param array $matches
     * @return string|null
     */
    protected function makeMarkdownLinks($matches=array()) {

        $url = $name = null;

        if(isset($matches[1])) {

            $link_content = $matches[1];

            if(stripos($link_content, '|') !== false){
                $link_content = explode('|', $link_content);
                $url = $link_content[0];
                $name = $link_content[1];
            }
            else{
                $url = $link_content;
            }
        }
        else{
            $return = '';
        }

        $url_info = parse_url($url);

        // external link
        if(isset($url_info['scheme'])) {
            if($name)
                $return = "[$name]($url)";
            else
                $return = $url;
        }
        // internal link
        else {
            $document = Helpers::getDocumentBySlug($url);
            if($document){
                $md_file = $this->getFileName($document['title']);
                if($name)
                    $return = "[$name]($md_file)";
                else
                    $return = "[$md_file]($md_file)";
            }
            else{
                $return = "[$name]($url)"; // document not found [[$url|$name]]
            }
        }

        return $return;
    }


    /**
     * Converts remarkup numbered lists to markdown lists.
     *
     * @param $content
     * @return string
     */
    protected function convertNumberedLists($content) {
        $lines = explode("\n", $content);
        $counter = 0;
        foreach ($lines as $i => $line) {
            if (preg_match('/^ # /', $line)) {
                $counter++;
            } else {
                $counter = 0;
            }
            $lines[$i] = preg_replace('/^ # /', $counter . '. ', $line, -1);

        }

        return implode("\n", $lines);
    }


    /**
     * Converts newlines.
     *
     * Remarkup uses new line for a <br>, markdown two or more spaces at the end of line.
     *
     * @param string $content remarkup
     * @return string
     */
    protected function convertNewLines($content){

        $lines = explode("\n", $content);
        foreach ($lines as $line_num => $line) {
            // is text line
            if (isset($lines[$line_num+1]) && $this->isTextLine($lines[$line_num]) && $this->isTextLine($lines[$line_num+1])) {
                $lines[$line_num] = $lines[$line_num] . "  ";
            }
        }
        $content = implode("\n", $lines);

        // keep max 2 new lines
        //$content = preg_replace('/(\n){3,}/m', "\n\n", $content);

        return $content;
    }


    /**
     * Checks if line is text in remarkup.
     *
     * @param $content
     * @return false|int
     */
    protected function isTextLine($content){
        return preg_match('/^([a-z]|[A-Z]|[0-9]|\*\*|!!|\{|@|##|#\{|\/|~~|__|\[|`)/', $content);
    }


    /**
     * Converts file with remarkup content to markdown.
     *
     * @param string $file_path
     * @return string
     */
    public function convertFile($file_path){
        $phriction_content = file_get_contents($file_path);
        return $this->convert($phriction_content);
    }

}
