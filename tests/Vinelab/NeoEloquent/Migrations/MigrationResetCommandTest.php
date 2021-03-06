<?php namespace Vinelab\NeoEloquent\Migrations;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Console\Migrations\ResetCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class MigrationResetCommandTest extends TestCase {

    public function tearDown()
    {
        M::close();
    }


    public function testResetCommandCallsMigratorWithProperArguments()
    {
        $command = new ResetCommand($migrator = M::mock('Illuminate\Database\Migrations\Migrator'));
        $command->setLaravel(new AppDatabaseMigrationStub());
        $migrator->shouldReceive('setConnection')->once()->with(null);
        $migrator->shouldReceive('rollback')->twice()->with(false)->andReturn(true, false);
        $migrator->shouldReceive('getNotes')->andReturn(array());

        $this->runCommand($command);
    }


    public function testResetCommandCanBePretended()
    {
        $command = new ResetCommand($migrator = M::mock('Illuminate\Database\Migrations\Migrator'));
        $command->setLaravel(new AppDatabaseMigrationStub());
        $migrator->shouldReceive('setConnection')->once()->with('foo');
        $migrator->shouldReceive('rollback')->twice()->with(true)->andReturn(true, false);
        $migrator->shouldReceive('getNotes')->andReturn(array());

        $this->runCommand($command, array('--pretend' => true, '--database' => 'foo'));
    }


    protected function runCommand($command, $input = array())
    {
        return $command->run(new ArrayInput($input), new NullOutput);
    }
}

class AppDatabaseMigrationStub {
    public $env = 'development';
    public function environment() { return $this->env; }
}
