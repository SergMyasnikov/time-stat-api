<?php

namespace Tests\Unit;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Task;
use App\Models\TimeBlock;

use App\Services\CategoryService;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testDeleteCategory() {
        $user = User::factory()->create();
        Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo', 'target_percentage' => 10]);
        $categories = Category::all();
        $this->assertEquals(1, count($categories));
        CategoryService::deleteCategory($categories[0]);
        $categories2 = Category::all();
        $this->assertEquals(0, count($categories2));
    }

    public function testDeleteCategoryHasChildTimeBlocks() {
        $user = User::factory()->create();
        Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo', 'target_percentage' => 10]);
        $categories = Category::all();
        TimeBlock::factory()->create([
            'user_id' => $user->id, 'category_id' => $categories[0]->id]);
        $this->expectException(
                \App\Exceptions\RemovingCategoryHasChildTimeBlocksException::class);
        CategoryService::deleteCategory($categories[0]);
    }
    
    public function testCheckCategorySum() {
        $user = User::factory()->create();
        Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo', 'target_percentage' => 20]);
        Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo2', 'target_percentage' => 80]);
        $this->assertTrue(CategoryService::checkCategorySum($user->id));
    }
    
    public function testCheckCategorySumLessThan100() {
        $user = User::factory()->create();
        Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo', 'target_percentage' => 20]);
        Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo2', 'target_percentage' => 70]);
        $this->assertFalse(CategoryService::checkCategorySum($user->id));
    }

    public function testCheckCategorySumGreaterThan100() {
        $user = User::factory()->create();
        Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo', 'target_percentage' => 20]);
        Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo2', 'target_percentage' => 40]);
        Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo3', 'target_percentage' => 50]);
        $this->assertFalse(CategoryService::checkCategorySum($user->id));
    }

    public function testCheckCategoryExistsTrue() {
        $user = User::factory()->create();
        Category::factory()->create([
            'user_id' => $user->id, 'name' => 'Foo', 'target_percentage' => 20]);
        $this->assertTrue(CategoryService::checkCategoryExists($user->id));
    }
    
    public function testCheckCategoryExistsFalse() {
        $user = User::factory()->create();
        $this->assertFalse(CategoryService::checkCategoryExists($user->id));
    }
}
