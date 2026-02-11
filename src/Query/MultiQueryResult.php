<?php

namespace Aternos\Model\Query;

class MultiQueryResult extends QueryResult
{
    /** @var QueryResult[] */
    protected array $queryResults = [];

    /**
     * Add a QueryResult
     *
     * If a query string or affected rows are set in the QueryResult, they will overwrite the ones in this MultiQueryResult
     *
     * @param QueryResult $queryResult
     * @return $this
     */
    public function addQueryResult(QueryResult $queryResult): static
    {
        $this->queryResults[] = $queryResult;
        if ($queryResult->getQueryString() !== null) {
            $this->setQueryString($queryResult->getQueryString());
        }
        if ($queryResult->getQueryString() !== null) {
            $this->setAffectedRows($queryResult->getAffectedRows());
        }
        return $this;
    }

    /**
     * @param QueryResult[] $queryResults
     * @return $this
     */
    public function addQueryResults(array $queryResults): static
    {
        foreach ($queryResults as $queryResult) {
            if ($queryResult instanceof QueryResult) {
                $this->addQueryResult($queryResult);
            }
        }
        return $this;
    }

    /**
     * @return QueryResult[]
     */
    public function getAllQueryResults(): array
    {
        return $this->queryResults;
    }
}