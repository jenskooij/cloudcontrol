<?php
/**
 * Created by jensk on 26-3-2018.
 */

namespace CloudControl\Cms\components;


use CloudControl\Cms\cc\Application;
use CloudControl\Cms\cc\Request;
use CloudControl\Cms\cc\ResponseHeaders;
use CloudControl\Cms\components\api\Response;
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
        ResponseHeaders::add(ResponseHeaders::HEADER_CONTENT_TYPE, ResponseHeaders::HEADER_CONTENT_TYPE_CONTENT_APPLICATION_JSON);
        $this->setResponse();
    }

    private function setResponse()
    {
        try {
            if (isset($_GET['id'])) {
                $this->response = $this->getSingleDocumentResponse();
                return;
            }

            if (isset($_GET['q'])) {
                $this->response = $this->getSearchDocumentsResponse();
                return;
            }

            if (isset($_GET['path'])) {
                $this->response = $this->getDocumentsByPathResponse();
                return;
            }
            $this->response = '{"swagger":"2.0","info":{"description":"This is documentation for the ApiComponent of the Cloud Control Framework & CMS. See https://getcloudcontrol.org. Additional documentation regarding the ApiComponent can be found here: https://github.com/jenskooij/cloudcontrol/wiki/ApiComponent","title":"ApiComponent for Cloud Control - Framework & CMS","contact":{"name":"Cloud Control - Framework & CMS","url":"https://getcloudcontrol.org"},"license":{"name":"MIT","url":"https://github.com/jenskooij/cloudcontrol/blob/master/LICENSE"},"version":"1.0.0"},"basePath":"' . Request::$subfolders . '","paths":{"' . Request::$subfolders . Request::$relativeUri . '":{"get":{"summary":"Retrieve documents","produces":["application/json"],"parameters":[{"name":"id","in":"query","description":"The identifier for the document that you\'re looking up","required":false,"type":"string"},{"name":"q","in":"query","description":"Search query","required":false,"type":"string"},{"name":"path","in":"query","description":"The (folder) path of which you want to see the contents","required":false,"type":"string"}],"responses":{"200":{"description":"Successful operation","schema":{"$ref":"#/definitions/ApiResponse"}},"default":{"schema":{"type":"string","example":"{}"},"description":"When no parameters present, shows the swagger definition"}}}}},"definitions":{"ApiResponse":{"type":"object","properties":{"success":{"type":"boolean","title":"Wheter or not the call was processed succesful"},"results":{"type":"array","title":"The array of Documents that were found","items":{"$ref":"#/definitions/Document"}},"error":{"title":"If an error occured, it will be displayed, empty if not.","type":"string"},"folder":{"type":"string","title":"Path of the currently selected folder, by using the id parameter","example":"/folder"},"searchSuggestions":{"type":"array","items":{"type":"array","items":{"$ref":"#/definitions/SearchSuggestion"}}}}},"Document":{"type":"object","properties":{"id":{"type":"string","title":"The Document identifier","example":"1"},"path":{"type":"string","title":"The Document path"},"title":{"type":"string","title":"The Document title"},"slug":{"type":"string","title":"The Document slug"},"type":{"type":"string","title":"The Document type","enum":["document","folder"]},"documentType":{"type":"string","title":"The Document DocumentType, as defined in the CMS"},"documentTypeSlug":{"type":"string","title":"The Document DocumentType slug"},"state":{"type":"string","title":"The publication state for this document","enum":["published","unpublished"]},"lastModificationDate":{"type":"string","title":"The Document\'s last modification timestamp","example":"0"},"publicationDate":{"type":"string","title":"The Document\'s publication timestamp","example":"0"}}},"SearchSuggestion":{"type":"object","properties":{"original":{"type":"string","title":"The query that was retrieved from parameter q","example":"kyeword"},"term":{"type":"string","title":"An existing term that is closest to the original","example":"keyword"},"editDistance":{"type":"string","title":"The amount of changes were made to get from the term to the original","example":"2"}}}}}';
            return;
        } catch (\Exception $e) {
            $error = $e->getFile() . ':' . $e->getLine() . ' ' . $e->getMessage();
            $this->response = new Response(array(), false, $error);
            return;
        }
    }

    /**
     * @return Document
     * @throws \RuntimeException
     */
    private function getDocumentById()
    {
        $id = (int) $_GET['id'];
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
     * @throws \RuntimeException
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
     * @throws \RuntimeException
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
    private function getDocumentsByPathResponse()
    {
        $path = $_GET['path'];
        if ($path[0] === '/') {
            $path = substr($path, 1);
        }
        $folderDocument = $this->storage->getDocuments()->getDocumentFolderBySlug($path);
        if ($folderDocument !== false) {
            return $this->getFolderResponse($folderDocument);
        }

        return new Response();
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
     * @throws \RuntimeException
     */
    private function getFolderResponse($document)
    {
        if ($document->type !== 'folder') {
            return new Response();
        }
        $document->dbHandle = $this->storage->getContentDbHandle();
        $document->documentStorage = new DocumentStorage($this->storage->getRepository());
        $response = new Response($document->getContent());
        $response->folder = $document->path;
        return $response;
    }


}