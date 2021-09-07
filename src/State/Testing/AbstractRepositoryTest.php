<?php

/*
 * This file is part of the broadway/broadway-saga package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga\State\Testing;

use Broadway\Saga\State;
use Broadway\Saga\State\Criteria;
use Broadway\Saga\State\RepositoryException;
use Broadway\Saga\State\RepositoryInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractRepositoryTest
 * @package Broadway\Saga\State\Testing
 */
abstract class AbstractRepositoryTest extends TestCase
{
    /**
     * @var State\RepositoryInterface
     */
    protected $repository;

    /**
     *
     */
    public function setUp(): void
    {
        $this->repository = $this->createRepository();
    }

    /**
     * @return mixed
     */
    abstract protected function createRepository(): RepositoryInterface;

    /**
     * @test
     */
    public function it_saves_a_state(): void
    {
        $s1 = new State(1, 'sagaId');
        $s1->set('appId', 42);
        $this->repository->save($s1);

        $found = $this->repository->findOneBy(new Criteria(['appId' => 42]), 'sagaId');

        $this->assertEquals($s1, $found);
    }

    /**
     * @test
     */
    public function it_removes_a_state_when_state_is_done(): void
    {
        $s1 = new State(1, 'sagaId');
        $s1->set('appId', 42);
        $this->repository->save($s1);
        $criteria = new Criteria(['appId' => 42]);

        $found = $this->repository->findOneBy($criteria, 'sagaId');
        $this->assertEquals($s1, $found);

        $s1->setDone();
        $this->repository->save($s1);
        $this->assertNull($this->repository->findOneBy($criteria, 'sagaId'));
    }

    /**
     * @test
     */
    public function it_finds_documents_matching_criteria(): void
    {
        $state = new State('yolo', 'sagaId');
        $state->set('Hi', 'There');
        $state->set('Bye', 'bye');
        $state->set('You', 'me');
        $this->repository->save($state);
        $fetchedState = $this->repository->findOneBy(new Criteria(['Hi' => 'There', 'Bye' => 'bye']), 'sagaId');
        $this->assertEquals($state, $fetchedState);
    }

    /**
     * @test
     */
    public function it_finds_documents_matching_in_criteria(): void
    {
        $state = new State('yolo', 'sagaId');
        $state->set('Hi', ['There', 'You']);
        $state->set('Bye', 'bye');
        $state->set('You', 'me');
        $this->repository->save($state);
        $fetchedState = $this->repository->findOneBy(new Criteria(['Hi' => 'There', 'Bye' => 'bye']), 'sagaId');
        $this->assertEquals($state, $fetchedState);
    }

    /**
     * @test
     */
    public function it_returns_null_when_no_states_match_the_criteria(): void
    {
        $state = new State('yolo', 'sagaId');
        $state->set('Hi', 'There');
        $this->repository->save($state);
        $this->assertNull($this->repository->findOneBy(new Criteria(['Bye' => 'There']), 'sagaId'));
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_multiple_matching_elements_are_found(): void
    {
        $this->expectExceptionMessage("Multiple saga state instances found.");
        $this->expectException(RepositoryException::class);
        $s1 = new State(1, 'sagaId');
        $s1->set('appId', 42);
        $this->repository->save($s1);
        $s2 = new State(2, 'sagaId');
        $s2->set('appId', 42);
        $this->repository->save($s2);

        $this->repository->findOneBy(new Criteria(['appId' => 42]), 'sagaId');
    }

    /**
     * @test
     */
    public function saving_a_state_object_with_the_same_id_only_keeps_the_last_one(): void
    {
        $s1 = new State(31415, 'sagaId');
        $s1->set('appId', 42);
        $this->repository->save($s1);
        $s2 = new State(31415, 'sagaId');
        $s2->set('appId', 1337);
        $this->repository->save($s2);

        $found = $this->repository->findOneBy(new Criteria(['appId' => 1337]), 'sagaId');

        $this->assertEquals(31415, $found->getId());
    }
}
