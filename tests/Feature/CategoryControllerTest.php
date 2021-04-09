<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Passport\Passport;
use App\Models\User;
use App\Models\Category;
use App\Models\TimeBlock;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function testGetCategoriesEmpty() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $response = $this->get('/api/categories');
        
        $response
                ->assertStatus(200)
                ->assertExactJson([
                    'categories' => [],
                    'message' => 'Retrieved successfully']);
    }

    public function testGetCategoriesExists() 
    {
        $users = [User::factory()->create(), User::factory()->create()];
        Passport::actingAs($users[0]);

        $categories = [];
        $categories []= Category::factory()->create(['user_id' => $users[0]->id, 'name' => 'Foo1']);
        $categories []= Category::factory()->create(['user_id' => $users[0]->id, 'name' => 'AAA']);
        $categories []= Category::factory()->create(['user_id' => $users[0]->id, 'name' => 'Foo3']);
        $categories []= Category::factory()->create(['user_id' => $users[1]->id, 'name' => 'Foo4']);

        $response = $this->get('/api/categories');
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure(['categories', 'message'])
                ->assertJsonFragment(['message' => 'Retrieved successfully'])
                ->assertJsonCount(3, 'categories')
                ->assertJsonFragment(['user' => ['id' => $users[0]->id, 'name' => $users[0]->name]])
                ->assertJsonFragment(['name' => 'Foo1'])
                ->assertJsonFragment(['name' => 'AAA'])
                ->assertJsonFragment(['name' => 'Foo3'])
                ->assertSeeInOrder(['AAA', 'Foo1', 'Foo3']);
    }

    public function testGetCategory() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $categories = [];
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo1']);
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo2']);
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo3']);
        
        $response = $this->get('/api/categories/' . $categories[1]->id);
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'category' => [
                        'name', 'target_percentage', 'user_id'
                    ],
                    'message'
                ])
                ->assertJsonFragment(['message' => 'Retrieved successfully'])
                ->assertJsonFragment(['user' => ['id' => $user->id, 'name' => $user->name]])
                ->assertJsonFragment(['name' => 'Foo2']);
    }

    public function testGetCategoryNotExists() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $categories = [];
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo1']);
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo2']);
        $categories []= Category::factory()->create(['user_id' => $user->id, 'name' => 'Foo3']);
        
        $response = $this->get('/api/categories/' . ($categories[2]->id + 1));
        
        $response->assertStatus(404);
    }
    
    public function testGetCategoryOtherUser() {
        $users = [];
        $user []= User::factory()->create();
        $user []= User::factory()->create();
 
        Passport::actingAs($user[0]);
        
        $categories = [];
        $categories []= Category::factory()->create(['user_id' => $user[0]->id, 'name' => 'Foo1']);
        $categories []= Category::factory()->create(['user_id' => $user[1]->id, 'name' => 'Foo2']);
        $categories []= Category::factory()->create(['user_id' => $user[0]->id, 'name' => 'Foo3']);
        
        $response = $this->get('/api/categories/' . ($categories[1]->id));
        
        $response->assertStatus(404);
        
    }
    
    public function testCreateCategory() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $response = $this->post('/api/categories', [
            'name' => 'My Category',
            'target_percentage' => '30'
        ]);

        $response
                ->assertStatus(201)
                ->assertJsonStructure([
                    'category' => [
                        'name', 'target_percentage', 'user_id'
                    ],
                    'message'
                ])
                ->assertJsonFragment(['message' => 'Created successfully'])
                ->assertJsonFragment(['name' => 'My Category'])
                ->assertJsonFragment(['target_percentage' => 30])
                ->assertJsonFragment(['user_id' => $user->id]);
        
        $categories = Category::all();
        $this->assertCount(1, $categories);
        $this->assertEquals('My Category', $categories[0]->name);
        $this->assertEquals(30, $categories[0]->target_percentage);
        $this->assertEquals($user->id, $categories[0]->user_id);
    }

    public function testCreateCategoryNameAlreadyExists() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo', 'target_percentage' => 30]);

        $response = $this->post('/api/categories', [
            'name' => 'Foo',
            'target_percentage' => '20'
        ]);

        $response->assertStatus(400);
        
        $categories = Category::all();
        $this->assertCount(1, $categories);
    }
    
    
    public function testCreateCategoryValidateName() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $response = $this->post('/api/categories', [
            'name' => 'M',
            'target_percentage' => '30'
        ]);

        $response->assertStatus(422);
        $categories = Category::all();
        $this->assertCount(0, $categories);
    }
    
    public function testCreateCategoryValidateTimeBlockPercentage() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $response = $this->post('/api/categories', [
            'name' => 'My Category',
            'target_percentage' => '130'
        ]);

        $response->assertStatus(422);
        $categories = Category::all();
        $this->assertCount(0, $categories);
    }
    
    
    public function testUpdateCategory() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $categories = [];
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo1', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo2', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo3', 'target_percentage' => 30]);
        
        $categoryId = $categories[1]->id;

        $response = $this->patch('/api/categories/' . $categoryId, [
            'name' => 'AAA',
            'target_percentage' => 40
        ]);
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'category' => [
                        'name', 'target_percentage', 'user_id'
                    ],
                    'message'
                ])
                ->assertJsonFragment(['message' => 'Update successfully'])
                ->assertJsonFragment(['name' => 'AAA'])
                ->assertJsonFragment(['target_percentage' => 40])
                ->assertJsonFragment(['user_id' => $user->id]);
        
        $category = Category::find($categoryId);
        
        $this->assertEquals('AAA', $category->name);
        $this->assertEquals(40, $category->target_percentage);
    }

    public function testUpdateCategoryOneParam() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $categories = [];
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo1', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo2', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo3', 'target_percentage' => 30]);
        
        $categoryId = $categories[1]->id;

        $response = $this->patch('/api/categories/' . $categoryId, [
            'name' => 'AAA',
        ]);
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                    'category' => [
                        'name', 'target_percentage', 'user_id'
                    ],
                    'message'
                ])
                ->assertJsonFragment(['message' => 'Update successfully'])
                ->assertJsonFragment(['name' => 'AAA'])
                ->assertJsonFragment(['target_percentage' => 30])
                ->assertJsonFragment(['user_id' => $user->id]);
        
        $category = Category::find($categoryId);
        
        $this->assertEquals('AAA', $category->name);
        $this->assertEquals(30, $category->target_percentage);
    }
    
    public function testUpdateCategoryNameAlreadyExists() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $categories = [];
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo1', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo2', 'target_percentage' => 30]);
        
        $categoryId = $categories[1]->id;

        $response = $this->patch('/api/categories/' . $categoryId, [
            'name' => 'Foo1',
            'target_percentage' => 40
        ]);
        
        $response->assertStatus(400);
    }
    
    public function testUpdateCategoryValidateName() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $categories = [];
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo1', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo2', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo3', 'target_percentage' => 30]);
        
        $categoryId = $categories[1]->id;

        $response = $this->patch('/api/categories/' . $categoryId, [
            'name' => 'A',
            'target_percentage' => 40
        ]);
        
        $response->assertStatus(422);
    }

    public function testUpdateCategoryValidateTargetPercentage() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $categories = [];
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo1', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo2', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo3', 'target_percentage' => 30]);
        
        $categoryId = $categories[1]->id;

        $response = $this->patch('/api/categories/' . $categoryId, [
            'name' => 'AAA',
            'target_percentage' => 140
        ]);
        
        $response->assertStatus(422);
    }
    
    public function testUpdateCategoryNotExists() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $categories = [];
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo1', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo2', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo3', 'target_percentage' => 30]);
        
        $categoryId = $categories[2]->id + 1;

        $response = $this->patch('/api/categories/' . $categoryId, [
            'name' => 'AAA',
            'target_percentage' => 40
        ]);
        
        $response->assertStatus(404);
    }
    
    public function testUpdateCategoryOtherUser()
    {
        $users = [];
        $users []= User::factory()->create();
        $users []= User::factory()->create();
        
        Passport::actingAs($users[0]);
        
        $categories = [];
        $categories []= Category::factory()->create([
            'user_id' => $users[0]->id, 'name' => 'Foo1', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $users[0]->id, 'name' => 'Foo2', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $users[1]->id, 'name' => 'Foo3', 'target_percentage' => 30]);
        
        $categoryId = $categories[2]->id;

        $response = $this->patch('/api/categories/' . $categoryId, [
            'name' => 'AAA',
            'target_percentage' => 40
        ]);
        
        $response->assertStatus(404);
    }
    
    public function testDeleteCategory() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $categories = [];
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo1', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo2', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo3', 'target_percentage' => 30]);
        
        $categoryId = $categories[1]->id;

        $response = $this->delete('/api/categories/' . $categoryId);
        
        $response
                ->assertStatus(200)
                ->assertExactJson(['message' => 'Deleted']);
        
        $category = Category::find($categoryId);
        
        $this->assertNull($category);
        
        $categoryCount = Category::count();
        $this->assertEquals(2, $categoryCount);
    }    

    public function testDeleteCategoryNotExists() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $categories = [];
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo1', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo2', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo3', 'target_percentage' => 30]);
        
        $categoryId = $categories[2]->id + 1;

        $response = $this->delete('/api/categories/' . $categoryId);
        
        $response->assertStatus(404);
    }
    
    public function testDeleteCategoryOtherUser() 
    {
        $users = [];
        $users[0] = User::factory()->create();
        $users[1] = User::factory()->create();
        Passport::actingAs($users[0]);
        $categories = [];
        $categories []= Category::factory()->create([
            'user_id' => $users[0]->id, 'name' => 'Foo1', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $users[0]->id, 'name' => 'Foo2', 'target_percentage' => 30]);
        $categories []= Category::factory()->create([
            'user_id' => $users[1]->id, 'name' => 'Foo3', 'target_percentage' => 30]);
        
        $categoryId = $categories[2]->id;

        $response = $this->delete('/api/categories/' . $categoryId);
        
        $response->assertStatus(404);
    }

    public function testDeleteCategoryHasTimeBlocks() 
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $category = Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo1', 'target_percentage' => 30]);
        
        TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $category->id, 
            'description' => 'Block1', 'block_date' => '2021-02-01']);        

        $response = $this->delete('/api/categories/' . $category->id);
        
        $response->assertStatus(403);
        
        $category = Category::find($category->id);
        
        $this->assertNotNull($category);
        
        $categoryCount = Category::count();
        $this->assertEquals(1, $categoryCount);
    }

    
}
