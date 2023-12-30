<?php

/**
 * Helper functions.
 */
class Helpers
{

    static $pdo;
    static $config;


    /**
     * Returns configuration options.
     *
     * Includes config-db.php, config-markup.php, config-markup.local.php and sets self::$config.
     *
     * @return array
     */
    public static function getConfig()
    {
        $config = [];

        if (!self::$config) {
            require_once __DIR__ . '/../config/config-db.php';
            require_once __DIR__ . '/../config/config-markup.php';
            if(file_exists(__DIR__ . '/../config/config-markup.local.php'))
                require_once __DIR__ . '/../config/config-markup.local.php';
            self::$config = $config;
        }
        return self::$config;
    }


    /**
     * Returns database connection object and sets self::$pdo.
     *
     * @return PDO
     */
    public static function getDbConnection()
    {

        if (!self::$pdo) {
            $config = self::getConfig();

            $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            // connect to database
            $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $options);

            self::$pdo = $pdo;
        }

        return self::$pdo;
    }


    /**
     * Returns data from Phriction database.
     *
     * @return array
     */
    public static function getPhrictionData($include_revisions = false){

        // all phriction documents
        $all_documents = self::getAllDocuments();

        // get revisions for each document
        foreach ($all_documents as $content_key => $document){
            // get current content row
            $content_row = self::getDocumentContent($document['contentPHID']);
            // get all revision till current content
            $all_documents[$content_key]['revisions'] = self::getDocumentRevisions($document['phid'], $content_row['id'], $include_revisions);
        }

        //return [$all_documents[0]];
        return $all_documents;
    }


    /**
     * Returns documents (rows) from phriction_document table.
     *
     * @return array
     */
    public static function getAllDocuments(){

        $sql = "SELECT * FROM phriction_document WHERE status = 'active'";
        $documents = Helpers::getDbConnection()->query($sql)->fetchAll();

        return $documents;
    }


    /**
     * Returns row form phriction_content table.
     *
     * @param string $content_phid
     * @return array
     */
    public static function getDocumentContent($content_phid){

        $sql = "SELECT * FROM phriction_content WHERE phid = ? ORDER by id DESC";
        $ps = Helpers::getDbConnection()->prepare($sql);
        $ps->execute([$content_phid]);
        $row = $ps->fetch();

        return $row;
    }


    /**
     * Returns document data by phriction slug.
     *
     * @param string $slug
     * @return array
     */
    public static function getDocumentBySlug($slug) {

        $slug = trim($slug);
        $slug = ltrim($slug, '/');
        $slug = rtrim($slug, '/');
        $slug = $slug . '/';

        $sql = "SELECT *, phriction_content.title AS title
                FROM phriction_document 
                JOIN phriction_content ON phriction_document.contentPHID = phriction_content.phid
                WHERE phriction_document.slug = ?";
        $ps = Helpers::getDbConnection()->prepare($sql);
        $ps->execute([$slug]);
        $document = $ps->fetch();

        return $document;
    }


    /**
     * Returns page revisions without drafts.
     *
     * @param $document_phid
     * @param $current_content_id
     * @param $all_revisions
     * @return array|false
     */
    public static function getDocumentRevisions($document_phid , $current_content_id, $all_revisions = false){

        if($all_revisions)
            $sql = "SELECT * FROM phriction_content WHERE documentPHID = ? AND id <= ?  ORDER by id DESC";
        else
            $sql = "SELECT * FROM phriction_content WHERE documentPHID = ? AND id = ? ORDER by id DESC"; // only last revision

        $ps = Helpers::getDbConnection()->prepare($sql);
        $ps->execute([$document_phid, $current_content_id]);
        $revisions = $ps->fetchAll();

        return $revisions;
    }


    /**
     * Converts all new lines to \n.
     *
     * @param string $content
     * @return string
     */
    public static function unixNewLines($content){
        return preg_replace('~\R~u', "\n", $content);
    }

}
