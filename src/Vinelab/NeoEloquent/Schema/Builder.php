<?php namespace Vinelab\NeoEloquent\Schema;

use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Grammars\Grammar as IlluminateSchemaGrammar;

class Builder {

    /**
     * The database connection resolver.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * The schema grammar instance.
     *
     * @var \Illuminate\Database\Schema\Grammars\Grammar
     */
    protected $grammar;

    /**
     * The Blueprint resolver callback.
     *
     * @var Closure
     */
    protected $resolver;

    /**
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Illuminate\Database\Schema\Grammars\Grammar  $grammar
     * @return void
     */
    public function __construct(ConnectionInterface $connection, IlluminateSchemaGrammar $grammar)
    {
        $this->connection = $connection;
        $this->grammar = $grammar;
    }

    /**
     * Fallback.
     *
     * @param  string  $label
     * @return boolean
     * @throws RuntimeException
     */
    public function hasTable($label)
    {
        throw new \RuntimeException("
Please use commands from namespace:
    neo4j:
    neo4j:migrate
    neo4j:reset
    neo4j:rollback
    neo4j:make
If your default database is set to 'neo4j' and you want use other databases side by side with Neo4j
you can do so by passing additional arguments to default migration command like:
    php artisan migrate --database=mysql
        ");
    }

    /**
     * Create a new data dafintion on label schema.
     *
     * @param  string   $label
     * @param  Closure  $callback
     * @return \Vinelab\NeoEloquent\Schema\Blueprint
     */
    public function label($label, Closure $callback)
    {
        return $this->build(
            $this->createBlueprint($label, $callback)
        );
    }

    /**
     * Drop a label from the schema.
     *
     * @param  string  $label
     * @return \Vinelab\NeoEloquent\Schema\Blueprint
     */
    public function drop($label)
    {
        $blueprint = $this->createBlueprint($label);

        $blueprint->drop();

        return $this->build($blueprint);
    }

    /**
     * Drop a label from the schema if it exists.
     *
     * @param  string  $label
     * @return \Vinelab\NeoEloquent\Schema\Blueprint
     */
    public function dropIfExists($label)
    {
        $blueprint = $this->createBlueprint($label);

        $blueprint->dropIfExists();

        return $this->build($blueprint);
    }

    /**
     * Determine if the given label exists.
     *
     * @param  string  $label
     * @return bool
     */
    public function hasLabel($label)
    {
        $cypher = $this->grammar->compileLabelExists($label);

        return $this->getConnection()->select($cypher, [])->count() > 0;
    }

    /**
     * Determine if the given relation exists.
     *
     * @param  string  $relation
     * @return bool
     */
    public function hasRelation($relation)
    {
        $cypher = $this->grammar->compileRelationExists($relation);

        return $this->getConnection()->select($cypher, [])->count() > 0;
    }

    /**
     * Rename a label.
     *
     * @param  string  $from
     * @param  string  $to
     * @return \Vinelab\NeoEloquent\Schema\Blueprint|boolean
     */
    public function renameLabel($from, $to)
    {
        $blueprint = $this->createBlueprint($from);

        $blueprint->renameLabel($to);

        return $this->build($blueprint);
    }

    /**
     * Execute the blueprint to modify the label.
     *
     * @param  Blueprint  $blueprint
     * @return void
     */
    protected function build(Blueprint $blueprint)
    {
        return $blueprint->build(
            $this->getConnection(),
            $this->grammar
        );
    }


    /**
     * Create a new command set with a Closure.
     *
     * @param  string   $label
     * @param  Closure  $callback
     * @return \Vinelab\NeoEloquent\Schema\Blueprint
     */
    protected function createBlueprint($label, Closure $callback = null)
    {
        if (isset($this->resolver))
        {
            return call_user_func($this->resolver, $label, $callback);
        }
        else
        {
            return new Blueprint($label, $callback);
        }
    }

    /**
     * Set the database connection instance.
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface
     * @return \Vinelab\NeoEloquent\Schema\Builder
     */
    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Get the database connection instance.
     *
     * @return \Illuminate\Database\ConnectionResolverInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set the Schema Blueprint resolver callback.
     *
     * @param  \Closure  $resolver
     * @return void
     */
    public function blueprintResolver(Closure $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Set schema grammar.
     *
     * @param \Illuminate\Database\Schema\Grammars\Grammar $grammar
     */
    public function setSchemaGrammar(IlluminateSchemaGrammar $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * Get schema grammar.
     *
     * @return \Vinelab\NeoEloquent\Query\Grammars\Grammar
     */
    public function getSchemaGrammar()
    {
        return $this->grammar;
    }

}
