<?php

/**
 * CLI script for exporting Phriction pages to .md files.
 *
 * Usage examples:
 *  php phriction-to-mediawiki.php -o /path/to/export/folder
 *  php phriction-to-mediawiki.php -f test/phriction-test-content.txt
 */

require_once __DIR__ . '/includes/Helpers.php';
require_once __DIR__ . '/includes/PhrictionToMd.php';


try {

    $phriction_to_md = new PhrictionToMd();
    $options = getopt("f:o:h");
    $nl = PHP_EOL;

    if(!$options or isset($options['h'])) {
        echo "options:{$nl} -o   Output directory path or file if -f option is used{$nl} -f   Filename with remarkup to convert{$nl}";
        echo "{$nl}example:{$nl} php phriction-to-md.php -o /dir/for/md/files{$nl}";
        exit(0);
    }

    if(isset($options['f'])) {
        $output = $phriction_to_md->convertFile($options['f']);

        if(isset($options['o'])) {
            file_put_contents($options['o'], $output);
        }
        else{
            echo $output;
        }
    }
    else if(isset($options['o'])) {
        $phriction_to_md->exportAll($options['o']);
    }

}
catch (Exception $e) {
    print $e->getMessage() . "\n";
    exit(1);
}
