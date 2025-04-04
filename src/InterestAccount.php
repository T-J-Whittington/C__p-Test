<?php

namespace ChipTest;

class InterestAccount {

    //region Constants, Getters and Setters.

    /**
     * Stats API Parameters
     */
    private const STATS_API = [
        'url' => 'https://stats.dev.chip.test/',
        'paths' => [
            'users' => 'users/'
        ]
    ];

    /**
     * Array of interest rates for different tiers of income.<br>
     * Includes a default interest rate for missing income data.<br>
     * This is ordered from high to low.
     * @var array(name array(rate, income))
     */
    private const INTEREST_RATES = [
        'high' => [
            'rate' => 1.02,
            'minimumIncome' => 500000 // £5000, but in pence.
        ],
        'low' => [
            'rate' => 0.93,
            'minimumIncome' => 0
        ],
        'default' => [
            'rate' => 0.5,
            'minimumIncome' => null
        ]
    ];

    /**
     * User ID, in UUIDv4 format.
     * @var null|string $userID UUIDv4 format.
     */
    private ?string $userID = null;

    private bool $accountSet = false;

    public function getUserID() {
        return $this->userID;
    }

    public function setUserID(string $userID) {
        return $this->userID = $userID;
    }

    /**
     * User's income, in pence.
     * @var null|int $income
     */
    private ?int $income = null;

    public function getIncome() {
        return $this->income;
    }

    public function setIncome(?int $income) {
        $this->income = $income;
    }

    /**
     * User's interest rate, set on account creation.
     * @var null|float $interestRate
     */
    private ?float $interestRate = null;

    public function getInterestRate() {
        return $this->interestRate;
    }

    public function setInterestRate(float $interestRate) {
        $this->interestRate = $interestRate;
    }

    //endregion

    public function __construct()
    {
        $this->setInterestRate($this::INTEREST_RATES['default']['rate']);
    }

    /**
     * Create a new account associated to the current $userID.
     * @param string $userID In UUIDv4 format.
     */
    public function newAccount(string $userID) {
        // If the User ID is absent, return a failure.
        if(!$userID){
            throw new \Exception('No userID');
        }

        // Attempt to create a userID via the endpoint.
        $this->handleUserResponse($this->statisticsAPIHandler('user', [$userID]));

        return [$this->getUserID(), $this->getInterestRate()];
    }
    
    /**
     * Get an existing account associated to the given $userID
     * @param string $userID In UUIDv4 format.
     */
    public function getAccount(string $userID) {
        // Attempt to get a userID via the endpoint.
        $this->handleUserResponse($this->statisticsAPIHandler('user', [$userID]));

        
    }

    /**
     * Handle incoming user account data from the API.
     */
    private function handleUserResponse($response) {
        if($response['statusCode'] !== 200) {
            throw new \Exception('Invalid Response - ' . $response['body']);
        }

        $accountDetails = $response['body'];

        print_r("Response value: ");
        print_r($response);

        // Set account details.
        $this->setUserID($accountDetails['id']);

        $this->setIncome($accountDetails['income']);

        $this->handleInterestRate();

        $this->accountSet = true;
    }

    /**
     * Set the appropriate interest rate for the current income.<br>
     * Otherwise leaves the default interest rate.
     */
    private function handleInterestRate() {
        if($this->income){
            $this->setInterestRate(array_find($this::INTEREST_RATES, function($rate) {
                return $rate['minimumIncome'] !== null && $rate['minimumIncome'] <= $this->income;
            })['rate']);
        } else {
            $this->setInterestRate($this::INTEREST_RATES['default']['rate']);
        }
    }


    /**
     * Validation for functions which require an active account.
     */
    private function accountRequired() {
        if(!$this->accountSet){
            throw new \Exception('No account set, required to perform this function.');
        }
    }

    /**
     * Deposit/add funds to the current savings account.
     * @param int $funds Funds to be deposited.
     * @return 
     */
    public function depositFunds(int $funds) {
        $this->accountRequired();
    }

    public function calculateInterest() {
        $this->accountRequired();

    }

    public function listTransactions() {
        $this->accountRequired();

    }


    /**
     * TODO: Update this once the Statistics API is in place.
     * 
     * Simulate sending an API request.
     *
     * @param string $endpoint Mock API endpoint.
     * @param array|null $body Request body.
     * @return array Response with status code and JSON body
     */
    private function statisticsAPIHandler(string $endpoint, ?array $body = null, $new = false): array
    {
        // Select the right endpoint and set up details.
        switch($endpoint) {
            case 'user':
                $method = $new ? 'POST' : 'GET';
                $path = $this::STATS_API['paths']['users'];
                break;
        }

        $endpoint = $this::STATS_API['url'] . $path;

        if($method === 'GET') {
            $endpoint .= $path;
        }

        $request = [
            'method' => strtoupper($method),
            'endpoint' => $endpoint,
            'body' => $body ? json_encode($body, JSON_THROW_ON_ERROR) : null,
            'path' => $path //TODO: Remove once the API is in place.
        ];

        // TODO: Update with a real request once the statistics API is in place.
        // Simulate the request via the mock endpoint.
        return (new StatisticsEndpoint)->sendRequest($request);
    }
}