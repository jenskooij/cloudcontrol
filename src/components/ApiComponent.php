<?php
/**
 * Created by jensk on 26-3-2018.
 */

namespace CloudControl\Cms\components;


use ApiComponent\Response;
use CloudControl\Cms\cc\Application;
use CloudControl\Cms\search\CharacterFilter;
use CloudControl\Cms\search\results\SearchSuggestion;
use CloudControl\Cms\search\Search;
use CloudControl\Cms\search\Tokenizer;
use CloudControl\Cms\storage\entities\Document;
use CloudControl\Cms\storage\Storage;
use CloudControl\Cms\storage\storage\DocumentStorage;

class ApiComponent extends CachableBaseComponent
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Storage $storage
     */
    public function run(Storage $storage)
    {
        parent::run($storage);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $this->response = $this->getResponse();
    }

    /**
     * @return Response
     */
    private function getResponse()
    {
        try {
            if (isset($_GET['id'])) {
                return $this->getSingleDocumentResponse();
            }

            if (isset($_GET['q'])) {
                return $this->getSearchDocumentsResponse();
            }

            return $this->getRootDocumentsResponse();
        } catch (\Exception $e) {
            $error = $e->getFile() . ':' . $e->getLine() . ' ' . $e->getMessage();
            return new Response(array(), false, $error);
        }
    }

    /**
     * @return Document
     */
    private function getDocumentById()
    {
        $id = intval($_GET['id']);
        $db = $this->storage->getRepository()->getContentRepository()->getContentDbHandle();
        $stmt = $this->getPDOStatement($db, $this->getDocumentByIdSql($db, $id));
        return $stmt->fetchObject(Document::class);
    }

    /**
     * @param \PDO $db
     * @param int $id
     * @return string
     */
    private function getDocumentByIdSql($db, $id)
    {
        return 'SELECT *
              FROM documents_published
             WHERE id = ' . $db->quote($id) . '
        ';
    }

    /**
     * @param \PDO $db
     * @param string $sql
     * @return \PDOStatement
     */
    private function getPDOStatement($db, $sql)
    {
        $stmt = $db->query($sql);
        if ($stmt === false) {
            $errorInfo = $db->errorInfo();
            $errorMsg = $errorInfo[2];
            throw new \RuntimeException('SQLite Exception: ' . $errorMsg . ' in SQL: <br /><pre>' . $sql . '</pre>');
        }
        return $stmt;
    }

    /**
     * @return Response
     */
    private function getSingleDocumentResponse()
    {
        $document = $this->getDocumentById();

        if ($document === false) {
            return new Response();
        }

        if ($document->type === 'folder') {
            return $this->getFolderResponse($document);
        }

        $documentContent = $this->getDocumentContent($document);
        $document->documentContent = $documentContent;

        return new Response($document);
    }

    /**
     * @param Application $application
     */
    public function render($application = null)
    {
        $this->renderedContent = $this->response;
    }

    /**
     * @return Response
     * @throws \Exception
     */
    private function getRootDocumentsResponse()
    {
        $documents = $this->storage->getDocuments()->getDocuments('published');
        return new Response($documents);
    }

    /**
     * @return Response
     * @throws \Exception
     */
    private function getSearchDocumentsResponse()
    {
        $rawResults = $this->getRawResults();
        $results = array();
        $suggestions = array();
        foreach ($rawResults as $rawResult) {
            if ($rawResult instanceof SearchSuggestion) {
                $suggestions[] = $rawResults;
                continue;
            }
            $result = $rawResult->getDocument();
            $result->searchInfo = $rawResult;
            $results[] = $result;
        }
        $response = new Response($results);
        $response->searchSuggestions = $suggestions;
        return $response;
    }

    /**
     * @param Document $document
     * @return \stdClass
     */
    private function getDocumentContent($document)
    {
        $documentContent = new \stdClass();
        $documentContent->fields = $document->fields;
        $documentContent->bricks = $document->bricks;
        $documentContent->dynamicBricks = $document->dynamicBricks;
        return $documentContent;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getRawResults()
    {
        $filteredQuery = new CharacterFilter($_GET['q']);
        $tokenizer = new Tokenizer($filteredQuery);
        $search = new Search($this->storage);
        $rawResults = $search->getDocumentsForTokenizer($tokenizer);
        return $rawResults;
    }

    /**
     * @param Document $document
     * @return Response
     */
    private function getFolderResponse($document)
    {
        $document->dbHandle = $this->storage->getContentDbHandle();
        $document->documentStorage = new DocumentStorage($this->storage->getRepository());
        $document->getContent();
        $response = new Response($document->getContent());
        $response->folder = $document->path;
        return $response;
    }


}

namespace ApiComponent;

/**
 * Class Response
 * @package ApiComponent
 */
class Response
{
    public $success = true;
    public $results = array();
    public $error;

    public function __construct($results = array(), $success = true, $error = null)
    {
        $this->results = $results;
        $this->error = $error;
        $this->success = $success;
    }


    public function __toString()
    {
        if (!is_array($this->results)) {
            $this->results = array($this->results);
        }
        return json_encode($this);
    }
}