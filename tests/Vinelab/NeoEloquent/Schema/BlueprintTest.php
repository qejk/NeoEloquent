<?php namespace Vinelab\NeoEloquent\Schema;

use Mockery as M;
use Vinelab\NeoEloquent\Schema\Blueprint;
use Vinelab\NeoEloquent\Tests\TestCase;

class BlueprintTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->blueprint = new Blueprint('User');
    }

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    public function testToSqlRunsCommandsFromBlueprint()
    {
        $conn = m::mock('Vinelab\NeoEloquent\Connection');
        $conn->shouldReceive('statement')->once()->with('foo');
        $conn->shouldReceive('statement')->once()->with('bar');
        $grammar = m::mock('Vinelab\NeoEloquent\Schema\Grammars\CypherGrammar');
        $blueprint = $this->getMock('Vinelab\NeoEloquent\Schema\Blueprint', array('toCypher'), array('User'));
        $blueprint->expects($this->once())->method('toCypher')->with($this->equalTo($conn), $this->equalTo($grammar))->will($this->returnValue(array('foo', 'bar')));

        $blueprint->build($conn, $grammar);
    }

    public function testBuleprintToStringConversion()
    {
        $blueprint = new Blueprint('Item');
        $label = (string) $blueprint;

        $this->assertEquals('Item', $label);
    }

    public function testLabelMutatorAccessor()
    {
        $label = 'Profile';

        $this->blueprint->setLabel($label);

        $this->assertEquals($this->blueprint->getLabel(), $label);
    }

    public function testAddingUniqueCommands()
    {
        $uniques = array('id', 'email');

        $this->blueprint->unique($uniques);

        $commands = $this->blueprint->getCommands();

        $this->assertCount(count($uniques), $commands, 'make sure array is processed via commands, and done separately');

        foreach ($uniques as $index => $uniqueName) {
            $this->assertEquals($commands[$index]->get('name'), 'unique');
            $this->assertEquals($commands[$index]->get('propertie'), $uniques[$index]);
        }
    }

    public function testIndexCommand()
    {
        $indices = array('email');

        $this->blueprint->index($indices);

        $commands = $this->blueprint->getCommands();

        foreach ($indices as $index => $indexName) {
            $this->assertInstanceOf('Illuminate\Support\Fluent', $commands[$index], 'make sure createCommand returns Fluent');
            $this->assertEquals($commands[$index]->get('name'), 'index');
            $this->assertEquals($commands[$index]->get('propertie'), $indices[$index]);
        }
    }

    public function testRenameLabelCommand()
    {
        $this->blueprint->renameLabel('Pet');

        $commands = $this->blueprint->getCommands();

        $this->assertEquals($commands[0]->get('name'), 'renameLabel');
        $this->assertEquals($commands[0]->get('to'), 'Pet');
    }

    public function testDropLabelCommands()
    {
        $methods = array('drop', 'dropIfExists');

        foreach ($methods as $method) {
            $this->blueprint->{$method}();
        }

        $commands = $this->blueprint->getCommands();

        foreach ($commands as $index => $command) {
            $this->assertEquals($commands[$index]->get('name'), $methods[$index]);
        }
    }

    public function testDropSchemaOnLabelsCommands()
    {
        $methods = array('dropIndex', 'dropUnique');
        $properties = array('email');

        foreach ($methods as $method) {
            $this->blueprint->{$method}($properties);
        }

        $commands = $this->blueprint->getCommands();

        $this->assertCount(count($methods), $commands, 'make sure array is processed via commands, and done separately');

        foreach ($commands as $index => $command) {
            $this->assertEquals($commands[$index]->get('name'), $methods[$index]);
            $this->assertEquals($commands[$index]->get('propertie'), $properties[0]);
        }
    }
}