<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Directory;

class DirectoryControllerTest extends TestCase
{

    public function testCreateDirectory()
    {
        // Arrange
        $payload = [
            'name' => 'Test Directory',
            'sort' => 1
        ];
        
        // Act
        $response = $this->json('POST', '/api/directory/create', $payload);
        $directory = Directory::where('name', $payload['name'])->first();

        // Assert
        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
        $this->assertNotNull($directory);
        $this->assertEquals($payload['name'], $directory->name);
        $this->assertEquals($payload['sort'], $directory->sort);
    }
}