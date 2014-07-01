<?php namespace Vinelab\NeoEloquent\Migrations;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;

class MigratorTest extends TestCase
{

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }



    public function testMigrationAreRunUpWhenOutstandingMigrationsExist()
    {
        $migrator = $this->getMock('Illuminate\Database\Migrations\Migrator', array('resolve'), array(
            M::mock('Illuminate\Database\Migrations\MigrationRepositoryInterface'),
            $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface'),
            M::mock('Illuminate\Filesystem\Filesystem'),
        ));
        $migrator->getFilesystem()->shouldReceive('glob')->once()->with(__DIR__.'/*_*.php')->andReturn(array(
            __DIR__.'/2_bar.php',
            __DIR__.'/1_foo.php',
            __DIR__.'/3_baz.php',
        ));

        $migrator->getFilesystem()->shouldReceive('requireOnce')->with(__DIR__.'/2_bar.php');
        $migrator->getFilesystem()->shouldReceive('requireOnce')->with(__DIR__.'/1_foo.php');
        $migrator->getFilesystem()->shouldReceive('requireOnce')->with(__DIR__.'/3_baz.php');

        $migrator->getRepository()->shouldReceive('getRan')->once()->andReturn(array(
            '1_foo',
        ));
        $migrator->getRepository()->shouldReceive('getNextBatchNumber')->once()->andReturn(1);
        $migrator->getRepository()->shouldReceive('log')->once()->with('2_bar', 1);
        $migrator->getRepository()->shouldReceive('log')->once()->with('3_baz', 1);
        $barMock = M::mock('stdClass');
        $barMock->shouldReceive('up')->once();
        $bazMock = M::mock('stdClass');
        $bazMock->shouldReceive('up')->once();
        $migrator->expects($this->at(0))->method('resolve')->with($this->equalTo('2_bar'))->will($this->returnValue($barMock));
        $migrator->expects($this->at(1))->method('resolve')->with($this->equalTo('3_baz'))->will($this->returnValue($bazMock));

        $migrator->run(__DIR__);
    }


    public function testUpMigrationCanBePretended()
    {
        $migrator = $this->getMock('Illuminate\Database\Migrations\Migrator', array('resolve'), array(
            M::mock('Illuminate\Database\Migrations\MigrationRepositoryInterface'),
            $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface'),
            M::mock('Illuminate\Filesystem\Filesystem'),
        ));
        $migrator->getFilesystem()->shouldReceive('glob')->once()->with(__DIR__.'/*_*.php')->andReturn(array(
            __DIR__.'/2_bar.php',
            __DIR__.'/1_foo.php',
            __DIR__.'/3_baz.php',
        ));
        $migrator->getFilesystem()->shouldReceive('requireOnce')->with(__DIR__.'/2_bar.php');
        $migrator->getFilesystem()->shouldReceive('requireOnce')->with(__DIR__.'/1_foo.php');
        $migrator->getFilesystem()->shouldReceive('requireOnce')->with(__DIR__.'/3_baz.php');
        $migrator->getRepository()->shouldReceive('getRan')->once()->andReturn(array(
            '1_foo',
        ));
        $migrator->getRepository()->shouldReceive('getNextBatchNumber')->once()->andReturn(1);

        $barMock = M::mock('stdClass');
        $barMock->shouldReceive('getConnection')->once()->andReturn(null);
        $barMock->shouldReceive('up')->once();

        $bazMock = M::mock('stdClass');
        $bazMock->shouldReceive('getConnection')->once()->andReturn(null);
        $bazMock->shouldReceive('up')->once();

        $migrator->expects($this->at(0))->method('resolve')->with($this->equalTo('2_bar'))->will($this->returnValue($barMock));
        $migrator->expects($this->at(1))->method('resolve')->with($this->equalTo('3_baz'))->will($this->returnValue($bazMock));

        $connection = M::mock('stdClass');
        $connection->shouldReceive('pretend')->with(M::type('Closure'))->andReturnUsing(function($closure)
        {
            $closure();
            return array(array('query' => 'foo'));
        },
        function($closure)
        {
            $closure();
            return array(array('query' => 'bar'));
        });
        $resolver->shouldReceive('connection')->with(null)->andReturn($connection);

        $migrator->run(__DIR__, true);
    }


    public function testNothingIsDoneWhenNoMigrationsAreOutstanding()
    {
        $migrator = $this->getMock('Illuminate\Database\Migrations\Migrator', array('resolve'), array(
            M::mock('Illuminate\Database\Migrations\MigrationRepositoryInterface'),
            $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface'),
            M::mock('Illuminate\Filesystem\Filesystem'),
        ));
        $migrator->getFilesystem()->shouldReceive('glob')->once()->with(__DIR__.'/*_*.php')->andReturn(array(
            __DIR__.'/1_foo.php',
        ));
        $migrator->getFilesystem()->shouldReceive('requireOnce')->with(__DIR__.'/1_foo.php');
        $migrator->getRepository()->shouldReceive('getRan')->once()->andReturn(array(
            '1_foo',
        ));

        $migrator->run(__DIR__);
    }


    public function testLastBatchOfMigrationsCanBeRolledBack()
    {
        $migrator = $this->getMock('Illuminate\Database\Migrations\Migrator', array('resolve'), array(
            M::mock('Illuminate\Database\Migrations\MigrationRepositoryInterface'),
            $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface'),
            M::mock('Illuminate\Filesystem\Filesystem'),
        ));
        $migrator->getRepository()->shouldReceive('getLast')->once()->andReturn(array(
            $fooMigration = new MigratorTestMigrationStub('foo'),
            $barMigration = new MigratorTestMigrationStub('bar'),
        ));

        $barMock = M::mock('stdClass');
        $barMock->shouldReceive('down')->once();

        $fooMock = M::mock('stdClass');
        $fooMock->shouldReceive('down')->once();

        $migrator->expects($this->at(0))->method('resolve')->with($this->equalTo('foo'))->will($this->returnValue($barMock));
        $migrator->expects($this->at(1))->method('resolve')->with($this->equalTo('bar'))->will($this->returnValue($fooMock));

        $migrator->getRepository()->shouldReceive('delete')->once()->with($barMigration);
        $migrator->getRepository()->shouldReceive('delete')->once()->with($fooMigration);

        $migrator->rollback();
    }


    public function testRollbackMigrationsCanBePretended()
    {
        $migrator = $this->getMock('Illuminate\Database\Migrations\Migrator', array('resolve'), array(
            M::mock('Illuminate\Database\Migrations\MigrationRepositoryInterface'),
            $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface'),
            M::mock('Illuminate\Filesystem\Filesystem'),
        ));
        $migrator->getRepository()->shouldReceive('getLast')->once()->andReturn(array(
            $fooMigration = new MigratorTestMigrationStub('foo'),
            $barMigration = new MigratorTestMigrationStub('bar'),
        ));

        $barMock = M::mock('stdClass');
        $barMock->shouldReceive('getConnection')->once()->andReturn(null);
        $barMock->shouldReceive('down')->once();

        $fooMock = M::mock('stdClass');
        $fooMock->shouldReceive('getConnection')->once()->andReturn(null);
        $fooMock->shouldReceive('down')->once();

        $migrator->expects($this->at(0))->method('resolve')->with($this->equalTo('foo'))->will($this->returnValue($barMock));
        $migrator->expects($this->at(1))->method('resolve')->with($this->equalTo('bar'))->will($this->returnValue($fooMock));

        $connection = M::mock('stdClass');
        $connection->shouldReceive('pretend')->with(M::type('Closure'))->andReturnUsing(function($closure)
        {
            $closure();
            return array(array('query' => 'bar'));
        },
        function($closure)
        {
            $closure();
            return array(array('query' => 'foo'));
        });
        $resolver->shouldReceive('connection')->with(null)->andReturn($connection);

        $migrator->rollback(true);
    }


    public function testNothingIsRolledBackWhenNothingInRepository()
    {
        $migrator = $this->getMock('Illuminate\Database\Migrations\Migrator', array('resolve'), array(
            M::mock('Illuminate\Database\Migrations\MigrationRepositoryInterface'),
            $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface'),
            M::mock('Illuminate\Filesystem\Filesystem'),
        ));
        $migrator->getRepository()->shouldReceive('getLast')->once()->andReturn(array());

        $migrator->rollback();
    }

}

class MigratorTestMigrationStub {
    public function __construct($migration)
    {
        $this->migration = $migration;
    }
    public $migration;
}