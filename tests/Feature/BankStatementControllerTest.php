<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class BankStatementControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetRangeStatementsByStatementIdReturnsStatus200()
    {
        $response = $this->json('POST', '/api/getRangeStatementsByStatementId');
        $response->assertStatus(200);
    }

    public function testGetRecipeByRangeStatementAndRibIdReturnsStatus200()
    {
        $response = $this->json('POST', '/api/getRecipeByRangeStatementAndRibId');
        $response->assertStatus(200);
    }
}
