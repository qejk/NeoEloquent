<?php namespace Vinelab\NeoEloquent\Schema;

use Mockery as M;
use Vinelab\NeoEloquent\Schema\Builder;
use Vinelab\NeoEloquent\Tests\TestCase;

class BuilderTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->connection = M::mock('Vinelab\NeoEloquent\Connection');
        $this->grammar = M::mock('Vinelab\NeoEloquent\Schema\Grammars\CypherGrammar');
        $this->connection->shouldReceive('getSchemaGrammar')->andReturn($this->grammar);

        $this->builder = new Builder($this->connection, $this->grammar);

        $this->cypherQuery = 'Some cypher here';
    }

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    public function testHasLabelCorrectlyCallsGrammar()
    {
        $label = 'User';

        $mock = M::mock('StdClass');
        $mock->shouldReceive('count')->andReturn(1);

        $this->connection->shouldReceive('select')->once()
            ->with($this->cypherQuery, array())->andReturn($mock);
        $this->grammar->shouldReceive('compileLabelExists')->once()->with($label)->andReturn($this->cypherQuery);

        $this->assertTrue($this->builder->hasLabel($label));
    }


    public function testHasRelation()
    {
        $relation = 'FALLOWS';

        $mock = M::mock('StdClass');
        $mock->shouldReceive('count')->andReturn(1);

        $this->connection->shouldReceive('select')->once()
            ->with($this->cypherQuery, array())->andReturn($mock);
        $this->grammar->shouldReceive('compileRelationExists')->once()->with($relation)->andReturn($this->cypherQuery);

        $this->assertTrue($this->builder->hasRelation($relation));
    }
}
