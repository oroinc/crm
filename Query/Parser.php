<?php
namespace Oro\Bundle\SearchBundle\Query;

use Oro\Bundle\SearchBundle\Query\Query;

class Parser
{
    const KEYWORD_FROM = 'from';
    const KEYWORD_WHERE = 'where';

    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    protected $orderDirections = array(
        self::ORDER_ASC,
        self::ORDER_DESC,
    );

    protected $keywords =
        array(
            Query::KEYWORD_AND,
            Query::KEYWORD_OR,
            self::KEYWORD_FROM,
            Query::KEYWORD_ORDER_BY,
            Query::KEYWORD_OFFSET,
            Query::KEYWORD_MAX_RESULTS
        );

    protected $types =
        array(
            Query::TYPE_TEXT,
            Query::TYPE_DATETIME,
            Query::TYPE_DECIMAL,
            Query::TYPE_INTEGER,
        );

    protected $typeOperators =
        array(
            Query::TYPE_TEXT => array(
                Query::OPERATOR_CONTAINS,
                Query::OPERATOR_NOT_CONTAINS
            ),
            QUERY::TYPE_INTEGER => array(
                Query::OPERATOR_GREATER_THAN,
                Query::OPERATOR_GREATER_THAN_EQUALS,
                Query::OPERATOR_LESS_THAN,
                Query::OPERATOR_LESS_THAN_EQUALS,
                Query::OPERATOR_EQUALS,
                Query::OPERATOR_NOT_EQUALS,
                Query::OPERATOR_IN,
                Query::OPERATOR_NOT_IN,
            ),
            QUERY::TYPE_DECIMAL => array(
                Query::OPERATOR_GREATER_THAN,
                Query::OPERATOR_GREATER_THAN_EQUALS,
                Query::OPERATOR_LESS_THAN,
                Query::OPERATOR_LESS_THAN_EQUALS,
                Query::OPERATOR_EQUALS,
                Query::OPERATOR_NOT_EQUALS,
                Query::OPERATOR_IN,
                Query::OPERATOR_NOT_IN,
            )
        );

    private $mappingConfig;

    public function __construct($mappingConfig)
    {
        $this->mappingConfig = $mappingConfig;
    }

    /**
     * Get query from string
     *
     * @param $inputString
     * @return \Oro\Bundle\SearchBundle\Query\Query
     */
    public function getQueryFromString($inputString)
    {
        $query = new Query(Query::SELECT);
        $query->setMappingConfig($this->mappingConfig);
        $this->parseExpression($query, trim($inputString));
        if (!$query->getFrom()) {
            $query->from('*');
        }

        return $query;
    }

    /**
     * Extention parser
     *
     * @param Query  $query
     * @param string $inputString
     */
    private function parseExpression(Query $query, $inputString)
    {
        $delimiterPosition = strpos($inputString, ' ');
        $keyWord = substr($inputString, 0, $delimiterPosition);

        // check if we can't identify keyword - set keyword to KEYWORD_AND
        if (!in_array($keyWord, $this->keywords)) {
            if ($keyWord == self::KEYWORD_WHERE) {
                $inputString = $this->trimString($inputString, self::KEYWORD_WHERE);
            }
            $keyWord = Query::KEYWORD_AND;
        } else {
            $inputString = trim(str_replace($keyWord, '', $inputString));
        }
        //check if we found 'where' statement
        if (in_array($keyWord, array(Query::KEYWORD_OR, Query::KEYWORD_AND))) {
            $inputString = $this->Where($query, $keyWord, $inputString);
        }
        //check if we found 'from' statement
        if ($keyWord == self::KEYWORD_FROM) {
            $inputString = $this->from($query, $inputString);
        }
        //keyword offset
        if ($keyWord == Query::KEYWORD_OFFSET) {
            $inputString = $this->offset($query, $inputString);
        }
        //keyword offset
        if ($keyWord == Query::KEYWORD_MAX_RESULTS) {
            $inputString = $this->maxResults($query, $inputString);
        }
        //keyword order by
        if ($keyWord == Query::KEYWORD_ORDER_BY) {
            $inputString = $this->orderBy($query, $inputString);
        }

        // recursion
        if (strlen($inputString)) {
            $this->parseExpression($query, $inputString);
        }
    }

    /**
     * ORDER BY keyword
     *
     * @param  \Oro\Bundle\SearchBundle\Query\Query $query
     * @param  type                                 $inputString
     * @return string
     */
    public function orderBy(Query $query, $inputString)
    {
        $orderField = $this->getWord($inputString);
        $inputString = $this->trimString($inputString, $orderField);

        $orderDirection = $this->getWord($inputString);
        if (in_array($orderDirection, $this->orderDirections)) {
            $inputString = $this->trimString($inputString, $orderDirection);
        } else {
            $orderDirection = self::ORDER_ASC;
        }

        $from = $query->getFrom();
        if (count($from) == 1 && $from[0] != '*') {
            $query->setOrderBy($orderField, $orderDirection);
        }

        return $inputString;
    }

    /**
     * OFFSET keyword
     *
     * @param Query $query
     * @param       $inputString
     *
     * @return string
     */
    private function offset(Query $query, $inputString)
    {
        $offset = $this->getWord($inputString);
        $inputString = $this->trimString($inputString, $offset);
        $query->setFirstResult($offset);
        if (!$query->getMaxResults()) {
            $query->setMaxResults(1000000);
        }

        return $inputString;
    }

    /**
     * MAX RESULTS keyword
     *
     * @param Query  $query
     * @param string $inputString
     *
     * @return string
     */
    private function maxResults(Query $query, $inputString)
    {
        $maxResults= $this->getWord($inputString);
        $inputString = $this->trimString($inputString, $maxResults);
        $query->setMaxResults($maxResults);

        return $inputString;
    }

    /**
     * Parse from statement
     *
     * @param Query  $query
     * @param string $inputString
     *
     * @return string
     */
    private function from(Query $query, $inputString)
    {
        if (substr($inputString, 0, 1) == '(') {
            $fromString = $this->getWord($inputString, ')');
            $inputString = $this->trimString($inputString, $fromString . ')');
            $fromString = str_replace(array('(', ')'), '', $fromString);
            $query->from(explode(', ', $fromString));

        } else {
            $from = $this->getWord($inputString);
            $inputString = $this->trimString($inputString, $from);
            $query->from($from);
        }

        return $inputString;
    }

    /**
     * Parse where statement
     *
     * @param Query  $query
     * @param string $keyWord
     * @param string $inputString
     *
     * @return string
     */
    private function where(Query $query, $keyWord, $inputString)
    {
        $typeWord = $this->getWord($inputString);

        if (!in_array($typeWord, $this->types)) {
            $typeWord = Query::TYPE_TEXT;
        } else {
            $inputString = $this->trimString($inputString, $typeWord);
        }

        //parse field name
        $fieldName = $this->getWord($inputString);
        $inputString = $this->trimString($inputString, $fieldName);

        //parse operator
        $operatorWord = $this->getWord($inputString);
        // check operator
        if (!in_array($operatorWord, $this->typeOperators[$typeWord])) {
            throw new \InvalidArgumentException('Type ' . $typeWord . ' does not support operator "' . $operatorWord . '"');
        }
        $inputString = $this->trimString($inputString, $operatorWord);

        if (!in_array($operatorWord, array(Query::OPERATOR_IN, Query::OPERATOR_NOT_IN))) {
            $value = $this->getWord($inputString);
            $inputString = $this->trimString($inputString, $value);
        } else {
            $fromString = $this->getWord($inputString, ')');
            $inputString = $this->trimString($inputString, $fromString . ')');
            $fromString = str_replace(array('(', ')'), '', $fromString);
            $value = explode(', ', $fromString);
        }

        $query->where($keyWord, $fieldName, $operatorWord, $value, $typeWord);

        return $inputString;
    }

    /**
     * Get the next word from string
     *
     * @param  string $inputString
     * @param  string $delimiter
     * @return string
     */
    private function getWord($inputString, $delimiter = ' ')
    {
        $word = substr($inputString, 0, strpos($inputString, $delimiter));

        if ($word == false) {
            $word = $inputString;
        }

        return $word;
    }

    /**
     * Trims input string
     *
     * @param  string $inputString
     * @param  string $trimString
     * @return string
     */
    private function trimString($inputString, $trimString)
    {
        return trim(substr(trim($inputString), strlen($trimString), strlen($inputString)));
    }
}
