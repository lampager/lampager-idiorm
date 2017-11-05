<?php

namespace Lampager\Idiorm;

use Lampager\AbstractProcessor;
use Lampager\Concerns\HasProcessor;
use Lampager\Contracts\Cursor;
use Lampager\Contracts\Formatter;
use Lampager\Idiorm\Concerns\HasSnakeAliases;
use Lampager\Paginator as BasePaginator;
use Lampager\Query;
use Lampager\Query\Select;
use Lampager\Query\SelectOrUnionAll;
use Lampager\Query\UnionAll;
use ORM;

/**
 * Class Paginator
 *
 * @method $this order_by(string $column, null|string $order) Add cursor parameter name for ORDER BY statement.
 * @method $this order_by_asc(string $column) Add cursor parameter name for ORDER BY statement.
 * @method $this order_by_desc(string $column) Add cursor parameter name for ORDER BY statement.
 * @method $this clear_order_by() Clear all cursor parameters.
 * @method $this from_array(bool[]|int[]|string[] $options) Define options from an associative array.
 * @method $this use_formatter(callable|Formatter|string $formatter) Use custom formatter.
 * @method $this restore_formatter() Restore default formatter.
 * @method $this use_processor(AbstractProcessor|string $processor) Use custom processor.
 *
 * @see BasePaginator, HasProcessor
 */
class Paginator extends BasePaginator
{
    use HasProcessor, HasSnakeAliases;

    public static $temporaryTableName = 'temporary_table';

    /**
     * @var \ReflectionClass
     */
    protected $reflector;

    /**
     * Paginator constructor wrapper.
     *
     * @param  ORM    $builder
     * @return static
     */
    public static function create(ORM $builder)
    {
        return new static($builder);
    }

    /**
     * Paginator constructor.
     *
     * @param ORM $builder
     */
    public function __construct(ORM $builder)
    {
        $this->builder = $builder;
        $this->processor = new Processor();
        $this->reflector = new \ReflectionClass(ORM::class);
    }

    /**
     * Add cursor parameter name for ORDER BY statement.
     *
     * @param  string $column
     * @return $this
     */
    public function orderByAsc($column)
    {
        return $this->orderBy($column);
    }

    /**
     * Build ORM instance from Query config.
     *
     * @param  Query $query
     * @return ORM
     */
    public function transform(Query $query)
    {
        return $this->compileSelectOrUnionAll($query->selectOrUnionAll());
    }

    /**
     * Configure -> Transform.
     *
     * @param  Cursor|int[]|string[]
     * @param  mixed $cursor
     * @return ORM
     */
    public function build($cursor = [])
    {
        return $this->transform($this->configure($cursor));
    }

    /**
     * Execute query and paginate them.
     *
     * @param  Cursor|int[]|string[] $cursor
     * @return PaginationResult
     */
    public function paginate($cursor = [])
    {
        $query = $this->configure($cursor);
        return $this->process($query, $this->transform($query)->findMany());
    }

    /**
     * @param  string              $name
     * @return \ReflectionProperty
     */
    protected function ormProperty($name)
    {
        $property = $this->reflector->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    /**
     * @param  SelectOrUnionAll $selectOrUnionAll
     * @return ORM
     */
    protected function compileSelectOrUnionAll(SelectOrUnionAll $selectOrUnionAll)
    {
        if ($selectOrUnionAll instanceof Select) {
            return $this->compileSelect($selectOrUnionAll);
        }
        if ($selectOrUnionAll instanceof UnionAll) {
            return $this->compileSelect($selectOrUnionAll->supportQuery(), $selectOrUnionAll->mainQuery());
        }
        // @codeCoverageIgnoreStart
        throw new \LogicException('Unreachable here');
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param  Select[] $selects
     * @return ORM
     */
    protected function compileSelect(Select ...$selects)
    {
        $bindings = [];
        $expressions = [];
        $property = $this->ormProperty('_values');
        foreach ($selects as $select) {
            $builder = clone $this->builder;
            $this
                ->compileWhere($builder, $select)
                ->compileOrderBy($builder, $select)
                ->compileLimit($builder, $select);
            $expressions[] = $builder->_buildSelect();
            $bindings = array_merge($bindings, $property->getValue($builder));
        }
        $builder = clone $this->builder;
        $table = $builder->_quoteIdentifier(static::$temporaryTableName);
        return $builder->rawQuery(
            count($expressions) > 1
            ? 'SELECT * FROM (' . implode(") $table UNION ALL SELECT * FROM (", $expressions) . ") $table"
            : current($expressions),
            $bindings
        );
    }

    /**
     * @param $builder ORM
     * @param  Select $select
     * @return $this
     */
    protected function compileWhere($builder, Select $select)
    {
        $bindings = [];
        $expressionGroups = [];
        foreach ($select->where() as $i => $group) {
            $expressions = [];
            foreach ($group as $condition) {
                $expressions[] = "{$builder->_quoteIdentifier($condition->left())} {$condition->comparator()} ?";
                $bindings[] = $condition->right();
            }
            $expressionGroups[] = implode(' AND ', $expressions);
        }
        if ($expressionGroups) {
            $builder->whereRaw('(' . implode(' OR ', $expressionGroups) . ')', $bindings);
        }
        return $this;
    }

    /**
     * @param $builder ORM
     * @param  Select $select
     * @return $this
     */
    protected function compileOrderBy($builder, Select $select)
    {
        foreach ($select->orders() as $order) {
            $builder->{'orderBy' . ucfirst($order->order())}($order->column());
        }
        return $this;
    }

    /**
     * @param $builder ORM
     * @param  Select $select
     * @return $this
     */
    protected function compileLimit($builder, Select $select)
    {
        $builder->limit($select->limit()->toInteger());
        return $this;
    }
}
