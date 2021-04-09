<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Passport\Passport;
use App\Models\User;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetSettings()
    {
        $user = User::factory()->create([
            'stat_period_length' => 3, 'stat_period_start_date' => '2021-04-01']);
        Passport::actingAs($user);
        
        $response = $this->get('/api/settings');
        
        $response
                ->assertStatus(200)
                ->assertJsonFragment([
                    'stat_period_length' => 3, 
                    'stat_period_start_date' => '2021-04-01']);        
    }
    
    public function testUpdateSettings()
    {
        $user = User::factory()->create([
            'stat_period_length' => 3, 'stat_period_start_date' => '2021-04-01']);
        Passport::actingAs($user);
        
        $response = $this->patch('/api/settings', [
            'stat_period_length' => 5, 'stat_period_start_date' => '2021-03-01']);
        
        $response
                ->assertStatus(200)
                ->assertJsonFragment([
                    'stat_period_length' => 5, 
                    'stat_period_start_date' => '2021-03-01']);
        
        $result = User::find($user->id);
        $this->assertEquals(5, $result->stat_period_length);
        $this->assertEquals('2021-03-01', $result->stat_period_start_date);
    }

    
    public function testUpdateSettingsStartDateIsNull()
    {
        $user = User::factory()->create([
            'stat_period_length' => 3, 'stat_period_start_date' => '2021-04-01']);
        Passport::actingAs($user);
        
        $response = $this->patch('/api/settings', [
            'stat_period_length' => 5, 'stat_period_start_date' => null]);
        
        $response
                ->assertStatus(200)
                ->assertJsonFragment([
                    'stat_period_length' => 5, 
                    'stat_period_start_date' => null]);

        $result = User::find($user->id);
        $this->assertEquals(5, $result->stat_period_length);
        $this->assertNull($result->stat_period_start_date);
    }
    
    public function testUpdateSettingsValidateStartDate()
    {
        $user = User::factory()->create([
            'stat_period_length' => 3, 'stat_period_start_date' => '2021-04-01']);
        Passport::actingAs($user);
        
        $response = $this->patch('/api/settings', [
            'stat_period_length' => 5, 'stat_period_start_date' => '2021-15-15']);
        
        $response->assertStatus(422);
    }
    
    public function testUpdateSettingsValidatePeriodLength()
    {
        $user = User::factory()->create([
            'stat_period_length' => 3, 'stat_period_start_date' => '2021-04-01']);
        Passport::actingAs($user);
        
        $response = $this->patch('/api/settings', [
            'stat_period_length' => 'Foo', 'stat_period_start_date' => '2021-05-15']);
        
        $response->assertStatus(422);
    }

    
}
