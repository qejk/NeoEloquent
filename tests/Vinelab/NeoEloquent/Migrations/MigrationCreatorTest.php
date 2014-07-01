<?php namespace Vinelab\NeoEloquent\Migrations;

use Mockery as M;
use Vinelab\NeoEloquent\Migrations\MigrationCreator;
use Vinelab\NeoEloquent\Tests\TestCase;

class MigrationCreatorTest extends TestCase
{

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    public function testBasicCreateMethodStoresMigrationFile()
    {
        $creator = $this->getCreator();
        unset($_SERVER['__migration.creator']);
        $creator->afterCreate(function() { $_SERVER['__migration.creator'] = true; });
        $creator->expects($this->any())->method('getDatePrefix')->will($this->returnValue('foo'));
        $creator->getFilesystem()->shouldReceive('get')->once()->with($creator->getStubPath().'/blank.stub')->andReturn('{{class}}');
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/foo_create_bar.php', 'CreateBar');

        $creator->create('create_bar', 'foo');

        $this->assertTrue($_SERVER['__migration.creator']);

        unset($_SERVER['__migration.creator']);
    }

    public function testTableUpdateMigrationStoresMigrationFile()
    {
        $creator = $this->getCreator();
        $creator->expects($this->any())->method('getDatePrefix')->will($this->returnValue('foo'));
        $creator->getFilesystem()->shouldReceive('get')->once()->with($creator->getStubPath().'/update.stub')->andReturn('{{class}} {{label}}');
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/foo_create_bar.php', 'CreateBar baz');

        $creator->create('create_bar', 'foo', 'baz');
    }

    public function testTableCreationMigrationStoresMigrationFile()
    {
        $creator = $this->getCreator();
        $creator->expects($this->any())->method('getDatePrefix')->will($this->returnValue('foo'));
        $creator->getFilesystem()->shouldReceive('get')->once()->with($creator->getStubPath().'/create.stub')->andReturn('{{class}} {{label}}');
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/foo_create_bar.php', 'CreateBar baz');

        $creator->create('create_bar', 'foo', 'baz', true);
    }

    protected function getCreator()
    {
        $files = m::mock('Illuminate\Filesystem\Filesystem');

        return $this->getMock('Vinelab\NeoEloquent\Migrations\MigrationCreator', array('getDatePrefix'), array($files));
    }
}