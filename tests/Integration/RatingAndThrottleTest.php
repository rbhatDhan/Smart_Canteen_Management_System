<?php
declare(strict_types=1);

namespace SmartCanteen\Tests\Integration;

use SmartCanteen\Modules\Auth\LoginThrottle;
use SmartCanteen\Modules\Ratings\RatingRepository;
use SmartCanteen\Tests\Support\DbTestCase;

final class RatingAndThrottleTest extends DbTestCase
{
    public function testNoRatingsMeansNoAverage(): void
    {
        $this->assertSame(['average' => null, 'count' => 0], (new RatingRepository())->summary());
    }

    public function testRatingsAreAveragedAcrossStudents(): void
    {
        $a = $this->makeStudent('alice', '4GW23IS001');
        $b = $this->makeStudent('bob', '4GW23IS002');
        $r = new RatingRepository();
        $r->save($a, 10, 5);
        $r->save($b, 10, 4);

        $this->assertSame(['average' => 4.5, 'count' => 2], $r->summary());
    }

    public function testRatingTheSameOrderAgainReplacesTheOldRating(): void
    {
        $a = $this->makeStudent();
        $r = new RatingRepository();
        $r->save($a, 7, 2);
        $r->save($a, 7, 5);

        $this->assertSame([7 => 5], $r->forStudent($a));
        $this->assertSame(1, $r->summary()['count']);
    }

    public function testAccountLocksAfterFiveFailures(): void
    {
        $t = new LoginThrottle();
        for ($i = 0; $i < 4; $i++) {
            $t->recordFailure('student', 'Alice', '10.0.0.1');
        }
        $this->assertFalse($t->isLocked('student', 'alice', '10.0.0.1'));

        $t->recordFailure('student', 'alice', '10.0.0.1');
        $this->assertTrue($t->isLocked('student', 'ALICE', '10.0.0.9'), 'case-insensitive, any IP');
        $this->assertFalse($t->isLocked('student', 'bob', '10.0.0.9'), 'other accounts stay open');
        $this->assertFalse($t->isLocked('admin', 'alice', '10.0.0.9'), 'student and admin logins are separate');
    }

    public function testSuccessfulLoginClearsTheFailures(): void
    {
        $t = new LoginThrottle();
        for ($i = 0; $i < 4; $i++) {
            $t->recordFailure('student', 'carol', '10.0.0.2');
        }
        $t->recordSuccess('student', 'carol');
        $t->recordFailure('student', 'carol', '10.0.0.2');

        $this->assertFalse($t->isLocked('student', 'carol', '10.0.0.2'));
    }

    public function testOneIpTryingManyAccountsGetsLocked(): void
    {
        $t = new LoginThrottle();
        for ($i = 0; $i < LoginThrottle::MAX_FAILURES_PER_IP; $i++) {
            $t->recordFailure('student', 'user' . $i, '10.0.0.3');
        }
        $this->assertTrue($t->isLocked('student', 'brand-new-user', '10.0.0.3'));
        $this->assertFalse($t->isLocked('student', 'brand-new-user', '10.0.0.4'));
    }
}
