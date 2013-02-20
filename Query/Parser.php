<?php
namespace Oro\Bundle\SearchBundle\Query;

use Oro\Bundle\SearchBundle\Query\Query;

class Parser
{
    const POSITION_KEYWORD = 0;
    const POSITION_TYPE = 1;
    const POSITION_FIELD = 2;
    const POSITION_OPERATOR = 3;
    const POSITION_VALUE = 4;

    protected $keywords =
        array(
            Query::KEYWORD_AND,
            Query::KEYWORD_OR,
        );

    protected $operators =
        array(
            Query::OPERATOR_EQUALS,
            Query::OPERATOR_NOT_EQUALS,
            Query::OPERATOR_GREATER_THAN,
            Query::OPERATOR_GREATER_THAN_EQUALS,
            Query::OPERATOR_LESS_THAN,
            Query::OPERATOR_LESS_THAN_EQUALS,
            Query::OPERATOR_CONTAINS,
            Query::OPERATOR_NOT_CONTAINS
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
                Query::OPERATOR_NOT_EQUALS
            ),
            QUERY::TYPE_DECIMAL => array(
                Query::OPERATOR_GREATER_THAN,
                Query::OPERATOR_GREATER_THAN_EQUALS,
                Query::OPERATOR_LESS_THAN,
                Query::OPERATOR_LESS_THAN_EQUALS,
                Query::OPERATOR_EQUALS,
                Query::OPERATOR_NOT_EQUALS
            )
        );

    public function parse($inputString)
    {
        return $this->getCompleteWord(
            explode(' ', $inputString),
            0,
            self::POSITION_TYPE,
            0
        );
    }

    protected function getCompleteWord(
        $searchArray,
        $expressionIndex,
        $wordPosition,
        $index,
        $resultExpression = array()
    ) {
        $word = $searchArray[$index];

        //check if we haven't Type in search parameters
        if ($wordPosition == self::POSITION_TYPE && !in_array($word, $this->types)) {
            $resultExpression[$expressionIndex][$wordPosition] = Query::TYPE_TEXT;
            $wordPosition++;
        }

        // check for string word
        if ($wordPosition == self::POSITION_VALUE && strpos($word, '"') === 0) {
            do {
                $index++;
                $subWord = $searchArray[$index];
                $word .= ' ' . $subWord;

                $stringNotComplete = strpos($subWord, '"') !== (strlen($subWord) - 1);
                if (!$stringNotComplete) {
                    $word = str_replace('"', '', $word);
                }
            } while ($stringNotComplete);
        }

        // check operator
        if ($wordPosition == self::POSITION_OPERATOR
            && !in_array(
                $word,
                $this->typeOperators[$resultExpression[$expressionIndex][self::POSITION_TYPE]]
            )
        ) {
            die(
                'TYPE ' . $resultExpression[$expressionIndex][self::POSITION_TYPE]
                    . ' DOES NOT SUPPORT OPERATOR "'
                    . $word . '"'
            );
        }
        // check if we now how to work with this word
        $this->checkWord($word, $wordPosition);

        // add word to result
        $resultExpression[$expressionIndex][$wordPosition] = $word;

        // check next index position
        $index++;
        if ($index < count($searchArray)) {
            $wordPosition++;
            if ($wordPosition > self::POSITION_VALUE) {
                $expressionIndex++;
                $wordPosition = self::POSITION_KEYWORD;
            }
            $resultExpression = $this->getCompleteWord(
                $searchArray,
                $expressionIndex,
                $wordPosition,
                $index,
                $resultExpression
            );
        }

        return $resultExpression;
    }

    protected function checkWord($word, $wordPosition)
    {
        if ($wordPosition == self::POSITION_KEYWORD) {
            if (!in_array($word, $this->keywords)) {
                die ($word . ' BAD KEYWORD!!!');
            }
        }
        if ($wordPosition == self::POSITION_OPERATOR) {
            if (!in_array($word, $this->operators)) {
                die ($word . ' BAD OPERATOR!!!');
            }
        }
    }
}