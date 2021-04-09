<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Passport\Passport;
use App\Models\User;
use App\Models\Category;
use App\Models\TimeBlock;

class StatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetStat() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $category = Category::factory()->create([
            'user_id' => $user->id, 'target_percentage' => 60]);
        $category2 = Category::factory()->create([
            'user_id' => $user->id, 'target_percentage' => 40]);
        
       TimeBlock::factory()->create([
           'user_id' => $user->id,
           'category_id' => $category->id,
           'block_date' => date('Y-m-d', strtotime('-1 days')),
           'block_length' => 120
           ]);        
       TimeBlock::factory()->create([
           'user_id' => $user->id,
           'category_id' => $category2->id,
           'block_date' => date('Y-m-d', strtotime('-2 days')),
           'block_length' => 80
           ]);        
        
       $response = $this->get('/api/stat');
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure(['stat' => ['dates', 'rows'], 'message'])
                ->assertJsonFragment(['message' => 'OK'])
                ->assertJsonFragment(
                    [
                        'category_name' => $category2->name,
                        'category_sum' => '80',
                        'target' => 40,
                        'fact' => 40,
                        'congruence' => 100,
                        'delta' => 0
                    ])
                ->assertJsonFragment(
                    [
                        'category_name' => $category->name,
                        'category_sum' => '120',
                        'target' => 60,
                        'fact' => 60,
                        'congruence' => 100,
                        'delta' => 0
                    ]);                
    }
}
