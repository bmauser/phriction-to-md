<?php

/**
 * Configuration for converting tags from remarkup to markdown.
 * The order of elements in this array is important.
 */
$config['tags'] = array(


    //'cleanup_end_whitespaces' => // cleans whitespaces at the end of lines
    //[
    //    'ph' => ['sarch' => '[ \t]*$'],
    //    'md' => ['replace' => ''],
    //],

    '##monospaced##' =>
        [
            'ph' => ['start' => '\#\#', 'end' => '\#\#'],
            'md' => ['start' => '`', 'end' => '`'],
        ],

    '===== h6 =====' =>
    [
        'ph' => ['start' => '^===== ', 'end' => '=====$'],
        'md' => ['start' => '###### ', 'end' => ""],
    ],

    '==== h5 ====' =>
    [
        'ph' => ['start' => '^==== ', 'end' => '====$'],
        'md' => ['start' => '##### ', 'end' => ''],
    ],

    '=== h4 ===' =>
    [
        'ph' => ['start' => '^=== ', 'end' => '===$'],
        'md' => ['start' => '#### ', 'end' => ''],
    ],

    '== h3 ==' =>
    [
        'ph' => ['start' => '^== ', 'end' => '==$'],
        'md' => ['start' => '### ', 'end' => ''],
    ],

    '= h2 =' =>
    [
        'ph' => ['start' => '^= ', 'end' => '=$'],
        'md' => ['start' => '## ', 'end' => ''],
    ],

    '===== h6' =>
    [
        'ph' => ['start' => '^===== ', 'end' => '(?<!=)$'],
        'md' => ['start' => '###### ', 'end' => ''],
    ],

    '==== h5' =>
    [
        'ph' => ['start' => '^==== ', 'end' => '(?<!=)$'],
        'md' => ['start' => '##### ', 'end' => ''],
    ],

    '=== h4' =>
    [
        'ph' => ['start' => '^=== ', 'end' => '(?<!=)$'],
        'md' => ['start' => '#### ', 'end' => ''],
    ],

    '== h3' =>
    [
        'ph' => ['start' => '^== ', 'end' => '(?<!=)$'],
        'md' => ['start' => '### ', 'end' => ''],
    ],

    '= h2' =>
    [
        'ph' => ['start' => '^= ', 'end' => '(?<!=)$'],
        'md' => ['start' => '## ', 'end' => ''],
    ],

    '//italic//' =>
    [
        'ph' => ['start' => '//', 'end' => '//'],
        'md' => ['start' => '*', 'end' => '*'],
    ],

    '__underlined__' =>
    [
        'ph' => ['start' => '__', 'end' => '__'],
        'md' => ['start' => '<u>', 'end' => '</u>'],
    ],

    '!!highlighted!!' =>
    [
        'ph' => ['start' => '!!', 'end' => '!!'],
        'md' => ['start' => '<mark>', 'end' => '</mark>'],
    ],

    '```code block```' =>
        [
            'ph' => ['start' => '```', 'end' => '```'],
            'md' => ['start' => '```', 'end' => '```'],
            'modifiers' => 'muis',
            'keep_block_content' => true,
        ],

    '%%%Literal Blocks%%%' =>
        [
            'ph' => ['start' => '%%%', 'end' => '%%%'],
            'md' => ['start' => '```text', 'end' => '```'],
            'modifiers' => 'muis',
            'keep_block_content' => true,
        ],

    '* list' =>
        [
            'ph' => ['sarch' => '^\* '],
            'md' => ['replace' => '- '],
        ],

);
