<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use App\Models\Category;
use App\Models\TimeBlock;

use App\Services\TimeBlockStat;


class TimeBlockStatTest extends TestCase
{
    use RefreshDatabase;
    
    public function testStartDateIsNotNull() 
    {
        $startDate = date('Y-m-d', strtotime('-10 days'));
                
        $user = User::factory()->create([
            'stat_period_length' => 1,
            'stat_period_start_date' => $startDate
        ]);

        $category = Category::factory()->create(['user_id' => $user->id]);

        $this->createBlock($user, $category, 9, 20);
        $this->createBlock($user, $category, 11, 30);

        $stat = TimeBlockStat::getStat($user->id);

        $this->assertTrue($stat['dates']['start'] == $startDate);
        $this->assertEquals(1, count($stat['rows']));
        $this->assertTrue($stat['rows'][0]['category_sum'] == 20);
    }

    public function testStartDateIsNull() 
    {
        $user = User::factory()->create([
            'stat_period_length' => 2,
            'stat_period_start_date' => null
        ]);
        $category = Category::factory()->create(['user_id' => $user->id]);

        $this->createBlock($user, $category, 32, 20);
        $this->createBlock($user, $category, 63, 30);

        $stat = TimeBlockStat::getStat($user->id);
        $this->assertTrue($stat['dates']['start'] == date('Y-m-d', strtotime('-2 months')));
        $this->assertEquals(1, count($stat['rows']));
        $this->assertTrue($stat['rows'][0]['category_sum'] == 20);
    }

    public function testEndDateIsNow() 
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $stat = TimeBlockStat::getStat($user->id);
        $this->assertTrue($stat['dates']['end'] == date('Y-m-d'));
    }

    public function testBlockSum()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $this->createBlock($user, $category, 1, 20);
        $this->createBlock($user, $category, 2, 40);
        $stat = TimeBlockStat::getStat($user->id);
        $this->assertEquals(1, count($stat['rows']));
        $this->assertTrue($stat['rows'][0]['category_sum'] == 60);
    }

    public function testPerfectStat()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $user->id, 'target_percentage' => 60]);
        $category2 = Category::factory()->create([
            'user_id' => $user->id, 'target_percentage' => 40]);
        $this->createBlock($user, $category, 1, 120);
        $this->createBlock($user, $category2, 2, 80);
        $stat = TimeBlockStat::getStat($user->id);
        $this->assertEquals(2, count($stat['rows']));
        $this->assertTrue($stat['rows'][0]['target'] == $stat['rows'][0]['fact']);
        $this->assertTrue($stat['rows'][0]['congruence'] == 100);
        $this->assertTrue($stat['rows'][0]['delta'] == 0);
        $this->assertTrue($stat['rows'][1]['target'] == $stat['rows'][1]['fact']);
        $this->assertTrue($stat['rows'][1]['congruence'] == 100);
        $this->assertTrue($stat['rows'][1]['delta'] == 0);
    }
    
    public function testDelta()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $user->id, 'target_percentage' => 80]);
        $category2 = Category::factory()->create([
            'user_id' => $user->id, 'target_percentage' => 20]);
        $this->createBlock($user, $category, 1, 40);
        $this->createBlock($user, $category2, 2, 40);
        $stat = TimeBlockStat::getStat($user->id);
        $this->assertEquals(2, count($stat['rows']));
        $this->assertTrue($stat['rows'][0]['category_name'] == $category2->name);
        $this->assertTrue($stat['rows'][0]['target'] == 20);
        $this->assertTrue($stat['rows'][0]['fact'] == 50);
        $this->assertTrue($stat['rows'][0]['congruence'] == 250);
        $this->assertTrue($stat['rows'][0]['delta'] == 24);

        $this->assertTrue($stat['rows'][1]['category_name'] == $category->name);
        $this->assertTrue($stat['rows'][1]['target'] == 80);
        $this->assertTrue($stat['rows'][1]['fact'] == 50);
        $this->assertTrue($stat['rows'][1]['congruence'] == 63);
        $this->assertTrue($stat['rows'][1]['delta'] == -24);
    }
    
    public function testSort()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $user->id, 'target_percentage' => 50]);
        $category2 = Category::factory()->create([
            'user_id' => $user->id, 'target_percentage' => 50]);
        $this->createBlock($user, $category, 1, 40);
        $this->createBlock($user, $category2, 2, 60);
        $stat = TimeBlockStat::getStat($user->id);
        $this->assertEquals(2, count($stat['rows']));
        $this->assertTrue($stat['rows'][0]['category_name'] == $category2->name);
        $this->assertTrue($stat['rows'][1]['category_name'] == $category->name);
    }

    public function testTwoUsers()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $user->id, 'target_percentage' => 50]);
        $category2 = Category::factory()->create([
            'user_id' => $user2->id, 'target_percentage' => 50]);
        $this->createBlock($user, $category, 1, 40);
        $this->createBlock($user2, $category2, 2, 50);
        $stat = TimeBlockStat::getStat($user2->id);
        $this->assertEquals(1, count($stat['rows']));
        $this->assertTrue($stat['rows'][0]['category_name'] == $category2->name);
    }
    
    private function createBlock(User $user, Category $category, int $dayShift, int $length)
    {
       TimeBlock::factory()->create([
           'user_id' => $user->id,
           'category_id' => $category->id,
           'block_date' => date('Y-m-d', strtotime('-' . $dayShift . ' days')),
           'block_length' => $length
           ]);
    }
}

