<?php namespace Vinelab\NeoEloquent\Migrations;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Console\Migrations\RollbackCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class DatabaseMigrationRollbackCommandTest extends TestCase {

    public function tearDown()
    {
        M::close();
    }


    public function testRollbackCommandCallsMigratorWithProperArguments()
    {
        $command = new RollbackCommand($migrator = M::mock('Illuminate\Database\Migrations\Migrator'));
        $command->setLaravel(new AppDatabaseMigrationRollbackStub());
        $migrator->shouldReceive('setConnection')->once()->with(null);
        $migrator->shouldReceive('rollback')->once()->with(false);
        $migrator->shouldReceive('getNotes')->andReturn(array());

        $this->runCommand($command);
    }


    public function testRollbackCommandCanBePretended()
    {
        $command = new RollbackCommand($migrator = M::mock('Illuminate\Database\Migrations\Migrator'));
        $command->setLaravel(new AppDatabaseMigrationRollbackStub());
        $migrator->shouldReceive('setConnection')->once()->with('foo');
        $migrator->shouldReceive('rollback')->once()->with(true);
        $migrator->shouldReceive('getNotes')->andReturn(array());

        $this->runCommand($command, array('--pretend' => true, '--database' => 'foo'));
    }


    protected function runCommand($command, $input = array())
    {
        return $command->run(new ArrayInput($input), new NullOutput);
    }

}

class AppDatabaseMigrationRollbackStub {
    public $env = 'development';
    public function environment() { return $this->env; }
}