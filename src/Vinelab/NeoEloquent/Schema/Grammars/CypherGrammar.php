<?php
namespace Vinelab\NeoEloquent\Schema\Grammars;

use Vinelab\NeoEloquent\Schema\Blueprint;
use Illuminate\Support\Fluent;

class CypherGrammar extends Grammar
{

    /**
     * Compile a drop table command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        $label = $this->wrapLabel($blueprint);

        return "MATCH (n$label) REMOVE n$label";
    }

    /**
     * Compile a drop table (if exists) command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileDrop($blueprint, $command);
    }

    /**
     * Compile the query to determine if the label exists.
     *
     * @var string $label
     * @return string
     */
    public function compileLabelExists($label)
    {
        $label = $this->wrapLabel($label);

        return "MATCH (n$label) RETURN n LIMIT 1;";
    }

    /**
     * Compile the query to find the relation.
     *
     * @var string $relation
     * @return string
     */
    public function compileRelationExists($relation)
    {
        $relation = mb_strtoupper($this->wrapLabel($relation));

        return "MATCH n-[r$relation]->m RETURN r LIMIT 1";
    }


    /**
     * Compile a rename label command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileRenameLabel(Blueprint $blueprint, Fluent $command)
    {
        $from = $this->wrapLabel($blueprint);
        $to = $this->wrapLabel($command->to);

        return "MATCH (n:$from) REMOVE n$from SET n$to";
    }

    /**
     * Compile a unique propertie command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileUniqueKey('CREATE', $blueprint, $command);
    }

    /**
     * Compile a index propertie command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileIndexKey('CREATE', $blueprint, $command);
    }


    /**
     * Compile a drop unique propertie command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileDropUnique(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileUniqueKey('DROP', $blueprint, $command);
    }

    /**
     * Compile a drop index propertie command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileDropIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileIndexKey('DROP', $blueprint, $command);
    }

    /**
     * Compiles index operation.
     *
     * @param  string    $operation
     * @param  Blueprint $blueprint
     * @param  Fluent    $command
     * @return string
     */
    protected function compileIndexKey($operation, Blueprint $blueprint, Fluent $command)
    {
        $label = $this->wrapLabel($blueprint);
        $propertie = $this->propertize($command->propertie);

        return "$operation INDEX ON $label($propertie)";
    }

    /**
     * Compiles unique operation.
     *
     * @param  string    $operation
     * @param  Blueprint $blueprint
     * @param  Fluent    $command
     * @return string
     */
    protected function compileUniqueKey($operation, Blueprint $blueprint, Fluent $command)
    {
        $label = $this->wrapLabel($blueprint);
        $propertie = $this->propertize($command->propertie);

        return "$operation CONSTRAINT ON (n$label) ASSERT n.$propertie IS UNIQUE";
    }

}

