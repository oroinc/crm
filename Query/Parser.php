<?php
namespace Oro\Bundle\SearchBundle\Query;

use Oro\Bundle\SearchBundle\Query\Query;

class Parser
{
    const KEYWORD_FROM = 'from';
    const KEYWORD_ORDERBY = 'order by';
    const KEYWORD_WHERE = 'where';

    protected $keywords =
        array(
            Query::KEYWORD_AND,
            Query::KEYWORD_OR,
            self::KEYWORD_FROM,
            self::KEYWORD_ORDERBY,
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
            ),
            QUERY::TYPE_DECIMAL => array(
                Query::OPERATOR_GREATER_THAN,
                Query::OPERATOR_GREATER_THAN_EQUALS,
                Query::OPERATOR_LESS_THAN,
                Query::OPERATOR_LESS_THAN_EQUALS,
                Query::OPERATOR_EQUALS,
                Query::OPERATOR_NOT_EQUALS,
                Query::OPERATOR_IN,
            )
        );

    public function getQueryFromString($inputString)
    {
        $query = new Query(Query::SELECT);
        $this->parseExpression($query, trim($inputString));
        if (!$query->getFrom()) {
            $query->from('*');
        }

        return $query;
    }

    /**
     * @param Query  $query
     * @param string $inputString
     */
    private function parseExpression(Query $query, $inputString) {
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

        // recursion
        if (strlen($inputString)) {
            $this->parseExpression($query, $inputString);
        }
    }

    /**
     * Parse from statement
     *
     * @param Query $query
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
            die(
                'TYPE ' . $typeWord
                    . ' DOES NOT SUPPORT OPERATOR "'
                    . $operatorWord . '"'
            );
        }
        $inputString = $this->trimString($inputString, $operatorWord);

        if (!in_array($operatorWord, array(Query::OPERATOR_IN))) {
            $value = $this->getWord($inputString);
            $inputString = $this->trimString($inputString, $value);
        }
        $query->where($keyWord, $fieldName, $operatorWord, $value, $typeWord);

        return $inputString;
    }

    /**
     * Get the next word from string
     *
     * @param string $inputString
     * @param string $delimiter
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
     * @param string $inputString
     * @param string $trimString
     * @return string
     */
    private function trimString($inputString, $trimString)
    {
        return trim(substr(trim($inputString), strlen($trimString), strlen($inputString)));
    }
}