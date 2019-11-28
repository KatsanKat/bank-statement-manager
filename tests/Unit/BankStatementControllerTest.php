<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use App\Http\Controllers\BankStatementController;
use Tests\TestCase;

class BankStatementControllerTest extends TestCase
{
    private $bankStatementClass;

    /**
     * @before
     */
    public function setBankStatementClass()
    {
        $this->bankStatementClass = new BankStatementController();
    }

    public function testJsonDataReturnObject()
    {
        $response = $this->bankStatementClass->getJsonData();
        $this->assertIsObject($response);
    }

    public function testFormatDateTimeToBeSortHasDateAttribute()
    {
        $item = new \stdClass();
        $item->Date = '08/08/2017';
        $response = $this->bankStatementClass->formatDatetimeToBeSort($item);

        $this->assertObjectHasAttribute('Date', $response);
    }

    public function testFormatDateTimeToBeSortReturnGoodFormat()
    {
        $item = new \stdClass();
        $item->Date = '08/08/2017';
        $response = $this->bankStatementClass->formatDatetimeToBeSort($item);

        $expected = $item = new \stdClass();
        $expected->Date = '2017-08-08';

        $this->assertEquals($expected, $response);
    }

    public function testComputeSpentsAndRecipesReturnSpentsAndRecipesAttributes()
    {
        $item = new \stdClass();
        $item->Montant = '713,49';

        $response = $this->bankStatementClass->computeSpentsAndRecipes($item);
        $this->assertObjectHasAttribute('Recette', $response);
        $this->assertObjectHasAttribute('Depense', $response);
    }


}
