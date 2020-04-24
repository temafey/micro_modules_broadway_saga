<?php

/*
 * This file is part of the broadway/broadway-saga package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga\State;

use Broadway\Saga\State;
use Broadway\UuidGenerator\Testing\MockUuidGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Class StateManagerTest
 * @package Broadway\Saga\State
 */
class StateManagerTest extends TestCase
{
    /**
     * @var InMemoryRepository
     */
    private $repository;

    /**
     * @var MockUuidGenerator
     */
    private $manager;

    /**
     * @var MockUuidGenerator
     */
    private $generator;

    /**
     *
     */
    public function setUp(): void
    {
        $this->repository = new InMemoryRepository();
        $this->generator  = new MockUuidGenerator(42);
        $this->manager    = new StateManager($this->repository, $this->generator);
    }

    /**
     * @test
     */
    public function it_returns_a_new_state_object_if_the_criteria_is_null(): void
    {
        $state = $this->manager->findOneBy(null, 'sagaId');

        $this->assertEquals(new State(42, 'sagaId'), $state);
    }

    /**
     * @test
     */
    public function it_returns_an_existing_state_instance_matching_the_returned_criteria(): void
    {
        $state = new State(1337, 'sagaId');
        $state->set('appId', 1337);
        $this->repository->save($state);
        $criteria = new Criteria(['appId' => 1337]);

        $resolvedState = $this->manager->findOneBy($criteria, 'sagaId');

        $this->assertEquals($state, $resolvedState);
    }

    /**
     * @test
     */
    public function it_returns_null_when_repository_does_not_find_for_given_criteria(): void
    {
        $criteria = new Criteria(['appId' => 1337]);

        $resolvedState = $this->manager->findOneBy($criteria, 'sagaId');

        $this->assertNull($resolvedState);
    }
}
