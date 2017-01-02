<?php

namespace Naroga\RedisCache\Tests;

use Naroga\RedisCache\Redis;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Predis\Command\TransactionMulti;

class RedisTest extends TestCase
{
    public function testGetWithLegalValue()
    {
        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['get'])
            ->getMock();

        $mockClient
            ->expects($this->once())
            ->method('get')
            ->with('someKey')
            ->willReturn(serialize('myValue'));

        $redis = new Redis($mockClient);
        $this->assertEquals('myValue', $redis->get('someKey'));
    }

    public function testGetWithDefaultValue()
    {
        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['get'])
            ->getMock();

        $mockClient
            ->expects($this->once())
            ->method('get')
            ->with('someKey')
            ->willReturn(false);

        $redis = new Redis($mockClient);
        $this->assertEquals('defaultValue', $redis->get('someKey', 'defaultValue'));
    }

    /**
     * @expectedException Naroga\RedisCache\Exception\InvalidArgumentException
     */
    public function testGetWithIllegalValue()
    {
        $redis = new Redis(new Client());
        $redis->get(new \stdClass());
    }

    public function testSetWithLegalValue()
    {
        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['set'])
            ->getMock();

        $mockClient
            ->expects($this->once())
            ->method('set')
            ->willReturn(true);

        $redis = new Redis($mockClient);
        $this->assertTrue($redis->set('someKey', 'someValue'));
    }

    /**
     * @expectedException \Naroga\RedisCache\Exception\InvalidArgumentException
     */
    public function testSetWithIllegalValue()
    {
        $redis = new Redis(new Client);
        $redis->set(new \stdClass(), 'someValue');
    }

    /**
     * @expectedException \Naroga\RedisCache\Exception\InvalidArgumentException
     */
    public function testSetWithIllegalTTL()
    {
        $redis = new Redis(new Client);
        $redis->set('someKey', 'someValue', new \stdClass());
    }

    public function testSetWithDateIntervalTTL()
    {
        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['setex'])
            ->getMock();

        $mockClient
            ->expects($this->once())
            ->method('setex')
            ->willReturn('OK');

        $redis = new Redis($mockClient);
        $this->assertTrue($redis->set('someKey', 'someValue', new \DateInterval('PT100S')));
    }

    public function testSetWithIntegerTTL()
    {
        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['setex'])
            ->getMock();

        $mockClient
            ->expects($this->once())
            ->method('setex')
            ->willReturn('OK');

        $redis = new Redis($mockClient);
        $this->assertTrue($redis->set('someKey', 'someValue', 100));
    }

    public function testDelete()
    {
        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['del'])
            ->getMock();

        $mockClient
            ->expects($this->once())
            ->method('del')
            ->willReturn(1);

        $redis = new Redis($mockClient);
        $this->assertTrue($redis->delete('someKey'));
    }

    public function testGetMultipleWithLegalValue()
    {
        $values = [
            'someKey1',
            'someKey2'
        ];


        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['get'])
            ->getMock();

        $mockClient
            ->method('get')
            ->willReturn(serialize('someValue'));

        $redis = new Redis($mockClient);
        $this->assertCount(2, $redis->getMultiple($values));
    }

    /**
     * @expectedException \Naroga\RedisCache\Exception\InvalidArgumentException
     */
    public function testGetMultipleWithIllegalValue()
    {
        $redis = new Redis(new Client);
        $redis->getMultiple(new \stdClass());
    }

    public function testSetMultipleWithLegalValue()
    {

        $mockTransaction = $this
            ->getMockBuilder(TransactionMulti::class)
            ->setMethods(['execute'])
            ->getMock();

        $mockTransaction
            ->method('execute')
            ->willReturn(true);

        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['set', 'transaction'])
            ->getMock();

        $mockClient
            ->method('set')
            ->willReturn(true);

        $mockClient
            ->method('transaction')
            ->willReturn($mockTransaction);


        $redis = new Redis($mockClient);

        $this->assertTrue(
            $redis->setMultiple(['someKey1' => 'someValue1', 'someKey2' => 'someValue2'])
        );
    }

    /**
     * @expectedException \Naroga\RedisCache\Exception\InvalidArgumentException
     */
    public function testSetMultipleWithIllegalValue()
    {
        $redis = new Redis(new Client);
        $redis->setMultiple(new \stdClass());
    }

    public function testDeleteMultipleWithLegalKeys()
    {
        $mockTransaction = $this
            ->getMockBuilder(TransactionMulti::class)
            ->setMethods(['execute'])
            ->getMock();

        $mockTransaction
            ->method('execute')
            ->willReturn(true);

        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['del', 'transaction'])
            ->getMock();

        $mockClient
            ->method('del')
            ->willReturn(1);

        $mockClient
            ->method('transaction')
            ->willReturn($mockTransaction);

        $redis = new Redis($mockClient);
        $this->assertTrue($redis->deleteMultiple(['someKey1', 'someKey2']));
    }

    /**
     * @expectedException \Naroga\RedisCache\Exception\InvalidArgumentException
     */
    public function testDeleteMultipleWithIllegalKeys()
    {
        $redis = new Redis(new Client);
        $redis->deleteMultiple(new \stdClass());
    }

    public function testClear()
    {
        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['flushdb'])
            ->getMock();

        $mockClient
            ->method('flushdb')
            ->willReturn(true);

        $redis = new Redis($mockClient);
        $this->assertTrue($redis->clear());
    }

    public function testHas()
    {
        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['exists'])
            ->getMock();

        $mockClient
            ->method('exists')
            ->willReturn(1);

        $redis = new Redis($mockClient);
        $this->assertTrue($redis->has('someKey'));
    }

    /**
     * @expectedException \Naroga\RedisCache\Exception\InvalidArgumentException
     */
    public function testHasWithIllegalKey()
    {
        $redis = new Redis(new Client);
        $redis->has(new \stdClass());
    }

    /**
     * @expectedException \Naroga\RedisCache\Exception\InvalidArgumentException
     */
    public function testDelWithIllegalKey()
    {
        $redis = new Redis(new Client);
        $redis->delete(new \stdClass);
    }

    public function testSetMultipleWithFailedTransaction()
    {
        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['set'])
            ->getMock();

        $mockClient
            ->method('set')
            ->willReturn(false);


        $redis = new Redis($mockClient);
        $this->assertFalse($redis->setMultiple(['key1' => 'value1', 'key2' => 'value2']));
    }


    public function testDeleteMultipleWithFailedTransaction()
    {
        $mockClient = $this
            ->getMockBuilder(Client::class)
            ->setMethods(['del'])
            ->getMock();

        $mockClient
            ->method('del')
            ->willReturn(false);

        $redis = new Redis($mockClient);
        $this->assertFalse($redis->deleteMultiple(['key1', 'key2']));
    }
}
