<?php
namespace Oro\Bundle\SearchBundle\Engine\Orm;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\SearchBundle\Engine\Orm\BaseDriver;
use Oro\Bundle\SearchBundle\Query\Query;

/**
 * "MATCH_AGAINST" "(" {StateFieldPathExpression ","}* InParameter {Literal}? ")"
 */
class PdoMysql extends BaseDriver
{
    public $columns = array();
    public $needle;
    public $mode;

    /**
     * @param \Doctrine\ORM\EntityManager         $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function initRepo(EntityManager $em, ClassMetadata $class)
    {
        $ormConfig = $em->getConfiguration();
        $ormConfig->addCustomStringFunction('MATCH_AGAINST', __CLASS__);

        parent::initRepo($em, $class);
    }

    /**
     * Parse parameters
     *
     * @param \Doctrine\ORM\Query\Parser $parser
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {

        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        do {
            $this->columns[] = $parser->StateFieldPathExpression();
            $parser->match(Lexer::T_COMMA);
        } while ($parser->getLexer()->isNextToken(Lexer::T_IDENTIFIER));

        $this->needle = $parser->InParameter();

        while ($parser->getLexer()->isNextToken(Lexer::T_STRING)) {
            $this->mode = $parser->Literal();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Create sql string
     *
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     *
     * @return string
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        $haystack = null;

        $first = true;
        foreach ($this->columns as $column) {
            $first ? $first = false : $haystack .= ', ';
            $haystack .= $column->dispatch($sqlWalker);
        }

        $query = "MATCH(" . $haystack .
            ") AGAINST (" . $this->needle->dispatch($sqlWalker);

        if ($this->mode) {

            $query .= " " . str_replace('\'', '', $this->mode->dispatch($sqlWalker)) . " )";
        } else {
            $query .= " )";
        }

        return $query;
    }

    /**
     * Sql plain query to create fulltext index for mySql.
     *
     * @return string
     */
    public static function getPlainSql()
    {
        return "ALTER TABLE `search_index_text` ADD FULLTEXT `value` ( `value`)";
    }

    /**
     * Set string parameter for qb
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param integer                    $index
     * @param string                     $fieldValue
     */
    protected function setFieldValueStringParameter(QueryBuilder $qb, $index, $fieldValue)
    {
        $qb->setParameter('value' . $index,  $fieldValue);
    }

    /**
     * Add text search to qb
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param integer                    $index
     * @param array                      $searchCondition
     *
     * @return string
     */
    protected function addTextField(QueryBuilder $qb, $index, $searchCondition)
    {
        $joinAlias = 'textFields' . $index;
        $qb->join('search.textFields', $joinAlias);

        $useFieldName = $searchCondition['fieldName'] == '*' ? false : true;

        $stringQuery = '';
        if ($useFieldName) {
            $stringQuery = ' AND ' . $joinAlias . '.field = :field' .$index;
        }

        if ($searchCondition['condition'] == Query::OPERATOR_CONTAINS) {
            $whereExpr = $searchCondition['type'] . ' (' . ( 'MATCH_AGAINST(' .$joinAlias . '.value, :value' .$index. ' \'IN BOOLEAN MODE\') >0' . $stringQuery . ')');
            if (strpos($searchCondition['fieldValue'], ' ') === false) {
                $additionalParameter = '*';
            } else {
                $additionalParameter = '';
            }

            $qb->setParameter('value' . $index, $searchCondition['fieldValue'] . $additionalParameter);
        } else {
            $whereExpr = $searchCondition['type'] . ' (' .( $joinAlias . '.value NOT LIKE :value' . $index . $stringQuery) . ')';
            $qb->setParameter('value' . $index, '%' . $searchCondition['fieldValue'] . '%' );
        }

        if ($useFieldName) {
            $qb->setParameter('field' . $index, $searchCondition['fieldName']);
        }

        return $whereExpr;
    }
}
