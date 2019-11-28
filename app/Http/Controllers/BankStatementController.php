<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use DateTime;

class BankStatementController extends Controller
{

    /**
     * Get json data from external ressource with Guzzle
     *
     * @return object
     */
    public function getJsonData()
    {
        $client = new Client();
        $request = $client->get('https://agrcf.lib.id/exercice@dev/');
        $response = $request->getBody();

        return $response;
    }

    /**
     * Convert json for Eloquent and format it for next queries
     *
     * @return object
     */
    public function transformToEloquent()
    {
        $operations = collect(json_decode($this->getJsonData()));
        $operations->pull('statut');
        $operations = $operations->flatten();
        $operations = $this->formatEachItems($operations);
        $operations = $operations->sortBy('Date');

        return $operations;
    }

    /**
     * send datas to methods for them to create spents/recipes fields and to format dates
     *
     * @param $operations
     * @return object
     */
    private function formatEachItems($operations)
    {
        return $operations->map(function ($item) {
            $item = $this->computeSpentsAndRecipes($item);
            $item = $this->formatDatetimeToBeSort($item);
            return $item;
        });
    }

    /**
     * Format item into datetime string to be sort
     *
     * @param $item
     * @return object
     */
    public function formatDatetimeToBeSort($item)
    {
        $item->Date = DateTime::createFromFormat('d/m/Y', $item->Date)->format('Y-m-d');
        return $item;
    }

    /**
     * Create spents and recipes field and format them to be absolute
     *
     * @param $item
     * @return object
     */
    public function computeSpentsAndRecipes ($item)
    {
        $amount = str_replace(',', '.', $item->Montant);
        $item->Montant = number_format((float)$amount, 2, '.', '');
        $item->Recette = ($item->Montant > 0) ? $item->Montant : 0;
        $item->Depense = ($item->Montant < 0) ? number_format(abs($item->Montant), 2, '.', '') : 0;
        return $item;
    }

    /**
     * filter eloquent object from request data
     *
     * @param $startDate string
     * @param $endDate string
     * @param $ribId string
     * @return object
     */
    private function filterRangeStatementsAndStatementId($startDate, $endDate, $ribId)
    {
        $operations = $this->transformToEloquent();
        $operations = $operations->where('Date', '>=', $startDate);
        $operations = $operations->where('Date', '<=', $endDate);
        if ($ribId !== null) {
            $operations = $operations->where('RIB', '===', $ribId);
        }
        return $operations;
    }

    /**
     * create api endpoint to get statements from request data
     *
     * @param Request $request
     * @return array
     */
    protected function getRangeStatementsByStatementId(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $ribId = ($request->input('ribId') != null) ? $request->input('ribId') : null;

        $operations = $this->filterRangeStatementsAndStatementId($startDate, $endDate, $ribId);

        return $operations->values()->toArray();
    }

    /**
     * create api endpoint to sum of statements data
     *
     * @param Request $request
     * @return double
     */
    protected function getRecipeByRangeStatementAndRibId(Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $ribId = ($request->input('ribId') != null) ? $request->input('ribId') : null;

        $operations = $this->filterRangeStatementsAndStatementId($startDate, $endDate, $ribId);

        return $operations->sum('Montant');
    }

    /**
     * create api endpoint to get all statements
     *
     * @return array
     */
    protected function getAllStatements()
    {
        $operations = $this->transformToEloquent();
        return $operations->values()->toArray();
    }
}
