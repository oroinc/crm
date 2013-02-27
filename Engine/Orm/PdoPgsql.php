<?php
namespace Oro\Bundle\SearchBundle\Engine\Orm;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\SearchBundle\Engine\Orm\BaseDriver;
use Oro\Bundle\SearchBundle\Query\Query;

/**
 * "TsvectorTsquery" "(" {StateFieldPathExpression ","}* InParameter ")"
 */
class PdoPgsql extends BaseDriver
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
        $ormConfig->addCustomStringFunction('TsvectorTsquery', __CLASS__);
        $ormConfig->addCustomStringFunction('TsRank', 'Oro\Bundle\SearchBundle\Engine\Orm\PdoPgsql\TsRank');

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

        $query = "to_tsvector(" . $haystack .
            ") @@ to_tsquery (" . $this->needle->dispatch($sqlWalker).   " )";

        return $query;
    }

    /**
     * Sql plain query to create fulltext index for Postgresql.
     *
     * @return string
     */
    public static function getPlainSql()
    {
        return "CREATE INDEX string_fts ON search_index_text USING gin(to_tsvector('english', 'value'))";
    }

    /**
     * Create fulltext search string for string parameters (contains)
     *
     * @param integer $index
     * @param bool    $useFieldName
     *
     * @return string
     */
    protected function createContainsStringQuery($index, $useFieldName = true)
    {
        $stringQuery = '';
        if ($useFieldName) {
            $stringQuery = ' AND textField.field = :field' .$index;
        }

        return '(TsvectorTsquery(textField.value, :value' .$index. ')) = TRUE' . $stringQuery;
    }

    /**
     * Create search string for string parameters (not contains)
     *
     * @param integer $index
     * @param bool    $useFieldName
     *
     * @return string
     */
    protected function createNotContainsStringQuery($index, $useFieldName = true)
    {
        return $this->createContainsStringQuery($index, $useFieldName);
    }

    /**
     * Set string parameter for qb
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param integer                    $index
     * @param string                     $fieldValue
     * @param string                     $searchCondition
     */
    protected function setFieldValueStringParameter(QueryBuilder $qb, $index, $fieldValue, $searchCondition)
    {
        $searchArray = explode(' ', $fieldValue);
        foreach ($searchArray as $index => $string) {
            $searchArray[$index] = $string . ':*';
        }

        if ($searchCondition != Query::OPERATOR_CONTAINS) {
            foreach ($searchArray as $index => $string) {
                $searchArray[$index] = '!' . $string;
            }
        }

        $qb->setParameter('value' . $index,  implode(' & ', $searchArray));
    }

    /**
     * Set fulltext range order by
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param int                        $index
     */
    protected function setTextOrderBy(QueryBuilder $qb, $index)
    {
        $qb->select(
            array(
                 'search as item',
                 'text',
                 'TsRank(textField.value, :value' .$index. ') AS rankField'
            )
        );
        $qb->orderBy('rankField', 'DESC');
    }
}
