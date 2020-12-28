<?php

namespace Icamys\SitemapGenerator;

use BadMethodCallException;
use DateTime;
use InvalidArgumentException;
use OutOfRangeException;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class SitemapGeneratorTest extends TestCase
{
    use PHPMock;

    private $testDomain = 'http://example.com';

    /**
     * @var SitemapGenerator
     */
    private $g;

    /**
     * @var FileSystem
     */
    private $fs;

    /**
     * @var Runtime
     */
    private $runtime;

    /**
     * @var DateTime current datetime
     */
    private $now;

    public function getSizeDiffInPercentsProvider(): array
    {
        return [
            ['args' => [100, 90], 'expected' => -10],
            ['args' => [100, 110], 'expected' => 10],
            ['args' => [200, 100], 'expected' => -50],
        ];
    }

    /**
     * Call protected/private method of a class.
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     * @return mixed Method return.
     * @throws ReflectionException
     */
    public function invokeMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testSetSitemapFilenameException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->g->setSitemapFilename();
    }

    public function testSetSitemapIndexFilenameException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->g->setSitemapIndexFilename();
    }

    public function testSetRobotsFileNameException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->g->setRobotsFileName('');
    }

    public function testSetRobotsFileName()
    {
        $return = $this->g->setRobotsFileName('robots.txt');
        $this->assertEquals($this->g, $return);
    }

    public function testSetMaxURLsPerSitemapLeftOutOfRangeException()
    {
        $this->expectException(OutOfRangeException::class);
        $this->g->setMaxURLsPerSitemap(0);
    }

    public function testSetMaxURLsPerSitemapRightOutOfRangeException()
    {
        $this->expectException(OutOfRangeException::class);
        $this->g->setMaxURLsPerSitemap(50001);
    }

    public function testAddURLWithInvalidChangeFreq()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->g->addURL('/product/', $this->now, 'INVALID_CHANGEFREQ', 0.8);
    }

    public function testAddURLWithInvalidPriority()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->g->addURL('/product/', $this->now, 'always', 1.11);
    }

    public function testAddTooLargeUrl()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->g->addURL(str_repeat('c', 5000), $this->now, 'always', 0.8);
    }

    public function testUpdateRobotsNoSitemapsException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->g->updateRobots();
    }


    public function testSubmitSitemapExceptionOnEmptySitemaps()
    {
        $this->expectException(BadMethodCallException::class);
        $this->g->submitSitemap();
    }

    public function testIsValidChangefreqValue()
    {
        $this->assertTrue($this->g->isValidChangefreqValue('always'));
        $this->assertFalse($this->g->isValidChangefreqValue('blahblah'));
    }

    public function testIsValidPriorityValue()
    {
        $this->assertTrue($this->g->isValidPriorityValue(0.0));
        $this->assertTrue($this->g->isValidPriorityValue(0.1));
        $this->assertTrue($this->g->isValidPriorityValue(0.2));
        $this->assertTrue($this->g->isValidPriorityValue(0.3));
        $this->assertTrue($this->g->isValidPriorityValue(0.4));
        $this->assertTrue($this->g->isValidPriorityValue(0.5));
        $this->assertTrue($this->g->isValidPriorityValue(0.6));
        $this->assertTrue($this->g->isValidPriorityValue(0.7));
        $this->assertTrue($this->g->isValidPriorityValue(0.8));
        $this->assertTrue($this->g->isValidPriorityValue(0.9));
        $this->assertTrue($this->g->isValidPriorityValue(1.0));
        $this->assertTrue($this->g->isValidPriorityValue('0.0'));
        $this->assertTrue($this->g->isValidPriorityValue('1.0'));

        $this->assertFalse($this->g->isValidPriorityValue(0.11));
        $this->assertFalse($this->g->isValidPriorityValue(0.01));
        $this->assertFalse($this->g->isValidPriorityValue(1.01));
        $this->assertFalse($this->g->isValidPriorityValue(1.11));
        $this->assertFalse($this->g->isValidPriorityValue('0.01'));
        $this->assertFalse($this->g->isValidPriorityValue('1.01'));
        $this->assertFalse($this->g->isValidPriorityValue(-0.1));
    }

    protected function setUp(): void
    {
        $this->fs = $this->createMock(FileSystem::class);
        $this->runtime = $this->createMock(Runtime::class);
        $this->g = new SitemapGenerator($this->testDomain, '', $this->fs, $this->runtime);
        $this->now = new DateTime();
    }

    protected function tearDown(): void
    {
        unset($this->fs);
        unset($this->runtime);
        unset($this->g);
    }
}
