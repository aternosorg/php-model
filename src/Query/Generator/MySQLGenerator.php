<?php
namespace Aternos\Model\Query\Generator;

use \Aternos\Model\Query\Query;
use \Aternos\Model\Query\OrderField;
use \Aternos\Model\Query\SelectQuery;
class MySQLGenerator extends \Aternos\Model\Query\Generator\SQL {
    /**
     * Generate a query string from a Query object with join
     *
     * @param Query $query
     * @return string
     */
    public function generate(Query $query): string
    {
        $queryString = "";
        
        if ($query instanceof SelectQuery) {
            $queryString .= "SELECT";
            
            if ($query->getFields()) {
                $queryString .= " " . $this->generateFields($query);
            } else {
                $queryString .= " *";
            }

            $queryString .= " FROM " . $this->tableEnclosure . $query->modelClassName::getName() . $this->tableEnclosure;
        } else if ($query instanceof UpdateQuery) {
            $queryString .= "UPDATE " . $this->tableEnclosure . $query->modelClassName::getName() . $this->tableEnclosure . " SET";
            $queryString .= " " . $this->generateFields($query);
        } else if ($query instanceof DeleteQuery) {
            $queryString .= "DELETE FROM " . $this->tableEnclosure . $query->modelClassName::getName() . $this->tableEnclosure;
        }
        
        if ($query instanceof SelectQuery && $query->getJoins()) {
            $queryString .= $this->generateJoins($query);
        }

        if ($query->getWhere()) {
            $queryString .= " WHERE " . $this->generateWhere($query);
        }

        $queryString .= $this->generateGroup($query);

        if ($query->getOrder()) {
            $queryString .= " " . $this->generateOrder($query);
        }

        if ($limit = $query->getLimit()) {
            $queryString .= " LIMIT " . $limit->start . ", " . $limit->length;
        }

        return $queryString;
    }

    /**
     * Generate query from joins statements
     *
     * @param Query $query
     * @param WhereCondition|WhereGroup|null $where
     * @return string
     */
    public function generateJoins(Query $query): string
    {
        $joins = $query->getJoins();
        if ($joins && count($joins) > 0) {
            $joinsString = "";
            foreach($joins as $join) {
                $joinsString .= " " . $join[0] . " " . $join[1] . " ON " . $join[2];
            }
            return $joinsString;
        }

        return "";
    }

}