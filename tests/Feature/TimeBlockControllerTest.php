<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Passport\Passport;
use App\Models\User;
use App\Models\Category;
use App\Models\TimeBlock;

class TimeBlockControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function testGetTimeBlocksEmpty() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $response = $this->get('/api/time-blocks');
        
        $response
                ->assertStatus(200)
                ->assertExactJson([
                    'timeBlocks' => [],
                    'message' => 'Retrieved successfully']);
    }
    
    public function testGetTimeBlocksExists() 
    {
        $users = [User::factory()->create(), User::factory()->create()];
        Passport::actingAs($users[0]);

        $categories = [];
        $categories []= Category::factory()->create(['user_id' => $users[0]->id, 'name' => 'Foo1']);
        $categories []= Category::factory()->create(['user_id' => $users[0]->id, 'name' => 'Foo2']);
        $categories []= Category::factory()->create(['user_id' => $users[1]->id, 'name' => 'Foo3']);
        
        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[0]->id, 'category_id' => $categories[0]->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01']);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[0]->id, 'category_id' => $categories[0]->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03']);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[0]->id, 'category_id' => $categories[1]->id, 
            'description' => 'Block3', 'block_date' => '2021-01-03']);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[1]->id, 'category_id' => $categories[2]->id, 
            'description' => 'Block4']);

        $response = $this->get('/api/time-blocks');
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure(['timeBlocks', 'message'])
                ->assertJsonFragment(['message' => 'Retrieved successfully'])
                ->assertJsonCount(3, 'timeBlocks')
                ->assertJsonFragment(['description' => 'Block1'])
                ->assertJsonFragment(['description' => 'Block2'])
                ->assertJsonFragment(['description' => 'Block3'])
                ->assertJsonFragment(['user' => ['id' => $users[0]->id, 'name' => $users[0]->name]])
                ->assertJsonFragment(['category' => ['id' => $categories[0]->id, 'name' => $categories[0]->name]])
                ->assertJsonFragment(['category' => ['id' => $categories[1]->id, 'name' => $categories[1]->name]])
                ->assertSeeInOrder(['Block2', 'Block1', 'Block3']);
    }
    
    
    public function testGetTimeBlock() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);
        
        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01']);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03']);
        
        $response = $this->get('/api/time-blocks/' . $timeBlocks[1]->id);
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'timeBlock' => [
                        'block_date', 'block_length', 'description', 
                        'category', 'user'
                        ], 
                    'message'
                ])
                ->assertJsonFragment(['message' => 'Retrieved successfully'])
                ->assertJsonFragment(['user' => ['id' => $user->id, 'name' => $user->name]])
                ->assertJsonFragment(['category' => ['id' => $category->id, 'name' => $category->name]])
                ->assertJsonFragment(['description' => 'Block2']);
    }

    public function testGetTimeBlockNotExists() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);
        
        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01']);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03']);
        
        $response = $this->get('/api/time-blocks/' . ($timeBlocks[1]->id + 1));
        
        $response->assertStatus(404);
    }

    public function testGetTimeBlockOtherUser() 
    {
        $users = [];
        $users []= User::factory()->create();
        $users []= User::factory()->create();
        Passport::actingAs($users[0]);
        $category = Category::factory()->create(['user_id' => $users[1]->id, 'name' => 'Foo']);
        
        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[1]->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01']);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[1]->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03']);
        
        $response = $this->get('/api/time-blocks/' . $timeBlocks[1]->id);
        
        $response->assertStatus(404);
    }

    
    public function testCreateTimeBlock() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);
        
        $response = $this->post('/api/categories/' . $category->id . '/time-blocks', [
            'block_date' => '2021-03-02', 
            'block_length' => 20, 
            'description' => 'My Block', 
        ]);

        $response
                ->assertStatus(201)
                ->assertJsonStructure([
                    'timeBlock' => [
                        'block_date', 'block_length', 'description'
                    ],
                    'message'
                ])
                ->assertJsonFragment(['block_date' => '2021-03-02'])
                ->assertJsonFragment(['block_length' => 20])
                ->assertJsonFragment(['description' => 'My Block'])
                ->assertJsonFragment(['user_id' => $user->id])
                ->assertJsonFragment(['category_id' => $category->id]);
        
        $timeBlocks = TimeBlock::all();
        $this->assertCount(1, $timeBlocks);
        $this->assertEquals('2021-03-02', $timeBlocks[0]->block_date);
        $this->assertEquals(20, $timeBlocks[0]->block_length);
        $this->assertEquals('My Block', $timeBlocks[0]->description);
        $this->assertEquals($user->id, $timeBlocks[0]->user_id);
        $this->assertEquals($category->id, $timeBlocks[0]->category_id);
    }    

    public function testCreateTimeBlockValidateDescription() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);
        
        $response = $this->post('/api/categories/' . $category->id . '/time-blocks', [
            'block_date' => '2021-03-02', 
            'block_length' => 20, 
            'description' => '', 
        ]);

        $response->assertStatus(422);
        
        $timeBlocks = TimeBlock::all();
        $this->assertCount(0, $timeBlocks);
    }    

    public function testCreateTimeBlockValidateBlockLength() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);
        
        $response = $this->post('/api/categories/' . $category->id . '/time-blocks', [
            'block_date' => '2021-03-02', 
            'block_length' => 2000, 
            'description' => 'AA', 
        ]);

        $response->assertStatus(422);
        
        $timeBlocks = TimeBlock::all();
        $this->assertCount(0, $timeBlocks);
    }     

    public function testCreateTimeBlockValidateBlockDate() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);
        
        $response = $this->post('/api/categories/' . $category->id . '/time-blocks', [
            'block_date' => '2021-13-02', 
            'block_length' => 20, 
            'description' => 'AA', 
        ]);

        $response->assertStatus(422);
        
        $timeBlocks = TimeBlock::all();
        $this->assertCount(0, $timeBlocks);
    }     

    public function testCreateTimeBlockValidateCategory() 
    {
        $users = [];
        $users []= User::factory()->create();
        $users []= User::factory()->create();

        Passport::actingAs($users[0]);
        
        $categories = [];
        $categories []= Category::factory()->create(['user_id' => $users[0]->id, 'name' => 'Foo']);
        $categories []= Category::factory()->create(['user_id' => $users[1]->id, 'name' => 'Foo2']);
        
        $response = $this->post('/api/categories/' . $categories[1]->id . '/time-blocks', [
            'block_date' => '2021-03-02', 
            'block_length' => 20, 
            'description' => 'AA', 
        ]);

        $response->assertStatus(403);
        
        $timeBlocks = TimeBlock::all();
        $this->assertCount(0, $timeBlocks);
    }     
    
    
    public function testUpdateTimeBlock() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);

        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01', 'block_length' => 20]);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03', 'block_length' => 20]);
        
        $timeBlockId = $timeBlocks[1]->id;

        $response = $this->patch('/api/time-blocks/' . $timeBlockId, [
            'description' => 'AAA',
            'block_date' => '2021-01-05',
            'block_length' => 50
        ]);
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'timeBlock' => [
                        'block_date', 'block_length', 'description'
                    ],
                    'message'
                ])
                ->assertJsonFragment(['message' => 'Update successfully'])
                ->assertJsonFragment(['description' => 'AAA'])
                ->assertJsonFragment(['block_date' => '2021-01-05'])
                ->assertJsonFragment(['block_length' => 50])
                ->assertJsonFragment(['user_id' => $user->id]);
        
        $timeBlock = TimeBlock::find($timeBlockId);
        
        $this->assertEquals('AAA', $timeBlock->description);
        $this->assertEquals('2021-01-05', $timeBlock->block_date);
        $this->assertEquals(50, $timeBlock->block_length);
    }

    public function testUpdateTimeBlockOneParam() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);

        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01', 'block_length' => 20]);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03', 'block_length' => 20]);
        
        $timeBlockId = $timeBlocks[1]->id;

        $response = $this->patch('/api/time-blocks/' . $timeBlockId, [
            'description' => 'AAA',
        ]);
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'timeBlock' => [
                        'block_date', 'block_length', 'description'
                    ],
                    'message'
                ])
                ->assertJsonFragment(['message' => 'Update successfully'])
                ->assertJsonFragment(['description' => 'AAA'])
                ->assertJsonFragment(['block_date' => '2021-02-03'])
                ->assertJsonFragment(['block_length' => 20])
                ->assertJsonFragment(['user_id' => $user->id]);
        
        $timeBlock = TimeBlock::find($timeBlockId);
        
        $this->assertEquals('AAA', $timeBlock->description);
        $this->assertEquals('2021-02-03', $timeBlock->block_date);
        $this->assertEquals(20, $timeBlock->block_length);
    }

    public function testUpdateTimeBlockValidateDescription() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);

        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01', 'block_length' => 20]);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03', 'block_length' => 20]);
        
        $timeBlockId = $timeBlocks[1]->id;

        $response = $this->patch('/api/time-blocks/' . $timeBlockId, [
            'description' => '',
            'block_date' => '2021-01-05',
            'block_length' => 50
        ]);
        
        $response->assertStatus(422);
    }

    public function testUpdateTimeBlockValidateBlockDate() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);

        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01', 'block_length' => 20]);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03', 'block_length' => 20]);
        
        $timeBlockId = $timeBlocks[1]->id;

        $response = $this->patch('/api/time-blocks/' . $timeBlockId, [
            'description' => 'AA',
            'block_date' => '2021-21-05',
            'block_length' => 50
        ]);
        
        $response->assertStatus(422);
    }

    public function testUpdateTimeBlockValidateBlockLength() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);

        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01', 'block_length' => 20]);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03', 'block_length' => 20]);
        
        $timeBlockId = $timeBlocks[1]->id;

        $response = $this->patch('/api/time-blocks/' . $timeBlockId, [
            'description' => 'AA',
            'block_date' => '2021-01-05',
            'block_length' => 5000
        ]);
        
        $response->assertStatus(422);
    }
    
    public function testUpdateTimeBlockNotExists() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);

        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01', 'block_length' => 20]);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03', 'block_length' => 20]);
        
        $timeBlockId = $timeBlocks[1]->id + 1;

        $response = $this->patch('/api/time-blocks/' . $timeBlockId, [
            'description' => 'AAA',
            'block_date' => '2021-01-05',
            'block_length' => 50
        ]);
        
        $response->assertStatus(404);
    }

    public function testUpdateTimeBlockOtherUser() 
    {
        $users = [];
        $users []= User::factory()->create();
        $users []= User::factory()->create();
        Passport::actingAs($users[0]);

        $category = Category::factory()->create(['user_id' => $users[1]->id, 'name' => 'Foo']);

        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[1]->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01', 'block_length' => 20]);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[1]->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03', 'block_length' => 20]);
        
        $timeBlockId = $timeBlocks[1]->id;

        $response = $this->patch('/api/time-blocks/' . $timeBlockId, [
            'description' => 'AAA',
            'block_date' => '2021-01-05',
            'block_length' => 50
        ]);
        
        $response->assertStatus(404);
    }
    
    public function testUpdateTimeBlockChangeCategory() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $categories = [];
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo1']);
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo2']);

        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $categories[0]->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01', 'block_length' => 20]);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $categories[0]->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03', 'block_length' => 20]);
        
        $timeBlockId = $timeBlocks[1]->id;

        $response = $this->patch('/api/time-blocks/' . $timeBlockId, [
            'category_id' => $categories[1]->id,
        ]);
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'timeBlock' => [
                        'block_date', 'block_length', 'description'
                    ],
                    'message'
                ])
                ->assertJsonFragment(['message' => 'Update successfully'])
                ->assertJsonFragment(['category_id' => $categories[1]->id])
                ->assertJsonFragment(['user_id' => $user->id]);
        
        $timeBlock = TimeBlock::find($timeBlockId);
        
        $this->assertEquals($categories[1]->id, $timeBlock->category_id);
    }

    public function testUpdateTimeBlockChangeCategoryValidateCategoryId() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $categories = [];
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo1']);
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo2']);

        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $categories[0]->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01', 'block_length' => 20]);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $categories[0]->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03', 'block_length' => 20]);
        
        $timeBlockId = $timeBlocks[1]->id;

        $response = $this->patch('/api/time-blocks/' . $timeBlockId, [
            'category_id' => 'Foo',
        ]);
        
        $response->assertStatus(422);
    }

    public function testUpdateTimeBlockChangeCategoryNotExists() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $categories = [];
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo1']);
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo2']);

        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $categories[0]->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01', 'block_length' => 20]);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $categories[0]->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03', 'block_length' => 20]);
        
        $timeBlockId = $timeBlocks[1]->id;

        $response = $this->patch('/api/time-blocks/' . $timeBlockId, [
            'category_id' => $categories[1]->id + 1,
        ]);
        
        $response->assertStatus(404);
    }

    public function testUpdateTimeBlockChangeCategoryOtherUser() 
    {
        $users = [];
        $users []= User::factory()->create();
        $users []= User::factory()->create();
        Passport::actingAs($users[0]);

        $categories = [];
        $categories []= Category::factory()->create(['user_id' => $users[0]->id, 'name' => 'Foo1']);
        $categories []= Category::factory()->create(['user_id' => $users[1]->id, 'name' => 'Foo2']);

        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[0]->id, 'category_id' => $categories[0]->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01', 'block_length' => 20]);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[0]->id, 'category_id' => $categories[0]->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03', 'block_length' => 20]);
        
        $timeBlockId = $timeBlocks[1]->id;

        $response = $this->patch('/api/time-blocks/' . $timeBlockId, [
            'category_id' => $categories[1]->id,
        ]);
        
        $response->assertStatus(404);
    }
    
    public function testDeleteTimeBlock() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);
        
        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01']);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03']);
        
        $timeBlockId = $timeBlocks[1]->id;
        $response = $this->delete('/api/time-blocks/' . $timeBlockId);
        
        $response
                ->assertStatus(200)
                ->assertExactJson(['message' => 'Deleted']);
        
        $timeBlock = TimeBlock::find($timeBlockId);
        
        $this->assertNull($timeBlock);
        
        $timeBlockCount = TimeBlock::count();
        $this->assertEquals(1, $timeBlockCount);
    }     

    public function testDeleteTimeBlockNotExists() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo']);
        
        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01']);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03']);
        
        $timeBlockId = $timeBlocks[1]->id + 1;
        $response = $this->delete('/api/time-blocks/' . $timeBlockId);
        
        $response->assertStatus(404);
    }     

    public function testDeleteTimeBlockOtherUser() 
    {
        $users = [];
        $users []= User::factory()->create();
        $users []= User::factory()->create();
        Passport::actingAs($users[0]);
        
        $category = Category::factory()->create(['user_id' => $users[1]->id, 'name' => 'Foo']);
        
        $timeBlocks = [];
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[1]->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01']);
        $timeBlocks []= TimeBlock::factory()->create([
            'user_id' => $users[1]->id, 'category_id' => $category->id, 
            'description' => 'Block2', 'block_date' => '2021-02-03']);
        
        $timeBlockId = $timeBlocks[1]->id;
        $response = $this->delete('/api/time-blocks/' . $timeBlockId);
        
        $response->assertStatus(404);
    }     
    
}
