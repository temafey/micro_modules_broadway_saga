<?php

/*
 * This file is part of the broadway/broadway-saga package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga\Testing;

use Broadway\CommandHandling\Testing\TraceableCommandBus;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\Saga\MultipleSagaManager;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Class Scenario
 * @package Broadway\Saga\Testing
 */
class Scenario
{
    /**
     * @var TestCase
     */
    private $testCase;

    /**
     * @var MultipleSagaManager
     */
    private $sagaManager;

    /**
     * @var TraceableCommandBus
     */
    private $traceableCommandBus;

    /**
     * @var int
     */
    private $playhead;

    /**
     * @var int
     */
    private $aggregateId;

    /**
     * Scenario constructor.
     *
     * @param TestCase $testCase
     * @param MultipleSagaManager $sagaManager
     * @param TraceableCommandBus $traceableCommandBus
     */
    public function __construct(
        TestCase $testCase,
        MultipleSagaManager $sagaManager,
        TraceableCommandBus $traceableCommandBus
    ) {
        $this->testCase            = $testCase;
        $this->sagaManager         = $sagaManager;
        $this->traceableCommandBus = $traceableCommandBus;
        $this->aggregateId         = 1;
        $this->playhead            = -1;
    }

    /**
     * @param string $aggregateId
     *
     * @return Scenario
     */
    public function withAggregateId($aggregateId): Scenario
    {
        $this->aggregateId = $aggregateId;

        return $this;
    }

    /**
     * @param mixed[] $events
     *
     * @return Scenario
     * @throws Throwable
     */
    public function given(array $events = []): self
    {
        foreach ($events as $given) {
            $this->sagaManager->handle($this->createDomainMessageForEvent($given));
        }

        return $this;
    }

    /**
     * @param mixed $event
     *
     * @return Scenario
     * @throws Throwable
     */
    public function when($event): self
    {
        $this->traceableCommandBus->record();

        $this->sagaManager->handle($this->createDomainMessageForEvent($event));

        return $this;
    }

    /**
     * @param mixed[] $commands
     *
     * @return Scenario
     */
    public function then(array $commands): self
    {
        $this->testCase::assertEquals($commands, $this->traceableCommandBus->getRecordedCommands());

        return $this;
    }

    /**
     * @param $event
     *
     * @return DomainMessage
     */
    private function createDomainMessageForEvent($event): DomainMessage
    {
        $this->playhead++;

        return DomainMessage::recordNow($this->aggregateId, $this->playhead, new Metadata([]), $event);
    }
}
