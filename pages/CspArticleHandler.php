<?php

namespace APP\plugins\generic\cspUI\pages;

use APP\facades\Repo;
use APP\pages\article\ArticleHandler;
use PKP\db\DAORegistry;
use PKP\submission\GenreDAO;

class CspArticleHandler extends ArticleHandler
{
    private bool $_supplementaryGranted = false;

    public function initialize($request, $args = [])
    {
        // Lê os args sem consumi-los para verificar o gênero antes dos 404 do parent
        $peek    = $args;
        $urlPath = empty($peek) ? 0 : array_shift($peek);
        $subPath = empty($peek) ? 0 : array_shift($peek);

        if ($subPath === 'version') {
            array_shift($peek); // descarta publicationId
            $galleyId = empty($peek) ? 0 : array_shift($peek);
        } else {
            $galleyId = $subPath;
        }

        if ($galleyId && in_array($request->getRequestedOp(), ['view', 'download']) && !$request->getUser()) {
            $contextId = $request->getContext()->getId();
            $submission = ctype_digit((string) $urlPath)
                ? Repo::submission()->get((int) $urlPath, $contextId)
                : Repo::submission()->getByUrlPath($urlPath, $contextId);

            if ($submission) {
                $publication = $submission->getCurrentPublication();
                foreach ($publication->getData('galleys') ?? [] as $galley) {
                    if ($galley->getBestGalleyId() == $galleyId || $galley->getId() == $galleyId) {
                        if ($this->_isSupplementaryGalley($galley)) {
                            $this->_supplementaryGranted = true;
                        }
                        break;
                    }
                }
            }
        }

        if ($this->_supplementaryGranted) {
            $this->_initializeAsPublished($request, $args);
        } else {
            parent::initialize($request, $args);
        }
    }

    public function userCanViewGalley($request, $articleId, $galleyId = null)
    {
        if ($this->_supplementaryGranted && $this->galley && $this->_isSupplementaryGalley($this->galley)) {
            return true;
        }
        return parent::userCanViewGalley($request, $articleId, $galleyId);
    }

    /**
     * Replica o initialize() do parent pulando os dois checks de status de publicação.
     */
    private function _initializeAsPublished($request, array $args): void
    {
        $urlPath = empty($args) ? 0 : array_shift($args);

        $this->article = ctype_digit((string) $urlPath)
            ? Repo::submission()->get((int) $urlPath, $request->getContext()->getId())
            : Repo::submission()->getByUrlPath($urlPath, $request->getContext()->getId());

        if (!$this->article) {
            $request->getDispatcher()->handle404();
        }

        $currentUrlPath = $this->article->getBestId();
        if ($currentUrlPath && $currentUrlPath != $urlPath) {
            $request->redirect(null, $request->getRequestedPage(), $request->getRequestedOp(), array_merge([$currentUrlPath], $args));
        }

        $subPath = empty($args) ? 0 : array_shift($args);

        if ($subPath === 'version') {
            $publicationId = (int) array_shift($args);
            $galleyId      = empty($args) ? 0 : array_shift($args);
            foreach ($this->article->getData('publications') as $publication) {
                if ($publication->getId() === $publicationId) {
                    $this->publication = $publication;
                    break;
                }
            }
            if (!$this->publication) {
                $request->getDispatcher()->handle404();
            }
        } else {
            $this->publication = $this->article->getCurrentPublication();
            $galleyId = $subPath;
        }

        // Pula os checks de status de publicação (linhas 125 e 156 do parent)

        if ($galleyId && in_array($request->getRequestedOp(), ['view', 'download'])) {
            foreach ($this->publication->getData('galleys') as $galley) {
                if ($galley->getBestGalleyId() == $galleyId) {
                    $this->galley = $galley;
                    break;
                } elseif (ctype_digit($galleyId) && $galley->getId() == $galleyId) {
                    $request->redirect(null, $request->getRequestedPage(), $request->getRequestedOp(), [$this->article->getBestId(), $galley->getBestGalleyId()]);
                }
            }
            if (!$this->galley) {
                $request->getDispatcher()->handle404();
            }
        }

        if (!empty($args)) {
            $this->submissionFileId = array_shift($args);
        }

        if ($this->publication->getData('issueId')) {
            $issue = Repo::issue()->get($this->publication->getData('issueId'));
            $this->issue = $issue && $issue->getJournalId() == $this->article->getData('contextId') ? $issue : null;
        }
    }

    private function _isSupplementaryGalley(\PKP\galley\Galley $galley): bool
    {
        $submissionFileId = $galley->getData('submissionFileId');
        if (!$submissionFileId) {
            return false;
        }
        $submissionFile = Repo::submissionFile()->get($submissionFileId);
        if (!$submissionFile || !$submissionFile->getData('genreId')) {
            return false;
        }
        /** @var GenreDAO $genreDao */
        $genreDao = DAORegistry::getDAO('GenreDAO');
        $genre    = $genreDao->getById($submissionFile->getData('genreId'));
        return $genre && $genre->getKey() === 'MATERIAL_SUPLEMENTAR';
    }
}
